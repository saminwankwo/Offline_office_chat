<?php   

  $data = $_POST["payload"];
  $result = array();
  
  foreach ($data AS $fn => $fn_data): 
    if ($actions[$fn]) {
                    
      if ( $actions[$fn][0] == "yes" && !$user->logged ) break;       
      if ( $actions[$fn][0] == "no" && $user->logged ) break;  
      
      if ( is_array($actions[$fn][1]) && !in_array($user->level, $actions[$fn][1]) ) break;  
      if ( $actions[$fn][1] && $actions[$fn][1] != $user->level ) break;  
      
      if ( $actions[$fn][2] && !$settings->{$actions[$fn][2]} ) break;
      
      // Run action!
      $result[$fn] = $actions[$fn][3]($fn_data);
    }
  endforeach;
                                                       
  echo json_encode($result);