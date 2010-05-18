<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');

	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}

	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["add-form"])){popup_add();exit;}
	if(isset($_GET["ip_addr"])){popup_save();exit;}
	if(isset($_GET["refresh"])){echo getlist();exit;}
	if(isset($_GET["del"])){popup_delete();exit;}
	js();
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{etc_hosts}");
	$title2=$tpl->_ENGINE_parse_body("{add_new_entry}");
	$html="
	
	function etc_host_show(){
			YahooWin3(730,'$page?popup=yes','$title');
	}
	
	function etc_hosts_add_form(){
		YahooWin4(360,'$page?add-form=yes','$title2');
	}

var X_etc_hosts_add_form_save= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	YahooWin4Hide();
	refresh();
	
	}		
	
	function etc_hosts_add_form_save(){
		var XHR = new XHRConnection();
		XHR.appendData('ip_addr',document.getElementById('ip_addr').value);
		XHR.appendData('servername',document.getElementById('servername').value);
		XHR.appendData('alias',document.getElementById('alias').value);
		document.getElementById('hostsdiv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_etc_hosts_add_form_save);		
	}
	
	function refresh(){
		LoadAjax('idhosts','$page?refresh=yes');
	}
	
	function etc_hosts_del(index){
		var XHR = new XHRConnection();
		XHR.appendData('del',index);
		document.getElementById('idhosts').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_etc_hosts_add_form_save);		
	}	
		
	etc_host_show();	
	";
	
	echo $html;
}



function popup(){
	
	$LIST=getlist();
	$add=Paragraphe("host-file-64-add.png","{add_new_entry}","{add_new_entry_text}","javascript:etc_hosts_add_form()","{add_new_entry_text}");
	$html="<p style='font-size:12px'>{etc_hosts_explain}</p>
	<table style='width:100%'>
	<tr>
		<td valign='top'><div style='width:100%;height:330px;overflow:auto' id='idhosts'>$LIST</div></td>
		<td valign='top'>$add</td>
	</tr>
	</table>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");	
	
	
}

function getlist(){
	$sock=new sockets();
	$res=base64_decode($sock->getFrameWork("cmd.php?etc-hosts-open=yes"));
	writelogs($res,__FUNCTION__,__FILE__,__LINE__);
	$datas=unserialize($res);
	while (list ($num, $ligne) = each ($datas) ){
		if(preg_match("#^([0-9\.\:]+)\s+(.+?)\s+(.+?)$#",$ligne,$re)){
			$array[]=array("name"=>$re[2],"alias"=>$re[3],"ip"=>$re[1],"md"=>md5($ligne));
			continue;
		}
		
		if(preg_match("#^([0-9\.\:]+)\s+(.+?)$#",$ligne,$re)){
			$array[]=array("name"=>$re[2],"ip"=>$re[1],"md"=>md5($ligne));
			continue;
		}		
		
	}
	
	
	if(!is_array($array)){return null;}
	$html="<table style='width:98%' class=table_form>
	<tr>
		<th>&nbsp;</th>
		<th>{ip_address}</th>
		<th>{servername}</th>
		<th>{alias}</th>
		<th>&nbsp;</th>
	</tr>
	";
	while (list ($num, $ligne) = each ($array) ){
		$html=$html."<tr ". CellRollOver().">
			<td width=1% nowrap><img src='img/base.gif'></td>
			<td width=1% nowrap>{$ligne["ip"]}</td>
			<td width=60% nowrap>{$ligne["name"]}</td>
			<td width=1% nowrap>{$ligne["alias"]}</td>
			<td width=1% nowrap>". imgtootltip("ed_delete.gif","{delete}","etc_hosts_del('{$ligne["md"]}')")."</td>
			</tr>
			
			";
		
	}
	
	$html=$html."</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("$html");		
	
	
}

function popup_save(){
	if($_GET["ip_addr"]==null){return false;}
	if($_GET["servername"]==null){return false;}
	if($_GET["alias"]==null){$_GET["alias"]=$_GET["servername"];}
	$line="{$_GET["ip_addr"]}\t{$_GET["servername"]}\t{$_GET["alias"]}";
	
	$sock=new sockets();
	$line=base64_encode($line);
	$sock->getFrameWork("cmd.php?etc-hosts-add=$line");
	
	
}

function popup_add(){
	
	$html="
	
	<div id='hostsdiv'>
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/64-computer.png'></td>
		<td valign='top'>
			<table style='width:100%' class=table_form>
			<tr>
				<td class=legend>{ip_address}:</td>
				<td>". Field_text('ip_addr',null,"width:120px")."</td>
			</tR>
			<tr>
				<td class=legend>{servername}:</td>
				<td>". Field_text('servername',null,"width:120px")."</td>
			</tR>
				
			<tr>
				<td class=legend>{alias}:</td>
				<td>". Field_text('alias',null,"width:120px")."</td>
			</tR>
		</table><hr>
		<div style='width:100%;text-align:right'>". button("{add}","etc_hosts_add_form_save()")."</div>
		</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");		
	
}

function popup_delete(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?etc-hosts-del={$_GET["del"]}");
}
	

?>