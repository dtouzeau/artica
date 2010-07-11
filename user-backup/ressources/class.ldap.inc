<?php
if(dirname($_SERVER['PHP_SELF'])<>"/smartsieve"){
	include_once(dirname(__FILE__) . '/class.cron.inc');
	include_once(dirname(__FILE__) . '/class.amavis.inc');
	include_once(dirname(__FILE__) . '/class.sockets.inc');
}

include_once(dirname(__FILE__) . '/logs.inc');
include_once(dirname(__FILE__) . '/class.templates.inc');

define('grep_ip','([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)');

class LdapUserInfos{
	var $telephoneNumber;
	var $mobile;
	var $title;
	var $CP;
	var $postalAddress;
	var $street;
	var $town;
	var $BP;
	var $phone;
	var $dn;
	var $error;
	var $displayName;
	var $ToolTips;
	
	var $index_key=array();
	function LdapUserInfos($userid=null){
		
		if($userid<>null){
			$ldap=new clladp();
			$res=$ldap->UserDatas($userid);
			$this->telephoneNumber=$res["telephoneNumber"];
			$this->mobile=$res["mobile"];
			$this->title=$res["title"];
			$this->CP=$res["CP"];
			$this->postalAddress=$res["postalAddress"];
			$this->street=$res["street"];
			$this->town=$res["town"];
			$this->BP=$res["BP"];
			$this->phone=$res["phone"];
			$this->dn=$res["dn"];
			$this->displayName=$res["displayName"];
			$this->index_key=$res["ARRAY_KEYS"];
			$this->ToolTips=$this->BuildToolTips();
			}
		
		
	}
	

	function removeaccents($string){ 
  	 	return replace_accents($string);
   	} 
	
	function Save(){
		
		if($this->telephoneNumber<>null){$attrs["telephoneNumber"][0]=$this->telephoneNumber;}
		if($this->mobile<>null){$attrs["mobile"][0]=$this->mobile;}
		if($this->CP<>null){$attrs["postalCode"][0]=$this->CP;}
		if($this->postalAddress<>null){$attrs["postalAddress"][0]=$this->removeaccents($this->postalAddress);}
		if($this->street<>null){$attrs["street"][0]=$this->removeaccents($this->street);}
		if($this->town<>null){$attrs["l"][0]=$this->removeaccents($this->town);}
		if($this->BP<>null){$attrs["postOfficeBox"][0]=$this->BP;}
		if(!is_array($attrs)){return true;}
		
		while (list ($key, $val) = each ($attrs) ){
			if(array_key_exists($key,$this->index_key)){
				$update_array[$key]=$val;
			}else{
				$infos=$infos . "\nAdd new attribute:$key";
				$add_array[$key]=$val;}
		}
		
		
		$ldap=new clladp();
		if(is_array($add_array)){
			if(!$ldap->Ldap_add_mod($this->dn,$add_array)){
				$this->error=$ldap->ldap_last_error .$infos;
				return false;
				}
		}
		
		if(is_array($update_array)){
			if(!$ldap->Ldap_modify($this->dn,$update_array)){
				$this->error=$ldap->ldap_last_error;
				return false;
				}
		}		
		return true;
		}
		function BuildToolTips(){
			$html="<div style=padding:5px;width:250px; class=caption><strong style=font-size:12px;text-decoration:underline>$this->displayName</strong><p><ul><li>$this->telephoneNumber</li><li>$this->mobile</li></ul></p><br>$this->street $this->CP $this->town</div>";
			return $html;
		}
	
	
}


class clladp{
	var $ldap_admin;
	var $ldap_password;
	var $ldap_host;
	var $ldap_port=389;
	var $ldap_connection;
	var $ldapbind;
	var $suffix;
	var $ldap_last_error;
	var $ldapFailed=false;
	var $use_suse_schema=false;
	var $ldap_last_error_num;
	var $ArticaBranchCorrupted=false;
	
	function clladp(){
		
	
		$CurrentPageName=CurrentPageName();
		if($CurrentPageName=="logon.php"){error_log("$CurrentPageName:: ->clladp() starting");}
		
		if(!function_exists('ldap_connect')){
				$tpl=new templates();
				error_log("Ldap is enabled but no ldap_connect in ".__FILE__);
				writelogs("Ldap is enabled but no php5 ldap !!!",__CLASS__."/" .__FUNCTION__,__FILE__);
				echo $tpl->_ENGINE_parse_body("<div style='border:3px solid red;font-size:22px;color:red;padding:5px'>
					{error_please_install_phpldap}
					<center style='margin:50px'>
					<form name='FFM1' method='POST' action='logon.php'>
						<input type='hidden' name='php5-ldap-restart' value='yes'>
						<input type='hidden' name='fromClass' value='yes'>
						<input type='submit' value='&nbsp;&nbsp;&nbsp;Restart The web service&nbsp;&nbsp;&nbsp;'>
						</form>
					</center>
					</div>");
				exit;
			
			return false;
		}
		
		$ldapport = $this->ldap_port;                 
		if(!is_file(dirname(__FILE__) . "/settings.inc")){
			echo "<H1>Warning unable to stat " . dirname(__FILE__) . "/settings.inc</H1>";
			writelogs("ERROR unable to locate " . dirname(__FILE__) . "/settings.inc",__CLASS__."=>" .__FUNCTION__,__FILE__);	
			$this->ldapFailed=true;
			return false;	
		}
		
		@include(dirname(__FILE__) . "/settings.inc");
		
		$this->ldap_admin=$_GLOBAL["ldap_admin"];
		$this->ldap_password=$_GLOBAL["ldap_password"];
		$this->suffix=$_GLOBAL["ldap_root_database"];
		$this->ldap_host=$_GLOBAL["ldap_host"];
		$this->ldap_port=$_GLOBAL["ldap_port"];
		$this->use_suse_schema=$_GLOBAL["USE_SUSE_SCHEMA"];
		if($_GLOBAL["ldap_host"]==null){$this->ldap_host="127.0.0.1";}
		if($_GLOBAL["ldap_host"]=='*'){$this->ldap_host="127.0.0.1";}
		if($this->ldap_port==null){$this->ldap_port="389";}
		
		$fp=@fsockopen($this->ldap_host, $this->ldap_port, $errno, $errstr, 2);
		if(!$fp){
			writelogs("ERROR unable to connect to ldap server in line ". __LINE__." $this->ldap_host, $this->ldap_port \"$errstr\"",
			__CLASS__."/" .__FUNCTION__,__FILE__);
			$this->ldap_last_error=$errstr;$this->ldapFailed=true;
			@fclose($fp);
			return false;
		}
		@fclose($fp);
		
		$GLOBALS["LDAP_COUNT_FAILED"]=0;
		if(!$this->ldap_perform_connection($ldapport)){
			writelogs(PHP_VERSION." php version) Unable to connect to ldap server {$this->ldap_host}:$ldapport ".ldap_err2str($this->ldap_connection),__CLASS__.'/'.__FUNCTION__,__FILE__);
		 	$this->ldapFailed=true;
		 	return null;
		}
		$GLOBALS["LDAP_COUNT_FAILED"]=0;
		

		ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3); // on passe le LDAP en version 3, necessaire pour travailler avec le AD
		ldap_set_option($this->ldap_connection, LDAP_OPT_REFERRALS, 0); 		 
		$this->ldapbind=@ldap_bind($this->ldap_connection, "cn=$this->ldap_admin,$this->suffix", $this->ldap_password);
		
		
			if(!$this->ldapbind){
					
					$errornumber= ldap_errno($this->ldap_connection);
					$error= "ERROR number : $errornumber\nWarning\n unable to connect with following\n cn=$this->ldap_admin,$this->suffix ($this->ldap_password) \n";
					writelogs("ERROR unable to connect to ldap server ldap://{$this->ldap_host}:{$this->ldap_port} right username cn=$this->ldap_admin,$this->suffix"
					,__CLASS__."/" .__FUNCTION__,__FILE__);	
					
		switch (ldap_errno($this->ldap_connection)) {
					case 0x31:
						$error=$error . "\nBad username or password. Please try again.";
						break;
					case 0x32:
						$error=$error . "\nInsufficient access rights.";
						break;
					case 81:
						$error=$error . "\nUnable to connect to the LDAP server\n
						$this->ldap_host:$ldapport\nplease,\nverify if ldap daemon is running\n or the ldap server address";
						break;						
					case -1:
						
						break;
					default:
						$error=$error . "\nCould not bind to the LDAP server." ."\n". ldap_err2str($this->ldap_connection);
 				}					
					
					//echo $error;
					$this->ldap_last_error=$error;$this->ldapFailed=true;
					return false;
			}
			$this->BuildMasterTree();
			$this->ArticaCreate();
				
			return true;	
	
		}
		
function ldap_escape($str){
    $metaChars = array('\\', '(', ')', '#', '*');
    $quotedMetaChars = array();
    foreach ($metaChars as $key => $value) $quotedMetaChars[$key] = '\\'.dechex(ord($value));
    $str=str_replace($metaChars,$quotedMetaChars,$str);
    return ($str);
}	

private function ldap_perform_connection($ldapport){
	$this->ldap_connection=ldap_connect($this->ldap_host, $ldapport ) ;
	 if(!$this->ldap_connection){ 
	 	@ldap_close();
	 	$GLOBALS["LDAP_COUNT_FAILED"]=$GLOBALS["LDAP_COUNT_FAILED"]+1;
	 	if($GLOBALS["LDAP_COUNT_FAILED"]>4){
		 	return false;
	 	}
	 	return $this->ldap_perform_connection($ldapport);
	 }
	 return true;
}
		
		
function ErrorConnection(){
	$ldapClass=$this;
	$port=$ldapClass->ldap_port;
	$password=$ldapClass->ldap_password;
	$admin=$ldapClass->ldap_admin;
	$suffix=$ldapClass->suffix;
	$ldap_host=$ldapClass->ldap_host;
	$ldap_port=$ldapClass->ldap_port;
	if($ldap_port==null){$ldap_port=389;}
	$dn="cn=$admin,$suffix";
	writelogs("testing connection dn $dn",__FUNCTION__,__FILE__);

	if(!$fp=@fsockopen($ldap_host, $ldap_port, $errno, $errstr, 2)){
		$ldapClass->builderror($errstr);
	}
	fclose($fp);


	$ldap_connection=ldap_connect($ldap_host, $ldap_port );
	if(!$ldap_connection){ 
	$error="Please check LDAP connection, it seems that Artica is not allowed to connect to &laquo;$ldap_host:$port&raquo;<br>";}
	writelogs("Error after connecting:$error '$ldap_connection'",__FUNCTION__,__FILE__);	
	
	if($error==null){
		writelogs("settings ldap options LDAP_OPT_PROTOCOL_VERSION",__FUNCTION__,__FILE__);
		ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3); // on passe le LDAP en version 3, necessaire pour travailler avec le AD
		writelogs("settings ldap options LDAP_OPT_REFERRALS",__FUNCTION__,__FILE__);
		ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, 0); 		 
		$ldapbind=@ldap_bind($ldap_connection, $dn, $password);
		writelogs("Bind success",__FUNCTION__,__FILE__);	
		
		if(!$ldapbind){
			$errornumber=ldap_errno($ldap_connection);
			$error_text=ldap_err2str($ldap_connection);
			
			switch ($errornumber) {
					case 0x31:
						$error=$error . "<li>Bad username or password. Please try again.</li>";
						break;
					case 0x32:
						$error=$error . "<li>Insufficient access rights.</li>";
						break;
					case 81:
						$error=$error . "<li>Unable to connect to the LDAP server\n $ldap_host:$port, <br>please,verify if ldap daemon is running or the ldap server address";
						break;						
					case -1:
						$error=$error . "<li>$error_text, <br>it seems that Artica could not connect to the server</li>";
						break;
					default:
						$error=$error . "<li>Could not bind to the LDAP server $error_text</li>";
 				}			

		$error="Error number $errornumber,$error_text<br>$error";}
	} 				
	
		return $ldapClass->builderror($error);

	
	}

	
	private function builderror($error){
	$ldapClass=$this;
	$port=$ldapClass->ldap_port;
	$password=$ldapClass->ldap_password;
	$admin=$ldapClass->ldap_admin;
	$suffix=$ldapClass->suffix;
	$ldap_host=$ldapClass->ldap_host;
	return array("TITLE"=>"{LDAP_ERROR_TEXT}","TEXT"=>"<ul><li>$ldap_host:$port</li><li>$dn</li></ul>$error");
	}
		
		
	function dn_escape($dn) {	
		$olddn = $dn;
		# Check if the RDN has a comma and escape it.
		while (preg_match('/([^\\\\]),(\s*[^=]*\s*),/',$dn))
			$dn = preg_replace('/([^\\\\]),(\s*[^=]*\s*),/','$1\\\\2C$2,',$dn);
			$dn = preg_replace('/([^\\\\]),(\s*[^=]*\s*)([^,])$/','$1\\\\2C$2$3',$dn);
		return $dn;
		}
		
	function BuildMasterTree(){
		$suffix=$this->suffix;
		if($this->ExistsDN($suffix)){return null;}
		writelogs("Build master tree seems that $suffix does not exists",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$tbl=explode(",",$this->suffix);
		$tbl[0]=str_replace("dc=","",$tbl[0]);
		$tbl[1]=str_replace("dc=","",$tbl[1]);
		$upd["objectClass"][]="dcObject";
		$upd["objectClass"][]="organization";
		$upd["o"]="Root";
		$upd["dc"]="{$tbl[0]}";
		
		writelogs("dn: $suffix");
        writelogs("objectClass: dcObject");
        writelogs("objectClass: organization");
        writelogs("dc: {$tbl[0]}");
        writelogs("o: Example Corporation");
        writelogs("description: The Example Corporation");
		
		$this->ldap_add($suffix,$upd);
		
	}
	
	public function BuildOrganizationBranch(){
		$suffix=$this->suffix;
		$dn="dc=organizations,$this->suffix";
		if($this->ExistsDN($dn)){return true;}
		$upd["dc"][]="organizations";
		$upd["o"][]="organizations";
		$upd["objectclass"][]="top";
		$upd["objectclass"][]="organization";
		$upd["objectclass"][]="dcObject";
		if(!$this->ldap_add($dn,$upd)){
			return false;
		}
		return true;
	}
		
	function dn_from_email($email){
		$real=$this->real_email_from_mail($email);
		$attr=array("cn");
		$pattern="(&(objectclass=userAccount)(mail=$real))";
		$sr =@ldap_search($this->ldap_connection,$this->suffix,$pattern,$attr);
		$hash=ldap_get_entries($this->ldap_connection,$sr);
		if($hash["count"]>0){return $hash[0]["dn"];}
		
	}
	
	function email_from_uid($uid){
		$attr=array("mail");
		$pattern="(&(objectclass=userAccount)(uid=$uid))";
		$sr =@ldap_search($this->ldap_connection,"dc=organizations,$this->suffix",$pattern,$attr);
		$hash=ldap_get_entries($this->ldap_connection,$sr);
		if($hash["count"]>0){return $hash[0]["mail"][0];}
	}	
	
	function uid_from_email($email){
		$real=$this->real_email_from_mail($email);
		$attr=array("uid");
		$pattern="(&(objectclass=userAccount)(|(mailAlias=$email)(mail=$email)(mozillaSecondEmail=$email)(FetchMailMatchAddresses=$email)))";
		$sr=@ldap_search($this->ldap_connection,$this->suffix,$pattern,$attr);
		if(!$sr){return null;}
		$hash=ldap_get_entries($this->ldap_connection,$sr);
		if($hash["count"]>0){return $hash[0]["uid"][0];}
	}
	
	function set_organization_picture($ou,$picture_data){
		$dn="ou=$ou,dc=organizations,$this->suffix";
		$update_array["jpegLogo"][0]=$picture_data;
		if(!$this->Ldap_modify($dn,$update_array)){return false;}
		$base=md5($ou);
		shell_exec("/bin/rm ".dirname(__FILE__)."/profiles/icons/$base*");
		return true;
	}
	
	function get_organization_picture($ou,$size){
		include_once(dirname(__FILE__)."/class.images.inc");
		$base=md5($ou).".$size";
		$jpeg_filename=dirname(__FILE__)."/profiles/icons/$base";
		@mkdir(dirname($jpeg_filename),0665,true);
		writelogs("want to find $jpeg_filename",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
		if(!is_file($jpeg_filename)){
			writelogs("$jpeg_filename did not exists, create it",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$dn="ou=$ou,dc=organizations,$this->suffix";
			$search =@ldap_read($this->ldap_connection,"ou=$ou,dc=organizations,$this->suffix",'(objectClass=*)');
			if ($search){
				$entry_id =ldap_first_entry($this->ldap_connection,$search);
				$attrs = ldap_get_attributes($this->ldap_connection,$entry_id);	
				$jpeg_filedata=$attrs["jpegLogo"][0];
				writelogs("jpegLogo=".strlen($jpeg_filedata)." bytes",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}else{
				writelogs("LDAP FAILED ou=$ou,dc=organizations,$this->suffix",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}
		
			if($jpeg_filedata==null){$jpeg_filedata=@file_get_contents("/usr/share/artica-postfix/img/folder-org-64.png");}
			@file_put_contents($jpeg_filename,$jpeg_filedata);
		}
		
		$jpeg_dimensions=@getimagesize($jpeg_filename);
		$img_type=array(1=>"gif",2=>'jpg',3=>'png',4=>'swf',5=>'psd',6=>'bmp',7=>'tiff',8=>'tiff',9=>'jpc',10=>'jp2',11=>'jpx');
		$ext=$img_type[$jpeg_dimensions[2]];
		if($ext<>null){$ext=".$ext";}
		$thumbnail_path="$jpeg_filename$ext";
		
		writelogs("$thumbnail_path",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
		if(!is_file($thumbnail_path)){
			$image=new images($jpeg_filename);
			if($image->thumbnail($size,$size,$thumbnail_path)){
				writelogs("thumbnail=SUCCESS return ressources/profiles/icons/".basename($thumbnail_path),__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				return "ressources/profiles/icons/".basename($thumbnail_path);
			}
		}else{
			writelogs("thumbnail=FAILED return ressources/profiles/icons/".basename($thumbnail_path),__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return "ressources/profiles/icons/".basename($thumbnail_path);
		}
		writelogs("return img/folder-org-64.png",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		return "img/folder-org-64.png";
		
	}

	function organization_name_from_transporttable($domain){
		$pattern="(&(objectClass=transportTable)(cn=$domain))";
		$attr=array();
		$sr =@ldap_search($this->ldap_connection,$this->suffix,$pattern,$attr);
		if($sr){
			$hash=ldap_get_entries($this->ldap_connection,$sr);
			if($hash["count"]>0){
				if(preg_match('#ou=(.+?),dc#',$hash[0]["dn"],$re)){
					return $re[1];
					}
			}
		}
	}
		
	function real_email_from_mail($email){
		$pattern="(&(objectclass=userAccount)(|(mailAlias=$email)(mozillaSecondEmail=$email)(FetchMailMatchAddresses=$email)))";
		$attr=array("mail");
		$sr =@ldap_search($this->ldap_connection,$this->suffix,$pattern,$attr);
		if($sr){
			$hash=ldap_get_entries($this->ldap_connection,$sr);
		}
		if($hash["count"]>0){
			return $hash[0]["mail"][0];}else{return $email;}		
	}
	
	
	function find_users_by_mail($email){
		$pattern="(&(objectclass=userAccount)(|(mailAlias=$email)(mail=$email)(mozillaSecondEmail=$email)(FetchMailMatchAddresses=$email)))";
		$attr=array("uid","mail");
		writelogs("Pattern:$pattern",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sr =ldap_search($this->ldap_connection,$this->suffix,$pattern,$attr);
		if($sr){
			$hash=ldap_get_entries($this->ldap_connection,$sr);
			writelogs("Found: {$hash["count"]} entries",__CLASS__.'/'.__FUNCTION__,__FILE__);
			if($hash["count"]>0){		
				for($i=0;$i<$hash["count"];$i++){
					$res[$hash[$i]["uid"][0]]=$hash[$i]["mail"][0];
				}
			}else{
				writelogs("Failed search $pattern",__CLASS__.'/'.__FUNCTION__,__FILE__);
			}
		return $res;}
		
	}
		
	function AddGroup($group,$ou=null,$generated_id=null){
		$group=replace_accents($group);
		
		if(!is_numeric($generated_id)){$generated_id=null;}
		
		if($generated_id==null){
			$generated_id=$this->_GenerateGUID();
			if($generated_id==1){$generated_id=10;}
		}
		
		if($ou==null){if($_GET["ou"]<>null){$ou=$_GET["ou"];}}
		if($ou==null){if($_SESSION["ou"]<>null){$ou=$_SESSION["ou"];}}
		
		if($ou==null){$dn="cn=$group,dc=organizations,$this->suffix";}
		else{$dn="cn=$group,ou=groups,ou=$ou,dc=organizations,$this->suffix";}
		
		$dn_master_branch="ou=groups,ou=$ou,dc=organizations,$this->suffix";
		if(!$this->ExistsDN($dn_master_branch)){
			$update_array["objectClass"][]="top";
			$update_array["objectClass"][]="organizationalUnit";
			$update_array["ou"][]="groups";
			if(!$this->ldap_add($dn_master_branch,$update_array)){
				echo "Unable to add master branch\n$dn_master_branch\n$this->ldap_last_error";
				return false;
			}
			unset($update_array);
			
		}
		
		
		
		$update_array["cn"][0]="$group";
		$update_array["gidNumber"][0]=$generated_id;
		$update_array["description"][0]="New posix group";
		$update_array["objectClass"][]='posixGroup';
		$update_array["objectClass"][]='ArticaSettings';
		$update_array["objectClass"][]='top';
		
		$dn=$this->dn_escape($dn);
		if(!$this->ldap_add($dn,$update_array)){
			echo  nl2br(
				"Error: Adding {$update_array["gidNumber"][0]} gid 
				cn=$group\ndn:$dn\n".
				$ldap->ldap_last_error);

		}
	}
	
	
	
	function delete_associated_domain($ou,$domain){
		$HashDomains=$this->Hash_associated_domains($ou);
		writelogs("Delete domain $domain in $ou",__FUNCTION__,__FILE__);
		if(is_array($HashDomains)){
			unset($HashDomains[$domain]);
			writelogs("Table count " . count($HashDomains) . " entries",__FUNCTION__,__FILE__);
			if(count($HashDomains)==0){
				$update_array["associatedDomain"][0]="localhost.localdomain";
				$this->Ldap_modify("ou=$ou,dc=organizations,$this->suffix",$update_array);
				return true;
			}
			while (list ($num, $val) = each ($HashDomains) ){
				$update_array["associatedDomain"][]=$num;
			}
		
			$this->Ldap_modify("ou=$ou,dc=organizations,$this->suffix",$update_array);
			}
		}
	
	
	function AddOrganization($ou){
		$hash=$this->hash_get_ou(true);
		if(isset($hash[$ou])){return true;}
		$res=true;
		
		$ou=replace_accents($ou);
		$ou=str_replace(" ","_",$ou);
		$ou=str_replace("-","_",$ou);
		$ou=substr($ou,0,20);
		
		if($ou=="users"){echo "Error: Adding\n$ou words not permitted\n";exit;}
		if($ou=="groups"){echo "Error: Adding\n$ou words not permitted\n";exit;}
		if($ou=="computers"){echo "Error: Adding\n$ou words not permitted\n";exit;}
		
		$this->BuildMasterTree();
		
		$update_array["ou"][0]="$ou";
		$update_array['objectClass'][]='organizationalUnit';
		$update_array['objectClass'][]='top';
		$update_array["objectClass"][]='ArticaSettings';
		$dn="ou=$ou,dc=organizations,$this->suffix";
		$dn=$this->dn_escape($dn);
		if(!$this->ldap_add($dn,$update_array)){
			if($this->ldap_last_error_num<>68){
				echo "Error: Adding\n$ou\ndn:$dn\n".$ldap->ldap_last_error;
				return false;
			}

		}
		unset($update_array);
		$dn="ou=groups,ou=$ou,dc=organizations,$this->suffix";
		$update_array["objectClass"][]="organizationalUnit";
		$update_array["objectClass"][]="top";
		$update_array["ou"][0]="groups";
		if(!$this->ldap_add($dn,$update_array)){
		if($this->ldap_last_error_num<>68){
				echo "Error: Adding\ndn:$dn\n".$this->ldap_last_error;
				return false;
			}
		}
		
		unset($update_array);
		$dn="ou=users,ou=$ou,dc=organizations,$this->suffix";
		$update_array["objectClass"][]="organizationalUnit";
		$update_array["objectClass"][]="top";
		$update_array["ou"][0]="users";
		if(!$this->ldap_add($dn,$update_array)){
		if($this->ldap_last_error_num<>68){
				echo "Error: Adding\ndn:$dn\n".$ldap->ldap_last_error;
				return false;
			}
		}

		unset($update_array);
		$dn="ou=computers,ou=$ou,dc=organizations,$this->suffix";
		$update_array["objectClass"][]="organizationalUnit";
		$update_array["objectClass"][]="top";
		$update_array["ou"][0]="computers";
		if(!$this->ldap_add($dn,$update_array)){
		if($this->ldap_last_error_num<>68){
				echo "Error: Adding\ndn:$dn\n".$ldap->ldap_last_error;
				return false;
			}
		}
	$sock=new sockets();
	$sock->DATA_CACHE_EMPTY();			
	return true;}	
	
	
	function AddUserGroup($ou,$groupname){
		$array["cn"]=$groupname;
		$guid=$this->_GenerateGUID();
		$array["objectClass"][]='posixGroup';
		$array["objectClass"][]='top';
		$array["objectClass"][]='ArticaSettings';
		$array["gidNumber"][]=$guid;
		$cn="cn=$groupname,ou=groups,ou=$ou,dc=organizations,$this->suffix";
		$cn=$this->dn_escape($cn);
		if(@ldap_add($this->ldap_connection,$cn,$array)==false){
		$this->ldap_last_error='Error number ' . ldap_errno($this->ldap_connection) . "\nAction:LDAP AddUserGroup LDAP ADD \ndn:$cn\n$ou; $groupname\n" . ldap_err2str(ldap_errno($this->ldap_connection));
		return 0;}
	
		return $guid;
		
	}
	
	function AddUserToGroup($Groupid,$userid){
		$group_datas=$this->GroupDatas($Groupid);
		if(is_array($group_datas["members"])){
			writelogs("$Groupid=>$userid =\"{$group_datas["members"][$userid]}\"",__CLASS__.'/'.__FUNCTION__,__FILE__);		
			if($group_datas["members"][$userid]<>null){return true;}
			
			while (list ($num, $ligne) = each ($group_datas["members"]) ){
				$update_array["memberUid"][]=$num;
			}
		}
	
		$update_array["memberUid"][]=$userid;
		writelogs("Adding $userid in $Groupid",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(!$this->Ldap_modify($group_datas["dn"],$update_array)){
		echo $this->ldap_last_error;return false;}
		return true;
	}	
	
 function HashAllGroupsForListBox(){
         		$hash=$this->hash_get_ou();
		while (list ($num, $ou) = each ($hash) ){
			$groups=$this->hash_groups($ou);
			if(is_array($groups)){
				while (list ($number, $group) = each ($groups) ){
					$gid=$group["gid"];
					$name="$ou         $number";
					$table[$gid]=$name;
				}
			}
		}
         	$table[0]="[{select_group}]";
         	$table[-100]="[{select_group}]";
         	return $table;
         	
         }
	
	function AddGlobalDomain($domain_name){
		$array["o"][]=$domain_name;
		$array["objectClass"][]='organization';
		$array["objectClass"][]='top';
		$cn="o=$domain_name,$this->suffix";
		$cn=$this->dn_escape($cn);
		$this->ldap_add($cn,$array);
		}
	
	function AllDomains(){
		$array=array();
		return $this->hash_get_all_domains();
		return $array;
	}
	
	
function AddRelayDomain($ou,$domain_name,$relayIP,$relayPort=25,$mx="no"){
	$ldap=new clladp();
	$tpl=new templates();
	$dn="cn=relay_domains,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$this->ExistsDN($dn)){
		$upd['cn'][0]="relay_domains";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		$this->ldap_add($dn,$upd);
		unset($upd);		
		}
	
	$hashdoms=$this->hash_get_all_domains();
	if($hashdoms[$domain_name]<>null){
		echo $tpl->_ENGINE_parse_body('{error_domain_exists}','domains.edit.domains.php');
		exit;
	}

	
	$dn="cn=$domain_name,cn=relay_domains,ou=$ou,dc=organizations,$this->suffix";	
	
	$upd['cn'][0]="$domain_name";
	$upd['objectClass'][0]='PostFixRelayDomains';
	$upd['objectClass'][1]='top';
	$this->ldap_add($dn,$upd);	
	
	$dn="cn=relay_recipient_maps,ou=$ou,dc=organizations,$this->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="relay_recipient_maps";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		$this->ldap_add($dn,$upd);
		unset($upd);		
		}	
	
	$dn="cn=@$domain_name,cn=relay_recipient_maps,ou=$ou,dc=organizations,$this->suffix";
	$upd['cn'][0]="@$domain_name";
	$upd['objectClass'][0]='PostfixRelayRecipientMaps';
	$upd['objectClass'][1]='top';
	$ldap->ldap_add($dn,$upd);		
	
	$dn="cn=transport_map,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="transport_map";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		$ldap->ldap_add($dn,$upd);
		unset($upd);		
		}	
if($relayIP<>null){
	if($mx=="no"){$relayIP="[$relayIP]";}
	$dn="cn=$domain_name,cn=transport_map,ou=$ou,dc=organizations,$this->suffix";
	$upd['cn'][0]="$domain_name";
	$upd['objectClass'][0]='transportTable';
	$upd['objectClass'][1]='top';
	$upd["transport"][]="relay:$relayIP:$relayPort";
	$this->ldap_add($dn,$upd);			
	}
			
}	
	
	function Ldap_search($dn,$filter,$attrs,$maxRow=0){
		if(!is_array($attrs)){
			$sr =@ldap_search($this->ldap_connection,$dn,$filter,array(),0,$maxRow);
			}else{$sr =@ldap_search($this->ldap_connection,$dn,$filter,$attrs,0,$maxRow);}
		if ($sr) {
			return ldap_get_entries($this->ldap_connection,$sr);
		}else{
			$this->ldap_last_error='Error LDAP search number ' . ldap_errno($this->ldap_connection) . "\nAction:LDAP search\ndn:$dn\n$filter\n" . ldap_err2str(ldap_errno($this->ldap_connection));
   			writelogs("ERROR Ldap_search : $filter in $dn" . ldap_err2str(ldap_errno($this->ldap_connection)),__CLASS__ . "/" . __FUNCTION__,__FILE__);return false;}
		return array();}
		
		

	
	function Hash_all_kav_settings(){
		$arr=array("gidNumber","KasperkyAVScanningDatas");
		$sr =@ldap_search($this->ldap_connection,$this->suffix,'(&(objectclass=ArticaSettings)(KasperkyAVScanningDatas=*))',$arr);
		if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);
			
			for($i=0;$i<$hash["count"];$i++){
				$gidnumber=$hash[$i]["gidnumber"][0];
				$kas=$hash[$i]["kasperkyavscanningdatas"][0];
				if($kas==null){
					writelogs("there is no datas here, try to get the default value",__FUNCTION__,__FILE__);
					$def=file_get_contents(dirname(__FILE__) . "/databases/kav4mailserver.default.conf");
					$def="[smtpscan.group:$gidnumber]\n$def";
					$kas=$def;
				}
				
				$res[$gidnumber]=$kas;
			}
			
		return $res;	
		}
		
	}
	
	function Hash_all_kas_settings(){
		include_once(dirname(__FILE__) . '/class.artica.inc');
		$artica=new artica_general();
		$defGid=$this->ArticaDefaultGroupGid();
		if($artica->EnableGroups=='no'){
			writelogs("Group behavior is set to 'no' exclude all groups except default group $defGid",__FUNCTION__,__FILE__);
			$filter="(&(objectclass=ArticaSettings)(KasperkyASDatas=*)(gidNumber=$defGid))";	}
		else{$filter="(&(objectclass=ArticaSettings)(KasperkyASDatas=*))";}
		
		$arr=array("gidNumber","KasperkyASDatas","KasperkyASDatasRules","cn");
		$sr =@ldap_search($this->ldap_connection,$this->suffix,$filter,$arr);
		if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);
			for($i=0;$i<$hash["count"];$i++){
				$gidnumber=$hash[$i]["gidnumber"][0];
				if($artica->EnableGroups=='yes' ){if($gidnumber==$defGid){continue;}}
					$kas=$hash[$i]["kasperkyasdatas"][0];
					$kas_rules=$hash[$i][strtolower("KasperkyASDatasRules")][0];
					$res[$gidnumber]["cn"]=$hash[$i]["cn"][0];
					$res[$gidnumber]["actions"]=$kas;
					$res[$gidnumber]["rules"]=$kas_rules;
					$res[$gidnumber]=$res[$gidnumber]+ $this->_Hash_blackWhites($gidnumber);
					$res[$gidnumber]["users"]=$this->GroupMailAliases($gidnumber);	
				}
			}
			
		return $res;	
		}
		
	
		
	function dnsmasqSettings(){
		$dn="cn=dnsmasq,cn=artica,$this->suffix";
		if(!$this->ExistsDN($dn)){
			$upd['cn'][0]="dnsmasq";
			$upd['objectClass'][0]='DnsMasqSettings';
			$upd['objectClass'][1]='top';
			$upd["DnsMasqConfigurationFile"][0]="listen-address=127.0.0.1\nresolv-file=/etc/dnsmasq.resolv.conf\nbogus-nxdomain=62.210.183.10\nbogus-nxdomain=62.210.183.11\nbogus-nxdomain=62.210.183.12\nbogus-nxdomain=62.210.183.13\nbogus-nxdomain=62.210.183.14\nbogus-nxdomain=62.210.183.15\nbogus-nxdomain=62.210.183.16\nbogus-nxdomain=62.210.183.17\nbogus-nxdomain=62.210.183.75\nbogus-nxdomain=62.210.183.76\nbogus-nxdomain=62.210.183.77\nbogus-nxdomain=62.210.183.78\nbogus-nxdomain=62.210.183.79\nbogus-nxdomain=62.210.183.80\nbogus-nxdomain=62.210.183.81";
			$this->ldap_add($dn,$upd);
			
		}
		
		
		$sr =@ldap_read($this->ldap_connection,$dn,'objectClass=DnsMasqSettings');
		if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);	
			$array["DnsMasqConfigurationFile"]=$hash[0][strtolower("DnsMasqConfigurationFile")][0];	
		}
		return $array;
		
	}
	
	function ArticaDatas(){
		if($this->ldapFailed){return null;}
		$CurrentPageName=CurrentPageName();
		
		if(!function_exists('ldap_connect')){return null;}
		if($CurrentPageName=="logon.php"){error_log("$CurrentPageName:: IFcn_notexists_CreateIt().. ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$this->IFcn_notexists_CreateIt('artica');
		$array=array();
		if($CurrentPageName=="logon.php"){error_log("$CurrentPageName::".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$sr =@ldap_read($this->ldap_connection,"cn=artica,$this->suffix",'objectClass=ArticaSettings');
		
		if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);
			
			$array["maincf"]=$hash[0]["postfixmaincf"][0];
			$array["headers_regex"]=$hash[0][strtolower("PostfixHeadersRegex")];
			for($i=0;$i<$hash[0][strtolower("PostfixHeadersRegex")]["count"];$i++){
				$array["headers_regex_table"][]=$this->ParseMyLdapInisData($hash[0][strtolower("PostfixHeadersRegex")][$i]);
			}
			
			if(!isset($hash[0]["kasperkyavscanningdatas"])){
				$updatearray["KasperkyAVScanningDatas"][]=".";
				writelogs("Adding KasperkyAVScanningDatas as artica data settings",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$this->Ldap_add_mod("cn=artica,$this->suffix",$updatearray);
				unset($updatearray);
			}
			
			if($hash[0][strtolower("ArticaMailAddonsLevel")][0]==null){
				$updatearray["ArticaMailAddonsLevel"]=0;
				writelogs("Adding ArticaMailAddonsLevel as artica data settings",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$this->Ldap_add_mod("cn=artica,$this->suffix",$updatearray);
				unset($updatearray);
				$array["ArticaMailAddonsLevel"]=0;
			}else{$array["ArticaMailAddonsLevel"]=$hash[0][strtolower("ArticaMailAddonsLevel")][0];}
						
			
			if($hash[0][strtolower("ArticaFilterEnabled")][0]==null){
				$updatearray["ArticaFilterEnabled"]=0;
				writelogs("Adding ArticaFilterEnabled as artica data settings",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$this->Ldap_add_mod("cn=artica,$this->suffix",$updatearray);
				unset($updatearray);
				$array["ArticaFilterEnabled"]=0;
			}else{$array["ArticaFilterEnabled"]=$hash[0][strtolower("ArticaFilterEnabled")][0];}			
			
			
			
			
			if($hash[0][strtolower("OBMEnabled")][0]==null){
				$updatearray["OBMEnabled"]=0;
				writelogs("Adding OBMEnabled as artica data settings",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$this->Ldap_add_mod("cn=artica,$this->suffix",$updatearray);
				unset($updatearray);
				$array["OBMEnabled"]=0;
			}else{$array["OBMEnabled"]=$hash[0][strtolower("OBMEnabled")][0];}				
			
			if($hash[0][strtolower("NTPDEnabled")][0]==null){
				$updatearray["NTPDEnabled"]=0;
				writelogs("Adding NTPDEnabled as artica data settings",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$this->Ldap_add_mod("cn=artica,$this->suffix",$updatearray);
				unset($updatearray);
				$array["NTPDEnabled"]=0;
			}else{$array["NTPDEnabled"]=$hash[0][strtolower("NTPDEnabled")][0];}					
			
			
			$array["kav4mailservers"]=$hash[0]["kasperkyavscanningdatas"][0];
			$array["ssl_certificate"]=$hash[0][strtolower('PostfixSSLCert')][0];
			$array["PostfixHashSmtpSaslAuth"]=$hash[0][strtolower("PostfixHashSmtpSaslAuth")][0];
			$array["SmtpdClientRestrictionsRules"]=$hash[0][strtolower("SmtpdClientRestrictionsRules")][0];
			$array["SmtpdSenderRestrictionsRules"]=$hash[0][strtolower("SmtpdSenderRestrictionsRules")][0];
			$array["SmtpdRecipientRestrictionsRules"]=$hash[0][strtolower("SmtpdRecipientRestrictionsRules")][0];
			$array["SmtpdHeloRestrictionsRules"]=$hash[0][strtolower("SmtpdHeloRestrictionsRules")][0];
			$array["PostFixBounceTemplate"]=$hash[0][strtolower("PostFixBounceTemplate")][0];
			$array["ArticaGlobalSettings"]=$hash[0][strtolower("ArticaGlobalSettings")][0];
			$array["DnsMasqConfigurationFile"]=$hash[0][strtolower("DnsMasqConfigurationFile")][0];
			if(!is_array($hash[0][strtolower("ArticaWebRootURI")])){
				writelogs("Adding ArticaWebRootURI as artica data settings",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$mod["ArticaWebRootURI"]="https://" .$_SERVER["SERVER_ADDR"] .':'.$_SERVER["SERVER_PORT"].'/' .dirname($_SERVER['PHP_SELF']);
				$this->Ldap_add_mod("cn=artica,$this->suffix",$mod);
				$array["ArticaWebRootURI"]=$mod["ArticaWebRootURI"];
			}
			$array["ArticaWebRootURI"]=$hash[0][strtolower("ArticaWebRootURI")][0];
			
			
			
			}
			
			
			
			
			
		
		return $array;
	}
	
	function ldap_close(){
		@ldap_unbind($this->ldap_connection);	
		@ldap_close();
	}
	
	function ArticaDatasFromField($fieldName){
		$sr =@ldap_read($this->ldap_connection,"cn=artica,$this->suffix",'objectClass=ArticaSettings');
		$fieldName=strtolower($fieldName);
		writelogs("read $fieldName in cn=artica,$this->suffix",__FUNCTION__,__FILE__);
		if ($sr) {$hash=ldap_get_entries($this->ldap_connection,$sr);
			if(!isset($hash[0][$fieldName])){writelogs("Failed to load in hash $fieldName in cn=artica,$this->suffix",__FUNCTION__,__FILE__);return null;}
			writelogs("return " . strlen($hash[0][$fieldName][0]) . " caracters",__FUNCTION__,__FILE__);
			return $hash[0][$fieldName][0];
		}else{writelogs("Failed to load $fieldName in cn=artica,$this->suffix",__FUNCTION__,__FILE__);}
		
	}
	
	function ArticaDatasAddField($fieldName,$datas){
		$fieldNameLower=strtolower($fieldName);
		$sr =@ldap_read($this->ldap_connection,"cn=artica,$this->suffix",'objectClass=ArticaSettings');
		if ($sr) {
				$hash=ldap_get_entries($this->ldap_connection,$sr);
				if(!isset($hash[0][$fieldNameLower])){
					$updateArray["$fieldName"]=$datas;
					writelogs("adding $fieldName into artica datas with " . strlen($datas) . " caracters",__FUNCTION__,__FILE__);
					$this->Ldap_add_mod("cn=artica,$this->suffix",$updateArray);
					}else{
					$updateArray["$fieldName"][0]=$datas;
					$this->Ldap_modify("cn=artica,$this->suffix",$updateArray);
					}
				}
		}
		
		
	function CyrusAdminCreate(){
		$dn="cn=cyrus,dc=organizations,$this->suffix";
		if(!$this->ExistsDN($dn)){
			writelogs('Creating new user Cyrus in order to perform imap operations....',__CLASS__ . '/' . __FUNCTION__);
			$upd["objectClass"][]="top";	
			$upd["objectClass"][]="inetOrgPerson";
			$upd["cn"][]="cyrus";
			$upd["sn"][]="cyrus";
			$upd["uid"][]="cyrus";
			$upd["userPassword"][]=$this->ldap_password;
			return $this->ldap_add($dn,$upd);
			
		}
		
		
		
	}
	
	function CyrusAdminOtherCreate($cyrus_admin,$cyrus_password){
		if(trim($cyrus_password)==null){
			writelogs("no password set...define has cyrus default",__CLASS__ . '/' . __FUNCTION__,__FILE__,__LINE__);
			$cyrus_password=$this->CyrusPassword();
		}
		if(trim($cyrus_password)==null){
			writelogs("no password set...define has admin LDAP default",__CLASS__ . '/' . __FUNCTION__,__FILE__,__LINE__);
			$cyrus_password=$this->ldap_password;
		}		
		
		
		$dn="cn=$cyrus_admin,dc=organizations,$this->suffix";
		if(!$this->ExistsDN($dn)){
			writelogs("Creating new user $cyrus_admin Cyrus in order to perform imap operations....",__CLASS__ . '/' . __FUNCTION__,__FILE__,__LINE__);
			$upd["objectClass"][]="top";	
			$upd["objectClass"][]="inetOrgPerson";
			$upd["cn"][]="$cyrus_admin";
			$upd["sn"][]="$cyrus_admin";
			$upd["uid"][]="$cyrus_admin";
			$upd["userPassword"][]=$cyrus_password;
			$this->ldap_add($dn,$upd);
		}else{
			$upd["userPassword"][]=$cyrus_password;
			$this->Ldap_modify($dn,$upd);
		}
		
		
		
	}	
	
		
	function ArticaCreate(){
		if(!$this->ExistsDN("cn=artica,$this->suffix")){
			writelogs("Creating cn=artica,$this->suffix branch",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$dn=$this->dn_escape("cn=artica,$this->suffix");
			$update_array["cn"][]='artica';
			$update_array["objectClass"][]='organizationalRole';
			$update_array["objectClass"][]='ArticaSettings';
			$update_array["objectClass"][]='top';
			$this->ldap_add($dn,$update_array);
		}
		
		if(!$this->ExistsDN("cn=artica,$this->suffix")){$this->ArticaBranchCorrupted=true;}
		
	}
	
	function IFcn_notexists_CreateIt($name){
		$CurrentPageName=CurrentPageName();
		if($CurrentPageName=="logon.php"){error_log($this->ldapFailed.__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		if($this->ldapFailed){return null;}
		try{
			$sr =@ldap_search($this->ldap_connection,$this->suffix,"(cn=$name)");
		}catch (Exception $e) {
			if($CurrentPageName=="logon.php"){error_log("$CurrentPageName: ".$e->getMessage()." ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		}
		
		if ($sr) {
			$hash=@ldap_get_entries($this->ldap_connection,$sr);
			if($hash["count"]==0){
				$dn=$this->dn_escape("cn=artica,$this->suffix");
				writelogs("Creating $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$update_array["cn"][]='artica';
				$update_array["objectClass"][]='organizationalRole';
				$update_array["objectClass"][]='ArticaSettings';
				$update_array["objectClass"][]='top';
				$this->ldap_add($dn,$update_array);
				
				}
			}
	}
	
	
	function _HashAllBlackWhites(){
		$sr =@ldap_search($this->ldap_connection,$this->suffix,'(KasperkyASDatasAllow=*)',array('KasperkyASDatasAllow'));
		if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);
			while (list ($num, $ligne) = each ($hash) ){
				for($i=0;$i<$ligne["kasperkyasdatasallow"]["count"];$i++){
					$allow[$ligne["kasperkyasdatasallow"][$i]]=$ligne["kasperkyasdatasallow"][$i];
					}
				}
			}
		//--------------------------------------------------------------------------------------------------------------------------------------------------------------
		$sr =@ldap_search($this->ldap_connection,$this->suffix,'(KasperkyASDatasDeny=*)',array('KasperkyASDatasDeny'));
		if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);
			while (list ($num, $ligne) = each ($hash) ){
				for($i=0;$i<$ligne["kasperkyasdatasdeny"]["count"];$i++){
					$deny[$ligne["kasperkyasdatasdeny"][$i]]=$ligne["kasperkyasdatasdeny"][$i];
					}
				}
			}
					
		return array("allows"=>$allow,"deny"=>$deny);	
		}
	

	function _Hash_blackWhites($gidnumber){
		$defGid=$this->ArticaDefaultGroupGid();
		if($defGid==$gidnumber){return $this->_HashAllBlackWhites();}
		$hash=$this->GroupDatas($gidnumber);
		$members=$hash["members"];
		if(is_array($members)){
			while (list ($num, $ligne) = each ($members) ){
				$hash=$this->UserDatas($num);
				if(is_array($hash["WhiteList"])){
					while (list ($index, $email) = each ($hash["WhiteList"]) ){$white[$email]=$email;}
				}
				
				if(is_array($hash["blacklist"])){
					while (list ($index, $email) = each ($hash["blacklist"]) ){$black[$email]=$email;}
				}
			}
		}
		
		return array("allows"=>$white,"deny"=>$black);
		
	}
	
function Hash_GetALLUsers(){
   $arr=array("uid","mail");
   $sr =@ldap_search($this->ldap_connection,"dc=organizations,$this->suffix",'(objectclass=userAccount)',$arr);
		if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);			
			for($i=0;$i<$hash["count"];$i++){
				$userid=$hash[$i]["uid"][0];
				$mail=$hash[$i]["mail"][0];
				$res[$userid]=$mail;
				}
			}else{
				return array();
			}

	return $res;
} 	
	
	
	function UserVacation($uid){
		
		$filter="(&(objectclass=Vacation)(uid=$uid)(vacationActive=TRUE))";
		$attrs=array("vacationEnabled","vacationActive","vacationEnd","vacationStart","vacationInfo","DisplayName");
		if($GLOBALS["VERBOSE"]){writelogs($filter,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		$sr =@ldap_search($this->ldap_connection,$this->suffix,"$filter",$attrs);
		if (!$sr) {return array();}
		$hash=ldap_get_entries($this->ldap_connection,$sr);
		return $hash[0];
	}
	
	
	function hash_load_transport(){
		$sr =@ldap_search($this->ldap_connection,$this->suffix,'(&(objectclass=transportTable)(transport=*))');
		if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);
			if(!is_array($hash)){return array();}
			while (list ($num, $ligne) = each ($hash) ){
				if($ligne["cn"][0]<>null){
					$res[$ligne["cn"][0]]=$ligne["transport"][0];
				
				}
				
			}
			
		}
		
	
		return $res;		
	}
	
	function sasl_relayhost($server){
		$sr =@ldap_search($this->ldap_connection,"cn=smtp_sasl_password_maps,cn=artica,$this->suffix"
		,"(&(objectclass=PostfixSmtpSaslPaswordMaps)(cn=*$server*))");
		if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);
			if(!is_array($hash)){return array();}
				return $hash[0]["smtpsaslpasswordstring"][0];
				
			}
		return $res;		
	}	
	
	function WhereisDomainTransport($domain){
		$sr =@ldap_search($this->ldap_connection,$this->suffix,"(cn=$domain)");
		$hash=ldap_get_entries($this->ldap_connection,$sr);
		return  $hash[0]["dn"];
	}
	
	
	function GetTransportTable($domain){
		$hash=$this->hash_load_transport();
		return $hash[$domain];
		
	}
	

	
 /**
     * return all organizationalUnit in an array
     * array[0]=ou 
     *
     * @param none
     * @return array
     */	
	
	
	
	function hash_get_ou($has_key=false){
		
		$sock=new sockets();
		$result=$sock->DATA_CACHE(__FUNCTION__.$has_key,0);		
		if(is_array($result)){return $result;}
		
		if($this->ldapFailed){return null;}
		$this->ArticaCreate();
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		$suffix="dc=organizations,$this->suffix";
		$res=array();
		$arr=array("ou");
		$sr = @ldap_list($ld,$suffix,'(&(objectclass=organizationalUnit)(ou=*))',$arr);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			if(!is_array($hash)){return array();}
			while (list ($num, $ligne) = each ($hash) ){
				if($ligne["ou"][0]<>null){
					   if(!preg_match('#dc=samba,#',$ligne["dn"])){
						$ou=$ligne["ou"][0];
						$pos=strpos($ou,',');
						if($pos>1){$ou=substr($ou,0,$pos-1);}
						if($ou=="sysusers"){continue;}
						if($ou=="users"){continue;}
						if($ou=="groups"){continue;}
						if($ou=="computers"){continue;}
					   	if($ou=="mounts"){continue;}
						if($has_key){$res[$ou]=$ou;}else{$res[]=$ou;}
						}
					}
				}
			}
			
		
		return $res;
		
		}
		
		
	function ReadDNInfos($dn){
		//if(!$this->ldap_connection){$this->
		$search = @ldap_read($this->ldap_connection,$dn,'(objectClass=*)');
		$entry = @ldap_get_entries($this->ldap_connection, $search);
		return $this->_ReturnArray($entry);
		
	}
	
		
	function DNGetDatas($dn){
		if(preg_match('#ou=(.+?),dc#',$dn,$reg)){
			$return["ou"]=$reg[1];
			$pos=strpos($return["ou"],',');
			if($pos>0){$return["ou"]=substr($return["ou"],0,$pos);}
		}
		$attrs=array();
		$search = ldap_read($this->ldap_connection,$dn,'(objectClass=*)',$attrs);
		if (! $search){return array();}
		if ($entry_id = ldap_first_entry($this->ldap_connection,$search))
		while ($entry_id) {
			$dn = ldap_get_dn($this->ldap_connection,$entry_id);
			$return['dn'] = $dn;
			$attrs = ldap_get_attributes($this->ldap_connection,$entry_id);
			$return=$return+ $this->_ReturnArray($attrs);
			$entry_id = ldap_next_entry($this->ldap_connection,$entry_id);

			}

		if ($sort_results && is_array($return)){ksort($return);}
		return $return;
	}
	

	function OUDatas($ou){
		$dn="ou=$ou,dc=organizations,$this->suffix";
		
		$search =@ldap_read($this->ldap_connection,"ou=$ou,dc=organizations,$this->suffix",'(objectClass=*)');
		if (! $search){return array();}
		$entry_id = ldap_first_entry($this->ldap_connection,$search);
		$attrs = ldap_get_attributes($this->ldap_connection,$entry_id);
		
		// privileges
		
		if(!is_array($attrs["ArticaGroupPrivileges"])){
			$upd["ArticaGroupPrivileges"][0]="DEFAULT";
			$this->Ldap_add_mod($dn,$upd);
			$res["ArticaGroupPrivileges"]="DEFAULT";
		}else{
			$res["ArticaGroupPrivileges"]=$attrs["ArticaGroupPrivileges"][0];
		}

		//Kaspersky Anti-Spam -----------------------------------------------------------------------------------------
		if(!is_array($attrs["KasperkyASDatasRules"])){
			$upd["KasperkyASDatasRules"][0]="DEFAULT";
			$this->Ldap_add_mod($dn,$upd);
			$res["KasperkyASDatasRules"]="DEFAULT";
		}else{
			$res["KasperkyASDatasRules"]=$attrs["KasperkyASDatasRules"][0];
		}
		
		//Kaspersky Anti-virus -----------------------------------------------------------------------------------------
		if(!is_array($attrs["KasperkyAVScanningDatas"])){
			$upd["KasperkyAVScanningDatas"][0]="DEFAULT";
			$this->Ldap_add_mod($dn,$upd);
			$res["kav4mailservers"]="DEFAULT";
		}else{
			$res["kav4mailservers"]=$attrs["KasperkyAVScanningDatas"][0];	
		}
		
		//Fake sender  -----------------------------------------------------------------------------------------
		if(!is_array($attrs["ArticaFakedMailFrom"])){
			$upd["ArticaFakedMailFrom"][0]="pass";
			$this->Ldap_add_mod($dn,$upd);
			$res["ArticaFakedMailFrom"]="pass";
		}else{
			$res["ArticaFakedMailFrom"]=$attrs["ArticaFakedMailFrom"][0];	
		}
		//----------------------------------------------------------------------------------------------------
		
		//Fake sender  -----------------------------------------------------------------------------------------
		if(!is_array($attrs["ArticaDenyNoMXRecords"])){
			$upd["ArticaDenyNoMXRecords"][0]="pass";
			$this->Ldap_add_mod($dn,$upd);
			$res["ArticaDenyNoMXRecords"]="pass";
		}else{
			$res["ArticaDenyNoMXRecords"]=$attrs["ArticaDenyNoMXRecords"][0];	
		}
		//----------------------------------------------------------------------------------------------------		
		
		//quarantine  -----------------------------------------------------------------------------------------
		if(!is_array($attrs["ArticaMaxDayQuarantine"])){
			$upd["ArticaMaxDayQuarantine"][0]="3";
			$this->Ldap_add_mod($dn,$upd);
			$res["ArticaMaxDayQuarantine"]="3";
		}else{
			$res["ArticaMaxDayQuarantine"]=$attrs["ArticaMaxDayQuarantine"][0];	
		}
		//----------------------------------------------------------------------------------------------------		
			
			if(is_array($attrs["VirtualDomainsMapsMTA"])){
				for($i=0;$i<$attrs["VirtualDomainsMapsMTA"]["count"];$i++){
					$res["VirtualDomainsMapsMTA"][$attrs["VirtualDomainsMapsMTA"][$i]]=$attrs["VirtualDomainsMapsMTA"][$i];
				}
			}
	//Country deny list  -----------------------------------------------------------------------------------------
			if(is_array($attrs["CountryDeny"])){
				for($i=0;$i<$attrs["CountryDeny"]["count"];$i++){
					$res["CountryDeny"][$i]=$attrs["CountryDeny"][$i];
				}
			}			
	//RBL  -----------------------------------------------------------------------------------------

	if(!is_array($attrs["RblServersAction"])){
		unset($upd);
		$upd["RblServersAction"]="pass";
		$this->Ldap_add_mod($dn,$upd);
	}else{
		$res["RblServersAction"]=$attrs["RblServersAction"][0];
	}
	
	if(!is_array($attrs["SURBLServersAction"])){
		unset($upd);
		$upd["SURBLServersAction"]="pass";
		$this->Ldap_add_mod($dn,$upd);
	}else{
		$res["SURBLServersAction"]=$attrs["SURBLServersAction"][0];
	}	
	
	if(is_array($attrs["RblServers"])){
				for($i=0;$i<$attrs["RblServers"]["count"];$i++){
					$res["RblServers"][$i]=$attrs["RblServers"][$i];
				}
			}			
			
	if(is_array($attrs["SURBLServers"])){
				for($i=0;$i<$attrs["SURBLServers"]["count"];$i++){
					$res["SURBLServers"][$i]=$attrs["SURBLServers"][$i];
				}
			}			
			
	//bogospam action  -----------------------------------------------------------------------------------------		
		if(!is_array($attrs["BogoFilterAction"])){
			unset($upd);	
			$upd["BogoFilterAction"]="80;prepend;*** SPAM ***";
			$this->Ldap_add_mod($dn,$upd);			
		}else{
			$res["BogoFilterAction"]=$attrs["BogoFilterAction"][0];	
		}

	//trust users  -----------------------------------------------------------------------------------------		
	if(!is_array($attrs["OuTrustMyUSers"])){
		unset($upd);	
			$upd["OuTrustMyUSers"]="yes";
			$this->Ldap_add_mod($dn,$upd);	
			$res["OuTrustMyUSers"]="yes";		
		}else{
			$res["OuTrustMyUSers"]=$attrs["OuTrustMyUSers"][0];	
		}
		
		
	//ObjectClass
	for($i=0;$i<$attrs["objectClass"]["count"];$i++){
		$res["objectClass"][$attrs["objectClass"][$i]]=true;
		
	}
		
		
		
			return $res;
		}
		
	
	function Hash_get_ou_blacklisted_domains($ou,$find='*'){
		
		$dn="cn=blackListedDomains,ou=$ou,dc=organizations,$this->suffix";
		if(!$this->ExistsDN($dn)){
				$update_array["cn"][]='blackListedDomains';
				$update_array["objectClass"][]='PostFixStructuralClass';
				$update_array["objectClass"][]='top';
				$this->ldap_add($dn,$update_array);
		}
		$arr=array("cn");
		$search = @ldap_search($this->ldap_connection,$dn,"(&(objectClass=DomainsBlackListOu)(cn=$find))");
		if (! $search){return array();}
		$hash=ldap_get_entries($this->ldap_connection,$search);
		for($i=0;$i<$hash["count"];$i++){
			$res[]=$hash[$i]["cn"][0];
		}
		
		return $res;
	}
	
		
	function hash_get_ou_full_datas($listefiled=0){
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		$suffix="dc=organizations,$this->suffix";
		writelogs($suffix . '= >(&(objectclass=organizationalUnit)(ou=*))',__FUNCTION__,__FILE__);
		$arr=array("ou","dn");
		$sr = @ldap_search($ld,$suffix,'(&(objectclass=organizationalUnit)(ou=*))',$arr);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			while (list ($num, $ligne) = each ($hash) ){
				if($ligne["ou"][0]=="sysusers"){continue;}
				if($ligne["ou"][0]==null){continue;}
				if(preg_match('#dc=samba,#',$ligne["dn"])){continue;}
				$ou=$ligne["ou"][0];
				$pos=strpos($ou,',');
				if($pos>1){$ou=substr($ou,0,$pos-1);}
				if($ou=="sysusers"){continue;}
				if($ou=="users"){continue;}
				if($ou=="groups"){continue;}
				if($ou=="computers"){continue;}
				switch ($listefiled) {
							case 1:$res[$ligne["ou"][0]]=$ligne["ou"][0];break;
							case 2:$res[]=$ligne["ou"][0];break;
							default:$res[$ligne["ou"][0]]=$ligne["dn"];	break;
						}
					
				
			}
		}
			
		return $res;
		
		}
		
	function Hash_Get_ou_from_users($userid,$listeField=0){
		$arr=array("cn");
		
		$sr = @ldap_search($this->ldap_connection,$this->suffix,"(&(objectclass=posixGroup)(memberUid=$userid))",$arr);
		if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);
			for($i=0;$i<$hash["count"];$i++){
				$dn=$hash[$i]["dn"];
				if(preg_match('#cn=(.+?),ou=#',$dn,$rs)){$cn=$rs[1];}
				if(preg_match('#ou=users,ou=(.+?),dc#',$dn,$reg)){
					if($listeField==0){$res[$reg[1]]="ou={$reg[1]},$this->suffix";}else{$res[]=$reg[1];}
				}
			}
		return $res;}
	}

	function GroupDeleteUser($gid,$userid){
		$hash=$this->GroupDatas($gid);
		$users=$hash["members"];
		$update_array["memberUid"]=$userid;
		writelogs("Delete $userid user from group $gid",__CLASS__ . "/" . __FUNCTION__,__FILE__ );
		$this->Ldap_del_mod($hash["dn"],$update_array);
		
	}
	
	function Hash_relay_domains($ou=null){
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		if($ou<>null){
			$suffix="ou=$ou,dc=organizations,$this->suffix";
		}else{$suffix=$this->suffix;}
		$arr=array();
		$sr =@ldap_search($ld,$suffix,'(&(objectclass=PostFixRelayDomains)(cn=*))',$arr);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			for($i=0;$i<$hash["count"];$i++){
				writelogs("Found DOMAIN \"{$hash[$i]["cn"][0]}\" in $suffix",__CLASS__ . "/" . __FUNCTION__,__FILE__ );
				$res[$hash[$i]["cn"][0]]=$this->Hash_relay_domains_transport($ou,$hash[$i]["cn"][0]);
				}
			
		}else{writelogs("No relais for this $ou...",__CLASS__ . "/" . __FUNCTION__,__FILE__ );}
		return $res;
	}

	function Hash_relay_domains_transport($ou=null,$domain){
		if($ou<>null){
			$dn="ou=$ou,dc=organizations,$this->suffix";
		}else{$dn="cn=transport_map,cn=artica,$this->suffix";}
		writelogs("search in $dn",__CLASS__ . "/" . __FUNCTION__,__FILE__ );
		$sr =@ldap_search($this->ldap_connection,$dn,"(&(ObjectClass=transportTable)(cn=$domain))",array());
		if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);
			writelogs("transport={$hash[0]["transport"][0]}",__CLASS__ . "/" . __FUNCTION__,__FILE__ );
			return $hash[0]["transport"][0];
		}else{writelogs("search failed",__CLASS__ . "/" . __FUNCTION__,__FILE__ );}
		
	}
	
	function Hash_vacation_domains(){
		$suffix="cn=PostfixVacationTransport,cn=artica,$this->suffix";
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;		
		$arr=array();
		$sr =@ldap_search($ld,$suffix,'(transport=*)',$arr);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			print_r($hash);
		}
	}
	
	
	function Hash_associated_domains($ou=null){
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		if($ou<>null){
			$suffix="ou=$ou,dc=organizations,$this->suffix";
		}else{$suffix=$this->suffix;}
		writelogs('pattern:(associatedDomain=*) in ' . $suffix,__CLASS__ . "/" . __FUNCTION__,__FILE__ );
		$arr=array("associatedDomain");
		$sr =@ldap_search($ld,$suffix,'(associatedDomain=*)',$arr);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			writelogs("pattern: " . count($hash) . " entries",__CLASS__ . "/" . __FUNCTION__,__FILE__,__LINE__ );
			for($i=0;$i<$hash["count"];$i++){
				for($j=0;$j<$hash[$i]["associateddomain"]["count"];$j++){
					writelogs("pattern: {$hash[$i]["associateddomain"][$j]} entries",__CLASS__ . "/" . __FUNCTION__,__FILE__,__LINE__ );
					$res[$hash[$i]["associateddomain"][$j]]="associateddomain";
				}
			}
		}
		return $res;
	}
	
	
	function ou_by_smtp_domain($domain){
		
		$sr =@ldap_search($this->ldap_connection,$this->suffix,"(associatedDomain=$domain)",array());
		if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);
			for($i=0;$i<$hash['count'];$i++){
				if(preg_match("#ou=(.+?),dc=organizations,$this->suffix#",$hash[$i]["dn"],$re)){
					return $re[1];
				}
			}
			
		}
		
		$sr =@ldap_search($this->ldap_connection,$this->suffix,"(&(objectclass=transportTable)(cn=$domain))",array());
		if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);
			for($i=0;$i<$hash['count'];$i++){
				if(preg_match("#ou=(.+?),dc=organizations,$this->suffix#",$hash[$i]["dn"],$re)){
					return $re[1];
				}
			}
			
		}
		
		
	}
	
	
	function Hash_domains_table($ou=null){
		
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		if($ou<>null){$suffix="ou=$ou,dc=organizations,$this->suffix";}else{$suffix=$this->suffix;}
		
		//--------------------- associatedDomain
		
		
		$arr=array("associatedDomain");
		
		
		$sr =@ldap_search($ld,$suffix,'(associatedDomain=*)',$arr);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			for($i=0;$i<$hash["count"];$i++){
				for($j=0;$j<$hash[$i]["associateddomain"]["count"];$j++){
					$res[$hash[$i]["associateddomain"][$j]]="associateddomain";
					$log=$log . $hash[$i]["associateddomain"][$j].";";
				}
			}
		}	
		
		
		//--------------------- TransportTable
		$log="";
		$arr=array("cn","dn");
		$sr =@ldap_search($ld,$suffix,'(&(objectclass=transportTable)(cn=*))',$arr);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			for($i=0;$i<$hash["count"];$i++){
				$dom=$hash[$i]["cn"][0];
				$log=$log . $dom.";";
				if(preg_match("#(.+?)@(.+)#",$dom)){continue;}
				if(preg_match("#vacation\.#",$dom)){continue;}
				$res[$dom]="dn";
			}
		}
		return $res;	
		
	}
	
	function delete_all_VirtualDomainsMapsMTA($ou){
		$hash=$this->OUDatas($ou);
		if(is_array($hash["VirtualDomainsMapsMTA"])){
			while (list ($num, $domain) = each ($hash["VirtualDomainsMapsMTA"]) ){	
				$domain=str_replace('@','',$domain);
				$this->delete_VirtualDomainsMapsMTA($ou,$domain);		
			}
		}
	}
	
	function delete_VirtualDomainsMapsMTA($ou,$domain){
		$dn="ou=$ou,dc=organizations,$this->suffix";
		writelogs("Delete VirtualDomainsMapsMTA $domain in $dn",__FUNCTION__,__FILE__);
		$array["VirtualDomainsMapsMTA"]="@$domain";
		$this->Ldap_del_mod($dn,$array);
		
	}
	
	function add_domains_to_VirtualDomainsMapsMTA($ou,$domain){
		$dn="ou=$ou,dc=organizations,$this->suffix";
		if($ou==null){writelogs("unable to open null ou...'",__FUNCTION__,__FILE__);return false;}
		$hash=$this->OUDatas($ou);
		$upd["VirtualDomainsMapsMTA"][]="@$domain";
		
		if($hash["VirtualDomainsMapsMTA"]["@$domain"]==null){
			$this->Ldap_add_mod($dn,$upd);
		}
		
		
	}
		
	function hash_get_domains_ou($ou){
		$hash=$this->Hash_domains_table($ou);
		if(is_array($hash)){
			while (list ($num, $ligne) = each ($hash) ){
				$res[$num]=$num;
			}
		}
		return $res;
		}
		
	function LOCAL_SID(){
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$ini->loadString($sock->GET_INFO("SambaSMBConf"));
		$filter="(&(objectClass=sambaDomain)(sambaDomainName=" . strtoupper($ini->_params["global"]["workgroup"])."))";
		$arr=array("sambaSID");
		$sr =@ldap_search($this->ldap_connection,$this->suffix,$filter,$arr);
		if ($sr) {
				$hash=ldap_get_entries($this->ldap_connection,$sr);
				$sid=$hash[0]["sambasid"][0];
				
			}
			
		if($sid==null){
			$sock=new sockets();
			$sid=$sock->getfile('GetLocalSid');
				
		}
	
		
		return $sid;
	}
		
		
		function domains_get_locals_domains($ou){
			$hash=$this->Hash_domains_table($ou);
			if(!is_array($hash)){return array();}
			while (list ($num, $ligne) = each ($hash) ){
				if($ligne=="associateddomain"){
					$res[$num]=true;
				}
			}
			return $res;
		}
		
		
		function domainsDelete($ou,$ArrayNum){
			$table=$this->hash_get_domains_ou($ou);
			//associatedDomain;
			unset($table[$ArrayNum]);
			while (list ($num, $ligne) = each ($table) ){
				$update_array["associatedDomain"][]=$ligne;
			}
			
			$this->Ldap_modify("ou=$ou,dc=organizations,$this->suffix",$update_array);
			
		}
		
		
	function Hash_get_users_groups($ou){
		$pos=strpos($ou,',');
		if($pos>0){$ou=substr($ou,0,$pos);}		
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		$suffix="ou=$ou,dc=organizations,$this->suffix";
		$arr=array("memberUid");
		$sr =@ldap_search($ld,$suffix,'(objectclass=posixGroup)',$arr);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			for($i=0;$i<$hash["count"];$i++){
				for($t=0;$t<$hash[$i]["memberuid"]["count"];$t++){
					$array_users[$hash[$i]["memberuid"][$t]]=$hash[$i]["memberuid"][$t];
				}
				
			}

		}
		return $array_users;
		
		}	
		
	function COUNT_DE_USERS(){
		if($this->ldapFailed){return 0;}
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		$suffix=$this->suffix;	
		$arr=array("cn","displayname");
		$sr = @ldap_search($ld,"dc=organizations,$suffix",'(objectclass=userAccount)',$arr);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			return $hash["count"];
		}
		
		return 0;
			
	}
	
		
	function hash_get_users_Only_ou($ou){
		$pos=strpos($ou,',');
		if($pos>0){$ou=substr($ou,0,$pos);}		
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		$suffix="ou=$ou,dc=organizations,$this->suffix";

		$arr=array("uid");
		$hashGroups=$this->Hash_get_users_groups($ou);
		
		writelogs(count($hashGroups) ." groups",__FUNCTION__,__FILE__);
		$sr =@ldap_search($ld,$suffix,'(objectclass=userAccount)',$arr);
		if ($sr) {
			
			$hash=ldap_get_entries($ld,$sr);
			
			for($i=0;$i<$hash["count"];$i++){
				$userid=$hash[$i]["uid"][0];
				if($hashGroups[$userid]==null){
					$res[]=$userid;
				}
			}
		}
		return $res;
		}		
		
	function hash_users_ou($ou=null){
		$pos=strpos($ou,',');
		if($pos>0){$ou=substr($ou,0,$pos);}		
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		$suffix=$this->suffix;	
		$dn="ou=$ou,dc=organizations,$suffix";
		if(posix_getuid()==0){
			if($ou==null){
				$dn="dc=organizations,$suffix";
			}
		}
		
		$arr=array("cn","dn","uid","displayname");
		$sr = @ldap_search($ld,$dn,'(objectclass=userAccount)',$arr);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			while (list ($num, $ligne) = each ($hash) ){
				$displayname=trim($ligne["displayname"][0]);
				
				if($displayname==null){$displayname=$ligne["uid"][0];}
				$res[$ligne["uid"][0]]=$displayname;
			}
		}
		return $res;
	}
	
	function hash_get_local_domains(){
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		$suffix=$this->suffix;
		writelogs($suffix . '= >objectclass=domainRelatedObject)(associatedDomain=*))',__CLASS__.'/'.__FUNCTION__,__FILE__);
		$filter=array("associateddomain");
		
		$sr = @ldap_search($ld,"dc=organizations,$suffix",'(&(objectclass=domainRelatedObject)(associatedDomain=*))',$filter);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			
			for($i=0;$i<$hash["count"];$i++){
				for($t=0;$t<$hash[$i]["associateddomain"]["count"];$t++){
					$xdom=$hash[$i]["associateddomain"][$t];
					if($xdom==null){continue;}
					$arr[$xdom]=$xdom;
				}
				
			}
		}
		return $arr;		
	}
	
//-------------------------------------------------------------------------------------------------------------------------------------------------------------------------	
	
	function hash_get_relay_domains(){
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		$dn="cn=relay_domains,cn=artica,$this->suffix";
		$sr = @ldap_search($this->ldap_connection,$dn,'(&(objectclass=PostFixRelayDomains)(cn=*))');
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			if(!is_array($hash)){return null;}
			for($i=0;$i<$hash["count"];$i++){
				$arr[$hash[$i]["cn"][0]]=$hash[$i]["cn"][0];
			}
		}
		return $arr;		
	}
//-------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	
	function hash_get_relay_recipients(){
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		$dn="cn=relay_recipient_maps ,cn=artica,$this->suffix";
		$sr = @ldap_search($this->ldap_connection,$dn,'(&(objectclass=PostfixRelayRecipientMaps)(cn=*))');
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			if(!is_array($hash)){return null;}
			for($i=0;$i<$hash["count"];$i++){
				$arr[$hash[$i]["cn"][0]]=$hash[$i]["cn"][0];
			}
		}
		return $arr;		
	}
//-------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	
	function hash_Smtp_Tls_Policy_Maps(){
		$base_dn="cn=smtp_tls_policy_maps,cn=artica,$this->suffix";
		$arr1=array("SmtpTlsPolicyMapsValue");
		$search_syntax="(&(objectclass=SmtpTlsPolicyMaps)(SmtpTlsPolicyMapsValue=*))";
		$sr =@ldap_search($this->ldap_connection,$base_dn,$search_syntax,$arr1);
		if ($sr) {
			
			$hash=ldap_get_entries($this->ldap_connection,$sr);
		
			for($i=0;$i<$hash["count"];$i++){
				$ligne=$hash[$i];
				$dn=$ligne["dn"];
				$server=$ligne['smtptlspolicymapsvalue'][0];
				if($dn<>null){
					if(preg_match('#cn=(.+?),cn=#',$dn,$re)){
						$xdom=$re[1];
						if($xdom<>null){
							if(!is_array($xdom)){
								$arr[$xdom]=$server;
							}
						}
					}
				}
			}
			
		}else{
			writelogs($base_dn .$search_syntax.' failed search',__CLASS__.'/'.__FUNCTION__,__FILE__);
			writelogs("ERROR ldap_search :  " . ldap_err2str(ldap_errno($this->ldap_connection)),__CLASS__ . "/" . __FUNCTION__,__FILE__);
		}
		return $arr;
	}	
	
	function hash_Sender_Dependent_Relay_host(){
		$base_dn="cn=Sender_Dependent_Relay_host_Maps,cn=artica,$this->suffix";
		$arr1=array("SenderRelayHost");
		$sr =@ldap_search($this->ldap_connection,$base_dn,'(&(objectclass=senderDependentRelayhostMaps)(SenderRelayHost=*))',$arr1);
		if ($sr) {
			
			$hash=ldap_get_entries($this->ldap_connection,$sr);
		
			for($i=0;$i<$hash["count"];$i++){
				$ligne=$hash[$i];
				$dn=$ligne["dn"];
				$server=$ligne['senderrelayhost'][0];
				if($dn<>null){
					if(preg_match('#cn=(.+?),cn=#',$dn,$re)){
						$xdom=$re[1];
						if($xdom<>null){
							if(!is_array($xdom)){
								$arr[$xdom]=$server;
							}
						}
					}
				}
			}
			
		}else{
			writelogs($base_dn . '(&(objectclass=senderDependentRelayhostMaps)(SenderRelayHost=*)) failed search',__CLASS__.'/'.__FUNCTION__,__FILE__);
			writelogs("ERROR ldap_search :  " . ldap_err2str(ldap_errno($this->ldap_connection)),__CLASS__ . "/" . __FUNCTION__,__FILE__);
		}
		return $arr;
	}
	
	
	
	function hash_get_smtp_connection_cache_destinations(){
		$dn="cn=smtp_connection_cache_destinations,cn=artica,$this->suffix";
		$sr = @ldap_search($this->ldap_connection,"$dn",'objectClass=PostFixSmtpConnectionCacheDestinations',array("cn"));
		if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);
			for($i=0;$i<$hash["count"];$i++){
				$res[$hash[$i]["cn"][0]]=$hash[$i]["cn"][0];
			}
		}else{
			writelogs("ERROR search :  $dn " . ldap_err2str(ldap_errno($this->ldap_connection)),__CLASS__ . "/" . __FUNCTION__,__FILE__);return false;
		}
		return $res;
	}
	
	function hash_get_class_RestrictionTableAction($dn){
		if($this->ldapFailed){return array();}
		$hash=$this->Ldap_read($dn,'(ObjectClass=*)',array(strtolower('PostFixRestrictionTableAction')));
		if(!is_array($hash)){writelogs("no rows...",__CLASS__ . "/".__FUNCTION__,__FILE__);return array();}
		$vals= $hash[0][strtolower("PostFixRestrictionTableAction")][0];
		if(strpos($vals,',')>0){
			return explode(",",$vals);
			}else{
				return array($vals);
			}
		}
		
	function hash_get_all_local_domains(){
		if($this->ldapFailed){return array();}
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		$suffix="dc=organizations,$this->suffix";
		$sr = @ldap_search($ld,$suffix,'(&(objectclass=domainRelatedObject)(associatedDomain=*))');
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			for($i=0;$i<$hash["count"];$i++){
				for($z=0;$z<$hash[$i]["associateddomain"]["count"];$z++){
					$xdom=$hash[$i]["associateddomain"][$z];
					if(preg_match('#^vacation\.#',$xdom)){continue;}
					$arr[$xdom]=$xdom;
					}
			}
			
		}else{
				writelogs("Could not find any domain in $suffix",__FUNCTION__.'/'.__CLASS__,__FILE__);
		}
			
		return $arr;
	}

	
	function hash_get_all_domains(){
		if($this->ldapFailed){return array();}
		if(is_array($GLOBALS["hash_get_all_domains"])){return $GLOBALS["hash_get_all_domains"];}
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		$arr=array();
		$filters=array("associateddomain");
		$suffix="dc=organizations,$this->suffix";
		$sr = @ldap_search($ld,$suffix,'(&(objectclass=domainRelatedObject)(associatedDomain=*))',$filters);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			for($i=0;$i<$hash["count"];$i++){
				for($z=0;$z<$hash[$i]["associateddomain"]["count"];$z++){
					$xdom=$hash[$i]["associateddomain"][$z];
					if(preg_match('#^vacation\.#',$xdom)){continue;}
					
					$arr[$xdom]=$xdom;
					}
			}
			
		}else{
				writelogs("Could not find any domain in $suffix",__FUNCTION__.'/'.__CLASS__,__FILE__);
		}

		
		$sr = @ldap_search($ld,$suffix,'(&(objectclass=transportTable)(cn=*))',array('cn'));
		if ($sr) {
				$hash=ldap_get_entries($ld,$sr);
				
				if(!is_array($hash)){
					$GLOBALS["hash_get_all_domains"]=$arr;
					return $arr;
				}
				while (list ($num, $ligne) = each ($hash) ){
					$xdom=trim($ligne["cn"][0]);
					if($xdom==null){continue;}
					if(preg_match('#^vacation\.#',$xdom)){continue;}
					$arr[$xdom]=$xdom;
					}
		}
		$GLOBALS["hash_get_all_domains"]=$arr;
		return $arr;
		
		}

		function hash_groups($ou,$fieldlist=0){
		if($this->ldapFailed){return array();}
		if($ou==null){return array();}
		$pos=strpos($ou,',');
		if($pos>0){$ou=substr($ou,0,$pos);}
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		$cols=array("cn","description",'gidnumber');
		$dn="ou=groups,ou=$ou,dc=organizations,$this->suffix";
		$sr = @ldap_search($ld,"$dn",'objectclass=posixGroup',$cols);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			if(!is_array($hash)){return array();}
			while (list ($num, $ligne) = each ($hash) ){
				if($ligne["cn"][0]<>null){
					switch ($fieldlist) {
						case 1:$res[$ligne["gidnumber"][0]]=$ligne["cn"][0];break;
						case 2;$res=$res . "{$ligne["gidnumber"][0]};{$ligne["cn"][0]}\n";break;
						default:$res[$ligne["cn"][0]]=array("gid"=>$ligne["gidnumber"][0],"description"=>$ligne["description"][0]);break;
					}
				}
				
			}
		}else{
			writelogs("Unable to find groups in $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
		writelogs(count($res). " groups found for $ou ($dn)",__CLASS__.'/'.__FUNCTION__,__FILE__);
		return $res;	
		}
		
		function GroupAllDatas($gidNumber){
			if($this->ldapFailed){return array();}
			if($gidNumber==null){return null;}
			$sr =@ldap_search($this->ldap_connection,$this->suffix,"(gidNumber=$gidNumber)");
			if($sr){
				$result = ldap_get_entries($this->ldap_connection, $sr);
				$res=$this->_ReturnArray($result[0]);
				return $res;
			}
		}
		
		function GroupGetMembersCount($gidNumber){
			if($this->ldapFailed){return array();}
			$attrs=array('memberUid','cn');
			$sr =@ldap_search($this->ldap_connection,$this->suffix,"(&(objectClass=posixGroup)(gidNumber=$gidNumber))",$attrs);
			if($sr){
				$result = ldap_get_entries($this->ldap_connection, $sr);
				$res=$result[0]["memberuid"]["count"];
				if(!is_numeric($res)){$res=0;}
				return $res;
			}else{return 0;}
			
		}
		
		function UserGetGroups($userid,$reverse=0){
		if($this->ldapFailed){return array();}
		if($userid==null){return null;}	
		$attrs=array('gidNumber','cn');
		$sr =@ldap_search($this->ldap_connection,$this->suffix,"(&(objectClass=posixGroup)(memberUid=$userid))",$attrs);	
			if($sr){
				$result = ldap_get_entries($this->ldap_connection, $sr);
				if(!is_array($result)){return array();}
				for($i=0;$i<$result["count"];$i++){
					if($reverse==1){$res[$result[$i]["gidnumber"][0]]=$result[$i]["cn"][0];}else{$res[$result[$i]["cn"][0]]=$result[$i]["gidnumber"][0];}
					
				}
				
			writelogs("$userid is in " . count($res)." groups ",__CLASS__.'/'.__FUNCTION__,__FILE__);	
			return  $res;}	
		}
		
		
	function UserDeleteToGroup($userid,$gid){
		$group_datas=$this->GroupDatas($gid);
		
		
		if( count($group_datas["members"])==0){
			$update_array["memberUid"]=$userid;
			writelogs("This group have no members, delete  memberUid object",__FUNCTION__,__FILE__);
			$this->Ldap_del_mod($group_datas["dn"],$update_array);
			if(trim($ldap->ldap_last_error)<>null){echo $ldap->ldap_last_error;}
			return false;
			}
	$update_array["memberUid"]=$userid;
	if(!$this->Ldap_del_mod($group_datas["dn"],$update_array)){
			if($ldap->ldap_last_error<>null){return false;}
	}
		return true;
	}
	
function AddMemberGroup(){
	$usr=new usersMenus();
	$tpl=new templates();
	if($usr->AllowAddGroup==false){
		writelogs("The administrator have no provileges to execute this operation....",__FUNCTION__,__FILE__);
		echo $tpl->_ENGINE_parse_body('{no_privileges}');echo Page($_GET["user"]);exit;
		}
		
	if(trim($_GET["AddMemberGroup"]==null)){return null;}
	$ldap=new clladp();
	$ldap->AddUserToGroup($_GET["AddMemberGroup"],$_GET["user"]);
	if(trim($ldap->ldap_last_error<>null)){echo $ldap->ldap_last_error;}
}


function HashMembersFromOU($ou){
	if($this->ldapFailed){return ;}
	$dn="ou=$ou,dc=organizations,$this->suffix";
	$attrs=array("uid");
	$filter="(&(objectClass=userAccount)(cn=*))";
	$h=$this->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$h["count"];$i++){
		$user[$h[$i]["uid"][0]]=$h[$i]["uid"][0];
		
	}
	
	return $user;
	
	
}
		
		function UserSearch($ou=null,$tofind){
			
			$uid=$_SESSION["uid"];
			if($tofind==null){$tofind='*';}else{$tofind="*$tofind*";}
		
			$filter="(&(objectClass=userAccount)(|(cn=$tofind)(mail=$tofind)(displayName=$tofind)(uid=$tofind) (givenname=$tofind) ))";
			$attrs=array("displayName","uid","mail","givenname","telephoneNumber","title","sn","mozillaSecondEmail","employeeNumber");
			if($ou==null){$dn=$this->suffix;}else{$dn="ou=$ou,dc=organizations,$this->suffix";}
			$hash=$this->Ldap_search($dn,$filter,$attrs,20);
			
			
			
			if($ou==null){$dn=$this->suffix;}else{$dn="ou=$uid,ou=People,dc=$ou,dc=NAB,$this->suffix";}
			$filter="(&(objectClass=evolutionPerson)(|(cn=$tofind)(mail=$tofind)(displayName=$tofind)(sn=$tofind)(givenname=$tofind)(mozillaSecondEmail=$tofind)(fileAs=$tofind) ))";
			writelogs("Search in $dn for contact",__CLASS__.'/'.__FUNCTION__,__FILE__);
			writelogs("$filter",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$hash2=$this->Ldap_search($dn,$filter,$attrs,20);
			writelogs("Address book contains ".count($hash2). " rows ",__CLASS__.'/'.__FUNCTION__,__FILE__);
			
			
			return array($hash,$hash2);

		}
		
		function UserFromAuth($name){
			$filter="(&(objectClass=UserArticaClass)(ArticaInterfaceLogon=$name))";
			$attrs=array("displayName","uid","mail","cn","Phone");
			if($ou==null){$dn=$this->suffix;}else{$dn="ou=$ou,dc=organizations,$this->suffix";}
			$hash=$this->Ldap_search($dn,$filter,$attrs);
			return $hash;
			
		}		
		
		function AllGroupsKavSettings(){
			$fl=array('KasperkyAVScanningDatas');
			$sr =@ldap_search($this->ldap_connection,$this->suffix,"(&(objectClass=posixGroup)(KasperkyAVScanningDatas=*))",$fl);
			if($sr){
				$result = ldap_get_entries($this->ldap_connection, $sr);
				for($i=0;$i<$result["count"];$i++){
					$conf[]=$result[$i]["kasperkyavscanningdatas"][0];
				}
			}else{writelogs("ldap_search return null for filter (&(objectClass=posixGroup)(KasperkyAVScanningDatas=*))");}
			
			return $conf;
			
		}
		
		function CyrusPassword($uid="cyrus"){
			$sr =@ldap_search($this->ldap_connection,"dc=organizations,$this->suffix","(cn=$uid)");
			if(!$sr){
				writelogs("Unable to find : Cyrus admin",__CLASS__.'/'.__FUNCTION__,__FILE__);
				return null;
			}

		$entry_id = ldap_first_entry($this->ldap_connection,$sr);
		if(!$entry_id){
			writelogs( 'INFOS: bad value $entry_id: (' . $entry_id . ')  find: (cn=cyrus) -> aborting function search engine doesn`t found the pattern',__CLASS__.'/'.__FUNCTION__,__FILE__);
			if($this->CyrusAdminCreate()){return $this->ldap_password;}
		}
		$attrs = ldap_get_attributes($this->ldap_connection, $entry_id);
		return $attrs["userPassword"][0];
			
		}
		
		
		function UserDatas($userid){
		if($this->ldapFailed){return null;}	
		if($userid==null){return null;}
		if($userid==-100){return null;}
		writelogs("Search query:(uid=$userid)",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sr =@ldap_search($this->ldap_connection,$this->suffix,"(uid=$userid)");
		if(!$sr){
			writelogs("Unable to find : $userid",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return null;
		}
		$entry_id = ldap_first_entry($this->ldap_connection,$sr);
		if(!$entry_id){
			writelogs( 'INFOS: bad value $entry_id: (' . $entry_id . ')  find: (uid=' . $userid . ') -> doesn`t found the pattern',__CLASS__.'/'.__FUNCTION__,__FILE__);
			return null;
		}
		$attrs = ldap_get_attributes($this->ldap_connection, $entry_id);
		$dn=$this->_Get_dn_userid($userid);
		$res=$this->_ReturnArray($attrs);
		
		$res["ARRAY_KEYS"]=$this->_ReturnArrayKeys($attrs);
		$classes=$this->getobjectClass(null,$dn);
		if($classes["ArticaSettings"]==null){$upd["objectClass"][]="ArticaSettings";}
		
		
		if($res["ARRAY_KEYS"]["KasperkyASDatasRules"]==null){
			$upd["KasperkyASDatasRules"][0]="DEFAULT";
			$this->Ldap_add_mod($dn,$upd);
		}
		if($res["ARRAY_KEYS"]["ArticaUserFilterRule"]==null){
			unset($upd);
			$upd["ArticaUserFilterRule"][]="DEFAULT";
			$this->Ldap_add_mod($dn,$upd);
		}
		
if($res["ARRAY_KEYS"]["FTPStatus"]==null){
			unset($upd);
			$upd["objectClass"]="PureFTPdUser";
			$upd["FTPStatus"]="FALSE";
			$upd["FTPQuotaMBytes"]="1000";
			$upd["FTPQuotaFiles"]="50";
			$upd["FTPDownloadBandwidth"]="550";
			$upd["FTPUploadBandwidth"]="550";
			$upd["FTPDownloadRatio"]="5";
			$upd["FTPUploadRatio"]="1"; 			
			$this->Ldap_add_mod($dn,$upd);
			writelogs("Adding ftp settings: dn=$dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$res["FTPStatus"]="FALSE";
			$res["FTPQuotaMBytes"]="1000";
			$res["FTPQuotaFiles"]="50";
			$res["FTPDownloadBandwidth"]="550";
			$res["FTPUploadBandwidth"]="550";
			$res["FTPDownloadRatio"]="5";
			$res["FTPUploadRatio"]="1";			
			}else{	
				$res["FTPStatus"]=$attrs["FTPStatus"][0];
				$res["FTPQuotaMBytes"]=$attrs["FTPQuotaMBytes"][0];
				$res["FTPQuotaFiles"]=$attrs["FTPQuotaFiles"][0];
				$res["FTPDownloadBandwidth"]=$attrs["FTPDownloadBandwidth"][0];
				$res["FTPUploadBandwidth"]=$attrs["FTPUploadBandwidth"][0];
				$res["FTPDownloadRatio"]=$attrs["FTPDownloadRatio"][0];
				$res["FTPUploadRatio"]=$attrs["FTPUploadRatio"][0];			
			}
		if($res["displayName"]==null){$res["displayName"]=$res["givenName"] . " " . $res["sn"];}
		if($res["displayName"]==null){	$res["displayName"]=$res["uid"];}
		$res["privileges"]=$this->_Get_privileges_userid($userid);	
		$res["dn"]=$dn;
		$res["groups"]=$this->UserGetGroups($userid);
		
		if(preg_match('#ou=users,ou=(.+?),#',$res["dn"],$reg)){$res["ou"]=$reg[1];}

		$res["SenderCanonical"]=$attrs["SenderCanonical"][0];
		
		for($i=0;$i<$attrs["mailAlias"]["count"];$i++){
			$aliases[]=$attrs["mailAlias"][$i];
		}
		
		for($i=0;$i<$attrs["ArticaUserFilterRule"]["count"];$i++){
			$res["RegexRules"][]=$attrs["ArticaUserFilterRule"][$i];
		}
		
		
		
		$res["mailAlias"]=$aliases;
		$res["telephoneNumber"]=$attrs["telephoneNumber"][0];
		$res["mobile"]=$attrs["mobile"][0];;
		$res["title"]=$attrs["title"][0];
		$res["CP"]=$attrs["postalCode"][0];
		$res["postalAddress"]=$attrs["postalAddress"][0];
		$res["street"]=$attrs["street"][0];
		$res["town"]=$attrs["l"][0];
		$res["BP"]=$attrs["postOfficeBox"];
		$res["SieveScriptsUser"]=$attrs["SieveScriptsUser"][0];
		$res["KasperkyASDatasRules"]=$attrs["KasperkyASDatasRules"];
		$res["homeDirectory"]=$attrs["homeDirectory"][0];
		
		
		for($i=0;$i<$attrs["FetchMailsRules"]["count"];$i++){
			$res["FetchMailsRulesSources"][]=$attrs["FetchMailsRules"][$i];
			$fetch[]=$this->ParseMyLdapInisData($attrs["FetchMailsRules"][$i]);
		}		
		
		$res["fetchmail"]=$fetch;
		
		for($i=0;$i<$attrs["KasperkyASDatasAllow"]["count"];$i++){
			$whitel[]=$attrs["KasperkyASDatasAllow"][$i];
		}
		$res["WhiteList"]=$whitel;
		for($i=0;$i<$attrs["KasperkyASDatasDeny"]["count"];$i++){
			$Blackl[]=$attrs["KasperkyASDatasDeny"][$i];
		}		
		
		$res["blacklist"]=$Blackl;
		return $res;
		}
		
		function ParseMyLdapInisData($datas){
			$tbl=explode("\n",$datas);
			while (list ($num, $ligne) = each ($tbl) ){
				$value=null;$key=null;
				if(preg_match('#\[(.+)\]#',$ligne,$reg1)){$key=$reg1[1];}
				if(preg_match('#"(.+)"#',$ligne,$reg1)){$value=$reg1[1];}
				$array[$key]=$value;
			}
			return $array;
			
		}
		
		function _Get_dn_userid($userid){
			if($userid==null){return null;}
			$userid=$this->ldap_escape($userid);
			$sr =@ldap_search($this->ldap_connection,$this->suffix,"(uid=$userid)");
			if($sr){$result = ldap_get_entries($this->ldap_connection, $sr);return trim($result[0]["dn"]);}
			else{
				writelogs("Unable to find dn (uid=$userid)",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}
		}
		
		function _Get_privileges_ou($userid=null,$ou=null){
			if($ou==null){
				$userdn=$this->_Get_dn_userid($userid);
				if(!preg_match("#ou=users,ou=(.+?),dc#",$userdn,$re)){return array();}
				$ou=$re[1];
			}
			
			
			$hash=$this->OUDatas($ou);
			$privs=$this->_ParsePrivieleges($hash["ArticaGroupPrivileges"]);
			return $privs;
		}

		
		function _Get_privileges_userid($userid){
			$org_priv=array();
			$filters=array("gidNumber","ArticaGroupPrivileges");
			$dr =ldap_search($this->ldap_connection,$this->suffix,"(&(objectClass=posixGroup)(memberUid=$userid))",$filters);	
			$result = ldap_get_entries($this->ldap_connection, $dr);
			if($result){
				for($i=0;$i<$result["count"];$i++){
					$privs=$this->_ParsePrivieleges($result[$i]["articagroupprivileges"][0]);
					$privs=$privs+$this->_Get_privileges_ou($userid);
					if(is_array($privs)){
						while (list ($num, $ligne) = each ($privs) ){
							if($ligne=="yes"){$org_priv[$num]="yes";}
							if($ligne=="1"){$org_priv[$num]="1";}
							if($num=="ForceLanguageUsers"){$org_priv[$num]=$ligne;}							
						}
						unset($privs);
					}
				}
				
				if(is_array($org_priv)){
					while (list ($num, $ligne) = each ($org_priv) ){
						//writelogs("PRIV:$num=$ligne",__CLASS__.'/'.__FUNCTION__,__FILE__);
						$conf=$conf."[$num]=\"$ligne\"\n";
					}
					
				}
			}
		return $conf;}
		
	function _ParsePrivieleges($content=null){
		$uid=posix_getuid();
		if(preg_match_all('#\[([a-zA-Z]+)\]="([a-z0-9]+)"#is',$content,$reg)){
				while (list ($num, $ligne) = each ($reg[1]) ){
					if($uid>0){writelogs("$ligne={$reg[2][$num]}",__CLASS__.'/'.__FUNCTION__,__FILE__);}
					$priv[$ligne]=$reg[2][$num];
					}}
			
			if(!is_array($priv)){return array();}		
			return $priv;
	}		
		
		
		function Hash_all_mailboxesActives(){
			$filters=array("uid","userPassword");
			$dr =ldap_search($this->ldap_connection,$this->suffix,"(&(objectClass=userAccount)(MailboxActive=TRUE))",$filters);	
			
			if($dr){
				$results = ldap_get_entries($this->ldap_connection,$dr);
				for($i=0;$i<$results["count"];$i++){
					
					$res[$results[$i]["uid"][0]]=$results[$i]["userpassword"][0];
					
				}
			return $res;
			}
			
		}
		
		
		function GroupMailAliases($gidNumber){
			
			$DefaultGid=$this->ArticaDefaultGroupGid();
			// default group.------------------------------------------------------------------------------------------------------
			if($gidNumber==$DefaultGid){
				$hashDOM=$this->AllDomains();
				while (list ($num, $ligne) = each ($hashDOM)){
					$email="*@$ligne";
					$aliases[$email]=$email;
				}
				return $aliases;
			}
			//------------------------------------------------------------------------------------------------------------------------------------
			
			$dr =ldap_search($this->ldap_connection,$this->suffix,"(&(objectClass=posixGroup)(gidNumber=$gidNumber))");	
			if($dr){
				$result = ldap_get_entries($this->ldap_connection, $dr);
				$members=$result[0]["memberuid"];
			}
			
			for($i=0;$i<$members["count"];$i++){
				$uid=$members[$i];
				$hash=$this->UserDatas($uid);
				$aliases[$hash["mail"]]=$hash["mail"];
				
				if(is_array($hash["mailAlias"])){
					while (list ($num, $ligne) = each ($hash["mailAlias"])){
						$aliases[$ligne]=$ligne;
					}
				}
		
			
		}
		return $aliases;
		}
		
			
		function _ReturnArrayKeys($array_search){
			if(!is_array($array_search)){return null;}
			$array_keys=array_keys($array_search);
			while (list ($num, $key) = each ($array_keys)){
				if(!is_numeric($key)){
				$return_array[$key]=true;}
			
		}
			
			
			return $return_array;	
		}
				
		
		function _ReturnArray($array_search){
			if(!is_array($array_search)){return null;}
			

			if(isset($array_search["count"])){if(is_array($array_search[0])){$array_search=$array_search[0];}}
			
			for($i=0;$i<$array_search["count"];$i++){
				$keys[]=$array_search[$i];
			}
			
			
			while (list ($num, $ligne) = each ($keys)){
				if($array_search[$ligne]["count"]==1){
					$return_array[$ligne]=$array_search[$ligne][0];
				}else{
					for($z=0;$z<$array_search[$ligne]["count"];$z++){
						$res[]=$array_search[$ligne][$z];
					}
					$return_array[$ligne]=$res;
				}
				
				
			}
			
			if(isset($array_search["dn"])){$return_array["dn"]=$array_search["dn"];}
			
			return $return_array;	
			
		}
		
		
function GroupForbiddenAttachment($attach_group,$gid,$add=false){
		$arr=array('FiltersExtensionsGroupName');
		$sr =@ldap_search($this->ldap_connection,$this->suffix,"(&(gidnumber=$gid)(objectClass=*))",$arr);
		
		if(!$sr){return null;}
		$entry_id =@ldap_first_entry($this->ldap_connection,$sr);
		$dn = ldap_get_dn($this->ldap_connection,$entry_id);
		$attrs =@ldap_get_attributes($this->ldap_connection, $entry_id);
		
		for($i=0;$i<$attrs["FiltersExtensionsGroupName"]["count"];$i++){
			$arr[$attrs["FiltersExtensionsGroupName"][$i]]="yes";
		}
		
		if($add==true){
			if($arr[$attach_group]==null){
			$upd["FiltersExtensionsGroupName"]=$attach_group;
			return $this->Ldap_add_mod($dn,$upd);
			}else{return false;}
		}
		
		if($add==false){
			$upd["FiltersExtensionsGroupName"]=$attach_group;
			return $this->Ldap_del_mod($dn,$upd);
		}
		
	}
		
	function ArticaDefaultGroupGid(){
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		$suffix=$this->suffix;		
		$sr =@ldap_search($this->ldap_connection,$suffix,"(cn=ArticaDefaultGroup)");
		if(!$sr){$this->AddGroup('ArticaDefaultGroup');}
		$sr =@ldap_search($this->ldap_connection,$suffix,"(cn=ArticaDefaultGroup)",array('gidNumber'));
		$entry_id = ldap_first_entry($this->ldap_connection,$sr);
		if($entry_id){
			$attrs = ldap_get_attributes($this->ldap_connection, $entry_id);
		}
		if($attrs["gidNumber"][0]==null){$this->AddGroup('ArticaDefaultGroup');return $this->ArticaDefaultGroupGid();}
		
		return $attrs["gidNumber"][0];
	}

		
	function GroupIDFromName($ou,$groupname){
		$dn="ou=$ou,dc=organizations,$this->suffix";
		$sr =@ldap_search($this->ldap_connection,$dn,"(&(cn=$groupname)(objectClass=posixGroup))",array('gidNumber'));
		if(!$sr){return null;}
		$entry_id = ldap_first_entry($this->ldap_connection,$sr);
		if($entry_id){
			$attrs = ldap_get_attributes($this->ldap_connection, $entry_id);
			return $attrs["gidNumber"][0];
		}		
		
	}
	
	function GroupDatas($gidnumber){
		include_once(dirname(__FILE__) .'/class.groups.inc');
		$group=new groups($gidnumber);
		return $group->main_array;		
		}
		        
//-------------------------------------------------------------------------------------------------------------------------------------		      
	function AddDomainEntity($ou=null,$domain){
		$domain=trim($domain);
		include_once(dirname(__FILE__) . "/class.artica.inc");
		$artica=new artica_general();
		
		if($artica->RelayType=="single"){$this->AddDomainTransport($ou,$domain,"127.0.0.1",25,'smtp');return null;}
	
		$suffix=$this->suffix;
		$object=$this->getobjectClass($ou);
		$cn="ou=$ou,dc=organizations,$suffix";
		
		if(!isset($object["domainRelatedObject"])){
			writelogs( "[domainRelatedObject] does not exists, add it",__FUNCTION__,__FILE__);
			$upd=$this->getobjectClass($ou,null,true);
			writelogs( "Edit objectClass \"$num\"",__FUNCTION__,__FILE__);
			$upd["objectClass"][]="domainRelatedObject";
			$upd["associatedDomain"]=$domain;
			if(!$this->Ldap_modify($cn,$upd)){
				writelogs("Failed add classes:" .explode("\n",$upd),__CLASS__.'/'.__FUNCTION__,__FILE__);
				return $this->ldap_last_error;
			}else{
				$sock=new sockets();
				$sock->getFrameWork("cmd.php?cyrus-reload=yes");
				return true;
			}
			
			
			
		}
		
		$hash=$this->Hash_associated_domains($ou);
		writelogs( "associatedDomain=". count($hash) . " entries",__FUNCTION__,__FILE__);
		
		while (list ($a, $b) = each ($hash)){
			writelogs( "associatedDomain=$a entries",__FUNCTION__,__FILE__);
			$update_array["associatedDomain"][]=$a;
		}
		
		writelogs( "associatedDomain=$domain entries",__FUNCTION__,__FILE__);
		$update_array["associatedDomain"][]=$domain;
		writelogs( "[associatedDomain]=$domain",__FUNCTION__,__FILE__);
		
		
		writelogs("Add domain \"$domain\" in $cn",__FUNCTION__,__FILE__);	
		if(!$this->Ldap_modify($cn,$update_array)){return $this->ldap_last_error;}
		}
//-------------------------------------------------------------------------------------------------------------------------------------
	function smtp_tls_policy_maps_add($transport_ip,$transport_port=null,$resolve_mx="yes",$tls_value){
		writelogs("$transport_ip:$transport_port tls value=$tls_value",__CLASS__."/".__FUNCTION__,__FILE__);
		$domainTool=new DomainsTools();
		$transport_ip=$domainTool->transport_maps_implode($transport_ip,$transport_port,'smtp',$resolve_mx);
		$transport_ip=str_replace("smtp:","",$transport_ip);
		
		if(!$this->ExistsDN("cn=smtp_tls_policy_maps,cn=artica,$this->suffix")){
				$upd['cn'][0]='smtp_tls_policy_maps';
				$upd['objectClass'][0]='SmtpTlsPolicyMaps';
				$upd['objectClass'][1]='top';
				$this->ldap_add("cn=smtp_tls_policy_maps,cn=artica,$this->suffix",$upd);	
				}
			$table=$this->hash_Smtp_Tls_Policy_Maps();		
			$update_array["SmtpTlsPolicyMapsValue"][0]=$tls_value;
			$new_dn="cn=$transport_ip,cn=smtp_tls_policy_maps,cn=artica,$this->suffix";
			writelogs("table[$transport_ip]=".$table[$transport_ip] . " tls value=$tls_value",__CLASS__."/".__FUNCTION__,__FILE__);
			if($table[$transport_ip]<>null){
				$this->Ldap_modify($new_dn,$update_array);
			}else {
				$update_array['cn'][0]=$transport_ip;
				$update_array['objectClass'][0]='SmtpTlsPolicyMaps';
				$update_array['objectClass'][1]='top';
				$this->ldap_add($new_dn,$update_array);		
				}
		}
//-------------------------------------------------------------------------------------------------------------------------------------

	function AddDomainTransport($ou=null,$domain,$transport_ip,$transport_port,$transport_type,$resolve_mx="yes"){
		if($transport_type==null){$transport_type="smtp";}
		writelogs("$ou,$domain,$transport_ip,$transport_port,$transport_type $resolve_mx",__FUNCTION__,__FILE__);
		$tool=new DomainsTools();
		$line=$tool->transport_maps_implode($transport_ip,$transport_port,$transport_type,$resolve_mx);	
		$this->AddTransportTable($domain,$line,$ou);
		return $line;
		
	}
	
	
	function ou_add_relay_domain($ou,$domain_name,$relayIP,$relayPort,$mx="no",$trusted_smtp_domain=0){
		$tpl=new templates();
		$trusted_smtp_domain=$_GET["trusted_smtp_domain"];
	
	$dn="cn=relay_domains,ou=$ou,dc=organizations,$this->suffix";
	if(!$this->ExistsDN($dn)){
		$upd['cn'][0]="relay_domains";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		$this->ldap_add($dn,$upd);
		unset($upd);		
		}
	
	$hashdoms=$this->hash_get_all_domains();
	if($hashdoms[$domain_name]<>null){
		echo $tpl->_ENGINE_parse_body('{error_domain_exists}');
		exit;
	}
	
	
		$dn="cn=$domain_name,cn=relay_domains,ou=$ou,dc=organizations,$this->suffix";	
		
		$upd['cn'][0]="$domain_name";
		$upd['objectClass'][0]='PostFixRelayDomains';
		$upd['objectClass'][1]='top';
		$this->ldap_add($dn,$upd);	
	
		$dn="cn=relay_recipient_maps,ou=$ou,dc=organizations,$this->suffix";
		if(!$this->ExistsDN($dn)){
			$upd['cn'][0]="relay_recipient_maps";
			$upd['objectClass'][0]='PostFixStructuralClass';
			$upd['objectClass'][1]='top';
			$this->ldap_add($dn,$upd);
			unset($upd);		
			}	
	
		if($trusted_smtp_domain==0){	
			$dn="cn=@$domain_name,cn=relay_recipient_maps,ou=$ou,dc=organizations,$ldap->suffix";
			$upd['cn'][0]="@$domain_name";
			$upd['objectClass'][0]='PostfixRelayRecipientMaps';
			$upd['objectClass'][1]='top';
			$this->ldap_add($dn,$upd);
		}		
	
		$dn="cn=transport_map,ou=$ou,dc=organizations,$ldap->suffix";
		if(!$this->ExistsDN($dn)){
			$upd['cn'][0]="transport_map";
			$upd['objectClass'][0]='PostFixStructuralClass';
			$upd['objectClass'][1]='top';
			$this->ldap_add($dn,$upd);
			unset($upd);		
			}	
	if($relayIP<>null){
		if($mx=="no"){$relayIP="[$relayIP]";}
		$dn="cn=$domain_name,cn=transport_map,ou=$ou,dc=organizations,$ldap->suffix";
		$upd['cn'][0]="$domain_name";
		$upd['objectClass'][0]='transportTable';
		$upd['objectClass'][1]='top';
		$upd["transport"][]="relay:$relayIP:$relayPort";
		$this->ldap_add($dn,$upd);			
		}		
		
		
		
	}
	
	
	
//-------------------------------------------------------------------------------------------------------------------------------------
	function AddTransportTable($domain,$transport,$ou=null){
		writelogs("$ou,$domain,$transport",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(!$this->ExistsDN("cn=transport_map,ou=$ou,dc=organizations,$this->suffix")){
			$upd['cn'][0]="transport_map";
			$upd['objectClass'][0]='PostFixStructuralClass';
			$upd['objectClass'][1]='top';
			$this->ldap_add("cn=transport_map,ou=$ou,dc=organizations,$this->suffix",$upd);
			unset($upd);
			}

		$dn="cn=$domain,cn=transport_map,ou=$ou,dc=organizations,$this->suffix";
		if($this->ExistsDN($dn)){
			$upd['transport'][0]=$transport;
			if(!$this->Ldap_modify($dn,$upd)){return false;}
			}else{
				$upd['cn'][0]=$domain;
				$upd['transport'][0]=$transport;
				$upd['objectClass'][0]='transportTable';
				$upd['objectClass'][1]='top';
				writelogs("add $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
				if(!$this->ldap_add($dn,$upd)){return false;}
			}
		
		unset($upd);
		
		if($ou<>null){
			$dn="cn=$domain,cn=relay_domains,ou=$ou,dc=organizations,$this->suffix";
			$upd['cn'][0]=$domain;
			$upd['objectClass'][0]='PostFixRelayDomains';
			$upd['objectClass'][1]='top';	
			if(!$this->ExistsDN($dn)){		
				writelogs("add $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
				if(!$this->ldap_add($dn,$upd)){return false;}
			}
			
		}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-transport-maps=yes");
		
		
		}
//-------------------------------------------------------------------------------------------------------------------------------------
	function AddDomainRelayTable($domain){
		if(!$this->ExistsDN("cn=relay_domains,cn=artica,$this->suffix")){
			$upd['cn'][0]="relay_domains";
			$upd['objectClass'][0]='PostFixStructuralClass';
			$upd['objectClass'][1]='top';
			$this->ldap_add("cn=relay_domains,cn=artica,$this->suffix",$upd);
			unset($upd);
			}
		$dn="cn=$domain,cn=relay_domains,cn=artica,$this->suffix";
		if(!$this->ExistsDN($dn)){
			$upd['cn'][0]="$domain";
			$upd['objectClass'][0]='PostFixRelayDomains';
			$upd['objectClass'][1]='top';
			$this->ldap_add($dn,$upd);
		}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-transport-maps=yes");
	}
//-------------------------------------------------------------------------------------------------------------------------------------
	function AddRecipientRelayTable($recipient){
		if(!$this->ExistsDN("cn=relay_recipient_maps,cn=artica,$this->suffix")){
			$upd['cn'][0]="relay_recipient_maps";
			$upd['objectClass'][0]='PostFixStructuralClass';
			$upd['objectClass'][1]='top';
			$this->ldap_add("cn=relay_recipient_maps,cn=artica,$this->suffix",$upd);
			unset($upd);
			}
		$dn="cn=$recipient,cn=relay_recipient_maps,cn=artica,$this->suffix";
		if(!$this->ExistsDN($dn)){
			$upd['cn'][0]="$recipient";
			$upd['objectClass'][0]='PostfixRelayRecipientMaps';
			$upd['objectClass'][1]='top';
			$this->ldap_add($dn,$upd);
	}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-transport-maps=yes");
	}
//-------------------------------------------------------------------------------------------------------------------------------------

	function ExistsDN($dn){
		$attrs=array();
		$dn=$dn;
		$search =@ldap_read($this->ldap_connection,$dn,'(objectClass=*)',$attrs);
		if (!$search){
			//echo "$dn. doesn't exists\n";
			return false;}
			else{
				
				return true;}
	
		}
//-------------------------------------------------------------------------------------------------------------------------------------		



function Add_Sender_Dependent_Relay_host($domainMail,$relay){
			$ldap=new clladp();
			if($domainMail==null){return null;}
			$table=$this->hash_Sender_Dependent_Relay_host();
			if(strpos($domainMail,'@')==0){$domainMail="@$domainMail";}
			
			if(!$this->ExistsDN("cn=Sender_Dependent_Relay_host_Maps,cn=artica,$this->suffix")){
				$upd['cn'][0]='Sender_Dependent_Relay_host_Maps';
				$upd['objectClass'][0]='senderDependentRelayhostMaps';
				$upd['objectClass'][1]='top';
				$this->ldap_add("cn=Sender_Dependent_Relay_host_Maps,cn=artica,$this->suffix",$upd);	
				}
				
			$update_array["SenderRelayHost"][0]=$relay;
			$new_dn="cn=$domainMail,cn=Sender_Dependent_Relay_host_Maps,cn=artica,$this->suffix";
			writelogs("table[$domainMail]=".$table[$domainMail],__CLASS__."/".__FUNCTION__,__FILE__);
			if($table[$domainMail]<>null){
				$this->Ldap_modify($new_dn,$update_array);
			}else {
				$update_array['cn'][0]=$domainMail;
				$update_array['objectClass'][0]='senderDependentRelayhostMaps';
				$update_array['objectClass'][1]='top';
				$this->ldap_add($new_dn,$update_array);		
				}
				
			}

//-------------------------------------------------------------------------------------------------------------------------------------	

function add_restriction_class($class_name,$class_type="PostFixRestrictionClasses",$description="no_description"){
		if(!$this->ExistsDN("cn=restrictions_classes,cn=artica,$this->suffix")){
					$upd['cn'][0]='restrictions_classes';
					$upd['objectClass'][0]="PostFixStructuralClass";
					$upd['objectClass'][1]='top';
					$this->ldap_add("cn=restrictions_classes,cn=artica,$this->suffix",$upd);	
					}
	$update_array['cn'][0]=$class_name;
	$update_array["PostFixRestrictionClassDescription"][0]=$description;
	$update_array['objectClass'][0]=$class_type;
	$update_array['objectClass'][1]='top';
	$this->ldap_add("cn=$class_name,cn=restrictions_classes,cn=artica,$this->suffix",$update_array);	
}
//-------------------------------------------------------------------------------------------------------------------------------------

function Hash_get_restrictions_classes_standard($class_name){
	$path="cn=$class_name,cn=restrictions_classes,cn=artica,$this->suffix";
	if(!$this->ExistsDN($path)){
		$this->add_restriction_class($class_name,"PostFixRestrictionStandardClasses");
	}
	
	
}


function Hash_get_restrictions_classes(){
	$path="cn=restrictions_classes,cn=artica,$this->suffix";
	$filter="(&(objectclass=PostFixRestrictionClasses)(cn=*))";
	$arr=array();
	writelogs("search $filter in $path",__CLASS__.'/'.__FUNCTION__,__FILE__);
	$sr = @ldap_search($this->ldap_connection,$path,$filter,$arr);
		if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);
			writelogs("Result='" . $hash["count"] . "' rows",__CLASS__.'/'.__FUNCTION__,__FILE__);
			for($i=0;$i<$hash["count"];$i++){
				$class_name=$hash[$i]["cn"][0];
				if($class_name<>"restrictions_classes"){
					writelogs("$class_name",__CLASS__.'/'.__FUNCTION__,__FILE__);
					$res[$class_name]=$class_name;
				}
			}
			return $res;
		}
}
//-------------------------------------------------------------------------------------------------------------------------------------



function Hash_get_restrictions_className($class_name){
	$path="cn=$class_name,cn=restrictions_classes,cn=artica,$this->suffix";
	writelogs("read path : $path",__CLASS__ . "/".__FUNCTION__,__FILE__);
	$hash=$this->Ldap_read($path,'(ObjectClass=*)',array('postfixrestrictionclasslist'));
	if(!is_array($hash)){writelogs("no rows...",__CLASS__ . "/".__FUNCTION__,__FILE__);return array();}
	for($i=0;$i<$hash[0]["postfixrestrictionclasslist"]["count"];$i++){
		$res[]=$hash[0]["postfixrestrictionclasslist"][$i];
		}
		return $res;
	}
//-------------------------------------------------------------------------------------------------------------------------------------

function RestrictionClassData($class_name,$debug=0){
	$path="cn=$class_name,cn=restrictions_classes,cn=artica,$this->suffix";
	writelogs("search path : $path",__CLASS__ . "/".__FUNCTION__,__FILE__);
	$sr=@ldap_search($this->ldap_connection,$path,"(&(ObjectClass=*)(cn=$class_name))",array());
	if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);	
			//print_r($hash);
			if($hash[0]["postfixrestrictionclassdescription"][0]==null){
				$update_array["PostFixRestrictionClassDescription"][]="no description";
				$this->Ldap_add_mod($path,$update_array);
				return array("description"=>"no description");
				}
				
			$count=$hash[0]['postfixrestrictionclasslist']["count"];	
			writelogs("$class_name, PostFixRestrictionClassList return $count rows",__CLASS__ . "/".__FUNCTION__,__FILE__);	
			if($hash[0]['postfixrestrictionclasslist']["count"]>0){
				for($i=0;$i<=$count;$i++){
					
					writelogs("$class_name, row $i return " . $hash[0]['postfixrestrictionclasslist'][$i],__CLASS__ . "/".__FUNCTION__,__FILE__);	
					$rule_single=$hash[0]['postfixrestrictionclasslist'][$i];
					$rulessaved[$rule_single]=$rule_single;
					
					if(preg_match('#(.+)?="(.+)?"#',$rule_single,$reg)){
					$rules_list[]=array("KEY"=>$reg[1],"datas"=>$reg[2]);}else{
						writelogs("$class_name, unable to preg_match $rule_single",__CLASS__ . "/".__FUNCTION__,__FILE__);		
					}
				}
				
			}
	
		return array("description"=>$hash[0]["postfixrestrictionclassdescription"][0],"rules"=>$rules_list,"rules_source"=>$rulessaved);
	}
}


function Hash_get_restrictions_classes_tables($class_name,$table_name){
	$path="cn=$table_name,cn=$class_name,cn=restrictions_classes,cn=artica,$this->suffix";
	writelogs("search path : $path",__CLASS__ . "/".__FUNCTION__,__FILE__);
	$sr = @ldap_search($this->ldap_connection,$path,'(&(objectclass=PostFixRestrictionCheckAccess)(cn=*))',array());
		if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);	
			for($i=0;$i<$hash["count"];$i++){
				$res[$hash[$i]["cn"][0]]=$hash[$i]["postfixrestrictiontableaction"][0];
			}
		}
		return $res;
		}

	function ADDUsers($ou,$cn,$update_array){
		$dn="cn=$cn,ou=$ou";
		$objects=$this->getobjectDNClass($dn);
		if(!isset($object["userAccount"])){
			if(!isset($object["UserArticaClass"])){
				$update_array["objectClass"][]="UserArticaClass";
			}
			$update_array["objectClass"][]="userAccount";
			$add=true;
		
		}
		
		if(!isset($object["top"])){$update_array["objectClass"][]="top";$add=true;}
		$dn=$this->dn_escape("$dn,$this->suffix");	
		if($add==true){
			ldap_add($this->ldap_connection,$dn,$update_array);
		}else{	ldap_modify($this->ldap_connection,$dn,$update_array);}

		}
//-------------------------------------------------------------------------------------------------------------------------------------		

	function _AddKasperskySettings($ou){
		
		$object=$this->getobjectClass($ou);
		if(!isset($object["KasperskySettings"])){
			writelogs( 'objectClass[0]=KasperskySettings',__FUNCTION__,__FILE__);
			$update_array["objectClass"][]="KasperskySettings";
		}
		$update_array["EnableKasperkyAVScanning"]='0';		
		writelogs( "[EnableKasperkyAVScanning]=no",__FUNCTION__,__FILE__);
		$suffix=$this->suffix;
		$cn="ou=$ou,dc=organizations,$suffix";
		$cn=$this->dn_escape($cn);	
		ldap_mod_add($this->ldap_connection,$this->dn_escape($cn),$update_array);		
		
	}
//-------------------------------------------------------------------------------------------------------------------------------------	

	
function load_mynetworks(){
		
		$dn="cn=mynetworks_maps,cn=artica,$this->suffix";
		$sr =@ldap_read($this->ldap_connection,$dn,'objectClass=PostfixMynetworks');
		if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);	
			for($i=0;$i<$hash[0]["mynetworks"]["count"];$i++){
				if(trim($hash[0]["mynetworks"][$i])==null){continue;}
					$array_mynetworks[]=$hash[0]["mynetworks"][$i];
				}
			}
	
	   return $array_mynetworks;
}
	

function WhitelistsFromDomain($domain=null){
	if($domain==null){$domain="*";}
	
	$filter="(&(objectClass=ArticaSettings)(mail=*@$domain)(amavisWhitelistSender=*))";
	$sr=@ldap_search($this->ldap_connection,$this->suffix,$filter,array("mail","amavisWhitelistSender"));
	if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);	
			for($i=0;$i<$hash["count"];$i++){
				$mail=$hash[$i]["mail"][0];
				for($j=0;$j<$hash[$i][strtolower("amavisWhitelistSender")]["count"];$j++){
					$res[$mail][]=$hash[$i][strtolower("amavisWhitelistSender")][$j];
				}
			}
	}
	
	
	$filter="(&(objectClass=ArticaSettings)(objectClass=PostFixStructuralClass)(cn=$domain)(kasperkyasdatasallow=*))";
	$sr=@ldap_search($this->ldap_connection,$this->suffix,$filter,array("cn","KasperkyASDatasAllow"));
	if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);	
			for($i=0;$i<$hash["count"];$i++){
				$mail=$hash[$i]["cn"][0];
				for($j=0;$j<$hash[$i]["kasperkyasdatasallow"]["count"];$j++){
					$res[$mail][]=$hash[$i]["kasperkyasdatasallow"][$j];
				}
			}
	}	
	
	return $res;
}
//-------------------------------------------------------------------------------------------------------------------------------------	
function BlackListFromDomain($domain=null){
	if($domain==null){$domain="*";}
	
	$filter="(&(objectClass=ArticaSettings)(mail=*@$domain)(amavisBlacklistSender=*))";
	$sr=@ldap_search($this->ldap_connection,$this->suffix,$filter,array("mail","amavisBlacklistSender"));
	if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);	
			for($i=0;$i<$hash["count"];$i++){
				$mail=$hash[$i]["mail"][0];
				for($j=0;$j<$hash[$i][strtolower("amavisBlacklistSender")]["count"];$j++){
					$res[$mail][]=$hash[$i][strtolower("amavisBlacklistSender")][$j];
				}
			}
	}
	
	
	$filter="(&(objectClass=ArticaSettings)(objectClass=PostFixStructuralClass)(cn=$domain)(KasperkyASDatasDeny=*))";
	$sr=@ldap_search($this->ldap_connection,$this->suffix,$filter,array("cn","KasperkyASDatasDeny"));
	if ($sr) {
			$hash=ldap_get_entries($this->ldap_connection,$sr);	
			for($i=0;$i<$hash["count"];$i++){
				$mail=$hash[$i]["cn"][0];
				for($j=0;$j<$hash[$i][strtolower("KasperkyASDatasDeny")]["count"];$j++){
					$res[$mail][]=$hash[$i][strtolower("KasperkyASDatasDeny")][$j];
				}
			}
	}	
	
	return $res;
}

function WhiteListsAddDomain($domain,$whitelist,$wbl=0){
	if(!$this->ExistsDN("cn=wlbl,cn=artica,$this->suffix")){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]="PostFixStructuralClass";
		$upd["cn"]="wlbl";
		$this->ldap_add("cn=wlbl,cn=artica,$this->suffix",$upd);
		unset($upd);
		}
		
	if(!$this->ExistsDN("cn=$domain,cn=wlbl,cn=artica,$this->suffix")){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='ArticaSettings';
		$upd["objectClass"][]="PostFixStructuralClass";
		$upd["cn"]="$domain";
		$this->ldap_add("cn=$domain,cn=wlbl,cn=artica,$this->suffix",$upd);
		unset($upd);		
	}
	
	if($wbl==0){
		$up["KasperkyASDatasAllow"]=$whitelist;
	}else{
		$up["KasperkyASDatasDeny"]=$whitelist;
	}
	if($this->Ldap_add_mod("cn=$domain,cn=wlbl,cn=artica,$this->suffix",$up)){
		if($wbl==0){$this->WhiteListsDelete($domain,$whitelist,1);}
		if($wbl==1){$this->WhiteListsDelete($domain,$whitelist,0);}
	}
	
}


function WhiteListsAddUser($uid,$whitelist,$wbl=0){
	include_once(dirname(__FILE__) . '/class.user.inc');
	$user=new user($uid);
	
if($wbl==0){
		$user->add_whitelist($whitelist);
		
	}else{
		$user->add_blacklist($whitelist);
	}		
		
}
	
	
function WhiteListsDelete($to,$from,$wbl=0){
include_once(dirname(__FILE__) . '/class.amavis.inc');
include_once(dirname(__FILE__) . '/class.user.inc');
	$amavis=new amavis();
		
if($wbl==0){
		$upd["KasperkyASDatasAllow"]=$from;
	}else{
		$upd["KasperkyASDatasDeny"]=$from;
	}
	
	writelogs("cn=$to,cn=wlbl,cn=artica,$this->suffix ?",__CLASS__ . '/' . __FUNCTION__,__FILE__);
	
	if($this->ExistsDN("cn=$to,cn=wlbl,cn=artica,$this->suffix")){
		writelogs("Yes, delete $from",__CLASS__ . '/' . __FUNCTION__,__FILE__);
		$this->Ldap_del_mod("cn=$to,cn=wlbl,cn=artica,$this->suffix",$upd);
		return ;
		}
	$uid=$this->uid_from_email($to);
	if($uid==null){return null;}
	$user=new user($uid);
	if($wbl==0){$user->del_whitelist($from);}else{$user->del_blacklist($from);}
	}
		
function getobjectDNClass($dn,$fulldn=0){
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		if($fulldn==0){$suffix=$dn. ',' . $this->suffix;}else{$suffix=$dn;}
		
		$arr=array("objectClass");
		$sr = @ldap_search($ld,$suffix,'(&(objectclass=*))',$arr);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);		
			if(is_array($hash[0]["objectclass"])){
				while (list ($num, $ligne) = each ($hash[0]["objectclass"]) ){
					$res[$ligne]=$ligne;
					}
			}
		}
		
	return $res;
	}	
	function getAllobjectsDN($dn,$fulldn=0){
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		if($fulldn==0){$suffix=$dn. ',' . $this->suffix;}else{$suffix=$dn;}
		
		
		$sr = @ldap_search($ld,$suffix,'(&(objectclass=*))');
		if ($sr) {
			$hash=@ldap_get_entries($ld,$sr);	
			for($i=0;$i<$hash[0]["count"];$i++){
				$res_arr[$hash[0][$i]]=$hash[0][$i];
			}
			
			
			if(is_array($hash[0]["objectclass"])){
				while (list ($num, $ligne) = each ($hash[0]["objectclass"]) ){
					$res[$ligne]=$ligne;
					}
			}
		}
	$res_arr["objectclass"]=$res;	
	return $res_arr;
	}	
	
	
	
	function getobjectClass($ou=null,$dn=null,$hasindex=false){
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		if($dn<>null){$suffix=$dn;}else{$suffix="ou=$ou,dc=organizations,$this->suffix";}
		writelogs("Get ObjectClass in $suffix",__FUNCTION__.'/'.__CLASS__,__FILE__);
		$arr=array("objectClass");
		$sr = @ldap_search($ld,$suffix,'(&(objectclass=*))',$arr);
		if ($sr) {
			$hash=@ldap_get_entries($ld,$sr);		
			if(is_array($hash[0]["objectclass"])){
				while (list ($num, $ligne) = each ($hash[0]["objectclass"]) ){
					if($hasindex){
						if(!is_numeric($ligne)){$res["objectClass"][]=$ligne;}
					}else{$res[$ligne]=$ligne;}
				}
			}
		}
			if(count($res)>0){
				writelogs("Get ObjectClass in $suffix return " . count($res). " classes " . implode("\n",$res),__FUNCTION__.'/'.__CLASS__,__FILE__);
			}else{
				writelogs("Get ObjectClass in $suffix return no classes ",__FUNCTION__.'/'.__CLASS__,__FILE__);
				return array();
			}
	return $res;
	}
	
  	function _GetUidNumber(){
  		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
      		$r =@ldap_search($ld, $this->suffix, 'uidnumber=*');
      		if ($r){
         		 ldap_sort($ld, $r, "uidnumber");
            	$result = ldap_get_entries($ld, $r);
         		$count = $result['count'];
         		$biguid = $result[$count-1]['uidnumber'][0];
         		return $biguid+1;
      		}
     		 return 0;
   	}
   	
  	function _GenerateGUID(){
  		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
      		$r =@ldap_search($ld, $this->suffix, 'gidnumber=*');
      		if ($r){
      			@ldap_sort($ld, $r, "gidnumber");
      			$result = ldap_get_entries($ld, $r);
			$count = $result['count'];
         			$biguid = $result[$count-1]['gidnumber'][0];
         			return $biguid+1;
      		}
     		 return 0;
   	}
   	
   	
   	
   	function GetDNValues($dn){	
   		$sr =@ldap_search($this->ldap_connection,$dn,'(&(objectclass=*))');
   		if ($sr) {
			return ldap_get_entries($ld,$sr);
   		}
   	}
   	
   	
   	function Ldap_modify($dn,$update_array){
   		if(!is_array($update_array)){
   			//$this->ldap_last_error="No datas in this command ! on line " . __LINE__ . " in file " .__FILE__;
   			error_log("No datas in this command !",__CLASS__ . "/" . __FUNCTION__,__FILE__);
   			return false;
   		}
   		$r=@ldap_modify($this->ldap_connection,$this->dn_escape($dn),$update_array);	
   		if(!$r){
   			
   			
   			$nolog=false;
   			if(ldap_errno($this->ldap_connection)<>0){
   				switch (ldap_errno($this->ldap_connection)) {
   					case 12:$add_error="\nPlease index your LDAP server\n";break;
   					case 20:$nolog=true;break;
   					case 0:$nolog=true;break;
   					case '0':$nolog=true;break;
   					default:$nolog=false;break;
   				}
   			}
   			if($nolog==false){
   				$this->ldap_last_error_num=ldap_errno($this->ldap_connection);
   				$this->ldap_last_error='Error number ' . ldap_errno($this->ldap_connection) . "\nAction:LDAP Modify\ndn:$dn\n" . 
   				ldap_err2str(ldap_errno($this->ldap_connection).$add_error);
   				$error_string="ERROR Number:" . ldap_errno($this->ldap_connection) . " Ldap_modify  \"dn=$dn\" :  " . ldap_err2str(ldap_errno($this->ldap_connection));
   				writelogs($error_string,__CLASS__ . "/" . __FUNCTION__,__FILE__);
				error_log($error_string);
   					while (list ($num, $ligne) = each ($update_array) ){
	   					if(is_array($ligne)){
	   						while (list ($a, $b) = each ($ligne) ){
	   							writelogs("ERROR $num($a)=$b",__CLASS__ . "/" . __FUNCTION__,__FILE__);
	   						}
	   					}else{
	   						writelogs("ERROR $num=$ligne",__CLASS__ . "/" . __FUNCTION__,__FILE__);
	   					}
	   					
	   				
	   			}
	   			return false;}
   			
   			return true;}
   		
   		return true;}
   		
	function Ldap_del_mod($dn,$update_array) {
		$r= @ldap_mod_del($this->ldap_connection,$this->dn_escape($dn),$update_array);
		if(!$r){
			$error_text=ldap_err2str(ldap_errno($this->ldap_connection));
			$error_num=ldap_errno($this->ldap_connection);
   			writelogs("ERROR Delete :  $dn :" . ldap_err2str(ldap_errno($this->ldap_connection)),__CLASS__ . "/" . __FUNCTION__,__FILE__);
			if($error_text="No such attribute"){return true;}
			$this->ldap_last_error="Error number $error_num\nAction:LDAP ldap_mod_del\ndn:$dn\nError: \"$error_text\"";
   			return false;
			}
   		return true;		
	}   

	
	function Ldap_rename_dn($newdn,$olddn,$parent_dn) {
		$r= @ldap_rename($this->ldap_connection,$olddn,$newdn,$parent_dn,true);
		if(!$r){
   			$this->ldap_last_error='Error number ' . ldap_errno($this->ldap_connection) . "\nAction:LDAP Ldap_rename_dn\ndn:$olddn -> $newdn\n" . ldap_err2str(ldap_errno($this->ldap_connection));
   			writelogs("ERROR rename DN :  $olddn to $newdn :" . ldap_err2str(ldap_errno($this->ldap_connection)),__CLASS__ . "/" . __FUNCTION__,__FILE__);return false;}
   		return true;		
	}  	
   		
	function Ldap_add_mod($cn,$update_array){
		$res=@ldap_mod_add($this->ldap_connection,$this->dn_escape($cn),$update_array);
		
  		if(!$r){
			$nolog=false;
			if(ldap_errno($this->ldap_connection)=="0"){return true;}
   			if(ldap_errno($this->ldap_connection)<>0){
   				switch (ldap_errno($this->ldap_connection)) {
   					case 20:$nolog=true;break;
   					case 21:$nolog=true;$this->__WriteLdapLogs($update_array,__FUNCTION__,$cn);return true;break;
   					case 0:$nolog=true;return true;break;
   					default:$nolog=false;break;
   				}
   			}
   			if($nolog==false){$this->__WriteLdapLogs($update_array,__FUNCTION__,$cn);return false;}
   			
   		return true;
  		
  		}
			return true;
   		}
   		
   		
   	function __WriteLdapLogs($update_array,$function,$dn=null){
   		$this->ldap_last_error='events ' . ldap_errno($this->ldap_connection) . "\nAction:LDAP add mod\ndn:$dn\n" . ldap_err2str(ldap_errno($this->ldap_connection));
   		writelogs("LDAP ERROR Number " . ldap_errno($this->ldap_connection) . " Ldap_add_mod  \"dn=$dn\" :  " 
   		. ldap_err2str(ldap_errno($this->ldap_connection)),__CLASS__ . "/" . $function,__FILE__);
   		
			if(is_array($update_array)){
   				 	while (list ($num, $ligne) = each ($update_array) ){
	   					writelogs("LDAP ERROR $num=$ligne",__CLASS__ . "/" . __FUNCTION__,__FILE__);
	   					}
	   			 }   		
   		}
   		
   		
   	function Ldap_read($dn,$filter,$attrs=null){
   		if(!$this->ExistsDN($dn)){return null;}
   		$res=@ldap_read($this->ldap_connection,$this->dn_escape($dn),$filter,$attrs,0,0,0,0);
   		writelogs("read dn " .$this->dn_escape($dn),__CLASS__ . "/" . __FUNCTION__,__FILE__);
   		if(!is_array($attrs)){$attrs=array();}
		if(!$res){
			if(ldap_errno($this->ldap_connection)>0){
				$this->ldap_last_error="\n" . '****' . "\n" . 'Error number ' . ldap_errno($this->ldap_connection) . "\nAction:LDAP read\ndn:$dn\nFilter:$filter\nattrs:" . implode(',',$attrs)."\n" . ldap_err2str(ldap_errno($this->ldap_connection) . '****' . "\n");
	   			writelogs("LDAP ERROR Reading : number " . ldap_errno($this->ldap_connection) .   " " . ldap_err2str(ldap_errno($this->ldap_connection)),__CLASS__ . "/" . __FUNCTION__,__FILE__);
	   			return false;
				}
		}
			
		$result =@ldap_get_entries($this->ldap_connection, $res);
		return $result;
		   		
   	}

   		
   	function ldap_add($dn,$update_array){
   		$r=@ldap_add($this->ldap_connection,$this->dn_escape($dn),$update_array);
   		if(!$r){
   			$error=ldap_errno($this->ldap_connection);
   			$msg=ldap_err2str(ldap_errno($this->ldap_connection));
   			$this->ldap_event($error,$msg,__FUNCTION__,$update_array,$dn);
   			if($error==20){return true;}
   			if($error==68){return true;}
   			$this->ldap_last_error_num=ldap_errno($this->ldap_connection) ;
   			$this->ldap_last_error="\n****\nError number $error\nAction:LDAP add\ndn:$dn\n$msg****\n";
   			return false;
   			}
   			
   		return true;}
   		
   		
   	function ldap_event($ldap_number,$ldap_mseg,$ldap_action,$update_array,$dn){
   		if($ldap_number==68){writelogs("$dn $ldap_mseg...",__CLASS__ . "/$ldap_action",__FILE__);return true;}
   		writelogs("$ldap_action ERROR $ldap_number ($ldap_mseg) on DN \"$dn\" entries: ",__CLASS__ . "/" . __FUNCTION__,__FILE__);
   		if(is_array($update_array)){
   		while (list ($num, $ligne) = each ($update_array) ){
   					if(is_array($ligne)){while (list ($a, $b) = each ($ligne) ){
   						$d=$d."$num:  $b\n";
   						writelogs("ERROR $ldap_action : $num($a)='{$b}'",__CLASS__ . "/" . __FUNCTION__,__FILE__);
   					}}
   			}
   		}
   		
   	writelogs("LDIF\ndn:$dn\n$d");}
   		
   		
   	function ldap_group_rename($olddn,$newdn){
   		$tbl=explode(",",$olddn);
   		unset($tbl[0]);
   		$path=implode(",",$tbl);
   		if (! @ldap_rename($this->ldap_connection,$olddn,$newdn,$path,1)) {
   			$this->ldap_last_error=ldap_err2str(ldap_errno($this->ldap_connection));
   			writelogs("ERROR:  ldap_rename(ldap_connection|$olddn|$newdn|$path|1)" . ldap_err2str(ldap_errno($this->ldap_connection)),__CLASS__ . "/" . __FUNCTION__,__FILE__);return false;
   		}
   		
   		
   	}
   	
   	function HashGetUsersAccounts($ou,$domain){
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		$suffix='ou=' . $ou . ',' . $this->suffix;
		writelogs($suffix . '= >(&(objectclass=*)(domainName=' .$domain .'))',__FUNCTION__,__FILE__);
		//$arr=array("associatedDomain");
		$sr =@ldap_search($ld,$suffix,'(&(objectclass=userAccount)(domainName=' .$domain .'))');
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			
			if(is_array($hash)){
			
				while (list ($num, $ligne) = each ($hash) ){
					if($ligne["cn"][0]<>null){
					$res[$num]["cn"]=$ligne["cn"][0];
					$res[$num]["dn"]=$ligne["dn"];
					$res[$num]["mail"]=$ligne["mail"][0];
					$res[$num]["first_name"]=$ligne["givenname"][0];
					$res[$num]["last_name"]=$ligne["sn"][0];	
					$res[$num]["uid"]=$ligne["uid"][0];	
					$res[$num]["mailboxactive"]=$ligne["mailboxactive"][0];
					}
					
				}
			}
			
			return $res;
			
		}
   	}
   	
   	
   	function noaccents($chaine){
 	 	$chaine =  strtr( $chaine, '���������������', 'aaaiioouuueeeec' );
 	 	return $chaine;
 	  	}

	
	function ldap_delete($dn,$recursive=false){
			if(trim($dn)==null){
				writelogs("ERROR:  ldap_delete dn is null",__CLASS__ . "/" . __FUNCTION__,__FILE__,__LINE__);
				return true;
			}
		   writelogs("Delete $dn recursive=$recursive",__FUNCTION__,__FILE__);
		    if($recursive == false){
		    	if(!@ldap_delete($this->ldap_connection,$dn)){
		    	  writelogs("ERROR:  ldap_delete \"$dn\"" . ldap_err2str(ldap_errno($this->ldap_connection)),__CLASS__ . "/" . __FUNCTION__,__FILE__,__LINE__);return false;			 
		    	}
		    }
		    $sr=@ldap_list($this->ldap_connection,$dn,"ObjectClass=*");
		        if($sr){
		        	$info =@ldap_get_entries($this->ldap_connection, $sr);
		        		for($i=0;$i<$info['count'];$i++){
		            		$result=$this->ldap_delete($info[$i]['dn'],$recursive);
		            		if(!$result){return($result);}
		        		}
		        if(!@ldap_delete($this->ldap_connection,$dn)){
		        	$this->ldap_last_error=ldap_err2str(ldap_errno($this->ldap_connection));
		        }
		    }
	return true;}	
   		
	
		

}


class DomainsTools{
	var $transport_port;
	var $transport_ip;
	var $transport_type;
	var $dn=null;
	var $amavisSpamLover="FALSE";
	var $amavisBadHeaderLover="FALSE";
	var $amavisBypassVirusChecks="FALSE";
	var $amavisBypassSpamChecks="FALSE";
	var $amavisBypassHeaderChecks="FALSE";
	var $amavisSpamTagLevel="-999";
	var $amavisSpamTag2Level="5";
	var $amavisSpamKillLevel="5";
	var $amavisSpamModifiesSubj="TRUE";	
	var $amavisSpamDsnCutoffLevel=15;
	var $amavisSpamQuarantineCutoffLevel=10;
	var $amavisSpamSubjectTag;
	var $amavisSpamSubjectTag2;
	var $amavis_domain=null;	
	var $objectClass_array=array();
	
	function DomainsTools($domainto=null){
		if($domainto<>null){
			$tr_table=$this->transport_maps_explode($domainto);
			if($tr_table[3]==null){$tr_table[3]=25;}
			$this->transport_type=$tr_table[0];
			$this->transport_port=$tr_table[3];
			$this->transport_ip=$tr_table[1];
		}
		
	}
	
	function LoadAmavisDomain($domain){
		$this->amavis_domain=$domain;
		$filter="(&(|(objectclass=transportTable)(objectclass=organizationalUnit))(|(associatedDomain=$domain)(cn=$domain)))";
		$attr=array();
		$ldap=new clladp();
		
		$sr=@ldap_search($ldap->ldap_connection,$ldap->suffix,$filter,$attr);
		if(!$sr){
			$this->LoadDefaults();
			$this->dn=$this->_Get_dn_domain($domain);
			return null;
		
		}
		$entry_id = ldap_first_entry($ldap->ldap_connection,$sr);
		if(!$entry_id){
			writelogs( 'INFOS: bad value $entry_id: (' . $entry_id . ')  find: (domain=' . $domain . ') -> aborting function search engine doesn`t found the pattern',__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->LoadDefaults();
			$this->dn=$this->_Get_dn_domain($domain);
			return null;
		}
		$attrs = ldap_get_attributes($ldap->ldap_connection, $entry_id);
		
	for($i=0;$i<$attrs["objectClass"]["count"];$i++){
			$class=$attrs["objectClass"][$i];
			$this->objectClass_array[$class]=$class;
			writelogs("Class $class found",__CLASS__.'/'.__FUNCTION__,__FILE__);
		}		
		
		$this->amavisSpamLover=$attrs["amavisSpamLover"][0];
		$this->amavisBadHeaderLover=$attrs["amavisBadHeaderLover"][0];
		$this->amavisBypassVirusChecks=$attrs["amavisBypassVirusChecks"][0];
		$this->amavisBypassSpamChecks=$attrs["amavisBypassSpamChecks"][0];
		$this->amavisBypassHeaderChecks=$attrs["amavisBypassHeaderChecks"][0];
		$this->amavisSpamTagLevel=$attrs["amavisSpamTagLevel"][0];
		$this->amavisSpamTag2Level=$attrs["amavisSpamTag2Level"][0];
		$this->amavisSpamKillLevel=$attrs["amavisSpamKillLevel"][0];
		$this->amavisSpamModifiesSubj=$attrs["amavisSpamModifiesSubj"][0];
		$this->amavisSpamDsnCutoffLevel=$attrs["amavisSpamDsnCutoffLevel"][0];
		$this->amavisSpamQuarantineCutoffLevel=$attrs["amavisSpamQuarantineCutoffLevel"][0];
		$this->amavisSpamSubjectTag=$attrs["amavisSpamSubjectTag"][0];
		$this->amavisSpamSubjectTag2=$attrs["amavisSpamSubjectTag2"][0];
		
		
		
		
		
		$this->dn=$this->_Get_dn_domain($domain);
		$this->LoadDefaults();
		
		
	}
	
		function _Get_dn_domain($domain){
			$ldap=new clladp();
			$filter="(&(|(objectclass=transportTable)(objectclass=organizationalUnit))(|(associatedDomain=$domain)(cn=$domain)))";
			$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$filter);
			if($sr){
				$result = ldap_get_entries($ldap->ldap_connection, $sr);
				return trim($result[0]["dn"]);
			}else{
				writelogs( 'INFOS: bad value $entry_id: find: (domain=' . $domain . ') -> aborting function search engine doesn`t found the pattern '.$filter,__CLASS__.'/'.__FUNCTION__,__FILE__);
			}
			
		}
		
	function DeleteAmavisAttribute($attr){
		$dd=new DomainsTools();
		$dd->LoadAmavisDomain($this->amavis_domain);
		$value=$dd->$attr;
		$upd[$attr]=$value;
		$ldap=new clladp();
		$ldap->Ldap_del_mod($this->dn,$upd);
		
	}
	
	
	function SaveAmavisConfig(){
		$ldap=new clladp();

		
		writelogs("Saving amavis configuration for dn $this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		if($this->objectClass_array["amavisAccount"]==null){
			$cl=$this->objectClass_array;
			while (list ($num, $ligne) = each ($cl)){
				$updClass["objectClass"][]=$ligne;
			}
			$updClass["objectClass"][]="amavisAccount";
			writelogs( 'Adding new class amavisAccount..',__CLASS__.'/'.__FUNCTION__,__FILE__);
			$ldap->Ldap_modify($this->dn,$updClass);
			
		}		
		

		$upd["amavisSpamLover"][0]=$this->amavisSpamLover;
		$upd["amavisBadHeaderLover"][0]=$this->amavisBadHeaderLover;
		$upd["amavisBypassVirusChecks"][0]=$this->amavisBypassVirusChecks;
		$upd["amavisBypassSpamChecks"][0]=$this->amavisBypassSpamChecks;
		$upd["amavisBypassHeaderChecks"][0]=$this->amavisBypassHeaderChecks;
		$upd["amavisSpamTagLevel"][0]=$this->amavisSpamTagLevel;
		$upd["amavisSpamTag2Level"][0]=$this->amavisSpamTag2Level;
		$upd["amavisSpamKillLevel"][0]=$this->amavisSpamKillLevel;
		$upd["amavisSpamModifiesSubj"][0]=$this->amavisSpamModifiesSubj;
		$upd["amavisSpamDsnCutoffLevel"][0]=$this->amavisSpamDsnCutoffLevel;
		$upd["amavisSpamQuarantineCutoffLevel"][0]=$this->amavisSpamQuarantineCutoffLevel;
		if($this->amavisSpamSubjectTag==null){
			$this->DeleteAmavisAttribute("amavisSpamSubjectTag");
		}else{
			$upd["amavisSpamSubjectTag"][0]=$this->amavisSpamSubjectTag;
		}
		
		$upd["amavisSpamSubjectTag2"][0]=$this->amavisSpamSubjectTag2;

		
		
		
		if(!$ldap->Ldap_modify($this->dn,$upd)){echo $ldap->ldap_last_error;return false;}
		return true;		
		
	}
	
	
	function SetDefaultAmavisConfig(){
		include_once(dirname(__FILE__).'/class.amavis.inc');
		$amavis=new amavis();
		$this->amavisSpamTagLevel=$amavis->main_array["BEHAVIORS"]["sa_tag_level_deflt"];
		$this->amavisSpamTag2Level=$amavis->main_array["BEHAVIORS"]["sa_tag2_level_deflt"];
		$this->amavisSpamKillLevel=$amavis->main_array["BEHAVIORS"]["sa_kill_level_deflt"];
		
		if($amavis->main_array["BEHAVIORS"]["spam_subject_tag_maps_enable"]=="yes"){$amavis->main_array["BEHAVIORS"]["spam_subject_tag_maps_enable"]="TRUE";}
		if($amavis->main_array["BEHAVIORS"]["spam_subject_tag_maps_enable"]=="no"){$amavis->main_array["BEHAVIORS"]["spam_subject_tag_maps_enable"]="FALSE";}
		
		$this->amavisSpamSubjectTag=$amavis->main_array["BEHAVIORS"]["spam_subject_tag_maps"];
		$this->amavisSpamSubjectTag2=$amavis->main_array["BEHAVIORS"]["spam_subject_tag2_maps"];
		$this->amavisSpamQuarantineCutoffLevel=$amavis->main_array["BEHAVIORS"]["sa_quarantine_cutoff_level"];
		$this->amavisSpamDsnCutoffLevel=$amavis->main_array["BEHAVIORS"]["sa_dsn_cutoff_level"];
		$this->amavisSpamModifiesSubj=$amavis->main_array["BEHAVIORS"]["spam_subject_tag_maps_enable"];
		$this->SaveAmavisConfig();
	}
	
	function DeleteAmavisConfig(){
		$upd["amavisSpamLover"]=$this->amavisSpamLover;
		$upd["amavisBadHeaderLover"]=$this->amavisBadHeaderLover;
		$upd["amavisBypassVirusChecks"]=$this->amavisBypassVirusChecks;
		$upd["amavisBypassSpamChecks"]=$this->amavisBypassSpamChecks;
		$upd["amavisBypassHeaderChecks"]=$this->amavisBypassHeaderChecks;
		$upd["amavisSpamTagLevel"]=$this->amavisSpamTagLevel;
		$upd["amavisSpamTag2Level"]=$this->amavisSpamTag2Level;
		$upd["amavisSpamKillLevel"]=$this->amavisSpamKillLevel;
		$upd["amavisSpamModifiesSubj"]=$this->amavisSpamModifiesSubj;
		
		$upd["amavisSpamDsnCutoffLevel"]=$this->amavisSpamDsnCutoffLevel;
		$upd["amavisSpamQuarantineCutoffLevel"]=$this->amavisSpamQuarantineCutoffLevel;
		$upd["amavisSpamSubjectTag"]=$this->amavisSpamSubjectTag;	
		$upd["amavisSpamSubjectTag2"]=$this->amavisSpamSubjectTag2;
		$upd["objectClass"]="amavisAccount";
		$ldap=new clladp();
		$ldap->Ldap_del_mod($this->dn,$upd);
		
		
	}	
	
	function LoadDefaults(){
			include_once(dirname(__FILE__).'/class.amavis.inc');
			$amavis=new amavis();	
			if($amavis->main_array["BEHAVIORS"]["spam_subject_tag_maps_enable"]=="yes"){$amavis->main_array["BEHAVIORS"]["spam_subject_tag_maps_enable"]="TRUE";}
			if($amavis->main_array["BEHAVIORS"]["spam_subject_tag_maps_enable"]=="no"){$amavis->main_array["BEHAVIORS"]["spam_subject_tag_maps_enable"]="FALSE";}			
			
			if($this->amavisSpamLover==null){$this->amavisSpamLover="FALSE";}
			if($this->amavisBadHeaderLover==null){$this->amavisBadHeaderLover="FALSE";}
			if($this->amavisBypassVirusChecks==null){$this->amavisBypassVirusChecks="FALSE";}
			if($this->amavisBypassSpamChecks==null){$this->amavisBypassSpamChecks="FALSE";}
			if($this->amavisBypassHeaderChecks==null){$this->amavisBypassHeaderChecks="FALSE";}
			if($this->amavisSpamTagLevel==null){$this->amavisSpamTagLevel=$amavis->main_array["BEHAVIORS"]["sa_tag_level_deflt"];}
			if($this->amavisSpamTag2Level==null){$this->amavisSpamTag2Level=$amavis->main_array["BEHAVIORS"]["sa_tag2_level_deflt"];}
			if($this->amavisSpamKillLevel==null){$this->amavisSpamKillLevel=$amavis->main_array["BEHAVIORS"]["sa_kill_level_deflt"];}
			if($this->amavisSpamModifiesSubj==null){$this->amavisSpamModifiesSubj=$amavis->main_array["BEHAVIORS"]["spam_subject_tag_maps_enable"];}
			//if($this->amavisSpamSubjectTag==null){$this->amavisSpamSubjectTag=$amavis->main_array["BEHAVIORS"]["spam_subject_tag_maps"];}
			if($this->amavisSpamSubjectTag2==null){$this->amavisSpamSubjectTag2=$amavis->main_array["BEHAVIORS"]["spam_subject_tag2_maps"];}
			
			if($this->amavisSpamDsnCutoffLevel==null){$this->amavisSpamDsnCutoffLevel=$amavis->main_array["BEHAVIORS"]["sa_dsn_cutoff_level"];}
			if($this->amavisSpamQuarantineCutoffLevel==null){$this->amavisSpamQuarantineCutoffLevel=$amavis->main_array["BEHAVIORS"]["sa_quarantine_cutoff_level"];}
			
			

			
	}
	
	
	
	function transport_maps_explode($domainto){
	writepostfixlogs($domainto,__CLASS__.'/'.__FUNCTION__,__FILE__);
	$domainto=strtolower($domainto);
	if(!preg_match('#([a-z\-\.]+):.+#',$domainto)){
		$domainto="smtp:$domainto";
	
	
	
	}
		if(strpos($domainto,'[')>0){
			$resolve="no";
			$domainto=str_replace('[','',$domainto);
			$domainto=str_replace(']','',$domainto);
		}else{$resolve="yes";}
		
		
		
		writepostfixlogs($domainto .' resolve=' . $resolve,__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		
		
		
		if(preg_match('#([a-z]+):' . grep_ip . ':([0-9]+)#',$domainto,$regs)){
			writepostfixlogs("result=$domainto" .' pattern 1=' . "{$regs[1]};{$regs[2]}.{$regs[3]}.{$regs[4]}.{$regs[5]};{$regs[6]};$resolve",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$resolve="no";
			return array($regs[1],"{$regs[2]}.{$regs[3]}.{$regs[4]}.{$regs[5]}",$regs[6],$resolve);
			
			
		}
		
		if(preg_match('#([a-z]+):' . grep_ip . '#',$domainto,$regs)){
			$resolve="no";
			writepostfixlogs($domainto .' pattern 2=' . "{$regs[1]};{$regs[2]}.{$regs[3]}.{$regs[4]}.{$regs[5]};25;$resolve",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return array($regs[1],"{$regs[2]}.{$regs[3]}.{$regs[4]}.{$regs[5]}",'25',$resolve);
		}

		if(preg_match('#([a-z]+):([a-z\.0-9\-]+):([0-9]+)#',$domainto,$regs)){
			writepostfixlogs("result=".$domainto .' pattern 3=' . "{$regs[1]};{$regs[2]};{$regs[3]};$resolve",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return array($regs[1],$regs[2],$regs[3],$resolve);
		}
		if(preg_match('#([a-z]+):([a-z\.0-9\-]+)#',$domainto,$regs)){
			writepostfixlogs("result=".$domainto .' pattern 4=' . "{$regs[1]};{$regs[2]};{$regs[3]};$resolve",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return array($regs[1],$regs[2],'25',$resolve);
		}				

	}	
	
	
	
	function transport_maps_implode($domainTo,$port=null,$service=null,$resolvemx="yes"){
		writepostfixlogs("implode values $domainTo,$port,$service,$resolvemx",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if($service<>null){$result="$service";}else{$result='smtp';}
		include_once(dirname(__FILE__.'/class.tcpip.inc'));
		$hasaddress=false;
		$ip=new IP();
		if($ip->isIPAddress($domainTo)){
				writepostfixlogs("$domainTo is an ip address, do not resolv mx",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$hasaddress=true;
				$resolvemx="no";}
		
		if($port==25){$port=null;}
		
		if($service==null){
				writepostfixlogs("no service specified, consider nothing",__CLASS__.'/'.__FUNCTION__,__FILE__);
				if($hasaddress){
					$result="[$domainTo]";
					if($port<>null){$result=$result.":$port";}
					return $result;
				}
			}
		
		writepostfixlogs("$domainTo:$port service($result) resolve=$resolvemx",__CLASS__.'/'.__FUNCTION__,__FILE__);		
		
		if(preg_match('#' . grep_ip . '#',$domainTo)){
			$result=$result .":$domainTo";	
			$resolvemx="no";
			if($port<>null){$result=$result . ":$port";}
			return $result;
		}
		
		if($resolvemx=="no"){
			$domainTo="[$domainTo]";
		}
		if($port<>null){
			if($port<>'25'){$result=$result . ":$domainTo:$port";}
			if($port=='25'){$result=$result . ":$domainTo";}
			return $result;
			}
		
		
		if($domainTo<>null){
			$result=$result .":$domainTo";
		}
	if($service<>null){
		if($service<>"smtp"){
			if($service<>'relay'){
			writepostfixlogs("delete [ ]",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$result=str_replace('[','',$result);
			$result=str_replace(']','',$result);
			}
		}
	}
		
		writepostfixlogs("$result",__CLASS__.'/'.__FUNCTION__,__FILE__);
		return $result;
	}	
	
}

function GET_PERFS($value){
	$ini=new Bs_IniHandler("/etc/artica-postfix/performances.conf");
	return $ini->_params["PERFORMANCES"][$key];
	
}

function EXEC_NICE(){
	include_once(dirname(__FILE__).'/class.os.system.inc');
	$useIonice=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/useIonice"));
	if($useIonice==null){$useIonice=1;}
	
	$tmp=GET_PERFS('ProcessNice');
	if($tmp==null){$tmp=19;}
	if(!is_numeric($tmp)){$tmp=19;}
	if($useIonice==1){
		$ionice=LOCATE_PROGRAM("ionice");
		if(trim($ionice)<>null){$ionice="$ionice -c2 -n7 ";}
	}
	$nice=LOCATE_PROGRAM("nice");
	if(trim($nice)<>null){
		$nice="$nice --adjustment=$tmp ";
	}
	return "$ionice$nice";    	
}
	
	
