<?php
include_once(dirname(__FILE__).'/class.dansguardian.inc');
include_once(dirname(__FILE__).'/class.kav4proxy.inc');
include_once(dirname(__FILE__).'/class.mysql.inc');
include_once(dirname(__FILE__).'/class.sockets.inc');
include_once(dirname(__FILE__).'/class.samba.inc');	
include_once(dirname(__FILE__)."/class.c-icap-filter.inc");	



class squid{
	var $hostname;
	var $squid_conf;
	var $http_port_array;
	var $https_port_array;
	var $acls_rules_array;
	var $restrictions_array;
	var $icap_service_array;
	var $icap_class_array=array();
	var $global_conf_array;
	var $dns_nameservers_array;
	var $cache_dir_array;
	var $acl_array_local;
	var $acl_array_wizard;
	var $protocol_table;
	var $protocol_field;
	var $EnableKav4Proxy;
	var $SQUID_VERSION;
	var $dn;
	
	var $acl_list=array("browser"=>"{acl_browser}",
							"src"=>"{acl_src}",
							"srcdomain"=>"{acl_srcdomain}",
							"srcdom_regex"=>"{acl_srcdom_regex}",
							"time"=>"{acl_time}",
							"dst_as"=>"{acl_dst_as}",
							"arp"=>"{acl_arp}",
							"proxy_auth"=>"{acl_proxy_auth}",
							"proxy_auth_regex"=>"{acl_proxy_auth_regex}",
							"external"=>"{acl_external}",
							"max_user_ip"=>"{acl_max_user_ip}",
							"maxconn"=>"{acl_maxconn}",
							"myip"=>"{acl_myip}",
							"myport"=>"{acl_myport}",
							"ident"=>"{acl_ident}",
							"ident_regex"=>"{acl_ident_regex}",
							"rep_mime_type"=>"{acl_rep_mime_type}",
							"req_mime_type"=>"{acl_req_mime_type}",
							"method"=>"{acl_method}",
							"src_as"=>"{acl_src_as}",
							"urlpath_regex"=>"{acl_urlpath_regex}",
							"url_regex"=>"{acl_url_regex}",
							"port"=>"{acl_port}",
							"proto"=>"{acl_proto}",
							"dst"=>"{acl_dst}",
							"dstdomain"=>"{acl_dstdomain}",
							"dstdom_regex"=>"{acl_dstdom_regex}",
							
							);
							
	var $cache_type_list=array("ufs"=>"{ufs}","aufs"=>"{squid_aufs}","diskd"=>"{squid_diskd}");

	
	function squid($hostname=null){
		
		
		
	}
	
	
	function LoadConfig(){
		
		$ldap=new clladp();
		$this->dn="cn=squid,cn=$this->hostname,cn=artica,".$ldap->suffix;
		$this->ParseACL();
		
		$res=@ldap_read($ldap->ldap_connection,$this->dn,"(objectClass=SquidProxyClass)",array());
		if($res){
			$hash=ldap_get_entries($ldap->ldap_connection,$res);
			$this->squid_conf=$hash[0][strtolower('GlobalSquidConf')][0];
			
			//ArticaEnableKav4ProxyInSquid ---------------------------------------------------------------------
			
			if($hash[0][strtolower('ArticaEnableKav4ProxyInSquid')][0]==null){
				$upd["ArticaEnableKav4ProxyInSquid"]=='1';
				$ldap->Ldap_add_mod($this->dn,$upd);
				unset($upd);
				$this->EnableKav4Proxy=1;
			}else{
				$this->EnableKav4Proxy=$hash[0][strtolower('ArticaEnableKav4ProxyInSquid')][0];
				}
			//---------------------------------------------------------------------------------------------------
			
		}	
	
		$this->ParseConfig();
		$this->BuildDefaultParameters();
		$this->Parse_restrictions();
	}
	
	function LoadProtocolTable(){
		
		$this->protocol_field=file_get_contents(dirname(__FILE__) . '/databases/protocols.field.db');
		return true;
		
		$datas=file_get_contents(dirname(__FILE__) . '/databases/protocols.db');
		$table=explode("\n",$datas);
		while (list ($num, $val) = each ($table) ){
				$tb=explode(";",$val);		
				//$this->protocol_table[$tb[0]]=$tb[1];
				echo "<option value=\"{$tb[0]}\">{$tb[0]} ({$tb[1]})</option>\n";
				
				
			}
			
		}
		
		
		
	
	
	
	function BuildWizard(){
		$tbl=explode("\n", $this->BuildAcls());
		while (list ($num, $val) = each ($tbl) ){
			$this->acl_array_local[strtolower($val)]=$val;
		}
		
		$datas=file_get_contents("ressources/databases/squid.acls.wizard.db");
		$tb=explode("\n",$datas);
		
		while (list ($num, $val) = each ($tbl) ){
			$this->acl_array_wizard[strtolower($val)]=$val;
		}		
		
	}
	
	
	function VerifyLdapBranch(){
		$ldap=new clladp();
		$ldap->ArticaCreate();
		$dn="cn=$this->hostname,cn=artica,$ldap->suffix";
		$this->dn="cn=squid,$dn";
		
		if(!$ldap->ExistsDN($dn)){
			writelogs("building $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$upd["ObjectClass"][]='top';
			$upd["ObjectClass"][]='ArticaHostName';
			$upd["cn"][]=$this->hostname;
			$ldap->ldap_add($dn,$upd);
			unset($upd);
		}else{writelogs("$dn exists",__CLASS__.'/'.__FUNCTION__,__FILE__);}

		
	$dn="cn=squid,cn=$this->hostname,cn=artica,$ldap->suffix";	
	if(!$ldap->ExistsDN($this->dn)){
			writelogs("building $this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$upd["ObjectClass"][]='top';
			$upd["ObjectClass"][]='SquidProxyClass';
			$upd["GlobalSquidConf"][]=$this->BuildConfig();
			$upd["cn"][]="squid";
			$ldap->ldap_add($this->dn,$upd);
			unset($upd);
		}else{writelogs("$dn exists",__CLASS__.'/'.__FUNCTION__,__FILE__);}
		
		if(!$ldap->ExistsDN("cn=acls,$this->dn")){
			$upd["ObjectClass"][]='top';
			$upd["ObjectClass"][]='PostFixStructuralClass';
			$upd["cn"][]="acls";
			$ldap->ldap_add("cn=acls,$this->dn",$upd);
			unset($upd);
			}
		
			
		$dn="cn=access,cn=squid,cn=$this->hostname,cn=artica,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			writelogs("Building $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);		
			$upd["ObjectClass"][]='top';
			$upd["ObjectClass"][]='PostFixStructuralClass';
			$upd["cn"][]="access";
			$ldap->ldap_add($dn,$upd);
			unset($upd);
			
			}	
			
		if(!$ldap->ExistsDN("cn=icap_class,cn=squid,cn=$this->hostname,cn=artica,$ldap->suffix")){
			$upd["ObjectClass"][]='top';
			$upd["ObjectClass"][]='PostFixStructuralClass';
			$upd["cn"][]="icap_class";
			$ldap->ldap_add("cn=icap_class,cn=squid,cn=$this->hostname,cn=artica,$ldap->suffix",$upd);
			unset($upd);
			}					
			
	}
	
	
	
	function BuildAclForKav(){
		
			$res=false;
			$users=new usersMenus();
			if($this->SQUID_VERSION>2.6){
			if($users->KAV4PROXY_INSTALLED){
				$res=true;
				}	
			}		
		
		if(!$res){return null;}
		$this->AddAclType("multimedia_rep","rep_mime_type","-i ^video/x-ms-asf$");	
		$this->AddAclType("multimedia_rep","rep_mime_type","-i ^application/vnd.ms.wms-hdr.asfv1$");
		$this->AddAclType("multimedia_rep","rep_mime_type","-i ^application/x-mms-framed$");
		$this->AddAclType("multimedia_rep","rep_mime_type","-i ^video/x-ms-asf$");
		$this->AddAclType("multimedia_rep","rep_mime_type","-i ^video/x-ms-asf$");
		$this->AddAclType("multimedia_rep","rep_mime_type","-i ^image/.*");
		$this->AddAclType("multimedia_rep","rep_mime_type","-i ^.*video.*");
		$this->AddAclType("multimedia_rep","rep_mime_type","-i ^.*audio.*");
		$this->AddAclType("multimedia_rep","rep_mime_type","-i ^application/x-dvi$");
		$this->AddAclType("multimedia_rep","rep_mime_type","-i ^application/x-isoview");
		$this->AddAclType("multimedia_browsers","browser","-i ^.*Windows-Media-Player.*");
		$this->AddAclType("multimedia_browsers","browser","-i ^.*player.*");
		$this->AddAclType("multimedia_browsers","browser","-i ^.*player.*");
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.deb$");
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.rpm$");
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.iso$");
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.tar\.gz$");
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.gz$");		
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.bz$");			
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.tar$");
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.cue$");
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.nrg$");
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.crf$");
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.bwi$");
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.bwt$");
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.lcd$");
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.ccd$");
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.mdf$");
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.mds$");
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.vcd$");
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.cif$");
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.vdi$");
		$this->AddAclType("bigfiles_types","urlpath_regex","-i \.img$");
		$this->AddAclType("acl_kav_GET","method","GET");
		writelogs("*** Building rules now ***",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		$this->icap_add_restrictions_rule("ic_kav_resp",'deny','multimedia_rep','no');
		$this->icap_add_restrictions_rule("ic_kav_resp",'deny','multimedia_browsers','no');
		$this->icap_add_restrictions_rule("ic_kav_resp",'deny','bigfiles_types','no');
		$this->icap_add_restrictions_rule("ic_kav_resp",'allow','all','no');
		
		
		$this->AccessRules_moveto_position("icap_access","ic_kav_resp deny multimedia_rep",0);		
		$this->AccessRules_moveto_position("icap_access","ic_kav_resp deny multimedia_browsers",1);
		$this->AccessRules_moveto_position("icap_access","ic_kav_resp deny bigfiles_types",2);
		$this->AccessRules_moveto_position("icap_access","ic_kav_resp allow all",3);
		
		
		
				$this->icap_service_array["is_kav_resp"]=array(
							"name"=>"is_kav_resp",
							"vpoint"=>"respmod_precache",
							"bypass"=>0,
							"url"=>"127.0.0.1:1344/av/respmod");
			    
				$this->icap_class_array["icap_class"]="ic_kav_resp";			
				$this->icap_add_service_in_class('ic_kav_resp','is_kav_resp');
				$this->icap_add_restrictions_rule("ic_kav_resp",'allow','all','no');
				$this->SaveToLdap();		
		
		}
	
	
	function BuildAclDefaults(){
		   
			$this->AddAclType("manager","proto","cache_object");
			$this->AddAclType("localhost","src","127.0.0.1/8");
			$this->AddAclType("to_localhost","dst","127.0.0.0/8");
			$this->AddAclType("SSL_ports","port","443");
			$this->AddAclType("SSL_ports","port","563");
			$this->AddAclType("Safe_ports","port","80");
			$this->AddAclType("Safe_ports","port","81");
			$this->AddAclType("Safe_ports","port","443");
			$this->AddAclType("Safe_ports","port","8081");
			$this->AddAclType("Safe_ports","port","1025-65535");
			$this->AddAclType("Safe_ports","port","21");
			$this->AddAclType("purge","method","PURGE");
			$this->AddAclType("POST","method","GET");
			$this->AddAclType("POST","method","POST");
			$this->AddAclType("POST","method","HEAD");
			$this->AddAclType("proto-ftp","proto","ftp");
			$this->AddAclType("QUERY","urlpath_regex","cgi-bin");
			$this->AddAclType("QUERY","urlpath_regex","\?");
			$this->AddAclType("CONNECT","method","CONNECT");
			
			$this->BuildAclForKav();			

		
		
		
	}
	
	
	function BuildIcapAccessRules(){
		if($this->SQUID_VERSION<=2.6){
			writelogs("$this->SQUID_VERSION aborting bad version",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return null;}
		$this->Parse_restrictions();	
		if(!is_array($this->restrictions_array)){
			writelogs("There is no rules here",__CLASS__.'/'.__FUNCTION__,__FILE__);		
			return "#no rules...\n";}
	
		reset($this->restrictions_array);
		$array=$this->restrictions_array["icap_access"]["rules"];
		if(is_array($array)){
			
			while (list ($num, $val) = each ($array) ){
				$tbl=explode(' ',$val);
				$icap_class=$tbl[0];
				if(isset($this->icap_class_array[$icap_class])){
				$conf=$conf . "icap_access $val\n";}
			}
		}else{
			$conf=$conf ."#no rules...\n";
		}
		return $conf;
	}
	
	
	
	
	
	
	function BuildAccessRulesDefault(){
			
			$this->AddAccessRule("http_access","allow","proto-ftp");
			$this->AddAccessRule("http_access","deny","!Safe_ports");
			$this->AddAccessRule("http_access","allow","POST");
			$this->AddAccessRule("http_access","allow","purge localhost");
			$this->AddAccessRule("http_access","allow","Safe_ports");
			$this->AddAccessRule("http_access","allow","CONNECT SSL_ports");
			$this->AddAccessRule("http_access","allow","CONNECT localhost");
			$this->AddAccessRule("icp_access","allow","all");
			$this->AddAccessRule("http_access","allow","all");
			if($this->SQUID_VERSION<=2.6){return null;}
			
			if($users->KAV4PROXY_INSTALLED){
				unset($this->icap_service_array);
				unset($this->icap_class_array);
				
				$this->AddAclType("acl_kav_GET","method","GET");
				unset($squid->icap_service_array["is_kav_resp"]);
				unset($squid->icap_service_array["is_kav_req"]);
	
				unset($squid->icap_class_array["ic_kav_resp"]);
				unset($squid->icap_class_array["ic_kav_req"]);
	
				$squid->icap_class_array["ic_kav_resp"]="ic_kav_resp";
				
				$squid->icap_service_array["is_kav_resp"]=array(
						"name"=>"is_kav_resp",
						"vpoint"=>"respmod_precache",
						"bypass"=>0,
						"url"=>"127.0.0.1:1344/av/respmod"
						);
				$this->icap_class_array["icap_class"]="ic_kav_resp";			
				$this->icap_add_restrictions_rule("ic_kav_resp",'allow','all','no');
			}
			$this->SaveToLdap();			
			
	}
		
		
		
		function AddAclType($ruleName,$acl_type,$value){
				
				$ruleName=str_replace(' ','_',$ruleName);
				writelogs("$ruleName=>$acl_type ($value), dn=$this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$ldap=new clladp();
				$dn="cn=$ruleName,cn=acls,$this->dn";
				if(!$ldap->ExistsDN($dn)){
					$upd["ObjectClass"][]='top';
					$upd["ObjectClass"][]='SquidProxyAclsRule';
					$upd["SquidProxyAclsType"][]=$acl_type;
					$upd["AclRuleEnabled"][]='yes';
					$upd["cn"][]=$ruleName;
					$ldap->ldap_add($dn,$upd);
					unset($upd);
					}
				
				$upd["AclDatas"]=$value;
				$ldap->Ldap_add_mod($dn,$upd);
				$this->SaveToLdap();
				$this->ParseACL();
				}
				
				
		function AddAccessRule($access_rule,$allow,$acl_rule){
			
			$ldap=new clladp();
			$dn="cn=$access_rule,cn=access,$this->dn";
			if(!$ldap->ExistsDN($dn)){
				writelogs("Building rule $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);		
				$upd["ObjectClass"][]='top';
				$upd["ObjectClass"][]='SquidProxyAccessRule';
				$upd["cn"][]=$access_rule;
				$upd["AccessRuleEnabled"][]='yes';
				$ldap->ldap_add($dn,$upd);
				unset($upd);
				}
				
				
				$upd["AclRuleName"]="$allow $acl_rule";
				$ldap->Ldap_add_mod($dn,$upd);
				$this->SaveToLdap();
			}
			
			
		
				
				
		function DelAclType($aclname,$acl_index){
				$ldap=new clladp();		
				$dn="cn=$aclname,cn=acls,$this->dn";			
				$valueToDestroy=$this->acls_rules_array[$aclname]["datas"][$acl_index];
				$upd["AclDatas"]=$valueToDestroy;
				$ldap->Ldap_del_mod($dn,$upd);
				$this->SaveToLdap();
				}
				
		function DeleteAclRule($acl_name){
			$ldap=new clladp();		
			$dn="cn=$acl_name,cn=acls,$this->dn";
			$ldap->ldap_delete($dn,true);
			$this->SaveToLdap();				
		}
		
		
		function Del_icap_class($group,$service){
			$ldap=new clladp();
			$dn="cn=$group,cn=icap_class,cn=squid,cn=$this->hostname,cn=artica,$ldap->suffix";
			$upd["SquidICAPClassService"]=$service;
			$ldap->Ldap_del_mod($dn,$upd);
		}
		
		function Del_icap_class_all($group){
			$ldap=new clladp();
			$dn="cn=$group,cn=icap_class,cn=squid,cn=$this->hostname,cn=artica,$ldap->suffix";
			$ldap->ldap_delete($dn,true);
		}
		
		
		function Add_icap_class($group,$service){
		$ldap=new clladp();
		$dn="cn=$group,cn=icap_class,cn=squid,cn=$this->hostname,cn=artica,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			writelogs("Building new class $dn group $group, service $service",__CLASS__.'/'.__FUNCTION__,__FILE__);		
			$upd["ObjectClass"][]='top';
			$upd["ObjectClass"][]='SquidICAPClass';
			$upd["SquidICAPClassService"][]=$service;
			$upd["cn"][]="$group";
			$ldap->ldap_add($dn,$upd);
			unset($upd);
			return true;
			}						

			$upd["SquidICAPClassService"]=$service;
			$ldap->Ldap_add_mod($dn,$upd);
			
		}
		
				
	function Load_icap_classes(){
		$ldap=new clladp();
		$dn="cn=icap_class,$this->dn";
		writelogs("find SquidICAPClass in $dn" ,__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sr=@ldap_search($ldap->ldap_connection,$dn,"(ObjectClass=SquidICAPClass)",array("cn","SquidICAPClassService"));
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			
			for($i=0;$i<$hash["count"];$i++){
				$class_name=$hash[$i]["cn"][0];
				for($z=0;$z<$hash[$i]["squidicapclassservice"]["count"];$z++){
					$this->icap_class_array[$class_name][$z]=$hash[$i]["squidicapclassservice"][$z];
				}
				
			}
			
		}	
		
		
	}
	
	
	function ParseConfig(){
		$tbl=explode("\n",$this->squid_conf);
		writelogs("Parsing config file " . count($tbl) . " lines number",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(is_array($this->http_port_array)){reset($this->http_port_array);}
		
		$this->Load_icap_classes();
		
		if(!is_array($tbl)){return null;}
		
		
		while (list ($num, $val) = each ($tbl) ){
			if(preg_match('#^http_port\s+(.+)#',$val,$re)){
				writelogs("Found http_port=$val",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$this->http_port_array[]=$re[1];
			}
			
			if(preg_match('#^https_port\s+(.+)#',$val,$re)){
				writelogs("Found https_port=$val",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$this->https_port_array[]=$re[1];
			}
			
			if(preg_match('#^dns_nameservers\s+(.+)#',$val,$re)){
				writelogs("Found dns_nameservers=$val",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$this->dns_nameservers_array[]=$re[1];
			}			
			
				
			if(preg_match('#^icap_service\s+(.+?)\s+([a-z_]+)\s+([0-9])\s+icap://(.+)#',$val,$re)){
				writelogs("Found icap_service=$val",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$this->icap_service_array[$re[1]]=array(
						"name"=>$re[1],
						"vpoint"=>$re[2],
						"bypass"=>$re[3],
						"url"=>$re[4]
					);
			}
			
					
			if(preg_match('#(.+?)\s+(.+)#',$val,$re)){
				$this->global_conf_array[$re[1]]=$re[2];
			}
			
			
			if(preg_match('#^cache_dir#',$val)){
				writelogs("Found cache_dir=$val",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$tba=explode(" ",$val);
				$this->cache_dir_array[]=array(
					"line"=>$val,
					"dir"=>$tba[2],
					"type"=>$tba[1],
					"sizemb"=>$tba[3],
					"level1"=>$tba[4],
					"level2"=>$tba[5]
					);
			}
			
		}
		
		
	}
	
	function BuildDefaultParameters(){
		
		$usersmenus=new usersMenus(0,0,$this->hostname);
		writelogs("Building default settings....for $this->hostname",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		if($this->global_conf_array["visible_hostname"]==null){$this->global_conf_array["visible_hostname"]="proxyweb";}
		if($this->global_conf_array["dead_peer_timeout"]==null){$this->global_conf_array["dead_peer_timeout"]="10 seconds";}
		if($this->global_conf_array["dns_timeout"]==null){$this->global_conf_array["dns_timeout"]="2 minutes";}
		if($this->global_conf_array["connect_timeout"]==null){$this->global_conf_array["connect_timeout"]="1 minutes";}
		if($this->global_conf_array["peer_connect_timeout"]==null){$this->global_conf_array["peer_connect_timeout"]="3 minutes";}
		if($this->global_conf_array["request_body_max_size"]==null){$this->global_conf_array["request_body_max_size"]="5 MB";}
		if($this->global_conf_array["maximum_object_size"]==null){$this->global_conf_array["maximum_object_size"]="300 MB";}
		if($this->global_conf_array["cache_mem"]==null){$this->global_conf_array["cache_mem"]="8 MB";}
		if($this->global_conf_array["cache_swap_high"]==null){$this->global_conf_array["cache_swap_high"]="90";}
		if($this->global_conf_array["cache_swap_low"]==null){$this->global_conf_array["cache_swap_low"]="95";}
		if($this->global_conf_array["minimum_object_size"]==null){$this->global_conf_array["minimum_object_size"]="0 KB";}
		if($this->global_conf_array["maximum_object_size_in_memory"]==null){$this->global_conf_array["maximum_object_size_in_memory"]="8 KB";}
		if($this->global_conf_array["ipcache_size"]==null){$this->global_conf_array["ipcache_size"]=1024;}
		if($this->global_conf_array["ipcache_low"]==null){$this->global_conf_array["ipcache_low"]=90;}
		if($this->global_conf_array["ipcache_high"]==null){$this->global_conf_array["ipcache_high"]=95;}
		if($this->global_conf_array["fqdncache_size"]==null){$this->global_conf_array["fqdncache_size"]=1024;}
		
		if($usersmenus->DANSGUARDIAN_INSTALLED){
			$this->AddAclType('my_network','src','127.0.0.1/8');
			$this->AddAccessRule('http_access','deny',"!my_network");
			}
		
		
		
	}
	
	
	function BuildConfig(){
		
		$ldap=new clladp();
		
		
		if(!is_array($this->cache_dir_array)){
			writelogs("no array for cache_dir=use_default",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->cache_dir_array[]=array(
					"dir"=>"/var/spool/squid",
					"type"=>"diskd",
					"sizemb"=>500,
					"level1"=>32,
					"level2"=>512);
			}
		
		
		
		
		
		$users=new usersMenus();
		$conf=$conf . "#--------- LDAP AUTH settings\n";
		$conf=$conf . "\n#Authentification mode, building using squid compiled for artica\n";
		if(trim($users->SQUID_LDAP_AUTH)<>null){
			$conf=$conf . "auth_param basic program $users->SQUID_LDAP_AUTH -b \"$ldap->suffix\"";
			$conf=$conf . " -D \"cn=$ldap->ldap_admin,$ldap->suffix\" -w $ldap->ldap_password -f \"(&(objectClass=userAccount)(uid=%s))\" -v 3 -h 127.0.0.1\n";
			$conf=$conf . "external_acl_type ldap_group %LOGIN " . dirname($users->SQUID_LDAP_AUTH)."/squid_ldap_group -D \"cn=$ldap->ldap_admin,$ldap->suffix\"";
			$conf=$conf . " -w $ldap->ldap_password -b \"$ldap->suffix\"  -f \"(&(objectClass=posixGroup)(cn=%a)(memberUid=%v))\" -S -v 3 -h 127.0.0.1\n";
			
			$conf=$conf . "auth_param basic children 5\n";
			$conf=$conf . "auth_param basic realm Squid proxy-caching web server\n";
			$conf=$conf . "auth_param basic credentialsttl 2 hour\n";
			$conf=$conf . "authenticate_ttl 1 hour\n";
		}
			$conf=$conf . "authenticate_ip_ttl 60 seconds\n";
			$conf=$conf . "cache_effective_user squid\n";
			$conf=$conf . "cache_effective_group squid\n";
		
		
		
		writelogs("dns server: ".count($this->dns_nameservers_array) . ' rows',__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		if(is_array($this->dns_nameservers_array)){
			reset($this->dns_nameservers_array);
			$conf=$conf ."\n\n# --------- SPECIFIC DNS SERVERS \n";
			while (list ($num, $val) = each ($this->dns_nameservers_array) ){
				$conf=$conf . "dns_nameservers " . $val . "\n";
				
			}
		
		
		
		
		}
		
	    $conf=$conf ."\n\n# --------- ACL LISTS \n";
	    $conf=$conf . "acl src all 0.0.0.0/0.0.0.0\n";
	    $conf=$conf . $this->BuildAcls(); 
	    
		
	    if($users->DANSGUARDIAN_INSTALLED){
	    	/*
	    	$conf=$conf ."\n\n# --------- DansGuardian installed using x-forward to allow ips waiting squid 3.1\n";
	    	$conf=$conf ."follow_x_forwarded_for allow localhost\n";
			$conf=$conf ."acl_uses_indirect_client on\n";
			$conf=$conf ."delay_pool_uses_indirect_client on\n";
			$conf=$conf ."log_uses_indirect_client on\n";
			*/
	    	}
	    
	    $conf=$conf ."\n\n# --------- HTTP ACCESS \n";	
	    $conf=$conf .$this->BuildAccessRules('http_access');
	    
	    $conf=$conf ."\n\n# --------- http_reply_access \n";	
	    $conf=$conf .$this->BuildAccessRules('http_reply_access');

	    $conf=$conf ."\n\n# --------- icp_access \n";	
	    $conf=$conf .$this->BuildAccessRules('icp_access');	

	    $conf=$conf ."\n\n# --------- miss_access \n";	
	    $conf=$conf .$this->BuildAccessRules('miss_access');
	    if($this->SQUID_VERSION>2.6){	    
	   		$conf=$conf ."\n\n# --------- icap_service \n";	
	    	$conf=$conf .$this->BuildIcapServices();		
	    
			$conf=$conf ."\n\n# --------- icap_class \n";	
	    	$conf=$conf .$this->BuildIcapClass();		    
	    	
	    	$conf=$conf ."\n\n# --------- icap_access \n";	
	    	$conf=$conf .$this->BuildIcapAccessRules();	    
	    }
	    $conf=$conf ."\n\n# --------- ident_lookup_access \n";	
	    $conf=$conf .$this->BuildAccessRules('ident_lookup_access');	        
  	     
	    $conf=$conf ."hierarchy_stoplist cgi-bin ?\n";
		
		$conf=$conf ."\n\n# --------- General settings \n";	
		$conf=$conf ."visible_hostname " .$this->global_conf_array["visible_hostname"] . "\n";

		
		$conf=$conf ."\n\n# --------- time-out \n";	
		$conf=$conf ."dead_peer_timeout " .$this->global_conf_array["dead_peer_timeout"] . "\n";
		$conf=$conf ."dns_timeout " .$this->global_conf_array["dns_timeout"] . "\n";
		$conf=$conf ."connect_timeout " .$this->global_conf_array["connect_timeout"] . "\n";
		$conf=$conf ."peer_connect_timeout " .$this->global_conf_array["peer_connect_timeout"] . "\n";
		$conf=$conf ."\n\n# --------- limits \n";	
		$conf=$conf ."request_body_max_size " .$this->global_conf_array["request_body_max_size"] . "\n";
		$conf=$conf ."maximum_object_size " .$this->global_conf_array["maximum_object_size"] . "\n";
		
		
		$http_port_count=count($this->http_port_array);
		if(is_array($this->http_port_array)){reset($this->http_port_array);}
			
		writelogs("building http_port section... ($http_port_count) rows",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		if(is_array($this->http_port_array)){
			$conf=$conf ."\n\n#http ports\n";	
			while (list ($num, $val) = each ($this->http_port_array) ){
				writelogs("$num) building http_port section: $val",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$conf=$conf ."http_port $val\n";
				}
		}
		
		if($http_port_count==0){
			$conf=$conf ."http_port 3128\n";
		}
		
		
		
		if(is_array($this->https_port_array)){
			$conf=$conf ."\n\n#https ports\n";	
			while (list ($num, $val) = each ($this->https_port_array) ){	
			$conf=$conf ."https_port $val\n";
			
			}
		}

		
		writelogs("building cache_dir section...",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$conf=$conf ."\n\n# --------- Caches \n";	
		if(is_array($this->cache_dir_array)){
			while (list ($num, $val) = each ($this->cache_dir_array) ){	
				$cache_dir="cache_dir {$val["type"]} {$val["dir"]} {$val["sizemb"]} {$val["level1"]} {$val["level2"]}";
				writelogs("Build cache dir number $num $cache_dir",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$conf=$conf ."$cache_dir\n";
				
			}
		}			
		$conf=$conf ."cache_mem " .$this->global_conf_array["cache_mem"] . "\n";
		
		$conf=$conf ."debug_options ALL,1\n";
		//$conf=$conf ."cache deny QUERY\n";
		$conf=$conf ."refresh_pattern ^ftp:		1440	20%	10080\n";
		$conf=$conf ."refresh_pattern ^gopher:	1440	0%	1440\n";
		$conf=$conf ."refresh_pattern .		0	20%	4320\n";
		$conf=$conf ."icp_port 3130\n";
		
		
		if($this->SQUID_VERSION>2.6){
			if(!is_array($this->icap_service_array)){
				if(($users->KAV4PROXY_INSTALLED) OR ($users->C_ICAP_INSTALLED)){
					$conf=$conf ."\n\n# --------- ICAP Kaspersky/c-icap\n";	
					$conf=$conf ."icap_enable on\n";
					$conf=$conf ."icap_send_client_ip on\n";
		
				}
				}else{
					$conf=$conf ."icap_enable on\n";
					$conf=$conf ."icap_send_client_ip on\n";
			}
		}
		
		
			
		$conf=$conf ."\n\n#Logs-------------------------------------------------\n";	
		$conf=$conf ."emulate_httpd_log on\n";
		$conf=$conf ."coredump_dir	/var/squid/cache\n";
		$conf=$conf ."cache_store_log	/var/log/squid/store.log\n";
		$conf=$conf ."cache_log	/var/log/squid/cache.log\n";
		$conf=$conf ."pid_filename	/var/run/squid.pid\n";
		$conf=$conf ."access_log	/var/log/squid/access.log\n";
		$this->squid_conf=$conf;
		return $conf;
		
		
		
	}
	
	function SaveToLdap(){
		$conf=$this->BuildConfig();
		$sock=new sockets();
		$sock->SaveConfigFile($conf,GlobalSquidConf);
		
		$upd["GlobalSquidConf"][0]=$conf;
		$upd["ArticaEnableKav4ProxyInSquid"][0]=$this->EnableKav4Proxy;
		
		$ldap=new clladp();
		writelogs("Save config file to $this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$ldap->Ldap_modify($this->dn,$upd);
		
		}
		
	function SaveToServer(){
		$this->SaveToLdap();
		$sock=new sockets();
		$sock->getFrameWork('cmd.php?squid-reconfigure=yes');
	}
		
		
	
	function ParseACL(){
		$ldap=new clladp();
		$dn="cn=acls,$this->dn";
		writelogs("find in $dn" ,__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sr=@ldap_search($ldap->ldap_connection,$dn,"(ObjectClass=SquidProxyAclsRule)",array("cn","SquidProxyAclsType","AclRuleEnabled","AclDatas"));
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
			if(count($hash)<2){$this->BuildAclDefaults();$this->ParseACL();}
		}else{
			writelogs("unable to find in $dn" ,__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->BuildAclDefaults();
			return;}
	
		writelogs("find {$hash["count"]} entries" ,__CLASS__.'/'.__FUNCTION__,__FILE__);
		for($i=0;$i<$hash["count"];$i++){
			
			unset($AclDatas);
			for($z=0;$z<$hash[$i][strtolower('AclDatas')]["count"];$z++){
				//writelogs("{$hash[$i]["cn"][0]} {$hash[$i][strtolower("SquidProxyAclsType")][0]} {$hash[$i][strtolower('AclDatas')][$z]}" ,__CLASS__.'/'.__FUNCTION__,__FILE__);
				$AclDatas[]=$hash[$i][strtolower('AclDatas')][$z];
			}
			
			
			$this->acls_rules_array[$hash[$i]["cn"][0]]=array(
									"acl_type"=>$hash[$i][strtolower("SquidProxyAclsType")][0],
									"enabled"=>$hash[$i][strtolower("AclRuleEnabled")][0],
									"datas"=>$AclDatas);
			}
		
		}
		
		
		function Parse_restrictions(){
			$dn="$this->dn";
			$ldap=new clladp();
			$sr=@ldap_search($ldap->ldap_connection,$dn,"(ObjectClass=SquidProxyAccessRule)",array());
			if ($sr) {
				$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
				for($i=0;$i<$hash["count"];$i++){
					$rule=$hash[$i]["cn"][0];
					unset($rules);
					for($z=0;$z<$hash[$i]["aclrulename"]["count"];$z++){
						$rules[]=$hash[$i]["aclrulename"][$z];
						
					}
					$this->restrictions_array[$rule]=array(
							"enabled"=>$hash[$i]["accessruleenabled"][0],
							"rules"=>$rules
						);
				}
			}
		}
		
		
	function Restrictions_switch($rulenname,$index){
		writelogs("change $rulenname [$index]",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$ruledatas=$this->restrictions_array[$rulenname]["rules"][$index];
		writelogs("Rule datas=$ruledatas",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		
		$ruledatas_table=explode(' ',$ruledatas);
		if($ruledatas_table[0]=='allow'){
			$ruledatas_table[0]='deny';
			}else{
				$ruledatas_table[0]='allow';
			}
		writelogs("change value $ruledatas to " . implode(' ',$ruledatas_table) ,__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->restrictions_array[$rulenname]["rules"][$index]=implode(' ',$ruledatas_table);
			while (list ($num, $val) = each ($this->restrictions_array[$rulenname]["rules"]) ){
				$upd["AclRuleName"][$num]=$val;
			}
		$dn="cn=$rulenname,cn=access,$this->dn";
		$ldap=new clladp();
		$ldap->Ldap_modify($dn,$upd);
		$this->SaveToLdap();
	}
	
	
	function Restrictions_move($rulenname,$index,$move){
		$ruledatas=$this->restrictions_array[$rulenname]["rules"][$index];
		$rules=$this->restrictions_array[$rulenname]["rules"];
		
		writelogs("move $rulenname($index) =>$ruledatas $move in " . count($rules) . " elements" ,__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		$rules_new=array_move_element($rules,$ruledatas,$move);
		while (list ($num, $val) = each ($rules_new) ){
				$upd["AclRuleName"][]=$val;
			}
		$dn="cn=$rulenname,cn=access,$this->dn";
		$ldap=new clladp();
		$ldap->Ldap_modify($dn,$upd);
		$this->SaveToLdap();
	}
	
	function Restrictions_index($rulename,$ruledatasrex){
		
		$ruledatas=$this->restrictions_array[$rulename]["rules"];
		if(!is_array($ruledatas)){return null;}
		while (list ($num, $val) = each ($ruledatas) ){
			if(preg_match("#$ruledatasrex#i",$val)){
				return $num;
			}
		}
		
		
		
	}
	
	
	function Restrictions_moveindex($rulename,$fromindex,$toindex){
		if($fromindex==$toindex){return false;}
		$rules=$this->restrictions_array[$rulename]["rules"];
		$ruledatas=$this->restrictions_array[$rulename]["rules"][$fromindex];
		
		$rules_new=array_move_position($rules,$fromindex,$toindex);
		writelogs("move $rulename [$fromindex]=$ruledatas $move from pos $fromindex to $toindex in " . count($rules) . " elements return [" . count($rules_new) . "] elements" ,
		__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->Restriction_SaveToLdap($rulename);
	}
	

function Restrictions_Delete($rulenname,$index){
		writelogs("Delete $rulenname [$index]",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$ruledatas=$this->restrictions_array[$rulenname]["rules"][$index];
		writelogs("Rule datas=$ruledatas",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$upd["AclRuleName"]=$ruledatas;
		$ldap=new clladp();
		$dn="cn=$rulenname,cn=access,$this->dn";
		$ldap->Ldap_del_mod($dn,$upd);
		$this->SaveToLdap();
		}
		
function Restriction_Add_new($rulename,$value){
	if(is_array($this->restrictions_array)){
		reset($this->restrictions_array);
		$this->restrictions_array[$rulename]["rules"][]=$value;
		$this->Restriction_SaveToLdap($rulename);
		}
	}

function Restriction_SaveToLdap($rulename){
	
	$RULES=$this->restrictions_array[$rulename]["rules"];
	if(!is_array($RULES)){return null;}
	reset($RULES);
	writelogs("[$rulename] -> " . count($RULES) . " rows",__CLASS__.'/'.__FUNCTION__,__FILE__);
	
	while (list ($num, $val) = each ($RULES) ){
		writelogs("RULE [$val]",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if($val<>null){
			if($this->AclExists($val)){
				if(preg_match('#(allow|deny)#',$val)){
					$T[trim($val)]=$num;
				}
				}else{writelogs("$val as no acls, skip it",__CLASS__.'/'.__FUNCTION__,__FILE__);}
			}else{writelogs("[$num] is null",__CLASS__.'/'.__FUNCTION__,__FILE__);}
	}
	
	while (list ($num, $val)= each ($T) ){
		writelogs("AclRuleName($val)=\"$num\"",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$upd["AclRuleName"][]=$num;
	}
	
	$dn="cn=$rulename,cn=access,$this->dn";
	$ldap=new clladp();
	$ldap->Ldap_modify($dn,$upd);
	$this->SaveToLdap();	
}

function AclExists($line){
		
	
	
		if(!preg_match('#(allow|deny)\s+(.+)#',$line,$re)){
			writelogs("unable to extract patterns #(allow|deny)\s+(.+)# in $line",__CLASS__.'/'.__FUNCTION__,__FILE__);	
			return false;
		}
		
		if(preg_match('#(allow|deny)\s+all#',$line)){
			return true;
		}
		
		
		
		$line=trim($re[2]);
		if(substr($line,0,1)=='!'){
				$line=substr($line,1,strlen($line));
				}
		
		$RULES=$this->acls_rules_array;
		reset($RULES);
		while (list ($num, $val) = each ($RULES) ){
			if(trim(strtolower($num))==trim(strtolower($line))){
				reset($RULES);
				return true;}
		
			}
		
		writelogs("rule $line as no acls",__CLASS__.'/'.__FUNCTION__,__FILE__);	
		return false;
	}
		
function Restrictions_add_acl($rulename,$index,$value){
		$ruledatas=$this->restrictions_array[$rulename]["rules"][$index];
		$ruledatas=$ruledatas . " " . $value;
		$this->restrictions_array[$rulename]["rules"][$index]=$ruledatas;
		$this->Restriction_SaveToLdap($rulename);
		}
		
function Restrictions_insert_acl($rulename,$index,$value){	
		writelogs("Insert in  $rulename at pos [$index]+1 ($value)",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$array_src=$this->restrictions_array[$rulename]["rules"];
		$dn="cn=$rulename,cn=access,$this->dn";
		writelogs("Array as ".count($array_src) . " rows",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(count($array_src)>0){
			$newpos=$index+2;
			$new_array=array_insert($array_src,$newpos,"allow $value");
		}else{
			$this->AddAccessRule($rulename,'allow',$value);
			return null;
		}
		while (list ($num, $val) = each ($new_array) ){$upd["AclRuleName"][]=$val;}
		
		$ldap=new clladp();
		$ldap->Ldap_modify($dn,$upd);
		$this->SaveToLdap();	
		}
//-----------------------------------------------------------------------------------------------------		
function Restriction_delete_acl($rulename,$ruleindex,$aclindex){
	writelogs("delete in  $rulename at pos [$ruleindex] acl ($aclindex)",__CLASS__.'/'.__FUNCTION__,__FILE__);
	$main_array=$this->restrictions_array[$rulename]["rules"];
	$val=$main_array[$ruleindex];
	writelogs("acl=$val",__CLASS__.'/'.__FUNCTION__,__FILE__);
	
			if(preg_match('#^allow#',$val)){
					$debut="allow";
					$val=substr($val,5,strlen($val));
				}
				
				if(preg_match('#^deny#',$val)){
					$debut="deny";
					$val=substr($val,4,strlen($val));
				}
				
	$val=trim($val);
	$table=explode(" ",$val);
		while (list ($num, $line) = each ($table)){
			if(trim($line)<>null){
				writelogs("acl=$line",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$arr[]=$line;
			}
			
		}
	writelogs("unset acl {$arr[$aclindex]}",__CLASS__.'/'.__FUNCTION__,__FILE__);
	unset($arr[$aclindex]);
	if(count($arr)==0){
		$this->Restrictions_Delete($rulename,$ruleindex);
		return null;
	}
	
	$main_array[$ruleindex]=$debut . " " . implode(' ',$arr);
	while (list ($num, $val) = each ($main_array) ){$upd["AclRuleName"][]=$val;}	
	$dn="cn=$rulename,cn=access,$this->dn";
	$ldap=new clladp();
	$ldap->Ldap_modify($dn,$upd);
	$this->SaveToLdap();	
	
}
		
//-----------------------------------------------------------------------------------------------------		
	 	
	 function EditAclEnable($rulename,$enable){
	 	$dn="cn=$rulename,cn=acls,$this->dn";
	 	$upd["AclRuleEnabled"][0]=$enable;
		$ldap=new clladp();
		$ldap->Ldap_modify($dn,$upd);	 	
		$this->SaveToLdap();
	 }
	 
	 
//-----------------------------------------------------------------------------------------------------
	 function BuildAcls(){
	 	if(!is_array($this->acls_rules_array)){return null;}
	 	reset($this->acls_rules_array);
	 	
	 	
		while (list ($num, $val) = each ($this->acls_rules_array) ){
			if($val["enabled"]=='yes'){
				
				if($num=="Safe_ports"){
					while (list ($index, $acl) = each ($val["datas"])){
						$line=$line . "acl Safe_ports port $acl\n";
					}
				continue;}
				
				if($val["acl_type"]=="urlpath_regex"){
					while (list ($index, $acl) = each ($val["datas"])){
						$line=$line . "acl $num {$val["acl_type"]} $acl\n";
					}
				continue;}
				
				if($val["acl_type"]=="rep_mime_type"){
					while (list ($index, $acl) = each ($val["datas"])){
						$line=$line . "acl $num {$val["acl_type"]} $acl\n";
					}
				continue;}				
				
				
				
				
				
	 			$line=$line . "acl $num {$val["acl_type"]} " . implode(' ',$val["datas"])."\n";
			}
		
	 	}
	 return $line;}
//-----------------------------------------------------------------------------------------------------		
	function BuildAccessRules($key_name){
		
		$ruledatas=$this->restrictions_array[$key_name]["rules"];
		 
		if(!is_array($ruledatas)){return null;}
		while (list ($num, $val) = each ($ruledatas)){
			$val=$this->_BuildAccessRulesAcls($val);
			if($val<>null){
				$T[$conf .$key_name. " ". $val]=$num;
			}
			
		}
		while (list ($val,$num ) = each ($T)){
			$conf=$conf."$val\n";
		}
		$conf=$conf."$key_name deny all";
		return $conf;
		
	}
//-----------------------------------------------------------------------------------------------------	
	function BuildIcapClass(){
		$user=new usersMenus();
		if(!$user->SQUID_ICAP_ENABLED){return null;}
		if(!is_array($this->icap_class_array)){return null;}
		reset($this->icap_class_array);
		while (list ($num, $val) = each ($this->icap_class_array)){
			$conf=$conf . "icap_class $num ".implode(" ",$val)."\n";
			
		}
		return $conf;
		
	}
//-----------------------------------------------------------------------------------------------------	
	function BuildIcapServices(){
	$user=new usersMenus(null,0,$this->hostname);
	$kav_default="icap_service is_kav_resp respmod_precache 0 icap://127.0.0.1:1344/av/respmod";	
	if(!$user->SQUID_ICAP_ENABLED){return null;}
		if(!is_array($this->icap_service_array)){
			writelogs("array is empty...",__CLASS__.'/'.__FUNCTION__,__FILE__);
			if($user->KAV4PROXY_INSTALLED==true){
					if($this->EnableKav4Proxy==1){
						writelogs("Kav exists and service is not added, add it: $kav_default",__CLASS__.'/'.__FUNCTION__,__FILE__);
						$conf=$conf ."$kav_default\n";
					}}
			return $conf;
		}
		
		reset($this->icap_service_array);
		$found=false;
		while (list ($num, $val) = each ($this->icap_service_array) ){
			
			if($val["name"]=="is_kav_resp"){
				$found=true;
				if($user->KAV4PROXY_INSTALLED==true){
					if($this->EnableKav4Proxy==1){
						if($user->SQUID_ICAP_ENABLED){
							writelogs("icap_service={$val["name"]} enable kaspersky in squid",__CLASS__.'/'.__FUNCTION__,__FILE__);
							$conf=$conf ."icap_service {$val["name"]} {$val["vpoint"]} {$val["bypass"]} icap://{$val["url"]}\n";
							}else{
								writelogs("icap_service={$val["name"]} disabled kaspersky in squid",__CLASS__.'/'.__FUNCTION__,__FILE__);
								}
					}
				}
			}else{
				if($user->SQUID_ICAP_ENABLED){
					$conf=$conf ."icap_service {$val["name"]} {$val["vpoint"]} {$val["bypass"]} icap://{$val["url"]}\n";
				}
			}
			}
			
			if($found==false){
				if($user->KAV4PROXY_INSTALLED==true){
					if($user->SQUID_ICAP_ENABLED){
						if($this->EnableKav4Proxy==1){
							writelogs("Kav exists and service is not added, add it: $kav_default",__CLASS__.'/'.__FUNCTION__,__FILE__);
							$conf=$conf ."$kav_default\n";
					}}}}
			
		return $conf;		
		}
//-----------------------------------------------------------------------------------------------------	
	function AccessRules_moveto_position($main_access,$ruledatas,$position){
		$index=$this->Restrictions_index($main_access,$ruledatas);
		
		if($index==null){
			$this->Restriction_Add_new($main_access,$ruledatas);
			$index=$this->Restrictions_index($main_access,$ruledatas);
		}
		writelogs("$main_access $ruledatas index='$index'",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->Restrictions_moveindex($main_access,$index,$position);
		
	}
	
	function SimpleModeReorgRules(){
		writelogs("Reorganize rules....",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->AccessRules_moveto_position("http_access","allow manager localhost",0);		
		$this->AccessRules_moveto_position("http_access","deny manager",1);
		$this->AccessRules_moveto_position("http_access","deny !Safe_ports",2);
		$this->AccessRules_moveto_position("http_access","deny manager",3);
		$this->AccessRules_moveto_position("http_access","allow group_password",4);		
		$this->AccessRules_moveto_position("http_access","deny CONNECT !SSL_ports",5);
		$this->AccessRules_moveto_position("http_access","allow localhost",6);
		$this->AccessRules_moveto_position("http_access","allow to_localhost",7);		
		$this->AccessRules_moveto_position("http_access","deny deny_ext",8);
		$this->AccessRules_moveto_position('http_access',"allow my_network",9);		
		$this->SaveToLdap();
		
		
		
	}
	

	function _BuildAccessRulesAcls($line_acl){
		
		if(preg_match('#acl_kav_GET#',$line_acl)){
			if($this->SQUID_VERSION==2.6){return null;}
		}
		
		
		
		if(preg_match('#^allow#',$line_acl)){
					$debut="allow";
					$line_acl=substr($line_acl,5,strlen($line_acl));
				}
				
				if(preg_match('#^deny#',$line_acl)){
					$debut="deny";
					$line_acl=substr($line_acl,4,strlen($line_acl));
				}
				
			$line_acl=trim($line_acl);
			
						
			$table=explode(" ",$line_acl);
				while (list ($num, $line) = each ($table)){
					if(trim($line)<>null){
						
							if(substr($line,0,1)=='!'){$acl_name=substr($line,1,strlen($line));}else{$acl_name=$line;}
							
						if($this->acls_rules_array[$acl_name]["enabled"]=='yes' or $line=='all'){
							$arr[]=$line;
						}
					}
					
				}
	if(count($arr)>0){
		return $debut . " " . implode(' ',$arr);
	}
		
	}
	
//-----------------------------------------------------------------------------------------------------	

	function icap_add_service_in_class($class_name,$service_name){
		$this->Add_icap_class($class_name,$service_name);
		$this->SaveToLdap();
		}
//-----------------------------------------------------------------------------------------------------			
	function icap_del_service_in_class($class_name,$index){
		$main=trim($this->icap_class_array[$class_name]);
		
		$value=$this->icap_class_array[$class_name][$index];
		$this->Del_icap_class($class_name,$value);
		$this->SaveToLdap();		
		}
//-----------------------------------------------------------------------------------------------------
	function icap_add_restrictions_rule($class_name,$switch,$acl,$isnot){
			if($isnot=="yes"){$isnot='!';}else{$isnot='';}
			$line="$class_name $switch $isnot$acl";
			$ldap=new clladp();
			$dn="cn=icap_access,cn=access,$this->dn";
			if(!$ldap->ExistsDN($dn)){
				writelogs("Building rule $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);		
				$upd["ObjectClass"][]='top';
				$upd["ObjectClass"][]='SquidProxyAccessRule';
				$upd["cn"][]='icap_access';
				$upd["AccessRuleEnabled"][]='yes';
				$ldap->ldap_add($dn,$upd);
				unset($upd);
				}
			writelogs("building ldap rule add \"$class_name $switch $isnot$acl\" in $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$upd["AclRuleName"]="$class_name $switch $isnot$acl";
			$ldap->Ldap_add_mod($dn,$upd);
			$this->SaveToLdap();
	}
//-----------------------------------------------------------------------------------------------------	
	function icap_insert_restrictions_rule($class_name,$switch,$acl,$isnot,$index){
			if($isnot=="yes"){$isnot='!';}else{$isnot='';}
			$line="$class_name $switch $isnot$acl";
			$array_src=$this->restrictions_array["icap_access"]["rules"];
			$dn="cn=icap_access,cn=access,$this->dn";
		
			writelogs("Array as ".count($array_src) . " rows",__CLASS__.'/'.__FUNCTION__,__FILE__);
			if(count($array_src)>0){
				$newpos=$index+2;
				$new_array=array_insert($array_src,$newpos,$line);
			}else{
				$this->icap_add_restrictions_rule($class_name,$switch,$acl,$isnot);
				return null;
			}
			while (list ($num, $val) = each ($new_array) ){$upd["AclRuleName"][]=$val;}
		
			$ldap=new clladp();
			$ldap->Ldap_modify($dn,$upd);			
			$this->SaveToLdap();
	}
//-----------------------------------------------------------------------------------------------------	
   function icap_restrictions_switch($icap_rule_index){
   		$datas=$this->restrictions_array["icap_access"]["rules"][$icap_rule_index];
   		writelogs("datas=$datas",__CLASS__.'/'.__FUNCTION__,__FILE__);	
   		
   		if(preg_match('#^(.+?)\s+(allow|deny)\s+(.+)#i',$datas,$re)){
   			writelogs("datas={$re[2]}",__CLASS__.'/'.__FUNCTION__,__FILE__);	
   			if($re[2]=='allow'){$allow='deny';}
   			if($re[2]=='deny'){$allow='allow';}
   			$this->restrictions_array["icap_access"]["rules"][$icap_rule_index]="{$re[1]} $allow {$re[3]}";
   			writelogs("new datas={$this->restrictions_array["icap_access"]["rules"][$icap_rule_index]}",__CLASS__.'/'.__FUNCTION__,__FILE__);	
   		}
   		$ldap=new clladp();
		$dn="cn=icap_access,cn=access,$this->dn";
		while (list ($num, $line) = each ($this->restrictions_array["icap_access"]["rules"])){
			$upd["AclRuleName"][]=$line;
			
		}
   		$ldap->Ldap_modify($dn,$upd);
   		$this->SaveToLdap();
   	
   }
//-----------------------------------------------------------------------------------------------------	   
	function icap_restrictions_addacl($acl,$isnot,$index){
			if($isnot=="yes"){$isnot='!';}else{$isnot='';}
			$lineToAdd="$isnot$acl";
			$this->restrictions_array["icap_access"]["rules"][$index]=$this->restrictions_array["icap_access"]["rules"][$index] . ' ' .$lineToAdd;
			$dn="cn=icap_access,cn=access,$this->dn";
			while (list ($num, $val) = each ($this->restrictions_array["icap_access"]["rules"]) ){$upd["AclRuleName"][]=$val;}
			$ldap=new clladp();
			$ldap->Ldap_modify($dn,$upd);			
			$this->SaveToLdap();
	}
//-----------------------------------------------------------------------------------------------------

function icap_restrictions_delacl($index,$acl_index){
		$line_rule=$this->restrictions_array["icap_access"]["rules"][$index];
		if(preg_match('#^(.+?)\s+(allow|deny)\s+(.+)#i',$line_rule,$re)){
			$acl=$re[3];
		}
		
		$tbl=explode(' ',$re[3]);
		if(!is_array($tbl)){return null;}
		while (list ($num, $line) = each ($tbl)){
			if(trim($line)<>null){
				$src[]=trim($line);
			}
			
		}
		unset($src[$acl_index]);
		if(count($src)==0){
			$this->icap_restrictions_delete($index);
			return null;
		}
		$this->restrictions_array["icap_access"]["rules"][$index]="{$re[1]} {$re[2]} " . implode($src);
		while (list ($num, $val) = each ($this->restrictions_array["icap_access"]["rules"]) ){$upd["AclRuleName"][]=$val;}
		$ldap=new clladp();
		$dn="cn=icap_access,cn=access,$this->dn";
		$ldap->Ldap_modify($dn,$upd);			
		$this->SaveToLdap();		
		
	}
//-----------------------------------------------------------------------------------------------------	
	function icap_restrictions_delete($index){
		unset($this->restrictions_array["icap_access"]["rules"][$index]);
		while (list ($num, $val) = each ($this->restrictions_array["icap_access"]["rules"]) ){$upd["AclRuleName"][]=$val;}
		$ldap=new clladp();
		$dn="cn=icap_access,cn=access,$this->dn";
		$ldap->Ldap_modify($dn,$upd);			
		$this->SaveToLdap();				
		}
//-----------------------------------------------------------------------------------------------------	
	function icap_restrictions_move($index,$move){
		$this->restrictions_array["icap_access"]["rules"]=array_move_element($this->restrictions_array["icap_access"]["rules"],$this->restrictions_array["icap_access"]["rules"][$index],$move);
		while (list ($num, $val) = each ($this->restrictions_array["icap_access"]["rules"]) ){$upd["AclRuleName"][]=$val;}
		$ldap=new clladp();
		$dn="cn=icap_access,cn=access,$this->dn";
		$ldap->Ldap_modify($dn,$upd);			
		$this->SaveToLdap();	
	}

//-----------------------------------------------------------------------------------------------------	
	function AddCache($cache_dir,$cache_size,$cache_type,$cache_dir_level1=null,$cache_dir_level2=null){
		$index=null;
		writelogs("save $cache_dir,$cache_size,$cache_type,$cache_dir_level1,$cache_dir_level2",__CLASS__ . '/'. __FUNCTION__,__FILE__);
		$cache_dir=str_replace(' ','_',$cache_dir);
		if(is_array($this->cache_dir_array)){
			while (list ($num, $line) = each ($this->cache_dir_array)){if($cache_dir==$line["dir"]){$index=$num;}}
		reset($this->cache_dir_array);}
		
		if($cache_dir_level1==null){$cache_dir_level1=16;}
		if($cache_dir_level2==null){$cache_dir_level2=256;}
		
		$this->cache_dir_array[$index]=array(
										"dir"=>$cache_dir,
										"type"=>$cache_type,
										"sizemb"=>$cache_size,
										"level1"=>$cache_dir_level1,
										"level2"=>$cache_dir_level2);
					
		$this->SaveToLdap();
	
	}
//-----------------------------------------------------------------------------------------------------		
	function DeleteCacheDir($index){
		writelogs("Delete Cache index number $index",__CLASS__ . '/'. __FUNCTION__,__FILE__);
		unset($this->cache_dir_array[$index]);
		$this->SaveToLdap();
		
	}
//-----------------------------------------------------------------------------------------------------	

	function is_rules_exists($access_rule_name,$acl_name){
		$access_rules=$this->restrictions_array[$access_rule_name]["rules"];
		if(!is_array($access_rules)){return false;}
		while (list ($num, $line) = each ($access_rules)){
			if(preg_match("#$acl_name#i",$line)){
				return true;
				}
		}
		return false;
		}



}



class squidbee{
	var $dn;
	var $network_array=array();
	var $dns_array=array();
	var $squid_conf;
	var $ArticaSquidParameters;
	var $ldap_error;
	var $listen_port;
	var $visible_hostname;
	var $enable_kavproxy;
	var $enable_cicap;
	var $enable_dansguardian;
	var $enable_squidguard;
	var $LDAP_AUTH=0;
	var $NTLM_AUTH=0;

	var $CACHE_SIZE=2000;
	var $CACHE_PATH;
	var $alt_listen_port;
	var $kav_accept=false;
	var $kav_accept_why='';
	var $SquidBlockSites='';
	var $array_block_istes=array();
	var $global_conf_array=array();
	var $cache_list=array();
	var $acl_times=array();
	var $hasProxyTransparent;
	var $ASROOT=false;
	var $SQUID_BIN_VERSION=0;
	var $SQUID_ICAP_ENABLED=false;
	var $SQUID_VERSION="";
	var $KAV4PROXY_INSTALLED=false;
	
	
	function squidbee(){
		$this->VerifyLdapBranch();
		$this->ParseConfig();
		$this->kav_accept=$this->isicap();
		$sock=new sockets();
		
		$users=new usersMenus();
		$this->SQUID_BIN_VERSION=$users->SQUID_BIN_VERSION;
		$this->SQUID_VERSION=$users->SQUID_VERSION;
		$this->SQUID_ICAP_ENABLED=$users->SQUID_ICAP_ENABLED;
		$this->KAV4PROXY_INSTALLED=$users->KAV4PROXY_INSTALLED;
		$this->SquidAcountUpdate();
		
		$this->hasProxyTransparent=$sock->GET_INFO("hasProxyTransparent");
		if($this->hasProxyTransparent==null){$this->hasProxyTransparent=0;}
		if(posix_getuid()==0){$this->ASROOT=true;}
	}
	
	
	function isicap(){
		
		if(preg_match('#([0-9\.]+)#',$this->SQUID_VERSION,$re)){$this->SQUID_VERSION=$re[1];}
		if($this->SQUID_VERSION>2.6){
			if($this->SQUID_ICAP_ENABLED){
				if($this->KAV4PROXY_INSTALLED){
					return true;
				}else{
					$this->kav_accept_why='{error_kavproxy_not_installed}';
				}
			}else{
				$this->kav_accept_why='{error_squid_icap_not_compliance}';
			}
		}else{
			$this->kav_accept_why='{error_squid_upto_three}<br><strong>'.$this->SQUID_VERSION."</strong>";
		}
	}
	
	function VerifyLdapBranch(){
		$sock=new sockets();
		$this->squid_conf=$sock->GET_INFO('GlobalSquidConf');
		if($this->squid_conf==null){
			$this->squid_conf=base64_decode($sock->getFrameWork('cmd.php?squid-GetOrginalSquidConf'));
		}
		
		$this->ArticaSquidParameters=$sock->GET_INFO('ArticaSquidParameters');
		$this->SquidBlockSites=$sock->GET_INFO('SquidBlockSites');
		$this->EnableKav4Proxy=$sock->GET_INFO('ArticaEnableKav4ProxyInSquid');
		
		}
	
	
	function ParseConfig(){
		$ini=new Bs_IniHandler();
		$sock=new sockets();
		$ini->loadString($this->ArticaSquidParameters);
		$network=explode(';',$ini->_params["NETWORK"]["cdir"]);
		$dns=explode(';',$ini->_params["NETWORK"]["dns_servers"]);
		$this->listen_port=$ini->_params["NETWORK"]["LISTEN_PORT"];
		$this->visible_hostname=$ini->_params["NETWORK"]["visible_hostname"];
		$this->LDAP_AUTH=$ini->_params["NETWORK"]["LDAP_AUTH"];
		$this->NTLM_AUTH=$ini->_params["NETWORK"]["NTLM_AUTH"];
		
		$this->enable_kavproxy=$ini->_params["KAV"]["enabled"];
		$this->enable_dansguardian=$ini->_params["DANSGUARDIAN"]["enable_dansguardian"];
		$this->alt_listen_port=$ini->_params["NETWORK"]["ALT_PORT"];
		$this->CACHE_SIZE=$ini->_params["CACHE"]["CACHE_SIZE"];
		$this->CACHE_PATH=$ini->_params["CACHE"]["CACHE_PATH"];
		$this->enable_cicap=$sock->GET_INFO('CicapEnabled');
		$this->enable_squidguard=$sock->GET_INFO('squidGuardEnabled');
		
		if($this->enable_cicap==null){$this->enable_cicap=0;}
		if(is_array($ini->_params)){
		while (list ($num, $val) = each ($ini->_params)){
			if(preg_match('#cache:(.+)#',$num,$re)){
				$this->cache_list[$re[1]]=array(
				"cache_type"=>$ini->_params[$num]["cache_type"],
				"cache_dir_level1"=>$ini->_params[$num]["cache_dir_level1"],
				"cache_dir_level2"=>$ini->_params[$num]["cache_dir_level2"],
				"cache_size"=>$ini->_params[$num]["cache_size"],
				);
			}
			
			if(preg_match('#time:([0-9]+):(.+)#',$num,$re)){
				while (list ($a, $b) = each ($ini->_params[$num])){
					$this->acl_times[$num][$a]=$b;
				}
			
				
			}
		}
		}
		
		
		if($this->listen_port==null){$this->listen_port=3128;}
		if($this->visible_hostname==null){$this->visible_hostname="proxyweb";}
		if($this->LDAP_AUTH==null){$this->LDAP_AUTH=0;}
		if($this->NTLM_AUTH==null){$this->NTLM_AUTH=0;}
		if($this->enable_kavproxy==null){$this->enable_kavproxy=0;}
		if($this->enable_dansguardian==null){$this->enable_dansguardian=0;}
		if($this->CACHE_SIZE==null){$this->CACHE_SIZE=2000;}
		if($this->CACHE_PATH==null){$this->CACHE_PATH="/var/cache/squid";}
		
		
		while (list ($num, $cidr) = each ($network)){
			if(trim($cidr)<>null){
				$this->network_array[]=$cidr;
			}
			
		}
		
		while (list ($num, $serv) = each ($dns)){
			if(trim($serv)<>null){
				$this->dns_array[]=$serv;
			}
			
		}	
		
		if($this->squid_conf==null){$this->squid_conf=$sock->GET_INFO("GlobalSquidConf");}
		$tb=explode("\n",$this->squid_conf);
		
		while (list ($num, $val) = each ($tb)){
		if(preg_match('#^([a-z\_]+)\s+(.+)#',$val,$re)){
				if($re[1]=="acl"){continue;}
				if($re[1]=="http_access"){continue;}
				if($re[1]=="auth_param"){continue;}
				$this->global_conf_array[$re[1]]=$re[2];
			}
		}	
		
		
		if($this->global_conf_array["dead_peer_timeout"]==null){$this->global_conf_array["dead_peer_timeout"]="10 seconds";}
		if($this->global_conf_array["dns_timeout"]==null){$this->global_conf_array["dns_timeout"]="2 minutes";}
		if($this->global_conf_array["connect_timeout"]==null){$this->global_conf_array["connect_timeout"]="1 minutes";}
		if($this->global_conf_array["peer_connect_timeout"]==null){$this->global_conf_array["peer_connect_timeout"]="3 minutes";}
		if($this->global_conf_array["request_body_max_size"]==null){$this->global_conf_array["request_body_max_size"]="5 MB";}
		if($this->global_conf_array["maximum_object_size"]==null){$this->global_conf_array["maximum_object_size"]="300 MB";}
		if($this->global_conf_array["cache_mem"]==null){$this->global_conf_array["cache_mem"]="8 MB";}
		if($this->global_conf_array["cache_swap_high"]==null){$this->global_conf_array["cache_swap_high"]="90";}
		if($this->global_conf_array["cache_swap_low"]==null){$this->global_conf_array["cache_swap_low"]="95";}
		if($this->global_conf_array["minimum_object_size"]==null){$this->global_conf_array["minimum_object_size"]="0 KB";}
		if($this->global_conf_array["maximum_object_size_in_memory"]==null){$this->global_conf_array["maximum_object_size_in_memory"]="8 KB";}
		if($this->global_conf_array["ipcache_size"]==null){$this->global_conf_array["ipcache_size"]=1024;}
		if($this->global_conf_array["ipcache_low"]==null){$this->global_conf_array["ipcache_low"]=90;}
		if($this->global_conf_array["ipcache_high"]==null){$this->global_conf_array["ipcache_high"]=95;}
		if($this->global_conf_array["fqdncache_size"]==null){$this->global_conf_array["fqdncache_size"]=1024;}
		if($this->global_conf_array["request_header_max_size"]==null){$this->global_conf_array["request_header_max_size"]="64 KB";}
		if($this->global_conf_array["request_body_max_size"]==null){$this->global_conf_array["request_body_max_size"]="0";}
		if($this->global_conf_array["reply_body_max_size"]==null){$this->global_conf_array["reply_body_max_size"]="0";}
		writelogs("Parsing squid.conf ". strlen($this->squid_conf)." bytes was ". count($tb). " lines.. ". count($this->global_conf_array)." parameters",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		
		
	}
	
	
	function SquidAcountUpdate(){
		$ldap=new clladp();
		$dn="cn=squidinternalauth,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="userAccount";
			$upd["cn"][0]="squidinternalauth";
			$upd["userid"][0]="squidinternalauth";
			$upd["accountActive"][0]="TRUE";
			$upd["accountgroup"][0]="1000";
			$upd["domainname"][0]="nodomain";
			$upd["domainname"][0]="nodomain";
			$upd["homedirectory"][0]="/dev/null";
			$upd["maildir"][0]="/dev/null";
			$upd["sn"][0]="squidinternalauth";	
			$upd["userpassword"][0]=$ldap->ldap_password;
			if(!$ldap->ldap_add($dn,$upd)){
				if(posix_getuid()==0){
					echo "SquidAcountUpdate():: error $ldap->ldap_last_error\n";
				}
			}
		}else{
			$upd["userpassword"][0]=$ldap->ldap_password;
			$ldap->Ldap_modify($dn,$upd);
		}
	}
	
	
	
	function BuildBlockedSites(){
			$sql="SELECT uri FROM squid_block ORDER BY ID DESC";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){
				if(posix_getuid()==0){echo "Starting......: Squid Mysql database, error, skip blocked sites\n";}
			}
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				if(trim($ligne["uri"])==null){continue;}
				$pattern=$ligne["uri"];
				$pattern=str_replace(".","\.",$pattern);
				$pattern=str_replace("*",".*",$pattern);
				
				if(!preg_match("#^(http|ftp|ftps)\:#",$pattern)){
					$pattern="^.+?$pattern";
				}else{
					$pattern="^$pattern";
				}			
				$tb[]=$pattern;
			}
			
			$countdesite=count($tb);
			writelogs("$countdesite rows",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			if(posix_getuid()==0){echo "Starting......: Squid $countdesite blocked site(s)\n";}
			
			if(is_array($tb)){$conf=implode("\n",$tb);}else{$conf="";}
			@file_put_contents("/etc/squid3/squid-block.acl",$conf);
	}
		
function SaveToLdap(){
			if(!$this->kav_accept){$this->enable_kavproxy=0;}
			if($this->hasProxyTransparent==1){$this->LDAP_AUTH=0;}
			$sock=new sockets();
			$ini=$ini."[NETWORK]\n";
			$ini=$ini."cdir=".implode(";",$this->network_array)."\n";
			$ini=$ini."dns_servers=".implode(";",$this->dns_array)."\n";
			$ini=$ini."LISTEN_PORT=$this->listen_port\n";
			if($this->enable_dansguardian==1){
					$this->alt_listen_port=$sock->RandomPort();
					$dansguardian=new dansguardian();
					$dansguardian->SaveSettings();
			}
			

			
			$ini=$ini."ALT_PORT=$this->alt_listen_port\n";
			$ini=$ini."visible_hostname=$this->visible_hostname\n";
			$ini=$ini."LDAP_AUTH=$this->LDAP_AUTH\n";
			$ini=$ini."NTLM_AUTH=$this->NTLM_AUTH\n";
			$ini=$ini."[KAV]\n";
			$ini=$ini."enabled=$this->enable_kavproxy\n";
			$ini=$ini."[DANSGUARDIAN]\n";
			$ini=$ini."enable_dansguardian=$this->enable_dansguardian\n";
			$ini=$ini."[CACHE]\n";
			$ini=$ini."CACHE_SIZE=$this->CACHE_SIZE\n";
			$ini=$ini."CACHE_PATH=$this->CACHE_PATH\n";

			if(is_array($this->cache_list)){
				reset($this->cache_list);
				while (list ($num, $val) = each ($this->cache_list)){
					$ini=$ini."[cache:$num]\n";
					$ini=$ini."cache_type={$val["cache_type"]}\n";
					$ini=$ini."cache_dir_level1={$val["cache_dir_level1"]}\n";
					$ini=$ini."cache_dir_level2={$val["cache_dir_level2"]}\n";
					$ini=$ini."cache_size={$val["cache_size"]}\n";
				}
			}
			
			if(is_array($this->acl_times)){
				reset($this->acl_times);
				while (list ($num, $array) = each ($this->acl_times)){
					$ini=$ini."[$num]\n";
					while (list ($a, $b) = each ($array)){
						$ini=$ini."$a=$b\n";
					}
					
				}
				
			}
			$sock=new sockets();
			$sock->SET_INFO("DansGuardianEnabled",$this->enable_dansguardian);
			$sock->SET_INFO('kavicapserverEnabled',$this->enable_kavproxy);
			$sock->SET_INFO("hasProxyTransparent",$this->hasProxyTransparent);
			$sock->SaveConfigFile($ini,"ArticaSquidParameters");
			$sock->SaveConfigFile($this->BuildSquidConf(),"GlobalSquidConf");
			$sock->SET_INFO("ArticaEnableKav4ProxyInSquid",$this->enable_kavproxy);
			$sock->SET_INFO("CicapEnabled",$this->enable_cicap);
			$sock->SET_INFO("squidGuardEnabled",$this->enable_squidguard);
			
				
			if($this->enable_dansguardian==1){
				writelogs("",__FUNCTION__,__FILE__);
				writelogs("Save Dansguardian configuration...",__FUNCTION__,__FILE__);
				$dans=new dansguardian();
				$dans->SaveSettings();
			}
			
			if($this->enable_kavproxy==1){
				$sock->getFrameWork("cmd.php?kav4proxy-reconfigure=yes");
			}
			
			$sock->getFrameWork("cmd.php?squidnewbee=yes");
			return true;
			
		}
		
		function SaveToServer(){
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?squidnewbee=yes");	
		}
		
		
		private function ICAP_HEADERS($kaspersky=0){
			$conf[]="icap_enable on";
			if($kaspersky==1){
				$conf[]="icap_preview_enable off";
				$conf[]="icap_preview_size 128";			
			}
			$conf[]="icap_send_client_ip on";
			$conf[]="icap_send_client_username on";
			$conf[]="icap_client_username_header X-Authenticated-User";
			$conf[]="icap_client_username_encode on";
			return implode("\n",$conf);
			
		}
		
public function SquidGuardDatabasesStatus($all=0){
	$datas=explode("\n",@file_get_contents("/etc/squid/squidGuard.conf"));
	while (list ($a, $b) = each ($datas)){
		if(preg_match("#domainlist.+?(.+)#",$b,$re)){
			$file="/var/lib/squidguard/{$re[1]}";
			if(filesize($file)==0){continue;}
			$f[]=$file;
			continue;
		}
				
		if(preg_match("#urllist.+?(.+)#",$b,$re)){
			$file="/var/lib/squidguard/{$re[1]}";
			if(filesize($file)==0){continue;}
			$f[]=$file;
			continue;
		}
		
		
	}
	
	if($all==0){while (list ($a, $b) = each ($f)){
		if(!is_file("$b.db")){
			$array[]="$b.db";
		}
		
	}}else{
		while (list ($a, $b) = each ($f)){
			$array[]="$b.db";
		}
	}
	
	
	return $array;
	
}		
		
		
		private function ldap_auth_conf(){
			if($this->LDAP_AUTH==0){return null;}
			$users=new usersMenus();
			$ldap=new clladp();
			if($ldap->ldap_host==null){$ldap->ldap_host="127.0.0.1";}
			$conf[]= "#--------- LDAP AUTH settings\n";
			$conf[]= "\n#Authentification mode, building using squid compiled for artica";
			if(trim($users->SQUID_LDAP_AUTH)<>null){
				$conf[]= "auth_param basic program $users->SQUID_LDAP_AUTH -b \"$ldap->suffix\" -D \"cn=$ldap->ldap_admin,$ldap->suffix\" -w $ldap->ldap_password -f \"(&(objectClass=userAccount)(uid=%s))\" -v 3 -h $ldap->ldap_host";
				$conf[]= "#--------- GLOBAL";
				$conf[]= "external_acl_type ldap_group %LOGIN " . dirname($users->SQUID_LDAP_AUTH)."/squid_ldap_group -D \"cn=$ldap->ldap_admin,$ldap->suffix\" -w $ldap->ldap_password -b \"$ldap->suffix\"  -f \"(&(objectClass=posixGroup)(gidNumber=%a)(memberUid=%v))\" -S -v 3 -h $ldap->ldap_host";
				$conf[]= "auth_param basic children 5";
				$conf[]= "auth_param basic realm Squid proxy-caching web server";
				$conf[]= "acl ldapauth proxy_auth REQUIRED";
				$conf[]= "http_access allow ldapauth";
				$conf[]="";
			}else{
				$conf[]= "#No LDAP auth_param basic program found !";
				$conf[]="";
			}

			return implode("\n",$conf);
			
		}
		
		private function ntlm_auth_conf(){
			// voir permissions on /var/lib/samba/winbindd_privileged
			// http://www.cyberciti.biz/faq/squid-ntlm-authentication-configuration-howto/
			$users=new usersMenus();
			if($this->NTLM_AUTH==0){return null;}
			if(!$users->SQUID_NTLM_ENABLED){
				$this->NTLM_AUTH=0;
				return "#No NTLM in compilation !\n";
			}
			
			if(trim($users->SQUID_NTLM_AUTH)==null){
				$this->NTLM_AUTH=0;
				return "#No NTLM auth_param basic program found !\n";
			}
			
			$smb=new samba();
			$domain=$smb->main_array["global"]["workgroup"];
			$server=$smb->main_array["global"]["netbios name"];
			if($server==null){$server=$users->hostname;}
			
			$conf[]= "#--------- NTLM AUTH settings\n";
			
			
			$conf[]= "\n#Authentification mode, building using squid compiled for artica";
			$conf[]= "auth_param ntlm program $users->SQUID_NTLM_AUTH --helper-protocol=squid-2.5-ntlmssp";
			$conf[]= "auth_param basic program $users->SQUID_NTLM_AUTH --helper-protocol=squid-2.5-basic";
			$conf[]= "auth_param ntlm children 15";
			$conf[]= "auth_param basic children 15";
			$conf[]= "auth_param basic credentialsttl 5 hours";	
			$conf[]= "auth_param basic casesensitive off";	
			$conf[]= "auth_param basic realm Squid proxy-caching web server";
			$conf[]= "authenticate_cache_garbage_interval 10 seconds";
			$conf[]= "authenticate_ttl 0 hour";
			$conf[]= "#--------- NTLM ACL settings";
			$conf[]= "acl ntlm_users proxy_auth REQUIRED";
			$conf[]="";
			return implode("\n",$conf);
			}
			

			

			
	private function icap_kav_services_3(){
		if($this->enable_kavproxy<>1){
			if($this->ASROOT){echo "Starting......: Squid Kaspersky ICAP is disabled\n";}
			return $this->icap_cicap_services_3();}
		if($this->ASROOT){echo "Starting......: Squid activating Kaspersky ICAP Mode\n";}
		if($this->SQUID_BIN_VERSION>310000000000){return $this->icap_kav_services_31();}
		$kav_rules[]="";
		$kav_rules[]="# --------- icap_service KASPERSKY mode 3.0.x";
		$kav_rules[]=$this->ICAP_HEADERS(1);
		$kav_rules[]="";
		$kav_rules[]="icap_service\tis_kav_resp respmod_precache 0 icap://127.0.0.1:1344/av/respmod";
		$kav_rules[]="icap_service\tis_kav_req reqmod_precache 0 icap://127.0.0.1:1344/av/reqmod";
		$kav_rules[]="";
		$kav_rules[]="# --------- icap_class";
		$kav_rules[]="icap_class\tic_kav_resp is_kav_resp";
		$kav_rules[]="icap_class\tic_kav_req is_kav_req"; 
		$kav_rules[]="";
		$kav_rules[]="# --------- icap_access";
		$kav_rules[]="icap_access\tic_kav_resp allow all";
		$kav_rules[]="icap_access\tic_kav_req allow all !acl_kav_GET";			
		$kav_rules[]="";	

		return implode("\n",$kav_rules);	
	}
	
	private function icap_kav_services_31(){
		
		if($this->ASROOT){echo "Starting......: Squid adaptation enabled\n";}
		$kav_rules[]="";
		$kav_rules[]="# --------- icap_service KASPERSKY mode 3.1.1";
		$kav_rules[]=$this->ICAP_HEADERS(1);
		$kav_rules[]="";
		$kav_rules[]="icap_service\tis_kav_resp respmod_precache 0 icap://127.0.0.1:1344/av/respmod";
		$kav_rules[]="icap_service\tis_kav_req reqmod_precache 0 icap://127.0.0.1:1344/av/reqmod";
		$kav_rules[]="";
		$kav_rules[]="# --------- adaptation";
		$kav_rules[]="";
		$kav_rules[]="adaptation_access is_kav_req allow all";
		$kav_rules[]="adaptation_access is_kav_resp allow all";	
		$kav_rules[]="";	
		return implode("\n",$kav_rules);	
	}	



	
	
	private function icap_cicap_services_3(){
		if($this->enable_cicap<>1){return null;}
		if($this->enable_dansguardian==1){return null;}
		if($this->SQUID_BIN_VERSION>310000000000){return $this->icap_cicap_services_31();}
		if($this->ASROOT){echo "Starting......: Squid activating C-ICAP\n";}
		$kav_rules[]="# --------- icap_service C-ICAP";
		$kav_rules[]="icap_service\tservice_req reqmod_precache bypass=1 icap://127.0.0.1:1344/srv_clamav";
		$kav_rules[]="icap_service\tservice_avi respmod_precache bypass=0 icap://127.0.0.1:1344/srv_clamav";
		
		$kav_rules[]=$this->ICAP_HEADERS();
		

		
		$kav_rules[]="";
		$kav_rules[]="icap_class\tclass_antivirus service_avi";
		$kav_rules[]="icap_class\tclass_antivirus_req service_req";		
		$kav_rules[]="icap_access\tclass_antivirus allow all";
		$kav_rules[]="icap_access\tclass_antivirus_req allow all";
		$kav_rules[]="";
		return implode("\n",$kav_rules);				
		
	}
	
	private function icap_cicap_services_31(){
		if($this->ASROOT){echo "Starting......: Squid activating C-ICAP\n";}
		$kav_rules[]="";
		$kav_rules[]="# --------- icap_service C-ICAP mode 3.1.x";
		$kav_rules[]="acl MULTIMEDIA rep_mime_type -i ^(audio\/x-mpegurl|audio\/mpeg|video\/flv|video\/x-flv|application\/x-shockwave-flash|audio\/ogg|video\/ogg|application\/ogg)$";		
		//$kav_rules[]="icap_service  service_antivir_req reqmod_precache 1 icap://127.0.0.1:1344/srv_clamav";
		if($this->enable_squidguard==1){
			$kav_rules[]="icap_service\tservice_url_check reqmod_precache 1 icap://127.0.0.1:1344/url_check";
			$kav_rules[]="adaptation_service_set  class_url_check   service_url_check";
			$kav_rules[]="adaptation_access  class_url_check  allow all";
			$kav_rules[]="";
		}		
		$kav_rules[]="icap_service  service_antivir 	respmod_precache 1 icap://127.0.0.1:1344/srv_clamav";
		$kav_rules[]="";		
		//$kav_rules[]="adaptation_service_set  class_antivirus_req $service_url_check service_antivir_req";
		//$kav_rules[]="adaptation_access       class_antivirus_req deny MULTIMEDIA";
		$kav_rules[]="";
		$kav_rules[]="adaptation_service_set  class_antivirus service_antivir";
		$kav_rules[]="adaptation_access       class_antivirus deny MULTIMEDIA";
		$kav_rules[]="adaptation_access       class_antivirus allow all";
		$kav_rules[]="";
		$kav_rules[]="icap_preview_enable on";
		$kav_rules[]="icap_preview_size 128";
		$kav_rules[]="icap_service_failure_limit -1";	
		$kav_rules[]=$this->ICAP_HEADERS();
		$kav_rules[]="";
		$kav_rules[]="";
		
		return implode("\n",$kav_rules);		
		}	
		
		
	private function squidGuard(){
		
		$users=new usersMenus();
		if(!$users->SQUIDGUARD_INSTALLED){return null;}
		if($this->enable_cicap==1){
			$conf[]= "\n#--------- squidGuard";
			$conf[]= "\n#transfered to C-ICAP";
			return implode("\n",$conf);
		}
		
		if($this->enable_squidguard<>1){
			$conf[]= "\n#--------- squidGuard";
			$conf[]= "\n#Disabled";
			return implode("\n",$conf);			
			return null;
		
		}
		$this->enable_dansguardian=0;
		$bin=trim($users->SQUIDGUARD_BIN_PATH);
		if($bin==null){
			$sock=new sockets();
			$bin=base64_decode($sock->getFrameWork("cmd.php?find-program=squidGard"));
			if(trim($bin)==null){
				if($this->ASROOT){echo "Starting......: Unable to activate squidGuard no path found\n";}
				return null;
			}
		}
		
		if(posix_getuid()==0){
			$array=$this->SquidGuardDatabasesStatus();
			if(is_array($array)){
				$conf[]= "\n#--------- squidGard";
				while (list ($num, $val) = each ($array) ){
					$conf[]="#not compiled $val";
					
				}
				if($this->ASROOT){echo "Starting......: disable squidGuard \"". count($array)."\" databases not compiled\n";}
				return implode("\n",$conf);
				
			}
		}
			$conf[]= "\n#--------- squidGard";
			$conf[]= "\n#IS C-ICAP enabled = $this->enable_cicap";
			$conf[]= "redirect_program $users->SQUIDGUARD_BIN_PATH";
			$conf[]= "redirect_children 10\n";
		if($this->ASROOT){echo "Starting......: Activate squidGuard \"$bin\"\n";}
			return implode("\n",$conf);
	}
		
		
function BuildSquidConf(){
		$sock=new sockets();

		if($this->ASROOT){echo "Starting......: Squid Version $this->SQUID_BIN_VERSION\n";}
		
		$squidGuard=$this->squidGuard();
		$EnableMalwarePatrol=$sock->GET_INFO("EnableMalwarePatrol");
		$kav_rules=$this->icap_kav_services_3();
		$kav_acl="acl acl_kav_GET method GET\n";
		$kav_acl=$this->acls_multimedias();
			
			
		if($this->hasProxyTransparent==1){$this->LDAP_AUTH=0;$this->NTLM_AUTH=0;}

		$conf=$conf . $this->ntlm_auth_conf();
		$conf=$conf . $this->ldap_auth_conf();
		$conf=$conf . "auth_param basic credentialsttl 2 hour\n";
		$conf=$conf . "authenticate_ttl 1 hour\n";
		$conf=$conf . "authenticate_ip_ttl 60 seconds\n";
		$conf=$conf . "cache_effective_user squid\n";
		$conf=$conf . "cache_effective_group squid\n";
		$conf=$conf . "#--------- TWEEKS PERFORMANCES\n";
		$conf=$conf . "# http://blog.last.fm/2007/08/30/squid-optimization-guide\n";
		$conf=$conf . "memory_pools off\n";
		$conf=$conf . "quick_abort_min 0 KB\n";
		$conf=$conf . "quick_abort_max 0 KB\n";
		$conf=$conf . "log_icp_queries off\n";
		$conf=$conf . "client_db off\n";
		$conf=$conf . "buffered_logs on\n";
		$conf=$conf . "half_closed_clients off\n";
		$access_blocked_sites="http_access deny blockedsites\n";
		$conf=$conf.$squidGuard;
		
		$acl_blocked_sites="acl blockedsites url_regex \"/etc/squid3/squid-block.acl\"\n";

		$conf=$conf  ."\n#--------- acls\n";
		if($EnableMalwarePatrol==1){
			$conf=$conf  ."acl malware_block_list url_regex -i \"/etc/squid3/malwares.acl\"\n";
		}
		$conf=$conf  .$acl_blocked_sites;
		$conf=$conf ."acl localhost src 127.0.0.1/32\n";
		$conf=$conf ."acl to_localhost dst 127.0.0.0/32\n";
		$conf=$conf. "acl CONNECT method CONNECT\n";
		$conf=$conf. "acl manager proto cache_object\n";
		
		
		if(trim($users->SQUID_LDAP_AUTH)==1){
				if(is_array($this->acl_times)){
					$conf=$conf."#--------- TIME RESTRICTON\n";
					reset($this->acl_times);
					while (list ($num, $val) = each ($this->acl_times) ){
						if(!preg_match("#time:([0-9]+):(.+)#",$num,$re)){continue;}
							$re[2]=trim($re[2]);
							while (list ($a, $b) = each ($val)){
								$c[]="$a $b"; 
							}
							$conf=$conf."acl {$re[1]}_{$re[2]}_time time ". implode(" " ,$c)."\n";
							$http_access_time_array[]="http_access deny ldap_{$re[1]}_{$re[2]} !{$re[1]}_{$re[2]}_time";
							
							$acgroup="acl ldap_{$re[1]}_{$re[2]} external ldap_group {$re[1]}";
							$acl_groups[$acgroup]=$acgroup;
							
							}
							
					if(is_array($http_access_time_array)){
						$http_access_time=implode("\n",$http_access_time_array)."\n";
					}
				}
		}

		$conf=$conf . $kav_acl;

		writelogs("How many networks ? = " . count($this->network_array),__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if(count($this->network_array)>0){
			$conf=$conf ."acl office_network src " . implode(" ",$this->network_array) ."\n";
		}
		
		if(count($this->network_array)>0){$office_network="http_access allow office_network\n";}
		

		
		
	
		if($this->LDAP_AUTH==1){
			$conf=$conf ."acl group_password external ldap_group\n";
			if(is_array($acl_groups)){
				while (list ($a, $b) = each ($acl_groups)){
					$gprs[]=$b;
				}
				$conf=$conf ."\n#--------- GROUPS definition\n";
				$conf=$conf .implode("\n",$gprs);
			}else{
				$conf=$conf ."\n#--------- GROUPS definition\n";
				$conf=$conf ."#no groups";
			}
		}

		$conf=$conf."\n\n#--------- MAIN RULES...\n";
		//$conf=$conf."follow_x_forwarded_for allow localhost\n";
		
		
		if($this->LDAP_AUTH==1){
				$acl_access_time=$http_access_time;
				$acl_ldap_auth="http_access allow group_password\n";
				$acl_ldapauth="http_access allow ldapauth\n";
		}
		
		if($this->NTLM_AUTH==1){
				$acl_access_time=$http_access_time;
				$acl_ldap_auth="http_access allow ntlm_users\n";
				@mkdir("/var/cache/samba/winbindd_privileged",0755,true);
				if(posix_getuid()==0){shell_exec("chgrp squid /var/cache/samba/winbindd_privileged/");}
				$office_network=null;
		}		
		
		if($this->enable_dansguardian==1){
				$office_network=null;
		}		
		

		if($this->hasProxyTransparent==1){$transparent=" transparent";}
		$listen_port=$this->listen_port;
		if($this->enable_dansguardian==1){
			$listen_port=$this->alt_listen_port;	
		}
		
		$conf=$conf."# --------- SAFE ports\n";
		$conf=$conf.$this->SAFE_PORTS();
		$conf=$conf."#\n";
		$conf=$conf ."http_access allow localhost\n";
		$conf=$conf ."http_access allow manager localhost\n";
		if($EnableMalwarePatrol==1){
			$conf=$conf  ."http_access deny malware_block_list\n";
		}
		if(preg_match("#([0-9+])#",$this->global_conf_array["reply_body_max_size"],$re)){
			if($re[1]==0){$this->global_conf_array["reply_body_max_size"]=$re[1];}
		}
		
		if(preg_match("#([0-9+])#",$this->global_conf_array["request_body_max_size"],$re)){
			if($re[1]==0){$this->global_conf_array["request_body_max_size"]=$re[1];}
		}		
		
		if(preg_match("#([0-9+])#",$this->global_conf_array["request_header_max_size"],$re)){
			if($re[1]==0){$this->global_conf_array["request_header_max_size"]="64 KB";}
		}	

		if($this->global_conf_array["reply_body_max_size"]==null){$this->global_conf_array["reply_body_max_size"]=0;}
		if($this->global_conf_array["request_body_max_size"]==null){$this->global_conf_array["request_body_max_size"]=0;}
		if($this->global_conf_array["request_header_max_size"]==null){$this->global_conf_array["request_header_max_size"]=0;}

		$conf=$conf .$access_blocked_sites;
		$conf=$conf. $acl_ldapauth;
		$conf=$conf .$acl_ldap_auth;
		$conf=$conf .$acl_access_time;
		$conf=$conf .$office_network;
		$conf=$conf ."http_access deny !Safe_ports\n";
		//$conf=$conf ."http_access deny !SSL_ports\n";
		$conf=$conf ."http_access deny all\n";
		$conf=$conf . $kav_rules;
		$conf=$conf."# --------- ident_lookup_access\n";
		$conf=$conf."hierarchy_stoplist cgi-bin ?\n";
		$conf=$conf."\n";
		$conf=$conf."# --------- General settings \n";
		$conf=$conf."visible_hostname $this->visible_hostname\n";
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf."# --------- time-out \n";
		$conf=$conf."dead_peer_timeout {$this->global_conf_array["dead_peer_timeout"]}\n";
		$conf=$conf."dns_timeout {$this->global_conf_array["dns_timeout"]}\n";
		$conf=$conf."connect_timeout 1600 seconds\n";
		$conf=$conf."persistent_request_timeout 3 minutes\n";
		$conf=$conf."pconn_timeout 1600 seconds\n"; 		
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf."# --------- Objects limits \n";
		$conf=$conf."request_body_max_size {$this->global_conf_array["request_body_max_size"]}\n";
		if($this->global_conf_array["reply_body_max_size"]<>0){
			$conf=$conf."reply_body_max_size {$this->global_conf_array["reply_body_max_size"]}\n";
		}
		$conf=$conf."request_header_max_size {$this->global_conf_array["request_header_max_size"]}\n";
		$conf=$conf."maximum_object_size {$this->global_conf_array["maximum_object_size"]}\n";
		$conf=$conf."minimum_object_size {$this->global_conf_array["minimum_object_size"]}\n";
		$conf=$conf."maximum_object_size_in_memory {$this->global_conf_array["maximum_object_size_in_memory"]}\n";

		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf."#http ports\n";
		$conf=$conf."http_port $listen_port$transparent\n";
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf ."# --------- Caches \n";
		$conf=$conf ."#cache_replacement_policy heap LFUDA\n";
		$conf=$conf ."cache_mem {$this->global_conf_array["cache_mem"]}\n";
		$conf=$conf ."cache_swap_high {$this->global_conf_array["cache_swap_high"]}\n";
		$conf=$conf ."cache_swap_low {$this->global_conf_array["cache_swap_low"]}\n";
	
		$conf=$conf."# --------- DNS and ip caches \n";
		$conf=$conf ."ipcache_size {$this->global_conf_array["ipcache_size"]}\n";
		$conf=$conf ."ipcache_low {$this->global_conf_array["ipcache_low"]}\n";		
		$conf=$conf ."ipcache_high {$this->global_conf_array["ipcache_high"]}\n";				
		$conf=$conf ."fqdncache_size {$this->global_conf_array["fqdncache_size"]}\n";						
	
		if(is_array($this->dns_array)){
			reset($this->dns_array);
			$conf=$conf ."\n\n# --------- SPECIFIC DNS SERVERS \n";
			while (list ($num, $val) = each ($this->dns_array) ){
				$conf=$conf . "dns_nameservers " . $val . "\n";
				
			}
		}

		
		$conf=$conf."debug_options ALL,1\n";
		//$conf=$conf."cache deny QUERY\n";
		$conf=$conf."refresh_pattern ^ftp:		1440	20%	10080\n";
		$conf=$conf."refresh_pattern ^gopher:	1440	0%	1440\n";
		$conf=$conf.$this->refresh_pattern_list();
		$conf=$conf."refresh_pattern .		   0	20%	4320\n";
		$conf=$conf."icp_port 3130\n";
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf."#Logs-------------------------------------------------\n";
		$conf=$conf."emulate_httpd_log on\n";
		$conf=$conf."log_fqdn on\n";
		$conf=$conf."coredump_dir	/var/squid/cache\n";
		$conf=$conf."cache_store_log	/var/log/squid/store.log\n";
		$conf=$conf."cache_log	/var/log/squid/cache.log\n";
		$conf=$conf."pid_filename	/var/run/squid.pid\n";
		$conf=$conf."access_log	/var/log/squid/access.log\n";
		if($this->enable_cicap==1){
			$conf=$conf."icap_log /var/log/squid/icap_access.log\n";
		}
		
		
		$conf=$conf."\n";
		$conf=$conf."cache_dir	ufs {$this->CACHE_PATH} {$this->CACHE_SIZE} 16 256\n";
		$conf=$conf."# --------- OTHER CACHES\n";
		if(is_array($this->cache_list)){
		reset($this->cache_list);
		while (list ($num, $val) = each ($this->cache_list)){
		$conf=$conf."cache_dir {$this->cache_list[$num]["cache_type"]} $num {$this->cache_list[$num]["cache_size"]} {$this->cache_list[$num]["cache_dir_level1"]} {$this->cache_list[$num]["cache_dir_level2"]}\n";
		}
		}
		
		$conf=$conf."\n";
		return $conf;	
		
		}
		
private function refresh_pattern_list(){
	$sql="SELECT * FROM `squid_speed` WHERE `domain` IS NOT NULL";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$pattern=$ligne["domain"];
		$pattern=str_replace(".","\.",$pattern);
		$pattern=str_replace("*",".*?",$pattern);
		$conf[]="refresh_pattern $pattern  {$ligne["refresh_pattern_min"]}    {$ligne["refresh_pattern_perc"]}%     {$ligne["refresh_pattern_max"]}";
		
	}
	if(is_array($conf)){return implode("\n",$conf)."\n";}
	
}
		
		
		
function SAFE_PORTS(){
	$ports["80"]="http";
	$ports["21"]="ftp";
	$ports["22"]="ssh";
	$ports["443 563"]="https, snews";
	$ports["1863 "]="msn";
	$ports["70"]="gopher";
	$ports["210"]="wais";
	$ports["1025-65535"]="unregistered ports";
	$ports["280"]="http-mgmt";
	$ports["488"]="gss-http";
	$ports["591"]="filemaker";
	$ports["777"]="multiling http";
	$ports["631"]="cups";
	$ports["873"]="rsync";
	$ports["901"]="SWAT";
	while (list ($num, $val) = each ($ports) ){
		$conf[]="acl Safe_ports port $num\t#$val";		
	}
	
	return implode("\n",$conf);
	
}
		
		
function acls_multimedias(){
	
		$conf=$conf."acl multimedia_rep rep_mime_type -i ^video/x-ms-asf$\n";
		$conf=$conf."acl multimedia_rep rep_mime_type -i ^application/vnd.ms.wms-hdr.asfv1$\n";
		$conf=$conf."acl multimedia_rep rep_mime_type -i ^application/x-mms-framed$\n";
		$conf=$conf."acl multimedia_rep rep_mime_type -i ^image/\n";
		$conf=$conf."acl multimedia_rep rep_mime_type -i ^video\n";
		$conf=$conf."acl multimedia_rep rep_mime_type -i ^audio\n";
		$conf=$conf."acl multimedia_rep rep_mime_type -i ^application/x-dvi$\n";
		$conf=$conf."acl multimedia_rep rep_mime_type -i ^application/x-isoview\n";
		$conf=$conf."acl multimedia_browsers browser -i ^Windows-Media-Player.* -i ^.*player.*\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.deb$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.rpm$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.iso$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.tar\.gz$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.gz$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.bz$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.tar$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.cue$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.nrg$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.crf$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.bwi$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.bwt$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.lcd$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.ccd$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.mdf$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.mds$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.vcd$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.cif$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.vdi$\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.img$\n";	
		return $conf;
		}
		
function external_ldap_ou($path){
	$ldap=new clladp();
	$ou_arr=$ldap->hash_get_ou();
	
if(is_array($ou_arr)){
	while (list ($num, $val) = each ($ou_arr)){		
		$conf=$conf . "external_acl_type ldap_{$val} %LOGIN " . dirname($path)."/squid_ldap_group -D \"cn=$ldap->ldap_admin,$ldap->suffix\"";
		$conf=$conf . " -w $ldap->ldap_password -b \"ou=$val,$ldap->suffix\"  -f \"(&(objectClass=posixGroup)(gidNumber=%a)(memberUid=%v))\" -S -v 3 -h 127.0.0.1\n";
		}
	return "$conf\n";
	}
}
}


class cicap{
	var $main_array=array();
	
	
	function cicap(){
		$ini=new Bs_IniHandler();
		$sock=new sockets();
		$ini->loadString($sock->GET_INFO('CicapInternalConfiguration'));
		$this->main_array=$ini->_params;
		$this->BuildDefaults();
		
		}
	
	function BuildDefaults(){
		if($this->main_array["CONF"]["Timeout"]==null){$this->main_array["CONF"]["Timeout"]=300;}
		if($this->main_array["CONF"]["KeepAlive"]==null){$this->main_array["CONF"]["KeepAlive"]="on";}
		if($this->main_array["CONF"]["MaxKeepAliveRequests"]==null){$this->main_array["CONF"]["MaxKeepAliveRequests"]="100";}
		if($this->main_array["CONF"]["KeepAliveTimeout"]==null){$this->main_array["CONF"]["KeepAliveTimeout"]="600";}
		if($this->main_array["CONF"]["StartServers"]==null){$this->main_array["CONF"]["StartServers"]="3";}
		if($this->main_array["CONF"]["MaxServers"]==null){$this->main_array["CONF"]["MaxServers"]="10";}
		if($this->main_array["CONF"]["MinSpareThreads"]==null){$this->main_array["CONF"]["MinSpareThreads"]="10";}
		if($this->main_array["CONF"]["MaxSpareThreads"]==null){$this->main_array["CONF"]["MaxSpareThreads"]="20";}
		if($this->main_array["CONF"]["ThreadsPerChild"]==null){$this->main_array["CONF"]["ThreadsPerChild"]="0";}
		if($this->main_array["CONF"]["MaxRequestsPerChild"]==null){$this->main_array["CONF"]["MaxRequestsPerChild"]="0";}
		if($this->main_array["CONF"]["srv_clamav.SendPercentData"]==null){$this->main_array["CONF"]["srv_clamav.SendPercentData"]="5";}
		if($this->main_array["CONF"]["srv_clamav.StartSendPercentDataAfter"]==null){$this->main_array["CONF"]["srv_clamav.StartSendPercentDataAfter"]="2";}
		if($this->main_array["CONF"]["srv_clamav.MaxObjectSize"]==null){$this->main_array["CONF"]["srv_clamav.MaxObjectSize"]="5";}
		if($this->main_array["CONF"]["srv_clamav.ClamAvMaxFilesInArchive"]==null){$this->main_array["CONF"]["srv_clamav.ClamAvMaxFilesInArchive"]="0";}
		if($this->main_array["CONF"]["srv_clamav.ClamAvMaxFileSizeInArchive"]==null){$this->main_array["CONF"]["srv_clamav.ClamAvMaxFileSizeInArchive"]="100";}		
		if($this->main_array["CONF"]["srv_clamav.ClamAvMaxRecLevel"]==null){$this->main_array["CONF"]["srv_clamav.ClamAvMaxRecLevel"]="5";}
		if($this->main_array["CONF"]["ThreadsPerChild"]==0){$this->main_array["CONF"]["ThreadsPerChild"]=10;}
		if($this->main_array["CONF"]["VirSaveDir"]==null){$this->main_array["CONF"]["VirSaveDir"]="/opt/artica/share/www/squid-attachments";}
		if($this->main_array["CONF"]["VirHTTPServer"]==null){$this->main_array["CONF"]["VirHTTPServer"]="https://{$_SERVER['SERVER_NAME']}/exec.cicap.php?usename=%f&remove=1&file=";}
		
		
		
	
	}
	
	private function LOCATE_CLAMD_CONF(){
		if(is_file("/etc/clamav/clamd.conf")){return "/etc/clamav/clamd.conf";}
		if(is_file("/etc/clamd.conf")){return "/etc/clamd.conf";}
	}
	
	public function LOCATE_MODULES_DIR(){
		if(is_file("/usr/lib/c_icap/sys_logger.so")){return "/usr/lib/c_icap";}
		if(is_file("/var/lib/c_icap/sys_logger.so")){return "/var/lib/c_icap";}
	}
	
	private function CLAMAV_USER(){
		$tbl=@explode("\n",@file_get_contents($this->LOCATE_CLAMD_CONF()));
		while (list ($num, $val) = each ($tbl)){	
			if(preg_match("#User\s+(.+)#",$val,$re)){return trim($re[1]);}
		}
	}
	
	private function DNBSL_LIST(){
		$sock=new sockets();
		$datas=explode("\n",$sock->GET_INFO("CicapDNSBL"));
		while (list ($num, $line) = each ($datas)){
			if(strlen($line)<4){continue;}
			$servicename=str_replace(".","",$line);
			$conf[]="url_check.LookupTableDB $servicename  domain dnsbl:$line";
			$servicenames[]=$servicename;
			}
		if(!is_array($conf)){return array();}
		
		$conf[]="url_check.Profile default block ". implode(" ",$servicenames);
		
		return $conf;
	}
	
	
	public function buildconf(){
		$this->BuildDefaults();
		$sock=new sockets();
			$usermenus=new usersMenus();
			$user="squid";
			$modules_path=$this->LOCATE_MODULES_DIR();
			echo "Starting......: c-icap using modules path in $modules_path\n";
			if(is_file("$modules_path/srv_clamav.so")){
				echo "Starting......: c-icap using ClamAV antivirus\n";
				$user=$this->CLAMAV_USER();
				
			}
			
			if(is_dir($GLOBALS["guarddb"])){shell_exec("/bin/chown -R $user:$user {$GLOBALS["guarddb"]}/*");}
			
			echo "Starting......: c-icap using User $user ($usermenus->hostname)\n";
			$conf[]="PidFile /var/run/c-icap.pid";
			$conf[]="CommandsSocket /var/run/c-icap/c-icap.ctl";
			$conf[]="Timeout {$this->main_array["CONF"]["Timeout"]}";
			$conf[]="MaxKeepAliveRequests {$this->main_array["CONF"]["MaxKeepAliveRequests"]}";
			$conf[]="KeepAliveTimeout {$this->main_array["CONF"]["KeepAliveTimeout"]}";
			$conf[]="StartServers {$this->main_array["CONF"]["StartServers"]}";
			$conf[]="MaxServers {$this->main_array["CONF"]["MaxServers"]}";
			$conf[]="MinSpareThreads     {$this->main_array["CONF"]["MinSpareThreads"]}";
			$conf[]="MaxSpareThreads     {$this->main_array["CONF"]["MaxSpareThreads"]}";
			$conf[]="ThreadsPerChild     {$this->main_array["CONF"]["ThreadsPerChild"]}";
			$conf[]="MaxRequestsPerChild  {$this->main_array["CONF"]["MaxRequestsPerChild"]}";
			$conf[]="MaxMemObject 131072";
			$conf[]="Port 1344 ";
			$conf[]="User $user";
			$conf[]="Group $user";
			$conf[]="ServerAdmin you@your.address";
			$conf[]="ServerName $usermenus->hostname";
			$conf[]="TmpDir /var/lib/c_icap/temporary";
			$conf[]="DebugLevel 5";
			$conf[]="ModulesDir $modules_path";
			$conf[]="ServicesDir $modules_path";
			$conf[]="TemplateDir /usr/share/c_icap/templates/";		
			$conf[]="LoadMagicFile /etc/c-icap.magic";
			$conf[]="TemplateDefaultLanguage en";
			$conf[]="#TemplateReloadTime 360";
			$conf[]="#TemplateCacheSize 20";
			$conf[]="#TemplateMemBufSize 8192";
			
			$conf[]="";
			$conf[]="acl all src 0.0.0.0/0.0.0.0";
			$conf[]="";
			
			
			$conf[]="RemoteProxyUsers on";
			$conf[]="RemoteProxyUserHeader X-Authenticated-User";
			$conf[]="RemoteProxyUserHeaderEncoded on";
			$conf[]="LogFormat allFormat \"%tl;%a;%un;%iu;%is;%huo\""; 		
			$conf[]="ServerLog /var/log/c-icap/server.log";
			$conf[]="AccessLog /var/log/c-icap/access.log allFormat all";
			//$conf[]="echo.PreviewSize 512";
			//$conf[]="echo.TransferIgnore gif, jpeg";
			
			$icc=new cicap_filter();
			$conf[]=$icc->Buildacls();
			
			if(is_file("$modules_path/sys_logger.so")){
				echo "Starting......: c-icap using Sysloger\n";
				$conf[]="#Sysloger";
				$conf[]="Module logger sys_logger.so\n";
				$conf[]="sys_logger.server_priority alert|crit|debug|emerg|err|info|notice|warning\n";
				$conf[]="sys_logger.Prefix \"C-ICAP:\"";
				$conf[]="sys_logger.Facility local1";
				$conf[]="";	
			}
			
			if(is_file("$modules_path/dnsbl_tables.so")){$conf[]="Module common bdb_tables.so";}
			$conf[]="Module common dnsbl_tables.so";
			$conf[]="Service url_check_module srv_url_check.so";
			$conf[]="";	
			
			if(is_file("$modules_path/dnsbl_tables.so")){
				if($sock->GET_INFO("DisableCicapDNBSL")<>1){
					$array=$this->DNBSL_LIST();
					echo "Starting......: c-icap using ". count($array) ." DNSBL services\n";
					if(count($array)>0){
						$this->cicap_whitelisted_servers();
						echo "Starting......: c-icap using DNSBL\n";
						$conf[]="#DNSBL";
						$conf[]="url_check.LookupTableDB whitelist domain hash:/etc/squid3/c-icap.whitelist.txt";
						$conf[]="url_check.Profile default pass whitelist";
						$conf[]=implode("\n",$this->DNBSL_LIST());
						$conf[]="";	
					}
				}else{
					echo "Starting......: c-icap Disabled DNSBL service\n";
				}
			}			
	
			$conf[]=$icc->GetSquidGuarddbs();
			
			
			
			if(is_file("$modules_path/srv_clamav.so")){
				$conf[]="";
				$conf[]="#Clamav";
				$conf[]="Service antivirus_module srv_clamav.so srv_url_check.so";
				$conf[]="ServiceAlias  avscan srv_clamav?allow204=off&sizelimit=off&mode=simple";
				$conf[]="srv_clamav.ScanFileTypes TEXT DATA EXECUTABLE ARCHIVE MSOFFICE";
				$conf[]="srv_clamav.VirScanFileTypes ARCHIVE EXECUTABLE";
				$conf[]="srv_clamav.TransferIgnore flv, f4v, f4p, f4a, f4b, mpeg, mp2, mp3";
				$conf[]="srv_clamav.SendPercentData {$this->main_array["CONF"]["srv_clamav.SendPercentData"]}";
				$conf[]="srv_clamav.StartSendPercentDataAfter {$this->main_array["CONF"]["srv_clamav.StartSendPercentDataAfter"]}M";
				$conf[]="srv_clamav.Allow204Responces off";
				$conf[]="srv_clamav.MaxObjectSize  {$this->main_array["CONF"]["srv_clamav.MaxObjectSize"]}M";
				$conf[]="srv_clamav.ClamAvTmpDir /var/tmp";
				$conf[]="srv_clamav.ClamAvMaxFilesInArchive {$this->main_array["CONF"]["srv_clamav.ClamAvMaxFilesInArchive"]}";
				$conf[]="srv_clamav.ClamAvMaxFileSizeInArchive {$this->main_array["CONF"]["srv_clamav.ClamAvMaxFileSizeInArchive"]}M";
				$conf[]="srv_clamav.ClamAvMaxRecLevel {$this->main_array["CONF"]["srv_clamav.ClamAvMaxRecLevel"]}";
				$conf[]="srv_clamav.VirSaveDir {$this->main_array["CONF"]["VirSaveDir"]}";
				$conf[]="srv_clamav.VirHTTPServer  \"{$this->main_array["CONF"]["VirHTTPServer"]}\"";
				$conf[]="srv_clamav.VirUpdateTime   15";
				}	
	
				@mkdir("/var/log/c-icap",0666,true);
				@mkdir("/var/lib/c_icap/temporary",0666,true);
				@mkdir("/var/run/c-icap",0666,true);
				@mkdir("/var/tmp",0666,true);
				@mkdir("/usr/share/c_icap/templates",0666,true);
				//if(!is_file("/var/log/c-icap/server.log")){@file_put_contents("/var/log/c-icap/server.log"," ");}
				//if(!is_file("/var/log/c-icap/access.log")){@file_put_contents("/var/log/c-icap/access.log"," ");}
				
				shell_exec("/bin/chmod -R 0755 /var/log/c-icap");
				shell_exec("/bin/chmod -R 0755 /var/run/c-icap");
				shell_exec("/bin/chmod -R 0755 /var/lib/c_icap");
				shell_exec("/bin/chmod -R 0755 /var/tmp");
				shell_exec("/bin/chmod -R 0755 /usr/share/c_icap");
				
				shell_exec("/bin/chown -R $user:$user /var/log/c-icap");
				shell_exec("/bin/chown -R $user:$user /var/run/c-icap");
				shell_exec("/bin/chown -R $user:$user /var/lib/c_icap");
				shell_exec("/bin/chown -R $user:$user /var/tmp");
				shell_exec("/bin/chown -R $user:$user /usr/share/c_icap");
				echo "Starting......: c-icap apply securities on user \"$user\" done\n";
				@file_put_contents("/etc/c-icap.conf",@implode("\n",$conf));
		
	}
	
	private function cicap_whitelisted_servers(){
		$sql="SELECT * FROM dansguardian_files WHERE filename='exceptionsitelist' AND RuleID=1 AND enabled=1";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
	
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$t[]=$ligne["pattern"];
		}
		
		@file_put_contents("/etc/squid3/c-icap.whitelist.txt",@implode("\n",$t));
		
	}
	
	
	function Save(){
		$ini=new Bs_IniHandler();
		$ini->_params=$this->main_array;
		$confArtica=$ini->toString();
		$sock=new sockets();
		$sock->SaveConfigFile($confArtica,'CicapInternalConfiguration');
		$sock->getFrameWork("cmd.php?cicap-reconfigure=yes");
		$sock->getFrameWork("cmd.php?cicap-reload=yes");
		}
	
}




