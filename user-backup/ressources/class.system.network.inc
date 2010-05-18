<?php
include_once(dirname(__FILE__) . '/class.sockets.inc');
include_once(dirname(__FILE__) . '/class.tcpip.inc');
include_once(dirname(__FILE__) . '/class.ini.inc');
include_once(dirname(__FILE__) . '/class.dnsmasq.inc');




class networking{
	var $array_conf;
	var $array_TCP;
	var $array_local_aliases;
	var $ldap_error;
	var $arrayNameServers;
	var $hostname;
	
	var $tcp_addr="0.0.0.0";
	var $wireless=false;
	var $netmask="0.0.0.0";
	var $gateway="0.0.0.0";
	var $linkup=false;
	
	function networking(){
		
			if(isset($GLOBALS["__CLASS-networking"])){
			foreach (get_object_vars($GLOBALS["__CLASS-networking"]) as $key => $value){
  				$this->$key = $value;
				}
				return null;
			}		
		
		
		$this->LoadNameServers();
		$this->LoadIPs();
		
		$GLOBALS["__CLASS-networking"]=$this;
		writelogs("Loading success",__CLASS__.'/'.__FUNCTION__,__FILE__);
	}
	
  public function Local_interfaces() {
    $results = array();
    if ($this->rfts('/proc/net/dev', $bufr)) {
      $bufe = explode("\n", $bufr);
      foreach($bufe as $buf) {
        if (preg_match('/:/', $buf)) {
          list($dev_name, $stats_list) = preg_split('/:/', $buf, 2);
          $stats = preg_split('/\s+/', trim($stats_list));
          $results[$dev_name] = $dev_name;
        }
      }
    }
    return $results;
  }	
  
	 private function rfts($strFileName, &$strRet, $intLines = 0, $intBytes = 4096, $booErrorRep = true) {
	  $error = "";
	  $strFile = "";
	  $intCurLine = 1;
	  if (is_file($strFileName)) {
	    if ($fd = fopen($strFileName, 'r')) {
	      while (!feof($fd)) {
	        $strFile.= fgets($fd, $intBytes);
	        if ($intLines <= $intCurLine && $intLines != 0) {
	          break;
	        } else {
	          $intCurLine++;
	        }
	      }
	      fclose($fd);
	      $strRet = $strFile;
	    } else {
	      if ($booErrorRep) {
	        writelogs('fopen(' . $strFileName . ') file can not read',__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	      }
	      return false;
	    }
	  } else {
	    if ($booErrorRep) {
	      writelogs('file_exists(' . $strFileName . ') the file does not exist on your machine',__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	    }
	    return false;
	  }
	  return true;
	}  
	

	
	function ALL_IPS_GET_ARRAY(){
		if(is_array($GLOBALS["ALL_IPS_GET_ARRAY"])){return $GLOBALS["ALL_IPS_GET_ARRAY"];}
		$sock=new sockets();
		$array=explode("\n",$sock->getfile("allips"));
		if(is_array($array)){
			while (list ($index, $line) = each ($array) ){
				if(trim($index)==null){continue;}
				$ARRAY_IP[trim($line)]=trim($line);
			}
		}
		$GLOBALS["ALL_IPS_GET_ARRAY"]=$ARRAY_IP;
		return $ARRAY_IP;
	}
	
	
	function LoadIPs(){
		
		$users=new usersMenus();
		$sock=new sockets();
		$this->array_TCP=$users->TCP_ARRAY;
		$this->hostname=trim($sock->getfile('myhostname'));
		
	}
	
	function SaveResolvconf($targetFile){
		if($targetFile=='/etc/resolv.conf'){return null;}
		$sock=new sockets();
		$sock->getfile('copyresolv:'.$targetFile);
		
	}
	
	function LoadNameServers(){
		$sock=new sockets();
		$dns=$sock->getFrameWork('cmd.php?dnslist=yes');
		$dnslist=explode(";",$dns);
		$ip=new IP();
		if(is_array($dnslist)){
			while (list ($num, $val) = each ($dnslist) ){
				$val=trim(strtolower($val));
				if($val<>null){
					//Check if is a valid tcp/ip address.
					if($ip->isValid($val)){
						$arr[$val]=trim($val);
					}
					
				}}
			}
			
						
	    if(is_array($arr)){
			while (list ($num, $val) = each ($arr) ){
				if($ip->isValid($val)){
				$this->arrayNameServers[]=$val;
				}
			}
	   	}
		}
	
	
	function ifconfig($nic){
		$sock=new sockets();
		$nicinfos=$sock->getfile("nicstatus:$nic");		
		$tbl=explode(";",$nicinfos);
		$this->tcp_addr=$tbl[0];
		$this->mac_addr=$tbl[1];
		$this->netmask=$tbl[2];
		$this->gateway=$tbl[4];
		if(trim($tbl[5])=='yes'){$this->wireless=true;}else{$this->wireless=false;}
		if(trim($tbl[6])=='yes'){$this->linkup=true;}else{$this->linkup=false;}		
		
		}
		
		function BuildResolvConf(){
			$users=new usersMenus();
			reset($this->arrayNameServers);
			
			if($users->BIND9_INSTALLED){
				writelogs("bind9 is installed...",__CLASS__.'/'.__FUNCTION__,__FILE__);
				include_once(dirname(__FILE)."/class.bind9.inc");
				$bind=new bind9();
				while (list ($index, $ligne) = each ($this->arrayNameServers) ){
					$bind->AddNewForwarder($ligne);
				}
			}			
			
			reset($this->arrayNameServers);
			$ip=new IP();
			while (list ($index, $ligne) = each ($this->arrayNameServers) ){
				$ligne=trim($ligne);
				if(!$ip->isValid($ligne)){continue;}
				$resolv=$resolv."nameserver $ligne\n";
			}
			
			if($users->BIND9_INSTALLED){
				$resolv="nameserver 127.0.0.1\n$resolv";
			}else{
				if($users->dnsmasq_installed){
					$resolv="nameserver 127.0.0.1\n$resolv";
				}
			}
			
			writeToFile($resolv,dirname(__FILE__).'/conf/resolv.conf');
			$sock=new sockets();
			$sock->getfile('SaveResolve:'.dirname(__FILE__).'/conf/resolv.conf;/etc/resolv.conf');
			if($users->dnsmasq_installed){
				$dnsmasq=new dnsmasq();
				if($dnsmasq->main_array["no-resolv"]=='justkey'){
					$dnsfile="/etc/resolv.conf";
					}else{
						$dnsfile=$dnsmasq->main_array["resolv-file"];
					}
					if($dnsfile==null){$dnsfile="/etc/resolv.conf";}
					$this->SaveResolvconf($dnsfile);
				}
			}
		
	function nameserver_add($name){
		$ip=new IP();
		if(!$ip->isValid($name)){
			writelogs("$name is not a valid ip address",__CLASS__.'/'.__FUNCTION__,__FILE__);
			echo "No a valid ip address!\n";
			return;
		}
		reset($this->arrayNameServers);
		while (list ($index, $ligne) = each ($this->arrayNameServers) ){
			if(trim(strtolower($name))==trim(strtolower($ligne))){
				writelogs("$name already exists in dns list, aboring",__CLASS__.'/'.__FUNCTION__,__FILE__);
				return;
			}
		}
		reset($this->arrayNameServers);
		$this->arrayNameServers[]=$name;
		$this->BuildResolvConf();
		}
		
	function nameserver_delete($name){
		$users=new usersMenus();
		while (list ($index, $ligne) = each ($this->arrayNameServers) ){
			if(trim(strtolower($name))==trim(strtolower($ligne))){
				unset($this->arrayNameServers[$index]);
				break;
			}
		}
		reset($this->arrayNameServers);
		if($users->BIND9_INSTALLED){
				include_once(dirname(__FILE__)."/class.bind9.inc");
				writelogs("BIND9 is installed update it for delete $name",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$bind=new bind9();
				$bind->DeleteForwarder($name);
				
		}
		$this->BuildResolvConf();
	}
		
	
	
	function GetNicInfos($nic){
			$sock=new sockets();
			$datas=$sock->getfile("nicinfos:$nic");
			$tbl=explode("\n",$datas);
			if(!is_array($tbl)){return array();}
			while (list ($index, $ligne) = each ($tbl) ){
				if(preg_match('#([a-zA-Z0-9\-\_]+)=(.+)#',$ligne,$reg)){
					$array[$reg[1]]=$reg[2];
				}
				
			}
			
		return $this->GetInfosReturnDefault($array,$nic);
		}
		
	function default_network(){
		if($this->array_TCP){
		while (list ($nic, $ip) = each ($this->array_TCP) ){
			if(preg_match("#^vmnet#",$nic)){continue;}
			if($ip==null){continue;}
			if($ip=="0.0.0.0"){continue;}
			return $ip;
		}}
		
	}
	
	function GetInfosReturnDefault($array,$nic=null){
	
		if($nic<>null){
			$sock=new sockets();
			$nicinfos=$sock->getfile("nicstatus:$nic");
			$tbl=explode(";",$nicinfos);
			$default_addr=$tbl[0];
			$default_netmask=$tbl[2];
			$default_gateway=$tbl[4];
		}
				
		if(trim($default_addr)==null){$default_addr="0.0.0.0";}
		if(trim($default_netmask)==null){$default_netmask="0.0.0.0";}
		if(trim($default_gateway)==null){$default_gateway="0.0.0.0";}
		
		if($array["ONBOOT"]==null){$array["ONBOOT"]="yes";}
		if($array["METRIC"]==null){$array["METRIC"]="10";}
		if($array["NEEDHOSTNAME"]==null){$array["NEEDHOSTNAME"]="yes";}	
		if($array["DHCP_CLIENT"]==null){$array["DHCP_CLIENT"]="dhclient";}	
		if($array["PEERDNS"]==null){$array["PEERDNS"]="yes";}	
		if($array["PEERYP"]==null){$array["PEERYP"]="no";}	
		if($array["PEERNTPD"]==null){$array["PEERNTPD"]="no";}		
		if($array["GATEWAY"]==null){$array["GATEWAY"]=$default_gateway;}
		if($array["IPADDR"]==null){$array["IPADDR"]=$default_addr;}
		if($array["NETMASK"]==null){$array["NETMASK"]=$default_netmask;}
		if($array["BOOTPROTO"]==null){$array["BOOTPROTO"]="dhcp";}
		return $array;		
		
	}
	
	
function route_shouldbe($ip_start,$mask=null){
	
	$ip=$ip_start;
	if(preg_match("#([0-9]+)$#",$ip,$re)){
		$calc_ip=$re[1].".0.0.0";
		$calc_ip_end=$re[1].".255.255.255";
	}
	
	if(preg_match("#([0-9]+)\.([0-9]+)$#",$ip,$re)){
		$calc_ip=$re[1].".{$re[2]}.0.0";
		$calc_ip_end=$re[1].".{$re[2]}.255.255";
	}
	
	if(preg_match("#([0-9]+)\.([0-9]+)\.$#",$ip,$re)){
		$calc_ip=$re[1].".{$re[2]}.0.0";
		$calc_ip_end=$re[1].".{$re[2]}.255.255";
	}	

	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)$#",$ip,$re)){
		$calc_ip=$re[1].".{$re[2]}.{$re[3]}.0";
		$calc_ip_end=$re[1].".{$re[2]}.{$re[3]}.255";
	}	
	
	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.$#",$ip,$re)){
		$calc_ip=$re[1].".{$re[2]}.{$re[3]}.0";
		$calc_ip_end=$re[1].".{$re[2]}.{$re[3]}.255";
	}	

	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$#",$ip,$re)){
		$calc_ip=$re[1].".{$re[2]}.{$re[3]}.0";
		$calc_ip_end=$re[1].".{$re[2]}.{$re[3]}.255";
	}		

	
	
	if($mask<>null){
		if(preg_match("#([0-9]+)$#",$mask,$re)){
			$newmask=$re[1].".0.0.0";
		}
	
	if(preg_match("#([0-9]+)\.([0-9]+)$#",$mask,$re)){
		$newmask=$re[1].".{$re[2]}.0.0";
	}
	
		if(preg_match("#([0-9]+)\.([0-9]+)\.$#",$mask,$re)){
		$newmask=$re[1].".{$re[2]}.0.0";
	}	

	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)$#",$mask,$re)){
		$newmask=$re[1].".{$re[2]}.{$re[3]}.0";
	}

	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.$#",$mask,$re)){
		$newmask=$re[1].".{$re[2]}.{$re[3]}.0";
	}		
		

	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$#",$mask,$re)){
		$newmask=$re[1].".{$re[2]}.{$re[3]}.{$re[4]}";
	}		
		
	}
	
	$ip=new IP();
	
	if($newmask<>null){
		return $ip->maskTocdir($calc_ip, $newmask);
	}
	
	
	
	$cdir=$ip->ip2cidr($calc_ip,$calc_ip_end);
	$arr=$ip->parseCIDR($cdir);
	$rang=$arr[0];
	$netbit=$arr[1];
	$ipv=new ipv4($calc_ip,$netbit);
	return "<strong>$cdir {$ipv->address()} - {$ipv->netmask()}</strong>"; 	
	
}

	

	
	
	
	
	
}


class ifconfig{
	
	var $main_array=array();
	
	function ifconfig(){
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$datas=$sock->getfile('netcardsinfo');
		$ini->loadString($datas);
		$this->main_array=$ini->_params;
		
		
	}
	
	
	
	
}




?>