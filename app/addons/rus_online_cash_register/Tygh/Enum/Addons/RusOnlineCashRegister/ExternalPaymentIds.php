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

namespace Tygh\Enum\Addons\RusOnlineCashRegister;

use Tygh\Addons\RusOnlineCashRegister\Receipt\Receipt;

/**
 * Class ExternalPaymentIds realize options for payment type into ATOL API.
 *
 * @see https://online.atol.ru/files/API_FFD_1-0-5.pdf
 *
 * @package Tygh\Enum\Addons\RusOnlineCashRegister
 */
class ExternalPaymentIds
{
    const CASHLESS = 1;

    const PREPAYMENT = 2;

    /**
     * Returns external payment type id, accordingly to ATOL API.
     * If payment method - prepayment, then customer pays cashless. If payment method - payment, then customer pays from his prepayment.
     *
     * @param string $payment_method_type Payment method type.
     *
     * @return int
     */
    public static function getPaymentId($payment_method_type)
    {
        switch ($payment_method_type) {
            case Receipt::PAYMENT_METHOD_FULL_PREPAYMENT:
                return self::CASHLESS;
            case Receipt::PAYMENT_METHOD_FULL_PAYMENT:
            default:
                return self::PREPAYMENT;
        }
    }
}
