{include file="_installer.header.tpl"}
  <div id="installer-page" class="container_24 round-all">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
        <div class="clearfix append_20">
          <div class="grid_6 prefix_5 right"><span class="label">PHP Version >= 5.2</span></div>
          <div class="grid_8 prefix_1 left">
            {if $php_compat}
            <span class="value yes">Yes</span>
            {else}
            <span class="value no">No</span>
            {/if}
          </div>
        </div>
        <div class="clearfix append_20">
          <div class="grid_6 prefix_5 right"><span class="label">cURL enabled</span></div>
          <div class="grid_8 prefix_1 left">
            {if $libs.curl}
            <span class="value yes">Yes</span>
            {else}
            <span class="value no">No</span>
            {/if}
          </div>
        </div>
        <div class="clearfix append_20">
          <div class="grid_6 prefix_5 right"><span class="label">GD lib installed</span></div>
          <div class="grid_8 prefix_1 left">
            {if $libs.gd}
            <span class="value yes">Yes</span>
            {else}
            <span class="value no">No</span>
            {/if}
          </div>
        </div>
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
        {if $permission.logs && $permission.compiled_view && $permission.cache}
        <div class="clearfix">
          <div class="grid_10 prefix_8 left">
            <div class="next_step tt-button ui-state-default ui-priority-secondary ui-corner-all">
              <a href="index.php?step=2">Next Step &raquo;</a>
            </div>
          </div>
        </div>
        {else}
        <div class="clearfix append_20 error_message">
        <p>Make sure the following directories are writeable by the web server:</p>
        <p><code>{$writeable_directories.logs}</code></p>
        <p><code>{$writeable_directories.compiled_view}</code></p>
        <p><code>{$writeable_directories.cache}</code></p>
        <p class="prepend_20">If you have command line (SSH) access to your web server then you can simply copy and paste the following command into your shell:</p>
        <p><code>chmod -R 777 {$writeable_directories.logs} {$writeable_directories.compiled_view} {$writeable_directories.cache}</code></p>
        </div>
        {/if}
      </div>
    </div>
  </div>
</body>
</html>
{include file="_installer.footer.tpl"}