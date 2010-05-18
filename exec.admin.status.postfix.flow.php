<?php
if(!function_exists("posix_getuid")){echo "posix_getuid !! not exists\n";}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.status.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/ressources/class.artica.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");



if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}
if(systemMaxOverloaded()){
	writelogs("This system is too many overloaded, die()",__FUNCTION__,__FILE__,__LINE__);
	die();
}
cpulimit();



if($GLOBALS["VERBOSE"]){
	writelogs(basename(__FILE__).":Executed",basename(__FILE__),__FILE__,__LINE__);
}

if($argv[1]=="--setup-center"){setup_center();die();}
if($argv[1]=="--services"){services();die();}
if($argv[1]=="--mysql"){test_mysql();die();}
if($argv[1]=="--monit"){test_monit();die();}

if($argv[1]=="--roundcube"){
	$_GET["front_page_notify"]=front_page_notify();
	status_roundcube_version();
	die();
}



if($argv[1]=='--force'){$_GET["FORCE"]=true;}
if(!$_GET["FORCE"]){
	if(system_is_overloaded()){die();}
	if(!Build_pid_func(__FILE__,"MAIN")){
		writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
		die();
	}
}
	
if($argv[1]=='--setup'){
	setup_center();
	error_log("setup_center() die in ".__FILE__);
	die();	
}
	
if(!$_GET["FORCE"]){
		$sock=new sockets();
		$PoolCoverPageSchedule=intval($sock->GET_INFO('PoolCoverPageSchedule'));
		if($PoolCoverPageSchedule<1){$PoolCoverPageSchedule=10;}
		$timef=file_get_time_min("/etc/artica-postfix/croned.2/".md5(__FILE__));
		if($timef<$PoolCoverPageSchedule){die();}
	}
	
@unlink("/etc/artica-postfix/croned.2/".md5(__FILE__));
@file_put_contents("/etc/artica-postfix/croned.2/".md5(__FILE__),date('Y-m-d H:i:s'));

$os=new os_system();
$_GET["CURRENT_MEMORY"]=RoundedLightGrey($os->html_Memory_usage())."<br>";
@file_put_contents('/usr/share/artica-postfix/ressources/logs/status.memory.html',$_GET["CURRENT_MEMORY"]);
@chmod('/usr/share/artica-postfix/ressources/logs/status.memory.html',0755);
BuildingExecStatus("Build deamons status...",10);
daemons_status();
events("daemons_status(); OK");
BuildingExecStatus("Current time...",20);
get_current_time();
events(__LINE__.") get_current_time(); OK");
events("status_right -> START;");
BuildingExecStatus("Right pan...",30);

status_right();
events("status_right(); OK");
BuildingExecStatus("Right Postfix pan...",40);

$status=new status();

BuildingExecStatus("Postfix status...",50);
status_right_postfix($status);
events("status_right_postfix(); OK");


BuildingExecStatus("New versions...",55);
BuildJgrowlVersions($status);
events("BuildVersions(); OK");

BuildingExecStatus("Setup Center...",60);
setup_center();
events("setup_center(); OK");
BuildingExecStatus("Samba status...",80);
samba_status();
events("samba_status(); OK");
BuildingExecStatus("Done...",100);

@unlink("/etc/artica-postfix/croned.2/".md5(__FILE__));
$restults=file_put_contents("/etc/artica-postfix/croned.2/".md5(__FILE__),'#');
events(basename(__FILE__).":: stamp $timef ($restults) done...");

function services(){
	events(basename(__FILE__).":: running daemons_status()");
	daemons_status();
	events(basename(__FILE__).":: running daemons_status done...");
}


function daemons_status(){
	$users=new usersMenus();
	$artica=new artica_general();
	$tpl=new templates();
	$soc=new sockets();
	$ONLY_SAMBA=false;
	
	if(!$users->SQUID_INSTALLED){
		if(!$users->POSTFIX_INSTALLED){
			if($users->SAMBA_INSTALLED){
				$ONLY_SAMBA=true;
			}
		}
	}
	
	if($users->collectd_installed){
		if($artica->EnableCollectdDaemon==1){
			$collectd=Paragraphe("64-charts.png","{collectd_statistics}","{collectd_statistics_text}","javascript:YahooWin(790,'collectd.index.php?PopUp=yes')","services_status_text",300,76);
		}
	}
	
	$interface=new networking();
	if(is_array($interface->array_TCP)){
		while (list ($num, $val) = each ($interface->array_TCP) ){
			if($val==null){continue;}
			$i++;
			$iptext=$iptext."<div style='font-size:11px'><strong>{nic}:$num:<a href='#' OnClick=\"javascript:Loadjs('system.nic.config.php?js=yes')\">$val</a></strong></div>";
			if($i>2){break;}
		}
	}
	
	
	
	if($users->POSTFIX_INSTALLED){
		$monthly_stats=Paragraphe("statistics-network-64.png","{monthly_statistics}","{monthly_statistics_text}","javascript:Loadjs('smtp.daily.statistics.php')","{monthly_statistics_text}",300,76,1);
	}
	
	$services="
	$interfaces_text
	$collectd
	$curlinit
	";
	
	
	
	$sock=new sockets();	
	$ini=new Bs_IniHandler();
	$ini->loadFile("/etc/artica-postfix/smtpnotif.conf");
	
	if($sock->GET_INFO("DisableWarnNotif")<>1){
		if(trim($ini->_params["SMTP"]["enabled"]==null)){
		$js="javascript:Loadjs('artica.settings.php?ajax-notif=yes')";
		$services=Paragraphe('danger64.png',"{smtp_notification_not_saved}","{smtp_notification_not_saved_text}","$js","{smtp_notification_not_saved}",300,80);
		
	}}
	
	if($sock->GET_INFO("WizardBackupSeen")<>1){
		$js="javascript:Loadjs('wizard.backup-all.php')";
		$nobackup=Paragraphe('danger64.png',"{BACKUP_WARNING_NOT_CONFIGURED}","{BACKUP_WARNING_NOT_CONFIGURED_TEXT}","$js","{BACKUP_WARNING_NOT_CONFIGURED_TEXT}",300,80);
	}
	
	
	
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
			if(!$main->CheckMyNetwork()){		
				$services="
				<div id='servinfos'>
					".	Paragraphe('pluswarning64.png','{postfix_mynet_not_conf}','{postfix_mynet_not_conf_text}',"javascript:Loadjs('postfix.network.php?ajax=yes');","{postfix_mynet_not_conf}",300,73)."
				</div>";
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


		
		
	if($ONLY_SAMBA){
			$computers=Paragraphe("64-win-nic-browse.png",'{browse_computers}','{browse_computers_text}',"javascript:Loadjs('computer-browse.php');","{browse_computers_text}",300,76,1);
			$samba=Paragraphe("explorer-64.png",'{explorer}','{SHARE_FOLDER_TEXT}',"javascript:Loadjs('tree.php');","{SHARE_FOLDER_TEXT}",300,76,1);
		}

		
	
	if(!is_file("/etc/artica-postfix/KASPER_MAIL_APP")){
		if($users->ZABBIX_INSTALLED){
		$EnableZabbixServer=$sock->GET_INFO("EnableZabbixServer");
		if($EnableZabbixServer==null){$EnableZabbixServer=1;}
		if($EnableZabbixServer==1){
			$zabbix=Paragraphe("zabbix_med.gif",'{APP_ZABIX_SERVER}','{APP_ZABIX_SERVER_TEXT}',"javascript:Loadjs('zabbix.php')","{APP_ZABIX_SERVER_TEXT}",300,76,1);
			}
		}
	}
	
	
	if($sock->GET_INFO("DisableFrontEndArticaEvents")<>1){
		$q=new mysql();
		$sql="SELECT COUNT(ID) as tcount FROM events";
		$events_sql=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
		
		if($events_sql["tcount"]>0){
			$events_paragraphe=Paragraphe("events-64.png",'{artica_events}',"{artica_events_text}",
			"javascript:Loadjs('artica.events.php')","{$events_sql["tcount"]} {events}",300,76,1);
		}
	}

	$newversion=null;

	$final="
	$services
	$nobackup
	$events_paragraphe
	$all
	$zabbix
	$disks
	$monthly_stats
	$pureftp_error
	$newversion
	$roundcube
	$samba
	$computers
	$check_apt";

events(__FUNCTION__."/usr/share/artica-postfix/ressources/logs/status.global.html ok");	
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
	events(__FUNCTION__."() done..");		
}


function status_right(){
	
	$postfix=$_GET["CURRENT_MEMORY"].BuildStatusRight();
	@file_put_contents('/usr/share/artica-postfix/ressources/logs/status.right.1.html',"$postfix$switch");
	@chmod('/usr/share/artica-postfix/ressources/logs/status.right.1.html',777);		
	events(__FUNCTION__."() done..");
}


function BuildStatusRight(){
	$users=new usersMenus();
	$sock=new sockets();
	$tpl=new templates();
	$SAMBA_INSTALLED=0;
	$SQUID_INSTALLED=0;
	$POSTFIX_INSTALLED=0;
	
	if($users->POSTFIX_INSTALLED){$POSTFIX_INSTALLED=1;}

	if($users->SQUID_INSTALLED){
		$SQUID_INSTALLED=1;
		$SQUID_INSTALLED=$sock->GET_INFO("SQUIDEnable");
		if($SQUID_INSTALLED==null){$SQUID_INSTALLED=1;}
	}
	
	if($users->SAMBA_INSTALLED){
		$SAMBA_INSTALLED=1;
		$SAMBA_INSTALLED=$sock->GET_INFO("SambaEnabled");
		if($SAMBA_INSTALLED==null){$SAMBA_INSTALLED=1;}
	}
	
	events("POSTFIX_INSTALLED=$POSTFIX_INSTALLED,SQUID_INSTALLED=$SQUID_INSTALLED,SAMBA_INSTALLED=$SAMBA_INSTALLED");
	writelogs("POSTFIX_INSTALLED=$POSTFIX_INSTALLED,SQUID_INSTALLED=$SQUID_INSTALLED,SAMBA_INSTALLED=$SAMBA_INSTALLED",__FUNCTION__,__FILE__,__LINE__);

	if($POSTFIX_INSTALLED==0){
		if($SQUID_INSTALLED==1){return StatusSquid();}
		if($SAMBA_INSTALLED==1){return StatusSamba();}
	}

	
	return @file_get_contents("/usr/share/artica-postfix/ressources/logs/status.right.postfix.html");
	
	
}


function StatusSamba(){
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getFrameWork("cmd.php?samba-status=yes"));
	$status_smbd=DAEMON_STATUS_ROUND("SAMBA_SMBD",$ini);
	$status_nmbd=DAEMON_STATUS_ROUND("SAMBA_NMBD",$ini);
	$html="
		<table style='width:100%'>
			<tr>
				<td valign='top'>
					<table style='width:100%'>
						<tr>
							<td valign='top' width=1%>" . imgtootltip('64-samba.png','{APP_SAMBA}',"javascript:Loadjs('fileshares.index.php?js=yes')")."</td>
							<td valign='top' ><br>$status_smbd<br>$status_nmbd</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>";	
	
	return $html;
}
function StatusSquid(){
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getFrameWork("cmd.php?squid-status=yes"));
	$status_squid=DAEMON_STATUS_ROUND("SQUID",$ini);
	$status_dansguardian=DAEMON_STATUS_ROUND("DANSGUARDIAN",$ini);
	$html="
		<table style='width:100%'>
			<tr>
				<td valign='top'>
					<table style='width:100%'>
						<tr>
							<td valign='top' width=1%>" . imgtootltip('64-samba.png','{APP_SQUID}',"javascript:Loadjs('squid.newbee.php?yes=yes')")."</td>
							<td valign='top' ><br>$status_squid<br>$status_dansguardian</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>";	
	writelogs("OK",__FUNCTION__,__FILE__,__LINE__);
	return $html;
}



function BuildJgrowlVersions($status){
$status->BuildNewersions();	
}

function status_right_postfix($status){
$html=RoundedLightGreen("<div id='servinfos' style='background-image:url(img/bg-status-postfix.png);background-position:-50px -1px'>".$status->Postfix_satus()."</div>");	
file_put_contents('/usr/share/artica-postfix/ressources/logs/status.right.postfix.html',"$html");
system('/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/status.right.postfix.html');		
$html=$status->Postfix_satus();	
file_put_contents('/usr/share/artica-postfix/ressources/logs/status.right.postfix-nodiv.html',"$html");
system('/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/status.right.postfix-nodiv.html');	
events(__FUNCTION__."() done..");	
}





	




	



function setup_center(){
	if(!$GLOBALS["FORCE"]){
		if(!Build_pid_func(__FILE__,__FUNCTION__)){return false;}
		$time_file="/etc/artica-postfix/croned.2/".md5(__FILE__.__FUNCTION__);
		$tt=file_time_sec($time_file);
		if($tt<30){
			events(__FUNCTION__." $tt seconds, please wait 30s");
			return null;
		}
	}

	
		include_once(dirname(__FILE__).'/setup.index.php');
		
	
		error_log("Starting ". __FUNCTION__." in ".__FILE__);
	
		
		BuildingExecStatus("Setup center:: statistics...",52);
		stat_packages();
		BuildingExecStatus("Setup center:: SMTP...",54);
		smtp_packages();
		BuildingExecStatus("Setup center:: WEB...",56);
		web_packages();
		BuildingExecStatus("Setup center:: Proxy...",58);
		proxy_packages();
		BuildingExecStatus("Setup center:: Samba...",60);
		samba_packages();
		BuildingExecStatus("Setup center:: System...",62);
		system_packages();
		BuildingExecStatus("Setup center:: Xapian...",64);
		xapian_packages();
		BuildingExecStatus("Setup center:: done...",68);
		events(__FUNCTION__."() done..");	
	
}

function events($text){
		$d=new debuglogs();
		$logFile="/var/log/artica-postfix/artica-status.debug";
		$d->events(basename(__FILE__)." $text",$logFile);
		}
		
function samba_status(){
	$ini=new Bs_IniHandler();
	$user=new usersMenus();
	$sock=new sockets();
	$tpl=new templates();
	$ini->loadString($sock->getfile('daemons_status',$_GET["hostname"]));	
	if($user->SAMBA_INSTALLED){
		$samba_status=DAEMON_STATUS_ROUND("SAMBA_SMBD",$ini);
		$nmmbd=DAEMON_STATUS_ROUND("SAMBA_NMBD",$ini);
		$winbind=DAEMON_STATUS_ROUND("SAMBA_WINBIND",$ini);
		$kav_status=DAEMON_STATUS_ROUND("KAV4SAMBA",$ini);
		$SAMBA_SCANNEDONLY=DAEMON_STATUS_ROUND("SAMBA_SCANNEDONLY",$ini);
	}
	if($user->PUREFTP_INSTALLED){
		$pureftpd_status=DAEMON_STATUS_ROUND("PUREFTPD",$ini);
	}
	
	$results="$samba_status<br>$nmmbd<br>$winbind<br>$SAMBA_SCANNEDONLY<br>$kav_status<br>$pureftpd_status";
	
file_put_contents('/usr/share/artica-postfix/ressources/logs/status.samba.html',$results);		
system('/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/status.samba.html');
events(__FUNCTION__."() done..");	
}


function interface_error(){
	
	$ini=new Bs_IniHandler();
	if(!is_file("/usr/share/artica-postfix/ressources/logs/interface.events")){return null;}
	$ini->loadFile("/usr/share/artica-postfix/ressources/logs/interface.events");
	
	while (list ($num, $ligne) = each ($ini->_params) ){
		if($ini->_params[$num]["error"]==null){continue;}
		$html=$html . Paragraphe("warning64.png","{error} {$num}",$ini->_params[$num]["error"],"javascript:StopInterfaceError('$num')");
		
	}
	events(__FUNCTION__."() done..");	
	return $html;
	
}

function test_monit(){
	$unix=new unix();
	$unix->monit_array();
	die();
}





?>