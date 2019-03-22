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
    <link rel="stylesheet" type="text/css" href="./css/room-page.css" />
    <script src="./js/jquery.v2.0.2.min.js" type="text/javascript"></script>
    <script src="./js/fn.extends.js" type="text/javascript"></script>
    <script src="./js/bootstrap.min.js" type="text/javascript"></script>
    <script src="./js/autosize.min.js" type="text/javascript"></script>
    <script src="./js/niftyplayer.min.js" type="text/javascript"></script>
    <script src="./js/system.page-room.js" type="text/javascript"></script>
    <script type="text/javascript">
        msg.lang = $.parseJSON('<?php echo str_replace("'", "\u0027", json_encode($lang));?>');
        msg.settings.data = $.parseJSON('<?php unset($settings->profile_items_enc); echo str_replace("'", "\u0027", json_encode($settings));?>');
        msg.system.level = "<?php echo $user->level;?>";
        var filename = "<?php echo basename($_SERVER["SCRIPT_NAME"]);?>";
    </script>
</head>
<body>

<!-- Header
    ================================================== -->
<div class="navbar navbar-fixed-top">
  <div class="navbar-inner">
    <div class="container">
      <span class="brand"><?php echo $settings->site_name;?></span>     
      <div data-toggle="buttons-checkbox" class="btn-group pull-right">
        <button type="button" id="sounds_state" class="btn btn-warning" title="<?php __("sounds");?>"><i class="icon-white icon-music"></i></button>
        <button type="button" id="visible_state" class="btn btn-warning" title="<?php __("visible");?>"><i class="icon-white icon-eye-open"></i></button>
        <a class="btn btn-warning dropdown-toggle" data-toggle="dropdown" href="#" style="padding-left: 20px;padding-right: 20px;">
          <i class="icon-user"></i> <?php echo $user->login;?> <span class="caret"></span>
        </a>
        
        <ul class="dropdown-menu header-menu">
          <li data-user-id="<?php echo $user->user_id;?>"><a href="#modal_profile" data-toggle="modal" class="clickable"><i class="icon-user"></i> <?php __("profile");?></a></li>
          <li><a href="#modal_change_password" data-toggle="modal"><i class="icon-pencil"></i> <?php __("change_password");?></a></li>
          
          <?php if($user->level == "administrator"): ?>
            <li class="divider"></li>
            <li class="nav-header"><?php __("administration");?></li>
            
            <li><a href="#" id="btn_open_modal_settings"><i class="icon-cog"></i> <?php __("settings");?></a></li>
            <li><a href="#" id="btn_open_modal_manage_users"><i class="icon-user"></i> <?php __("manage_users");?></a></li>
            <li><a href="#" id="btn_open_modal_manage_rooms"><i class="icon-comment"></i> <?php __("manage_rooms");?></a></li>
          <?php endif; ?>
          
          <li class="divider"></li>
          <li><a href="?action=logout"><i class="icon-share"></i> <?php __("logout");?></a></li>
        </ul>
      </div>
      <i class="icon-loading pull-right" id="page_loading_state" style="margin: 14px 10px 0 0; display: none;"></i>
      
    </div>
  </div>
</div>


<!-- Main part
    ================================================== -->
<div class="container" style="margin-top: 50px;">
  <div class="row-fluid">
    <div class="leftbar span3">
      <div class="well list"> 
        <span class="nav-header clearfix"><?php __("rooms");?></span>
        <ul class="nav nav-list" id="list-rooms"></ul>
      </div>

      <div class="well list">
        <span class="nav-header clearfix"><?php __("online_users");?></span>
        <ul class="nav nav-list" id="list-online"></ul>
      </div>
    </div>

    <div class="mainbar span9" style="display: none;">
      <div class="msg_textarea_wrapper">
        <textarea class="msg_textarea standard-input" id="msg_textarea" autocorrect="off" autocapitalize="off" placeholder="<?php __("response");?>"></textarea>
        <ul class="actions">
          <li><a href="#" id="msg_send_button"><i class="icon-ok"></i> <?php __("send");?></a></li>
          <?php if ( ($user->level == "member" && $settings->enabled_attachments_room) OR $user->level == "administrator"): ?>
          <li class="attachment">
            <i class="icon-upload" id="msg-attachment-icon"></i> <span><?php __("upload_file");?></span>
            <input type="file" id="msg-attachment-input" />
          </li>
          <li class="attachment_file" style="display: none;">
            <i class="icon-file"></i> <span></span> <a href="#" style="margin-left: 10px;" id="msg-attachment-delete"><i class="icon-remove-sign"></i></a>
          </li>
          <?php endif; ?>
          
          <div style="clear: both;"></div>
        </ul>
      </div>
      <ul class="msg_content_box" id="msg-content-box"></ul>
      <div class="label label-warning msg_loading_older" id="msg_loading_older"><i class="icon-loading"></i>&nbsp;&nbsp;&nbsp;<?php __("loading_older");?></div>
    </div>

    <div class="clearfix"></div>
  </div>
</div>


<?php if ($settings->enabled_pms): ?>
<!-- Private messages
    ================================================== -->
  <div class="pms_container navbar-fixed-bottom container">
    <div class="pull-right" style="height: 29px;">  
      <div class="btn-group dropup tab tab_grouper">
        <button class="btn dropdown-toggle tab_button" tab-index="−1" data-toggle="dropdown"><i class="icon-folder-open"></i> <span>0</span></button>
        <ul class="dropdown-menu" id="pms_tabs_grouper"></ul>
      </div>
      <div class="live_tabs_group" id="live_tabs_group"></div>
      <div class="tab tab_typeahead" style="font-size: 14px;">
        <input type="text" class="standard-input" placeholder="<?php __("start_chat_with");?>" autocomplete="off">
      </div>    
    </div>
  </div>
<?php endif; ?>


<!-- Modal user_card
    ================================================== -->
<div class="modal hide user_card" id="modal_user_card">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3 id="login">
      <i class="icon-ok-sign" style="margin-top: 10px; display: none;" title="<?php __("online");?>"></i>
      <span></span>
    </h3>
  </div>
  <div class="modal-body row-fluid">
    <div class="span4">
      <img id="avatar" class="img-rounded img-polaroid" style="display: block;" width="152">
      <div class="edit_buttons" style="display: none;">
        <a href="#" id="btn_change_avatar" style="position: relative;"><button class="btn"><?php __("change_avatar");?></button><input type="file" style="opacity: 0;top: 0px;right: 0px;padding: 0px;position: absolute;cursor: pointer; width: 100%; height: 25px;" /></a>
        <a href="#" id="btn_delete_avatar"><button class="btn btn-mini btn-warning" data-toggle="button"><?php __("delete_avatar");?></button></a>
        <a href="#" id="btn_ban"><button class="btn btn-mini btn-danger" data-toggle="button"><?php __("ban");?></button></a>
      </div>
    </div>
    
    <div class="span7">
      <div class="custom_caret"></div>
      <div class="well well-small" id="status"></div>
      
      <table id="profile">
        <tbody>
        </tbody>
      </table>
      
     <h4 id="informations_headline"><?php __("informations");?></h4>
      <table id="informations">
        <tbody>
        </tbody>
      </table>
          
    </div>
  </div>
  
  <div class="modal-footer">
    <a href="#" class="btn btn-success" id="btn_edit" style="display: none;"><i class="icon-pencil icon-white"></i> <?php __("edit");?></a>
    <a href="#" class="btn" id="btn_discard" style="display: none;"><i class="icon-remove"></i> <?php __("discard");?></a>
    <a href="#" class="btn btn-primary" id="btn_save" style="display: none;"><i class="icon-ok-circle icon-white"></i> <?php __("save_changes");?></a>
  </div>
</div>


<!-- Modal change password
    ================================================== -->   
<div class="modal hide change_password" id="modal_change_password">
  <form action="#" method="POST">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      <h3><?php __("change_password");?></h3>
    </div>    
    <div class="modal-body">
      <div class="alert alert-error" style="display: none;">
        <button type="button" class="close" onclick="$(this).parent().hide(0);">&times;</button>
        <strong><?php __("warning");?></strong> <span class="alert_text"></span>
      </div> 
      <div class="input-prepend">
        <span class="add-on"><?php __("now_using_password");?></span>
        <input class="span2" id="password_old" type="password">
      </div>
      <div class="input-prepend">
        <span class="add-on"><?php __("new_password");?></span>
        <input class="span2" id="password_new" type="password">
      </div>
      <div class="input-prepend">
        <span class="add-on"><?php __("repeat_new_password");?></span>
        <input class="span2" id="password_new_repeat" type="password">
      </div>
    </div>
  
    <div class="modal-footer">
      <button class="btn btn-primary btn-save"><i class="icon-ok-circle icon-white"></i> <?php __("change_password");?></button>
    </div>   
  </form> 
</div>
   
<?php if($user->level == "administrator"): ?>   
   
<!-- Modal settings
    ================================================== -->
<div class="modal hide settings" id="modal_settings">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3><?php __("settings");?></h3>
  </div>
  <div class="modal-body">
    <form class="form-horizontal">
      <div class="control-group">
        <label class="control-label" for="site_name"><?php __("site_name");?></label>                        
        <div class="controls">
          <input type="text" id="site_name" name="site_name" />
        </div>
      </div>
      
      <div class="control-group">
        <label class="control-label" for="profile_items_enc"><?php __("profile_items");?></label>                        
        <div class="controls">
          <input type="text" id="profile_items_enc" name="profile_items_enc" />
          <span class="help-inline"><strong><?php __("warning");?></strong> <?php __("profile_items_warning");?></span>
        </div>
      </div>
      
      <div class="control-group">
        <label class="control-label" for="refresh_delay"><?php __("refresh_delay");?></label>                        
        <div class="controls">
          <div class="input-append">
            <input type="text" id="refresh_delay"  name="refresh_delay" />
            <span class="add-on"><?php __("milisecond");?></span>
          </div>
          <span class="help-inline"><?php __("refresh_delay_info");?></span>
        </div>
      </div>
      
      <div class="control-group">
        <label class="control-label" for="delay_for_offline"><?php __("delay_for_offline");?></label>                        
        <div class="controls">
          <div class="input-append">
            <input id="delay_for_offline" type="text" name="delay_for_offline" />
            <span class="add-on"><?php __("second");?></span>
          </div>
          <span class="help-inline"><?php __("delay_for_offline_info");?></span>
        </div>
      </div>
      
      <div class="control-group">
        <label class="control-label" for="attachment_max_size"><?php __("attachment_max_size");?></label>                        
        <div class="controls">
          <div class="input-append">
            <input id="attachment_max_size" type="text" name="attachment_max_size" />
            <span class="add-on"><?php __("megabyte");?></span>
          </div>
        </div>
      </div>     
      
      <div class="control-group">
        <label class="control-label" for="enabled_pms"><?php __("enabled_private_messages");?></label>
        <div class="controls">
          <input type="checkbox" id="enabled_pms" name="enabled_pms" />
        </div>   
      </div>
      
      <div class="control-group">
        <label class="control-label" for="enabled_register"><?php __("enabled_registration");?></label>
        <div class="controls">
          <input type="checkbox" id="enabled_register" name="enabled_register" />
          <span class="add-on"><?php __("enabled_registration_info");?></span>
        </div>   
      </div>
      
      <div class="control-group">
        <label class="control-label" for="direction_rtl"><?php __("direction_rtl");?></label>
        <div class="controls">
          <input type="checkbox" id="direction_rtl" name="direction_rtl" />
        </div>   
      </div>
                  
      <div class="control-group">
        <label class="control-label" for="message_max_characters"><?php __("max_characters_in_message");?></label>
        <div class="controls">
          <input type="text" id="message_max_characters" name="message_max_characters" />
        </div>
      </div>
      
      <div class="control-group">
        <label class="control-label" for="messages_per_page"><?php __("messages_per_page");?></label>                        
        <div class="controls">
          <input type="text" id="messages_per_page" name="messages_per_page" />
        </div>
      </div>
      
      <div class="control-group">
        <label class="control-label" for="next_message_delay"><?php __("next_message_delay");?></label>                        
        <div class="controls">
          <input type="text" id="next_message_delay" name="next_message_delay" />
        </div>
      </div>
      
      <div class="control-group">
        <label class="control-label" for="datetime_format"><?php __("datetime_format");?></label>                        
        <div class="controls">
          <input type="text" id="datetime_format" name="datetime_format" />
          <span class="help-inline"><?php __("datetime_format_info");?></span>
        </div>
      </div>
      
      <div class="control-group">
        <label class="control-label"><?php __("available_attachments_for_members_at");?></label>                        
        <div class="controls">
          <label><input type="checkbox" id="enabled_attachments_room" name="enabled_attachments_room" /> <?php __("rooms");?></label>
          <label><input type="checkbox" id="enabled_attachments_pms" name="enabled_attachments_pms" /> <?php __("private_messages");?></label>
        </div>
      </div>

    </form>    
  </div>
  
  <div class="modal-footer">
    <div class="pull-left muted"><strong><?php __("settings_bottom_info");?></strong></div> <a href="#" id="btn_save" class="btn btn-primary"><i class="icon-ok-circle icon-white"></i> <?php __("save_settings");?></a>
  </div>
</div>

<!-- Modal admin user
    ================================================== -->   
<div class="modal hide admin_user" id="modal_admin_user">
  <form action="#" method="POST">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      <h3 id="headline_text"></h3>
    </div>    
    <div class="modal-body">
      <div class="alert alert-error" style="display: none;">
        <button type="button" class="close" onclick="$(this).parent().hide(0);">&times;</button>
        <strong><?php __("warning");?></strong> <span class="alert_text"></span>
      </div> 
      <div class="input-prepend">
        <span class="add-on"><?php __("login");?></span>
        <input class="span2" id="login" type="text">
      </div>
      <div class="input-prepend">
        <span class="add-on"><?php __("password");?></span>
        <input class="span2" id="password" type="text">
      </div>
      <div class="input-prepend">
        <span class="add-on"><?php __("level");?></span>
        <select id="level" class="span2">
          <option value="member"><?php __("member");?></option>
          <option value="administrator"><?php __("administrator");?></option>
        </select>
      </div>
    </div>
  
    <div class="modal-footer">
      <button class="btn btn-primary btn-save"><i class="icon-ok-circle icon-white"></i> <span id="btn_save_text"></span></button>
    </div>   
  </form> 
</div>

<!-- Modal manage user
    ================================================== -->   
<div class="modal hide manage_users" id="modal_manage_users">
  <form action="#" method="POST">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      <h3><?php __("manage_users");?> <a href="#" style="margin-left: 15px;" id="btn_open_modal_admin_user"><button class="btn"><i class="icon-plus"></i> <?php __("add_user");?></button></a></h3>
    </div>    
    <div class="modal-body">
      <table class="table table-striped table-condensed">
        <thead>
          <th><?php __("id");?></th>
          <th><?php __("login");?></th>  
          <th><?php __("level");?></th>
          <th><?php __("ban");?></th>
          <th><?php __("actions");?></th>
        </thead>
        <tbody>
        </tbody>
      </table>
      
      <label style="display:inline;"><?php __("page");?> <select id="pagination" style="width:80px;margin:0;"></select></label>
    </div>
  
    <div class="modal-footer"></div>   
  </form> 
</div>

<!-- Modal manage rooms
    ================================================== -->   
<div class="modal hide manage_rooms" id="modal_manage_rooms">
  <form action="#" method="POST">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      <h3><?php __("manage_rooms");?> <a href="#" style="margin-left: 15px;" id="btn_open_modal_add_room"><button class="btn"><i class="icon-plus"></i> <?php __("add_room");?></button></a></h3>
    </div>    
    <div class="modal-body">
      <table class="table table-striped table-condensed">
        <thead>
          <th><?php __("id");?></th>
          <th><?php __("name");?></th>
          <th><?php __("actions");?></th>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  
    <div class="modal-footer"></div>   
  </form> 
</div>
  
<?php endif; ?>
  
<!-- Hidden objects
    ================================================== -->  
<div style="position: absolute; top: -9999px;">
  <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="0" height="0" id="niftyPlayer">
    <param name=movie value="./sounds/niftyplayer.swf?file=./sounds/message.mp3&as=0">
    <embed src="./sounds/niftyplayer.swf?file=./sounds/message.mp3&as=0" quality=high bgcolor=#FFFFFF width="0" height="0" name="niftyPlayer" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>
  </object>

  <li data-user-id="{USER_ID}" id="pms_group_tab_prototype">
    <a href="#" class="noselect">
      <i class="icon-ok-sign login-state-icon" style="display: none;" title="<?php __("online");?>"></i> 
      {USER_LOGIN} 
      <i class="icon-remove-sign btn-remove img-rounded"></i>
    </a>
  </li>
  
  <div data-user-id="{USER_ID}" id="pms_tab_prototype" class="tab active">
    <a class="btn tab_button noselect" tab-index="−1">
      <i class="icon-ok-sign login-state-icon" style="display: none;" title="<?php __("online");?>"></i> 
      {USER_LOGIN}
      <i class="icon-remove-sign btn-remove img-rounded"></i>
    </a>
    
    <div class="pms_content_box">
      <ul class="well well-small text_box"></ul>
      <div class="pms_textarea_wrapper clearfix">
        <div class="pms_attached_file_wrapper">
          <i class="icon-file"></i> 
          <span class="pms_attached_file"></span> 
          &nbsp;&nbsp;&nbsp; 
          <a href="#"><i class="icon-remove-sign remove_attachment"></i></a>
        </div>
        <textarea class="pms_textarea standard-input"></textarea>
        
        <ul class="actions">
          <?php if ($settings->enabled_attachments_pms): ?>
          <li class="attachment">  
            <i class="icon-upload"></i> 
            <input type="file" class="pms-attachment-input" />
          </li>
          <?php endif;?>
          <li>
            <a href="#" class="pms_send_button">
              <i class="icon-ok"></i>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
  
</div>

<!-- Others
    ================================================== -->    

</body>
</html>