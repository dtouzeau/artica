<?php

include_once(dirname(__FILE__)."/class.dansguardian.inc");
include_once(dirname(__FILE__)."/class.groups.inc");
include_once(dirname(__FILE__)."/class.c-icap-filter.inc");


class squidguard{
	var $categories=array();
	var $rules=array();
	var $groupsofRule=array();
	var $DefaultRules=array();
	var $DestOfRules=array();
	var $CategoriesDefinitions=array();
	var $rulesOK=array();
	var $ldap_server;
	var $ldap_port;
	var $ldap_suffix;
	var $EnableWhiteList=false;
	var $EnableBlackList=false;
	var $SquidGuardIPWeb=null;
	
	function squidguard(){
		$GLOBALS["guarddb"]="/var/lib/squidguard";
		$this->rules=$this->GetRules();
	}
	
	
	function BuildConf(){
		
		$ldap=new clladp();
		$sock=new sockets();
		$usrs=new usersMenus();
		$this->ldap_port=$ldap->ldap_port;
		$this->ldap_server=$ldap->ldap_host;
		$this->ldap_suffix=$ldap->suffix;
		$this->SquidGuardIPWeb=$sock->GET_INFO("SquidGuardIPWeb");
		if($this->SquidGuardIPWeb==null){$this->SquidGuardIPWeb="$usrs->hostname:9000";}
		$this->SquidGuardIPWeb=str_replace('.(none)',"",$this->SquidGuardIPWeb);
		echo "Starting......: squidGuard verbose mode={$GLOBALS["VERBOSE"]}\n";
		
	
		$ldapbinddn="cn=$ldap->ldap_admin,$ldap->suffix";
		
		$conf[]="dbhome /var/lib/squidguard";
		$conf[]="logdir /var/log/squid";
		//$conf[]="ldapbinddn $ldapbinddn";
 		//$conf[]="ldapbindpass \"$ldap->ldap_password\"";
		//$conf[]="ldapprotover 3";
 		//$conf[]="ldapcachetime 300";		
 		

 		
 		$conf[]=$this->BUILD_SRC();
 		$conf[]="";
 		$conf[]="";
 		$conf[]=$this->BUILD_DEST();
 		$conf[]="";
 		$conf[]="";
 		$conf[]=$this->BUILD_ACLS(); 		
 		
 		
 		
 		return implode("\n",$conf);
		
		
	}
	
	private function GetRules(){
		if(is_array($GLOBALS["RULES_DG"])){return $GLOBALS["RULES_DG"];}
		$cicap=new cicap_filter();
		$array=$cicap->rules;
		if(!is_array($array)){return array();}
		$GLOBALS["RULES_DG"]=$array;
		return $array;
	}
	
	private function BUILD_SRC(){
		$rules=$this->GetRules();
		$this->loadusers();
		if(is_array($this->groupsofRule)){
			echo "Starting......: squidGuard no source rules set\n";
			reset($this->groupsofRule);
			while (list ($ruleID, $sources) = each ($this->groupsofRule)){
				while (list ($a, $b) = each ($sources)){
					$c[$b]=$b;
				}
				$this->rulesOK[$ruleID]=true;
				$t[]="\nsrc {$rules[$ruleID]} {";
				$t[]=implode("\n",$c);
				$t[]="}\n";
				unset($c);
			}
		}
		
		$whitelisted=$this->WhiteList();
		$whitelisted[]="\tip 127.0.0.1";
		echo "Starting......: squidGuard ". count($whitelisted)." whitelisted IP(s)\n";
		if(is_array($whitelisted)){
			
		while (list ($a, $b) = each ($whitelisted)){
				$c[$b]=$b;
			}			
			$this->EnableWhiteList=true;
			$t[]="\nsrc whitelisted {";
			$t[]=implode("\n",$c);
			$t[]="}\n";
			unset($c);
		}
		
		
		$blacklisted=$this->Blacklist();
		echo "Starting......: squidGuard ". count($blacklisted)." blacklisted IP(s)\n";
		if(is_array($blacklisted)){
			while (list ($a, $b) = each ($blacklisted)){
					$c[$b]=$b;
				}			
				$this->EnableBlackList=true;
				$t[]="\nsrc blacklisted {";
				$t[]=implode("\n",$c);
				$t[]="}\n";
				unset($c);
		}		
		
		
		if(is_array($t)){
			return implode("\n",$t);
		}
	}
	
	private function BUILD_ACLS(){
		$rules=$this->GetRules();
		
		$t[]="\nacl{";
			if(is_array($this->DestOfRules)){
				while (list ($ruleID, $categories) = each ($this->DestOfRules)){
					if(!$this->rulesOK[$ruleID]){continue;}
					$t[]=" {$rules[$ruleID]} {";
					$t[]="   pass ". implode(" ",$categories)." all";
					$t[]="   redirect 302:https://$this->SquidGuardIPWeb/exec.squidguard.php?clientaddr=%a&clientname=%n&clientuser=%i&clientgroup=%s&targetgroup=%t&url=%u";
					$t[]=" }";
				}
			}
			
			
		if($this->EnableWhiteList){
			$t[]=" whitelisted {";
			$t[]="   pass all";
			$t[]=" }";
		}
		
		
		if($this->EnableBlackList){
			$t[]=" blacklisted {";
			$t[]="   pass none";
			$t[]="   redirect https://$this->SquidGuardIPWeb/exec.squidguard.php?clientaddr=%a&clientname=%n&clientuser=%i&clientgroup=%s&targetgroup=%t&url=%u";
			$t[]=" }";
		}		

		if(is_array($this->DefaultRules["rule"])){
			
		while (list ($a, $b) = each ($this->DefaultRules["rule"])){
					$c[$b]=$b;
				}
			
				$default_categories=implode(" ",$c);
		}
			$t[]=" default {";
			$t[]="   pass $default_categories all";
			$t[]="   redirect https://$this->SquidGuardIPWeb/exec.squidguard.php?clientaddr=%a&clientname=%n&clientuser=%i&clientgroup=%s&targetgroup=%t&url=%u";
			$t[]=" }";
			$t[]="}";
			
			return implode("\n",$t);
			
	}
	
	
	private function BUILD_DEST(){
		$rules=$this->GetRules();
		$this->loadusers();
		if(!is_array($this->DestOfRules)){
			echo "Starting......: squidGuard no dest rules set\n";
			return;
		}
		
		while (list ($name, $sources) = each ($this->CategoriesDefinitions)){
			
			while (list ($a, $b) = each ($sources)){
				$c[$b]=$b;
			}			
			
			$t[]="\ndest $name {";
			$t[]=implode("\n",$c);
			$t[]="}\n";
			unset($c);
		}		
		
		
		
		if(is_array($t)){
			return implode("\n",$t);
		}
		
		
		
		
	}	
	
	private function loadusers(){
		if(!is_array($this->rules)){return;}
		reset($this->rules);
		
		while (list ($num, $rulename) = each ($this->rules)){
			$this->searchgroups($num);
			$this->dump_exceptionSites($num);
			$this->dump_blockfiles($num);
			$this->SearchCategories($num);
		}
		$this->DefaultCategories();
	}
	
	private function searchgroups($RuleID){
		$sql="SELECT RuleID,group_id FROM `dansguardian_groups` WHERE RuleID=$RuleID";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$u=$this->BuildUserLists($ligne["group_id"]);
			if(is_array($u)){$this->groupsofRule[$RuleID][]="\tuser\t" . implode(" ",$u);unset($u);}else{
				$this->groupsofRule[$RuleID][]="\tuserlist\tGroupNumberID{$ligne["group_id"]}";
			}
			
			
		}
		$sql="SELECT RuleID,pattern FROM `dansguardian_ipgroups` WHERE RuleID=$RuleID";
		$results=$q->QUERY_SQL($sql,"artica_backup");
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				if($ligne["pattern"]==null){continue;}
				$this->groupsofRule[$RuleID][]="\tip\t{$ligne["pattern"]}";
			
		}
	}
	
	
	private function BuildUserLists($gpid){
		$group=new groups($gpid);
		if(is_array($group->members)){
			if(count($group->members)<21){return $group->members;}
			 
			$conf=implode("\n",$group->members);
		}
		
		if($GLOBALS["VERBOSE"]){echo "write {$GLOBALS["guarddb"]}/$gpid\n";}
		file_put_contents("{$GLOBALS["guarddb"]}/GroupNumberID$gpid",$conf);
		if(!is_file("{$GLOBALS["guarddb"]}/GroupNumberID$gpid")){
			echo "Starting......: squidGuard unable to write {$GLOBALS["guarddb"]}/$gpid file!\n";
		}
		return null;
	}
	
	
	
	private function CheckHostname($ip){
		if(trim($ip)==null){return null;}
		if($GLOBALS["VERBOSE"]){echo "CheckHostname($ip)\n";}
		if(substr($ip,strlen($ip)-1,1)=="$"){$ip=substr($ip,0,strlen($ip)-1);}
		if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+#",$ip)){
			if($GLOBALS["VERBOSE"]){echo "CheckHostname($ip) is an hostname, try to resolv\n";}
			$ip=gethostbyname($ip);
		}
		if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+#",$ip)){$ip=null;}
		return $ip;
	}
	
	
	
	private function WhiteList(){
		$q=new mysql();
		$sql="SELECT pattern FROM `dansguardian_files` WHERE filename ='exceptioniplist'";
		if($GLOBALS["VERBOSE"]){echo "WhiteList($sql)\n";}
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$ligne["pattern"]=$this->CheckHostname($ligne["pattern"]);
			if($ligne["pattern"]==null){continue;}
			$array[$ligne["pattern"]]="\tip\t{$ligne["pattern"]}";
		}
		return $array;
	}
	
	private function Blacklist(){
		$q=new mysql();
		$sql="SELECT pattern FROM `dansguardian_files` WHERE filename ='bannediplist'";
		if($GLOBALS["VERBOSE"]){echo "Blacklist($sql)\n";}
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$ligne["pattern"]=$this->CheckHostname($ligne["pattern"]);
			if($ligne["pattern"]==null){continue;}
			$array[$ligne["pattern"]]="\tip {$ligne["pattern"]}";
		}
		return $array;
	}		
		
		
	private function DefaultCategories(){
	if($GLOBALS["VERBOSE"]){echo "######################## DEFAULT CATEGORY ########################\n";}
		
		$q=new mysql();
		
			$main_path="{$GLOBALS["guarddb"]}/personal-categories/W-1";
			if(is_file("$main_path/domains")){
				$this->DefaultRules["rule"][]="W-1";
				$this->CategoriesDefinitions["W-1"][]="\tdomainlist personal-categories/W-1/domains";
				$this->CategoriesDefinitions["W-1"][]="\turllist personal-categories/W-1/urls";
				$this->CategoriesDefinitions["W-1"][]="\texpressionlist personal-categories/W-1/expressions";
				if(!is_file("$main_path/urls")){@file_put_contents("$main_path/urls","www.nowebsite.com/fr");}
				if(!is_file("$main_path/expressions")){@file_put_contents("$main_path/expressions","\n");}
			
			
		}
		
		
		if(is_file("{$GLOBALS["guarddb"]}/personal-categories/filesblock-{$this->rules[$RuleID]}/expressions")){
			$this->DefaultRules["rule"][]="!F-1";
			$this->CategoriesDefinitions["F-1"][]="\texpressionlist personal-categories/filesblock-{$this->rules[$RuleID]}/expressions";
			$this->CategoriesDefinitions["F-1"][]="\turllist personal-categories/filesblock-{$this->rules[$RuleID]}/urls";
			$this->CategoriesDefinitions["F-1"][]="\tdomainlist personal-categories/filesblock-{$this->rules[$RuleID]}/domains";
			if(!is_file("$main_path/urls")){@file_put_contents("$main_path/urls","www.nowebsite.com/fr");}
			if(!is_file("$main_path/domains")){@file_put_contents("$main_path/domains","nowebsite.com");}
			
		}		
		
		
		$sql="SELECT pattern FROM `dansguardian_files` WHERE filename = 'bannedsitelist' AND RuleID=1;";
		
		if($GLOBALS["VERBOSE"]){echo "$sql\n";}
		
		$results=$q->QUERY_SQL($sql,"artica_backup");
		
		if(!$q->ok){if($GLOBALS["VERBOSE"]){echo "$sql\nError $q->mysql_error\n";}}
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			
			$addplus="plus-{$ligne["pattern"]}";
			$addplus=str_replace("/","-",$addplus);
			$addplus_com=$addplus."-artica";
			
			if($GLOBALS["VERBOSE"]){echo "Default rule cat -> \"{$ligne["pattern"]}\" (default)\n";}
			
			if(is_file("{$GLOBALS["guarddb"]}/{$ligne["pattern"]}/domains")){
					$this->CategoriesDefinitions[$ligne["pattern"]][]="\tdomainlist {$ligne["pattern"]}/domains";
					$this->CategoriesDefinitions[$ligne["pattern"]][]="\turllist {$ligne["pattern"]}/urls";
					$this->CategoriesDefinitions[$ligne["pattern"]][]="\texpressionlist {$ligne["pattern"]}/expressions";					
					$this->DefaultRules["rule"][]="!{$ligne["pattern"]}";	

				if(!is_file("{$GLOBALS["guarddb"]}/{$ligne["pattern"]}/urls")){
					file_put_contents("{$GLOBALS["guarddb"]}/{$ligne["pattern"]}/urls","www.nowebsite.com/fr");
				}
				
				if(!is_file("{$GLOBALS["guarddb"]}/{$ligne["pattern"]}/expressions")){
					file_put_contents("{$GLOBALS["guarddb"]}/{$ligne["pattern"]}/expressions","\n");
				}
				
					
				
			}
			
		if(is_file("{$GLOBALS["guarddb"]}/web-filter-plus/BL/{$ligne["pattern"]}/domains")){
					$this->CategoriesDefinitions[$addplus][]="\tdomainlist web-filter-plus/BL/{$ligne["pattern"]}/domains";
					$this->CategoriesDefinitions[$addplus][]="\turllist web-filter-plus/BL/{$ligne["pattern"]}/urls";
					$this->CategoriesDefinitions[$addplus][]="\texpressionlist web-filter-plus/BL/{$ligne["pattern"]}/expressions";
					$this->DefaultRules["rule"][]="!$addplus";
					
					if(!is_file("{$GLOBALS["guarddb"]}/web-filter-plus/BL/{$ligne["pattern"]}/urls")){
						file_put_contents("{$GLOBALS["guarddb"]}/web-filter-plus/BL/{$ligne["pattern"]}/urls","www.nowebsite.com/fr");
					}
					
					if(!is_file("{$GLOBALS["guarddb"]}/web-filter-plus/BL/{$ligne["pattern"]}/expressions")){
						file_put_contents("{$GLOBALS["guarddb"]}/web-filter-plus/BL/{$ligne["pattern"]}/expressions","\n");
					}					
					
					
				}					


			
				$main_path="{$GLOBALS["guarddb"]}/blacklist-artica/{$ligne["pattern"]}";
				if(is_file("$main_path/domains")){
					$this->CategoriesDefinitions[$addplus_com][]="\tdomainlist blacklist-artica/{$ligne["pattern"]}/domains";
					$this->CategoriesDefinitions[$addplus_com][]="\turllist blacklist-artica/{$ligne["pattern"]}/urls";
					$this->CategoriesDefinitions[$addplus_com][]="\texpressionlist blacklist-artica/{$ligne["pattern"]}/expressions";
					$this->DefaultRules["rule"][]="!$addplus_com";
					
					if(!is_file("$main_path/urls")){@file_put_contents("$main_path/urls","www.nowebsite.com/fr");}
					if(!is_file("$main_path/expressions")){@file_put_contents("$main_path/expressions","\n");}						
					
					
				}					

		}
		$count=count($this->DefaultRules["rule"]);
		echo "Starting......: squidGuard Default rule: $count categories\n";
		
			$sql="SELECT category FROM `dansguardian_personal_categories` WHERE category_type = 'enabled' AND RuleID=1;";
			$results=$q->QUERY_SQL($sql,"artica_backup");
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$category=trim($ligne["category"]);
				$categoryPrint=$this->substrCategory($category);
				$this->dump_personals($category);	
				if($GLOBALS["VERBOSE"]){echo "Default rule personal cat -> \"$category\" (default)\n";}			
				
				$main_path="{$GLOBALS["guarddb"]}/personal-categories/$categoryPrint";
				@mkdir($main_path,0666,true);
				if(is_file("$main_path/domains")){
					echo "Starting......: squidGuard Personal category $category ($categoryPrint) for Default rule\n";
					$this->CategoriesDefinitions["P-$categoryPrint"][]="\tdomainlist personal-categories/$categoryPrint/domains";
					$this->CategoriesDefinitions["P-$categoryPrint"][]="\turllist personal-categories/$categoryPrint/urls";
					$this->CategoriesDefinitions["P-$categoryPrint"][]="\texpressionlist personal-categories/$categoryPrint/expressions";
					$this->DefaultRules["rule"][]="!P-$categoryPrint";
					
					if(!is_file("$main_path/urls")){@file_put_contents("$main_path/urls","www.nowebsite.com/fr");}
					if(!is_file("$main_path/expressions")){@file_put_contents("$main_path/expressions","\n");}						
					
				}
				
				
			}		
		
		
		
		
	
	if($GLOBALS["VERBOSE"]){echo "##################################################################\n";}
		
	}
	
	
	private function SearchCategories($RuleID){
		
		$main_path="{$GLOBALS["guarddb"]}/personal-categories/W-$RuleID";
		@mkdir($main_path,0666,true);
		if(is_file("$main_path/domains")){
			$this->DestOfRules[$RuleID]["W-$RuleID"]="W-$RuleID";
			$this->CategoriesDefinitions["W-$RuleID"][]="\tdomainlist personal-categories/W-$RuleID/domains";
			$this->CategoriesDefinitions["W-$RuleID"][]="\turllist personal-categories/W-$RuleID/urls";
			$this->CategoriesDefinitions["W-$RuleID"][]="\texpressionlist personal-categories/W-$RuleID/expressions";
			
			if(!is_file("$main_path/urls")){@file_put_contents("$main_path/urls","www.nowebsite.com/fr");}
			if(!is_file("$main_path/expressions")){@file_put_contents("$main_path/expressions","\n");}					
		}
		
		$main_path="{$GLOBALS["guarddb"]}/personal-categories/filesblock-{$this->rules[$RuleID]}";
		@mkdir($main_path,0666,true);
		if(is_file("$main_path/expressions")){
			$this->DestOfRules[$RuleID]["F-$RuleID"]="!F-$RuleID";
			$this->CategoriesDefinitions["F-$RuleID"][]="\texpressionlist personal-categories/filesblock-{$this->rules[$RuleID]}/expressions";
			$this->CategoriesDefinitions["F-$RuleID"][]="\turllist personal-categories/filesblock-{$this->rules[$RuleID]}/urls";
			$this->CategoriesDefinitions["F-$RuleID"][]="\tdomainlist personal-categories/filesblock-{$this->rules[$RuleID]}/domains";
			if(!is_file("$main_path/urls")){@file_put_contents("$main_path/urls","www.nowebsite.com/fr");}
			if(!is_file("$main_path/domains")){@file_put_contents("$main_path/domains","\n");}				
		}		
		
		
		$q=new mysql();
		$sql="SELECT pattern FROM `dansguardian_files` WHERE filename = 'bannedsitelist' AND RuleID=$RuleID;";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		

		
		
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				
				$addplus="plus-{$ligne["pattern"]}";
				$addplus=str_replace("/","-",$addplus);
				$addplus_com=$addplus."-artica";

				
				$main_path="{$GLOBALS["guarddb"]}/{$ligne["pattern"]}";
				@mkdir($main_path,0666,true);
				if(is_file("$main_path/domains")){
					$this->CategoriesDefinitions[$ligne["pattern"]][]="\tdomainlist {$ligne["pattern"]}/domains";
					$this->CategoriesDefinitions[$ligne["pattern"]][]="\turllist {$ligne["pattern"]}/urls";
					$this->CategoriesDefinitions[$ligne["pattern"]][]="\texpressionlist {$ligne["pattern"]}/expressions";
					$this->DestOfRules[$RuleID][$ligne["pattern"]]="!{$ligne["pattern"]}";	
					if(!is_file("$main_path/urls")){@file_put_contents("$main_path/urls","www.nowebsite.com/fr");}
					if(!is_file("$main_path/expressions")){@file_put_contents("$main_path/expressions","\n");}					
				}
				
				$main_path="{$GLOBALS["guarddb"]}/web-filter-plus/BL/{$ligne["pattern"]}";
				@mkdir($main_path,0666,true);
				if(is_file("$main_path/domains")){
					$this->CategoriesDefinitions[$addplus][]="\tdomainlist web-filter-plus/BL/{$ligne["pattern"]}/domains";
					$this->CategoriesDefinitions[$addplus][]="\turllist web-filter-plus/BL/{$ligne["pattern"]}/urls";
					$this->CategoriesDefinitions[$addplus][]="\texpressionlist web-filter-plus/BL/{$ligne["pattern"]}/expressions";
					$this->DestOfRules[$RuleID][$addplus]="!$addplus";
					if(!is_file("$main_path/urls")){@file_put_contents("$main_path/urls","www.nowebsite.com/fr");}
					if(!is_file("$main_path/expressions")){@file_put_contents("$main_path/expressions","\n");}	
					if($GLOBALS["VERBOSE"]){echo "Adding professional category [$addplus] => {$ligne["pattern"]}\n";}
					}
				
					
				$main_path="{$GLOBALS["guarddb"]}/blacklist-artica/{$ligne["pattern"]}";	
				@mkdir($main_path,0666,true);
				if(is_file("$main_path/domains")){
					$this->CategoriesDefinitions[$addplus_com][]="\tdomainlist blacklist-artica/{$ligne["pattern"]}/domains";
					$this->CategoriesDefinitions[$addplus_com][]="\turllist blacklist-artica/{$ligne["pattern"]}/urls";
					$this->CategoriesDefinitions[$addplus_com][]="\texpressionlist blacklist-artica/{$ligne["pattern"]}/expressions";
					
					$this->DestOfRules[$RuleID][$addplus_com]="!$addplus_com";
					if(!is_file("$main_path/urls")){@file_put_contents("$main_path/urls","www.nowebsite.com/fr");}
					if(!is_file("$main_path/expressions")){@file_put_contents("$main_path/expressions","\n");}					
					}					
				
					
				
			}
			
			$sql="SELECT category FROM `dansguardian_personal_categories` WHERE category_type = 'enabled' AND RuleID=$RuleID;";
			$results=$q->QUERY_SQL($sql,"artica_backup");
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$category=trim($ligne["category"]);
				$categoryPrint=$this->substrCategory($category);
				$this->dump_personals($category);				
				
				$main_path="{$GLOBALS["guarddb"]}/personal-categories/$categoryPrint";	
				@mkdir($main_path,0666,true);
				if(is_file("$main_path/domains")){
					echo "Starting......: squidGuard Personal category $category ($categoryPrint)\n";
					$this->CategoriesDefinitions["P-$categoryPrint"][]="\tdomainlist personal-categories/$categoryPrint/domains";
					$this->CategoriesDefinitions["P-$categoryPrint"][]="\turllist personal-categories/$categoryPrint/urls";
					$this->CategoriesDefinitions["P-$categoryPrint"][]="\texpressionlist personal-categories/$categoryPrint/expressions";
					if(!is_file("$main_path/urls")){@file_put_contents("$main_path/urls","www.nowebsite.com/fr");}
					if(!is_file("$main_path/expressions")){@file_put_contents("$main_path/expressions","\n");}						
					$this->DestOfRules[$RuleID]["P-$categoryPrint"]="!P-$categoryPrint";
				}
				
				
			}
		
	}
	
	
	private function dump_exceptionSites($RuleID){
		@mkdir("{$GLOBALS["guarddb"]}/personal-categories/W-$RuleID",0755,true);
		$File="{$GLOBALS["guarddb"]}/personal-categories/W-$RuleID/domains";
		
		$count=0;
		$q=new mysql();
		$sql="SELECT pattern FROM `dansguardian_files` WHERE filename = 'exceptionsitelist' 
		AND RuleID=$RuleID AND enabled=1;";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		$fh = fopen($File, 'w');
		
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$datas=trim($ligne["pattern"]);
				$count=$count+1;
				if($datas==null){continue;}
				fwrite($fh, $datas."\n");	
			}
		
		echo "Starting......: squidGuard Exception sites: {$this->rules[$RuleID]} $count rows\n";
		fclose($fh);
	}
	
	
	private function substrCategory($name){
		$name=str_replace("(","",$name);
		$name=str_replace(")","",$name);
		$name=str_replace("[","",$name);
		$name=str_replace("]","",$name);
		$name=str_replace(" ","_",$name);
		$name=str_replace(".","",$name);
		return $name;
		
	}
	
	
	private function dump_personals($category_name){
		if($GLOBALS["CATEGORIES_SAVED"][$category_name]){return;}
		$categoryPrint=$this->substrCategory($category_name);
		
		//echo "Starting......: squidGuard personal: base path {$GLOBALS["guarddb"]}\n";
		@mkdir("{$GLOBALS["guarddb"]}/personal-categories/$categoryPrint",0755,true);
		$File="{$GLOBALS["guarddb"]}/personal-categories/$categoryPrint/domains";
		$sql="SELECT pattern FROM `dansguardian_personal_categories` WHERE category_type = 'data' AND category='$category_name';";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		$fh = fopen($File, 'w');
		$count=0;
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$datas=trim($ligne["pattern"]);
			$count=$count+1;
			if($datas==null){continue;}
			fwrite($fh, $datas."\n");
			}

		fclose($fh);
		if(!is_file($File)){
			echo "Starting......: squidGuard personal: $category_name ($categoryPrint) failed!\n";
			return;
		}
		echo "Starting......: squidGuard personal: $category_name $count ($categoryPrint) rows\n";
		$GLOBALS["CATEGORIES_SAVED"][$category_name]=true;
		
	}
	
	private function dump_blockfiles($RuleID){
		@mkdir("{$GLOBALS["guarddb"]}/personal-categories/filesblock-{$this->rules[$RuleID]}",0755,true);
		$File="{$GLOBALS["guarddb"]}/personal-categories/filesblock-{$this->rules[$RuleID]}/expressions";
		@unlink($File);
		$q=new mysql();
		$sql="SELECT pattern FROM `dansguardian_files` WHERE 
			filename ='bannedextensionlist'
			AND enabled=1 AND RuleID=$RuleID";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$ext=trim($ligne["pattern"]);
			if($ext==null){continue;}
			if(preg_match("#\.(.+)#",$ext,$re)){$f[]=$re[1];}
		}
		$count=count($f);
		
		
		if(is_array($f)){
			echo "Starting......: squidGuard block files: {$this->rules[$RuleID]} $count rows\n";
			$expression="(/\.)(". implode("|",$f).")$\n";
			$expression=$expression."(/\.)(". implode("|",$f).")(/\.)(". implode("|",$f).")$";
			file_put_contents($File,$expression);
		}
		
		
	}
	
	
	
	
}

?>