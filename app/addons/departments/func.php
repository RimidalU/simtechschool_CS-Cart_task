<?php

/***************************************************************************
 *                                                                          *
 *   (c) 2023 Uladzimir Stankevich                                          *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

use Tygh\Registry;
use Tygh\Languages\Languages;


function fn_get_departments_data($department_id = 0, $lang_code = CART_LANGUAGE)
{
    $department = [];

    if (!empty($department_id)) {
        list($departments) = fn_get_departments([
            'department_id' =>  $department_id
        ], 1, $lang_code);
        if (!empty($departments)) {
            $department = reset($departments);
            $department['employee_ids'] = fn_department_get_links($department['department_id']);
        }
    }

    return $department;
};

function fn_get_departments($params = [], $items_per_page = 0, $lang_code = CART_LANGUAGE)
{

    // Set default values to input params
    $default_params = array(
        'page' => 1,
        'items_per_page' => $items_per_page,
    );
    $currentUser = Registry::get('user_info')['user_id'];
    // fn_print_die($currentUser);

    $params = array_merge($default_params, $params);

    if (AREA == 'C') {
        $params['status'] = 'A';
    }

    $sortings = array(
        'timestamp' => '?:departments.timestamp',
        'position' => '?:departments.position',
        'departments' => '?:department_descriptions.department',
        'status' => '?:departments.status',
        'owner_id' => '?:departments.owner_id',
    );

    $condition = $limit = $join = '';

    if (!empty($params['limit'])) {
        $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
    }

    $sorting = db_sort($params, $sortings, 'name', 'asc');

    if (!empty($params['item_ids'])) {
        $condition .= db_quote(' AND ?:departments.department_id IN (?n)', explode(',', $params['item_ids']));
    }

    if (!empty($params['department_id'])) {
        $condition .= db_quote(' AND ?:departments.department_id = ?i', $params['department_id']);
    }

    if (!empty($params['user_id'])) {
        $condition .= db_quote(' AND ?:departments.owner_id = ?i', $params['user_id']);
    }

    if (!empty($params['status'])) {
        $condition .= db_quote(' AND ?:departments.status = ?s', $params['status']);
    }

    if (!empty($params['departments'])) {
        $condition .= db_quote(' AND ?:department_descriptions.department LIKE ?l', '%' . trim($params['departments']) . '%');
    }

    $fields = array(
        '?:departments.department_id',
        '?:departments.status',
        '?:departments.timestamp',
        '?:departments.position',
        '?:departments.chief_id',
        '?:departments.owner_id',
        '?:department_descriptions.department',
        '?:department_descriptions.description',
    );

    $join .= db_quote(' LEFT JOIN ?:department_descriptions ON ?:department_descriptions.department_id = ?:departments.department_id AND ?:department_descriptions.lang_code = ?s', $lang_code);

    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:departments $join WHERE 1 $condition");
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
    }

    $departments = db_get_hash_array(
        "SELECT ?p FROM ?:departments " .
            $join .
            "WHERE 1 ?p ?p ?p",
        'department_id',
        implode(', ', $fields),
        $condition,
        $sorting,
        $limit
    );

    $department_image_ids = array_keys($departments);
    $images = fn_get_image_pairs($department_image_ids, 'department', 'M', true, false, $lang_code);

    foreach ($departments as $department_id => $department) {
        $departments[$department_id]['main_pair'] = !empty($images[$department_id]) ? reset($images[$department_id]) : array();
        if (!empty($department['chief_id'])) {
            $chief_info = fn_get_user_short_info($department['chief_id']);
            $departments[$department_id]['chief_name'] = $chief_info['firstname'] . " " .  $chief_info['lastname'];
        }
    }
    return array($departments, $params);
};

function fn_update_department($data, $department_id, $lang_code = DESCR_SL)
{
    if (isset($data['timestamp'])) {
        $data['timestamp'] = fn_parse_date($data['timestamp']);
    }

    if (!empty($department_id)) {
        db_query("UPDATE ?:departments SET ?u WHERE department_id = ?i", $data, $department_id);
        db_query("UPDATE ?:department_descriptions SET ?u WHERE department_id = ?i AND lang_code = ?s", $data, $department_id, $lang_code);
    } else {
        $department_id = $data['department_id'] = db_replace_into('departments', $data);

        foreach (Languages::getAll() as $data['lang_code'] => $v) {
            db_query("REPLACE INTO ?:department_descriptions ?e", $data);
        }
    }
    if (!empty($department_id)) {
        fn_attach_image_pairs('department', 'department', $department_id, $lang_code);
    }

    $employee_ids = !empty($data['employee_ids']) ? $data['employee_ids'] : [];

    fn_department_remove_links($department_id);
    fn_department_add_links($department_id, $employee_ids);

    return $department_id;
}

function fn_delete_department($department_id)
{
    if (!empty($department_id)) {
        db_query("DELETE FROM ?:departments WHERE department_id = ?i", $department_id);
        db_query("DELETE FROM ?:department_descriptions WHERE department_id = ?i", $department_id);
        fn_department_remove_links($department_id);
    }
};

function fn_department_remove_links($department_id)
{
    if (!empty($department_id)) {
        db_query("DELETE FROM ?:department_links WHERE department_id = ?i", $department_id);
    }
};

function fn_department_add_links($department_id, $employee_ids)
{

    if (!empty($employee_ids)) {
        foreach (explode(",", $employee_ids) as $employee_id) {

            db_query("REPLACE INTO ?:department_links ?e", [
                "department_id" => $department_id,
                "employee_id" => $employee_id
            ]);
        }
    }
};

function fn_department_get_links($department_id)
{
    return !empty($department_id) ? db_get_fields("SELECT employee_id FROM `?:department_links` WHERE `department_id` = ?i", $department_id) : [];
};
