<?php
include_once(dirname(__FILE__)."/ressources/class.status.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.sockets.inc");
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');

if($argv[1]=="--disks"){CheckHardDrives();die();}

$sock=new sockets();
if($sock->DATA_CACHE("JGROWL_EXECUTED",false,2)==1){die();}


if($sock->GET_INFO("DisableJGrowl")==1){
	writelogs("JGrowl disabled, aborting","MAIN",__FUNCTION__,__FILE__,__LINE__);
	die();
}

$sock->DATA_CACHE_SAVE("JGROWL_EXECUTED",1);


writelogs("Running jGrowl...","MAIN",__FUNCTION__,__FILE__,__LINE__);
$status=new status();
$tpl=new templates();
$users=new usersMenus();
$GLOBALS["USERS"]=$users;
$GLOBALS["NO_CLAMAV_UPDATE"]=$sock->GET_INFO("jgrowl_no_clamav_update");
$GLOBALS["NO_KAS_UPDATE"]=$sock->GET_INFO("jgrowl_no_kas_update");

$array=$status->StatusFailed(1);

		if(count($array)>2){
		while (list ($num, $val) = each ($array) ){
			$disable_this_service=$tpl->_ENGINE_parse_body("{disable_this_service_click}");
			
				$add_1[]=$tpl->_ENGINE_parse_body("<li style=color:#C3393E;font-size:10px><strong>{{$val["PRODUCT"]}}</strong></li>");
			}	
			
		$title=$tpl->_ENGINE_parse_body("{TOO_MANY_STOPPED_SERVICES}");
			$start_service_in_debug=$tpl->_ENGINE_parse_body("{start_all_failed_services}");
			$title=str_replace(" ","&nbsp;",$title);
			$html[]="\$.jGrowl(\"";
			$html[]="<table>";
			$html[]="<tr>";
			$html[]="<td width=1% valign=top>";
			$html[]="<img src=img/danger48.png>";
			$html[]="</td>";
			$html[]="<td valign=top>";
			$html[]="<span style=color:#C3393E;font-size:15px>$title";
			$html[]="</span><hr>";
			$html[]=implode(" ",$add_1). "<div style=text-align:right><a href='#' OnClick=javascript:Loadjs('admin.index.php?start-all-services=yes'); style='text-decoration:underline;font-size:16px'>$start_service_in_debug</a></div>";
			$html[]="\",";
			$html[]="{header: '$title',life:15000});";
		
			echo implode("",$html)."\n";
			unset($html);	
			unset($array);
		}


if(is_array($array)){
$tpl=new templates();
while (list ($num, $val) = each ($array) ){
	$disable_this_service=$tpl->_ENGINE_parse_body("{disable_this_service_click}");
	if($val["PRODUCT"]=="APP_NFS"){
		$add="<li><a href='#' OnClick=javascript:Loadjs('admin.index.services.status.php?disable-nfs=yes'); style='text-decoration:underline'>$disable_this_service</a></li>";
	}
	$title=$tpl->_ENGINE_parse_body("{{$val["PRODUCT"]}}");
	$start_service_in_debug=$tpl->_ENGINE_parse_body("{start_service_in_debug}");
	$js_service="Loadjs('StartStopServices.php?APP={$val["PRODUCT"]}&cmd={$val["service_cmd"]}&action=start')";
	$title=str_replace(" ","&nbsp;",$title);
	$html[]="\$.jGrowl(\"";
	$html[]="<table>";
	$html[]="<tr>";
	$html[]="<td width=1%>";
	$html[]="<img src=img/danger48.png>";
	$html[]="</td>";
	$html[]="<td valign=top>";
	$html[]="<span style=color:#C3393E;font-size:16px>$title ". $tpl->_ENGINE_parse_body("{$val["WHY"]}");
	$html[]="</span><hr>";
	$html[]="<li><a href='#' OnClick=javascript:$js_service style='text-decoration:underline'>$start_service_in_debug</a></li>$add";
	$html[]="\",";
	$html[]="{header: '$title',life:15000});";

	echo implode("",$html)."\n";
	unset($html);
	
}
}
jGrowQueue();
CheckLDAPBranch();
CheckHardDrives();
CheckPhilesight();
CurrentInstall();
CurrentCyrusBackup();
VirusFound();
gdinfos();
curlinit();
test_mysql();
CurrentUpdate();
nmap();
Xapian();
CurrentXapian();
NEW_VERSIONS();
ldap_err();
milter_keepup2date();
proxy_keepup2date();
DomainAdmin();
DansGuardianPattern();
aptget();
imapsync();
Overloaded();
lshw();
sa_compile();
echo "Loadjs('admin.index.php?memory-status=yes');";

$sock->DATA_CACHE_SAVE("JGROWL_EXECUTED",0);
function Overloaded(){
	if(!systemMaxOverloaded()){return false;}
	
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{OVERLOADED_SYSTEM}");
	$link="<center><a href='#' OnClick=javascript:Loadjs('artica.performances.php'); style='font-weight:bolder;text-decoration:underline;font-size:11px'>{performances_settings}</a></center>";
	$html[]="\$.jGrowl(\"";
	$html[]="<table>";
	$html[]="<tr>";
	$html[]="<td width=1% valign='top'>";
	$html[]="<img src=img/database-error-48.png>";
	$html[]="</td>";
	$html[]="<td valign=top>";
	$html[]="<span style=color:red;font-size:16px>$title";
	$html[]="</span><hr>";
	$html[]=$tpl->_ENGINE_parse_body("<span style=font-size:13px>{OVERLOADED_SYSTEM_EXPLAIN}</span><p>$link</p>","");
	$html[]="\",";
	$html[]="{header: '$title',life:15000});";
	echo implode("",$html)."\n";		
	}

function DansGuardianPattern(){
	if(is_file("ressources/logs/dansguardian.patterns")){return;}
	if(!$GLOBALS["USERS"]->DANSGUARDIAN_INSTALLED){return null;}
	
	$sock=new sockets();
	if($sock->GET_INFO("DansGuardianEnabled")<>1){return null;}
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{DANSGUARDIAN_BLACKLISTS_MISSING}");
	$link="<center><a href='#' OnClick=javascript:Loadjs('dansguardian.db.update.php'); style='font-weight:bolder;text-decoration:underline;font-size:11px'>{DANSGUARDIAN_BLACKLISTS_UPDATE}</a></center>";
	$html[]="\$.jGrowl(\"";
	$html[]="<table>";
	$html[]="<tr>";
	$html[]="<td width=1% valign='top'>";
	$html[]="<img src=img/database-error-48.png>";
	$html[]="</td>";
	$html[]="<td valign=top>";
	$html[]="<span style=color:red;font-size:16px>$title";
	$html[]="</span><hr>";
	$html[]=$tpl->_ENGINE_parse_body("<span style=font-size:13px>{DANSGUARDIAN_BLACKLISTS_MISSING_TEXT}</span><p>$link</p>","dansguardian.index.php");
	$html[]="\",";
	$html[]="{header: '$title',life:15000});";
	echo implode("",$html)."\n";		
	}


function DomainAdmin(){
	
	
	if(!$GLOBALS["USERS"]->SAMBA_INSTALLED){return null;}
	$sock=new sockets();
	$DomainAdministratorEdited=$sock->GET_INFO("DomainAdministratorEdited");	
	if($DomainAdministratorEdited==1){return;}
	$link="<center><a href='#' OnClick=javascript:Loadjs('samba.index.php?behavior-admin=yes&script=yes'); style='font-weight:bolder;text-decoration:underline;font-size:11px'>{domain_admin}</a></center>";
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{DOMAIN_ADMIN_NOT_EDITED}");
	$html[]="\$.jGrowl(\"";
	$html[]="<table>";
	$html[]="<tr>";
	$html[]="<td width=1% valign='top'>";
	$html[]="<img src=img/user-error-48.png>";
	$html[]="</td>";
	$html[]="<td valign=top>";
	$html[]="<span style=color:red;font-size:16px>$title";
	$html[]="</span><hr>";
	$html[]=$tpl->_ENGINE_parse_body("<span style=font-size:13px>{DOMAIN_ADMIN_NOT_EDITED_TEXT}</span><p>$link</p>","samba.index.php");
	$html[]="\",";
	$html[]="{header: '$title',life:15000});";
	echo implode("",$html)."\n";	
	
}




function Xapian(){
	if(!is_file("/usr/share/artica-postfix/ressources/logs/xapian.results")){return null;}
	$link="<center style='margin:10px'><a href='#' OnClick=javascript:Loadjs('index.troubleshoot.php?artica-branch=yes'); style='font-weight:bolder;text-decoration:underline;font-size:14px'>{repair}</a></center>";
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_XAPIAN}");
	$html[]="\$.jGrowl(\"";
	$html[]="<table>";
	$html[]="<tr>";
	$html[]="<td width=1% valign='top'>";
	$html[]="<img src=img/info-48.png>";
	$html[]="</td>";
	$html[]="<td valign=top>";
	$html[]="<span style=color:red;font-size:16px>$title";
	$html[]="</span><hr>";
	$html[]=$tpl->_ENGINE_parse_body("<span style=font-size:13px>".@file_get_contents("/usr/share/artica-postfix/ressources/logs/xapian.results")."</span>");
	$html[]="\",";
	$html[]="{header: '$title',life:15000});";
	echo implode("",$html)."\n";
	@unlink("/usr/share/artica-postfix/ressources/logs/xapian.results");	
}


function CheckLDAPBranch(){
	$ldap=new clladp();
	if(!$ldap->ArticaBranchCorrupted){return null;}
	$link="<center style='margin:10px'><a href='#' OnClick=javascript:Loadjs('index.troubleshoot.php?artica-branch=yes'); style='font-weight:bolder;text-decoration:underline;font-size:14px'>{repair}</a></center>";
	
		$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{CORRUPTED_LDAP_BRANCH}");
	$html[]="\$.jGrowl(\"";
	$html[]="<table>";
	$html[]="<tr>";
	$html[]="<td width=1% valign='top'>";
	$html[]="<img src=img/danger48.png>";
	$html[]="</td>";
	$html[]="<td valign=top>";
	$html[]="<span style=color:red;font-size:16px>$title";
	$html[]="</span><hr>";
	$html[]=$tpl->_ENGINE_parse_body("<strong style=font-size:12px>{CORRUPTED_LDAP_BRANCH_TEXT}</strong><br>$link</a>");
	$html[]="\",";
	$html[]="{header: '$title',life:15000});";
	echo implode("",$html)."\n";	
	
}

function CheckHardDrives(){
	
$sock=new sockets();
$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?disks-list=yes")));
if(!is_array($array)){return array();}

$tpl=new templates();
while (list ($num, $val) = each ($array) ){
	$num=basename($num);
	if(preg_match("#^sr[0-9]$#",$num)){continue;}
	if($val["POURC"]>90){
	$title=$tpl->_ENGINE_parse_body("<strong>$num</strong> {$val["POURC"]}% {used} !");
	$start_service_in_debug=$tpl->_ENGINE_parse_body("{start_service_in_debug}");
	$title=str_replace(" ","&nbsp;",$title);
	$html[]="\$.jGrowl(\"";
	$html[]="<table>";
	$html[]="<tr>";
	$html[]="<td width=1%>";
	$html[]="<img src=img/48-hd-warning.png>";
	$html[]="</td>";
	$html[]="<td valign=top>";
	$html[]="<span style=color:red;font-size:16px>$title";
	$html[]="</span><hr>";
	$html[]=$tpl->_ENGINE_parse_body("<strong style=font-size:14px>{$val["DISP"]}{$val["UNIT"]} {free}/{$val["USED"]}{$val["UNIT"]} {used}</strong>");
	$html[]="\",";
	$html[]="{header: '$title',life:15000});";

	echo implode("",$html)."\n";
	unset($html);
	}
}



	
}

function CheckPhilesight(){
	$unix=new unix();
	$pid=$unix->PIDOF_PATTERN("/usr/bin/ruby /usr/share/artica-postfix/bin/philesight");
	
	if($pid>0){
		$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{INDEXING_STORAGE}");
	$html[]="\$.jGrowl(\"";
	$html[]="<table>";
	$html[]="<tr>";
	$html[]="<td width=1% valign='top'>";
	$html[]="<img src=img/info-48.png>";
	$html[]="</td>";
	$html[]="<td valign=top>";
	$html[]="<span style=color:#C3393E;font-size:16px>$title";
	$html[]="</span><hr>";
	$html[]=$tpl->_ENGINE_parse_body("<strong style=font-size:12px>{APP_PHILESIGHT_INDEXING}</strong><br><i>PID:$pid</i>");
	$html[]="\",";
	$html[]="{header: '$title',life:15000});";
	echo implode("",$html)."\n";
	}
	
	
}
function sa_compile(){
	$unix=new unix();
	$pid=$unix->PIDOF_PATTERN("sa-compile -D");
	
	if($pid>0){
		$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{SPAMASSASSIN_UPDATE_COMPILATION}");
	$html[]="\$.jGrowl(\"";
	$html[]="<table>";
	$html[]="<tr>";
	$html[]="<td width=1% valign='top'>";
	$html[]="<img src=img/info-48.png>";
	$html[]="</td>";
	$html[]="<td valign=top>";
	$html[]="<span style=color:#C3393E;font-size:16px>$title";
	$html[]="</span><hr>";
	$html[]=$tpl->_ENGINE_parse_body("<strong style=font-size:12px>{SPAMASSASSIN_UPDATE_COMPILATION_TEXT}</strong><br><i>PID:$pid</i>");
	$html[]="\",";
	$html[]="{header: '$title',life:15000});";
	echo implode("",$html)."\n";
	}
	
	
}





function aptget(){
	$unix=new unix();
	$pid=$unix->PIDOF_PATTERN("/usr/bin/apt-get");
	
	if($pid>0){
		$TIME=$unix->PROCCESS_TIME_MIN($pid);
		$tpl=new templates();
		$title=$tpl->_ENGINE_parse_body("{APT_GET_RUNNING}");
		$html[]="\$.jGrowl(\"";
		$html[]="<table>";
		$html[]="<tr>";
		$html[]="<td width=1% valign='top'>";
		$html[]="<img src=img/info-48.png>";
		$html[]="</td>";
		$html[]="<td valign=top>";
		$html[]="<span style=color:#C3393E;font-size:16px>$title";
		$html[]="</span><hr>";
		$html[]=$tpl->_ENGINE_parse_body("<strong style=font-size:12px>{APT_GET_RUNNING_TEXT}</strong><br><i>PID:$pid {since} {$TIME}mn</i>");
		$html[]="\",";
		$html[]="{header: '$title',life:15000});";
		echo implode("",$html)."\n";
	}	
}


function lshw(){
	$unix=new unix();
	$pid=$unix->PIDOF_PATTERN("lshw -html");
if($pid>0){
		$TIME=$unix->PROCCESS_TIME_MIN($pid);
		$tpl=new templates();
		$title=$tpl->_ENGINE_parse_body("{LSHW_RUNNING}");
		$html[]="\$.jGrowl(\"";
		$html[]="<table>";
		$html[]="<tr>";
		$html[]="<td width=1% valign='top'>";
		$html[]="<img src=img/info-48.png>";
		$html[]="</td>";
		$html[]="<td valign=top>";
		$html[]="<span style=color:#C3393E;font-size:16px>$title";
		$html[]="</span><hr>";
		$html[]=$tpl->_ENGINE_parse_body("<strong style=font-size:12px>{LSHW_RUNNING_TEXT}</strong><br><i>PID:$pid {since} {$TIME}mn</i>");
		$html[]="\",";
		$html[]="{header: '$title',life:15000});";
		echo implode("",$html)."\n";
	}				
	
}




function nmap(){
	$unix=new unix();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{scanning_your_network}");
	$pattern=$unix->find_program('pgrep')." -l -f \"nmap\s+-O\"";
	exec($pattern,$returns);
	writelogs("$pattern count:".count($returns),__FUNCTION__,__FILE__,__LINE__);
	
	if(!is_array($returns)){return null;}
	while (list ($num, $val) = each ($returns) ){
		writelogs("$val",__FUNCTION__,__FILE__,__LINE__);
		if(preg_match("#nmap -O\s+(.+?)\s+-#",$val,$re)){
			if($GLOBALS["NMAP"][$re[1]]){continue;}
			$GLOBALS["NMAP"][$re[1]]=true;
			$html[]="\$.jGrowl(\"";
			$html[]="<table>";
			$html[]="<tr>";
			$html[]="<td width=1% valign='top'>";
			$html[]="<img src=img/info-48.png>";
			$html[]="</td>";
			$html[]="<td valign=top>";
			$html[]="<span style=color:#C3393E;font-size:16px>$title";
			$html[]="</span><hr>";
			$html[]=$tpl->_ENGINE_parse_body("<strong style=font-size:12px>{currently_scanning} {$re[1]}");
			$html[]="\",";
			$html[]="{header: '$title',life:15000});";
		echo implode("",$html)."\n";
			
		}else{
			writelogs("NO MATCH:$val",__FUNCTION__,__FILE__,__LINE__);	
		}
	}
}


function NEW_VERSIONS(){
	$unix=new unix();
	$tpl=new templates();
	
	$array_vers=unserialize(@file_get_contents("ressources/logs/jGrowl-new-versions.txt"));
	if(!is_array($array_vers)){return null;}

	while (list ($num, $array) = each ($array_vers) ){
		writelogs("$num",__FUNCTION__,__FILE__,__LINE__);
		$title=$tpl->_ENGINE_parse_body("{$array["TITLE"]}");
		$text=$tpl->_ENGINE_parse_body("{$array["TEXT"]}");
		$link="<a href='#' OnClick=javascript:Loadjs('{$array["JS"]}'); style='font-weight:bolder;text-decoration:underline;font-size:11px'>{INSTALL_UPGRADE_RECOMPILE} {{$num}}</a>";
			$html[]="\$.jGrowl(\"";
			$html[]="<table>";
			$html[]="<tr>";
			$html[]="<td width=1% valign='top'>";
			$html[]="<img src=img/info-48.png>";
			$html[]="</td>";
			$html[]="<td valign=top>";
			$html[]="<span style=color:#C3393E;font-size:12px;font-weight:bold>$title</span>";
			$html[]="<hr>";
			$html[]=$tpl->_ENGINE_parse_body("<strong style=font-size:11px>$text</strong><p>$link</p>");
			$html[]="\",";
			$html[]="{header: '$title',life:15000});";
		echo implode("",$html)."\n";
		unset($html);	
		
	}
}
function CurrentInstall(){
	
	$unix=new unix();
	$tpl=new templates();
	$pattern=$unix->find_program('pgrep')." -l -f \"artica-make\"";
	$returns=array();
	
	exec($pattern,$returns);
	
	$installation_lauched=$tpl->_ENGINE_parse_body("{installation_lauched}");
	$installation_lauched=str_replace("\n"," ",$installation_lauched);
	$installation_lauched=str_replace("\r"," ",$installation_lauched);
			
	writelogs("$pattern",__FUNCTION__,__FILE__,__LINE__);
	if(!is_array($returns)){return null;}
	while (list ($num, $val) = each ($returns) ){
		writelogs("$val",__FUNCTION__,__FILE__,__LINE__);
		if(preg_match("#artica-make\s+(.+)#",$val,$re)){
		$re[1]=trim($re[1]);
		if(preg_match("#(.+?)\s+$#",$re[1],$ri)){$re[1]=$ri[1];}	
		$file=dirname(__FILE__). "/ressources/install/{$re[1]}.ini";
		$ini=new Bs_IniHandler();
		if(file_exists($file)){
	    	$data=file_get_contents($file);
			$ini->loadString($data);
			$pourc=$ini->_params["INSTALL"]["STATUS"];
			$text_info=$tpl->_ENGINE_parse_body($ini->_params["INSTALL"]["INFO"]);
			$text_info=str_replace("\n"," ",$text_info);
			$text_info=str_replace("\r"," ",$text_info);	
		}		
			
		$title=$tpl->_ENGINE_parse_body("{{$re[1]}}");
		$html[]="\$.jGrowl(\"";
		$html[]="<table>";
		$html[]="<tr>";
		$html[]="<td width=1% valign='top'>";
		$html[]="<img src=img/info-48.png>";
		$html[]="</td>";
		$html[]="<td valign=top>";
		$html[]="<span style=color:#C3393E;font-size:16px>$title";
		$html[]="</span><hr>";
		$html[]=$tpl->_ENGINE_parse_body("<strong style=font-size:12px>$installation_lauched </strong><br><i style=color:red>$pourc% $text_info</i>");
		$html[]="\",";
		$html[]="{header: '$title',life:15000});";
		echo implode("",$html)."\n";
		}
	}
	
	
	
}



function CurrentUpdate(){
	
	$unix=new unix();
	$tpl=new templates();
	$pattern=$unix->find_program('pgrep')." -l -f \"artica-update\"";
	$returns=array();
	exec($pattern,$returns);
	
	$title=$tpl->_ENGINE_parse_body("{artica_update_processing}");
	if(!is_array($returns)){return null;}
	while (list ($num, $val) = each ($returns) ){
		if(trim($val)==null){continue;}
		if(preg_match("#pgrep #",$val)){continue;}
		
		$array[]=$val;
	}
	
	if(!is_array($array)){return null;}
		while (list ($num, $val) = each ($array) ){
				if(preg_match("#([0-9]+)\s+#",trim($val),$re)){
					writelogs("Found PID {$re[1]} \"$val\"",__FUNCTION__,__FILE__);
					$pids[]=$re[1];
				}
			}
	
	writelogs(implode("\n",$array),__FUNCTION__,__LINE__);
		$pid=$pids[0];
		$TIME=$unix->PROCCESS_TIME_MIN($pid);
		if($TIME>90){
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?kill-pid-number=$pid");
		}
		$html[]="\$.jGrowl(\"";
		$html[]="<table>";
		$html[]="<tr>";
		$html[]="<td width=1% valign='top'>";
		$html[]="<img src=img/info-48.png>";
		$html[]="</td>";
		$html[]="<td valign=top>";
		$html[]="<span style=color:#C3393E;font-size:16px>$title";
		$html[]="</span><hr>";
		$html[]=$tpl->_ENGINE_parse_body("<strong style=font-size:12px>{artica_update_processing_text}</strong><br><i>{since} {$TIME}mn</i>");
		$html[]="\",";
		$html[]="{header: '$title',life:15000});";
		echo implode("",$html)."\n";
	}
	
	function milter_keepup2date(){
	if(!$GLOBALS["USERS"]->KAV_MILTER_INSTALLED){return null;}	
	$unix=new unix();
	$tpl=new templates();
	$pid=$unix->PIDOF("/opt/kav/5.6/kavmilter/bin/keepup2date");
	if($pid==null){return ;}
	$title=$tpl->_ENGINE_parse_body("{APP_KAVMILTER}");
	
		
		$TIME=$unix->PROCCESS_TIME_MIN($pid);
		if($TIME>90){
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?kill-pid-number=$pid");
		}
		$html[]="\$.jGrowl(\"";
		$html[]="<table>";
		$html[]="<tr>";
		$html[]="<td width=1% valign='top'>";
		$html[]="<img src=img/info-48.png>";
		$html[]="</td>";
		$html[]="<td valign=top>";
		$html[]="<span style=color:#C3393E;font-size:16px>$title";
		$html[]="</span><hr>";
		$html[]=$tpl->_ENGINE_parse_body("<strong style=font-size:12px>{artica_update_processing_text}</strong><br><i>{since} {$TIME}mn</i>");
		$html[]="\",";
		$html[]="{header: '$title',life:15000});";
		echo implode("",$html)."\n";
	}

function proxy_keepup2date(){
	if(!$GLOBALS["USERS"]->KAV4PROXY_INSTALLED){return null;}	
	$unix=new unix();
	$tpl=new templates();
	$pid=$unix->PIDOF("/opt/kaspersky/kav4proxy/bin/kav4proxy-keepup2date");
	if($pid==null){return ;}
	$title=$tpl->_ENGINE_parse_body("{APP_KAV4PROXY}");
	
		
		$TIME=$unix->PROCCESS_TIME_MIN($pid);
		if($TIME>90){
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?kill-pid-number=$pid");
		}
		$html[]="\$.jGrowl(\"";
		$html[]="<table>";
		$html[]="<tr>";
		$html[]="<td width=1% valign='top'>";
		$html[]="<img src=img/info-48.png>";
		$html[]="</td>";
		$html[]="<td valign=top>";
		$html[]="<span style=color:#C3393E;font-size:16px>$title";
		$html[]="</span><hr>";
		$html[]=$tpl->_ENGINE_parse_body("<strong style=font-size:12px>{artica_update_processing_text}</strong><br><i>{since} {$TIME}mn</i>");
		$html[]="\",";
		$html[]="{header: '$title',life:15000});";
		echo implode("",$html)."\n";
	}	
	
	
	
	
function CurrentCyrusBackup(){
	if(!$GLOBALS["USERS"]->cyrus_imapd_installed){return null;}
	$unix=new unix();
	$tpl=new templates();
	$pattern=$unix->find_program('pgrep')." -l -f \"artica-backup --single-cyrus\"";
	$returns=array();
	exec($pattern,$returns);
	
	$title=$tpl->_ENGINE_parse_body("{artica_cyrus_backup_processing}");
	if(!is_array($returns)){return null;}
	while (list ($num, $val) = each ($returns) ){
		if(trim($val)==null){continue;}
		if(preg_match("#pgrep #",$val)){continue;}
		
		$array[]=$val;
	}
	
	if(!is_array($array)){return null;}
		while (list ($num, $val) = each ($array) ){
				if(preg_match("#([0-9]+)\s+#",trim($val),$re)){
					writelogs("Found PID {$re[1]} \"$val\"",__FUNCTION__,__FILE__);
					$pids[]=$re[1];
				}
			}
	
	writelogs(implode("\n",$array),__FUNCTION__,__LINE__);
		$pid=$pids[0];
		$TIME=$unix->PROCCESS_TIME_MIN($pid);
		if($TIME>190){
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?kill-pid-number=$pid");
		}
		$html[]="\$.jGrowl(\"";
		$html[]="<table>";
		$html[]="<tr>";
		$html[]="<td width=1% valign='top'>";
		$html[]="<img src=img/info-48.png>";
		$html[]="</td>";
		$html[]="<td valign=top>";
		$html[]="<span style=color:#C3393E;font-size:16px>$title";
		$html[]="</span><hr>";
		$html[]=$tpl->_ENGINE_parse_body("<strong style=font-size:12px>{artica_cyrus_backup_processing_text}</strong><br><i>{since} {$TIME}mn</i>");
		$html[]="\",";
		$html[]="{header: '$title',life:15000});";
		echo implode("",$html)."\n";
	}	
	
	
	

function CurrentXapian(){
	
	$unix=new unix();
	$tpl=new templates();
	$pattern=$unix->find_program('pgrep')." -l -f \"omindex\"";
	$returns=array();
	exec($pattern,$returns);
	
	$title=$tpl->_ENGINE_parse_body("{xapian_processing}");
	if(!is_array($returns)){return null;}
	while (list ($num, $val) = each ($returns) ){
		if(trim($val)==null){continue;}
		if(preg_match("#pgrep #",$val)){continue;}
		$array[]=$val;
	}
	
	if(!is_array($array)){return null;}
	while (list ($num, $val) = each ($array) ){
		if(preg_match("#([0-9]+)\s+#",trim($val),$re)){
			$pids[]=$re[1];
		}
	}
	
	writelogs(implode("\n",$array),__FUNCTION__,__LINE__);
	
		$html[]="\$.jGrowl(\"";
		$html[]="<table>";
		$html[]="<tr>";
		$html[]="<td width=1% valign='top'>";
		$html[]="<img src=img/info-48.png>";
		$html[]="</td>";
		$html[]="<td valign=top>";
		$html[]="<span style=color:#C3393E;font-size:16px>$title";
		$html[]="</span><hr>";
		$html[]=$tpl->_ENGINE_parse_body("<strong style=font-size:12px>{xapian_processing_text}</strong><div style=text-align:right><i>PID:".implode(" ",$pids)."</i></div>");
		$html[]="\",";
		$html[]="{header: '$title',life:15000});";
		echo implode("",$html)."\n";
	}
	
function imapsync(){
	$unix=new unix();
	$tpl=new templates();
	$imapsync=$unix->find_program('imapsync');
	
	if($imapsync==null){
		writelogs("imapsync is not installed",__FUNCTION__,__FILE__);
		return;
		}
	$pattern=$unix->find_program('pgrep')." -l -f \"$imapsync\"";
	$returns=array();
	exec($pattern,$returns);
	
	while (list ($num, $val) = each ($returns) ){
		if(trim($val)==null){continue;}
		writelogs("val \"$val\"",__FUNCTION__,__FILE__);
		if(preg_match("#pgrep #",$val)){continue;}
		
		if(preg_match("#^([0-9]+)\s+(.+)#",$val,$re)){$array[$re[1]]=$re[2];}
	}	
	
	if(!is_array($array)){
		writelogs("imapsync no pgrep",__FUNCTION__,__FILE__);
		return;}
	
	$title=$tpl->_ENGINE_parse_body("{synchronizing_mailbox}");
	
	while (list ($pid, $commandline) = each ($array) ){
		
		if(!preg_match("#imapsync.+?--host1\s+(.+?)\s+.+?--user2\s+(.+?)\s+#",$commandline,$re)){
			writelogs("unable to preg match \"$commandline\"",__FUNCTION__,__FILE__);
			continue;
			}
			
		if($GLOBALS["imapsync-jgrowl"][md5("{$re[1]}{$re[2]}")]==true){continue;}	
			
		$GLOBALS["imapsync-jgrowl"][md5("{$re[1]}{$re[2]}")]=true;	
		writelogs("imapsync {$re[1]} {$re[2]}",__FUNCTION__,__FILE__);
		$TIME=$unix->PROCCESS_TIME_MIN($pid);
		$HOST=$re[1];
		$uid=$re[2];
		
		$html[]="\$.jGrowl(\"";
		$html[]="<table>";
		$html[]="<tr>";
		$html[]="<td width=1% valign='top'>";
		$html[]="<img src=img/info-48.png>";
		$html[]="</td>";
		$html[]="<td valign=top>";
		$html[]="<span style=color:#C3393E;font-size:16px>$title";
		$html[]="</span><hr>";
		$html[]=$tpl->_ENGINE_parse_body("<strong style=font-size:11px>{server}:$HOST<br>{mailbox}: $uid</strong><div style=text-align:right><br><i>{since} {$TIME}mn</i>");
		$html[]="\",";
		$html[]="{header: '$title',life:15000});";
		echo implode("",$html)."\n";
		
	}
	

	
}
	
	
function VirusFound(){
	$q=new mysql();
	$sql="SELECT COUNT(ID) as tcount FROM antivirus_events WHERE email=0";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));
	$events_virus=$ligne["tcount"];
	$tpl=new templates();
	if($events_virus==0){return null;}
		$title=$events_virus ." ".$tpl->_ENGINE_parse_body("{VIRUSES_FOUND}");
		$html[]="\$.jGrowl(\"";
		$html[]="<table>";
		$html[]="<tr>";
		$html[]="<td width=1% valign='top'>";
		$html[]="<img src=img/48-virus.png>";
		$html[]="</td>";
		$html[]="<td valign=top>";
		$html[]="<span style=color:#C3393E;font-size:16px>$title";
		$html[]="</span><hr>";
		$html[]=$tpl->_ENGINE_parse_body("<a href='#' OnClick=javascript:Loadjs('antivirus.events.php'); style='text-decoration:underline'><strong style=font-size:12px>$events_virus {VIRUSES_FOUND_TEXT}</strong></a>");
		$html[]="\",";
		$html[]="{header: '$title',life:15000});";
		echo implode("",$html)."\n";
	}

function gdinfos(){
$init=true;	
$tpl=new templates();
if(!function_exists("gd_info")){
		$init=false;
}
if($init){
$inf=@gd_info();
if(!is_array($inf)){
		writelogs("It seems that GD library is not installed, please install it to have full feature",__FUNCTION__,__FILE__);
		$init=false;
}}

if(!$init){
	
		$title=$tpl->_ENGINE_parse_body("{GDPHP_NOT_INSTALLED}");
		$html[]="\$.jGrowl(\"";
		$html[]="<table>";
		$html[]="<tr>";
		$html[]="<td width=1% valign='top'>";
		$html[]="<img src=img/software-error-48.png>";
		$html[]="</td>";
		$html[]="<td valign=top>";
		$html[]="<span style=color:#C3393E;font-size:16px>$title";
		$html[]="</span><hr>";
		$html[]=$tpl->_ENGINE_parse_body("<strong style=font-size:12px>{ERROR_GDPHP_NOT_INSTALLED}</strong></a>");
		$html[]="\",";
		$html[]="{header: '$title',life:15000});";
		echo implode("",$html)."\n";	
	

}

	
}
function curlinit(){
$init=true;	
$tpl=new templates();
if(!function_exists("curl_init")){
		$init=false;
}
if(!$init){
			$title=$tpl->_ENGINE_parse_body("{CURLPHP_NOT_INSTALLED}");
		$html[]="\$.jGrowl(\"";
		$html[]="<table>";
		$html[]="<tr>";
		$html[]="<td width=1% valign='top'>";
		$html[]="<img src=img/software-error-48.png>";
		$html[]="</td>";
		$html[]="<td valign=top>";
		$html[]="<span style=color:#C3393E;font-size:16px>$title";
		$html[]="</span><hr>";
		$html[]=$tpl->_ENGINE_parse_body("<strong style=font-size:12px>{ERROR_CURLPHP_NOT_INSTALLED}</strong></a>");
		$html[]="\",";
		$html[]="{header: '$title',life:15000});";
		echo implode("",$html)."\n";

}

	
}

function ldap_err(){
	$ldap=new clladp();
	$tpl=new templates();
	
	if($ldap->ldapFailed==true){
		$ldap_error=$ldap->ErrorConnection();
		$title=$tpl->_ENGINE_parse_body("{$ldap_error["TITLE"]}");
		$html[]="\$.jGrowl(\"";
		$html[]="<table>";
		$html[]="<tr>";
		$html[]="<td width=1% valign='top'>";
		$html[]="<img src=img/database-error-48.png>";
		$html[]="</td>";
		$html[]="<td valign=top>";
		$html[]="<span style=color:#C3393E;font-size:16px>$title";
		$html[]="</span><hr>";
		$html[]=$tpl->_ENGINE_parse_body("{$ldap_error["TEXT"]}");
		$html[]="\",";
		$html[]="{header: '$title',life:15000});";
		echo implode("",$html)."\n";		
	}	
}
function test_mysql(){
	$q=new mysql();
	$tpl=new templates();
	if(!$q->TestingConnection()){
		$title=$tpl->_ENGINE_parse_body("{ERROR_MYSQL_CONNECTION}");
		$html[]="\$.jGrowl(\"";
		$html[]="<table>";
		$html[]="<tr>";
		$html[]="<td width=1% valign='top'>";
		$html[]="<img src=img/database-error-48.png>";
		$html[]="</td>";
		$html[]="<td valign=top>";
		$html[]="<span style=color:#C3393E;font-size:16px>$title";
		$html[]="</span><hr>";
		$html[]=$tpl->_ENGINE_parse_body("<a href='#' OnClick=javascript:Loadjs('mysql.password.php'); style='text-decoration:underline'><strong>$q->mysql_error</strong>");
		$html[]="\",";
		$html[]="{header: '$title',life:15000});";
		echo implode("",$html)."\n";			
		
	}
}

function jGrowQueue(){
	
	$tpl=new templates();
	$array=DirListTime("/usr/share/artica-postfix/ressources/logs/jGrowl");

	
	if(!is_array($array)){return null;}
	$sock=new sockets();
	$jGrowlMaxEvents=$sock->GET_INFO("jGrowlMaxEvents");
	if($jGrowlMaxEvents==null){$jGrowlMaxEvents=50;}
	ksort($array);
	$count=0;
	
	while (list ($num, $filename) = each ($array) ){
		$noecho=false;
		$conf=jGrowQueue_parse("/usr/share/artica-postfix/ressources/logs/jGrowl/$filename");
		if(!is_array($conf)){continue;}
		$count=$count+1;
		if($count<$jGrowlMaxEvents){
			$title=$tpl->_ENGINE_parse_body("{$conf["subject"]}");
			$title=str_replace("(","",$title);
			$title=str_replace(")","",$title);
			$title=str_replace("'","`",$title);
			$title=str_replace('"',"`",$title);
			$title=htmlentities($title);
			if(strlen($conf["text"])>250){$conf["text"]=null;}
			$conf["text"]=str_replace("(","",$conf["text"]);
			$conf["text"]=str_replace(")","",$conf["text"]);
			$conf["text"]=str_replace("\n"," ",$conf["text"]);
			$conf["text"]=str_replace("'","`",$conf["text"]);
			$conf["text"]=str_replace('"',"`",$conf["text"]);
			$conf["text"]=htmlentities($conf["text"]);
			$html[]="\$.jGrowl(\"";
			$html[]="<table>";
			$html[]="<tr>";
			$html[]="<td width=1% valign='top'>";
			$html[]="<img src=img/info-48.png>";
			$html[]="</td>";
			$html[]="<td valign=top>";
			$html[]="<span style=color:#C3393E;font-size:16px>$title";
			$html[]="</span><hr>";
			$html[]=$tpl->_ENGINE_parse_body("<div style=font-size:13px>{$conf["date"]}<hr>{$conf["text"]}</div>");
			$html[]="\",";
			$html[]="{header: '$title',life:15000});";
			
			if($GLOBALS["NO_CLAMAV_UPDATE"]==1){
				if(preg_match("#ClamAV Database#is",$conf["text"])){$noecho=true;}
			}
			
			if($GLOBALS["NO_KAS_UPDATE"]==1){
				if(preg_match("#Kaspersky Anti-Spam.+?pattern file#is",$conf["text"])){$noecho=true;}
				if(preg_match("#Kaspersky Anti-Spam.+?pattern file#is",$title)){$noecho=true;}
			}		
			if(!$noecho){echo implode("",$html)."\n";}
		}				
		@unlink("/usr/share/artica-postfix/ressources/logs/jGrowl/$filename");
	}
}

function jGrowQueue_parse($file){
	$datas=@file_get_contents($file);
	if(preg_match("#<text>(.+?)</text>#is",$datas,$re)){
		$ARRAY_t["text"]=$re[1];
	}
	
	$tbl=explode("\n",$datas);
	while (list ($num, $val) = each ($tbl) ){
		if(preg_match("#date=(.+)#",$val,$re)){$ARRAY_t["date"]=$re[1];}
		if(preg_match("#subject=(.+)#",$val,$re)){$ARRAY_t["subject"]=$re[1];}

	}
	
	return $ARRAY_t;
}

function DirList($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){
		return array();
	}
	$count=0;	
	while ($file = readdir($dir_handle)) {
	  if($file=='.'){continue;}
	  if($file=='..'){continue;}
	  if(!is_file("$path/$file")){continue;}
			$array[$file]=$file;
			continue;
			}
	  
	if(!is_array($array)){return array();}
	@closedir($dir_handle);
	return $array;
}

function DirListTime($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){
		return array();
	}
	$count=0;	
	while ($file = readdir($dir_handle)) {
	  if($file=='.'){continue;}
	  if($file=='..'){continue;}
	  if(!is_file("$path/$file")){continue;}
	  		$time=jgrowl_file_time_min("$path/$file");
			$array[$time]=$file;
			continue;
			}
	  
	if(!is_array($array)){return array();}
	@closedir($dir_handle);
	return $array;
}
function jgrowl_file_time_min($path){
	if(!is_file($path)){return 100000;}
	 $last_modified = filemtime($path);
	 
$data1 = $last_modified;

$data2 = time();
$difference = ($data2 - $data1); 	 
return round($difference/60);	 
}


//$.jGrowl('<table style=\"width: 100%;\">	<tbody><tr>	<td valign=\"top\" width=\"1%\"><img src=\"img/danger64.png\" 
//onmouseover=\"javascript:AffBulle(\'Start this service in debug mode\');lightup(this, 100);\" 
//onmouseout=\"javascript:HideBulle();lightup(this, 50);\" style=\"border: 0px none ; opacity: 0.5;\" 
//id=\"img_cb4c90b8112c438a03f89c473bb28335\"></td>	<td valign=\"top\">
//<h3 style=\"height: 36px;\">DansGuardian</h3><div id=\"text_cb4c90b8112c438a03f89c473bb28335\" 
//style=\"height: 70px; font-size: 11px;\"><span style=\"color: rgb(211, 45, 45);\">
//Stopped</span>.&nbsp;&nbsp;.&nbsp;</div></td>	</tr>	</tbody></table>	', { header: 'Dansguardian is stopped', sticky: true });
?>