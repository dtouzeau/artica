<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.rtmm.tools.inc');

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["oldorg"])){ChangeOrg();exit;}
if(isset($_GET["servername"])){Upload();exit;}
if(isset($_GET["OUlanguage"])){OUSettings();exit;}
if(isset($_GET["export"])){EXPORT_ORG();exit;}

js();



function js(){
$page=CurrentPageName();
$prefix=str_replace(".","_",$page);
$prefix=str_replace("-","_",$prefix);
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{ORG_SETTINGS}',"domains.manage.org.index.php");
$ou=$_GET["ou"];
$ou_encrypted=base64_encode($ou);	
	$users=new usersMenus();
	if(!$users->AsArticaAdministrator){
		$func="SwitchMoveOrgUser();";
		if(!$users->AsOrgAdmin){
			$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
			echo "alert('$error')";
			die();
		}
	}
	if($users->AsArticaAdministrator){$func="SwitchMoveOrgAdmin();";}
	
	$start="{$prefix}Loadpage();";
	if(isset($_GET["js-export"])){
		$title=$tpl->_ENGINE_parse_body("{EXPORT_ORG}::$ou");
		$start="{$prefix}export();";
	}
	
	

$html="
var {$prefix}timeout=0;

	function {$prefix}Loadpage(){
		RTMMail('550','$page?popup=yes&ou=$ou_encrypted','$title');
	}
	
	function {$prefix}export(){
		RTMMail('550','$page?export=yes&ou=$ou_encrypted','$title');
	}
	
	var x_RenameOrganization=function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		$func
	}	
	
	function RenameOrganization(){	
		var oldorg='$ou';
		var neworg=document.getElementById('organization_name').value;
		if(oldorg==neworg){return;}
		var XHR = new XHRConnection();
		XHR.appendData('oldorg',oldorg);
		XHR.appendData('neworg',neworg);		
		XHR.sendAndLoad('$page', 'GET', x_RenameOrganization);				
	}
	
	function SwitchMoveOrgUser(){
		MyHref('logoff.php');
	}
	
	function SwitchMoveOrgAdmin(){
		Loadjs('domains.index.php?js=yes');
		RTMMailHide();
	}
	
	var x_UploadOrganization=function (obj) {
		var results=obj.responseText;
		document.getElementById('uploadinfos').innerHTML=results;
		
	}

	var x_SaveOUDefSettings=function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		{$prefix}Loadpage();
		
	}	
	
	function SaveOUDefSettings(){
	 var language=document.getElementById('OUlanguage').value;
		var XHR = new XHRConnection();
		XHR.appendData('OUlanguage',language);
		XHR.appendData('ou','$ou');		
		document.getElementById('{$ou}_div').innerHTML='<center><img src=img/wait_verybig.gif></center>';	
		XHR.sendAndLoad('$page', 'GET', x_SaveOUDefSettings);			 
	 
	}


	
	function UploadOrganization(){
		var XHR = new XHRConnection();
		XHR.appendData('ou','$ou');
		XHR.appendData('servername',document.getElementById('servername').value);
		XHR.appendData('port',document.getElementById('port').value);
		XHR.appendData('username',document.getElementById('username').value);	
		XHR.appendData('password',document.getElementById('password').value);
		document.getElementById('uploadinfos').innerHTML='<center><img src=img/wait_verybig.gif></center>';							
		XHR.sendAndLoad('$page', 'GET', x_UploadOrganization);			
	}


$start
";
	
	echo $html;
}


function popup(){
	$ou=base64_decode($_GET["ou"]);
	$users=new usersMenus();
	
	$ldap=new clladp();
	$hash=$ldap->OUDatas($ou);
	$privs=$ldap->_ParsePrivieleges($hash["ArticaGroupPrivileges"]);
	$langdef=$privs["ForceLanguageUsers"];
	
	$lang=DirFolders('ressources/language');
	unset($lang["language"]);
	$lang[null]="{default}";
	$language=Field_array_Hash($lang,'OUlanguage',$langdef,null,null,0,"font-size:13px;padding:3px");	
	
	
	//ArticaGroupPrivileges
	
	$form="<table style='width:100%'>
	<tr>
	<td valign='top' class=legend nowrap style='font-size:13px'>{rename_org}:</td>
	<td valign='top'>". Field_text('organization_name',$ou,"font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>
		<hr>". button("{rename}","RenameOrganization()")."
		
	</tr>
	</table>
	
	";
	
	$form3="<table style='width:100%'>
	<tr>
	<td valign='top' class=legend nowrap style='font-size:13px'>{default_language}:</td>
	<td valign='top'>$language</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>
		<hr>". button("{apply}","SaveOUDefSettings()")."
		</td>
	</tr>
	</table>";
	
	
	

	
	
	$html="<H1>$ou</H1>
	<p class=caption>{ORG_SETTINGS_TEXT}</p>
	<div id='{$ou}_div'>
		$form3
		<br>
		$form
	</div>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"domains.manage.org.index.php");
	
}

function EXPORT_ORG(){
$ou=base64_decode($_GET["ou"]);	
$session="CopyOrgServerDatas".md5($_SESSION["uid"]);
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO($session));
	$users=new usersMenus();
	
	$form2="
	<table style='width:100%'>
	<tr>
	<td valign='top' class=legend nowrap>{REMOTE_ARTICA_SERVER}:</td>
	<td valign='top' >". Field_text('servername',$ini->_params["CONF"]["servername"])."</td>
	</tr>
	<tr>
	<td valign='top' class=legend nowrap>{REMOTE_ARTICA_SERVER_PORT}:</td>
	<td valign='top' >". Field_text('port',$ini->_params["CONF"]["port"])."</td>
	</tr>
	<tr>
	<td valign='top' class=legend nowrap>{REMOTE_ARTICA_USERNAME}:</td>
	<td valign='top' >". Field_text('username',$ini->_params["CONF"]["username"])."</td>
	</tr>	
	<tr>
	<td valign='top' class=legend nowrap>{REMOTE_ARTICA_PASSWORD}:</td>
	<td valign='top'>". Field_password('password',$ini->_params["CONF"]["password"])."</td>
	</tr>		
	<tr>
		<td colspan=2 align='right'>
		<hr>". button("{upload}","UploadOrganization()")."
		</td>
	</tr>
	</table>
	
	";
		
	$tpl=new templates();
	if($users->CURL_PATH==null){$error="<hr>{ERROR_CURL_NOT_INSTALLED}<hr>";echo $tpl->_ENGINE_parse_body($error);exit;}

	$html="<H1>{duplicate_to_remote_server}</H1>
	$form2
	<br>
	<div style='width:100%;height:200px;overflow:auto;font-size:11px;;padding:3px;border:1px solid #CCCCCC' id='uploadinfos'>$error</div>
	
	";
	

	echo $tpl->_ENGINE_parse_body($html,"domains.manage.org.index.php");
		
}

function ChangeOrg(){
	$tpl=new templates();
	$users=new usersMenus();
	if(!$users->AsArticaAdministrator){
		if(!$users->AsOrgAdmin){
			$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
			echo html_entity_decode("$error");
			die();
		}
	}
	
	$ldap=new clladp();
	$dn="ou={$_GET["neworg"]},dc=organizations,$ldap->suffix";

	if($ldap->ExistsDN($dn)){
		echo html_entity_decode($tpl->_ENGINE_parse_body("{$_GET["neworg"]} {error_domain_exists}"));
		exit;
	}
	
	$sock=new sockets();
	$datas=$sock->getfile("MoveLDAPOu:{$_GET["neworg"]};{$_GET["oldorg"]}");
	$tbl=explode("\n",$datas);
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		echo html_entity_decode($ligne)."\n";
	}

}
function Upload(){
	$ou=$_GET["ou"];
	$ini=new Bs_IniHandler();
	while (list ($num, $ligne) = each ($_GET) ){
		$ini->_params["CONF"][$num]=$ligne;
		
	}
	
	$session="CopyOrgServerDatas".md5($_SESSION["uid"]);
	$sock=new sockets();
	$sock->SaveConfigFile($ini->toString(),$session);
	$tbl=unserialize(base64_decode($sock->getFrameWork("cmd.php?upload-organization=". base64_encode($ou)."&config-file=$session")));
	
	$tpl=new templates();
	if(!is_array($tbl)){echo $tpl->_ENGINE_parse_body("{failed}");return;}

	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne==null)){continue;}
		echo "<div><code>".$tpl->_ENGINE_parse_body(htmlspecialchars($ligne))."</code></div>";
		
	}
}

function OUSettings(){
$ldap=new clladp();
	$ou=$_GET["ou"];
	$hash=$ldap->OUDatas($ou);
	$users=new usersMenus();
	$privs=$users->_ParsePrivieleges($hash["ArticaGroupPrivileges"]);
	$privs["ForceLanguageUsers"]=$_GET["OUlanguage"];
	$conf=$users->_BuildPrivileges($privs);
	
	$dn="ou=$ou,dc=organizations,$ldap->suffix";
	$upd["ArticaGroupPrivileges"][0]=$conf;
	if(!$ldap->Ldap_modify($dn,$upd)){
		echo $ldap->ldap_last_error;
	}
	
	
}


?>