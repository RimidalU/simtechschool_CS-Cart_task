{if $settings.Security.secure_storefront === "YesNo::YES"|enum}
    {$storefront_url = fn_url('', 'SiteArea::STOREFRONT'|enum, 'https')|replace:$config.customer_index:""|rtrim:"/"}
{else}
    {$storefront_url = fn_url('', 'SiteArea::STOREFRONT'|enum, 'http')|replace:$config.customer_index:""|rtrim:"/"}
{/if}

{$result_url = "{$storefront_url}/payment_notification/result/robokassa_split"}
{$success_url = "{$storefront_url}/payment_notification/success/robokassa_split"}
{$fail_url = "{$storefront_url}/payment_notification/fail/robokassa_split"}

{include file = "common/subheader.tpl"
    title = __("robokassa.technical_preferences")
}

<input type="hidden"
       name="payment_data[processor_params][is_robokassa_split]"
       value="{"YesNo::YES"|enum}"
/>

<div class="control-group">
    <label class="control-label">
        {__("robokassa.result_url")}
    </label>
    <div class="controls">
        {include file = "common/widget_copy.tpl"
            widget_copy_code_text = $result_url
            widget_copy_class = "widget-copy--compact"
        }
    </div>
</div>

<div class="control-group">
    <label class="control-label">
        {__("robokassa.notification_result_url_method")}
    </label>
    <div class="controls">
        <p class="switch">POST</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label">
        {__("robokassa.success_url")}
    </label>
    <div class="controls">
        {include file = "common/widget_copy.tpl"
            widget_copy_code_text = $success_url
            widget_copy_class = "widget-copy--compact"
        }
    </div>
</div>

<div class="control-group">
    <label class="control-label">
        {__("robokassa.notification_success_url_method")}
    </label>
    <div class="controls">
        <p class="switch">GET</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label">
        {__("robokassa.fail_url")}
    </label>
    <div class="controls">
        {include file = "common/widget_copy.tpl"
            widget_copy_code_text = $fail_url
            widget_copy_class = "widget-copy--compact"
        }
    </div>
</div>

<div class="control-group">
    <label class="control-label">
        {__("robokassa.notification_fail_url_method")}
    </label>
    <div class="controls">
        <p class="switch">GET</p>
    </div>
</div>

<hr>

<div class="control-group">
    <label class="control-label" for="elm_robokassa_master_store_id_{$payment_id}">{__("robokassa.master_store_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][master_store_id]" id="elm_robokassa_master_store_id_{$payment_id}" value="{$processor_params.master_store_id}" size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_robokassa_account_number_{$payment_id}">{__("robokassa.account_number")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][account_number]" id="elm_robokassa_account_number_{$payment_id}" value="{$processor_params.account_number}" size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_robokassa_password1_{$payment_id}">{__("robokassa.password1")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][password1]" id="elm_robokassa_password1_{$payment_id}" value="{$processor_params.password1}" size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_robokassa_password2_{$payment_id}">{__("robokassa.password2")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][password2]" id="elm_robokassa_password2_{$payment_id}" value="{$processor_params.password2}" size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_robokassa_mode_{$payment_id}">{__("robokassa.test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="elm_robokassa_mode_{$payment_id}">
            <option value="test"{if $processor_params.mode == 'test'} selected="selected"{/if}>{__("robokassa.test")}</option>
            <option value="live"{if $processor_params.mode == 'live'} selected="selected"{/if}>{__("robokassa.live")}</option>
        </select>
    </div>
</div>
