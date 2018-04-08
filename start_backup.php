<?php 
/*
   _____    _   _    _                             
  |  __ \  (_) | |  | |                            
  | |__) |  _  | |__| |   ___    _ __ ___     ___  
  |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \ 
  | |      | | | |  | | | (_) | | | | | | | |  __/ 
  |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___| 

     S M A R T   H E A T I N G   C O N T R O L 

*************************************************************************"
* PiHome is Raspberry Pi based Central Heating Control systems. It runs *"
* from web interface and it comes with ABSOLUTELY NO WARRANTY, to the   *"
* extent permitted by applicable law. I take no responsibility for any  *"
* loss or damage to you or your property.                               *"
* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTILL UNLESS YOU KNOW *"
* WHAT YOU ARE DOING                                                    *"
*************************************************************************"
*/

require_once(__DIR__.'/st_inc/connection.php');
require_once(__DIR__.'/st_inc/functions.php');

$backup_emailfrom='info@pihome.eu';

//dump all mysql database and save as sql file
$dumpfname = $dbname . "_" . date("Y-m-d_H-i-s").".sql";
$command = "mysqldump --ignore-table=$dbname.backup --add-drop-table --host=$hostname --user=$dbusername ";
if ($dbpassword)
        $command.= "--password=". $dbpassword ." ";
$command.= $dbname;
$command.= " > " . $dumpfname;
shell_exec($command);

// compress sql file and unlink (delete) sql file after creating zip file. 
$zipfname = $dbname . "_mysql_" . date("Y-m-d_H-i-s").".zip";
$zip = new ZipArchive();
if($zip->open($zipfname,ZIPARCHIVE::CREATE))
{
   $zip->addFile($dumpfname,$dumpfname);
   $zip->close();
   unlink($dumpfname);
}

// get size of zip file to save size in database
function get_zip_originalsize($filename) {
    $size = 0;
    $resource = zip_open($filename);
    while ($dir_resource = zip_read($resource)) {
        $size += zip_entry_filesize($dir_resource);
    }
    zip_close($resource);
    return $size;
}

//email backup file as attachment
require_once('class.phpmailer.php');
$email = new PHPMailer();
$email->isHTML(true);
$email->From      = $backup_emailfrom;
$email->FromName  = settings($conn, 'name');
$email->AddAddress ( settings($conn, 'backup_email') );
$email->addCC      ( $backup_emailfrom );
$email->Subject   = 'PiHome MySQL DataBase Backup';
$email->Body      = 'This email is sent by ';
$file_to_attach = $zipfname;
$email->AddAttachment($file_to_attach);
return $email->Send();


if(!$mail->send()) {
   echo ' Message could not be sent Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo ' Message has been sent';
}
?>