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

use Tygh\Addons\Robokassa\Payments\RobokassaSplit;
use Tygh\Enum\ObjectStatuses;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    return [CONTROLLER_STATUS_OK];
}

if ($mode === 'update') {
    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];

    $robokassa_split_payment_methods = fn_get_payments(
        [
            'processor_script' => RobokassaSplit::PROCESSOR_SCRIPT,
            'status'           => ObjectStatuses::ACTIVE,
        ]
    );

    $view->assign('is_robokassa_split_used', !empty($robokassa_split_payment_methods));
}

return [CONTROLLER_STATUS_OK];
