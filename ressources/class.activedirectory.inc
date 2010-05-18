<?php

include_once(dirname(__FILE__).'/class.ldap.inc');
include_once(dirname(__FILE__).'/class.ini.inc');
include_once(dirname(__FILE__).'/class.user.inc');
include_once(dirname(__FILE__).'/class.cron.inc');

class wad{
	
	var $ou;
	var $dn;
	var $ldap_admin;
	var $anonymous=0;
	var $ldap_password;
	var $ldap_host;
	var $ldap_port=389;
	var $ldap_connection;
	var $ldapbind;
	var $suffix;	
	var $ldap_error;
	var $dn_ou;
	var $target_group;
	var $failed_number=0;
	var $success_number=0;
	var $log_file;
	var $sub_suffix;
	var $domain_filter;
	var $AccountLess3Carac=1;
	var $schedule="30mn";
	var $enable_schedule=0;
	
	function wad($ou){
		$this->ou=$ou;
		$this->verifyBranch();
		$this->LoadConfig();
		$this->log_file="/usr/share/artica-postfix/ressources/logs/web/ad-$this->ou.log";
		

		}
	
	
	private function LoadConfig(){
		$ldap=new clladp();
		$res=@ldap_read($ldap->ldap_connection,$this->dn,"(objectClass=AdLinker)",array());
		if($res){
			$hash=ldap_get_entries($ldap->ldap_connection,$res);
			$conf=$hash[0][strtolower('AdLinkerConf')][0];
			$ini=new Bs_IniHandler();
			$ini->loadString($conf);
			$this->ldap_admin=$ini->get("LDAP","ldap_admin");
			$this->ldap_password=$ini->get("LDAP","ldap_password");
			$this->suffix=$ini->get("LDAP","suffix");
			$this->ldap_host=$ini->get("LDAP","ldap_host");
			$this->ldap_port=$ini->get("LDAP","ldap_port");			
			$this->target_group=$ini->get("LDAP","target_group");
			$this->dn_ou=$ini->get("LDAP","dn_ou");
			$this->anonymous=$ini->get("LDAP","anonymous");
			$this->sub_suffix=$ini->get("LDAP","sub_suffix");
			$this->domain_filter=$ini->get("LDAP","domain_filter");
			$this->AccountLess3Carac=$ini->get("LDAP","AccountLess3Carac");
			$this->schedule=$ini->get("LDAP","schedule");
			$this->enable_schedule=$ini->get("LDAP","enable_schedule");
			if($this->AccountLess3Carac==null){$this->AccountLess3Carac=0;}
			if($this->enable_schedule==null){$this->enable_schedule=0;}
			if($this->schedule==null){$this->schedule="30mn";}
			
		}
	}	
		
	private	function verifyBranch(){
			if($this->ou==null){
				writelogs("Warning ou string is null !!!",__CLASS__.'/'.__FUNCTION__,__FILE__);
				return null;}
			$ldap=new clladp();
			$this->dn="cn=adlinker,ou=$this->ou,dc=organizations,$ldap->suffix";
			if(!$ldap->ExistsDN($this->dn)){
				writelogs("Create $this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$upd["objectClass"][]='top';
				$upd["objectClass"][]='AdLinker';
				$upd["cn"]="adlinker";
				$upd["AdLinkerConf"][]="DEFAULT";
				$ldap->ldap_add($this->dn,$upd);
			}
	
		}
		
		function SaveToLdap(){
			$conf=$conf ."[LDAP]\n";
			$conf=$conf ."ldap_admin=$this->ldap_admin\n";
			$conf=$conf ."ldap_password=$this->ldap_password\n";
			$conf=$conf ."suffix=$this->suffix\n";			
			$conf=$conf ."ldap_host=$this->ldap_host\n";						
			$conf=$conf ."ldap_port=$this->ldap_port\n";									
			$conf=$conf ."target_group=$this->target_group\n";
			$conf=$conf ."dn_ou=$this->dn_ou\n";
			$conf=$conf ."anonymous=$this->anonymous\n";
			$conf=$conf ."sub_suffix=$this->sub_suffix\n";
			$conf=$conf ."domain_filter=$this->domain_filter\n";
			$conf=$conf ."AccountLess3Carac=$this->AccountLess3Carac\n";
			$conf=$conf ."enable_schedule=$this->enable_schedule\n";
			$conf=$conf ."schedule=$this->schedule\n";
			$sock=new sockets();
			$ouenc=base64_encode($this->ou);
			if($this->enable_schedule==1){
				$cron=new cron_macros();
				$schedule=base64_encode($cron->cron_defined_macros[$this->schedule]);		
				$sock->getFrameWork("cmd.php?ad-import-schedule=yes&ou=$ouenc&schedule=$schedule");
				
			}else{
				$sock->getFrameWork("cmd.php?ad-import-remove-schedule=yes&ou=$ouenc");
			}
			
			writelogs("Save master configuration for '$this->dn'",__CLASS__.'/'.__FUNCTION__,__FILE__);
			
			
			
			
			$ldap=new clladp();
			$upd["AdLinkerConf"][]=$conf;
			$ldap->Ldap_modify($this->dn,$upd);
			}
			
			
		function TestingADConnection(){
			if(!$this->Connect()){return false;}
			
			return true;
			
			
		}
		
		function FindBranchs(){
		if(!$this->Connect()){echo 0;return false;}
					
			$attrs[0]="namingContexts";
			$attrs[1]="subschemaSubentry";
			$attrs[2]="altServer";
			$attrs[3]="supportedExtension";
			$attrs[4]="supportedControl";
			$attrs[5]="supportedSASLMechanisms";
			$attrs[6]="supportedLDAPVersion";
			$attrs[7]="currentTime";
			$attrs[8]="dsServiceName";
			$attrs[9]="defaultNamingContext";
			$attrs[10]="schemaNamingContext";
			$attrs[11]="configurationNamingContext";
			$attrs[12]="rootDomainNamingContext";
			$attrs[13]="supportedLDAPPolicies";
			$attrs[14]="highestCommittedUSN";
			$attrs[15]="dnsHostName";
			$attrs[16]="ldapServiceName";
			$attrs[17]="serverName";
			$attrs[18]="supportedCapabilities";
			$attrs[19]="changeLog";
			$attrs[20]="tlsAvailableCipherSuites";
			$attrs[21]="tlsImplementationVersion";
			$attrs[22]="supportedSASLMechanisms";
			$attrs[23]="dsaVersion";
			$attrs[24]="myAccessPoint";
			$attrs[25]="dseType";
			//$attrs[26]="+";
			//$attrs[27]="*";
			$sr = ldap_read($this->ldap_connection," ","(&(objectClass=*))",$attrs,0,0,0,3);
			
			if ($sr) {
				$hash=ldap_get_entries($this->ldap_connection,$sr);	
			}else{
				$errornumber= ldap_errno($this->ldap_connection);
				$error_text=ldap_err2str($this->ldap_connection);
				echo "$errornumber $error_text";
			}
			
			print_r($hash);
		}
		
	function TestAddport(){
		
		if($GLOBALS["CACHEINFOS_{$this->ldap_host}"][$time]){return true;}
		$time=date('h:i');
		unset($GLOBALS["CACHEINFOS_{$this->ldap_host}"]);
		$fp = @fsockopen($this->ldap_host, $this->ldap_port, $errno, $errstr, 1);
		
		if(!$fp){
			$this->ldap_error="{error} $errno \"$errstr\" $this->ldap_host:$this->ldap_port";
				write_syslog("Bind remote $this->ldap_host:$this->ldap_port   $errstr ($errno)",__CLASS__."=>" .__FUNCTION__,__FILE__);	
				$GLOBALS["CACHEINFOS_{$this->ldap_host}"][$time]=false;	
				return false;	
		}
		$GLOBALS["CACHEINFOS_{$this->ldap_host}"][$time]=true;
		return true;
		
	}		
		
		
		function Connect(){
				if(!$this->TestAddport()){return false;}
			
			
				$this->ldap_connection=@ldap_connect($this->ldap_host, $this->ldap_port ) ;
		 		if(!$this->ldap_connection){
		 			$errornumber= ldap_errno($this->ldap_connection);
					$error_text=ldap_err2str($this->ldap_connection);
		 			writelogs("ERROR ($this->ou) unable to connect to Active Directory server $this->ldap_host:$this->ldap_port",__CLASS__."=>" .__FUNCTION__,__FILE__);	
		 			$this->ldap_error="ErrConnect $errornumber {unable_to_connect} $error_text";
		 			return false;
		 		}

			ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3); // on passe le LDAP en version 3, necessaire pour travailler avec le AD
			ldap_set_option($this->ldap_connection, LDAP_OPT_REFERRALS, 0); 
			if($this->anonymous==1){
					$this->ldapbind=@ldap_bind($this->ldap_connection);	 
			}else{
				if(!preg_match("#cn=(.+)#",$this->ldap_admin)){
					$this->ldap_admin="cn=$this->ldap_admin,cn=Users,$this->suffix";
				}
				$this->ldapbind=@ldap_bind($this->ldap_connection, $this->ldap_admin, $this->ldap_password);
			}
		
		    
			if(!$this->ldapbind){
					
					$errornumber= ldap_errno($this->ldap_connection);
					$error_text=ldap_err2str($this->ldap_connection);
					
					writelogs("ERROR $this->ldapbind $errornumber ($error_text) unable to connect to Active Directory server with right username $this->ldap_admin",__CLASS__."=>" .__FUNCTION__,__FILE__);	
								
					switch (ldap_errno($this->ldap_connection)) {
								case 0x31:
									$error=$error . "<b>{bdu}</b>";
									break;
								case 0x32:
									$error=$error . "<b>{iar}</b>";
									break;
								case 81:
									$error=$error . "<b>{ucl}</b>";
									break;						
								default:
									$error=$error . "<div style='border:1px solid #CCCCCC;margin:3px;padding:3px'>
										<b>{cls}</b><br><strong>Err ($this->ou) $errornumber, $error_text </strong><br>
										<span style='font-size:11px;font-weight:bold'>{err_unkown} $this->ldap_admin &laquo;$this->ldap_host:$this->ldap_port&raquo;</span>
										</div>";
			 				}					
					
					//echo $error;
					$this->ldap_error=$error.$this->ldap_error;
					ldap_close($this->ldap_connection);
					return false;
			}			
			
		return true;	
			
		}
		
		function CountDeusers(){
			if(!$this->Connect()){
				writelogs("Connect failed",__CLASS__.'/'.__FUNCTION__,__FILE__);
				return false;
			};
			$filter[]="(objectclass=person)";		
			$suffix=$this->suffix;
			if($this->sub_suffix<>null){$suffix=$this->sub_suffix.",$suffix";}
			while (list ($num, $val) = each ($filter) ){
				$sr = @ldap_search($this->ldap_connection,$suffix,$val,array("dn"));
				if(!$sr){
					
					$errornumber= ldap_errno($this->ldap_connection);
					$error_text=ldap_err2str($this->ldap_connection);
					writelogs("$this->ldap_connection $val failed ($suffix) $errornumber $error_text",__CLASS__.'/'.__FUNCTION__,__FILE__);
					$this->ldap_error=$errornumber." ".$error_text;
					
					return false;
				}
				
				$hash=@ldap_get_entries($this->ldap_connection,$sr);
				if($hash["count"]==0){continue;}
				return $hash["count"];
			}
			
			return 0;
		}
		
		
		function table_ou(){
			$ld =$this->ldap_connection;
			$bind =$this->ldapbind;
			$suffix=$this->suffix;
			$res=array();
		    $arr=array("ou");
			$sr = @ldap_search($ld,$suffix,'(&(objectclass=organizationalUnit)(ou=*))',$arr);
			if ($sr) {
				$hash=ldap_get_entries($ld,$sr);
				while (list ($num, $ligne) = each ($hash) ){
					if($ligne["ou"][0]<>null){
						if(!preg_match('#dc=samba,#',$ligne["dn"])){
								$ou=$ligne["ou"][0];
								$pos=strpos($ou,',');
								if($pos>1){$ou=substr($ou,0,$pos-1);}
								if($ou<>"sysusers"){$res[$ou]=$ou;}
								}
							}
						}
					}
			writelogs("resturn array of " . count($res) . " values",__FUNCTION__,__FILE__);
			return $res;			
		}
		
		
		function Perform_import(){
			$ldap=new clladp();
			$domainz=$ldap->hash_get_domains_ou($this->ou);
			while (list ($num, $ligne) = each ($domainz) ){
				if(trim($ligne==null)){continue;}
				$ou_domains[]=$ligne;
			}
			$default_domain=$ou_domains[0];
			
			if(is_file($this->log_file)){
				writelogs("delete $this->log_file",__FUNCTION__,__FILE__);
				unlink($this->log_file);}
			
			$this->Adlogs("Connection to Active Directory server");
			if(!$this->Connect()){
				$this->Adlogs("Unable to connect to the specified server...");
				return false;}
			
			$this->Adlogs("Connected to Active Directory server");
			if($this->dn_ou=='Users'){
				$dn="cn=Users,$this->suffix";
			}else{
				$dn=$this->suffix;
			}
			
			if($this->sub_suffix<>null){
				$dn=$this->sub_suffix.",$dn";
			}

			$filter="(objectclass=person)";
			$this->Adlogs("path: $dn");
			$this->Adlogs("Filter: $filter");
			$arr=array("cn","mail","name","givenName","displayName","mailNickname","userPrincipalName","sAMAccountName","proxyaddresses");
			$this->Adlogs("Search the right query...");
			$this->Adlogs("try $filter");
			
			$sr =@ldap_search($this->ldap_connection,$dn,$filter,$arr);
			if ($sr) {
				$hash=ldap_get_entries($this->ldap_connection,$sr);
				$this->Adlogs("Found {$hash["count"]} users...");
			}else{
				$this->Adlogs($this->ldap_error);	
				return;		
			}
	
			if($hash["count"]==0){
				$this->Adlogs("No sufficient filters handle users in your active directory server");
				$this->Adlogs("use Artica Forum to create a ticket in order to find the right one.");
				return false;
			}
			
			$cld=new clladp();
			$DOMAINS=$cld->Hash_domains_table($this->ou);
			for($i=0;$i<$hash["count"];$i++){
				    $cn=$hash[$i]["cn"][0];
					
				    if($hash[$i]["mail"][0]==null){$hash[$i]["mail"][0]=$hash[$i][strtolower("userPrincipalName")][0];}
				    
				    
				    
				   	$mail=$hash[$i]["mail"][0];
				    $givenName=$hash[$i][strtolower("givenName")][0];
				    $displayName=$hash[$i][strtolower("displayName")][0];
				    $mailNickname=$hash[$i][strtolower("mailNickname")][0];
				    $sAMAccountName=$hash[$i][strtolower("sAMAccountName")][0];
					if($sAMAccountName==null){$sAMAccountName=$mailNickname;}
				    
				    if($this->domain_filter<>null){
				    	if(!preg_match("#.+?@$this->domain_filter#",$mail)){
				    		$this->Adlogs("SKIPPED $mail did not match $this->domain_filter");
				    		continue;
				    	}
				    }
				    unset($aliases);
				    
				    
				    
				    for($z=0;$z<$hash[$i]["proxyaddresses"]["count"];$z++){
				    	$proxyaddresses=strtolower($hash[$i]["proxyaddresses"][$z]);
				    	if(preg_match("#smtp:(.+?)#",$proxyaddresses,$rz)){
				    		 if($this->domain_filter<>null){if(!preg_match("#.+?@$this->domain_filter#",$mail)){continue;}}
				    		 $aliases[]=$rz[1];
				    	}else{
				    		if($this->domain_filter<>null){if(!preg_match("#.+?@$this->domain_filter#",$proxyaddresses)){continue;}}
				    		 $aliases[]=$proxyaddresses;
				    	}
				    	
				    }
				    
				    
				    $user=new user();
				    if($this->AccountLess3Carac==1){
				    	$user->DisableAccountLessThan4Caracters=true;
				    }else{
				    	$user->DisableAccountLessThan4Caracters=false;
				    }
				    
				    $mail=trim($mail);
				    if($mail==null){if(is_array($aliases)){$mail=$aliases[0];}}
					if($mail==null){if($this->domain_filter<>null){$mail="$sAMAccountName@$this->domain_filter";}}
					if($mail==null){if($this->default_domain<>null){$mail="$sAMAccountName@$default_domain";}}
					
					
				    
				    if(preg_match('#(.+?)@(.+)#',$mail,$re)){
				    	$user->domainname=$re[2];
				    	if($DOMAINS[$re[2]]==null){
				    		$cld->AddDomainTransport($this->ou,$user->domainname,$this->ldap_host,'25','relay',"no");
				    		$DOMAINS[$user->domainname]="cn";
				    	}
				    }
				    
				    
				    
				    $user->cn=$user->stripAccents($cn);
				    $user->mail=$mail;
				    $user->DisplayName=$user->stripAccents($displayName);
				    $user->uid=$user->stripAccents($sAMAccountName);
				    $user->givenName=$givenName;
				    $user->group_id=$this->target_group;
				    $user->ou=$this->ou;
				   	if(!$user->add_user()){
						$this->failed_number=$this->failed_number+1;
				   		$this->Adlogs("[$sAMAccountName]: Parsing \"$cn\" displayName=$displayName in group $user->group_id failed with error $user->ldap_error");
				   		continue;
				   	}				   		
					$this->success_number=$this->success_number+1;
				   	
				  
				   	if(is_array($aliases)){
				   		$user=new user($sAMAccountName);
				   		while (list ($num, $email) = each ($aliases) ){
				   			$this->Adlogs("[$sAMAccountName]: Adding new alias $email"); 
				   			$user->add_alias($email);
				   		}
				   	}
				   	
				   	$this->Adlogs("[$sAMAccountName]: Parsing \"$cn\" displayName=$displayName ($mail) in group $user->group_id SUCCESS");	
				   		
				   	
					
				}
				
			

	
		
		
		$this->BuildLdapErrors();
		return true;
			
			
			
		}
		
function BuildLdapErrors(){
	$errornumber= ldap_errno($this->ldap_connection);
	$error_text=ldap_err2str($this->ldap_connection);
	if($errornumber<>0){
		$this->ldap_error=$this->ldap_error."<br>Error $errornumber $error_text";
	}
	$this->Adlogs($this->ldap_error);
$this->ldap_error="
		<p><h4>{importation_completed}</h4><br>
		<ul><li><strong>{success}:$this->success_number {users}</li>
		<li><strong>{failed}:$this->failed_number {users}</li>
		</ul></p>
		<br>".$this->ParseAdlogs();		
	
}
		
function ParseAdlogs(){
	
	if(!is_file($this->log_file)){
		writelogs("Unable to stat $this->log_file",__FUNCTION__.'/'.__CLASS__,__FILE__);
		return null;
	}
	
	$datas=file_get_contents($this->log_file);
	$tpl=explode("\n",$datas);
	$tbl=array_reverse ($tpl, TRUE);		
		while (list ($num, $val) = each ($tbl) ){
			if(trim($val)<>null){
				$val=str_replace("SUCCESS","<span style='color:#005447'>{success}</span>",$val);
				$line=$line . "<div style='text-align:left'><code style='font-size:10px'>$val</code></div>";
			}
		}
		
	return "<div style='overflow:auto;border:1px solid #CCCCCC;background-color:white;font-weight:normal;padding:3px;'>$line</div>";
	
	
}
		
		
function Adlogs($text=null){
		if($GLOBALS["output"]){echo "$text\n";}
		if($file_source==null){$file_source= __FILE__ ;}
		writelogs($text,__CLASS__,__FILE__);
		$file_source=basename($file_source);
		@mkdir('/usr/share/artica-postfix/ressources/logs/web');
		$logFile=$this->log_file;
		if(!is_dir(dirname($logFile))){mkdir(dirname($logFile));}
   		if (is_file($logFile)) { 
   			$size=filesize($logFile);
		    	if($size>1000000){unlink($logFile);}
   		}
		$logFile=str_replace("//","/",$logFile);
		$f = @fopen($logFile, 'a');
		$date=date("Y-m-d H:i:s");
		@fwrite($f, "$date: $text\n");
		@fclose($f);
}		
		
		
		
}

?>