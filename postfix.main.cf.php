<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	

	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["show"])){main_cf_page();exit;}

js();

function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{main.cf}");
	$page=CurrentPageName();
	$html="
		function MainCfShowConfig(){
			YahooWin2(800,'$page?show=yes','$title');
		}
		MainCfShowConfig();
	
	";
		
	echo $html;
	
	
}

function main_cf_page(){
	
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork('cmd.php?get-main-cf=yes')));
	$html="<table>";
	while (list ($index, $line) = each ($datas) ){
		$html=$html."
		<tr>
			<td width=1%><code style='font-size:11px'>$index</td>
			<td><code style='font-size:11px'>". htmlspecialchars($line)."</code></td>
		</tr>
		
		";
	}
	$html=$html."</table>";
	
	echo "<div style='width:100%;height:550px;overflow:auto'>$html</div>";
	
	
}
	
?>	

