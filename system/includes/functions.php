<?php

function make_clickable($text) {
    $ret = ' ' . $text;
    $ret = preg_replace("#(^|[\n ])([\w]+?://[^ \"\n\r\t<]*)#is", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $ret);
    $ret = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r<]*)#is", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $ret);
    $ret = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);
    $ret = preg_replace("#(^|[\n ])(([a-z0-9_-]+)\.([a-z0-9_-]+)\.([a-z0-9_-]+)[^ \"\t\n\r\)<]*)#is", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $ret);
    $ret = substr($ret, 1);
    return($ret);
}

function allowed($expected) {
    if ($_SERVER['REQUEST_METHOD'] != $expected) {
        return false;
    } else {
        return true;
    }  
}

function client_ip() {
    if (getenv('HTTP_CLIENT_IP')) {
      $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
      $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('HTTP_X_FORWARDED')) {
      $ip = getenv('HTTP_X_FORWARDED');
    } elseif (getenv('HTTP_FORWARDED_FOR')) {
      $ip = getenv('HTTP_FORWARDED_FOR');
    } elseif (getenv('HTTP_FORWARDED')) {
      $ip = getenv('HTTP_FORWARDED');
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }
    if(empty($ip)){
      $ip = '00.000.000.000';
    }
    
  return $ip;
}

// Echo
function __($s) {  
  global $lang;
  
  echo $lang[$s] ? $lang[$s] : "___" . $s . "___";  
}

// Get
function __g($s) {
  global $lang;
  
  return $lang[$s] ? $lang[$s] : "___" . $s . "___";  
}