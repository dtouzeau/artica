<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==true){}else{header('location:users.index.php');exit;}		
	
	if(isset($_GET["tab"])){main_switch();exit;}
	if(isset($_GET["ajaxmenu"])){echo popup();exit;}
	if(isset($_GET["js"])){main_ajax();exit;}
	if(isset($_GET["AdressBookPopup"])){echo AdressBookPopup();exit;}
	if(isset($_GET["EnableRemoteAddressBook"])){AdressBookPopup_save();exit;}

page();	
function page(){
$page=CurrentPageName();	
$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==true){}else{header('location:users.index.php');exit;}	
$sys=new systeminfos();

$distri=$sys->ditribution_name;


$html="
<div class=caption>Distribution: <strong>$distri</strong>&nbsp;Kernel:&nbsp;$sys->kernel_version&nbsp;LIBC:&nbsp;$sys->libc_version</div>
<table style='width:600px' align=center>
<tr>
<td width=1% valign='top'>
	<table>
		<tr>
			<td width=1% valign='top'>
				<img src='img/system.jpg'>
			</td>
			<td valign='top'>
				<table style='width:100%'>
					<tr><td valign='top'>  ".Paragraphe('folder-tasks-64.jpg','{system_tasks}','{system_tasks_text}','system.tasks.settings.php') ."</td></tr>
					<tr><td valign='top' >".Paragraphe('folder-network-64.jpg','{nic_infos}','{nic_infos_text}','system.nic.config.php') ."</td></tr>
				</table>
			</td>
		</tr>
	</table>
</td>
</tr>
</table>
	
	
	
<script>
".add_script()."
</script>

";
$tpl=new template_users('System',$html);
echo $tpl->web_page;
}

function popup(){
	if(GET_CACHED(__FILE__,__FUNCTION__,null)){return;}
	$html=main_tab();
	
	
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	SET_CACHED(__FILE__,__FUNCTION__,null,$html);
	echo $html;
	
}

function add_script(){
	$page=CurrentPageName();
	$html="
	 function AdressBookPopup(){
 		YahooWin(600,'$page?AdressBookPopup=yes');
 	}
 
function x_AddressBookSave(obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){
                alert(tempvalue);
	}
	AdressBookPopup();
}
	
 
 function AddressBookSave(){
   if(!document.getElementById('EnableRemoteAddressBook')){
   	return false;
   }
   
if(!document.getElementById('EnablePerUserRemoteAddressBook')){
   	return false;
   }   
   
   
   
   
   
    var XHR = new XHRConnection();
	XHR.appendData('EnableRemoteAddressBook',document.getElementById('EnableRemoteAddressBook').value);
	XHR.appendData('EnablePerUserRemoteAddressBook',document.getElementById('EnablePerUserRemoteAddressBook').value);
	XHR.appendData('EnableNonEncryptedLdapSession',document.getElementById('EnableNonEncryptedLdapSession').value);
	document.getElementById('rdr').innerHTML=\"<center style='width:400px'><img src='img/wait_verybig.gif'></center>\";
	XHR.sendAndLoad('$page', 'GET',x_AddressBookSave);
 
 }

	";
	
	return $html;
	
	
}


function main_ajax(){
	
	if(GET_CACHED(__FILE__,__FUNCTION__,null)){return;}
	$adds=add_script();
	$page=CurrentPageName();
	$sys=new systeminfos();
	$distri=$sys->ditribution_name;	
	$sys->libc_version=trim($sys->libc_version);
	$sys->kernel_version=trim($sys->kernel_version);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{system}");
	
	
	$html="
	$adds
	
	function LoadSystem(){
		$('#BodyContent').load('$page?ajaxmenu=yes');
		//YahooWinS(750,'$page?ajaxmenu=yes','$title:: $distri&nbsp;Kernel:&nbsp;$sys->kernel_version&nbsp;LIBC:&nbsp;$sys->libc_version');
		//setTimeout(\"LoadSystemBack()\",900);
	}
	
	function LoadSystemBack(){
		$back
		YahooSetupControlHide();
	}
	
	LoadSystem();
	";
	
	SET_CACHED(__FILE__,__FUNCTION__,null,$html);
	echo $html;
	
}


function main_switch(){
	
	switch ($_GET["tab"]) {
		case "system":main_system();exit;break;
		case "network":main_network();exit;break;
		case "services":main_services();exit;break;
		case "dns":main_dns();exit;break;
		case "upd":main_update();exit;break;
		default:main_system();exit;break;
			
	}
	
}


function main_tab(){
	$tpl=new templates();
	if($_GET["tab"]==null){$_GET["tab"]="system";};
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	$array["system"]=$tpl->_ENGINE_parse_body('{system}');
	$array["network"]=$tpl->_ENGINE_parse_body('{network}');
	$array["dns"]=$tpl->_ENGINE_parse_body('{dns}');
	$array["services"]=$tpl->_ENGINE_parse_body('{services}');
	$array["upd"]=$tpl->_ENGINE_parse_body('{update}');
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		if(strlen($ligne)>28){
			$ligne=texttooltip(substr($ligne,0,25)."...",$ligne,null,null,1);
		}
		
		$html[]= "<li><a href=\"$page?tab=$num&hostname=$hostname\"><span>$ligne</span></li>\n";
		
		//$html=$html . "<li><a href=\"javascript:LoadAjax('main_system_settings','$page?tab=$num&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=main_system_settings style='width:100%;height:600px;overflow:auto;background-color:white;'>
				<ul>". implode("\n",$html)."</ul>
		</div>
		<script>
				$(document).ready(function(){
					$('#main_system_settings').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			

			});
		</script>";			
	
	
	
}

function main_system(){
	if(GET_CACHED(__FILE__,__FUNCTION__,null)){return;}
	$users=new usersMenus();
	$automount=Buildicon64("DEF_ICO_AUTOFS_CENTER");
	$disks=Buildicon64("DEF_ICO_DISKS");
	$ntp=Buildicon64('DEF_ICO_NTP');
	$hardware=Paragraphe('folder-hardware-64.png','{hardware_info}','{hardware_info_text}',"javascript:Loadjs('system.hardware.php')");
	$memory=Paragraphe('folder-memory-64.png','{memory_info}','{memory_info_text}',"javascript:Loadjs('system.memory.php?js=yes')");
	$proc_infos=Buildicon64("DEF_ICO_PROC_INFOS");
	$philesight=Buildicon64('DEF_ICON_PHILESIGHT');
	$mysql=Buildicon64('DEF_ICO_MYSQL');
	$usb=Buildicon64('DEF_ICO_DEVCONTROL');
	$perfs=Paragraphe('64-performances.png','{artica_performances}','{artica_performances_text}',"javascript:Loadjs('artica.performances.php')");
	$zabix=Buildicon64("DEF_ICO_ZABBIX");
	
	if(!$users->ZABBIX_INSTALLED){$zabix=null;}
	$tr[]=$ntp;
	$tr[]=$hardware;
	$tr[]=$memory;
	$tr[]=$proc_infos;
	$tr[]=$philesight;
	$tr[]=$zabix;
	$tr[]=$perfs;
	$tr[]=$automount;
	$tr[]=$disks;
	$tr[]=$usb;

	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	
	
	$html=implode("\n",$tables);

	
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	SET_CACHED(__FILE__,__FUNCTION__,null,$html);
	echo $html;
}
	
function main_network(){
if(GET_CACHED(__FILE__,__FUNCTION__,null)){return;}
	$users=new usersMenus();
	$fw=Paragraphe('folder-64-firewall-grey.png','{APP_IPTABLES}','{error_app_not_installed_disabled}','','error_app_not_installed_disabled');
	$nmap=Paragraphe('folder-64-nmap-grey.png','{APP_NMAP}','{error_app_not_installed_disabled}','','error_app_not_installed_disabled');
	$network=Paragraphe('network-connection2.png','{net_settings}','{net_settings_text}',"javascript:Loadjs('system.nic.config.php?js=yes')",'net_settings_text');
	$pdns=Buildicon64('DEF_ICO_PDNS');
	 
	
	if($users->IPTABLES_INSTALLED){
			$fw=Paragraphe('folder-64-firewall.png','{APP_IPTABLES}','{APP_IPTABLES_TEXT}','iptables.index.php');
			}	
			
	if($users->HAMACHI_INSTALLED){
		$hamachi=Paragraphe('logmein_logo-64.gif','{APP_AMACHI}','{APP_AMACHI_TEXT}',"javascript:Loadjs('hamachi.php')");
	}
	
	
			
	if($users->nmap_installed){
			$nmap=Paragraphe('folder-64-nmap.png','{APP_NMAP}','{APP_NMAP_TEXT}','nmap.index.php');
			}

	$gateway=Paragraphe('relayhost.png','{APP_ARTICA_GAYTEWAY}','{APP_ARTICA_GAYTEWAY_TEXT}',"javascript:Loadjs('index.gateway.php?script=yes')");	
	$dhcp=Buildicon64('DEF_ICO_DHCP');
	
	if($users->OPENVPN_INSTALLED){
		$openvpn=Buildicon64('DEF_ICO_OPENVPN');
	}
if(!$user->POWER_DNS_INSTALLED){$pdns=null;}
	if($users->KASPERSKY_SMTP_APPLIANCE){
		$openvpn=null;
		$dhcp=null;
		$nmap=null;
		$fw=null;
		$gateway=null;
		
		
	}
	
	
	$tr[]=$network;
	$tr[]=$gateway;
	$tr[]=$dhcp;
	$tr[]=$pdns;
	$tr[]=$openvpn;
	$tr[]=$nmap;
	$tr[]=$fw;


	
	$tables[]="<table style='width:100%'><tr>";
	$t=0;
	while (list ($key, $line) = each ($tr) ){
			$line=trim($line);
			if($line==null){continue;}
			$t=$t+1;
			$tables[]="<td valign='top'>$line</td>";
			if($t==3){$t=0;$tables[]="</tr><tr>";}
			
	}
	if($t<3){
		for($i=0;$i<=$t;$i++){
			$tables[]="<td valign='top'>&nbsp;</td>";				
		}
	}
					
	$tables[]="</table>";	
	
	$html=implode("\n",$tables);	
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	SET_CACHED(__FILE__,__FUNCTION__,null,$html);
	echo $html;
}

function main_services(){
	if(GET_CACHED(__FILE__,__FUNCTION__,null)){return;}
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	if($users->DOTCLEAR_INSTALLED){
		$dotclear=Paragraphe('64-dotclear.png','{APP_DOTCLEAR}','{APP_DOTCLEAR_TEXT}','dotclear.index.php','APP_DOTCLEAR_TEXT');
	}
	
	if($users->OBM2_INSTALLED){
		$obm2=Paragraphe('64-obm2.png','{APP_OBM2}','{APP_OBM2_TEXT}',"javascript:Loadjs('obm2.index.php')",'APP_OBM2_TEXT');
	}
		
	
	if($users->openldap_installed){
		$addressbook=Paragraphe("64-addressbook.png","{remote_addressbook}","{remote_addressbook_text}","javascript:AdressBookPopup();");
	}
	
	if($users->XAPIAN_PHP_INSTALLED){
		$instantsearch=Paragraphe("64-xapian.png","{InstantSearch}","{InstantSearch_text}","javascript:Loadjs('instantsearch.php');");
	}

	if($users->OPENGOO_INSTALLED){
		$opengoo=Paragraphe("64-opengoo.png","{APP_OPENGOO}","{APP_OPENGOO_TEXT}","javascript:Loadjs('opengoo.php');");
	}		

	if($users->phpldapadmin_installed){
		$phpldapadmin=Paragraphe('phpldap-admin-64.png','{APP_PHPLDAPADMIN}','{APP_PHPLDAPADMIN_TEXT}',"javascript:s_PopUpFull('ldap/index.php',1024,800);","{artica_events_text}");
	}	
	
	
	$userautofill=Paragraphe('member-add-64.png','{auto_account}','{auto_account_text}',"javascript:Loadjs('auto-account.php?script=yes')",'auto_account_text');
	$installed_applis=Paragraphe('folder-applications-64.jpg','{installed_applications}','{installed_applications_text}','system.applications.php','installed_applications_text');
	$add_remove=Paragraphe('add-remove-64.png','{application_setup}','{application_setup_txt}',"javascript:Loadjs('setup.index.php?js=yes')");
	$services=Paragraphe('folder-servicesm-64.jpg','{manage_services}','{manage_services_text}','javascript:Loadjs("admin.index.services.status.php?js=yes");','manage_services_text');

	$tr[]=$installed_applis;
	$tr[]=$add_remove;
	$tr[]=$services;
	$tr[]=$phpldapadmin;
	$tr[]=$addressbook;
	$tr[]=$userautofill;
	$tr[]=$obm2;
	$tr[]=$dotclear;


	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	

$html=implode("\n",$tables);	
	
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	SET_CACHED(__FILE__,__FUNCTION__,null,$html);
	echo $html;
}

function main_dns(){
	
	
	$user=new usersMenus();
	$static=Paragraphe('folder-64-dns-grey.png','{nic_static_dns}','{nic_static_dns_text}','');
	$bind9=ICON_BIND9();
	$etc_hosts=Buildicon64("DEF_ICO_ETC_HOSTS");
	$pdns=Buildicon64('DEF_ICO_PDNS');
	$dyndns=Paragraphe('folder-64-dyndns.png','{nic_dynamic_dns}','{nic_dynamic_dns_text}','system.nic.dynamicdns.php');
	
	if($user->KASPERSKY_SMTP_APPLIANCE){
		$dyndns=null;
	}
	
	if(!$user->BIND9_INSTALLED){
		$static=null;
		$bind9=null;
	}	
	
	if(!$user->POWER_DNS_INSTALLED){$pdns=null;}
	
	$tr[]=$pdns;
	$tr[]=$bind9;
	$tr[]=$static;
	$tr[]=$dyndns;
	$tr[]=$etc_hosts;

	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	

$html=implode("\n",$tables);	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}

function main_update(){
	
			
	$html="<table style='width:100%'>
	<tr>
	<td valign='top' >".Paragraphe('64-dar-index.png','{incremental_backup}','{incremental_backup_text}',"javascript:Loadjs('dar.index.php?js=yes');",'repository_manager_text') ."</td>
	<td valign='top' >".Paragraphe('folder-64-artica-update.png','{artica_autoupdate}','{artica_autoupdate_text}',"javascript:Loadjs('artica.update.php?js=yes')",'artica_autoupdate_text') ."</td>
	<td valign='top' >".Buildicon64("DEF_ICO_APT") ."</td>
	</tr>
	<td valign='top' >".Paragraphe('64-retranslator.png','{APP_KRETRANSLATOR}','{APP_KRETRANSLATOR_TEXT}',"javascript:Loadjs('index.retranslator.php')",'APP_KRETRANSLATOR_TEXT') ."</td>
	<td valign='top' >&nbsp;</td>
	<td valign='top' >&nbsp;</td>
	</tr>
	
	
	</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);			
	
}
function AdressBookPopup(){
	
	$users=new usersMenus();
	$artica=new artica_general();
	$info=Paragraphe_switch_img('{remote_addressbook}','{remote_addressbook_explain}','EnableRemoteAddressBook',$artica->EnableRemoteAddressBook,'{enable_disable}',290);
	$singleAddressBook=Paragraphe_switch_img('{per_user_addressbook}','{per_user_addressbook_explain}','EnablePerUserRemoteAddressBook',$artica->EnablePerUserRemoteAddressBook,'{enable_disable}',290);
	$singleAddressBook_button="<input type='button' OnClick=\"javascript:AdressBookAcls();\" value='{edit_acls}&nbsp;&raquo;'>";
	
	
	if($users->SLPAD_LOCKED){
		$info=Paragraphe('warning64.png','{error}','{ERROR_SLAPDCONF_LOCKED}',null,null,210,null,1);
		$singleAddressBook=null;
		$singleAddressBook_button=null;
	}
	
	$singleAddressBook_button=null;
	
$html="<div id='rdr'>
	<H1>{remote_addressbook}</H1>
	<p class=caption>{remote_addressbook_text}</p>
	<table style='width:100%'>
	<tr>

	<td valign='top' width=100%>
		<table style='width:100%'>
		<tr>
			<td valign='top' style='width:300px'>
				$info
			</td>
			<td valign='top'>
				<input type='button' OnClick=\"javascript:AddressBookSave();\" value='{edit}&nbsp;&raquo;'>
			</td>
		</tr>
		</table>
		
		<br>
		
		<table style='width:100%'>
		<tr>
			<td valign='top' style='width:300px'>
				$singleAddressBook
			</td>
			<td valign='top'>
				<input type='button' OnClick=\"javascript:AddressBookSave();\" value='{edit}&nbsp;&raquo;'>
				<hr>
				<table style='width:100%'>
					<tr>
						<td class=legend>{enable_non_encrypted_sessions}</td>
						<td>" . Field_numeric_checkbox_img('EnableNonEncryptedLdapSession',$users->EnableNonEncryptedLdapSession)."</td>
					</tr>
				</table>
				$singleAddressBook_button
			</td>
		</tr>
		</table>		
		
		
		
	</td>
	
	</tr>
	</table>
	</div>
	";
	
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);		

}

function AdressBookPopup_save(){
	$artica=new artica_general();
	$artica->EnableRemoteAddressBook=$_GET["EnableRemoteAddressBook"];
	$artica->EnablePerUserRemoteAddressBook=$_GET["EnablePerUserRemoteAddressBook"];
	$artica->EnableNonEncryptedLdapSession=$_GET["EnableNonEncryptedLdapSession"];
	$artica->Save();
	$sock=new sockets();
	
	$LdapAclsPlus=$sock->GET_INFO('LdapAclsPlus');
	//if(trim($LdapAclsPlus)==null){$LdapAclsPlus=AdressBookAclDefault();}
	$LdapAclsPlus=AdressBookAclDefault();
	$sock->SaveConfigFile(CleanLdapAclsPlus($LdapAclsPlus),"LdapAclsPlus");
	
	$sock->getfile('OpenLdapRestart');
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("{success}:Address Book\n");
	
}
function AdressBookAclDefault(){
	$ldap=new clladp();
	$html="access to dn.regex=\"^ou=([^,]+),ou=People,dc=([^,]+),dc=NAB,$ldap->suffix$\"\n";
	$html=$html . "\tattrs=entry,@inetOrgPerson\n";
	$html=$html . "\tby dn.exact,expand=\"cn=$1,ou=users,ou=$2,dc=organizations,$ldap->suffix\" write\n";
	return $html;
}

function CleanLdapAclsPlus($content){
	$tbl=explode("\n",$content);
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$conf=$conf . "$ligne\n";
		
	}
	
	return $conf;
	
}


 	
	



?>	