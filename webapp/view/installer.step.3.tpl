{include file="_installer.header.tpl"}
  <div id="installer-page" class="container_24 round-all">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
          <div class="clearfix success_message">
            <strong>Congrulations!</strong> ThinkTank has been installed.
            Please login with account provided below. This account information
            also already sent to admin@diazuwi.web.id
          </div>
        
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