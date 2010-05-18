<?php
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.ldap.inc');
include_once ("ressources/jpgraph-3/src/jpgraph.php");
include_once ("ressources/jpgraph-3/src/jpgraph_pie.php");
include_once ("ressources/jpgraph-3/src/jpgraph_pie3d.php");
include_once ("ressources/jpgraph-3/src/jpgraph_line.php");
include_once ("ressources/class.templates.inc");
include_once('ressources/charts.php');

if(isset($_GET["view-stats"])){view_stats();exit;}

js();



	
function js(){
$tpl=new templates();	


if(!privs()){
	$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
	echo "alert('$error');";
	die();
}
	$ou=$_GET["ou"];
	$page=CurrentPageName();
	$tile=$tpl->_ENGINE_parse_body("{statistics}:$ou");
	$suffix=md5($ou);
	$html="
	function Stat$suffix(){
		YahooWin5(750,'$page?view-stats=$ou&ou=$ou','$tile');	
		
	}
	
	Stat$suffix();
	";
	
echo $html;	
	
}


function view_stats(){
	if(!privs()){die();}
	$ou=$_GET["ou"];
	$users=new usersMenus();
	
	$graph1=InsertChart('js/charts.swf',
	"js/charts_library","listener.graphs.php?ORG_MAIL_STAT=$ou&G=1",
	300,167,"",true,$users->ChartLicence);		
	
	$graph2=InsertChart('js/charts.swf',
	"js/charts_library","listener.graphs.php?ORG_MAIL_STAT=$ou&G=2",
	300,167,"",true,$users->ChartLicence);	

	$graph3=InsertChart('js/charts.swf',
	"js/charts_library","listener.graphs.php?ORG_MAIL_STAT=$ou&G=3",
	600,167,"",true,$users->ChartLicence);		
	
$html="<H1>{statistics}:$ou</H1>

<table style='width:100%'>
<tr>
	<td valign='top' align='center'>
	<h3>{today} {flow}</h3>
	<div style='padding:3px;margin:3px;background-color:white;border:1px solid #CCCCCC'>$graph1</div>
	</td>
	<td valign='top' align='center'>
	<h3>{topten} spam domains</h3>
	<div style='padding:3px;margin:3px;background-color:white;border:1px solid #CCCCCC'>$graph2</div>
	</td>	
</tr>
<tr>
	<td colspan=2 align='center'>
		<h3>{flow}:{hourly}</h3>
		<div style='padding:3px;margin:3px;background-color:white;border:1px solid #CCCCCC'>$graph3</div>
</tr>
</table>

";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);

	
	
}

function privs(){
$usersmenus=new usersMenus();
$ou=$_GET["ou"];
$niprov=false;
if(($usersmenus->AllowAddUsers) OR ($usersmenus->AsOrgAdmin)){	
	$niprov=true;
}

if(!$niprov){return false;}

if($_SESSION["uid"]<>-100){
		if($_SESSION["ou"]<>$ou){
		$niprov=false;
		}
	}
return $niprov;
}
	
	
	
	
	
	
	
	
	
function graph1(){
$ldap=new clladp();
$domains=$ldap->Hash_domains_table($ou);
$users=new usersMenus();
$users->LoadModulesEnabled();
$tpl=new templates();
if($users->EnableAmavisDaemon==0){die($tpl->_ENGINE_parse_body('{only_with_amavis}'));}
if($users->EnableMysqlFeatures==0){die($tpl->_ENGINE_parse_body('{only_with_mysql}'));}
if(!is_array($domains)){return null;}
while (list ($num, $line) = each ($domains) ){if($num==null){continue;}$domain[]=$num;}		
					
$sql_doms=get_domains_array_sql($domain);

$sql="SELECT COUNT(ID) as tcount,DATE_FORMAT(zDate,'%d') as tday FROM mails_events WHERE DATE_FORMAT(zDate,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m') AND ($sql_doms) GROUP BY tday ORDER BY tday";
$s=new mysql();
$results=$s->QUERY_SQL($sql,"artica_events");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$flow[]=$ligne["tcount"];
			$flow_spam[$ligne["tday"]]=0;
			$flow_bann[$ligne["tday"]]=0;
			$datax[]=$ligne["tday"];
			}
if(count($flow)==0){die("no datas...");}

$sql="SELECT COUNT(ID) as tcount,DATE_FORMAT(zDate,'%d') as tday FROM mails_events WHERE DATE_FORMAT(zDate,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m') AND ($sql_doms) AND spam=1 GROUP BY tday ORDER BY tday";
$s=new mysql();
$results=$s->QUERY_SQL($sql,"artica_events");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$flow_spam[$ligne["tday"]]=$ligne["tcount"];
			}
			
$sql="SELECT COUNT(ID) as tcount,DATE_FORMAT(zDate,'%d') as tday FROM mails_events WHERE DATE_FORMAT(zDate,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m') AND ($sql_doms) AND banned=1 GROUP BY tday ORDER BY tday";
$s=new mysql();
$results=$s->QUERY_SQL($sql,"artica_events");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$flow_bann[$ligne["tday"]]=$ligne["tcount"];
			}			
			
while (list ($num, $ligne) = each ($flow_bann) ){
	$bann[]=$ligne;
}
while (list ($num, $ligne) = each ($flow_spam) ){
	$spam[]=$ligne;
}



$graph = new Graph(600,200,"auto");
$graph->SetScale("textlin");
$lineplot=new LinePlot($flow);
$lineplot->SetColor("green");
$lineplot->SetWeight(1);
$lineplot->SetLegend("Flow");
$graph->Add($lineplot);

if(count($spam)>0){
	$lineplot2=new LinePlot($spam);
	$lineplot2->SetColor("red");
	$lineplot2->SetWeight(1);
	$lineplot2->SetLegend("SPAM");	
	$graph->Add($lineplot2);
}

if(count($bann)>0){
	$lineplot3=new LinePlot($bann);
	$lineplot3->SetColor("orange");
	$lineplot3->SetWeight(1);
	$lineplot3->SetLegend("BANN");	
	$graph->Add($lineplot3);
}


$graph->xaxis->SetTickLabels($datax);
$graph->title->Set("eMail flow (month)");
$graph->xaxis->title->Set("date");
$graph->yaxis->title->Set("mails number");
$graph->SetFrame(false); 
$graph->StrokeCSIM();

}


function get_domains_array_sql($domains){
	
while (list ($num, $ligne) = each ($domains) ){
	if($ligne==null){continue;}
	$recieve[]="OR 	rcpt_to_domain='$ligne'";
	}	
$recieve[0]=str_replace("OR ",'',$recieve[0]);	

return implode(" ",$recieve);

}

//top senders domains
function graph2(){
$ldap=new clladp();
$domains=$ldap->Hash_domains_table($ou);
$users=new usersMenus();
$users->LoadModulesEnabled();
$tpl=new templates();
if($users->EnableAmavisDaemon==0){die($tpl->_ENGINE_parse_body('{only_with_amavis}'));}
if($users->EnableMysqlFeatures==0){die($tpl->_ENGINE_parse_body('{only_with_mysql}'));}
if(!is_array($domains)){return null;}
while (list ($num, $line) = each ($domains) ){if($num==null){continue;}$domain[]=$num;}		
					
$sql_doms=get_domains_array_sql($domain);
	
$sql="SELECT COUNT(ID) as tcount,mailfrom_domain FROM mails_events WHERE DATE_FORMAT(zDate,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m') 
AND ( $sql_doms) GROUP BY mailfrom_domain ORDER BY COUNT(ID) DESC LIMIT 0,10";
	
$s=new mysql();
$results=$s->QUERY_SQL($sql,"artica_events");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$data[]=$ligne["tcount"];
			$textes[]=$ligne["mailfrom_domain"];
			}	
if(count($data)==0){die("no datas");}
$title='Top Senders domains (monthly)';
$graph = new PieGraph(600,300,'auto');
//$graph->SetShadow();
$graph->title->Set($title);
$graph->title->SetFont(FF_FONT1,FS_BOLD);
$p1 = new PiePlot3D($data);
$p1->SetLabels($textes,1); 
$p1->SetEdge('black',0); 
$p1->SetAngle(55); 
$p1->SetLabelMargin(2); 
$p1->SetCenter(0.4,0.5);
$p1->ExplodeAll(10); 
$graph->Add($p1);
$graph->SetFrame(false); 
$graph->StrokeCSIM();


}
	

	
?>	

