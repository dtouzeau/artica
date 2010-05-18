<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

	include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
	include_once(dirname(__FILE__) . '/ressources/class.spamassassin.inc');
	
	$user=new usersMenus();
	if(!$user->spamassassin_installed){
		write_syslog("want to change spamassassin settings but not installed",__FILE__);
		die();
	}
	
	if(!is_file($user->spamassassin_conf_path)){
		write_syslog("want to change spamassassin settings but could not stat main configuration file",__FILE__);
	}
	
	
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

?>