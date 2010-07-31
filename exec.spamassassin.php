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
	
	x_headers();
	x_bounce();
	
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
$r[]="loadplugin Mail::SpamAssassin::Plugin::DKIM";	
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


function x_headers(){
	//X-Wum-Spamlevel
$conf[]="header INFOMANIAK_SPAM X-Infomaniak-Spam =~ /spam/";
$conf[]="score INFOMANIAK_SPAM       1";
$conf[]="header SPAMASS_SPAM X-Spam-Status =~ /Yes/";
$conf[]="score SPAMASS_SPAM       1";
$conf[]="header XASF_SPAM X-ASF-Spam-Status =~ /Yes/";
$conf[]="score XASF_SPAM       1";	
$conf[]="header XTMAS_SPAM X-TM-AS-Result =~ /Yes/";
$conf[]="score XTMAS_SPAM       1";		
@file_put_contents("/etc/spamassassin/x-headers.pre",@implode("\n",$conf));
	
}


function x_bounce(){
	$sock=new sockets();
	if($sock->GET_INFO("SpamAssassinVirusBounceEnabled")<>1){
		echo "Starting......: spamassassin Virus Bounce Ruleset is disabled\n";
		@file_put_contents('/etc/spamassassin/20_vbounce.cf',"#");
		return;
	}
	
echo "Starting......: spamassassin Virus Bounce Ruleset is enabled\n";
x_bound_pm();	
$f[]="# very frequent, using unrelated From lines; either spam or C/R, not yet";
$f[]="# sure which";
$f[]="header __CRBOUNCE_GETRESP Return-Path =~ /<bounce\S+\@\S+\.getresponse\.com>/";
$f[]="";
$f[]="header __CRBOUNCE_TMDA  Message-Id =~ /\@\S+\-tmda\-confirm>$/";
$f[]="header __CRBOUNCE_ASK   X-AskVersion =~ /\d/";
$f[]="header __CRBOUNCE_SZ    X-Spamazoid-MD =~ /\d/";
$f[]="header __CRBOUNCE_SPAMLION Spamlion =~ /\S/";
$f[]="";
$f[]="# something called /cgi-bin/notaspammer does this!";
$f[]="header __CRBOUNCE_PREC_SPAM  Precedence =~ /spam/";
$f[]="";
$f[]="header __AUTO_GEN_XBT   exists:X-Boxtrapper";
$f[]="header __AUTO_GEN_BBTL  exists:X-Bluebottle-Request";
$f[]="meta __CRBOUNCE_HEADER    (__AUTO_GEN_XBT || __AUTO_GEN_BBTL)";
$f[]="";
$f[]="header __CRBOUNCE_EXI   X-ExiSpam =~ /ExiSpam/";
$f[]="";
$f[]="header __CRBOUNCE_UNVERIF   Subject =~ /^Unverified email to /";
$f[]="";
$f[]="meta CRBOUNCE_MESSAGE       !MY_SERVERS_FOUND && (__CRBOUNCE_UOL || __CRBOUNCE_VERIF || __CRBOUNCE_RP || __CRBOUNCE_VANQ || __CRBOUNCE_HEADER || __CRBOUNCE_QURB || __CRBOUNCE_0SPAM || __CRBOUNCE_GETRESP || __CRBOUNCE_TMDA || __CRBOUNCE_ASK || __CRBOUNCE_EXI || __CRBOUNCE_PREC_SPAM || __CRBOUNCE_SZ || __CRBOUNCE_SPAMLION || __CRBOUNCE_MIB || __CRBOUNCE_SI || __CRBOUNCE_UNVERIF || __CRBOUNCE_RP_2)";
$f[]="";
$f[]="describe CRBOUNCE_MESSAGE   Challenge-response bounce message";
$f[]="score    CRBOUNCE_MESSAGE   0.1";
$f[]="";
$f[]="# ---------------------------------------------------------------------------";
$f[]="# \"Virus found in your mail\" bounces";
$f[]="";
$f[]="# source: VirusBounceRules from the exit0 SA wiki";
$f[]="";
$f[]="body __VBOUNCE_EXIM      /a potentially executable attachment /";
$f[]="body __VBOUNCE_GUIN      /message contains file attachments that are not permitted/";
$f[]="body __VBOUNCE_CISCO     /^Found virus \S+ in file \S+/m";
$f[]="body __VBOUNCE_SMTP      /host \S+ said: 5\d\d\s+Error: Message content rejected/";
$f[]="body __VBOUNCE_AOL       /TRANSACTION FAILED - Unrepairable Virus Detected. /";
$f[]="body __VBOUNCE_DUTCH     /bevatte bijlage besmet welke besmet was met een virus/";
$f[]="body __VBOUNCE_MAILMARSHAL       /Mail.?Marshal Rule: Inbound Messages : Block Dangerous Attachments/";
$f[]="header __VBOUNCE_MAILMARSHAL2    Subject =~ /^MailMarshal has detected possible spam in your message/";
$f[]="header __VBOUNCE_NAVFAIL   Subject =~ /^Norton Anti.?Virus failed to scan an attachment in a message you sent/";
$f[]="header __VBOUNCE_REJECTED   Subject =~ /^EMAIL REJECTED$/";
$f[]="header __VBOUNCE_NAV   Subject =~ /^Norton Anti.?Virus detected and quarantined/";
$f[]="header __VBOUNCE_MELDING   Subject =~ /^Virusmelding$/";
$f[]="body __VBOUNCE_VALERT      /The mail message \S+ \S+ you sent to \S+ contains the virus/";
$f[]="body __VBOUNCE_REJ_FILT    /Reason: Rejected by filter/";
$f[]="header __VBOUNCE_YOUSENT   Subject =~ /^Warning - You sent a Virus Infected Email to /";
$f[]="body __VBOUNCE_MAILSWEEP   /MAILsweeper has found that a \S+ \S+ \S+ \S+ one or more virus/";
$f[]="header   __VBOUNCE_SCREENSAVER Subject =~ /(Re: ?)+Wicked screensaver\b/i";
$f[]="header   __VBOUNCE_DISALLOWED Subject =~ /^Disallowed attachment type found/";
$f[]="header   __VBOUNCE_FROMPT From =~ /Security.?Scan Anti.?Virus/";
$f[]="header   __VBOUNCE_WARNING Subject =~ /^Warning:\s*E-?mail virus(es)? detected/i";
$f[]="header   __VBOUNCE_DETECTED Subject =~ /^Virus detected /i";
$f[]="header   __VBOUNCE_AUTOMATIC Subject =~ /\b(automatic reply|AutoReply)\b/";
$f[]="header   __VBOUNCE_INTERSCAN Subject =~ /^Failed to clean virus\b/i";
$f[]="header   __VBOUNCE_VIOLATION Subject =~ /^Content violation/i";
$f[]="header   __VBOUNCE_ALERT Subject =~ /^Virus Alert\b/i";
$f[]="header   __VBOUNCE_NAV2 Subject =~ /^NAV detected a virus in a document /";
$f[]="body      __VBOUNCE_NAV3 /^Reporting-MTA: Norton Anti.?Virus Gateway/";
$f[]="header   __VBOUNCE_INTERSCAN2 Subject =~ /^InterScan MSS for SMTP has delivered a message/";
$f[]="header   __VBOUNCE_INTERSCAN3 Subject =~ /^InterScan NT Alert/";
$f[]="header   __VBOUNCE_ANTIGEN Subject =~ /^Antigen found\b/i";
$f[]="header   __VBOUNCE_LUTHER From =~ /\blutherh\@stratcom.com\b/";
$f[]="header   __VBOUNCE_AMAVISD Subject =~ /^VIRUS IN YOUR MAIL /i";
$f[]="body     __VBOUNCE_AMAVISD2 /\bV I R U S\b/";
$f[]="header __VBOUNCE_GSHIELD Subject =~ /^McAfee GroupShield Alert/";
$f[]="";
$f[]="# off: got an FP in a simple forward";
$f[]="# rawbody  __VBOUNCE_SUBJ_IN_MAIL /^\s*Subject:\s*(Re: )*((my|your) )?(application|details)/i";
$f[]="# rawbody  __VBOUNCE_SUBJ_IN_MAIL2 /^\s*Subject:\s*(Re: )*(Thank you!?|That movie|Wicked screensaver|Approved)/i";
$f[]="";
$f[]="header __VBOUNCE_SCANMAIL Subject =~ /^Scan.?Mail Message: .{0,30} virus found /i";
$f[]="header __VBOUNCE_DOMINO1 Subject =~ /^Report to Sender/";
$f[]="body __VBOUNCE_DOMINO2 /^Incident Information:/";
$f[]="header __VBOUNCE_RAV Subject =~ /^RAV Anti.?Virus scan results/";
$f[]="";
$f[]="body __VBOUNCE_ATTACHMENT0     /(?:Attachment.{0,40}was Deleted|Virus.{1,40}was found|the infected attachment)/i";
$f[]="# Bart says: it appears that _ATTACHMENT0 is an alternate for _NAV -- both match the same messages.";
$f[]="";
$f[]="body __VBOUNCE_AVREPORT0       /(antivirus system report|the antivirus module has|illegal attachment|Unrepairable Virus Detected)/i";
$f[]="header __VBOUNCE_SENDER       Subject =~ /^Virus to sender/";
$f[]="body __VBOUNCE_MAILSWEEP2     /\bblocked by Mailsweeper\b/i";
$f[]="";
$f[]="header __VBOUNCE_MAILSWEEP3   From =~ /\bmailsweeper\b/i";
$f[]="# Bart says: This one could replace both MAILSWEEP2 and MAILSWEEP as far as I can tell.";
$f[]="#            Perhaps it's too general?";
$f[]="";
$f[]="body __VBOUNCE_CLICKBANK      /\bvirus scanner deleted your message\b/i";
$f[]="header __VBOUNCE_FORBIDDEN    Subject =~ /\bFile type Forbidden\b/";
$f[]="header   __VBOUNCE_MMS        Subject =~ /^MMS Notification/";
$f[]="# added by JoeyKelly";
$f[]="";
$f[]="header __VBOUNCE_JMAIL Subject =~ /^Message Undeliverable: Possible Junk\/Spam Mail Identified$/";
$f[]="";
$f[]="body __VBOUNCE_QUOTED_EXE     /> TVqQAAMAAAAEAAAA/";
$f[]="";
$f[]="# majordomo is really stupid about this stuff";
$f[]="header __MAJORDOMO_SUBJ     Subject =~ /^Majordomo results: /";
$f[]="rawbody __MAJORDOMO_HELP_BODY  /\*\*\*\* Help for [mM]ajordomo\@/";
$f[]="rawbody __MAJORDOMO_HELP_BODY2 /\*\*\*\* Command \'.{0,80}\' not recognized\b/";
$f[]="meta __VBOUNCE_MAJORDOMO_HELP (__MAJORDOMO_SUBJ && __MAJORDOMO_HELP_BODY && __MAJORDOMO_HELP_BODY2)";
$f[]="";
$f[]="header __VBOUNCE_AV_RESULTS   Subject =~ /AntiVirus scan results/";
$f[]="header __VBOUNCE_EMVD         Subject =~ /^Warning: E-mail viruses detected/";
$f[]="header __VBOUNCE_UNDELIV      Subject =~ /^Undeliverable mail, invalid characters in header/";
$f[]="header __VBOUNCE_BANNED_MAT   Subject =~ /^Banned or potentially offensive material/";
$f[]="header __VBOUNCE_NAV_DETECT   Subject =~ /^Norton AntiVirus detected and quarantined/";
$f[]="header __VBOUNCE_DEL_WARN     Subject =~ /^Delivery warning report id=/";
$f[]="header __VBOUNCE_MIME_INFO    Subject =~ /^The MIME information you requested/";
$f[]="header __VBOUNCE_EMAIL_REJ    Subject =~ /^EMAIL REJECTED/";
$f[]="header __VBOUNCE_CONT_VIOL    Subject =~ /^Content violation/";
$f[]="header __VBOUNCE_SYM_AVF      Subject =~ /^Symantec AVF detected /";
$f[]="header __VBOUNCE_SYM_EMP      Subject =~ /^Symantec E-Mail-Proxy /";
$f[]="header __VBOUNCE_VIR_FOUND    Subject =~ /^Virus Found in message/";
$f[]="header __VBOUNCE_INFLEX       Subject =~ /^Inflex scan report \[/";
$f[]="";
$f[]="header __VBOUNCE_RAPPORT      Subject =~ /^Spam rapport \/ Spam report \S+ -\s+\(\S+\)$/";
$f[]="header __VBOUNCE_GWAVA        Subject =~ /^GWAVA Sender Notification .RBL block.$/";
$f[]="";
$f[]="header __VBOUNCE_EMANAGER     Subject =~ /^\[MailServer Notification\]/";
$f[]="header __VBOUNCE_MSGLABS      Return-Path =~ /alert\@notification\.messagelabs\.com/i";
$f[]="body __VBOUNCE_ATT_QUAR       /\bThe attachment was quarantined\b/";
$f[]="body __VBOUNCE_SECURIQ        /\bGROUP securiQ.Wall\b/";
$f[]="";
$f[]="header __VBOUNCE_PT_BLOCKED   Subject =~ /^\*\*\*\s*Mensagem Bloqueada/i";
$f[]="";
$f[]="meta VBOUNCE_MESSAGE        !MY_SERVERS_FOUND && (__VBOUNCE_MSGLABS || __VBOUNCE_EXIM || __VBOUNCE_GUIN || __VBOUNCE_CISCO || __VBOUNCE_SMTP || __VBOUNCE_AOL || __VBOUNCE_DUTCH || __VBOUNCE_MAILMARSHAL || __VBOUNCE_MAILMARSHAL2 || __VBOUNCE_NAVFAIL || __VBOUNCE_REJECTED || __VBOUNCE_NAV || __VBOUNCE_MELDING || __VBOUNCE_VALERT || __VBOUNCE_REJ_FILT || __VBOUNCE_YOUSENT || __VBOUNCE_MAILSWEEP || __VBOUNCE_SCREENSAVER || __VBOUNCE_DISALLOWED || __VBOUNCE_FROMPT || __VBOUNCE_WARNING || __VBOUNCE_DETECTED || __VBOUNCE_AUTOMATIC || __VBOUNCE_INTERSCAN || __VBOUNCE_VIOLATION || __VBOUNCE_ALERT || __VBOUNCE_NAV2 || __VBOUNCE_NAV3 || __VBOUNCE_INTERSCAN2 || __VBOUNCE_INTERSCAN3 || __VBOUNCE_ANTIGEN || __VBOUNCE_LUTHER || __VBOUNCE_AMAVISD || __VBOUNCE_AMAVISD2 || __VBOUNCE_SCANMAIL || __VBOUNCE_DOMINO1 || __VBOUNCE_DOMINO2 || __VBOUNCE_RAV || __VBOUNCE_GSHIELD || __VBOUNCE_ATTACHMENT0 || __VBOUNCE_AVREPORT0 || __VBOUNCE_SENDER || __VBOUNCE_MAILSWEEP2 || __VBOUNCE_MAILSWEEP3 || __VBOUNCE_CLICKBANK || __VBOUNCE_FORBIDDEN || __VBOUNCE_MMS || __VBOUNCE_QUOTED_EXE || __VBOUNCE_MAJORDOMO_HELP || __VBOUNCE_AV_RESULTS || __VBOUNCE_EMVD || __VBOUNCE_UNDELIV || __VBOUNCE_BANNED_MAT || __VBOUNCE_NAV_DETECT || __VBOUNCE_DEL_WARN || __VBOUNCE_MIME_INFO || __VBOUNCE_EMAIL_REJ || __VBOUNCE_CONT_VIOL || __VBOUNCE_SYM_AVF || __VBOUNCE_SYM_EMP || __VBOUNCE_ATT_QUAR || __VBOUNCE_SECURIQ || __VBOUNCE_VIR_FOUND || __VBOUNCE_EMANAGER || __VBOUNCE_JMAIL || __VBOUNCE_GWAVA || __VBOUNCE_PT_BLOCKED || __VBOUNCE_INFLEX)";
$f[]="";
$f[]="describe VBOUNCE_MESSAGE    Virus-scanner bounce message";
$f[]="score    VBOUNCE_MESSAGE    0.1";
$f[]="";
$f[]="# ---------------------------------------------------------------------------";
$f[]="";
$f[]="# a catch-all type for all the above";
$f[]="";
$f[]="meta     ANY_BOUNCE_MESSAGE (CRBOUNCE_MESSAGE||BOUNCE_MESSAGE||VBOUNCE_MESSAGE)";
$f[]="describe ANY_BOUNCE_MESSAGE Message is some kind of bounce message";
$f[]="score    ANY_BOUNCE_MESSAGE 0.1";
$f[]="";
$f[]="# ---------------------------------------------------------------------------";
$f[]="";
$f[]="# ensure these aren't published in rule-updates as general antispam rules;";
$f[]="# this is required, since it appears we're now at the stage where they";
$f[]="# *do* appear to correlate strongly :(";
$f[]="# http://ruleqa.spamassassin.org/20060405-r391250-n/BOUNCE_MESSAGE";
$f[]="#";
$f[]="tflags   CRBOUNCE_MESSAGE   nopublish";
$f[]="tflags   BOUNCE_MESSAGE     nopublish";
$f[]="tflags   VBOUNCE_MESSAGE    nopublish";
$f[]="tflags   ANY_BOUNCE_MESSAGE nopublish";
$f[]="";

@file_put_contents('/etc/spamassassin/20_vbounce.cf',@implode("\n",$f));

}

function x_bound_pm(){
	if(file_exists("/etc/spamassassin/VBounce.pm")){return;}
$f[]="# <@LICENSE>";
$f[]="# Licensed to the Apache Software Foundation (ASF) under one or more";
$f[]="# contributor license agreements.  See the NOTICE file distributed with";
$f[]="# this work for additional information regarding copyright ownership.";
$f[]="# The ASF licenses this file to you under the Apache License, Version 2.0";
$f[]="# (the \"License\"); you may not use this file except in compliance with";
$f[]="# the License.  You may obtain a copy of the License at:";
$f[]="#";
$f[]="#     http://www.apache.org/licenses/LICENSE-2.0";
$f[]="#";
$f[]="# Unless required by applicable law or agreed to in writing, software";
$f[]="# distributed under the License is distributed on an \"AS IS\" BASIS,";
$f[]="# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.";
$f[]="# See the License for the specific language governing permissions and";
$f[]="# limitations under the License.";
$f[]="# </@LICENSE>";
$f[]="";
$f[]="=head1 NAME";
$f[]="";
$f[]="Mail::SpamAssassin::Plugin::VBounce";
$f[]="";
$f[]="=head1 SYNOPSIS";
$f[]="";
$f[]=" loadplugin Mail::SpamAssassin::Plugin::VBounce [/path/to/VBounce.pm]";
$f[]="";
$f[]="=cut";
$f[]="";
$f[]="package Mail::SpamAssassin::Plugin::VBounce;";
$f[]="";
$f[]="use Mail::SpamAssassin::Plugin;";
$f[]="use Mail::SpamAssassin::Logger;";
$f[]="use strict;";
$f[]="use warnings;";
$f[]="";
$f[]="our @ISA = qw(Mail::SpamAssassin::Plugin);";
$f[]="";
$f[]="sub new {";
$f[]="  my \$class = shift;";
$f[]="  my \$mailsaobject = shift;";
$f[]="";
$f[]="  \$class = ref(\$class) || \$class;";
$f[]="  my \$self = \$class->SUPER::new(\$mailsaobject);";
$f[]="  bless (\$self, \$class);";
$f[]="";
$f[]="  \$self->register_eval_rule(\"have_any_bounce_relays\");";
$f[]="  \$self->register_eval_rule(\"check_whitelist_bounce_relays\");";
$f[]="";
$f[]="  \$self->set_config(\$mailsaobject->{conf});";
$f[]="";
$f[]="  return \$self;";
$f[]="}";
$f[]="";
$f[]="sub set_config {";
$f[]="  my(\$self, \$conf) = @_;";
$f[]="  my @cmds = ();";
$f[]="";
$f[]="=head1 USER PREFERENCES";
$f[]="";
$f[]="The following options can be used in both site-wide (C<local.cf>) and";
$f[]="user-specific (C<user_prefs>) configuration files to customize how";
$f[]="SpamAssassin handles incoming email messages.";
$f[]="";
$f[]="=over 4";
$f[]="";
$f[]="=item whitelist_bounce_relays hostname [hostname2 ...]";
$f[]="";
$f[]="This is used to 'rescue' legitimate bounce messages that were generated in";
$f[]="response to mail you really *did* send.  List the MTA relays that your outbound";
$f[]="mail is delivered through.  If a bounce message is found, and it contains one";
$f[]="of these hostnames in a 'Received' header, it will not be marked as a blowback";
$f[]="virus-bounce.";
$f[]="";
$f[]="The hostnames can be file-glob-style patterns, so C<relay*.isp.com> will work.";
$f[]="Specifically, C<*> and C<?> are allowed, but all other metacharacters are not.";
$f[]="Regular expressions are not used for security reasons.";
$f[]="";
$f[]="Multiple addresses per line, separated by spaces, is OK.  Multiple";
$f[]="C<whitelist_from> lines is also OK.";
$f[]="";
$f[]="";
$f[]="=cut";
$f[]="";
$f[]="  push (@cmds, {";
$f[]="      setting => 'whitelist_bounce_relays',";
$f[]="      type => \$Mail::SpamAssassin::Conf::CONF_TYPE_ADDRLIST";
$f[]="    });";
$f[]="";
$f[]="  \$conf->{parser}->register_commands(\@cmds);";
$f[]="}";
$f[]="";
$f[]="sub have_any_bounce_relays {";
$f[]="  my (\$self, \$pms) = @_;";
$f[]="  return (defined \$pms->{conf}->{whitelist_bounce_relays} &&";
$f[]="      (scalar values %{\$pms->{conf}->{whitelist_bounce_relays}} != 0));";
$f[]="}";
$f[]="";
$f[]="sub check_whitelist_bounce_relays {";
$f[]="  my (\$self, \$pms) = @_;";
$f[]="";
$f[]="  my \$body = \$pms->get_decoded_stripped_body_text_array();";
$f[]="  my \$res;";
$f[]="";
$f[]="  # catch lines like:";
$f[]="  # Received: by dogma.boxhost.net (Postfix, from userid 1007)";
$f[]="";
$f[]="  # check the plain-text body, first";
$f[]="  foreach my \$line (@{\$body}) {";
$f[]="    next unless (\$line =~ /Received: /);";
$f[]="    while (\$line =~ / (\S+\.\S+) /g) {";
$f[]="      return 1 if \$self->_relay_is_in_whitelist_bounce_relays(\$pms, \$1);";
$f[]="    }";
$f[]="  }";
$f[]="";
$f[]="  # now check any \"message/anything\" attachment MIME parts, too";
$f[]="  # don't ignore non-leaf nodes, some bounces are odd that way";
$f[]="  foreach my \$p (\$pms->{msg}->find_parts(qr/^message\//, 0)) {";
$f[]="    my \$line = \$p->decode();";
$f[]="    next unless \$line && (\$line =~ /Received: /);";
$f[]="    while (\$line =~ / (\S+\.\S+) /g) {";
$f[]="      return 1 if \$self->_relay_is_in_whitelist_bounce_relays(\$pms, \$1);";
$f[]="    }";
$f[]="  }";
$f[]="";
$f[]="  return 0;";
$f[]="}";
$f[]="";
$f[]="sub _relay_is_in_whitelist_bounce_relays {";
$f[]="  my (\$self, \$pms, \$relay) = @_;";
$f[]="  return 1 if \$self->_relay_is_in_list(";
$f[]="        \$pms->{conf}->{whitelist_bounce_relays}, \$pms, \$relay);";
$f[]="  dbg(\"rules: relay \$relay doesn't match any whitelist\");";
$f[]="}";
$f[]="";
$f[]="sub _relay_is_in_list {";
$f[]="  my (\$self, \$list, \$pms, \$relay) = @_;";
$f[]="  \$relay = lc \$relay;";
$f[]="";
$f[]="  if (defined \$list->{\$relay}) { return 1; }";
$f[]="";
$f[]="  foreach my \$regexp (values %{\$list}) {";
$f[]="    if (\$relay =~ qr/\$regexp/i) {";
$f[]="      dbg(\"rules: relay \$relay matches regexp: \$regexp\");";
$f[]="      return 1;";
$f[]="    }";
$f[]="  }";
$f[]="";
$f[]="  return 0;";
$f[]="}";
$f[]="";
$f[]="1;";
$f[]="__DATA__";
$f[]="";
$f[]="=back";
$f[]="";
$f[]="=cut";
$f[]="";
@file_put_contents('/etc/spamassassin/VBounce.pm',@implode("\n",$f));


}



?>