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

use Tygh\Addons\Tinkoff\Enum\PayTypes;
use Tygh\Addons\Tinkoff\Payments\EACQClient;

defined('BOOTSTRAP') or die('Access denied');

/** @var string $mode */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    return [CONTROLLER_STATUS_OK];
}

if ($mode === 'details') {
    /** @var array $order_info */
    $order_info = Tygh::$app['view']->getTemplateVars('order_info');
    $is_tinkoff_payment = isset($order_info['payment_method']['processor_params']['is_tinkoff']);
    $is_payment_id_exists = isset($order_info['payment_info']['payment_id']);
    if ($is_tinkoff_payment && $is_payment_id_exists) {
        $payment_info = $order_info['payment_info'];
        $processor_params = $order_info['payment_method']['processor_params'];
        $client = new EACQClient($processor_params['terminal_key'], $processor_params['password'], Tygh::$app['addons.rus_taxes.receipt_factory']);
        $response = $client->getState($order_info['payment_info']['payment_id']);
        if (!empty($response['Success'])) {
            fn_update_order_payment_info($order_info['order_id'], ['addons.tinkoff.payment_status' => $response['Status']]);
            $order_info['payment_info']['addons.tinkoff.payment_status'] = $response['Status'];
        }
        $is_show_button = isset($processor_params['pay_type'])
            && $processor_params['pay_type'] === PayTypes::TWO_STEP
            && (
                isset($payment_info['addons.tinkoff.payment_status'])
                && $payment_info['addons.tinkoff.payment_status'] === 'AUTHORIZED'
                && (
                    !isset($response['Status'])
                    || ($response['Status'] === 'AUTHORIZED')
                )
                || (isset($response['Status']) && $response['Status'] === 'AUTHORIZED')
            );

        Tygh::$app['view']->assign(
            'is_show_transfer_funds_button',
            $is_show_button
        );
        Tygh::$app['view']->assign('order_info', $order_info);
        Tygh::$app['view']->assign('is_tinkoff_payment', $is_tinkoff_payment);
    }
}
