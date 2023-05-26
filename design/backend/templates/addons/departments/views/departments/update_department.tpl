{if $departments_data}
    {assign var="id" value=$departments_data.department_id}
{else}
    {assign var="id" value=0}
{/if}

{capture name="mainbox"}

    <form action="{""|fn_url}" method="post" class="form-horizontal form-edit" name="departments_form" enctype="multipart/form-data">
        <input type="hidden" class="cm-no-hide-input" name="fake" value="1" />
        <input type="hidden" class="cm-no-hide-input" name="department_id" value="{$id}" />

        <div id="content_general">
            <div class="control-group">
                <label for="owner" class="control-label cm-required">{__("owner")}</label>
                <div class="control-group">
                    <div class="controls">
                        {include
                            file="pickers/users/picker.tpl"
                            but_text=__("assign_owner")
                            id="owner"
                            data_id="return_users"
                            but_meta="btn"
                            input_name="departments_data[owner_id]" 
                            display="radio"
                            user_info=$o_info
                            view_mode="single_button"
                            placement="right"
                        }
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label for="chief" class="control-label cm-required">{__("chief")}</label>
                <div class="control-group">
                    <div class="controls">
                        {include
                            file="pickers/users/picker.tpl"
                            but_text=__("assign_chief")
                            id="chief"
                            data_id="return_users"
                            but_meta="btn"
                            input_name="departments_data[chief_id]"
                            display="radio"
                            user_info=$u_info
                            view_mode="single_button"
                            placement="right"
                        }
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label for="elm_department_name" class="control-label cm-required">{__("department")}</label>
                <div class="controls">
                    <input
                        type="text" 
                        name="departments_data[department]" 
                        id="elm_department_name" 
                        value="{$departments_data.department}" 
                        size="25" 
                        class="input-large" 
                    />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="elm_department_position">{__("position")}:</label>
                <div class="controls">
                    <input
                        type="text" 
                        name="departments_data[position]" 
                        id="elm_department_position" 
                        size="10" 
                        value="{$departments_data.position}" 
                        class="input-text-short"
                    />
                </div>
            </div>
            <div class="control-group" id="department_graphic">
                <label class="control-label">{__("logo")}</label>
                <div class="controls">
                    {include file="common/attach_images.tpl"
                        image_name="department"
                        image_object_type="department"
                        image_pair=$departments_data.main_pair
                        image_object_id=$id
                        no_detailed=true
                        hide_titles=true
                    }
                </div>
            </div>
            <div class="control-group" id="department_text">
                <label class="control-label" for="elm_department_description">{__("description")}:</label>
                <div class="controls">
                    <textarea 
                        id="elm_department_description"                     
                        name="departments_data[description]" 
                        cols="35" 
                        rows="8" 
                        class="cm-wysiwyg input-large"
                    >
                        {$departments_data.description}
                </textarea>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">{__("creation_date")}</label>
                <div class="controls" id="elm_department_creation_date">
                    {$departments_data.timestamp|date_format:"`$settings.Appearance.date_format`"}
                </div>
            </div>

            {include file="common/select_status.tpl" 
                input_name="departments_data[status]" 
                id="elm_department_status" 
                obj_id=$id 
                obj=$departments_data 
                hidden=false
            }
            <div class="control-group">
                <label class="control-label">{__("department_staff")}</label>
                <div class="controls">
                    {include file="pickers/users/picker.tpl" 
                        but_text=__("add_employee") 
                        data_id="employee_ids" 
                        but_meta="btn" 
                        item_ids=$departments_data.employee_ids
                        placement="right"
                        input_name="departments_data[employee_ids]"
                    }
                </div>
            </div>

            <!--content_general-->
        </div>

        {capture name="buttons"}
            {if !$id}
                {include file="buttons/save_cancel.tpl" 
                    but_role="submit-link" 
                    but_target_form="departments_form" 
                    but_name="dispatch[departments.update_department]"
                }
            {else}
            
            {capture name="tools_list"}
                <li>
                    {btn 
                        type="list" 
                        text=__("delete") 
                        class="cm-confirm" 
                        href="departments.delete_department?department_id=`$department.department_id`" 
                        method="POST"
                    }
                </li>
            {/capture}

            {dropdown content=$smarty.capture.tools_list}
            {include file="buttons/save_cancel.tpl" 
                but_name="dispatch[departments.update_department]" 
                but_role="submit-link" 
                but_target_form="departments_form" 
                hide_first_button=$hide_first_button 
                hide_second_button=$hide_second_button 
                save=department_id
            }
            {/if}
        {/capture}

    </form>

{/capture}

{include file="common/mainbox.tpl"
    title=($id) ? $departments_data.department : __("new_department")
    content=$smarty.capture.mainbox
    buttons=$smarty.capture.buttons
    select_languages=true
}

{** department section **}