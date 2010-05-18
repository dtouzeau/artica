<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.os.system.inc');
	include_once(dirname(__FILE__).'/framework/frame.class.inc');
	include_once(dirname(__FILE__).'/framework/class.unix.inc');
	
	
	CleanTempDirs();
	
	die();
	
	
	
	
	
function CleanTempDirs(){
	$unix=new unix();
	$dirs=$unix->dirdir("/tmp");
	if(!is_array($dirs)){return null;}
	while (list ($num, $ligne) = each ($dirs) ){
		if(trim($num)==null){continue;}
		$time=$unix->file_time_min($num);
		if($time<380){continue;}
		shell_exec("/bin/rm -rf \"$num\"");
		
	}
	
}

?>