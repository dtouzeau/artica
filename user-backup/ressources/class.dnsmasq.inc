<?php
if(!is_file("class.ldap.inc")){include_once(dirname(__FILE__) . '/class.ldap.inc');}else{include_once('class.ldap.inc');}
include_once(dirname(__FILE__).'/class.sockets.inc');

class dnsmasq{
	var $configuration_file_datas;
	var $main_array;
	var $array_address;
	var $array_interface;
	var $array_listenaddress;
	var $array_mxhost;

	function dnsmasq(){
		
		$ldap=new clladp();
		$hash=$ldap->dnsmasqSettings();
		$this->configuration_file_datas=$hash["DnsMasqConfigurationFile"];
		if(trim($this->configuration_file_datas)==null){
			$sock=new sockets();
			$this->configuration_file_datas=$sock->getfile('dnsmasq.conf');
		}
		$this->ParseConf();
		$this->ParseAddress();
		$this->ParseListenAddress();
		$this->ParseInterfaces();
		$this->Parsemxhost();
		$this->Mainunset();
	}
	
	function ParseConf(){
		$tpl=explode("\n",$this->configuration_file_datas);
		if(!is_array($tpl)){return null;}
		while (list ($index, $line) = each ($tpl) ){
			if(preg_match('#^([a-zA-Z\-\_]+)=(.+)#',$line,$regs)){
				$this->main_array[$regs[1]]=$regs[2];
			}else{
				if(preg_match('#^([a-zA-Z\-\_]+)#',$line,$regs)){
					$this->main_array[$regs[1]]="justkey";
				}
			}
			
		}
		

	}
	function Mainunset(){
		unset($this->main_array["mxrecdomainfrom"]);
		unset($this->main_array["mxrecdomainto"]);
		unset($this->main_array["mxheight"]);
		unset($this->main_array["address"]);
		unset($this->main_array["listen-address"]);
		unset($this->main_array["interface"]);
		unset($this->main_array["mx-host"]);	
	}
	
	function ParseAddress(){
		$tpl=explode("\n",$this->configuration_file_datas);
		if(!is_array($tpl)){return null;}
		$filter='#^address=/(.+)?/(.+)?#';
		while (list ($index, $line) = each ($tpl) ){
			if(preg_match($filter,$line,$regs)){
				$this->array_address[$regs[1]]=$regs[2];
			}
			
		}
		
	}
	
	function Parsemxhost(){
	$tpl=explode("\n",$this->configuration_file_datas);
		if(!is_array($tpl)){return null;}
		$filter='#^mx-host=(.+)?#';
		while (list ($index, $line) = each ($tpl) ){
			if(preg_match($filter,$line,$regs)){
				$list=$regs[1];
				$this->array_mxhost[]=$regs[1];	
				}
			}
		}			

	
	function ParseListenAddress(){
		$tpl=explode("\n",$this->configuration_file_datas);
		if(!is_array($tpl)){return null;}
		$filter='#^listen-address=(.+)?#';
		while (list ($index, $line) = each ($tpl) ){
			if(preg_match($filter,$line,$regs)){
				$list=$regs[1];
				if(strpos($list,",")>0){
					$this->array_listenaddress=explode(',',$list);
				}else{$this->array_listenaddress[]=$list;}
			}
		}	
	}
	function ParseInterfaces(){
		$tpl=explode("\n",$this->configuration_file_datas);
		if(!is_array($tpl)){return null;}
		$filter='#^interface=(.+)?#';
		while (list ($index, $line) = each ($tpl) ){
			if(preg_match($filter,$line,$regs)){
				$this->array_interface[]=$regs[1];
			}
			
		}
	}

	
	function SaveConf(){
		while (list ($key, $value) = each ($this->main_array) ){
			if($value<>'nokey'){
				if($value=='justkey'){
					$conf=$conf . "$key\n";
				}else{
					$conf=$conf . "$key=$value\n";
				}
				
			}
			
		}
		
		//listen-address ---------------------------------------------------------------
		if(is_array($this->array_listenaddress)){
				$conf=$conf . "listen-address=" . implode(",",$this->array_listenaddress) . "\n";
			}
		
		//interfaces ---------------------------------------------------------------
		if(is_array($this->array_interface)){
		while (list ($index, $value) = each ($this->array_interface) ){
			if($value<>null){
				$conf=$conf . "interface=$value\n";
			}
		}}		
		//address ---------------------------------------------------------------
		if(is_array($this->array_address)){
		while (list ($index, $value) = each ($this->array_address) ){
			if($value<>null){
				$conf=$conf . "address=/$index/$value\n";
			}
		}}
		//mx-host ---------------------------------------------------------------
		if(is_array($this->array_mxhost)){
			
		while (list ($index, $value) = each ($this->array_mxhost) ){
			//strip duplicates entries
			if($value<>null){$array[$value]=$value;}
		}
		while (list ($index, $value) = each ($array) ){	
			
			if($value<>null){
				$conf=$conf . "mx-host=$value\n";
			}
		}}else{writelogs("mx-host : no array...",__CLASS__ . '/'. __FUNCTION__, __FILE__);	}
		//-----------------------------------------------------------------------------
		
		
		$upd[strtolower("DnsMasqConfigurationFile")][0]=$conf;
		$ldap=new clladp();
		$dn="cn=dnsmasq,cn=artica,$ldap->suffix";
		$tpl=new templates();
		if($ldap->Ldap_modify($dn,$upd)){
			echo $tpl->_ENGINE_parse_body('{success}');
			writelogs("Save configuration done...",__CLASS__ . '/'. __FUNCTION__, __FILE__);	
			return true;}
		echo $tpl->_ENGINE_parse_body('{failed}');return false;
		
	}
	
	function SaveConfToServer(){
		$conf=$this->configuration_file_datas;
		$tempfile=dirname(__FILE__) . '/conf/dnsmasq.conf';
		writelogs("writing $tempfile" ,__CLASS__.'/'.__FUNCTION__,__FILE__);
		$fp = fopen($tempfile, "w",0); #open for writing
		fputs($fp, $conf); #write all of $data to our opened file
		fclose($fp); #close the file
		
		//send command to server for apply config
		$sock=new sockets();
		$sock->getfile('SavednsmasqConfigurationFile' );
		
	}
	
	
	
	
	
	
	
}

?>