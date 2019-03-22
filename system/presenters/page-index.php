<!DOCTYPE html>
<html lang="en">
<head>
   <meta http-equiv="content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="author" content="Blažej Krajňák" />
   <title><?php echo $settings->site_name;?></title>
   
    <!--[if lt IE 9]>
        <script src="./js/html5shiv.min.js"></script>
    <![endif]-->
   
    <link rel="shortcut icon" href="./img/favicon.png" />
    <?php if (!$settings->direction_rtl): ?>
      <link rel="stylesheet" type="text/css" href="./css/bootstrap.min.css" />
      <link rel="stylesheet" type="text/css" href="./css/bootstrap-responsive.min.css" />
    <?php else: ?>
      <link rel="stylesheet" type="text/css" href="./css/bootstrap-rtl.min.css" />
      <link rel="stylesheet" type="text/css" href="./css/bootstrap-responsive-rtl.min.css" />
    <?php endif;?>
    <link rel="stylesheet" type="text/css" href="./css/index-page.css" />
    <script src="./js/jquery.v2.0.2.min.js" type="text/javascript"></script>
    <script src="./js/fn.extends.js" type="text/javascript"></script>
    <script src="./js/bootstrap.min.js" type="text/javascript"></script>
    <script src="./js/system.page-index.js" type="text/javascript"></script>
    <script type="text/javascript">
        var filename = "<?php echo basename($_SERVER["SCRIPT_NAME"]);?>";
    </script>
</head>
<body>

<div class="container">
  <h1><?php echo $settings->site_name;?></h1>

  <div class="alert alert-error" <?php echo (!$alert ? 'style="display: none;"' : ''); ?>>
    <button type="button" class="close" onclick="$(this).parent().hide(0);">&times;</button>
    <strong><?php __("warning");?></strong> <span id="alert_text"><?php echo $alert;?></span>
  </div> 
  
  <ul class="nav nav-tabs">
    <li class="active"><a href="#tab_login" data-toggle="tab"><?php __("login");?></a></li>
    <?php if ($settings->enabled_register): ?><li><a href="#tab_register" data-toggle="tab"><?php __("register");?></a></li><?php endif; ?>
  </ul>
 
  <div class="tab-content">
    <div class="tab-pane active" id="tab_login">
      <form method="post" action="ajax.php" id="login" onsubmit="send_form('login'); return false;">
          <input type="hidden" name="action" value="login">
          <h2 class="form-signin-heading"><?php __("please_sign_in");?></h2>
          <input type="text" name="login" class="input-block-level" placeholder="<?php __("login");?>">
          <input type="password" name="password" class="input-block-level" placeholder="<?php __("password");?>">
          <label class="checkbox">
            <input type="checkbox" name="remember"> <?php __("remember_me");?>
          </label>
          <button class="btn btn-large btn-primary" type="submit"><?php __("sign_in");?></button>
        </form>    
    </div>
    
    <?php if ($settings->enabled_register): ?>  
    <div class="tab-pane" id="tab_register">
      <form method="post" action="ajax.php" id="register" onsubmit="send_form('register'); return false;">
          <input type="hidden" name="action" value="register">
          <h2 class="form-signin-heading"><?php __("create_member");?></h2>
          <input type="text" name="login" class="input-block-level" placeholder="<?php __("login");?>">
          <input type="password" name="password" class="input-block-level" placeholder="<?php __("password");?>">
          <input type="password" name="password_repeat" class="input-block-level" placeholder="<?php __("password_repeat");?>">
        
          <button class="btn btn-large btn-primary" type="submit"><?php __("register");?></button>
        </form>  
    </div>
    <?php endif; ?>
    
  </div>
</div>

</body>
</html>