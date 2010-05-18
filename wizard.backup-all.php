<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.cron.inc');
	include_once('ressources/class.backup.inc');

$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["popup-ressource"])){popup_resource();exit;}
	if(isset($_GET["popup-schedule"])){popup_schedule();exit;}
	if(isset($_GET["W_RESOURCE"])){$_SESSION["WIZARD"]["W_RESOURCE"]=$_GET["W_RESOURCE"];exit;}
	if(isset($_GET["W_UUID"])){$_SESSION["WIZARD"]["W_UUID"]=$_GET["W_UUID"];exit;}
	
	if(isset($_GET["W_SMB_SERVER"])){
		$_SESSION["WIZARD"]["W_SMB_SERVER"]=$_GET["W_SMB_SERVER"];
		$_SESSION["WIZARD"]["W_SMB_USERNAME"]=$_GET["W_SMB_USERNAME"];
		$_SESSION["WIZARD"]["W_SMB_PASSWORD"]=$_GET["W_SMB_PASSWORD"];
		$_SESSION["WIZARD"]["W_SMB_SHAREDDIR"]=$_GET["W_SMB_SHAREDDIR"];
		exit;
		}
		
	if(isset($_GET["CRON_DAYS"])){
		$_SESSION["WIZARD"]["CRON_DAYS"]=$_GET["CRON_DAYS"];
		$_SESSION["WIZARD"]["CRON_HOURS"]=$_GET["CRON_HOURS"];
		$_SESSION["WIZARD"]["CRON_MIN"]=$_GET["CRON_MIN"];
		$_SESSION["WIZARD"]["CRON_CONTAINER"]=$_GET["CRON_CONTAINER"];
		
		
		exit;
		}		
		
	
	if(isset($_GET["WIZARD_CANCEL"])){WIZARD_CANCEL();exit;}
	if(isset($_GET["BACKUP_COMPILE"])){BACKUP_COMPILE();exit;}
	
	if(isset($_GET["SMTP_DOM"])){
		$_SESSION["WIZARD"]["SMTP_DOM"]=$_GET["SMTP_DOM"];
		$_SESSION["WIZARD"]["MAILBOX_IP"]=$_GET["MAILBOX_IP"];
		exit;
	}
	
	if(isset($_GET["SMTP_NET"])){$_SESSION["WIZARD"]["SMTP_NET"]=$_GET["SMTP_NET"];exit;}
	if(isset($_GET["popup-finish"])){popup_finish();exit;}
	if(isset($_GET["COMPILE"])){COMPILE();exit;}
	
js();	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{WIZARD_BACKUP}");
	$WIZARD_CONFIGURE_RESOURCE=$tpl->_ENGINE_parse_body("{WIZARD_BACKUP_CHOOSE_STORAGE}");
	$WIZARD_COMPILE=$tpl->_ENGINE_parse_body("{WIZARD_COMPILE}");
	$WIZARD_CONFIGURE_SCHEDULE=$tpl->_ENGINE_parse_body("{WIZARD_CONFIGURE_SCHEDULE}");
	$WIZARD_FINISH=$tpl->_ENGINE_parse_body("{WIZARD_FINISH}");
	
	$html="
	
		function WizardBackupLoad(){YahooWin(650,'$page?popup=yes','$title');}
		function WizardRessourceShow(){YahooWin(650,'$page?popup-ressource=yes','$WIZARD_CONFIGURE_RESOURCE');}
		function WizardScheduleShow(){YahooWin(650,'$page?popup-schedule=yes','$WIZARD_CONFIGURE_SCHEDULE');}
		function WizardFinish(){YahooWin(650,'$page?popup-finish=yes','$WIZARD_FINISH');}
		

		
		function CancelBackupWizard(){
			YahooWinHide();
			var XHR = new XHRConnection();
			XHR.appendData('WIZARD_CANCEL','yes');
			XHR.sendAndLoad('$page', 'GET');			
		}
		
	var x_WizardRessource= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		WizardRessourceShow();
	 }	
	 
	var x_WizardUSBSaveRessource= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		WizardScheduleShow();
	 }	

	var x_WizardBackupScheduleSave= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		WizardFinish();
	 }

	 var x_WizardBackupCompile= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){
			alert(tempvalue);
			return;
			}
		WizardBackupLoad();
	 }



function CloseTimeOut(){
		Loadjs('domains.manage.org.index.php?js=yes&ou='+document.getElementById('ou').value);
	 	YahooWinHide();
	 	
	}

	function WizardRessource(){
			var XHR = new XHRConnection();
			var storage=document.getElementById('storage').value;
			if(storage.length>1){
				XHR.appendData('W_RESOURCE',storage);
				XHR.sendAndLoad('$page', 'GET',x_WizardRessource);
			}
		}
		
	function WizardUSBSaveRessource(){
		var XHR = new XHRConnection();
		var UUID=document.getElementById('UUID').value;
		if(UUID.length>1){
				XHR.appendData('W_UUID',UUID);
				XHR.sendAndLoad('$page', 'GET',x_WizardUSBSaveRessource);
			}
	}
	
	function WizardSMBSaveRessource(){
		var XHR = new XHRConnection();
		var W_SMB_SERVER=document.getElementById('W_SMB_SERVER').value;
		if(W_SMB_SERVER.length>1){
				XHR.appendData('W_SMB_SERVER',W_SMB_SERVER);
				XHR.appendData('W_SMB_USERNAME',document.getElementById('W_SMB_USERNAME').value);
				XHR.appendData('W_SMB_PASSWORD',document.getElementById('W_SMB_PASSWORD').value);
				XHR.appendData('W_SMB_SHAREDDIR',document.getElementById('W_SMB_SHAREDDIR').value);
				XHR.sendAndLoad('$page', 'GET',x_WizardUSBSaveRessource);
			}
	}	

	function WizardBackupScheduleSave(){
		var XHR = new XHRConnection();
		var CRON_DAYS=document.getElementById('CRON_DAYS').value;
		if(CRON_DAYS.length>0){
				XHR.appendData('CRON_DAYS',CRON_DAYS);
				XHR.appendData('CRON_HOURS',document.getElementById('CRON_HOURS').value);
				XHR.appendData('CRON_MIN',document.getElementById('CRON_MIN').value);
				XHR.appendData('CRON_CONTAINER',document.getElementById('CRON_CONTAINER').value);
				
				
				XHR.sendAndLoad('$page', 'GET',x_WizardBackupScheduleSave);
			}
	}

	
	function WizardBackupCompile(){
		var XHR = new XHRConnection();
		XHR.appendData('BACKUP_COMPILE','yes');
		XHR.sendAndLoad('$page', 'GET',x_WizardBackupCompile);
	}
	
	

		  
	
	
	WizardBackupLoad();";
	
	echo $html;
}	


function popup_finish(){
	$html="
		<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/backup-128-bg.png'></td>
		<td valign='top'>
		<div style='color:#005447;font-size:13px'>{WIZARD_BACKUP_CHOOSE_STORAGE}: {$_SESSION["WIZARD"]["W_RESOURCE"]}</div>";
		
	switch ($_SESSION["WIZARD"]["W_RESOURCE"]) {
		case "usb":$html=$html."<div style='color:#005447;font-size:13px'>{usb_external_drive}: {$_SESSION["WIZARD"]["W_UUID"]}</div>";break;
		case "smb":$html=$html."<div style='color:#005447;font-size:13px'>{remote_smb_server}: \\\\{$_SESSION["WIZARD"]["W_SMB_SERVER"]}\\{$_SESSION["WIZARD"]["W_SMB_SHAREDDIR"]}</div>";break;
		case "rsync":$html=$html."<div style='color:#005447;font-size:13px'>{remote_smb_server}: rsync://{$_SESSION["WIZARD"]["W_SMB_SERVER"]}/{$_SESSION["WIZARD"]["W_SMB_SHAREDDIR"]}</div>";break;
		default:
			;
		break;
	}

	$cron=new cron_macros();
	$html=$html."<div style='color:#005447;font-size:13px'>{run_every}:{$cron->cron_days[$_SESSION["WIZARD"]["CRON_DAYS"]]} {day}; {time} {$cron->cron_hours[$_SESSION["WIZARD"]["CRON_HOURS"]]}:{$cron->cron_mins[$_SESSION["WIZARD"]["CRON_MIN"]]}</div>";
	$html=$html."<hr>
<H3 style='color:#005447;font-size:16px'>{wizardCompileButton}</H3>
	<p style='font-size:14px'>{wizardCompileButton_text}</p>
	<hr>
		<center>
			". button("{wizardCompileButton}","WizardBackupCompile()")."
		</center>
<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{back}","WizardScheduleShow()")."</td>
			<td width=50% align='right'>". button("{wizardCompileButton}","WizardBackupCompile()")."</td>
		</tr>
	</table>";
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"wizard.kaspersky.appliance.php");	
}	
	

function popup_schedule(){
	$html="
		<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/backup-128-bg.png'></td>
		<td valign='top'>
		<div style='color:#005447;font-size:13px'>{WIZARD_BACKUP_CHOOSE_STORAGE}: {$_SESSION["WIZARD"]["W_RESOURCE"]}</div>";
		
	switch ($_SESSION["WIZARD"]["W_RESOURCE"]) {
		case "usb":$html=$html."<div style='color:#005447;font-size:13px'>{usb_external_drive}: {$_SESSION["WIZARD"]["W_UUID"]}</div>";break;
		case "smb":$html=$html."<div style='color:#005447;font-size:13px'>{remote_smb_server}: \\\\{$_SESSION["WIZARD"]["W_SMB_SERVER"]}\\{$_SESSION["WIZARD"]["W_SMB_SHAREDDIR"]}</div>";break;
		case "rsync":$html=$html."<div style='color:#005447;font-size:13px'>{remote_smb_server}: rsync://{$_SESSION["WIZARD"]["W_SMB_SERVER"]}/{$_SESSION["WIZARD"]["W_SMB_SHAREDDIR"]}</div>";break;
		default:
			;
		break;
	}		


	$cron=new cron_macros();
$days=Field_array_Hash($cron->cron_days,"CRON_DAYS",$_SESSION["WIZARD"]["CRON_DAYS"],null,null,0,"font-size:14px;padding:5px;");	
$hours=Field_array_Hash($cron->cron_hours,"CRON_HOURS",$_SESSION["WIZARD"]["CRON_HOURS"],null,null,0,"font-size:14px;padding:5px;");
$mins=Field_array_Hash($cron->cron_mins,"CRON_MIN",$_SESSION["WIZARD"]["CRON_MIN"],null,null,0,"font-size:14px;padding:5px;");

$container=Field_array_Hash(array("daily"=>"{daily}","weekly"=>"{weekly}"),"CRON_CONTAINER",$_SESSION["WIZARD"]["CRON_CONTAINER"],null,null,0,"font-size:14px;padding:5px;");

$html=$html."<hr>
<H3 style='color:#005447;font-size:16px'>{WIZARD_CONFIGURE_SCHEDULE}</H3>
	<p style='font-size:14px'>{WIZARD_CONFIGURE_SCHEDULE_EXPLAIN}</p>
<table style='width:100%'>
		<tr>
			<td style='font-size:13px' align='right'>{run_every}:</td>
			<td>$days</td>
		</tr>
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr>
			<td style='font-size:13px' align='right'>{time}:</td>
			<td nowrap>$hours:$mins</td>
		</tr>	
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr><td colspan=2><p style='font-size:14px'>{WIZARD_CONFIGURE_CONTAINER_EXPLAIN}</p></td></tr>
		<tr>
			<td style='font-size:13px' align='right'>{container}:</td>
			<td nowrap>$container</td>
		</tr>	
		
		
</table>	

<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{back}","WizardRessourceShow()")."</td>
			<td width=50% align='right'>". button("{next}","WizardBackupScheduleSave()")."</td>
		</tr>
	</table>	
	";
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function popup_resource(){
	
	$html="
		<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/backup-128-bg.png'></td>
		<td valign='top'>
	<div style='color:#005447;font-size:13px'>{WIZARD_BACKUP_CHOOSE_STORAGE}: &laquo;{$_SESSION["WIZARD"]["W_RESOURCE"]}&raquo;</div>
	<hr>";
	
	
	switch ($_SESSION["WIZARD"]["W_RESOURCE"]) {
		case "usb":$html=$html.popup_resource_usb();break;
		case "smb":$html=$html.popup_resource_smb();break;
		case "rsync":$html=$html.popup_resource_smb();break;
		default:
			;
		break;
	}
	
	
	
	$html=$html."</td></tr></table>	";
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function popup_resource_usb(){
	
	$usb=new usb();
	$hash=$usb->HASH_UUID_LIST();
	$hash[null]='{select}';
	
	$select=Field_array_Hash($hash,"UUID",$_SESSION["WIZARD"]["W_RESOURCE"],null,null,0,"font-size:14px;padding:5px;");
	$html="
	<H3 style='color:#005447;font-size:16px'>{usb_external_drive}</H3>
	<p style='font-size:14px'>{WIZARD_BACKUP_USB_STORAGE_EXPLAIN}</p>
	$select
	<div style='text-align:right;width:100%'>".button("{refresh}","WizardRessourceShow()")."</center>
<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{back}","WizardBackupLoad()")."</td>
			<td width=50% align='right'>". button("{next}","WizardUSBSaveRessource()")."</td>
		</tr>
	</table>	
	";
	return $html;
}
function popup_resource_smb(){
	
	
	$html="
	<H3 style='color:#005447;font-size:16px'>{remote_smb_server}</H3>
	<p style='font-size:14px'>{WIZARD_BACKUP_SMB_STORAGE_EXPLAIN}</p>
	<table style='width:100%'>
		<tr>
			<td style='font-size:13px' align='right'>{servername}:</td>
			<td>". Field_text("W_SMB_SERVER",$_SESSION["WIZARD"]["W_SMB_SERVER"],"font-size:13px;padding:5px")."</td>
		</tr>	
		<tr>
			<td style='font-size:13px' align='right'>{username}:</td>
			<td>". Field_text("W_SMB_USERNAME",$_SESSION["WIZARD"]["W_SMB_USERNAME"],"font-size:13px;padding:5px")."</td>
		</tr>
		<tr>
			<td style='font-size:13px' align='right'>{password}:</td>
			<td>". Field_password("W_SMB_PASSWORD",$_SESSION["WIZARD"]["W_SMB_PASSWORD"],"font-size:13px;padding:5px")."</td>
		</tr>	
		<tr>
			<td style='font-size:13px' align='right'>{shared_folder}:</td>
			<td>". Field_text("W_SMB_SHAREDDIR",$_SESSION["WIZARD"]["W_SMB_SHAREDDIR"],"font-size:13px;padding:5px")."</td>
		</tr>	
	</table>			
		
<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{back}","WizardBackupLoad()")."</td>
			<td width=50% align='right'>". button("{next}","WizardSMBSaveRessource()")."</td>
		</tr>
	</table>	
	";
	return $html;
}


function popup(){
	
	$sql="SELECT COUNT(*) as tcount FROM backup_schedules";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["tcount"]>0){
		$intro="<hr><div id='wizard-backup-intro'>". texttooltip("[{$ligne["tcount"]}] {WIZARD_BACKUP_TASKS_ALREADY_SCHEDULED}",
		"[{$ligne["tcount"]}] {WIZARD_BACKUP_TASKS_ALREADY_SCHEDULED}",
		"Loadjs('backup.tasks.php')",null,0,"font-size:13px;color:#C23302")."</div>";
		
	}
	
	
	
	$storages["usb"]="{usb_external_drive}";
	$storages["smb"]="{remote_smb_server}";
	$storages["rsync"]="{remote_rsync_server}";
	
	$select=Field_array_Hash($storages,"storage",null,null,null,0,"font-size:14px;padding:5px;");
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/backup-128-bg.png'>$intro</td>
		<td valign='top'>
	<H1>{WIZARD_BACKUP}</H1>
	<p style='font-size:14px'>{WIZARD_BACKUP_EXPLAIN}</p>
	
	<H3 style='color:#005447;font-size:16px'>{WIZARD_BACKUP_CHOOSE_STORAGE}</H3>
	<p style='font-size:14px'>{WIZARD_BACKUP_CHOOSE_STORAGE_EXPLAIN}</p>
	$select
	
	<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{cancel}","CancelBackupWizard()")."</td>
			<td width=50% align='right'>". button("{next}","WizardRessource()")."</td>
		</tr>
	</table>
	</td></tr></table>	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function BACKUP_COMPILE(){
	$backup=new backup_protocols();
	switch ($_SESSION["WIZARD"]["W_RESOURCE"]) {
		case "usb":$pattern="usb://{$_SESSION["WIZARD"]["W_UUID"]}";break;
		case "smb":$pattern=$backup->build_smb_protocol($_SESSION["WIZARD"]["W_SMB_SERVER"],
		$_SESSION["WIZARD"]["W_SMB_SHAREDDIR"],
		$_SESSION["WIZARD"]["W_SMB_USERNAME"],
		$_SESSION["WIZARD"]["W_SMB_PASSWORD"]);break;

		case "rsync":$pattern=$backup->build_rsync_protocol($_SESSION["WIZARD"]["W_SMB_SERVER"],
		$_SESSION["WIZARD"]["W_SMB_SHAREDDIR"],
		$_SESSION["WIZARD"]["W_SMB_USERNAME"],
		$_SESSION["WIZARD"]["W_SMB_PASSWORD"]);break;
		
		
		
		default:;break;
	}
	$cron=new cron_macros();
	
	$ressources_array[0]="all";
	$ressources_array["OPTIONS"]["STOP_IMAP"]=0;
	
	
	$schedule=$cron->cron_compile_eachday($_SESSION["WIZARD"]["CRON_DAYS"],$_SESSION["WIZARD"]["CRON_HOURS"],$_SESSION["WIZARD"]["CRON_MIN"]);
	$datasbackup=base64_encode(serialize($ressources_array));
	$resource_type=$_SESSION["WIZARD"]["W_RESOURCE"];
	$CRON_CONTAINER=$_SESSION["WIZARD"]["CRON_CONTAINER"];
	$md5=md5($schedule.$pattern);
	
	$q=new mysql();
	$sql="INSERT INTO  backup_schedules(`zMD5`,`resource_type`,`pattern`,`schedule`,`datasbackup`,`container`)
	VALUES('$md5','$resource_type','$pattern','$schedule','$datasbackup','$CRON_CONTAINER')";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
		return ;
	}
	$sock=new sockets();
	$sock->SET_INFO("WizardBackupSeen",1);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?backup-build-cron=yes");
	
}

function WIZARD_CANCEL(){
	$sock=new sockets();
	$sock->SET_INFO("WizardBackupSeen",1);
	
}





?>