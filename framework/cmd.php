<?php
include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");
include_once(dirname(__FILE__)."/class.postfix.inc");


if(isset($_GET["CleanCache"])){CleanCache();exit;}
if(isset($_GET["SaveConfigFile"])){SaveConfigFile();exit;}
if(isset($_GET["SaveClusterConfigFile"])){SaveClusterConfigFile();exit;}
if(isset($_GET["SmtpNotificationConfig"])){SmtpNotificationConfig();exit;}
if(isset($_GET["refresh-frontend"])){Refresh_frontend();exit;}
if(isset($_GET["find-program"])){find_sock_program();exit;}

if(isset($_GET["LaunchRemoteInstall"])){LaunchRemoteInstall();exit;}
if(isset($_GET["restart-web-server"])){RestartWebServer();exit;}
if(isset($_GET["ChangeMysqlLocalRoot"])){ChangeMysqlLocalRoot();exit;}
if(isset($_GET["viewlogs"])){viewlogs();exit;}
if(isset($_GET["LdapdbStat"])){LdapdbStat();exit;}
if(isset($_GET["LdapdbSize"])){LdapdbSize();exit;}
if(isset($_GET["ldap-restart"])){ldap_restart();exit;}
if(isset($_GET["buildFrontEnd"])){buildFrontEnd();exit;}
if(isset($_GET["cpualarm"])){cpualarm();exit;}
if(isset($_GET["CurrentLoad"])){CurrentLoad();exit;}
if(isset($_GET["TaskLastManager"])){TaskLastManager();exit;}
if(isset($_GET["start-all-services"])){StartAllServices();exit;}
if(isset($_GET["kill-pid-number"])){process_kill();exit;}
if(isset($_GET["start-service-name"])){StartServiceCMD();exit;}
if(isset($_GET["stop-service-name"])){StopServiceCMD();exit;}
if(isset($_GET["START-STOP-SERVICES"])){START_STOP_SERVICES();exit;}
if(isset($_GET["monit-status"])){MONIT_STATUS();exit;}
if(isset($_GET["monit-restart"])){MONIT_RESTART();exit;}
if(isset($_GET["restart-artica-maillog"])){ARTICA_MAILLOG_RESTART();exit;}

if(isset($_GET["hamachi-net"])){hamachi_net();exit;}
if(isset($_GET["hamachi-status"])){hamachi_status();exit;}
if(isset($_GET["hamachi-sessions"])){hamachi_sessions();exit;}
if(isset($_GET["hamachi-ip"])){hamachi_currentIP();exit;}
if(isset($_GET["hamachi-restart"])){hamachi_restart();exit;}
if(isset($_GET["hamachi-delete-net"])){hamachi_delete_network();exit;}
if(isset($_GET["UpdateKav4Proxy"])){Kav4ProxyUpdate();exit;}

if(isset($_GET["kavmilter-configure"])){kavmilter_configure();exit;}
if(isset($_GET["kavmilter-mem"])){kavmilter_mem();exit;}
if(isset($_GET["kavmilter-pattern"])){kavmilter_pattern();exit;}
if(isset($_GET["kavmilter_license"])){kavmilter_license();exit;}
if(isset($_GET["kavmilter-bases-infos"])){kav4lms_bases_infos();exit;}
if(isset($_GET["kasversion"])){kasversion();exit;}
if(isset($_GET["kas-reconfigure"])){kas_reconfigure();exit;}
if(isset($_GET["kaspersky-status"])){kaspersky_status();exit;}
if(isset($_GET["kav4proxy-reconfigure"])){kav4proxy_reload();exit;}
if(isset($_GET["RestartRetranslator"])){retranslator_restart();exit;}
if(isset($_GET["RetranslatorSitesList"])){retranslator_sites_lists();exit;}
if(isset($_GET["RetranslatorEvents"])){retranslator_events();exit;}
if(isset($_GET["retranslator-status"])){retranslator_status();exit;}
if(isset($_GET["retranslator-execute"])){retranslator_execute();exit;}
if(isset($_GET["retranslator-dbsize"])){retranslator_dbsize();exit;}
if(isset($_GET["retranslator-tmp-dbsize"])){retranslator_tmp_dbsize();exit;}

if(isset($_GET["Global-Applications-Status"])){Global_Applications_Status();exit;}
if(isset($_GET["status-forced"])){Global_Applications_Status();exit;}
if(isset($_GET["system-reboot"])){shell_exec("reboot");exit;}
if(isset($_GET["system-shutdown"])){shell_exec("init 0");exit;}
if(isset($_GET["system-unique-id"])){GetUniqueID();exit;}



//amavis restart
if(isset($_GET["amavis-restart"])){RestartAmavis();exit;}



//rsync
if(isset($_GET["RestartRsyncServer"])){RestartRsyncServer();exit;}
if(isset($_GET["rsyncd-conf"])){rsync_load_config();exit;}
if(isset($_GET["rsync-save-conf"])){rsync_save_conf();exit;}

//zarafa
if(isset($_GET["zarafa-admin"])){zarafa_admin_chock();exit;}
if(isset($_GET["zarafa-migrate"])){zarafa_migrate();exit;}
if(isset($_GET["zarafa-restart-web"])){zarafa_restart_web();exit;}
if(isset($_GET["zarafa-user-details"])){zarafa_user_details();exit;}
if(isset($_GET["zarafa-user-create-store"])){zarafa_user_create_store();exit;}

//Install/Uninstall
if(isset($_GET["organization-delete"])){organization_delete();exit;}
if(isset($_GET["uninstall-app"])){application_uninstall();exit;}
if(isset($_GET["AppliCenterGetDebugInfos"])){application_debug_infos();exit;}
if(isset($_GET["services-install"])){application_service_install();exit;}


if(isset($_GET["Kav4ProxyLicense"])){kav4proxy_license();exit;}
if(isset($_GET["Kav4ProxyUploadLicense"])){kav4proxy_upload_license();exit;}
if(isset($_GET["Kav4ProxyLicenseDelete"])){kav4proxy_delete_license();exit;}

//fetchmail
if(isset($_GET["restart-fetchmail"])){RestartFetchmail();exit;}
if(isset($_GET["fetchmail-status"])){fetchmail_status();exit;}
if(isset($_GET["fetchmail-logs"])){fetchmail_logs();exit;}


//Ad importation
if(isset($_GET["ad-import-schedule"])){AD_IMPORT_SCHEDULE();exit;}
if(isset($_GET["ad-import-remove-schedule"])){AD_REMOVE_SCHEDULE();exit;}
if(isset($_GET["ad-import-perform"])){AD_PERFORM();exit;}


//exec.hamachi.php
if(isset($_GET["list-nics"])){TCP_LIST_NICS();exit;}
if(isset($_GET["virtuals-ip-reconfigure"])){TCP_VIRTUALS();exit;}


if(isset($_GET["QueryArticaLogs"])){artica_update_query_fileslogs();exit;}
if(isset($_GET["ReadArticaLogs"])){artica_update_query_logs();exit;}

if(isset($_GET["repair-artica-ldap-branch"])){RepairArticaLdapBranch();exit;}

//certitifcate
if(isset($_GET["ChangeSSLCertificate"])){ChangeSSLCertificate();exit;}
if(isset($_GET["postfix-certificate"])){postfix_certificate();exit;}
if(isset($_GET["certificate-viewinfos"])){certificate_infos();exit;}



//opengoo
if(isset($_GET["opengoouid"])){opengoo_user();exit;}
if(isset($_GET["GroupOfficeUid"])){groupoffice_user();exit;}


//safeBox
if(isset($_GET["SafeBoxUser"])){safe_box_set_user();exit;}
if(isset($_GET["mount-safebox"])){safebox_mount();exit;}
if(isset($_GET["umount-safebox"])){safebox_umount();exit;}
if(isset($_GET["safebox-logs"])){safebox_logs();exit;}
if(isset($_GET["check-safebox"])){safebox_check();exit;}

//ntpd
if(isset($_GET["ntpd-restart"])){ntpd_restart();exit;}
if(isset($_GET["ntpd-events"])){ntpd_events();exit;}

//zabix
if(isset($_GET["zabbix-restart"])){zabbix_restart();exit;}


//cyrus
if(isset($_GET["mailboxlist-domain"])){cyrus_mailboxlist_domain();exit;}
if(isset($_GET["mailboxlist"])){cyrus_mailboxlist();exit;}
if(isset($_GET["mailbox-delete"])){cyrus_mailboxdelete();exit;}
if(isset($_GET["DelMbx"])){delete_mailbox();exit;}
if(isset($_GET["cyrus-check-cyr-accounts"])){cyrus_check_cyraccounts();exit;}
if(isset($_GET["cyrus-reconfigure"])){cyrus_reconfigure();exit;}
if(isset($_GET["cyrus-get-partition-default"])){cyrus_paritition_default_path();exit;}
if(isset($_GET["cyrus-MoveDefaultToCurrentDir"])){cyrus_move_default_dir_to_currentdir();exit;}
if(isset($_GET["cyrus-SaveNewDir"])){cyrus_move_newdir();exit;}
if(isset($_GET["cyrus-rebuild-all-mailboxes"])){cyrus_rebuild_all_mailboxes();exit;}





//restore

if(isset($_GET["cyr-restore"])){cyrus_restore_mount_dir();exit;}
if(isset($_GET["cyr-restore-container"])){cyr_restore_container();;exit;}
if(isset($_GET["cyr-restore-mailbox"])){cyr_restore_mailbox();;exit;}


//WIFI

if(isset($_GET["wifi-ini-status"])){WIFI_INI_STATUS();exit;}
if(isset($_GET["wifi-connect-point"])){WIFI_CONNECT_AP();exit;}
if(isset($_GET["wifi-eth-status"])){WIFI_ETH_STATUS();exit;}
if(isset($_GET["wifi-eth-client-check"])){WIFI_ETH_CHECK();exit;}



//SQUID

if(isset($_GET["squid-status"])){SQUID_STATUS();exit;}
if(isset($_GET["squid-ini-status"])){SQUID_INI_STATUS();exit;}
if(isset($_GET["squid-restart-now"])){SQUID_RESTART_NOW();exit;}

if(isset($_GET["Sarg-Scan"])){SQUID_SARG_SCAN();exit;}
if(isset($_GET["squid-GetOrginalSquidConf"])){squid_originalconf();exit;}
if(isset($_GET["MalwarePatrol"])){MalwarePatrol();exit;}
if(isset($_GET["force-upgrade-squid"])){SQUID_FORCE_UPGRADE();exit;}
if(isset($_GET["squid-cache-infos"])){SQUID_CACHE_INFOS();exit;}


if(isset($_GET["reload-squidguard"])){SQUIDGUARD_RELOAD();exit;}
if(isset($_GET["squidguard-db-status"])){squidGuardDatabaseStatus();exit;}
if(isset($_GET["squidguard-status"])){squidGuardStatus();exit;}
if(isset($_GET["compile-squidguard-db"])){squidGuardCompile();exit;}
if(isset($_GET["squidguard-tests"])){squidguardTests();exit;}

if(isset($_GET["philesize-img"])){philesizeIMG();exit;}
if(isset($_GET["philesize-img-path"])){philesizeIMGPath();exit;}

//samba
if(isset($_GET["smblient"])){samba_smbclient();exit;}
if(isset($_GET["smb-logon-scripts"])){samba_logon_scripts();exit;}
if(isset($_GET["SAMBA-HAVE-POSIX-ACLS"])){SAMBA_HAVE_POSIX_ACLS();exit;}

if(isset($_GET["add-acl-group"])){samba_add_acl_group();exit;}
if(isset($_GET["add-acl-user"])){samba_add_acl_user();exit;}
if(isset($_GET["change-acl-user"])){samba_change_acl_user();exit;}
if(isset($_GET["change-acl-group"])){samba_change_acl_group();exit;}
if(isset($_GET["delete-acl-group"])){samba_delete_acl_group();exit;}
if(isset($_GET["delete-acl-user"])){samba_delete_acl_user();exit;}
if(isset($_GET["change-acl-items"])){samba_change_acl_items();exit;}



//postfix
if(isset($_GET["postfixQueues"])){postfixQueues();exit;}
if(isset($_GET["getMainCF"])){postfix_read_main();exit;}
if(isset($_GET["postfix-tail"])){postfix_tail();exit;}
if(isset($_GET["postfix-hash-tables"])){postfix_hash_tables();exit;}
if(isset($_GET["postfix-transport-maps"])){postfix_hash_transport_maps();exit;}
if(isset($_GET["postfix-hash-senderdependent"])){postfix_hash_senderdependent();exit;}
if(isset($_GET["postfix-hash-aliases"])){postfix_hash_aliases();exit;}
if(isset($_GET["postfix-bcc-tables"])){postfix_hash_bcc();exit;}
if(isset($_GET["postfix-others-values"])){postfix_others_values();exit;}
if(isset($_GET["postfix-mime-header-checks"])){postfix_mime_header_checks();exit;}
if(isset($_GET["postfix-interfaces"])){postfix_interfaces();exit;}




if(isset($_GET["ChangeLDPSSET"])){ChangeLDPSSET();exit;}
if(isset($_GET["ASSPOriginalConf"])){ASSPOriginalConf();exit;}
if(isset($_GET["SetupCenter"])){SetupCenter();exit;}
if(isset($_GET["restart-assp"])){RestartASSPService();exit;}
if(isset($_GET["reload-assp"])){ReloadASSPService();exit;}
if(isset($_GET["restart-mailgraph"])){RestartMailGraphService();exit;}
if(isset($_GET["restart-mysql"])){RestartMysqlDaemon();exit;}
if(isset($_GET["restart-openvpn-server"])){RestartOpenVPNServer();exit;}
if(isset($_GET["read-log"])){read_log();exit;}

//roundcube
if(isset($_GET["roundcube-restart"])){RoundCube_restart();exit;}
if(isset($_GET["roundcube-install-sieverules"])){RoundCube_sieverules();exit;}
if(isset($_GET["roundcube-install-calendar"])){RoundCube_calendar();exit;}
if(isset($_GET["roundcube-sync"])){RoundCube_sync();exit;}



//assp
if(isset($_GET["assp-multi-load-config"])){ASSP_MULTI_CONFIG();exit;}

//rsync
if(isset($_GET["rsync-reconfigure"])){rsync_reconfigure();exit;}


//mailman
if(isset($_GET["syncro-mailman"])){MailManSync();exit;}
if(isset($_GET["restart-mailman"])){RestartMailManService();exit;}
if(isset($_GET["MailMan-List"])){MailManList();exit;}
if(isset($_GET["mailman-delete"])){MailManDelete();exit;}
if(isset($_GET["MailManSaveGlobalSettings"])){MailManSync();exit;}

//DHCPD
if(isset($_GET["restart-dhcpd"])){RestartDHCPDService();exit;}

if(isset($_GET["MySqlPerf"])){MySqlPerf();exit;}
if(isset($_GET["mysql-audit"])){MysqlAudit();exit;}
if(isset($_GET["RestartDaemon"])){RestartDaemon();exit;}
if(isset($_GET["restart-apache-no-timeout"])){RestartApacheNow();exit;}

//network
if(isset($_GET["SaveNic"])){Reconfigure_nic();exit;}
if(isset($_GET["dnslist"])){DNS_LIST();exit;}
if(isset($_GET["ChangeHostName"])){ChangeHostName();exit;}

//WIFI
if(isset($_GET["iwlist"])){iwlist();exit;}
if(isset($_GET["start-wifi"])){start_wifi();exit;}

//imapSYnc

if(isset($_GET["imapsync-events"])){imapsync_events();exit;}
if(isset($_GET["imapsync-cron"])){imapsync_cron();exit;}
if(isset($_GET["imapsync-run"])){imapsync_run();exit;}
if(isset($_GET["imapsync-stop"])){imapsync_stop();exit;}
	
//cyrus
if(isset($_GET["cyrus-backup-now"])){CyrusBackupNow();exit;}
if(isset($_GET["restart-cyrus"])){RestartCyrusImapDaemon();exit;}
if(isset($_GET["reload-cyrus"])){ReloadCyrus();exit;}
if(isset($_GET["reconfigure-cyrus"])){ReconfigureCyrusImapDaemon();exit;} // --reconfigure-cyrus
if(isset($_GET["reconfigure-cyrus-debug"])){ReconfigureCyrusImapDaemonDebug();exit;} // --reconfigure-cyrus
if(isset($_GET["restart-cyrus-debug"])){rRestartCyrusImapDaemonDebug();exit;} // --reconfigure-cyrus
if(isset($_GET["repair-mailbox"])){CyrusRepairMailbox();exit;}
if(isset($_GET["cyr-restore-computer"])){cyr_restore_computer();exit;}

//backup
if(isset($_GET["backup-sql-test"])){backup_sql_tests();exit;}
if(isset($_GET["backup-build-cron"])){backup_build_cron();exit;}
if(isset($_GET["backup-task-run"])){backup_task_run();exit;}



//apache
if(isset($_GET["restart-groupware-server"])){RestartGroupwareWebServer();exit;}
if(isset($_GET["philesight-perform"])){philesight_perform();exit;}

//postfix
if(isset($_GET["headers-check-postfix"])){PostfixHeaderCheck();exit;}
if(isset($_GET["SaveMaincf"])){SaveMaincf();exit;}
if(isset($_GET["sasl-finger"])){SASL_FINGER();exit;}
if(isset($_GET["reconfigure-postfix"])){postfix_reconfigure();exit;}
if(isset($_GET["postfix-stat"])){postfix_stat();exit;}
if(isset($_GET["postfix-multi-queues"])){postfix_multi_queues();exit;}
if(isset($_GET["postfix-mutli-stat"])){postfix_multi_stat();exit;}
if(isset($_GET["postfix-multi-configure-ou"])){postfix_multi_configure();exit;}
if(isset($_GET["postfix-multi-disable"])){postfix_multi_disable();exit;}
if(isset($_GET["postfix-restricted-users"])){postfix_restricted_users();exit;}




//organizations
if(isset($_GET["upload-organization"])){ldap_upload_organization();exit;}


//cups
if(isset($_GET["cups-delete-printer"])){cups_delete_printer();exit;}
if(isset($_GET["cups-add-printer"])){cups_add_printer();exit;}

//samba
if(isset($_GET["samba-save-config"])){samba_save_config();exit;}
if(isset($_GET["samba-build-homes"])){samba_build_homes();exit;}
if(isset($_GET["restart-samba"])){samba_restart();exit;}
if(isset($_GET["Debugpdbedit"])){samba_pdbedit_debug();exit;}
if(isset($_GET["pdbedit"])){samba_pdbedit();exit;}
if(isset($_GET["samba-status"])){samba_status();exit;}
if(isset($_GET["samba-shares-list"])){samba_shares_list();exit;}
if(isset($_GET["samba-synchronize"])){samba_synchronize();exit;}
if(isset($_GET["samba-change-sid"])){samba_change_sid();exit;}
if(isset($_GET["samba-original-conf"])){samba_original_config();exit;}

if(isset($_GET["smbpass"])){samba_password();exit;}
if(isset($_GET["home-single-user"])){samba_build_home_single();exit;}


//squid;
if(isset($_GET["squidnewbee"])){squid_config();exit;}
if(isset($_GET["cicap-reconfigure"])){cicap_reconfigure();exit;}
if(isset($_GET["cicap-reload"])){cicap_reload();exit;}
if(isset($_GET["MalwarePatrolDatabasesCount"])){MalwarePatrolDatabasesCount();exit;}

if(isset($_GET["artica-filter-reload"])){ReloadArticaFilter();exit;}

if(isset($_GET["dirdir"])){dirdir();exit;}
if(isset($_GET["view-file-logs"])){ViewArticaLogs();exit;}
if(isset($_GET["ExecuteImportationFrom"])){ExecuteImportationFrom();exit;}
if(isset($_GET["squid-reconfigure"])){RestartSquid();exit;}
if(isset($_GET["mempy"])){mempy();exit;}
if(isset($_GET["build-vhosts"])){BuildVhosts();exit;}
if(isset($_GET["vhost-delete"])){DeleteVHosts();exit;}
if(isset($_GET["replicate-performances-config"])){ReplicatePerformancesConfig();exit;}
if(isset($_GET["reload-dansguardian"])){reload_dansguardian();exit;}
if(isset($_GET["dansguardian-template"])){dansguardian_template();exit;}
if(isset($_GET["searchww-cat"])){dansguardian_search_categories();exit;}
if(isset($_GET["export-community-categories"])){dansguardian_community_categories();exit;}


//disks
if(isset($_GET["disks-list"])){disks_list();exit;}
if(isset($_GET["usb-scan-write"])){shell_exec("/usr/share/artica-postfix/bin/artica-install --usb-scan-write");exit;}
if(isset($_GET["lvm-lvs"])){lvs_scan();exit;}
if(isset($_GET["sfdisk-dump"])){sfdisk_dump();exit;}
if(isset($_GET["mkfs"])){mkfs();exit;}
if(isset($_GET["parted-print"])){parted_print();exit;}
if(isset($_GET["format-disk-unix"])){format_disk_unix();exit;}
if(isset($_GET["lvs-mapper"])){LVM_LVS_DEV_MAPPER();exit;}
if(isset($_GET["check-dev"])){DEV_CHECK();}
if(isset($_GET["fstab-add"])){fstab_add();exit;}
if(isset($_GET["fstablist"])){fstab_list();exit;}
if(isset($_GET["path-acls"])){acls_infos();exit;}
if(isset($_GET["IsDir"])){IsDir();exit;}


// cmd.php?fstab-acl=yes&acl=$acl&dev=$dev
if(isset($_GET["fstab-acl"])){fstab_acl();exit;}
if(isset($_GET["fstab-remove"])){fstab_del();exit;}
if(isset($_GET["DiskInfos"])){DiskInfos();exit;}
if(isset($_GET["fstab-get-mount-point"])){fstab_get_mount_point();exit;}
if(isset($_GET["get-mounted-path"])){disk_get_mounted_point();exit;}
if(isset($_GET["fdisk-build-big-partitions"])){disk_format_big_partition();}




if(isset($_GET["umount-disk"])){umount_disk();exit;}
if(isset($_GET["lvremove"])){LVM_REMOVE();exit;}
if(isset($_GET["fdiskl"])){fdisk_list();exit;}
if(isset($_GET["lvmdiskscan"])){lvmdiskscan();exit;}
if(isset($_GET["pvscan"])){pvscan();exit;}
if(isset($_GET["vgs-info"])){LVM_VGS_INFO();exit;}
if(isset($_GET["vg-disks"])){LVM_VG_DISKS();exit;}
if(isset($_GET["lvm-unlink-disk"])){LVM_UNLINK_DISK();exit;}
if(isset($_GET["lvm-link-disk"])){LVM_LINK_DISK();exit;}
if(isset($_GET["vgcreate-dev"])){LVM_CREATE_GROUP();exit;}
if(isset($_GET["DirectorySize"])){disk_directory_size();exit;}


if(isset($_GET["lvs-all"])){LVM_lVS_INFO_ALL();exit;}
if(isset($_GET["lv-resize-add"])){LVM_LV_ADDSIZE();exit;}
if(isset($_GET["lv-resize-red"])){LVM_LV_DELSIZE();exit;}
if(isset($_GET["disk-ismounted"])){disk_ismounted();exit;}

if(isset($_GET["filesize"])){file_size();exit;}
if(isset($_GET["filetype"])){file_type();exit;}
if(isset($_GET["mime-type"])){mime_type();exit;}

if(isset($_GET["sync-remote-smtp-artica"])){postfix_sync_artica();exit;}

//etc/hosts
if(isset($_GET["etc-hosts-open"])){etc_hosts_open();exit;}
if(isset($_GET["etc-hosts-add"])){etc_hosts_add();exit;}
if(isset($_GET["etc-hosts-del"])){etc_hosts_del();exit;}
if(isset($_GET["full-hostname"])){hostname_full();exit;}

//tcp
if(isset($_GET["ifconfig-interfaces"])){ifconfig_interfaces();exit;}
if(isset($_GET["ifconfig-all"])){ifconfig_all();exit;}
if(isset($_GET["resolv-conf"])){resolv_conf();exit;}
if(isset($_GET["myos"])){MyOs();exit;}
if(isset($_GET["lspci"])){lspci();exit;}
if(isset($_GET["freemem"])){freemem();exit;}
if(isset($_GET["dfmoinsh"])){dfmoinsh();exit;}
if(isset($_GET["printenv"])){printenv();exit;}
if(isset($_GET["GenerateCert"])){GenerateCert();exit;}
if(isset($_GET["all-status"])){GLOBAL_STATUS();exit;}
if(isset($_GET["procstat"])){procstat();exit;}




if(isset($_GET["arp-ip"])){arp_and_ip();exit;}
if(isset($_GET["browse-computers-import-list"])){import_computer_from_list();exit;}




if(isset($_GET["refresh-status"])){RefreshStatus();exit;}

if(isset($_GET["SpamassassinReload"])){reloadSpamAssassin();exit;}
if(isset($_GET["SpamAssassin-Reload"])){reloadSpamAssassin();exit;}


if(isset($_GET["SetupIndexFile"])){SetupIndexFile();exit;}
if(isset($_GET["install-web-services"])){InstallWebServices();exit;}
if(isset($_GET["ForceRefreshLeft"])){ForceRefreshLeft();exit;}
if(isset($_GET["ForceRefreshRight"])){ForceRefreshRight();exit;}


if(isset($_GET["aptupgrade"])){AptGetUpgrade();exit;}
if(isset($_GET["perform-autoupdate"])){artica_update();exit;}


if(isset($_GET["SmtpNotificationConfigRead"])){SmtpNotificationConfigRead();exit;}
if(isset($_GET["testnotif"])){testnotif();exit;}
if(isset($_GET["ComputerRemoteRessources"])){ComputerRemoteRessources();exit;}
if(isset($_GET["free-cache"])){FreeCache();exit;}
if(isset($_GET["DumpPostfixQueue"])){DumpPostfixQueue();exit;}
if(isset($_GET["smtp-whitelist"])){SMTP_WHITELIST();exit;}
if(isset($_GET["LaunchNetworkScanner"])){LaunchNetworkScanner();exit;}
if(isset($_GET["idofUser"])){idofUser();exit;}
if(isset($_GET["php-rewrite"])){rewrite_php();exit;}

if(isset($_GET["B64-dirdir"])){dirdirBase64();exit;}
if(isset($_GET["Dir-Files"])){Dir_Files();exit;}
if(isset($_GET["filestat"])){filestat();exit;}
if(isset($_GET["create-folder"])){folder_create();exit;}
if(isset($_GET["folder-remove"])){folder_delete();exit;}
if(isset($_GET["file-content"])){file_content();exit;}


//CLUSTERS
if(isset($_GET["notify-clusters"])){CLUSTER_NOTIFY();exit;}
if(isset($_GET["cluster-restart-notify"])){CLUSTER_CLIENT_RESTART_NOTIFY();exit;}
if(isset($_GET["cluster-client-list"])){CLUSTER_CLIENT_LIST();exit;}
if(isset($_GET["cluster-delete"])){CLUSTER_DELETE();exit;}
if(isset($_GET["cluster-add"])){CLUSTER_ADD();exit;}

//computers
if(isset($_GET["computers-import-nets"])){COMPUTERS_IMPORT_ARTICA();exit;}

//paths 
if(isset($_GET["SendmailPath"])){SendmailPath();exit;}
if(isset($_GET["release-quarantine"])){release_quarantine();exit;}

//policyd-weight
if(isset($_GET["PolicydWeightReplicConF"])){Restart_Policyd_Weight();exit;}

//dansguardian
if(isset($_GET["dansguardian-update"])){dansguardian_update();exit;}
if(isset($_GET["shalla-update-now"])){shalla_update();exit;}

$uri=$_GET["uri"];

switch ($uri) {
	case "GlobalApplicationsStatus":GlobalApplicationsStatus();exit;break;
	case "artica_version":artica_version();exit;break;
	case "daemons_status":daemons_status();exit;break;
	case "pid":echo "<articadatascgi>".getmypid()."</articadatascgi>";exit;break;
	case "myhostname";myhostname();exit;break;
	
	default:
		;
	break;
}

writelogs_framework("unable to understand query !!!!!!!!!!!...","main()",__FILE__,__LINE__);
die();

function SMTP_WHITELIST(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/postfix.whitelist.php");
}

function artica_update_query_fileslogs(){
	$unix=new unix();
	$array=$unix->DirFiles("/var/log/artica-postfix");
	echo "<articadatascgi>".base64_encode(serialize($array))."</articadatascgi>";	
}
function artica_update_query_logs(){
	$array=explode("\n",@file_get_contents("/var/log/artica-postfix/{$_GET["file"]}"));
	echo "<articadatascgi>".base64_encode(serialize($array))."</articadatascgi>";	
}

function acls_infos(){
	$unix=new unix();
	$path=base64_decode($_GET["path-acls"]);
	$getfacl=$unix->find_program("getfacl");
	if($getfacl==null){return false;}
	exec("$getfacl --tabular \"$path\"",$results);
	
	while (list ($num, $line) = each ($results)){
		if(preg_match("#USER\s+(.+?)\s+(.*)#",$line,$re)){
			$array["OWNER"]=array("NAME"=>$re[1],"RIGHTS"=>$re[2]);
			continue;
		}
		
		if(preg_match("#GROUP\s+(.+?)\s+(.*)#",$line,$re)){
			$array["GROUP"]=array("NAME"=>$re[1],"RIGHTS"=>$re[2]);
			continue;
		}	

		if(preg_match("#other\s+(.+?)\s+(.*)#",$line,$re)){
			$array["other"]=array("NAME"=>$re[1],"RIGHTS"=>$re[2]);
			continue;
		}			

		if(preg_match("#user\s+(.+?)\s+\s+(.*)#",$line,$re)){
			$array["users"][]=array("NAME"=>$re[1],"RIGHTS"=>$re[2]);
			continue;
		}
		
		if(preg_match("#group\s+(.+?)\s+\s+(.*)#",$line,$re)){
			$array["groups"][]=array("NAME"=>$re[1],"RIGHTS"=>$re[2]);
			continue;
		}		

		
	}
	
	echo "<articadatascgi>".base64_encode(serialize($array))."</articadatascgi>";
	
}

function IsDir(){
	$_GET["IsDir"]=base64_decode($_GET["IsDir"]);
	if(is_dir($_GET["IsDir"])){
		echo "<articadatascgi>".base64_encode("TRUE")."</articadatascgi>";
	}
}


function file_size(){
	$unix=new unix();
	
	exec($unix->find_program("stat")." {$_GET["filesize"]} ",$results);
	while (list ($num, $line) = each ($results)){
		if(preg_match("#Size:\s+([0-9]+)\s+Blocks#",$line,$re)){
			$res=$re[1];break;
		}
	}
	echo "<articadatascgi>$res</articadatascgi>";	
}

function file_type(){
$unix=new unix();
$filetype=base64_decode($_GET["filetype"]);
	exec($unix->find_program("file")." \"$filetype\" ",$results);	
while (list ($num, $line) = each ($results)){
		if(preg_match("#.+?:\s+(.+?)$#",$line,$re)){
			$res=$re[1];break;
		}
	}
	echo "<articadatascgi>".base64_encode($res)."</articadatascgi>";	
}

function mime_type(){
$unix=new unix();
$filetype=base64_decode($_GET["mime-type"]);
	exec($unix->find_program("file")." -i -b \"$filetype\" ",$results);	
while (list ($num, $line) = each ($results)){
		if(preg_match("#.+?;.+?$#",$line,$re)){
			$res=$line;break;
		}
	}
	echo "<articadatascgi>".base64_encode($res)."</articadatascgi>";	
}	


function COMPUTERS_IMPORT_ARTICA(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.import-networks.php");
	
}
function ReloadCyrus(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reload-cyrus");
}

function import_computer_from_list(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.computer.scan.php --import-list");
}

function format_disk_unix(){
	$logs=md5($_GET["format-disk-unix"]);
	@unlink("/usr/share/artica-postfix/ressources/logs/$logs.format");
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --format-disk-unix {$_GET["format-disk-unix"]} --verbose >/usr/share/artica-postfix/ressources/logs/$logs.format 2>&1");
}
function read_log(){
	if(!is_file("/usr/share/artica-postfix/ressources/logs/{$_GET["read-log"]}")){
		writelogs_framework("unable to stat /usr/share/artica-postfix/ressources/logs/{$_GET["read-log"]}");
		return;
	}
echo "<articadatascgi>". @file_get_contents("/usr/share/artica-postfix/ressources/logs/{$_GET["read-log"]}")."</articadatascgi>";	
}


function StartServiceCMD(){
	$cmd=$_GET["start-service-name"];
	exec("/etc/init.d/artica-postfix start $cmd",$results);
	$datas=base64_encode(serialize($results));
	echo "<articadatascgi>$datas</articadatascgi>";	
}
function StopServiceCMD(){
	$cmd=$_GET["stop-service-name"];
	exec("/etc/init.d/artica-postfix stop $cmd",$results);
	$datas=base64_encode(serialize($results));
	echo "<articadatascgi>$datas</articadatascgi>";	
}

function StartAllServices(){
	$unix=new unix();
	$d=$unix->ServicesCMDArray();
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix start");
	while (list ($num, $cmd) = each ($d)){
		sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix start $cmd");	
	}
	
	
}
function DEV_CHECK(){
	$dev=$_GET["check-dev"];
	if(is_link($dev)){
		$link=readlink($dev);
		$dev=str_replace("../mapper","/dev/mapper",$link);
		echo "<articadatascgi>$dev</articadatascgi>";
	}else{
		echo "<articadatascgi>$dev</articadatascgi>";
	}
}

function SQUID_STATUS(){
	exec("/usr/share/artica-postfix/bin/artica-install --squid-status",$results);
	echo "<articadatascgi>". implode("\n",$results)."</articadatascgi>";
}
function SQUID_INI_STATUS(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --all-squid",$results);
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";
}
function WIFI_INI_STATUS(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --wifi",$results);
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";	
}


function SQUID_FORCE_UPGRADE(){
	sys_THREAD_COMMAND_SET( "/usr/share/artica-postfix/bin/artica-make APP_SQUID --reconfigure");
}

function artica_update(){
	sys_THREAD_COMMAND_SET( "/usr/share/artica-postfix/bin/artica-update --update --force");
}

function SQUID_SARG_SCAN(){
	$unix=new unix();
	$sarg=$unix->find_program("sarg");
	if(!is_file($sarg)){return null;}
	exec("/usr/share/artica-postfix/bin/artica-install --sarg-scan",$results);
	$datas=base64_encode(serialize($results));
	echo "<articadatascgi>$datas</articadatascgi>";
	
}

function disk_ismounted(){
	$unix= new unix();
	$dev=$_GET["dev"];
	if(is_link($dev)){
		$link=@readlink($dev);
		$dev2=str_replace("../mapper","/dev/mapper",$link);
	}
	writelogs_framework("$dev OR $dev2 ",__FUNCTION__,__FILE__,__LINE__);
	if(!$unix->DISK_MOUNTED($dev)){
		if($dev2<>null){
			if($unix->DISK_MOUNTED($dev2)){
				echo "<articadatascgi>TRUE</articadatascgi>";
				return ;
			}
		}
	}else{
		echo "<articadatascgi>TRUE</articadatascgi>";
		return;
	}
	
	echo "<articadatascgi>FALSE</articadatascgi>";
	
}


function rsync_reconfigure(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.rsync-lvm.php");
}

function DeleteVHosts(){
	$unix=new unix();
	$tmp=$unix->FILE_TEMP();
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.www.install.php remove {$_GET["vhost-delete"]} --verbose >$tmp 2>&1");
	echo "<articadatascgi>". @file_get_contents($tmp)."</articadatascgi>";
	@unlink($tmp);
	
}

function fstab_add(){
	$dev=$_GET["dev"];
	$mount=$_GET["mount"];
	$unix=new unix();
	writelogs_framework("Add Fstab $dev -> $mount ",__FUNCTION__,__FILE__,__LINE__);
	$unix->AddFSTab($dev,$mount);
	
}

function fstab_del(){
	$dev=$_GET["dev"];
	$unix=new unix();
	$unix->DelFSTab($dev);
}

function fstab_get_mount_point(){
	$dev=$_GET["dev"];
	$unix=new unix();
	$datas=$unix->GetFSTabMountPoint($dev);
	writelogs_framework(count($datas)." mounts points",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>".base64_encode(serialize($datas));
	echo "</articadatascgi>";	
}

function DiskInfos(){
	
	$dev=$_GET["DiskInfos"];
	$unix=new unix();
	exec($unix->find_program("df")." -h $dev",$results);
while (list ($num, $line) = each ($results)){
		if(preg_match("#(.+?)\s+([0-9-A-Z,\.]+)\s+([0-9-A-Z,\.]+)\s+([0-9-A-Z,\.]+)\s+([0-9,\.]+)%\s+(.+)$#i",$line,$re)){
			if($re[6]=="/dev"){continue;}
			$array["SIZE"]=$re[2];
			$array["USED"]=$re[3];
			$array["FREE"]=$re[4];
			$array["POURC"]=$re[5];
			$array["MOUNTED"]=$re[6];
			echo "<articadatascgi>".base64_encode(serialize($array))."</articadatascgi>";
			break;
		}
	}	
	
}

function fstab_list(){
	$datas=explode("\n",@file_get_contents("/etc/fstab"));
	echo "<articadatascgi>".base64_encode(serialize($datas))."</articadatascgi>";
}

function ViewArticaLogs(){
	$datas=@file_get_contents("/var/log/artica-postfix/{$_GET["view-file-logs"]}");
	echo "<articadatascgi>$datas</articadatascgi>";
	}
	
function ExecuteImportationFrom(){
	$path=$_GET["ExecuteImportationFrom"];
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.cyrus-restore.php \"$path\"");
}
function LaunchNetworkScanner(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.scan-networks.php");
}

function CLUSTER_NOTIFY(){
	$server=$_GET["notify-clusters"];
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.gluster.php --notify-client $server");
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.gluster.php");
}
function CLUSTER_CLIENT_RESTART_NOTIFY(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.gluster.php --cluster-restart-notify");
}

function RoundCube_restart(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart roundcube");
}
function RoundCube_sieverules(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.roundcube.php --sieverules");
}

function RoundCube_sync(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.roundcube.php");
}



function RoundCube_calendar(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.roundcube.php --calendar");
}
function opengoo_user(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.opengoo.php --user={$_GET["opengoouid"]}");
}
function groupoffice_user(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.GroupOffice.php --user={$_GET["GroupOfficeUid"]}");
}
function ntpd_restart(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart ntpd");
}

	
function ntpd_events(){
	$unix=new unix();
	$syslog=$unix->LOCATE_SYSLOG_PATH();
	$tmpf=$unix->FILE_TEMP();
	$cmd=$unix->find_program("tail")." -n 5000 $syslog|". $unix->find_program("grep")." ntpd >$tmpf 2>&1";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	$results=explode("\n",@file_get_contents($tmpf));
	@unlink($tmpf);
	writelogs_framework(count($results),__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
}
function ReloadArticaFilter(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --artica-filter --reload");
}

function Reconfigure_nic(){
	
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reconfigure-nic {$_GET["SaveNic"]} {$_GET["ip"]} {$_GET["net"]} {$_GET["gw"]} {$_GET["dhcp"]}");
}

function postfix_sync_artica(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.smtp.export.users.php --sync");
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --reconfigure");
}




function CLUSTER_CLIENT_LIST(){
	$unix=new unix();
	
	$files=$unix->DirFiles("/etc/artica-cluster");
while (list ($num, $path) = each ($files)){
		if(preg_match("#clusters-(.+)#",$path,$re)){
			$ff[]="{$re[1]}";
		}
	}
	if(is_array($ff)){
		echo "<articadatascgi>" . implode("\n",$ff)."</articadatascgi>";
	}
}

function CLUSTER_DELETE(){
	$server=$_GET["cluster-delete"];
	@unlink("/etc/artica-cluster/clusters-$server");
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.gluster.php --notify-all-clients");
	
}
function CLUSTER_ADD(){
	$server=$_GET["cluster-add"];
	@file_put_contents("/etc/artica-cluster/clusters-$server","#");
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.gluster.php --notify-all-clients");
	
}

function mempy(){
	shell_exec("/usr/share/artica-postfix/bin/ps_mem.py >/tmp/mempy.txt 2>&1");
	echo "<articadatascgi>". @file_get_contents("/tmp/mempy.txt")."</articadatascgi>";
}

function SmtpNotificationConfigRead(){
	$datas=trim(@file_get_contents("/etc/artica-postfix/smtpnotif.conf"));
	echo "<articadatascgi>$datas</articadatascgi>";
}

function safebox_mount(){
	if(is_file("/var/log/artica-postfix/safebox.{$_GET["uid"]}.debug")){@unlink("/var/log/artica-postfix/safebox.{$_GET["uid"]}.debug");}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.safebox.php --init {$_GET["uid"]}");
}
function safebox_umount(){
	if(is_file("/var/log/artica-postfix/safebox.{$_GET["uid"]}.debug")){@unlink("/var/log/artica-postfix/safebox.{$_GET["uid"]}.debug");}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.safebox.php --umount {$_GET["uid"]}");
}
function safebox_check(){
	if(is_file("/var/log/artica-postfix/safebox.{$_GET["uid"]}.debug")){@unlink("/var/log/artica-postfix/safebox.{$_GET["uid"]}.debug");}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.safebox.php --fsck {$_GET["uid"]}");	
}

function safe_box_set_user(){
	if($_GET["uid"]==null){writelogs_framework("no user set",__FUNCTION__,__FILE__,__LINE__);}
	if(is_file("/var/log/artica-postfix/safebox.{$_GET["uid"]}.debug")){@unlink("/var/log/artica-postfix/safebox.{$_GET["uid"]}.debug");}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.safebox.php --init {$_GET["uid"]}");
}
function safebox_logs(){
	$uid=$_GET["uid"];
	if(!is_file("/var/log/artica-postfix/safebox.$uid.debug")){
	writelogs_framework("unable to stat /var/log/artica-postfix/safebox.$uid.debug",__FUNCTION__,__FILE__,__LINE__);
	}
	$f=@file_get_contents("/var/log/artica-postfix/safebox.$uid.debug");
	$datas=explode("\n",$f);
	writelogs_framework(count($datas)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($datas))."</articadatascgi>";
}


function CyrusBackupNow(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-backup --single-cyrus \"{$_GET["cyrus-backup-now"]}\"");
}

function Refresh_frontend(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.smtp.flow.status.php --force");
}

function cyrus_mailboxlist_domain(){
	exec("/usr/share/artica-postfix/bin/artica-install --mailboxes-domain {$_GET["mailboxlist-domain"]}",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}
function RepairArticaLdapBranch(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-backup --repair-artica-branch");
}

function cyrus_mailboxlist(){
	exec("/usr/share/artica-postfix/bin/artica-install --mailboxes-list",$results);
	writelogs_framework(count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
}

function cyrus_mailboxdelete(){
	exec("/usr/share/artica-postfix/bin/artica-install --mailbox-delete {$_GET["mailbox-delete"]}",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";		
}

function cyrus_check_cyraccounts(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reload-cyrus");
}
function cyrus_reconfigure(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --cyrus-checkconfig --force");
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart imap");
	}
function cyrus_paritition_default_path(){
	$unix=new unix();
	
	echo "<articadatascgi>". base64_encode($unix->IMAPD_GET("partition-default"))."</articadatascgi>";
	
}

function CyrusRepairMailBox(){
	$uid=$_GET["repair-mailbox"];
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.cyrus-repair-mailbox.php $uid");
//cmd:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap --repair-mailbox '+RegExpr.Match[1]+' '+GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/cyr.repair.'+RegExpr.Match[1];
	}


function InstallWebServices(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.www.install.php");
}
function AptGetUpgrade(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.apt-get.php --upgrade");
}

function MailManSync(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.mailman.php");
}

function RefreshStatus(){
	sys_THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --generate-status');
}

function ForceRefreshLeft(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.status.postfix.flow.php --services");	
}
function ForceRefreshRight(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.smtp.flow.status.php --services");	
}

function Global_Applications_Status(){
	
		if(isset($_GET["status-forced"])){
			sys_THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --generate-status --forced');
			return;
		}
	
         if(!is_file('/usr/share/artica-postfix/ressources/logs/global.versions.conf')){ 
             sys_exec('/usr/share/artica-postfix/bin/artica-install -versions > /usr/share/artica-postfix/ressources/logs/global.versions.conf 2>&1');
         }
      
        if(!is_file('/usr/share/artica-postfix/ressources/logs/global.status.ini')){ 
            sys_exec('/usr/share/artica-postfix/bin/artica-install --status > /usr/share/artica-postfix/ressources/logs/global.status.ini 2>&1');
         }

         $datas=@file_get_contents('/usr/share/artica-postfix/ressources/logs/global.status.ini');
         $datas2=@file_get_contents('/usr/share/artica-postfix/ressources/logs/global.versions.conf');

         if (file_time_min('/usr/share/artica-postfix/ressources/logs/global.status.ini')>10){
            @unlink('/usr/share/artica-postfix/ressources/logs/global.status.ini');
            @unlink('/usr/share/artica-postfix/ressources/logs/global.versions.conf');
            sys_THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --write-versions');
            sys_THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --generate-status');
         }
		
        $unix=new unix(); 
		$tmp=$unix->FILE_TEMP();
        exec("/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/global.* >$tmp 2>&1");
		@unlink($tmp);
		writelogs_framework("datas=".strlen($datas)." bytes datas2=".strlen($datas)." bytes",__FUNCTION__,__FILE__,__LINE__);;
        echo "<articadatascgi>". base64_encode("$datas\n$datas2")."</articadatascgi>";
	}
	
function artica_version(){
	$datas=@file_get_contents("/usr/share/artica-postfix/VERSION");
	if(trim($datas)==null){$datas="0.00";}
	echo "<articadatascgi>$datas</articadatascgi>";
	  
}
function daemons_status(){

      if(!is_file('/usr/share/artica-postfix/ressources/logs/global.status.ini')){ 
            sys_exec('/usr/share/artica-postfix/bin/artica-install --status > /usr/share/artica-postfix/ressources/logs/global.status.ini 2>&1');
         }

      if(is_file('/usr/share/artica-postfix/ressources/logs/global.status.ini')){ 
            $datas=@file_get_contents("/usr/share/artica-postfix/ressources/logs/global.status.ini");
            echo "<articadatascgi>$datas</articadatascgi>";
            return ;
        }       
}

function myhostname(){
if($_SESSION["FRAMEWORK"]["myhostname"]<>null){echo $_SESSION["FRAMEWORK"]["myhostname"];exit;}
	$datas=sys_hostname_g();
	$_SESSION["FRAMEWORK"]["myhostname"]="<articadatascgi>$datas</articadatascgi>";
	sys_events(basename(__FILE__)."::{$_SERVER['REMOTE_ADDR']}:: myhostname ($datas)");
	echo $_SESSION["FRAMEWORK"]["myhostname"];
}

function SmtpNotificationConfig(){
	@copy("/etc/artica-postfix/settings/Daemons/SmtpNotificationConfig","/etc/artica-postfix/smtpnotif.conf");
}
function SaveMaincf(){
	$php=LOCATE_PHP5_BIN2();
	shell_exec("/etc/init.d/artica-postfix start daemon &");
	sys_THREAD_COMMAND_SET("$php /usr/share/artica-postfix/exec.postfix.maincf.php --reconfigure");	
}

function SaveConfigFile(){
	$file="/usr/share/artica-postfix/ressources/conf/{$_GET["SaveConfigFile"]}";
	if(!is_file($file)){
		writelogs_framework("read user-backup/ressources/conf/{$_GET["SaveConfigFile"]} ?",__FUNCTION__,__FILE__,__LINE__);
		if(is_file("/usr/share/artica-postfix/user-backup/ressources/conf/{$_GET["SaveConfigFile"]}")){
			$file="/usr/share/artica-postfix/user-backup/ressources/conf/{$_GET["SaveConfigFile"]}";
		}
	}
	
	if(!is_file($file)){
		writelogs_framework("Unable to stat {$_GET["SaveConfigFile"]} ",__FUNCTION__,__FILE__,__LINE__);
		return;
	}

	$key=$_GET["key"];
	$datas=file_get_contents($file);
	writelogs_framework("read $file ". strlen($datas)." lenght",__FUNCTION__,__FILE__,__LINE__);
	file_put_contents("/etc/artica-postfix/settings/Daemons/$key",$datas);
	writelogs_framework("Saving /etc/artica-postfix/settings/Daemons/$key (". strlen($datas)." bytes)",__FUNCTION__,__FILE__,__LINE__);
	unlink($file);
	unset($_SESSION["FRAMEWORK"]);
	}
	
function SaveClusterConfigFile(){
	$file="/usr/share/artica-postfix/ressources/conf/{$_GET["SaveClusterConfigFile"]}";
	$key=$_GET["key"];
	$datas=@file_get_contents($file);
	sys_events("read $file");
	@mkdir("/etc/artica-cluster");
	@file_put_contents("/etc/artica-cluster/$key",$datas);
	sys_events("Saving /etc/artica-cluster/$key (". strlen($datas)." bytes)");
	@unlink($file);
	unset($_SESSION["FRAMEWORK"]);		
}

function LaunchRemoteInstall(){
	$php=LOCATE_PHP5_BIN2();
	sys_THREAD_COMMAND_SET("$php /usr/share/artica-postfix/exec.remote-install.php");
}
function RestartWebServer(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart apache");
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart apache-groupware");
}
function RestartMailManService(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart mailman");	
}
function samba_restart(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart samba");	
}

function samba_synchronize(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.samba.synchronize.php");
}

function samba_save_config(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.samba.php --reconfigure");
}

function samba_original_config(){
	$datas=@file_get_contents("/etc/samba/smb.conf");
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";	
}




function samba_build_homes(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.samba.php --homes");
}
function samba_build_home_single(){
	$uid=base64_decode($_GET["home-single-user"]);
	if($uid==null){return;}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.samba.php --home \"$uid\"");
}



function samba_change_sid(){
	$unix=new unix();
	$sid=$_GET["samba-change-sid"];
	shell_exec($unix->find_program("net")." setlocalsid $sid");
	shell_exec("/usr/share/artica-postfix/bin/process1 --force");
}
function samba_password(){
	$password=base64_decode($_GET["smbpass"]);
	$file="/usr/share/artica-postfix/bin/install/smbldaptools/smbencrypt";
	$unix=new unix();
	$tmp=$unix->FILE_TEMP();
	$cmd="$file $password >$tmp 2>&1";
	shell_exec($cmd);
	$results=explode("\n",@file_get_contents($tmp));
	@unlink($tmp);
	writelogs_framework("SambaLoadpasswd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	
	
	echo "<articadatascgi>". base64_encode(implode(" ",$results))."</articadatascgi>";
	
}

function samba_status(){
	exec("/usr/share/artica-postfix/bin/artica-install --samba-status",$results);
	$datas=implode("\n",$results);
	echo "<articadatascgi>$datas</articadatascgi>";	
	
}

function samba_shares_list(){
	$ini=new Bs_IniHandler("/etc/samba/smb.conf");
	while (list($num,$array)=each($ini->_params)){
		if(trim($array["path"])==null){continue;}
		if(!is_dir(trim($array["path"]))){continue;}
			$results[]=$array["path"];
	}
	
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
}



function samba_pdbedit(){
	$user=$_GET["pdbedit"];
	$unix=new unix();
	$cmd=$unix->find_program("pdbedit")." -Lv $user -s /etc/samba/smb.conf";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}

function samba_pdbedit_debug(){
	$user=$_GET["Debugpdbedit"];
	$unix=new unix();
	$cmd=$unix->find_program("pdbedit")." -Lv -d 10 $user -s /etc/samba/smb.conf";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	
}



function arp_and_ip(){
	$computer_name=$_GET["arp-ip"];
	if($computer_name==null){return;}
	
	$ip=gethostbyname($computer_name);
	writelogs_framework("gethostbyname -> $computer_name = $ip",__FUNCTION__,__FILE__,__LINE__);
	if($ip==$computer_name){return null;}
	$unix=new unix();
	$arp=$unix->find_program("arp");
	exec("$arp $ip",$ri);

while (list ($num, $line) = each ($ri)){
		if(!preg_match("#.+?\s+.+?\s+(.+?)\s+#",$line,$re)){
			continue;}
			$arp_mac=strtolower(trim($re[1]));
			if(strtolower($arp_mac)==strtolower("HWaddress")){continue;}
			break;
		
	}	
	
	
	
	echo "<articadatascgi>".  base64_encode(serialize(array($ip,$arp_mac)))."</articadatascgi>";	
	}


function RestartGroupwareWebServer(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart apache");
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart apache-groupware");
}


function RestartASSPService(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart assp");	
}
function ReloadASSPService(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reload-assp");	
}
function rewrite_php(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --php-include");	
}

function RestartDHCPDService(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart dhcp");		
}


function RestartMailGraphService(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart mailgraph");		
}
function RestartDaemon(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart daemon");
}
function RestartFetchmail(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart fetchmail");
}

function SQUIDGUARD_RELOAD(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squidguard.php --build --reload");
}


function RestartSquid(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart squid");
}
function RestartMysqlDaemon(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart mysql");
}

function RestartOpenVPNServer(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart openvpn");
}

function RestartCyrusImapDaemon(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart imap");
}

function rRestartCyrusImapDaemonDebug(){
	exec("/etc/init.d/artica-postfix restart imap --verbose",$results);
	$a=serialize($results);
	echo "<articadatascgi>". base64_encode($a)."</articadatascgi>";		
}

function ReconfigureCyrusImapDaemon(){
	if(isset($_GET["force"])){$force=" --force";}
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reconfigure-cyrus$force");
}
function ReconfigureCyrusImapDaemonDebug(){
	exec("/usr/share/artica-postfix/bin/artica-install --reconfigure-cyrus --force --verbose",$results);
	$a=serialize($results);
	echo "<articadatascgi>". base64_encode($a)."</articadatascgi>";		
}

function reload_dansguardian(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squidguard.php --build");
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reload-dansguardian");
	}
function delete_mailbox(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --delete-mailbox {$_GET["DelMbx"]}");
}

function umount_disk(){
	$mount=$_GET["umount-disk"];
	$unix=new unix();
	writelogs_framework("umount $mount",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($unix->find_program("umount")." -l $mount");
}

function fdisk_list(){
	$unix=new unix();
	exec($unix->find_program("fdisk")." -l",$results);
	if(!is_array($results)){return null;}
	
	while (list ($num, $path) = each ($results)){
		if(preg_match("#Disk\s+(.+?):\s+([0-9\.]+)\s+([A-Z]+),#",$path,$re)){
			$array[trim($re[1])]=trim($re[2]." ".$re[3]);
		}else{
			
		}
	}
	writelogs_framework(count($array)." disks found",__FUNCTION__,__FILE__,__LINE__);
	$a=serialize($array);
	echo "<articadatascgi>". base64_encode($a)."</articadatascgi>";	
}

function lvmdiskscan(){
	$unix=new unix();
	exec($unix->find_program("lvmdiskscan")." -l",$results);
	if(!is_array($results)){return null;}	
	///dev/sda2                [      148.95 GB] LVM physical volume
	while (list ($num, $path) = each ($results)){
		if(preg_match("#(.+?)\s+\[(.+?)\]\s+#",$path,$re)){
			$array[trim($re[1])]=trim($re[2]);
		}else{
			
		}
	}
writelogs_framework(count($array)." disks found",__FUNCTION__,__FILE__,__LINE__);
	$a=serialize($array);
	echo "<articadatascgi>". base64_encode($a)."</articadatascgi>";			
}

function pvscan(){
$unix=new unix();
	exec($unix->find_program("pvscan")." -u",$results);	
if(!is_array($results)){return null;}	
	while (list ($num, $path) = each ($results)){
		if(preg_match("#PV\s+(.+?)\s+with\s+UUID\s+(.+?)\s+VG\s+(.+?)\s+lvm[0-9]\s+\[([0-9,\.]+)\s+([A-Z]+).+?([0-9,\.]+)\s+([A-Z]+)#",$path,$re)){
			$array[trim($re[1])]=array("VG"=>trim($re[3]),"SIZE"=>trim($re[4])." ".trim($re[5]),"UUID"=>trim($re[2]),"FREE"=>trim($re[6])." ".trim($re[7]));
		}
	}
writelogs_framework(count($array)." disks found",__FUNCTION__,__FILE__,__LINE__);
	$a=serialize($array);
	echo "<articadatascgi>". base64_encode($a)."</articadatascgi>";			
	
	
}

function LVM_VG_DISKS(){
	$unix=new unix();
	exec($unix->find_program("pvdisplay")." -c",$results);	
	if(!is_array($results)){return null;}
	while (list ($num, $line) = each ($results)){
		$tb=explode(":",$line);
		
		$size=round(($tb[2]/2048)/1000);
		$array[$tb[1]][]=array($tb[0],$size);
		
	}
	
	$a=serialize($array);
	echo "<articadatascgi>". base64_encode($a)."</articadatascgi>";		
}

function LVM_UNLINK_DISK(){
	$groupname=$_GET["groupname"];
	$dev=$_GET["dev"];
	$unix=new unix();
	$cmd=$unix->find_program("vgreduce")." $groupname $dev";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);	
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	  
	
}
function LVM_LINK_DISK(){
	$groupname=$_GET["groupname"];
	$dev=$_GET["dev"];
	$unix=new unix();
	$tmpstr=$unix->FILE_TEMP();
	$cmd=$unix->find_program("vgextend")." $groupname $dev";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("$cmd >$tmpstr 2>&1");
	$results=explode("\n",@file_get_content($tmpstr));
	$results[]="$cmd";
	$results[]="$dev -> $groupname";	
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	  
	
}

function LVM_CREATE_GROUP(){
	$groupname=$_GET["groupname"];
	$dev=$_GET["dev"];
	exec("/usr/share/artica-postfix/bin/artica-install --vgcreate-dev $dev $groupname",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}



function ChangeMysqlLocalRoot(){
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/ChangeMysqlLocalRoot","{scheduled}");
	@chmod("/usr/share/artica-postfix/ressources/logs/ChangeMysqlLocalRoot",0755);
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --change-mysqlroot --inline {$_GET["ChangeMysqlLocalRoot"]} {$_GET["password"]} --verbose >>/usr/share/artica-postfix/ressources/logs/ChangeMysqlLocalRoot 2>&1");
	echo "<articadatascgi>$datas</articadatascgi>";
}

function ChangeSSLCertificate(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --change-certificate");
	}


function viewlogs(){
	$file=$_GET["viewlogs"];
	$datas=@shell_exec("tail -n 100 /var/log/artica-postfix/$file");
	echo "<articadatascgi>$datas</articadatascgi>";
}
function LdapdbStat(){
	$unix=new unix();
	$dbstat=$unix->LOCATE_DB_STAT();
	$ldap_datas=$unix->PATH_LDAP_DIRECTORY_DATA();
	error_log($ldap_datas);
	$head=$unix->LOCATE_HEAD();
	if($dbstat==null){return null;}
	$cmd="$dbstat -h $ldap_datas -m | $head -n 11";
	
	error_log($cmd);
	$results=shell_exec($cmd);
	echo "<articadatascgi>$results</articadatascgi>";
} 
function LdapdbSize(){
	$unix=new unix();
	$du=$unix->LOCATE_DU();
	$ldap_datas=$unix->PATH_LDAP_DIRECTORY_DATA();
	if($du==null){return null;}
	$results=trim(shell_exec("$du -h $ldap_datas"));
	echo "<articadatascgi>$results</articadatascgi>";
}

function ldap_restart(){
sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart ldap");	
}

function buildFrontEnd(){
	
	if(isset($_GET["right"])){
		$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.smtp.flow.status.php --force";
		
		error_log($cmd." ". __FILE__);
		BuildingExecRightStatus("Scheduled",10);	
		sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.smtp.flow.status.php --force");		
		return null;
	}
	
	
	BuildingExecStatus("Scheduled",10);	
	error_log("schedule commande ". LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.status.postfix.flow.php --force");
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.status.postfix.flow.php --force");		
}
function cpualarm(){
$cpu=shell_exec("/usr/share/artica-postfix/bin/cpu-alarm.pl");	
echo "<articadatascgi>$cpu</articadatascgi>";
}
function CurrentLoad(){
	if(preg_match('#load average:\s+([0-9\.]+)#',shell_exec("uptime"),$re)){
		echo "<articadatascgi>{$re[1]}</articadatascgi>";	
	}
}

function TaskLastManager(){
	$datas=shell_exec("/bin/ps -w axo ppid,pcpu,pmem,time,args --sort -pcpu,-pmem|/usr/bin/head --lines=30");	
	echo "<articadatascgi>$datas</articadatascgi>";
}

function postfixQueues(){
	$p=new postfix_system();
	$datas=serialize($p->getQueuesNumber());
	echo "<articadatascgi>".serialize($p->getQueuesNumber())."</articadatascgi>";
}

function postfix_read_main(){
	echo "<articadatascgi>".@file_get_contents("/etc/postfix/main.cf")."</articadatascgi>";
}
function postfix_reconfigure(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --reconfigure");
}

function postfix_restricted_users(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --restricted");
}

function postfix_tail(){
	if(isset($_GET["filter"])){$filter=" \"{$_GET["filter"]}\"";}
	exec("/usr/share/artica-postfix/bin/artica-install --mail-tail$filter",$results);
	//writelogs_framework(count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>".base64_encode(serialize($results))."</articadatascgi>";
}

function postfix_multi_configure(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --org {$_GET["postfix-multi-configure-ou"]}");
}
function postfix_multi_disable(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --removes");
}


function zabbix_restart(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart zabbix");
}

function postfix_multi_stat(){
	$instance=$_GET["postfix-mutli-stat"];
	$unix=new unix();
	$pid=$unix->POSTFIX_MULTI_PID($instance);
	$path="/proc/$pid/exe";
	writelogs_framework("POSTFIX_MULTI_PID->$pid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	
	
	$version=$unix->POSTFIX_VERSION();
	if($version==null){
		$pid=$pid;
		$array[0]=-2;
		$array[1]=$version;
		$array[2]=$pid;
		$array[3]=$path;
		echo "<articadatascgi>".base64_encode(serialize($array))."</articadatascgi>";
		return ;
	}
	
	
	if(is_file($path)){
		$pid=$pid;
		$array[0]=1;
		$array[1]=$version;
		$array[2]=$pid;
		$array[3]=$path;
	}else{
		$pid=null;
		$array[0]=0;
		$array[1]=$version;
		$array[2]=null;
		$array[3]=$path;
	}
echo "<articadatascgi>".base64_encode(serialize($array))."</articadatascgi>";	
}



function postfix_stat(){
	$unix=new unix();
	$pid=$unix->POSTFIX_PID();
	$path="/proc/$pid/exe";
	writelogs_framework("POSTFIX_PID->$pid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	
	
	$version=$unix->POSTFIX_VERSION();
	if($version==null){
		$pid=$pid;
		$array[0]=-2;
		$array[1]=$version;
		$array[2]=$pid;
		$array[3]=$path;
		echo "<articadatascgi>".base64_encode(serialize($array))."</articadatascgi>";
		return ;
	}
	
	
	if(is_file($path)){
		$pid=$pid;
		$array[0]=1;
		$array[1]=$version;
		$array[2]=$pid;
		$array[3]=$path;
	}else{
		$pid=null;
		$array[0]=0;
		$array[1]=$version;
		$array[2]=null;
		$array[3]=$path;
	}
echo "<articadatascgi>".base64_encode(serialize($array))."</articadatascgi>";

	
}

function ChangeLDPSSET(){
	$vals=shell_exec("/usr/share/artica-postfix/bin/artica-install --change-ldap-settings {$_GET["ldap_server"]} {$_GET["ldap_port"]} {$_GET["suffix"]} {$_GET["username"]} {$_GET["password"]} {$_GET["change_ldap_server_settings"]}");
	echo "<articadatascgi>$vals</articadatascgi>";
}
function ASSPOriginalConf(){
	echo "<articadatascgi>".@file_get_contents("/usr/share/assp/assp.cfg")."</articadatascgi>";
}
function SetupCenter(){
sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.status.postfix.flow.php --setup");
sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --write-versions");	
}
function BuildVhosts(){
sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.www.install.php");	
}


function MySqlPerf(){
	$cmd="mysql -p{$_GET["pass"]} -u {$_GET["username"]} -T -P {$_GET["port"]} -h {$_GET["host"]} -e \"SELECT benchmark(100000000,1+2);\" -vvv >/tmp/mysqlperfs.txt 2>&1";
	shell_exec($cmd);
	
	$tbl=explode("\n",@file_get_contents("/tmp/mysqlperfs.txt"));
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match('#row in set\s+\(([0-9\.]+)#',$ligne,$re)){
			$time=trim($re[1]);
		}
	}
	
	echo "<articadatascgi>$time</articadatascgi>";
	@unlink("/tmp/mysqlperfs.txt");
	
	
}

function MysqlAudit(){
	$cmd="/usr/share/artica-postfix/bin/mysqltuner.pl --skipsize --noinfo --nogood --nocolor --pass {$_GET["pass"]} --user {$_GET["username"]} --port {$_GET["port"]} --host {$_GET["host"]} --forcemem {$_GET["server_memory"]} --forceswap {$_GET["server_swap"]} 2>&1";
	exec($cmd,$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#Performance Metrics#",$ligne)){$start=true;}
		if(!$start){continue;}
		$f[]=$ligne;
		
	}
	
	
	echo "<articadatascgi>". implode("\n",$f)."</articadatascgi>";
}
function RestartApacheNow(){
	error_log("restarting apache");
	$datas=sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart apache &");
	error_log("restarting apache done $datas");
}
function reloadSpamAssassin(){
sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reload-spamassassin");
}


function dirdir(){
	$path=$_GET["dirdir"];
	$unix=new unix();
	$array=$unix->dirdir($path);
	writelogs_framework("$path=".count($array)." directories",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". serialize($array)."</articadatascgi>";
}

function Dir_Files(){
	$path=base64_decode($_GET["Dir-Files"]);
	writelogs_framework("$path",__FUNCTION__,__FILE__,__LINE__);
	$unix=new unix();
	$array=$unix->DirFiles($path);
	writelogs_framework("$path=".count($array)." files",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}
function filestat(){
	$path=base64_decode($_GET["filestat"]);
	writelogs_framework("stat -> $path",__FUNCTION__,__FILE__,__LINE__);
	$unix=new unix();
	$array=$unix->alt_stat($path);
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";	
}

function folder_create(){
$path=base64_decode($_GET["create-folder"]);
$perms=base64_decode($_GET["perms"]);
$unix=new unix();
writelogs_framework("path=$path (".base64_decode($_GET["perms"]).")",__FUNCTION__,__FILE__,__LINE__);
	if(!mkdir($path,0666,true)){
		writelogs_framework("FATAL ERROR while creating folder $path (".base64_decode($_GET["perms"]).")",__FUNCTION__,__FILE__,__LINE__);
		echo "<articadatascgi>". base64_encode($path." -> {failed}")."</articadatascgi>";
		exit;
	}
	
	if($perms<>null){
		$cmd=$unix->find_program("chown")." ".base64_decode($_GET["perms"])." \"$path\"";
		writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($cmd);
		}
	
}

function folder_delete(){
$path=base64_decode($_GET["folder-remove"]);
$unix=new unix();
if($unix->IsProtectedDirectory($path)){
	echo "<articadatascgi>". base64_encode($path." -> {failed} {protected}")."</articadatascgi>";
	exit;
}

writelogs_framework("path=$path",__FUNCTION__,__FILE__,__LINE__);

shell_exec($unix->find_program("rm")." -rf \"$path\"");
}



function dirdirBase64(){
	$path=$_GET["B64-dirdir"];
	$unix=new unix();
	$array=$unix->dirdir(base64_decode($path));
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}

function ReplicatePerformancesConfig(){
	copy("/etc/artica-postfix/settings/Daemons/ArticaPerformancesSettings","/etc/artica-postfix/performances.conf");
}

function SetupIndexFile(){
 
 	$unix=new unix();
 	$tmpf=$unix->FILE_TEMP();
 	if(is_file("/usr/share/artica-postfix/ressources/index.ini")){@unlink("/usr/share/artica-postfix/ressources/index.ini");}
 	shell_exec("/usr/share/artica-postfix/bin/artica-update --index --verbose >$tmpf 2>&1");
    $datas=@file_get_contents($tmpf);
    @unlink($tmpf);  
    
    $cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.status.postfix.flow.php --setup-center";
    error_log("framework:: $cmd");
	shell_exec(LOCATE_PHP5_BIN2().' /usr/share/artica-postfix/exec.admin.status.postfix.flow.php --setup-center');	
	echo "<articadatascgi>$datas</articadatascgi>";
	
	
}

function testnotif(){
	$unix=new unix();
	$tmpstr=$unix->FILE_TEMP();
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/cron.notifs.php --sendmail >$tmpstr 2>&1");
	echo "<articadatascgi>".@file_get_contents($tmpstr)."</articadatascgi>";
	@unlink($tmpstr);
}	
function ComputerRemoteRessources(){
	$unix=new unix();
	$tmpstr=$unix->FILE_TEMP();
	$cmd="/usr/share/artica-postfix/bin/artica-install --remote-ressources \"{$_GET["ComputerRemoteRessources"]}\" \"{$_GET["username"]}\" \"{$_GET["password"]}\" >$tmpstr 2>&1";
	error_log("framework:: $cmd");
	shell_exec($cmd);
	echo "<articadatascgi>".@file_get_contents($tmpstr)."</articadatascgi>";
	@unlink($tmpstr);	
}
function FreeCache(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.swap-monitor.php --free");	
}

function DumpPostfixQueue(){
	$queue=$_GET["DumpPostfixQueue"];
	error_log("framework:: DumpPostfixQueue() -> $queue");
	$postfix=new postfix_system();
	echo "<articadatascgi>".$postfix->READ_QUEUE($queue)."</articadatascgi>";
	
	
}
function idofUser(){
	$unix=new unix();
	exec($unix->find_program('id')." {$_GET["idofUser"]}",$return);
	if(preg_match("#uid=([0-9]+)\({$_GET["idofUser"]}\)#",$return[0],$re)){
		echo "<articadatascgi>{$re[1]}</articadatascgi>";
	}
}

function MailManList(){
	$cmd="/usr/lib/mailman/bin/list_lists -a";
	exec($cmd,$array);
	while (list ($num, $ligne) = each ($array) ){
		
		if(preg_match("#([a-zA-Z0-9-_\.]+)\s+-\s+\[#",$ligne,$re)){
			$rr[]=strtolower($re[1]);
		}
		
	}	
	
 echo "<articadatascgi>". serialize($rr)."</articadatascgi>";	
	
}
function MailManDelete(){
	$list=$_GET["mailman-delete"];
	shell_exec("/bin/touch /var/lib/mailman/data/aliases");
	exec("/usr/lib/mailman/bin/rmlist -a $list",$re);
	if(is_array($re)){
		echo "<articadatascgi>". serialize($re)."</articadatascgi>";
	}
}

function philesizeIMG(){
	
	$unix=new unix();
	$tmpf=$unix->FILE_TEMP();
	$path=$_GET["philesize-img"];
	$img=md5($path);
	$path=str_replace("//","/",$path);
	if(substr($path,strlen($path)-1,1)=='/'){$path=substr($path,0,strlen($path)-1);}
	if($path==null){$path="/";}
	chdir("/usr/share/artica-postfix/bin");
	$cmd="/usr/share/artica-postfix/bin/philesight --db /opt/artica/philesight/database.db --path $path --draw /usr/share/artica-postfix/ressources/logs/$img.png";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("/usr/share/artica-postfix/bin/philesight --db /opt/artica/philesight/database.db --path $path --draw /usr/share/artica-postfix/ressources/logs/$img.png >$tmpf 2>&1");
	echo "<articadatascgi><img src='ressources/logs/$img.png'></articadatascgi>";
	$res=@file_get_contents($tmpf);
	@unlink($tmpf);
	writelogs_framework("ressources/logs/$img.png=>\"$res\" (". @filesize("/usr/share/artica-postfix/ressources/logs/$img.png")." bytes)",__FUNCTION__,__FILE__,__LINE__);
	
	
}
function philesizeIMGPath(){
	
	$unix=new unix();
	$tmpf=$unix->FILE_TEMP();
	$path=$_GET["philesize-img-path"];
	$img=md5($path);
	$path=str_replace("//","/",$path);
	if(substr($path,strlen($path)-1,1)=='/'){$path=substr($path,0,strlen($path)-1);}
	if($path==null){$path="/";}
	chdir("/usr/share/artica-postfix/bin");
	$cmd="/usr/share/artica-postfix/bin/philesight --db /opt/artica/philesight/database.db --path $path --draw /usr/share/artica-postfix/ressources/logs/$img.png";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	@mkdir("/usr/share/artica-postfix/user-backup/ressources/logs");
	@copy("/usr/share/artica-postfix/ressources/logs/$img.png","/usr/share/artica-postfix/user-backup/ressources/logs/$img.png");
	@chmod("/usr/share/artica-postfix/user-backup/ressources/logs/$img.png",0755);
	shell_exec("/usr/share/artica-postfix/bin/philesight --db /opt/artica/philesight/database.db --path $path --draw /usr/share/artica-postfix/ressources/logs/$img.png >$tmpf 2>&1");
	echo "<articadatascgi>ressources/logs/$img.png</articadatascgi>";
	$res=@file_get_contents($tmpf);
	@unlink($tmpf);
	writelogs_framework("ressources/logs/$img.png=>\"$res\" (". @filesize("/usr/share/artica-postfix/ressources/logs/$img.png")." bytes)",__FUNCTION__,__FILE__,__LINE__);
	
	
}

function kaspersky_status(){
	exec("/usr/share/artica-postfix/bin/artica-install --kaspersky-status",$results);
	$text=trim(implode("\n",$results));
	echo "<articadatascgi>". base64_encode($text)."</articadatascgi>";
	
}

function kavmilter_configure(){
	if(is_file("/opt/kav/5.6/kavmilter/bin/kavmilter")){
		sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.kavmilter.php");
	}
		
if(is_file("/opt/kaspersky/kav4lms/bin/kav4lms-cmd")){
		sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.kav4mls.php");
	}	
	
}
function kavmilter_mem(){
	exec("/usr/share/artica-postfix/bin/artica-install --kavmilter-mem",$results);
	$text=trim(implode(" ",$results));
	echo "<articadatascgi>". base64_encode($text)."</articadatascgi>";
}
function kavmilter_pattern(){
	exec("/usr/share/artica-postfix/bin/artica-install --kavmilter-pattern",$results);
	$text=trim(implode(" ",$results));
	echo "<articadatascgi>". base64_encode($text)."</articadatascgi>";
}


function squid_originalconf(){
	$unix=new unix();
	echo "<articadatascgi>". base64_encode(@file_get_contents($unix->LOCATE_SQUID_CONF()))."</articadatascgi>";
}



function  philesight_perform(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.philesight.php --rebuild");
}

function PostfixHeaderCheck(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --headers-check");
}

function disks_list(){
	$unix=new unix();
	$array=$unix->DISK_LIST();
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}

function disk_get_mounted_point(){
	$dev=base64_decode($_GET["get-mounted-path"]);
	$unix=new unix();
	echo "<articadatascgi>". base64_encode($unix->MOUNTED_PATH($dev))."</articadatascgi>";
}




function lvs_scan(){
	$results=array();
	$VolumeGroupName=$_GET["lvm-lvs"];
	$unix=new unix();
	exec($unix->find_program("lvs")." --noheadings --aligned --separator \";\" --units g $VolumeGroupName",$returns);
	while (list ($num, $ligne) = each ($returns) ){
		if(!preg_match("#(.+?);(.+?);(.+?);(.+?)G#",$ligne,$re)){continue;}
		$array[trim($re[1])]=str_replace(",",".",trim($re[4]));
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	}

function sfdisk_dump(){
	$dev=$_GET["sfdisk-dump"];
	$unix=new unix();
	exec($unix->find_program("sfdisk")." -d $dev",$returns);	
	echo "<articadatascgi>". base64_encode(serialize($returns))."</articadatascgi>";
	}
function mkfs(){
	$dev=$_GET["mkfs"];
	if($dev==null){return null;}
	$unix=new unix();
	$ext=$unix->BetterFS();
	exec($unix->find_program("mkfs")." -T $ext $dev",$returns);	
	echo "<articadatascgi>". base64_encode(serialize($returns))."</articadatascgi>";	
}

function parted_print(){
	$dev=$_GET["parted-print"];
	$unix=new unix();
	if($dev==null){return;}
	exec($unix->find_program("parted")." $dev -s unit GB print",$returns);
	echo "<articadatascgi>". base64_encode(serialize($returns))."</articadatascgi>";		
}

function LVM_LVS_DEV_MAPPER(){
	$dev=$_GET["lvs-mapper"];
	$mapper=@readlink($dev);
	$mapper=str_replace("../mapper","/dev/mapper",$mapper);
	echo "<articadatascgi>$mapper</articadatascgi>";
}
function LVM_VGS_INFO(){
	$vg=$_GET["vgs-info"];
	$unix=new unix();
	exec($unix->find_program("vgs")." $vg",$returns);
	$pattern="$vg\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)(.+?)\s+([0-9,\.A-Z]+)\s+([0-9,\.A-Z]+)";
	writelogs_framework("$vg:: PATTERN=\"$pattern\"",__FUNCTION__,__FILE__,__LINE__);
	while (list ($num, $ligne) = each ($returns) ){
		if(preg_match("#$pattern#",$ligne,$re)){
			$array[$vg]=array("SIZE"=>$re[5],"FREE"=>$re[6]);
			echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
			break;
		}else{
			writelogs_framework("$vg:: FAILED=\"$ligne\"",__FUNCTION__,__FILE__,__LINE__);
		}
	}	
	
}


function LVM_lVS_INFO_ALL(){
$unix=new unix();
	exec($unix->find_program("lvs"),$returns);
	$pattern="(.+?)\s+(.+?)\s+(.+?)\s+([0-9,\.A-Z]+)";
	writelogs_framework("PATTERN=\"$pattern\"",__FUNCTION__,__FILE__,__LINE__);
	
	
	while (list ($num, $ligne) = each ($returns) ){
		if(preg_match("#$pattern#",trim($ligne),$re)){
			$array[trim($re[1])]=array("SIZE"=>$re[4],"GROUPE"=>$re[2]);
		}else{
			writelogs_framework("FAILED=\"$ligne\"",__FUNCTION__,__FILE__,__LINE__);
		}
	}

	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}

function LVM_LV_ADDSIZE(){
	$mapper=$_GET["lv-resize-add"];
	$size=$_GET["size"];
	$unit=$_GET["unit"];
	$results=array();
	$unix=new unix();
	
	$cmd0=$unix->find_program("lvextend")." -L $size$unit $mapper";
	$cmd1=$unix->find_program("umount")." -f $mapper";
	$cmd2=$unix->find_program("resize2fs")." -f $mapper";
	$cmd3=$unix->find_program("mount")." $mapper";
	
	writelogs_framework("$cmd0",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd0,$results0);
	
	writelogs_framework("$cmd1",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd1,$results1);
	
	writelogs_framework("$cmd2",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd2,$results2);

	writelogs_framework("$cmd3",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd3,$results3);	
	
	
	if(is_array($results0)){$results=$results+$results0;}
	if(is_array($results1)){$results=$results+$results1;}
	if(is_array($results2)){$results=$results+$results2;}
	if(is_array($results3)){$results=$results+$results3;}
	
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	

}
function LVM_LV_DELSIZE(){
	$mapper=$_GET["lv-resize-red"];
	$size=$_GET["size"];
	$unit=$_GET["unit"];
	$results=array();
	$unix=new unix();
	
	
	$cmd0=$unix->find_program("lvreduce")." -y -f -L$size$unit $mapper";
	$cmd1=$unix->find_program("umount")." -f $mapper";
	$cmd2=$unix->find_program("resize2fs")." -f -p $mapper $size$unit";
	$cmd3=$unix->find_program("mount")." $mapper";
	
	writelogs_framework("$cmd0",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd0,$results0);
	
	writelogs_framework("$cmd2",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd2,$results2);	
	
	writelogs_framework("$cmd1",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd1,$results1);
	

	writelogs_framework("$cmd3",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd3,$results3);	
	
	
	if(is_array($results0)){$results=$results+$results0;}
	if(is_array($results1)){$results=$results+$results1;}
	if(is_array($results2)){$results=$results+$results2;}
	if(is_array($results3)){$results=$results+$results3;}
	
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	

}



function LVM_REMOVE(){
	
	// dmsetup info -c
	$dev=$_GET["lvremove"];
	$unix=new unix();
	$cmd=$unix->find_program("lvremove")." -f $dev 2>&1";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}

function SASL_FINGER(){
	$unix=new unix();
	$saslfinger=$unix->find_program("saslfinger");
	if(!is_file($saslfinger)){
		echo "<articadatascgi>". base64_encode(serialize(array("unable to stat saslfinger")))."</articadatascgi>";
		return;
	}	
	
	exec("$saslfinger -s",$returns);
	echo "<articadatascgi>". base64_encode(serialize($returns))."</articadatascgi>";
}
function cups_delete_printer(){
	$unix=new unix();
	$printer=$_GET["cups-delete-printer"];
	$printer=urlencode($printer);
	$lpadmin=$unix->find_program("lpadmin");
	if(!is_file($lpadmin)){
			echo "<articadatascgi>". base64_encode(serialize(array("unable to stat lpadmin")))."</articadatascgi>";
			return;
		}
	
	exec("$lpadmin -x $printer",$returns);
	echo "<articadatascgi>". base64_encode(serialize($returns))."</articadatascgi>";	
}
function cups_add_printer(){
	$unix=new unix();
	$lpadmin=$unix->find_program("lpadmin");
	if(!is_file($lpadmin)){
			echo "<articadatascgi>". base64_encode(serialize(array("unable to stat lpadmin")))."</articadatascgi>";
			return;
		}
	//&name=$name&path=$path&driver=$driver&localization=$localization
	
	$array=unserialize(base64_decode($_GET["params"]));	
	$name=$array["name"];
	$name=urlencode($name);	
	$path=$array["path"];
	
	if(preg_match("#usb:\/\/(.+?)\/(.+)#",$path,$re)){
		writelogs_framework("Found printer name {$re[1]} slash {$re[2]}",__FUNCTION__,__FILE__,__LINE__);
		$re[1]=str_replace(" ","%20",$re[1]);
		$path="usb://".$re[1]."/".$re[2];
	}
	
	shell_exec("/bin/cp {$array["driver"]} /usr/share/cups/model/");
	shell_exec("/bin/chmod -R 777 /usr/share/cups/model");
	$driver=$array["driver"];
	$driver_name=basename($driver_name);
	$cmd="$lpadmin -p $name -L {$array["localization"]} -v \"$path\" -m $driver_name -o printer-is-shared=true";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	if(is_file("/etc/init.d/cups")){shell_exec("/etc/init.d/cups restart");}
	$cupsenable=$unix->find_program("cupsenable");
	if(is_file($cupsenable)){shell_exec("$cupsenable $name");}
	if(is_file($unix->LOCATE_CUPS_ACCEPT())){shell_exec($unix->LOCATE_CUPS_ACCEPT()." $name");}
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	

}

function squid_config(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squid.php --reconfigure");
}

function etc_hosts_open(){
	$datas=explode("\n",@file_get_contents("/etc/hosts"));
	$datas[]="\n";
	$datz=serialize($datas);
	echo "<articadatascgi>". base64_encode($datz)."</articadatascgi>";	
}
function etc_hosts_add(){
	$datas=explode("\n",@file_get_contents("/etc/hosts"));
	writelogs_framework(count($datas)." rows",__FUNCTION__,__FILE__,__LINE__);
	$datas[]=base64_decode($_GET["etc-hosts-add"]);
	@file_put_contents("/etc/hosts",implode("\n",$datas));
	
}
 
function etc_hosts_del(){
	$datas=explode("\n",@file_get_contents("/etc/hosts"));
	writelogs_framework("delete entry {$_GET["etc-hosts-del"]}",__FUNCTION__,__FILE__,__LINE__);
	while (list ($num, $ligne) = each ($datas) ){
		if(md5($ligne)==$_GET["etc-hosts-del"]){
			writelogs_framework("delete line $ligne",__FUNCTION__,__FILE__,__LINE__);
			unset($datas[$num]);
			break;
		}
	}
	
	
	
	@file_put_contents("/etc/hosts",implode("\n",$datas));
}

function file_content(){
	$datas=@file_get_contents(base64_decode($_GET["file-content"]));
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";	
}

function samba_smbclient(){
	$ini=new Bs_IniHandler("/etc/samba/smb.conf");
	$unix=new unix();
	$creds=unserialize(base64_decode($_GET["creds"]));
	$comp=$_GET["computer"];
	$cmd=$unix->find_program("smbclient")." -N -U {$creds[0]}%{$creds[1]} -L //$comp -g";
	exec($cmd,$results);
	if(is_array($results)){
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match("#Disk\|(.+?)\|#",$ligne,$re)){
				$folder=$re[1];
				$array[$folder]=$ini->_params[$folder]["path"];
			}
		}
	}
	unset($array[$creds[0]]);
	if(!is_array($array)){$array=array();}
	writelogs_framework($cmd." =".count($array)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
	
}

function postfix_certificate(){
	$cmd='/usr/share/artica-postfix/bin/artica-install --change-postfix-certificate --verbose';
	exec($cmd,$results);
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}

function certificate_infos(){
	$unix=new unix();
	$openssl=$unix->find_program("openssl");
	$l=$unix->FILE_TEMP();
	$f[]="/etc/ssl/certs/cyrus.pem";
	$f[]="/etc/ssl/certs/openldap/cert.pem";
	$f[]="/opt/artica/ssl/certs/lighttpd.pem";
	
	while (list ($num, $path) = each ($f) ){
		if(is_file($path)){
			$cmd="$openssl x509 -in $path -text -noout >$l 2>&1";
			break;
		}
	}
	
	if($cmd<>null){
		shell_exec($cmd);
		$datas=explode("\n",@file_get_contents($l));
		writelogs_framework($cmd." =".count($array)." rows",__FUNCTION__,__FILE__,__LINE__);
		@unlink($l);
	}
	echo "<articadatascgi>". base64_encode(serialize($datas))."</articadatascgi>";
}

function process_kill(){
	if($_GET["kill-pid-number"]==null){return;}
	if($_GET["kill-pid-number"]<2){return;}
	process_kill_perform($_GET["kill-pid-number"]);
}

function process_kill_perform($pid){
		if($pid==null){return;}
		if($pid<2){return;}
		$unix=new unix();
		$array=$unix->PROCESS_STATUS($pid);
		if(!$array){return null;}
		if($array[0]="Z"){
			writelogs_framework("Zombie detected PPID:{$array[1]}",__FUNCTION__,__FILE__,__LINE__);
			process_kill_perform($array[1]);			
		}
		$cmd=$unix->find_program("kill")." -9 {$pid}";
		writelogs_framework("kill PID process $pid",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($cmd);
}

function TCP_LIST_NICS(){
	$datas=explode("\n",@file_get_contents("/proc/net/dev"));
	while (list ($num, $line) = each ($datas) ){
		if(preg_match("#^(.+?):#",$line,$re)){
			if(trim($re[1])=="lo"){continue;}
			if(preg_match("#pan[0-9]+#",$re[1])){continue;}
			if(preg_match("#tun[0-9]+#",$re[1])){continue;}
			if(preg_match("#vboxnet[0-9]+#",$re[1])){continue;}
			if(preg_match("#wmaster[0-9]+#",$re[1])){continue;}
			$array[]=trim($re[1]);
		}
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
}

function samba_logon_scripts(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.samba.php --logon-scripts");
}

function TCP_VIRTUALS(){
	if(isset($_GET["stay"])){
		shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.virtuals-ip.php");
		sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --virtuals");
		return;
	}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.virtuals-ip.php");
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --virtuals");
}

function MalwarePatrol(){
	if(!is_file("/etc/squid3/malwares.acl")){@file_put_contents("/etc/squid3/malwares.acl","#");}
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-update --MalwarePatrol");
	}
	
function MalwarePatrolDatabasesCount(){
	$datas=explode("\n",@file_get_contents("/etc/squid3/malwares.acl"));
	$count=0;
	while (list ($num, $line) = each ($datas) ){
		if(trim($line)==null){continue;}
		if(substr($line,0,1)=="#"){continue;}
		$count=$count+1;
	}
	echo "<articadatascgi>$count</articadatascgi>";
	
}

function postfix_multi_queues(){
	$instance=$_GET["postfix-multi-queues"];
	$unix=new unix();
	$queue_directory=trim($unix->POSTCONF_MULTI_GET($instance,"queue_directory"));
	$queues=array("active","bounce","corrupt","defer","deferred","flush","hold","incoming");
	
	while (list ($num, $queuename) = each ($queues) ){
		$array["$queuename"]=$unix->dir_count_files_recursive("$queue_directory/$queuename");
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}

function ASSP_MULTI_CONFIG(){
	$ou=base64_decode($_GET["assp-multi-load-config"]);
	$instance=str_replace(" ","-",$ou);
	$path="/usr/share/assp-$instance/assp.cfg";
	if($ou=="DEFAULT"){
		$path="/usr/share/assp/assp.cfg";
	}
	$data=explode("\n",@file_get_contents($path));
	while (list ($num, $line) = each ($data) ){
		if(preg_match("#(.+?):=(.*)#",$line,$re)){
			$array[trim($re[1])]=trim($re[2]);
		}
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
}

function START_STOP_SERVICES(){
	$md5=$_GET["APP"].$_GET["action"].$_GET["cmd"];
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/web/$md5.log","...");
	@chmod("/usr/share/artica-postfix/ressources/logs/web/$md5.log",0777);
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix {$_GET["action"]} {$_GET["cmd"]} >>/usr/share/artica-postfix/ressources/logs/web/$md5.log");
	
	
}

function kas_reconfigure(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.kasfilter.php");
}

function retranslator_execute(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-update --retranslator");
}
function retranslator_dbsize(){
	$unix=new unix();
	$cmd=$unix->find_program("du")." -h -s /var/db/kav/databases 2>&1";
	exec($cmd,$results);
	$text=trim(implode(" ",$results));
	if(preg_match("#^([0-9\.\,A-Z]+)#",$text,$re)){
		$dbsize=$re[1];
	}
	
	
	echo "<articadatascgi>". base64_encode($dbsize)."</articadatascgi>";
}
function retranslator_tmp_dbsize(){
	$unix=new unix();
	$array=$unix->getDirectories("/tmp");
	while (list ($num, $ligne) = each ($array) ){
		if(preg_match("#(.+?)\/temporaryFolder\/bases\/av#",$ligne,$re)){
			$folder=$re[1];
		}
	}
	if(is_dir($folder)){
		$cmd=$unix->find_program("du")." -h -s $folder 2>&1";
		exec($cmd,$results);
		$text=trim(implode(" ",$results));
		if(preg_match("#^([0-9\.\,A-Z]+)#",$text,$re)){
			$dbsize=$re[1];
		}
	}else{
		$dbsize="0M";
	}
	
echo "<articadatascgi>". base64_encode($dbsize)."</articadatascgi>";
}






function retranslator_restart(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart retranslator");
}
function retranslator_sites_lists(){
	$cmd="/usr/share/artica-postfix/bin/retranslator.bin -s -c /etc/kretranslator/retranslator.conf 2>&1";
	exec($cmd,$results);
	writelogs_framework(count($results)." lines [$cmd]",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}
function retranslator_events(){
	$unix=new unix();
	$cmd=$unix->find_program("tail").' -n 100 /var/log/kretranslator/retranslator.log 2>&1';
	exec($cmd,$results);
	writelogs_framework(count($results)." lines [$cmd]",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}
function retranslator_status(){
	$cmd="/usr/share/artica-postfix/bin/artica-install --retranslator-status 2>&1";
	exec($cmd,$results);
	writelogs_framework(count($results)." lines [$cmd]",__FUNCTION__,__FILE__,__LINE__);
	$datas=implode("\n",$results);
	
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";	
}

function hamachi_net(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.hamachi.php");
//if(isset($_GET["hamachi-net"])){hamachi_net();exit;} 
}

function hamachi_status(){
	exec("/usr/share/artica-postfix/bin/artica-install --hamachi-status",$rr);
	$ini=new Bs_IniHandler();
	$ini->loadString(implode("\n",$rr));
	echo "<articadatascgi>". base64_encode(serialize($ini->_params))."</articadatascgi>";
}
function hamachi_sessions(){
	$unix=new unix();
exec($unix->find_program("hamachi")." -c /etc/hamachi list",$l);
	while (list ($num, $ligne) = each ($l) ){
		if(preg_match("#\[(.+?)\]#",$ligne,$re)){$net=$re[1];continue;}
		if(preg_match("#([0-9\.]+)#",$ligne,$re)){
			$session[$net][]=$re[1];
		}
		
		
	}
	echo "<articadatascgi>". base64_encode(serialize($session))."</articadatascgi>";
}

function hamachi_currentIP(){
	
	$datas=explode("\n",@file_get_contents("/etc/hamachi/state"));
	while (list ($num, $ligne) = each ($datas) ){
		if(preg_match("#Identity\s+([0-9\.]+)#",$ligne,$re)){
			echo "<articadatascgi>". $re[1]."</articadatascgi>";
			break;
		}
	}
	
}

function hamachi_restart(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart hamachi");
	
}

function hamachi_delete_network(){
	$unix=new unix();
	$_GET["hamachi-delete-net"]=base64_decode($_GET["hamachi-delete-net"]);
	exec($unix->find_program("hamachi")." -c /etc/hamachi leave {$_GET["hamachi-delete-net"]}",$l);
	exec($unix->find_program("hamachi")." -c /etc/hamachi delete {$_GET["hamachi-delete-net"]}",$l);
}

function Kav4ProxyUpdate(){
	$unix=new unix();
	$cmd="/opt/kaspersky/kav4proxy/bin/kav4proxy-keepup2date";
	$type=$_GET["type"];
	
	if($type=="milter"){
		if(is_file("/opt/kav/5.6/kavmilter/bin/keepup2date")){
			$cmd="/opt/kav/5.6/kavmilter/bin/keepup2date";
		}
		
		if(is_file("/opt/kaspersky/kav4lms/bin/kav4lms-keepup2date")){
			shell_exec("/usr/share/artica-postfix/bin/artica-install --kavm4mls-info");
			$cmd="/opt/kaspersky/kav4lms/bin/kav4lms-keepup2date";
		}
		
	}
	
	if($type=="kas"){
		$cmd="/usr/local/ap-mailfilter3/bin/keepup2date -c /usr/local/ap-mailfilter3/etc/keepup2date.conf";
	}
	

	$pid=$unix->PIDOF(basename($cmd));
	if(strlen($pid)>0){return;}
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/Kav4ProxyUpdate","{waiting}...\nSelected $type: ".basename($cmd)."\n\n");
	@chmod("/usr/share/artica-postfix/ressources/logs/Kav4ProxyUpdate",0775);
	sys_THREAD_COMMAND_SET("$cmd >>/usr/share/artica-postfix/ressources/logs/Kav4ProxyUpdate");
}


function SendmailPath(){
	$unix=new unix();
	echo "<articadatascgi>". base64_encode($unix->LOCATE_SENDMAIL_PATH())."</articadatascgi>";
}


function kasmilter_license(){
	$unix=new unix();
	$tmpstr=$unix->FILE_TEMP();	
	$cmd="/usr/local/ap-mailfilter3/bin/licensemanager -c /usr/local/ap-mailfilter3/etc/keepup2date.conf";
	exec("$cmd -s >$tmpstr 2>&1");
	$results=explode("\n",@file_get_contents($tmpstr));
	@unlink($tmpstr);
	$results[]="--------------------------------------------------------------";
	$results[]=basename($cmd);
	$results[]="--------------------------------------------------------------";
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";	
}

function kavmilter_license(){
	$unix=new unix();
	$tmpstr=$unix->FILE_TEMP();	
	if(is_file("/opt/kav/5.6/kavmilter/bin/licensemanager")){
		$cmd="/opt/kav/5.6/kavmilter/bin/licensemanager";
		
	}
	
	if(is_file("/opt/kaspersky/kav4lms/bin/kav4lms-licensemanager")){
		$cmd="/opt/kaspersky/kav4lms/bin/kav4lms-licensemanager";
	}
	exec("$cmd -s >$tmpstr 2>&1");
	$results=explode("\n",@file_get_contents($tmpstr));
	@unlink($tmpstr);
	$results[]="--------------------------------------------------------------";
	$results[]=basename($cmd);
	$results[]="--------------------------------------------------------------";
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";
	
}

function kav4proxy_license(){
	$unix=new unix();
	$tmpstr=$unix->FILE_TEMP();
	$cmd="/opt/kaspersky/kav4proxy/bin/kav4proxy-licensemanager";
	if($_GET["type"]=="milter"){kavmilter_license();return;}
	if($_GET["type"]=="kas"){kasmilter_license();return;}
	exec("$cmd -s >$tmpstr 2>&1");
	$results=explode("\n",@file_get_contents($tmpstr));
	@unlink($tmpstr);
	$results[]="--------------------------------------------------------------";
	$results[]=basename($cmd);
	$results[]="--------------------------------------------------------------";
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";	
	
	
}

function kav4proxy_upload_license(){
	$f=$_GET["Kav4ProxyUploadLicense"];
	$type=$_GET["type"];
	$cmd="/opt/kaspersky/kav4proxy/bin/kav4proxy-licensemanager";
	if($type=="milter"){
		if(is_file("/opt/kav/5.6/kavmilter/bin/licensemanager")){
			$cmd="/opt/kav/5.6/kavmilter/bin/licensemanager";
			
		}
		
		if(is_file("/opt/kaspersky/kav4lms/bin/kav4lms-licensemanager")){
			$cmd="/opt/kaspersky/kav4lms/bin/kav4lms-licensemanager";
		}
		
	}
	
	
	if($type="kas"){$cmd="/usr/local/ap-mailfilter3/bin/licensemanager -c /usr/local/ap-mailfilter3/etc/keepup2date.conf";}
	
	$unix=new unix();
	$tmpf=$unix->FILE_TEMP();
	shell_exec("$cmd -a $f >$tmpf 2>&1");
	$results=explode("\n",@file_get_contents($results));
	@unlink($tmpf);
	$results[]="--------------------------------------------------------------";
	$results[]=basename($cmd) .":$cmd -a $f";
	$results[]="--------------------------------------------------------------";
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";		
	
}
function kav4proxy_reload(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reload-kav4proxy");
}


function kav4proxy_delete_license(){
	
	$type=$_GET["type"];
	$cmd="/opt/kaspersky/kav4proxy/bin/kav4proxy-licensemanager";
	if($type=="milter"){
		if(is_file("/opt/kav/5.6/kavmilter/bin/licensemanager")){
			$cmd="/opt/kav/5.6/kavmilter/bin/licensemanager";
			
		}
		
		if(is_file("/opt/kaspersky/kav4lms/bin/kav4lms-licensemanager")){
			$cmd="/opt/kaspersky/kav4lms/bin/kav4lms-licensemanager";
		}
		
	}
	
	if($type="kas"){$cmd="/usr/local/ap-mailfilter3/bin/licensemanager -c /usr/local/ap-mailfilter3/etc/keepup2date.conf";}
	
	exec("$cmd -a $f",$results);
	$results[]="--------------------------------------------------------------";
	$results[]=basename($cmd) .":$cmd -da";
	$results[]="--------------------------------------------------------------";
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";		
	
}


function kav4lms_bases_infos(){
	$unix=new unix();
	if(is_file("/opt/kav/5.6/kavmilter/bin/licensemanager")){
		$cmd="/usr/share/artica-postfix/bin/artica-install --kavmilter-pattern";
		$cmd2=$unix->find_program("du"). " -h -s /var/db/kav/databases";
	}
	
	if(is_file("/opt/kaspersky/kav4lms/bin/kav4lms-licensemanager")){
		$cmd="/usr/share/artica-postfix/bin/artica-install --kavm4mls-pattern";
		$cmd2=$unix->find_program("du"). " -h -s /var/opt/kaspersky/kav4lms/bases/";
	}	
	
	exec($cmd,$results);
	$f=trim(implode("",$results));
	$d=substr($f,0,2);
	$m=substr($f,2,2);
	$y=substr($f,4,4);
	$h=substr($f,9,2);
	$M=substr($f,11,2);
	$date="$y-$m-$d $h:$M:00";
	unset($results);

	exec($cmd2,$results);
	$f=trim(implode(" ",$results));
	
	
	$f=str_replace(",",".",$f);
	preg_match("#([0-9\.A-Z]+)\s+#",$f,$re);
	$size=$re[1];
	
	echo "<articadatascgi>". base64_encode(serialize(array($date,$size)))."</articadatascgi>";
	
}

function kasversion(){
	exec("/usr/share/artica-postfix/bin/artica-install --kas3-version",$results);
	preg_match("#([0-9\.]+);([0-9]+);([0-9]+)#",implode("",$results),$re);
	$array["version"]=$re[1];
	$f=$re[2];
	$d=substr($f,0,2);
	$m=substr($f,2,2);
	$y=substr($f,4,4);
	
	$f=$re[3];
	$H=substr($f,0,2);
	$M=substr($f,2,2);
	$array["pattern"]="$y-$m-$d $H:$M:00";
	$unix=new unix();
	unset($results);
	$cmd2=$unix->find_program("du"). " -h -s /usr/local/ap-mailfilter3/cfdata/bases/";
	exec($cmd2,$results);
	$f=trim(implode(" ",$results));
	$f=str_replace(",",".",$f);
	preg_match("#([0-9\.A-Z]+)\s+#",$f,$re);
	$size=$re[1];
	$array["size"]=$size;
	
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
}

function release_quarantine(){
	$array=unserialize(base64_decode($_GET["release-quarantine"]));
	$unix=new unix();
	$tmpfileConf=$unix->FILE_TEMP();
	
	$msmtp[]= "syslog on";
	$msmtp[]="from {$array["from"]}";
	$msmtp[]="protocol smtp";
	$msmtp[]="host 127.0.0.1";
	$msmtp[]="port 33559";
	@file_put_contents($tmpfileConf,implode("\n",$msmtp));
	if(is_file("/usr/share/artica-postfix/bin/artica-msmtp")){$msmtp_cmd="/usr/share/artica-postfix/bin/artica-msmtp";}
	if(is_file($unix->find_program("msmtp"))){$msmtp_cmd=$unix->find_program("msmtp");}
	$logfile=$unix->FILE_TEMP().".log";
	chmod($tmpfileConf,0600);
	$cmd="$msmtp_cmd --tls-certcheck=off --timeout=10 --file=$tmpfileConf --syslog=on  --logfile=$logfile -- {$array["to"]} <{$array["file"]}";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	$data=explode("\n",@file_get_contents($logfile));
	writelogs_framework(implode("\n",$data),__FUNCTION__,__FILE__,__LINE__);
	@unlink($logfile);
	@unlink($tmpfileConf);
	echo "<articadatascgi>". base64_encode(serialize($data))."</articadatascgi>";
}

if(isset($_GET["uninstall-app"])){application_uninstall();exit;}

function application_uninstall(){
	$cmdline=base64_decode($_GET["uninstall-app"]);
	$app=$_GET["app"];
	$unix=new unix();
	@unlink("/usr/share/artica-postfix/ressources/install/$app.ini");
	@unlink("/usr/share/artica-postfix/ressources/install/$app.dbg");
	$tmpstr="/usr/share/artica-postfix/ressources/logs/UNINSTALL_$app";
	
	@file_put_contents($tmpstr,"Scheduled.....");
	@chmod($tmpstr,0755);
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install $cmdline >>$tmpstr 2>&1");
	$results=explode("\n",@file_get_contents($tmpstr));
	@unlink($tmpstr);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}

function application_debug_infos(){
	$appli=$_GET["AppliCenterGetDebugInfos"];
	$results=explode("\n",@file_get_contents("/usr/share/artica-postfix/ressources/install/$appli.dbg"));
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
	
}
function application_service_install(){
	$cmdline=base64_decode($_GET["services-install"]);
	writelogs_framework("launch $cmdline !!!",__FUNCTION__,__FILE__,__LINE__);
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/setup-ubuntu $cmdline");
}
function Restart_Policyd_Weight(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart policydw");
}

function dansguardian_update(){
	$cmd="/usr/share/artica-postfix/bin/artica-update --dansguardian --verbose";
	file_put_contents("/usr/share/artica-postfix/ressources/logs/DANSUPDATE","{waiting}...\n\n\n");
	@chmod("/usr/share/artica-postfix/ressources/logs/DANSUPDATE",0775);
	sys_THREAD_COMMAND_SET("$cmd >>/usr/share/artica-postfix/ressources/logs/DANSUPDATE");	
	}


function ldap_upload_organization(){
	$ou=base64_decode($_GET["upload-organization"]);
	
	writelogs_framework("Exporting $ou",__FUNCTION__,__FILE__,__LINE__);
	
	$config=$_GET["config-file"];
	$unix=new unix();
	$tmpstr=$unix->FILE_TEMP();
	writelogs_framework("executing  ".LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.ldap.move-orgs.php --upload \"$ou\" \"$config\" >$tmpstr 2>&1",__FUNCTION__,__FILE__,__LINE__);
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.ldap.move-orgs.php --upload \"$ou\" \"$config\" >$tmpstr 2>&1");
	$results=explode("\n",@file_get_contents($tmpstr));
	@unlink($tmpstr);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}
function ifconfig_interfaces(){
	$unix=new unix();
	$cmd=$unix->find_program("ifconfig")." -s";
	exec($cmd,$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#^(.+?)\s+[0-9]+#",$line,$re)){
			$array[trim($re[1])]=trim($re[1]);
		}
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
}
function ifconfig_all(){
	$unix=new unix();
	$cmd=$unix->find_program("ifconfig")." -a 2>&1";
	exec($cmd,$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
	
}


function organization_delete(){
	
	$ou=base64_decode($_GET["organization-delete"]);
	$deletmailboxes=$_GET["delete-mailboxes"];
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.delete-ou.php $ou $deletmailboxes");
}

function fetchmail_status(){
	exec("/usr/share/artica-postfix/bin/artica-install --fetchmail-status",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}
function fetchmail_logs(){
	$unix=new unix();
	$tail=$unix->find_program("tail");
	exec("$tail -n 200 /var/log/fetchmail.log",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
	
}

function AD_IMPORT_SCHEDULE(){
	$ou=base64_decode($_GET["ou"]);
	$schedule=base64_decode($_GET["schedule"]);
	@mkdir("/etc/artica-postfix/ad-import");
	$f="$schedule ".LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.ad-import-ou.php $ou"; 
	$file="/etc/artica-postfix/ad-import/import-ad-".md5($ou);
	@file_put_contents($file,$f);
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart daemon");

}
function AD_REMOVE_SCHEDULE(){
	$ou=base64_decode($_GET["ou"]);
	$file="/etc/artica-postfix/ad-import/import-ad-".md5($ou);
	writelogs_framework("Remove $file");
	@unlink($file);
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart daemon");
}

function AD_PERFORM(){
$ou=base64_decode($_GET["ou"]);

$file="/usr/share/artica-postfix/ressources/logs/web/ad-$ou.log";
@file_put_contents($file,"{scheduled}...");
@chmod($file,777);
sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.ad-import-ou.php $ou");
}

function backup_sql_tests(){
	writelogs_framework("Testing backup id {$_GET["backup-sql-test"]}",__FUNCTION__,__FILE__,__LINE__);
	exec(LOCATE_PHP5_BIN2() ." /usr/share/artica-postfix/exec.backup.php {$_GET["backup-sql-test"]} --only-test --verbose",$results);
	
	writelogs_framework(count($results)." line",__FUNCTION__,__FILE__,__LINE__);
	
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}

function backup_task_run(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.backup.php {$_GET["backup-task-run"]}");
}


function backup_build_cron(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.backup.php --cron");
}
function GlobalApplicationsStatus(){
	$unix=new unix();
	$mainfile="/usr/share/artica-postfix/ressources/logs/global.versions.conf";
	$mainstatus="/usr/share/artica-postfix/ressources/logs/global.status.ini";
	if(!is_file($mainfile)){
		shell_exec("/usr/share/artica-postfix/bin/artica-install -versions > /usr/share/artica-postfix/ressources/logs/global.versions.conf 2>&1");
	}
	if(!is_file($mainstatus)){
            shell_exec('/usr/share/artica-postfix/bin/artica-install --status > /usr/share/artica-postfix/ressources/logs/global.status.ini 2>&1');
	}
	
	$datas=@file_get_contents($mainstatus)."\n".@file_get_contents($mainfile);
	
	if($unix->file_time_min($mainstatus)>0){
		@unlink($mainfile);
		@unlink($mainstatus);
		sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install -versions >/usr/share/artica-postfix/ressources/logs/global.versions.conf");
		sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --status >/usr/share/artica-postfix/ressources/logs/global.status.ini");
	}
	sys_THREAD_COMMAND_SET("/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/global.*");
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";
}
function resolv_conf(){
	$datas=explode("\n",@file_get_contents("/etc/resolv.conf"));
	echo "<articadatascgi>". base64_encode(serialize($datas))."</articadatascgi>";
	}
function MyOs(){
	exec("/usr/share/artica-postfix/bin/artica-install --myos 2>&1",$results);
	writelogs_framework(trim(implode("",$results)),__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". trim(implode("",$results))."</articadatascgi>";
}
function lspci(){
	$unix=new unix();
	$lspci=$unix->find_program("lspci");
	exec("$lspci 2>&1",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}
function freemem(){
	$unix=new unix();
	$prog=$unix->find_program("free");
	exec("$prog -m -o 2>&1",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
}
function dfmoinsh(){
	$unix=new unix();
	$prog=$unix->find_program("df");
	exec("$prog -h 2>&1",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
}
function printenv(){
	$unix=new unix();
	$prog=$unix->find_program("printenv");
	exec("$prog 2>&1",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
}
function GenerateCert(){
	$path=$_GET["path"];
	exec("/usr/share/artica-postfix/bin/artica-install --gen-cert $path",$results);
	echo "<articadatascgi>". trim(implode(" ",$results))."</articadatascgi>";
}
function GLOBAL_STATUS(){
exec("/usr/share/artica-postfix/bin/artica-install --all-status",$results);	
echo "<articadatascgi>". base64_encode((implode("\n",$results)))."</articadatascgi>";
}

function MONIT_STATUS(){
	$unix=new unix();
	$array=$unix->monit_array();
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}
function MONIT_RESTART(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart monit");
}
function DNS_LIST(){
	exec("/usr/share/artica-postfix/bin/artica-install --local-dns",$results);
	echo "<articadatascgi>". implode("",$results)."</articadatascgi>";
}

FUNCTION procstat(){
	exec("/usr/share/artica-postfix/bin/procstat {$_GET["procstat"]}",$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#(.+?):(.+)#",$line,$re)){
			$array[trim($re[1])]=trim($re[2]);
		}
	}
	
	if($array["start_time"]<>null){
		if(preg_match("#\(([0-9]+)#",$array["start_time"],$re)){
			$mins=$re[1]/60;
			$text="{$mins}mn";
			if($mins>60){
				$h=round($mins/60,2);
				if(preg_match("#(.+?)\.(.+)#",$h,$re)){
					if(strlen($re[2])==1){$re[2]="{$re[2]}0";}
					$text="{$re[1]}h {$re[2]}mn";
				}else{
					$text="{$h}h";
				}
			}
		}
	}
	
	$array["since"]=$text;
	
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";	
	
}

function imapsync_events(){
	$f="/usr/share/artica-postfix/ressources/logs/imapsync.{$_GET["imapsync-events"]}.logs";
	if(is_file($f)){
		exec("tail -n 300 $f",$datas);
	}else{
		writelogs_framework("unable to stat imapsync.{$_GET["imapsync-events"]}.logs",__FUNCTION__,__FILE__);
		exit;
	}
	writelogs_framework(basename($f).": ".count($datas)." rows",__FUNCTION__,__FILE__);
	echo "<articadatascgi>". base64_encode(serialize($datas))."</articadatascgi>";	
}

function imapsync_cron(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.mailsync.php --cron");
}
function imapsync_run(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.mailsync.php --sync {$_GET["imapsync-run"]}");
}
function imapsync_stop(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.mailsync.php --stop {$_GET["imapsync-stop"]}");
}

function cyrus_restore_mount_dir(){
	$taskid=$_GET["cyr-restore"];
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.backup.php --mount $taskid",$results);
	writelogs_framework(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.backup.php --mount $taskid",__FUNCTION__,__FILE__);
	$datas=trim(implode("",$results));
	writelogs_framework(strlen($datas)." bytes",__FUNCTION__,__FILE__);
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";	
	
}

function cyr_restore_computer(){
	$taskid=$_GET["cyr-restore-computer"];
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.backup.php --mount --id=$taskid --dir={$_GET["dir"]}",$results);
	$datas=trim(implode("",$results));
	writelogs_framework($datas,__FUNCTION__,__FILE__);
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";		
	// cyr-restore-computer
	
}
//cyr-restore-container
function cyr_restore_container(){
	$taskid=$_GET["cyr-restore-container"];
	$_GET["dir"]=base64_decode($_GET["dir"]);
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.backup.php --mount --id=$taskid --dir={$_GET["dir"]} --list",$results);
	$datas=trim(implode("",$results));
	writelogs_framework($datas,__FUNCTION__,__FILE__);
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";			
}
function cyr_restore_mailbox(){
	$datas=$_GET["cyr-restore-mailbox"];
	writelogs_framework($datas,__FUNCTION__,__FILE__);
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.backup.php --restore-mbx $datas");
}
function disk_format_big_partition(){
	exec("/usr/share/artica-postfix/bin/artica-install --format-b-part {$_GET["dev"]} {$_GET["label"]}",$datas);
	$r=implode("\n",$datas);
	echo "<articadatascgi>". base64_encode($r)."</articadatascgi>";			
	
}
function RestartRsyncServer(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart rsync");
}

function rsync_load_config(){
	$datas=@file_get_contents("/etc/rsync/rsyncd.conf");
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";		
}
function rsync_save_conf(){
	$datas=base64_decode($_GET["rsync-save-conf"]);
	@file_put_contents("/etc/rsync/rsyncd.conf",$datas);
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reload-rsync");
}
function ARTICA_MAILLOG_RESTART(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart postfix-logger");
}
function disk_directory_size(){
	$dir=base64_decode($_GET["DirectorySize"]);
	$unix=new unix();
	exec($unix->find_program("du")." -h -s $dir 2>&1",$results);
	$r=implode("",$results);
	if(preg_match("#^(.+?)\s+#",$r,$re)){
		echo "<articadatascgi>". $re[1]."</articadatascgi>";		
	}
}

function cyrus_move_default_dir_to_currentdir(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.cyrus-restore.php --move-default-current");
}
function  cyrus_move_newdir(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.cyrus-restore.php --move-new-dir {$_GET["cyrus-SaveNewDir"]}");
}
function cyrus_rebuild_all_mailboxes(){
	$f="/usr/share/artica-postfix/ressources/logs/web/". md5($_GET["cyrus-rebuild-all-mailboxes"])."-mailboxes-rebuilded.log";
	@unlink("$f");
	@file_put_contents($f,"");
	@chmod($f,755);
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.cyrus-restore.php --rebuildmailboxes {$_GET["cyrus-rebuild-all-mailboxes"]}");
	
}
function postfix_hash_tables(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php");
}
function postfix_hash_transport_maps(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php --transport");
}
function postfix_hash_senderdependent(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php --smtp-passwords");
}
function postfix_hash_aliases(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php --aliases");
}
function postfix_hash_bcc(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php --bcc");
}
function postfix_others_values(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --others-values");
}
function postfix_mime_header_checks(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --mime-header-checks");
}

function postfix_interfaces(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --interfaces");
}

function CleanCache(){
	shell_exec("/bin/rm /usr/share/artica-postfix/ressources/logs/web/cache/*");
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.shm.php --remove");
}
function zarafa_admin_chock(){
	sys_THREAD_COMMAND_SET("/usr/local/bin/zarafa-admin -l");
}
function zarafa_user_create_store(){
	sys_THREAD_COMMAND_SET("/usr/local/bin/zarafa-admin --create-store {$_GET["zarafa-user-create-store"]}");
}


function zarafa_migrate(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.zarafa-migrate.php {$_GET["zarafa-migrate"]}");
}
function zarafa_restart_web(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart zarafa-web");
}
function RestartAmavis(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart amavis");
}

function zarafa_user_details(){
	$cmd="/usr/local/bin/zarafa-admin --details {$_GET["zarafa-user-details"]}";
	exec($cmd,$results);
	while (list ($index, $line) = each ($results) ){
		$line=trim($line);
		if(preg_match("#(.+?):(.+)#",$line,$re)){
			$key=trim($re[1]);
			$value=trim($re[2]);
			if($value=="unlimited"){$value=0;}
			if($value=="yes"){$value=1;}
			if($value=="no"){$value=0;}
			$array[$key]=$value;
			
		}
	}
	
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";	
	
	
}
function fstab_acl(){
	$acl_enabled=$_GET["acl"];
	$dev=base64_decode($_GET["dev"]);
	writelogs_framework("$dev= enable acl=$acl_enabled",__FUNCTION__,__FILE__);
	$unix=new unix();
	$unix->FSTAB_ACL($dev,$acl_enabled);
	}
	
function samba_add_acl_group(){
	$group=base64_decode($_GET["group"]);
	$path=base64_decode($_GET["path"]);
	$unix=new unix();
	$setfacl=$unix->find_program("setfacl");
	if($setfacl==null){
		$results[]="Unable to stat setfacl";
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
		return ;	
	}
	$cmd="$setfacl -m group:\"$group\":r \"$path\" 2>&1";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__);
	exec($cmd,$results);
	samba_change_acl_items(1);
	if(is_array($results)){
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	}
	
}
function samba_add_acl_user(){
	$user=base64_decode($_GET["username"]);
	$path=base64_decode($_GET["path"]);
	$unix=new unix();
	$setfacl=$unix->find_program("setfacl");
	if($setfacl==null){
		$results[]="Unable to stat setfacl";
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
		return ;	
	}
	$cmd="$setfacl -m u:\"$user\":r \"$path\" 2>&1";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__);
	exec($cmd,$results);
	samba_change_acl_items(1);
	if(is_array($results)){
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	}	
}

function samba_delete_acl_group(){
	$group=base64_decode($_GET["group"]);
	$path=base64_decode($_GET["path"]);
	$unix=new unix();
	$setfacl=$unix->find_program("setfacl");
	if($setfacl==null){
		$results[]="Unable to stat setfacl";
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
		return ;	
	}
	$cmd="$setfacl -x group:\"$group\" \"$path\" 2>&1";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__);
	exec($cmd,$results);
	samba_change_acl_items(1);
	if(is_array($results)){
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	}	
	
}
function samba_delete_acl_user(){
	$user=base64_decode($_GET["username"]);
	$path=base64_decode($_GET["path"]);
	$unix=new unix();
	$setfacl=$unix->find_program("setfacl");
	if($setfacl==null){
		$results[]="Unable to stat setfacl";
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
		return ;	
	}
	$cmd="$setfacl -x u:\"$user\" \"$path\" 2>&1";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__);
	exec($cmd,$results);
	samba_change_acl_items(1);
	if(is_array($results)){
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	}	
}

function samba_change_acl_group(){
	$group=base64_decode($_GET["group"]);
	$path=base64_decode($_GET["path"]);
	$unix=new unix();
	$setfacl=$unix->find_program("setfacl");
	if($setfacl==null){
		$results[]="Unable to stat setfacl";
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
		return ;	
	}
	if($_GET["chmod"]==null){$_GET["chmod"]='---';}
	$cmd="$setfacl -m group:\"$group\":{$_GET["chmod"]} \"$path\" 2>&1";
	
	if($group=="GROUP"){
		$cmd="$setfacl -m g::{$_GET["chmod"]} \"$path\" 2>&1";
	}
	if($group=="OTHER"){
		$cmd="$setfacl -m o::{$_GET["chmod"]} \"$path\" 2>&1";
	}	
	
	writelogs_framework("$cmd",__FUNCTION__,__FILE__);
	exec($cmd,$results);
	samba_change_acl_items(1);
	if(is_array($results)){
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	}	
	
}

function samba_change_acl_items($noecho=0){
$path=base64_decode($_GET["path"]);
$unix=new unix();
	$setfacl=$unix->find_program("setfacl");
	$getfacl=$unix->find_program("getfacl");
	
	if($setfacl==null){
		$results[]="Unable to stat setfacl";
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
		return ;	
	}
	
	if($_GET["default"]==1){
		$cmd="$getfacl --access \"$path\" | $setfacl -d -M- \"$path\"";
		writelogs_framework("$cmd",__FUNCTION__,__FILE__);
		exec($cmd,$results);
	}	
	
	if($_GET["recursive"]==1){
		$cmd="$getfacl --access \"$path\" | $setfacl -R -M- \"$path\"";
		writelogs_framework("$cmd",__FUNCTION__,__FILE__);
		exec($cmd,$results);
	}

	if($noecho==1){return;}
	if(is_array($results)){
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	}	

}

function samba_change_acl_user(){
	$username=base64_decode($_GET["username"]);
	$path=base64_decode($_GET["path"]);
	$unix=new unix();
	$setfacl=$unix->find_program("setfacl");
	if($setfacl==null){
		$results[]="Unable to stat setfacl";
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
		return ;	
	}
	if($_GET["chmod"]==null){$_GET["chmod"]='---';}
	$cmd="$setfacl -m u:\"$username\":{$_GET["chmod"]} \"$path\" 2>&1";
	
	if($username=="OWNER"){
		$cmd="$setfacl -m u::{$_GET["chmod"]} \"$path\" 2>&1";
	}
	
	writelogs_framework("$cmd",__FUNCTION__,__FILE__);
	exec($cmd,$results);
	samba_change_acl_items(1);
	if(is_array($results)){
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	}		
}
	
function SAMBA_HAVE_POSIX_ACLS(){
	$unix=new unix();
	$HAVE_POSIX_ACLS="FALSE";
	$smbd=$unix->find_program("smbd");
	$grep=$unix->find_program("grep");
	exec("$smbd -b|$grep -i acl 2>&1",$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#HAVE_POSIX_ACLS#",$line)){
			$HAVE_POSIX_ACLS="TRUE";
			break;
		}
	}
	
	echo "<articadatascgi>". base64_encode($HAVE_POSIX_ACLS)."</articadatascgi>";	
	}

function dansguardian_template(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --dansguardian-template");
}

function dansguardian_categories(){
$unix=new unix();
	
	
}	

function find_sock_program(){
	$unix=new unix();
	echo "<articadatascgi>".  base64_encode($unix->find_program($_GET["find-program"]))."</articadatascgi>";	
}

function squidGuardDatabaseStatus(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squidguard.php --db-status-www",$ri);
	echo "<articadatascgi>".  base64_encode(implode("",$ri))."</articadatascgi>";
}
function squidGuardStatus(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squidguard.php --status",$ri);
	echo "<articadatascgi>".  base64_encode(implode("\n",$ri))."</articadatascgi>";	
}

function squidGuardCompile(){
	$l="/usr/share/artica-postfix/ressources/logs/squidguard.compile.db.txt";
	@file_put_contents($l,"{waiting}...");
	@chmod($l,0777);
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squidguard.php --compile >>$l 2>&1");
	
}

function squidguardTests(){
	$uri=base64_decode($_GET["uri"]);
	$client=base64_decode($_GET["client"]);	
	$unix=new unix();
	$squidGuard=$unix->find_program("squidGuard");
	$echo=$unix->find_program("echo");
	$cmd="$echo \"$uri $client/- - GET\" | $squidGuard -c /etc/squid/squidGuard.conf -d 2>&1";
	exec($cmd,$results);
	$results[]=$cmd;
	echo "<articadatascgi>".  base64_encode(serialize($results))."</articadatascgi>";	
	
	
}


function SQUID_CACHE_INFOS(){
	$unix=new unix();
	$squidclient=$unix->find_program("squidclient");
	if($squidclient==null){return;}
	$cmd="$squidclient mgr:storedir";
	exec($cmd,$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#Store Directory\s+\#([0-9]+).+?:\s+(.+)#",$line,$re)){
			$path=trim($re[2]);
			$array[$path]["index"]=$re[1];
			continue;
		}
		if(preg_match("#Maximum Size:\s+([0-9]+)#",$line,$re)){
			$array[$path]["MAX"]=$re[1];
		}
		
		if(preg_match("#Current Size:\s+([0-9]+)#",$line,$re)){
			$array[$path]["CURRENT"]=$re[1];
		}		

		if(preg_match("#Percent Used:\s+([0-9\.]+)#",$line,$re)){
			$array[$path]["POURC"]=$re[1];
		}			
		
		
	}
	echo "<articadatascgi>".  base64_encode(serialize($array))."</articadatascgi>";	
}


function cicap_reconfigure(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.c-icap.php --build");
}

function cicap_reload(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --c-icap-reload");
}

function SQUID_RESTART_NOW(){
	shell_exec("/etc/init.d/artica-postfix restart squid-cache");
}

function iwlist(){
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.wifi.detect.cards.php --iwlist");
}
function WIFI_CONNECT_AP(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.wifi.detect.cards.php --ap",$r);	
	echo "<articadatascgi>".  base64_encode(implode("\n",$r))."</articadatascgi>";	
}
function start_wifi(){
	shell_exec("/etc/init.d/artica-postfix start wifi");
}

function WIFI_ETH_STATUS(){
	$unix=new unix();
	$eth=$unix->GET_WIRELESS_CARD();
	if($eth==null){
		writelogs_framework("NO eth card found",__FUNCTION__,__FILE__,__LINE__);
		return null;
	}
	$wpa_cli=$unix->find_program("wpa_cli");
	if($wpa_cli==null){
		writelogs_framework("NO wpa_cli found",__FUNCTION__,__FILE__,__LINE__);
		return null;
	}
	exec("$wpa_cli -p/var/run/wpa_supplicant status -i{$eth}",$results);
	writelogs_framework(count($results)." lines",__FUNCTION__,__FILE__,__LINE__);
	$conf="[IF]\neth=$eth\n".implode("\n",$results);
	echo "<articadatascgi>".  base64_encode($conf)."</articadatascgi>";	
}
function WIFI_ETH_CHECK(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.wifi.detect.cards.php --checkap",$r);
	echo "<articadatascgi>".  base64_encode(implode("\n",$r))."</articadatascgi>";	
}
function ChangeHostName(){
	$servername=$_GET["ChangeHostName"];
	shell_exec("/usr/share/artica-postfix/bin/artica-install --change-hostname $servername");
	
}
function hostname_full(){
	$unix=new unix();
	$ypdomainname=$unix->find_program("ypdomainname");
	$hostname=$unix->find_program("hostname");
	$sysctl=$unix->find_program("sysctl");
	if($ypdomainname<>null){
		exec("$ypdomainname",$results);
		$domain=trim(@implode(" ",$results));
		
		
	}else{
		exec("$sysctl -n kernel.domainname",$results);
		$domain=trim(@implode(" ",$results));
	}
	unset($results);
	exec("$hostname -s",$results);
	$host=trim(@implode(" ",$results));
	if(preg_match("#not set#",$domain)){$domain=null;}
	if(preg_match("#\(none#",$domain)){$domain=null;}
	if(strlen($domain)>0){$host="$host.$domain";}
	$host=str_replace('.(none)',"",$host);
	echo "<articadatascgi>$host</articadatascgi>";	
	
}
function GetUniqueID(){
	$uuid=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/SYSTEMID"));
	if($uuid==null){
		$unix=new unix();
		$blkid=$unix->find_program("blkid");
		exec($blkid,$results);
		while (list ($index, $line) = each ($results) ){
			if(preg_match("#UUID=\"(.+?)\"#",$line,$re)){
				$uuid=$re[1];
			}
		}
		
		@file_put_contents("/etc/artica-postfix/settings/Daemons/SYSTEMID",$uuid);
	}
	
	echo "<articadatascgi>". base64_encode($uuid)."</articadatascgi>";	
	
}

function shalla_update(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-update --filter-plus --force");
}

function dansguardian_search_categories(){
	$www=base64_decode($_GET["searchww-cat"]);
	
	if(preg_match("#www\.(.+?)$#i",$www,$re)){$www=$re[1];}
	writelogs_framework("Search \"$www\" :=>{$_GET["searchww-cat"]}",__FUNCTION__,__FILE__,__LINE__);
	
	$dansguardian_enabled=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/DansGuardianEnabled"));
	if($dansguardian_enabled==null){$dansguardian_enabled=0;}
	$squidGuardEnabled=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/squidGuardEnabled"));
	if($squidGuardEnabled==null){$squidGuardEnabled=0;}
	
	if($squidGuardEnabled==1){$path="/var/lib/squidguard";}
	if($dansguardian_enabled==1){$path="/etc/dansguardian/lists";}
	
	if($path==null){return;}
	
	$unix=new unix();
	$www=str_replace(".","\.",$www);
	$www="^$www$";
	$grep=$unix->find_program("grep");
	$cmd="$grep -R -E \"$www\" --mmap -s -l $path";
	exec($cmd,$results);
	writelogs_framework("$cmd -> ".count($results)." lines",__FUNCTION__,__FILE__,__LINE__);
	while (list ($index, $line) = each ($results) ){
		$line=trim(str_replace("$path/","",$line));
		unset($re);
		writelogs_framework("Search \"$www\" :=>\"$line\"",__FUNCTION__,__FILE__,__LINE__);
		if(preg_match("#web-filter-plus\/BL\/(.+?)\/domains$#",$line,$re)){
			
			$array[$re[1]]=true;
			continue;
		}
		
		if(preg_match("#blacklist-artica\/(.+?)\/domains$#",$line,$re)){
			$array[$re[1]]=true;
			continue;
		}
		
		if(preg_match("lists\/blacklists.+*\/(.+?)\/domains$#",$line,$re)){
			$array[$re[1]]=true;
			continue;
		}
		
		if(preg_match("#^blacklists\/#",$line,$re)){continue;}
		
		if(preg_match("#^(.+?)\/domains$#",$line,$re)){
			
			$array[$re[1]]=true;
			continue;
		}

		if(preg_match("#personal-categories#",$line)){continue;}
		
	}
	
	writelogs_framework(serialize($array),__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";	
}
function dansguardian_community_categories(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.web-community-filter.php");	
}


?>