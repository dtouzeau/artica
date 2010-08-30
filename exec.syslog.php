<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(!Build_pid_func(__FILE__,"MAIN")){
	events(basename(__FILE__)." Already executed.. aborting the process");
	die();
}


$pid=getmypid();
$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
@mkdir("/var/log/artica-postfix/xapian",0755,true);
@mkdir("/var/log/artica-postfix/infected-queue",0755,true);
events("running $pid ");
file_put_contents($pidfile,$pid);
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');

$GLOBALS["RSYNC_RECEIVE"]=array();
$GLOBALS["LOCATE_PHP5_BIN"]=LOCATE_PHP5_BIN();

$users=new usersMenus();
$_GET["server"]=$users->hostname;
$pipe = fopen("php://stdin", "r");
while(!feof($pipe)){
$buffer .= fgets($pipe, 4096);
try{ Parseline($buffer);}catch (Exception $e) {events("fatal error:".  $e->getMessage());}

$buffer=null;
}
fclose($pipe);
events("Shutdown...");
die();
function Parseline($buffer){
$buffer=trim($buffer);	



if(preg_match("#artica-filter#",$buffer)){return true;}
if(preg_match("#postfix\/#",$buffer)){return true;}
if(preg_match("#CRON\[#",$buffer)){return true;}
if(preg_match("#: CACHEMGR:#",$buffer)){return true;}
if(preg_match("#exec\.postfix-logger\.php:#",$buffer)){return true;}
if(preg_match("#artica-install\[#",$buffer)){return true;}
if(preg_match("#monitor action done#",$buffer)){return true;}
if(preg_match("#monitor service.+?on user request#",$buffer)){return true;}
if(preg_match("#CRON\[.+?\(root\).+CMD#",$buffer)){return true;}
if(preg_match("#winbindd\[.+?winbindd_listen_fde_handler#",$buffer)){return true;}


if(preg_match('#smbd\[.+Ignoring unknown parameter\s+"hide_unwriteable_files"#',$buffer,$re)){
	events("SAMBA unknown parameter hide_unwriteable_files");
	$file="/etc/artica-postfix/croned.1/hide_unwriteable_files";
	if(IfFileTime($file)){
		email_events("Samba unknown parameter hide_unwriteable_files","Samba claim \"$buffer\" Artica will correct the configuration file",'system');
		shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.samba.php --fix-HideUnwriteableFiles &");
		@file_put_contents($file,"#");
	}
	return true;
}


if(preg_match('#load_usershare_shares: directory\s+(.+?)\s+is not owned by root or does not have the sticky bit#',$buffer,$re)){
	events("SAMBA load_usershare_shares {$re[1]}");
	$file="/etc/artica-postfix/croned.1/load_usershare_shares";
	if(IfFileTime($file)){
		email_events("Samba load_usershare_shares permissions issues","Samba claim \"$buffer\" Artica will correct the filesystem directory",'system');
		shell_exec("chmod 1775 $re[1]/ &");
		shell_exec("chmod chmod +t $re[1]/ &");
		@file_put_contents($file,"#");
	}
	return true;	
}


if(preg_match("#amavis\[.+?:\s+\(.+?\)TROUBLE\s+in child_init_hook:#",$buffer,$re)){
	events("AMAVIS TROUBLE in child_init_hook");
	$file="/etc/artica-postfix/croned.1/amavis.".md5("AMAVIS:TROUBLE in child_init_hook");
	if(IfFileTime($file)){
		email_events("Amavis child error","Amavis claim \"$buffer\" the amavis daemon will be restarted",'postfix');
		shell_exec('/etc/init.d/artica-postfix restart amavis &');
		@file_put_contents($file,"#");
	}
	return true;
}

if(preg_match("#amavis\[.+?:\s+\(.+?\)_DIE:\s+Suicide in child_init_hook#",$buffer,$re)){
	events("AMAVIS TROUBLE in child_init_hook");
	$file="/etc/artica-postfix/croned.1/amavis.".md5("AMAVIS:TROUBLE in child_init_hook");
	if(IfFileTime($file)){
		email_events("Amavis child error","Amavis claim \"$buffer\" the amavis daemon will be restarted",'postfix');
		shell_exec('/etc/init.d/artica-postfix restart amavis &');
		@file_put_contents($file,"#");
	}
	return true;
}


if(preg_match("#smbd_audit:\s+(.+?)\|(.+?)\|(.+?)\|(.+?)\|(.+?)\|(.+?)\|(.+?)\|(.+?)$#",$buffer,$re)){
	events("{$re[5]}/{$re[8]} in xapian queue");
	WriteXapian("{$re[5]}/{$re[8]}"); 
	return true;
}


if(preg_match("#squid\[.+?comm_old_accept:\s+FD\s+15:.+?Invalid argument#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/comm_old_accept.FD15";
	if(IfFileTime($file)){
			events("comm_old_accept FD15 SQUID");
			email_events("Squid File System error","SQUID claim \"$buffer\" the squid service will be restarted",'system');
			THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart squid-cache');
			WriteFileCache($file);
			return;
		}else{
			events("comm_old_accept FD15 SQUID");
			return;
		}	
}

if(preg_match("#dansguardian.+?:\s+Error connecting to proxy#",$buffer,$re)){
		$file="/etc/artica-postfix/croned.1/squid.tostart.error";
		if(IfFileTime($file,2)){
			events("Squid not available...! Artica will start squid");
			email_events("Proxy error","DansGuardian claim \"$buffer\", Artica will start squid ",'system');
			THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart squid-cache');
			THREAD_COMMAND_SET('/etc/init.d/artica-postfix start dansguardian');
			WriteFileCache($file);
			return;
		}else{
			events("Proxy error, but take action after 10mn");
			return;
		}		
}


if(preg_match("#zarafa-server.+?INNODB engine is disabled#",$buffer)){
	$file="/etc/artica-postfix/croned.1/zarafa.INNODB.engine";
	if(IfFileTime($file,2)){
			events("Zarafa innodb errr");
			THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart mysql');
			THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart zarafa');
			WriteFileCache($file);
			return;
		}else{
			events("Zarafa innodb err, but take action after 10mn");
			return;
		}			
}


if(preg_match("#(.+?)\[.+?segfault at.+?error.+?in.+?\[#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/segfault.{$re[1]}";
	if(IfFileTime($file,10)){
		events("{$re[1]}: segfault");
		email_events("{$re[1]}: segfault","Kernel claim \"$buffer\" ",'system');
		WriteFileCache($file);
		return;	
	}
}

if(preg_match("#kernel:.+?Out of memory:\s+kill\s+process\s+#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/kernel.Out.of.memory";
	if(IfFileTime($file,1)){
		events("Out of memory -> REBOOT !!!");
		email_events("Out of memory ! server will be rebooted","Kernel claim \"$buffer\" the server will be rebooted",'system');
		WriteFileCache($file);
		shell_exec("/etc/init.d/artica-postfix stop");
		shell_exec("reboot");
		return;	
	}
}

if(preg_match("#winbindd\[.+?failed to bind to server\s+(.+?)\s+with dn.+?Error: Can.+?contact LDAP server#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/winbindd.ldap.failed";
	if(IfFileTime($file,10)){
		events("winbindd -> LDAP FAIELD");
		email_events("LDAP server is unavailable","Samba claim \"$buffer\" artica will try to restart LDAP server ",'system');
		WriteFileCache($file);
		THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart ldap');
		return;	
	}
}


if(preg_match("#winbindd\[.+?resolve_name: unknown name switch type lmhost#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/winbindd.lmhost.failed";
	if(IfFileTime($file,10)){
		events("winbindd -> lmhost failed");
		WriteFileCache($file);
		THREAD_COMMAND_SET("{$GLOBALS["LOCATE_PHP5_BIN"]} /usr/share/artica-postfix/exec.samba.php --fix-lmhost");
		return;	
	}	
}

if(preg_match("#nmbd\[.+?become_logon_server_success: Samba is now a logon server for workgroup (.+?)\s+on subnet\s+([A-Z0-9\._-]+)#",$buffer,$re)){
	email_events("Samba (file sharing) started domain {$re[1]}/{$re[2]}","Samba notice: \"$buffer\"",'system');
	return;	
}




if(preg_match("#zarafa-server.+?Unable to connect to database.+?MySQL server on.+?([0-9\.]+)#",$buffer)){
	$file="/etc/artica-postfix/croned.1/zarafa.MYSQL.CONNECT";
	if(IfFileTime($file,2)){
			events("Zarafa Mysql Error errr");
			email_events("MailBox server unable connect to database","Zarafa server  claim \"$buffer\" ",'mailbox');
			WriteFileCache($file);
			return;
		}else{
			events("MailBox server unable connect to database but take action after 10mn");
			return;
		}			
}

if(preg_match("#winbindd:\s+Exceeding\s+[0-9]+\s+client\s+connections.+?no idle connection found#",$buffer)){
	$file="/etc/artica-postfix/croned.1/Winbindd.connect.error";
	if(IfFileTime($file,2)){
			events("winbindd Error connections");
			email_events("Winbindd exceeding connections","Samba server  claim \"$buffer\" \nArtica will restart samba",'system');
			shell_exec('/etc/init.d/artica-postfix restart samba &');
			WriteFileCache($file);
			return;
		}else{
			events("Winbindd exceeding connections take action after 10mn");
			return;
		}			
}




// -------------------------------------------------------------------- MONIT


if(preg_match("#'(.+?)'\s+total mem amount of\s+([0-9]+).+?matches resource limit#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/mem.{$re[1]}.monit";
	if(IfFileTime($file,15)){
				events("{$re[1]} limit memory exceed");
				email_events("{$re[1]}: memory limit","Monitor claim \"$buffer\"",'system');
				WriteFileCache($file);
				return;
			}else{
				events("{$re[1]} limit memory exceed, but take action after 10mn");
				return;
			}			
	}
if(preg_match("#monit\[.+?'(.+?)'\s+trying to restart#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/restart.{$re[1]}.monit";
	if(IfFileTime($file,5)){
				events("{$re[1]} was restarted");
				email_events("{$re[1]}: stopped, try to restart","Monitor claim \"$buffer\"",'system');
				WriteFileCache($file);
				return;
			}else{
				events("{$re[1]}: stopped, try to restart, but take action after 10mn");
				return;
			}			
	}

if(preg_match("#monit\[.+?'(.+?)'\s+process is not running#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/restart.{$re[1]}.monit";
	if(IfFileTime($file,5)){
				events("{$re[1]} was stopped");
				email_events("{$re[1]}: stopped","Monitor claim \"$buffer\"",'system');
				WriteFileCache($file);
				return;
			}else{
				events("{$re[1]}: stopped, but take action after 10mn");
				return;
			}			
	}
	
	
if(preg_match("#pdns\[.+?:\s+binding UDP socket to.+?Address already in use#",$buffer,$re)){
$file="/etc/artica-postfix/croned.1/restart.pdns.bind.error";
	if(IfFileTime($file,5)){
				events("PowerDNS: Unable to bind UDP socket");
				email_events("PowerDNS: Unable to bind UDP socket","Artica will restart PowerDNS",'system');
				THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart pdns');
				WriteFileCache($file);
				return;
			}else{
				events("PowerDNS: Unable to bind UDP socket: but take action after 10mn");
				return;
			}			
	}	
	

	
if(preg_match("#cpu system usage of ([0-9\.]+)% matches#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/cpu.system.monit";
	if(IfFileTime($file,15)){
				events("cpu exceed");
				email_events("cpu warning {$re[1]}%","Monitor claim \"$buffer\"",'system');
				WriteFileCache($file);
				return;
			}else{
				events("cpu exceed, but take action after 10mn");
				return;
			}			
	}

if(preg_match("#monit.+?'(.+)'\s+start:#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/monit.start.{$re[1]}";
	if(IfFileTime($file,5)){
				events("{$re[1]} start");
				email_events("{$re[1]} starting","Monitor currently starting service {$re[1]}",'system');
				WriteFileCache($file);
				return;
			}else{
				events("{$re[1]} start, but take action after 10mn");
				return;
			}			
	}		

if(preg_match("#monit\[.+?:\s+'(.+?)'\s+process is running with pid\s+([0-9]+)#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/monit.run.{$re[1]}";
	if(IfFileTime($file,5)){
				events("{$re[1]} running");
				email_events("{$re[1]} now running pid {$re[2]}","Monitor report $buffer",'system');
				WriteFileCache($file);
				return;
			}else{
				events("{$re[1]} running, but take action after 10mn");
				return;
			}			
	}		
	
if(preg_match("#nmbd.+?:\s+Cannot sync browser lists#",$buffer,$re)){
		$file="/etc/artica-postfix/croned.1/samba.CannotSyncBrowserLists.error";
		if(IfFileTime($file)){
			events("Samba cannot sync browser list, remove /var/lib/samba/wins.dat");
			@unlink("/var/lib/samba/wins.dat");
			WriteFileCache($file);
		}else{
			events("Samba error:$buffer, but take action after 10mn");
			return;
		}		
}

if(preg_match("#freshclam.+?:\s+Database updated \(([0-9]+)\s+signatures\) from .+?#",$buffer,$re)){
			email_events("ClamAV Database Updated {$re[1]} signatures","$buffer",'update');
			return;
		}
		
if(preg_match("#squid.+?:\s+essential ICAP service is down after an options fetch failure:\s+icap:\/\/:1344\/av\/respmod#",$buffer,$re)){
		$file="/etc/artica-postfix/croned.1/squid.icap1.error";
		if(IfFileTime($file)){
			email_events("Kaspersky for Squid Down","$buffer",'system');
			THREAD_COMMAND_SET('/etc/init.d/artica-postfix start kav4proxy');
			THREAD_COMMAND_SET('squid -k reconfigure');
			WriteFileCache($file);
			return;
		}else{
			events("KAV4PROXY error:$buffer, but take action after 10mn");
			return;
		}			
}

if(preg_match("#KASERROR.+?NOLOGID.+?Can.+?find user mailflt3#",$buffer)){
	$file="/etc/artica-postfix/croned.1/KASERROR.NOLOGID.mailflt3";
		if(IfFileTime($file)){
			THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --mailflt3');
			WriteFileCache($file);
			return;
		}else{
			events("KASERROR error:$buffer, but take action after 10mn");
			return;
		}		
}

if(preg_match("#lmtp.+?status=deferred.+?lmtp\]:.+?(No such file or directory|Too many levels of symbolic links)#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/cyrus.lmtp.failed";
		if(IfFileTime($file)){
			email_events("cyrus-imapd socket error","Postfix claim \"$buffer\", Artica will restart cyrus",'system');
			THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --cyrus-checkconfig');
			THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart imap');
			THREAD_COMMAND_SET("{$GLOBALS["LOCATE_PHP5_BIN"]} /usr/share/artica-postfix/exec.postfix.main.cf.php --imap-sockets");
			cyrus_socket_error($buffer,$re[1]."lmtp");
			WriteFileCache($file);
			return;
		}else{
			events("CYRUS error:$buffer, but take action after 10mn");
			return;
		}		
}


if(preg_match("#dhcpd: DHCPREQUEST for (.+?)\s+from\s+(.+?)\s+\((.+?)\)\s+via#",$buffer,$re)){
	events("DHCPD: IP:{$re[1]} MAC:({$re[2]}) computer name={$re[3]}-> exec.dhcpd-leases.php");
	THREAD_COMMAND_SET("{$GLOBALS["LOCATE_PHP5_BIN"]} /usr/share/artica-postfix/exec.dhcpd-leases.php --single-computer {$re[1]} {$re[2]} {$re[3]}");
	return;
}


if(preg_match("#rsyncd\[.+?:\s+recv.+?\[(.+?)\].+?([0-9]+)$#",$buffer,$re)){
	$file=md5($buffer);
	@mkdir('/var/log/artica-postfix/rsync',null,true);
	$f["IP"]=$re[1];
	$f["DATE"]=date('Y-m-d H:00:00');
	$f["SIZE"]=$re[2];
	@file_put_contents("/var/log/artica-postfix/rsync/$file",serialize($f));
}






if(preg_match("#kavmilter.+?Can.+?t load keys: No active key#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/kavmilter.key.failed";
		if(IfFileTime($file)){
			email_events("Kaspersky Antivirus Mail license error","KavMilter claim \"$buffer\"",'system');
			WriteFileCache($file);
			return;
		}else{
			events("Kaspersky Antivirus Mail license error:$buffer, but take action after 10mn");
			return;
		}		
}

if(preg_match("#kavmd.+?Can.+?t load keys:.+?#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/kavmd.key.failed";
		if(IfFileTime($file)){
			email_events("Kaspersky Antivirus Mail license error","Kaspersky Antivirus Mail claim \"$buffer\"",'system');
			WriteFileCache($file);
			return;
		}else{
			events("Kaspersky Antivirus Mail license error:$buffer, but take action after 10mn");
			return;
		}		
}

if(preg_match("#kavmd.+?ERROR Engine problem#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/kavmd.engine.failed";
		if(IfFileTime($file)){
			email_events("Kaspersky Antivirus Mail Engine error","Kaspersky Antivirus Mail claim \"$buffer\"",'system');
			WriteFileCache($file);
			return;
		}else{
			events("Kaspersky Antivirus Mail Engine error:$buffer, but take action after 10mn");
			return;
		}		
}



if(preg_match("#kavmilter.+?WARNING.+?Your AV signatures are older than#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/kavmilter.upd.failed";
		if(IfFileTime($file)){
			email_events("Kaspersky Antivirus Mail AV signatures are older","KavMilter claim \"$buffer\"",'system');
			WriteFileCache($file);
			return;
		}else{
			events("Kaspersky Antivirus update license error:$buffer, but take action after 10mn");
			return;
		}		
}
if(preg_match("#dansguardian.+?Error compiling regexp#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/dansguardian.compiling.regexp";
		if(IfFileTime($file)){
			email_events("Dansguardian failed to start","Dansguardian claim \"$buffer\"",'system');
			WriteFileCache($file);
			return;
		}else{
			events("Dansguardian failed to start:$buffer, but take action after 10mn");
			return;
		}		
}

if(preg_match("#kavmilter.+?Invalid value specified for SendmailPath#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/kavmilter.SendmailPath.Invalid";
		if(IfFileTime($file)){
			events("Check SendmailPath for kavmilter");
			THREAD_COMMAND_SET("{$GLOBALS["LOCATE_PHP5_BIN"]} /usr/share/artica-postfix/exec.kavmilter.php --SendmailPath");
			WriteFileCache($file);
			return;
		}else{
			events("Check SendmailPath for kavmilter:$buffer, but take action after 10mn");
			return;
		}		
}

if(preg_match("#KAVMilter Error.+?Group.+?Default.+?has error#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/kavmilter.Default.error";
		if(IfFileTime($file)){
			events("Check Group default for kavmilter");
			THREAD_COMMAND_SET("{$GLOBALS["LOCATE_PHP5_BIN"]} /usr/share/artica-postfix/exec.kavmilter.php --default-group");
			WriteFileCache($file);
			return;
		}else{
			events("Check Group default for kavmilter:$buffer, but take action after 10mn");
			return;
		}		
}

if(preg_match("#kavmilter.+?Message INFECTED from (.+?)\(remote:\[(.+?)\).+?with\s+(.+?)$#",$buffer,$re)){
	events("KAVMILTER INFECTION <{$re[1]}> {$re[2]}");
	infected_queue("kavmilter",trim($re[1]),trim($re[2]),trim($re[3]));
	return;
}


if(preg_match("#pdns\[.+?\[LdapBackend.+?Ldap connection to server failed#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/pdns.ldap.error";
	if(IfFileTime($file)){
			events("PDNS LDAP FAILED");
			email_events("PowerDNS ldap connection failed","PowerDNS claim \"$buffer\"",'system');
			WriteFileCache($file);
			return;
		}else{
			events("PDNS FAILED:$buffer, but take action after 10mn");
			return;
		}		
}





if(preg_match("#master.+?cannot find executable for service.+?sieve#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/cyrus.sieve.error";
		if(IfFileTime($file)){
			events("Check sieve path");
			THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reconfigure-cyrus");
			WriteFileCache($file);
			return;
		}else{
			events("Check sieve path error :$buffer, but take action after 10mn");
			return;
		}		
}


if(preg_match("#smbd\[.+?write_data: write failure in writing to client 0.0.0.0. Error Connection reset by peer#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/samba.Error.Connection.reset.by.peer.error";
		if(IfFileTime($file)){
			events("Check sieve Error Connection reset by peer");
			$text[]="Your MS Windows computers should not have access to the server cause network generic errors";
			$text[]="- Check these parameters:"; 
			$text[]="- Check if Apparmor or SeLinux are disabled on the server.";
			$text[]="- Check your hard drives by this command-line: hdparm -tT /dev/sda(0-9)";
			$text[]="- Check that 137|138|139|445 ports is open from workstation to this server";
			$text[]="- Check network switch or hub connection between this server and your workstations.";
			$text[]="- Try to add this registry key [HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\Disk]\n\t\"TimeOutValue\"=dword:0000003c";
			email_events("Samba network error","Samba claim \"$buffer\"\n" .implode("\n",$text) ,'system');
			WriteFileCache($file);
			return;
		}else{
			events("Check sieve Error Connection reset by peer :$buffer, but take action after 10mn");
			return;
		}		
}

	
events("Not Filtered:\"$buffer\"");		
}




function IfFileTime($file,$min=10){
	if(file_time_min($file)>$min){return true;}
	return false;
}

function WriteFileCache($file){
	@unlink("$file");
	@unlink($file);
	@file_put_contents($file,"#");	
}




function events($text){
		$pid=@getmypid();
		$date=@date("H:i:s");
		$logFile="/var/log/artica-postfix/syslogger.debug";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "[$pid] $date $text\n");
		@fclose($f);	
		}
		
function WriteXapian($path){
	$md=md5($path);
	$f="/var/log/artica-postfix/xapian/$md.queue";
	if(is_file($f)){return null;}
	@file_put_contents($f,$path);
	
}
function email_events($subject,$text,$context){
	events("DETECTED: $subject: $text -> $context");
	send_email_events($subject,$text,$context);
	}

?>
