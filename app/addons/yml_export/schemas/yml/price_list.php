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

return [
    'default' => [
        'general'          => [
            'urls'                 => [
                'type'        => 'template',
                'template'    => 'addons/yml_export/views/yml/components/urls.tpl',
                'update_only' => true,
            ],
            'selected_storefront'  => [
                'type'     => 'template',
                'template' => 'addons/yml_export/views/yml/components/storefront.tpl',
                'required' => true,
            ],
            'enable_authorization' => [
                'type'    => 'checkbox',
                'default' => 'Y',
            ],
            'access_key'           => [
                'type'     => 'template',
                'template' => 'addons/yml_export/views/yml/components/access_key.tpl',
            ],
            'name_price_list'      => [
                'type'     => 'input',
                'default'  => 'Яндекс.Маркет',
                'required' => true,
            ],
            'shop_name'            => [
                'type'     => 'input',
                'default'  => '',
                'required' => true,
                'tooltip'  => __('yml_export.tooltip_shop_name'),
            ],
            'export_encoding'      => [
                'type'     => 'selectbox',
                'variants' => [
                    'utf-8'        => 'yml_export.utf8',
                    'windows-1251' => 'yml_export.windows1251',
                ],
                'default'  => 'utf-8',
            ],
            'enable_cpa'           => [
                'type'     => 'selectbox',
                'variants' => [
                    'Y' => 'yml2_true',
                    'N' => 'yml2_false',
                ],
                'default'  => 'yes',
                'tooltip'  => __('yml_export.tooltip_enable_cpa'),
            ],
            'detailed_generation'  => [
                'type'    => 'checkbox',
                'default' => 'Y',
            ],
        ],
        'export_data'      => [
            'utm_link'                 => [
                'type'        => 'input',
                'default'     => '',
                'class'       => 'input-large',
                'placeholder' => 'utm_source=yandex_market&utm_medium=cpc&utm_content={product_code}',
            ],
            'export_stock'             => [
                'type'    => 'checkbox',
                'default' => 'N',
            ],
            'export_null_price'        => [
                'type'    => 'checkbox',
                'default' => 'N',
            ],
            'export_shared_products'   => [
                'type'     => 'checkbox',
                'default'  => 'N',
                'disabled' => '',
            ],
            'minimal_discount'         => [
                'type'    => 'input',
                'default' => '5',
            ],
            'export_min_product_price' => [
                'type'    => 'input',
                'default' => '',
            ],
            'export_max_product_price' => [
                'type'    => 'input',
                'default' => '',
            ],
        ],
        'export_fields'    => [
            'weight'           => [
                'type'    => 'checkbox',
                'default' => 'N',
            ],
            'dimensions'       => [
                'type'    => 'checkbox',
                'default' => 'N',
            ],
            'not_downloadable' => [
                'type'    => 'checkbox',
                'default' => 'N',
            ],
        ],
        'images'           => [
            'image_type'             => [
                'type'     => 'selectbox',
                'variants' => [
                    'thumbnail' => 'yml_export.thumbnail',
                    'detailed'  => 'yml_export.detailed',
                ],
                'default'  => 'detailed',
            ],
            'thumbnail_width'        => [
                'type'    => 'input',
                'default' => '280',
                'min'     => '250',
            ],
            'thumbnail_height'       => [
                'type'    => 'input',
                'default' => '280',
                'min'     => '250',
            ],
            'check_watermarks_addon' => [
                'type'     => 'template',
                'template' => 'addons/yml_export/views/yml/components/check_watermarks_addon.tpl',
            ],
        ],
        'delivery_options' => [
            'store'    => [
                'type'     => 'selectbox',
                'variants' => [
                    'Y' => 'yes',
                    'N' => 'no',
                    ''  => 'none',
                ],
                'default'  => 'N',
            ],
            'pickup'   => [
                'type'     => 'selectbox',
                'variants' => [
                    'Y' => 'yes',
                    'N' => 'no',
                    ''  => 'none',
                ],
                'default'  => 'N',
            ],
            'delivery' => [
                'type'     => 'selectbox',
                'variants' => [
                    'Y' => 'yes',
                    'N' => 'no',
                    ''  => 'none',
                ],
                'default'  => 'Y',
            ],
            'options'  => [
                'type'      => 'template',
                'template'  => 'addons/yml_export/common/yml_delivery_options.tpl',
                'name_data' => 'delivery_options',
            ],
        ],
        'categories'       => [
            'export_hidden_categories'       => [
                'type'    => 'checkbox',
                'default' => 'N',
            ],
            'exclude_categories_not_logging' => [
                'type'    => 'checkbox',
                'default' => 'Y',
            ],
            'categories'                     => [
                'type'      => 'template',
                'template'  => 'addons/yml_export/views/yml/components/categories.tpl',
                'name_data' => 'company_id',
            ],
        ],
    ],
];
