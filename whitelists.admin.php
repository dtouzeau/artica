<?php
include_once('ressources/class.templates.inc');

session_start();
$ldap=new clladp();
if(isset($_GET["loadhelp"])){loadhelp();exit;}
if(!isset($_SESSION["uid"])){header('location:logon.php');exit;}
$user=new usersMenus();
if($user->AsPostfixAdministrator==false){header('location:users.index.php');exit();}
if(isset($_GET["whitelist"])){SaveWhiteList();exit;}
if(isset($_GET["del_whitelist"])){del_whitelist();exit;}
if(isset($_GET["js"])){js_popup();exit;}
if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["SelectedDomain"])){popup_switch();exit;}
if(isset($_GET["wblopt"])){wblopt_js();exit;}
if(isset($_GET["wblopt-popup"])){wblopt_popup();exit;}
if(isset($_GET["WBLReplicEnable"])){wblopt_save();exit;}
if(isset($_GET["WBLReplicNow"])){wblopt_replic();exit;}
if(isset($_GET["EnableWhiteListAndBlackListPostfix"])){ArticaRobotsSave();exit;}



page();

function page(){
	
	
	switch ($_GET["main"]) {
		case "white":echo whitelist();exit;break;
		case "black":echo BlackList();exit;break;		
		}
	
$html=
"<p class=caption>{whitelist_explain}</p>
<br>



<div id='list'>" . whitelist() . "</div>

";
$cfg["JS"][]="js/wlbl.js";
$tpl=new template_users('{global_whitelist}',$html,0,0,0,0,$cfg);	
echo $tpl->web_page;

}



function js_popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{global_whitelist}');
	$data=file_get_contents('js/wlbl.js');
	$html="
	$data
	
	function StartIndex(){
		YahooWinS(700,'$page?popup=yes','$title');
		setTimeout('SelectDomain()',1000);
	}
	
	StartIndex();
	
	function SelectDomain(){
		var selected_domain=document.getElementById('selected_domain').value;
		var selected_form=document.getElementById('selected_form').value;
		LoadAjax('wblarea','$page?SelectedDomain='+selected_domain+'&type='+selected_form);
	}
	
	function EnableWhiteListAndBlackListPostfixEdit(){
		var EnableWhiteListAndBlackListPostfix=document.getElementById('EnableWhiteListAndBlackListPostfix').value;
		LoadAjax('EnableWhiteListAndBlackListPostfixDiv','$page?EnableWhiteListAndBlackListPostfix='+EnableWhiteListAndBlackListPostfix);
	
	}
	
	
	";
	echo $html;
	}
	
function wblopt_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{options}');
	$html="	
	function StartIndex2(){
		YahooWin(600,'$page?wblopt-popup=yes','$title');
	}
	

	
var x_WBLReplicNow= function (obj) {
	var results=obj.responseText;
	alert(results);
	StartIndex2();
	}
	
	
	function WBLReplicNow(){
		var XHR = new XHRConnection();
		XHR.appendData('WBLReplicNow','yes');
		document.getElementById('wbldiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_WBLReplicNow);
	
	}		
	
	StartIndex2();
	";
	
	echo $html;
}

function ArticaRobotsSave(){
	$sock=new sockets();
	$sock->SET_INFO("EnableWhiteListAndBlackListPostfix",$_GET["EnableWhiteListAndBlackListPostfix"]);
	echo ArticaRobots();
	
}

function ArticaRobots(){
	
	$sock=new sockets();
	$EnableWhiteListAndBlackListPostfix=$sock->GET_INFO('EnableWhiteListAndBlackListPostfix');
	$p=Paragraphe_switch_img('{enable_artica_wbl_robots}','{enable_artica_wbl_robots_text}',
	"EnableWhiteListAndBlackListPostfix",$EnableWhiteListAndBlackListPostfix,'{enable_disbable}',300);
	$html="
	<table style='width:100%' class=table_form>
	<tr>
	<td>
	<div style='padding:3px;'>$p
	<div style='width:101%;text-align:right'>
		<input type='button' value='{edit}&nbsp;&nbsp;&raquo;&raquo;' OnClick=\"javascript:EnableWhiteListAndBlackListPostfixEdit();\">
	</div>
	</div>
	</td>
	</tr>
	</table>
	";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
	
	
}


function wblopt_replic(){
	$sock=new sockets();
	$sock->getfile("WBLReplicNow");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');	
	
}

function wblopt_popup(){
	$page=CurrentPageName();
	
	for($i=1;$i<30;$i++){
		$va=$i*10;
		$array[$va]=$va;
	}
	
	
	$auto=new autolearning_spam();
	
$days="<table style='width:100%' class=table_form>
<tr><td valign='top'><H3>{schedule}</h3>
<p class=caption>{run_every}...</p>
</td></tr>";	

for($i=0;$i<60;$i++){
	if($i<10){$mins[$i]="0$i";}else{$mins[$i]=$i;}
	}
for($i=0;$i<24;$i++){
	if($i<10){$hours[$i]="0$i";}else{$hours[$i]=$i;}
	}	
	
preg_match('#(.+?):(.+)#',$auto->WBLReplicSchedule["CRON"]["time"],$re);
$minutes=Field_array_Hash($mins,'msched',$re[2]);
$hour=Field_array_Hash($hours,'hsched',$re[1]);



while (list ($num, $line) = each ($auto->array_days)){
	$day=$line;
	$enabled=$auto->WBLReplicSchedule["DAYS"][$day];
	$days=$days."
	<tr>
		<td class=legend>{$day}</td>
		<td>".Field_checkbox($day,1,$enabled)."</td>
	</tr>";
	
}	

$days=$days."
<tr>
			<td class=legend>{time}</td>
			<td>$hour&nbsp;:&nbsp;$minutes</td>
		</tr>
</table>";

	
	$ArticaRobots=ArticaRobots();
	$WBLReplicEachMin=$auto->WBLReplicEachMin;
	
	if($WBLReplicEachMin==null){$WBLReplicEachMin=60;}
	if(preg_match('#([0-9]+)h#',$WBLReplicEachMin,$re)){
		$WBLReplicEachMin=$re[1]*60;
	}
	
	$form1="<table style='width:100%' class=table_form>
					<tr>
						<td class=legend>{enable_learning_spam_mailbox}:</td>
						<td>" . Field_checkbox('WBLReplicEnable',1,$auto->WBLReplicEnable) ."</td>
					</tr>
					<tr>
						<td class=legend>{enable_learning_ham_mailbox}:</td>
						<td>" . Field_checkbox('WBLReplicaHamEnable',1,$auto->WBLReplicaHamEnable) ."</td>
					</tr>
					<tr>
					<tr><td colspan=2><hr></td></tr>
						<td class=legend colspan=2>
							<input type='button' OnClick=\"javascript:WBLReplicNow()\" value='{replicate_now}&nbsp;&raquo;'>
						</td>
					</tr>	
				</table>";
	
	
	
	$html="<H1>{autolearning}</H1>
	<p class=caption>{autolearning_text}</p>
	<div id='wbldiv'>
	<form name='ffm1rep'>
	<table style='width:100%' class=table_form>
	<tr>
		<td valign='top'>
		  $form1
		  <div id='EnableWhiteListAndBlackListPostfixDiv'>
		  $ArticaRobots
		  </div>
		</td>
		<td valign='top'>
			
			$days
		</td>
	</tr>
<tr>
		<td colspan=2 align='right'>
			<hr>
		</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'>
			<input type='button' OnClick=\"javascript:ParseForm('ffm1rep','$page',true);\" value='{edit}&nbsp;&raquo;'>
		</td>
	</tr>	
	</table>
	</form>
	</div>
		";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}

function wblopt_save(){
	$sock=new sockets();
	$WBLReplicEachMin=$_GET["WBLReplicEachMin"];
		if($WBLReplicEachMin>60){
		$WBLReplicEachMin=round($WBLReplicEachMin/60).'h';
	}
	
	$auto=new autolearning_spam();
	$auto->WBLReplicEachMin=$WBLReplicEachMin;
	$auto->WBLReplicaHamEnable=$_GET["WBLReplicaHamEnable"];
	$auto->WBLReplicEnable=$_GET["WBLReplicEnable"];
	
$time="{$_GET["hsched"]}:{$_GET["msched"]}";

$auto->WBLReplicSchedule["CRON"]["time"]=$time;
$auto->WBLReplicSchedule["TIME"]["time"]=$time;
	
	while (list ($num, $line) = each ($_GET)){
		$auto->WBLReplicSchedule["DAYS"][$num]=$line;
	}
	$auto->Save();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	
	}

function popup_switch(){
	$domain=$_GET["SelectedDomain"];
	$type=$_GET["type"];
	
	
	$formbl="<table style='width:99%' class=table_form>
			<tr>
			<td><strong>{from}:</td>
			<td>" . Field_text('wlfrom',$_GET["whitelist"],'width:150px',
			null,null,null,false,"AddblwformCheck(1,event)") ."</td>
			<td><strong>{recipient}:</td>
			<td>" . Field_text('wlto',$_GET["recipient"],'width:150px',
			null,null,null,false,"AddblwformCheck(1,event)") ."</td>
			<td align='right'><input type='button' OnClick=\"javascript:Addblwform(1);\" value='{add}&nbsp;&raquo;'></td>
			</tr>
		</table>";
	
	
	$formwl="	<table style='width:99%' class=table_form>
			<tr>
			<td><strong>{from}:</td>
			<td>" . Field_text('wlfrom',$_GET["whitelist"],'width:150px',
			null,null,null,false,"AddblwformCheck(0,event)") ."</td>
			<td><strong>{recipient}:</td>
			<td>" . Field_text('wlto',$_GET["recipient"],'width:150px',
			null,null,null,false,"AddblwformCheck(0,event)") ."</td>
			<td align='right'><input type='button' OnClick=\"javascript:Addblwform(0);\" value='{add}&nbsp;&raquo;'></td>
			</tr>
		</table>";
	
	$tpl=new templates();
	switch ($type) {
		case "white":echo $tpl->_ENGINE_parse_body($formwl.whitelistdom($domain));exit;break;
		case "black":echo $tpl->_ENGINE_parse_body($formbl.blacklistdom($domain));exit;break;
		case null:echo $tpl->_ENGINE_parse_body(whitelistdom($domain)).$tpl->_ENGINE_parse_body(blacklistdom($domain));exit;break;	
		}	
	
}


function popup(){
	$ldap=new clladp();
	$page=CurrentPageName();
	$domain=$ldap->hash_get_all_domains();
	$domain[null]='{all}';
	$array["white"]='{white list}';
	$array["black"]='{black list}';	
	$array[null]='{all}';
	$field=Field_array_Hash($domain,'selected_domain',null,"SelectDomain()",null,0,"font-size:13px;padding:3px");
	$field2=Field_array_Hash($array,'selected_form',null,"SelectDomain()",null,0,"font-size:13px;padding:3px");
	$tpl=new templates();
	
	$whitelist_explain=$tpl->_ENGINE_parse_body("{whitelist_explain}");
	$whitelist_explain="<p style='font-size:13px'>$whitelist_explain</p>";
	
	$old_wbl="<td valign='top'>
			<table style='width:100%' class=table_form ". element_rollover("Loadjs('$page?wblopt=yes')").">
				<tr>
					<td width=1% valign='top'>" . imgtootltip('32-settings-black.png',"{options}","Loadjs('$page?wblopt=yes')")."</td>
					<td valign='top'>
						<div style='font-size:13px;font-weight:bold'>{autolearning}</div>
						<p class=caption>{autolearning_text}</p>
					</td>
				</tr>
			</table>
		</td>";
	
	$html="
	
	<table style='width:100%'>
	
	<tr>	
	<td valign='top'>
	<table style='widh:100%'>
	<tr>
		
		<td valign='top'>
			<div style=''>$whitelist_explain</div>
		</td>
	</tr>
	</table>
	</td>
	<td valign='top'>
		<table style='width:100%;margin-right:0px' class=table_form>
		<td class=legend>{domain}:</td>
		<td>$field</td>
		</tr>
		<tr>
		<td class=legend>{type}:</td>
		<td>$field2</td>	
		</tr>
		</table>
	</td>	
	</tr>
	</table><div id='wblarea' style='width:100%;height:250px;overflow:auto'></div>";
	
	
	
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function main_tabs(){
	$page=CurrentPageName();
	$array["white"]='{white list}';
	$array["black"]='{black list}';
	if($_GET["section"]==null){$_GET["section"]="white";}
		
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["section"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('list','$page?main=$num&section=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	return "<br><div id=tablist>$html</div><br>";		
}	


function whitelist(){
	$ldap=new clladp();
	
	$domain=$ldap->hash_get_all_domains();
	if(!is_array($domain)){return null;}
	
	while (list ($num, $line) = each ($domain)){
		$html=$html . whitelistdom($num);
		
		
	}
	$page=main_tabs () . "<br>" . RoundedLightGreen("
		<table style='width:99%'>
			<tr>
			<td><strong>{from}:</td>
			<td>" . Field_text('wlfrom',$_GET["whitelist"],'width:120px',
			null,null,null,false,"AddblwformCheck(0,event)") ."</td>
			<td><strong>{recipient}:</td>
			<td>" . Field_text('wlto',$_GET["recipient"],'width:120px',
			null,null,null,false,"AddblwformCheck(0,event)") ."</td>
			<td align='right'><input type='button' OnClick=\"javascript:Addblwform(0);\" value='{add}&nbsp;&raquo;'></td>
			</tr>
		</table>")."
	<br>	
	
	
	$html";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($page);
	
}

function whitelistdom($domain=null){

	$ldap=new clladp();
	if($domain<>null){$domain="*";}
	$hash=$ldap->WhitelistsFromDomain($domain);
	
	
	
	$html="<H5>$domain</H5>
	<table style='width:99%' class=table_form>
	<tr style='background-color:#CCCCCC'>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><strong>{recipients}</strong></td>
		<td><strong>{from}</strong></td>
		<td>&nbsp;</td>
	</tr>";
if(is_array($hash)){	
	while (list ($from, $line) = each ($hash)){
		$recipient_domain=$from;
		if(preg_match("#(.+?)@(.+)#",$recipient_domain,$re)){$recipient_domain=$re[2];}
		$ou=$ldap->ou_by_smtp_domain($recipient_domain);		
		while (list ($num, $wl) = each ($line)){
		$html=$html . 
			"<tr " . CellRollOver() . ">
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td><strong>$ou</strong></td>
				<td><strong>$from</strong></td>
				<td><strong>$wl</strong>
				<td width=1%>" . imgtootltip('x.gif','{delete}',"DeleteWhiteList('$from','$wl');")."</td>
			</tr>";}
		
	}}
	
$html=$html . "</table>";
$form=$html;


return $form;

}

function SaveWhiteList(){
	$tpl=new templates();
	$to=$_GET["recipient"];
	$wbl=$_GET["wbl"];
	$RcptDomain=$_GET["RcptDomain"];
	
	$from=$_GET["whitelist"];
	if($to==null){
		$to="*@$RcptDomain";
	}
	
if($from==null){
		echo $tpl->_ENGINE_parse_body('{from}: {error_miss_datas}');return false;
	}	
	
	
	if(substr($to,0,1)=='@'){
		$domain=substr($to,1,strlen($to));
	}else{
		if(strpos($to,'@')>0){
			$tbl=explode('@',$to);
			$domain=$tbl[1];
		}else{
			$domain=$to;
			$to="@$to";
		}
	}
	
	$tbl[0]=str_replace("*","",$tbl[0]);
	$ldap=new clladp();
	$domains=$ldap->hash_get_all_domains();
	if($domains[$domain]==null){
		echo $tpl->_ENGINE_parse_body('{recipient}: {error_unknown_domain} '.$domain);return false;
	}
	
	if($tbl[0]==null){
		$ldap->WhiteListsAddDomain($domain,$from,$wbl);
		return true;
	}else{
		$uid=$ldap->uid_from_email($to);
		if($uid==null){
			echo $tpl->_ENGINE_parse_body('{recipient}: {error_no_user_exists} '.$to);return false;
		}
		$ldap->WhiteListsAddUser($uid,$from,$wbl);
	}
}


function del_whitelist(){
	$ldap=new clladp();
	$to=$_GET["recipient"];
	$from=$_GET["del_whitelist"];
	$ldap->WhiteListsDelete($to,$from,$_GET["wbl"]);
	}




function blacklist(){
	$ldap=new clladp();
	
	$domain=$ldap->hash_get_all_domains();
	if(!is_array($domain)){return null;}
	
	while (list ($num, $line) = each ($domain)){
		$html=$html . blacklistdom($num);
		
		
	}
	$page=main_tabs () . "<br>" . RoundedLightGreen("
		<table style='width:99%'>
			<tr>
			<td><strong>{from}:</td>
			<td>" . Field_text('wlfrom',$_GET["whitelist"],'width:120px',
			null,null,null,false,"AddblwformCheck(1,event)") ."</td>
			<td><strong>{recipient}:</td>
			<td>" . Field_text('wlto',$_GET["recipient"],'width:120px',
			null,null,null,false,"AddblwformCheck(1,event)") ."</td>
			<td align='right'><input type='button' OnClick=\"javascript:Addblwform(1);\" value='{add}&nbsp;&raquo;'></td>
			</tr>
		</table>")."
	<br>	
	
	
	$html";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($page);
	
}

function blacklistdom($domain=null){
	if($domain==null){$domain="*";}
	$ldap=new clladp();
	$hash=$ldap->BlackListFromDomain($domain);	
	
	
	$html="<H5>$domain</H5>
	<table style='width:99%' class=table_form>
	<tr style='background-color:#CCCCCC'>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><strong>{recipients}</strong></td>
		<td><strong>{from}</strong></td>
		<td>&nbsp;</td>
	</tr>";
if(is_array($hash)){	
	while (list ($from, $line) = each ($hash)){
		$recipient_domain=$from;
		if(preg_match("#(.+?)@(.+)#",$from,$re)){$recipient_domain=$re[2];}
		$ou=$ldap->ou_by_smtp_domain($recipient_domain);
		while (list ($num, $wl) = each ($line)){
			
			
			
		$html=$html . 
			"<tr " . CellRollOver() . ">
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td><strong>$ou</strong></td>
				<td><strong>$from</strong></td>
				<td><strong>$wl</strong>
				<td width=1%>" . imgtootltip('x.gif','{delete}',"DeleteBlackList('$from','$wl');")."</td>
			</tr>";}
		
	}}
	
$html=$html . "</table>";

$form=$html;


return $form;

}



class autolearning_spam{
	var $WBLReplicEachMin="6h";
	var $WBLReplicEnable=0;
	var $WBLReplicaHamEnable=0;
	var $WBLReplicSchedule=array();
	var $array_days=array();
	
	
	
	function autolearning_spam(){
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$this->WBLReplicEachMin=$sock->GET_INFO('WBLReplicEachMin');
		$this->WBLReplicEnable=$sock->GET_INFO('WBLReplicEnable');
		$this->WBLReplicaHamEnable=$sock->GET_INFO('WBLReplicaHamEnable');
		$ini->loadString($sock->GET_INFO('WBLReplicSchedule'));
		$this->WBLReplicSchedule=$ini->_params;
		$this->array_days=array("sunday","monday","tuesday","wednesday","thursday","friday","saturday");
		$this->BuildDefault();
		
		
	}
	
	function BuildDefault(){
		if($this->WBLReplicEachMin==null){$this->WBLReplicEachMin="6h";}
		if($this->WBLReplicEnable==null){$this->WBLReplicEnable=0;}
		if($this->WBLReplicaHamEnable==null){$this->WBLReplicaHamEnable=0;}
		
		while (list ($num, $line) = each ($this->array_days)){
			if($this->WBLReplicSchedule["DAYS"][$line]==null){$this->WBLReplicSchedule["DAYS"][$line]=1;}
			}
		if($this->WBLReplicSchedule["TIME"]["time"]==null){
			$this->WBLReplicSchedule["CRON"]["time"]="3:0";
			$this->WBLReplicSchedule["TIME"]["time"]="3:0";
		}
		reset($this->array_days);
	}
	
	
	function Save(){
		$days=null;
		$sock=new sockets();
		$sock->SET_INFO('WBLReplicEachMin',$this->WBLReplicEachMin);
		$sock->SET_INFO('WBLReplicEnable',$this->WBLReplicEnable);
		$sock->SET_INFO('WBLReplicaHamEnable',$this->WBLReplicaHamEnable);
		
		while (list ($num, $line) = each ($this->array_days)){
			if($this->WBLReplicSchedule["DAYS"][$line]==1){$days[]=$num;}
		}
		if(is_array($days)){
			
			$this->WBLReplicSchedule["CRON"]["days"]=implode(',',$days);
		}else{
			$this->WBLReplicSchedule["CRON"]["days"]=null;
		}
		$this->WBLReplicSchedule["CRON"]["time"]=$this->WBLReplicSchedule["TIME"]["time"];
		
		$ini=new Bs_IniHandler();
		$ini->_params=$this->WBLReplicSchedule;
		$sock->SaveConfigFile($ini->toString(),'WBLReplicSchedule');
		$sock->getfile("delcron:artica-autolearn");
		if($this->WBLReplicSchedule["CRON"]["days"]<>null){
			if(preg_match('#(.+?):(.+)#',$this->WBLReplicSchedule["CRON"]["time"],$re)){
				$sock->getfile("addcron:{$re[2]} {$re[1]} * * {$this->WBLReplicSchedule["CRON"]["days"]} root /usr/share/artica-postfix/bin/artica-learn >/dev/null 2>&1;artica-autolearn");
			}
		}
		
	}
	
}


?>