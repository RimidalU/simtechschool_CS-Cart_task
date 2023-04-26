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

class AgentTypes
{
    const BANKING_PAYMENT_AGENT = 'banking_payment_agent';
    const BANKING_PAYMENT_SUBAGENT = 'banking_payment_subagent';
    const PAYMENT_AGENT = 'payment_agent';
    const PAYMENT_SUBAGENT = 'payment_subagent';
    const ATTORNEY = 'attorney';
    const COMMISSIONER = 'commissioner';
    const AGENT = 'agent';

    /**
     * Convert value of agent type to specific version of it for current object.
     *
     * @param string $original_agent_type_value Original value of agent type
     *
     * @return string
     */
    public static function getValue($original_agent_type_value)
    {
        return (string) constant('self::' . strtoupper($original_agent_type_value));
    }

    /**
     * Return all available agent type variants for specific object.
     *
     * @return array<string, string>
     */
    public static function getAllValues()
    {
        $reflect = new ReflectionClass(self::class);
        return $reflect->getConstants();
    }
}
