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

namespace Tygh\Addons\Tinkoff\Payments;

defined('BOOTSTRAP') or die('Access denied');

use Tygh\Addons\RusTaxes\ReceiptFactory;
use Tygh\Addons\Tinkoff\Client\EACQApiClient;
use Tygh\Addons\Tinkoff\Enum\QRDataTypes;
use Tygh\Enum\YesNo;
use Tygh\Http;
use Tygh\Tygh;

/**
 * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint
 */
class EACQClient extends EACQApiClient
{
    /** @var string $terminal_key */
    protected $terminal_key;

    /** @var string $password */
    protected $password;

    /**
     * @var ReceiptFactory
     */
    private $receipt_factory;

    /**
     * Constructor of EACQClient class.
     *
     * @param string         $terminal_key    Terminal key.
     * @param string         $password        Terminal password.
     * @param ReceiptFactory $receipt_factory Receipt factory.
     */
    public function __construct($terminal_key, $password, ReceiptFactory $receipt_factory)
    {
        $this->terminal_key = $terminal_key;
        $this->password = $password;
        $this->receipt_factory = $receipt_factory;
    }

    /**
     * Method for initialize payment at Tinkoff API.
     *
     * @param array<string, float|int|string|array<string, string>>                $order_info       Order information.
     * @param array{send_receipt: string, pay_type: string, is_recurrent?: string} $processor_params Payment processor parameters.
     *
     * @return array<string, string>|string
     */
    public function init(array $order_info, array $processor_params)
    {
        $method = 'Init';

        $amount = fn_format_price_by_currency($order_info['total'], CART_PRIMARY_CURRENCY, 'RUB') * 100;
        $data = [
            'TerminalKey' => $this->terminal_key,
            'Amount'      => (string) $amount,
            'OrderId'     => $order_info['order_id'],
        ];
        if (YesNo::toBool($processor_params['send_receipt'])) {
            $data['Receipt'] = $this->generateReceipt($order_info, $processor_params);
        }

        /** @var \Tygh\Storefront\Repository $repository */
        $repository = Tygh::$app['storefront.repository'];
        $storefront = $repository->findById((int) $order_info['storefront_id']);
        if ($storefront) {
            $protocol = fn_get_storefront_protocol() . '://';
            $storefront_url = $protocol . $storefront->url;
            $data['NotificationURL'] = $storefront_url . '/index.php?dispatch=tinkoff.get_notification';
            $data['SuccessURL']      = $storefront_url . '/index.php?dispatch=tinkoff.success&OrderId=${OrderId}&PaymentId=${PaymentId}';
            $data['FailURL']         = $storefront_url . '/index.php?dispatch=tinkoff.fail&OrderId=${OrderId}&Message=${Message}';
        }

        $optional_data = [
            'IP'              => $order_info['ip_address'],
            'Language'        => CART_LANGUAGE,
            'Recurrent'       => $processor_params['is_recurrent'] ?? YesNo::NO,
            'PayType'         => $processor_params['pay_type'],
        ];
        if (YesNo::toBool($optional_data['Recurrent'])) {
            /** @psalm-suppress InvalidArrayOffset */
            unset($optional_data['Recurrent'], $optional_data['CustomerKey']); //TODO For recurrent payments this has to be changed.
        }
        $data = array_merge($data, $optional_data);
        /** @psalm-suppress InvalidArgument */
        $data['Token'] = $this->generateRequestToken($data, $this->password);
        return $this->execute($method, Http::POST, $data);
    }

    /**
     * Creates QR-code for payment.
     *
     * @param string $payment_id Payment identifier into Tinkoff API.
     * @param string $type       QR type.
     *
     * @return string|string[]
     */
    public function getQr($payment_id, $type = QRDataTypes::PAYLOAD)
    {
        $method = 'GetQr';
        $data = [
            'TerminalKey' => $this->terminal_key,
            'PaymentId'   => $payment_id,
            'DataType'    => $type,
        ];
        $data['Token'] = $this->generateRequestToken($data, $this->password);
        return $this->execute($method, Http::POST, $data);
    }

    /**
     * Method for confirming payment. For 2step payments.
     *
     * @param array<string, int|string|array<string, string>> $order_info Order information.
     *
     * @return string|string[]
     */
    public function confirm(array $order_info)
    {
        $method = 'Confirm';
        $query_params = [
            'TerminalKey' => $this->terminal_key,
            'PaymentId'   => $order_info['payment_info']['payment_id'],
        ];

        $query_params['Token'] = $this->generateRequestToken($query_params, $this->password);
        return $this->execute($method, Http::POST, $query_params);
    }

    /**
     * Method for canceling payment.
     *
     * @param array<string, int|string|array<string, string>> $order_info       Order information.
     * @param array{send_receipt: string}                     $processor_params Payment processor parameters.
     *
     * @return string|string[]
     */
    public function cancel(array $order_info, array $processor_params)
    {
        $method = 'Cancel';
        $query_params = [
            'TerminalKey' => $this->terminal_key,
            'PaymentId'   => $order_info['payment_info']['payment_id'],
        ];
        if (YesNo::toBool($processor_params['send_receipt'])) {
            $query_params['Receipt'] = $this->generateReceipt($order_info, $processor_params);
        }
        $query_params['Token'] = $this->generateRequestToken($query_params, $this->password);
        return $this->execute($method, Http::POST, $query_params);
    }

    /**
     * Method for getting state of payment.
     *
     * @param string $payment_id Payment identifier.
     *
     * @return string|string[]
     */
    public function getState($payment_id)
    {
        $method = 'GetState';
        $query_params = [
            'TerminalKey' => $this->terminal_key,
            'PaymentId'   => $payment_id,
        ];

        $query_params['Token'] = $this->generateRequestToken($query_params, $this->password);

        return $this->execute($method, Http::POST, $query_params);
    }

    /**
     * Method for getting notifications about payment states.
     *
     * @return string|string[]
     */
    public function resend()
    {
        $method = 'Resend';
        $query_params = [
            'TerminalKey' => $this->terminal_key,
        ];
        $query_params['Token'] = $this->generateRequestToken($query_params, $this->password);

        return $this->execute($method, Http::POST, $query_params);
    }

    /**
     * Method for sending second receipt. For 2-step payments.
     *
     * @param array<string, int|string|array<string, string>> $order_info       Order information.
     * @param array{send_receipt: string}                     $processor_params Payment processor parameters.
     *
     * @return string|string[]
     */
    public function sendClosingReceipt(array $order_info, array $processor_params)
    {
        $method = 'SendClosingReceipt';
        $query_params = [
            'TerminalKey' => $this->terminal_key,
            'PaymentId'   => $order_info['payment_info']['payment_id'],
        ];
        if (YesNo::toBool($processor_params['send_receipt'])) {
            $query_params['Receipt'] = $this->generateReceipt($order_info, $processor_params, 'full_payment', 'commodity');
            $query_params['Receipt']['Payments']['AdvancePayment'] = $query_params['Receipt']['Payments']['Electronic'];
            $query_params['Receipt']['Payments']['Electronic'] = 0;
        }
        $query_params['Token'] = $this->generateRequestToken($query_params, $this->password);

        return $this->execute($method, Http::POST, $query_params);
    }

    /**
     * Method for generating receipt information.
     *
     * @param array<string, int|string|array<string, string>> $order_info       Order information.
     * @param array<string, string>                           $processor_params Parameters of payment processor.
     * @param string                                          $payment_method   Payment method for current receipt.
     * @param string                                          $payment_object   Payment object for current receipt.
     *
     * @return array<string, string|array<string, string>>|null
     *
     * @psalm-return array{Email: array<string, string>|int|string, FfdVersion: string, Items: array<int, array{Amount: float|string, Name: string, PaymentMethod: string, PaymentObject: string, Price: float|string, Quantity: float, Tax: string}>, Payments: array{AdvancePayment: string|int, Cash: string|int, Credit: string|int, Electronic: string|int, Provision: string|int}, Phone: array<string, string>|int|string, Taxation: mixed}|null
     */
    protected function generateReceipt(array $order_info, array $processor_params, $payment_method = 'full_prepayment', $payment_object = 'payment')
    {
        $receipt = $this->receipt_factory->createReceiptFromOrder($order_info, 'RUB');

        if (!$receipt) {
            return null;
        }
        $items = [];
        $receipt_items = $receipt->getItems();
        if (empty($receipt_items)) {
            return null;
        }

        foreach ($receipt_items as $item) {
            $items[] = [
                'Name'          => $item->getName(),
                'Quantity'      => $item->getQuantity(),
                'Amount'        => (string) ($item->getTotal() * 100),
                'Price'         => (string) ($item->getPrice() * 100),
                'PaymentMethod' => $payment_method,
                'PaymentObject' => $payment_object,
                'Tax'           => empty($item->getTaxType()) ? 'none' : $item->getTaxType(),
                //AgentData
                //SupplierInfo
            ];
        }

        $receipt_sum = (float) sprintf('%2f', round((float) $order_info['total'] + 0.00000000001, 2)) * 100;

        return [
            'Email'         => $order_info['email'],
            'Phone'         => $order_info['phone'],
            'Taxation'      => $processor_params['tax_system'],
            'Payments'      => $this->createPayments([
                'electronic' => (string) $receipt_sum,
            ]),
            'Items'         => $items,
            'FfdVersion'    => $processor_params['ffd_version']
        ];
    }

    /**
     * Creates payment object as part of receipt object.
     *
     * @param array{cash?: int|string, electronic?: int|string, prepayment?: int|string, credit?: int|string, other?: int|string} $values Distribution of funds by types.
     *
     * @return array{Cash: int|string, Electronic: int|string, AdvancePayment: int|string, Credit: int|string, Provision: int|string}
     */
    private function createPayments(array $values): array
    {
        $default_values = [
            'cash'       => 0,
            'electronic' => 0,
            'prepayment' => 0,
            'credit'     => 0,
            'other'      => 0,
        ];
        $values = array_merge($default_values, $values);
        return [
            'Cash'           => $values['cash'],
            'Electronic'     => $values['electronic'],
            'AdvancePayment' => $values['prepayment'],
            'Credit'         => $values['credit'],
            'Provision'      => $values['other']
        ];
    }
}
