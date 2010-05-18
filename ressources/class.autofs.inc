<?php
include_once(dirname(__FILE__) . "/class.templates.inc");
include_once(dirname(__FILE__) . "/class.ldap.inc");
include_once(dirname(__FILE__) . "/class.ini.inc");
include_once(dirname(__FILE__) . "/class.groups.inc");
include_once(dirname(__FILE__) . "/class.user.inc");


class autofs{
	var $uuid=null;
	
	function autofs($uuid=null){
		$this->CreateBranchs();
		$this->uuid=$uuid;
		
	}
	
	private function CreateBranchs(){
	$ldap=new clladp();
	$mustrestart=false;
	$dn="ou=mounts,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="organizationalUnit";
			$upd["ou"][]="mounts";
			$ldap->ldap_add($dn,$upd);
		}
		unset($upd);


		$dn="ou=auto.master,ou=mounts,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="automountMap";
			$upd["ou"][]="auto.master";
			$ldap->ldap_add($dn,$upd);
			$mustrestart=true;
		}
		
		
		$dn="cn=/automounts,ou=auto.master,ou=mounts,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="automount";
			$upd["cn"][]="/automounts";
			$upd["automountInformation"][]="ldap:$ldap->ldap_host:ou=auto.automounts,ou=mounts,$ldap->suffix -g";
			$ldap->ldap_add($dn,$upd);
			$mustrestart=true;
		}

		$dn="ou=auto.automounts,ou=mounts,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="automountMap";
			$upd["ou"][]="auto.automounts";
			if(!$ldap->ldap_add($dn,$upd)){
				echo __FILE__."\n".__LINE__. "\n$ldap->ldap_last_error\n";
			}else{
				$mustrestart=true;
			}
		}

		if($mustrestart){
			$sock=new sockets();
			$sock->getfile('AutoMountReload');
		}
		
	}
	
	public function nfs_add($folder,$server,$mount_point){
		$ldap=new clladp();
		$dn="cn=$mount_point,ou=auto.automounts,ou=mounts,$ldap->suffix";
		$pattern="-fstype=nfs,rw,nfs,hard,intr,nodev,nosuid $server:$folder";
		if(!$ldap->ExistsDN($dn)){
						$upd["objectClass"][]="top";
						$upd["objectClass"][]="automount";
						$upd["cn"][]="$mount_point";
						$upd["automountInformation"][]=$pattern;
						//"-fstype=nfs,rw,soft,sync,tcp 192.168.1.154:/home/papa";
						if(!$ldap->ldap_add($dn,$upd)){
								echo __FILE__."\n".__LINE__. "\n$ldap->ldap_last_error\n";
								return false;
							}
						return true;	
						
					}
				$upd["automountInformation"][]=$pattern;
				if(!$ldap->Ldap_modify($dn,$upd)){
								echo __FILE__."\n".__LINE__. "\n$ldap->ldap_last_error\n";
								return false;
							}
						return true;				
		
	}
	
	public function by_uuid_addmedia($folder,$type){
			if($folder==null){return null;}	
			$ldap=new clladp();
			if($type=="vfat"){
				$pattern="-fstype=vfat,fmask=0666,dmask=0755  UUID=\"$this->uuid\"";
			}
			
			if($type=="ext4"){
				$pattern="-fstype=ext4,noatime,grpid UUID=\"$this->uuid\"";
			}

			if($type=="ext3"){
				$pattern="-fstype=ext3,rw UUID=\"$this->uuid\"";
			}	
			if($type=="ntfs"){
				$pattern="-fstype=ntfs,ro UUID=\"$this->uuid\"";
			}	
			 
			if($pattern==null){
				$tpl=new templates();
				echo $tpl->_ENGINE_parse_body('{error_understand_fs} '.$type);
				return false;
			}
			
			
			$dn="cn=$folder,ou=auto.automounts,ou=mounts,$ldap->suffix";
			if(!$ldap->ExistsDN($dn)){
				$upd["objectClass"][]="top";
				$upd["objectClass"][]="automount";
				$upd["cn"][]="$folder";
				$upd["automountInformation"][]=$pattern;
				//"-fstype=nfs,rw,soft,sync,tcp 192.168.1.154:/home/papa";
				if(!$ldap->ldap_add($dn,$upd)){
						echo __FILE__."\n".__LINE__. "\n$ldap->ldap_last_error\n";
						return false;
					}
				return true;	
				
			}
		$upd["automountInformation"][]=$pattern;
		if(!$ldap->Ldap_modify($dn,$upd)){
						echo __FILE__."\n".__LINE__. "\n$ldap->ldap_last_error\n";
						return false;
					}
				return true;		
				
		
	}
	
	public function nfs_delete($mount){
		$ldap=new clladp();
		$dn="cn=$mount,ou=auto.automounts,ou=mounts,$ldap->suffix";
		$ldap->ldap_delete($dn);
	}
	
	public function by_uuid_removemedia($folder,$type){
		$ldap=new clladp();
		$pattern="(&(ObjectClass=automount)(cn=$folder))";
		$attr=array("automountInformation","cn","dn");
		$suffix="ou=mounts,$ldap->suffix";
		$sr =@ldap_search($ldap->ldap_connection,$suffix,$pattern,$attr);
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		if($hash["count"]>0){
			
			for($i=0;$i<$hash["count"];$i++){
				$automountinformation=$hash[$i]["automountinformation"][0];
				if(preg_match("#UUID.+?$uuid\"#",$automountinformation)){
					if(!$ldap->ldap_delete($hash[$i]["dn"])){
						echo "Error:".__FILE__."\nLine:".__LINE__."\n".$hash[$i]["dn"]."\n".$ldap->ldap_last_error;
					}
					
				}
			}
		}
		
		
	}
	
	public function list_nfs(){
		$ldap=new clladp();
		$pattern="(&(ObjectClass=automount)(automountInformation=*))";
		$attr=array("automountInformation","cn");
		$suffix="ou=mounts,$ldap->suffix";
		$sr =@ldap_search($ldap->ldap_connection,$suffix,$pattern,$attr);
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		//print_r($hash);
		//automountinformation
		if($hash["count"]>0){
			for($i=0;$i<$hash["count"];$i++){
				$automountinformation=$hash[$i]["automountinformation"][0];				
				if(preg_match("#fstype=nfs.+?\s+(.+)#",$automountinformation,$re)){
					$arr[$hash[$i]["cn"][0]]=$re[1];
				}
			}
		}
		
		if(!is_array($arr)){return array();}
		return $arr;
	}	
	
	public function list_byuuid($uuid){
		$ldap=new clladp();
		$pattern="(&(ObjectClass=automount)(automountInformation=*))";
		$attr=array("automountInformation","cn");
		$suffix="ou=mounts,$ldap->suffix";
		
		$sr =@ldap_search($ldap->ldap_connection,$suffix,$pattern,$attr);
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		//automountinformation
		if($hash["count"]>0){
			for($i=0;$i<$hash["count"];$i++){
				$automountinformation=$hash[$i]["automountinformation"][0];
				
				if(preg_match("#UUID.+?$uuid\"#",$automountinformation)){
					$arr[$hash[$i]["cn"][0]]=$hash[$i]["automountinformation"][0];
				}
			}
		}
		
		
	if(!is_array($arr)){return array();}
	return $arr;	
		
	}
	
	
	public function AutofsSharedDir($gpid){
		$gp=new groups($gpid);
		$users=$gp->members_array;
		writelogs("$gpid group contains ".count($users)." rows",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$SharedFolders=$gp->SharedFolders_list;
		$ldap=new clladp();
		
		if(!is_array($SharedFolders)){return null;}
		while (list ($num, $local_directory) = each ($SharedFolders) ){
			$this->AutofsSharedUsers($users,$local_directory);
			}
		
	}
	
	public function AutofsDeleteDir($gpid,$folder){
		if($gpid==null){return null;}
		$gp=new groups($gpid);
		$ou=$gp->ou;
		if($ou==null){return null;}
		$ldap=new clladp();
		$folder=basename($folder);
		$dn="ou=$ou,dc=organizations,$ldap->suffix";
		
		
		writelogs("Delete in $dn for cn=$folder",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		$filter="(&(ObjectClass=automount)(cn=$folder))";
		$attrs=array("dn");
		$sr =@ldap_search($ldap->ldap_connection,$dn,$filter,$attrs);
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		for($i=0;$i<$hash["count"];$i++){
				writelogs("Found {$hash[$i]["dn"]}",__CLASS__.'/'.__FUNCTION__,__FILE__);
				if($hash[$i]["dn"]<>null){
					$ldap->ldap_delete($hash[$i]["dn"]);
				}
				
			}
	}	
	
	private function AutofsSharedUsers($users,$path){
		if(!is_array($users)){
			writelogs("users() is not an array",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return null;
			}
		if($path==null){
			writelogs("Path value is null",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return null;
			}
		reset($users);
		while (list ($uid,$null) = each ($users) ){
			$u=new user($uid);
			$u->add_SharedFolder($path);
			
		}
		
	}
	
	public function automounts_Browse(){
		$ldap=new clladp();
		$pattern='(objectClass=automount)';
		$attrs=array("automountInformation","cn");
		$sr =@ldap_search($ldap->ldap_connection,"ou=auto.automounts,ou=mounts,$ldap->suffix",$pattern,$attrs);
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		for($i=0;$i<$hash["count"];$i++){
			$folder=$hash[$i]["cn"][0];
			$automountinformation=$hash[$i]["automountinformation"][0];
			$array[$folder]=$this->parseCommand($automountinformation);
			
		}
		if(!is_array($array)){return array();}
		return $array;
		
	}
	
	private function parseCommand($line){
		if(preg_match("#-fstype=([a-z0-9]+)#",$line,$re)){
			$array["FS"]=$re[1];
		}
		
		if(preg_match('#UUID="(.+?)"$#',$line,$re)){
			$array["SRC"]="{device}:$re[1]";
		}
		
		
		if(preg_match('#([A-Za-z\.\-_0-9\$]+):\/(.+)#',$line,$re)){
			$array["SRC"]="{$re[1]}:/{$re[2]}";
		}
			if(preg_match('#(.+?):\/$#',$line,$re)){
			$array["SRC"]="{$re[1]}:/";
		}		
		
		if(preg_match('#\/\/(.+?)\/(.+)#',$line,$re)){
			$array["SRC"]="//{$re[1]}/{$re[2]}";
		}
		
		return $array;
	}
	
}

?>