<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.gluster.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(is_array($argv)){
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
}

if($GLOBALS["VERBOSE"]){echo "Debug mode TRUE for {$argv[1]}\n";}

if($argv[1]=='--master'){NotifyMaster();exit;}
if($argv[1]=='--conf'){BuildLocalConf();exit;}
if($argv[1]=='--build-replicator'){build_replicator();die();}
if($argv[1]=='--notify-client'){NotifySpecificClient($argv[2]);die();}
if($argv[1]=='--notify-all-clients'){NotifyAllClients();die();}
if($argv[1]=='--cluster-restart-notify'){client_restart_notify();die();}
if($argv[1]=='--notify-server'){NotifyStatus();die();}
if($argv[1]=='--cyrus-id'){tests_cyrusid();die();}

	if($argv[1]=='--notify-server'){
		if($GLOBALS["VERBOSE"]){echo "Debug -> NotifyStatus()\n";}
		NotifyStatus();
		die();
	}

if(isset($_POST["notify"])){ReceiveParams();exit;}
if(isset($_POST["NTFY_STATUS"])){server_receive_status();exit;}
if(isset($_POST["CLIENT_NTFY_SRV_INFO"])){server_request_client_info();die();}


if(isset($_POST)){
	while (list ($num, $ligne) = each ($_POST) ){
		writelogs("unable to understand $num= $ligne",__FUNCTION__,__FILE__,__LINE__);
	}
	die();
}


writelogs("no posts notify clients by default...",__FUNCTION__,__FILE__,__LINE__);
NotifyClients();
die();

function NotifySpecificClient($server){
		$ini=new Bs_IniHandler();
		$ini->_params["PARAMS"]["notify"]=1;
		$ini->_params["PARAMS"]["error"]=0;
		$ini->_params["PARAMS"]["error_text"]="{scheduled}";
		$ini->_params["PARAMS"]["name"]="$server";
		$ini->saveFile("/etc/artica-cluster/notify-$server");	
		NotifyClients();
	}



function NotifyClients(){
	$unix=new unix();
	$files=$unix->DirFiles("/etc/artica-cluster");
	
	
	while (list ($num, $ligne) = each ($files) ){
		if($ligne==null){continue;}
		if(preg_match("#notify-(.+)#",$ligne,$re)){
			$server=$re[1];
			writelogs("Notify $server",__FUNCTION__,__FILE__,__LINE__);
			NotifyClient($server);
		}
	
	}

}

function NotifyAllClients(){
	$gl=new gluster();
	if(is_array($gl->clients)){
		while (list ($num, $ligne) = each ($gl->clients) ){
			NotifyClient($num);
		}
	}
	
}


function NotifyClient($server){
		
	$file="/etc/artica-cluster/notify-$server";
	$ini=new Bs_IniHandler();
	$ini->loadFile($file);
	$ini->_params["PARAMS"]["name"]="$server";
	$sock=new sockets();
	$cyrus_id=$sock->getFrameWork("cmd.php?idofUser=cyrus");
	$ini->_params["PARAMS"]["cyrus_id"]=$cyrus_id;
	
	
	if(!function_exists("curl_init")){
		$ini->_params["PARAMS"]["notify"]=0;
		$ini->_params["PARAMS"]["error"]=1;
		$ini->_params["PARAMS"]["error_text"]="{error_php_curl}";
		$ini->_params["PARAMS"]["name"]="$server";
		$ini->_params["PARAMS"]["cyrus_id"]=$cyrus_id;
		$ini->saveFile($file);
		return null;
	}
	
	while (list ($num, $ligne) = each ($ini->_params["PARAMS"])){
		$curlPost .='&'.$num.'=' . urlencode($ligne);
	}
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://$server:9000/exec.gluster.php");
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
	
	$data = curl_exec($ch);
	$error=curl_errno($ch);
	if($error>0){
	writelogs("Connect to $server error $error",__FUNCTION__,__FILE__,__LINE__); 
	}

switch ($error) {
	case 6:
		$ini->_params["PARAMS"]["notify"]=0;
		$ini->_params["PARAMS"]["error"]=1;
		$ini->_params["PARAMS"]["error_text"]="{error_curl_resolve}";
		$ini->saveFile($file);
		curl_close($ch);
		return null;
	break;
	
	default:
		;
	break;
}
	if(curl_errno($ch)==false){
		if(preg_match("#404 Not Found#is",$data)){
			writelogs("Connect to $server error 404 Not Found",__FUNCTION__,__FILE__,__LINE__);
			$ini->_params["PARAMS"]["notify"]=0;
			$ini->_params["PARAMS"]["error"]=1;
			$ini->_params["PARAMS"]["notified"]=0;
			$ini->_params["PARAMS"]["error_text"]="{error_wrong_artica_version}";
			$ini->saveFile($file);
			curl_close($ch);
			return null;
		}
		
		if(preg_match("#GLUSTER_NOT_INSTALLED#is",$data)){
				writelogs("Connect to $server error GLUSTER_NOT_INSTALLED",__FUNCTION__,__FILE__,__LINE__);
				$ini->_params["PARAMS"]["notify"]=0;
				$ini->_params["PARAMS"]["error"]=1;
				$ini->_params["PARAMS"]["notified"]=0;
				$ini->_params["PARAMS"]["error_text"]="{error_gluster_not_installed}";
				$ini->saveFile($file);
				curl_close($ch);
				return null;	
		}
		if(preg_match("#CURL_NOT_INSTALLED#is",$data)){
				writelogs("Connect to $server error CURL_NOT_INSTALLED",__FUNCTION__,__FILE__,__LINE__);
				$ini->_params["PARAMS"]["notify"]=0;
				$ini->_params["PARAMS"]["error"]=1;
				$ini->_params["PARAMS"]["notified"]=0;
				$ini->_params["PARAMS"]["error_text"]="{error_php_curl}";
				$ini->saveFile($file);
				curl_close($ch);
				return null;	
		}		
	
	
	}

if(preg_match("#GLUSTER_OK#is",$data)){
		writelogs("Connect to $server success",__FUNCTION__,__FILE__,__LINE__);
		$ini->_params["PARAMS"]["notify"]=0;
		$ini->_params["PARAMS"]["error"]=0;
		$ini->_params["PARAMS"]["notified"]=1;
		$ini->_params["PARAMS"]["error_text"]="{success}";
		writelogs("Set this server has notified",__FUNCTION__,__FILE__,__LINE__);
		$ini->saveFile($file);
		curl_close($ch);
		return null;	
}	

//echo $data;



	
}

function client_restart_notify(){
	$master=@file_get_contents("/etc/artica-cluster/master");
	
	
	$gl=new gluster();
	$myname=@file_get_contents("/etc/artica-cluster/local.name");
	
	writelogs("MASTER=\"$master\"; me=\"$myname\"",__FUNCTION__,__FILE__,__LINE__);
	
	if($myname<>$master){
	if(is_array($gl->clients)){
		while (list ($num, $ligne) = each ($gl->clients) ){
			writelogs("Deleting clusters-$num",__FUNCTION__,__FILE__,__LINE__);
			@unlink("/etc/artica-cluster/clusters-$num");
		}
		}
	}
	writelogs("Notify master",__FUNCTION__,__FILE__,__LINE__);
	NotifyStatus();	
	BuildLocalConf();
	writelogs("Notify master...",__FUNCTION__,__FILE__,__LINE__);
	NotifyStatus();	
}





function ReceiveParams(){
	$master=$_SERVER['REMOTE_ADDR'];
	echo "RECIEVE OK\n\n";
	$users=new usersMenus();
	if(!$users->GLUSTER_INSTALLED){
		echo "GLUSTER_NOT_INSTALLED\n\n";
		die();
	}
	
	if(!function_exists("curl_init")){
		echo "CURL_NOT_INSTALLED\n\n";
		die();
	}
	
	
	
	$gluster=new GlusterClient();
	$sock=new sockets();
	$sock->SET_CLUSTER("local.name",$_POST["name"]);
	if($_POST["cyrus_id"]<>null){
		$sock->SET_CLUSTER("cyrus_id",$_POST["cyrus_id"]);
	}
	

	while (list ($num, $ligne) = each ($_POST) ){
		
		$gluster->PARAMS[$num]=$ligne;
		
		
	}
	
	$sock->SET_CLUSTER("master",$master);
	$gluster->Save();
	echo "GLUSTER_OK";
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?cluster-restart-notify=yes");
}

function BuildLocalConf(){
	$gluser=new GlusterClient();
	$gluser->Save();
	
}



function NotifyMaster(){
	$users=new usersMenus();
	if(!$users->GLUSTER_INSTALLED){die();}
	$master=@file_get_contents("/etc/artica-cluster/master");
	$server=$master;
	
	if(trim($master)==null){
		writelogs("Unable to get the master ",__FUNCTION__,__FILE__,__LINE__);
		return null;
	}
	

	$orders["NTFY_CLIENT"]=$gluster->PARAMS["name"];
	
	while (list ($num, $ligne) = each ($orders)){
		$curlPost .='&'.$num.'=' . urlencode($ligne);
	}
	
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://$server:9000/exec.gluster.php");
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);

	$data = curl_exec($ch);
	$error=curl_errno($ch);
	if($error>0){
	writelogs("Connect to $server error $error",__FUNCTION__,__FILE__,__LINE__); 
	}
}

function NotifyStatus(){
	$users=new usersMenus();
	$master=@file_get_contents("/etc/artica-cluster/master");
	$server=$master;
	$sock=new sockets();
	if(trim($master)==null){
		writelogs("Unable to get the master ",__FUNCTION__,__FILE__,__LINE__);
		return null;
	}
			
	
	if($GLOBALS["VERBOSE"]){echo "NotifyStatus() start...\n";}
	if(!$users->GLUSTER_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo "GLUSTER_INSTALLED=false\n";}
		die();
	}
	
	$gluster=new GlusterClient();
	
	
	$unix=new unix();
	$filetemp=$unix->FILE_TEMP();
	if($GLOBALS["VERBOSE"]){echo "/usr/share/artica-postfix/bin/artica-install --gluster-status >$filetemp 2>&1\n";}
	shell_exec("/usr/share/artica-postfix/bin/artica-install --gluster-status >$filetemp 2>&1");
	$ini=new Bs_IniHandler();
	$ini->loadFile($filetemp);
	while (list ($num, $ligne) = each ($ini->_params["GLUSTER"])){
		$orders[$num]=$ligne;
		writelogs("order $num=$ligne",__FUNCTION__,__FILE__,__LINE__);
		if($GLOBALS["VERBOSE"]){echo "order $num=$ligne\n";}
	}	
	@unlink($filetemp);
	
	
	if($GLOBALS["VERBOSE"]){echo "my name is {$gluster->PARAMS["name"]}\n";}
	$orders["NTFY_STATUS"]=$gluster->PARAMS["name"];
	$orders["bricks"]=implode(",",$gluster->CLUSTERED_BRIKS);
	
	$folders=$gluster->CLUSTERED_FOLDERS;
	
	while (list ($num, $ligne) = each ($folders)){
		$orders[$num]=$ligne;
	}
	
	
	
	$curl=new glusterCurl("https://$server:9000/exec.gluster.php");
	$curl->parms=$orders;
	if($GLOBALS["VERBOSE"]){echo "sending infos to $server\n";}
	if(!$curl->get()){
		writelogs("curl error !");
		return null;
	}
	
	if(preg_match("#DELETE_YOU#is",$curl->data)){
		shell_exec("/etc/init.d/artica-postfix stop gluster");
		shell_exec("/bin/rm -f /etc/artica-cluster/*");
		$sock->getFrameWork("cmd.php?reconfigure-cyrus=yes");
		return null;
	}
	
	if(preg_match("#CYRUS-ID=(.*?);#is",$curl->data,$re)){
		writelogs("Master cyrus id =\"{$re[1]}\"",__FUNCTION__,__FILE__,__LINE__);
		$sock->SET_CLUSTER('cyrus_id',$re[1]);
		$cyrus_id=$sock->getFrameWork("cmd.php?idofUser=cyrus");
		if($cyrus_id<>$re[1]){
			writelogs("cyrus id \"{$re[1]}\" is different of my cyrus id $cyrus_id (restart cyrus)",__FUNCTION__,__FILE__,__LINE__);
			$sock->getFrameWork("cmd.php?reconfigure-cyrus=yes");
		}
		str_replace("CYRUS-ID={$re[1]};","",$curl->data);
		
		
		
	}
	
	
	$data=explode(";",$curl->data);
	if(is_array($data)){
		while (list ($index, $computer) = each ($data)){
			if($computer==null){continue;}
			if(!is_file("/etc/artica-cluster/clusters-$computer")){
				$mustRestart=true;
				GetCLusterCLientInfos($computer);
			}
		}
	}
	
if($mustRestart){shell_exec("/etc/init.d/artica-postfix restart gluster");}
	
}



function GetCLusterCLientInfos($computer){
	
$gluster=new GlusterClient();
$master=@file_get_contents("/etc/artica-cluster/master");
	$server=$master;
	
	if(trim($master)==null){
		writelogs("Unable to get the master ",__FUNCTION__,__FILE__,__LINE__);
		return null;
	}
		
	
	$curl=new glusterCurl("https://$master:9000/exec.gluster.php");
	$curl->parms["CLIENT_NTFY_SRV_INFO"]=trim($computer);
	if(!$curl->get()){
		writelogs("protocol error",__FUNCTION__,__FILE__,__LINE__);
		return null;
	}
	
	if(strlen(trim($curl->data))<5){
		writelogs("data error",__FUNCTION__,__FILE__,__LINE__);
	}
	
	writelogs("Saving /etc/artica-cluster/clusters-$computer",__FUNCTION__,__FILE__,__LINE__);
	@file_put_contents("/etc/artica-cluster/clusters-$computer",$curl->data);
	
	
	
}

function server_request_client_info(){
	writelogs("request infos for {$_POST["CLIENT_NTFY_SRV_INFO"]}",__FUNCTION__,__FILE__,__LINE__);
	$sock=new sockets();
	$server=trim($_POST["CLIENT_NTFY_SRV_INFO"]);
	$datas=$sock->GET_CLUSTER("clusters-$server");
	writelogs("sending infos for {$_POST["CLIENT_NTFY_SRV_INFO"]} (clusters-$server) file". strlen($datas)." bytes",__FUNCTION__,__FILE__,__LINE__);
	echo $datas;
}


function server_receive_status(){
	writelogs("Receive infos from {$_POST["NTFY_STATUS"]}",__FUNCTION__,__FILE__,__LINE__);
	
	$gl=new gluster();
	
	if($gl->clients[$_POST["NTFY_STATUS"]]==null){
		writelogs("Depreciated server, send order to delete",__FUNCTION__,__FILE__,__LINE__);
		echo "DELETE_YOU";
		exit;
		}
	
	
	$ini=new Bs_IniHandler();
	while (list ($num, $ligne) = each ($_POST)){
		writelogs("Receive infos $num = $ligne from {$_POST["NTFY_STATUS"]}",__FUNCTION__,__FILE__,__LINE__);
		$ini->_params["CLUSTER"][$num]=$ligne;
	}
	
	$sock=new sockets();
	$sock->SaveClusterConfigFile($ini->toString(),"clusters-".$_POST["NTFY_STATUS"]);
	$cyrus_id=$sock->getFrameWork("cmd.php?idofUser=cyrus");
	echo "CYRUS-ID=$cyrus_id;\n";
	
	
	$gl=new gluster();
	if(is_array($gl->clients)){
		while (list ($num, $name) = each ($gl->clients) ){
			$cl[]=$name;
		}
	}
	
	$datas=implode(";",$cl);
	writelogs("Sending servers list ". strlen($datas)." bytes",__FUNCTION__,__FILE__,__LINE__);
	echo $datas;
	
	
}


function build_replicator(){
	$gluster=new gluster();
	$datas=$gluster->BuildDispatcher();
}

function tests_cyrusid(){
	$sock=new sockets();
	$cyrus_id=$sock->getFrameWork("cmd.php?idofUser=cyrus");
	echo "id:$cyrus_id\n";	
}



?>