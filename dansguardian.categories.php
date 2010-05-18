<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.dansguardian.inc');
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	
	if(!IsDansGuardianrights()){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		exit;
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["DansGuardian_addcategory"])){main_rules_addcategory();exit;}
	if(isset($_GET["DansGuardian_delcategory"])){main_rules_delcategory();exit;}	
	
	js();
	
	
function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$rulename=base64_decode($_GET["rule-name"]);
	$title=$tpl->_ENGINE_parse_body("{categories}");
	$html="
	
	function DANSGUARDIAN_LOAD_CATEGORIES(){
		RTMMail(650,'$page?popup=yes&rule_main={$_GET["rule_main"]}','$title');
	
	}
	
	var x_dansguardian_addcategory=function(obj){
	      DANSGUARDIAN_LOAD_CATEGORIES();
	}	
	
	function dansguardian_addcategory(){
	 		var XHR = new XHRConnection();
	        XHR.appendData('DansGuardian_addcategory',document.getElementById('blacklist').value);
	        XHR.appendData('rule_main','{$_GET["rule_main"]}');
	        document.getElementById('main_rules_categories_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';       
	        XHR.sendAndLoad('$page', 'GET',x_dansguardian_addcategory);  
	    
	}
	
function dansguardian_delcategory(hostname,rule_main,index){
 var XHR = new XHRConnection();
        hostname_mem=hostname;
        rule_main_mem=rule_main;
        XHR.appendData('DansGuardian_delcategory',index);
        XHR.appendData('rule_main','{$_GET["rule_main"]}');
        document.getElementById('main_rules_categories_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
        XHR.sendAndLoad('$page', 'GET',x_dansguardian_addcategory);  
}	
	
	DANSGUARDIAN_LOAD_CATEGORIES()";
	
	echo $html;
	
}
function strip_rulename($rulename){
	if(preg_match('#(.+?);(.+)#',$rulename,$re)){
		return $re[1];
		
	}else{
		return $rulename;
	}
	
}

function popup($noecho=0){
	$rule_main=$_GET["rule_main"];
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$dansg=new dansguardian($_GET["hostname"]);
	$rulename=strip_rulename($dansg->Master_rules_index[$rule_main]);
	
	
$html="
	<input type='hidden' name='rule_main' value='$rule_main'>
	<p class=caption>{categories_explain}</p>
	<table style='width:100%'>
	<tr>
		<td><span id='categories_field_list'>" .main_rules_categories_fieldlist(1) . "</span></td>
		<td align='left' width=1%>
		". imgtootltip("plus-24.png","{add_category}","dansguardian_addcategory()")."
		
	</td>
	</tr>
	</table><br>
	<div id='main_rules_categories_list' style='width:100%;height:250px;overflow:auto'>".main_rules_categories_list("$rule_main",1)."</div>
	";	




$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body("<br>$html<br>$categ<br>","dansguardian.index.php");}
	echo $tpl->_ENGINE_parse_body("$html<br>$categ<br>");	
	
	
}

function main_rules_categories_fieldlist($noecho=0){
	$dans=new dansguardian_rules();
	$array=$dans->array_blacksites;
	$dansguardian=new dansguardian();
	$dansguardian->DefinedCategoryBlackListLoad();
	
	if(is_array($dansguardian->UserCategoryBlackList)){
		while (list ($num, $val) = each ($dansguardian->UserCategoryBlackList) ){
			$array[$val]=$val;
		}
	}
	
	
	if($noecho==1){
		return Field_array_Hash($array,'blacklist',null,null,null,0,"font-size:13px;padding:3px");
	}else{
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body(Field_array_Hash($array,'blacklist',null));
	}
	
}
function main_rules_categories_list($rule_main,$noecho=0){
	$dansguardian=new dansguardian();
	$array_categories_user=$dansguardian->DefinedCategoryBlackListLoad();
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	//bannedsitelist
	
	$q=new mysql();
	$sql="SELECT * FROM dansguardian_files WHERE filename='bannedsitelist' AND RuleID=$rule_main";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
$style=CellRollOver();
	$categ="
	<table style='width:99%'>";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){		
		$num=$ligne["ID"];
		$val=$ligne["pattern"];
		if(trim($val)==null){continue;}
		$delete_icon=imgtootltip("ed_delete.gif","{delete}","dansguardian_delcategory('$hostname','$rule_main','$num')");
		$edit_icon=imgtootltip("icon_edit.gif","{edit}","dansguardian_edit_user_category('$val')");
		
		
		if(trim($array_categories_user[$val])<>null){
			
				$categ=$categ .
				 "<tr $style>
					 <td width=1% valign='top'><img src='img/red-pushpin-24.png'></td>				
					 	<td valign='top'><span style='font-size:13px;font-weight:bold'>$val:{$dans->array_blacksites[$val]}</span></td>
						<td valign='top'>$delete_button</td>
						<td width=1%>$edit_icon</td>
						<td width=1%>$delete_icon</td>
					</tr>
					";	
			
			continue;
		}
		$categ=$categ . 
		"<tr $style>
			<td width=1% valign='top'><img src='img/red-pushpin-24.png'></td>				
				<td valign='top'><span style='font-size:13px;font-weight:bold'>$val:{$dans->array_blacksites[$val]}</span></td>
				<td valign='top'>$delete_button</td>
				<td width=1%>&nbsp;</td>
				<td width=1%>$delete_icon</td>
			</tr>
				";
			
		}
		
	
	$categ="<div style='height:500px'>$categ</div>";
	
$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body("$categ");}
	echo $tpl->_ENGINE_parse_body("$categ");		
	
	}
	
function main_rules_addcategory(){
	writelogs("add new category ->{$_GET["rule_main"]}={$_GET["DansGuardian_addcategory"]} ",__FUNCTION__,__FILE__);
	$dans=new dansguardian_rules($_GET["hostname"],$_GET["rule_main"]);
	$dans->AddCategory($_GET["DansGuardian_addcategory"],$_GET["rule_main"]);
	}	

	
function main_rules_delcategory(){
	$dans=new dansguardian_rules($_GET["hostname"],$_GET["rule_main"]);
	$dans->DelCategory($_GET["rule_main"],$_GET["DansGuardian_delcategory"]);
}




	
	
?>