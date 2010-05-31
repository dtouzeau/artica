<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.user.inc');
	
	//if(count($_POST)>0)
	$usersmenus=new usersMenus();
	if(!$usersmenus->AllowAddUsers){
		writelogs("Wrong account : no AllowAddUsers privileges",__FUNCTION__,__FILE__);
		if(isset($_GET["js"])){
			$tpl=new templates();
			$error="{ERROR_NO_PRIVS}";
			echo $tpl->_ENGINE_parse_body("alert('$error')");
			die();
		}
		header("location:domains.manage.org.index.php?ou={$_GET["ou"]}");
		}
	
	
	if(isset($_GET["FindInGroup"])){MEMBERS_SEARCH_USERS();exit;}
	if(isset($_POST["groupid"])){MEMBERS_UPLOAD_FILE();exit();}
	if(isset($_GET["addgroup"])){AddGroup();exit;}
	if(isset($_GET["GroupPriv"])){echo GROUP_PRIVILEGES($_GET["GroupPriv"]);exit;}
	if(isset($_GET["PrivilegesGroup"])){EditGroup();exit;}
	if(isset($_GET["DeleteMember"])){DeleteMember();exit;}
	if(isset($_GET["DeleteNotAffectedUsers"])){MEMBERS_NOT_AFFECTED_DELETE($_GET["ou"]);exit;}

	
	if(isset($_GET["DeleteGroup"])){DeleteGroup();exit;}
	if(isset($_GET["LoadGroupList"])){echo GROUPS_LIST($_GET["LoadGroupList"]);exit;}
	if(isset($_GET["MembersList"])){echo MEMBERS_LIST($_GET["MembersList"]);exit;}
	
	if(isset($_GET["ImportMembersFile"])){MEMBERS_IMPORT_FILE();exit;}
	if(isset($_GET["DeleteMembersForGroup"])){GROUP_DELETE_MEMBERS($_GET["DeleteMembersForGroup"]);exit;}
	if(isset($_GET["ForbiddenAttach"])){GROUP_ATTACHMENTS($_GET["ForbiddenAttach"]);exit();}
	if(isset($_GET["SaveAttachmentGroup"])){FORBIDDEN_ATTACHMENTS_SAVE();exit;}
	if(isset($_GET["LoadGroupSettings"])){GROUP_SETTINGS_PAGE();exit;}
	if(isset($_GET["group_add_attach_rule"])){FORBIDDEN_ATTACHMENTS_ADDRULE();exit;}
	if(isset($_GET["kavmilter_group"])){echo GROUP_KAVMILTER_RULE($_GET["gpid"]);exit;}
	if(isset($_GET["KavMilterGroupAddNewRule"])){echo GROUP_KAVMILTER_ADD_NEW_RULE($_GET["KavMilterGroupAddNewRule"]);exit;}
	if(isset($_GET["AddMemberIntoGroup"])){echo MEMBER_ADD($_GET["gpid"]);exit();}
	if(isset($_GET["DansGuardian_rules"])){GROUP_DANSGUARDIAN($_GET["DansGuardian_rules"]);exit;}
	if(isset($_GET["save_dansguardian_rule"])){GROUP_DANSGUARDIAN_SAVE();exit;}
	if(isset($_GET["delgroup"])){DeleteGroup();exit;}
	if(isset($_GET["GetTreeFolders"])){browser();exit;}
	
	
	if(isset($_GET["LoadMailingList"])){GROUP_MAILING_LIST();exit();}
	if(isset($_GET["RemoveMailingList"])){GROUP_MAILING_LIST_DEL();exit;}
	
	
	if(isset($_GET["LoadComputerGroup"])){COMPUTERS_LIST();exit;}
	if(isset($_GET["FORM_COMPUTER"])){COMPUTER_FORM_ADD();exit;}
	if(isset($_GET["find_computer"])){COMPUTER_FIND();exit;}
	if(isset($_GET["add_computer_to_group"])){COMPUTER_ADD_TO_GROUP();exit;}
	if(isset($_GET["FORM_GROUP"])){GROUP_SAMBA_SETTINGS();exit;}
	if(isset($_GET["SaveGroupSamba"])){GROUP_SAMBA_SETTINGS_SAVE();exit;}
	if(isset($_GET["ShowDeleteSelected"])){MEMBERS_ICON_DELETEALL();exit;}
	if(isset($_GET["DeleteUserByUID"])){MEMBERS_DELETE();exit;}
	if(isset($_GET["default_password"])){GROUP_DEFAULT_PASSWORD();exit;}
	if(isset($_GET["ChangeDefaultGroupPassword"])){GROUP_DEFAULT_PASSWORD_SAVE();exit;}
	
	
	if(isset($_GET["GroupPrivilegesjs"])){GroupPrivilegesjs();exit;}
	
	
	if(isset($_GET["sieve-js"])){GROUP_SIEVE_JS();exit;}
	if(isset($_GET["sieve-index"])){GROUP_SIEVE_INDEX();exit;}
	if(isset($_GET["sieve-save-filter"])){GROUP_SIEVE_SAVE();exit;}
	if(isset($_GET["sieve-update-users"])){GROUP_SIEVE_UPDATE();exit;}
	
	if(isset($_GET["js"])){js();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	
	
	INDEX();
	
	
	
function GroupPrivilegesjs(){
	$gpid=$_GET["GroupPrivilegesjs"];
	$js=file_get_contents("js/edit.group.js");
	$html="
	$js
	GroupPrivileges($gpid);";
	echo $html;
	
}


function js(){
$ou=$_GET["ou"];	
$ou_encrypted=base64_encode($ou);
$cfg[]="js/edit.group.js";
$cfg[]="js/webtoolkit.aim.js";
$cfg[]="js/kavmilterd.js";
$cfg[]="js/edit.user.js";
$cfg[]="js/json.js";
$cfg[]="js/users.kas.php.js";
$title=$ou . ":&nbsp;{groups}";
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body($title);
$page=CurrentPageName();
$prefix=str_replace('.','_',$page);

if(isset($_GET["group-id"])){
	$loadgp="LoadAjax('GroupSettings','domains.edit.group.php?LoadGroupSettings={$_GET["group-id"]}&ou=$ou')";
}

while (list ($num, $ligne) = each ($cfg) ){
	$jsadd=$jsadd.file_get_contents($ligne)."\n";
	
}


$html="
{$prefix}timeout=0;
$jsadd
function LoadGroupAjaxSettingsPage(){
	YahooWinS('750','$page?popup=yes&ou=$ou_encrypted','$title');
	setTimeout('DisplayDivs()',900);
	}
	
function DisplayDivs(){
		{$prefix}timeout={$prefix}timeout+1;
		if({$prefix}timeout>10){
			{$prefix}timeout=10;
			return;
		}
		if(!document.getElementById('grouplist')){
			setTimeout('DisplayDivs()',900);
		}
		LoadAjax('grouplist','$page?LoadGroupList=$ou');
		LoadGroupSettings();
		{$prefix}timeout=0;
		$loadgp
	}
	
LoadGroupAjaxSettingsPage();	
	";

echo $html;
	
	
}

function popup(){
	$ou=base64_decode($_GET["ou"]);
	if($ou==null){$ou=ORGANISTATION_FROM_USER();}
	$page=CurrentPageName();
	$title=$ou . ":&nbsp;{groups}";
	
	$add_group="	<table style='width:100%'>
	<tr>
	<td align='right'>
	<strong>{add_group}:&nbsp;</strong></td>
	<td>" . Field_text('group_add',null,'width:100%',null,null,null,false,"DomainEditGroupPressKey(event)") ."</td>
	<td><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:addgroup();\"></td>
	</tr>
	</table>";
	
	
	
	$html=RoundedLightGrey("
	<input type='hidden' id='inputbox delete' value=\"{are_you_sure_to_delete}\">
	<input type='hidden' id='ou' value='$ou'>
	<input type='hidden' id='warning_delete_all_users' value='{warning_delete_all_users}'>
		<table style='width:100%'>
		<tr>
		<td valign='top'>$add_group</td>
		<td valign='top'><span id='grouplist'></span>
		</td>
		</table>
	")."
	<br>
	<div id='GroupSettings'></div>
	<div id='MembersList'></div>
	<div id='groupprivileges'></div>";

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}
	
function INDEX(){
	$ou=$_GET["ou"];
	if($ou==null){$ou=ORGANISTATION_FROM_USER();}
	$page=CurrentPageName();
	$title=$ou . ":&nbsp;{groups}";
	$html=RoundedLightGrey("
	<input type='hidden' id='inputbox delete' value=\"{are_you_sure_to_delete}\">
	<input type='hidden' id='ou' value='$ou'>
	<input type='hidden' id='warning_delete_all_users' value='{warning_delete_all_users}'>
	<table style='width:300px'>
	<tr>
	<td align='right'>
	<strong>{add_group}:&nbsp;</strong></td>
	<td>" . Field_text('group_add',null,'width:100%') ."</td>
	<td><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:addgroup();\"></td>
	</tr>
	</table>
	<span id='grouplist'></span>
	")."
	<br>
	<div id='GroupSettings'></div>
	<div id='MembersList'></div>
	<div id='groupprivileges'></div>	
	
	<script>LoadAjax('grouplist','$page?LoadGroupList=$ou');</script>
	<script>LoadGroupSettings();</script>
	
	";
	
	
$cfg["JS"][]="js/edit.group.js";
$cfg["JS"][]="js/webtoolkit.aim.js";
$cfg["JS"][]="js/kavmilterd.js";
$cfg["JS"][]="js/edit.user.js";
$cfg["JS"][]="js/json.js";
$cfg["JS"][]="js/users.kas.php.js";

$tpl=new template_users($title,$html,0,0,0,0,$cfg);	
echo $tpl->web_page;		
}
function ORGANISTATION_FROM_USER(){
	include_once(dirname(__FILE__).'/ressources/class.user.inc');
	$ct=new user($_SESSION["uid"]);
	return $ct->ou;
	}
	
function GROUP_SIEVE_JS(){
	$gid=$_GET["sieve-js"];
	$tpl=new templates();
	$gp=new groups($gid);
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("$gp->ou::$gp->groupName::{sieve_auto_script}");
	
	$html="
		function SieveGroupOptions(){
			YahooWin2(500,'$page?sieve-index=$gid','$title');
			}
			
		function x_SieveSaveArticaFilters(obj){
				var tempvalue=obj.responseText;
				if(tempvalue.length>0){alert(tempvalue);}
				SieveGroupOptions();
				YahooWin3(500,'$page?sieve-update-users=$gid','$title');
				}			
			
		function SieveSaveArticaFilters(){
			var XHR = new XHRConnection();
			XHR.appendData('sieve-save-filter',document.getElementById('EnableSieveArticaScript').value);
			XHR.appendData('gid','$gid');
			document.getElementById('div-sieve-filters').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SieveSaveArticaFilters);	
			}
			
		SieveGroupOptions();
	
	";
	echo $html;
	}
	
function GROUP_SIEVE_INDEX(){
	$gid=$_GET["sieve-index"];
	$gp=new groups($gid);
	
	$form=Paragraphe_switch_img("{sieve_auto_script}","{sieve_auto_explain}","EnableSieveArticaScript",$gp->Privileges_array["EnableSieveArticaScript"],null,"100%");
	
	$html="<H1>{sieve_auto_script}</H1>
	<div id='div-sieve-filters'>
		$form
		<div style='text-align:right'><input type='button' OnClick=\"javascript:SieveSaveArticaFilters();\" value='{edit}&nbsp;&raquo;'></div>
	</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function GROUP_SIEVE_SAVE(){
	$gid=$_GET["gid"];
	$value=$_GET["sieve-save-filter"];
	$gp=new groups($gid);
	$gp->Privileges_array["EnableSieveArticaScript"]=$value;
	$gp->SavePrivileges();
	}
	
function GROUP_SIEVE_UPDATE(){
	$gid=$_GET["sieve-update-users"];
	$tpl=new templates();
	$gp=new groups($gid);
	echo $tpl->_ENGINE_parse_body("<H1>{sieve_auto_script}:{events}</H1>");
	
	if($gp->Privileges_array["EnableSieveArticaScript"]==1){
		include_once('ressources/class.sieve.inc');
		if(!is_array($gp->members_array)){
			echo $tpl->_ENGINE_parse_body("<span style='color:red;font-size:14px;font-weight:bold;color:red'>{ERROR_GROUP_STORE_NO_MEMBERS}</span>");
			return null;
		}
		echo "<div style='width:100%;height:200px;overflow:auto;background-color:white'>";
		while (list ($num, $ligne) = each ($gp->members_array) ){
			if(trim($num)==null){continue;}
			$sieve=new clSieve($num);
			$sieve->ECHO_ERROR=false;
			if($sieve->AddAutoScript()){
				
			}else{
				$result=$tpl->_ENGINE_parse_body("{failed}:<div style='margin:4px;font-size:10px;font-weight:bold;color:red'>$sieve->error</div>");
			}
			
			echo "<div style='border:1px dotted #CCCCCC;padding:3px;margin:3px'>
					<div style='font-size:12px;font-weight:bold'>$num:&nbsp;<code>$result</code></div>
				
				</div>";
		}
		
		echo "</div>";
	}
}


	
	
	
function GROUPS_LIST($OU){
	$ou=$OU;
	writelogs("ou=$ou,{$_SESSION["uid"]}",__FUNCTION__,__FILE__);
	
	
	$ldap=new clladp();
	$users=new usersMenus();
	
	
	
	if($users->AsArticaAdministrator){
		writelogs("AsArticaAdministrator privileges",__FUNCTION__,__FILE__);
		$org=$ldap->hash_get_ou(true);
		$orgs=Field_array_Hash($org,'SelectOuList',$ou,"LoadGroupList()",null,0,'width:250px');
		$hash=$ldap->hash_groups($ou,1);
	}else{
		$ou=ORGANISTATION_FROM_USER();
		$orgs="<strong>$ou</strong><input type='hidden' name=SelectOuList id='SelectOuList' value='$ou'>";
		$hash=$ldap->UserGetGroups($_SESSION["uid"],1);
		if(is_array($hash)){
			while (list ($num, $line) = each ($hash)){
				if(strtolower($line)=='default_group'){unset($hash["$num"]);}
			}
		reset($hash);
		}
	}
		
	
	
	writelogs("Load " . count($hash) . " groups from ou $OU",__FUNCTION__,__FILE__);
	$hash[null]="{select}";
	$field=Field_array_Hash($hash,'SelectGroupList',null,"LoadGroupSettings()",null,0,'width:250px');
	$html="
	<table style='width:300px'>
	<tr>
	<td align='right' nowrap><strong>{select_org}:</strong></td>
	<td width=80%>$orgs</td>	
	<td>&nbsp;</td>
	<tr>
	<td align='right' nowrap><strong>{select_group}:</strong></td>
	<td width=80%>$field</td>
	<td width=1%>" . imgtootltip('20-refresh.png','{refresh}',"LoadAjax('grouplist','$page?LoadGroupList=$ou');")."</td>
	</tr>
	</table>
	";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
}

function GROUP_DEFAULT_PASSWORD(){
	$gpid=$_GET["gpid"];
	$group=new groups($gpid);
	
	$html="<h1>{group_default_password}</H1>
	<div id='GROUP_DEFAULT_PASSWORD'>
	<p class=caption>{group_default_password_text}</p>
	<input type='hidden' id='error_passwords_mismatch' value='{error_passwords_mismatch}'>
	<table style='width:100%' class=table_form>
		<tr>
		<td class=legend>{password}:</td>	
		<td>" . Field_password("default_password1",$group->DefaultGroupPassword)."</td>
		</tr>
		<tr>
		<td class=legend>{confirm}:</td>	
		<td>" . Field_password("default_password2",$group->DefaultGroupPassword)."</td>
		</tr>
		<td class=legend>{change_password_now}:</td>	
		<td>" . Field_onoff_checkbox_img('change_now','no','{group_default_password_change}')."</td>
		</tr>
		<tr>
			<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ChangeDefaultGroupPassword($gpid);\" value='{edit}&nbsp;&raquo;'>
		</tr>
	</table>	
	</div>	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function GROUP_DEFAULT_PASSWORD_SAVE(){
	$gpid=$_GET["ChangeDefaultGroupPassword"];
	$group=new groups($gpid);
	$group->DefaultGroupPassword=$_GET["password"];
	if(!$group->edit_DefaultGroupPassword()){
		echo $group->ldap_error;
		exit;
	}
	
	if($_GET["change_now"]=="on"){
		if(!$group->changeAllMembersPassword()){
			echo "Members password:failed\n";
			exit;
		}
	}
	
	
	
	
}

function GROUP_SETTINGS_PAGE(){
	$ldap=new clladp();
	$page=CurrentPageName();
	$num=$_GET["LoadGroupSettings"];
	if(!is_numeric($num)){return null;}
	if(trim($num)==null){$num=0;}
	if($num==0){
		if(isset($_GET["byGroupName"])){$num=$ldap->GroupIDFromName($_GET["ou"],$_GET["byGroupName"]);}else{return null;}
	}	
	
	$group=new groups($num);
	if(trim($_GET["ou"])<>null){
		if($group->ou<>$_GET["ou"]){
			$tpl=new templates();
			$error="<center style='border:2px solid red;padding:10px;margin:10px'><span style='font-size:13px;font-weight:bold;color:red'>Group: $num<br> {error_group_not_in_your_organization}</span></center>";
			echo $tpl->_ENGINE_parse_body($error);
			writelogs("ERROR: group organization $group->ou is different from requested organization \"{$_GET["ou"]}\"",__FUNCTION__,__FILE__);
			return null;
			}
	}
	
	
	$text_disbaled="{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}";
	$user=new usersMenus();
	$user->LoadModulesEnabled();
	$tab=GROUP_SETTING_PAGE_TAB();
	
	$group=new groups($num);
	$SAMBA_GROUP=Paragraphe('64-group-samba-grey.png','{MK_SAMBA_GROUP}',$text_disbaled,'');
	
	
	
	$mailing_list=Paragraphe('64-mailinglist-grey.png',"{mailing_list}","$text_disbaled");
	

	
	
	
	$hash=$ldap->GroupDatas($num);
	
	
	
	$members=count($hash["members"]);
	

	
	if($user->POSTFIX_INSTALLED==true){
		$mailing_list_count=$group->CountMailingListes();
		$mailing_list=Paragraphe('64-mailinglist.png',"($mailing_list_count) {mailing_list}","{mailing_list_text}","javascript:LoadMailingList($num)");
		
	}
	
	if($user->DANSGUARDIAN_INSTALLED==true){
		$DANSGUARDIAN=Paragraphe('icon-chevallier-564.png','{dansguardian_rules}','{dansguardian_rules_text}',"javascript:DansGuardianRules($num)");
		//
	}
	
	
	
	
	$automount=Paragraphe('folder-64-automount.png','{shared_folders}','{shared_folders_text}',"javascript:Loadjs('SharedFolders.groups.php?gpid=$num')");
		
	
	
	if($user->cyrus_imapd_installed){
		$sieve_auto=Paragraphe('64-learning.png','{sieve_auto_script}','{sieve_auto_script_text}',"javascript:Loadjs('$page?sieve-js=$num')");
	}
	
	
	
	
	if($user->SAMBA_INSTALLED){
		$COMPUTERS=Paragraphe('computers-64.png','{computers}','{computers_text}',"javascript:LoadComputerGroup($num)");
		$SAMBA_GROUP=Paragraphe('64-group-samba.png','{MK_SAMBA_GROUP}','{MK_SAMBA_GROUP_text}',"javascript:Change_group_settings($num)");
		$LOGON_SCRIPT=Paragraphe('script-64.png','{LOGON_SCRIPT}','{LOGON_SCRIPT_TEXT}',"javascript:Loadjs('domains.edit.group.login.script.php?gpid=$num')");
		}
	
	
	
	
	if($DANSGUARDIAN==null){$DANSGUARDIAN=Paragraphe('icon-chevallier-564-grey.png','{dansguardian_rules}',$text_disbaled,'');}
	if($automount==null){$automount=Paragraphe('folder-64-automount-grey.png','{shared_folders}',$text_disbaled,'');}	
	
	if($COMPUTERS==null){$COMPUTERS=Paragraphe('computers-64-grey.png','{computers}',$text_disbaled,'');}
	
	if(!$user->cyrus_imapd_installed){
		if($user->SAMBA_INSTALLED){
			$sieve_auto=$LOGON_SCRIPT;
			$LOGON_SCRIPT=null;
		}
		
	}
	
	$RENAME_GROUP=Paragraphe('group_rename-64.png','{GROUP_RENAME}','{GROUP_RENAME_TEXT}',"javascript:Loadjs('domains.edit.group.rename.php?group-id=$num&ou={$_GET["ou"]}')");
	$OPTIONS_DEFAULT_PASSWORD=Paragraphe('64-key.png','{group_default_password}','{group_default_password_text}',"javascript:YahooWin('400','$page?default_password=yes&gpid=$num')");
	
	
	$html_tab1="
	<table style='width:100%'>
	<tr>
	<td valign='top'>".Paragraphe('members-priv-64.png','{privileges}','{privileges_text}',"javascript:GroupPrivileges($num)") ."</td>
	<td valign='top'>".Paragraphe('member-64.png',"($members) {members}","{members_text}","javascript:LoadMembers($num)") ."</td>
	<td valign='top'>$COMPUTERS</td>
	</tr>
	<tr>
	<td valign='top'>$SAMBA_GROUP</td>
	<td valign='top'>$mailing_list</td>
	<td valign='top'>$automount</td>
	</tr>
	</table>";
	
	$html_tab2="	<table style='width:100%'>
	<tr>
	<td valign='top'>&nbsp;</td>
	<td valign='top'>&nbsp;</td>
	<td valign='top'>&nbsp;</td>
	</tr>
	<tr>
	<td valign='top'>&nbsp;</td>
	<td valign='top'>&nbsp;</td>
	<td valign='top'>&nbsp;</td>
	</tr>
	</table>";
	
	$html_tab3="	
	<table style='width:100%'>
		<tr>
			<td valign='top'>$DANSGUARDIAN</td>
			<td valign='top'>&nbsp;</td>
			<td valign='top'>&nbsp;</td>
		</tr>
		<tr>
			<td valign='top'>&nbsp;</td>
			<td valign='top'>&nbsp;</td>
			<td valign='top'>&nbsp;</td>
		</tr>
	</table>";

	
	$html_tab4="
	<table style='width:100%'>
		<tr>
			<td valign='top'>$RENAME_GROUP</td>
			<td valign='top'>$OPTIONS_DEFAULT_PASSWORD</td>
			<td valign='top'>$sieve_auto</td>
			
		</tr>
		<tr>
			<td valign='top'>$LOGON_SCRIPT</td>
			<td valign='top'>&nbsp;</td>
			<td valign='top'>&nbsp;</td>
		</tr>
	</table>
	
	";
	
	
	if($_GET["tab"]=='asav'){$html_tab1=$html_tab2;}
	if($_GET["tab"]=='proxy'){$html_tab1=$html_tab3;}
	if($_GET["tab"]=='options'){$html_tab1=$html_tab4;}
	$html=$html_tab1;
	
	$tpl=new templates();
	
	$barre_principale="
	<input type='hidden' id='group_delete_text' value='{group_delete_text}'>
	<table style='width:100%'>
	<tr>
		<td width=3%><div style='height:1px;border-bottom:1px solid #CCCCCC;width:100%;float:right'>&nbsp;</div></td>
		<td width=1% nowrap><H5>{group}&nbsp;&nbsp;&laquo;&nbsp;{$hash["cn"]}&nbsp;&raquo;</td>
		<td><div style='height:1px;border-bottom:1px solid #CCCCCC;width:100%;float:right'>&nbsp;</div></td>
		<td width=1%>" . imgtootltip("32-cancel.png","{delete}::{$hash["cn"]}","Loadjs('domains.delete.group.php?gpid=$num')")."</td>
	</tr>
	</table>
	
	";
	
	echo $tpl->_ENGINE_parse_body("$barre_principale$tab" .RoundedLightGrey($html));
	}

function GROUP_SETTING_PAGE_TAB(){
	if(!isset($_GET["tab"])){$_GET["tab"]="config";}
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$users=new usersMenus();
	$page=CurrentPageName();
	$array["config"]='{group_settings}';
	if($users->POSTFIX_INSTALLED){
		$array["asav"]='{asav}';
	}
	if($users->SQUID_INSTALLED){
		$array["proxy"]='{proxy}';
	}
	$array["options"]='{advanced_options}';
	
	if(!$users->AsArticaAdministrator){
	if($users->AllowAddUsers){return null;}
	}
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadGroupSettings('$num');\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}




function MEMBERS_LIST_TABS($maxpages,$currentpage){
	$gid=$_GET["MembersList"];
	$page=CurrentPageName();
	$url="$page?MembersList=$gid";
	if(!isset($_GET["next"])){$next=0;}else{$next=$_GET["next"];}
	$nextnext=$next+1;
	$splitPages=4;
	
	// calcul de la page de fin
	$start=($next*$splitPages);
	if($start<0){$start=0;}
	$max=$start+$splitPages;
	$nextpage=$next+$splitPages+1;
	$backpage=$next-1;
	if($maxpages>$splitPages){
		$end="<li><a href=\"javascript:LoadAjax('MembersList','$url&page=$nextpage&next=$nextnext')\" $class>{next}&nbsp;&raquo;&raquo;</a></li>";
		$find="<li><a href=\"javascript:FindInGroup($gid);\">&laquo;&nbsp;{search}&nbsp;&raquo;</a></li>";
		
	}
	
	// calcul de la page de debut.
	if($backpage>=0){
		$start_page="<li><a href=\"javascript:LoadAjax('MembersList','$url&page=$nextpage&next=$backpage')\" $class>&laquo;&laquo;{back}</a></li>";
	}
	
	   
	
	for($i=$start;$i<$max;$i++){
		if($currentpage==$i){$class="id=tab_current";}else{$class=null;}
		$page_name=$i+1;
		
		$html=$html . "<li><a href=\"javascript:LoadAjax('MembersList','$url&page=$i&next=$next')\" $class>&laquo;&nbsp;{page} $page_name&nbsp;&raquo;</a></li>\n";
			
		}
	return "
	<input type='hidden' id='FindInGroup_text' value='{FindInGroup_text}'>
	<div id=tablist>
		$start_page$html$find$end
	</div>
	<br>";			
	
	
}
function MEMBERS_ICON_DELETEALL(){
	
	if($_GET["ShowDeleteSelected"]>0){
		echo "<br>".imgtootltip('64-delete_user.png',"{delete_selected_members} ({$_GET["ShowDeleteSelected"]})","DeleteSelectedMembersGroup()");
	}
	
}


function MEMBERS_LIST($gid){

	
	
	$group=new groups($gid);
	$js_addmember="Loadjs('domains.add.user.php?ou=$group->ou&gpid=$gid')";
$html="
<input type='hidden' id='groups-section-from-members' value='$gid'>
<H1>&laquo;{$group->groupName}&raquo;&nbsp;{members}</H1>
<div id='delete_this_user' value='{delete_this_user}'>
<input type='hidden' name='sure_to_delete_selected_user' id='sure_to_delete_selected_user' value='{sure_to_delete_selected_user}'>
<br>
	<table style='width:100%'>
	<td valign='top'><div id='members_area'>".(MEMBERS_LIST_LIST($gid)) ."</div></td>
	<td valign='top' width=5%>
	<table style='width:100%'>
		<tr>
		<td>".  imgtootltip('member-add-64.png','{add_member}',$js_addmember)."</td>
		</tr>	
		<tr>
		<td>". imgtootltip('member-64-import.png','{import}',"Loadjs('domains.import.members.php?gid=$gid')")."</td>
		</tr>
		<tr>
		<td>".  imgtootltip('member-64-delete.png','{delete_members}',"DeleteMembersGroup($gid)")."</td>
		</tr>
		<tr>
		<span id='ShowDeleteAll'></span>		
	</table>
		
	
	</td>
	
	</table><br>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
}


function GROUP_MAILING_LIST(){
	$ou=$_GET["LoadMailingList"];
	$group=new groups(null);
	$hash=$group->load_MailingList($ou);
	
	$html="
	<input type='hidden' id='RemoveMailingList_text' value='{RemoveMailingList_text}'>
	<table style='width:90%' align=\"center\" style='margin-left:50px'>";
	
	
	while (list ($num, $ligne) = each ($hash) ){
		
		$ldap=new clladp();
		$uid=$ldap->uid_from_email($num);
		$js=MEMBER_JS($uid,1);
		$delete="RemoveMailingList('$ou','$num');";
		
		$html=$html . "
		<tr ". CellRollOver().">
		<td width=1%>".imgtootltip('24-mailinglist.png','{select}',$js)."</td>
		<td><strong>".texttooltip("$num ($ligne {members})","{select}",$js)."</strong></td>
		<td width=1%>".imgtootltip('ed_delete.gif','{delete}',$delete)."</td>
		</tr>
		
	";
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("<h1 style='width:103%'>{mailing_list}</h1>".RoundedLightWhite($html));
	
}

function GROUP_MAILING_LIST_DEL(){
	$ldap=new clladp();
	$dn="cn={$_GET["RemoveMailingList"]},cn=aliases-mailing,ou={$_GET["ou"]},dc=organizations,$ldap->suffix";
	if(!$ldap->ldap_delete($dn,true)){
		echo $ldap->ldap_last_error;
	}
	
}



function MEMBERS_SEARCH_USERS(){
	$gid=$_GET["FindInGroup"];
	$pattern=$_GET["pattern"];
	$pattern=str_replace('*','',$pattern);
$styleRoll="
	style='border:1px solid white;width:190px;float:left;margin:1px'
	OnMouseOver=\"this.style.backgroundColor='#F3F3DF';this.style.cursor='pointer';this.style.border='1px solid #CCCCCC'\"
	OnMouseOut=\"this.style.backgroundColor='transparent';this.style.cursor='auto';this.style.border='1px solid white'\"
	";	
	
	
	//first we search the users 
	$ldap=new clladp();
	$hash=$ldap->UserSearch(null,$pattern);

	
	//second we load users uids of the group and build the hash
$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,"(gidnumber=$gid)",array("memberUid"));
	if(!$sr){
		writelogs("Search members for (gidnumber=$gid) failed",__FUNCTION__,__FILE__);
		return null;
	
	}
	$entry_id = ldap_first_entry($ldap->ldap_connection,$sr);
	if(!$entry_id){return null;}
	$attrs = ldap_get_attributes($ldap->ldap_connection, $entry_id);
	if(!is_array($attrs["memberUid"])){
		writelogs("memberUid no attrs",__FUNCTION__,__FILE__);
		return null;
		}
	
		$count=$attrs["memberUid"]["count"];
		while (list ($num, $ligne) = each ($attrs["memberUid"]) ){
			$hash_members[$ligne]=true;
		}
	
	//now we parse the search results
	$count=0;
	for($i=0;$i<$hash["count"];$i++){
		if($hash_members[$hash[$i]["uid"][0]]){
			$uid=$hash[$i]["uid"][0];
			
			$count=$count+1;
			$html=$html . "<div $styleRoll id='mainid_{$uid}'>".MEMBERS_SELL($uid)."</div>";
			if($count>41){break;}
			
		}
		
	}
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function MEMBERS_LIST_LIST($gid){
	
	if($gid=="undefined"){$gid=0;}
	
	$ldap=new clladp();
	$hashGroup=$ldap->GroupDatas($gid);	
	
	$members=$hashGroup["ARRAY_MEMBERS"];

	
	$count=count($members);
	$number_of_users=$count;
	writelogs("found $count members for (gidnumber=$gid)",__FUNCTION__,__FILE__);
	if(!is_array($members)){return null;}
	sort($members);
	
	if($_GET["page"]==null){$_GET["page"]=0;}
	
	$priv=new usersMenus();
	
	$pagesNumber=round($count/40,1);
	//if(strpos($pagesNumber,'.')>0){$pagesNumber=$pagesNumber+1;}
	
	$pageaffich=round($pagesNumber);
	$pageaffich=$pageaffich+1;
	
	if($count>40){
		$start=$_GET["page"]*40;
		$max=$start+40;
		$curmx=$count-$start;
		$cols=round($curmx/10);
	}else{
		$start=0;
		$max=$count;
		$cols=round($max/10);
	}	
	
		if($pagesNumber>1){
			$title_pages="&nbsp;&laquo;&nbsp; {$_GET["page"]}/$pageaffich {pages} {members} $start/$max ($number_of_users {members})&nbsp;&raquo;&nbsp;";
			$tabs=MEMBERS_LIST_TABS($pagesNumber,$_GET["page"]);
		}
	
		
	$html="
	$tabs
	<table style='width:100%;padding:1px;border:1px solid #CCCCCC'>
	</tr><td valign='top'><div id='listofMembersOfThisGroup'>";

	

	
	$styleRoll="
	style='border:1px solid white;width:190px;float:left;margin:1px'
	OnMouseOver=\"this.style.backgroundColor='#F3F3DF';this.style.cursor='pointer';this.style.border='1px solid #CCCCCC'\"
	OnMouseOut=\"this.style.backgroundColor='transparent';this.style.cursor='auto';this.style.border='1px solid white'\"
	";
	
	for($i=$start;$i<=$max;$i++){
		
		$html=$html . "<div $styleRoll id='mainid_{$members[$i]}'>".MEMBERS_SELL($members[$i],$i)."</div>";
		
	}
	$html=$html . "</div></td></table><p>&nbsp;</p>";
	$tpl=new templates();
	$html="
	<p class=caption>$title_pages</p>
	<div style='width:99.5%;height:300px;overflow:auto'>$html</div>";
	
	return  $tpl->_ENGINE_parse_body($html);
}


function MEMBERS_SELL($uid,$number=null){
	if($uid==null){return "&nbsp;";}
	$computer_img="base.gif";
	$user_img="user-single-18.gif";
	$tpl=new templates();
	$view_member=$tpl->_ENGINE_parse_body('{view_member}');
	$show=MEMBER_JS($uid);
	if(substr($uid,strlen($uid)-1,1)=='$'){
		$img=$computer_img;
	}else{$img=$user_img;}
	$uid_show=$uid;
	if(strlen($uid)>23){
		$uid_show=substr($uid,0,20)."...";
	}
	
	$html="<table style='width:100%'>
	<tr>
		<td width=1%>
			<img src='img/$img' id='icon_$uid'>
		</td>
		<td $show><strong id='$uid'>".texttooltip($uid_show,'{view_member}')."</strong>
			<input type='hidden' id='deleteuid_$uid' name='deleteuid_$uid' value='0'>
			<input type='hidden' id='orgin_icon_$uid' name='orgin_icon_$uid' value='img/$img'></td>
		<td width=1%>" .imgtootltip('ed_delete.gif','{delete}',"DeleteUID('$uid')")."</td>
	</tr>
	</table>
	";
	return $html;
}





function MEMBERS_NOT_AFFECTED_DELETE($ou){
	$ldap=new clladp();
	$hash_users=$ldap->hash_get_users_Only_ou($ou);	
	
	while (list ($num, $ligne) = each ($hash_users) ){
		$ldap=new clladp();
		$dn=$ldap->_Get_dn_userid($ligne);
		if($dn<>null){
			$ldap->ldap_delete($dn,true);
		}
		
	}
	echo GROUPS_LIST($ou);
	
}


function MEMBERS_NOT_AFFECTED($ou){
	
	$ldap=new clladp();
	$hash_users=$ldap->hash_get_users_Only_ou($ou);
	if(!is_array($hash_users)){return null;}
	return count($hash_users);
	$html="
	
	<table style='width:400px;margin-left:10px'>";
	while (list ($num, $ligne) = each ($hash_users) ){
		$arr=$ldap->UserDatas($ligne);
		$mail=$arr["mail"];
		$domain=$arr["domainName"];
		$html=$html . "
		<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><a href='domains.edit.user.php?userid=$ligne&tab=3'>$ligne</a></td>
		<td>$mail</td>
		<td>$domain</td>
		<td>" . imgtootltip('x.gif','{delete}',"javascript:DeleteMember('$ligne','0')")."</td>
		</tr>";
		
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);	
	
}


function AddGroup(){
	$group=$_GET["addgroup"];
	$ou=$_GET["ou"];
	$ldap=new clladp();
	include_once(dirname(__FILE__).'/ressources/class.groups.inc');
	
	$groupClass=new groups();
	$list=$groupClass->samba_group_list();
	
	if(is_array($list)){
		while (list ($num, $ligne) = each ($list) ){
			if(trim(strtolower($ligne))==trim(strtolower($group))){
				$tpl=new templates();
				echo $tpl->_ENGINE_parse_body('{no_samba_group_in_ou}');
				exit;
			}
		}	
	}
	
	if(!$ldap->AddGroup($group,$ou)){echo $ldap->ldap_last_error;}
	
}

function GROUP_DELETE_MEMBERS($gid){
	
	$ldap=new clladp();
	$hash=$ldap->GroupDatas($gid);
	
	if(is_array($hash["members"])){
		while (list ($num, $ligne) = each ($hash["members"]) ){
			$ldap->GroupDeleteUser($gid,$num);
			}
	}
	
	
	
	//if(!$ldap->Ldap_del_mod($hash["dn"],$upd["memberUid"])){echo $ldap->ldap_last_error;}
	
	
}


function FORBIDDEN_ATTACHMENTS_SAVE(){
	$ldap=new clladp();
	$gid=$_GET["SaveAttachmentGroup"];
	unset($_GET["SaveAttachmentGroup"]);

	while (list ($num, $ligne) = each ($_GET) ){
		if($ligne=='yes'){
			$ldap->GroupForbiddenAttachment($num,$gid,true);
		}else{$ldap->GroupForbiddenAttachment($num,$gid,false);}
	
	}
	
	
	
	
		
}

function FORBIDDEN_ATTACHMENTS_ADDRULE(){
	
	$ldap=new clladp();
	$hash=$ldap->GroupDatas($_GET["group_add_attach_rule"]);
	$ou=$_GET["ou"];
	$rule=$hash["cn"]. "_attach";
	
	
	
	
	
$dn="cn=forbidden_attachments,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='PostFixStructuralClass';	
		$upd["cn"]="forbidden_attachments";
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;exit;}
		unset($upd);
	}

	
for($i=1;$i<100;$i++){
	$dn="cn={$rule}-$i,cn=forbidden_attachments,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]='top';	
		$upd["objectClass"][]='FilterExtensionsGroup';
		$upd["cn"]="{$rule}-$i";
		$rule="{$rule}-$i";
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;exit;}
		unset($upd);
		break;
	}		
	
}
	

	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]='top';	
		$upd["objectClass"][]='FilterExtensionsGroup';
		$upd["cn"]=$rule;
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;exit;}
		unset($upd);
	}	

	
	$upd["FiltersExtensionsGroupName"]=$rule;
	if($ldap->Ldap_add_mod($hash["dn"],$upd)){echo $ldap->ldap_last_error;}	
	
	
}




function FORBIDDEN_ATTACHMENTS_GROUPS($gid){
	
	
	$ldap=new clladp();	
	$hashG=$ldap->GroupDatas($gid);
	$ou=$hashG["ou"];
	if($ou==null){$ou=$_GET["ou"];}
	$page=CurrentPageName();
	
	$path="cn=forbidden_attachments,ou=$ou,dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($path,'(&(ObjectClass=FilterExtensionsGroup)(cn=*))',array('cn'));
	
	
	$html="
	<center><input type='button' value='&laquo;&nbsp;{add_attach_rule}&nbsp;&raquo;' OnClick=\"javascript:group_add_attach_rule('$gid');\"></center>
	<form name='FFM1'>
	
	<input type='hidden' name='SaveAttachmentGroup' value='$gid'>
	<table style='width:100%;padding:1px;border:1px solid #CCCCCC;margin:20px'>
		<tr style='background-color:#CCCCCC'>
		<th>&nbsp;</th>
		<th><strong>{artica_filtersext_rules}&nbsp;{group}</th>
		<th><strong>{enabled}</th>
		</tr>";
	if(is_array($hash)){
	for($i=0;$i<$hash["count"];$i++){
		$group_name=$hash[$i]["cn"][0];
		if(trim($group_name)<>null){
				if($hashG["FiltersExtensionsGroupName"][$group_name]=="yes"){$value='yes';}else{$value="no";}
				
				$html=$html . "
				<tr style='background-color:#F3F3DF'>
				<td width='1%'><img src='img/red-pushpin-24.png'></td>
				<td><strong style='font-size:13px'>$group_name</strong></td>
				<td width=1% align='center'>" . Field_yesno_checkbox_img($group_name,$value,'{enable_disable}') . "</td>
				</tr>";
			}
		}}
		return $html."
		<tr>
		<td width=1% valign='top' align='right' style='background-color:#F6F5E7' colspan=3>
		<input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFM1','$page',true);\">
		</td>
		</FORM>
		</table>";	
	
}




function GROUP_KAVMILTER_ADD_NEW_RULE($gid){
	include_once('ressources/class.kavmilterd.inc');
	$ldap=new clladp();
	$hash=$ldap->GroupDatas($gid);
	$milter=new kavmilterd();
	$milter->LoadRule("{$hash["cn"]}.{$hash["ou"]}");
	$milter->SaveRuleToLdap();
	$milter->KavMilterdGroup=$gid;
	$milter->AddRuleToGroup();
	
	
	
}


function GROUP_ATTACHMENTS($gid){
	
		$ldap=new clladp();
    	$hash=$ldap->GroupDatas($gid);
    	$ou=$hash["ou"];	
    	
    	$html="<H5>{artica_filtersext_rules}</H5>" . RoundedLightGreen("
    	<div class=caption>{attachments_deny_text}</div>
   		" . FORBIDDEN_ATTACHMENTS_GROUPS($gid));
    	
    	$tpl=new templates();
    	echo $tpl->_ENGINE_parse_body($html);
    	}
    	
    	
function GROUP_DANSGUARDIAN($gid){
		include_once('ressources/class.dansguardian.inc');
		$users=new usersMenus();
		
	    $ldap=new clladp();
    	$hashG=$ldap->GroupDatas($gid);
    	$ou=$hash["ou"];
    	
    	$dans=new dansguardian($users->hostname);
    	$hash=$dans->Master_rules_index;
    	
    	if(is_array($hash)){
		while (list ($num, $line) = each ($hash)){
			if(preg_match('#(.+?);(.+)#',$line,$re)){
				$rulename=$re[1];
			
			}else{
				$rulename=$line;
			}
			
			$rules[$num]=$rulename;
		}
    	}
    	
    	$rules[0]="{no_rules}";
    	$field=Field_array_Hash($rules,'dansguardian_rule',$hashG["ArticaDansGuardianGroupRuleEnabled"],null,null,0,"width:300px;font-size:13px;padding:5px");
    	
    	
    	$form="
    	<table style='width:100%'>
    	<tr>
    	<td align='right' nowrap><strong>{selected_rule}:&nbsp;</strong></td>
    	<td width=70%>$field</td>
    	<td align=left><input type=button value='{edit}&nbsp;&raquo;' OnClick=\"javascript:EditGroupDansGuardianRule('$gid','$ou');\" style='width:200px'></td>
    	</tr>
    	</table>
    	";
    	
    	$form=RoundedLightGreen($form);
    	
    	
    	$html="<br>
    	<H5>{dansguardian_rules}</H5>
    	<p class=caption>{dansguardian_rules_text}</p>
    	<br>
    	$form
    	<br>
    	";
    	
    	
    	$tpl=new templates();
    	echo $tpl->_ENGINE_parse_body($html);
    			
    	}
function GROUP_DANSGUARDIAN_SAVE(){
	$ldap=new clladp();
	$hashG=$ldap->GroupDatas($_GET["gpid"]);
	$upd["ArticaDansGuardianGroupRuleEnabled"][0]=$_GET["save_dansguardian_rule"];
	$ldap->Ldap_modify($hashG["dn"],$upd);
	echo $ldap->ldap_last_error;
	
}


    	

function GROUP_PRIVILEGES_TABS($gid){
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["U"]='{users_allow}';
	
	if($users->AllowEditOuSecurity){
		$array["G"]='{groups_allow}';
		$array["O"]='{organization_allow}';
		$array["A"]='{administrators_allow}';		
	}
	
	while (list ($num, $ligne) = each ($array) ){
		$a[]="<li><a href=\"$page?GroupPriv=$gid&tab=$num\"><span>$ligne</span></a></li>\n";
			
		}
		
$html="
	<div id='{$gid}_priv' style='background-color:white;height:450px;overflow:auto;width:100%'>
	<ul>
		". implode("\n",$a). "
	</ul>
		</div>
		<script>
				$(document).ready(function(){
					$('#{$gid}_priv').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			$('#{$gid}_priv').tabs('option', 'fx', { opacity: 'toggle' });
			});
		</script>
	
	";

		$tpl=new templates();
    	return $tpl->_ENGINE_parse_body($html);
			
}  






    	
function GROUP_PRIVILEGES($gid){
	    $usr=new usersMenus();
	    
    	if(!isset($_GET["tab"])){
    		echo GROUP_PRIVILEGES_TABS($gid);
    		return;
    		
    	}
    	
    	if(isset($_GET["start"])){
    		$div1="<div id='{$gid}_priv'>";
    		$div2="</div>";
    		
    	}
    	
		$group=new groups($gid);
    	$hash=$group->LoadDatas($gid);
    	if($usr->SAMBA_INSTALLED){
    		$group->TransformGroupToSmbGroup();
    	}
    	
    	
    	$ou=$hash["ou"];
    	$HashPrivieleges=$hash["ArticaGroupPrivileges"];
    	
    	
    	$priv= new usersMenus();
    	
    	
    	$AllowAddGroup=Field_yesno_checkbox('AllowAddGroup',$HashPrivieleges["AllowAddGroup"]);
    	$AllowAddUsers=Field_yesno_checkbox('AllowAddUsers',$HashPrivieleges["AllowAddUsers"]);
    	$AsArticaAdministrator=Field_yesno_checkbox('AsArticaAdministrator',$HashPrivieleges["AsArticaAdministrator"]);
    	$AllowChangeDomains=Field_yesno_checkbox('AllowChangeDomains',$HashPrivieleges["AllowChangeDomains"]);
    	$AsSystemAdministrator=Field_yesno_checkbox('AsSystemAdministrator',$HashPrivieleges["AsSystemAdministrator"]);
    	$AsSambaAdministrator=Field_yesno_checkbox('AsSambaAdministrator',$HashPrivieleges["AsSambaAdministrator"]);
    	$AsDnsAdministrator=Field_yesno_checkbox('AsDnsAdministrator',$HashPrivieleges["AsDnsAdministrator"]);
    	$AsQuarantineAdministrator=Field_yesno_checkbox('AsQuarantineAdministrator',$HashPrivieleges["AsQuarantineAdministrator"]);
    	$AsMailManAdministrator=Field_yesno_checkbox('AsMailManAdministrator',$HashPrivieleges["AsMailManAdministrator"]);
    	$AsOrgStorageAdministrator=Field_yesno_checkbox('AsOrgStorageAdministrator',$HashPrivieleges["AsOrgStorageAdministrator"]);
    	$AllowManageOwnComputers=Field_yesno_checkbox('AllowManageOwnComputers',$HashPrivieleges["AllowManageOwnComputers"]);
    	$AsOrgPostfixAdministrator=Field_yesno_checkbox('AsOrgPostfixAdministrator',$HashPrivieleges["AsOrgPostfixAdministrator"]);
    	$AsDansGuardianGroupRule=Field_yesno_checkbox('AsDansGuardianGroupRule',$HashPrivieleges["AsDansGuardianGroupRule"]);
    	$AsMessagingOrg=Field_yesno_checkbox('AsMessagingOrg',$HashPrivieleges["AsMessagingOrg"]);
    	
    	
    	if($priv->AllowAddUsers==false){
    		$AllowAddUsers="<img src='img/status_critical.gif'>".Field_hidden('AllowAddUsers',$HashPrivieleges["AllowAddUsers"]);
    		$AsDansGuardianGroupRule="<img src='img/status_critical.gif'>".Field_hidden('AsDansGuardianGroupRule',$HashPrivieleges["AsDansGuardianGroupRule"]);
    		$AsMessagingOrg="<img src='img/status_critical.gif'>".Field_hidden('AsMessagingOrg',$HashPrivieleges["AsMessagingOrg"]);
    	
    	}
    	if($priv->AsArticaAdministrator==false){
    		$AsArticaAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsArticaAdministrator',$HashPrivieleges["AsArticaAdministrator"]);
    		$AsSambaAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsSambaAdministrator',$HashPrivieleges["AsSambaAdministrator"]);
    		$AsDnsAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsDnsAdministrator',$HashPrivieleges["AsDnsAdministrator"]);
    		$AsQuarantineAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsQuarantineAdministrator',$HashPrivieleges["AsQuarantineAdministrator"]);
    		$AsOrgStorageAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsOrgStorageAdministrator',$HashPrivieleges["AsOrgStorageAdministrator"]);
    		$AsOrgPostfixAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsOrgPostfixAdministrator',$HashPrivieleges["AsOrgPostfixAdministrator"]);
    		$AsDansGuardianGroupRule="<img src='img/status_critical.gif'>".Field_hidden('AsDansGuardianGroupRule',$HashPrivieleges["AsDansGuardianGroupRule"]);
    		$AsMessagingOrg="<img src='img/status_critical.gif'>".Field_hidden('AsMessagingOrg',$HashPrivieleges["AsMessagingOrg"]);
		}
    		
    		
    	if($priv->AllowAddGroup==false){
    		$AllowAddGroup="<img src='img/status_critical.gif'>".Field_hidden('AllowAddGroup',$HashPrivieleges["AllowAddGroup"]);
    		$AsDansGuardianGroupRule="<img src='img/status_critical.gif'>".Field_hidden('AsDansGuardianGroupRule',$HashPrivieleges["AsDansGuardianGroupRule"]);
    		$AsMessagingOrg="<img src='img/status_critical.gif'>".Field_hidden('AsMessagingOrg',$HashPrivieleges["AsMessagingOrg"]);
    	
    	}
    	if($priv->AllowChangeDomains==false){$AllowChangeDomains="<img src='img/status_critical.gif'>".Field_hidden('AllowChangeDomains',$HashPrivieleges["AllowChangeDomains"]);}
    	if($priv->AsSystemAdministrator==false){$AsSystemAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsSystemAdministrator',$HashPrivieleges["AsSystemAdministrator"]);}
    	if($priv->AsDnsAdministrator==false){$AsDnsAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsDnsAdministrator',$HashPrivieleges["AsDnsAdministrator"]);}
    	if($priv->AsQuarantineAdministrator==false){$AsQuarantineAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsQuarantineAdministrator',$HashPrivieleges["AsQuarantineAdministrator"]);}
		if($priv->AsOrgStorageAdministrator==false){$AsOrgStorageAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsOrgStorageAdministrator',$HashPrivieleges["AsOrgStorageAdministrator"]);}
		if($priv->AsOrgPostfixAdministrator==false){$AsOrgPostfixAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsOrgPostfixAdministrator',$HashPrivieleges["AsOrgPostfixAdministrator"]);}
		if($priv->AsMessagingOrg==false){$AsMessagingOrg="<img src='img/status_critical.gif'>".Field_hidden('AsMessagingOrg',$HashPrivieleges["AsMessagingOrg"]);}
    	
		
		
		
    	
    	
    	
$group_allow="&nbsp;{groups_allow}</H3><br>
		<table style='width:100%' class=table_form>
		
			<tr>
				<td align='right'><strong>{AllowAddUsers}:</td><td>$AllowAddUsers</td>
			</tr>
			<tr>
				<td align='right'><strong>{AsDansGuardianGroupRule}:</td><td>$AsDansGuardianGroupRule</td>
			</tr>			
			
			
		</table>
";  	
    	
$user_allow="&nbsp;{users_allow}</H3><br>
					<table style='width:100%' class=table_form>
						
						<tr>
							<td align='right' nowrap><strong>{AllowChangeAntiSpamSettings}:</td><td>" . Field_yesno_checkbox('AllowChangeAntiSpamSettings',$HashPrivieleges["AllowChangeAntiSpamSettings"]) ."</td>
						</tr>											
						<tr>
							<td align='right' nowrap><strong>{AllowChangeUserPassword}:</td><td>" . Field_yesno_checkbox('AllowChangeUserPassword',$HashPrivieleges["AllowChangeUserPassword"]) ."</td>
						</tr>
						<tr>
							<td align='right' nowrap><strong>{AllowFetchMails}:</td><td>" . Field_yesno_checkbox('AllowFetchMails',$HashPrivieleges["AllowFetchMails"]) ."</td>
						</tr>
						<tr>
							<td align='right' nowrap><strong>{AllowChangeUserKas}:</td><td>" . Field_yesno_checkbox('AllowChangeUserKas',$HashPrivieleges["AllowChangeUserKas"]) ."</td>
						</tr>												
						<tr>
							<td align='right' nowrap><strong>{AllowEditAliases}:</td><td>" . Field_yesno_checkbox('AllowEditAliases',$HashPrivieleges["AllowEditAliases"]) ."</td>
						</tr>
						<tr>
							<td align='right' nowrap><strong>{AllowChangeMailBoxRules}:</td><td>" . Field_yesno_checkbox('AllowChangeMailBoxRules',$HashPrivieleges["AllowChangeMailBoxRules"]) ."</td>
						</tr>						
						<tr>
							<td align='right' nowrap><strong>{AllowSender_canonical}:</td><td>" . Field_yesno_checkbox('AllowSenderCanonical',$HashPrivieleges["AllowSenderCanonical"]) ."</td>
						</tr>
						<tr>
							<td align='right' nowrap><strong>{AllowOpenVPN}:</td><td>" . Field_yesno_checkbox('AllowOpenVPN',$HashPrivieleges["AllowOpenVPN"]) ."</td>
						</tr>
						<tr>
							<td align='right' nowrap><strong>{AllowDansGuardianBanned}:</td><td>" . Field_yesno_checkbox('AllowDansGuardianBanned',$HashPrivieleges["AllowDansGuardianBanned"]) ."</td>
						</tr>
						<tr>
							<td align='right' nowrap><strong>{AllowXapianDownload}:</td><td>" . Field_yesno_checkbox('AllowXapianDownload',$HashPrivieleges["AllowXapianDownload"]) ."</td>
						</tr>																									
						<tr>
							<td align='right' nowrap><strong>{AllowManageOwnComputers}:</td><td>" . Field_yesno_checkbox('AllowManageOwnComputers',$HashPrivieleges["AllowManageOwnComputers"]) ."</td>
						</tr>						
						
						
						<tr>
							<td align='right' nowrap><strong>{AllowEditAsWbl}:</td><td>" . Field_yesno_checkbox('AllowEditAsWbl',$HashPrivieleges["AllowEditAsWbl"]) ."</td>
						</tr>									
					</table>";

$org_allow="&nbsp;{organization_allow}</H3><br>
<table style='width:100%' class=table_form>	
	<tr>
		<td align='right' nowrap><strong>{AllowEditOuSecurity}:</td>
		<td>" . Field_yesno_checkbox('AllowEditOuSecurity',$HashPrivieleges["AllowEditOuSecurity"]) ."</td>
	</tr>
	<tr>
		<td align='right' nowrap><strong>{AsOrgPostfixAdministrator}:</td>
		<td>$AsOrgPostfixAdministrator</td>
	</tr>	
	<tr>
		<td align='right' nowrap><strong>{AsQuarantineAdministrator}:</td>
		<td>$AsQuarantineAdministrator</td>
	</tr>
	<tr>
		<td align='right' nowrap><strong>{AsMailManAdministrator}:</td>
		<td>$AsMailManAdministrator</td>
	</tr>	
	<tr>
		<td align='right' nowrap><strong>{AsOrgStorageAdministrator}:</td>
		<td>$AsOrgStorageAdministrator</td>
	</tr>	
	<tr>
		<td align='right' nowrap><strong>{AsMessagingOrg}:</td>
		<td>$AsMessagingOrg</td>
	</tr>		
	
	
	
	<tr>
		<td align='right'><strong>{AllowChangeDomains}:</td><td>$AllowChangeDomains</td>
	</tr>	
</table>					
";


$admin_allow="&nbsp;{administrators_allow}</H3><br>
<table style='width:100%' class=table_form>
				
						<tr>
							<td align='right' nowrap><strong>{AsPostfixAdministrator}:</td>
							<td>" . Field_yesno_checkbox('AsPostfixAdministrator',$HashPrivieleges["AsPostfixAdministrator"]) ."</td>
						</tr>
						
						
						<tr>
							<td align='right' nowrap><strong>{AsSquidAdministrator}:</td>
							<td>" . Field_yesno_checkbox('AsSquidAdministrator',$HashPrivieleges["AsSquidAdministrator"]) ."</td>
						</tr>

						<tr>
							<td align='right' nowrap><strong>{AsSambaAdministrator}:</td>
							<td>$AsSambaAdministrator</td>
						</tr>						
											
						<tr>
							<td align='right' nowrap><strong>{AsArticaAdministrator}:</td>
							<td>$AsArticaAdministrator</td>
						</tr>						
						<tr>
							<td align='right' nowrap><strong>{AsSystemAdministrator}:</td>
							<td>$AsSystemAdministrator</td>
						</tr>	
						<tr>
							<td align='right' nowrap><strong>{AsDnsAdministrator}:</td>
							<td>$AsDnsAdministrator</td>
						</tr>
												
						<tr>
							<td align='right' nowrap><strong>{AsMailBoxAdministrator}:</td>
							<td>" . Field_yesno_checkbox('AsMailBoxAdministrator',$HashPrivieleges["AsMailBoxAdministrator"]) ."</td>
						</tr>	
						<tr>
							<td align='right' nowrap><strong>{AllowViewStatistics}:</td>
							<td>" . Field_yesno_checkbox('AllowViewStatistics',$HashPrivieleges["AllowViewStatistics"]) ."</td>
						</tr>																					
						</table>";
$sufform=$_GET["tab"];
switch ($_GET["tab"]) {
	case "G":$g=$group_allow;break;
	case "U":$g=$user_allow;break;
	case "A":$g=$admin_allow;break;
	case "O":$g=$org_allow;break;
	default:$g=$user_allow;break;
}
$page=CurrentPageName();
$html="
	$div1
	
	<div style='padding:20px'>
	$tabs
	<form name='{$sufform}_priv'>
		<input type='hidden' name='PrivilegesGroup' value='$gid'><br>
		<H3>{group}: &laquo;{$hash["cn"]}&raquo;
		$g
		
		</form>
		<div style='text-align:right;'>". button("{submit}","EditGroupPrivileges()")."</div>

		</div>$div2

		<script>
		function EditGroupPrivileges(){
			ParseForm('{$sufform}_priv','$page',true);
			if(document.getElementById('groupprivileges')){document.getElementById('groupprivileges').innerHTML='';}
		}
		</script>
		
		";
    	
	$tpl=new templates();
    	return $tpl->_ENGINE_parse_body($html);
}
function EditGroup(){
	$gid=$_GET["PrivilegesGroup"];

	$ldap=new clladp();
	$Hash=$ldap->GroupDatas($gid);
	$ArticaGroupPrivileges=$Hash["ArticaGroupPrivileges"];
	if(is_array($ArticaGroupPrivileges)){while (list ($num, $val) = each ($ArticaGroupPrivileges) ){$GroupPrivilege[$num]=$val;}}
	while (list ($num, $val) = each ($_GET) ){$GroupPrivilege[$num]=$val;}		
	while (list ($num, $val) = each ($GroupPrivilege) ){$values=$values . "[$num]=\"$val\"\n";}	

	$update_array["ArticaGroupPrivileges"][0]=$values;
	$ldap->Ldap_modify($Hash["dn"],$update_array);
	if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;}
		
	
	
}
function DeleteMember(){
	$usermenu=new usersMenus();
	$tpl=new templates();
	if($usermenu->AllowAddUsers==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}		
	$ldap=new clladp();
	$Userdatas=$ldap->UserDatas($_GET["DeleteMember"]);
	$dn=$Userdatas["dn"];
	$ldap->ldap_delete($dn,false);
	if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;exit;}	
	}
function DeleteGroup(){
	
	if(isset($_GET["DeleteGroup"])){$gpid=$_GET["DeleteGroup"];}
	if(isset($_GET["delgroup"])){$gpid=$_GET["delgroup"];}
	$ou=$_GET["ou"];
	
	$ldap=new clladp();
	$tpl=new templates();
	$classGroup=new groups($gpid);
	$hashgroup=$ldap->GroupDatas($gpid);
	$default_dn_nogroup="cn=nogroup,ou=groups,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($default_dn_nogroup)){$ldap->AddGroup("nogroup",$ou);}
	$nogroup_id=$ldap->GroupIDFromName($ou,"nogroup");	
	
	if(is_array($hashgroup["members"])){
		while (list ($num, $val) = each ($hashgroup["members"]) ){
			$ldap->AddUserToGroup($nogroup_id,$num);
		}
	}
	
	$users=new usersMenus();
	if($users->KAV_MILTER_INSTALLED){
		$sock=new sockets();
		$sock->getfile("KavMilterDeleteRule:$classGroup->groupName.$classGroup->ou");
	}
	
	
	$kas_dn="cn=$gpid,cn=kaspersky Antispam 3 rules,cn=artica,$ldap->suffix";
	if($ldap->ExistsDN($kas_dn)){$ldap->ldap_delete($kas_dn,false);}
	$ldap->ldap_delete($hashgroup["dn"],false);
	

	
	if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;}else{echo $tpl->_ENGINE_parse_body('{success}');}
	}	
	
function browser(){
	$html="
	<input type='hidden' id='YahooSelectedFolders_ask' value='{YahooSelectedFolders_ask}'>
	<div id='folderTree'>
	</div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	

	
function COMPUTERS_LIST(){
	$gpid=$_GET["gpid"];
	$computer_list=COMPUTERS_LIST_LIST();
	$js=MEMBER_JS('NewComputer$',1);
	$html="<br>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=60% style='padding:5px'>$computer_list</td>
		<td valign='top'>". RoundedLightGrey(Paragraphe("computer-search-add-64.png","{find_computer}","{addfind_computer_text}","javascript:addComputer($gpid)")).
	"<br>".RoundedLightGrey(Paragraphe("computer-64-add.png","{add_computer}","{add_computer_text}","javascript:YahooUser(670,\"domains.edit.user.php?userid=newcomputer$&ajaxmode=yes&gpid=$gpid\",\"windows: New {add_computer}\");"))."</td>
	</tr>	
	</table>
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}

function COMPUTERS_LIST_LIST(){
	$gpid=$_GET["gpid"];
	$group=new groups($gpid);
	$html="<table style='width:100%'>";
	
	while (list ($num, $val) = each ($group->computers_array) ){
		$js=MEMBER_JS($val,1);
		$html=$html.
		"<tr " . CellRollOver().">
			<td width=1%><img src='img/base.gif'></td>
			<td><strong>". texttooltip($val,'{view}',$js)."</td>
		</tr>";
		
	
	}	

	$html=$html  . "</table>";
	return RoundedLightGrey($html);
	
}

function COMPUTER_FORM_ADD(){
$gpid=$_GET["gpid"];
	$html="
	<input type='hidden' id='gpid' value='$gpid'>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1% nowrap><strong>{find_computer}:</strong></td>
		<td valign='top'>".Field_text('find_computer',null,'width:100%')."</td>
		<td valign='top' width=1%><input type='button' value='{search}&nbsp;&raquo;' OnClick=\"javascrit:find_computer($gpid);\" style='margin:0px'></td>
		</tr>
	<tr>
	<td colspan=3  valign='top'><div id='computer_to_find'></div></td>
	</tr>
	</table>	";
	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function COMPUTER_FIND(){
$gpid=$_GET["gpid"];
$ou=$_GET["ou"];
$tofind=$_GET["find_computer"];
$ldap=new clladp();
if($tofind==null){$tofind='*';}else{$tofind="*$tofind*";}
$filter="(&(objectClass=ArticaComputerInfos)(|(cn=$tofind)(ComputerIP=$tofind)(uid=$tofind))(gecos=computer))";

$attrs=array("uid","ComputerIP","ComputerOS");
$dn="dc=samba,$ldap->suffix";
$html="
<input type='hidden' id='add_computer_confirm' value='{add_computer_confirm}'>
<table style='width:100%'>";

$hash=$ldap->Ldap_search($dn,$filter,$attrs);
for($i=0;$i<$hash["count"];$i++){
	$realuid=$hash[$i]["uid"][0];
	$hash[$i]["uid"][0]=str_replace('$','',$hash[$i]["uid"][0]);
	$html=$html . 
	"<tr " .CellRollOver().">
	<td width=1%><img src='img/base.gif'></td>
	<td><strong>{$hash[$i]["uid"][0]}</strong></td>
	<td><strong>{$hash[$i][strtolower("ComputerIP")][0]}</strong></td>
	<td><strong>{$hash[$i][strtolower("ComputerOS")][0]}</strong></td>
	<td width=1%>"  . imgtootltip('plus-16.png','{add_computer}',"javascript:add_computer_selected('$gpid','{$hash[$i]["dn"]}','{$hash[$i]["uid"][0]}','$realuid')")."</td>
	</tr>
	";
	}
$html=$html . "</table>";
$html=RoundedLightGrey($html);
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);				
	
}

function COMPUTER_ADD_TO_GROUP(){
	$dn=$_GET["add_computer_to_group"];
	$gpid=$_GET["gpid"];
	$uid=$_GET["uid"];
	writelogs("Adding $dn in group $gpid");
	$group=new groups($gpid);
	$group->AddUsertoThisGroup($uid);
	}
	
function GROUP_SAMBA_SETTINGS(){
	$page=CurrentPageName();
	$group=new groups($_GET["gpid"]);
	if($group->sambaSID==null){
		$text="{not_group_samba}";
	}else{$text="{yes_group_samba}";}
	
	$array_group=array(null=>"{select}",5=>"{smbg_typ2}",2=>"{smbg_typ}");
	
$html="<H5>{MK_SAMBA_GROUP}</H5>
<form name='FFM1GPP'>
<input type='hidden' name='gpid' id='gpid' value='{$_GET["gpid"]}'>
<input type='hidden' name='ou' id='ou' value='{$_GET["ou"]}'>
<input type='hidden' name='SaveGroupSamba' id='SaveGroupSamba' value='yes'>
<p class=caption>$text</p>
<table style='width:100%'>
<tr>
	<td align='right'><strong>{sambaGroupType}</strong>:</td>
	<td>".Field_array_Hash($array_group,'sambaGroupType',$group->sambaGroupType)."</td>
</tr>
<tr>
	<td align='right'><strong>{sambaSID}</strong>:</td>
	<td><strong>$group->sambaSID</strong></td>
</tr>
<tr>
	<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ParseForm('FFM1GPP','$page',true);\" value='{edit}&nbsp;&raquo;'></td>
</tr>
</table>
</form>

";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}
function GROUP_SAMBA_SETTINGS_SAVE(){
	$gpid=$_GET["gpid"];
	$group=new groups($gpid);
	$group->sambaGroupType=$_GET["sambaGroupType"];
	$group->EditAsSamba();
	}
	
function MEMBERS_DELETE(){
	$uid=$_GET["DeleteUserByUID"];
	$user=new user($uid);
	$user->DeleteUser();
	}

?>	
