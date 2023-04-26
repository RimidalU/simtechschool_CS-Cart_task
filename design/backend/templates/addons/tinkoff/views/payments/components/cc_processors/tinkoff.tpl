{include file="common/subheader.tpl" title=__("information") target="#tinkoff_payment_instruction_`$payment_id`"}
<div id="tinkoff_payment_instruction_{$payment_id}" class="in collapse">
    {include file="common/widget_copy.tpl"
        widget_copy_text=__("addons.tinkoff.url_for_payment_notifications")
        widget_copy_code_text="{$notification_url|default: fn_url("tinkoff.get_notification","SiteArea::STOREFRONT"|enum)}"
    }

    {include file="common/widget_copy.tpl"
        widget_copy_text=__("addons.tinkoff.url_for_success")
        widget_copy_code_text="{$success_url|default: fn_url("tinkoff.success","SiteArea::STOREFRONT"|enum)}"
    }

    {include file="common/widget_copy.tpl"
        widget_copy_text=__("addons.tinkoff.url_for_fail")
        widget_copy_code_text="{$fail_url|default: fn_url("tinkoff.fail","SiteArea::STOREFRONT"|enum)}"
    }
</div>
<input type="hidden"
       name="payment_data[processor_params][is_tinkoff]"
       value="{"YesNo::YES"|enum}"
/>
{include file="common/subheader.tpl" title=__("settings") target="#tinkoff_payment_settings_`$payment_id`"}
<div id="tinkoff_payment_settings_{$payment_id}" class="in collapse">
    <div class="control-group">
        <label class="control-label cm-required" for="terminal_key_{$payment_id}">{__("addons.tinkoff.terminal_key")}:</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][terminal_key]" id="terminal_key_{$payment_id}" value="{$processor_params.terminal_key}" class="input-text-large"  size="60" />
        </div>
        <div class="controls">
            <p class="muted description">{__("addons.tinkoff.terminal_key_notice")}</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label cm-required" for="password_{$payment_id}">{__("addons.tinkoff.terminal_password")}:</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][password]" id="password_{$payment_id}" value="{$processor_params.password}" class="input-text-large"  size="60" />
        </div>
        <div class="controls">
            <p class="muted description">{__("addons.tinkoff.terminal_password_notice")}</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="get_qr_{$payment_id}">
            {__("addons.tinkoff.get_qr")}
        </label>
        <input type="hidden" name="payment_data[processor_params][get_qr]" value="{"YesNo::NO"|enum}" />
        <div class="controls">
            <input type="checkbox"
                   name="payment_data[processor_params][get_qr]"
                   id="get_qr_{$payment_id}"
                   value="{"YesNo::YES"|enum}"
                   {if $processor_params.get_qr === "YesNo::YES"|enum}checked="checked"{/if}
            />
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="send_receipt_{$payment_id}">
            {__("addons.tinkoff.send_receipt")}
        </label>
        <input type="hidden" name="payment_data[processor_params][send_receipt]" value="{"YesNo::NO"|enum}" />
        <div class="controls">
            <input type="checkbox"
                   name="payment_data[processor_params][send_receipt]"
                   id="send_receipt_{$payment_id}"
                   value="{"YesNo::YES"|enum}"
                   {if $processor_params.send_receipt === "YesNo::YES"|enum}checked="checked"{/if}
            />
        </div>
        <div class="controls">
            <p class="muted description">{__("addons.tinkoff.receipt_acquiring_should_be_set")}</p>
        </div>
    </div>

    {$statuses=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses}
    <div class="control-group">
        <label class="control-label" for="tinkoff_confirmed_order_status_{$payment_id}">{__("addons.tinkoff.confirmed_order_status")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][final_success_status]" id="tinkoff_confirmed_order_status_{$payment_id}">
                {foreach $statuses as $key => $item}
                    <option value="{$key}"
                        {if $processor_params.final_success_status|default:"C" === $key}
                            selected="selected"
                        {/if}
                    >{$item}</option>
                {/foreach}
            </select>
        </div>
        <div class="controls">
            <p class="muted description">{__("addons.tinkoff.confirmed_order_status.description")}</p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="pay_type_{$payment_id}">
            {__("addons.tinkoff.pay_type")}
        </label>
        <div class="controls">
            <label class="radio" for="one_step_type_{$payment_id}">
                <input type="radio"
                       name="payment_data[processor_params][pay_type]"
                       id="one_step_type_{$payment_id}"
                       {if !$processor_params.pay_type || $processor_params.pay_type === "\Tygh\Addons\Tinkoff\Enum\PayTypes::ONE_STEP"|constant}checked="checked"{/if}
                       value="{"\Tygh\Addons\Tinkoff\Enum\PayTypes::ONE_STEP"|constant}"
                />
                {__("addons.tinkoff.one_step_payment")}
            </label>
            <label class="radio" for="two_step_type_{$payment_id}">
                <input type="radio"
                       name="payment_data[processor_params][pay_type]"
                       id="two_step_type_{$payment_id}"
                       {if $processor_params.pay_type === "\Tygh\Addons\Tinkoff\Enum\PayTypes::TWO_STEP"|constant}checked="checked"{/if}
                       value="{"\Tygh\Addons\Tinkoff\Enum\PayTypes::TWO_STEP"|constant}"
                />
                {__("addons.tinkoff.two_step_payment")}
            </label>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="tax_system_{$payment_id}">{__("rus_taxes.tax_system")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][tax_system]" id="tax_system_{$runtime.company_id}">
                {foreach $tax_systems as $tax_system}
                    <option value="{$tax_system}"
                            {if $processor_params.tax_system|default:"osn" === $tax_system}
                                selected="selected"
                            {/if}
                    >{__("rus_taxes.tax_system.`$tax_system`")}</option>
                {/foreach}
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="ffd_version_{$payment_id}">
            {__("addons.tinkoff.ffd_version")}
        </label>
        <div class="controls">
            <label class="radio" for="ffd_version_1_{$payment_id}">
                <input type="radio"
                       name="payment_data[processor_params][ffd_version]"
                       id="ffd_version_1_{$payment_id}"
                       {if !$processor_params.ffd_version || $processor_params.ffd_version === "1.05"}checked="checked"{/if}
                       value="1.05"
                />
                1.05
            </label>
        </div>
    </div>
</div>