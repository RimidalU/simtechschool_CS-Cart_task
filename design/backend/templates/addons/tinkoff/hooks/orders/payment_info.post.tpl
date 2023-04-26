{if $is_show_transfer_funds_button && $is_tinkoff_payment}
    <div class="control-group">
        {include file="buttons/button.tpl"
        but_text=__("addons.tinkoff.transfer_funds")
        but_role="action"
        but_href="tinkoff.transfer_funds?order_id=`$order_info.order_id`&redirect_url=`$config.current_url|escape:url`"
        but_meta="btn cm-post"
        }
    </div>
{/if}