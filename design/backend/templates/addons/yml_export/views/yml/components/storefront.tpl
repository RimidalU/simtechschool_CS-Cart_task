{if ("MULTIVENDOR"|fn_allowed_for)}
    <div class="control-group">
        <label for="elm_ym_{$field_name}" class="control-label{if $field.required} cm-required{/if}">{__("yml_export.param_$field_name")}:</label>
        <div class="controls">
            {include file="views/storefronts/components/picker/picker.tpl"
            input_name="pricelist_data[storefront_id]"
            item_ids=[$price.param_data.storefront_id]
            show_advanced=false
            }
        </div>
    </div>
{/if}
