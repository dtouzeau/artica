<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.status.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["debug"]=true;$GLOBALS["VERBOSE"]=true;}

if($GLOBALS["VERBOSE"]){writelogs("Start...","MAIN",__FILE__,__LINE__);}

if(systemMaxOverloaded()){
	writelogs("This system is too many overloaded, die()",__FUNCTION__,__FILE__,__LINE__);
	die();
}


if(GET_INFO_DAEMON("cpuLimitEnabled")==1){$GLOBALS["cpuLimitEnabled"]=true;}else{$GLOBALS["cpuLimitEnabled"]=false;}
$GLOBALS["OVERLOADED"]=system_is_overloaded();


if($GLOBALS["OVERLOADED"]){
	writelogs("This system is overloaded",__FUNCTION__,__FILE__,__LINE__);
	if($GLOBALS["cpuLimitEnabled"]){
		if(GET_INFO_DAEMON("cpulimit")>0){
			shell_exec("/usr/share/artica-postfix/bin/process1 --cpulimit");
		}
	}
}
$dirname=dirname(__FILE__);
$_GET["NICE"]=EXEC_NICE();
$_GET["PHP5"]=LOCATE_PHP5_BIN();
$users=new usersMenus();
$sock=new sockets();
$GLOBALS["SQUID_INSTALLED"]=$users->SQUID_INSTALLED;
$GLOBALS["POSTFIX_INSTALLED"]=$users->POSTFIX_INSTALLED;
$GLOBALS["SAMBA_INSTALLED"]=$users->SAMBA_INSTALLED;
$_GET["MIME_DEFANGINSTALLED"]=$users->MIMEDEFANG_INSTALLED;
$GLOBALS["DANSGUARDIAN_INSTALLED"]=$users->DANSGUARDIAN_INSTALLED;
$GLOBALS["KAS_INSTALLED"]=$users->kas_installed;
if($GLOBALS["VERBOSE"]){writelogs("DANSGUARDIAN_INSTALLED={$GLOBALS["DANSGUARDIAN_INSTALLED"]}","MAIN",__FILE__,__LINE__);}
$GLOBALS["EnableArticaWatchDog"]=GET_INFO_DAEMON("EnableArticaWatchDog");
if($GLOBALS["VERBOSE"]){if($GLOBALS["POSTFIX_INSTALLED"]){events("Postfix is installed...");}}
if($GLOBALS["VERBOSE"]){events("Nice=\"\", php5 {$_GET["PHP5"]}");}
$nohup=LOCATE_NOHUP()." ";
shell_exec("$nohup{$_GET["PHP5"]} $dirname/exec.parse-orders.php &");



if($argv[1]=='--mails-archives'){mailarchives();die();}
if($argv[1]=='--stats-console'){stats_console();die();}
if($argv[1]=='--group5'){group5();die();}
if($argv[1]=='--group10'){group10();die();}
if($argv[1]=='--group30s'){group30s();die();}
if($argv[1]=='--group10s'){group10s();die();}
if($argv[1]=='--group0'){group0();die();}
if($argv[1]=='--group2'){group2();die();}
if($argv[1]=='--group300'){group300();die();}
if($argv[1]=='--group120'){group120();die();}

events("Unable to understand ". implode(" ",$argv));

die();

function stats_console(){
	$array[]="exec.admin.smtp.flow.status.php";
	$array[]="exec.postfix-logger.php --postfix";
	$array[]="exec.postfix.iptables.php";
	$array[]="exec.last.100.mails.php";
	
	while (list ($index, $file) = each ($array) ){
		$cmd="{$_GET["PHP5"]} /usr/share/artica-postfix/$file";
		sys_THREAD_COMMAND_SET($cmd);
	}	
		
}

// sans vérifications, toutes les 5 minutes
function group5(){
	
	writelogs("DANSGUARDIAN_INSTALLED={$GLOBALS["DANSGUARDIAN_INSTALLED"]}",__FUNCTION__,__FILE__,__LINE__);
	
	$unix=new unix();
	
	$array["exec.dstat.top.php"]="exec.dstat.top.php";
	$array["exec.admin.status.postfix.flow.php"]="exec.admin.status.postfix.flow.php";
	$array["exec.admin.smtp.flow.status.php"]="exec.admin.smtp.flow.status.php";
	$array["exec.remote-install.php"]="exec.remote-install.php";
	$array["exec.parse.dar-xml.php"]="exec.parse.dar-xml.php";
	$array["exec.import-networks.php"]="exec.import-networks.php";
	$array["cron.notifs.php"]="cron.notifs.php";
	$array["exec.watchdog.php"]="exec.watchdog.php";
	

	
	if($GLOBALS["DANSGUARDIAN_INSTALLED"]){
		if(!is_file("/usr/share/artica-postfix/ressources/logs/dansguardian.patterns")){
			$array["exec.dansguardian.compile.php --patterns"]="exec.dansguardian.compile.php --patterns";
		}
	}

	if($GLOBALS["SAMBA_INSTALLED"]){
		$array["exec.xapian.index.php"]="exec.xapian.index.php";
	}
	
	
	if(is_file("/usr/sbin/glusterfsd")){
		$array["exec.gluster.php"]="exec.gluster.php --notify-server";
	}
	
	if($GLOBALS["EnableArticaWatchDog"]==1){
		$array2[]="artica-install --start-minimum-daemons";
	}
	
	if($GLOBALS["POSTFIX_INSTALLED"]){
		if($GLOBALS["KAS_INSTALLED"]){
			$array2[]="artica-update --kas3 &";
		}
		
	}
	
	if(!is_file($unix->find_program("cpulimit"))){@unlink("/etc/artica-postfix/cpulimit-installed");}
	
	if(!is_file("/etc/artica-postfix/cpulimit-installed")){
		$array2[]="artica-make APP_CPULIMIT";
	}
	
	$array2[]="artica-install --generate-status";
	
	if($GLOBALS["OVERLOADED"]){
		unset($array["exec.dstat.top.php"]);
		unset($array["exec.admin.status.postfix.flow.php"]);
		unset($array["exec.parse.dar-xml.php"]);
		unset($array["exec.import-networks.php"]);
		unset($array["exec.admin.smtp.flow.status.php"]);
	}
	
	
	while (list ($index, $file) = each ($array) ){
		$cmd="{$_GET["PHP5"]} /usr/share/artica-postfix/$file";
		sys_THREAD_COMMAND_SET($cmd);
	}
	
	if($GLOBALS["POSTFIX_INSTALLED"]){
		mailarchives();
	}
	
	if(is_array($array2)){
	while (list ($index, $file) = each ($array2) ){
		$cmd="/usr/share/artica-postfix/bin/$file";
		sys_THREAD_COMMAND_SET($cmd);
	}}		
	
	if($GLOBALS["VERBOSE"]){events(__FUNCTION__. ":: die...");}

}


//sans vérifications toutes les 10mn
function group10(){
	$EnablePhileSight=GET_INFO_DAEMON("EnablePhileSight");
	if($EnablePhileSight==null){$EnablePhileSight=1;}
	
	$array[]="exec.clean.logs.php --clean-tmp";
	
	if($GLOBALS["OVERLOADED"]){return;}
	
	
	if($EnablePhileSight==1){$array[]="exec.philesight.php --check";}
	$array[]="exec.kaspersky-update-logs.php";
	
	if($GLOBALS["SQUID_INSTALLED"]){
		$array[]="exec.dansguardian.last.php";
	}
	
	
	if($GLOBALS["EnableArticaWatchDog"]==1){
		$array2[]="artica-install --startall";
	}
	
	$array2[]="process1 --force";
	$array2[]="artica-install --check-virus-logs";
	$array2[]="artica-install --monit-check";
	$array2[]="process1 --cleanlogs";
	
	while (list ($index, $file) = each ($array) ){
		$cmd="{$_GET["PHP5"]} /usr/share/artica-postfix/$file";
		sys_THREAD_COMMAND_SET($cmd);
	}

	while (list ($index, $file) = each ($array2) ){
		$cmd="/usr/share/artica-postfix/bin/$file";
		sys_THREAD_COMMAND_SET($cmd);
	}	
	
	if($GLOBALS["VERBOSE"]){events(__FUNCTION__. ":: die...");}
	
	
}

//toutes les minutes
function Group0(){
	

	if($GLOBALS["POSTFIX_INSTALLED"]){
		$array[]="exec.whiteblack.php";
		$array[]="exec.postfix-logger.php";
	}
	
	
	$array2[]="process1";
	
if(is_array($array)){
	while (list ($index, $file) = each ($array) ){
		if(system_is_overloaded()){events(__FUNCTION__. ":: die, overloaded");die();}
		$cmd="{$_GET["PHP5"]} /usr/share/artica-postfix/$file";
		sys_THREAD_COMMAND_SET($cmd);
	}
}
if(is_array($array2)){
	while (list ($index, $file) = each ($array2) ){
		if(system_is_overloaded()){events(__FUNCTION__. ":: die, overloaded");die();}
		$cmd="/usr/share/artica-postfix/bin/$file";
		sys_THREAD_COMMAND_SET($cmd);
	}	
}
	
}
//toutes les 2 minutes
function Group2(){

	$array[]="exec.dhcpd-leases.php";
	$array[]="exec.mailbackup.php";

	if($GLOBALS["POSTFIX_INSTALLED"]){$array[]="exec.watchdog.postfix.queue.php";}
	if($GLOBALS["DANSGUARDIAN_INSTALLED"]){$array[]="exec.dansguardian.injector.php";}

	if(is_array($array)){
		while (list ($index, $file) = each ($array) ){
			$cmd="{$_GET["PHP5"]} /usr/share/artica-postfix/$file";
			sys_THREAD_COMMAND_SET($cmd);
		}	
	}
	
	
if(is_array($array2)){
	while (list ($index, $file) = each ($array2) ){
		$cmd="/usr/share/artica-postfix/bin/$file";
		sys_THREAD_COMMAND_SET($cmd);
	}	
}
	
}

function group10s(){


  if(is_array($array)){
		while (list ($index, $file) = each ($array) ){
			$cmd="{$_GET["PHP5"]} /usr/share/artica-postfix/$file";
			@sys_THREAD_COMMAND_SET($cmd);
		}	
	}
	
	if($GLOBALS["cpuLimitEnabled"]){$array2[]="process1 --cpulimit";}
	
if(is_array($array2)){
		while (list ($index, $file) = each ($array2) ){
			$cmd="/usr/share/artica-postfix/bin/$file";
			sys_THREAD_COMMAND_SET($cmd);
		}	
	}

	if($GLOBALS["VERBOSE"]){events(__FUNCTION__. ":: die...");}

}
function group30s(){
	$array[]="exec.mpstat.php";
	$array[]="exec.jgrowl.php --build";
	$array[]="cron.notifs.php";
    

	if($GLOBALS["cpuLimitEnabled"]){$array2[]="process1 --cpulimit";}
	if($GLOBALS["POSTFIX_INSTALLED"]){
		if($_GET["MIME_DEFANGINSTALLED"]){$array2[]="artica-install --graphdefang-gen";}
		if(!$GLOBALS["OVERLOADED"]){
				$array2[]="artica-attachments";
				$array2[]="artica-thread-back";
				}
	}

	
	if(!$GLOBALS["OVERLOADED"]){$array2[]="artica-install --usb-backup";}
	
	
  if(is_array($array)){
		while (list ($index, $file) = each ($array) ){
			$cmd="{$_GET["PHP5"]} /usr/share/artica-postfix/$file";
			sys_THREAD_COMMAND_SET($cmd);
		}	
	}
	
if(is_array($array2)){
		while (list ($index, $file) = each ($array2) ){
			$cmd="/usr/share/artica-postfix/bin/$file";
			sys_THREAD_COMMAND_SET($cmd);
		}	
	}	
}

//2H
function Group300(){
	
	if(!is_file("/etc/artica-postfix/settings/Daemons/HdparmInfos")){sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.hdparm.php");}
	$array[]="exec.mysql.build.php --tables";
	
	
	if($GLOBALS["POSTFIX_INSTALLED"]){
		$array[]="exec.organization.statistics.php";
		$array[]="exec.quarantine-clean.php";
	}
	
	
	$array2[]="artica-install -geoip-updates";
	  
	while (list ($index, $file) = each ($array) ){
		$cmd="{$_GET["PHP5"]} /usr/share/artica-postfix/$file";
		sys_THREAD_COMMAND_SET($cmd);
	}

	while (list ($index, $file) = each ($array2) ){
		$cmd="/usr/share/artica-postfix/bin/$file";
		sys_THREAD_COMMAND_SET($cmd);
	}   
	if($GLOBALS["VERBOSE"]){events(__FUNCTION__. ":: die...");}   	
}


function group120(){
	$array[]="exec.apt-get.php --update";
	$array[]="exec.cleanfiles.php";
	
	if($GLOBALS["POSTFIX_INSTALLED"]){
		$array[]="exec.smtp.export.users.php --sync";
		$array[]="exec.quarantine-clean.php";
	}
	
	
if($GLOBALS["DANSGUARDIAN_INSTALLED"]){
		$array["exec.dansguardian.compile.php --patterns"]="exec.dansguardian.compile.php --patterns";
		}	
	
	while (list ($index, $file) = each ($array) ){
		$cmd="{$_GET["PHP5"]} /usr/share/artica-postfix/$file";
		sys_THREAD_COMMAND_SET($cmd);
	}	
	
	
	$array2[]="artica-install --awstats-generate";
	$array2[]="artica-update";
	$array2[]="artica-install --cups-drivers";
	$array2[]="artica-update --spamassassin-bl";
	$array2[]="artica-install -watchdog daemon";
	
	if($GLOBALS["EnableArticaWatchDog"]==1){
		$array2[]="artica-install --urgency-start";
	}
	
	

	while (list ($index, $file) = each ($array2) ){
		$cmd="/usr/share/artica-postfix/bin/$file";
		sys_THREAD_COMMAND_SET($cmd);
	}		

	if($GLOBALS["VERBOSE"]){events(__FUNCTION__. ":: die...");}
}


function mailarchives(){
	$array[]="exec.mailarchive.php";
	$array[]="exec.mailbackup.php";
	$array[]="exec.fetchmail.sql.php";
	
	
	while (list ($index, $file) = each ($array) ){
	if(system_is_overloaded()){events(__FUNCTION__. ":: die, overloaded");die();}
		$cmd="{$_GET["PHP5"]} /usr/share/artica-postfix/$file";
		sys_THREAD_COMMAND_SET($cmd);
	}
	if($GLOBALS["VERBOSE"]){events(__FUNCTION__. ":: die...");}
	
}



function events($text){
		$f=new debuglogs();
		$f->events(basename(__FILE__)." $text","/var/log/artica-postfix/artica-status.debug");
		}
?>