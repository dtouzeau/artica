<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.samba.inc');


	
	
	$user=new usersMenus();
	if($user->AsSambaAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["sharedlist"])){shared_folders_list();exit;}
	if(isset($_GET["acldisks"])){acldisks();exit;}
	if(isset($_GET["aclline"])){aclsave();exit;}
js();
//fstablist

function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{shared_folders}","samba.index.php");
	$page=CurrentPageName();
	$html="
		function shared_folders_start(){
			YahooWin5('600','$page?popup=yes','$title');
		}
	
	
	shared_folders_start();";
	
echo $html;	
	
}

function popup(){
	
	$array["sharedlist"]="{shared_folders}";
	$array["acldisks"]="{acl_disks}";
	$tpl=new templates();
	$page=CurrentPageName();
	while (list ($num, $ligne) = each ($array) ){
		$ligne=$tpl->_ENGINE_parse_body($ligne);
		$ligne_text= html_entity_decode($ligne,ENT_QUOTES,"UTF-8");
		if(strlen($ligne_text)>17){
			$ligne_text=substr($ligne_text,0,14);
			$ligne_text=htmlspecialchars($ligne_text)."...";
			$ligne_text=texttooltip($ligne_text,$ligne,null,null,1);
			}
		//$html=$html . "<li><a href=\"javascript:ChangeSetupTab('$num')\" $class>$ligne</a></li>\n";
		
		$html[]= "<li><a href=\"$page?$num=yes\"><span>$ligne_text</span></li>\n";
			
		}
	$tpl=new templates();
	
	echo "
	<div id=main_samba_shared_folders style='width:100%;height:550px;overflow:auto;background-color:white;'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_samba_shared_folders').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			

			});
		</script>";			
}

function shared_folders_list(){
	
	
	
	
	$samba=new samba();
	$folders=$samba->main_folders;
	if(!is_array($folders)){return null;}
	
	
	$html="
	<input type='hidden' id='del_folder_name' value='{del_folder_name}'>
	<table style='width:100%'>
	<tr>
	<th>&nbsp;</th>
	<th>{name}</th>
	<th>{path}</th>
	<th>&nbsp;</th>
	</tr>";
	
	
	while (list ($FOLDER, $ligne) = each ($folders) ){
		if($FOLDER=="netlogon"){continue;}
		if($FOLDER=="homes"){continue;}
		if($FOLDER=="printers"){continue;}
		if($FOLDER=="print$"){continue;}
		$properties="FolderProp('$FOLDER')";
		$delete=imgtootltip('ed_delete.gif','{delete}',"FolderDelete('$FOLDER')");
		if($samba->main_array[$FOLDER]["path"]=="/home/netlogon"){continue;}
		if($samba->main_array[$FOLDER]["path"]=="/home/export/profile"){continue;}

					
		
		
		
	$html=$html . "
	<tr " . CellRollOver($properties) . ">
	<td width=1%><img src='img/shared20x20.png'></td>
	<td><strong style='font-size:12px' width=1% nowrap>$FOLDER</td>
	<td><strong style='font-size:12px' width=99%>{$samba->main_array[$FOLDER]["path"]}</td>
	<td width=1%>$delete</td>
	</tr>
	";
	}
	
	$html=$html ."</table>";
	
	$html="<div style='width:99%;height:250px;overflow:auto'>$html</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function acldisks(){
	$sock=new sockets();
	$SAMBA_HAVE_POSIX_ACLS=base64_decode($sock->getFrameWork("cmd.php?SAMBA-HAVE-POSIX-ACLS=yes"));
	
	if($SAMBA_HAVE_POSIX_ACLS<>"TRUE"){
		$acl_samba_not="<strong style='color:red;font-size:11px;padding:4px'>{acl_samba_not}</strong>";
	}
	$fstab=unserialize(base64_decode($sock->getFrameWork("cmd.php?fstablist=yes")));
	$page=CurrentPageName();
	$html="
	<div style='font-size:13px;margin:8px;padding:3px'>{acl_feature_about}</div>$acl_samba_not
	<div id='acltable'>
	<table style='width:100%'>
	<tr>
		<th colspan=2>{disk}</th>
		<th>{mounted}</th>
		<th>{acl_enabled}</th>
	</tr>
	";
	
	while (list ($num, $ligne) = each ($fstab) ){
		if(substr($ligne,0,1)=="#"){continue;}
		if(preg_match("#(.+?)\s+(.+?)\s+(.*?)\s+(.*?)\s+#",$ligne,$re)){
			if($re[1]=="proc"){continue;}
			if($re[2]=="none"){continue;}
			if(preg_match("#cdrom#",$re[2])){continue;}
			if(preg_match("#floppy#",$re[2])){continue;}
			if(preg_match("#\/boot$#",$re[2])){continue;}
			if($re[3]=="tmpfs"){continue;}
			
			
			if(preg_match("#acl#",$re[4])){$acl=1;}else{$acl=0;}
			
			
			
			$dev=base64_encode(trim($re[1]));
			$enableacl=Field_checkbox("acl_$num",1,$acl,"FdiskEnableAcl('acl_$num','$dev');");
			$html=$html."
			<tr ". CellRollOver().">
				<td width=1%><img src='img/mailbox_hd.gif'></td>
				<td><code style='font-size:13px'>$re[1] ($re[3])</code></td>
				<td><code style='font-size:13px'>$re[2]</code></td>
				<td width=1% valign='top'>$enableacl</td>
			</tr>
			";
		}
	}
	
	$html=$html."</table>
	</div>
	<script>
	
	var x_FdiskEnableAcl=function (obj) {
			tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue);}
			if(document.getElementById('main_samba_shared_folders')){
				RefreshTab('main_samba_shared_folders');
				}
			
			if(document.getElementById('main_config_samba')){
				RefreshTab('main_config_samba');
				}			
			
	    }
	
		function FdiskEnableAcl(id,dev){
			var XHR = new XHRConnection();
			XHR.appendData('aclline',dev);
			if(document.getElementById(id).checked){XHR.appendData('acl','1');}else{XHR.appendData('acl','0');}
			document.getElementById('acltable').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>'; 
			XHR.sendAndLoad('$page', 'GET',x_FdiskEnableAcl);
		}
	
	</script>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function aclsave(){
	$dev=$_GET["aclline"];
	$acl=$_GET["acl"];
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?fstab-acl=yes&acl=$acl&dev=$dev");
	
	
	
}


?>