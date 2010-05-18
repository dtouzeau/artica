<?php

class roundcube_ldap{
	var $_GLOBAL;
	var $ldap_admin;
	var $ldap_password;
	var $ldap_host;
	var $ldap_port;
	var $suffix;
	var $ldap_connection;
	var $ldapFailed;
	var $ldapbind;
	
	function roundcube_ldap(){
		if(!function_exists('ldap_connect')){
			$this->debug("ERROR number : ldap_connect is not installed, check php ldap libraries",__FUNCTION__);
			$this->ldapFailed=true;
			return false;
		}
		
		include(dirname(__FILE__).'/settings.inc');
		$this->GLOBAL_CONFIG=$_GLOBAL;
		$this->ldap_host=$_GLOBAL["ldap_host"];
		if($_GLOBAL["ldap_host"]==null){$this->ldap_host="127.0.0.1";}
		if($_GLOBAL["ldap_host"]=='*'){$this->ldap_host="127.0.0.1";}
		$this->ldap_admin=$_GLOBAL["ldap_admin"];
		$this->ldap_password=$_GLOBAL["ldap_password"];
		$this->suffix=$_GLOBAL["ldap_root_database"];
		$this->ldap_connection=@ldap_connect($this->ldap_host) ;
		 if(!$this->ldap_connection){ 
		 	$this->debug("Impossible de se connecter au serveur LDAP {$this->ldap_host}",__FUNCTION__);
		 	$this->ldapFailed=true;return null;
		 }
		ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3); // on passe le LDAP en version 3, necessaire pour travailler avec le AD
		ldap_set_option($this->ldap_connection, LDAP_OPT_REFERRALS, 0); 		 
		$this->ldapbind=@ldap_bind($this->ldap_connection, "cn=$this->ldap_admin,$this->suffix", $this->ldap_password);
		if(!$this->ldapbind){
				$this->ldapFailed=true;
				$errornumber= ldap_errno($this->ldap_connection);
				$errorr_text=ldap_err2str($this->ldap_connection);
				$this->debug("ERROR number : $errornumber \"$errorr_text\" unable to bind with following cn=$this->ldap_admin,$this->suffix ($this->ldap_password)",__FUNCTION__);
		}

		return true;
		
	}
	
  private function debug($text,$function){
  		$logFile=dirname(__FILE__).'/logs/debug.log';
  		$file=basename(__FILE__);
  		$f = @fopen($logFile, 'a');
		$date=date("Y-m-d H:i:s");
		@fwrite($f, "$date: $file [$function] $text\n");
		@fclose($f);
  }	
  
  private function user_search($uid,$tofind){
  	if($tofind==null){$tofind='*';}else{$tofind="*$tofind*";}
  	if($_SESSION["ARTICA_RDCUBE_OU"]==null){$_SESSION["ARTICA_RDCUBE_OU"]=$this->GetOuForUser($uid);}
  	if($_SESSION["ARTICA_RDCUBE_OU"]==null){
  		$this->debug("Unable to find organization for $uid",__FUNCTION__);
  		return null;
  	}
  	$ou=$_SESSION["ARTICA_RDCUBE_OU"];
  	$filter="(&(objectClass=userAccount)(|(cn=$tofind)(mail=$tofind)(displayName=$tofind)(uid=$tofind) (givenname=$tofind) ))";
	$attrs=array("displayName","uid","mail","givenname","telephoneNumber","title","sn","mozillaSecondEmail","employeeNumber");
	$dn="ou=$ou,dc=organizations,$this->suffix";
	$hash=$this->Ldap_search($dn,$filter,$attrs);
	$dn="ou=$uid,ou=People,dc=$ou,dc=NAB,$this->suffix";
	$filter="(&(objectClass=evolutionPerson)(|(cn=$tofind)(mail=$tofind)(displayName=$tofind)(sn=$tofind)(givenname=$tofind)(mozillaSecondEmail=$tofind)(fileAs=$tofind) ))";
	$hash2=$this->Ldap_search($dn,$filter,$attrs);
	return array($hash,$hash2);  	
  	
  	
  }
  
  public function GetLocalUserinfos($uid){
  if($_SESSION["ARTICA_RDCUBE_OU"]==null){$_SESSION["ARTICA_RDCUBE_OU"]=$this->GetOuForUser($uid);}
  	if($_SESSION["ARTICA_RDCUBE_OU"]==null){
  		$this->debug("Unable to find organization for $uid",__FUNCTION__);
  		return null;
  	}

  	$sr =@ldap_search($this->ldap_connection,"dc=organizations,$this->suffix","(uid=$uid)");
		if(!$sr){
			$this->debug("Unable to find : $uid",__FUNCTION__);
			return null;
		}
		
	$entry_id = ldap_first_entry($this->ldap_connection,$sr);
		if(!$entry_id){
			$this->debug("INFOS: bad value \$entry_id for $uid",__FUNCTION__);
			return null;
		}
		$attrs =@ldap_get_attributes($this->ldap_connection, $entry_id);
		return array('ID' => "ORG-$uid", 'name' => $attrs["displayName"][0], 'firstname' =>$attrs["givenName"][0] , 'surname' => $attrs["sn"][0], 'email' => $attrs["mail"][0]);
  	
  }
  
  public function GetNABUserinfos($uid){
  if($_SESSION["ARTICA_RDCUBE_OU"]==null){$_SESSION["ARTICA_RDCUBE_OU"]=$this->GetOuForUser($uid);}
  	if($_SESSION["ARTICA_RDCUBE_OU"]==null){
  		$this->debug("Unable to find organization for $uid",__FUNCTION__);
  		return null;
  	}

  	$sr =@ldap_search($this->ldap_connection,"ou={$_SESSION["username"]},ou=People,dc={$_SESSION["ARTICA_RDCUBE_OU"]},dc=NAB,$this->suffix","(employeeNumber=$uid)");
		if(!$sr){
			$this->debug("Unable to find : $uid in ou={$_SESSION["username"]},ou=People,dc={$_SESSION["ARTICA_RDCUBE_OU"]},dc=NAB,$this->suffix branch",__FUNCTION__);
			return null;
		}
		
	$entry_id = ldap_first_entry($this->ldap_connection,$sr);
		if(!$entry_id){
			$this->debug("INFOS: bad value \$entry_id for $uid",__FUNCTION__);
			return null;
		}
		$attrs =@ldap_get_attributes($this->ldap_connection, $entry_id);
		return array('ID' => "NAB-$uid", 'name' => $attrs["displayName"][0], 'firstname' => $attrs["givenName"][0], 'surname' =>$attrs["sn"][0] , 'email' => $attrs["mail"][0]);
  	
  }  
  
  
  public function SearchAddressBook($uid,$tofind){
  	$this->debug("Searching $uid \"$tofind\"",__FUNCTION__);
  	$hash_full=$this->user_search($uid,$tofind);
  	if(!is_array($hash_full)){
  		$this->debug("Failed",__FUNCTION__);
  		return null;
  	}
	
	$hash1=$hash_full[0];
	$hash2=$hash_full[1];
	$count=0;
	
		  if(is_array($hash1)){
			while (list ($num, $ligne) = each ($hash1) ){
			if(($ligne["uid"][0]==null) && ($ligne["employeenumber"][0]==null)){continue;}
				$hash[$count]["displayname"][0]=$ligne["displayname"][0];
				$hash[$count]["givenname"][0]=$ligne["givenname"][0];
				$hash[$count]["uid"][0]="ORG-".$ligne["uid"][0];
				$hash[$count]["employeenumber"][0]=$ligne["employeenumber"][0];
				$hash[$count]["title"][0]=$ligne["title"][0];
				$hash[$count]["uri"][0]=$ligne["uri"][0];
				$hash[$count]["mail"][0]=$ligne["mail"][0];
				$hash[$count]["phone"][0]=$ligne["telephonenumber"][0];
				$hash[$count]["sn"][0]=$ligne["sn"][0];
				$count=$count+1;
				
			}}	
			
		if(is_array($hash2)){
			while (list ($num, $ligne) = each ($hash2) ){
			if(($ligne["uid"][0]==null) && ($ligne["employeenumber"][0]==null)){continue;}
				$hash[$count]["displayname"][0]=$ligne["displayname"][0];
				$hash[$count]["givenname"][0]=$ligne["givenname"][0];
				$hash[$count]["uid"][0]='NAB-'.$ligne["uid"][0];
				$hash[$count]["employeenumber"][0]=$ligne["employeenumber"][0];
				$hash[$count]["title"][0]=$ligne["title"][0];
				$hash[$count]["uri"][0]=$ligne["uri"][0];
				$hash[$count]["mail"][0]=$ligne["mail"][0];
				$hash[$count]["phone"][0]=$ligne["telephonenumber"][0];
				$hash[$count]["sn"][0]=$ligne["sn"][0];
				$count=$count+1;
				
			}}	
	
	
	$count=count($hash);
	if(is_array($hash)){
		while (list ($num, $ligne) = each ($hash) ){
		if(($ligne["uid"][0]==null) && ($ligne["employeenumber"][0]==null)){continue;}
			$displayname=$ligne["displayname"][0];
			$givenname=$ligne["givenname"][0];
			$mail=$ligne["mail"][0];
			$uid=$ligne["uid"][0];
			$sn=$ligne["sn"][0];
			if($displayname==null){$displayname=$ligne["uid"][0];}
			if($givenname==null){$givenname='unknown';}
			if($mail==null){$mail='unknown';}			
			$res[]=array('ID' => $uid, 'name' => $displayname, 'firstname' =>$givenname , 'surname' =>$sn, 'email' => $mail);
			
		}
	}
	$this->debug("Success with ".count($res). " entries",__FUNCTION__);
	return $res;
	
  
  }
  
  private function Ldap_search($dn,$filter,$attrs){
		if(!is_array($attrs)){
			$sr =@ldap_search($this->ldap_connection,$dn,$filter);
			}else{$sr =@ldap_search($this->ldap_connection,$dn,$filter,$attrs);}
		if ($sr) {
			return ldap_get_entries($this->ldap_connection,$sr);
		}else{
			$this->ldap_last_error='Error LDAP search number ' . ldap_errno($this->ldap_connection) . "\nAction:LDAP search\ndn:$dn\n$filter\n" . ldap_err2str(ldap_errno($this->ldap_connection));
   			$this->debug($this->ldap_last_error,__FUNCTION__);
   			return false;
		}
		return array();
  }  
  
 
  
  private function GetOuForUser($uid){
 		$dn=$this->_Get_dn_userid($uid);
 		$this->debug("$uid -> $dn",__FUNCTION__);
	    if(@preg_match("#cn=.+?,ou=users,ou=(.+?),dc#",$dn,$re)){
	    	$this->debug("$uid is in organization {$re[1]}",__FUNCTION__);
	    	return $re[1];
	    	
	    	}
	}
  
 	private function _Get_dn_userid($userid){
			if($userid==null){return null;}
			$sr =@ldap_search($this->ldap_connection,$this->suffix,"(uid=$userid)");
			if($sr){$result =@ldap_get_entries($this->ldap_connection, $sr);return trim($result[0]["dn"]);}
		} 

	private function LastUidNumber(){
		
		$this->debug("Search the last uid number",__FUNCTION__);
      	$r =@ldap_search($this->ldap_connection, $this->suffix, '(uidnumber=*)',array());
      		if ($r){
         			@ldap_sort($this->ldap_connection, $r, "uidnumber");
            		$result =@ldap_get_entries($this->ldap_connection, $r);
         			$count = $result['count'];
         			$biguid = $result[$count-1]['uidnumber'][0];
         			$this->debug("biguid=$biguid",__FUNCTION__);
         			$biguid=$biguid+1;
      		}else{
      			$biguid=2000;
      		}
      		if($biguid<2000){$biguid=2000;}
      		return $biguid;
      		
	}
		
		
	public function add_contact($array){
		if($_SESSION["ARTICA_RDCUBE_OU"]==null){$_SESSION["ARTICA_RDCUBE_OU"]=$this->GetOuForUser($uid);}
	  	if($_SESSION["ARTICA_RDCUBE_OU"]==null){
	  		$this->debug("Unable to find organization for $uid",__FUNCTION__);
	  		return null;
	  	}
		$time=time();
		$employeeNumber=$time."-". rand(0,10000);
		$givenName=utf8_encode($array["firstname"]);
		$sn=$array["surname"];
		if($sn==null){$sn=utf8_encode($givenName);}
		$displayName=utf8_encode($array["name"]);
		if($displayName==null){$displayName=utf8_encode("$sn $givenName");}
		
		
		//
		$dn="cn=$employeeNumber,ou={$_SESSION["username"]},ou=People,dc={$_SESSION["ARTICA_RDCUBE_OU"]},dc=NAB,$this->suffix";
		$upd["objectClass"][]="top";
		$upd["objectClass"][]="person";
		$upd["objectClass"][]="organizationalPerson";
		$upd["objectClass"][]="inetOrgPerson";
		$upd["objectClass"][]="evolutionPerson";
		$upd["objectClass"][]="mozillaOrgPerson";
		$upd["objectClass"][]="posixAccount";
		$upd["cn"][0]=$employeeNumber;
		if($sn<>null){$upd["sn"][0]=$sn;}
		if($givenName<>null){$upd["givenName"][0]=$givenName;}
		$upd["employeeNumber"][0]=$employeeNumber;
		$upd["displayName"][0]=$displayName;
		$upd["mail"][0]=$array["email"];
		$upd["uidNumber"][0]=$this->LastUidNumber();
		$upd["gidNumber"][0]=546;
		$upd["uid"][0]=$employeeNumber;
		$upd["homeDirectory"][0]="/dev/null";
		
		if(!$this->ldap_add($dn,$upd) ){
			$this->debug("$this->ldap_last_error",__FUNCTION__);
			return null;
		}
		$this->debug("success adding new contact $employeeNumber",__FUNCTION__);
		return $employeeNumber;
	}
		
	
	
	private function ldap_add($dn,$update_array){
   		$r=@ldap_add($this->ldap_connection,$this->dn_escape($dn),$update_array);
   		if(!$r){
   			$error=ldap_errno($this->ldap_connection);
   			$msg=ldap_err2str(ldap_errno($this->ldap_connection));
   			
   			if($error==20){return true;}
   			if($error==68){return true;}
   			$this->ldap_last_error="Error number $error :: Action:LDAP add dn:$dn\n**** \n$msg****\n";
   			return false;
   			}
   			
   		return true;}	
  
  
	private function dn_escape($dn) {	
		$olddn = $dn;
		# Check if the RDN has a comma and escape it.
		while (preg_match('/([^\\\\]),(\s*[^=]*\s*),/',$dn))
			$dn = preg_replace('/([^\\\\]),(\s*[^=]*\s*),/','$1\\\\2C$2,',$dn);
			$dn = preg_replace('/([^\\\\]),(\s*[^=]*\s*)([^,])$/','$1\\\\2C$2$3',$dn);
		return $dn;
		}
	
	
}


?>