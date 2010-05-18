<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.functions.inc");
if(!isset($_SESSION["uid"])){header("Location: session-fermee.php");die();}


if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["DisplayName"])){Save();exit;}


js();

function js(){
	
	$user=new user($_SESSION["uid"]);
	$page=CurrentPageName();
	$prefix=str_replace('.',"_",$page);
	
	
	$html="
	function {$prefix}Load(){
		YahooWin(500,'$page?popup=yes','$user->DisplayName');
	
	}
	
var x_EditProfile= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				document.getElementById('user-form').innerHTML='';
				YahooWinHide();
				ReloadIdentity();
			}	
	
	function EditProfile(){
		var XHR = new XHRConnection();
		XHR.appendData('DisplayName',document.getElementById('DisplayName').value);
		XHR.appendData('sn',document.getElementById('sn').value);
		XHR.appendData('givenName',document.getElementById('givenName').value);
		XHR.appendData('telephoneNumber',document.getElementById('telephoneNumber').value);
		XHR.appendData('mobile',document.getElementById('mobile').value);
		if(document.getElementById('password')){
			XHR.appendData('password',document.getElementById('password').value);
		}
		document.getElementById('user-form').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_EditProfile);	
	}
	
	{$prefix}Load();
	
	";
	echo $html;
}

function Save(){
	$user=new user($_SESSION["uid"]);
	$user->telephoneNumber=$_GET["telephoneNumber"];
	$user->sn=utf8_encode($_GET["sn"]);
	$user->givenName=utf8_encode($_GET["givenName"]);
	$user->DisplayName=utf8_encode($_GET["DisplayName"]);	
	$user->mobile=$_GET["mobile"];
	
	$users=new usersMenus();
	if($users->AllowChangeUserPassword){
		if($_GET["password"]<>null){
			$user->password=$_GET["password"];
		}
	}
	
	$user->SaveUser();
}


function popup(){
	
	$user=new user($_SESSION["uid"]);
	$users=new usersMenus();

	
	$formpassword="
	<tr>
		<td class=legend nowrap>{password}:</td>
		<td>". Field_password("password",$user->password)."</td>
	</tr>
	
	";
	
	if(!$users->AllowChangeUserPassword){
		$formpassword=null;
	}
	
	
	$html="
	
	<div id='user-form'>
	<table class=table_form>
	<tr>
		<td class=legend nowrap>{displayname}:</td>
		<td>". Field_text("DisplayName",$user->DisplayName)."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{sn}:</td>
		<td>". Field_text("sn",$user->sn)."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{givenname}:</td>
		<td>". Field_text("givenName",$user->givenName)."</td>
	</tr>	
	$formpassword		
	</table>
	<br>
<table class=table_form>
	<tr>
		<td class=legend nowrap>{phone}:</td>
		<td>". Field_text("telephoneNumber",$user->telephoneNumber)."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{mobile}:</td>
		<td>". Field_text("mobile",$user->mobile)."</td>
	</tr>		
	</table>	
<div style='text-align:right'>". button("{save}","EditProfile()")."</div>	
</div>	
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

?>