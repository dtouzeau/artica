<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.main_cf.inc');
include_once(dirname(__FILE__).'/ressources/class.maincf.multi.inc');
include_once(dirname(__FILE__).'/ressources/class.main_cf_filtering.inc');
include_once(dirname(__FILE__).'/ressources/class.policyd-weight.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');

$_GET["LOGFILE"]="/usr/share/artica-postfix/ressources/logs/web/interface-postfix.log";
if(!is_file("/usr/share/artica-postfix/ressources/settings.inc")){shell_exec("/usr/share/artica-postfix/bin/process1 --force --verbose");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
$sock=new sockets();
$unix=new unix();



$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
if($unix->process_exists(@file_get_contents($pidefile))){
	echo "Starting......: Postfix configurator already executed PID ". @file_get_contents($pidefile)."\n";
	die();
}


$pid=getmypid();
echo "Starting......: Postfix configurator running $pid\n";
file_put_contents($pidfile,$pid);

$users=new usersMenus();
if(!$users->POSTFIX_INSTALLED){
	echo("Postfix is not installed\n");
	die();
}


if(!$unix->IS_OPENLDAP_RUNNING()){
	echo "Starting......: Postfix openldap is not running, start it\n";
	system("/etc/init.d/artica-postfix start ldap");
}
if(!$unix->IS_OPENLDAP_RUNNING()){
	echo "Starting......: Postfix openldap is not running, aborting\n";
	die();
}


$GLOBALS["EnablePostfixMultiInstance"]=$sock->GET_INFO("EnablePostfixMultiInstance");
$GLOBALS["EnableBlockUsersTroughInternet"]=$sock->GET_INFO("EnableBlockUsersTroughInternet");
$GLOBALS["postconf"]=$unix->find_program("postconf");
$GLOBALS["postmap"]=$unix->find_program("postmap");
$GLOBALS["postfix"]=$unix->find_program("postfix");

if($argv[1]=='--networks'){mynetworks();shell_exec("{$GLOBALS["postfix"]} reload");die();}
if($argv[1]=='--headers-check'){headers_check();die();}
if($argv[1]=='--assp'){ASSP_LOCALDOMAINS();die();}
if($argv[1]=='--artica-filter'){ArticaFilterInMasterCF();die();}
if($argv[1]=='--ldap-branch'){BuildDefaultBranchs();die();}
if($argv[1]=='--ssl'){MasterCFSSL(1);die();}
if($argv[1]=='--ssl-on'){MasterCFSSL_enable(1);die();}
if($argv[1]=='--ssl-off'){MasterCFSSL_disable(1);die();}
if($argv[1]=='--imap-sockets'){imap_sockets();die();}
if($argv[1]=='--policyd-reconfigure'){policyd_weight_reconfigure();die();}
if($argv[1]=='--restricted'){RestrictedForInternet(true);die();}
if($argv[1]=='--others-values'){OthersValues();CleanMyHostname();exec("{$GLOBALS["postfix"]} reload");die();}
if($argv[1]=='--mime-header-checks'){mime_header_checks();exec("{$GLOBALS["postfix"]} reload");die();}
if($argv[1]=='--interfaces'){inet_interfaces();exec("{$GLOBALS["postfix"]} stop");exec("{$GLOBALS["postfix"]} start");die();}
if($argv[1]=='--mailbox-transport'){MailBoxTransport();exec("{$GLOBALS["postfix"]} stop");exec("{$GLOBALS["postfix"]} start");die();}
if($argv[1]=='--disable-smtp-sasl'){disable_smtp_sasl();exec("{$GLOBALS["postfix"]} reload");die();}
if($argv[1]=='--perso-settings'){perso_settings();die();}
if($argv[1]=='--luser-relay'){luser_relay();die();}





if($argv[1]=='--reconfigure'){
	if($GLOBALS["EnablePostfixMultiInstance"]==1){
		shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.postfix-multi.php --from-main-reconfigure");
	}
	

	
	$main=new main_cf();
	$main->save_conf_to_server(1);
	if(!is_file("/etc/postfix/hash_files/header_checks.cf")){@file_put_contents("/etc/postfix/hash_files/header_checks.cf","#");}
	file_put_contents('/etc/postfix/main.cf',$main->main_cf_datas);
	echo "Starting......: Postfix Building main.cf ". strlen($main->main_cf_datas). " bytes done line ". __LINE__."\n";
	_DefaultSettings();
	die();
}

function _DefaultSettings(){
if($GLOBALS["EnablePostfixMultiInstance"]==1){shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.postfix-multi.php --from-main-null");return;}
	SetTLS();
	inet_interfaces();
	headers_check(1);
	ArticaFilterInMasterCF();
	ArticaFilterInMasterCFPipe();
	MasterCFSSL();
	mime_header_checks();
	smtp_sasl_auth_enable();
	smtpd_recipient_restrictions();
	smtpd_client_restrictions();
	smtpd_sasl_exceptions_networks();
	sender_bcc_maps();
	CleanMyHostname();
	OthersValues();
	MailBoxTransport();
	mynetworks();
	luser_relay();
	perso_settings();
	ReloadPostfix();	
	
}



if($argv[1]=='--write-maincf'){
	if($GLOBALS["EnablePostfixMultiInstance"]==1){shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.postfix-multi.php --from-main-write-maincf");return;}
	echo "Starting......: Postfix Postfix Multi Instance disabled, single instance mode\n";
	$main=new main_cf();
	$main->save_conf_to_server(1);
	file_put_contents('/etc/postfix/main.cf',$main->main_cf_datas);
	echo "Starting......: Postfix Building main.cf ". strlen($main->main_cf_datas). "line ". __LINE__." bytes done\n";
	if(!is_file("/etc/postfix/hash_files/header_checks.cf")){@file_put_contents("/etc/postfix/hash_files/header_checks.cf","#");}
	_DefaultSettings();
	if($argv[2]=='no-restart'){appliSecu();die();}
	echo "Starting......: restarting postfix\n";
	shell_exec("/etc/init.d/artica-postfix restart postfix");
	die();
}

if($argv[1]=='--maincf'){
	if($GLOBALS["EnablePostfixMultiInstance"]==1){shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.postfix-multi.php --from-main-maincf");return;}	
	$main=new main_cf();
	$main->save_conf_to_server(1);
	file_put_contents('/etc/postfix/main.cf',$main->main_cf_datas);
	_DefaultSettings();
	if($GLOBALS["DEBUG"]){echo @file_get_contents("/etc/postfix/main.cf");}
	die();
}





function ASSP_LOCALDOMAINS(){
	if($GLOBALS["EnablePostfixMultiInstance"]==1){return null;}
	if(!is_dir("/usr/share/assp/files")){return null;}
	$ldap=new clladp();
	$domains=$ldap->hash_get_all_domains();
	while (list ($num, $ligne) = each ($domains) ){
		$conf=$conf."$ligne\n";
	}
	echo "Starting......: ASSP ". count($domains)." local domains\n"; 
	@file_put_contents("/usr/share/assp/files/localdomains.txt",$conf);
	
}


function SetTLS(){
	
	$sock=new sockets();
	$smtpd_tls_security_level=trim($sock->GET_INFO('smtpd_tls_security_level'));
	if($smtpd_tls_security_level<>null){
		shell_exec("{$GLOBALS["postconf"]} -e \"smtpd_tls_security_level = $smtpd_tls_security_level\" >/dev/null 2>&1");
	}
	
if($sock->GET_INFO('smtp_sender_dependent_authentication')==1){
	shell_exec("{$GLOBALS["postconf"]} -e \"smtp_sender_dependent_authentication = yes\" >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"smtpd_sasl_auth_enable = yes\" >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"smtp_sasl_security_options = \" >/dev/null 2>&1");
	}
	
	$main=new main_cf();
	shell_exec("{$GLOBALS["postconf"]} -e \"broken_sasl_auth_clients = $main->broken_sasl_auth_clients\" >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"smtpd_sasl_security_options = $main->smtpd_sasl_security_options\" >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"smtpd_sasl_local_domain = $main->smtpd_sasl_local_domain\" >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"smtpd_sasl_authenticated_header = $main->smtpd_sasl_authenticated_header\" >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"smtpd_tls_security_level = $main->smtpd_tls_security_level\" >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"smtpd_tls_auth_only = $main->smtpd_tls_auth_only\" >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"smtpd_tls_received_header = $main->smtpd_tls_received_header\" >/dev/null 2>&1");
	}

function mynetworks(){
	
	if($GLOBALS["EnablePostfixMultiInstance"]==1){
		echo "Starting......: Building mynetworks multiple-instances, enabled\n";
		shell_exec("{$GLOBALS["postconf"]} -e \"mynetworks =127.0.0.0/8\" >/dev/null 2>&1");
		return;
	}
	
	$ldap=new clladp();
	$nets=$ldap->load_mynetworks();
	if(!is_array($nets)){
		if($GLOBALS["DEBUG"]){echo "No networks sets\n";}
		shell_exec("{$GLOBALS["postconf"]} -e \"mynetworks =127.0.0.0/8\" >/dev/null 2>&1");
		return;
	}
	$nets[]="127.0.0.0/8";

	while (list ($num, $network) = each ($nets) ){$cleaned[$network]=$network;}
	unset($nets);
	while (list ($network, $network2) = each ($cleaned) ){$nets[]=$network;}
	
	
	
	$inline=@implode(", ",$nets);
	$inline=str_replace(',,',',',$inline);
	$config_net=@implode("\n",$nets);
	echo "Starting......: Building mynetworks ". count($nets)." Networks ($inline)\n";
	@file_put_contents("/etc/artica-postfix/mynetworks",$config_net);
	shell_exec("{$GLOBALS["postconf"]} -e \"mynetworks = $inline\" >/dev/null 2>&1");
	
}

function headers_check($noreload=0){
	
	$main=new main_header_check();
	$headers=false;
	$array=$main->main_table;
	if(is_array($array)){
		$conf=implode("\n",$array);
		$headers=true;
	}
	
	@file_put_contents("/etc/postfix/header_checks",$conf);
	@chown("/etc/postfix/header_checks","root");
	@chgrp("/etc/postfix/header_checks","root");
	if($headers){
		shell_exec("{$GLOBALS["postconf"]} -e \"header_checks = regexp:/etc/postfix/header_checks\" >/dev/null 2>&1");
	}else{
		shell_exec("{$GLOBALS["postconf"]} -e \"header_checks = \" >/dev/null 2>&1");
	}
	
	
	
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.white-black-central.php");
	if($noreload==0){ReloadPostfix();}
}


function ReloadPostfix(){
	echo "Starting......: Postfix Compiling tables...\n";
	system(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php");
	shell_exec("{$GLOBALS["postconf"]} -e \"myorigin =\\\$mydomain \" >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"smtpd_delay_reject =no \" >/dev/null 2>&1");
	
	
	
	echo "Starting......: Postfix Apply securities issues\n"; 
	appliSecu();
	echo "Starting......: Postfix Reloading ASSP\n"; 
	system("/usr/share/artica-postfix/bin/artica-install --reload-assp");
	echo "Starting......: Postfix reloading postfix master\n";
	if(is_file("/usr/sbin/postfix")){
		shell_exec("/usr/sbin/postfix reload");
		return;
	}
	
	
	
}

function appliSecu(){
	if(is_file("/var/lib/postfix/smtpd_tls_session_cache.db")){shell_exec("/bin/chown postfix:postfix /var/lib/postfix/smtpd_tls_session_cache.db");}
	if(is_file("/var/lib/postfix/master.lock")){@chown("/var/lib/postfix/master.lock","postfix");}
}

function ArticaFilterInMasterCF(){

	$sock=new sockets();
	$EnableArticaSMTPFilter=$sock->GET_INFO("EnableArticaSMTPFilter");
	$EnableAmavisInMasterCF=$sock->GET_INFO('EnableAmavisInMasterCF');
	$EnableAmavisDaemon=$sock->GET_INFO('EnableAmavisDaemon');
	if($GLOBALS["EnablePostfixMultiInstance"]==1){$EnableAmavisDaemon=0;}
	if($EnableAmavisInMasterCF==null){$EnableAmavisInMasterCF=0;}
	if($EnableAmavisDaemon==0){$EnableAmavisInMasterCF=0;}
	shell_exec("{$GLOBALS["postconf"]} -e \"artica-filter_destination_recipient_limit = 1\" >/dev/null 2>&1");
	if($EnableAmavisInMasterCF==0){
		if($EnableArticaSMTPFilter==1){
			echo "Starting......: Enable Artica-filter globally\n"; 
			shell_exec("{$GLOBALS["postconf"]} -e \"content_filter = artica-filter:\" >/dev/null 2>&1");
		}else{
			echo "Starting......: Disable Artica-filter globally\n"; 
			shell_exec("{$GLOBALS["postconf"]} -e \"content_filter =\" >/dev/null 2>&1");
		}
	}
	
	
	if($EnableArticaSMTPFilter==1){
		shell_exec("{$GLOBALS["postconf"]} -e \"recipient_bcc_maps = \" >/dev/null 2>&1");
	}
	
	$file_content=@file_get_contents("/etc/postfix/master.cf");
	$file_content=str_replace("\n\n","\n",$file_content);
	$data=explode("\n",$file_content);
	$start=false;
	while (list ($num, $ligne) = each ($data) ){
		if(!$start){
		if(preg_match("#^127\.0\.0\.1:33559\s+inet.+?smtpd#",$ligne,$re)){
			$start=true;
			unset($data[$num]);
			continue;
		}
		}
		
		if($start){
			if(preg_match("#^\s+-o\s+.+#",$ligne)){
				unset($data[$num]);
				continue;
			}else{
				break;
			}
			if(preg_match("#^[0-9A-Za-z\:\-]+#",$ligne)){
				break;
			}
		}
		
	}
	
	$data[]="127.0.0.1:33559	inet	n	-	n	-	-	smtpd";
	$data[]="    -o notify_clases=protocol,resource,software";
	$data[]="    -o header_checks=";
	$data[]="    -o content_filter=";
	$data[]="    -o smtpd_restriction_classes=";
	$data[]="    -o smtpd_delay_reject=no";
	$data[]="    -o smtpd_client_restrictions=permit_mynetworks,reject";
	$data[]="    -o smtpd_helo_restrictions=";
	$data[]="    -o smtpd_sender_restrictions=";
	$data[]="    -o smtpd_recipient_restrictions=permit_mynetworks,reject";
	$data[]="    -o smtpd_data_restrictions=reject_unauth_pipelining";
	$data[]="    -o smtpd_end_of_data_restrictions=";
	$data[]="    -o mynetworks=127.0.0.0/8";
	$data[]="    -o strict_rfc821_envelopes=yes";
	$data[]="    -o smtpd_error_sleep_time=0";
	$data[]="    -o smtpd_soft_error_limit=1001";
	$data[]="    -o smtpd_hard_error_limit=1000";
	$data[]="    -o smtpd_client_connection_count_limit=0";
	$data[]="    -o smtpd_client_connection_rate_limit=0";
	$data[]="    -o receive_override_options=no_header_body_checks,no_unknown_recipient_checks";
	$data[]="    -o smtp_send_xforward_command=yes";
	$data[]="    -o disable_dns_lookups=yes";
	$data[]="    -o local_header_rewrite_clients=";
	$data[]=" 	 -o smtp_generic_maps=";
	$data[]=" 	 -o sender_canonical_maps=";
	$data[]="    -o smtpd_milters="; 	
	@file_put_contents("/etc/postfix/master.cf",implode("\n",$data));	
	
	if($EnableAmavisInMasterCF==1){
		$data=explode("\n",@file_get_contents("/etc/postfix/master.cf"));
		$start=false;
		while (list ($num, $ligne) = each ($data) ){
			if(!$start){if(preg_match("#^127\.0\.0\.1:10025\s+inet#",$ligne,$re)){$start=true;continue;}}
			if($start){
				if(preg_match("#\s+-o\s+content_filter#",$ligne)){
					echo "Starting......: artica-filter enable=$EnableArticaSMTPFilter\n";
					if($EnableArticaSMTPFilter==1){$data[$num]="    -o content_filter=artica-filter:";}
					if($EnableArticaSMTPFilter==0){$data[$num]="    -o content_filter=";}
					@file_put_contents("/etc/postfix/master.cf",implode("\n",$data));
					break;
				}
			}
		}
	}
	
if($GLOBALS["RELOAD"]){shell_exec("/usr/sbin/postfix reload");}	
	
	
}
function ArticaFilterInMasterCFPipe(){
	if($GLOBALS["EnablePostfixMultiInstance"]==1){return null;}
	$sock=new sockets();
	$ArticaFilterMaxProc=$sock->GET_INFO("ArticaFilterMaxProc");
	if($ArticaFilterMaxProc==null){$ArticaFilterMaxProc=20;}
	$file_content=@file_get_contents("/etc/postfix/master.cf");
	$file_content=@file_get_contents("/etc/postfix/master.cf");
	$file_content=str_replace("\n\n","\n",$file_content);
	$data=explode("\n",$file_content);
	$start=false;
	while (list ($num, $ligne) = each ($data) ){	
	if(preg_match("#^artica-filter\s+#",$ligne)){unset($data[$num]);continue;}
	if(preg_match("#exec\.artica-filter\.php#",$ligne)){unset($data[$num]);continue;}
		
	}
	echo "Starting......: Artica-filter max process: $ArticaFilterMaxProc\n";
	$data[]="artica-filter    unix  -       n       n       -       $ArticaFilterMaxProc       pipe";
	$data[]="  flags=FOh  user=www-data argv=/usr/share/artica-postfix/exec.artica-filter.php -f \${sender} --  -s \${sender} -r \${recipient} -c \${client_address}";  	
	@file_put_contents("/etc/postfix/master.cf",implode("\n",$data));
	
}


function BuildDefaultBranchs(){
	
	$main=new main_cf();
	$main->BuildDefaultWhiteListRobots();
	
	$sender=new sender_dependent_relayhost_maps();
	
	if($GLOBALS["RELOAD"]){
		$unix=new unix();
		$postfix=$unix->find_program("postfix");
		shell_exec("$postfix stop && $postfix start");
	}
}

function MasterCFSSL($restart=0){

	$sock=new sockets();
	$PostfixEnableMasterCfSSL=$sock->GET_INFO("PostfixEnableMasterCfSSL");
	if($GLOBALS["EnablePostfixMultiInstance"]==1){$PostfixEnableMasterCfSSL=0;}
	if($PostfixEnableMasterCfSSL==1){MasterCFSSL_enable();}else{MasterCFSSL_disable();}
	if($restart<>0){shell_exec("/usr/sbin/postfix stop && /usr/sbin/postfix start");}
	
}

function MasterCFSSL_disable(){
if($GLOBALS["EnablePostfixMultiInstance"]){die();}
	echo "Starting......: Disabling SSL (465 port)\n";
	$file_content=@file_get_contents("/etc/postfix/master.cf");
	$file_content=str_replace("\n\n","\n",$file_content);
	$data=explode("\n",$file_content);
	$start=false;
	while (list ($num, $ligne) = each ($data) ){
		if(preg_match("#^smtps\s+inet#",$ligne)){
			unset($data[$num]);
			$start=true;
			continue;
		}
		if($start){
			if(preg_match("#-o\s+#",$ligne)){
				unset($data[$num]);
			}else{
				break;
			}
		}
		
	}
	
@file_put_contents("/etc/postfix/master.cf",implode("\n",$data));	
}	
function MasterCFSSL_enable(){
	if($GLOBALS["EnablePostfixMultiInstance"]){die();}
	echo "Starting......: Enabling SSL (465 port)\n";
	SetTLS();
	$file_content=@file_get_contents("/etc/postfix/master.cf");
	$file_content=str_replace("\n\n","\n",$file_content);
	$data=explode("\n",$file_content);
	$start=false;
	while (list ($num, $ligne) = each ($data) ){	
		if(preg_match("#^smtps\s+inet#",$ligne)){return true;}
	}
	
	$data[]="\nsmtps	inet	n	-	n	-	-	smtpd";
	$data[]=" -o smtpd_tls_wrappermode=yes";
	$data[]=" -o smtpd_client_restrictions=permit_mynetworks,permit_sasl_authenticated,reject\n";
	@file_put_contents("/etc/postfix/master.cf",implode("\n",$data));
	}
	
function imap_sockets(){
	if(!is_file("/etc/imapd.conf")){
		echo "Starting......: cyrus transport no available\n";
		return;
	}
	
	shell_exec("/usr/share/artica-postfix/bin/artica-install --reconfigure-cyrus");
	
	
	
	$f=explode("\n",@file_get_contents("/etc/imapd.conf"));
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#lmtpsocket:(.+)#",$ligne,$re)){
			$socket=trim($re[1]);
		}
	}
	
	$f=explode("\n",@file_get_contents("/etc/cyrus.conf"));
	while (list ($num, $ligne) = each ($f) ){
		if(substr($ligne,0,1)=="#"){continue;}
		if(preg_match("#lmtpunix\s+(.+)#",$ligne,$re)){
			echo "Starting......: cyrus lmtpunix: $ligne\n";
			$f[$num]="  lmtpunix	cmd=\"lmtpd\" listen=\"$socket\" prefork=1";
			$write=true;
		}
	}	
	
	if($write){
		@file_put_contents("/etc/cyrus.conf",implode("\n",$f));
		shell_exec("/etc/init.d/artica-postfix restart imap");
	}
	if(!is_file($socket)){
		if(is_file("$socket=")){$socket="$socket=";}
	}
	
	echo "Starting......: cyrus transport: unix: $socket\n";
	if($socket<>null){
		shell_exec("{$GLOBALS["postconf"]} -e \"mailbox_transport = lmtp:unix:$socket\" >/dev/null 2>&1");
		shell_exec("postfix stop && postfix start && postqueue -f");
	}
	
	
	
}

function policyd_weight_reconfigure(){
	$pol=new policydweight();
	$conf=$pol->buildConf();
	@file_put_contents("/etc/artica-postfix/settings/Daemons/PolicydWeightConfig",$conf);
	echo "Starting......: policyd-weight building first config done\n";
}

function mime_header_checks(){
	$sql=new mysql();
	$sql="SELECT * FROM smtp_attachments_blocking WHERE ou='_Global' ORDER BY IncludeByName";
	writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
	$q=new mysql();
	writelogs("-> Qyery",__FUNCTION__,__FILE__,__LINE__);
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		writelogs("Error mysql $q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		return null;}
		
	writelogs("-> loop",__FUNCTION__,__FILE__,__LINE__);
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["IncludeByName"]==null){continue;}
		$f[]=$ligne["IncludeByName"];
		
	}	
	if(!is_array($f)){
		echo "Starting......: No extensions blocked\n";
		shell_exec("{$GLOBALS["postconf"]} -e \"mime_header_checks = \" >/dev/null 2>&1");
		return;
	}
	
	$strings=implode("|",$f);
	echo "Starting......: ". count($f)." extensions blocked\n";
	$pattern[]="/^\s*Content-(Disposition|Type).*name\s*=\s*\"?(.+\.($strings))\"?\s*$/\tREJECT file attachment types is not allowed. File \"$2\" has the unacceptable extension \"$3\"";
	$pattern[]="";
	@file_put_contents("/etc/postfix/mime_header_checks",implode("\n",$pattern));
	shell_exec("{$GLOBALS["postconf"]} -e \"mime_header_checks = regexp:/etc/postfix/mime_header_checks\" >/dev/null 2>&1");
}

function smtp_sasl_auth_enable(){
	$ldap=new clladp();
	if($ldap->ldapFailed){
		echo "Starting......: SMTP SALS connection to ldap failed\n";
		return;
	}

	$suffix="dc=organizations,$ldap->suffix";
	$filter="(&(objectclass=SenderDependentSaslInfos)(SenderCanonicalRelayPassword=*))";
	$res=array();
	$search = @ldap_search($ldap->ldap_connection,$suffix,"$filter",array());
	$count=0;		
	if ($search) {
			$hash=ldap_get_entries($ldap->ldap_connection,$search);	
			$count=$hash["count"];
		}
	
	echo "Starting......: SMTP SALS $count account(s)\n"; 	
	if($count>0){
		shell_exec("{$GLOBALS["postconf"]} -e \"smtp_sasl_auth_enable = yes\" >/dev/null 2>&1");
		shell_exec("{$GLOBALS["postconf"]} -e \"smtp_sender_dependent_authentication = yes\" >/dev/null 2>&1");
		
	}else{
		shell_exec("{$GLOBALS["postconf"]} -e \"smtp_sender_dependent_authentication = no\" >/dev/null 2>&1");
	}

}


function smtpd_client_restrictions(){
	
	
	exec("{$GLOBALS["postconf"]} -h smtpd_client_restrictions",$datas);
	$tbl=explode(",",implode(" ",$datas));
	
	

	if(is_array($tbl)){
		while (list ($num, $ligne) = each ($tbl) ){
		$ligne=trim($ligne);
		if(trim($ligne)==null){continue;}
		if($ligne=="Array"){continue;}
		$newHash[$ligne]=$ligne;
		}
	}
	
	
	
	if(is_array($newHash)){	
		while (list ($num, $ligne) = each ($newHash) ){
			
			if(preg_match("#hash:(.+)$#",$ligne,$re)){
				$path=trim($re[1]);
				if(!is_file($path)){
					echo "Starting......: smtpd_client_restrictions: bungled \"$ligne\"\n"; 
					continue;
				}
			}
			$smtpd_client_restrictions[]=$num;
		}
	}

	
	$newval=implode(",",$smtpd_client_restrictions);
	system("{$GLOBALS["postconf"]} -e \"smtpd_client_restrictions = $newval\" >/dev/null 2>&1");	
	
	
}

function smtpd_recipient_restrictions(){
	
	exec("{$GLOBALS["postconf"]} -h smtpd_recipient_restrictions",$datas);
	$tbl=explode(",",implode(" ",$datas));
	$permit_mynetworks_remove=false;

	if(is_array($tbl)){
		while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$newHash[trim($ligne)]=trim($ligne);
		}
	}
	
	unset($newHash["permit"]);
	unset($newHash["check_sender_access hash:/etc/postfix/disallow_my_domain"]);
	unset($newHash["check_sender_access hash:/etc/postfix/unrestricted_senders"]);
	unset($newHash["reject_unauth_destination"]);
	unset($newHash["permit_mynetworks"]);
	
	if(is_array($newHash)){	
		while (list ($num, $ligne) = each ($newHash) ){
		if(preg_match("#hash:(.+)$#",$ligne,$re)){
				$path=trim($re[1]);
				if(!is_file($path)){
					echo "Starting......: smtpd_recipient_restrictions: bungled \"$ligne\"\n"; 
					continue;
				}
			}
			$smtpd_recipient_restrictions[]=$num;
		}
	}
	
	$smtpd_recipient_restrictions[]="permit_mynetworks";
	$smtpd_recipient_restrictions[]="permit_sasl_authenticated";
	
	
	
	system("{$GLOBALS["postconf"]} -e \"auth_relay=\" >/dev/null 2>&1");	
	
	
	if($GLOBALS["EnableBlockUsersTroughInternet"]==1){
		echo "Starting......: Restricted users are enabled\n"; 	
		if(RestrictedForInternet()){
			 system("{$GLOBALS["postconf"]} -e \"auth_relay=check_recipient_access hash:/etc/postfix/local_domains, reject\" >/dev/null 2>&1");			
			 array_unshift($smtpd_recipient_restrictions,"check_sender_access hash:/etc/postfix/unrestricted_senders");
			__ADD_smtpd_restriction_classes("auth_relay");
		}else{__REMOVE_smtpd_restriction_classes("auth_relay");}
	}
	else{__REMOVE_smtpd_restriction_classes("auth_relay");}
		
		
	$sock=new sockets();
	$reject_forged_mails=$sock->GET_INFO("reject_forged_mails");
	if($reject_forged_mails==1){
		if(smtpd_recipient_restrictions_reject_forged_mails()){
			echo "Starting......: Reject Forged mails enabled\n"; 	
			$smtpd_recipient_restrictions[]="check_sender_access hash:/etc/postfix/disallow_my_domain";
		}
	}else{
		echo "Starting......: Reject Forged mails disabled\n"; 			
	}
	$smtpd_recipient_restrictions[]="reject_unauth_destination";	
	
	
	//CLEAN engine ---------------------------------------------------------------------------------------
	while (list ($num, $ligne) = each ($smtpd_recipient_restrictions) ){
		$smtpd_recipient_restrictions_cleaned[trim($ligne)]=trim($ligne);
	}
	
	
	
	unset($smtpd_recipient_restrictions);
	while (list ($num, $ligne) = each ($smtpd_recipient_restrictions_cleaned) ){$smtpd_recipient_restrictions[]=trim($ligne);}

   //CLEAN engine ---------------------------------------------------------------------------------------
	
	
	if(is_array($smtpd_recipient_restrictions)){$newval=implode(",",$smtpd_recipient_restrictions);}
	system("{$GLOBALS["postconf"]} -e \"smtpd_recipient_restrictions = $newval\" >/dev/null 2>&1");	
	
	}
	
function __ADD_smtpd_restriction_classes($classname){
exec("{$GLOBALS["postconf"]} -h smtpd_restriction_classes",$datas);
	$tbl=explode(",",implode(" ",$datas));
	

	if(is_array($tbl)){
		while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$newHash[$ligne]=$ligne;
		}
	}
	
	unset($newHash[$classname]);
	
	if(is_array($newHash)){	
		while (list ($num, $ligne) = each ($newHash) ){	
			$smtpd_restriction_classes[]=$num;
		}
	}
	
	$smtpd_restriction_classes[]=$classname;
	if(is_array($smtpd_restriction_classes)){$newval=implode(",",$smtpd_restriction_classes);}
	
	system("{$GLOBALS["postconf"]} -e \"smtpd_restriction_classes = $newval\" >/dev/null 2>&1");		
	
}

function __REMOVE_smtpd_restriction_classes($classname){
exec("{$GLOBALS["postconf"]} -h smtpd_restriction_classes",$datas);
	$tbl=explode(",",implode(" ",$datas));
	

	if(is_array($tbl)){
		while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$newHash[$ligne]=$ligne;
		}
	}
	
	unset($newHash[$classname]);
	
	if(is_array($newHash)){	
		while (list ($num, $ligne) = each ($newHash) ){	
			$smtpd_restriction_classes[]=$num;
		}
	}
	
	if(is_array($smtpd_restriction_classes)){$newval=implode(",",$smtpd_restriction_classes);}
	$cmd="{$GLOBALS["postconf"]} -e \"smtpd_restriction_classes = $newval\" >/dev/null 2>&1";
	system("{$GLOBALS["postconf"]} -e \"smtpd_restriction_classes = $newval\" >/dev/null 2>&1");
}
	
	
function smtpd_recipient_restrictions_reject_forged_mails(){
	$ldap=new clladp();
	$unix=new unix();
	$postmap=$unix->find_program("postmap");
	$hash=$ldap->hash_get_all_domains();
	if(!is_array($hash)){return false;}
	while (list ($domain, $ligne) = each ($hash) ){
		$f[]="$domain\t 554 $domain FORGED MAIL"; 
		
	}
	
	if(!is_array($f)){return false;}
	@file_put_contents("/etc/postfix/disallow_my_domain",@implode("\n",$f));
	echo "Starting......: compiling domains against forged messages\n";
	shell_exec("$postmap hash:/etc/postfix/disallow_my_domain");
	return true;
}

function RestrictedForInternet($reload=false){
	$main=new main_cf();
	$unix=new unix();
	$GLOBALS["postmap"]=$unix->find_program("postmap");
	$restricted_users=$users=$main->check_sender_access();
	if(!$reload){echo "Starting......: Restricted users ($restricted_users)\n";}
	if($restricted_users>0){
		@copy("/etc/artica-postfix/settings/Daemons/unrestricted_senders","/etc/postfix/unrestricted_senders");
		@copy("/etc/artica-postfix/settings/Daemons/unrestricted_senders_domains","/etc/postfix/local_domains");
		echo "Starting......: Compiling unrestricted users ($restricted_users)\n";
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/unrestricted_senders");
		echo "Starting......: Compiling local domains\n";
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/local_domains");
		if($reload){shell_exec("{$GLOBALS["postfix"]} reload");}
		return true;
		}
	return false;
	
}

function CleanMyHostname(){
	exec("{$GLOBALS["postconf"]} -h myhostname",$results);
	$myhostname=trim(implode("",$results));
	$myhostname=str_replace("header_checks =","",$myhostname);
	exec("{$GLOBALS["postconf"]} -h relayhost",$results);
	
	if(is_array($results)){
		$relayhost=trim(@implode("",$results));
	}
	
	if($myhostname=="Array.local"){
		$users=new usersMenus();
		$myhostname=$users->hostname;
	}
	
	if($relayhost<>null){
		if($myhostname==$relayhost){
			$myhostname="$myhostname.local";
		}
	}
	
	//fix bug with extension.
	$myhostname=str_replace(".local.local.",".local",$myhostname);
	$myhostname=str_replace(".locallocal.locallocal.",".",$myhostname);
	$myhostname=str_replace(".locallocal",".local",$myhostname);
	$sock=new sockets();
	$myhostname2=trim($sock->GET_INFO("myhostname"));
	if(strlen($myhostname2)>0){
		$myhostname=$myhostname2;
	}
	
	echo "Starting......: Hostname \"$myhostname\"\n";
	system("{$GLOBALS["postconf"]} -e \"myhostname = $myhostname\" >/dev/null 2>&1");
}

function smtpd_sasl_exceptions_networks(){
	$sock=new sockets();
	$smtpd_sasl_exceptions_networks_list=unserialize(base64_decode($sock->GET_INFO("smtpd_sasl_exceptions_networks")));
	$smtpd_sasl_exceptions_mynet=$sock->GET_INFO("smtpd_sasl_exceptions_mynet");	
	if($smtpd_sasl_exceptions_mynet==1){
		$nets[]="\\\$mynetworks";
	}
	
	if(is_array($smtpd_sasl_exceptions_networks_list)){
		while (list ($num, $val) = each ($smtpd_sasl_exceptions_networks_list) ){
			if($val==null){continue;}
			$nets[]=$val;
		}
	}
	
	if(is_array($nets)){
		$final_nets=implode(",",$nets);
		echo "Starting......: SASL exceptions enabled\n";
		system("{$GLOBALS["postconf"]} -e \"smtpd_sasl_exceptions_networks = $final_nets\" >/dev/null 2>&1");
	}else{
		echo "Starting......: SASL exceptions disabled\n";
		system("{$GLOBALS["postconf"]} -e \"smtpd_sasl_exceptions_networks = \" >/dev/null 2>&1");
	}
}

function sender_bcc_maps(){
	$sock=new sockets();
	$sender_bcc_maps_path=$sock->GET_INFO("sender_bcc_maps_path");
	if(is_file($sender_bcc_maps_path)){
		echo "Starting......: Sender BCC \"$sender_bcc_maps_path\"\n";
		system("{$GLOBALS["postconf"]} -e \"sender_bcc_maps =  hash:$sender_bcc_maps_path\" >/dev/null 2>&1");
		shell_exec("{$GLOBALS["postmap"]} hash:$sender_bcc_maps_path");
	}
	
}

function OthersValues(){
	$sock=new sockets();
	if($sock->GET_INFO("EnablePostfixMultiInstance")==1){return;}	
	$main=new main_cf();
	$main->FillDefaults();	
	echo "Starting......: Fix others settings\n";
	
	
	$main->main_array["message_size_limit"]=$sock->GET_INFO("message_size_limit");
	$main->main_array["default_destination_recipient_limit"]=$sock->GET_INFO("default_destination_recipient_limit");
	$main->main_array["smtpd_recipient_limit"]=$sock->GET_INFO("smtpd_recipient_limit");
	$main->main_array["mime_nesting_limit"]=$sock->GET_INFO("mime_nesting_limit");
	$main->main_array["header_address_token_limit"]=$sock->GET_INFO("header_address_token_limit");
	$main->main_array["virtual_mailbox_limit"]=$sock->GET_INFO("virtual_mailbox_limit");
	
	if($main->main_array["message_size_limit"]==null){$main->main_array["message_size_limit"]=102400000;}
	if($main->main_array["virtual_mailbox_limit"]==null){$main->main_array["virtual_mailbox_limit"]=102400000;}
	if($main->main_array["default_destination_recipient_limit"]==null){$main->main_array["default_destination_recipient_limit"]=50;}
	if($main->main_array["smtpd_recipient_limit"]==null){$main->main_array["smtpd_recipient_limit"]=1000;}
	if($main->main_array["mime_nesting_limit"]==null){$main->main_array["mime_nesting_limit"]=100;}
	if($main->main_array["header_address_token_limit"]==null){$main->main_array["header_address_token_limit"]=10240;}
	
	echo "Starting......: message_size_limit={$main->main_array["message_size_limit"]}\n";
	echo "Starting......: virtual_mailbox_limit={$main->main_array["virtual_mailbox_limit"]}\n";
	echo "Starting......: default_destination_recipient_limit={$main->main_array["default_destination_recipient_limit"]}\n";
	echo "Starting......: smtpd_recipient_limit={$main->main_array["smtpd_recipient_limit"]}\n";
	echo "Starting......: mime_nesting_limit={$main->main_array["mime_nesting_limit"]}\n";
	echo "Starting......: header_address_token_limit={$main->main_array["header_address_token_limit"]}\n";
	
	system("{$GLOBALS["postconf"]} -e \"message_size_limit = {$main->main_array["message_size_limit"]}\" >/dev/null 2>&1");
	system("{$GLOBALS["postconf"]} -e \"default_destination_recipient_limit = {$main->main_array["default_destination_recipient_limit"]}\" >/dev/null 2>&1");
	system("{$GLOBALS["postconf"]} -e \"smtpd_recipient_limit = {$main->main_array["smtpd_recipient_limit"]}\" >/dev/null 2>&1");
	system("{$GLOBALS["postconf"]} -e \"mime_nesting_limit = {$main->main_array["mime_nesting_limit"]}\" >/dev/null 2>&1");
	system("{$GLOBALS["postconf"]} -e \"header_address_token_limit = {$main->main_array["header_address_token_limit"]}\" >/dev/null 2>&1");
	system("{$GLOBALS["postconf"]} -e \"virtual_mailbox_limit = {$main->main_array["virtual_mailbox_limit"]}\" >/dev/null 2>&1");
	perso_settings();
}

function inet_interfaces(){
	$sock=new sockets();
	if($sock->GET_INFO("EnablePostfixMultiInstance")==1){return;}
	$table=explode("\n",$sock->GET_INFO("PostfixBinInterfaces"));	
	if(!is_array($table)){$table[]="all";}
	
	while (list ($num, $val) = each ($table) ){
		if($val==null){continue;}
		$newarray[]=$val;
	}
	
	if(!is_array($newarray)){$newarray[]="all";}
	$finale=implode(",",$newarray);
	$finale=str_replace(',,',',',$finale);
	echo "Starting......: Postfix Listen interface(s) \"$finale\"\n";
	system("{$GLOBALS["postconf"]} -e \"inet_interfaces = $finale\" >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"artica-filter_destination_recipient_limit = 1\" >/dev/null 2>&1");
}

function MailBoxTransport(){
	$main=new maincf_multi();
	$sock=new sockets();
	$users=new usersMenus();
	$default=$main->getMailBoxTransport();
	
	system("{$GLOBALS["postconf"]} -e \"zarafa_destination_recipient_limit = 1\" >/dev/null 2>&1");
	system("{$GLOBALS["postconf"]} -e \"mailbox_transport = $default\" >/dev/null 2>&1");
	
	if(preg_match("#lmtp:(.+?):[0-9]+#",$default)){
		if(!$users->ZARAFA_INSTALLED){
			if(!$users->cyrus_imapd_installed){
				disable_lmtp_sasl();
				return null;
			}
			echo "Starting......: Postfix LMTP is enabled $default\n";
			$ldap=new clladp();
			$CyrusLMTPListen=trim($sock->GET_INFO("CyrusLMTPListen"));
			$cyruspass=$ldap->CyrusPassword();
			@file_put_contents("/etc/postfix/lmtpauth","$CyrusLMTPListen\tcyrus:$cyruspass");
			shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/lmtpauth");
			system("{$GLOBALS["postconf"]} -e \"lmtp_sasl_auth_enable = yes\" >/dev/null 2>&1");
			system("{$GLOBALS["postconf"]} -e \"lmtp_sasl_password_maps = hash:/etc/postfix/lmtpauth\" >/dev/null 2>&1");
			system("{$GLOBALS["postconf"]} -e \"lmtp_sasl_mechanism_filter = plain, login\" >/dev/null 2>&1");
			system("{$GLOBALS["postconf"]} -e \"lmtp_sasl_security_options =\" >/dev/null 2>&1");			
		}
	}else{
		disable_lmtp_sasl();
	}
	
	
	}
	
function disable_lmtp_sasl(){
	echo "Starting......: Postfix LMTP is disabled\n";
	system("{$GLOBALS["postconf"]} -e \"lmtp_sasl_auth_enable = no\" >/dev/null 2>&1");
			
}
	
function disable_smtp_sasl(){
	shell_exec("{$GLOBALS["postconf"]} -e \"smtp_sasl_password_maps =\" >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"smtp_sasl_auth_enable =no\" >/dev/null 2>&1");	
	
}

function perso_settings(){
	$main=new main_perso();
	if(!is_array($main->main_array)){
		echo "Starting......: Postfix no main.cf tokens defined by admin\n";
		return;
	}
	while (list ($key, $array) = each ($main->main_array) ){
		echo "Starting......: Postfix Added by administrator: $key = {$array["VALUE"]}\n";
		system("{$GLOBALS["postconf"]} -e \"$key = {$array["VALUE"]}\" >/dev/null 2>&1");
	}
	
	if($GLOBALS["RELOAD"]){exec("{$GLOBALS["postfix"]} reload");}
	
}

function luser_relay(){
	$sock=new sockets();
	$luser_relay=trim($sock->GET_INFO("luser_relay"));
	if($luser_relay==null){
		echo "Starting......: Postfix no Unknown user recipient set\n";
		system("{$GLOBALS["postconf"]} -e \"luser_relay = \" >/dev/null 2>&1");
		return;
	}
	echo "Starting......: Postfix Unknown user set to $luser_relay\n";
	system("{$GLOBALS["postconf"]} -e \"luser_relay = $luser_relay\" >/dev/null 2>&1");
	system("{$GLOBALS["postconf"]} -e \"local_recipient_maps =\" >/dev/null 2>&1");
	if($GLOBALS["RELOAD"]){shell_exec("{$GLOBALS["postfix"]} reload");}
	
}




?>