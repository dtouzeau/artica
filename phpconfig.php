<?php
include_once('ressources/class.templates.inc');
include_once('ressources/class.ldap.inc');
include_once('ressources/class.users.menus.inc');


		$usersmenus=new usersMenus();
		if(!$usersmenus->AsPostfixAdministrator){
			$tpl=new templates();
			echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";
			die();
		}
		
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["php5DisableMagicQuotesGpc"])){save();exit;}
	if(isset($_GET["options"])){popup_options();exit;}
	if(isset($_GET["modules"])){popup_modules();exit;}
	if(isset($_GET["load-module"])){load_module();exit;}
		
	js();
	
function js(){
$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$prefix=str_replace('-','',$prefix);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{advanced_options}');
	
	
$html="
	var {$prefix}timeout=0;
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	


	function {$prefix}LoadPage(){
		RTMMail(650,'$page?popup=yes','$title');
	}
	
	
	

	
	
var x_SavePHP5AdvancedSettings=function (obj) {
	{$prefix}LoadPage();
	}	
	
	function SavePHP5AdvancedSettings(){
    	var XHR = new XHRConnection();
    	var php5DisableMagicQuotesGpc='';
    	var SSLStrictSNIVHostCheck='';
    	if(document.getElementById('php5DisableMagicQuotesGpc').checked){php5DisableMagicQuotesGpc=1;}else{php5DisableMagicQuotesGpc=0;}
		if(document.getElementById('php5FuncOverloadSeven').checked){php5FuncOverloadSeven=1;}else{php5FuncOverloadSeven=0;}
		if(document.getElementById('SSLStrictSNIVHostCheck').checked){SSLStrictSNIVHostCheck=1;}else{SSLStrictSNIVHostCheck=0;}
		
		
		
		XHR.appendData('php5DisableMagicQuotesGpc',php5DisableMagicQuotesGpc);
		XHR.appendData('php5FuncOverloadSeven',php5FuncOverloadSeven);				
		XHR.appendData('SSLStrictSNIVHostCheck',SSLStrictSNIVHostCheck);
 		document.getElementById('php5div').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    	XHR.sendAndLoad('$page', 'GET',x_SavePHP5AdvancedSettings);
	}

	{$prefix}LoadPage();";

	echo $html;
	}
	
function save(){
	$sock=new sockets();
	$sock->SET_INFO("php5FuncOverloadSeven",$_GET["php5FuncOverloadSeven"]);
	$sock->SET_INFO("php5DisableMagicQuotesGpc",$_GET["php5DisableMagicQuotesGpc"]);
	$sock->SET_INFO("SSLStrictSNIVHostCheck",$_GET["SSLStrictSNIVHostCheck"]);
	$sock->getFrameWork("cmd.php?php-rewrite=yes");
	$sock->getFrameWork("cmd.php?restart-web-server=yes");
	
	
}

function popup(){
		$tpl=new templates();
		$array["options"]="{options}";
		$array["modules"]="{loaded_modules}";
		$page=CurrentPageName();

	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></li>\n");
	}
	
	
	echo "
	<div id=main_config_phpadv style='width:100%;height:450px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_phpadv').tabs({
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

function popup_modules(){
	$array=parsePHPModules();
	$page=CurrentPageName();
	
	
	while (list ($module, $array_f) = each ($array) ){$array_fi[$module]=$module;}
	
	
	
	$array_fi[null]="{select}";
	
	krsort($array_fi);
	
	$table=Field_array_Hash($array_fi,'modules-choose',null,"PhpLoadModule()",null,0,"font-size:16px;padding:3px;font-weight:bold");
	
	$html="
	$table
	<div id='show-module'></div>
	<script>
		function PhpLoadModule(module){
			LoadAjax('show-module','$page?load-module='+document.getElementById('modules-choose').value);
		}
	</script>
	";
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
}

function load_module(){
	$array=parsePHPModules();
	$module=$array[$_GET["load-module"]];
	
	$html[]="<table style='width:100%;padding:4px;margin:5px;border:1px solid #005447'>";
	
	while (list ($index, $data) = each ($module) ){
		$html[]="<tr>";
		$html[]="<td class=legend valign='top' style='font-size:14px'>$index:</td>";
		$html[]="<td><strong style='font-size:12px'>";
		if(is_array($data)){
			while (list ($a, $b) = each ($data) ){
				$html[]="<li style='font-size:12px'>$a:$b</li>";
			}
		}else{
			$html[]=$data;
		}
		$html[]="</strong></td>";
		$html[]="</tr>";
	}
	
	$html[]="</table>";
	echo implode("\n",$html);
	
}

	
function popup_options(){
	
	$sock=new sockets();
	$php5FuncOverloadSeven=$sock->GET_INFO("php5FuncOverloadSeven");
	$php5FuncOverloadSeven=Field_checkbox("php5FuncOverloadSeven",1,$php5FuncOverloadSeven);
	
	
	$DisableMagicQuotesGpc=$sock->GET_INFO("php5DisableMagicQuotesGpc");
	$DisableMagicQuotesGpc=Field_checkbox("php5DisableMagicQuotesGpc",1,$DisableMagicQuotesGpc);
	
	$SSLStrictSNIVHostCheck=$sock->GET_INFO("SSLStrictSNIVHostCheck");
	$SSLStrictSNIVHostCheck=Field_checkbox("SSLStrictSNIVHostCheck",1,$SSLStrictSNIVHostCheck);	
	
	$html="<h1>{advanced_options}</H1>
	<div id='php5div'>
	<table width=100%>
	<tr>
		<td valign='top' class=legend nowrap>{php5FuncOverloadSeven}:</td>
		<td valign='top'>$php5FuncOverloadSeven</td>
		<td>{php5FuncOverloadSeven_text}</td>
	</tr>
	<tr><td colspan=3 align='right'><hr></td>	
	<tr>
		<td valign='top' class=legend nowrap>{DisableMagicQuotesGpc}:</td>
		<td valign='top'>$DisableMagicQuotesGpc</td>
		<td>{DisableMagicQuotesGpc_text}</td>
	</tr>	
	<tr><td colspan=3 align='right'><hr></td>
	<tr>
		<td valign='top' class=legend nowrap>{SSLStrictSNIVHostCheck}:</td>
		<td valign='top'>$SSLStrictSNIVHostCheck</td>
		<td>{SSLStrictSNIVHostCheck_text}</td>
	</tr>		
	<tr>
		<td colspan=3 align='right'>
		<hr>". button('{edit}','SavePHP5AdvancedSettings()')."
		
		</td>
	</tr> 
	</table>
	</div>
";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function parsePHPModules() {
 ob_start();
 phpinfo(INFO_MODULES);
 $s = ob_get_contents();
 ob_end_clean();

 $s = strip_tags($s,'<h2><th><td>');
 $s = preg_replace('/<th[^>]*>([^<]+)<\/th>/',"<info>\\1</info>",$s);
 $s = preg_replace('/<td[^>]*>([^<]+)<\/td>/',"<info>\\1</info>",$s);
 $vTmp = preg_split('/(<h2[^>]*>[^<]+<\/h2>)/',$s,-1,PREG_SPLIT_DELIM_CAPTURE);
 $vModules = array();
 for ($i=1;$i<count($vTmp);$i++) {
  if (preg_match('/<h2[^>]*>([^<]+)<\/h2>/',$vTmp[$i],$vMat)) {
   $vName = trim($vMat[1]);
   $vTmp2 = explode("\n",$vTmp[$i+1]);
   foreach ($vTmp2 AS $vOne) {
   $vPat = '<info>([^<]+)<\/info>';
   $vPat3 = "/$vPat\s*$vPat\s*$vPat/";
   $vPat2 = "/$vPat\s*$vPat/";
   if (preg_match($vPat3,$vOne,$vMat)) { // 3cols
     $vModules[$vName][trim($vMat[1])] = array(trim($vMat[2]),trim($vMat[3]));
   } elseif (preg_match($vPat2,$vOne,$vMat)) { // 2cols
     $vModules[$vName][trim($vMat[1])] = trim($vMat[2]);
   }
   }
  }
 }
 return $vModules;
}

?>