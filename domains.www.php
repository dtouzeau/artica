<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.apache.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.pdns.inc');
	
	$usersmenus=new usersMenus();
	if(!$usersmenus->AsOrgAdmin){
		$tpl=new templates();
		echo $tpl->javascript_parse_text('{ERROR_NO_PRIVS}');
		exit;
	}
	
	if(isset($_GET["add"])){js_add();exit;}
	if(isset($_GET["popup-add"])){popup_add();exit;}
	if(isset($_GET["wwwInfos"])){wwwInfos();exit;}
	if(isset($_GET["add-www-service"])){add_web_service();exit;}
	if(isset($_GET["delete-www-service"])){delete_www_service();exit;}
	
js();

function delete_www_service(){
	$sock=new sockets();
	echo $sock->getFrameWork('cmd.php?vhost-delete='.$_GET["delete-www-service"]);
}

function js_add(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{ADD_WEB_SERVICE}",'domains.manage.org.index.php');
	$page=CurrentPageName();
	$prefix="add_".str_replace(".","_",$page);
	$ou=$_GET["ou"];
	$www_delete_web_service_confirm=$tpl->javascript_parse_text("{www_delete_web_service_confirm}");
	$html="
	var timeout=0;
	
	function {$prefix}_start(){
		YahooWin(650,'$page?popup-add=yes&ou=$ou&host={$_GET["host"]}');
		
	}
	
	function AddWWWServiceChange(){
		LoadAjax('wwwInfos','$page?wwwInfos='+document.getElementById('ServerWWWType').value);   
	}
	
var x_AddWebService= function (obj) {
			var results=obj.responseText;
			if(results.length>0){
				alert(results);
				document.getElementById('wwwInfos').innerHTML='';
				return;
			}
			document.getElementById('wwwInfos').innerHTML='';
			YahooWinHide();
			if(document.getElementById('ORG_VHOSTS_LIST')){
				LoadAjax('ORG_VHOSTS_LIST','domains.manage.org.index.php?ORG_VHOSTS_LIST=$ou');
			}
			
			
			}

	var x_DelWebService= function (obj) {
			var results=obj.responseText;
			if(results.length>0){
				alert(results);
				document.getElementById('wwwInfos').innerHTML='';
				}
			document.getElementById('wwwInfos').innerHTML='';
			YahooWinHide();
			if(document.getElementById('ORG_VHOSTS_LIST')){
				LoadAjax('ORG_VHOSTS_LIST','domains.manage.org.index.php?ORG_VHOSTS_LIST=$ou');
			}
	}			
	
	function AddWebService(){
		var XHR = new XHRConnection();
		XHR.appendData('add-www-service','yes');
		XHR.appendData('ou','$ou');
		if(document.getElementById('ServerWWWType')){XHR.appendData('ServerWWWType',document.getElementById('ServerWWWType').value);}
		if(document.getElementById('servername')){XHR.appendData('servername',document.getElementById('servername').value);}
		if(document.getElementById('domain')){XHR.appendData('domain',document.getElementById('domain').value);}
		if(document.getElementById('IP')){XHR.appendData('IP',document.getElementById('IP').value);}
		XHR.appendData('host','{$_GET["host"]}');
		
		if(document.getElementById('WWWSSLMode').checked){
			XHR.appendData('WWWSSLMode','TRUE');
		}else{
			XHR.appendData('WWWSSLMode','FALSE');
		}
		
		
		
		XHR.appendData('WWWMysqlUser',document.getElementById('WWWMysqlUser').value);
		XHR.appendData('WWWMysqlPassword',document.getElementById('WWWMysqlPassword').value);
		if(document.getElementById('WWWAppliUser')){XHR.appendData('WWWAppliUser',document.getElementById('WWWAppliUser').value);}
		XHR.appendData('WWWAppliPassword',document.getElementById('WWWAppliPassword').value);
		document.getElementById('wwwInfos').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    	XHR.sendAndLoad('$page', 'GET',x_AddWebService);		
		}
		
		function DelWebService(){
			if(confirm('$www_delete_web_service_confirm')){
				var XHR = new XHRConnection();
				XHR.appendData('delete-www-service','{$_GET["host"]}');
				document.getElementById('wwwInfos').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
				XHR.sendAndLoad('$page', 'GET',x_DelWebService);
			}	
			
		}
	
	
	{$prefix}_start();
	";
	
	echo $html;
	
}


function popup_add(){
	
	$list=listOfAvailableServices();
	$ldap=new clladp();
	$domns=$ldap->hash_get_domains_ou($_GET["ou"]);
	$domns[null]="{select}";
	$domains=Field_array_Hash($domns,"domain",null);
	
	
	
	
	$ip=new networking();
	$ips=$ip->ALL_IPS_GET_ARRAY();
	$ips[null]="{select}";
	$eth=Field_array_Hash($ips,'IP');
	$title="{ADD_WEB_SERVICE}";
	$button="{add}";
	$img="www-add-128.png";
	
	$server_row="<tr>
					<td class=legend>{www_server_name}:</td>
					<td>". Field_text("servername",null,"width:90px")."&nbsp;$domains</td>
				</tr>";
				
	$address_row="<tr>
					<td class=legend>{address}:</td>
					<td>$eth</td>
				</tr>";
				
$h=new vhosts();
	
if($_GET["host"]<>null){
		$title=$_GET["host"];
		$button="{edit}";
		$LoadVhosts=$h->LoadHost($_GET["ou"],$_GET["host"]);
		$img=$h->IMG_ARRAY_128[$LoadVhosts["wwwservertype"]];
		$list="<input type='hidden' id='ServerWWWType' name='ServerWWWType' value='{$LoadVhosts["wwwservertype"]}'>{$LoadVhosts["wwwservertype"]}";
		$server_row="<tr>
					<td class=legend>{www_server_name}:</td>
					<td><strong>{$_GET["host"]}</strong></td>
				</tr>";	
	   $address_row=null;
	   $delete=	"<tr>
	   				
					<td colspan=2 align='right'>".button("{delete}","DelWebService();")."
				</tr>";
	}
	
	$users_row="<tr>
					<td class=legend>{WWWAppliUser}:</td>
					<td>". Field_text("WWWAppliUser",$LoadVhosts["wwwappliuser"],'width:120px')."</td>
				</tr>";

	if($_GET["host"]<>null){
		if($h->noneeduser[$LoadVhosts["wwwservertype"]]){
			$users_row=null;	
		}
	}
	
	
	if($LoadVhosts["wwwsslmode"]=="TRUE"){$LoadVhosts["wwwsslmode"]=1;}else{$LoadVhosts["wwwsslmode"]=0;}
	
	$html="<H1>$title</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/$img'>
		<td valign='top'>
			<table style='width:100%'>
				<tr>
					<td class=legend>{service_type}:</td>
					<td>$list</td>
				</tr>
				$server_row
				$address_row
				<tr>
					<td class=legend>{https_mode} ({port}:443):</td>
					<td>". Field_checkbox("WWWSSLMode",1,$LoadVhosts["wwwsslmode"])."</td>
				</tr>				
				<tr>
					<td class=legend>{WWWMysqlUser}:</td>
					<td>". Field_text("WWWMysqlUser",$LoadVhosts["wwwmysqluser"],'width:120px')."</td>
				</tr>
				<tr>
					<td class=legend>{WWWMysqlPassword}:</td>
					<td>". Field_password("WWWMysqlPassword",$LoadVhosts["wwwmysqlpassword"])."</td>
				</tr>				
				$users_row
				<tr>
					<td class=legend>{WWWAppliPassword}:</td>
					<td>". Field_password("WWWAppliPassword",$LoadVhosts["wwwapplipassword"])."</td>
				</tr>		
				<tr>
					<td colspan=2 align='right'><hr>
					". button("$button","AddWebService();")."
				</tr>
				$delete							
				<tr>
					<td colspan=2 valign='top'>
						<div id='wwwInfos'></div>	
					</td>
				</tr>

					
			</table>
			
		</td>
	</tr>
	</table>
	
	
	";
	
	if($h->noneeduser["$serv"]){
		$script_1="
		if(document.getElementById('WWWAppliUser')){document.getElementById('WWWAppliUser').disabled=true;}
		if(document.getElementById('WWWAppliPassword')){document.getElementById('WWWAppliPassword').disabled=true;}
		";
	}else{
		$script_1="
		if(document.getElementById('WWWAppliUser')){document.getElementById('WWWAppliUser').disabled=false;}
		if(document.getElementById('WWWAppliPassword')){document.getElementById('WWWAppliPassword').disabled=false;}
		";
	}
	
	if($h->noneeduser_mysql["$serv"]){
		$script_2="
		if(document.getElementById('WWWMysqlUser')){document.getElementById('WWWMysqlUser').disabled=true;}
		if(document.getElementById('WWWMysqlPassword')){document.getElementById('WWWMysqlPassword').disabled=true;}
		";
	}else{
		$script_2="
		if(document.getElementById('WWWMysqlUser')){document.getElementById('WWWMysqlUser').disabled=false;}
		if(document.getElementById('WWWMysqlPassword')){document.getElementById('WWWMysqlPassword').disabled=false;}
		";
	}	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'domains.manage.org.index.php')."
	<script>
		function wwwEnableDisable(){
			$script_1
			$script_2
		}
		setTimeout('wwwEnableDisable()',1000);
	</script>";
	
	
}


function wwwInfos(){
	$tpl=new templates();
	$h=new vhosts();
	$serv=$_GET["wwwInfos"];
	if(is_numeric($serv)){return null;}
	if($serv==null){return null;}
	
	if($h->noneeduser["$serv"]){
		$script_1="
		if(document.getElementById('WWWAppliUser')){document.getElementById('WWWAppliUser').disabled=true;}
		if(document.getElementById('WWWAppliPassword')){document.getElementById('WWWAppliPassword').disabled=true;}
		";
	}else{
		$script_1="
		if(document.getElementById('WWWAppliUser')){document.getElementById('WWWAppliUser').disabled=false;}
		if(document.getElementById('WWWAppliPassword')){document.getElementById('WWWAppliPassword').disabled=false;}
		";
	}
	
	if($h->noneeduser_mysql["$serv"]){
		$script_2="
		if(document.getElementById('WWWMysqlUser')){document.getElementById('WWWMysqlUser').disabled=true;}
		if(document.getElementById('WWWMysqlPassword')){document.getElementById('WWWMysqlPassword').disabled=true;}
		";
	}else{
		$script_2="
		if(document.getElementById('WWWMysqlUser')){document.getElementById('WWWMysqlUser').disabled=false;}
		if(document.getElementById('WWWMysqlPassword')){document.getElementById('WWWMysqlPassword').disabled=false;}
		";
	}	
	
$html="<table style='width:100%'>
<tr>
<td valign='top'><img src='img/{$h->IMG_ARRAY_128["$serv"]}'></td>
<td valign='top'>
		<H2 style='margin-bottom:0px'>{{$h->TEXT_ARRAY[$serv]["TITLE"]}}</H2><hr>
		<p style='font-size:12px'>{{$h->TEXT_ARRAY[$serv]["TEXT"]}}</p>
		<hr>
</td>
</tr>
</table>
		";
	
	$tpl=new templates();
	
	echo "<div style='padding:3px;border:1px dotted #CCCCCC;margin-top:5px'>
		".$tpl->_ENGINE_parse_body($html,'domains.manage.org.index.php')."
	</div>
	<script>
	$script_1
	$script_2
	</script>";
}

function add_web_service(){
	$ou=$_GET["ou"];
	$ServerWWWType=$_GET['ServerWWWType'];
	$servername=$_GET["servername"];
	$domain=$_GET["domain"];
	$IP=$_GET["IP"];
	$tpl=new templates();
	$noneed_mysql=false;
	$noneed_appliPass=false;
	
	if($_GET["host"]==null){
		if($servername==null){echo $tpl->_ENGINE_parse_body("{server_name}=null");exit;}
		if($IP==null){echo $tpl->_ENGINE_parse_body("{address}=null");exit;}    
		if($domain==null){echo $tpl->_ENGINE_parse_body("{domain}=null");exit;}
		if($ServerWWWType==null){echo $tpl->_ENGINE_parse_body("{service_type}=null");exit;}
	}
	
	
	
	$noneed_mysql=false;
	
	if($ou==null){echo $tpl->_ENGINE_parse_body("{organization}=null");exit;}
	$vhosts=new vhosts($_GET["ou"]);
	$vvhosts=new vhosts();
	$noneeduser=$vvhosts->noneeduser;
	$noneeduser_mysql=$vvhosts->noneeduser_mysql;
	
	
	if($noneeduser[$ServerWWWType]){$noneed_appliPass=true;}
	if($noneeduser_mysql[$ServerWWWType]){$noneed_mysql=true;}
	
	
	
	

	if(!$noneed_mysql){
		if($_GET["WWWMysqlUser"]==null){
			echo $tpl->_ENGINE_parse_body("\"$ServerWWWType\":\n{WWWMysqlUser}=null\n$noneed_mysql\nL.".__LINE__);
			exit;
		}
		if($_GET["WWWMysqlPassword"]==null){
			echo $tpl->_ENGINE_parse_body("$ServerWWWType:{WWWMysqlPassword}=null");exit;
			}
	}
	
	if(!$noneeduser["$ServerWWWType"]){
		if($_GET["WWWAppliUser"]==null){
			echo $tpl->_ENGINE_parse_body("$ServerWWWType:\n{WWWAppliUser}=null\n{$vhosts->noneeduser["$ServerWWWType"]}");
		exit;}
	}
	
	if(!$noneed_appliPass){
		if($_GET["WWWAppliPassword"]==null){echo $tpl->_ENGINE_parse_body("{WWWAppliPassword}=null");exit;}
	}
	

	if($_GET["host"]==null){
		$hostname=$servername.".".$domain;
		$pdns=new pdns($domain);
		$pdns->EditIPName($servername,$IP,"A",null);		
	}else{
		$hostname=$_GET["host"];
	}
	
	$hostname=str_replace(" ","_",$hostname);
	$database=str_replace("-","_",$database);
	$database=str_replace(".","_",$database);
	
	
	$vhosts->ou=$ou;
	$vhosts->BuildRoot();
	$vhosts->WWWAppliPassword=$_GET["WWWAppliPassword"];
	$vhosts->WWWAppliUser=$_GET["WWWAppliUser"];
	$vhosts->WWWMysqlUser=$_GET["WWWMysqlUser"];
	$vhosts->WWWMysqlPassword=$_GET["WWWMysqlPassword"];
	$vhosts->WWWSSLMode=$_GET["WWWSSLMode"];
	$vhosts->Addhost($hostname,$ServerWWWType);
	
	$sock=new sockets();
	writelogs("Scheduling =>cmd.php?install-web-services=yes",__FUNCTION__,__FILE__,__LINE__);
	$sock->getFrameWork("cmd.php?install-web-services=yes");
	
	}

function listOfAvailableServices(){
	
	$user=new usersMenus();
	$array[]="{select}";
	
	if($user->LMB_LUNDIMATIN_INSTALLED){
		$array["LMB"]="{APP_LMB}";
		
	}
	
	if($user->JOOMLA_INSTALLED){
		$array["JOOMLA"]="{APP_JOOMLA}";
	}
	
	if($user->SUGARCRM_INSTALLED){
		$array["SUGAR"]="{APP_SUGARCRM}";
	}	
	
	if($user->roundcube_installed){
		$array["ROUNDCUBE"]="{APP_ROUNDCUBE}";
	}
	
	if($user->OBM2_INSTALLED){
		$array["OBM2"]="{APP_OBM2}";
	}
	if($user->OPENGOO_INSTALLED){
			$array["OPENGOO"]="{APP_OPENGOO}";
	}
	
	if($user->GROUPOFFICE_INSTALLED){
			$array["GROUPOFFICE"]="{APP_GROUPOFFICE}";
	}	
	
	if($user->ZARAFA_INSTALLED){
		$array["ZARAFA"]="{APP_ZARAFA}";
		$array["ZARAFA_MOBILE"]="{APP_ZARAFA_MOBILE_ACCESS}";
	}
	
	if($user->DRUPAL_INSTALLED){
		$array["DRUPAL"]="{APP_DRUPAL}";
	}

	
	
	$array["ARTICA_USR"]="{APP_ARTICA_USR}";
	
	$tpl=new templates();
	
	
	
	return $tpl->_ENGINE_parse_body(Field_array_Hash($array,'ServerWWWType',null,"AddWWWServiceChange()"));
}


?>