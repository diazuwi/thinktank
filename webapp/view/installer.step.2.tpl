{include file="_installer.header.tpl"}
  <div id="installer-page" class="container_24 round-all">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
        <form class="input" name="form1" method="post">
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Database Name</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="db_name" id="db_name"{if isset($db_name)} value="{$db_name}"{/if}>
              <span class="input_information">The name of the MySQL database your ThinkTank data will be stored in.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>User Name</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="db_user" id="db_user"{if isset($db_user)} value="{$db_user}"{/if}>
              <span class="input_information">Your MySQL username.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Password</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="db_user" id="db_passwd"{if isset($db_passwd)} value="{$db_passwd}"{/if}>
              <span class="input_information">Your MySQL password.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Database Host</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="db_host" id="db_host"{if isset($db_host)} value="{$db_host}"{/if}>
              <span class="input_information">This is usually <strong>localhost</strong> or a host name provided by the hosting provide.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Table Prefix</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="db_prefix" id="db_prefix"{if isset($db_prefix)} value="{$db_prefix}"{/if}>
              <span class="input_information">Prefix of your table name</span>
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