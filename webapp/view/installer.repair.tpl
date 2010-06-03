{include file="_installer.header.tpl"}
  <div id="installer-page" class="container_24 round-all">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
        <h2 class="clearfix step_title">Repairing</h2>
        {if $succeed}
        <div class="clearfix success_message">
          <strong>Great!</strong> Your system is met ThinkTank's requirements.
          Click on the <strong>Next Step &raquo;</strong> button below to proceed the next step
        </div>
        {else}
        <div class="clearfix error_message">
          <strong>Ups!</strong> Something goes wrong, read the hints below!
        </div>
        {/if}
        
        {if $repair.db}
        <div class="clearfix append_20">
          <div class="grid_6 prefix_5 right">
            {if $permission.logs && $permission.compiled_view && $permission.cache}
            <span class="label">Template and Log directories are writeable?</span>
            {else}
            <span class="label no">Template and Log directories are writeable?</span>
            {/if}
          </div>
          <div class="grid_8 prefix_1 left">
            {if $permission.logs && $permission.compiled_view && $permission.cache}
            <span class="value yes">Yes</span>
            {else}
            <span class="value no">No</span>
            {/if}
          </div>
        </div>
        {/if}
        
      </div>
    </div>
  </div>
</body>
</html>
{include file="_installer.footer.tpl"}