<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==false){header('location:users.index.php');exit;}


if(isset($_GET["js"])){echo js();exit;}
if(isset($_GET["popup"])){popup();exit;}


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{memory_info}');
	$html="
	 YahooWin5('500','$page?popup=yes','$title');
	 
	
	
	";
	
	echo $html;
}

function popup(){
	$table_memory=table_memory();
	$html="<H1>{memory_info}</H1>
	
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/bg_memory-250.png'></td>
		<td valign='top'>". RoundedLightWhite($table_memory)."</td>
	</tr>
	</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


applications_Status();	

function applications_Status(){
	$tpl=new templates();
	
	
	
	
	$html="
	<div class=caption style='float:right'>{memory_info_text}</div>
		<h4>{memory_info}</H4>
		<table style='width:100%'>
		<tr>
		<td valign='top' width=1%><img src='img/bg_memory.jpg'></td>
		<td valign='top'>
		".table_memory()."
		
			</td>
		</tr>
	</table>
	";
	$tpl=new template_users('{memory_info}',$html);
	echo $tpl->web_page;
	
}

function table_memory(){
	$sys=new systeminfos();
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("<table  style='width:99%;border-left:2px solid #CCCCCC'>
				<tr><td colspan=2><h4>{physical_memory}</td></tr>
				<tr>
					<td class=legend>{total}</strong></td>
					<td class=legend>$sys->memory_total Mb</strong></td>	
				</tr>
				<tr>
					<td class=legend>{free}</strong></td>
					<td class=legend>$sys->memory_free Mb</strong></td>	
				</tr>	
				<tr>
					<td class=legend>{used}</strong></td>
					<td class=legend>$sys->memory_used Mb</strong></td>	
				</tr>					
				<tr>
					<td class=legend>{shared}</strong></td>
					<td class=legend>$sys->memory_shared Mb</strong></td>	
				</tr>
				<tr>
					<td class=legend>{cached}</strong></td>
					<td class=legend>$sys->memory_cached Mb</strong></td>	
				</tr>	
				<tr><td colspan=2><h4>{swap_memory}</td></tr>				
				<tr>
					<td class=legend>{total}:</strong></td>
					<td class=legend>$sys->swap_total Mb</strong></td>	
				</tr>	
				<tr>
					<td class=legend>{free}:</strong></td>
					<td class=legend>$sys->swap_free Mb</strong></td>	
				</tr>
				<tr>
					<td class=legend>{used}:</strong></td>
					<td class=legend>$sys->swap_used Mb</strong></td>	
				</tr>																									
			</table>");
	
}


function disk(){
$tpl=new templates();
	$sys=new systeminfos();
	$hash=$sys->DiskUsages();	
	if(!is_array($hash)){return null;}
	$img="<img src='img/fw_bold.gif'>";
	$html="<H4>{disks_usage}:</h4>
	<table style='width:600px' align=center>
	<tr style='background-color:#CCCCCC'>
	<td>&nbsp;</td>
	<td class=legend>{Filesystem}:</strong></td>
	<td class=legend>{size}:</strong></td>
	<td class=legend>{used}:</strong></td>
	<td class=legend>{available}:</strong></td>
	<td align='center'><strong>{use%}:</strong></td>
	<td class=legend>{mounted_on}:</strong></td>
	</tr>
	";
	
	 while (list ($num, $ligne) = each ($hash) ){
	 	$html=$html . "<tr " . CellRollOver().">
	 	<td width=1% class=bottom>$img</td>
	 	<td class=bottom>{$ligne[0]}:</td>
	 	<td class=bottom>{$ligne[2]}:</td>
	 	<td class=bottom>{$ligne[3]}:</td>
	 	<td class=bottom>{$ligne[4]}:</td>
	 	<td align='center' class=bottom><strong>{$ligne[5]}:</strong></td>
	 	<td class=bottom>{$ligne[6]}:</td>
	 	</tr>
	 	";
	 	
	 }
	return $html . "</table>";
	
}
	