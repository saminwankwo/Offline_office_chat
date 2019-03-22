<?php

class user {

  var $logged = false;
  var $user_id = 0;
  var $ip = false;
  var $session_id = 0;
  var $level = false;
  
  function __construct() {
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_trans_sid', 0);
    ini_set('session.cookie_httponly', true);
    $this->start_session();
    $this->init_auth();  
  }
  
  function start_session() {
    session_start();
    $this->session_id = session_id();
    $this->ip = client_ip();
  }

  function init_auth() {
    global $db;
    
    $session_query = $db->query('
      SELECT user.*, session.id AS sid
      FROM `'.SESSIONS_TABLE.'` AS session
      JOIN `'.USERS_TABLE.'` AS user ON user.id = session.user_id
      WHERE session.`session_id` = "'.$this->session_id.'" AND
            session.`ip` = "'.$this->ip.'" AND
            session.`logout_time` is NULL AND
            user.ban is NULL AND 
            (
              (
                session.`remember` = "0" AND 
                session.`activity` + INTERVAL '. 60*60 .' SECOND > NOW()
              )
                OR
              (
                session.`remember` = "1" AND 
                session.`activity` + INTERVAL '. 60*60*24*365 .' SECOND > NOW() 
              )              
            )
      LIMIT 1
    ');
    
    echo $db->error;
    
    $this->logged = $session_query->num_rows ? true : false;
    
    if ($this->logged) {
      $data = $session_query->fetch_object();
      $this->sid = $data->sid;
      $this->login = $data->login;
      $this->user_id = $data->id;
      $this->level = $data->level;
      if ($this->remember) 
          ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 365);
        else
          ini_set('session.gc_maxlifetime', 60 * 60);

      $db->query('UPDATE `'.SESSIONS_TABLE.'` SET `activity` = NOW() WHERE `id` = "'. $this->sid .'"');    
    }
  }
  
  function login($login, $password, $remember) {
    global $db;
    
    $user_query = $db->query('
      SELECT *
      FROM `'.USERS_TABLE.'`
      WHERE `login` = "'.$login.'" AND
            `password` = "'.md5($password).'"
    ');  
    
    if ($user_query->num_rows) {
      $data = $user_query->fetch_object();
      if ($data->ban) return "banned";
      
      $db->query('
        INSERT INTO `'.SESSIONS_TABLE.'`
        SET `user_id` = "'.$data->id.'",
            `session_id` = "'.$this->session_id.'",
            `ip` = "'.$this->ip.'",
            `remember` = "'.($remember ? 1 : 0).'",
            `activity` = NOW(),
            `login_time` = NOW()
      ');
      
      return "logged";
    } else return "error";
      
  }
  
  function logout() {
    global $db;
    
    if ($this->logged) $db->query('
      UPDATE `'.SESSIONS_TABLE.'`
      SET `logout_time` = NOW()
      WHERE `session_id` = "'.$this->session_id.'"   
    ');
    
    $this->init_auth();
  }
  
  function register($login, $password, $level) {
    global $db;
    
    $match = $db->query('
      SELECT id
      FROM `'.USERS_TABLE.'`
      WHERE `login` = "'.$login.'"
    ')->num_rows;

    if ($match) return "login_exists";
    
    if ($db->query('
      INSERT INTO `'.USERS_TABLE.'`
      SET `login` = "'.$login.'",
          `password` = "'. md5($password) .'",
          `level` = "'. ($level == "administrator" ? "administrator" : "member") .'",
          `registered` = NOW(),
          `ban` = NULL,
          `avatar` = NULL,
          `sounds` = "1",
          `visible` = "1",
          `status` = "" 
    ')) 
        return "ok"; 
    else 
        return "error"; 
  }
  
  function change_password($password_old, $password_new, $user_id) {
    global $db;
    
    if (!$user_id) $user_id = $this->user_id;
    
    if ($this->level != "administrator" && $user_id != $this->user_id) return "not_permitted";
    
    if (!$db->query('
      SELECT id
      FROM `'.USERS_TABLE.'`
      WHERE `password` = "'. md5($password_old) .'" AND
            `id` = "'. $user_id .'"
    ')->num_rows) return "bad_old";
    
    if ($db->query('
      UPDATE `'.USERS_TABLE.'`
      SET `password` = "'. md5($password_new) .'"
      WHERE `id` = "'. $user_id .'"
    ')) return "ok"; else return false;
  }
  
}

$user = new user; 