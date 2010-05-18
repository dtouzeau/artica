<?php

	include_once(dirname(__FILE__) .'/class.kavmilterd.inc');
	include_once(dirname(__FILE__) .'/logs.inc');
	
	
	
if(function_exists("DefineSessions")){DefineSessions();}
if(function_exists("CheckSessions")){CheckSessions();}
if(!$GLOBALS["KAV4PROXY_NOSESSION"]){session_start();}
//64-out-of-office.png
class usersMenus {
	var $TreePage=array("domains.edit.group.php"=>true,"samba.index.php"=>true);
	var $menus;
	var $main_left_menus=array();
	var $menusright;
	var $toplinks;
	var $priv;
	var $MAIN_CONSOLE_PORT=9000;
	var $EnablePerUserRemoteAddressBook=0;
	var $dhcp_installed=false;
	var $AllowChangeDomains=false;
	var $AsSystemAdministrator=false;
	var $AsOrgStorageAdministrator=false;
	var $AllowXapianDownload=false;
	var $AllowAddGroup=false;
	var $AllowChangeKav=false;
	var $AllowChangeKas=false;
	var $AllowEditAsWbl=false;
	var $AllowEditOuSecurity=false;	
	var $AsArticaAdministrator=false;
	var $AsSambaAdministrator=false;
	var $AsMailManAdministrator=false;
	var $AsPostfixAdministrator=false;
	var $AsSquidAdministrator=false;
	var $AsMailBoxAdministrator=false;
	var $AsDansGuardianGroupRule=false;
	var $AsDnsAdministrator=false;
	var $AsQuarantineAdministrator=false;
	var $AllowFetchMails=false;
	var $AllowSenderCanonical=false;
	var $AllowChangeUserPassword=false;
	var $AllowDansGuardianBanned=false;
	var $AllowChangeUserKas=false;
	var $AllowEditAliases=false;
	var $AllowEmailing=false;
	var $AllowOpenVPN=false;
	var $AsAnAdministratorGeneric=false;
	var $AllowAddUsers=false;
	var $AllowViewStatistics=false;
	var $AllowChangeAntiSpamSettings=false;
	var $AsOrgAdmin=false;
	var $uid=null;
	var $groupid=-900;
	var $IptablesEnabled;
	var $KAVMILTER_ENABLED;
	var $lighttpd_installed;
	var $spamassassin_installed=false;
	var $spamassassin_version=0;
	var $fetchmail_configured=false;
	var $LOCAL_SID;
	var $EnableFetchmail=0;
	var $EnableFDMFetch=0;
	var $P3ScanEnabled=0;
	var $RoundCubeHTTPEngineEnabled=0;
	var $roundcube_intversion=0;
	var $roundcube_plugins=null;
	var $template_users_menus_h;
	var $template_user_addr;
	var $disks_size=null;
	var $cpumhz=null;
	var $preload_installed=false;
	var $smbmount_installed=false;
	var $collectd_installed=false;
	var $EnableVirtualDomainsInMailBoxes=false;
	var $SIMPLE_GROUPEWARE_INSTALLED=false;
	var $APP_ATOPENMAIL_INSTALLED=false;
	var $CLAMD_DATABASE_PATH="";
	var $APP_ATOPENMAIL_VERSION="";
	var $COLLECTD_RRD=array();
	var $LoadModulesEnabledParsed=false;
	var $dar_installed=false;
	var $GNARWL_INSTALLED=false;
	var $AllowChangeMailBoxRules=false;
	var $cyrus_ldap_admin_password="";
	var $CLAMD_CONF_PATH="";
	var $PFLOGSUMM_INSTALLED=false;
	var $maillog_path;
	var $spamassassin_ipcountry=false;
	var $CUPS_INSTALLED=false;
	var $OBM2_INSTALLED=false;
	var $SARG_INSTALLED=false;
	var $OPENGOO_INSTALLED=false;
	var $APACHE_GROUPWARE_INSTALLED=false;
	var $POSTFIX_QUEUE=array();
	var $LinuxDistriCode=null;
	var $LinuxDistriFullName=null;
	var $APACHE_INSTALLED=false;
	var $cyrus_sync_installed=false;
	var $AllowManageOwnComputers=false;
	var $POLICYD_WEIGHT_PORT;
	var $CRYPTSETUP_INSTALLED=false;
	var $ZABBIX_INSTALLED=true;
	var $HAMACHI_INSTALLED=true;
	var $phpldapadmin_installed=false;
	var $phpmyadmin_installed=false;
	/* This licence must be used Only with Artica-for postfix */
	var $ChartLicence="D1XFWGMEW9L.HSK5T4Q79KLYCK07EK";
	/*you cannot use it without any authorizations of maani.us.
	this licence is Copyright 2004-2007, maani.us
	For more information, see
	http://www.maani.us/xml_charts/index.php?menu=License
	*/
	
	var $ChangeAutoInterface=null;
	var $mysql_password='';
	var $mysql_admin='root';
	var $mysql_server='127.0.0.1';
	var $ARTICA_FILTER_QUEUE_PATH;
	var $ARTICA_FILTER_MAXSUBQUEUE;
	var $POSTFIX_MAILDROP_PATH;
	//-------------------------------- products info
	var $GLOBAL_CONFIG;
	var $AutomaticConfig=false;
	var $fetchmail_installed=false;
	var $cyrus_imapd_installed=false;
	var $cyrus_lmtp_path='/var/run/cyrus/socket/lmtp';
	var $kas_installed=false;
	var $aveserver_installed;
	var $rrdtool_installed;
	var $procmail_installed=false;	
	var $dnsmasq_installed=false;
	var $mysql_installed=false;
	var $mailgraph_installed=false;
	var $mailgraph_virus_database;
	var $mailgraph_postfix_database;
	var $queuegraph_installed=false;
	var $yorel_installed=false;
	var $roundcube_installed=false;
	var $awstats_installed=false;
	var $mysql_enabled=true;
	var $aveserver_pattern_date="00000000;0000";
	var $POSTIFX_STATUS="-1;0.0.0;";
	var $KAS_STATUS="-1;0.0.0;";
	var $CYRUS_STATUS="-1;0.0.0;";
	var $KAV_STATUS="-1;0.0.0;";
	var $FETHMAIL_STATUS="-1;0.0.0;";
	var $ARTICA_ROOT_PATH;
	var $ARTICA_VERSION;
	var $ARTICA_FILTER_STATUS=false;
	var $ARTICA_POLICY_STATUS=false;
	var $EMAILRELAY_STATUS=false;
	var $EMAILRELAY_INSTALLED=false;
	var $BOGOFILTER_INSTALLED=false;
	var $KAV_MILTER_INSTALLED=false;
	var $MAILMAN_INSTALLED=false;
	var $POSTFIX_INSTALLED=false;
	var $POSTFIX_VERSION="0.0.0";
	var $crossroads_installed=true;
	var $fetchmail_daemon_logs="";
	var $KAVMILTER_PID;
	var $hotwayd_installed;
	var $SQUID_INSTALLED=false;
	var $SQUID_BIN_PATH="";
	var $VMWARE_HOST=false;
	var $KAV4PROXY_INSTALLED=false;	
	var $KAV4PROXY_PID;
	var $KAV4PROXY_MEMORY=0;
	var $KAV4PROXY_PATTERN="";
	var $KAV4PROXY_VERSION="0.0.0";
	var $DANSGUARDIAN_INSTALLED=false;
	var $AMAVIS_INSTALLED=false;
	var $AMAVISD_VERSION=null;
	var $DANSGUARDIAN_VERSION=null;
	var $PUREFTP_INSTALLED=false;
	var $PUREFTP_USERS=false;
	var $MILTERGREYLIST_INSTALLED=false;
	var $MAILFROMD_INSTALLED=false;
	var $SQUID_PID;	
	var $fqdn;
	var $hostname;
	var $mysql_port;
	var $SMTP_LEVEL;
	var $ArticaFilterEnabled;
	var $AmavisFilterEnabled;
	var $MilterGreyListEnabled;
	var $EnableMilterSpyDaemon=0;
	var $SQLGreyEnabled;
	var $KasxFilterEnabled;
	var $OBM_INSTALLED;
	var $OBMEnabled;
	var $NTPD_INSTALLED;
	var $NTPDEnabled;
	var $MailFromdEnabled;
	var $IPTABLES_INSTALLED;
	var $ApacheArticaEnabled;
	var $SAMBA_INSTALLED;
	var $WINBINDD_INSTALLED;
	var $SAMBA_VERSION;
	var $GeoIPDat_Path;
	var $BadMysqlPassword=0;
	var $POSTFIX_LDAP_COMPLIANCE=false;
	var $MAILFROMD_SOCKET="";
	var $CLAMAV_INSTALLED=false;
	var $CLAMD_INSTALLED=false;
	var $CLAMSCAN_INSTALLED=false;
	var $CLAMAV_SOCKET="";
	var $ClamavMilterEnabled=0;
	var $CLAMAV_MILTER_SOCKET;
	var $CLAMAV_MILTER_INSTALLED=false;
	var $SpamAssMilterEnabled=0;
	var $SPAMASS_MILTER_INSTALLED=false;
	var $SPAMASS_MILTER_SOCKET="";
	var $roundcube_folder="";
	var $phpcgi;
	var $razor_installed=false;
	var $pyzor_installed=false;
	var $razor_config="";
	var $spfmilterEnabled=0;
	var $spfmilter_installed=false;
	var $MIMEDEFANG_INSTALLED=false;
	var $netbiosname;
	var $MIMEDEFANG_SOCKET="";
	var $MimeDefangEnabled;
	var $GRAPHDEFANG_INSTALLED=false;
	var $MHONARC_INSTALLED=alse;
	var $SQUID_VERSION;
	var $SQUID_LDAP_AUTH;
	var $SQUID_NTLM_AUTH;
	var $SQUID_BIN_VERSION=0;
	var $stunnel4_installed=false;
	var $DkimFilterEnabled=0;
	var $DKIMFILTER_INSTALLED=false;
	var $DKIMFILTER_SOCKET="";
	var $blkid_installed=false;
	var $nmap_installed=false;
	var $usb_list=array();
	var $roundcube_mysql_sources;
	var $roundcube_web_folder;
	var $KAV4SAMBA_INSTALLED=false;
	var $KAV4SAMBA_VFS="";
	var $SAMBA_MYSQL_AUDIT=false;
	var $awstats_www_path="";
	var $SQUID_ICAP_ENABLED=false;
	var $LIGHTTPD_LDAP_AUTH=false;
	var $BIND9_INSTALLED=false;
	var $bindrrd_installed=false;
	var $fdm_installed=false;
	var $fdm_cache=false;
	var $p3scan_installed=false;
	var $syslogng_installed=false;
	var $AsDebianSystem=false;
	var $sTunnel4enabled=0;
	var $MailArchiverEnabled=0;
	var $roundcube_version="0.0.0";
	var $EnableMilterBogom=0;
	var $openldap_installed=false;
	var $EnableMysqlFeatures=1;
	var $MILTER_SPY_INSTALLED=false;
	var $EnableCollectdDaemon=0;
	var $EnableAmavisDaemon=0;
	var $imapsync_installed=false;
	var $mailsync_installed=false;
	var $ISOQLOG_INSTALLED=false;
	var $DOTCLEAR_INSTALLED=false;
	var $JCHECKMAIL_INSTALLED=false;
	var $zip_installed=false;
	var $JCheckMailEnabled=0;
	var $JCHECKMAIL_SOCKET;
	var $EnableAmavisBackup=0;
	var $OPENLDAP_DYNLIST=false;
	var $EnableRemoteAddressBook=0;
	var $SLPAD_LOCKED=false;
	var $ALTERMIME_INSTALLED=false;
	var $POMMO_INSTALLED=false;
	var $C_ICAP_INSTALLED=false;
	var $MILTERGREYLIST_SOCKET="";
	var $OPENVPN_INSTALLED=false;
	var $spamassassin_conf_path="";
	var $EnableNonEncryptedLdapSession=1;
	var $SMARTMONTOOLS_INSTALLED=false;
	var $XAPIAN_PHP_INSTALLED=false;
	var $INSTANSEARCH_ENABLE=false;
	var $JOOMLA_INSTALLED=false;
	var $DSTAT_INSTALLED=false;
	var $SUGARCRM_INSTALLED=false;
	var $GNUPLOT_PNG=false;
	var $IOSTAT_INSTALLED=false;
	var $IOSTAT_ARRAY=array();
	var $PHP_BIN_PATH=null;	
	var $EXEC_NICE=null;
	var $spamassassin_bin_path=null;
	var $cyr_partition_default=null;
	var $cyr_config_directory=null;
	var $smbclient_installed=false;
	var $cyrus_murder_installed=false;
	var $FETCHMAIL_VERSION=null;
	var $cyrus_sync_client_path=null;
	var $ctl_mboxlist=null;
	var $CURL_PATH=null;
	var $NMAP_PATH=null;
	var $msmtp_installed=false;
	var $nmap_version=null;
	var $autofs_installed=false;
	var $APACHE_PORT=null;
	var $NFS_SERVER_INSTALLED=false;
	var $LVM_INSTALLED=false;
	var $MYSQL_NDB_MGMD_INSTALLED=false;
	var $CPU_NUMBER=0;
	var $LOAD_AVERAGE=0;
	var $winexe_installed=false;
	var $OCSI_INSTALLED=false;
	var $ASSP_INSTALLED=false;
	var $MEM_HIGER_1G=true;
	var $LMB_LUNDIMATIN_INSTALLED=false;
	var $APACHE_MODE_VHOSTS_LDAP=false;
	var $TCP_ARRAY=array();
	var $POWER_DNS_INSTALLED=false;
	var $mysqld_version=0;
	var $GLUSTER_INSTALLED=false;
	var $SLAPD_CONF_PATH;
	var $cyrus_squatter_exists=false;
	var $cyrus_ipurge_exists=false;
	var $SCANNED_ONLY_INSTALLED=false;
	var $GROUPOFFICE_INSTALLED=false;
	var $POSTFIX_MULTI=false;
	var $AsOrgPostfixAdministrator=false;
	var $ASSP_VERSION=0;
	var $MEM_TOTAL_INSTALLEE=0;
	var $KASPERSKY_SMTP_APPLIANCE=false;
	var $POSTFILTER_INSTALLED=false;
	var $VMWARE_TOOLS_INSTALLED=false;
	var $SQUID_NTLM_ENABLED=false;
	var $ZARAFA_INSTALLED=false;
	var $SQUIDGUARD_INSTALLED=false;
	var $SQUIDGUARD_BINVER=0;
	var $SQUIDGUARD_BIN_PATH="";
	
	function usersMenus($priv=null,$nobuild=0,$hostname=null){

		


			$tpl=new templates(null,null,null,1);
			$settings=dirname(__FILE__).'/settings.inc';
			if(file_exists($settings)){
				@include($settings);
				$this->GLOBAL_CONFIG=$_GLOBAL;
				if(!is_array($this->GLOBAL_CONFIG)){return false;}
			}else{
				die("Unable to stat $settings");
			}
			$this->cyrus_ldap_admin_password=$_GLOBAL["cyrus_ldap_admin_password"];
			$this->AutomaticConfig=$_GLOBAL["AutomaticConfig"];
			$this->MAIN_CONSOLE_PORT=$_GLOBAL["MAIN_CONSOLE_PORT"];
			$this->imapsync_installed=$_GLOBAL["imapsync_installed"];
			$this->fetchmail_installed=$_GLOBAL["fetchmail_installed"];
			$this->cyrus_imapd_installed=$_GLOBAL["cyrus_imapd_installed"];
			$this->cyrus_lmtp_path=$_GLOBAL["cyrus_lmtp_path"];
			$this->cyr_partition_default=$_GLOBAL["cyr_partition_default"];
			$this->cyr_config_directory=$_GLOBAL["cyr_config_directory"];
			$this->kas_installed=$_GLOBAL["kas_installed"];
			$this->aveserver_installed=$_GLOBAL["aveserver_installed"];
			$this->rrdtool_installed=$_GLOBAL["rrdtool_installed"];
			$this->procmail_installed=$_GLOBAL["procmail_installed"];
			$this->mysql_installed=$_GLOBAL["mysql_installed"];
			$this->mailgraph_installed=$_GLOBAL["mailgraph_installed"];
			$this->mailgraph_virus_database=$_GLOBAL["mailgraph_virus_database"];
			$this->mailgraph_postfix_database=$_GLOBAL["mailgraph_postfix_database"];
			$this->queuegraph_installed=$_GLOBAL["queuegraph_installed"];
			$this->yorel_installed=$_GLOBAL["yorel_installed"];
			$this->roundcube_installed=$_GLOBAL["roundcube_installed"];
			$this->ChangeAutoInterface=$_GLOBAL["ChangeAutoInterface"];
			$this->aveserver_pattern_date=$_GLOBAL["aveserver_pattern_date"];
			$this->POSTIFX_STATUS=$_GLOBAL["POSTFIX_STATUS"];
			$this->KAS_STATUS=$_GLOBAL["KAS_STATUS"];
			$this->CYRUS_STATUS=$_GLOBAL["CYRUS_STATUS"];
			$this->KAV_STATUS=$_GLOBAL["KAV_STATUS"];
			$this->FETCHMAIL_STATUS=$_GLOBAL["FETCHMAIL_STATUS"];
			$this->awstats_installed=$_GLOBAL["awstats_installed"];
			$this->BOGOFILTER_INSTALLED=$_GLOBAL["BOGOFILTER_INSTALLED"];
			$this->MAILMAN_INSTALLED=$_GLOBAL["MAILMAN_INSTALLED"];
			$this->KAV_MILTER_INSTALLED=$_GLOBAL["kavmilter_installed"];
			$this->fetchmail_daemon_logs=$_GLOBAL["fetchmail_daemon_logs"];
			$this->ARTICA_ROOT_PATH=$_GLOBAL["ARTICA_ROOT_PATH"];
			$this->ARTICA_FILTER_STATUS=$_GLOBAL["ARTICA_FILTER_STATUS"];
			$this->ARTICA_POLICY_STATUS=$_GLOBAL["ARTICA_POLICY_STATUS"];
			$this->ARTICA_VERSION=$_GLOBAL["ARTICA_VERSION"];
			$this->hotwayd_installed=$_GLOBAL["hotwayd_installed"];
			$this->POSTFIX_INSTALLED=$_GLOBAL["POSTFIX_INSTALLED"];
			$this->EMAILRELAY_STATUS=$_GLOBAL["EMAILRELAY_STATUS"];
			$this->EMAILRELAY_INSTALLED=$_GLOBAL["EMAILRELAY_INSTALLED"];
			$this->KAVMILTER_PID=$_GLOBAL["KAVMILTER_PID"];
			$this->SQUID_INSTALLED=$_GLOBAL["SQUID_INSTALLED"];
			$this->KAV4PROXY_INSTALLED=$_GLOBAL["KAV4PROXY_INSTALLED"];
			$this->SQUID_PID=$_GLOBAL["SQUID_PID"];
			$this->SQUID_NTLM_AUTH=$_GLOBAL["SQUID_NTLM_AUTH"];
			$this->SQUID_BIN_VERSION=$_GLOBAL["SQUID_BIN_VERSION"];
			$this->KAV4PROXY_MEMORY=$_GLOBAL["KAV4PROXY_MEMORY"];
			$this->KAV4PROXY_PATTERN=$_GLOBAL["KAV4PROXY_PATTERN"];
			$this->KAV4PROXY_VERSION=$_GLOBAL["KAV4PROXY_VERSION"];
			$this->hostname=$_GLOBAL["fixed_hostname"];
			$this->fqdn=$_GLOBAL["fqdn_hostname"];
			$this->DANSGUARDIAN_INSTALLED=$_GLOBAL["DANSGUARDIAN_INSTALLED"];
			$this->KAV4PROXY_PID=$_GLOBAL["KAV4PROXY_PID"];
			$this->dnsmasq_installed=$_GLOBAL["dnsmasq_installed"];
			$this->PUREFTP_INSTALLED=$_GLOBAL["PUREFTP_INSTALLED"];
			$this->uid=$_SESSION["uid"];
			$this->groupid=$_SESSION["groupid"];
			$this->crossroads_installed=$_GLOBAL["crossroads_installed"];
			$this->AMAVIS_INSTALLED=$_GLOBAL["AMAVIS_INSTALLED"];
			$this->mysql_port=$_GLOBAL["mysql_port"];
			$this->MILTERGREYLIST_INSTALLED=$_GLOBAL["MILTERGREYLIST_INSTALLED"];
			$this->mysql_password=$_GLOBAL["mysql_password"];
			$this->mysql_admin=$_GLOBAL["mysql_admin"];	
			$this->mysql_server=$_GLOBAL["mysql_server"];
			$this->mysql_enabled=$_GLOBAL["mysql_enabled"];
			$this->OBM_INSTALLED=$_GLOBAL["OBM_INSTALLED"];
			$this->NTPD_INSTALLED=$_GLOBAL["NTPD_INSTALLED"];
			$this->MAILFROMD_INSTALLED=$_GLOBAL["MAILFROMD_INSTALLED"];		
			$this->ARTICA_FILTER_QUEUE_PATH=$_GLOBAL["ARTICA_FILTER_QUEUE_PATH"];
			$this->ARTICA_FILTER_MAXSUBQUEUE=$_GLOBAL["ARTICA_FILTER_MAXSUBQUEUE"];
			$this->IPTABLES_INSTALLED=$_GLOBAL["IPTABLES_INSTALLED"];
			$this->GeoIPDat_Path=$_GLOBAL["GeoIPDat_Path"];
			$this->lighttpd_installed=$_GLOBAL["lighttpd_installed"];
			if($this->ChartLicence==null){$this->ChartLicence='D1XFWGMEW9L.HSK5T4Q79KLYCK07EK';}
			$this->SAMBA_INSTALLED=$_GLOBAL["SAMBA_INSTALLED"];
			$this->LOCAL_SID=$_GLOBAL["LOCAL_SID"];
			$this->BadMysqlPassword=$_GLOBAL["BadMysqlPassword"];
			if($this->BadMysqlPassword==null){$this->BadMysqlPassword=0;}
			$this->POSTFIX_LDAP_COMPLIANCE=$_GLOBAL["POSTFIX_LDAP_COMPLIANCE"];
			$this->MAILFROMD_SOCKET=$_GLOBAL["MAILFROMD_SOCKET"];
			$this->CLAMAV_INSTALLED=$_GLOBAL["CLAMAV_INSTALLED"];
			$this->CLAMSCAN_INSTALLED=$_GLOBAL["CLAMSCAN_INSTALLED"];
			$this->CLAMAV_SOCKET=$_GLOBAL["CLAMAV_SOCKET"];
			$this->CLAMAV_MILTER_SOCKET=$_GLOBAL["CLAMAV_MILTER_SOCKET"];
			$this->CLAMAV_MILTER_INSTALLED=$_GLOBAL["CLAMAV_MILTER_INSTALLED"];
			$this->SPAMASS_MILTER_INSTALLED=$_GLOBAL["SPAMASS_MILTER_INSTALLED"];
			$this->SPAMASS_MILTER_SOCKET=$_GLOBAL["SPAMASS_MILTER_SOCKET"];
			$this->roundcube_folder=$_GLOBAL["roundcube_folder"];
			$this->phpcgi=$_GLOBAL["phpcgi"];
			$this->razor_config=$_GLOBAL["razor_config"];
			$this->razor_installed=$_GLOBAL["razor_installed"];
			$this->pyzor_installed=$_GLOBAL["pyzor_installed"];
			$this->spfmilter_installed=$_GLOBAL["spfmilter_installed"];
			$this->netbiosname=$_GLOBAL["netbiosname"];
			$this->MIMEDEFANG_INSTALLED=$_GLOBAL["MIMEDEFANG_INSTALLED"];
			$this->MIMEDEFANG_SOCKET=$_GLOBAL["MIMEDEFANG_SOCKET"];
			$this->GRAPHDEFANG_INSTALLED=$_GLOBAL["GRAPHDEFANG_INSTALLED"];
			$this->MHONARC_INSTALLED=$_GLOBAL["MHONARC_INSTALLED"];
			$this->MHONARC_VERSION=$_GLOBAL["MHONARC_VERSION"];
			$this->spamassassin_installed=$_GLOBAL["spamassassin_installed"];
			$this->spamassassin_version=$_GLOBAL["spamassassin_version"];
			$this->BOGOFILTER_INSTALLED=$_GLOBAL["bogofilter_installed"];
			$this->SQUID_VERSION=$_GLOBAL["SQUID_VERSION"];
			$this->SQUID_LDAP_AUTH=$_GLOBAL["SQUID_LDAP_AUTH"];
			$this->stunnel4_installed=$_GLOBAL["stunnel4_installed"];
			$this->DKIMFILTER_INSTALLED=$_GLOBAL["DKIMFILTER_INSTALLED"];
			$this->DKIMFILTER_SOCKET=$_GLOBAL["DKIMFILTER_SOCKET"];
			$this->POSTFIX_VERSION=$_GLOBAL["POSTFIX_VERSION"];
			$this->blkid_installed=$_GLOBAL["blkid_installed"];
			$this->usb_list=$_GLOBAL["usb_list"];
			$this->nmap_installed=$_GLOBAL["nmap_installed"];
			$this->roundcube_mysql_sources=$_GLOBAL["roundcube_mysql_sources"];
			$this->roundcube_web_folder=$_GLOBAL["roundcube_web_folder"];
			$this->fetchmail_configured=$_GLOBAL["fetchmail_configured"];
			$this->KAV4SAMBA_INSTALLED=$_GLOBAL["KAV4SAMBA_INSTALLED"];
			$this->KAV4SAMBA_VFS=$_GLOBAL["KAV4SAMBA_VFS"];
			$this->SAMBA_MYSQL_AUDIT=$_GLOBAL["SAMBA_MYSQL_AUDIT"];
			$this->awstats_www_path=$_GLOBAL["awstats_www_path"];
			$this->SQUID_ICAP_ENABLED=$_GLOBAL["SQUID_ICAP_ENABLED"];
			$this->LIGHTTPD_LDAP_AUTH=$_GLOBAL["LIGHTTPD_LDAP_AUTH"];
			
			$this->bindrrd_installed=$_GLOBAL["bindrrd_installed"];
			$this->fdm_installed=$_GLOBAL["fdm_installed"];
			$this->fdm_cache=$_GLOBAL["fdm_cache"];
			$this->p3scan_installed=$_GLOBAL["p3scan_installed"];
			$this->syslogng_installed=$_GLOBAL["syslogng_installed"];
			$this->AsDebianSystem=$_GLOBAL["AsDebianSystem"];
			$this->disks_size=$_GLOBAL["disks_size"];
			if(trim($this->hostname)==null){$this->hostname=$this->fqdn;}
			$this->cpumhz=$_GLOBAL["cpumhz"];
			$this->preload_installed=$_GLOBAL["preload_installed"];
			$this->POSTFIX_MAILDROP_PATH=$_GLOBAL["POSTFIX_MAILDROP_PATH"];
			$this->smbmount_installed=$_GLOBAL["smbmount_installed"];
			$this->PUREFTP_USERS=$_GLOBAL["PUREFTP_USERS"];
			$this->roundcube_version=$_GLOBAL["roundcube_version"];
			$this->openldap_installed=$_GLOBAL["openldap_installed"];
			$this->collectd_installed=$_GLOBAL["collectd_installed"];
			$this->COLLECTD_RRD=$_GLOBAL["COLLECTD_RRD"];
			$this->EnableVirtualDomainsInMailBoxes=$_GLOBAL["EnableVirtualDomainsInMailBoxes"];
			$this->SIMPLE_GROUPEWARE_INSTALLED=$_GLOBAL["SIMPLE_GROUPEWARE_INSTALLED"];
			$this->APP_ATOPENMAIL_INSTALLED=$_GLOBAL["APP_ATOPENMAIL_INSTALLED"];
			$this->APP_ATOPENMAIL_VERSION=$_GLOBAL["APP_ATOPENMAIL_VERSION"];
			$this->MILTER_SPY_INSTALLED=$_GLOBAL["MILTER_SPY_INSTALLED"];
			$this->mailsync_installed=$_GLOBAL["mailsync_installed"];
			$this->ISOQLOG_INSTALLED=$_GLOBAL["ISOQLOG_INSTALLED"];
			$this->DOTCLEAR_INSTALLED=$_GLOBAL["DOTCLEAR_INSTALLED"];
			$this->JCHECKMAIL_INSTALLED=$_GLOBAL["JCHECKMAIL_INSTALLED"];
			$this->JCHECKMAIL_SOCKET=$_GLOBAL["JCHECKMAIL_SOCKET"];
			$this->zip_installed=$_GLOBAL["zip_installed"];
			$this->BIND9_INSTALLED=$_GLOBAL["BIND9_INSTALLED"];
			$this->OPENLDAP_DYNLIST=$_GLOBAL["OPENLDAP_DYNLIST"];
			$this->SLPAD_LOCKED=$_GLOBAL["SLPAD_LOCKED"];
			$this->dhcp_installed=$_GLOBAL["dhcp_installed"];
			$this->ALTERMIME_INSTALLED=$_GLOBAL["ALTERMIME_INSTALLED"];
			$this->POMMO_INSTALLED=$_GLOBAL["POMMO_INSTALLED"];
			$this->dar_installed=$_GLOBAL["dar_installed"];
			$this->SAMBA_VERSION=$_GLOBAL["SAMBA_VERSION"];
			$this->C_ICAP_INSTALLED=$_GLOBAL["C_ICAP_INSTALLED"];
			$this->MILTERGREYLIST_SOCKET=$_GLOBAL["MILTERGREYLIST_SOCKET"];
			$this->OPENVPN_INSTALLED=$_GLOBAL["OPENVPN_INSTALLED"];
			$this->GNARWL_INSTALLED=$_GLOBAL["GNARWL_INSTALLED"];
			$this->CLAMD_CONF_PATH=$_GLOBAL["CLAMD_CONF_PATH"];
			$this->CLAMD_DATABASE_PATH=$_GLOBAL["CLAMD_DATABASE_PATH"];
			$this->PFLOGSUMM_INSTALLED=$_GLOBAL["PFLOGSUMM_INSTALLED"];
			$this->maillog_path=$_GLOBAL["maillog_path"];
			$this->spamassassin_ipcountry=$_GLOBAL["spamassassin_ipcountry"];
			$this->spamassassin_conf_path=$_GLOBAL["spamassassin_conf_path"];
			$this->CUPS_INSTALLED=$_GLOBAL["CUPS_INSTALLED"];
			$this->OBM2_INSTALLED=$_GLOBAL["OBM2_INSTALLED"];
			$this->SMARTMONTOOLS_INSTALLED=$_GLOBAL["SMARTMONTOOLS_INSTALLED"];
			$this->SARG_INSTALLED=$_GLOBAL["SARG_INSTALLED"];
			$this->XAPIAN_PHP_INSTALLED=$_GLOBAL["XAPIAN_PHP_INSTALLED"];
			$this->OPENGOO_INSTALLED=$_GLOBAL["OPENGOO_INSTALLED"];
			$this->JOOMLA_INSTALLED=$_GLOBAL["JOOMLA_INSTALLED"];
			$this->APACHE_GROUPWARE_INSTALLED=$_GLOBAL["APACHE_GROUPWARE_INSTALLED"];
			$this->DSTAT_INSTALLED=$_GLOBAL["DSTAT_INSTALLED"];
			$this->SUGARCRM_INSTALLED=$_GLOBAL["SUGARCRM_INSTALLED"];
			$this->GNUPLOT_PNG=$_GLOBAL["GNUPLOT_PNG"];
			$this->IOSTAT_INSTALLED=$_GLOBAL["IOSTAT_INSTALLED"];
			if(is_array($_GLOBAL["IOSTAT_ARRAY"])){
				$this->IOSTAT_ARRAY=$_GLOBAL["IOSTAT_ARRAY"];
			}
			$this->POSTFIX_QUEUE=$_GLOBAL["POSTFIX_QUEUE"];
			$this->PHP_BIN_PATH=$_GLOBAL["PHP_BIN_PATH"];
			$this->EXEC_NICE=$_GLOBAL["EXEC_NICE"];
			$this->spamassassin_bin_path=$_GLOBAL["spamassassin_bin_path"];
			$this->LinuxDistriCode=$_GLOBAL["LinuxDistriCode"];
			$this->LinuxDistriFullName=$_GLOBAL["LinuxDistriFullName"];
			$this->APACHE_INSTALLED=$_GLOBAL["APACHE_INSTALLED"];
			$this->roundcube_intversion=$_GLOBAL["roundcube_intversion"];
			$this->roundcube_plugins=$_GLOBAL["roundcube_plugins"];
			$this->smbclient_installed=$_GLOBAL["smbclient_installed"];
			$this->AMAVISD_VERSION=$_GLOBAL["AMAVISD_VERSION"];
			$this->cyrus_murder_installed=$_GLOBAL["cyrus_murder_installed"];
			$this->cyrus_sync_installed=$_GLOBAL["cyrus_sync_installed"];
			$this->FETCHMAIL_VERSION=$_GLOBAL["FETCHMAIL_VERSION"];
			$this->cyrus_sync_client_path=$_GLOBAL["cyrus_sync_client_path"];
			$this->ctl_mboxlist=$_GLOBAL["ctl_mboxlist"];
			$this->CURL_PATH=$_GLOBAL["CURL_PATH"];
			$this->DANSGUARDIAN_VERSION=$_GLOBAL["DANSGUARDIAN_VERSION"];
			$this->NMAP_PATH=$_GLOBAL["nmap_path"];
			$this->msmtp_installed=$_GLOBAL["msmtp_installed"];
			$this->nmap_version=$_GLOBAL["nmap_version"];
			$this->autofs_installed=$_GLOBAL["autofs_installed"];
			$this->APACHE_PORT=$_GLOBAL["APACHE_PORT"];
			$this->NFS_SERVER_INSTALLED=$_GLOBAL["NFS_SERVER_INTSALLED"];
			$this->LVM_INSTALLED=$_GLOBAL["LVM_INSTALLED"];
			$this->MYSQL_NDB_MGMD_INSTALLED=$_GLOBAL["MYSQL_NDB_MGMD_INSTALLED"];
			$this->POLICYD_WEIGHT_PORT=$_GLOBAL["POLICYD_WEIGHT_PORT"];
			$this->winexe_installed=$_GLOBAL["winexe_installed"];
			$this->OCSI_INSTALLED=$_GLOBAL["OCSI_INSTALLED"];
			$this->ASSP_INSTALLED=$_GLOBAL["ASSP_INSTALLED"];
			$this->MEM_HIGER_1G=$_GLOBAL["MEM_HIGER_1G"];
			$this->LMB_LUNDIMATIN_INSTALLED=$_GLOBAL["LMB_LUNDIMATIN_INSTALLED"];
			$this->APACHE_MODE_VHOSTS_LDAP=$_GLOBAL["APACHE_MODE_VHOSTS_LDAP"];
			$this->TCP_ARRAY=$_GLOBAL["TCP_ARRAY"];
			$this->POWER_DNS_INSTALLED=$_GLOBAL["POWER_DNS_INSTALLED"];
			$this->CLAMD_INSTALLED=$_GLOBAL["CLAMD_INSTALLED"];
			$this->mysqld_version=$_GLOBAL["mysqld_version"];
			$this->GLUSTER_INSTALLED=$_GLOBAL["GLUSTER_INSTALLED"];
			$this->SLAPD_CONF_PATH=$_GLOBAL["SLAPD_CONF_PATH"];
			$this->cyrus_squatter_exists=$_GLOBAL["cyrus_squatter_exists"];
			$this->SCANNED_ONLY_INSTALLED=$_GLOBAL["SCANNED_ONLY_INSTALLED"];
			$this->CRYPTSETUP_INSTALLED=$_GLOBAL["CRYPTSETUP_INSTALLED"];
			$this->ZABBIX_INSTALLED=$_GLOBAL["ZABBIX_INSTALLED"];
			$this->GROUPOFFICE_INSTALLED=$_GLOBAL["GROUPOFFICE_INSTALLED"];
			$this->cyrus_ipurge_exists=$_GLOBAL["cyrus_ipurge_exists"];
			$this->POSTFIX_MULTI=$this->postfixmutli();
			$this->ASSP_VERSION=$_GLOBAL["ASSP_VERSION"];
			$this->HAMACHI_INSTALLED=$_GLOBAL["HAMACHI_INSTALLED"];
			$this->MEM_TOTAL_INSTALLEE=$_GLOBAL["MEM_TOTAL_INSTALLEE"]; // 716800
			$this->KASPERSKY_SMTP_APPLIANCE=$_GLOBAL["KASPERSKY_SMTP_APPLIANCE"];
			$this->POSTFILTER_INSTALLED=$_GLOBAL["POSTFILTER_INSTALLED"];
			$this->VMWARE_HOST=$_GLOBAL["VMWARE_HOST"];
			$this->VMWARE_TOOLS_INSTALLED=$_GLOBAL["VMWARE_TOOLS_INSTALLED"];
			$this->phpldapadmin_installed=$_GLOBAL["phpldapadmin_installed"];
			$this->phpmyadmin_installed=$_GLOBAL["phpmyadmin_installed"];			
			$this->SQUID_NTLM_ENABLED=$_GLOBAL["SQUID_NTLM_ENABLED"];
			$this->WINBINDD_INSTALLED=$_GLOBAL["WINBINDD_INSTALLED"];
			$this->ZARAFA_INSTALLED=$_GLOBAL["ZARAFA_INSTALLED"];
			$this->SQUIDGUARD_INSTALLED=$_GLOBAL["SQUIDGUARD_INSTALLED"];
			$this->SQUIDGUARD_BINVER=$_GLOBAL["SQUIDGUARD_BINVER"];
			$this->SQUIDGUARD_BIN_PATH=$_GLOBAL["SQUIDGUARD_BIN_PATH"];
			$this->SQUID_BIN_PATH=$_GLOBAL["SQUID_BIN_PATH"];
			
			if($this->SAMBA_INSTALLED){
				if($this->LOCAL_SID==null){
					$sock=new sockets();
					$this->LOCAL_SID=$sock->getfile('GetLocalSid');	
				}
			}
			
			$this->CPU_NUMBER=$_GLOBAL["CPU_NUMBER"];
			$this->LOAD_AVERAGE=$_GLOBAL["LOAD_AVERAGE"];
			
			$page=$this->CurrentPageName();
			$this->SessionExpired();
			if(posix_getuid()==0){
				if($this->roundcube_installed){
				$this->roundcube_detect_version();
				}
			}
			
			//Parse privileges
			$priv=$this->_ParsePrivieleges();
			
			$this->_TranslateRights($priv);			
			$this->priv=$priv;

			if($nobuild==0){
				$rights=$priv;

				$this->AsAnAdministratorGeneric=$this->IsGlobalAdmin();
				if($this->AsArticaAdministrator==false){$menus["{account}"]="javascript:Loadjs('users.account.php?js=yes')";}
				if($this->AllowEditAsWbl==true){$menus["{white list} & {black list}"]="users.aswb.php";}
				
				if($this->AsPostfixAdministrator==true){
					$menus["{manage_your_server}"]="javascript:ConfigureYourserver()";
					$menus["{global settings}"]="javascript:Loadjs(\"artica.settings.php?js=yes\")";
					$menus["{softwares_install}"]="javascript:Loadjs(\"setup.index.php?js=yes\");";
					$menus["{services}"]="javascript:Loadjs(\"admin.index.services.status.php?js=yes\");";
					
					}
				$menus["{explorer}"]="javascript:Loadjs(\"tree.php\");";
				$menus["{logoff}"]="javascript:logoff()";
				$menus["{cache}"]="javascript:CacheOff();";
				$this->menus=$menus;
			}

		
		
		
	}
	
	private function postfixmutli(){
	$vers=explode(".",$this->POSTFIX_VERSION);
	if($vers[0]>=2){if($vers[1]>=7){return true;}}
	}
	
	function SessionExpired(){}
	

	private function CurrentPageName(){$phpPage=basename($_SERVER["SCRIPT_FILENAME"]);return $phpPage;}  		
	

	function LoadModulesEnabled(){
		$CurrentPageName=CurrentPageName();
		if($this->LoadModulesEnabledParsed){return ;}
		
		if(!class_exists("sqlgrey")){include(dirname(__FILE__).'/class.sqlgrey.inc');}
		if(!class_exists("milter_greylist")){include(dirname(__FILE__) . '/class.milter.greylist.inc');}
		
		include_once(dirname(__FILE__).'/class.artica.inc');
		$sock=new sockets();
		if($CurrentPageName=="logon.php"){error_log("$CurrentPageName: includes done ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$artica=new artica_general();
		if($CurrentPageName=="logon.php"){error_log("$CurrentPageName: artica class ok ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$ldap=new clladp();
		if($CurrentPageName=="logon.php"){error_log("$CurrentPageName: ldap->ArticaDatas() ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$hash=$ldap->ArticaDatas();	
		if(CurrentPageName()=="logon.php"){writelogs($CurrentPageName.": ".__FUNCTION__.'/'.__LINE__);}		
		$sqlg=new sqlgrey($this->hostname,true);
		if(CurrentPageName()=="logon.php"){writelogs($CurrentPageName.": ".__FUNCTION__.'/'.__LINE__);}
		$milter=new milter_greylist(true);
		if(CurrentPageName()=="logon.php"){writelogs($CurrentPageName.": ".__FUNCTION__.'/'.__LINE__);}
		if($sock->GET_INFO("kavmilterEnable")==1){$this->KAVMILTER_ENABLED=1;}else{$this->KAVMILTER_ENABLED=0;}
		if($sock->GET_INFO("InstantSearchEnable")==1){$this->INSTANSEARCH_ENABLE=true;}
		$this->MilterGreyListEnabled=$artica->MilterGreyListEnabled;
		$this->SMTP_LEVEL=$hash["ArticaMailAddonsLevel"];
		$this->ArticaFilterEnabled=$hash["ArticaFilterEnabled"];
		$this->AmavisFilterEnabled=$sock->GET_INFO('EnableAmavisDaemon');
		$this->KasxFilterEnabled=$artica->KasxFilterEnabled;
		$this->NTPDEnabled=$hash["NTPDEnabled"];
		$this->OBMEnabled=$hash["OBMEnabled"];
		$this->SQLGreyEnabled=$sqlg->SqlGreyEnabled;
		$this->MailFromdEnabled=$hash["MailFromdEnabled"];
		$this->IptablesEnabled=$hash["IptablesEnabled"];
		$this->ApacheArticaEnabled=$artica->ApacheArticaEnabled;
		if($this->ApacheArticaEnabled==null){$this->ApacheArticaEnabled=0;}
		$this->ClamavMilterEnabled=$artica->ClamavMilterEnabled;
		$this->SpamAssMilterEnabled=$artica->SpamAssMilterEnabled;
		$this->spfmilterEnabled=$artica->spfmilterEnabled;
		$this->MimeDefangEnabled=$artica->MimeDefangEnabled;
		$this->DkimFilterEnabled=$artica->DkimFilterEnabled;
		$this->EnableFetchmail=$artica->EnableFetchmail;
		$this->EnableFDMFetch=$artica->EnableFDMFetch;
		$this->P3ScanEnabled=$artica->P3ScanEnabled;
		$this->RoundCubeHTTPEngineEnabled=$artica->RoundCubeHTTPEngineEnabled;
		$this->sTunnel4enabled=$artica->sTunnel4enabled;
		$this->MailArchiverEnabled=$sock->GET_INFO("MailArchiverEnabled");
		$this->EnableMilterBogom=$artica->EnableMilterBogom;
		$this->EnableMysqlFeatures=$artica->EnableMysqlFeatures;
		$this->EnableMilterSpyDaemon=$artica->EnableMilterSpyDaemon;
		$this->EnableCollectdDaemon=$artica->EnableCollectdDaemon;
		$this->EnableAmavisDaemon=$artica->EnableAmavisDaemon;
		$this->JCheckMailEnabled=$artica->JCheckMailEnabled;
		$this->EnableAmavisBackup=$artica->EnableAmavisBackup;
		$this->KAVMILTER_ENABLED=$artica->kavmilterEnable;
		$this->EnableRemoteAddressBook=$artica->EnableRemoteAddressBook;
		$this->EnablePerUserRemoteAddressBook=$artica->EnablePerUserRemoteAddressBook;
		$this->EnableNonEncryptedLdapSession=$artica->EnableNonEncryptedLdapSession;
		//writelogs("KasxFilterEnabled={$hash["KasxFilterEnabled"]},ApacheArticaEnabled=$this->ApacheArticaEnabled ",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(!$this->AMAVIS_INSTALLED){$this->EnableAmavisDaemon==0;}
		
		if($this->SMTP_LEVEL==null){$this->SMTP_LEVEL=0;}
		if(CurrentPageName()=="logon.php"){writelogs($CurrentPageName.": ".__FUNCTION__.'/'.__LINE__);}
		
		$this->LoadModulesEnabledParsed=true;
		}
		
		
function ORGANISTATION_FROM_USER(){
	$ldap=new clladp();
	if($ldap->ldapFailed){return null;}
	$hash=$ldap->Hash_Get_ou_from_users($_SESSION["uid"],1);
	if(is_array($hash)){return $hash[0];}
	}	

private function roundcube_detect_version(){
	$webfolder=$this->roundcube_folder;
	$index_php="$this->roundcube_folder/index.php";
		if($_GET["__ROUNDCUBE_VER"]==null){
			if(!is_file($index_php)){return null;}
			$pattern="#Version\s+([0-9\.a-zA-Z\-]+)#";
			$datas=explode("\n",@file_get_contents($index_php));
			if(!is_array($datas)){return null;}
			while (list ($num, $line) = each ($datas)){
				if(preg_match($pattern,$line,$re)){
					$this->roundcube_version=$re[1];
					$_GET["__ROUNDCUBE_VER"]=$re[1];
					break;
				}
			}
		}
		
	if($_GET["__ROUNDCUBE_INTVER"]==null){
		if(preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)$#",$this->roundcube_version,$re)){
			if(strlen($re[3])==1){$re[3]="0{$re[3]}";}
			$this->roundcube_intversion=$re[1].$re[2].$re[3];
			$_GET["__ROUNDCUBE_INTVER"]=$this->roundcube_intversion;
			return null;
		}
		
		if(preg_match("#^([0-9]+)\.([0-9]+)$#",$this->roundcube_version,$re)){
			$this->roundcube_intversion=$re[1].$re[2].'0';
			$_GET["__ROUNDCUBE_INTVER"]=$this->roundcube_intversion;
			return null;
		}	
		
		if(preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)-#",$this->roundcube_version,$re)){
			
			$this->roundcube_intversion=$re[2].$re[3]."0";
			$_GET["__ROUNDCUBE_INTVER"]=$this->roundcube_intversion;
			return null;
		}	
	}else{
		$this->roundcube_intversion=$this->roundcube_version=$_GET["__ROUNDCUBE_INTVER"];
	}

}
	
	
	function BuildLeftMenus(){
		$sock=new sockets();
		$currentPageName=CurrentPageName();
		error_log("page=$currentPageName ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);
		if($_SESSION["UsersInterfaceDatas"]<>null){
			if($currentPageName=="logon.php"){error_log("$currentPageName: UsersInterfaceDatas<>null, return ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
			return null;
		}
		if(is_array($_SESSION["MainLeftMenus"])){
			$this->main_left_menus=$_SESSION["MainLeftMenus"];
			if($currentPageName=="logon.php"){error_log("$currentPageName: MainLeftMenus<>null, return ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
			return $_SESSION["MainLeftSubPagesHTML"];
		}
		
		if($currentPageName=="logon.php"){error_log("$currentPageName: ->LoadModulesEnabled ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$this->LoadModulesEnabled();
		if($currentPageName=="logon.php"){error_log("$currentPageName: LoadModulesEnabled() done ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$this->BuildBackupFeature();
		$page=$currentPageName;
		if($page=='imap.index.php'){return null;}
		if(!isset($_SESSION["uid"])){return null;}
	
		if($_GET["ou"]==null){
			if($this->AsArticaAdministrator){
				$ldap=new clladp();
				$hash=$ldap->hash_get_ou(false);
				$_GET["ou"]=$hash[0];
			}else{
				$_GET["ou"]=$hash[0]=$this->ORGANISTATION_FROM_USER();
			}
			
		}
		if($cuurentpage=="logon.php"){error_log("$currentPageName: ORG={$_GET["ou"]} done ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$SMTP_LEVEL=$this->SMTP_LEVEL;
		
		
	//#############################################" user ########################################################	
		if($_SESSION["uid"]==-100){$asSuperAdmin=true;}
			
			
			
			
			
			if($_SESSION["uid"]<>-100){
						
			
			
				$sub_array_pages["ACCOUNT"]["javascript:Loadjs(\"users.account.php?js=yes\")"]='{account}';
				$this->main_left_menus["users.account.php"]=array(
							"TITLE"=>"{account}",
							"IMG"=>"identity-64.png",
							"TEXT"=>"{menu_account_text}",
							"AJAX"=>"javascript:Loadjs('users.account.php?js=yes')",
							"NOAJAX"=>false);
				}
				
			if($this->POSTFIX_INSTALLED){	
			if($_SESSION["backup_feature"]){
				$sub_array_pages["ACCOUNT"]["users.backup.php"]="{backup}";
				$this->main_left_menus["users.backup.php"]=array(
					"TITLE"=>"{backup}",
					"IMG"=>"folder-64-artica-backup.png",
					"TEXT"=>"{menu_backup_text}",
					"NOAJAX"=>true);
				}
			}
							
//************************************** avec postfix **********************************************************************************************			
		if(!$asSuperAdmin){
			
					if($this->AsOrgAdmin){
									$this->main_left_menus['ORG']=array(
										"TITLE"=>"{$_SESSION["ou"]}",
										"IMG"=>"folder-org-64.png",
										"TEXT"=>"{manage_organisations_text}",
										"POPUP"=>true,
										"NOAJAX"=>false,
										"AJAX"=>"javascript:Loadjs('domains.manage.org.index.php?js=yes&ou={$_SESSION["ou"]}')",
										);
						}
			
			   if($this->EnablePerUserRemoteAddressBook==1){
			   		$this->main_left_menus["users.addressbook.index.php"]=array(
										"TITLE"=>"{ADDRESSBOOK}",
										"IMG"=>"64-addressbook.png",
										"TEXT"=>"{ADDRESSBOOK_TEXT}",
										"NOAJAX"=>false,
										"AJAX"=>"Loadjs('users.addressbook.index.php')",
										);
			   	
			   }
			   if($this->cyrus_imapd_installed){
					$this->main_left_menus["my.addressbook.php"]=array(
										"TITLE"=>"{YOUR_ADDRESSBOOK}",
										"IMG"=>"64-your-address-book.png",
										"TEXT"=>"{YOUR_ADDRESSBOOK_TEXT}",
										"NOAJAX"=>false,
										"AJAX"=>"Loadjs('my.addressbook.php')",
										);
			   }			   
			   
			  
			
			
									
				if($this->OPENVPN_INSTALLED){
					if($this->AllowOpenVPN){
								$this->main_left_menus["users.openvpn.index.php"]=array(
										"TITLE"=>"{APP_OPENVPN}",
										"IMG"=>"64-openvpn.png",
										"TEXT"=>"{menu_openvpn_text}",
										"NOAJAX"=>false,
										"AJAX"=>"Loadjs('users.openvpn.index.php?&uid={$_SESSION["uid"]}')",
										);
					}
				}
			
			
			if($this->POSTFIX_INSTALLED){
						if($this->cyrus_imapd_installed==true){
							if($this->roundcube_installed==true){
								if($_SESSION["MailboxActive"]=="TRUE"){
									include_once(dirname(__FILE__).'/class.roundcube.inc');
									$r=new roundcube();
									$sub_array_pages["ACCOUNT"]["popup:".$r->roundCubeArray["user_link"]]="{webmail}";
										$this->main_left_menus[$r->roundCubeArray["user_link"]]=array(
										"TITLE"=>"{webmail}",
										"IMG"=>"folder-usermailbox-64.png",
										"TEXT"=>"{menu_webmail_text}",
										"POPUP"=>true,
										"NOAJAX"=>true);
									
									}
							
							}
							
							if($_SESSION["MailboxActive"]=="TRUE"){
								$this->main_left_menus["users.out-of-office.php"]=array(
										"TITLE"=>"{OUT_OF_OFFICE}",
										"IMG"=>"64-out-of-office.png",
										"TEXT"=>"{menu_OUT_OF_OFFICE_text}",
										"NOAJAX"=>false,
										"AJAX"=>"Loadjs('sieve.vacation.php')",
										);
								}
							
							if($this->APP_ATOPENMAIL_INSTALLED==true){
								if($_SESSION["MailboxActive"]=="TRUE"){
									include_once(dirname(__FILE__).'/class.roundcube.inc');
									$r=new roundcube();
									$sub_array_pages["ACCOUNT"]["popup:mail/index.php"]="{APP_ATOPENMAIL}";
										$this->main_left_menus["mail/index.php"]=array(
										"TITLE"=>"{APP_ATOPENMAIL}",
										"IMG"=>"64-atmail.png",
										"TEXT"=>"{menu_webmail_text}",
										"POPUP"=>true,
										"NOAJAX"=>true);
									}
							}								
		
									
									
								if($this->AllowFetchMails){
									if($_SESSION["MailboxActive"]=="TRUE"){
									if($this->fetchmail_installed){
										$sub_array_pages["ACCOUNT"]["users.fetchmail.index.php"]="{fetchmail}";
										$this->main_left_menus["users.fetchmail.index.php"]=array(
										"TITLE"=>"{fetchmail}",
										"IMG"=>"folder-64-fetchmail.png",
										"TEXT"=>"{menu_fetchmail_text}",
										"NOAJAX"=>false,
										"AJAX"=>"Loadjs('wizard.fetchmail.newbee.php?script=yes&uid={$_SESSION["uid"]}')",
										);
										
									/*$sub_array_pages["ACCOUNT"]["users.hotmail.index.php"]="{hotmail}";
										$this->main_left_menus["users.hotmail.index.php"]=array(
										"TITLE"=>"{hotmail}",
										"IMG"=>"64-msn.png",
										"TEXT"=>"{fetch_hotmail}",
										"NOAJAX"=>true);*/
									
										}									
									
									}}
									
									
								/*if($this->AllowEmailing){
									$this->main_left_menus["users.emailing.index.php"]=array(
										"TITLE"=>"{emailing}",
										"IMG"=>"64-emailing.png",
										"TEXT"=>"{menu_emailing_text}",
										"AJAX"=>"javascript:Loadjs('users.emailing.php?js=yes')",
										"NOAJAX"=>false);
									
								}*/
									
						}
						
				if(!$this->cyrus_imapd_installed==true){	
						if($this->GNARWL_INSTALLED){
								$this->main_left_menus["users.out-of-office.php"]=array(
										"TITLE"=>"{OUT_OF_OFFICE}",
										"IMG"=>"64-out-of-office.png",
										"TEXT"=>"{menu_OUT_OF_OFFICE_text}",
										"NOAJAX"=>false,
										"AJAX"=>"Loadjs('users.out-of-office.php')",
										);
								}
						}						
					
				if($_SESSION["uid"]<>-100){
						if($this->cyrus_imapd_installed){
							if($this->AllowChangeMailBoxRules){
								if($_SESSION["MailboxActive"]=="TRUE"){
									$sub_array_pages["ACCOUNT"]["user.sieve.index.php"]='{manage_your_mailbox_rules}';
									$this->main_left_menus["user.sieve.index.php"]=array(
											"TITLE"=>"{mailbox_rules}",
											"IMG"=>"folder-mailrules-64.png",
											"TEXT"=>"{menu_sieve_text}",
											"NOAJAX"=>false,
											"AJAX"=>"Loadjs('users.sieve.php')",);
							}
						}}
				
				if($this->EnableAmavisDaemon==1){
					if($this->AllowEditAsWbl==true){
					$sub_array_pages["ACCOUNT"]["users.aswb.php"]="{AllowEditAsWbl}";
					$this->main_left_menus["users.aswb.php"]=array(
									"TITLE"=>"{AllowEditAsWbl}",
									"IMG"=>"folder-domains-blacklist-64.png",
									"TEXT"=>"{AllowEditAsWbl_text}",
									"NOAJAX"=>true);
					}
					
					if($this->AllowChangeUserKas==1){
						$this->main_left_menus["javascript:Loadjs('users.amavis.php')"]=array(
									"TITLE"=>"{AntiSpam_parameters}",
									"IMG"=>"folder-caterpillar.png",
									"TEXT"=>"{AntiSpam_parameters_text}",
									"NOAJAX"=>true);
					}
				
				
				
				}
					
					
					if($this->DOTCLEAR_INSTALLED){
						if($_SESSION["DotClearUserEnabled"]==1){
							$sub_array_pages["ACCOUNT"]["javascript:YahooWin(450,\"dotclear.index.php?UserDotClear=yes\")"]="{APP_DOTCLEAR}";
							$this->main_left_menus["javascript:YahooWin(450,'dotclear.index.php?UserDotClear=yes')"]=array(
									"TITLE"=>"{APP_DOTCLEAR}",
									"IMG"=>"64-dotclear.png",
									"TEXT"=>"{dotclear_user_intro}",
									"NOAJAX"=>true);
						}
					}
								
					}
		
					if($_SESSION["backup_feature"]){
						$sub_array_pages["ACCOUNT"]["users.backup.php"]="{backup}";
						$this->main_left_menus["users.backup.php"]=array(
									"TITLE"=>"{backup}",
									"IMG"=>"folder-64-artica-backup.png",
									"TEXT"=>"{menu_backup_text}",
									"NOAJAX"=>true);
						
					}
						
					
						$sub_array_pages["ACCOUNT"]["user.quarantine.query.php"]="{quarantine}";
					
						$this->main_left_menus["user.quarantine.query.php"]=array(
									"TITLE"=>"{quarantine}",
									"IMG"=>"folder-quarantine-0-64.png",
									"TEXT"=>"{menu_quarantine_text}",
									"NOAJAX"=>true);
									
											
					if($this->POSTFIX_INSTALLED){
						if($_SESSION["MailboxActive"]=="TRUE"){
									$this->main_left_menus['statistics']=array(
										"TITLE"=>"{statistics}",
										"IMG"=>"64-charts.png",
										"TEXT"=>"{menu_statistic_text}",
										"POPUP"=>true,
										"NOAJAX"=>false,
										"AJAX"=>"javascript:Loadjs('users.index.php?graph-js=yes')",
										);
						}
					}
					
			

										
						
//----------------------------------------- OBM -------------------------------------------------------			
			if($this->OBM_INSTALLED){
					if($this->OBMEnabled){
					include_once(dirname(__FILE__).'/class.obm.inc');
							$obm=new obm();
							if($obm->external_url==null){$obm->external_url=$this->hostname;}
							$sub_array_pages["ACCOUNT"]["popup:https://$obm->external_url:$obm->apache_listen"]="{calendar}";
							$this->main_left_menus["https://$obm->external_url:$obm->apache_listen"]=array(
										"TITLE"=>"{calendar}",
										"IMG"=>"64-obm.png",
										"TEXT"=>"{menu_calendar_text}",
										"POPUP"=>true,
										"NOAJAX"=>true);
							
						}else{
							writelogs("OBM is not enabled, skip",__CLASS__.'/'.__FUNCTION__,__FILE__);
						}
					}else{
						writelogs("OBM is not installed, skip",__CLASS__.'/'.__FUNCTION__,__FILE__);
					}
	
//-----------------------------------------//-----------------------------------------//-----------------
			if($this->OBM2_INSTALLED){
				$sock=new sockets();
				$Obm2ListenPort=trim($sock->GET_INFO('Obm2ListenPort'));
				$Obm2Externaluri=trim($sock->GET_INFO('Obm2Externaluri'));	
				if($Obm2ListenPort==null){$Obm2ListenPort=8080;}					
				if(trim($Obm2Externaluri)==null){$Obm2Externaluri="http://$this->hostname:$Obm2ListenPort";}
				$sub_array_pages["ACCOUNT"]["popup:$Obm2Externaluri"]="{APP_OBM2} {calendar}";
				$this->main_left_menus[$Obm2Externaluri]=array(
										"TITLE"=>"{APP_OBM2} {calendar}",
										"IMG"=>"64-obm2.png",
										"TEXT"=>"{menu_calendar_text}",
										"POPUP"=>true,
										"NOAJAX"=>true);
			}
	
	
//-----------------------------------------//-----------------------------------------//-----------------

//-----------------------------------------//-----------------------------------------//-----------------
			if($this->XAPIAN_PHP_INSTALLED){
				if($this->INSTANSEARCH_ENABLE){
					$sub_array_pages["ACCOUNT"]["xapian.index.php"]="{InstantSearch}";
					$this->main_left_menus["xapian.index.php"]=array(
											"TITLE"=>"{InstantSearch}",
											"IMG"=>"64-xapian.png",
											"TEXT"=>"{InstantSearch_text}",
											"POPUP"=>false,
											"NOAJAX"=>true);
				}
			}
	
	
//-----------------------------------------//-----------------------------------------//-----------------				
			

		}}else {

		}

		//************************************************************************************************************************************	
//#######################################################################################################################
		
		
		
		if($this->AsArticaAdministrator==true){
			$sub_array_pages["ARTICA"]["system.index.php"]='MAIN';
			$this->main_left_menus["system.index.php"]=array(
				"TITLE"=>"{system}",
				"IMG"=>"folder-tasks2-64.png",
				"TEXT"=>"{menu_system_text}"
				);
		

		$sub_array_pages["ORG"]["domains.index.php"]='MAIN';
			$this->main_left_menus["domains.index.php"]=array(
				"TITLE"=>"{organizations}",
				"IMG"=>"member-64.png",
				"TEXT"=>"{menu_organizations_text}",
				"NOAJAX"=>false,
				"AJAX"=>"Loadjs('domains.index.php?js=yes')",
				);
			}
		
		if($this->AsPostfixAdministrator==true){
			if($this->POSTFIX_INSTALLED==true){
					$sub_array_pages["POSTFIX"]["postfix.index.php"]='MAIN';
					$this->main_left_menus["postfix.index.php"]=array(
				"TITLE"=>"{messaging}",
				"IMG"=>"folder-64-fetchmail.png",
				"TEXT"=>"{menu_messaging_text}",
				"NOAJAX"=>false);

					
					}
				}
		
		
		
		/*if(!$this->BIND9_INSTALLED){
			if($this->dnsmasq_installed==true){
				$sub_array_pages["DNSMASQ"]=array(
					'dnsmasq.index.php'=>'MAIN',
					'dnsmasq.dns.settings.php'=>'{dnsmasq_settings}',
					'dnsmasq.records.settings.php'=>'{dnsmasq_DNS_records}',
					'dnsmasq.daemon.events.php'=>'{events}'
					);
			}
		}*/
		
		if($this->AsSambaAdministrator==true){
			if($this->SAMBA_INSTALLED){
				$sub_array_pages["{fileshare}"]["fileshares.index.php"]='MAIN';
				$this->main_left_menus["fileshares.index.php"]=array(
				"TITLE"=>"{fileshare}",
				"IMG"=>"folder-64-share.png",
				"TEXT"=>"{menu_share_text}",
				"NOAJAX"=>false,
				"AJAX"=>"Loadjs('fileshares.index.php?js=yes')",
				);				
				}
		}		

	

			

		
		$array["{index page}"]="javascript:document.location.href='users.index.php'";
	

			
		
			$OU=trim($_GET["ou"]);
			if($OU==null){
				include_once(dirname(__FILE__).'/class.user.inc');
				$us=new user($_SESSION["uid"]);
				$OU=$us->ou;
			}
			if($this->AllowAddUsers){writelogs("AllowAddUsers=TRUE && ou=$OU",__FUNCTION__,__FILE__);}else{writelogs("AllowAddUsers=FALSE && ou=$OU",__FUNCTION__,__FILE__);}

			if($this->AllowViewStatistics==true){
				
				$sub_array_pages["STAT"]['statistics.index.php']='MAIN';
				if($this->mailgraph_installed==true){
					$sub_array_pages["STAT"]['statistics.mailgraph.php']='{emails_flow}';
				}
				if($this->queuegraph_installed==true){
					$sub_array_pages["STAT"]['statistics.queuegraph.php']='{queue_flow}';
				}
				if($this->yorel_installed==true){
					$sub_array_pages["STAT"]['statistics.systems.yorel.php']='{system_perfomances}';
					$sub_array_pages["STAT"]['statistics.systems-storage.yorel.php']='{system_perfomances_datas}';
				}
				
				
			}

			
		if($this->AsArticaAdministrator==true){
			$array["{organizations}"]["MAIN"]="javascript:Loadjs('domains.index.php?js=yes')";
			$ldap=new clladp();
			$ous=$ldap->hash_get_ou(true);
			while (list ($num, $ligne) = each ($ous) ){
				$array["{organizations}"]["SUB"][$num]="javascript:Loadjs('domains.manage.org.index.php?js=yes&ou=$num');";
			}
		
		
		}
		
		
		if(($this->AsOrgAdmin) or ($this->AllowAddUsers)){
			if($OU<>null){
				if(strlen($OU)>13){$OU_TEXT=substr($OU,0,10).'...';}else{$OU_TEXT=$OU;}
				$array[$OU_TEXT]="javascript:Loadjs('domains.manage.org.index.php?js=yes&ou=$OU');";}
			
		}
		
		if($this->AsArticaAdministrator==true){
			$array["system"]["MAIN"]="javascript:Loadjs('system.index.php?js=yes')";
			$array["system"]["SUB"]["{left_menu_internal_hard_drives}"]="javascript:Loadjs('system.internal.disks.php')";
			$array["system"]["SUB"]["{left_menu_net_settings}"]="javascript:Loadjs('system.nic.config.php?js=yes')";
			if($this->dhcp_installed){
				$array["system"]["SUB"]["{APP_DHCP}"]="javascript:Loadjs('index.gateway.php?index_dhcp=yes')";
			}
		}
			

			
			
		if($this->AsSambaAdministrator==true){if($this->SAMBA_INSTALLED){
			$array["{fileshare}"]["MAIN"]="javascript:Loadjs('fileshares.index.php?js=yes')";}
			$array["{fileshare}"]["SUB"]["{main_settings}"]="javascript:Loadjs('samba.index.php?script=smbpop');";
			$array["{fileshare}"]["SUB"]["{SHARE_FOLDER}"]="javascript:Loadjs('SambaBrowse.php');";
			$array["{fileshare}"]["SUB"]["{computers}"]="javascript:Loadjs('computer-browse.php');";
		}
		if($this->AsSystemAdministrator==true){if($this->PUREFTP_INSTALLED){$array["{fileshare}"]="javascript:Loadjs('fileshares.index.php?js=yes')";}}
		if($this->SQUID_INSTALLED==true){if($this->AsSquidAdministrator==true){
			$array["{web_proxy}"]["MAIN"]="javascript:Loadjs('squid.newbee.php?yes')";
			if($this->DANSGUARDIAN_INSTALLED){
				$array["{web_proxy}"]["SUB"]["{DANSGUARDIAN_RULES}"]="javascript:Loadjs('dansguardian.index.php?js=yes')";

			}
			
			if($this->SQUIDGUARD_INSTALLED){
				$array["{web_proxy}"]["SUB"]["{DANSGUARDIAN_RULES}"]="javascript:Loadjs('dansguardian.index.php?js=yes')";

			}

			$array["{web_proxy}"]["SUB"]["{statistics}"]="javascript:Loadjs('squid.statistics.php')";
			
		}
		
		
		}
		
		if($this->POSTFIX_INSTALLED){
			if($this->AsPostfixAdministrator==true){
				$array["{messaging}"]["MAIN"]="javascript:Loadjs('postfix.index.php')";
				$array["{messaging}"]["SUB"]["{postfix_events}"]="s_PopUp(\"postfix.events.php?pop=true\",450,400)";
				if($this->AMAVIS_INSTALLED){
					if($sock->GET_INFO("EnableAmavisDaemon")==1){
						$array["{messaging}"]["SUB"]["{APP_AMAVIS}"]="javascript:Loadjs('amavis.index.php?ajax=yes')";
					}
				}
				
				
			}
		
		}
		
							 

//		if($this->AsPostfixAdministrator==true && $this->dnsmasq_installed==true && $this->BIND9_INSTALLED==false){$array["dnsmasq"]="dnsmasq.index.php";}
		if($this->POSTFIX_INSTALLED){if($this->AsPostfixAdministrator==true && $this->mailgraph_installed==true){$array["Mailman"]="mailman.index.php";}}
		if($this->AllowViewStatistics==true){
			$array["{statistics}"]="javascript:Loadjs('statistics.index.php')";
			$array["{statistics}"]["SUB"]["{web_proxy}"]="javascript:Loadjs('squid.statistics.php')";
		
		}		
		$menus=$this->BuildArticaMenus($array,$sub_array_pages);
		$this->toplinks=$menus;
		return $menus;
		
		
		
			
		
		$this->toplinks="<div id='TpLink'>
		<table style='width:100%'>";
		
		$html=$html."\n\t<li class='top'><a href='#'> </a></li>\n";
		if(is_array($array)){
		while (list ($num, $ligne) = each ($array) ){
			
			if($page==$ligne){$class="id='selected'";}else{if($ligne=="#"){$class="id=subtitle";}else{$class=null;}}
				if(strpos($ligne,'ascript')>0){
					$html=$html . "\t\t<li $class><a href='#'>$num&nbsp;&raquo;</a></li>\n";
				}else{
					$html=$html . "\t\t<li $class><a href='$ligne'>$num&nbsp;&raquo;</a></li>\n";
				}
				
			
				
				$toplinks=$this->BuildLefetMenusSubmenu($sub_array_pages,$ligne,false);
				if($toplinks<>null){
						$this->toplinks=$this->toplinks.$toplinks;
						$count_toplinks=$count_toplinks+1;
				}
				
				$BuildLefetMenusSubmenu=$this->BuildLefetMenusSubmenu($sub_array_pages,$ligne);
				
				$html=$html . $BuildLefetMenusSubmenu;
			}
		}
		$html=$html . "<li class='bottom'><a href='#'> </a></li>";
		$this->toplinks=$this->toplinks. "</table></div>";
		if($count_toplinks==0){$this->toplinks=null;}
		$_SESSION["MainLeftMenus"]=$this->main_left_menus;
		$_SESSION["MainLeftSubPagesHTML"]=$html;
		return $html;
		
	}
	
	function BuildArticaMenus($master_array,$subarray){
		$tpl=new templates();
		$html[]="
		<div>
		<ul id=\"navigation\">
		
		
		";
		while (list ($num, $ligne) = each ($master_array) ){
			if(is_array($ligne)){
				$html[]="<li><a class=\"head\" href=\"{$ligne["MAIN"]}\">". $tpl->_ENGINE_parse_body($num)."</a>";
				if(is_array($ligne["SUB"])){
					$html[]="<ul>";
					while (list ($submenu_title, $js) = each ($ligne["SUB"]) ){
						$html[]="<li><a href=\"$js\">". $tpl->_ENGINE_parse_body($submenu_title)."</a></li>";
					}
					$html[]="</ul>";
				}
				$html[]="</li>";
				continue;
			}
			$html[]="<li><a class=\"head\" href=\"$ligne\">$num</a></li>";
			//$html[]="<ul><li> <a href=\"$ligne\">$num</a></li></ul>";
			
		}
		
		$html[]="</u></div>
				<script>
		jQuery().ready(function(){	
			jQuery('#navigation').accordion({
			active: false,
			header: '.head',
			navigation: true,
			event: 'mouseover',
			fillSpace: true,
			clearStyle: true,
			animated: 'easeslide'});
		});
		</script>";
		return implode("\n",$html);
		
		$html[]="<div>
<ul id=\"navigation\">

				<li>
					<a class=\"head\" href=\"?p=1.1.1\">Guitar</a>
					<ul>
						<li><a href=\"?p=1.1.1.1\">Electric</a></li>
						<li><a href=\"?p=1.1.1.2\">Acoustic</a></li>
						<li><a href=\"?p=1.1.1.3\">Amps</a></li>
						<li><a href=\"?p=1.1.1.4.1\">Effects A</a></li>

						<li><a href=\"?p=1.1.1.4.2\">Effects B</a></li>
						<li><a href=\"?p=1.1.1.4.3\">Effects C</a></li>
						<li><a href=\"?p=1.1.1.4.4\">Effects D</a></li>
						<li><a href=\"?p=1.1.1.5\">Accessories</a></li>
					</ul> 
				</li>
				<li>

					<a class=\"head\" href=\"?p=1.1.2\">Bass</a>
					<ul>
						<li><a href=\"?p=1.1.2.1\">Electric</a></li>
						<li><a href=\"?p=1.1.2.2\">Acoustic</a></li>
						<li><a href=\"?p=1.1.2.3\">Amps</a></li>
						<li><a href=\"?p=1.1.2.4\">Effects</a></li>

						<li><a href=\"?p=1.1.2.5\">Accessories</a></li>
					</ul> 
				</li>
				<li>
					<a class=\"head\" href=\"?p=1.1.3\">Drums</a>
					<ul>
						<li><a href=\"?p=1.1.3.2\">Acoustic Drums</a></li>
						<li><a href=\"?p=1.1.3.3\">Electronic Drums</a></li>

						<li><a href=\"?p=1.1.3.4\">Cymbals</a></li>
						<li><a href=\"?p=1.1.3.5\">Hardware</a></li>
						<li><a href=\"?p=1.1.3.6\">Accessories</a></li>
					</ul> 
				</li>
			</ul>
		</div>		
		

		
		";
		return implode("\n",$html);
		
		
		
		$w="100%";
		$html="<div class=navig>";
		$html=$html . "<table width=\"$w\" cellspacing=\"0\" cellpadding=\"10\" border=\"0\">";
		$tpl=new templates();
		while (list ($num, $ligne) = each ($master_array) ){
			
			
			$id=md5($num.$ligne);
			
			
			
			$master_js="onclick=\"LeftMenusSwitch('{$id}_menubox', '{$id}_menubullet')\"";
			if(strpos($ligne,'vascript')>0){
				$ligne=str_replace("javascript:","",$ligne);
				$master_js="onclick=\"javascript:document.getElementById('{$id}_menubullet').src='ajax-menus-loader.gif';
				$ligne;setTimeout('Rebullet(\'{$id}_menubullet\')',1800)\"";
				$master_js=str_replace("#bullet#","&bullet-id=$id",$master_js);
			}
			
			$num=$tpl->_ENGINE_parse_body($num);
			$num=str_replace(" ","&nbsp;",$num);
			$html=$html. "
			<tr>
			<td id='nav'>
				<div align=\"left\" style='width:$w'>
					<input type='hidden' id='{$id}_menubox_link' value='$ligne'>
					<a title=\"{$num}\" href=\"#\" $master_js>
						<img src=\"img/fullbullet.gif\" name=\"{$id}_menubullet\" border=\"0\" id=\"{$id}_menubullet\"/ style='margin-right:5px'>{$num}</a>
				</div>
				<div id=\"{$id}_menubox\" align=\"left\" style='display:none;width:$w'>
					<table width=\"$w\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
				 	";
			
				$key=$this->BuildLefetMenusFoundSubmenubyPage($subarray,$ligne);
				if($subarray[$key][$ligne]<>null){
					while (list ($s_num, $s_ligne) = each ($subarray[$key] )){
						$target="";
						$s_ligne=str_replace("MAIN","{menus_left_main}",$s_ligne);
						if(preg_match('#popup:(.+)#',$s_num,$re)){
							$target=" target='_new'";
							$s_num=$re[1];
						}
						

						$html=$html . 
	
						"<tr>
                    		<td id=\"subnav\"><a href='$s_num' $target>{$s_ligne}</a></td>
                 		</tr>";
					
					
					}}
					
				$html=$html . "
				
				</table>
				</div>
				</td>
				</tr>";
			
		}
		
		$html=$html . "</table>
		</div>";
		return $html;
	}
	
	
	function BuildLefetMenusSubmenu($array,$page,$normal=true){
		
		$key=$this->BuildLefetMenusFoundSubmenu($array);
		if($array[$key][$page]==null){return null;}
		$page=CurrentPageName();
		if($key<>null){
			while (list ($num, $ligne) = each ($array[$key] )){
				if(strpos($num,'?')>0){$link=substr($num,0,strpos($num,'?'));}else{$link=$num;}
				
				
				if($page==$link){$class="id='selected'";}else{if($num=="#"){$class="id=subtitle";}else{$class=null;}}
				if($ligne<>"MAIN"){
					if($normal==true){$html=$html . "\t\t<li $class><a href='$num' style='font-size:10px'>$ligne&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>\n";}
					else {$html=$html . "\t\t<tr><td width=1%><img src='img/arrow.gif'></td><td class='ten'><a href='$num' >$ligne</a></td></tr>\n";}
				}
			}
			}
		return $html;}
		
		
		function BuildBackupFeature(){
			if(isset($_SESSION["backup_feature"])){return null;}
			$this->LoadModulesEnabled();
			writelogs("backup_feature:: MailArchiverEnabled=$this->MailArchiverEnabled",__FUNCTION__,__FILE__);
			if($this->MailArchiverEnabled==1){
						$array["users.backup.php"]="{backup}";
						$_SESSION["backup_feature"]=true;
					
				}else{					
				
				writelogs("backup_feature:: MIMEDEFANG_INSTALLED=$this->MIMEDEFANG_INSTALLED",__FUNCTION__,__FILE__);
				if($this->MIMEDEFANG_INSTALLED){
					include_once(dirname(__FILE__).'/class.mimedefang.inc');
					$mime=new mimedefang();
					if($mime->ScriptConf_array["BUILD"]["BACKUP_ENABLED"]==1){
						include_once(dirname(__FILE__).'/class.backup.emails.inc');
						$back=new backup_email($_SESSION["ou"]);
						if($back->BackupEnabled==1){
							$array["users.backup.php"]="{backup}";
							$_SESSION["backup_feature"]=true;
							}
						}
					}	
				}
				writelogs("backup_feature:: AMAVIS_INSTALLED=$this->AMAVIS_INSTALLED",__FUNCTION__,__FILE__);
				writelogs("backup_feature:: EnableAmavisDaemon=$this->EnableAmavisDaemon",__FUNCTION__,__FILE__);
				writelogs("backup_feature:: EnableAmavisBackup=$this->EnableAmavisBackup",__FUNCTION__,__FILE__);
				if($this->AMAVIS_INSTALLED){
					if($this->EnableAmavisDaemon==1){
						if($this->EnableAmavisBackup==1){
							$array["users.backup.php"]="{backup}";
							$_SESSION["backup_feature"]=true;
						}
					}
				}
				
			
			
			
		}
		
		
		function BuildUsersTabs(){
			
			if(!isset($_SESSION["uid"])){return false;}
			if($_SESSION['UsersInterfaceDatas']<>null){return false;}
			$this->LoadModulesEnabled();
			if($this->IfIsAnuser()==false){return false;}
			include_once(dirname(__FILE__).'/class.user.inc');
			
			if($_SESSION["TEMPLATE_USER_ADDR"]==null){
					$user=new user($_SESSION["uid"]);
					$_SESSION["TEMPLATE_USER_ADDR"]="<strong>$user->givenName $user->sn</strong><br />
					$user->postalAddress $user->street $user->postalCode<br />
					$user->town<br />
					Phone: $user->telephoneNumber<br />
					Mobile: $user->mobile<br />
					$user->mail";
			}
			
			$this->template_user_addr=$_SESSION["TEMPLATE_USER_ADDR"];
				
			if($_SESSION["ou"]==null){
				$user=new user($_SESSION["uid"]);
				$_SESSION["ou"]=$user->ou;
			}
			
			
		if($this->cyrus_imapd_installed==true){
				if($this->roundcube_installed==true){
					include_once(dirname(__FILE__).'/class.roundcube.inc');
					$r=new roundcube();
					$array[$r->roundCubeArray["user_link"]]='{webmail}';	
				}
			}	
						

			
			
			if(!isset($_SESSION["quarantine_feature"])){
				if($this->MIMEDEFANG_INSTALLED){
					include_once(dirname(__FILE__).'/class.mimedefang.inc');
					$mime=new mimedefang();
					if($mime->ScriptConf_array["BUILD"]["QUARANTINE_SPAM"]==1){
							$array["users.quarantine.php"]="{quarantine}";
							$_SESSION["quarantine_feature"]=true;
						}
					}
			}
						
//----------------------------------------- OBM -------------------------------------------------------			
			if($this->OBM_INSTALLED){
					if($this->OBMEnabled){
					include_once(dirname(__FILE__).'/class.obm.inc');
							$obm=new obm();
							if($obm->external_url==null){$obm->external_url=$_SERVER['SERVER_NAME'];}
							$array["https://$obm->external_url:$obm->apache_listen"]="{APP_OBM}";
						}else{
							writelogs("OBM is not enabled, skip",__CLASS__.'/'.__FUNCTION__,__FILE__);
						}
					}else{
						writelogs("OBM is not installed, skip",__CLASS__.'/'.__FUNCTION__,__FILE__);
					}
	
//-----------------------------------------//-----------------------------------------//-----------------					
			
			if($_SESSION["backup_feature"]){$array["users.backup.php"]="{backup}";}
			if($_SESSION["quarantine_feature"]){$array["users.quarantine.php"]="{quarantine}";}


			
			/*if($this->kas_installed==true){
			if($this->AllowChangeAntiSpamSettings==true){
				$array["kas.user.rules.php"]="Anti-spam";
			}}*/
			
			if($this->AllowFetchMails){
				if($this->fetchmail_installed){
					$array["javascript:Loadjs('wizard.fetchmail.newbee.php?script=yes&uid={$_SESSION["uid"]}')"]="{fetchmail}";
				}
				
			}
			
			if($this->cyrus_imapd_installed){
				if($this->AllowChangeMailBoxRules){
					$password=$_SESSION["passwd"];
					$password=md5($password);
					$array["/smartsieve/login.php?uid={$_SESSION["uid"]}&_session=$password"]="{smartsieve}";
					}
			}
			
			if(is_array($array)){
			while (list ($num, $ligne) = each ($array)){
				$page=CurrentPageName();
				if($page==$num){
					$li=$li . "<a href='$num'><span style='color:yellow'>$ligne</span></a>\n";
					$this->template_users_menus_h=$this->template_users_menus_h."<a href=\"$num\">$ligne</a>&nbsp;|&nbsp;";
				}else{
					$li=$li ."<a href='$num'><span>$ligne</span></a>\n";
					$this->template_users_menus_h=$this->template_users_menus_h."<a href=\"$num\">$ligne</a>&nbsp;|&nbsp;";
				}
				
			}}
			
			
			return $li;
			
		}
		
	
	
	function BuildLefetMenusFoundSubmenu($array){
		$page=CurrentPageName();
		while (list ($num, $ligne) = each ($array) ){
			while (list ($key, $val) = each ($ligne) ){	
				if(strpos($key,'?')>0){$key=substr($key,0,strpos($key,'?'));}
				if($key==$page){
					return $num;
				}
			}
		}
	}
	
	function BuildLefetMenusFoundSubmenubyPage($array,$page){
		while (list ($num, $ligne) = each ($array) ){
			if(is_array($ligne)){
				while (list ($key, $val) = each ($ligne) ){	
					if(strpos($key,'?')>0){$key=substr($key,0,strpos($key,'?'));}
					if($key==$page){
						return $num;
						}
					}
			}
		}
	}	
	
	function _TranslateRights($priv){
		
		if(posix_getuid()==0){
			$this->AsMailBoxAdministrator=true;
			$this->AsArticaAdministrator=true;
		
		}
		$this->AsArticaAdministrator=false;
		$this->AsSystemAdministrator=false;
		
		if($priv["AllowChangeDomains"]=='yes'){$this->AllowChangeDomains=true;}
		if($priv["AllowAddGroup"]=="yes"){$this->AllowAddGroup=true;}
		if($priv["AsSystemAdministrator"=='yes']){$this->AsSystemAdministrator=true;}
		if($priv["AsArticaAdministrator"]=='yes'){$this->AsArticaAdministrator=true;}
		if($priv["AllowFetchMails"]=='yes'){$this->AllowFetchMails=true;}
		if($priv["AsMailBoxAdministrator"]=='yes'){$this->AsMailBoxAdministrator=true;}
		if($priv["AllowChangeKav"]=='yes'){$this->AllowChangeKav=true;}
		if($priv["AllowSenderCanonical"]=='yes'){$this->AllowSenderCanonical=true;}
		if($priv["AllowChangeUserPassword"]=='yes'){$this->AllowChangeUserPassword=true;}
		if($priv["AllowChangeKas"]=='yes'){$this->AllowChangeKas=true;}
		if($priv["AllowChangeUserKas"]=='yes'){$this->AllowChangeUserKas=true;}
		if($priv["AllowAddUsers"]=='yes'){$this->AllowAddUsers=true;}
		if($priv["AllowViewStatistics"]=='yes'){$this->AllowViewStatistics=true;}
		if($priv["AllowEditAsWbl"]=='yes'){$this->AllowEditAsWbl=true;}
		if($priv["AllowChangeAntiSpamSettings"]=='yes'){$this->AllowChangeAntiSpamSettings=true;}
		if($priv["AllowEditOuSecurity"]=='yes'){$this->AllowEditOuSecurity=true;}
		if($priv["AsSambaAdministrator"]=='yes'){$this->AsSambaAdministrator=true;}
		if($priv["AsDnsAdministrator"]=='yes'){$this->AsDnsAdministrator=true;}
		if($priv["AsQuarantineAdministrator"]=='yes'){$this->AsQuarantineAdministrator=true;}
		if($priv["AllowEditAliases"]=='yes'){$this->AllowEditAliases=true;}
		if($priv["AllowEmailing"]=='yes'){$this->AllowEmailing=true;}
		if($priv["AllowOpenVPN"]=='yes'){$this->AllowOpenVPN=true;}
		if($priv["AllowDansGuardianBanned"]=='yes'){$this->AllowDansGuardianBanned=true;}
		if($priv["AllowChangeMailBoxRules"]=='yes'){$this->AllowChangeMailBoxRules=true;}
		if($priv["AllowXapianDownload"]=='yes'){$this->AllowXapianDownload=true;}
		if($priv["AsMailManAdministrator"]=='yes'){$this->AsMailManAdministrator=true;}
		if($priv["AsOrgStorageAdministrator"]=='yes'){$this->AsOrgStorageAdministrator=true;}
		if($priv["AllowManageOwnComputers"]=='yes'){$this->AllowManageOwnComputers=true;}
		if($priv["AsOrgPostfixAdministrator"]=='yes'){$this->AsOrgPostfixAdministrator=true;}
		if($priv["AsDansGuardianGroupRule"]=='yes'){$this->AsDansGuardianGroupRule=true;}
		
		
		
		
		if($this->AllowEditOuSecurity==true){
			$this->AllowChangeAntiSpamSettings=true;
			$this->AllowDansGuardianBanned=true;
			$this->AllowManageOwnComputers=true;
			$this->AsDansGuardianGroupRule=true;
		}
		
		
		if($this->IsGlobalAdmin()==true){
			$this->AllowChangeDomains=true;
			$this->AllowAddGroup=true;
			$this->AllowChangeOrg=true;
			$this->AsPostfixAdministrator=true;
			$this->AllowChangeKav=true;
			$this->AllowChangeKas=true;
			$this->AllowViewStatistics=true;
			$this->AllowEditOuSecurity=true;
			$this->AllowAddUsers=true;
			$this->AsSquidAdministrator=true;
			$this->AsSambaAdministrator=true;
			$this->AsDnsAdministrator=true;
			$this->AsQuarantineAdministrator=true;
			$this->AllowDansGuardianBanned=true;
			$this->AllowChangeAntiSpamSettings=true;
			$this->AllowViewStatistics=true;
			$this->AllowSenderCanonical=true;
			$this->AsOrgStorageAdministrator=true;
			$this->AllowManageOwnComputers=true;
			$this->AsDansGuardianGroupRule=true;
			}
		
		if($this->AsArticaAdministrator==true){
			$this->AsSystemAdministrator=true;
			$this->AsMailBoxAdministrator=true;
			$this->AsPostfixAdministrator=true;
			$this->AllowChangeKav=true;
			$this->AllowChangeKas=true;
			$this->AllowAddUsers=true;
			$this->AsOrgAdmin=true;
			$this->AllowViewStatistics=true;
			$this->AllowEditOuSecurity=true;
			$this->AsSquidAdministrator=true;
			$this->AsSambaAdministrator=true;
			$this->AsDnsAdministrator=true;
			$this->AsQuarantineAdministrator=true;
			$this->AllowDansGuardianBanned=true;
			$this->AllowFetchMails=true;
			$this->AllowChangeAntiSpamSettings=true;
			$this->AllowSenderCanonical=true;
			$this->AsMailManAdministrator=true;
			$this->AsOrgStorageAdministrator=true;
			$this->AllowManageOwnComputers=true;
			$this->AsOrgPostfixAdministrator=true;
			$this->AsDansGuardianGroupRule=true;
			}
			
		
		
		if($this->AllowChangeDomains==true){$this->AsOrgAdmin=true;$this->AllowSenderCanonical=true;}
		if($this->AllowEditOuSecurity==true){$this->AsOrgAdmin=true;$this->AllowAddGroup=true;$this->AllowSenderCanonical=true;}
		if($this->AsDnsAdministrator==true){$this->AsOrgAdmin=true;$this->AllowAddGroup=true;}
		if($this->AsOrgAdmin==true){$this->AllowSenderCanonical=true;$this->AllowAddGroup=true;}
		if($this->AsQuarantineAdministrator==true){$this->AsOrgAdmin=true;}
		
	}
	
	function IfIsAnuser(){
			if($this->AsSystemAdministrator==true){return false;}
			if($this->AsMailBoxAdministrator==true){return false;}
			if($this->AsPostfixAdministrator==true){return false;}
			if($this->AllowChangeKav==true){return false;}
			if($this->AllowChangeKas==true){return false;}
			if($this->AllowAddUsers==true){return false;}
			if($this->AsOrgAdmin==true){return false;}
			if($this->AllowViewStatistics==true){return false;}
			if($this->AllowEditOuSecurity==true){return false;}
			if($this->AsSambaAdministrator==true){return false;}
			if($this->AsDnsAdministrator==true){return false;}
			if($this->AsQuarantineAdministrator==true){return false;}
			if($this->AsOrgStorageAdministrator==true){return false;}
			return true;
			}
	
	
	function _ParsePrivieleges($content=null){
		if($content==null){$content=$_SESSION["privileges"]["ArticaGroupPrivileges"];}
		if(is_array($_SESSION["privileges_array"])){
			//print_r($_SESSION["privileges_array"]);
		return $_SESSION["privileges_array"];} 
		
			if(preg_match_all('#\[([a-zA-Z]+)\]="([a-z0-9]+)"#is',$content,$reg)){
				while (list ($num, $ligne) = each ($reg[1]) ){
					if($reg[2][$num]=="yes"){
						writelogs("Privilege  on $ligne",__CLASS__.'/'.__FUNCTION__,__FILE__);
					}
					$priv[$ligne]=$reg[2][$num];
					}}
			$_SESSION["privileges_array"]=$priv;
			return $priv;
	}

	function _BuildPrivileges($hash){
		if(!is_array($hash)){return "DEFAULT";}
		while (list ($num, $ligne) = each ($hash) ){
			$conf=$conf ."[$num]=\"$ligne\"\n";
			
		}
		
		return $conf;
		
	}
	
	
	function isTreeReadable($priv=null){
		if(posix_getuid()==0){$this->uid==-100;}
		if($this->uid==-100){return true;}
		if($priv==null){$priv=$this->priv;}
		if($priv["AsSystemAdministrator"]=="yes"){return true;}
		if($priv["AsPostfixAdministrator"]=="yes"){return true;}
		if($priv["AsArticaAdministrator"]=="yes"){return true;}
		if($priv["AsSystemAdministrator"]=="yes"){return true;}
		if($priv["AsSambaAdministrator"]=="yes"){return true;}
		
		if($priv["AllowChangeKas"]=="yes"){return true;}
		if($priv["AllowChangeKav"]=="yes"){return true;}
		if($priv["AllowAddUsers"]=="yes"){return true;}
		if($priv["AllowAddGroup"]=="yes"){return true;}
		return false;
		}
		
	function IsGlobalAdmin($priv=null){
		if(posix_getuid()==0){$this->uid==-100;}
		if($this->uid==-100){
			$this->AsArticaAdministrator=true;
			$this->AsQuarantineAdministrator=true;
			return true;
			}
		if($priv==null){$priv=$this->priv;}
		if($priv["AsSystemAdministrator"]=="yes"){return true;}
		if($priv["AsPostfixAdministrator"]=="yes"){return true;}
		if($priv["AsArticaAdministrator"]=="yes"){return true;}
		if($priv["AsSquidAdministrator"]=="yes"){return true;}
		if($priv["AsSambaAdministrator"]=="yes"){return true;}
		return false;
		}
	
		
function array_monitorix($type="day"){
				
				$arrayz[]="monitorix/cpu1z.day.png";
				$arrayz[]="monitorix/cpu1z.month.png";
				$arrayz[]="monitorix/cpu1z.week.png";
				$arrayz[]="monitorix/cpu1z.year.png";
				$arrayz[]="monitorix/cpu2z.day.png";
				$arrayz[]="monitorix/cpu2z.month.png";
				$arrayz[]="monitorix/cpu2z.week.png";
				$arrayz[]="monitorix/cpu2z.year.png";	
				$arrayz[]="monitorix/cpu3z.day.png";
				$arrayz[]="monitorix/cpu3z.month.png";
				$arrayz[]="monitorix/cpu3z.week.png";
				$arrayz[]="monitorix/cpu3z.year.png";
				
				$arrayz[]="monitorix/kern1z.day.png";
				$arrayz[]="monitorix/kern1z.month.png";
				$arrayz[]="monitorix/kern1z.week.png";
				$arrayz[]="monitorix/kern1z.year.png";				
				
				$arrayz[]="monitorix/disk3z.day.png";
				$arrayz[]="monitorix/disk3z.month.png";
				$arrayz[]="monitorix/disk3z.week.png";
				$arrayz[]="monitorix/disk3z.year.png";				
				
				$arrayz[]="monitorix/int1z.day.png";
				$arrayz[]="monitorix/int1z.month.png";
				$arrayz[]="monitorix/int1z.week.png";
				$arrayz[]="monitorix/int1z.year.png";				
				
				$arrayz[]="monitorix/disk2z.day.png";
				$arrayz[]="monitorix/disk2z.month.png";
				$arrayz[]="monitorix/disk2z.week.png";
				$arrayz[]="monitorix/disk2z.year.png";				

				$arrayz[]="monitorix/disk1z.day.png";
				$arrayz[]="monitorix/disk1z.month.png";
				$arrayz[]="monitorix/disk1z.week.png";
				$arrayz[]="monitorix/disk1z.year.png";	
				
				$arrayz[]="monitorix/kern2z.day.png";
				$arrayz[]="monitorix/kern2z.month.png";
				$arrayz[]="monitorix/kern2z.week.png";
				$arrayz[]="monitorix/kern2z.year.png";	
				
				$arrayz[]="monitorix/net02z.day.png";
				$arrayz[]="monitorix/net02z.month.png";
				$arrayz[]="monitorix/net02z.week.png";
				$arrayz[]="monitorix/net02z.year.png";							

				
				$arrayz[]="monitorix/int2z.day.png";
				$arrayz[]="monitorix/int2z.month.png";
				$arrayz[]="monitorix/int2z.week.png";
				$arrayz[]="monitorix/int2z.year.png";	

				$arrayz[]="monitorix/net01z.day.png";
				$arrayz[]="monitorix/net01z.month.png";
				$arrayz[]="monitorix/net01z.week.png";
				$arrayz[]="monitorix/net01z.year.png";								
	
				$arrayz[]="monitorix/cpu1.day.png";
				$array[]="monitorix/cpu2.day.png";
				$array[]="monitorix/cpu3.day.png";
				$arrayz[]="monitorix/disk1.day.png";
				$array[]="monitorix/disk2.day.png";
				$array[]="monitorix/disk3.day.png";
				
			
				
				
				$array[]="monitorix/cpu1.month.png";
				$array[]="monitorix/cpu1.week.png";
				$array[]="monitorix/cpu1.year.png";
				
				
				
				$array[]="monitorix/cpu2.month.png";
				$array[]="monitorix/cpu2.week.png";
				$array[]="monitorix/cpu2.year.png";
				
				
				$array[]="monitorix/cpu3.month.png";
				$array[]="monitorix/cpu3.week.png";
				$array[]="monitorix/cpu3.year.png";
				
				
				$array[]="monitorix/disk1.month.png";
				$array[]="monitorix/disk1.week.png";
				$array[]="monitorix/disk1.year.png";
				
				
				$array[]="monitorix/disk2.month.png";
				$array[]="monitorix/disk2.week.png";
				$array[]="monitorix/disk2.year.png";
				
				
				$array[]="monitorix/disk3.month.png";
				$array[]="monitorix/disk3.week.png";
				$array[]="monitorix/disk3.year.png";
				
				$arrayz[]="monitorix/int1.day.png";
				$array[]="monitorix/int1.month.png";
				$array[]="monitorix/int1.week.png";
				$array[]="monitorix/int1.year.png";
				
				$array[]="monitorix/int2.day.png";
				$array[]="monitorix/int2.month.png";
				$array[]="monitorix/int2.week.png";
				$array[]="monitorix/int2.year.png";
				
				
				$arrayz[]="monitorix/kern1.day.png";
				$array[]="monitorix/kern1.month.png";
				$array[]="monitorix/kern1.week.png";
				$array[]="monitorix/kern1.year.png";
				
				$array[]="monitorix/kern2.day.png";
				$array[]="monitorix/kern2.month.png";
				$array[]="monitorix/kern2.week.png";
				$array[]="monitorix/kern2.year.png";
				
				$arrayz[]="monitorix/net01.day.png";
				$array[]="monitorix/net01.month.png";
				$array[]="monitorix/net01.week.png";
				$array[]="monitorix/net01.year.png";

				$array[]="monitorix/net02.day.png";
				$array[]="monitorix/net02.month.png";
				$array[]="monitorix/net02.week.png";
				$array[]="monitorix/net02.year.png";
				
				$array[]="monitorix/net03.day.png";
				$array[]="monitorix/net03.month.png";
				$array[]="monitorix/net03.week.png";
				$array[]="monitorix/net03.year.png";
				$arrayz[]="monitorix/net03z.day.png";
				$arrayz[]="monitorix/net03z.month.png";
				$arrayz[]="monitorix/net03z.week.png";
				$arrayz[]="monitorix/net03z.year.png";
				$arrayz[]="monitorix/port01.day.png";
				$array[]="monitorix/port01.month.png";
				$array[]="monitorix/port01.week.png";
				$array[]="monitorix/port01.year.png";
				$arrayz[]="monitorix/port01z.day.png";
				$arrayz[]="monitorix/port01z.month.png";
				$arrayz[]="monitorix/port01z.week.png";
				$arrayz[]="monitorix/port01z.year.png";
				$array[]="monitorix/port02.day.png";
				$array[]="monitorix/port02.month.png";
				$array[]="monitorix/port02.week.png";
				$array[]="monitorix/port02.year.png";
				$arrayz[]="monitorix/port02z.day.png";
				$arrayz[]="monitorix/port02z.month.png";
				$arrayz[]="monitorix/port02z.week.png";
				$arrayz[]="monitorix/port02z.year.png";
				$array[]="monitorix/port03.day.png";
				$array[]="monitorix/port03.month.png";
				$array[]="monitorix/port03.week.png";
				$array[]="monitorix/port03.year.png";
				$array[]="monitorix/port03z.day.png";
				$array[]="monitorix/port03z.month.png";
				$array[]="monitorix/port03z.week.png";
				$array[]="monitorix/port03z.year.png";
				$array[]="monitorix/port04.day.png";
				$array[]="monitorix/port04.month.png";
				$array[]="monitorix/port04.week.png";
				$array[]="monitorix/port04.year.png";
				$array[]="monitorix/port04z.day.png";
				$array[]="monitorix/port04z.month.png";
				$array[]="monitorix/port04z.week.png";
				$array[]="monitorix/port04z.year.png";
				$array[]="monitorix/port05.day.png";
				$array[]="monitorix/port05.month.png";
				$array[]="monitorix/port05.week.png";
				$array[]="monitorix/port05.year.png";
				$array[]="monitorix/port05z.day.png";
				$array[]="monitorix/port05z.month.png";
				$array[]="monitorix/port05z.week.png";
				$array[]="monitorix/port05z.year.png";
				$array[]="monitorix/port06.day.png";
				$array[]="monitorix/port06.month.png";
				$array[]="monitorix/port06.week.png";
				$array[]="monitorix/port06.year.png";
				$array[]="monitorix/port06z.day.png";
				$array[]="monitorix/port06z.month.png";
				$array[]="monitorix/port06z.week.png";
				$array[]="monitorix/port06z.year.png";
				$array[]="monitorix/port07.day.png";
				$array[]="monitorix/port07.month.png";
				$array[]="monitorix/port07.week.png";
				$array[]="monitorix/port07.year.png";
				$array[]="monitorix/port07z.day.png";
				$array[]="monitorix/port07z.month.png";
				$array[]="monitorix/port07z.week.png";
				$array[]="monitorix/port07z.year.png";
				$array[]="monitorix/port08.day.png";
				$array[]="monitorix/port08.month.png";
				$array[]="monitorix/port08.week.png";
				$array[]="monitorix/port08.year.png";
				$array[]="monitorix/port08z.day.png";
				$array[]="monitorix/port08z.month.png";
				$array[]="monitorix/port08z.week.png";
				$array[]="monitorix/port08z.year.png";
				$array[]="monitorix/port09.day.png";
				$array[]="monitorix/port09.month.png";
				$array[]="monitorix/port09.week.png";
				$array[]="monitorix/port09.year.png";
				$array[]="monitorix/port09z.day.png";
				$array[]="monitorix/port09z.month.png";
				$array[]="monitorix/port09z.week.png";
				$array[]="monitorix/port09z.year.png";
				$array[]="monitorix/port10.day.png";
				$array[]="monitorix/port10.month.png";
				$array[]="monitorix/port10.week.png";
				$array[]="monitorix/port10.year.png";
				$array[]="monitorix/port10z.day.png";
				$array[]="monitorix/port10z.month.png";
				$array[]="monitorix/port10z.week.png";
				$array[]="monitorix/port10z.year.png";
				$array[]="monitorix/port11.day.png";
				$array[]="monitorix/port11.month.png";
				$array[]="monitorix/port11.week.png";
				$array[]="monitorix/port11.year.png";
				$array[]="monitorix/port11z.day.png";
				$array[]="monitorix/port11z.month.png";
				$array[]="monitorix/port11z.week.png";
				$array[]="monitorix/port11z.year.png";
				$array[]="monitorix/port12.day.png";
				$array[]="monitorix/port12.month.png";
				$array[]="monitorix/port12.week.png";
				$array[]="monitorix/port12.year.png";
				$array[]="monitorix/port12z.day.png";
				$array[]="monitorix/port12z.month.png";
				$array[]="monitorix/port12z.week.png";
				$array[]="monitorix/port12z.year.png";
				$arrayz[]="monitorix/proc0.day.png";
				$array[]="monitorix/proc0z.day.png";
				$arrayz[]="monitorix/proc1.day.png";
				$array[]="monitorix/proc1z.day.png";
				$arrayz[]="monitorix/servu1.day.png";
				$array[]="monitorix/servu1.month.png";
				$array[]="monitorix/servu1.week.png";
				$array[]="monitorix/servu1.year.png";
				$arrayz[]="monitorix/servu1z.day.png";
				$arrayz[]="monitorix/servu1z.month.png";
				$arrayz[]="monitorix/servu1z.week.png";
				$arrayz[]="monitorix/servu1z.year.png";
				$array[]="monitorix/servu2.day.png";
				$array[]="monitorix/servu2.month.png";
				$array[]="monitorix/servu2.week.png";
				$array[]="monitorix/servu2.year.png";
				$arrayz[]="monitorix/servu2z.day.png";
				$arrayz[]="monitorix/servu2z.month.png";
				$arrayz[]="monitorix/servu2z.week.png";
				$arrayz[]="monitorix/servu2z.year.png";
				$array[]="monitorix/servu3.day.png";
				$array[]="monitorix/servu3.month.png";
				$array[]="monitorix/servu3.week.png";
				$array[]="monitorix/servu3.year.png";
				$arrayz[]="monitorix/servu3z.day.png";
				$arrayz[]="monitorix/servu3z.month.png";
				$arrayz[]="monitorix/servu3z.week.png";
				$arrayz[]="monitorix/servu3z.year.png";
				$arrayz[]="monitorix/user1.day.png";
				$array[]="monitorix/user1.month.png";
				$array[]="monitorix/user1.week.png";
				$array[]="monitorix/user1.year.png";
				$array[]="monitorix/user1z.day.png";
				$array[]="monitorix/user1z.month.png";
				$array[]="monitorix/user1z.week.png";
				$array[]="monitorix/user1z.year.png";
				$array[]="monitorix/user2.day.png";
				$array[]="monitorix/user2.month.png";
				$array[]="monitorix/user2.week.png";
				$array[]="monitorix/user2.year.png";
				$array[]="monitorix/user2z.day.png";
				$array[]="monitorix/user2z.month.png";
				$array[]="monitorix/user2z.week.png";
				$array[]="monitorix/user2z.year.png";
				$array[]="monitorix/user3.day.png";
				$array[]="monitorix/user3.month.png";
				$array[]="monitorix/user3.week.png";
				$array[]="monitorix/user3.year.png";
				$array[]="monitorix/user3z.day.png";
				$array[]="monitorix/user3z.month.png";
				$array[]="monitorix/user3z.week.png";
				$array[]="monitorix/user3z.year.png";	
	
while (list ($num, $val) = each ($array) ){
	
	if(!preg_match('#z\.#',$val)){
	if(preg_match('#month#',$val)){
		$array_m["month"][$val]=$val;
	}
	
	if(preg_match('#week#',$val)){
		$array_m["week"][$val]=$val;
	}

	if(preg_match('#day#',$val)){
		$array_m["day"][$val]=$val;
	}}	
	
}
return $array_m[$type];
	
}		
		
	
}




?>