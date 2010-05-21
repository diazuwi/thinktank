{include file="_installer.header.tpl"}
  <div id="installer-page" class="container_24 round-all">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
        <form class="input" name="form1" method="post" action="index.php?step=4">
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Site Name</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="site_name" id="db_name"{if isset($site_name)} value="{$site_name}"{/if}>
              <span class="input_information">The name of your ThinkTank site.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Your E-mail</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="site_email" id="site_email"{if isset($site_email)} value="{$site_email}"{/if}>
              <span class="input_information">This will be the email address of the ThinkTank site administrator.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_10 prefix_9 left">
              <input type="submit" name="Submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="Next Step &raquo">
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
{include file="_installer.footer.tpl"}