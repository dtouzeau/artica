<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/samba.sid.php');

system('/usr/share/artica-postfix/bin/artica-install --samba-reconfigure');
$ldap=new clladp();
$samba=new samba();
$sid=$ldap->LOCAL_SID();
$samba->ChangeSID($sid);
SMBCHANGECOMPUTERS();
SMBGROUPS();
SMBCHANGEUSERS();
SMBRESTART();
die();


?>