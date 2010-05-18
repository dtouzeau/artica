<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.dnsmasq.inc');
	include_once('ressources/class.main_cf.inc');
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){header('location:users.index.php');exit;}	


page();	
function page(){
	
	
$html =ParseLogs();	
	
$JS["JS"][]='js/dnsmasq.js';
$tpl=new template_users('Dnsmasq&nbsp;{events}',$html,0,0,0,10,$JS);
echo $tpl->web_page;
	
}


function ParseLogs(){
	
	
	$sock=new sockets();
	$datas=$sock->getfile('dnsmasqlogs');
	$tbl=explode("\n",$datas);
	if(!is_array($tbl)){return null;}
	$tbl=array_reverse($tbl);
	$html="<table style='width:100%'>";
	while (list ($index, $line) = each ($tbl) ){
		if($line<>null){
			$html=$html  . "
			<tr>
			<td width='1%' valign='middle' class=bottom><img src='img/fw_bold.gif'><td>
			<td class=bottom>$line</td>
			</tr>";
		}
		
	}
	
	return $html . "</table>";
	
}

