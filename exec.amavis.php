<?php
include(dirname(__FILE__).'/ressources/class.amavis.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');

	
	if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	
	
	echo "Starting......: amavisd-new build configuration\n";
	
	$amavis=new amavis();
	$amavis->CheckDKIM();
	$conf=$amavis->buildconf();
	echo "Starting......: amavisd-new ". strlen($conf)." bytes length\n";
	@file_put_contents("/usr/local/etc/amavisd.conf",$conf);
	shell_exec("/bin/chown -R postfix:postfix /etc/amavis/dkim >/dev/null 2>&1");
	shell_exec("/bin/chmod -R 755 /etc/amavis/dkim >/dev/null 2>&1");
	echo "Starting......: amavisd-new done\n";

?>