<?php

include_once(dirname(__FILE__)."/frame.class.inc");

if(isset($_GET["cluster-key"])){CLUSTER_KEY();exit;}
if(!isset($_GET["key"])){die();}


sys_events(basename(__FILE__)."::{$_SERVER['REMOTE_ADDR']}:: Save key {$_GET["key"]} (". strlen($_GET["value"]).") bytes length()");

@copy("/usr/share/artica-postfix/ressources/logs/{$_GET["key"]}","/etc/artica-postfix/settings/Daemons/{$_GET["key"]}");
@unlink("/usr/share/artica-postfix/ressources/logs/{$_GET["key"]}");

function CLUSTER_KEY(){
sys_events(basename(__FILE__)."::{$_SERVER['REMOTE_ADDR']}:: Save cluster key {$_GET["cluster-key"]} (". strlen($_GET["value"]).") bytes length()");	
@copy("/usr/share/artica-postfix/ressources/logs/cluster/{$_GET["cluster-key"]}","/etc/artica-cluster/{$_GET["cluster-key"]}");
@unlink("/usr/share/artica-postfix/ressources/logs/cluster/{$_GET["cluster-key"]}");	
}


?>