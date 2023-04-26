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

namespace Tygh\Addons\Robokassa\HookHandlers;

use Tygh\Application;

/**
 * This class describes the hook handlers related to company management
 *
 * @package Tygh\Addons\Robokassa\HookHandlers
 */
class CompaniesHookHandler
{
    /** @var Application $application */
    protected $application;

    /**
     * CompaniesHookHandler constructor.
     *
     * @param Application $application Application
     *
     * @return void
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * The "get_companies" hook handler.
     *
     * Actions performed:
     * - Adds field robokassa_store_id and robokassa_account_number to the list of retrieved fields.
     *
     * @param array<string, string|int> $params Companies search params
     * @param array<string>             $fields Fields that should be retrieved
     *
     * @return void
     *
     * @see fn_get_companies()
     */
    public function onGetCompanies($params, array &$fields)
    {
        $fields[] = db_quote('?:companies.robokassa_store_id');
        $fields[] = db_quote('?:companies.robokassa_account_number');
    }
}
