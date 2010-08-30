<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.lvm.org.inc");
include_once(dirname(__FILE__)."/ressources/class.computers.inc");


if(isset($_GET["list"])){computers_list();exit;}

page();

function page(){
	$page=CurrentPageName();
	$html="<H1>{my_computers}:{$_SESSION["uid"]}/{$_SESSION["ou"]}</H1>
	<div id='computerslist' style='width:100%'></div>
	<script>
		LoadAjax('computerslist','$page?list=yes');
	</script>
	";
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}




function computers_list(){
		$userid=new user($_SESSION["uid"]);
		$dn=$userid->dn;
		$ldap=new clladp();
		$pattern="(&(objectClass=ComputerAfectation)(cn=*))";
		$attr=array();
		$sr=@ldap_search($ldap->ldap_connection,$dn,$pattern,$attr);
		if(!$sr){return null;}
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		if($hash["count"]==0){return;}
		
		for($i=0;$i<$hash["count"];$i++){
			$uid=$hash[$i]["uid"][0];
			$mac=$hash[$i]["computermacaddress"][0];
			$computer=new computers($uid);
			$uid_text=str_replace("$","",$uid);
			
			$js="javascript:Loadjs('computer.infos.php?uid=$uid');";
			$tb[]="<div style='float:left;margin:3px'>".Paragraphe("64-computer.png",
			$uid_text,"<strong>$mac<div><i>$computer->ComputerOS</i></div><div>$computer->ComputerIP</div></strong>",$js)."</div>";
		}
		
			
	$html="<div style='width:100%'>".implode("\n",$tb);
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
?>