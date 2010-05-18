<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.crossroads.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.status.inc');	
	
	$user=new usersMenus();
	if(isset($_GET["status"])){echo main_status();exit;}
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	if($user->AsPostfixAdministrator==false){header('location:users.index.php');exit();}
	if(isset($_GET["PostfixMasterServerIdentity"])){main_master_config_edit();exit;}
	if(isset($_GET["PostfixSlaveServersIdentity"])){main_slaves_addserv();exit;}
	if(isset($_GET["SynchronizeSlaves"])){main_SynchronizeSlaves();exit;}
	if(isset($_GET["CrossRoadsDeleteServer"])){main_slaves_delete();exit;}
	
	
	
	
page();	
function page(){
$page=CurrentPageName();
$usersmenus=new usersMenus();

switch ($_GET["main"]) {
			case "master-config":echo main_master_config();exit;break;
			case "slaves":echo main_slaves();exit;break;
			case "download":main_download();exit;break;
			case "rules":echo main_rules();exit;break;
			case "deny_ext":main_denyext();exit;break;
			case "deny_ext_list":echo main_denyext_list();exit;break;
			case "members":echo main_members();exit;break;
			default:
				break;
		}	
		
		

$html="
<table style='width:100%' align=center>
<tr>
<td width=1% valign='top'><img src='img/bg_balance.png'>

</td>
<td valign='top' align='left'>
".main_status()."
</tr>
</table><br>
<div id='mainconfig'></div>

<script>LoadAjax('mainconfig','$page?main=master-config&tab=0');</script>

";

$CFG["JS"][]="js/crossroads.js";
$tpl=new template_users('Postfix {APP_CROSSROADS}',$html,0,0,0,0,$CFG);
echo $tpl->web_page;
	
	
	
}


function main_status(){

$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}		
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('pureftd_status',$_GET["hostname"]));	
	if($ini->_params["SQLGREY"]["running"]==0){
		$img="okdanger32.png";
		$rouage='rouage_on.png';
		$rouage_title='{start_service}';
		$rouage_text='{start_service_text}';
		$error= "";
		$js="SQlgreyActionService(\"{$_GET["hostname"]}\",\"start\")";
		$status="{stopped}";
	}else{
		$img="ok32.png";
		$status="running";
		$rouage='rouage_off.png';
		$rouage_title='{stop_service}';
		$rouage_text='{stop_service_text}';		
		$js="SQlgreyActionService(\"{$_GET["hostname"]}\",\"stop\")";
	}
	
	$status="
	
	<table style='width:100%'>
	<tr>
	<td valign='top'>
		<img src='img/$img'>
	</td>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
		<td align='right' nowrap><strong>{APP_SQLGREY}:</strong></td>
		<td><strong>$status</strong></td>
		</tr>		
		<tr>
		<td align='right'><strong>{pid}:</strong></td>
		<td><strong>{$ini->_params["SQLGREY"]["master_pid"]}</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{memory}:</strong></td>
		<td><strong>{$ini->_params["SQLGREY"]["master_memory"]}&nbsp; mb</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{version}:</strong></td>
		<td><strong>{$ini->_params["SQLGREY"]["master_version"]}</strong></td>
		</tr>	
		<tr>
		<td align='right' nowrap><strong>white list {last_update}:</strong></td>
		<td><strong>{$ini->_params["SQLGREY"]["fqdn_wl_date"]}</strong></td>
		</tr>			
		<tr><td colspan=2>$error</td></tr>	
		<tr><td colspan=2>&nbsp;</td></tr>	
		
		</table>		
	</td>
	</tr>
	</table>";
	
	$status=RoundedLightGreen($status);
	$status_serv=RoundedLightGrey(Paragraphe($rouage ,$rouage_title. " (squid)",$rouage_text,"javascript:$js"));
	
	$tpl=new templates();
	
	return $tpl->_ENGINE_parse_body("<p class=caption>{crossroads_explain}</p>$status");
	
}


function main_master_config(){
	$ldap=new clladp();
	$cross=new crossroads();
	
	$sys=new systeminfos();
	$sys->ParseIP();
	
	$sys_ips=$sys->array_tcp_addr;
	$sys->array_tcp_addr[null]='{or_select}';
	
	$list_ip=Field_array_Hash($sys->array_tcp_addr,"PostfixMasterServerIdentity_ip",null,null,null,0,'width:150px');
	
	$CrossRoadsBalancingServerIP=Field_array_Hash($sys_ips,"CrossRoadsBalancingServerIP",$cross->CrossRoadsBalancingServerIP,null,null,0,'width:150px');
	
	$html=main_tabs() ."<br>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'>
	<img src='img/green-server.png'>
	</td>
	<td valign='top'>
	<H5>{main_settings}</H5>
	<p class=caption>{main_server_text}</p>" . RoundedLightGrey("
	<table style='width:100%'>
	<tr>
		<td align='right'><strong>{replicate_suffix}:</strong></td>
		<td><strong>$ldap->suffix</td>
	</tr>
	<tr>
		<td colspan=2 class=caption align='right'>{replicate_suffix_text}</td>
	</tr>
	<tr>
		<td align='right'><strong>{CrossRoadsBalancingServerName}:</strong></td>
		<td><strong>" . Field_text('CrossRoadsBalancingServerName',$cross->CrossRoadsBalancingServerName,'width:150px')."</td>
	</tr>	
	<tr>
		<td colspan=2 class=caption align='right'>{CrossRoadsBalancingServerName_text}</td>
	</tr>	

	<tr>
		<td align='right'><strong>{CrossRoadsPoolingTime}:</strong></td>
		<td><strong>" . Field_text('CrossRoadsPoolingTime',$cross->CrossRoadsPoolingTime,'width:150px')."</td>
	</tr>	
	<tr>
		<td colspan=2 class=caption align='right'>{CrossRoadsPoolingTime_text}</td>
	</tr>		
	
	
	<tr>
		<td align='right'><strong>{PostfixMasterServerIdentity}:</strong></td>
		<td><strong>" . Field_text('PostfixMasterServerIdentity',$cross->PostfixMasterServerIdentity,'width:150px')."</td>
	</tr>
	<tr>
		<td align='right'>&nbsp;</td>
		<td><strong>$list_ip</td>
	</tr>

	<tr>
		<td colspan=2 class=caption align='right'>{PostfixMasterServerIdentity_text}</td>
	</tr>		
<tr><td colspan=2 class=caption align='right'><hr></td></tr>		
	<tr>
		<td align='right'><strong>{CrossRoadsBalancingServerIP}:</strong></td>
		<td><strong>$CrossRoadsBalancingServerIP</td>
	</tr>	
<tr>
		<td colspan=2 class=caption align='right'>{CrossRoadsBalancingServerIP_text}</td>
	</tr>		
	
	
	

	<tr>
		<td colspan=2 class=caption align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:CrossRoadsSaveMaster();\"</td>
	</tr>
	</table>")."
	</td>
	</tr>
	</table>
	
	";
$tpl=new templates();
	
	return $tpl->_ENGINE_parse_body($html);	
	
}
function main_master_config_edit(){
	$cross=new crossroads();
	$cross->PostfixMasterServerIdentity=$_GET["PostfixMasterServerIdentity"];
	$cross->CrossRoadsBalancingServerIP=$_GET["CrossRoadsBalancingServerIP"];
	$cross->CrossRoadsPoolingTime=$_GET["CrossRoadsPoolingTime"];
	echo $cross->SaveToLdap();
}


function main_slaves(){
	$ldap=new clladp();
	$cross=new crossroads();
	$warn=RoundedLightBlue("
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'>
		<img src='img/i32.png'>
	</td>
	<td valing='top'>{warning_all_ldap_deleted}</strong></td>
	</tr>
	</table>");
	$html=main_tabs() ."<br>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'>
	<img src='img/red-server.png'>
	</td>
	<td valign='top'>
	<H5>{slaves_servers}</H5>
	<p class=caption>{main_slaves_text}</p>
	
	<table style='width:100%'>
	<tr>
	<td valign='top'>
			" . RoundedLightGreen("
			<table style='width:100%'>
			<tr>
				<td align='right' nowrap><strong>{PostfixSlaveServersIdentity}:</strong></td>
				<td><strong>" . Field_text('PostfixSlaveServersIdentity',null,'width:150px')."</td>
			</tr>
			<tr>
				<td colspan=2 class=caption align='right'>{PostfixSlaveServersIdentity_text}</td>
			</tr>
		<tr>
				<td colspan=2 class=caption align='right'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:CrossRoadsSaveSlave();\"</td>
			</tr>	
			
			</tr>
			</table>") . main_sub_slaves() ."
		</td>
		
		<td valign='top'>
		" . applysettingsGeneral('synchronize',"SynchronizeSlaves()","synchronize_text")."$warn
		</td>
		</tr>
		
		</table>
	</td>
	</tr>
	</table>
	
	";	
	
	

$tpl=new templates();
	
	return $tpl->_ENGINE_parse_body("$html<br>$table<br>");		
}

function main_slaves_addserv(){
	$cross=new crossroads();
	echo trim($cross->AddPostfixSlaveServer($_GET["PostfixSlaveServersIdentity"]));
}

function main_slaves_delete(){
	$cross=new crossroads();
	echo trim($cross->DeletePostfixSlaveServer($_GET["CrossRoadsDeleteServer"]));
}

function main_sub_slaves(){
$cross=new crossroads();	
$arr=$cross->PostfixSlaveServersIdentity;
if(is_array($arr)){
	while (list ($num, $ligne) = each ($arr) ){
		
		if(file_exists("ressources/conf/$ligne.global-status.ini")){
			$img="green-server.png";
			
		}else{$img="red-server.png";}
		
		$ini=new status(1,"ressources/conf/$ligne.global-status.ini");
		$text=$ini->ParseIniStatus();
		$table=$table . 
		
		"<div style='float:left;width:100%;margin:5px'>
		".RoundedLightGrey("
		<table style='width:100%'>
			<tr>
				<td width=1% valign='top'><img src='img/$img'></td>
				<td valign='top'><H5>$ligne</h5><table style='width:100%'>$text</table></td>
			</tr>
			<tr>
				<td colspan=2 align='right'>" . imgtootltip('x.gif','{delete}',"CrossRoadsDeleteServer($num)") ."</td>
			</tr>
		</table>")."
		</div>
		";
		
		
	}
}

return $table;
	
}



function main_tabs(){
	$page=CurrentPageName();
	$array["master-config"]='{main_settings}';
	$array["slaves"]='{slaves_servers}';

	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('mainconfig','$page?main=$num&tab=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}

function main_SynchronizeSlaves(){
	$cross=new crossroads();
	$cross->SynchronizeSlaves();
	
}

?>