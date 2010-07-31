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
if($argv[1]=='--restart-all'){restart_all_instances();die();}


$sock=new sockets();
$GLOBALS["EnablePostfixMultiInstance"]=$sock->GET_INFO("EnablePostfixMultiInstance");
if($GLOBALS["EnablePostfixMultiInstance"]<>1){
		echo "Starting......: Multi-instances is not enabled ({$GLOBALS["EnablePostfixMultiInstance"]})\n";
		PostfixMultiDisable();
		die();
}
$unix=new unix();

writelogs("receive ". implode(",",$argv),"MAIN",__FILE__,__LINE__);

$GLOBALS["postmulti"]=$unix->find_program("postmulti");
$GLOBALS["postconf"]=$unix->find_program("postconf");
$GLOBALS["postmap"]=$unix->find_program("postmap");
$GLOBALS["postalias"]=$unix->find_program("postalias");

if($argv[1]=='--removes'){PostfixMultiDisable();die();}
if($argv[1]=='--instance-reconfigure'){reconfigure_instance($argv[2]);die();}
if($argv[1]=='--instance-relayhost'){reconfigure_instance_relayhost($argv[2]);die();}
if($argv[1]=='--instance-ssl'){reconfigure_instance_ssl($argv[2]);die();}
if($argv[1]=='--instance-settings'){reconfigure_instance_minimal($argv[2]);die();}
if($argv[1]=='--instance-mastercf'){reconfigure_instance_mastercf($argv[2]);die();}
if($argv[1]=='--clean'){remove_old_instances();die();}
if($argv[1]=='--mime-header-checks'){reconfigure_instance_mime_checks($argv[2]);die();}
if($argv[1]=='--from-main-maincf'){die();}

reconfigure();

function restart_all_instances(){
	$unix=new unix();
	$postfix=$unix->find_program("postfix");
	$GLOBALS["postmulti"]=$unix->find_program("postmulti");
	echo "Starting......: Stopping master instance\n";
	system("$postfix stop");
	if($sock->GET_INFO("EnablePostfixMultiInstance")==1){
		$main=new maincf_multi(null);
		$main->PostfixMainCfDefaultInstance();
	}	
	
	echo "Starting......: checking first instance security\n";
	system("$postfix -c /etc/postfix set-permissions");
	$sock=new sockets();
	
	if($sock->GET_INFO("EnablePostfixMultiInstance")==1){
		echo "Starting......: checking all instances security\n";
		MysqlInstancesList();
		if(is_array($GLOBALS["INSTANCES_LIST"])){
			while (list ($num, $ligne) = each ($GLOBALS["INSTANCES_LIST"]) ){
				echo "Starting......: checking instance $ligne security\n";
				system("$postfix -c /etc/postfix-$ligne set-permissions");
			}
		}
		

		
		echo "Starting......: Starting master\n";
		system("$postfix stop");
		system("$postfix start");
		reset($GLOBALS["INSTANCES_LIST"]);
		while (list ($num, $hostname) = each ($GLOBALS["INSTANCES_LIST"]) ){
			_start_instance($hostname);
		}
		
	
	}else{
		echo "Starting......: Starting master\n";
		system("$postfix start");
	}
	
}



function reconfigure(){
	echo "Starting......: Enable Postfix multi-instances\n";
	shell_exec("{$GLOBALS["postmulti"]} -e init >/dev/null 2>&1");	
	InstancesList();
	remove_old_instances();
	CheckInstances();
	
}


function InstancesList(){
	$unix=new unix();
	if($GLOBALS["postmulti"]==null){
		$GLOBALS["postmulti"]=$unix->find_program("postmulti");
	}
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

function MysqlInstancesList(){
		$sql="SELECT `value` FROM postfix_multi WHERE `key`='myhostname' GROUP BY `value`";	
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo "Starting......: Postfix error $q->mysql_error\n";}
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			$myhostname=trim($ligne["value"]);
			if($myhostname==null){continue;}
			$GLOBALS["INSTANCES_LIST"][]=$myhostname;
		}	
	
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
	writelogs("reconfigure instance $hostname",__FUNCTION__,__FILE__,__LINE__);
	echo "Starting......: Postfix checking instance $hostname\n";
	$instance_path="/etc/postfix-$hostname";	
	$maincf=new maincf_multi($hostname);
	$maincf->buildconf();	
	writelogs("Building configuration done",__FUNCTION__,__FILE__,__LINE__);
	_start_instance($hostname);
	
	
}

function reconfigure_instance_relayhost($hostname){
	$maincf=new maincf_multi($hostname);
	$maincf->buildconf();	
	$maincf->CheckDirectories($hostname);
	$maincf->postmap_relayhost();
	_start_instance($hostname);
}
	

function reconfigure_instance_ssl($hostname){
	$maincf=new maincf_multi($hostname);
	$maincf->certificate_generate();
	$maincf->buildconf();	
	
	
	echo "Starting......: restarting Postfix {$GLOBALS["postmulti"]} -i postfix-$hostname -p stop\n";		
	shell_exec("{$GLOBALS["postmulti"]} -i postfix-$hostname -p stop");
	shell_exec("{$GLOBALS["postmulti"]} -i postfix-$hostname -p start");
	
}

function reconfigure_instance_minimal($hostname){
	$maincf=new maincf_multi($hostname);
	$maincf->buildconf();	
	echo "Starting......: Postfix {$GLOBALS["postmulti"]} -i postfix-$hostname -p reload\n";		
	shell_exec("{$GLOBALS["postmulti"]} -i postfix-$hostname -p reload");		
}
function reconfigure_instance_mastercf($hostname){
	$maincf=new maincf_multi($hostname);
	$maincf->buildmaster();
	$sock=new sockets();
	echo "Starting......: restarting Postfix {$GLOBALS["postmulti"]} -i postfix-$hostname -p stop\n";		
	shell_exec("{$GLOBALS["postmulti"]} -i postfix-$hostname -p stop");
	shell_exec("{$GLOBALS["postmulti"]} -i postfix-$hostname -p start");	
	$sock->getFrameWork("cmd.php?amavis-restart=yes");
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
	reconfigure_instance_mime_checks($myhostname);
	$maincf->buildconf();
	$assp=new assp_multi($ou);
	if($assp->AsspEnabled==1){
		shell_exec(LOCATE_PHP5_BIN2()." ". dirname(__FILE__)."/exec.assp-multi.php --org \"$ou\"");
	}
	
	shell_exec("{$GLOBALS["postmulti"]} -i postfix-$hostname -e enable");
	_start_instance($hostname);
}

function isInstanceRunning($hostname){
	$pidfile="/var/spool/postfix-$hostname/pid/master.pid";
	$unix=new unix();	
	$pid=$unix->get_pid_from_file($pidfile);
	if($unix->process_exists($pid)){return true;}
	return false;	
	
}

function _start_instance($hostname){
	if(trim($hostname)==null){return;}
	$pidfile="/var/spool/postfix-$hostname/pid/master.pid";
	$unix=new unix();
	if($GLOBALS["postmulti"]==null){$GLOBALS["postmulti"]=$unix->find_program("postmulti");}
	$pid=$unix->get_pid_from_file($pidfile);
	$main=new maincf_multi();
	writelogs("$hostname:: Checking directories",__FUNCTION__,__FILE__,__LINE__);
	$main->CheckDirectories($hostname);
	writelogs("$hostname:: $pidfile=$pid",__FUNCTION__,__FILE__,__LINE__);
	
	if($unix->process_exists($pid)){
		echo "Starting......: Postfix reloading \"$hostname\"\n";
		writelogs("$hostname::reloading postfix {$GLOBALS["postmulti"]} -i postfix-$hostname -p reload",__FUNCTION__,__FILE__,__LINE__);
		exec("{$GLOBALS["postmulti"]} -i postfix-$hostname -p reload 2>&1",$results);
		while (list ($num, $line) = each ($results) ){
			writelogs("$line",__FUNCTION__,__FILE__,__LINE__);
			echo "Starting......: Postfix \"$instance\" $line\n";
			
			if(preg_match("#fatal: open /etc/postfix-(.+?)\/main\.cf#",$line,$re)){
				echo "Starting......: Postfix reconfigure \"{$re[1]}\"\n";
				reconfigure_instance($re[1]);
			}
			
		}
		
		return;
	}	
	
	echo "Starting......: Postfix starting \"$hostname\"\n";
	writelogs("$hostname::Starting postfix {$GLOBALS["postmulti"]} -i postfix-$hostname -p start",__FUNCTION__,__FILE__,__LINE__);
	exec("{$GLOBALS["postmulti"]} -i postfix-$hostname -p start 2>&1",$results);
	writelogs("$hostname::Starting LOG=".count($results)." lines",__FUNCTION__,__FILE__,__LINE__);
	
		while (list ($num, $line) = each ($results) ){
			writelogs("$line",__FUNCTION__,__FILE__,__LINE__);
			echo "Starting......: Postfix \"$hostname\" $line\n";
			if(preg_match("#fatal: open /etc/postfix-(.+?)\/main\.cf#",$line,$re)){
				echo "Starting......: Postfix reconfigure \"{$re[1]}\"\n";
				reconfigure_instance($re[1]);
			}			
	}

	
	$pid=$unix->get_pid_from_file($pidfile);
	for($i=0;$i<10;$i++){
		if($unix->process_exists($pid)){break;}
		echo "Starting......: Postfix \"$hostname\" waiting run ($pid)\n";
		sleep(1);
	}
	
	
	if($unix->process_exists($pid)){
		echo "Starting......: Postfix \"$hostname\" SUCCESS with PID=$pid\n";
		writelogs("$hostname::DONE",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	echo "Starting......: Postfix \"$hostname\" FAILED\n";
	writelogs("$hostname::FAILED",__FUNCTION__,__FILE__,__LINE__);
	
	
	
	
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

function remove_old_instances(){
	
		$sql="SELECT `value` FROM postfix_multi WHERE `key`='myhostname' GROUP BY `value`";
		$restart=false;
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			$array[$ligne["value"]]=true;
		}
	
	
	foreach (glob("/etc/postfix-*",GLOB_ONLYDIR) as $dirname) {
		if(preg_match("#postfix-(.+)#",$dirname,$re)){
			$hostname=trim($re[1]);
			if($hostname==null){continue;}
			if($hostname=="hub"){continue;}
			if(!$array[$hostname]){
				$restart=true;
				echo "Starting......: Postfix remove old instance $hostname\n";
				shell_exec("/bin/rm -rf /etc/postfix-$hostname");
				shell_exec("/bin/rm -rf /var/lib/postfix-$hostname");
				shell_exec("/bin/rm -rf /var/spool/postfix-$hostname");
			}
				
		}
	
	}
	
	if($restart){shell_exec("/etc/init.d/artica-postfix stop postfix");}
	
}


function reconfigure_instance_mime_checks($hostname){
	
	$unix=new unix();
	$users=new usersMenus();
	$postconf=$unix->find_program("postconf");
	$postmulti=$unix->find_program("postmulti");	
	
	if($users->AMAVIS_INSTALLED){
		$main=new maincf_multi($hostname);
		$array_filters=unserialize(base64_decode($main->GET_BIGDATA("PluginsEnabled")));
		if($array_filters["APP_AMAVIS"]==1){
			@unlink("/etc/postfix-$hostname/mime_header_checks");
			shell_exec("$postconf -c \"/etc/postfix-$hostname\" -e \"mime_header_checks = \"");
			system("/usr/share/artica-postfix/bin/artica-install --amavis-reload");
			_start_instance($hostname);
			return;
		}
	}
	
	
	
	
	$sql="SELECT * FROM smtp_attachments_blocking WHERE ou='{$_GET["ou"]}' AND hostname='$hostname' ORDER BY IncludeByName";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["IncludeByName"]==null){continue;}
			$f[]=$ligne["IncludeByName"];
		
	}
	if(!is_array($f)){
		@unlink("/etc/postfix-$hostname/mime_header_checks");
		shell_exec("$postconf -c \"/etc/postfix-$hostname\" -e \"mime_header_checks = \"");
		_start_instance($hostname);
		return;
	}
	
	$strings=implode("|",$f);
	echo "Starting......: Postfix \"$hostname\" ". count($f)." extensions blocked\n";
	$pattern[]="/^\s*Content-(Disposition|Type).*name\s*=\s*\"?(.+\.($strings))\"?\s*$/\tREJECT file attachment types is not allowed. File \"$2\" has the unacceptable extension \"$3\"";	
	$pattern[]="";
	@file_put_contents("/etc/postfix-$hostname/mime_header_checks",implode("\n",$pattern));	
	shell_exec("$postconf -c \"/etc/postfix-$hostname\" -e \"mime_header_checks = regexp:/etc/postfix-$hostname/mime_header_checks\"");
	
}







?>