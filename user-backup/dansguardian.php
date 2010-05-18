<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.dansguardian.inc');

	
	if(isset($_GET["panel-left"])){LeftPanel();exit;}
	if(isset($_GET["panel-right"])){RightPanelTabs();exit;}
	if(isset($_GET["main-rules"])){main_switch();exit;}
	
	
	
$page=CurrentPageName();	

$html="
<table style='width:100%'>
	<tr>
		<td valign='top' width=250px>
		<div id='panel_rules'></div>
		</td>
		<td valign='top' width=100%>
		<div id='panel_icons'></div>
		</td>
	</tr>
</table>
<script>
	function RefreshPanelLeft(){
		LoadAjax('panel_rules','$page?panel-left=yes');
	}
	
	var X_RefreshRightPanel= function (obj) {
		var results=obj.responseText;
		document.getElementById('panel_icons').innerHTML=results;
		ActivateRightPanel();
	}
		
	function RefreshRightPanel(ID){
		var XHR = new XHRConnection();
		XHR.appendData('panel-right',ID);
		document.getElementById('panel_icons').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_RefreshRightPanel);
		}		

	function ActivateRightPanel(){
			
				$('#main_config_dansguardian_panel').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
					        });
					    }
					});
						
					$('#main_config_dansguardian_panel').tabs('option', 'fx', { opacity: 'toggle' });
				
	
	}
	
	
	
RefreshPanelLeft();
RefreshRightPanel(0);
</script>

";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);




function LoadRules(){
	$user=new user($_SESSION["uid"]);
	if(isset($_SESSION["DANSGUARDIAN_RULES"])){return $_SESSION["DANSGUARDIAN_RULES"];}
	$groups=$user->Groups_list();
	while (list ($gpid, $groupname) = each ($groups)){
		$RuleID=RuleIDFromGpid($gpid);
		if(trim($RuleID)==null){continue;}
		$dans=new dansguardian_rules(null,$RuleID);
		$_SESSION["DANSGUARDIAN_RULES"][$RuleID]=$dans->Main_array["groupname"];
		
		
		
	}
	return $_SESSION["DANSGUARDIAN_RULES"];
	
}

function RuleIDFromGpid($gpid){
	$sql="SELECT RuleID FROM dansguardian_groups WHERE group_id='$gpid'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	return $ligne["RuleID"];
	}
	
function LeftPanel(){

	$rules=LoadRules();
	if(!is_array($rules)){return null;}
	while (list ($RuleID, $RuleName) = each ($rules)){
		$html=$html.iconTable("script-64.png",$RuleName,"{dansguardian_user_manage_rule}","RefreshRightPanel($RuleID)",null,205);
	}
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}


function RightPanelTabs(){
	$page=CurrentPageName();
	$tpl=new templates();
	
		if($_GET["panel-right"]==0){
			$rules=LoadRules();
			
			if(!is_array($rules)){return null;}
			reset($rules);
			$rules_z=array();
			while (list ($RuleID, $RuleName) = each ($rules)){
				if($RuleID==null){continue;}
				$rules_z[]=$ruleID;
				$_GET["panel-right"]=$RuleID;
				break;
				
			}
			
				
		}	
	
	
	$array["whitelist"]="{dansguardian_user_whitelists}";
	$array["blacklist"]="{dansguardian_user_blacklists}";
	$array["files-blacklist"]="{dansguardian_user_filesblacklists}";
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?main-rules=$num&RuleID={$_GET["panel-right"]}\"><span>$ligne</span></li>\n");
	}
	
	
	echo "
	<div id=main_config_dansguardian_panel style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>";	
	
}



function main_switch(){
	
	switch ($_GET["main-rules"]) {
		case "whitelist":panel_whitelists();exit;break;
		case "blacklist":panel_blacklists();exit;break;
		case "files-blacklist":panel_blockfiles();exit;
		default:panel_blacklists();break;
	}
}

function panel_blockfiles(){
$ID=$_GET["RuleID"];

$bannedextensionlist=iconTable("64-filetype.png","{bannedextensionlist}",'{bannedextensionlist_user_explain}',
"Loadjs('dansguardian.banned-extensions.php?rule_main=$ID')",
'{bannedextensionlist}',$psize);


$BannedMimetype=iconTable("64-mime.png","{BannedMimetype}",'{BannedMimetype_user_explain}',
"Loadjs('dansguardian.banned-mime.php?rule_main=$ID')",
'{BannedMimetype}',$psize);







	
	$html="
<table style='width:100%'>
<tr>
	<td valign='top'>$bannedextensionlist</td>
	<td valign='top'> $BannedMimetype </td>
</tr>
</table>

";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"dansguardian.index.php");		
	
}


function panel_whitelists(){
	$ID=$_GET["RuleID"];

$ExceptionSiteList=iconTable("routing-rule.png","{ExceptionSiteList}",'{dansguardian_exception_site_list}',
	"Loadjs('dansguardian.exception.sites.php?rule_main=$ID')",
	'{dansguardian_exception_site_list}',$psize);	


//javascript:LoadAjax('dans_rules_content','dansguardian.index.php?rule_main=1&tab=BannedMimetype&hostname=pc-touzeau.klf.fr')
$ExeptionFileSiteList=iconTable("64-download.png","{ExeptionFileSiteList}",'{ExeptionFileSiteList_user_explain}',
"Loadjs('dansguardian.exception.sites.php?rule_main=$ID')",
'{ExeptionFileSiteList}',$psize);	
	$html="<H1>$RuleName</H1>
<table style='width:100%'>
<tr>
	<td valign='top'>$ExceptionSiteList</td>
	<td valign='top'>$ExeptionFileSiteList</td>
</tr>
<tr>
	<td valign='top'></td>
	<td valign='top'></td>
</tr>
<tr>
	<td valign='top'></td>
	<td valign='top'></td>
</tr>
<tr>
	<td valign='top'></td>
	<td valign='top'></td>
</tr>
<tr>
	<td valign='top'>$banned_regex</td>
	<td valign='top'>&nbsp;</td>
</tr>
</table>

";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"dansguardian.index.php");	
}

function panel_blacklists(){
	
$ID=$_GET["RuleID"];
$categories=iconTable("64-categories.png","{categories}","{dansguardian_blacklist_users_categories}",
"Loadjs('dansguardian.categories.php?rule_main=$ID&rule-name=$rulename_encrypted')",null,$psize);
	
$weightedphraselist=iconTable("64-weight-phrases.png","{weightedphraselist}",'{weightedphraselist_text}',
"Loadjs('dansguardian.weight-phrases.php?rule_main=$ID')",
'{weightedphraselist}',$psize);


$bannedphraselist=iconTable("64-banned-phrases.png","{bannedphraselist}",'{bannedphraselist_explain}',
"Loadjs('dansguardian.banned-phrases.php?rule_main=$ID')"
,'{bannedphraselist}',$psize);

$banned_regex=iconTable("64-banned-regex.png","{bannedregexpurllist}",'{bannedregexpurllist_explain}',
"Loadjs('dansguardian.banned-regex-purlist.php?rule_main=$ID')",
'{bannedregexpurllist}',$psize);

$personal_categories=iconTable("64-categories-personnal.png","{personal_categories}",'{personal_categories_text}',
"Loadjs('dansguardian.categories.personnal.php?rule_main=$ID','{personal_categories}')",
'{personal_categories}',$psize);


$html="
<table style='width:100%'>
<tr>
	<td valign='top'>$categories</td>
	<td valign='top'>$personal_categories  </td>
</tr>
<tr>
	<td valign='top'>$bannedphraselist</td>
	<td valign='top'>$weightedphraselist </td>
</tr>
<tr>
	<td valign='top'>$banned_regex</td>
	<td valign='top'>&nbsp;</td>
</tr>


</table>

";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"dansguardian.index.php");	
}





?>