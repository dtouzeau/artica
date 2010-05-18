<?php
include_once(dirname(__FILE__) . '/class.ini.inc');
include_once(dirname(__FILE__) . '/class.sockets.inc');

class harddrive{
	
	var $main_array;
	
	function harddrive(){
	$this->__LoadFoldersList();
		
		
		
	}
	
	function __LoadFoldersList($verify=true){
		$ldap=new clladp();
		$attrs=array("ArticaFoldersSizeConfig");
		$sr =@ldap_read($ldap->ldap_connection,"cn=artica,$ldap->suffix",'objectClass=ArticaSettings',$attrs);
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			if($verify){if(!isset($hash[0][strtolower("ArticaFoldersSizeConfig")]["count"])){$this->_builddefault();}}
			for($i=0;$i<$hash[0][strtolower("ArticaFoldersSizeConfig")]["count"];$i++){
				$this->main_array["folders_list"][]=$hash[0][strtolower("ArticaFoldersSizeConfig")][$i];
				
			}
				
			}
	}
	
	
	function edit_path($index,$path){
		$this->main_array["folders_list"][$index]=$path;
		while (list ($num, $ligne) = each ($this->main_array["folders_list"])){	
			$upd["ArticaFoldersSizeConfig"][]=$ligne;
		}
		$ldap=new clladp();
		$dn="cn=artica,$ldap->suffix";
		$ldap->Ldap_modify($dn,$upd);
		$this->__applyconf();
	}
	
	function add_path($path){
		$ldap=new clladp();
		$dn="cn=artica,$ldap->suffix";
		$upd["ArticaFoldersSizeConfig"]=$path;
		$ldap->Ldap_add_mod($dn,$upd);
		$this->__applyconf();
	}
	function delete_path($num){
		$ldap=new clladp();
		$dn="cn=artica,$ldap->suffix";
		$path=$this->main_array["folders_list"][$num];
		$upd["ArticaFoldersSizeConfig"]=$path;
		$ldap->Ldap_del_mod($dn,$upd);
		$this->__applyconf();
	}
	
	function __applyconf(){
		$sock=new sockets();
		$sock->getfile('DeleteFolderSizeMon');
		
	}
	
	
	function _builddefault(){
		$ldap=new clladp();
		$ldap->IFcn_notexists_CreateIt('artica');
		$dn="cn=artica,$ldap->suffix";
		$u[]='/var/log';
		$u[]='/opt/artica';
		$u[]='/var/spool';
		
		 while (list ($num, $ligne) = each ($u) ){
		 	$upd["ArticaFoldersSizeConfig"]=$ligne;
		 	$ldap->Ldap_add_mod($dn,$upd);
		 	
		 }
		$this->__LoadFoldersList(false);
	}
	
	function BuildSizes(){
		$sock=new sockets();
		$datas=$sock->getfile("SystemGetFolderSize");
		$ini=new Bs_IniHandler();
		$ini->loadString($datas);
		
		
		
		
		if(!is_array($this->main_array["folders_list"])){return null;}
		reset($this->main_array["folders_list"]);
		while (list ($num, $ligne) = each ($this->main_array["folders_list"])){	
			$size=$ini->_params[$ligne]["Size"];
			$total=$total+$size;
			$this->main_array["folders_size"][$ligne]=$ini->_params[$ligne]["Size"];
		}
		reset($this->main_array["folders_list"]);
		$this->main_array["sum"]["total"]=$total;
		
		
	}
	
	
}