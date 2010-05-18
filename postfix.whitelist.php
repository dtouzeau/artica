<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');


	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["AutoBlockDenyAddWhiteList"])){AutoBlockDenyAddWhiteList();exit;}
	if(isset($_GET["PostfixAutoBlockDenyDelWhiteList"])){PostfixAutoBlockDenyDelWhiteList();exit;}
	if(isset($_GET["BlockDenyAddWhiteList"])){echo WhiteList();exit;}
	
js();



function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{PostfixAutoBlockDenyAddWhiteList}',"postfix.index.php");
	$PostfixAutoBlockDenyAddWhiteList_explain=$tpl->_ENGINE_parse_body('{PostfixAutoBlockDenyAddWhiteList_explain}');
	$prefix=str_replace(".",'_',$page);
	
	$html="
	
	function {$prefix}Start(){
	YahooWin2(650,'$page?popup=yes','$title');
	}
	
var x_AutoBlockDenyAddWhiteList= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
	LoadAjax('BlockDenyAddWhiteList','$page?BlockDenyAddWhiteList=yes');
}	
	
	function PostfixAutoBlockDenyAddWhiteList(){
		var server=prompt('$PostfixAutoBlockDenyAddWhiteList_explain');
		if(server){
			var XHR = new XHRConnection();
			XHR.appendData('AutoBlockDenyAddWhiteList',server);
			XHR.sendAndLoad('$page', 'GET',x_AutoBlockDenyAddWhiteList);
		}
	}

	function PostfixAutoBlockDenyDelWhiteList(server){
		var XHR = new XHRConnection();
		XHR.appendData('PostfixAutoBlockDenyDelWhiteList',server);
		XHR.sendAndLoad('$page', 'GET',x_AutoBlockDenyAddWhiteList);
	
	}	
	
	
	{$prefix}Start();
	";
	
	echo $html;
}

function popup(){
	
	$tpl=new templates();
	$PostfixAutoBlockDenyAddWhiteList=$tpl->_ENGINE_parse_body("{PostfixAutoBlockDenyAddWhiteList}","postfix.index.php");
	
		$add_whitelist=Paragraphe("64-bind9-add-zone.png","$PostfixAutoBlockDenyAddWhiteList","{PostfixAutoBlockDenyAddWhiteList_explain}",
		"javascript:PostfixAutoBlockDenyAddWhiteList();");
	
	$html="<H1>{PostfixAutoBlockDenyAddWhiteList}</H1>
	<table style='width:100%'>
	<tr>
	<td valign='top'>
	". RoundedLightWhite("<div style='width:100%;height:300px;overflow:auto' id='BlockDenyAddWhiteList'>".WhiteList()."</div>")."	
		
	</td>
	<td valign='top' width=2%>
	$add_whitelist
	</td>
	</tr>
	</table>
	
	";
	
	
	
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");		
}


function WhiteList(){
	$sock=new sockets();
	$datas=$sock->GET_INFO('PostfixAutoBlockWhiteList');
	$tpl=explode("\n",$datas);
	if(!is_array($tpl)){return null;}
	$html="<table style='width:100%'>";
	
	while (list ($num, $ligne) = each ($tpl) ){
		if($ligne==null){continue;}
		$html=$html . "<tr ". CellRollOver().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong style='font-size:12px'><code>$ligne</code></td>
		<td width=1%>" . imgtootltip("ed_delete.gif","{delete}","PostfixAutoBlockDenyDelWhiteList('$ligne')")."</td>
	</tr>";
		
		
	}
	$html=$html."</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
	
}
function AutoBlockDenyAddWhiteList(){
	if($_GET["AutoBlockDenyAddWhiteList"]==null){
		echo "NULL VALUE";
		return null;}
	
	$users=new usersMenus();
	$tpl=new templates();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "$error";
		die();
	}	
	
	$sock=new sockets();
	$datas=$sock->GET_INFO('PostfixAutoBlockWhiteList');
	$tpl=explode("\n",$datas);
	if(is_array($tpl)){
	while (list ($num, $ligne) = each ($tpl) ){
			if($ligne==null){continue;}
			$array[$ligne]=$ligne;
	}}
	
$array[$_GET["AutoBlockDenyAddWhiteList"]]=$_GET["AutoBlockDenyAddWhiteList"];
if(is_array($array)){
while (list ($num, $ligne) = each ($array) ){
		if($ligne==null){continue;}
			$conf=$conf .$ligne."\n";
}}

$sock->SaveConfigFile($conf,"PostfixAutoBlockWhiteList");
$sock->getFrameWork("cmd.php?smtp-whitelist=yes");

	
	
}
function PostfixAutoBlockDenyDelWhiteList(){
	$users=new usersMenus();
	$tpl=new templates();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "$error";
		die();
	}	
		
	$found=false;
	$server=$_GET["PostfixAutoBlockDenyDelWhiteList"];
	$sock=new sockets();
	$datas=$sock->GET_INFO('PostfixAutoBlockWhiteList');
	$tpl=explode("\n",$datas);
	if(is_array($tpl)){
	while (list ($num, $ligne) = each ($tpl) ){
			if($ligne==null){continue;}
			$array[$ligne]=$ligne;
	}}	
	
	if($array[$server]==null){
		echo " Unable to find $server in WhiteList";
		exit;
	}
	
	unset($array[$server]);	
	
if(is_array($array)){
while (list ($num, $ligne) = each ($array) ){
		if($ligne==null){continue;}
			$conf=$conf .$ligne."\n";
}}	

$sock->SaveConfigFile($conf,"PostfixAutoBlockWhiteList");
$sock->getFrameWork("cmd.php?smtp-whitelist=yes");
	
}
?>