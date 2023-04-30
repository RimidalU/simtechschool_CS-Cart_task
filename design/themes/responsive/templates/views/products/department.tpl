<div id="department_features_{$block.block_id}">
    <div class="ty-feature">
        {if $departments_data.main_pair}
            <div class="ty-feature__image">
                {include
                    file="common/image.tpl"
                    images=$departments_data.main_pair
                    image_id=$departments_data.main_pair.image_id
                    image_width="500"
                    image_height=$image_height
                }
            </div>
        {/if}
        <div class="ty-price-num">
            <bdi>
                {$departments_data.description nofilter}
            </bdi>
        </div>
        <div class="ty-cr-product-info-header">
            <h3>
                <bdi>
                    {__("chief")}{": "}{$departments_data.chief_name} 
                </bdi>
            </h3>
        </div>
    </div>

    {if employees}
        {include file="blocks/list_templates/departments_grid_list.tpl"
            products=$employees
            columns=3
            form_prefix="block_manager"
            show_name=true
        }
            {assign var="layouts" value=""|fn_get_products_views:false:0}
        {if $layouts.$selected_layout.template}
            {include file="`$layouts.$selected_layout.template`" columns=$settings.Appearance.columns_in_products_list}
        {/if}
    {else}
        <p class="ty-no-items">{__("there are no employees in this section")}</p>
    {/if}
</div>
{capture name="mainbox_title"}
    {__("department")} {": "} {$departments_data.department nofilter}
{/capture}
