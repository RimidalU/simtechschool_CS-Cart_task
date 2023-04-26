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

namespace Tygh\Addons\Tinkoff\HookHandlers;

class PaymentsHookHandler
{
    /**
     * The `get_payment_processors_post` hook handler.
     *
     * Actions performed:
     *     - Adds specific attributes to some payment processor for categorization.
     *
     * @param string                            $lang_code  Language code.
     * @param array<array<string, string|bool>> $processors Payment processors.
     *
     * @see \fn_get_payment_processors()
     *
     * @return void
     */
    public function onGetPaymentProcessorsPost($lang_code, array &$processors)
    {
        foreach ($processors as &$processor) {
            if ($processor['addon'] !== 'tinkoff') {
                continue;
            }
            $processor['russian'] = true;
        }
        unset($processor);
    }
}
