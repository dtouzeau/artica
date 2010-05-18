<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
include_once(dirname(__FILE__) . '/ressources/class.templates.inc');
include_once(dirname(__FILE__) . '/ressources/class.system.network.inc');
include_once(dirname(__FILE__) . '/ressources/class.os.system.inc');
include_once(dirname(__FILE__) . '/framework/class.postfix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}


$users=new usersMenus();
if(!$users->POSTFIX_INSTALLED){die();}

$ini=new Bs_IniHandler("/etc/artica-postfix/smtpnotif.conf");
$PostfixQueueEnabled=$ini->get("SMTP","PostfixQueueEnabled");
$PostfixQueueMaxMails=$ini->get("SMTP","PostfixQueueMaxMails");

if($PostfixQueueEnabled==null){$PostfixQueueEnabled=1;}
if($PostfixQueueMaxMails==null){$PostfixQueueMaxMails=20;}
if($PostfixQueueEnabled<>1){die();}

$postfix_system=new postfix_system();
$array=$postfix_system->getQueuesNumber();

while (list ($num, $val) = each ($array)){
	$logs[]="$num=$val message(s)";
	if(intval($val)>$PostfixQueueMaxMails){
		$subject="Postfix queue $num exceed limit";
		$text="The $num storage queue contains $val messages\nIt exceed the maximum $PostfixQueueMaxMails messages number...";
		send_email_events($subject,$text,'system');
	}
}

$logs[]="$num=$val message(s)";
RTMevents(implode(" ",$logs));


function RTMevents($text){
		$f=new debuglogs();
		$f->events(basename(__FILE__)." $text","/var/log/artica-postfix/artica-status.debug");
		}
?>