<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.mailboxes.inc');
include_once('ressources/class.main_cf.inc');
include_once('ressources/class.status.inc');

if(!isset($_SESSION["uid"])){header('location:logon.php');exit;}
if(isset($_SESSION["uid"])){header('location:users.index.php');exit;}










?>
