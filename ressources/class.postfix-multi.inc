<?php
include_once(dirname(__FILE__) . '/class.ldap.inc');
include_once(dirname(__FILE__) . '/class.mysql.inc');
include_once(dirname(__FILE__) . '/class.assp-multi.inc');
include_once(dirname(__FILE__) . '/class.system.network.inc');


class multi_config{
	var $ou=null;
	var $q;
	function multi_config($ou){
		$this->ou=$ou;
		$this->q=new mysql();
	}
	

	public function GET($key){
		$sql="SELECT `value` FROM postfix_multi WHERE `key`='$key' AND `ou`='$this->ou' LIMIT 0,1";
		$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));	
		return $ligne["value"];
	}
	
	
	public function SET_VALUE($key,$value){
		$sql="SELECT `ID` FROM postfix_multi WHERE `key`='$key' AND `ou`='$this->ou' LIMIT 0,1";
		$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));	
		if($ligne["ID"]==null){
			$sql="INSERT INTO postfix_multi  (`ou`,`key`,`value`) VALUES('$this->ou','$key','$value')";
		}else{
			$sql="UPDATE postfix_multi SET `value`='$value' WHERE ID={$ligne["ID"]}";
		}
		$this->q->QUERY_SQL($sql,"artica_backup");
		if(!$this->q->ok){
			echo "{$this->q->mysql_error}\nSET_VALUE($key,$value)";
		}
	}	
	
	
}


class main_multi{
	var $ou;
	var $instance;
	var $MAIN_ARRAY=array();
	var $DN;
	var $inet_interfaces=array();
	
	function main_multi($ou,$nostart=0){
		$this->ou=$ou;
		$ldap=new clladp();
		$this->DN="ou=$ou,dc=organizations,$ldap->suffix";
		
		$this->instance=str_replace(" ","-",$ou);
		if($nostart==0){
			$this->LoadDatas();
			$this->inet_interfaces_load();
			$this->mydestination();
			$this->transport_maps();
			$this->Aliases();
			$this->ArticaDefault();
			$this->mynetworks();
			$this->MyHostname_SET();
			$this->smtpd_client_restrictions_set();
			$this->postfix_allowed_connections();
			$this->SaveConf();
			}
		}
		
	private function LoadDatas(){
		$datas=explode("\n",@file_get_contents("/etc/postfix-$this->instance/main.cf"));
		while (list ($num, $line) = each ($datas) ){
			if(substr($line,0,1)=="#"){continue;}
			if(preg_match("#(.+?)=(.+)#",$line,$re)){$this->MAIN_ARRAY[trim($re[1])]=trim($re[2]);}
		}
	}
	
	private function SaveConf(){
		while (list ($key, $line) = each ($this->MAIN_ARRAY) ){$conf=$conf."$key = $line\n";}
		@file_put_contents("/etc/postfix-$this->instance/main.cf",$conf);
		$array=$this->BuildMasterCF();
		@file_put_contents("/etc/postfix-$this->instance/master.cf",implode("\n",$array));
	}
	
	private function inet_interfaces_load(){
		$sql="SELECT nic,ipaddr FROM nics_virtuals WHERE org='$this->ou'";
		$q=new mysql();
		$ip=new networking();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["ipaddr"]==null){
				if($ligne["nic"]<>null){
					$ligne["ipaddr"]=$net->array_TCP[$ligne["nic"]];
				}
			}
			if($ligne["ipaddr"]<>null){
				$array[]=$ligne["ipaddr"];
			}			
		}
		
		$this->inet_interfaces=$array;
		if(is_array($array)){
			$this->MAIN_ARRAY["inet_interfaces"]=implode(",",$array);
		}
	}
	
	
	private function mydestination(){
	  		$this->MAIN_ARRAY["mydestination"]="ldap:mydestinationTable";
			$this->BuildLdapSettings("mydestinationTable",
			$this->DN,"(&(objectclass=organizationalUnit)(associatedDomain=%s))",
			"associatedDomain");
		//............................................................................................................................................................................................
	}
	
	private function transport_maps(){
		$ldap=new clladp();
		$this->MAIN_ARRAY["transport_maps"]="ldap:TransportMapsTable";
		$this->BuildLdapSettings("TransportMapsTable",
			$this->DN,"(&(objectClass=transportTable)(cn=%d))",
			"transport");
			
		$this->MAIN_ARRAY["relay_domains"]="ldap:RelaisDomainsTable";
		$this->BuildLdapSettings("RelaisDomainsTable",$ldap->suffix,"(&(objectclass=PostFixRelayDomains)(cn=%s))","cn");			
			
	}
	
	private function Aliases(){
		$this->MAIN_ARRAY["virtual_mailbox_maps"]="ldap:VirtualMailboxMapsTable";
		$this->MAIN_ARRAY["alias_maps"]="ldap:AliasMapsTable";
		$this->MAIN_ARRAY["local_recipient_maps"]="ldap:VirtualMailboxMapsTable";
		$this->BuildLdapSettings("VirtualMailboxMapsTable",$this->DN,"(&(objectClass=userAccount)(mail=%s))","uid");
		$this->BuildLdapSettings("AliasMapsTable",$this->DN,"(&(objectClass=userAccount)(uid=%u))","mail");
		
	}
	
	private function MyHostname_SET(){
		$this->MAIN_ARRAY["myhostname"]=$this->MyHostname();
	}
	
	public function MyHostname(){
		$sql="SELECT `value` FROM postfix_multi WHERE `key`='myhostname' AND `ou`='$this->ou' LIMIT 0,1;";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		writelogs("\"{$ligne["value"]}\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
		if(trim($ligne["value"])==null){
			$users=new usersMenus();
			writelogs("FQDN=\"{$users->fqdn}\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
			return $users->fqdn;
		}
		return $ligne["value"];
	}
	
	private function ArticaDefault(){
		$this->MAIN_ARRAY["artica_destination_recipient_limit"]=1;
	}
	
	private function mynetworks(){
		$sql="SELECT * FROM postfix_multi WHERE `key`='mynetworks' AND `ou`='$this->ou' ORDER BY ID DESC;";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$array[]=$ligne["value"];			
		}
		
		if(is_array($array)){
			$this->MAIN_ARRAY["mynetworks"]=implode(", ",$array);
		}
		
	}
	
	private function smtpd_client_restrictions_set(){
		
		$assp=new assp_multi($this->ou);
		if($assp->AsspEnabled==1){$ASSP_ENABLED=true;}
		
		$array=$this->smtpd_client_restrictions_get();
		//smtpd_client_restrictions
		//reject_unknown_client_hostname
		//reject_invalid_hostname
		//reject_unknown_reverse_client_hostname
		//reject_unknown_sender_domain
		//reject_non_fqdn_sender
		if(is_array($array)){
			$EnablePostfixAntispamPack=$array["EnablePostfixAntispamPack"];
			unset($array["EnablePostfixAntispamPack"]);
			
			while (list ($key, $line) = each ($array) ){
				if($line==1){$smtpd_recipient_restrictions[]=$key;}
			}
			
			if(is_array($smtpd_recipient_restrictions)){$smtpd_recipient_restrictions_line=",".implode(",",$smtpd_recipient_restrictions);}
		
		}
		
		if($EnablePostfixAntispamPack==1){
			$RBL[]="reject_rbl_client bl.spamcop.net";
	        $RBL[]="reject_rbl_client b.barracudacentral.org";
	        $RBL[]="reject_rbl_client zen.spamhaus.org";
	        $RBL[]="reject_rbl_client psbl.surriel.com";
	        $RBL[]="reject_rbl_client ix.dnsbl.manitu.net";
		}
		
		$sql="SELECT * FROM postfix_multi WHERE `key`='RBL' AND `ou`='$this->ou' ORDER BY ID DESC;";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["value"]<>null){$RBL[]="reject_rbl_client ".$ligne["value"];}
		}
		
		if($ASSP_ENABLED){unset($RBL);}
		if(is_array($RBL)){
			$RBL_TEXT=",".implode(",",$RBL);
		}
		
		$this->MAIN_ARRAY["smtpd_recipient_restrictions"]="permit_mynetworks,permit_sasl_authenticated";
		$this->MAIN_ARRAY["smtpd_recipient_restrictions"]=$this->MAIN_ARRAY["smtpd_recipient_restrictions"].",reject_unauth_destination";
		$this->MAIN_ARRAY["smtpd_recipient_restrictions"]=$this->MAIN_ARRAY["smtpd_recipient_restrictions"].",check_client_access hash:/etc/postfix-$this->instance/postfix_allowed_connections";
		$this->MAIN_ARRAY["smtpd_recipient_restrictions"]=$this->MAIN_ARRAY["smtpd_recipient_restrictions"]."$smtpd_recipient_restrictions_line$RBL_TEXT,permit";
		}
	
	
	function smtpd_client_restrictions_get(){
		$sql="SELECT ValueTEXT FROM postfix_multi WHERE `key`='smtpd_client_restrictions' AND `ou`='$this->ou'";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		return unserialize(base64_decode($ligne["ValueTEXT"]));
		
	}
	
	
	private function postfix_allowed_connections(){
		$sql="SELECT * FROM postfix_multi WHERE `key`='ip_white_listed' AND `ou`='{$_GET["ou"]}' ORDER BY ID DESC;";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["value"]<>null){
				$arr[]="{$ligne["value"]}\t OK";
			}
			
		}
		
		if(!is_array($arr)){
			$conf="";
		}else{
			$conf=implode("\n",$arr);
		}
		
		@file_put_contents("/etc/postfix-$this->instance/postfix_allowed_connections",$conf);
		shell_exec("postmap /etc/postfix-$this->instance/postfix_allowed_connections");
		
	}
	
	
	
	
	private function BuildLdapSettings($ldap_key,$searchBaseWithoutSuffix=null,$queryfilter,$result_attribute,$special_result_attribute=null){
				$ldap=new clladp();
				if(trim($searchBaseWithoutSuffix)==null){$searchBaseWithoutSuffix="dc=organizations,$ldap->suffix";}
				$ldap_host=$ldap->ldap_host;
				$ldap_port=$ldap->ldap_port;
				if($ldap_host=='*'){$ldap_host='127.0.0.1';}
				if($ldap_port==null){$ldap_port='389';}
				
				
				$this->MAIN_ARRAY["{$ldap_key}_server_host"]="$ldap_host";
				$this->MAIN_ARRAY["{$ldap_key}_server_port"]="$ldap_port";
				$this->MAIN_ARRAY["{$ldap_key}_bind"]="yes";
				$this->MAIN_ARRAY["{$ldap_key}_bind_dn"]="cn=$ldap->ldap_admin,$ldap->suffix";
				$this->MAIN_ARRAY["{$ldap_key}_bind_pw"]="$ldap->ldap_password";
				$this->MAIN_ARRAY["{$ldap_key}_search_base"]="$searchBaseWithoutSuffix";
				$this->MAIN_ARRAY["{$ldap_key}_timeout"]="10";
				$this->MAIN_ARRAY["{$ldap_key}_query_filter"]="$queryfilter";
				$this->MAIN_ARRAY["{$ldap_key}_version"]="3";
				if($special_result_attribute<>null){
					$this->MAIN_ARRAY["{$ldap_key}_special_result_attribute"]="$special_result_attribute";
					 
				}
				$this->MAIN_ARRAY["{$ldap_key}_result_attribute"]="$result_attribute";	
				
	}

	private function CleanArray(){
		$this->CleanLdapArrayKeys('ldaprelcert');
		$this->CleanLdapArrayKeys('ConnectionCacheDestinations');
		$this->CleanLdapArrayKeys('mydestinationTable');
		$this->CleanLdapArrayKeys('RelaisDomainsTable');
		$this->CleanLdapArrayKeys('RelaisRecipientTable');
		$this->CleanLdapArrayKeys('VirtualAliasMapsTable');
		$this->CleanLdapArrayKeys('VirtualMailboxMapsTable');
		$this->CleanLdapArrayKeys('mynetworksTable');
		$this->CleanLdapArrayKeys('senderCanonicalTable');
		$this->CleanLdapArrayKeys('TransportMapsTable');
		$this->CleanLdapArrayKeys('TransportUsersTable');
		$this->CleanLdapArrayKeys('SmtpSaslPasswordMaps');
		$this->CleanLdapArrayKeys('VirtualMailManMaps');
		$this->CleanLdapArrayKeys('smtpd_client_restrictions_check_client_access');
		$this->CleanLdapArrayKeys('recipient_canonical_maps');
		$this->CleanLdapArrayKeys('MailBoxTransportMaps');
		$this->CleanLdapArrayKeys('TransportMailmanMaps');
		$this->CleanLdapArrayKeys('dynalistTable');
		$this->CleanLdapArrayKeys('RecipientBccMaps');
		$this->CleanLdapArrayKeys('AliasWhiteListTable');
		$this->CleanLdapArrayKeys('WhiteBlackTransport');
		$this->CleanLdapArrayKeys('WhiteBlackMaps');
		$this->CleanLdapArrayKeys('CatchAllAliasMaps');
		$this->CleanLdapArrayKeys('SenderDependentRelayhostMaps');
		$this->CleanLdapArrayKeys('UserDependentRelayhostMaps');
		$this->CleanLdapArrayKeys('SenderCanonicalPassword');
		$this->CleanLdapArrayKeys('SenderCanonicalDependentRelayhostMaps');
		$this->CleanLdapArrayKeys('SyncArticaMaps');
		unset($this->main_array["relay_clientcerts"]);
		unset($this->main_array["smtp_connection_cache_destinations"]);
		unset($this->main_array["relais_domain"]);
		unset($this->main_array["virtual_mailbox_maps"]);
		unset($this->main_array["alias_maps"]);
		unset($this->main_array["relay_recipient_maps"]);
		unset($this->main_array["mydestination"]);
		unset($this->main_array["sender_canonical_maps"]);
		unset($this->main_array["smtp_generic_maps"]);
		unset($this->main_array["transport_maps"]);
		unset($this->main_array["smtp_sasl_password_maps"]);
		unset($this->main_array["artica_destination_recipient_limit"]);
		unset($this->main_array["smtpd_helo_restrictions"]);
		unset($this->main_array["smtpd_recipient_restrictions"]);
		unset($this->main_array["smtpd_client_restrictions"]);
		unset($this->main_array["virtual_transport_maps"]);
		unset($this->main_array["virtual_alias_domains"]);
		unset($this->main_array["sender_dependent_relayhost_maps"]);
	}

	private function CleanLdapArrayKeys($key){
		unset($this->main_array["{$key}_server_host"]);
		unset($this->main_array["{$key}_server_port"]);
		unset($this->main_array["{$key}_bind"]);
		unset($this->main_array["{$key}_bind_dn"]);
		unset($this->main_array["{$key}_bind_pw"]);
		unset($this->main_array["{$key}_search_base"]);
		unset($this->main_array["{$key}_timeout"]);
		unset($this->main_array["{$key}_query_filter"]);
		unset($this->main_array["{$key}_version"]);
		unset($this->main_array["{$key}_result_attribute"]);
		unset($this->main_array["{$key}_special_result_attribute"]);
		}	
		
		
		
	private function BuildMasterCF(){
		$ASSP=new assp_multi($this->ou);
		
		$MASTER[]="# Postfix master process configuration file.  For details on the format";
		$MASTER[]="# of the file, see the master(5) manual page (command: \"man 5 master\").";
		$MASTER[]="#";
		$MASTER[]="# Do not forget to execute \"postfix reload\" after editing this file.";
		$MASTER[]="#";
		$MASTER[]="# ==========================================================================";
		$MASTER[]="# service type  private unpriv  chroot  wakeup  maxproc command + args";
		$MASTER[]="#               (yes)   (yes)   (yes)   (never) (100)";
		$MASTER[]="# ==========================================================================";
		
		if($ASSP->AsspEnabled==1){
			$MASTER[]="6000      inet  n       -       n       -       -       smtpd";
		}else{
			$MASTER[]="smtp      inet  n       -       n       -       -       smtpd";
		}
		$MASTER[]="submission inet n       -       n       -       -       smtpd";
		$MASTER[]="  -o smtpd_tls_security_level=encrypt";
		$MASTER[]="  -o smtpd_sasl_auth_enable=yes";
		$MASTER[]="  -o smtpd_client_restrictions=permit_sasl_authenticated,reject";
		$MASTER[]="  -o milter_macro_daemon_name=ORIGINATING";
		$MASTER[]="smtps     inet  n       -       n       -       -       smtpd";
		$MASTER[]="  -o smtpd_tls_wrappermode=yes";
		$MASTER[]="  -o smtpd_sasl_auth_enable=yes";
		$MASTER[]="  -o smtpd_client_restrictions=permit_sasl_authenticated,reject";
		$MASTER[]="  -o milter_macro_daemon_name=ORIGINATING";
		$MASTER[]="628       inet  n       -       n       -       -       qmqpd";
		$MASTER[]="pickup    fifo  n       -       n       60      1       pickup";
		$MASTER[]="cleanup   unix  n       -       n       -       0       cleanup";
		$MASTER[]="qmgr      fifo  n       -       n       300     1       qmgr";
		$MASTER[]="#qmgr     fifo  n       -       n       300     1       oqmgr";
		$MASTER[]="tlsmgr    unix  -       -       n       1000?   1       tlsmgr";
		$MASTER[]="rewrite   unix  -       -       n       -       -       trivial-rewrite";
		$MASTER[]="bounce    unix  -       -       n       -       0       bounce";
		$MASTER[]="defer     unix  -       -       n       -       0       bounce";
		$MASTER[]="trace     unix  -       -       n       -       0       bounce";
		$MASTER[]="verify    unix  -       -       n       -       1       verify";
		$MASTER[]="flush     unix  n       -       n       1000?   0       flush";
		$MASTER[]="proxymap  unix  -       -       n       -       -       proxymap";
		$MASTER[]="proxywrite unix -       -       n       -       1       proxymap";
		$MASTER[]="smtp      unix  -       -       n       -       -       smtp";
		$MASTER[]="relay     unix  -       -       n       -       -       smtp";
		$MASTER[]="	-o smtp_fallback_relay=";
		$MASTER[]="        -o smtp_helo_timeout=5 -o smtp_connect_timeout=5";
		$MASTER[]="showq     unix  n       -       n       -       -       showq";
		$MASTER[]="error     unix  -       -       n       -       -       error";
		$MASTER[]="retry     unix  -       -       n       -       -       error";
		$MASTER[]="discard   unix  -       -       n       -       -       discard";
		$MASTER[]="local     unix  -       n       n       -       -       local";
		$MASTER[]="virtual   unix  -       n       n       -       -       virtual";
		$MASTER[]="lmtp      unix  -       -       n       -       -       lmtp";
		$MASTER[]="anvil     unix  -       -       n       -       1       anvil";
		$MASTER[]="scache    unix  -       -       n       -       1       scache";
		$MASTER[]="mailman   unix 	- 	n 	n 	- 	- 	pipe";
		$MASTER[]=" flags=FR user=mail:mail argv=/etc/mailman/postfix-to-mailman.py \${nexthop} \${mailbox}";
		$MASTER[]="artica-whitelist    unix  -       n       n       -       -       pipe";
		$MASTER[]="  flags=F  user=mail argv=/usr/share/artica-postfix/bin/artica-whitelist -a \${nexthop} -s \${sender} --white";
		$MASTER[]="artica-blacklist    unix  -       n       n       -       -       pipe";
		$MASTER[]="  flags=F  user=mail argv=/usr/share/artica-postfix/bin/artica-whitelist -a \${nexthop} -s \${sender} --black";
		$MASTER[]="artica-reportwbl    unix  -       n       n       -       -       pipe";
		$MASTER[]="  flags=F  user=mail argv=/usr/share/artica-postfix/bin/artica-whitelist -a \${nexthop} -s \${sender} --report";
		$MASTER[]="artica-reportquar    unix  -       n       n       -       -       pipe";
		$MASTER[]="  flags=F  user=mail argv=/usr/share/artica-postfix/bin/artica-whitelist -a \${nexthop} -s \${sender} --quarantines";
		$MASTER[]="artica-filter    unix  -       n       n       -       20       pipe";
		$MASTER[]="  flags=FOh  user=www-data argv=/usr/share/artica-postfix/exec.artica-filter.php -f \${sender} --  -s \${sender} -r \${recipient} -c \${client_address}";
		/* $MASTER[]="127.0.0.1:33559	inet	n	-	n	-	-	smtpd";
		$MASTER[]="    -o notify_clases=protocol,resource,software";
		$MASTER[]="    -o header_checks=";
		$MASTER[]="    -o content_filter=";
		$MASTER[]="    -o smtpd_restriction_classes=";
		$MASTER[]="    -o smtpd_delay_reject=no";
		$MASTER[]="    -o smtpd_client_restrictions=permit_mynetworks,reject";
		$MASTER[]="    -o smtpd_helo_restrictions=";
		$MASTER[]="    -o smtpd_sender_restrictions=";
		$MASTER[]="    -o smtpd_recipient_restrictions=permit_mynetworks,reject";
		$MASTER[]="    -o smtpd_data_restrictions=reject_unauth_pipelining";
		$MASTER[]="    -o smtpd_end_of_data_restrictions=";
		$MASTER[]="    -o mynetworks=127.0.0.0/8";
		$MASTER[]="    -o strict_rfc821_envelopes=yes";
		$MASTER[]="    -o smtpd_error_sleep_time=0";
		$MASTER[]="    -o smtpd_soft_error_limit=1001";
		$MASTER[]="    -o smtpd_hard_error_limit=1000";
		$MASTER[]="    -o smtpd_client_connection_count_limit=0";
		$MASTER[]="    -o smtpd_client_connection_rate_limit=0";
		$MASTER[]="    -o receive_override_options=no_header_body_checks,no_unknown_recipient_checks";
		$MASTER[]="    -o smtp_send_xforward_command=yes";
		$MASTER[]="    -o disable_dns_lookups=yes";
		$MASTER[]="    -o local_header_rewrite_clients=";
		$MASTER[]="    -o smtpd_milters=";
	*/
		$MASTER[]="";
		return $MASTER;
		
		
	}
	
}
?>