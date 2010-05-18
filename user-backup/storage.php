<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.lvm.org.inc");

page();



function page(){
	$explorer=iconTable('explorer-64.png','{explorer}','{explorer_browse_yours_files}',"Loadjs('tree.php')",null,210,null,0,true);
	
	$html="<H1>{storage}:{$_SESSION["ou"]}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$explorer" . storage_icon()."</td>
		<td valign='top'>" . Xapian()."</td>
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