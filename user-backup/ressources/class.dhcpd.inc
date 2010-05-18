<?php
include_once(dirname(__FILE__).'/class.users.menus.inc');
include_once(dirname(__FILE__).'/class.system.network.inc');
include_once(dirname(__FILE__).'/class.ldap.inc');
include_once(dirname(__FILE__).'/class.ini.inc');
include_once(dirname(__FILE__).'/class.bind9.inc');
include_once(dirname(__FILE__) .'/class.tcpip.inc');
include_once(dirname(__FILE__) .'/class.mysql.inc');


class dhcpd_hosts{
	var $uid;
	var $dn;
	var $dhcpHWAddress;
	var $dhcpStatements;

	var $IP;
	var $MAC;
	
	function dhcpd_hosts($uid=null){
		
		if($uid==null){
			writelogs("DHCP:: Resquest an uid null, aborting",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return null;
		}
		$this->uid=$uid;	
		writelogs("DHCP:: Resquest an uid $this->uid...",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->load();
		
	}
	
	private function load(){
		if(substr($this->uid,0,1)=='$'){$this->uid=substr($this->uid,1,strlen($this->uid));}
		if(substr($this->uid,strlen($this->uid)-1,1)=='$'){$this->uid=substr($this->uid,0,strlen($this->uid)-1);}
		$ldap=new clladp();
		$this->dn="cn=$this->uid,cn=DhcpConfig,ou=dhcp,$ldap->suffix";
		if(!$ldap->ExistsDN($this->dn)){return null;}
		$hash=$ldap->Ldap_read($this->dn,"(objectclass=*)",array());
		$this->dhcpHWAddress=$hash[0][strtolower("dhcpHWAddress")][0];
		$this->dhcpStatements=$hash[0][strtolower("dhcpStatements")][0];
	
		
	}
	
function DeleteFixed(){
	$ldap=new clladp();
	if(!$ldap->ExistsDN($this->dn)){return true;}
	$tpl=new templates();
	if(!$ldap->ldap_delete($this->dn)){
		echo $tpl->_ENGINE_parse_body("DHCP:: {error} function:".__FUNCTION__."\nfile: ".__FILE__."\nline:".__LINE__."\n");
		echo $ldap->ldap_last_error;
		return false;
	}
	
	$sock=new sockets();
	$sock->getfile("BackGroundDCHPD");
	
	return true;
	
}	
function AddFixed(){
	$ldap=new clladp();
	$tpl=new templates();
	$tcp=new IP();
	if($this->dn==null){
		$this->dn="cn=$this->uid,cn=DhcpConfig,ou=dhcp,$ldap->suffix";
	}
	
	if(!$tcp->isValid($this->IP)){
		echo $tpl->_ENGINE_parse_body("DHCP:: {error} IP: $this->IP {invalid}\n");
		return false;
	}	
	
	
	$this->LDAPBranch();
	if(!$ldap->ExistsDN($this->dn)){
		$upd["objectClass"][]="top";
		$upd["objectClass"][]="dhcpHost";
		$upd["cn"][]="$this->uid";
		if(!$ldap->ldap_add($this->dn,$upd)){
			echo $tpl->_ENGINE_parse_body("DHCP:: {error} function:".__FUNCTION__."\nfile: ".__FILE__."\nline:".__LINE__."\n");
			echo $ldap->ldap_last_error;
			return false;
		}
		
		unset($upd);
	}
	
	$upd["dhcpHWAddress"][0]="ethernet $this->MAC";
	$upd["dhcpStatements"][0]="fixed-address $this->IP";
	if(!$ldap->Ldap_modify($this->dn,$upd)){
	   echo $tpl->_ENGINE_parse_body("DHCP:: {error} function:".__FUNCTION__."\nfile: ".__FILE__."\nline:".__LINE__."\n");
	   echo $ldap->ldap_last_error;
	   return false;
		
	}
	
	$sock=new sockets();
	$sock->getfile("BackGroundDCHPD");
	return true;	
	
}
	
function LDAPBranch(){
	$ldap=new clladp();
	$users=new usersMenus();
	$dn="ou=dhcp,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd["ou"][]="dhcp";
		$upd["objectClass"][]="organizationalUnit";
		$upd["objectClass"][]="top";
		if(!$ldap->ldap_add($dn,$upd)){
			echo __FUNCTION__.' line '.__LINE__."\n".__FILE__;
			echo "Please try to re-index your LDAP server\n";
			echo "Under the troubleshoot section\n";			
			echo $ldap->ldap_last_error;
			return;
		}
		
	}
	
	
	$dn="cn=$users->hostname,ou=dhcp,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]="dhcpServer";
		$upd["objectClass"][]="top";
		$upd["cn"][]="$users->hostname";
		$upd["dhcpServiceDN"][]="cn=dhcpConfig,ou=dhcp,$ldap->suffix";
		
		if(!$ldap->ldap_add($dn,$upd)){
			echo __FUNCTION__.' line '.__LINE__."\n".__FILE__;
			echo "Please try to re-index your LDAP server\n";
			echo "Under the troubleshoot section\n";			
			echo $ldap->ldap_last_error;
			return;
		}
		
	}	
	
	$dn="cn=DhcpConfig,ou=dhcp,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]="dhcpService";
		$upd["objectClass"][]="dhcpOptions";
		$upd["dhcpStatements"][]="ddns-update-style ad-hoc";
		$upd["dhcpStatements"][]="default-lease-time 86400";
		$upd["dhcpStatements"][]="max-lease-time 604800";
		$upd["dhcpPrimaryDN"][]="ou=dhcp,$ldap->suffix";
		if(!$ldap->ldap_add($dn,$upd)){
			echo __FUNCTION__.' line '.__LINE__."\n".__FILE__;
			echo "Please try to re-index your LDAP server\n";
			echo "Under the troubleshoot section\n";
			echo $ldap->ldap_last_error;
			return;
		}
		
	}	
	
}	
}


class dhcpd{
	var $ddns_domainname="";
	var $max_lease_time;
	var $array_tcp;
	var $listen_nic;
	var $netmask;
	var $range2;
	var $range1;
	var $DNS_1;
	var $DNS_2;
	var $EnableArticaAsGateway=0;
	var $EnableArticaAsDNSFirst=0;
	var $gateway;
	var $pxe_enable=0;
	var $pxe_server=null;
	var $pxe_file=null;
	var $conf;
	var $routes=array();
	
	function dhcpd($noload=0){
		if($noload<>0){return ;}
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$this->EnableArticaAsGateway=$sock->GET_INFO("EnableArticaAsGateway");
		$ini->loadString($sock->GET_INFO('ArticaDHCPSettings'));
		$this->ddns_domainname=$ini->_params["SET"]["ddns_domainname"];
		$this->max_lease_time=$ini->_params["SET"]["max_lease_time"];
		$this->listen_nic=$ini->_params["SET"]["listen_nic"];
		$this->netmask=$ini->_params["SET"]["netmask"];
		$this->range1=$ini->_params["SET"]["range1"];
		$this->range2=$ini->_params["SET"]["range2"];
		$this->EnableArticaAsDNSFirst=trim($ini->_params["SET"]["EnableArticaAsDNSFirst"]);
		$this->gateway=$ini->_params["SET"]["gateway"];
		$this->DNS_1=$ini->_params["SET"]["DNS_1"];
		$this->DNS_2=$ini->_params["SET"]["DNS_2"];
		$this->pxe_server=$ini->_params["SET"]["pxe_server"];
		$this->pxe_file=$ini->_params["SET"]["pxe_file"];
		$this->pxe_enable=$ini->_params["SET"]["pxe_enable"];
	
		
		$ip=new networking();
		$ip->LoadIPs();
		$this->array_tcp=$ip->Local_interfaces();
		if($this->pxe_file==null){$this->pxe_file="/tftpboot/pxelinux.0";}
		if($this->max_lease_time==null){$this->max_lease_time=3600;}
		if($this->netmask==null){$this->netmask="255.255.255.0";}
		if($this->range1==null){$this->range1="192.168.1.55";}
		if($this->range2==null){$this->range2="192.168.1.240";}
		$this->LoadRoutes();
		$this->RemoteVPN();
		$this->conf=$this->BuildConf();
	}
	
	function Save(){
		$ini=$ini."[SET]\n";
		$ini=$ini."ddns_domainname=$this->ddns_domainname\n";
		$ini=$ini."max_lease_time=$this->max_lease_time\n";
		$ini=$ini."listen_nic=$this->listen_nic\n";
		$ini=$ini."netmask=$this->netmask\n";
		$ini=$ini."range1=$this->range1\n";
		$ini=$ini."range2=$this->range2\n";
		$ini=$ini."gateway=$this->gateway\n";
		$ini=$ini."DNS_1=$this->DNS_1\n";
		$ini=$ini."DNS_2=$this->DNS_2\n";
		$ini=$ini."pxe_server=$this->pxe_server\n";
		$ini=$ini."pxe_file=$this->pxe_file\n";
		$ini=$ini."pxe_enable=$this->pxe_enable\n";
		$ini=$ini."EnableArticaAsDNSFirst=$this->EnableArticaAsDNSFirst\n";
		
		
		$sock=new sockets();
		$sock->SaveConfigFile($ini,'ArticaDHCPSettings');
		$sock->SaveConfigFile($this->BuildConf(),'DHCP3ConfigurationFile');
		$sock->SET_INFO('DHCP3ListenNIC',$this->listen_nic);
		if($this->EnableArticaAsDNSFirst==1){$sock->getfile("BackGroundBIND9");}
		$sock->getFrameWork('cmd.php?restart-dhcpd=yes');
		
		
		
	}
	
function DeleteFixed($COMPUTER_NAME){
	$dhcp=new dhcpd_hosts($COMPUTER_NAME);
	return $dhcp->DeleteFixed();
	
}

function AddFixed($COMPUTER_NAME,$MAC,$IP){
	$dhcp=new dhcpd_hosts($COMPUTER_NAME);
	$dhcp->MAC=$MAC;
	$dhcp->IP=$IP;
	return $dhcp->AddFixed();
	
}

function AddRoute($ip,$netmask,$gateway,$info=null){
	$this->LoadRoutes();
	if(!is_array($this->routes[$ip])){
		$this->routes[$ip]=array($netmask,$gateway,$info);
		$this->SaveRoutes();
		$this->Save();
	}
}

function DelRoute($ip){
	$this->LoadRoutes();
	while (list ($ipr, $array) = each ($this->routes) ){
		if($ipr==$ip){
			writelogs("Delete Gateway $ip (".$this->routes[$ip][0].")",__CLASS__.'/'.__FUNCTION__,__FILE__);
			continue;
		}
		$arrayF[]="$ipr;{$array[0]};{$array[1]}";
	}
	
	if(is_array($arrayF)){$conf=implode("\n",$arrayF);}
	$sock=new sockets();
	$sock->SaveConfigFile($conf,"DHCPDRoutesInfos");
}

private function LoadRoutes(){
	$sock=new sockets();
	$datas=explode("\n",$sock->GET_INFO("DHCPDRoutesInfos"));
	while (list ($num, $ligne) = each ($datas) ){
		$tbl=explode(";",$ligne);
		if(count($tbl)>1){
			$this->routes[$tbl[0]]=array($tbl[1],$tbl[2]);
		}
		
	}
}

private function SaveRoutes(){
	if(is_array($this->routes)){
	reset($this->routes);
	$conf=null;
	while (list ($ip, $array) = each ($this->routes) ){
		$ll="$ip;{$array[0]};{$array[1]};{$array[2]}";
		writelogs("$ll",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$arrayF[]="$ip;{$array[0]};{$array[1]};{$array[2]}";
	}
		if(is_array($arrayF)){
			$conf=implode("\n",$arrayF);
		}
	}
	
	
	
	$sock=new sockets();
	$sock->SaveConfigFile($conf,"DHCPDRoutesInfos");
}


private function GetMyDNS(){
		$ip=new networking();
		if($this->listen_nic==null){return null;}
		$mip=$ip->GetNicInfos($this->listen_nic);	
		$eth_IP=$mip["IPADDR"];
		if($eth_IP=="0.0.0.0"){$eth_IP=$ip->default_network();}
	
	
		$arrayNameServers=array();
		if(is_array($ip->arrayNameServers)){
			while (list ($index, $ipaddr) = each ($ip->arrayNameServers) ){
				if($ipaddr=="0.0.0.0"){continue;}
				$arrayNameServers_tmp[$ipaddr]=$ipaddr;
			}


			
			
			while (list ($index, $ipaddr) = each ($arrayNameServers_tmp) ){
				if($ipaddr=="0.0.0.0"){continue;}
				$arrayNameServers[]=$ipaddr;
			}			
			
		}	
	
		if($this->DNS_2<>null){array_unshift($arrayNameServers,$this->DNS_2);}
		if($this->DNS_1<>null){array_unshift($arrayNameServers,$this->DNS_1);}
		if($this->EnableArticaAsDNSFirst==1){array_unshift($arrayNameServers,$eth_IP);}
		return $arrayNameServers;
	
}
	
	
	function BuildConf(){
		$ip=new networking();
		$users=new usersMenus();
		$users->LoadModulesEnabled();
		if($this->listen_nic==null){return null;}
		$mip=$ip->GetNicInfos($this->listen_nic);	
		$eth_gateway=$mip["GATEWAY"];
		$eth_IP=$mip["IPADDR"];
		

		$conf=$conf."ddns-domainname \"$this->ddns_domainname\";\n";
		if($users->BIND9_INSTALLED){
			$sock=new sockets();
			$Bind9GlobalKey=$sock->GET_INFO('Bind9GlobalKey');
			$conf=$conf."\n";
			$conf=$conf.  "key globalkey {\n";
			$conf=$conf.  "\talgorithm hmac-md5;\n";
			$conf=$conf.  "\tsecret \"$Bind9GlobalKey\";\n";
			$conf=$conf.  "};\n";
			$conf=$conf."\n";
			
			$conf=$conf.  "ddns-update-style interim;\n";
			$conf=$conf . "update-static-leases on;\n";
			$conf=$conf . "do-forward-updates on;\n";
			$conf=$conf . "ddns-updates on;\n"; 
			$conf=$conf . "ignore client-updates;\n";
			$conf=$conf . "update-static-leases on;\n";
		}else{
			$conf=$conf."ddns-update-style none;\n";
		}
		
		$conf=$conf."allow unknown-clients;\n";
		$conf=$conf."authoritative;\n";
		$conf=$conf."max-lease-time $this->max_lease_time;\n";
		$conf=$conf."default-lease-time $this->max_lease_time;\n";
		$conf=$conf."option domain-name \"$this->ddns_domainname\";\n";
		
		if($this->gateway<>null){
			$gateway_array[]=$this->gateway;
		}
		
		if($this->EnableArticaAsGateway==1){
			$gateway_array[]=$eth_IP;
		}
		
		$routes=$this->BuildRoutes();
		if(count($routes["ROUTERS"])>0){
			while (list ($index, $router) = each ($routes["ROUTERS"]) ){
			$gateway_array[]=$router;
			}
			$conf=$conf."option ms-classless-static-routes code 249 = array of unsigned integer 8;\n";
			$conf=$conf."option rfc3442-classless-static-routes code 121 = array of unsigned integer 8;\n";
			$conf=$conf."option ms-classless-static-routes ". implode(" ,",$routes["COMPILE"]).";\n";
			$conf=$conf."option rfc3442-classless-static-routes ". implode(" ,",$routes["COMPILE"]).";\n";
		}
		
		
		if(is_array($gateway_array)){	
				$conf=$conf."option routers ". implode(",",$gateway_array). ";\n";
		}	
		
		$arrayNameServers=$this->GetMyDNS();
		
		if(is_array($ip->arrayNameServers)){
			$conf=$conf ."option domain-name-servers ". implode(",",$arrayNameServers).";\n";
		}
			
		
		if(!preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)#',$this->range1,$re)){
			echo "Wrong range!\n";
			return null;
		}
		
		$conf=$conf."\n";
		$conf=$conf."subnet {$re[1]}.{$re[2]}.{$re[3]}.0 netmask $this->netmask {\n";
		$conf=$conf."\trange $this->range1 $this->range2;\n";
		$conf=$conf."\toption broadcast-address {$re[1]}.{$re[2]}.{$re[3]}.255;\n";
		if($this->pxe_enable==1){
			if($this->pxe_file<>null){
				$conf=$conf."\tfilename \"$this->pxe_file\";\n";
				if($this->pxe_server<>null){
					$conf=$conf."\tnext-server $this->pxe_server;\n";
				}
			}
		}
		
		
		if($users->BIND9_INSTALLED){
			
			$conf=$conf."\tddns-domainname \"$this->ddns_domainname\";\n";
			$conf=$conf."\tddns-rev-domainname \"in-addr.arpa\";\n\n";
			$conf=$conf."\tzone $this->ddns_domainname. {\n";
			$conf=$conf."\t\tprimary 127.0.0.1;\n";
			$conf=$conf."\t\tkey globalkey;\n";
			$conf=$conf."\t}\n";
			$conf=$conf."\n";
			$conf=$conf."\tzone {$re[3]}.{$re[2]}.{$re[1]}.in-addr.arpa. {\n";
			$conf=$conf."\t\tprimary 127.0.0.1;\n";
			$conf=$conf."\t\tkey globalkey;\n";
			$conf=$conf."\t}\n";	
			$this->SetBind9Zone();
			
			
		}else{
			$conf=$conf."\tddns-updates off;\n"; 
			
		}
		$conf=$conf."}\n\n";
		$conf=$conf.$this->SetFixedAddresses();
		
		return $conf;
		
	}
	
	
	function LoadfixedAddresses(){
		$ldap=new clladp();
		$IP=new IP();	
		$filter=array("computerip","ComputerMacAddress");
		$query="(&(objectClass=ArticaComputerInfos)(DnsZoneName=$this->ddns_domainname))";
		$hash=$ldap->Ldap_search($ldap->suffix,$query,$filter);
		writelogs("compile $count computers for $this->ddns_domainname",__CLASS__.'/'.__FUNCTION__,__FILE__);
		for($i=0;$i<$count;$i++){
			$uid=str_replace('$','',$hash[$i]["uid"][0]);
			$ip_addr=$hash[$i]["computerip"][0];
			$ComputerMacAddress=$hash[$i][strtolower("ComputerMacAddress")][0];
			if(!$IP->isIPAddress($ip_addr)){continue;}
			if($ComputerMacAddress==null){continue;}
			$ARR[$uid]=array("MAC"=>"hardware ethernet $ComputerMacAddress","IP"=>"fixed-address $ip_addr");
		}
		
	$query="(&(objectClass=dhcpHost)(cn=*))";
	$dn="cn=DhcpConfig,ou=dhcp,$ldap->suffix";
	$filter=array();
	
	$hash=$ldap->Ldap_search($dn,$query,$filter);
	$count=$hash["count"];	
	for($i=0;$i<$count;$i++){
		$uid=$hash[$i]["cn"][0];	
		$ARR[$uid]=array("MAC"=>"hardware ".$hash[$i][strtolower("dhcpHWAddress")][0],"IP"=>$hash[$i][strtolower("dhcpStatements")][0]);
	}
	
	return $ARR;
	
	}
	
	
	private function SetFixedAddresses(){
		$array=$this->LoadfixedAddresses();
		if(!is_array($array)){return null;}
		while (list ($num, $ligne) = each ($array) ){
				if($num==null){continue;}
				$conf=$conf ."\thost $num{\n";
				$conf=$conf ."\t\t{$ligne["MAC"]};\n";
				$conf=$conf ."\t\t{$ligne["IP"]};\n";
				$conf=$conf ."\t}\n";
		
			}
	
		return $conf;
		
	}
	
	
	function SetBind9Zone(){
	
		$bind=new Bind9Zone($this->ddns_domainname);
		$bind->SaveBind9Zone($this->range1);

	}
	
	
	function GetKey($domain){
		$sock=new sockets();
		$md=md5($domain);
		$datas=$sock->GET_INFO("Bind9DnsKey.$md.key");
		if($datas==null){
			$key=$sock->getfile("Bind9Key:$domain");
			$conf=$conf."[KEY]\n";
			$conf=$conf."";
		}
		
		
	}
	
	private function RemoteVPN(){
			$ip=new networking();
			$array=$ip->array_TCP;
			
			$gateway=$array[$this->listen_nic];
			$sql="SELECT IP_START,netmask,sitename  FROM vpnclient WHERE connexion_type=1 ORDER BY sitename DESC";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"artica_backup");
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
				writelogs("find route on vpn=>{$ligne["IP_START"]}=>{$ligne["netmask"]}=>$gateway",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$this->AddRoute($ligne["IP_START"],$ligne["netmask"],$gateway,"VPN ".$ligne["sitename"]);
			}
	}
	
	private function BuildRoutes(){
		if(!is_array($this->routes)){return array();}
		$datas=$this->routes;
		while (list ($ipaddr, $infos) = each ($datas) ){
			if(!preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#",$ipaddr,$re)){continue;}
			unset($re[0]);
			if($re[4]==0){unset($re[4]);}
			if($re[3]==0){unset($re[3]);}
			if($re[2]==0){unset($re[2]);}
			$ip=new networking();
			$cdir=$ip->route_shouldbe($ipaddr,$infos[0]);
			if($cdir==null){continue;}
			if(preg_match("#\/([0-9]+)#",$cdir,$cdr)){$cdir=$cdr[1];}
			if(!preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#",$infos[1],$router)){continue;}
			unset($router[0]);
			$line1[]="$cdir, ". implode(",",$re).", " .implode(",",$router);
			$routers[]=$infos[1];
			
		}
		
		return array("COMPILE"=>$line1,"ROUTERS"=>$routers);
	}
	
	
}

?>