<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

$pid=getmypid();
$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
$sock=new sockets();
events("running $pid ");
file_put_contents($pidfile,$pid);
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
$users=new usersMenus();
$_GET["server"]=$users->hostname;
$_GET["IMAP_HACK"]=array();
$GLOBALS["POP_HACK"]=array();
$GLOBALS["SMTP_HACK"]=array();

	$GLOBALS["PopHackEnabled"]=$sock->GET_INFO("PopHackEnabled");
	$GLOBALS["PopHackCount"]=$sock->GET_INFO("PopHackCount");
	if($GLOBALS["PopHackEnabled"]==null){$GLOBALS["PopHackEnabled"]=1;}
	if($GLOBALS["PopHackCount"]==null){$GLOBALS["PopHackCount"]=10;}

@mkdir("/etc/artica-postfix/cron.1",0755,true);
@mkdir("/etc/artica-postfix/cron.2",0755,true);

$pipe = fopen("php://stdin", "r");
while(!feof($pipe)){
$buffer .= fgets($pipe, 4096);
Parseline($buffer);
$buffer=null;
}
fclose($pipe);
events("Shutdown...");
die();
function Parseline($buffer){
$buffer=trim($buffer);
if($buffer==null){return null;}

if(preg_match("#assp\[.+?LDAP Results#",$buffer,$re)){return null;}
if(preg_match("#smtpd\[.+?\]: disconnect from#",$buffer,$re)){return null;}
if(preg_match("#smtpd\[.+?\]: connect from#",$buffer,$re)){return null;}
if(preg_match("#smtpd\[.+?\]: timeout after END-OF-MESSAGE#",$buffer,$re)){return null;}
if(preg_match("#smtpd\[.+?\]:.+?enabling PIX workarounds#",$buffer,$re)){return null;}
if(preg_match("#milter-greylist:.+?skipping greylist#",$buffer,$re)){return null;}
if(preg_match("#milter-greylist:\s+\(.+?greylisted entry timed out#",$buffer,$re)){return null;}
if(preg_match("#postfix\/qmgr\[.+?\]:\s+.+?: removed#",$buffer,$re)){return null;}
if(preg_match("#postfix\/smtpd\[.+?\]:\s+lost connection after#",$buffer,$re)){return null;}
if(preg_match("#assp.+?\[MessageOK\]#",$buffer,$re)){return null;}
if(preg_match("#assp.+?\[NoProcessing\]#",$buffer,$re)){return null;}
if(preg_match("#passed trough amavis and event is saved#",$buffer,$re)){return null;}
if(preg_match("#assp.+?AdminUpdate#",$buffer,$re)){return null;}
if(preg_match("#last message repeated.+?times#",$buffer,$re)){return null;}
if(preg_match("#cyrus\/master.+?about to exec#",$buffer,$re)){return null;}
if(preg_match("#cyrus\/.+?open: user#",$buffer,$re)){return null;}
if(preg_match("#cyrus\/lmtpunix.+?accepted connection#",$buffer,$re)){return null;}
if(preg_match("#cyrus\/lmtpunix.+?Delivered:#",$buffer,$re)){return null;}
if(preg_match("#cyrus\/master.+?process.+?exited#",$buffer,$re)){return null;}
if(preg_match("#lmtpunix.+?mystore: starting txn#",$buffer,$re)){return null;}
if(preg_match("#lmtpunix.+?duplicate_mark#",$buffer,$re)){return null;}
if(preg_match("#lmtpunix.+?mystore: committing txn#",$buffer,$re)){return null;}
if(preg_match("#ctl_cyrusdb.+?archiving#",$buffer,$re)){return null;}
if(preg_match("#assp.+?LDAP - found.+?in LDAPlist;#",$buffer,$re)){return null;}
if(preg_match("#anvil.+?statistics: max#",$buffer,$re)){return null;}
if(preg_match("#smfi_getsymval failed for#",$buffer)){return null;}
if(preg_match("#cyrus\/imap\[.+?Expunged\s+[0-9]+\s+message.+?from#",$buffer)){return null;}
if(preg_match("#cyrus\/imap\[.+?seen_db:\s+#",$buffer)){return null;}
if(preg_match("#cyrus\/[pop3|imap]\[.+?SSL_accept\(#",$buffer)){return null;}
if(preg_match("#cyrus\/[pop3|imap]\[.+?starttls:#",$buffer)){return null;}
if(preg_match("#cyrus\/[pop3|imap]\[.+?:\s+inflate#",$buffer)){return null;}
if(preg_match("#cyrus\/.+?\[.+?:\s+accepted connection$#",$buffer)){return null;}
if(preg_match("#cyrus\/.+?\[.+?:\s+deflate\(#",$buffer)){return null;}
if(preg_match("#cyrus\/.+?\[.+?:\s+\=>\s+compressed to#",$buffer)){return null;}
if(preg_match("#filter-module\[.+?:\s+KASINFO#",$buffer)){return null;}
if(preg_match("#exec\.mailbackup\.php#",$buffer)){return null;}
if(preg_match("#kavmilter\[.+?\]:\s+Loading#",$buffer)){return null;}
if(preg_match("#DBERROR: init.+?on berkeley#",$buffer)){return null;}
if(preg_match("#FATAL: lmtpd: unable to init duplicate delivery database#",$buffer)){return null;}
if(preg_match("#skiplist: checkpointed.+?annotations\.db#",$buffer)){return null;}
if(preg_match("#duplicate_prune#",$buffer)){return null;}
if(preg_match("#cyrus\/cyr_expire\[[0-9]+#",$buffer)){return null;}



if(preg_match("#spamd\[[0-9]+.+?Can.+?locate\s+Mail\/SpamAssassin\/CompiledRegexps\/body_0\.pm#",$buffer,$re)){
	SpamAssassin_error_saupdate($buffer);
	return null;
}

if(preg_match("#zarafa-monitor.+?:\s+Unable to get store entry id for company\s+(.+?), error code#",$buffer,$re)){
	zarafa_store_error($buffer);
	return null;
}

if(preg_match("#smtp.+?status=deferred.+?connect.+?\[127\.0\.0\.1\]:10024: Connection refused#",$buffer,$re)){
	AmavisConfigErrorInPostfix($buffer);
	return null;
}

if(preg_match("#postfix\/.+?:(.+?):\s+milter-reject: END-OF-MESSAGE\s+.+?Error in processing.+?ALL VIRUS SCANNERS FAILED;.+?from=<(.+?)>\s+to=<(.+?)>#",$buffer,$re)){
	event_message_milter_reject($re[1],"antivirus failed",$re[1],$re[2],$buffer);
	clamav_error_restart($buffer);
	return null;	
	}

if(preg_match("#postfix\/.+?:(.+?):\s+to=<(.+?)>,.+?\[(.+?)\].+?status=deferred.+?virus_scan FAILED#",$buffer,$re)){
	event_messageid_rejected($re[1],"antivirus failed",$re[3],$re[2]);
	return null;
	}


if(preg_match("#kavmilter\[.+?:\s+KAVMilter Error\(13\):\s+Active key expired.+?Exiting#",$buffer,$re)){
	kavmilter_expired($buffer);
	return null;
}


if(preg_match("#cyrus\/.+?\[.+?IOERROR: fstating sieve script\s+(.+?):\s+No such file or directory#",$buffer,$re)){
	@mkdir(dirname($re[1]),null,true);
	@file_put_contents($re[1]," ");
	return null;
}
if(preg_match("#cyrus\/.+?\[.+?IOERROR: fstating sieve script\s+(.+?):\s+Permission denied#",$buffer,$re)){
	shell_exec("/bin/chown cyrus:mail {$re[1]}");
	return null;
}
if(preg_match("#cyrus\/.+?\[.+?IOERROR: fstating sieve script\s+(.+?):\s+Permission denied#",$buffer,$re)){
	shell_exec("/bin/chown cyrus:mail {$re[1]}");
	return null;
}

if(preg_match("#cyrus\/imap\[.+?:\s+Deleted mailbox user\.(.+)#",$buffer,$re)){
	email_events("{$re[1]} Mailbox has been deleted",$buffer,"mailbox"); 
	return;
}
if(preg_match("#cyrus.+?reconstruct\[.+?:\s+Updating last_appenddate for user\.(.+?):#",$buffer,$re)){
	email_events("{$re[1]} Mailbox has been reconstructed",$buffer,"mailbox"); 
	return;
}

if(preg_match("#cyrus\/lmtpunix.+?IOERROR:\s+opening.+?\/user\/(.+?)\/cyrus.header:\s+No such file or directory#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/postfix.{$re[1]}.mbx.error";
	events("lmtpunix -> mailbox IOERROR error");
	if(file_time_min($file)>5){
		email_events("{$re[1]} Mailbox is deleted but postfix wants to tranfert mails !","Postfix claim\n$buffer\nArtica will re-create the mailbox","mailbox");
		events(LOCATE_PHP5_BIN2(). " /usr/share/artica-postfix/exec.cyrus-restore.php --create-mbx {$re[1]}"); 
		THREAD_COMMAND_SET(LOCATE_PHP5_BIN2(). " /usr/share/artica-postfix/exec.cyrus-restore.php --create-mbx {$re[1]}");
		@unlink($file);
		file_put_contents($file,"#");
	}
	events("lmtpunix -> mailbox IOERROR error (timeout)");
	return;
}

if(preg_match('#NOQUEUE: reject: MAIL from.+?452 4.3.1 Insufficient system storage#',$buffer)){
	$file="/etc/artica-postfix/croned.1/postfix.storage.error";
	if(file_time_min($file)>10){
		email_events("Postfix Insufficient storage disk space!!! ","Postfix claim: $buffer\n Please check your hard disk space !" ,"system");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;
}


if(preg_match("#starting amavisd-milter.+?on socket#",$buffer)){
	email_events("Amavisd New has been successfully started",$buffer,"system"); 
	return;
}


if(preg_match("#kavmilter\[.+?\]:\s+Could not open pid file#",$buffer)){
	$file="/etc/artica-postfix/croned.1/postfix.kavmilter.pid.error";
		if(file_time_min($file)>10){
			events("Kaspersky Milter PID error");
			email_events("Kaspersky Milter PID error","kvmilter claim $buffer\nArtica will try to restart it","postfix");
			THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart kavmilter');
			@unlink($file);
		}else{
			events("Kaspersky Milter PID error, but take action after 10mn");
		}	
	file_put_contents($file,"#");	
	return null;
	
}	


// HACK POP3
if(preg_match("#cyrus\/pop3\[.+?badlogin.+?.+?\[(.+?)\]\s+APOP.+?<(.+?)>.+?SASL.+?: user not found: could not find password#",$buffer,$re)){
	hackPOP($re[1],$re[2],$buffer);
	return;
	}
if(preg_match("#cyrus\/pop3\[.+?:\s+badlogin:\s+.+?\[(.+?)\]\s+plaintext\s+(.+?)\s+SASL.+?authentication failure:#",$buffer,$re)){
	hackPOP($re[1],$re[2],$buffer);
	return;
}

if(preg_match("#zarafa-gateway\[.+?: Failed to login from\s+(.+?)\s+with invalid username\s+\"(.+?)\"\s+or wrong password#",$buffer,$re)){
	hackPOP($re[1],$re[2],$buffer);
	return;
}

if(preg_match("#smtpd.+?:\s+warning: SASL authentication failure: no secret in database#",$buffer)){
	$file="/etc/artica-postfix/croned.1/postfix.sasl.secret.error";
		if(file_time_min($file)>10){
			events("SASL authentication failure");
			email_events("Postfix error SASL","Postfix claim $buffer\nArtica will try to repair it","postfix");
			THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --postfix-sasldb2');
			@unlink($file);
		}else{
			events("SASL authentication failure, but take action after 10mn");
		}	
	return null;
	
}

if(preg_match("#smtp.+?connect to 127\.0\.0\.1\[127\.0\.0\.1\]:10024: Connection refused#",$buffer,$re)){
	AmavisConfigErrorInPostfix($buffer);
	return null;
}


if(preg_match("#postfix\/smtp\[.+?:\s+(.+?):\s+to=<(.+?)>.+?status=deferred\s+\(SASL authentication failed.+?\[(.+?)\]#",$buffer,$re)){
	event_messageid_rejected($re[1],"authentication failed",$re[3],$re[2]);
	smtp_sasl_failed($re[3],$re[3],$buffer);
}


if(preg_match("#postfix\/smtp\[.+?:\s+(.+?):\s+to=<(.+?)>.+?status=bounced.+?.+?\[(.+?)\]\s+said:\s+554.+?http:\/\/#",$buffer,$re)){
	ImBlackListed($re[3],$buffer);
	event_messageid_rejected($re[1],"Your are blacklisted",$re[3],$re[2]);
	return null;
}

if(preg_match("#postfix\/(cleanup|bounce|smtp|smtpd|flush|trivial-rewrite)\[.+?warning: database\s+(.+?)\.db\s+is older than source file\s+(.+)#",$buffer,$re)){
	postfix_compile_db($re[3],$buffer);
	return null;
}
if(preg_match("#postfix\/(cleanup|bounce|smtp|smtpd|flush|trivial-rewrite)\[.+?fatal: open database\s+(.+?)\.db:\s+No such file or directory#",$buffer,$re)){
	postfix_compile_missing_db($re[2],$buffer);
	return null;
}

if(preg_match("#postfix\/smtp\[.+?:\s+(.+?):\s+host.+?\[(.+?)\]\s+said:\s+[0-9]+\s+invalid sender domain#",$buffer,$re)){
	event_messageid_rejected($re[1],"invalid sender domain",$re[2],null);
	return null;
}

if(preg_match("#warning: connect to Milter service unix:(.+?)clamav-milter.ctl: Connection refused#",$buffer,$re)){
	MilterClamavError($buffer,"$re[1]/clamav-milter.ctl");
	return null;
}

if(preg_match("#warning: connect to Milter service unix:(.+?)spamass.sock: No such file or directory#",$buffer,$re)){
	MilterSpamAssassinError($buffer,"$re[1]/spamass.sock");
	return null;
}

if(preg_match("#warning: connect to Milter service unix:(.+?)greylist.sock: No such file or directory#",$buffer,$re)){
	miltergreylist_error($buffer,"{$re[1]}/greylist.sock");
}

if(preg_match("#warning: connect to Milter service unix:/var/spool/postfix/var/run/amavisd-milter/amavisd-milter.sock: Connection refused#",$buffer)){
		AmavisConfigErrorInPostfix($buffer);
		return null;
}

if(preg_match("#qmgr.+?transport amavis: Connection refused#",$buffer)){
	AmavisConfigErrorInPostfixRestart($buffer);
	return null;
}



if(preg_match('#milter-greylist: greylist: Unable to bind to port (.+?): Permission denied#',$buffer,$re)){
	miltergreylist_error($buffer,$re[1]);
}

if(preg_match("#cyrus\/master.+? unable to create lmtpunix listener socket(.+?)#",$buffer,$re)){
	cyrus_socket_error($buffer,"$re[1]");
	return null;
}

if(preg_match("#cyrus\/lmtpunix\[.+?:\s+verify_user\(.+?\)\s+failed:\s+System I\/O error#",$buffer,$re)){
	cyrus_generic_reconfigure($buffer,"Cyrus I/O error");
	return null;
}

if(preg_match('#]:\s+(.+?): to=<(.+?)>.+?socket/lmtp\].+?status=deferred.+?lost connection with.+?end of data#',$buffer,$re)){
	event_finish($re[1],$re[2],"deferred","mailbox service error",null,$buffer);
	return null;
}
if(preg_match('#imap.+?IOERROR.+?opening\s+(.+?):.+?Permission denied#',$buffer,$re)){
	if(is_dir($re[1])){
		events("chown ".dirname($re[1]));
		THREAD_COMMAND_SET('/bin/chown -R cyrus:mail '.dirname($re[1]));
	}
	return null;
}
if(preg_match('#IOERROR: fstating sieve script (.+?): No such file or directory#',$buffer,$re)){
		events("/bin/touch {$re[1]}");
		THREAD_COMMAND_SET("/bin/touch {$re[1]}");
		return null;
}
if(preg_match('#ctl_cyrusdb.+?IOERROR.+?: Permission denied#',$buffer,$re)){
		$file="/etc/artica-postfix/croned.1/cyrus.IOERROR.permissions.error";
		if(file_time_min($file)>10){
			events("IOERROR detected, check perms");
			email_events("Cyrus error permissions on databases","Cyrus imap claim $buffer\nArtica will try to repair it","mailbox");
			THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --cyrus-checkperms');
			@unlink($file);
		}else{
			events("IOERROR detected, but take action after 10mn");
		}	
	@file_put_contents($file,"#");	
	return null;
}

if(preg_match('#cyrus\/lmtpunix\[.+? IOERROR: can not open sieve script\s+(.+?):\s+Permission denied#',$buffer,$re)){
		$file="/etc/artica-postfix/croned.1/cyrus.IOERROR.permissions.". md5($re[1]).".error";
		if(file_time_min($file)>10){
			events("IOERROR detected {$re[1]}, check perms");
			THREAD_COMMAND_SET("/bin/chown cyrus:mail {$re[1]}");
			@unlink($file);
		}else{
			events("IOERROR detected, {$re[1]} but take action after 10mn");
		}	
	@file_put_contents($file,"#");	
	return null;
}







if(preg_match('#badlogin: \[(.+?)\] plaintext\s+(.+?)\s+SASL\(-13\): authentication failure: checkpass failed#',$buffer,$re)){
	$date=date('Y-m-d H');
	$_GET["IMAP_HACK"][$re[1]][$date]=$_GET["IMAP_HACK"][$re[1]][$date]+1;
	events("cyrus Hack:bad login {$re[1]}:{$_GET["IMAP_HACK"][$re[1]][$date]} retries");
	if($_GET["IMAP_HACK"][$re[1]][$date]>15){
		email_events("Cyrus HACKING !!!!","Build iptables rule \"iptables -I INPUT -s {$re[1]} -j DROP\" for {$re[1]}!\nlaster error: $buffer","mailbox");
		shell_exec("iptables -I INPUT -s {$re[1]} -j DROP");
		events("IMAP Hack: -> iptables -I INPUT -s {$re[1]} -j DROP");
		unset($_GET["IMAP_HACK"][$re[1]]);
	}
	
	return null;
}

if(preg_match('#warning.+?\[([0-9\.]+)\]:\s+SASL LOGIN authentication failed: authentication failure#',$buffer,$re)){
	$date=date('Y-m-d H');
	$GLOBALS["SMTP_HACK"][$re[1]][$date]=$GLOBALS["SMTP_HACK"][$re[1]][$date]+1;
	events("Postfix Hack:bad SASL login {$re[1]}:{$GLOBALS["SMTP_HACK"][$re[1]][$date]} retries");
	if($GLOBALS["SMTP_HACK"][$re[1]][$date]>15){
		email_events("SMTP HACKING !!!!","Build iptables rule \"iptables -I INPUT -s {$re[1]} -j DROP\" for {$re[1]}!\nlast error: $buffer","postfix");
		shell_exec("iptables -I INPUT -s {$re[1]} -j DROP");
		events("SMTP Hack: -> iptables -I INPUT -s {$re[1]} -j DROP");
		unset($GLOBALS["IMAP_HACK"][$re[1]]);	
	}
	return null;
}

if(preg_match('#badlogin: \[(.+?)\] plaintext\s+(.+?)\s+SASL\(-1\): generic failure: checkpass failed#',$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/cyrus.checkpass.error";
	if(file_time_min($file)>10){
		email_events("Cyrus auth error","Artica will restart messaging service\n\"$buffer\"","mailbox");
		THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart imap');
		@unlink($file);
	}
	return null;
}

if(preg_match('#cyrus\/notify.+?DBERROR db[0-9]: PANIC: fatal region error detected; run recovery#',$buffer)){
	$file="/etc/artica-postfix/croned.1/cyrus.db.error";
	if(file_time_min($file)>10){
		$buffer="Artica has detected a fatal error on cyrus\n$buffer\nArtica will try to repair it but it should not working\n";
		$buffer=$buffer."Perhaps you need to contact your support to correctly recover cyrus databases\n";
		$buffer=$buffer."Notice,read this topic : http://www.gradstein.info/software/how-to-recover-from-cyrus-when-you-have-some-db-errors/\n";
		THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --cyrus-recoverdb');
		email_events("Cyrus database error !!",$buffer,"mailbox");
		events("DBERROR detected, take action");
		@unlink($file);
		file_put_contents($file,"#");
	}else{
		events("DBERROR detected, but take action after 10mn");
	}
	return null;	
}


if(preg_match("#cyrus.+?DBERROR\s+db[0-9]+:\s+DB_AUTO_COMMIT may not be specified in non-transactional environment#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/cyrus.db.error";
	if(file_time_min($file)>10){
		$buffer="Artica has detected a fatal error on cyrus\n$buffer\nArtica will try to repair it but it should not working\n";
		$buffer=$buffer."Perhaps you need to contact your support to correctly recover cyrus databases\n";
		$buffer=$buffer."Notice,read this topic : http://www.gradstein.info/software/how-to-recover-from-cyrus-when-you-have-some-db-errors/\n";
		THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --cyrus-ctl-cyrusdb');
		email_events("Cyrus database error !!",$buffer,"mailbox");
		events("DBERROR detected, take action");
		@unlink($file);
		file_put_contents($file,"#");
	}else{
		events("DBERROR detected, but take action after 10mn");
	}
	return null;
}


if(preg_match('#cyrus\/imap.+?DBERROR db[0-9]: PANIC: fatal region error detected; run recovery#',$buffer)){
	$file="/etc/artica-postfix/croned.1/cyrus.db.error";
	$ftime=file_time_min($file);
	if($ftime>10){
		$buffer="Artica has detected a fatal error on cyrus\n$buffer\nArtica will try to repair it but it should not working\n";
		$buffer=$buffer."Perhaps you need to contact your support to correctly recover cyrus databases\n";
		$buffer=$buffer."Notice,read this topic : http://www.gradstein.info/software/how-to-recover-from-cyrus-when-you-have-some-db-errors/\n";
		THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --cyrus-recoverdb');
		email_events("Cyrus database error !!",$buffer,"mailbox");
		events("DBERROR detected, take action ftime=$ftime");
		@unlink($file);
		
		file_put_contents($file,"#");
	}else{
		events("DBERROR detected, but take action after 10mn");
	}
	return null;	
}



if(preg_match("#cyrus.+?:\s+DBERROR:\s+opening.+?mailboxes.db:\s+cyrusdb error#",$buffer)){
	$file="/etc/artica-postfix/croned.1/cyrus.db.error";
	if(file_time_min($file)>10){
		$buffer="Artica has detected a fatal error on cyrus\n$buffer\nArtica will try to repair it but it should not working\n";
		$buffer=$buffer."Perhaps you need to contact your support to correctly recover cyrus databases\n";
		$buffer=$buffer."Notice,read this topic : http://www.gradstein.info/software/how-to-recover-from-cyrus-when-you-have-some-db-errors/\n";
		THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --cyrus-recoverdb');
		email_events("Cyrus database error !!",$buffer,"mailbox");
		@unlink($file);
		file_put_contents($file,"#");
	}else{
		events("DBERROR detected, but take action after 10mn");
	}
	return null;	
}


if(preg_match('#cyrus\/(.+?)\[.+?login:(.+?)\[(.+?)\]\s+(.+?)\s+.+?User#',$buffer,$re)){
	$service=trim($re[1]);
	$server=trim($re[2]);
	$server_ip=trim($re[3]);
	$user=trim($re[4]);
	cyrus_imap_conx($service,$server,$server_ip,$user);
	return null;
}

if(preg_match("#zarafa-gateway\[.+?:\s+IMAP Login from\s+(.+)\s+for user\s+(.+?)\s+#",$buffer,$re)){
	$service="IMAP";
	$server=trim($re[1]);
	$server_ip=trim($re[1]);
	$user=trim($re[2]);
	cyrus_imap_conx($service,$server,$server_ip,$user);
	return null;
}




if(preg_match('#cyrus\/ctl_mboxlist.+?DBERROR: reading.+?, assuming the worst#',$buffer)){
	$file="/etc/artica-postfix/croned.1/cyrus.db1.error";
	if(file_time_min($file)>10){
		$buffer="Artica has detected a fatal error on cyrus\n$buffer\n\n";
		email_events("Cyrus database error !!",$buffer,"mailbox");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;	
}
if(preg_match('#cyrus\/sync_client.+?Can not connect to server#',$buffer)){
	$file="/etc/artica-postfix/croned.1/cyrus.cluster.error";
	if(file_time_min($file)>10){
		$buffer="Artica has detected that the cyrus cluster replica is not available on cyrus\n$buffer\n\n";
		email_events("Cyrus replica not available",$buffer,"mailbox");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;	
}

if(preg_match('#cyrus\/sync_client.+?connect.+?failed: No route to host#',$buffer)){
	$file="/etc/artica-postfix/croned.1/cyrus.cluster.error";
	if(file_time_min($file)>10){
		$buffer="Artica has detected that the cyrus cluster replica is not available on cyrus\n$buffer\n\n";
		email_events("Cyrus replica not available",$buffer,"mailbox");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;	
}

if(preg_match('#warning: dict_ldap_connect: Unable to bind to server ldap#',$buffer)){
	$file="/etc/artica-postfix/croned.1/ldap.error";
	if(file_time_min($file)>10){
		email_events("Postfix is unable to connect to ldap server ",$buffer,"system");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;
}





if(preg_match('#service pop3 pid.+?in BUSY state and serving connection#',$buffer)){
	$file="/etc/artica-postfix/croned.1/pop3-busy.error";
	if(file_time_min($file)>10){
		email_events("Pop3 service is overloaded","pop3 report:\n$buffer\nPlease,increase pop3 childs connections in artica Interface","mailbox");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;
}

if(preg_match('#milter inet:[0-9\.]+:1052.+?Connection timed out#',$buffer)){
	$file="/etc/artica-postfix/croned.1/KAV-TIMEOUT.error";
	if(file_time_min($file)>10){
		email_events("Postfix service Cannot connect to Kaspersky Antivirus milter",
		"it report:\n$buffer\nPlease,disable Kaspersky service or contact your support",
		"postfix");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;
}

if(preg_match('#milter unix:/var/run/milter-greylist/milter-greylist.sock.+?Connection timed out#',$buffer)){
	$file="/etc/artica-postfix/croned.1/miltergreylist-TIMEOUT.error";
	if(file_time_min($file)>10){
		email_events("milter-greylist error",
		"it report:\n$buffer\nPlease,investigate what plugin cannot send to milter-greylist events",
		"postfix");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;
}


if(preg_match('#SASL authentication failure: cannot connect to saslauthd server#',$buffer)){
	$file="/etc/artica-postfix/croned.1/saslauthd.error";
	if(file_time_min($file)>10){
		email_events("saslauthd failed to run","it report:\n$buffer\nThis error is fatal, nobody can be logged on the system.","mailbox");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;
}


if(preg_match("#smtp.+?warning:\s+(.+?)\[(.+?)\]:\s+SASL DIGEST-MD5 authentication failed#",$buffer,$re)){
	$router_name=$re[1];
	$ip=$re[2];
	smtp_sasl_failed($router,$ip,$buffer);
	return null;
}



if(preg_match('#warning: connect to Milter service unix:/var/run/kas-milter.socket: Permission denied#',$buffer)){
	$file="/etc/artica-postfix/croned.1/kas-perms.error";
	if(file_time_min($file)>10){
		email_events("Kaspersky Anti-spam socket error","it report:\n$buffer\nArtica will restart kas service...","postfix");
		@unlink($file);
		file_put_contents($file,"#");
		THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart kas3');
		
	}
	return null;
}


if(preg_match('#smtpd.+?warning: problem talking to server (.+?):\s+Connection refused#',$buffer,$re)){
	$pb=md5($re);
	$file="/etc/artica-postfix/croned.1/postfix-talking.$pb.error";
	$time=file_time_min($file);
	if($time>10){
		events("Postfix routing error {$re[1]}");
		email_events("Postfix routing error {$re[1]}","it report:\n$buffer\nPlease take a look of your routing table","postfix");
		@unlink($file);
		file_put_contents($file,"#");
	}
	events("Postfix routing error {$re[1]} (SKIP) $time/10mn");
	return null;
	
}



if(preg_match("#sync_client.+?connect\((.+?)\) failed: Connection refused#",$buffer,$re)){
$file="/etc/artica-postfix/croned.1/".md5($buffer);
	if(file_time_min($file)>10){
		email_events("Cyrus replica {$re[1]} cluster failed","it report:\n$buffer\n
		please check your support, mails will not be delivered until replica is down !","mailbox");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;	
}


if(preg_match("#could not connect to amavisd socket /var/spool/postfix/var/run/amavisd-new/amavisd-new.sock: No such file or directory#",$buffer)){
	amavis_socket_error($buffer);
	return null;
	}
	
if(preg_match("#could not connect to amavisd socket.+?Connection timed out#",$buffer)){
	amavis_socket_error($buffer);
	return null;	
}

if(preg_match("#NOQUEUE: reject:.+?from.+?\[([0-9\.]+)\]:.+?Sender address rejected: Domain not found; from=<(.+?)> to=<(.+?)> proto#",$buffer,$re)){
	event_message_reject_hostname("Domain not found",$re[2],$re[3],$re[1]);
	events("{$re[1]} Domain not found from=<{$re[2]}> to=<{$re[3]}>");
	return null;
	}
	
if(preg_match("#NOQUEUE: reject:.+?from.+?\[([0-9\.]+)\]:.+?Client host rejected: cannot find your hostname.+?from=<(.+?)> to=<(.+?)> proto#",$buffer,$re)){
	event_message_reject_hostname("hostname not found",$re[2],$re[3],$re[1]);
	return null;
}

if(preg_match("#smtpd.+?NOQUEUE:.+?from.+?\[(.+?)\].+?Client host rejected.+?reverse hostname.+?from=<(.+?)>.+?to=<(.+?)>#",$buffer,$re)){
	event_message_reject_hostname("hostname not found",$re[2],$re[3],$re[1]);
	return null;
}

if(preg_match("#smtpd.+?NOQUEUE: reject.+?from.+?\[(.+?)\].+?Helo command rejected:.+?from=<(.+?)> to=<(.+?)>#",$buffer,$re)){
	event_message_reject_hostname("Helo command rejected",$re[2],$re[3],$re[1]);
	return null;
}
if(preg_match("#smtpd.+?NOQUEUE: reject.+?from.+?\[(.+?)\].+?4.3.5 Server configuration problem.+?from=<(.+?)> to=<(.+?)>#",$buffer,$re)){
	event_message_reject_hostname("Server configuration problem",$re[2],$re[3],$re[1]);
	return null;
}



if(preg_match("#cyrus.+?badlogin:\s+(.+?)\s+\[(.+?)\]\s+.+?\s+(.+?)\s+(.+)#",$buffer,$re)){
	$router=$re[1];
	$ip=$re[2];
	$user=$re[3];
	$error=$re[4];
	cyrus_bad_login($router,$ip,$user,$error);
	return null;
}



if(preg_match("#IOERROR.+?fstating sieve script\s+(.+?):\s+No such file or directory#",$buffer,$re)){
	THREAD_COMMAND_SET("/bin/touch \"".trim($re[1])."\"");
	return null;
}



if(preg_match("#smtp.+?\].+?([A-Z0-9]+):\s+to=<(.+?)>.+?status=deferred.+?\((.+?)command#",$buffer,$re)){
	event_message_rejected("deferred",$re[1],$re[2],$re[3]);
	return null;
}
if(preg_match("#smtp.+?:\s+(.+?):\s+to=<(.+?)>,\s+relay=none,.+?status=deferred \(connect to .+?\[(.+?)\].+?Connection refused#",$buffer,$re)){
	event_message_rejected("Connection refused",$re[1],$re[2],$re[3]);
	return null;
}



if(preg_match("#smtp.+?\].+?([A-Z0-9]+):.+?SASL authentication failed#",$buffer,$re)){
	event_messageid_rejected($re[1],"Authentication failed");
	return null;
}
if(preg_match("#smtp.+?\].+?([A-Z0-9]+):.+?refused to talk to me.+?554 RBL rejection#",$buffer,$re)){
	ImBlackListed($re[2],$buffer);
	event_messageid_rejected($re[1],"Your are blacklisted");
	return null;
}


if(preg_match("#smtp\[.+?:\s+(.+?):\s+to=<(.+?)>,\s+relay=.+?\[(.+?)\].+?status=deferred.+?refused to talk to me#",$buffer,$re)){
	ImBlackListed($re[3],$buffer);
	event_messageid_rejected($re[1],"Your are blacklisted",$re[3],$re[2]);
	return null;
}


if(preg_match("#smtp\[.+?\]:\s+(.+?):\s+to=<(.+?)>, relay=(.+?)\[.+?status=bounced\s+\(.+?loops back to myself#",$buffer,$re)){
	event_messageid_rejected($re[1],"loops back to myself",$re[3],$re[2]);
	return null;
}



if(preg_match("#smtp\[.+?:\s+(.+?):\s+host.+?\[(.+?)\]\s+refused to talk to me:#",$buffer,$re)){
	ImBlackListed($re[2],$buffer);
	event_messageid_rejected($re[1],"Your are blacklisted",$re[2]);
	return null;
}

if(preg_match("#NOQUEUE: reject:.+?from.+?\[([0-9\.]+)\]:.+?Service unavailable.+?blocked using.+?from=<(.+?)> to=<(.+?)> proto#",$buffer,$re)){
	event_message_reject_hostname("RBL",$re[2],$re[3],$re[1]);
	events("{$re[1]} RBL !! from=<{$re[2]}> to=<{$re[3]}>");
	return null;
}



if(preg_match('#milter-greylist:.+?:.+?addr.+?from <(.+?)> to <(.+?)> delayed for#',
$buffer,$re)){
	event_message_milter_reject(md5($re[1].$re[2].date('Y-m d H is')),"Greylisting",$re[1],$re[2],$buffer);
	return null;
}

if(preg_match("#assp.+?<(.+?)>\s+to:\s+(.+?)\s+recipient delayed#",$buffer,$re)){
	event_message_milter_reject(md5($re[1].$re[2].date('Y-m d H is')),"Greylisting",$re[1],$re[2],$buffer);
	return null;
}

if(preg_match("#assp.+?MessageScoring.+?<(.+?)>\s+to:\s+(.+?)\s+\[spam found\]#",$buffer,$re)){
	event_message_milter_reject(md5($re[1].$re[2].date('Y-m d H is')),"SPAM",$re[1],$re[2],$buffer);
	return null;
}
if(preg_match("#assp.+?MalformedAddress.+?<(.+?)>\s+to:\s+(.+?)\s+\malformed address:'|(.+?)'#",$buffer,$re)){
	event_message_milter_reject(md5($re[1].$re[2].date('Y-m d H is')),"malformed address",$re[1],$re[2],$buffer);
	return null;
}

if(preg_match("#assp.+?\[Extreme\]\s+(.+?)\s+<(.+?)>\s+to:\s+(.+?)\s+\[spam found\]#",$buffer,$re)){
	event_message_milter_reject(md5($re[1].$re[2].date('Y-m d H is')),"SPAM",$re[2],$re[3],$buffer,$re[1]);
	return null;	
}


if(preg_match("#assp.+?<(.*?)>\s+to:\s+(.+?)\s+bounce delayed#",$buffer,$re)){
	if($re[1]==null){$re[1]="Unknown";}
	event_message_milter_reject(md5($re[1].$re[2].date('Y-m d H is')),"bounce delayed",$re[1],$re[2],$buffer);
}

if(preg_match("#assp.+?\[DNSBL\]\s+(.+?)\s+<(.*?)>\s+to:\s+(.+?)\s+#",$buffer,$re)){
	if($re[2]==null){$re[2]="Unknown";}
	event_message_reject_hostname("DNSBL",$re[2],$re[3],$re[1]);
	return null;
}
if(preg_match("#assp.+?\[URIBL\]\s+(.+?)\s+<(.*?)>\s+to:\s+(.+?)\s+#",$buffer,$re)){
	if($re[2]==null){$re[2]="Unknown";}
	event_message_reject_hostname("URIBL",$re[2],$re[3],$re[1]);
	return null;
}


if(preg_match("#assp.+?\[SpoofedSender\]\s+(.+?)\s+<(.*?)>\s+to:\s+(.+?)\s+.+?No Spoofing Allowed#",$buffer,$re)){
	if($re[2]==null){$re[2]="Unknown";}
	event_message_reject_hostname("SPOOFED",$re[2],$re[3],$re[1]);
	return null;
}
if(preg_match("#assp.+?\[InvalidHELO\]\s+(.+?)\s+<(.*?)>\s+to:\s+(.+?)\s+#",$buffer,$re)){
	if($re[2]==null){$re[2]="Unknown";}
	event_message_reject_hostname("BAD HELO",$re[2],$re[3],$re[1]);
	return null;
}


if(preg_match("#NOQUEUE: reject: RCPT from.+?<(.+?)>: Recipient address rejected: User unknown in relay recipient table;.+?to=<(.+?)> proto=SMTP#",
$buffer,$re)){
	$id=md5($re[1].$re[2].date('Y-m d H is'));
	event_finish($id,$re[2],"reject","User unknown",$re[1]);
	return null;
	
}

if(preg_match("#postfix\/lmtp.+?:\s+(.+?):\s+to=<(.+?)>.+?said:\s+550-Mailbox unknown#",$buffer,$re)){
	$id=$re[1];
	$to=$re[2];
	event_message_milter_reject($id,"Mailbox unknown",null,$re[2],$buffer);
	mailbox_unknown($buffer,$to);
	return null;
}


if(preg_match('#: (.+?): reject: RCPT.+?Relay access denied; from=<(.+?)> to=<(.+?)> proto=SMTP#',$buffer,$re)){
	if($re[1]=="NOQUEUE"){$re[1]=md5($re[3].$re[2].date('Y-m d H is'));}
	event_finish($re[1],$re[3],"reject","Relay access denied",$re[2],$buffer);
	return null;
}

if(preg_match('#postfix.+?cleanup.+?:\s+(.+?):\s+milter-reject: END-OF-MESSAGE.+4.6.0 Content scanner malfunction; from=<(.+?)> to=<(.+?)> proto=SMTP#',
$buffer,$re)){
	events("{$re[1]} Content scanner malfunction from=<{$re[2]}> to=<{$re[3]}>");
	event_Content_scanner_malfunction($re[1],$re[2],$re[3]);
	return null;
}
if(preg_match("#postfix.+?cleanup.+?:\s+(.+?):\s+milter-discard.+?END-OF-MESSAGE.+?DISCARD.+?from=<(.+?)> to=<(.+?)> proto=SMTP#",
$buffer,$re)){
	event_DISCARD($re[1],$re[2],$re[3],$buffer);
	return null;
}
	
if(preg_match("#^([A-ZA-z]+)\s+([0-9]+)\s+([0-9\:]+).+?:\s+([A-Z0-9]+):\s+client=(.+)#",$buffer,$re)){
	$date=date('Y-m-d H:i:s');
	event_newmail($re[4],$date);
	return null;
}



if(preg_match("#^([A-ZA-z]+)\s+([0-9]+)\s+([0-9\:]+).+?:\s+([A-Z0-9]+):\s+message-id=<(.*?)>#",$buffer,$re)){
	events("NEW message_id {$re[4]} {$re[5]}");
	event_message_id($re[4],$re[5]);
	return null;	
}
if(preg_match("#^([A-ZA-z]+)\s+([0-9]+)\s+([0-9\:]+).+?:\s+([A-Z0-9]+):\s+from=<(.*?)>, size=([0-9]+)#",$buffer,$re)){
	events("NEW MAIL {$re[4]} <{$re[5]}> ({$re[6]} bytes)");
	event_message_from($re[4],$re[5],$re[6]);
	return null;
}

if(preg_match("#NOQUEUE: milter-reject: RCPT from.+?: 451 4.7.1 Greylisting in action, please come back in .+?; from=<(.+?)> to=<(.+?)> proto=SMTP#",$buffer,$re)){
	event_message_milter_reject(md5($re[1].$re[2].date('Y-m d H is')),"Greylisting",$re[1],$re[2],$buffer);
	return null;
}

if(preg_match("#^([A-ZA-z]+)\s+([0-9]+)\s+([0-9\:]+).+?:\s+([A-Z0-9]+):\s+milter-reject:.+?:(.+?)\s+from=<(.+?)>#",$buffer,$re)){
	events("milter-reject {$re[4]} <{$re[5]}> ({$re[6]})");
	event_message_milter_reject($re[4],$re[5],$re[6],null,$buffer);
	return null;
}




if(preg_match("#^([A-ZA-z]+)\s+([0-9]+)\s+([0-9\:]+).+?:\s+([A-Z0-9]+):\s+to=<(.+?)>,\s+orig_to=<.+?>,\s+relay=(.+?),\s+delay=.+?,\s+delays=.+?,\s+dsn=.+?,\s+status=([a-zA-Z]+)#"
,$buffer,$re)){
	if(preg_match('#\s+status=.+?\s+\((.+?)\)#',$buffer,$ri)){
		$bounce_error=$ri[1];
	}
   events("Finish {$re[4]} <{$re[5]}> ({$re[7]})");
   event_finish($re[4],$re[5],$re[7],$bounce_error,null,$buffer);   
   return null;
	
}
if(preg_match("#^([A-ZA-z]+)\s+([0-9]+)\s+([0-9\:]+).+?:\s+([A-Z0-9]+):\s+to=<(.+?)>,\s+relay=(.+?),\s+delay=.+?,\s+delays=.+?,\s+dsn=.+?,\s+status=([a-zA-Z]+)#"
,$buffer,$re)){
	if(preg_match('#\s+status=.+?\s+\((.+?)\)#',$buffer,$ri)){
		$bounce_error=$ri[1];
	}
   event_finish($re[4],$re[5],$re[7],$bounce_error,null,$buffer);   
   return null;	
}

	
//-------------------------------------------------------------- ERRORS

if(preg_match('#amavisd-milter.+?could not read from amavisd socket.+?\.sock:Connection timed out#',$buffer,$re)){
	amavis_socket_error($buffer);
	return null;
}

if(preg_match('#DBERROR: skiplist recovery\s+(.+?)\.seen:\s+ADD\s+at.+?exists#',$buffer,$re)){
	cyrus_bad_seen($re[1]);
	return null;
}

if(preg_match('#warning: milter unix.+?amavisd-milter.sock:.+SMFIC_MAIL reply packet header: Broken pipe#',$buffer,$re)){
	amavis_error_restart($buffer);
	return null;
}
if(preg_match('#sfupdates.+?KASERROR.+?keepup2date\s+failed.+?code.+?critical error#',$buffer,$re)){
	kas_error_update($buffer);
	return null;
}
if(preg_match('#DBERROR db4:(.+?): unexpected file type or format#',$buffer,$re)){
	cyrus_db_error($buffer,$re[1]);
	return null;
}

if(preg_match('#couldn.+?exec.+?imapd: Too many open files#',$buffer)){
	cyrus_generic_error($buffer,"Too many open files");
	return null;
}
if(preg_match("#sieve script\s+(.+?)\s+doesn.+?t exist: No such file or directory#",$buffer,$re)){
	cyrus_sieve_error($re[1]);
	return null;
}

if(preg_match('#lmtp.+?:\s+(.+?): to=<(.+?)>,.+?status=deferred.+?connect to .+?\[(.+?)\].+?No such file or directory#',
$buffer,$re)){
	event_message_milter_reject($re[1],"deferred",null,$re[1]);
	cyrus_socket_error($buffer,"$re[3]");
	return null;
}

if(preg_match('#lmtp.+?:(.+?):\s+to=<(.+?)>.+?said: 550-Mailbox unknown#',$buffer,$re)){
	event_message_milter_reject($re[1],"Mailbox unknown",null,$re[2]);
	mailbox_unknown($buffer,$re[2]);
	return null;
}

if(preg_match('#cyrus.+?:DBERROR.+?DB_VERSION_MISMATCH#',$buffer,$re)){
	cyrus_database_error($buffer);
	return null;
}


events("Not Filtered:\"$buffer\"");	
}





function events($text){
		$pid=@getmypid();
		$date=@date("H:i:s");
		$logFile="/var/log/artica-postfix/postfix-logger.debug";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "[$pid] $date $text\n");
		@fclose($f);	
		}
		
function event_Content_scanner_malfunction($postfix_id,$from,$to){
	$file="/var/log/artica-postfix/RTM/$postfix_id.msg";
	$ini=new Bs_IniHandler($file);
	$ini->set("TIME","mailfrom","$from");
	$ini->set("TIME","mailto","$to");
	$ini->set("TIME","bounce_error","Content scanner malfunction");
	$ini->set("TIME","time_end",date("Y-m-d H:i:s"));
	$ini->set("TIME","delivery_success","no");
	$ini->saveFile($file);	
}
function event_DISCARD($postfix_id,$from,$to,$buffer=null){
	$file="/var/log/artica-postfix/RTM/$postfix_id.msg";
	$ini=new Bs_IniHandler($file);
	
	if(preg_match("#from.+?\[([0-9\.]+)?\]#",$buffer,$re)){$ini->set("TIME","smtp_sender",$re[1]);}	
	
	$ini->set("TIME","mailfrom","$from");
	$ini->set("TIME","mailto","$to");
	$ini->set("TIME","bounce_error","Discard");
	$ini->set("TIME","time_end",date("Y-m-d H:i:s"));
	$ini->set("TIME","delivery_success","no");
	$ini->saveFile($file);	
}
function event_newmail($postfix_id,$date){
	$file="/var/log/artica-postfix/RTM/$postfix_id.msg";
	$ini=new Bs_IniHandler($file);
	$ini->set("TIME","time_connect",$date);
	$ini->set("TIME","delivery_success","no");
	$ini->saveFile($file);	
}
function event_message_from($postfix_id,$from,$size){
	$file="/var/log/artica-postfix/RTM/$postfix_id.msg";
	$ini=new Bs_IniHandler($file);
	$ini->set("TIME","mailfrom",$from);
	$ini->set("TIME","mailsize",$size);
	$ini->set("TIME","time_connect",date("Y-m-d H:i:s"));
	$ini->set("TIME","delivery_success","no");
	$ini->saveFile($file);	
}
function event_message_milter_reject($postfix_id,$reject,$from,$to=null,$buffer=null,$sender=null){
	$file="/var/log/artica-postfix/RTM/$postfix_id.msg";
	$ini=new Bs_IniHandler($file);
	if($sender==null){
		if(preg_match("#from.+?\[([0-9\.]+)?\]#",$buffer,$re)){$ini->set("TIME","smtp_sender",$re[1]);}	
		if(preg_match("#assp\[.+?\]:\s+.+?\s+(.+?)\s+<#",$buffer,$re)){$ini->set("TIME","smtp_sender",$re[1]);}
	}
	if($to<>null){$ini->set("TIME","mailto",$to);}
	if($from<>null){$ini->set("TIME","mailfrom",$from);}
	$ini->set("TIME","bounce_error",$reject);
	$ini->set("TIME","time_connect",date("Y-m-d H:i:s"));
	$ini->set("TIME","delivery_success","no");
	$ini->saveFile($file);	
}

function event_message_reject_hostname($reject,$from,$to=null,$server){
	$file="/var/log/artica-postfix/RTM/".md5(date("Y-m-d H:i:s").$server.$from).".msg";
	$ini=new Bs_IniHandler($file);
	$ini->set("TIME","smtp_sender",$server);	
	if($to<>null){$ini->set("TIME","mailto",$to);}
	if($from<>null){$ini->set("TIME","mailfrom",$from);}
	$ini->set("TIME","bounce_error",$reject);
	$ini->set("TIME","time_connect",date("Y-m-d H:i:s"));
	$ini->set("TIME","delivery_success","no");
	$ini->saveFile($file);	
}


function event_messageid_rejected($msg_id_postfix,$error,$server=null,$to=null){
	$file="/var/log/artica-postfix/RTM/$msg_id_postfix.msg";
	$ini=new Bs_IniHandler($file);
	if($server<>null){$ini->set("TIME","smtp_sender",$server);}
	if($to<>null){$ini->set("TIME","mailto",$to);}
	$ini->set("TIME","delivery_success","no");
	$ini->set("TIME","bounce_error",$error);
	$ini->set("TIME","time_connect",date("Y-m-d H:i:s"));
	$ini->saveFile($file);		
}

function event_message_rejected($reject,$msg_id_postfix,$to=null,$buffer){
	$file="/var/log/artica-postfix/RTM/$msg_id_postfix.msg";
	$ini=new Bs_IniHandler($file);
	
	if(preg_match("#invalid sender domain#",$buffer)){
		$reject="Invalid sender domain";
	}
	
	if(preg_match("#^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$buffer)){
		$ini->set("TIME","server_from","$buffer");
	}
	
	if($to<>null){$ini->set("TIME","mailto",$to);}
	
	$ini->set("TIME","bounce_error",$reject);
	$ini->set("TIME","time_connect",date("Y-m-d H:i:s"));
	$ini->set("TIME","delivery_success","no");
	$ini->saveFile($file);	
}

function event_message_id($postfix_id,$messageid){
	$file="/var/log/artica-postfix/RTM/$postfix_id.msg";
	$ini=new Bs_IniHandler($file);
	$ini->set("TIME","message-id","$messageid");
	$ini->set("TIME","time_connect",date("Y-m-d H:i:s"));
	$ini->saveFile($file);		
}
		
function event_greylisted($server,$from){
	$file="/var/log/artica-postfix/RTM/".md5(date("Y-m-d H:i:s").$server.$from).".msg";
	$ini=new Bs_IniHandler($file);
	$ini->set("TIME","mailfrom","$from");
	$ini->set("TIME","server_from","$server");
	$ini->set("TIME","bounce_error","greylisted");
	$ini->set("TIME","time_end",date("Y-m-d H:i:s"));
	$ini->set("TIME","delivery_success","no");
	$ini->saveFile($file);
}
function event_finish($postfix_id,$to,$status,$bounce_error,$from=null,$buffer=null){
 
    $delivery_success='yes';
    if($status='bounced'){$delivery_success='no';}
	if($status='deferred'){$delivery_success='no';}
	if($status='reject'){$delivery_success='no';}
    
	if(preg_match("#Queued mail for delivery#",$bounce_error)){
		$status="Deliver";
		$delivery_success="yes";
		$bounce_error="Sended";
	}
	
	if(preg_match("#Sender address rejected: need fully-qualified address#",$bounce_error)){
		$status="rejected";
		$delivery_success="no";
		$bounce_error="need fully-qualified address";
	}
	
	if(preg_match("#no mailbox here#",$bounce_error)){
		$status="rejected";
		$delivery_success="no";
		$bounce_error="Mailbox Unknown";
	}	
	
	if(preg_match("#refused to talk to me.+?RBL rejection#",$bounce_error)){
			$status="rejected";
			$delivery_success="no";
			$bounce_error="RBL";
	}

	if(preg_match("#550.+?Service unavailable.+?blocked using.+?RBL#",$bounce_error)){
			$status="rejected";
			$delivery_success="no";
			$bounce_error="RBL";
	}
	
	
	
	if(preg_match("#delivered via#",$bounce_error)){
		$status="Deliver";
		$delivery_success="yes";
		$bounce_error="Sended";
	}
	
	
	if(preg_match("#Content scanner malfunction#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="Content scanner malfunction";
	}
	
	if(preg_match("#4\.5\.0 Failure#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="Error";
	}	
	
	
	if(preg_match("#250 2\.0\.0 Ok#",$bounce_error)){
		$status="Deliver";
		$delivery_success="yes";
		$bounce_error="Sended";
	}
	
	if(preg_match("#Host or domain name not found#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="Host or domain name not found";
	}
	
	
	if(preg_match("#4\.5\.0 Error in processing#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="Error";
	};
	
if(preg_match("#Sender address rejected.+?Domain not found#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="Domain not found";
	};	
	
if(preg_match("#delivered to command: procmail -a#",$bounce_error)){
		$status="Deliver";
		$delivery_success="yes";
		$bounce_error="Sent to procmail";
	};

if(preg_match("#550 must be authenticated#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="Authentication error";
	};	

if(preg_match("#250 Message.+?accepted by#",$bounce_error)){
		$status="Deliver";
		$delivery_success="yes";
		$bounce_error="Sended";
	};		
	
	
if(preg_match("#Connection timed out#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="timed out";
	};	
	
if(preg_match("#connect\s+to.+?Connection refused#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="Connection refused";		
}

if(preg_match("#temporary failure.+?artica-msmtp:\s+recipient address\s+(.+?)\s+not accepted by the server artica-msmtp#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="artica-filter error";		
}
		
	if(preg_match("#250 2\.1\.5 Ok#",$bounce_error)){
		$status="Deliver";
		$delivery_success="yes";
		$bounce_error="Sended";
	}
	
	if($bounce_error=="250 OK: data received"){
		$status="Deliver";
		$delivery_success="yes";
		$bounce_error="Sended";		
	}
	
	if($bounce_error=="250 Ok: queued as"){
			$status="Deliver";
			$delivery_success="yes";
			$bounce_error="Sended";		
		}


	
	
	if(preg_match("#504.+?Recipient address rejected#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="recipient address rejected";
		
	}
	
if(preg_match("#Address rejected#",$bounce_error)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="Address rejected";
			}

if(preg_match("#conversation with .+?timed out#",$bounce_error)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="timed out";
			}	

if(preg_match("#connect to\s+(.+?)\[.+?cyrus.+?lmtp\]: Connection refused#",$bounce_error)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="mailbox service error";
			cyrus_generic_error($bounce_error,"Cyrus socket error");	
			}
	
if(preg_match("#host.+?\[(.+?)\]\s+said:.+?<(.+?)>: Recipient address rejected: User unknown in local recipient table#",$bounce_error)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="User unknown";
			$to=$re[2];
			}
			
			
			

if($delivery_success=="no"){
			if($bounce_error=="User unknown in relay recipient table"){$bounce_error="User unknown";}
			
	    	events("event_finish() line ".__LINE__. " bounce_error=$bounce_error");
	    	if(preg_match("#connect to.+?\[(.+?)lmtp\].+?No such file or directory#",$bounce_error,$ra)){
	    		events("Cyrus error found -> CyrusSocketErrot");
	    		cyrus_socket_error($bounce_error,$ra[1].'/lmtp');
	    		}
	    	if(preg_match("#550\s+User\s+unknown\s+<(.+?)>.+?in reply to RCPT TO command#",$bounce_error,$ra)){mailbox_unknown($bounce_error,$ra[1]);}
	    }
    
	$file="/var/log/artica-postfix/RTM/$postfix_id.msg";
	$ini=new Bs_IniHandler($file);
	if(preg_match("#from.+?\[([0-9\.]+)?\]#",$buffer,$re)){$ini->set("TIME","smtp_sender",$re[1]);}	
	if($from<>null){$ini->set("TIME","mailfrom",$from);}
	$ini->set("TIME","mailto","$to");
	$ini->set("TIME","bounce_error","$bounce_error");
	$ini->set("TIME","time_end",date("Y-m-d H:i:s"));
	$ini->set("TIME","delivery_success","$delivery_success");
	
	events("event_finish() [$postfix_id]: $from => $to err=$bounce_error success=$delivery_success");
	
	$ini->saveFile($file);	    
       
	
}

function cyrus_imap_conx($service,$server,$server_ip,$user){
	$date=date('Y-m-d H:i:s');
	events("imap connection $user from ($server_ip)");
	$sql="INSERT INTO mbx_con (`zDate`,`mbx_service`,`client_name`,`client_ip`,`uid`,`imap_server`)
	VALUES('$date','$service','$server','$server_ip','$user','{$_GET["server"]}')";
	$md5=md5($sql);
	@mkdir("/var/log/artica-postfix/IMAP",0750,true);
	$file="/var/log/artica-postfix/IMAP/$md5.sql";
	@file_put_contents($file,$sql);
}


function CyrusSocketErrot(){
	
	
}

function _MonthToInteger($month){
  $zText=$month;	
  $zText=str_replace('JAN', '01',$zText);
  $zText=str_replace('FEB', '02',$zText);
  $zText=str_replace('MAR', '03',$zText);
  $zText=str_replace('APR', '04',$zText);
  $zText=str_replace('MAY', '05',$zText);
  $zText=str_replace('JUN', '06',$zText);
  $zText=str_replace('JUL', '07',$zText);
  $zText=str_replace('AUG', '08',$zText);
  $zText=str_replace('SEP', '09',$zText);
  $zText=str_replace('OCT', '10',$zText);
  $zText=str_replace('NOV', '11',$zText);
  $zText=str_replace('DEC', '12',$zText);
  return $zText;	
}
function email_events($subject,$text,$context){
	send_email_events($subject,$text,$context);
	}
	
function interface_events($product,$line){
	$ini=new Bs_IniHandler();
	if(is_file("/usr/share/artica-postfix/ressources/logs/interface.events")){
		$ini->loadFile("/usr/share/artica-postfix/ressources/logs/interface.events");
	}
	$ini->set($product,'error',$line);
	$ini->saveFile("/usr/share/artica-postfix/ressources/logs/interface.events");
	@chmod("/usr/share/artica-postfix/ressources/logs/interface.events",0755);
	
}



function amavis_socket_error($line){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	$ftime=file_time_min($file);
	if($ftime<15){
		events("Unable to process new operation for amavis...waiting 15mn (current {$ftime}mn)");
		return null;
	}
	THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart amavis");
	email_events("Warning Amavis socket is not available",$line." (Postfix claim that amavis socket is not available, 
	Artica will restart amavis service)","postfix");
	@unlink($file);
	@mkdir("/etc/artica-postfix/cron.1");
	@file_put_contents($file,"#");	
}

function mailbox_unknown($line,$to){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.'.'.md5($to);
	if(file_time_min($file)<15){return null;}
	email_events("Warning unknown mailbox $to","Postfix claim that $to mailbox is not available you should create this alias or mailbox $line","mailbox");
	
}

function cyrus_bad_seen($fileseen){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	$fileseen=$fileseen.".seen";
	if(file_time_min($file)<15){return null;}
	email_events('Warning Corrupted mailbox detected','Cyrus claim that '.$fileseen.'is corrupted, Artica will delete this file to repair it','mailbox');
    THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-backup --repair-seen-file $fileseen");
	@unlink($file);
	file_put_contents($file,"#");
 }
 
function amavis_error_restart($buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	email_events('Warning Amavis error',"Amavis claim that $buffer, Artica will restart amavis",'postfix');
	THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart amavis");
	@unlink($file);
	file_put_contents($file,"#");	
	}
	
	function clamav_error_restart($buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	email_events('Warning Clamad error',"Postfix claim that $buffer, Artica will restart clamav",'postfix');
	THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart clamd");
	@unlink($file);
	file_put_contents($file,"#");	
	}	
	
function kas_error_update($buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	email_events('Kaspersky Anti-spam report failure when updating it`s database',"for your information: $buffer",'postfix');
	THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart amavis");
	@unlink($file);
	file_put_contents($file,"#");	
	}
function cyrus_db_error($buffer,$dbfile){
	$dbfile=strim($dbfile);
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	$stime=date('YmdHis');
	$b_path="$dbfile.bak.$stime";
	@unlink($dbfile);
	THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart imap');
	email_events("Warning cyrus db error on $dbfile","cyrus-imap claim: $buffer file will be backuped to",'mailbox');
	@unlink($file);
	file_put_contents($file,"#");	
	}
function cyrus_generic_error($buffer,$subject){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	events("Cyrus error !! $buffer (cache=$file)");
	email_events("cyrus-imapd error: $subject","$buffer, Artica will restart cyrus",'mailbox');
	THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart imap");
	@unlink($file);
	file_put_contents($file,"#");
	
}

function cyrus_generic_reconfigure($buffer,$subject){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	events("Cyrus error !! $buffer (cache=$file)");
	email_events("cyrus-imapd error: $subject","$buffer, Artica will reconfigure cyrus",'mailbox');
	THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reconfigure-cyrus");
	@unlink($file);
	file_put_contents($file,"#");	
	
}


function cyrus_sieve_error($file){
	THREAD_COMMAND_SET("/bin/touch $file");
}

function cyrus_socket_error($buffer,$socket){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	email_events("cyrus-imapd socket error: $socket","Postfix claim \"$buffer\", Artica will restart cyrus",'mailbox');
	THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --cyrus-checkconfig');
	THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart imap');
	@unlink($file);
	@file_put_contents($file,"#");
}

function MilterSpamAssassinError($buffer,$socket){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	email_events("spamassin-milter socket error: $socket","Postfix claim \"$buffer\", Artica will reload Postfix and compile new Postfix settings",'postfix');
	THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --postfix-reload');
	@unlink($file);
	@file_put_contents($file,"#");	
}


function AmavisConfigErrorInPostfix($buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	$timeFile=file_time_min($file);
	if($timeFile<15){
		events("*** $buffer ****");
		events("amavisd-new socket no operations, blocked by timefile $timeFile Mn!!!");
		return null;}	
	events("amavisd-new socket error time:$timeFile Mn!!!");
	email_events("amavisd-new socket error","Postfix claim \"$buffer\", Artica will reload Postfix and compile new Postfix settings",'postfix');
	THREAD_COMMAND_SET(LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.postfix.maincf.php --reconfigure");
	THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart amavis');
	THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --postfix-reload');
	@unlink($file);
	@file_put_contents($file,"#");	
	if(!is_file($file)){
		events("error writing time file:$file");
	}
}

function SpamAssassin_error_saupdate($buffer){
$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	$timeFile=file_time_min($file);
	if($timeFile<15){
		events("*** $buffer ****");
		events("Spamassassin no operations, blocked by timefile $timeFile Mn!!!");
		return null;}	
	events("Spamassassin error time:$timeFile Mn!!!");
	email_events("SpamAssassin error Regex","SpamAssassin claim \"$buffer\", Artica will run /usr/bin/sa-update to fix it",'postfix');
	THREAD_COMMAND_SET("/usr/bin/sa-update");
	@unlink($file);
	@file_put_contents($file,"#");	
	if(!is_file($file)){
		events("error writing time file:$file");
	}	
}

function miltergreylist_error($buffer,$socket){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	email_events("Milter Greylist error: $socket","System claim \"$buffer\", Artica will restart milter-greylist",'postfix');
	THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart mgreylist');
	@unlink($file);
	@file_put_contents($file,"#");
}

function cyrus_database_error($buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}
	email_events("cyrus-imapd FATAL error !! database engine is incompatible reinstall mailbox system !","Cyrus claim: $buffer",'mailbox');
	interface_events("APP_CYRUS_IMAP",$buffer);
	@unlink($file);
	@file_put_contents($file,"#");
}

function MilterClamavError($buffer,$socket){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	email_events("Milter-clamav socket error: $socket","Postfix claim \"$buffer\", 
	Artica will grant postfix to this socket\but you can use amavis instead that will handle clamav antivirus scanner too",'postfix');
	THREAD_COMMAND_SET("/bin/chmod -R 775 ". dirname($socket));
	THREAD_COMMAND_SET("/bin/chown -R postfix:postfix ". dirname($socket));
	THREAD_COMMAND_SET("postqueue -f");
	@unlink($file);
	@file_put_contents($file,"#");	
	
}
function AmavisConfigErrorInPostfixRestart($buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	email_events("Amavis network error: $socket","Postfix claim \"$buffer\", Artica will restart postfix",'postfix');
	THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart postfix");
	@unlink($file);
	@file_put_contents($file,"#");		
}
function ImBlackListed($server,$buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.".".md5($server);
	if(file_time_min($file)<15){return null;}	
	email_events("Your are blacklisted from $server","Postfix claim \"$buffer\", try to investigate why or contact our technical support",'postfix');
	@unlink($file);
	@file_put_contents($file,"#");		
}


function postfix_compile_db($hash_file,$buffer){
	$unix=new unix();
	events("DB Problem -> $hash_file");
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.".".md5($hash_file);
	if(file_time_min($file)<5){return null;}
	
	if(!is_file($hash_file)){
		@file_put_contents($hash_file,"#");
	}
	email_events("Postfix Database problem","Postfix claim \"$buffer\", Artica will recompile ".basename($hash_file),'postfix');
	$cmd=$unix->find_program("postmap"). " hash:$hash_file";
	THREAD_COMMAND_SET($cmd);
	events("DB Problem -> $hash_file -> $cmd");
	THREAD_COMMAND_SET($unix->find_program("postfix"). " reload");		
	@unlink($file);
	@file_put_contents($file,"#");		
	
}

function postfix_compile_missing_db($hash_file,$buffer){
	$unix=new unix();
	events("DB Problem -> $hash_file");
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.".".md5($hash_file);
	if(file_time_min($file)<5){return null;}
	
	if(!is_file($hash_file)){
		@file_put_contents($hash_file,"#");
	}
	
	email_events("Postfix Database problem","Postfix claim \"$buffer\", Artica will create blanck file and recompile ".basename($hash_file),'postfix');
	$cmd=$unix->find_program("postmap"). " hash:$hash_file";
	THREAD_COMMAND_SET($cmd);
	events("DB Problem -> $hash_file -> $cmd");
	THREAD_COMMAND_SET($unix->find_program("postfix"). " reload");		
	@unlink($file);
	@file_put_contents($file,"#");		
	
}

function cyrus_bad_login($router,$ip,$user,$error){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.".".md5("$router,$ip,$user,$error");
	if(file_time_min($file)<15){return null;}	
	@unlink($file);
	email_events("User $user cannot login to mailbox","cyrus claim \"$error\" for $user (router:$router, ip:$ip),
	 please,send the right password to $user",'mailbox');
	@file_put_contents($file,"#");		
}

function smtp_sasl_failed($router,$ip,$buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.".".md5("$router,$ip");
	if(file_time_min($file)<15){return null;}
	@unlink($file);
	email_events("SMTP authentication failed from $router","Postfix claim \"$buffer\" for ip address $ip",'postfix');
	@file_put_contents($file,"#");		
}

function kavmilter_expired($buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.".expired";
	if(file_time_min($file)<15){return null;}
	@unlink($file);
	@file_put_contents("/etc/artica-postfix/settings/Daemons/kavmilterEnable","0");
	$cmd=LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.postfix.maincf.php --reconfigure";
	events("$cmd");
	THREAD_COMMAND_SET($cmd);
	THREAD_COMMAND_SET("/etc/init.d/artica-postfix stop kavmilter");
	email_events("Kaspersky For Mail server, license expired","Postfix claim \"$buffer\" Artica will disable Kaspersky and restart postfix",'postfix');
	@file_put_contents($file,"#");
	}

function hackPOP($ip,$logon,$buffer){
	if($GLOBALS["PopHackEnabled"]==0){return;}
	$file="/etc/artica-postfix/croned.1/postfix.hackPop3.error";
	if($ip=="127.0.0.1"){return;}
	$GLOBALS["POP_HACK"][$ip]=$GLOBALS["POP_HACK"][$ip]+1;
	$count=$GLOBALS["POP_HACK"][$ip];
	events("POP HACK {$ip} email={$logon} $count/{$GLOBALS["PopHackCount"]} failed");

	if(file_time_min($file)>10){
			email_events("POPHACK {$ip}/{$logon} $count/{$GLOBALS["PopHackCount"]} failed",
			"Mailbox server claim $buffer\nAfter ( $count/{$GLOBALS["PopHackCount"]}) {$GLOBALS["PopHackCount"]} times failed, 
			a firewall rule will added","mailbox");
			@unlink($file);
		}else{
			events("User not found for mailbox {$ip}/{$logon} $count/{$GLOBALS["PopHackCount"]} failed");
		}	
	
	if($GLOBALS["POP_HACK"][$ip]>=$GLOBALS["PopHackCount"]){
		shell_exec("iptables -I INPUT -s {$ip} -j DROP");
		events("POP HACK RULE CREATED {$ip} $count/{$GLOBALS["PopHackCount"]} failed");
		email_events("HACK pop3 from {$ip}","A firewall rule has been created and this IP:{$ip} is now denied ","mailbox");
		unset($GLOBALS["POP_HACK"][$ip]);
	}
	file_put_contents($file,"#");	
}


function zarafa_store_error(){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.".store.error";
	if(file_time_min($file)<15){return null;}
	@unlink($file);
	$cmd=LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.zarafa.build.stores.php";
	events("$cmd");
	THREAD_COMMAND_SET($cmd);
	email_events("Zarafa mailbox server store error","Zarafa claim \"$buffer\" Artica will try to reactivate stores and accounts",'mailbox');
	@file_put_contents($file,"#");	
}


?>
