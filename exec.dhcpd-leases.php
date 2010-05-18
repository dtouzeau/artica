<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.dhcpd.inc');
include_once(dirname(__FILE__) . '/ressources/class.computers.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

if($argv[1]=='--force'){$GLOBALS["FORCE"]=true;}
if($argv[1]=='--single-computer'){update_computer($argv[2],$argv[3],$argv[4]);die();}

$sock=new sockets();
$EnableDHCPServer=$sock->GET_INFO('EnableDHCPServer');
if($EnableDHCPServer==0){die();}
if(!$GLOBALS["FORCE"]){
	if(!Changed()){die();}
}

CleanFile();
$datas=@file_get_contents("/var/lib/dhcp3/dhcpd.leases");

$md5=md5($datas);
if(!preg_match_all("#lease\s+(.+?)\s+{(.+?)\}#is",$datas,$re)){
	events("Unable to preg_match","main",__LINE__);
	die();
}

$dhcp=new dhcpd();
$GLOBALS["domain"]=$dhcp->ddns_domainname;
while (list ($num, $ligne) = each ($re[1]) ){
	$ip=$ligne;
	
	if(preg_match("#hardware ethernet\s+(.+?);\s+#is",$re[2][$num],$ri)){
		$MAC=trim($ri[1]);
	}
	
	if(preg_match("#client-hostname \"(.+?)\";#",$re[2][$num],$ri)){
		$HOST=trim($ri[1]);
	}
	
	$MAC=trim($MAC);
	$HOST=trim($HOST);
	$ip=str_replace("lease",'',$ip);
	$ip=trim($ip);
	$comp=new computers();
	$uid=$comp->ComputerIDFromMAC($MAC);
	$dns=true;
	
	if($uid==null){
		if($HOST==null){$uid=$ip.'$';}else{$uid=$HOST.'$';}
		$comp=new computers();
		$comp->ComputerRealName=$HOST;
		$comp->ComputerMacAddress=$MAC;
		$comp->ComputerIP=$ip;
		$comp->DnsZoneName=$GLOBALS["domain"];
		$comp->uid=$uid;
		$ComputerRealName=$HOST;
		$comp->Add();
	}else{
		
		$comp=new computers($uid);
		$comp->ComputerIP=$ip;
		$comp->DnsZoneName=$GLOBALS["domain"];
		$comp->Edit();
		if($comp->ComputerRealName==null){$ComputerRealName=$uid;}else{
			if(!preg_match("#[0-9]+\.[0-9]+\.#",$comp->ComputerRealName)){
				$ComputerRealName=$comp->ComputerRealName;
			}
		}
		
		$ComputerRealName=$comp->ComputerRealName;
		
		
	}
	
	
	$dns=new pdns($GLOBALS["domain"]);
	$dns->EditIPName(strtolower($ComputerRealName),$ip,'A',$MAC);
	
	
}

events("Set content cache has $md5","main",__LINE__);
$sock->SET_INFO('DHCPLeaseMD5',$md5);



function events($text,$function,$line){
		writelogs($text,$function,__FILE__,$line);
}


function Changed(){
	if(!is_file("/var/lib/dhcp3/dhcpd.leases")){return false;}
	$sock=new sockets();
	$DHCPLeaseMD5=$sock->GET_INFO('DHCPLeaseMD5');
	if($DHCPLeaseMD5==null){return true;}
	$datas=@file_get_contents("/var/lib/dhcp3/dhcpd.leases");
	$md5=md5($datas);
	if(trim($DHCPLeaseMD5)==$md5){return false;}
	}

function CleanFile(){
	$datas=@file_get_contents("/var/lib/dhcp3/dhcpd.leases");
	$tbl=explode("\n",$datas);
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match("#^\##",$ligne)){
			unset($tbl[$num]);
		}
	}
	
	@file_put_contents("/var/lib/dhcp3/dhcpd.leases",implode("\n",$tbl));
}

function update_computer($ip,$mac,$name){
	$mac=trim($mac);
	$name=trim(strtolower($name));
	$ip=trim($ip);
	if($ip==null){return;}
	if($mac==null){return;}
	if($name==null){return;}
	$dhcp=new dhcpd();
	$GLOBALS["domain"]=$dhcp->ddns_domainname;	
	
	$comp=new computers();
	$uid=$comp->ComputerIDFromMAC($mac);
	if($uid==null){
		$add=true;
		$uid="$name$";
		$comp=new computers();
		$comp->ComputerRealName=$name;
		$comp->ComputerMacAddress=$mac;
		$comp->ComputerIP=$ip;
		$comp->DnsZoneName=$GLOBALS["domain"];
		$comp->uid=$uid;
		$ComputerRealName=$HOST;
		$comp->Add();

	}else{
		$comp=new computers($uid);
		if($comp->ComputerRealName==null){$ComputerRealName=$name;}
		if(preg_match("#[0-9]+\.[0-9]+\.#",$comp->ComputerRealName)){$comp->ComputerRealName=$name;}
		$comp->ComputerIP=$ip;
		$comp->DnsZoneName=$GLOBALS["domain"];
		$comp->Edit();
		
	}
	
	
	$dns=new pdns($GLOBALS["domain"]);
	$dns->EditIPName(strtolower($name),$ip,'A',$mac);	

}



?>