<?php

  define("DB_HOST",     "localhost");
  define("DB_USER",     "nice");
  define("DB_PASSWORD", "nice12345");
  define("DB_NAME",     "nice");
  define("DB_PREFIX",   "msg_");
  define("DB_CHARSET",  "utf8");

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
