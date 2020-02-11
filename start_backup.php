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

//PHPMailer 
require(__DIR__.'/st_inc/phpmailer/PHPMailer.php');
require(__DIR__.'/st_inc/phpmailer/Exception.php');
$mail = new PHPMailer\PHPMailer\PHPMailer();


$backup_emailfrom='noreply@pihome.eu';

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
if($zip->open($zipfname,ZIPARCHIVE::CREATE)){
   $zip->addFile($dumpfname,$dumpfname);
   $zip->close();
   unlink($dumpfname);
}
try {
    //Recipients
    $mail->setFrom($backup_emailfrom, settings($conn, 'name'));
	//Recipient email 
	$mail->addAddress(settings($conn, 'backup_email'));
    // Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'DataBase Backup';
    $mail->Body    = '
	This email is sent by '.settings($conn, 'name').' Version <b>'.settings($conn, 'version').'</b> Build Number <b>'.settings($conn, 'build').'</b></br>
	Email contain database backup for your <b>'.settings($conn, 'name').'.</b> </br>
	</br>
	<b>..::Technical Info::..</b></br>
	Contains Drop Table: <b>Yes </b></br> 
	Contain Tables: <b>Yes </b></br> 
	Contains Data: <b>Yes </b></br> 
	Comments:<b></b> </br>';

	// Attachments
	$mail->addAttachment($zipfname);

    $mail->send();
    echo 'Message has been sent \n';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>