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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $suffix = '';

    // Define trusted variables that shouldn't be stripped
    fn_trusted_vars(
        'departments_data'
    );
    if ($mode == 'update_department') {
        $department_id = !empty($_REQUEST['department_id']) ? $_REQUEST['department_id'] : 0;
        $data = !empty($_REQUEST['departments_data']) ? $_REQUEST['departments_data'] : [];
        $department_id = fn_update_department($data, $department_id, DESCR_SL);

        if (!empty($department_id)) {
            $suffix = ".update_department?department_id={$department_id}";
        } else {
            $suffix = ".add_department";
        }
    } elseif ($mode == 'update_selected_department') {
        if (!empty($_REQUEST['departments_data'])) {
            foreach ($_REQUEST['departments_data'] as $department_id => $data) {
                fn_update_department($data, $department_id, DESCR_SL);
            }
        }
        $suffix = ".manage_departments";
    } elseif ($mode == 'delete_department') {
        $department_id = !empty($_REQUEST['department_id']) ? $_REQUEST['department_id'] : 0;

        fn_delete_department($department_id);
        $suffix = ".manage_departments";
    } elseif ($mode == 'delete_selected_department') {
        if (!empty($_REQUEST['departments_ids'])) {
            foreach ($_REQUEST['departments_ids'] as $department_id) {
                fn_delete_department($department_id);
            }
        }
        $suffix = ".manage_departments";
    }
    return [CONTROLLER_STATUS_OK, 'departments' . $suffix];
}

if ($mode == 'add_department' || $mode == 'update_department') {
    $department_id = !empty($_REQUEST['department_id']) ? $_REQUEST['department_id'] : 0;

    $departments_data = fn_get_departments_data($department_id, DESCR_SL);

    if (empty($departments_data) && $mode == 'update_department') {
        return [CONTROLLER_STATUS_NO_PAGE];
    }

    Tygh::$app['view']->assign([
        'departments_data' => $departments_data,
        'u_info' => !empty($departments_data['chief_id']) ? fn_get_user_short_info($departments_data['chief_id']) : [],
        'o_info' => !empty($departments_data['owner_id']) ? fn_get_user_short_info($departments_data['owner_id']) : [],
    ]);
} elseif ($mode == 'manage_departments') {
    list($departments, $search) = fn_get_departments($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'), DESCR_SL);

    Tygh::$app['view']->assign('departments', $departments);
    Tygh::$app['view']->assign('search', $search);
}
