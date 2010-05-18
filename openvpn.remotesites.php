<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.openvpn.inc');
include_once('ressources/class.system.network.inc');
include_once('ressources/class.tcpip.inc');
include_once('ressources/class.mysql.inc');
$users=new usersMenus();
if(!$users->AsSystemAdministrator){die("alert('no access');");}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["remote-site"])){remote_site_edit();exit;}
if(isset($_GET["siteid"])){remote_site_save();exit;}
if(isset($_GET["remotesiteslist"])){echo remote_sitelist();exit;}
if(isset($_GET["delete-siteid"])){remote_site_delete();exit;}
if(isset($_GET["config-site"])){remote_site_config();exit;}
js();



function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{REMOTE_SITES_VPN}','index.openvpn.php');
	$ADD_REMOTE_SITES_VPN=$tpl->_ENGINE_parse_body('{ADD_REMOTE_SITES_VPN}','index.openvpn.php');
	$DOWNLOAD_CONFIG_FILES=$tpl->_ENGINE_parse_body('{DOWNLOAD_CONFIG_FILES}','users.openvpn.index.php');
	
	$page=CurrentPageName();
	
	$html="
	
	function RemoteVPNStart(){
		YahooWin3(600,'$page?popup=yes','$title');
	
	}
	
	function EditVPNRemoteSite(siteid){
		YahooWin4(500,'$page?remote-site='+siteid,'$ADD_REMOTE_SITES_VPN');
	
	}
	
	function VPNRemoteSiteRefreshList(){
		LoadAjax('remotesiteslist','$page?remotesiteslist=yes');
	}
	
	var x_EditOpenVPNSite= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);return;}
		YahooWin4Hide();
		VPNRemoteSiteRefreshList();
		
	}			
	
	function EditOpenVPNSite(){
		var XHR = new XHRConnection();
		XHR.appendData('siteid',document.getElementById('siteid').value);
		XHR.appendData('sitename',document.getElementById('sitename').value);
		XHR.appendData('IP_START',document.getElementById('IP_START').value);
		XHR.appendData('netmask',document.getElementById('netmask').value);
		XHR.sendAndLoad('$page', 'GET',x_EditOpenVPNSite);		
	}
	
	function RemoteVPNDelete(siteid){
		var XHR = new XHRConnection();
		XHR.appendData('delete-siteid',siteid);
		XHR.sendAndLoad('$page', 'GET',x_EditOpenVPNSite);	
	}
	
	function VPNRemoteSiteConfig(siteid){
		YahooWin5(500,'$page?config-site='+siteid,'$DOWNLOAD_CONFIG_FILES');
	}
	
	
	RemoteVPNStart();
	";
	
	echo $html;
	
}


function popup(){
	
	$add=Paragraphe('HomeAdd-64.png','{ADD_REMOTE_SITES_VPN}',
	'{ADD_REMOTE_SITES_VPN_TEXT}',"javascript:EditVPNRemoteSite('');",null,210,null,0,false);
	
	
	$list=remote_sitelist();
	
	$html="<H1>{REMOTE_SITES_VPN}</H1>
	<table style='width:100%'>
		<tr>
			<td valign='top' width=1%>$add</td>
			<td valign='top'><div id='remotesiteslist' style='width:100%;height:250px;overflow:auto'>$list</div></td>
		</tr>
	</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function remote_site_delete(){
	$sql="DELETE FROM vpnclient WHERE ID={$_GET["delete-siteid"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");	
}

function remote_site_edit(){
	$siteid=$_GET["remote-site"];
	
	if($siteid>0){
		$q=new mysql();
		$sql="SELECT * FROM vpnclient WHERE ID='$siteid'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	}
	
	$title=$ligne["sitename"];
	$button_title="{edit}";
	
	if($title==null){
		$title="{ADD_REMOTE_SITES_VPN}";
		$button_title="{add}";
	}
	
	$html="<H1>$title</H1>
	<input type='hidden' id='siteid' value='$siteid'>
	<table class=table_form>
		<tr>
			<td class=legend>{site_name}:</td>
			<td>". Field_text("sitename",$ligne["sitename"],"width:120px")."</td>
			<td></td>
		</tr>
		<tr>
			<td class=legend>{from_ip_address}:</td>
			<td>". Field_text("IP_START",$ligne["IP_START"],"width:90px")."</td>
			<td>10.1.1.0 {or} 192.168.2.0...</td>
		</tr>
		<tr>
			<td class=legend>{netmask}:</td>
			<td>". Field_text("netmask",$ligne["netmask"],"width:90px")."</td>
			<td>255.255.255.0 {or} 255.227.0.0...</td>
		</tr>	
		<tr>
			<td colspan=3 align='right'>
			<hr>
				<input type='button' OnClick=\"javascript:EditOpenVPNSite();\" value='$button_title&nbsp;&raquo'>
			</td>
		</tr>			
	</table>";
	
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'index.openvpn.php');
	
	
	
}

function remote_sitelist(){
	
	$sql="SELECT * FROM vpnclient WHERE connexion_type=1 ORDER BY sitename DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$html="<table style='width:99%'>";
	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$js="EditVPNRemoteSite('{$ligne["ID"]}');";
			$jsDownload="VPNRemoteSiteConfig('{$ligne["ID"]}');";
			$html=$html. "
			<tr ". CellRollOver($js).">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td nowrap>{$ligne["sitename"]}</td>
			<td nowrap>{$ligne["IP_START"]}</td>
			<td nowrap>{$ligne["netmask"]}</td>
			<td width=1%>". imgtootltip("ed_delete.gif","{delete}","RemoteVPNDelete('{$ligne["ID"]}')")."</td>
			</tr>
			<tr ". CellRollOver($jsDownload).">
				<td colspan=5 align='right' style='padding:5px'><strong>{DOWNLOAD_CONFIG_FILES}</strong></td>
			</tr>
				
			
			";
		
		}	
		
	$html=$html."</table>";
	
	$html=RoundedLightWhite($html);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html,"users.openvpn.index.php");
	
}



function remote_site_save(){
	$sitename=$_GET["sitename"];
	$IP_START=$_GET["IP_START"];
	$netmask=$_GET["netmask"];
	$siteid=$_GET["siteid"];
	$connexion_type=1;
	
	if($sitename==null){$error[]="{site_name}";}
	if($IP_START==null){$error[]="{from_ip_address}";}
	if($IP_START==null){$error[]="{netmask}";}
	
	if(count($error)>0){
		echo "{error}:".implode("\n",$error)." =NULL\n";
		exit;
	}
	
	
	if($siteid==0){
		$sql="INSERT INTO vpnclient (sitename,IP_START,netmask,connexion_type) VALUES('$sitename','$IP_START','$netmask',1)";
	}else{
		$sql="UPDATE vpnclient SET sitename='$sitename',IP_START='$IP_START',netmask='$netmask' WHERE ID='$siteid'";
	}
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	
	
	}
	
function remote_site_config(){
	$vpn=new openvpn();
	$siteid=$_GET["config-site"];
	
		$q=new mysql();
		$sql="SELECT * FROM vpnclient WHERE ID='$siteid'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));		
		$sitename=$ligne["sitename"];
		$sitename=str_replace(" ","_",$sitename);
		$sitename=strtolower($sitename);

	$config=$vpn->BuildClientconf($sitename);
	$uid=$sitename;
	$sock=new sockets();
	$sock->SaveConfigFile($config,"$uid.ovpn");
	//$datas=$sock->getfile('OpenVPNGenerate:'.$uid);	
	$datas=$sock->getFrameWork("openvpn.php?build-vpn-user=$uid&basepath=".dirname(__FILE__));
	$tbl=explode("\n",$datas);
	$tbl=array_reverse($tbl);
	while (list ($num, $line) = each ($tbl) ){
		if(trim($line)==null){continue;}
		$html=$html . "<div><code style='font-size:10px;color:black;'>" . htmlentities($line)."</code></div>";
		
	}
	
	if(is_file('ressources/logs/'.$uid.'.zip')){
		$download="
		<center>
			<a href='ressources/logs/".$uid.".zip'><img src='img/download-64.png' title=\"{DOWNLOAD_CONFIG_FILES}\" style='padding:8Px;border:1px solid #055447;margin:3px'></a>
		</center>
		";
		
	}
	
	$download=RoundedLightWhite($download)."<hr>";
	
	$html="
	<H1>{DOWNLOAD_CONFIG_FILES}</H1>
	
	$download
	<H3>{events}</H3>
	". RoundedLightWhite("<div style='width:100%;height:200px;overflow:auto'>$html</div>");
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}


?>