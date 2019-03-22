// Define main structure
var msg = {
    "layout": {
        "top_fix": 50
    },
    "el": { },
    "lang": false,
    "settings": { },
    "refresh": {
        "timeout": false,
        "xhr" : false,
        "delay": 5000,
        "functions": $.Callbacks(),
        "data": {},
        "callbacks": $.Callbacks()
    },
    "first_room_loaded": false,
    "system": {
        "sounds": true,
        "visible": true
    },
    "active_room": { },
    "rooms_list": {
        "list": false
     },
    "users_list": {
        "online_checksum": false,
        "typeahead_checksum": false
    },
    "pms": {
        "list": {}
    },
    "user_card": {
        "relationship": false
    },
    "change_password": {},
    "admin_user": {},
    "manage_users": {},
    "manage_rooms": {}
};

$(document).ready(function(){

    // Store elements from HTML
    msg.el = {
        "btn_sounds":                       $("#sounds_state"),
        "btn_visible":                      $("#visible_state"),
        "page_loading_state":               $("#page_loading_state"),

        "list_rooms":                       $("#list-rooms"),
        "list_users":                       $("#list-online"),

        "msg":                              $(".mainbar"),
        "msg_textarea":                     $("#msg_textarea"),
        "msg_btn_send":                     $("#msg_send_button"),
        "msg_btn_send_icon":                $("#msg_send_button i"),

        "msg_att_input":                    $("#msg-attachment-input"),
        "msg_att_state_icon":               $("#msg-attachment-icon"),
        "msg_att_uploader":                 $(".msg_textarea_wrapper .actions .attachment"),
        "msg_att_uploaded":                 $(".msg_textarea_wrapper .actions .attachment_file"),
        "msg_att_name":                     $(".attachment_file span"),
        "msg_att_delete":                   $("#msg-attachment-delete"),

        "msg_glass":                        $("#msg-content-box"),
        "msg_alert_load_older":             $("#msg_loading_older"),

        "pms_grouper":                      $(".tab_grouper"),
        "pms_grouper_btn":                  $(".tab_grouper button"),
        "pms_grouper_count":                $(".tab_grouper button span"),
        "pms_grouper_content":              $("#pms_tabs_grouper"),

        "pms_tabs_content":                 $("#live_tabs_group"),

        "pms_typeahead":                    $(".pms_container .tab_typeahead input"),

        "prototype_group_tab":              $("#pms_group_tab_prototype"),
        "prototype_live_tab":               $("#pms_tab_prototype"),

        // uc = user card
        "modal_uc":                         $("#modal_user_card"),
        "modal_uc_login":                   $("#modal_user_card #login span"),
        "modal_uc_login_icon":              $("#modal_user_card #login i"),
        "modal_uc_edit_buttons":            $("#modal_user_card .edit_buttons"),
        "modal_uc_avatar":                  $("#modal_user_card #avatar"),
        "modal_uc_status":                  $("#modal_user_card #status"),
        "modal_uc_profile":                 $("#modal_user_card #profile tbody"),
        "modal_uc_informations_headline":   $("#modal_user_card #informations_headline"),
        "modal_uc_informations":            $("#modal_user_card #informations tbody"),

        "modal_uc_btn_edit":                $("#modal_user_card #btn_edit"),
        "modal_uc_btn_discard":             $("#modal_user_card #btn_discard"),
        "modal_uc_btn_save":                $("#modal_user_card #btn_save"),
        "modal_uc_btn_change_avatar":       $("#modal_user_card #btn_change_avatar"),
        "modal_uc_btn_change_avatar_input": $("#modal_user_card #btn_change_avatar input"),
        "modal_uc_btn_delete_avatar":       $("#modal_user_card #btn_delete_avatar button"),
        "modal_uc_btn_ban":                 $("#modal_user_card #btn_ban"),
        "modal_uc_btn_ban_state":           $("#modal_user_card #btn_ban button"),

        // cp = change password
        "modal_cp":                         $("#modal_change_password"),
        "modal_cp_alert":                   $("#modal_change_password .alert"),
        "modal_cp_alert_text":              $("#modal_change_password .alert span"),
        "modal_cp_pwd_old":                 $("#modal_change_password #password_old"),
        "modal_cp_pwd_new":                 $("#modal_change_password #password_new"),
        "modal_cp_pwd_new_repeat":          $("#modal_change_password #password_new_repeat"),
        "modal_cp_btn_save":                $("#modal_change_password .btn-save"),
        "modal_cp_btn_save_icon":           $("#modal_change_password .btn-save i"),

        "btn_open_modal_settings":          $("#btn_open_modal_settings"),
        "modal_settings":                   $("#modal_settings"),
        "modal_settings_form":              $("#modal_settings form"),
        "modal_settings_btn_save":          $("#modal_settings #btn_save"),
        "modal_settings_btn_save_icon":     $("#modal_settings #btn_save i"),

        // au = admin user
        "btn_open_modal_admin_user":        $("#btn_open_modal_admin_user"),
        "modal_au":                         $("#modal_admin_user"),
        "modal_au_headline_text":           $("#modal_admin_user #headline_text"),
        "modal_au_alert":                   $("#modal_admin_user .alert"),
        "modal_au_alert_text":              $("#modal_admin_user .alert span"),
        "modal_au_login":                   $("#modal_admin_user #login"),
        "modal_au_password":                $("#modal_admin_user #password"),
        "modal_au_level":                   $("#modal_admin_user #level"),
        "modal_au_btn_save":                $("#modal_admin_user .btn-save"),
        "modal_au_btn_save_icon":           $("#modal_admin_user .btn-save i"),
        "modal_au_btn_save_text":           $("#modal_admin_user #btn_save_text"),

        // mu = manage users
        "btn_open_modal_manage_users":      $("#btn_open_modal_manage_users"),
        "modal_mu":                         $("#modal_manage_users"),
        "modal_mu_table":                   $("#modal_manage_users tbody"),
        "modal_mu_pagination":              $("#modal_manage_users #pagination"),

        // mr = manage room
        "btn_open_modal_add_room":          $("#btn_open_modal_add_room"),
        "modal_mr":                         $("#modal_manage_rooms"),
        "modal_mr_table":                   $("#modal_manage_rooms tbody"),

        "btn_open_modal_manage_rooms":      $("#btn_open_modal_manage_rooms"),

        "live_tab":             function(user_id) {
            return msg.el["pms_tabs_content"].find(".tab[data-user-id="+ user_id +"]");
        },

        "grouped_tab":          function(user_id) {
            return msg.el["pms_grouper_content"].find("li[data-user-id="+ user_id +"]");
        },

        "list_room":            function(room_id) {
            return msg.el["list_rooms"].find("li[data-id="+ room_id +"]");
        },

        "msg_message":              function(msg_id) {
            return msg.el["msg_glass"].find("li[data-id="+ msg_id +"]");
        }
    };

    // Set layout first time
    msg.layout.refresh();

    // Remember to dynamic layout
    $(window).resize(msg.layout.refresh);

    // Set autoresize for msg_textarea
    msg.el["msg_textarea"].autosize({"callback": function(){ msg.layout.refresh(); } });

    // Bind event handler for button sounds
    msg.el["btn_sounds"].click(function(){
        msg.system.states("sounds");
        msg.refresh.refresh();
    });

    // Bind event handler for button visible
    msg.el["btn_visible"].click(function(){
        msg.system.states("visible");
        msg.refresh.refresh();
    });

    // MSG - Catch keypress for msg_textarea
    msg.el["msg_textarea"].keypress(function(e){

        // Which key pressed?
        if (e.keyCode == 13 && !e.shiftKey) {

            // Is enter => send message
            e.preventDefault();
            msg.active_room.send_message();

        } else if (e.keyCode == 13 && e.shiftKey) {
            // New line
        }
    });

    // MSG - Send message by sendbutton
    msg.el["msg_btn_send"].click(function(e){
        e.preventDefault();
        msg.active_room.send_message()
    });

    // MSG - Glass scroll => LOAD OLDER
    msg.el["msg_glass"].scroll(function(){

        // Is it at the end?
        if (this.offsetHeight + this.scrollTop >= this.scrollHeight && !msg.active_room.at_end) {
            $("#msg_loading_older").show(0);
            msg.active_room.update({"todo":"load_older", "room_id": msg.active_room.room_id, "first_id": msg.active_room.first_id});
            msg.refresh.callbacks.add(function(){ msg.el["msg_alert_load_older"].hide(0); });
            msg.refresh.refresh();
        }
    });

    // Select room from rooms list
    msg.el["list_rooms"].on("click", "li a", function(e){
        e.preventDefault();
        msg.active_room.update({"todo": "select", "room_id": $(this).parent().attr("data-id")});
        msg.refresh.refresh();
    });

    // MSG - An attachment added
    msg.el["msg_att_input"].change(msg.active_room.upload_attachment);

    // MSG - Remove attachment
    msg.el["msg_att_delete"].click(function(e){
        e.preventDefault();
        msg.active_room.remove_attachment();
    });

    // PMS - Wake up typeahead
    msg.el["pms_typeahead"].typeahead({
        "updater": function (item) {
            msg.pms.start_conversation(item);
        }
    });

    // PMS - Toggle live tab
    msg.el["pms_tabs_content"].on("click", ".tab .tab_button", function(){
        if ($(this).parent().hasClass("active")) {
            $(this).parent().removeClass("active");
        } else {
            $(this).parent().addClass("active").find(".pms_textarea").focus();
            $(this).parent().find(".text_box").scrollBottom();
        }
    });

    // PMS - A style event - mouseover
    msg.el["pms_tabs_content"].on("mouseover", ".btn-remove", function(){
        $(this).addClass("icon-white");
        $(this).css("background-color", "#555");
    });

    // PMS - A style event - mouselaeave
    msg.el["pms_tabs_content"].on("mouseleave", ".btn-remove", function(){
        $(this).removeClass("icon-white");
        $(this).css("background-color", "transparent");
    });

    // PMS - Close an tab
    msg.el["pms_tabs_content"].add(msg.el["pms_grouper_content"]).on("click", ".btn-remove", function(e){
        e.preventDefault();
        var user_id = $(this).parent().parent().attr("data-user-id");
        msg.pms.close({"user_id": user_id});
        return false;
    });

    // PMS - Open tab from grouper
    msg.el["pms_grouper_content"].on("click", "li a", function(e){
        e.preventDefault();
        var user_id = $(this).parent().attr("data-user-id");
        msg.pms.open({"user_id": user_id});
    });

    // PMS - Start uploading attachment
    msg.el["pms_tabs_content"].on("change", ".pms-attachment-input", function(){
        var user_id = $(this).parents("div.tab").attr("data-user-id");
        msg.pms.upload_attachment(user_id);
    });

    // PMS - Remove attachment
    msg.el["pms_tabs_content"].on("click", ".remove_attachment", function(e){
        e.preventDefault();
        var user_id = $(this).parents("div.tab").attr("data-user-id");
        msg.pms.remove_attachment(user_id);
    });

    // PMS - An keypress
    msg.el["pms_tabs_content"].on("keypress", ".pms_textarea", function(e){
        if (e.keyCode == 13 && !e.shiftKey) {
            e.preventDefault();
            var user_id = $(this).parents("div.tab").attr("data-user-id");
            msg.pms.send_message(user_id);

        } else if (e.keyCode == 13 && e.shiftKey) {
           // New line
        }
    });

    // PMS - Send message by sendbutton
    msg.el["pms_tabs_content"].on("click", ".pms_send_button", function(){
        var user_id = $(this).parents("div.tab").attr("data-user-id");
        msg.pms.send_message(user_id);
    });

    // Handler on focus .pms_textarea
    msg.el["pms_tabs_content"].on("focus", ".pms_textarea", function(){
        msg.pms.set_seen($(this).parents("div.tab").attr("data-user-id"));
    });

    // Open user card
    msg.el["msg_glass"].on("click", ".msg_delete", function(e){
        e.preventDefault();
        msg.active_room.admin_delete_message($(this).parents("li").attr("data-id"));
    });

    // Open profile card
    $("body").on("click", ".clickable", function(){
        var uid = $(this).parents("li").attr("data-user-id");
        if (!uid) uid = $(this).parents("tr").attr("data-user-id");
        msg.user_card.open(uid);
    });

    // Start editing
    msg.el["modal_uc_btn_edit"].click(msg.user_card.start_edit);

    // Save edits
    msg.el["modal_uc_btn_save"].click(msg.user_card.save);

    // Discard
    msg.el["modal_uc_btn_discard"].click(function(){ msg.user_card.open(msg.user_card.user_id); });

    // AVATAR - A image attached
    msg.el["modal_uc_btn_change_avatar_input"].change(msg.user_card.upload_avatar);

    // Start changing password
    msg.el["modal_cp"].find("form").submit(function(e){
        e.preventDefault();
        msg.change_password.save();
    });

    // Open modal settings
    msg.el["btn_open_modal_settings"].click(function(e){
        e.preventDefault();
        msg.settings.start_edit();
    });

    // Save modal settings
    msg.el["modal_settings_btn_save"].click(function(e){
        e.preventDefault();
        msg.settings.save();
    });

    // Open modal manage users
    msg.el["btn_open_modal_manage_users"].click(function(e){
        e.preventDefault();
        msg.manage_users.open();
    });

    // Manage useres btn_edit
    msg.el["modal_mu"].on("click", ".btn_edit", function(e){
        e.preventDefault();
        msg.manage_users.edit($(this).parents("tr").attr("data-user-id"));
    });

    // Manage users btn_delete
    msg.el["modal_mu"].on("click", ".btn_delete", function(e){
        e.preventDefault();
        msg.manage_users.delete($(this).parents("tr").attr("data-user-id"));
    });

    // Open modal admin users
    msg.el["btn_open_modal_admin_user"].click(function(e){
        e.preventDefault();
        msg.admin_user.add_user();
    });

    msg.el["modal_mu_pagination"].change(function(e){
      msg.manage_users.open($(this).val());
    });

    // Open prompt for adding new room
    msg.el["btn_open_modal_add_room"].click(function(e){
        e.preventDefault();
        msg.manage_rooms.add(prompt(msg.lang["select_name_for_new_room"]));
    });

    // Open rooms management
    msg.el["btn_open_modal_manage_rooms"].click(function(e){
        e.preventDefault();
        msg.manage_rooms.open();
    });

    msg.el["modal_mr"].on("click", ".btn_edit", function(e){
      e.preventDefault();
      msg.manage_rooms.change_name(prompt(msg.lang["select_new_name"], $(this).attr("data-name")),$(this).attr("data-id"));
    });

    msg.el["modal_mr"].on("click", ".btn_delete", function(e){
      e.preventDefault();
      if (confirm(msg.lang["are_you_sure"])) msg.manage_rooms.delete($(this).parents("tr").attr("data-id"));
    });

    // Set an interval for toggle class => make blink effect
    setInterval(function(){
        $("div.tab.new .tab_button").toggleClass("btn-warning");
        $("#list-rooms li.new, #pms_tabs_grouper li.new").toggleClass("highlight");
    }, 800);

    // Open tab at new message in PMS
    if (msg.settings.data["enabled_pms"])
    msg.refresh.functions.add(function() {
        var new_data = [];
        for (id in msg.pms.list) {
            new_data.push(id);
        }
        msg.refresh.data["pms_to_open"] = {"todo": "check", "data": new_data};

        msg.refresh.callbacks.add(function(result){
          result = result.pms_to_open;

          for (id in result) {
            msg.pms.start_conversation(result[id], true);
          }
        });
    });


    // *** WAKE UP SYSTEM! ***
    msg.refresh.refresh();
});

// Refresh dynamic layout
msg.layout.refresh = function() {
    var body = $(window).height();
    var msg_area = $(".msg_textarea_wrapper").outerHeight();

    $("#msg-content-box").css("height", body - msg_area - msg.layout.top_fix - 35);
    $("div.list, .leftbar div.online").css("height", (body - msg.layout.top_fix) / 2 - 40);
}

// Refresh all data
msg.refresh.refresh = function() {
    if (msg.refresh.timeout) window.clearTimeout(msg.refresh.timeout);

    if (msg.refresh.xhr && msg.refresh.xhr.readyState != 4) {
        window.setTimeout(msg.refresh.refresh, 500);
        return false;
    }

    msg.el["page_loading_state"].show();

    msg.refresh.functions.fire();

    msg.refresh.active_data = msg.refresh.data;
    msg.refresh.active_callbacks = msg.refresh.callbacks;

    msg.refresh.data = {};
    msg.refresh.callbacks = $.Callbacks();

    msg.refresh.xhr = $.ajax({
        type:      'POST',
        cache:     false,
        url:       filename + '?todo=ajax',
        datatype:  'json',
        data:      {"payload": msg.refresh.active_data}
    }).done(function(result) {
        msg.el["page_loading_state"].hide();

        if (result.session_update.logged != "1")
          window.location = "./";

        msg.refresh.active_callbacks.fire(result);

        msg.refresh.timeout = window.setTimeout(msg.refresh.refresh, msg.settings.data["refresh_delay"]);

        if (!msg.first_room_loaded) {
          msg.first_room_loaded = true;
          msg.active_room.update({"todo": "select", "room_id": msg.el["list_rooms"].find("li").first().attr("data-id")});
          msg.refresh.refresh();
        }

    }).fail(function() {
        msg.el["page_loading_state"].hide();
        alert("XHR Failed!");
        msg.refresh.timeout = window.setTimeout(msg.refresh.refresh, msg.settings.data["refresh_delay"]);
    });
}

// Register session update event
msg.refresh.functions.add(function() {
    msg.refresh.data["session_update"] = {"todo": "check"};
});

// If no change of states yet, send actual states
msg.refresh.functions.add(function() {
    if (!msg.refresh.data["states"]) msg.system.states();
});

// If required: change states
// Then register states update event
msg.system.states = function(to_change) {
    var data = {"todo": "check"};
    if (to_change == "sounds") {
        data.to_change = "sounds";
        data.value =  msg.el["btn_sounds"].hasClass("active") ? "0" : "1";
    }

    if (to_change == "visible") {
        data.to_change = "visible";
        data.value =  msg.el["btn_visible"].hasClass("active") ? "0" : "1";
    }

    msg.refresh.data["states"] = data;

    msg.refresh.callbacks.add(function(result) {
        result = result.states;

        if (result.sounds == "1") {
            msg.el["btn_sounds"].addClass("active");
            msg.system.sounds = true;
        } else {
            msg.el["btn_sounds"].removeClass("active");
            msg.system.sounds = false;
        }

        if (result.visible == "1") {
            msg.el["btn_visible"].addClass("active");
            msg.system.visible = true;
        } else {
            msg.el["btn_visible"].removeClass("active");
            msg.system.visible = false;
        }
    });
}

// Register update of rooms list
msg.refresh.functions.add(function() {
    msg.refresh.data["rooms_list"] = {"todo": "check", "rooms": msg.rooms_list.list};

    msg.refresh.callbacks.add(function(result){
        my_send = result.send_message;
        result = result.rooms_list;

        if (result.type == "upgrade") {
            var html="";
            var new_rooms={};
            var new_message=false;
            for (id in result.data) {
                html += '<li data-id="'+ result.data[id].id +'" '+ ((result.data[id].unread == "1" && result.data[id].id != msg.active_room.room_id) ? 'class="new"' : '') +'><a href="#">'+ result.data[id].name +'</a></li>';
                new_rooms[result.data[id].id] = result.data[id].last_id;

                if (result.data[id].unread == "1" && msg.system.sounds && !my_send)
                  msg.system.play_sound();
            }

            msg.el["list_rooms"].html(html);
            msg.rooms_list.list = new_rooms;
            msg.el.list_room( msg.active_room.room_id ).addClass("active").removeClass("new");

        } else if (result.type == "update") {
            for (id in result.data) {
                msg.rooms_list.list[id] = result.data[id];

                if (id != msg.active_room.room_id)
                  msg.el.list_room(id).addClass("new");
            }

            if (msg.system.sounds && result.data && !my_send) msg.system.play_sound();
        }
    });
});

// If not load new selected room, update active room
msg.refresh.functions.add(function() {
    if (msg.active_room.room_id && !msg.refresh.data["update_room"])
      if (!msg.active_room.last_id)
          msg.active_room.update({"todo":"select", "room_id": msg.active_room.room_id});
        else
          msg.active_room.update({"todo":"update", "room_id": msg.active_room.room_id, "last_id": msg.active_room.last_id});
});

// Register active room: select, update, load_older
msg.active_room.update = function(input) {
    msg.refresh.data["update_room"] = input;

    msg.refresh.callbacks.add(function(result){
        result = result.update_room;

        var html = "";
            if (result.data) {
                for (id in result.data) {
                    html += '<li data-id="'+ result.data[id].id +'" data-user-id="'+ result.data[id].user_id +'"><img src="'+ result.data[id].avatar +'" class="clickable avatar img-rounded img-polaroid" /><div class="msg"><div class="header clearfix"><div class="user clickable">'+ result.data[id].from +'</div> '+ (msg.system.level == "administrator" ? '<div class="pull-left">&nbsp;| <a href="#" class="msg_delete">Delete</a></div>' : '') + ' <div class="info">'+ result.data[id].time +'</div> <div class="attachment" style="float: right; margin-right: 15px;">'+ (result.data[id].attachment.name ? '<a href="'+ filename +'?todo=attachment&id='+ result.data[id].attachment.id +'" target="_self"><i class="icon-file"></i>' + result.data[id].attachment.name : "") +'</a></div>   </div><div class="text">'+ result.data[id].text +'</div></div></li>';
                }
            }

        if (result.todo == "load_older" && !result.data) msg.active_room.at_end = true;
        if (result.todo == "select") msg.active_room.at_end = false;

        if (result.room_id) msg.active_room.room_id = result.room_id;
        if (result.first_id) msg.active_room.first_id = result.first_id;
        if (result.last_id) msg.active_room.last_id = result.last_id;

        if (result.todo == "select") {
            msg.el["msg_glass"].html(html);
            msg.el["list_rooms"].find("li").removeClass("active");
            msg.el.list_room(result.room_id).addClass("active").removeClass("new");

            msg.el["msg"].show(0);
            msg.el["msg_glass"].scrollTop(0);
            msg.el["msg_textarea"].focus();
            msg.layout.refresh();

        } else if (result.todo == "update") {
            msg.el["msg_glass"].prepend(html);
        } else if (result.todo == "load_older") {
            msg.el["msg_glass"].append(html);
        }
    });

}

// Send message
msg.active_room.send_message = function() {
    if (!msg.el["msg_textarea"].val()) return false;
    msg.el["msg_textarea"].attr("disabled", "disabled");
    msg.el["msg_btn_send"].attr("disabled", "disabled");
    msg.el["msg_btn_send_icon"].removeClass("icon-ok").addClass("icon-loading");

    msg.refresh.data["send_message"] = {
        "room_id": msg.active_room.room_id,
        "text": $("#msg_textarea").val(),
        "attachment_id": msg.active_room.attachment_id
    };

    msg.active_room.remove_attachment();

    msg.refresh.callbacks.add(function(result){
        result = result.send_message;

        msg.el["msg_btn_send_icon"].removeClass("icon-loading").addClass("icon-ok");
        msg.el["msg_textarea"].removeAttr("disabled").focus();
        msg.el["msg_btn_send"].removeAttr("disabled");

        if (result.status == "short") {
            alert(msg.lang["write_text"]);
        } else if (result.status == "long") {
            alert(msg.lang["too_long_message"]);
        } else if (result.status == "too_fast") {
            alert(msg.lang["you_are_too_fast"]);
        } else {
            msg.el["msg_textarea"].val("").height("14");
        }

        msg.layout.refresh();
    });

    msg.refresh.refresh();
}

// Start uploading attachment, then write all informations into site
msg.active_room.upload_attachment = function() {
    var file = msg.el["msg_att_input"].prop("files")[0];
    var formdata = new FormData();
    formdata.append("file", file);

    if (!file) return false;

    msg.el["msg_att_state_icon"].removeClass("icon-upload").addClass("icon-loading");
    $.ajax({
        url: filename + "?todo=uploader&type=attachment",
        type: "POST",
        data: formdata,
        processData: false,
        contentType: false,
        success: function (data) {
            msg.el["msg_att_state_icon"].removeClass("icon-loading").addClass("icon-upload");
            msg.el["msg_att_input"].wrap('<form>').closest('form').get(0).reset();
            msg.el["msg_att_input"].unwrap();

            if (data.status == "ok") {
                msg.el["msg_att_uploader"].hide(0);
                msg.el["msg_att_uploaded"].show(0);
                msg.el["msg_att_name"].text(data.file + " (" + data.size + "MB)");
                msg.active_room.attachment_id = data.attachment_id;
            }
        }
		});
}

// Remove attachment
msg.active_room.remove_attachment = function() {
    msg.el["msg_att_uploader"].show(0);
    msg.el["msg_att_uploaded"].hide(0);
    msg.active_room.attachment_id = false;
}

msg.active_room.admin_delete_message = function(msg_id) {
    msg.refresh.data["delete_message"] = {
        "msg_id": msg_id
    };

    msg.el.msg_message(msg_id).remove();

    msg.refresh.refresh();
}

// If sounds are ON, play
msg.system.play_sound = function() {
    if (msg.system.sounds)
      niftyplayer('niftyPlayer').play();
}

// Register update of users list
msg.refresh.functions.add(function() {
    msg.refresh.data["users_list"] = {
        "online_checksum": msg.users_list.online_checksum,
        "typeahead_checksum": msg.users_list.typeahead_checksum
    };

    msg.refresh.callbacks.add(function(result){
        result = result.users_list;

        //ONLINE
        if (result.online.todo == "update") {
            var html = "";
            if (result.online.data) for (id in result.online.data) {
                html += '<li data-user-id="'+ result.online.data[id].id +'"><a href="#" class="clickable"><img src="'+ result.online.data[id].avatar +'" class="avatar img-rounded img-polaroid">'+ result.online.data[id].login +'</a></li>';
            }

            msg.el["list_users"].html(html);
            msg.users_list.online_checksum = result.online.checksum;
        }

        //TYPEAHEAD
        if (result.typeahead.todo == "update") {
            msg.el["pms_typeahead"].data('typeahead').source = result.typeahead.data;
            msg.users_list.typeahead_checksum = result.typeahead.checksum;
        }

    });
});

// Initial opening of user card
msg.user_card.open = function(id) {
    msg.refresh.data["user_card"] = {"todo": "get", "user_id": id};

    msg.refresh.callbacks.add(function(result){
        result = result.user_card;
        msg.user_card.relationship = result.relationship;
        msg.user_card.user_id = result.user_id;

        // Clear
        msg.el["modal_uc_btn_save"]
          .add(msg.el["modal_uc_btn_discard"])
          .add(msg.el["modal_uc_edit_buttons"])
          .hide();

        msg.el["modal_uc_btn_delete_avatar"].removeClass("active");

        msg.el["modal_uc_login"].text(result.login);
        msg.el["modal_uc_status"].text(result.status);
        msg.el["modal_uc_avatar"].attr("src", result.avatar);

        if (result.relationship == "administrator" || result.relationship == "me")
            msg.el["modal_uc_btn_edit"].show();
              else
            msg.el["modal_uc_btn_edit"].hide();

        msg.el["modal_uc_avatar"].attr("src", result.avatar);

        if (result.state == "online")
            msg.el["modal_uc_login_icon"].show();
              else
            msg.el["modal_uc_login_icon"].hide();

        // Profile
        var html = "";
        for (id in result.profile) {
            html += '<tr><td>'+ id +'</td><td>'+ result.profile[id] +'</td><tr>';
        }

        msg.el["modal_uc_profile"].html(html);

        // Informations
        var html = "";
        for (id in result.informations) {
            html += '<tr><td>'+ id +'</td><td>'+ result.informations[id] +'</td><tr>';
        }

        if (html) {
            msg.el["modal_uc_informations"].html(html);
            msg.el["modal_uc_informations_headline"].show();
        } else
            msg.el["modal_uc_informations_headline"].hide();


        $(".modal").modal('hide');
        msg.el["modal_uc"].modal();
    });

    msg.refresh.refresh();
}

msg.user_card.start_edit = function() {
    msg.refresh.data["user_card_start_edit"] = {"user_id": msg.user_card.user_id};

    msg.refresh.callbacks.add(function(result){
        result = result.user_card_start_edit;

        msg.el["modal_uc_btn_save"]
          .add(msg.el["modal_uc_btn_discard"])
          .add(msg.el["modal_uc_edit_buttons"])
          .show();

        msg.el["modal_uc_btn_edit"].hide(0);

        if (result.ban == "1")
            msg.el["modal_uc_btn_ban_state"].addClass("active");
              else
            msg.el["modal_uc_btn_ban_state"].removeClass("active");

        if (msg.user_card.relationship == "administrator")
            msg.el["modal_uc_btn_ban"].show();
              else
            msg.el["modal_uc_btn_ban"].hide();

        // Status
          msg.el["modal_uc_status"].html('<textarea class="nomargin">'+ result.status +'</textarea>');

        // Profile
        var html = "";
        for (id in result.profile) {
            html += '<tr><td>'+ result.profile[id].name +'</td><td><input type="text" data-name-slug="'+ result.profile[id].name_slug +'" value="'+ result.profile[id].value +'" /></td><tr>';
        }

        msg.el["modal_uc_profile"].html(html);
    });

    msg.refresh.refresh();
}

msg.user_card.save = function() {
    var new_profile = [];
    msg.el["modal_uc_profile"].find("input").each(function(){
        new_profile.push({
          "name_slug": $(this).attr("data-name-slug"),
          "value": $(this).val()
        });
    });

    msg.refresh.data["user_card_save"] = {
        "user_id": msg.user_card.user_id,
        "profile": new_profile,
        "status": msg.el["modal_uc_status"].find("textarea").val(),
        "delete_avatar": (msg.el["modal_uc_btn_delete_avatar"].hasClass("active") ? "1" : "0"),
        "ban": (msg.el["modal_uc_btn_ban_state"].hasClass("active") ? "1" : "0")
    };

    // Re-open and start msg.refresh.refresh(); <- system hack
    msg.user_card.open(msg.user_card.user_id);
}

// PMS - start conversation
// Check if user exists, load his ID
msg.pms.start_conversation = function(login, nofocus) {
    msg.refresh.data["pms_start_conversation"] = {"login": login};

    msg.refresh.callbacks.add(function(result){
        result = result.pms_start_conversation;
        result.nofocus = nofocus;

        if (result.status == "ok") {
            if (!msg.pms.list[result.user_id]) {
                msg.pms.start(result);
            } else {
                msg.pms.open({"user_id": result.user_id, "nofocus": nofocus});
            }
        }
    });

    msg.refresh.refresh();
}

// Update grouper counter - count and new message alert
msg.pms.grouper_counter = function() {
    var count = Object.keys(msg.pms.list).length-2;

    msg.el["pms_grouper_count"].text(count);

    if (count >= 1) {
        msg.el["pms_grouper"].show();
    } else {
        msg.el["pms_grouper"].hide();
    }

    if (msg.el["pms_grouper_content"].find("li").hasClass("new")) {
        msg.el["pms_grouper"].addClass("new");
    } else {
        msg.el["pms_grouper"]
          .removeClass("new")
          .find("button").removeClass(" btn-warning");
    }
}

// Return element from live tab (first or second)
// @number - number: 1/2
msg.pms.active = function(number) {
    var length = msg.el["pms_tabs_content"].find(".tab:not(.grouped)").length;


    if (number == 1) var child = ":last";
    if (number == 2) var child = ":first";

    if (length == 1 && number == 1) var child = ":nth-child(999999)";

    return msg.el["pms_tabs_content"].find(".tab:not(.grouped)"+child).attr("data-user-id");
}

msg.pms.group = function(user_id) {
    var html = msg.el["prototype_group_tab"].clone();
    html = html.removeAttr("id").outerHTML();
    html = html.replace("{USER_LOGIN}", msg.pms.list[user_id].login);
    html = html.replace("{USER_ID}", user_id);
    msg.el["pms_grouper_content"].append(html);

    if (msg.el.live_tab(user_id).hasClass("new"))
      msg.el.grouped_tab(user_id).addClass("new");

    msg.el.live_tab(user_id).removeClass("active").addClass("grouped");
}

msg.pms.set_unseen = function(user_id) {
    msg.el.live_tab(user_id).addClass("new");
    msg.el.grouped_tab(user_id).addClass("new");
    msg.pms.grouper_counter();
}

msg.pms.set_seen = function(user_id) {
    msg.el.live_tab(user_id).removeClass("new");
    msg.el.live_tab(user_id).find(".tab_button").removeClass("btn-warning")
}

/* Start conversation
 * Create tab
 * Hide a tab if needed
 * Initial: Focus textarea,
 *          Set autosize for textarea - don't forget for callback:  msg.pms.tab_layout()
 *          Set scroll to load older
 */
msg.pms.start = function(data) {
    if (msg.pms.active(1) && msg.pms.active(2))  msg.pms.group(msg.pms.active(2));

    msg.pms.list[data.user_id] = {
        "user_id": data.user_id,
        "login": data.login
    };

    var html = msg.el["prototype_live_tab"].clone();
    html = html.removeAttr("id").outerHTML();
    html = html.replace("{USER_LOGIN}", data.login);
    html = html.replace("{USER_ID}", data.user_id);
    html = html.replace('id="pms_tab_prototype"', '');

    msg.el["pms_tabs_content"].append(html);

    if (!data.nofocus) msg.el.live_tab(data.user_id).find(".pms_textarea").focus();
    msg.el.live_tab(data.user_id).find(".pms_textarea").autosize({ "callback": function(){ msg.pms.tab_layout(data.user_id); } });

    msg.el.live_tab(data.user_id).find("ul.text_box").on("scroll", function(){
        if ($(this).scrollTop() == 0 && !msg.pms.list[data.user_id].at_end) {
            if (!msg.refresh.data["pms_update"]) msg.refresh.data["pms_update"] = [];
            msg.refresh.data["pms_update"].push({
                "user_id": data.user_id,
                "first_id": msg.pms.list[data.user_id].first_id,
                "todo": "load_older"
            });

            msg.refresh.refresh();
        }
    });


    msg.pms.grouper_counter();

    //load data
    msg.refresh.refresh();
}

// Open tab - live tab or grouped tab
msg.pms.open = function(data) {

    // If is opened, die
    if (!msg.el.live_tab(data.user_id).hasClass("grouped")) {
      if (!data.nofocus) msg.el.live_tab(data.user_id).find("textarea").focus();
      return false;
    }

    if (msg.pms.list[msg.pms.active(2)] && !data.after_close) msg.pms.group(msg.pms.active(2));

    msg.el.live_tab(data.user_id).removeClass("grouped").addClass("active");

    msg.el.live_tab(data.user_id).find(".text_box").scrollBottom();
    if (!data.nofocus) msg.el.live_tab(data.user_id).find(".pms_textarea").focus();

    msg.el.grouped_tab(data.user_id).remove();
    msg.pms.grouper_counter();
}

// Close a tab, remove HTML elements, unregister from msg.pms.list
msg.pms.close = function(data) {
    msg.el.grouped_tab(data.user_id).remove();

    delete window.msg.pms.list[data.user_id];

    if (data.user_id == msg.pms.active(1) || data.user_id == msg.pms.active(2)) {
        var user_to_open = msg.el["pms_grouper_content"].find("li:first").attr("data-user-id");
        msg.el.live_tab(data.user_id).remove();
        msg.pms.open({"user_id": user_to_open, "after_close": true});
    }

    msg.pms.grouper_counter();
}

// Make summary request for update PMS tab
msg.refresh.functions.add(function(){
    var send_data = [];
    if (!msg.refresh.data["pms_update"]) msg.refresh.data["pms_update"] = [];
    for (id in msg.pms.list) {
        msg.refresh.data["pms_update"].push({
            "user_id": msg.pms.list[id].user_id,
            "last_id": msg.pms.list[id].last_id,
            "todo": ((msg.pms.list[id].first_id && msg.pms.list[id].last_id) ? "update" : "select")
      });
    }

    msg.refresh.callbacks.add(function(result){
        result = result.pms_update;

        if(result && result.result) for(id in result.result) {
            msg.pms.update(result.result[id]);
        }
    });
});

// Process received data for tab - select, update or load older
msg.pms.update = function(data) {
    if (data.state == "online") {
        msg.el.live_tab(data.user_id).find(".login-state-icon").show();
        msg.el.grouped_tab(data.user_id).find(".login-state-icon").show();
    } else {
        msg.el.live_tab(data.user_id).find(".login-state-icon").hide();
        msg.el.grouped_tab(data.user_id).find(".login-state-icon").hide();
    }

    var html = "";
    if(data.messages) for (id in data.messages) {
        html = '<li data-id="'+ data.messages[id].id +'" data-user-id="'+ data.messages[id].user_id +'"><img src="'+ data.messages[id].avatar +'" class="clickable avatar img-rounded img-polaroid"><div class="msg"><div class="header clearfix"><div class="user clickable">'+ data.messages[id].from +'</div> <div class="info">'+ data.messages[id].time +'</div> </div><div class="text">'+ data.messages[id].text +' '+( data.messages[id].attachment.id ? '<hr /><a href="'+ filename +'?todo=attachment&id='+ data.messages[id].attachment.id +'">'+ data.messages[id].attachment.name +'</a>' : '' )+'</div></div></li>' + html;
    }

    if (data.first_id) msg.pms.list[data.user_id].first_id = data.first_id;
    if (data.last_id) msg.pms.list[data.user_id].last_id = data.last_id;

    if (data.todo == "update") {
        var at_bottom = msg.el.live_tab(data.user_id).find("ul.text_box").atBottom();
        msg.el.live_tab(data.user_id).find("ul.text_box").append(html);
        if (at_bottom) msg.el.live_tab(data.user_id).find("ul.text_box").scrollBottom();

        // Notification
        if (!msg.el.live_tab(data.user_id).find(".pms_textarea").is(":focus") && html) {
            msg.pms.set_unseen(data.user_id);
            if (msg.system.sounds) msg.system.play_sound();
        }

    } else if (data.todo == "select") {
        msg.el.live_tab(data.user_id ).find("ul.text_box")
          .html(html)
          .scrollBottom();

        // Notification
        if (html && data.unseen) {
            msg.pms.set_unseen(data.user_id);
            if (msg.system.sounds) msg.system.play_sound();
        }

    } else if (data.todo == "load_older") {
        if (!html) msg.pms.list[data.user_id].at_end = true;

        var path = msg.el.live_tab(data.user_id).find("ul.text_box");
        var before_height = path[0].scrollHeight;
        path.prepend(html);
        var after_height = path[0].scrollHeight;

        path.scrollTop(after_height - before_height);
    }
}

// Start uploading attachment
msg.pms.upload_attachment = function(user_id) {
    var path = msg.el.live_tab(user_id);
    var file = path.find("input.pms-attachment-input").prop("files")[0];
    var formdata = new FormData();
    formdata.append("file", file);

    if (!file) return false;
    path.find(".attachment i").removeClass("icon-upload").addClass("icon-loading");
    $.ajax({
        url: filename + "?todo=uploader&type=attachment",
        type: "POST",
        data: formdata,
        processData: false,
        contentType: false,
        success: function (data) {
            path.find(".attachment i").removeClass("icon-loading").addClass("icon-upload");
            path.find("input.pms-attachment-input").wrap('<form>').closest('form').get(0).reset();
            path.find("input.pms-attachment-input").unwrap();
            if (data.status == "ok") {
                path.find(".pms_attached_file_wrapper")
                  .show()
                  .find(".pms_attached_file").text(data.file + " (" + data.size + "MB)");
                msg.pms.tab_layout(user_id);
                msg.pms.list[user_id].attachment_id = data.attachment_id;
            }
        }
    });
}

// Remove attachment
msg.pms.remove_attachment = function(user_id) {
    msg.el.live_tab(user_id).find(".pms_attached_file_wrapper").hide();
    msg.pms.list[user_id].attachment_id = false;
    msg.pms.tab_layout(user_id);
}

// Measure the tab and resize it
// @user_id
msg.pms.tab_layout = function(user_id) {
    var path = msg.el.live_tab(user_id);
    var textarea_height = path.find(".pms_textarea_wrapper").outerHeight(false);

    path.find("ul.text_box").css("height", 250-(textarea_height-10-20)+"px");
}

// Send message
msg.pms.send_message = function(user_id) {
    var path = msg.el.live_tab(user_id);

    if (!path.find(".pms_textarea").val()) return false;
    path.find(".pms_textarea").attr("disabled", "disabled");
    path.find(".pms_send_button")
      .attr("disabled", "disabled")
      .find("i").removeClass("icon-ok").addClass("icon-loading");

    msg.refresh.data["pms_send_message"] = {
        "user_id": user_id,
        "text": path.find(".pms_textarea").val(),
        "attachment_id": msg.pms.list[user_id].attachment_id
    };

    msg.pms.remove_attachment(user_id);

    msg.refresh.callbacks.add(function(result){
        result = result.pms_send_message;
        var path = msg.el.live_tab(result.user_id);

        path.find(".pms_send_button i")
           .removeClass("icon-loading").addClass("icon-ok")
           .find(".pms_send_button").removeAttr("disabled");
        path.find(".pms_textarea").removeAttr("disabled").focus();

        if (result.status == "short") {
            alert(msg.lang("wirte_text"));
        } else if (result.status == "long") {
            alert(msg.lang["too_long_message"]);
        } else if (result.status == "too_fast") {
            alert(msg.lang["you_are_too_fast"]);
        } else {
            path.find(".pms_textarea").val("").height("22");
            msg.pms.tab_layout(result.user_id);
        }

        msg.layout.refresh();
    });

    msg.refresh.refresh();
}

msg.change_password.save = function() {
    msg.refresh.data["change_password"] = {
        "password_old": msg.el["modal_cp_pwd_old"].val(),
        "password_new": msg.el["modal_cp_pwd_new"].val(),
        "password_new_repeat": msg.el["modal_cp_pwd_new_repeat"].val()
    };

    msg.refresh.callbacks.add(function(result){
        result = result.change_password;

        msg.el["modal_cp_alert_text"].text(result.alert);
        msg.el["modal_cp_alert"].show();

        if (result.status == "1") {
            msg.el["modal_cp_pwd_old"].val("");
            msg.el["modal_cp_pwd_new"].val("");
            msg.el["modal_cp_pwd_new_repeat"].val("");
        }
    });

    msg.refresh.refresh();
}

// Start uploading attachment
msg.user_card.upload_avatar = function() {
    var user_id = msg.user_card.user_id;
    var file = msg.el["modal_uc_btn_change_avatar_input"].prop("files")[0];
    var formdata = new FormData();
    formdata.append("file", file);

    if (!file) return false;
    msg.el["modal_uc_btn_change_avatar"].find("button").html('<i class="icon-loading"></i> '+msg.lang["uploading"]).attr("disabled", "disabled");
    $.ajax({
        url: filename + "?todo=uploader&type=avatar&user_id="+msg.user_card.user_id,
        type: "POST",
        data: formdata,
        processData: false,
        contentType: false,
        success: function (data) {
          msg.el["modal_uc_btn_change_avatar_input"].wrap('<form>').closest('form').get(0).reset();
          msg.el["modal_uc_btn_change_avatar_input"].unwrap();

          msg.el["modal_uc_btn_change_avatar"].find("button").html(msg.lang["change_avatar"]).removeAttr("disabled");
          msg.el["modal_uc_avatar"].attr("src", data.file);
        }
    });
}

msg.settings.start_edit = function() {
    msg.refresh.data["settings_start_edit"] = {"todo": "get"};

    msg.refresh.callbacks.add(function(result){
        result = result.settings_start_edit;

        // Inserting
        msg.el["modal_settings"].find("#site_name").val(result.site_name);
        msg.el["modal_settings"].find("#profile_items_enc").val(result.profile_items_enc);
        msg.el["modal_settings"].find("#refresh_delay").val(result.refresh_delay);
        msg.el["modal_settings"].find("#delay_for_offline").val(result.delay_for_offline);
        msg.el["modal_settings"].find("#attachment_max_size").val(result.attachment_max_size);
        if (result.enabled_pms) msg.el["modal_settings"].find("#enabled_pms").prop("checked", true); else msg.el["modal_settings"].find("#enabled_pms").prop("checked", false);
        if (result.enabled_register) msg.el["modal_settings"].find("#enabled_register").prop("checked", true); else msg.el["modal_settings"].find("#enabled_register").prop("checked", false);
        if (result.direction_rtl) msg.el["modal_settings"].find("#direction_rtl").prop("checked", true); else msg.el["modal_settings"].find("#direction_rtl").prop("checked", false);
        msg.el["modal_settings"].find("#message_max_characters").val(result.message_max_characters);
        msg.el["modal_settings"].find("#messages_per_page").val(result.messages_per_page);
        msg.el["modal_settings"].find("#next_message_delay").val(result.next_message_delay);
        msg.el["modal_settings"].find("#datetime_format").val(result.datetime_format);
        if (result.enabled_attachments_room) msg.el["modal_settings"].find("#enabled_attachments_room").prop("checked", true); else msg.el["modal_settings"].find("#enabled_attachments_room").prop("checked", false);
        if (result.enabled_attachments_pms) msg.el["modal_settings"].find("#enabled_attachments_pms").prop("checked", true); else msg.el["modal_settings"].find("#enabled_attachments_pms").prop("checked", false);
        // Inserting

        msg.el["modal_settings"].modal();
    });

    msg.refresh.refresh();
}

msg.settings.save = function() {
    msg.el["modal_settings_btn_save"].attr("disabled", "disabled");
    msg.el["modal_settings_btn_save_icon"].removeClass("icon-ok-circle").addClass("icon-loading");

    msg.refresh.data["settings_save"] = msg.el["modal_settings_form"].serializeObject();

    msg.refresh.callbacks.add(function(result){
      result = result.settings_save;

      msg.el["modal_settings_btn_save"].removeAttr("disabled");
      msg.el["modal_settings_btn_save_icon"].removeClass("icon-loading").addClass("icon-ok-circle");

      msg.el["modal_settings"].modal('hide');
    });

    msg.refresh.refresh();
}

msg.admin_user.add_user = function() {
  msg.el["modal_au_headline_text"].text(msg.lang["add_user"]);
  msg.el["modal_au_btn_save_text"].text(msg.lang["add_user"]);

  msg.el["modal_au_login"].val("");
  msg.el["modal_au_password"].val("");
  msg.el["modal_au_level"].val(msg.el["modal_au_level"].find("option:first").val());

  msg.el["modal_au_alert"].hide();

  // Start add user
  msg.el["modal_au"].find("form")
    .unbind("submit")
    .submit(function(e){
      e.preventDefault();
      msg.admin_user.save();
  });

  $(".modal").modal("hide");
  msg.el["modal_au"].modal();
}

msg.admin_user.save = function() {
    msg.refresh.data["add_user"] = {
        "login": msg.el["modal_au_login"].val(),
        "password": msg.el["modal_au_password"].val(),
        "level": msg.el["modal_au_level"].val()
    };

    msg.refresh.callbacks.add(function(result){
        result = result.add_user;

        msg.el["modal_au_alert_text"].text(result.alert);
        msg.el["modal_au_alert"].show();

        if (result.status == "1") {
            msg.el["modal_au_login"].val("");
            msg.el["modal_au_password"].val("");
            msg.el["modal_au_level"].val(msg.el["modal_au_level"].find("option:first").val());
        }
    });

    msg.refresh.refresh();
}

msg.admin_user.save_edits = function(user_id) {
  msg.el["modal_settings_btn_save"].attr("disabled", "disabled");
  msg.el["modal_settings_btn_save_icon"].removeClass("icon-ok-circle").addClass("icon-loading");

  msg.refresh.data["save_edits"] = {
      "user_id": user_id,
      "login": msg.el["modal_au_login"].val(),
      "password": msg.el["modal_au_password"].val(),
      "level": msg.el["modal_au_level"].val()
  };

  msg.refresh.callbacks.add(function(result){
      result = result.save_edits;

      msg.el["modal_settings_btn_save"].removeAttr("disabled");
      msg.el["modal_settings_btn_save_icon"].removeClass("icon-loading").addClass("icon-ok-circle");

      if (result.status == "1") {
          $(".modal").modal("hide");
      } else {
          msg.el["modal_au_alert_text"].text(result.alert);
          msg.el["modal_au_alert"].show();
      }

  });

  msg.refresh.refresh();
}

msg.manage_users.open = function(page) {
    if (!page) page = 1;
    msg.refresh.data["manage_users"] = {"todo": "get", "page": page};

    msg.refresh.callbacks.add(function(result){
        result = result.manage_users;

        var html = "";
        for (id in result.users) {
          html += '<tr data-user-id="'+ result.users[id].id +'"><td>'+ result.users[id].id +'</td><td>'+ result.users[id].login +'</td><td>'+ result.users[id].level +'</td><td>'+ result.users[id].ban +'</td><td><a href="#" title="'+ msg.lang["profile"] +'" class="clickable"><i class="icon-align-left"></i></a> <a href="#" title="'+ msg.lang["edit"] +'" class="btn_edit"><i class="icon-pencil"></i></a> <a href="#" title="'+ msg.lang["delete"] +'" class="btn_delete"><i class="icon-trash"></i></a></td></tr>';
        }

        msg.el["modal_mu_table"].html(html);

        var html = "";
        for (var i=1;i<=result.pages;i++) {
          html += '<option value="'+ i +'" '+ ((i == page) ? 'selected="selected"' : '') +'>'+ i +'</option>';
        }

        msg.el["modal_mu_pagination"].html(html);

        $(".modal").modal("hide");
        msg.el["modal_mu"].modal();
    });

    msg.refresh.refresh();
}

msg.manage_users.edit = function(user_id) {
    msg.refresh.data["edit_user"] = {"user_id": user_id};

    msg.refresh.callbacks.add(function(result){
        result = result.edit_user;

        msg.el["modal_au_headline_text"].text(msg.lang["edit_user"]);
        msg.el["modal_au_btn_save_text"].text(msg.lang["edit_user"]);

        msg.el["modal_au_login"].val(result.login);
        msg.el["modal_au_level"].val(msg.el["modal_au_level"].find("option[value="+ result.level +"]").val());

        msg.el["modal_au_alert"].hide();

        // Start add user
        msg.el["modal_au"].find("form")
          .unbind("submit")
          .submit(function(e){
            e.preventDefault();
            msg.admin_user.save_edits(result.user_id);
        });

        $(".modal").modal('hide');
        msg.el["modal_au"].modal();
    });

    msg.refresh.refresh();

}

msg.manage_users.delete = function(user_id) {
  if (confirm(msg.lang["are_you_sure"])) {
    msg.refresh.data["delete_user"] = {"user_id": user_id};
    msg.el["modal_mu_table"].find("tr[data-user-id="+ user_id +"]").remove();
    msg.refresh.refresh();
  }
}

msg.manage_rooms.add = function(name) {
    msg.refresh.data["add_room"] = {"name": name};
    msg.manage_rooms.open();
}

msg.manage_rooms.open = function() {
    msg.refresh.data["get_rooms"] = {"todo": "get"};

    msg.refresh.callbacks.add(function(result){
      result = result.get_rooms;

      var html = "";
      for (id in result) {
        html += '<tr data-id="'+ result[id].id +'"><td>'+ result[id].id +'</td><td>'+ result[id].name +'</td><td><a href="#" class="btn_edit" data-name="'+ result[id].name +'" data-id="'+ result[id].id +'" title="'+ msg.lang["edit"] +'"><i class="icon-pencil"></i></a> <a href="#" class="btn_delete" title="'+ msg.lang["delete"] +'"><i class="icon-trash"></i></a></td></tr>';
      }

      msg.el["modal_mr_table"].html(html);
      msg.el["modal_mr"].modal();
    });

    msg.refresh.refresh();
}

msg.manage_rooms.change_name = function(name, room_id) {
    msg.refresh.data["room_change_name"] = {"name": name, "room_id": room_id};
    $(".modal").modal("hide");
    msg.manage_rooms.open();
}

msg.manage_rooms.delete = function(room_id) {
    msg.refresh.data["room_delete"] = {"name": name, "room_id": room_id};
    msg.el["modal_mr_table"].find("tr[data-id="+room_id+"]").remove();
    msg.refresh.refresh();
}