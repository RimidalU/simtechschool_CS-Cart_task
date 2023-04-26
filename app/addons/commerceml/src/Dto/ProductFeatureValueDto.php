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
 * Class ProductFeatureValueDto
 *
 * @package Tygh\Addons\CommerceML\Dto
 */
class ProductFeatureValueDto
{
    /**
     * @var \Tygh\Addons\CommerceML\Dto\IdDto
     */
    public $feature_id;

    /**
     * @var \Tygh\Addons\CommerceML\Dto\ProductFeatureSelectedValueDtoCollection
     */
    public $selected_values;

    /**
     * ProductFeatureValueDto constructor.
     *
     * @param \Tygh\Addons\CommerceML\Dto\IdDto                                    $feature_id      Freature ID
     * @param \Tygh\Addons\CommerceML\Dto\ProductFeatureSelectedValueDtoCollection $selected_values Selected feature values
     */
    public function __construct(IdDto $feature_id, ProductFeatureSelectedValueDtoCollection $selected_values)
    {
        $this->feature_id = $feature_id;
        $this->selected_values = $selected_values;
    }

    /**
     * @param \Tygh\Addons\CommerceML\Dto\IdDto                                    $feature_id      Freature ID
     * @param \Tygh\Addons\CommerceML\Dto\ProductFeatureSelectedValueDtoCollection $selected_values Selected feature values
     *
     * @return \Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto
     */
    public static function create(IdDto $feature_id, ProductFeatureSelectedValueDtoCollection $selected_values)
    {
        return new self($feature_id, $selected_values);
    }
}
