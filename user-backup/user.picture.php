<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.functions.inc");
if(!isset($_SESSION["uid"])){header("Location: session-fermee.php");die();}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["iframe"])){iframe();exit;}
if( isset($_POST['upload']) ){PhotoUploaded();}
js();

function js(){
	
	$user=new user($_SESSION["uid"]);
	$page=CurrentPageName();
	$prefix=str_replace('.',"_",$page);
	$tpl=new templates();
	$edit_photo_title=$tpl->_ENGINE_parse_body("{edit_photo_title}","edit.thumbnail.php");
	
	$html="
	function {$prefix}Load(){
		YahooWin(500,'$page?popup=yes','$edit_photo_title');
	
	}
	
var x_EditProfile= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				$('#dialog').dialog('close');
				ReloadIdentity();
			}	
	
	function EditProfile(){
		var XHR = new XHRConnection();
		XHR.appendData('DisplayName',document.getElementById('DisplayName').value);
		XHR.appendData('sn',document.getElementById('sn').value);
		XHR.appendData('givenName',document.getElementById('givenName').value);
		XHR.appendData('telephoneNumber',document.getElementById('telephoneNumber').value);
		XHR.appendData('mobile',document.getElementById('mobile').value);
		document.getElementById('user-form').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_EditProfile);	
	}
	
	{$prefix}Load();
	
	";
	echo $html;
}

function popup(){
	$page=CurrentPageName();
	$html="<p style='color:black'>{edit_photo_text}</p><iframe src ='$page?iframe=yes' width='100%' style='border:0px;margin:0px;height:300px'></iframe>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function iframe(){
	$page=CurrentPageName();
	$tpl=new templates();
	$head=$tpl->Heads();
	$user=new user($_SESSION["uid"]);
	$html="
	$head
	<body style='margin:0px;padding:0px;background:none'>
	<span style='font-size:16px;font-weight:bold'>{$_GET["Photo_error"]}</span>
	<form method=\"post\" enctype=\"multipart/form-data\" action=\"$page\">
	<input type='hidden' name='uid' value='{$_SESSION["uid"]}'>
	$hidden
	<p>
		<input type=\"file\" name=\"photo\" size=\"30\">
		<div style='width:100%;text-align:right'><input type='submit' name='upload' value='{upload file}&nbsp;&raquo;' style='width:200px'></div>
	</p>
	</form>
	<center>
	<center style='margin:10px;padding:5px;border:1px solid #005447;width:120px'>
	<img src='$user->img_identity'>
	</center>
	</center>
	</body>
	</html>
	";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);

}

function PhotoUploaded(){
	$tmp_file = $_FILES['photo']['tmp_name'];
	$content_dir=dirname(__FILE__)."/ressources/conf/upload";
	if(!is_dir($content_dir)){@mkdir($content_dir,0755,true);}
	
	if(!is_dir($content_dir)){$_GET["Photo_error"]='{error_unable_to_create_dir} '.$content_dir;iframe();exit();}
	
	if( !@is_uploaded_file($tmp_file) ){
		$_GET["Photo_error"]='{error_unable_to_upload_file} <code style=font-size:11px>'.$tmp_file."</code>";
		exit;
	}
	$name_file = $_FILES['photo']['name'];

if(file_exists( $content_dir . "/" .$name_file)){@unlink( $content_dir . "/" .$name_file);}
 if( !move_uploaded_file($tmp_file, $content_dir . "/" .$name_file) ){
 	$_GET["Photo_error"]="{error_unable_to_move_file} : <code style=font-size:11px>$tmp_file</code> {to} <code style=font-size:11px>". str_replace(dirname(__FILE__),"",$content_dir) . "/" .$name_file."</code>";iframe();exit();}
     
    $file=$content_dir . "/" .$name_file;
    
    
    if(isset($_POST["uid"])){
		$_GET["uid"]=$_POST["uid"];
		$user=new user($_POST["uid"]);
		$user->jpegPhoto_datas=file_get_contents($file); 
		$user->add_user(); 
    	if(is_file($user->thumbnail_path)){unlink($user->thumbnail_path);}  
    	iframe();exit();
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
    iframe();exit();
    }   

iframe();exit();
	
}


?>