<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");

cpulimit();



if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

@mkdir("/var/log/artica-postfix/IMAP",0755,true);

if($argv[1]=='--postfix'){
	events("Starting OnlyPostfix...");
	OnlyPOstfix();
	die();
}


$q=new mysql();
optimizetable();
ScanPostfixID($q);
ScanCyrusConnections($q);
ScanVirusQueue($q);
CheckPostfixLogs();
THREAD_COMMAND_SET(LOCATE_PHP5_BIN() ." ". dirname(__FILE__)."/exec.last.100.mails.php");
THREAD_COMMAND_SET(LOCATE_PHP5_BIN() ." ". dirname(__FILE__)."/exec.admin.smtp.flow.status.php");

function OnlyPOstfix(){
	$pid=getmypid();
	$pidefile="/etc/artica-postfix/croned.1/".basename(__FILE__).".onlypostfix.pid";
	if(is_file($pidefile)){
		$currentpid=trim(file_get_contents($pidefile));
		if($currentpid<>$pid){
			if(is_dir('/proc/'.$currentpid)){
			write_syslog("Already instance executed aborting...",__FILE__);
			die();
			}	
		}
	}
	
	file_put_contents($pidefile,$pid);
	$path="/var/log/artica-postfix/RTM";
	$arrayf=DirListPostfix($path);
	$q=new mysql();
	if(!is_array($arrayf)){return null;}
	$max=count($arrayf);
	if($max<500){return null;}
	$count=0;
	events("Starting analyze $max sql files....",__FILE__);
	while (list ($num, $file) = each ($arrayf) ){
		$count=$count+1;
		events("OnlyPOstfix(): parsing $path/$file $count/$max");
		if(!preg_match("#\.msg$#",$file)){continue;}
		if($file=="NOQUEUE.msg"){@unlink("$path/$file");continue;}
		if(!is_file("$path/$file")){continue;}
		
		if(PostfixFullProcess("$path/$file",$q)){
					echo "OnlyPOstfix(): success $path/$file $count/$max\n";
					@unlink("$path/$file");
					
					}
		}	
	
}

function ScanCyrusConnections($q){
$path="/var/log/artica-postfix/IMAP";
	$files=DirListsql($path);
	$startedAT=date("Y-m-d H:i:s");
	$count=0;
	if(!is_array($files)){events(__FUNCTION__." No files.. Aborting\n");return null;}
   events('##########################################################');
   events("Get sql in $path ". count($files)." files");		
	while (list ($num, $file) = each ($files) ){
		$count=$count+1;
		events("running $path/$file");	
		$q->QUERY_SQL(@file_get_contents("$path/$file"),"artica_events");
		if(!$q->ok){
			events("$path/$file failed");
		}else{
			@unlink("$path/$file");
		}
	}
}

function ScanVirusQueue($q){
$path="/var/log/artica-postfix/infected-queue";
	$files=DirListsql($path);
	$startedAT=date("Y-m-d H:i:s");
	$count=0;
	if(!is_array($files)){events(__FUNCTION__." No files.. Aborting\n");return null;}
   events('##########################################################');
   events("Get sql in $path ". count($files)." files");		
	while (list ($num, $file) = each ($files) ){
		$count=$count+1;
		events("running $path/$file");	
		$q->QUERY_SQL(@file_get_contents("$path/$file"),"artica_events");
		if(!$q->ok){
			events("$path/$file failed");
		}else{
			@unlink("$path/$file");
		}
	}
}


function ScanPostfixID($q){
	$q=new mysql();
	$path="var/log/artica-postfix/RTM";
	$files=DirList("/var/log/artica-postfix/RTM");
	$startedAT=date("Y-m-d H:i:s");
	$count=0;
	if(!is_array($files)){events("ScanPostfixID() No files.. Aborting");return null;}
		
   events('ScanPostfixID():: ##########################################################');
   events("ScanPostfixID():: Get msg in $path ". count($files));
   
   	$max=count($files);
   	if($max>0){events("ScanPostfixID():: Starting analyze $max sql files....",__FILE__);}
	while (list ($num, $file) = each ($files) ){
		$count=$count+1;
		
		if(preg_match("#\.id-message$#",$file)){
			$amavis[]=$file;
			continue;
		}
		
		events("ScanPostfixID():: ($count/$max)");
		events("ScanPostfixID()::  \"/$path/$file\"");
		
		
		if(!preg_match("#\.msg$#",$file)){continue;}
		
		if($file=="NOQUEUE.msg"){
			events("ScanPostfixID(): Delete /$path/$file");
			@unlink("/$path/$file");
			continue;
		}
		
		
		if(PostfixFullProcess("/$path/$file",$q)){
			SetStatus("Postfix",$max,$count,$startedAT);
			unset($files[$num]);
			events("ScanPostfixID(): DOne...");
			events("ScanPostfixID():: ($count/$max)");
		}else{continue;}
		
	}
	
	
	events("##########################################################");
	
	events("ScanPostfixID():Get messages-id");
	if(is_array($amavis)){
		reset($amavis);
	
		$max=count($amavis);
		$count=0;
		while (list ($num, $file) = each ($amavis) ){
			$count=$count+1;
			if(!preg_match("#\.id-message$#",$file)){continue;}
			events("##########################################################");
			events("ScanPostfixID():amavis_logger(): parsing /$path/$file $count/$max");
			SetStatus("amavis",$max,$count,$startedAT);
			amavis_logger("/$path/$file");
			events("##########################################################");
				
		}	
	}
	
if($count>0){write_syslog("Success inserting $count mails events in mysql database...",__FILE__);}
	
}


function SetStatus($filetype,$max,$current,$startedAT){
	$ini=new Bs_IniHandler();
	$ini->set("PROGRESS","type",$filetype);
	$ini->set("PROGRESS","max",$max);
	$ini->set("PROGRESS","current",$current);
	$ini->set("PROGRESS","time",date('Y-m-d H:i:s'));
	$ini->set("PROGRESS","pid",getmypid());
	$ini->set("PROGRESS","starton",$startedAT);
	
	$ini->saveFile("/usr/share/artica-postfix/ressources/logs/postfix-logger.ini");
	@chmod("/usr/share/artica-postfix/ressources/logs/postfix-logger.ini",0777);
	
	
}

function PostfixFullProcess($file){
	$q=new mysql();
	$org_file=$file;
	if(!is_file($file)){return null;}
	$ini=new Bs_IniHandler($file);
	$delivery_success=$ini->_params["TIME"]["delivery_success"];
	$message_id=$ini->_params["TIME"]["message-id"];
	$time_end=$ini->_params["TIME"]["time_end"];
	$mailfrom=$ini->_params["TIME"]["mailfrom"];
	$mailto=$ini->_params["TIME"]["mailto"];	
	$delivery_success=$ini->_params["TIME"]["delivery_success"];
	$bounce_error=$ini->_params["TIME"]["bounce_error"];
	$time_connect=$ini->_params["TIME"]["time_connect"];
	$delivery_user=$ini->_params["TIME"]["delivery_user"];
	$time_start=$ini->_params["TIME"]["time_start"];
	$smtp_sender=$ini->_params["TIME"]["smtp_sender"];
	$search_postfix_id=true;
	if($time_connect==null){if($time_start<>null){$time_connect=$time_start;}}
	

	
	$file=basename($file);
	$postfix_id=str_replace(".msg","",$file);
	
	
	if($mailto==null){if($delivery_user<>null){$mailto=$delivery_user;}}
	if($time_connect==null){if($time_end<>null){$time_connect=$time_end;}}
	if($time_end==null){if($time_connect<>null){$time_end=$time_connect;}}
	
	if(preg_match('#(.+?)@(.+)#',$mailfrom,$re)){$domain_from=$re[2];}
	if(preg_match('#(.+?)@(.+)#',$mailto,$re)){$domain_to=$re[2];}
	$mailfrom=str_replace("'",'',$mailfrom);
	
	if($delivery_success==null){$delivery_success="no";}
	
	if($message_id==null){$message_id=md5(time().$mailfrom.$mailto);}
	
	$bounce_error_array["RBL"]=true;
	$bounce_error_array["Helo command rejected"]=true;
	$bounce_error_array["Domain not found"]=true;
	
	
	if($bounce_error_array[$bounce_error]){$search_postfix_id=false;}else{
		events("PostfixFullProcess():: bounce ERROR=\"$bounce_error\"");
	}
	
	
	
	if($search_postfix_id){$sqlid=getid_from_postfixid($postfix_id,$q);}
	
	events("PostfixFullProcess():: message-id=$message_id from=$mailfrom to=$mailto bounce_error=$bounce_error");
	
	if($sqlid==null){
		$sql="INSERT INTO smtp_logs (delivery_id_text,msg_id_text,time_connect,time_sended,delivery_success,sender_user,sender_domain,delivery_user,delivery_domain,bounce_error,smtp_sender  )
		VALUES('$postfix_id','$message_id','$time_connect','$time_end','$delivery_success','$mailfrom','$domain_from','$mailto','$domain_to','$bounce_error','$smtp_sender');
		";
		events("PostfixFullProcess():: INSERT $message_id or $postfix_id");
		$q->QUERY_SQL($sql,"artica_events");
		events("PostfixFullProcess():: done..");
		
		if($q->ok){
			events("PostfixFullProcess():: ADD time=<$time_connect/$time_end> id=<$postfix_id/$messageid> rcpt=<$mailto> From=<$mailfrom> server=<$smtp_sender> bounce=<$bounce_error>");
			events("PostfixFullProcess():: Delete $org_file DONE... finish");
			@unlink($org_file);
			return true;
		}else{echo events("FAILED MYSQL $org_file");events($sql);return false;}
		
	}else{
		if($mailfrom<>null){$mailfrom=" ,sender_user='$mailfrom'";}
		if($delivery_success<>null){$delivery_success=" ,delivery_success='$delivery_success'";}
		if($domain_from<>null){$domain_from=" ,sender_domain='$domain_from'";}
		if($domain_to<>null){$domain_to=" ,delivery_domain='$domain_to'";}
		if($bounce_error<>null){$bounce_error=" ,bounce_error='$bounce_error'";}
		if($time_connect<>null){$time_connect=" ,time_connect='$time_connect'";}
		if($time_end<>null){$time_end=" ,time_sended='$time_end'";}
		if($message_id<>null){$message_id=" ,msg_id_text='$message_id'";}
		if($smtp_sender<>null){$smtp_sender=" ,smtp_sender='$smtp_sender'";}
		
		
									
		events("PostfixFullProcess():: EDIT $message_id or $postfix_id");
		$sql="UPDATE smtp_logs SET delivery_id_text='$postfix_id'$mailfrom$delivery_success$domain_from$domain_to$bounce_error$time_connect$time_end$message_id
		WHERE id=$sqlid";
		$q->QUERY_SQL($sql,"artica_events");
		if($q->ok){
			events("EDIT time=<$time_connect/$time_end> id=<$postfix_id/$messageid> rcpt=<$mailto> From=<$mailfrom> server=<$smtp_sender> bounce=<$bounce_error>");
			events("PostfixFullProcess():: Delete $org_file DONE... finish");
			@unlink($org_file);
			return true;
		}
		else{
			events("FAILED MYSQL $org_file");
			events($sql);
			return false;
			}
		
	}
}

function getid_from_postfixid($postfix_id,$q){
	$date=date('Y-m-d');
	$sqlclass=new mysql();
	$sql="SELECT id FROM smtp_logs WHERE delivery_id_text='$postfix_id' AND DATE_FORMAT(`time_stamp`,'%Y-%m-%d')='$date'";
	events("getid_from_postfixid:: $sql");
	$ligne=@mysql_fetch_array($sqlclass->QUERY_SQL($sql,"artica_events"));
	events("getid_from_postfixid($postfix_id)={$ligne["id"]}");
	return trim($ligne["id"]);
	}
	

function deleteid_from_messageid($messageid){
	if($messageid==null){return null;}
	$q=new mysql();
	$sql="DELETE FROM smtp_logs WHERE msg_id_text='$messageid'";
	$q->QUERY_SQL($sql,"artica_events");
	
}

function DirListPostfix($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){
		write_syslog("Unable to open \"$path\"",__FILE__);
		return array();
	}
		$count=0;	
		while ($file = readdir($dir_handle)) {
		  if($file=='.'){continue;}
		  if($file=='..'){continue;}
		  if(!is_file("$path/$file")){continue;}
		   if(!preg_match("#\.msg$#",$file)){continue;}
		  	$array[$file]=$file;
		  }
		if(!is_array($array)){return array();}
		@closedir($dir_handle);
		return $array;
}
function DirListsql($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){
		write_syslog("Unable to open \"$path\"",__FILE__);
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

function DirList($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){
		write_syslog("Unable to open \"$path\"",__FILE__);
		return array();
	}
$count=0;	
while ($file = readdir($dir_handle)) {
  if($file=='.'){continue;}
  if($file=='..'){continue;}
  if(!is_file("$path/$file")){continue;}
  	$array[$file]=$file;
  }
if(!is_array($array)){return array();}
@closedir($dir_handle);
return $array;
}

function amavis_logger($fullpath){
	
	$q=new mysql();
	$ini=new Bs_IniHandler($fullpath);
	$message_id=$ini->_params["TIME"]["message-id"];
	$time_amavis=$ini->_params["TIME"]["time_amavis"];
	$smtp_sender=$ini->_params["TIME"]["server_from"];
	$mailfrom=$ini->_params["TIME"]["mailfrom"];
	$mailto=$ini->_params["TIME"]["mailto"];
	$Country=$ini->_params["TIME"]["Country"];
	$Region=$ini->_params["TIME"]["Region"];
	$City=$ini->_params["TIME"]["City"];
	$kas=$ini->_params["TIME"]["kas"];
	$banned=$ini->_params["TIME"]["banned"];
	$infected=$ini->_params["TIME"]["infected"];
	$spammy=$ini->_params["TIME"]["spammy"];
	$spam=$ini->_params["TIME"]["spam"];
	$blacklisted=$ini->_params["TIME"]["blacklisted"];
	$whitelisted=$ini->_params["TIME"]["whitelisted"];
	
	events("$fullpath ($message_id) from=<$mailfrom> to=<$mailto>");
	$Region=str_replace("'",'`',$Region);
	$Country=str_replace("'",'`',$Country);
	$City=str_replace("'",'`',$City);
	
	if(preg_match('#(.+?)@(.+)#',$mailfrom,$re)){$domain_from=$re[2];}
	if($kas==null){$kas=0;}
	if($banned==null){$banned=0;}
	if($infected==null){$infected=0;}
	if($spammy==null){$spammy=0;}
	if($spam==null){$spam=0;}
	if(!is_numeric($whitelisted)){$whitelisted=0;}
	if($blacklisted==null){$blacklisted=0;}
	if($whitelisted==null){$whitelisted=0;}
	$mailto=$mailto.",";
	$mailto_array=explode(",",$mailto);
	if(!is_array($mailto_array)){return null;}
	
	$mailfrom=str_replace("'",'',$mailfrom);	
	
	events("Delete id <$message_id>");
	if($message_id<>null){deleteid_from_messageid($message_id,$q);}
	
	events("Start loop for Recipients number=".count($mailto_array)." id=<$message_id>");
	
	while (list ($num, $destataire) = each ($mailto_array) ){
		if($message_id==null){continue;}
		if($destataire==null){continue;}
		if(preg_match('#(.+?)@(.+)#',$destataire,$re)){$domain_to=$re[2];}	
		events("$time_amavis $messageid rcpt=<$destataire> From=<$mailfrom> SQL ID=\"$id\" ADD spammy=$spammy, infected=$infected,spam=$spam");
		$sql="INSERT INTO smtp_logs (msg_id_text,sender_user,delivery_user,sender_domain,delivery_domain,time_amavis,Country,Region,kas,infected,
		spammy,SPAM,blacklisted,whitelisted,smtp_sender,time_connect)
		VALUES('$messageid','$mailfrom','$destataire','$domain_from','$domain_to','$time_amavis','$Country','$Region','$kas','$infected','$spammy','$spam','$blacklisted',
		'$whitelisted','$smtp_sender','$time_amavis')";
		$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){
				events("amavis_logger Failed $sql");
				return null;
				}
		}
	events("DELETE $fullpath");
	if(!@unlink("$fullpath")){events("WARNING UNABLE TO DELETE $fullpath");}
	
}
function events($text){
		$pid=getmypid();
		$date=date('H:i:s');
		$logFile="/var/log/artica-postfix/postfix-logger.sql.debug";
		$size=filesize($logFile);
		if($size>5000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$date [$pid] $text\n");
		@fclose($f);	
		}
		
function optimizetable(){
	$file="/etc/artica-postfix/table.smtp.logs.optimize";
	$filetime=intval(file_time_min($file));
	events("optimizetable:: $file=$filetime mn");
	if($filetime<2880){return null;}	
	@unlink($file);
	file_put_contents($file,date("y-m-d H:i:s"));
	$q=new mysql();	
	events("OPTIMIZE TABLE");
	$sql="OPTIMIZE TABLE `smtp_logs`";
	$q->QUERY_SQL($sql,"artica_events");
	events("OPTIMIZE TABLE DONE");
	
}


function CheckPostfixLogs(){
	$log_path=LOCATE_MAILLOG_PATH();
	if(!is_file($log_path)){events("CheckPostfixLogs(): Cannot found log path");return null;}
	$size=filesize($log_path);
	events("CheckPostfixLogs():$log_path=$size bytes");
	
	if($size==0){
		events("CheckPostfixLogs():Restarting postfix");
		if(is_file("/etc/init.d/syslog-ng")){shell_exec("/etc/init.d/syslog-ng restart");}
		shell_exec("/etc/init.d/artica-postfix restart postfix");
	}
	
	
}


?>