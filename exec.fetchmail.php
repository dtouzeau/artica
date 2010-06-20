<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/ressources/class.fetchmail.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}

if($argv[1]=="--multi-start"){MultiBuildRules();die();}

BuildRules();


function BuildRules(){

		$fetch=new fetchmail();
		$l[]="set logfile /var/log/fetchmail.log";
		$l[]="set daemon $fetch->FetchmailPoolingTime";
		$l[]="set postmaster \"$fetch->FetchmailDaemonPostmaster\"";
		$l[]="set idfile \"/var/log/fetchmail.id\"";	
		$l[]="";

		$sql="SELECT * FROM fetchmail_rules WHERE enabled=1";
		$q=new mysql();
		
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			echo "Starting......: fetchmail saving configuration file FAILED\n";
			return false;
		}
		$array=array();
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$ligne["poll"]=trim($ligne["poll"]);
			if($ligne["poll"]==null){continue;}
			if($ligne["proto"]==null){continue;}
			if($ligne["uid"]==null){continue;}
			$user=new user($ligne["uid"]);
			$ligne["is"]=$user->mail;
			if($ligne["proto"]=="httpp"){$ligne["proto"]="pop3";}
			
			if(trim($ligne["port"])>0){$port="port {$ligne["port"]}";}
			if(trim($ligne["aka"])<>null){$aka="\n\taka {$ligne["aka"]}";}
			if($ligne["ssl"]==1){$ssl="\n\tssl ";}	
			if($ligne["timeout"]>0){$timeout="\n\ttimeout {$ligne["timeout"]}";}
			if($ligne["folder"]<>null){$folder="\n\tfolder {$ligne["folder"]}";}				
			if($ligne["tracepolls"]==1){$tracepolls="\n\ttracepolls";}
			if($ligne["interval"]>0){$interval="\n\\tinterval {$ligne["interval"]}";}		
			if($ligne["keep"]==1){$keep="\n\tkeep ";}
			if($ligne["nokeep"]==1){$keep="\n\tnokeep";}
			if($ligne["multidrop"]==1){$ligne["is"]="*";}
			if($ligne["fetchall"]==1){$fetchall="\n\tfetchall";}
		
		$l[]="poll {$ligne["poll"]}$tracepolls\n\tproto {$ligne["proto"]} $port\n\tuser {$ligne["user"]}\n\tpass {$ligne["pass"]}\n\tis {$ligne["is"]}$aka$folder$ssl$fetchall$interval$timeout$keep$multidrop\n\n";
		}
		if(is_array($l)){$conf=implode("\n",$l);}else{$conf=null;}
		@file_put_contents("/etc/fetchmailrc",$conf);
		@chmod("/etc/fetchmailrc",600);
		echo "Starting......: fetchmail saving configuration file done\n";
			
}

function MultiBuildRules(){
	
	
	
	$sql="SELECT uid FROM fetchmail_rules WHERE enabled=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	
	
	
	
}

function MultiBuildServerArray(){
	$sock=new sockets();
	$config=unserialize(base64_decode($sock->GET_INFO("PostfixMultiFetchMail")));
	if(!is_array($config)){
		echo "Starting......: fetchmail no enabled rules, aborting\n";
		return;	
	}
	
	while (list ($servername, $array) = each ($config) ){
		if($array["enabled"]<>1){continue;}
		$ou=$array["ou"];
		
	}
	
}


?>