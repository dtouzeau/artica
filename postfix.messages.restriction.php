<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){header('location:users.index.php');exit;}



		if(isset($_GET["script"])){ajax_js();exit;}
		if(isset($_GET["ajax-pop"])){ajax_pop();exit;}
		if(isset($_GET["message_size_limit"])){save();exit;}
		

function ajax_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{messages_restriction}');
	$html="
	var x='$x';
	$datas
	function LoadMain(){
		YahooWinS(550,'$page?ajax-pop=yes','$title');
	}
	
var x_SaveMessagesRestrictions=function (obj) {
	tempvalue=obj.responseText;
	YahooWinSHide();
    }	
	
function SaveMessagesRestrictions(){
	var XHR = new XHRConnection();
	XHR.appendData('message_size_limit',document.getElementById('message_size_limit').value);
	XHR.appendData('default_destination_recipient_limit',document.getElementById('default_destination_recipient_limit').value);
	XHR.appendData('smtpd_recipient_limit',document.getElementById('smtpd_recipient_limit').value);
	XHR.appendData('mime_nesting_limit',document.getElementById('mime_nesting_limit').value);
	XHR.appendData('header_address_token_limit',document.getElementById('header_address_token_limit').value);
	XHR.appendData('virtual_mailbox_limit',document.getElementById('virtual_mailbox_limit').value);
	document.getElementById('messages_restriction_id').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_SaveMessagesRestrictions);
}		
		
	LoadMain();
	
	";
	
	echo $html;
	
	
	
	
}

function save(){
	$sock=new sockets();
	$_GET["message_size_limit"]=($_GET["message_size_limit"]*1000)*1024;
	$sock->SET_INFO("message_size_limit",$_GET["message_size_limit"]);
	$sock->SET_INFO("default_destination_recipient_limit",$_GET["default_destination_recipient_limit"]);
	$sock->SET_INFO("smtpd_recipient_limit",$_GET["smtpd_recipient_limit"]);
	$sock->SET_INFO("mime_nesting_limit",$_GET["mime_nesting_limit"]);
	$sock->SET_INFO("header_address_token_limit",$_GET["header_address_token_limit"]);
	$sock->SET_INFO("virtual_mailbox_limit",$_GET["virtual_mailbox_limit"]);
	echo "<H1>OK</H1>";
	$sock->getFrameWork("cmd.php?postfix-others-values=yes");
}

function ajax_pop(){
		
		$sock=new sockets();
		
		
		$main=new main_cf();
		$main->main_array["message_size_limit"]=$sock->GET_INFO("message_size_limit");
		$main->main_array["default_destination_recipient_limit"]=$sock->GET_INFO("default_destination_recipient_limit");
		$main->main_array["smtpd_recipient_limit"]=$sock->GET_INFO("smtpd_recipient_limit");
		$main->main_array["mime_nesting_limit"]=$sock->GET_INFO("mime_nesting_limit");
		$main->main_array["header_address_token_limit"]=$sock->GET_INFO("header_address_token_limit");
		$main->main_array["virtual_mailbox_limit"]=$sock->GET_INFO("virtual_mailbox_limit");
		$main->FillDefaults();
		
		$main->main_array["message_size_limit"]=($main->main_array["message_size_limit"]/1024)/1000;
		$html="
		
		<div id='messages_restriction_id'>
		<table style='width:100%'>
		<tr>
			    <td nowrap class=legend>{message_size_limit}</strong>:</td>
			    <td>" . Field_text('message_size_limit',$main->main_array["message_size_limit"],'width:60px')." (Mb)</td>
			    <td>". help_icon('{message_size_limit_text}')."</td>
		</tr>
		
		
	<tr>
			    <td nowrap class=legend>{default_destination_recipient_limit}</strong>:</td>
			    <td>" . Field_text('default_destination_recipient_limit',$main->main_array["default_destination_recipient_limit"],'width:60px')."</td>
			     <td>". help_icon('{default_destination_recipient_limit_text}')."</td>
	</tr>
	
	<tr>
			    <td nowrap class=legend>{smtpd_recipient_limit}</strong>:</td>
			    <td>" . Field_text('smtpd_recipient_limit',$main->main_array["smtpd_recipient_limit"],'width:60px')."</td>
			     <td>". help_icon('{smtpd_recipient_limit_text}')."</td>
		</tr>

		<tr>
			    <td nowrap class=legend>{mime_nesting_limit}</strong>:</td>
			    <td>" . Field_text('mime_nesting_limit',$main->main_array["mime_nesting_limit"],'width:60px')." </td>
			     <td>". help_icon('{mime_nesting_limit_text}')."</td>
		</tr>
		
		<tr>
			    <td nowrap class=legend>{header_address_token_limit}</strong>:</td>
			    <td>" . Field_text('header_address_token_limit',$main->main_array["header_address_token_limit"],'width:60px')." </td>
			     <td>". help_icon('{header_address_token_limit_text}')."</td>
		</tr>
		<tr>
			    <td nowrap class=legend>{virtual_mailbox_limit}</strong>:</td>
			    <td>" . Field_text('virtual_mailbox_limit',$main->main_array["virtual_mailbox_limit"],'width:40%')." </td>
			    <td>". help_icon('{virtual_mailbox_limit_text}')."</td>
		</tr>
		<tr><td colspan=2 align='rigth' style='padding-right:10px;text-align:right'>
		<hr>". button("{apply}","SaveMessagesRestrictions()")."
		</td></tr>
	</table>
	</div>";


$tpl=new templates();
echo  $tpl->_parse_body($html);
}