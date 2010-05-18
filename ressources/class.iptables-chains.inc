<?php
include_once(dirname(__FILE__)."/class.mysql.inc");


class iptables_chains{
	var $id;
	var $servername;
	var $serverip;
	var $service;
	var $localport;
	var $disable;
	var $events_number;
	var $iptables_id;
	var $rule_string;
	var $isRoot=false;
	var $error="";

	
	function iptables_chains(){
		if(posix_getuid()==0){$this->isRoot=true;}
		
	}
	
	
public	function addPostfix_chain(){
		$rulemd5=md5($this->rule_string);
		$sql="INSERT INTO iptables(servername,serverip,local_port,disable,events_number,rule_string,rulemd5,flux)
		VALUES('$this->servername','$this->serverip','25','0','$this->events_number','$this->rule_string','$rulemd5','INPUT')";
		
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){$this->events("$q->mysql_error");}
		
		}
		
private function events($text){
			if(!$this->isRoot){writelogs("iptables_chains: $text",__CLASS__,__FILE__);return null;}
	
		$pid=getmypid();
		$date=date('Y-m-d H:i:s');
		$logFile=$_GET["filelogs"];
		if(!is_file($logFile)){echo "iptables_chains::events() unable to stat log file\n";return null;}
		$size=filesize($logFile);
		if($size>1000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$date [$pid] $text\n");
		@fclose($f);	
		}
		
public function deletePostfix_chain($rulemd5){
	$sql="DELETE FROM iptables WHERE rulemd5='$rulemd5'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){$this->events("$q->mysql_error");$this->error=$q->mysql_error;return false;}
	return true;
	}
	
public function deleteAllPostfix_chains(){
	$sql="DELETE FROM iptables WHERE local_port=25 AND flux='INPUT'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){$this->events("$q->mysql_error");
		$this->error=$q->mysql_error;
		return false;
	}
	return true;
	}	
	
	

function loadPostfix_chains($limit=0,$search=null){
	$q=new mysql();
	if($search<>null){
		if(strpos($search,"*")==0){$search=$search."*";}
		$search=str_replace("*","%",$search);
		$sql_search=" AND (servername LIKE '$search' OR serverip LIKE '$search') ";
		
	}
	
	$sql_count="SELECT COUNT(*) AS tcount FROM iptables WHERE local_port=25 AND flux='INPUT'{$sql_search}";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql_count,"artica_backup"));
	$max=$ligne["tcount"];
	if($limit==null){$limit=0;}
	
	$sql="SELECT * FROM iptables WHERE local_port=25 AND flux='INPUT' {$sql_search}ORDER BY ID DESC LIMIT $limit,50";
	$q=new mysql();	
	$results=$q->QUERY_SQL($sql,"artica_backup");
	return array($max,$results);
		
	//-j LOG --log-prefix "TCP LOGDROP: "
}
}

?>