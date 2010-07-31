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
	YahooWin3('550','$page?popup=yes&','$title');
	
	}
	
var X_APP_ZARAFA_WEB_SAVE= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	document.getElementById('zrfa-logo').src='img/zarafa-web-128.png';
	}	
	

	
function APP_ZARAFA_WEB_SAVE(){
	var XHR = new XHRConnection();
	XHR.appendData('ZarafaApachePort',document.getElementById('ZarafaApachePort').value);
	XHR.appendData('ZarafaiCalPort',document.getElementById('ZarafaiCalPort').value);
	
	if(document.getElementById('ZarafaApacheSSL').checked){
		XHR.appendData('ZarafaApacheSSL',1);
	}else{
		XHR.appendData('ZarafaApacheSSL',0);
	}

	if(document.getElementById('ZarafaiCalEnable').checked){
		XHR.appendData('ZarafaiCalEnable',1);
	}else{
		XHR.appendData('ZarafaiCalEnable',0);
	}		

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
	
	$sock->SET_INFO("ZarafaiCalPort",trim($_GET["ZarafaiCalPort"]));
	$sock->SET_INFO("ZarafaiCalEnable",trim($_GET["ZarafaiCalEnable"]));
		
	
	
	
	$sock->getFrameWork("cmd.php?zarafa-restart-web=yes");
}

function popup(){
	
$users=new usersMenus();

if(!$users->APACHE_INSTALLED){
$html="
<table style='width:100%'>
<tr>
	<td valign='top'><img id='zrfa-logo' src='img/zarfa-web-error-128.png'></td>
	<td valign='top'>	
		<table style='width:100%'>
		<tr>
			<td colspan=2><H3>{WEBMAIL}</H3>
			<p style='font-size:14px;color:#C61010'>{ZARAFA_ERROR_NO_APACHE}</p>
			
			</td>
		</tr>
		</table>
	</td>
	</tr>
	</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	return;
}


$sock=new sockets();
$ZarafaApachePort=$sock->GET_INFO("ZarafaApachePort");
$enable_ssl=$sock->GET_INFO("ZarafaApacheSSL");	
if($ZarafaApachePort==null){$ZarafaApachePort="9010";}

$ZarafaiCalEnable=$sock->GET_INFO("ZarafaiCalEnable");
$$ZarafaiCalPort=$sock->GET_INFO('ZarafaiCalPort');	
if($ZarafaiCalPort==null){$ZarafaiCalPort="8088";}



if($enable_ssl==null){$enable_ssl="0";}
if($ZarafaiCalEnable==null){$ZarafaiCalEnable=0;}


$html="
<table style='width:100%'>
<tr>
	<td valign='top'><img id='zrfa-logo' src='img/zarafa-web-128.png'></td>
	<td valign='top'>
	
		<table style='width:100%'>
		<tr><td colspan=2><H3>{WEBMAIL}</H3></td></tr>
			<tr>
				<td class=legend style='font-size:12px'>{listen_port}:</td>
				<td>". Field_text("ZarafaApachePort",$ZarafaApachePort,"font-size:12px;padding:3px;width:60px")."</td>
			</tr>
			<tr>
				<td class=legend style='font-size:12px'>{enable_ssl}:</td>
				<td>". Field_checkbox("ZarafaApacheSSL",1,$enable_ssl)."</td>
			</tr>	
		</table>

		<p>&nbsp;</p>
		
	<table style='width:100%'>
		<tr><td colspan=2><H3>{APP_ZARAFA_ICAL}</H3></td></tr>
			<tr>
				<td class=legend style='font-size:12px'>{listen_port}:</td>
				<td>". Field_text("ZarafaiCalPort",$ZarafaiCalPort,"font-size:12px;padding:3px;width:60px")."</td>
			</tr>
			<tr>
				<td class=legend style='font-size:12px'>{enable}:</td>
				<td>". Field_checkbox("ZarafaiCalEnable",1,$ZarafaiCalEnable)."</td>
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