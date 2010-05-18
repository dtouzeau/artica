<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.pure-ftpd.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/charts.php');
	include_once('ressources/class.mimedefang.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.contacts.inc');
	
	
	if( isset($_POST['upload']) ){PhotoUploaded();}
	
	$usersmenus=new usersMenus();
		if($usersmenus->AllowAddGroup==false){
			
		if(isset($_GET["uid"])){
			if($_SESSION["uid"]<>$_GET["uid"]){die('No permissions');}
		}
		}
		
			
		
			
		
$hidden=null;	
	
	
if(isset($_GET["uid"])){
	$user=new user($_GET["uid"]);
	$hidden="<input type='hidden' name='uid' value='{$_GET["uid"]}'>";		
}

if(isset($_GET["employeeNumber"])){
	$user=new contacts($_SESSION["uid"],$_GET["employeeNumber"]);	
	$hidden="<input type='hidden' name='employeeNumber' value='{$_GET["employeeNumber"]}'>";	
}

if($hidden==null){die("No index");}
	
	

$html="<p>&nbsp;</p>
<div id='content' style='width:400px'>
<table style='width:100%'>
<tr>
<td valign='top'>
<h3>{edit_photo_title}</h3>
<p>{edit_photo_text}</p>
<div style='color:red'>{$_GET["Photo_error"]}</div>
<div style='font-size:11px'><code>$error</code></div>
<form method=\"post\" enctype=\"multipart/form-data\" action=\"$page\">
$hidden
<p>
<input type=\"file\" name=\"photo\" size=\"30\">
<input type='submit' name='upload' value='{upload file}&nbsp;&raquo;' style='width:90px'>
</p>
</form>
</td>
<td valign='top'><img src='$user->img_identity'>
</td>
</div>

";	
$tpl=new template_users('{edit_photo_title}',$html,0,1,1);
echo $tpl->web_page;
	
	
function PhotoUploaded(){
	$tmp_file = $_FILES['photo']['tmp_name'];
	$content_dir=dirname(__FILE__)."/ressources/conf/upload";
	if(!is_dir($content_dir)){@mkdir($content_dir);}
	if( !@is_uploaded_file($tmp_file) ){
		$_GET["Photo_error"]='{error_unable_to_upload_file} '.$tmp_file;
		exit;
	}
	$name_file = $_FILES['photo']['name'];

if(file_exists( $content_dir . "/" .$name_file)){@unlink( $content_dir . "/" .$name_file);}
 if( !move_uploaded_file($tmp_file, $content_dir . "/" .$name_file) ){
 	$_GET["Photo_error"]="{error_unable_to_move_file} : ". $content_dir . "/" .$name_file;exit();}
     
    $file=$content_dir . "/" .$name_file;
    
    
    if(isset($_POST["uid"])){
		$_GET["uid"]=$_POST["uid"];
		$user=new user($_POST["uid"]);
		$user->jpegPhoto_datas=file_get_contents($file); 
		$user->add_user(); 
    	if(is_file($user->thumbnail_path)){unlink($user->thumbnail_path);}  
    	return null;	
    }
    
    if(isset($_POST["employeeNumber"])){
		$_GET["employeeNumber"]=$_POST["employeeNumber"];
		$user=new contacts($_SESSION["uid"],$_POST["employeeNumber"]);
		$user->jpegPhoto_datas=file_get_contents($file); 
		
		if($_SESSION["uid"]<>-100){
				$ldap=new clladp();
				$user2=new user($_SESSION["uid"]);
				$dn="cn=$user->sn $user->givenName,ou=$user2->uid,ou=People,dc=$user2->ou,dc=NAB,$ldap->suffix";
				if($dn==$user->dn){
					$user->Save();
				}else{
					$tpl=new templates();
					echo $tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
				}
				
			}			
		
		
   	if(is_file($user->thumbnail_path)){unlink($user->thumbnail_path);}  
    return null;
    }   


   

}

?>	
	