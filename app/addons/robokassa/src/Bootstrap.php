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


namespace Tygh\Addons\Robokassa;


use Tygh\Core\ApplicationInterface;
use Tygh\Core\BootstrapInterface;
use Tygh\Core\HookHandlerProviderInterface;

/**
 * This class describes instructions for loading the robokassa add-on
 *
 * @package Tygh\Addons\Robokassa
 */
class Bootstrap implements BootstrapInterface, HookHandlerProviderInterface
{
    /**
     * @inheritDoc
     */
    public function boot(ApplicationInterface $app)
    {
        $app->register(new ServiceProvider());
    }

    /**
     * @inheritDoc
     */
    public function getHookHandlerMap()
    {
        if (!fn_allowed_for('MULTIVENDOR')) {
            return [];
        }

        return [
            'get_companies' => [
                'addons.robokassa.hook_handlers.companies',
                'onGetCompanies',
            ],
            'get_payments'  => [
                'addons.robokassa.hook_handlers.payments',
                'onGetPayments',
            ],
            'get_payment_processors_post' => [
                'addons.robokassa.hook_handlers.payments',
                'onGetPaymentProcessorsPost',
            ],
            'change_order_status' => [
                'addons.robokassa.hook_handlers.orders',
                'onChangeOrderStatus',
            ],
        ];
    }
}
