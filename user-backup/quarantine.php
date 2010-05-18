<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__)."/ressources/class.functions.inc");
if(!isset($_SESSION["uid"])){header("Location: session-fermee.php");die();}

if(isset($_GET["js-filter-bounce"])){js_filter_bounce();exit;}
if(isset($_GET["js-filter-sender"])){js_filter_sender();exit;}
if(isset($_GET["search-js"])){search_js();exit;}
if(isset($_GET["search-popup"])){search_popup();exit;}
if(isset($_GET["ShowID"])){ShowID_js();exit;}
if(isset($_GET["ViewID"])){ShowID_popup();exit;}

if(isset($_GET["release-mail"])){release_mail_js();exit;}
if(isset($_GET["release-mail-send"])){release_mail_send();exit;}

$body=listemails();

$html="<div id='quardiv'>$body</div>";


	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	die();




function listemails(){
	$order="ORDER BY zDate DESC";
	$page=CurrentPageName();
	if(isset($_GET["filter-sender"])){
		if($_GET["filter-sender"]<>null){
			$_SESSION["QUARANTINE"]["SENDER"]=$_GET["filter-sender"];
			if((strpos($_GET["filter-sender"],'*')>0) OR ($_GET["filter-sender"][0]=='*') ){
				$and=" AND mailfrom LIKE '". str_replace("*","%","{$_GET["filter-sender"]}")."'";
			}else{
				$and=" AND mailfrom='{$_GET["filter-sender"]}'";
			}
			
			$back=true;
		}
	}
	
	if($_GET["body"]<>null){
		$_SESSION["QUARANTINE"]["body"]=$_GET["body"];
	$pertinance=",MATCH (MessageBody) AGAINST (\"{$_GET["body"]}\") AS pertinence ";
	$order="ORDER BY pertinence DESC";	
	}

	
	$mailto=mailto($and);
	
	$q=new mysql();
	$sql="select MessageID,mailfrom,mailto,zDate,subject$pertinance FROM quarantine WHERE ($mailto)  $order LIMIT 0,150";
	writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	$head="
	<table>
	<td valign='middle' width=99%>
		<H3>{quarantine_mails}</H3>
	</td>
	<td>". imgtootltip("question-48.png",'{search}',"Loadjs('$page?search-js=yes')")."</td>
	<td>". imgtootltip("48-infos.png",'{infos}',"Loadjs('user.messaging.php?email-infos-js=quarantine')")."</td>
	</tr>
	</table>
	";
	
	
	
	
	$table="<table style='width:95%' class=table_form>
		<tr>
		<th><span style='color:white'>{date}</span></th>
		<th><span style='color:white'>{subject}</span></th>
		<th><span style='color:white'>{senderm}</span></th>		
		<th><span style='color:white'>{recipient}</span></th>
		</tr>
		
		";
	
	$date_hier = strftime("%y-%m-%d", mktime(0, 0, 0, date('m'), date('d')-1, date('y')));
	$date=date('Y-m-d');		
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$ligne["zDate"]=str_replace($date,'{today}',$ligne["zDate"]);
		$ligne["zDate"]=str_replace($date_hier,'{yesterday}',$ligne["zDate"]);		
		if($class=="row1"){$class="row2";}else{$class="row1";}
		if(strlen($ligne["subject"])>40){$ligne["subject"]=substr($ligne["subject"],0,37)."...";}
		
		
		$table=$table. "<tr class=$class>
		<td valign='top' nowrap class=$class width=1%>{$ligne["zDate"]}</td>
		<td valign='top' class=$class>".divlien("Loadjs('$page?ShowID={$ligne["MessageID"]}')",$ligne["subject"])."</td>
		<td valign='top' class=$class>{$ligne["mailfrom"]}</a></td>
		<td valign='top' class=$class>{$ligne["mailto"]}</td>
		
		</tr>";
	}
	
	
	$table=$table . "</table>";
	if($back){return "$head$table";}
	
	return "<div style='width:100%;height:500px;overflow:auto' id='rtmm-panel'>$head$table</div>
	
	
	";
	
	
	
}

function search_popup(){
	
	$html="<table class=table_form>
	<tr>
		<td class=legend>{senderm}:</td>
		<td>". Field_text("filter-by-sender",$_SESSION["QUARANTINE"]["SENDER"],null,null,null,null,false,"QueryQuarantineType(event)")."</td>
	</tr>
	<tr>
		<td class=legend>{bodym}:</td>
		<td>". Field_text("filter-by-body",$_SESSION["QUARANTINE"]["body"],null,null,null,null,false,"QueryQuarantineType(event)")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{search}","QueryQuarantine()")."</td>
	</tr>
	</table>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}
function search_js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{search}:&nbsp;{quarantinem}");
	
	echo "
		function QueryQuarantineType(e){
			if(checkEnter(e)){QueryQuarantine();}
		}
		
		function QueryQuarantine(){
			var sender=document.getElementById('filter-by-sender').value;
			var body=document.getElementById('filter-by-body').value;
			LoadAjax('quardiv','$page?query=yes&filter-sender='+sender+'&body='+body);
		}
	
	
		YahooWin('500','$page?search-popup=yes','$title');
		
	
	
	";

}


function ShowID_js(){
	$page=CurrentPageName();
	echo "YahooWin('800','$page?ViewID={$_GET["ShowID"]}','{$_GET["ShowID"]}');";
}


function ShowID_popup(){
	$sql="SELECT MessageBody FROM quarantine WHERE MessageID='{$_GET['ViewID']}'";
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
	$sql="SELECT BinMessg,MessageBody FROM quarantine WHERE MessageID='{$_GET['release-mail-send']}'";
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