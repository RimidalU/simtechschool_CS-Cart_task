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

use Tygh;
use Tygh\Addons\Robokassa\Payments\RobokassaSplit;
use Tygh\Application;
use Tygh\Enum\SiteArea;

/**
 * This class describes the hook handlers related to payment management
 *
 * @package Tygh\Addons\Robokassa\HookHandlers
 */
class PaymentsHookHandler
{
    /** @var Application $application */
    protected $application;

    /**
     * PaymentsHookHandler constructor.
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
     * The "get_payments" hook handler.
     *
     * Actions performed:
     * - Removes Robokassa method from customer area when products' vendor has no Robokassa store id.
     *
     * @param array<string, string|int> $params    Array of flags/data which determines which data should be gathered
     * @param array<string>             $fields    List of fields for retrieving
     * @param array<string>             $join      Array with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
     * @param array<string>             $order     Array containing SQL-query with sorting fields
     * @param array<string>             $condition Array containing SQL-query condition possibly prepended with a logical operator AND
     *
     * @return void
     *
     * @see fn_get_payments()
     */
    public function onGetPayments(array $params, array $fields, array $join, array $order, array &$condition)
    {
        //phpcs:ignore
        if ((SiteArea::isStorefront((string) $params['area']) || defined('ORDER_MANAGEMENT'))
            && !empty(Tygh::$app['session']['cart']['product_groups'])
        ) {
            foreach (Tygh::$app['session']['cart']['product_groups'] as $product_group) {
                if (!RobokassaSplit::getReceiver($product_group['company_id'])) {
                    $condition[] = db_quote(
                        '(?:payment_processors.processor_script IS NULL'
                        . ' OR ?:payment_processors.processor_script <> ?s)',
                        RobokassaSplit::PROCESSOR_SCRIPT
                    );
                    break;
                }
            }
        }
    }

    /**
     * The "get_payment_processors_post" hook handler.
     *
     * Actions performed:
     *     - Adds specific attributes to some payment processor for categorization.
     *
     * @param string                                $lang_code  Two letter language code
     * @param array<array<string, string|int|bool>> $processors Payment processors list
     *
     * @return void
     *
     * @see \fn_get_payment_processors()
     */
    public function onGetPaymentProcessorsPost($lang_code, &$processors)
    {
        foreach ($processors as &$processor) {
            //phpcs:ignore
            if ($processor['addon'] === 'robokassa') {
                $processor['russian'] = true;
            }
        }
        unset($processor);
    }
}
