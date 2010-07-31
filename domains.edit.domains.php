<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.auto-aliases.inc');
	
	
	if(!VerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	
	if(isset($_GET["remote-domain-add-js"])){remote_domain_js();exit;}
	if(isset($_GET["remote-domain-popup"])){remote_domain_popup();exit;}
	if(isset($_GET["remote-domain-form"])){remote_domain_form();exit;}
	
	if(isset($_GET["organization-local-domain-list"])){echo DOMAINSLIST($_GET["organization-local-domain-list"]);exit;}
	if(isset($_GET["organization-relay-domain-list"])){echo RELAY_DOMAINS_LIST($_GET["organization-relay-domain-list"]);exit;}
	if(isset($_GET["AddNewInternetDomainDomainName"])){AddNewInternetDomain();exit;}
	if(isset($_GET["AddNewRelayDomainName"])){AddNewRelayDomain();exit;}
	if(isset($_GET["DeleteInternetDomain"])){DeleteInternetDomain();exit;}
	if(isset($_GET["EditRelayDomainIP"])){EditRelayDomain();exit();}
	if(isset($_GET["DeleteRelayDomainName"])){DeleteRelayDomainName();exit;}
	if(isset($_GET["LocalDomainList"])){echo DOMAINSLIST($_GET["ou"]);exit;}
	if(isset($_GET["RelayDomainsList"])){echo RELAY_DOMAINS_LIST($_GET["ou"]);exit;}
	if(isset($_GET["EditInfosLocalDomain"])){echo EditInfosLocalDomain();exit;}
	if(isset($_GET["EditLocalDomain"])){EditLocalDomain();exit();}
	if(isset($_GET["duplicate_local_domain"])){COPY_DOMAINS_SAVE();exit;}
	
	if(isset($_GET["js"])){echo js_script();exit;}
	if(isset($_GET["ajax"])){echo js_popup();exit;}
	
	if(isset($_GET["round-robin"])){round_robin_js();exit;}
	if(isset($_GET["roundrobin_ipaddress"])){round_robin_save();exit;}
	if(isset($_GET["round-robin-popup"])){round_robin_popup();exit;}
	if(isset($_GET["round-robin-list"])){echo round_robin_list();exit;}
	if(isset($_GET["round-robin-delete"])){round_robin_delete();exit;}
	INDEX();
	
	
function VerifyRights(){
	$usersmenus=new usersMenus();
	if($usersmenus->AsMessagingOrg){return true;}
	if(!$usersmenus->AllowChangeDomains){return false;}
}
	
function round_robin_js(){
	$page=CurrentPageName();
	//&ou=$ou&domain=$num
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{roundrobin}');
	
	$ou=$_GET["ou"];
	$ou_encrypted=base64_encode($ou);
	$domain=$_GET["domain"];
	
	$html="
	function DomainViewConfig(){
			YahooWin3(600,'$page?round-robin-popup=yes&domain=$domain&ou={$_GET["ou"]}','$title $domain');
		
		}
		
	var x_RoundRobinSave= function (obj) {
		var response=obj.responseText;
		AddRemoteDomain_form('$ou','$domain');
		if(response){alert(response);}
	    LoadAjax('hostDomainList','$page?round-robin-list=&domain=$domain&ou={$_GET["ou"]}');
	}		
	
	
	function RoundRobinSave(){
		var roundrobin_ipaddress=document.getElementById('roundrobin_ipaddress').value;
		var roundrobin_nameserver=document.getElementById('roundrobin_nameserver').value;
		document.getElementById('hostDomainList').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		var XHR = new XHRConnection();
		XHR.appendData('roundrobin_ipaddress',roundrobin_ipaddress);
		XHR.appendData('roundrobin_nameserver',roundrobin_nameserver);
		XHR.appendData('ou','$ou');
		XHR.appendData('domain','$domain');
		XHR.sendAndLoad('$page', 'GET',x_RoundRobinSave);
	}
	
	function  RoundRobinDelete(num){
		var XHR = new XHRConnection();
		XHR.appendData('round-robin-delete',num);
		XHR.appendData('domain','$domain');
		document.getElementById('hostDomainList').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_RoundRobinSave);
	}
		
	DomainViewConfig();
	
	
	
	";
	
	echo $html;
	
	
}

function round_robin_delete(){
	$domain=$_GET["domain"];
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$datas=$sock->GET_INFO('RoundRobinHosts');
	$ini->loadString($datas);
	$ips=explode(",",$ini->_params["$domain"]["IP"]);
	unset($ips[$_GET["round-robin-delete"]]);
	$ini->_params["$domain"]["IP"]=implode(",",$ips);
	$sock->SaveConfigFile($ini->toString(),"RoundRobinHosts");
	$sock->getfile("RoundRobinHosts");
	}

function round_robin_save(){
	
	$ou=$_GET["ou"];
	$tpl=new templates();
	$roundrobin_nameserver=$_GET["roundrobin_nameserver"];
	$roundrobin_ipaddress=$_GET["roundrobin_ipaddress"];
	$domain=$_GET["domain"];
	if(IsIPValid($roundrobin_nameserver)){
		echo $tpl->_ENGINE_parse_body("{servername}:\n$roundrobin_nameserver\n {error_cannot_be_ip_address}");
		exit;
	}
	
	if(!IsIPValid($roundrobin_ipaddress)){
		echo $tpl->_ENGINE_parse_body("{add_ip_address}:\n$roundrobin_ipaddress\n {error_must_be_ip_address}");
		exit;
	}
	
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$datas=$sock->GET_INFO('RoundRobinHosts');
	$ini->loadString($datas);
	$ips=explode(",",$ini->_params["$domain"]["IP"]);
	$ips[]=$roundrobin_ipaddress;
	
	$ini->_params["$domain"]["servername"]=$roundrobin_nameserver;
	$ini->_params["$domain"]["IP"]=implode(",",$ips);
	$sock->SaveConfigFile($ini->toString(),"RoundRobinHosts");
	$sock->getfile("RoundRobinHosts");
	
	$ldap=new clladp();
	$dn="cn=$domain,cn=transport_map,ou=$ou,dc=organizations,$ldap->suffix";
	$upd["transport"][0]="[$roundrobin_nameserver]";
	if(!$ldap->Ldap_modify($dn,$upd)){
		echo $ldap->ldap_last_error;
		exit;				
	}
	
	echo html_entity_decode($tpl->_ENGINE_parse_body('{success}'));
	
	
	
}

function round_robin_list(){
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$datas=$sock->GET_INFO('RoundRobinHosts');
	$ini->loadString($datas);
	$ips=explode(",",$ini->_params[$_GET["domain"]]["IP"]);
	$server=$ini->_params[$_GET["domain"]]["servername"];
	
	$html="<table style='width:100%' class=table_form>";
	while (list ($num, $ligne) = each ($ips) ){
		if(!IsIPValid($ligne)){continue;}
		$html=$html . "<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><strong style='font-size:14px'><code>$server&nbsp;&nbsp;==&nbsp;&nbsp;$ligne</code></strong></td>
			<td width=1%>" . imgtootltip('ed_delete.gif',"{delete}","RoundRobinDelete($num,'{$_GET["domain"]}')")."</td>
			</tr>";
		
	}
	
	$html=$html . "</table>";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}

function round_robin_popup(){
	
$ldap=new clladp();	
$HashDomains=$ldap->Hash_relay_domains($_GET["ou"]);
$tools=new DomainsTools();
$arr=$tools->transport_maps_explode($HashDomains[$_GET["domain"]]);	
$roundrobin_nameserver=$arr[1];
$list=round_robin_list();
	$html="
	<H1>{roundrobin}: {$_GET["domain"]}</H1>
	<img src='img/roundrobin_bg.png' align='right' style='margin:3px'><p class=caption>{roundrobin_text}</p>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>{servername}:</td>
		<td>" . Field_text('roundrobin_nameserver',$roundrobin_nameserver,'width:210px')."</td>
	</tr>
	<tr>
		<td class=legend>{add_ip_address}:</td>
		<td>" . Field_text('roundrobin_ipaddress',null,'width:90px')."</td>
	</tr>
	<tr><td colspan=2 align='right'><hR></td></tr>	
	<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:RoundRobinSave();\" value='{add}&nbsp;&raquo;'></td>
	</tr>
	</table>
	<div id='hostDomainList'>$list</div>	
	
	
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}




	
function INDEX(){
	$ou=$_GET["ou"];
	if($ou==null){$ou=ORGANISTATION_FROM_USER();}
	$title=$ou . ":&nbsp;{groups}";

	
	$add_local_domain=Paragraphe("64-localdomain-add.png",'{add_local_domain}','{add_local_domain_text}',"javascript:AddLocalDomain_form()","add_local_domain",260);
	$add_remote_domain=Paragraphe("64-remotedomain-add.png",'{add_relay_domain}','{add_relay_domain_text}',"javascript:AddRemoteDomain_form(\"$ou\",\"new domain\")","add_relay_domain",260);
	
	
	
	
	$html="
	<table style='width:100%'>
		<tr>
			<td valign='top'><img src='img/bg_dns.jpg'></td>
				<td>
					<p style='font-size:12px'>{introduction}</p>
					<input type='hidden' id='inputbox delete' value=\"{are_you_sure_to_delete}\">
					<input type='hidden' id='add_local_domain_form' value=\"{add_local_domain_form}\">
					<input type='hidden' id='ou' value='$ou'>
					</td>
				</tr>
		
		<tr>
		<td valign='top'>
				<table style='width:100%'>
					<tr>
						<td><H5>{local_domain_map}</h5>
							<div id='LocalDomainsList'></div>
							<p>&nbsp;</p>
							<div id='RelayDomainsList'></div>
						</td>
					</tr>
				</table>
		</td>
		<td valign='top'>$add_local_domain<br>$add_remote_domain</td>
	</tr>
</table>";
	
	
$cfg["JS"][]="js/edit.localdomain.js";
$tpl=new template_users($ou.':&nbsp;{localdomains}',$html,0,0,0,0,$cfg);	
echo $tpl->web_page;		
}

function js_script(){
	if(isset($_GET["encoded"])){$_GET["ou"]=base64_decode($_GET["ou"]);}
	if($_GET["ou"]==null){$_GET["ou"]=ORGANISTATION_FROM_USER();}
	$ou=$_GET["ou"];
	$ou_encrypted=base64_encode($ou);
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("$ou:&nbsp;{localdomains}");
	$datas=file_get_contents("js/edit.localdomain.js");
	$startup="LoadOuDOmainsIndex();";
	
	if(isset($_GET["in-front-ajax"])){
		$startup="LoadOuDOmainsIndexInFront();";
		$jsadd=remote_domain_js();
	}
	
	$html="
	var timeout=0;
	$datas
	
	function LoadOuDOmainsIndex(){
		YahooWin0(750,'$page?ajax=yes&ou=$ou_encrypted','$title');
		
	}
	
	function LoadOuDOmainsIndexInFront(){
		$('#BodyContent').load('$page?ajax=yes&ou=$ou_encrypted&in-front-ajax=yes');
	}	
	$jsadd
	$startup
	";
	
	
	echo $html;
	
}

function js_popup(){
	
	$ou=base64_decode($_GET["ou"]);
	$page=CurrentPageName();
	if($ou==null){$ou=ORGANISTATION_FROM_USER();}
	$title=$ou . ":&nbsp;{groups}";
	$users=new usersMenus();
	$POSTFIX_INSTALLED=$users->POSTFIX_INSTALLED;
	if($users->ZARAFA_INSTALLED){$users->cyrus_imapd_installed=true;}
	
	$add_local_domain=Paragraphe("64-localdomain-add.png",'{add_local_domain}','{add_local_domain_text}',"javascript:AddLocalDomain_form()","add_local_domain",210);
	$add_remote_domain=Paragraphe("64-remotedomain-add.png",'{add_relay_domain}','{add_relay_domain_text}',"javascript:AddRemoteDomain_form(\"$ou\",\"new domain\")","add_relay_domain",210);
	$local_js="LoadAjax('LocalDomainsList','$page?organization-local-domain-list=$ou');";
	
	$local_part=
	"<div style='font-size:14px;font-weight:bolder;color:#005447;width:100%;border-bottom:1px solid #005447;margin-bottom:5px'>
		{local_domain_map}:
	</div>
	<div id='LocalDomainsList' style='width:100%;overflow:auto'></div>
	<br>
	<br>";
	
	
	$remote_part="
	<div style='font-size:14px;font-weight:bolder;color:#005447;width:100%;border-bottom:1px solid #005447;margin-bottom:5px'>{relay_domain_map}:</h3>
	<div id='RelayDomainsList' style='width:100%;overflow:auto'></div>
	<br>";
	
	$remote_js="LoadAjax('RelayDomainsList','$page?organization-relay-domain-list=$ou');";
	
	if(!$POSTFIX_INSTALLED){$add_remote_domain="<p>&nbsp;</p>";$remote_part=null;$remote_js=null;}
	
	if(!$users->SAMBA_INSTALLED){
		if(!$users->cyrus_imapd_installed){$add_local_domain=null;$local_part=null;$local_js=null;}
	}
	
	
	$html="
	<input type='hidden' id='inputbox delete' value=\"{are_you_sure_to_delete}\">
	<input type='hidden' id='add_local_domain_form' value=\"{add_local_domain_form}\">
	<input type='hidden' id='ou' value='$ou'>		
	<table style='width:100%'>
	<tr>
	<td valign='top'><br>
	$local_part
	$remote_part
	</td>
	<td valign='top' width=1%><br>$add_local_domain<br>$add_remote_domain</td>
	</tr>
	
</table>
<script>
	$local_js
	$remote_js
</script>

";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
	
}



function ORGANISTATION_FROM_USER(){
	$ldap=new clladp();
	$hash=$ldap->Hash_Get_ou_from_users($_SESSION["uid"],1);
	if(!is_array($hash)){header('location:domains.index.php');}
	return $hash[0];
	}
	
// ----------------------------------------------------------------------------------------------------------------------------------------------	
function EditInfosLocalDomain(){
	$num=$_GET["EditInfosLocalDomain"];
	$ou=$_GET["ou"];
	
	$autoalias=new AutoAliases($ou);
	if(strlen($autoalias->DomainsArray[$_GET["EditInfosLocalDomain"]])>0){
		$alias="1";
	}
	
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	if($users->AMAVIS_INSTALLED){
		if($users->EnableAmavisDaemon==1){
			$amavis=COPY_DOMAINS($_GET["EditInfosLocalDomain"],$ou);
			if($users->AllowChangeAntiSpamSettings){
				$button_as_settings=Paragraphe("64-spam.png","{Anti-spam}","{change_antispam_domain_text}","javascript:Loadjs('domains.amavis.php?domain=$num')");
				//RoundedLightWhite("<input type='button' OnClick=\"javascript:Loadjs('domains.amavis.php?domain=$num');\" value='&nbsp;&nbsp;&nbsp;&nbsp;{Anti-spam}&nbsp;&raquo;&nbsp;&nbsp;&nbsp;&nbsp;'>");
			}
		}
	}
	
	if($alias=='yes'){$alias=1;}
	if($alias=='no'){$alias=0;}
	$autoalias_p=Paragraphe_switch_img("{autoaliases}","{autoaliases_text}","{$num}_autoaliases",$alias,'{enable_disable}',300);
	$amavis=RoundedLightWhite($amavis);
	$catchall=Paragraphe("64-catch-all.png","{catch_all}","{catch_all_mail_text}","javascript:Loadjs('domains.catchall.php?domain=$num&ou=$ou')");
	
	
	
	$html="
	<input type='hidden' id='ou' name='ou' value='$ou'>
	<H1>{domain}:&nbsp;&laquo;{$_GET["EditInfosLocalDomain"]}&raquo;</H1>
	<table style='width:100%'>
		<tr>
		<td valign='top' width=99%>
		<table style='width:100%'>
		<tr>
			<td valign='top' style='padding:4px'>
				$autoalias_p
				<hr>
				<div style='text-align:right'>
					<input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:EditLocalDomain('$num');\">
				</div>				
				<br>
				$amavis
				<br>
				

				
				
				</td>
			<td valign='top' style='padding:4px'>
		$button_as_settings
		<br>$catchall
			</td>
		</tr>
		</table>
	</td>
	</tr>
	</table><br>
	
	";		
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function COPY_DOMAINS($domain,$ou){
	include_once("ressources/class.amavis.inc");
	$amavis=new amavis();
	$domain=strtolower($domain);
	$refresh="LoadAjax('LocalDomainsList','domains.edit.domains.php?LocalDomainList=yes&ou=$ou');";
	$html="<H3>{duplicate_domain}</H3>
	
	
	
	<p class=caption>{duplicate_domain_text}</p>
	<form name='ffmdup'>
	<input type='hidden'  name='duplicate_local_domain' id='duplicate_local_domain' value='$domain'>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>{enable}:</td>
		<td>" . Field_numeric_checkbox_img('enable',$amavis->copy_to_domain_array[$domain]["enable"],'{enable_disable}')."</td>
	</tr>
	<tr>
		<td class=legend>{target_computer_name}:</td>
		<td>" .Field_text('duplicate_host',$amavis->copy_to_domain_array[$domain]["duplicate_host"],'width:160px')."</td>
	</tr>
	<tr>
		<td class=legend>{target_computer_port}:</td>
		<td>" .Field_text('duplicate_port',$amavis->copy_to_domain_array[$domain]["duplicate_port"],'width:30px')."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ParseForm('ffmdup','domains.edit.domains.php',true);$refresh\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
	</table>
	</form>
	";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}

function COPY_DOMAINS_SAVE(){
include_once("ressources/class.amavis.inc");
	$amavis=new amavis();
	$domain=$_GET["duplicate_local_domain"];
	if(!is_numeric($_GET["duplicate_port"])){$_GET["duplicate_port"]=25;}
	$amavis->copy_to_domain_array[$domain]["enable"]=$_GET["enable"];
	$amavis->copy_to_domain_array[$domain]["duplicate_host"]=$_GET["duplicate_host"];
	$amavis->copy_to_domain_array[$domain]["duplicate_port"]=$_GET["duplicate_port"];
	$amavis->SaveCopyToDomains();
	$tpl=new templates();
	echo html_entity_decode($tpl->_ENGINE_parse_body("{enable}:{$_GET["enable"]}\n$domain -> {$_GET["duplicate_host"]}:{$_GET["duplicate_port"]}\n{success}"));
	
}

// ------------------------------------------------------ <<<
function EditLocalDomain(){
	$domain=$_GET["EditLocalDomain"];
	$ou=$_GET["ou"];
	
	//Save Autoaliases.
	$autoaliases=new AutoAliases($ou);
	if($_GET["autoaliases"]=="1"){$autoaliases->DomainsArray[$domain]=$domain;}else{unset($autoaliases->DomainsArray[$domain]);}
	$autoaliases->Save();
	
	
}


function remote_domain_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{relay_domain_map}');
	$ou=$_GET["ou"];
	$start="remote_domain_popup()";
	if(isset($_GET["in-front-ajax"])){$start=null;}
	
	$index=base64_encode($_GET["index"]);
	$html="
		function remote_domain_popup(){
			YahooWin(650,'$page?remote-domain-popup=yes&add=yes&ou=$ou&index=$index','$title :: {$_GET["index"]}',true);	
		}
		
		
		function refresh_remote_domain_popup(){
			LoadAjax('remote_domain_popup','$page?remote-domain-form=yes&add=yes&ou=$ou&index=$index');
		}
		
		var x_AddRelayDomain= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			YahooWinHide();
			LoadAjax('RelayDomainsList','domains.edit.domains.php?RelayDomainsList=yes&ou=$ou');
		}
	
function AddRelayDomain(){
	var XHR = new XHRConnection();
	var ou=document.getElementById('ou').value;
	XHR.appendData('AddNewRelayDomainIP',document.getElementById('AddNewRelayDomainIP').value);
	XHR.appendData('AddNewRelayDomainPort',document.getElementById('AddNewRelayDomainPort').value);
	XHR.appendData('AddNewRelayDomainName',document.getElementById('AddNewRelayDomainName').value);
	XHR.appendData('trusted_smtp_domain',document.getElementById('trusted_smtp_domain').value);
	XHR.appendData('MX',document.getElementById('MX').value);
	memory_ou=document.getElementById('ou').value;
	XHR.appendData('ou','$ou');
	document.getElementById('RelayDomainsList').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
	document.getElementById('remote_domain_popup').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
	XHR.sendAndLoad(\"domains.edit.domains.php\", 'GET',x_AddRelayDomain);	
	}

function EditRelayDomain(domain_name){
	var XHR = new XHRConnection();
	XHR.appendData('EditRelayDomainIP',document.getElementById(domain_name+'_IP').value);
	XHR.appendData('EditRelayDomainPort',document.getElementById(domain_name+'_PORT').value);
	XHR.appendData('EditRelayDomainName',domain_name);
	XHR.appendData('MX',document.getElementById(domain_name+'_MX').value);
	XHR.appendData('autoaliases',document.getElementById(domain_name+'_autoaliases').value);
	XHR.appendData('trusted_smtp_domain',document.getElementById('trusted_smtp_domain').value);

	
	XHR.appendData('ou','$ou');
	document.getElementById('remote_domain_popup').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
	document.getElementById('RelayDomainsList').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
	XHR.sendAndLoad(\"domains.edit.domains.php\", 'GET',x_AddRelayDomain);	
	}	
		
   $start;
	";
		
	echo $html;
}

function remote_domain_popup(){
	$html="
	<div style='width:100%;background-image:url(img/earth-256-transp.png);
		background-position:bottom left;background-repeat:no-repeat' id='remote_domain_popup'>
	
	</div>
	<script>
		refresh_remote_domain_popup();
	</script>
	";
	echo $html;
}

function remote_domain_form(){
$_GET["index"]=base64_decode($_GET["index"]);	
$ldap=new clladp();	
$HashDomains=$ldap->Hash_relay_domains($_GET["ou"]);
$tools=new DomainsTools();
$arr=$tools->transport_maps_explode($HashDomains[$_GET["index"]]);
$page=CurrentPageName();
$autoalias=new AutoAliases($_GET["ou"]);
$users=new usersMenus();
$users->LoadModulesEnabled();

$num=$_GET["index"];
if(strlen($autoalias->DomainsArray[$num])>0){
	$alias="yes";
}
$button_as_settings=Paragraphe('64-buldo.png','{Anti-spam}','{antispam_text}',"javascript:Loadjs('domains.amavis.php?domain=$num');");
if(!$users->AMAVIS_INSTALLED){$button_as_settings=null;}
if($users->EnableAmavisDaemon<>1){$button_as_settings=null;}
if(!$users->AllowChangeAntiSpamSettings){$button_as_settings=null;}




if($_GET["index"]<>"new domain"){
	$dn="cn=@$num,cn=relay_recipient_maps,ou={$_GET["ou"]},dc=organizations,$ldap->suffix";
	$trusted_smtp_domain=0;
	if($ldap->ExistsDN($dn)){$trusted_smtp_domain=1;}
	$edit_button="<hr>". button("{edit}","EditRelayDomain('$num')");
	
	$trusted=Paragraphe_switch_img("{trusted_smtp_domain}","{trusted_smtp_domain_text}","trusted_smtp_domain",$trusted_smtp_domain,"{enable_disable}",220);
	$roundrobin=Paragraphe('64-computer-alias.png','{roundrobin}','{roundrobin_text}',"javascript:Loadjs('$page?round-robin=yes&ou={$_GET["ou"]}&domain=$num');");
	$form="
	<table style='width:100%'>
		<tr>
			<td><strong style='font-size:18px;color:black'>{domain_name}:</strong></td>
		</tr>
		<tr>
			<td align='right'><strong style='font-size:18px;color:black'>{$_GET["index"]}</strong></td>
		</tr>
		<tr>
			<td><hr></td>
		</tr>							
			<td nowrap><strong style='font-size:18px;color:black'>{target_computer_name}:&nbsp;</strong></td>
		</tr>
			<td align='right'>". Field_text("{$num}_IP",$arr[1],'width:256px;padding:10px;font-size:16px') ."</td>
		</tr>
			<td align='right'><strong style='font-size:18px;color:black'>{port}:&nbsp;". 
				Field_text("{$num}_PORT",$arr[2],'width:50px;padding:3px;font-size:16px').
				"&nbsp;" . Field_yesno_checkbox_img("{$num}_MX",$arr[3],'{mx_look}')."&nbsp;".
				Field_yesno_checkbox_img("{$num}_autoaliases",$alias,'<b>{autoaliases}</b><br>{autoaliases_text}')."
			</td>
		</tr>
		
		<tr>
			<td align='right'>$edit_button</td>
		</tr>
	</table>";
	
	
}else{

	$button_as_settings=null;
	$form="
	<table style='width:100%'>
		<tr>
			<td><strong style='font-size:18px;color:black'>{domain_name}:</strong></td>
		</tr>
		<tr>
			<td align='right'>". Field_text('AddNewRelayDomainName',null,'width:256px;padding:10px;font-size:16px') ."</td>
		</tr>
		<tr>
			<td><hr></td>
		</tr>							
			<td nowrap><strong style='font-size:18px;color:black'>{target_computer_name}:&nbsp;</strong></td>
		</tr>
			<td align='right'>". Field_text('AddNewRelayDomainIP',null,'width:256px;padding:10px;font-size:16px') ."</td>
		</tr>
			<td align='right'><strong style='font-size:18px;color:black'>{port}:&nbsp;". 
				Field_text('AddNewRelayDomainPort','25','width:50px;padding:3px;font-size:16px') .
				"&nbsp;" . Field_yesno_checkbox_img('MX','no','{mx_look}')."
			</td>
		</tr>
		<tr>
			<td colspan=2 align='right'><div style='float:right'>". Paragraphe_switch_img("{trusted_smtp_domain}","{trusted_smtp_domain_text}","trusted_smtp_domain",1,"{enable_disable}",300)."</div></td>
		</tr>		
		<tr>
			<td align='right'>
			<hr>". button("{add}","AddRelayDomain()")."</td>
		</tr>
	</table>";

}

$html="

<table style='width:100%'>
<tr>
	<td valign='top'>$form</td>
	<td valign='top' style='padding-left:5px'>$button_as_settings$roundrobin$trusted</td>
</tr>
<tr>
	<td colspan=2 align='right'>$edit_button</td>
</tr>
</table>

";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}
	
	
function RELAY_DOMAINS_LIST($ou){
$ldap=new clladp();	
$tpl=new templates();
$amavis_oui=false;
writelogs("----------------> Hash_relay_domains",__FUNCTION__,__FILE__);	
$HashDomains=$ldap->Hash_relay_domains($ou);
$aliases=new AutoAliases($ou);


if(!is_array($HashDomains)){
	return $tpl->_ENGINE_parse_body('<H5>{no_remote_domain_here}</H5>');
	
}

$users=new usersMenus();
$users->LoadModulesEnabled();
if(!$users->POSTFIX_INSTALLED){return null;}
if($users->AMAVIS_INSTALLED){if($users->EnableAmavisDaemon==1){$amavis_oui=true;}}
$disclaimer=IS_DISCLAIMER();

$tools=new DomainsTools();

	if(is_array($HashDomains)){
		$ul[]="<ul id='domains-checklist'>";
		
		while (list ($num, $ligne) = each ($HashDomains) ){
			writelogs("add in row $ligne ",__FUNCTION__,__FILE__);
			$arr=$tools->transport_maps_explode($ligne);
			$count=$count=1;
			$js="AddRemoteDomain_form('$ou','$num')";
			$relay="{$arr[1]}:{$arr[2]}";
			
			if(strlen($aliases->DomainsArray[$num])>0){$autoalias="{yes}";}else{$autoalias="{no}";}
			if($arr[3]=="yes"){$mx="{yes}";}else{$mx="{no}";}
			if($amavis_oui){
				$amavis="
				<tr>
				<td class=legend width=1% nowrap>{Anti-spam}:</td>
				<td>". texttooltip("[{settings}]","{Anti-spam}:$num","Loadjs('domains.amavis.php?domain=$num')",null,0,"font-weight:bold;font-size:12px")."</td>
				</tr>";
			}
			
					
			if($disclaimer){
				$disclaimer_domain="
				<tr>
				<td class=legend width=1% nowrap>{disclaimer}:</td>
				<td>". texttooltip("[{settings}]","{disclaimer}:$num","Loadjs('domains.disclaimer.php?domain=$num&ou=$ou')",null,0,"font-weight:bold;font-size:12px")."</td>
				</tr>";		
			}
	
		
		$ul[]="<li class='domainsli' style='width:350px'><table style='width:100%'>
			<tr>
				<td width=1% valign='top'>". imgtootltip("domain-relay-64.png","{edit}",$js)."</td>
				<td valign='top'>";						
		$ul[]="
			<table style='width:90%'>
			<tr>
				<td colspan=2>
					<table style='width:100%'>
					<tr>
						<td><strong style='font-size:16px'>". texttooltip($num,"{parameters}",$js,null,0,"font-size:16px;color:#005447")."</strong>
						<div style='font-size:12px;text-align:right;border-top:1px solid #005447;padding:3px;font-weight:bolder'>$relay</div>
						</td>
						<td valign='top' width=1% align='right'>".imgtootltip("delete-24.png",'{label_delete_transport}',"DeleteRelayDomain('$num')")."</td>
					</tr>
					</table>
				</td>
			<tr>
				<td class=legend width=1% nowrap>{mx_look_text}:</td>
				<td><strong>$autoalias</strong></td>
			</tr>			
		
			<tr>
				<td class=legend width=1% nowrap>{aliases}:</td>
				<td><strong>$autoalias</strong></td>
			</tr>
			
			$amavis
			$amavis_duplicate
			$disclaimer_domain
			</tr>
			</table>
			</td>
			</tr>
			</table>
			";
			$ul[]="</li>";				
			
		}
	$ul[]="</ul>";}
	
	
return $tpl->_ENGINE_parse_body(@implode("\n",$ul));
}

function IS_DISCLAIMER(){
	$disclaimer=true;
	$users=new usersMenus();
	$sock=new sockets();
	$users->LoadModulesEnabled();
	$POSTFIX_INSTALLED=$users->POSTFIX_INSTALLED;
	$ALTERMIME_INSTALLED=$users->ALTERMIME_INSTALLED;
	$EnableAlterMime=$sock->GET_INFO('EnableAlterMime');
	$EnableArticaSMTPFilter=$sock->GET_INFO("EnableArticaSMTPFilter");
	$DisclaimerOrgOverwrite=$sock->GET_INFO("DisclaimerOrgOverwrite");
	if(!$POSTFIX_INSTALLED){$disclaimer=false;}
	if(!$ALTERMIME_INSTALLED){$disclaimer=false;}
	if($EnableAlterMime==1){
		if($EnableArticaSMTPFilter==0){$disclaimer=false;}
	}
	
	if($DisclaimerOrgOverwrite==0){$disclaimer=false;}	
	return $disclaimer;
}
	
function DOMAINSLIST($ou){
$ldap=new clladp();	
$tpl=new templates();
include_once("ressources/class.amavis.inc");
$amavis=new amavis();
$amavis_oui=false;
$disclaimer=true;
$users=new usersMenus();
$users->LoadModulesEnabled();
if($users->AMAVIS_INSTALLED){if($users->EnableAmavisDaemon==1){$amavis_oui=true;}}
	$POSTFIX_INSTALLED=$users->POSTFIX_INSTALLED;
	$sock=new sockets();
	$disclaimer=IS_DISCLAIMER();



$HashDomains=$ldap->Hash_associated_domains($ou);
	$aliases=new AutoAliases($ou);
	if(is_array($HashDomains)){
		
		$ul[]="<ul id='domains-checklist'>";
		
		while (list ($num, $ligne) = each ($HashDomains) ){
			$ul[]="<li class='domainsli' style='width:350px'>
			<table style='width:100%'>
			<tr>
				<td width=1% valign='top'>". imgtootltip("domain-64.png","{edit}",$js)."</td>
				<td valign='top'>";		
			$js="EditInfosLocalDomain('$num','$ou');";
			$jstr=CellRollOver();
			if(strlen($aliases->DomainsArray[$num])>0){$autoalias="{yes}";}else{$autoalias="{no}";}
			
			if($amavis_oui){
				$amavis_infos="
				<tr>
				<td class=legend width=1% nowrap>{Anti-spam}:</td>
				<td>". texttooltip("[{settings}]","{Anti-spam}:$num","Loadjs('domains.amavis.php?domain=$num')",null,0,"font-weight:bold;font-size:12px")."</td>
				</tr>";
				
			if($amavis->copy_to_domain_array[strtolower($num)]["enable"]==1){
				$amavis_duplicate=
				"
				<tr>
				<td class=legend width=1% nowrap>{duplicate_domain}:</td>
				<td><strong style='font-size:12px'>{$amavis->copy_to_domain_array[strtolower($num)]["duplicate_host"]}:{$amavis->copy_to_domain_array[strtolower($num)]["duplicate_port"]}</td>
				</tr>";				
				}
				
			}
			
			if($disclaimer){
				$disclaimer_domain="
				<tr>
				<td class=legend width=1% nowrap>{disclaimer}:</td>
				<td>". texttooltip("[{settings}]","{disclaimer}:$num","Loadjs('domains.disclaimer.php?domain=$num&ou=$ou')",null,0,"font-weight:bold;font-size:12px")."</td>
				</tr>";		
			}
			
			$delete="<tr>
				<td colspan=2 align='right'>". imgtootltip("delete-24.png",'{label_delete_transport}',"DeleteInternetDomain('$num')")."</td>
			</tr>";
			
			
			
			
			if(!$POSTFIX_INSTALLED){$js=null;}
			
			
				
			
			$ul[]="
			<table style='width:90%'>
			<tr>
				<td colspan=2>
					<table style='width:100%'>
					<tr>
						<td><strong style='font-size:16px'>". texttooltip($num,"{parameters}",$js,null,0,"font-size:16px;color:#005447")."</strong></td>
						<td>".imgtootltip("delete-24.png",'{label_delete_transport}',"DeleteInternetDomain('$num')")."</td>
					</tr>
					</table>
				</td>
			<tr>
				<td class=legend width=1% nowrap>{aliases}:</td>
				<td><strong>$autoalias</strong></td>
			</tr>
			$amavis_infos
			$amavis_duplicate
			$disclaimer_domain
			</tr>
			</table>
			</td>
			</tr>
			</table>
			";
			$ul[]="</li>";
		}
	}
	$ul[]="</ul>";
	$html=@implode("\n",$ul);
	
return $tpl->_ENGINE_parse_body($html);
}	







function AddNewInternetDomain(){
	$usr=new usersMenus();
	$tpl=new templates();
	if($usr->AllowChangeDomains==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}		
	$tpl=new templates();
	$ou=$_GET["AddNewInternetDomain"];
	$domain=trim($_GET["AddNewInternetDomainDomainName"]);
	$ldap=new clladp();
	
	$hashdoms=$ldap->hash_get_all_domains();
	writelogs("hashdoms[$domain]={$hashdoms[$domain]}",__FUNCTION__,__FILE__);
	
	if($hashdoms[$domain]<>null){
		echo $tpl->_ENGINE_parse_body('{error_domain_exists}');
		exit;
	}
	
	
	$ldap->AddDomainEntity($ou,$domain);
	if($ldap->ldap_last_error<>null){
		echo $ldap->ldap_last_error;
		}else{ 
			
			$sock=new sockets();
			if($usr->cyrus_imapd_installed){
				$sock->getFrameWork("cmd.php?cyrus-check-cyr-accounts=yes");
				
			}
			
			$sock->getFrameWork("cmd.php?postfix-transport-maps=yes");
			
		}
	}
	
function DeleteInternetDomain(){
	$usr=new usersMenus();
	
	if($usr->AllowChangeDomains==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}		
	
	$domain=$_GET["DeleteInternetDomain"];
	$ou=$_GET["ou"];
	$tpl=new templates();
	$artica=new artica_general();
	$ldap=new clladp();
	if($artica->RelayType=="single"){$ldap->delete_VirtualDomainsMapsMTA($ou,$domain);}
	
	$HashDomains=$ldap->Hash_domains_table($ou);
	
	$ldap=new clladp();
	$h=$ldap->Ldap_search($ldap->suffix,"(associatedDomain=$domain)",array("dn"));
	if($h["count"]>0){
		$res["associatedDomain"]=$domain;
		
		if($h["count"]==1){
			$res["objectClass"]="domainRelatedObject";
		}
		for($i=0;$i<$h["count"];$i++){
			$ldap->Ldap_del_mod($h[$i]["dn"],$res);
		}
	}
	
	
	
	writelogs("$domain type=" . $HashDomains[$domain],__FUNCTION__,__FILE__);
	switch ($HashDomains[$domain]) {
			case "associateddomain":$ldap->delete_associated_domain($ou,$domain);break;
			case "dn":
				if($ldap->ExistsDN("cn=$domain,ou=$ou,$ldap->suffix")){
					writelogs("cn=$domain,ou=$ou,$ldap->suffix exists, delete it...",__FUNCTION__,__FILE__);
					if(!$ldap->ldap_delete("cn=$domain,ou=$ou,$ldap->suffix")){
						writelogs("Error line :".__LINE__." ".$ldap->ldap_last_error,__FUNCTION__,__FILE__);
						}
					}
					break;
			default:return null;break;
		}	
	
	
	if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;}else{echo html_entity_decode($tpl->_ENGINE_parse_body('{success}'));exit;}	
}
function AddNewRelayDomain(){
	$ldap=new clladp();
	$tpl=new templates();
	$ou=$_GET["ou"];
	$trusted_smtp_domain=$_GET["trusted_smtp_domain"];
	$dn="cn=relay_domains,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="relay_domains";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		$ldap->ldap_add($dn,$upd);
		unset($upd);		
		}
	$domain_name=trim($_GET["AddNewRelayDomainName"]);
	$hashdoms=$ldap->hash_get_all_domains();
	if($hashdoms[$domain_name]<>null){
		echo $tpl->_ENGINE_parse_body('{error_domain_exists}');
		exit;
	}
	
	
	$relayIP=$_GET["AddNewRelayDomainIP"];
	$relayPort=$_GET["AddNewRelayDomainPort"];
	$mx=$_GET["MX"];
	
	$dn="cn=$domain_name,cn=relay_domains,ou=$ou,dc=organizations,$ldap->suffix";	
	
	$upd['cn'][0]="$domain_name";
	$upd['objectClass'][0]='PostFixRelayDomains';
	$upd['objectClass'][1]='top';
	$ldap->ldap_add($dn,$upd);	
	
	$dn="cn=relay_recipient_maps,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="relay_recipient_maps";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		$ldap->ldap_add($dn,$upd);
		unset($upd);		
		}	
	
	if($trusted_smtp_domain==0){	
		$dn="cn=@$domain_name,cn=relay_recipient_maps,ou=$ou,dc=organizations,$ldap->suffix";
		$upd['cn'][0]="@$domain_name";
		$upd['objectClass'][0]='PostfixRelayRecipientMaps';
		$upd['objectClass'][1]='top';
		$ldap->ldap_add($dn,$upd);
	}		
	
	$dn="cn=transport_map,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="transport_map";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		$ldap->ldap_add($dn,$upd);
		unset($upd);		
		}	
if($relayIP<>null){
	if($mx=="no"){$relayIP="[$relayIP]";}
	$dn="cn=$domain_name,cn=transport_map,ou=$ou,dc=organizations,$ldap->suffix";
	$upd['cn'][0]="$domain_name";
	$upd['objectClass'][0]='transportTable';
	$upd['objectClass'][1]='top';
	$upd["transport"][]="relay:$relayIP:$relayPort";
	$ldap->ldap_add($dn,$upd);			
	}
	
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-transport-maps=yes");

			
}
function EditRelayDomain(){
	$relayIP=$_GET["EditRelayDomainIP"];
	$relayPort=$_GET["EditRelayDomainPort"];
	$domain_name=$_GET["EditRelayDomainName"];
	$MX=$_GET["MX"];
	$ldap=new clladp();
	$ou=$_GET["ou"];
	$autoaliases=$_GET["autoaliases"];
	$trusted_smtp_domain=$_GET["trusted_smtp_domain"];
	
	$auto=new AutoAliases($ou);
	if($autoaliases=="yes"){
		$auto->DomainsArray[$domain_name]=$domain_name;
	}else{
		unset($auto->DomainsArray[$domain_name]);
	}
	$auto->Save();
	writelogs("saving $dn relay:$relayIP:$relayPort trusted_smtp_domain=$trusted_smtp_domain",__FUNCTION__,__FILE__,__LINE__);
	$dn="cn=transport_map,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="transport_map";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		$ldap->ldap_add($dn,$upd);
		unset($upd);		
		}	
if($MX=="no"){$relayIP="[$relayIP]";}

$dn="cn=$domain_name,cn=transport_map,ou=$ou,dc=organizations,$ldap->suffix";		
if($ldap->ExistsDN($dn)){$ldap->ldap_delete($dn);}


	writelogs("Create $dn",__FUNCTION__,__FILE__);	
	$upd['cn'][0]="$domain_name";
	$upd['objectClass'][0]='transportTable';
	$upd['objectClass'][1]='top';
	$upd["transport"][]="relay:$relayIP:$relayPort";
	if(!$ldap->ldap_add($dn,$upd)){
		echo "Error\n"."Line: ".__LINE__."\n$ldap->ldap_last_error";
		return;
	}
	unset($upd);			
	
	$dn="cn=relay_recipient_maps,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
			$upd['cn'][0]="relay_recipient_maps";
			$upd['objectClass'][0]='PostFixStructuralClass';
			$upd['objectClass'][1]='top';
				if(!$ldap->ldap_add($dn,$upd)){
					echo "Error\n"."Line: ".__LINE__."\n$ldap->ldap_last_error";
					return;
				}
			unset($upd);		
			}
		
	
	
	$dn="cn=@$domain_name,cn=relay_recipient_maps,ou=$ou,dc=organizations,$ldap->suffix";
	if($ldap->ExistsDN($dn)){$ldap->ldap_delete($dn);}	
	if($trusted_smtp_domain==1){
		$upd['cn'][0]="@$domain_name";
		$upd['objectClass'][0]='PostfixRelayRecipientMaps';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn,$upd)){
			echo "Error\n"."Line: ".__LINE__."\n$ldap->ldap_last_error";
			return;
		}
	}
	
}
function DeleteRelayDomainName(){
	$ou=$_GET["ou"];
	$domain_name=$_GET["DeleteRelayDomainName"];
	$ldap=new clladp();
	$dn="cn=$domain_name,cn=transport_map,ou=$ou,dc=organizations,$ldap->suffix";
	$ldap->ldap_delete($dn,false);
	$dn="cn=@$domain_name,cn=relay_recipient_maps,ou=$ou,dc=organizations,$ldap->suffix";
	$ldap->ldap_delete($dn,false);
	$dn="cn=$domain_name,cn=relay_domains,ou=$ou,dc=organizations,$ldap->suffix";	
	$ldap->ldap_delete($dn,false);

}
?>	
