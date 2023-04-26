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

namespace Tygh\Addons\Robokassa\Factories;

use Tygh\Addons\Robokassa\Payments\RobokassaSplit;
use Tygh\Database\Connection;

/**
 * @package Tygh\Addons\Robokassa\Factories
 */
class ProcessorFactory
{
    /** @var \Tygh\Database\Connection */
    protected $db;

    /** @var \Tygh\Addons\StripeConnect\PriceFormatter */
    protected $price_formatter;

    /**
     * ProcessorFactory constructor.
     *
     * @param \Tygh\Database\Connection $db Database connection
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Constructs payment method processor with default components by the payment method ID.
     *
     * @param int                        $payment_id       Payment method ID
     * @param array<string, string>|null $processor_params Payment method configuration
     *
     * @return \Tygh\Addons\Robokassa\Payments\RobokassaSplit|void
     */
    public function getByPaymentId($payment_id, $processor_params = null)
    {
        if (!$payment_id) {
            return;
        }

        $processor_script = $this->db->getField(
            'SELECT ?:payment_processors.processor_script'
            . ' FROM ?:payments'
            . ' LEFT JOIN ?:payment_processors'
                . ' ON ?:payments.processor_id = ?:payment_processors.processor_id'
            . ' WHERE payment_id = ?i',
            $payment_id
        );

        switch ($processor_script) {
            case RobokassaSplit::PROCESSOR_SCRIPT:
                return new RobokassaSplit(
                    $payment_id,
                    $this->db,
                    $processor_params
                );
            default:
                return;
        }
    }
}
