<?php if (!@file_get_contents(ABSPATH . "/_config.php")): 

error_reporting(0);

/****************************
 *CHECK IF DB IS UP AND HAVE PRIVILEGE 
 */ 
 
function check_permissions() {
  global $db, $db_host, $db_username, $db_password, $db_name, $db_prefix, $db_charset;

  @file_put_contents(ABSPATH . "/_config.php", "");

  if (!is_writable(ABSPATH . "/_config.php")) {
    if (!chmod(ABSPATH . "/_config.php", 0666)) {
      return "Cannot change permissions of config.php <br /> You must do it manually: set chmod 0666 for file system/_config.php";
    };
  }

  $db = @new mysqli($db_host, $db_username, $db_password, $db_name);
  if ($db->connect_error) return "Error: Check database connection informations!";
  if (!$db->set_charset($db_charset)) return "Error: Check database charset!";
      
  return "done";
}
  
function install_db() {
  global $db, $db_host, $db_username, $db_password, $db_name, $db_prefix, $db_charset;
    
  /* Attachments */
  $db->query("
    CREATE TABLE IF NOT EXISTS `". $db_prefix ."attachments` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `file` varchar(255) NOT NULL,
      `size` int(11) NOT NULL,
      `author` int(11) NOT NULL,
      `datetime` datetime NOT NULL,
      `session_id` int(11) NOT NULL,
      UNIQUE KEY `id` (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=". $db_charset ." AUTO_INCREMENT=1 ;
  ");
  
  /* Messages */
  $db->query("
    CREATE TABLE IF NOT EXISTS `". $db_prefix ."messages` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `type` enum('room','private') NOT NULL,
      `from_id` int(11) NOT NULL,
      `to_id` int(11) NOT NULL,
      `time` datetime NOT NULL,
      `text` longtext NOT NULL,
      `attachment_id` int(11) DEFAULT NULL,
      `session_id` int(11) NOT NULL,
      `seen` tinyint(1) DEFAULT NULL,
      UNIQUE KEY `id` (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=". $db_charset ." AUTO_INCREMENT=1 ;
  ");
  
  /* Rooms */
  $db->query("
    CREATE TABLE IF NOT EXISTS `". $db_prefix ."rooms` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      UNIQUE KEY `id` (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=". $db_charset ." AUTO_INCREMENT=1 ;
  ");
  
  /* Sessions */
  $db->query("
    CREATE TABLE IF NOT EXISTS `". $db_prefix ."sessions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `session_id` varchar(64) NOT NULL,
      `ip` varchar(64) NOT NULL,
      `remember` tinyint(1) NOT NULL,
      `activity` datetime NOT NULL,
      `login_time` datetime NOT NULL,
      `logout_time` datetime DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=". $db_charset ." AUTO_INCREMENT=1 ;
  ");
    
  /* Users */
  $db->query("
    CREATE TABLE IF NOT EXISTS `". $db_prefix ."users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `login` varchar(32) NOT NULL,
      `password` varchar(32) NOT NULL,
      `level` enum('administrator','member') NOT NULL,
      `registered` datetime NOT NULL,
      `ban` tinyint(1) DEFAULT NULL,
      `avatar` varchar(255) DEFAULT NULL,
      `sounds` tinyint(1) NOT NULL,
      `visible` tinyint(1) NOT NULL,
      `status` text NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=". $db_charset ." AUTO_INCREMENT=1 ;
  ");
  
  /* Users profile */
  $db->query("
    CREATE TABLE IF NOT EXISTS `". $db_prefix ."users_profile` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `name_slug` varchar(255) NOT NULL,
      `value` varchar(255) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=". $db_charset ." AUTO_INCREMENT=1 ;
  ");
  
  // END OF INSTALLING
}

function create_user() {
  global $db, $db_prefix, $user_login, $user_password;
  
  $db->query("
    INSERT INTO `". $db_prefix ."users` (`login`, `password`, `level`, `registered`, `ban`, `avatar`, `sounds`, `visible`, `status`) 
      VALUES ('". $user_login ."', '". md5($user_password) ."', 'administrator', NOW(), NULL, NULL, '1', '1', '');
  ");
}

function make_config() {
  global $db, $db_host, $db_username, $db_password, $db_name, $db_prefix, $db_charset;
  
$file = '<?php

  define("DB_HOST",     "'.$db_host.'");
  define("DB_USER",     "'.$db_username.'");
  define("DB_PASSWORD", "'.$db_password.'");
  define("DB_NAME",     "'.$db_name.'");
  define("DB_PREFIX",   "'.$db_prefix.'");
  define("DB_CHARSET",  "'.$db_charset.'");

  define("SESSIONS_TABLE",      DB_PREFIX . "sessions");
  define("USERS_TABLE",         DB_PREFIX . "users");
  define("USERS_PROFILE_TABLE", DB_PREFIX . "users_profile");
  define("ROOMS_TABLE",         DB_PREFIX . "rooms");
  define("MESSAGES_TABLE",      DB_PREFIX . "messages");
  define("ATTACHMENTS_TABLE",   DB_PREFIX . "attachments");

  define("DEBUG",               false);
  define("STORAGE_DIR",         ABSPATH . "/storage");
  define("AVATARS_DIR",         ABSPATH . "/../avatars");
  define("AVATARS_DIR_OUTSIDE", "./avatars");
  define("DEFAULT_AVATAR",      "./img/default-avatar.png");

  if(!$settings = json_decode( file_get_contents(ABSPATH . "/_settings.json") )) die("Error in _settings.json");
  $settings->profile_items = @json_decode("{" . $settings->profile_items_enc . "}");

  header("Content-Type: text/html; charset=utf-8");
  ini_set("display_errors", (boolean) DEBUG);
  if (DEBUG)
      error_reporting(E_ERROR | E_WARNING | E_PARSE); //or -1
    else
      error_reporting(0);

  $db = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  if ($db->connect_error) die("DB CONNECTION ERROR! Error " . $db->connect_errno . ": ". $db->connect_error);
  if (!$db->set_charset(DB_CHARSET)) die("DB CAHRSET ERROR! Error " . $db->connect_errno . " : ". $db->connect_error);
';

  file_put_contents(ABSPATH . "/_config.php", $file);
}

if ($_POST["action"] == "install") {

  $db_host = $_POST["db_host"];
  $db_username = $_POST["db_username"];
  $db_password = $_POST["db_password"];
  $db_name = $_POST["db_name"];
  $db_prefix = $_POST["db_prefix"];
  $db_charset = $_POST["db_charset"];
  
  $user_login = $_POST["user_login"];
  $user_password = $_POST["user_password"];  
  
  $check_permissions = check_permissions();
  
  if (!$db_host || !$db_username || !$db_name || !$db_prefix || !$db_charset || !$user_login || !$user_password) { 
    $error_msg = "Check all form items!"; 
  } elseif ($check_permissions != "done") {
    $error_msg = $check_permissions;  
  } else {
    install_db();
    create_user();
    make_config();
    
    header('Location: '.$_SERVER['REQUEST_URI']); exit;  
  }
  

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Blažej Krajňák" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Messaging system - installation</title>
    
    <!--[if lt IE 9]>
      <script src="assets/js/html5shiv.js"></script>
    <![endif]-->
   
    <link rel="shortcut icon" href="./img/favicon.png" />
    <link rel="stylesheet" type="text/css" href="./css/reset.css" />
    <link rel="stylesheet" type="text/css" href="./css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="./css/bootstrap-responsive.min.css" />
    <style type="text/css">
      body {
        background-color: #f5f5f5;
      }
      
      h1,h2 {
        text-align: center;
      }
      
      h3 {
        margin: 10px 0 0 0;
      }
      
      form {
        margin: 20px auto;
        width: 550px;
      }
    </style>
</head>
<body>

  <div class="container">
  
  
  
<h1>Messaging system</h1>
<h3 style="color: red; text-align: center;"><?php echo $error_msg;?></h3>
  
<form class="form-horizontal" method="POST" action="">
  <input type="hidden" name="action" value="install">
  <!--DATABASE-->
  <div class="control-group">
    <div class="controls">
      <h3>Database</h3>  
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="input-db-host">Server</label>
    <div class="controls">
      <input type="text" id="input-db-host" name="db_host" value="<?php echo $db_host;?>">
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="input-db-username">Username</label>
    <div class="controls">
      <input type="text" id="input-db-username" name="db_username" value="<?php echo $db_username;?>">
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="input-db-password">Password</label>
    <div class="controls">
      <input type="password" id="input-db-password" name="db_password" value="<?php echo $db_password;?>">
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="input-db-name">Database name</label>
    <div class="controls">
      <input type="text" id="input-db-name" name="db_name" value="<?php echo $db_name;?>">
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="input-db-prefix">Tables prefix</label>
    <div class="controls">
      <input type="text" id="input-db-prefix" name="db_prefix" value="<?php echo $db_prefix ? $db_prefix : 'msg_';?>">
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="input-db-charset">Database charset</label>
    <div class="controls">
      <input type="text" id="input-db-charset" name="db_charset" value="<?php echo $db_charset ? $db_charset : 'utf8';?>">
    </div>
  </div>
  
  <!-- FIRST ADMIN-->
  <div class="control-group">
    <div class="controls">
      <h3>First administrator</h3>  
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="input-user-login">Login</label>
    <div class="controls">
      <input type="text" id="input-user-login" name="user_login" value="<?php echo $user_login;?>">
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="input-user-password">Password</label>
    <div class="controls">
      <input type="text" id="input-user-password" name="user_password" value="<?php echo $user_password;?>">
    </div>
  </div>
  
  
  
  <div class="control-group">
    <div class="controls">
      <button type="submit" class="btn">Go install!</button>
    </div>
  </div>
</form>  
  
  
  
  
  
    
  </div>
  
</body>
</html>
<?php exit; endif;  ?>