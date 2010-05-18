<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.dnsmasq.inc');
	include_once('ressources/class.main_cf.inc');
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){header('location:users.index.php');exit;}	
if(isset($_GET["othersoptions"])){othersoptions();exit;}
if(isset($_GET["mxrecdomainfrom"])){SaveConfMx();exit;}
if(isset($_GET["mxHostsReload"])){echo Loadmxhosts();exit;}
if(isset($_GET["DnsmasqDeleteMxHost"])){DnsmasqDeleteMxHost();exit;}
if(isset($_GET["DnsMasqMxMove"])){DnsMasqMxMove();exit;}
page();	
function page(){

	$cf=new dnsmasq();
	$page=CurrentPageName();
	
	
	
	
$html="
<H4>{others_options}</H4>
<form name='fmm1'>
<input type='hidden' name='othersoptions' value='yes'>
<center>
<table style='width:100%'>
<tr>
<td align='right' valign='top' class=bottom style='font-weight:bold' class=bottom>{localmx}:</td>
<td align='left' valign='top' class=bottom class=bottom >" . Field_key_checkbox_img('localmx',$cf->main_array["localmx"],'{enable_disable}')."</td>
<td align='left' valign='top' class=bottom style='font-size:9px'>{localmx_text}</td>
</tr>
<tr>
<td align='right' valign='top' class=bottom style='font-weight:bold' class=bottom>{selfmx}:</td>
<td align='left' valign='top' class=bottom class=bottom >" . Field_key_checkbox_img('selfmx',$cf->main_array["selfmx"],'{enable_disable}')."</td>
<td align='left' valign='top' class=bottom style='font-size:9px'>{selfmx_text}</td>
</tr>
</table>
<table style='width:100%'>
<tr>
	<td nowrap>{mx-target}</td>
	<td><strong>" . Field_text('mx-target',$cf->main_array["mx-target"]) . "</td>
	<td style='font-size:9px'>{mx-target_text}</td>
</tr>
<tr><td colspan=3 align='right'><input type='button' OnClick=\"javasript:ParseForm('fmm1','$page',true);\" value='{edit}&nbsp;&raquo;'></td></tr>
</table>
</form>
</center>	


<p>{dnsmasq_DNS_records_text}<br>{mxexamples}</p>

<form name='ffm1'>
<center>
<table style='width:100%'>
<td class='bottom'>&nbsp;</td>
<tr style='background-color:#CCCCCC'>
<td class='bottom'>&nbsp;</td>
<td class='bottom'><strong>{mxrecdomainfrom}</td>
<td class='bottom'><strong>{mxrecdomainto}</td>
<td class='bottom'><strong>{mxheight}</td>
<td class='bottom'>&nbsp;</td>
</tr>
<tr>
<td class='bottom' width='20px'>&nbsp;</td>
<td class='bottom' width='140px'>" . Field_text('mxrecdomainfrom') . "</td>
<td class='bottom'  width='180px'>" . Field_text('mxrecdomainto') . "</td>
<td class='bottom'  width='100px'>" . Field_text('mxheight') . "</td>
<td class='bottom'><input type='button' OnClick=\"javascript:ParseForm('ffm1','$page',true);mxHostsReload();\" value='{add}&nbsp;&raquo;'></td>
</tr>
</table>
</form>
</center>
<div id='mx_hosts'>" . Loadmxhosts() . "</div>
";
	$JS["JS"][]='js/dnsmasq.js';
$tpl=new template_users('{dnsmasq_DNS_records}',$html,0,0,0,0,$JS);
echo $tpl->web_page;
	
}

function othersoptions(){
	unset($_GET["othersoptions"]);
	$conf=new dnsmasq();
	while (list ($key, $line) = each ($_GET) ){
		if($line<>null){
			$conf->main_array[$key]=$line;	
		}else{unset($conf->main_array[$key]);}
		}
	$conf->SaveConf(); 
}

function SaveConfMx(){
	$mxrecdomainfrom=$_GET["mxrecdomainfrom"];
	$mxrecdomainto=$_GET["mxrecdomainto"];
	$mxheight=$_GET["mxheight"];
	if($mxrecdomainfrom==null && $mxrecdomainto==null && $mxheight==null){return null;}
	$cf=new dnsmasq();
	$cf->array_mxhost[]="$mxrecdomainfrom,$mxrecdomainto,$mxheight";
	$cf->SaveConf();
}
function DnsmasqDeleteMxHost(){
	$cf=new dnsmasq();
	unset($cf->array_mxhost[$_GET["DnsmasqDeleteMxHost"]]);
	$cf->SaveConf();
	}
function DnsMasqMxMove(){
	$cf=new dnsmasq();
	$newarrar=array_move_element($cf->array_mxhost,$cf->array_mxhost[$_GET["DnsMasqMxMove"]],$_GET["move"]);
	$cf->array_mxhost=$newarrar;
	$cf->SaveConf();
	
}
function Loadmxhosts(){
	$conf=new dnsmasq();
	if(!is_array($conf->array_mxhost)){return null;}
	$html="<center><table style='width:100%'>";
	while (list ($index, $line) = each ($conf->array_mxhost) ){
		$m=explode(",",$line);
		$cell_up="<td width=1% class=bottom>" . imgtootltip('arrow_up.gif','{up}',"DnsMasqMxMove('$index','up')") ."</td>";
		$cell_down="<td width=1% class=bottom>" . imgtootltip('arrow_down.gif','{down}',"DnsMasqMxMove('$index','down')") ."</td>";		
		$html=$html . "
		<tr>
			<td width='20px' valign='middle' class=bottom><img src='img/fw_bold.gif'><td>
			<td class=bottom width='140px'>{$m[0]}</td>
			<td class=bottom width='180px'>{$m[1]}</td>
			<td class=bottom width='100px'>{$m[2]}</td>
			$cell_up
			$cell_down
			<td class=bottom width='100px'>" . imgtootltip('x.gif','{delete}',"DnsmasqDeleteMxHost('$index');")."</td>
		</tr>";
	}
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html . "</table></center>");
	
}

	