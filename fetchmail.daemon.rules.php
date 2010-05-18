<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.fetchmail.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){header('location:users.index.php');exit;}

if(isset($_GET["Showlist"])){echo section_rules_list();exit;}

if(isset($_GET["ajax"])){ajax_index();exit;}

section_Fetchmail_Daemon();



function section_Fetchmail_Daemon(){
		
		$yum=new usersMenus();
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$sock=new sockets();		
		$title="{fetchmail_rules}";
		
		$add_fetchmail=Paragraphe('add-fetchmail-64.png','{add_new_fetchmail_rule}','{fetchmail_explain}',"javascript:add_fetchmail_rules()",null,340);

	$ini->loadString($sock->getfile('fetchmailstatus'));
	$status=DAEMON_STATUS_ROUND("FETCHMAIL",$ini,null);
	$status=$tpl->_ENGINE_parse_body($status);	
		$html="<table style='width:600px'>
		<tr>
		<td valign='top' width=1%><img src='img/bg_fetchmail2.jpg'>
		<td valign='top' align='right'><div style='width:350px'>$status <br> $add_fetchmail</div></td>
		</tr>
		<td colspan=2>
		<div id='fetchmail_daemon_rules'></div>
			</td>
			</tr>			
					</table>
					
	<script>LoadAjax('fetchmail_daemon_rules','fetchmail.daemon.rules.php?Showlist=yes');</script>";
				
$cfg["LANG_FILE"][]="artica.wizard.fetchmail.php";		
$cfg["LANG_FILE"][]="user.fetchmail.index.php";
$tpl=new template_users($title,$html,0,0,0,0,$cfg);
echo $tpl->web_page;		
	
	}
	
	
function ajax_index(){
	$html="
	<H1>{fetchmail_rules}</H1>
	<br>
	<div id='fetchmail_daemon_rules'>
	".section_rules_list()."
	</div>
	
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
	
	
function section_rules_list(){
	
	if($_GET["tab"]==1){section_config();exit;}
	
	$fetch=new Fetchmail_settings();
	$rules=$fetch->LoadAllRules();
	
	
	$html=section_tabs()."<br><H5>{fetchmail_rules}</H5>";
$rd="<table style='width:1OO%' class=table_form>";
	
while (list ($num, $hash) = each ($rules) ){
		
		$uid=$hash["uid"];
		$user=new user($uid);
		if($hash["enabled"]==0){$img='status_ok-grey.gif';}else{$img="status_ok.gif";}
		$link=CellRollOver("UserFetchMailRule('$num','$uid')",'{edit}');
		$rd=$rd . "<tr $link>
		<td width=1% valign='top'><img src='img/$img'></td>
		<td valign='top'><strong>{$user->mail}</strong></td>
		<td ><strong>{$hash["poll"]}</strong></td>
		<td ><strong>{$hash["proto"]}</strong></td>
		<td ><strong>{$hash["user"]}</strong></td>
		
		</tr>";
		
		
		
		
		
		
	}
	$rd=$rd . "</table>";
	$rd="<div style='width:100%;height:400px;overflow:auto'>$rd</div>";
	
  $tpl=new templates();
  if(isset($_GET["ajax"])){
  	return $tpl->_ENGINE_parse_body($html . $rd);
  }
	echo $tpl->_ENGINE_parse_body($html . $rd);
	
	
}


function section_config(){
	
	$fetch=new fetchmail();
	if(isset($_GET["build"])){
		
		$fetch->Save();
		$fetch=new fetchmail();
	}
	
	$fetchmailrc=$fetch->fetchmailrc;
	$FetchGetLive=$fetch->FetchGetLive;
	$save=Paragraphe('disk-save-64.png','{generate_config}','{generate_config_text}',"javascript:LoadAjax(\"fetchmail_daemon_rules\",\"fetchmail.daemon.rules.php?Showlist=yes&tab=1&build=yes\")");
	
	$fetchmailrc=htmlentities($fetchmailrc);
	$fetchmailrc=nl2br($fetchmailrc);
	
	$FetchGetLive=htmlentities($FetchGetLive);
	$FetchGetLive=nl2br($FetchGetLive);	
	
	$tpl=new templates();
	$html=section_tabs() ."<br><H5>{see_config}</H5><br>
	<table style='width:100%'>
	<tr>
	<td width=75% valign='top'>" . RoundedLightGreen("<code>$fetchmailrc</code>")  ."<br>" . RoundedLightGreen("<code>$FetchGetLive</code>")  . "</td>
	<td valign='top'>$save<br>" . applysettings("fetch") . "</td>
	</tr>
	</table>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}



function section_tabs(){
	if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	$page=CurrentPageName();
	$array[]='{fetchmail_rules}';
	$array[]='{see_config}';
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('fetchmail_daemon_rules','$page?Showlist=yes&section=yes&tab=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}  
	