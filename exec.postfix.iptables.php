<?php
$_GET["filelogs"]="/var/log/artica-postfix/iptables.debug";
$_GET["filetime"]="/etc/artica-postfix/croned.1/".basename(__FILE__).".time";
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
include_once(dirname(__FILE__) . '/ressources/class.iptables-chains.inc');
include_once(dirname(__FILE__) . '/ressources/class.baseunix.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
cpulimit();

if($argv[1]=='--compile'){
	Compile_rules();
	die();
}

if($argv[1]=='--no-check'){
	$_GET["nocheck"]=true;
}



include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

$EnablePostfixAutoBlock=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/EnablePostfixAutoBlock"));
if($EnablePostfixAutoBlock<>1){
	events("This feature is currently disabled ($EnablePostfixAutoBlock)");
	die();
}


$ini=new Bs_IniHandler("/etc/artica-postfix/settings/Daemons/PostfixAutoBlockParameters");
if($ini->_params["CONF"]["PostfixAutoBlockEvents"]==null){$ini->_params["CONF"]["PostfixAutoBlockEvents"]=100;}
if($ini->_params["CONF"]["PostfixAutoBlockDays"]==null){$ini->_params["CONF"]["PostfixAutoBlockDays"]=2;}
if($ini->_params["CONF"]["PostfixAutoBlockEvents"]<10){$ini->_params["CONF"]["PostfixAutoBlockEvents"]=10;}
if($ini->_params["CONF"]["PostfixAutoBlockPeriod"]==null){$ini->_params["CONF"]["PostfixAutoBlockPeriod"]=240;}

$PostfixAutoBlockPeriod=$ini->_params["CONF"]["PostfixAutoBlockPeriod"];
$f=new baseunix();
if(is_file($_GET["filetime"])){
	$ftime=$f->file_time_min($_GET["filetime"]);
	if($ftime<$PostfixAutoBlockPeriod){
		events("It is not time to run ($ftime mn, needs {$PostfixAutoBlockPeriod}mn) for {$_GET["filetime"]}..");
		if(!$_GET["nocheck"]){die();}
	}
}

events("Loading white and black lists....");
$whitelist=load_whitelist();
$iptables=ArrayIPTables();
events(count($whitelist). " IP whitelist & ".count($iptables). " iptables SMTP inbound rules");


$q=new mysql();
$date=date('Y-m-d H:i:s');


$sql="SELECT COUNT( ID ) AS tcount, smtp_sender, DATE_FORMAT( time_stamp, '%W %D' ) AS tdate, SPAM
FROM smtp_logs
GROUP BY smtp_sender
HAVING COUNT( ID ) >{$ini->_params["CONF"]["PostfixAutoBlockEvents"]}
AND (SPAM =1 OR bounce_error='Discard' OR bounce_error='RBL' OR bounce_error='hostname not found' OR bounce_error='Domain not found')
AND (
tdate > DATE_ADD( '$date', INTERVAL -{$ini->_params["CONF"]["PostfixAutoBlockDays"]}
DAY )
)
ORDER BY tcount DESC";


$sql="SELECT smtp_sender,bounce_error, DATE_FORMAT( time_stamp, '%W %D' ) AS tdate, SPAM FROM smtp_logs
WHERE time_stamp > DATE_ADD( '$date', INTERVAL -{$ini->_params["CONF"]["PostfixAutoBlockDays"]} DAY )
AND (SPAM =1 OR bounce_error='Discard' OR bounce_error='RBL' OR bounce_error='hostname not found' OR bounce_error='Domain not found')
";

events("Starting query:".$sql);

$resultats=$q->QUERY_SQL($sql,"artica_events");
if(!$q->ok){
	events($q->mysql_error);
}

events("Calculating IP addresses query..;");

while($ligne=mysql_fetch_array($resultats,MYSQL_ASSOC)){
	$ipaddr=trim($ligne["smtp_sender"]);
	if($ipaddr==null){continue;}
	$array[$ipaddr]=$array[$ipaddr]+1;
	
}

$max_rows=count($array);
$PostfixAutoBlockEvents=$ini->_params["CONF"]["PostfixAutoBlockEvents"];
if($PostfixAutoBlockEvents<10){$PostfixAutoBlockEvents=10;}

events("$max_rows IP addresses matches search for events reach more than $PostfixAutoBlockEvents events");

$count=0;
while (list ($ip, $count_events) = each ($array) ){
	if($count_events>$PostfixAutoBlockEvents){
		events("$ip - $count/$max_rows - found $count_events factors");
		if(BlockIP($ip,$iptables,$whitelist,$count_events)){$count=$count+1;}
	}
	
	
}		
	
if($count>0){SendNotification($count);}
ParseResultsConfig();
@unlink($_GET["filetime"]);
file_put_contents($_GET["filetime"],'#');
die();

		
//iptables -L OUTPUT --line-numbers		
//iptables -A INPUT -s 65.55.44.100 -p tcp --destination-port 25 -j DROP;

function BlockIP($ip,$iptables,$whitelist,$events){
$ip=trim($ip);	
$ini=new Bs_IniHandler("/etc/artica-postfix/settings/Daemons/PostfixAutoBlockResults");	

			if($ip==null){return null;}
			$server_name=gethostbyaddr($ip);
			if($whitelist[$ip]<>null){return false;}
			if($whitelist[$server_name]<>null){return false;}
			if($ip=="127.0.0.1"){return false;}
			if($iptables[$ip]>0){return false;}
			if($iptables[$server_name]>0){return false;}
			
			$count_events=$events;
			$ini->set($ip,"hostname",$server_name);
			$ini->set($ip,"events",$count_events);
			
			
			events("Adding $ip ($server_name) into iptables ({$iptables[$ip]}/{$iptables[$server_name]})");
			$cmd="iptables -A INPUT -s $ip -p tcp --destination-port 25 -j DROP -m comment --comment \"ArticaInstantPostfix\"";
			$ini->set($ip,"iptablerule",$cmd);
			system("/sbin/$cmd");
			$ini->saveFile("/etc/artica-postfix/settings/Daemons/PostfixAutoBlockResults");
			return true;
	
}
	
		
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

function SendNotification($count_new_rules){
$array=ArrayIPTables();
if(!is_array($array)){return null;}	
$rulenumber=count($array);
$html="

<H2>$rulenumber spammers IP blocked</H2><p>
here it is spammers detected and rules added by Artica into the local firewall...</p>

<table style='width:100%'>
<tr>
	<th><strong>Rule Number</strong></th>
	<th><strong>Server IP/address blocked</th>
</tr>";

while (list ($num, $ligne) = each ($array) ){
	$html=$html . "<tr>
		<td width=1% nowrap><strong>Rule $ligne</strong></td>
		<td><strong><code>$num</code></td>
		</tr>
		<tr><td colspan=2><hr></td></tr>
		";
	
}

$html=$html . '</table>';

$path="/etc/artica-postfix/smtpnotif.conf";
if(!file_exists($path)){return null;}
$ini=new Bs_IniHandler($path);

if(file_exists('/etc/artica-postfix/settings/Daemons/PostmasterAdress')){
	$PostmasterAdress=trim(file_get_contents('/etc/artica-postfix/settings/Daemons/PostmasterAdress'));
}
$administrator=$ini->_params["SMTP"]["smtp_dest"];
$mailfrom=$ini->_params["SMTP"]["smtp_sender"];
if($mailfrom==null){$mailfrom=$PostmasterAdress;}


$html="
<html>
<head></head>
<body>
<hr>
$html
</body>
</html>
";
	$tmpfile="/tmp/".md5($html);
	
	events("Sending notification to $administrator $count_new_rules new rules and $rulenumber stored rules");
	$subject="[Postfix Instant IpTables]: $count_new_rules new rules and $rulenumber stored rules in your firewall";
	file_put_contents($tmpfile,$html);
	$cmd="/usr/share/artica-postfix/bin/artica-mime --sendmail --from=$mailfrom --to=$administrator --subject=\"$subject\" --content=\"$tmpfile\" --";
	system($cmd);
	@unlink($tmpfile);
	return true;

}

function ParseResultsConfig(){
	$ini=new Bs_IniHandler();
	$ini->loadFile('/etc/artica-postfix/settings/Daemons/PostfixAutoBlockResults');
	if(!is_array($ini->_params)){
		events("No array given in /etc/artica-postfix/settings/Daemons/PostfixAutoBlockResults");
		return null;}
	
    events("History file store ".count($ini->_params) . " events");		
	$ini2=new Bs_IniHandler();
	
	
	while (list ($key, $array) = each ($ini->_params) ){
		
		
		$iptables=new iptables_chains();
		$iptables->serverip=$key;
		$iptables->events_number=$array["events"];
		$iptables->servername=$array["hostname"];
		$iptables->rule_string=$array["iptablerule"];
		if($iptables->addPostfix_chain()){
			events("Add IP:Addr=<$key>, servername=<{$array["hostname"]}> to mysql");
			$ini2->set($key,"events",$array["events"]);
			$ini2->set($key,"iptablerule",$array["iptablerule"]);
			$ini2->set($key,"hostname",$array["hostname"]);
		}
	}
	
	
	
	$filestr=$ini2->toString();
	file_put_contents("/etc/artica-postfix/settings/Daemons/PostfixAutoBlockResults",$filestr);
	
	
	
	
}

?>