#!/usr/bin/php
<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.domains.diclaimers.inc');
include_once(dirname(__FILE__).'/ressources/class.mail.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/ressources/class.smtp.sockets.inc');

define( 'EX_TEMPFAIL', 75 );
define( 'EX_UNAVAILABLE', 69 );
define( RM_STATE_READING_HEADER, 1 );
define( RM_STATE_READING_FROM,   2 );
define( RM_STATE_READING_SUBJECT,3 );
define( RM_STATE_READING_SENDER, 4 );
define( RM_STATE_READING_BODY,   5 );
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;echo "verbose=true;\n";}
if($argv[1]=='--disclaimer-domain'){CheckDisclaimerTest($argv[2]);die();}
if($argv[1]=='--disclaimer-uid'){CheckDisclaimerTestUid($argv[2]);die();}
if($argv[1]=='--vacation-uid'){CheckOutOfOffice($argv[2],$argv[3]);die();}



events("receive: " . implode(" ",$argv),"main",__LINE__);

$options = parse_args( array( 's', 'r', 'c', 'h', 'u' ), $_SERVER['argv']); //getopt("s:r:c:h:u:");

if (!array_key_exists('r', $options) || !array_key_exists('s', $options)) {
    fwrite(STDOUT, "Usage is $argv[0] -s sender@domain -r recip@domain\n");
    exit(EX_TEMPFAIL);
}

$tmpfname = tempnam( "/var/lib/artica/mail/filter", 'IN.' );
$tmpf = @fopen($tmpfname, "w");
if( !$tmpf ) {
  writelogs("Error: Could not open $tempfname for writing: ".php_error(), "main",__FILE__,__LINE__);
  exit(EX_TEMPFAIL);
}


$GLOBALS["sender"]= strtolower($options['s']);
$GLOBALS["recipients"] = $options['r'];
$GLOBALS["original_recipient"]=$options['r'];
$client_address = $options['c'];
$smtp_final_sender = strtolower($options['h']);
$sasl_username = strtolower($options['u']);

// make sure recipients is an array
if( !is_array($GLOBALS["recipients"]) ) {
  $GLOBALS["recipients"] = array( $GLOBALS["recipients"] );
}

// make recipients lowercase
for( $i = 0; $i < count($GLOBALS["recipients"]); $i++ ) {
  $GLOBALS["recipients"][$i] = strtolower($GLOBALS["recipients"][$i]);
}

events("starting up, [$fqhostname] user=$sasl_username, sender=$sender, recipients=".join(',', $GLOBALS["recipients"]).", client_address=$client_address", "main",__LINE__);

$ical = false;
$from = false;
$subject = false;
$senderok = true;
$rewrittenfrom = false;
$state = RM_STATE_READING_HEADER;
while (!feof(STDIN) && $state != RM_STATE_READING_BODY) {
  $buffer = fgets(STDIN, 8192);
  $headers=$headers.$buffer;
  $line = rtrim( $buffer, "\r\n");
  if( $line == '' ) {
    // Done with headers
    $state = RM_STATE_READING_BODY;
	} else {
    if( $line[0] != ' ' && $line[0] != "\t" ) $state = RM_STATE_READING_HEADER;
    switch( $state ) {
    case RM_STATE_READING_HEADER:
    	//events("Receive \"$line\"");
    	//events($line,"main",__LINE__);
    	if(preg_match("#CC:\s+(.*)#",$line,$regs)){
    		$recpt=explode(",",$regs[1]);
    		for( $i = 0; $i < count($recpt); $i++ ) {
    			
    			$GLOBALS["CC"][] = strtolower($recpt[$i]);
    		}
    	}
    
      if( $params['allow_sender_header'] && preg_match( '#^Sender: (.*)#i', $line, $regs ) ) {
			$from = $regs[1];
			$state = RM_STATE_READING_SENDER;
      		} else if( !$from && preg_match( '#^From: (.*)#i', $line, $regs ) ) {
				$from = $regs[1];
				$state = RM_STATE_READING_FROM;
      			} else if( preg_match( '#^Subject: (.*)#i', $line, $regs ) ) {
					$subject = $regs[1];
					$state = RM_STATE_READING_SUBJECT;
      				}

      break;
    case RM_STATE_READING_FROM:
      $from .= $line;
      break;
    case RM_STATE_READING_SENDER:
      $from .= $line;
      break;
    case RM_STATE_READING_SUBJECT:
      $subject .= $line;
      break;
    }
  }
  if( fwrite($tmpf, $buffer) === false ) {
    exit(EX_TEMPFAIL);
  }
}



while (!feof(STDIN)) {
  $buffer = fread( STDIN, 8192 );
  if( fwrite($tmpf, $buffer) === false ) {
    exit(EX_TEMPFAIL);
  }
}
fclose($tmpf);
$unix=new unix();
$send_result_file=$unix->FILE_TEMP();
for( $i = 0; $i < count($GLOBALS["recipients"]); $i++ ) {
	CheckOutOfOffice($GLOBALS["recipients"][$i],$GLOBALS["sender"],$subject);
	CheckDisclaimerGlobal($GLOBALS["sender"],$GLOBALS["recipients"][$i],$tmpfname);
	}


$mailsize=@filesize($tmpfname);
$unix=new unix();



if($smtp_final_sender==null){$smtp_final_sender="127.0.0.1";}

$smtp_sock=new SMTP_SOCKETS();
$smtp_sock->myhostname=$unix->hostname_g();
if(!$smtp_sock->SendSMTPMailFromPath($smtp_final_sender,"33559",
		$GLOBALS["sender"],$GLOBALS["original_recipient"],$tmpfname)){
		events("error:smtp_sock ERROR".@implode("\n",$smtp_sock->error),"main",__LINE__);
		exit(EX_TEMPFAIL);
	}
WriteToSyslogMail("from=<{$GLOBALS["sender"]}> to: <{$GLOBALS["original_recipient"]}> success delivered trough $smtp_final_sender:33559","artica-filter");	
events("Success", "main",__LINE__);
exit(0);



function parse_args( $opts, $args ){
  $ret = array();
  for( $i = 0; $i < count($args); ++$i ) {
    $arg = $args[$i];
    if( $arg[0] == '-' ) {
      if( in_array( $arg[1], $opts ) ) {
	$val = array();
	$i++;
	while( $i < count($args) && $args[$i][0] != '-' ) {
	  $val[] = $args[$i];
	  $i++;
	}
	$i--;
	if( array_key_exists($arg[1],$ret) && is_array( $ret[$arg[1]] ) ) $ret[$arg[1]] = array_merge((array)$ret[$arg[1]] ,(array)$val);
	else if( count($val) == 1 ) $ret[$arg[1]] = $val[0];
	else $ret[$arg[1]] = $val;
      }
    }
  }
  return $ret;
}

function CheckDisclaimerGlobal($sender,$recipient,$temp_file){
	
	if(!is_file("/usr/local/bin/altermime")){
		events("AlterMime is not installed",__FUNCTION__,__LINE__);
		return true;
	}
	
	$sock=new sockets();
	$EnableAlterMime=$sock->GET_INFO("EnableAlterMime");
	
	if($EnableAlterMime==0){
		events("AlterMime is disabled",__FUNCTION__,__LINE__);
		return true;
	}
	
	$DisclaimerOrgOverwrite=$sock->GET_INFO("DisclaimerOrgOverwrite");
	if($DisclaimerOrgOverwrite==1){
		if(CheckDisclaimerOrg_Recipient($sender,$recipient,$temp_file)){return true;}
		if(CheckDisclaimerOrg_Sender($sender,$recipient,$temp_file)){return true;}		
	}
	
	if(!preg_match("#(.+?)@(.+)#",$recipient,$re)){
		events("Unable to preg_match recipient domain",__FUNCTION__,__LINE__);
		return false;
	}
	
	$recipient_domain=trim($re[2]);
	$generic_disclaimer=$sock->GET_INFO("AlterMimeHTMLDisclaimer");
	$DisclaimerOutbound=$sock->GET_INFO("DisclaimerOutbound");
	$DisclaimerInbound=$sock->GET_INFO("DisclaimerInbound");
	
	$DisclaimerOrgOverwrite=$sock->GET_INFO("DisclaimerInbound");
	
	if($DisclaimerOutbound==null){$DisclaimerOutbound=1;}
	if($DisclaimerInbound==null){$DisclaimerInbound=0;}	
	
	$ldap=new clladp();
	$domains=$ldap->hash_get_all_domains();
	if($domains[$recipient_domain]<>null){
		
		if($DisclaimerInbound==0){
			return false;
			events("$recipient_domain is a local domain, skip disclaimer (Inbound is disabled)",__FUNCTION__,__LINE__);
		}
		
		if($DisclaimerOutbound==0){
			return false;
			events("$recipient_domain is a foregin domain, skip disclaimer (Outbound is disabled)",__FUNCTION__,__LINE__);
		}
		
	}else{
		if($DisclaimerInbound==0){
			return false;
			events("$recipient_domain is a local domain, skip disclaimer (Inbound is disabled)",__FUNCTION__,__LINE__);
		}
	}
	
	WriteDisclaimer($generic_disclaimer,$temp_file);
	return true;
	
}

function CheckDisclaimerOrg_Sender($sender,$recipient,$temp_file){
if(preg_match("#(.+?)@(.+)#",$recipient,$re)){
		$recipient_domain=trim(strtolower($re[2]));
	}
	if($recipient_domain==null){return false;}

if(preg_match("#(.+?)@(.+)#",$sender,$re)){
		$sender_domain=trim(strtolower($re[2]));
	}
	if($sender_domain==null){return false;}	
	
	
$dd=new domains_disclaimer(null,$sender_domain);
events("<$sender_domain>: DisclaimerActivate=$dd->DisclaimerActivate,DisclaimerOutbound=$dd->DisclaimerOutbound; $dd->error",__FUNCTION__,__LINE__);
	if($dd->DisclaimerUserOverwrite=="TRUE"){
		if(CheckDisclaimerUser_Recipient($sender,$recipient,$temp_file)){return true;}
		if(CheckDisclaimerUser_Sender($sender,$recipient,$temp_file)){return true;}
	}

if($dd->DisclaimerActivate=="TRUE"){
	if($dd->DisclaimerOutbound=="TRUE"){
		if($recipient_domain<>$sender_domain){
			$writedisclaimer=true;
		}
	}
}

if(!$writedisclaimer){
	events("<$sender_domain>: FALSE",__FUNCTION__,__LINE__);
	return false;}
WriteDisclaimer($dd->DisclaimerContent,$temp_file);
return true;	
}
function CheckDisclaimerOrg_Recipient($sender,$recipient,$temp_file){
if(preg_match("#(.+?)@(.+)#",$recipient,$re)){
		$recipient_domain=trim(strtolower($re[2]));
	}
	if($recipient_domain==null){return false;}

if(preg_match("#(.+?)@(.+)#",$sender,$re)){
		$sender_domain=trim(strtolower($re[2]));
	}
	if($sender_domain==null){return false;}	
	
	
	$dd=new domains_disclaimer(null,$recipient_domain);
	events("<$recipient_domain>: DisclaimerActivate=$dd->DisclaimerActivate,DisclaimerInbound=$dd->DisclaimerInbound",__FUNCTION__,__LINE__);
	if($dd->DisclaimerUserOverwrite=="TRUE"){
		if(CheckDisclaimerUser_Recipient($sender,$recipient,$temp_file)){return true;}
		if(CheckDisclaimerUser_Sender($sender,$recipient,$temp_file)){return true;}
	}
	
	
	if($dd->DisclaimerActivate=="TRUE"){
		if($dd->DisclaimerInbound=="TRUE"){
			$writedisclaimer=true;
			}
	}


if(!$writedisclaimer){
	events("<$recipient_domain>: FALSE",__FUNCTION__,__LINE__);
	return false;
}
WriteDisclaimer($dd->DisclaimerContent,$temp_file);
return true;
	
}
function CheckDisclaimerUser_Sender($sender,$recipient,$temp_file){
	
	$ldap=new clladp();
	$uid=$ldap->uid_from_email($sender);
	
	if($uid==null){return false;}	
	$GLOBALS["USERS"][$sender]["uid"]=$uid;
	$dd=new user_disclaimer(null,$uid);
	events("<$uid>: DisclaimerActivate=$dd->DisclaimerActivate,DisclaimerOutbound=$dd->DisclaimerOutbound",__FUNCTION__);
	if($dd->DisclaimerActivate=="TRUE"){
		if($dd->DisclaimerOutbound=="TRUE"){
			$uid=$ldap->uid_from_email($recipient);
			if($uid==null){$writedisclaimer=true;}
			}
	}


if(!$writedisclaimer){
	events("<$uid>: FALSE",__FUNCTION__);
	return false;
}
WriteDisclaimer($dd->DisclaimerContent,$temp_file);
return true;
	
}
function CheckDisclaimerUser_Recipient($sender,$recipient,$temp_file){
	if($GLOBALS["uid"][$recipient]=="NO"){return false;}
	$ldap=new clladp();
	
	if($GLOBALS["uid"][$recipient]<>null){
		$uid=$GLOBALS["uid"][$recipient];
	}else{
		$uid=$ldap->uid_from_email($recipient);
	}
	
	$GLOBALS["USERS"][$recipient]["uid"]=$uid;
	if($uid==null){return false;}
	
	$dd=new user_disclaimer(null,$uid);
	events("<$uid>: DisclaimerActivate=$dd->DisclaimerActivate,DisclaimerInbound=$dd->DisclaimerInbound",__FUNCTION__,__LINE__);
	if($dd->DisclaimerActivate=="TRUE"){
		if($dd->DisclaimerInbound=="TRUE"){
			$writedisclaimer=true;
			}
	}


if(!$writedisclaimer){
	events("<$uid>: FALSE",__FUNCTION__);
	return false;
}
WriteDisclaimer($dd->DisclaimerContent,$temp_file);
return true;
	
}
function WriteDisclaimer($text,$temp_file){
$unix=new unix();
	$text=stripslashes($text);
	$tmp_disclaimer=$unix->FILE_TEMP();
	$tmp_disclaimer2=$unix->FILE_TEMP().".txt";
	@file_put_contents($tmp_disclaimer,$text);
	$disctxt=br2nl($text);
	$disctxt=p2nl($disctxt);
	$disctxt=strip_tags($disctxt);
	$disctxt=html_entity_decode($disctxt);	
	@file_put_contents($tmp_disclaimer2,$disctxt);
	events("$temp_file write disclaimer ". strlen($text)." bytes",__FUNCTION__,__LINE__);
	$cmd="/usr/local/bin/altermime --input=$temp_file --log-syslog --disclaimer=$tmp_disclaimer2 --disclaimer-html=$tmp_disclaimer --xheader=X-Copyrighted-Material:";
	shell_exec($cmd);	
	@unlink($tmp_disclaimer2);
	@unlink($tmp_disclaimer);	
}

function CheckDisclaimerTest($domain){
	$dd=new domains_disclaimer(null,$domain);
	echo "DisclaimerActivate=$dd->DisclaimerActivate\n";
	echo "DisclaimerInbound=$dd->DisclaimerInbound\n";
	echo "DisclaimerOutbound=$dd->DisclaimerOutbound\n";
	echo "DisclaimerUserOverwrite=$dd->DisclaimerUserOverwrite\n";
	
}
function CheckDisclaimerTestUid($uid){
	$dd=new domains_disclaimer(null,$uid);
	echo "DisclaimerActivate=$dd->DisclaimerActivate\n";
	echo "DisclaimerInbound=$dd->DisclaimerInbound\n";
	echo "DisclaimerOutbound=$dd->DisclaimerOutbound\n";
	echo "DisclaimerUserOverwrite=$dd->DisclaimerUserOverwrite\n";
	
}
function SendResultOK($file){
	$datas=explode("\n",@file_get_contents($file));
	@unlink($file);
	$length=strlen($datas);
	$filename=basename($file);
	while (list ($num, $val) = each ($datas) ){
		events("$filename:$length bytes:$num. $val");
		if(preg_match("#exitcode=EX_OK#",$val)){
			events("$filename: OK -> return back");
			return true;
		}
	}
	
	
}

function X_ReplaceTo($tmpfname){
	$f=false;
	$datas=explode("\n",@file_get_contents($tmpfname));
	while (list ($num, $ligne) = each ($datas) ){
		if(preg_match("#^To:\s+(.+)$#i",$ligne,$re)){
				if(preg_match("#<(.+?)>,$#",$re[1],$ri)){
					if($ri[1]<>$GLOBALS["original_recipient"]){
						$GLOBALS["original_recipient"]=$ri[1];
						events("change original {$GLOBALS["original_recipient"]} to {$ri[1]}",__FUNCTION__,__LINE__);
						}
					}
				events("Replace \"$ligne\" -> To: {$GLOBALS["original_recipient"]} in line $num",__FUNCTION__,__LINE__);
				$datas[$num]="To: {$GLOBALS["original_recipient"]}";
				$f=true;
				break;	
		}
		if(trim($ligne)==null){break;}
	}
	
	if($f){
		@file_put_contents($tmpfname,implode("\n",$datas));
	}else{
		events("Could not find To: in $tmpfname (". count($datas)." line(s))",__FUNCTION__,__LINE__);
	}
}

function RecipientsToAdd($mailto,$tmpfname){
	$sock=new sockets();
	return null;
	$RecipientsToAddEnableSingleMail=$sock->GET_INFO("RecipientsToAddEnableSingleMail");
	if($RecipientsToAddEnableSingleMail==null){$RecipientsToAddEnableSingleMail=1;}
	$ldap=new clladp();
	$uid=$ldap->uid_from_email($mailto);
	events("<$mailto> \"uid=$uid\" RecipientsToAddEnableSingleMail=\"$RecipientsToAddEnableSingleMail\"",__FUNCTION__,__LINE__);
	if($uid==null){return true;}
	$filter="(&(objectClass=UserArticaClass)(uid=$uid))";
	$attr=array("RecipientToAdd");
	$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$filter,$attr);
	if(!$sr){return true;}
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	if($hash["count"]==0){return true;}
	
	for($i=0;$i<$hash["count"];$i++){
		for($z=0;$z<$hash[$i][strtolower("RecipientToAdd")]["count"];$z++){
			$rcpt=$hash[$i][strtolower("RecipientToAdd")][$z];
			
			if($rcpt==null){events("rcpt $mailto cc to <$rcpt> SKIP",__FUNCTION__,__LINE__);continue;}
			if($mailto==$rcpt){events("rcpt $mailto cc to <$rcpt> SKIP",__FUNCTION__,__LINE__);continue;}
			 WriteToSyslogMail("rcpt $mailto cc to <$rcpt>","artica-filter");
			$mails[]=$rcpt;
		}
	}
	
	if(count($mails)==0){return;}
	if(!is_array($mails)){return;}
	events(count($mails)." cc emails",__FUNCTION__,__LINE__);
	if($RecipientsToAddEnableSingleMail<>1){
		WriteToSyslogMail("from: <{$GLOBALS["sender"]}> to:<$mailto> Add Blind Carbon Copy to ". count($mails) . " recipient(s) (". implode(",",$mails.")","artica-filter"));
	}
	
	if($RecipientsToAddEnableSingleMail==1){
		while (list ($num, $recipient) = each ($mails) ){
			WriteToSyslogMail("from: <{$GLOBALS["sender"]}> to:<$recipient> create a new mail");
			$cmd="/usr/share/artica-postfix/bin/artica-msmtp --host 127.0.0.1 --read-envelope-from -- $recipient < $tmpfname";
			exec($cmd,$results);
			}
		return true;
	}
	
	
	$datas=explode("\n",@file_get_contents($tmpfname));
	while (list ($num, $ligne) = each ($datas) ){
		if(preg_match("#Bcc:\s+(.*)#i",$line,$regs)){
			events("ADD Bcc: line $num",__FUNCTION__,__LINE__);
			$datas[$num]=$regs[1].','.implode(", ",$mails);
			break;
		}
		
		if(trim($ligne)==null){
			events("insert Bcc: line $num",__FUNCTION__,__LINE__);
			$datas[$num]="Bcc: ".implode(", ",$mails)."\n";
			break;
		}
		
	}
	
	@file_put_contents($tmpfname,implode("\n",$datas));
	}
	
function CheckOutOfOffice($recipient,$from,$subject){
	$ldap=new clladp();
	$uid=$ldap->uid_from_email($recipient);
	if($uid==null){
		events("unknown user $recipient from=<$from>",__FUNCTION__,__LINE__);
		$GLOBALS["uid"][$recipient]="NO";
		return;
	}else{
		events("user $uid from=<$from>",__FUNCTION__,__LINE__);
		$GLOBALS["uid"][$recipient]=$uid;
	}
	
	$vacation=$ldap->UserVacation($uid);
	if($vacation["vacationactive"][0]<>"TRUE"){events("Vacation is disabled ({$vacation["vacationactive"][0]})",__FUNCTION__,__LINE__);return;}
	$datefrom=$vacation["vacationstart"][0];
	$dateTo=$vacation["vacationend"][0];
	$DisplayName=$vacation["displayname"][0];
	$vacationinfo=stripslashes($vacation["vacationinfo"][0]);
	$now=time();
	if($now<$datefrom){events("Vacation not started $datefrom",__FUNCTION__,__LINE__);return;}
	if($now>=$dateTo){events("Vacation is finished $dateTo",__FUNCTION__,__LINE__);return;}
	
	$q=new mysql();
	$md5=md5("$datefrom$dateTo$from$uid");
	$date=date('Y-m-d h:i:s');
	$sql="SELECT zMD5 from OutOfOffice WHERE zMD5='$md5' LIMIT 0,1";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
	if($GLOBALS["VERBOSE"]){events("zMD5={$ligne["zMD5"]} \"$sql\"",__FUNCTION__,__LINE__);}
	if($ligne["zMD5"]<>null){events("Vacation is already sended",__FUNCTION__,__LINE__);return;}
	
 $mail = new simplemail;
 $mail -> addrecipient($from,$from);
 $mail -> addfrom($recipient,$DisplayName);
 $mail -> addsubject("Re: $subject");
 $disctxt=br2nl($vacationinfo);
 $disctxt=p2nl($disctxt);
 $disctxt=strip_tags($disctxt);
 $disctxt=html_entity_decode($disctxt);
 $mail -> text = $disctxt;
 $mail -> html =$vacationinfo;
 if ( $mail -> sendmail() ) { echo events("Auto-reply sent..",__FUNCTION__,__LINE__); } else { events("Auto-reply Error:$mail->error_log");return;}	
	$sql="INSERT INTO OutOfOffice (zMD5,uid,zDate,mailfrom) VALUES ('$md5','$uid',NOW(),'$from')";
	$q->QUERY_SQL($sql,"artica_events");
}


function events($text,$function,$line=0){
		$pid=@getmypid();
		$date=@date("H:i:s");
		$logFile="/var/log/artica-filter/mail.log";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$text="[$pid] $date $function:: $text (L.$line)\n";
		if($GLOBALS["VERBOSE"]){echo $text;}
		@fwrite($f, $text);
		@fclose($f);	
		}

?>