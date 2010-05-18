<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}

if($argv[1]=='--clean-tmp'){CleanLogs();die();}

if(systemMaxOverloaded()){
	writelogs("This system is too many overloaded, die()",__FUNCTION__,__FILE__,__LINE__);
	die();
}

function init(){
	$sock=new sockets();
	$ArticaMaxLogsSize=$sock->GET_PERFS("ArticaMaxLogsSize");
	if($ArticaMaxLogsSize<1){$ArticaMaxLogsSize=500;}
	$ArticaMaxLogsSize=$ArticaMaxLogsSize*1000;	
	$GLOBALS["ArticaMaxLogsSize"]=$ArticaMaxLogsSize;
	$GLOBALS["logs_cleaning"]=$sock->GET_NOTIFS("logs_cleaning");
	$GLOBALS["MaxTempLogFilesDay"]=$sock->GET_INFO("MaxTempLogFilesDay");
	if($GLOBALS["MaxTempLogFilesDay"]==null){$GLOBALS["MaxTempLogFilesDay"]=5;}
	
	
}

function CleanLogs(){
	cleanTmplogs();
	CleanDirLogs('/var/log');
	CleanDirLogs('/opt/artica/tmp');
	CleanDirLogs('/etc/artica-postfix/pids');
	CleanDirLogs('/opt/artica/install');
	phplogs();
	CleanClamav();
	
	
	$size=str_replace("&nbsp;"," ",FormatBytes($GLOBALS["DELETED_SIZE"]));
	echo "$size cleaned :  {$GLOBALS["DELETED_FILES"]} files\n";
	if($GLOBALS["DELETED_SIZE"]>500){
		send_email_events("$size logs files cleaned","{$GLOBALS["DELETED_FILES"]} files cleaned for $size free disk space","logs_cleaning");
	}
	
	
}

function cleanTmplogs(){
$badfiles["100k"]=true;
$badfiles["2"]=true;
$badfiles["size"]=true;
$badfiles["versions"]=true;
$badfiles["3"]=true;
$badfiles["named_dump.db"]=true;
$badfiles["named.stats"]=true;
$badfiles["log-queries.info"]=true;
$badfiles["log-named-auth.info"]=true;
$badfiles["log-lame.info"]=true;
$badfiles["bind.pid"]=true;
$badfiles["ipp.txt"]=true;
$badfiles["debug"]=true;
$badfiles["log-update-debug.log"]=true;
$badfiles["ldap.ppu"]=true;
$badfiles["#"]=true;	

	while (list ($num, $ligne) = each ($badfiles) ){
		if($num==null){continue;}
		if(is_file("/usr/share/artica-postfix/$num")){@unlink("/usr/share/artica-postfix/$num");}
	}
	
	$unix=new unix();
	foreach (glob("/tmp/artica*.tmp") as $filename) {
    	$time=$unix->file_time_min($filename);
    	if($time>2){
    		$size=@filesize($filename)/1024;
    		$GLOBALS["DELETED_SIZE"]=$GLOBALS["DELETED_SIZE"]+$size;
    		$GLOBALS["DELETED_FILES"]=$GLOBALS["DELETED_FILES"]+1;
    		if($GLOBALS["VERBOSE"]){echo "Delete $filename\n";}
    		@unlink($filename);
    	}else{
    	if($GLOBALS["VERBOSE"]){echo "$filename TTL:$time \n";}
    	}
	}
	
	
if($GLOBALS["VERBOSE"]){echo "/tmp/process1*.tmp\n";}
	foreach (glob("/tmp/process1*.tmp") as $filename) {
    	$time=$unix->file_time_min($filename);
    	if($time>2){
    		$size=@filesize($filename)/1024;
    		$GLOBALS["DELETED_SIZE"]=$GLOBALS["DELETED_SIZE"]+$size; 
    		$GLOBALS["DELETED_FILES"]=$GLOBALS["DELETED_FILES"]+1;   
    		if($GLOBALS["VERBOSE"]){echo "Delete $filename\n";}		
    		@unlink($filename);
    	}else{
    		if($GLOBALS["VERBOSE"]){echo "$filename TTL:$time \n";}
    	}
	}
	
}


function phplogs(){
	$filename="/usr/share/artica-postfix/ressources/logs/php.log";
	$size=@filesize($filename)/1024;
	if($GLOBALS["VERBOSE"]){echo "php.log size:{$size}Ko \n";}
	if($size>50681){
		$GLOBALS["DELETED_FILES"]=$GLOBALS["DELETED_FILES"]+1; 
		$GLOBALS["DELETED_SIZE"]=$size;
		@unlink($filename);
	}
}


function CleanClamav(){
	$unix=new unix();
	
	foreach (glob("/var/lib/clamav/clamav-*") as $filename) {
		$time=$unix->file_time_min($filename);
		if($time>60){
			if(is_dir($filename)){
				$size=dirsize($filename)/1024;
				$GLOBALS["DELETED_FILES"]=$GLOBALS["DELETED_FILES"]+1; 
				$GLOBALS["DELETED_SIZE"]=$size;
				shell_exec($unix->find_program("rm")." -rf $filename");
				if($GLOBALS["VERBOSE"]){echo "Delete directory $filename ($size Ko) TTL:$time\n";}
				continue;
				
			}
			$GLOBALS["DELETED_FILES"]=$GLOBALS["DELETED_FILES"]+1; 
			$GLOBALS["DELETED_SIZE"]=$size;
			if($GLOBALS["VERBOSE"]){echo "Delete $filename ($size Ko)\n";}		
			unlink($filename);
		}
		
	}
}

function dirsize($path){
	$unix=new unix();
	
	exec($unix->find_program("du")." -b $path",$results);
	$tt=implode("",$results);
	if(preg_match("#([0-9]+)\s+#",$tt,$re)){return $re[1];}
	
}


function CleanDirLogs($path){
	$BigSize=false;
	if($path=='/var/log'){$BigSize=true;}
if($GLOBALS["ArticaMaxLogsSize"]<100){
   $BigSize=false;
   writelogs('Bad parameter ArticaMaxLogsSize is less than 100mb, skip it for security reason..',__FUNCTION__,__FILE__,__LINE__);
}
$maxday=($GLOBALS["MaxTempLogFilesDay"]*24)*60; 


$unix=new unix();
$files=$unix->DirRecursiveFiles($path);
if($path==null){return;}
	while (list ($num, $filepath) = each ($files) ){
		if($ligne==null){continue;}
		if(is_link($filepath)){continue;}
		if(is_dir($filepath)){continue;}
		$size=@filesize($filepath)/1024;
		$time=$unix->file_time_min($filepath);
		if($GLOBALS["VERBOSE"]){echo "$filepath $size Ko, $time TTL\n";}
		if($size>$GLOBALS["ArticaMaxLogsSize"]){
			$GLOBALS["DELETED_SIZE"]=$GLOBALS["DELETED_SIZE"]+$size;
			$GLOBALS["DELETED_FILES"]=$GLOBALS["DELETED_FILES"]+1;
			@unlink($filepath);
			continue;
		}
		
		if($time>$maxday){
			$GLOBALS["DELETED_SIZE"]=$GLOBALS["DELETED_SIZE"]+$size;
			$GLOBALS["DELETED_FILES"]=$GLOBALS["DELETED_FILES"]+1;
			@unlink($filepath);
			continue;
		}
		
		
	}
	
}






//############################################################################## 
?>