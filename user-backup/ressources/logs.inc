<?php

function writeToFile($text,$filepath){
	$f = @fopen($filepath, 'w');
	@fwrite($f,$text);
	@fclose($f);
}
function writelogs($text=null,$function=null,$file_source=null,$line=null){
if($file_source==null){$file_source= __FILE__ ;}
if(posix_getuid()==0){$prefix="[DAEMON]";}else{$prefix="[USER-CONSOLE]";}
if($line<>null){$line=" line $line";}
$file_source=str_replace("/usr/share/artica-postfix","",$file_source);
error_log("$prefix::$function:: $text in $file_source$line");

$arr=explode("/",$function);
if($arr[1]<>null){$class=$arr[1];}
writeClasslogs($text,$function,$class);

}function writeClasslogs($text=null,$function=null,$class=null){
if($class==null){return null;}
	
	
			@mkdir('/usr/share/artica-postfix/ressources/logs/web');
		    $logFile="/usr/share/artica-postfix/ressources/logs/web/$class.log";
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
		@fwrite($f, "$date:($file_source) [$function()][{$_SERVER['REMOTE_ADDR']}]:: $text\n");
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
