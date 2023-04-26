{* Object picker lazy load placeholder *}
<span class="select2 select2-container select2-container--default"
    dir="{$language_direction}"
    style="width: 100%;"
    data-ca-object-picker-lazy-load-field="{$input_id}"
>
    <span class="selection">

        {if $multiple
            || $feature.feature_type === "ProductFeatures::MULTIPLE_CHECKBOX"|enum
        }
            <span class="select2-selection select2-selection--multiple" role="combobox" aria-haspopup="true" aria-expanded="false">
                <ul class="select2-selection__rendered">

                    {foreach $item_ids as $item_id}
                        {if !$item_id.selected}
                            {continue}
                        {/if}
                        

                        <li class="select2-selection__choice">
                            <span class="select2-selection__choice__remove" role="presentation"
                                data-ca-object-picker-lazy-load-select2-id="{$item_id.variant_id}"
                                data-ca-object-picker-lazy-load-clear="{$input_id}"
                            >&times;</span>
                        
                            {include file="views/product_features/components/variants_picker/lazy_load_variant.tpl"
                                name=$item_id.variant
                                color=$item_id.color
                                template_type=$template_type
                                enable_color=$enable_color
                            }
                        </li>
                    {/foreach}

                    <li class="select2-search select2-search--inline">
                        <input class="select2-search__field" type="search" tabindex="-1"
                            autocomplete="off" autocorrect="off" autocapitalize="none"
                            spellcheck="false" role="searchbox" aria-autocomplete="list"
                            style="width: 100%;"
                        />
                    </li>
                </ul>
            </span>

        {elseif $feature.feature_type === "ProductFeatures::TEXT_SELECTBOX"|enum
            || $feature.feature_type === "ProductFeatures::NUMBER_SELECTBOX"|enum
            || $feature.feature_type === "ProductFeatures::EXTENDED"|enum
        }
            <span class="select2-selection select2-selection--single" role="combobox" aria-haspopup="true" aria-expanded="false">

                <span class="select2-selection__rendered">
                    {if $feature.variant_id}
                        {foreach $item_ids as $item_id}
                            {if !$item_id.selected}
                                {continue}
                            {/if}

                            <span class="select2-selection__clear" title="{__("object_picker.remove_all_items")}"
                                data-ca-object-picker-lazy-load-clear="{$input_id}"
                            >&times;</span>

                            {include file="views/product_features/components/variants_picker/lazy_load_variant.tpl"
                                name=$item_id.variant
                                color=$item_id.color
                                template_type=$template_type
                                enable_color=$enable_color
                            }
                        {/foreach}
                    {else}
                        <span class="select2-selection__placeholder">
                            {include file="views/product_features/components/variants_picker/lazy_load_variant.tpl"
                                name=$empty_variant_text
                                template_type=$template_type
                                enable_color=$enable_color
                            }
                        </span>
                    {/if}
                </span>

                <span class="select2-selection__arrow" role="presentation">
                    <b role="presentation"></b>
                </span>
            </span>
        {/if}

    </span>
    <span class="dropdown-wrapper" aria-hidden="true"></span>
</span>
