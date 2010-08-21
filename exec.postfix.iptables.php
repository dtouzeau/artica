<?php
$_GET["filelogs"]="/var/log/artica-postfix/iptables.debug";
$_GET["filetime"]="/etc/artica-postfix/croned.1/".basename(__FILE__).".time";
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
include_once(dirname(__FILE__) . '/ressources/class.iptables-chains.inc');
include_once(dirname(__FILE__) . '/ressources/class.baseunix.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');

$GLOBALS["EnablePostfixAutoBlock"]=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/EnablePostfixAutoBlock"));

if($argv[1]=='--compile'){
	
	Compile_rules();
	die();
}

if($argv[1]=='--parse-queue'){
	parsequeue();
	die();
}

if($argv[1]=='--no-check'){
	$_GET["nocheck"]=true;
}


if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

parsequeue();


if($EnablePostfixAutoBlock<>1){
	events("This feature is currently disabled ($EnablePostfixAutoBlock)");
	die();
}


die();
		
//iptables -L OUTPUT --line-numbers		
//iptables -A INPUT -s 65.55.44.100 -p tcp --destination-port 25 -j DROP;


function ArrayIPTables(){
$pattern="#INPUT\s+-s\s(.+?)\/.+?--dport 25.+?ArticaInstantPostfix#";	
$cmd="/sbin/iptables-save > /etc/artica-postfix/iptables.conf"; 
system($cmd);
events("ArrayIPTables:: loading current ipTables list");
$datas=explode("\n",@file_get_contents("/etc/artica-postfix/iptables.conf"));
if(!is_array($datas)){return null;}
while (list ($num, $ligne) = each ($datas) ){
	if(preg_match($pattern,$ligne,$re)){
		$array[$re[1]]=$re[1];
	}else{
		
	}
}
events("ArrayIPTables:: loading current ipTables list ". count($array). " rules");
return $array;


}

function iptables_delete_all(){
events("Exporting datas iptables-save > /etc/artica-postfix/iptables.conf");
system("/sbin/iptables-save > /etc/artica-postfix/iptables.conf");
$data=file_get_contents("/etc/artica-postfix/iptables.conf");
$datas=explode("\n",$data);
$pattern="#.+?ArticaInstantPostfix#";	
while (list ($num, $ligne) = each ($datas) ){
		if($ligne==null){continue;}
		if(preg_match($pattern,$ligne)){continue;}
		events("skip rule $ligne from deletion");
		$conf=$conf . $ligne."\n";
		}

events("restoring datas iptables-restore < /etc/artica-postfix/iptables.new.conf");
file_put_contents("/etc/artica-postfix/iptables.new.conf",$conf);
system("/sbin/iptables-restore < /etc/artica-postfix/iptables.new.conf");


}


function Compile_rules(){
	progress(5,"Cleaning rules");
	iptables_delete_all();
	if($GLOBALS["EnablePostfixAutoBlock"]<>1){
		progress(100,"Building rules done...");
		return;
	}
	events("Query iptables rules from mysql");
	progress(10,"Query rules");
	progress(25,"Building logging rules");
	$sql="SELECT * FROM iptables WHERE disable=0 AND flux='INPUT' and log=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ip=$ligne["serverip"];
		events("LOG {$ligne["serverip"]} REJECT INBOUND PORT 25");
		progress(35,"Building logging rules for $ip");
		$cmd="/sbin/iptables -A INPUT -s $ip -p tcp --destination-port 25 -j LOG --log-prefix \"SMTP DROP: \" -m comment --comment \"ArticaInstantPostfix\"";
		system("$cmd");
		
	}
	progress(40,"Building rules...");
	$sql="SELECT * FROM iptables WHERE disable=0 AND flux='INPUT'";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	progress(55,"Building rules...");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ip=$ligne["serverip"];
		events("ADD REJECT {$ligne["serverip"]} INBOUND PORT 25");
		progress(60,"Building rules for $ip...");
		$cmd="/sbin/iptables -A INPUT -s $ip -p tcp --destination-port 25 -j DROP -m comment --comment \"ArticaInstantPostfix\"";
		system("$cmd");
	}
	
	progress(90,"Building rules done...");
	progress(100,"Building rules done...");
	
	
}

function progress($pourc,$text){
	$file="/usr/share/artica-postfix/ressources/logs/compile.iptables.progress";
	$ini=new Bs_IniHandler();
	$ini->set("PROGRESS","pourc",$pourc);
	$ini->set("PROGRESS","text",$text);
	$ini->saveFile($file);
	chmod($file,0777);
	}



function events($text){
		$pid=getmypid();
		$date=date('Y-m-d H:i:s');
		$logFile=$_GET["filelogs"];
		$size=filesize($logFile);
		if($size>1000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$date [$pid] $text\n");
		@fclose($f);	
		}

		
function load_whitelist(){
$array=array();
	$datas=@file_get_contents('/etc/artica-postfix/settings/Daemons/PostfixAutoBlockWhiteList');
	$tpl=explode("\n",$datas);
	if(is_array($tpl)){
	while (list ($num, $ligne) = each ($tpl) ){
			if($ligne==null){continue;}
			$array[$ligne]=$ligne;
	}}
	
	$sql="SELECT serverip FROM iptables WHERE disable=1 AND flux='INPUT'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");

	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ip=$ligne["serverip"];
		$array[$ip]=$ip;
	}	

return $array;	
}



function parsequeue(){
	
	$q=new mysql();
	$q->Check_iptables_table();
	$ini=new Bs_IniHandler();
	$ini->loadFile('/etc/artica-postfix/settings/Daemons/PostfixAutoBlockResults');	
	
	foreach (glob("/var/log/artica-postfix/smtp-hack/*.hack") as $filename) {
		$basename=basename($filename);
		$array=unserialize(@file_get_contents($filename));
		
		$IP=$array["IP"];
		if($IP=="127.0.0.1"){@unlink($filename);continue;}
		
		$server_name=gethostbyaddr($IP);
		$matches=$array["MATCHES"];
		$EVENTS=$array["EVENTS"];
		$date=$array["DATE"];
		
		if($GLOBALS["VERBOSE"]){echo "$basename: servername:$server_name IP=[$IP]\n";}
		
		$cmd="iptables -A INPUT -s $IP -p tcp --destination-port 25 -j DROP -m comment --comment \"ArticaInstantPostfix\"";
		$iptables=new iptables_chains();
		$iptables->serverip=$IP;
		$iptables->servername=$server_name;
		$iptables->rule_string=$cmd;
		$iptables->EventsToAdd=$EVENTS;
		if($iptables->addPostfix_chain()){
			if($GLOBALS["VERBOSE"]){echo "Add IP:Addr=<$IP>, servername=<{$server_name}> to mysql\n";}
			$ini->set($IP,"events",$matches);
			$ini->set($IP,"iptablerule",$cmd);
			$ini->set($IP,"hostname",$server_name);	
			if($GLOBALS["VERBOSE"]){echo "delete $filename\n";}	
			@unlink($filename);
		}
		
	}
	
	$filestr=$ini->toString();
	file_put_contents("/etc/artica-postfix/settings/Daemons/PostfixAutoBlockResults",$filestr);
	
}

?>