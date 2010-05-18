<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	
	if(isset($_GET["index"])){INDEX_CREATE();exit;}
	js();
	
function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{add_user}');
	$ou=$_GET["ou"];
	$ou_encoded=base64_encode($ou);
	$page=CurrentPageName();
	
	$js_add=file_get_contents('js/edit.user.js');
	
	if($ou==null){
		$tpl=new templates();
		$error=$tpl->_ENGINE_parse_body('{error_please_select_an_organization}');
		die("alert('$error')");
	}
	
	$html="
		$js_add
		function loadAdduser(){
			YahooUser(650,'$page?index=yes&ou=$ou_encoded&gpid={$_GET["gpid"]}','$title');
		
		}
		
		loadAdduser();
		
		
	";
	echo $html;
	
}
	

function INDEX_CREATE(){
	$ldap=new clladp();
	if($_GET["ou"]==null){die();}
	$_GET["ou"]=base64_decode($_GET["ou"]);
	$hash=$ldap->hash_groups($_GET["ou"],1);

	
	$domains=$ldap->hash_get_domains_ou($_GET["ou"]);
	
	if(count($domains)==0){
		$users=new usersMenus();
		if($users->POSTFIX_INSTALLED){
			$field_domains=Field_text('user_domain',"{$_GET["ou"]}.com","width:85px");
		}else{
			if(!preg_match("#(.+?)\.(.+)#",$_GET["ou"])){$dom="{$_GET["ou"]}.com";}else{$dom="{$_GET["ou"]}";}
			$field_domains="<code><strong>$dom</strong></code>".Field_hidden('user_domain',"$dom","width:120px");
			
		}
		
		
		
	}else{
		$field_domains=Field_array_Hash($domains,'user_domain');
	}
	
	
	$hash[null]="{select}";
	$groups=Field_array_Hash($hash,'group_id',$_GET["gpid"]);
	
	
	
	$title="{$_GET["ou"]}:{create_user}";
	
	$step1="<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src='img/chiffre1.png'></td>
	<td valign='top'>
	<H3>{name_the_new_account_title}</H3><br>
	<strong>{name_the_new_account_field}:</strong>
	<br>" . Field_text('new_userid',null,null,null,"UserAutoChange_eMail()") ."
	<div class=caption>{name_the_new_account_explain}</div>
	</td>
	</tr>
	</table>";
	
	$step2="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src='img/chiffre2.png'></td>
	<td valign='top'>
	<H3>{email}</H3><br>
	<strong>{email}:</strong>
	<br><input type='hidden' name='email' value='' id='email'>
	<span id='prefix_email' style='width:90px;border:1px solid #CCCCCC;padding:2px;font-size:11px;font-weight:bold;margin:2px'></span>@$field_domains&nbsp;<a href='javascript:ChangeAddUsereMail();'>[{change}]</a>
	<div class=caption>{user_email_text}</div>
	</td>
	</tr>
	</table>";
	
	$step3=RoundedLightWhite("
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src='img/chiffre3.png'></td>
	<td valign='top'>
	<H3>{password}</H3><br>
	<strong>{give_password}:</strong>
	<br>" . Field_password('password') ."
	<div class=caption>{give_password_text}</div>
	</td>
	</tr>
	</table>
	");
	
	$step4="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src='img/chiffre4.png'></td>
	<td valign='top'>
	<H3>{group}</H3><br>
	<strong>{select_user_group_title}:</strong>$groups
	<div class=caption>{select_user_group_text}</div>
	</td>
	</tr>
	</table>
	";
	
if($_GET["gpid"]>0){$step4="<input type='hidden' id='group_id' value='{$_GET["gpid"]}'>";}
	
	$html="
	<input type='hidden' id='ou-mem-add-form-user' value='{$_GET["ou"]}'>
	<input type='hidden' id='ou' value='{$_GET["ou"]}'>
	<div style='float:right'><img src='img/64_bg_lego.png'></div><H1>$title</H1>
	<p class=caption>{create_user_text}</p>
	<div id='adduser_ajax_newfrm'>
	<table style='width:100%'>
	<tr>
	<td valign='top'>$step1</td>
	<td valign='top'>$step2</td>
	</tr>
	<tr>
	<td valign='top'><br>$step3
	</td>
	<td valign='top'><br>$step4</td>
	</tr>
	<tr>
	<td colspan=2>
	<hr>
	<div style='padding:10px;text-align:right'>
		". button("{add}","UserADD()")."
	
	</td>
	</tr>		
	
	</table>
	</div>
	";
$tpl=new templates();

echo $tpl->_ENGINE_parse_body($html);
}
?>