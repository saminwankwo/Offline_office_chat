<?php
   
$id = is_numeric($_GET["id"]) ? $_GET["id"] : false;

if (!$user->logged or !$id) exit;
                             
$attachment = $db->query('
  SELECT att.*, msg.from_id, msg.to_id, msg.type 
  FROM `'. ATTACHMENTS_TABLE .'` AS att
  JOIN `'. MESSAGES_TABLE .'` AS msg ON msg.attachment_id = att.id
  WHERE att.`id` = "'.$id.'"
  LIMIT 1
')->fetch_object();

if ($attachment->type == "private" && $attachment->from_id != $user->user_id && $attachment->to_id != $user->user_id) return false;
                               
$file = STORAGE_DIR . "/" . $attachment->file;
                                       
if (!file_exists($file) or !$attachment) return false;
                                     
$len = filesize($file);
$filename = $attachment->name;
$file_extension = strtolower(substr(strrchr($filename,"."),1));

switch( $file_extension ) {
   case "exe":  $ctype="application/octet-stream"; break;
   case "zip":  $ctype="application/zip"; break;
   case "mp3":  $ctype="audio/mpeg"; break;
   case "mpg":  $ctype="video/mpeg"; break;
   case "avi":  $ctype="video/x-msvideo"; break;
   case "gz":   $ctype="application/gzip"; break;
   case "xls":  $ctype="application/msexcel"; break;
   case "xla":  $ctype="application/msexcel"; break;
   case "hlp":  $ctype="application/mshelp"; break;
   case "chm":  $ctype="application/mshelp"; break;
   case "ppt":  $ctype="application/mspowerpoint"; break;
   case "pps":  $ctype="application/mspowerpoint"; break;
   case "doc":  $ctype="application/msword"; break;
   case "dot":  $ctype="application/msword"; break;
   case "dot":  $ctype="application/msword"; break;
   case "pdf":  $ctype="application/pdf"; break;
   case "ps":   $ctype="application/postscript"; break;
   case "rtf":  $ctype="application/rtf"; break;
   case "xml":  $ctype="application/xml"; break;
   case "swf":  $ctype="application/x-shockwave-flash"; break;
   case "wav":  $ctype="application/x-wav"; break;
   case "gif":  $ctype="application/gif"; break;
   case "jpeg": $ctype="application/jpeg"; break;
   case "jpg":  $ctype="application/jpeg"; break;
   case "png":  $ctype="application/png"; break;
   case "tiff": $ctype="application/tiff"; break;
   case "tif":  $ctype="application/tiff"; break;
   case "csv":  $ctype="text/comma-separated-values"; break;
   case "txt":  $ctype="text/plain"; break;
   default: $ctype="application/force-download";
}
   //Begin writing headers
   header("Pragma: public");
   header("Expires: 0");
   header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
   header("Cache-Control: public");
   header("Content-Description: File Transfer");

   //Use the switch-generated Content-Type
   header("Content-Type: $ctype");

   //Force the download
   header("Content-Disposition: attachment; filename=".$filename.";");
   header("Content-Transfer-Encoding: binary");
   header("Content-Length: ".$len);
   @readfile($file);