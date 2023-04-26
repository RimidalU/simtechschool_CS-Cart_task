{script src="js/addons/rus_edost/func.js"}

{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == "edost"}
    {script src="js/addons/rus_edost/map.js"}

    {$office_count=$shipping.data.office|count}

    {$shipping_id=$shipping.shipping_id}
    {$old_office_id=$select_office.$group_key.$shipping_id}

    <div class="ty-checkout-select-office">
        {if $shipping.data.city_pickpoint}
            {script src="//pickpoint.ru/select/postamat.js" charset="utf-8"}
            <input type="hidden" name="pickpointmap[{$group_key}][{$shipping.shipping_id}][pickpoint_id]" id="pickpoint_id" data-ca-shipping-field="pickpoint" value="{$cart.pickpointmap.$group_key.$shipping_id.pickpoint_id}" />
            <input type="hidden" name="pickpointmap[{$group_key}][{$shipping.shipping_id}][pickpoint_name]" id="pickpoint_name" data-ca-shipping-field="pickpoint" value="{$cart.pickpointmap.$group_key.$shipping_id.pickpoint_name}" />
            <input type="hidden" name="pickpointmap[{$group_key}][{$shipping.shipping_id}][pickpoint_address]" id="pickpoint_address" data-ca-shipping-field="pickpoint" value="{$cart.pickpointmap.$group_key.$shipping_id.pickpoint_address}" />

            <div class="ty-one-office__name">
                <div id="pickpoint_name_terminal">{$cart.pickpointmap.$group_key.$shipping_id.pickpoint_name}</div>
                <div id="pickpoint_address_terminal">{$cart.pickpointmap.$group_key.$shipping_id.pickpoint_address}</div>
            </div>

            <a href="#" id="pickpoint_select_terminal" data-pickpoint-select-state="" data-pickpoint-select-city="{$shipping.data.city_pickpoint}">{__("select")}</a>
        {/if}
    </div>

    {if $shipping.data.office}
        <h2 class="litecheckout__step-title">{__("lite_checkout.select_pickup_item")}</h2>

        {$edost_map_container = "edost_map_$shipping_id"}
        {$store_count = $shipping.data.office|count}

        {hook name="checkout:rus_edost_pickup_content"}
            {include file="addons/rus_edost/views/checkout/components/shippings/list_edost.tpl"}
        {/hook}
    {/if}
{/if}