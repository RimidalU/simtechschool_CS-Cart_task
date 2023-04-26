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

use Tygh\Enum\NotificationSeverity;
use Tygh\Http;
use Tygh\Settings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'oauth') {

    if (!empty($_REQUEST['code'])) {
        $params = [];

        if ($_REQUEST['state']) {
            $params['storefront_id'] = (int) $_REQUEST['state'];
        }

        $settings = Settings::instance($params);

        $response = Http::post(
            'https://oauth.yandex.ru/token',
            [
                'grant_type' => 'authorization_code',
                'code' => $_REQUEST['code'],
                'client_id' => $settings->getValue('application_id', 'rus_yandex_metrika'),
                'client_secret' => $settings->getValue('application_password', 'rus_yandex_metrika')
            ]
        );

        $result = json_decode($response, true);

        if (isset($result['error'])) {
            fn_set_notification(NotificationSeverity::ERROR, __('error'), $result['error_description']);
            return [CONTROLLER_STATUS_REDIRECT, 'addons.update&addon=rus_yandex_metrika'];
        }

        if (!empty($result['access_token'])) {
            $settings->updateValue('auth_token', $result['access_token'], 'rus_yandex_metrika');
        }
    }

    return [CONTROLLER_STATUS_REDIRECT, 'addons.update&addon=rus_yandex_metrika'];

}
