<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if($argv[1]=="--sync"){sync($argv[2]);exit;}
if($argv[1]=="--cron"){cron();exit;}
if($argv[1]=="--stop"){cron($argv[2]);exit;}




die();

function stop($id){
$unix=new unix();	
$sql="SELECT * FROM imapsync WHERE ID='$id'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if(!$q->ok){
		write_syslog("Mysql error $q->mysql_error",__FILE__);
		die();
	}
	$pid_org=$ligne["pid"];
	$ligne["imap_server"]=str_replace(".","\.",$ligne["imap_server"]);
	$ligne["username"]=str_replace(".","\.",$ligne["username"]);	
	exec($unix->find_program("pgrep")." -f \"imapsync.+?--host1 {$ligne["imap_server"]}.+?--user1 {$ligne["username"]}\"",$pids);

	while (list ($index, $pid) = each ($pids) ){
		if($pid>5){shell_exec("/bin/kill -9 $pid");}
	}
	
	shell_exec("/bin/kill -9 $pid_org");
}

function sync($id){
	$unix=new unix();
	$users=new usersMenus();
	$GLOBALS["unique_id"]=$id;
	$sql="SELECT * FROM imapsync WHERE ID='$id'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if(!$q->ok){
		write_syslog("Mysql error $q->mysql_error",__FILE__);
		die();
	}
	$pid=$ligne["pid"];
	if($unix->process_exists($pid)){die();}
	update_pid(getmypid());
	if(!is_file($unix->find_program("imapsync"))){
		update_status(-1,"Could not find imapsync program");
		return;
	}
	update_status(1,"Executed");
	$ct=new user($ligne["uid"]);
	
	$parameters=unserialize(base64_decode($ligne["parameters"]));
	$array_folders=unserialize(base64_decode($ligne["folders"]));
	if($user->cyrus_imapd_installed){$local_mailbox=true;}
	if($user->ZARAFA_INSTALLED){$local_mailbox=true;}
	
	if($ligne["ssl"]==1){$ssl="	--ssl1";}
	if($local_mailbox){
		if($parameters["local_mailbox"]==null){$parameters["local_mailbox"]=1;}
	}else{
		$parameters["local_mailbox"]=0;
	}
	if(!$local_mailbox){if($parameters["remote_imap_server"]==null){update_status(-1,"unable to get destination imap server");return;}}
	if($parameters["local_mailbox"]==0){if($parameters["remote_imap_server"]==null){update_status(-1,"unable to get destination imap server");return;}}
	
	$host2="127.0.0.1";
	$user2=$ct->uid;
	$password2=$ct->password;
	if($parameters["local_mailbox"]==0){
		$host2=$parameters["remote_imap_server"];
		$user2=$parameters["dest_imap_username"];
		$password2=$parameters["dest_imap_password"];
		if($parameters["dest_use_ssl"]==1){$ssl2=" --ssl2";}
	}
	
	if($parameters["syncinternaldates"]==null){$parameters["syncinternaldates"]=1;}
	if($parameters["noauthmd5"]==null){$parameters["noauthmd5"]=1;}
	if($parameters["allowsizemismatch"]==null){$parameters["allowsizemismatch"]=1;}
	if($parameters["nosyncacls"]==null){$parameters["nosyncacls"]=1;}
	if($parameters["skipsize"]==null){$parameters["skipsize"]=0;}
	if($parameters["nofoldersizes"]==null){$parameters["nofoldersizes"]=1;}
	
	
	
	if(count($array_folders["FOLDERS"])>0){
		while (list($num,$folder)=each($array_folders["FOLDERS"])){
			if(trim($folder)==null){continue;}
			$cleaned[trim($folder)]=trim($folder);
		}
		while (list($num,$folder)=each($cleaned)){$foldersr[]=$folder;}
		$folders_replicate=@implode(" --folder ",$foldersr);
	}
	$parameters["sep"]=trim($parameters["sep"]);
	$parameters["sep2"]=trim($parameters["sep2"]);
	if($parameters["delete_messages"]==1){$delete=" --delete --expunge1";}
	if($parameters["syncinternaldates"]==1){$syncinternaldates=" --syncinternaldate";}
	if($parameters["noauthmd5"]==1){$noauthmd5=" --noauthmd5";}
	if($parameters["allowsizemismatch"]==1){$allowsizemismatch=" --allowsizemismatch";}
	if($parameters["nosyncacls"]==1){$nosyncacls=" --nosyncacls";}
	if($parameters["skipsize"]==1){$skipsize=" --skipsize";}
	if($parameters["nofoldersizes"]==1){$nofoldersizes=" --nofoldersizes";}
	if(trim($parameters["sep"])<>null){$sep=" --sep {$parameters["sep"]}";}
	if(trim($parameters["sep2"])<>null){$sep2=" --sep2 {$parameters["sep2"]}";}
	
	
	
	$file_temp="/usr/share/artica-postfix/ressources/logs/imapsync.$id.logs";
	$cmd=$unix->find_program("imapsync")." --buffersize 8192000$nosyncacls --subscribe$syncinternaldates";
	$cmd=$cmd." --host1 {$ligne["imap_server"]} --user1 {$ligne["username"]} --password1 {$ligne["password"]}$ssl$sep --host2 $host2 --user2 $user2";
	$cmd=$cmd." --password2 $password2$ssl2$sep2$folders_replicate$delete$noauthmd5$allowsizemismatch$skipsize$nofoldersizes >$file_temp 2>&1";
	
	if($GLOBALS["VERBOSE"]){
		echo "$cmd\n";
		return;
	}
	
	shell_exec($cmd);
	update_status(0,addslashes(@file_get_contents($file_temp)));
	
	
}


function update_pid($pid){
	$q=new mysql();
	$date=date('Y-m-d H:i:s');
	$sql="UPDATE imapsync SET pid='$pid',zDate='$date' WHERE ID={$GLOBALS["unique_id"]}";
	$q->QUERY_SQL($sql,"artica_backup");
}
function update_status($int,$text){
	$q=new mysql();
	$date=date('Y-m-d H:i:s');
	$sql="UPDATE imapsync SET state='$int',state_event='$text',zDate='$date' WHERE ID={$GLOBALS["unique_id"]}";
	$q->QUERY_SQL($sql,"artica_backup");
}


function cron(){
	$unix=new unix();
	$files=$unix->DirFiles("/etc/cron.d");
	
	$sql="SELECT CronSchedule,ID FROM imapsync";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){return null;}
	
	
	while (list ($index, $line) = each ($files) ){
		if($index==null){continue;}
		if(preg_match("#^imapsync-#",$index)){
			@unlink("/etc/cron.d/$index");
		}
	}
	
	$sql="SELECT CronSchedule,ID FROM imapsync";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
 	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
 		if(trim($ligne["CronSchedule"]==null)){continue;}
 		$f[]="PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/X11R6/bin:/usr/share/artica-postfix/bin";
		$f[]="MAILTO=\"\"";
		$f[]="{$ligne["CronSchedule"]}  root ".__FILE__." --sync {$ligne["ID"]}";
		$f[]="";
		@file_put_contents("/etc/cron.d/imapsync-{$ligne["ID"]}",implode("\n",$f));
		@chmod("/etc/cron.d/imapsync-{$ligne["ID"]}",600);
		unset($f);
 	}
	
}





?>