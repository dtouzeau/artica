<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/ressources/class.autofs.inc');

$ldap=new clladp();
$suffix="dc=organizations,$ldap->suffix";
$filter="(&(ObjectClass=SharedFolders)(SharedFolderList=*))";
$attr=array("gidNumber");

$sr =@ldap_search($ldap->ldap_connection,$suffix,$filter,$attr);
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		for($i=0;$i<$hash["count"];$i++){
			$gpid=$hash[$i][strtolower("gidNumber")][0];
			$auto=new autofs();
			$auto->AutofsSharedDir($gpid);
			
		}











?>