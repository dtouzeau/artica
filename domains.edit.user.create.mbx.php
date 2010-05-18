<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.pure-ftpd.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/charts.php');
	include_once('ressources/class.mimedefang.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.ini.inc');	
	include_once('ressources/class.ocs.inc');
	include_once(dirname(__FILE__). "/ressources/class.cyrus.inc");

	$usr=new usersMenus();
	
	
	if($usr->AsMailBoxAdministrator==false){
		$tpl=new Templates();
		echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";
		die();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["Status"])){echo Status($_GET["Status"]);exit;}
	if(isset($_GET["checkrights"])){checkrights();exit;}
	if(isset($_GET["CreateMBX"])){CreateMBX();exit;}
	if(isset($_GET["MBXSetACL"])){MBXSetACL();exit;}
	if(isset($_GET["MBXSubscribe"])){MBXSubscribe();exit;}
	if(isset($_GET["StatusFailed"])){StatusFailed();exit;}
	
	
js();

//error_creating_mailbox
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$uid=$_GET["uid"];
	$title=$tpl->_ENGINE_parse_body('{mailbox_main_settings}');
	$html="
	
	function LoadMBXInterface(){
		RTMMail('550','$page?popup=yes','$title');
		setTimeout('MBXCheckrights()',1000);
	}
	
	function mbxFilogs(logs){
		logs=escapeVal(logs,'<br>');
		var MBX_textlogs=document.getElementById('MBX_textlogs').innerHTML;
		MBX_textlogs='<div style=\"margin:3px;padding:3px;border-bottom:1px solid #CCCCCC\"><code>'+logs+'</code></div>'+MBX_textlogs;
		document.getElementById('MBX_textlogs').innerHTML=MBX_textlogs;
	}
	
	var x_MBXChangeStatus= function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('progression_mbx_compile').innerHTML=tempvalue;
		
	}	

	function MBXChangeStatusFailed(){
		var XHR = new XHRConnection();
		XHR.appendData('StatusFailed','yes');
		XHR.sendAndLoad('$page', 'GET',x_MBXChangeStatus);	
	}	
	
	function MBXChangeStatus(number){
		var XHR = new XHRConnection();
		XHR.appendData('Status',number);
		XHR.sendAndLoad('$page', 'GET',x_MBXChangeStatus);	
	}
	
	var x_MBXCheckrights= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){
			mbxFilogs(tempvalue);
			finish_failed();
			return;
		}
		CreateMBX();
	}		

	function MBXCheckrights(){
		MBXChangeStatus(10);
		var XHR = new XHRConnection();
		XHR.appendData('checkrights','$uid');
		XHR.appendData('MailboxActive',document.getElementById('MailboxActive').value);
		XHR.appendData('MailBoxMaxSize',document.getElementById('MailBoxMaxSize').value);
		XHR.appendData('mp_l',document.getElementById('mp_l').value);
		XHR.appendData('mp_r',document.getElementById('mp_r').value); 
		XHR.appendData('mp_s',document.getElementById('mp_s').value);
		XHR.appendData('mp_w',document.getElementById('mp_w').value);
		XHR.appendData('mp_i',document.getElementById('mp_i').value);
		XHR.appendData('mp_p',document.getElementById('mp_p').value);
		XHR.appendData('mp_c',document.getElementById('mp_c').value);
		XHR.appendData('mp_d',document.getElementById('mp_d').value);
		XHR.appendData('mp_a',document.getElementById('mp_a').value);		
		XHR.sendAndLoad('$page', 'GET',x_MBXCheckrights);	
	}
	
	
	var x_CreateMBX= function (obj) {
		var tempvalue=obj.responseText;
		mbxFilogs(tempvalue);
		MBXSetACL();
	}

	var x_MBXSetACL= function (obj) {
		var tempvalue=obj.responseText;
		mbxFilogs(tempvalue);
		MBXSubscribe();
		
	}	
	

	var x_MBXSubscribe= function (obj) {
		var tempvalue=obj.responseText;
		mbxFilogs(tempvalue);
		finish();
		RefreshTab('container-users-tabs');
		YahooWinHide();
	}		
	
	function CreateMBX(){
		MBXChangeStatus(20);
		var XHR = new XHRConnection();
		XHR.appendData('CreateMBX','$uid');
		XHR.sendAndLoad('$page', 'GET',x_CreateMBX);	
	}	
	
	function MBXSetACL(){
		MBXChangeStatus(50);
		var XHR = new XHRConnection();
		XHR.appendData('MBXSetACL','$uid');
		XHR.sendAndLoad('$page', 'GET',x_MBXSetACL);	
	}

	function MBXSubscribe(){
		MBXChangeStatus(80);
		var XHR = new XHRConnection();
		mbxFilogs('subscription');
		XHR.appendData('MBXSubscribe','$uid');
		XHR.sendAndLoad('$page', 'GET',x_MBXSubscribe);	
	}	

	function finish(){
		MBXChangeStatus(100);
		document.getElementById('wait_image_mbx').innerHTML='&nbsp;';
		RefreshTab('container-users-tabs');
		YahooWinHide();
		
	}

	function finish_failed(){
		document.getElementById('wait_image_mbx').innerHTML='&nbsp;';
		MBXChangeStatusFailed();
		RefreshTab('container-users-tabs');
		YahooWinHide();
	}
	
	function escapeVal(content,replaceWith){
		content = escape(content) 
	
			for(i=0; i<content.length; i++){
				if(content.indexOf(\"%0D%0A\") > -1){
					content=content.replace(\"%0D%0A\",replaceWith)
				}
				else if(content.indexOf(\"%0A\") > -1){
					content=content.replace(\"%0A\",replaceWith)
				}
				else if(content.indexOf(\"%0D\") > -1){
					content=content.replace(\"%0D\",replaceWith)
				}
	
			}	
		return unescape(content);
	}		
	
	
	
	LoadMBXInterface();";
	echo $html;
	
}

function popup(){
	$users=new usersMenus();
	$tpl=new templates();
	if(!$users->AsMailBoxAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "<H3>$error<H3>";
		die();
	}
	$pourc=0;
	$table=Status(0);
	$color="#5DD13D";
	$html="<H1>{mailbox_main_settings}</H1>
	
	<table style='width:100%'>
	<tr>
		<td width=1%><div id='wait_image_mbx'><img src='img/wait.gif'></div>
		</td>
		<td width=99%>
			<table style='width:100%'>
			<tr>
			<td>
				<div style='width:100%;background-color:white;padding-left:0px;border:1px solid $color'>
					<div id='progression_mbx_compile'>
						<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
							<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>{$pourc}%</strong></center>
						</div>
					</div>
				</div>
			</td>
			</tr>
			</table>		
		</td>
	</tr>
	</table>
	<br>
	" . RoundedLightWhite("<div id='MBX_textlogs' style='width:99%;height:120px;overflow:auto'></div>")."";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function Status($pourc){
$color="#5DD13D";	
$html="
	<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
		<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>{$pourc}%</strong></center>
	</div>
";	


return $html;
	
}

function StatusFailed(){
$color="red";	
$html="
	<div style='width:100%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
		<strong style='color:white;font-size:12px;font-weight:bold'>100%</strong></center>
	</div>
";	


return $html;	
}

function checkrights(){
	$tpl=new templates();
	$uid=$_GET["checkrights"];
	$user=new user($uid);
	
	$acls="[mailbox]\n";
	
	while (list ($num, $val) = each ($_GET) ){
		if(preg_match('#mp_([a-zA-Z])#',$num,$re)){
			writelogs("set acls {$re[1]}=$val on mailbox",__FUNCTION__,__FILE__);
			$acls=$acls."{$re[1]}=$val\n";
		}
	}

	$user=new user($uid);
	$user->MailBoxMaxSize=$_GET["MailBoxMaxSize"];
	$user->MailboxActive=strtoupper($_GET["MailboxActive"]);
	$user->MailboxSecurityParameters=$acls;
	
	if(!$user->add_user()){echo $user->ldap_error;}	
	
	
	if($user->MailboxActive<>"TRUE"){
		echo $tpl->_ENGINE_parse_body("$uid:{mailbox_disabled} ($user->MailboxActive)");
	}
}

function CreateMBX(){
	$tpl=new templates();
	$uid=$_GET["CreateMBX"];
	$cyrus=new cyrus();
	if(!$cyrus->MailBoxExists($uid)){
		echo $tpl->_ENGINE_parse_body("$uid: $cyrus->cyrus_infos");
		$cyrus->CreateMailbox($uid,1);
		echo $tpl->_ENGINE_parse_body($cyrus->cyrus_infos);
	}else{
		echo $tpl->_ENGINE_parse_body("$uid: {mailbox_already_exists}\n");
	}
}

function MBXSetACL(){
$tpl=new templates();
	$uid=$_GET["MBXSetACL"];
	$cyrus=new cyrus();	
	$cyrus->CreateMailbox($uid);
	echo $tpl->_ENGINE_parse_body($cyrus->cyrus_infos);
}

function MBXSubscribe(){
	$tpl=new templates();
	$uid=$_GET["MBXSubscribe"];

}
	
		
		
		
?>