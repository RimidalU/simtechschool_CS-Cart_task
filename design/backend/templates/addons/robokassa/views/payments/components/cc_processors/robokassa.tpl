{if $settings.Security.secure_storefront === "YesNo::YES"|enum}
    {$storefront_url = fn_url('', 'SiteArea::STOREFRONT'|enum, 'https')|replace:$config.customer_index:""|rtrim:"/"}
{else}
    {$storefront_url = fn_url('', 'SiteArea::STOREFRONT'|enum, 'http')|replace:$config.customer_index:""|rtrim:"/"}
{/if}

{$result_url = "{$storefront_url}/payment_notification/result/robokassa"}
{$success_url = "{$storefront_url}/payment_notification/success/robokassa"}
{$fail_url = "{$storefront_url}/payment_notification/fail/robokassa"}

{include file = "common/subheader.tpl"
    title = __("robokassa.technical_preferences")
}

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
    <label class="control-label" for="robokassa_merchantid">{__("robokassa.merchantid")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchantid]" id="robokassa_merchantid" value="{$processor_params.merchantid}" size="60"><a href="#" id="robokassa_get_payment_methods">{__("robokassa.get_payment_methods")}</a>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="robokassa_password1">{__("robokassa.password1")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][password1]" id="robokassa_password1" value="{$processor_params.password1}" size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="robokassa_password2">{__("robokassa.password2")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][password2]" id="robokassa_password2" value="{$processor_params.password2}" size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="robokassa_descr">{__("robokassa.description")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][details]" id="robokassa_descr" value="{$processor_params.details}" size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="robokassa_mode">{__("robokassa.test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="robokassa_mode">
            <option value="test"{if $processor_params.mode === 'test'} selected="selected"{/if}>{__("robokassa.test")}</option>
            <option value="live"{if $processor_params.mode === 'live'} selected="selected"{/if}>{__("robokassa.live")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="robokassa_commission">{__("robokassa.commission")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][commission]" id="robokassa_commission">
            <option value="customer"{if $processor_params.commission === 'customer'} selected="selected"{/if}>{__("robokassa.customer")}</option>
            <option value="admin"{if $processor_params.commission === 'admin'} selected="selected"{/if}>{__("robokassa.administrator")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="robokassa_currency">{__("robokassa.payment_method")}:</label>
    {include file="addons/robokassa/views/payments/components/cc_processors/robokassa_cur_selectbox.tpl"}
</div>

{include file="common/subheader.tpl" title=__("robokassa.text_status_map") target="#text_status_map"}

<div id="text_status_map" class="in collapse">
    {$statuses = $smarty.const.STATUSES_ORDER|fn_get_simple_statuses}

    <div class="control-group">
        <label class="control-label" for="elm_paid">{__("robokassa.paid")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][paid]" id="elm_paid">
                {foreach $statuses as $k => $s}
                    <option value="{$k}" {if (isset($processor_params.statuses.paid) && $processor_params.statuses.paid === $k) || (!isset($processor_params.statuses.paid) && $k === 'P')}selected="selected"{/if}>{$s}</option>
                {/foreach}
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_final">{__("robokassa.final_status")}</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][final]" id="elm_final">
                {foreach $statuses as $key => $status}
                    <option value="{$key}" {if $processor_params.statuses.final|default:"C" === $key} selected="selected"{/if}>
                        {$status}
                    </option>
                {/foreach}
            </select>
        </div>
    </div>
</div>

<script>
    (function (_, $) {
        $(_.doc).ready(function () {
            fn_get_robokassa_payment_methods();
            $('#robokassa_get_payment_methods').on('click', fn_get_robokassa_payment_methods);
        });

        function fn_get_robokassa_payment_methods() {
            var merchantid = $('#robokassa_merchantid').val();
            $.ceAjax('request', '{fn_url("payment_notification.robokassa_get_payment_methods")}', {
                data: {
                    payment: 'robokassa',
                    merchantid: merchantid,
                    result_ids: 'robokassa_payment_method_div',
                    payment_id: {$smarty.request.payment_id},
                }
            });
        }
    })(Tygh, Tygh.$);
</script>
