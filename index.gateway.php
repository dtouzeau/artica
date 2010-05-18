<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.dhcpd.inc');
$users=new usersMenus();
if(!$users->AsSystemAdministrator){die("alert('no access');");}
if(isset($_GET["script"])){index_script();exit;}
if(isset($_GET["index_popup"])){index_page();exit;}


if(isset($_GET["dhcp-tab"])){dhcp_switch();exit;}
if(isset($_GET["index_dhcp"])){dhcp_index_js();exit;}
if(isset($_GET["index_dhcp_popup"])){dhcp_tabs();exit;}
if(isset($_GET["dhcp_enable_popup"])){dhcp_enable();exit;}
if(isset($_GET["dhcp_form"])){echo dhcp_form();exit;}
if(isset($_GET["dhcp-list"])){echo dhcp_computers_scripts();exit;}
if(isset($_GET["dhcp-pxe"])){echo dhcp_pxe_form();exit;}
if(isset($_GET["pxe_enable"])){echo dhcp_pxe_save();exit;}

if(isset($_GET["SaveDHCPSettings"])){dhcp_save();exit;}
if(isset($_GET["EnableDHCPServer"])){dhcp_enable_save();exit;}
if(isset($_GET["AsGatewayForm"])){echo gateway_page();exit;}
if(isset($_GET["gayteway_enable"])){echo gateway_enable();exit;}
if(isset($_GET["EnableArticaAsGateway"])){gateway_save();exit;}
if(isset($_GET["popup-network-masks"])){popup_networks_masks();exit;}
if(isset($_GET["show-script"])){dhcp_scripts();exit;}



function index_script(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body('{APP_ARTICA_GAYTEWAY}');
	$html="
		YahooWin0(550,'$page?index_popup=yes','$title');
	
	
	";
	
	echo $html;
}

function dhcp_index_js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body('{APP_DHCP}');
	$pxe=$tpl->_ENGINE_parse_body('{APP_DHCP} {PXE}');	
	$enable=$tpl->_ENGINE_parse_body("{EnableDHCPServer}");
	$html="
		function DHCPDGBCONF(){
		YahooWin2(790,'$page?index_dhcp_popup=yes','$title');
		setTimeout(\"DHCPCOmputers()\",800);
		}

		function EnableDHCPServerForm(){
			YahooWin3(350,'$page?dhcp_enable_popup=yes','$enable');
		}
		
		function PxeConfig(){
		YahooWin3(550,'$page?dhcp-pxe=yes','$pxe');
		
		}
		
		var x_EnableDHCPServerSave= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue);}
			YahooWin3Hide();
		}			
		
		
		function EnableDHCPServerSave(){
			var XHR = new XHRConnection();
			XHR.appendData('EnableDHCPServer',document.getElementById('EnableDHCPServer').value);
			document.getElementById('img_EnableDHCPServer').src='img/wait_verybig.gif';
			XHR.sendAndLoad('$page', 'GET',x_EnableDHCPServerSave);	
		}

	function DHCPCOmputers(){
			if(!document.getElementById('dhcpd_lists')){
				setTimeout(\"DHCPCOmputers()\",800);
			}
			LoadAjax('dhcpd_lists','$page?dhcp-list=yes');
		}
		
var x_SaveDHCPSettings= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
	RefreshTab('main_config_dhcpd');
	}		
		
	function SaveDHCPSettings(){
		var XHR = new XHRConnection();
		XHR.appendData('SaveDHCPSettings','yes');
		XHR.appendData('range1',document.getElementById('range1').value);
		XHR.appendData('range2',document.getElementById('range2').value);
		XHR.appendData('gateway',document.getElementById('gateway').value);
		XHR.appendData('netmask',document.getElementById('netmask').value);
		XHR.appendData('DNS_1',document.getElementById('DNS_1').value);
		XHR.appendData('DNS_2',document.getElementById('DNS_2').value);
		XHR.appendData('max_lease_time',document.getElementById('max_lease_time').value);
		XHR.appendData('dhcp_listen_nic',document.getElementById('dhcp_listen_nic').value);
		XHR.appendData('EnableDHCPServer',document.getElementById('EnableDHCPServer').value);
		if(document.getElementById('EnableArticaAsDNSFirst')){
			if(document.getElementById('EnableArticaAsDNSFirst').checked){XHR.appendData('EnableArticaAsDNSFirst',1);}else{XHR.appendData('EnableArticaAsDNSFirst',0);}
		}else{
			XHR.appendData('EnableArticaAsDNSFirst',0);
		}
		
		XHR.appendData('ddns_domainname',document.getElementById('ddns_domainname').value);
		document.getElementById('dhscpsettings').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveDHCPSettings);	

	}
	
		
var x_SavePXESettings= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
	PxeConfig();
	}		
	
	
	function SavePXESettings(){
		var XHR = new XHRConnection();
		XHR.appendData('pxe_enable',document.getElementById('pxe_enable').value);
		XHR.appendData('pxe_file',document.getElementById('pxe_file').value);
		XHR.appendData('pxe_server',document.getElementById('pxe_server').value);
		document.getElementById('dhcppxeform').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SavePXESettings);	

	}
		
	
    	
	
	DHCPDGBCONF();";
	
	echo $html;	
}


function index_page(){
	$bind9=Paragraphe('folder-64-bind9-grey.png','{APP_BIND9}','{APP_BIND9_TEXT}',"",null,210,null,0,false);
	$openvpn=Paragraphe('64-openvpn-grey.png','{APP_OPENVPN}','{APP_OPENVPN_TEXT}',"",null,210,null,0,false);	
	$users=new usersMenus();
	
	
	if($users->dhcp_installed){
		$dhcp=Buildicon64('DEF_ICO_DHCP');
		}
		
	if($users->BIND9_INSTALLED==true){
		$bind9=ICON_BIND9();
	}
	
	if($users->OPENVPN_INSTALLED==true){
		$openvpn=Paragraphe('64-openvpn.png','{APP_OPENVPN}','{APP_OPENVPN_TEXT}',"javascript:Loadjs('index.openvpn.php')",null,210,null,0,false);	
	}
	
	$comp=ICON_ADD_COMPUTER();
	$gateway=Buildicon64('DEF_ICO_GATEWAY');
	$html="<div style='width:530px'>
	<table>
	<tr>
	<td valign='top'>$gateway</td>
	<td valign='top'>$dhcp</td>
	</tr>
	<tr>
	<td valign='top'>$bind9</td>
	<td valign='top'>$comp</td>
	</tr>
	<tr>
		<td valign='top'>$openvpn</td>
		<td valign='top'>&nbsp;</td>
	</tr>
	</table>
	</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"system.index.php");
}

function dhcp_pxe_form(){
	
	
	$dhcp=new dhcpd();
	
	$enable=Paragraphe_switch_img('{enable}','{EnablePXEDHCP}',"pxe_enable",$dhcp->pxe_enable);
	
	$form=RoundedLightWhite("<div id='dhcppxeform'>
	<table style='width:100%'>
			<tr>
				<td valign='top'>$enable</td>
			<td valign='top'>
			<table style='width:100%'>
			<tr>
				<td class=legend nowrap>{pxe_file}:</td>
				<td>".Field_text('pxe_file',$dhcp->pxe_file,'width:130px')."</td>
				<td>&nbsp;</td>
			</tr>	
			<tr>
				<td class=legend nowrap>{pxe_server}:</td>
				<td>".Field_text('pxe_server',$dhcp->pxe_server,'width:130px')."</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td colspan=3 align='right'>
					<input type='button' OnClick=\"javascript:SavePXESettings();\" value='{edit}&nbsp;&raquo;'>
				
				</td>
			</tr>					
			
			</table>
			</td>
		</tr>
		</table>");
	$html="<H1>{PXE}</H1>
	<p class=caption>{PXE_DHCP_MINI_TEXT}</p>
	$form
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function dhcp_form(){
	$ldap=new clladp();
	$domains=$ldap->hash_get_all_domains();
	$dhcp=new dhcpd();
	$page=CurrentPageName();
	
	$users=new usersMenus();
	$sock=new sockets();
	$EnableDHCPServer=$sock->GET_INFO('EnableDHCPServer');
	
	if(count($domains)==0){$dom=Field_text('ddns_domainname',$dhcp->ddns_domainname,"font-size:13px;");}
	else{
		$domains[null]="{select}";
		$dom=Field_array_Hash($domains,'ddns_domainname',$dhcp->ddns_domainname,null,null,null,";font-size:13px;padding:3px");}
		$nic=$dhcp->array_tcp;
		if($dhcp->listen_nic==null){$dhcp->listen_nic="eth0";
	}
	
	
	while (list ($num, $val) = each ($nic) ){
		if($num==null){continue;}
		if($num=="lo"){continue;}
		$nics[$num]=$num;
	}
	if($dhcp->listen_nic<>null){
		$nics[$dhcp->listen_nic]=$dhcp->listen_nic;
	}
	$nics[null]='{select}';

	
	if(($users->BIND9_INSTALLED) OR ($users->POWER_DNS_INSTALLED) ){
		
		$EnableArticaAsDNSFirst=Field_checkbox("EnableArticaAsDNSFirst",1,$dhcp->EnableArticaAsDNSFirst);
		
		
	}else{
		$EnableArticaAsDNSFirst=Field_numeric_checkbox_img_disabled('EnableArticaAsDNSFirst',0,'{enable_disable}');	
	}
	

	
	$html="

			<form name='FFM13'><div id='dhscpsettings'>
			<input type='hidden' id='EnableDHCPServer' value='$EnableDHCPServer' name='EnableDHCPServer'>
			<table style='width:100%' class=table_form>
			
			<tr>
				<td class=legend style='font-size:13px'>{EnableArticaAsDNSFirst}:</td>
				<td>$EnableArticaAsDNSFirst</td>
				<td>". help_icon('{EnableArticaAsDNSFirst_explain}')."</td>
			</tr>				
			<tr>
				<td class=legend style='font-size:13px'>{nic}:</td>
				<td>".Field_array_Hash($nics,'dhcp_listen_nic',$dhcp->listen_nic,null,null,null,";font-size:13px;padding:3px")."</td>
				<td>&nbsp;</td>
			</tr>	
			<tr>
				<td class=legend style='font-size:13px'>{ddns_domainname}:</td>
				<td>$dom</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{max_lease_time}:</td>
				<td style='font-size:13px'>".Field_text('max_lease_time',$dhcp->max_lease_time,'width:60px;font-size:13px;padding:3px')."&nbsp;seconds</td>
				<td >".help_icon('{max_lease_time_text}')."</td>
			</tr>	
			
			<tr>
				<td class=legend style='font-size:13px'>{subnet}:</td>
				<td>".Field_text('netmask',$dhcp->netmask,'width:110px;font-size:13px;padding:3px')."&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{gateway}:</td>
				<td>".Field_text('gateway',$dhcp->gateway,'width:110px;font-size:13px;padding:3px')."&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{DNSServer} 1:</td>
				<td>".Field_text('DNS_1',$dhcp->DNS_1,'width:110px;font-size:13px;padding:3px')."&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{DNSServer} 2:</td>
				<td>".Field_text('DNS_2',$dhcp->DNS_2,'width:110px;font-size:13px;padding:3px')."&nbsp;</td>
				<td>&nbsp;</td>
			</tr>											
			<tr>
			<td class=legend style='font-size:13px'>{range}:</td>
			<td colspan=2>
			<table>
				<td class=legend style='font-size:13px'>{from}:</td>
				<td>".Field_text('range1',$dhcp->range1,'width:110px;font-size:13px;padding:3px')."&nbsp;</td>
				<td class=legend style='font-size:13px'>{to}:</td>
				<td>".Field_text('range2',$dhcp->range2,'width:110px;font-size:13px;padding:3px')."&nbsp;</td>
				</tr>
				</table>
			</td>
			</tr>	
			<tr>
				<td colspan=3 align='right'><hr>
				". button("{edit}","SaveDHCPSettings()")."
					
				
				</td>
			</tr>		
			</table>
			</form></div><br>
			
		
	";

	
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);		
	
}

function dhcp_computers_scripts(){
	$dhc=new dhcpd();
	$array=$dhc->LoadfixedAddresses();
	if(!is_array($array)){return null;}
	
	$html="<H3 style='color:#005447'>{fixedHosts}</H3>
	<table style='width:100%'>
	";
	
	while (list ($num, $ligne) = each ($array) ){
		$ligne["MAC"]=str_replace("hardware ethernet","",$ligne["MAC"]);
		$js=MEMBER_JS("$num$",1);
		$html=$html . "
		<tr " . CellRollOver($js).">
			<td valign='top'><img src='img/base.gif'></td>
			<td><strong>$num</td>
			<td><strong>{$ligne["MAC"]}</td>
			<td><strong>{$ligne["IP"]}</td>
		</tr>
			
		";
		
	}
	
	$html=$html."</table>";
	$tpl=new templates();
	return RoundedLightWhite($tpl->_ENGINE_parse_body($html));
	
}

function dhcp_scripts(){
	$dhcp=new dhcpd();
	
	if(trim($dhcp->conf)==null){
		$dhcp->conf="{ERROR_NO_CONFIG_SAVED}";
	}
	
	$html="<H1>{APP_DHCP_MAIN_CONF}</H1>
	
	<textarea style='width:100%;height:400px;border:1px solid #CCCCCC;background-color:white'>$dhcp->conf</textarea>";
	$tpl=new templates();
	echo  $tpl->_ENGINE_parse_body($html);		
	
}

function dhcp_switch(){
	switch ($_GET["dhcp-tab"]) {
		case "status":dhcp_index();break;
		case "config":echo dhcp_form();break;
		case "hosts":echo dhcp_computers_scripts();break;
	}
	
	
}

function dhcp_tabs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$array["status"]='{status}';
	$array["config"]='{settings}';
	$array["hosts"]='{hosts}';
	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?dhcp-tab=$num\"><span>$ligne</span></li>\n";
	}
	
	
	echo "
	<div id=main_config_dhcpd style='width:100%;height:500px;overflow:auto'>
		<ul>". $tpl->_ENGINE_parse_body(implode("\n",$html))."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_dhcpd').tabs({
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
	


function dhcp_index(){
	
	$config=Paragraphe("64-settings.png","{APP_DHCP_MAIN_CONF}","{APP_DHCP_MAIN_CONF_TEXT}","javascript:YahooWin3(700,'index.gateway.php?show-script=yes','{APP_DHCP_MAIN_CONF}');");
	$pxe=	Paragraphe("pxe-64.png","{PXE}","{PXE_DHCP_MINI_TEXT}","javascript:PxeConfig();");
	$routes=Buildicon64('DEF_ICO_DHCP_ROUTES');
	$events=Buildicon64('DEF_ICO_DHCP_EVENTS');
	$pcs=Buildicon64('DEF_ICO_BROWSE_COMP');
	$enable=Paragraphe("modem-64.png","{EnableDHCPServer}","{EnableDHCPServer_text}",
	"javascript:EnableDHCPServerForm()","{EnableDHCPServer_text}");
	
	
	$html="<H1>{APP_DHCP}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$enable</td>
		<td valign='top'>$config</td>
		<td valign='top'>$events</td>
	</tr>
	<tr>
		
		<td valign='top'>$routes</td>
		<td valign='top'>$pxe</td>
		<td valign='top'>$pcs</td>
		
	</tr>
	</table>
	";
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	exit;	
	
	
	$html="<H1>{APP_DHCP}</H1>
	
	<div id='dhcp_form'>" . dhcp_form()."</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function dhcp_pxe_save(){
	$dhcp=new dhcpd();
	while (list ($index, $line) = each ($_GET) ){
		$dhcp->$index=$line;
	}
	$dhcp->Save();
	
}

function dhcp_enable(){
	$sock=new sockets();
	$form=Paragraphe_switch_img("{EnableDHCPServer}","{EnableDHCPServer_text}","EnableDHCPServer",
	$sock->GET_INFO("EnableDHCPServer"),"EnableDHCPServer_text",330);
	$html="
	$form
	<div style='text-align:right;width:100%'>
	<HR>
		". button("{edit}","EnableDHCPServerSave()")."</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function dhcp_enable_save(){
	$dhcp=new dhcpd();
	$sock=new sockets();
	$sock->SET_INFO('EnableDHCPServer',$_GET["EnableDHCPServer"]);
	$dhcp->Save();
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{success}");
}

function dhcp_save(){
	$dhcp=new dhcpd();
	$sock=new sockets();
	$sock->SET_INFO('EnableDHCPServer',$_GET["EnableDHCPServer"]);
	
	
	$dhcp->listen_nic=$_GET["dhcp_listen_nic"];
	$dhcp->ddns_domainname=$_GET["ddns_domainname"];
	$dhcp->max_lease_time=$_GET["max_lease_time"];
	$dhcp->netmask=$_GET["netmask"];
	$dhcp->range1=$_GET["range1"];
	$dhcp->range2=$_GET["range2"];
	$tpl=new templates();

	$dhcp->gateway=$_GET["gateway"];
	$dhcp->DNS_1=$_GET["DNS_1"];
	$dhcp->DNS_2=$_GET["DNS_2"];

	$dhcp->EnableArticaAsDNSFirst=$_GET["EnableArticaAsDNSFirst"];
	$dhcp->Save();
	}
	
	
function gateway_enable(){
	$artica=new artica_general();
	$enable=Paragraphe_switch_img('{ARTICA_AS_GATEWAY}','{ARTICA_AS_GATEWAY_EXPLAIN}','EnableArticaAsGateway',$artica->EnableArticaAsGateway);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($enable);		
}
function gateway_page(){
	$artica=new artica_general();
	$page=CurrentPageName();
	$html="
	<form name='ffm2'>
	<table style='width:100%'>
	<tr>
		<td valign='top'><div id='gayteway_enable'>" . gateway_enable()."</div></td>
		<td valign='top'><input type='button' OnClick=\"javascript:ParseForm('ffm2','$page',true,false,false,'gayteway_enable','$page?gayteway_enable=yes');\" value='{edit}&nbsp;&raquo;'>
		</td>
	</tr>
	</table>
	</form>
	";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);			
}

function gateway_save(){
	$artica=new artica_general();
	$artica->EnableArticaAsGateway=$_GET["EnableArticaAsGateway"];
	$artica->Save();
	$dhcp=new dhcpd();
	$dhcp->Save();
	
}

function popup_networks_masks(){
	include_once(dirname(__FILE__)."/ressources/class.tcpip.inc");
	include_once(dirname(__FILE__)."/ressources/class.system.network.inc");
	$net=new networking();
	$class_ip=new IP();
	$array=$net->ALL_IPS_GET_ARRAY();
	while (list ($index, $line) = each ($array) ){
		$ip=$index;
		if(preg_match('#(.+?)\.([0-9]+)$#',$ip,$re)){
			$ip_start=$re[1].".0";
			$ip_end=$re[1].".255";
			$cdir=$class_ip->ip2cidr($ip_start,$ip_end);
			if(preg_match("#(.+)\/([0-9]+)#",$cdir,$ri)){
				$ipv4=new ipv4($ri[1],$ri[2]);
				$netmask=$ipv4->netmask();
				$hosts=$class_ip->HostsNumber($index,$netmask);
				$html=$html."
				<tr>
					<td style='font-size:13px;font-weight:bold'>$ip_start</td>
					<td style='font-size:13px;font-weight:bold'>$netmask</td>
					<td style='font-size:13px;font-weight:bold'>$hosts</td>
					
				</tr>";
			}
		}
		
		
	}
	

	
	$html="<H1>{newtork_help_me}</H1>
	<p class=caption>{you_should_use_one_of_these_network}</p>
	<table style='width:100%' class=table_form>
	<tr>
		<th>{from_ip_address}</th>
		<th>{netmask}</th>
		<th>{hosts_number}</th>
	</tr>
	$html
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}



?>