<?php
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.templates.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');


if(posix_getuid()<>0){
	die("Cannot be used in web server mode\n\n");
}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if($argv[1]=="--just-add"){$GLOBALS["NO_DELETE"]=true;}
if($argv[1]=="--ifconfig"){ifconfig_tests();exit;}

$unix=new unix();
$ip=$unix->find_program("ip");

$sock=new sockets();
GetDefaultRoute();

if(!$GLOBALS["NO_DELETE"]){
		exec("$ip addr show",$virtualsList);
		while (list ($index, $line) = each ($virtualsList) ){
			if(preg_match("#inet\s+(.+?)\s+scope global\s+(.+?):([0-9]+)#",$line,$re)){
				echo "Removing {$re[1]} from NIC {$re[2]} index {$re[3]}\n";
				shell_exec("$ip addr del {$re[1]} dev {$re[2]} >/dev/null 2>&1");
			}
			
		}

}


$configs=explode("\n",@file_get_contents("/etc/artica-postfix/settings/Daemons/VirtualsIPs"));
if(!is_array($configs)){
	die();
}

while (list ($index, $line) = each ($configs) ){
	if(trim($line)==null){continue;}
	if(!preg_match("#add local\s+([0-9\.]+)\/.+?dev\s+(.+?)\s+#",$line,$re)){
		echo "Starting......: tcp: Garbage:$line\n";
		continue;
	}
	$tcpaddr=$re[1];
	$nic=$re[2];
	echo "Starting......: $nic:$tcpaddr: $line\n"; 
	shell_exec("$ip $line >/dev/null 2>&1");
	shell_exec("$ip route add $tcpaddr dev $nic scope link >/dev/null 2>&1");
	
	
}

while (list ($index, $line) = each ($GLOBALS["DEFAULT_ROUTES"]) ){
	if(trim($line)==null){continue;}
	echo "Starting......: change: $line\n"; 
	shell_exec("$ip route change $line >/dev/null 2>&1");
}






function GetDefaultRoute(){
	$unix=new unix();
	$ip=$unix->find_program("ip");	
	exec("$ip route list",$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#default via\s+(.+?)\s+#",$line)){
			echo "Starting......: route : $line in memory\n"; 
			$GLOBALS["DEFAULT_ROUTES"][]=$line;
		}
	}
}

function ifconfig_tests(){
	$unix=new unix();
	$cmd=$unix->find_program("ifconfig")." -s";
	exec($cmd,$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#^(.+?)\s+[0-9]+#",$line,$re)){
			$array[trim($re[1])]=trim($re[1]);
		}
	}
	print_r($array);
	
}

	


	
	
	






?>