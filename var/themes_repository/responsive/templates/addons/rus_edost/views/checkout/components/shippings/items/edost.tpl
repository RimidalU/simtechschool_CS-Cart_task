<label for="office_{$group_key}_{$shipping_id}_{$store.office_id}"
       class="ty-one-store js-pickup-search-block-{$group_key} {if $old_office_id == $store.office_id || $store_count == 1}ty-edost-office__selected{/if} "
>
    <input
        type="radio"
        name="select_office[{$group_key}][{$shipping_id}]"
        value="{$store.office_id}"
        {if $old_office_id == $store.office_id || $store_count == 1}
            checked="checked"
        {/if}
        class="cm-edost-select-store ty-edost-office__radio-{$group_key} ty-valign"
        id="office_{$group_key}_{$shipping_id}_{$store.office_id}"
        data-ca-pickup-select-office="true"
        data-ca-shipping-id="{$shipping_id}"
        data-ca-group-key="{$group_key}"
        data-ca-location-id="{$store.office_id}"
    />

    <div class="ty-edost-store__label ty-one-store__label">
        <p class="ty-one-store__name">
            <span class="ty-one-store__name-text">{$store.name}</span>
        </p>

        <div class="ty-one-store__description">
            {if $store.address}
                <span class="ty-one-office__address">{$store.address}</span>
                <br />
            {/if}
            {if $store.schedule}
                <span class="ty-one-office__worktime">{$store.schedule}</span>
                <br />
            {/if}
            {if $store.tel}
                <span class="ty-one-office__worktime">{$store.tel}</span>
                <br />
            {/if}
        </div>
    </div>
</label>
