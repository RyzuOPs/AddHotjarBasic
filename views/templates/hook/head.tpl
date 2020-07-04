{if isset($addhotjarbasic_enabled, $addhotjarbasic_container_id) && $addhotjarbasic_enabled}
{literal}
<!-- Hotjar Basic Tracking Code -->
<script>
    (function(h,o,t,j,a,r){
    h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
    h._hjSettings={hjid:{/literal}{$addhotjarbasic_container_id}{literal},hjsv:6};
    a=o.getElementsByTagName('head')[0];
    r=o.createElement('script');r.async=1;
    r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
    a.appendChild(r);
    })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>
{/literal}
<!-- End of Hotjar Basic Tracking Code -->
{/if}
