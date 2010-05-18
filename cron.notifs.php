<?php
include_once(dirname(__FILE__) . '/class.cronldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/logs.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");


if($argv[1]=='--sendmail'){SendMailNotification(null,null,true);die();}
if(preg_match("#--verbose#",implode(" ",$argv))){$_GET["DEBUG"]=true;}
	


$pid=getmypid();

if(file_exists('/etc/artica-postfix/croned.1/cron.notifs.php.pid')){
	$currentpid=trim(file_get_contents('/etc/artica-postfix/croned.1/cron.notifs.php.pid'));
	if($currentpid<>$pid){
		if(is_dir('/proc/'.$currentpid)){
			die(date('Y-m-d h:i:s')." Already instance executed");
	}else{
		echo date('Y-m-d h:i:s')." $currentpid is not executed continue...\n";
	}
		
	}
}


@mkdir("/etc/artica-postfix/croned.1");
file_put_contents('/etc/artica-postfix/croned.1/cron.notifs.php.pid',$pid);
events("new pid $pid");
echo events("Starting parsing events...");
ParseEvents();
echo events("Starting Launch notifications");
LaunchNotifs();
echo events("Die");
die();



function ParseEvents(){

$path="/var/log/artica-postfix/events";
$f=new filesClasses();
$hash=$f->DirListTable($path);


if(!is_array($hash)){return null;}

echo date('Y-m-d h:i:s')." " .count($hash) . " file(s) notifications...\n";

$mysql=new mysql();

	while (list ($num, $file) = each ($hash)){
		
		
		$bigtext=file_get_contents($path.'/'.$file);
		
		echo date('Y-m-d h:i:s')." Parsing $file ". strlen($bigtext)." bytes text\n";
		
		$ini=new Bs_IniHandler();
		if(preg_match("#<text>(.+?)</text>#is",$bigtext,$re)){
			$text=$re[1];
			if(strlen($text)>0){
				$bigtext=str_replace($re[0],'',$bigtext);
			}
		}
		
		$ini->loadString($bigtext);
        $processname=$ini->_params["LOG"]["processname"];
        $date=$ini->_params["LOG"]["date"];
        $context=$ini->_params["LOG"]["context"];
        $context=addslashes($context);
        if(strlen($text)<2){
        	$text=$ini->_params["LOG"]["text"];
        }
        $text=addslashes($text);
        $subject=$ini->_params["LOG"]["subject"];
        $subject=addslashes($subject);
        
        echo date('Y-m-d h:i:s')." Parsing subject $subject ". strlen($text)." bytes text\n";
        
        writelogs("New notification: $subject (". strlen($text)." bytes)",__FUNCTION__,__FILE__,__LINE__);
        
        $sql="INSERT INTO events (zDate,hostname,process,text,context,content) VALUES(
        	'$date',
        	'$mysql->hostname',
        	'$processname',
        	'$subject',
        	'$context','$text')";
        	
        	
        	
       echo date('Y-m-d h:i:s')." run mysql query\n"; 
		
		if($mysql->QUERY_SQL($sql,'artica_events')){
			unlink($path.'/'.$file);}
			else{
				error_log("Mysql error keep $path/$file;");
			}
		
	}

if(count($hash)>0){
events(count($hash). " events queue parsed...");
}


}

function LaunchNotifs(){
$ini=new Bs_IniHandler("/etc/artica-postfix/smtpnotif.conf");
$sa_learn=$ini->_params["SMTP"]["sa-learn"];
$system=$ini->_params["SMTP"]["system"];
$update=$ini->_params["SMTP"]["update"];	
$q=new mysql();


$sql="SELECT COUNT(*) as tcount FROM events";
$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));

events("Mysql store {$ligne["tcount"]} events");



if($ligne["tcount"]>4000){
	$sql="DELETE FROM events ORDER BY zDate LIMIT 1000";
	events("Mysql Delete 1000 old events");
	$q->QUERY_SQL($sql,"artica_events");
}


$sql="SELECT * FROM `events` WHERE sended=0 ORDER BY zDate DESC LIMIT 0,100";

$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			events("New event: {$ligne["text"]}");
	
			$context=$ligne["context"];
			$ligne["content"]=str_replace('[br]',"\n",$ligne["content"]);
			if($ini->_params["SMTP"][$context]==1){
				$ligne["content"]="{$ligne["zDate"]} :{$ligne["process"]}: {$ligne["text"]}\n\n-----------------------------------------------------\n{$ligne["content"]}\n";
				events("Notify {$ligne["text"]}");
				SendMailNotification($ligne["content"],"[$context]: {$ligne["text"]}");
			}
			$sql="UPDATE events SET sended=1 WHERE ID={$ligne["ID"]}";
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){
				events("Mysql error $sql");
			}
	
	
	
	}

}

function events($text){
		$pid=getmypid();
		$filename=basename(__FILE__);
		$date=date("H:i:s");
		$logFile="/var/log/artica-postfix/notifications.debug";
		$size=filesize($logFile);
		if($size>1000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$line="$date $filename[$pid] $text\n";
		if($_GET["DEBUG"]){echo $line;}
		@fwrite($f,$line);
		@fclose($f);
	}



//sa-learn
//system
//update
	
	

?>