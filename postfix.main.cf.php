<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsMailBoxAdministrator==false){header('location:users.index.php');exit;}

main_cf_page();
function main_cf_page(){
	
	$sock=new sockets();
	$datas=$sock->getfile('main.cf');
	
	$datas=str_replace("\n","<br>",$datas);
	$datas=str_replace("<br><br>","<br>",$datas);
	$datas=str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$datas);
	
	$tpl=new template_users('{main.cf}',"<div style='padding:5px;border:1px solid #CCCCCC'><code style='font-size:10px'>$datas</code></div>");
	
	echo $tpl->web_page;
	
}
	
?>	

