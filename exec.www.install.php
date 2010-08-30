<?php
	include_once(dirname(__FILE__).'/ressources/class.templates.inc');
	include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
	include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
	include_once(dirname(__FILE__).'/ressources/class.apache.inc');
	include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
	include_once(dirname(__FILE__).'/ressources/class.pdns.inc');
	include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
	include_once(dirname(__FILE__).'/framework/class.unix.inc');
	include_once(dirname(__FILE__).'/framework/frame.class.inc');
	include_once(dirname(__FILE__).'/ressources/class.joomla.php');
	include_once(dirname(__FILE__).'/ressources/class.opengoo.inc');

	
	

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

if(is_array($argv)){
	while (list ($i, $cmds) = each ($argv) ){
		echo "$cmds\n";
		if(preg_match("#--verbose#",$cmds)){$_GET["debug"]=true;}
		if(preg_match("#--only-([A-Z0-9]+)#",$cmds,$re)){
			echo "Starting......: {$re[1]} sub-domains\n";
			$GLOBALS["ONLY"]=$re[1];
		}
	}
}

if($argv[1]=="getou"){$f=new opengoo();echo $f->get_Organization($argv[2])."\n";die();}

if(preg_match("#--vhosts#",implode(" ",$argv))){
	vhosts();
	die();
}
if(preg_match("#--mailman#",implode(" ",$argv))){
	$GLOBALS["OUTPUT"]=true;
	mailmanhosts();
	die();
}

if(preg_match("#--Wvhosts#",implode(" ",$argv))){
	@mkdir("/usr/local/apache-groupware/conf");
	@file_put_contents("/usr/local/apache-groupware/conf/vhosts",vhosts(true));
	die();
}


if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}


if($argv[1]=="remove"){remove($argv[2]);die;}


$ldap=new clladp();
$pattern="(&(objectclass=apacheConfig)(apacheServerName=*))";
$attr=array();
$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
//print_r($hash);

for($i=0;$i<$hash["count"];$i++){
	
	$root=$hash[$i]["apachedocumentroot"][0];
	$wwwservertype=trim($hash[$i]["wwwservertype"][0]);
	$apacheservername=trim($hash[$i]["apacheservername"][0]);
	
		$dn=$hash[$i]["dn"];
		if(preg_match("#ou=www,ou=(.+?),dc=organizations#",$dn,$re) ){$hash[$i]["OU"][0]=trim($re[1]);$ouexec=trim($re[1]);}
	
	if($GLOBALS["ONLY"]<>null){if($wwwservertype<>$GLOBALS["ONLY"]){continue;}}
	echo "Starting......: Apache groupware checking $apacheservername host ($wwwservertype)\n";
	
	if($wwwservertype=="LMB"){
		LMB_INSTALL($apacheservername,$root,$hash[$i]);
	}
	if($wwwservertype=="JOOMLA"){
		JOOMLA_INSTALL($apacheservername,$root,$hash[$i]);
	}

	if($wwwservertype=="ROUNDCUBE"){
		ROUNDCUBE_INSTALL($apacheservername,$root,$hash[$i]);
	}	
	
	if($wwwservertype=="SUGAR"){
		SUGAR_INSTALL($apacheservername,$root,$hash[$i]);
	}

	if($wwwservertype=="ARTICA_USR"){
		ARTICA_INSTALL($apacheservername,$root,$hash[$i]);
	}	
	
if($wwwservertype=="OBM2"){
		OBM2_INSTALL($apacheservername,$root,$hash[$i]);
	}

if($wwwservertype=="OPENGOO"){
		OPENGOO_INSTALL($apacheservername,$root,$hash[$i]);
	}	

if($wwwservertype=="GROUPOFFICE"){
		GROUPOFFICE_INSTALL($apacheservername,$root,$hash[$i]);
	}

if($wwwservertype=="ZARAFA"){
		ZARAFA_INSTALL($apacheservername,$root,$hash[$i]);
	}	

if($wwwservertype=="ZARAFA_MOBILE"){
		ZARAFA_MOBILE_INSTALL($apacheservername,$root,$hash[$i]);
	}


if($wwwservertype=="DRUPAL"){
		DRUPAL_INSTALL($apacheservername,$root,$hash[$i]);
	}

if($wwwservertype=="WEBDAV"){
		WEBDAV_USERS($apacheservername,$root,$hash[$i]);
	}	
	
	
}

if($hash["count"]>0){
	echo "restart apache\n";
	writelogs("restart apache...",basename(__FILE__),__FILE__,__LINE__);
	system('/etc/init.d/artica-postfix restart apache-groupware');
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?roundcube-sync=yes&ou=$ouexec");
}



function remove($servername){
	$apache=new vhosts();
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";
	$confs=$apache->SearchHosts($servername);
	events(__FUNCTION__.":: Check $servername");
	events(__FUNCTION__.":: remove files and directories");
	if(is_dir("/usr/share/artica-groupware/domains/$servername")){
		shell_exec("/bin/rm -rf /usr/share/artica-groupware/domains/$servername");
	}	
	$server_database=str_replace(" ","_",$servername);
	$server_database=str_replace(".","_",$servername);
	$server_database=str_replace("-","_",$server_database);
	
	$q=new mysql();
	if($q->DATABASE_EXISTS($server_database)){
		$q->DELETE_DATABASE($server_database);

	}
	
	$sql="DELETE FROM `mysql`.`db` WHERE `db`.`Db` = '$server_database'";
	$q->QUERY_SQL($sql,"mysql");
	
	$sql="CREATE USER '$user'@'%' IDENTIFIED BY '$mysql_password';";
	$q->QUERY_SQL($sql,"mysql");

	events(__FUNCTION__.":: removing ldap branch {$confs["dn"]}");
	
	$ldap=new clladp();
	if($ldap->ExistsDN($confs["dn"])){
		$ldap->ldap_delete($confs["dn"]);
	}
	events(__FUNCTION__.":: restarting HTTP service...");
	shell_exec("/etc/init.d/artica-postfix restart apache-groupware &");
	
}

function JOOMLA_INSTALL($servername,$root,$hash=array()){
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";	
	if($root==null){events("Starting install joomla Unable to stat root dir");return false;}
	if(!is_dir("/usr/local/share/artica/joomla_src")){
		events("Starting install joomla Unable to stat JOOMLA SRC");
		return false;
	}
	$sql_file="/usr/share/artica-postfix/bin/install/joomla/joomla.sql";

	$user=$hash["wwwmysqluser"][0];
	$mysql_password=$hash[strtolower("WWWMysqlPassword")][0];
	
	$appli_user=$hash["wwwappliuser"][0];
	$appli_password=$hash["wwwapplipassword"][0];
	
	
	if($user==null){events("Starting install Joomla Unable to stat Mysql username");return false;}
	if($mysql_password==null){events("Starting install Joomla Unable to stat Mysql password");return false;}

	@mkdir($root,0755,true);
	
	$server_database=str_replace(".","_",$servername);
	$server_database=str_replace("-","_",$server_database);	
	events("Starting install Joomla sub-system mysql database $server_database...");
	$q=new mysql();
	$q->CREATE_DATABASE($server_database);
	
	if(!$q->DATABASE_EXISTS($server_database)){
		events("Starting install Joomla unable to create MYSQL Database");
		return false;
	}
	
		
	events("Starting install Joomla installing source code");
	shell_exec("/bin/cp -rf /usr/local/share/artica/joomla_src/* $root/");
	if($q->mysql_password<>null){
		$password=" --password=$q->mysql_password ";
	}else{
		events("Starting install Joomla installing tables datas with null password");
	}
	
		$cmd="mysql --port=$q->mysql_port --skip-column-names --database=$server_database --silent --xml ";
		$cmd=$cmd." --user=$q->mysql_admin$password <$sql_file";
		shell_exec($cmd);

		
	AddPrivileges($user,$mysql_password,$server_database);
	$joomla=new joomla();
	$joomla->SaveAdminPasswordDatabase($server_database,$appli_password);
	shell_exec("/bin/cp -rf /usr/local/share/artica/joomla_src/* $root/");	
}


function SUGAR_INSTALL($servername,$root,$hash=array()){
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";	
	if($root==null){events("Starting install Sugar Unable to stat root dir");return false;}
	
	$sql_file="/usr/share/artica-postfix/bin/install/SugarCRM/sugarcrm.sql";

	$user=$hash["wwwmysqluser"][0];
	$mysql_password=$hash[strtolower("WWWMysqlPassword")][0];
	
	$appli_user=$hash["wwwappliuser"][0];
	$appli_password=$hash["wwwapplipassword"][0];
	$wwwsslmode=$hash["wwwsslmode"][0];
	if($wwwsslmode=="TRUE"){$SSL=true;}else{$SSL=false;}
	
	
	if($user==null){events("Starting install SugarCRM Unable to stat Mysql username");return false;}
	if($mysql_password==null){events("Starting install SugarCRM Unable to stat Mysql password");return false;}

	@mkdir($root,0755,true);
	
	$server_database=str_replace(".","_",$servername);
	$server_database=str_replace("-","_",$server_database);	
	
	$q=new mysql();
	
	if(!$q->DATABASE_EXISTS($server_database)){
		events("Install SugarCRM sub-system mysql database $server_database...");
		$q->CREATE_DATABASE($server_database);
	}
	
	
	
	if(!$q->DATABASE_EXISTS($server_database)){
		events("Install SugarCRM unable to create MYSQL Database");
		return false;
	}

	
	if(!is_file("$root/index.php")){
		events("Install SugarCRM installing source code");
		shell_exec("/bin/cp -rf /usr/local/share/artica/sugarcrm_src/* $root/");
	}
	
	
	
	
	$sugar=new SugarCRM();
	$manquesfichiers=$sugar->checkRootFiles($root);
	if(count($manquesfichiers)>0){
		events("Install SugarCRM installing source code missing ". count($manquesfichiers)." files");
		shell_exec("/bin/cp -rf /usr/local/share/artica/sugarcrm_src/* $root/");
	}
	$sugar->sql_db=$server_database;
	$sugar->sql_admin=$user;
	$sugar->sql_password=$mysql_password;
	
	$sugar->sugar_supposed_version=SUGAR_CRMVERSION($root);
	events("$servername v.$sugar->sugar_supposed_version");
	$sugar->servername=$servername;
	
	AddPrivileges($user,$mysql_password,$server_database);
	if(!$sugar->TestTables()){	
		if($q->mysql_password<>null){
			$password=" --password=$q->mysql_password ";
			}else{
				events("Install SugarCRM installing tables datas with null password");
			}
	
		$cmd="mysql --port=$q->mysql_port --skip-column-names --database=$server_database --silent --xml ";
		$cmd=$cmd." --user=$q->mysql_admin$password <$sql_file";
		shell_exec($cmd);
	}

	if($appli_user==null){$appli_user="admin";}
	$sugar->CreateAdminPassword($appli_user,$appli_password);
	$conf=$sugar->BuildSugarConf($SSL);
	events("Creating configuration file config.php");
	@file_put_contents("$root/config.php",$conf);
	
	
	events("sugar version is $sugar->sugar_supposed_version");
	$sql="UPDATE `$server_database`.`config` SET `value` = '$sugar->sugar_supposed_version' 
	WHERE `config`.`category` =  'info' 
	AND `config`.`name` = 'sugar_version'  LIMIT 1 ;";
	$q=new mysql();
	$q->QUERY_SQL($sql,$server_database);
	if(!$q->ok){
		events("$q->mysql_error");
		events("$sql");
	}
	shell_exec("chmod -R 755 $root/include/javascript");
	
	
	
}

function SUGAR_CRMVERSION($rootpath){
     $path="$rootpath/sugar_version.php";
     $datas=@file_get_contents($path);
     $tbl=explode("\n",$datas);
     	while (list ($num, $line) = each ($tbl) ){
     		if(preg_match("#\$sugar_version.+?([0-9\.a-z]+)#",$line,$re)){
     			return $re[1];
     		}
     	}   
}
//##############################################################################

function ARTICA_INSTALL($servername,$root,$hash=array()){
	@mkdir($root,0755,true);
	shell_exec("cp -rf /usr/share/artica-postfix/user-backup/* $root/");
	shell_exec("ln -s --force /usr/share/artica-postfix/ressources/settings.inc $root/ressources/settings.inc");
	shell_exec("ln -s --force /usr/share/artica-postfix/ressources/language $root/ressources/language");
	shell_exec("cp -f /usr/share/artica-postfix/ressources/class.cyrus-admin.inc $root/ressources/class.cyrus-admin.inc");
	shell_exec("cp -f /usr/share/artica-postfix/ressources/class.apache.inc $root/ressources/class.apache.inc");
	shell_exec("cp -f /usr/share/artica-postfix/ressources/class.mysql.inc $root/ressources/class.mysql.inc");

	
	
}
function ZARAFA_INSTALL($servername,$root,$hash=array()){
	events("Starting install ZARAFA_INSTALL sub-system ");
	events("ln -s --force /usr/share/zarafa-webaccess $root");
	shell_exec("ln -s --force /usr/share/zarafa-webaccess $root");
}
function ZARAFA_MOBILE_INSTALL($servername,$root,$hash=array()){
	events("Starting install ZARAFA_MOBILE sub-system ");
	events("ln -s --force /usr/share/zarafa-webaccess-mobile $root");
	shell_exec("ln -s --force /usr/share/zarafa-webaccess-mobile $root");
}






function LMB_INSTALL($servername,$root,$hash=array()){
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";
	if($root==null){events("Starting install LMB Unable to stat root dir");return false;}
	if(!is_dir("/usr/local/share/artica/lmb_src")){
		events("Starting install LMB Unable to stat LMB SRC");
		return false;
	}
	
	$user=$hash["wwwmysqluser"][0];
	$mysql_password=$hash[strtolower("WWWMysqlPassword")][0];
	
	$appli_user=$hash["wwwappliuser"][0];
	$appli_password=$hash["wwwapplipassword"][0];
	
	
	if($user==null){events("Starting install LMB Unable to stat Mysql username");return false;}
	if($mysql_password==null){events("Starting install LMB Unable to stat Mysql password");return false;}
	@mkdir($root,0755,true);
	
	$server_database=str_replace(".","_",$servername);
	$server_database=str_replace("-","_",$server_database);	
	events("Starting install LMB sub-system mysql database $server_database...");
	$q=new mysql();
	$q->CREATE_DATABASE($server_database);
	
	if(!$q->DATABASE_EXISTS($server_database)){
		events("Starting install LMB unable to create MYSQL Database");
		return false;
	}
	
	events("Starting setting permissions on Database with user $user");
	AddPrivileges($user,$mysql_password,$server_database);
	
	
	events("Starting install LMB installing source code");
	shell_exec("/bin/cp -rf /usr/local/share/artica/lmb_src/* $root/");
	if($q->mysql_password<>null){
		$password=" --password=$q->mysql_password ";
	}else{
		events("Starting install LMB installing tables datas with null password");
	}
	
	$files[]="/usr/local/share/artica/lmb_src/__install_lmb_files/bdd/lmb_install.sql";
	$files[]="/usr/local/share/artica/lmb_src/__install_lmb_files/bdd/lundimatin_villes_01.sql";
	$files[]="/usr/local/share/artica/lmb_src/__install_lmb_files/bdd/lundimatin_villes_02.sql";
	$files[]="/usr/local/share/artica/lmb_src/__install_lmb_files/bdd/lundimatin_villes_01b.sql";
	$files[]="/usr/local/share/artica/lmb_src/__install_lmb_files/bdd/lundimatin_villes_02b.sql";
	
	while (list ($num, $line) = each ($files) ){
		events("Starting install LMB installing tables datas $server_database/$num");
		$cmd="mysql --port=$q->mysql_port --skip-column-names --database=$server_database --silent --xml ";
		$cmd=$cmd." --user=$q->mysql_admin$password <$line";
		shell_exec($cmd);

	}
	
	events("Delete user if not exists...");
	$sql="DELETE FROM annu_admin WHERE ref_contact='C-000000-00001'";
	$q->QUERY_SQL($sql,$server_database);
	$sql="DELETE FROM annu_collab WHERE ref_contact='C-000000-00001'";
	$q->QUERY_SQL($sql,$server_database);
	$sql="DELETE FROM annu_collab_fonctions WHERE ref_contact='C-000000-00001'";
	$q->QUERY_SQL($sql,$server_database);
	$sql="DELETE FROM users WHERE ref_contact='C-000000-00001'";
	$q->QUERY_SQL($sql,$server_database);
	$sql="DELETE FROM users_permissions WHERE ref_user='U-000000-00001'";
	$q->QUERY_SQL($sql,$server_database);
	
$sql="INSERT INTO `annu_admin` (`ref_contact`, `type_admin`) VALUES ('C-000000-00001', 'Interne');";
$q->QUERY_SQL($sql,$server_database);
$sql="INSERT INTO `annu_collab` (`ref_contact`, `numero_secu`, `date_naissance`, `lieu_naissance`, `id_pays_nationalite`, `situation_famille`, `nbre_enfants`) VALUES ('C-000000-00001', '', '0000-00-00', '', NULL, '', NULL);";
$q->QUERY_SQL($sql,$server_database);	
$sql="INSERT INTO `annu_collab_fonctions` (`ref_contact`, `id_fonction`) VALUES ('C-000000-00001', 1);";
$q->QUERY_SQL($sql,$server_database);

$passw2=md5($appli_password);
$sql="INSERT INTO `users` (`ref_user`, `ref_contact`, `ref_coord_user`, `master`, `pseudo`, `code`, `actif`, `ordre`, `id_langage`, `last_id_interface`) VALUES
('U-000000-00001', 'C-000000-00001', 'COO-000000-00002', 1, '$appli_user', '$passw2', 1, 1, 1, 2);";
$q->QUERY_SQL($sql,$server_database);

$sq="INSERT INTO `users_permissions` (`ref_user`, `id_permission`, `value`) VALUES
('U-000000-00001', 1, 1),
('U-000000-00001', 3, 1),
('U-000000-00001', 5, 1),
('U-000000-00001', 6, 1),
('U-000000-00001', 7, 1),
('U-000000-00001', 8, 1),
('U-000000-00001', 9, 1),
('U-000000-00001', 10, 1),
('U-000000-00001', 11, 1),
('U-000000-00001', 12, 1),
('U-000000-00001', 13, 1),
('U-000000-00001', 14, 1),
('U-000000-00001', 15, 1),
('U-000000-00001', 16, 1),
('U-000000-00001', 17, 1),
('U-000000-00001', 18, 1),
('U-000000-00001', 19, 1);";
$q->QUERY_SQL($sql,$server_database);



events("Writing configurations");	
$conf="<?php\n";
$conf=$conf."\$bdd_hote = '$q->mysql_server;port=$q->mysql_port';\n"; 
$conf=$conf."\$bdd_user = '$user';\n";  
$conf=$conf."\$bdd_pass = '$mysql_password';\n";
$conf=$conf."\$bdd_base = '$server_database';\n";
$conf=$conf."?>";

@file_put_contents("$root/config/config_bdd.inc.php",$conf);
@chmod("$root/config/config_bdd.inc.php",0755);

$conf="<?php\n";
$conf=$conf."\$DIR = './';\n"; 
$conf=$conf."\$THIS_DIR = \$DIR;\n";  
$conf=$conf."?>";

@file_put_contents("$root/_dir.inc.php",$conf);
@chmod("$root/_dir.inc.php",0755);
	
	
}

function AddPrivileges($user,$mysql_password,$server_database){
	events("Starting setting permissions on Database $server_database with user $user");
	$q=new mysql();
	$sql="DELETE FROM `mysql`.`db` WHERE `db`.`Db` = '$server_database'";
	$q->QUERY_SQL($sql,"mysql");
	
	$sql="CREATE USER '$user'@'%' IDENTIFIED BY '$mysql_password';";
	$q->QUERY_SQL($sql,"mysql");

	$sql="GRANT USAGE ON $server_database. * 
	TO '$user'@'%' IDENTIFIED BY '$mysql_password' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;";
	$q->QUERY_SQL($sql);

	$sql="GRANT ALL PRIVILEGES ON `$server_database` . * TO '$user'@'%' WITH GRANT OPTION ;";
	$q->QUERY_SQL($sql);	
	
}



function vhosts($noecho=false){
$ldap=new clladp();
$sock=new sockets();
$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
$SSLStrictSNIVHostCheck=$sock->GET_INFO("SSLStrictSNIVHostCheck");
$pattern="(&(objectclass=apacheConfig)(apacheServerName=*))";
$attr=array();
$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
$hash=ldap_get_entries($ldap->ldap_connection,$sr);	

//print_r($hash);

for($i=0;$i<$hash["count"];$i++){
	$ApacheGroupWarePort_WRITE=$ApacheGroupWarePort;
	$root=$hash[$i]["apachedocumentroot"][0];
	$apacheservername=trim($hash[$i]["apacheservername"][0]);
	$wwwservertype=trim($hash[$i]["wwwservertype"][0]);
	if($wwwservertype=="WEBDAV"){continue;}
	if($wwwservertype=="BACKUPPC"){continue;}
	$wwwsslmode=$hash[$i]["wwwsslmode"][0];
	$DirectoryIndex="index.php";
	unset($rewrite);
	unset($dirplus);
	$magic_quotes_gpc="off";
	$adds=null;
	$ssl=null;
	
	
	
	if($wwwsslmode=="TRUE"){
		$ssl="\tSSLEngine on\n";
		$ssl=$ssl."\tSSLCertificateFile {$GLOBALS["SSLKEY_PATH"]}/$apacheservername.crt\n";
		$ssl=$ssl."\tSSLCertificateKeyFile {$GLOBALS["SSLKEY_PATH"]}/$apacheservername.key\n";
		vhosts_BuildCertificate($apacheservername);
		$ApacheGroupWarePort_WRITE="443";
		$SSLMODE=true;
		$conf=$conf."\n<VirtualHost *:$ApacheGroupWarePort>\n";
		$conf=$conf."\tServerName $apacheservername\n";
		$conf=$conf."\tRedirect / https://$apacheservername\n";
		$conf=$conf."</VirtualHost>\n\n";
	}
	
	$open_basedir=$root;
	
	if($wwwservertype=="OBM2"){
		$adds=$adds."\tSetEnv OBM_INCLUDE_VAR obminclude\n";
		$adds=$adds."\tAddDefaultCharset ISO-8859-15\n";
		$adds=$adds."\tphp_value  include_path \".:/usr/share/php:/usr/share/php5:$root\"\n";
		$magic_quotes_gpc="On";
		$DirectoryIndex="obm.php";
		$alias="\tAlias /images $root/resources\n";
		$root="$root/php";
	}
	
	if($wwwservertype=="DRUPAL"){
		$DirectoryIndex="index.php";
		$adds=null;
		$adds=$adds."\tAddDefaultCharset ISO-8859-15\n";
		$adds=$adds."\tAccessFileName .htaccess\n";
		$rewrite[]="\t\t\t<IfModule mod_rewrite.c>";
		$rewrite[]="\t\t\t\tRewriteEngine on";
  		$rewrite[]="\t\t\t\tRewriteBase /";
   		$rewrite[]="\t\t\t\tRewriteCond %{REQUEST_FILENAME} !-f";
   		$rewrite[]="\t\t\t\tRewriteCond %{REQUEST_FILENAME} !-d";
   		$rewrite[]="\t\t\t\tRewriteRule ^(.*)$ index.php?q=$1 [L,QSA]";
   		$rewrite[]="\t\t\t</IfModule>";
        $rewrite[]="\t\t\t<FilesMatch \"\.(engine|inc|info|install|module|profile|po|sh|.*sql|theme|tpl(\.php)?|xtmpl)$|^(code-style\.pl|Entries.*|Repository|Root|Tag|Template)$\">";
        $rewrite[]="\t\t\t\tOrder allow,deny";
        $rewrite[]="\t\t\t\tdeny from all";
        $rewrite[]="\t\t\t</FilesMatch>";  	

        $dirplus[]="\t\t\t<Location /cron.php>";
        $dirplus[]="\t\t\t\tOrder deny,allow";
        $dirplus[]="\t\t\t\tdeny from all";
        $dirplus[]="\t\t\t\tallow from 127.0.0.1";
        $dirplus[]="\t\t\t\tallow from IP";
    	$dirplus[]="\t\t\t</Location>";
        
		$root="/usr/share/drupal";
		@mkdir("/usr/share/drupal/sites/$apacheservername/files",0755,true);
		@chmod("/usr/share/drupal/sites/$apacheservername/files",0777);
	}	
	
	
	if($wwwservertype=="GROUPOFFICE"){$open_basedir=null;}
	
	
	
	
	@mkdir("$root/php_logs/$apacheservername",0755,true);
	$conf=$conf."\n\n<VirtualHost *:$ApacheGroupWarePort_WRITE>\n";
	$conf=$conf."\tServerName $apacheservername\n";
	$conf=$conf."\tServerAdmin webmaster@$apacheservername\n";
	$conf=$conf."\tDocumentRoot $root\n";
	$conf=$conf.$ssl;
	$conf=$conf.$alias;
	$conf=$conf.$adds;
	$conf=$conf."\tphp_value  error_log  \"$root/php_logs/$apacheservername/php.log\"\n";  
	if($open_basedir<>null){
		$conf=$conf."\tphp_value open_basedir \"$root\"\n";
	} 
	$conf=$conf."\tphp_value magic_quotes_gpc $magic_quotes_gpc\n";	
	
	$conf=$conf."\t<Directory \"$root\">\n";
	if(is_array($rewrite)){$conf=$conf.@implode("\n",$rewrite)."\n";}	
	$conf=$conf."\t\t\tDirectoryIndex $DirectoryIndex\n";
	$conf=$conf."\t\t\tOptions Indexes FollowSymLinks MultiViews\n";
	$conf=$conf."\t\t\tAllowOverride all\n";
	$conf=$conf."\t\t\tOrder allow,deny\n";
	$conf=$conf."\t\t\tAllow from all\n";

	$conf=$conf."\t</Directory>\n";
	if(is_array($dirplus)){$conf=$conf.@implode("\n",$dirplus)."\n";}	
	$conf=$conf."\tCustomLog /usr/local/apache-groupware/logs/{$apacheservername}_access.log \"%h %l %u %t \\\"%r\\\" %>s %b \\\"%{Referer}i\\\" \\\"%{User-Agent}i\\\" %V\"\n";
	$conf=$conf."\tErrorLog /usr/local/apache-groupware/logs/{$apacheservername}_err.log\n";
	$conf=$conf."</VirtualHost>\n";
	
}
if($SSLMODE){
	if($SSLStrictSNIVHostCheck==1){$SSLStrictSNIVHostCheck="\nSSLStrictSNIVHostCheck off";}
	$conf="Listen 443$SSLStrictSNIVHostCheck\nNameVirtualHost *:443\n".$conf;
}
$conf=$conf.mailmanhosts();

if($noecho){return $conf;}
echo $conf;
	
}

function ROUNDCUBE_SRC_FOLDER(){
	if(is_file('/usr/share/roundcube/index.php')){return '/usr/share/roundcube';}
	if(is_file('/usr/share/roundcubemail/index.php')){return '/usr/share/roundcubemail';}	
	
}

function OPENGOO_TEST_FILES($root){
	$file="/usr/share/artica-postfix/bin/install/opengoo/files.txt";
	if(!is_file($file)){return false;}
	$tbl=explode("\n",@file_get_contents($file));
	while (list ($num, $file) = each ($tbl) ){
			if($file==null){continue;}
			if(!is_file("$root/$file")){
				events("Starting install OpenGoo $root/$file does not exists");
				return false;	
			}
	}

	return true;
	
}

function GROUPOFFICE_TEST_FILES($root){
	$file="/usr/share/artica-postfix/bin/install/opengoo/group-office.txt";
	if(!is_file($file)){return false;}
	$tbl=explode("\n",@file_get_contents($file));
	while (list ($num, $file) = each ($tbl) ){
			if($file==null){continue;}
			if(!is_file("$root/$file")){
				events("Starting install GroupOffice $root/$file does not exists");
				return false;	
			}
	}

	return true;	
}

function OPENGOO_INSTALL($servername,$root,$hash=array()){
	$srcfolder="/usr/local/share/artica/opengoo";
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";	
	$sql_file="/usr/share/artica-postfix/bin/install/opengoo/opengoo.sql";



	if($root==null){events("Starting install opengoo Unable to stat root dir");return false;}
	if(!is_dir($srcfolder)){
		events("Starting install opengoo Unable to stat SRC");
		return false;
	}
	
	
	$user=$hash["wwwmysqluser"][0];
	$mysql_password=$hash[strtolower("WWWMysqlPassword")][0];
	
	$appli_user=$hash["wwwappliuser"][0];
	$appli_password=$hash["wwwapplipassword"][0];	
	$wwwsslmode=$hash["wwwsslmode"][0];
		
	$server_database=str_replace(".","_",$servername);
	$server_database=str_replace(" ","_",$server_database);
	$server_database=str_replace("-","_",$server_database);	
	events("Starting install opengoo sub-system mysql database $server_database...");	
	
	if($user==null){events("Starting install opengoo Unable to stat Mysql username");return false;}
	if($mysql_password==null){events("Starting install opengoo Unable to stat Mysql password");return false;}
	@mkdir($root,0755,true);
	
	events("Starting install opengoo sub-system mysql database $server_database...");
	$q=new mysql();
	if(!$q->DATABASE_EXISTS($server_database)){
		$q->CREATE_DATABASE($server_database);
	}
	
	if(!$q->DATABASE_EXISTS($server_database)){
		events("Starting install opengoo unable to create MYSQL Database");
		return false;
	}
	
	events("Starting setting permissions on Database with user $user");
	AddPrivileges($user,$mysql_password,$server_database);
	
	
	if(!OPENGOO_TEST_FILES($root)){
		events("Starting install opengoo installing source code");
		shell_exec("/bin/cp -rf $srcfolder/* $root/");
	}
	
	$opengoo=new opengoo(null,$server_database);
	if(!OPENGOO_CHECK_TABLES($server_database)){
		if($q->mysql_password<>null){
			$password=" --password=$q->mysql_password ";
		}else{
			events("Starting install opengoo installing tables datas with null password");
		}
		
		$cmd="mysql --port=$q->mysql_port --skip-column-names --database=$server_database --silent --xml ";
		$cmd=$cmd." --user=$q->mysql_admin$password <$sql_file";
		shell_exec($cmd);	
	}else{
		events("Starting install opengo Mysql tables are already installed");
		
	}
	
	$sock=new sockets();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	events("Starting install opengo SSL=$wwwsslmode");
	if($wwwsslmode=="TRUE"){
		$ROOT_URL="https://$servername";
	}else{
		$ROOT_URL="http://$servername:$ApacheGroupWarePort";
	}
	$conf="<?php\n";
	$conf=$conf."define('DB_ADAPTER', 'mysql');\n"; 
	$conf=$conf."define('DB_HOST', '127.0.0.1');\n";
	$conf=$conf."define('DB_USER', '$q->mysql_admin');\n"; 
	$conf=$conf."define('DB_PASS', '$q->mysql_password');\n"; 
	$conf=$conf."define('DB_NAME', '$server_database');\n"; 
	$conf=$conf."define('DB_PERSIST', true);\n"; 
	$conf=$conf."define('TABLE_PREFIX', 'og_');\n"; 
	$conf=$conf."define('DB_ENGINE', 'InnoDB');\n"; 
	$conf=$conf."define('ROOT_URL', '$ROOT_URL');\n"; 
	$conf=$conf."define('DEFAULT_LOCALIZATION', 'en_us');\n"; 
	$conf=$conf."define('COOKIE_PATH', '/');\n"; 
	$conf=$conf."define('DEBUG', false);\n"; 
	$conf=$conf."define('SEED', '6eb2551152da5a57576754716397703c');\n"; 
	$conf=$conf."define('DB_CHARSET', 'utf8');\n"; 
	$conf=$conf."return true;\n";
	$conf=$conf."?>";
	
	@file_put_contents("$root/config/config.php",$conf);
	
	$opengoo->DefaultsValues();
	events("updating administrator credentials");
	$opengoo->www_servername=$servername;
	$opengoo->UpdateAdmin($appli_user,$appli_password);
	events("updating company name");
	$ou=$opengoo->get_Organization($servername);
	$opengoo->UpdateCompany($ou);
	$unix=new unix();
	$sock=new sockets();
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.opengoo.php");
	
	

}
function GROUPOFFICE_INSTALL($servername,$root,$hash=array()){
	$srcfolder="/usr/local/share/artica/group-office";
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";	
	$sql_file="/usr/share/artica-postfix/bin/install/opengoo/group-office.sql";
	$sql_datas="/usr/share/artica-postfix/bin/install/opengoo/group-office-datas.sql";


	if($root==null){events("Starting install GroupOffice Unable to stat root dir");return false;}
	if(!is_dir($srcfolder)){
		events("Starting install GroupOffice Unable to stat SRC");
		return false;
	}
	
	
	$user=$hash["wwwmysqluser"][0];
	$mysql_password=$hash[strtolower("WWWMysqlPassword")][0];
	
	$appli_user=$hash["wwwappliuser"][0];
	$appli_password=$hash["wwwapplipassword"][0];	
	$wwwsslmode=$hash["wwwsslmode"][0];
	$ou=$hash["OU"][0];
	$server_database=str_replace(".","_",$servername);
	$server_database=str_replace(" ","_",$server_database);
	$server_database=str_replace("-","_",$server_database);	
	events("Starting install GroupOffice sub-system mysql database $server_database...");	
	
	if($user==null){events("Starting install GroupOffice Unable to stat Mysql username");return false;}
	if($mysql_password==null){events("Starting install GroupOffice Unable to stat Mysql password");return false;}
	@mkdir($root,0755,true);
	
	events("Starting install GroupOffice sub-system mysql database $server_database...");
	$q=new mysql();
	if(!$q->DATABASE_EXISTS($server_database)){
		$q->CREATE_DATABASE($server_database);
	}
	
	if(!$q->DATABASE_EXISTS($server_database)){
		events("Starting install GroupOffice unable to create MYSQL Database");
		return false;
	}
	
	events("Starting setting permissions on Database with user $user");
	AddPrivileges($user,$mysql_password,$server_database);

	
	if(!GROUPOFFICE_TEST_FILES($root)){
		events("Starting install GroupOffice installing source code");
		shell_exec("/bin/cp -rf $srcfolder/* $root/");
		@mkdir("/home/groupoffice/$servername",0777,true);
	 }
	 @mkdir("/home/groupoffice/$servername",0777,true);
	 $unix=new unix();
	 $apacheuser=$unix->APACHE_GROUPWARE_ACCOUNT();
	 events("chown /home/groupoffice has $apacheuser");
	 shell_exec("/bin/chown -R $apacheuser /home/groupoffice");
	
	
	if($q->mysql_password<>null){
		$password=" --password=$q->mysql_password ";
	}else{
		events("Starting install GroupOffice installing tables datas with null password");
	}
		
	$cmd="mysql --port=$q->mysql_port --skip-column-names --database=$server_database --silent --xml ";
	$cmd=$cmd." --user=$q->mysql_admin$password <$sql_file";
	shell_exec($cmd);	
	
	$sock=new sockets();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	events("Starting install opengo SSL=$wwwsslmode");
	if($wwwsslmode=="TRUE"){
		$ROOT_URL="https://$servername";
	}else{
		$ROOT_URL="http://$servername:$ApacheGroupWarePort";
	}
	
	$q=new mysql();
		
		$conf[]="<?php";
		$conf[]="\$config['enabled']=true;";
		$conf[]="\$config['id']=\"groupoffice\";";
		$conf[]="\$config['debug']=false;";
		$conf[]="\$config['log']=false;";
		$conf[]="\$config['language']=\"en\";";
		$conf[]="\$config['default_country']=\"FR\";";
		$conf[]="\$config['default_timezone']=\"Europe/Amsterdam\";";
		$conf[]="\$config['default_currency']=\"€\";";
		$conf[]="\$config['default_date_format']=\"dmY\";";
		$conf[]="\$config['default_date_separator']=\"-\";";
		$conf[]="\$config['default_time_format']=\"G:i\";";
		$conf[]="\$config['default_first_weekday']=\"1\";";
		$conf[]="\$config['default_decimal_separator']=\",\";";
		$conf[]="\$config['default_thousands_separator']=\".\";";
		$conf[]="\$config['theme']=\"Default\";";
		$conf[]="\$config['allow_themes']=true;";
		$conf[]="\$config['allow_password_change']=false;";
		$conf[]="\$config['allow_profile_edit']=true;";
		$conf[]="\$config['allow_registration']=false;";
		$conf[]="\$config['registration_fields']=\"title_initials,sex,birthday,address,home_phone,fax,cellular,company,department,function,work_address,work_phone,work_fax,homepage\";";
		$conf[]="\$config['required_registration_fields']=\"company,address\";";
		$conf[]="\$config['allow_duplicate_email']=false;";
		$conf[]="\$config['auto_activate_accounts']=false;";
		$conf[]="\$config['notify_admin_of_registration']=true;";
		$conf[]="\$config['register_modules_read']=\"summary,email,calendar,tasks,addressbook,files,notes,links,tools,comments\";";
		$conf[]="\$config['register_modules_write']=\"\";";
		$conf[]="\$config['allowed_modules']=\"\";";
		$conf[]="\$config['register_user_groups']=\"\";";
		$conf[]="\$config['register_visible_user_groups']=\",\";";
		$conf[]="\$config['host']=\"/\";";
		$conf[]="\$config['force_login_url']=false;";
		$conf[]="\$config['full_url']=\"$ROOT_URL/\";";
		$conf[]="\$config['title']=\"Group-Office\";";
		$conf[]="\$config['webmaster_email']=\"webmaster@example.com\";";
		$conf[]="\$config['root_path']=\"$root/\";";
		$conf[]="\$config['tmpdir']=\"/tmp/\";";
		$conf[]="\$config['max_users']=\"0\";";
		$conf[]="\$config['quota']=\"0\";";
		$conf[]="\$config['db_type']=\"mysql\";";
		$conf[]="\$config['db_host']=\"$q->mysql_server\";";
		$conf[]="\$config['db_name']=\"$server_database\";";
		$conf[]="\$config['db_user']=\"$user\";";
		$conf[]="\$config['db_pass']=\"$mysql_password\";";
		$conf[]="\$config['db_port']=\"$q->mysql_port\";";
		$conf[]="\$config['db_socket']=\"\";";
		$conf[]="\$config['file_storage_path']=\"/home/groupoffice/$servername/\";";
		$conf[]="\$config['max_file_size']=\"10000000\";";
		$conf[]="\$config['smtp_server']=\"127.0.0.1\";";
		$conf[]="\$config['smtp_port']=\"25\";";
		$conf[]="\$config['smtp_username']=\"\";";
		$conf[]="\$config['smtp_password']=\"\";";
		$conf[]="\$config['smtp_encryption']=\"\";";
		$conf[]="\$config['smtp_local_domain']=\"\";";
		$conf[]="\$config['restrict_smtp_hosts']=\"\";";
		$conf[]="\$config['max_attachment_size']=\"10000000\";";
		$unix=new unix();
		$ldap=new clladp();
		$zip=$unix->find_program("zip");
		$unzip=$unix->find_program("unzip");
		$xml2wbxml=$unix->find_program("xml2wbxml");
		$conf[]="\$config['cmd_zip']=\"$zip\";";
		$conf[]="\$config['cmd_unzip']=\"$unzip\";";
		$conf[]="\$config['cmd_tar']=\"/bin/tar\";";
		$conf[]="\$config['cmd_chpasswd']=\"/usr/sbin/chpasswd\";";
		$conf[]="\$config['cmd_sudo']=\"/usr/bin/sudo\";";
		$conf[]="\$config['cmd_xml2wbxml']=\"$xml2wbxml\";";
		$conf[]="\$config['cmd_wbxml2xml']=\"/usr/bin/wbxml2xml\";";
		$conf[]="\$config['cmd_tnef']=\"/usr/bin/tnef\";";
		$conf[]="\$config['cmd_php']=\"php\";";
		$conf[]="\$config['phpMyAdminUrl']=\"\";";
		$conf[]="\$config['allow_unsafe_scripts']=\"\";";
		$conf[]="\$config['default_password_length']=\"6\";";
		$conf[]="\$config['session_inactivity_timeout']=\"0\"";		
		//$conf[]="\$config['ldap_host']='$ldap->ldap_host';";	
		//$conf[]="\$config['ldap_user']='$ldap->ldap_admin';";	
		//$conf[]="\$config['ldap_pass']='$ldap->ldap_password';";	
		//$conf[]="\$config['ldap_basedn']='ou=$ou,dc=organizations,$ldap->suffix';";	
		//$conf[]="\$config['ldap_peopledn']='ou=users,ou=$ou,dc=organizations,$ldap->suffix';";	
		//$conf[]="\$config['ldap_groupsdn']='ou=groups,ou=$ou,dc=organizations,$ldap->suffix';";
		$conf[]="?>";		
		@file_put_contents("$root/config.php",implode("\n",$conf));
		
		$sql = "UPDATE go_users SET password='".md5($appli_password)."',username='$appli_user' WHERE id='1'";
		$q=new mysql();
		$q->QUERY_SQL($sql,$server_database);
		
		events("Starting install GroupOffice $root/config.php done...");
}




function ROUNDCUBE_INSTALL($servername,$root,$hash=array()){
	$srcfolder=ROUNDCUBE_SRC_FOLDER();
	$sock=new sockets();
	$ldap=new clladp();
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	
	echo "Starting......: Roundcube $servername\n"; 
	
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";	
	if($root==null){events("Starting install roundcube Unable to stat root dir");return false;}
	if(!is_dir($srcfolder)){
		events("Starting install roundcube Unable to stat SRC");
		return false;
	}
	$sql_file="$srcfolder/SQL/mysql.initial.sql";
	
	if(!is_file($sql_file)){
		events("Starting install roundcube Unable to stat $srcfolder");
		return false;
	}
	

	$user=$hash["wwwmysqluser"][0];
	$mysql_password=$hash[strtolower("WWWMysqlPassword")][0];
			
	$server_database=str_replace(".","_",$servername);
	$server_database=str_replace("-","_",$server_database);	
	events("Starting install roundcube sub-system mysql database $server_database...");	
	
	if($user==null){events("Starting install roundcube Unable to stat Mysql username");return false;}
	if($mysql_password==null){events("Starting install roundcube Unable to stat Mysql password");return false;}
	@mkdir($root,0755,true);
	
	events("Starting install roundcube sub-system mysql database $server_database...");
	$q=new mysql();
	$q->CREATE_DATABASE($server_database);
	
	if(!$q->DATABASE_EXISTS($server_database)){
		events("Starting install roundcube unable to create MYSQL Database");
		return false;
	}
	
	events("Starting setting permissions on Database with user $user");
	echo "Starting......: Roundcube $servername set permissions on Database with user $user\n"; 
	AddPrivileges($user,$mysql_password,$server_database);
	
	
	events("Starting install roundcube installing source code");
	echo "Starting......: Roundcube $servername installing source code\n"; 
	
	shell_exec("/bin/rm -rf $root/*");
	shell_exec("/bin/cp -rf $srcfolder/* $root/");
	if($q->mysql_password<>null){
		$password=" --password=$q->mysql_password ";
	}else{
		events("Starting install roundcube installing tables datas with null password");
	}

	$files[]=$sql_file;
	$files[]="$srcfolder/SQL/mysql.update.sql";
	
	while (list ($num, $line) = each ($files) ){
		events("Starting install roundcube installing tables $server_database/$num");
		$cmd="mysql --port=$q->mysql_port --skip-column-names --database=$server_database --silent --xml ";
		$cmd=$cmd." --user=$q->mysql_admin$password <$line";
		shell_exec($cmd);
		
		events("Starting install roundcube installing datas $server_database/$num");
		$cmd="mysql --port=$q->mysql_port --skip-column-names --database=$server_database --silent --xml ";
		$cmd=$cmd." --user=$q->mysql_admin$password <$sql_datas";
		shell_exec($cmd);		
		

	}	

	$q->checkRoundCubeTables($server_database);
	$conf[]="<?php";
	$conf[]="\$rcmail_config = array();";
	$conf[]="\$rcmail_config[\"db_dsnw\"] = \"mysql://$q->mysql_admin:$q->mysql_password@$q->mysql_server/$server_database\";";
	$conf[]="\$rcmail_config[\"db_dsnr\"] = \"\";";
	$conf[]="\$rcmail_config[\"db_max_length\"] = 512000;  // 500K";
	$conf[]="\$rcmail_config[\"db_persistent\"] = FALSE;";
	$conf[]="\$rcmail_config[\"db_table_users\"] = \"users\";";
	$conf[]="\$rcmail_config[\"db_table_identities\"] = \"identities\";";
	$conf[]="\$rcmail_config[\"db_table_contacts\"] = \"contacts\";";
	$conf[]="\$rcmail_config[\"db_table_session\"] = \"session\";";
	$conf[]="\$rcmail_config[\"db_table_cache\"] = \"cache\";";
	$conf[]="\$rcmail_config[\"db_table_messages\"] = \"messages\";";
	$conf[]="\$rcmail_config[\"db_sequence_users\"] = \"user_ids\";";
	$conf[]="\$rcmail_config[\"db_sequence_identities\"] = \"identity_ids\";";
	$conf[]="\$rcmail_config[\"db_sequence_contacts\"] = \"contact_ids\";";
	$conf[]="\$rcmail_config[\"db_sequence_cache\"] = \"cache_ids\";";
	$conf[]="\$rcmail_config[\"db_sequence_messages\"] = \"message_ids\";";
	$conf[]="?>";
	events("Starting install roundcube saving $root/config/db.inc.php");
	echo "Starting......: Roundcube $servername db.inc.php OK\n";
	@file_put_contents("$root/config/db.inc.php",@implode("\n",$conf));	
	
	unset($conf);
	
	$wwwmultismtpsender=$hash["wwwmultismtpsender"][0];
	$WWWEnableAddressBook=$hash["wwwenableaddressbook"][0];
	events("OU={$hash["OU"][0]} EnablePostfixMultiInstance=$EnablePostfixMultiInstance, SMTP=$wwwmultismtpsender");
	
	
	
	$conf[]="<?php";
	$conf[]="\$rcmail_config = array();";
	$conf[]="\$rcmail_config['debug_level'] =1;";
	$conf[]="\$rcmail_config['enable_caching'] = TRUE;";
	$conf[]="\$rcmail_config['message_cache_lifetime'] = '10d';";
	$conf[]="\$rcmail_config['auto_create_user'] = TRUE;";
	$conf[]="\$rcmail_config['default_host'] = '127.0.0.1';";
	$conf[]="\$rcmail_config['default_port'] = 143;";
	
	if($EnablePostfixMultiInstance==1){
		if(trim($wwwmultismtpsender)<>null){
		$conf[]="// SMTP server used for sending mails.";
		$conf[]="\$rcmail_config['smtp_server'] = '$wwwmultismtpsender';";
		$conf[]="\$rcmail_config['smtp_port'] = 25;"; 
		
	} }else{
		$conf[]="\$rcmail_config['smtp_server'] = '127.0.0.1';";
		$conf[]="\$rcmail_config['smtp_port'] = 25;"; 	
	}
	
	
	$conf[]="\$rcmail_config['smtp_user'] = '';";
	$conf[]="\$rcmail_config['smtp_pass'] = '';";	
	$conf[]="\$rcmail_config['smtp_auth_type'] = '';";
	$conf[]="\$rcmail_config['smtp_helo_host'] = '';";
	$conf[]="\$rcmail_config['smtp_log'] = TRUE;";
	$conf[]="\$rcmail_config['username_domain'] = '';";
	$conf[]="\$rcmail_config['mail_domain'] = '';";
	$conf[]="\$rcmail_config['virtuser_file'] = '';";
	$conf[]="\$rcmail_config['virtuser_query'] = '';";
	$conf[]="\$rcmail_config['list_cols'] = array('subject', 'from', 'date', 'size');";
	$conf[]="\$rcmail_config['skin_path'] = 'skins/default/';";
	$conf[]="\$rcmail_config['skin_include_php'] = FALSE;";
	$conf[]="\$rcmail_config['temp_dir'] = 'temp/';";
	$conf[]="\$rcmail_config['log_dir'] = 'logs/';";
	$conf[]="\$rcmail_config['session_lifetime'] = 10;";
	$conf[]="\$rcmail_config['ip_check'] = false;";
	$conf[]="\$rcmail_config['double_auth'] = false;";
	$conf[]="\$rcmail_config['des_key'] = 'NIbXC7RaFsZvQTV5NWBbQd9H';";
	$conf[]="\$rcmail_config['locale_string'] = 'us';";
	$conf[]="\$rcmail_config['date_short'] = 'D H:i';";
	$conf[]="\$rcmail_config['date_long'] = 'd.m.Y H:i';";
	$conf[]="\$rcmail_config['date_today'] = 'H:i';";
	$conf[]="\$rcmail_config['useragent'] = 'RoundCube Webmail/0.1-rc2';";
	$conf[]="\$rcmail_config['product_name'] = 'RoundCube Webmail for {$hash["OU"][0]}';";
	$conf[]="\$rcmail_config['imap_root'] = null;";
	$conf[]="\$rcmail_config['drafts_mbox'] = 'Drafts';";
	$conf[]="\$rcmail_config['junk_mbox'] = 'Junk';";
	$conf[]="\$rcmail_config['sent_mbox'] = 'Sent';";
	$conf[]="\$rcmail_config['trash_mbox'] = 'Trash';";
	$conf[]="\$rcmail_config['default_imap_folders'] = array('INBOX', 'Drafts', 'Sent', 'Junk', 'Trash');";
	$conf[]="\$rcmail_config['protect_default_folders'] = TRUE;";
	$conf[]="\$rcmail_config['skip_deleted'] = TRUE;";
	$conf[]="\$rcmail_config['read_when_deleted'] = TRUE;";
	$conf[]="\$rcmail_config['flag_for_deletion'] = TRUE;";
	$conf[]="\$rcmail_config['enable_spellcheck'] = TRUE;";
	$conf[]="\$rcmail_config['spellcheck_uri'] = '';";
	$conf[]="\$rcmail_config['spellcheck_languages'] = NULL;";
	$conf[]="\$rcmail_config['generic_message_footer'] = '';";
	$conf[]="\$rcmail_config['mail_header_delimiter'] = NULL;";
	$conf[]="";
	if($WWWEnableAddressBook==1){
		$conf[]="\$rcmail_config['ldap_public']['{$hash["OU"][0]}'] = array(";
		$conf[]="	'name'          => '{$hash["OU"][0]}',";
		$conf[]="	'hosts'         => array('$ldap->ldap_host'),";
		$conf[]="	'port'          => $ldap->ldap_port,";
		$conf[]="	'base_dn'       => 'ou={$hash["OU"][0]},dc=organizations,$ldap->suffix',";
		$conf[]="	'bind_dn'       => 'cn=$ldap->ldap_admin,$ldap->suffix',";
		$conf[]="	'bind_pass'     => '$ldap->ldap_password',";
		$conf[]="	'ldap_version'  => 3,       // using LDAPv3";
		$conf[]="	'search_fields' => array('mail', 'cn','uid','givenName','DisplayName'),  // fields to search in";
		$conf[]="	'name_field'    => 'cn',    // this field represents the contact's name";
		$conf[]="	'email_field'   => 'mail',  // this field represents the contact's e-mail";
		$conf[]="	'surname_field' => 'sn',    // this field represents the contact's last name";
		$conf[]="	'firstname_field' => 'gn',  // this field represents the contact's first name";
		$conf[]="	'scope'         => 'sub',   // search mode: sub|base|list";
		$conf[]="	'LDAP_Object_Classes' => array( 'person', 'inetOrgPerson', 'userAccount'),";
		$conf[]="	'filter'        => 'givenName=*',      // used for basic listing (if not empty) and will be &'d with search queries. ex: (status=act)";
		$conf[]="	'fuzzy_search'  => true);   // server allows wildcard search";
	}
	$conf[]="// enable composing html formatted messages (experimental)";
	$conf[]="\$rcmail_config['enable_htmleditor'] = TRUE;";
	$conf[]="\$rcmail_config['dont_override'] =array('index_sort','trash_mbox','sent_mbox','junk_mbox','drafts_mbox');";
	$conf[]="\$rcmail_config['javascript_config'] = array('read_when_deleted', 'flag_for_deletion');";
	$conf[]="\$rcmail_config['include_host_config'] = FALSE;";
	$conf[]="";
	$conf[]="";
	$conf[]="/***** these settings can be overwritten by user's preferences *****/";
	$conf[]="";
	$conf[]="// show up to X items in list view";
	$conf[]="\$rcmail_config['pagesize'] = 40;";
	$conf[]="";
	$conf[]="// use this timezone to display date/time";
	$conf[]="\$rcmail_config['timezone'] = intval(date('O'))/100 - date('I');";
	$conf[]="";
	$conf[]="// is daylight saving On?";
	$conf[]="\$rcmail_config['dst_active'] = (bool)date('I');";
	$conf[]="";
	$conf[]="// prefer displaying HTML messages";
	$conf[]="\$rcmail_config['prefer_html'] = TRUE;";
	$conf[]="";
	$conf[]="// show pretty dates as standard";
	$conf[]="\$rcmail_config['prettydate'] = TRUE;";
	$conf[]="";
	$conf[]="// default sort col";
	$conf[]="\$rcmail_config['message_sort_col'] = 'date';";
	$conf[]="";
	$conf[]="// default sort order";
	$conf[]="\$rcmail_config['message_sort_order'] = 'DESC';";
	$conf[]="";
	$conf[]="// save compose message every 300 seconds (5min)";
	$conf[]="\$rcmail_config['draft_autosave'] = 300;";
	$conf[]="";
	$conf[]="/***** PLUGINS for Roundcube V3 *****/";
	$conf[]="\$rcmail_config['plugins'] = array();";
	
	if(is_file("$root/plugins/sieverules/sieverules.php")){
		$conf[]="\$rcmail_config['plugins'] = array('sieverules');";
		$sieve[]="<?php";
		$sieve[]="\$rcmail_config[\"sieverules_host\"] = \"127.0.0.1\";";
		$sieve[]="\$rcmail_config[\"sieverules_port\"] = 2000;";
		$sieve[]="\$rcmail_config[\"sieverules_usetls\"] = FALSE;";
		$sieve[]="\$rcmail_config[\"sieverules_folder_delimiter\"] = null;";
		$sieve[]="\$rcmail_config[\"sieverules_folder_encoding\"] = null;";
		$sieve[]="\$rcmail_config[\"sieverules_include_imap_root\"] = FALSE;";
		$sieve[]="\$rcmail_config[\"sieverules_ruleset_name\"] = \"roundcube\";";
		$sieve[]="\$rcmail_config[\"sieverules_multiple_actions\"] = TRUE;";
		$sieve[]="\$rcmail_config[\"sieverules_allowed_actions\"] = array(\"fileinto\" => TRUE,\"vacation\" => TRUE,\"reject\" => TRUE,\"redirect\" => TRUE,\"keep\" => TRUE,\"discard\" => TRUE,\"imapflags\" => TRUE,\"notify\" => TRUE,\"stop\" => TRUE);";
		$sieve[]="\$rcmail_config[\"sieverules_other_headers\"] = array(\"Reply-To\", \"List-Id\", \"MailingList\", \"Mailing-List\",\"X-ML-Name\", \"X-List\", \"X-List-Name\", \"X-Mailing-List\",\"Resent-From\",";
		$sieve[]="	\"Resent-To\", \"X-Mailer\", \"X-MailingList\",\"X-Spam-Status\", \"X-Priority\", \"Importance\", \"X-MSMail-Priority\",\"Precedence\", \"Return-Path\", \"Received\", \"Auto-Submitted\",\"X-Spam-Flag\", \"X-Spam-Tests\");";
		$sieve[]="\$rcmail_config[\"sieverules_predefined_rules\"] = array();";
		$sieve[]="\$rcmail_config[\"sieverules_adveditor\"] = 0;";
		$sieve[]="\$rcmail_config[\"sieverules_multiplerules\"] = FALSE;";
		$sieve[]="\$rcmail_config[\"sieverules_default_file\"] = \"/etc/dovecot/sieve/default\";";
		$sieve[]="\$rcmail_config[\"sieverules_auto_load_default\"] = FALSE;";
		$sieve[]="\$rcmail_config[\"sieverules_example_file\"] = \"/etc/dovecot/sieve/example\";";
		$sieve[]="\$rcmail_config[\"sieverules_force_vacto\"] = TRUE;";
		$sieve[]="\$rcmail_config[\"sieverules_use_elsif\"] = TRUE;";
		$sieve[]="?>";		
		@file_put_contents("$root/plugins/sieverules/config.inc.php",@implode("\n",$sieve));
		
	}
	
	$conf[]="";
	$conf[]="";
	$conf[]="// don't let users set pagesize to more than this value if set";
	$conf[]="\$rcmail_config['max_pagesize'] = 200;";
	$conf[]="\$rcmail_config['create_default_folders'] = TRUE;";
	$conf[]="";
	$conf[]="";
	$conf[]="// end of config file";
	$conf[]="?>";	
		
	@file_put_contents("$root/config/main.inc.php",@implode("\n",$conf));
	
	
	$sock=new sockets();
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	
	if($EnablePostfixMultiInstance==1){
		echo "Starting......: Roundcube $servername Postfix Multi Instance Enabled \n";
		$smtp=$hash[strtolower("WWWMultiSMTPSender")][0];
		$tbl=@explode("\n",@file_get_contents("$root/config/main.inc.php"));
		while (list ($i, $line) = each ($tbl) ){
			if(preg_match("#rcmail_config.+?smtp_server#",$line)){
				echo "Starting......: Roundcube $servername Postfix change line $i to $smtp\n";
				$tbl[$i]="\$rcmail_config['smtp_server'] = '$smtp';";
			}
		}
		@file_put_contents("$root/config/main.inc.php",@implode("\n",$tbl));
	}
}


function events($text){
		if($GLOBALS["VERBOSE"]){$_GET["debug"]=true;}
		if($_GET["debug"]){echo "Starting......: Apache groupware $text\n";}
		
		writelogs($text,"main",__FILE__,__LINE__);
		}
		
		
		
		
function mailmanhosts(){
	$ldap=new clladp();
	$sock=new sockets();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	$filter="(&(Objectclass=ArticaMailManRobots)(cn=*))";
	$sr = @ldap_search($ldap->ldap_connection,"dc=organizations,$ldap->suffix",$filter,array());
	if(!$sr){
		writelogs("No mailman list found for pattern $filter",__FUNCTION__,__FILE__,__LINE__);	
		return null;
	
	}
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	$cgi_path=mailman_cgibin_path();
	writelogs("cgi_path=$cgi_path, \"dc=organizations,$ldap->suffix\" count=\"".$hash["count"]."\"",__FUNCTION__,__FILE__,__LINE__);
	
	for($i=0;$i<$hash["count"];$i++){
		$webservername=null;
		$webservername=$hash[$i][strtolower("MailManWebServerName")][0];
		$admin_email=$hash[$i]["mailmanowner"][0];
		$cn=$hash[$i]["cn"][0];
		if(preg_match("#(.+?)@#",$cn,$re)){$listname=$re[1];}
		if($admin_email==null){
			writelogs("$webservername= no admin mail, abort DN: {$hash[$i]["dn"]}",__FUNCTION__,__FILE__,__LINE__);
			continue;
		}
		if($webservername==null){
			writelogs("no webserver name, abort",__FUNCTION__,__FILE__,__LINE__);
			continue;
		
			}
			@mkdir("/usr/share/artica-groupware/$webservername/css",0755,true);
			@copy("/usr/share/artica-postfix/bin/install/mailman/style.css","/usr/share/artica-groupware/$webservername/css/style.css");
			$conf=$conf."\n\n<VirtualHost *:$ApacheGroupWarePort>\n";
			$conf=$conf."ServerAdmin $admin_email\n";
			$conf=$conf."ServerName $webservername\n";
			$conf=$conf."DocumentRoot /usr/share/artica-groupware/$webservername\n";
			$conf=$conf."ScriptAlias /mailman/ $cgi_path/\n";
			$conf=$conf."ScriptAlias /cgi-bin/mailman/ $cgi_path/\n";
			$conf=$conf."<Directory \"$cgi_path\">\n"; 
			$conf=$conf."   Options -MultiViews +SymLinksIfOwnerMatch\n";
			$conf=$conf."   AllowOverride all\n";
			$conf=$conf."   Order allow,deny\n";
			$conf=$conf."  Allow from all\n";			
			$conf=$conf."</Directory>\n";
			$conf=$conf."Alias /images/mailman/ /usr/share/images/mailman/\n";
			$conf=$conf."<Directory \"/usr/share/images/mailman/\">\n";
			$conf=$conf."    AllowOverride None\n";
			$conf=$conf."    Order allow,deny\n";
			$conf=$conf."    Allow from all\n";
			$conf=$conf."</Directory>\n";
			$conf=$conf."Alias /css/ /usr/share/artica-groupware/$webservername/css/\n";
			$conf=$conf."<Directory \"/usr/share/artica-groupware/$webservername/css\">\n";
			$conf=$conf."    AllowOverride None\n";
			$conf=$conf."    Order allow,deny\n";
			$conf=$conf."    Allow from all\n";
			$conf=$conf."</Directory>\n";			
			$conf=$conf."\n";
			$conf=$conf."Alias /pipermail/ /var/lib/mailman/archives/public/\n";
			$conf=$conf."<Directory \"/var/lib/mailman/archives/public\">\n";
			$conf=$conf."    Options Indexes MultiViews FollowSymLinks\n";
			$conf=$conf."    AllowOverride None\n";
			$conf=$conf."    Order allow,deny\n";
			$conf=$conf."    Allow from all\n";
			$conf=$conf."</Directory>\n";
			$conf=$conf."\n";
			$conf=$conf."<IfModule mod_rewrite.c>\n";
			$conf=$conf."	RewriteEngine on\n";
			$conf=$conf."	# Redirect root access to mailman list\n";
			$conf=$conf."	RewriteRule ^$ /mailman/listinfo/$listname [R=permanent,L]\n";
			$conf=$conf."	RewriteRule ^/$ /mailman/listinfo/$listname [R=permanent,L]\n";
			$conf=$conf."	RewriteRule ^mailman.+?/style.css$ /css/style.css [R=permanent,L]\n";	
			$conf=$conf."	RedirectMatch ^/$ /listinfo\n";
			$conf=$conf."</IfModule>\n";	
			$conf=$conf."\n";
			$conf=$conf."CustomLog \"|/usr/sbin/rotatelogs /usr/local/apache-groupware/logs/$webservername 86400\" \"%h %l %u %t \\\"%r\\\" %>s %b \\\"%{Referer}i\\\" \\\"%{User-Agent}i\\\" %V\"\n";
			$conf=$conf."ErrorLog /usr/local/apache-groupware/logs/{$webservername}_err.log\n";
			$conf=$conf."</VirtualHost>\n";		
			}
			
		
			if($GLOBALS["OUTPUT"]){echo $conf;}
			return $conf;
}


	
	
function mailman_cgibin_path(){
	
			$conf=$conf."# Redirect to SSL if available\n";
			$conf=$conf."  <IfModule mod_ssl.c>\n";
			$conf=$conf."      RewriteCond %{HTTPS} !^on$ [NC]\n";
			$conf=$conf."      RewriteRule . https://%{HTTP_HOST}%{REQUEST_URI}  [L]\n";
			$conf=$conf."  </IfModule>\n";		
	
	if(is_file("/var/lib/mailman/cgi-bin/subscribe")){return "/var/lib/mailman/cgi-bin";}
	if(is_file("/usr/local/mailman/cgi-bin/subscribe")){return "/usr/local/mailman/cgi-bin";}
}
	
function OBM2_INSTALL($servername,$root,$hash=array()){
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";	
	if($root==null){events("Starting install OBM2 Unable to stat root dir");return false;}
	if(!is_dir("/opt/artica/install/sources/obm")){
		events("Starting install OBM2 Unable to stat /opt/artica/install/sources/obm");
		return false;
	}
	
	$sqlfiles=array(
			"create_obmdb_2.3.mysql.sql",
			"obmdb_prefs_values_2.3.sql",
			"obmdb_default_values_2.3.sql",
			"obmdb_test_values_2.3.sql",
			"data-fr/obmdb_nafcode_2.3.sql",
			"data-fr/obmdb_ref_2.3.sql",
			"data-en/obmdb_nafcode_2.3.sql",
			"data-en/obmdb_ref_2.3.sql");
	
	$user=$hash["wwwmysqluser"][0];
	$mysql_password=$hash[strtolower("WWWMysqlPassword")][0];
	
	$appli_user=$hash["wwwappliuser"];
	$appli_password=$hash["wwwapplipassword"];
	
	
	if($user==null){events("Starting install OBM2 Unable to stat Mysql username");return false;}
	if($mysql_password==null){events("Starting install OBM2 Unable to stat Mysql password");return false;}

	@mkdir($root,0755,true);
	
	$server_database=str_replace(".","_",$servername);
	$server_database=str_replace("-","_",$server_database);	
	$q=new mysql();
	if(!$q->DATABASE_EXISTS($server_database)){
		events("Starting install OBM2 sub-system mysql database $server_database...");
		$q->CREATE_DATABASE($server_database);
	
		if(!$q->DATABASE_EXISTS($server_database)){
			events("Starting install OBM2 unable to create MYSQL Database");
			return false;
		}
	}
	
		
	events("Starting install OBM2 installing source code in $root");
	shell_exec("/bin/cp -rf /opt/artica/install/sources/obm/* $root/");
	if($q->mysql_password<>null){
		$password=" --password=$q->mysql_password ";
	}else{
		events("Starting install OBM2 installing tables datas with null password");
	}
	$unix=new unix();
		//<$sql_file
		$cmd=$unix->find_program("mysql")." --port=$q->mysql_port --skip-column-names --database=$server_database --silent --xml ";
		$cmd=$cmd." --user=$q->mysql_admin$password";
		
		if(!OBM2_CheckObmTables($server_database)){
			while (list ($num, $filesql) = each ($sqlfiles) ){
				if(is_file("/opt/artica/install/sources/obm/scripts/2.3/$filesql")){
					events("installing $filesql SQL commands");
					shell_exec($cmd ." </opt/artica/install/sources/obm/scripts/2.3/$filesql");
				}
		}}

	$version=OBM2_VERSION($root);
	if($version==null){
		events("Starting install unable to stat version");
		return false;
	}
	events("Starting install OBM2 version $version");
	if(is_file("$root/scripts/2.3/updates/update-2.3.1-$version.mysql.sql")){
		events("Starting updating OBM2 version 2.3.1-$version");
		shell_exec($cmd ." <$root/scripts/2.3/updates/update-2.3.1-$version.mysql.sql");
	}else{
		events("Starting updating unable to stat $root/scripts/2.3/updates/update-2.3.1-$version.mysql.sql");
	}
		//scripts/2.3/updates/update-2.3.1-2.3.2.mysql.sql
		
	AddPrivileges($user,$mysql_password,$server_database);
	OBM2_INSTALL_SCRIPTS($root,$servername,$server_database,$user,$mysql_password);
}

function OBM2_INSTALL_SCRIPTS($root,$servername,$server_database,$mysql_user,$mysql_password){
$sock=new sockets();
$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");	
$ldap=new clladp();
$conf=$conf."<script language=\"php\">\n";
$conf=$conf."\$cgp_todo_nb = 5;\n";
$conf=$conf."\$conf_display_max_rows = 200;\n";
$conf=$conf."\$cgp_sql_star = true;\n";
$conf=$conf."\$ctu_sql_limit = true;\n";
$conf=$conf."\$cgp_mail_enabled = true;\n";
$conf=$conf."\$cgp_demo_enabled = false;\n";
$conf=$conf."\$cs_lifetime = 0;\n";
$conf=$conf."\$cgp_sess_db = false;\n";
$conf=$conf."\$password_encryption = 'PLAIN';\n";
$conf=$conf."\$caf_company_name = true;\n";
$conf=$conf."\$caf_town = true;\n";
$conf=$conf."\$csearch_advanced_default = false;\n";
$conf=$conf."\$cgp_mailing_default = true;\n";
$conf=$conf."\$ccalendar_public_groups = true;\n";
$conf=$conf."\$ccalendar_first_hour = 8;\n";
$conf=$conf."\$ccalendar_last_hour = 20;\n";
$conf=$conf."\$ccalendar_resource = true;\n";
$conf=$conf."\$ccalendar_send_ics = true;\n";
$conf=$conf."\$ccalendar_hour_fraction = 4;\n";
$conf=$conf."\$ccalendar_invocation_method = 'onDblClick';\n";
$conf=$conf."\$c_working_days = array(0,1,1,1,1,1,0);\n";
$conf=$conf."\$cimage_logo = 'linagora.jpg';\n";
$conf=$conf."\$cgroup_private_default = true;\n";
$conf=$conf."\$cdefault_tax = array ('TVA 19,6' => 1.196, 'TVA 5,5' => 1.055, 'Pas de TVA' => 1);\n";
$conf=$conf."\$cgp_default_right = array (\n";
$conf=$conf."  'resource' => array (\n";
$conf=$conf."      'public' => array(\n";
$conf=$conf."        'access' => 1,\n";
$conf=$conf."        'read' => 0,\n";
$conf=$conf."        'write' => 0,\n";
$conf=$conf."        'admin' => 0\n";
$conf=$conf."      )\n";
$conf=$conf."    ),\n";
$conf=$conf."  'contact' => array (\n";
$conf=$conf."      'public' => array(\n";
$conf=$conf."        'access' => 1,\n";
$conf=$conf."        'read' => 1,\n";
$conf=$conf."        'write' => 0,\n";
$conf=$conf."        'admin' => 0\n";
$conf=$conf."      )\n";
$conf=$conf."    ),\n";
$conf=$conf."  'mailshare' => array (\n";
$conf=$conf."      'public' => array(\n";
$conf=$conf."        'access' => 1,\n";
$conf=$conf."        'read' => 0,\n";
$conf=$conf."        'write' => 0,\n";
$conf=$conf."        'admin' => 0\n";
$conf=$conf."      )\n";
$conf=$conf."    ),\n";
$conf=$conf."  'mailbox' => array (\n";
$conf=$conf."      'public' => array(\n";
$conf=$conf."        'access' => 1,\n";
$conf=$conf."        'read' => 0,\n";
$conf=$conf."        'write' => 0,\n";
$conf=$conf."        'admin' => 0\n";
$conf=$conf."      )\n";
$conf=$conf."    ),\n";
$conf=$conf."  'calendar' => array (\n";
$conf=$conf."      'public' => array(\n";
$conf=$conf."        'access' => 1,\n";
$conf=$conf."        'read' => 1,\n";
$conf=$conf."        'write' => 0,\n";
$conf=$conf."        'admin' => 0\n";
$conf=$conf."      )\n";
$conf=$conf."    )\n";
$conf=$conf."  );\n";
$conf=$conf."\n";
$conf=$conf."\$profiles['admin'] = array (\n";
$conf=$conf."  'section' => array (\n";
$conf=$conf."    'default' => 1\n";
$conf=$conf."  ),\n";
$conf=$conf."  'module' => array (\n";
$conf=$conf."    'default' => \$perm_admin,\n";;
$conf=$conf."    'domain' => 0),\n";
$conf=$conf."  'properties' => array (\n";
$conf=$conf."    'admin_realm' => array ('user', 'delegation', 'domain')\n";
$conf=$conf."    ),\n";
$conf=$conf."  'level' => 1,\n";
$conf=$conf."  'level_managepeers' => 1,\n";
$conf=$conf."  'access_restriction' => 'ALLOW_ALL'\n";
$conf=$conf."\n";
$conf=$conf.");\n";
$conf=$conf."\$cgp_show['section']['com'] = false;\n";
$conf=$conf."\$cgp_show['section']['prod'] = false;\n";
$conf=$conf."\$cgp_show['section']['compta'] = false;\n";
$conf=$conf."\$cgp_show['module']['company'] = false;\n";
$conf=$conf."\$cgp_show['module']['lead'] = false;\n";
$conf=$conf."\$cgp_show['module']['deal'] = false;\n";
$conf=$conf."\$cgp_show['module']['cv'] = false;\n";
$conf=$conf."\$cgp_show['module']['publication'] = false;\n";
$conf=$conf."\$cgp_show['module']['statistic'] = false;\n";
$conf=$conf."\$cgp_show['module']['time'] = false;\n";
$conf=$conf."\$cgp_show['module']['project'] = false;\n";
$conf=$conf."\$cgp_show['module']['contract'] = false;\n";
$conf=$conf."\$cgp_show['module']['incident'] = false;\n";
$conf=$conf."\$cgp_show['module']['invoice'] = false;\n";
$conf=$conf."\$cgp_show['module']['payment'] = false;\n";
$conf=$conf."\$cgp_show['module']['account'] = false;\n";
$conf=$conf."\n";
$conf=$conf."</script>\n";
@file_put_contents("$root/conf/obm_conf.inc",$conf);
$conf=null;
$conf=$conf."; OBM system configuration file\n";
$conf=$conf."; Copy it to obm_conf.ini (without \".sample\")\n";
$conf=$conf."; Set here Common global parameteres\n";
$conf=$conf."; \n";
$conf=$conf."; Parameters are set like : key = value\n";
$conf=$conf."; Comments are lines beginning with \";\"\n";
$conf=$conf."; OBM Automate need the [global] for the perl section (beware : php is permissive)\n";
$conf=$conf.";\n";
$conf=$conf."[global]\n";
$conf=$conf."; General information\n";
$conf=$conf."title = $servername\n";
$conf=$conf.";\n";
$conf=$conf."; example : for https://extranet.aliasource.fr/obm/ \n";
$conf=$conf."; external-url = extranet.aliasource.fr\n";
$conf=$conf."; external-protocol = https\n";
$conf=$conf."; obm-prefix = /obm/\n";
$conf=$conf."external-url = http://$servername:$ApacheGroupWarePort\n";
$conf=$conf."external-protocol = http\n";
$conf=$conf."obm-prefix = /\n";
$conf=$conf."\n";
$conf=$conf."; Database infos\n";
$conf=$conf."host = 127.0.0.1\n";
$conf=$conf."dbtype = MYSQL\n";
$conf=$conf."db = $server_database\n";
$conf=$conf."user = $mysql_user\n";
$conf=$conf."; Password must be enclosed with \"\n";
$conf=$conf."password = \"$mysql_password\"\n";
$conf=$conf."\n";
$conf=$conf."; Default language\n";
$conf=$conf."lang = fr\n";
$conf=$conf."\n";
$conf=$conf."; Enabled OBM module\n";
$conf=$conf."obm-ldap = false\n";
$conf=$conf."obm-mail = true\n";
$conf=$conf."obm-samba = false\n";
$conf=$conf."obm-web = false\n";
$conf=$conf."obm-contact = true\n";
$conf=$conf."\n";
$conf=$conf."; singleNameSpace mode allow only one domain\n";
$conf=$conf."; login are 'login' and not 'login@domain'\n";
$conf=$conf."; Going multi-domain from mono domain needs system work (ldap, cyrus,...)\n";
$conf=$conf."; Multi-domain disabled by default\n";
$conf=$conf."singleNameSpace = false\n";
$conf=$conf."\n";
$conf=$conf."; backupRoot is the directory used to store backup data\n";
$conf=$conf."backupRoot = \"/var/lib/obm/backup\"\n";
$conf=$conf."\n";
$conf=$conf."; documentRoot is root of document repository\n";
$conf=$conf."documentRoot=\"/var/lib/obm/documents\"\n";
$conf=$conf."documentDefaultPath=\"/\"\n";
$conf=$conf."\n";
$conf=$conf."; LDAP Authentification for obm-sync & ui\n";
$conf=$conf."; ldap authentication server (specify :port if different than default)\n";
$conf=$conf."auth-ldap-server = ldap://localhost\n";
$conf=$conf."; base dn for search (search are performed with scope sub, of not specified, use the server default)\n";
$conf=$conf.";auth-ldap-basedn = \"$ldap->suffix\"\n";
$conf=$conf."; filter used for the search part of the authentication\n";
$conf=$conf."; See http://www.faqs.org/rfcs/rfc2254.html for filter syntax\n";
$conf=$conf.";  - %u will be replace with user login\n";
$conf=$conf.";  - %d will be replace with user OBM domain name\n";
$conf=$conf."; ie: toto@domain.foo : %u=toto, %d=domain.foo\n";
$conf=$conf."; auth-ldap-filter = \"(&(uid=%u)(obmDomain=%d))\"\n";
$conf=$conf."\n";
$conf=$conf."[automate]\n";
$conf=$conf."; Automate specific parameters\n";
$conf=$conf.";\n";
$conf=$conf."; Log level\n";
$conf=$conf."logLevel = 2\n";
$conf=$conf.";\n";
$conf=$conf."; LDAP server address\n";
$conf=$conf.";ldapServer = ldap://localhost\n";
$conf=$conf.";\n";
$conf=$conf."; LDAP use TLS [none|may|encrypt]\n";
$conf=$conf."ldapTls = may\n";
$conf=$conf.";\n";
$conf=$conf."; LDAP Root\n";
$conf=$conf."; Exemple : 'aliasource,local' means that the root DN is: 'dc=aliasource,dc=local' \n";
$conf=$conf."ldapRoot = local\n";
$conf=$conf."\n";
$conf=$conf."; Enable Cyrus partition support\n";
$conf=$conf."; if cyrusPartition is enable, a dedicated Cyrus partition is created for each OBM domain\n";
$conf=$conf."; Going cyrusPartition enabled from cyrusPartition disabled needs system work\n";
$conf=$conf."cyrusPartition = false\n";
$conf=$conf.";\n";
$conf=$conf."; ldapAllMainMailAddress :\n";
$conf=$conf.";    false : publish user mail address only if mail right is enable - default\n";
$conf=$conf.";    true : publish main user mail address, even if mail right is disable\n";
$conf=$conf."ldapAllMainMailAddress = true\n";
$conf=$conf.";\n";
$conf=$conf."; userMailboxDefaultFolders are IMAP folders who are automaticaly created\n";
$conf=$conf."; at user creation ( must be enclosed with \" and in IMAP UTF-7 modified encoding)\n";
$conf=$conf."; Small convertion table\n";
$conf=$conf."; é -> &AOk-\n";
$conf=$conf."; è -> &AOg-\n";
$conf=$conf."; à -> &AOA-\n";
$conf=$conf."; & -> &\n";
$conf=$conf."; Example : userMailboxDefaultFolders = \"Envoy&AOk-s,Corbeille,Brouillons,El&AOk-ments ind&AOk-sirables\"\n";
$conf=$conf."userMailboxDefaultFolders = \"\"\n";
$conf=$conf.";\n";
$conf=$conf."; shareMailboxDefaultFolders are IMAP folders who are automaticaly created\n";
$conf=$conf."; at share creation ( must be enclosed with \" and in IMAP UTF-7 modified\n";
$conf=$conf."; encoding)\n";
$conf=$conf."shareMailboxDefaultFolders = \"\"\n";
$conf=$conf.";\n";
$conf=$conf."; oldSidMapping mode is for compatibility with Aliamin and old install\n";
$conf=$conf."; Modifying this on a running system need Samba domain work (re-register host,\n";
$conf=$conf."; ACL...) \n";
$conf=$conf."; For new one, leave this to 'false'\n";
$conf=$conf."oldSidMapping = false\n";
$conf=$conf.";\n";
$conf=$conf.";\n";
$conf=$conf."; Settings use by OBM Thunderbird autoconf\n";
$conf=$conf."[autoconf]\n";
$conf=$conf.";\n";
$conf=$conf."ldapHostname = ldap.aliacom.local\n";
$conf=$conf."ldapHost = 127.0.0.1\n";
$conf=$conf."ldapPort = 389\n";
$conf=$conf."ldapSearchBase = \"dc=local\"\n";
$conf=$conf."ldapAtts = cn,mail,mailAlias,mailBox,obmDomain,uid\n";
$conf=$conf."ldapFilter = \"mail\"\n";
$conf=$conf."configXml = /usr/lib/obm-autoconf/config.xml\n";
$conf=$conf.";\n";
$conf=$conf."; EOF";
@file_put_contents("$root/conf/obm_conf.ini",$conf);	
}

function OBM2_CheckObmTables($database){
	
$tables[]="Account";
$tables[]="AccountEntity";
$tables[]="ActiveUserObm";
$tables[]="Address";
$tables[]="AddressBook";
$tables[]="AddressbookEntity";
$tables[]="CalendarEntity";
$tables[]="Campaign";
$tables[]="CampaignDisabledEntity";
$tables[]="CampaignEntity";
$tables[]="CampaignMailContent";
$tables[]="CampaignMailTarget";
$tables[]="CampaignPushTarget";
$tables[]="CampaignTarget";
$tables[]="Category";
$tables[]="CategoryLink";
$tables[]="Company";
$tables[]="CompanyActivity";
$tables[]="CompanyEntity";
$tables[]="CompanyNafCode";
$tables[]="CompanyType";
$tables[]="Contact";
$tables[]="ContactEntity";
$tables[]="ContactFunction";
$tables[]="ContactList";
$tables[]="Contract";
$tables[]="ContractEntity";
$tables[]="ContractPriority";
$tables[]="ContractStatus";
$tables[]="ContractType";
$tables[]="Country";
$tables[]="CV";
$tables[]="CvEntity";
$tables[]="DataSource";
$tables[]="Deal";
$tables[]="DealCompany";
$tables[]="DealCompanyRole";
$tables[]="DealEntity";
$tables[]="DealStatus";
$tables[]="DealType";
$tables[]="DefaultOdtTemplate";
$tables[]="Deleted";
$tables[]="DeletedAddressbook";
$tables[]="DeletedContact";
$tables[]="DeletedEvent";
$tables[]="DeletedUser";
$tables[]="DisplayPref";
$tables[]="Document";
$tables[]="DocumentEntity";
$tables[]="DocumentLink";
$tables[]="DocumentMimeType";
$tables[]="Domain";
$tables[]="DomainEntity";
$tables[]="DomainProperty";
$tables[]="DomainPropertyValue";
$tables[]="Email";
$tables[]="Entity";
$tables[]="EntityRight";
$tables[]="Event";
$tables[]="EventAlert";
$tables[]="EventCategory1";
$tables[]="EventEntity";
$tables[]="EventException";
$tables[]="EventLink";
$tables[]="EventTag";
$tables[]="EventTemplate";
$tables[]="GroupEntity";
$tables[]="GroupGroup";
$tables[]="Host";
$tables[]="HostEntity";
$tables[]="IM";
$tables[]="Import";
$tables[]="ImportEntity";
$tables[]="Incident";
$tables[]="IncidentEntity";
$tables[]="IncidentPriority";
$tables[]="IncidentResolutionType";
$tables[]="IncidentStatus";
$tables[]="Invoice";
$tables[]="InvoiceEntity";
$tables[]="Kind";
$tables[]="Lead";
$tables[]="LeadEntity";
$tables[]="LeadSource";
$tables[]="LeadStatus";
$tables[]="List";
$tables[]="ListEntity";
$tables[]="MailboxEntity";
$tables[]="MailShare";
$tables[]="MailshareEntity";
$tables[]="ObmBookmark";
$tables[]="ObmbookmarkEntity";
$tables[]="ObmBookmarkProperty";
$tables[]="ObmInfo";
$tables[]="ObmSession";
$tables[]="of_usergroup";
$tables[]="OGroup";
$tables[]="OgroupEntity";
$tables[]="OGroupLink";
$tables[]="opush_device";
$tables[]="opush_folder_mapping";
$tables[]="opush_sec_policy";
$tables[]="opush_sync_mail";
$tables[]="opush_sync_perms";
$tables[]="opush_sync_state";
$tables[]="OrganizationalChart";
$tables[]="OrganizationalchartEntity";
$tables[]="ParentDeal";
$tables[]="ParentdealEntity";
$tables[]="Payment";
$tables[]="PaymentEntity";
$tables[]="PaymentInvoice";
$tables[]="PaymentKind";
$tables[]="Phone";
$tables[]="PlannedTask";
$tables[]="Profile";
$tables[]="ProfileEntity";
$tables[]="ProfileModule";
$tables[]="ProfileProperty";
$tables[]="ProfileSection";
$tables[]="Project";
$tables[]="ProjectClosing";
$tables[]="ProjectCV";
$tables[]="ProjectEntity";
$tables[]="ProjectRefTask";
$tables[]="ProjectTask";
$tables[]="ProjectUser";
$tables[]="Publication";
$tables[]="PublicationEntity";
$tables[]="PublicationType";
$tables[]="P_Domain";
$tables[]="P_DomainEntity";
$tables[]="P_EntityRight";
$tables[]="P_GroupEntity";
$tables[]="P_Host";
$tables[]="P_HostEntity";
$tables[]="P_MailboxEntity";
$tables[]="P_MailShare";
$tables[]="P_MailshareEntity";
$tables[]="P_of_usergroup";
$tables[]="P_Service";
$tables[]="P_ServiceProperty";
$tables[]="P_UGroup";
$tables[]="P_UserEntity";
$tables[]="P_UserObm";
$tables[]="Region";
$tables[]="Resource";
$tables[]="ResourceEntity";
$tables[]="ResourceGroup";
$tables[]="ResourcegroupEntity";
$tables[]="ResourceItem";
$tables[]="ResourceType";
$tables[]="RGroup";
$tables[]="Service";
$tables[]="ServiceProperty";
$tables[]="SSOTicket";
$tables[]="Stats";
$tables[]="Subscription";
$tables[]="SubscriptionEntity";
$tables[]="SubscriptionReception";
$tables[]="SyncedAddressbook";
$tables[]="TaskEvent";
$tables[]="TaskType";
$tables[]="TaskTypeGroup";
$tables[]="TimeTask";
$tables[]="UGroup";
$tables[]="Updated";
$tables[]="Updatedlinks";
$tables[]="UserEntity";
$tables[]="UserObm";
$tables[]="UserObmGroup";
$tables[]="UserObmPref";
$tables[]="UserObm_SessionLog";
$tables[]="UserSystem";
$tables[]="Website";
$q=new mysql();
while (list ($num, $table) = each ($tables) ){
	if(!$q->TABLE_EXISTS($table,$database)){
		events("Starting install OBM2 $table table does not exists");
		return false;	
	}
}
return true;
}

function OBM2_VERSION($root){
	if(!is_file("$root/obminclude/global.inc")){
		events("Starting install OBM2 $root/obminclude/global.inc file does not exists");
	}
	
	$tbl=explode("\n",@file_get_contents("$root/obminclude/global.inc"));
	while (list ($num, $line) = each ($tbl) ){
		if(preg_match("#obm_version.+?([0-9\.]+)#",$line,$re)){
			return trim($re[1]);
		}
	}
	
	events("Starting install OBM2 unable to find verison in $root/obminclude/global.inc");
}

function OPENGOO_CHECK_TABLES($database){
$tables[]="og_administration_tools";
$tables[]="og_application_logs";
$tables[]="og_billing_categories";
$tables[]="og_comments";
$tables[]="og_companies";
$tables[]="og_config_categories";
$tables[]="og_config_options";
$tables[]="og_contacts";
$tables[]="og_contact_im_values";
$tables[]="og_cron_events";
$tables[]="og_custom_properties";
$tables[]="og_custom_properties_by_co_type";
$tables[]="og_custom_property_values";
$tables[]="og_event_invitations";
$tables[]="og_file_repo";
$tables[]="og_file_repo_attributes";
$tables[]="og_file_types";
$tables[]="og_groups";
$tables[]="og_group_users";
$tables[]="og_gs_books";
$tables[]="og_gs_borderstyles";
$tables[]="og_gs_cells";
$tables[]="og_gs_columns";
$tables[]="og_gs_fonts";
$tables[]="og_gs_fontstyles";
$tables[]="og_gs_layoutstyles";
$tables[]="og_gs_mergedcells";
$tables[]="og_gs_rows";
$tables[]="og_gs_sheets";
$tables[]="og_gs_userbooks";
$tables[]="og_gs_users";
$tables[]="og_guistate";
$tables[]="og_im_types";
$tables[]="og_linked_objects";
$tables[]="og_mail_accounts";
$tables[]="og_mail_account_imap_folder";
$tables[]="og_mail_account_users";
$tables[]="og_mail_contents";
$tables[]="og_mail_conversations";
$tables[]="og_object_handins";
$tables[]="og_object_properties";
$tables[]="og_object_reminders";
$tables[]="og_object_reminder_types";
$tables[]="og_object_subscriptions";
$tables[]="og_object_user_permissions";
$tables[]="og_projects";
$tables[]="og_project_charts";
$tables[]="og_project_chart_params";
$tables[]="og_project_companies";
$tables[]="og_project_contacts";
$tables[]="og_project_co_types";
$tables[]="og_project_events";
$tables[]="og_project_files";
$tables[]="og_project_file_revisions";
$tables[]="og_project_forms";
$tables[]="og_project_messages";
$tables[]="og_project_milestones";
$tables[]="og_project_tasks";
$tables[]="og_project_users";
$tables[]="og_project_webpages";
$tables[]="og_queued_emails";
$tables[]="og_read_objects";
$tables[]="og_reports";
$tables[]="og_report_columns";
$tables[]="og_report_conditions";
$tables[]="og_searchable_objects";
$tables[]="og_shared_objects";
$tables[]="og_tags";
$tables[]="og_templates";
$tables[]="og_template_objects";
$tables[]="og_template_object_properties";
$tables[]="og_template_parameters";
$tables[]="og_timeslots";
$tables[]="og_users";
$tables[]="og_user_passwords";
$tables[]="og_user_ws_config_categories";
$tables[]="og_user_ws_config_options";
$tables[]="og_user_ws_config_option_values";
$tables[]="og_workspace_billings";
$tables[]="og_workspace_objects";
$tables[]="og_workspace_templates";	
$q=new mysql();
while (list ($num, $table) = each ($tables) ){
	if(!$q->TABLE_EXISTS($table,$database)){
		events("Starting install OpenGoo $table table does not exists");
		return false;	
	}
}
return true;
}

function vhosts_BuildCertificate($hostname){
	$unix=new unix();
	$unix->vhosts_BuildCertificate($hostname);
	
}

function DRUPAL_INSTALL($apacheservername,$root,$hash=array()){
	$ldap=new clladp();
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";	
	$server_database=str_replace(".","_",$apacheservername);
	$server_database=str_replace("-","_",$server_database);	
	events("Starting install drupal table prefix={$server_database}_...");
	$sock=new sockets();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	$prefix_web="http";
	if($hash["wwwsslmode"][0]=='TRUE'){$prefix_web="https";}
	$dn=$hash["dn"];
	
	if(preg_match("#ou=www,ou=(.+?),#",$dn,$re)){$ou=$re[1];}
	$q=new mysql();	
	$conf[]="<?php";
	$conf[]="\$db_url = 'mysql://$q->mysql_admin:$q->mysql_password@$q->mysql_server:$q->mysql_port/drupal';";
	$conf[]="\$db_prefix = '{$server_database}_';";
	$conf[]="\$update_free_access = FALSE;";
	$conf[]="\$base_url = '$prefix_web://$apacheservername:$ApacheGroupWarePort';  // NO trailing slash!";
	$conf[]="ini_set('arg_separator.output',     '&amp;');";
	$conf[]="ini_set('magic_quotes_runtime',     0);";
	$conf[]="ini_set('magic_quotes_sybase',      0);";
	$conf[]="ini_set('session.cache_expire',     200000);";
	$conf[]="ini_set('session.cache_limiter',    'none');";
	$conf[]="ini_set('session.cookie_lifetime',  2000000);";
	$conf[]="ini_set('session.gc_maxlifetime',   200000);";
	$conf[]="ini_set('session.save_handler',     'user');";
	$conf[]="ini_set('session.use_cookies',      1);";
	$conf[]="ini_set('session.use_only_cookies', 1);";
	$conf[]="ini_set('session.use_trans_sid',    0);";
	$conf[]="ini_set('url_rewriter.tags',        '');";
	$conf[]="?>";	
	
	@mkdir("/usr/share/drupal/sites/$apacheservername/files",0755,true);
	
	@file_put_contents("/usr/share/drupal/sites/$apacheservername/settings.php",@implode("\n",$conf));
	
	
}

function WEBDAV_USERS(){}



?>