<?php
$GLOBALS["KAV4PROXY_NOSESSION"]=true;
if(posix_getuid()<>0){parseTemplate();die();}

include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.groups.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.system.network.inc");
include_once(dirname(__FILE__)."/ressources/class.dansguardian.inc");
include_once(dirname(__FILE__)."/ressources/class.squid.inc");
include_once(dirname(__FILE__)."/ressources/class.squidguard.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;}
$_GET["LOGFILE"]="/var/log/artica-postfix/dansguardian.compile.log";
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

if($argv[1]=="--categories"){build_categories();exit;}
if($argv[2]=="--reload"){$GLOBALS["RELOAD"]=true;}
if($argv[1]=="--build"){build();exit;}
if($argv[1]=="--status"){echo status();exit;}
if($argv[1]=="--compile"){echo compile_databases();exit;}
if($argv[1]=="--db-status"){print_r(databasesStatus());exit;}
if($argv[1]=="--db-status-www"){echo serialize(databasesStatus());exit;}


//http://cri.univ-tlse1.fr/documentations/cache/squidguard.html


function build_categories(){
	
	$unix=new unix();
	$unix->DANSGUARDIAN_CATEGORIES();
	
}

function build(){
	
	$users=new usersMenus();
	$sock=new sockets();
	if(!$users->SQUIDGUARD_INSTALLED){return null;}
	if($sock->GET_INFO("squidGuardEnabled")<>1){return null;}
	
	$s=new squidguard();
	$datas=$s->BuildConf();
	@file_put_contents("/etc/squid/squidGuard.conf",$datas);
	shell_exec($users->SQUID_BIN_PATH." -k reconfigure");
	
	

}


function databasesStatus(){
	$datas=explode("\n",@file_get_contents("/etc/squid/squidGuard.conf"));
	$count=0;
	while (list ($a, $b) = each ($datas)){
		
		if(preg_match("#domainlist.+?(.+)#",$b,$re)){
			$f[]["domainlist"]["path"]="/var/lib/squidguard/{$re[1]}";
			
			continue;
			
		}
		
		if(preg_match("#expressionlist.+?(.+)#",$b,$re)){
			$f[]["expressionlist"]["path"]="/var/lib/squidguard/{$re[1]}";
			
			continue;
		}
		
		if(preg_match("#urllist.+?(.+)#",$b,$re)){
			$f[]["urllist"]["path"]="/var/lib/squidguard/{$re[1]}";
			
			continue;
		}
		
		
	}
	

	
	while (list ($a, $b) = each ($f)){

		$domainlist=$b["domainlist"]["path"];
		$expressionlist=$b["expressionlist"]["path"];
		$urllist=$b["urllist"]["path"];
		
		if(is_file($domainlist)){
			$key="domainlist";
			$path=$domainlist;
		}
		
		if(is_file($expressionlist)){
			$key="expressionlist";
			$path=$expressionlist;
		}

		if(is_file($urllist)){
			$key="urllist";
			$path=$urllist;
		}			
		
		$d=explode("\n",@file_get_contents($path));
		$i[$path]["type"]=$key;
		$i[$path]["size"]=@filesize("$domainlist.db");
		$i[$path]["linesn"]=count($d);
		$i[$path]["date"]=filemtime($path);
		
		
		
		
	}
	
	return $i;
	
}

function status(){
	
	
	$squid=new squidbee();
	$array=$squid->SquidGuardDatabasesStatus();
	$conf[]="[APP_SQUIDGUARD]";
	$conf[]="service_name=APP_SQUIDGUARD";
	
	
	if(is_array($array)){
		$conf[]="running=0";
		$conf[]="why={waiting_database_compilation}<br>{databases}:&nbsp;".count($array);
		return implode("\n",$conf);
		
	}
	
	
	$unix=new unix();
	$users=new usersMenus();
	$pidof=$unix->find_program("pidof");
	exec("$pidof $users->SQUIDGUARD_BIN_PATH",$res);
	$array=explode(" ",implode(" ",$res));
	while (list ($index, $line) = each ($array)){
		if(preg_match("#([0-9]+)#",$line,$ri)){
			$pid=$ri[1];
			$inistance=$inistance+1;
			$mem=$mem+$unix->MEMORY_OF($pid);
			$ppid=$unix->PPID_OF($pid);
		}
	}
	$conf[]="running=1";
	$conf[]="master_memory=$mem";
	$conf[]="master_pid=$ppid";
	$conf[]="other={processes}:$inistance"; 
	return implode("\n",$conf);
	
}




function compile_databases(){
	$users=new usersMenus();
	$squid=new squidbee();
	$array=$squid->SquidGuardDatabasesStatus();
	$verb=" -d";
	echo "Starting......: squidGuard compiling ". count($array)." databases\n";
		while (list ($index, $file) = each ($array)){
			$file=str_replace(".db",'',$file);
			$textfile=str_replace("/var/lib/squidguard/","",$file);
			echo "Starting......: squidGuard compiling $textfile database ".($index+1) ."/". count($array)."\n";
			if($GLOBALS["VERBOSE"]){$verb=" -d";echo $users->SQUIDGUARD_BIN_PATH." -P$verb -C $file\n";}
			system($users->SQUIDGUARD_BIN_PATH." -P$verb -C $file");
		}
		
 	system(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.squid.php --build");
	build();
 
 
}

function parseTemplate(){
	include_once(dirname(__FILE__)."/ressources/class.sockets.inc");
	$sock=new sockets();
	$template=$sock->GET_INFO("DansGuardianHTMLTemplate");
	if(preg_match("#<body>(.+?)</body>#is",$template,$re)){$template=$re[1];}
	
	
	$template=str_replace("-USER-",$_GET["clientname"],$template);
	$template=str_replace("-URL-",$_GET["url"],$template);
	$template=str_replace("-IP-",$_GET["clientaddr"],$template);
	$template=str_replace("-REASONGIVEN-",$_GET["targetgroup"],$template);
	$template=str_replace("-REASONLOGGED-",$_GET["clientgroup"],$template);

	echo "
	<html>
	<head>
	<title>{$_GET["clientname"]}::{$_GET["clientaddr"]}::{$_GET["targetgroup"]}</title>
	</head>
	<body>
	<center>
	$template
	</center>
	</body>
	</html>
	";
	
	
	
}


?>