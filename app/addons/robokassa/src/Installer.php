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

use Tygh\Addons\InstallerInterface;
use Tygh\Addons\Robokassa\Payments\RobokassaSplit;
use Tygh\Core\ApplicationInterface;
use Tygh\Enum\ObjectStatuses;
use Tygh\Enum\YesNo;

/**
 * This class describes the instructions for installing and uninstalling the robokassa add-on
 *
 * @package Tygh\Addons\Robokassa
 */
class Installer implements InstallerInterface
{
    /**
     * @inheritDoc
     */
    public static function factory(ApplicationInterface $app)
    {
        return new self();
    }

    /**
     * @inheritDoc
     */
    public function onInstall()
    {
        $this->addRobokassaProcessor();
        //phpcs:ignore
        if (fn_allowed_for('MULTIVENDOR')) {
            $this->addRobokassaSplitProcessor();
        }
    }

    /**
     * @inheritDoc
     */
    public function onUninstall()
    {
        $processor_ids = db_get_fields(
            'SELECT processor_id'
            . ' FROM ?:payment_processors'
            . ' WHERE addon = ?s',
            'robokassa'
        );

        if ($processor_ids) {
            db_query('UPDATE ?:payments SET processor_id = ?i, status = ?s WHERE processor_id IN (?n)', 0, ObjectStatuses::DISABLED, $processor_ids);
        }

        $this->removeRobokassaProcessor();
        //phpcs:ignore
        if (fn_allowed_for('MULTIVENDOR')) {
            $this->removeRobokassaSplitProcessor();
        }
    }

    /**
     * @inheritDoc
     */
    public function onBeforeInstall()
    {
    }

    /**
     * @return void
     */
    protected function addRobokassaProcessor()
    {
        $processor = [
            'processor'          => 'Робокасса',
            'processor_script'   => 'robokassa.php',
            'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
            'admin_template'     => 'robokassa.tpl',
            'callback'           => YesNo::NO,
            'type'               => 'P',
            'position'           => 10,
            'addon'              => 'robokassa',
        ];

        db_replace_into('payment_processors', $processor);
    }

    /**
     * @return void
     */
    protected function removeRobokassaProcessor()
    {
        $processor_id = db_get_field(
            'SELECT processor_id FROM ?:payment_processors'
            . ' WHERE processor_script = ?s'
            . ' AND addon = ?s',
            'robokassa.php',
            'robokassa'
        );

        if (!$processor_id) {
            return;
        }

        db_query('DELETE FROM ?:payment_processors WHERE processor_id = ?i', $processor_id);
        db_query(
            'UPDATE ?:payments SET ?u WHERE processor_id = ?i',
            [
                'processor_id'     => 0,
                'processor_params' => '',
                'status'           => ObjectStatuses::DISABLED,
            ],
            $processor_id
        );
    }

    /**
     * @return void
     */
    protected function addRobokassaSplitProcessor()
    {
        $processor = [
            'processor'          => 'Робокасса с разделением платежей [Beta]',
            'processor_script'   => RobokassaSplit::PROCESSOR_SCRIPT,
            'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
            'admin_template'     => 'robokassa_split.tpl',
            'callback'           => YesNo::NO,
            'type'               => 'P',
            'position'           => 20,
            'addon'              => 'robokassa',
        ];

        db_replace_into('payment_processors', $processor);
    }

    /**
     * @return void
     */
    protected function removeRobokassaSplitProcessor()
    {
        $processor_id = db_get_field(
            'SELECT processor_id FROM ?:payment_processors'
            . ' WHERE processor_script = ?s'
            . ' AND addon = ?s',
            RobokassaSplit::PROCESSOR_SCRIPT,
            'robokassa'
        );

        if (!$processor_id) {
            return;
        }

        db_query('DELETE FROM ?:payment_processors WHERE processor_id = ?i', $processor_id);
        db_query(
            'UPDATE ?:payments SET ?u WHERE processor_id = ?i',
            [
                'processor_id'     => 0,
                'processor_params' => '',
                'status'           => ObjectStatuses::DISABLED,
            ],
            $processor_id
        );
    }
}
