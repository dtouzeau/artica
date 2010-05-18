<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.mailsync.inc');
	include_once('ressources/class.cron.inc');

	
	
	if((isset($_GET["uid"])) && (!isset($_GET["userid"]))){$_GET["userid"]=$_GET["uid"];}
	
	if(!permissions()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["remote_imap_server"])){add_account();exit;}
	if(isset($_GET["imapsynclist"])){imapsynclist();exit;}
	if(isset($_GET["imapSyncDelete"])){imapSyncDelete();exit;}
	if(isset($_GET["AddForm"])){add_popup();exit;}
	if(isset($_GET["events"])){events();exit;}
	if(isset($_GET["schedule"])){schedule();exit;}
	if(isset($_GET["imapsync_save_schedule"])){schedule_save();exit;}
	if(isset($_GET["imapRun"])){imapRun();exit;}
	if(isset($_GET["imapStop"])){imapStop();exit;}
	
	
	
js();



function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{import_mailbox}","domains.edit.users.php");
	$add=$tpl->_ENGINE_parse_body("{add}");
	$events=$tpl->_ENGINE_parse_body("{events}");
	$schedule=$tpl->_ENGINE_parse_body("{schedule}");
	$apply_upgrade_help=$tpl->javascript_parse_text("{apply_upgrade_help}");
	$page=CurrentPageName();
	
	
	$html="
		function import_mailbox_run(){
			YahooWin5(600,'$page?popup=yes&uid={$_GET["uid"]}','$title');
		
		}
		
	var x_impasync_add=function(obj){
      var tempvalue=obj.responseText;
      if(tempvalue.length>0){alert(tempvalue);}
      YahooWin6Hide();
      imapsynclist();
      
     }	

    function AddForm(){
    	YahooWin6(420,'$page?AddForm=yes&uid=$uid','$add');
	}     
	
	function imapSyncEvents(id){
		YahooWin6(700,'$page?events=yes&uid=$uid&id='+id,'$events');
	}
	
	function imapSyncSchedule(id){
		YahooWin6(245,'$page?schedule=yes&uid=$uid&id='+id,'$schedule');
	}
	
	
		
	function impasync_add(){
			var XHR = new XHRConnection();
			XHR.appendData('uid','{$_GET["uid"]}');
			XHR.appendData('remote_imap_server',document.getElementById('remote_imap_server').value);
			XHR.appendData('remote_imap_username',document.getElementById('remote_imap_username').value);
			XHR.appendData('remote_imap_password',document.getElementById('remote_imap_password').value);
			if(document.getElementById('use_ssl').checked){XHR.appendData('use_ssl',1);}else{XHR.appendData('use_ssl',0);}
			document.getElementById('imapsyncadddiv').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			document.getElementById('imapsynclist').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_impasync_add); 
			}

	function imapsynclist(){
		LoadAjax('imapsynclist','$page?imapsynclist=yes&uid={$_GET["uid"]}');
	}
	
	function imapSyncDelete(id){
			var XHR = new XHRConnection();
			XHR.appendData('uid','{$_GET["uid"]}');
			XHR.appendData('imapSyncDelete',id);
			document.getElementById('imapsynclist').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_impasync_add); 
	}
	
	function imapRun(id){
			var XHR = new XHRConnection();
			XHR.appendData('uid','{$_GET["uid"]}');
			XHR.appendData('imapRun',id);
			document.getElementById('imapsynclist').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_impasync_run); 	
	}
	
	var x_impasync_run=function(obj){
     var tempvalue=obj.responseText;
     alert('$apply_upgrade_help');
     imapsynclist();
      
     }	

	function imapStop(pid){
			var XHR = new XHRConnection();
			XHR.appendData('uid','{$_GET["uid"]}');
			XHR.appendData('imapStop',pid);
			document.getElementById('imapsynclist').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_impasync_run); 	
	}     
	
     
     
	
	
	function imapsync_save_schedule(id){
			var XHR = new XHRConnection();
			XHR.appendData('uid','{$_GET["uid"]}');
			XHR.appendData('imapsync_save_schedule',document.getElementById('schedule').value);
			XHR.appendData('id',id);
			document.getElementById('imapsync_save_schedule_div').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_impasync_add); 
	}
	
	
	import_mailbox_run()";
	
	echo $html;
	
	
	
}



function add_popup(){
	$uid=$_GET["uid"];	
$user=new usersMenus();
if(!$user->imapsync_installed){
	$content=Paragraphe('add-remove-64.png','{imapsync_not_installed}','{imapsync_not_installed_text}',null,null,290);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	exit;
}	
$html="
<H3>{add_new_account}</H3>
<div id='imapsyncadddiv'>
		<table style='width:100%'>
		<tr>
			<td class=legend nowrap>{remote_imap_server}:</td>
			<td>" . Field_text('remote_imap_server',null,"font-size:13px",null) . "</td>
		</tr>
		<tr>
			<td class=legend nowrap>{remote_imap_username}:</td>
			<td>" . Field_text('remote_imap_username',null,"font-size:13px",null) . "</td>
		</tr>
		<tr>
			<td class=legend nowrap>{remote_imap_password}:</td>
			<td>" . Field_password('remote_imap_password',null,"font-size:13px",null) . "</td>
		</tr>
		<tr>
			<td class=legend nowrap>{use_ssl}:</td>
			<td>". Field_checkbox("use_ssl",1,0)."</td>
		</tr>		
		<tr>
			<td colspan=2 align='right'>
			<hr>
			". button("{add}","impasync_add()")."
			
		</tr>
</table>
</div>";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);

}

function popup(){
$uid=$_GET["uid"];	
$user=new usersMenus();
if(!$user->imapsync_installed){
	$content=Paragraphe('add-remove-64.png','{imapsync_not_installed}','{imapsync_not_installed_text}',null,null,290);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	exit;
}


$html="
<p style='font-size:13px'>
<span style='float:right;padding:3px;'>". button("{add}","AddForm()")."</span>
{import_mailbox_text}</p>
<div style='text-align:right'>". imgtootltip("20-refresh.png","{refresh}","imapsynclist()")."</div>
<div id='imapsynclist' style='width:100%;height:250px;overflow:auto'></div>


<script>
	imapsynclist();
</script>
";


$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}
	

function add_account(){
	
	$sql="INSERT INTO imapsync (`uid`,`imap_server`,`username`,`password`,`ssl`,`enabled`) VALUES('{$_GET["uid"]}',
	'{$_GET["remote_imap_server"]}','{$_GET["remote_imap_username"]}','{$_GET["remote_imap_password"]}',
	'{$_GET["use_ssl"]}','1')";

	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;echo "\n$sql";}
			
}


function imapsynclist(){
	$sock=new sockets();
	
	$cron=new cron_macros();
	while (list ($index, $line) = each ($cron->cron_defined_macros) ){
		if($index==0){continue;}
		
		$retour[$line]=$index;
	}	
	
	
	$sql="SELECT * FROM imapsync WHERE uid='{$_GET["uid"]}'";
	$html="<table style='width:99%'>
	<tr>
		<th>{status}</th>
		<th>{server}</th>
		<th>{username}</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
	</tr>
	
	";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
 	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
 		$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?procstat={$ligne["pid"]}")));
 		if($array["since"]==null){$ligne["state"]=0;}
 		switch ($ligne["state"]) {
 			case -1:$img="status_service_removed.png";$text="{error}";$run=imgtootltip("run-24.png","{run}","imapRun({$ligne["ID"]})");break;
 			case 0: $img="status_service_wait.png";$text="{sleeping}";$run=imgtootltip("run-24.png","{run}","imapRun({$ligne["ID"]})");break;
 			case 1: $img="status_service_run.png";$text="{running}: pid {$ligne["pid"]}";$run=imgtootltip("x-delete.gif","{stop}","imapStop({$ligne["ID"]})");break;

 			
 			
 		}
 	 	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?procstat={$ligne["pid"]}")));
 		if($array["since"]<>null){
 			$running="{running}: {since} {$array["since"]}";
 		} 		
 		
 		if($ligne["CronSchedule"]<>null){$sched="<br>{each}:".$retour[$ligne["CronSchedule"]];}else{$sched=null;}
 	
 		$status=imgtootltip($img,$text."<br>$running","imapSyncEvents({$ligne["ID"]})");
 		$schedule=imgtootltip("time-30.png","{schedule}$sched","imapSyncSchedule({$ligne["ID"]})");

 		
 		
 		
 		$html=$html."
 		<tr ". CellRollOver().">
 			<td width=1% align='center'>$status</td>
 			<td><code style='font-size:14px'>{$ligne["imap_server"]}</code></td>
 			<td><code style='font-size:14px'>{$ligne["username"]}</code></td>
 			<td width=1% align='center'>$schedule</code></td>
 			<td width=1% align='center'>$run</td>
 			<td width=1% align='center'>". imgtootltip("delete-32.png","{delete}","imapSyncDelete({$ligne["ID"]})")."</td>
 		</tr>
 			
 		";
 	}
	
 	$html=$html."</table>";
 	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function imapSyncDelete(){
	$sql="DELETE FROM imapsync WHERE ID={$_GET["imapSyncDelete"]}";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?imapsync-cron=yes");	
	
}

function events(){
	$tr=array();
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?imapsync-events={$_GET["id"]}")));
	
	if(is_array($datas)){
		while (list ($index, $line) = each ($datas) ){
			if($line==null){continue;}
			$tr[]="<div style='font-size:12px;padding:3px'>".htmlspecialchars($line)."</div>";
		}
	}
	
	$html="
	<div style='width:100%;height:450px;overflow:auto;'>".implode("\n",$tr)."</div>";
	echo $html;
}

function schedule(){
	$cron=new cron_macros();
	while (list ($index, $line) = each ($cron->cron_defined_macros) ){
		if($index==0){continue;}
		$array[$index]=$index;
		$retour[$line]=$index;
	}
	
$sql="SELECT CronSchedule FROM imapsync WHERE ID='{$_GET["id"]}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	
	$array[null]="{disabled}";
	
	$field=Field_array_Hash($array,"schedule",$retour[$ligne["CronSchedule"]],null,null,0,"font-size:15px");

	$html="
	<div id='imapsync_save_schedule_div'>
	<H3>{schedule}</H3>
	<table style='width:100%'>
	<tr>
		<td class=legend>{each}:</td>
		<td>$field</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>
			<hr>
				". button("{apply}","imapsync_save_schedule('{$_GET["id"]}')")."
		</td>
	</tr>
	</table>

	</div>";
	

	 	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function schedule_save(){
	$cron=new cron_macros();
	$data=$cron->cron_defined_macros[$_GET["imapsync_save_schedule"]];
	$sql="UPDATE imapsync SET CronSchedule='$data' WHERE ID='{$_GET["id"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n$sql";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?imapsync-cron=yes");
	}
	
function imapRun(){
	$id=$_GET["imapRun"];
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?imapsync-run=$id");	
}
function imapStop(){
	$id=$_GET["imapStop"];
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?imapsync-stop=$id");		
}

	
	
function permissions(){

	//sync-64.png
	//imgtootltip("icon_sync.gif",'{export_mailbox_text}',"Loadjs('mailsync.php?uid=$uid');") 
	
$usersprivs=new usersMenus();
if(!$usersprivs->AsAnAdministratorGeneric){
		if(!$usersprivs->AllowFetchMails){
			return false;
			}
		if($_SESSION["uid"]<>$_GET["userid"]){return false;}
	}

	return true;
	
}




?>