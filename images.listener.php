<?php
include_once('ressources/class.sockets.inc');


if(isset($_GET["mailattach"])){mailattach();exit;}

if(!isset($_GET["uri"])){exit;}

$sock=new sockets();




$sock->downloadFile($_GET["uri"],'127.0.0.1');



function mailattach(){
	$file="/opt/artica/share/www/attachments/{$_GET["mailattach"]}";
header("Content-type: application/force-download" );
header("Content-Disposition: attachment; filename=\"{$_GET["mailattach"]}\"");
header("Content-Length: ".filesize($file)."" );
header("Expires: 0" );
readfile($file); 	

}


?>