<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");

if($_SESSION["uid"]<>null){
	header("Location: user.php"); 
	die();
}

if(isset($_GET["credentials"])){
	$array=unserialize(base64_decode($_GET["credentials"]));
	$_POST["uid"]=$array["USERNAME"];
	$_POST["password"]=$array["PASSWORD"];
}

if(isset($_POST["uid"])){
	$error=login();
	if($error==null){
		$_SESSION["uid"]=$_POST["uid"];
		header("Location: user.php"); 
		die();
	}

}





$login=" <div class=\"login-box\">
		<form name='FFM1' method=POST><span style='color:red'>$error</span>
	    	<div align=\"left\">{username}: <br /></div>
	    	<div><input name=\"uid\" type=\"text\" class=\"input-login\" /></div>
			<div align=\"left\" style=\"padding:5px 0px;\">{password}: <br /></div>
			<div><input name=\"password\" type=\"password\" class=\"input-login\" />
			<center style='padding:10px'><input type='submit' value='{logon}&nbsp;&raquo;'>
		</form>
	  </div>
";

$html="
<div style='width:100%;height:423px;background-image:url(img/artica4.jpg);'>
<center><br>$login</center></div>
</div>

";

$tpl=new templates($html);
echo $tpl->buildPage();

function login(){
	$tpl=new templates();
	$ldap=new clladp();
	$att=array("userPassword","DisplayName");
$sr =@ldap_search($ldap->ldap_connection,"dc=organizations,$ldap->suffix","(uid={$_POST["uid"]})",$att);
		if(!$sr){
			echo $sr;
			return $tpl->_ENGINE_parse_body('{unknown_user}');
			
		}
		
		$entry_id = ldap_first_entry($ldap->ldap_connection,$sr);
		if(!$entry_id){
			writelogs( "INFOS: bad value $entry_id: (' . $entry_id . ')  find: (uid={$_POST["uid"]}) -> aborting function search engine doesn`t found the pattern",__LINE__,__FILE__);
			return $tpl->_ENGINE_parse_body('{unknown_user}');
		}
		$attrs = ldap_get_attributes($ldap->ldap_connection, $entry_id);	
		$passw=$attrs["userPassword"][0];
		
		$passw=md5($passw);
		if(!$_GET["credentials"]){$_POST["password"]=md5($_POST["password"]);}
			
		
		
		if($passw<>$_POST["password"]){
			return $tpl->_ENGINE_parse_body('{bad_password}');
		}
		unset($_SESSION["MLDONKEY_{$_POST["uid"]}"]);
		$_SESSION["NOM"]=$attrs["DisplayName"][0];
		$privs=$ldap->_Get_privileges_userid($_POST["uid"]);
		$_SESSION["privileges"]["ArticaGroupPrivileges"]=$privs;
		$users=new usersMenus();
		$uid_class=new user($_POST["uid"]);
		$_SESSION["ou"]=$uid_class->ou;
		$_SESSION["privs"]=$users->_ParsePrivieleges($privs);
		if($_SESSION["privs"]["ForceLanguageUsers"]<>null){
			$_COOKIE["ArticaForceLanguageUsers"]=$_SESSION["privs"]["ForceLanguageUsers"];
		}else{
			unset($_COOKIE["ArticaForceLanguageUsers"]);
		}

}






?>