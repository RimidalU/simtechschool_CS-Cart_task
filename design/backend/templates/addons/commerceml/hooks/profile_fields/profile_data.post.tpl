<div class="control-group">
    <label class="control-label">{__("commerceml.export_field_via_commerceml")}:</label>
    <div class="controls">
        <input type="hidden"
               name="field_data[cml_export_to_1c]"
               value="{if $field.field_name === "email"}{"YesNo::YES"|enum}{else}{"YesNo::NO"|enum}{/if}" />
        <input type="checkbox"
               name="field_data[cml_export_to_1c]"
               value="{"YesNo::YES"|enum}"
               {if $field.cml_export_to_1c === "YesNo::YES"|enum || $field.is_default === "YesNo::YES"|enum}
                   checked="checked"
               {/if}
               class="cm-switch-availability"
               {if $field.field_name === "email" || $field.is_default === "YesNo::YES"|enum}
                   disabled="disabled"
               {/if}
        />
        <p class="muted description">
            {if $field.is_default === "YesNo::YES"|enum}
                {if $field.section === "ProfileFieldSections::BILLING_ADDRESS"|enum || $field.section === "ProfileFieldSections::SHIPPING_ADDRESS"|enum}
                    {__("commerceml.tooltip.export_field_via_commerceml_default_bs")}
                {else}
                    {__("commerceml.tooltip.export_field_via_commerceml_default_contact_info")}
                {/if}
            {else}
                {if $field.section === "ProfileFieldSections::BILLING_ADDRESS"|enum || $field.section === "ProfileFieldSections::SHIPPING_ADDRESS"|enum}
                    {__("commerceml.tooltip.export_field_via_commerceml_custom_bs")}
                {else}
                    {__("commerceml.tooltip.export_field_via_commerceml_custom_contact_info")}
                {/if}
            {/if}
        </p>
    </div>
</div>
