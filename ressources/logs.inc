<?php
include_once(dirname(__FILE__).'/class.syslog.inc');
function WriteToSyslogMail($text,$file,$error=false){
	$file=basename($file);
	if(!$error){$LOG_SEV=LOG_INFO;}else{$LOG_SEV=LOG_ERR;}
	openlog($file, LOG_PID , LOG_MAIL);
	syslog($LOG_SEV, $text);
	closelog();
	
	
	}


function writeToFile($text,$filepath){
	$f = @fopen($filepath, 'w');
	@fwrite($f,$text);
	@fclose($f);
}
function writelogs($text=null,$function=null,$file_source=null,$line=null){
if($file_source==null){$file_source= __FILE__ ;}
if(posix_getuid()==0){$prefix="[DAEMON]";}else{$prefix="[CONSOLE]::{$_SESSION["uid"]}";}
if(function_exists("getmypid")){$pid=getmypid();}

$p=dirname(__FILE__);
$p=str_replace("/ressources","",$p);
$file_source=str_replace("$p/","",$file_source);

if($line<>null){$line=" line $line";}
$file_source=str_replace("/usr/share/artica-postfix/","",$file_source);
$file_source=str_replace("/home/dtouzeau/developpement/artica-postfix/","",$file_source);
if($GLOBALS["VERBOSE"]){echo "[$pid] $prefix::$function:: $text in $file_source$line\n";}
$text2=date('H:i:s')." $function:: $text $line";
$text="[$pid] $prefix::$function:: $text in $file_source$line";
writeOtherlogs(dirname(__FILE__)."/logs/web/interface.debug",$text2);
if($GLOBALS["ADDLOG"]<>null){writeOtherlogs($GLOBALS["ADDLOG"],$text2);}
error_log("$text");
if(basename($file_source)=="exec.backup.php"){writeOtherlogs("/var/log/artica-postfix/exec.backup.".date("Y-m-d").".log",$text);}
$arr=explode("/",$function);
if($arr[1]<>null){$class=$arr[1];}
writeClasslogs($text,$function,$class);

}function writeOtherlogs($file,$text=null){
	@mkdir(dirname($file));
	$logFile=$file;
	if(!is_dir(dirname($logFile))){mkdir(dirname($logFile));}
   	if (is_file($logFile)) { 
   		$size=filesize($logFile);
   		if($size>1000000){unlink($logFile);}
   	}
	$logFile=str_replace("//","/",$logFile);
	$f = @fopen($logFile, 'a');
	@fwrite($f, "$text\n");
	@fclose($f);
}function writeClasslogs($text=null,$function=null,$class=null){
if($class==null){return null;}
	
	
			@mkdir('/usr/share/artica-postfix/ressources/logs/web');
		    $logFile="/usr/share/artica-postfix/ressources/logs/web/class.$class.log";
		    if(!is_dir(dirname($logFile))){mkdir(dirname($logFile));}
   		if (is_file($logFile)) { 
   			$size=filesize($logFile);
		    	if($size>1000000){unlink($logFile);}
   		}
		 $logFile=str_replace("//","/",$logFile);
		$f = @fopen($logFile, 'a');
		$date=date("Y-m-d H:i:s");
		@fwrite($f, "$date: [$function()]:: $text\n");
		@fclose($f);
}function writepostfixlogs($text=null,$function=null,$file_source=null){
	
if($file_source==null){$file_source= __FILE__ ;}
		$file_source=basename($file_source);
			@mkdir('/usr/share/artica-postfix/ressources/logs/web');
		    $logFile="/usr/share/artica-postfix/ressources/logs/web/interface-postfix.log";
		    if(!is_dir(dirname($logFile))){mkdir(dirname($logFile));}
   		if (is_file($logFile)) { 
   			$size=filesize($logFile);
		    	if($size>1000000){unlink($logFile);}
   		}
		 $logFile=str_replace("//","/",$logFile);
		$f = @fopen($logFile, 'a');
		$date=date("Y-m-d H:i:s");
		$text="$date:($file_source) [$function()][{$_SERVER['REMOTE_ADDR']}]:: $text\n";
		if($GLOBALS["DEBUG"]){echo $text;}
		@fwrite($f,$text );
		@fclose($f);}function writesquidlogs($text=null,$function=null,$file_source=null){
	
if($file_source==null){$file_source= __FILE__ ;}
		$file_source=basename($file_source);
			@mkdir('/usr/share/artica-postfix/ressources/logs/web');
		    $logFile="/usr/share/artica-postfix/ressources/logs/web/interface-squid.log";
		    if(!is_dir(dirname($logFile))){mkdir(dirname($logFile));}
   		if (is_file($logFile)) { 
   			$size=filesize($logFile);
		    	if($size>1000000){unlink($logFile);}
   		}
		 $logFile=str_replace("//","/",$logFile);
		$f = @fopen($logFile, 'a');
		$date=date("Y-m-d H:i:s");
		@fwrite($f, "$date:($file_source) [$function()][{$_SERVER['REMOTE_ADDR']}]:: $text\n");
		@fclose($f);}function write_syslog($text,$file){$file=basename($file);if(!function_exists('syslog')){return null;}openlog($file, LOG_PID | LOG_PERROR, LOG_LOCAL0);syslog(LOG_INFO, $text);closelog();}
		
?>
