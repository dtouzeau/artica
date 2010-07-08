<?php
include_once(dirname(__FILE__)."/class.ldap.inc");
include_once(dirname(__FILE__)."/class.ini.inc");
include_once(dirname(__FILE__)."/class.user.inc");

class samba{
	var $dn;
	var $main_array=array();
	var $global_conf;
	var $main_folders;
	var $main_shared_folders=array();
	var $SambaUsbShare;
	var $usb_array=array();
	var $LOCAL_SID;
	var $SambaEnableEditPosixExtension=0; // see http://wiki.samba.org/index.php/Ldapsam_Editposix
	
	function samba(){
		$this->verifyBranch();
		$this->LoadSettings();
		$this->ParseFolders();
		$this->CheckAdmin();
		$sock=new sockets();
		
		$this->SambaEnableEditPosixExtension=$sock->GET_INFO("SambaEnableEditPosixExtension");
	}
	
function verifyBranch(){
	$ldap=new clladp();
	$this->dn="cn=samba-config,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($this->dn)){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='SambaArticaClass';
		$upd["cn"]="samba-config";
		$upd["SambaSMBConf"][]=$this->BuildConfig();
		$upd["SambaUsbShare"][]="#";
		
		
		$ldap->ldap_add($this->dn,$upd);
	}
	unset($upd);
	$dn="cn=$ldap->ldap_admin,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]='organizationalRole';
		$upd["cn"][]=$ldap->ldap_admin;
		$ldap->ldap_add($dn,$upd);
		
	}

	
}


function BuildDefaults(){
	$users=new usersMenus();
	$sock=new sockets();
	if($this->main_array["global"]["workgroup"]==null){$this->main_array["global"]["workgroup"]="MSHOME";}
	if($this->main_array["global"]["server string"]==null){$this->main_array["global"]["server string"]="%h server";}
	if($this->main_array["global"]["disable netbios"]==null){$this->main_array["global"]["disable netbios"]="no";}
	if($this->main_array["global"]["domain logons"]==null){$this->main_array["global"]["domain logons"]="no";}
	if($this->main_array["global"]["domain master"]==null){$this->main_array["global"]["domain master"]="no";}
	if($this->main_array["global"]["preferred master"]==null){$this->main_array["global"]["preferred master"]="no";}
	if($this->main_array["global"]["local master"]==null){$this->main_array["global"]["local master"]="yes";}
	if($this->main_array["global"]["os level"]==null){$this->main_array["global"]["os level"]="40";}
	if($this->main_array["global"]["netbios name"]==null){$this->main_array["global"]["netbios name"]=$users->hostname;}
	if($this->main_array["global"]["dns proxy"]==null){$this->main_array["global"]["dns proxy"]="no";}
	if($this->main_array["global"]["name resolve order"]==null){$this->main_array["global"]["name resolve order"]="host lmhost wins bcast";}
	
	if($this->main_array["global"]["nt acl support"]==null){$this->main_array["global"]["nt acl support"]="yes";}
	if($this->main_array["global"]["map acl inherit"]==null){$this->main_array["global"]["map acl inherit"]="yes";}
	if($this->main_array["global"]["acl check permissions"]==null){$this->main_array["global"]["acl check permissions"]="yes";}
	if($this->main_array["global"]["acl group control"]==null){$this->main_array["global"]["acl group control"]="yes";}
	if($this->main_array["global"]["inherit permissions"]==null){$this->main_array["global"]["inherit permissions"]="no";}
	if($this->main_array["global"]["inherit acls"]==null){$this->main_array["global"]["inherit acls"]="no";}		
	
	
	
	
	
	if($users->CUPS_INSTALLED){
		$this->main_array["global"]["printing"]="cups";
		$this->main_array["global"]["printcap name"]="cups";
		$this->main_array["global"]["load printers"]="yes";
		}
		
	$SambaNetBiosName=trim($sock->GET_INFO("SambaNetBiosName"));
	if($SambaNetBiosName<>null){$this->main_array["global"]["netbios name"]=$SambaNetBiosName;}	
}

function SaveUSB(){
	while (list ($uuid, $ligne) = each ($this->usb_array) ){
		$conf=$conf."<uuid>$uuid</uuid><name>{$ligne["name"]}</name><umounttime>{$ligne["time"]}</umounttime><maxoff>{$ligne["maxoff"]}</maxoff>\n";
		
	}
	reset($this->usb_array);
	$this->SambaUsbShare=$conf;
	$upd["SambaUsbShare"][]=$this->SambaUsbShare;
	$ldap=new clladp();
	if(!$ldap->Ldap_modify($this->dn,$upd)){echo $ldap->ldap_last_error;return null;}else{
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("{success}:Samba USB Devices;\n");
		$this->SaveToLdap();
	}	
	
}

function SaveToLdap(){
	
	$conf=$this->BuildConfig();
	$upd["SambaSMBConf"][]=$conf;
	$upd["SambaUsbShare"][]=$this->SambaUsbShare;
	
	$group=new groups();
	$group->EditSambaGroups();
	$this->CreateGuestUser();
	$this->createRootID();
	$ldap=new clladp();
	if(!$ldap->Ldap_modify($this->dn,$upd)){echo $ldap->ldap_last_error;return null;}
	$sock=new sockets();
	$sock->SaveConfigFile($conf,"SambaSMBConf");
	$sock->SET_INFO("SambaEnableEditPosixExtension",$this->SambaEnableEditPosixExtension);
	$sock->getFrameWork('cmd.php?samba-save-config=yes');
	
}

function LoadSettings(){
		$ldap=new clladp();
		$res=@ldap_read($ldap->ldap_connection,$this->dn,"(objectClass=SambaArticaClass)",array());
		if($res){
			$hash=ldap_get_entries($ldap->ldap_connection,$res);
			$oldconf=$hash[0][strtolower('SambaSMBConf')][0];
			$this->SambaUsbShare=$hash[0][strtolower('SambaUsbShare')][0];
			WriteLogs("loadconfig end",__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
		
		$sock=new sockets();
		$this->global_conf=$sock->GET_INFO("SambaSMBConf");
		if(trim($this->global_conf)==null){$this->global_conf=base64_decode($sock->getFrameWork("cmd.php?samba-original-conf=yes"));}
		
		$ini=new Bs_IniHandler();
		$ini->loadString($this->global_conf);
		$this->main_array=$ini->_params;
		$this->BuildDefaults();
		if($this->SambaUsbShare==null){$this->SambaUsbShare='#';}
		$this->CleanAllDomains($this->main_array["global"]["workgroup"]);
		$this->ParseUSB();
		
	}

function ParseUSB(){
	$tbl=explode("\n",$this->SambaUsbShare);
	while (list ($index, $ligne) = each ($tbl) ){
		if(preg_match('#<uuid>(.+?)</uuid><name>(.+?)</name><umounttime>(.+?)</umounttime>#',$ligne,$re)){
			if(preg_match('#<maxoff>(.+?)</maxoff>#',$ligne,$ra)){
				$re[4]=$ra[1];
			}else{
				$re[4]=10;
			}
			
			$this->usb_array[$re[1]]=array("name"=>$re[2],"time"=>$re[3],"maxoff"=>$re[4]);
		}
		
	}
}
	

function ParseFolders(){
	reset($this->main_array);
	while (list ($FOLDER, $ligne) = each ($this->main_array) ){
		if($FOLDER<>"global"){
			if(is_array($ligne)){
				$this->main_folders[$FOLDER]=$FOLDER;
				$this->main_shared_folders[$this->main_array[$FOLDER]["path"]]=$FOLDER;
			}
		}
	}
	reset($this->main_array);
}


function CheckAdmin(){
	$ldap=new clladp();
	$dn="cn=administrator,ou=users,dc=samba,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN("$dn")){
		$this->CreateAdministrator("administrator",$ldap->ldap_password);
	}
	
}


function GetAdminUidNumber($admin_name){
	$ldap=new clladp();
	$dn="cn=$admin_name,ou=users,dc=samba,dc=organizations,$ldap->suffix";
	$sr =@ldap_read($ldap->ldap_connection,$dn,'objectClass=*');
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
			return $hash[0][strtolower("uidNumber")][0];	
			
		}
	}
	
function GetAdminPassword($admin_name){
	$ldap=new clladp();
	$dn="cn=$admin_name,ou=users,dc=samba,dc=organizations,$ldap->suffix";
	$sr =@ldap_read($ldap->ldap_connection,$dn,'objectClass=*');
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
			return $hash[0][strtolower("userPassword")][0];	
		}
	}

	
function EditAdministrator($admin_name,$password){
	$ldap=new clladp();
	$dn="cn=$admin_name,ou=users,dc=samba,dc=organizations,$ldap->suffix";
	$ldap=new clladp();
	if(!$ldap->ExistsDN("$dn")){
		$this->CreateAdministrator($admin_name,$password);
		return true;
	}
	
	$sr =@ldap_read($ldap->ldap_connection,$dn,'objectClass=*');
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		}	
	
	$old_password=$hash[0][strtolower("userPassword")][0];
	$uidNumber=$hash[0][strtolower("uidNumber")][0];
	$sock=new sockets();
	$datas=trim(base64_decode($sock->getFrameWork("cmd.php?smbpass=".base64_encode($password))));
	if(preg_match('#(.+?):(.+)#',$datas,$re)){
		$sambaLMPassword=$re[1];
		$sambaNTPassword=$re[2];
	}
	if($password==null){$password=$old_password;}
		
			
		
	
	$group=new groups();
	$user=new usersMenus();
	$sambaPrimaryGroupSID=$group->samba_group_sid_from_gid('512');
	$sambaSID=$ldap->LOCAL_SID().'-'. (2 * $uidNumber * 1000);		

	$upd["sambaLMPassword"][0]=		$sambaLMPassword;
	$upd["sambaNTPassword"][0]=		$sambaNTPassword;
	$upd["sambaPrimaryGroupSID"][0]=$sambaPrimaryGroupSID;
	$upd["userPassword"][0]		   =$password;
	$upd["sambaSID"][0]=			$sambaSID;
	$upd["gidNumber"][0]='512';
	
	$group=new groups(544);
	$group->AddUsertoThisGroup("administrator");
	writelogs("Editing groups...",__CLASS__.'/'.__FUNCTION__,__FILE__);
	$group->EditSambaGroups();

	
	
	if(!$ldap->Ldap_modify($dn,$upd)){
		writelogs("Unable to edit administrator:$admin_name settings!!!...",__CLASS__.'/'.__FUNCTION__,__FILE__);
		echo $ldap->ldap_last_error;
		exit;
	}
	$tpl=new templates();
	writelogs("Success edit administrator:$admin_name settings!!!...",__CLASS__.'/'.__FUNCTION__,__FILE__);
	
	$user=new user();
	$rootpassword=$user->GetRootPassword();	
	$sock->getfile("SetNetUsePrivs:$rootpassword;SeAddUsersPrivilege;$admin_name;grant");
	$sock->getfile("SetNetUsePrivs:$rootpassword;SeBackupPrivilege;$admin_name;grant");
	$sock->getfile("SetNetUsePrivs:$rootpassword;SeDiskOperatorPrivilege;$admin_name;grant");
	$sock->getfile("SetNetUsePrivs:$rootpassword;SeMachineAccountPrivilege;$admin_name;grant");
	$sock->getfile("SetNetUsePrivs:$rootpassword;SePrintOperatorPrivilege;$admin_name;grant");
	$sock->getfile("SetNetUsePrivs:$rootpassword;SeRemoteShutdownPrivilege;$admin_name;grant");
	$sock->getfile("SetNetUsePrivs:$rootpassword;SeRestorePrivilege;$admin_name;grant");
	$sock->getfile("SetNetUsePrivs:$rootpassword;SeTakeOwnershipPrivilege;$admin_name;grant");
	
	
}

function CreateGuestUser(){
	$ldap=new clladp();
	$user=new usersMenus();
	
	$dn="cn=nobody,ou=users,dc=samba,dc=organizations,$ldap->suffix";
	if($ldap->ExistsDN($dn)){
		$ldap->ldap_delete($dn);
	}
					
	$dn="uid=nobody,ou=users,dc=samba,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN("$dn")){
		$u_ser=new user();
		$group=new groups(null);
		$upd["objectClass"][]='account';
		$upd["objectClass"][]='posixAccount';
		$upd["objectClass"][]='sambaSAMAccount';
		$uidNumber=$u_ser->uidNumberCheck(1);		
		$upd["uidNumber"][0]=$uidNumber;
		$sambaPrimaryGroupSID=$group->samba_group_sid_from_gid('514');
		$sambaSID=$ldap->LOCAL_SID().'-'. (2 * $uidNumber * 1000);	
		$upd["sambaPrimaryGroupSID"][0]=$sambaPrimaryGroupSID;
		$upd["sambaAcctFlags"][0]=		'[DU         ]';
		$upd["sambaSID"][0]=			$sambaSID;
		$upd["loginshell"][0]='/bin/sh';
		$upd["uid"][0]='nobody';	
		$upd["homedirectory"][0]="/nonexistent";
		$upd["gidnumber"][0]="1999";
		$upd["displayname"][0]="nobody";
		$upd["uid"][0]="nobody";
		$upd["cn"][0]="nobody";		
		if(!$ldap->ldap_add($dn,$upd)){
			echo $ldap->ldap_last_error ."(line ".__LINE__.":".__CLASS__.'/'.__FUNCTION__.',' .__FILE__;
		}
		
	}

}





function CreateAdministrator($admin_name,$password){
	$user=new usersMenus();
	if(!$user->SAMBA_INSTALLED){return null;}
	$ldap=new clladp();
	$sock=new sockets();
	
	
	
	$dn="cn=$admin_name,ou=users,dc=samba,dc=organizations,$ldap->suffix";
	
	
	
	$create=false;	
		
			
	
	
	if(!$ldap->ExistsDN("$dn")){
				$u_ser=new user();
				$create=true;
				writelogs("Create administrator account",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$upd["objectClass"][]='top';
				$upd["objectClass"][]='inetOrgPerson';
				$upd["objectClass"][]='sambaSAMAccount';
				$upd["objectClass"][]='posixAccount';
				$uidNumber=$u_ser->uidNumberCheck(1);
				$upd["uidNumber"][0]=$uidNumber;
				if($admin_name=="root"){$upd["homeDirectory"][]='/root';}else{$upd["homeDirectory"][]="/home/$admin_name";}
				$upd["cn"][]=$admin_name;
				$upd["sn"][]=$admin_name;
				$upd["uid"][]=$admin_name;
				}
				$sock=new sockets();
				$datas=trim(base64_decode($sock->getFrameWork("cmd.php?smbpass=".base64_encode($password))));			
				if(preg_match('#(.+?):(.+)#',$datas,$re)){
					$sambaLMPassword=$re[1];
					$sambaNTPassword=$re[2];
					}
				writelogs("sambaLMPassword=$sambaLMPassword, sambaNTPassword=$sambaNTPassword",__CLASS__.'/'.__FUNCTION__,__FILE__);				
				
				
				$group=new groups();
				$sambaPrimaryGroupSID=$group->samba_group_sid_from_gid('512');
				$sambaSID=$ldap->LOCAL_SID().'-'. (2 * $uidNumber * 1000);
				
				//Samba
				$upd["sambaPwdLastSet"][0]=		time();
				$upd["sambaLogonTime"][0]=		'0';
				$upd["sambaLogoffTime"][0]=		'2147483647';
				$upd["sambaKickoffTime"][0]=	'2147483647';
				$upd["sambaPwdCanChange"][0]=	'1286597349';
				$upd["sambaPwdMustChange"][0]=	'2147483647';
				$upd["sambaPrimaryGroupSID"][0]=$sambaPrimaryGroupSID;
				$upd["sambaLMPassword"][0]=		$sambaLMPassword;
				$upd["sambaNTPassword"][0]=		$sambaNTPassword;
				$upd["sambaAcctFlags"][0]=		'[UX         ]';
				$upd["sambaSID"][0]=			$sambaSID;
				
				writelogs("upd[\"sambaLMPassword\"][]={$upd["sambaLMPassword"][0]}",__CLASS__.'/'.__FUNCTION__,__FILE__);
			
				
				//posixAccount
				$upd["gidNumber"][0]='512';
				$upd["loginShell"][0]='/bin/sh';
				$upd["userPassword"][]=$password;
	

				
	
				if($create){
					if(!$ldap->ldap_add($dn,$upd)){
						echo $ldap->ldap_last_error;
					}
					
				}else{
						$group=new groups(544);
						$group->AddUsertoThisGroup("administrator");
						$group-EditSambaGroups();
					
					echo $dn ." success\n";
				}
	
	
}

function createRootID(){
	$ldap=new clladp();
	$group=new groups();
	$user=new usersMenus();
	$dn="uid=root,ou=users,dc=samba,dc=organizations,$ldap->suffix";
	if($ldap->ExistsDN($dn)){
		$ldap->ldap_delete($dn);
	}
	
	$sock=new sockets();
	$datas=trim(base64_decode($sock->getFrameWork("cmd.php?smbpass=". base64_encode($ldap->ldap_password))));			
	if(preg_match('#(.+?):(.+)#',$datas,$re)){
		$sambaLMPassword=$re[1];
		$sambaNTPassword=$re[2];
	}	
	
	$sambaPrimaryGroupSID=$group->samba_group_sid_from_gid('512');
	$sambaSID=$ldap->LOCAL_SID().'-1000';
				$upd["objectClass"][]='top';
				$upd["objectClass"][]='inetOrgPerson';
				$upd["objectClass"][]='sambaSAMAccount';
				$upd["objectClass"][]='posixAccount';
				$upd["uidNumber"][0]=0;
				$upd["gidNumber"][0]=0;	
				$upd["cn"][0]="root";
				$upd["uid"][0]="root";
				$upd["sn"][0]="root";
				$upd["loginShell"][0]='/bin/bash';
				$upd["homeDirectory"][0]='/root';
				$upd["sambaPwdLastSet"][0]=		time();
				$upd["sambaLogonTime"][0]=		'0';
				$upd["sambaLogoffTime"][0]=		'2147483647';
				$upd["sambaKickoffTime"][0]=	'2147483647';
				$upd["sambaPwdCanChange"][0]=	'1286597349';
				$upd["sambaPwdMustChange"][0]=	'2147483647';
				$upd["sambaPrimaryGroupSID"][0]=$sambaPrimaryGroupSID;
				$upd["sambaLMPassword"][0]=		$sambaLMPassword;
				$upd["sambaNTPassword"][0]=		$sambaNTPassword;
				$upd["sambaAcctFlags"][0]=		'[UX         ]';
				$upd["sambaSID"][0]=			$sambaSID;	
				$upd["userPassword"][0]=$ldap->ldap_password;			
				
if(!$ldap->ldap_add($dn,$upd)){
	echo $ldap->ldap_last_error.":".__FUNCTION__.",\n".__FILE__."\n";
	}	
	
}

function ChangeSID($sid){
	$ldap=new clladp();
	$dn="sambaDomainName={$this->main_array["global"]["workgroup"]},$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		echo "Unable to stat $dn\n";
	}
	
	$upd["sambaSID"][0]=$sid;
	$ldap->Ldap_modify($dn,$upd);
	$sock=new sockets();
	
	
	$ldap=new clladp();
	$dn="cn=nobody,ou=users,dc=samba,dc=organizations,$ldap->suffix";
	if($ldap->ExistsDN($dn)){
		$ldap->ldap_delete($dn);
		$this->CreateGuestUser();
	}
	
	$sock->getFrameWork("cmd.php?samba-change-sid=$sid");
	
}

function BuildConfig(){
			$users=new usersMenus();
			$ldap=new clladp();
			$this->BuildDefaults();
			$posix_getuid=posix_getuid();
			
			
			$sock=new sockets();
			$SambaRoamingEnabled=$sock->GET_INFO('SambaRoamingEnabled');
			$SambaNetBiosName=trim($sock->GET_INFO("SambaNetBiosName"));
			$EnableSambaActiveDirectory=$sock->GET_INFO("EnableSambaActiveDirectory");
			$config_activedirectory=unserialize(base64_decode($sock->GET_INFO("SambaAdInfos")));
			
			if($SambaNetBiosName<>null){$this->main_array["global"]["netbios name"]=$SambaNetBiosName;}
			
			
			writelogs("log level=".$this->main_array["global"]["log level"],__CLASS__.'/'.__FUNCTION__,__FILE__);
			writelogs("SambaRoamingEnabled=$SambaRoamingEnabled",__CLASS__.'/'.__FUNCTION__,__FILE__);
			
			if($this->main_array["global"]["log file"]==null){$this->main_array["global"]["log file"]="/var/log/samba/log.%m";}
			if($this->main_array["global"]["log level"]==null){$this->main_array["global"]["log level"]=0;}
			
			if($SambaRoamingEnabled==1){
				$this->main_array["global"]["domain logons"]="yes";
			}
			
			if(preg_match('#(.+?)\.#',$this->main_array["global"]["netbios name"],$re)){
				$this->main_array["global"]["netbios name"]=$re[1];
			}
			
			$conf=$conf ."[global]\n";

			
			if($EnableSambaActiveDirectory){
				$conf=$conf ."workgroup = ". strtoupper($config_activedirectory["WORKGROUP"])."\n";
			}else{
				$conf=$conf ."workgroup = {$this->main_array["global"]["workgroup"]}\n";
			}
			$conf=$conf ."netbios name = {$this->main_array["global"]["netbios name"]}\n";
			$conf=$conf ."server string = {$this->main_array["global"]["server string"]}\n";
			$conf=$conf ."disable netbios ={$this->main_array["global"]["disable netbios"]}\n";
			
			writelogs("name resolve order ={$this->main_array["global"]["name resolve order"]}",__CLASS__,__FUNCTION__,__FILE__);
			$conf=$conf ."name resolve order ={$this->main_array["global"]["name resolve order"]}\n";
			$conf=$conf ."dns proxy ={$smb->main_array["global"]["dns proxy"]}\n";
			$conf=$conf ."syslog = 3\n";
			$conf=$conf ."log level = {$this->main_array["global"]["log level"]}\n";
			$conf=$conf ."log file = {$this->main_array["global"]["log file"]}\n";
			$conf=$conf ."debug timestamp = yes\n";
			$conf=$conf ."unix extensions = no\n";
			$conf=$conf ."usershare allow guests = no\n";
			$conf=$conf ."usershare max shares = 100\n";
			$conf=$conf ."usershare owner only = true\n";
			$conf=$conf ."usershare path=/var/lib/samba/usershares/data\n";
			
			
			
			
			if($users->CUPS_INSTALLED){
				$conf=$conf ."\n";
				$conf=$conf ."# printer  -----------------------------------\n";
				$conf=$conf ."load printers = yes\n";
				$conf=$conf ."printing = cups\n";
				$conf=$conf ."printcap name = cups\n";
				$conf=$conf ."\n";
			}


			
			
			$domain_logon=$this->main_array["global"]["domain logons"];
			
			$conf=$conf ."\n";
			$conf=$conf ."# Controler ?? -----------------------------------\n";
			if($users->WINBINDD_INSTALLED){
			if($posix_getuid==0){echo "Starting......: Samba winbindd service is installed\n";}
				if($EnableSambaActiveDirectory==1){
					if($posix_getuid==0){echo "Starting......: Samba join domain {$config_activedirectory["ADDOMAIN"]}\n";}
					$conf=$conf ."security = ADS\n";
					$conf=$conf ."realm = ". strtoupper($config_activedirectory["ADDOMAIN"])."\n";
					$conf=$conf ."client use spnego = yes\n";
				}else{
					$conf=$conf ."security = user\n";
					
				}			
			}else{
				$conf=$conf ."security = user\n";
			}
			$conf=$conf ."enable privileges = yes\n";
			if($EnableSambaActiveDirectory<>1){
				$conf=$conf ."domain master = {$this->main_array["global"]["domain master"]}\n";
				$conf=$conf ."local master = {$this->main_array["global"]["local master"]}\n";
				$conf=$conf ."preferred master = {$this->main_array["global"]["preferred master"]}\n";
				$conf=$conf ."domain logons = {$this->main_array["global"]["domain logons"]}\n";
			}
			$conf=$conf ."os level = {$this->main_array["global"]["os level"]}\n";
			$conf=$conf ."ldap passwd sync = no\n";
			
			$users=new usersMenus();
			$conf=$conf ."# WINBINDD  ($users->WINBINDD_INSTALLED)-----------------------------------\n";
			if($users->WINBINDD_INSTALLED){
				$conf=$conf ."winbind use default domain = yes\n";
				if($EnableSambaActiveDirectory==1){
					if($posix_getuid==0){echo "Starting......: Samba master server {$config_activedirectory["ADSERVER"]}.{$config_activedirectory["ADDOMAIN"]}\n";}
					$conf=$conf ."password server={$config_activedirectory["ADSERVER"]}.{$config_activedirectory["ADDOMAIN"]}\n";					
				}else{
					$conf=$conf ."password server=*\n";
				}
				$conf=$conf ."encrypt passwords = yes\n";
				$conf=$conf ."winbind separator = +\n";			
				$conf=$conf ."winbind uid = 10000-20000\n";
				$conf=$conf ."winbind gid = 10000-20000\n";
				$conf=$conf ."winbind enum users = yes\n";
				$conf=$conf ."winbind enum groups = yes\n";
				$conf=$conf ."\n";
			}
			
			$conf=$conf ."\n";
			$conf=$conf ."#VISTA/Windows7 compatibility\n";
			$conf=$conf ."client lanman auth = yes\n";
        	$conf=$conf ."client ntlmv2 auth = yes\n";
        	$conf=$conf ."\n";
        	
        	$conf=$conf ."\n";
        	$conf=$conf ."#ACLs settings\n";
        	$conf=$conf ."nt acl support={$this->main_array["global"]["nt acl support"]}\n";
        	$conf=$conf ."map acl inherit={$this->main_array["global"]["map acl inherit"]}\n";
        	$conf=$conf ."acl check permissions={$this->main_array["global"]["acl check permissions"]}\n";
        	$conf=$conf ."acl group control={$this->main_array["global"]["acl group control"]}\n";
        	$conf=$conf ."inherit permissions={$this->main_array["global"]["inherit permissions"]}\n";
        	$conf=$conf ."inherit acls={$this->main_array["global"]["inherit acls"]}\n";      	
        	$conf=$conf ."acl map full control=yes\n";
        	$conf=$conf ."force unknown acl user = no\n";
			
			$conf=$conf ."\n";
			$conf=$conf ."# LDAP settings -----------------------------------\n";
			if($this->SambaEnableEditPosixExtension==0){
				$conf=$conf ."ldap delete dn = no\n";
				$conf=$conf ."passdb backend = ldapsam:ldap://$ldap->ldap_host:$ldap->ldap_port\n";
				$conf=$conf . "#scripts -----------------------------------\n";
				$conf=$conf . "add machine script = /usr/share/artica-postfix/bin/artica-install --samba-add-computer \"%u\"\n";				
			}			
			
			$conf=$conf ."ldap admin dn = cn=$ldap->ldap_admin,$ldap->suffix\n";
			$conf=$conf ."ldap suffix = $ldap->suffix\n";
			//$conf=$conf ."ldap group suffix = ou=groups,dc=samba,dc=organizations\n";
			//$conf=$conf ."ldap user suffix =  ou=users,dc=samba,dc=organizations\n";
			$conf=$conf ."ldap group suffix = dc=organizations\n";
			$conf=$conf ."ldap user suffix =  dc=organizations\n";			
			$conf=$conf ."ldap machine suffix = ou=Computer,dc=samba,dc=organizations\n";
			
			if($this->SambaEnableEditPosixExtension==1){
				$group=new groups(null);
				$group->BuildOrdinarySambaGroups();
				$conf=$conf ."\n\n#Samba and the Editposix/Trusted Ldapsam extension\n";
				$conf=$conf ."ldap idmap suffix = ou=idmap,dc=samba,dc=organizations\n";
				$conf=$conf ."ldap delete dn = yes\n";
				$conf=$conf ."encrypt passwords = true\n";
				$conf=$conf ."passdb backend = ldapsam\n";
				$conf=$conf ."ldapsam:trusted=yes\n";
	  			$conf=$conf ."ldapsam:editposix=yes\n";
	  			$conf=$conf ."template homedir = /home/%U\n";
	  			$conf=$conf ."template shell = /bin/false\n";
	  			$conf=$conf ."idmap domains = {$this->main_array["global"]["workgroup"]}\n";
	  			$conf=$conf ."idmap config {$this->main_array["global"]["workgroup"]}:backend = ldap\n";
	  			$conf=$conf ."idmap config {$this->main_array["global"]["workgroup"]}:readonly = no\n";
	    		$conf=$conf ."idmap config {$this->main_array["global"]["workgroup"]}:default = yes\n";
	    		$conf=$conf ."idmap config {$this->main_array["global"]["workgroup"]}:ldap_base_dn = ou=idmap,dc=samba,dc=organizations,$ldap->suffix\n";
	    		$conf=$conf ."idmap config {$this->main_array["global"]["workgroup"]}:ldap_user_dn = cn=$ldap->ldap_admin,$ldap->suffix\n";
	    		$conf=$conf ."idmap config {$this->main_array["global"]["workgroup"]}:ldap_url = ldap://localhost\n";
	    		$conf=$conf ."idmap config {$this->main_array["global"]["workgroup"]}:range = 20000-500000\n";
	    		$conf=$conf ."idmap alloc backend = ldap\n";
	    		$conf=$conf ."idmap alloc config:ldap_base_dn = ou=idmap,dc=samba,dc=organizations,$ldap->suffix\n";
	    		$conf=$conf ."idmap alloc config:ldap_user_dn = cn=$ldap->ldap_admin,$ldap->suffix\n";
	    		$conf=$conf ."idmap alloc config:ldap_url = ldap://localhost\n";
	    		$conf=$conf ."idmap alloc config:range = 20000-500000\n";
				}
				
				
			$conf=$conf ."ldap ssl  = no\n";	
			if($SambaRoamingEnabled==1){
				$conf=$conf ."logon path = \\\\{$this->main_array["global"]["netbios name"]}\profile\%U\n";
				$conf=$conf ."logon drive = P:\n";
				$conf=$conf ."logon home = \\\\{$this->main_array["global"]["netbios name"]}\%U\n";
			}else{
				$conf=$conf ."logon path =\"\"\n";
				$conf=$conf ."logon home =\"\"\n";
				$conf=$conf ."logon drive = \"\"\n";
			}
			

			$conf=$conf ."socket options = TCP_NODELAY IPTOS_LOWDELAY IPTOS_THROUGHPUT SO_KEEPALIVE SO_RCVBUF=8192 SO_SNDBUF=8192\n";
			$conf=$conf ."case sensitive = No\n";
			$conf=$conf ."default case = lower\n";
			$conf=$conf ."preserve case = yes\n";
			$conf=$conf ."short preserve case = yes\n";
			$conf=$conf ."#character set = iso8859-1\n";
			$conf=$conf ."#domain admin group = @admin\n";
			$conf=$conf ."dns proxy = No\n";
			$conf=$conf ."wins support = Yes\n";
			$conf=$conf ."#hosts allow = 192.168.0. 127.\n";
			$conf=$conf ."winbind use default domain = yes\n";
			$conf=$conf ."winbind enum users = yes\n";
			$conf=$conf ."winbind enum groups = yes\n";
			

			
			
			$conf=$conf ."time server = yes\n";
			$conf=$conf ."#MDFS parameters\n";
			$conf=$conf ."msdfs root = no\n";
			$conf=$conf ."host msdfs = no\n";
			
			$conf=$conf ."\n";
			$conf=$conf ."# Shared Folders lists -----------------------------------\n";
			unset($this->main_array["global"]);
			
			if(is_array($this->usb_array)){
				while (list ($uuid, $array) = each ($this->usb_array) ){
				$this->main_array[$array["name"]]["path"]="/opt/artica/usb_mount/$uuid";
				$this->main_array[$array["name"]]["create mask"]="0660";
				$this->main_array[$array["name"]]["directory mask"]="0770";
				$this->main_array[$array["name"]]["force user"]="root";
				$this->main_array[$array["name"]]["force group"]="root";
				}
			}
			
			
			unset($this->main_array["netlogon"]);
			unset($this->main_array["profile"]);
			unset($this->main_array["profiles"]);
			unset($this->main_array["profiles.V2"]);
			unset($this->main_array["homes"]);
			unset($this->main_array["printers"]);
			unset($this->main_array["print$"]);
			
			
			while (list ($FOLDER, $ligne) = each ($this->main_array) ){
				
					if(is_array($ligne)){
						if($FOLDER==null){
							if($ligne["path"]==null){continue;}
							$FOLDER=basename($ligne["path"]);
						}
											
						$conf=$conf ."\n[$FOLDER]\n";
						while (list ($KEY, $VALS) = each ($ligne) ){
							$conf=$conf."$KEY = $VALS\n";
						
						}
				}
			}
			
			
				$conf=$conf ."[netlogon]\n";
				$conf=$conf ."path = /home/netlogon/\n";
				$conf=$conf ."writable = No\n";
				$conf=$conf ."browseable = No\n";
				$conf=$conf ."write list = root\n\n";
			
			
			if(strtolower($domain_logon)=='yes'){
				if($SambaRoamingEnabled==1){
					$sock=new sockets();
					$profile_path=$sock->GET_INFO('SambaProfilePath');
					if(trim($profile_path)==null){$profile_path="/home/export/profile";}
					$conf=$conf ."[profiles]\n";
					$conf=$conf ."#see USERENV.LOG in c:\windows\debug\UserMod on Windows XP for troubleshooting\n";
					$conf=$conf ."path = $profile_path\n";
					$conf=$conf ."oplocks = false\n";
					$conf=$conf ."level2 oplocks = false\n";
					$conf=$conf ."csc policy = disable\n";
					$conf=$conf ."browseable = No\n";
					$conf=$conf ."writeable = Yes\n";
					$conf=$conf ."read only = No\n";
					$conf=$conf ."profile acls = yes\n";
					$conf=$conf ."create mask = 0700\n";
					$conf=$conf ."directory mask = 0700\n";
					$conf=$conf ."comment = Roaming user profile storage\n";
					$conf=$conf ."\n";
					$conf=$conf ."[profiles.V2]\n";
	        		$conf=$conf ."copy = profiles\n";
	        		$conf=$conf ."browseable = no\n";
	        		$conf=$conf ."csc policy = disable\n";	        		
	        		$conf=$conf ."\n";
					$conf=$conf ."[profile]\n";
	        		$conf=$conf ."copy = profiles\n";
	        		$conf=$conf ."browseable = no\n";	        		

				}
			}	
					#
				$conf=$conf ."[homes]\n";
				$conf=$conf ."comment = Personnal Folder\n";
				$conf=$conf ."browseable = No\n";
				$conf=$conf ."writeable = Yes\n\n"; 
			
			
			if($users->CUPS_INSTALLED){
				$conf=$conf ."\n\n[printers]\n";
				$conf=$conf ."comment = Printers\n";
				$conf=$conf ."browseable = yes\n";
				$conf=$conf ."path = /var/spool/samba\n";
				$conf=$conf ."printable = yes\n";
				$conf=$conf ."public = yes\n";
				$conf=$conf ."guest ok = yes\n";
				$conf=$conf ."writable = no\n";
 				$conf=$conf ."create mode = 0700\n\n";
				$conf=$conf ."[print$]\n";
  				$conf=$conf ."comment = Printers drivers\n";
  				$conf=$conf ."path = /etc/samba/printer_drivers\n";
  				$conf=$conf ."browseable = yes\n";
  				$conf=$conf ."guest ok = no\n";
  				$conf=$conf ."read only = yes\n";
  				$conf=$conf ."write list = root,administrator,@Administrators,@lpadmin\n";			
			}

			$this->CleanAllDomains($this->main_array["global"]["workgroup"]);	
			reset($this->main_array);
			return $conf;
	
}

function CleanAllDomains($CurrentDomain){
	if($CurrentDomain==null){return null;}
	
	//WriteLogs("sambaDomainName Current domain \"$CurrentDomain\" against ".strtolower($CurrentDomain) . " and " .strtolower($this->main_array["global"]["netbios name"]) ,__CLASS__.'/'.__FUNCTION__,__FILE__);
	$ldap=new clladp();
	
	$upper=strtoupper($CurrentDomain);
	$baddn="sambaDomainName=$upper,dc=samba,dc=organizations,$ldap->suffix";
	
	WriteLogs("Check $baddn",__CLASS__.'/'.__FUNCTION__,__FILE__);
	
	if($ldap->ExistsDN($baddn)){
		WriteLogs("Delete $baddn",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$ldap->ldap_delete($baddn,true);
	}
	
	
	
	$group=new groups();
	$group->BuildOrdinarySambaGroups();
	if(strlen($CurrentDomain)==0){return null;}
	
	$users=new usersMenus();
	$hostname=$users->hostname;
	if(preg_match("#^(.+?)\.#",$hostname,$re)){$hostname=strtolower($re[1]);}	
	$query="(ObjectClass=sambaDomain)";
	$filter=array("dn","sambaDomainName");
	$h=$ldap->Ldap_search($ldap->suffix,$query,$filter);
	if(!is_array($h)){
		$this->CreateDomainName($CurrentDomain);
		return null;}

	for($i=0;$i<$h["count"];$i++){
		$domain=$h[$i][strtolower("sambaDomainName")][0];
		$domain=strtolower($domain);
		//WriteLogs("sambaDomainName found \"$domain\" against ".strtolower($CurrentDomain),__CLASS__.'/'.__FUNCTION__,__FILE__);
		if($domain==strtolower($CurrentDomain)){
			continue;
		}
		if($domain==strtolower($this->main_array["global"]["netbios name"])){
			continue;
		}
		
		WriteLogs("sambaDomainName delete {$h[$i]["dn"]}",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$ldap->ldap_delete($h[$i]["dn"],false);
		
	}
	
	$this->CreateDomainName($CurrentDomain);
	

	
}

function CreateDomainName($CurrentDomain){
	$CurrentDomain=strtoupper($CurrentDomain);
	$ldap=new clladp();
	$users=new usersMenus();
	$SID=$ldap->LOCAL_SID();
	$dn="sambaDomainName=$CurrentDomain,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]="sambaDomain";
		$upd["sambaDomainName"][]="$CurrentDomain";
		$upd["sambaSID"][]="$SID";
		$upd["sambaAlgorithmicRidBase"][]="1000";
		$upd["sambaNextUserRid"][]="1000";
		$upd["sambaMinPwdLength"][]="5";
		$upd["sambaPwdHistoryLength"][]="0";
		$upd["sambaLogonToChgPwd"][]="0";
		$upd["sambaMaxPwdAge"][]="-1";
		$upd["sambaMinPwdAge"][]="0";
		$upd["sambaLockoutDuration"][]="30";
		$upd["sambaLockoutObservationWindow"][]="30";
		$upd["sambaLockoutThreshold"][]="0";
		$upd["sambaForceLogoff"][]="-1";
		$upd["sambaRefuseMachinePwdChange"][]="0";
		if(!$ldap->ldap_add($dn,$upd)){
			echo "CreateDomainName($CurrentDomain)\n$SID\n$ldap->ldap_last_error\n";
			exit;
		}
		else{
			$tpl=new templates();
			writelogs("sambaDomainName:Creating $CurrentDomain $SID success",__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
		
	}
	
	
}


function hash_privileges($folder){
	$ta=explode(',',$this->main_array[$folder]["valid users"]);
	if(is_array($ta)){
		while (list ($KEY, $VALS) = each ($ta) ){
			if(trim($VALS)<>null){
				$h[$VALS]["valid users"]='yes';
			}
		}
	}
	
$ta=explode(',',$this->main_array[$folder]["write list"]);
	if(is_array($ta)){
		while (list ($KEY, $VALS) = each ($ta) ){
			if(trim($VALS)<>null){
				$h[$VALS]["write list"]='yes';
			}
		}
	}

$ta=explode(',',$this->main_array[$folder]["read list"]);
	if(is_array($ta)){
		while (list ($KEY, $VALS) = each ($ta) ){
			if(trim($VALS)<>null){
				$h[$VALS]["read list"]='yes';
			}
		}
	}	
	
	return $h;
}

function GetUsrsRights($userid){
	
	include_once(dirname(__FILE__).'/class.user.inc');
	
	$folders=$this->main_shared_folders;
	
		while (list ($num, $folder_name) = each ($folders) ){
		if($folder_name=="print$"){continue;}
		if($folder_name=="printers"){continue;}
		if($folder_name=="netlogon"){continue;}
		if($folder_name=="homes"){continue;}
		if($folder_name=="profile"){continue;}
		if($folder_name=="profiles"){continue;}
		$res[$folder_name]=$num;
		}
		
	if(!is_array($res)){return array();}
	
	$user=new user($userid);
	$user_group=$user->GetGroups($userid);
	$folderallowed[$user->homeDirectory]=true;
	while (list ($foldername, $path) = each ($res) ){
		
		
		$hash=$this->hash_privileges($foldername);
		if(!is_array($hash)){
			$folderallowed[$path]=true;
			continue;
		}
		while (list ($userGroup, $rights) = each ($hash) ){	
			if(substr($userGroup,0,1)=='@'){
				if(is_numeric($user_group[substr($userGroup,1,strlen($userGroup))])){
					$folderallowed[$path]=true;
					continue;
				}
				
			}
			
			if($userGroup==$userid){
				$folderallowed[$path]=true;
				continue;
			}
			
		}
		
	}
	
	if(!is_array($folderallowed)){return array();}else{	return $folderallowed;}
	
}
	
	
	
}

function CheckSambaRights(){
	$user=new usersMenus();
	if($user->AsArticaAdministrator){return true;}
	if($user->AsSambaAdministrator){return true;}
	if($user->AsOrgStorageAdministrator){return true;}
	return false;
}
/*
 * 
 * A voir :inherit permissions
 * A voir : hide unwriteable files
 * a voir les chown et les acls.
 * 
 cat /var/cache/samba/browse.dat
 Configuring the Windows XP Client:

Notes : Only Windows XP-Professional Edition can join the Domain, it does not work for WindowsXP-Home Edition.

STEPS:

1) Make sure that the workstation belonged to the same workgroup as the server and have a fixed IP address and hostname assigned.

2) Change the registry entry, run the command regedt32 and do the below
a) RequireSignOrSeal Registry hack

HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\netlogon\parameters
"RequireSignOrSeal"=dword:00000000

b) Use the Registry Editor and edit the
HKEY_LOCAL_MACHINE\SOFTWARE\Policies\Microsoft\Windows\System\CompatibleRUPSecurity to have the DWORD value of 1

3) Use the Group Policy Editor (gpedit.msc) and enable "Computer Configuration\Administrative Templates\System\User Profiles\Do not check for user ownership of Roaming Profile Folders".

4) Go to MyComputer right click Properties. Go to Change and click on Domain and enter the domain-name you want to join. When joining the domain for the First time enter userid as root and give the samba password. Make sure there is an entry for the root in the smbpasswd (samba password) file.

5) Reboot and then the changes will be effective.
http://wiki.samba.org/index.php/Samba4/Andrew_and_Jelmers_Fantasy_Page 
*/
?>