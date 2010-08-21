<?php
include_once(dirname(__FILE__) . '/logs.inc');
include_once(dirname(__FILE__) . '/class.templates.inc');
include_once(dirname(__FILE__) . '/class.ldap.inc');
include_once(dirname(__FILE__) . '/class.pdns.inc');

class vhosts{
	var $ou;
	var $WWWMysqlUser;
	var $WWWMysqlPassword;
	var $WWWAppliUser;
	var $WWWAppliPassword;
	var $WWWMultiSMTPSender;
	var $WWWEnableAddressBook;
	var $WWWSSLMode="FALSE";
	var $IMG_ARRAY_128=array("LMB"=>"logo_lmb-200.png",
	"JOOMLA"=>"128.joomla.png","SUGAR"=>"98-sugarcrm.png","ARTICA_USR"=>"bg_user-120.png",
	"OBM2"=>"obm-128.png",
	"OPENGOO"=>"opengoo-128.png",
	"ROUNDCUBE"=>"roundcube3logo.png",
	"GROUPOFFICE"=>"groupoffice-128.png",
	"ZARAFA"=>"zarafa-logo-128.png",
	"ZARAFA_MOBILE"=>"zarafa-logo-128.png",
	"DRUPAL"=>"drupal-logo-128.png",
	"WEBDAV"=>"webdav-128.png"
	
	);
	
	var $IMG_ARRAY_64=array(
	"LMB"=>"logo_lmb-64.png",
	"JOOMLA"=>"64.joomla.png",
	"SUGAR"=>"64-sugarcrm.png",
	"ARTICA_USR"=>"64-bg_user.png",
	"OBM2"=>"64-obm2.png",
	"OPENGOO"=>"64-opengoo.png",
	"ROUNDCUBE"=>"64-roundcube.png",
	"GROUPOFFICE"=>"groupoffice-64.png",
	"ZARAFA"=>"zarafa-logo-64.png",
	"ZARAFA_MOBILE"=>"zarafa-logo-64.png",
	"DRUPAL"=>"drupal-64.png",
	"WEBDAV"=>"webdav-64.png",
	
	);

	
	var $WWWEnableAddressBook_ENABLED=array("ROUNDCUBE"=>true);
	
	
	
	var $noneeduser=array("JOOMLA"=>true,"ROUNDCUBE"=>true,"ARTICA_USR"=>true,"ZARAFA_MOBILE"=>true,"ZARAFA"=>true,"DRUPAL"=>true,'WEBDAV'=>true);
	var $noneeduser_mysql=array("ARTICA_USR"=>true,"ZARAFA_MOBILE"=>true,"ZARAFA"=>true,"DRUPAL"=>true,"WEBDAV"=>true);
	
	
	
	var $TEXT_ARRAY=array(
	"LMB"=>array("TITLE"=>"APP_LMB","TEXT"=>"APP_LMB_TEXT"),
	"JOOMLA"=>array("TITLE"=>"APP_JOOMLA","TEXT"=>"APP_JOOMLA_TEXT"),
	"ROUNDCUBE"=>array("TITLE"=>"APP_ROUNDCUBE","TEXT"=>"APP_ROUNDCUBE_TEXT"),
	"SUGAR"=>array("TITLE"=>"APP_SUGARCRM","TEXT"=>"APP_SUGARCRM_TEXT"),
	"ARTICA_USR"=>array("TITLE"=>"APP_ARTICA_USR","TEXT"=>"APP_ARTICA_USR_TEXT"),
	"OBM2"=>array("TITLE"=>"APP_OBM2","TEXT"=>"APP_OBM2_TEXT"),
	"OPENGOO"=>array("TITLE"=>"APP_OPENGOO","TEXT"=>"APP_OPENGOO_TEXT"),
	"GROUPOFFICE"=>array("TITLE"=>"APP_GROUPOFFICE","TEXT"=>"APP_GROUPOFFICE_TEXT"),
	"ZARAFA"=>array("TITLE"=>"APP_ZARAFA","TEXT"=>"APP_ZARAFA_TEXT"),
	"ZARAFA_MOBILE"=>array("TITLE"=>"APP_ZARAFA_MOBILE_ACCESS","TEXT"=>"APP_ZARAFA_TEXT"),
	"DRUPAL"=>array("TITLE"=>"APP_DRUPAL","TEXT"=>"APP_DRUPAL_TEXT"),
	"WEBDAV"=>array("TITLE"=>"APP_GROUPWAREDAV","TEXT"=>"APP_GROUPWAREDAV_TEXT"),
	
	
	
	);
	
	function vhost($ou){
		if($ou<>null){
			writelogs("Loading organization $ou",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->ou=$ou;
			$this->BuildRoot();
		}
		
	}
	
	function LoadHost($ou,$host){
		$ldap=new clladp();
		$dn="ou=www,ou=$ou,dc=organizations,$ldap->suffix";
		$attrs=array();
		$sr = @ldap_list($ldap->ldap_connection,$dn,"(&(objectclass=apacheConfig)(apacheServerName=$host))",$attrs);
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			for($i=0;$i<$hash["count"];$i++){
				$LoadVhosts["wwwservertype"]=$hash[$i]["wwwservertype"][0];
				$LoadVhosts["apacheservername"]=$hash[$i]["apacheservername"][0];
				$LoadVhosts["wwwmysqluser"]=$hash[$i]["wwwmysqluser"][0];
				$LoadVhosts["wwwmysqlpassword"]=$hash[$i]["wwwmysqlpassword"][0];
				$LoadVhosts["wwwappliuser"]=$hash[$i]["wwwappliuser"][0];
				$LoadVhosts["wwwapplipassword"]=$hash[$i]["wwwapplipassword"][0];
				$LoadVhosts["wwwsslmode"]=$hash[$i]["wwwsslmode"][0];
				$LoadVhosts["WWWMultiSMTPSender"]=$hash[$i][strtolower("WWWMultiSMTPSender")][0];
				$LoadVhosts["WWWEnableAddressBook"]=$hash[$i][strtolower("WWWEnableAddressBook")][0];
				
				
				
				}
		}
		
		return $LoadVhosts;
	}
	
	function SearchHosts($servername){
		$ldap=new clladp();
		$dn="dc=organizations,$ldap->suffix";
		$attrs=array();
		$sr = @ldap_search($ldap->ldap_connection,$dn,"(&(objectclass=apacheConfig)(apacheServerName=$servername))",$attrs);
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			for($i=0;$i<$hash["count"];$i++){
				$dn=$hash[$i]["dn"];
				$LoadVhosts["wwwservertype"]=$hash[$i]["wwwservertype"][0];
				$LoadVhosts["apacheservername"]=$hash[$i]["apacheservername"][0];
				$LoadVhosts["wwwmysqluser"]=$hash[$i]["wwwmysqluser"][0];
				$LoadVhosts["wwwmysqlpassword"]=$hash[$i]["wwwmysqlpassword"][0];
				$LoadVhosts["wwwappliuser"]=$hash[$i]["wwwappliuser"][0];
				$LoadVhosts["wwwapplipassword"]=$hash[$i]["wwwapplipassword"][0];
				$LoadVhosts["wwwsslmode"]=$hash[$i]["wwwsslmode"][0];
				$LoadVhosts["WWWMultiSMTPSender"]=$hash[$i][strtolower("WWWMultiSMTPSender")][0];
				$LoadVhosts["WWWEnableAddressBook"]=$hash[$i][strtolower("WWWEnableAddressBook")][0];
				
				
				
				$LoadVhosts["dn"]=$hash[$i]["dn"];
				$LoadVhosts["apacheDocumentRoot"]=$hash[$i][strtolower("apacheDocumentRoot")][0];
				if(preg_match("#ou=www,ou=(.+?),dc=organizations#",$dn,$re) ){$LoadVhosts["OU"]=$re[1];}
			
				}
		}
		
		return $LoadVhosts;
		
	}
	
	
	function LoadVhosts($ou){
		$ldap=new clladp();
		$dn="ou=www,ou=$ou,dc=organizations,$ldap->suffix";
		$attrs=array();
		$sr = @ldap_list($ldap->ldap_connection,$dn,'(&(objectclass=apacheConfig)(apacheServerName=*))',$attrs);
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			for($i=0;$i<$hash["count"];$i++){
				
				$LoadVhosts[$hash[$i]["apacheservername"][0]]=$hash[$i]["wwwservertype"][0];
			}
		}
		
		if(!is_array($LoadVhosts)){return array();}
		return $LoadVhosts;
	}
	
	function LoadVhostsType($ou){
		$ldap=new clladp();
		$dn="ou=www,ou=$ou,dc=organizations,$ldap->suffix";
		$attrs=array();
		$sr = @ldap_list($ldap->ldap_connection,$dn,'(&(objectclass=apacheConfig)(apacheServerName=*))',$attrs);
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			for($i=0;$i<$hash["count"];$i++){
				$array[$hash[$i]["wwwservertype"][0]]=true;
				
			}
		}
		
		if(!is_array($array)){
			writelogs("No vhosts",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return array();}
		return $array;
	}	
	
	
	public function BuildRoot(){
		$ldap=new clladp();
		$dn="ou=www,ou=$this->ou,dc=organizations,$ldap->suffix";
		writelogs("Building dn $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(!$ldap->ExistsDN($dn)){
			$up["objectClass"][]="top";
			$up["objectClass"][]="organizationalUnit";
			$up["ou"][]="www";
			$ldap->ldap_add($dn,$up);
		}
		
	}
	
	function Addhost($servername,$servertype){
		$ldap=new clladp();
		$dn="apacheServerName=$servername,ou=www,ou=$this->ou,dc=organizations,$ldap->suffix";
		writelogs("Building dn $dn WWWMultiSMTPSender=$this->WWWMultiSMTPSender",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="apacheConfig";
			$upd["objectClass"][]="ApacheArtica";
			$upd["apacheServerName"][]="$servername";
			$upd["apacheDocumentRoot"][]="/usr/share/artica-groupware/domains/$servername";
			$upd["WWWServerType"][]=$servertype;
			$upd["WWWSSLMode"][]=$this->WWWSSLMode;
			if($this->WWWMysqlUser<>null){$upd["WWWMysqlUser"][]=$this->WWWMysqlUser;}
			if($this->WWWMysqlPassword<>null){$upd["WWWMysqlPassword"][]=$this->WWWMysqlPassword;}
			if($this->WWWAppliUser<>null){$upd["WWWAppliUser"][]=$this->WWWAppliUser;}
			if($this->WWWAppliPassword<>null){$upd["WWWAppliPassword"][]=$this->WWWAppliPassword;}
			if($this->WWWMultiSMTPSender<>null){$upd["WWWMultiSMTPSender"][]=$this->WWWMultiSMTPSender;}
			if($this->WWWEnableAddressBook<>null){$upd["WWWEnableAddressBook"][]=$this->WWWEnableAddressBook;}
			
			
			
			$ldap->ldap_add($dn,$upd);
		}else{
			$upd["WWWServerType"][]=$servertype;
			$upd["WWWSSLMode"][]=$this->WWWSSLMode;
			if($this->WWWMysqlUser<>null){$upd["WWWMysqlUser"][]=$this->WWWMysqlUser;}
			if($this->WWWMysqlPassword<>null){$upd["WWWMysqlPassword"][]=$this->WWWMysqlPassword;}
			if($this->WWWAppliUser<>null){$upd["WWWAppliUser"][]=$this->WWWAppliUser;}
			if($this->WWWAppliPassword<>null){$upd["WWWAppliPassword"][]=$this->WWWAppliPassword;}
			if($this->WWWMultiSMTPSender<>null){$upd["WWWMultiSMTPSender"][]=$this->WWWMultiSMTPSender;}
			if($this->WWWEnableAddressBook<>null){$upd["WWWEnableAddressBook"][]=$this->WWWEnableAddressBook;}
			
			
			
			$ldap->Ldap_modify($dn,$upd);
		}
	$sock=new sockets();
	$sock->getFrameWork('cmd.php?build-vhosts=yes');	

	}
	
	
}
?>