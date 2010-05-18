<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__)."/ressources/class.functions.inc");
if(!isset($_SESSION["uid"])){header("Location: session-fermee.php");die();}

if(isset($_GET["js-filter-bounce"])){js_filter_bounce();exit;}
if(isset($_GET["js-filter-sender"])){js_filter_sender();exit;}
if(isset($_GET["search-js"])){js_filter_search();exit;}
if(isset($_GET["search-popup"])){search_popup();exit;}
if(isset($_GET["query"])){echo listemails();exit;}

$body=listemails();

$html="<div id='quardiv'>$body</div>";


	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	die();




function listemails(){
	$tpl=new templates();	
	$page=CurrentPageName();
	if(isset($_GET["filter-sender"])){
		if($_GET["filter-sender"]<>null){
			$_SESSION["RTMM"]["SENDER"]=$_GET["filter-sender"];
			if((strpos($_GET["filter-sender"],'*')>0) OR ($_GET["filter-sender"][0]=='*') ){
				$and=" AND sender_user LIKE '". str_replace("*","%","{$_GET["filter-sender"]}")."'";
			}else{
				$and=" AND sender_user='{$_GET["filter-sender"]}'";
			}
			
			$back=true;
		}
	}
	
	if(isset($_GET["error"])){
		$_SESSION["RTMM"]["ERROR"]=$_GET["error"];
		$and=$and." AND bounce_error='{$_GET["error"]}'";
		$back=true;
	}
	
	$delivery_users=delivery_users($and);
	
	$q=new mysql();
	$sql="select sender_user,delivery_user,time_stamp,bounce_error FROM smtp_logs WHERE ($delivery_users) ORDER BY time_stamp DESC LIMIT 0,150";
	writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
	$results=$q->QUERY_SQL($sql,"artica_events");
	

	
$head=$tpl->_ENGINE_parse_body("
	<table>
	<td valign='middle' width=99%>
		<H3>{postfix_realtime_events_text}</H3>
	</td>
	<td>". imgtootltip("question-48.png",'{search}',"Loadjs('$page?search-js=yes')")."</td>
	<td>". imgtootltip("48-infos.png",'{infos}',"Loadjs('user.messaging.php?email-infos-js=quarantine')")."</td>
	</tr>
	</table>
	");	
	
	
	$table=$tpl->_ENGINE_parse_body("<table style='width:95%' class=table_form>
		<tr>
		<th><span style='color:white'>{date}</span></th>
		<th><span style='color:white'>{status}</span></th>
		<th><span style='color:white'>{senderm}</span></th>		
		<th><span style='color:white'>{recipient}</span></th>
		</tr>");
	
	$date_hier = strftime("%y-%m-%d", mktime(0, 0, 0, date('m'), date('d')-1, date('y')));
	$date=date('Y-m-d');	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$ligne["time_stamp"]=str_replace($date,'{today}',$ligne["time_stamp"]);
		$ligne["time_stamp"]=str_replace($date_hier,'{yesterday}',$ligne["time_stamp"]);
		if($class=="row1"){$class="row2";}else{$class="row1";}
		$table=$table. "<tr class=$class>
		<td valign='top' nowrap class=$class width=1%>&nbsp;{$ligne["time_stamp"]}&nbsp;</td>
		<td valign='top' class=$class nowrap>&nbsp;{$ligne["bounce_error"]}&nbsp;</td>
		<td valign='top' class=$class>&nbsp;{$ligne["sender_user"]}&nbsp;</td>
		<td valign='top' class=$class>&nbsp;{$ligne["delivery_user"]}&nbsp;</td>
		
		</tr>";
	}
	
	
	$table=$tpl->_ENGINE_parse_body($table . "</table>");
	if($back){return "$head$table";}
	
	return $tpl->_ENGINE_parse_body("<div style='width:100%;height:500px;overflow:auto' id='rtmm-panel'>$head$table</div>");
	

	
	
	
}

function search_popup(){
	$delivery_users=delivery_users($and);
	$sql="SELECT bounce_error FROM smtp_logs WHERE ($delivery_users) GROUP BY bounce_error ORDER BY bounce_error";
	$q=new mysql();
	writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
	$results=$q->QUERY_SQL($sql,"artica_events");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$array[$ligne["bounce_error"]]=$ligne["bounce_error"];
	}
	
	$bounce_error=Field_array_Hash($array,'bounce_error',$_SESSION["RTMM"]["ERROR"]);
	
	$html="<table class=table_form>
	<tr>
		<td class=legend>{status}:</td>
		<td>$bounce_error</td>
	</tr>	
	<tr>
		<td class=legend>{senderm}:</td>
		<td>". Field_text("filter-by-sender",$_SESSION["RTMM"]["SENDER"],null,null,null,null,false,"QueryRTMMType(event)")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{search}","QueryRTMM()")."</td>
	</tr>
	</table>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}



function js_filter_search(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{search}:{postfix_realtime_events}");
	
	echo "
		YahooWin('400','$page?search-popup=yes','$title');
		
	function QueryRTMMType(e){
			if(checkEnter(e)){QueryRTMM();}
		}
		
		function QueryRTMM(){
			var sender=document.getElementById('filter-by-sender').value;
			var bounce_error=document.getElementById('bounce_error').value;
			LoadAjax('quardiv','$page?query=yes&filter-sender='+sender+'&error='+bounce_error);
		}
	
	
	
	";
}




?>