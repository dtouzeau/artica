<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.dhcpd.inc');
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
include_once(dirname(__FILE__) . '/ressources/class.iptables-chains.inc');
include_once(dirname(__FILE__) . '/ressources/class.baseunix.inc');
include_once(dirname(__FILE__) . '/ressources/class.bind9.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if($argv[1]=='--bind'){compile_bind();die();}

BuildDHCP();

function BuildDHCP(){
	$dhcpd=new dhcpd();
	$dhcpd->BuildConf();
	$dhcpd->Save();
}

function compile_bind(){
	$bind=new bind9();
	$bind->Compile();
	$bind->SaveToLdap();
}



?>