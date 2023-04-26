<div class="controls" id="robokassa_payment_method_div">
    <select name="payment_data[processor_params][payment_method]" id="robokassa_payment_method">
        {foreach $robokassa_currencies as $group_name => $cur_names}
            <optgroup label="{$group_name}">
                {foreach $cur_names as $cur_code => $cur_name}
                    <option value="{$cur_code}" {if $processor_params.payment_method === $cur_code}selected="selected"{/if}>{$cur_name}</option>
                {/foreach}
            </optgroup>
        {foreachelse}
            <option value="--">--</option>
        {/foreach}
    </select>
<!--robokassa_payment_method_div--></div>
