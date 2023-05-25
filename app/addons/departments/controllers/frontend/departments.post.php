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

if ($mode == 'departments') {
    Tygh::$app['session']['continue_url'] = "departments.departments";

    $params = $_REQUEST;

    if ($items_per_page = fn_change_session_param(Tygh::$app['session'], $_REQUEST, 'items_per_page')) {
        $params['items_per_page'] = $items_per_page;
    }
    if ($sort_by = fn_change_session_param(Tygh::$app['session'], $_REQUEST, 'sort_by')) {
        $params['sort_by'] = $sort_by;
    }
    if ($sort_order = fn_change_session_param(Tygh::$app['session'], $_REQUEST, 'sort_order')) {
        $params['sort_order'] = $sort_order;
    }

    if (isset($params['order_ids'])) {
        $order_ids = is_array($params['order_ids']) ? $params['order_ids'] : explode(',', $params['order_ids']);
        foreach ($order_ids as $order_id) {
            if (!fn_is_order_allowed($order_id, $auth)) {
                return [CONTROLLER_STATUS_NO_PAGE];
            }
        }
    }

    $params['user_id'] = Tygh::$app['session']['auth']['user_id'];

    [$departments, $search] =
        fn_get_departments($params, Registry::get('settings.Appearance.products_per_page'), CART_LANGUAGE);

    $selected_layout = fn_get_products_layout($_REQUEST);

    Tygh::$app['view']->assign('departments', $departments);
    Tygh::$app['view']->assign('search', $search);
    Tygh::$app['view']->assign('columns', 3);

    fn_add_breadcrumb(__('departments'), 'departments.departments');
} elseif ($mode === 'department') {
    $department_data = [];
    $department_id = !empty($_REQUEST['department_id']) ? $_REQUEST['department_id'] : 0;
    $departments_data = fn_get_departments_data($department_id, CART_LANGUAGE);

    if (empty($departments_data)) {
        return [CONTROLLER_STATUS_NO_PAGE];
    };

    Tygh::$app['view']->assign('departments_data', $departments_data);
    fn_add_breadcrumb(__('departments'), 'departments.departments');
    fn_add_breadcrumb($departments_data['department']);

    $params = $_REQUEST;
    $params['extend'] = ['description'];
    $params['employee_ids'] =
        !empty($departments_data['employee_ids']) ? implode(',', $departments_data['employee_ids']) : -1;

    if ($items_per_page =
        fn_change_session_param(Tygh::$app['session']['search_params'], $_REQUEST, 'items_per_page')
    ) {
        $params['items_per_page'] = $items_per_page;
    }
    if ($sort_by = fn_change_session_param(Tygh::$app['session']['search_params'], $_REQUEST, 'sort_by')) {
        $params['sort_by'] = $sort_by;
    }
    if ($sort_order =
        fn_change_session_param(Tygh::$app['session']['search_params'], $_REQUEST, 'sort_order')
    ) {
        $params['sort_order'] = $sort_order;
    }

    [$users, $params] = fn_get_users(['user_id' => $departments_data['employee_ids']], $auth, $items_per_page);
    $selected_layout = fn_get_products_layout($_REQUEST);

    Tygh::$app['view']->assign('employees', $users);
    Tygh::$app['view']->assign('search', $search);
    Tygh::$app['view']->assign('selected_layout', $selected_layout);
}
