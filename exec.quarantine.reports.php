<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
include_once(dirname(__FILE__) . '/ressources/Rmail.php');

if($argv[1]=="--single"){
	$_GET["SINGLE"]=true;
	write_syslog("build quarantine report for organization=<{$argv[3]}> and user=<{$argv[2]}>",__FILE__);
	$_GET["mailfrom"]=$argv[4];
	$_GET["subject"]="Re:{$argv[5]}";
	BuildReport($argv[2],$argv[3]);
	die();
}


$ou=$argv[1];
if(trim($ou)==null){die("no organization specified");}

$ldap=new clladp();
$hash=$ldap->HashMembersFromOU($ou);
if(!is_array($hash)){
	write_syslog("\"$ou\ has no organization, shutdown...",__FILE__);
	die("no members");
}

while (list ($email, $ligne) = each ($hash) ){
	BuildReport($email,$ou);
	
}


function BuildReport($uid,$ou){
	$usr=new usersMenus();
	$user=new user($uid);
	$emailsnumbers=count($user->HASH_ALL_MAILS);
	
	if($emailsnumbers==0){
		write_syslog("BuildReport() user=<$uid> has no email addresses",__FILE__);
		return null;
	}
	
	$ouU=strtoupper($ou);
	$ini=new Bs_IniHandler("/etc/artica-postfix/settings/Daemons/OuSendQuarantineReports$ouU");
	$days=$ini->_params["NEXT"]["days"];
	if($days==null){$days=2;}
	
if($ini->_params["NEXT"]["title1"]==null){$ini->_params["NEXT"]["title1"]="Quarantine domain senders";}
if($ini->_params["NEXT"]["title2"]==null){$ini->_params["NEXT"]["title2"]="Quarantine list";}
if($ini->_params["NEXT"]["explain"]==null){$ini->_params["NEXT"]["explain"]="You will find here all mails stored in your quarantine area";}
if($ini->_params["NEXT"]["externalLink"]==null){$ini->_params["NEXT"]["externalLink"]="https://$usr->hostname:9000/user.quarantine.query.php";}

if(preg_match("#([0-9]+) (jours|days)#",$_GET["subject"],$re)){
	write_syslog("Change to {$re[1]} days from subject",__FILE__);
	$days=$re[1];
}


	write_syslog("Starting HTML report ($days days) for $uid $user->DisplayName ($emailsnumbers recipient emails)",__FILE__);
	$date=date('Y-m-d');
	$font_normal="<FONT FACE=\"Arial, Helvetica, sans-serif\" SIZE=2>";
	$font_title="<FONT FACE=\"Arial, Helvetica, sans-serif\" SIZE=4>";
	
while (list ($num, $ligne) = each ($user->HASH_ALL_MAILS) ){
	$recipient_sql[]="mailto='$ligne'";
	
}
	$recipients=implode(" OR ",$recipient_sql);
	$sql="SELECT mailfrom,zDate,MessageID,DATE_FORMAT(zdate,'%W %D %H:%i') as tdate,subject FROM quarantine
	WHERE (zDate>DATE_ADD('$date', INTERVAL -$days DAY)) AND ($recipients)  ORDER BY zDate DESC;";
	
	$q=new mysql();
//	echo "$sql\n";
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){
		write_syslog("Wrong sql query $q->mysql_error",__FILE__);
		return null;
	}
	$style="font-size:11px;border-bottom:1px solid #CCCCCC;margin:3px;padding:3px";
	
	$session=md5($user->password);
	
while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	$subject=htmlspecialchars($ligne["subject"]);
	$from=trim($ligne["mailfrom"]);
	$zDate=$ligne["tdate"];
	$MessageID=$ligne["MessageID"];
	if($from==null){$from="unknown";}{$domain="unknown";}
	if(preg_match("#(.+?)@(.+)#",$from,$re)){
		$domain=$re[2];
	}
	
	$uri="<a href=\"{$ini->_params["NEXT"]["externalLink"]}?uid=$user->uid&session=$session&mail=$MessageID\">";
	
	$array[$domain][]="<tr>
		<td style=\"$style\" nowrap>$uri$font_normal$zDate</FONT></a></td>
		<td style=\"$style\" nowrap>$uri$font_normal<code>$from</code></FONT></a></td>
		<td style=\"$style\">$uri$font_normal<strong>$subject</strong></FONT></a></td>
		
		</tr>";
	
}
write_syslog("BuildReport: Single ???=<{$_GET["SINGLE"]}>",__FILE__);
$count_domains=count($array);
if(!$_GET["SINGLE"]){
	if($count_domains==0){
		write_syslog("BuildReport() user=<$uid> has no spam domains senders",__FILE__);
		return null;
	}
}
$html="<H1>$font_title$days {$ini->_params["NEXT"]["title1"]}</FONT></H1>
<p style=\"font-size:12px;font-weight:bold\">$font_title{$ini->_params["NEXT"]["explain"]}</FONT> </p>
<hr>
<H2>$font_title$count_domains Domains</FONT></H2>
<table style=\"width:100%\">";
if(is_array($array)){
	while (list ($num, $ligne) = each ($array) ){
		$html=$html."<tr><td><li><strong style=\"font-size:12px\">$font$num</FONT></li></td></tr>\n";
		}
	reset($array);
}



$html=$html."</table>
<hr>
<h2>$font_title{$ini->_params["NEXT"]["title2"]}</FONT></h2>
<table style=\"width:100%;border:1px solid #CCCCCC;margin:5px;padding:5px\">";

if(is_array($array)){
while (list ($num, $ligne) = each ($array) ){
	$html=$html."<hr>
	<table border=1 style=\"width:100%;border:1px solid #CCCCCC;margin:5px;padding:5px\">
	<tr>
		<td colspan=3><strong style=\"font-size:16px\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$font_title$num</FONT></td>
	</tr>
	".implode("\n",$ligne);
	
	
	$html=$html."
	</table>
	";
	
}}
	
if($ini->_params["NEXT"]["mailfrom"]==null){$ini->_params["NEXT"]["mailfrom"]=$_GET["mailfrom"];}
if($ini->_params["NEXT"]["mailfrom"]==null){$ini->_params["NEXT"]["mailfrom"]="root@localhostlocaldomain";}
if($ini->_params["NEXT"]["subject"]==null){$ini->_params["NEXT"]["subject"]=$_GET["subject"];}
if($ini->_params["NEXT"]["subject"]==null){$ini->_params["NEXT"]["subject"]="Daily Quarantine report";}
$tpl=new templates();
$subject=$ini->_params["NEXT"]["subject"];
$mail = new Rmail();
$mail->setFrom("quarantine <{$ini->_params["NEXT"]["mailfrom"]}>");
$mail->setSubject($subject);
$mail->setPriority('normal');
$mail->setText(strip_tags($html));
$mail->setHTML($html);
$address = $user->mail;
$result  = $mail->send(array($address));
write_syslog("From=<{$ini->_params["NEXT"]["mailfrom"]}> to=<{$user->mail}> Send Quarantine Report=<$result>",__FILE__);
	
}






?>