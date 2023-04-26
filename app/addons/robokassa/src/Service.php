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


namespace Tygh\Addons\Robokassa;

use Tygh;

/**
 * Implements methods for working with a robokassa payments
 *
 * @package Tygh\Addons\ProductVariations
 */
class Service
{
    /**
     * Gets full prepayment receipt by order_info for Robokassa service
     *
     * @param array<string|int> $order_info Order information
     *
     * @return array<array<string, string|int>>|false Returns an array with receipt data or false in case of an error
     */
    public static function getReceipt($order_info)
    {
        /** @var \Tygh\Addons\RusTaxes\ReceiptFactory $receipt_factory */
        $receipt_factory = Tygh::$app['addons.rus_taxes.receipt_factory'];
        $receipt = $receipt_factory->createReceiptFromOrder($order_info, CART_PRIMARY_CURRENCY);
        $receipt_result = [];

        if ($receipt) {
            foreach ($receipt->getItems() as $item) {
                $receipt_result['items'][] = [
                    'name'     => self::truncateReceiptItemName($item->getName()),
                    'quantity' => $item->getQuantity(),
                    'sum'      => $item->getTotal(),
                    'payment_method' => 'full_prepayment',
                    'payment_object' => 'commodity',
                    'tax'      => $item->getTaxType(),
                ];
            }
            fn_update_order_payment_info((int) $order_info['order_id'], ['robokassa.prepayment_receipt_created' => __('yes')]);
            return $receipt_result;
        }

        return false;
    }

    /**
     * Gets full payment receipt for Robokassa service
     *
     * @param array<array<string|int>> $processor_data Information about selected payment processor
     * @param array<string|int>        $order_info     Order information
     * @param array<int>               $params         Additional parameters
     *
     * @return array<array<string, string|int>> Returns full payment receipt according to https://docs.robokassa.ru/#7772
     */
    public static function getFullPaymentReceipt(array $processor_data, array $order_info, array $params = [])
    {
        /** @var \Tygh\Addons\RusTaxes\ReceiptFactory $receipt_factory */
        $receipt_factory = Tygh::$app['addons.rus_taxes.receipt_factory'];
        $receipt = $receipt_factory->createReceiptFromOrder($order_info, CART_PRIMARY_CURRENCY);
        if ($receipt) {
            $receipt_result = [];
            foreach ($receipt->getItems() as $item) {
                $receipt_result['items'][] = [
                    'name'           => self::truncateReceiptItemName($item->getName()),
                    'quantity'       => $item->getQuantity(),
                    'sum'            => $item->getTotal(),
                    'payment_method' => 'full_payment',
                    'payment_object' => 'commodity',
                    'tax'            => $item->getTaxType(),
                ];
                if (isset($receipt_result['vats'])) {
                    foreach ($receipt_result['vats'] as &$vat) {
                        if ($vat['type'] === $item->getTaxType()) {
                            $vat['sum'] += $item->getTaxSum();
                            continue 2;
                        }
                    }
                    unset($vat);
                    $receipt_result['vats'][] = [
                        'type' => $item->getTaxType(),
                        'sum'  => $item->getTaxSum(),
                    ];
                } else {
                    $receipt_result['vats'][] = [
                        'type' => $item->getTaxType(),
                        'sum'  => $item->getTaxSum(),
                    ];
                }
            }

            $result = array_merge($receipt_result, [
                'merchantId' => $processor_data['processor_params']['merchantid'],
                'id'         => isset($params['id'])
                    ? $params['id']
                    : time(),
                'originId'   => $order_info['order_id'],
                'operation'  => 'sell',
                'URL'        => fn_url('', 'C'),
                'total'      => number_format($order_info['total'], 2, '.', ''),
                'client'     => [
                    'email' => $receipt->getEmail(),
                    'phone' => $receipt->getPhone(),
                ],
                'payments'   => [
                    [
                        /**
                         * @var int
                         *
                         * @see https://docs.robokassa.ru/#7772
                         */
                        'type' => 2,
                        'sum'  => number_format($order_info['total'], 2, '.', ''),
                    ],
                ],
            ]);
        } else {
            $result = [];
        }

        return $result;
    }

    /**
     * Encodes full payment receipt according to Robokassa API docs https://docs.robokassa.ru/#7782
     *
     * @param array<array<string, string|int>> $receipt        Receipt structure
     * @param array<array<string>>             $processor_data Array of payment processor params
     *
     * @return string Encoded form of full payment receipt
     */
    public static function encodeReceipt(array $receipt, array $processor_data)
    {
        $json = json_encode($receipt, JSON_UNESCAPED_SLASHES);
        $data = strtr(base64_encode($json), [
            '+' => '-',
            '/' => '_',
        ]);
        $signature = rtrim($data, '=');
        $post_signature = $signature . $processor_data['processor_params']['password1'];
        $encoding_signature = md5($post_signature);

        return rtrim($signature . '.' . base64_encode($encoding_signature), '=');
    }

    /**
     * Formats a string with the name for tax data by deleting error-prone symbols
     *
     * @param string $name   Receipt item name
     * @param int    $length Length name
     * @param string $suffix String to append to the end of truncated string
     *
     * @return string Truncates item name
     */
    public static function truncateReceiptItemName($name, $length = 64, $suffix = '...')
    {
        $name = preg_replace('/[^0-9a-zA-Zа-яА-Я-,. ]/ui', '', $name);

        if (function_exists('mb_strlen') && mb_strlen($name, 'UTF-8') > $length) {
            $length -= mb_strlen($suffix);
            return rtrim(mb_substr($name, 0, $length, 'UTF-8')) . $suffix;
        }

        return $name;
    }
}
