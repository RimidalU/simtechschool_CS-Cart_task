{if fn_allowed_for("ULTIMATE")}
    {__("commerceml.connection_instructions.ult", [
        "[href]" => {"sync_data.update?sync_provider_id=commerceml"|fn_url}
    ]) nofilter}
{/if}

{if fn_allowed_for("MULTIVENDOR")}
    <div>
        {include file="common/widget_copy.tpl"
            widget_copy_title=__("information")
            widget_copy_text=__("commerceml.connection_instructions.mve")
            widget_copy_code_text = fn_url("sync_data.update?sync_provider_id=commerceml", "UserTypes::VENDOR"|enum)
        }
    </div>
{/if}
