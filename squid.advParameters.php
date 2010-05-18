<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.system.network.inc');

	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["sizelimit"])){sizelimit_popup();exit;}
	if(isset($_GET["request_header_max_size"])){sizelimit_save();exit;}

	
js();

function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{squid_advanced_parameters}");
	
	$html="
	function SquidAVParamStart(){
		YahooWin('650','$page?popup=yes','$title');
	}
	
var X_SquidAVParamSave= function (obj) {
	var results=obj.responseText;
	if(results.length>0){
		alert(results);
		document.getElementById('SquidAVParamID').innerHTML='';
		return;
	}
	YahooWinHide(); 
	}
		
	function SquidAVParamSave(){
		var XHR = new XHRConnection();
		XHR.appendData('request_header_max_size',
		document.getElementById('request_header_max_size').value+' '+document.getElementById('request_header_max_size_M').value);
		
		XHR.appendData('request_body_max_size',
		document.getElementById('request_body_max_size').value+' '+document.getElementById('request_body_max_size_M').value);
		
		XHR.appendData('reply_body_max_size',
		document.getElementById('reply_body_max_size').value);
				
		
		document.getElementById('SquidAVParamID').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_SquidAVParamSave);				
	}	
	
	
	SquidAVParamStart()";
	
	echo $html;
	
	
}

function sizelimit_save(){
	$squid=new squidbee();
	$squid->global_conf_array["request_header_max_size"]=$_GET["request_header_max_size"];
	$squid->global_conf_array["request_body_max_size"]=$_GET["request_body_max_size"];
	$squid->global_conf_array["reply_body_max_size"]=$_GET["reply_body_max_size"];
	$squid->SaveToLdap();
	
}

function sizelimit_popup(){
	
	$aray_m["KB"]="KB";
	$aray_m["MB"]="MB";
	$aray_m[null]="{none}";
	
	$squid=new squidbee();
	
	
	
	if(preg_match("#([0-9]+)\s+([A-Z]+)#",$squid->global_conf_array["request_header_max_size"],$re)){
		$request_header_max_size_v=$re[1];
		$request_header_max_size_m=$re[2];
	}
	
	if(preg_match("#([0-9]+)\s+([A-Z]+)#",$squid->global_conf_array["request_body_max_size"],$re)){
		$request_body_max_size_v=$re[1];
		$request_body_max_size_m=$re[2];
	}

	if(preg_match("#([0-9]+)\s+([A-Z]+)#",$squid->global_conf_array["reply_body_max_size"],$re)){
		$reply_body_max_size_v=$re[1];
		$reply_body_max_size_m=$re[2];
	}	
	
	$html="
	<div id='SquidAVParamID'></div>
	<table style='width:100%'>
	<tr>
		<td style='font-size:12px;' class=legend>{request_header_max_size}:</td>
		<td>". Field_text("request_header_max_size",$request_header_max_size_v,"font-size:12px;padding:3px;width:40px")."</td>
		<td>". Field_array_Hash($aray_m,"request_header_max_size_M",$request_header_max_size_m)."</td>
		<td>". help_icon("{request_header_max_size_text}")."</td>
	</tr>
	
	
	<tr>
		<td style='font-size:12px' class=legend>{request_body_max_size}:</td>
		<td>". Field_text("request_body_max_size",$request_body_max_size_v,"font-size:12px;padding:3px;width:40px")."</td>
		<td>". Field_array_Hash($aray_m,"request_body_max_size_M",$request_body_max_size_m)."</td>
		<td>". help_icon("{request_body_max_size_text}")."</td>
	</tr>	
	
	<tr>
		<td style='font-size:12px' class=legend>{reply_body_max_size}:</td>
		<td>". Field_text("reply_body_max_size",$reply_body_max_size_v,"font-size:12px;padding:3px;width:40px")."</td>
		<td>". Field_array_Hash($aray_m,"reply_body_max_size_M",$reply_body_max_size_m)."</td>
		<td>". help_icon("{reply_body_max_size_text}")."</td>
	</tr>		
	
	
	
	<tr>
		<td colspan=4 align='right'>
			<hr>
				". button("{apply}","SquidAVParamSave()")."
		</td>
	</tr>
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function popup(){
	
	$page=CurrentPageName();
	$array["sizelimit"]='{squid_sizelimit}';
	
	

	while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?$num=yes\"><span>$ligne</span></li>\n";
	}
	
	
	echo "
	<div id=main_squid_adv style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_squid_adv').tabs({
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

?>