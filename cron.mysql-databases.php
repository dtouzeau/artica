<?php
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");


if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(!Build_pid_func(__FILE__,"MAIN")){
	die();
}
$sql=new mysql();
$sql->BuildTables();

?>