<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__) . '/ressources/class.system.network.inc');
include_once(dirname(__FILE__) . '/ressources/class.postfix-multi.inc');
include_once(dirname(__FILE__) . '/ressources/class.main_cf.inc');
include_once(dirname(__FILE__) . '/ressources/class.assp-multi.inc');
include_once(dirname(__FILE__) . '/ressources/class.maincf.multi.inc');


$_GET["LOGFILE"]="/usr/share/artica-postfix/ressources/logs/web/interface-postfix.log";
if(!is_file("/usr/share/artica-postfix/ressources/settings.inc")){shell_exec("/usr/share/artica-postfix/bin/process1 --force --verbose");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}
$sock=new sockets();
$GLOBALS["EnablePostfixMultiInstance"]=$sock->GET_INFO("EnablePostfixMultiInstance");
if($GLOBALS["EnablePostfixMultiInstance"]<>1){echo "Starting......: Multi-instances is not enabled ({$GLOBALS["EnablePostfixMultiInstance"]})\n";PostfixMultiDisable();die();}
$unix=new unix();
$GLOBALS["postmulti"]=$unix->find_program("postmulti");
$GLOBALS["postconf"]=$unix->find_program("postconf");
$GLOBALS["postmap"]=$unix->find_program("postmap");

if($argv[1]=='--removes'){PostfixMultiDisable();die();}
if($argv[1]=='--instance-reconfigure'){reconfigure_instance($argv[2]);die();}
if($argv[1]=='--instance-relayhost'){reconfigure_instance_relayhost($argv[2]);die();}

reconfigure();


function reconfigure(){
	echo "Starting......: Enable Postfix multi-instances\n";
	shell_exec("{$GLOBALS["postmulti"]} -e init >/dev/null 2>&1");	
	InstancesList();
	CheckInstances();
}


function InstancesList(){
	$unix=new unix();
	if(is_dir("/etc/postfix-hub")){
		if(!is_file("/etc/postfix-hub/dynamicmaps.cf")){@file_put_contents("/etc/postfix-hub/dynamicmaps.cf","#");}
	}
	exec("{$GLOBALS["postmulti"]} -l -a",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#^(.+?)\s+#",$ligne,$re)){
			$re[1]=trim($re[1]);
			if($re[1]=='-'){continue;}
			echo "Starting......: Detecting instance {$re[1]}\n";
			$GLOBALS["INSTANCE"][$re[1]]=true;
		}
	}
	$tmpstr=$unix->FILE_TEMP();
	shell_exec("{$GLOBALS["postmulti"]} -p status >$tmpstr 2>&1");
	echo @file_get_contents($tmpstr);
	

	
}

function CheckInstances(){
		$maincf=new maincf_multi("");
		$maincf->PostfixMainCfDefaultInstance();
		$sql="SELECT `value` FROM postfix_multi WHERE `key`='myhostname' GROUP BY `value`";
		echo "Starting......: Postfix activate HUB(s)\n";

		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			$myhostname=trim($ligne["value"]);
			if($myhostname==null){continue;}
			echo "Starting......: Postfix checking HUB $myhostname\n";
			ConfigureMainCF($myhostname);
		}
	
}



function reconfigure_instance($hostname){
	$users=new usersMenus();
	$unix=new unix();
	echo "Starting......: Postfix checking instance $hostname\n";
	$instance_path="/etc/postfix-$hostname";	
	$maincf=new maincf_multi($hostname);
	$maincf->buildconf();	
	echo "Starting......: Postfix {$GLOBALS["postmulti"]} -i postfix-$hostname -p reload\n";
	shell_exec("{$GLOBALS["postmulti"]} -i postfix-$hostname -p reload");
	
}

function reconfigure_instance_relayhost($hostname){
	$maincf=new maincf_multi($hostname);
	$maincf->buildconf();	
	$maincf->postmap_relayhost();
	echo "Starting......: Postfix {$GLOBALS["postmulti"]} -i postfix-$hostname -p reload\n";		
	shell_exec("{$GLOBALS["postmulti"]} -i postfix-$hostname -p reload");
}


function ConfigureMainCF($hostname){
	if(strlen(trim($hostname))<3){return null;}
	$users=new usersMenus();
	$unix=new unix();
	echo "Starting......: Postfix checking instance $hostname\n";
	

	
	$instance_path="/etc/postfix-$hostname";
	if(!is_file("$instance_path/dynamicmaps.cf")){
		echo "Starting......: Postfix $hostname creating dynamicmaps.cf\n";
		@file_put_contents("$instance_path/dynamicmaps.cf","#");
	}
	
	
	$maincf=new maincf_multi($hostname);
	$maincf->buildconf();
	$assp=new assp_multi($ou);
	if($assp->AsspEnabled==1){
		shell_exec(LOCATE_PHP5_BIN2()." ". dirname(__FILE__)."/exec.assp-multi.php --org \"$ou\"");
	}
	

	

                                    
	
	
	
	shell_exec("{$GLOBALS["postmulti"]} -i postfix-$hostname -e enable");
	if(!$GLOBALS["running"]["postfix-$instance"]){
			echo "Starting......: Postfix start postfix-$hostname\n";
			shell_exec("{$GLOBALS["postmulti"]} -i postfix-$hostname -p start");
			
		}else{
			echo "Starting......: Postfix restart postfix-$hostname\n";
			shell_exec("{$GLOBALS["postmulti"]} -i postfix-$hostname -p stop");
			shell_exec("{$GLOBALS["postmulti"]} -i postfix-$hostname -p start");
		}

//ConfigureMainMaster();		
		
}

function ConfigureMainMaster(){
	$main=new main_cf();
	$main->save_conf_to_server(1);
	if(!is_file("/etc/postfix/hash_files/header_checks.cf")){@file_put_contents("/etc/postfix/hash_files/header_checks.cf","#");}
	file_put_contents('/etc/postfix/main.cf',$main->main_cf_datas);
	$unix=new unix();
	$postfix=$unix->find_program("postfix");
	shell_exec("$postfix reload");
	}
	
function PostfixMultiDisable(){
	InstancesList();
	while (list ($instance, $ou) = each ($GLOBALS["INSTANCE"]) ){
		if($instance==null){continue;}
		if($instance=="-"){continue;}
		echo "Starting......: Postfix destroy \"$instance\"\n";
		shell_exec("{$GLOBALS["postmulti"]} -i $instance -p stop");
		shell_exec("{$GLOBALS["postmulti"]} -i $instance -e disable");
		shell_exec("{$GLOBALS["postmulti"]} -i $instance -e destroy");
	}
	
	$unix=new unix();
	$unix->POSTCONF_SET("multi_instance_enable","no");
	$unix->POSTCONF_SET("inet_interfaces","all");
	$unix->POSTCONF_SET("multi_instance_directories","");
	system(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.postfix.maincf.php --reconfigure");
	
	
}






?>