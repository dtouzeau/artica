<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
	
	if(posix_getuid()==0){die();}
	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}


	
	
if(isset($_GET["use-my-isp"])){isp_js();exit;}	
if(isset($_GET["ajaxmenu"])){main_switch();exit;}
if(isset($_GET["mastercf"])){main_mastercf();exit;}
if(isset($_GET["master_datas"])){SaveMastercf();exit;}
if(isset($_GET["main"])){main_switch();exit;}
if(isset($_GET["DeleteCache"])){emptycache();exit;}
if($_GET["script"]=="antispam"){echo antispam_script();exit;}
if($_GET["script"]=="milterbehavior"){echo milter_behavior_script();exit;}
if($_GET["script"]=="auth"){echo sasl_script();exit;}
if($_GET["script"]=="backup"){echo backup_script();exit;}
if($_GET["script"]=="deny_domain"){echo deny_domain_script();exit;}
if($_GET["script"]=="multidomains"){echo multidomains_script();exit;}
if($_GET["script"]=="orangefr"){echo orangefr_script();exit;}
if(isset($_GET["isp_address"])){SaveISPAddress();exit;}



if(isset($_GET["multidomains"])){echo multidomains_popup();exit;}
if(isset($_GET["orangefr"])){echo orangefr_popup();exit;}


if(isset($_GET["popup-antispam"])){antispam_popup();exit;}
if(isset($_GET["popup-milter-behavior"])){milter_behavior_popup();exit;}
if(isset($_GET["popup-backup-behavior"])){backup_popup();exit;}

if(isset($_GET["enable_as_modules"])){antispam_popup_save();exit;}
if(isset($_GET["enable_milter"])){milter_behavior_save();exit;}
if(isset($_GET["relayhost"])){relayhost_popup();exit();}

if(isset($_GET["popup-auth"])){sasl_popup();exit();}
if(isset($_GET["save_auth"])){sasl_save();exit;}
if(isset($_GET["popup-auth-status"])){sasl_satus();exit;}
if(isset($_GET["popup-auth-adv"])){sasl_adv();exit;}
if(isset($_GET["broken_sasl_auth_clients"])){sasl_adv_save();exit;}

if(isset($_GET["MailArchiverEnabled"])){backup_save();exit;}
if(isset($_GET["EnableVirtualDomainsInMailBoxes"])){multidomains_save();exit;}
if(isset($_GET["bar-status"])){echo bar_status();exit;}
if(isset($_GET["emptycache"])){emptycache();exit;}

if(isset($_GET["postfix-notifs"])){notifs_js();exit;}
if(isset($_GET["postfix-notifs-popup"])){notifs_popup();exit;}
if(isset($_GET["postfix-notifs-template"])){notifs_template();exit;}

if(isset($_GET["double_bounce_sender"])){notifs_save();exit;}
if(isset($_GET["2bounce_notice_recipient"])){notifs_save();exit;}
if(isset($_GET["template_save"])){notifs_save_template();exit;}

if(isset($_GET["js"])){js();exit;}
if(isset($_GET["popup-index"])){popup_index();exit;}
if(isset($_GET["popup-isp"])){isp_popup();exit;}

//reject_unknown_sender_domain,reject_non_fqdn_hostname, reject_non_fqdn_sender,reject_invalid_hostname 

//http://wiki.centos.org/HowTos/postfix_restrictions

js();


function isp_js(){
	
if(GET_CACHED(__FILE__,__FUNCTION__)){return false;}	
	
$page=CurrentPageName();
$users=new usersMenus();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{USE_MY_ISP}');

$html="

function USE_MY_ISP_LOAD(){
	YahooWin3('730','$page?popup-isp=yes','$title');
	
	}
	
USE_MY_ISP_LOAD();
";

SET_CACHED(__FILE__,__FUNCTION__,null,$html);
echo $html;
	
}

function js(){
if(GET_CACHED(__FILE__,__FUNCTION__)){return false;}		
$prefix="postfix_index_page_php_";
$page=CurrentPageName();
$users=new usersMenus();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{postfix_messaging}');

if($users->KASPERSKY_SMTP_APPLIANCE){
	$title=$tpl->_ENGINE_parse_body('Artica For Kaspersky Appliance');
}
$addons=js_addons();

$html="$addons

function {$prefix}Loadpage(){
	$('#BodyContent').load('$page?popup-index=yes');
	}
	

	
function {$prefix}DisplayDivs(){
		timeout=timeout+1;
		if(timeout>10){return;}
		if(!document.getElementById('main_config_postfix')){
			setTimeout('{$prefix}DisplayDivs()',900);
			return;
		}
		
		{$prefix}demarre();
		setTimeout('{$prefix}ChargeLogs()',1000);
		setTimeout('{$prefix}StatusBar()',1500);
	}

	
	
	
	
	function RefreshIndexPostfixAjax(){
		{$prefix}StatusBar();
	}


var X_PostfixDeleteCache= function (obj) {
	var results=obj.responseText;
	alert(results);
	YahooWin2(750,'$page?popup-antispam=yes','Anti-spam',''); 
	}
		


function PostfixDeleteCache(){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteCache','DeleteCache');
		document.getElementById('dialog2').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_PostfixDeleteCache);		
	
}
	
 {$prefix}Loadpage();
";
SET_CACHED(__FILE__,__FUNCTION__,null,$html);	
	echo $html;
}

function isp_popup(){
if(GET_CACHED(__FILE__,__FUNCTION__,null)){return;}
	$orange=Buildicon64('DEF_ICO_SEND_TO_ORANGE');
	$oleane=Buildicon64('DEF_ICO_SEND_TO_OLEANE');
	$oneone=Buildicon64('DEF_ICO_SEND_TO_ONEONE');
	$wanadoo=Buildicon64('DEF_ICO_SEND_TO_WANADOO');
	$free=Buildicon64('DEF_ICO_SEND_TO_FREE');
	
	$tr[]=$orange;
	$tr[]=$oleane;
	$tr[]=$wanadoo;
	$tr[]=$oneone;
	$tr[]=$free;


	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
	$tables[]="</table>";	
	$html=implode("\n",$tables);
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	SET_CACHED(__FILE__,__FUNCTION__,null,$html);		
	
}


function popup_index(){
	if(GET_CACHED(__FILE__,__FUNCTION__,null)){return;}
	$page=CurrentPageName();
	$html=main_tabs();
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	SET_CACHED(__FILE__,__FUNCTION__,null,$html);
	echo $html;
}

function js_addons(){

	$page=CurrentPageName();
	$prefix="postfix_index_page_php_";
	$tpl=new templates();
	$start_monitor=$tpl->_ENGINE_parse_body('{start_monitor}');
	$stop_monitor=$tpl->_ENGINE_parse_body('{stop_monitor}');	
	
	$addons=file_get_contents('js/postfix-tls.js')."\n".file_get_contents('js/postfix-transport.js');
	
	
$html="
var {$prefix}timerID  = null;
var {$prefix}tant=0;
var {$prefix}reste=0;
var stopm='$stop_monitor';
var startm='$start_monitor';
var timeout=0;

function {$prefix}demarre(){
	{$prefix}tant = {$prefix}tant+1;
	{$prefix}reste=20-{$prefix}tant;
	if(!document.getElementById('main_config_postfix')){return;}
	if ({$prefix}tant < 30 ) {                           
	{$prefix}timerID =setTimeout(\"{$prefix}demarre()\",2000);
      } else {
		{$prefix}tant = 0;
		{$prefix}ChargeLogs();
		{$prefix}demarre(); 
		                              
   }
}

function {$prefix}StatusBar(){
 	   var myl=document.getElementById('main_config_postfix').innerHTML;
	   if(myl.length<100){
	   	setTimeout('{$prefix}StatusBar()',500);
	   	return;
	   }
	   
	   var xl=document.getElementById('{$prefix}servinfos').innerHTML;
		if(xl.length<100){
	   	setTimeout('{$prefix}StatusBar()',500);
	   	return;
	   }	   
	   

	if(document.getElementById('monitor_page_switch')){
		LoadAjax('barstatus','postfix.index.php?bar-status=yes&mode='+document.getElementById('monitor_page_switch').value);
	}else{
		LoadAjax('barstatus','postfix.index.php?bar-status=yes&mode=1');
	}

	
}



function EmptyCache(){
		var XHR = new XHRConnection();
        XHR.appendData('emptycache','yes');
        XHR.sendAndLoad('$page', 'GET'); 
}

function {$prefix}ChargeLogs(){
	   if(!document.getElementById('monitor_page')){return;}
	   var myl=document.getElementById('main_config_postfix').innerHTML;
	   if(myl.length<100){
	   	setTimeout('{$prefix}ChargeLogs()',500);
	   	return;
	   }

   		if(document.getElementById('monitor_page').value==1){
			LoadAjax('{$prefix}servinfos','admin.index.php?postfix-status=yes&hostname={$_GET["hostname"]}');
			setTimeout('{$prefix}StatusBar()',1000);
		}
	}
	
$addons";	
return $html;	
	
}




function main_tabs(){
	
	if(!isset($_GET["main"])){$_GET["main"]="network";};
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	$tpl=new templates();
	$sock=new sockets();
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	$filters_settings=$tpl->_ENGINE_parse_body('{filters_settings}');
	if(strlen($filters_settings)>25){$filters_settings=texttooltip(substr($filters_settings,0,22).'...',$filters_settings,null,null,1);}
	
	$page=CurrentPageName();
	$array["status"]='{status}';
	$array["mailbox"]='{mailbox_settings}';
	$array["transport_settings"]='{transport_settings}';
	$array["security_settings"]='{security_settings}';
	$array["filters"]=$filters_settings;
	$array["filters-connect"]="{filters_connect}";
	

	
	if($EnablePostfixMultiInstance==1){
		unset($array["security_settings"]);
		unset($array["tweaks"]);
		unset($array["filters-connect"]);
	}

	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?main=$num&hostname=$hostname\"><span>$ligne</span></li>\n";
	}
	
	
	return "
	<div id=main_config_postfix style='width:100%;height:850px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_postfix').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";		
}


function cookies_main(){
	
	if($_GET["main"]==null){
		if($_COOKIE["postfix_index_main"]<>null){
			$_GET["main"]=$_COOKIE["postfix_index_main"];
		}else{
			$_GET["main"]="transport_settings";
		}
	}else{
		setcookie('postfix_index_main',$_GET["main"], (time() + 3600));

	}
	
}




function milter_behavior_script(){
$page=CurrentPageName();	
$html=
	"YahooWin2(550,'$page?popup-milter-behavior=yes','milters...',''); 

	
var X_ApplyMilterBehavior= function (obj) {
	var results=obj.responseText;
	alert(results);
	YahooWin2(550,'$page?popup-milter-behavior=yes','milters...',''); 
	}
		
	function ApplyMilterBehavior(){
		var XHR = new XHRConnection();
		XHR.appendData('enable_milter',document.getElementById('enable_milter').value);
		XHR.appendData('ArticaFilterMaxProc',document.getElementById('ArticaFilterMaxProc').value);
		
		
		document.getElementById('img_enable_milter').src='img/wait_verybig.gif';
		XHR.sendAndLoad('$page', 'GET',X_ApplyMilterBehavior);				
	}";
	
return  $html;	
}

function notifs_js(){
$page=CurrentPageName();	
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{POSTFIX_SMTP_NOTIFICATIONS}');
$html=
	"YahooWin2(550,'$page?postfix-notifs-popup=yes','$title',''); 	
	
	
	function switch_notifs_popup(id){
	   document.getElementById('id_sender').style.display='none';
   	document.getElementById('id_recipients').style.display='none';
   	document.getElementById('id_templates').style.display='none';
   	document.getElementById(id).style.display='block';  
	
	}
	
	function ShowTemplateFrom(template){
		YahooWin3(650,'$page?postfix-notifs-template='+template,template,''); 
	
	}
	
	";
	
echo $html;
	
	
}

function notifs_save(){
	$main=new main_cf();
	if(isset($_GET["double_bounce_sender"])){$main->main_array["double_bounce_sender"]=$_GET["double_bounce_sender"];}
	if(isset($_GET["address_verify_sender"])){$main->main_array["address_verify_sender"]=$_GET["address_verify_sender"];}
	if(isset($_GET["2bounce_notice_recipient"])){$main->main_array["2bounce_notice_recipient"]=$_GET["2bounce_notice_recipient"];}
	if(isset($_GET["error_notice_recipient"])){$main->main_array["error_notice_recipient"]=$_GET["error_notice_recipient"];}
	if(isset($_GET["delay_notice_recipient"])){$main->main_array["delay_notice_recipient"]=$_GET["delay_notice_recipient"];}
	if(isset($_GET["empty_address_recipient"])){$main->main_array["empty_address_recipient"]=$_GET["empty_address_recipient"];}
	$main->save_conf();
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("{notif_save_conf_ldap}");
	
	}

function notifs_popup(){
	
	
	$array["id_sender"]="{senders}";
	$array["id_recipients"]="{recipients}";
	$array["id_templates"]="{templates}";
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$tab=$tab . "<li><a href=\"javascript:switch_notifs_popup('$num');\" $class>$ligne</a></li>\n";
			
		}	
	$tab="<div id=tablist>$tab</div><br>";
	$page=CurrentPageName();
	$main=new main_cf();
	$html="<H1>{POSTFIX_SMTP_NOTIFICATIONS}</H1>
	$tab
	<table style='width:100%'>
	<tr>
	<td valign='top'>
		<p class=caption>{POSTFIX_SMTP_NOTIFICATIONS_TEXT}</p>
	</td>
	<td valign='top' width=2%>
	</td>
	</tr>
	</table>
	<div style='width:95%;height:300px;overflow:auto'>
	<div id='id_sender'>
	<H3>{senders}</H3>
	<form name='ffm1notif'>
	<table style='width:95%' class=table_form>	
	<tr>
		<td class=legend nowrap>{double_bounce_sender}:</td>
		<td>" . Field_text('double_bounce_sender',$main->main_array["double_bounce_sender"],'width:160px')."</td>
	</tr>
	
	<tr>
		<td><td class=explain>{double_bounce_sender_text}</td>
	</tr>
	
	<tr>
		<td class=legend nowrap nowrap>{address_verify_sender}:</td>
		<td>" . Field_text('address_verify_sender',$main->main_array["address_verify_sender"],'width:160px')."</td>
	</tr>
	<tr>
		<td><td class=explain>{address_verify_sender_text}</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>
		<hr>
		". button("{edit}","ParseForm('ffm1notif','$page',true);")."
		</td>
		
		
	</tr>		
</table>
</form>
</div>
<div id='id_recipients' style='display:none'>
	<H3>{recipients}</H3>
	<form name='ffm1notif2'>
<table style='width:95%' class=table_form>	
		
	<tr>
		<td class=legend nowrap>{2bounce_notice_recipient}:</td>
		<td>" . Field_text('2bounce_notice_recipient',$main->main_array["2bounce_notice_recipient"],'width:160px')."</td>
	</tr>
	<tr>
		<td><td class=explain>{2bounce_notice_recipient_text}</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{error_notice_recipient}:</td>
		<td>" . Field_text('error_notice_recipient',$main->main_array["error_notice_recipient"],'width:160px')."</td>
	</tr>
	<tr>
		<td><td class=explain>{error_notice_recipient_text}</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{delay_notice_recipient}:</td>
		<td>" . Field_text('delay_notice_recipient',$main->main_array["delay_notice_recipient"],'width:160px')."</td>
	</tr>
	<tr>
		<td><td class=explain>{delay_notice_recipient_text}</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{empty_address_recipient}:</td>
		<td>" . Field_text('empty_address_recipient',$main->main_array["empty_address_recipient"],'width:160px')."</td>
	</tr>
	<tr>
		<td><td class=explain>{empty_address_recipient_text}</td>
	</tr>		
	<tr>
			<td colspan=2 align='right'>
		<hr>
		". button("{edit}","ParseForm('ffm1notif','$page',true);")."
		</td>
	</tr>		
	</table>
</div>
	</div>
	";
	
$tmpl="<H3>{templates}</H3>";
$main=new bounces_templates();

	while (list ($num, $ligne) = each ($main->templates_array) ){
		$tmpl=$tmpl. Paragraphe('64-templates.png',$num,"{{$num}}","javascript:ShowTemplateFrom('$num')",null,210,null,0,true);
			
		}
$html=$html."
<div id='id_templates' style='display:none'>$tmpl</div>";		
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}


function notifs_template(){
	$template=$_GET["postfix-notifs-template"];
	$main=new bounces_templates();
	
	$array=$main->templates_array[$template];
	$html="<H1>$template</H1>
		<p class=caption>{{$template}}</p>
		<form name='ffm$template'>
		<input type='hidden' id='template_save' value='$template' name='template_save'>
		<table style='width:100%' class=table_form>
		<tr>
			<td class=legend>Charset:</td>
			<td>" . Field_text('Charset',$array["Charset"],'width:90px')."</td>
		</tr>
		<tr>
			<td class=legend>{mail_from}:</td>
			<td>" . Field_text('From',$array["From"],'width:200px')."</td>
		</tr>
		<tr>
			<td class=legend>{subject}:</td>
			<td>" . Field_text('Subject',$array["Subject"],'width:290px')."</td>
		</tr>	
		<tr>
			<td class=legend>Postmaster-Subject:</td>
			<td>" . Field_text('Postmaster-Subject',$array["Postmaster-Subject"],'width:290px')."</td>
		</tr>	
		<tr>
			<td valign='top' colspan=2 align='right'><input type='button' OnClick=\"javascript:ParseForm('ffm$template','$page',true)\" value='{edit}&nbsp;&raquo;'></td>
		</tr>	
		<tr>
			<td valign='top' colspan=2><textarea id='Body' style='width:100%;border:1px dotted #CCCCCC;height:200px;margin:4px;padding:4px'>{$array["Body"]}</textarea></td>
		</tr>
			
		</table>
		</form>
		
		";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function notifs_save_template(){
	$template=$_GET["template_save"];
	$main=new bounces_templates();
	
	while (list ($num, $ligne) = each ($_GET) ){
		$main->templates_array[$template][$num]=$ligne;
		
	}
	
	$main->Save();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("{notif_save_conf_ldap}");
	
}

function multidomains_script(){
	
	$tpl=new templates();
	$mul=$tpl->_ENGINE_parse_body('{multidomains}');
	$page=CurrentPageName();
	$html="YahooWin2(550,'$page?multidomains=yes','$mul',''); 
	
var X_ApplyMultidomains= function (obj) {
	var results=obj.responseText;
	alert(results);
	YahooWin2(550,'$page?multidomains=yes','$mul',''); 
	}
		
	function ApplyMultidomains(){
		var XHR = new XHRConnection();
		XHR.appendData('EnableVirtualDomainsInMailBoxes',document.getElementById('EnableVirtualDomainsInMailBoxes').value);
		document.getElementById('img_EnableVirtualDomainsInMailBoxes').src='img/wait_verybig.gif';
		XHR.sendAndLoad('$page', 'GET',X_ApplyMultidomains);				
	}	
	
	";
	
	
return  $html;		
}




function multidomains_save(){
	$artica=new artica_general();
	$artica->EnableVirtualDomainsInMailBoxes=$_GET["EnableVirtualDomainsInMailBoxes"];
	$artica->Save();
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?reconfigure-cyrus=yes");
	$sock->getFrameWork("cmd.php?restart-cyrus=yes");
}


function multidomains_popup(){
	$artica=new artica_general();
	
	$main=new main_cf();
	$milter=Paragraphe_switch_img('{multidomains}',
	'{multidomains_explain}','EnableVirtualDomainsInMailBoxes',$artica->EnableVirtualDomainsInMailBoxes,'{enable_disable}',495);

	$html="
	<H1>{multidomains}</H1>
	<p class=caption>{multidomains_text}</p>
	$milter
	<hr>
		<div style='text-align:right;width:100%'>". button('{apply}',"ApplyMultidomains()")."</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'postfix.plugins.php');	
	
}



function backup_script(){
$page=CurrentPageName();	
$html=
	"YahooWin2(550,'$page?popup-backup-behavior=yes','backup...',''); 

	
var X_ApplyBackupBehavior= function (obj) {
	var results=obj.responseText;
	alert(results);
	YahooWin2(550,'$page?popup-backup-behavior=yes','backup...',''); 
	}
		
	function ApplyBackupBehavior(){
		var XHR = new XHRConnection();
		XHR.appendData('MailArchiverEnabled',document.getElementById('enable_archiver').value);
		document.getElementById('img_enable_archiver').src='img/wait_verybig.gif';
		XHR.sendAndLoad('$page', 'GET',X_ApplyBackupBehavior);				
	}";
	
return  $html;	
}

function backup_save(){
	
	$MailArchiverEnabled=$_GET["MailArchiverEnabled"];
	writelogs("MailArchiverEnabled=$MailArchiverEnabled",__FUNCTION__,__FILE__);
	$sock=new sockets();
	$sock->SET_INFO('MailArchiverEnabled',$MailArchiverEnabled);
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?SaveMaincf=yes");
	}


function sasl_script(){
	$page=CurrentPageName();
	$html=
	"YahooWin2(700,'$page?popup-auth=yes','SASL...',''); 
	
var X_enable_auth= function (obj) {
	var results=obj.responseText;
	alert(results);
	YahooWin2(700,'$page?popup-auth=yes','SASL...',''); 
	}	
	
	function SaslStatus(){
		YahooWin3(650,'$page?popup-auth-status=yes','SASL...',''); 
		
	}
	
	function SasladvOptions(){
		YahooWin3(550,'$page?popup-auth-adv=yes','SASL...',''); 
		
	}	
	
	function enable_auth(){
		var XHR = new XHRConnection();
		XHR.appendData('save_auth',document.getElementById('enable_auth').value);
		XHR.appendData('PostfixEnableSubmission',document.getElementById('PostfixEnableSubmission').value);
		document.getElementById('dialog2').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_enable_auth);	
	
	}
";
	return $html;
	}
	
function deny_domain_script(){
	$tpl=new templates();
	$text1=$tpl->_ENGINE_parse_body('{BLOCK_DOMAIN_HOWTO}');
	$text1=str_replace("\n",'\n',$text1);
	$page=CurrentPageName();
	
	
	$html="
		var X_PDOM= function (obj) {
			var results=obj.responseText;
			alert(results);
			}
	
	
		var pattern=prompt(\"$text1\");
		var XHR = new XHRConnection();
		XHR.appendData('quick_deny_domains',pattern);
		XHR.sendAndLoad('smtp.rules.php', 'GET',X_PDOM);
	 	
	
	";
	echo $html;
}

function milter_behavior_save(){
	$sock=new sockets();
	$sock->SET_INFO("PostfixMiltersBehavior",$_GET["enable_milter"]);
	$sock->SET_INFO("ArticaFilterMaxProc",$_GET["ArticaFilterMaxProc"]);
	$sock->getFrameWork("cmd.php?reconfigure-postfix=yes");
	
}

function antispam_script(){
	
	$page=CurrentPageName();
	$html=
	"YahooWin2(750,'$page?popup-antispam=yes','Anti-spam',''); 
	
	
var X_ApplyKasSpamas= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	YahooWin2Hide();
	RefreshTab('main_config_postfix');
	}	
	
	function ApplyKasSpamas(){
		var XHR = new XHRConnection();
	
		if(document.getElementById('enable_spamassassin')){
			XHR.appendData('enable_spamassassin',document.getElementById('enable_spamassassin').value);
			document.getElementById('img_enable_spamassassin').src='img/wait_verybig.gif';
			
		}
		
		if(document.getElementById('enable_kaspersky_as')){
			XHR.appendData('enable_kaspersky_as',document.getElementById('enable_kaspersky_as').value);
			document.getElementById('img_enable_kaspersky_as').src='img/wait_verybig.gif';
		}	
		
		if(document.getElementById('enable_amavis')){
			XHR.appendData('enable_amavis',document.getElementById('enable_amavis').value);
			document.getElementById('img_enable_amavis').src='img/wait_verybig.gif';
		}

		if(document.getElementById('MilterGreyListEnabled')){
			XHR.appendData('MilterGreyListEnabled',document.getElementById('MilterGreyListEnabled').value);
			document.getElementById('img_MilterGreyListEnabled').src='img/wait_verybig.gif';
		}			

		if(document.getElementById('EnableASSP')){
			XHR.appendData('EnableASSP',document.getElementById('EnableASSP').value);
			document.getElementById('img_EnableASSP').src='img/wait_verybig.gif';
		}	

		if(document.getElementById('EnableArticaSMTPFilter')){
			XHR.appendData('EnableArticaSMTPFilter',document.getElementById('EnableArticaSMTPFilter').value);
			document.getElementById('img_EnableArticaSMTPFilter').src='img/wait_verybig.gif';
		}		
		
		if(document.getElementById('kavmilterEnable')){
			XHR.appendData('kavmilterEnable',document.getElementById('kavmilterEnable').value);
			document.getElementById('img_kavmilterEnable').src='img/wait_verybig.gif';
		}		
		
		
		
		XHR.appendData('enable_as_modules','yes');	
		XHR.sendAndLoad('$page', 'GET',X_ApplyKasSpamas);	
		
	
	}";
	return  $html;
	
}



function antispam_popup(){
	if(GET_CACHED(__FILE__,__FUNCTION__)){return false;}
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	$sock=new sockets();
	$EnableArticaSMTPFilter=$sock->GET_INFO("EnableArticaSMTPFilter");
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	$amavis=Paragraphe_switch_disable('{enable_amavis}','{feature_not_installed}','{feature_not_installed}');
	$assp=Paragraphe_switch_disable('{enable_assp}','{feature_not_installed}','{feature_not_installed}');
	
	


	
	if($users->SPAMASS_MILTER_INSTALLED){
		$spamassin=Paragraphe_switch_img('{enable_spamasssin}','{enable_spamasssin_text}','enable_spamassassin',$users->SpamAssMilterEnabled,'{enable_disable}',290);
	}else{
		$spamassin=Paragraphe_switch_disable('{enable_spamasssin}','{feature_not_installed}','{feature_not_installed}');	
	}
	
	if($users->AMAVIS_INSTALLED){
		$amavis=Paragraphe_switch_img('{enable_amavis}','{enable_amavis_text}','enable_amavis',$users->EnableAmavisDaemon,'{enable_disable}',290);
		if($users->EnableAmavisDaemon==1){
			$spamassin=Paragraphe_switch_disable('{spamassassin_in_amavis}','{spamassassin_in_amavis_text}','{spamassassin_in_amavis_text}');
		}
	}
	
	$artica=Paragraphe_switch_img('{enable_artica_filter}','{enable_artica_filter_text}','EnableArticaSMTPFilter',$EnableArticaSMTPFilter,'{enable_disable}',290);
	
	
	if($users->ASSP_INSTALLED){
		$sock=new sockets();
		$EnableASSP=$sock->GET_INFO('EnableASSP');
		$assp=Paragraphe_switch_img('{enable_assp}','{enable_assp_text}','EnableASSP',$EnableASSP,'{enable_disable}',290);
	}
	
	
	if($users->MILTERGREYLIST_INSTALLED){
		$miltergreylist=Paragraphe_switch_img('{APP_MILTERGREYLIST}','{enable_miltergreylist_text}','MilterGreyListEnabled',$users->MilterGreyListEnabled,'{enable_disable}',290);
		
	}else{
		$miltergreylist=Paragraphe_switch_disable('{APP_MILTERGREYLIST}','{feature_not_installed}','{feature_not_installed}');
	}
	
	
	if($users->KAV_MILTER_INSTALLED){
		$kavmilter=Paragraphe_switch_img('{APP_KAVMILTER}','{enable_kavmilter_text}','kavmilterEnable',$users->KAVMILTER_ENABLED,'{enable_disable}',290);
		
	}else{
		$kavmilter=Paragraphe_switch_disable('{APP_KAVMILTER}','{feature_not_installed}','{feature_not_installed}');
	}	
	
	
	
	if($users->kas_installed){
		$kaspersky=Paragraphe_switch_img('{enable_kaspersky_as}','{enable_kaspersky_as_text}','enable_kaspersky_as',$users->KasxFilterEnabled,'{enable_disable}',290);
		
	}else{
		$kaspersky=Paragraphe_switch_disable('{enable_kaspersky_as}','{feature_not_installed}','{feature_not_installed}');	
	}
	
	if(!$users->MEM_HIGER_1G){
		$amavis=Paragraphe_switch_disable('{enable_amavis}','{ressources_insuffisantes}','{ressources_insuffisantes}');
		$spamassin=Paragraphe_switch_disable('{enable_spamasssin}','{ressources_insuffisantes}','{ressources_insuffisantes}');	
	}
	
	if($EnablePostfixMultiInstance==1){
		$amavis=Paragraphe_switch_disable('{enable_amavis}','{feature_disabled_multiple_postfix_instances_enabled}','{ressources_insuffisantes}');
		$spamassin=Paragraphe_switch_disable('{enable_spamasssin}','{feature_disabled_multiple_postfix_instances_enabled}','{feature_disabled_multiple_postfix_instances_enabled}');
		$miltergreylist=Paragraphe_switch_disable('{APP_MILTERGREYLIST}','{feature_disabled_multiple_postfix_instances_enabled}','{feature_disabled_multiple_postfix_instances_enabled}');
		$assp=Paragraphe_switch_disable('{enable_assp}','{feature_disabled_multiple_postfix_instances_enabled}','{feature_disabled_multiple_postfix_instances_enabled}');
	}	
	
	if($users->KASPERSKY_SMTP_APPLIANCE){
		$amavis=null;
		$spamassin=null;
		$assp=null;
	}
	
	$html="
	<H1>{AS_ACTIVATE_TEXT}</H1>
	<div style='width:100%;height:400px;overflow:auto'>
	<table style='width:100%'>
	<tr>

	<td valign='top' width=50%>
		$kaspersky<br>$kavmilter<br>$amavis<br>$spamassin
	</td>
	<td valign='top' width=50%>
		$miltergreylist<br>$assp<br>$artica
	</td>
	</tr>
	<tr>
	</table>
	</div>
	<div style='width:100%;text-align:right'><hr>". button("{edit}","ApplyKasSpamas()")."</div>
		
		
	
	
	";
	
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html,'postfix.plugins.php');
	SET_CACHED(__FILE__,__FUNCTION__,null,$html);
	echo $html;
}



function sasl_popup(){
	
	$ldap=new clladp();
	$main=new smtpd_restrictions();
	
	$sock=new sockets();
	$PostfixEnableSubmission=$sock->GET_INFO("PostfixEnableSubmission");
	
	$enabled=1;
	if(!$main->IfRestrictionsKeysExists("smtpd_recipient_restrictions",array("permit_mynetworks","permit_sasl_authenticated"))){
		$enabled=0;
	}
	
   $maincf=new main_cf();
   
   writepostfixlogs("smtpd_sasl_auth_enable=".$maincf->main_array["smtpd_sasl_auth_enable"],__FUNCTION__,__FILE__);
   
   if($maincf->main_array["smtpd_sasl_auth_enable"]<>"yes"){
   	$enabled=0;
   }
   

	$sasl=Paragraphe_switch_img('{sasl_title}','{sasl_intro}','enable_auth',$enabled,'{enable_disable}',390);
	$settings=Paragraphe("rouage-64.png","{advanced_options}","{advanced_options}","javascript:SasladvOptions()");
	
	$smtpd_sasl_exceptions_networks=Paragraphe("64-white-computer.png",
	"{smtpd_sasl_exceptions_networks}","{smtpd_sasl_exceptions_networks_text}","javascript:Loadjs('smtpd_sasl_exceptions_networks.php')");
	
	
	$PostfixEnableSubmission_field=Paragraphe_switch_img('{PostfixEnableSubmission}','{PostfixEnableSubmission_text}','PostfixEnableSubmission',$PostfixEnableSubmission,'{enable_disable}',390);
	
	
	
$html="
	<H1>{sasl_title}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			$sasl
			<hr>$PostfixEnableSubmission_field
			<div style='text-align:right'>
			<hr>". button("{edit}","enable_auth()"). "
			</div>
		</td>
	<td valign='top'>
		" . Paragraphe("64-settings-black.png","{SASL_STATUS}","{SASL_STATUS_TEXT}","javascript:SaslStatus();")."
			$settings
			$smtpd_sasl_exceptions_networks
	</td>
	</tr>
	</table>
	";



	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'postfix.sasl.php');	
}


function sasl_satus(){
	
	$sock=new sockets();
	$tbl=unserialize(base64_decode($sock->getFrameWork("cmd.php?sasl-finger=yes")));
	
	
	while (list ($num, $ligne) = each ($tbl)){
		if(trim($ligne)==null){continue;}
		
		if(preg_match("#^--\s+(.+?)\s+--#",$ligne,$re)){
			$t=$t."<div style='font-size:13px;font-weight:bold;padding:3px;margin-bottom:3px;margin-top:5px;border-bottom:1px solid #CCCCCC'>{$re[1]}</div>";
			continue;
		}
		$ligne=str_replace(" ","&nbsp;",$ligne);
		$ligne=str_replace("\t","<span style='padding-left:40px;'>&nbsp;</span>",$ligne);
		$t=$t."<div><code>$ligne</code></div>\n";
		
	}
	
	
	$html="<H1>{SASL_STATUS}</H1>
	<p class=caption>{SASL_STATUS_TEXT}</p>
	" . RoundedLightWhite("<div style='width:100%;height:300px;overflow:auto'>$t</div>")."";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function sasl_adv(){
	
	$main=new main_cf();
	$smtpd_sasl_security_options_ARR=array(
		"noplaintext"=>"noplaintext",
		"noactive"=>"noactive",
		"nodictionary"=>"nodictionary",
		"mutual_auth"=>"mutual_auth"

	);
	
	
	$smtpd_tls_security_level_ARR=array("none"=>"none","may"=>"may","encrypt"=>"encrypt");
	
	$tpl=new templates();
	$smtpd_sasl_authenticated_header=$tpl->_ENGINE_parse_body("{smtpd_sasl_authenticated_header}");
	$smtpd_tls_auth_only=$tpl->_ENGINE_parse_body("{smtpd_tls_auth_only}");
	$smtpd_tls_received_header=$tpl->_ENGINE_parse_body("{smtpd_tls_received_header}");
	
	if(strlen($smtpd_sasl_authenticated_header)>25){
		$smtpd_sasl_authenticated_header=texttooltip(substr($smtpd_sasl_authenticated_header,0,25)."...",$smtpd_sasl_authenticated_header);
	}
	
	if(strlen($smtpd_tls_auth_only)>25){
		$smtpd_tls_auth_only=texttooltip(substr($smtpd_tls_auth_only,0,25)."...",$smtpd_tls_auth_only);
	}	
	
	if(strlen($smtpd_tls_received_header)>25){
		$smtpd_tls_received_header=texttooltip(substr($smtpd_tls_received_header,0,25)."...",$smtpd_tls_received_header);
	}		
	
	$html="
	<div id='sasl_adv_options'>
	<table class=table_form style='width:100%'>
	<tr>
		<td valign='top' class=legend nowrap>{broken_sasl_auth_clients}:</td>
		<td valign='top'>".Field_yesno_checkbox("broken_sasl_auth_clients",$main->broken_sasl_auth_clients)."</td>
		<td valign='top'>". help_icon('{broken_sasl_auth_clients_text}')."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>$smtpd_tls_auth_only</td>
		<td valign='top'>".Field_yesno_checkbox("smtpd_tls_auth_only",$main->smtpd_tls_auth_only)."</td>
		<td valign='top'>". help_icon('{smtpd_tls_auth_only_text}')."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{smtpd_sasl_local_domain}:</td>
		<td valign='top'>".Field_text("smtpd_sasl_local_domain",$main->smtpd_sasl_local_domain)."</td>
		<td valign='top'>". help_icon('{smtpd_sasl_local_domain_text}')."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend nowrap>$smtpd_sasl_authenticated_header</td>
		<td valign='top'>".Field_yesno_checkbox("smtpd_sasl_authenticated_header",$main->smtpd_sasl_authenticated_header)."</td>
		<td valign='top'>&nbsp;</td>
	</tr>
	<tr>
		<td valign='top' class=legend nowrap>$smtpd_tls_received_header</td>
		<td valign='top'>".Field_yesno_checkbox("smtpd_tls_received_header",$main->smtpd_tls_received_header)."</td>
		<td valign='top'>". help_icon('{smtpd_tls_received_header_text}')."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{smtpd_tls_security_level}:</td>
		<td valign='top'>".Field_array_Hash($smtpd_tls_security_level_ARR,"smtpd_tls_security_level",$main->smtpd_tls_security_level)."</td>
		<td valign='top'>". help_icon('{smtpd_tls_security_level_text}')."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{smtpd_sasl_security_options}:</td>
		<td valign='top'>".Field_array_Hash($smtpd_sasl_security_options_ARR,"smtpd_sasl_security_options",$main->smtpd_sasl_security_options)."</td>
		<td valign='top'>". help_icon('{smtpd_sasl_security_options_text}')."</td>
	</tr>		
	
	
	<tr>
		<td colspan=3 align='right'>". button("{edit}","SaveSaslAdvOptions()")."</td>
	</tr>
	</table>
	</div>
	<script>
	
	var X_SaveSaslAdvOptions= function (obj) {
		var results=obj.responseText;
		YahooWin3Hide();
		}	
	
	function SaveSaslAdvOptions(){
		var XHR = new XHRConnection();
		XHR.appendData('broken_sasl_auth_clients',document.getElementById('broken_sasl_auth_clients').value);
		XHR.appendData('smtpd_tls_auth_only',document.getElementById('smtpd_tls_auth_only').value);
		XHR.appendData('smtpd_sasl_local_domain',document.getElementById('smtpd_sasl_local_domain').value);
		XHR.appendData('smtpd_sasl_authenticated_header',document.getElementById('smtpd_sasl_authenticated_header').value);
		XHR.appendData('smtpd_tls_received_header',document.getElementById('smtpd_tls_received_header').value);
		XHR.appendData('smtpd_tls_security_level',document.getElementById('smtpd_tls_security_level').value);
		XHR.appendData('smtpd_sasl_security_options',document.getElementById('smtpd_sasl_security_options').value);
		document.getElementById('sasl_adv_options').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_SaveSaslAdvOptions);	
	
	}	
	</script>
	";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function  sasl_adv_save(){
	$sock=new sockets();
	while (list ($num, $ligne) = each ($_GET) ){
		$sock->SET_INFO($num,$ligne);
	}
	
	$sock->getFrameWork("cmd.php?reconfigure-postfix=yes");
}


function sasl_save(){
	
	
	if($_GET["PostfixEnableSubmission"]==1){$_GET["save_auth"]=1;}
	
	
	$main=new main_cf();
	$socks=new sockets();
	$socks->SET_INFO('PostfixEnableSubmission',$_GET["PostfixEnableSubmission"]);
	
	
	if($_GET["save_auth"]==1){
		$main->main_array["smtpd_sasl_auth_enable"]="yes";
		$main->main_array["smtpd_use_tls"]= "yes";
		$main->main_array["smtpd_sasl_path"]="smtpd";
		$main->main_array["smtpd_tls_session_cache_database"]="btree:\$queue_directory/smtpd_tls_cache ";
		if($main->main_array["smtpd_tls_key_file"]==null){$main->main_array["smtpd_tls_key_file"]="/etc/ssl/certs/postfix/ca.key";}
		if($main->main_array["smtpd_tls_cert_file"]==null){$main->main_array["smtpd_tls_cert_file"]="/etc/ssl/certs/postfix/ca.crt";}		
		if($main->main_array["smtpd_tls_CAfile"]==null){$main->main_array["smtpd_tls_CAfile"]="/etc/ssl/certs/postfix/ca.csr";}			
		if($main->main_array["smtpd_tls_session_cache_timeout"]==null){$main->main_array["smtpd_tls_session_cache_timeout"]="3600s";}	
		$socks->SET_INFO('PostFixSmtpSaslEnable','1');
		$mainr=new smtpd_restrictions();
		$mainr->AddKey("permit_mynetworks","smtpd_recipient_restrictions");
		$mainr->AddKey("permit_sasl_authenticated","smtpd_recipient_restrictions");
		$mainr->AddKey("reject_unauth_destination","smtpd_recipient_restrictions");
		
	}else{
		$main->main_array["smtpd_sasl_auth_enable"]="no";
		$main->main_array["smtpd_use_tls"]= "no";
		$main->main_array["smtpd_tls_auth_only"]= "no";
		$mainr=new smtpd_restrictions();
		$socks->SET_INFO('PostFixSmtpSaslEnable','0');
		$mainr->DeleteKey("permit_sasl_authenticated","smtpd_recipient_restrictions");
	}
	

	
	$main->save_conf();
	$main->save_conf_to_server();

	
}



function milter_behavior_popup(){
	//64-green.png
	$sock=new sockets();
	$PostfixMiltersBehavior=$sock->GET_INFO("PostfixMiltersBehavior");
	$milter=Paragraphe_switch_img('{enable_milter}','{enable_milter_text}','enable_milter',$PostfixMiltersBehavior,'{enable_disable}',290);
	$EnableArticaSMTPFilter=$sock->GET_INFO("EnableArticaSMTPFilter");
	$ArticaFilterMaxProc=$sock->GET_INFO("ArticaFilterMaxProc");
	
	if($ArticaFilterMaxProc==null){$ArticaFilterMaxProc=20;}
	
	$ArticaFilterMaxProc_arr=array("-"=>'{illimited}',"5"=>5,"10"=>10,20=>20,50=>50,100=>10);
	$ArticaFilterMaxProc=Field_array_Hash($ArticaFilterMaxProc_arr,"ArticaFilterMaxProc",$ArticaFilterMaxProc);
	
		$articafilter_form="
		<table style='width:100%'>
		<tr>
			<td class=legend>{ArticaFilterMaxProc}:</td>
			<td>$ArticaFilterMaxProc</td>
		</tr>
		<tr>
			<td colspan=2 align='right'><hr>". button("{edit}","ApplyMilterBehavior()")."</td>
		</tr>
		</table>
		";
		
	$articafilter_form=RoundedLightWhite($articafilter_form);
	
	$html="
	<H1>{plugins_behavior}</H1>
	<p class=caption>{plugins_behavior_text}</p>
	<table style='width:100%'>
	<tr>

	<td valign='top' width=50%>
		$milter
	</td>
	<td valign='top' width=50% style='margin:4px'>
		" . applysettingsGeneral('apply','ApplyMilterBehavior()','apply_milter_behavior')."
	
	</td>	
	</tr>
	</table>
	<br>$articafilter_form
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'postfix.plugins.php');
}

function backup_popup(){
	$sock=new sockets();
	$users=new usersMenus();
	$enable_amavis=$sock->GET_INFO("EnableAmavisDaemon");
	$enable_assp=$sock->GET_INFO("EnableASSP");
	
	if($users->ASSP_INSTALLED){
		if($enable_assp==1){
			echo "<script>
					Loadjs('assp.php?script-backup=yes');
					YahooWin2Hide();
				</script>
				";
			exit;		
		}
	}
	
	
	if($users->AMAVIS_INSTALLED){
		if($enable_amavis==1){
			echo "<script>
					Loadjs('amavis.index.php?script=backup');
					YahooWin2Hide();
				</script>
				";
			exit;
		}
	}
	
	
	
	
	
	$milter=Paragraphe_switch_img('{enable_APP_MAILARCHIVER}',
	'{enable_APP_MAILARCHIVER_text}','enable_archiver',$sock->GET_INFO("MailArchiverEnabled"),'{enable_disable}',450);

	$html="
	<H1>{backupemail_behavior}</H1>
	$milter
	<div style='text-align:right;width:100%'>". button("{apply}","ApplyBackupBehavior()")."</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'postfix.plugins.php');	
	}

function antispam_popup_save(){
	$artica=new artica_general();
	$sock=new sockets();
	$tpl=new templates();
	if(isset($_GET["enable_kaspersky_as"])){
		echo $tpl->_ENGINE_parse_body("{APP_KAS3}: {$_GET["enable_kaspersky_as"]}\n");
		$sock->SET_INFO("KasxFilterEnabled",$_GET["enable_kaspersky_as"]);
	}
	
	if(isset($_GET["enable_amavis"])){
		$sock->SET_INFO('EnableAmavisDaemon',$_GET["enable_amavis"]);
		echo $tpl->_ENGINE_parse_body("{APP_AMAVISD_NEW}: {$_GET["enable_amavis"]}\n");
		}
	
	if(isset($_GET["MilterGreyListEnabled"])){
		echo $tpl->_ENGINE_parse_body("{APP_MILTERGREYLIST}: {$_GET["MilterGreyListEnabled"]}\n");
		$sock->SET_INFO("MilterGreyListEnabled",$_GET["MilterGreyListEnabled"]);
		}
	
	if(isset($_GET["enable_spamassassin"])){
		echo $tpl->_ENGINE_parse_body("{APP_SPAMASSASSIN}: {$_GET["enable_spamassassin"]}\n");
		$sock->SET_INFO("SpamAssMilterEnabled",$_GET["enable_spamassassin"]);
		}	
		
	if(isset($_GET["EnableASSP"])){
		echo $tpl->_ENGINE_parse_body("{APP_ASSP}: {$_GET["EnableASSP"]}\n");
		$sock->SET_INFO("EnableASSP",$_GET["EnableASSP"]);
		$sock->getFrameWork("cmd.php?restart-assp=yes");
	}
	
	if(isset($_GET["kavmilterEnable"])){
		echo $tpl->_ENGINE_parse_body("{APP_KAVMILTER}: {$_GET["kavmilterEnable"]}\n");
		$sock->SET_INFO("kavmilterEnable",$_GET["kavmilterEnable"]);
	}
	
	$sock->SET_INFO("EnableArticaSMTPFilter",$_GET["EnableArticaSMTPFilter"]);
	
	$users=new usersMenus();
	if(!$users->MEM_HIGER_1G){
		$sock->SET_INFO('EnableAmavisDaemon',0);
		$sock->SET_INFO('SpamAssMilterEnabled',0);
	}
	
	$sock->getFrameWork("cmd.php?SaveMaincf=yes");
	

}

function filters_connect_section(){
	
	$sock=new sockets();
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	$users=new usersMenus();
	$users->LoadModulesEnabled();		
	
	if($users->DkimFilterEnabled==1){
		$dkim=Paragraphe('folder-64-certified.png','{APP_DKIM_FILTER}','{dkim_filter}','dkim.index.php',null,210,null,0,true);
	}
	
	$miltergreylist=Buildicon64("DEF_ICO_MAIL_MGLIST");
	$policydweight=Buildicon64("DEF_ICO_MAIL_POLICYDWEIGHT");
	$block_domain=Buildicon64('DEF_ICO_MAIL_BLOCKDOM');	
	$whitelist=Buildicon64("DEF_ICO_POSTFIX_WHITELIST");
	
		$tr[]=$miltergreylist;
		$tr[]=$policydweight;
		$tr[]=$dkim;
		$tr[]=$block_domain;
		$tr[]=$whitelist;

	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		}

if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}	
	
	
$html="<div style='width:700px'>". implode("\n",$tables)."</div>";

	$tpl=new templates();
	$datas=$tpl->_ENGINE_parse_body($html,"postfix.plugins.php");	
	return $datas;
	
}

function filters_section_kaspersky(){
	
	if(posix_getuid()==0){return null;}
	
	$page=CurrentPageName();
	$sock=new sockets();
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	$users=new usersMenus();
	$users->LoadModulesEnabled();
		
	
	
	$kas3=Paragraphe('folder-caterpillar.png','{APP_KAS3}','{KAS3_TEXT}','javascript:Loadjs("kas.group.rules.php?ajax=yes")',null,210,null,0,true);
	$kasper=Paragraphe('icon-antivirus-64.png','{APP_KAVMILTER}','{APP_KAVMILTER_TEXT}',"javascript:Loadjs('milter.index.php?ajax=yes')",null,210,null,0,true);
	$activate=Paragraphe('64-folder-install.png','{AS_ACTIVATE}','{AS_ACTIVATE_TEXT}',"javascript:Loadjs('$page?script=antispam')",null,210,null,0,true);	
	$mailspy=Paragraphe('64-milterspy.png','{APP_MAILSPY}','{APP_MAILSPY_TEXT}','mailspy.index.php',null,210,100,0,true);
	$install=Buildicon64("DEF_ICO_CONTROLCENTER");
	$milter_script=Paragraphe('64-milter-behavior.png','{plugins_behavior}','{plugins_behavior_text}',"javascript:Loadjs('$page?script=milterbehavior')",null,210,100,0,true);	
	$wbl=Buildicon64('DEF_ICO_MAIL_WBL');
	$quarantine=Paragraphe('folder-quarantine-0-64.png','{quarantine_and_backup_storage}','{quarantine_and_backup_storage_text}',"javascript:Loadjs('quarantine.php?script=quarantine')",null,210,100,0,true);
	$apply=applysettings_postfix(true) ;	
	$assp=Buildicon64("DEF_ICO_ASSP");
	$quarantine_admin=Paragraphe("64-banned-regex.png","{all_quarantines}","{all_quarantines_text}","javascript:Loadjs('domains.quarantine.php?js=yes&Master=yes')",null,210,100,0,true);
	$quarantine_report=Paragraphe("64-administrative-tools.png","{quarantine_reports}","{quarantine_reports_text}","javascript:Loadjs('domains.quarantine.php?js=yes&MailSettings=yes')",null,210,100,0,true);	
	
	
	if($users->KasxFilterEnabled<>1){$kas3=null;}
	if($users->kas_installed<>1){$kas3=null;}
	if(!$users->KAV_MILTER_INSTALLED){$kasper=null;}
	if($users->KAVMILTER_ENABLED<>1){$kasper=null;}
	if($users->KasxFilterEnabled<>1){$kas3=null;}
	if($users->kas_installed<>1){$kas3=null;}
	if($users->KAVMILTER_ENABLED<>1){$kav=null;}
	if(!$users->KAV_MILTER_INSTALLED){$kav=null;}
	if($users->MilterGreyListEnabled<>1){$mg=nul;}
	if(!$users->MILTERGREYLIST_INSTALLED){$mg=null;}
	if($EnablePostfixMultiInstance==1){$mg=null;}
	if($users->EnableMilterSpyDaemon<>1){$mailspy=null;}
	if(!$users->MILTER_SPY_INSTALLED){$mailspy=null;}
	
	
	$tr[]=$apply;
	$tr[]=$activate;
	$tr[]=$milter_script;
	$tr[]=$kas3;
	$tr[]=$assp;
	$tr[]=$kasper;
	$tr[]=$quarantine;
	$tr[]=$quarantine_admin;
	$tr[]=$quarantine_report;
	$tr[]=$wbl;
	$tr[]=$mailspy;

	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	
	
	$html=implode("\n",$tables);
$html="
	<div style='width:700px'>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$apply</td>
		<td valign='top'>$activate</td>
		<td valign='top'>$kas3</td>
	</tr>
	<tr>
		<td valign='top'>$kasper</td>
		<td valign='top'>$quarantine</td>	
		<td valign='top'>$quarantine_admin</td>			
	</tr>
	
	<tr>
		
		<td valign='top'>$quarantine_report</td>
		<td valign='top'>$wbl</td>
		<td valign='top'>$mailspy</td>		

	</tr>
	</table>

</div>";	


	$tpl=new templates();
	$datas=$tpl->_ENGINE_parse_body($html,"postfix.plugins.php,domain.manage.org.index.php,domains.quarantine.php");	
	return $datas;
}



function filters_section(){
	
	if(posix_getuid()==0){return null;}
	
	$page=CurrentPageName();
	$sock=new sockets();
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	$users=new usersMenus();
	$users->LoadModulesEnabled();	
	if($users->KASPERSKY_SMTP_APPLIANCE){
		return filters_section_kaspersky();
		
	}
	
	$spamassassin=Paragraphe('folder-64-spamassassin.png','{APP_SPAMASSASSIN}','{SPAMASSASSIN_TEXT}',"javascript:Loadjs('spamassassin.index.php')",null,210,null,0,true);
	$kas3=Paragraphe('folder-caterpillar.png','{APP_KAS3}','{KAS3_TEXT}','javascript:Loadjs("kas.group.rules.php?ajax=yes")',null,210,null,0,true);
	$kasper=Paragraphe('icon-antivirus-64.png','{APP_KAVMILTER}','{APP_KAVMILTER_TEXT}',"javascript:Loadjs('milter.index.php?ajax=yes')",null,210,null,0,true);
	$activate=Paragraphe('64-folder-install.png','{AS_ACTIVATE}','{AS_ACTIVATE_TEXT}',"javascript:Loadjs('$page?script=antispam')",null,210,null,0,true);
	$amavis=Paragraphe('64-amavis.png','{APP_AMAVISD_NEW}','{APP_AMAVISD_NEW_ICON_TEXT}',"javascript:Loadjs('amavis.index.php?ajax=yes')",null,210,100,0,true);
	$mimedefang=Paragraphe('folder-64-mimedefang.png','{APP_MIMEDEFANG}','{MIMEDEFANG_TEXT}','mimedefang.index.php',null,210,100,0,true);
	$mailspy=Paragraphe('64-milterspy.png','{APP_MAILSPY}','{APP_MAILSPY_TEXT}','mailspy.index.php',null,210,100,0,true);	
	$install=Buildicon64("DEF_ICO_CONTROLCENTER");
	$milter_script=Paragraphe('64-milter-behavior.png','{plugins_behavior}','{plugins_behavior_text}',"javascript:Loadjs('$page?script=milterbehavior')",null,210,100,0,true);
	$plugins_activate=Paragraphe('folder-lego.png','{postfix_plugins}','{postfix_plugins_text}',"javascript:Loadjs('postfix.plugins.php?js=yes')",null,210,100,0,true);
	$wbl=Buildicon64('DEF_ICO_MAIL_WBL');
	$quarantine=Paragraphe('folder-quarantine-0-64.png','{quarantine_and_backup_storage}','{quarantine_and_backup_storage_text}',"javascript:Loadjs('quarantine.php?script=quarantine')",null,210,100,0,true);
	$apply=applysettings_postfix(true) ;	
	$assp=Buildicon64("DEF_ICO_ASSP");
	$quarantine_admin=Paragraphe("64-banned-regex.png","{all_quarantines}","{all_quarantines_text}","javascript:Loadjs('domains.quarantine.php?js=yes&Master=yes')",null,210,100,0,true);
	$quarantine_report=Paragraphe("64-administrative-tools.png","{quarantine_reports}","{quarantine_reports_text}","Loadjs('domains.quarantine.php?js=yes&MailSettings=yes')",null,210,100,0,true);	
		
	
	if($users->EnableAmavisDaemon==0){$amavis=null;}
	if(!$users->AMAVIS_INSTALLED){$amavis=null;}		
	if(!$users->spamassassin_installed){$spamassassin=null;}
	if(!$users->MEM_HIGER_1G){$spamassassin=null;}
	if($users->KasxFilterEnabled<>1){$kas3=null;}
	if($users->kas_installed<>1){$kas3=null;}
	if(!$users->KAV_MILTER_INSTALLED){$kasper=null;}
	if($users->KAVMILTER_ENABLED<>1){$kasper=null;}
	if($users->EnableAmavisDaemon==0){$amavis=null;}
	if(!$users->MEM_HIGER_1G){$amavis=null;}
	if(!$users->AMAVIS_INSTALLED){$amavis=null;}	
	if($EnablePostfixMultiInstance==1){$amavis=null;}
	if($users->MimeDefangEnabled<>1){$mimedefang=null;}
	if(!$users->MIMEDEFANG_INSTALLED){$mimedefang=null;}
	if(!$users->spamassassin_installed){$spamassassin=null;}
	if(!$users->spamassassin_installed){$spamassassin=null;}
	if($EnablePostfixMultiInstance==1){$spamassassin=null;}
	if($users->KasxFilterEnabled<>1){$kas3=null;}
	if($users->kas_installed<>1){$kas3=null;}
	if($users->KAVMILTER_ENABLED<>1){$kav=null;}
	if(!$users->KAV_MILTER_INSTALLED){$kav=null;}
	if($users->ClamavMilterEnabled<>1){$clamav=null;}
	if(!$users->CLAMAV_MILTER_INSTALLED){$clamav=null;}
	if($EnablePostfixMultiInstance==1){$clamav=null;}
	if($users->MilterGreyListEnabled<>1){$mg=null;}
	if(!$users->MILTERGREYLIST_INSTALLED){$mg=null;}
	if($EnablePostfixMultiInstance==1){$mg=null;}
	if($users->EnableMilterSpyDaemon<>1){$mailspy=null;}
	if(!$users->MILTER_SPY_INSTALLED){$mailspy=null;}

		$tr[]=$apply;
		$tr[]=$activate;
		$tr[]=$amavis;
		$tr[]=$assp;
		$tr[]=$kas3;	
		$tr[]=$kasper;			
		$tr[]=$spamassassin;
		$tr[]=$quarantine;
		$tr[]=$quarantine_admin;
		$tr[]=$quarantine_report;
		$tr[]=$wbl;
		$tr[]=$clamav;		
		$tr[]=$mailspy;
		$tr[]=$plugins_activate;

	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	
$html=implode("\n",$tables);
	
	
	
$html="<div style='width:700px'>$html</div>";	


	$tpl=new templates();
	$datas=$tpl->_ENGINE_parse_body($html,"postfix.plugins.php,domain.manage.org.index.php,domains.quarantine.php");
	SET_CACHED(__FILE__,__FUNCTION__,null,$datas);
	return $datas;
}


function icon_backup(){
	$sock=new sockets();
	$users=new usersMenus();
	$page=CurrentPageName();
	$backup=Paragraphe('folder-64-backup-grey.png','{backupemail_behavior}','{feature_disabled}',"",null,210,100);
	$backup=Paragraphe('folder-64-backup.png','{backupemail_behavior}','{backupemail_behavior_text}',"javascript:Loadjs('$page?script=backup')",null,210,100,0,true);
	
	if($users->AMAVIS_INSTALLED){
			if($users->EnableAmavisDaemon==1){
				$backup=Paragraphe('folder-64-backup.png','{backupemail_behavior}','{backupemail_behavior_text}',"javascript:Loadjs('amavis.index.php?script=backup')",null,210,100,0,true);
			}
		}
		
	if($users->ASSP_INSTALLED){
		if($sock->GET_INFO("EnableASSP")==1){
			$backup=Paragraphe('folder-64-backup.png','{backupemail_behavior}','{backupemail_behavior_text}',"javascript:Loadjs('assp.php?script-backup=yes')",null,210,100,0,true);
		}
	}

	return $backup;
}



function mailbox_section(){
	$date_start=time();

	
	
$failedtext="{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}";
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	
	$fetchmail=Buildicon64('DEF_ICO_FETCHMAIL');
	$install=Paragraphe('add-remove-64.png','{INSTALL_NEW_PLUGINS}','{INSTALL_NEW_PLUGINS_TEXT}','setup.index.php',null,210,100,0,true);
	$cyrus_cluster=Buildicon64('DEF_ICO_CYR_CLUSTER');
	$cyrus_backup=Buildicon64('DEF_ICO_IMAP_BACKUP');
	$cyrus_connexions=Buildicon64('DEF_ICO_EVENTS_IMAPCON');
	$cyrus_scan=Buildicon64('DEF_ICO_CYRUS_AV');	
	
	if($users->roundcube_installed){
		$roundcube=Paragraphe('64-roundcube.png','{APP_ROUNDCUBE}','{APP_ROUNDCUBE_TEXT}',"javascript:Loadjs('roundcube.index.php?script=yes')",'APP_ROUNDCUBE',210,100,0,true);
	}	
	
	if($users->APP_ATOPENMAIL_INSTALLED){
		$atmail=Paragraphe('64-atmail.png','{APP_ATOPENMAIL}',"{APP_ATOPENMAIL} v$users->APP_ATOPENMAIL_VERSION",'mail/index.php','APP_ATOPENMAIL',210,100,0,true);
	}
	
	if($users->cyrus_imapd_installed){
		if($users->AsMailBoxAdministrator){
			$cyrus=Paragraphe('bg-cyrus-64.png','{APP_CYRUS}','{mange_cyrus_mailbox}',"javascript:Loadjs('cyrus.index.php')",null,210,100,0,true);
			$multimdomains=Paragraphe('folder-org-64.png','{multidomains}','{multidomains_icon_text}',"javascript:Loadjs('postfix.index.php?script=multidomains')",null,210,100,0,true);
			$murder=Buildicon64('DEF_ICO_IMAP_MURDER',210,100);
		}
	}
	
	if($users->ZARAFA_INSTALLED){
		if($users->AsMailBoxAdministrator){
			$zarafaweb=Paragraphe('zarafa-web-64.png','{APP_ZARAFA_WEB}','{APP_ZARAFA_WEB_TEXT}',"javascript:Loadjs('zarafa.web.php')",null,210,100,0,true);
			$cyrus=null;
			$multimdomains=null;
			$murder=null;
			$cyrus_cluster=null;
			$cyrus_backup=null;
		}
	}
	

	

	
		$tr[]=$zarafaweb;
		$tr[]=$cyrus;
		$tr[]=$multimdomains;
		$tr[]=$cyrus_connexions;
		$tr[]=$cyrus_scan;
		$tr[]=$murder;		
		$tr[]=$cyrus_cluster;
		$tr[]=$cyrus_backup;
		$tr[]=$roundcube;
		$tr[]=$fetchmail;
		$tr[]=$atmail;

	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		}

if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	
$html=implode("\n",$tables);	
	
	
$html="<div style='width:700px'>$html</div>";
	
if(posix_getuid()==0){
	@unlink($CacheFile);
	@file_put_contents($CacheFile,$html);
	@chmod($CacheFile,0755);
	$date_end=time();
	$time=distanceOfTimeInWords($date_start,$date_end);		
	//writelogs("Building cache file done... $time",__FUNCTION__,__FILE__,__LINE__);
	return true;
}
	
	$tpl=new templates();
	$datas=$tpl->_ENGINE_parse_body($html,"postfix.plugins.php");	
	return $datas;

}


function Transport_rules(){
	$datas=GET_CACHED(__FILE__,__FUNCTION__,null,TRUE);
	if($datas<>null){return $datas;}
	$sock=new sockets();
	$page=CurrentPageName();
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	$failedtext="{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}";
	$network=Buildicon64('DEF_ICO_POSTFIX_NETWORK');
	$transport=Buildicon64('DEF_ICO_POSTFIX_TRANSPORT');
	$applysettings=Buildicon64('DEF_ICO_POSTFIX_APPLY');
	$queue=Buildicon64('DEF_ICO_POSTFIX_QUEUE');
	$relayhost=Buildicon64('DEF_ICO_POSTFIX_RELAYHOST');
	$relayhostssl=Buildicon64('DEF_ICO_POSTFIX_RELAYHOSTSSL');
	$notifs=Buildicon64('DEF_ICO_POSTFIX_NOTIFS');
	$mailman=Buildicon64('DEF_ICO_POSTFIX_MAILMAN');
	$mailgraph=Buildicon64('DEF_ICO_EVENTS_MAILGRAPH');	
	
	
	if($EnablePostfixMultiInstance==1){
		$queue=null;
		$relayhost=null;
		$relayhostssl=null;
		$orange=null;
		$oleane=null;
		$oneone=null;
		$wanadoo=null;
		$notifs=null;
		$mailman=null;
		$mailgraph=null;
		$applysettings=null;
		
		$applysettings=Paragraphe("org-smtp-settings-64.png","{OU_BIND_ADDR_AFFECT}","{OU_BIND_ADDR_AFFECT_TEXT}","javascript:Loadjs('system.nic.config.php?postfix-virtual=yes')");
	}
	
	
$html="
<div style='width:700px'>
<table style='width:100%'>
<tr>
<td valing='top'>$network</td>
<td valing='top'>$transport</td>
<td valing='top'>$applysettings</td>
</tr>
<tr>
<td valing='top'>$queue</td>
<td valing='top'>$mailgraph</td>
<td valing='top'>$relayhost</td>
</tr>
<tr>
<td valing='top'>$relayhostssl</td>
<td valing='top'>$notifs</td>
<td valing='top'>$mailman</td>
</tr>
</table>
</div>
";


$tpl=new templates();
$datas=$tpl->_ENGINE_parse_body($html);
SET_CACHED(__FILE__,__FUNCTION__,null,$datas);
return $datas;
}


function tweaks(){
	$datas=GET_CACHED(__FILE__,__FUNCTION__,null,TRUE);
	if($datas<>null){return $datas;}
	
	$users=new usersMenus();
	$page=CurrentPageName();
	$tpl=new templates();
	
	if($users->ALTERMIME_INSTALLED){
		$altermime=Buildicon64("DEF_ICO_ALTERMIME");
	}
	
	
	if($users->POMMO_INSTALLED){
		$pommo=Paragraphe('64-pommo.png','{APP_POMMO}','{APP_POMMO_TEXT}',"javascript:Loadjs('pommo.index.php?pommo-js=yes')",null,210,100,0,true);	
	}
	
	
	if($users->zip_installed){
		if($users->AMAVIS_INSTALLED){
			if($users->EnableAmavisDaemon==1){
				$winzip=Paragraphe('64-winzip.png','{auto-compress}','{auto-compress_text}',"javascript:Loadjs('auto-compress.php?script=winzip')",null,210,100,0,true);	
			}
		}
	}	
	
	
	
	
	
	$postfix_restrictions_classes=Paragraphe('folder-64-restrictions-classes.png',
	'{postfix_restrictions_classes}','{restriction_classes_minitext}',"javascript:Loadjs('postfix.restrictions.classes.php?js=yes')",null,210,null,0,true);
	
	$events=Buildicon64("DEF_ICO_EVENTS_POSTFIX");
	$performances=Paragraphe('folder-performances-64.png','{performances_settings}','{performances_settings_text}',"javascript:Loadjs('postfix.performances.php')");
	$maincf=Paragraphe('folder-script-64.png','{main.cf}','{main.cf_explain}','postfix.main.cf.php');
	$storage=Paragraphe('folder-storage2-64.png','{storage_rules}','{storage_rules_text}',"javascript:Loadjs('postfix.storage.rules.php')");
	$maincfedit=Paragraphe('folder-maincf-64.png','{main.cf_edit}','{main.cfedit_explain}',"javascript:Loadjs('postfix.main.cf.edit.php?js=yes')");
	$mastercf=Paragraphe('folder-script-64-master.png','{master.cf}','{mastercf_explain}',"javascript:Loadjs('postfix.master.cf.php?script=yes')") ;
	$other=Paragraphe('folder-tools2-64.png','{other_settings}','{other_settings_text}',"javascript:Loadjs('postfix.other.php')");
	$main_src=Paragraphe('folder-script-database-64.png','{main_ldap}','{main_ldap_explain}',"javascript:s_PopUp(\"postfix.report.php\",500,500,true)");
		$tr[]=$altermime;
		$tr[]=$pommo;
		$tr[]=$events;
		$tr[]=$performances;
		$tr[]=$maincf;
		$tr[]=$main_src;
		$tr[]=$maincfedit;
		$tr[]=$mastercf;
		$tr[]=$storage;	
		$tr[]=$postfix_restrictions_classes;	
		$tr[]=$other;
		$tr[]=$cyrus_backup;
		$tr[]=$roundcube;
		$tr[]=$fetchmail;
		$tr[]=$atmail;

	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		}

if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	
$html=implode("\n",$tables);	
	
	
$html="<div style='width:700px'>$html</div>";	
$datas=$tpl->_ENGINE_parse_body($html);	
SET_CACHED(__FILE__,__FUNCTION__,null,$datas);
return $datas;
}

function security(){
		
	$datas=GET_CACHED(__FILE__,__FUNCTION__,null,TRUE);
	if($datas<>null){return $datas;}
	

	$failedtext="{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}";
	$page=CurrentPageName();
	$users=new usersMenus();
	$users->LoadModulesEnabled();

	
	$tls=Buildicon64("DEF_ICO_POSTFIX_TLS");
	$security_rules=Paragraphe('folder-rules-64.png','{security_rules}','{security_rules_text}',"javascript:Loadjs('postfix.security.rules.php?js=yes')",null,210,null,0,true);
	$apply=applysettings_postfix(true);
	
	$messages_restriction=Paragraphe('folder-message-restriction.png',
	'{messages_restriction}','{messages_restriction_text}',"javascript:Loadjs('postfix.messages.restriction.php?script=yes')",null,210,null,0,true);
	
	$sasl=Paragraphe('64-smtp-auth.png','{SASL_TITLE}','{SASL_TEXT}',"javascript:Loadjs('postfix.index.php?script=auth');",null,210,null,0,true);
	
	$internet_deny=Paragraphe('64-internet-deny.png','{INTERNET_DENY}','{INTERNET_DENY_TEXT}',"javascript:Loadjs('postfix.internet.deny.php')",null,210,100,0,true);	
	
	$ssl=Paragraphe('folder-64-routing-secure.png','{SSL_ENABLE}','{SSL_ENABLE_TEXT}',"javascript:Loadjs('postfix.master.cf.php?script=ssl');",null,210,null,0,true);
	
	
	$global_smtp_rules=Buildicon64('DEF_ICO_POSTFIX_REGEX');
	
	
	$smtpd_client_restrictions=Paragraphe('64-sender-check.png','{smtpd_client_restrictions_icon}','{smtpd_client_restrictions_icon_text}',
	"javascript:Loadjs('postfix.smtpd_client_restrictions.php')",null,210,null,0,true);
	
	$ou_encoded=base64_encode("_Global");
	$extensions_block=Paragraphe("bg_forbiden-attachmt-64.png","{attachment_blocking}","{attachment_blocking_text}","javascript:Loadjs('domains.edit.attachblocking.ou.php?ou=$ou_encoded')",null,210,null,0,true);

		
	$plugins_activate=Paragraphe('folder-lego.png','{POSTFIX_BUNDLE}','{POSTFIX_BUNDLE_TEXT}',"javascript:Loadjs('postfix.plugins.php?script=yes')",null,210,100,0,true);
	$postfixInstantIptables=Buildicon64("DEF_ICO_MAIL_IPABLES");
	$backup=icon_backup();
	

$html="
<div style='width:700px'>
<table style='width:100%'>
<tr>
<td valign='top'>$backup</td>
<td valign='top'>$tls</td>
<td valign='top'>$security_rules</td>
</tr>
<tr>
<td valign='top'>$apply</td>
<td valign='top'>$messages_restriction</td>
<td valign='top'>$smtpd_client_restrictions</td>
</tr>
<tr>
<td valign='top'>$sasl</td>
<td valign='top'>$ssl</td>
<td valign='top'>$internet_deny</td>
</tr>
<tr>
<td valign='top'>$global_smtp_rules</td>
<td valign='top'>$postfixInstantIptables</td>
<td valign='top'>$extensions_block</td>
</td>
</table>
</div>
";
$tpl=new templates();
$datas=$tpl->_ENGINE_parse_body($html);	
SET_CACHED(__FILE__,__FUNCTION__,null,$datas);
return $datas;
}


function events(){
	
	
	
$html="
		<table>
			<tr>
			<td valign='top' >".Paragraphe('folder-logs-64.jpg','{postfix_events}','{postfix_events_text}','postfix.events.php') ."</td>
			<td valign='top' >".Paragraphe('folder-queue-64.jpg','{queue_monitoring}','{queue_monitoring_text}','postfix.queue.monitoring.php') ."</td>
			</tr>
			<tr>
			<td valign='top' >".Paragraphe('folder-message-restriction.jpg','{messages_restriction}','{messages_restriction_text}','postfix.messages.restriction.php') ."</td>
			<td>&nbsp;</td>
			</tr>
			
			
		</table>";	

$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);

}

function main_switch(){
cookies_main();
	if(GET_CACHED(__FILE__,__FUNCTION__,$_GET["main"])){return;}
	
	$array["transport_settings"]='{transport_settings}';
	$array["security_settings"]='{security_settings}';
	$array["tweaks"]='{tweaks}';
	
	if(isset($_GET["ajaxmenu"])){echo "<div id='main_config_postfix'>";}
	
	switch ($_GET["main"]) {
		case "transport_settings":
			$html=Transport_rules();
			SET_CACHED(__FILE__,__FUNCTION__,$_GET["main"],$html);
			echo $html;
			break;			
			
			
		case "security_settings":
			$html=security();
			SET_CACHED(__FILE__,__FUNCTION__,$_GET["main"],$html);
			echo $html;
			break;	

		case "tweaks":
			$html=tweaks();
			SET_CACHED(__FILE__,__FUNCTION__,$_GET["main"],$html);
			echo $html;
			break;
			
		case "filters":
			$html=filters_section();
			SET_CACHED(__FILE__,__FUNCTION__,$_GET["main"],$html);
			echo $html;
			break;

		case "mailbox":
			$html=mailbox_section();
			SET_CACHED(__FILE__,__FUNCTION__,$_GET["main"],$html);
			echo $html;
			break;

		case "status":
			$html=status_section();
			SET_CACHED(__FILE__,__FUNCTION__,$_GET["main"],$html);
			echo $html;
			break;			
	
		case "filters-connect":
			$html=filters_connect_section();
			SET_CACHED(__FILE__,__FUNCTION__,$_GET["main"],$html);
			echo $html;
			break;				

		default:
			$html=Transport_rules();
			SET_CACHED(__FILE__,__FUNCTION__,$_GET["main"],$html);
			echo $html;
			break;	

	}
	
	if(isset($_GET["ajaxmenu"])){echo "</div>";}
}


function main_mastercf(){
	$master=new master_cf();
	$page=CurrentPageName();
	
	$html="<H5>{master.cf}</H5>
	<div style='width:100%;text-align:right'><input type='button' OnClick=\"javascript:ParseForm('ffmmaster','$page',true);\" value='{edit}&nbsp;&raquo;'></div>
	<form name='ffmmaster'>
	<p class=caption>{mastercf_explain}</p>
	<textarea id='master_datas' name='master_datas' cols=100 rows=30>$master->PostfixMasterCfFile</textarea>
	</form>
	
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function SaveMastercf(){
	$master=new master_cf();
	$master->PostfixMasterCfFile=$_GET["master_datas"];
	$master->SaveToLdap();
	}
	
function orangefr_script(){
	$tpl=new templates();
	$page=CurrentPageName();
	$isp=$_GET["isp"];
	$mul=$isp;
	$html="YahooWin2(550,'$page?orangefr=yes&isp=$isp','$mul','');";
return  $html;		
}	

function orangefr_popup(){
	
	$sasl=new smtp_sasl_password_maps();
	$domain=new DomainsTools();
	$ISP=$_GET["isp"];
	$ini=new Bs_IniHandler();
	$ldap=new clladp();
	
	$ini->loadFile("ressources/databases/isp.defaults.settings.conf");
	$default_server=$ini->_params[$ISP]["default_server"];
	$default_port=$ini->_params[$ISP]["default_port"];
	$serverstring=$domain->transport_maps_implode($default_server,$default_port,null,"no");

	
	$auth=$ldap->sasl_relayhost($default_server);
	if($auth<>null){
		if(preg_match('#(.+?):(.+)#',$auth,$re)){
			$username=$re[1];
			$password=$re[2];
		}
	}
	$tpl=new templates();
	$isp_server_address_label=$tpl->_ENGINE_parse_body('{isp_server_address}');
	$isp_server_port_label=$tpl->_ENGINE_parse_body('{isp_server_port}');
	
	if(strlen($isp_server_address_label)>25){$isp_server_address_label=texttooltip(substr($isp_server_address_label,0,22).'...',$isp_server_address_label,null,1);}
	if(strlen($isp_server_port_label)>25){$isp_server_port_label=texttooltip(substr($isp_server_port_label,0,22).'...',$isp_server_port_label,null,1);}
	
	$page=CurrentPageName();
	$text="
	<div id='anim'></div>
	<p class=caption style='font-size:12px'>{please_verify_addressisp}</p>
	<strong style='font-size:13px'>{technical_address}:<code>$serverstring</code></strong><br>
	<form name='FFMISPRELAY'>
	<table style='width:100%;background-color:#FFFFFF;border:1px solid #CCCCCC;padding:5px'>
		<tr>
			<td class=legend nowrap>$isp_server_address_label</td>
			<td>".Field_text('isp_address',$default_server,'width:220px;')."</td>
		</tr>
		<tr>
			<td class=legend nowrap>$isp_server_port_label</td>
			<td>".Field_text('isp_port',$default_port,'width:30px;')."</td>
		</tr>
		<tr>
			<td class=legend nowrap>{username}</td>
			<td>".Field_text('isp_username',$username,'width:190px;')."</td>
		</tr>		
		<tr>
			<td class=legend nowrap>{password}</td>
			<td>".Field_password('isp_password',$password,'width:90px;')."</td>
		</tr>	
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr>
			<td colspan=2 style='padding-top:4px;border-top:1px solid #CCCCCC' align='right'>
			". button("{edit}","FFMISPRELAY_SAVE()")."
				
			</td
		</tr>
	</table>	
	
	";
	
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><img src='img/{$ini->_params[$ISP]["default_icon"]}' style='margin:4px;padding:5px;border:1px solid #7B787E;background-color:white'></td>
		<td valign='top'>$text</td>
	</tr>
	</table>
	<script>		
	var x_FFMISPRELAY_SAVE=function(obj){
    	var tempvalue=obj.responseText;
	  	if(tempvalue.length>0){alert(tempvalue);}
		document.getElementById('anim').innerHTML='';
		}
	
		function FFMISPRELAY_SAVE(){
			var XHR = new XHRConnection(); 
			XHR.appendData('isp_address',document.getElementById('isp_address').value);
			XHR.appendData('isp_password',document.getElementById('isp_password').value);
			XHR.appendData('isp_port',document.getElementById('isp_port').value);
			XHR.appendData('isp_username',document.getElementById('isp_username').value);
			document.getElementById('anim').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_FFMISPRELAY_SAVE);
		}		
		
	</script>
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);
}

function SaveISPAddress(){
	$tpl=new templates();
	$domain=new DomainsTools();
	$page=CurrentPageName();
	$address=$domain->transport_maps_implode($_GET["isp_address"],$_GET["isp_port"]);
	$sasl=new smtp_sasl_password_maps();
	writepostfixlogs("Set ISP server has $address",__FUNCTION__,__FILE__);;
	$sock=new sockets();
	$sock->SET_INFO("PostfixRelayHost","$address");
	
	writepostfixlogs("is this server $address must use authentication ?",__FUNCTION__,__FILE__);
	if(trim($_GET["isp_username"])==null){
		$sasl->delete($address);
		$main->save_conf();
		$main->save_conf_to_server();
		exit;
		
	}
	
	if(trim($_GET["isp_password"])==null){die("password NULL !");}
	
	writepostfixlogs("Enable SMTP Sasl",__FUNCTION__,__FILE__);
	$main=new main_cf();
	$main->smtp_sasl_password_maps_enable();

	
	if(!$sasl->add($address,trim($_GET["isp_username"]),trim($_GET["isp_password"]))){
		die($sasl->ldap_infos);
	}
	
	
	
}


function bar_status(){
	
	$refresh="<div style='width:100%;text-align:right'>". imgtootltip("refresh-24.png","{refresh}","RefreshIndexPostfixAjax()")."</div>";
	/*if(isset($_GET["mode"])){
		if($_GET["mode"]==1){
			echo 
			"<input type='hidden' id='monitor_page_switch' value='0' name='monitor_page_switch'>
			<img src='img/postfix2.jpg'>$refresh";
			return false;
		}
		
		
	}*/
	
	$tpl=new templates();
	if(is_file("ressources/logs/global.status.ini")){
		$ini=new Bs_IniHandler("ressources/logs/global.status.ini");
	}else{
		$sock=new sockets();
		$datas=base64_decode($sock->getFrameWork('cmd.php?Global-Applications-Status=yes'));
		$ini=new Bs_IniHandler($datas);
	}
	
	$sock=new sockets();
	$datas=$sock->getFrameWork('cmd.php?refresh-status=yes');
	
	$array[]="POSTFIX";
	$array[]="ASSP";
	$array[]="AMAVISD";
	$array[]="AMAVISD_MILTER";
	$array[]="SPAMASSASSIN";
	$array[]="SPAMASS_MILTER";
	$array[]="MIMEDEFANG";
	$array[]="MIMEDEFANGX";
	$array[]="DKIM_FILTER";
	$array[]="SPFMILTER";
	$array[]="CLAMAV";
	$array[]="FRESHCLAM";
	$array[]="MAILSPY";	
	$array[]="KAVMILTER";
	$array[]="KAS_MILTER";
	$array[]="KAS3";
	$array[]="MAILARCHIVER";
	$array[]="BOGOM";
	$array[]="MILTER_GREYLIST";
	$array[]="POLICYD_WEIGHT";
	$array[]="MAILMAN";
	$array[]="APP_CYRUS_IMAP";	
	$array[]="MAILARCHIVER";	

	
	$status="<input type='hidden' id='monitor_page_switch' value='1' name='monitor_page_switch'>";
	while (list ($num, $ligne) = each ($array) ){
		$st=DAEMON_STATUS_LINE($ligne,$ini,null,1);
		if($st==null){continue;}
		$status=$status .$st."\n";
	}

	return  $tpl->_ENGINE_parse_body($status.$refresh);
	
}

function emptycache(){
	$sock=new sockets();
	$sock->DeleteCache();
	
}

function status_section(){
	$page=CurrentPageName();
	$main=new main_cf();
	
	$count=0;
	while (list ($num, $ligne) = each ($main->array_mynetworks) ){
		if($ligne=="127.0.0.0/8"){continue;}
		if($ligne=="127.0.0.0/8 [::ffff:127.0.0.0]/104 [::1]/128"){continue;}
		$count=$count+1;
	}
	
	if($count==0){
		$nonet=Paragraphe('server_network_error-64.png',"{NO_POSTFIX_NETWORK_SET}",'{NO_POSTFIX_NETWORK_SET_EXPLAIN}',"javascript:Loadjs('postfix.network.php?ajax=yes')");
	}
	
	
	$prefix="postfix_index_page_php_";
	$html="<input type='hidden' id='monitor_page' value='1' name='monitor_page'>
<table style='width:650px' align=center>
<tr>

<td valign='top'>
	<div id='barstatus' style='width:350px;overflow:auto'>
	<img src='img/postfix2.jpg'>
	</div><hr>$nonet
</td>
<td valign='top'>
<div id='{$prefix}servinfos' style='width:350px'></div>
</td>
</tr>
</table>
<div id='tweaks'></div>
<script>
{$prefix}DisplayDivs();
LoadAjax('tweaks','$page?main=tweaks&hostname=');
</script>
";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}
	
?>	

