<?php
class SmartyInstaller extends Smarty {
  function SmartyInstaller() {
    $this->Smarty();
    $this->template_dir = array(THINKTANK_WEBAPP_PATH . 'view');
    $this->compile_dir = THINKTANK_WEBAPP_PATH . 'view' . DS . 'compiled_view' . DS;
    $this->caching = FALSE;
  }
}
?>