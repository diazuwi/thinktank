{include file="_installer.header.tpl"}
  <div class="container">
    <div id="thinkup-tabs">
      <div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
        <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
          <li id="step-tab-1" class="ui-state-default ui-corner-top">
            <div class="key-stat install_step">
            <h1><span class="pass_step" id="pass-step-1">1</span></h1>
            <h3>Requirements Check</h3>
            </div>  
          </li>
          <li id="step-tab-2" class="ui-state-default ui-corner-top">
            <div class="key-stat install_step">
            <h1><span class="pass_step" id="pass-step-2">2</span></h1>
            <h3>Database Setup and Site Configuration</h3>
            </div>
          </li>
          <li id="step-tab-3" class="no-border ui-state-default ui-corner-top ui-tabs-selected ui-state-active">
            <div class="key-stat install_step">
            <h1>{if empty($errors)}<span class="pass_step" id="pass-step-3">3</span>{else}3{/if}</h1>
            <h3>Finish</h3>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
  
  <div id="installer-page" class="container_24 round-all">
    <img id="dart3" class="dart" alt="" src="/assets/img/dart_wht.png">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
          <p class="success" style="margin-bottom: 30px">
            <strong>Congrulations!</strong> ThinkUp has been installed.
            Please login with account provided below. This account information
            also already sent to {$username}
          </p>
          {if !empty($errors)}
          <div class="clearfix error_message">
            <strong>Ups!</strong> there are something you need to do:
            <ul>
            {foreach from=$errors item=error}
              <li>{$error}</li>
            {/foreach}
            </ul>
          </div>
          {/if}
        
          {if $username}
          <div class="clearfix append_20">
            <div class="grid_6 prefix_5 right">
              <span class="label">Username</span>
            </div>
            <div class="grid_8 prefix_1 left">
              <span class="value">{$username}</span>
            </div>
          </div>
          
          
          <div class="clearfix append_20">
            <div class="grid_6 prefix_5 right">
              <span class="label">Password</span>
            </div>
            <div class="grid_8 prefix_1 left">
              <span class="value">{$password}</span>
            </div>
          </div>
          {/if}
          
          <div class="clearfix append_20">
            <div class="grid_10 prefix_8 left">
              <div class="next_step tt-button ui-state-default ui-priority-secondary ui-corner-all">
                <a href="{$login_url}">Log In &raquo;</a>
              </div>
            </div>
          </div>
        
      </div>
    </div>
  </div>
</body>
</html>
{include file="_installer.footer.tpl"}