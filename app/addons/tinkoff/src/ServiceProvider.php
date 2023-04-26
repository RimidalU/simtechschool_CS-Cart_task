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

namespace Tygh\Addons\Tinkoff;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tygh\Addons\Tinkoff\HookHandlers\OrdersHookHandler;
use Tygh\Addons\Tinkoff\HookHandlers\PaymentsHookHandler;
use Tygh\Application;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Container $app)
    {
        $app['addons.tinkoff.hook_handlers.orders'] = static function (Application $application) {
            return new OrdersHookHandler();
        };

        $app['addons.tinkoff.hook_handlers.payments'] = static function (Application $application) {
            return new PaymentsHookHandler();
        };
    }
}
