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
	$normal_start_js="YahooWin2(650,'$page?popup=yes','$title');";
	
	
if(isset($_GET["white-js"])){
		$normal_start_js="YahooWin3(650,'$page?popup-white=yes','$title');";
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
		
		
		
	function PostfixEnableFwRule(field){
		var value=document.getElementById(field).value;
		var XHR = new XHRConnection();
		XHR.appendData('PostfixEnableFwRule',field);
		XHR.appendData('value',value);
		XHR.sendAndLoad('$page', 'GET',x_PostfixEnableFwRule);	
	}
	
	function PostfixEnableLog(field){
		var value=document.getElementById(field).value;
		var XHR = new XHRConnection();
		XHR.appendData('PostfixEnableLog',field);
		XHR.appendData('value',value);
		XHR.sendAndLoad('$page', 'GET',x_PostfixEnableFwRule);		
	
	}
	
	
	StartPostfixAutoBlockDeny();
	";
	echo $html;
	}
	
function PostfixAutoBlockParameters(){
	$users=new usersMenus();
	$tpl=new templates();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "$error";
		die();
	}	
	
	$ini=new Bs_IniHandler();
	$ini->_params["CONF"]["PostfixAutoBlockDays"]=$_GET["PostfixAutoBlockDays"];
	$ini->_params["CONF"]["PostfixAutoBlockEvents"]=$_GET["PostfixAutoBlockEvents"];
	$ini->_params["CONF"]["PostfixAutoBlockPeriod"]=$_GET["PostfixAutoBlockPeriod"];
	$sock=new sockets();
	$sock->SaveConfigFile($ini->toString(),"PostfixAutoBlockParameters");
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
	
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO("PostfixAutoBlockParameters"));
	if($ini->_params["CONF"]["PostfixAutoBlockDays"]==null){$ini->_params["CONF"]["PostfixAutoBlockDays"]=2;}
	if($ini->_params["CONF"]["PostfixAutoBlockEvents"]==null){$ini->_params["CONF"]["PostfixAutoBlockEvents"]=100;}
	if($ini->_params["CONF"]["PostfixAutoBlockPeriod"]==null){$ini->_params["CONF"]["PostfixAutoBlockPeriod"]=240;}
	
	
	
	$parameters="
	<div id='PostfixAutoBlockParameters'>
	<table style='width:240px'>
	<tr>
		<td class=legend nowrap>{PostfixAutoBlockDays}:</td>
		<td>" . Field_array_Hash($arr_day,"PostfixAutoBlockDays",$ini->_params["CONF"]["PostfixAutoBlockDays"])."</td>
		<td>" . help_icon("{PostfixAutoBlockDays_text}")."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{PostfixAutoBlockEvents}:</td>
		<td>" . Field_text("PostfixAutoBlockEvents",$ini->_params["CONF"]["PostfixAutoBlockEvents"],"width:40px")."</td>
		<td>" . help_icon("{PostfixAutoBlockEvents_text}")."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{PostfixAutoBlockPeriod}:</td>
		<td nowrap>" . Field_text("PostfixAutoBlockPeriod",$ini->_params["CONF"]["PostfixAutoBlockPeriod"],"width:40px")."&nbsp;{minutes}</td>
		<td>" . help_icon("{PostfixAutoBlockPeriod_text}")."</td>
	</tr>		
	<tr>
	<td colspan=3 align='right'><hr>
	<input type='button' OnClick=\"javascript:PostfixAutoBlockParameters();\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
	
	</table>
	</div>
	
	
	";
	$parameters=RoundedLightWhite($parameters);
	
	$form=Paragraphe_switch_img("{enable_postfix_autoblock}",
	"{enable_postfix_autoblock_text}",'EnablePostfixAutoBlock',$EnablePostfixAutoBlock,"{enable_disable}",250);
	
    $form="
    <div id='EnablePostfixAutoBlockDiv'>
			$form
		<div style='width:100%;text-align:right'>
			<input type='button' OnClick=\"javascript:EnablePostfixAutoBlockDeny()\" value='{edit}&nbsp;&raquo;&raquo;'>
		</div>
	</div>";
	
	$form=RoundedLightWhite($form);
	$PostfixAutoBlockDenyAddWhiteList=$tpl->_ENGINE_parse_body("{PostfixAutoBlockDenyAddWhiteList}","postfix.index.php");
	$add_whitelist=Paragraphe("64-bind9-add-zone.png","$PostfixAutoBlockDenyAddWhiteList","{PostfixAutoBlockDenyAddWhiteList_explain}",
	"javascript:PostfixAutoBlockDenyAddWhiteList();");
	
	$manage_fw=Paragraphe("folder-64-firewall.png","{PostfixAutoBlockManageFW}","{PostfixAutoBlockManageFW_text}",
	"javascript:PostfixAutoBlockLoadFW();");
	
	$compile=Paragraphe("system-64.png","{PostfixAutoBlockCompileFW}","{PostfixAutoBlockCompileFW_text}",
	"javascript:PostfixAutoBlockCompileFW();");
	
	
	
	
	
	$html="<H1>{postfix_autoblock}</H1>
	<p class=caption>{postfix_autoblock_explain}</p>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$form<hr></td>
		<td valign='top'>$parameters</td>
	</tr>
	</table>
	<table style='width:100%'>
	<tr>
	<td valign='top'>
	". RoundedLightWhite("<div style='width:100%;height:300px;overflow:auto' id='BlockDenyAddWhiteList'>".BlockDenyWhiteList()."</div>")."	
		
	</td>
	<td valign='top' width=2%>
	$add_whitelist
	$manage_fw
	$compile
	</td>
	</tr>
	</table>
	
	";
	
	
	
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
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

	$rules=firewall_rules();
	
	$rules=RoundedLightWhite("<div id='iptables_postfix_rules' style='width:100%;height:300px;overflow:auto'>$rules</div>");
	
$html="
<H1>{PostfixAutoBlockManageFW}</H1>
<table style='width:100%' class=table_form>
<tr>
	<td class=legend nowrap>{search}:</td>
	<td>" . Field_text('search_fw',null,"width:190px",null,null,null,null,"PostfixIptablesSearchKey(event)")."</td>
	<td align='right'><input type='button' OnClick=\"javascript:DeleteAllIptablesPostfixRules();\" value='{delete_all_items}&nbsp;&raquo;'></td>
</tR>
</table>
<br>

$rules
";
	
//empty_table_confirm
echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
	
}

function firewall_rules(){
	if($_GET["page"]==null){$_GET["page"]=0;}
	$cache_index=md5("{$_GET["page"]}{$_GET["search"]}");
	if(strlen($_SESSION["postfix_firewall_rules"][$cache_index])>0){return $_SESSION["postfix_firewall_rules"][$cache_index];}
	
	$iptables=new iptables_chains();
	$page=CurrentPageName();
	$results=$iptables->loadPostfix_chains(($_GET["page"]*50),$_GET["search"]);
	
	$max=$results[0];
	$pages=round($max/50)+1;

	for($i=0;$i<$pages;$i++){
		if($_GET["page"]==$i){$class="id=tab_current";}else{$class=null;}
		$tabs=$tabs . "<li><a href=\"javascript:LoadAjax('iptables_postfix_rules','$page?PostfixAutoBlockLoadFWRules=yes&page=$i&search={$_GET["search"]}')\" $class>$i</a></li>\n";
	}
	$tabs="
	<input type='hidden' id='postfix_firewall_page' value='{$_GET["postfix_firewall_page"]}'>
	<br><div id=tablist>$tabs</div><br>";
	
	$html="$tabs<table style='width:100%'>
	<tr>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th>{date}</th>
		<th>{server}</th>
		<th>{enable}</th>
		<th>{log}</th>
		<th>{delete}</th>
	</tr>
	";
	
//servername,serverip,local_port,disable,events_number,rule_string,rulemd5,flu	
	
while($ligne=@mysql_fetch_array($results[1],MYSQL_ASSOC)){
		$disable=Field_numeric_checkbox_img("disable_{$ligne["rulemd5"]}",$ligne["disable"],"{enable_disable}","PostfixEnableFwRule");
		$log=Field_numeric_checkbox_img("log_{$ligne["rulemd5"]}",$ligne["log"],"{enable_disable}","PostfixEnableLog");
		$delete=imgtootltip("ed_delete.gif","{delete}","PostfixIptableDelete('{$ligne["rulemd5"]}')");
		$ligne["saved_date"]=str_replace(date('Y-'),'',$ligne["saved_date"]);
		$ligne["saved_date"]=str_replace(date('m-d'),'',$ligne["saved_date"]);
		
		if($ligne["disable"]==1){$block="{allow}";}else{$block="{block}";}
		
		$html=$html . "
		<tr " . CellRollOver().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td nowrap><strong>{$ligne["saved_date"]}</strong></td>
		<td>$block</td>
		<td><strong>". texttooltip($ligne["servername"],"{$ligne["serverip"]}<br>{$ligne["events_number"]} {events}",null,null,0,"font-size:12px")."</strong></td>
		<td width=1%>$disable</td>
		<td width=1%>$log</td>
		<td width=1%>$delete</td>
		</td>
		</tr>";
		}
		
$html=$html."</table>";
$tpl=new templates();
$page=$tpl->_ENGINE_parse_body($html,"postfix.index.php");
$_SESSION["postfix_firewall_rules"][$cache_index]=$page;
return $page;		
	
}


function save(){
	$sock=new sockets();
	$sock->SET_INFO('EnablePostfixAutoBlock',$_GET["EnablePostfixAutoBlock"]);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
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
	if(preg_match("#disable_(.+)#",$_GET["PostfixEnableFwRule"],$re)){
		$rulemd=trim($re[1]);
	}
	
	$sql="UPDATE iptables SET disable={$_GET["value"]} WHERE rulemd5='$rulemd'";
	writelogs($sql,__FUNCTION__,__FILE__);
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	unset($_SESSION["postfix_firewall_rules"]);
	
}

function PostfixEnableLog(){
	if(preg_match("#log_(.+)#",$_GET["PostfixEnableLog"],$re)){
		$rulemd=trim($re[1]);
	}
	
	$sql="UPDATE iptables SET log={$_GET["value"]} WHERE rulemd5='$rulemd'";
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