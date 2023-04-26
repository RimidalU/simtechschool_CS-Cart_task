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

namespace Tygh\Addons\YandexCheckout\Services;

use Tygh\Addons\YandexCheckout\Commands\ChangeOrderStatusWithYandexCheckout;
use Tygh\Addons\YandexCheckout\Commands\ChangeOrderStatusWithYandexCheckoutForMarketplaces;
use Tygh\Addons\YandexCheckout\Enum\ProcessorScript;

class OrderService
{
    /**
     * Executes correct operations, associated with change of order status.
     *
     * @param array<string, int|array<string, array<string>>> $order_info Order information.
     * @param string                                          $status_to  New order status.
     *
     * @return void
     */
    public function processChangeOrderStatus(array $order_info, $status_to)
    {
        if (!in_array($order_info['status'], fn_get_settled_order_statuses())) {
            /** @var int $order_info['order_id'] */
            fn_change_order_status($order_info['order_id'], 'P');
        } else {
            $this->performOperationsConnectedToOrderStatusChange($order_info, $status_to);
        }
    }

    /**
     * Creates full payment receipt for YooKassa
     * Creates full pre-payment and full payment receipts for YooKassa for Marketplaces
     * Creates withdrawals for YooKassa for Marketplaces
     *
     * @param array<string, int|array<string, array<string>>> $order_info Order information.
     * @param string                                          $status_to  New order status.
     *
     * @return void
     */
    public function performOperationsConnectedToOrderStatusChange($order_info, $status_to)
    {
        $processor_id = null;
        if (isset($order_info['payment_method']['processor_id'])) {
            $processor_id = (int) $order_info['payment_method']['processor_id'];
        }

        $is_yandex_checkout_payment = (bool) db_get_field(
            'SELECT 1'
            . ' FROM ?:payment_processors'
            . ' WHERE processor_script = ?s'
            . ' AND addon = ?s'
            . ' AND processor_id = ?i',
            ProcessorScript::YANDEX_CHECKOUT,
            'yandex_checkout',
            $processor_id
        );
        if ($is_yandex_checkout_payment) {
            $command = new ChangeOrderStatusWithYandexCheckout($order_info['order_id'], $status_to, $order_info);
            $order_status_change_result = $command->run();
            if (!$order_status_change_result->isSuccess()) {
                $order_status_change_result->showNotifications(false, 'S');
            }

            return;
        }

        $is_yandex_checkout_for_marketplaces_payment = (bool) db_get_field(
            'SELECT 1'
            . ' FROM ?:payment_processors'
            . ' WHERE processor_script = ?s'
            . ' AND addon = ?s'
            . ' AND processor_id = ?i',
            ProcessorScript::YANDEX_CHECKOUT_FOR_MARKETPLACES,
            'yandex_checkout',
            $processor_id
        );
        //phpcs:ignore
        if ($is_yandex_checkout_for_marketplaces_payment) {
            $command = new ChangeOrderStatusWithYandexCheckoutForMarketplaces($order_info['order_id'], $status_to, $order_info);
            $order_status_change_result = $command->run();
            if (!$order_status_change_result->isSuccess()) {
                $order_status_change_result->showNotifications(false, 'S');
            }
        }
    }
}
