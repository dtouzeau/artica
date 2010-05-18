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
	if(isset($_GET["main"])){popup_main();exit;}
	if(isset($_GET["sharedlist"])){shared_folders_list();exit;}
	if(isset($_GET["acldisks"])){acldisks();exit;}
	if(isset($_GET["aclline"])){aclsave();exit;}
	if(isset($_GET["SearchUser"])){SearchUser();exit;}
	if(isset($_GET["SearchPattern"])){list_users();exit;}
	
	if(isset($_GET["AddAclUser"])){AddAclUser();exit;}
	if(isset($_GET["DeleteAclUser"])){DeleteAclUser();exit;}
	if(isset($_GET["ChangeAclUser"])){ChangeAclUser();exit;}
	
	if(isset($_GET["AddAclGroup"])){AddAclGroup();exit;}
	if(isset($_GET["DeleteAclGroup"])){DeleteAclGroup();exit;}
	if(isset($_GET["ChangeAclGroup"])){ChangeAclGroup();exit;}
	if(isset($_GET["set-recursive"])){SubitemsMode();exit;}
	
js();
//fstablist

function js(){
	$tpl=new templates();
	$folder_decrypted=base64_decode($_GET["path"]);
	$title=$tpl->_ENGINE_parse_body("{ACLS}::$folder_decrypted","samba.index.php");
	$members=$tpl->_ENGINE_parse_body("{members}::$folder_decrypted","samba.index.php");
	$page=CurrentPageName();
	$html="
		function acls_folders_start(){
			YahooWin6('410','$page?popup=yes&path={$_GET["path"]}','$title');
		}
		
		function AclAddUser(){
			YahooSearchUser('300','$page?SearchUser=yes','$members');
		}	
		
	   var x_SearchUserPerform=function (obj) {
			tempvalue=obj.responseText;
			document.getElementById('acls_user_list').innerHTML=tempvalue;
	    }		
		
		function SearchUserPerform(){
			var XHR = new XHRConnection();
			XHR.appendData('SearchPattern',document.getElementById('SearchPattern').value);
			document.getElementById('acls_user_list').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>'; 
			XHR.sendAndLoad('$page', 'GET',x_SearchUserPerform);
		}
		
		function SearchUserPress(e){
			if(checkEnter(e)){SearchUserPerform();}
		}
		
	   var x_addacl=function (obj) {
			tempvalue=obj.responseText;
			document.getElementById('acls_log').innerHTML=tempvalue;
			RefreshAclTable();
	    }	

	   var x_changeacl=function (obj) {
	   		tempvalue=obj.responseText;
			document.getElementById('acls_log').innerHTML=tempvalue;
		}
		
		
		function AddAclGroup(groupname){
			var XHR = new XHRConnection();
			XHR.appendData('AddAclGroup',groupname);
			XHR.appendData('path','{$_GET["path"]}');
			if(document.getElementById('recursive')){
				if(document.getElementById('recursive').checked){XHR.appendData('recursive','1');}else{XHR.appendData('recursive','0');}
				if(document.getElementById('default').checked){XHR.appendData('default','1');}else{XHR.appendData('default','0');}
			}					
			document.getElementById('MAIN_CLS_INFOS').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>'; 
			XHR.sendAndLoad('$page', 'GET',x_addacl);
		}
		
		function AddAclUser(username){
			var XHR = new XHRConnection();
			XHR.appendData('AddAclUser',username);
			XHR.appendData('path','{$_GET["path"]}');
			if(document.getElementById('recursive')){
				if(document.getElementById('recursive').checked){XHR.appendData('recursive','1');}else{XHR.appendData('recursive','0');}
				if(document.getElementById('default').checked){XHR.appendData('default','1');}else{XHR.appendData('default','0');}
			}					
			document.getElementById('MAIN_CLS_INFOS').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>'; 
			XHR.sendAndLoad('$page', 'GET',x_addacl);
		}
		
		
		
		function DeleteAclGroup(groupname){
			var XHR = new XHRConnection();
			XHR.appendData('DeleteAclGroup',groupname);
			XHR.appendData('path','{$_GET["path"]}');
			if(document.getElementById('recursive')){
				if(document.getElementById('recursive').checked){XHR.appendData('recursive','1');}else{XHR.appendData('recursive','0');}
				if(document.getElementById('default').checked){XHR.appendData('default','1');}else{XHR.appendData('default','0');}
			}					
			document.getElementById('MAIN_CLS_INFOS').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>'; 
			XHR.sendAndLoad('$page', 'GET',x_addacl);
		}
		
		function DeleteAclUser(username){
			var XHR = new XHRConnection();
			XHR.appendData('DeleteAclUser',username);
			XHR.appendData('path','{$_GET["path"]}');
			if(document.getElementById('recursive')){
				if(document.getElementById('recursive').checked){XHR.appendData('recursive','1');}else{XHR.appendData('recursive','0');}
				if(document.getElementById('default').checked){XHR.appendData('default','1');}else{XHR.appendData('default','0');}
			}					
			document.getElementById('MAIN_CLS_INFOS').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>'; 
			XHR.sendAndLoad('$page', 'GET',x_addacl);
		}		
		
		
		function ChangeAclUser(username){
			var chmod='';	
			var XHR = new XHRConnection();
			if(document.getElementById(username+'_R').checked){chmod=chmod+'r';}
			if(document.getElementById(username+'_W').checked){chmod=chmod+'w';}
			if(document.getElementById(username+'_X').checked){chmod=chmod+'x';}
			if(document.getElementById('recursive')){
				if(document.getElementById('recursive').checked){XHR.appendData('recursive','1');}else{XHR.appendData('recursive','0');}
				if(document.getElementById('default').checked){XHR.appendData('default','1');}else{XHR.appendData('default','0');}
			}				
			
			XHR.appendData('ChangeAclUser',username);
			XHR.appendData('chmod',chmod);
			XHR.appendData('path','{$_GET["path"]}');
			XHR.sendAndLoad('$page', 'GET',x_changeacl);
			}	

		function AclChangeSubitems(){
			var XHR = new XHRConnection();
			XHR.appendData('path','{$_GET["path"]}');
			if(document.getElementById('recursive').checked){XHR.appendData('set-recursive','1');}else{XHR.appendData('set-recursive','0');}
			if(document.getElementById('default').checked){XHR.appendData('set-default','1');}else{XHR.appendData('set-default','0');}			
			XHR.sendAndLoad('$page', 'GET',x_changeacl);
		}
		
		
		function ChangeAclGroup(groupname){
			var chmod='';	
			var XHR = new XHRConnection();
			if(document.getElementById(groupname+'_R').checked){chmod=chmod+'r';}
			if(document.getElementById(groupname+'_W').checked){chmod=chmod+'w';}
			if(document.getElementById(groupname+'_X').checked){chmod=chmod+'x';}
			
			if(document.getElementById('recursive')){
				if(document.getElementById('recursive').checked){XHR.appendData('recursive','1');}else{XHR.appendData('recursive','0');}
				if(document.getElementById('default').checked){XHR.appendData('default','1');}else{XHR.appendData('default','0');}
			}				
			
			
			XHR.appendData('ChangeAclGroup',groupname);
			XHR.appendData('chmod',chmod);
			XHR.appendData('path','{$_GET["path"]}');
			XHR.sendAndLoad('$page', 'GET',x_changeacl);
			}
		
		function RefreshAclTable(){
			LoadAjax('MAIN_CLS_INFOS','$page?main=yes&path={$_GET["path"]}');
		}
	
	acls_folders_start();";
	
echo $html;	
	
}

function SubitemsMode(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?change-acl-items=yes&recursive={$_GET["set-recursive"]}&default={$_GET["set-default"]}&path={$_GET["path"]}")));
	if(is_array($datas)){echo "<span style='color:red;font-weight:bold'>".implode("<br>",$datas)."</span>";}	

}

function AddAclGroup(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?add-acl-group=yes&group={$_GET["AddAclGroup"]}&path={$_GET["path"]}&recursive={$_GET["recursive"]}&default={$_GET["default"]}")));
	if(is_array($datas)){echo "<span style='color:red;font-weight:bold'>".implode("<br>",$datas)."</span>";}
	}
function AddAclUser(){
	$sock=new sockets();
	writelogs("Add ". base64_decode($_GET["AddAclUser"]),__FUNCTION__,__FILE__,__LINE__);
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?add-acl-user=yes&username={$_GET["AddAclUser"]}&path={$_GET["path"]}&recursive={$_GET["recursive"]}&default={$_GET["default"]}")));
	if(is_array($datas)){echo "<span style='color:red;font-weight:bold'>".implode("<br>",$datas)."</span>";}	
	
}
	
function DeleteAclGroup(){
	$sock=new sockets();
	$_GET["DeleteAclGroup"]=base64_encode($_GET["DeleteAclGroup"]);
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?delete-acl-group=yes&group={$_GET["DeleteAclGroup"]}&path={$_GET["path"]}&recursive={$_GET["recursive"]}&default={$_GET["default"]}")));
	if(is_array($datas)){echo "<span style='color:red;font-weight:bold'>".implode("<br>",$datas)."</span>";}		
}
function DeleteAclUser(){
	$sock=new sockets();
	$_GET["DeleteAclUser"]=base64_encode($_GET["DeleteAclUser"]);
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?delete-acl-user=yes&username={$_GET["DeleteAclUser"]}&path={$_GET["path"]}&recursive={$_GET["recursive"]}&default={$_GET["default"]}")));
	if(is_array($datas)){echo "<span style='color:red;font-weight:bold'>".implode("<br>",$datas)."</span>";}		
}


function ChangeAclGroup(){
	$sock=new sockets();
	$_GET["ChangeAclGroup"]=base64_encode($_GET["ChangeAclGroup"]);
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?change-acl-group=yes&chmod={$_GET["chmod"]}&group={$_GET["ChangeAclGroup"]}&path={$_GET["path"]}&recursive={$_GET["recursive"]}&default={$_GET["default"]}")));
	if(is_array($datas)){echo "<span style='color:red;font-weight:bold'>".implode("<br>",$datas)."</span>";}
}
function ChangeAclUser(){
	$sock=new sockets();
	$_GET["ChangeAclUser"]=base64_encode($_GET["ChangeAclUser"]);
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?change-acl-user=yes&chmod={$_GET["chmod"]}&username={$_GET["ChangeAclUser"]}&path={$_GET["path"]}&recursive={$_GET["recursive"]}&default={$_GET["default"]}")));
	if(is_array($datas)){echo "<span style='color:red;font-weight:bold'>".implode("<br>",$datas)."</span>";}	
}
	

function popup(){

	$html="
	<div id='acls_log'></div>
	<div id='MAIN_CLS_INFOS'></div>
	<script>
		RefreshAclTable();
	</script>
	";
	
	echo $html;
	
}
function popup_main(){	
	$path=$_GET["path"];
	$sock=new sockets();
	$acls=unserialize(base64_decode($sock->getFrameWork("cmd.php?path-acls=$path")));
	
	
	
	if(!is_array($acls)){
		$tpl=new templates();
		$html="<div style='font-size:14px;font-weight:bold;color:red'>{acls_get_error}</div>";
		echo $tpl->_ENGINE_parse_body($html);
		exit;
	}

	$group_img="img/wingroup.png";
	$user_img="img/winuser.png";
	

	
	$USER_RIGHTS=exploderights($acls["OWNER"]["RIGHTS"]);
	$GROUP_RIGHTS=exploderights($acls["GROUP"]["RIGHTS"]);
	$OTHERS_RIGHTS=exploderights($acls["other"]["RIGHTS"]);
	
	$USER_RIGHTS_JS="ChangeAclUser('OWNER')";
	$GROUP_RIGHTS_JS="ChangeAclGroup('GROUP');";
	$OTHERS_RIGHTS_JS="ChangeAclGroup('OTHER');";
	
	if(base64_decode($sock->getFrameWork("cmd.php?IsDir=$path"))=="TRUE"){
		$changesubitems=
		"<table style='width:40%'>
			<tr>
				<td class=legend>{recursive}</td>
				<td>".Field_checkbox("recursive",1,null,"AclChangeSubitems()")."</td>
				<td class=legend>{default}</td>
				<td>".Field_checkbox("default",1,null,"AclChangeSubitems()")."</td>
			</tr>	
		</table>		
		";
	}
	
	
	$html="
	<div style='text-align:right'><input type='button' value='{add_users_and_groups}&nbsp;&raquo' Onclick=\"javascript:AclAddUser()\"></div>
	$changesubitems
	<table style='width:100%'>
	<tr ". CellRollOver().">
		<th colspan=3>{members}</th>
		<th width=1%>{read}</th>
		<th width=1%>{write}</th>
		<th width=1%>{execute}</th>
	</tr>
	<tr ". CellRollOver().">
		<td width=1%><img src='$user_img'></td>
		<td><strong style='font-size:12px'>{$acls["OWNER"]["NAME"]}</strong></td>
		<td width=1%>&nbsp;</td>
		<td width=1%>". Field_checkbox("OWNER_R",1,$USER_RIGHTS["r"],$USER_RIGHTS_JS)."</td>
		<td width=1%>". Field_checkbox("OWNER_W",1,$USER_RIGHTS["w"],$USER_RIGHTS_JS)."</td>
		<td width=1%>". Field_checkbox("OWNER_X",1,$USER_RIGHTS["x"],$USER_RIGHTS_JS)."</td>		
	</tr>
	<tr ". CellRollOver().">
		<td width=1%><img src='$group_img'></td>
		<td><strong style='font-size:12px'>{$acls["GROUP"]["NAME"]} ({group})</strong></td>
		<td width=1%>&nbsp;</td>
		<td width=1%>". Field_checkbox("GROUP_R",1,$GROUP_RIGHTS["r"],$GROUP_RIGHTS_JS)."</td>
		<td width=1%>". Field_checkbox("GROUP_W",1,$GROUP_RIGHTS["w"],$GROUP_RIGHTS_JS)."</td>
		<td width=1%>". Field_checkbox("GROUP_X",1,$GROUP_RIGHTS["x"],$GROUP_RIGHTS_JS)."</td>		
	</tr>
	<tr ". CellRollOver().">
		<td width=1%><img src='$group_img'></td>
		<td><strong style='font-size:12px'>{other} ({group})</strong></td>
		<td width=1%>&nbsp;</td>
		<td width=1%>". Field_checkbox("OTHER_R",1,$OTHERS_RIGHTS["r"],$OTHERS_RIGHTS_JS)."</td>
		<td width=1%>". Field_checkbox("OTHER_W",1,$OTHERS_RIGHTS["w"],$OTHERS_RIGHTS_JS)."</td>
		<td width=1%>". Field_checkbox("OTHER_X",1,$OTHERS_RIGHTS["x"],$OTHERS_RIGHTS_JS)."</td>		
	</tr>";

		
if(is_array($acls["groups"])){
	while (list ($index, $arry) = each ($acls["groups"]) ){
		$group_name=$arry["NAME"];
		$group_name=str_replace('\040'," ",$group_name);
		
		
		$rights=exploderights($arry["RIGHTS"]);
		$js="ChangeAclGroup('$group_name');";
		$html=$html."
		<tr ". CellRollOver().">
		<td width=1%><img src='$group_img'></td>
		<td><strong style='font-size:12px'>$group_name ({group})</strong></td>
		<td width=1%>". imgtootltip("ed_delete.gif","{delete}","DeleteAclGroup('{$group_name}')")."</td>
		<td width=1%>". Field_checkbox("{$group_name}_R",1,$rights["r"],"$js")."</td>
		<td width=1%>". Field_checkbox("{$group_name}_W",1,$rights["w"],"$js")."</td>
		<td width=1%>". Field_checkbox("{$group_name}_X",1,$rights["x"],"$js")."</td>		
		</tr>";
		
		
		
	}
	
}
if(is_array($acls["users"])){
	while (list ($index, $arry) = each ($acls["users"]) ){
		$group_name=$arry["NAME"];
		$rights=exploderights($arry["RIGHTS"]);
		$js="ChangeAclUser('$group_name');";
		$html=$html."
		<tr ". CellRollOver().">
		<td width=1%><img src='$user_img'></td>
		<td><strong style='font-size:12px'>$group_name</strong></td>
		<td width=1%>". imgtootltip("ed_delete.gif","{delete}","DeleteAclUser('{$group_name}')")."</td>
		<td width=1%>". Field_checkbox("{$group_name}_R",1,$rights["r"],"$js")."</td>
		<td width=1%>". Field_checkbox("{$group_name}_W",1,$rights["w"],"$js")."</td>
		<td width=1%>". Field_checkbox("{$group_name}_X",1,$rights["x"],"$js")."</td>		
		</tr>";
		
		
		
	}
	
}
		
		
	
	$html=$html."</table>";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function exploderights($pattern){
	for($i=0;$i<strlen($pattern);$i++){
		$array[$pattern[$i]]=1;
	}
	
	return $array;
}


function SearchUser(){
	
	$html="
	<H3>{add_member}</H3>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{search}:</td>
		<td>". Field_text("SearchPattern",null,"font-size:13px;",null,null,null,false,"SearchUserPress(event)")."</td>
	</tr>
	</table>
	<hr>
	<div id='acls_user_list' style='width:100%;height:350px;overflow:auto'></div>
	
	<script>
		SearchUserPerform();
	</script>
	
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}


function list_users(){
	
	$query=$_GET["SearchPattern"];

	
	$ldap=new clladp();
	
	$sr =@ldap_search($ldap->ldap_connection,"dc=organizations,$ldap->suffix",
	"(&(objectClass=posixAccount)(|(uid=$query*)(cn=$query*)(displayName=$query*)))",array("displayname","uid"),0,10);
		if($sr){
				$result = ldap_get_entries($ldap->ldap_connection, $sr);
				for($i=0;$i<$result["count"];$i++){
					
					$displayname=$result[$i]["displayname"][0];
					$uid=$result[$i]["uid"][0];
					if(substr($uid,strlen($uid)-1,1)=='$'){continue;}
					
					
					if($displayname==null){$displayname=$uid;}
					$res[$uid]=$displayname;
				}
				
		}	
		
		$sr =@ldap_search($ldap->ldap_connection,"dc=organizations,$ldap->suffix",
		"(&(objectClass=posixGroup)(|(cn=$query*)(memberUid=$query*)))",array("cn","memberUid"),0,10);
		if($sr){
				$result = ldap_get_entries($ldap->ldap_connection, $sr);
				for($i=0;$i<$result["count"];$i++){
					$displayname=$result[$i]["cn"][0];
					$uid=$result[$i]["cn"][0];
					if($displayname==null){$displayname=$uid;}
					$res[$uid]=array("displayname"=>"$displayname","members"=>$result[$i]["memberuid"]);
				}
				
		}		
	
	
	
	
	
if(!is_array($res)){return null;}
rsort($res);

	
	while (list ($num, $ligne) = each ($res) ){
		if($num==null){continue;}
		
		
		
		if(is_array($ligne)){
			$img="wingroup.png";
			$js="AddAclGroup('".base64_encode($ligne["displayname"])."');";
			
			if(strlen($ligne["displayname"])>30){$ligne["displayname"]=substr($ligne["displayname"],0,27)."...";}
			if($ligne["members"]["count"]>10){$ligne["members"]["count"]=10;}
			$mm[]="<strong>{members}</strong><ul>";
			for($i=0;$i<$ligne["members"]["count"];$i++){
				$mm[]="<li style=font-size:11px>{$ligne["members"][$i]}</li>";
			}
			$mm[]="</ul>";
			$Displayname=texttooltip($ligne["displayname"],@implode("",$mm),null,null,1,"font-size:13px");
			unset($mm);
			
		}else{
			$Displayname=$ligne;
			$js="AddAclUser('".base64_encode($Displayname)."');";
			if(strlen($Displayname)>30){$Displayname=substr($Displayname,0,27)."...";}
			$img="winuser.png";
			
		}
		
		
		
	$html=$html."<table>
		<tr ". CellRollOver().">
		<td width=1%><img src='img/$img'></td>
		<td><strong style='font-size:13px' >$Displayname</td>
		<td width=1%>". imgtootltip("add-18.gif","{add}",$js)."</td>
		</tr>
		</table>
	
	";
	}
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");
}	

?>