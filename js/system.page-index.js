$(document).ready(function(){
  $("input[type=text]:first").focus();
});

function send_form(element_id) {  
  if ( $("form #"+element_id+"input[value=]").data() ) {
    $("form #"+element_id+"input[value=]:first").focus();
  } else {
    var serialize = $("body form#"+element_id).serializeObject();
    var data = {"payload" : {}};
    data.payload[serialize.action] = serialize;
    
    $("body form#"+element_id+" input, body form#"+element_id+" button").attr("disabled", "disabled");

    var loginRequest = $.ajax({
      type:      'POST',
      cache:     false,
      url:       '?todo=ajax',
      datatype:  'json',
      data:      data
    }).done(function(data) {
      $("body form#"+element_id+" input, body form#"+element_id+" button").removeAttr("disabled");
      
      data = data[element_id]; 
      if (data.status == 1) 
          window.location = filename;
        else {
          $("#alert_text").text(data.alert);
          $(".alert").show(0);
        }
            
    }).fail(function() {
      alert("Fail!");
    });

  }
}