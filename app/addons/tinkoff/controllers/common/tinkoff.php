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

use Tygh\Addons\Tinkoff\Payments\EACQClient;
use Tygh\Enum\NotificationSeverity;
use Tygh\Enum\OrderStatuses;

/**
 * @var array  $auth
 * @var string $mode
 */

if ($mode === 'get_notification') {
    $request_information = file_get_contents('php://input');
    if (empty($request_information)) {
        return [CONTROLLER_STATUS_NO_CONTENT];
    }
    $notification = json_decode($request_information, true);
    if (empty($notification['OrderId'])) {
        return [CONTROLLER_STATUS_NO_CONTENT];
    }
    $order_id = $notification['OrderId'];
    $order_info = fn_get_order_info($order_id);

    if (!empty($notification['PaymentId'])) {
        $client = new EACQClient(
            $order_info['payment_method']['processor_params']['terminal_key'],
            $order_info['payment_method']['processor_params']['password'],
            Tygh::$app['addons.rus_taxes.receipt_factory']
        );
        $response = $client->getState($notification['PaymentId']);
        /** @psalm-suppress PossiblyInvalidArrayOffset */
        if ($response['Status'] !== $notification['Status']) {
            return [CONTROLLER_STATUS_OK];
        }
    }

    if (in_array($notification['Status'], ['AUTHORIZED', 'CONFIRMED']) && !in_array($order_info['status'], fn_get_settled_order_statuses())) {
        fn_change_order_status($order_id, OrderStatuses::PAID);
    }
    if (in_array($notification['Status'], ['REVERSED', 'CANCELED', 'REJECTED', 'REFUNDED']) && in_array($order_info['status'], fn_get_settled_order_statuses())) {
        fn_update_order_payment_info($order_id, ['addons.tinkoff.funds_were_transferred' => '']);
        fn_change_order_status($order_id, OrderStatuses::CANCELED);
    }
    return [CONTROLLER_STATUS_OK];
}

if ($mode === 'success') {
    if (!isset($_REQUEST['OrderId'])) {
        return [CONTROLLER_STATUS_DENIED];
    }
    $order_id = $_REQUEST['OrderId'];
    $order_info = fn_get_order_info($order_id);
    if (empty($order_info)) {
        return [CONTROLLER_STATUS_DENIED];
    }
    Tygh::$app['session']['confirming_order'] = true;
    if (!fn_is_order_allowed($order_id, $auth)) {
        return [CONTROLLER_STATUS_DENIED];
    }
    if (in_array($order_info['status'], fn_get_settled_order_statuses())) {
        fn_order_placement_routines('route', $order_info['order_id'], false);
    }
    if (!isset($_REQUEST['PaymentId']) || $order_info['payment_info']['payment_id'] !== $_REQUEST['PaymentId']) {
        return [CONTROLLER_STATUS_DENIED];
    }
    $client = new EACQClient(
        $order_info['payment_method']['processor_params']['terminal_key'],
        $order_info['payment_method']['processor_params']['password'],
        Tygh::$app['addons.rus_taxes.receipt_factory']
    );
    $response = $client->getState($order_info['payment_info']['payment_id']);
    if (!empty($response['Success'])) {
        fn_update_order_payment_info($order_id, ['addons.tinkoff.payment_status' => $response['Status']]);
        if (in_array($response['Status'], ['AUTHORIZED', 'CONFIRMED'])) {
            fn_change_order_status($order_id, OrderStatuses::PAID);
        }
        if (in_array($response['Status'], ['REJECTED', 'CANCELED', 'REVERSED', 'REFUNDED'])) {
            fn_change_order_status($order_id, OrderStatuses::CANCELED);
        }
    }
    fn_order_placement_routines('route', $order_info['order_id'], false);
}

if ($mode === 'fail') {
    if (!isset($_REQUEST['OrderId'])) {
        return [CONTROLLER_STATUS_DENIED];
    }
    $order_id = $_REQUEST['OrderId'];
    $order_info = fn_get_order_info($order_id);
    if (empty($order_info)) {
        return [CONTROLLER_STATUS_DENIED];
    }
    Tygh::$app['session']['confirming_order'] = true;
    if (!fn_is_order_allowed($order_id, $auth)) {
        return [CONTROLLER_STATUS_DENIED];
    }

    fn_set_notification(NotificationSeverity::ERROR, __('addons.tinkoff.payment_failed'), $_REQUEST['Message']);
    fn_update_order_payment_info($order_id, ['addons.tinkoff.payment_message' => $_REQUEST['Message']]);
    fn_order_placement_routines('route', $order_info['order_id'], false);
    //TODO Remove extra notification about transaction canceled by customer.
}
