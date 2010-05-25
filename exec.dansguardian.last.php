<?php


if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.system.network.inc");
include_once(dirname(__FILE__)."/ressources/class.dansguardian.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__)."/ressources/class.rtmm.tools.inc");
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");

	
	if(systemMaxOverloaded()){
		writelogs("This system is too many overloaded, die()",__FUNCTION__,__FILE__,__LINE__);
		die();
	}	
	
	$sock=new sockets();
	$SQUIDEnable=$sock->GET_INFO("SQUIDEnable");
	if($SQUIDEnable==null){$SQUIDEnable=1;}
	if($SQUIDEnable==0){die();}
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	
writelogs("Start running statistics for dansGuardian",basename(__FILE__),__FILE__,__LINE__);	
$sql="SELECT sitename,country,uri,uid,remote_ip,CLIENT,TYPE,REASON,DATE_FORMAT(zDate,'%H:%i:%s') as tdate,QuerySize FROM dansguardian_events ORDER BY zDate DESC LIMIT 0,100";
$html=GetToday()."
<div style='width:100%;height:300px;overflow:auto'>
<table style='width:100%'>";
$q=new mysql();
writelogs("running master query",__FUNCTION__,__FILE__,__LINE__);
$results=$q->QUERY_SQL($sql,"artica_events");
if(!$q->ok){die("Wrong query");}



	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$count=$count+1;
		$Country=$ligne["country"];
		$country_img=GetFlags($Country);
		
		
		
		if($ligne["TYPE"]==null){$ligne["TYPE"]="PASS";}
		if(preg_match("#EXCEPTION#",$ligne["TYPE"])){$ligne["TYPE"]="PASS";}
		
		switch (strtolower($ligne["TYPE"])) {
			case "pass":$roll=CellRollOver();$color="black";break;
			case "not modified":$roll=CellRollOver();$color="black";break;
			case "moved temporarily":$roll=CellRollOver();$color="black";break;
			default:$roll=CellRollOver_jaune() ." style='color:#B70C25'";$color="#B70C25";break;
		}
		
		$sitename=texttooltip($ligne["sitename"],"{$ligne["uri"]}<br>{$ligne["remote_ip"]}",null,null,0,"font-weight:bold;color:$color");
		$ligne["TYPE"]=texttooltip($ligne["TYPE"],$ligne["REASON"],null,null,0,"font-weight:bold;color:$color");		
		$time=$ligne["tdate"];
		$QuerySize=$ligne["QuerySize"];
		if($QuerySize==null){$QuerySize="-";}else{$QuerySize=FormatBytes($QuerySize/1024);}
		
		$mailfrom=$ligne["CLIENT"];
		if($ligne["remote_ip"]==null){$ligne["remote_ip"]=$ligne["sitename"];}
		$flag_infos="$Country {$ligne["remote_ip"]}";
		if($ligne["REASON"]==null){$ligne["REASON"]="&nbsp;";}
		if($ligne["uid"]<>null){$mailfrom=$ligne["uid"];}
		$html=$html. "
		<tr  $roll>
		<td width=1%>" . imgtootltip($country_img,$flag_infos)."</td>
		<td width=1% nowrap><strong>$time</strong></td>
		<td nowrap><strong>$mailfrom</td>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td nowrap><strong>{$ligne["remote_ip"]}</td>
		<td nowrap><strong>$sitename</td>
		<td nowrap><strong>{$ligne["TYPE"]}</td>
		<td nowrap><strong>{$ligne["REASON"]}</td>
		<td nowrap><strong>$QuerySize</td>
		</tr>";
		
	}

$html=$html."</table></div>";
$target_file="/usr/share/artica-postfix/ressources/logs/dansguardian-rtmm.html";
file_put_contents($target_file,$html);
chmod($target_file,0755);
die();

function GetToday(){
	$q=new mysql();
	$sql="SELECT COUNT( ID ) as tcount, DATE_FORMAT( zDate, '%h' ) FROM dansguardian_events WHERE DATE_FORMAT( zDate, '%Y-%m-%d' ) = DATE_FORMAT( NOW( ) , '%Y-%m-%d')";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));
	$hits=$ligne["tcount"];	
	writelogs("hits=$hits",__FUNCTION__,__FILE__,__LINE__);
	
	$sql="SELECT COUNT( ID ) as tcount, DATE_FORMAT( zDate, '%h' ) FROM dansguardian_events WHERE DATE_FORMAT( zDate, '%Y-%m-%d' ) = DATE_FORMAT( NOW( ) , '%Y-%m-%d') 
	AND TYPE='DENIED'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));
	$denied=$ligne["tcount"];	
	
	$hits=texttooltip($hits,'{dansguardian_statistics}',"Loadjs('dansguardian.stats.php')",null,0,"font-weight:bold");
	
	$html="
	<hr>
	<H3>{today}</H3>
	<table style='width:100%'>
	<tr>
		<td width=1%>
		". imgtootltip("statistics2-32.png",'{dansguardian_statistics}',"Loadjs('dansguardian.stats.php')")."</td>
	</td>
	<td valign='top'>
	<table style='width:100%'>
	<tr>
		<td class=legend>{hits_number}</td>
		<td><strong>$hits</strong>
		<td class=legend>{denied}</td>
		<td><strong>$denied</strong>
	</tr>	
	</table>
	</td>
	</tr>
	</table>
	<hr>
	";
	
	return $html;
	
	
	
}


?>