{if $users}

    {script src="js/tygh/exceptions.js"}
    {split data=$users size=$columns|default:"2" assign="splitted_users" skip_complete=true}
    {math equation="100 / x" x=$columns|default:"2" assign="cell_width"}
    {assign var="cur_number" value=1}

    <div class="grid-list">
        {strip}
            {foreach from=$splitted_users item="susers" name="sprod"}
                {foreach from=$susers item="user" name="susers"}
                    <div class="ty-column{$columns}">
                        {if $user}
                        <div class="ty-grid-list__item ty-quick-view-button__wrapper">
                            <div class="ty-grid-list__item-name">
                                <bdi>
                                    {__("name")}{": "}{$user.firstname}{" "}{$user.lastname}
                                </bdi>
                            </div>
                            <div class="ty-grid-list__item-name">
                                <bdi>
                                    {__("email")}{": "}{$user.email}
                                </bdi>
                            </div>
                            <div class="ty-grid-list__item-name">
                                <bdi>
                                    {__("phone")}{": "}{$user.phone}
                                </bdi>
                            </div>
                        </div>
                        {/if}
                    </div>
                {/foreach}
                {if $iteration % $columns != 0}
                    {section loop=$empty_count name="empty_rows"}
                        <div class="ty-column{$columns}">
                            <div class="ty-user-empty">
                                <span class="ty-user-empty__text">{__("empty")}</span>
                            </div>
                        </div>
                    {/section}
                {/if}
            {/foreach}
        {/strip}
    </div>
{/if}

{capture name="mainbox_title"}{$title}{/capture}