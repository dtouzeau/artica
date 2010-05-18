<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");
include_once("ressources/class.main_cf.inc");
$user=new usersMenus();

if(isset($_GET["js"])){echo popup_js();exit;}

if($user->AsPostfixAdministrator==false){header('location:logon.php');}
if(isset($_GET["main"])){switch_main();exit();}
if(isset($_GET["key"])){main_save();exit;}
if(isset($_GET["table"])){main_table();exit;}
if(isset($_GET["popup"])){popup_main();exit;}
page();

function page(){
$page=CurrentPageName();
$html="
<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function demarre(){
   tant = tant+1;
   reste=10-tant;
	if (tant < 10 ) {                           
      timerID = setTimeout(\"demarre()\",3000);
      } else {
               tant = 0;
               //document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
               ChargeLogs();
               demarre();                                //la boucle demarre !
   }
}


function ChargeLogs(){
	LoadAjax('servinfos','admin.index.php?postfix-status=yes&hostname={$_GET["hostname"]}');
	}
	
	
function MainPersoDelete(num){
LoadAjax('main_maincf_datas','$page?table=yes&delete='+num+'&hostname={$_GET["hostname"]}');
}

function ChargeTable(){
	setTimeout(\"ChargeT()\",1000);
	}	
	
function ChargeT(){
LoadAjax('main_maincf_datas','$page?table=yes&hostname={$_GET["hostname"]}');
}
</script>	


<table style='width:650px' align=center>
<tr>
<td valign='top'>
	<img src='img/postfix2.jpg'>
</td>
<td valign='top'>
<div id='servinfos'></div>
</td>
</tr>
<tr>
	<td colspan=2>
	<div id='main_config_cf_datas'></div>
	<div id='main_maincf_datas'></div>
	</td>
</tr>
		
</table>
<script>demarre();ChargeLogs()</script>
<script>LoadAjax('main_config_cf_datas','$page?main={$_GET["main"]}&hostname=$hostname')</script>
<script>ChargeTable();</script>
";
$JS["JS"][]='js/postfix-tls.js';
$tpl=new template_users('{perso_maincf}',$html,0,0,0,0,$JS);
echo $tpl->web_page;
}


function popup_js(){
	
	$jsdatas=file_get_contents("js/postfix-tls.js");
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{perso_maincf}');
	$page=CurrentPageName();
	$html="
	
	function loadpageMainCf(){
		YahooWinS(600,'$page?popup=yes','$title');
	
	}
	
	loadpageMainCf();
	
function MainPersoDelete(num){
LoadAjax('main_maincf_datas','$page?table=yes&delete='+num+'&hostname={$_GET["hostname"]}');
}

function ChargeTable(){
	setTimeout(\"ChargeT()\",1000);
	}	
	
function ChargeT(){
LoadAjax('main_maincf_datas','$page?table=yes&hostname={$_GET["hostname"]}');
}	
	
	";
	
	echo $html;
	
}

function popup_main(){
	$tpl=new templates();
	
	echo "<H1>".$tpl->_ENGINE_parse_body('{perso_maincf}')."</H1>";
	echo "<div id='main_config_cf_datas'>";
	main_config();
	echo "</div>";
	echo "<div id='main_maincf_datas'>";
	main_table();
	echo "</div>";
}


function switch_main(){
	
	switch ($_GET["main"]) {
		case "main":
			
			break;
	
		default:main_config();exit;break;
	}
	
	
	
}

function main_config(){
	
	$page=CurrentPageName();
	$options=array("r"=>"{replace}","a"=>"{add}");
	$intro="<table style='width=100%'>
	<tr>
	<td valign='top'><p class=caption>{main_help}</p></td>
	<td valign='top'>".applysettings_postfix() ."</td>
	</tr>
	</table>";
	
	$form1="
	<form name='FFM0'>
	<table style='width:100%'>
	<tr>
		<td width=1% nowrap><strong>{maincf_key}:</strong></td>
		<td>" . Field_text('key',null,'width:100%')."</td>
		<td width=1% nowrap><strong>{maincf_data}:</strong></td>
		<td>" . Field_text('data',null,'width:100%')."</td>
		<td width=1% nowrap><strong>{option}:</strong></td>
		<td>" . Field_array_Hash($options,'opt')."</td>
	</tr>
	<tr>
	<td colspan=6 align='right'><hr><input type='button' OnClick=\"javascript:ParseForm('FFM0','$page',true);ChargeTable();\" value='{add}&nbsp;&raquo;'></td>
	</tr>
	</table>
	</form>
	";
	
	$form1=RoundedLightGrey("$form1");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$intro$form1");
	
}

function main_save(){
	$main=new main_perso();
	$main->add(trim($_GET["key"]),trim($_GET["data"]), trim($_GET["opt"]));
	
}

function main_table(){
	$main=new main_perso();
	if(isset($_GET["delete"])){
		$main->Delete($_GET["delete"]);
	}
	
	$options=array("r"=>"{replace}","a"=>"{add}");
	if(!is_array($main->main_array)){return null;}
	while (list ($num, $val) = each ($main->main_array) ){
		$fmname=md5($num);
		$html=$html . "
		
		<div style='margin-top:3px;border:1px dotted #CCCCCC;padding-top:4px;width:100%;height:300px;overflow:auto'>
		<form name='FFM_$fmname'>
	<table style='width:100%' class=table_form>
	<tr>
		<td width=1% nowrap><strong>{maincf_key}:</strong></td>
		<td>" . Field_text('key',$num,'width:100%')."</td>
		<td width=1% nowrap><strong>{maincf_data}:</strong></td>
		<td>" . Field_text('data',$val["VALUE"],'width:100%')."</td>
		<td width=1% nowrap><strong>{option}:</strong></td>
		<td>" . Field_array_Hash($options,'opt',$val["TYPE"])."</td>
		<td width=1%>" . imgtootltip('ed_delete.gif','{delete}',"MainPersoDelete('$num')")."</td>
	</tr>
	<tr>
	
	<td colspan=7 align='right'>
		<table style='width:100%'>
			<tr>
			<td width=99%><code>$num={$val["VALUE"]}</code>&nbsp;</td>
			<td width=1% align='right'><input type='button' OnClick=\"javascript:ParseForm('FFM_$fmname','$page',true);ChargeTable()\" value='{edit}&nbsp;&raquo;'></td>
			</tr>
		</table>
	</td>
	</tr>
	</table>
	</form></div>";
		
		
	}
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}
