<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.httpd.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.ini.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==false){die();}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["tri"])){echo events_table();exit;}

if(isset($_GET["ShowID"])){ShowID();exit;}



js();	


		
function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{artica_events}');
	$start="artica_events_start()";
	if(isset($_GET["in-front-ajax"])){
		$start="artica_events_start2()";
	}
	
	$html="
	
	function artica_events_start(){
	 	YahooWin5('750','$page?popup=yes&without-tri={$_GET["without-tri"]}','$title');
	}
	
	function artica_events_start2(){
		$('#BodyContent').load('$page?popup=yes');
	}
	 
	 function tripar(){
	 	var context=document.getElementById('context').value;
	 	var process=document.getElementById('process').value;
	 	LoadAjax('articaevents','$page?tri=yes&context='+context+'&process='+process);
	 
	}
	
	function articaShowEvent(ID){
		 YahooWin6('750','$page?ShowID='+ID,'$title::'+ID);
	}
	 
	
	
	$start;";
	
	echo $html;	
	
}

function popup(){
	$sock=new sockets();
	$datas=$sock->APC_GET(md5(__FILE__.__FUNCTION__),10);
	if($datas<>null){echo $datas;return;}
	
	
	$table=events_table();
	$html="<H1>{artica_events}</H1>
	
	<div style='width:100%;height:500px;overflow:auto' id='articaevents'>$table</div>
	
	
	";
	
	$tpl=new templates();
	$datas=$tpl->_ENGINE_parse_body($html);
	$sock->APC_SAVE(md5(__FILE__.__FUNCTION__),$html);
	echo $datas;	
	
}

function events_table(){
	
	
	$q=new mysql();
	if($_GET["without-tri"]==null){
			$sql="SELECT process FROM events GROUP BY process ORDER BY process";
			$results=$q->QUERY_SQL($sql,"artica_events");	
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["process"]==null){$ligne["process"]="{unknown}";}
					$text_interne=$ligne["process"];
				$text_externe=$ligne["process"];
				if($text_externe=="class.templates.inc"){$text_externe="system";}
				if($text_externe=="process1"){$text_externe="watchdog";}
			
				$arr[$text_interne]=$text_externe;
			}
			$arr[null]="{select}";
			
			$sql="SELECT context FROM events GROUP BY context ORDER BY context";
			$results=$q->QUERY_SQL($sql,"artica_events");	
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["context"]==null){$ligne["context"]="{unknown}";}
			
				$text_interne=$ligne["context"];
				$text_externe=$ligne["context"];
			
				$arr1[$text_interne]=$text_externe;
			}
			
					
			$arr1[null]="{select}";
			
			$field_process=Field_array_Hash($arr,'process',$_GET["process"],"tripar()");
			$field_context=Field_array_Hash($arr1,'context',$_GET["context"],"tripar()");
			
			
			
		
			
		
			$html="
			<table style='width:99%'>
				<tr>
					<td class=legend>{process}:</td>
					<td>$field_process</td>
					<td class=legend>{context}:</td>
					<td>$field_context</td>			
				</tr>
			</table>
			<br>
			";
	}
	
	if($_GET["process"]<>null){$pp1=" AND process='{$_GET["process"]}'";}
	if($_GET["context"]<>null){$pp2=" AND context='{$_GET["context"]}'";}
	$html=$html."<table style='width:99%'>";
	$sql="SELECT * FROM events WHERE 1 $pp2$pp1 ORDER by zDate DESC LIMIT 0,300";
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	
	$tt=date('Y-m-d');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["process"]==null){$ligne["process"]="{unknown}";}
		$original_date=$ligne["zDate"];
		$ligne["zDate"]=str_replace($tt,'{today}',$ligne["zDate"]);
		
		if(preg_match("#\[.+?\]:\s+\[.+?\]:\s+\((.+?)\)\s+:(.+)#",$ligne["text"],$re)){
			$ligne["text"]=$re[2];
			$computer=$re[1];
		}
		
		if(preg_match("#\[.+?\]:\s+\[.+?\]:\s+\((.+?)\)\:\s+(.+)#",$ligne["text"],$re)){
			$ligne["text"]=$re[2];
			$computer=$re[1];
		}
		
		if(preg_match("#\[.+?\]:\s+\[.+?\]:\s+\((.+?)\)\s+(.+)#",$ligne["text"],$re)){
			$ligne["text"]=$re[2];
			$computer=$re[1];
		}
		
		if(preg_match("#\[.+?\]:\s+\[.+?\]:\((.+?)\)\s+(.+)#",$ligne["text"],$re)){
			$ligne["text"]=$re[2];
			$computer=$re[1];
		}
		
		
		if(preg_match("#\[.+?\]:\s+\[.+?\]:\s+(.+)#",$ligne["text"],$re)){
			$ligne["text"]=$re[1];
			
		}
		
		$affiche_text=$ligne["text"];
		if(strlen($affiche_text)>90){$affiche_text=substr($affiche_text,0,85).'...';}
		
		$tooltip="<li><strong>{date}:&nbsp;$original_date</li><li><strong>{computer}:&nbsp;$computer</strong></li><li><strong>{process}:&nbsp;{$ligne["process"]}</li>";
		$tooltip=$tooltip."<li><strong>{context}:&nbsp;{$ligne["context"]}</strong></li><hr>{click_to_display}<hr>";
		$tooltip=$tooltip."<div style=font-size:9px;padding:3px>{$ligne["text"]}</div>";
		
		$ID=$ligne["ID"];
		$js="articaShowEvent($ID);";
		
		$affiche_text=texttooltip($affiche_text,$tooltip,$js,null,0,"font-size:13px");
		
		
		$html=$html . "<tr . " .CellRollOver().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td valign='top' nowrap style='font-size:13px' width=1%>{$ligne["zDate"]}</td>
		<td valign='top' nowrap style='font-size:13px'>$affiche_text</td>
		</tR>
		
		";
		
	}
	$html=$html . "</table>";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}

function ShowID(){
	$id=$_GET["ShowID"];
	$sql="SELECT * FROM events WHERE ID=$id";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
	
	$subject=$ligne["text"];
	
	
	if(preg_match("#<body>(.+?)</body>#is",$ligne["content"],$re)){
		$content=$re[1];
	}
	
	
	if($content==null){
		$tbl=explode("\n",$ligne["content"]);
			if(is_array($tbl)){
				while (list ($index, $line) = each ($tbl) ){
				$content=$content."<div><code>". htmlentities(stripslashes($line))."</code></div>";
			
				}
			}
		}
	
	$html="<H3>$subject</H3>
	<hr>
	<div style='width:92%;height:450px;overflow:auto;margin:5px;padding:5px'>
	$content
	</div>
	
	
	";
	
	echo $html;
	
	
}


//ChangeSuperSuser	
	
?>	

