<div id="department_features_{$block.block_id}">
    <div class="ty-feature">
        {if $departments_data.main_pair}
            <div class="ty-feature__image">
                {include
                    file="common/image.tpl"
                    images=$departments_data.main_pair
                    image_id=$departments_data.main_pair.image_id
                    image_width="300"
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
    <h3 class="ty-mainbox-title">{__("employees:")} </h3>

    {if $departments_data.employee_ids}
        {include file="addons/departments/blocks/list_templates/departments_grid_list.tpl"
            users=$employees
            columns=3
        }
    {else}
        <p class="ty-no-items">{__("there are no employees in this section")}</p>
    {/if}
</div>
{capture name="mainbox_title"}
    {__("department")} {": "} {$departments_data.department nofilter}
{/capture}