<?php
include_once('ressources/class.templates.inc');
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.cyrus.inc');
include_once('ressources/class.main_cf.inc');
include_once('ressources/charts.php');
include_once('ressources/class.mysql.inc');
include_once('ressources/class.status.inc');
session_start();
$ldap=new clladp();
if(isset($_GET["loadhelp"])){loadhelp();exit;}


if(!isset($_SESSION["uid"])){
	writelogs("uid=" . $_SESSION["uid"] . " come back to logon",__FUNCTION__,__FILE__);
	header('location:logon.php');
	exit;
	}




    if(isset($_GET["GeoipCity"])){GeoipCity();exit;}
	if(isset($_GET["PostfixStatus"])){echo Artica_infos();exit;}
	if(isset($_GET["today"])){echo GetToday();exit;}
	if(isset($_GET["lastinfos"])){echo GetLastInfos();;exit;}
	if(isset($_GET["allstatus"])){echo PAGE_REFRESH_All_Status();exit;}
	if(isset($_GET["StartPostfix"])){echo StartPostfix();exit;}
    if(isset($_GET["PostfixHistoryMsgID"])){echo PostfixHistoryMsgID();exit;}
    if(isset($_GET["postfix-status"])){echo postfix_status();exit;}

$users=new usersMenus();
writelogs("uid=" . $_SESSION["uid"],__FUNCTION__,__FILE__);
writelogs("AsOrgAdmin=$users->AsOrgAdmin",__FUNCTION__,__FILE__);
writelogs("AsArticaAdministrator=$users->AsArticaAdministrator",__FUNCTION__,__FILE__,__LINE__);
writelogs("AsOrgPostfixAdministrator=$users->AsOrgPostfixAdministrator",__FUNCTION__,__FILE__,__LINE__);


if($_SESSION["uid"]==-100){header('location:admin.index.php');exit;}
if($users->AsArticaAdministrator==true or $users->AsPostfixAdministrator or $user->AsSquidAdministrator){header('location:admin.index.php');exit;}
if($users->AsOrgAdmin==true && $users->AsArticaAdministrator==true){header('location:admin.index.php');exit;}

if(isset($_GET["graph1"])){echo USER_MAILBOX_GRAPH();exit;}
if(isset($_GET["graph2"])){echo USER_MAILS_STATS();exit;}
if(isset($_GET["USER_MAIL_STATS"])){echo USER_MAILS_STATS_GEN();exit;}
if(isset($_GET["graph-js"])){graph_js();exit;}
if(isset($_GET["graph-popup"])){graph_popup();exit;}


if($users->AsOrgPostfixAdministrator){
		OVERVIEW_POSTFIX();
		exit;
}
OVERVIEWUSER();



function OVERVIEW_POSTFIX(){
	$page=CurrentPageName();
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'><div id='left-section'></div></td>
		<td valign='top'><div id='right-section'></div></td>
	</tr>
	</table>
	<div id='bottom-section'></div>
	
	
	<script>
		var timerID  = null;
		var timerID1  = null;
		var tant=0;
		var reste=0;
		
		
		function INDEX_USER_demarre(){
			   tant = tant+1;
			   reste=10-tant;
				if (tant < 20 ) {                           
			      timerID = setTimeout(\"INDEX_USER_demarre()\",2000);
			      } else {
			               tant = 0;
			               INDEX_USER_ChargeLogs();
			              INDEX_USER_demarre();
			   }
		}




function INDEX_USER_ChargeLogs(){
	LoadAjax('right-section','$page?postfix-status=yes');
	}	
	INDEX_USER_ChargeLogs();
	INDEX_USER_demarre();
	</script>
	";
	
	$status=new status();

$tpl=new template_users("{welcome} {$_SESSION["uid"]}",$html,$_SESSION);

echo $tpl->web_page;	
		
}

function postfix_status(){
	$status=new status();
	$postfix=$status->Postfix_multi_status($_SESSION["ou"]);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($postfix);
	
}	


function OVERVIEWUSER(){
	
	
	
$ou=$_SESSION["ou"];
if($_SESSION["UsersInterfaceDatas"]<>null){
	$PersoChargeLogs="PersoChargeLogs()";	
	$frontend="<div id=emule-page></div>";
}else{
	$frontend="<table style='width:100%'>
				<tr>
				<td valign='top'>
					<div id='menu_subleft'></div>
				</td>
				<td valign='top' style='width=1%'>
					<div id='menu_right' style='padding-left:40px'></div>
					<div id='menu_subright' style='padding-left:40px'></div>
				</td>
				</table>";
	$PersoChargeLogs="ChargeLogs()";
	$PersoChargeLogsCycle="ChargeLogs();";
}


$html=
	"
<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function whatsnew(){
if(document.getElementById('leftpanel_content')){
	LoadAjax('leftpanel_content','users.whatsnew.php');
}

}

function demarre(){
   tant = tant+1;
   reste=10-tant;
	if (tant < 20 ) {                           
      timerID = setTimeout(\"demarre()\",5000);
      } else {
               tant = 0;
               //document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
               $PersoChargeLogsCycle;
               demarre();                                //la boucle demarre !
   }
}

function PersoChargeLogs(){
	Loadjs('users.tabs.php?tab-js=index&uid=__SESSION__')
}


function ChargeLogs(){
	LoadAjax('menu_right','menus.builder.php?fromnum=0&tonum=4');
	LoadAjax('menu_subleft','menus.builder.php?fromnum=4&tonum=8');
	LoadAjax('menu_subright','menus.builder.php?fromnum=8&tonum=18');
	whatsnew();
	}

</script>
<H3>{your_organization}: $ou</H3>
$frontend
<script>demarre();$PersoChargeLogs;</script>
<script>setTimeout(\"whatsnew()\",3000);</script>

";



	
$tpl=new template_users("{welcome} {$_SESSION["uid"]}",$html,$_SESSION);

echo $tpl->web_page;		
}

function graph_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{statistics}');
	$html="
	  YahooWin(780,'$page?graph-popup=yes','$title');
	  setTimeout(\"startGraphUser()\",1500);
	  function startGraphUser(){
	  		LoadAjax('graph1','users.charts.php?GraphMailbox=yes');
			LoadAjax('graph2','users.charts.php?flow=yes');
	  }
	  
	
	";
	
	echo $html;
}

function graph_popup(){
	$html="
	<H1>{statistics}</H1>
	<table style='width:100%'>
<tr>
<td valign='top'>
	<div id='graph1' style='border:1px solid #CCCCCC;margin:2px'></div>
</td>
<td>
	<div id='graph2' style='border:1px solid #CCCCCC;margin:2px'></div>
</td>
</tr>	
</table>
";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}


function OVERVIEWOU(){
$ldap=new clladp();
$hash=$ldap->UserDatas($_SESSION["uid"]);
$ou=$hash["ou"];
$mysql=new MySqlQueries();
$html="
	<table style='width:100%;border:1px solid #CCCCCC'>
	<tr>
	<td width=1% valign='top'>
		<img src='img/bg_welcome.jpg'><br>
	</td>
	<td valign='top'>
		" . BuildManagementTable($ou) ."
		
		
		</td>
	</tr>
	</table>
		<H3 style='margin-top:5px;margin-bottom:5px'>{overview} [$ou]</H3>
<div id='today'>
		<div id='lastinfos'></div>
		<script>LoadAjax('today','users.index.php?today=yes');</script>	
		<script>LoadAjax('lastinfos','users.index.php?lastinfos=yes');</script>
	";
	
$tpl=new template_users("Welcome {$hash["displayName"]}",$html,$_SESSION);

echo $tpl->web_page;	
}




function StartPostfix(){
	$users=new usersMenus();
	if($users->AsPostfixAdministrator==true){
	$sock=new sockets();
	$sock->getfile('StartPostfix');
	echo Artica_infos();
	}
}

function BuildManagementTable($ou){
	$usersmenus=new usersMenus();
	//status_statistics.jpg
	$stylehref="style='font-size:12px;text-decoration:underline'";
	if($usersmenus->AllowChangeKas==true){$html=$html . "<tr><td valign='top' style='width:1%'><img src='img/rule-16.jpg'></td><td><a href='kas.user.rules.php?ou=$ou' $stylehref>{manage_groups_antispam}</a></td></tr>";}
	if($usersmenus->AllowChangeKav==true){$html=$html . "<tr><td valign='top' style='width:1%'><img src='img/rule-16.jpg'></td><td><a href='aveserver.settings.php?ou=$ou' $stylehref>{antivirus_engine}</a></td></tr>";}
	if($usersmenus->AllowViewStatistics==true or $usersmenus->AllowEditOuSecurity==true ){$html=$html . "<tr><td valign='top' style='width:1%'><img src='img/status_statistics.jpg'></td><td><a href='statistics.ou.php?ou=$ou' $stylehref>{statistics}</a></td></tr>";}
	if($usersmenus->AllowEditOuSecurity==true){
		$html=$html . "<tr><td valign='top' style='width:1%'><img src='img/rule-16.jpg'></td><td><a href='global-blacklist.ou.php?ou=$ou' $stylehref>{global_blacklist}</a></td></tr>";
		$html=$html . "<tr><td valign='top' style='width:1%'><img src='img/rule-16.jpg'></td><td><a href='global-filters.ou.php?ou=$ou' $stylehref>{artica_filters_rules}</a></td></tr>";
	
	}
	
	
	
	$table="<center style='margin:4px'>
		<table style='width:95%:3px;border:1px solid #005447'>
		<tr style='background-color:#005447'><td colspan=2 style='color:white;padding:5px'><strong>{manage_your_org}</strong></td></tr>
			$html
		</table>
		</center>";
	return $table;
	
}

function GetToday() {
$users=new usersMenus();
if($users->mysql_enabled==false){echo "<span></span>";}
if($_SESSION["uid"]==-100){echo Today(null,1);}

$ldap=new clladp();
$hash=$ldap->UserDatas($_SESSION["uid"]);

if($users->AsOrgAdmin==true && $users->AsArticaAdministrator==false){echo Today($hash["ou"]);exit;}
if($users->IfIsAnuser()==true){echo Today($hash["ou"],$hash["mail"]);exit;}	
	
	
}

function GetLastInfos(){
	writelogs("UID={$_SESSION["uid"]}",__FUNCTION__,__FILE__);
	$users=new usersMenus();	
	if($users->mysql_enabled==false){echo "<span></span>";exit;}
	
	$ldap=new clladp();
	$mysql=new MySqlQueries();	
	$hash=$ldap->UserDatas($_SESSION["uid"]);

	if($_SESSION["uid"]==-100){$bottom=AdminOverview();}
	else{
		if($users->AsOrgAdmin==true && $users->AsArticaAdministrator==false){	$bottom=AdminOverview();}
		if($users->IfIsAnuser()==true){
			writelogs("UID={$_SESSION["uid"]} -> it is an user",__FUNCTION__,__FILE__);
			$bottom=$mysql->LastReceiveMails(null,$hash["mail"]);}
	}

	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($bottom);
}

function Today($ou=null,$tr=0,$user=null){
	
	$users=new usersMenus();
	if($users->mysql_enabled==false){echo "<span></span>";exit;}
	$mysql=new MySqlQueries();
	
	$graph=InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?LinesMessagesDay=$ou",200,180,"",true,$users->ChartLicence);	
	
	
if($tr<>1){
	
	$html="<H4>{today}</H4>
		<table style='width:100%;' cellspacing=0>
		   <tr>
			<td align='right'nowrap><strong>{sended}:</strong></td>
			<td align='left' style=''><strong>{$mysql->today_OU_Total_filtered($ou,'send',$user)}</strong></td>		
			<td align='right' nowrap><strong>{detected_spam}:</strong></td>
			<td align='left'><strong>{$mysql->today_OU_Total_filtered($ou,'spam',$user)}</strong></td>
			<td align='right' nowrap><strong>{APP_BOGOFILTER}:</strong></td>
			<td align='left'><strong>{$mysql->today_OU_Total_filtered($ou,'bogo',$user)}</strong></td>
			<td align='right' nowrap><strong>{infected_spam}:</strong></td>
			<td align='left'><strong>{$mysql->today_OU_Total_filtered($ou,'infected',$user)}</strong></td>
			<td align='right' nowrap><strong>{filtered_spam}:</strong></td>
			<td align='left'><strong>{$mysql->today_OU_Total_filtered($ou,'filtered',$user)}</strong></td>
			<td align='right'><strong>{faked_spam}:</strong></td>
			<td align='left'><strong>{$mysql->today_OU_Total_filtered($ou,'faked',$user)}</strong></td>
			<td align='right'><strong>{black_spam}:</strong></td>
			<td align='left'><strong>{$mysql->today_OU_Total_filtered($ou,'black',$user)}</strong></td>										
			</tr>		
		<tr style=''>
		<td colspan=12 style='padding-top:5px;padding-bottom:5px' align='right'><strong>{emails_recieved}</strong></td>
		<td align='right'  style='padding-top:5px;padding-bottom:5px'><strong>{$mysql->today_OU_Total_mails($ou,$user)}</strong></td>
		</tr>
		</table>
";
}else{
	
$html="<H4>{today}</H4>

		<table style='width:100%;padding:0px' cellspacing=0>
		<tr>
		<td><p style='border:1px solid #CCCCCC'>$graph</p></td>
		<td valign='top'>
		<table style='width:100%;padding:0px' cellspacing=0>
			<tr>
			<td align='right'  nowrap><strong>{sended}:</strong></td>
			<td align='left' ><strong>{$mysql->today_OU_Total_filtered($ou,'send')}</strong></td>
			</tr>
			<tr>		
				<td align='right' nowrap><strong>{detected_spam}:</strong></td>
				<td align='left'><strong>{$mysql->today_OU_Total_filtered($ou,'spam')}</strong></td>
			<tr>
			<tr>		
				<td align='right' nowrap><strong>{APP_BOGOFILTER}:</strong></td>
				<td align='left'><strong>{$mysql->today_OU_Total_filtered($ou,'bogo')}</strong></td>
			<tr>			
			</tr>
				<td align='right' nowrap><strong>{infected_spam}:</strong></td>
				<td align='left'><strong>{$mysql->today_OU_Total_filtered($ou,'infected')}</strong></td>
			</tr>
			<tr>
				<td align='right' nowrap><strong>{filtered_spam}:</strong></td>
				<td align='left'><strong>{$mysql->today_OU_Total_filtered($ou,'filtered')}</strong></td>
			</tr>
			<tr>
				<td align='right'><strong>{faked_spam}:</strong></td>
				<td align='left'><strong>{$mysql->today_OU_Total_filtered($ou,'faked')}</strong></td>
			</tr>
			<tr>
				<td align='right'><strong>{black_spam}:</strong></td>
				<td align='left'><strong>{$mysql->today_OU_Total_filtered($ou,'black')}</strong></td>										
			</tr>		
			<tr style=''>
				<td style='padding-top:5px;padding-bottom:5px' align='right' nowrap><strong>{emails_recieved}</strong></td>
				<td align='right'  style='padding-top:5px;padding-bottom:5px'><strong>{$mysql->today_OU_Total_mails($ou)}</strong></td>
			</tr>
			<tr>
				<td>&nbsp;</td><td align='right'>" . imgtootltip('icon_refresh-20.gif','{refresh}',"LoadAjax('today','users.index.php?today=yes');")."</td>
			</tr>
		</table>
		
		<center>" . imgtootltip('charts-plus.png','{more_stats}',"MyHref('system_statistics.php')")."</center>
		</td>
		</tr>
		</table>
		";
	
}
$tpl=new templates()	;
return $tpl->_ENGINE_parse_body(RoundedGrey($html));
	
}





function INDEX(){

if(isset($_GET["tab"])){$_GET["tab"]=0;}
$mailbox=accountMailboxes();
$ldap=new clladp();
$hash=$ldap->UserDatas($_SESSION["uid"]);
$hash["displayName"]="Administrator";
$services_infos=GetAllSTatus();
//$Graph=InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?allSpamInfos",250,250,"FFFFFF",true,$usermenus->ChartLicence);

$html="
<table style='width:100%'>
<tr>
	<td width=1% valign='top'>
		<img src='img/bg_welcome.jpg'><br>
		<div id='today'></div>
	</td>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
		<td valign='top' width=50%>$services_infos</td>
		</tr>
		<tr>
		<td valign='top' width=50%><div id='servinfos'></div></td>
		</tr>
		</table>
	</td>
</tr>
</table>
<div id='lastinfos'></div>
<script>LoadAjax('servinfos','users.index.php?PostfixStatus=yes');</script>
<script>LoadAjax('today','users.index.php?today=yes');</script>
<script>LoadAjax('lastinfos','users.index.php?lastinfos=yes&tab={$_GET["tab"]}');</script>


";

$tpl=new template_users("Welcome {$hash["displayName"]}",$html,$_SESSION);

echo $tpl->web_page;
}



function PAGE_REFRESH_All_Status(){
	include_once('ressources/class.status.inc');
	$users=new usersMenus();
	$tpl=new templates();
	$status=new status(1);
	$tpl=new templates();
	if($users->AsArticaAdministrator==false){exit;}
	
	
	
	$status=RoundedLightGreen($tpl->_ENGINE_parse_body($status->AllStatus()));
	echo iframe($status);
	
	
	//PostfixErrorsLogs
}


function GetAllSTatus(){
	return "<iframe src='users.index.php?allstatus=true' style='border:0px solid #CCCCCC;padding:0px;margin:0px;width:100%;height:230px'></iframe>";
	
	
}
function Artica_infos(){
	
	$ldap=new clladp();
	$domains=$ldap->hash_get_ou();
	if(count($domains)==0){
		$status=Paragraphe('folder-update.jpg','{create_org_postfix}','{create_org_postfix_text}',"artica.wizard.org.php");
	}else{
	
	$status=new status(1);
	$status=$status->Postfix_satus();
	}
	$tpl=new templates();
	return "<br>" .RoundedLightGreen($tpl->_ENGINE_parse_body("<div id='status'>$status</div>"));	
	
}
function userInfos(){
	include_once('ressources/class.mysql.inc');
	
	
	$text="<table>
	<tr>
		<td align='right'><strong>{quarantine_number}:</strong>
		<td>" . QuarantineNumber()  . "</td>
	</tr>
	</table>";
	
	$html="
	<table>
	<tr>
	<td valign='top'> . ".Paragraphe('folder-storage-64.jpg','{manage_your_quarantine}',$text,'user.quarantine.php') ."</td>
	</tr>
	<tr><td valign='top'> . ".Paragraphe('folder-rules-64.jpg','{manage_your_mailbox_rules}','{manage_your_mailbox_rules_text}','user.sieve.index.php') ."</td></tr>";
	$html=$html . "<tr><td valign='top'>  ".Paragraphe('folder-queuegraph-64.jpg','{messages_performance}','{messages_performance_text}',"user.messages.statistics.php") ."</td></tr>";			
	$html=$html  . "</table>";
$tpl=new templates();
	return $tpl->_ENGINE_parse_body("<div id='status'>$html</div>");		
}

function loadhelp(){
	$field=$_GET["loadhelp"];
	$title=$_GET["title"];
	$curpage=$_GET["currpage"];
	if(strpos($field,'}')==0){
		$field="{{$field}}";
	}

	$text="
<div  style='font-size:12px;padding:3px;margin:3px;border:1px sold #CCCCCC;background-color:#FFFFFF'>" . $field . "</div>
	</div>";
	


	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($text,$curpage);
	
}

function QuarantineNumber(){
	$ldap=new clladp();
	$hash=$ldap->UserDatas($_SESSION["uid"]);
	$mail=$hash["mail"];
	if($mail==null){return null;}	
	$sql="SELECT COUNT(ID) as tcount FROM messages WHERE mail_to='$mail'  AND filter_action='quarantine' AND Deleted=0";
	$ligne=mysql_fetch_array(QUERY_SQL($sql));
	return $ligne["tcount"];
	
}

function PostfixHistoryMsgID(){
	
	$msg_id=$_GET["PostfixHistoryMsgID"];
	$sock=new sockets();
	$datas=$sock->getfile('mailloghistory:' . $msg_id);
	$datas=explode("\n",$datas);
	array_reverse($datas);
	if(!is_array($datas)){echo "&nbsp;";exit();}

	
	$p=new PostFixLogs();
	echo $p->ParsePostfixLogs($datas,count($datas),1);
}


function AdminOverview_tabs(){
	if(!isset($_GET["tab"])){$_GET["tab"]=0;}
	$array[0]="{mail_events}";
	$array[1]="{postfix_last_errors}";
	$array[2]="{countries_and_cities}";
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('lastinfos','users.index.php?lastinfos=yes&tab=$num');\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
	
	
}


function AdminOverview(){
	$sql=new MySqlQueries();
	writelogs("UID={$_SESSION["uid"]}",__FUNCTION__,__FILE__);
if($_SESSION["uid"]==-100){	
		writelogs('Administrator overview',__FUNCTION__,__FILE__);
		$html=AdminOverview_tabs() . "<br>";
		switch ($_GET["tab"]) {
			case 0:$html=$html . $sql->LastReceiveMails(null);
				break;
		
			case 1:
				writelogs('Administrator overview -> GetErrorLogs()',__FUNCTION__,__FILE__);
				$postlogs=new PostFixLogs();
				$html=$html . $postlogs->GetErrorLogs();
				break;
			
				
			case 2:$html=$html . OVERVIEW_GEOIP();	
				
			default:
				break;
		}
}
			
$tpl=new templates()	;
return $tpl->_ENGINE_parse_body($html);
	
}

function OVERVIEW_GEOIP(){
	
$users=new usersMenus();
$graph=InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?geoip1=admin",450,180,"",true,$users->ChartLicence);		
return "<H5>{todaysenderscountry}</H5><div style='width:auto;border:1px dotted #CCCCCC;padding:2px;'>
<table style='width:100%'><tr>
<td valign='top'>$graph</td>
<td valign='top'><div id='GraphDatas'></div></td>
</tr>
</table>";	
}
function GeoipCity(){
	header('Content-Type: text/html; charset=iso-8859-1');
	$sql=new MySqlQueries();
	$result=$sql->today_OU_GEOIP_CITY($_GET["datas"]);
	if(!$result){echo "&nbsp;{$_GET["datas"]}=Error!";return null;}
	$html="<table style='width:100%'>";
	while($ligne=sqlite3_fetch_array($result)){	
				$city=$ligne["GeoCity"];
				$count=$ligne["tcount"];
		$html=$html . "<tr>
		<td width=1%><img src='img/fw_bold.gif'>
		<td>$city</td>
		<td>$count</td>
		</tr>";
		

		
		
	}
	
	
	echo $html . "</table>";
	
	
}


function USER_MAILBOX_GRAPH(){
	$users=new usersMenus();
	$uid=$_SESSION["uid"];
	if($users->cyrus_imapd_installed==0){return null;}
	$ldap=new clladp();
	
	$hash=$ldap->UserDatas($_SESSION["uid"]);
	
   if($hash["MailboxActive"]=='TRUE'){
      		$cyrus=new cyrus();
      		$res=$cyrus->get_quota_array($uid);
      		$size=$cyrus->MailboxInfosSize($uid);
      		$free=$cyrus->USER_STORAGE_LIMIT -$cyrus->USER_STORAGE_USAGE;
			
			if($cyrus->MailBoxExists($uid)){
				$graph1=InsertChart('js/charts.swf',
				"js/charts_library","listener.graphs.php?USER_STORAGE_USAGE=$cyrus->USER_STORAGE_USAGE&STORAGE_LIMIT=$cyrus->USER_STORAGE_LIMIT&FREE=$free",
				300,300,"",true,$users->ChartLicence);	
				$t=md5('Y-md- HIs');
				//$graph1=open_flash_chart_object_str(300,300,
				//"users.charts.php?USER_STORAGE_USAGE=$cyrus->USER_STORAGE_USAGE&STORAGE_LIMIT=$cyrus->USER_STORAGE_LIMIT&FREE=$free&t=$t",true,'');
				
				
				
			}
    
   }
   $tpl=new templates();
	return $tpl->_ENGINE_parse_body("<div style='border:1px dotted #CCCCCC;text-align:center;padding:5px'><H2>{mailboxsize}</H2>$graph1</div>");
}

function USER_MAILS_STATS(){
	$users=new usersMenus();
	$page=CurrentPageName();
	$users->LoadModulesEnabled();
	$uid=$_SESSION["uid"];
	if(!$users->MIMEDEFANG_INSTALLED){return null;}
	if($users->MimeDefangEnabled<>1){return null;}
	
	
	$graph=InsertChart('js/charts.swf',"js/charts_library","$page?USER_MAIL_STATS=yes",400,180,"",true,$users->ChartLicence);	
  	 $tpl=new templates();
	return $tpl->_ENGINE_parse_body("<br><div style='border:1px dotted #CCCCCC;text-align:center'><H2>{receivemails}</H2>$graph</div>");	
	
}

function USER_MAILS_STATS_GEN(){
	
	$ldap=new clladp();
	$hash=$ldap->UserDatas($_SESSION["uid"]);	
$sql="SELECT COUNT(zDate) as tcount ,rcpt_to,
		DATE_FORMAT(zDate,'%Y-%m-%d') as tday ,
		DATE_FORMAT(zDate,'%H') as thour
		FROM mails_events
		
			group by DATE_FORMAT(zDate,'%Y-%m-%d'),
			DATE_FORMAT(zDate,'%H') 	
HAVING tday=DATE_FORMAT(NOW(),'%Y-%m-%d') AND rcpt_to='{$hash["mail"]}'
ORDER BY tday desc limit 0,24;";
		


$textes[]='title';
$donnees[]='nb mails/hour';
	$s=new mysql();
	$results=$s->QUERY_SQL($sql,"artica_events");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$textes[]=$ligne["thour"];
		$donnees[]=$ligne["tcount"];
		}	
	
	//$links=array("url"=>"javascript:MyHref('system_statistics.php?LinesMessagesHour=yes',_category_)","target"=>"javascript");
	include_once('listener.graphs.php');
	BuildGraphCourbe(array($textes,$donnees),$links);

	
}





?>