<?php
include_once(dirname(__FILE__) . '/logs.inc');
include_once(dirname(__FILE__) . '/class.templates.inc');
include_once(dirname(__FILE__) . '/class.ldap.inc');


class pdns{
	var $domainname=null;
	var $suffix;
	var $last_error;
	
	
	function pdns($domainname=null){
		if($domainname<>null){
			$this->domainname=$domainname;
			$this->BuildBranch();
		}
		
	}
	
	private function BuildBranch(){
		$ldap=new clladp();
		$dn="ou=dns,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$up["objectClass"][]="top";
			$up["objectClass"][]="organizationalUnit";
			$up["ou"][]="dns";
			$ldap->ldap_add($dn,$up);
		}
		
		writelogs("Check domain $this->domainname",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$tbl=explode(".",$this->domainname);
		if(!is_array($tbl)){return null;}
		
		$extension=$tbl[count($tbl)-1];
		if($extension==null){return null;}
		
		
		$dn="dc=$extension,ou=dns,$ldap->suffix";
		writelogs("Check domain $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(!$ldap->ExistsDN($dn)){
			$up["objectClass"][]="top";
			$up["objectClass"][]="dcobject";
			$up["objectClass"][]="dNSDomain2";
			$up["objectClass"][]="domainRelatedObject";
			$up["dc"][]="$extension";
			$up["associateddomain"][]="$extension";
			$ldap->ldap_add($dn,$up);
			unset($up);
			
		}

		
		
		$strippeddomain=str_replace(".$extension","",$this->domainname);
		
		
		
		$dn="dc=$strippeddomain,dc=$extension,ou=dns,$ldap->suffix";
		$this->suffix=$dn;
		
		writelogs("Check domain $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(!$ldap->ExistsDN($dn)){
			$up["objectClass"][]="top";
			$up["objectClass"][]="dcobject";
			$up["objectClass"][]="dNSDomain2";
			$up["objectClass"][]="domainRelatedObject";
			$up["dc"][]="$strippeddomain";
			$up["soarecord"][]="ns.$this->domainname me@$this->domainname 1 1800 3600 86400 7200";
			$up["nsrecord"][]="ns.$this->domainname";
			$up["ARecord"][]="127.0.0.1";
			$up["associateddomain"][]="$this->domainname";
			$ldap->ldap_add($dn,$up);
			unset($up);
			}
		
			
		
		
		$dn="dc=ns,dc=$strippeddomain,dc=$extension,ou=dns,$ldap->suffix";
		writelogs("Check domain $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(!$ldap->ExistsDN($dn)){
			$up["objectClass"][]="top";
			$up["objectClass"][]="dNSDomain2";
			$up["objectClass"][]="domainRelatedObject";
			$up["dc"][]="ns";
			$up["ARecord"][]="127.0.0.1";
			$up["associateddomain"][]="ns.$this->domainname";
			$ldap->ldap_add($dn,$up);
			unset($up);
			}
	
	
	}	

	private function DeleteFromMacAddrr($macaddress){
		$ldap=new clladp();
		if($macaddress==null){return null;}
		$suffix="ou=dns,$ldap->suffix";
		writelogs("find mac  (&(objectclass=dNSDomain2)(MacRecord=$macaddress))",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sr = @ldap_search($ldap->ldap_connection,$suffix,"(&(objectclass=dNSDomain2)(MacRecord=$macaddress))",array());
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			writelogs("find mac  $macaddress ".count($hash)." entries",__CLASS__.'/'.__FUNCTION__,__FILE__);
			for($i=0;$i<$hash["count"];$i++){
				if($hash[$i]["dn"]<>null){
					writelogs("Delete {$hash[$i]["dn"]}",__CLASS__.'/'.__FUNCTION__,__FILE__);
					$ldap->ldap_delete($hash[$i]["dn"]);
				}
			}
		}
		
		
	}
	
	
		
		public function EditIPName($computername,$IP,$DnsType,$macaddress=null){
			$ldap=new clladp();
			
			if(preg_match('#[0-9]+\.[0-9]+\.[0-9]+\.#',$computername)){
				writelogs("Cannot put in DNS an ip address as computer name",__CLASS__.'/'.__FUNCTION__,__FILE__);
				return false;
			}
			
			$macaddress=trim($macaddress);
			if($macaddress<>null){
				$this->DeleteFromMacAddrr($macaddress);
			}
			
			
			
			$computername=str_replace('$','',$computername);
			$dn="dc=$computername,$this->suffix";
			if(!$ldap->ExistsDN($dn)){
				writelogs("Add $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$up["objectClass"][]="top";
				$up["objectClass"][]="dNSDomain2";
				$up["objectClass"][]="domainrelatedobject";
				$up["associatedDomain"][]="$computername.$this->domainname";
				if($macaddress<>null){$up["MacRecord"][]=$macaddress;}
				if($DnsType=="A"){$up["arecord"][]=$IP;}
				if(!$ldap->ldap_add($dn,$up)){$this->last_error=$ldap->ldap_last_error; return false;}
			}else{
				if($DnsType=="A"){$up["arecord"][]=$IP;}
				$up["arecord"][]=$IP;
				if(!$ldap->Ldap_modify($dn,$up)){$this->last_error=$ldap->ldap_last_error; return false;}
				
			}
			
			
			unset($up);
			if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#",$IP,$re)){
				$arpa1=$re[3].".".$re[2].".". $re[1].".in-addr.arpa";
				$arpa2_dc_ip=$re[4].".".$re[3].".".$re[2]."in-addr.arpa";
				
			}else{
				$this->last_error=" $IP bad value\n";
				return false;
			}
			
			$dn="dc=$arpa1,$this->suffix";
			if(!$ldap->ExistsDN($dn)){
				writelogs("Add $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$up["objectClass"][]="top";
				$up["objectClass"][]="dNSDomain2";
				$up["objectClass"][]="domainrelatedobject";	
				$up["dc"][]="$arpa1";	
				$up["soarecord"][]="ns.$this->domainname me@$this->domainname 1 1800 3600 86400 7200";
				$up["nsrecord"][]="ns.$this->domainname";
				$up["associateddomain"][]="$arpa1";
				if(!$ldap->ldap_add($dn,$up)){$this->last_error=$ldap->ldap_last_error; return false;}
			}
			unset($up);
			$dn="dc={$re[4]},dc=$arpa1,$this->suffix";
		if(!$ldap->ExistsDN($dn)){
				writelogs("Add $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$up["objectClass"][]="top";
				$up["objectClass"][]="dNSDomain2";
				$up["objectClass"][]="domainrelatedobject";	
				//$up["objectClass"][]="ArticaComputerInfos";
				$up["dc"][]="$re[4]";	
				$up["ptrrecord"][]="$computername.$this->domainname";
				$up["associateddomain"][]="{$re[4]}.$arpa1";
				$up["MacRecord"][]=$macaddress;
				if(!$ldap->ldap_add($dn,$up)){$this->last_error=$ldap->ldap_last_error; return false;}
			}else{
				$up["ptrrecord"]="$computername.$this->domainname";
				if(!$ldap->Ldap_add_mod($dn,$up)){$this->last_error=$ldap->ldap_last_error; return false;}		
			}
			
			return true;
			
		}
		
	public function GetIp($servername){
			$ldap=new clladp();
			$user=new usersMenus();
			$filter="(&(Objectclass=dNSDomain2)(associatedDomain=$servername))";
			$sr = @ldap_search($ldap->ldap_connection,"ou=dns,$ldap->suffix",$filter,array("aRecord"));
			if(!$sr){return null;}
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			return $hash[0]["arecord"][0];
			
		}
		
	
	
	
}




?>