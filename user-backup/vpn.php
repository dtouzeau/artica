<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.functions.inc");
include_once(dirname(__FILE__)."/ressources/class.openvpn.inc");
if(!isset($_SESSION["uid"])){header("Location: session-fermee.php");die();}
if(isset($_GET["config-js"])){config_js();exit;}
if(isset($_GET["config-popup"])){config_popup();exit;}
$tpl=new templates();

$page=CurrentPageName();
//users.openvpn.index.php

$downloadvinvin=iconTable("download-64.png",
		"{DOWNLOAD_OPENVPN_CLIENT}",
		"{DOWNLOAD_OPENVPN_CLIENT_TEXT}",
		"s_PopUp('http://www.artica.fr/download/openvpn-2.0.9-gui-1.0.3-install.exe')",
		"{DOWNLOAD_OPENVPN_CLIENT_TEXT}");


$build=iconTable("user-config-download-64.png",
		"{DOWNLOAD_CONFIG_FILES}",
		"{DOWNLOAD_CONFIG_FILES_TEXT}",
		"Loadjs('$page?config-js=yes')",
		"{DOWNLOAD_CONFIG_FILES}");

	
	

$html="
<H2>{WELCOME_USER_OPENVPN}</H2>
<table class=table_form>
<tr>
<td valign='top'>
		<table style='width:95%'>
			<tr>
				<td>$downloadvinvin</td>
			</tr>
			<tr>
				<td>$build</td>
			</tr>
		</table>
</td>
</tr>
</table>


";

echo $tpl->_ENGINE_parse_body($html);


function config_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{DOWNLOAD_CONFIG_FILES}');
	$html="YahooWin('600','$page?config-popup=yes','$title');";
	echo $html;
}

function config_popup(){
	$vpn=new openvpn();
	$config=$vpn->BuildClientconf($_SESSION["uid"]);
	$tbconfig=explode("\n",$config);
	$html_logs[]=htmlentities("VPN config -> ". strlen($config)." bytes length (".count($tbconfig)." lines)");
	$uid=$_SESSION["uid"];
	writelogs("VPN config -> ". strlen($config)." bytes length (".count($tbconfig)." lines)",__FUNCTION__,__FILE__,__LINE__);
	$sock=new sockets();
	if(!$sock->SaveConfigFile($config,"$uid.ovpn")){
		$html_logs[]=htmlentities("Framework error while saving  -> $uid.ovpn;". strlen($config)." bytes length (".count($tbconfig)." lines)");
	}
	
	writelogs("sockets() OK",__FUNCTION__,__FILE__,__LINE__);
	
	//$datas=$sock->getfile('OpenVPNGenerate:'.$uid);	
	$datas=$sock->getFrameWork("openvpn.php?build-vpn-user={$_SESSION["uid"]}&basepath=".dirname(__FILE__));
	$tbl=explode("\n",$datas);
	$tbl=array_reverse($tbl);
	while (list ($num, $line) = each ($tbl) ){
		if(trim($line)==null){continue;}
		$html_logs[]="<div><code style='font-size:10px;color:black;'>" . htmlentities($line)."</code></div>";
		
	}
	
	if(is_file('ressources/logs/'.$uid.'.zip')){
		$download="
		<center>
			<a href='ressources/logs/".$uid.".zip'><img src='img/download-64.png' title=\"{DOWNLOAD_CONFIG_FILES}\" style='padding:8Px;border:1px solid #055447;margin:3px'></a>
		</center>
		";
		
	}
	
	$html="
	
	$download
	<H3>{events}</H3>
	". ParagrapheTXT("<div style='width:100%;height:200px;overflow:auto'>". implode("\n",$html_logs)."</div>");
	
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}


?>