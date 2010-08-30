<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/ressources/class.lvm.org.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.groups.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}

if($argv[1]=='--users'){
	CountDeUsers();
	die();
}

if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}
if(systemMaxOverloaded()){
	writelogs("This system is too many overloaded, die()",__FUNCTION__,__FILE__,__LINE__);
	die();
}

if($argv[1]=='--home'){CheckHomeFor($argv[2],null);die();}


if($argv[1]=='--homes'){
	ParseHomeDirectories();
	die();
}

if($argv[1]=='--reconfigure'){
	reconfigure();
	die();
}
if($argv[1]=='--samba-audit'){
	SambaAudit();
	die();
}

if($argv[1]=='--disable-profiles'){
	DisableProfiles();
	die();
}
if($argv[1]=='--enable-profiles'){
	EnableProfiles();
	die();
}
if($argv[1]=='--logon-scripts'){LogonScripts();die();}
if($argv[1]=='--fix-lmhost'){fix_lmhosts();die();}

if($argv[1]=='--fix-HideUnwriteableFiles'){fix_hide_unwriteable_files();die();}






$users=new usersMenus();
if(!$users->SAMBA_INSTALLED){echo "Samba is not installed\n";die();}

FixsambaDomainName();

function FixsambaDomainName(){
	$smb=new samba();
	$workgroup=$smb->main_array["global"]["workgroup"];
	$smb->CleanAllDomains($workgroup);
	}


function ParseHomeDirectories(){
		$ldap=new clladp();
		$attr=array("homeDirectory","uid","dn");
		$pattern="(&(objectclass=sambaSamAccount)(uid=*))";
		$sock=new sockets();
		
		if(trim($profile_path)==null){$profile_path="/home/export/profile";}	
		$sock=new sockets();
		$SambaRoamingEnabled=$sock->GET_INFO('SambaRoamingEnabled');
		if($SambaRoamingEnabled==1){EnableProfiles();}else{DisableProfiles();}			
		
		$sr =@ldap_search($ldap->ldap_connection,"dc=organizations,".$ldap->suffix,$pattern,$attr);
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		$sock=new sockets();
		for($i=0;$i<$hash["count"];$i++){
			$dn=$hash[$i]["dn"];
			$uid=$hash[$i]["uid"][0];
			$homeDirectory=$hash[$i][strtolower("homeDirectory")][0];
			writelogs("loading: {$hash[$i]["uid"][0]}",__FUNCTION__,__FILE__,__LINE__);
			if(preg_match("#ou=users,dc=samba,dc=organizations#",$dn)){writelogs("$uid:No a standard user...SKIP",__FUNCTION__,__FILE__,__LINE__);continue;}
			if($uid==null){writelogs("uid is null, SKIP ",__FUNCTION__,__FILE__,__LINE__);continue;}
			if($uid=="nobody"){writelogs("uid is nobody, SKIP ",__FUNCTION__,__FILE__,__LINE__);continue;}
			if($uid=="root"){writelogs("uid is root, SKIP ",__FUNCTION__,__FILE__,__LINE__);continue;}
			if(substr($uid,strlen($uid)-1,1)=='$'){writelogs("$uid:This is a computer, SKIP ",__FUNCTION__,__FILE__,__LINE__);continue;}
			writelogs("-> CheckHomeFor($uid,$homeDirectory)",__FUNCTION__,__FILE__,__LINE__);
			CheckHomeFor($uid,$homeDirectory);
			}
		}
		
function CheckHomeFor($uid,$homeDirectory=null){
	$ct=new user($uid);
	if($homeDirectory==null){$homeDirectory=$ct->homeDirectory;}
	
	echo "Starting......: Home $uid checking home: $homeDirectory\n";
	
	if($GLOBALS["profile_path"]==null){
		$sock=new sockets();
		$profile_path=$sock->GET_INFO('SambaProfilePath');
		$GLOBALS["profile_path"]=$profile_path;
	}
	if($ct->ou==null){writelogs("$uid: OU=NULL, No a standard user...SKIP",__FUNCTION__,__FILE__,__LINE__);return;}
	$ou=$ct->ou;
	$uid=strtolower($uid);
	$newdir=trim(getStorageEnabled($ou,$uid));
	if($newdir<>null){
		$newdir="$newdir/$uid";
		writelogs("LVM: [$ou]:: storage=$newdir;homeDirectory=$homeDirectory",__FUNCTION__,__FILE__,__LINE__);
		if($newdir<>$homeDirectory){
			writelogs("$uid:: change $homeDirectory to $newdir",__FUNCTION__,__FILE__,__LINE__);
			$ct->homeDirectory=$newdir;
			$ct->edit_system();
			$homeDirectory=$newdir;
		}
	}
	
if($homeDirectory==null){
	$homeDirectory="/home/$uid";
	writelogs("$uid:: change $homeDirectory",__FUNCTION__,__FILE__,__LINE__);
	$ct->homeDirectory=$homeDirectory;
	$ct->edit_system();
	}
	
	if($GLOBALS["profile_path"]<>null){
		$export="$profile_path/$uid";
		writelogs("Checking export:$export",__FUNCTION__,__FILE__,__LINE__);
		@mkdir($export);
		@chmod($export,0775);
		@chown($export,$uid);
	}
	
	
	writelogs("Checking home:$homeDirectory",__FUNCTION__,__FILE__,__LINE__);
	@mkdir($homeDirectory);
	@chmod($homeDirectory,0775);
	@chown($homeDirectory,$uid);
	
	if($ct->WebDavUser==1){
		$unix=new unix();
		$find=$unix->find_program("find");
		$apacheuser=$unix->APACHE_GROUPWARE_ACCOUNT();
		$internet_folder="$homeDirectory/Internet Folder";
		@mkdir($internet_folder);
		@chmod($internet_folder,0775);
		$internet_folder=$unix->shellEscapeChars($internet_folder);
		echo "Starting......: Home $uid checking home: $internet_folder\n";
		writelogs("Checking $ct->uid:$apacheuser :$internet_folder",__FUNCTION__,__FILE__,__LINE__);
		shell_exec("/bin/chown -R $ct->uid:$apacheuser $internet_folder >/dev/null 2>&1 &");
		shell_exec("$find $internet_folder -type d -exec chmod 755 {} \; >/dev/null 2>&1 &");
	}
	
	
	
}

function getStorageEnabled($ou,$uid){
if($GLOBALS["LVM_$ou"]==null){
		$lvm=new lvm_org($ou);
		if($lvm->storage_enabled<>null){
			writelogs("Checking $ou:$lvm->storage_enabled subdir=$lvm->OuBackupStorageSubDir",__FUNCTION__,__FILE__,__LINE__);
			$GLOBALS["LVM_$ou"]="$lvm->storage_enabled";
			$GLOBALS["LVM_{$ou}_subdir"]="$lvm->OuBackupStorageSubDir";
		}
	}
	
	$storage_enabled=trim($GLOBALS["LVM_$ou"]);
	$OuBackupStorageSubDir=trim($GLOBALS["LVM_{$ou}_subdir"]);
	if($storage_enabled==null){return null;}	
	
	if($GLOBALS[$storage_enabled]<>null){
		$storage_mounted=$GLOBALS[$storage_enabled];
	 }else{	
		$sock=new sockets();
		$storage_mounted=trim(base64_decode($sock->getFrameWork("cmd.php?get-mounted-path=".base64_encode($storage_enabled))));
		if($storage_mounted<>null){$storage_mounted="$storage_mounted/$OuBackupStorageSubDir";}
		$GLOBALS[$storage_enabled]=$storage_mounted;
	 }
	 
	 return $storage_mounted;
	 
}

		
function DisableProfiles(){
	$ldap=new clladp();
	$pattern="(&(objectclass=sambaSamAccount)(sambaProfilePath=*))";
	$attr=array("sambaProfilePath","uid","dn");
	$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	for($i=0;$i<$hash["count"];$i++){
		$uid=$hash[$i][strtolower("uid")][0];
		$dn=$hash[$i][strtolower("dn")];
		$sambaProfilePath=$hash[$i][strtolower("sambaProfilePath")][0];
		$upd["sambaProfilePath"]=$sambaProfilePath;
		$ldap->Ldap_del_mod($dn,$upd);
		}
}
function EnableProfiles(){
		$ldap=new clladp();
		$sock=new sockets();
		$smb=new samba();
		$SAMBA_HOSTNAME=$smb->main_array["global"]["netbios name"];
		$SAMBA_IP=gethostbyname($SAMBA_HOSTNAME);
		if(trim($SAMBA_IP)==null){$SAMBA_IP=$SAMBA_HOSTNAME;}
		if(trim($SAMBA_IP)=="127.0.0.1"){$SAMBA_IP=$SAMBA_HOSTNAME;}
		if(trim($SAMBA_IP)=="127.0.1.1"){$SAMBA_IP=$SAMBA_HOSTNAME;}		
		
		$profile_path=$sock->GET_INFO('SambaProfilePath');
		if(trim($profile_path)==null){$profile_path="/home/export/profile";}
		$profile_base=basename($profile_path);	
		
		$attr=array("dn","uid");
		$pattern="(&(objectclass=sambaSamAccount)(uid=*))";	
		$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		for($i=0;$i<$hash["count"];$i++){
			$uid=$hash[$i]["uid"][0];
			if(strpos($uid,'$')>0){continue;}
			$dn=$hash[$i]["dn"];
			$upd["SambaProfilePath"][0]='\\\\' .$SAMBA_IP. '\\'.$profile_base.'\\' . $uid;
			$ldap->Ldap_modify($dn,$upd);
		}	
		
}


function reconfigure(){
	$unix=new unix();
	$sock=new sockets();
	$EnableSambaActiveDirectory=$sock->GET_INFO("EnableSambaActiveDirectory");
	
	if($EnableSambaActiveDirectory==1){activedirectory();}
	CheckExistentDirectories();
	$samba=new samba();
	@file_put_contents("/etc/samba/smb.conf",$samba->BuildConfig());
	
	
	if(!is_file("/var/lib/samba/usershares/data")){
		@mkdir("/var/lib/samba/usershares",null,true);
		@file_put_contents("/var/lib/samba/usershares/data","#");
	}
	
	SambaAudit();
	ParseHomeDirectories();
	
	$samba=new samba();
	$net=$unix->find_program("net");
	$master_password=$samba->GetAdminPassword("administrator");
	$SambaEnableEditPosixExtension=$sock->GET_INFO("SambaEnableEditPosixExtension");
	if($SambaEnableEditPosixExtension==1){
		$cmd="$net idmap secret {$samba->main_array["global"]["workgroup"]} $master_password >/dev/null 2>&1 &";
		shell_exec($cmd);
		$cmd="$net idmap secret alloc $master_password >/dev/null 2>&1 &";
	}
	
	if($EnableSambaActiveDirectory==1){kinit();}
	
	shell_exec("/usr/share/artica-postfix/bin/artica-install --samba-reconfigure >/dev/null 2>&1");
	}
	
	
function kinit(){
	$unix=new unix();
	$kinit=$unix->find_program("kinit");
	$echo=$unix->find_program("echo");
	$net=$unix->find_program("net");
	$hostname=$unix->find_program("hostname");
	$sock=new sockets();
	$config=unserialize(base64_decode($sock->GET_INFO("SambaAdInfos")));
	$domain=strtoupper($config["ADDOMAIN"]);
	$domain_lower=strtolower($config["ADDOMAIN"]);
	
	$ad_server=strtolower($config["ADSERVER"]);
	
	if($kinit<>null){	
		shell_exec("$echo \"{$config["PASSWORD"]}\"|$kinit {$config["ADADMIN"]}@$domain");
	}
	
	
	exec($hostname,$results);
	$servername=trim(@implode(" ",$results));
	echo "Starting......: Samba using server name has $servername.$domain_lower\n";
	shell_exec("/usr/share/artica-postfix/bin/artica-install --change-hostname $servername.$domain_lower");
	echo "Starting......: connecting to $ad_server.$domain_lower\n";
	$cmd="$net ads join -W $ad_server.$domain_lower -S $ad_server -U {$config["ADADMIN"]}%{$config["PASSWORD"]} 2>&1";
	exec("$cmd",$results);
	
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#DNS update failed#",$line)){
			echo "FAILED with command line \"$cmd\"\n";
		}
		echo "Starting......: connecting to $ad_server.$domain_lower ($line)\n";
	}
	
	
	
}

function activedirectory(){
	
	$sock=new sockets();
	$config=unserialize(base64_decode($sock->GET_INFO("SambaAdInfos")));
	$domain=strtoupper($config["ADDOMAIN"]);	
	$server=strtoupper($config["ADSERVER"]);
	$full_servername=strtolower($config["ADSERVER"].".".$config["ADDOMAIN"]);
	$full_servernameUpper=strtoupper($full_servername);
	$conf[]="[libdefaults]";
	$conf[]="default_realm = $domain";
	$conf[]="dns_lookup_realm = true";
	$conf[]="dns_lookup_kdc = true";
	$conf[]="ticket_lifetime = 24h";
	$conf[]="forwardable = yes";
	$conf[]="default_tgs_enctypes = DES-CBC-CRC DES CBC-MD5 RC4-HMAC";
	$conf[]="default_tkt_enctypes = DES-CBC-CRC DES-CBC-MD5 RC4-HMAC";
	$conf[]="preferred_enctypes = DES-CBC-CRC DES-CBC-MD5 RC4-HMAC";
	$conf[]="";
	$conf[]="[realms]";
	$conf[]="$domain = {";
	$conf[]="  kdc = $full_servername";
	$conf[]="  admin_server = $full_servername";
	$conf[]="  default_domain = ".strtolower($domain);
	$conf[]="}";
	$conf[]="";
	$conf[]="[domain_realm]";
	$conf[]=strtolower(".{$config["ADDOMAIN"]}")." = $domain";
	$conf[]=strtolower(".{$config["ADDOMAIN"]}")."= $domain";
	$conf[]="";
	$conf[]="[kdc]";
	$conf[]="profile = /etc/kdc.conf";
	$conf[]="";
	@file_put_contents("/etc/krb5.conf",@implode("\n",$conf));
	unset($conf);
	$conf[]="[kdcdefaults]";
	$conf[]="kdc_ports = 750,88";
	$conf[]="acl_file = /var/kerberos/krb5kdc/kadm5.acl";
	$conf[]="dict_file = /usr/share/dict/words";
	$conf[]="admin_keytab = /var/kerberos/krb5kdc/kadm5.keytab";
	$conf[]="v4_mode = noreauth";
	$conf[]="[libdefaults]";
	$conf[]="        default_realm = $domain";
	$conf[]="[realms]";
	$conf[]="$domain = {";
	$conf[]="	master_key_type = des-cbc-crc";
	$conf[]="   supported_enctypes = des3-hmac-sha1:normal arcfour-hmac:normal des-hmac-sha1:normal des-cbc-md5:normal des-cbc-crc:normal des-cbc-crc:v4 des-cbc-crc:afs3";
	$conf[]="}";	
	$conf[]="";
	@file_put_contents("/etc/kdc.conf",@implode("\n",$conf));
	
	$config="*/{$config["ADADMIN"]}@$domain\n";
	@file_put_contents("/etc/kadm.acl",$config);
	
	
	
	
	
}


function CheckExistentDirectories(){
	$change=false;
	$ini=new Bs_IniHandler("/etc/artica-postfix/settings/Daemons/SambaSMBConf");
	while (list ($index, $array) = each ($ini->_params) ){
		if($index=="print$"){continue;}
		if($index=="printers"){continue;}
		if($index=="homes"){continue;}
		if($index=="global"){continue;}
		if($array["path"]==null){continue;}
		if(is_link($array["path"])){continue;}
		if(is_dir($array["path"])){continue;}
		unset($ini->_params[$index]);
		$change=true;
		continue;
	}
	
	$ini->saveFile("/etc/artica-postfix/settings/Daemons/SambaSMBConf");
	
}


function SambaAudit(){
	$sock=new sockets();
	$EnableSambaXapian=$sock->GET_INFO("EnableSambaXapian");
	$EnableScannedOnly=$sock->GET_INFO('EnableScannedOnly');
	if($EnableSambaXapian==null){$EnableSambaXapian=1;}
	if($EnableScannedOnly==null){$EnableScannedOnly=1;}
	$users=new usersMenus();
	if(!$users->XAPIAN_PHP_INSTALLED){$EnableSambaXapia=0;}
	if(!$users->SCANNED_ONLY_INSTALLED){$EnableScannedOnly=0;}
	
	
	$ini=new Bs_IniHandler("/etc/samba/smb.conf");
	$write=true;
	
	
	while (list ($num, $ligne) = each ($ini->_params) ){
		
		if($num<>"homes"){
			if($ligne["path"]==null){continue;}
		}
		if($num=="profiles"){continue;}
		if($num=="printers"){continue;}
		if($num=="print$"){continue;}
		if($num=="netlogon"){continue;}
		$vfs_objects=$ligne["vfs object"];
		
		
		if($EnableSambaXapian==1){
			if(!IsVfsExists($vfs_objects,"full_audit")){
				$ini->_params[$num]["vfs object"]=$ini->_params[$num]["vfs object"]." full_audit";
				$ini->_params[$num]["vfs object"]=VFSClean($ini->_params[$num]["vfs object"]);
				$ini->_params[$num]["full_audit:prefix"]="%u|%I|%m|%S|%P";
				$ini->_params[$num]["full_audit:success"]="rename unlink pwrite write";
				$ini->_params[$num]["full_audit:failure"]="none";
				$ini->_params[$num]["full_audit:facility"]="LOCAL7";
				$ini->_params[$num]["full_audit:priority"]="NOTICE";				
				$write=true;
			}
		}else{
			if(IsVfsExists($vfs_objects,"full_audit")){
				$ini->_params[$num]["vfs object"]=str_replace("full_audit","",$ini->_params[$num]["vfs object"]);
				$ini->_params[$num]["vfs object"]=VFSClean($ini->_params[$num]["vfs object"]);
				unset($ini->_params[$num]["full_audit:prefix"]);
				unset($ini->_params[$num]["full_audit:success"]);
				unset($ini->_params[$num]["full_audit:failure"]);
				unset($ini->_params[$num]["full_audit:facility"]);
				unset($ini->_params[$num]["full_audit:priority"]);
				$write=true;
			}
		}
		
		if($EnableScannedOnly==0){
			if(IsVfsExists($vfs_objects,"scannedonly")){
				$ini->_params[$num]["vfs object"]=str_replace("scannedonly","",$ini->_params[$num]["vfs object"]);
				$ini->_params[$num]["vfs object"]=VFSClean($ini->_params[$num]["vfs object"]);
				$write=true;
			}
		}		
}
	
if($write){$ini->saveFile("/etc/samba/smb.conf");	}
	
	
	
	
}

function IsVfsExists($line,$module){
	$tbl=explode(" ",$line);
	if(!is_array($tbl)){return false;}
	while (list ($num, $ligne) = each ($tbl) ){
		if(strtolower(trim($ligne))==$module){return true;}
	}
	return false;
}
function VFSClean($line){
	$tbl=explode(" ",$line);
	if(!is_array($tbl)){return false;}
	while (list ($num, $ligne) = each ($tbl) ){
		if($ligne==null){continue;}
		$r[]=$ligne;
	}

	if(!is_array($r)){return null;}
	return implode(" ",$r);
	
}


function LogonScripts(){
	$sql="SELECT * FROM logon_scripts";
	writelogs("checking /home/netlogon security settings",__FUNCTION__,__FILE__,__LINE__);
	@mkdir("/home/netlogon");
	@chmod("/home/netlogon",0755);
	LogonScripts_remove();
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		writelogs("mysql failed \"SELECT * FROM logon_scripts\" in artica_backup database",__FUNCTION__,__FILE__,__LINE__);
		writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	$count=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$count=$count+1;
		$gpid=$ligne["gpid"];
		$script=$ligne["script_code"];
		if($gpid==null){writelogs("gpid is null, skip",__FUNCTION__,__FILE__,__LINE__);continue;}
		if($script==null){writelogs("script contains no data, skip",__FUNCTION__,__FILE__,__LINE__);continue;}
		$script=base64_decode($script);
		$script=str_replace("\n","\r\n",$script);
		$script=$script."\r\n";
		writelogs("Saving /home/netlogon/artica-$gpid.bat",__FUNCTION__,__FILE__,__LINE__);
		@file_put_contents("/home/netlogon/artica-$gpid.bat",$script);
		LogonScripts_updateusers($gpid);		
	}
	writelogs("$count scripts updated.",__FUNCTION__,__FILE__,__LINE__);
	
	
}

function LogonScripts_updateusers($gpid){
	$gp=new groups($gpid);
	if(!is_array($gp->members_array)){
		writelogs("Group $gpid did not store users.",__FUNCTION__,__FILE__,__LINE__);
		return null;
	}
	$script="artica-$gpid.bat";
	while (list ($uid, $ligne) = each ($gp->members_array) ){
		$u=new user($uid);
		
		if($u->dn<>null){
			if($u->NotASambaUser){writelogs("$uid is not a Samba user",__FUNCTION__,__FILE__,__LINE__);continue;}
			writelogs("edit $uid for $script script name",__FUNCTION__,__FILE__,__LINE__);
			$u->Samba_edit_LogonScript($script);
			
		}
	}
	
}




function LogonScripts_remove(){
	$dir_handle = @opendir("/home/netlogon");
	if(!$dir_handle){
		return array();
	}
	$count=0;	
	while ($file = readdir($dir_handle)) {
		  if($file=='.'){continue;}
		  if($file=='..'){continue;}
		  if(!is_file("/home/netlogon/$file")){continue;}
		  if(preg_match("#artica-[0-9]+#",$file)){
		  	@unlink("/home/netlogon/$file");
		  }
		  continue;
		}
}


function CountDeUsers(){
	$ldap=new clladp();
	$arr=$ldap->hash_users_ou(null);
	@file_put_contents("/etc/artica-postfix/UsersNumber",count($arr));
}

function fix_lmhosts(){
	$smb=new samba();
	$smb->main_array["global"]["name resolve order"]=null;
	$smb->SaveToLdap();
	
}

function fix_hide_unwriteable_files(){
	
	$smb=new samba();
	while (list ($key, $array) = each ($smb->main_array)){
		while (list ($valuename, $value) = each ($array) ){
			
			if($valuename=="hide_unwriteable_files"){
				echo "Found $key,$valuename\n";
				$mod=true;
				unset($smb->main_array[$key][$valuename]);
				$smb->main_array[$key]["hide unwriteable files"]=$value;
			}
		}
	}
	
	if($mod==true){$smb->SaveToLdap();}
	
	
}


// #smbd_audit:\s+(.+?)\|(.+?)\|(.+?)\|(.+?)\|(.+?)\|(.+?)\|(.+?)\|(.+?)$#
/*
 # [0]=>smbd_audit: marie|192.168.1.211|ak8|marie|/home/marie|pwrite|ok|New folder3/Nouveau Document texte.txt

# [1]=>marie

# [2]=>192.168.1.211

# [3]=>ak8

# [4]=>marie

# [5]=>/home/marie

# [6]=>pwrite

# [7]=>ok

# [8]=>New folder3/Nouveau Document texte.txt
{$re[5]}/{$re[8]}
*/
?>