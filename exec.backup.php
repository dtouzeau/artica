<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/ressources/class.autofs.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.backup.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.cyrus.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');

$date=date('Y-m-d');
$GLOBALS["ADDLOG"]="/var/log/artica-postfix/backup-starter-$date.log";
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}
if(preg_match("#--only-test#",implode(" ",$argv))){$GLOBALS["ONLY_TESTS"]=true;}
if(preg_match("#--no-umount#",implode(" ",$argv))){$GLOBALS["NO_UMOUNT"]=true;}
if(preg_match("#--no-standard-backup#",implode(" ",$argv))){$GLOBALS["NO_STANDARD_BACKUP"]=true;}
$GLOBALS["USE_RSYNC"]=false;

if($argv[1]=="--restore-mbx"){
	restorembx($argv[2]);
	die();
}

if(preg_match("#--cron#",implode(" ",$argv))){
	buildcron();
	die();
}


if($argv[1]=="--usb"){
	mount_usb("usb://{$argv[2]}",0,true);
	die();
}

if($argv[1]=="--mount"){
	$id=$argv[2];
	while (list ($num, $cmd) = each ($argv) ){
		if(preg_match("#--dir=(.+)#",$cmd,$re)){$GLOBALS["DIRLIST"]="/".$re[1];continue;}
		if(preg_match("#--id=([0-9]+)#",$cmd,$re)){$id=$re[1];continue;}
		if(preg_match("#--list#",$cmd,$re)){$GLOBALS["dirlist"]=true;}			
		}
	
	$GLOBALS["ONNLY_MOUNT"]=true;
	writelogs("mounting $id",__FUNCTION__,__FILE__);
	$dir=backup($id);
	ParseMailboxDir($dir);
	if(!$GLOBALS["NO_UMOUNT"]){shell_exec("umount -l $dir");}
	die();
	}


$ID=$argv[1];
if($ID<1){
	writelogs("unable to get task ID",__FUNCTION__,__FILE__,__LINE__);
	die();
}





backup($ID);



function buildcron(){
	$unix=new unix();
	$path="/etc/cron.d";
	
	$sql="SELECT * FROM backup_schedules ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){return null;}	
	
	$files=$unix->DirFiles("/etc/cron.d");
	while (list ($num, $filename) = each ($files) ){
		if(preg_match("#artica-backup-([0-9]+)$#",$filename)){
			echo "Starting......: Backup remove $filename\n";
			@unlink("$path/$filename");
		}
	}
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$schedule=$ligne["schedule"];
		$f[]="$schedule  ". LOCATE_PHP5_BIN()." ". __FILE__." {$ligne["ID"]} >/dev/null 2>&1";
		
	}
	
	@file_put_contents("/etc/artica/backup.tasks",@implode("\n",$f));
	system("/etc/init.d/artica-postfix restart daemon");
	
}



function backup($ID){
	$GLOBALS["RESOURCE_MOUNTED"]=true;
	$sql="SELECT * FROM backup_schedules WHERE ID='$ID'";
	$mount_path="/opt/artica/mounts/backup/$ID";
	$q=new mysql();
	$unix=new unix();
	
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	if(!$q->ok){
		send_email_events("Backup Task $ID:: Mysql database error !","Aborting backup","backup");
		writelogs("[TASK $ID]: Mysql database error",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	
	if(!$GLOBALS["ONNLY_MOUNT"]){
		$pid=$ligne["pid"];	
		if($unix->process_exists($pid)){
			send_email_events("Backup Task $ID::  Already instance $pid running","Aborting backup","backup");
			writelogs("[TASK $ID]: Already instance $pid running",__FUNCTION__,__FILE__,__LINE__);
			return false;
		}
	}
	
	$sql="UPDATE backup_schedules set pid='".getmypid()."' WHERE ID='$ID'";
	$q->QUERY_SQL($sql,"artica_backup");
		
	$ressources=unserialize(base64_decode($ligne["datasbackup"]));
	if(count($ressources)==0){
		writelogs("[TASK $ID]: No source specified",__FUNCTION__,__FILE__,__LINE__);
		send_email_events("Backup Task $ID::  No source specified","Aborting backup","backup");
		return false;
	}
	
	if($ressources["OPTIONS"]["STOP_IMAP"]==1){$GLOBALS["NO_STOP_CYRUS"]=" --no-cyrus-stop";}
	
	$backup=new backup_protocols();
	$resource_type=$ligne["resource_type"];
	$pattern=$ligne["pattern"];
	$first_ressource=$backup->extractFirsRessource($ligne["pattern"]);
	$container=$ligne["container"];
	writelogs("[TASK $ID]: resource: $resource_type -> $first_ressource",__FUNCTION__,__FILE__,__LINE__);
	if($resource_type==null){
		writelogs("[TASK $ID]: No resource specified",__FUNCTION__,__FILE__,__LINE__);
		send_email_events("Backup Task $ID:: No resource specified !","Aborting backup","backup");
		return false;
	}
	
	
	
	
	if($resource_type=="smb"){
		
		if(!mount_smb($pattern,$ID,true)){
			writelogs("[TASK $ID]: resource: $first_ressource unable to mount",__FUNCTION__,__FILE__,__LINE__);
			send_email_events("Backup Task $ID::  resource: $first_ressource unable to mount","Aborting backup","backup");
			
			return false;
		}
	}
	
	
	if($resource_type=="usb"){
		if(!mount_usb($pattern,$ID,true)){
			writelogs("[TASK $ID]: resource: $first_ressource unable to mount",__FUNCTION__,__FILE__,__LINE__);
			send_email_events("Backup Task $ID::  resource: $first_ressource unable to mount","Aborting backup","backup");
			return false;
		}
	}	
	
	if($resource_type=="rsync"){
		$GLOBALS["RESOURCE_MOUNTED"]=false;
		$GLOBALS["USE_RSYNC"]=true;
		$GLOBALS["NO_UMOUNT"]=true;
		if(!mount_rsync($pattern,$ID,true)){
			writelogs("[TASK $ID]: resource: $first_ressource unable to connect",__FUNCTION__,__FILE__,__LINE__);
			send_email_events("Backup Task $ID::  resource: $first_ressource unable to connect","Aborting backup","backup");
			return false;
		}
		$mount_path=$pattern;
	}	
	
	
	
	if($GLOBALS["ONLY_TESTS"]){
		writelogs("[TASK $ID]:umount $mount_path",__FUNCTION__,__FILE__,__LINE__);
		if($GLOBALS["RESOURCE_MOUNTED"]){exec("umount -l $mount_path");}
		return;
	}
	
	if($GLOBALS["ONNLY_MOUNT"]){return $mount_path;}
	
	$users=new usersMenus();
	if($container=="daily"){
		writelogs("[TASK $ID]: Daily container",__FUNCTION__,__FILE__,__LINE__);
		$mount_path_final=$mount_path."/backup.".date('Y-m-d')."/$users->fqdn";
	}else{
		writelogs("[TASK $ID]: Weekly container",__FUNCTION__,__FILE__,__LINE__);
		$mount_path_final=$mount_path."/backup.".date('Y-W')."/$users->fqdn";
	}
	
if($GLOBALS["DEBUG"]){
		$cmd_verb=" --verbose";
		writelogs("[TASK $ID]: Verbose mode detected",__FUNCTION__,__FILE__,__LINE__);
	}

if(!$GLOBALS["NO_STANDARD_BACKUP"]){
	while (list ($num, $WhatToBackup) = each ($ressources) ){
		if($WhatToBackup=="all"){
			send_email_events("Backup Task $ID:: Backup starting Running macro all ","Backup is running","backup");
			writelogs("[TASK $ID]: Running macro all -> cyrus, mysql, LDAP, Artica...",__FUNCTION__,__FILE__,__LINE__);
			$cmd="/usr/share/artica-postfix/bin/artica-backup --rsync-cyrus \"$mount_path_final\"{$GLOBALS["NO_STOP_CYRUS"]}$cmd_verb";
			writelogs("[TASK $ID]: $cmd",__FUNCTION__,__FILE__,__LINE__);
			shell_exec($cmd);
			$cmd="/usr/share/artica-postfix/bin/artica-backup --rsync-ldap \"$mount_path_final\"$cmd_verb";
			writelogs("[TASK $ID]: $cmd",__FUNCTION__,__FILE__,__LINE__);
			shell_exec($cmd);			
			$cmd="/usr/share/artica-postfix/bin/artica-backup --rsync-mysql \"$mount_path_final\"$cmd_verb";
			writelogs("[TASK $ID]: $cmd",__FUNCTION__,__FILE__,__LINE__);
			shell_exec($cmd);			
			$cmd="/usr/share/artica-postfix/bin/artica-backup --rsync-artica \"$mount_path_final\"$cmd_verb";
			writelogs("[TASK $ID]: $cmd",__FUNCTION__,__FILE__,__LINE__);
			system($cmd);
			continue;				
		}
	}
}else{
	writelogs("[TASK $ID]: Skipping standard macros",__FUNCTION__,__FILE__,__LINE__);
}
	
	$sql="SELECT * FROM backup_folders WHERE taskid=$ID";
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		if($ligne["recursive"]==1){$recursive=" --recursive";}else{$recursive=null;}
		$path=trim(base64_decode($ligne["path"]));
		if(!is_dir($path)){continue;}
		send_email_events("Backup Task $ID:: Backup starting $path","Backup is running for path $path","backup");
		$cmd="/usr/share/artica-postfix/bin/artica-backup --rsync-folder \"$path\" \"$mount_path_final\"$recursive$cmd_verb";
		writelogs("[TASK $ID]: $cmd",__FUNCTION__,__FILE__,__LINE__);
		system($cmd);
	}
	
	if(!$GLOBALS["NO_UMOUNT"]){
		writelogs("[TASK $ID]:umount $mount_path",__FUNCTION__,__FILE__,__LINE__);
		exec("umount -l $mount_path");
	}
	send_email_events("Backup Task $ID:: Backup stopping","Backup is stopped","backup");
}


function mount_usb($pattern,$ID,$testwrite=true){
	$backup=new backup_protocols();
	$uuid=$backup->extractFirsRessource($pattern);
	if($uuid==null){
		writelogs("[TASK $ID]: usb protocol error $pattern",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	
	$usb=new usb($uuid);
	writelogs("[TASK $ID]: $uuid $usb->path $usb->ID_FS_TYPE",__FUNCTION__,__FILE__,__LINE__);	
	
	if($usb->ID_FS_TYPE==null){
		writelogs("[TASK $ID]: usb type error $pattern ",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	
	if($usb->path==null){
		writelogs("[TASK $ID]: usb dev error $pattern ",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}	
	
	$mount=new mount($GLOBALS["ADDLOG"]);
	$mount_path="/opt/artica/mounts/backup/$ID";
	
	if(!$mount->ismounted($mount_path)){
		writelogs("[TASK $ID]: local mount point $mount_path not mounted",__FUNCTION__,__FILE__,__LINE__);
		@mkdir($mount_path,null,true);
	}	
	
	if(!$mount->usb_mount($mount_path,$usb->ID_FS_TYPE,$usb->path)){
		writelogs("[TASK $ID]: unable to mount target server",__FUNCTION__,__FILE__,__LINE__);
		return false;	
		
	}
	
	if(!$testwrite){return true;}
	
	$md5=md5(date('Y-m-d H:i:s'));
	@file_put_contents("$mount_path/$md5","#");
	if(is_file("$mount_path/$md5")){
		@unlink("$mount_path/$md5");
		writelogs("[TASK $ID]: OK !",__FUNCTION__,__FILE__,__LINE__);
		if($GLOBALS["ONLY_TESTS"]){writelogs("<H2>{success}</H2>",__FUNCTION__,__FILE__,__LINE__);}
		return true;
	}else{
		writelogs("[TASK $ID]: failed to write $mount_path/$md5",__FUNCTION__,__FILE__,__LINE__);
		exec("umount -l $mount_path");
	}	
	
	
	
}



function mount_smb($pattern,$ID,$testwrite=true){
	$backup=new backup_protocols();
	$array=$backup->extract_smb_protocol($pattern);
	if(!is_array($array)){
		writelogs("[TASK $ID]: smb protocol error",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	
	$mount_path="/opt/artica/mounts/backup/$ID";
	writelogs("[TASK $ID]: local mount point $mount_path",__FUNCTION__,__FILE__,__LINE__);
	
	$mount=new mount($GLOBALS["ADDLOG"]);
	if(!$mount->ismounted($mount_path)){
		writelogs("[TASK $ID]: local mount point $mount_path not mounted",__FUNCTION__,__FILE__,__LINE__);
		@mkdir($mount_path,null,true);
	}

	
	if(!$mount->smb_mount($mount_path,$array["SERVER"],$array["USER"],$array["PASSWORD"],$array["FOLDER"])){
		writelogs("[TASK $ID]: unable to mount target server",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	
	if(!$testwrite){return true;}
	
	$md5=md5(date('Y-m-d H:i:s'));
	exec("/bin/touch $mount_path/$md5 2>&1",$results_touch);
	if(is_file("$mount_path/$md5")){
		@unlink("$mount_path/$md5");
		writelogs("[TASK $ID]: OK !",__FUNCTION__,__FILE__,__LINE__);
		if($GLOBALS["ONLY_TESTS"]){writelogs("<H2>{success}</H2>",__FUNCTION__,__FILE__,__LINE__);}
		return true;
	}else{
		$logs_touch=implode("<br>",$results_touch);
		writelogs("[TASK $ID]: failed to write $mount_path/$md5",__FUNCTION__,__FILE__,__LINE__);
		writelogs("[TASK $ID]: $logs_touch",__FUNCTION__,__FILE__,__LINE__);
		exec("umount -l $mount_path");
	}
}
function ParseMailboxDir($dir){
	$unix=new unix();
	$targetdir=$dir.$GLOBALS["DIRLIST"];
	@mkdir("/usr/share/artica-postfix/ressources/logs/cache");
	@chmod("/usr/share/artica-postfix/ressources/logs/cache",755);
	
	$cachefile="/usr/share/artica-postfix/ressources/logs/cache/".md5($GLOBALS["dirlist"].$targetdir)."list";
	if(is_file($cachefile)){
		if($unix->file_time_min($cachefile)<1441){
			echo @file_get_contents($cachefile);
			return;
		}
	}
	
	if($GLOBALS["dirlist"]){
		if($GLOBALS["USE_RSYNC"]){
			writelogs("Using rsync protocol $targetdir",__FUNCTION__,__FILE__,__LINE__);
			$ser=ParseMailboxDirRsync($targetdir);
			$ser=serialize($ser);
			//@file_put_contents($cachefile,$ser);
			echo $ser;
			return;			

		}
		writelogs("directory listing $targetdir",__FUNCTION__,__FILE__);
		exec("/usr/share/artica-postfix/bin/artica-install --dirlists $targetdir",$dirs);
		writelogs(count($dirs)." directories",__FUNCTION__,__FILE__);
		$ser=serialize($dirs);
		@file_put_contents($cachefile,$ser);
		echo $ser;
		return;
	}
	
	
	writelogs("parsing $targetdir",__FUNCTION__,__FILE__);
	if($GLOBALS["USE_RSYNC"]){
		writelogs("Using rsync protocol $dir",__FUNCTION__,__FILE__);
		$dirs=ParseMailboxDirRsync($targetdir);
		writelogs(count($dirs)." directories",__FUNCTION__,__FILE__);
		echo serialize($dirs);
		return;
	}
	
	
	$dirs=$unix->dirdir($targetdir);
	writelogs(count($dirs)." directories",__FUNCTION__,__FILE__);
	echo serialize($dirs);
	}
	
function restorembx($basedContent){
	$GLOBALS["ONNLY_MOUNT"]=true;
	$unix=new unix();
	$rsync=$unix->find_program("rsync");
	$chown=$unix->find_program("chown");
	$sudo=$unix->find_program("sudo");
	$reconstruct=$unix->LOCATE_CYRRECONSTRUCT();
	if(!is_file($rsync)){
		writelogs("Unable to stat rsync program",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	if(!is_file($reconstruct)){
		writelogs("Unable to stat reconstruct program",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
		
	
	$array=unserialize(base64_decode($basedContent));
	$id=$array["taskid"];
	writelogs("mounting $id",__FUNCTION__,__FILE__);
	$mounted_dir=backup($id);	
	if($mounted_dir==null){
		writelogs("cannot mount task id $id",__FUNCTION__,__FILE__);
		return ;
		}
		
		
		
	$path=$array["path"];
	$uid=$array["uid"];
	
	if(preg_match("#INBOX\/(.+)#",$array["mailbox"],$re)){
		$mailbox=$re[1];
		$cyrus=new cyrus();
		$cyrus->CreateSubDir($uid,$mailbox);
	}else{
		$mailbox=$array["mailbox"];
	}
	
	
	//$cyrus->CreateMailbox()
	
	
	$localimapdir=$unix->IMAPD_GET("partition-default");
	
	
	
	if(!is_dir($localimapdir)){writelogs("Unable to stat local partition-default",__FUNCTION__,__FILE__,__LINE__);return;}
	$userfs=str_replace(".","^",$uid);
	$firstletter=substr($userfs,0,1);
	$localuserfs="$localimapdir/$firstletter/user/$userfs";
	$localimapdir="$localimapdir/$firstletter/user/$userfs/";
	if(!is_dir($localimapdir)){writelogs("Unable to stat local \"$localimapdir\"",__FUNCTION__,__FILE__,__LINE__);return;}
	
	
	
	$remoteimapdir="$mounted_dir/$path/$mailbox";
	@mkdir($localimapdir,null,true);
	
	if(substr($remoteimapdir,strlen($remoteimapdir)-1,1)<>"/"){$remoteimapdir=$remoteimapdir."/";}
	
	
	 $cmd="$rsync -z --stats $remoteimapdir* $localimapdir 2>&1";
	if($GLOBALS["USE_RSYNC"]){
		$backup=new backup_protocols();
		writelogs("Using rsync protocol",__FUNCTION__,__FILE__,__LINE__);
		$array_config=$backup->extract_rsync_protocol($remoteimapdir);
		if(!is_array($array)){
			writelogs("[TASK $ID]: rsync protocol error",__FUNCTION__,__FILE__,__LINE__);
			return false;
		}	
		
		if($array_config["PASSWORD"]<>null){
			$tmpstr="/opt/artica/passwords/".md5($array_config["PASSWORD"]);
			@mkdir("/opt/artica/passwords",null,true);
			@file_put_contents($tmpstr,$array_config["PASSWORD"]);
			$pwd=" --password-file=$tmpstr";
		}
	
		if($array["USER"]<>null){
			$user="{$array["USER"]}@";
		}
		
		$cmd="$rsync$pwd --stats rsync://$user{$array_config["SERVER"]}/{$array_config["FOLDER"]}*  $localimapdir 2>&1";
		
		
	}
	
	
	writelogs("Restore from $remoteimapdir",__FUNCTION__,__FILE__,__LINE__);
	writelogs("Restore to $localimapdir",__FUNCTION__,__FILE__,__LINE__);
	writelogs("reconstruct path $reconstruct",__FUNCTION__,__FILE__,__LINE__);
	writelogs("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$rsynclogs);
	
	$i=0;
	while (list ($num, $line) = each ($rsynclogs)){
		if(preg_match("#Number of files transferred:\s+([0-9]+)#",$line,$re)){$GLOBALS["events"][]="Files restored: {$re[1]}";}
		if(preg_match("#Total transferred file size:\s+([0-9]+)#",$line,$re)){$bytes=$re[1];$re[1]=round(($re[1]/1024)/1000)."M";$GLOBALS["events"][]="{$re[1]} size restored ($bytes bytes)";}		
		if(preg_match("#Permission denied#",$line)){$i=$i+1;}
		
	}
	$GLOBALS["events"][]="$i file(s) on error";
	shell_exec("$chown -R cyrus:mail $localuserfs");
	shell_exec("/bin/chmod -R 755 $localuserfs");
	
	$cmd="$sudo -u cyrus $reconstruct -r -f user/$uid 2>&1";
	writelogs("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$rsynclogs);
	$GLOBALS["events"][]="Reconstruct information: ";
	while (list ($num, $line) = each ($rsynclogs)){
		$GLOBALS["events"][]="reconstructed path: $line";
	}	
	
	writelogs("restarting imap service",__FUNCTION__,__FILE__,__LINE__);
	system("/etc/init.d/artica-postfix restart imap");
	print_r($GLOBALS["events"]);
	
}

function mount_rsync($pattern,$ID,$testwrite=true){
	$backup=new backup_protocols();
	$unix=new unix();
	$rsync=$unix->find_program("rsync");
	
	if(!is_file($rsync)){
		writelogs("[TASK $ID]: unable to stat rsync",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	
	$array=$backup->extract_rsync_protocol($pattern);
	if(!is_array($array)){
		writelogs("[TASK $ID]: rsync protocol error",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}	
	
	if($array["PASSWORD"]<>null){
		$tmpstr=$unix->FILE_TEMP();
		@file_put_contents($tmpstr,$array["PASSWORD"]);
		$pwd=" --password-file=$tmpstr";
	}
	
	if($array["USER"]<>null){
		$user="{$array["USER"]}@";
	}
	
	$pattern_list="$rsync --list-only$pwd rsync://$user{$array["SERVER"]}/{$array["FOLDER"]} --stats --dry-run 2>&1";
	exec($pattern_list,$results);
	if(is_file($tmpstr)){@unlink($tmpstr);}
	
	while (list ($num, $line) = each ($results)){
		if(preg_match("#\@ERROR#",$line)){
			writelogs("[TASK $ID]: failed to connect rsync://$user{$array["SERVER"]}/{$array["FOLDER"]}",__FUNCTION__,__FILE__,__LINE__);
		}
		if(preg_match("#Number of files#",$line)){return true;}
	}		
}

function ParseMailboxDirRsync($pattern){
	$backup=new backup_protocols();
	$unix=new unix();
	$rsync=$unix->find_program("rsync");

	$array=$backup->extract_rsync_protocol($pattern);
	if(!is_array($array)){
		writelogs("rsync protocol error",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}	
	
	if($array["PASSWORD"]<>null){
		$tmpstr=$unix->FILE_TEMP();
		@file_put_contents($tmpstr,$array["PASSWORD"]);
		$pwd=" --password-file=$tmpstr";
	}
	
	if($array["USER"]<>null){
		$user="{$array["USER"]}@";
	}	
	
	$pattern_list="$rsync --list-only$pwd rsync://$user{$array["SERVER"]}/{$array["FOLDER"]}/ --stats --dry-run 2>&1";
	writelogs("$pattern_list",__FUNCTION__,__FILE__,__LINE__);
	exec($pattern_list,$results);
	@unlink($tmpstr);
	unset($array);
	
	while (list ($num, $line) = each ($results)){
		
		if(preg_match("#^d[rwx\-]+\s+[0-9]+\s+[0-9\/]+\s+[0-9\:]+\s+(.+)#",$line,$re)){
			writelogs($re[1],__FUNCTION__,__FILE__,__LINE__);
			if(trim($re[1])=='.'){continue;}
			$array[trim($re[1])]=trim($re[1]);
			continue;
		}
	}
	
	return $array;
	
}



?>