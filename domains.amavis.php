<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	
	
	if(isset($_GET["show"])){domain_amavis_front();exit;}
	if(isset($_GET["amavisSpamLover"])){SaveAmavisConfig();exit;}
	if(isset($_GET["GoBackDefaultAmavis"])){GoBackDefaultAmavis();exit;}
	
	
js();



function js(){
	
	$tpl=new templates();
	
	$domain=$_GET["domain"];
	
	
	$translate_page="amavis.index.php";
	$page=CurrentPageName();

	$title=$domain." ::{spam_rules}";
	$title=$tpl->_ENGINE_parse_body($title,$translate_page);
	
	
	$html="
	
	function LoadDomainAmavis(){
		YahooWin2(750,'$page?show=yes&domain=$domain','$title');
	}
	
	
	var x_SaveDomainAmavis= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				LoadDomainAmavis();	
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
		
		XHR.appendData('amavisSpamDsnCutoffLevel',document.getElementById('amavisSpamDsnCutoffLevel').value);
		XHR.appendData('amavisSpamQuarantineCutoffLevel',document.getElementById('amavisSpamQuarantineCutoffLevel').value);
		XHR.appendData('amavisSpamSubjectTag',document.getElementById('amavisSpamSubjectTag').value);
		XHR.appendData('amavisSpamSubjectTag2',document.getElementById('amavisSpamSubjectTag2').value);
		
		
		XHR.appendData('domain','$domain');
		document.getElementById('domain-amavis').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveDomainAmavis);	
	
	}
	
	function GoBackDefaultAmavis(){
		var XHR = new XHRConnection();
		XHR.appendData('GoBackDefaultAmavis','$domain');
		document.getElementById('domain-amavis').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveDomainAmavis);	
	}
	
	LoadDomainAmavis();
	
	";

	

	echo $html;
	
}


function domain_amavis_front(){
	
$tpl=new templates();	
$users=new usersMenus();
if(!$users->AllowChangeAntiSpamSettings){echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;}

$domain=$_GET["domain"];
$dom=new DomainsTools();
$dom->LoadAmavisDomain($domain);
$button_admin="<div style='text-align:center'>
		<input type='button' OnClick=\"javascript:GoBackDefaultAmavis()\" value='{back_to_defaults}&nbsp;&raquo;'></div>";

$form1="
<table style='width:100%'>
<tr>
	<td class=legend>{amavisSpamLover}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisSpamLover',$dom->amavisSpamLover,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisBadHeaderLover}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisBadHeaderLover',$dom->amavisBadHeaderLover,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisBypassVirusChecks}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisBypassVirusChecks',$dom->amavisBypassVirusChecks,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisBypassSpamChecks}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisBypassSpamChecks',$dom->amavisBypassSpamChecks,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisBypassHeaderChecks}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisBypassHeaderChecks',$dom->amavisBypassHeaderChecks,'{enable_disable}')."</td>
</tR>
<tr>
	<td colspan=2 align='right'><hr><input type='button' OnClick=\"javascript:SaveUserAmavis();\" value='{edit}&nbsp;&raquo;'></td>
</tr>
</table>";


$sa_quarantine_cutoff_level=$tpl->_ENGINE_parse_body('{sa_quarantine_cutoff_level}','amavis.index.php,spamassassin.index.php');
if(strlen($sa_quarantine_cutoff_level)>50){
	$sa_quarantine_cutoff_level=texttooltip(substr($sa_quarantine_cutoff_level,0,47).'...',$sa_quarantine_cutoff_level);
}

$sa_dsn_cutoff_level=$tpl->_ENGINE_parse_body('{sa_dsn_cutoff_level}','amavis.index.php,spamassassin.index.php');
if(strlen($sa_dsn_cutoff_level)>50){
	$sa_dsn_cutoff_level=texttooltip(substr($sa_dsn_cutoff_level,0,47).'...',$sa_dsn_cutoff_level);
}


$form2="

<table style='width:100%'>	
		<tr>
			<td class=legend nowrap>{sa_tag2_level_deflt}:</td>
			<td width=1%>". Field_text('amavisSpamTag2Level',$dom->amavisSpamTag2Level,'width:90px')."</td>
			<td>&nbsp;</td>
							
		</tr>
		<tr>
			<td class=legend nowrap>{sa_kill_level_deflt}:</td>
			<td width=1%>". Field_text('amavisSpamKillLevel',$dom->amavisSpamKillLevel,'width:90px')."</td>
			<td>&nbsp;</td>
		</tr>	
		<tr>
		<td colspan=3 align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SaveUserAmavis();\">
		</tr>		
		<tr><td colspan=3><hr></td></tR>
		<tr>
			<td class=legend nowrap>$sa_dsn_cutoff_level:</td>
			<td width=1%>". Field_text('amavisSpamDsnCutoffLevel',$dom->amavisSpamDsnCutoffLevel,'width:90px')."</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class=legend nowrap>$sa_quarantine_cutoff_level:</td>
			<td width=1%>". Field_text('amavisSpamQuarantineCutoffLevel',$dom->amavisSpamQuarantineCutoffLevel,'width:90px')."</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td colspan=2 align='right'><hr><input type='button' OnClick=\"javascript:SaveUserAmavis();\" value='{edit}&nbsp;&raquo;'></td>
		</tr>	
	</table>

";


$spam_subject_tag2_maps=$tpl->_ENGINE_parse_body('{spam_subject_tag2_maps}','amavis.index.php,spamassassin.index.php');



$form3="
<table style='width:100%'>
<tr>
	
	
	<td class=legend nowrap>{amavisSpamModifiesSubj}:</td>
	<td width=1%>" . Field_TRUEFALSE_checkbox_img('amavisSpamModifiesSubj',$dom->amavisSpamModifiesSubj,'{enable_disable}')."</td>	
	
</tr>	
</table>
<table style='width:100%'>
		<tr>
			<td class=legend nowrap>{spam_subject_tag_maps}:</td>
			<td width=1%>". Field_text('amavisSpamSubjectTag',$dom->amavisSpamSubjectTag,'width:190px')."</td>
			<td class=legend nowrap>{score}:</td>
			<td>" . Field_text("amavisSpamTagLevel",$dom->amavisSpamTagLevel,'width:33px')."</td>
		</tr>
		<tr>
			<td class=legend nowrap>$spam_subject_tag2_maps:</td>
			<td width=1%>". Field_text('amavisSpamSubjectTag2',$dom->amavisSpamSubjectTag2,'width:190px')."</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>		
	<tr>
		<td colspan=5 align='right'><hr><input type='button' OnClick=\"javascript:SaveUserAmavis();\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
</table>

";
$form1=RoundedLightWhite($form1);
$form2=RoundedLightWhite($form2);
$form3=RoundedLightWhite($form3);

$html="
<H1>{spam_rules}</H1>

<table style='width:100%'>
<td valign='top' width=1%>
<img src='img/caterpillarkas.png'>
<p class=caption>{amavis_domain_text}</p>
<hr>
$button_admin
</td>
<td><div id='domain-amavis'>$form1<br>$form2<br>$form3</div></td>
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


echo $tpl->_ENGINE_parse_body($html,'amavis.index.php,spamassassin.index.php');
}


function GoBackDefaultAmavis(){
		$users=new usersMenus();
		if(!$users->AllowChangeAntiSpamSettings){echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;}
		$domain=$_GET["GoBackDefaultAmavis"];
		$dom=new DomainsTools();
		$dom->LoadAmavisDomain($domain);
		$dom->SetDefaultAmavisConfig();
	
}


function SaveAmavisConfig(){
	$tpl=new templates();
	$domain=$_GET["domain"];
	$users=new usersMenus();
	if(!$users->AllowChangeAntiSpamSettings){echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;}
		
	
	writelogs("domain=$domain",__FUNCTION__,__FILE__);
	$dom=new DomainsTools();
	$dom->LoadAmavisDomain($domain);	
	while (list ($num, $ligne) = each ($_GET)){
		$dom->$num=$ligne;
	}
	
	if($dom->SaveAmavisConfig()){
		echo $tpl->_ENGINE_parse_body('{success}');
	}

}


?>