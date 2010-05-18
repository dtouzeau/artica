<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.dnsmasq.inc');
	include_once('ressources/class.main_cf.inc');
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){header('location:users.index.php');exit;}	
if(isset($_GET["SaveConf1"])){SaveConf1();exit;}
if(isset($_GET["interfaces"])){interfaces();exit;}
if(isset($_GET["InterfacesReload"])){echo LoadInterfaces();exit;}
if(isset($_GET["address_server"])){SaveAddress();exit;}
if(isset($_GET["addressesReload"])){echo Loadaddresses();exit;}
if(isset($_GET["ListentAddressesReload"])){echo LoadListenAddress();exit;}
if(isset($_GET["DnsmasqDeleteInterface"])){DnsmasqDeleteInterface();exit;}
if(isset($_GET["DnsmasqDeleteAddress"])){DnsmasqDeleteAddress();exit();}
if(isset($_GET["listen_addresses"])){SaveListenAddress();exit;}
if(isset($_GET["DnsmasqDeleteListenAddress"])){DnsmasqDeleteListenAddress();exit;}

page();	
function page(){

	$cf=new dnsmasq();
	$page=CurrentPageName();
	$sys=new systeminfos();
	$sys->array_interfaces[null]='{select}';
	$sys->array_tcp_addr[null]='{select}';
	$interfaces=Field_array_Hash($sys->array_interfaces,'interfaces',null);
	$tcpaddr=Field_array_Hash($sys->array_tcp_addr,'listen_addresses',null);
	
	
	
$html="
<p>{dnsmasq_intro_settings}</p>
<form name='ffm1'>
<table style='width:100%'>
<input type='hidden' name='SaveConf1' value='yes'>
<tr>
<td align='right' valign='top' class=bottom style='font-weight:bold' class=bottom>{domain-needed}:</td>
<td align='left' valign='top' class=bottom class=bottom >" . Field_key_checkbox_img('domain-needed',$cf->main_array["domain-needed"],'{enable_disable}')."</td>
<td align='left' valign='top' class=bottom style='font-size:9px'>{domain-needed_text}</td>
</tr>
<tr>
<td align='right' valign='top' class=bottom style='font-weight:bold' class=bottom>{expand-hosts}:</td>
<td align='left' valign='top' class=bottom class=bottom >" . Field_key_checkbox_img('expand-hosts',$cf->main_array["expand-hosts"],'{enable_disable}')."</td>
<td align='left' valign='top' class=bottom style='font-size:9px'>{expand-hosts_text}</td>
</tr>


<tr>
<td align='right' valign='top' class=bottom style='font-weight:bold'>{bogus-priv}:</td>
<td align='left' valign='top' class=bottom>" . Field_key_checkbox_img('bogus-priv',$cf->main_array["bogus-priv"],'{enable_disable}')."</td>
<td align='left' valign='top' class=bottom style='font-size:9px'>{bogus-priv_text}</td>
</tr>
<tr>
<td align='right' valign='top' class=bottom valign='top' class=bottom style='font-weight:bold'>{filterwin2k}:</td>
<td align='left' valign='top' class=bottom>" . Field_key_checkbox_img('filterwin2k',$cf->main_array["filterwin2k"],'{enable_disable}')."</td>
<td align='left' valign='top' class=bottom style='font-size:9px'>{filterwin2k_text}</td>
</tr>
<tr>
<td align='right' valign='top' class=bottom valign='top' class=bottom style='font-weight:bold'>{strict-order}:</td>
<td align='left' valign='top' class=bottom>" . Field_key_checkbox_img('strict-order',$cf->main_array["strict-order"],'{enable_disable}')."</td>
<td align='left' valign='top' class=bottom style='font-size:9px'>{strict-order_text}</td>
</tr>

<tr>
<td align='right' valign='top' class=bottom valign='top' class=bottom style='font-weight:bold'>{no-resolv}:</td>
<td align='left' valign='top' class=bottom>" . Field_key_checkbox_img('no-resolv',$cf->main_array["no-resolv"],'{enable_disable}')."</td>
<td align='left' valign='top' class=bottom style='font-size:9px'>{no-resolv_text}</td>
</tr>
<tr>
<td align='right' valign='top' class=bottom valign='top' class=bottom style='font-weight:bold'>{no-negcache}:</td>
<td align='left' valign='top' class=bottom>" . Field_key_checkbox_img('no-negcache',$cf->main_array["no-negcache"],'{enable_disable}')."</td>
<td align='left' valign='top' class=bottom style='font-size:9px'>{no-negcache_text}</td>
</tr>



<tr>
<td align='right' valign='top' class=bottom valign='top' class=bottom style='font-weight:bold'>{no-poll}:</td>
<td align='left' valign='top' class=bottom>" . Field_key_checkbox_img('no-poll',$cf->main_array["no-poll"],'{enable_disable}')."</td>
<td align='left' valign='top' class=bottom style='font-size:9px'>{no-poll_text}</td>
</tr>

<tr>
<td align='right' valign='top' class=bottom valign='top' class=bottom style='font-weight:bold'>{log-queries}:</td>
<td align='left' valign='top' class=bottom>" . Field_key_checkbox_img('log-queries',$cf->main_array["log-queries"],'{enable_disable}')."</td>
<td align='left' valign='top' class=bottom style='font-size:9px'>{log-queries_text}</td>
</tr>



</table>

<table style='width:100%'>
</tr>
<td align='right' valign='top' class=bottom valign='top' class=bottom style='font-weight:bold' nowrap>{resolv-file}:</td>
<td align='left' valign='top' class=bottom>" . Field_text('resolv-file',$cf->main_array["resolv-file"])."</td>
<td align='left' valign='top' class=bottom style='font-size:9px'>{resolv-file_text}</td>
</tr>
</tr>
<td align='right' valign='top' class=bottom valign='top' class=bottom style='font-weight:bold' nowrap>{cache-size}:</td>
<td align='left' valign='top' class=bottom>" . Field_text('cache-size',$cf->main_array["cache-size"])."</td>
<td align='left' valign='top' class=bottom style='font-size:9px'>{cache-size_text}</td>
</tr>

</tr>
<td align='right' valign='top' class=bottom valign='top' class=bottom style='font-weight:bold' nowrap>{dnsmasq_domain}:</td>
<td align='left' valign='top' class=bottom>" . Field_text('domain',$cf->main_array["domain"])."</td>
<td align='left' valign='top' class=bottom style='font-size:9px'>{dnsmasq_domain_text}</td>
</tr>

</tr>
<td colspan=3 align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','$page',true);\"></td>
</tr>
</table>
</form>

<H4>{dnmasq_interface}</H4>
<p>{dnmasq_interface_text}</p>
<form name='ffm2'>
<table style='width:130px'><tr><td>$interfaces&nbsp;</td><td><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm2','$page',true);InterfacesReload()\" style='margin:0px;'></td></tr></table>
</form>
<div id='dnmasq_interface'>" . LoadInterfaces() . "</div>


<H4>{dnsmasq_listen_address}</H4>
<p>{dnsmasq_listen_address_text}</p>
<form name='ffm21'>
<table style='width:170px'><tr><td>$tcpaddr&nbsp;</td><td><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm21','$page',true);ListentAddressesReload()\" style='margin:0px;'></td></tr></table>
</form>
<div id='dnsmasq_listen_address'>" . LoadListenAddress() . "</div>



<H4>{dnsmasq_address}</h4>
<p>{dnsmasq_address_text}</p>
<center>
<form name='ffm3'>
<table style='width:100%'>
<tr>
	<td nowrap><strong>{domain_or_server}</strong></td>
	<td>" . Field_text('address_server') . "</td>
	<td nowrap><strong>{ip}</strong>
	<td>" . Field_text('address_ip') . "</td>
	<td><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm3','$page',true);addressesReload();\"></td>
	</tr>
	</table>
	</center>
</form>
<div id='array_addresses'>" . Loadaddresses() . "</div>
";
	
	$JS["JS"][]='js/dnsmasq.js';
$tpl=new template_users('{dnsmasq_settings}',$html,0,0,0,0,$JS);
echo $tpl->web_page;
	
}

function LoadInterfaces(){
	$conf=new dnsmasq();
	if(!is_array($conf->array_interface)){return null;}
	$html="<center><table style='width:100px'>";
	while (list ($index, $line) = each ($conf->array_interface) ){
		$html=$html . "
		<tr>
			<td width=1% valign='middle' class=bottom><img src='img/fw_bold.gif'><td>
			<td class=bottom width=99%>$line</td>
			<td class=bottom width=1%>" . imgtootltip('x.gif','{delete}',"DnsmasqDeleteInterface('$index');")."</td>
		</tr>";
	}
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html . "</table></center>");
	
}
function LoadListenAddress(){
	$conf=new dnsmasq();
	if(!is_array($conf->array_listenaddress)){return null;}
	$html="<center><table style='width:100px'>";
	while (list ($index, $line) = each ($conf->array_listenaddress) ){
		$html=$html . "
		<tr>
			<td width=1% valign='middle' class=bottom><img src='img/fw_bold.gif'><td>
			<td class=bottom width=99%>$line</td>
			<td class=bottom width=1%>" . imgtootltip('x.gif','{delete}',"DnsmasqDeleteListenAddress('$index');")."</td>
		</tr>";
	}
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html . "</table></center>");
	
}
function Loadaddresses(){
	$conf=new dnsmasq();
	if(!is_array($conf->array_address)){return null;}
	$html="<center><table style='width:100px'>";
	while (list ($index, $line) = each ($conf->array_address) ){
		$html=$html . "
		<tr>
			<td width=1% valign='middle' class=bottom><img src='img/fw_bold.gif'><td>
			<td class=bottom width=99%>$index</td>
			<td class=bottom width=99%>$line</td>
			<td class=bottom width=1%>" . imgtootltip('x.gif','{delete}',"DnsmasqDeleteAddress('$index');")."</td>
		</tr>";
	}
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html . "</table></center>");
	
}

function SaveConf1(){
	unset($_GET["SaveConf1"]);
	
	if($_GET["resolv-file"]=='/etc/resolv.conf'){$_GET["resolv-file"]="/etc/dnsmasq.resolv.conf";}
	
	$conf=new dnsmasq();
	while (list ($key, $line) = each ($_GET) ){
		if($line<>null){
			$conf->main_array[$key]=$line;	
		}else{unset($conf->main_array[$key]);}
		}
	$conf->SaveConf(); 
}

function interfaces(){
	$conf=new dnsmasq();
	$conf->array_interface[]=$_GET["interfaces"];
	$conf->SaveConf();
}
function DnsmasqDeleteInterface(){
	$conf=new dnsmasq();
	unset($conf->array_interface[$_GET["DnsmasqDeleteInterface"]]);
	$conf->SaveConf();
}
function DnsmasqDeleteAddress(){
	$conf=new dnsmasq();
	unset($conf->array_address[$_GET["DnsmasqDeleteAddress"]]);
	$conf->SaveConf();	
}

function SaveAddress(){
	$server=$_GET["address_server"];
	$adip=$_GET["address_ip"];
	$conf=new dnsmasq();
	$conf->array_address[$server]=$adip;
	writelogs("save $server $adip",__FUNCTION__,__FILE__);
	$conf->SaveConf();
	}
function SaveListenAddress(){
	$addr=$_GET["listen_addresses"];
	$conf=new dnsmasq();
	$conf->array_listenaddress[]=$addr;
	$conf->SaveConf();
}
function DnsmasqDeleteListenAddress(){
	$index=$_GET["DnsmasqDeleteListenAddress"];
	$conf=new dnsmasq();
	unset($conf->array_listenaddress[$index]);
	$conf->SaveConf();
	
}
	