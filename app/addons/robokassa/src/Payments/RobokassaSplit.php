<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Tygh\Addons\Robokassa\Payments;

use Tygh\Addons\Robokassa\PayoutsManager;
use Tygh\Database\Connection;
use Tygh\Enum\OrderStatuses;

class RobokassaSplit
{
    const PROCESSOR_SCRIPT = 'robokassa_split.php';
    const PAYMENT_URL = 'https://auth.robokassa.ru/Merchant/Index.aspx';

    /** @var array<string> $processor_params */
    protected $processor_params = [];

    /** @var int $payment_id */
    protected $payment_id;

    /** @var \Tygh\Database\Connection $db */
    protected $db;

    /** @var array<int, array<string>|null> $receivers_cache */
    protected static $receivers_cache = [];

    /**
     * RobokassaSplit constructor.
     *
     * @param int                $payment_id       Payment ID
     * @param Connection         $db               Database object
     * @param null|array<string> $processor_params Processor params
     *
     * @return void
     */
    public function __construct(
        $payment_id,
        Connection $db,
        $processor_params
    ) {
        $this->payment_id = $payment_id;
        $this->db = $db;

        if ($processor_params === null) {
            $this->processor_params = static::getProcessorParameters($payment_id);
        } else {
            $this->processor_params = $processor_params;
        }
    }

    /**
     * @param int $payment_id Payment ID
     *
     * @return array<string>
     */
    private static function getProcessorParameters($payment_id)
    {
        if ($payment_id && $processor_data = fn_get_processor_data($payment_id)) {
            return $processor_data['processor_params'];
        }

        return [];
    }

    /**
     * Obtains Robokassa store id and account number to transfer funds to.
     *
     * @param int $company_id Vendor company ID.
     *
     * @return array<string>|null Array of company store id and account number
     */
    public static function getReceiver($company_id)
    {
        if (!isset(static::$receivers_cache[$company_id])) {
            if ($company_id) {
                $company_data = fn_get_company_data($company_id);
                static::$receivers_cache[$company_id] = [
                    'robokassa_store_id'       => $company_data['robokassa_store_id'],
                    'robokassa_account_number' => $company_data['robokassa_account_number'],
                ];
            } else {
                static::$receivers_cache[$company_id] = null;
            }
        }

        return static::$receivers_cache[$company_id];
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::PAYMENT_URL;
    }

    /**
     * @param array<string|int|float> $order_info Order info
     *
     * @return array<int|string>|false
     */
    public function getRequestData(array $order_info)
    {
        $total = $this->formatAmount($order_info['total']);

        $crc = md5(
            $this->processor_params['master_store_id']
            . ':' . $total
            . ':' . $order_info['order_id']
            . ':' . $this->processor_params['password1']
        );

        $orders_queue = $this->getOrders($order_info);

        // Check that all receivers are valid
        if (!$this->validateOrdersQueueReceivers($orders_queue)) {
            return false;
        }

        $request_data = [
            'MrchLogin'      => $this->processor_params['master_store_id'],
            'OutSum'         => $total,
            'InvId'          => $order_info['order_id'],
            'Desc'           => 'Order #' . $order_info['order_id'],
            'SignatureValue' => $crc,
            'Culture'        => CART_LANGUAGE,
        ];

        $split = $this->formatSplitParam($orders_queue);

        if ($split) {
            $request_data['Split'] = $split;
        }

        if ($this->processor_params['mode'] !== 'live') {
            $request_data['isTest'] = 1;
        }

        return $request_data;
    }

    /**
     * Gets orders that should be paid.
     *
     * @param array<int|string> $order Parent order info
     *
     * @return array<string|int, string|int> Keys are order IDs, values are vendors IDs
     */
    protected function getOrders(array $order)
    {
        if ($order['status'] === OrderStatuses::PARENT) {
            $queue = $this->db->getSingleHash(
                'SELECT order_id, company_id FROM ?:orders WHERE parent_order_id = ?i',
                ['order_id', 'company_id'],
                $order['order_id']
            );
        } else {
            $queue = [
                $order['order_id'] => $order['company_id'],
            ];
        }

        return $queue;
    }

    /**
     * Checks that all companies in an order have robokassa_store_id.
     *
     * @param array<string|int, int> $orders_queue Orders queue
     *
     * @return bool
     */
    protected function validateOrdersQueueReceivers(array $orders_queue)
    {
        foreach ($orders_queue as $company_id) {
            $reciever = static::getReceiver($company_id);

            if (empty($reciever['robokassa_store_id']) && $company_id) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string|int, string|int> $orders_queue Orders queue
     *
     * @return string
     */
    protected function formatSplitParam(array $orders_queue)
    {
        $split = '';
        $total_fee = 0;

        foreach ($orders_queue as $order_id => $company_id) {
            if (!$company_id) {
                // fallback for Vendor debt payout
                continue;
            }

            $suborder_info = fn_get_order_info((int) $order_id);

            if (
                !$suborder_info
                || !$this->formatAmount($suborder_info['total'])
            ) {
                continue;
            }

            $payouts_manager = new PayoutsManager((int) $company_id);
            $receiver = static::getReceiver($suborder_info['company_id']);

            if (!$receiver) {
                continue;
            }

            $application_fee = $payouts_manager->getOrderFee($suborder_info['order_id']);
            $application_fee = min($application_fee, $suborder_info['total']);

            $accounting_withdrawal = $suborder_info['total'] - $application_fee;

            $accounting_withdrawal = $this->formatAmount($accounting_withdrawal);
            $application_fee = $this->formatAmount($application_fee);
            $total_fee += (float) $application_fee;

            $split .= ',' . $receiver['robokassa_store_id'] . ':' . $accounting_withdrawal . ':';

            //phpcs:ignore
            if (!empty($receiver['robokassa_account_number'])) {
                $split .= $receiver['robokassa_account_number'];
            }
        }

        $master_store = $this->processor_params['master_store_id'] . ':' . $total_fee . ':';

        if (!empty($this->processor_params['account_number'])) {
            $master_store .= $this->processor_params['account_number'];
        }

        return $split
            ? $master_store . $split
            : '';
    }

    /**
     * Formats payment amount by currency.
     *
     * @param float $amount Payment amount
     *
     * @return string Order amount
     */
    protected function formatAmount($amount)
    {
        $amount = fn_format_price_by_currency($amount, CART_PRIMARY_CURRENCY, 'RUB');
        return number_format($amount, 2, '.', '');
    }

    /**
     * @param array<string> $response Robokassa response
     *
     * @return bool
     */
    public function isValidSignatureValue(array $response)
    {
        if (
            empty($response['OutSum'])
            || empty($response['InvId'])
            || empty($response['SignatureValue'])
            || empty($this->processor_params['password2'])
        ) {
            return false;
        }

        $crc = strtoupper(md5($response['OutSum'] . ':' . $response['InvId'] . ':' . $this->processor_params['password2']));

        return strtoupper($response['SignatureValue']) === $crc;
    }

    /**
     * @param array<string|int|float> $order_info Order info
     *
     * @return false|void
     */
    public function createWithdrawals($order_info)
    {
        $orders_queue = $this->getOrders($order_info);

        // Check that all receivers are valid
        if (!$this->validateOrdersQueueReceivers($orders_queue)) {
            return false;
        }

        foreach ($orders_queue as $order_id => $company_id) {
            if (!$company_id) {
                // fallback for Vendor debt payout
                continue;
            }

            $suborder_info = fn_get_order_info((int) $order_id);

            if (
                !$suborder_info
                || !$this->formatAmount($suborder_info['total'])
            ) {
                continue;
            }

            $payouts_manager = new PayoutsManager((int) $company_id);
            $receiver = static::getReceiver((int) $suborder_info['company_id']);

            if (!$receiver) {
                continue;
            }

            $application_fee = $payouts_manager->getOrderFee($suborder_info['order_id']);
            $application_fee = min($application_fee, $suborder_info['total']);

            $accounting_withdrawal = $suborder_info['total'] - $application_fee;

            $payouts_manager->createWithdrawal($accounting_withdrawal, (int) $order_id);
        }
    }
}
