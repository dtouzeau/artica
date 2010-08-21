<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.iptables-chains.inc');
	
	

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["EnablePostfixAutoBlock"])){save();exit;}
if(isset($_GET["BlockDenyAddWhiteList"])){echo BlockDenyWhiteList();exit;}
if(isset($_GET["AutoBlockDenyAddWhiteList"])){AutoBlockDenyAddWhiteList();exit;}
if(isset($_GET["PostfixAutoBlockDenyDelWhiteList"])){PostfixAutoBlockDenyDelWhiteList();exit;}
if(isset($_GET["PostfixAutoBlockDays"])){PostfixAutoBlockParameters();exit;}
if(isset($_GET["PostfixAutoBlockLoadFW"])){firewall_popup();exit;}
if(isset($_GET["PostfixAutoBlockLoadFWRules"])){echo firewall_rules();exit;}
if(isset($_GET["PostfixEnableFwRule"])){PostfixEnableFwRule();exit;}
if(isset($_GET["PostfixEnableLog"])){PostfixEnableLog();exit;}
if(isset($_GET["compile"])){PostfixAutoBlockCompile();exit;}
if(isset($_GET["compileCheck"])){PostfixAutoBlockCompileCheck();exit;}
if(isset($_GET["DeleteSMTPIptableRule"])){firewall_delete_rule();exit;}
if(isset($_GET["popup-white"])){popup_white();exit;}
if(isset($_GET["DeleteSMTPAllIptableRules"])){firewall_delete_all_rules();exit;}
if(isset($_GET["PostfixAutoBlockParameters"])){popup_parameters();exit;}
if(isset($_GET["PostfixAutoBlockParametersSave"])){popup_parameters_save();exit;}

if(isset($_GET["firewall-rules-list"])){firewall_rules();exit;}

js();

function firewall_delete_rule(){
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=html_entity_decode($tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}"));
		echo "$error";
		die();
	}

	$iptables_chains=new iptables_chains();
	if(!$iptables_chains->deletePostfix_chain($_GET["DeleteSMTPIptableRule"])){
		echo $iptables_chains->error;
		return false;
	}
	
	unset($_SESSION["postfix_firewall_rules"]);
	$tpl=new templates();
	echo html_entity_decode($tpl->_ENGINE_parse_body("{success}\n{delete_not_forget_to_compile}"));
	
}

function firewall_delete_all_rules(){
$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=html_entity_decode($tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}"));
		echo "$error";
		die();
	}	
	
	$iptables_chains=new iptables_chains();
	
	if(!$iptables_chains->deleteAllPostfix_chains()){
			echo $iptables_chains->error;
			return false;
		}
	
	unset($_SESSION["postfix_firewall_rules"]);
	$tpl=new templates();
	echo html_entity_decode($tpl->_ENGINE_parse_body("{success}\n{delete_not_forget_to_compile}"));
}


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	
	$title=$tpl->_ENGINE_parse_body('{postfix_autoblock}',"postfix.index.php");
	$title2=$tpl->_ENGINE_parse_body('{PostfixAutoBlockManageFW}',"postfix.index.php");
	$title_compile=$tpl->_ENGINE_parse_body('{PostfixAutoBlockCompileFW}',"postfix.index.php");
	$normal_start_js="YahooWin2(490,'$page?popup=yes','$title');";
	$PostfixAutoBlockParameters=$tpl->_ENGINE_parse_body("{PostfixAutoBlockParameters}");
	
	
if(isset($_GET["white-js"])){
		$normal_start_js="YahooWin3(490,'$page?popup-white=yes','$title');";
	}
	
	$prefix="PostfixAutoBlockjs";
	$PostfixAutoBlockDenyAddWhiteList_explain=$tpl->_ENGINE_parse_body('{PostfixAutoBlockDenyAddWhiteList_explain}');
	$empty_table_confirm=html_entity_decode($tpl->_ENGINE_parse_body('{empty_table_confirm}'));
	$html="
var {$prefix}timerID  = null;
var {$prefix}tant=0;
var {$prefix}reste=0;

	function {$prefix}demarre(){
		{$prefix}tant = {$prefix}tant+1;
		{$prefix}reste=20-{$prefix}tant;
		if(!YahooWin4Open()){return false;}
		if ({$prefix}tant < 5 ) {                           
		{$prefix}timerID = setTimeout(\"{$prefix}demarre()\",1000);
	      } else {
				{$prefix}tant = 0;
				{$prefix}CheckProgress();
				{$prefix}demarre();                                
	   }
	}	
	
	
	function StartPostfixAutoBlockDeny(){
		$normal_start_js
	}
	
	function PostfixAutoBlockLoadFW(){
		YahooWin3(650,'$page?PostfixAutoBlockLoadFW=yes','$title2');
	}
	
	function PostfixAutoBlockCompileFW(){
		YahooWin4(500,'$page?compile=yes','$title_compile');
		setTimeout('PostfixAutoBlockStartCompile()',1000);		
	}
	
	function PostfixAutoBlockStartCompile(){
		{$prefix}CheckProgress();
		{$prefix}demarre();       
	}
	
	var x_{$prefix}CheckProgress= function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('PostfixAutoBlockCompileStatusCompile').innerHTML=tempvalue;
	}	
	
	function {$prefix}CheckProgress(){
			var XHR = new XHRConnection();
			XHR.appendData('compileCheck','yes');
			XHR.sendAndLoad('$page', 'GET',x_{$prefix}CheckProgress);
	
	}

	
	
	
	
var x_EnablePostfixAutoBlock= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
	StartPostfixAutoBlockDeny();
}
	
	function EnablePostfixAutoBlockDeny(){
		var EnablePostfixAutoBlock=document.getElementById('EnablePostfixAutoBlock').value;
		document.getElementById('EnablePostfixAutoBlockDiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		var XHR = new XHRConnection();
		XHR.appendData('EnablePostfixAutoBlock',EnablePostfixAutoBlock);
		XHR.sendAndLoad('$page', 'GET',x_EnablePostfixAutoBlock);	
	
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
	
	function PostfixAutoBlockParameters(){
		var XHR = new XHRConnection();
		XHR.appendData('PostfixAutoBlockDays',document.getElementById('PostfixAutoBlockDays').value);
		XHR.appendData('PostfixAutoBlockEvents',document.getElementById('PostfixAutoBlockEvents').value);
		XHR.appendData('PostfixAutoBlockPeriod',document.getElementById('PostfixAutoBlockPeriod').value);
		document.getElementById('PostfixAutoBlockParameters').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_EnablePostfixAutoBlock);
	
	}	
	
	var x_PostfixIptableDelete= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}	
		PostfixIptablesSearch();
	}	
	
	function PostfixIptableDelete(key){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteSMTPIptableRule',key);
		document.getElementById('iptables_postfix_rules').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_PostfixIptableDelete);
		}
		
	function DeleteAllIptablesPostfixRules(){
		if(confirm('$empty_table_confirm')){
			var XHR = new XHRConnection();
			XHR.appendData('DeleteSMTPAllIptableRules','yes');
			document.getElementById('iptables_postfix_rules').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_PostfixIptableDelete);		
		}
	}
	
	
	function PostfixIptablesSearchKey(e){
			if(checkEnter(e)){
				PostfixIptablesSearch();
			}
	}
	
	function PostfixIptablesSearch(){
		var pattern=document.getElementById('search_fw').value;
		LoadAjax('iptables_postfix_rules','$page?PostfixAutoBlockLoadFWRules=yes&search='+pattern);
		}
		
var x_PostfixEnableFwRule= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}
	}		
		
			
	function PostfixEnableLog(ID){
		var XHR = new XHRConnection();
		XHR.appendData('ID',ID)
		if(document.getElementById('log_'+ID).checked){XHR.appendData('PostfixEnableLog',1);}else{XHR.appendData('PostfixEnableLog',0);}
		XHR.sendAndLoad('$page', 'GET',x_PostfixEnableFwRule);	
	}
	
	function FirewallDisableSMTPRUle(ID){
		var XHR = new XHRConnection();
		XHR.appendData('ID',ID)
		if(document.getElementById('enabled_'+ID).checked){XHR.appendData('PostfixEnableFwRule',0);}else{XHR.appendData('PostfixEnableFwRule',1);}
		XHR.sendAndLoad('$page', 'GET',x_PostfixEnableFwRule);
	}
	
	function PostfixAutoBlockParameters(){
		YahooWin4('550','$page?PostfixAutoBlockParameters=yes','$PostfixAutoBlockParameters');
	
	}
	
	
	StartPostfixAutoBlockDeny();
	";
	echo $html;
	}
	

	
	
function popup(){
	$users=new usersMenus();
	$tpl=new templates();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "<H3>$error<H3>";
		die();
	}
	
	
	for($i=0;$i<91;$i++){
		$arr_day[$i]=$i;
		
	}
	
	$sock=new sockets();
	$EnablePostfixAutoBlock=$sock->GET_INFO("EnablePostfixAutoBlock");
	
		
	$form=Paragraphe_switch_img("{enable_postfix_autoblock}",
	"{enable_postfix_autoblock_text}",'EnablePostfixAutoBlock',$EnablePostfixAutoBlock,"{enable_disable}",450);
	
    $form="
    <div id='EnablePostfixAutoBlockDiv'>
			$form
		<div style='width:100%;text-align:right'>
			". button("{apply}","javascript:EnablePostfixAutoBlockDeny()")."
		</div>
	</div>";
	

	$PostfixAutoBlockDenyAddWhiteList=$tpl->_ENGINE_parse_body("{PostfixAutoBlockDenyAddWhiteList}","postfix.index.php");
	$add_whitelist=Paragraphe("64-bind9-add-zone.png","$PostfixAutoBlockDenyAddWhiteList","{PostfixAutoBlockDenyAddWhiteList_explain}",
	"javascript:PostfixAutoBlockDenyAddWhiteList();");
	
	$manage_fw=Paragraphe("folder-64-firewall.png","{PostfixAutoBlockManageFW}","{PostfixAutoBlockManageFW_text}",
	"javascript:PostfixAutoBlockLoadFW();");
	
	$compile=Paragraphe("system-64.png","{PostfixAutoBlockCompileFW}","{PostfixAutoBlockCompileFW_text}",
	"javascript:PostfixAutoBlockCompileFW();");
	
	$parameters=Paragraphe("64-parameters.png","{PostfixAutoBlockParameters}","{PostfixAutoBlockParameters_text}",
	"javascript:PostfixAutoBlockParameters();");	
	
	
	
	$html="
	<p style='font-size:13px'>{postfix_autoblock_explain}</p>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$form<hr></td>
	</tr>
	<tr>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
			<td>$parameters</td>
			<td>$add_whitelist</td>
			
		</tr>
		<tr>
			<td>$manage_fw</td>
			<td>$compile</td>
		</tr>
		</table>
	</td>
	</table>

	";
	
	
	
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
}

function popup_parameters(){
	
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PostfixAutoBlockParameters")));
	$page=CurrentPageName();
	
if($array["NAME_SERVICE_NOT_KNOWN"]<1){$array["NAME_SERVICE_NOT_KNOWN"]=10;}
if($array["SASL_LOGIN"]<1){$array["SASL_LOGIN"]=15;}
if($array["RBL"]<1){$array["RBL"]=5;}
if($array["USER_UNKNOWN"]<1){$array["USER_UNKNOWN"]=10;}
if($array["BLOCKED_SPAM"]<1){$array["BLOCKED_SPAM"]=5;}

$html="
<div id='PostfixAutoBlockParameters_id'>
<table style='width:100%'>
<tr>
	<td class=legend style='font-size:13px'>{SMTPHACK_NAME_SERVICE_NOT_KNOWN}:</td>
	<td>". Field_text("NAME_SERVICE_NOT_KNOWN",$array["NAME_SERVICE_NOT_KNOWN"],"font-size:13px;padding:3px;width:45px")."</td>
</tr>
<tr>
	<td class=legend style='font-size:13px'>{SMTPHACK_SASL_LOGIN}:</td>
	<td>". Field_text("SASL_LOGIN",$array["SASL_LOGIN"],"font-size:13px;padding:3px;width:45px")."</td>
</tr>
<tr>
	<td class=legend style='font-size:13px'>{SMTPHACK_RBL}:</td>
	<td>". Field_text("RBL",$array["RBL"],"font-size:13px;padding:3px;width:45px")."</td>
</tr>
<tr>
	<td class=legend style='font-size:13px'>{SMTPHACK_USER_UNKNOWN}:</td>
	<td>". Field_text("USER_UNKNOWN",$array["USER_UNKNOWN"],"font-size:13px;padding:3px;width:45px")."</td>
</tr>
<tr>
	<td class=legend style='font-size:13px'>{SMTPHACK_BLOCKED_SPAM}:</td>
	<td>". Field_text("BLOCKED_SPAM",$array["BLOCKED_SPAM"],"font-size:13px;padding:3px;width:45px")."</td>
</tr>
<tr>
	<td colspan=2 align='right'><hr>". button("{apply}","PostfixAutoBlockParametersSave()")."</td>
</tr>
</table>
</div>
<script>
	var x_PostfixAutoBlockParametersSave= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			YahooWin4Hide();
	}			
		
function PostfixAutoBlockParametersSave(){
	var XHR = new XHRConnection();
	XHR.appendData('PostfixAutoBlockParametersSave','yes');
	XHR.appendData('NAME_SERVICE_NOT_KNOWN',document.getElementById('NAME_SERVICE_NOT_KNOWN').value);
	XHR.appendData('SASL_LOGIN',document.getElementById('SASL_LOGIN').value);
	XHR.appendData('RBL',document.getElementById('RBL').value);	
	XHR.appendData('USER_UNKNOWN',document.getElementById('USER_UNKNOWN').value);
	XHR.appendData('BLOCKED_SPAM',document.getElementById('BLOCKED_SPAM').value);
	document.getElementById('PostfixAutoBlockParameters_id').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_PostfixAutoBlockParametersSave);	
	}
</script>

";

		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function popup_parameters_save(){
	$sock=new sockets();
	$datas=base64_encode(serialize($_GET));
	$sock->SaveConfigFile($datas,"PostfixAutoBlockParameters");
	$sock->getFrameWork("cmd.php?smtp-hack-reconfigure=yes");
}


function popup_white(){
	
	$tpl=new templates();
	$PostfixAutoBlockDenyAddWhiteList=$tpl->_ENGINE_parse_body("{PostfixAutoBlockDenyAddWhiteList}","postfix.index.php");
	
		$add_whitelist=Paragraphe("64-bind9-add-zone.png","$PostfixAutoBlockDenyAddWhiteList","{PostfixAutoBlockDenyAddWhiteList_explain}",
		"javascript:PostfixAutoBlockDenyAddWhiteList();");
	
	$html="<H1>{PostfixAutoBlockDenyAddWhiteList}</H1>
	<table style='width:100%'>
	<tr>
	<td valign='top'>
	". RoundedLightWhite("<div style='width:100%;height:300px;overflow:auto' id='BlockDenyAddWhiteList'>".BlockDenyWhiteList()."</div>")."	
		
	</td>
	<td valign='top' width=2%>
	$add_whitelist
	</td>
	</tr>
	</table>
	
	";
	
	
	
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");		
	
}

function firewall_popup(){
	unset($_SESSION["postfix_firewall_rules"]);
	$users=new usersMenus();
	$tpl=new templates();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "<H3>$error<H3>";
		die();
	}

	
	$page=CurrentPageName();
	
	
$html="
<table>
<tr>
	<td class=legend nowrap>{search}:</td>
	<td>" . Field_text('search_fw',null,"width:190px;font-size:13px;padding:3px",null,null,null,null,"PostfixIptablesSearchKey(event)")."</td>
</tR>
</table>
<br>
<div id='iptables_postfix_rules' style='width:100%;height:300px;overflow:auto'></div>
<hr>
<div style='text-align:right'><input type='button' OnClick=\"javascript:DeleteAllIptablesPostfixRules();\" value='{delete_all_items}&nbsp;&raquo;'></div>
<script>
	LoadAjax('iptables_postfix_rules','$page?firewall-rules-list=yes');
</script>
";
	
//empty_table_confirm
echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
	
}


function firewall_rules(){
	$q=new mysql();
	$sql_count="SELECT COUNT(*) AS tcount FROM iptables WHERE local_port=25 AND flux='INPUT'{$sql_search}";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql_count,"artica_backup"));
	$max=$ligne["tcount"];
	
	
	if($limit==null){$limit=0;}
	
	if($_GET["search"]<>null){
		$_GET["search"]=$_GET["search"]."*";
		$_GET["search"]=str_replace("**","*",$_GET["search"]);
		$_GET["search"]=str_replace("*","%",$_GET["search"]);
		if(preg_match("#([a-zA-Z]+)#",$_GET["search"])){
			$sql_search="AND servername LIKE '{$_GET["search"]}' ";
		}else{
			$sql_search="AND serverip LIKE '{$_GET["search"]}' ";
		}
	}
	
	$sql="SELECT * FROM iptables WHERE local_port=25 AND flux='INPUT' {$sql_search}ORDER BY ID DESC LIMIT $limit,200";
	writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
	$q=new mysql();	
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	$html="
	<div style='text-align:right'>". imgtootltip("refresh-32.png","{refresh}","PostfixIptablesSearch()")."</div>
	<table style='width:100%'>
	<tr>
		<th>&nbsp;</th>
		<th>{date}</th>
		<th>{server}</th>
		<th>{enable}</th>
		<th>{log}</th>
		<th>&nbsp;</th>
	</tr>";
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["servername"]==null){$ligne["servername"]=$ligne["serverip"];}
		
		$disable=Field_checkbox("enabled_{$ligne["ID"]}",0,$ligne["disable"],"FirewallDisableSMTPRUle('{$ligne["ID"]}')");
		$log=Field_checkbox("log_{$ligne["ID"]}",1,$ligne["log"],"PostfixEnableLog('{$ligne["ID"]}')");
		$delete=imgtootltip("ed_delete.gif","{delete}","PostfixIptableDelete('{$ligne["rulemd5"]}')");
		$ligne["events_block"]="<div style=font-size:13px>".nl2br($ligne["events_block"])."</div>";
		
		$html=$html . "
		<tr " . CellRollOver().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td  width=1% nowrap><strong style='font-size:13px'>{$ligne["saved_date"]}</strong></td>
		<td><strong style='font-size:13px'><code>". texttooltip("{$ligne["servername"]}",$ligne["events_block"],null,null,0,"font-size:13px")."</strong></code></td>
		<td width=1%>$disable</td>
		<td width=1%>$log</td>
		<td width=1%>$delete</td>
		</td>
		</tr>";
		
	}
	$html=$html."</table>
	
	";
	
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function save(){
	$sock=new sockets();
	$sock->SET_INFO('EnablePostfixAutoBlock',$_GET["EnablePostfixAutoBlock"]);
	
}

function BlockDenyWhiteList(){
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
$sock->getFrameWork("cmd.php?reconfigure-postfix=yes");

	
	
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

function PostfixEnableFwRule(){
	
	$sql="UPDATE iptables SET disable={$_GET["PostfixEnableFwRule"]} WHERE ID='{$_GET["ID"]}'";
	writelogs($sql,__FUNCTION__,__FILE__);
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	unset($_SESSION["postfix_firewall_rules"]);
	
}

function PostfixEnableLog(){
	$sql="UPDATE iptables SET log={$_GET["PostfixEnableLog"]} WHERE ID='{$_GET["ID"]}'";
	writelogs($sql,__FUNCTION__,__FILE__);
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	unset($_SESSION["postfix_firewall_rules"]);	
	
}

function PostfixAutoBlockCompile(){
	
	$html="<H1>{PostfixAutoBlockCompileFW}</H1>
	<p class=caption>{PostfixAutoBlockCompileFW_text}</p>
	<div id='PostfixAutoBlockCompileStatusCompile'>
	</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'postfix.index.php');
	
	$sock=new sockets();
	$sock->getfile('PostfixAutoBlockCompile');
	
}

function PostfixAutoBlockCompileCheck(){
	
	$ini=new Bs_IniHandler();
	$ini->loadFile("ressources/logs/compile.iptables.progress");
	$pourc=$ini->get("PROGRESS","pourc");
	$text=$ini->get("PROGRESS","text");
	
	
$color="#5DD13D";	
$html="
<center>
<div style='width:96%;text-align:center;font-size:12px;font-weight:bold;margin:5px;background-color:white;padding:5px;border:1px solid #CCCCCC'>
	<div style='width:95%;text-align:center;font-size:12px;font-weight:bold;margin:5px'>$text</div>
	<div style='width:100%;border:1px dotted #CCCCCC'>
	<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color;'>
		<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>{$pourc}%</strong></center>
	</div>
	</div>
</div>
</center>
";	
$html=RoundedLightWhite($html);
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
}






?>