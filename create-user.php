<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.pure-ftpd.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/charts.php');
	include_once('ressources/class.mimedefang.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.ini.inc');	
	

	if(isset($_GET["form"])){formulaire();exit;}
	if(isset($_GET["ch-groupid"])){groups_selected();exit;}
	if(isset($_GET["ch-domain"])){domain_selected();exit;}
	if(isset($_GET["password"])){save();exit;}
	
	js();


$users=new usersMenus();
if(!$users->AllowAddUsers){die("alert('not allowed');");}
	
function js(){
$tpl=new templates();
$page=CurrentPageName();

$title=$tpl->_ENGINE_parse_body('{add user explain}');
$html="
var x_serid='';

function OpenAddUser(){
	YahooWin5('500','$page?form=yes','$title');
}

var x_ChangeFormValues= function (obj) {
	var tempvalue=obj.responseText;
	var internet_domain='';
	var ou=document.getElementById('organization').value;
	document.getElementById('select_groups').innerHTML=tempvalue;
	if(document.getElementById('internet_domain')){internet_domain=document.getElementById('internet_domain').value;}
  	 var XHR = new XHRConnection();
     	XHR.appendData('ou',ou);
		XHR.appendData('ch-domain',internet_domain);     	
        XHR.sendAndLoad('$page', 'GET',x_ChangeFormValues2);		
}


var x_SaveAddUser= function (obj) {
var tempvalue=obj.responseText;
if(tempvalue.length>0){
	alert(tempvalue);
	document.getElementById('ffform').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/identity-add-96.png'></center></div>\";  
	return false;
}
YAHOO.example.container.dialog5.hide();
YahooWin(740,'domains.edit.user.php?userid='+x_serid+'&ajaxmode=yes','windows: '+x_serid);

}

function SaveAddUser(){
  var gpid='';
  var internet_domain='';
  var ou=document.getElementById('organization').value;
  var email=document.getElementById('email').value;
  var firstname=document.getElementById('firstname').value;
  var lastname=document.getElementById('lastname').value;  
  var login=document.getElementById('login').value;
  var password=document.getElementById('password').value;
  x_serid=login;
  if(document.getElementById('groupid')){gpid=document.getElementById('groupid').value;}
  if(document.getElementById('internet_domain')){internet_domain=document.getElementById('internet_domain').value;}
  var EnableVirtualDomainsInMailBoxes=domain=document.getElementById('EnableVirtualDomainsInMailBoxes').value;
  if(EnableVirtualDomainsInMailBoxes==1){x_serid=email+'@'+internet_domain;}

  	 var XHR = new XHRConnection();
     XHR.appendData('ou',ou);
     XHR.appendData('internet_domain',internet_domain);
	 XHR.appendData('email',email);
     XHR.appendData('firstname',firstname);
     XHR.appendData('lastname',lastname);
     XHR.appendData('login',login);
     XHR.appendData('password',password);
     XHR.appendData('gpid',gpid);     
     document.getElementById('ffform').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait_verybig.gif'></center></div>\";                                  		      	
     XHR.sendAndLoad('$page', 'GET',x_SaveAddUser);		  
}



var x_ChangeFormValues2= function (obj) {
	var tempvalue=obj.responseText;
	var domain='';
	var email='';
	var login='';
	var ou=document.getElementById('organization').value;
	document.getElementById('select_domain').innerHTML=tempvalue;
	
	email=document.getElementById('email').value;
	login=document.getElementById('login').value;
	if(login.length==0){
		if(email.length>0){
			document.getElementById('login').value=email;
		}
	}
		
}
	

function ChangeFormValues(){
  var gpid='';
  var ou=document.getElementById('organization').value;

  if(document.getElementById('groupid')){gpid=document.getElementById('groupid').value;}
  		var XHR = new XHRConnection();
        XHR.appendData('ch-groupid',gpid);
        XHR.appendData('ou',ou);
        XHR.sendAndLoad('$page', 'GET',x_ChangeFormValues);	

}



OpenAddUser();";
echo $html;
}

function groups_selected(){
	$ldap=new clladp();
	$hash_groups=$ldap->hash_groups($_GET["ou"],1);
	$groups=Field_array_Hash($hash_groups,'groupid',$_GET["ch-groupid"]);
	echo $groups;
	
}

function domain_selected(){
		$ldap=new clladp();
	$hash_domains=$ldap->hash_get_domains_ou($_GET["ou"]);
	$domains=Field_array_Hash($hash_domains,'internet_domain',$_GET["ch-domain"]);
	echo $domains;
	
}

function formulaire(){
	$users=new usersMenus();
	$ldap=new clladp();
$tpl=new templates();
$page=CurrentPageName();	
	if($users->AsAnAdministratorGeneric){
		$hash=$ldap->hash_get_ou(false);
	}else{
		$hash=$ldap->Hash_Get_ou_from_users($_SESSION["uid"],1);
		
	}
	
	if(count($hash)==1){$org=$hash[0];
	$hash_groups=$ldap->hash_groups($org,1);
	$hash_domains=$ldap->hash_get_domains_ou($org);
	$groups=Field_array_Hash($hash_groups,'groupid');
	$domains=Field_array_Hash($hash_domains,'domain');
	}
	
	
	$artica=new artica_general();
	$EnableVirtualDomainsInMailBoxes=$artica->EnableVirtualDomainsInMailBoxes;	
	
	
	while (list ($num, $ligne) = each ($hash) ){
		$ous[$ligne]=$ligne;
	}
	
	
	
	
		$ou=Field_array_Hash($ous,'organization',null,"ChangeFormValues()");
	$form="
	
	<input type='hidden' id='EnableVirtualDomainsInMailBoxes' value='$EnableVirtualDomainsInMailBoxes'>
	<table style='width:100%'>
		<tr>
			<td class=legend>{organization}:</td>
			<td>$ou</td>
		</tr>
		<tr>
			<td class=legend>{group}:</td>
			<td><span id='select_groups'>$groups</span>
		</tr>
		<tr>
		<tr>
			<td class=legend>{firstname}:</td>
			<td>" . Field_text('firstname',null,'width:120px',null,'ChangeFormValues()')."</td>
		</tr>		
		<tr>
			<td class=legend>{lastname}:</td>
			<td>" . Field_text('lastname',null,'width:120px',null,"ChangeFormValues()")."</td>
		</tr>		
			
		<tr>
			<td class=legend>{email}:</td>
			<td>" . Field_text('email',null,'width:120px',null,"ChangeFormValues()")."@<span id='select_domain'>$domains</span></td>
		</tr>
		<tr>
			<td class=legend>{login}:</td>
			<td>" . Field_text('login',null,'width:120px')."</td>
		</tr>
		<tr>
			<td class=legend>{password}:</td>
			<td>" .Field_password('password')."</td>
		</tr>	
		<tr><td colspan=2><hr></td></tr>
		<tr>
			<td colspan=2 align='right'>
				<input type='button' OnClick=\"javascript:SaveAddUser();\" value='{add}&nbsp;&raquo;'>
			</td>
		</tr>
		
		</table>
	";
			
			
	
	$form=RoundedLightWhite($form);

	$html="<h1>{add_user}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><div id='ffform'><img src='img/identity-add-96.png'></div></td>
		<td valign='top'>$form</div></td>
	</tr>
	</table>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function save(){
	$tpl=new templates();     
     $users=new user($_GET["login"]);
     if($users->password<>null){
     	writelogs("User already exists {$_GET["login"]} ",__FUNCTION__,__FILE__);
     	echo($tpl->_ENGINE_parse_body('{account_already_exists}'));
     	exit;
     }
     
     writelogs("Add new user {$_GET["login"]} {$_GET["ou"]} {$_GET["gpid"]}",__FUNCTION__,__FILE__);
     $users->ou=$_GET["ou"];
     $users->password=$_GET["password"];
     $users->mail="{$_GET["email"]}@{$_GET["internet_domain"]}";    
     $users->DisplayName="{$_GET["firstname"]} {$_GET["lastname"]}";
     $users->givenName=$_GET["firstname"];
     $users->sn=$_GET["lastname"];
     $users->group_id=$_GET["gpid"];
	 $users->add_user();
    
}


?>