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

namespace Tygh\Addons\RusBoxberry;

use Boxberry\Requests\ListCitiesRequest;

/* @codingStandardsIgnoreStart */
class CustomListCitiesRequest extends ListCitiesRequest
{
    /** @var string[] $propNameMap */
    protected $propNameMap = [
        'CountryCode' => 'CountryCode',
    ];

    /** @var string $countryCode */
    protected $countryCode = '';

    /**
     * Sets countryCode int value.
     *
     * @param string $countryCode Country code.
     *
     * @return void
     */
    public function setCountryCode($countryCode)
    {
        $country_codes = [
            'RU' => '643',
            'KZ' => '398',
            'BY' => '112',
            'KG' => '417',
            'AM' => '051',
        ];
        $this->countryCode = isset($country_codes[$countryCode]) ? $country_codes[$countryCode] : '';
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return str_replace('Custom', '', parent::getClassName());
    }
}
