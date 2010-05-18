<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.dansguardian.inc');
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		exit;
		
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["SelectDansGuardianExceptionipList"])){echo FindComputerByIP();exit;}
	if(isset($_GET["AddDansGuardianExceptionipList"])){AddDansGuardianExceptionipList();exit;}
	if(isset($_GET["ExceptionipListRefresh"])){echo ComputersList();exit;}
	if(isset($_GET["DelDansGuardianExceptionipList"])){DelDansGuardianExceptionipList();exit;}
	js();
	
	
function js(){
	
$page=CurrentPageName();
$tpl=new templates();

$prefix=str_replace(".","_",$page);
$title=$tpl->_ENGINE_parse_body("{white_ip_group}");
$html="
var {$prefix}timerID  = null;
var {$prefix}timerID1  = null;
var {$prefix}tant=0;
var {$prefix}reste=0;
var m_gpid;
var m_ou;

function {$prefix}load(){
	YahooWin2('650','$page?popup=yes','$title');
}

function {$prefix}StartPage(){
	if(!document.getElementById('squid_main_config')){
		setTimeout(\"{$prefix}StartPage()\",500);
	}
	LoadAjax('squid_main_config','$page?main=yes');
	setTimeout(\"{$prefix}demarre()\",500);
	setTimeout(\"{$prefix}ChargeLogs()\",500)
}

	function {$prefix}demarre(){
		{$prefix}tant = {$prefix}tant+1;
		{$prefix}reste=10-{$prefix}tant;
			if ({$prefix}tant < 10 ) {                           
		{$prefix}timerID = setTimeout(\"demarre()\",5000);
		      } else {
		{$prefix}tant = 0;
		              
		{$prefix}ChargeLogs();
		{$prefix}demarre();                                //la boucle demarre !
		   }
	}


function {$prefix}ChargeLogs(){
	var status='status';
	
	if(document.getElementById('statusid')){
		status=document.getElementById('statusid').value;
	}
	LoadAjax('services_status_squid','squid.index.php?status='+status+'&hostname={$_GET["hostname"]}&apply-settings=no');
	}
	
		
	function AddCache(folder){
		document.getElementById('cache_graph').innerHTML='';
		YahooWin(500,'$page?add-cache=yes&cache='+folder);
		
	}
	
var x_DeleteCache= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)};
    cachelist();  
	}	
	
function cachelist(){
	LoadAjax('cache_list','$page?cache-list=yes');   
}
	
function DeleteCache(folder){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteCache',folder);
		document.getElementById('cache_list').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_DeleteCache);
}

function ConnectionTime(){
	YahooWin(500,'$page?connection-time=yes');
}

function ConnectionTimeSelectOU(){
	var ou=document.getElementById('ou').value;
	LoadAjax('group_field','$page?connection-time-showgroup='+ou);
	}
	
function ConnectionTimeSelectGroup(){
	var ou=document.getElementById('ou').value;
	var gpid=document.getElementById('gpid').value;
	LoadAjax('ConnectionTimeRule','$page?connection-time-rule=yes&ou='+ou+'&gpid='+gpid);
	
}

function ConnecTimeRefreshlist(gpid,ou){
LoadAjax('rule_list','$page?time-rule-list=yes&gpid='+gpid+'&ou='+ou);   
}

var x_SelectDansGuardianExceptionipList= function (obj) {
	document.getElementById('popup_selected_computers').innerHTML=obj.responseText;
	}
	
var x_ExceptionipList_Refresh_list= function (obj) {
	document.getElementById('popup_saved_computers').innerHTML=obj.responseText;
	}	
	
function ExceptionipList_Refresh_list(){
	var XHR = new XHRConnection();
	document.getElementById('popup_saved_computers').innerHTML='<center><img src=\"img/wait.gif\"></center>'
	XHR.appendData('ExceptionipListRefresh','yes');
	XHR.sendAndLoad('$page', 'GET',x_ExceptionipList_Refresh_list);
}
	
var x_AddDansGuardianExceptionipList= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		ExceptionipList_Refresh_list();
	}

	function SelectDansGuardianExceptionipListButton(){	
		AddDansGuardianExceptionipList(document.getElementById('IpWhite').value);
	}


function SelectDansGuardianExceptionipList(e){

	if(checkEnter(e)){
		AddDansGuardianExceptionipList(document.getElementById('IpWhite').value);
		return;
	}

	var XHR = new XHRConnection();
	document.getElementById('popup_selected_computers').innerHTML='<center><img src=\"img/wait.gif\"></center>'
	XHR.appendData('SelectDansGuardianExceptionipList',document.getElementById('IpWhite').value);
	XHR.sendAndLoad('$page', 'GET',x_SelectDansGuardianExceptionipList);
}

function AddDansGuardianExceptionipList(ip){
var XHR = new XHRConnection();
	document.getElementById('popup_saved_computers').innerHTML='<center><img src=\"img/wait.gif\"></center>'
	XHR.appendData('AddDansGuardianExceptionipList',ip);
	XHR.sendAndLoad('$page', 'GET',x_AddDansGuardianExceptionipList);

}

function DelDansGuardianExceptionipList(ip){
var XHR = new XHRConnection();
	document.getElementById('popup_saved_computers').innerHTML='<center><img src=\"img/wait.gif\"></center>'
	XHR.appendData('DelDansGuardianExceptionipList',ip);
	XHR.sendAndLoad('$page', 'GET',x_AddDansGuardianExceptionipList);
}



{$prefix}load();";
echo $html;
	
}	

function popup(){
$tpl=new templates();	
$html="<H1>{white_ip_group}</H1>
<p class=caption>{white_ip_group_text}</p>



<table style='width:100%'>
<tr>
	<td width=99%>
		<center>". Field_text("IpWhite",null,"padding:3px;margin:3px;font-size:13px",null,null,null,false,"SelectDansGuardianExceptionipList(event)")."</center>
	</td>
		<td width=1%>". imgtootltip("plus-24.png","{add}","SelectDansGuardianExceptionipListButton()")."
	</td>
</tr>
<tr>
<td colspan=2>
		<table style='width:100%'>
		<tr>
		<td valign='top' style='border:1px solid #CCCCCC'><div id='popup_saved_computers'>". ComputersList()."</div></td>
		<td valign='top' style='border:1px solid #CCCCCC'><div id='popup_selected_computers'>". FindComputerByIP()."</div></td>
		</tr>
		</table>
</td>
</tr>
</table>


";	
	
echo $tpl->_ENGINE_parse_body($html);
	
}

function FindComputerByIP(){
	
	if($_GET["SelectDansGuardianExceptionipList"]=='*'){$_GET["SelectDansGuardianExceptionipList"]=null;}
	if($_GET["SelectDansGuardianExceptionipList"]==null){$tofind="*";}else{$tofind="{$_GET["SelectDansGuardianExceptionipList"]}*";}
	$filter_search="(&(objectClass=ArticaComputerInfos)(|(cn=$tofind)(ComputerIP=$tofind)(uid=$tofind))(gecos=computer))";
	
	writelogs($filter_search,__FUNCTION__,__FILE__,__LINE__);
	$ldap=new clladp();
	$attrs=array("uid","ComputerIP","ComputerOS","ComputerMachineType");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter_search,$attrs,10);
	$html="<table style='width:250px' class=table_form>";

for($i=0;$i<$hash["count"];$i++){
	$realuid=$hash[$i]["uid"][0];
	$hash[$i]["uid"][0]=str_replace('$','',$hash[$i]["uid"][0]);
	$ip=$hash[$i][strtolower("ComputerIP")][0];
	if(trim($ip)==null){continue;}
	
	$js="AddDansGuardianExceptionipList('$ip');";
	
	$html=$html . 
	"<tr ". CellRollOver($js,"{add}").">
		<td width=1%><img src='img/base.gif'></td>
		<td nowrap><strong>{$hash[$i]["uid"][0]}</strong></td>
		<td ><strong>$ip</strong></td>
	</tr>
	";
	}
$html=$html . "</table>";

$html="<center><div style='height:300px;overflow:auto'>$html</div></center>";

$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);	
}

function ComputersList(){
$q=new mysql();
	$sql="SELECT ID,pattern FROM dansguardian_files WHERE filename='exceptioniplist' AND RuleID=1 ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	$style=CellRollOver();
	$html="
	<table style='width:250px' class=table_form>";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$html=$html."<tr $style>
	<td width=1% ><img src='img/base.gif'></td>
	<td  nowrap><strong>{$ligne["pattern"]}</strong></td>
	<td ><strong>". imgtootltip("ed_delete.gif","{delete}","DelDansGuardianExceptionipList('{$ligne["ID"]}');")."</strong></td>
	</tr>
	";
	}
	$html=$html . "</table>";	
	$html="<center><div style='height:300px;overflow:auto'>$html</div></center>";		
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);		
	
	
}

function AddDansGuardianExceptionipList(){
	$dans=new dansguardian_rules(null,1);
	if(!$dans->Add_exceptioniplist($_GET["AddDansGuardianExceptionipList"],$_GET["AddDansGuardianExceptionipList"],1)){
		echo $dans->error;
	}
	
}

function DelDansGuardianExceptionipList(){
	$dans=new dansguardian_rules(null,1);
	if(!$dans->Del_exceptioniplist(1,$_GET["DelDansGuardianExceptionipList"])){
		echo $dans->error;
	}	
}

	
?>