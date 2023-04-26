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

namespace Tygh\Addons\YandexCheckout;

use Tygh\Addons\InstallerInterface;
use Tygh\Addons\YandexCheckout\Api\Logger;
use Tygh\Addons\YandexCheckout\Enum\ProcessorScript;
use Tygh\Core\ApplicationInterface;
use Tygh\Enum\YesNo;
use Tygh\Languages\Languages;
use Tygh\Settings;

class Installer implements InstallerInterface
{
    /**
     * @var \Tygh\Core\ApplicationInterface
     */
    protected $app;

    public function __construct(ApplicationInterface $app)
    {
        $this->app = $app;
    }

    public static function factory(ApplicationInterface $app)
    {
        return new self($app);
    }

    public function onBeforeInstall()
    {

    }

    public function onInstall()
    {
        $this->installYandexCheckoutProcessor();

        if (fn_allowed_for('MULTIVENDOR')) {
            $this->installYandexCheckoutForMarketplacesProcessor();
        }
        $this->addLoggingSettings();
    }

    /**
     * Adds logging settings to all network operations.
     *
     * @return void
     */
    protected function addLoggingSettings()
    {
        $settings_name = 'log_type_' . Logger::LOG_TYPE;
        $settings = Settings::instance()->getSettingDataByName($settings_name);

        $logging_section = Settings::instance()->getSectionByName('Logging');
        $lang_codes = array_keys(Languages::getAll());

        if ($settings) {
            return;
        }

        $settings = [
            'name'           => $settings_name,
            'section_id'     => $logging_section['section_id'],
            'section_tab_id' => 0,
            'type'           => 'N',
            'position'       => 10,
            'is_global'      => 'N',
            'edition_type'   => 'ROOT',
        ];
        $descriptions = [];
        foreach ($lang_codes as $lang_code) {
            $descriptions[] = [
                'object_type'   => Settings::SETTING_DESCRIPTION,
                'lang_code'     => $lang_code,
                'value'         => __('log_type_yandex_checkout', [], $lang_code),
            ];
        }

        $settings_id = Settings::instance()->update($settings, null, $descriptions, true);
        $logging_actions = [
            Logger::ACTION_REQUEST,
            Logger::ACTION_FAILURE,
        ];
        foreach ($logging_actions as $position => $variant) {
            $variant_id = Settings::instance()->updateVariant(
                [
                    'object_id' => $settings_id,
                    'name'      => $variant,
                    'position'  => $position,
                ]
            );

            foreach ($lang_codes as $lang_code) {
                $description = [
                    'object_id' => $variant_id,
                    'object_type' => Settings::VARIANT_DESCRIPTION,
                    'lang_code'   => $lang_code,
                    'value'       => __('log_action_' . $variant, [], $lang_code),
                ];
                /** @psalm-suppress InvalidScalarArgument */
                Settings::instance()->updateDescription($description);
            }
        }

        Settings::instance()->updateValue($settings_name, [Logger::ACTION_FAILURE], 'Logging');
    }

    public function onUninstall()
    {
        $payment_ids = db_get_fields(
            'SELECT payment_id'
            . ' FROM ?:payments AS payments'
            . ' LEFT JOIN ?:payment_processors AS payment_processors'
            . ' ON payments.processor_id = payment_processors.processor_id'
            . ' WHERE payment_processors.addon = ?s',
            'yandex_checkout'
        );

        foreach ($payment_ids as $payment_id) {
            fn_delete_payment($payment_id);
        }

        db_query('DELETE FROM ?:payment_processors WHERE addon = ?s', 'yandex_checkout');

        $setting = Settings::instance()->getSettingDataByName('log_type_yandex_checkout');
        if (!$setting) {
            return;
        }

        Settings::instance()->removeById((int) $setting['object_id']);
    }

    protected function installYandexCheckoutProcessor()
    {
        $processor = [
            'processor' => 'ЮKassa',
            'processor_script' => ProcessorScript::YANDEX_CHECKOUT,
            'admin_template' => 'yandex_checkout.tpl',
            'callback'  => YesNo::YES,
            'type' => 'P',
            'position' => 10,
            'addon' => 'yandex_checkout',
        ];

        db_replace_into('payment_processors', $processor);
    }

    protected function installYandexCheckoutForMarketplacesProcessor()
    {
        $processor = [
            'processor' => 'ЮKassa для платформ',
            'processor_script' => ProcessorScript::YANDEX_CHECKOUT_FOR_MARKETPLACES,
            'admin_template' => 'yandex_checkout_for_marketplaces.tpl',
            'callback'  => YesNo::YES,
            'type' => 'P',
            'position' => 11,
            'addon' => 'yandex_checkout',
        ];

        db_replace_into('payment_processors', $processor);
    }
}