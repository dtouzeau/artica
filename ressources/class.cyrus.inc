<?php
if(!is_file(dirname($_SERVER['SCRIPT_FILENAME'])."/ressources/class.templates.inc")){include_once(dirname(__FILE__) . '/class.templates.inc');}else{include_once(dirname($_SERVER['SCRIPT_FILENAME'])."/ressources/class.templates.inc");}
if(!is_file(dirname($_SERVER['SCRIPT_FILENAME'])."/ressources/class.ldap.inc")){include_once(dirname(__FILE__) . '/class.ldap.inc');}else{include_once(dirname($_SERVER['SCRIPT_FILENAME'])."/ressources/class.ldap.inc");}
include_once(dirname(__FILE__) . "/class.ini.inc");
include_once(dirname(__FILE__) . "/class.cyrus-admin.inc");




class cyrus{
	var $cyrus_enabled=True;
	var $array_mailboxes;
	var $cyrus_adm;
	var $PasswordOfCyrus;
	var $cyrus_server="127.0.0.1";
	var $cyrus_port=143;
	var $cyrus_dn;
	var $connection;
	var $connection_off=false;
	var $cyrus_last_error;
	var $cyrus_infos_cleaned;
	var $USER_STORAGE_USAGE;
	var $USER_STORAGE_LIMIT;
	var $USER_STORAGE_MESSAGE_USAGE;
	var $USER_STORAGE_MESSAGE_LIMIT;
	var $cyrus_infos;
	var $info_err="SECURITY PROBLEM: insecure server advertised AUTH=PLAIN"	;
	var $cyr_params;
	var $cyrus_error;
	
	
	function cyrus(){
		$this->GetCyrusPassword();
		if(trim($this->PasswordOfCyrus)==null){
			writelogs("WARNING PASSWORD IS NULL",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		$this->cyr_params= array(
			'hostspec'=>'localhost',
			'protocol'=>'imap',
			'username'=>'cyrus',
			'password'=>$this->PasswordOfCyrus
    		);
	
		}
	
	function GetCyrusPassword(){
		$ldap=new clladp();
		$this->PasswordOfCyrus=$ldap->CyrusPassword();
		if(trim($this->PasswordOfCyrus)==null){$this->PasswordOfCyrus=$ldap->ldap_password;}
		return $this->PasswordOfCyrus;
	}
	
	
	public function ListMailboxes($cn){
		$uid=$cn;
		$ldap=new clladp();
		
		if(preg_match("#(.+?)@(.+)#",$cn,$re)){
			writelogs("Mailbox in multidomain $cn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$uid=$re[1];
			
			$ldap->CyrusAdminOtherCreate("cyrus@{$re[2]}",$this->PasswordOfCyrus);	
			$this->PasswordOfCyrus=$ldap->CyrusPassword("cyrus@{$re[2]}");
			$this->cyr_params= array(
				'hostspec'=>'localhost',
				'protocol'=>'imap',
				'username'=>"cyrus@{$re[2]}",
				'password'=>$this->PasswordOfCyrus
    			);	
			}
			
			
		$cyradm=new CyrusAdmin($this->cyr_params);
		if(!$cyradm->_connect()){
			$this->cyrus_infos="{unable_to_connect_imap}";
			return false;	
		}
    
        $mbx=$cyradm->listmbox("user/$uid/*");
        if(is_array($mbx)){
        	while (list ($num, $box) = each ($mbx) ){
        		$folder=str_replace("user/$uid/","",$box);
        		$array[$folder]=$folder;
        	}
        }
        $cyradm->_disconnect();
        return $array;
	}
	
	
	public function IfMailBoxExists($cn){
		$uid=$cn;
		$ldap=new clladp();
		
		if(preg_match("#(.+?)@(.+)#",$cn,$re)){
			writelogs("Mailbox in multidomain $cn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$uid=$re[1];
			
			$ldap->CyrusAdminOtherCreate("cyrus@{$re[2]}",$this->PasswordOfCyrus);	
			$this->PasswordOfCyrus=$ldap->CyrusPassword("cyrus@{$re[2]}");
			$this->cyr_params= array(
				'hostspec'=>'localhost',
				'protocol'=>'imap',
				'username'=>"cyrus@{$re[2]}",
				'password'=>$this->PasswordOfCyrus
    			);	
			}
			
			
		$cyradm=new CyrusAdmin($this->cyr_params);
		if(!$cyradm->_connect()){
			$this->cyrus_infos="{unable_to_connect_imap}";
			return false;	
		}
    
        $mbx=$cyradm->listmbox("user/$uid");
        if(preg_match("#$uid#",$mbx[0])){
        	$cyradm->_disconnect();
        	return true;
        }
        writelogs("{$mbx[0]},{$mbx[1]},{$mbx[2]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
        $cyradm->_disconnect();
        $this->cyrus_infos="{mailbox_does_not_exists}";
  		return false;
		}
	

	function LoadLdapAllMailboxes(){
		$filter="(&(objectclass=userAccount)(MailboxActive=TRUE))";
		$attr=array("dn","cn","uid");
		$ldap=new clladp();
		$hash=$ldap->Ldap_search($ldap->suffix,$filter,$attr);
		for($i=0;$i<$hash["count"];$i++){
			$array[$hash[$i]["uid"][0]]=array("cn"=>$hash[$i]["cn"][0],"dn"=>$hash[$i]["dn"]);
			
		}
		return $array;
	}

	
	function mailbox_uid($uid){return false;}
	
	private function CreateCyrParamsAsuser($uid){
		$ldap=new clladp();
		$ct=new user($uid);
		$this->cyr_params= array(
				'hostspec'=>'localhost',
				'protocol'=>'imap',
				'username'=>$ct->uid,
				'password'=>$ct->password
    			);	
	}
		
	
	private function CreateCyrParams($cn){
		$ldap=new clladp();
		if(preg_match("#(.+?)@(.+)#",$cn,$re)){
			writelogs("Mailbox in multidomain $cn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$uid=$re[1];
			
			$ldap->CyrusAdminOtherCreate("cyrus@{$re[2]}",$this->PasswordOfCyrus);	
			$this->PasswordOfCyrus=$ldap->CyrusPassword("cyrus@{$re[2]}");
			$this->cyr_params= array(
				'hostspec'=>'localhost',
				'protocol'=>'imap',
				'username'=>"cyrus@{$re[2]}",
				'password'=>$this->PasswordOfCyrus
    			);	
			}
		else{
			$this->PasswordOfCyrus=$ldap->CyrusPassword("cyrus");
			$this->cyr_params= array(
				'hostspec'=>'localhost',
				'protocol'=>'imap',
				'username'=>"cyrus",
				'password'=>$this->PasswordOfCyrus
    			);				
		}
	}
	
	
	public function CreateMailbox($cn,$notContinue=0){
		$uid=$cn;
		$this->CreateCyrParams($cn);
		
		$user=new user($uid);
		$ini=new Bs_IniHandler();
		$ini->loadString($user->MailboxSecurityParameters);
		while (list ($key, $val) = each ($ini->_params["mailbox"]) ){
			if($val==1){$k[]=$key;}
		}
		$acls_user=implode("",$k);
		writelogs("$uid: Create acls $acls",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
		
		if(preg_match("#(.+?)@#",$uid,$re)){$REAL_UID=$re[1];}else{$REAL_UID=$uid;}
		
		$cyradm=new CyrusAdmin($this->cyr_params);
		if(!$cyradm->_connect()){
			$this->cyrus_infos="{unable_to_connect_imap}";
			return false;	
		}
		
		if(!$cyradm->createmailbox("user/$REAL_UID")){
			$this->cyrus_infos="{unable_to_create_mailbox}";
			return false;
		}
		
		if($user->MailBoxMaxSize>0){
			$this->cyrus_infos=$this->cyrus_infos."$REAL_UID: QUOTA => $user->MailBoxMaxSize\n";
			$cyradm->setquota("user/$REAL_UID",($user->MailBoxMaxSize*1000));
		}
		
		
		
		$acls="lrswipkxtecda";
		if(!$cyradm->setacl("user/$REAL_UID",$REAL_UID,$acls)){
			$this->cyrus_infos=$this->cyrus_infos."$REAL_UID: {unable_to_create_acls} user/$REAL_UID (level 1)\n";
			return false;
		}		
		
		
		
		$cyradm->_disconnect();
		if(!$this->CreateSubBox($uid,$acls_user)){
			return false;
		}
		if(!$cyradm->setacl("user/$REAL_UID",$REAL_UID,$acls_user)){
			$this->cyrus_infos=$this->cyrus_infos."$REAL_UID: {unable_to_create_acls} user/$REAL_UID (level 2)\n";
			return false;
		}	
		
		
	}
	
	public function CreateSubDir($uid,$foldername){
		$this->CreateCyrParamsAsuser($uid);
		$box=$foldername;
		$cyradm=new CyrusAdmin($this->cyr_params);
		$cyradm=new CyrusAdmin($this->cyr_params);
		if(!$cyradm->_connect()){
			$this->cyrus_infos="{unable_to_connect_imap} has user";
			return false;	
		}
		$acls="lrswipcda";
		
		if(preg_match("#(.+?)@#",$uid,$re)){$REAL_UID=$re[1];}else{$REAL_UID=$uid;}
		if(!$cyradm->createmailbox("user/$REAL_UID/$box")){
				$this->cyrus_infos="{unable_to_create_mailbox} $REAL_UID/$box";
				return false;
			}else{
				$this->cyrus_infos=$this->cyrus_infos."$REAL_UID: Mbx: \"$REAL_UID/$box\" {success}\n";
			}
			
		if(!$cyradm->setacl("user/$REAL_UID/$box",$uid,$acls)){
				$this->cyrus_infos=$this->cyrus_infos."$REAL_UID: {unable_to_create_acls} user/$REAL_UID/$box\n";
				return false;		
		 }else{
			$this->cyrus_infos=$this->cyrus_infos."$REAL_UID: ACL:$acls \"$REAL_UID/$box\" {success}\n";
		}
			
		$cyradm->subscribe("user/$REAL_UID/$box");			
			
			
		
	}
	
		
	private function CreateSubBox($uid,$acls){
		$subboxes[]="Sent";
		$subboxes[]="Trash";
		$subboxes[]="Drafts";
		$subboxes[]="Junk";
		$this->CreateCyrParamsAsuser($uid);
		$cyradm=new CyrusAdmin($this->cyr_params);
		if(!$cyradm->_connect()){
			$this->cyrus_infos="{unable_to_connect_imap} has user";
			return false;	
		}
		
	if(preg_match("#(.+?)@#",$uid,$re)){$REAL_UID=$re[1];}else{$REAL_UID=$uid;}
		
		while (list ($num, $box) = each ($subboxes) ){
			if($box==null){continue;}
			if(!$cyradm->createmailbox("user/$REAL_UID/$box")){
				$this->cyrus_infos="{unable_to_create_mailbox} $REAL_UID/$box";
				return false;
			}else{
				$this->cyrus_infos=$this->cyrus_infos."$REAL_UID: Mbx: \"$REAL_UID/$box\" {success}\n";
			}
			
			if(!$cyradm->setacl("user/$REAL_UID/$box",$uid,$acls)){
				$this->cyrus_infos=$this->cyrus_infos."$REAL_UID: {unable_to_create_acls} user/$REAL_UID/$box\n";
				return false;		
			}else{
				$this->cyrus_infos=$this->cyrus_infos."$REAL_UID: ACL:$acls \"$REAL_UID/$box\" {success}\n";
			}
			
			$cyradm->subscribe("user/$REAL_UID/$box");
			
		}
		
		$cyradm->_disconnect();
		return true;
	}
	
	public function CreateACLS($uid,$notcontinue=0){
		if($uid==null){return true;}
		$this->CreateCyrParams($uid);
		$cyradm=new CyrusAdmin($this->cyr_params);
		if(!$cyradm->_connect()){
			$this->cyrus_infos="$uid:: CreateACLS: {unable_to_connect_imap}";
			return false;	
		}
		$user=new user($uid);
		$ini=new Bs_IniHandler();
		$ini->loadString($user->MailboxSecurityParameters);
		while (list ($key, $val) = each ($ini->_params["mailbox"]) ){
			if($val==1){$k[]=$key;}
		}
		$acls=implode("",$k);	

		
		if(preg_match("#(.+?)@#",$uid,$re)){$REAL_UID=$re[1];}else{$REAL_UID=$uid;}

		$this->cyrus_infos=$this->cyrus_infos."$REAL_UID: ACL: \"$acls\"\n";
		if(!$cyradm->setacl("user/$REAL_UID",$REAL_UID,$acls)){
			$this->cyrus_infos=$this->cyrus_infos."$REAL_UID: {unable_to_create_acls} user/$REAL_UID\n";
			return false;
		}
		
	

		if($notcontinue==0){
			
			
		}
		if($user->MailBoxMaxSize>0){
			$this->cyrus_infos=$this->cyrus_infos."$REAL_UID: QUOTA => $user->MailBoxMaxSize\n";
			$cyradm->setquota("user/$REAL_UID",($user->MailBoxMaxSize*1000));
		}

		$cyradm->_disconnect();
	}
	

	
	
	function MailBoxExists($uid){
		
		return $this->IfMailBoxExists($uid);
		
		$sock=new sockets();
		$datas=$sock->getfile('MbxStat:'.$uid);
		writelogs("$datas => $uid",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(preg_match('#TRUE.*#',$datas)){
			return true;
		}
		return false;
	}
	
	
	function MailboxInfosSize($uid){
		$array=$this->GetQuota($uid);
		return $array["STORAGE_USAGE"];
		
		$sock=new sockets();
		$datas=$sock->getfile('MailboxInfos:'.$uid);
		if(preg_match('#([0-9]+)#',$datas,$re)){
			return $re[1];
		}
		return 0;
		
		
	}
	

	
	function SetQuotaDN($cn,$quota=3000){
		return $this->CreateMailbox($cn);
	}
	
	
	function GetQuota($cn){
		if($GLOBALS["CYRUSIMAP"][$cn]["QUOTA"]["STORAGE_USAGE"]<>null){
			return $GLOBALS["CYRUSIMAP"][$cn]["QUOTA"];
		}
		
		
		$uid=$cn;
		$ldap=new clladp();
		
		if(preg_match("#(.+?)@(.+)#",$cn,$re)){
			writelogs("Mailbox in multidomain $cn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$uid=$re[1];
			
			$ldap->CyrusAdminOtherCreate("cyrus@{$re[2]}",$this->PasswordOfCyrus);	
			$this->PasswordOfCyrus=$ldap->CyrusPassword("cyrus@{$re[2]}");
			$this->cyr_params= array(
				'hostspec'=>'localhost',
				'protocol'=>'imap',
				'username'=>"cyrus@{$re[2]}",
				'password'=>$this->PasswordOfCyrus
    			);	
			}
			
			
		$cyradm=new CyrusAdmin($this->cyr_params);
		if(!$cyradm->_connect()){
			$this->cyrus_infos="{unable_to_connect_imap}";
			return false;	
		}
		
		$array=$cyradm->getquota("user/$uid");
		$GLOBALS["CYRUSIMAP"][$cn]["QUOTA"]["STORAGE_USAGE"]=$array[0];
		$GLOBALS["CYRUSIMAP"][$cn]["QUOTA"]["STORAGE_LIMIT"]=$array[1];
		$this->USER_STORAGE_USAGE=$array[0];
		$this->USER_STORAGE_LIMIT=$array[1];
		$res["STORAGE_USAGE"]=$array[0];
		$res["STORAGE_LIMIT"]=$array[1];
		$cyradm->_disconnect();
		return $res;
	}
	
	
	function get_quota_array($cn){
			return $this->GetQuota($cn);
			$this->GetQuotasRoot($cn);
			$res["STORAGE_USAGE"]=$this->USER_STORAGE_USAGE;
			$res["STORAGE_LIMIT"]=$this->USER_STORAGE_LIMIT;
			return $res;
	}
	
	function SetAclDN($cn){
		return true;
	}
	
	
	function GetQuotasRoot($user){
			$sock=new sockets();
			$datas=$sock->getfile('MailboxQuota:'.$user);
			$tbl=explode("\n",$datas);
			while (list ($num, $ligne) = each ($tbl) ){
				if(preg_match('#USED.+?([0-9]+)#',$ligne,$re)){
					$this->USER_STORAGE_USAGE=$re[1];
				}
				if(preg_match('#MAX.+?([0-9]+)#',$ligne,$re)){
					$this->USER_STORAGE_LIMIT=$re[1];
				}					
			
			}
		
	}
	
}


class cyrus_conf{
	
	var $CyrusConf;
	var $impadconf;
	var $globalconf;
	var $main_array;
	var $impad_array=array();
	var $dn;
	
	function cyrus_conf(){
		$this->LoadSettings();
		$this->parse_imapdconf();
	}
	
	
	
	
	
function LoadSettings(){
		
			$sock=new sockets();
			$this->CyrusConf=$sock->GET_INFO("CyrusConf");
			$this->impadconf=$sock->GET_INFO("impadconf");
			$this->globalconf=$sock->GET_INFO("ArticaImapIniConf");
			
		
		if($this->impadconf==null){$this->impadconf=$sock->getfile('cyrus_imapconf');}
		if($this->globalconf==null){$this->globalconf=$this->BuildArticaConf();}
		
		$ini=new Bs_IniHandler();
		$ini->loadString($this->globalconf);
		$this->main_array=$ini->_params;
		$this->BuildDefault();
		
	}	
	
	
function SaveToLdap(){
		$sock=new sockets();
		$tpl=new templates();
		$sock->SaveConfigFile($this->BuildArticaConf(),"ArticaImapIniConf");
		$sock->SaveConfigFile($this->BuildConfig(),"CyrusConf");
		$sock->SaveConfigFile($this->Build_imapdconf(),"impadconf");
		$this->ApplyToServer();
		}


function parse_imapdconf(){
	
	$tbl=explode("\n",$this->impadconf);
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match('#^([a-zA-Z_]+):(.+)#',$ligne,$re)){
			$this->impad_array[$re[1]]=trim($re[2]);
		}
		
	}
	
	if($this->impad_array["maxmessagesize"]==null){$this->impad_array["maxmessagesize"]=0;}
	if($this->impad_array["createonpost"]==null){$this->impad_array["createonpost"]=1;}
	if($this->impad_array["autocreateinboxfolders"]==null){$this->impad_array["autocreateinboxfolders"]="sent|drafts|spam|templates";}
	if($this->impad_array["popminpoll"]==null){$this->impad_array["popminpoll"]="1";}
	if($this->impad_array["quotawarn"]==null){$this->impad_array["quotawarn"]="90";}
	
}

function Build_imapdconf(){
	 $ldap=new clladp();
$this->impad_array["sasl_auto_transition"]="no";
$this->impad_array["sasl_saslauthd_path"]="/var/run/saslauthd/mux";
$this->impad_array["altnamespace"]="no";
$this->impad_array["unixhierarchysep"]="yes";
$this->impad_array["lmtp_downcase_rcpt"]="yes";
$this->impad_array["umask"]="077";
$this->impad_array["sieveusehomedir"]="false";
$this->impad_array["sieve_maxscriptsize"]="1024";
$this->impad_array["hashimapspool"]="true";
$this->impad_array["allowplaintext"]="yes";
$this->impad_array["tls_session_timeout"]="1440";
$this->impad_array["tls_cipher_list"]="TLSv1+HIGH:!aNULL:@STRENGTH";
$this->impad_array["idlemethod"]="poll";
$this->impad_array["syslog_prefix"]="cyrus";
$this->impad_array["servername"]="$users->hostname";
$this->impad_array["virtdomains"]="no";
$this->impad_array["admins"]="cyrus";
$this->impad_array["username_tolower"]="1";
$this->impad_array["ldap_uri"]="ldap://$ldap->ldap_host:$ldap->ldap_port";
$this->impad_array["sasl_mech_list"]="PLAIN LOGIN";
$this->impad_array["sasl_pwcheck_method"]="saslauthd";
$this->impad_array["configdirectory"]="/var/lib/cyrus";
$this->impad_array["defaultpartition"]="default";
$this->impad_array["partition-default"]="/var/spool/cyrus/mail";
$this->impad_array["partition-news"]="/var/spool/cyrus/news";
$this->impad_array["srvtab"]="/var/lib/cyrus/srvtab";
$this->impad_array["newsspool"]="/var/spool/cyrus/news";	
	
	
	while (list ($num, $ligne) = each ($this->impad_array) ){
		if(trim($ligne)==null){continue;}
		$conf=$conf ."$num: $ligne\n";
		
	}

	return $conf;
	
}

function ApplyToServer(){
	$sock=new sockets();
	$sock->getfile('cyrusconf');
	
}
	
	
	
function BuildConfig(){	
	
					
				$conf=$conf ."# # Debian defaults for Cyrus IMAP server/cluster implementation\n";
				$conf=$conf ."# see cyrus.conf(5) for more information\n";
				$conf=$conf ."#\n";
				$conf=$conf ."# All the tcp services are tcpd-wrapped. see hosts_access(5)\n";
				$conf=$conf ."# $Id: cyrus.conf 567 2006-08-14 18:19:32Z sven $\n";
				$conf=$conf ."\n";
				$conf=$conf ."START {\n";
				$conf=$conf ."	# do not delete this entry!\n";
				$conf=$conf ."	recover		cmd=\"/usr/sbin/ctl_cyrusdb -r\"\n";
				$conf=$conf ."  \n";
				$conf=$conf ."	# this is only necessary if idlemethod is set to \"idled\" in imapd.conf\n";
				$conf=$conf ."	#idled		cmd=\"idled\"\n";
				$conf=$conf ."\n";
				$conf=$conf ."	# this is useful on backend nodes of a Murder cluster\n";
				$conf=$conf ."	# it causes the backend to syncronize its mailbox list with\n";
				$conf=$conf ."	# the mupdate master upon startup\n";
				$conf=$conf ."	#mupdatepush   cmd=\"/usr/sbin/ctl_mboxlist -m\"\n";
				$conf=$conf ."\n";
				$conf=$conf ."	# this is recommended if using duplicate delivery suppression\n";
				$conf=$conf ."	delprune	cmd=\"/usr/sbin/cyr_expire -E 3\"\n";
				$conf=$conf ."	# this is recommended if caching TLS sessions\n";
				$conf=$conf ."	tlsprune	cmd=\"/usr/sbin/tls_prune\"\n";
				$conf=$conf ."}\n";
				$conf=$conf ."\n";
				$conf=$conf ."# UNIX sockets start with a slash and are absolute paths\n";
				$conf=$conf ."# you can use a maxchild=# to limit the maximum number of forks of a service\n";
				$conf=$conf ."# you can use babysit=true and maxforkrate=# to keep tight tabs on the service\n";
				$conf=$conf ."# most services also accept -U (limit number of reuses) and -T (timeout)\n";
				$conf=$conf ."SERVICES {\n";
				$conf=$conf ."	# --- Normal cyrus spool, or Murder backends ---\n";
				$conf=$conf ."	# add or remove based on preferences\n";
				
				if($this->main_array["CYRUS"]["service_imap_enabed"]==1){
					$conf=$conf ."	imap		cmd=\"imapd -U 30\" listen=\"{$this->main_array["CYRUS"]["service_imap_listen"]}\" prefork=0 maxchild=100\n";
				}
				
				if($this->main_array["CYRUS"]["service_imapssl_enabed"]==1){
					$conf=$conf ."	imaps		cmd=\"imapd -s -U 30\" listen=\"{$this->main_array["CYRUS"]["service_imapssl_listen"]}\" prefork=0 maxchild=100\n";
				}
				
				if($this->main_array["CYRUS"]["service_pop3_enabed"]==1){
					$conf=$conf ."	pop3		cmd=\"pop3d -U 30\" listen=\"{$this->main_array["CYRUS"]["service_pop3_listen"]}\" prefork=0 maxchild=50\n";
				}
				
				if($this->main_array["CYRUS"]["service_pop3ssl_enabed"]==1){
					$conf=$conf ."	pop3s		cmd=\"pop3d -s -U 30\" listen=\"{$this->main_array["CYRUS"]["service_pop3ssl_listen"]}\" prefork=0 maxchild=50\n";
				}
				
				
				if($this->main_array["CYRUS"]["service_nntpd_enabed"]==1){
					$conf=$conf ."	nntp		cmd=\"nntpd -U 30\" listen=\"{$this->main_array["CYRUS"]["service_nntpd_listen"]}\" prefork=0 maxchild=100\n";
				}
				
				if($this->main_array["CYRUS"]["service_nntpds_enabed"]==1){
					$conf=$conf ."	nntps		cmd=\"nntpd -s -U 30\" listen=\"{$this->main_array["CYRUS"]["service_nntpds_listen"]}\" prefork=0 maxchild=100\n";
				}
				
				$conf=$conf ."\n";
				$conf=$conf ."	# At least one form of LMTP is required for delivery\n";
				$conf=$conf ."	# (you must keep the Unix socket name in sync with imap.conf)\n";
				$conf=$conf ."	#lmtp		cmd=\"lmtpd\" listen=\"localhost:lmtp\" prefork=0 maxchild=20\n";
				$conf=$conf ."	lmtpunix	cmd=\"lmtpd\" listen=\"/var/run/cyrus/socket/lmtp\" prefork=0 maxchild=20\n";
				$conf=$conf ."	# ----------------------------------------------\n";
				$conf=$conf ."\n";
				$conf=$conf ."	# useful if you need to give users remote access to sieve\n";
				$conf=$conf ."	# by default, we limit this to localhost in Debian\n";
				$conf=$conf ."  	sieve		cmd=\"timsieved\" listen=\"localhost:sieve\" prefork=0 maxchild=100\n";
				$conf=$conf ."\n";
				$conf=$conf ."	# this one is needed for the notification services\n";
				$conf=$conf ."	notify		cmd=\"notifyd\" listen=\"/var/run/cyrus/socket/notify\" proto=\"udp\" prefork=1\n";
				$conf=$conf ."\n";
				$conf=$conf ."	# --- Murder frontends -------------------------\n";
				$conf=$conf ."	# enable these and disable the matching services above, \n";
				$conf=$conf ."	# except for sieve (which deals automatically with Murder)\n";
				$conf=$conf ."\n";
				$conf=$conf ."	# mupdate database service - must prefork at least 1\n";
				$conf=$conf ."	# (mupdate slaves)\n";
				$conf=$conf ."	#mupdate       cmd=\"mupdate\" listen=3905 prefork=1\n";
				$conf=$conf ."	# (mupdate master, only one in the entire cluster)\n";
				$conf=$conf ."	#mupdate       cmd=\"mupdate -m\" listen=3905 prefork=1\n";
				$conf=$conf ."\n";
				$conf=$conf ."	# proxies that will connect to the backends\n";
				$conf=$conf ."	#imap		cmd=\"proxyd\" listen=\"imap\" prefork=0 maxchild=100\n";
				$conf=$conf ."	#imaps		cmd=\"proxyd -s\" listen=\"imaps\" prefork=0 maxchild=100\n";
				$conf=$conf ."	#pop3		cmd=\"pop3proxyd\" listen=\"pop3\" prefork=0 maxchild=50\n";
				$conf=$conf ."	#pop3s		cmd=\"pop3proxyd -s\" listen=\"pop3s\" prefork=0 maxchild=50\n";
				$conf=$conf ."	#lmtp		cmd=\"lmtpproxyd\" listen=\"lmtp\" prefork=1 maxchild=20\n";
				$conf=$conf ."	# ----------------------------------------------\n";
				$conf=$conf ."}\n";
				$conf=$conf ."\n";
				$conf=$conf ."EVENTS {\n";
				$conf=$conf ."	# this is required\n";
				$conf=$conf ."	checkpoint	cmd=\"/usr/sbin/ctl_cyrusdb -c\" period=30\n";
				$conf=$conf ."\n";
				$conf=$conf ."	# this is only necessary if using duplicate delivery suppression\n";
				$conf=$conf ."	delprune	cmd=\"/usr/sbin/cyr_expire -E 3\" at=0401\n";
				$conf=$conf ."\n";
				$conf=$conf ."	# this is only necessary if caching TLS sessions\n";
				$conf=$conf ."	tlsprune	cmd=\"/usr/sbin/tls_prune\" at=0401\n";
				$conf=$conf ."	\n";
				$conf=$conf ."	# indexing of mailboxs for server side fulltext searches\n";
				$conf=$conf ."\n";
				$conf=$conf ."	# reindex changed mailboxes (fulltext) approximately every other hour\n";
				$conf=$conf ."	#squatter_1	cmd=\"/usr/bin/nice -n 19 /usr/sbin/squatter -s\" period=120\n";
				$conf=$conf ."\n";
				$conf=$conf ."	# reindex all mailboxes (fulltext) daily\n";
				$conf=$conf ."	#squatter_a	cmd=\"/usr/sbin/squatter\" at=0517\n";
				$conf=$conf ."}\n";
				
				return $conf;
	
			}
			
			
		function impadconf(){
			   $ldap=new clladp();
				$users=new usersMenus();
				$conf=$conf ."sasl_auto_transition: no\n";
				$conf=$conf ."sasl_saslauthd_path: /var/run/saslauthd/mux\n";
				$conf=$conf ."altnamespace: no\n";
				$conf=$conf ."unixhierarchysep: yes\n";
				$conf=$conf ."lmtp_downcase_rcpt: yes\n";
				$conf=$conf ."allowanonymouslogin: no\n";
				$conf=$conf ."popminpoll: 1\n";
				$conf=$conf ."autocreatequota: 0\n";
				$conf=$conf ."umask: 077\n";
				$conf=$conf ."sieveusehomedir: false\n";
				$conf=$conf ."sieve_maxscriptsize: 1024\n";
				$conf=$conf ."hashimapspool: true\n";
				$conf=$conf ."allowplaintext: yes\n";
				$conf=$conf ."tls_session_timeout: 1440\n";
				$conf=$conf ."tls_cipher_list: TLSv1+HIGH:!aNULL:@STRENGTH\n";
				$conf=$conf ."idlemethod: poll\n";
				$conf=$conf ."syslog_prefix: cyrus\n";
				$conf=$conf ."servername: $users->hostname\n";
				$conf=$conf ."virtdomains: no\n";
				$conf=$conf ."admins: cyrus\n";
				$conf=$conf ."username_tolower: 1\n";
				$conf=$conf ."ldap_uri: ldap://$ldap->ldap_host:$ldap->ldap_port\n";
				$conf=$conf ."sasl_mech_list: PLAIN LOGIN\n";
				$conf=$conf ."sasl_pwcheck_method: saslauthd\n";
				$conf=$conf ."configdirectory: /var/lib/cyrus\n";
				$conf=$conf ."defaultpartition: default\n";
				$conf=$conf ."partition-default: /var/spool/cyrus/mail\n";
				$conf=$conf ."partition-news: /var/spool/cyrus/news\n";
				$conf=$conf ."srvtab: /var/lib/cyrus/srvtab\n";
				$conf=$conf ."newsspool: /var/spool/cyrus/news\n";	
				
				$conf=$conf ."tls_cert_file: /etc/ssl/certs/cyrus.pem\n";	
				$conf=$conf ."tls_key_file: /etc/ssl/certs/cyrus.pem\n";
				$conf=$conf ."tls_imap_cert_file: /etc/ssl/certs/cyrus.pem\n";
				$conf=$conf ."tls_imap_key_file: /etc/ssl/certs/cyrus.pem\n";
				$conf=$conf ."tls_pop3_cert_file: /etc/ssl/certs/cyrus.pem\n";
				$conf=$conf ."tls_pop3_key_file: /etc/ssl/certs/cyrus.pem\n";
				$conf=$conf ."tls_lmtp_cert_file: /etc/ssl/certs/cyrus.pem\n";
				$conf=$conf ."tls_lmtp_key_file: /etc/ssl/certs/cyrus.pem\n";
				$conf=$conf ."sieve_tls_key_file: /etc/ssl/certs/cyrus.pem\n";
				$conf=$conf ."tls_sieve_cert_file: /etc/ssl/certs/cyrus.pem\n";
				$conf=$conf ."tls_sieve_key_file: /etc/ssl/certs/cyrus.pem\n";
				$conf=$conf ."tls_ca_file: /etc/ssl/certs/cyrus.pem\n";
				$conf=$conf ."tls_ca_path: /etc/ssl/certs\n";
				$conf=$conf ."tls_require_cert: false\n";
				$conf=$conf ."tls_imap_require_cert: false\n";
				$conf=$conf ."tls_pop3_require_cert: false\n";
				$conf=$conf ."tls_lmtp_require_cert: false\n";
				$conf=$conf ."tls_sieve_require_cert: false	\n";			
				
				return $conf;	
			
			
		}
		
		
	  function BuildDefault(){
	  	$this->main_array["CYRUS"]["service_imap_enabed"]=1;
	  	if($this->main_array["CYRUS"]["service_imap_listen"]==null){$this->main_array["CYRUS"]["service_imap_listen"]="imap";}
	  	
	  	if($this->main_array["CYRUS"]["service_imap_maxchild"]==null){$this->main_array["CYRUS"]["service_imap_maxchild"]="500";}
	  	if($this->main_array["CYRUS"]["service_imapssl_maxchild"]==null){$this->main_array["CYRUS"]["service_imapssl_maxchild"]="500";}
	  	
	  	if($this->main_array["CYRUS"]["service_pop3_maxchild"]==null){$this->main_array["CYRUS"]["service_pop3_maxchild"]="150";}
	  	if($this->main_array["CYRUS"]["service_pop3ssl_maxchild"]==null){$this->main_array["CYRUS"]["service_pop3ssl_maxchild"]="150";}
	  	
	  	
	  	if($this->main_array["CYRUS"]["service_imapssl_enabed"]==null){$this->main_array["CYRUS"]["service_imapssl_enabed"]=0;}
	  	if($this->main_array["CYRUS"]["service_imapssl_listen"]==null){$this->main_array["CYRUS"]["service_imapssl_listen"]="imaps";}

	  	if($this->main_array["CYRUS"]["service_pop3_enabed"]==null){$this->main_array["CYRUS"]["service_pop3_enabed"]=1;}
	  	if($this->main_array["CYRUS"]["service_pop3_listen"]==null){$this->main_array["CYRUS"]["service_pop3_listen"]="pop3";}	

	  	if($this->main_array["CYRUS"]["service_pop3ssl_enabed"]==null){$this->main_array["CYRUS"]["service_pop3ssl_enabed"]=0;}
	  	if($this->main_array["CYRUS"]["service_pop3ssl_listen"]==null){$this->main_array["CYRUS"]["service_pop3ssl_listen"]="pop3s";}	

	  	if($this->main_array["CYRUS"]["service_nntpd_enabed"]==null){$this->main_array["CYRUS"]["service_nntpd_enabed"]=1;}
	  	if($this->main_array["CYRUS"]["service_nntpd_listen"]==null){$this->main_array["CYRUS"]["service_nntpd_listen"]="nntp";}

	  	if($this->main_array["CYRUS"]["service_nntpds_enabed"]==null){$this->main_array["CYRUS"]["service_nntpds_enabed"]=0;}
	  	if($this->main_array["CYRUS"]["service_nntpds_listen"]==null){$this->main_array["CYRUS"]["service_nntpds_listen"]="nntps";}	  	  	
	  	
	  }
		
		
	  function BuildArticaConf(){
	  	
	  	$conf=$conf ."[CYRUS]\n";
	  	$conf=$conf ."service_imap_enabed={$this->main_array["CYRUS"]["service_imap_enabed"]}\n";
	  	$conf=$conf ."service_imap_listen={$this->main_array["CYRUS"]["service_imap_listen"]}\n";
	  	$conf=$conf ."service_imap_maxchild={$this->main_array["CYRUS"]["service_imap_maxchild"]}\n";
	  	
	  	$conf=$conf ."service_imapssl_enabed={$this->main_array["CYRUS"]["service_imapssl_enabed"]}\n";
	  	$conf=$conf ."service_imapssl_listen={$this->main_array["CYRUS"]["service_imapssl_listen"]}\n";
	  	$conf=$conf ."service_imapssl_maxchild={$this->main_array["CYRUS"]["service_imapssl_maxchild"]}\n";	
	  	
	  	$conf=$conf ."service_pop3_enabed={$this->main_array["CYRUS"]["service_pop3_enabed"]}\n";
	  	$conf=$conf ."service_pop3_listen={$this->main_array["CYRUS"]["service_pop3_listen"]}\n";
	  	$conf=$conf ."service_pop3_maxchild={$this->main_array["CYRUS"]["service_pop3_maxchild"]}\n";	  	  	  	
	  	
	  	$conf=$conf ."service_pop3ssl_enabed={$this->main_array["CYRUS"]["service_pop3ssl_enabed"]}\n";
	  	$conf=$conf ."service_pop3ssl_listen={$this->main_array["CYRUS"]["service_pop3ssl_listen"]}\n";
		$conf=$conf ."service_pop3ssl_maxchild={$this->main_array["CYRUS"]["service_pop3ssl_maxchild"]}\n";		  		 

	  	$conf=$conf ."service_nntpd_enabed={$this->main_array["CYRUS"]["service_nntpd_enabed"]}\n";
	  	$conf=$conf ."service_nntpd_listen={$this->main_array["CYRUS"]["service_nntpd_listen"]}\n";	 	  	
	  	  	
	  	$conf=$conf ."service_nntpds_enabed={$this->main_array["CYRUS"]["service_nntpds_enabed"]}\n";
	  	$conf=$conf ."service_nntpds_listen={$this->main_array["CYRUS"]["service_nntpds_listen"]}\n";		  	
	  	return $conf;
	  	
	  }
	
}