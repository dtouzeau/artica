<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__) .'/ressources/class.os.system.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(!Build_pid_func(__FILE__,"MAIN")){
	events(basename(__FILE__).": Already executed.. aborting the process");
	die();
}
if(systemMaxOverloaded()){
	writelogs("This system is too many overloaded, die()",__FUNCTION__,__FILE__,__LINE__);
	die();
}

$GLOBALS["OVERLOAD"]=system_is_overloaded();

ParseLocalQueue();
die();

function ParseLocalQueue(){
	
if(is_file("/etc/artica-postfix/orders.queue")){
		$size=@filesize("/etc/artica-postfix/orders.queue");
		if($size>0){
			events("Loading /etc/artica-postfix/orders.queue $size bytes");
			$orders_queue=explode("\n",@file_get_contents("/etc/artica-postfix/orders.queue"));
			if(is_array($orders_queue)){
				while (list ($num, $ligne) = each ($orders_queue) ){
					if(trim($ligne)==null){continue;}
					$orders[md5($ligne)]=$ligne;
				}	
			}
		}
		@unlink("/etc/artica-postfix/orders.queue");	
}

if(is_file("/etc/artica-postfix/background")){
		$size=@filesize("/etc/artica-postfix/background");
		if($size>0){
			events("Loading /etc/artica-postfix/background $size bytes");
			$background=explode("\n",@file_get_contents("/etc/artica-postfix/background"));
			if(is_array($background)){
				while (list ($num, $ligne) = each ($background) ){
					if(trim($ligne)==null){continue;}
					$orders[md5($ligne)]=$ligne;
				}
			}
		}
		@unlink("/etc/artica-postfix/background");
		
}

if(count($orders)==0){return null;}
	$nice=EXEC_NICE();
	shell_exec('export PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/bin/X11');
	$nohup=LOCATE_NOHUP()." ";
	$orders_number=count($orders);
	$count_max=$orders_number;
	if($count_max>2){$count_max=2;}
	
	
	while (list ($num, $cmd) = each ($orders) ){
		$count=$count+1;
		$devnull=" >/dev/null 2>&1";
		if(strpos($cmd,">")>0){$devnull=null;}

		if($GLOBALS["OVERLOAD"]){
			if($count>=$count_max){break;}
			unset($orders[$num]);
			events("[OVERLOAD]:: running in overload mode $cmd");
			shell_exec("$nohup$nice$cmd$devnull");
			continue;
		}
		events("[NORMAL]:: running in normal mode $cmd");
		shell_exec("$nohup$nice$cmd$devnull &");
		unset($orders[$num]);
		if($count>=$count_max){break;}
	}
	
	
	events("$count/$orders_number order(s) executed...end;");
	if(is_array($orders)){
		if(count($orders)>0){
			reset($orders);
			$fh = fopen("/etc/artica-postfix/background", 'w') or die("can't open file");
			while (list ($num, $cmd) = each ($orders) ){
				$datas="$cmd\n";
				fwrite($fh, $datas);
				}
			fclose($fh);
			events("Queued ". count($orders)." order(s)");
		}
	}

}



function events($text){
	$l=new debuglogs();
	$i=basename(__FILE__);
	$l->events("[$i] $text","/var/log/artica-postfix/artica-orders.debug");
}


?>
