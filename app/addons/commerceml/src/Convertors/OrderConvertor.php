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


namespace Tygh\Addons\CommerceML\Convertors;


use Tygh\Addons\CommerceML\Dto\IdDto;
use Tygh\Addons\CommerceML\Dto\OrderDto;
use Tygh\Addons\CommerceML\Dto\OrderProductDto;
use Tygh\Addons\CommerceML\Dto\TranslatableValueDto;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;

/**
 * Class OrderConvertor
 *
 * @package Tygh\Addons\CommerceML\Convertors
 */
class OrderConvertor
{
    /**
     * Convertes CommerceML element product to ProductDTO
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement   $element        Xml element
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
     */
    public function convert(SimpleXmlElement $element, ImportStorage $import_storage)
    {
        $order = new OrderDto();

        $order->id = IdDto::createByLocalId($element->getAsString('number'));
        $order->id->external_id = $element->getAsString('id');

        foreach ($element->get('products/product', []) as $item) {
            if ($item->getAsString('name') === SimpleXmlElement::findAlias('delivery_order')) {
                $order->shipping_cost = $item->getAsFloat('total');
                continue;
            }

            $product = new OrderProductDto();

            $product->id = IdDto::createByExternalId($item->getAsString('id'));
            $product->amount = $item->getAsInt('amount');
            $product->price = $item->getAsFloat('total') / $product->amount;
            $product->total_price = $item->getAsFloat('total');

            $order->products[] = $product;

            $this->convertDiscounts($item, $order, $product);
            $this->convertTaxes($item, $product);

            $order->subtotal += $product->price * $product->amount;
        }

        /**
         * Order status
         *
         * @psalm-suppress PossiblyNullIterator
         */
        foreach ($element->get('value_fields/value_field', []) as $item) {
            $order_status = null;
            $item_name = $item->getAsString('name');
            $item_value = $item->getAsString('value');

            switch ($item_name) {
                case SimpleXmlElement::findAlias('status_order'):
                    $order_status = $item_value;
                    break;
                case SimpleXmlElement::findAlias('shipment_date_1c'):
                    if ($this->dateIsValid($item_value)) {
                        $order_status = $import_storage->getSetting('orders_importer.shipment_date_status');
                    }
                    break;
                case SimpleXmlElement::findAlias('payment_date_1c'):
                    if ($this->dateIsValid($item_value)) {
                        $order_status = $import_storage->getSetting('orders_importer.payment_date_status');
                    }
                    break;
            }

            if (empty($order_status)) {
                continue;
            }

            $order->status = TranslatableValueDto::create($order_status);
        }

        if ($element->has('date')) {
            $date_time = $element->getAsString('date');

            if ($element->has('time')) {
                $date_time .= ' ' . $element->getAsString('time');
            }

            $order->updated_at = strtotime($date_time);
        }

        /**
         * Executes after CommerceML element converted to order DTO
         * Allows to modify or extend order DTO
         *
         * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement   $element        Xml element
         * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
         * @param \Tygh\Addons\CommerceML\Dto\OrderDto           $order          Order DTO
         */
        fn_set_hook('commerceml_order_convertor_convert', $element, $import_storage, $order);

        $import_storage->saveEntities([$order]);
    }

    /**
     * Converts order discounts
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement $element Xml element
     * @param \Tygh\Addons\CommerceML\Dto\OrderDto         $order   Order Dto
     * @param OrderProductDto                              $product Order product Dto
     */
    private function convertDiscounts(SimpleXmlElement $element, OrderDto $order, OrderProductDto $product)
    {
        /**
         * @psalm-suppress PossiblyNullIterator
         */
        foreach ($element->get('discounts/discount', []) as $item) {
            $product_discount = $item->getAsFloat('total');
            $order->subtotal_discount += $product_discount;

            if (!$item->getAsBool('in_total')) { // If discount included in product price
                continue;
            }

            /**
             * @psalm-suppress PossiblyNullOperand
             */
            $product->price += ($product_discount / $product->amount);
        }
    }

    /**
     * Converts order taxes
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement $element Xml element
     * @param OrderProductDto                              $product Order product Dto
     *
     * @return void
     */
    private function convertTaxes(SimpleXmlElement $element, OrderProductDto $product)
    {
        /**
         * @psalm-suppress PossiblyNullIterator
         */
        foreach ($element->get('taxes/tax', []) as $item) {
            if ($item->getAsBool('in_total')) { // If taxes included in product price
                continue;
            }

            $tax_value = $item->getAsFloat('total');

            /**
             * @psalm-suppress PossiblyNullOperand
             */
            $product->price += ($tax_value / $product->amount);
        }
    }

    /**
     * Validates date
     *
     * @param string $date Date
     *
     * @return bool
     */
    private function dateIsValid($date)
    {
        $date_info = date_parse($date);
        return !empty($date_info['year']);
    }
}
