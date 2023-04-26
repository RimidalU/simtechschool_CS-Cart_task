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

defined('BOOTSTRAP') or die('Access denied');

use Tygh\Addons\InstallerInterface;
use Tygh\Core\ApplicationInterface;
use Tygh\Enum\ObjectStatuses;
use Tygh\Enum\YesNo;

class Installer implements InstallerInterface
{
    /**
     * @var \Tygh\Core\ApplicationInterface
     */
    protected $app;

    /**
     * Constructor for Installer class.
     *
     * @param ApplicationInterface $app Application interface
     */
    public function __construct(ApplicationInterface $app)
    {
        $this->app = $app;
    }

    /**
     * @inheritDoc
     */
    public static function factory(ApplicationInterface $app)
    {
        return new self($app);
    }

    /**
     * @inheritDoc
     */
    public function onBeforeInstall()
    {
    }

    /**
     * @inheritDoc
     */
    public function onInstall()
    {
        $processor = [
            'processor'        => 'Tinkoff',
            'processor_script' => 'tinkoff.php',
            'admin_template'   => 'tinkoff.tpl',
            'callback'         => YesNo::YES,
            'type'             => 'P',
            'position'         => 10,
            'addon'            => 'tinkoff',
        ];

        db_replace_into('payment_processors', $processor);
    }

    /**
     * @inheritDoc
     */
    public function onUninstall()
    {
        $payment_ids = db_get_fields(
            'SELECT payment_id'
            . ' FROM ?:payments AS payments'
            . ' LEFT JOIN ?:payment_processors AS payment_processors'
            . ' ON payments.processor_id = payment_processors.processor_id'
            . ' WHERE payment_processors.addon = ?s',
            'tinkoff'
        );


        db_query(
            'UPDATE ?:payments SET processor_id = ?i , status = ?s, processor_params = ?s WHERE payment_id IN (?n)',
            0,
            ObjectStatuses::DISABLED,
            '',
            $payment_ids
        );


        db_query('DELETE FROM ?:payment_processors WHERE addon = ?s', 'tinkoff');
    }
}
