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

namespace Tygh\Addons\Tinkoff\HookHandlers;

use Tygh\Addons\Tinkoff\Payments\EACQClient;
use Tygh\Enum\NotificationSeverity;
use Tygh\Enum\YesNo;
use Tygh\Tygh;

class OrdersHookHandler
{
    /**
     * The `is_order_allowed_post` hook handler.
     *
     * Action performed:
     *    - Allows to manage order status at returning from Tinkoff.
     *
     * @param int                                        $order_id Order identifier.
     * @param array{user_id: int, order_ids: array<int>} $auth     Authenticator.
     * @param bool                                       $allowed  Flag that managing order is allowed.
     *
     * @param-out bool $allowed
     *
     * @param-out bool $allowed Flag.
     *
     * @see fn_is_order_allowed()
     *
     * @return void
     */
    public function onIsOrderAllowed($order_id, array $auth, &$allowed)
    {
        if (!isset(Tygh::$app['session']['confirming_order'])) {
            return;
        }

        $orders_company_condition = '';
        if (fn_allowed_for('ULTIMATE')) {
            $orders_company_condition = fn_get_company_condition('?:orders.company_id');
        }

        if (!empty($auth['user_id'])) {
            $allowed = (bool) db_get_field(
                'SELECT 1 FROM ?:orders WHERE user_id = ?i AND order_id = ?i ?p',
                $auth['user_id'],
                $order_id,
                $orders_company_condition
            );
            /** @psalm-suppress InvalidArgument */
        } elseif (!empty($auth['order_ids'])) {
            $allowed = in_array($order_id, $auth['order_ids']);
        }

        if (empty($allowed)) {
            $allowed = fn_check_permissions('orders', 'manage', 'admin', 'POST');
        }
        unset(Tygh::$app['session']['confirming_order']);
    }

    /**
     * The `change_order_status_post` hook handler.
     *
     * Actions performed:
     *     - Creates a data set for the closing receipt for Tinkoff
     *     - Sends a data transfer request to the online sales register
     *
     * @param int                                             $order_id           Order identifier
     * @param string                                          $status_to          New order status (one char)
     * @param string                                          $status_from        Old order status (one char)
     * @param array<string, bool>                             $force_notification Array with notification rules
     * @param bool                                            $place_order        True, if this function has been called inside of fn_place_order function
     * @param array<string, int|array<string, array<string>>> $order_info         Order information
     * @param array<string>                                   $edp_data           Downloadable products data
     *
     * @see \fn_change_order_status()
     *
     * @return void
     */
    public function onChangeOrderStatusPost(
        $order_id,
        $status_to,
        $status_from,
        array $force_notification,
        $place_order,
        array $order_info,
        array $edp_data
    ) {
        $processor_data = fn_get_processor_data($order_info['payment_id']);
        if (
            empty($processor_data['processor_script'])
            || $processor_data['processor_script'] !== 'tinkoff.php'
            || empty($order_info['payment_method']['processor_params'])
        ) {
            return;
        }

        $processor_params = $order_info['payment_method']['processor_params'];
        $payment_state = null;

        if (
            !YesNo::toBool($processor_params['send_receipt'])
            || $status_to !== $processor_params['final_success_status']
            || !empty($order_info['payment_info']['addons.tinkoff.closing_receipt_was_sended'])
        ) {
            return;
        }

        $client = new EACQClient(
            $processor_params['terminal_key'],
            $processor_params['password'],
            Tygh::$app['addons.rus_taxes.receipt_factory']
        );

        if (!empty($order_info['payment_info']['payment_id'])) {
            $payment_state = $client->getState((string) $order_info['payment_info']['payment_id']);
        }

        if (empty($payment_state['Success'])) {
            return;
        }

        /** @psalm-suppress InvalidArgument */
        $response = $client->sendClosingReceipt($order_info, $processor_params);
        if (empty($response['Success'])) {
            if (!empty($response['Message'])) {
                fn_set_notification(NotificationSeverity::ERROR, __('error'), $response['Message']);
            }
            fn_update_order_payment_info(
                (int) $order_info['order_id'],
                [
                    'addons.tinkoff.closing_receipt_was_sended' => __('no')
                ]
            );
        } else {
            fn_set_notification(NotificationSeverity::NOTICE, __('notice'), __('addons.tinkoff.closing_receipt_was_sended'));
            fn_update_order_payment_info(
                (int) $order_info['order_id'],
                [
                    'addons.tinkoff.closing_receipt_was_sended' => __('yes')
                ]
            );
        }
    }
}
