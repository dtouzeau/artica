<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	
	
	if(isset($_GET["show"])){user_amavis_front();exit;}
	if(isset($_GET["amavisSpamLover"])){SaveAmavisConfig();exit;}
	if(isset($_GET["GoBackDefaultAmavis"])){GoBackDefaultAmavis();exit;}
	
	
js();



function js(){
	
	$tpl=new templates();
	
	if(!isset($_GET["userid"])){
		if(!isset($_SESSION["uid"])){echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;}
		$uid=$_SESSION["uid"];
	}
	
	if(isset($_GET["userid"])){
		$users=new usersMenus();
		if(!$users->AllowChangeAntiSpamSettings){echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;}
		$uid=$_GET["userid"];
		$adduri="&userid=$uid";
		$addXHR="XHR.appendData('userid','$uid');";
	}
	
	
	$translate_page="amavis.index.php";
	$page=CurrentPageName();

	$title=$uid." ::{spam_rules}";
	$title=$tpl->_ENGINE_parse_body($title,$translate_page);
	
	
	$html="
	
	function LoadUserAmavis(){
		YahooWin2(600,'$page?show=yes$adduri','$title');
	}
	
	
	var x_SaveUserAmavis= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				LoadUserAmavis();	
			}			
			
	
	function SaveUserAmavis(){
		var XHR = new XHRConnection();
		XHR.appendData('amavisSpamLover',document.getElementById('amavisSpamLover').value);
		XHR.appendData('amavisBadHeaderLover',document.getElementById('amavisBadHeaderLover').value);
		XHR.appendData('amavisBypassVirusChecks',document.getElementById('amavisBypassVirusChecks').value);
		XHR.appendData('amavisBypassSpamChecks',document.getElementById('amavisBypassSpamChecks').value);
		XHR.appendData('amavisBypassHeaderChecks',document.getElementById('amavisBypassHeaderChecks').value);
		XHR.appendData('amavisSpamTagLevel',document.getElementById('amavisSpamTagLevel').value);
		XHR.appendData('amavisSpamTag2Level',document.getElementById('amavisSpamTag2Level').value);
		XHR.appendData('amavisSpamKillLevel',document.getElementById('amavisSpamKillLevel').value);
		XHR.appendData('amavisSpamModifiesSubj',document.getElementById('amavisSpamModifiesSubj').value);
		$addXHR
		document.getElementById('user-amavis').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveUserAmavis);	
	
	}
	
	function GoBackDefaultAmavis(){
		var XHR = new XHRConnection();
		XHR.appendData('GoBackDefaultAmavis','$uid');
		document.getElementById('user-amavis').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveUserAmavis);	
	}
	
	LoadUserAmavis();
	
	";

	

	echo $html;
	
}


function user_amavis_front(){
	
$tpl=new templates();	
if(isset($_GET["userid"])){
		$users=new usersMenus();
		if(!$users->AllowChangeAntiSpamSettings){echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;}
		$uid=$_GET["userid"];
		$button_admin="<div style='text-align:center'>
		<input type='button' OnClick=\"javascript:GoBackDefaultAmavis('$uid')\" value='{back_to_defaults}&nbsp;&raquo;'></div>";
		
	}
else{
	$uid=$_SESSION["uid"];
}
		
	

$user=new user($uid);	

$form="
<table style='width:100%'>
<tr>
	<td class=legend>{amavisSpamLover}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisSpamLover',$user->amavisSpamLover,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisBadHeaderLover}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisBadHeaderLover',$user->amavisBadHeaderLover,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisBypassVirusChecks}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisBypassVirusChecks',$user->amavisBypassVirusChecks,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisBypassSpamChecks}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisBypassSpamChecks',$user->amavisBypassSpamChecks,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisBypassHeaderChecks}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisBypassHeaderChecks',$user->amavisBypassHeaderChecks,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisSpamModifiesSubj}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisSpamModifiesSubj',$user->amavisSpamModifiesSubj,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisSpamTagLevel}:</td>
	<td>" . Field_text('amavisSpamTagLevel',$user->amavisSpamTagLevel,'width:90px')."</td>
</tR>
<tr>
	<td class=legend>{amavisSpamTag2Level}:</td>
	<td>" . Field_text('amavisSpamTag2Level',$user->amavisSpamTag2Level,'width:90px')."</td>
</tR>
<tr>
	<td class=legend>{amavisSpamKillLevel}:</td>
	<td>" . Field_text('amavisSpamKillLevel',$user->amavisSpamKillLevel,'width:90px')."</td>
</tR>
<tr>
	<td colspan=2 align='right'><hr><input type='button' OnClick=\"javascript:SaveUserAmavis();\" value='{edit}&nbsp;&raquo;'></td>
</tr>
</table>
";
$form=RoundedLightWhite($form);

$html="
<H1>{spam_rules}</H1>

<table style='width:100%'>
<td valign='top' width=1%>
<img src='img/caterpillarkas.png'>
<p class=caption>{amavis_user_text}</p>
<hr>
$button_admin
</td>
<td><div id='user-amavis'>$form</div>
</td>
</tr>
</table>

";

/*	
amavisSpamLover: FALSE
amavisBadHeaderLover: FALSE
amavisBypassVirusChecks: FALSE
amavisBypassSpamChecks: FALSE

amavisBypassHeaderChecks: FALSE
amavisSpamTagLevel: -999
amavisSpamTag2Level: 5
amavisSpamKillLevel: 5
amavisSpamModifiesSubj: TRUE
*/


echo $tpl->_ENGINE_parse_body($html,'amavis.index.php');
}


function GoBackDefaultAmavis(){
		$users=new usersMenus();
		if(!$users->AllowChangeAntiSpamSettings){echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;}
		$userid=$_GET["GoBackDefaultAmavis"];
		$user=new user($userid);
		$user->DeleteAmavisConfig();
	
}


function SaveAmavisConfig(){
	$tpl=new templates();
if(isset($_GET["userid"])){
		$users=new usersMenus();
		if(!$users->AllowChangeAntiSpamSettings){echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;}
		$uid=$_GET["userid"];
		unset($_GET["userid"]);
	}
else{
	$uid=$_SESSION["uid"];
}	
	writelogs("uid=$uid",__FUNCTION__,__FILE__);
	$user=new user($uid);	
	while (list ($num, $ligne) = each ($_GET)){
		$user->$num=$ligne;
	}
	
	if($user->SaveAmavisConfig()){
		echo $tpl->_ENGINE_parse_body('{success}');
	}

}


?>