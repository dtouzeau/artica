<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.cyrus.inc');
include_once('ressources/class.main_cf.inc');
include_once('ressources/charts.php');
include_once('ressources/class.syslogs.inc');
include_once('ressources/class.system.network.inc');
include_once('ressources/class.os.system.inc');

//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$users=new usersMenus();
if($users->AsArticaAdministrator==true or $users->AsPostfixAdministrator or $user->AsSquidAdministrator){}else{header('location:users.index.php');exit;}
if(isset($_GET["StartStopService-js"])){StartStopService_js();exit;}
if(isset($_GET["StartStopService-popup"])){StartStopService_popup();exit;}
if(isset($_GET["StartStopService-perform"])){StartStopService_perform();exit;}
if(isset($_GET["postfix-status-right"])){echo status_postfix();exit;}

if(isset($_GET["graph"])){graph();exit;}
if(isset($_GET["start-all-services"])){START_ALL_SERVICES();exit;}
if($_GET["status"]=="left"){status_left();exit;}
if($_GET["status"]=="right"){status_right();exit;}



if(isset($_GET["postfix-status"])){POSTFIX_STATUS();exit;}
if(isset($_GET["AdminDeleteAllSqlEvents"])){warnings_delete_all();exit;}
if(isset($_GET["ShowFileLogs"])){ShowFileLogs();exit;}
if(isset($_GET["buildtables"])){CheckTables();exit;}
if(isset($_GET["CheckDaemon"])){CheckDaemon();exit;}
if(isset($_GET["EmergencyStart"])){EmergencyStart();exit;}
if(isset($_GET["memcomputer"])){status_computer();exit;}
if(isset($_GET["mem-dump"])){status_memdump();exit;}
if(isset($_GET["memory-status"])){status_memdump_js();exit;}

if(isset($_GET["js-left"])){js_left();exit;}
if(isset($_GET["js-left-popup"])){js_left_popup();exit;}
if(isset($_GET["js-left-status"])){echo js_left_status();exit;}
if(isset($_GET["js-right"])){echo js_right();exit;}
if(isset($_GET["js-right-popup"])){js_right_popup();exit;}
if(isset($_GET["js-right-status"])){echo js_right_status();exit;}


page($users);



function StartStopService_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{$_GET["apps"]}");
	$apps=base64_encode($title);
	$html="
		function StartStopServiceStart(){
			YahooLogWatcher(550,'$page?StartStopService-popup=yes&cmd={$_GET["cmd"]}&typ={$_GET["typ"]}&apps=$apps','$title');
		}
	
	
	
	StartStopServiceStart()";
	
	echo $html;
}

function StartStopService_popup(){
	$page=CurrentPageName();
	if($_GET["typ"]==1){$title='{starting}';}else{$title="{stopping}";}
	$html="
	<H1>$title: ".base64_decode($_GET["apps"])."</h1>
	<div style='padding:3px;margin:3px;font-size:11px;width:100%;height:450px;overflow:auto' id='StartStopService_popup'>
	</div>
	
	<script>
		LoadAjax('StartStopService_popup','$page?StartStopService-perform=yes&cmd={$_GET["cmd"]}&typ={$_GET["typ"]}');
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function StartStopService_perform(){
	$cmd=$_GET["cmd"];
	$typ=$_GET["typ"];
	$sock=new sockets();
	if($typ==1){
		$datas=$sock->getFrameWork("cmd.php?start-service-name=$cmd");
	}else{
		$datas=$sock->getFrameWork("cmd.php?stop-service-name=$cmd");
	}
	
	$tbl=unserialize(base64_decode($datas));
	$html="<table style='width:100%'>";
	while (list ($num, $ligne) = each ($tbl) ){
			if(trim($ligne==null)){continue;}
			$html=$html . "
			<tr>
				<td width=1%>
					<img src='img/fw_bold.gif'>
				</td>
				<td style='font-size:12px'>" . htmlentities($ligne)."</td>
			</tr>
			";
			
		
	}
	

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html. "</table>");	
	
	
}

function js_right(){
$page=CurrentPageName();
$idmd=str_replace('.','_',$page).'_right';
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{refresh}::{status}');

$html="
var {$idmd}timerID  = null;
var {$idmd}timerID1  = null;
var {$idmd}tant=0;
var {$idmd}TIMEOUT=0;

	function {$idmd}demarre(){
		if(document.getElementById('js-right-id')){
			var pourc=document.getElementById('js-right-id').value;
			if(pourc>99){
				{$idmd}ChargeLogs();
				Demarre();
				RTMMailHide();
				return;
			}}
	
		if(!RTMMailOpen()){return;}
		{$idmd}tant = {$idmd}tant+1;	
		if ({$idmd}tant < 5 ) {                           
			{$idmd}timerID = setTimeout(\"{$idmd}demarre()\",700);
	      } else {
			{$idmd}tant = 0;
			{$idmd}ChargeLogs();
			{$idmd}demarre();                                
	   }
	}
	
	function {$idmd}Charge(){
		RTMMail(500,'$page?js-right-popup=yes','$title');
		{$idmd}ChargeTimeout();
	}
	
	function {$idmd}ChargeTimeout(){ 
		{$idmd}TIMEOUT={$idmd}TIMEOUT+1;
		if({$idmd}TIMEOUT>20){
			alert('div-refresh-index: time-out');
			return;
		}
		if(!document.getElementById('progression_js_right')){
			setTimeout(\"{$idmd}ChargeTimeout()\",900);
			return;
		}
		{$idmd}TIMEOUT={$idmd}TIMEOUT=0;
		{$idmd}demarre();
		{$idmd}ChargeLogs();
	}
	
	var x_{$idmd}ChargeLogs= function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('progression_js_right').innerHTML=tempvalue;
		}		
	
	function {$idmd}ChargeLogs(){
		var XHR = new XHRConnection();
		XHR.appendData('js-right-status','yes');
		XHR.sendAndLoad('$page', 'GET',x_{$idmd}ChargeLogs);	
	}
	
	{$idmd}Charge();";
	
	echo $html;
}

function js_left(){
$page=CurrentPageName();
$idmd=str_replace('.','_',$page).'_left';
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{refresh}::{status}');

$html="
var {$idmd}timerID  = null;
var {$idmd}timerID1  = null;
var {$idmd}tant=0;
var {$idmd}TIMEOUT=0;

	function {$idmd}demarre(){
			
		if(document.getElementById('js-left-id')){
			var pourc=document.getElementById('js-left-id').value;
			if(pourc>99){
				LoadAjax('left_status','$page?status=left&hostname={$_GET["hostname"]}');
				RTMMailHide();
				return;
			}}
	
		if(!RTMMailOpen()){return;}
		{$idmd}tant = {$idmd}tant+1;	
		if ({$idmd}tant < 5 ) {                           
			{$idmd}timerID = setTimeout(\"{$idmd}demarre()\",800);
	      } else {
			{$idmd}tant = 0;
			{$idmd}ChargeLogs();
			{$idmd}demarre();                                
	   }
	}
	
	function {$idmd}Charge(){

		RTMMail(500,'$page?js-left-popup=yes','$title');
		{$idmd}ChargeTimeout();
	}
	
	function {$idmd}ChargeTimeout(){ 
		{$idmd}TIMEOUT={$idmd}TIMEOUT+1;
		if({$idmd}TIMEOUT>20){
			alert('div-refresh-index: time-out');
			return;
		}
		if(!document.getElementById('progression_js_left')){
			setTimeout(\"{$idmd}ChargeTimeout()\",900);
			return;
		}
		{$idmd}TIMEOUT={$idmd}TIMEOUT=0;
		{$idmd}demarre();
		{$idmd}ChargeLogs();
	}
	
	var x_{$idmd}ChargeLogs= function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('progression_js_left').innerHTML=tempvalue;
		}		
	
	function {$idmd}ChargeLogs(){
		var XHR = new XHRConnection();
		XHR.appendData('js-left-status','yes');
		XHR.sendAndLoad('$page', 'GET',x_{$idmd}ChargeLogs);	
	}
	
	{$idmd}Charge();";
	
	echo $html;
}
function js_left_popup(){
	$html="<H1>{ADMIN_COVER_PAGE_STATUS}</H1>
				<div style='width:100%;background-color:white;padding-left:0px;border:1px solid $color'>
					<div id='progression_js_left'>
						".js_left_status(5)."
					</div>
				</div>	";
	
	
	$tpl=new templates();
	$sock=new sockets();
	$sock->getFrameWork('cmd.php?buildFrontEnd=yes');
	echo $tpl->_ENGINE_parse_body($html,'artica.performances.php');
	
}


function js_left_status($pourc=0){
	if($pourc==0){	
		$ini=new Bs_IniHandler("ressources/logs/exec.status.ini");
		$pourc=$ini->get("status",'pourc');
		$text=$ini->get("status",'text');
	}else{
		$text="Waiting...";
	}
	if($pourc<1){$pourc=5;}
	$color="#5DD13D";	
	$html="
		<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
			<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>{$pourc}%&nbsp;$text</strong></center>
		</div>
		<input type='hidden' id='js-left-id' value='$pourc'>
		
	";	
	
	
	return $html;
}

function js_right_popup(){
	$html="<H1>{today}</H1>
				<div style='width:100%;background-color:white;padding-left:0px;border:1px solid $color'>
					<div id='progression_js_right'>
						".js_right_status(5)."
					</div>
				</div>	";
	
	
	$tpl=new templates();
	$sock=new sockets();
	$sock->getFrameWork('cmd.php?buildFrontEnd=yes&right=1');
	echo $tpl->_ENGINE_parse_body($html,'artica.performances.php');
	
}
function js_right_status($pourc=0){
	$color="white";
	$file="ressources/logs/exec-right.status.ini";
if($pourc==0){	
	if(!is_file($file)){error_log("unable to stat ressources/logs/exec-right.status.ini");}
	$ini=new Bs_IniHandler("ressources/logs/exec-right.status.ini");
	$pourc=$ini->get("status",'pourc');
	$text=$ini->get("status",'text');
}else{
	$text="Waiting...";
}
if($pourc<1){$pourc=5;}
$color="#5DD13D";	
$html="
	<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
		<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>{$pourc}%&nbsp;$text</strong></center>
	</div>
	<input type='hidden' id='js-right-id' value='$pourc'>
	
";	


return $html;
	
}


function page($usersmenus){
if(GET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__)){return null;}	
$page=CurrentPageName();
$ldap=new clladp();
error_log(basename(__FILE__)." ".__FUNCTION__.'() line '. __LINE__);
$hash=$ldap->UserDatas($_SESSION["uid"]);
error_log(basename(__FILE__)." ".__FUNCTION__.'() line '. __LINE__);
if($hash["displayName"]==null){$hash["displayName"]="{Administrator}";}
$sock=new sockets();
$ou=$hash["ou"];
$users=new usersMenus();
error_log(basename(__FILE__)." ".__FUNCTION__.'() line '. __LINE__);
if($users->KASPERSKY_SMTP_APPLIANCE){
	if($sock->GET_INFO("KasperskyMailApplianceWizardFinish")<>1){
		$wizard_kaspersky_mail_appliance="Loadjs('wizard.kaspersky.appliance.php');";
	}
}


$html="	
<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var fire=0;
var loop=0;
var loop2=0;
var reste=0;
var mem_ossys=0;

function Loop(){
	loop = loop+1;
	loop2 = loop2+1;
	
	if(loop2>10){
		
		if(!IfWindowsOpen()){
			if(RunJgrowlCheck()){
				Loadjs('jGrowl.php');
				
			}
		}
		loop2=0;
	}
	
	
    fire=10-fire;
    if(loop<25){
    	setTimeout(\"Loop()\",5000);
    }else{
      loop=0;
      if(!IfWindowsOpen()){Demarre();}
      Loop();
    }
}

	function RunJgrowlCheck(){
		if($('#jGrowl').size()==0){return true;}
		if($('#jGrowl').size()==1){return true;}
		alert('jgrow=false');
		return false;
	
	}

	

	
	function Demarre(){
		setTimeout(\"ResfreshGraphs()\",800);
			
		}
		
	function ResfreshGraphs(){
		RefreshTab('admin_perso_tabs');	
	}
	function RefreshStatusRight(){
		loop=0;
		Loadjs('$page?js-right=yes');
	}


	function RefreshStatusLeft(){
			Loadjs('$page?js-left=yes');
		}
		
	function sysevents_query(){
		if(document.getElementById('q_daemons')){
			var q_daemons=document.getElementById('q_daemons').value;
			var q_lines=document.getElementById('q_lines').value;
			var q_search=document.getElementById('q_search').value;
			LoadAjax('events','$page?main=logs&q_daemons='+ q_daemons +'&q_lines=' + q_lines + '&q_search='+q_search+'&hostname={$_GET["hostname"]}');
			}
	
	}
	
	function LoadCadencee(){
		
		Loadjs('jGrowl.php');	
		setTimeout(\"Demarre()\",1500);
		setTimeout(\"Loop()\",2000);
		
		
	}


	var x_{$idmd}ChargeLogs= function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('progression_js_left').innerHTML=tempvalue;
		}		
	
function LoadMemDump(){
		YahooWin(500,'$page?mem-dump=yes');
	}



function CheckDaemon(){
	var XHR = new XHRConnection();
	XHR.appendData('CheckDaemon','yes');
	XHR.sendAndLoad('$page', 'GET');
	}	


</script>	
	".main_admin_tabs()."
	
	<script>
	
		LoadCadencee();
		RTMMailHide();
		$wizard_kaspersky_mail_appliance
		
	</script>
	{$arr[0]}
	";

$cfg["JS"][]=$arr[1];
$cfg["JS"][]="js/admin.js";

if(isset($_GET["admin-ajax"])){
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	exit;
}
error_log(basename(__FILE__)." ".__FUNCTION__.'() line '. __LINE__);
$tpl=new templates();
error_log(basename(__FILE__)." ".__FUNCTION__.'() line '. __LINE__);
$title=$tpl->_ENGINE_parse_body("<span style='text-transform:lowercase;font-size:12px'>[$usersmenus->hostname]</span>&nbsp;{WELCOME} <span style='font-size:12px'>{$hash["displayName"]} </span>");


if($users->KASPERSKY_SMTP_APPLIANCE){
	$title=$tpl->_ENGINE_parse_body("<span style='color:#005447'>{WELCOME}</span> <span style='font-size:13px;color:#005447'>For Kaspersky Appliance</span>&nbsp;|&nbsp;<span style='font-size:12px'>{$hash["displayName"]} - <span style='text-transform:lowercase'>$usersmenus->hostname</span></span>");
}


$tpl=new template_users($title,$html,$_SESSION,0,0,0,$cfg);
error_log(basename(__FILE__)." ".__FUNCTION__.'() line '. __LINE__);
$html=$tpl->web_page;
SET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__,$html);
echo $html;			
error_log(basename(__FILE__)." ".__FUNCTION__.'() line '. __LINE__);	
	
}



function main_admin_tabs(){
	$array["t:frontend"]="{admin}";
	$users=new usersMenus();
	$sys=new syslogs();
	$artica=new artica_general();
	$tpl=new templates();
	$page=CurrentPageName();
	$array["t:graphs"]='{graphs}';	
	if($artica->EnableMonitorix==1){$array["t:monitorix"]='{monitorix}';}
	
	if($users->POSTFIX_INSTALLED){$array["t:emails_received"]="{emails_received}";}

	
	$sock=new sockets();
	$SQUIDEnable=$sock->GET_INFO("SQUIDEnable");
	if($SQUIDEnable==null){$SQUIDEnable=1;}
	if($SQUIDEnable==1){
			$array["t:HTTP_FILTER_STATS"]="{HTTP_FILTER_MONITOR}";
		}
	

if($users->KASPERSKY_SMTP_APPLIANCE){
	$array["t:kaspersky"]="Kaspersky";	
}else{
	$array["t:system"]="{system_settings}";
}	
$array=$array+main_admin_tabs_perso_tabs();
$count=count($array);
if($count<7){
	$array["add-tab"]="{add}&nbsp;&raquo;";
}
$page=CurrentPageName();
if($_GET["tab_current"]==null){$_GET["tab_current"]="frontend";}
$tpl=new templates();


	while (list ($num, $ligne) = each ($array) ){
		if(preg_match("#t:(.+)#",$num,$re)){
			$ligne=$tpl->javascript_parse_text($ligne);
			if(strlen($ligne)>15){$ligne=texttooltip(substr($ligne,0,12)."...",$ligne);}
			$html[]= "<li><a href=\"admin.tabs.php?main={$re[1]}\"><span>$ligne</span></li>\n";
			continue;
		}
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"admin.tabs.php?tab=$num\"><span>$ligne</span></li>\n");
		}
	
	
return "
	<div id='mainlevel' style='width:758px;height:auto;'>
		<div id=admin_perso_tabs style='width:755px;height:auto;'>
			<ul>". implode("\n",$html)."</ul>
		</div>
	</div>
		<script>
				$(document).ready(function(){
					$('#admin_perso_tabs').tabs({
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

function main_admin_tabs_perso_tabs(){
	$uid=$_SESSION["uid"];
	if(!is_file("ressources/profiles/$uid.tabs")){return array();}
	$ini=new Bs_IniHandler("ressources/profiles/$uid.tabs");
	if(!is_array($ini->_params)){return array();}
	while (list ($num, $ligne) = each ($ini->_params) ){
		if($ligne["name"]==null){continue;}
		$array[$num]=$ligne["name"];
		
	}
	if(!is_array($array)){return array();}
	return $array;
}










function POSTFIX_STATUS(){
	$users=new usersMenus();
	$tpl=new templates();

	if($users->POSTFIX_INSTALLED){
			$status=new status();
			echo $tpl->_ENGINE_parse_body($status->Postfix_satus());
			exit;
	}	
}

function status_computer(){
	if(GET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__,false,1)){return null;}
	$os=new os_system();
	$html=RoundedLightGrey($os->html_Memory_usage())."<br>";
	SET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__,$html);
	echo $html;
}


function status_right(){
	include_once(dirname(__FILE__)."/ressources/class.browser.detection.inc");
	$users=new usersMenus();
	if(!$users->AsArticaAdministrator){die("<H2 style='color:red'>permission denied</H2>");}

	$ldap=new clladp();
	if($ldap->ldap_password=="secret"){
		echo RoundedLightGrey(Paragraphe("danger64-user-lock.png",'{MANAGER_DEFAULT_PASSWORD}','{MANAGER_DEFAULT_PASSWORD_TEXT}',"javascript:Loadjs('artica.settings.php?js=yes&bigaccount-interface=yes');",null,330))."<br>";
	}
	if(!function_exists("browser_detection")){include(dirname(__FILE__).'/ressources/class.browser.detection.inc');}
	$browser=browser_detection();
	
	if($browser=="ie"){
		echo Paragraphe("no-ie-64.png",'{NOIEPLEASE} !!','{NOIEPLEASE_TEXT}',"javascript:s_PopUp('http://www.mozilla-europe.org/en/firefox/','800',800);",null,330)."<br>";
	}
	
	if($users->VMWARE_HOST){
		if(!$users->VMWARE_TOOLS_INSTALLED){
			echo RoundedLightGrey(Paragraphe("vmware-logo.png",'{INSTALL_VMWARE_TOOLS}','{INSTALL_VMWARE_TOOLS_TEXT}',
			"javascript:Loadjs('setup.index.progress.php?product=APP_VMTOOLS&start-install=yes');",null,330))."<br>";
		}
	}
	
	
	
	
	
	if($users->POSTFIX_INSTALLED){
			echo status_postfix();
			return null;
		}
	
	
	$tpl=new templates();
	$html=file_get_contents("ressources/logs/status.right.1.html");
	echo $tpl->_ENGINE_parse_body("$html");
	$sock=new sockets();
	$sock->getFrameWork('cmd.php?ForceRefreshRight=yes');
	}


function status_postfix(){
	$tpl=new templates();
	if($_GET["counter"]==null){$_GET["counter"]=1;}
	if($_GET["counter"]==1){$newcounter=0;}else{$newcounter=1;}
	$counter=Field_hidden('counter',$newcounter);
	$memory="<div id='mem_status_computer'>".@file_get_contents("ressources/logs/status.memory.html")."</div>";
	$postfix=@file_get_contents("ressources/logs/status.right.postfix.html");
	$status=new status();
	$postfix=$status->Postfix_satus();
	if($_GET["counter"]==1){
		return $counter.$tpl->_ENGINE_parse_body($memory.$postfix.$switch);
	}
	
	if($_GET["counter"]==0){
		return $counter.$tpl->_ENGINE_parse_body($memory.$postfix.$switch);
	}	
	
}

function DateDiff($debut, $fin) {

	if(preg_match("#([0-9]+)-([0-9]+)-([0-9]+)\s+([0-9]+):([0-9]+):([0-9]+)#",$debut,$re)){
		$t1=mktime($re[4], $re[5],$re[6], $re[2], $re[3], $re[1]);
	}
	
	if(preg_match("#([0-9]+)-([0-9]+)-([0-9]+)\s+([0-9]+):([0-9]+):([0-9]+)#",$fin,$re)){
		$t2=mktime($re[4], $re[5],$re[6], $re[2], $re[3], $re[1]);
	}	
	

  $t=$t1-$t2;
  if($t==0){return 0;};
  
  
  
  $diff = $t2 - $t1;
  
  return (($diff/60)+1);

}

function status_memdump_js(){
	$page=CurrentPageName();
	
	if(GET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__)){return ;}
	
	$html="
		var x_MemoryStatus= function (obj) {
			var results=obj.responseText;
			document.getElementById('mem_status_computer').innerHTML=results
		
		}	
	
	
		function MemoryStatus(){
			if(!document.getElementById('mem_status_computer')){return;}
			var XHR = new XHRConnection();
			XHR.appendData('memcomputer','yes');
			XHR.sendAndLoad('$page', 'GET',x_MemoryStatus);
		
		}
	MemoryStatus();";
	SET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__,$html);
	echo $html;
	
}


function status_memdump(){
	$sock=new sockets();
	$datas=$sock->getFrameWork("cmd.php?mempy=yes");
	$tbl=explode("\n",$datas);
	
	rsort($tbl);
	
	$html="<table class=table_form>";
	
	while (list ($num, $val) = each ($tbl) ){
		if(trim($val)==null){continue;}
		if(preg_match("#=\s+([0-9\.]+)\s+(MiB)\s+(.+)#",$val,$re)){
			$color=CellRollOver();
			if(intval($re[1])>50){$color="style='background-color:#F7D0CC;color:black'";}
			
			$html=$html."<tr $color>
				<td valign='top' width=1%><img src='img/status_service_run.png'></td>
				<td><strong style='font-size:13px'>{$re[3]}</strong></td>
				<td valign='top' width=1% nowrap><strong style='font-size:13px'>{$re[1]} {$re[2]}</strong></td>
				</tr>";
		}
	}
	
	$html="<H1>{memory_use}</H1>".RoundedLightWhite("<div style='width:100%;height:400Px;overflow:auto'>$html.</table></div>");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	
	





function status_left(){
	$html=@file_get_contents("ressources/logs/status.global.html");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	$sock=new sockets();
	$sock->getFrameWork('cmd.php?ForceRefreshLeft=yes');	
	}


function syslogs(){
	if($_GET["lines"]==null){$_GET["lines"]=50;}
	$users->syslogng_installed=false;
	$users=new usersMenus();
	if(!$users->syslogng_installed){
		if($users->EnableMysqlFeatures==0){
			echo graph();
			exit;
		}
	}
	
	$q=new syslogs();
	$q->BuildNecessaryTables();
	$q->q_daemons=$_GET["q_daemons"];
	$q->limit_end=$_GET["q_lines"];
	$q->q_search=$_GET["q_search"];
	$daemon=Field_array_Hash($q->GetDaemons(),'q_daemons',$_GET["q_daemons"],'sysevents_query()');
	
	
	$form="<table style=\"width:100%\">
	<tr>
	<td align='right'><strong>Daemon:</strong></td>
	<td>$daemon</td>
	<td align='right'><strong>{search}:</strong></td>
	<td>".Field_text('q_search',$_GET["q_search"],'width:150px',null,'sysevents_query()')."</td>	
	<td align='right'><strong>{lines_number}:</strong></td>
	<td>".Field_text('q_lines',$_GET["q_lines"],'width:40px',null,'sysevents_query()')."</td>
	<td>" . imgtootltip('icon_refresh-20.gif','{refresh}','sysevents_query()')."</td>
		
	</tr>
	</table>";
	$form="<br>" . RoundedLightGrey($form);
	
	$html="
	<input type='hidden' id='switch' value='{$_GET["main"]}'>
	
	
	<table style=\"width:100%\">
	
	";
	$tpl=new templates();
	$r=$q->build_query();
	$count=0;
	$style="style='border-bottom:1px dotted #CCCCCC'";
	while($ligne=@mysql_fetch_array($r,MYSQL_ASSOC)){
		$ligne["msg"]=htmlentities($ligne["msg"]);
		$html=$html . 
		"<tr " . CellRollOver_jaune() . ">
		<td width=1% valign='top' $style><img src='img/fw_bold.gif'></td>
		<td width=1% nowrap valign='top' $style>{$ligne["date"]}</td>
		<td $style>{$ligne["msg"]}</td>
		<td width=1% nowrap valign='top' $style>{$ligne["program"]}</td>
		</tr>";
		$count=$count+1;
		if($count>500){
			$error="
			<tr>
				<td width=1% valign='top' styme='border-bottom:1px dotted red'><img src='img/fw_bold.gif'></td>
				<td colspan=3 style='color:red;font-weight:bold;border-bottom:1px dotted red'>{too_many_lines_exceed}:500</td>
			</tr>";
			$html=$tpl->_ENGINE_parse_body($error).$html;
			break;
		}
			
			
		}
	
	$html=$html . "</table>";
		
	$html=$tpl->_ENGINE_parse_body($form)."<br>$html";

	echo $html;
	
	
}


function warnings_delete_all(){
	$sql="TRUNCATE `notify`";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_events");
	}








function graph_defang(){
	
	$style="style='border:1px dotted #CCCCCC;padding:3px;margin:3px;text-align:center'";
	$md=md5(date('Ymdhis'));	
	
$defgraph[]="daily_spam_9recipient_stacked_bar_Heartlight_Traffic.png";
$defgraph[]="daily_spam_9sender_stacked_bar.png";
$defgraph[]="daily_spam_9value2_stacked_bar.png";
$defgraph[]="daily_spamprobable_spamvirusmail_in_summary_line.png";
$defgraph[]="daily_spamvirus_9recipient_stacked_bar.png";
$defgraph[]="daily_virus_9value2_stacked_bar.png";
$defgraph[]="daily_virus_value1_stacked_bar.png";
$defgraph[]="hourly_spam_9recipient_stacked_bar_Heartlight_Traffic.png";
$defgraph[]="hourly_spam_9sender_stacked_bar.png";
$defgraph[]="hourly_spam_9value2_stacked_bar.png";
$defgraph[]="hourly_spamprobable_spamvirusmail_in_summary_line.png";
$defgraph[]="hourly_spamvirus_9recipient_stacked_bar.png";
$defgraph[]="hourly_virus_9value2_stacked_bar.png";
$defgraph[]="hourly_virus_value1_stacked_bar.png";
$defgraph[]="monthly_spam_9recipient_stacked_bar_Heartlight_Traffic.png";
$defgraph[]="monthly_spam_9sender_stacked_bar.png";
$defgraph[]="monthly_spam_9value2_stacked_bar.png";
$defgraph[]="monthly_spamprobable_spamvirusmail_in_summary_line.png";
$defgraph[]="monthly_spamvirus_9recipient_stacked_bar.png";
$defgraph[]="monthly_virus_9value2_stacked_bar.png";
$defgraph[]="monthly_virus_value1_stacked_bar.png";

$index=rand(0,count($defgraph));
	
	$g_system="
<div id='g_sys2' $style>
		<H3>GraphDefang</H3>
			<a href='index.graphdefang.php'><img src='images.listener.php?uri=graphdefang/{$defgraph[$index]}&md=$md'></a>
	</div>
<div id='g_sys' $style>
		<H3>system</H3>
			<img src='images.listener.php?uri=system/rrd/01cpu-1day.png&md=$md'>
	</div>
	
";	
	
	$p="
	<input type='hidden' id='switch' value='{$_GET["main"]}'>
	$g_system.$g_postfix.$g_squid";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($p);	

	
}







function ShowFileLogs(){
	$file="ressources/logs/{$_GET["ShowFileLogs"]}";
	$datas=file_get_contents($file);
	$datas=htmlentities($datas);
	$datas=nl2br($datas);
	$html="
	<H3>{service_info}</H3>
	<div style='overflow-y:auto'>
	<code style='font-size:10px'>$datas</code>
	</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


FUNCTION CheckTables(){
	$sql=new mysql();
	$sql->BuildTables();	
	
}

FUNCTION CheckDaemon(){
	$sock=new sockets();
	$sock->getfile('CheckDaemon');
	
}

function START_ALL_SERVICES(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?start-all-services=yes");
	$tpl=new templates();
	echo "alert('".$tpl->javascript_parse_text("{start_all_services_perform}")."');";
}

function EmergencyStart(){
	$service_cmd=$_GET["EmergencyStart"];
	$sock=new sockets();
	$datas=$sock->getfile("EmergencyStart:$service_cmd");
	$tbl=explode("\n",$datas);
		$tbl=array_reverse ($tbl, TRUE);		
		while (list ($num, $val) = each ($tbl) ){
			if(trim($val)<>null){
				if($arr[md5($val)]==true){continue;}
				$img=statusLogs($val);
			$html=$html . "
			<div style='black;margin-bottom:1px;padding:2px;border-bottom:1px dotted #CCCCCC;border-left:5px solid #CCCCCC;width:98%;'>
			<table style='width:100%'>
			<tr>
			<td width=1%><img src='img/fw_bold.gif'></td><td><code style='font-size:10px'>$val</code></td>
			</tr>
			</table>
			</div>";
			$arr[md5($val)]=true;
			
			}
		}	
		
		echo "<div style='width:100%;height:400px;overflow:auto;'>$html</div>";
	
}

function isoqlog(){
	
	
	echo "<input type='hidden' id='switch' value='{$_GET["main"]}'>";
	include_once('isoqlog.php');
	
	
}





?>