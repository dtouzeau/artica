<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.status.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if($argv[1]=='--force'){$_GET["FORCE_EXECUTION"]=true;}
if($argv[1]=='--services'){services();die();}
if($argv[1]=='--versions'){versions();die();}

if(!Build_pid_func(__FILE__,"MAIN")){
	events("Already executed.. aborting the process");
	error_log(basename(__FILE__). " Already executed.. aborting the process");
	BuildingExecRightStatus("Already executed.. aborting the process",100);
	die();
}
if(systemMaxOverloaded()){
	writelogs("This system is too many overloaded, die()",__FUNCTION__,__FILE__,__LINE__);
	die();
}

	if(!$_GET["FORCE_EXECUTION"]){
		if(system_is_overloaded()){events("die, overloaded");die();}
		$sock=new sockets();
		$PoolCoverPageSchedule=intval($sock->GET_INFO('PoolCoverPageSchedule'));
		if($PoolCoverPageSchedule<1){$PoolCoverPageSchedule=20;}		
		if(file_get_time_min("/etc/artica-postfix/croned.2/".md5(__FILE__))<30){
			events("die, {$PoolCoverPageSchedule}mn minimal");die();
		}
	}
	
	@unlink("/etc/artica-postfix/croned.2/".md5(__FILE__));
	@file_put_contents("/etc/artica-postfix/croned.2/".md5(__FILE__),date('Y-m-d H:i:s'));
	error_log(basename(__FILE__). " start_execution();");
	start_execution();
	
function services(){
postfix_status();
	
}
	
function start_execution(){
	$unix=new unix();
	BuildingExecRightStatus("Building postfix status...",20);
	postfix_status();
	BuildingExecRightStatus("Building postfix status...",55);
	events("postfix_status() OK");
	get_current_time();
	events("get_current_time() OK");
	BuildingExecRightStatus("Building postfix status...",90);
	BuildingExecRightStatus("done...",100);
	}

function postfix_status(){
	$user=new usersMenus();
	if(!$user->POSTFIX_INSTALLED){return null;}
	$user->LoadModulesEnabled();
	$q=new mysql();
	$fetchmail_count=0;
	if($user->fetchmail_installed){
		BuildingExecRightStatus("Building fetchmail statistics...",25);
		$sql="SELECT COUNT(ID) as tcount FROM `fetchmail_events` WHERE DATE_FORMAT(zDate,'%Y-%m-%d')=DATE_FORMAT( NOW( ) ,'%Y-%m-%d' )"; 
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));
		$fetchmail_count=$ligne["tcount"];
		
		$sql="SELECT SUM(size) as tcount FROM `fetchmail_events` WHERE DATE_FORMAT(zDate,'%Y-%m-%d')=DATE_FORMAT( NOW( ) ,'%Y-%m-%d' )"; 
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));
		$fetchmail_size=FormatBytes(($ligne["tcount"]/1024));
		events("fetchmail_count=$fetchmail_count, fetchmail_size=$fetchmail_size");
		
	}else{
		events('Fetchmail is not installed');
	}
	BuildingExecRightStatus("Building storage statistics...",30);
	$sql="SELECT COUNT( MessageID ) as tcount FROM storage WHERE DATE_FORMAT( zDate, '%Y-%m-%d' ) = DATE_FORMAT( NOW( ) , '%Y-%m-%d' ) ";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$backuped_mails=$ligne["tcount"];
	
	
	BuildingExecRightStatus("Building quarantine statistics...",35);
	$sql="SELECT COUNT( MessageID ) as tcount FROM quarantine WHERE DATE_FORMAT( zDate, '%Y-%m-%d' ) = DATE_FORMAT( NOW( ) , '%Y-%m-%d' ) ";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$quarantine_mails=$ligne["tcount"];
	
	
	BuildingExecRightStatus("Building messages number statistics...",40);
	$sql="SELECT COUNT(ID) as tcount FROM `smtp_logs` WHERE DATE_FORMAT( time_sended, '%Y-%m-%d' ) = DATE_FORMAT( NOW( ) , '%Y-%m-%d' )";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));
	$flow_mails=$ligne["tcount"];	
	
	
	if($user->cyrus_imapd_installed){
		BuildingExecRightStatus("Building imap/pop3 number statistics...",45);
		$sql="SELECT COUNT(ID) as tcount FROM `mbx_con` WHERE DATE_FORMAT( zDate, '%Y-%m-%d' ) = DATE_FORMAT( NOW( ) , '%Y-%m-%d' )";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));
		$mbx_con=$ligne["tcount"];	
	$mbx_con="<tr>
			<td class=legend nowrap>{mbx_con}:</td>
			<td style='font-size:12px;font-weight:bold'>$mbx_con {connections}</td>
		</tr>";
	}
	
	if($fetchmail_count>0){
		$fetchmail="<tr>
			<td class=legend nowrap>{fetchmail_recup}:</td>
			<td style='font-size:12px;font-weight:bold'>$fetchmail_count {emails} ($fetchmail_size)</td>
		</tr>";
		}
	
	$html="
	<H5>Postfix:&nbsp;{today}</h5>
	<table class=table_form>
		<tr>
			<td class=legend nowrap>{received_mails}:</td>
			<td style='font-size:12px;font-weight:bold'>$flow_mails</td>
		</tr>
		$mbx_con	
		$fetchmail
		<tr>
			<td class=legend nowrap>{backuped_mails}:</td>
			<td style='font-size:12px;font-weight:bold'>$backuped_mails</td>
		</tr>
		<tr>
			<td class=legend nowrap>{quarantine_mails}:</td>
			<td style='font-size:12px;font-weight:bold'>$quarantine_mails</td>
		</tr>
	</table>
						
	
	";
	if($user->AMAVIS_INSTALLED){
		if($user->EnableAmavisDaemon){
			BuildingExecRightStatus("Building Amavis statistics...",50);
			$ini=new Bs_IniHandler();
			$sock=new sockets();
			$ini->loadString($sock->getfile('amavisstatus'));
			$status_amavis=DAEMON_STATUS_ROUND("AMAVISD",$ini,null);
			$status_amavismilter=DAEMON_STATUS_ROUND("AMAVISD_MILTER",$ini,null);
			$status="<br>$status_amavis<br>$status_amavismilter";
		}
	}
	$html=RoundedLightGrey($html).$status."<br>";
	file_put_contents('/usr/share/artica-postfix/ressources/logs/status.postfix.flow.html',$html);
	system('/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/status.postfix.flow.html');
	BuildingExecRightStatus("Building done...",100);
}

function daemons_status(){
	$users=new usersMenus();
	$artica=new artica_general();
	$tpl=new templates();
	$ldap=new clladp();
	$sql=new mysql();
	$status=new status(1);
	$gd_info=gdinfos();
	
	if($ldap->ldapFailed==true){
		$ldap_error=$ldap->ErrorConnection()."<br>";
	}
	
	
	
	
	$all=$status->StatusFailed();
	
	
	if($users->collectd_installed){
		if($artica->EnableCollectdDaemon==1){
			$collectd=Paragraphe("64-charts.png","{collectd_statistics}","{collectd_statistics_text}","javascript:YahooWin(790,'collectd.index.php?PopUp=yes')","services_status_text",300,76);
		}
	}
	
	$interface=new networking();
	if(is_array($interface->array_TCP)){
		while (list ($num, $val) = each ($interface->array_TCP) ){
			$iptext=$iptext."<div style='font-size:11px'><strong>{nic}:$num:<a href='#' OnClick=\"javascript:Loadjs('system.nic.config.php?js=yes')\">$val</a></strong></div>";
		}
	}
	
	//$manage_services=Paragraphe("folder-tasks2-64.png","{services_status}","{services_status_text}$iptext","admin.index.services.status.php","services_status_text",300,76,1);
	
	$services="
	$collectd
	";
	
	
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->GET_INFO("SmtpNotificationConfig"));
	if(trim($ini->_params["SMTP"]["enabled"]==null)){
		$js="javascript:Loadjs('artica.settings.php?ajax-notif=yes')";
		$services=Paragraphe('danger64.png',"{smtp_notification_not_saved}","{smtp_notification_not_saved_text}","$js","smtp_notification_not_saved",300,80);
		
	}
	
	$sock=new sockets();
	$datas=trim($sock->getfile('aptcheck'));
	if(preg_match('#nb:([0-9]+)\s+#is',$datas,$re)){
		$services=Paragraphe('i64.png',"{upgrade_your_system}","{$re[1]}&nbsp;{packages_to_upgrade}","javascript:Loadjs('artica.repositories.php?show=update')",null,300,76);
		
	}
	
	
	
	if($users->POSTFIX_INSTALLED){
		if(!$users->POSTFIX_LDAP_COMPLIANCE){
			$services="
					<table style='width:100%'>
					<tr>
					<td valign='top' width=1%><img src='img/rouage_off.png'></td>
					<td valign='top'><a href='#' OnClick=\"javascript:YahooWin(600,'index.remoteinstall.php','Install');\">
					<H5>{error_postfix_ldap_compliance}</H5><p class=caption>{error_postfix_ldap_compliance_text}
					</A>
					</p></td>
					</tr>
					</table>";
					$services=RoundedLightYellow($services);	
		}else{
			$ok=true;
			$main=new main_cf();
			if(!is_array($main->array_mynetworks)){
				$ok=false;
			}else{
			while (list ($num, $ligne) = each ($main->array_mynetworks) ){
				if($ligne<>"127.0.0.1"){
					$rr[]=$ligne;
				}
			}
			
			if(count($rr)==0){
				$ok=false;
			}
			}
			
			if(!$ok){
				$services="<div id='servinfos'>".	Paragraphe('danger64.png','{postfix_mynet_not_conf}','{postfix_mynet_not_conf_text}',"postfix.network.php",null,300,73)."</div>";
			}
		}
	}
	
	
	if($users->BadMysqlPassword==1){
			$services="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src='img/superuser-64-bad.png'></td>
	<td valign='top'><H5>{error_mysql_user}</H5><p class=caption>{error_mysql_user_text}</p></td>
	</tr>
	</table>";
	$services=RoundedLightGrey($services,"artica.settings.php",1);	
	}
	
	
	//
	if(!$users->POSTFIX_INSTALLED){
		if($users->SAMBA_INSTALLED){
			$sock=new sockets();
			$SambaEnabled=$sock->GET_INFO("SambaEnabled");
			if($SambaEnabled==null){$SambaEnabled=1;}
			if($SambaEnabled==1){
				$samba=Paragraphe("64-share.png",'{SHARE_FOLDER}','{SHARE_FOLDER_TEXT}',"javascript:Loadjs('SambaBrowse.php');","SHARE_FOLDER_TEXT",300,76,1);
			}
		}
	}
	
	
		$newversion=null;
		if(is_file(dirname(__FILE)."/ressources/index.ini")){
			$ini=new Bs_IniHandler(dirname(__FILE)."/ressources/index.ini");
			$remote_version=$ini->_params["NEXT"]["artica"];
			$local_version=$users->ARTICA_VERSION;
			$remote_version_bin=str_replace('.','',$remote_version);
			$local_version_bin=str_replace('.','',$local_version);
			if($local_version<$remote_version){
				$newversion=Paragraphe('i64.png',"{upgrade_artica} $remote_version","{upgrade_artica_text}","javascript:Loadjs('artica.update.php?js=yes')",null,300,76);
			}
		}

	$final="
	$switch
	$ldap_error
	$all
	$pureftp_error
	$samba
	$newversion
	$check_apt";

	
file_put_contents('/usr/share/artica-postfix/ressources/logs/status.global.html',$final);
system('/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/status.global.html');	
	
}

function get_current_time(){
	$sock=new sockets();
	$time=$sock->getfile('GetCurrentBindate');
	$users=new usersMenus();
	$html1="<a href='#' OnClick=\"Loadjs('index.time.php?settings=yes')\">$time</a>";
	$html2="<a href='#'>$time</a>";
	file_put_contents('/usr/share/artica-postfix/ressources/logs/status.time.admin.html',$html1);
	file_put_contents('/usr/share/artica-postfix/ressources/logs/status.time.user.html',$html2);
	system('/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/status.time.admin.html');
	system('/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/status.time.user.html');		
}

function events($text){
	$d=new debuglogs();
	$logFile="/var/log/artica-postfix/artica-status.debug";
	$d->events(basename(__FILE__)." $text",$logFile);
}
function versions(){
	$stat=new status();
	$stat->BuildNewersions();
	
}




?>