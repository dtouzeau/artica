<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.functions.inc");
if(!isset($_SESSION["uid"])){header("Location: session-fermee.php");die();}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["sasl_username"])){save();exit;}
if(isset($_GET["sasl_username_delete"])){Delete();exit;}
js();

function js(){
	
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{sender_canonical}');
	$page=CurrentPageName();
	
	$html="
	function SenderSetStart(){
		YahooWin(500,'$page?popup=yes','$title');
		}
		
var x_SaveSenderCanonicalNew= function (obj) {
	var results=obj.responseText;
	if (results.length>0){alert(results);}
	SenderSetStart();
	}		

		
	function SaveUserSenderTransport(){
		var XHR = new XHRConnection();
		XHR.appendData('sasl_username',document.getElementById('sasl_username').value);
		XHR.appendData('sasl_password',document.getElementById('sasl_password').value);
		XHR.appendData('relay_address',document.getElementById('relay_address').value);
		XHR.appendData('relay_port',document.getElementById('relay_port').value);
		XHR.appendData('sender_canonical',document.getElementById('sender_canonical').value);
		document.getElementById('sasltransport').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveSenderCanonicalNew);	
	}	

	function DeleteUserSenderSettings(){
		var XHR = new XHRConnection();
		XHR.appendData('sasl_username_delete','yes');
		document.getElementById('sasltransport').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveSenderCanonicalNew);			
	}
		
	SenderSetStart();
	
	
	
	
	";
	
	echo $html;
	
}


function popup(){
	
	$user=new user($_SESSION["uid"]);
	$SenderParams=$user->SenderCanonicalSMTPRelay();
	$host=$SenderParams["HOST"];
	$auth=$SenderParams["AUTH"];

	$edit_b=button("{edit}","SaveUserSenderTransport()");
	$delete_b=button("{edit}","DeleteUserSenderSettings()");
	
	$users=new usersMenus();
	if(!$user->AllowSenderCanonical){
		$edit_b=button_hidden();
		$delete_b=null;
	}
	
	if($host<>null){
		$dom=new DomainsTools();
		$arr=$dom->transport_maps_explode($user->AlternateSmtpRelay);
	}	
	if($arr[2]==null){$arr[2]=25;}	
	
	$server=$arr[1];
	$port=$arr[2];
	
	if(preg_match("#(.+?):(.+)#",$auth,$re)){
		$username=$re[1];
		$password=$re[2];
	}	
	
	$t=ParagrapheTXT("{sender_user_explain}");
	$html="
	
	$t
	<div id='sasltransport'>
	<table class=table_form>
	<tr>
		<td class=legend nowrap>{sender_canonical}:</td>
		<td>". Field_text("sender_canonical",$user->SenderCanonical)."</td>
	</tr>
		<tr>
		<td colspan=2 align=right><br>$edit_b</td>
	</tr>
	</table	>
	
	<H3>{smtp_internet_relay_option}</H3>
	<table class=table_form>
	<tr>
		<td class=legendl nowrap align='left'>{relay_address}:</td>
		<td class=legendl nowrap align='left'>{listen_port}:</td>
	</tr>
	<tr>
		<td>". Field_text("relay_address",$server)."</td>
		<td>". Field_text("relay_port",$port,"width:40px")."</td>
	</tr>
	<tr>
		<td colspan=2>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legendl nowrap align='left'>{username}:</td>
		<td class=legendl nowrap align='left'>{password}:</td>
	</tr>	

	<tr>
		<td>". Field_text("sasl_username",$username)."</td>
		<td>". Field_password("sasl_password",$password,"")."</td>
	</tr>	
	<tr>
		<td><br>$delete_b</td>
		<td><br>$edit_b</td>
	</tr>
	</table>
	</div>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function save(){
	$sender_canonical=$_GET["sender_canonical"];
	$relay_address=$_GET["relay_address"];
	$relay_port=$_GET["relay_port"];
	$sasl_username=$_GET["sasl_username"];
	$sasl_password=$_GET["sasl_password"];
	
	$user=new user($_SESSION["uid"]);
	$user->SenderCanonical=$_GET["sender_canonical"];
	$user->add_Canonical();	
	
	if(($relay_address<>null) && ($relay_port<>null)){
		$domain=new DomainsTools();
		$line=$domain->transport_maps_implode($relay_address,$relay_port,null,"no");
		$user->SenderCanoniCalSMTPRelayAdd($line,$sasl_username,$sasl_password,$relay_address);
	}
}
function Delete(){
	$user=new user($_SESSION["uid"]);
	$user->DeleteCanonical();
	$user->del_transport();
}

