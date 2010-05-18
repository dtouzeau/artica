<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.system.network.inc');
	
	
	
	$user=new usersMenus();
	if($user->SQUID_INSTALLED==false){die('not allowed');}
	if($user->AsSquidAdministrator==false){die('not allowed');}
	
	if($_GET["main"]=="index"){echo index();exit;}
	if($_GET["main"]=="daemons"){echo daemons();exit;}
	if($_GET["main"]=="clamav"){echo clamav();exit;}
	if($_GET["main"]=="logs"){echo logs();exit;}
	if(isset($_GET["MaxKeepAliveRequests"])){save_settings();exit;}
	if(isset($_GET["srv_clamav_SendPercentData"])){save_settings();exit;}
	
	
	js();
	
	
function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body('{cicap_title}');
	$title1=$tpl->_ENGINE_parse_body('{clamav_settings}');
	$title2=$tpl->_ENGINE_parse_body('{clamav_settings}');
	$html="
	
		function loadcicap(){
			YahooWin(700,'$page?main=index','$title');
		
		}
		
		function cicap_daemons(){
			YahooWin2(550,'$page?main=daemons','$title');
		
		}		

		function cicap_clamav(){
			YahooWin2(550,'$page?main=clamav','$title');
		
		}					
		
		function cicap_logs(){
			YahooWin2(550,'$page?main=logs','$title');
		
		}			
		
		loadcicap();
	
	";
	echo $html;
}

function index(){
	
	$daemon=Paragraphe('rouage-64.png','{daemon_settings}','{daemon_settings_text}',"javascript:cicap_daemons()");
	$clamav=Paragraphe('clamav-64.png','{clamav_settings}','{clamav_settings_text}',"javascript:cicap_clamav()");
	$logs=Paragraphe('folder-logs-643.png','{icap_logs}','{icap_logs_text}',"javascript:cicap_logs()");
	
	//
	
	$html="<H1>{cicap_title}</H1>
	
	<table style='width:100%'>
	<tr>
		<td valign='top'>$daemon</td>
		<td valign='top'>$clamav</td>
	</tr>
		
	<tr>
		<td valign='top'>$logs</td>
		<td valign='top'>&nbsp;</td>
	</tr>
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function logs(){
	$page=CurrentPageName();
	
	
	
	$dd=logs_datas();
$html="<H1>{icap_logs}</H1>
	<p class=caption>{icap_logs_text}</p>
	
	". RoundedLightWhite("<div style='width:99%;height:300px;overflow:auto'>$dd</div>");
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}

function logs_datas(){
	
	$sock=new sockets();
	$datas=$sock->getfile('cicapevents');
	$tbl=explode("\n",$datas);
	if(!is_array($tbl)){return null;}
	$tbl=array_reverse($tbl);
	while (list ($num, $line) = each ($tbl) ){
		if(trim($line==null)){continue;}
		$line=htmlspecialchars($line);
		$html=$html."<div><code style='font-size:10px'>$line</code></div>";
		

		
	}
	
	return $html;
	
	
}


function clamav(){
	
	$ci=new cicap();
	$page=CurrentPageName();
	$html="<H1>{clamav_settings}</H1>
	<p class=caption>{clamav_settings_text}</p>
	
	<form name=ffmcc2>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>{srv_clamav.SendPercentData}:</td>
		<td>" . Field_text('srv_clamav.SendPercentData',$ci->main_array["CONF"]["srv_clamav.SendPercentData"],'width:30px')."&nbsp;%</td>
		<td>" . help_icon('{srv_clamav.SendPercentData_text}')."</td>
	</tr>

	<tr>
		<td class=legend>{srv_clamav.StartSendPercentDataAfter}:</td>
		<td>" . Field_text('srv_clamav.StartSendPercentDataAfter',$ci->main_array["CONF"]["srv_clamav.StartSendPercentDataAfter"],'width:30px')."&nbsp;M</td>
		<td>" . help_icon('{srv_clamav.StartSendPercentDataAfter_text}')."</td>
	</tr>	
	
	<tr>
		<td class=legend>{srv_clamav.MaxObjectSize}:</td>
		<td>" . Field_text('srv_clamav.MaxObjectSize',$ci->main_array["CONF"]["srv_clamav.MaxObjectSize"],'width:30px')."&nbsp;M</td>
		<td>" . help_icon('{srv_clamav.MaxObjectSize_text}')."</td>
	</tr>

	<tr>
		<td class=legend>{srv_clamav.ClamAvMaxFilesInArchive}:</td>
		<td>" . Field_text('srv_clamav.ClamAvMaxFilesInArchive',$ci->main_array["CONF"]["srv_clamav.ClamAvMaxFilesInArchive"],'width:30px')."&nbsp;{files}</td>
		<td>" . help_icon('{srv_clamav.ClamAvMaxFilesInArchive}')."</td>
	</tr>	
	
	<tr>
		<td class=legend>{srv_clamav.ClamAvMaxFileSizeInArchive}:</td>
		<td>" . Field_text('srv_clamav.ClamAvMaxFileSizeInArchive',$ci->main_array["CONF"]["srv_clamav.ClamAvMaxFileSizeInArchive"],'width:30px')."&nbsp;M</td>
		<td>" . help_icon('{srv_clamav.ClamAvMaxFileSizeInArchive}')."</td>
	</tr>

	<tr>
		<td class=legend>{srv_clamav.ClamAvMaxRecLevel}:</td>
		<td>" . Field_text('srv_clamav.ClamAvMaxRecLevel',$ci->main_array["CONF"]["srv_clamav.ClamAvMaxRecLevel"],'width:30px')."&nbsp;M</td>
		<td>" . help_icon('{srv_clamav.ClamAvMaxRecLevel}')."</td>
	</tr>		
	
	<tr>
		<td colspan=3 align='right'>
			<input type='button' OnClick=\"javascript:ParseForm('ffmcc2','$page',true,false,false,'dialog2_content','$page?main=clamav');\" value='{edit}&nbsp;&raquo;'>
		</td>
	</tr>
	</table>
	</form>
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function daemons(){
	
	$ci=new cicap();
	$page=CurrentPageName();
	
	$html="<H1>{daemon_settings}</H1>
	<p class=caption>{daemon_settings_text}</p>
	
	<form name=ffmcc1>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>{Timeout}:</td>
		<td>" . Field_text('Timeout',$ci->main_array["CONF"]["Timeout"],'width:30px')."&nbsp;{seconds}</td>
		<td>" . help_icon('{Timeout_text}')."</td>
	</tr>
	<tr>
		<td class=legend>{KeepAlive}:</td>
		<td>" . Field_onoff_checkbox_img('KeepAlive',$ci->main_array["CONF"]["KeepAlive"],'{enable_disable}')."</td>
		<td>" . help_icon('{KeepAlive_text}')."</td>
	</tr>
	<tr>
		<td class=legend>{MaxKeepAliveRequests}:</td>
		<td>" . Field_text('MaxKeepAliveRequests',$ci->main_array["CONF"]["MaxKeepAliveRequests"],'width:30px')."&nbsp;</td>
		<td>" . help_icon('{MaxKeepAliveRequests_text}')."</td>
	</tr>	
	
	<tr>
		<td class=legend>{KeepAliveTimeout}:</td>
		<td>" . Field_text('KeepAliveTimeout',$ci->main_array["CONF"]["KeepAliveTimeout"],'width:30px')."&nbsp;{seconds}</td>
		<td>" . help_icon('{KeepAliveTimeout_text}')."</td>
	</tr>
	
	<tr>
		<td class=legend>{MaxServers}:</td>
		<td>" . Field_text('MaxServers',$ci->main_array["CONF"]["MaxServers"],'width:30px')."&nbsp;</td>
		<td>" . help_icon('{MaxServers_text}')."</td>
	</tr>	
	
	
	<tr>
		<td class=legend>{MinSpareThreads}:</td>
		<td>" . Field_text('MinSpareThreads',$ci->main_array["CONF"]["MinSpareThreads"],'width:30px')."&nbsp;</td>
		<td>" . help_icon('{MinSpareThreads_text}')."</td>
	</tr>		
	
	<tr>
		<td class=legend>{MaxSpareThreads}:</td>
		<td>" . Field_text('MaxSpareThreads',$ci->main_array["CONF"]["MaxSpareThreads"],'width:30px')."&nbsp;</td>
		<td>" . help_icon('{MaxSpareThreads_text}')."</td>
	</tr>	

	<tr>
		<td class=legend>{ThreadsPerChild}:</td>
		<td>" . Field_text('ThreadsPerChild',$ci->main_array["CONF"]["ThreadsPerChild"],'width:30px')."&nbsp;</td>
		<td>" . help_icon('{ThreadsPerChild_text}')."</td>
	</tr>	

	<tr>
		<td class=legend>{MaxRequestsPerChild}:</td>
		<td>" . Field_text('MaxRequestsPerChild',$ci->main_array["CONF"]["MaxRequestsPerChild"],'width:30px')."&nbsp;</td>
		<td>" . help_icon('{MaxRequestsPerChild_text}')."</td>
	</tr>	
	<tr>
		<td class=legend>{VirSaveDir}:</td>
		<td>" . Field_text('VirSaveDir',$ci->main_array["CONF"]["VirSaveDir"],'width:290px')."&nbsp;</td>
		<td>" . help_icon('{VirSaveDir_text}')."</td>
	</tr>		
	<tr>
		<td class=legend>{VirHTTPServer}:</td>
		<td>" . Field_text('VirHTTPServer',$ci->main_array["CONF"]["VirHTTPServer"],'width:290px')."&nbsp;</td>
		<td>" . help_icon('{VirHTTPServer_text}')."</td>
	</tr>		
	<tr>	
		<td class=legend>{example}:</td>
		<td colspan=2><strong><a href='https://{$_SERVER['SERVER_NAME']}/exec.cicap.php?usename=%f&remove=1&file='>https://{$_SERVER['SERVER_NAME']}/exec.cicap.php?usename=%f&remove=1&file=</a></strong></td>
	</tr>	

	<tr>
		<td colspan=3 align='right'>
			<input type='button' OnClick=\"javascript:ParseForm('ffmcc1','$page',true,false,false,'dialog2_content','$page?main=daemons');\" value='{edit}&nbsp;&raquo;'>
		</td>
	</tr>
	</table>
	</form>

	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function save_settings(){
	
	
	
	$ci=new cicap();
	while (list ($num, $line) = each ($_GET)){	
		if(preg_match('#^srv_clamav_(.+)#',$num,$re)){
			$num="srv.clamav.{$re[1]}";
		}
		$ci->main_array["CONF"][$num]=$line;
	}
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	$ci->Save();
	
}

//
	
?>	