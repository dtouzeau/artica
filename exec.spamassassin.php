<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

	include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
	include_once(dirname(__FILE__) . '/ressources/class.spamassassin.inc');
	include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
	include_once(dirname(__FILE__).  '/framework/class.unix.inc');
	include_once(dirname(__FILE__).  '/framework/frame.class.inc');
	include_once(dirname(__FILE__).  '/ressources/class.os.system.inc');
	include_once(dirname(__FILE__).  '/ressources/class.system.network.inc');	
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}	
	$user=new usersMenus();
	if(!$user->spamassassin_installed){
		write_syslog("want to change spamassassin settings but not installed",__FILE__);
		die();
	}
	
	if(!is_file($user->spamassassin_conf_path)){
		write_syslog("want to change spamassassin settings but could not stat main configuration file",__FILE__);
	}
	
	
	if($argv[1]=='--spf'){spf();die();}
	if($argv[1]=='--dkim'){dkim();die();}
	if($argv[1]=='--dnsbl'){dnsbl();die();}
	
	
SaveConf();
RelayCountryPlugin();


function SaveConf(){
	$user=new usersMenus();
	$spam=new spamassassin();
	$datas=$spam->BuildConfig();
	file_put_contents($user->spamassassin_conf_path,$datas);
}

function RelayCountryPlugin(){	
	$user=new usersMenus();
	CleanConf($user->spamassassin_conf_path);
	
	if(!$user->spamassassin_ipcountry){
		write_syslog("wants  to add IP countries but IP::Country::Fast is not installed.",__FILE__);
		return null;
	}
	
	$spam=new spamassassin();
	
	$RelayCountryPlugin_path=dirname($user->spamassassin_conf_path)."/RelayCountryPlugin.cf";
	$init_pre=dirname($user->spamassassin_conf_path)."/init.pre";
	
	
	if(is_array($spam->main_country)){
		while (list ($country_code, $array) = each ($spam->main_country)){
			if(trim($country_code)==null){continue;}
			$count=$count+1;
			$conf=$conf."header\tRELAYCOUNTRY_$country_code X-Relay-Countries =~ /$country_code/\n";
   			$conf=$conf."describe        RELAYCOUNTRY_$country_code Relayed through {$array["country_name"]}\n";
   			$conf=$conf."score           RELAYCOUNTRY_$country_code {$array["score"]}\n\n";
		
		}
	}
	
	file_put_contents($RelayCountryPlugin_path,$conf);
	write_syslog("Saved $count Countries into spamassassin configuration",__FILE__);
	if($count>1){
		$file=file_get_contents($user->spamassassin_conf_path);
		$file=$file . "\n##### Relay Countries\ninclude\t$RelayCountryPlugin_path\n";
		$file=$file . "add_header all Relay-Country _RELAYCOUNTRY_\n\n";
		file_put_contents($user->spamassassin_conf_path,$file);
		$file=null;
		$file=file_get_contents($init_pre);
		$file=$file . "\nloadplugin\tMail::SpamAssassin::Plugin::RelayCountry\n";
		file_put_contents($init_pre,$file);
		}
	
}
	
	
	
function CleanConf($confPath){
	
	$file=file_get_contents($confPath);
	$init_pre=dirname($confPath)."/init.pre";
	$array=explode("\n",$file);
	while (list ($num, $line) = each ($array)){
		if(trim($line)==null){continue;}
		if(preg_match("#^\##",$line)){continue;}
		if(preg_match("#^include\s+".dirname($confPath)."/RelayCountryPlugin\.cf#",$line)){continue;}
		if(preg_match("#^add_header.+?Relay-Country#",$line)){continue;}
		$conf=$conf . "$line\n";
	}
	
	file_put_contents($confPath,$conf);
	$conf=null;
	
	if(file_exists($init_pre)){
		$file=file_get_contents($init_pre);
		$array=explode("\n",$file);
		while (list ($num, $line) = each ($array)){
			if(trim($line)==null){continue;}
			if(preg_match("#^\##",$line)){continue;}
			if(preg_match("#loadplugin.+?RelayCountry#",$line)){continue;}
			$conf=$conf . "$line\n";
		}		
		file_put_contents($init_pre,$conf);
	}
}

function spf(){
	
	$sock=new sockets();
	$EnableSPF=$sock->GET_INFO("EnableSPF");
	if($EnableSPF==null){$EnableSPF=1;}
	
	
	
	$Config=unserialize(base64_decode($sock->GET_INFO("SpamAssassinSPFConfig")));
	if($GLOBALS["VERBOSE"]){print_r($Config);}
	
	if(!is_array($Config)){$Config=array();}
	if($Config["SPF_PASS_1"]==null){$Config["SPF_PASS_1"]="-0.001";}
	if($Config["SPF_PASS_2"]==null){$Config["SPF_PASS_2"]="-";}
	if($Config["SPF_PASS_3"]==null){$Config["SPF_PASS_3"]="-";}
	if($Config["SPF_PASS_4"]==null){$Config["SPF_PASS_4"]="-";}	
	if($Config["SPF_HELO_PASS_1"]==null){$Config["SPF_HELO_PASS_1"]="-0.001";}
	if($Config["SPF_HELO_PASS_2"]==null){$Config["SPF_HELO_PASS_2"]="-";}
	if($Config["SPF_HELO_PASS_3"]==null){$Config["SPF_HELO_PASS_3"]="-";}
	if($Config["SPF_HELO_PASS_4"]==null){$Config["SPF_HELO_PASS_4"]="-";}	
	if($Config["SPF_FAIL_1"]==null){$Config["SPF_FAIL_1"]="0";}
	if($Config["SPF_FAIL_2"]==null){$Config["SPF_FAIL_2"]="1.333";}
	if($Config["SPF_FAIL_3"]==null){$Config["SPF_FAIL_3"]="0";}
	if($Config["SPF_FAIL_4"]==null){$Config["SPF_FAIL_4"]="1.142";}	
	if($Config["SPF_HELO_FAIL_1"]==null){$Config["SPF_HELO_FAIL_1"]="0";}
	if($Config["SPF_HELO_FAIL_2"]==null){$Config["SPF_HELO_FAIL_2"]="-";}
	if($Config["SPF_HELO_FAIL_3"]==null){$Config["SPF_HELO_FAIL_3"]="-";}
	if($Config["SPF_HELO_FAIL_4"]==null){$Config["SPF_HELO_FAIL_4"]="-";}		
	if($Config["SPF_HELO_NEUTRAL_1"]==null){$Config["SPF_HELO_NEUTRAL_1"]="0";}
	if($Config["SPF_HELO_NEUTRAL_2"]==null){$Config["SPF_HELO_NEUTRAL_2"]="-";}
	if($Config["SPF_HELO_NEUTRAL_3"]==null){$Config["SPF_HELO_NEUTRAL_3"]="-";}
	if($Config["SPF_HELO_NEUTRAL_4"]==null){$Config["SPF_HELO_NEUTRAL_4"]="-";}			
	if($Config["SPF_NEUTRAL_1"]==null){$Config["SPF_NEUTRAL_1"]="0";}
	if($Config["SPF_NEUTRAL_2"]==null){$Config["SPF_NEUTRAL_2"]="1.379";}
	if($Config["SPF_NEUTRAL_3"]==null){$Config["SPF_NEUTRAL_3"]="0";}
	if($Config["SPF_NEUTRAL_4"]==null){$Config["SPF_NEUTRAL_4"]="1.069";}			
	if($Config["SPF_SOFTFAIL_1"]==null){$Config["SPF_SOFTFAIL_1"]="0";}
	if($Config["SPF_SOFTFAIL_2"]==null){$Config["SPF_SOFTFAIL_2"]="1.470";}
	if($Config["SPF_SOFTFAIL_3"]==null){$Config["SPF_SOFTFAIL_3"]="0";}
	if($Config["SPF_SOFTFAIL_4"]==null){$Config["SPF_SOFTFAIL_4"]="1.384";}	
	if($Config["SPF_HELO_SOFTFAIL_1"]==null){$Config["SPF_HELO_SOFTFAIL_1"]="0";}
	if($Config["SPF_HELO_SOFTFAIL_2"]==null){$Config["SPF_HELO_SOFTFAIL_2"]="2.078";}
	if($Config["SPF_HELO_SOFTFAIL_3"]==null){$Config["SPF_HELO_SOFTFAIL_3"]="0";}
	if($Config["SPF_HELO_SOFTFAIL_4"]==null){$Config["SPF_HELO_SOFTFAIL_4"]="2.432";}
	
	while (list ($key, $val) = each ($Config) ){
		if($val=="-"){$Config[$key]=null;}
	}
	

$conf[]="ifplugin Mail::SpamAssassin::Plugin::SPF";
$conf[]=trim("score SPF_PASS {$Config["SPF_PASS_1"]} {$Config["SPF_PASS_2"]} {$Config["SPF_PASS_3"]} {$Config["SPF_PASS_4"]}");
$conf[]=trim("score SPF_HELO_PASS {$Config["SPF_HELO_PASS_1"]} {$Config["SPF_HELO_PASS_2"]} {$Config["SPF_HELO_PASS_3"]} {$Config["SPF_HELO_PASS_4"]}");
$conf[]=trim("score SPF_FAIL {$Config["SPF_FAIL_1"]} {$Config["SPF_FAIL_2"]} {$Config["SPF_FAIL_3"]} {$Config["SPF_FAIL_4"]}");
$conf[]=trim("score SPF_HELO_FAIL {$Config["SPF_HELO_FAIL_1"]} {$Config["SPF_HELO_FAIL_2"]} {$Config["SPF_HELO_FAIL_3"]} {$Config["SPF_HELO_FAIL_4"]}");
$conf[]=trim("score SPF_HELO_NEUTRAL {$Config["SPF_HELO_NEUTRAL_1"]} {$Config["SPF_HELO_NEUTRAL_2"]} {$Config["SPF_HELO_NEUTRAL_3"]} {$Config["SPF_HELO_NEUTRAL_4"]}");
$conf[]=trim("score SPF_HELO_SOFTFAIL {$Config["SPF_HELO_SOFTFAIL_1"]} {$Config["SPF_HELO_SOFTFAIL_2"]} {$Config["SPF_HELO_SOFTFAIL_3"]} {$Config["SPF_HELO_SOFTFAIL_4"]}");
$conf[]=trim("score SPF_NEUTRAL {$Config["SPF_NEUTRAL_1"]} {$Config["SPF_NEUTRAL_2"]} {$Config["SPF_NEUTRAL_3"]} {$Config["SPF_NEUTRAL_4"]}");
$conf[]=trim("score SPF_SOFTFAIL {$Config["SPF_SOFTFAIL_1"]} {$Config["SPF_SOFTFAIL_2"]} {$Config["SPF_SOFTFAIL_3"]} {$Config["SPF_SOFTFAIL_4"]}");
$conf[]="";
$conf[]="header USER_IN_SPF_WHITELIST	eval:check_for_spf_whitelist_from()";
$conf[]="describe USER_IN_SPF_WHITELIST	From: address is in the user's SPF whitelist";
$conf[]="tflags USER_IN_SPF_WHITELIST	userconf nice noautolearn net";
$conf[]="";
$conf[]="header USER_IN_DEF_SPF_WL	eval:check_for_def_spf_whitelist_from()";
$conf[]="describe USER_IN_DEF_SPF_WL	From: address is in the default SPF white-list";
$conf[]="tflags USER_IN_DEF_SPF_WL	userconf nice noautolearn net";
$conf[]="";
$conf[]="header __ENV_AND_HDR_FROM_MATCH	eval:check_for_matching_env_and_hdr_from()";
$conf[]="meta ENV_AND_HDR_SPF_MATCH	(USER_IN_DEF_SPF_WL && __ENV_AND_HDR_FROM_MATCH)";
$conf[]="describe ENV_AND_HDR_SPF_MATCH	Env and Hdr From used in default SPF WL Match";
$conf[]="tflags ENV_AND_HDR_SPF_MATCH	userconf nice noautolearn net";
$conf[]="";
$conf[]="";	

$conf[]="";

	$q=new mysql();
	$sql="SELECT * FROM spamassassin_spf_wl ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	if(!$q->ok){
		echo "Starting......: spamassassin Mysql fatal error !\n";
		return; 
	}
	$count=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$count=$count+1;
		$conf[]="def_whitelist_from_spf   {$ligne["domain"]}";
		
	}
$conf[]="endif";
$conf[]="";	
@file_put_contents("/etc/spamassassin/spf.pre",@implode("\n",$conf));
echo "Starting......: spamassassin writing spf.pre done ($count whitelisted sender(s))\n";


}

function dkim(){
	
	
	$sock=new sockets();
	$enable_dkim_verification=$sock->GET_INFO("enable_dkim_verification");
	
	if($enable_dkim_verification<>1){
		@file_put_contents("/etc/spamassassin/dkim.pre","");
		echo "Starting......: spamassassin writing dkim.pre inbound verification disabled\n";
		return ;
	}
	
$r[]="  score DKIM_VERIFIED -0.1";
$r[]="  score DKIM_SIGNED    0";
$r[]="";
$r[]="  # don't waste time on fetching ASP record, hardly anyone publishes it";
$r[]="  score DKIM_POLICY_SIGNALL  0";
$r[]="  score DKIM_POLICY_SIGNSOME 0";
$r[]="  score DKIM_POLICY_TESTING  0";
$r[]="";
$r[]="  # DKIM-based whitelisting of domains with good reputation:";
$r[]="  score USER_IN_DKIM_WHITELIST -8.0";
$r[]="";

	$q=new mysql();
	$sql="SELECT * FROM spamassassin_dkim_wl ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	if(!$q->ok){
		echo "Starting......: spamassassin Mysql fatal error ! (DKIM)\n";
		return; 
	}
	$count=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$count=$count+1;
		$r[]="whitelist_from_dkim   {$ligne["domain"]}";
		
	}
$r[]="";
$r[]="loadplugin Mail::SpamAssassin::Plugin::DKIM";
$r[]="  # DKIM-based whitelisting of domains with less then perfect";
$r[]="  # reputation can be given fewer negative score points:";
$r[]="  score USER_IN_DEF_DKIM_WL -1.5";
$r[]="  def_whitelist_from_dkim   *@google.com";
$r[]="  def_whitelist_from_dkim   *@googlemail.com";
$r[]="  def_whitelist_from_dkim   *@*  googlegroups.com";
$r[]="  def_whitelist_from_dkim   *@*  yahoogroups.com";
$r[]="  def_whitelist_from_dkim   *@*  yahoogroups.co.uk";
$r[]="  def_whitelist_from_dkim   *@*  yahoogroupes.fr";
$r[]="  def_whitelist_from_dkim   *@yousendit.com";
$r[]="  def_whitelist_from_dkim   *@meetup.com";
$r[]="  def_whitelist_from_dkim   dailyhoroscope@astrology.com";
$r[]="";
$r[]="  # reduce default scores, which are being abused";
$r[]="  score ENV_AND_HDR_DKIM_MATCH -0.1";
$r[]="  score ENV_AND_HDR_SPF_MATCH  -0.5";
$r[]="";
$r[]="  header   __ML1        Precedence =~ m{\b(list|bulk)\b}i";
$r[]="  header   __ML2        exists:List-Id";
$r[]="  header   __ML3        exists:List-Post";
$r[]="  header   __ML4        exists:Mailing-List";
$r[]="  header   __ML5        Return-Path:addr =~ m{^([^\@]+-(request|bounces|admin|owner)|owner-[^\@]+)(\@|\z)}mi";
$r[]="  meta     __VIA_ML     __ML1 || __ML2 || __ML3 || __ML4 || __ML5";
$r[]="  describe __VIA_ML     Mail from a mailing list";
$r[]="";
$r[]="  header   __AUTH_YAHOO1  From:addr =~ m{[\@.]yahoo\.com$}mi";
$r[]="  header   __AUTH_YAHOO2  From:addr =~ m{\@yahoo\.com\.(ar|au|br|cn|hk|mx|my|ph|sg|tw)$}mi";
$r[]="  header   __AUTH_YAHOO3  From:addr =~ m{\@yahoo\.co\.(id|in|jp|nz|th|uk)$}mi";
$r[]="  header   __AUTH_YAHOO4  From:addr =~ m{\@yahoo\.(ca|cn|de|dk|es|fr|gr|ie|it|no|pl|se)$}mi";
$r[]="  meta     __AUTH_YAHOO   __AUTH_YAHOO1 || __AUTH_YAHOO2 || __AUTH_YAHOO3 || __AUTH_YAHOO4";
$r[]="  describe __AUTH_YAHOO   Author claims to be from Yahoo";
$r[]="";
$r[]="  header   __AUTH_GMAIL   From:addr =~ m{\@gmail\.com$}mi";
$r[]="  describe __AUTH_GMAIL   Author claims to be from gmail.com";
$r[]="";
$r[]="  header   __AUTH_PAYPAL  From:addr =~ /[\@.]paypal\.(com|co\.uk)$/mi";
$r[]="  describe __AUTH_PAYPAL  Author claims to be from PayPal";
$r[]="";
$r[]="  header   __AUTH_EBAY    From:addr =~ /[\@.]ebay\.(com|at|be|ca|ch|de|ee|es|fr|hu|ie|in|it|nl|ph|pl|pt|se|co\.(kr|uk)|com\.(au|cn|hk|mx|my|sg))$/mi";
$r[]="  describe __AUTH_EBAY    Author claims to be from eBay";
$r[]="";
$r[]="  meta     NOTVALID_YAHOO !DKIM_VERIFIED && __AUTH_YAHOO && !__VIA_ML";
$r[]="  priority NOTVALID_YAHOO 500";
$r[]="  describe NOTVALID_YAHOO Claims to be from Yahoo but is not";
$r[]="";
$r[]="  meta     NOTVALID_GMAIL !DKIM_VERIFIED && __AUTH_GMAIL && !__VIA_ML";
$r[]="  priority NOTVALID_GMAIL 500";
$r[]="  describe NOTVALID_GMAIL Claims to be from gmail.com but is not";
$r[]="";
$r[]="  meta     NOTVALID_PAY   !DKIM_VERIFIED && (__AUTH_PAYPAL || __AUTH_EBAY)";
$r[]="  priority NOTVALID_PAY   500";
$r[]="  describe NOTVALID_PAY   Claims to be from PayPal or eBay, but is not";
$r[]="";
$r[]="  score    NOTVALID_YAHOO  2.8";
$r[]="  score    NOTVALID_GMAIL  2.8";
$r[]="  score    NOTVALID_PAY    6";
$r[]="";
$r[]="  # accept replies from abuse@yahoo.com even if not dkim/dk-signed:";
$r[]="  whitelist_from_rcvd abuse@yahoo.com          yahoo.com";
$r[]="  whitelist_from_rcvd MAILER-DAEMON@yahoo.com  yahoo.com";
$r[]="";	
@file_put_contents("/etc/spamassassin/dkim.pre",@implode("\n",$r));
echo "Starting......: spamassassin writing dkim.pre done ($count whitelisted sender(s))\n";	
}

function dnsbl(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("SpamassassinDNSBL")));
	$count=0;
	if(!is_array($datas)){
		@file_put_contents("/etc/spamassassin/dnsbl.pre","#");
		return ;	
	}
	
	while (list ($key, $vlue) = each ($datas)){
		if($vlue==null){continue;}
		if($vlue==0){continue;}	
		$count=$count+1;
	}
	if($count==0){
		@file_put_contents("/etc/spamassassin/dnsbl.pre","#");
		return;	
	}
	
	
# SpamAssassin rules file: DNS blacklist tests";
$conf[]="#";
$conf[]="# Please don't modify this file as your changes will be overwritten with";
$conf[]="# the next update. Use @@LOCAL_RULES_DIR@@/local.cf instead.";
$conf[]="# See 'perldoc Mail::SpamAssassin::Conf' for details.";
$conf[]="#";
$conf[]="# <@LICENSE>";
$conf[]="# Copyright 2004 Apache Software Foundation";
$conf[]="#";
$conf[]="# Licensed under the Apache License, Version 2.0 (the \"License\");";
$conf[]="# you may not use this file except in compliance with the License.";
$conf[]="# You may obtain a copy of the License at";
$conf[]="#";
$conf[]="#     http://www.apache.org/licenses/LICENSE-2.0";
$conf[]="#";
$conf[]="# Unless required by applicable law or agreed to in writing, software";
$conf[]="# distributed under the License is distributed on an \"AS IS\" BASIS,";
$conf[]="# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.";
$conf[]="# See the License for the specific language governing permissions and";
$conf[]="# limitations under the License.";
$conf[]="# </@LICENSE>";
$conf[]="#";
$conf[]="###########################################################################";
$conf[]="";
$conf[]="require_version @@VERSION@@";
$conf[]="";
$conf[]="# See the Mail::SpamAssassin::Conf manual page for details of how to use";
$conf[]="# check_rbl().";
$conf[]="";
$conf[]="# ---------------------------------------------------------------------------";
$conf[]="# Multizone / Multi meaning BLs first.";
$conf[]="#";
$conf[]="# Note that currently TXT queries cannot be used for these, since the";
$conf[]="# DNSBLs do not return the A type (127.0.0.x) as part of the TXT reply.";
$conf[]="# Well, at least NJABL doesn't, it seems, as of Apr 7 2003.";
$conf[]="";
if($datas["njabl"]==1){
	$conf[]="# ---------------------------------------------------------------------------";
	$conf[]="# NJABL";
	$conf[]="# URL: http://www.dnsbl.njabl.org/";
	$conf[]="";
	$conf[]="header __RCVD_IN_NJABL		eval:check_rbl('njabl', 'combined.njabl.org.')";
	$conf[]="describe __RCVD_IN_NJABL	Received via a relay in combined.njabl.org";
	$conf[]="tflags __RCVD_IN_NJABL		net";
	$conf[]="";
	$conf[]="header RCVD_IN_NJABL_RELAY	eval:check_rbl_sub('njabl', '127.0.0.2')";
	$conf[]="describe RCVD_IN_NJABL_RELAY	NJABL: sender is confirmed open relay";
	$conf[]="tflags RCVD_IN_NJABL_RELAY	net";
	$conf[]="";
	$conf[]="header RCVD_IN_NJABL_DUL	eval:check_rbl('njabl-notfirsthop', 'combined.njabl.org.', '127.0.0.3')";
	$conf[]="describe RCVD_IN_NJABL_DUL	NJABL: dialup sender did non-local SMTP";
	$conf[]="tflags RCVD_IN_NJABL_DUL	net";
	$conf[]="";
	$conf[]="header RCVD_IN_NJABL_SPAM	eval:check_rbl_sub('njabl', '127.0.0.4')";
	$conf[]="describe RCVD_IN_NJABL_SPAM	NJABL: sender is confirmed spam source";
	$conf[]="tflags RCVD_IN_NJABL_SPAM	net";
	$conf[]="";
	$conf[]="header RCVD_IN_NJABL_MULTI	eval:check_rbl_sub('njabl', '127.0.0.5')";
	$conf[]="describe RCVD_IN_NJABL_MULTI	NJABL: sent through multi-stage open relay";
	$conf[]="tflags RCVD_IN_NJABL_MULTI	net";
	$conf[]="";
	$conf[]="header RCVD_IN_NJABL_CGI	eval:check_rbl_sub('njabl', '127.0.0.8')";
	$conf[]="describe RCVD_IN_NJABL_CGI	NJABL: sender is an open formmail";
	$conf[]="tflags RCVD_IN_NJABL_CGI	net";
	$conf[]="";
	$conf[]="header RCVD_IN_NJABL_PROXY	eval:check_rbl_sub('njabl', '127.0.0.9')";
	$conf[]="describe RCVD_IN_NJABL_PROXY	NJABL: sender is an open proxy";
	$conf[]="tflags RCVD_IN_NJABL_PROXY	net";
	$conf[]="";
}

if($datas["SORBS"]==1){
	$conf[]="# ---------------------------------------------------------------------------";
	$conf[]="# SORBS";
	$conf[]="# transfers: both axfr and ixfr available";
	$conf[]="# URL: http://www.dnsbl.sorbs.net/";
	$conf[]="# pay-to-use: no";
	$conf[]="# delist: $50 fee for RCVD_IN_SORBS_SPAM, others have free retest on request";
	$conf[]="";
	$conf[]="header __RCVD_IN_SORBS		eval:check_rbl('sorbs', 'dnsbl.sorbs.net.')";
	$conf[]="describe __RCVD_IN_SORBS	SORBS: sender is listed in SORBS";
	$conf[]="tflags __RCVD_IN_SORBS		net";
	$conf[]="";
	$conf[]="header RCVD_IN_SORBS_HTTP	eval:check_rbl_sub('sorbs', '127.0.0.2')";
	$conf[]="describe RCVD_IN_SORBS_HTTP	SORBS: sender is open HTTP proxy server";
	$conf[]="tflags RCVD_IN_SORBS_HTTP	net";
	$conf[]="";
	$conf[]="header RCVD_IN_SORBS_MISC	eval:check_rbl_sub('sorbs', '127.0.0.3')";
	$conf[]="describe RCVD_IN_SORBS_MISC	SORBS: sender is open proxy server";
	$conf[]="tflags RCVD_IN_SORBS_MISC	net";
	$conf[]="";
	$conf[]="header RCVD_IN_SORBS_SMTP	eval:check_rbl_sub('sorbs', '127.0.0.4')";
	$conf[]="describe RCVD_IN_SORBS_SMTP	SORBS: sender is open SMTP relay";
	$conf[]="tflags RCVD_IN_SORBS_SMTP	net";
	$conf[]="";
	$conf[]="header RCVD_IN_SORBS_SOCKS	eval:check_rbl_sub('sorbs', '127.0.0.5')";
	$conf[]="describe RCVD_IN_SORBS_SOCKS	SORBS: sender is open SOCKS proxy server";
	$conf[]="tflags RCVD_IN_SORBS_SOCKS	net";
	$conf[]="";
	$conf[]="# delist: $50 fee";
	$conf[]="#header RCVD_IN_SORBS_SPAM	eval:check_rbl_sub('sorbs', '127.0.0.6')";
	$conf[]="#describe RCVD_IN_SORBS_SPAM	SORBS: sender is a spam source";
	$conf[]="#tflags RCVD_IN_SORBS_SPAM	net";
	$conf[]="";
	$conf[]="header RCVD_IN_SORBS_WEB	eval:check_rbl_sub('sorbs', '127.0.0.7')";
	$conf[]="describe RCVD_IN_SORBS_WEB	SORBS: sender is a abuseable web server";
	$conf[]="tflags RCVD_IN_SORBS_WEB	net";
	$conf[]="";
	$conf[]="header RCVD_IN_SORBS_BLOCK	eval:check_rbl_sub('sorbs', '127.0.0.8')";
	$conf[]="describe RCVD_IN_SORBS_BLOCK	SORBS: sender demands to never be tested";
	$conf[]="tflags RCVD_IN_SORBS_BLOCK	net";
	$conf[]="";
	$conf[]="header RCVD_IN_SORBS_ZOMBIE	eval:check_rbl_sub('sorbs', '127.0.0.9')";
	$conf[]="describe RCVD_IN_SORBS_ZOMBIE	SORBS: sender is on a hijacked network";
	$conf[]="tflags RCVD_IN_SORBS_ZOMBIE	net";
	$conf[]="";
	$conf[]="header RCVD_IN_SORBS_DUL	eval:check_rbl('sorbs-notfirsthop', 'dnsbl.sorbs.net.', '127.0.0.10')";
	$conf[]="describe RCVD_IN_SORBS_DUL	SORBS: sent directly from dynamic IP address";
	$conf[]="tflags RCVD_IN_SORBS_DUL	net";
	$conf[]="";
}

if($datas["Spamhaus"]==1){
	$conf[]="# ---------------------------------------------------------------------------";
	$conf[]="# Spamhaus SBL+XBL";
	$conf[]="#";
	$conf[]="# Spamhaus XBL contains both the Abuseat CBL (cbl.abuseat.org) and Blitzed";
	$conf[]="# OPM (opm.blitzed.org) lists so it's not necessary to query those as well.";
	$conf[]="";
	$conf[]="header __RCVD_IN_SBL_XBL	eval:check_rbl('sblxbl', 'sbl-xbl.spamhaus.org.')";
	$conf[]="describe __RCVD_IN_SBL_XBL	Received via a relay in Spamhaus SBL+XBL";
	$conf[]="tflags __RCVD_IN_SBL_XBL	net";
	$conf[]="";
	$conf[]="# SBL is the Spamhaus Block List: http://www.spamhaus.org/sbl/";
	$conf[]="header RCVD_IN_SBL		eval:check_rbl_sub('sblxbl', '127.0.0.2')";
	$conf[]="describe RCVD_IN_SBL		Received via a relay in Spamhaus SBL";
	$conf[]="tflags RCVD_IN_SBL		net";
	$conf[]="";
	$conf[]="# XBL is the Exploits Block List: http://www.spamhaus.org/xbl/";
	$conf[]="header RCVD_IN_XBL		eval:check_rbl('sblxbl-notfirsthop', 'sbl-xbl.spamhaus.org.', '127.0.0.[456]')";
	$conf[]="describe RCVD_IN_XBL		Received via a relay in Spamhaus XBL";
	$conf[]="tflags RCVD_IN_XBL		net";
	$conf[]="";
}

if($datas["RFC-Ignorant"]==1){
	$conf[]="# ---------------------------------------------------------------------------";
	$conf[]="# RFC-Ignorant blacklists (both name and IP based)";
	$conf[]="";
	$conf[]="header __RFC_IGNORANT_ENVFROM	eval:check_rbl_envfrom('rfci_envfrom', 'fulldom.rfc-ignorant.org.')";
	$conf[]="tflags __RFC_IGNORANT_ENVFROM	net";
	$conf[]="";
	$conf[]="header DNS_FROM_RFC_DSN		eval:check_rbl_sub('rfci_envfrom', '127.0.0.2')";
	$conf[]="describe DNS_FROM_RFC_DSN	Envelope sender in dsn.rfc-ignorant.org";
	$conf[]="tflags DNS_FROM_RFC_DSN		net";
	$conf[]="";
	$conf[]="header DNS_FROM_RFC_POST	eval:check_rbl_sub('rfci_envfrom', '127.0.0.3')";
	$conf[]="describe DNS_FROM_RFC_POST	Envelope sender in postmaster.rfc-ignorant.org";
	$conf[]="tflags DNS_FROM_RFC_POST	net";
	$conf[]="";
	$conf[]="header DNS_FROM_RFC_ABUSE	eval:check_rbl_sub('rfci_envfrom', '127.0.0.4')";
	$conf[]="describe DNS_FROM_RFC_ABUSE	Envelope sender in abuse.rfc-ignorant.org";
	$conf[]="tflags DNS_FROM_RFC_ABUSE	net";
	$conf[]="";
	$conf[]="header DNS_FROM_RFC_WHOIS	eval:check_rbl_sub('rfci_envfrom', '127.0.0.5')";
	$conf[]="describe DNS_FROM_RFC_WHOIS	Envelope sender in whois.rfc-ignorant.org";
	$conf[]="tflags DNS_FROM_RFC_WHOIS	net";
	$conf[]="";
	$conf[]="# this is 127.0.0.6 if querying fullip.rfc-ignorant.org, but since there";
	$conf[]="# is only one right now, we might as well get the TXT record version";
	$conf[]="# 2004-10-21: disabled since ipwhois is going away";
	$conf[]="#header RCVD_IN_RFC_IPWHOIS	eval:check_rbl_txt('ipwhois-notfirsthop', 'ipwhois.rfc-ignorant.org.')";
	$conf[]="#describe RCVD_IN_RFC_IPWHOIS	Sent via a relay in ipwhois.rfc-ignorant.org";
	$conf[]="#tflags RCVD_IN_RFC_IPWHOIS	net";
	$conf[]="";
	$conf[]="# 127.0.0.7 is the response for an entire TLD in whois.rfc-ignorant.org,";
	$conf[]="# but it has too many false positives.";
	$conf[]="";
	$conf[]="header DNS_FROM_RFC_BOGUSMX	eval:check_rbl_sub('rfci_envfrom', '127.0.0.8')";
	$conf[]="describe DNS_FROM_RFC_BOGUSMX	Envelope sender in bogusmx.rfc-ignorant.org";
	$conf[]="tflags DNS_FROM_RFC_BOGUSMX	net";
}


if($datas["multihop.dsbl.org"]==1){
$conf[]="";
$conf[]="# ---------------------------------------------------------------------------";
$conf[]="# Now, single zone BLs follow:";
$conf[]="";
$conf[]="# DSBL catches open relays, badly-installed CGI scripts and open SOCKS and";
$conf[]="# HTTP proxies.  list.dsbl.org lists servers tested by \"trusted\" users,";
$conf[]="# multihop.dsbl.org lists servers which open SMTP servers relay through,";
$conf[]="# unconfirmed.dsbl.org lists servers tested by \"untrusted\" users.";
$conf[]="# See http://dsbl.org/ for full details.";
$conf[]="# transfers: yes - rsync and http, see http://dsbl.org/usage";
$conf[]="# pay-to-use: no";
$conf[]="# delist: automated/distributed";
$conf[]="header RCVD_IN_DSBL		eval:check_rbl_txt('dsbl-notfirsthop', 'list.dsbl.org.')";
$conf[]="describe RCVD_IN_DSBL		Received via a relay in list.dsbl.org";
$conf[]="tflags RCVD_IN_DSBL		net";
$conf[]="";
$conf[]="########################################################################";
}


if($datas["rhsbl.ahbl.org"]==1){
	$conf[]="";
	$conf[]="# another domain-based blacklist";
	$conf[]="header DNS_FROM_AHBL_RHSBL	eval:check_rbl_from_host('ahbl', 'rhsbl.ahbl.org.')";
	$conf[]="describe DNS_FROM_AHBL_RHSBL	From: sender listed in dnsbl.ahbl.org";
	$conf[]="tflags DNS_FROM_AHBL_RHSBL	net";
	$conf[]="";
}

if($datas["sa-hil.habeas.com"]==1){
	$conf[]="# sa-hil.habeas.com for SpamAssassin queries";
	$conf[]="# hil.habeas.com for other queries";
	$conf[]="header HABEAS_INFRINGER		eval:check_rbl_swe('hil', 'sa-hil.habeas.com.')";
	$conf[]="describe HABEAS_INFRINGER	Has Habeas warrant mark and on Infringer List";
	$conf[]="tflags HABEAS_INFRINGER		net";
	$conf[]="";
}

if($datas["sa-hul.habeas.com"]==1){
	$conf[]="# sa-hul.habeas.com for SpamAssassin queries";
	$conf[]="# hul.habeas.com for other queries";
	$conf[]="header HABEAS_USER		eval:check_rbl_swe('hul', 'sa-hul.habeas.com.')";
	$conf[]="describe HABEAS_USER		Has Habeas warrant mark and on User List";
	$conf[]="tflags HABEAS_USER		net nice";
	$conf[]="";
	$conf[]="header RCVD_IN_BSP_TRUSTED	eval:check_rbl_txt('bsp-firsttrusted', 'sa-trusted.bondedsender.org.')";
	$conf[]="describe RCVD_IN_BSP_TRUSTED	Sender is in Bonded Sender Program (trusted relay)";
	$conf[]="tflags RCVD_IN_BSP_TRUSTED	net nice";
	$conf[]="";
	$conf[]="header RCVD_IN_BSP_OTHER	eval:check_rbl_txt('bsp-untrusted', 'sa-other.bondedsender.org.')";
	$conf[]="describe RCVD_IN_BSP_OTHER	Sender is in Bonded Sender Program (other relay)";
	$conf[]="tflags RCVD_IN_BSP_OTHER	net nice";
	$conf[]="";
	$conf[]="# SenderBase information <http://www.senderbase.org/dnsresponses.html>";
	$conf[]="# these are experimental example rules";
	$conf[]="";
}

if($datas["senderbase.org"]==1){
	$conf[]="# sa.senderbase.org for SpamAssassin queries";
	$conf[]="# query.senderbase.org for other queries";
	$conf[]="header __SENDERBASE eval:check_rbl_txt('sb', 'sa.senderbase.org.')";
	$conf[]="tflags __SENDERBASE net";
	$conf[]="";
	$conf[]="# S23 = domain daily magnitude, S25 = date of first message from this domain";
	$conf[]="header SB_NEW_BULK		eval:check_rbl_sub('sb', 'sb:S23 > 6.2 && (time - S25 < 120*86400)')";
	$conf[]="describe SB_NEW_BULK		Sender domain is new and very high volume";
	$conf[]="tflags SB_NEW_BULK		net";
	$conf[]="";
	$conf[]="# S5 = category, S40 = IP daily magnitude, S41 = IP monthly magnitude";
	$conf[]="# note: accounting for rounding, \"> 0.3\" means at least a 59% volume spike";
	$conf[]="header SB_NSP_VOLUME_SPIKE	eval:check_rbl_sub('sb', 'sb:S5 =~ /NSP/ && S41 > 3.8 && S40 - S41 > 0.3')";
	$conf[]="describe SB_NSP_VOLUME_SPIKE	Sender IP hosted at NSP has a volume spike";
	$conf[]="tflags SB_NSP_VOLUME_SPIKE	net";
	$conf[]="";
}

if($datas["spamcop"]==1){
	$conf[]="# ---------------------------------------------------------------------------";
	$conf[]="# NOTE: donation tests, see README file for details";
	$conf[]="";
	$conf[]="header RCVD_IN_BL_SPAMCOP_NET	eval:check_rbl_txt('spamcop', 'bl.spamcop.net.')";
	$conf[]="describe RCVD_IN_BL_SPAMCOP_NET	Received via a relay in bl.spamcop.net";
	$conf[]="tflags RCVD_IN_BL_SPAMCOP_NET	net";
	$conf[]="";
}

if($datas["relays.visi.com"]==1){
	$conf[]="header RCVD_IN_RSL		eval:check_rbl_txt('rsl', 'relays.visi.com.')";
	$conf[]="describe RCVD_IN_RSL		Received via a relay in RSL";
	$conf[]="tflags RCVD_IN_RSL		net";
	$conf[]="";
	$conf[]="# ---------------------------------------------------------------------------";
}

$conf[]="# ---------------------------------------------------------------------------";
$conf[]="# Other DNS tests";
$conf[]="";
$conf[]="header NO_DNS_FOR_FROM		eval:check_dns_sender()";
$conf[]="describe NO_DNS_FOR_FROM	Envelope sender has no MX or A DNS records";
$conf[]="tflags NO_DNS_FOR_FROM		net";
$conf[]="";	

@file_put_contents("/etc/spamassassin/dnsbl.pre",@implode("\n",$conf));
	
}


?>