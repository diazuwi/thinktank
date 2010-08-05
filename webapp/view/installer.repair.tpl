{include file="_installer.header.tpl"}
  <div id="installer-page" class="container_24 round-all">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
        <h2 class="clearfix step_title">Repairing</h2>
        {$info}
        {if $posted}
          {if $succeed}
          <div style="margin-bottom: 20px;">
            <p class="success"><strong>Repairs complete</strong>. Please remove <code>$THINKUP_CFG['repair'] = true;</code>
              from config.inc.php to prevent this page from being used by unauthorized users.
              {if $username && password}
                Your newly created admin user: <strong>{$username}</strong>, password:
                <strong>{$password}</strong>
              {/if}
            </p>
          </div>
          <div class="clearfix">
            {foreach from=$messages_db item=msg}
              {$msg}
            {/foreach}
            {foreach from=$messages_admin item=msg}
              {$msg}
            {/foreach}
          </div>
          {else}
          <div class="clearfix error_message">
            <strong>Ups!</strong> Something goes wrong, read the hints below!
          </div>
          <div class="clearfix">
            {foreach from=$messages_db item=msg}
              {$msg}
            {/foreach}
            {foreach from=$messages_admin item=msg}
              {$msg}
            {/foreach}
            {foreach from=$messages_error item=msg}
              {$msg}
            {/foreach}
          </div>
          {/if}
        {elseif $show_form}
        <form class="input" name="form1" method="post" action="{$action_form}">
          {if $admin_form}          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Your E-mail</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="site_email" id="site_email"{if isset($site_email)} value="{$site_email}"{/if}>
              <span class="input_information">This will be the email address of the ThinkUp site administrator.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Password</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="password" name="password" id="password"{if isset($password)} value="{$password}"{/if}>
              <span class="input_information">This will be the password of the thinkup site administrator.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_6 prefix_2 right">
              <label>Confirm Password</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="password" name="confirm_password" id="confirm_password"{if isset($confirm_password)} value="{$confirm_password}"{/if}>
            </div>
          </div>
          {/if}
          
          <div class="clearfix append_20">
            <div class="grid_10 prefix_7 left">
              <input type="submit" name="repair" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="Repair &raquo">
            </div>
          </div>
        </form>
        {else}
        <p>There are three options of repairing:</p>
        <ul>
          <li><a href="repair.php?db=1">Repair database</a></li>
          <li><a href="repair.php?admin=1">Create admin user</a></li>
          <li><a href="repair.php?db=1&admin=1">Repair and create admin user</a></li>
        </ul>
        {/if}
      </div>
    </div>
  </div>
</body>
</html>
{include file="_installer.footer.tpl"}
