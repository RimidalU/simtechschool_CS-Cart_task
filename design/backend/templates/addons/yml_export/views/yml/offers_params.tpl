{capture name="mainbox"}
    {$allow_save = fn_check_permissions("yml", "update", "admin", "POST")}

<div id="yml_offer_features">
    <form action="{""|fn_url}" method="post" name="yml_export_offers" class="form-horizontal form-edit {if !$allow_save} cm-hide-inputs{/if}">
        {if fn_allowed_for("MULTIVENDOR") && !$runtime.company_id}
            {include file="common/subheader.tpl" title="{__("vendor")}" target="#collapsable_addon_option_yml_export_company"}

            <div id="collapsable_addon_option_yml_export_company" class="in collapse">
                <div id="container_addon_option_yml_export_company" class="control-group setting-wide yml_export">
                    {include file="views/companies/components/company_field.tpl"
                    name="company_id"
                    id="offer_data_company_id"
                    selected=$company_id
                    js_action="fn_select_vendor_offers();"
                    zero_company_id_name_lang_var="yml_export.default_settings_variant"
                    }
                </div>
            </div>
        {/if}

        {foreach from=$yml_offer_types item="offer_name" key="offer"}
            {if !empty($yml_offer_features[$offer])}

                {include file="common/subheader.tpl" title="{__($offer_name)}" target="#collapsable_addon_option_yml_export_{$offer}"}

                <div id="collapsable_addon_option_yml_export_{$offer}" class="in collapse" style="height: auto;">

                    {foreach from=$yml_offer_features[$offer] item="data" key="offer_feature_key"}
                        <div id="container_addon_option_yml_export_{$offer}_{$offer_feature_key}" class="control-group setting-wide yml_export">
                            <label for="addon_option_yml_export_{$offer_feature_key}" class="control-label ">{__("yml2_offer_feature_{$offer}_{$offer_feature_key}")}:
                            </label>

                            <div class="controls">
                                <select id="addon_option_yml_export_{$offer_feature_key}"
                                        name="data[ym_features][{$offer}][{$offer_feature_key}]"
                                        class="cm-object-selector"
                                        data-ca-page-size="50"
                                        data-ca-enable-search="true"
                                        data-ca-load-via-ajax="true"
                                        data-ca-data-url="{"yml.get_variants_list?offer=`$offer`&offer_key=`$offer_feature_key`"|fn_url nofilter}"
                                        >
                                    {if $data.type == 'product'}
                                        <option value="product.{$data.value}" selected="selected">{__("yml2_product_field_`$data.value`")}</option>
                                    {else}
                                        <option value="feature.{$data.value}" selected="selected">{$data.feature_name}</option>
                                    {/if}
                                </select>
                                <div class="right">
                                </div>
                            </div>
                        </div>
                    {/foreach}

                </div>
            {/if}
        {/foreach}
    </form>
<!--yml_offer_features--></div>
{/capture}

{capture name="buttons"}
    {include file="buttons/save.tpl" but_name="dispatch[yml.update_offers]" but_role="submit-link" but_target_form="yml_export_offers"}
{/capture}

{include file="common/mainbox.tpl" title=__("yml_export.offers_params") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}

{if fn_allowed_for("MULTIVENDOR") && !$runtime.company_id}
<script>
    var fn_select_vendor_offers = function(){
        $.ceAjax('request', Tygh.current_url, {
            data: {
                company_id: $('[name="company_id"]').val()
            },
            result_ids: 'yml_offer_features'
        })
    };
</script>
{/if}