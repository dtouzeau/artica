<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.status.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/ressources/class.artica.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
//server-syncronize-64.png

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}


if(!Build_pid_func(__FILE__,"MAIN")){
	Myevents(basename(__FILE__).":Already executed.. aborting the process");
	die();
}

if($argv[1]=="--loadavg"){loadavg();exit;}
if($argv[1]=="--mem"){loadmem();exit;}
if($argv[1]=="--cpu"){loadcpu();exit;}



checkProcess1();

function checkProcess1(){
	
	$unix=new unix();
	$pid=$unix->PIDOF_PATTERN("bin/process1");
	if($pid<5){return null;}
	$process1=$unix->PROCCESS_TIME_MIN($pid);
	$mem=$unix->PROCESS_MEMORY($pid);
	Myevents("process1: $pid ($process1 mn) memory:$mem Mb",__FUNCTION__);
	
	if($mem>30){
		@copy("/var/log/artica-postfix/process1.debug","/var/log/artica-postfix/process1.killed".time().".debug");
		system("/bin/kill -9 $pid");
		email_events(
		"artica process1 (process1) Killed",
		"Process1 use too much memory $mem MB","watchdog"); 		
	}
	
	if($process1>2){
		@copy("/var/log/artica-postfix/process1.debug","/var/log/artica-postfix/process1.killed".time().".debug");
		system("/bin/kill -9 $pid");
		email_events(
		"artica process1 (process1) Killed",
		"Process1 run since $process1 Pid: $pid and exceed 2 minutes live","watchdog"); 
	}

}

function Myevents($text=null,$function=null){
			$pid=getmygid();
			$file="/var/log/artica-postfix/watchdog.debug";
			@mkdir(dirname($file));
		    $logFile=$file;
		 
   		if (is_file($logFile)) { 
   			$size=filesize($logFile);
		    	if($size>1000000){unlink($logFile);}
   		}
		$date=date('Y-m-d H:i:s'). " [$pid]: ";
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$date $function:: $text\n");
		@fclose($f);
}


function loadcpu(){
	$timefile="/etc/artica-postfix/croned.1/".basename(__FILE__).__FUNCTION__;
	if(file_time_min($timefile)<5){return null;}
	@unlink($timefile);
	@file_put_contents($timefile,"#");	
	$datas=loadavg_table();
	if($GLOBALS["VERBOSE"]){echo strlen($datas)." bytes body text\n";}
	send_email_events("System CPU exceed rule",$datas,"system");
	checkProcess1();
}
function loadmem(){
	$timefile="/etc/artica-postfix/croned.1/".basename(__FILE__).__FUNCTION__;
	if(file_time_min($timefile)<5){return null;}
	@unlink($timefile);
	@file_put_contents($timefile,"#");	
	$sys=new os_system();
	$mem=$sys->realMemory();
	
	$pourc=$mem["ram"]["percent"];
	$ram_used=$mem["ram"]["used"];
	$ram_total=$mem["ram"]["total"];	
	
	$datas=loadavg_table();
	if($GLOBALS["VERBOSE"]){echo strlen($datas)." bytes body text\n";}	
	send_email_events("System Memory $pourc% used exceed rule",$datas,"system");
	checkProcess1();
}

function loadavg(){
	$timefile="/etc/artica-postfix/croned.1/".basename(__FILE__).__FUNCTION__;
	if(file_time_min($timefile)<5){return null;}
	@unlink($timefile);
	@file_put_contents($timefile,"#");
	
	
	$array_load=sys_getloadavg();
	$internal_load=$array_load[0];		
	$datas=loadavg_table();
	if($GLOBALS["VERBOSE"]){echo strlen($datas)." bytes body text\n";}	
	send_email_events("System Load - $internal_load - exceed rule ",$datas,"system");
	checkProcess1();
}

function loadavg_table(){
	$unix=new unix();
	$ps=$unix->find_program("ps");
	exec("$ps -aux",$results);
	while (list ($index, $line) = each ($results) ){
	if(!preg_match("#(.+?)\s+([0-9]+)\s+([0-9\.]+)\s+([0-9\.]+)\s+([0-9]+)\s+([0-9\.]+)\s+.+?\s+.+?\s+([0-9\:]+)\s+([0-9\:]+)\s+(.+?)$#",$line,$re)){continue;}	
	$user=$re[1];
	$pid=$re[2];
	$pourcCPU=$re[3];
	$purcMEM=$re[4];
	$VSZ=$re[5];
	$RSS=$re[6];
	$START=$re[7];
	$TIME=$re[8];
	$cmd=$re[9];
	
	$pourcCPU=str_replace("0.0","0",$pourcCPU);
	$purcMEM=str_replace("0.0","0",$purcMEM);
	
	$key="$pourcCPU$purcMEM";
	$key=str_replace(".",'',$key);
	
	$array[$key][]=array(
			"PID"=>$pid,
			"CPU"=>$pourcCPU,
			"MEM"=>$purcMEM,
			"START"=>$START,
			"TIME"=>$TIME,
			"CMD"=>$cmd
		);
	
	
	
	
		
	}
	
	krsort($array);
	$htm[]="<html><head></head><body>";
	$htm[]="<table style='width:100%'>";
	$htm[]="<tr>";
	$htm[]="<th>PID</th>";
	$htm[]="<th>CPU</th>";
	$htm[]="<th>MEM</th>";
	$htm[]="<th>START</th>";
	$htm[]="<th>TIME</th>";
	$htm[]="<th>CMD</th>";
	$htm[]="</tr>";
	while (list ($index, $line) = each ($array) ){
	
		while (list ($a, $barray) = each ($line) ){
			$htm[]="<tr>";
			$htm[]="<td style='font-size:10px;font-weight:bold'>{$barray["PID"]}</td>";
			$htm[]="<td style='font-size:10px;font-weight:bold'>{$barray["CPU"]}%</td>";
			$htm[]="<td style='font-size:10px;font-weight:bold'>{$barray["MEM"]}%</td>";
			$htm[]="<td style='font-size:10px;font-weight:bold'>{$barray["START"]}</td>";
			$htm[]="<td style='font-size:10px;font-weight:bold'>{$barray["TIME"]}</td>";
			$htm[]="<td style='font-size:10px;font-weight:bold'><code>{$barray["CMD"]}</code></td>";
			$htm[]="</tr>";
		}
	}
	
	$htm[]="</table></body></html>";
	return implode("",$htm);
}




?>