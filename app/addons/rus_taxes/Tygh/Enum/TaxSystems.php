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

namespace Tygh\Enum;

use ReflectionClass;

defined('BOOTSTRAP') or die('Access denied');

class TaxSystems
{
    const OSN = 'osn';
    const USN_INCOME = 'usn_income';
    const USN_INCOME_OUTCOME = 'usn_income_outcome';
    const PATENT = 'patent';
    const ENVD = 'envd';
    const ESN = 'esn';

    /**
     * Returns all tax systems.
     *
     * @return array<string, string>
     */
    public static function getAllValues()
    {
        $reflect = new ReflectionClass(self::class);
        return $reflect->getConstants();
    }
}
