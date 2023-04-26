{if $addons.gdpr.gdpr_cookie_consent !== 'none'}
    <script 
        data-no-defer
        type="text/javascript"
        src="{$config.current_location}/{$config.dir.files|fn_get_rel_dir}gdpr/klaro/config.js">
    </script>
    <script 
        data-no-defer
        data-klaro-config="klaroConfig"
        data-config="klaroConfig"
        type="text/javascript"
        src="{$config.current_location}/js/addons/gdpr/lib/klaro.js">
    </script>
{/if}
