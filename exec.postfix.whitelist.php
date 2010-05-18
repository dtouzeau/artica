<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.main_cf.inc');
include_once(dirname(__FILE__).'/ressources/class.milter.greylist.inc');

if($argv[1]=='--verbose'){$GLOBALS["VERBOSE"]=true;}
CleanFile();
ASSP();
milter_greylist();



function CleanFile(){
$mainfile="/etc/artica-postfix/settings/Daemons/PostfixAutoBlockWhiteList";	
$datas=@file_get_contents($mainfile);
$tbl=explode("\n",$datas);

if(is_array($tbl)){
while (list ($num, $ligne) = each ($tbl) ){
			if($ligne==null){continue;}
			if($GLOBALS["VERBOSE"]){echo "Found $ligne\n";}
			$GLOBALS["WHITELISTED"][$ligne]=$ligne;
	}
}
$GLOBALS["WHITELISTED"]["127.0.0.1"]="127.0.0.1";
}

function ASSP(){
$mainfile="/etc/artica-postfix/settings/Daemons/PostfixAutoBlockWhiteList";
if(is_dir("/usr/share/assp/files")){
		@copy($mainfile,"/usr/share/assp/files/nodelay.txt");
		shell_exec("/usr/share/artica-postfix/bin/artica-install --reload-assp");
	}
}

function milter_greylist(){
	$save=false;
	if(!is_array($GLOBALS["WHITELISTED"])){return null;}
	$mi=new milter_greylist();
	$whitelisted=$mi->GetWhiteListed();
	if($GLOBALS["VERBOSE"]){echo "whitelisted=".count($whitelisted)."\n";}
	while (list ($num, $ligne) = each ($GLOBALS["WHITELISTED"]) ){
		if($GLOBALS["VERBOSE"]){echo "whitelisted[$num]=".$whitelisted[$num]."\n";}
		if(!$whitelisted[$num]){
			if($GLOBALS["VERBOSE"]){echo "insert new milter-greylist rule for $ligne\n";}
			$mi->acl[]="acl whitelist addr $num # PostfixAutoBlockWhiteList";
			$save=true;
		}else{
		  if($GLOBALS["VERBOSE"]){echo "Already inserted $ligne\n";}
		}
		
	}
	if($save){
		$mi->SaveToLdap();
	}
	
	
	
}

?>