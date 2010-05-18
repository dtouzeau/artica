<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.system.network.inc");
include_once(dirname(__FILE__)."/ressources/class.os.system.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');

$_GET["APT-GET"]="/usr/bin/apt-get";
if(!is_file($_GET["APT-GET"])){die();}

if(systemMaxOverloaded()){
	writelogs("This system is too many overloaded, die()",__FUNCTION__,__FILE__,__LINE__);
	die();
}

include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

if($argv[1]=='--update'){GetUpdates();die();}
if($argv[1]=='--upgrade'){UPGRADE();die();}


function GetUpdates(){
if(COUNT_REPOS()==0){INSERT_DEB_PACKAGES();}	
$unix=new unix();
$tmpf=$unix->FILE_TEMP();

$sock=new sockets();	
$ini=new Bs_IniHandler();
$configDisk=trim($sock->GET_INFO('ArticaAutoUpdateConfig'));	
$ini->loadString($configDisk);	
$AUTOUPDATE=$ini->_params["AUTOUPDATE"];
if(trim($AUTOUPDATE["auto_apt"])==null){$AUTOUPDATE["auto_apt"]="no";}

shell_exec("{$_GET["APT-GET"]} update >/dev/null 2>&1");
shell_exec("{$_GET["APT-GET"]} -f install --force-yes >/dev/null 2>&1");
shell_exec("{$_GET["APT-GET"]} upgrade -s >$tmpf 2>&1");
	
$datas=@file_get_contents($tmpf);
$tbl=explode("\n",$datas);
writelogs("Found ". strlen($datas)." bytes for apt",__FUNCTION__,__FILE__,__LINE__);
@unlink($tmpf);

	while (list ($num, $val) = each ($tbl) ){
		if($val==null){continue;}
		if(preg_match("#^Inst\s+(.+?)\s+#",$val,$re)){
			$packages[]=$re[1];
			writelogs("Found {$re[1]} new package",__FUNCTION__,__FILE__,__LINE__);
			
		}else{
			
			writelogs("Garbage \"$val\"",__FUNCTION__,__FILE__,__LINE__);
		}
		
	}

	$count=count($packages);
	if($count>0){
		@file_put_contents("/etc/artica-postfix/apt.upgrade.cache",implode("\n",$packages));
		$text="You can perform upgrade of linux packages for\n".@file_get_contents("/etc/artica-postfix/apt.upgrade.cache");
		send_email_events("new upgrade $count packages(s) ready",$text,"system");
		
		THREAD_COMMAND_SET(LOCATE_PHP5_BIN()." /usr/share/artica-postfix/exec.admin.status.postfix.flow.php --services");
		
		if($AUTOUPDATE["auto_apt"]=="yes"){
			UPGRADE();
		}
	}else{
		writelogs("No new packages...",__FUNCTION__,__FILE__,__LINE__);
		@unlink("/etc/artica-postfix/apt.upgrade.cache");
	}



}

function COUNT_REPOS(){
	$sql="SELECT COUNT(package_name) FROM debian_packages";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	return($ligne["tcount"]);
}



function INSERT_DEB_PACKAGES(){
	if(!is_file("/usr/bin/dpkg")){die();}
	$sql="TRUNCATE TABLE `debian_packages`";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	$unix=new unix();
	$tmpf=$unix->FILE_TEMP();	
	shell_exec("/usr/bin/dpkg -l >$tmpf 2>&1");
	$datas=@file_get_contents($tmpf);
	@unlink($tmpf);
	$tbl=explode("\n",$datas);
	while (list ($num, $val) = each ($tbl) ){
		if($val==null){continue;}
			
	if(preg_match("#^([a-z]+)\s+(.+?)\s+(.+?)\s+(.+)#",$val,$re)){
			$content=addslashes($re[4]);
			$pname=$re[2];
			$package_description=addslashes(PACKAGE_EXTRA_INFO($pname));
			
		$sql="INSERT INTO debian_packages(package_status,package_name,package_version,package_info,package_description) 
  		VALUES('{$re[1]}','$pname','{$re[3]}','$content','$package_description');";
  		$q->QUERY_SQL($sql,"artica_backup");  			
			
		}	
	}
}

function PACKAGE_EXTRA_INFO($pname){
	$unix=new unix();
	$tmpf=$unix->FILE_TEMP();		
	shell_exec("/usr/bin/dpkg-query -p $pname >$tmpf 2>&1");
	$datas=@file_get_contents($tmpf);
	@unlink($tmpf);
}

function UPGRADE(){
	$unix=new unix();
	$tmpf=$unix->FILE_TEMP();		
$txt="PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/X11R6/bin\n";
$txt=$txt."echo \$PATH >$tmpf 2>&1\n";
$txt=$txt."rm -f $tmpf\n";

$tmpf=$unix->FILE_TEMP();	
@file_put_contents($tmpf,$txt);
@chmod($tmpf,'0777');
shell_exec($tmpf);

$tmpf=$unix->FILE_TEMP();
$cmd="DEBIAN_FRONTEND=noninteractive {$_GET["APT-GET"]} -o Dpkg::Options::=\"--force-confnew\" --force-yes update >$tmpf 2>&1";
writelogs($cmd,__FUNCTION__,__FILE__,__LINE__);
shell_exec($cmd);


$cmd="DEBIAN_FRONTEND=noninteractive {$_GET["APT-GET"]} -o Dpkg::Options::=\"--force-confnew\" --force-yes --yes install -f >$tmpf 2>&1";
writelogs($cmd,__FUNCTION__,__FILE__,__LINE__);
shell_exec($cmd);


$cmd="DEBIAN_FRONTEND=noninteractive {$_GET["APT-GET"]} -o Dpkg::Options::=\"--force-confnew\" --force-yes --yes upgrade >>$tmpf 2>&1";
writelogs($cmd,__FUNCTION__,__FILE__,__LINE__);
shell_exec($cmd);

$datas=@file_get_contents($tmpf);
$datassql=addslashes($datas);


$q=new mysql();
$sql="INSERT INTO debian_packages_logs(zDate,package_name,events,install_type) VALUES(NOW(),'artica-upgrade','$datassql','upgrade');";
$q->QUERY_SQL($sql,"artica_backup");  	
@unlink('/etc/artica-postfix/apt.upgrade.cache');

send_email_events("Debian/Ubuntu System upgrade operation",$datas,"system");
INSERT_DEB_PACKAGES();
THREAD_COMMAND_SET(LOCATE_PHP5_BIN()." /usr/share/artica-postfix/exec.admin.status.postfix.flow.php --services"); 	
}





?>