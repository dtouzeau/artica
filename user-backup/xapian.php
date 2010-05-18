<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
	include_once(dirname(__FILE__)."/ressources/class.xapian.inc");
	include_once(dirname(__FILE__)."/ressources/xapian.php");
	include_once(dirname(__FILE__)."/ressources/class.crypt.php");
	include_once(dirname(__FILE__)."/ressources/class.user.inc");
	include_once(dirname(__FILE__)."/ressources/class.samba.inc");
	

	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["XapianWords"])){XapianSearch();exit;}
	if(isset($_GET["XapianFileInfo"])){XapianFileInfo();exit;}
	if(isset($_GET["download-file"])){download_file();exit;}
js();


function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{find_documents}');
	$page=CurrentPageName();
	$html="
	
		function XapianPageLoad(){
			YahooWin(800,'$page?popup=yes','$title');
		
		}
		
		function StartXapianSearchCheck(e){
			if(checkEnter(e)){
				StartXapianSearch();
			}
		}
		
		var x_StartXapianSearch= function (obj) {
			var res=obj.responseText;
			document.getElementById('XapianDiv').innerHTML=res;
		}			
		
		function StartXapianSearch(){
			var XapianWords=document.getElementById('XapianWords').value;
			var XHR = new XHRConnection();
			document.getElementById('XapianDiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.appendData('XapianWords',XapianWords);
			XHR.sendAndLoad('$page', 'GET',x_StartXapianSearch);
		}
		
		function XapianFileInfo(file){
			YahooWin2(680,'$page?XapianFileInfo='+file,'$title');
		}
	
	XapianPageLoad();";
	
	echo $html;
	
}


function popup(){
	
	$html="
	<div style='background-image:url(img/find-documents-128.png);background-repeat:no-repeat;background-position:left top'>
	<center style='font-size:14px;margin:50px;'>
		". Field_text("XapianWords","","font-size:14px;width:350px;border:1px solid black",null,null,null,false,"StartXapianSearchCheck(event)").
	"
	<br><span style='font-size:11px'>{find_documents}</span>
	</center>
	<div id='XapianDiv' style='width:100%;height:450px;overflow:auto'></div>
	</div>
	
	
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}


function XapianSearch(){
$userid=$_SESSION["uid"];
	$ct=new user($userid);
	$page=CurrentPageName();
	
	/*if($userid<>null){
		$samba=new samba();
		$samba_folders=$samba->GetUsrsRights($userid);
	}*/
	
	$users=new usersMenus();
	
	writelogs("$userid ->AllowXapianDownload \"$users->AllowXapianDownload\"",__FUNCTION__,__FILE__);;
	$xapian=new XapianSearch();
	if(!is_file("/usr/share/artica-postfix/LocalDatabases/samba.db")){
		$xapian->add_database("/usr/share/artica-postfix/LocalDatabases/samba.db");
	}
	
	$current=$_GET["p"];
	if($current==null){$current=0;}
	$xapian->start=$current;
	
	$xapian->terms=$_GET["XapianWords"];
	$xapian->start=$current;
	if(count($xapian->databases)>0){
		$array=$xapian->search();
	}

	$maxdocs=$array["ESTIMATED"];
	$page_number=round($maxdocs/10);
	if($page_number<=1){$page_text="{page}";}else{$page_text="{pages}";}
	
if($page_number>1){
	$max=$page_number;
	if($max>10){$max=12;}	
	
for($i=0;$i<$max;$i++){
		if($i==$current){$class="id=tab_current";}else{$class=null;}
		$tab=$tab . "<li><a href=\"javascript:LoadAjax('XapianDiv','$page?p=$num&XapianWords={$_GET["XapianWords"]}&p=$i')\" $class>{page} $i</a></li>\n";
			
		}
		$tab="<div id=tablist>$tab</div>";		
	
}
	
	

	$table="
	<div style='width:95%;font-size:16px;padding:9px;margin-bottom:5px;text-align:right;border-bottom:1px solid #CCCCCC'>$maxdocs {results}&nbsp;|&nbsp;$page_number $page_text</div>
	$tab
	<table style='width:95%'>";
	if(is_array($array["RESULTS"])){
		while (list ($num, $arr) = each ($array["RESULTS"]) ){
			$DATA=$arr["DATA"];
			$PERCENT=$arr["PERCENT"];
			$PATH=$arr["PATH"];
			$TIME=$arr["TIME"];
			$SIZE=$arr["SIZE"];
			$endcoded_path=base64_encode($PATH);
			//if(!is_file($PATH)){continue;}
			$basename=basename($PATH);
			$ext=strtolower(Get_extension($basename));
			$img="img/ext/def_small.gif";
			
			if($users->AllowXapianDownload){
				$url="<a href='#' OnClick=\"javascript:XapianFileInfo('$endcoded_path')\">";
			}
			
			if(is_file("img/ext/{$ext}_small.gif")){$img="img/ext/{$ext}_small.gif";}
			$table=$table."
				<tr><td colspan=3>&nbsp;</td>
				<tr>
				<td width=1%><img src='$img'></td>
				<td width=1%><span style='font-size:16px'>$PERCENT%</span></td>
				<td width=99%><strong style='font-size:16px'>$url$basename</a></strong></td>
				</tr>
				<tr>
				<td width=1%>&nbsp;</td>
				<td colspan=2 width=99%><div style='font-size:11px;font-weight:normal'>$DATA</div></td>
				</tr>
				";
			
			
		}
		
		$table=$table."</table>";
	}
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($table);
	
}
function FormatResponse($ligne,$users,$pass){
	$f=new filesClass();
	$crypt=new SimpleCrypt($pass);
	$uri="<a href=\"download.attach.php?xapian-file=".$crypt->encrypt($ligne["PATH"])."\">";
	$text_deco="text-decoration:underline";
	
	
	
	if(!$users->AllowXapianDownload){
		$text_deco=null;
	}
	$ligne["PATH"]=str_replace("'",'`',$ligne["PATH"]);
	$title=substr($ligne["DATA"],0,60);
	$title="$uri<span style='color:#0000CC;$text_deco;font-size:medium'>{$ligne["PERCENT"]}%&nbsp;$title</span></a>";
	$body=$ligne["DATA"];
	$body=wordwrap($body, 100, "<br />\n");
	
	$img="img/file_ico/unknown.gif";
	$file=basename($ligne["PATH"]);
	$ext=$f->Get_extension(strtolower($file));
	if(is_file("img/file_ico/$ext.gif")){
			$img="img/file_ico/$ext.gif";
		}
	
	
	$html="
	
	<table style='width:99%;margin-top:6px'>
	<tr>
		<td>
		<table style='width:100%'>
		<tr>
			<td valign='top' width=1%><img src='$img'></td>
			<td valign='top' width=1%>" . imgtootltip("folderopen.gif","{path}:{$ligne["PATH"]}<br>{size}:{$ligne["SIZE"]}<br>{date}:{$ligne["TIME"]}")."</td>
			<td valing='top'>$title</td>
		</tr>
		</table>
	</tr>
	<tr>
	<td><span style='font-size:small;color:#676767;'>&laquo&nbsp;<strong>{$file}</strong>&nbsp;&raquo;&nbsp;-&nbsp;{$ligne["TIME"]}</span></td>
	</tr>
	<tr>
	<td style='font-size:11px;'>$body</td>
	</tr>
	<tr>
	<td style='font-size:small;color:green;' align='left'>{$ligne["TYPE"]} ({$ligne["SIZE"]})</td>
	</tr>	
	</table>
	";
	
	return $html;	
	
	
	
}

function XapianFileInfo(){
	if(!is_object($GLOBALS["USERMENUS"])){$users=new usersMenus();$GLOBALS["USERMENUS"]=$users;}else{$users=$GLOBALS["USERMENUS"];}
	if(!is_object($GLOBALS["SMBCLASS"])){$smb=new samba();$GLOBALS["SMBCLASS"]=$smb;}else{$smb=$GLOBALS["SMBCLASS"];}	
	$ldap=new clladp();
	$path=base64_decode($_GET["XapianFileInfo"]);
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?filestat=". base64_encode($path))));
	$type=base64_decode($sock->getFrameWork("cmd.php?filetype=". base64_encode($path)));	
	
	

	
$permissions=$array["perms"]["human"];
$permissions_dec=$array["perms"]["octal1"];
$accessed=$array["time"]["accessed"];
$modified=$array["time"]["modified"];
$created=$array["time"]["created"];
$file=$array["file"]["basename"];
$permissions_g=$array["owner"]["group"]["name"].":". $array["owner"]["owner"]["name"];
$ext=Get_extension($file);
$page=CurrentPageName();

$cr=new SimpleCrypt($ldap->ldap_password);
$path_encrypted=base64_encode($cr->encrypt($path));

$samba=new samba();
$samba_folders=$samba->GetUsrsRights($_SESSION["uid"]);

$download=Paragraphe("download-64.png","{download}","{download} $file<br>".FormatBytes($array["size"]["size"]/1024),"javascript:s_PopUp('$page?download-file=$path_encrypted',10,10)");
if(!IfDirectorySambaRights($samba_folders,$path)){
	$download=null;
}



		


$img="img/ext/def.jpg";
if(is_file("img/ext/$ext.jpg")){$img="img/ext/$ext.jpg";}

$html="<H1>$file</H1>
<code>$path</code>
<div style='font-size:11px;margin-top:3px;padding-top:5px;border-top:1px solid #CCCCCC;text-align:right;'><i>$type</i></div>
<table style='width:100%'>
<tr>
<td width=1% valign='top'><img src='$img' style='margin:15px'></td>
<td valign='top'>
<hr>
<table>
	<tr>
		<td class=legend>{permission}:</td>
		<td><strong>$permissions $permissions_g ($permissions_dec)</td>
	</tr>
	<tr>
		<td class=legend>{accessed}:</td>
		<td><strong>$accessed</td>
	</tr>
<tr><td class=legend>{modified}:</td><td><strong>$modified</td></tr>
<tr><td class=legend>{created}:</td><td><strong>$created</td></tr>
<tr>
	<td class=legend>{size}:</td>
	<td><strong>{$array["size"]["size"]} bytes (". FormatBytes($array["size"]["size"]/1024).")</td>
</tr>
<tr>
	<td class=legend>blocks:</td>
	<td><strong>{$array["size"]["blocks"]}</td>
</tr>	
<tr>
	<td class=legend>block size:</td>
	<td><strong>{$array["size"]["block_size"]}</td>
</tr>
</table>
</td>
<td valign='top'>
$download
</td>
</tr>
</table>";
$tpl=new templates();	
echo $tpl->_ENGINE_parse_body($html);
	
}

function IfDirectorySambaRights($samba_folders,$pathToCheck){
	while (list ($path, $rights) = each ($samba_folders) ){
		$path=str_replace("/","\/",$path);
		$path=str_replace(".","\.",$path);
		if(preg_match("#$path#",$pathToCheck)){return true;}
		
	}
	return false;
}
function download_file(){
	$ldap=new clladp();
	$cr=new SimpleCrypt($ldap->ldap_password);
	$path=$cr->decrypt(base64_decode($_GET["download-file"]));
	$file=basename($path);
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("cmd.php?file-content=".base64_encode($path)));
	$content_type=base64_decode($sock->getFrameWork("cmd.php?mime-type=".base64_encode($path)));
header('Content-Type: '.$content_type);
header("Content-Disposition: inline; filename=\"$file\""); 
echo $datas;	
	
}


?>