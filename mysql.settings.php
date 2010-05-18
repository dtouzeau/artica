<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.backup.inc');
	include_once('ressources/class.os.system.inc');
	include_once('ressources/class.mysql-server.inc');
	include_once('ressources/class.system.network.inc');
	
	
	
	$usersprivs=new usersMenus();
	if(!$usersprivs->AsSystemAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";
		die();
		
	}
	
	if(isset($_GET["skip-external-locking"])){save();exit;}
	
	if(isset($_GET["popup"])){popup();exit;}
	
	
	js();
	
	
function js(){

$page=CurrentPageName();
$prefix=str_replace(".","_",$page);
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{mysql_settings}');	





$html="

function {$prefix}Load(){
		YahooWin(600,'$page?popup=yes','$title');
	
	}
	

	
var x_SaveUMysqlParameters= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	{$prefix}Load();
	}
	


function SaveUMysqlParameters(){
	var XHR = new XHRConnection();
	if(document.getElementById('skip-external-locking')){XHR.appendData('skip-external-locking',document.getElementById('skip-external-locking').value);}
	if(document.getElementById('key_buffer')){XHR.appendData('key_buffer',document.getElementById('key_buffer').value);}
	if(document.getElementById('innodb_buffer_pool_size')){XHR.appendData('innodb_buffer_pool_size',document.getElementById('innodb_buffer_pool_size').value);}
	if(document.getElementById('innodb_additional_mem_pool_size')){XHR.appendData('innodb_additional_mem_pool_size',document.getElementById('innodb_additional_mem_pool_size').value);}
	if(document.getElementById('read_rnd_buffer_size')){XHR.appendData('read_rnd_buffer_size',document.getElementById('read_rnd_buffer_size').value);}
	if(document.getElementById('table_cache')){XHR.appendData('table_cache',document.getElementById('table_cache').value);}
	if(document.getElementById('tmp_table_size')){XHR.appendData('tmp_table_size',document.getElementById('tmp_table_size').value);}
	if(document.getElementById('max_allowed_packet')){XHR.appendData('max_allowed_packet',document.getElementById('max_allowed_packet').value);}
	if(document.getElementById('max_connections')){XHR.appendData('max_connections',document.getElementById('max_connections').value);}
	if(document.getElementById('myisam_sort_buffer_size')){XHR.appendData('myisam_sort_buffer_size',document.getElementById('myisam_sort_buffer_size').value);}
	if(document.getElementById('net_buffer_length')){XHR.appendData('net_buffer_length',document.getElementById('net_buffer_length').value);}
	if(document.getElementById('sort_buffer_size')){XHR.appendData('sort_buffer_size',document.getElementById('sort_buffer_size').value);}
	if(document.getElementById('join_buffer_size')){XHR.appendData('join_buffer_size',document.getElementById('join_buffer_size').value);}
	if(document.getElementById('read_buffer_size')){XHR.appendData('read_buffer_size',document.getElementById('read_buffer_size').value);}
	if(document.getElementById('key_buffer_size')){XHR.appendData('key_buffer_size',document.getElementById('key_buffer_size').value);}
	if(document.getElementById('thread_cache_size')){XHR.appendData('thread_cache_size',document.getElementById('thread_cache_size').value);}
	if(document.getElementById('query_cache_limit')){XHR.appendData('query_cache_limit',document.getElementById('query_cache_limit').value);}
	if(document.getElementById('query_cache_size')){XHR.appendData('query_cache_size',document.getElementById('query_cache_size').value);}
	if(document.getElementById('table_open_cache')){XHR.appendData('table_open_cache',document.getElementById('table_open_cache').value);}
	if(document.getElementById('bind-address')){XHR.appendData('bind-address',document.getElementById('bind-address').value);}		
	document.getElementById('mysqlsettings').innerHTML='<center><img src=img/wait_verybig.gif></center>';
	XHR.sendAndLoad('$page', 'GET',x_SaveUMysqlParameters);	
}

{$prefix}Load();";
	
echo $html;	
}


function popup(){
	
	$mysql=new mysqlserver();
	$net=new networking();
	$array=$net->ALL_IPS_GET_ARRAY();
	
	$array[null]="{all}";
	
	$bind=Field_array_Hash($array,"bind-address",$mysql->main_array["bind-address"]);

//Les devs de mysql conseillent un key_buffer de la taille de la somme de tous les fichiers .MYI dans le repertoire mysql.	
	
	$hover=CellRollOver();
$form="	<table style='width:100%'>
	<tr $hover>
		<td class=legend>{skip-external-locking}:</td>
		<td>". Field_yesno_checkbox('skip-external-locking',$mysql->main_array["skip_external_locking"])."</td>
		<td><code>skip-external-locking</code></td>
		<td>". help_icon('{skip-external-locking_text}')."</td>
	</tr>
	<tr $hover>
		<td class=legend>{bind-address}:</td>
		<td>$bind</td>
		<td><code>bind-address</code></td>
		<td>&nbsp;</td>
	</tr>	
	<tr $hover>
		<td class=legend>{key_buffer}:</td>
		<td>". Field_text("key_buffer",$mysql->main_array["key_buffer"],"width:60px")."&nbsp;M</td>
		<td><code>key_buffer</code></td>
		<td>". help_icon('{key_buffer_text}')."</td>
	</tr>
	<tr $hover>
		<td class=legend>{key_buffer_size}:</td>
		<td>". Field_text("key_buffer_size",$mysql->main_array["key_buffer_size"],"width:60px")."&nbsp;M</td>
		<td><code>key_buffer_size</code></td>
		<td>". help_icon('{key_buffer_size_text}')."</td>
	</tr>		
	<tr $hover>
		<td class=legend>{innodb_buffer_pool_size}:</td>
		<td>". Field_text("innodb_buffer_pool_size",$mysql->main_array["innodb_buffer_pool_size"],"width:60px")."&nbsp;M</td>
		<td><code>innodb_buffer_pool_size</code></td>
		<td>". help_icon('{innodb_buffer_pool_size_text}')."</td>
	</tr>
	
	<tr $hover>
		<td class=legend>{innodb_additional_mem_pool_size}:</td>
		<td>". Field_text("innodb_additional_mem_pool_size",$mysql->main_array["innodb_additional_mem_pool_size"],"width:60px")."&nbsp;M</td>
		<td><code>innodb_additional_mem_pool_size</code></td>
		<td>". help_icon('{innodb_additional_mem_pool_size_text}')."</td>
	</tr>		
	
	<tr $hover>
		<td class=legend>{myisam_sort_buffer_size}:</td>
		<td>". Field_text("myisam_sort_buffer_size",$mysql->main_array["myisam_sort_buffer_size"],"width:60px")."&nbsp;M</td>
		<td><code>myisam_sort_buffer_size</code></td>
		<td>". help_icon('{myisam_sort_buffer_size_text}')."</td>
	</tr>
	<tr $hover>
		<td class=legend>{sort_buffer_size}:</td>
		<td>". Field_text("sort_buffer_size",$mysql->main_array["sort_buffer_size"],"width:60px")."&nbsp;M</td>
		<td><code>sort_buffer_size</code></td>
		<td>". help_icon('{sort_buffer_size_text}')."</td>
	</tr>	
	<tr $hover>
		<td class=legend>{join_buffer_size}:</td>
		<td>". Field_text("join_buffer_size",$mysql->main_array["join_buffer_size"],"width:60px")."&nbsp;M</td>
		<td><code>join_buffer_size</code></td>
		<td>". help_icon('{join_buffer_size_text}')."</td>
	</tr>		
	<tr $hover>
		<td class=legend>{read_buffer_size}:</td>
		<td>". Field_text("read_buffer_size",$mysql->main_array["read_buffer_size"],"width:60px")."&nbsp;M</td>
		<td><code>read_buffer_size</code></td>
		<td>". help_icon('{read_buffer_size_text}')."</td>
	</tr>		
		<td class=legend>{query_cache_size}:</td>
		<td>". Field_text("query_cache_size",$mysql->main_array["query_cache_size"],"width:60px")."&nbsp;M</td>
		<td><code>query_cache_size</code></td>
		<td>". help_icon('{query_cache_size_text}')."</td>
	</tr>		
	
	
	<tr $hover>
		<td class=legend>{query_cache_limit}:</td>
		<td>". Field_text("query_cache_limit",$mysql->main_array["query_cache_limit"],"width:60px")."&nbsp;M</td>
		<td><code>query_cache_limit</code></td>
		<td>". help_icon('{query_cache_limit_text}')."</td>
	</tr>	
	
	

	
	
	
	
	
	<tr $hover>
		<td class=legend>{read_rnd_buffer_size}:</td>
		<td>". Field_text("read_rnd_buffer_size",$mysql->main_array["read_rnd_buffer_size"],"width:60px")."&nbsp;M</td>
		<td><code>read_rnd_buffer_size</code></td>
		<td>". help_icon('{read_rnd_buffer_size_text}')."</td>
	</tr>
	<tr $hover>
		<td class=legend>{table_cache}:</td>
		<td>". Field_text("table_cache",$mysql->main_array["table_cache"],"width:60px")."&nbsp;table(s)</td>
		<td><code>table_cache</code></td>
		<td>". help_icon('{table_cache}')."</td>
	</tr>
	
	<tr $hover>
		<td class=legend>{tmp_table_size}:</td>
		<td>". Field_text("tmp_table_size",$mysql->main_array["tmp_table_size"],"width:60px")."&nbsp;M</td>
		<td><code>tmp_table_size</code></td>
		<td>". help_icon('{tmp_table_size}')."</td>
	</tr>	
	<tr $hover>
		<td class=legend>{max_allowed_packet}:</td>
		<td>". Field_text("max_allowed_packet",$mysql->main_array["max_allowed_packet"],"width:60px")."&nbsp;M</td>
		<td><code>max_allowed_packet</code></td>
		<td>". help_icon('{max_allowed_packet}')."</td>
	</tr>	
	<tr $hover>
		<td class=legend>{max_connections}:</td>
		<td>". Field_text("max_connections",$mysql->main_array["max_connections"],"width:60px")."&nbsp;M</td>
		<td><code>max_connections</code></td>
		<td>". help_icon('{max_connections}')."</td>
	</tr>	
	<tr $hover>
		<td class=legend>{net_buffer_length}:</td>
		<td>". Field_text("net_buffer_length",$mysql->main_array["net_buffer_length"],"width:60px")."&nbsp;M</td>
		<td><code>net_buffer_length</code></td>
		<td>". help_icon('{net_buffer_length_text}')."</td>
	</tr>
	<tr $hover>
		<td class=legend>{thread_cache_size}:</td>
		<td>". Field_text("thread_cache_size",$mysql->main_array["thread_cache_size"],"width:60px")."&nbsp;M</td>
		<td><code>thread_cache_size</code></td>
		<td>". help_icon('{thread_cache_size_text}')."</td>
	</tr>
	<tr>
		<td colspan=4 align='right'>
		<hr>". button("{apply}","SaveUMysqlParameters()")."
		
		</td>
	</tr>
	</table>";	
	
	$html="<H1>{mysql_settings} v. $mysql->mysql_version_string ($mysql->mysqlvbin)</H1>
	<div id='mysqlsettings'>".RoundedLightWhite($form)."</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}
function save(){
			$mysql=new mysqlserver();
	while (list ($index, $line) = each ($_GET) ){

		$mysql->main_array[trim($index)]=trim($line);
		
	}
	
	$mysql->save();
	
}
	
?>