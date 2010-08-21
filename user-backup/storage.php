<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.lvm.org.inc");
include_once(dirname(__FILE__)."/ressources/class.donkey.inc");
include_once(dirname(__FILE__)."/ressources/class.apache.inc");
page();



function page(){
	$explorer=iconTable('explorer-64.png','{explorer}','{explorer_browse_yours_files}',"Loadjs('tree.php')",null,210,null,0,true);
	
	$users=new usersMenus();
	if($users->SAMBA_INSTALLED){
		$shareAfolder=iconTable('folder-granted-64.png','{SHARE_FOLDER}','{SHARE_A_FOLDER_USER_TEXT}',"Loadjs('share-a-folder.php')",null,210,null,0,true);
	}
	
	if($users->MLDONKEY_INSTALLED){
		$ml=new EmuleTelnet();
		if($ml->UserIsActivated($_SESSION["uid"])){
			$mldonkey=iconTable('64-emule.png','{PEER_TO_PEER_NETWORKS}','{PEER_TO_PEER_NETWORKS_TEXT}',"Loadjs('donkey.php')",null,210,null,0,true);
		}
	}else{
		writelogs("MLDONKEY_INSTALLED return false",__FUNCTION__,__FILE__,__LINE__);
	}
	
	$ct=new user($_SESSION["uid"]);
	
	writelogs("WebDavUser=$ct->WebDavUser",__FUNCTION__,__FILE__,__LINE__);
	
	if($ct->WebDavUser==1){
			$apache=new vhosts();
			print_r($hash);
			$hash=$apache->LoadVhostsType($ct->ou);
			if($hash["WEBDAV"]){
				$webdav=iconTable('webdav-64.png','{WEBDAV_HOWTO}','{WEBDAV_HOWTO_TEXT}',"Loadjs('webdav.php')",null,210,null,0,true);
			}
	}
	
	
	
	
	
	
	$html="<H1>{storage}:{$_SESSION["ou"]}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$explorer$shareAfolder" . storage_icon()."</td>
		<td valign='top'>" . Xapian()."$mldonkey$webdav</td>
	</tr>
	</table>
	
	";
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function storage_icon(){
	$user=new usersMenus();
	if(!$user->AsOrgAdmin){return null;}
	$lvm_g=new lvm_org($_SESSION["ou"]);
	if(count($lvm_g->disklist)==0){return null;}
	return iconTable('64-hd.png','{your_storage}','{your_storage_text}',"Loadjs('domains.edit.hd.php?&ou={$_SESSION["ou"]}')",null,210,null,0,true);
}


function Xapian(){
	
	
	
	$sock=new sockets();
	$users=new usersMenus();
	$EnableSambaXapian=$sock->GET_INFO('EnableSambaXapian');
	if($EnableSambaXapian==null){$EnableSambaXapian=1;}
	if(!$users->XAPIAN_PHP_INSTALLED){$EnableSambaXapian=0;}
	if($EnableSambaXapian==0){return null;}
	
	$uri="https://{$_SERVER['SERVER_NAME']}/exec.OpenSearch.php";
	
	$icon1=iconTable('find-documents-64.png','{find_documents}','{find_documents_xapian_text}',"Loadjs('xapian.php')",null,210,null,0,true);
	$icon2=iconTable('loupe-64.png','{search_connector}','{add_search_connector_text}',"s_PopUp('$uri',800,800)",null,210,null,0,true);
	return $icon1.$icon2;
}
?>