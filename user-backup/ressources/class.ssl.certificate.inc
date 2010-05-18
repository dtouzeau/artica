<?php
include_once(dirname(__FILE__) . "/class.ini.inc");
if(!is_file("class.ldap.inc")){include_once(dirname(__FILE__) . '/class.ldap.inc');}else{include_once('class.ldap.inc');}
class ssl_certificate{
	
	var $default_file;
	var $array_ssl=array();
	var $ini_sources;
	var $array_country_codes;
	var $ldap_last_error;
	
	
	function ssl_certificate(){
		$this->default_file=dirname(__FILE__) . '/databases/DEFAULT-CERTIFICATE-DB.txt';
		$this->LoadCountryCodes();
		$this->LoadSources();
		unset($_SESSION["CACHEINFOS_PostfixSSLCert"]);
		}
	
	
	function LoadCountryCodes(){
		$db=file_get_contents(dirname(__FILE__) . '/databases/ISO-3166-Codes-Countries.txt');
		$tbl=explode("\n",$db);
		while (list ($num, $ligne) = each ($tbl) ){
			if(preg_match('#(.+?);\s+([A-Z]{1,2})#',$ligne,$regs)){
				$regs[2]=trim($regs[2]);
				$regs[1]=trim($regs[1]);
				
				$this->array_country_codes["{$regs[1]}_{$regs[2]}"]=$regs[1];
			}
			
		}
	}
	
	function LoadSources(){
		$sock=new sockets();
		$this->ini_sources=$sock->GET_INFO('PostfixSSLCert');
		writelogs("PostfixSSLCert=".strlen($this->ini_sources),__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		if(trim($this->ini_sources)==null){
			$this->ini_sources=$sock->getfile("CertificateConfigFile");
		}
		$ini=new Bs_IniHandler();
		if($this->ini_sources==null){
			$ini->loadFile($this->default_file);
		}else{
			writelogs("loading ini_sources",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$ini->loadString($this->ini_sources);
			}
		
		
		$this->array_ssl=$ini->_params;
		if($this->array_ssl["default_db"]["dir"]==null){$this->array_ssl["default_db"]["dir"]='/etc/postfix/certificates';}
		if($this->array_ssl["postfix"]["smtpd_tls_key_file"]==null){$this->array_ssl["postfix"]["smtpd_tls_key_file"]='smtpd.key';}
		if($this->array_ssl["postfix"]["smtpd_tls_cert_file"]==null){$this->array_ssl["postfix"]["smtpd_tls_cert_file"]='smtpd.crt';}
		if($this->array_ssl["postfix"]["smtpd_tls_CAfile"]==null){$this->array_ssl["postfix"]["smtpd_tls_CAfile"]='cacert.pem';}
		}
		
	function SaveToLdap(){
			$ini=new Bs_IniHandler();
			$ini->_params=$this->array_ssl;
			$datas=$ini->toString();
			$sock=new sockets();
			unset($_SESSION["CACHEINFOS_PostfixSSLCert"]);
			$sock->SaveConfigFile($datas,'PostfixSSLCert');
			$sock->getFrameWork('cmd.php?ChangeSSLCertificate=yes');
			return true;
			}
			
	function SaveToDisk(){
			$ini=new Bs_IniHandler();
			$ini->_params=$this->array_ssl;
			$sock=new sockets();
			unset($_SESSION["CACHEINFOS_PostfixSSLCert"]);
			$ret=$sock->getFrameWork("cmd.php?GenerateCert=yes&path=$tempfile");
			
			return "ok";
			
	}
	
	
	
}