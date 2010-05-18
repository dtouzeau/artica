<?php

if(isset($_SESSION["uid"])){
	header("Location: user.php");
	die();exit;
}
	
header("Location: logon.php"); 
?>