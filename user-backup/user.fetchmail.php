<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.functions.inc");
include_once(dirname(__FILE__)."/ressources/class.fetchmail.inc");
	
if(!isset($_SESSION["uid"])){header("Location: session-fermee.php");die();}
if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["edit-rule"])){form_rule();exit;}
if(isset($_GET["poll"])){saverule();exit;}
if(isset($_GET["rule_delete"])){rule_delete();exit;}

js();

function js(){
	
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_FETCHMAIL}');
	$title2=$tpl->_ENGINE_parse_body('{fetchmail_rules}');
	
	
	$page=CurrentPageName();
	
	$html="
	var fetchid='';
	
	
	function FetchStart(){
		YahooWin(650,'$page?popup=yes','$title');
		}
		
	function EditRuleFetchmail(index){
		fetchid=index;
		YahooWin2(450,'$page?edit-rule='+index,'$title2');
		}		
		
		
var x_SaveFetchmailRule= function (obj) {
	var results=obj.responseText;
	if (results.length>0){alert(results);}
	
	FetchStart();
	if(fetchid.length==0){
		$('#dialog2').dialog( 'destroy' );
		return;
	}
	FetchStart();
	}

var x_DeleteFetchmail= function (obj) {
	var results=obj.responseText;
	$('#dialog1').dialog( 'destroy' );
	FetchStart();
	}		

		
	function SaveFetchmailRule(ruleid){
		var XHR = new XHRConnection();
		XHR.appendData('poll',document.getElementById('poll').value);
		XHR.appendData('user',document.getElementById('user').value);
		XHR.appendData('pass',document.getElementById('pass').value);
		XHR.appendData('proto',document.getElementById('proto').value);
		XHR.appendData('rule_number',ruleid);
		if(document.getElementById('ssl').checked){XHR.appendData('ssl',1);}
		if(document.getElementById('smtp_host')){
			XHR.appendData('smtp_host',document.getElementById('smtp_host').value);
		}
	
		XHR.sendAndLoad('$page', 'GET',x_SaveFetchmailRule);	
	}	

	function DeleteUserSenderSettings(){
		var XHR = new XHRConnection();
		XHR.appendData('sasl_username_delete','yes');
		document.getElementById('sasltransport').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveSenderCanonicalNew);			
	}
	
	function DeleteFetchmail(num){
		var XHR = new XHRConnection();
		XHR.appendData('rule_delete',num);
		XHR.sendAndLoad('$page', 'GET',x_DeleteFetchmail);	
	}
		
	FetchStart();
	
	
	
	
	";
	
	echo $html;
	
}

function popup(){
	
	
	$add=iconTable("64-plus.png",'{add_new_fetchmail_rule}','{add_new_fetchmail_rule_text}',"EditRuleFetchmail('');");
	
	$users=new usersMenus();
	$AllowFetchMails=$users->AllowFetchMails;

	if(!$AllowFetchMails){
		$add=iconTable("64-plus.png",'{add_new_fetchmail_rule}','{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}',"blur()");
	}
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("
	<table class=table_form>
	<tr>
	<td valign='top'>
		<div id='fetch_rules'>".ruleslist()."</div>
	</td>
	<td valign='top'>$add
	</td>
	</tr>
	</table>
	
	");
	
	
		
}

function form_rule(){
	$rule_id=$_GET["edit-rule"];
	$user=new user($_SESSION["uid"]);
	$ligne=$user->fetchmail_rules[$rule_id];
	$f=new Fetchmail_settings();
	$sock=new sockets();
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	
	
	
	
	$array=$f->LoadRule($rule_id);
	
	$proto=array(""=>"{select}",
				"auto"=>"AUTO IMAP, POP3",
				"pop3"=>"POP3",
				"imap"=>"IMAP",
				"hotmail"=>"Get Live Hotmail (@hotmail.x/@live.x)");

	$proto=Field_array_Hash($proto,'proto',$array["proto"],"",null,0,'width:180px');
	
	if($array["ssl"]){$ssl=1;}else{$ssl=0;}
	
	
	$sslcheck=Field_checkbox("ssl",1,$ssl);
	
	if($EnablePostfixMultiInstance==1){
		$smtp_sender=
		"<tr>
			<td valign='top' class=legend nowrap>{local_smtp_host}:</td>
			<td valign='top'>".	Field_array_Hash(fetchmail_PostFixMultipleInstanceList($user->ou),"smtp_host",$array["smtp_host"])."</td>
		</tr>";
	}
	
	
	
	$table="
	<div id='mypool'>
	<table class=table_form>
	<tr>
		<td class=legend>{server_type}:</td>
		<td>$proto</td>
	</tr>
	<tr>
		<td class=legend>{server_name}:</td>
		<td>" . Field_text('poll',$array["poll"],'width:70%')."</td>
	</tr>
	$smtp_sender
	<tr>
		<td class=legend>ssl:</td>
		<td>$sslcheck</td>
	</tr>	
	<tr>
		<td align='right' class=legend>{remoteuser}</strong>:&nbsp;</td>
		<td align='left'>" . Field_text('user',$array["user"],'width:70%')."</td>
	</tr>	
	<tr>
		<td align='right' class=legend>{password}</strong>:&nbsp;</td>
		<td align='left'>" . Field_password('pass',$array["pass"],'width:70%')."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{edit}","SaveFetchmailRule('$rule_id')")."</td>
	</tr>	
	</table>
	</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($table);	

}


function ruleslist(){
	$user=new user($_SESSION["uid"]);
	$html="<table class=table_inside>
	<tr>
		<th>{server}</th>
		<th colspan=2>{protocol}</th>
	</tr>
	";

	$f=new Fetchmail_settings();
	
	$fetchmail_rules=$f->LoadUsersRules($_SESSION["uid"]);
	
	
	
	$users=new usersMenus();
	$AllowFetchMails=$users->AllowFetchMails;
	
		
	
	
	while (list ($num, $line) = each ($fetchmail_rules) ){
		$server=$line["poll"];
		$proto=$line["proto"];
		$user=$line["user"];
		$delete=imgtootltip("ed_delete.gif","{delete}","DeleteFetchmail($num)");
		$edit="EditRuleFetchmail($num)";
		if(!$AllowFetchMails){$delete="&nbsp;";$edit=null;}
		$edit=CellRollOver($edit);
		$html=$html."<tr>
		<td nowrap $edit>$server<hr style='margin:0px'><i style='font-size:10px;font-weight:normal'>$user</i></td>
		<td $edit>$proto</td>
		<td >$delete</td>
		</tr>

		"
		;
		
	}
	
	$html=$html."</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}

function saverule(){
	
	$uid=$_SESSION["uid"];
	$_GET["uid"]=$_SESSION["uid"];
	writelogs("local user is :\"{$_GET["is"]}\"",__FUNCTION__,__FILE__);
	$tpl=new templates();
	$ldap=new clladp();
	$user=new user($uid);
	
		if($user->dn==null){
			echo $tpl->_ENGINE_parse_body("\"$uid\"\n{doesntexists}");
			exit;
			}	
			
	$fr=new Fetchmail_settings();
	$_GET["is"]=$user->mail;
	$fr->EditRule($_GET,$_GET["rule_number"]);
	
}

function rule_delete(){
	$user=new user($_SESSION["uid"]);
	$fr=new Fetchmail_settings();
	$fr->DeleteRule($_GET["rule_delete"],$_SESSION["uid"]);
}
function fetchmail_PostFixMultipleInstanceList($ou){
	$sock=new sockets();
	$uiid=$sock->uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));	
	$q=new mysql();
	$sql="SELECT `value`,`ip_address` FROM postfix_multi WHERE `key`='myhostname' AND `ou`='$ou' AND `uuid`='$uiid'";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){writelogs("$q->mysql_error\n$sql",__FUNCTION__,__FILE__,__LINE__);}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$array[$ligne["value"]]=$ligne["value"];
	}
	$array[null]="{select}";
	return $array;
}


?>