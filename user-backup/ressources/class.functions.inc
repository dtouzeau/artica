<?php
include_once(dirname(__FILE__)."/class.user.inc");

class funct{
	
	
	public function getCountOFMailsRTM(){
		$delivery_user=delivery_users();
		$sql="select COUNT(time_stamp) AS tcount FROM smtp_logs WHERE ($delivery_user)";
		$q=new mysql();
		writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));
		return $ligne["tcount"];
		
	}
	
	public function getSizeOfBackupedMails(){
		$delivery_user=mailto();
		$sql="select SUM(filesize) AS tcount FROM storage WHERE ($delivery_user)";
		$q=new mysql();
		writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		return $ligne["tcount"];
		
	}

	public function getCountOFMailQuar(){
		$delivery_user=mailto();
		$sql="select COUNT(MessageID) AS tcount FROM quarantine WHERE ($delivery_user)";
		$q=new mysql();
		writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		return $ligne["tcount"];
		
	}	
	
	
}

function mailto($and=null){
		$mails=$_SESSION["ALL_MAILS"];
		
if(!isset($_SESSION["ALL_MAILS"])){
		$user=new user($_SESSION["uid"]);
		$_SESSION["ALL_MAILS"]=$user->HASH_ALL_MAILS;
	}	

	
	$mails=$_SESSION["ALL_MAILS"];
	
		writelogs("eMail number for {$_SESSION["uid"]}: ".count($mails),__FUNCTION__,__FILE__,__LINE__);
		
		if(count($mails)==1){
			return "mailto='{$mails[0]}' $and";
		}
		
		
		$delivery_user="mailto='{$mails[0]}' $and";
		unset($mails[0]);
		while (list ($num, $ligne) = each ($mails)){
			$num=$num;
			$delivery_user=$delivery_user." OR mailto='$ligne' $and";
		}
return $delivery_user;		
}


function delivery_users($and=null){
		$mails=$_SESSION["ALL_MAILS"];
		
if(!isset($_SESSION["ALL_MAILS"])){
		$user=new user($_SESSION["uid"]);
		$_SESSION["ALL_MAILS"]=$user->HASH_ALL_MAILS;
	}	

	
	$mails=$_SESSION["ALL_MAILS"];
	
		writelogs("eMail number for {$_SESSION["uid"]}: ".count($mails),__FUNCTION__,__FILE__,__LINE__);
		
		if(count($mails)==1){
			return "delivery_user='{$mails[0]}' $and";
		}
		
		
		$delivery_user="delivery_user='{$mails[0]}' $and";
		unset($mails[0]);
		while (list ($num, $ligne) = each ($mails)){
			$num=$num;
			$delivery_user=$delivery_user." OR delivery_user='$ligne' $and";
		}
return $delivery_user;		
}





?>