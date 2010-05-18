<?php
include_once(dirname(__FILE__) . '/ressources/class.main_cf.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . "/ressources/class.sockets.inc");
include_once(dirname(__FILE__) . "/ressources/class.pdns.inc");


if(posix_getuid()<>0){
	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
		die();exit();
	}
}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["popup-dn"])){popup_dn();exit;}
if(isset($_GET["AddAssociatedDomain"])){AddPointerDc();exit;}
if(isset($_GET["DelAssociatedDomain"])){DelPointerDC();exit;}
if(isset($_GET["RefreshDNSList"])){echo dnslist();exit;}
if(isset($_GET["popup-add-dns"])){echo popup_adddns();exit;}
if(isset($_GET["SaveDNSEntry"])){AddDNSEntry();exit;}
if(isset($_GET["DelDNSEntry"])){DelDNSEntry();exit;}



js();

function AddPointerDc(){
	$dn=$_GET["AddAssociatedDomain"];
	$ldap=new clladp();
	$upd["associateddomain"]=$_GET["entry"];
	if(!$ldap->Ldap_add_mod($dn,$upd)){echo $ldap->ldap_last_error;}
	
}
function DelPointerDC(){
	$dn=$_GET["DelAssociatedDomain"];
	$ldap=new clladp();
	$upd["associateddomain"]=$_GET["entry"];
	if(!$ldap->Ldap_del_mod($dn,$upd)){echo $ldap->ldap_last_error;}	
}

function DelDNSEntry(){
	$ldap=new clladp();
	if(!$ldap->ldap_delete($_GET["DelDNSEntry"])){
		echo $ldap->ldap_last_error;
	}
	
}

function AddDNSEntry(){
	$computername=$_GET["computername"];
	$DnsZoneName=$_GET["DnsZoneName"];
	$ComputerIP=$_GET["ComputerIP"];
	$pdns=new pdns($DnsZoneName);
	if(!$pdns->EditIPName($computername,$ComputerIP,"A",null)){echo $pdns->last_error;}
	
}


function js(){
	
	$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_PDNS}');
	$addaliases=$tpl->_ENGINE_parse_body('{pdns_addaliases_text}');
	$ADD_DNS_ENTRY=$tpl->_ENGINE_parse_body('{ADD_DNS_ENTRY}');
	$ERROR_VALUE_MISSING_PLEASE_FILL_THE_FORM=$tpl->javascript_parse_text('{ERROR_VALUE_MISSING_PLEASE_FILL_THE_FORM}');
	
	
	$html="
		var mem_dn='';
		var mem_dc='';
		notshow=0;
		function {$prefix}StartPage(){
			YahooWin3('700','$page?popup=yes','$title');
		}
		
		function EditDNSEntry(dn,name){
			YahooWin4('450','$page?popup-dn='+dn,name);
		}
		
		function AddPDNSEntry(){
			YahooWin4('450','$page?popup-add-dns=yes','$ADD_DNS_ENTRY');
		}
		
	var x_DNSAddAssociatedDomain=function (obj) {
			var results=obj.responseText;
			if (results.length>0){alert(results);}
			YahooWin4('450','$page?popup-dn='+mem_dn,mem_dc);
			RefreshDNSList();
			
		}
		
	
	var x_SaveDNSEntry=function (obj) {
			var results=obj.responseText;
			if (results.length>0){alert(results);}
			YahooWin4Hide();
			RefreshDNSList();
			
		}
		
		function SaveDNSEntry(){
			var ok=1;
			var computername=document.getElementById('computername').value;
			var DnsZoneName=document.getElementById('DnsZoneName').value;
			var ComputerIP=document.getElementById('ComputerIP').value;
			if(computername.length==0){ok=0;}
			if(DnsZoneName.length==0){ok=0;}
			if(ComputerIP.length==0){ok=0;}
			if(ok==0){alert('$ERROR_VALUE_MISSING_PLEASE_FILL_THE_FORM');return;}
			var XHR = new XHRConnection();
			XHR.appendData('SaveDNSEntry','yes');
			XHR.appendData('computername',computername);
			XHR.appendData('DnsZoneName',DnsZoneName);
			XHR.appendData('ComputerIP',ComputerIP);
			document.getElementById('SaveDNSEntry').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveDNSEntry);
		
		}
			
		
		function DNSAddAssociatedDomain(dn,dc){
			mem_dn=dn;
			mem_dc=dc;
			notshow=0;
			var entry=prompt('$addaliases');
			if(!entry){return;}
			var XHR = new XHRConnection();
			XHR.appendData('AddAssociatedDomain',dn);
			XHR.appendData('entry',entry);
			document.getElementById('DNSAssociatedDomain').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_DNSAddAssociatedDomain);
			
		}
		
		function DelDNSEntry(dn){
			var XHR = new XHRConnection();
			XHR.appendData('DelDNSEntry',dn);
			document.getElementById('DNSAssociatedDomain').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveDNSEntry);		
		
		}
		
		function DNSDeleteAssociatedDomain(dn,dc){
			mem_dn=dn;
			mem_dc=dc;
			notshow=1;
			var XHR = new XHRConnection();
			XHR.appendData('DelAssociatedDomain',dn);
			XHR.appendData('entry',dc);
			XHR.sendAndLoad('$page', 'GET',x_DNSAddAssociatedDomain);		
		}
		
	var x_RefreshDNSList=function (obj) {
			var results=obj.responseText;
			document.getElementById('dns_list').innerHTML=results;
			
		}			
		
		function RefreshDNSList(){
			var XHR = new XHRConnection();
			XHR.appendData('RefreshDNSList','yes');
			XHR.appendData('pattern',document.getElementById('search-dns').value);
			document.getElementById('dns_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_RefreshDNSList);
		
		}
		
	
		
		function QueryPowerDNSCHange(e){
			if(checkEnter(e)){RefreshDNSList();}
		}
		
	
	{$prefix}StartPage();";
	
	echo $html;
}

function not_installed(){
	
	$html="
	<div style='margin:75px'>
	". Paragraphe('dns-not-installed-64.png','{APP_PDNS}','{pdns_not_installed}',"javascript:Loadjs('setup.index.progress.php?product=APP_PDNS&start-install=yes');")
	."</div>";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	

}


function popup(){
	
	$user=new usersMenus();
	if(!$user->POWER_DNS_INSTALLED){
		not_installed();return null;
	}
	
	
	$add=Paragraphe("dns-cp-add-64.png","{ADD_DNS_ENTRY}","{ADD_DNS_ENTRY_TEXT}","javascript:AddPDNSEntry()");
	$nic=Buildicon64('DEF_ICO_IFCFG');
	
	$list=dnslist();

	$html="<H1>{APP_PDNS}</H1>
	<p class=caption>{pdns_explain}</p>
	<table style='width:100%'>
	<tr>
		
		<td valign='top'>
				". RoundedLightWhite("<div id='dns_list' style='width:400px;height:320px;overflow:auto'>$list</div>")."
		</td>
		<td valign='top'>$add<br>$nic<br>".PDNSStatus()."</td>
	</tr>
	</table>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}

function dnslist(){
	$original_pattern=$_GET["pattern"];
	if(trim($_GET["pattern"])==null){$pattern="*";}else{
		$pattern=$_GET["pattern"];
		if(strpos($pattern,'*')==0){$pattern=$pattern.'*';}
	}
	
	$ldap=new clladp();
		$pattern="(&(objectclass=dNSDomain2)(|(aRecord=$pattern)(associatedDomain=$pattern)(dc=$pattern)))";
		$attr=array("associatedDomain","MacRecord","aRecord","sOARecord");
		$sr =ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
		$html="<table style='width:100%'>
							<tr>
						<td colspan=3>
						<table style='width:100%'>
						<tr>
							<td class=legend>{search}:</td>
							<td>". Field_text('search-dns',$original_pattern,'width:100%',null,null,null,null,"QueryPowerDNSCHange(event)")."</td>
						</tr>
						</table>
						</td>
					</tr><tr><td colspan=3><hr></tr>";
		if($sr){
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			writelogs("Found: {$hash["count"]} entries",__FUNCTION__,__FILE__);
			
			
			$count=0;
			if($hash["count"]>0){	
				if($hash["count"]>200){$hash["count"]=200;}	
				for($i=0;$i<$hash["count"];$i++){
					
					$dn=$hash[$i]["dn"];
					$arecord=$hash[$i]["arecord"][0];
					$macrecord=$hash[$i]["macrecord"][0];
					$sOARecord=$hash[$i]["soarecord"][0];
					if($arecord=="127.0.0.1"){continue;}
					if($sOARecord<>null){continue;}
					if($arecord==null){continue;}
					if($count>100){break;}
					$count=$count+1;
					$tt="<table style='width:100%'>";
					for($z=0;$z<$hash[$i]["associateddomain"]["count"];$z++){
						$tt=$tt."
							<tr>
							<td width=1%><img src='img/fw_bold.gif'></td>
							<td><strong style='font-size:11px'>{$hash[$i]["associateddomain"][$z]}</td>
							</tr>
							";
						}
					$tt=$tt."</table>";
					$CellRollOver=CellRollOver("EditDNSEntry('$dn','$arecord::$macrecord');");
					$arecord=texttooltip($arecord,$macrecord,null,null,0,'font-size:12px;font-weight:bold');
					$html=$html. "
					<tr $CellRollOver>
						<td width=1% valign='top'><img src='img/dns-cp-22.png'></td>
						<td valign='top'><strong style='font-size:12px' width=1% nowrap valign='top'>$arecord</td>
						<td valign='top'>$tt</td>
					</tr>
					<tr>
						<td colspan='4' style='border-bottom:1px solid #CCCCCC'>&nbsp;</td>
					</tr>";
					
					
				}
			}else{
				writelogs("Failed search $pattern",__FUNCTION__,__FILE__);
			}
			
		}
		$tpl=new templates();
		return $tpl->_ENGINE_parse_body($html."</table>");
			
}

function popup_adddns(){
	
	
	$ldap=new clladp();
	$domains=$ldap->hash_get_all_domains();	
	$DnsZoneName=Field_array_Hash($domains,"DnsZoneName",$computer->DnsZoneName,null,null,0,null,$disabled);
	$dnstypeTable=array(""=>"{select}","MX"=>"{mail_exchanger}","A"=>"{dnstypea}");
	$DnsType=Field_array_Hash($dnstypeTable,"DnsType",$computer->DnsType,null,null,0,null,$disabled);

$html="		
<H1>{ADD_DNS_ENTRY}</H1>	
<p class=caption>{ADD_DNS_ENTRY_TEXT}</p>
<div id='SaveDNSEntry'>
". RoundedLightWhite("
<table style='width:100%'>
<tr>
	<td class=legend>{computer_name}:</strong></td>
	<td align=left>". Field_text("computername",null,"width:120px")."</strong></td>
</tr>
<tr>
	<td class=legend>{DnsZoneName}:</strong></td>
	<td align=left>$DnsZoneName</strong></td>
</tr>
<tr>	
	<td class=legend>{computer_ip}:</strong></td>
	<td align=left>". Field_text('ComputerIP',$computer->ComputerIP,'width:120px')."</strong></td>
<tr>
<tr>	
	<td colspan=2 align='right'><hr><input type='button' OnClick=\"javascript:SaveDNSEntry();\" value='{add}&nbsp;&raquo'></td>
<tr>
</table>")."</div>";	
					
	$tpl=new templates();					
	echo $tpl->_ENGINE_parse_body($html,"domains.edit.user.php");	
}

function popup_dn(){
	$dn=$_GET["popup-dn"];
	$hash=array();
	$ldap=new clladp();
	$filter="(objectclass=*)";
	$attrs=array();
	$sr=ldap_read($ldap->ldap_connection, $dn, $filter, $attrs);
	if($sr){
    	$hash = ldap_get_entries($ldap->ldap_connection, $sr);
	}
   	@ldap_close($ldap->ldap_connection); 
	$arecord=$hash[0]["arecord"][0];
	$macrecord=$hash[0]["macrecord"][0];
	$dc=$hash[0]["dc"][0];
	$rol=CellRollOver();
	$associateddomain="<table style='width:100%'>
	<tr ". CellRollOver("DNSAddAssociatedDomain('$dn','$dc')").">
	<td colspan=3 align='right' style='border-bottom:1px solid #CCCCCC;padding-bottom:3px'>
	". imgtootltip("dns-cp-add-22.png","{add aliase}")."
	</tr>
	";
	for($i=0;$i<$hash[0]["associateddomain"]["count"];$i++){
		$associateddomain=$associateddomain.
		"<tr $rol>
			<td width=1%><img src='img/dns-cp-22.png'></td>
			<td><strong style='font-size:11px'>{$hash[0]["associateddomain"][$i]}</td>
			<td width=1%>". imgtootltip('ed_delete.gif',"{delete}","DNSDeleteAssociatedDomain('$dn','{$hash[0]["associateddomain"][$i]}')")."</td>
		</tr>";
		
	}
	$associateddomain=$associateddomain."</table>";
	
	$table="<table style='width:100%'>
	<tr>
		<td valign='middle' class=legend>{servername}:</td>
		<td valign='middle'><strong style='font-size:14px'>$dc</strong></td>
		<td valign='middle' class=legend>{ip_address}:</td>
		<td valign='middle'><strong style='font-size:14px'>$arecord</strong></td>
		<td valign='top'>". imgtootltip('dns-cp-del-32.png','{delete}',"DelDNSEntry('$dn')")."</td>
	</tr>
	</table>
	";
	
	
$html="<H1>$dc</H1>
<div id='DNSAssociatedDomain'>
$table
". RoundedLightWhite($associateddomain)."</div>";



	
   
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
	
}
function PDNSStatus(){
	$tpl=new templates();
	$tpl=new templates();
	if(is_file("ressources/logs/global.status.ini")){
		$ini=new Bs_IniHandler("ressources/logs/global.status.ini");
	}else{
		$sock=new sockets();
		$datas=base64_decode($sock->getFrameWork('cmd.php?Global-Applications-Status=yes'));
		$ini=new Bs_IniHandler($datas);
	}
	
	$status=DAEMON_STATUS_ROUND("APP_PDNS",$ini,null);
	return $tpl->_ENGINE_parse_body($status);
	
	
}




?>