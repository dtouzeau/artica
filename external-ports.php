<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.httpd.inc');
	include_once('ressources/class.roundcube.inc');
	include_once('ressources/class.main_cf.inc');	

	$usersmenus=new usersMenus();
	if(!$usersmenus->AsArticaAdministrator){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");
		die();		
	}	
	
		if(isset($_GET["index"])){external_ports_index();exit;}
		if(isset($_GET["artica_port"])){ARTICA_PORT_SAVE();exit;}
		if(isset($_GET["APP_GROUPWARE_APACHE"])){APP_GROUPWARE_APACHE();exit;}
		if(isset($_GET["APP_ROUNDCUBE"])){APP_ROUNDCUBE();exit;}
		if(isset($_GET["APP_OBM2"])){APP_OBM2();exit;}
		if(isset($_GET["APP_APACHE"])){APP_APACHE();exit;}
js();
	
	
function js(){

$tpl=new templates();
$page=CurrentPageName();

$title=$tpl->_ENGINE_parse_body('{EXTERNAL_PORTS}');

$html="

	function ExternalPortsPage(){
		YahooWin5('400','$page?index=yes','$title');
	
	}
	
	var x_ChangeExternalPorts= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);}
		ExternalPortsPage();
	}		
	
function ChangeArticaPort(){
		var artica_port=document.getElementById('artica_port').value;
		document.getElementById('externalportsdiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		var XHR = new XHRConnection();
		XHR.appendData('artica_port',artica_port);
		XHR.sendAndLoad('$page', 'GET',x_ChangeExternalPorts);
		 		
	}
function ChangeApacheGroupwarePort(){
		var APP_GROUPWARE_APACHE=document.getElementById('APP_GROUPWARE_APACHE').value;
		document.getElementById('externalportsdiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		var XHR = new XHRConnection();
		XHR.appendData('APP_GROUPWARE_APACHE',APP_GROUPWARE_APACHE);
		XHR.sendAndLoad('$page', 'GET',x_ChangeExternalPorts);
		 		
	}
function ChangeRoundCubePort(){
		var APP_ROUNDCUBE=document.getElementById('APP_ROUNDCUBE').value;
		document.getElementById('externalportsdiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		var XHR = new XHRConnection();
		XHR.appendData('APP_ROUNDCUBE',APP_ROUNDCUBE);
		XHR.sendAndLoad('$page', 'GET',x_ChangeExternalPorts);
		 		
	}
function ChangeOBM2Port(){
		var APP_OBM2=document.getElementById('APP_OBM2').value;
		document.getElementById('externalportsdiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		var XHR = new XHRConnection();
		XHR.appendData('APP_OBM2',APP_OBM2);
		XHR.sendAndLoad('$page', 'GET',x_ChangeExternalPorts);
		 		
	}	

function ChangeApachePort(){
		var APP_APACHE=document.getElementById('APP_APACHE').value;
		document.getElementById('externalportsdiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		var XHR = new XHRConnection();
		XHR.appendData('APP_APACHE',APP_APACHE);
		XHR.sendAndLoad('$page', 'GET',x_ChangeExternalPorts);

}
	
	
	
	
	
	
ExternalPortsPage();
";
	echo $html;
	
}	

function external_ports_index(){

	$sock=new sockets();
	
	$users=new usersMenus();
	
	if($users->APACHE_INSTALLED){
		$port=$sock->GET_INFO("ApacheGroupWarePort");
		
		
		$APACHE_GROUPWARE="
		<tr " . CellRollOver().">
		<td valign='top' class=legend nowrap>{APP_APACHE}:</td>
		<td valign='top' nowrap>(HTTP)</td>
		<td valign='top' width=1%>" . Field_text('APP_APACHE',$users->APACHE_PORT,'width:60px')."</td>
		<td valign='top' width=1%><input type='button' OnClick=\"javascript:ChangeApachePort()\" value='{edit}&nbsp;&raquo;' style='margin:0px;padding:0px'></td>
		</tr>		
		<tr " . CellRollOver().">
		<td valign='top' class=legend nowrap>{APP_GROUPWARE_APACHE}:</td>
		<td valign='top' nowrap>(HTTP)</td>
		<td valign='top' width=1%>" . Field_text('APP_GROUPWARE_APACHE',$port,'width:60px')."</td>
		<td valign='top' width=1%><input type='button' OnClick=\"javascript:ChangeApacheGroupwarePort()\" value='{edit}&nbsp;&raquo;' style='margin:0px;padding:0px'></td>
		</tr>";
		
	}else{
		$APACHE_GROUPWARE="
		<tr " . CellRollOver().">
		<td valign='top' class=legend nowrap>{APP_GROUPWARE_APACHE}:</td>
		<td valign='top' nowrap>(HTTP)</td>
		<td valign='top' width=1% align='center'>--</td>
		<td valign='top' width=1% align='center'>--</td>
		</tr>";
	}
	
	
	
$APP_ROUNDCUBE="
		<tr " . CellRollOver().">
		<td valign='top' class=legend nowrap>{APP_ROUNDCUBE}:</td>
		<td valign='top' nowrap>(HTTPS)</td>
		<td valign='top' width=1% align='center'>--</td>
		<td valign='top' width=1% align='center'>--</td>
		</tr>";	
	
	if($users->roundcube_installed){
		$round=new roundcube();
		if($round->RoundCubeHTTPEngineEnabled==1){
		$port=$round->https_port;
		$APP_ROUNDCUBE="
		<tr " . CellRollOver().">
		<td valign='top' class=legend nowrap>{APP_ROUNDCUBE}:</td>
		<td valign='top' nowrap>(HTTPS)</td>
		<td valign='top' width=1%>" . Field_text('APP_ROUNDCUBE',$port,'width:60px')."</td>
		<td valign='top' width=1%><input type='button' OnClick=\"javascript:ChangeRoundCubePort()\" value='{edit}&nbsp;&raquo;' style='margin:0px;padding:0px'></td>
		</tr>";
		}}
	
$APP_OBM2="
		<tr " . CellRollOver().">
		<td valign='top' class=legend nowrap>{APP_OBM2}:</td>
		<td valign='top' nowrap>(HTTP)</td>
		<td valign='top' width=1% align='center'>--</td>
		<td valign='top' width=1% align='center'>--</td>
		</tr>";

if($users->OBM2_INSTALLED){
		$Obm2ListenPort=trim($sock->GET_INFO('Obm2ListenPort'));
		$APP_OBM2="
		<tr " . CellRollOver().">
		<td valign='top' class=legend nowrap>{APP_OBM2}:</td>
		<td valign='top' nowrap>(HTTP)</td>
		<td valign='top' width=1%>" . Field_text('APP_OBM2',$Obm2ListenPort,'width:60px')."</td>
		<td valign='top' width=1%><input type='button' OnClick=\"javascript:ChangeOBM2Port()\" value='{edit}&nbsp;&raquo;' style='margin:0px;padding:0px'></td>
		</tr>";
		}
		
		
if($users->POSTFIX_INSTALLED){
		
		$APP_POSTFIX_SMTP="
		<tr " . CellRollOver().">
		<td valign='top' class=legend nowrap>{APP_POSTFIX}:</td>
		<td valign='top' nowrap>(SMTP)</td>
		<td valign='top' width=1%><code style='font-size:11px;font-weight:bold'>25</code></td>
		<td valign='top' width=1% align=center>--</td>
		</tr>";
		
		$master=new master_cf(1);
		if($master->PostfixEnableMasterCfSSL==1){
			$APP_POSTFIX_SMTPS="
		<tr " . CellRollOver().">
		<td valign='top' class=legend nowrap>{APP_POSTFIX}:</td>
		<td valign='top' nowrap>(SMTPs)</td>
		<td valign='top' width=1%><code style='font-size:11px;font-weight:bold'>465</code></td>
		<td valign='top' width=1% align=center>--</td>
		</tr>";
		}
		
		$sock=new sockets();
		$PostfixEnableSubmission=$sock->GET_INFO("PostfixEnableSubmission");		
		
		if($PostfixEnableSubmission==1){
			$APP_POSTFIX_SUBMISSION="
		<tr " . CellRollOver().">
		<td valign='top' class=legend nowrap>{APP_POSTFIX}:</td>
		<td valign='top' nowrap>(subm.)</td>
		<td valign='top' width=1%><code style='font-size:11px;font-weight:bold'>587</code></td>
		<td valign='top' width=1% align=center>--</td>
		</tr>";
		}		
		
		}		
		
		
	
	
	$httpd=new httpd();
	$artica_port=$httpd->https_port;
	
	
	$html="<h1>{EXTERNAL_PORTS}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><img src='img/64-bind.png'</td>
		<td valign='top'><p class=caption>{EXTERNAL_PORTS_TEXT}</p>
	</tr>
	</table>
	
	
	<div id='externalportsdiv'>
	<table style='width:100%' class=table_form>
	
	<tr " . CellRollOver().">
		<td valign='top' class=legend nowrap>{APP_ARTICA}:</td>
		<td valign='top' nowrap>(HTTPS)</td>
		<td valign='top' width=1%>" . Field_text('artica_port',$artica_port,'width:60px')."</td>
		<td valign='top' width=1%><input type='button' OnClick=\"javascript:ChangeArticaPort()\" value='{edit}&nbsp;&raquo;' style='margin:0px;padding:0px'></td>
	</tr>
	$APACHE_GROUPWARE
	$APP_ROUNDCUBE
	$APP_OBM2
	$APP_POSTFIX_SMTP
	$APP_POSTFIX_SMTPS
	$APP_POSTFIX_SUBMISSION
		
	
	
	</table>
	</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function ARTICA_PORT_SAVE(){
	$httpd=new httpd();
	$httpd->https_port=$_GET["artica_port"];
	$httpd->SaveToServer();
	
}

function APP_GROUPWARE_APACHE(){
	$sock=new sockets();
	$sock->SET_INFO("ApacheGroupWarePort",$_GET["APP_GROUPWARE_APACHE"]);
	$sock->getfile('RestartApacheGroupware');	
	}
function APP_ROUNDCUBE(){
$round=new roundcube();
$round->https_port=$_GET["APP_ROUNDCUBE"];
$round->Save();
	
}

function APP_OBM2(){
	$sock=new sockets();
	$sock->SET_INFO("Obm2ListenPort",$_GET["APP_OBM2"]);
	$sock->getfile("Obm2restart");
	}
	
function APP_APACHE(){
	$sock=new sockets();
	$sock->getfile("ApacheChangePortStandard:".$_GET["APP_APACHE"]);
	$sock->DeleteCache();		
}


?>