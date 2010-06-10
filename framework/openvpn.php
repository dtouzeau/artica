<?php
include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");
include_once(dirname(__FILE__)."/class.postfix.inc");

if(isset($_GET["build-vpn-user"])){BuildWindowsClient();exit;}
if(isset($_GET["restart-clients"])){RestartClients();exit;}
if(isset($_GET["restart-clients-tenir"])){RestartClientsTenir();exit;}
if(isset($_GET["is-client-running"])){vpn_client_running();exit;}


function RestartClients(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.openvpn.php --client-restart");		
	}
	
function RestartClientsTenir(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.openvpn.php --client-restart",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
	
}
	
function vpn_client_running(){
	$id=$_GET["is-client-running"];
	$pid=trim(@file_get_contents("/etc/artica-postfix/openvpn/clients/$id/pid"));
	$unix=new unix();
	writelogs_framework("/etc/artica-postfix/openvpn/clients/$id/pid -> $pid",__FUNCTION__,__FILE__,__LINE__);
	
	if($unix->process_exists($pid)){
		echo "<articadatascgi>TRUE</articadatascgi>";
		return;
	}
	writelogs_framework("$id: pid $pid",__FUNCTION__,__FILE__,__LINE__);
	
	exec($unix->find_program("pgrep") ." -l -f \"openvpn.+?clients\/2\/settings.ovpn\" 1>&1",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#^([0-9]+)\s+.*openvpn#",$ligne)){
			writelogs_framework("pid= preg_match= {$re[1]}",__FUNCTION__,__FILE__,__LINE__);
			echo "<articadatascgi>TRUE</articadatascgi>";
			return;
		}
	}
	writelogs_framework("$pid NOT RUNNING",__FUNCTION__,__FILE__,__LINE__);
}	


function BuildWindowsClient(){
	$commonname=$_GET["build-vpn-user"];
	$basepath=$_GET["basepath"];
	$unix=new unix();
	@mkdir($basepath,0755,true);
	$workingDir="/etc/artica-postfix/openvpn/$commonname";
	@mkdir($workingDir);
	if(!is_file('/usr/bin/zip')){
		echo "<articadatascgi>ERROR: unable to stat \"zip\", please advise your Administrator</articadatascgi>";
		exit;
	}
	
	if(!is_file("/etc/artica-postfix/settings/Daemons/$commonname.ovpn")){
		echo "<articadatascgi>ERROR: unable to stat \"$commonname.ovpn\", please advise your Administrator</articadatascgi>";
		exit;
	}
	
	
	$filesize=filesize("/etc/artica-postfix/settings/Daemons/$commonname.ovpn");
	if($filesize==0){
		echo "<articadatascgi>ERROR: corrupted \"$commonname.ovpn\" 0 bytes, please advise your Administrator</articadatascgi>";
		exit;
	}	
	
	
	
	
	
	echo "<articadatascgi>";
	echo "$commonname.ovpn: ". filesize("/etc/artica-postfix/settings/Daemons/$commonname.ovpn")." bytes length\n";
	
	
	$password=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/OpenVpnPasswordCert"));
	if($password==null){$password="MyKey";}
	
	$zipfile=$basepath."/ressources/logs/$commonname.zip";
	@mkdir("$basepath/ressources/logs",0755,true);
	
	if(!ChangeCommonName($commonname)){exit;}
	if(is_file($zipfile)){@unlink($zipfile);}
       
    chdir('/etc/artica-postfix/openvpn');
    $filetemp=$unix->FILE_TEMP();
    shell_exec("source ./vars");   
    copy("/etc/artica-postfix/openvpn/keys/allca.crt","$workingDir/ca.crt");
    copy("/etc/artica-postfix/settings/Daemons/$commonname.ovpn","$workingDir/$commonname.ovpn"); 
    @unlink("/etc/artica-postfix/openvpn/$commonname.ovpn");
    @unlink("/etc/artica-postfix/openvpn/keys/index.txt");
    shell_exec("/bin/touch /etc/artica-postfix/openvpn/keys/index.txt");
    
    
    
    $cmd="openssl req -batch -days 3650 -nodes -new -newkey rsa:1024 -keyout \"$workingDir/$commonname.key\" -out \"$workingDir/$commonname.csr\" -config \"/etc/artica-postfix/openvpn/openssl.cnf\"";   
    $cmd="openssl req -nodes -new -keyout \"$workingDir/$commonname.key\" -out \"$workingDir/$commonname.csr\" -batch -config /etc/artica-postfix/openvpn/openssl.cnf";
    
    echo substr($cmd,0,60)."...\n";
	shell_exec("$cmd >$filetemp 2>&1");       
	echo @file_get_contents($filetemp);
	
	
	$cmd="openssl ca -batch -days 3650 -out \"$workingDir/$commonname.crt\" -in \"$workingDir/$commonname.csr\" -md sha1 -config \"/etc/artica-postfix/openvpn/openssl.cnf\"";
	$cmd="openssl ca -keyfile /etc/artica-postfix/openvpn/keys/openvpn-ca.key -cert /etc/artica-postfix/openvpn/keys/openvpn-ca.crt";
	$cmd=$cmd." -out \"$workingDir/$commonname.crt\" -in \"$workingDir/$commonname.csr\" -batch -config /etc/artica-postfix/openvpn/openssl.cnf -passin pass:$password";
	
	echo substr($cmd,0,60)."...\n";
	shell_exec("$cmd >$filetemp 2>&1");   
	echo @file_get_contents($filetemp);
	  $mycurrentdir=getcwd();
	  chdir($workingDir);
      @file_put_contents("$workingDir/password",$password);
	  
	  $cmd="/usr/bin/zip $zipfile";
      
      $cmd=$cmd. " $commonname.crt $commonname.csr $commonname.key $commonname.ovpn ca.crt password >$filetemp 2>&1";;
      shell_exec($cmd);
      chdir($mycurrentdir);
      echo @file_get_contents($filetemp);
      
   @chmod($zipfile,0755);
   @unlink($filetemp);
   @unlink("$workingDir/ca.crt");
   @unlink("$workingDir/$commonname.crt");
   @unlink("$workingDir/$commonname.csr");
   @unlink("$workingDir/$commonname.key");
   @unlink("$workingDir/$commonname.ovpn");
   @unlink("$workingDir/password");		
    echo "----------------------------------\n";
    echo "{success} !!!\n";
    echo "----------------------------------\n";
	echo "</articadatascgi>";
}


function ChangeCommonName($commonname){

if(!is_file("/etc/artica-postfix/openvpn/openssl.cnf")){
	echo "<articadatascgi>ERROR: Unable to stat /etc/artica-postfix/openvpn/openssl.cnf</articadatascgi>";
	return false;
}
	
$tbl=explode("\n",@file_get_contents("/etc/artica-postfix/openvpn/openssl.cnf"));
while (list ($num, $ligne) = each ($tbl) ){
	if(preg_match("#^commonName_default#",$ligne)){
		$tbl[$num]="commonName_default=\t$commonname";
	}
}

@file_put_contents("/etc/artica-postfix/openvpn/openssl.cnf",implode("\n",$tbl));
return true;
}
?>