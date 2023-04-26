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

use Tygh\Enum\TaxSystems;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

/**
 * @var string $mode
 */
if (
    $mode === 'processor'
    && (!empty($_REQUEST['processor_id']) || !empty($_REQUEST['payment_id']))
) {
    $processor_data = (!empty($_REQUEST['processor_id']))
        ? db_get_row('SELECT * FROM ?:payment_processors WHERE processor_id = ?i', $_REQUEST['processor_id'])
        : fn_get_processor_data($_REQUEST['payment_id']);

    if (
        !empty($processor_data['processor_script'])
        && $processor_data['processor_script'] === 'tinkoff.php'
    ) {
        $tax_systems = TaxSystems::getAllValues();

        /** @var \Tygh\SmartyEngine\Core $view */
        $view = Tygh::$app['view'];

        $view->assign('tax_systems', $tax_systems);
    }
}
