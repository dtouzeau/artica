<?php
include_once(dirname(__FILE__)."/frame.class.inc");



if(isset($_GET["dump-database"])){database_list();exit;}
function database_list(){
	$db="/var/milter-greylist/greylist.db";
	$datas=file_get_contents($db);
	
	$tbl=explode("\n",$datas);
	if(!is_array($tbl)){return null;}
	
	while (list ($num, $line) = each ($tbl) ){
		if(trim($line)==null){continue;}
		if(preg_match("#greylisted tuples#",$line)){$KEY="GREY";continue;}
		if(preg_match("#Auto-whitelisted tuples#",$line)){$KEY="WHITE";continue;}
		
		if(preg_match("#([0-9\.]+)\s+<(.+?)>\s+<(.+?)>#",$line,$re)){
			$conf[]="\$MGREYLIST_DB[\"$KEY\"][]=array('{$re[1]}','{$re[2]}','{$re[3]}');";
		}
	}
	
	$file="<?php\n";
	if(is_array($conf)){
	$file=$file.implode("\n",$conf);
	}
	$file=$file."\n";
	$file=$file."?>";
	
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/mgrelist-db.inc",$file);
	@chmod("/usr/share/artica-postfix/ressources/logs/mgrelist-db.inc",0755);
	
	
}

?>