<?php



	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.mysql.inc');
	
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsSystemAdministrator==false){exit;}

if(isset($_GET["listnics"])){zlistnics();exit;}
if($_GET["main"]=="listnics"){zlistnics();exit;}
if($_GET["main"]=="virtuals"){Virtuals();exit;}
if(isset($_GET["virtuals-list"])){virtuals_list();exit;}
if(isset($_GET["virt-ipaddr"])){virtuals_add();exit;}
if(isset($_GET["virt-del"])){virtuals_del();exit;}

if(isset($_GET["script"])){switch_script();exit;}
if(isset($_GET["netconfig"])){netconfig_popup();exit;}
if(isset($_GET["ipconfig"])){ipconfig();exit;}
if(isset($_GET["save_nic"])){save_nic();exit;}
if(isset($_GET["hostname"])){hostname();exit;}
if(isset($_GET["ChangeHostName"])){ChangeHostName();exit;}
if(isset($_GET["AddDNSServer"])){AddDNSServer();exit;}
if(isset($_GET["DeleteDNS"])){DeleteDNS();exit;}
if(isset($_GET["js"])){js();exit;}
if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["virtual-popup-add"])){virtual_add_form();exit;}
if(isset($_GET["cdir-ipaddr"])){virtual_cdir();exit;}
if(isset($_GET["postfix-virtual"])){virtuals_js();exit;}
if(isset($_GET["js-add-nic"])){echo virtuals_js_datas();exit;}
StartPage();



function popup(){
	
	$lzwh=Buildicon64("DEF_ICO_NIC_INFOS");
	
$html="
	<p class=caption>{network_about}
		
	<table class=table_form style='width:99%;'>
	<tr>
	<td valign='top' >
	<table style='width:100%'>
	<tr>
		<td valign='top'>
		$lzwh
		</td>
		
		</tr>
		</table>
	</div>
	</td>
	<td valign='top'>
	<div id='hostname_cf'>
	<div id='nic_status'>
	</td>
	</tr>
	</table>
	".tabs();


$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
	
}

function tabs(){
	
	$page=CurrentPageName();
	$array["listnics"]='{main_interfaces}';
	$array["virtuals"]='{virtual_interfaces}';
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?main=$num\"><span>$ligne</span></li>\n";
	}
	
	
	return "
	<div id=main_config_nics style='width:100%;height:430px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_nics').tabs({
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


function js(){
	$add=js_addon()."\n".file_get_contents("js/system-network.js");
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{net_settings}');
	$page=CurrentPageName();
	$prefix=md5($page);
	$openjs="YahooWin(700,'$page?popup=yes','$title');";
	IF(isset($_GET["in-front-ajax"])){
		$openjs="$('#BodyContent').load('$page?popup=yes');";
	}
	
	$html="
var {$prefix}_timerID  = null;
var {$prefix}_tant=0;
var {$prefix}_reste=0;
var div_mem=0;

function {$prefix}_demarre(){
 if(!YahooWinOpen()){return false;}
   {$prefix}_tant = {$prefix}_tant+1;
   {$prefix}_reste=10-{$prefix}_tant;
	if ({$prefix}_tant < 20 ) {                           
      {$prefix}_timerID = setTimeout(\"{$prefix}_demarre()\",5000);
      } else {
               {$prefix}_tant = 0;
               NicSettingsChargeLogs();
               {$prefix}_demarre();                                //la boucle demarre !
   }
}
	
	$add
	
	function {$prefix}_StartNicConfig(){
		$openjs
		setTimeout(\"NicSettingsChargeLogs()\",1000);
		setTimeout(\"{$prefix}_demarre()\",1000);
	
	}
	
	{$prefix}_StartNicConfig();
	
	";
	
	echo $html;
}


function js_addon(){
	$page=CurrentPageName();
	return "var x_ChangeHostName= function (obj) {
	var results=obj.responseText;
	alert(results);
	NicSettingsChargeLogs();
}

function ChangeHostName(current){
		var text=document.getElementById('ChangeHostName').value;
		var hostname=prompt(text,current);
		var XHR = new XHRConnection();
		XHR.appendData('ChangeHostName',hostname);
		document.getElementById('hostname_cf').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_ChangeHostName);

}

function AddDNSServer(){
		var text=document.getElementById('AddDNSServer').value;
		var hostname=prompt(text);
		var XHR = new XHRConnection();
		XHR.appendData('AddDNSServer',hostname);
		XHR.sendAndLoad('$page', 'GET',x_ChangeHostName);	
}

function DeleteDNS(nameserver){
var text=document.getElementById('DeleteDNS').value;
	if(confirm(text+'\\n'+nameserver)){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteDNS',nameserver);
		XHR.sendAndLoad('$page', 'GET',x_ChangeHostName);	
	}

}

function NicSettingsChargeLogs(){
	RefreshTab('main_config_nics');
	setTimeout(\"NicSettingsChargeHostanme()\",1000);
	}
	
function NicSettingsChargeHostanme(){
	LoadAjax('hostname_cf','$page?hostname=yes');
}

";
	
}

function StartPage(){

//

$apply=RoundedLightGrey(Paragraphe("64-apply-network-service.png","{apply_network}","{apply_network_text}","javascript:ApplyConfigToServer()","apply_network"));
$conf=RoundedLightGrey(Paragraphe("64-tablet.png","{show_config_file}","{show_config_file_text}","javascript:NicShowConfig()","show_config_file"));
$tabs=tabs();

$page=CurrentPageName();
	
	$html=
	"
	
<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function demarre(){
   tant = tant+1;
   reste=10-tant;
	if (tant < 20 ) {                           
      timerID = setTimeout(\"demarre()\",5000);
      } else {
               tant = 0;
               //document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
               NicSettingsChargeLogs();
               demarre();                                //la boucle demarre !
   }
}

" . js_addon()."

</script>		
<table style='width:100%'>
	<tr>
	<td width=1% valign='top' style='padding-left:80px;padding-right:50px'><img src='img/bg_ip_settings.png'></td>
	<td valing='top' style='padding-left:10px'>
	<p class=caption>{network_about}</p>
	<div id='hostname_cf'></div>
	
	<div id='nic_status'>
	
	</div>
	
	</td>
	</tr>
	<tr>
		<td colspan=2 valign='top'>
			$tabs
			<div id='current_table' style='width:100%;height:200px;overflow:auto'></div>
		</td>
	</tr>
	</table>
	<script>demarre();</script>
	<script>NicSettingsChargeLogs();</script>
	";

$JS["JS"][]="js/system-network.js";
$tpl=new template_users('{net_settings}',$html,0,0,0,0,$JS);
echo $tpl->web_page;
}


function switch_script(){
	
	switch ($_GET["script"]) {
		case "netconfig":echo netconfig();break;
	
		default:
			break;
	}
	
}

function hostname(){
$nic=new networking();
$nameserver=$nic->arrayNameServers;
$dns_text="<table style='width:100%;border:1px solid #5B5B5B;padding:3px;margin:3px;background-color:#E3E3E3'>";


if(is_array($nameserver)){
	while (list ($num, $val) = each ($nameserver) ){
		$val=trim($val);
		$dns_text=$dns_text."<tr " . CellRollOver_jaune().">
			<td width=1%><img src='img/fw_bold.gif'>
			<td class=legend nowrap>{nameserver}:</td>
			<td width=99% nowrap><strong style='font-size:11px'>$val</strong></td>
			<td width=1%>" . imgtootltip('ed_delete.gif','{delete}',"DeleteDNS('$val');")."</td>
			</tr>";
		
		
	}
}

$dns_text=$dns_text."
<tr>
<td align='right' colspan=4><input type='button' OnClick=\"javascript:AddDNSServer();\" value='{add}&raquo;'></td>
</tr>
</table>
<br>
<input type='hidden' name='ChangeHostName' id='ChangeHostName' value='{ChangeHostName}'>
<input type='hidden' name='AddDNSServer' id='AddDNSServer' value='{AddDNSServer}'>
<input type='hidden' name='DeleteDNS' id='DeleteDNS' value='{DeleteDNS}'>




<table style='width:100%;border:1px solid #5B5B5B;padding:3px;margin:3px;background-color:#E3E3E3'>
<tr>
	<td class=legend>{hostname}:</td>
	<td><strong style='font-size:12px'><strong>$nic->hostname</strong></td>
	<td width=1%><input type='button' OnClick=\"javascript:ChangeHostName('$nic->hostname');\" value='{edit}&nbsp;&raquo;'></td>
</tr>
</table>";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($dns_text);
}

function ChangeHostName(){
	$tpl=new templates();
	if($_GET["ChangeHostName"]=='null'){
		echo $tpl->_ENGINE_parse_body('{cancel}');
		return null;}
	
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?ChangeHostName={$_GET["ChangeHostName"]}");
	
	
	$users=new usersMenus();
	if($users->POSTFIX_INSTALLED){
		include_once('ressources/class.main_cf.inc');
		$main=new main_cf();
		$main->main_array["myhostname"]=$_GET["ChangeHostName"];
		$main->save_conf();
		$main->save_conf_to_server();
	}
	
	
	
}



function zlistnics(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tcp=new networking();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?list-nics=yes")));
	$html="<table style='width:99%' class=table_form>
	<tr>
	";
	$count=0;
	while (list ($num, $val) = each ($datas) ){
		$val=trim($val);
		if(preg_match('#master#',$val)){continue;}
		if(trim($val)==null){continue;}
		$tcp->ifconfig(trim($val));
		$text=listnicinfos(trim($val));
		$js="javascript:Loadjs('$page?script=netconfig&nic=$val')";
		if(!$tcp->linkup){$img_on="64-win-nic-off.png";}else{$img_on="64-win-nic.png";}
		$tr_deb=null;
		$tr_fin=null;
		
		if($count>1){
			$tr_deb="
			<tr>";
			$tr_fin="</tr>";
			$count=0;
		}
		
		$nic_table="
		
		<table style='width:100%;margin:3px;padding:3px;border:1px solid #CCCCCC' 
		OnMouseOver=\";this.style.cursor='pointer';this.style.background='#F5F5F5';\"
		OnMouseOut=\";this.style.cursor='default';this.style.background='#FFFFFF';\"
		>
		<tr>
			<td valign='top' width=1%><img src='img/$img_on'></td>
			<td valign='top' style='padding:4px'>
				<div OnClick=\"$js\">$text</div>
				<div style='text-align:right'>". imgtootltip("plus-16.png","{add_virtual_ip_addr_explain_js}","Loadjs('$page?js-add-nic=$val')")."</div>
			</td>
		</tr>
		</table>
		
		";
		
		
		$html=$html . "
		$tr_fin
		$tr_deb
		
		<td valign='top'>
			$nic_table
		</td>
		
		";
		
		$count=$count+1;
		
		}
	echo "
	
	$html</table>";
	}

function listnicinfos($nicname){
	$sock=new sockets();
	$nicinfos=$sock->getfile("nicstatus:$nicname");
	$tbl=explode(";",$nicinfos);
	$tpl=new templates();
	
	$_netmask=html_entity_decode($tpl->_ENGINE_parse_body("{netmask}"));
	if(strlen($_netmask)>11){$_netmask=texttooltip(substr($_netmask,0,8)."...:",$tpl->_ENGINE_parse_body("{netmask}"));}else{$_netmask=$_netmask.":";}
	$wire='';
	if(trim($tbl[5])=="yes"){
		$wire=" (wireless)";
	}
	
	$defaults_infos_array=base64_encode(serialize(array("IP"=>$tbl[0],"NETMASK"=>$tbl[2],"GW"=>$tbl[4],"NIC"=>$nicname)));
	
	$html="
	<input type='hidden' id='infos_$nicname' value='$defaults_infos_array'>
	<table style='width:100%'>
	<tr>
		<td class=legend nowrap>{tcp_address}:</td>
		<td style='font-weight:bold;font-size:12px'>{$tbl[0]}</td>
	</tr>
	<tr>
		<td class=legend nowrap>$_netmask</td>
		<td style='font-weight:bold;font-size:12px'>{$tbl[2]}</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{gateway}:</td>
		<td style='font-weight:bold;font-size:12px'>{$tbl[4]}</td>
	</tr>		
	<tr>
		<td class=legend nowrap>{mac_addr}:</td>
		<td style='font-weight:bold;font-size:12px'>{$tbl[1]}</td>
	</tr>	
	</table>
	";
	
	
	return $tpl->_ENGINE_parse_body($html);
	
	
}

function netconfig(){
	$page=CurrentPageName();
	$html="
	YahooWin2(300,'$page?netconfig={$_GET["nic"]}','{$_GET["nic"]}','');
	
	function ipconfig(eth){
		YahooWin2(300,'$page?ipconfig='+eth,eth,'');
	}
	
	function SwitchDHCP(){
		if(document.getElementById('dhcp').checked==true){
			document.getElementById('IPADDR').disabled=true;
			document.getElementById('NETMASK').disabled=true;
			document.getElementById('GATEWAY').disabled=true;
			}
		else{
			document.getElementById('IPADDR').disabled=false;
			document.getElementById('NETMASK').disabled=false;
			document.getElementById('GATEWAY').disabled=false;
		
		}
	}
	
	";
	return $html;
	}

function netconfig_popup(){
	$eth=$_GET["netconfig"];
	$text_ip=listnicinfos($eth);
	


	$sock=new sockets();
	$type=$sock->getfile("SystemNetworkUse");
	$dns=$sock->getFrameWork('cmd.php?dnslist=yes');
	
	$nicinfos=$sock->getfile("nicstatus:$eth");
	$tbl=explode(";",$nicinfos);
	$wire=false;
	if(trim($tbl[5])=="yes"){
		$wire=true;
	}		
	
	$dnslist=explode(";",$dns);
	if(is_array($dnslist)){
	while (list ($num, $val) = each ($dnslist) ){
		$dns_text=$dns_text."<div style='font-size:11px;padding-left:4px'><strong>$val</strong></div>";
		
	}
	}
	
	$button=button("{properties}","ipconfig('$eth')");
	if($wire){
		$button="<div style='background-color:#F5F59F;border:1px solid #676767;padding:3px;margin:3px;font-weight:bold'>
		{warning_wireless_nic}
		</div>";
	}
	
	$html="
	<div style='background-color:#F7F7F7;border:1px solid #676767;padding:3px;margin:3px'>
	$text_ip
	</div>
	<div style='background-color:#F7F7F7;border:1px solid #676767;padding:3px;margin:3px'>
	{network_style}:<strong>$type</strong>
	</div>
	<div style='background-color:#F7F7F7;border:1px solid #676767;padding:3px;margin:3px'>
	{dns_servers}:
	$dns_text
	</div>	
	
	<div style='margin:4px;text-align:right;'>
		$button
	</div>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function ipconfig(){
	$sock=new sockets();
	$ip=new networking();
	$eth=$_GET["ipconfig"];
	$page=CurrentPageName();
	$array=$ip->GetNicInfos($eth);
	
	$dns=$sock->getFrameWork('cmd.php?dnslist=yes');
	
	$dnslist=explode(";",$dns);
	if(is_array($dnslist)){
	while (list ($num, $val) = each ($dnslist) ){
		if(trim($val)<>null){
			$dns_text[]=$val;
		}
	}}	
	
	if(($array["BOOTPROTO"]=='dhcp') OR ($array["BOOTPROTO"]==null)){
		$DISABLED=true;
		$dhcp='yes';
	}
	
	
	
	$html="
	<form name='ffm$eth'>
	<table style='width:100%'>
	<input type='hidden' name='save_nic' id='save_nic' id='save_nic' value='$eth'>
	
	
	<tr>
	<td class=legend>{use_dhcp}:</td>
	<td width=1%>" . Field_yesno_checkbox('dhcp',$dhcp,'SwitchDHCP()')."</td>
	</tr>
	
	
	</tr>
	</table>
	
	<div style='background-color:#F7F7F7;border:1px solid #676767;padding:3px;margin:3px'>
	<table style='width:100%'>
		<tr>
			<td class=legend>{proto}:</td>
			<td>{$array["BOOTPROTO"]}</td>
		</tr>
		<tr>
			<td class=legend>{tcp_address}:</td>
			<td>" . Field_text("IPADDR",$array["IPADDR"],'width:100px',null,null,null,false,null,$DISABLED)."</td>
		</tr>
		<tr>
			<td class=legend>{netmask}:</td>
			<td>" . Field_text("NETMASK",$array["NETMASK"],'width:100px',null,null,null,false,null,$DISABLED)."</td>
		</tr>	
		<tr>
			<td class=legend>{gateway}:</td>
			<td>" . Field_text("GATEWAY",$array["GATEWAY"],'width:100px',null,null,null,false,null,$DISABLED)."</td>
		</tr>
	
		
	</table>
	</div>	
	<br>
	<div style='background-color:#F7F7F7;border:1px solid #676767;padding:3px;margin:3px'>
	<table style='width:100%'>
	<tr>
		<td class=legend>{primary_dns}:</td>
		<td>" . Field_text("DNS_1",$dns_text[0],'width:100px',null,null,null,false,null)."</td>
	</tr>
	<tr>
		<td class=legend>{secondary_dns}:</td>
		<td>" . Field_text("DNS_2",$dns_text[1],'width:100px',null,null,null,false,null)."</td>
	</tr>	
	</table>
		
	
	</div>
	<table style='width:100%'>
	<tr>
	<td align='right'>
		". button("{edit}","SaveNicSettings()")."&nbsp;&nbsp;".button("{cancel}","YahooWin2(300,'$page?netconfig=$eth','$eth','');")."
	</td>
	</tr>
	</table>
	<script>
	
		var X_SaveNicSettings= function (obj) {
			var results=obj.responseText;
			var ipaddr=document.getElementById('IPADDR').value;
			alert(results+'\\n'+'<https://'+ipaddr+':{$_SERVER['SERVER_PORT']}>');
			setTimeout(\"logofff()\",15000);
			}

		function logofff(){
			var ipaddr=document.getElementById('IPADDR').value;
			document.location.href='https://'+ipaddr+':{$_SERVER['SERVER_PORT']}';
		}
	
		function SaveNicSettings(){
			var XHR = new XHRConnection();
			if(document.getElementById('dhcp').checked){XHR.appendData('dhcp','yes');}else{XHR.appendData('dhcp','no');}
			XHR.appendData('IPADDR',document.getElementById('IPADDR').value);
			XHR.appendData('NETMASK',document.getElementById('NETMASK').value);
			XHR.appendData('GATEWAY',document.getElementById('GATEWAY').value);
			XHR.appendData('DNS_1',document.getElementById('DNS_1').value);
			XHR.appendData('DNS_2',document.getElementById('DNS_2').value);
			XHR.appendData('save_nic',document.getElementById('save_nic').value);
			XHR.sendAndLoad('$page', 'GET',X_SaveNicSettings);
			
		}
	
	</script>	
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	}
	
	
	
function save_nic(){
	$nic=trim($_GET["save_nic"]);
	$IPADDR=trim($_GET["IPADDR"]);
	$NETMASK=trim($_GET["NETMASK"]);
	$GATEWAY=trim($_GET["GATEWAY"]);
	$DNS_1=$_GET["DNS_1"];
	$DNS_2=$_GET["DNS_2"];
	$dhcp=trim($_GET["dhcp"]);
	writelogs("save nic infos : $nic;$IPADDR;$NETMASK;$GATEWAY;$dhcp",__FUNCTION__,__FILE__);
	$ip=new networking();
	$ip->nameserver_add($DNS_1);
	if(trim($DNS_2)<>null){
		$ip->nameserver_add($DNS_2);
	}
	$ip->BuildResolvConf();
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?SaveNic=$nic&ip=$IPADDR&net=$NETMASK&gw=$GATEWAY&dhcp=$dhcp");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}\n{success_save_nic_infos}');

	}
	
function AddDNSServer(){
	$ip=new networking();
	$ip->nameserver_add($_GET["AddDNSServer"]);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}: {add}'.$_GET["AddDNSServer"]);	
	
}

function DeleteDNS(){
	$ip=new networking();
	$ip->nameserver_delete($_GET["DeleteDNS"]);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}:{delete} '.$_GET["DeleteDNS"]);	
	}
	
	
function virtuals_js(){

	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{virtual_interfaces}");
	$html="
	YahooWin(700,'$page?main=virtuals','$title');
	
	";
	
	echo $html;
	
}

function virtuals_js_datas(){
	$page=CurrentPageName();
	$tpl=new templates();
	$virtual_interfaces=$tpl->_ENGINE_parse_body('{virtual_interfaces}');
	$default_load="VirtualIPRefresh();";
	if(isset($_GET["js-add-nic"])){
		$default_load="VirtualIPJSAdd('{$_GET["js-add-nic"]}');";
	}
	
	$html="
		var windows_size=500;
	
		function VirtualIPAdd(){
			YahooWin2(windows_size,'$page?virtual-popup-add=yes&default-datas={$_GET["default-datas"]}','$virtual_interfaces');
		
		}
		
		function VirtualIPJSAdd(nic){
			var defaultDatas='';
			if(document.getElementById('infos_'+nic)){
				defaultDatas=document.getElementById('infos_'+nic).value;
			}
			YahooWin2(windows_size,'$page?virtual-popup-add=yes&default-datas='+defaultDatas,'$virtual_interfaces');
		}
		
		function VirtualsEdit(ID){
			YahooWin2(500,'$page?virtual-popup-add=yes&ID='+ID,'$virtual_interfaces');
		}
		
		var X_CalcCdirVirt= function (obj) {
			var results=obj.responseText;
			document.getElementById('cdir').value=results;
		}		
		
		function CalcCdirVirt(recheck){
			var cdir=document.getElementById('cdir').value;
			if(recheck==0){
				if(cdir.length>0){return;}
			}
			var XHR = new XHRConnection();
			
			XHR.appendData('cdir-ipaddr',document.getElementById('ipaddr').value);
			XHR.appendData('netmask',document.getElementById('netmask').value);
			XHR.sendAndLoad('$page', 'GET',X_CalcCdirVirt);
		}
		
		var X_VirtualIPAddSave= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			YahooWin2Hide();
			if(document.getElementById('main_openvpn_config')){RefreshTab('main_openvpn_config');}
			VirtualIPRefresh();
			
		}
		
		function VirtualIPAddSave(){
			var XHR = new XHRConnection();
			XHR.appendData('virt-ipaddr',document.getElementById('ipaddr').value);
			XHR.appendData('netmask',document.getElementById('netmask').value);
			XHR.appendData('cdir',document.getElementById('cdir').value);
			XHR.appendData('gateway',document.getElementById('gateway').value);
			XHR.appendData('nic',document.getElementById('nic').value);
			XHR.appendData('org',document.getElementById('org').value);
			XHR.appendData('ID',document.getElementById('ID').value);
			document.getElementById('virtip').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad('$page', 'GET',X_VirtualIPAddSave);
		}
		function VirtualIPRefresh(){
			LoadAjax('virtuals-list','$page?virtuals-list=yes');
		}
		
		function BuildVirtuals(){
			if(document.getElementById('virtuals-list')){
				LoadAjax('virtuals-list','$page?virtuals-list=yes&build=yes');
			}
		}
		
		function VirtualsDelete(id){
			document.getElementById('virtuals-list').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			var XHR = new XHRConnection();
			XHR.appendData('virt-del',id);
			XHR.sendAndLoad('$page', 'GET',X_VirtualIPAddSave);
		}
		
		$default_load	
	";
		
	return $html;
}

	
function Virtuals(){
	$page=CurrentPageName();
	$tpl=new templates();
	$virtual_interfaces=$tpl->_ENGINE_parse_body('{virtual_interfaces}');
	$html="
	<div style='float:left'>". imgtootltip("20-refresh.png","{refresh}","VirtualIPRefresh()")."</div>
	<div style='width:100%;text-align:right'>". button("{add}","VirtualIPAdd()")."</div>
	
	<div id='virtuals-list'></div>	
	<script>
	". virtuals_js_datas()."
	</script>";
	

	echo $tpl->_ENGINE_parse_body($html);	
	
}

function virtual_add_form(){
	$ldap=new clladp();
	$sock=new sockets();
	$page=CurrentPageName();
	$nics=unserialize(base64_decode($sock->getFrameWork("cmd.php?list-nics=yes")));
	if($_GET["ID"]>0){
		$sql="SELECT * FROM nics_virtuals WHERE ID='{$_GET["ID"]}'";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	}
	
	if(isset($_GET["default-datas"])){
			$default_array=unserialize(base64_decode($_GET["default-datas"]));
			if(is_array($default_array)){
				$ligne["nic"]=$default_array["NIC"];
			if(preg_match("#(.+?)\.([0-9]+)$#",$default_array["IP"],$re)){
				if($re[2]>254){$re[2]=1;}
				$re[2]=$re[2]+1;
				$ligne["ipaddr"]="{$re[1]}.{$re[2]}";
				$ligne["gateway"]=$default_array["GW"];
				$ligne["netmask"]=$default_array["NETMASK"];
			}
		}
	}	
	
	$styleOfFields="width:190px;font-size:14px;padding:3px";
	$ous=$ldap->hash_get_ou(true);
	$ous["openvpn_service"]="{APP_OPENVPN}";
	while (list ($num, $val) = each ($nics) ){
		$nics_array[$val]=$val;
	}
	$nics_array[null]="{select}";
	$ous[null]="{select}";
	
	$nic_field=Field_array_Hash($nics_array,"nic",$ligne["nic"],null,null,0,"font-size:14px;padding:3px");
	$ou_fields=Field_array_Hash($ous,"org",$ligne["org"],null,null,0,"font-size:14px;padding:3px");
	$html="
	<div id='virtip'>
	". Field_hidden("ID","{$_GET["ID"]}")."
	<table style='width:100%'>
	<tr>
		<td class=legend>{nic}</td>
		<td>$nic_field</td>
	</tr>
	<tr>
		<td class=legend>{organization}</td>
		<td>$ou_fields</td>
	</tr>	
	<tr>
			<td class=legend>{tcp_address}:</td>
			<td>" . Field_text("ipaddr",$ligne["ipaddr"],$styleOfFields,null,"CalcCdirVirt(0)",null,false,null,$DISABLED)."</td>
		</tr>
		<tr>
			<td class=legend>{netmask}:</td>
			<td>" . Field_text("netmask",$ligne["netmask"],$styleOfFields,null,"CalcCdirVirt(0)",null,false,null,$DISABLED)."</td>
		</tr>
		<tr>
			<td class=legend>CDIR:</td>
			<td style='padding:-1px;margin:-1px'>
			<table style='width:99%;padding:-1px;margin:-1px'>
			<tr>
			<td width=1%>
			" . Field_text("cdir",$ligne["cdir"],$styleOfFields,null,null,null,false,null,$DISABLED)."</td>
			<td align='left'> ".imgtootltip("img_calc_icon.gif","cdir","CalcCdirVirt(1)") ."</td>
			</tr>
			</table></td>
		</tr>			
		<tr>
			<td class=legend>{gateway}:</td>
			<td>" . Field_text("gateway",$ligne["gateway"],$styleOfFields,null,null,null,false,null,$DISABLED)."</td>
		</tr>	
	</table>
	</div>
	<div style='text-align:right'><hr>". button("{add}","VirtualIPAddSave()")."</div>
	<script>
		var cdir=document.getElementById('cdir').value;
		var netmask=document.getElementById('netmask').value;
		if(netmask.length>0){
			if(cdir.length==0){
				CalcCdirVirt(0);
				}
			}
	</script>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function virtual_cdir(){
	$ipaddr=$_GET["cdir-ipaddr"];
	$newmask=$_GET["netmask"];
	$ip=new IP();
	
	if($newmask<>null){
		echo $ip->maskTocdir($ipaddr, $newmask);
	}
	
}

function virtuals_add(){
	$tpl=new templates();
	if($_GET["nic"]==null){
		echo $tpl->_ENGINE_parse_body("{nic}=null");
		exit;
	}
	

	
	
	$sql="
	INSERT INTO nics_virtuals (nic,org,ipaddr,netmask,cdir,gateway)
	VALUES('{$_GET["nic"]}','{$_GET["org"]}','{$_GET["virt-ipaddr"]}','{$_GET["netmask"]}','{$_GET["cdir"]}','{$_GET["gateway"]}');
	";
	
	if($_GET["ID"]>0){
		$sql="UPDATE nics_virtuals SET nic='{$_GET["nic"]}',
		org='{$_GET["org"]}',
		ipaddr='{$_GET["virt-ipaddr"]}',
		netmask='{$_GET["netmask"]}',
		cdir='{$_GET["cdir"]}',
		gateway='{$_GET["gateway"]}' WHERE ID={$_GET["ID"]}";
	}
	writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
	}else{
		ConstructVirtsIP();
	}
	
}

function virtuals_list(){
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$q=new mysql();
	
	$sock=new sockets();
	if(isset($_GET["build"])){
		$sock->getFrameWork("cmd.php?virtuals-ip-reconfigure=yes&stay=yes");
	}
	
	$interfaces=unserialize(base64_decode($sock->getFrameWork("cmd.php?ifconfig-interfaces=yes")));
	$sql="SELECT * FROM nics_virtuals ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	$style=CellRollOver();
	$html="
	<center>
	<table style='width:100%' class=table_form>
	<tr>
		<th>&nbsp;</th>
		<th nowrap>{organization}</th>
		<th nowrap>{nic}</th>
		<th nowrap>{tcp_address}</th>
		<th nowrap>{netmask}</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
	</tr>
	";	
	
			$net=new networking();
			$ip=new IP();	
		
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		
		$eth="{$ligne["nic"]}:{$ligne["ID"]}";
		if($ligne["cdir"]==null){
			$ligne["cdir"]=$net->array_TCP[$ligne["nic"]];
			$eth=$ligne["nic"];
		}
		$img="22-win-nic-off.png";
		
		if($interfaces[$eth]<>null){
			$img="22-win-nic.png";
		}
		
		
		$html=$html."
		<tr style='font-size:14px' ". CellRollOver().">
			<td width=1%><img src='img/$img'></td>
			<td><strong style='font-size:14px' align='right'>{$ligne["org"]}</strong></td>
			<td><strong style='font-size:14px' align='right'>$eth</strong></td>
			<td><strong style='font-size:14px' align='right'>{$ligne["ipaddr"]}</strong></td>
			<td><strong style='font-size:14px' align='right'>{$ligne["netmask"]}</strong></td>
			<td width=1%>". imgtootltip("24-administrative-tools.png","{edit}","VirtualsEdit({$ligne["ID"]})")."</td>
			<td width=1%>". imgtootltip("ed_delete.gif","{delete}","VirtualsDelete({$ligne["ID"]})")."</td>
		</tr>
		
		
		";
		
	}
	$html=$html."</table></center>
	<div style='text-align:right'>". button("{reconstruct_virtual_ips}","BuildVirtuals()")."</div>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function virtuals_del(){
		$sql="DELETE FROM nics_virtuals WHERE ID={$_GET["virt-del"]}";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		ConstructVirtsIP();
}


function ConstructVirtsIP(){
	$sql="SELECT * FROM nics_virtuals ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		if($ligne["org"]=="openvpn_service"){continue;}
		$arr=explode(".",$ligne["ipaddr"]);
		$arr[3]="255";
		$brd=implode(".",$arr);
		$eth="{$ligne["nic"]}:{$ligne["ID"]}";

		$conf[$eth]=array(
			"NETMASK"=>$ligne["netmask"],
			"IP_ADDR"=>$ligne["ipaddr"],
			"BROADCAST"=>$brd,
			"GATEWAY"=>$ligne["gateway"],
			"org"=>$ligne["org"]
		
		
		);
		
	}
	
	$sock=new sockets();
	if(is_array($conf)){
		$sock->SaveConfigFile(base64_encode(serialize($conf)),"VirtualsIPs");
		$sock->getFrameWork("cmd.php?virtuals-ip-reconfigure=yes");
		return;
	}
	$sock->SaveConfigFile("","VirtualsIPs");
	$sock->getFrameWork("cmd.php?virtuals-ip-reconfigure=yes");
}

//if(isset($_GET["cdir-ipaddr"])){virtual_cdir();exit;}
	

