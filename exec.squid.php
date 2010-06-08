<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.squid.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');



if(!is_file("/usr/share/artica-postfix/ressources/settings.inc")){shell_exec("/usr/share/artica-postfix/bin/process1 --force --verbose");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}


if($argv[1]=="--retrans"){retrans();die();}

if($argv[1]=="--reconfigure"){
	ApplyConfig();
	echo "Starting......: Reloading Squid\n";
	writelogs("reload squid (if enabled)",__FUNCTION__,__FILE__,__LINE__);
	exec("/usr/share/artica-postfix/bin/artica-install --squid-reload");
	writelogs("reload Dansguardian (if enabled)",__FUNCTION__,__FILE__,__LINE__);
	echo "Starting......: Reloading Dansguardian (if enabled)\n";
	exec("/usr/share/artica-postfix/bin/artica-install --reload-dansguardian");
	writelogs("reload c-icap (if enabled)",__FUNCTION__,__FILE__,__LINE__);
	echo "Starting......: Reloading c-icap (if enabled)\n";
	exec("/usr/share/artica-postfix/bin/artica-install --c-icap-reload");
	writelogs("reload Kav4Proxy (if enabled)",__FUNCTION__,__FILE__,__LINE__);
	echo "Starting......: Reloading Kaspersky (if enabled)\n";
	exec("/usr/share/artica-postfix/bin/artica-install --reload-kav4proxy");	
	
	
	die();
}


if($argv[1]=="--build"){
	$squid=new squidbee();
	$unix=new unix();
	$squid->BuildBlockedSites();
	$SQUID_CONFIG_PATH=$unix->SQUID_CONFIG_PATH();
	if(!is_file("/etc/squid3/squid-block.acl")){@file_put_contents("/etc/squid3/squid-block.acl","");}	
	$conf=$squid->BuildSquidConf();
	@file_put_contents($SQUID_CONFIG_PATH,$conf);
	die();
}


function ApplyConfig(){
	$unix=new unix();
	
	$squid=new squidbee();
	writelogs("->BuildBlockedSites",__FUNCTION__,__FILE__,__LINE__);
	$squid->BuildBlockedSites();
	if(!is_file("/etc/squid3/squid-block.acl")){@file_put_contents("/etc/squid3/squid-block.acl","");}
	
	
	$SQUID_CONFIG_PATH=$unix->SQUID_CONFIG_PATH();
	if(!is_file($SQUID_CONFIG_PATH)){
		writelogs("Unable to stat squid configuration file \"$SQUID_CONFIG_PATH\"",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	echo "Starting......: Squid building main configuration done\n";
	$squid=new squidbee();
	$conf=$squid->BuildSquidConf();
	@file_put_contents("/etc/artica-postfix/settings/Daemons/GlobalSquidConf",$conf);
	@file_put_contents($SQUID_CONFIG_PATH,$conf);

}

function retrans(){
	$unix=new unix();
	$array=$unix->getDirectories("/tmp");
	while (list ($num, $ligne) = each ($array) ){
		if(preg_match("#(.+?)\/temporaryFolder\/bases\/av#",$ligne,$re)){
			$folder=$re[1];
		}
	}
	if(is_dir($folder)){
		$cmd=$unix->find_program("du")." -h -s $folder 2>&1";
		exec($cmd,$results);
		$text=trim(implode(" ",$results));
		if(preg_match("#^([0-9\.\,A-Z]+)#",$text,$re)){
			$dbsize=$re[1];
		}
	}else{
		$dbsize="0M";
	}
	
	echo $dbsize;
}







// /etc/init.d/artica-postfix restart squid &



?>