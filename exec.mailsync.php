<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');


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
	if($ligne["ssl"]==1){$ssl="	--ssl1 ";}
	
	$file_temp="/usr/share/artica-postfix/ressources/logs/imapsync.$id.logs";
	$cmd=$unix->find_program("imapsync")." --buffersize 8192000 --nosyncacls --subscribe --syncinternaldate ";
	$cmd=$cmd." --host1 {$ligne["imap_server"]} --user1 {$ligne["username"]} --password1 {$ligne["password"]}$ssl --host2 127.0.0.1 --user2 $ct->uid";
	$cmd=$cmd." --password2 $ct->password --noauthmd5 --allowsizemismatch >$file_temp 2>&1";
	
	
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