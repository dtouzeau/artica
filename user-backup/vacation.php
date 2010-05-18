<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.functions.inc");
include_once(dirname(__FILE__)."/ressources/class.domains.diclaimers.inc");
if(!isset($_SESSION["uid"])){header("Location: session-fermee.php");die();}


	if(isset($_POST["vacationInfo"])){vacationInfoSave();exit;}
	if(isset($_GET["form"])){form();exit;}
	if(isset($_GET["content"])){content();exit;}
	if(isset($_GET["events"])){events();exit;}
	if(isset($_GET["delete-all-events"])){delete_all();exit;}
	if(isset($_GET["all-events"])){echo events(1);exit;}
	if(isset($_POST["vacationStart"])){
		SaveVacation();
		
	}
	
	
	$page=CurrentPageName();
	
	
	$html="
		<div id='container-vacation' style='width:98%;margin:4px'>
			<ul>
					<li><a href=\"$page?form=yes\"><span>{vacation_message}</span></a></li>
                	<li><a href=\"$page?events=yes\"><span>{outofoffice_events}</span></a></li>
			</ul>
		</div>	
		
	<script type='text/javascript'>	
					$(document).ready(function(){
					$('#container-vacation').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			

			});
	
	</script>

	";
	$tpl=new templates();
	$page=$tpl->_ENGINE_parse_body($html);
	echo $tpl->PopupPage($page);

function SaveVacation(){
	if($_POST["vacationActive"]==null){$_POST["vacationActive"]="FALSE";}
	$user=new user($_SESSION["uid"]);
	$tpl=new templates();
	writelogs("->SaveVacationInfos()  {$_POST["vacationStart"]}-> {$_POST["vacationEnd"]}",__FUNCTION__,__FILE__,__LINE__);
	if(preg_match('#([0-9]+)-([0-9]+)-([0-9]+)#',$_POST["vacationStart"],$re)){
				$timestamp = mktime(0, 0, 0, $re[2], $re[3], $re[1]);
				$user->vacationStart=$timestamp;
			}else{
				$_GET["ERROR"]="{$_POST["vacationStart"]} -> {failed} ";
				return;
			}

		if(preg_match('#([0-9]+)-([0-9]+)-([0-9]+)#',$_POST["vacationEnd"],$re)){
				$timestamp = mktime(0, 0, 0, $re[2], $re[3], $re[1]);
				$user->vacationEnd=$timestamp;
					}else{
				$_GET["ERROR"]="{$_POST["vacationEnd"]} -> {failed} ";
				return;
			}

	$user->vacationActive=$_POST["vacationActive"];
	$user->vacationEnabled=$_POST["vacationActive"];
	//$user->vacationInfo=$_POST["vacationInfo"];


if($user->vacationEnd<$users->vacationStart){$_GET["ERROR"]=$tpl->_ENGINE_parse_body('{error_check_dates}');return;}
writelogs("->SaveVacationInfos()  {$_POST["vacationStart"]}-> $user->vacationStart,{$_POST["vacationEnd"]} $user->vacationEnd",__FUNCTION__,__FILE__,__LINE__);
if(!$user->SaveVacationInfos()){$_GET["ERROR"]=$user->error;}

}


function content(){
	$user=new user($_SESSION["uid"]);
	$tiny=TinyMce('vacationInfo',stripslashes($user->vacationInfo));
	$tpl=new templates();
	
	$html="
	<form name='tinymcedisclaimer' method='post' action=\"$page\">
	$tiny
	<center>
	<hr>". button("{edit}","document.forms['tinymcedisclaimer'].submit();")."</center>
	</form>
	
	";
	
	$page=$tpl->_ENGINE_parse_body($html);
	echo $tpl->PopupPage($page);	
}


function form(){
	
	$user=new user($_SESSION["uid"]);
	$enable=Field_checkbox("vacationActive","TRUE",$user->vacationActive);
	$tpl=new templates();
	$page=CurrentPageName();
	
	$date1=date("Y-m-d",$user->vacationStart);
	$date2=date("Y-m-d",$user->vacationEnd);	
	
	$form="<div style='width:100%;height:100%;background-color:#FFFFFF;padding:8px;'>
	<H1>{vacation_message}</H1>
	<span style='color:red;font-size:16px'>{$_GET["ERROR"]}</span>
	<p class=caption style='color:black'>{OUT_OF_OFFICE_TEXT}</p>
	<form name='tinymcedisclaimer' method='post' action=\"$page\">
	<table style='width:100%'>
	<tr>
	<td valign='top'>
		<table style='width:240px' class=table_form style='background-color:white'>
			<tr>
				<td class=legend>{enable}:</td>
				<td>$enable</td>
			</tr>	
			<tr>
				<td class=legend>{date_from}:</td>
				<td><input type='text' id='date_from' name='vacationStart' value='$date1' style='width:100px;font-size:16px;padding:4px'></td>
			</tr>
			<tr>
				<td class=legend>{date_to}:</td>
				<td><input type='text' id='date_to' name='vacationEnd' value='$date2' style='width:100px;font-size:16px;padding:4px'></td>
			</tr>	
			<tr>
				<td colspan=2 align='right'><hr>". button("{edit}","document.forms['tinymcedisclaimer'].submit();")."</td>
			</tr>
			</table>
			</td>
			<td valign='top'>
				<div style='color:black;padding:3px;margin:3px;border:1px solid #005447;background-color:#F8F8FF'>". stripslashes($user->vacationInfo)."</div>
				<div style='text-align:right'>". button("{edit}","javascript:s_PopUp('vacation.php?content=yes',800,400)")."</div>
			</td>
			</tr>
			</table>
			<hr>
			
			
			</form>
			
	</div>
	
<script type='text/javascript'>
	\$('#date_from').datepicker({ dateFormat: 'yy-mm-dd' });
	\$('#date_to').datepicker({ dateFormat: 'yy-mm-dd' });
</script>	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($form);
	
}


function vacationInfoSave(){
	$user=new user($_SESSION["uid"]);
	$user->vacationInfo=$_POST["vacationInfo"];
	writelogs("->SaveVacationInfos() ".strlen($_POST["vacationInfo"])." bytes length",__FUNCTION__,__FILE__,__LINE__);
	if(!$user->SaveVacationInfos()){$_GET["ERROR"]=$user->error;}	
	content();
}


function events($return=0){
	$q=new mysql();
	$sql="select * FROM OutOfOffice WHERE uid='{$_SESSION["uid"]}' ORDER BY zDate DESC LIMIT 0,150";
	writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
	$results=$q->QUERY_SQL($sql,"artica_events");
	$page=CurrentPageName();
	$head="
	<script>
	
	var x_OutOfOfficeDeleteAllEvents= function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		LoadAjax('repondeur-panel','$page?all-events=yes');
		
		}	
	
		function OutOfOfficeDeleteAllEvents(){
			var XHR = new XHRConnection();
			XHR.appendData('delete-all-events','yes');
			XHR.sendAndLoad('$page', 'GET',x_OutOfOfficeDeleteAllEvents);	
		}
	
	</script>
	<table>
	<td valign='middle' width=99%>
		<H3>{outofoffice_events}</H3>
	</td>
	</tr>
	</table>
	";
	
	
	$head=$head."<div style='text-align:right'>".button("{delete_all}","OutOfOfficeDeleteAllEvents()");
	
	$table="<table style='width:95%' class=table_form>
		<tr>
		<th><span style='color:white'>{date}</span></th>
		<th><span style='color:white'>{senderm}</span></th>
		</tr>
		
		";
	
	$date_hier = strftime("%y-%m-%d", mktime(0, 0, 0, date('m'), date('d')-1, date('y')));
	$date=date('Y-m-d');		
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$ligne["zDate"]=str_replace($date,'{today}',$ligne["zDate"]);
		$ligne["zDate"]=str_replace($date_hier,'{yesterday}',$ligne["zDate"]);		
		if($class=="row1"){$class="row2";}else{$class="row1";}
		
		
		
		$table=$table. "<tr class=$class>
		<td valign='top' nowrap class=$class width=1% style='padding:4px'>{$ligne["zDate"]}</td>
		<td valign='top' class=$class style='padding:4px'>{$ligne["mailfrom"]}</a></td>
		</tr>";
	}
	
	
	$table=$table . "</table>";
	$tpl=new templates();
	if($return==1){return $tpl->_ENGINE_parse_body("$head$table");}
	
	echo $tpl->_ENGINE_parse_body("<div style='width:100%;height:500px;overflow:auto' id='repondeur-panel'>$head$table</div>");
	
		
}

function delete_all(){
	
	$sql="DELETE FROM OutOfOffice WHERE uid='{$_SESSION["uid"]}'";
	$q=new mysql();
	if(!$q->QUERY_SQL($sql,"artica_events")){echo $q->mysql_error;}
}
