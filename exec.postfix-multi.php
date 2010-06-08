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


$_GET["LOGFILE"]="/usr/share/artica-postfix/ressources/logs/web/interface-postfix.log";
if(!is_file("/usr/share/artica-postfix/ressources/settings.inc")){shell_exec("/usr/share/artica-postfix/bin/process1 --force --verbose");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}
$sock=new sockets();
$GLOBALS["EnablePostfixMultiInstance"]=$sock->GET_INFO("EnablePostfixMultiInstance");
$unix=new unix();
$GLOBALS["postmulti"]=$unix->find_program("postmulti");
$GLOBALS["postconf"]=$unix->find_program("postconf");

if($argv[1]=='--removes'){PostfixMultiDisable();die();}
if($GLOBALS["EnablePostfixMultiInstance"]<>1){
	echo "Starting......: Multi-instances is not enabled ({$GLOBALS["EnablePostfixMultiInstance"]})\n";
	die();
}
main_inet_interface();
InstancesList();
running();
virtuals();

if($argv[1]=='--virtuals'){virtuals();die();}
if($argv[1]=='--org'){ConfigureMainCF($argv[2]);die();}


reconfigure();


function reconfigure(){
	
	
	echo "Starting......: Enable Postfix multi-instances\n";
	shell_exec("{$GLOBALS["postmulti"]} -e init >/dev/null 2>&1");
	if(!$GLOBALS["INSTANCE"]["postfix-hub"]){
		echo "Starting......: Activate Postfix HUB\n";
		shell_exec("{$GLOBALS["postmulti"]} -I postfix-hub -G hub -e create >/dev/null 2>&1");
	}
	shell_exec("{$GLOBALS["postmulti"]} -i postfix-hub -e enable >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e inet_interfaces = 127.0.0.1 >/dev/null 2>&1");
	CheckOU();
	
}


function InstancesList(){
	$unix=new unix();
	exec("{$GLOBALS["postmulti"]} -l -a",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#^(.+?)\s+#",$ligne,$re)){
			$GLOBALS["INSTANCE"][$re[1]]=true;
		}
	}
	$tmpstr=$unix->FILE_TEMP();
	shell_exec("{$GLOBALS["postmulti"]} -p status >$tmpstr 2>&1");
	echo @file_get_contents($tmpstr);
	

	
}

function CheckOU(){
	$ldap=new clladp();
	$hash=$ldap->hash_get_ou();
	while (list ($num, $ou) = each ($hash) ){
		$ou=str_replace(" ","-",$ou);
		if(!$GLOBALS["INSTANCE"]["postfix-$ou"]){
			echo "Starting......: Postfix activate HUB for $ou\n";
			shell_exec("{$GLOBALS["postmulti"]} -I postfix-$ou -G orgs -e create");
			
		}
	}
	
	reset($hash);
	while (list ($num, $ou) = each ($hash) ){
		
		ConfigureMainCF($ou);
		
		$instance=str_replace(" ","-",$ou);
		echo "Starting......: Postfix activate instance for $ou ip={$GLOBALS["IP"]["postfix-$instance"]}, running={$GLOBALS["running"]["postfix-$instance"]}\n";
		shell_exec("{$GLOBALS["postmulti"]} -i postfix-$instance -e enable");
		if($GLOBALS["IP"]["postfix-$instance"]==null){
			shell_exec("{$GLOBALS["postmulti"]} -i postfix-$instance -x postconf -e master_service_disable=\"inet\"");
		}else{
			shell_exec("{$GLOBALS["postmulti"]} -i postfix-$instance -x postconf -e master_service_disable=\"\"");
			shell_exec("{$GLOBALS["postmulti"]} -i postfix-$instance -x postconf -e inet_interfaces=\"{$GLOBALS["IP"]["postfix-$instance"]}\"");
		}
		if(!$GLOBALS["running"]["postfix-$instance"]){
			shell_exec("{$GLOBALS["postmulti"]} -i postfix-$instance -p start");
		}else{
			shell_exec("{$GLOBALS["postmulti"]} -i postfix-$instance -p reload");
		}
		
		
		
	}	
	
	
}

function running(){
	if($GLOBALS["DEBUG"]){echo "running() function\n";}
	$ldap=new clladp();
	$unix=new unix();
	$hash=$ldap->hash_get_ou();
	while (list ($num, $ou) = each ($hash) ){
		$ou=str_replace(" ","-",$ou);
		if($GLOBALS["DEBUG"]){echo "unix->POSTFIX_MULTI_PID($ou)\n";}
		$pid=$unix->POSTFIX_MULTI_PID($ou);
		if(!is_file("/proc/$pid/exe")){continue;}
		$GLOBALS["running"]["postfix-$ou"]=true;
	}
}

function virtuals(){
	$q=new mysql();
	$net=new networking();
	$sql="SELECT * FROM nics_virtuals ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$ou=$ligne["org"];
		if($ou==null){continue;}
		$ou=str_replace(" ","-",$ou);
		if($ligne["ipaddr"]==null){
			if($ligne["nic"]<>null){
				$ligne["ipaddr"]=$net->array_TCP[$ligne["nic"]];
			}
		}
		$GLOBALS["IP"]["postfix-$ou"]=$ligne["ipaddr"];
	}
}

function main_inet_interface(){
	$unix=new unix();
	$inet_interfaces=$unix->POSTCONF_GET("inet_interfaces");
	if($inet_interfaces=="127.0.0.1"){return;}
	if($inet_interfaces=="loopback-only"){return;}
		echo "Starting......: Postfix change to loopback for the main instance (currently $inet_interfaces)\n";
		$unix->POSTCONF_SET("inet_interfaces","loopback-only");
		$postfix=$unix->find_program("postfix");
		shell_exec("$postfix stop");
		shell_exec("$postfix start");
	
}


function ConfigureMainCF($ou){
	$instance=str_replace(" ","-",$ou);
	$users=new usersMenus();
	$unix=new unix();
	echo "Starting......: Postfix checkign organization $ou\n";
	$main=new main_multi($ou);
	
	
	if($users->cyrus_imapd_installed){
		echo "Starting......: Postfix change $ou cyrus is installed\n";
		$unix->POSTCONF_MULTI_SET($instance,"mailbox_transport","lmtp:unix:$users->cyrus_lmtp_path");
		$unix->POSTCONF_MULTI_SET($instance,"virtual_transport","\$mailbox_transport");
	}else{
		$unix->POSTCONF_MULTI_SET($instance,"mailbox_transport","");
		$unix->POSTCONF_MULTI_SET($instance,"virtual_transport","\$mailbox_transport");
	}
	
	$assp=new assp_multi($ou);
	if($assp->AsspEnabled==1){
		shell_exec(LOCATE_PHP5_BIN2()." ". dirname(__FILE__)."/exec.assp-multi.php --org \"$ou\"");
	}
	
	shell_exec("{$GLOBALS["postmulti"]} -i postfix-$instance -e enable");
if(!$GLOBALS["running"]["postfix-$instance"]){
			echo "Starting......: Postfix start postfix-$instance\n";
			shell_exec("{$GLOBALS["postmulti"]} -i postfix-$instance -p start");
			
		}else{
			echo "Starting......: Postfix restart postfix-$instance\n";
			shell_exec("{$GLOBALS["postmulti"]} -i postfix-$instance -p stop");
			shell_exec("{$GLOBALS["postmulti"]} -i postfix-$instance -p start");
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
	shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.postfix.maincf.php --reconfigure");
	shell_exec($unix->find_program("postfix")." start");
	
}






?>