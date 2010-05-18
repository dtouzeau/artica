<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	

page();	
function page(){
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==true){}else{header('location:users.index.php');exit;}	
$html="<table style='width:600px' align=center>
<tr>
<td width=50% valign='top' class='caption' style='text-align:justify'>
<img src='img/bg_dns.jpg'><p>
{dnsmasq_intro}</p></td>
<td valign='top'>
	<table>";

if($usersmenus->AsPostfixAdministrator==true){
		$html=$html . "
		<tr><td valign='top' >".Paragraphe('folder-tools-64.jpg','{dnsmasq_DNS_cache_settings}','{dnsmasq_DNS_cache_settings_text}','dnsmasq.dns.settings.php') ."</td></tr>
		<tr><td valign='top' >".Paragraphe('folder-storage-64.jpg','{dnsmasq_DNS_records}','{dnsmasq_DNS_records_text}','dnsmasq.records.settings.php') ."</td></tr>
		<tr><td valign='top'>  ".Paragraphe('folder-logs-64.jpeg','{events}','{events_text}','dnsmasq.daemon.events.php') ."</td></tr>";
		}

		

		
$html=$html . "</table>
</td>
</tr>
</table>
";
$tpl=new template_users('DnsMasq',$html);
echo $tpl->web_page;
	
	
	
}
	