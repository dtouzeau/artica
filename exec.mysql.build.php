<?php

include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql-server.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");

cpulimit();
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

if($argv[1]=='--tables'){$mysql=new mysql();$mysql->BuildTables();die();}
if($argv[1]=='--imapsync'){rebuild_imapsync();die();}
if($argv[1]=='--rebuild-zarafa'){rebuild_zarafa();die();}

$q=new mysqlserver();

$unix=new unix();
$mem=$unix->TOTAL_MEMORY_MB();
echo "Starting......: Mysql my.cnf........: Total memory {$mem}MB\n";

if($mem<550){
	echo "Starting......: Mysql my.cnf........: SWITCH TO LOWER CONFIG.\n";
	$datas=$q->Mysql_low_config();
}else{
	$datas=$q->BuildConf();
}

if(!is_file($argv[1])){echo "Starting......: Mysql my.cnf........: unable to stat {$argv[1]}\n";die();}

@file_put_contents($argv[1],$datas);
echo "Starting......: Mysql my.cnf........: Updating \"{$argv[1]}\" success ". strlen($datas)." bytes\n";


function rebuild_imapsync(){
	$q=new mysql();
	writelogs("DELETE imapsync table...",__FUNCTION__,__FILE__,__LINE__);
	$sql="DROP TABLE `imapsync`";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$sql:: $q->mysql_error\n";}
	writelogs("Rebuild tables",__FUNCTION__,__FILE__,__LINE__);
	$q->BuildTables();
	}
	
function rebuild_zarafa(){
	$q=new mysql();
	$q->DELETE_DATABASE("zarafa");
	shell_exec("/etc/init.d/artica-postfix restart zarafa");
	}

?>