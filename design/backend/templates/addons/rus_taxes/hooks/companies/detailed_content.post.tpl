{if ($agent_types)}
    {include file="common/subheader.tpl" title=__("rus_taxes.rus_taxes")}
    <div class="control-group">
        <label class="control-label" for="agent_type_{$runtime.company_id}">{__("rus_taxes.agent_type")}:</label>
        <div class="controls">
            <select name="company_data[agent_type]" id="agent_type_{$runtime.company_id}">
                {foreach $agent_types as $agent_type}
                    <option value="{$agent_type}"{if $company_data.agent_type === $agent_type} selected="selected"{/if}>{__("rus_taxes.agent_type.`$agent_type`")}</option>
                {/foreach}
            </select>
        </div>
    </div>
{/if}