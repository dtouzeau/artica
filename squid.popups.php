<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.tcpip.inc');
	
	$user=new usersMenus();

	if($user->SQUID_INSTALLED==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	

	
	if($_GET["script"]=="network"){echo network_js();exit;}
	if($_GET["script"]=="listen_port"){echo listen_port_js();exit;}
	if($_GET["script"]=="visible_hostname"){echo visible_hostname_js();exit;}
	if($_GET["script"]=="ldap"){echo ldap_js();exit;}
	if($_GET["script"]=="dns"){echo dns_js();exit;}
	if($_GET["script"]=="plugins"){echo plugins_js();exit;}
	
	
	if($_GET["script"]=="url_regex"){echo url_regex_js();exit;}
	if(isset($_GET["url_regex_list"])){echo url_regex_popup_list();exit;}
	

	if($_GET["content"]=="dns"){echo dns_popup();exit;}
	if($_GET["content"]=="network"){echo network_popup();exit;}
	if($_GET["content"]=="listen_port"){echo listen_port_popup();exit;}
	if($_GET["content"]=="visible_hostname"){echo visible_hostname_popup();exit;}
	if($_GET["content"]=="ldap_auth"){echo ldap_auth_popup();exit;}
	if($_GET["content"]=="plugins"){echo plugins_popup();exit;}
	if($_GET["content"]=="url_regex"){echo url_regex_popup();exit;}
	if($_GET["content"]=="url_regex_list"){echo url_regex_popup_list();exit;}
	if($_GET["content"]=="url_regex_import"){url_regex_popup_import();exit;}
	
	
	
	
	if($_GET["blocksites"]=="deny"){url_regex_popup1();exit;}
	if($_GET["blocksites"]=="MalwarePatrol"){url_regex_MalwarePatrol_popup();exit;}
	if(isset($_GET["EnableMalwarePatrol"])){url_regex_MalwarePatrol_save();exit;}
	
	
	
	if(isset($_GET["addipfrom"])){CalculCDR();exit;}
	if(isset($_GET["NetDelete"])){network_delete();exit;}
	if(isset($_GET["listenport"])){listen_port_save();exit;}
	if(isset($_GET["visible_hostname_save"])){visible_hostname_save();exit;}
	if(isset($_GET["ldap_auth"])){ldap_auth_save();exit;}
	if(isset($_GET["ntlm_auth"])){ldap_ntlm_auth_save();exit;}
	if(isset($_GET["nameserver"])){dns_add();exit();}
	if(isset($_GET["DnsDelete"])){dns_del();exit();}
	if(isset($_GET["enable_plugins"])){plugins_save();exit;}
	if(isset($_GET["website_block"])){url_regex_save();exit;}
	if(isset($_GET["website_block_delete"])){url_regex_del();exit;}
	if(isset($_GET["force-upgrade-squid"])){force_upgrade_squid();exit;}
	if(isset($_POST["DenyWebSiteImportPerform"])){url_regex_popup_import_receive();exit;}
	


	
	function network_js(){
		$page=CurrentPageName();
		$tpl=new templates();
		$your_network=$tpl->_ENGINE_parse_body("{your_network}");
		echo "
		YahooWin2(500,'$page?content=network','$your_network','');
		
		
		var x_netadd= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			YahooWin2(500,'$page?content=network','$your_network','');
		}
		
		function netadd(){
			var XHR = new XHRConnection();
			XHR.appendData('addipfrom',document.getElementById('from_ip').value);
			XHR.appendData('addipto',document.getElementById('to_ip').value);
			document.getElementById('squid_network_id').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_netadd);	
		}
		
		function NetDelete(num){
			var XHR = new XHRConnection();
			XHR.appendData('NetDelete',num);
			document.getElementById('squid_network_id').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_netadd);	
		}
		
		function SquidnetaddCheck(e){
			if(checkEnter(e)){netadd();}
		}
		";
		
	}
	
	
function ldap_auth_save(){
		$squid=new squidbee();	
		$squid->LDAP_AUTH=$_GET["ldap_auth"];
		if($squid->LDAP_AUTH==1){$squid->NTLM_AUTH=0;}
		if(!$squid->SaveToLdap()){
			echo $squid->ldap_error;
			exit;
		}
	}

	
function ldap_ntlm_auth_save(){
		$squid=new squidbee();	
		$squid->NTLM_AUTH=$_GET["ntlm_auth"];
		if($squid->NTLM_AUTH==1){$squid->LDAP_AUTH=0;}
		if(!$squid->SaveToLdap()){
			echo $squid->ldap_error;
			return;
		}
}
	
	

		
function dns_js(){
		$page=CurrentPageName();
		echo "
		YahooWin2(450,'$page?content=dns','DNS servers...','');
		
		var x_dnsadd= function (obj) {
			var results=obj.responseText;
			alert(results);
			YahooWin2(450,'$page?content=dns','DNS servers...','');
		}
		
		function dnsadd(){
			var XHR = new XHRConnection();
			XHR.appendData('nameserver',document.getElementById('nameserver').value);
			XHR.sendAndLoad('$page', 'GET',x_dnsadd);	
		}
		
		function DnsDelete(num){
			var XHR = new XHRConnection();
			XHR.appendData('DnsDelete',num);
			XHR.sendAndLoad('$page', 'GET',x_dnsadd);	
		}
";}
		
		
		
function url_regex_js(){
		$page=CurrentPageName();
		$tpl=new templates();
		$import=$tpl->_ENGINE_parse_body("{import}");
		
		echo "
		function url_regex_js_start(){
			YahooWin2(650,'$page?content=url_regex','web-site blocking...','');
		}
		
		function url_regex_js_list(){
			var adduri='';
			if(document.getElementById('SearchDenyWebSitePattern')){
				adduri='&SearchDenyWebSitePattern='+document.getElementById('SearchDenyWebSitePattern').value
			}
			LoadAjax('squid-block-list','$page?content=url_regex_list'+adduri);
		}
		
		var x_DenyWebSiteAdd= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			url_regex_js_list();
			
		}
		
		function DenyWebSiteAdd(){
			var XHR = new XHRConnection();
			XHR.appendData('website_block',document.getElementById('website_block').value);
			if(document.getElementById('SquidAutoblock').checked){
			XHR.appendData('SquidAutoblock',1);}else{XHR.appendData('SquidAutoblock',0);}
			
			
			XHR.sendAndLoad('$page', 'GET',x_DenyWebSiteAdd);	
		}
		
		function DenyWebSiteDel(num){
			var XHR = new XHRConnection();
			XHR.appendData('website_block_delete',num);
			XHR.sendAndLoad('$page', 'GET',x_DenyWebSiteAdd);	
		}
		
		var x_EnableMalwarePatrol= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			RefreshTab('main_config_denywbl');
			}		
		
		function EnableMalwarePatrol(){
			var XHR = new XHRConnection();
			XHR.appendData('EnableMalwarePatrol',document.getElementById('EnableMalwarePatrol').value);
			document.getElementById('img_EnableMalwarePatrol').src='img/wait_verybig.gif';
			XHR.sendAndLoad('$page', 'GET',x_EnableMalwarePatrol);
		}
		
		function DenyWebSiteImport(){
			YahooWin3(550,'$page?content=url_regex_import','$import...','');
		}
		
		var x_DenyWebSiteImportPerform= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			RefreshTab('main_config_denywbl');
			YahooWin3Hide();
			}			
		
		function DenyWebSiteImportPerform(){
			var lisr=document.getElementById('url_regex_popup_import').value;
			var XHR = new XHRConnection();
			XHR.appendData('DenyWebSiteImportPerform',lisr);
			document.getElementById('url_regex_popup_import_div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'POST',x_DenyWebSiteImportPerform);
		}
		
		function SearchDenyWebSitePatternEnter(e){
			if(!checkEnter(e)){return;}
			url_regex_js_list();
		}
		
		url_regex_js_start();";	
	
}


function url_regex_MalwarePatrol_save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableMalwarePatrol",$_GET["EnableMalwarePatrol"]);
	$sock->getFrameWork("cmd.php?MalwarePatrol=yes");
	$squid=new squidbee();
	$squid->SaveToLdap();
	
}

function url_regex_MalwarePatrol_popup(){
	
	
	$sock=new sockets();
	$EnableMalwarePatrol=$sock->GET_INFO("EnableMalwarePatrol");
	$MalwarePatrolDatabasesCount=$sock->getFrameWork("cmd.php?MalwarePatrolDatabasesCount=yes");
	
	$text="<hr><strong>{database_entries_number}:$MalwarePatrolDatabasesCount</strong>";
	
	$EnableMalwarePatrol=Paragraphe_switch_img("{EnableMalwarePatrol}","{MalwarePatrol_text}$text","EnableMalwarePatrol",$EnableMalwarePatrol,null,430);
	
	$html="
	$EnableMalwarePatrol
	<div style='width:100%;text-align:right;border-top:1px solid #CCCCCC'>
	".button("{edit}","EnableMalwarePatrol()");
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'squid.index.php');	
	
}

function url_regex_popup_import(){
	
	$html="<p style='font-size:13px'>{url_regex_popup_import_explain}</p>
	<div id='url_regex_popup_import_div'>
	<textarea id='url_regex_popup_import' style='width:99%;height:450px;overflow:auto'></textarea>
	<div style='text-align:right'>
		<hr>
			". button("{import}","DenyWebSiteImportPerform()")."
	</div>
	</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function url_regex_popup_import_receive(){
	$datas=explode("\n",$_POST["DenyWebSiteImportPerform"]);
	if(!is_array($datas)){return null;}
	$q=new mysql();
	while (list ($num, $ligne) = each ($datas) ){
		$ligne=trim($ligne);
		if($ligne==null){continue;}
		if(substr($ligne,0,1)=="#"){continue;}
		
		$sql="INSERT INTO squid_block(uri,task_type,zDate) VALUES('$ligne','admin',NOW());";
		$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return ;}
	}

	$sock=new sockets();
	$sock->getFrameWork("cmd.php?squidnewbee=yes");		
	
}



function url_regex_popup_list(){
	if(trim($_GET["SearchDenyWebSitePattern"])<>null){
		$pattern=trim($_GET["SearchDenyWebSitePattern"])."%";
		$pattern=str_replace("*","%",$pattern);
		$pattern=str_replace("%%","%",$pattern);
		$pattern="WHERE uri LIKE '$pattern'";
		
	}
	$sql="SELECT * FROM squid_block $pattern ORDER BY uri LIMIT 0,50";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	$style=CellRollOver();
	$html="
	
	<table style='width:100%'>
	<tr>
		<td colspan=3>
			<table style='width:100%'>
			<tr>
				<td><strong>{search}:</strong></td>
				<td>
				". Field_text("SearchDenyWebSitePattern",$_GET["SearchDenyWebSitePattern"],
				"font-size:13px;padding:3px",
				null,null,null,false,"SearchDenyWebSitePatternEnter(event)")."
				</td>
			</tr>
		</table>
	<tr>
		<th>&nbsp;</th>
		<th>{website}</th>
		<th>&nbsp;</th>
	</tr>";
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
$tooltip="{$ligne["zDate"]}:<br>{$ligne["task_type"]}";
		
		$html=$html."
		<tr ".CellRollOver().">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><strong style='font-size:12px'>".texttooltip("{$ligne["uri"]}",$tooltip)."</td>
			<td width=1%>". imgtootltip('ed_delete.gif','{delete}',"DenyWebSiteDel({$ligne["ID"]})")."</td>
		</tr>";
		
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}

function url_regex_popup(){
	$page=CurrentPageName();
	$array["deny"]='{deny_websites}';
	$array["MalwarePatrol"]='{MalwarePatrol}';
	$tpl=new templates();
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?blocksites=$num\"><span>$ligne</span></li>\n");
	}
	
	
	return "
	<div id=main_config_denywbl style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_denywbl').tabs({
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


function url_regex_popup1(){
		$sock=new sockets();
		$SquidAutoblock=$sock->GET_INFO("SquidAutoblock");
		if($SquidAutoblock==null){$SquidAutoblock=0;}
		$autoblock=
		$form="
		<table style='width:100%'>
			<tr>
			<td class=legend nowrap>{autoblock}:</td>
			<td>" .Field_checkbox("SquidAutoblock",1,$SquidAutoblock)."</td>
			</tr>
			<tr>
			<td class=legend nowrap>{deny_website_label}:</td>
			<td>" . Field_text('website_block',null,'width:100%;font-size:12px;padding:4px')."</td>
			</tr>			
			<tr>
			<td align='left'>". button("{import}","DenyWebSiteImport()")."</td>
			<td align='right'>
			". button("{add}","DenyWebSiteAdd();")."
		
			</tr>
		</table>";
		
		
		
		
		$html="
			<p class=caption>{deny_websites_explain}</p>
				$form
			<br>
			<div id='squid-block-list' style='with:100%;height:300px;overflow:auto'></div>
			<script>url_regex_js_list()</script>";	
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html,'squid.index.php');			
	
}

function url_regex_save(){
	
	$sock=new sockets();
	$sock->SET_INFO("SquidAutoblock",$_GET["SquidAutoblock"]);
	if($_GET["website_block"]==null){return;}
	$sql="INSERT INTO squid_block(uri,task_type,zDate)
	VALUES('{$_GET["website_block"]}','admin',NOW());";
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return ;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?squidnewbee=yes");	
	}

function url_regex_del(){
	$num=$_GET["website_block_delete"];
	$sql="DELETE FROM squid_block WHERE ID=$num";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?squidnewbee=yes");
	}

function dns_popup(){
		$squid=new squidbee();
		while (list ($num, $ligne) = each ($squid->dns_array) ){
			$list=$list . "
			<tr " . CellRollOver().">
				<td width=1%><img src='img/network-1.gif'></td>
				<td><strong style='font-size:13px'>$ligne</strong></td>
				<td width=1%>" . imgtootltip('ed_delete.gif','{delete}',"DnsDelete($num)")."</td>
			</tr>
			
			";
			
			
		}
		
		
		$list=RoundedLightGreen("<table style='width:100%'>$list</table>");
		
		$form="
		<table style='width:100%'>
			<tr>
			<td class=legend nowrap>{server}:</td>
			<td>" . Field_text('nameserver',null,'width:195px')."</td>
			</tr>
			<tr>
			<td colspan=2 align='right'><input type='button' OnClick=\"javascript:dnsadd();\" value='{add}&nbsp;&raquo;'></td>
			</tr>
		</table>";
		
		$form=RoundedLightWhite($form);
		
		
		$html="<H1>{dns_nameservers}</H1>
			<p class=caption>{dns_nameservers_text}</p>
				$form
			<br>
			$list";
		
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html,'squid.index.php');	
	
}


function dns_add(){
	$squid=new squidbee();
	$squid->dns_array[]=$_GET["nameserver"];
	if(!$squid->SaveToLdap()){
			echo $squid->ldap_error;
			exit;
		}else{
			$tpl=new templates();
			echo $tpl->_ENGINE_parse_body('DNS:{success}');
		}		
	
}

function dns_del(){
	$squid=new squidbee();
	unset($squid->dns_array[$_GET["DnsDelete"]]);
if(!$squid->SaveToLdap()){
			echo $squid->ldap_error;
			exit;
		}else{
			$tpl=new templates();
			echo $tpl->_ENGINE_parse_body('DNS:{success} kill');
		}		
		
	
}
function ldap_js(){
		$page=CurrentPageName();
		echo "
		function ldapauth_display(){
				YahooWin2(500,'$page?content=ldap_auth','LDAP authentication...','');
		}
		
		
		var x_ldapauth= function (obj) {
			var results=trim(obj.responseText);
			if(results.length>0){alert(results);}
			ldapauth_display();
		}
		
		function ldapauth(){
			var XHR = new XHRConnection();
			XHR.appendData('ldap_auth',document.getElementById('ldap_auth').value);
			XHR.sendAndLoad('$page', 'GET',x_ldapauth);	
		}
		
		function ntlmpauth(){
			var XHR = new XHRConnection();
			XHR.appendData('ntlm_auth',document.getElementById('ntlm_auth').value);
			XHR.sendAndLoad('$page', 'GET',x_ldapauth);			
		}
		

		
		function ForceUpgradeSquid(){
			var XHR = new XHRConnection();
			XHR.appendData('force-upgrade-squid','yes');
			XHR.sendAndLoad('$page', 'GET',x_ldapauth);		
		}

		ldapauth_display();";}


function ldap_auth_popup(){
	$squid=new squidbee();
	$users=new usersMenus();
	
	$form_ldap="	
		<table style='width:100%'>
			<tr>
			<td valign='top'>" . Paragraphe_switch_img("{authenticate_users}","{authenticate_users_explain}",'ldap_auth',$squid->LDAP_AUTH,'{enable_disable}',340)."</td>
			<td  valign='top'>". button("{apply}","ldapauth()")."</td>
			</tr>
		</table>			
		";
		
	
	if($users->SAMBA_INSTALLED){
		if($users->WINBINDD_INSTALLED){
		if($users->SQUID_NTLM_ENABLED){
			if($users->SQUID_NTLM_AUTH<>null){
				$form_ntlm="	
				<table style='width:100%'>
				<tr>
				<td valign='top'>" . Paragraphe_switch_img("{authenticate_users_ntlm}","{authenticate_users_ntlm_explain}",'ntlm_auth',$squid->NTLM_AUTH,'{enable_disable}',340)."</td>
				<td  valign='top'>". button("{apply}","ntlmpauth()")."</td>
				</tr>
				</table>			
			";
			}
		}
	}}
		
		

if(trim($users->SQUID_LDAP_AUTH)==null){
	$form_ldap="	
		<table style='width:100%'>
			<tr>
			<td valign='top'>" . Paragraphe_switch_disable("{authenticate_users}","{authenticate_users_no_binaries}",null,300)."</td>
			<td  valign='top'></td>
			</tr>
		</table>";
	
}

	if($users->SAMBA_INSTALLED){
			if($users->WINBINDD_INSTALLED){
				if(!$users->SQUID_NTLM_ENABLED){
						$form_ntlm="	
						<table style='width:100%'>
						<tr>
						<td valign='top' style='font-size:13px'>{SQUID_NOT_COMPILED_NTLM_RUN_RECONFIGURE}</td>
						<td  valign='top'>". button("{install_upgrade}","ForceUpgradeSquid()")."</td>
						</tr>
						</table>			
					";
		}}
	}	
	

$html="<H1>{authenticate_users}</H1>$form_ntlm$form_ldap";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,'squid.index.php');		
}

	function plugins_js(){
		$page=CurrentPageName();
		echo "
		YahooWin2(570,'$page?content=plugins','Plugins...','');
		
		
		var x_save_plugins= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			Loadjs('div-poubelle','CacheOff.php?cache=yes');
			YahooWin2Hide();
			RefreshTab('squid_main_config');
			RefreshLeftMenu();
			}		
		
		function save_plugins(){
		  var XHR = new XHRConnection();
		  XHR.appendData('enable_plugins','yes');
		  
		  if(document.getElementById('enable_c_icap')){
		  	document.getElementById('img_enable_c_icap').src='img/wait_verybig.gif';
		  	XHR.appendData('enable_c_icap',document.getElementById('enable_c_icap').value);
		  }
		 if(document.getElementById('enable_kavproxy')){
		 	document.getElementById('img_enable_kavproxy').src='img/wait_verybig.gif';
		  	XHR.appendData('enable_kavproxy',document.getElementById('enable_kavproxy').value);
		  }		  
			
 			if(document.getElementById('enable_dansguardian')){
 				document.getElementById('img_enable_dansguardian').src='img/wait_verybig.gif';
				XHR.appendData('enable_dansguardian',document.getElementById('enable_dansguardian').value);
			}
			
	 		if(document.getElementById('enable_squidguard')){
 				document.getElementById('img_enable_squidguard').src='img/wait_verybig.gif';
				XHR.appendData('enable_squidguard',document.getElementById('enable_squidguard').value);
			}			
			
			
			
		XHR.sendAndLoad('$page', 'GET',x_save_plugins);				
 		}
	";
}

function plugins_save(){
	$squid=new squidbee();
	if(isset($_GET["enable_kavproxy"])){
		if($_GET["enable_c_icap"]==1){$_GET["enable_kavproxy"]=0;}
		$squid->enable_kavproxy=$_GET["enable_kavproxy"];
	}
	
	if(isset($_GET["enable_c_icap"])){
		writelogs("Save c-icap {$_GET["enable_c_icap"]}",__FUNCTION__,__FILE__);
		$squid->enable_cicap=$_GET["enable_c_icap"];
	}
	
	if(isset($_GET["enable_squidguard"])){
		writelogs("Save enable_squidguard {$_GET["enable_squidguard"]}",__FUNCTION__,__FILE__);
		$squid->enable_squidguard=$_GET["enable_squidguard"];
		if($_GET["enable_squidguard"]==1){$_GET["enable_dansguardian"]=0;}
	}	
	
	
	
	if(isset($_GET["enable_dansguardian"])){
		writelogs("Save dansguardian {$_GET["enable_dansguardian"]}",__FUNCTION__,__FILE__);
		$squid->enable_dansguardian=$_GET["enable_dansguardian"];
		include_once(dirname(__FILE__).'/ressources/class.dansguardian.inc');
		$dans=new dansguardian();
		$dans->SaveSettings();
	}

	if(!$squid->SaveToLdap()){
			if(trim($squid->ldap_error)<>null){echo $squid->ldap_error;}
			return;
	}
	
}


function plugins_popup(){
	$squid=new squidbee();
	$users=new usersMenus();
	
	
	$dans=Paragraphe_switch_disable('{enable_dansguardian}','{feature_not_installed}','{feature_not_installed}');	
	$cicap=Paragraphe_switch_disable('{enable_c_icap}','{feature_not_installed}','{feature_not_installed}');
	$squidguard=Paragraphe_switch_disable('{enable_squidguard}','{feature_not_installed}','{feature_not_installed}');
	
	
	//C_ICAP_INSTALLED
	
	if($squid->isicap()){
		$kav=Paragraphe_switch_img("{enable_kavproxy}","{enable_kavproxy_text}",'enable_kavproxy',$squid->enable_kavproxy,'{enable_disable}',250);
	}else{
		$kav=Paragraphe_switch_disable('{enable_kavproxy}',"{feature_not_installed}<br><strong style='color:red'>$squid->kav_accept_why</strong>",'{feature_not_installed}');		
	}
		
	if($users->C_ICAP_INSTALLED){
		if($users->SQUID_ICAP_ENABLED){
			$cicap=Paragraphe_switch_img("{enable_c_icap}","{enable_c_icap_text}",'enable_c_icap',$squid->enable_cicap,'{enable_disable}',250);
		}
	}

	
	if($users->DANSGUARDIAN_INSTALLED){
		$dans=Paragraphe_switch_img("{enable_dansguardian}","{enable_dansguardian_text}",'enable_dansguardian',$squid->enable_dansguardian,'{enable_disable}',250);
	}
	
	if($users->SQUIDGUARD_INSTALLED){
		$squidguard=Paragraphe_switch_img("{enable_squidguard}","{enable_squidguard_text}",'enable_squidguard',$squid->enable_squidguard,'{enable_disable}',250);
	}
	
	
	if($users->KASPERSKY_WEB_APPLIANCE){
		$dans=null;
		$cicap=null;
	}
	
	$form="<div id='div-poubelle'></div>
		<table style='width:100%'>
			<tr>
			<td valign='top'>$cicap<br>$kav</td>
			<td valign='top'>$squidguard<br>$dans</td>
			
			</tr>
			<tr>
			<td  valign='top' colspan=2 align='right'><hr>". button("{edit}","save_plugins()")."</td>
		</table>			
		";
		

		
$html="<H1>{activate_plugins}</H1>$form";
		
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html,'squid.index.php');		
	
	
}

	
function listen_port_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{listen_port}");
	
		echo "
		YahooWin2(350,'$page?content=listen_port','$title');
		
		var x_listenport= function (obj) {
			var results=obj.responseText;
			alert(results);
			YahooWin2Hide();
		}
		
		function listenport(){
			var XHR = new XHRConnection();
			XHR.appendData('listenport',document.getElementById('listen_port').value);
			XHR.sendAndLoad('$page', 'GET',x_listenport);	
		}		
		";	
}

function visible_hostname_js(){
	$page=CurrentPageName();
		echo "
		YahooWin2(450,'$page?content=visible_hostname','Hostname...','');
		
		var x_visible_hostname= function (obj) {
			var results=obj.responseText;
			alert(results);
			YahooWin2(450,'$page?content=visible_hostname','hostname...','');
		}
		
		function visible_hostname(){
			var XHR = new XHRConnection();
			XHR.appendData('visible_hostname_save',document.getElementById('visible_hostname_to_save').value);
			XHR.sendAndLoad('$page', 'GET',x_visible_hostname);	
		}		
		";
}


function visible_hostname_popup(){
	$squid=new squidbee();
	$form="	
		<table style='width:100%'>
			<tr>
			<td class=legend nowrap>{visible_hostname}:</td>
			<td>" . Field_text('visible_hostname_to_save',$squid->visible_hostname,'width:195px')."</td>
			</tr>
			<tr>
			<td colspan=2 align='right'><input type='button' OnClick=\"javascript:visible_hostname();\" value='{edit}&nbsp;&raquo;'></td>
			</tr>
		</table>			
		";
		
		$form=RoundedLightWhite($form);
		
$html="<H1>{visible_hostname}</H1>
			<p class=caption>{visible_hostname_text}</p>
				$form
			<br>
		";
		
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html,'squid.index.php');	
	
}

function visible_hostname_save(){
	
$squid=new squidbee();
		$squid->visible_hostname=$_GET["visible_hostname_save"];
		if(!$squid->SaveToLdap()){
			echo $squid->ldap_error;
			exit;
		}else{
			$tpl=new templates();
			echo $tpl->_ENGINE_parse_body('hostname:{success}');
		}	
	
}

function listen_port_popup(){
	
	$squid=new squidbee();
	
	
$form="
		
		<table style='width:100%'>
			<tr>
			<td class=legend nowrap style='font-size:16px;'>{listen_port}:</td>
			<td>" . Field_text('listen_port',$squid->listen_port,'width:95px;font-size:16px;padding:5px')."</td>
			</tr>
			<tr>
			<td colspan=2 align='right'><hr>". button("{edit}","listenport()")."</td>
			</tr>
		</table>			
		
		";

if($squid->enable_dansguardian==1){
$form="
		
		<table style='width:100%'>
			<tr>
			<td class=legend nowrap style='font-size:16px;'>DansGuardian {listen_port}:</td>
			<td><strong style='width:13px'>" . Field_text('listen_port',$squid->listen_port,'width:95px;;font-size:16px;padding:5px')."</strong></td>
			</tr>		
			<tr>
			<td class=legend nowrap>SQUID {listen_port}:</td>
			<td><strong style='font-size:16px;'>$squid->alt_listen_port</strong></td>
			</tr>
			<tr>
			<td colspan=2 align='right'><hr>". button("{edit}","listenport()")."
			</td>
			</tr>
		</table>";	
	
}
		
		$form=RoundedLightWhite($form);
		
$html="
			<p class=caption style='font-size:14px;padding:5px'>{listen_port_text}</p>
				$form
			<br>
			
		";
		
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html,'squid.index.php');		
			
	
}
	
	
function CalculCDR(){
	$ip=new IP();
	$ipfrom=$_GET["addipfrom"];
	$ipto=$_GET["addipto"];
	
	if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#',$ipfrom,$re)){
		$ipfrom="{$re[1]}.{$re[2]}.{$re[3]}.0";
	}
	
	if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#',$ipto,$re)){
		$ipto="{$re[1]}.{$re[2]}.{$re[3]}.255";
	}	
	
	
	$SIP=$ip->ip2cidr($ipfrom,$ipto);
	writelogs("Adding new CDIR $ipfrom -> $ipto\"$SIP\"",__FUNCTION__,__FILE__);
	if(trim($SIP)==null){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("Network:{failed}\n$ipfrom -> $ipto");
		exit;
	}
	
	$squid=new squidbee();
	$squid->network_array[]=$SIP;
	if(!$squid->SaveToLdap()){
		echo $squid->ldap_error;
		exit;
	}
	}

	
	function network_delete(){
		$squid=new squidbee();
		unset($squid->network_array[$_GET["NetDelete"]]);
		if(!$squid->SaveToLdap()){
			echo $squid->ldap_error;
			exit;
		}	
		
	}
	
function listen_port_save(){
	
	
$squid=new squidbee();
		$squid->listen_port=$_GET["listenport"];
		if(!$squid->SaveToLdap()){
			echo $squid->ldap_error;
			exit;
		}else{
			$tpl=new templates();
			echo $tpl->_ENGINE_parse_body('Port:{success}');
		}		
		
	}
	
	
	function network_popup(){
		$squid=new squidbee();
		while (list ($num, $ligne) = each ($squid->network_array) ){
			$list=$list . "
			<tr " . CellRollOver().">
				<td width=1%><img src='img/network-1.gif'></td>
				<td><strong style='font-size:13px'>$ligne</strong></td>
				<td width=1%>" . imgtootltip('ed_delete.gif','{delete}',"NetDelete($num)")."</td>
			</tr>
			<tr>
				<td colspan=3><hr></td>
			</tr>
			";
			
			
		}
		
		
		$list="<table style='width:100%;'>$list</table>";
		
		
		$form="
		<div id='squid_network_id'>
		<p class=caption style='font-size:13px'>{your_network_text}</p>
		<div style='font-size:13px;font-weight:bold'>{allow_network}:</div><br>
		<table style='width:100%'>
			<tr>
			<td valign='top' style='padding:4xp'>
			<div style='padding:2px;border:1px solid #CCCCCC;height:225px;overflow:auto'>$list</div></td>
			<td valign='top' style='padding:4xp'>
				<table style='width:100%;padding:3px;border:1px solid #CCCCCC'>
					<tr>
					<td class=legend nowrap style='font-size:13px'>{from_ip}:</td>
					<td>" . Field_text('from_ip',null,'width:120px;font-size:13px;padding:3px',null,null,null,false,"SquidnetaddCheck(event)")."</td>
					</tr>
					<tr>
					<td class=legend style='font-size:13px'>{to_ip}:</td>
					<td>" . Field_text('to_ip',null,'width:120px;font-size:13px;padding:3px',null,null,null,false,"SquidnetaddCheck(event)")."</td>
					</tr>
					<tr>
					<td colspan=2 align='right'>
					<hr>
						". button("{add}","netadd()")."
					</tr>
					</table>	
				</td>		
			</tr>
		</table>
		</div>
		";
		
		
		
		
		$html=$form;
		
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html,'squid.index.php');
		
		
	}
	
function force_upgrade_squid(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?force-upgrade-squid=yes");
	$tpl=new templates();
echo $tpl->javascript_parse_text("{CONTROL_CENTER_UPGRADE_OK}");
	
}
	
	