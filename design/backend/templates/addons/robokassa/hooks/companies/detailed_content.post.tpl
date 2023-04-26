{if $is_robokassa_split_used || !$runtime.company_id}
    {include file="common/subheader.tpl" title=__("robokassa.robokassa")}
    <div class="control-group">
        <label class="control-label"
               for="elm_robokassa_store_id"
        >
            {__("robokassa.store_id")}:
        </label>
        <div class="controls">
            <input type="text"
                   name="company_data[robokassa_store_id]"
                   id="elm_robokassa_store_id"
                   value="{$company_data.robokassa_store_id}"
            />
        </div>
    </div>
    <div class="control-group">
        <label class="control-label"
               for="elm_robokassa_account_number"
        >
            {__("robokassa.account_number")}:
        </label>
        <div class="controls">
            <input type="text"
                   name="company_data[robokassa_account_number]"
                   id="elm_robokassa_account_number"
                   value="{$company_data.robokassa_account_number}"
            />
        </div>
    </div>
{/if}
