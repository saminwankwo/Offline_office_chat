<?php

/***********************************************************************************
 * This file contain all functions called by XHR requests
 */

function ac_login($data) {
  global $db, $user;

  if ($user->logged) return array("status" => 1);

  $login = $db->real_escape_string($data["login"]);
  $password = $db->real_escape_string($data["password"]);
  $remember = ($data["remember"] == "on") ? true : false;

  if (!$login or !$password) return array("status" => 0, "alert" => __g("check_empty_fields"));

  $status = $user->login($login, $password, $remember);

  if ($status == "logged") return array("status" => 1);
  elseif ($status == "banned") return array("status" => 0, "alert" => __g("this_account_has_ban"));
  elseif ($status == "error") return array("status" => 0, "alert" => __g("check_login_and_password"));
}

$actions["login"] = array(false, false, false, "ac_login");

function ac_register($data) {
  global $db, $user;

  $login = $db->real_escape_string($data["login"]);
  $password = $db->real_escape_string($data["password"]);
  $password_repeat = $db->real_escape_string($data["password_repeat"]);

  if (!$login or !$password or !$password_repeat) return array("status" => 0, "alert" => __g("check_empty_fields"));
  if (!preg_match("/^[a-z,0-9]{3,15}+$/", $login)) return array("status" => 0, "alert" => __g("login_contain"));
  if ($password != $password_repeat) return array("status" => 0, "alert" => __g("passwords_not_match"));

  $status = $user->register($login, $password, "member");

  if ($status == "ok") {
      $user->login($login, $password, true);
      return array("status" => 1);
  } elseif ($status == "login_exists") {
      return array("status" => 0, "alert" => __g("login_is_already_using"));
  } else {
      return array("status" => 0, "alert" => __g("internal_error"));
  }
}

$actions["register"] = array("no", false, "enabled_register", "ac_register");

function ac_session_update() {
  global $user;
  return array("logged" => $user->logged ? "1" : "0");
}

$actions["session_update"] = array(false, false, false, "ac_session_update");


function ac_states($data) {
  global $db, $user;

      if ($data["to_change"] == "sounds") $to_change = "sounds";
  elseif ($data["to_change"] == "visible") $to_change = "visible";
  $value = $data["value"] ? true : false;

  if ($to_change) {
    $db->query('UPDATE `'.USERS_TABLE.'` SET `'.$to_change.'` = "'. ($value ? 1 : 0) .'" WHERE `id` = "'. $user->user_id .'"');
  }

  $states = $db->query('SELECT sounds, visible FROM `'.USERS_TABLE.'` WHERE `id` = "'. $user->user_id .'"')->fetch_object();
  return array("sounds" => $states->sounds ? 1 : 0, "visible" => $states->visible ? 1 : 0);
}

$actions["states"] = array("yes", false, false, "ac_states");

function ac_rooms_list($data) {
  global $db, $settings;

  $data = $data["rooms"];
  if (!$data) $full_reload = true;

  $rooms_query = $db->query('
    SELECT room.*,
      (SELECT MAX(msg.id)
       FROM `'.MESSAGES_TABLE.'` AS msg
       WHERE msg.to_id = room.id AND
             msg.type = "room"
      ) AS last_id
    FROM `'.ROOMS_TABLE.'` AS room
  ');

  while ($room = $rooms_query->fetch_object()):
    $rooms[$room->id] = array(
      "id" => $room->id,
      "name" => $room->name,
      "last_id" => $room->last_id,
      "unread" => (($data[$room->id] and $data[$room->id] < $room->last_id) ? 1 : 0)
    );
  endwhile;

  if (!$full_reload) if ( !$data or !$rooms or (count($data) != count($rooms)) or (count($rooms) != count(@array_intersect_key($data, $rooms))) ) $full_reload = true;


  if ($full_reload) {
    $result["type"] = "upgrade";
    $result["data"] = $rooms;
  } else {
    $result["type"] = "update";

    foreach($rooms AS $room):
      if ($data[$room["id"]] < $room["last_id"]) {
        $result["data"][$room["id"]] = $room["last_id"];
      }
    endforeach;
  }

  return $result;
}

$actions["rooms_list"] = array("yes", false, false, "ac_rooms_list");

function ac_update_room($data) {
  global $db, $settings;

  $todo = $data["todo"];
  $room_id = is_numeric($data["room_id"]) ? $data["room_id"] : false;
  $first_id = is_numeric($data["first_id"]) ? $data["first_id"] : false;
  $last_id = is_numeric($data["last_id"]) ? $data["last_id"] : false;
  $result = array("todo" => $todo, "room_id" => $room_id, "data" => false);

  switch($todo) {
    case "select": break;
    case "update":  break;
    case "load_older":  break;
    default: $todo = false; break;
  }

  if (!$todo || !is_numeric($room_id)) return array("status" => "error");

  $sql = '
    SELECT message.id AS id, user.id AS user_id, user.login AS login, user.avatar AS avatar, message.text AS text, message.time AS time, attachment.id AS attachment_id, attachment.name AS attachment_name
    FROM `'.MESSAGES_TABLE.'` AS message
    JOIN `'.USERS_TABLE.'` AS user ON message.from_id = user.id
    LEFT JOIN `'.ATTACHMENTS_TABLE.'` AS attachment ON attachment.id = message.attachment_id
    WHERE message.`type` = "room"
  ';

  if ($todo == "select")
    $sql .= '
      AND message.`to_id` = "'. $room_id .'"
      ORDER BY message.time DESC
      LIMIT 0,'.$settings->messages_per_page.'
    ';

  elseif ($todo == "update" and $last_id)
    $sql .= '
      AND message.`to_id` = "'. $room_id .'" AND
          message.id > '. $last_id .'
      ORDER BY message.time DESC
    ';

  elseif ($todo == "load_older" and $first_id)
    $sql .= '
      AND message.`to_id` = "'. $room_id .'" AND
            message.id < '. $first_id .'
      ORDER BY message.time DESC
      LIMIT 0,'.$settings->messages_per_page.'
    ';
  else return false;

  $query = $db->query($sql);
  $new_first_id = false;
  $new_last_id = false;

  while ($message = $query->fetch_object()):
    if (!$new_last_id) $new_last_id = $message->id;   //set first id
                       $new_first_id = $message->id;  //set last id

    $result["data"][] = array(
      "id" => $message->id,
      "user_id" => $message->user_id,
      "from" => $message->login,
      "avatar" => $message->avatar ? AVATARS_DIR_OUTSIDE . "/" . $message->avatar : DEFAULT_AVATAR,
      "text" => make_clickable(nl2br($message->text)),
      "time" => date($settings->datetime_format, strtotime($message->time)),
      "attachment" => array(
        "id" => $message->attachment_id,
        "name" => $message->attachment_name
      )
    );
  endwhile;

  if ($todo == "select") {$result["first_id"] = $new_first_id; $result["last_id"] = $new_last_id;}
  if ($todo == "update") $result["last_id"] = $new_last_id;
  if ($todo == "load_older") $result["first_id"] = $new_first_id;

  return $result;
}

$actions["update_room"] = array("yes", false, false, "ac_update_room");

function ac_send_message($data) {
  global $db, $user, $settings;
  $room_id = is_numeric($data["room_id"]) ? $data["room_id"] : false;
  $text = $data["text"];
  $attachment_id = is_numeric($data["attachment_id"]) ? $data["attachment_id"] : false;
  if (strlen($text) > $settings->message_max_characters) return array("status" => "long");
  if (!trim($text) or empty($text)) return array("status" => "short");

  if ($room_id) $exists_room = $db->query('SELECT id FROM `'.ROOMS_TABLE.'` WHERE `id` = "'.$room_id.'" ')->num_rows;
  if (!$exists_room) return array("status" => "unknown_room");

  $antispam = $db->query('SELECT time FROM `'.MESSAGES_TABLE.'` WHERE `from_id` = "'. $user->user_id .'" AND `to_id` = "'. $room_id .'" AND `type` = "room" AND `time` > NOW() - INTERVAL '. $settings->next_message_delay .' SECOND LIMIT 1');
  if ($antispam->num_rows) return array("status" => "too_fast");

  if ($attachment_id) $exists_attachment = $db->query('SELECT id FROM `'.ATTACHMENTS_TABLE.'` WHERE `id` = "'.$attachment_id.'" AND `author` = "'.$user->user_id.'" ')->num_rows;

  $db->query('
    INSERT INTO `'.MESSAGES_TABLE.'`
    SET `type` = "room",
        `from_id` = "'. $user->user_id .'",
        `to_id` = "'. $room_id .'",
        `time` = NOW(),
        `text` = "'. $db->real_escape_string(htmlspecialchars(trim($text), ENT_QUOTES)) .'",
        `attachment_id` = '.($exists_attachment ? '"'.$attachment_id.'"' : "NULL").',
        `session_id` = "'. $user->sid .'"
  ');

  return array("status" => "ok");
}

$actions["send_message"] = array("yes", false, false, "ac_send_message");

function ac_users_list($data) {
  global $db, $settings, $user;
  $return = array();

  $users_query = $db->query('
    SELECT user.*, session.activity AS last_activity, session.logout_time
    FROM `'.SESSIONS_TABLE.'` AS session
    JOIN `'.USERS_TABLE.'` AS user ON session.user_id = user.id
    WHERE session.activity + INTERVAL '. $settings->delay_for_offline .' SECOND > NOW() AND
          session.logout_time is NULL AND
          user.id != "'. $user->user_id .'"
    GROUP BY user.id
    ORDER BY session.id
  ');

  while ($row = $users_query->fetch_object()):
    if (strtotime($row->last_activity) > (time() - $settings->delay_for_offline) AND !$row->logout_time)
      $online[] = array(
        "id" => $row->id,
        "login" => $row->login,
        "avatar" => $row->avatar ? AVATARS_DIR_OUTSIDE . "/" . $row->avatar : DEFAULT_AVATAR
      );
  endwhile;

  $new_online_checksum = sha1(json_encode($online));
  if ($new_online_checksum != $data["online_checksum"]) {
    $return["online"] = array(
      "todo"     => "update",
      "data"     => $online,
      "checksum" => $new_online_checksum
    );
  } else $return["online"]["todo"] = "nothing";

  //////////////////////////////////////////////////////ú
  $users_query = $db->query('
    SELECT login
    FROM `'.USERS_TABLE.'`
    WHERE `id` != "'.$user->user_id.'"
  ');

  while ($row = $users_query->fetch_object()):
    $typeahead[] = $row->login;
  endwhile;

  $new_typeahead_checksum = sha1(json_encode($typeahead));
  if ($new_typeahead_checksum != $data["typeahead_checksum"] && $settings->enabled_pms) {
    $return["typeahead"] = array(
      "todo" => "update",
      "data" => $typeahead,
      "checksum" => $new_typeahead_checksum
    );
  } else $return["typeahead"]["todo"] = "nothing";

  return $return;
}

$actions["users_list"] = array("yes", false, false, "ac_users_list");

function ac_user_card($data) {
  global $db, $settings, $user;
  $user_id = is_numeric($data["user_id"]) ? $data["user_id"] : false;
  if (!$user_id) return array("status" => "unknown_user");

  $user_data = $db->query('
    SELECT user.*, (
      SELECT max(login_time)
      FROM `'.SESSIONS_TABLE.'`
      WHERE `user_id` = user.`id`
    ) AS last_login, (
      SELECT max(activity)
      FROM `'.SESSIONS_TABLE.'`
      WHERE `user_id` = user.id AND
                   `logout_time` is NULL
    ) AS last_activity
    FROM `'.USERS_TABLE.'` AS user
    WHERE user.`id` = "'.$user_id.'"
  ')->fetch_object();

  if (!$user_data) return array("status" => "unknown_user");

  $profile_items = $db->query('
    SELECT name_slug, value
    FROM `'.USERS_PROFILE_TABLE.'`
    WHERE user_id = "'. $user_id .'"
  ');

  while ($item = $profile_items->fetch_object()):
    if ($settings->profile_items->{$item->name_slug}) {
      $profile[$settings->profile_items->{$item->name_slug}] = $item->value;
    }
  endwhile;

  if ($user->level == "administrator") $relationship = "administrator";
  elseif ($user->user_id == $user_id) $relationship = "me";
  else $relationship = false;

  if ($relationship == "administrator" || $relationship == "me")
    $informations = array(
      __g("banned")     => $user_data->ban ? "Yes" : "No",
      __g("last_login") => ($user_data->last_login ? date($settings->datetime_format, strtotime($user_data->last_login)) : "Never"),
      __g("registered") => date($settings->datetime_format, strtotime($user_data->registered))
    );


  return array(
    "status" => "ok",
    "relationship" => $relationship,
    "status" => $user_data->status,
    "user_id" => $user_id,
    "state" => (strtotime($user_data->last_activity) > (time() - $settings->delay_for_offline)) ? "online" : "offline",
    "ban" => ($user->level == "administrator" ? $user_data->ban : false),
    "login" => $user_data->login,
    "avatar" => $user_data->avatar ? AVATARS_DIR_OUTSIDE . "/" . $user_data->avatar : DEFAULT_AVATAR,
    "profile" => $profile,
    "informations" => $informations
  );
}

$actions["user_card"] = array("yes", false, false, "ac_user_card");

function ac_pms_start_conversation ($data) {
  global $db, $user, $settings;

  $login = $data["login"] ? $db->real_escape_string($data["login"]) : false;
  if (!$login or $login == $user->login) return array("status" => "unknown_user");

  $pms_user = $db->query('
    SELECT user.*
    FROM `'. USERS_TABLE .'` AS user
    WHERE `login` = "'. $login .'"
    LIMIT 1
  ');

  if (!$pms_user->num_rows) return array("status" => "unknown_user");
  $pms_user = $pms_user->fetch_object();

  return array(
    "status" => "ok",
    "user_id" => $pms_user->id,
    "login" => $pms_user->login,
  );
}

$actions["pms_start_conversation"] = array("yes", false, "enabled_pms", "ac_pms_start_conversation");

function ac_pms_update($data) {
  global $actions;

  foreach($data AS $tab) {
    $result[] = $actions["pms_get_messages"][3]($tab);
  }

  return array(
    "status" => "ok",
    "result" => $result
  );
}

$actions["pms_update"] = array("yes", false, "enabled_pms", "ac_pms_update");

function ac_pms_get_messages($data) {
  global $db, $user, $settings;

    $todo = $data["todo"];
    $user_id = is_numeric($data["user_id"]) ? $data["user_id"] : false;
    $first_id = is_numeric($data["first_id"]) ? $data["first_id"] : false;
    $last_id = is_numeric($data["last_id"]) ? $data["last_id"] : false;
    $result = array("user_id" => $user_id, "todo" => $todo, "unseen" => false, "messages" => false);

    if (!$user_id) return array("status" => "unknown_user");
    if ($todo != "update" and $todo != "select" and $todo != "load_older") return array("status" => "nothing_todo");

    $online_check = $db->query('
      SELECT *
      FROM `'.USERS_TABLE.'` AS user
      LEFT JOIN (
        SELECT *
        FROM `'.SESSIONS_TABLE.'`
        ORDER BY id DESC
        LIMIT 1
      ) AS session ON session.user_id = user.id
      WHERE user.id = "'. $user_id .'" AND
            user.visible = 1
      LIMIT 1
    ')->fetch_object();

    $result["state"] = ((strtotime($online_check->activity) > (time() - $settings->delay_for_offline) and !$online_check->logout_time) ? "online" : "offline");

    $sql = '
      SELECT message.id AS id, user.id AS user_id, user.login AS login, user.avatar AS avatar, message.text AS text, message.time AS time, message.seen AS seen, attachment.id AS attachment_id, attachment.name AS attachment_name
      FROM `'.MESSAGES_TABLE.'` AS message
      JOIN `'.USERS_TABLE.'` AS user ON message.from_id = user.id
      LEFT JOIN `'.ATTACHMENTS_TABLE.'` AS attachment ON attachment.id = message.attachment_id
      WHERE message.`type` = "private" AND
            (
              (message.from_id = "'. $user->user_id .'" AND message.to_id = "'. $user_id .'")
                OR
              (message.from_id = "'. $user_id .'" AND message.to_id = "'. $user->user_id .'")
            )
    ';

    if ($todo == "update") {
      $sql .= 'AND message.id > "'.$last_id.'"' . ' ORDER BY message.time DESC';
    } elseif ($todo == "select") {
      $sql .= ' ORDER BY  message.time DESC' . ' LIMIT 0, '.$settings->messages_per_page;
    } elseif ($todo == "load_older") {
      $sql .= 'AND message.id < "'.$first_id.'" ORDER BY  message.time DESC LIMIT 0, '.$settings->messages_per_page;
    } else return false;

    $pms = $db->query($sql);

    while ($message = $pms->fetch_object()):
      $result["messages"][] = array(
        "id" => $message->id,
        "user_id" => $message->user_id,
        "from" => $message->login,
        "avatar" => $message->avatar ? AVATARS_DIR_OUTSIDE . "/" . $message->avatar : DEFAULT_AVATAR,
        "text" => make_clickable(nl2br($message->text)),
        "time" => date($settings->datetime_format, strtotime($message->time)),
        "attachment" => array(
          "id" => $message->attachment_id,
          "name" => $message->attachment_name
        )
      );

      $new_first_id = $message->id;
      if (!$new_last_id) $new_last_id = $message->id;
      if (!$message->seen && $message->user_id == $user_id) $result["unseen"] = true;

    endwhile;

    //mark as seen
    if ($new_last_id) $db->query('
      UPDATE `'.MESSAGES_TABLE.'`
      SET `seen` = "1"
      WHERE `type` = "private" AND
            `from_id` = "'. $user_id .'" AND
            `to_id` = "'. $user->user_id .'" AND
            `id` <= "'. $new_last_id .'"
    ');



  if ($todo == "select") {$result["first_id"] = $new_first_id; $result["last_id"] = $new_last_id;}
  if ($todo == "update") $result["last_id"] = $new_last_id;
  if ($todo == "load_older") $result["first_id"] = $new_first_id;

  return $result;
}

$actions["pms_get_messages"] = array("yes", false, "enabled_pms", "ac_pms_get_messages");

function ac_pms_send_message($data) {
  global $db, $user, $settings;

  $user_id = is_numeric($data["user_id"]) ? $data["user_id"] : false;
  $text = $data["text"];
  $attachment_id = is_numeric($data["attachment_id"]) ? $data["attachment_id"] : false;

  if (strlen($text) > $settings->message_max_characters) return array("status" => "long", "user_id" => $user_id);
  if (!trim($text) or empty($text)) return array("status" => "short", "user_id" => $user_id);

  if ($user_id) $exists_user = $db->query('SELECT id FROM `'.USERS_TABLE.'` WHERE `id` = "'.$user_id.'" ')->num_rows;
  if (!$exists_user) return array("status" => "unknown_user", "user_id" => $user_id);

  if ($attachment_id) $exists_attachment = $db->query('SELECT id FROM `'.ATTACHMENTS_TABLE.'` WHERE `id` = "'.$attachment_id.'" AND `author` = "'.$user->user_id.'" ')->num_rows;

  $db->query('
    INSERT INTO `'.MESSAGES_TABLE.'`
    SET `type` = "private",
        `from_id` = "'. $user->user_id .'",
        `to_id` = "'. $user_id .'",
        `time` = NOW(),
        `text` = "'. $db->real_escape_string(htmlspecialchars(trim($text), ENT_QUOTES)) .'",
        `attachment_id` = '.($exists_attachment ? '"'.$attachment_id.'"' : "NULL").',
        `session_id` = "'. $user->sid .'",
        `seen` = "0"
  ');

  return array("status" => "ok", "user_id" => $user_id);
}

$actions["pms_send_message"] = array("yes", false, "enabled_pms", "ac_pms_send_message");

function ac_delete_message($data) {
  global $db, $user;

  if ($user->level == "administrator" && is_numeric($data["msg_id"])) {
    $db->query('
      DELETE FROM `'.MESSAGES_TABLE.'`
      WHERE `id` = "'.$data["msg_id"].'"
      LIMIT 1
    ');
  }
}

$actions["delete_message"] = array("yes", "administrator", false, "ac_delete_message");

function ac_change_password($data) {
  global $user;

  $password_old = $data["password_old"];
  $password_new = $data["password_new"];
  $password_new_repeat = $data["password_new_repeat"];

  if (!$password_old || !$password_new || !$password_new_repeat) return array("status" => "0", "alert" => __g("check_empty_fields"));
  if ($password_new != $password_new_repeat) return array("status" => "0", "alert" => __g("passwords_not_match"));

  $return = $user->change_password($password_old, $password_new, $user->user_id);

  if ($return == "not_permitted") return array("status" => "0", "alert" => __g("not_permitted"));
  elseif ($return == "bad_old") return array("status" => "0", "alert" => __g("Incorrect old password"));
  elseif ($return == "ok") return array("status" => "1", "alert" => __g("password_successfully_changed"));
  else return array("status" => "0", "alert" => __g("internal_error"));
}

$actions["change_password"] = array("yes", false, false, "ac_change_password");

function ac_user_card_start_edit($data) {
  global $db, $user, $settings;
  $user_id = is_numeric($data["user_id"]) ? $data["user_id"] : false;

  if ( ($user->level != "administrator" && $user_id != $user->user_id) || !$user_id ) return false;

  $the_user = $db->query('
    SELECT id, status, ban
    FROM `'.USERS_TABLE.'`
    WHERE `id` = "'. $user_id .'"
  ');

  if (!$the_user->num_rows) return false;

  $the_user_data = $the_user->fetch_object();

  $the_profile = $db->query('
    SELECT *
    FROM `'.USERS_PROFILE_TABLE.'`
    WHERE `user_id` = "'. $user_id .'"
  ');

  while ($item = $the_profile->fetch_object()) { $profile[$item->name_slug] = $item->value; }

  if ($settings->profile_items) foreach ($settings->profile_items AS $name_slug => $name):
    $return_profile[] = array(
      "name_slug" => $name_slug,
      "name" => $name,
      "value" => ($profile[$name_slug] ? $profile[$name_slug] : "")
    );
  endforeach;

  return array(
    "status" => $the_user_data->status,
    "ban" => $the_user_data->ban ? "1" : "0",
    "profile" => $return_profile
  );
}

$actions["user_card_start_edit"] = array("yes", false, false, "ac_user_card_start_edit");

function ac_user_card_save($data) {
  global $db, $user, $settings;

  $user_id = is_numeric($data["user_id"]) ? $data["user_id"] : false;
  $profile = is_array($data["profile"]) ? $data["profile"] : false;
  $status = $db->real_escape_string($data["status"]);
  $delete_avatar = $data["delete_avatar"] ? true : false;
  $ban = $data["ban"] ? true : false;

  if ( ($user->level != "administrator" && $user_id != $user->user_id) || !$user_id ) return false;

  $the_user = $db->query('
    SELECT id
    FROM `'.USERS_TABLE.'`
    WHERE `id` = "'. $user_id .'"
  ');

  if (!$the_user->num_rows) return false;

  // Let's go!
  $status_update = $db->query('
    UPDATE `'.USERS_TABLE.'`
    SET `status` = "'. $status .'",
        `ban` = '. ($ban ? '"1"' : 'NULL') .'
        '.( $delete_avatar ? ',`avatar` = NULL' : '' ).'
    WHERE `id` = "'. $user_id .'"
  ');

  // Profile - delete old
  $profile_delete_old = $db->query('
    DELETE FROM `'.USERS_PROFILE_TABLE.'`
    WHERE `user_id` = "'. $user_id .'"
  ');

  // Parse profile
  if ($data) foreach ($profile AS $id => $data) {
    if (!$settings->profile_items->{$data["name_slug"]}) break;
    $values[] = "('". $user_id ."', '". $data["name_slug"] ."', '". $db->real_escape_string($data["value"]) ."')";
  }

  // Insert
  if ($values) $insert = $db->query('
    INSERT INTO `'.USERS_PROFILE_TABLE.'`
    (`user_id`, `name_slug`, `value`) VALUES '. implode(",", $values) .'
  ');
}

$actions["user_card_save"] = array("yes", false, false, "ac_user_card_save");

function ac_settings_start_edit($data) {
  global $settings;

  return $settings;
}

$actions["settings_start_edit"] = array("yes", "administrator", false, "ac_settings_start_edit");

function ac_settings_save($data) {
  global $settings;

  if ($data["site_name"]) $settings->site_name = $data["site_name"];
  if ($data["profile_items_enc"]) $settings->profile_items_enc = $data["profile_items_enc"];
  if (is_numeric($data["refresh_delay"])) $settings->refresh_delay = $data["refresh_delay"];
  if (is_numeric($data["delay_for_offline"])) $settings->delay_for_offline = $data["delay_for_offline"];
  if (is_numeric($data["attachment_max_size"])) $settings->attachment_max_size = $data["attachment_max_size"];
  $settings->enabled_pms = ($data["enabled_pms"] == "on" ? true : false);
  $settings->enabled_register = ($data["enabled_register"] == "on" ? true : false);
  $settings->direction_rtl = ($data["direction_rtl"] == "on" ? true : false);
  if (is_numeric($data["message_max_characters"])) $settings->message_max_characters = $data["message_max_characters"];
  if (is_numeric($data["messages_per_page"])) $settings->messages_per_page = $data["messages_per_page"];
  if (is_numeric($data["next_message_delay"])) $settings->next_message_delay = $data["next_message_delay"];
  if ($data["datetime_format"]) $settings->datetime_format = $data["datetime_format"];
  $settings->enabled_attachments_room = ($data["enabled_attachments_room"] == "on" ? true : false);
  $settings->enabled_attachments_pms = ($data["enabled_attachments_pms"] == "on" ? true : false);

  $file_settings = fopen(ABSPATH . "/_settings.json", "w");
  fwrite($file_settings, json_encode($settings));
  fclose($file_settings);

}

$actions["settings_save"] = array("yes", "administrator", false, "ac_settings_save");

function ac_add_user($data) {
  global $user;

  $login = $data["login"];
  $password = $data["password"];
  $level = $data["level"];

  if (!$login or !$password) return array("status" => 0, "alert" => __g("check_empty_fields"));
  if (!preg_match("/^[a-z,0-9]{3,15}+$/", $login)) return array("status" => 0, "alert" => __g("login_contain"));
  if ($level != "administrator" && $level != "member") return array("status" => 0, "alert" => __g("unknown_level"));

  $status = $user->register($login, $password, $level);

  if ($status == "ok") {
      return array("status" => 1, "alert" => __g("user_successfully_added"));
  } elseif ($status == "login_exists") {
      return array("status" => 0, "alert" => __g("login_is_already_using"));
  } else {
      return array("status" => 0, "alert" => __g("internal_error"));
  }

}

$actions["add_user"] = array("yes", "administrator", false, "ac_add_user");

function ac_manage_users($data) {
  global $db, $user;

  $page = is_numeric($data["page"]) ? $data["page"] : 1;

  $total = $db->query('
    SELECT id
    FROM `'.USERS_TABLE.'`
  ')->num_rows;

  $users = $db->query('
    SELECT *
    FROM `'.USERS_TABLE.'`
    ORDER BY login
    LIMIT '. (($page - 1) * 10) .', 10
  ');

  while ($item = $users->fetch_object()):
    $result["users"][] = array(
      "id" => $item->id,
      "login" => $item->login,
      "level" => ($item->level == "administrator" ? __g("administrator") : __g("member")),
      "ban" => ($item->ban ? __g("yes") : __g("no"))
    );
  endwhile;

  $result["pages"] = ceil($total / 10);
  $result["page"] = $page;

  return $result;
}

$actions["manage_users"] = array("yes", "administrator", false, "ac_manage_users");

function ac_delete_user($data) {
  global $db;

  if (is_numeric($data["user_id"])) $user_id = $data["user_id"]; else return false;

  $db->query('
    DELETE FROM `'.USERS_TABLE.'`
    WHERE `id` = "'. $user_id .'"
    LIMIT 1
  ');
}

$actions["delete_user"] = array("yes", "administrator", false, "ac_delete_user");

function ac_edit_users($data) {
  global $db;

  if (is_numeric($data["user_id"])) $user_id = $data["user_id"]; else return false;

  $the_user = $db->query('
    SELECT login, level
    FROM `'.USERS_TABLE.'`
    WHERE `id` = "'. $user_id .'"
    LIMIT 1
  ');

  if (!$the_user->num_rows) return false;
  $the_user = $the_user->fetch_object();

  return array(
    "login" => $the_user->login,
    "level" => $the_user->level,
    "user_id" => $user_id
  );
}

$actions["edit_user"] = array("yes", "administrator", false, "ac_edit_users");

function ac_save_edits($data) {
  global $db, $user;

  $user_id = is_numeric($data["user_id"]) ? $data["user_id"] : false;
  $login = ($data["login"] ? $data["login"] : false);
  $password = ($data["password"] ? $data["password"] : false);
  $level = ($data["level"] == "administrator" ? "administrator" : "member");

  if (!$user_id || !$login) return array("status" => "0", "alert" => __g("check_empty_fields"));
  if (!preg_match("/^[a-z,0-9]{3,15}+$/", $login)) return array("status" => 0, "alert" => __g("login_contain"));

  if ($db->query('
    UPDATE `'.USERS_TABLE.'`
    SET `login` = "'. $login .'",
        `level` = "'. $level .'"
        '.( $password ? ',`password` = "'.md5($password).'"' : '' ).'
    WHERE `id` = "'. $user_id .'"
  '))
      return array("status" => 0, "alert" => __g("user_successfully_updated"));
    else
      return array("status" => 0, "alert" => __g("internal_error"));
}

$actions["save_edits"] = array("yes", "administrator", false, "ac_save_edits");

function ac_add_room($data) {
  global $db;
  if ($data["name"]) $name = $db->real_escape_string($data["name"]); else return false;

  $db->query('
    INSERT INTO `'.ROOMS_TABLE.'`
    SET `name` = "'. $name .'"
  ');
}

$actions["add_room"] = array("yes", "administrator", false, "ac_add_room");

function ac_get_rooms($data) {
  global $db;

  $users = $db->query('
    SELECT *
    FROM `'.ROOMS_TABLE.'`
    ORDER BY id
  ');

  while ($item = $users->fetch_object()):
    $result[] = array(
      "id" => $item->id,
      "name" => $item->name
    );
  endwhile;

  return $result;
}

$actions["get_rooms"] = array("yes", "administrator", false, "ac_get_rooms");

function ac_room_change_name($data) {
  global $db;
  if (is_numeric($data["room_id"])) $room_id = $data["room_id"]; else return false;
  if ($data["name"]) $name = $data["name"]; else return false;

  $db->query('
    UPDATE `'.ROOMS_TABLE.'`
    SET `name` = "'. $name .'"
    WHERE `id` = "'. $room_id .'"
  ');
}

$actions["room_change_name"] = array("yes", "administrator", false, "ac_room_change_name");

function ac_room_delete($data) {
  global $db;
  if (is_numeric($data["room_id"])) $room_id = $data["room_id"]; else return false;

  $db->query('
    DELETE FROM `'.ROOMS_TABLE.'`
    WHERE `id` = "'. $room_id .'"
  ');
}

$actions["room_delete"] = array("yes", "administrator", false, "ac_room_delete");

function ac_pms_to_open($data) {
  global $db, $user;

  $to_open = $db->query('
    SELECT user.login, user.id
    FROM `'.MESSAGES_TABLE.'` AS msg
    JOIN `'.USERS_TABLE.'` AS user ON user.id = msg.from_id
    WHERE msg.`type` = "private" AND
          msg.`to_id` = "'. $user->user_id .'" AND
          msg.`seen` = "0"
    GROUP BY msg.from_id
  ');

  while ($item = $to_open->fetch_object()):

      $result[] = $item->login;
  endwhile;

  return $result;
}

$actions["pms_to_open"] = array("yes", false, "enabled_pms", "ac_pms_to_open");