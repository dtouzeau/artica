<?php

class status{
	
	var $_postfix_status;
	var $array_queue;	
	var $array_right;
	var $array_left;
	var $sock;
	var $cyrus_init_d;
	var $status_in_line;
	var $myini;
	var $newversions=array();
	function status($noconnect=0,$local_ini=null){
			if(!file_exists(dirname(__FILE__) . '/settings.inc')){
				writelogs("ERROR: unable to stat " . dirname(__FILE__) . '/settings.inc',__CLASS__ . "=>" . __FUNCTION__,__FILE__);
			return false;}
		include(dirname(__FILE__) . '/settings.inc');
		$sock=new sockets();
		$queues=unserialize($sock->getFrameWork("cmd.php?postfixQueues=yes"));
		$this->array_queue=$queues;
		$this->cyrus_init_d=$_GLOBAL["cyrus_initd_path"];
		$this->myini=$local_ini;
		$this->sock=new sockets();
		if($noconnect<>0){return null;}
		$this->array_right[]=$this->Postfix_satus();
		$this->array_right[]=$this->cyrus_satus();
		$this->array_right[]=$this->avestatus_satus();
		$this->array_left[]=$this->artica_status();
		if ($_GLOBAL["kas_installed"]==true){
			$this->array_right[]=$this->kas_status();
		}
		
		
	}
	
function events($text){
		$pid=getmypid();
		$date=date('Y-m-d H:i:s');
		$logFile="/var/log/artica-postfix/background-status.debug";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "[$pid]: $date $text\n");
		@fclose($f);	
		}	
		
function ProcessExistsByCommand($command){
	$cmd="/usr/bin/pgrep -l -f \"$command\"";
	writelogs($cmd,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$vals);
while (list ($num, $val) = each ($vals) ){
		writelogs("\"$val\" ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$val=trim($val);
		if($val==null){continue;}
		if(preg_match("#pgrep#",$val)){continue;}
		if(!preg_match('#([0-9]+)#',$val,$re)){continue;}
		writelogs("\"$val\" Processing {$val["master_pid"]} PID",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if(is_file("/proc/{$val["master_pid"]}/exe")){return true;}
	}
	return false;	
}

 public function PGREP($command){
 	$command=str_replace(".","\.",$command);
 	$command=str_replace("/","\/",$command);
	$cmd="/usr/bin/pgrep -l -f \"$command\"";
	writelogs($cmd,__FUNCTION__.'/'.__CLASS__,__FILE__,__LINE__); 
	exec($cmd,$vals);
while (list ($num, $val) = each ($vals) ){
		writelogs("\"$val\" ",__FUNCTION__.'/'.__CLASS__,__FILE__,__LINE__); 
		$val=trim($val);
		if($val==null){continue;}
		if(preg_match("#pgrep#",$val)){continue;}
		if(!preg_match('#([0-9]+)#',$val,$re)){continue;}
		writelogs("\"$val\" Processing {$val["master_pid"]} PID",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if(is_file("/proc/{$val["master_pid"]}/exe")){return true;}
	}
	return false;	
}
	

	
	function StatusFailed($returnArray=0){
		include_once(dirname(__FILE__) . '/class.ini.inc');
		$p=new usersMenus();
		$bycommand=false;
		if(posix_getuid()==0){$bycommand=true;}
		$users=new usersMenus();
		$users->LoadModulesEnabled();	
		$socks=new sockets();
		$ini=new Bs_IniHandler();
		$StopMonitoNFS=$socks->GET_INFO('StopMonitoNFS');
		if(file_exists(dirname(__FILE__)."/logs/global.status.ini")){
			$ini->loadFile(dirname(__FILE__)."/logs/global.status.ini");
		}else{
			$ini->loadString($socks->getfile('daemons_status'));
		}
		
		if(!is_array($ini->_params)){return null;}
	
		
		while (list ($num, $val) = each ($ini->_params) ){
			if($val["application_installed"]==0){continue;}
			if($val["service_disabled"]=='0'){continue;}
			if($val["running"]==1){continue;}
			if($val["service_croned"]==1){continue;}
			if($val["application_installed"]==null){continue;}
			if($val["status"]=="command line"){continue;}
			$val["master_pid"]=trim($val["master_pid"]);
			if($val["master_pid"]>0){continue;}
			if($val["service_name"]=="APP_XFCE"){continue;}
			if($val["service_name"]==="APP_CONSOLEKIT"){continue;}
			if($val["service_name"]==="APP_PRELOAD"){continue;}			
			writelogs("Scanning {$val["service_name"]} disabled={$val["service_disabled"]} master_pid={$val["master_pid"]} installed={$val["application_installed"]} running=0",__FUNCTION__.'/'.__CLASS__,__FILE__,__LINE__);
			
			if($val["service_name"]=="APP_NFS"){
				if($StopMonitoNFS==1){continue;}
			}
			
			if($val["service_name"]=="APP_ARTICA_MYSQMAIL"){
					writelogs("{$val["service_name"]} -> pgrep",__FUNCTION__.'/'.__CLASS__,__FILE__,__LINE__); 
					if($this->PGREP("artica-postfix/exec.maillog.php")){
						writelogs("Scanning {$val["service_name"]} artica-postfix/exec.maillog.php exists, continue;",__FUNCTION__.'/'.__CLASS__,__FILE__,__LINE__); 
						continue;
					}else{
						writelogs("Scanning {$val["service_name"]} pgrep failed (bycommand=$bycommand)",__FUNCTION__.'/'.__CLASS__,__FILE__,__LINE__);
					}
			}			
			
			if($bycommand){
				$this->events("Scanning by command=true");
				
				$this->events("Scanning {$val["service_name"]} installed={$val["application_installed"]} running={$val["running"]} master_pid={$val["master_pid"]}");
				if($val["master_pid"]<>null){
					if(is_file("/proc/{$val["master_pid"]}/exe")){
						$this->events("Scanning {$val["service_name"]} /proc/{$val["master_pid"]}/exe exists, continue;"); 
						continue;
					}
				}
				
				
				
				if($val["service_name"]=="APP_ARTICA_MYSQMAIL"){
					
					if($this->ProcessExistsByCommand("artica-postfix/exec.maillog.php")){
						$this->events("Scanning {$val["service_name"]} artica-postfix/exec.maillog.php exists, continue;"); 
						continue;
					}else{
						$this->events("ProcessExistsByCommand return false");
					}
				
				system('/etc/init.d/artica-postfix start postfix-logger &');
				if($this->ProcessExistsByCommand("artica-postfix/exec.maillog.php")){
						$this->events("Scanning {$val["service_name"]} artica-postfix/exec.maillog.php exists, with start it command;"); 
						continue;
					}				
				}
				
				
				if($val["service_name"]=="APP_POSTFIX"){
					if($this->postfix_check_pid()){continue;}
				}
				
				if($val["service_name"]=="APP_SPAMASSASSIN"){
					if(!$users->MEM_HIGER_1G){continue;}
					
					if($this->PIDOF($users->spamassassin_bin_path)){
						$this->events("Scanning {$val["service_name"]} pidof return true");
						continue; 
					}
					
				}
				

				

				
			 $this->events("Scanning {$val["service_name"]}...");	
			  if($val["service_name"]=="APP_KAS3_MILTER"){
					if($this->PIDOF("/usr/local/ap-mailfilter3/bin/kas-milter")){
						$this->events("Scanning {$val["service_name"]} pidof return true");
						continue; 
					}else{
						$this->events("Scanning {$val["service_name"]} pidof return false, start it");
						system('/etc/init.d/kas3 start &');
						$this->events("Scanning {$val["service_name"]} done");
						if($this->PIDOF("/etc/init.d/artica-postfix start kas3 &")){
							$this->events("Scanning {$val["service_name"]} pidof return true");
							continue;
						}
						
					}
					
				}				

			}
			
			
			
			$infos="&nbsp;";
					
					
					
						$count=$count+1;
						$why='{stopped}';
						
						if($val["license"]=="license_expired"){
							$why="{license_expired}";
						}
						if(trim($val["service_cmd"])<>null){
							$javascript="javascript:YahooWin3(550,'admin.index.php?EmergencyStart={$val["service_cmd"]}','{{$val["service_name"]}}');";
							$javascript_a="YahooWin3(550,'admin.index.php?EmergencyStart={$val["service_cmd"]}','{{$val["service_name"]}}');";
							$tooltip="{start_service_in_debug}";
						}else{
							$javascript="admin.index.services.status.php";
							$tooltip=$why;
						}
						$table["BIG"][]=Paragraphe("danger64.png","{{$val["service_name"]}}",
						"<span style='color:#D32D2D'>$why</span><br>$infos","$javascript",$tooltip,'300',73);
						$table["LITTLE"][]="
						<tr " . CellRollOver($javascript_a,$tooltip)." style='border:1px solid red'>
							<td valign='top'>
								<table style='width:282px;border-bottom:1px solid #D32D2D;border-right:1px solid #D32D2D;border-top:1px solid #CCCCCC;border-left:1px solid #CCCCCC;margin-top:5px'>
								<tr>
									<td width=1% valign='top'><img src='img/danger24.png'></td>
									<td><strong>{{$val["service_name"]}}</strong>:<span style='color:#D32D2D'>$why</span>...&nbsp;$infos</td>
								</tr>
								</table>
							</td>
						</tr>
						";
						
						$json[]=array("PRODUCT"=>$val["service_name"],"WHY"=>$why,"INFOS"=>$infos,"service_cmd"=>$val["service_cmd"]);
					
				
			
		}
		if($returnArray==0){
			if(count($table["BIG"])>0){
				$final=implode(" ",$table["BIG"]);
				if(count($table["BIG"])>3){
					$final="<div style='width:310px;height:120px;overflow:auto'><table>".implode(" ",$table["LITTLE"])."</table></div>";
				}
				$tpl=new templates();
				return $tpl->_ENGINE_parse_body($final);
			}
			
		}
		return $json;
		
	}
	
function postfix_check_pid(){
	$queue=system("/usr/sbin/postconf -h queue_directory");
	if(!is_file("$queue/pid/master.pid")){
		$this->events("Scanning {$val["service_name"]} $queue/pid/master.pid does not exists");
		return false;
	}
	
	$pid=trim(file_get_contents("$queue/pid/master.pid"));
	if(is_file("/proc/$pid/exe")){
		$this->events("Scanning {$val["service_name"]} /proc/$pid/exe exists, continue;"); 
		return true;
	}

  $this->events("Scanning {$val["service_name"]} /proc/$pid/exe does not exists, failed;"); 
  return false;
}

function PIDOF($path){
	$datas=system("/bin/pidof $path");
	$this->events("Scanning $path= $datas");
	if(trim($datas)<>null){return true;}
	
}

function AllStatus(){
		include_once(dirname(__FILE__) . '/class.html.pages.inc');
		include_once(dirname(__FILE__) . '/class.ini.inc');
		$p=new usersMenus();
		$users=new usersMenus();
//-----------------------------------------------------------------------------------------------------------------------------		
		if($p->kas_installed==true){
			$stat=trim($users->KAS_STATUS);
			$stat=explode(';',$stat);
			$ap_process_server=explode('-',$stat[0]);
			$ap_spfd=explode('-',$stat[1]);
			$kas_license=explode('-',$stat[2]);
			$kas_thttpd=explode('-',$stat[3]);
			
			$res=$ap_process_server[1]+$ap_spfd[1]+$kas_license[1] + $kas_thttpd[1];			
			if($res==4){$kas=$this->BuildLittleTemplate('kas_status',true);}else{$kas=$this->BuildLittleTemplate('kas_status',false);}
			}
//-----------------------------------------------------------------------------------------------------------------------------	
		if($this->sock->TestArticaPort()==false){
		$srv_artica=$this->BuildLittleTemplate('srv_artica',false);}else{$srv_artica=$this->BuildLittleTemplate('srv_artica',true);}
			
//-----------------------------------------------------------------------------------------------------------------------------		
		$stat=$users->POSTIFX_STATUS;
		$stat=explode(';',$stat);
		$postfixStatus=$stat[0];	
		if($postfixStatus==1){
			$postfix_status=$this->BuildLittleTemplate('postfix_status',true);}else{$postfix_status=$this->BuildLittleTemplate('postfix_status',false,"IndexStartPostfix()");}
//-----------------------------------------------------------------------------------------------------------------------------

	if($p->aveserver_installed==true){
		$stat=trim($users->KAV_STATUS);
		$stat=explode(';',$stat);
		$title="aveserver_status";
		if($stat[0]==1){
		$aveserver=$this->BuildLittleTemplate($title,true);}else{
		$aveserver=$this->BuildLittleTemplate($title,false);}
	}
//-----------------------------------------------------------------------------------------------------------------------------					
	if($users->fetchmail_installed==true){
		$stat=trim($users->FETCHMAIL_STATUS);
		$stat=explode(';',$stat);
		$title="fetchmail_status";
		if($stat[0]==1){
		$fetchmail_status=$this->BuildLittleTemplate($title,true);}else{
		$fetchmail_status=$this->BuildLittleTemplate($title,false);}
		
	}
//-----------------------------------------------------------------------------------------------------------------------------					
writelogs("Cyrus installed ? $users->cyrus_imapd_installed",__FUNCTION__,__FILE__);	
if($users->cyrus_imapd_installed==true){
		$stat=trim($users->CYRUS_STATUS);
		$stat=explode(';',$stat);
		$title="cyrus_status";
		if($stat[0]==1){
		$cyrus_status=$this->BuildLittleTemplate($title,true);}else{
		$cyrus_status=$this->BuildLittleTemplate($title,false);}	
	}
//-----------------------------------------------------------------------------------------------------------------------------		
	//$sock=new sockets();
	if($users->ARTICA_POLICY_STATUS){
	$title="artica_policy_status";
		$artica_policy=$this->BuildLittleTemplate($title,true);}else{
		$artica_policy=$this->BuildLittleTemplate("artica_policy_status",false);}		
//-----------------------------------------------------------------------------------------------------------------------------		
//-----------------------------------------------------------------------------------------------------------------------------		
	//$sock=new sockets();
	if($users->KAV_MILTER_INSTALLED){
	 $title="kav_milter_status";
		if ($users->KAVMILTER_PID<>null) {
	 		$kav_milter_status=$this->BuildLittleTemplate($title,true);}else{
			$kav_milter_status=$this->BuildLittleTemplate($title,false);}
	}
	
	if($users->SQUID_INSTALLED){
	$title="squid_status";
		if ($users->SQUID_PID<>null) {
	 		$squid_status=$this->BuildLittleTemplate($title,true);}else{
			$squid_status=$this->BuildLittleTemplate($title,false);}	
	}
	
	if($users->KAV4PROXY_INSTALLED){
	$title="kav4proxy_status";
		if ($users->KAV4PROXY_PID<>null) {
	 		$kav4proxy_status=$this->BuildLittleTemplate($title,true);}else{
			$kav4proxy_status=$this->BuildLittleTemplate($title,false);}	
	}

	
	if($users->DANSGUARDIAN_INSTALLED){
		$title="dansguardian_status";
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$ini->loadString($sock->getFrameWork('cmd.php?squid-status=yes'));
		if($ini->_params["DANSGUARDIAN"]["master_pid"]<>null){
			$dansguardian_status=$this->BuildLittleTemplate($title,true);}else{
				$dansguardian_status=$this->BuildLittleTemplate($title,false);}	
		}
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$ini->loadString($sock->getfile('pureftd_status'));
		
			
if($users->PUREFTP_INSTALLED){
		$title="pureftpd_satus";
		
		if($ini->_params["PUREFTPD"]["master_pid"]<>null){
			$pure_ftp=$this->BuildLittleTemplate($title,true);}else{
				$pure_ftp=$this->BuildLittleTemplate($title,false);}	
		}

		if($ini->_params["SQLGREY"]["sqlgrey_installed"]==1){
			$title="sqlgrey_satus";
			if($ini->_params["SQLGREY"]["master_pid"]<>null){
			$sqlgrey=$this->BuildLittleTemplate($title,true);}else{
			$sqlgrey=$this->BuildLittleTemplate($title,false);}	
			
		}
			
	

//-----------------------------------------------------------------------------------------------------------------------------	
	if($users->POSTFIX_INSTALLED==false){$postfix_status=null;$artica_filter=null;}
	$html="<H4>{services_status}</H4>
	<table>
	$srv_artica
	$postfix_status
	$sqlgrey
	$artica_policy
	$artica_filter
	$cyrus_status
	$fetchmail_status
	$kas
	$aveserver
	$kav_milter_status
	$squid_status
	$dansguardian_status
	$kav4proxy_status
	$pure_ftp
	</table>
	";
	
	$in_line="
	<table style='width:100%'>
	<tr>
		<td>
			<table style='width:100%'>
			$srv_artica
			</table>
		</td>
		<td>
			<table style='width:100%'>
			$artica_filter
			</table>
		</td>	
		<td>
			<table style='width:100%'>
			$artica_policy
			</table>
		</td>	
		<td>
			<table style='width:100%'>
			$postfix_status
			</table>
		</td>
		<td>
			<table style='width:100%'>
			$cyrus_status
			</table>
		</td>							
	</tr>
	
	";
	$this->status_in_line=$in_line;
	return $html;
	
	
	
}


function BuildLittleTemplate($title,$status=false,$onfailed=null){
	if($status==false){
		if($onfailed<>null){$a="<a href=\"javascript:$onfailed\">";$b="</a>";}
		return "<tr>
				<td width=1% style='border-bottom:1px dotted #CCCCCC' valign='top'><img src='img/icon_err.gif'></td>
				<td style='border-bottom:1px dotted #CCCCCC' valign='top'>{".$title."}</td>
				<td style='border-bottom:1px dotted #CCCCCC' valign='top'>$a{some_service_is_stopped}$b</td>
			</tr>";
		
	}else{
		return "<tr>
				<td width=1% style='border-bottom:1px dotted #CCCCC' valign='top'><img src='img/icon_ok.gif'></td>
				<td style='border-bottom:1px dotted #CCCCCC' valign='top'>{".$title."}</td>
				<td  style='border-bottom:1px dotted #CCCCCC' valign='top'>{all_services_running}</td>
			</tr>";
	}
	
	
}
	
	
	
	
	function kas_status(){
		$user=new usersMenus();
		$stat=trim($user->KAS_SATUS);
		$stat=explode(';',$stat);
		if(!is_array($stat)){return null;}
		
		if($stat[0]==null){
			$img='i24.png';
			$text='{no_infos}';
			return $this->BuildDesign($img,'{kas_status}',$text);
		}
		$ap_process_server=explode('-',$stat[0]);
		$ap_spfd=explode('-',$stat[1]);
		$kas_license=explode('-',$stat[2]);
		$kas_thttpd=explode('-',$stat[3]);
		$res=$ap_process_server[1]+$ap_spfd[1]+$kas_license[1] + $kas_thttpd[1];
		
		if($res<4){
			$img='warning24.png';
			$text="{some_service_is_stopped}";
			}
		if($res==4){
			$text="{all_services_running} {using_pid} :";
			$img='ok24.png';
		}
			
		
		
		$text=$text . "<ul>
		<li>ap-process-server: PID {$ap_process_server[0]}</li>
		<li>ap-spfd: PID {$ap_spfd[0]}</li>
		<li>kas-licence: PID {$kas_license[0]}</li>
		<li>kas-thttpd: PID {$kas_thttpd[0]}</li></ul>";
		
		
		return $this->BuildDesign($img,'{kas_status}',$text);
	}
	
	
	function artica_status(){
	
	if($this->sock->TestArticaPort()==false){
		$img='danger32.png';
		$text='{error_no_socks}<br> {request_start_artica_service}';
		$color='e53838';
		return $this->BuildDesign_infos($img,'{srv_artica}',$text,$color);
	}else{
		$pid=$this->sock->getfile('pid');
		$img='ok32.png';
		$text="{service_running} {using_pid} $pid {on port}:{$this->sock->remote_port}";
		$color='18a712';
	}
	if(!$this->sock->error==false){
		if($this->_postfix_status==0){
			$this->array_left[]=$this->BuildDesign_infos('i32.png','{postfix_status_stopped}','{recommend_restart_postfix}','5e5ec7');
			
		}
	}
	
	return $this->BuildDesign_infos($img,'{srv_artica}',$text,$color);
	
	}
	
	
function Squid_status(){
	
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getFrameWork('cmd.php?squid-status=yes'));	
	
if($ini->_params["SQUID"]["running"]==0){
		$img="okdanger32.png";
		$status="{stopped}";
	}else{
		$img="ok32.png";
		$status="running";
		
	}

if($ini->_params["SQUID"]["icap_enabled"]<>'1'){
	$icap="
			<tr>
			<td align='right'><img src='img/status_warning.gif'></td>
			<td><strong style='font-size:11px'>{no_icap_support}</td>
		</tr>";
	
}


$html="
<table style='width:1OO%'>
<tr>
<td width=1% valign='top'>
<img src='img/$img'></td>
<td>
	<table style='width:100%'>
		<tr>
		<td colspan=2><strong>{squid_status}:&nbsp;<span style='font-size:13px'>$status</span></strong></td>
		</tr>
		<tr>
		<td align='right' nowrap><strong>{pid}:</strong></td>
		<td><strong>{$ini->_params["SQUID"]["master_pid"]}</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{memory}:</strong></td>
		<td><strong>{$ini->_params["SQUID"]["master_memory"]}&nbsp; mb</strong></td>
		</tr>
		<tr>
		<td align='right' nowrap><strong>{version}:</strong></td>
		<td><strong>{$ini->_params["SQUID"]["master_version"]}</strong></td>
		$icap
		</tr>
	</table>
	</td>
</table>
";		
return $html;	
}

function Postfix_multi_status($ou){
	$instance=str_replace(" ","-",$ou);
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?postfix-multi-queues=$instance")));
	$tot=$array["incoming"]+$array["active"]+$array["deferred"]+$array["corrupt"];
	
	
	
	
$status_queues="
			<table>
			<tr>
				<td colspan=2><span style='font-size:13px'>{smtp_queues}</span></td>
			</tr>
				<tr>
					<td align='right' $link style='font-size:12px'>{incoming_queue}:</a>&nbsp;</td>
					<td $link><strong style='font-size:12px'>{$array["incoming"]} </strong>emails</td>
				</tr>
				<tr>
					<td align='right' $link style='font-size:12px'>{active_queue}:</a>&nbsp;</td>
					<td $link><strong style='font-size:12px'>{$array["active"]} </strong>emails</td>
				</tr>
				<tr>
					<td align='right' $link style='font-size:12px'>{deferred_queue}:</a>&nbsp;</td>
					<td $link><strong style='font-size:12px'>{$array["deferred"]} </strong>emails</td>
				</tr>
				<tr $link_corrupt>
					<td align='right' style='font-size:12px'>{corrupt_queue}:</a>&nbsp;</td>
					<td><strong style='font-size:12px'>{$array["corrupt"]} </strong>emails</td>
				</tr>				
	
				<tr $link>
					<td align='right' style='color:#C53110;border-top:1px dotted #CCCCCC'>Total:&nbsp;</td>
					<td style='color:#C53110;border-top:1px dotted #CCCCCC;font-size:12px'>
					<strong>$tot&nbsp;</strong>emails</td>
				</tr>
				
			</table>";	
			
$stat=unserialize(base64_decode($sock->getFrameWork("cmd.php?postfix-mutli-stat=$instance")));

	switch ($stat[0]) {
		
		case -2:$img='i64.png';
			$text='{no_infos}';
			return $this->BuildDesign($img,'{postfix_status}',$text);
			break;	
		
		case '-1':$img='status_postfix_bg_failed.png';
			$text='{service_not_installed}';
			break;
		case '1':$img='status_postfix_bg_ok.png';
			if(preg_match("#2\.7.*#",$stat[1])){$img='status_postfix_bg_ok27.png';}
			$text='{service_running}<br>{using_version} ' . $stat[1]. ' {pid} ' . $stat[2];
			break;
		case '0':
			
			$img='status_postfix_bg_failed.png';
			$text=RoundedLightWhite('<span style=color:red>{service_stopped}<br>{using_version} ' . $stat[1]."</span>");
					
			break;
	}

	$design="
	<div style='background-image:url(img/$img);background-repeat:no-repeat;width:350px;height:270px'>
	<div style='padding-left:155px'>
	<span style='font-size:16px;font-weight:bold;color:#005447'>{APP_POSTFIX}</span>
	<br><span style='font-size:11px;font-weight:bold'><i style='color:black'>$text</i>
		$status_queues
	</div>
	<table style='width:100%'>
		<tr>
			<td valign='top'>
				<table style='width:100%;border-top:1px solid #CCCCCC;margin-top:5px'>
					<tr>
						<td style='border-right:2px solid #005447;padding:4px' align='center'>$amavis</td>
						<td style='border-right:2px solid #005447;padding:4px' align='center'>$dstat</td>
						<td style='border-right:2px solid #005447;padding:4px' align='center'>$rpcess_number</td>
					</tr>
				</table>
			</td>
			<td valign='top'>
				
			</td>
		</tr>
	</table>
	</div>
	";	
	
return $design;
	
}


function Postfix_satus(){
	$usersmenus=new usersMenus();
	$usersmenus->LoadModulesEnabled();
	$stat=$usersmenus->POSTIFX_STATUS;
	$stat=explode(';',$stat);
	$this->_postfix_status=$stat[0];
	$tot=$this->array_queue["incoming"]+$this->array_queue["active"]+$this->array_queue["deferred"]+$this->array_queue["corrupt"];
	$link=CellRollOver("Loadjs('postfix.queue.monitoring.php?js')");
	$logs="javascript:s_PopUp('postfix.events.php?pop=true',450,400);";
	
	if($usersmenus->DSTAT_INSTALLED){
		
		$dstat=texttooltip("{statistics}","{statistics}","Loadjs('dstat.postfix.php')");
		
	}
	
	
	if($usersmenus->AMAVIS_INSTALLED){
		if($usersmenus->EnableAmavisDaemon==1){		
		$amavis=texttooltip("{APP_AMAVISD_NEW}","{APP_AMAVIS_EXPLAIN}","Loadjs('amavis.index.php?ajax=yes')");
		}
	}
	$rpcess_number=texttooltip("{POSTFIX_PROCESS_NUMBER}","{POSTFIX_PROCESS_NUMBER}","Loadjs('postfix.process.number.php')");
	
	
	
	
	if(is_file(dirname(__FILE__).'/logs/artica-backup-size.ini')){
		$ini=new Bs_IniHandler(dirname(__FILE__).'/logs/artica-backup-size.ini');
		if($ini->_params["artica_backup"]["original_messages"]==null){$ini->_params["artica_backup"]["original_messages"]=0;}
		if($ini->_params["artica_backup"]["attachments"]==null){$ini->_params["artica_backup"]["attachments"]=0;}
		$size=$ini->_params["artica_backup"]["original_messages"]+$ini->_params["artica_backup"]["attachments"];
		$size=FormatBytes($size);
		$link2=CellRollOver("Loadjs('postfix.backup.monitoring.php')");
		$artica_backup="<tr>
						<td align='right' $link2>{backup_size}:</a>&nbsp;</td>
						<td $link2>$size</td>
						</tr>";
						
		if($this->array_queue["quarantine"]>0){
			$link_quarantine_progress=CellRollOver("Loadjs('postfix.quarantine.progress.php')");
		}
						
		
		
	}
	
$oldaddon="				<tr>
					<td align='right' $link>backup queue:</a>&nbsp;</td>
					<td $link><strong>{$this->array_queue["backup"]} </strong>emails</td>
				</tr>	
				<tr $link_quarantine_progress>
					<td align='right' nowrap>quarantine queue:</a>&nbsp;</td>
					<td nowrap><strong>{$this->array_queue["quarantine"]} </strong>emails</td>
				</tr>					
				$artica_backup";	
	
	
	$link_corrupt=CellRollOver("Loadjs('postfix.corrupt.queue.php')");
	$link_incoming=CellRollOver("Loadjs('postfix.queue.monitoring.php?show-queue=incoming&count={$this->array_queue["incoming"]}');");
	$link_active=CellRollOver("Loadjs('postfix.queue.monitoring.php?show-queue=active&count={$this->array_queue["active"]}');");
	$link_deferred=CellRollOver("Loadjs('postfix.queue.monitoring.php?show-queue=deferred&count={$this->array_queue["deferred"]}');");
	
	$status_queues="
			<table>
			<tr>
				<td colspan=2 style='border-bottom:1px solid #005447'><span style='font-size:13px;color:#005447;'><strong>{smtp_queues}:</strong></span></td>
			</tr>
			<tr><td colspan=2>&nbsp;</td></tr>
				<tr>
					<td align='right' $link_incoming style='font-size:12px'>{incoming_queue}:</a>&nbsp;</td>
					<td $link_incoming><strong style='font-size:12px'>{$this->array_queue["incoming"]} </strong>emails</td>
				</tr>
				<tr>
					<td align='right' $link_active style='font-size:12px'>{active_queue}:</a>&nbsp;</td>
					<td $link_active><strong style='font-size:12px'>{$this->array_queue["active"]} </strong>emails</td>
				</tr>
				<tr>
					<td align='right' $link_deferred style='font-size:12px'>{deferred_queue}:</a>&nbsp;</td>
					<td $link_deferred><strong style='font-size:12px'>{$this->array_queue["deferred"]} </strong>emails</td>
				</tr>
				<tr $link_corrupt>
					<td align='right' style='font-size:12px'>{corrupt_queue}:</a>&nbsp;</td>
					<td><strong style='font-size:12px'>{$this->array_queue["corrupt"]} </strong>emails</td>
				</tr>				
	
				<tr $link>
					<td align='right' style='color:#C53110;border-top:1px dotted #CCCCCC;font-size:12px'>Total:&nbsp;</td>
					<td style='color:#C53110;border-top:1px dotted #CCCCCC;font-size:12px'>
					<strong style='font-size:12px'>$tot&nbsp;</strong>emails</td>
				</tr>
				
			</table>";
	
	$queue="
	<table style='width:100%'>
	<tr>
	
	</td>
	<td valign='top'>
	<table style='width:100%'>
	<tr>
		<td>" . imgtootltip('22-logs.png','{events}',"PostfixPopupEvents()")."</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	
	";

	$sock=new sockets();
	$stat=unserialize(base64_decode($sock->getFrameWork("cmd.php?postfix-stat=yes")));
	switch ($stat[0]) {
		
		case -2:$img='i64.png';
			$text='{no_infos}';
			return $this->BuildDesign($img,'{postfix_status}',$text);
			break;	
		
		case '-1':$img='status_postfix_bg_failed.png';
			$text='{service_not_installed}';
			break;
		case '1':$img='status_postfix_bg_ok.png';
			if(preg_match("#2\.7.*#",$stat[1])){$img='status_postfix_bg_ok27.png';}
			$text='{service_running}<br>{using_version} ' . $stat[1]. ' {pid} ' . $stat[2];
			break;
		case '0':
			
			$img='status_postfix_bg_failed.png';
			$text=RoundedLightWhite('<span style=color:red>{service_stopped}<br>{using_version} ' . $stat[1]."</span>");
					
			break;
	}
	
	
	$design="
	<div style='background-image:url(img/$img);background-repeat:no-repeat;width:350px;height:270px'>
	<div style='padding-left:155px'>
	<span style='font-size:16px;font-weight:bold;color:#005447'>{APP_POSTFIX}</span>
	<br><span style='font-size:11px;font-weight:bold'><i style='color:black'>$text</i>
		$status_queues
	</div>
	<table style='width:100%'>
		<tr>
			<td valign='top'>
				<table style='width:100%;border-top:1px solid #CCCCCC;margin-top:5px'>
					<tr>
						<td style='border-right:2px solid #005447;padding:4px' align='center'>$amavis</td>
						<td style='border-right:2px solid #005447;padding:4px' align='center'>$dstat</td>
						<td style='border-right:2px solid #005447;padding:4px' align='center'>$rpcess_number</td>
					</tr>
				</table>
			</td>
			<td valign='top'>
				
			</td>
		</tr>
	</table>
	</div>
	";
	
	return $design;
	return $this->BuildDesign($img,'{postfix_status}',$text,"$amavis$dstat$rpcess_number","javascript:Loadjs('RTMMail.php')");
	
	
}
function fetchmail_satus(){
	$user=new usersMenus();
	$stat=trim($user->FETCHMAIL_STATUS);
	$stat=explode(';',$stat);
	$this->_postfix_status=$stat[0];
	
	switch ($stat[0]) {
		
		case null:$img='i24.png';
			$text='{no_infos}';
			return $this->BuildDesign($img,'{fetchmail_status}',$text);
			break;	
		
		case '-1':$img='warning24.png';
			$text='{service_not_installed}';
			break;
		case '1':$img='ok24.png';
			$text='{service_running}<br>{using_version} ' . $stat[1]. ' {pid} ' . $stat[2];
			break;
		case '0':$img='warning24.png';
			$text='{service_stopped}<br>{using_version} ' . $stat[1] . ' {pid} ' . $stat[2] . "<br><i style='color:red'>{$user->fetchmail_daemon_logs}</i>";
					
			break;
	}
	
	return RoundedLightGreen($this->BuildDesign($img,'{fetchmail_status}',$text));
	
	
}

function cyrus_satus(){
	$users=new usersMenus();
	$title="{cyrus_status}";
	$init=basename($this->cyrus_init_d);
	$stat=trim($users->CYRUS_STATUS);
	$stat=explode(';',$stat);
	switch ($stat[0]) {
		case null:$img='i24.png';
			$text='{no_infos}';
			break;			
		case '-1':$img='i24.png';
			$text='{service_not_installed}';
			break;
		case '1':$img='ok24.png';
			$text='{service_running}<br>{using_version} ' . $stat[1]. ' {pid} ' . $stat[2] .$this->banner_services($init);
			break;
		case '0':$img='warning24.png';
			$text='{service_stopped}<br>{using_version} ' . $stat[1] . ' {pid} ' . $stat[2] .$this->banner_services($init);		
			break;
	}
	return $this->BuildDesign($img,$title,$text);
	
	
	
}



function avestatus_satus(){
	$stat=trim($this->sock->getfile('aveserver_status'));
	$stat=explode(';',$stat);
	$title="{aveserver_status}";
	switch ($stat[0]) {
		case null:$img='i24.png';
			$text='{no_infos}';
			break;			
		case '-1':$img='i24.png';
			$text='{service_not_installed}';
			break;
		case '1':$img='ok24.png';
			$date=$this->avestatus_pattern_date($stat[3],$stat[4]);
			$text='{service_running}<br>{using_version} ' . $stat[1]. ' {pid} ' . $stat[2]  .$date .$this->banner_services('aveserver');
			break;
		case '0':$img='warning24.png';
			$date=$this->avestatus_pattern_date($stat[3],$stat[4]);
			$text='{service_stopped}<br>{using_version} ' . $stat[1] . ' {pid} ' . $stat[2] . $date. $this->banner_services('aveserver');		
			break;
	}
	
	return $this->BuildDesign($img,$title,$text);
	
}


function avestatus_pattern_date($date,$time,$nobuild=0){
	if(preg_match('#([0-9]{2})([0-9]{2})([0-9]{4})#',$date,$reg)){
		$day=$reg[1];
		$month=$reg[2];
		$year=$reg[3];
	}else{return null;}
	
	if(preg_match('#([0-9]{2})([0-9]{2})#',$time,$reg)){
		$hour=$reg[1];
		$min=$reg[2];
		
	}else{return null;}	
	$date1 = mktime($hour-2,$min,0,$month,$day,$year);
	$time_date=date('Y/m/d H:i/s',$date1);
	
$date2 = mktime(date('H'),date('I'),0,date('m'),date('d'),date('Y'));
	$diff_date = $date2-$date1;
	$diff['H'] = (int)($diff_date/(60*60));
	$diff['D'] = (int)($diff_date/(60*60*24));
	if($diff['H'] >0){
		if($nobuild==0){
			$this->array_left[]=$this->BuildDesign_infos('warning32.png','{update_kaspersky_database_title}','{recommend_update_kaspersky}','ffa22a');
		}
	}
	if($nobuild==0){
	$text="<br>{pattern_update_date} :<strong>$time_date</stong><br></strong><i> {since} {$diff['D']} {days},  {$diff['H']} {hours} </i>";
	}else{
		$text="<strong>$time_date</stong><br></strong><i> {since} {$diff['D']} {days},  {$diff['H']} {hours}</i>";
	}
	return $text;
	
}

function dateadd($per,$n,$d) {
   switch($per) {
      case "yyyy": $n*=12;
      case "m":
         $d=mktime(date("H",$d),date("i",$d)
            ,date("s",$d),date("n",$d)+$n
            ,date("j",$d),date("Y",$d));
         $n=0; break;
      case "ww": $n*=7;
      case "d": $n*=24;
      case "h": $n*=60;
      case "n": $n*=60;
   }
   return $d+$n;
}

function BuildDesign($img,$title,$text,$letcon=null,$title_js=null){
	
	if($title_js<>null){
		$js_h5="OnClick=\"$title_js\" OnMouseOver=\"this.style.cursor='pointer'\"
		OnMouseOut=\"this.style.cursor='default\"";
		$h5_style="border-bottom:1px solid #CCCCCC;'";
		
		
	}
	
		$html="
	<table style='border:0px;margin:5px'>
	<td width=1% valign='top'>
		<img src='img/$img'>
		$letcon	
	</td>
		
	<td valign='top' width=100%>
	<table style='width:100%'>
		<tr>
			<td><h5 $title_js $h5_style>$title</h5></td>
		</tr>
		<tr>
			<td valign='top'>$text</td>
		</tr>
	</table>
	</td>
	</tr>
	</table>";
	return $html;
	
}
function BuildDesign_infos($img,$title,$text,$color){
		$html="
	<div style='border:1px solid #CCCCCC;margin-bottom:5px'>
		<div style='background-color:#$color;width:250px;height:20px;padding-top:5px;'>
			<img src='img/$img' align='left'><strong style='font-size:12px;color:white;padding-left:5px;'>$title</strong>
		</div>
		
	<div style='padding:10px;'>$text</div>
	</div>";
	
	return $html;
}

function banner_services($service){
	$start="<a href='#' OnClick=\"javascript:artica_service_start('$service','applications:$service');\"  onMouseOver=\"javascript:AffBulle('{action_start_service}');\" OnMouseOut=\"javascript:HideBulle();\">";
	$stop="<a href='#' OnClick=\"javascript:artica_service_stop('$service','applications:$service');\" onMouseOver=\"javascript:AffBulle('{action_stop_service}');\" OnMouseOut=\"javascript:HideBulle();\">";
	return
	"
	<table style='margin:0px;padding:0px;border:0px;width:90%;;border-top:1px solid #3939ac;margin-top:8px;'>
	<tr>
	<td align='right' style='padding:0px;margin:0px'>
	<table style='margin:0px;padding:0px;border:0px;width:1%' width=1%>
	<tr>
		<td width=1% style='margin:0px;padding:0px' nowrap>&nbsp;$start<img src='img/task-run.gif'></a>&nbsp;</td>
		<td width=1% style='margin:0px;padding:0px;' nowrap>&nbsp;$stop<img src='img/task-stop-on.gif'></a>&nbsp;</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>";
}

function ParseIniStatus(){
	if(!is_file($this->myini)){return "<tr><td>{error_waiting_sync}</td></tr>";}
	$ini=new Bs_IniHandler($this->myini);
	
	if($ini->_params["SQLGREY"]["sqlgrey_installed"]==1){
			$title="sqlgrey_satus";
			if($ini->_params["SQLGREY"]["master_pid"]<>null){
			$sqlgrey=$this->BuildLittleTemplate($title,true);}else{
			$sqlgrey=$this->BuildLittleTemplate($title,false);}
	}

if($ini->_params["POSTFIX"]["postfix_installed"]==1){
			$title="postfix_status";
			if($ini->_params["SQLGREY"]["master_pid"]<>null){
			$postfix=$this->BuildLittleTemplate($title,true);}else{
			$postfix=$this->BuildLittleTemplate($title,false);}
	}	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($postfix.$sqlgrey);			
	
}

public function BuildNewersions(){
	if(!is_file(dirname(__FILE__).'/index.ini')){
		$this->NewVersionsEvents("unable to stat ". dirname(__FILE__).'/index.ini');
	}
	
	if(!$this->front_page_notify()){
		@file_put_contents(dirname(__FILE__)."/logs/jGrowl-new-versions.txt","#");
		$this->NewVersionsEvents("front_page_notify return false");
		return;
	}
	
	$users=new usersMenus();
	$this->status_artica_version($users);
	$this->status_amavis_version($users);
	$this->status_dansguardian_version($users);
	$this->status_fetchmail_version($users);
	$this->status_squid_version($users);
	$this->status_nmap_version($users);
	$this->status_roundcube_version($users);
	$this->status_assp_version($users);
	$this->status_spamassassin($users);
	$datas=serialize($this->newversions);
	
	@file_put_contents(dirname(__FILE__)."/logs/jGrowl-new-versions.txt",$datas);
	@chmod(dirname(__FILE__)."/logs/jGrowl-new-versions.txt",0755);
	
}

private function front_page_notify(){
$sock=new sockets();	
$ini=new Bs_IniHandler("/etc/artica-postfix/settings/Daemons/ArticaAutoUpdateConfig");
$front_page_notify=$ini->_params["AUTOUPDATE"]["front_page_notify"];
$this->NewVersionsEvents("front_page_notify():: front_page_notify=$front_page_notify");
if($front_page_notify==null){return true;}
if(strtolower($front_page_notify)=='yes'){return true;}
if(strtolower($front_page_notify)=='no'){return false;}
return true;
	
}

private function NewVersionsEvents($text){
	writelogs($text,__CLASS__.'/'."BuildNewersions",__FILE__,__LINE__);
}


private function status_amavis_version($users){
	if($users->KASPERSKY_SMTP_APPLIANCE){return false;}
	if(!$users->AMAVIS_INSTALLED){return false;}
	$localversion=$users->AMAVISD_VERSION;
	if($localversion==null){return null;}
	if(preg_match("#([0-9\.]+)#",$localversion,$re)){$localversion=$re[1];}
	$ini=new Bs_IniHandler(dirname(__FILE__).'/index.ini');
	$remote_version=$ini->_params["NEXT"]["amavisd-new"];
	if(preg_match("#([0-9\.]+)#",$remote_version,$re)){$remote_version=$re[1];}
	$remote_int=str_replace('.','',$remote_version);
	$local_int=str_replace('.','',$localversion);
		
	if($remote_int>$local_int){
		$this->newversions["APP_AMAVISD_NEW"]=array(
			"TITLE"=>"{AMAVIS_NEWVERSION}:&nbsp;$remote_version",
			"TEXT"=>"{AMAVIS_NEWVERSION_TEXT}",
			"JS"=>"setup.index.progress.php?product=APP_AMAVISD_NEW&start-install=yes"
		);
	}
}
private function status_dansguardian_version($users){
	$sock=new sockets();if($sock->GET_INFO('SQUIDEnable')==0){return false;}
	if(!$users->DANSGUARDIAN_INSTALLED){return false;}
	$localversion=$users->DANSGUARDIAN_VERSION;
	if($localversion==null){$this->NewVersionsEvents(__FUNCTION__.":: unable to stat local dansguardian version");return null;}
	if(preg_match("#([0-9\.]+)#",$localversion,$re)){$localversion=$re[1];}
	$ini=new Bs_IniHandler(dirname(__FILE__).'/index.ini');
	$remote_version=$ini->_params["NEXT"]["dansguardian"];
	if(preg_match("#([0-9\.]+)#",$remote_version,$re)){$remote_version=$re[1];}
	$remote_int=str_replace('.','',$remote_version);
	$local_int=str_replace('.','',$localversion);
	$this->NewVersionsEvents(__FUNCTION__.":: Local $local_int against $remote_int ");	
	if($remote_int>$local_int){
		$this->newversions["APP_DANSGUARDIAN"]=array(
			"TITLE"=>"{DANSGUARDIAN_NEWVERSION}:&nbsp;$remote_version",
			"TEXT"=>"{DANSGUARDIAN_NEWVERSION_TEXT}",
			"JS"=>"setup.index.progress.php?product=APP_DANSGUARDIAN&start-install=yes"
		);		
		
	}
}

private function status_fetchmail_version($users){
	if(!$users->fetchmail_installed){$this->NewVersionsEvents(__FUNCTION__.":: fetchmail is not installed");return false;}
	$localversion=$users->FETCHMAIL_VERSION;
	if($localversion==null){$this->NewVersionsEvents(__FUNCTION__.":: unable to stat fetchmail version");return null;}
	if(preg_match("#([0-9\.]+)#",$localversion,$re)){$localversion=$re[1];}
	$ini=new Bs_IniHandler(dirname(__FILE__).'/index.ini');
	$remote_version=$ini->_params["NEXT"]["fetchmail"];
	if(preg_match("#([0-9\.]+)#",$remote_version,$re)){$remote_version=$re[1];}
	$remote_int=str_replace('.','',$remote_version);
	$local_int=str_replace('.','',$localversion);
	$this->NewVersionsEvents(__FUNCTION__.":: Local $local_int against $remote_int ");	
	if($remote_int>$local_int){
		$this->newversions["APP_FETCHMAIL"]=array(
			"TITLE"=>"{FETCHMAIL_NEWVERSION}:&nbsp;$remote_version",
			"TEXT"=>"{FETCHMAIL_NEWVERSION_TEXT}",
			"JS"=>"setup.index.progress.php?product=APP_FETCHMAIL&start-install=yes"
		);	
	
	}
}

private function status_assp_version($users){
	if($users->KASPERSKY_SMTP_APPLIANCE){return false;}
	if(!$users->ASSP_INSTALLED){$this->NewVersionsEvents(__FUNCTION__.":: assp is not installed");return false;}
	$localversion=$users->ASSP_VERSION;
	if($localversion==null){$this->NewVersionsEvents(__FUNCTION__.":: unable to stat assp version");return null;}
	if(preg_match("#([0-9\.]+)#",$localversion,$re)){$localversion=$re[1];}
	$ini=new Bs_IniHandler(dirname(__FILE__).'/index.ini');
	$remote_version=$ini->_params["NEXT"]["assp"];
	$this->NewVersionsEvents(__FUNCTION__.":: ASSP \"$remote_version\" On web server");
	
	if(preg_match("#([0-9\.]+)#",$remote_version,$re)){$remote_version=$re[1];}
	$remote_int=str_replace('.','',$remote_version);
	$local_int=str_replace('.','',$localversion);
	$this->NewVersionsEvents(__FUNCTION__.":: Local $local_int against $remote_int ");	
	if($remote_int>$local_int){
		$this->newversions["APP_ASSP"]=array(
			"TITLE"=>"{ASSP_NEWVERSION}:&nbsp;$remote_version",
			"TEXT"=>"{ASSP_NEWVERSION_TEXT}",
			"JS"=>"setup.index.progress.php?product=APP_ASSP&start-install=yes"
		);	
	
	}
}


private function status_squid_version($users){
	$sock=new sockets();if($sock->GET_INFO('SQUIDEnable')==0){return false;}
	if(!$users->SQUID_INSTALLED){$this->NewVersionsEvents(__FUNCTION__.":: squid is not installed");return false;}
	if(!is_file(dirname(__FILE__).'/index.ini')){$this->NewVersionsEvents(__FUNCTION__.":: unable to stat ".dirname(__FILE__).'/index.ini');return null;}
	$localversion=$users->SQUID_VERSION;
	if($localversion==null){$this->NewVersionsEvents(__FUNCTION__.":: unable to stat squid version");return null;}
	
	if(preg_match("#([0-9\.]+).+?([0-9]+)#",$localversion,$re)){
		if(strlen($re[2])==1){$re[2]='0'.$re[2];}
		$localversion=$re[1].'.'.$re[2];
	}
	
	
	$ini=new Bs_IniHandler(dirname(__FILE__).'/index.ini');
	$remote_version=$ini->_params["NEXT"]["squid3"];
	if(preg_match("#([0-9\.]+).+?([0-9]+)#",$remote_version,$re)){
		if(strlen($re[2])==1){$re[2]='0'.$re[2];}
		$remote_version=$re[1].'.'.$re[2];
		}
	
	
	$localversion=str_replace('..','.',$localversion);
	$remote_version=str_replace('..','.',$remote_version);
	$remote_int=str_replace('.','',$remote_version);
	$local_int=str_replace('.','',$localversion);
	$this->NewVersionsEvents(__FUNCTION__.":: Local $local_int against $remote_int ");	
	if($remote_int>$local_int){
		$this->newversions["APP_SQUID"]=array(
			"TITLE"=>"{SQUID_NEWVERSION}:&nbsp;$remote_version",
			"TEXT"=>"{SQUID_NEWVERSION_TEXT}",
			"JS"=>"setup.index.progress.php?product=APP_SQUID&start-install=yes"
		);			
		
	}
}

private	function status_nmap_version($users){
		
		if(!$users->nmap_installed){
			$this->NewVersionsEvents(__FUNCTION__.":: NMAP is not installed");
			return false;}
			
		
		$localversion=$users->nmap_version;
		$this->NewVersionsEvents(__FUNCTION__.":: Local version is $users->nmap_version");	
	
		if(preg_match('#([0-9\.]+)#',$localversion,$re)){$localversion=$re[1];}
		$ini=new Bs_IniHandler(dirname(__FILE__).'/index.ini');
		$remote_version=$ini->_params["NEXT"]["nmap"];
		
		if(preg_match('#([0-9]+)\.([0-9]+)$#',$localversion,$re)){
			$localversion="{$re[1]}.{$re[2]}0";
		}	
		
		$this->NewVersionsEvents(__FUNCTION__.":: remote version is $remote_version");	
		if(preg_match('#([0-9]+)\.([0-9]+)$#',$remote_version,$re)){
			$remote_version="{$re[1]}.{$re[2]}0";
		}
		
	
		
		$remote_int=str_replace('.','',$remote_version);
		$local_int=str_replace('.','',$localversion);
		
		$this->NewVersionsEvents(__FUNCTION__.":: Local $local_int against $remote_int ");
		
		if($remote_int>$local_int){
		$this->newversions["APP_NMAP"]=array(
				"TITLE"=>"{NMAP_NEWVERSION}:&nbsp;$remote_version",
				"TEXT"=>"{NMAP_NEWVERSION_TEXT}",
				"JS"=>"setup.index.progress.php?product=APP_NMAP&start-install=yes"
			);			
		}
	}
	
private function status_roundcube_version($users){
	if(!$users->roundcube_installed){
		$this->NewVersionsEvents(__FUNCTION__.":: roundcube is not installed");
		return false;}
		
	if(!is_file(dirname(__FILE__).'/index.ini')){$this->NewVersionsEvents(__FUNCTION__.":: unable to stat ".dirname(__FILE__).'/index.ini');return null;}
	$localversion=$users->roundcube_intversion;


	if($localversion==null){
		if($users->roundcube_version<>null){
			if(preg_match("#([0-9\.]+)#",$users->roundcube_version,$re)){
				$localversion=$re[1];
			}
		}
	}
	
	$this->NewVersionsEvents(__FUNCTION__.":: Local int version is $localversion");
	$this->NewVersionsEvents(__FUNCTION__.":: Folder is $users->roundcube_folder");
	$this->NewVersionsEvents(__FUNCTION__.":: Local version is $users->roundcube_version");	

	if(preg_match('#([0-9\.]+)#',$localversion,$re)){$localversion=$re[1];}
	$ini=new Bs_IniHandler(dirname(__FILE__).'/index.ini');
	$remote_version=$ini->_params["NEXT"]["roundcubemail3"];
	$this->NewVersionsEvents(__FUNCTION__.":: remote version is $remote_version ");
	if(preg_match('#([0-9]+)\.([0-9]+)$#',$localversion,$re)){
		$localversion="{$re[1]}.{$re[2]}0";
	}	
	
	
	if(preg_match('#([0-9]+)\.([0-9]+)$#',$remote_version,$re)){
		$remote_version="{$re[1]}.{$re[2]}0";
	}
	$this->NewVersionsEvents(__FUNCTION__.":: remote version (2) is $remote_version ");

	
	$remote_int=str_replace('.','',$remote_version);
	$local_int=str_replace('.','',$localversion);
	
	$this->NewVersionsEvents(__FUNCTION__.":: Local $local_int against $remote_int ");
	
	if($remote_int>$local_int){
		$this->NewVersionsEvents(__FUNCTION__.":: Build warning...");
		//APP_ROUNDCUBE3
		$this->newversions["APP_ROUNDCUBE3"]=array(
				"TITLE"=>"{ROUNDCUBE_NEWVERSION}:&nbsp;$remote_version",
				"TEXT"=>"{ROUNDCUBE_NEWVERSION_TEXT}",
				"JS"=>"setup.index.progress.php?product=APP_ROUNDCUBE3&start-install=yes"
			);					
		
	}
	}

private function status_samba_version($users){	
	if(!$users->SAMBA_INSTALLED){$this->NewVersionsEvents(__FUNCTION__.":: Samba is not installed");return false;}
	$localversion=$users->SAMBA_VERSION;
	

	if(preg_match('#([0-9\.]+)#',$localversion,$re)){$localversion=$re[1];}
	$ini=new Bs_IniHandler(dirname(__FILE__).'/index.ini');
	$remote_version=$ini->_params["NEXT"]["samba"];
	
	
	
	if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)#',$remote_version,$re)){
		if(strlen($re[3])==1){$re[3]=$re[3]."0";}
		$remote_version="{$re[1]}.{$re[2]}.{$re[3]}";
	}
	
	if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)#',$localversion,$re)){
		if(strlen($re[3])==1){$re[3]=$re[3]."0";}
		$localversion="{$re[1]}.{$re[2]}.{$re[3]}";
	}	
	$this->NewVersionsEvents(__FUNCTION__.":: Local version is $localversion");
	$this->NewVersionsEvents(__FUNCTION__.":: remote version is $remote_version");

	
	$remote_int=str_replace('.','',$remote_version);
	$local_int=str_replace('.','',$localversion);
	
	$this->NewVersionsEvents(__FUNCTION__.":: Local $local_int against $remote_int ");
	
	if($remote_int>$local_int){
		$this->newversions["APP_SAMBA"]=array(
				"TITLE"=>"{SAMBA_NEWVERSION}:&nbsp;$remote_version",
				"TEXT"=>"{SAMBA_NEWVERSION_TEXT}",
				"JS"=>"setup.index.progress.php?product=APP_SAMBA&start-install=yes"
			);		
		
	}
	}
	
private function status_spamassassin($users){
	if($users->KASPERSKY_SMTP_APPLIANCE){return false;}
	if(!$users->spamassassin_installed){$this->NewVersionsEvents(__FUNCTION__.":: SpamAssassin is not installed");return false;}
	$localversion=$users->spamassassin_version;
	

	if(preg_match('#([0-9\.]+)#',$localversion,$re)){$localversion=$re[1];}
	$ini=new Bs_IniHandler(dirname(__FILE__).'/index.ini');
	$remote_version=$ini->_params["NEXT"]["Mail-SpamAssassin"];
	
	
	
	if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)#',$remote_version,$re)){
		if(strlen($re[3])==1){$re[3]=$re[3]."0";}
		$remote_version="{$re[1]}.{$re[2]}.{$re[3]}";
	}
	
	if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)#',$localversion,$re)){
		if(strlen($re[3])==1){$re[3]=$re[3]."0";}
		$localversion="{$re[1]}.{$re[2]}.{$re[3]}";
	}	
	$this->NewVersionsEvents(__FUNCTION__.":: Local version is $localversion");
	$this->NewVersionsEvents(__FUNCTION__.":: remote version is $remote_version");

	
	$remote_int=str_replace('.','',$remote_version);
	$local_int=str_replace('.','',$localversion);
	
	$this->NewVersionsEvents(__FUNCTION__.":: Local $local_int against $remote_int ");
	
	if($remote_int>$local_int){
		$this->newversions["APP_SPAMASSASSIN"]=array(
				"TITLE"=>"{SPAMASSASSIN_NEWVERSION}:&nbsp;$remote_version",
				"TEXT"=>"{SPAMASSASSIN_NEWVERSION_TEXT}",
				"JS"=>"setup.index.progress.php?product=APP_SPAMASSASSIN&start-install=yes"
			);		
		
	}
	}	

private function status_artica_version($users){
			$ini=new Bs_IniHandler(dirname(__FILE)."/index.ini");
			$remote_version=$ini->_params["NEXT"]["artica"];
			$local_version=$users->ARTICA_VERSION;
			$remote_version_bin=str_replace('.','',$remote_version);
			$local_version_bin=str_replace('.','',$local_version);
			if($local_version<$remote_version){
			$this->newversions["APP_ARTICA"]=array(
				"TITLE"=>"{upgrade_artica}:&nbsp;$remote_version",
				"TEXT"=>"{upgrade_artica_text}",
				"JS"=>"artica.update.php?js=yes"
			);					
				
				
			}
		}	
}
function status_pgrep($command){
	$cmd="/usr/bin/pgrep -l -f \"$command\"";
	writelogs($cmd,__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$vals);
	while (list ($num, $val) = each ($vals) ){
			writelogs("\"$val\" ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$val=trim($val);
			if($val==null){continue;}
			if(preg_match("#pgrep#",$val)){continue;}
			if(!preg_match('#([0-9]+)#',$val,$re)){continue;}
			writelogs("\"$val\" Processing {$val["master_pid"]} PID",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			if(is_file("/proc/{$val["master_pid"]}/exe")){return true;}
		}
		return false;	
}
?>