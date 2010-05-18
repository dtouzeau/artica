<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	
	$users=new usersMenus();
$tpl=new templates();
if(!$users->AsPostfixAdministrator){
		echo $tpl->javascript_parse_text("alert('{ERROR_NO_PRIVS}');");
		die();
	}	

if(isset($_GET["popup"])){smtpd_client_restrictions_popup();exit;}
if(isset($_GET["reject_unknown_client_hostname"])){smtpd_client_restrictions_save();exit;}


js();


function js_smtpd_client_restrictions_save(){
	$page=CurrentPageName();
	
	return "
function smtpd_client_restrictions_save(){
	var XHR = new XHRConnection();
		if(document.getElementById('reject_unknown_client_hostname').checked){XHR.appendData('reject_unknown_client_hostname',1);}
		else{XHR.appendData('reject_unknown_client_hostname',0);}
	
		if(document.getElementById('reject_unknown_reverse_client_hostname').checked){XHR.appendData('reject_unknown_reverse_client_hostname',1);}
		else{XHR.appendData('reject_unknown_reverse_client_hostname',0);}		
		
		if(document.getElementById('reject_unknown_sender_domain').checked){XHR.appendData('reject_unknown_sender_domain',1);}
		else{XHR.appendData('reject_unknown_sender_domain',0);}	
		
		
		if(document.getElementById('reject_invalid_hostname').checked){XHR.appendData('reject_invalid_hostname',1);}
		else{XHR.appendData('reject_invalid_hostname',0);}	
				
		if(document.getElementById('reject_non_fqdn_sender').checked){XHR.appendData('reject_non_fqdn_sender',1);}
		else{XHR.appendData('reject_non_fqdn_sender',0);}

		if(document.getElementById('EnablePostfixAntispamPack').checked){XHR.appendData('EnablePostfixAntispamPack',1);}
		else{XHR.appendData('EnablePostfixAntispamPack',0);}		
			
		if(document.getElementById('reject_forged_mails').checked){XHR.appendData('reject_forged_mails',1);}
		else{XHR.appendData('reject_forged_mails',0);}		
					
		
		
		document.getElementById('smtpd_client_restrictions_div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_smtpd_client_restrictions_save);	
	}
	
	
	";
	
	
}


function smtpd_client_restrictions_popup(){
	
	
	$sock=new sockets();
	$EnablePostfixAntispamPack_value=$sock->GET_INFO('EnablePostfixAntispamPack');	
	$reject_forged_mails=$sock->GET_INFO('reject_forged_mails');	
	
	$restrictions=get_restrictions_classes();
	
	$whitelists=Paragraphe("routing-domain-relay.png","{PostfixAutoBlockDenyAddWhiteList}","{PostfixAutoBlockDenyAddWhiteList_explain}","javascript:Loadjs('postfix.iptables.php?white-js=yes')");
	$rollover=CellRollOver();
	
$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%>
	<img src='img/96-planetes-free.png'>
	</td>
	<td valign='top'>
	<table style='width:100%'>
	<tr>
	<td valign='top'>
		<p class=caption>{smtpd_client_restrictions_text}</p>
	</td>
	<td valign='top'>
		$whitelists
	</td>
	</tr>
	</table>
	
	</td>
	</tr>
	</table>
	<div id='smtpd_client_restrictions_div'>
	<table style='width:100%'>
	<tr $rollover>
	<td valign='top' width=1%>". Field_checkbox("reject_unknown_client_hostname",1,$restrictions["reject_unknown_client_hostname"])."</td>
	<td valign='top' style='font-size:14px;text-transform:capitalize'>{reject_unknown_client_hostname}</td>
	<td valign='top' width=1%>". help_icon("{reject_unknown_client_hostname_text}")."</td>
	</tr $rollover>
	<tr>
	<td valign='top' width=1%>". Field_checkbox("reject_unknown_reverse_client_hostname",1,$restrictions["reject_unknown_reverse_client_hostname"])."</td>
	<td valign='top' style='font-size:14px;text-transform:capitalize'>{reject_unknown_reverse_client_hostname}</td>
	<td valign='top' width=1%>". help_icon("{reject_unknown_reverse_client_hostname_text}")."</td>
	<tr $rollover>
	<td valign='top' width=1%>". Field_checkbox("reject_unknown_sender_domain",1,$restrictions["reject_unknown_sender_domain"])."</td>
	<td valign='top' style='font-size:14px;text-transform:capitalize'>{reject_unknown_sender_domain}</td>
	<td valign='top' width=1%>". help_icon("{reject_unknown_sender_domain_text}")."</td>
	</tr>
	<tr $rollover>
	<td valign='top' width=1%>". Field_checkbox("reject_invalid_hostname",1,$restrictions["reject_invalid_hostname"])."</td>
	<td valign='top' style='font-size:14px;text-transform:capitalize'>{reject_invalid_hostname}</td>
	<td valign='top' width=1%>". help_icon("{reject_invalid_hostname_text}")."</td>
	</tr>
	<tr $rollover>
	<td valign='top' width=1%>". Field_checkbox("reject_non_fqdn_sender",1,$restrictions["reject_non_fqdn_sender"])."</td>
	<td valign='top' style='font-size:14px;text-transform:capitalize'>{reject_non_fqdn_sender}</td>
	<td valign='top' width=1%>". help_icon("{reject_non_fqdn_sender_text}")."</td>
	</tr>
	<tr $rollover>
	<td valign='top' width=1%>". Field_checkbox("reject_forged_mails",1,$reject_forged_mails)."</td>
	<td valign='top' style='font-size:14px;text-transform:capitalize'>{reject_forged_mails}</td>
	<td valign='top' width=1%>". help_icon("{reject_forged_mails_text}")."</td>
	</tr>	
	
	
	<tr $rollover>
	<td valign='top' width=1%>". Field_checkbox("EnablePostfixAntispamPack",1,$EnablePostfixAntispamPack_value)."</td>
	<td valign='top' style='font-size:14px;text-transform:capitalize'>{EnablePostfixAntispamPack}</td>
	<td valign='top' width=1%>". help_icon("{EnablePostfixAntispamPack_text}")."</td>
	</tr>					
	</table>
	</div>
<hr>
	<div style='width:100%;text-align:right'>
	". button("{edit}","smtpd_client_restrictions_save()")."
	
	</div>	
	";


//smtpd_client_connection_rate_limit = 100
//smtpd_client_recipient_rate_limit = 20
	

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
	
	
	
}

function get_restrictions_classes(){
	
	
	$main=new smtpd_restrictions();
	if(!is_array($main->main_array["smtpd_client_restrictions"]["rules"])){return null;}
	
	
	while (list ($num, $ligne) = each ($main->main_array["smtpd_client_restrictions"]["rules"]) ){
		$array[$ligne["KEY"]]=1;
	}
	
	return $array;
	
	
}

function smtpd_client_restrictions_save(){
	$ldap=new clladp();
	
	if(!$ldap->ExistsDN("cn=restrictions_classes,cn=artica,$ldap->suffix")){
		$upd["objectClass"][]="top";
		$upd["objectClass"][]="top";
		$upd["objectClass"][]="PostFixStructuralClass";
		$upd["cn"][0]="restrictions_classes";
		if(!$ldap->ldap_add("cn=restrictions_classes,cn=artica,$ldap->suffix",$upd)){
			echo "cn=restrictions_classes,cn=artica,$ldap->suffix\n$ldap->ldap_last_error";
			return null;
		}		
	}		
		
		
		
	if($ldap->ExistsDN("cn=smtpd_client_restrictions,cn=restrictions_classes,cn=artica,$ldap->suffix")){
		$ldap->ldap_delete("cn=smtpd_client_restrictions,cn=restrictions_classes,cn=artica,$ldap->suffix",false);	
		$upd1["objectClass"][]="top";
		$upd1["objectClass"][]="PostFixRestrictionStandardClasses";
		$upd1["cn"][0]="smtpd_client_restrictions";
		if(!$ldap->ldap_add("cn=smtpd_client_restrictions,cn=restrictions_classes,cn=artica,$ldap->suffix",$upd1)){
			echo "Modify smtpd_client_restrictions branch\n$ldap->ldap_last_error";
			return null;
		}
	}
	
	unset($upd1);
	
	if($ldap->ExistsDN("cn=smtpd_helo_restrictions,cn=restrictions_classes,cn=artica,$ldap->suffix")){
		$ldap->ldap_delete("cn=smtpd_helo_restrictions,cn=restrictions_classes,cn=artica,$ldap->suffix",false);	
	}
	
	
	if(!$ldap->ExistsDN("cn=smtpd_helo_restrictions,cn=restrictions_classes,cn=artica,$ldap->suffix")){
		$upd1["objectClass"][]="top";
		$upd1["objectClass"][]="PostFixRestrictionStandardClasses";
		$upd1["cn"][0]="smtpd_helo_restrictions";
		if(!$ldap->ldap_add("cn=smtpd_helo_restrictions,cn=restrictions_classes,cn=artica,$ldap->suffix",$upd1)){
			echo "Modify smtpd_helo_restrictions branch\n$ldap->ldap_last_error";
			return null;
		}
	}	
	
	
	
	
	$EnablePostfixAntispamPack=$_GET["EnablePostfixAntispamPack"];
	$upd_vals["PostFixRestrictionClassList"][]="permit_mynetworks=\"\"";
	$upd_vals["PostFixRestrictionClassList"][]="permit_sasl_authenticated=\"\"";
	$upd_vals["PostFixRestrictionClassList"][]="check_client_access=\"hash:/etc/postfix/postfix_allowed_connections\"";
	if($_GET["reject_unknown_client_hostname"]==1){$upd_vals["PostFixRestrictionClassList"][]="reject_unknown_client_hostname=\"\"";}
	if($_GET["reject_invalid_hostname"]==1){$upd_vals["PostFixRestrictionClassList"][]="reject_invalid_hostname=\"\"";}
	if($_GET["reject_unknown_reverse_client_hostname"]==1){$upd_vals["PostFixRestrictionClassList"][]="reject_unknown_reverse_client_hostname=\"\"";}
	if($_GET["reject_unknown_sender_domain"]==1){$upd_vals["PostFixRestrictionClassList"][]="reject_unknown_sender_domain=\"\"";}
	if($_GET["reject_non_fqdn_sender"]==1){$upd_vals["PostFixRestrictionClassList"][]="reject_non_fqdn_sender=\"\"";}
	
	if($EnablePostfixAntispamPack==1){
		
		$upd_vals["PostFixRestrictionClassList"][]="reject_rbl_client=\"zen.spamhaus.org\"";
		$upd_vals["PostFixRestrictionClassList"][]="reject_rbl_client=\"sbl.spamhaus.org\"";
		$upd_vals["PostFixRestrictionClassList"][]="reject_rbl_client=\"cbl.abuseat.org\"";
		
	}
	
	$upd_vals["PostFixRestrictionClassList"][]="permit=\"\"";
	
	$sock=new sockets();
	$sock->SET_INFO('EnablePostfixAntispamPack',$EnablePostfixAntispamPack);
	$sock->SET_INFO('reject_forged_mails',$_GET["reject_forged_mails"]);
	
	
	
	
	if(!$ldap->Ldap_modify("cn=smtpd_client_restrictions,cn=restrictions_classes,cn=artica,$ldap->suffix",$upd_vals)){
		echo "Modify smtpd_client_restrictions branch\n$ldap->ldap_last_error";
			return null;
		}

unset($upd_vals);		
if($EnablePostfixAntispamPack==1){
		$upd_vals["PostFixRestrictionClassList"][]="permit_mynetworks=\"\"";
		$upd_vals["PostFixRestrictionClassList"][]="permit_sasl_authenticated=\"\"";
		$upd_vals["PostFixRestrictionClassList"][]="check_client_access=\"hash:/etc/postfix/postfix_allowed_connections\"";
		$upd_vals["PostFixRestrictionClassList"][]="reject_non_fqdn_hostname=\"\"";
		$upd_vals["PostFixRestrictionClassList"][]="reject_invalid_hostname=\"\"";
		$upd_vals["PostFixRestrictionClassList"][]="permit=\"\"";
		
	if(!$ldap->Ldap_modify("cn=smtpd_helo_restrictions,cn=restrictions_classes,cn=artica,$ldap->suffix",$upd_vals)){
		echo "Modify datas in smtpd_helo_restrictions branch\n$ldap->ldap_last_error";
			return null;
		}		
		
	}		
		
$main=new main_cf();		
$main->save_conf_to_server(1);
$sock=new sockets();
$tpl=new templates();
$sock->getFrameWork("cmd.php?reconfigure-postfix=yes");
	
}




function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	
	$title=$tpl->_ENGINE_parse_body('{smtpd_client_restrictions_icon}',"postfix.index.php");
	$title2=$tpl->_ENGINE_parse_body('{PostfixAutoBlockManageFW}',"postfix.index.php");
	$title_compile=$tpl->_ENGINE_parse_body('{PostfixAutoBlockCompileFW}',"postfix.index.php");
	
	$prefix="smtpd_client_restriction";
	$PostfixAutoBlockDenyAddWhiteList_explain=$tpl->_ENGINE_parse_body('{PostfixAutoBlockDenyAddWhiteList_explain}');
	$html="
var {$prefix}timerID  = null;
var {$prefix}tant=0;
var {$prefix}reste=0;

	function {$prefix}demarre(){
		{$prefix}tant = {$prefix}tant+1;
		{$prefix}reste=20-{$prefix}tant;
		if(!YahooWin4Open()){return false;}
		if ({$prefix}tant < 5 ) {                           
		{$prefix}timerID = setTimeout(\"{$prefix}demarre()\",1000);
	      } else {
				{$prefix}tant = 0;
				{$prefix}CheckProgress();
				{$prefix}demarre();                                
	   }
	}	
	
	
	function {$prefix}StartPostfixPopup(){
		YahooWin2(650,'$page?popup=yes','$title');
	}
	
var x_smtpd_client_restrictions_save= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
	{$prefix}StartPostfixPopup();
}		
	

	
	function PostfixAutoBlockStartCompile(){
		{$prefix}CheckProgress();
		{$prefix}demarre();       
	}
	
	var x_{$prefix}CheckProgress= function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('PostfixAutoBlockCompileStatusCompile').innerHTML=tempvalue;
	}	
	
	function {$prefix}CheckProgress(){
			var XHR = new XHRConnection();
			XHR.appendData('compileCheck','yes');
			XHR.sendAndLoad('$page', 'GET',x_{$prefix}CheckProgress);
	
	}

	
	function PostfixIptablesSearchKey(e){
			if(checkEnter(e)){
				PostfixIptablesSearch();
			}
	}

		
".js_smtpd_client_restrictions_save()."
	
	
	{$prefix}StartPostfixPopup();
	";
	echo $html;
	}
?>