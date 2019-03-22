<?php

  function upload_attachment() {
    global $db, $settings, $user;
    if ($_FILES["file"]["error"] != UPLOAD_ERR_OK) return array("status" => "upload_error");

    $size = $_FILES["file"]["size"];
    $real_name = $_FILES["file"]["name"];
    $name = "attachment-" . $user->user_id . "-" . time() . "-" . rand(0009,9999);
    
    if ($_FILES["file"]["size"] > ($settings->attachment_max_size*1024*1024)) return array("status" => "too_big");
    if (!@move_uploaded_file($_FILES["file"]["tmp_name"], STORAGE_DIR . "/" . $name)) return array("status" => "upload_error");
    if (!file_exists(STORAGE_DIR . "/" . $name)) return array("status" => "upload_error");
  

    if (!$db->query('
      INSERT INTO `'. ATTACHMENTS_TABLE .'`
      SET `name` = "'. $real_name .'",
          `file` = "'. $name .'",
          `size` = "'. $size .'",
          `author` = "'. $user->user_id .'",
          `datetime` = NOW(),
          `session_id` = "'. $user->sid .'"
    ')) return array("status" => "inserting_error");
      
    return array("status" => "ok", "file" => $real_name, "size" => round($size/1024/1024,2), "attachment_id" => $db->insert_id);
  }
  
  
  function upload_avatar($user_id = false) {     
    global $db, $settings, $user;
    if ($_FILES["file"]["error"] != UPLOAD_ERR_OK) return array("status" => "upload_error");

    if ($type == "avatar" && !is_numeric($user_id)) return array("status" => "unknown_user");
    if ($type == "avatar" && ($user->level != "administrator" or $user->user_id != $user_id)) return array("status" => "not_permitted");
    
    $the_user = $db->query('
      SELECT id
      FROM `'. USERS_TABLE .'`
      WHERE `id` = "'. $user_id .'"  
    ');
    
    if (!$the_user->num_rows) return array("status" => "unknown_user");
    
    // Go upload!
    
    $size = $_FILES["file"]["size"];
    $real_name = $_FILES["file"]["name"];
    $name = $user_id . "-" . time() . ".jpg";
    $tmp_file = $_FILES["file"]["tmp_name"];


    if ($_FILES["file"]["size"] > ($settings->attachment_max_size*1024*1024)) return array("status" => "too_big");
    
    $image_type = getimagesize($tmp_file);
    $image_type = $image_type[2];
      
    if( $image_type == IMAGETYPE_JPEG ) $avatar = imagecreatefromjpeg($tmp_file);
    elseif( $image_type == IMAGETYPE_GIF ) $avatar = imagecreatefromgif($tmp_file);
    elseif( $image_type == IMAGETYPE_PNG ) $avatar = imagecreatefrompng($tmp_file);
    elseif( $image_type == IMAGETYPE_BMP) $avatar = imagecreatefromwbmp($tmp_file);
    else return array("status" => "invalid");


    list($width, $height) = getimagesize($tmp_file);

    // Calculating
    if ($width > $height) {
      $y = 0;
      $x = ($width - $height) / 2;
      $smallestSide = $height;
    } else {
      $x = 0;
      $y = ($height - $width) / 2;
      $smallestSide = $width;
    }
    
    // Sizing
    $thumb_size = 160;
    $thumb = imagecreatetruecolor($thumb_size, $thumb_size); 
    imagecopyresampled($thumb, $avatar, 0, 0, $x, $y, $thumb_size, $thumb_size, $smallestSide, $smallestSide);

    // Save
    imagejpeg($thumb, AVATARS_DIR . "/" . $name, 100);
    
    if (!$db->query('
      UPDATE `'. USERS_TABLE .'`
      SET `avatar` = "'. $name .'"
      WHERE `id` = "'. $user_id .'"
    ')) return array("status" => "inserting_error");  
      
    return array("status" => "ok", "file" => AVATARS_DIR_OUTSIDE . "/" . $name);
      
  } 
  
  if ($_GET["type"] == "attachment") $result = upload_attachment();
  elseif ($_GET["type"] == "avatar") $result = upload_avatar($_GET["user_id"]);
  
  echo json_encode($result);