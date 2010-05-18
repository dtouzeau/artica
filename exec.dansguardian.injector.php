<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.system.network.inc");
include_once(dirname(__FILE__)."/ressources/class.dansguardian.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
cpulimit();
$_GET["LOGFILE"]="/var/log/artica-postfix/dansguardian-logger.debug";
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["debug"]=true;$GLOBALS["VERBOSE"]=true;}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}


if(!Build_pid_func(__FILE__,"MAIN")){
	events(basename(__FILE__).": Already executed.. aborting the process");
	die();
}


$files=DirListsql("/var/log/artica-postfix/dansguardian-stats");
$max=count($files);
if($max=0){die();}

if(!is_array($files)){
	events("No files die()");
	die();
}
writelogs("Parse $max sql files","MAIN",__FILE__,__LINE__);
$count=0;
while (list ($num, $file) = each ($files) ){
	$q=new mysql();
	$count=$count+1;
	$sql=@file_get_contents("/var/log/artica-postfix/dansguardian-stats/$file");
	if(trim($sql)==null){
		@unlink("/var/log/artica-postfix/dansguardian-stats/$file");
		continue;
	}
	$q->QUERY_SQL($sql,"artica_events");
	if($q->ok){
		writelogs("success Parse $file sql file","MAIN",__FILE__,__LINE__);
		@unlink("/var/log/artica-postfix/dansguardian-stats/$file");
	}else{
		writelogs("Failed Parse $file sql file $count/$max","MAIN",__FILE__,__LINE__);
		writelogs("$q->mysql_error","MAIN",__FILE__,__LINE__);
		writelogs("SQL[\"$sql\"]","MAIN",__FILE__,__LINE__);
	}
	
	$q->ok=true;
	
}


function events($text){
		$pid=getmypid();
		$logFile=$_GET["LOGFILE"];
		$size=filesize($logFile);
		if($size>1000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		if($GLOBALS["debug"]){echo "$pid $text\n";}
		@fwrite($f, "$pid ".basename(__FILE__)." $text\n");
		@fclose($f);	
		}
		
function DirListsql($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){
		events("Unable to open \"$path\"");
		return array();
	}
		$count=0;	
		while ($file = readdir($dir_handle)) {
		  if($file=='.'){continue;}
		  if($file=='..'){continue;}
		  if(!is_file("$path/$file")){continue;}
		   if(!preg_match("#\.sql$#",$file)){continue;}
		  	$array[$file]=$file;
		  }
		if(!is_array($array)){return array();}
		@closedir($dir_handle);
		return $array;
}		

		
		
		
?>