<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.functions.inc");
if(!isset($_SESSION["uid"])){header("Location: session-fermee.php");die();}

if(isset($_GET["search-js"])){search_js();exit;}
if(isset($_GET["search-popup"])){search_popup();exit;}
if(isset($_GET["ShowID"])){ShowID_js();exit;}
if(isset($_GET["ViewID"])){ShowID_popup();exit;}
if(isset($_GET["query"])){echo listemails();exit;}


if(isset($_GET["release-mail"])){release_mail_js();exit;}
if(isset($_GET["release-mail-send"])){release_mail_send();exit;}



$body=listemails();
$html="<div style='padding:5px;background-image:url(img/bg-hd.gif);background-position:bottom right;background-repeat:no-repeat;width:100%' id='quardiv'>$body</div>";

if(isset($_GET["ajax"])){
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	die();
}



$tpl=new templates($html);
echo $tpl->buildPage();

function listemails(){
	$page=CurrentPageName();
	$and="AND filesize>0";
	
	$head="
	<table>
	<td valign='middle' width=99%>
		<H3>{user_backup_emails_query_text}</H3>
	</td>
	<td>". imgtootltip("question-48.png",'{search}',"Loadjs('$page?search-js=yes')")."</td>
	<td>". imgtootltip("48-infos.png",'{infos}',"Loadjs('user.messaging.php?email-infos-js=quarantine')")."</td>
	</tr>
	</table>
	";	
	
	//filter-body
	
	

//$sql="SELECT storage.MessageID,storage.mailfrom$recipient_to_find_fields,storage.zDate,storage.subject,storage.MessageBody,MATCH (storage.MessageBody) 
	//		AGAINST (\"$stringtofind\") AS pertinence  
	//		FROM storage,storage_recipients WHERE storage.MessageID=storage_recipients.MessageID$recipient_to_find$sender_to_find ORDER BY pertinence DESC LIMIT 0,90";
				
	
	$order="ORDER BY zDate DESC";
	if(isset($_GET["filter-sender"])){
		if($_GET["filter-sender"]<>null){
			if((strpos($_GET["filter-sender"],'*')>0) OR ($_GET["filter-sender"][0]=='*') ){
				$and=$and." AND mailfrom LIKE '". str_replace("*","%","{$_GET["filter-sender"]}")."'";
			}else{
				$and=$and." AND mailfrom='{$_GET["filter-sender"]}'";
			}
			
			$back=true;
		}
	}
	
if($_GET["body"]<>null){
	$pertinance=",MATCH (MessageBody) AGAINST (\"{$_GET["filter-body"]}\") AS pertinence ";
	$order="ORDER BY pertinence DESC";
}
	
	$delivery_users=mailto($and);
	
	$q=new mysql();
	$sql="select MessageID,mailfrom,mailto,DATE_FORMAT(zDate,'%Y-%m-%d %H:%i') as tdate ,filesize,subject$pertinance FROM storage WHERE ($delivery_users) $order LIMIT 0,150";
	writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
	$results=$q->QUERY_SQL($sql,"artica_backup");

	
	
	if($back){
		$html=$html."<table style='width:90%'>
		<tr>
			<td><a href='backup.php'><H3>&laquo;&nbsp;{back}</H3></td>
		</tr>
		</table>";
	}
	
	$html=$html."<table style='width:95%' class=table_form>
	
		<tr>
		<th>{date}</span></th>
		<th>{size}</span></th>
		<th>{subject}</span></th>
		<th>{senderm}</span></th>		
		<th>{recipient}</span></th>

		</tr>
		
		";
	
	$date_hier = strftime("%y-%m-%d", mktime(0, 0, 0, date('m'), date('d')-1, date('y')));
	$date=date('Y-m-d');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$filesize=$ligne["filesize"]/1024;
	if($class=="row1"){$class="row2";}else{$class="row1";}
		$filesize=FormatBytes($filesize);
		if(strlen($ligne["subject"])>40){$ligne["subject"]=substr($ligne["subject"],0,37)."...";}
		$ligne["tdate"]=str_replace($date,'{today}',$ligne["tdate"]);
		$ligne["tdate"]=str_replace($date_hier,'{yesterday}',$ligne["tdate"]);
		$html=$html. "
		<tr class=$class>
		<td valign='top' nowrap width=1%>&nbsp;{$ligne["tdate"]}&nbsp;</td>
		<td valign='top' >&nbsp;$filesize&nbsp;</td>
			<td valign='top' class=$class>".divlien("Loadjs('$page?ShowID={$ligne["MessageID"]}')",$ligne["subject"])."</td>
		<td valign='top' >{$ligne["mailfrom"]}</td>
		<td valign='top' >{$ligne["mailto"]}</td>
		</tr>";
	}
	
	
	$html=$html . "</table>";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("<div style='width:100%;height:500px;overflow:auto'>$head$html</div>");

	
	
	
}

function search_js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{search}:&nbsp;");
	
	echo "
		function QueryBackupType(e){
			if(checkEnter(e)){QueryBackup();}
		}
		
		function QueryBackup(){
			var sender=document.getElementById('filter-by-sender').value;
			var body=document.getElementById('filter-by-body').value;
			LoadAjax('quardiv','$page?query=yes&filter-sender='+sender+'&body='+body);
		}
	
	
		YahooWin('500','$page?search-popup=yes','$title');
		";

}

function search_popup(){
	
	$html="<table class=table_form>
	<tr>
		<td class=legend>{senderm}:</td>
		<td>". Field_text("filter-by-sender",$_SESSION["SEARCHBACK"]["SENDER"],null,null,null,null,false,"QueryBackupType(event)")."</td>
	</tr>
	<tr>
		<td class=legend>{bodym}:</td>
		<td>". Field_text("filter-by-body",$_SESSION["SEARCHBACK"]["body"],null,null,null,null,false,"QueryBackupType(event)")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{search}","QueryBackup()")."</td>
	</tr>
	</table>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}


function ShowMessage(){
if($_SESSION[$_GET['ShowID']]<>null){echo $_SESSION[$_GET['ShowID']];exit;}	
$msostyles="
<style>
body {
	padding:5px;
	background-image:url(img/bg-hd.gif);
	background-position:bottom right;
	background-repeat:no-repeat;
}


</style>
";
	
	$sql="SELECT MessageBody FROM storage WHERE MessageID='{$_GET['ShowID']}'";
	writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$msg=$ligne["MessageBody"];
	$msg=str_replace("</head>","
	<link href='css/stylesheet.css' rel='stylesheet' type='text/css' media='screen' />\n
	<link type=\"text/css\" href=\"css/custom-theme/jquery-ui-1.7.2.custom.css\" rel=\"stylesheet\" />
	
	$msostyles\n</head>",$msg);
	$msg=str_replace("images.listener.php?mailattach=/","backup.php?ShowID={$_GET["ShowID"]}",$msg);
	$tpl=new templates();
	//<!--X-Subject-Header-Begin-->
	$form="
	<form name=FFM1 METHOD=POST action='release.php'>
	<div style='text-align:right'>
		<input type='hidden' value='{$_GET['ShowID']}' name='release-id'>
		<input type='submit' value='{release_mail}&nbsp;&raquo;'>
	</div>
	</form>
	
	
	";
	$form=$tpl->_ENGINE_parse_body($form);
	$msg=str_replace("<!--X-Subject-Header-Begin-->",$form,$msg);
	$_SESSION[$_GET['ShowID']]=$msg;
	
	
	
	echo $msg;
	
}
function ShowID_js(){
	$page=CurrentPageName();
	echo "YahooWin('800','$page?ViewID={$_GET["ShowID"]}','{$_GET["ShowID"]}');";
}


function ShowID_popup(){
	$sql="SELECT MessageBody FROM storage WHERE MessageID='{$_GET['ViewID']}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$msg=$ligne["MessageBody"];
	$msg=ParseOrginalMessage($msg,$_GET['ViewID'],CurrentPageName());
	
	echo $msg;
	
}

function release_mail_js(){
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{release_mail}');

	echo "YahooWin1('450','$page?release-mail-send={$_GET["release-mail"]}','$title:&nbsp;{$_GET["release-mail"]}');";	
	
}

function release_mail_send(){
	$sql="SELECT BinMessg,MessageBody FROM storage WHERE MessageID='{$_GET['release-mail-send']}'";
	$tpl=new templates();
	$q=new mysql();
	$user=new user($_SESSION["uid"]);
	
	writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
	
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if(!$q->ok){
		echo $tpl->_ENGINE_parse_body("
	<div style='background-color:#005447;border:1px solid #D0C9AD;padding:50px'>
		<center>
			<p style='color:white;font-size:18px;border:1px solid white;padding:10px;margin:10px'>{failed}<br> $user->mail<hr>$q->mysql_error<hr>$lenght bytes</p>
		</center>
	</div>");
	exit;
	}
	$filename=md5($ligne["MessageBody"]);
	
	$lenght=strlen($ligne["BinMessg"]);
	writelogs("Sending message /tmp/$filename from $user->mail ($lenght bytes)",__FUNCTION__,__FILE__,__LINE__);
	@file_put_contents("/tmp/$filename",$ligne["BinMessg"]);
	writelogs("Sending message /tmp/$filename from $user->mail",__FUNCTION__,__FILE__,__LINE__);
	$cmd="/usr/sbin/sendmail -bm -t -f $user->mail </tmp/$filename";
	$output=shell_exec($cmd);
	@unlink("/tmp/$filename");
	
	echo $tpl->_ENGINE_parse_body("
	<div style='background-color:#005447;border:1px solid #D0C9AD;padding:50px'>
		<center>
			<p style='color:white;font-size:18px;border:1px solid white;padding:10px;margin:10px'>{success}<br> $user->mail<hr>$lenght bytes</p>
		</center>
	</div>");
	
	
}




?>