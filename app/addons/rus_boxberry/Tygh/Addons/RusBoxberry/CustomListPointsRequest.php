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

use Boxberry\Requests\ListPointsRequest;

/* @codingStandardsIgnoreStart */
class CustomListPointsRequest extends ListPointsRequest
{
    /** @var string[] $propNameMap */
    protected $propNameMap = [
        'CityCode' => 'CityCode',
        'prepaid'  => 'prepaid',
        'CountryCode' => 'CountryCode',
    ];

    /** @var string $countryCode */
    protected $countryCode = '';

    /**
     * @param string $country_code Country code.
     *
     * @return void
     */
    public function setCountryCode($country_code)
    {
        $country_codes = [
            'RU' => '643',
            'KZ' => '398',
            'BY' => '112',
            'KG' => '417',
            'AM' => '051',
        ];
        //phpcs:ignore
        $this->countryCode = isset($country_codes[$country_code]) ? $country_codes[$country_code] : '';
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    public function getClassName()
    {
        return str_replace('Custom', '', parent::getClassName());
    }
}
