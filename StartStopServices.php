<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.postfix-multi.inc');
	
	
$users=new usersMenus();
$tpl=new templates();
if(!$users->AsSystemAdministrator){
		echo $tpl->javascript_parse_text("alert('{ERROR_NO_PRIVS}');");
		die();
	}
	
if(isset($_GET["popup"])){popup();exit;}	
if(isset($_GET["APPLICATIONSTART"])){filllogs();exit;}
js();


function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$service_cmd=$_GET["cmd"];
	$application=$tpl->_ENGINE_parse_body("{".$_GET["APP"]."}");
	$action=$_GET["action"];
	$idmd="STARTSTOPSERVICE_START";
	$html="
	var {$idmd}tant=0;
	

function {$idmd}demarre(){
	if(!RTMMailOpen()){return false;}
	{$idmd}tant = {$idmd}tant+1;
	if ({$idmd}tant < 5 ) {                           
		
		setTimeout(\"{$idmd}demarre()\",800);
		
      } else {
			  {$idmd}tant = 0;
              {$idmd}ChargeLogs();
			  {$idmd}demarre(); 
   }
}	
	
	
	function STARTSTOPSERVICE_START(){
			RTMMail(650,'$page?popup=yes&APP={$_GET["APP"]}&cmd=$service_cmd&action=$action','$application... ($action)');
			{$idmd}demarre();
		}
	var x_{$idmd}ChargeLogs= function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('APPLICATIONSTART').innerHTML=tempvalue;
		}			
		
	function {$idmd}ChargeLogs(){
		var XHR = new XHRConnection();
		XHR.appendData('APPLICATIONSTART','yes');
		XHR.appendData('cmd','$service_cmd');
		XHR.appendData('action','$action');
		XHR.appendData('APP','{$_GET["APP"]}');
		XHR.sendAndLoad('$page', 'GET',x_{$idmd}ChargeLogs);
		}
	
	STARTSTOPSERVICE_START();";
	echo $html;
}

function popup(){
	
	$html="
	<table style='width:100%'>
	
	<tr>
		<td valign='top' width=1%><div id='wait-id'><img src=img/wait-clock.gif></div></td>
		<td valign='middle' style='border-bottom:1px solid #005447'>
			<span style='font-size:16px;font-weight:bolder;color:#005447'>{{$_GET["APP"]}}</span>
		</td>
	</tr>
	</table>
	
	<div style='width:100%;height:350px;overflow:auto;padding:3px;marging:3px;' id='APPLICATIONSTART'>
	<center>
		<img src=\"img/wait_verybig.gif\">
	</center>
	</div>";
	
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?START-STOP-SERVICES=yes&cmd={$_GET["cmd"]}&action={$_GET["action"]}&APP={$_GET["APP"]}");
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function filllogs(){
	$md5=$_GET["APP"].$_GET["action"].$_GET["cmd"];
	$file="ressources/logs/web/$md5.log";
	if(!is_file(dirname(__FILE__)."/$file")){
		echo "
		
		<center><img src=\"img/wait_verybig.gif\"></center>";
		exit;
	}
	$tbl=explode("\n",@file_get_contents($file));
	while (list ($num, $ligne) = each ($tbl) ){
		$html[]="<div><code style='font-size:11px'>$ligne</code></div>";
		
	}
	
	if(is_array($html)){echo implode("\n",$html);}else{
		echo "<center><img src=\"img/wait_verybig.gif\"></center>";exit;
	}
}
	
?>