<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.stunnel4.inc');
	include_once('ressources/class.main_cf.inc');
	
	
	
	
	
$usersmenus=new usersMenus();
if(!$usersmenus->AsPostfixAdministrator){
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
	die();
}


if(isset($_GET["js"])){js();exit;}
if(isset($_GET["popup"])){popup();exit;}
if(isset($_POST["save_step"])){echo save_step();exit;}
if(isset($_GET["stunnel-status"])){echo main_stunnel_status();exit;}
if(isset($_GET["ApplyConfig"])){echo ApplyConfig();exit;}
if(isset($_GET["FillSenderForm"])){echo FillSenderForm();exit;}
if(isset($_POST["smtp_sender_dependent_authentication_email"])){smtp_sender_dependent_authentication_submit();exit();}

page();

function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_STUNNEL}');
	$page=CurrentPageName();	
	$include=file_get_contents("js/postfix-tls.js");
	$idmd='Stunnel4_';
	
	$html="var {$idmd}timerID  = null;
var {$idmd}timerID1  = null;
var {$idmd}tant=0;
var {$idmd}reste=0;

	function {$idmd}demarre(){
		if(!YahooWin2Open()){return false;}
		{$idmd}tant = {$idmd}tant+1;
		{$idmd}reste=10-{$idmd}tant;
		if ({$idmd}tant < 10 ) {                           
			{$idmd}timerID = setTimeout(\"{$idmd}demarre()\",3000);
	      } else {
			{$idmd}tant = 0;
			{$idmd}ChargeLogs();
			{$idmd}demarre();                                
	   }
	}
	
		function {$idmd}start(){
			YahooWin2(750,'$page?popup=yes','$title');
			setTimeout(\"{$idmd}ChargeLogs()\",1000);
			setTimeout(\"{$idmd}demarre()\",1000);
		
		}
		
		
function {$idmd}ChargeLogs(){
	LoadAjax('servinfos_stunnel','$page?stunnel-status=yes&hostname={$_GET["hostname"]}');
	}	
		
	{$idmd}start();
	$include
	";
	
	
	echo $html;
	
	
}


function popup(){
	
$intro="<table style='width:100%' align=center>
<tr>
<td valign='top'>
	<img src='img/postfix-relayhost-ssl-bg.png' style='padding:4px;border:1px dotted #CCCCCC;margin:3px'>
</td>
<td valign='top' width='99%'>
<div id='servinfos_stunnel'></div>
</td>
</tr>
<tr><td colspan=2><p class=caption>{smtps_relayhost_text}</p></td></tr>
</table>";	


$intro=RoundedLightWhite($intro);
$subpage=sub_page();

$html="$intro<br>$subpage";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);

}

	
function page(){
$page=CurrentPageName();	
$html="
<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function demarre(){
   tant = tant+1;
   reste=10-tant;
	if (tant < 10 ) {                           
      timerID = setTimeout(\"demarre()\",3000);
      } else {
               tant = 0;
               //document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
               ChargeLogs();
               demarre();                                //la boucle demarre !
   }
}


function ChargeLogs(){
	LoadAjax('servinfos','$page?stunnel-status=yes&hostname={$_GET["hostname"]}');
	}
</script>	


<table style='width:100%' align=center>
<tr>
<td valign='top'>
	<img src='img/postfix-relayhost-ssl-bg.png' style='padding:4px;border:1px dotted #CCCCCC;margin:3px'>
	<p class=caption>{smtps_relayhost_text}</p>
</td>
<td valign='top' width='99%'>
<div id='servinfos'></div>
</td>
</tr>
<tr>
	<td colspan=2>
	<div id='main_config'>
	" . sub_page()."
		</div>
	</td>
</tr>
		
</table>
<script>demarre();ChargeLogs()</script>

";
$cfg["JS"][]="js/postfix-tls.js";
//<script>LoadAjax('main_config','$page?main=transport_settings&hostname=$hostname')</script>
$tpl=new template_users('{smtps_relayhost}',$html,0,0,0,0,$cfg);
echo $tpl->web_page;
	
}


function sub_page(){
$main=new main_cf();

$stunnel=new stunnel4();
$relay_host=$stunnel->main_array["postfix_relayhost"]["connect"];
$localport=$stunnel->main_array["postfix_relayhost"]["accept"];
$apply=applysettingsGeneral('apply','relayssl_start()','apply_text',true);
$main=new main_cf();

$sock=new sockets();
$sTunnel4enabled=$sock->GET_INFO('sTunnel4enabled');

preg_match('#(.+?):([0-9]+)#',$relay_host,$h);


//$relayhost=$main->main_array["relayhost"];

$sasl=new smtp_sasl_password_maps();
preg_match('#(.+?):(.+)#',$sasl->smtp_sasl_password_hash[$h[1]],$ath);

if($localport==null){
	$sock=new sockets();
	$localport=$sock->RandomPort();
	}
if($h[2]==null){$h[2]=465;}

$form="
<table style='width:100%'>
				<tr>
					<td align='right' nowrap style='font-size:14px'><strong>{yserver}:&nbsp;</strong></td>
					<td><input type='text' id='server' value='{$h[1]}' style='font-size:14px'></td>
				</tr>
				<tr>
					<td align='right' nowrap style='font-size:14px'><strong>{yport}:&nbsp;</strong></td>
					<td><input type='text' id='port' value='{$h[2]}' style='font-size:14px;width:30%'></td>
				</tr>				
			</table>

";


$artica=new artica_general();
$enable=Paragraphe_switch_img('{enable_stunnel}',"{enable_stunnel_text}",'enable_stunnel',$sTunnel4enabled);

$form1="
<table style='width:100%'>
				<tr>
					<td align='right' nowrap style='font-size:14px'><strong>{stunnelport}:&nbsp;</strong></td>
					<td><input type='text' id='localport' value='$localport' style='font-size:14px;width:30%'></td>
				</tr>			
			</table>

";


$form2="
<table style='width:100%'>
				<tr>
					<td align='right' nowrap style='font-size:14px'><strong><u>".texttooltip("{smtp_sender_dependent_authentication}","{smtp_sender_dependent_authentication_tooltip}","smtp_sender_dependent_authentication()")."</u>:&nbsp;</strong></td>
					<td>" . Field_yesno_checkbox('smtp_sender_dependent_authentication',$main->main_array["smtp_sender_dependent_authentication"])."</td>
				</tr>
			</table>
			<div id='peruser'>
			<table style='width:100%'>
				<tr>
				<tr>
					<td align='left' nowrap style='font-size:16px' colspan=2><strong>{single_auth}:</strong>
				</tr>
				
					<td align='right' nowrap style='font-size:14px'><strong>{username}:&nbsp;</strong></td>
					<td><input type='text' id='username' value='{$ath[1]}' style='font-size:14px'></td>
				</tr>
				<tr>
					<td align='right' nowrap style='font-size:14px'><strong>{password}:&nbsp;</strong></td>
					<td><input type='text' id='password' value='{$ath[2]}' style='font-size:14px;'></td>
				</tr>				
			</table>
		</div>

";

$form="<br>".RoundedLightWhite($form);
$form1="<br>".RoundedLightWhite($form1);
$form2="<br>".RoundedLightWhite($form2);
	return "
	<table style='width:100%'>
	<tr>
		<td valign='top'>
				<table style='width:100%'>
				<tr " . CellRollOver("stunnelSwitchdiv('stunnel_relayhost')").">
				<td valign='top' width=1%><img src='img/chiffre1_32.png'></td>
				<td valign='top' width=99%><span style='font-size:13px;font-weight:bold'>{relayhost}</span>
				</tr>
				<tr>
					<td colspan=2>
						<div id='stunnel_relayhost'>				
						<p class=caption>{relayhost_text}</p>
						$form
						</div>
					</td>
				</tr>
				<tr><td colspan=2><hr></tr>
				<tr " . CellRollOver("stunnelSwitchdiv('stunnel_relayport')").">
				<td valign='top' width=1%><img src='img/chiffre2_32.png'></td>
				<td valign='top' width=99%><span style='font-size:13px;font-weight:bold'>{stunnelport}</span></td>
				</tr>
				<tr>
				<td colspan=2>
						<div id='stunnel_relayport' style='width:0px;height:0px;visibility:hidden'>
						<p class=caption>{stunnelport_text}</p>
						$form1
						</div>
					</td>
				</tr>	
				<tr><td colspan=2><hr></tr>
				<tr " . CellRollOver("stunnelSwitchdiv('stunnel_auth')").">
				<td valign='top' width=1%><img src='img/chiffre3_32.png'></td>
				<td valign='top' width=99%><span style='font-size:13px;font-weight:bold'>{authentication}</span></td>
				</tr>
				<tr>
				<td colspan=2>
						<div id='stunnel_auth' style='width:0px;height:0px;visibility:hidden'>
						<p class=caption>{authentication_text}</p>
						$form2
						</div>
					</td>
				</tr>		
				</table>
		</td>
		<td valign='top'>$enable<br>$apply<br></td>
		
	</tr>
	</table>
	
	
	";
	
	
	
}



function main_stunnel_status(){
	$users=new usersMenus();
	$tpl=new templates();
	if(!$users->stunnel4_installed){
		return $tpl->_ENGINE_parse_body(RoundedLightYellow('{stunnel_not_installed}'));

	}
		
	$ini=new Bs_IniHandler();
	$sock=new sockets();	
	$ini->loadString($sock->getfile('stunnel4status',$_GET["hostname"]));	
	$status=DAEMON_STATUS_ROUND("STUNNEL",$ini);
	return $tpl->_ENGINE_parse_body($status);
	
	
}

function ApplyConfig(){
	
	$sock=new sockets();
	$sock->SET_INFO('sTunnel4enabled',$_GET["enable"]);
	
	$html="
	<div id='stars' style='float:right'></div>
	<H5>apply</H5>
	<div id='content_postfix' style='width:100%;height:300px;overflow:auto'></div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function save_step(){
	$tpl=new templates();
	
	$artica=new artica_general();
	if($artica->sTunnel4enabled==0){
		if($_POST["save_step"]<11){
		echo $tpl->_ENGINE_parse_body(save_step_infos(true,'{stunnel4_is_disabled} ('.$_POST["save_step"].')'));exit;break;
		}
	}
	
	
	switch ($_POST["save_step"]) {
		case 0:echo $tpl->_ENGINE_parse_body(save_step_infos(true,'{scan_form}'));exit;break;
		case 1:echo save_step_form();exit;break;
		case 2:echo $tpl->_ENGINE_parse_body(save_step_infos(true,'{step_stunnel}'));exit;break;
		case 3:echo save_step_stunnel();exit;break;
		case 4:echo $tpl->_ENGINE_parse_body(save_step_infos(true,'{step_sasl}'));exit;break;
		case 5:echo save_step_sasl();exit;break;
		case 6:echo $tpl->_ENGINE_parse_body(save_step_infos(true,'{step_sasl_enable}'));exit;break;
		case 7:echo save_step_sasl_enable();exit;break;
		case 8:echo $tpl->_ENGINE_parse_body(save_step_infos(true,'{step_save_stunnel}'));exit;break;
		case 9:echo save_step_stunnel_server();exit;break;
		case 10:echo $tpl->_ENGINE_parse_body(save_step_infos(true,'{step_save_postfix}'));exit;break;
		case 11:echo save_step_postfix_server();exit;break;
		case 12:echo save_step_stunnel();echo $tpl->_ENGINE_parse_body(save_step_infos(true,'{step_stunnel}'));exit;break;
		default:
			break;
	}
	
	
}

function save_step_sasl_enable(){
	$main=new main_cf();
	$main->smtp_sasl_password_maps_enable_2();
	$main->main_array["relayhost"]="localhost:{$_POST["localport"]}";
	$main->save_conf();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(save_step_infos(true,'{step_sasl_enabled}'));
	
}

function save_step_sasl(){
		$tpl=new templates();
		$continue=true;
		$main=new main_cf();
		$main->main_array["smtp_sender_dependent_authentication"]=$_POST["smtp_sender_dependent_authentication"];
		$main->save_conf();	
		
		if($_POST["username"]==null){$continue=false;}
		if($_POST["password"]==null){$continue=false;}
		
		if($continue){
			$saslpswd=new smtp_sasl_password_maps();
		
	if(!$saslpswd->add("localhost",$_POST["username"],$_POST["password"])){
		return $tpl->_ENGINE_parse_body(save_step_infos(false,"{err_sasl_saveldap}<br>$saslpswd->ldap_infos"));	
	}else{
		return $tpl->_ENGINE_parse_body(save_step_infos(true,'{ok_sasl_saveldap}'));
	}}
	return $tpl->_ENGINE_parse_body(save_step_infos(true,'{ok_sasl_saveldap}'));
	
}

function save_step_form(){
	$res=true;
	$tpl=new templates();
	if($_POST["server"]==null){$res=false;}
	if($_POST["port"]==null){$res=false;}	
	if($_POST["localport"]==null){$res=false;}	
			
	
	if(!$res){return $tpl->_ENGINE_parse_body(save_step_infos(false,'{missing_in_form}'));}
	
	
}

function save_step_stunnel(){
	$user=new usersMenus();
	$tpl=new templates();
	if(!$user->stunnel4_installed){
		return $tpl->_ENGINE_parse_body(save_step_infos(false,'{err_stunnel_inst}'));
	}

	$stunnel=new stunnel4();
	$stunnel->main_array["postfix_relayhost"]["connect"]="{$_POST["server"]}:{$_POST["port"]}";
	$stunnel->main_array["postfix_relayhost"]["accept"]="{$_POST["localport"]}";
	if(!$stunnel->SaveConf()){
		return $tpl->_ENGINE_parse_body(save_step_infos(false,'{err_stunnel_saveldap}'));
	}
	return $tpl->_ENGINE_parse_body(save_step_infos(true,'{step_stunnel_ok}'));
}


function save_step_infos($success=true,$text){
	if($success){$img="icon_ok.gif";}else{
		$start="<err>";
		$end="</err>";
		$img='icon_err.gif';}
	$text=str_replace("\n\n","\n",$text);
	$text=htmlentities($text);
	$text=nl2br($text);
	return "$start<table style='width:350px'><tr><td valign='top' width=1%><img src='img/$img'></td><td valign='top'><strong>$text</strong></td></tr></table>$end";
	}
function save_step_stunnel_server(){
	$stunnel=new stunnel4();
	$datas=$stunnel->SaveToserver();
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body(save_step_infos(true,$datas));
}
function save_step_postfix_server(){
	$main=new main_cf();
	$tpl=new templates();
	$datas=$main->save_conf_to_server();
	return $tpl->_ENGINE_parse_body(save_step_infos(true,$datas));
	
}

function FillSenderForm(){
	$html="<H5>{sender_authentication_maps}</H5>
	<strong>{sender_authentication_maps_text}</strong>
	<br><br>
	<table style='width:100%'>
		</tr>
			<td align='right' nowrap style='font-size:14px'><strong>{sender_email}:&nbsp;</strong></td>
			<td><input type='text' id='sender_email' value='' style='font-size:14px'></td>
		</tr>
		</tr>
			<td align='right' nowrap style='font-size:14px'><strong>{username}:&nbsp;</strong></td>
			<td><input type='text' id='smtp_sender_dependent_authentication_username' value='' style='font-size:14px'></td>
		</tr>		
		</tr>
			<td align='right' nowrap style='font-size:14px'><strong>{password}:&nbsp;</strong></td>
			<td><input type='text' id='smtp_sender_dependent_authentication_password' value='' style='font-size:14px'></td>
		</tr>		
		<tr>
			<td align='right' nowrap  colspan=2><input type='button' OnClick=\"javascript:smtp_sender_dependent_authentication_submit();\" value='&laquo;&nbsp;&nbsp;&nbsp;{add}&nbsp;&nbsp;&nbsp;&raquo;'></td>
		</tr>
	</table>
	<div id='table'></div>
	";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
	
}

function FillSenderForm_table(){
	
$sasl=new smtp_sasl_password_maps();
	
$html="<table style='width:100%'>";

while (list ($num, $val) = each ($sasl->smtp_sasl_password_hash) ){
	preg_match('#(.+?):(.+)#',$val,$ath);
	$html=$html . 
	
	"<tr>
		
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$num</strong></td>
		<td><strong>{$ath[1]}</td>
	</tr>
		
		";
	
}
$html=$html . "</table>";
return RoundedLightGreen($html);
}
function smtp_sender_dependent_authentication_submit(){
	$sasl=new smtp_sasl_password_maps();
	$tpl=new templates();
	if($sasl->add($_POST["smtp_sender_dependent_authentication_email"],$_POST["smtp_sender_dependent_authentication_username"],$_POST["smtp_sender_dependent_authentication_password"])){
		echo $tpl->_ENGINE_parse_body('{success}');
	}else{
		echo $sasl->ldap_infos;
	}
	
}

	
?>	

