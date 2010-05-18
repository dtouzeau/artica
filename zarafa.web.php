<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
	
	$user=new usersMenus();
	if($user->AsMailBoxAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["ZarafaApachePort"])){SAVE();exit;}
	
	js();
	
	
function js(){
$page=CurrentPageName();
$users=new usersMenus();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_ZARAFA_WEB}');

$html="

function APP_ZARAFA_WEB(){
	YahooWin3('450','$page?popup=yes&','$title');
	
	}
	
var X_APP_ZARAFA_WEB_SAVE= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	document.getElementById('zrfa-logo').src='img/zarafa-web-128.png';
	}	
	

	
function APP_ZARAFA_WEB_SAVE(){
	var XHR = new XHRConnection();
	XHR.appendData('ZarafaApachePort',document.getElementById('ZarafaApachePort').value);
	XHR.appendData('ZarafaApacheSSL',document.getElementById('ZarafaApacheSSL').value);
	XHR.appendData('ou','$ou_decrypted');
	document.getElementById('zrfa-logo').src='img/wait_verybig.gif';
	XHR.sendAndLoad('$page', 'GET',X_APP_ZARAFA_WEB_SAVE);	
}
	
APP_ZARAFA_WEB();
";

echo $html;	
	
}

function SAVE(){
	$sock=new sockets();
	$sock->SET_INFO("ZarafaApachePort",trim($_GET["ZarafaApachePort"]));
	$sock->SET_INFO("ZarafaApacheSSL",trim($_GET["ZarafaApacheSSL"]));
	$sock->getFrameWork("cmd.php?zarafa-restart-web=yes");
}

function popup(){
	
	
$sock=new sockets();
$ZarafaApachePort=$sock->GET_INFO("ZarafaApachePort");
$enable_ssl=$sock->GET_INFO("ZarafaApacheSSL");	
if($ZarafaApachePort==null){$ZarafaApachePort="9010";}
if($enable_ssl==null){$enable_ssl="0";}
$html="
<table style='width:100%'>
<tr>
	<td valign='top'><img id='zrfa-logo' src='img/zarafa-web-128.png'></td>
	<td valign='top'>
		<table style='width:100%'>
			<tr>
				<td class=legend style='font-size:12px'>{listen_port}:</td>
				<td>". Field_text("ZarafaApachePort",$ZarafaApachePort,"font-size:12px;padding:3px;width:60px")."</td>
			</tr>
			<tr>
				<td class=legend style='font-size:12px'>{enable_ssl}:</td>
				<td>". Field_checkbox("ZarafaApacheSSL",1,$enable_ssl)."</td>
			</tr>			
	
			<tr>
				<td colspan=2 align='right'>
				<hr>
					". button("{apply}","APP_ZARAFA_WEB_SAVE()")."
				</td>
			</tr>
	</td>
	</tr>
</table>


";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}




?>