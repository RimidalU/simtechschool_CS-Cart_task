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

defined('BOOTSTRAP') or die('Access denied');

use Tygh\Addons\Robokassa\ServiceProvider;
use Tygh\Enum\OrderStatuses;

/** @var array $order_info */
/** @var array $processor_data */

if (defined('PAYMENT_NOTIFICATION')) {
    /** @var string $mode */
    if (empty($_REQUEST['InvId']) || empty($_REQUEST['OutSum']) || (empty($_REQUEST['SignatureValue']) && $mode !== 'fail')) {
        die('Access denied');
    }

    $order_id = (int) $_REQUEST['InvId'];
    $pp_response = [];

    switch ($mode) {
        case 'result':
            $order_info = fn_get_order_info($order_id);

            if (!$order_info || empty($order_info['payment_id'])) {
                fn_order_placement_routines('route', $order_id);
                break;
            }

            /** @var \Tygh\Addons\Robokassa\Payments\RobokassaSplit|null $processor */
            $processor = ServiceProvider::getProcessorFactory()->getByPaymentId($order_info['payment_id']);

            if (!$processor) {
                fn_order_placement_routines('route', $order_id);
                break;
            }

            if ($processor->isValidSignatureValue($_REQUEST)) {
                $pp_response['order_status'] = OrderStatuses::PAID;
                $pp_response['reason_text'] = __('robokassa.approved');
                $processor->createWithdrawals($order_info);
            } else {
                $pp_response['order_status'] = OrderStatuses::FAILED;
                $pp_response['reason_text'] = __('robokassa.control_summ_wrong');
            }
            fn_finish_payment($order_id, $pp_response);

            die('OK' . $order_id);
        case 'success':
            $order_info = fn_get_order_info($order_id);
            if (empty($order_info['payment_info']['order_status'])) {
                fn_change_order_status($order_id, OrderStatuses::OPEN);
            }

            fn_order_placement_routines('route', $order_id);
            break;
        case 'fail':
            $pp_response = [
                'order_status' => OrderStatuses::INCOMPLETED,
                'reason_text'  => __('robokassa.text_transaction_cancelled'),
            ];
            fn_finish_payment($order_id, $pp_response);

            fn_order_placement_routines('route', $order_id);
            break;
        default:
            fn_order_placement_routines('route', $order_id);
            break;
    }
} else {
    /**
     * @var array $order_info
     * @var array $processor_data
     */
    $processor = ServiceProvider::getProcessorFactory()->getByPaymentId(
        $order_info['payment_id'],
        $processor_data['processor_params']
    );

    if ($processor) {
        $url = $processor->getUrl();
        $request_data = $processor->getRequestData($order_info);

        if ($request_data) {
            fn_create_payment_form($url, $request_data, 'Robokassa Split');
        }
    }
}
