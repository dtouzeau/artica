<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');


	
$ou=$argv[1];
$delete_mailbox=$argv[2];
echo ini_get('error_log')."\n";
error_log("PHP Infos: Starting to delete $ou",0);

if($delete_mailbox==1){
	DeleteMailboxesOU($ou);
	
}

DeleteUser($ou);
$ldap=new clladp();
$ldap->ldap_delete("ou=$ou,dc=organizations,$ldap->suffix",true);

	$sql="DELETE FROM postfix_multi WHERE ou='$ou'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	
	$sql="DELETE FROM reports WHERE ou='$ou'";
	$q->QUERY_SQL($sql,"artica_backup");
	
	$sql="DELETE FROM nics_virtuals WHERE org='$ou'";
	$q->QUERY_SQL($sql,"artica_backup");
	
	$sql="DELETE FROM emailing_campain_queues WHERE ou='$ou'";
	$q->QUERY_SQL($sql,"artica_backup");
	
	$sql="DELETE FROM emailing_campain_linker WHERE ou='$ou'";
	$q->QUERY_SQL($sql,"artica_backup");
	
	$sql="DELETE FROM emailing_db_paths WHERE ou='$ou'";
	$q->QUERY_SQL($sql,"artica_backup");
	
	$sql="DELETE FROM emailing_mailers WHERE ou='$ou'";
	$q->QUERY_SQL($sql,"artica_backup");


writelogs("PHP Infos: Delete $ou organization done...","main",__FILE__,__LINE__);
die();



function DeleteUser($ou){
$ldap=new clladp();
	$hash=$ldap->hash_users_ou($ou);
	if(!is_array($hash)){return true;}	
	while (list ($num, $ligne) = each ($hash) ){
		if(trim($num)==null){continue;}
		$users=new user($num);
		error_log("PHP Infos: Delete $num user",0);
		$users->DeleteUser();
		}
	
}


function DeleteMailboxesOU($ou){
	$ldap=new clladp();
	$hash=$ldap->hash_users_ou($ou);
	if(!is_array($hash)){return true;}
	while (list ($num, $ligne) = each ($hash) ){
		if(trim($num)==null){continue;}
		error_log("PHP Infos: Delete $num mailbox",0);
		system("/usr/share/artica-postfix/bin/artica-install --delete-mailbox \"$num\"");
	}
	
	
	
}


//	$sock=new sockets();
//	$sock->getfile('DelMbx:'.$mbx);





?>