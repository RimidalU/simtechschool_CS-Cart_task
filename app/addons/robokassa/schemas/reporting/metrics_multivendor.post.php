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

/** @var array $schema */
$schema['robokassa'] = static function () {
    $processor_data = fn_get_processor_data_by_name('robokassa.php');

    if ($processor_data) {
        $payment_ids = db_get_fields(
            'SELECT payment_id FROM ?:payments WHERE status = ?s AND processor_id = ?i',
            ObjectStatuses::ACTIVE,
            $processor_data['processor_id']
        );

        foreach ($payment_ids as $payment_id) {
            $data = fn_get_processor_data($payment_id);

            if (
                !empty($data['processor_params']['merchantid'])
                && !empty($data['processor_params']['password1'])
                && !empty($data['processor_params']['password2'])
            ) {
                return true;
            }
        }
    }

    return false;
};

$schema['robokassa_split'] = static function () {
    $processor_data = fn_get_processor_data_by_name(RobokassaSplit::PROCESSOR_SCRIPT);

    if ($processor_data) {
        $payment_ids = db_get_fields(
            'SELECT payment_id FROM ?:payments WHERE status = ?s AND processor_id = ?i',
            ObjectStatuses::ACTIVE,
            $processor_data['processor_id']
        );

        foreach ($payment_ids as $payment_id) {
            $data = fn_get_processor_data($payment_id);

            if (!empty($data['processor_params']['master_store_id'])) {
                return true;
            }
        }
    }

    return false;
};

return $schema;
