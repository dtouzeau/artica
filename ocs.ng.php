<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.os.system.inc');

	
	$user=new usersMenus();
	if(($user->AsSystemAdministrator==false) OR ($user->AsSambaAdministrator==false)) {
		$tpl=new templates();
		$text=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
		$text=replace_accents(html_entity_decode($text));
		echo "alert('$text');";
		exit;
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["params-web"])){params_web();exit;}
	if(isset($_GET["ocswebservername"])){params_web_save();exit;}
	if(isset($_GET["deploy-js"])){agent_deploy_js();exit;}
	if(isset($_GET["deploy-popup"])){agent_deploy_popup();exit;}
	if(isset($_GET["agent_deploy_add_task"])){agent_deploy_add_task();exit;}
	
	js();
	
	
function agent_deploy_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$prefix=str_replace(".","_",$page);
	$uid=$_GET["deploy-js"];
	$title=$tpl->_ENGINE_parse_body("$uid::{OCS_DEPLOY_WINDOWS}","domains.edit.user.php");
	$html="
	
	function {$prefix}LoadMain(){
		LoadWinORG('550','$page?deploy-popup=$uid','$title');
		
	}
	
var x_agent_deploy_add_task=function (obj) {
	var results=obj.responseText;
	if (results.length>0){alert(results);}
	Loadjs('computer.install.php?uid=$uid');
	WinORGHide();
}	
	
	function agent_deploy_add_task(){
		var id_files=document.getElementById('id_files').value;
		if(id_files.length==0){return;}
		var XHR = new XHRConnection();
		XHR.appendData('agent_deploy_add_task',id_files);
		XHR.appendData('uid','$uid');
		document.getElementById('ocs-deploy-div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_agent_deploy_add_task);
		
	}
	
	

	{$prefix}LoadMain();";
	
	echo $html;
		
	
}
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_OCSI}');
	$prefix=str_replace(".","_",$page);
	$html="
	
	function {$prefix}LoadMain(){
		LoadWinORG('650','$page?popup=yes','$title');
		}	
		
	function ocs_web_params(){
		YahooWin2('500','$page?params-web=yes');
	}
	
	function EditRemoteSoftware(id){
		YahooWin2('500','$page?popup-edit-software='+id);
	}
	
	function RefreshSoftwaresList(){
		LoadAjax('software_list','$page?sflist=yes');
	}
	
	
var x_DelRemoteSoftware=function (obj) {
	var results=obj.responseText;
	if (results.length>0){alert(results);}
	RefreshSoftwaresList();
}

function DelRemoteSoftware(id){
		var XHR = new XHRConnection();
		XHR.appendData('DelRemoteSoftware',id);
		document.getElementById('software_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_DelRemoteSoftware);
}

var x_SavePackegInfos=function (obj) {
	var results=obj.responseText;
	if (results.length>0){alert(results);}
	RefreshSoftwaresList();
	YahooWin2Hide();
}

function SavePackegInfos(id){
		var XHR = new XHRConnection();
		XHR.appendData('SavePackegInfos',id);
		XHR.appendData('description',document.getElementById('txtDescription').value);
		XHR.appendData('commandline',document.getElementById('commandline').value);
		XHR.appendData('ExecuteAfter',document.getElementById('ExecuteAfter').value);
		XHR.appendData('MinutesToWait',document.getElementById('MinutesToWait').value);
		document.getElementById('packageinfo').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SavePackegInfos);
}

var x_SaveWebServerName=function (obj) {
	var results=obj.responseText;
	if (results.length>0){alert(results);}
	ocs_web_params();
	
}



function SaveWebServerName(){
		var XHR = new XHRConnection();
		XHR.appendData('ocswebservername',document.getElementById('ocswebservername').value);
		if(document.getElementById('ocswebservernameEnabled').checked){
			XHR.appendData('ocswebservernameEnabled',1);
		}else{
			XHR.appendData('ocswebservernameEnabled',0);
		}
		document.getElementById('ocswebservername_div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveWebServerName);
}
	

{$prefix}LoadMain();
	
";

echo $html;
}	


function popup(){
	
	$users=new usersMenus();
	if(!$users->OCSI_INSTALLED){
		$notinstalled=Paragraphe("64-ocs.png","{APP_OCSI}: {feature_not_installed}","{ERROR_NOT_INSTALLED_REDIRECT}",
		"javascript:Loadjs('setup.index.progress.php?product=APP_OCSI&start-install=yes');");
		
		$html="<H1>{APP_OCSI}</H1>
		<center>$notinstalled</center>
		";
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);	
		exit;
		
	}
	
	$webparm=Paragraphe("web-64.png","{ocs_web_params}","{ocs_web_params_text}","javascript:ocs_web_params()");
	$sock=new sockets();
	
		
	
	$html="<H1>{APP_OCSI}</H1>
	<img src='img/ocs-banner.gif' style='margin-top: -14px;margin-left:-10px'>
	<p class=caption>{APP_OCSI_TEXT}</p>
	<table style='width:100%'>
		<tr>
			<td valign='top'>$webparm</td>
		</tr>
	</table>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function params_web_save(){
	$sock=new sockets();
	
	$sock->SET_INFO("ocswebservername",$_GET["ocswebservername"]);
	$sock->SET_INFO("ocswebservernameEnabled",$_GET["ocswebservernameEnabled"]);
	$sock->getFrameWork("artica-groupware.php?restart=yes");
}

function params_web(){
	
	$sock=new sockets();
	$ocswebservername=$sock->GET_INFO("ocswebservername");
	$ocswebservernameEnabled=$sock->GET_INFO("ocswebservernameEnabled");
	if($ocswebservernameEnabled==null){$ocswebservernameEnabled=1;}
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	if($ocswebservername==null){$ocswebservername="ocs.localhost.localdomain";}

	$www="<a href='#' 
		OnClick=\"javascript:s_PopUpFull('http://$ocswebservername:$ApacheGroupWarePort',800,600)\"
		style='font-size:12px;font-weight:bold'
		>
		{website}:http://$ocswebservername:$ApacheGroupWarePort</a>
		";	
	
$html="<H1>{ocs_web_params}</H1>
<div id='ocswebservername_div'>

	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%>
		<img src='img/web-64.png'>
	</td>
	<td valign='top'>
			<p class=caption>{ocs_web_params_text}</p>
			<table style='width:100%'>
			<tr>
				<td colspan=2><hr><div>$www</div><hr></td>
			</tr>
				<tr>
					<td valign='top' class=legend>{enable}:</td>
					<td valign='top'>". Field_checkbox("ocswebservernameEnabled",1,$ocswebservernameEnabled)."</td>
				</tr>			
				<tr>
					<td valign='top' class=legend>{servername}:</td>
					<td valign='top'>". Field_text("ocswebservername",$ocswebservername,"width:180px")."</td>
				</tr>
				<tr>
					<td colspan=2 align='right'>
					<hr>
					". button("{edit}","SaveWebServerName()")."
					
					
				</tr>
				
			</table>
	</td>
	</tr>
	</table>
	</div>
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function agent_deploy_popup(){
	
	$q=new mysql();
	$sql="SELECT id_files,filename FROM files_storage WHERE OCS_PACKAGE=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$array[$ligne["id_files"]]=$ligne["filename"];
	}
	$array[null]="{select}";
	
	$package=Field_array_Hash($array,"id_files");
	
	$html="
	<H1>{OCS_DEPLOY_WINDOWS}</H1>
	<p class=caption>{OCS_DEPLOY_WINDOWS_TEXT}</p>
	<table style='width:100%' class=table_form>
	<tr>
		<td valign='top' class=legend nowrap>{filename}:</td>
		<td>$package</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr><input type='button' OnClick=\"javascript:agent_deploy_add_task();\" value='{deploy}&nbsp;&raquo;'></td>
	</tr>
	</table>
	<div id='ocs-deploy-div'></div>;
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,'domains.edit.user.php');	
	
}

function agent_deploy_add_task(){
	$file_id=$_GET["agent_deploy_add_task"];
	$uid=$_GET["uid"];
	$commandline=null;
	$debug_mode=1;
	
	$sql_insert="INSERT INTO deploy_tasks (files_id,computer_id,commandline,username,password,debug_mode)
	VALUES('$file_id','$uid','$commandline','$username','$password','$debug_mode');
	";
	$q=new mysql();
	$q->QUERY_SQL($sql_insert,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
	}
	
	$sock=new sockets();
	$sock->getFrameWork('cmd.php?LaunchRemoteInstall=yes');
	
}


?>