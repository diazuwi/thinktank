<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="utf-8">
  <title>ThinkTank :: {$subtitle}</title>
  <link rel="shortcut icon" type="image/x-icon" href="{$favicon}">
  
  <!-- jquery -->
  <link type="text/css" rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css">
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>

  <!-- custom css -->
  <link type="text/css" rel="stylesheet" href="{$base_url}assets/css/base.css">
  <link type="text/css" rel="stylesheet" href="{$base_url}assets/css/positioning.css">
  <link type="text/css" rel="stylesheet" href="{$base_url}assets/css/style.css">
  <link type="text/css" rel="stylesheet" href="{$base_url}assets/css/installer.css">
</head>
<body>
  <div id="status-bar">&nbsp;</div>
  <div class="container clearfix">
    <div id="app-title">
          <h1><span class="bold">Think</span><span class="gray">Up</span></h1>
          <h2>New ideas</h2>
    </div>
  </div>
  <div id="installer-die" class="container_24 round-all">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
        <h2 class="clearfix error_title">{$subtitle}</h2>
        {$message}
      </div>
    </div>
  </div>
</body>
</html>