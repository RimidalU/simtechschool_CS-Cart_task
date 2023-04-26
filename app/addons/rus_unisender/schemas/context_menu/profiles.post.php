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

use Tygh\ContextMenu\Items\DividerItem;

defined('BOOTSTRAP') or die('Access denied!');

/** @var array $schema */
$schema['items']['actions']['items']['actions_divider3'] = [
    'type'     => DividerItem::class,
    'position' => 60,
];

$schema['items']['actions']['items']['add_selected_to_unisender'] = [
    'name'     => ['template' => 'addons.rus_unisender.add_selected_to_unisender'],
    'dispatch' => 'unisender.add_selected',
    'data'     => [
        'action_class' => 'cm-confirm',
    ],
    'position' => 70,
];

return $schema;
