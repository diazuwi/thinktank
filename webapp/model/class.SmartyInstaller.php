<?php
class SmartyInstaller extends Smarty {
  function SmartyInstaller() {
    $this->Smarty();
    $this->template_dir = array(THINKTANK_WEBAPP_PATH . 'view');
    $this->compile_dir = THINKTANK_WEBAPP_PATH . 'view/compiled_view/';
    $this->caching = FALSE;
  }
}
?>