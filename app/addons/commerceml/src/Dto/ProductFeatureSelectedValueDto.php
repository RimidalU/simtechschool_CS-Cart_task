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

namespace Tygh\Addons\CommerceML\Dto;

/**
 * Class ProductFeatureSelectedValueDto
 *
 * @package Tygh\Addons\CommerceML\Dto
 */
class ProductFeatureSelectedValueDto
{
    /**
     * @var string|null
     */
    public $value;

    /**
     * @var \Tygh\Addons\CommerceML\Dto\IdDto|null
     */
    public $value_id;

    /**
     * ProductFeatureSelectedValueDto constructor.
     *
     * @param string|null                            $value    Feature value
     * @param \Tygh\Addons\CommerceML\Dto\IdDto|null $value_id Feature value variant id
     */
    public function __construct($value = null, IdDto $value_id = null)
    {
        $this->value = $value;
        $this->value_id = $value_id;
    }

    /**
     * @param string|null                            $value    Feature value
     * @param \Tygh\Addons\CommerceML\Dto\IdDto|null $value_id Feature value variant id
     *
     * @return \Tygh\Addons\CommerceML\Dto\ProductFeatureSelectedValueDto
     */
    public static function create($value = null, IdDto $value_id = null)
    {
        return new self($value, $value_id);
    }
}
