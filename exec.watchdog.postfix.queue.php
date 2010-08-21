<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
include_once(dirname(__FILE__) . '/ressources/class.templates.inc');
include_once(dirname(__FILE__) . '/ressources/class.system.network.inc');
include_once(dirname(__FILE__) . '/ressources/class.os.system.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/framework/class.postfix.inc');
include_once(dirname(__FILE__).  "/framework/frame.class.inc");
include_once(dirname(__FILE__).  '/framework/class.unix.inc');

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;}

if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

$users=new usersMenus();
$sock=new sockets();

if(!$users->POSTFIX_INSTALLED){die();}
$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
watchdog();
postqueue_master("MASTER");
if($EnablePostfixMultiInstance==1){multiples_instances();exit;}


function watchdog(){



$ini=new Bs_IniHandler("/etc/artica-postfix/smtpnotif.conf");
$PostfixQueueEnabled=$ini->get("SMTP","PostfixQueueEnabled");
$PostfixQueueMaxMails=$ini->get("SMTP","PostfixQueueMaxMails");

if($PostfixQueueEnabled==null){$PostfixQueueEnabled=1;}
if($PostfixQueueMaxMails==null){$PostfixQueueMaxMails=20;}
if($PostfixQueueEnabled<>1){return;}

$postfix_system=new postfix_system();
$array=$postfix_system->getQueuesNumber();

while (list ($num, $val) = each ($array)){
	$logs[]="$num=$val message(s)";
	if(intval($val)>$PostfixQueueMaxMails){
		if(is_file("/etc/artica-postfix/croned.1/postfix.$num.exceed")){if(file_time_min("/etc/artica-postfix/croned.1/postfix.$num.exceed")<30){continue;}}
		@file_put_contents("/etc/artica-postfix/croned.1/postfix.$num.exceed","#");
		$subject="Postfix queue $num exceed limit";
		$text="The $num storage queue contains $val messages\nIt exceed the maximum $PostfixQueueMaxMails messages number...";
		send_email_events($subject,$text,'system');
	}
}

$logs[]="$num=$val message(s)";
RTMevents(implode(" ",$logs));
}


function postqueue_master($instance="MASTER"){
	$unix=new unix();
	if($GLOBALS["VERBOSE"]){echo __FUNCTION__.":: $instance:: analyze $instance\n";}
	$instance_name=$instance;
	if($instance=="MASTER"){$instance=null;}else{$instance="-$instance";}
	$postqueue=$unix->find_program("postqueue");
	
	if($GLOBALS["VERBOSE"]){echo __FUNCTION__.":: $instance:: $postqueue -c /etc/postfix$instance -p\n";}
	exec("$postqueue -c /etc/postfix$instance -p",$results);
	$count=count($results);
	
	$array["COUNT"]=postqueue_master_count($instance,$results);
	if($GLOBALS["VERBOSE"]){echo __FUNCTION__.":: $instance:: {$array["COUNT"]} message(s)\n";}
	if($count>900){$count=900;}
	
	for($i=0;$i<=$count;$i++){
		$line=$results[$i];
		if(preg_match("#([A-Z0-9]+)\s+([0-9]+)\s+(.+?)\s+([0-9]+)\s+([0-9:]+)\s+(.+?)$#",$line,$re)){
			$MSGID=$re[1];
			$size=$re[2];
			$day=$re[3];
			$dayNum=$re[4];
			$time=$re[5];
			$from=$re[6];
			$array["LIST"][$MSGID]["DATE"]="$day $dayNum $time";
			$array["LIST"][$MSGID]["FROM"]="$from";
			continue;
		}
		if(preg_match("#^\((.+?)\)$#",trim($line),$re)){
			$array["LIST"][$MSGID]["STATUS"]=$re[1];
			continue;
		}
		
		if(preg_match("#^\s+\s+\s+(.+?)$#",$line,$re)){
			$array["LIST"][$MSGID]["TO"]=trim($re[1]);
			continue;
		}
		
		
	}
	
	@file_put_contents("/var/log/artica-postfix/postqueue.$instance_name",serialize($array));
	
}

function postqueue_master_count($instance="MASTER",$results){
	$unix=new unix();
	$instance_name=$instance;
	if($instance=="MASTER"){$instance=null;}else{$instance="-$instance";}
	reset($results);
	while (list ($num, $line) = each ($results) ){
		if(preg_match("#Mail queue is empty#",$line)){
			if($GLOBALS["VERBOSE"]){echo __FUNCTION__.":: $line ($num)\n";} 
			$count=0;
			break;
		}
		
		if(preg_match("#-- [0-9]+\s+([a-zA-Z]+)\s+in\s+([0-9]+)\s+Requests#",$line,$re)){
			if($GLOBALS["VERBOSE"]){echo __FUNCTION__.":: $line ($num)\n";}
			$count=$re[2];
			break;
		}
		
	}

	return $count;
}

function multiples_instances(){
	$sql="SELECT `value` FROM postfix_multi WHERE `key`='myhostname'";
	$q= new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$instance=$ligne["value"];
		postqueue_master($instance);
	}

}



function RTMevents($text){
		$f=new debuglogs();
		$f->events(basename(__FILE__)." $text","/var/log/artica-postfix/artica-status.debug");
		}
?>