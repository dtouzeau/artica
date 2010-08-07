<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.sockets.inc');
	include_once('ressources/class.ini.inc');
	

$usersmenus=new usersMenus();
if($usersmenus->AsMailBoxAdministrator==false){header('location:users.index.php');exit;}
if(isset($_GET["PostfixLoadeMailsQueue"])){echo PostfixLoadeMailsQueue();exit;}
if(isset($_GET["MailID"])){MailID();exit;}
if(isset($_GET["PostQueueF"])){PostQueueF();exit();}
if(isset($_GET["TableQueue"])){echo Table_queue();exit;}
if(isset($_GET["DeleteMailID"])){DeleteMailID();exit;}
if(isset($_GET["PostCatReprocess"])){reprocessMailID();exit;}
if(isset($_GET["PostfixDeleteMailsQeue"])){PostfixDeleteMailsQeue();exit;}
if(isset($_GET["js"])){popup_js();exit;}
if(isset($_GET["popup"])){popup_tabs();exit;}
if(isset($_GET["smtp_queues"])){popup_index();exit;}
if(isset($_GET["show-queue"])){queue_js();exit;}
if(isset($_GET["popup-queue"])){queue_popup();exit;}
if(isset($_GET["read-queue"])){queue_popup_list();exit;}
if(isset($_GET["popup-message"])){popup_message();exit;}
if(isset($_GET["details"])){popup_postqueue();exit;}
if(isset($_GET["js-message"])){queue_js();exit;}

//postfix_queue_monitoring();


function popup_js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$js_add=file_get_contents("js/artica_postfix_queue.js");
	$title=$tpl->_ENGINE_parse_body('{queue_monitoring}');
	$html="
	$js_add
	
	function StartIndex(){
		YahooWinS(700,'$page?popup=yes','$title');
	
	}
	
	StartIndex();
	";
	
	
	echo $html;
	}
	
function queue_js(){
$page=CurrentPageName();
$queue=$_GET["show-queue"];
	$tpl=new templates();	
	$delete_message_text=$tpl->javascript_parse_text("{delete_message_text}");
	$start="Start$queue();";
	
	if(isset($_GET["js-message"])){$start="PostCat('{$_GET["js-message"]}');";}
	
	$html="
	
	function redqueue(){
		LoadAjax('{$queue}_queueidlist','$page?read-queue=$queue');
	
	}
	
	function Start$queue(){
		YahooWin(750,'$page?popup-queue=$queue&count={$_GET["count"]}','$queue ({$_GET["count"]}) mails');
		setTimeout(\"redqueue()\",1000);
	}
	
	function PostCat(message){
		YahooWin2('700','$page?popup-message='+message,message);
	}
	
var X_PostCatDelete= function (obj) {
	var results=obj.responseText;
	if(results.length>2){alert(results);}
	YahooWin2Hide();
	RefreshTab('queue_monitor'); 
	}	
	
	function PostCatDelete(message){
		if(confirm('$delete_message_text ?\\n'+message)){
			var XHR = new XHRConnection();
			XHR.appendData('DeleteMailID',message);
			document.getElementById('loupemessage').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',X_PostCatDelete);	
		}
	}
	
	function PostCatReprocess(message){
			var XHR = new XHRConnection();
			XHR.appendData('PostCatReprocess',message);
			document.getElementById('loupemessage').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',X_PostCatDelete);		
	}
	
	
function switchDivViewQueue(id){
	document.getElementById('messageidtable').style.display='none';
   	document.getElementById('messageidbody').style.display='none';
   	document.getElementById(id).style.display='block';   
   	
}	
	
	$start";
	
	echo $html;	
}

function popup_postqueue(){
	$page=CurrentPageName();
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?postqueue-master-list=yes")));
	if(is_array($datas)){
		while (list ($msgid, $line) = each ($datas) ){
			$line["TO"]=trim($line["TO"]);

			$html=$html."
			<tr ". CellRollOver("Loadjs('$page?js-message=$msgid')").">
			<td>
				<table style='width:100%'>
					<td  width=10% style='font-size:12px;' nowrap>{$line["DATE"]}</td>
					<td width=33% style='font-size:12px;font-weight:bold' nowrap>{$line["FROM"]}</td>
					<td width=1%><img src='img/fw_bold.gif'></td>
					<td width=33% style='font-size:12px;font-weight:bold' nowrap>{$line["TO"]}</td>
					</tr>
					<tr>
					<td colspan=4><code style='font-size:11px;'>{$line["STATUS"]}</code><hr></td>
					</tr>
				</table>
			</td>
			</tr>";
			
			
		}
		
	}
	
	$html="
		<div style='font-size:14px;margin:5px'>{postqueue_list_explain}</div>
		<table style='width:100%'>
		$html
		</table>
	";
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
	
}



function popup_message(){
	include_once(dirname(__FILE__).'/ressources/class.mime.parser.inc');
	include_once(dirname(__FILE__).'/ressources/rfc822_addresses.php');
	$messageid=$_GET["popup-message"];
	$sock=new sockets();
	$datas=$sock->getfile("view_queue_file:$messageid");
	
	if(preg_match('#\*\*\* ENVELOPE RECORDS.+?\*\*\*(.+?)\s+\*\*\*\s+MESSAGE CONTENTS#is',$datas,$re)){
		$table_content=$re[1];
	}
if(preg_match('#\*\*\* MESSAGE CONTENTS.+?\*\*\*(.+?)\*\*\*\s+HEADER EXTRACTED #is',$datas,$re)){
		$message_content=$re[1];
	}	
	
$tbl=explode("\n",$table_content);
while (list ($num, $val) = each ($tbl) ){
	if(trim($val)==null){continue;}
	if(preg_match('#(.+?):(.+)#',$val,$ri)){
		$fields[$ri[1]]=trim($ri[2]);
	}
	
}
if(preg_match('#^([0-9]+)#',$fields["message_size"],$ri)){
	$fields["message_size"]=FormatBytes(($fields["message_size"]/1024));
}
$table="
<table style='width:99%'>";
while (list ($num, $val) = each ($fields) ){
	$table=$table . "
	<tr>
		<td class=legend>{{$num}}:</td>
		<td><strong style='width:11px'>{$val}</strong></td>
	</tr>
	";
	
}
$table=$table . "</table>";
$message_content=htmlspecialchars($message_content);
$messagesT=explode("\n",$message_content);
$message_content=null;
while (list ($num, $val) = each ($messagesT) ){
	if(trim($val)==null){continue;}
	$message_content=$message_content."<div><code>$val</code></div>";
	
	
}

$html="
<H1>{show_mail}:$messageid</H1>
<div id='loupemessage'>
<table style='width:100%'>
<tr>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
		
		<td>" . Paragraphe("64-banned-phrases.png",'{routing_info}','{routing_info_text}',"javascript:switchDivViewQueue('messageidtable');")."</td>
		</tr>
		<tr>
		<td>" . Paragraphe("64-banned-regex.png",'{body_message}','{body_message_text}',"javascript:switchDivViewQueue('messageidbody');")."</td>
		</tr>
		<tr>
		<td>" . Paragraphe("64-refresh.png",'{reprocess_message}','{reprocess_message_text}',"javascript:PostCatReprocess('$messageid');")."</td>
		</tr>		
		<tr>
		<td>" . Paragraphe("delete-64.png",'{delete_message}','{delete_message_text}',"javascript:PostCatDelete('$messageid');")."</td>
		</tr>	
	
		
		
		</table>
	</td>
	<td valign='top'>
	<div id='messageidbody' style='display:none;width:100%;height:300px;overflow:auto'>$message_content
	</div>
	<div id='messageidtable' style='display:block;width:100%;height:300px;overflow:auto'>$table</div>
	</td>
</tr>
</table>
</div>
";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}


function queue_popup(){
	$queue=$_GET["popup-queue"];
	$count=$_GET["count"];
	if($queue=="defer"){$qtxt="deferred";}else{$qtxt=$queue;}
	if($queue=="trace"){$qtxt=null;}
	if($queue=="bounce"){$qtxt=null;}
	if($qtxt<>null){$explain="<p class=caption>{{$qtxt}_text}</p>";}
	$html="
	<H1>$queue</H1>
	$explain
	<div id='{$queue}_queueidlist'></div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function queue_popup_list(){
	$queue=$_GET["read-queue"];
	$sock=new sockets();
	$datas=$sock->getFrameWork("cmd.php?DumpPostfixQueue=$queue");
	$tbl=explode("\n",$datas);
	
	if(is_array($tbl)){
		$html="<table style='width:99%' class=table_form>
		<tr>
			<th>&nbsp;</th>
			<th>{time}</th>
			<th>{sender}</th>
			<th>{recipient}</th>
			<th>{subject}</th>
			</tr>";
		while (list ($num, $val) = each ($tbl) ){
		$val=str_replace('<sender></sender>','<sender>unknown</sender>',$val);
		$count=0;
		$max=30;

		if(preg_match('#<time>(.+?)</time><named_attr>(.+?)</named_attr><sender>(.+?)</sender><recipient>(.+?)</recipient><subject>(.+?)</subject><MessageID>(.+?)</MessageID>#',$val,$regs)){
			$count=$count+1;
			$file=$regs[1];
			$path=$regs[2];
			$time=PostFixTimeToPhp($regs[1]);
			$named=$regs[2];
			$sender=$regs[3];
			$seemail=imgtootltip('spotlight-18.png','{show_mail}',"PostCat('{$regs[6]}')");
			$recipient=$regs[4];
			$subject=htmlentities('"'.$regs[5].'"');
			
		if(strlen($sender)>$max){$sender=texttooltip(substr($sender,0,27).'...',$sender,null,null,1);}
		if(strlen($recipient)>$max){$recipient=texttooltip(substr($recipient,0,27).'...',$recipient,null,null,1);}			
		if(strlen($subject)>$max){$subject=texttooltip(substr($subject,0,27).'...',$subject,null,null,1);}
			
			$html=$html . "<tr ". CellRollOver().">
			<td width=1% style='font-size:11px'>$seemail</td>
			<td width=2% nowrap style='font-size:11px'>$time</td>
			<td nowrap style='font-size:11px'>$sender</td>
			<td nowrap style='font-size:11px'>$recipient</td>
			<td style='font-size:11px'>$subject</td>
			</tr>
			
			";
			
		}
	}	
	

	
$html=$html . "</table>";}

if($count==0){$err="<div style='font-size:13px;font-weight:bolder;padding:10px;margin:5px;border:1px solid #CCCCCC;background-color:white'>{too_late_or_no_queue_files}</div>";}

$div="<div style='width:100%;height:300px;overflow:auto'>$err$html</div>";
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($div);
}


function popup_tabs(){
	$array["details"]="{emails}";
	$array["smtp_queues"]='{smtp_queues}';
	$tpl=new templates();
	$page=CurrentPageName();

	
	while (list ($num, $ligne) = each ($array) ){
		$ligne=$tpl->_ENGINE_parse_body("$ligne");
		$html[]= "<li><a href=\"$page?$num=yes\"><span>$ligne</span></li>\n";
	}
	
	
	echo "
	<div id=queue_monitor style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#queue_monitor').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";	
	
}

	
function popup_index(){
	
	$html="
	
	<input type='hidden' id='remove_mailqueue_text' value=\"{remove_mailqueue_text}\">
	<table style='width:100%'>
	<tr>
		<td width=1%><img src='img/bg_postfix_queue.png'></td>
		<td valign='top'>
			<div id=table_queue>".Table_queue()."</div>
		</td>
	</tr>
	<tr>	
	</table>
	
	";
	
	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}



function postfix_queue_monitoring(){
	$page=CurrentPageName();
	if(!isset($_SESSION["uid"])){header('location:logon.php');exit();}
	//".Table_queue() . "
	$html="
	<input type='hidden' id='remove_mailqueue_text' value=\"{remove_mailqueue_text}\">
	<table style='width:100%'>
	<tr>
		<td width=1%><img src='img/bg_postfix_queue.jpg'></td>
		<td valign='top'>
			<div id=table_queue></div>
		</td>
	</tr>
	<tr>
	<td colspan=2>
	<table style='width:100%'>
		<tr>
			<td valign='top' width=50%>
			" . RoundedLightGreen("<H5>{incoming}</H5>{incoming_text}")."<br>".			
				RoundedLightGreen("<H5>{active}</H5>{active_text}")."<br>
			</td>
			<td valign='top' width=50%>".			
				RoundedLightGreen("<H5>{deferred}</H5>{deferred_text}")."<br>".			
				RoundedLightGreen("<H5>{maildrop}</H5>{maildrop_text}")."<br>			
			</td>
		</tr>
	</table>
	</tr>
	</table>
	
			
		
	<div id='queuelist' style='width:650px'></div>
	
	
	<script>LoadAjax('table_queue','$page?TableQueue=yes');</script>
	";
	
	$cfg["JS"][]="js/artica_postfix_queue.js";
	//$cfg["JS"][]="js/mootools.js";
	
	$tpl=new template_users('{queue_monitoring}',$html,0,0,0,0,$cfg);
	echo $tpl->web_page;	
}

function Table_queue(){
	$sock=new sockets();
	$datas=$sock->getFrameWork("cmd.php?postfixQueues=yes");
	$queues=unserialize($sock->getFrameWork("cmd.php?postfixQueues=yes"));
	$page=CurrentPageName();

	$html="<table style='padding:3px;margin:4px;width:240px' class=table_form>
			<tr>
				<th>{queue}&nbsp;&nbsp;</td>
				<th>{email_number}</td>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
			</tr>";

	
	while (list ($queuename, $number) = each ($queues) ){
			if(!is_numeric($number)){continue;}
			$reg[1]=$queuename;
			$reg[2]=$number;
			$tdroll=CellRollOver() ;
			
			$jsDeleteQueue=imgtootltip('x.gif',"{remove_mailqueue} :<strong>{$reg[1]}","PostfixDeleteMailsQeue('{$reg[1]}');");
			$js_showqueue=CellRollOver("Loadjs('$page?show-queue={$reg[1]}&count={$reg[2]}')");
			if($reg[2]>0){
				$seequeue=imgtootltip('spotlight-18.png','{show_queue}',$js_showqueue);
			}else{$seequeue="&nbsp;";}
			
			$html=$html . "
			<tr $tdroll>
				<td align='right' $js_showqueue><strong>{$reg[1]}:&nbsp;</strong></td
				<td align='left' $js_showqueue><strong>{$reg[2]}&nbsp;</strong></td>
				<td align='center'>$seequeue</td>
				<td width=1%>$jsDeleteQueue</td>
			</tr>
			";
			
		
		
	}
	$page=CurrentPageName();
	$html=$html . "
	<td><center><input type='button' OnClick=\"javascript:LoadAjax('table_queue','$page?TableQueue=yes');\" value='{refresh}'></center></td>
	<td><center><input type='button' OnClick=\"javascript:PostQueueF();\" value='{reprocess_queue}'></center></td>
	<td>&nbsp;</td>
	</table>
	";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}


function Tabs($numberPages,$queue){
	for($i=0;$i<=$numberPages;$i++){
		if($_GET["tab"]==$i){$class="id=tab_current";}else{$class=null;}
		$ligne_number=$i+1;
		$ligne="{page} " . $ligne_number;
		$html=$html . "<li><a href=\"#\" OnClick=\"javascript:PostfixLoadeMailsQueue('$queue','','$i');\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";	
	
}

function PostfixLoadeMailsQueue(){
	$tpl=new templates();
	$tot=$_GET["total"];
	if(!isset($_GET["numStart"])){$numStart=0;}
	if(!isset($_GET["tab"])){$tab=0;}else{$tab=$_GET["tab"];}
	if($tab==''){$tab=0;}
	$queue_name=$_GET["PostfixLoadeMailsQueue"];
	$ini=new Bs_IniHandler();
	if(!is_file('ressources/databases/postfix-queue-cache.conf')){return $tpl->_ENGINE_parse_body("{no_cache_created}");}
	$ini->loadFile('ressources/databases/postfix-queue-cache.conf');
	$PagesNumber=$ini->get($queue_name,'PagesNumber');
	
	if($PagesNumber>0){$tabublation=Tabs($PagesNumber,$queue_name);}
	
	
	$filetemp="ressources/databases/queue.list.$tab.$queue_name.cache";
	if(!is_file($filetemp)){return $tpl->_ENGINE_parse_body("<strong>{unable_to_locate}: $filetemp</strong>");}
	
	$datas=explode("\n",file_get_contents($filetemp));
	
	$countRows=count($datas);
	$number_pages=round($tot/$countRows);
	

	
	
	$html="
	<H4>{queue} $queue_name $PagesNumber {pages}  $countRows {lines}</H4>
	<div align='right' class=caption>{from_cache_file} $filetemp</div>
	$tabublation
	<table style='width:100%'>
	<tr class='caption'>
	<td><strong>{date}</strong></td>
	<td><strong>{operation}</strong></td>
	<td><strong>{mail_from}</strong></td>
	<td><strong>{mail_to}</strong></td>
	<td><strong>{delete}</strong></td>
	</tr>
	";
	while (list ($num, $val) = each ($datas) ){
		$val=str_replace('<sender></sender>','<sender>unknown</sender>',$val);
		if(preg_match('#<file>(.+?)</file><path>(.+?)</path><time>(.+?)</time><named_attr>(.+?)</named_attr><sender>(.+?)</sender><recipient>(.+?)</recipient><subject>(.+?)</subject>#',$val,$regs)){
			$file=$regs[1];
			$path=$regs[2];
			$time=PostFixTimeToPhp($regs[3]);
			$named=$regs[4];
			$sender=$regs[5];
			$recipient=$regs[6];
			$subject=utf8_decode($subject);
			$subject=htmlentities('"'.$regs[7].'"');
			$varClick="OnClick=\"javascript:LoadMailID('$file','$queue_name','$tab')\"";
			
			$tooltips="<strong>$file</strong><br>$subject";
			$html=$html . "
			<tr " . CellRollOver(null,$tooltips) . " class=caption>
			<td nowrap $varClick>$time</td>
			<td $varClick>$named</td>
			<td $varClick>$sender</td>
			<td $varClick>$recipient</td>
			<td align='center' width=1%>" . imgtootltip('x.gif','{delete}',"DeleteMailID('$queue_name','$tab','$file')")."</td>
			</tr>
			";
			$count=$count+1;
			if($count>100){break;}
			
		}
		
	}
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	$html=$html . "</table>";
	return $html;
}
function  MailID(){
	$mailid=$_GET["MailID"];
	$sock=new sockets();
	$datas=$sock->getfile('view_queue_file:'.$mailid);
	$datas=htmlentities($datas);
	$datas=str_replace("\n","<br>",$datas);
	$datas=str_replace("<br><br>","<br>",$datas);
	$tab=$_GET["page_number"];
	$queue_name=$_GET["queue_name"];
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("
	<div style='padding:10px;margin:5px;font-size:9px'>
	<H4>$mailid {file}</H4>
	<div style='text-align:right;padding-right:10px'><input type='button' value='{delete}' OnClick=\"javascript:DeleteMailID('$queue_name','$tab','$mailid')\"></div>
	<code>$datas</code></div>");
	
}
function PostQueueF(){
	$mailid=$_GET["MailID"];
	$sock=new sockets();
	$datas=$sock->getfile('postqueue_f');	
	$datas=htmlentities($datas);
	$datas=str_replace("\n","<br>",$datas);
	$datas=str_replace("<br><br>","<br>",$datas);
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("
	<div style='padding:10px;margin:5px;font-size:9px'>
	<H4 style='margin-rigth:20px'>{reprocess_queue}</H4>
	<code>$datas</code>
	</div>");	
}
function DeleteMailID(){
	$mailid=$_GET["DeleteMailID"];
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork('cmd.php?postsuper-d-master='.$mailid));	
	echo $datas;
	}
	
function reprocessMailID(){
	$mailid=$_GET["PostCatReprocess"];
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork('cmd.php?postsuper-r-master='.$mailid));	
	echo $datas;	
}


	
function PostfixDeleteMailsQeue(){
	$PostfixDeleteMailsQeue=$_GET["PostfixDeleteMailsQeue"];
	$sock=new sockets();
	$datas=$sock->getfile('PostfixDeleteMailsQeue:'.$PostfixDeleteMailsQeue);	
	
}



