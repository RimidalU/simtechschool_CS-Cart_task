{if $products}

    {script src="js/tygh/exceptions.js"}
    {split data=$products size=$columns|default:"2" assign="splitted_products" skip_complete=true}
    {math equation="100 / x" x=$columns|default:"2" assign="cell_width"}
    {assign var="cur_number" value=1}

    <div class="grid-list">
        {strip}
            {foreach from=$splitted_products item="sproducts" name="sprod"}
                {foreach from=$sproducts item="product" name="sproducts"}
                    <div class="ty-column{$columns}">
                        {if $product}
                        <div class="ty-grid-list__item ty-quick-view-button__wrapper">
                            <div class="ty-grid-list__item-name">
                                <bdi>
                                    {__("name")}{": "}{$product.firstname}
                                </bdi>
                            </div>
                            <div class="ty-grid-list__item-name">
                                <bdi>
                                    {__("email")}{": "}{$product.email}
                                </bdi>
                            </div>
                            <div class="ty-grid-list__item-name">
                                <bdi>
                                    {__("lastname")}{": "}{$product.lastname}
                                </bdi>
                            </div>
                            <div class="ty-grid-list__item-name">
                                <bdi>
                                    {__("phone")}{": "}{$product.phone}
                                </bdi>
                            </div>
                        </div>
                        {/if}
                    </div>
                {/foreach}
                {if $iteration % $columns != 0}
                    {section loop=$empty_count name="empty_rows"}
                        <div class="ty-column{$columns}">
                            <div class="ty-product-empty">
                                <span class="ty-product-empty__text">{__("empty")}</span>
                            </div>
                        </div>
                    {/section}
                {/if}
            {/foreach}
        {/strip}
    </div>
{/if}

{capture name="mainbox_title"}{$title}{/capture}