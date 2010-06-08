<?php
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.templates.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');


if(posix_getuid()<>0){
	die("Cannot be used in web server mode\n\n");
}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if($argv[1]=="--just-add"){die();}
if($argv[1]=="--ifconfig"){ifconfig_tests();exit;}


$configs_exploded=unserialize(base64_decode(@file_get_contents("/etc/artica-postfix/settings/Daemons/VirtualsIPs")));


if(is_file("/etc/network/interfaces")){
	echo "Starting......: Virtuals IP mode Debian\n";
	ParseDebianNetworks($configs_exploded);
}

if(is_dir("/etc/sysconfig/network-scripts")){
	ParseRedHatNetworks($configs_exploded);
}



function ParseDebianNetworks($config){
	
	$f=explode("\n",@file_get_contents("/etc/network/interfaces"));
	
	
	
	
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#iface\s+([a-z0-9\:]+)#",$ligne,$re)){
			$iface=$re[1];
			$array[$iface][]=$ligne;
			continue;
		}
		
		if(preg_match("#^auto#",$ligne)){continue;}
		
		if($iface<>null){
			if(trim($ligne)<>null){
				$array[$iface][]=$ligne;
			}
		}
	}
	
	
  while (list ($eth, $nics) = each ($array) ){
  	if(strpos($eth,":")>0){
  		unset($array[$eth]);}	
  }
  
  reset($array);

	
	if(is_array($config)){
		while (list ($eth, $nics) = each ($config) ){
			echo "Starting......: Virtuals $eth {$nics["IP_ADDR"]}/{$nics["NETMASK"]} gateway {$nics["GATEWAY"]}\n";
			$array[$eth][]="iface $eth inet static";
			$array[$eth][]="\taddress {$nics["IP_ADDR"]}";
			$array[$eth][]="\tnetmask {$nics["NETMASK"]}";
			$array[$eth][]="\tbroadcast {$nics["BROADCAST"]}";
			$array[$eth][]="\tgateway {$nics["GATEWAY"]}";
			}
	}
	
	
while (list ($eth, $nics2) = each ($array) ){	
	
			$conf[]="auto $eth";
			if($eth<>"lo"){
				echo "Starting......: Virtuals stopping $eth\n";
				system("ifdown $eth");
			}
			
			while (list ($index, $line) = each ($nics2) ){	
				
				if(!$att["$eth$line"]){
					$conf[]=$line;}
				$att["$eth$line"]=true;
			}
}
$conf[]="";
@file_put_contents("/etc/network/interfaces",@implode("\n",$conf));
echo "Starting......: Virtuals reloading interfaces\n";
system("/etc/init.d/networking restart");
shell_exec("ifconfig lo 127.0.0.1");
	
}

function ParseRedHatNetworks($config){

	foreach (glob("/etc/sysconfig/network-scripts/ifcfg-*") as $filename) {
		$fileconf=basename($filename);
		if(preg_match("#ifcfg-(.+?):([0-9]+)#",$fileconf)){
			@unlink($filename);
		}
	}
	
	if(is_array($config)){
		while (list ($eth, $nics) = each ($config) ){
			$array[]="DEVICE=$eth";
			$array[]="IPADDR={$nics["IP_ADDR"]}";
			$array[]="NETMASK={$nics["NETMASK"]}";
			$array[]="BROADCAST={$nics["BROADCAST"]}";
			$array[]="GATEWAY={$nics["GATEWAY"]}";
			$array[]="ONBOOT=yes";
			$array[]="USERCTL=yes";
			@file_put_contents("/etc/sysconfig/network-scripts/ifcfg-$eth",@implode("\n",$array));
			unset($array);
			}
	}		
	shell_exec("/etc/init.d/network restart");
	shell_exec("ifconfig lo 127.0.0.1");
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