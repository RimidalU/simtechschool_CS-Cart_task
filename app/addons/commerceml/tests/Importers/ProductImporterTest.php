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


namespace Tygh\Addons\CommerceML\Tests\Importers;


use Tygh\Addons\CommerceML\Dto\CategoryDto;
use Tygh\Addons\CommerceML\Dto\CurrencyDto;
use Tygh\Addons\CommerceML\Dto\IdDto;
use Tygh\Addons\CommerceML\Dto\PriceTypeDto;
use Tygh\Addons\CommerceML\Dto\PriceValueDto;
use Tygh\Addons\CommerceML\Dto\ProductDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureSelectedValueDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureSelectedValueDtoCollection;
use Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto;
use Tygh\Addons\CommerceML\Dto\PropertyDto;
use Tygh\Addons\CommerceML\Dto\TaxDto;
use Tygh\Addons\CommerceML\Dto\TranslatableValueDto;
use Tygh\Addons\CommerceML\Importers\CategoryImporter;
use Tygh\Addons\CommerceML\Importers\ProductFeatureImporter;
use Tygh\Addons\CommerceML\Importers\ProductImporter;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Tests\Unit\StorageBasedTestCase;
use Tygh\Enum\ObjectStatuses;

class ProductImporterTest extends StorageBasedTestCase
{
    public $products = [];

    public $entities = [];

    public $entities_map = [];

    protected function setUp()
    {
        $this->requireMockFunction('__');
        $this->requireMockFunction('fn_set_hook');
        $this->requireMockFunction('fn_get_product_feature_type_by_feature_id');

        parent::setUp();
    }

    public function testImport()
    {
        $import_storage = $this->getImportStorage();
        $product_storage = $this->getProductStorage();

        $this->loadEntities($import_storage);

        $product1 = new ProductDto();
        $product1->is_creatable = true;
        $product1->id = new IdDto('0d9efc08-d695-11e8-8324-b010418127da');
        $product1->product_code = 'P005';
        $product1->status = ObjectStatuses::DISABLED;
        $product1->name = new TranslatableValueDto('Тайтсы GIVOVA SLIM', ['en' => 'Тайтсы GIVOVA SLIM EN']);
        $product1->description = new TranslatableValueDto('<span>Описание</span>', ['en' => 'description']);
        $product1->categories[] = new IdDto('16b791bb-816f-11e9-9c72-e0d55e229524');
        $product1->properties->add(PropertyDto::create('local_id', 100));
        $product1->properties->add(PropertyDto::create('product_type', 'P'));

        $selected_feature_values_coll1 = new ProductFeatureSelectedValueDtoCollection();
        $selected_feature_value1 = ProductFeatureSelectedValueDto::create(
            '9d67a9cf-baf9-11e6-853e-60a44c5c87dc',
            IdDto::createByExternalId('9d67a9cd-baf9-11e6-853e-60a44c5c87dc#9d67a9cf-baf9-11e6-853e-60a44c5c87dc'));
        $selected_feature_values_coll1->add($selected_feature_value1);

        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('9d67a9cd-baf9-11e6-853e-60a44c5c87dc'),
            $selected_feature_values_coll1
        ));

        $selected_feature_values_coll2 = new ProductFeatureSelectedValueDtoCollection();
        $selected_feature_value2 = ProductFeatureSelectedValueDto::create(
            '9d67a9b8-baf9-11e6-853e-60a44c5c87dc',
            IdDto::createByExternalId('9d67a9b6-baf9-11e6-853e-60a44c5c87dc#9d67a9b8-baf9-11e6-853e-60a44c5c87dc')
        );
        $selected_feature_values_coll2->add($selected_feature_value2);

        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('9d67a9b6-baf9-11e6-853e-60a44c5c87dc'),
            $selected_feature_values_coll2
        ));

        $selected_feature_values_coll3 = new ProductFeatureSelectedValueDtoCollection();
        $selected_feature_value3 = ProductFeatureSelectedValueDto::create(
            '10',
            IdDto::createByExternalId('9d67a9c9-baf9-11e6-853e-60a44c5c87dc#10')
        );
        $selected_feature_values_coll3->add($selected_feature_value3);

        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('9d67a9c9-baf9-11e6-853e-60a44c5c87dc'),
            $selected_feature_values_coll3
        ));

        $selected_feature_values_coll4 = new ProductFeatureSelectedValueDtoCollection();
        $selected_feature_value4 = ProductFeatureSelectedValueDto::create(
            '20',
            IdDto::createByExternalId('9d67a9ca-baf9-11e6-853e-60a44c5c87dc#20')
        );
        $selected_feature_values_coll4->add($selected_feature_value4);

        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('9d67a9ca-baf9-11e6-853e-60a44c5c87dc'),
            $selected_feature_values_coll4
        ));

        $selected_feature_values_coll5 = new ProductFeatureSelectedValueDtoCollection();
        $selected_feature_value5 = ProductFeatureSelectedValueDto::create(
            'PR-00000366',
            IdDto::createByExternalId('b9069efd-ca9c-11e7-8576-60a44c5c87dc#PR-00000366')
        );
        $selected_feature_values_coll5->add($selected_feature_value5);

        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('b9069efd-ca9c-11e7-8576-60a44c5c87dc'),
            $selected_feature_values_coll5
        ));

        $selected_feature_values_coll6 = new ProductFeatureSelectedValueDtoCollection();
        $selected_feature_value6 = ProductFeatureSelectedValueDto::create(
            'b9069eff-ca9c-11e7-8576-60a44c5c87dc',
            IdDto::createByExternalId('b9069efe-ca9c-11e7-8576-60a44c5c87dc#b9069eff-ca9c-11e7-8576-60a44c5c87dc')
        );
        $selected_feature_values_coll6->add($selected_feature_value6);
        $selected_feature_value7 = ProductFeatureSelectedValueDto::create(
            'b9069efg-ca9c-11e7-8576-60a44c5c87dc',
            IdDto::createByExternalId('b9069efe-ca9c-11e7-8576-60a44c5c87dc#b9069efg-ca9c-11e7-8576-60a44c5c87dc')
        );
        $selected_feature_values_coll6->add($selected_feature_value7);

        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('b9069efe-ca9c-11e7-8576-60a44c5c87dc'),
            $selected_feature_values_coll6
        ));

        $product1->taxes[] = new IdDto('Без НДС', 10);
        $product1->taxes[] = new IdDto('Без НДС2');
        $product1->prices[] = PriceValueDto::create(IdDto::createByExternalId('1price'), 150);
        $product1->prices[] = PriceValueDto::create(IdDto::createByExternalId('2price'), 2, IdDto::createByExternalId('USD_1'));
        $product1->quantity = 17;

        $importer = new ProductImporter(
            new CategoryImporter($product_storage),
            new ProductFeatureImporter($product_storage),
            $product_storage
        );

        $result = $importer->import($product1, $import_storage);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(100, $result->getData());
        $this->assertEquals(100, $product1->getEntityId()->local_id);

        $this->assertEquals(
            [
                '0d9efc08-d695-11e8-8324-b010418127da'                                      => 100,
                '9d67a9cd-baf9-11e6-853e-60a44c5c87dc#9d67a9cf-baf9-11e6-853e-60a44c5c87dc' => '1',
                '9d67a9cd-baf9-11e6-853e-60a44c5c87dc'                                      => 1,
                '9d67a9b6-baf9-11e6-853e-60a44c5c87dc#9d67a9b8-baf9-11e6-853e-60a44c5c87dc' => '2',
                '9d67a9b6-baf9-11e6-853e-60a44c5c87dc'                                      => 2,
                '9d67a9c9-baf9-11e6-853e-60a44c5c87dc'                                      => 3,
                '9d67a9ca-baf9-11e6-853e-60a44c5c87dc'                                      => 4,
                'b9069efd-ca9c-11e7-8576-60a44c5c87dc'                                      => 5,
                'b9069efe-ca9c-11e7-8576-60a44c5c87dc'                                      => 6,
                '16b791bb-816f-11e9-9c72-e0d55e229524'                                      => 1,
                'b9069efe-ca9c-11e7-8576-60a44c5c87dc#b9069eff-ca9c-11e7-8576-60a44c5c87dc' => '3',
                'b9069efe-ca9c-11e7-8576-60a44c5c87dc#b9069efg-ca9c-11e7-8576-60a44c5c87dc' => '4',
                '1price'                                                                    => 'base_price',
                '2price'                                                                    => 'list_price',
                'USD_1'                                                                     => 'USD',
                'Без НДС2'                                                                  => 11
            ],
            $import_storage->getImportEntityMapRepository()->getIdMap()
        );

        $this->assertEquals(
            [
                100 => [
                    'ru' => [
                        'product'           => 'Тайтсы GIVOVA SLIM',
                        'local_id'          => '100',
                        'product_type'      => 'P',
                        'product_code'      => 'P005',
                        'status'            => 'D',
                        'main_category'     => 1,
                        'amount'            => 17,
                        'category_ids'      => [1],
                        'full_description'  => '<span>Описание</span>',
                        'product_features'  => [
                            1 => 1,
                            2 => 2,
                            3 => '10',
                            4 => '20',
                            5 => 'PR-00000366',
                            6 => [3, 4]
                        ],
                        'product_features_params' => [
                            'commerceml_update_product_feature_categories' => true,
                            'commerceml_import'                            => true
                        ],
                        'price'             => 150.0,
                        'list_price'        => 2 * 69.9,
                        'prices'            => [],
                        'source_import_key' => 'import_key',
                        'tax_ids'           => [10, 11],
                    ],
                    'en' => [
                        'product' => 'Тайтсы GIVOVA SLIM EN',
                        'full_description' => 'description'
                    ]
                ],
            ],
            $product_storage->products
        );

        $this->assertEmpty($import_storage->getImportEntityRepository()->storage);
    }

    public function loadEntities(ImportStorage $import_storage)
    {
        $category1 = new CategoryDto();
        $category1->id = IdDto::createByExternalId('16b791bb-816f-11e9-9c72-e0d55e229524');
        $category1->properties->add(PropertyDto::create('category_id', 1));

        $product_feature1 = new ProductFeatureDto();
        $product_feature1->id = new IdDto('9d67a9cd-baf9-11e6-853e-60a44c5c87dc');
        $product_feature1->properties->add(PropertyDto::create('feature_id', 1));

        $variant1 = ProductFeatureVariantDto::create(IdDto::createByExternalId('9d67a9cd-baf9-11e6-853e-60a44c5c87dc#9d67a9cf-baf9-11e6-853e-60a44c5c87dc'), TranslatableValueDto::create('1'));
        $variant1->properties->add(PropertyDto::create('variant_id', 1));
        $product_feature1->variants[] = $variant1;

        $product_feature2 = new ProductFeatureDto();
        $product_feature2->id = new IdDto('9d67a9b6-baf9-11e6-853e-60a44c5c87dc');
        $product_feature2->properties->add(PropertyDto::create('feature_id', 2));

        $variant2 = ProductFeatureVariantDto::create(IdDto::createByExternalId('9d67a9b6-baf9-11e6-853e-60a44c5c87dc#9d67a9b8-baf9-11e6-853e-60a44c5c87dc'), TranslatableValueDto::create('2'));
        $variant2->properties->add(PropertyDto::create('variant_id', 2));
        $product_feature2->variants[] = $variant2;

        $product_feature3 = new ProductFeatureDto();
        $product_feature3->id = new IdDto('9d67a9c9-baf9-11e6-853e-60a44c5c87dc');
        $product_feature3->properties->add(PropertyDto::create('feature_id', 3));

        $product_feature4 = new ProductFeatureDto();
        $product_feature4->id = new IdDto('9d67a9ca-baf9-11e6-853e-60a44c5c87dc');
        $product_feature4->properties->add(PropertyDto::create('feature_id', 4));

        $product_feature5 = new ProductFeatureDto();
        $product_feature5->id = new IdDto('b9069efd-ca9c-11e7-8576-60a44c5c87dc');
        $product_feature5->properties->add(PropertyDto::create('feature_id', 5));

        $product_feature6 = new ProductFeatureDto();
        $product_feature6->id = new IdDto('b9069efe-ca9c-11e7-8576-60a44c5c87dc');
        $product_feature6->properties->add(PropertyDto::create('feature_id', 6));

        $variant3 = ProductFeatureVariantDto::create(IdDto::createByExternalId('b9069efe-ca9c-11e7-8576-60a44c5c87dc#b9069eff-ca9c-11e7-8576-60a44c5c87dc'), TranslatableValueDto::create('3'));
        $variant3->properties->add(PropertyDto::create('variant_id', 3));
        $product_feature6->variants[] = $variant3;

        $variant4 = ProductFeatureVariantDto::create(IdDto::createByExternalId('b9069efe-ca9c-11e7-8576-60a44c5c87dc#b9069efg-ca9c-11e7-8576-60a44c5c87dc'), TranslatableValueDto::create('3'));
        $variant4->properties->add(PropertyDto::create('variant_id', 4));
        $product_feature6->variants[] = $variant4;

        $price_type = new PriceTypeDto();
        $price_type->id = new IdDto('1price', 'base_price');
        $price_type->properties->add(PropertyDto::create('local_id', 'base_price'));

        $price_type2 = new PriceTypeDto();
        $price_type2->id = new IdDto('2price', 'list_price');
        $price_type2->properties->add(PropertyDto::create('local_id', 'list_price'));

        $currency = new CurrencyDto();
        $currency->id = new IdDto('USD_1', 'USD');
        $currency->properties->add(PropertyDto::create('local_id', 'USD'));

        $tax1 = TaxDto::create(new IdDto('Без НДС2', 11), 'Без НДС2');

        $import_storage->saveEntities([
            $category1,
            $product_feature1,
            $variant1,
            $product_feature2,
            $variant2,
            $product_feature3,
            $product_feature4,
            $product_feature5,
            $product_feature6,
            $variant3,
            $variant4
        ]);

        $import_storage->mapEntityId($price_type);
        $import_storage->mapEntityId($price_type2);
        $import_storage->mapEntityId($currency);
        $import_storage->mapEntityId($tax1);
    }
}
