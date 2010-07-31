<?php
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);
function QUERY_SQL($sql,$logfile=null,$notLog=0,$Database='artica_filter'){
	
	
	
	if(!function_exists('sqlite3_open')){
		$s=new mysql();
		return $s->QUERY_SQL($sql,$Database);
	}
	
	
	
	
	if($notLog==0){
			$sqll=nl2br($sql);
			$sqll=str_replace("\n","",$sqll);
			$sqll=str_replace("\r","",$sql);
			$sqll=str_replace("\t","",$sql);			
			$sqll=str_replace("  ","",$sqll);
			WriteLogs("QUERY_SQL:["  . $_SERVER['SERVER_NAME'] . "::$Database] $sqll",__FUNCTION__,__FILE__);
	
			}
			
	$Database="/usr/share/artica-postfix/LocalDatabases/artica_database.db";

	$db =sqlite3_open($Database);
	if(!$db){
		$des=sqlite3_error($db);
		WriteLogs("ERROR access to $Database ($des) $error",__FUNCTION__,__FILE__);
		return null;
	}
	
	
	
	$results=sqlite3_query($db,$sql);
	
	
	
	if(!$results){
			$errornum=sqlite3_error($db);
			WriteLogs("QUERY_SQL:Error Number $errornum",__FUNCTION__,__FILE__);
			return null;}	
	//var_dump(sqlite3_fetch_array($results));
	//sqlite3_query_close($query);
	//sqlite3_close($db);
	return $results;
	}
	
	
class MySqlQueries{
	
	function MySqlQueries(){
 		if (!extension_loaded('sqlite3')) {
                echo "<H1>Warning sqlite3 library is not loaded</H1>";
       			}
	}
	
	
	function today_OU_Total_mails($ou=null,$user=null){
		if($ou<>null){$q1="AND ou='$ou'";}
		if($user<>null){$q2="AND mail_to LIKE '%$user%'";}
		$sql="SELECT count(ID) as tcount FROM messages
		WHERE  strftime('%Y-%m-%d',received_date)='" . date('Y-m-d') . "'
		$q1 $q2
		";
		$ligne=@sqlite3_fetch_array(QUERY_SQL($sql));
		return $ligne["tcount"];
	}
	
	function today_OU_GEOIP($ou=null,$user=null){
		if($ou<>null){$q1="AND ou='$ou'";}
		if($user<>null){$q2="AND mail_to LIKE '%$user%'";}	
		$sql="SELECT COUNT(ID) as tcount, GeoCountry,strftime('%Y-%m-%d',received_date) as tdate FROM messages 
			GROUP BY GeoCountry HAVING strftime('%Y-%m-%d',received_date)='" . date('Y-m-d') . "' 
			$q1 $q2
			ORDER BY tcount DESC LIMIT 0,10";		
		
		return QUERY_SQL($sql);
		
	}
	
	function today_OU_GEOIP_CITY($country){
		$sql="SELECT COUNT(ID) as tcount, GeoCity,GeoCountry,strftime('%Y-%m-%d',received_date) as tdate FROM messages 
			GROUP BY GeoCity,GeoCountry HAVING strftime('%Y-%m-%d',received_date)='" . date('Y-m-d') . "' 
			AND GeoCountry='$country'
			ORDER BY tcount DESC LIMIT 0,20";		
		
		return QUERY_SQL($sql);
		
	}	
	

	function today_OU_Total_filtered($ou=null,$reason='spam',$user=null){
		if($ou<>null){$q1="AND ou='$ou'";}
		if($user<>null){$q2="AND mail_to LIKE '%$user%'";}
		$sql="SELECT count(ID) as tcount FROM messages
		WHERE  strftime('%Y-%m-%d',received_date)='" . date('Y-m-d') . "'
		$q1 $q2
		AND filter_action='$reason'
		";
		$ligne=@sqlite3_fetch_array(QUERY_SQL($sql));
		return $ligne["tcount"];
	}	
	
function LastReceiveMails($ou=null,$email_addr=null){
	if($email_addr<>null){$q1=" AND mail_to LIKE '%$email_addr%'";}
	if($ou<>null){$q2=" AND ou='$ou' ";}
	
	$sql="SELECT * FROM messages WHERE 1 $q1 $q2 ORDER BY ID DESC LIMIT 0,10";
	$results=QUERY_SQL($sql);
	$html="<div style='float:right'>" . imgtootltip('icon_refresh-20.gif','{refresh}',"LoadAjax('lastinfos','users.index.php?lastinfos=yes');")."</div><H4>{mail_events}</H4>";

	while($ligne=@sqlite3_fetch_array($results)){	
	$ligne["subject"]=htmlentities($ligne["subject"]);
	$ligne["mail_from"]=substr($ligne["mail_from"],0,40);
	
$relayed=$ligne["filter_action"];
			
			switch ($relayed) {
				case 'quarantine':
					$img='warning32.png';
					break;
				case 'sended':
					$img='ok32.png';
					break;
				case 'spam':
					$img='warning32.png';
					break;	
				case 'bogo':
					$img='warning32.png';
					break;									
				case 'attach_badlist_rename':
					$img='warning32.png';
					break;		
				case "filtered"	:
					$img='warning32.png';
					break;		
				case 'infected':
					$img='danger32.png';
					break;
				case 'faked':
					$img='warning32.png';
					break;					
				default:
					$img='ok32.png';
					break;
			}	
	
			if(strlen($ligne["subject"])>250){$ligne["subject"]=substr($ligne["subject"],0,250) . '...';}
	$html=$html .RoundedLightGrey( "
		<table>
		<tr>
			<td width=1% valign='top'><img src='img/mail4-64.png'></td>
			<td valign='top'>
				<table width=100%'>
				<tr>
					<td width=70%>
					<table style='width:100%'>
					<tr>
						<td>&laquo;&nbsp;{$ligne["subject"]}&nbsp;&raquo;</td>
					</tr>
					<tr>
						
						<td nowrap>
						<table style='width:100%'>
							<tr>
								<td width=1% nowrap>{$ligne["zDate"]}</td>
								<td width=1%><img src='img/mailbox.gif'></td>
								<td width=100%>{$ligne["mail_from"]}</td>
							</tr>
							<tr>
							<td>&nbsp;</td>
							<td width=1%><img src='img/upload.gif'></td>
							<td width=1% nowrap><i>{$ligne["GeoCity"]}&nbsp;|&nbsp;{$ligne["GeoCountry"]}</i></td>
							</tr>
						</table>
						</td>
					</tr>
					</table>
					</td>
					<td valign='top'>
					<table style='width:100%'>
					<tr><td width=1%><img src='img/$img'></td>
					<td width=99%><strong>{$ligne["filter_action"]}</td>
					</tr>
					</table>
					</td>
				</tr>
		</table>
		</td>
		</tr>
		</table>
	
		") . "<br>";
	}	
	

	return "<div id='maillogsH'>$html</div>";
}

function Quarantine_TOP_Domains($UsereMail=null,$ou=null){
	if($UsereMail<>null){
		$g1=",mail_to";
		$q1=" AND mail_to LIKE '%$UsereMail%'";}
	if($ou<>null){
		$g2=",ou";
		$q2=" AND ou='$ou' ";}
	
	$sql="SELECT count(ID) as tcount, mailfrom_domain,quarantine,Deleted $g1 $g2 from messages GROUP BY 
	mailfrom_domain,quarantine,Deleted $g1 $g2
	HAVING quarantine=1 AND Deleted=0 $q1 $q2 ORDER BY tcount DESC LIMIT 0,10";
	$results=QUERY_SQL($sql);
while($ligne=@sqlite3_fetch_array($results)){	
	
		$html=$html . "
		<tr>
		<td width=1% valign='top'><img src='img/eml_small.gif'></td>
		<td nowrap width=1%>{$ligne["tcount"]}</td>
		<td nowrap>{$ligne["mailfrom_domain"]}</td>
		</tr>
		";
	}
	return "<table>$html</table>";
}
function Quarantine_TOP_sender($UsereMail=null,$ou=null){
	if($UsereMail<>null){
		$g1=",mail_to";
		$q1=" AND mail_to LIKE '%$UsereMail%'";}
	if($ou<>null){
		$g2=",ou";
		$q2=" AND ou='$ou' ";}
	
	$sql="SELECT count(ID) as tcount, mail_from,quarantine,Deleted $g1 $g2 from messages GROUP BY 
	mail_from,quarantine,Deleted $g1 $g2
	HAVING quarantine=1 AND Deleted=0 $q1 $q2 ORDER BY tcount DESC LIMIT 0,10";
	$results=QUERY_SQL($sql);
while($ligne=@sqlite3_fetch_array($results)){	
	
		$html=$html . "
		<tr>
		<td width=1% valign='top'><img src='img/eml_small.gif'></td>
		<td nowrap width=1%>{$ligne["tcount"]}</td>
		<td nowrap>{$ligne["mail_from"]}</td>
		</tr>
		";
	}
	return "<table>$html</table>";
}
}


class mysql{
	var $mysql_server;
	var $mysql_admin;
	var $mysql_password;
	var $mysql_port;
	var $mysql_error;
	var $hostname;
	var $ok=false;
	var $CurrentPageName;
	var $mysql_connection;
	var $last_id;
	
	function mysql(){
		
		$phpPage=basename($_SERVER["SCRIPT_FILENAME"]);
	  
		$settings=dirname(__FILE__) . '/settings.inc';
		if(!function_exists('mysql_connect')){
			die("<div style='border:3px solid red;font-size:22px;color:red;padding:5px'>ERROR: Please install php mysql</div>");
			}
		$this->CurrentPageName=$phpPage;
		if(!is_file($settings)){
			die("Could not stat necessary file \"$settings\" (". __FILE__.")\n");
			}
		include($settings);
		$this->mysql_server=$_GLOBAL["mysql_server"];
		$this->mysql_admin=$_GLOBAL["mysql_admin"];
		$this->mysql_password=$_GLOBAL["mysql_password"];
		$this->hostname=$_GLOBAL["fqdn_hostname"];		
		$this->mysql_port=$_GLOBAL["mysql_port"];
		if($this->mysql_server==null){$this->mysql_server="127.0.0.1";}
		if($this->mysql_admin==null){$this->mysql_admin="root";}
		if($this->mysql_port==null){$this->mysql_port=3306;}
		
	}
	
	
	function TestingConnection(){
		if($GLOBALS["VERBOSE"]){echo "mysql_connect($this->mysql_server:$this->mysql_port,$this->mysql_admin,$this->mysql_password)\n";}
		$bd=@mysql_connect("$this->mysql_server:$this->mysql_port",$this->mysql_admin,$this->mysql_password);
		if(!$bd){
    		$des=mysql_error();
    		$this->mysql_error=$des;
    		$errnum=mysql_errno();
			if($GLOBALS["VERBOSE"]){echo "mysql_connect failed ($errnum) \"$des\"\n";}
    		$this->writeLogs("Error Number ($errnum) ($des) config:$this->mysql_server:$this->mysql_port@$this->mysql_admin:****",__CLASS__.'/'.__FUNCTION__,__FILE__);
    		if($errnum==0){	
    			if($this->mysql_password<>null){
    				if($this->mysql_server=="127.0.0.1"){
    					if($GLOBALS["VERBOSE"]){echo "framework --> ChangeMysqlLocalRoot()\n";}
    					$sock=new sockets();
    					$sock->getFrameWork("cmd.php?ChangeMysqlLocalRoot=$this->mysql_admin&password=$this->mysql_password");
    				}
    			}
    		}
    		
    		
    		return false;
		}
		
		return true;
		
	}
	
		function writelogs($text=null,$function=null,$line=0){
		$file_source="/usr/share/artica-postfix/ressources/logs/web/mysql.debug";
		@mkdir('/usr/share/artica-postfix/ressources/logs/web');
	    $logFile=$file_source;
		if(!is_dir(dirname($logFile))){mkdir(dirname($logFile));}
			if (is_file($logFile)) { 
		   			$size=filesize($logFile);
				    	if($size>1000000){unlink($logFile);}
		   		}
		
				$f = @fopen($logFile, 'a');
				$date=date("Y-m-d H:i:s");
				@fwrite($f, "$date:[$function()][{$_SERVER['REMOTE_ADDR']}]:: $text (L.$line)\n");
				@fclose($f);
		}	
	
	
	function DATABASE_STATUS($database){
		$results=$this->QUERY_SQL("SHOW TABLE STATUS",$database);
		$html="<table style='width:100%;margin:5px;border:1px solid #CCCCCC'>
		<tr>
				<td colspan=4 align='left' style='background-color:#CCCCCC'><strong style='font-size:14px'>$database</strong></td>
			</tr>";
		while ($row = mysql_fetch_array($results)) { 
 			$dbsize += $row['Data_length'] + $row['Index_length']; 
 			$html=$html."<tr>
 			<td width=1%><img src='img/fw_bold.gif'></td>
 			<td><strong>{$row["Name"]}</strong></td>
 			<td><strong>{$row["Rows"]}</strong></td>
 			<td><strong>" . FormatBytes($row["Data_length"]/1024)."</td>
 			</tr>";
			
			} 
			
			$html=$html . "
			<tr>
				<td colspan=4 align='right' style='background-color:#CCCCCC'><strong style='font-size:13px'>".FormatBytes($dbsize/1024)."</strong></td>
			</tr>
			
			</table>";
		return $html;
	}
	
	function REPAIR_TABLES($database){
		$bd=@mysql_connect("$this->mysql_server:$this->mysql_port",$this->mysql_admin,$this->mysql_password);
  	 	$mysql_result = mysql_query("SHOW TABLES;",$bd);
 		while ($ligne = mysql_fetch_row($mysql_result)){
 			$table_name=$ligne[0];
 			echo "<div><code>Repair $database/$table_name</code></div>";
 			$this->REPAIR_TABLE($database,$table_name);
     	}
	}
	
	public function ReadBinaryFile($filepath){
		$filesize=filesize($filepath);
		$fileHandle = fopen($filepath, "r");
		$fileContent = fread($fileHandle, $filesize);
		$fileContent = addslashes($fileContent);
		return $fileContent;
		}
		
		
		
	public function test_mysql_connection(){
		$bd=@mysql_connect("$this->mysql_server:$this->mysql_port",$this->mysql_admin,$this->mysql_password);
		if(!$bd){
			$errnum=mysql_error();
	    	$des=mysql_error();
	    	writelogs("Error $errnum, $des",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	    	return false;
		}
		return true;
	}
	
	
	function QUERY_SQL($sql,$database=null){
		$database=trim($database);
		$this->ok=true;
		if(!function_exists('mysql_connect')){
			$this->mysql_error="mysql_connect() function does not exists";
			$this->ok=false;
			return null;
		}
		$bd=@mysql_connect("$this->mysql_server:$this->mysql_port",$this->mysql_admin,$this->mysql_password);
		$this->mysql_connection=$bd;
		if($database<>null){
			$ok=@mysql_select_db($database);
	    	if (!$ok){
	    		$errnum=mysql_error();
	    		$des=mysql_error();
	    		$this->mysql_error=$des;
	    		$time=date('h:i:s');
	    		
	    			$page=$this->CurrentPageName;
						if($page=='obm.export.php' OR $page=="exec.obm.synchro.php"){
							echo "Error Number ($errnum) ($des) config:$this->mysql_server:$this->mysql_port@$this->mysql_admin:****\n";
							echo "Database selected=$database\n";
						}
	    		
	    		$this->writeLogs("QUERY_SQL:: DB:\"$database\" Error Number ($errnum) ($des) config:$this->mysql_server:$this->mysql_port@$this->mysql_admin:****",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	    		$this->mysql_error="QUERY_SQL:: Error Number ($errnum) ($des) config:$this->mysql_server:$this->mysql_port@$this->mysql_admin:****";
	    		
	    		$this->ok=false;
	    		return null;
    		}
		}	
	$results=mysql_query($sql);
	
	
	
	
		if(mysql_error()){
				$time=date('h:i:s');
				$errnum=mysql_errno();
				$des=mysql_error();
				$this->mysql_error="Error Number ($errnum) ($des)";
				
				if(preg_match('#try to repair it#',$des)){
					$this->writeLogs("QUERY_SQL:: DB:\"$database\" Error $errnum -> i will try to repair it",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$this->REPAIR_TABLES($database);
				}
				
				if(preg_match("#Table '(.+?)' is marked as crashed and should be repaired#",$des,$re)){
					echo "<H2>DB:$database Error table {$re[1]} is crashed $errnum : i will try to repair it</H2>";
					$this->REPAIR_TABLE($database,$re[1]);
					$this->writeLogs("QUERY_SQL:: DB:\"$database\" Error table {$re[1]} is crashed $errnum -> i will try to repair it",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				}
				
				

				
				if(preg_match('#Duplicate entry#',$des)){
					$this->writeLogs("QUERY_SQL:: DB:\"$database\" Error $errnum $des line:".__LINE__,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$this->writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__);
					@mysql_close($bd);
					$this->ok=true;
					return true;
				}				
				

				if(preg_match('#/(.+?)/(.+?)\'\s+.+?crashed.+?(repaired|repair)#',$des,$re)){
					if(!preg_match('#REPAIR TABLE#',$sql)){
						$this->writeLogs("Try to repair {$re[2]}",__CLASS__.'/'.__FUNCTION__,__FILE__);
						$this->REPAIR_TABLE($re[1],$re[2]);
						$this->ok=false;
						return null;
					}
				}
				
				if(preg_match("#Table.+?quarantine.+?doesn't exist#",$des)){
						if(!$GLOBALS["CHECKMYSQLTABLE"]["quarantine"]){
							$this->writeLogs("Try to build quarantine table",__CLASS__.'/'.__FUNCTION__,__FILE__);
							$GLOBALS["CHECKMYSQLTABLE"]["quarantine"]=true;
							$this->Check_quarantine_table();
							$this->ok=true;
							return null;
						}
					
				}

				if(preg_match("#Table.+?storage.+?doesn't exist#",$des)){
							if(!$GLOBALS["CHECKMYSQLTABLE"]["storage"]){
								$GLOBALS["CHECKMYSQLTABLE"]["storage"]=true;
								$this->writeLogs("Try to build storage table",__CLASS__.'/'.__FUNCTION__,__FILE__);
								$this->check_storage_table();
								$this->ok=true;
								return null;
							}
					
				}					
				
				
				
				$this->writeLogs("QUERY_SQL:: DB:\"$database\" Error [".mysql_errno()."] line:".__LINE__,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$this->writeLogs("QUERY_SQL:: DB ".mysql_error(),__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$this->writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__);
				
				if($errnum==1062){
					$this->ok=true;
					@mysql_close($bd);
					return true;
				}				
				
				$this->ok=false;
				return null;
				
				
				
		}	
		
	$this->last_id=@mysql_insert_id();	
	$this->ok=true;
	$result_return=$results;
	//@mysql_free_result($result); 
	@mysql_close($bd);
	return $result_return;}
	
	
	function _mysql_close(){
		@mysql_close($this->mysql_connection);
	}

	
	function REPAIR_TABLE($database,$table){
		$this->writeLogs("Try to repair $database/$table",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sql="REPAIR TABLE $table EXTENDED";
		$this->QUERY_SQL($sql,$database);
		
	}
	
	function SIMPLE_GROUPWARE_ENABLED($userid){
		$database="SimpleGroupware";
		$sql="SELECT id,activated FROM simple_sys_users WHERE username='$userid' LIMIT 0,1";
		$ligne=@mysql_fetch_array($this->QUERY_SQL($sql,$database));
		if(!is_numeric($ligne["id"])){return false;}
		if($ligne["activated"]=="0"){return false;}
		return true;
		}
		
		
	function SET_SIMPLE_GROUPWARE_ACTIVE($userid,$active){
		$database="SimpleGroupware";
		$sql="SELECT id,activated FROM simple_sys_users WHERE username='$userid' LIMIT 0,1";
		$ligne=@mysql_fetch_array($this->QUERY_SQL($sql,$database));
		if(!is_numeric($ligne["id"])){
			$this->SIMPLE_GROUPWARE_USER_ADD($userid,$active);
			return true;}
			
		$this->SIMPLE_GROUPWARE_USER_EDIT($ligne["id"],$active);
		
	}
	
	function SIMPLE_GROUPWARE_USER_EDIT($sql_id,$active){
		$database="SimpleGroupware";
		$sql="UPDATE simple_sys_users SET activated='$active' WHERE id='$sql_id'";
		$this->QUERY_SQL($sql,$database);
		
	}
	
	function SIMPLE_GROUPWARE_USER_ADD($userid,$active){
		$user=new user($userid);
		$md=md5($user->password);
		$database="SimpleGroupware";
		$sql="INSERT INTO `simple_sys_users` (
		`id`, 
		`username`, 
		`password`, 
		`email`, 
		`firstname`, 
		`lastname`, 
		`neverexp`, 
		`activated`)
		VALUES(
		'$user->uidNumber',
		'$userid',
		'$md',
		'$user->mail',
		'$user->givenName',
		'$user->sn',
		'1',
		'$active')";
		$this->QUERY_SQL($sql,$database);
		
	}
	
		
	
	
	function TABLE_EXISTS($table,$database){
		if($GLOBALS["__MYSQL_TABLE_EXISTS"][$database][$table]==true){return true;}
		if($_SESSION["__MYSQL_TABLE_EXISTS"][$database][$table]==true){return true;}
		if(!$this->DATABASE_EXISTS($database)){
			$this->writeLogs("Database $database does not exists...create it",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->CREATE_DATABASE($database);
			if(!$this->DATABASE_EXISTS($database)){$this->writeLogs("Unable to create $database database",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return false;}
		}
		
		
		
		
		$sql="SHOW TABLES";
		$results=$this->QUERY_SQL($sql,$database);
	
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if(strtolower($table)==strtolower($ligne["Tables_in_$database"])){
				$GLOBALS["__MYSQL_TABLE_EXISTS"][$database][$table]=true;
				$_SESSION["__MYSQL_TABLE_EXISTS"][$database][$table]=true;
				return true;
			}
		}
		$this->writeLogs("testing \"$table\" failed in $database",__CLASS__.'/'.__FUNCTION__,__FILE__);
		return false;
		
	}
	
	
	function DATABASE_EXISTS($database){
		if($_SESSION["MYSQL_DATABASE_EXISTS"][$database]==true){return true;}
		$sql="SHOW DATABASES";
		$bd=@mysql_connect("$this->mysql_server:$this->mysql_port",$this->mysql_admin,$this->mysql_password);
		$results=@mysql_query($sql);
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if(strtolower($database)==strtolower($ligne["Database"])){
				$_SESSION["MYSQL_DATABASE_EXISTS"][$database]=true;
				return true;
			}
		}
		
		return false;
		
	}
	
	function DATABASE_LIST(){
		$sql="SHOW DATABASES";
		$bd=@mysql_connect("$this->mysql_server:$this->mysql_port",$this->mysql_admin,$this->mysql_password);
		
		if(!$bd){
    		$errnum=mysql_error();
    		$des=mysql_error();
    		$this->mysql_error=$des;
    		return null;
		}
		
		
		$results=@mysql_query($sql);
		
		$errnum=mysql_error();
    	$des=mysql_error();
    	$this->mysql_error=$des;
		
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$Database=$ligne["Database"];
			$array[$Database]=$this->DATABASE_INFOS($Database);
			}
			
			return $array;
			
		}
		
		
	function TABLES_LIST($database){
		$sql="SHOW TABLE STATUS";
		$results=$this->QUERY_SQL($sql,$database);
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$dbsize= $ligne['Data_length'] + $ligne['Index_length']; 
			$dbsize_text=ParseBytes($dbsize);
			$Name=$ligne["Name"];
			$Rows=$ligne["Rows"];
			$Max_data_length=$ligne["Max_data_length"];
			$array[$Name]=array($dbsize,$dbsize_text,$Rows,$Max_data_length);
			
		}
		
		return $array;
		
	}
		
		
		
	

	private function DATABASE_INFOS($database){
		$sql="show TABLE STATUS";
		$results=$this->QUERY_SQL($sql,$database);
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$dbsize += $ligne['Data_length'] + $ligne['Index_length']; 
			$count=$count+1;
		}
		
		return array($count,ParseBytes($dbsize));
		
	}
	
	
	
	FUNCTION CREATE_DATABASE($database){
		$bd=@mysql_connect("$this->mysql_server:$this->mysql_port",$this->mysql_admin,$this->mysql_password);
		$results=@mysql_query("CREATE DATABASE $database");
		
	}
	
	FUNCTION CHECK_PERFORMANCES(){
		$bd=@mysql_connect("$this->mysql_server:$this->mysql_port",$this->mysql_admin,$this->mysql_password);
		$results=@mysql_query("SELECT benchmark(100000000,1+2);");
	}
	
	FUNCTION DELETE_DATABASE($database){
		$bd=@mysql_connect("$this->mysql_server:$this->mysql_port",$this->mysql_admin,$this->mysql_password);
		$results=@mysql_query("DROP DATABASE $database");	
	}
	
	
	FUNCTION COUNT_ROWS($table,$database){
		$sql="SELECT COUNT(*) as tcount FROM $table";
		$ligne=@mysql_fetch_array($this->QUERY_SQL($sql,$database));
		if($ligne["tcount"]==null){$ligne["tcount"]=0;}
		return $ligne["tcount"];
		}
		
	FUNCTION DATABASE_SIZE($database){
		$sql="SHOW table STATUS";
		$dbsize=0;
		$results=$this->QUERY_SQL($sql,$database);
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$dbsize += $ligne['Data_length'] + $ligne['Index_length'];
		}
		$dbsize=round(($dbsize/1024),2);
		if($dbsize>1000){
			$dbsize=round($dbsize/1000,1) . " Mb";
		}else{
			$dbsize=$dbsize . " kb";
		}
		return $dbsize;
		
	}
	
	FUNCTION INDEX_EXISTS($table,$index,$database){
		
		
		if($_SESSION["MYSQL_INDEX_EXISTS"][$database][$table][$index]==true){return true;}
		$sql="SHOW INDEX FROM $table WHERE Key_name='$index'";
		
		
		$ligne=@mysql_fetch_array($this->QUERY_SQL($sql,$database));
		
		$this->writeLogs("Key_name={$ligne["Key_name"]}",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		if($ligne["Key_name"]<>null){
			$_SESSION["MYSQL_INDEX_EXISTS"][$database][$table][$index]=true;
			return true;
		}else{return true;}
	}
	
	
	FUNCTION FIELD_EXISTS($table,$field,$database){
		if($GLOBALS["__FIELD_EXISTS"][$database][$table][$field]==true){return true;}
		$sql="SHOW FULL FIELDS FROM $table WHERE Field='$field';";
		///writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$ligne=@mysql_fetch_array($this->QUERY_SQL($sql,$database));
		
		if($ligne["Field"]<>null){
			$GLOBALS["__FIELD_EXISTS"][$database][$table][$field]=true;
			return true;
		}else{return false;}		
		
		
	}
	
	FUNCTION CREATE_INDEX($table,$index,$database){
		$sql="ALTER TABLE `$table` ADD INDEX ( `$index` )";
		$this->QUERY_SQL($sql,$database);
		
	}
	
	function Check_iptables_table(){
		if(!$this->TABLE_EXISTS('iptables','artica_backup')){	
			$sql="
			CREATE TABLE IF NOT EXISTS `iptables` (
			  `ID` int(5) NOT NULL auto_increment,
			  `servername` varchar(80) default NULL,
			  `serverip` varchar(30) NOT NULL,
			  `service` varchar(20) NOT NULL,
			  `local_port` int(10) NOT NULL,
			  `disable` int(1) NOT NULL,
			  `events_number` int(10) NOT NULL,
			  `iptables_id` int(5) NOT NULL,
			  `rule_string` varchar(255) NOT NULL,
			  `saved_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
			  `rulemd5` varchar(255) NOT NULL,
			  `flux` varchar(10) NOT NULL,
			  `log` int(1) NOT NULL default '0',
			  PRIMARY KEY  (`ID`),
			  UNIQUE KEY `rulemd5` (`rulemd5`),
			  KEY `servername` (`servername`,`serverip`,`service`,`local_port`,`disable`,`events_number`),
			  KEY `iptables_id` (`iptables_id`),
			  KEY `rule_string` (`rule_string`),
			  KEY `saved_date` (`saved_date`),
			  KEY `flux` (`flux`,`log`)
			) 
			";
		
			$this->QUERY_SQL($sql,'artica_backup');
		}
		
		if(!$this->FIELD_EXISTS("iptables","rulemd5","artica_backup")){	
			$sql="ALTER TABLE `iptables` ADD `rulemd5` VARCHAR( 255 ) NOT NULL ,ADD UNIQUE (`rulemd5`)";
			$this->QUERY_SQL($sql,'artica_backup');
		}

		if(!$this->FIELD_EXISTS("iptables","flux","artica_backup")){	
			$sql="ALTER TABLE `iptables` ADD `flux` VARCHAR( 10 ) NOT NULL ,ADD INDEX (`flux`)";
			$this->QUERY_SQL($sql,'artica_backup');
		}		

		if(!$this->FIELD_EXISTS("iptables","log","artica_backup")){	
			$sql="ALTER TABLE `iptables` ADD `log` INT( 1 ) NOT NULL ,ADD INDEX (`log`)";
			$this->QUERY_SQL($sql,'artica_backup');
		}				


		
	}
	
	
	function Check_smtp_logs_table(){
		if(!$this->FIELD_EXISTS("smtp_logs","time_amavis","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `time_amavis` DATETIME NULL ,ADD INDEX ( `time_amavis` )";
			$this->QUERY_SQL($sql,'artica_events');
		}
		
		if(!$this->FIELD_EXISTS("smtp_logs","Country","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `Country` VARCHAR( 25 ) NULL ,ADD INDEX ( `Country` )";
			$this->QUERY_SQL($sql,'artica_events');
		}	

		if(!$this->FIELD_EXISTS("smtp_logs","Region","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `Region` VARCHAR( 25 ) NULL ,ADD INDEX ( `Region` )";
			$this->QUERY_SQL($sql,'artica_events');
		}

		if(!$this->FIELD_EXISTS("smtp_logs","kas","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `kas` INT( 3 ) NOT NULL DEFAULT '0',ADD INDEX ( `kas` ) ";
			$this->QUERY_SQL($sql,'artica_events');
		}	

		if(!$this->FIELD_EXISTS("smtp_logs","infected","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `infected` INT( 1 ) NOT NULL DEFAULT '0',ADD INDEX ( `infected` )";
			$this->QUERY_SQL($sql,'artica_events');
		}

		if(!$this->FIELD_EXISTS("smtp_logs","spammy","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `spammy` INT( 1 ) NOT NULL DEFAULT '0',ADD INDEX ( `spammy` )";
			$this->QUERY_SQL($sql,'artica_events');
		}	

		if(!$this->FIELD_EXISTS("smtp_logs","blacklisted","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `blacklisted` INT( 1 ) NOT NULL DEFAULT '0',ADD INDEX ( `blacklisted` )";
			$this->QUERY_SQL($sql,'artica_events');
		}

		if(!$this->FIELD_EXISTS("smtp_logs","whitelisted","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `whitelisted` INT( 1 ) NOT NULL DEFAULT '0',ADD INDEX ( `whitelisted` )";
			$this->QUERY_SQL($sql,'artica_events');
		}	
		
		if(!$this->FIELD_EXISTS("smtp_logs","smtp_sender","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `smtp_sender` VARCHAR( 55 ) NULL ,ADD INDEX ( `smtp_sender` ) ";
			$this->QUERY_SQL($sql,'artica_events');
		}	
		

		
	}
	
	
	public function CheckTablesPostfilter(){
		if(!$this->DATABASE_EXISTS("postfilter")){
			$this->CREATE_DATABASE("postfilter");
		}
		if(!$this->DATABASE_EXISTS("postfilter")){return false;}
		
		if(!$this->TABLE_EXISTS('spamtrap','postfilter')){
			$sql="CREATE TABLE spamtrap (
				_key CHAR(64) NOT NULL default '',
				_value CHAR(32) NOT NULL default '',
				_timestamp TIMESTAMP,
				UNIQUE INDEX (_key)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}

	if(!$this->TABLE_EXISTS('dha','postfilter')){
			$sql="CREATE TABLE dha (
					_key CHAR(64) NOT NULL default '',
					_value CHAR(32) NOT NULL default '',
					_timestamp TIMESTAMP,
					UNIQUE INDEX (_key)
				) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}

	if(!$this->TABLE_EXISTS('greylist_white','postfilter')){
			$sql="CREATE TABLE greylist_white (
				_key CHAR(64) NOT NULL default '',
				_value CHAR(32) NOT NULL default 'auto-whitelisted',
				_timestamp TIMESTAMP,
				UNIQUE INDEX (_key)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}			
		
	if(!$this->TABLE_EXISTS('greylist','postfilter')){
			$sql="CREATE TABLE greylist (
				_sender CHAR(64) NOT NULL default '',
				_recipient CHAR(64) NOT NULL default '',
				_ip char(40) NOT NULL default '',
				_last_seen TIMESTAMP,
				_first_seen TIMESTAMP,
				_count SMALLINT UNSIGNED NOT NULL default 0,
				UNIQUE KEY _ip (_ip,_sender,_recipient),
				KEY _last_seen (_last_seen),
				KEY _first_seen (_first_seen)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}
		
		if(!$this->TABLE_EXISTS('honeypot','postfilter')){
			$sql="CREATE TABLE honeypot (
					_key CHAR(64) NOT NULL default '',
					_value CHAR(32) NOT NULL default '',
					_timestamp TIMESTAMP,
					UNIQUE INDEX (_key)
				) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}		
		
		

		if(!$this->TABLE_EXISTS('throttle','postfilter')){
			$sql="CREATE TABLE throttle (
					_address CHAR(64) NOT NULL default '',
					_count_max MEDIUMINT UNSIGNED NOT NULL default 0,
					_count_curr MEDIUMINT UNSIGNED NOT NULL default 1,
					_rcpt_max MEDIUMINT UNSIGNED NOT NULL default 0,
					_rcpt_curr MEDIUMINT UNSIGNED NOT NULL default 1,
					_quota_max INT UNSIGNED NOT NULL default 0,
					_quota_curr INT UNSIGNED NOT NULL default 0,
					_disabled INT UNSIGNED NOT NULL default 0,
					_date INT UNSIGNED NOT NULL default 0,
					_time_period MEDIUMINT UNSIGNED NOT NULL default 0,
					_priority TINYINT UNSIGNED NOT NULL default 0,
					UNIQUE KEY (_address)
				) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}
		
		if(!$this->TABLE_EXISTS('captcha','postfilter')){
			$sql="CREATE TABLE captcha (
				_sender CHAR(64) NOT NULL DEFAULT '',
				_recipient CHAR(64) NOT NULL DEFAULT '',
				_captcha CHAR(32),
				_timestamp TIMESTAMP,
				_try TIMESTAMP,
				_probes TINYINT UNSIGNED DEFAULT 0,
				_used TINYINT UNSIGNED DEFAULT 0,
				UNIQUE INDEX (_sender,_recipient)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}	
		
		if(!$this->TABLE_EXISTS('cookie','postfilter')){
			$sql="CREATE TABLE cookie (
				_address CHAR(64) NOT NULL DEFAULT '',
				_cookie CHAR(32),
				_timestamp TIMESTAMP,
				UNIQUE INDEX (_address)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}	

		if(!$this->TABLE_EXISTS('banned_client','postfilter')){
			$sql="CREATE TABLE banned_client (
				_key CHAR(64) NOT NULL default '',
				_value CHAR(32) NOT NULL default '',
				_timestamp TIMESTAMP,
				UNIQUE INDEX (_key)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}

		if(!$this->TABLE_EXISTS('banned_sender','postfilter')){
			$sql="CREATE TABLE banned_sender (
					_key CHAR(64) NOT NULL default '',
					_value CHAR(32) NOT NULL default '',
					_timestamp TIMESTAMP,
					UNIQUE INDEX (_key)
				) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}	

		if(!$this->TABLE_EXISTS('banned_helo','postfilter')){
			$sql="CREATE TABLE banned_helo (
				_key CHAR(64) NOT NULL default '',
				_value CHAR(32) NOT NULL default '',
				_timestamp TIMESTAMP,
				UNIQUE INDEX (_key)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}	

		if(!$this->TABLE_EXISTS('blacklist','postfilter')){
			$sql="CREATE TABLE blacklist (
				_key CHAR(64) NOT NULL default '',
				_value CHAR(32) NOT NULL default '',
				_timestamp TIMESTAMP,
				UNIQUE INDEX (_key)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}	

		if(!$this->TABLE_EXISTS('whitelist','postfilter')){
			$sql="CREATE TABLE whitelist (
				_key CHAR(64) NOT NULL default '',
				_value CHAR(32) NOT NULL default '',
				_timestamp TIMESTAMP,
				UNIQUE INDEX (_key)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}	

		if(!$this->TABLE_EXISTS('helo','postfilter')){
			$sql="CREATE TABLE helo (
				_key CHAR(64) NOT NULL default '',
				_value CHAR(32) NOT NULL default '',
				_timestamp TIMESTAMP,
				UNIQUE INDEX (_key)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}

		if(!$this->TABLE_EXISTS('user_whitelist','postfilter')){
			$sql="CREATE TABLE user_whitelist (
				_userid INT UNSIGNED NOT NULL,
				_address CHAR(64) NOT NULL default '',
				_type BOOL DEFAULT 0,
				_timestamp TIMESTAMP,
				INDEX (_userid),
				UNIQUE INDEX (_userid, _address)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}
		
		if(!$this->TABLE_EXISTS('user','postfilter')){
			$sql="CREATE TABLE user (
				_userid INT UNSIGNED NOT NULL AUTO_INCREMENT,
				_master INT UNSIGNED NOT NULL DEFAULT 0,
				_address CHAR(64) NOT NULL default '',
				_passwd CHAR(40) DEFAULT '',
				_type BOOL DEFAULT 0,
				_mode BOOL DEFAULT 0,
				_hidden BOOL DEFAULT 0,
				_filter CHAR(255) NOT NULL DEFAULT 'permit',
				_lang CHAR(2) DEFAULT '',
				PRIMARY KEY (_userid),
				INDEX (_master),
				UNIQUE INDEX (_address)
			) TYPE=MyISAM;	"; 
			$this->QUERY_SQL($sql,'postfilter');
			$sql="ALTER TABLE user AUTO_INCREMENT=1;";
			$this->QUERY_SQL($sql,'postfilter');
		}	

			if(!$this->TABLE_EXISTS('config','postfilter')){
			$sql="CREATE TABLE config (
				_version INT UNSIGNED NOT NULL AUTO_INCREMENT,
				_config MEDIUMTEXT,
				_timestamp TIMESTAMP,
				UNIQUE INDEX (_version)
			) TYPE=MyISAM"; 
			$this->QUERY_SQL($sql,'postfilter');
		}

			if(!$this->TABLE_EXISTS('report','postfilter')){
			$sql="CREATE TABLE report (
				_sender CHAR(64),
				_recipient CHAR(64) NOT NULL DEFAULT '',
				_client_name CHAR(128),
				_client_address CHAR(40),
				_username CHAR(64) NOT NULL DEFAULT '',
				_class CHAR(32),
				_policy CHAR(32),
				_report BOOL DEFAULT 1,
				_timestamp TIMESTAMP,
				INDEX (_recipient)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
		}			

			if(!$this->TABLE_EXISTS('statistics','postfilter')){
			$sql="CREATE TABLE statistics (
				_entry CHAR(64) NOT NULL DEFAULT '',
				_type CHAR(64) NOT NULL DEFAULT '',
				_info CHAR(128) NOT NULL DEFAULT '',
				_count INT UNSIGNED NOT NULL DEFAULT 0,
				UNIQUE KEY (_entry, _type)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
		}

		return true;
	}
	
	
	public function checkRoundCubeTables($database){
		
		$posix_getuid=posix_getuid();
		
		if(!$this->TABLE_EXISTS('contactgroupmembers',$database)){
			if($posix_getuid==0){echo "Starting......: roundcube $database.contactgroupmembers did not exists (create it)\n";}
			
		$sql="CREATE TABLE `contactgroupmembers` (
  			`contactgroup_id` int(10) UNSIGNED NOT NULL,
  			`contact_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  			`created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
			  PRIMARY KEY (`contactgroup_id`, `contact_id`),
			  CONSTRAINT `contactgroup_id_fk_contactgroups` FOREIGN KEY (`contactgroup_id`)
			  REFERENCES `contactgroups`(`contactgroup_id`) ON DELETE CASCADE ON UPDATE CASCADE,
			  CONSTRAINT `contact_id_fk_contacts` FOREIGN KEY (`contact_id`)
			  REFERENCES `contacts`(`contact_id`) ON DELETE CASCADE ON UPDATE CASCADE)
			  ";
		$this->QUERY_SQL($sql,$database);
		if(!$this->ok){if($posix_getuid==0){echo "Starting......: roundcube $this->mysql_error\n";}}
		}else{
			if($posix_getuid==0){echo "Starting......: roundcube $database.contactgroupmembers exists\n";}
		}
		
		if(!$this->TABLE_EXISTS('contactgroups',$database)){
			if($posix_getuid==0){echo "Starting......: roundcube $database.contactgroups did not exists (create it)\n";}
			$sql="CREATE TABLE `contactgroups` (
			  `contactgroup_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
			  `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
			  `del` tinyint(1) NOT NULL DEFAULT '0',
			  `name` varchar(128) NOT NULL DEFAULT '',
			  PRIMARY KEY(`contactgroup_id`),
			  CONSTRAINT `user_id_fk_contactgroups` FOREIGN KEY (`user_id`)
			    REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
			  INDEX `contactgroups_user_index` (`user_id`,`del`)
			)";		
			$this->QUERY_SQL($sql,$database);
			if(!$this->ok){if($posix_getuid==0){echo "Starting......: roundcube $this->mysql_error\n";}}
		}else{
			if($posix_getuid==0){echo "Starting......: roundcube $database.contactgroups exists\n";}
		}
		
		
		if(!$this->FIELD_EXISTS("identities","changed",$database)){	
			if($posix_getuid==0){echo "Starting......: roundcube $database.identities.changed did not exists (create it)\n";}
			$sql="ALTER TABLE `identities` ADD INDEX `user_identities_index` (`user_id`, `del`);";	
			$this->QUERY_SQL($sql,$database);
			if(!$this->ok){if($posix_getuid==0){echo "Starting......: roundcube $this->mysql_error\n";}}
			$sql="ALTER TABLE `identities` ADD `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' AFTER `user_id`;";
			$this->QUERY_SQL($sql,$database);
			if(!$this->ok){if($posix_getuid==0){echo "Starting......: roundcube $this->mysql_error\n";}}
		}
		
		
		

		
		
	}
	
	
	
	private function checkAgentTables(){
			if(!$this->TABLE_EXISTS('computers_drives','artica_backup')){
			$sql="CREATE TABLE `artica_backup`.`computers_drives` (
				`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`MAC` VARCHAR( 25 ) NOT NULL ,
				`letter` VARCHAR( 1 ) NOT NULL ,
				`path` VARCHAR( 255 ) NOT NULL ,
				INDEX ( `MAC` , `letter` , `path` )
				)"; 
			$this->QUERY_SQL($sql,'artica_backup');
			}
			
			if(!$this->TABLE_EXISTS('computers_tasks','artica_backup')){
			$sql="CREATE TABLE IF NOT EXISTS `computers_tasks` (
					  `ID` int(11) NOT NULL AUTO_INCREMENT,
					  `MAC` varchar(60) NOT NULL,
					  `task_type` varchar(20) NOT NULL,
					  `schedule` varchar(80) NOT NULL,
					  `task_enabled` int(1) NOT NULL,
					  `path` varchar(255) NOT NULL,
					  PRIMARY KEY (`ID`),
					  KEY `MAC` (`MAC`,`task_type`,`task_enabled`)
					)";	
			$this->QUERY_SQL($sql,'artica_backup');
			}
			
			
			if(!$this->TABLE_EXISTS('computers_tasks','artica_backup')){
			$sql="CREATE TABLE `artica_backup`.`computers_events` (
				`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`MAC` VARCHAR( 80 ) NOT NULL ,
				`zDate` DATETIME NOT NULL ,
				`events` TEXT NOT NULL ,
				`events_type` VARCHAR( 60 ) NOT NULL ,
				INDEX ( `MAC` , `zDate` , `events_type` )
				)";	
				$this->QUERY_SQL($sql,'artica_backup'); 
			}
			
		if(!$this->TABLE_EXISTS('computers_orders','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`computers_orders` (
						`ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
						`taskid` INT( 10 ) NOT NULL ,
						`task_type` VARCHAR( 20 ) NOT NULL ,
						`parameters` TEXT NOT NULL ,
						`MAC` VARCHAR( 80 ) NOT NULL ,
						INDEX ( `taskid` , `task_type` , `MAC` )
						)";	
				$this->QUERY_SQL($sql,'artica_backup'); 
			}
			
			
		if(!$this->TABLE_EXISTS('computers_parameters','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`computers_parameters` (
				`ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`MAC` VARCHAR( 50 ) NOT NULL ,
				`parameters` TEXT NOT NULL ,
				INDEX ( `MAC` )
				)";	
				$this->QUERY_SQL($sql,'artica_backup'); 
			}			
		
	}
	
	
	private function CheckTableVirus(){
		
	if(!$this->TABLE_EXISTS('antivirus_events','artica_events')){
			$sql="CREATE TABLE IF NOT EXISTS `antivirus_events` (
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `zDate` datetime NOT NULL,
			  `TaskName` varchar(90) NOT NULL,
			  `email` int(1) NOT NULL DEFAULT '0',
			  `VirusName` varchar(60) NOT NULL,
			  `InfectedPath` varchar(255) NOT NULL DEFAULT '',
			  `ComputerName` varchar(150) NOT NULL,
			  `zmd5` varchar(90) NOT NULL,
			  PRIMARY KEY (`ID`),
			  UNIQUE KEY `zmd5` (`zmd5`),
			  KEY `zDate` (`zDate`,`TaskName`,`email`,`VirusName`),
			  KEY `ComputerName` (`ComputerName`)
			)";	
			$this->QUERY_SQL($sql,'artica_events');
			}
			
		if(!$this->FIELD_EXISTS("antivirus_events","zmd5","artica_events")){	
			$sql="ALTER TABLE `antivirus_events` ADD `zmd5` varchar(90) NOT NULL ,ADD UNIQUE KEY `zmd5` (`zmd5`)";
			$this->QUERY_SQL($sql,'artica_events');
		}				
		
	}
	
	public function check_emailing_tables(){
		if(!$this->TABLE_EXISTS('emailing_db_paths','artica_backup')){
		
		$sql="CREATE TABLE IF NOT EXISTS `emailing_db_paths` (
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `filepath` varchar(255) NOT NULL,
			  `databasename` varchar(255) NOT NULL,
			  `progress` int(2) NOT NULL,
			  `finish` int(1) NOT NULL DEFAULT '0',
			  `reports_import` text NOT NULL,
			  `ou` varchar(255) NOT NULL,
			  `zDate` datetime NOT NULL,
			  `progress_export` int(11) NOT NULL,
			  `options` TINYTEXT NOT NULL,
			  PRIMARY KEY (`ID`),
			  UNIQUE KEY `databasename` (`databasename`),
			  KEY `finish` (`finish`),
			  KEY `ou` (`ou`),
			  KEY `zDate` (`zDate`)
			) ";
				$this->QUERY_SQL($sql,'artica_backup');
		}
		
		
		if(!$this->FIELD_EXISTS("zDate","emailing_db_paths","artica_backup")){
				$sql="ALTER TABLE `emailing_db_paths` ADD `zDate` datetime NOT NULL,ADD INDEX ( `zDate` )";
				$this->QUERY_SQL($sql,'artica_backup');
		}

		if(!$this->FIELD_EXISTS("progress_export","emailing_db_paths","artica_backup")){
				$sql="ALTER TABLE `emailing_db_paths` ADD `progress_export` int(11) NOT NULL";
				$this->QUERY_SQL($sql,'artica_backup');
		}	

		if(!$this->FIELD_EXISTS("options","emailing_db_paths","artica_backup")){
				$sql="ALTER TABLE `emailing_db_paths` ADD `options` TINYTEXT NOT NULL";
				$this->QUERY_SQL($sql,'artica_backup');
		}

		if(!$this->TABLE_EXISTS('emailing_templates','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`emailing_templates` (
					`ID` INT( 2 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`zdate` DATETIME NOT NULL ,
					`template_name` VARCHAR( 255 ) NOT NULL ,
					`template_datas` longtext NOT NULL,
					`ou` VARCHAR( 255 ) NOT NULL ,
					`template_css` longtext NOT NULL,
					`subject` VARCHAR( 255 ) NOT NULL,
					 `advopts` MEDIUMTEXT NOT NULL,
					 KEY `ou` (`ou`)
				)";		
			$this->QUERY_SQL($sql,'artica_backup');
		}
		
	if(!$this->TABLE_EXISTS('emailing_tbl_files','artica_backup')){			
		$sql="CREATE TABLE emailing_tbl_files (
		     ID tinyint(3) unsigned NOT NULL auto_increment,
		     bin_data longblob NOT NULL,
		     template_id INT( 10 ) NOT NULL,
		     filename varchar(50) NOT NULL,
		     filesize varchar(50) NOT NULL,
		     filetype varchar(50) NOT NULL,
		     PRIMARY KEY (ID),
		     KEY `template_id` (`template_id`))";		
			$this->QUERY_SQL($sql,'artica_backup');	
		}

		
		
	if(!$this->TABLE_EXISTS('emailing_mailers','artica_backup')){			
			$sql="CREATE TABLE `artica_backup`.`emailing_mailers` (
					`ID` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`smtpserver` VARCHAR( 255 ) NOT NULL ,
					`parameters` LONGTEXT NOT NULL ,
					`ou` VARCHAR( 255 ) NOT NULL ,
					INDEX ( `ou` ))";
			$this->QUERY_SQL($sql,'artica_backup');	
		}
		
	if(!$this->TABLE_EXISTS('emailing_campain_linker','artica_backup')){			
			$sql="CREATE TABLE IF NOT EXISTS `emailing_campain_linker` (
				  `ID` int(5) NOT NULL AUTO_INCREMENT,
				  `template_id` int(5) NOT NULL,
				  `database_id` int(5) NOT NULL,
				  `parameters` longtext NOT NULL,
				  `finish_queue_processing` int(1) NOT NULL DEFAULT '0',
				  `queue_builder_pourc` INT( 1 ) NOT NULL DEFAULT '0',
				  `zdate` datetime NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `ou` varchar(255) NOT NULL,
				  PRIMARY KEY (`ID`),
				  KEY `template_id` (`template_id`,`database_id`,`finish_queue_processing`),
				  KEY `ou` (`ou`)
				) ";
		$this->QUERY_SQL($sql,'artica_backup');	
	}	
	
	
if(!$this->TABLE_EXISTS('emailing_campain_queues','artica_backup')){			
			$sql="CREATE TABLE `artica_backup`.`emailing_campain_queues` (
					`ID` 				INT( 10 ) NOT NULL AUTO_INCREMENT,
					`body` 				BLOB NOT NULL ,
					`from` 				VARCHAR( 255 ) NOT NULL ,
					`rcpt_to` 			VARCHAR( 255 ) NOT NULL ,
					`msmtp` 			BLOB NOT NULL ,
					`sended` 			INT( 1 ) NOT NULL DEFAULT '0',
					`msg_size` 			INT( 5 ) NOT NULL DEFAULT '0',
					`campain_linker_id` INT( 10 ) NOT NULL ,
					`ou` 		   		VARCHAR( 255 ) NOT NULL ,
					`time_created` 		DATETIME NOT NULL ,
					`time_sended`  		DATETIME NOT NULL ,
					`template_id`  		INT( 10 ) NOT NULL ,
					`contact_id`   		INT( 10 ) NOT NULL ,
					`unique_key`   		VARCHAR( 255 ) NOT NULL,
					 PRIMARY KEY (`ID`),
					 UNIQUE KEY `unique_key` (`unique_key`),
					 KEY `sended` (`sended`),
					 INDEX ( `rcpt_to` , `campain_linker_id` , `ou` , `template_id` , `contact_id` )
					)";	
		$this->QUERY_SQL($sql,'artica_backup');	
	}
		
}
	
	
	public function CheckTableEmailingContacts($tablename){
		if(!$this->TABLE_EXISTS($tablename,'artica_backup')){	
				$sql="CREATE TABLE `artica_backup`.`$tablename` (
				`ID` INT( 10 ) NOT NULL AUTO_INCREMENT ,
				`gender` VARCHAR( 10 ) NOT NULL ,
				`firstname` VARCHAR( 35 ) NOT NULL ,
				`lastname` VARCHAR( 255 ) NOT NULL ,
				`email` VARCHAR( 255 ) NOT NULL ,
				`domain` VARCHAR( 90 ) NOT NULL ,
				`phone` VARCHAR( 65 ) NOT NULL ,
				`city` VARCHAR( 35 ) NOT NULL ,
				`cp` VARCHAR( 10 ) NOT NULL ,
				`postaladdress` VARCHAR( 255 ) NOT NULL ,
				PRIMARY KEY ( `ID` ) ,
			    UNIQUE KEY `email` (`email`),
				KEY `lastname` (`lastname`),
				KEY `domain` (`domain`),
				KEY `city` (`city`),
				KEY `cp` (`cp`)
				);";
		$this->QUERY_SQL($sql,'artica_events');
	
		
	}
	
 	
	
	
	
	}
	
	
	private function CheckTableIMAP(){
		if(!$this->TABLE_EXISTS('mbx_con','artica_events')){
			$sql="CREATE TABLE IF NOT EXISTS `mbx_con` (
			  `ID` int(50) NOT NULL auto_increment,
			  `zDate` datetime NOT NULL,
			  `mbx_service` varchar(30) NOT NULL,
			  `client_name` varchar(90) NOT NULL,
			  `client_ip` varchar(20) NOT NULL,
			  `uid` varchar(50) NOT NULL,
			  `imap_server` varchar(255) NOT NULL,
			  PRIMARY KEY  (`ID`),
			  KEY `zDate` (`zDate`,`mbx_service`,`client_name`,`client_ip`,`uid`),
			  KEY `imap_server` (`imap_server`)
			)";	
			$this->QUERY_SQL($sql,'artica_events');
		}
		
		if(!$this->TABLE_EXISTS('fetchmail_events','artica_events')){
			$sql="CREATE TABLE `artica_events`.`fetchmail_events` (
				`ID` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`zDate` DATETIME NOT NULL ,
				`server` VARCHAR( 128 ) NOT NULL ,
				`account` VARCHAR( 128 ) NOT NULL ,
				`size` INT( 10 ) NOT NULL ,
				INDEX ( `zDate` , `server` , `account` )
				);";
			
			$this->QUERY_SQL($sql,'artica_events');
		}
		
			if(!$this->TABLE_EXISTS('fetchmail_rules','artica_backup')){
			$sql="CREATE TABLE IF NOT EXISTS `fetchmail_rules` (
				  `ID` int(10) NOT NULL AUTO_INCREMENT,
				  `uid` varchar(255) NOT NULL,
				  `is` varchar(255) NOT NULL,
				  `user` varchar(255) NOT NULL,
				  `pass` varchar(255) NOT NULL,
				  `poll` varchar(255) NOT NULL,
				  `proto` varchar(20) NOT NULL,
				  `keep` int(1) NOT NULL,
				  `enabled` int(1) NOT NULL,
				  `md5` varchar(120) NOT NULL,
				  `tracepolls` int(1) NOT NULL DEFAULT '0',
				  `fetchall` int(1) NOT NULL DEFAULT '0',
				  `ssl` int(1) NOT NULL DEFAULT '0',
				  `sslcertck` int(1) NOT NULL DEFAULT '0',
				  `port` int(5) NOT NULL,
				  `timeout` int(5) NOT NULL,
				  `interval` int(5) NOT NULL,
				  `aka` varchar(255) NOT NULL,
				  `nokeep` int(1) NOT NULL DEFAULT '0',
				  `multidrop` int(1) NOT NULL DEFAULT '0',
				  `smtp_host` VARCHAR( 75 ) NOT NULL DEFAULT '127.0.0.1',
				  `sslfingerprint` VARCHAR( 255 ) NOT NULL,
				  PRIMARY KEY (`ID`),
				  UNIQUE KEY `md5` (`md5`),
				  KEY `smtp_host` (`smtp_host`),
				  KEY `uid` (`uid`)
				) ";
			
			$this->QUERY_SQL($sql,'artica_backup');
		}

		if(!$this->FIELD_EXISTS("fetchmail_rules","nokeep","artica_backup")){
				$sql="ALTER TABLE `fetchmail_rules` ADD `nokeep` INT( 1 ) NOT NULL DEFAULT '0'";
				$this->QUERY_SQL($sql,'artica_backup');
		}
		
		if(!$this->FIELD_EXISTS("fetchmail_rules","sslcertck","artica_backup")){
				$sql="ALTER TABLE `fetchmail_rules` ADD `sslcertck` INT( 1 ) NOT NULL DEFAULT '0'";
				$this->QUERY_SQL($sql,'artica_backup');
		}		
		
		
		if(!$this->FIELD_EXISTS("fetchmail_rules","smtp_host","artica_backup")){
				$sql="ALTER TABLE `fetchmail_rules` ADD `smtp_host` VARCHAR( 75 ) NOT NULL,ADD INDEX ( `smtp_host` )";
				$this->QUERY_SQL($sql,'artica_backup');
		}
		
			if(!$this->FIELD_EXISTS("fetchmail_rules","sslfingerprint","artica_backup")){
				$sql="ALTER TABLE `fetchmail_rules` ADD `sslfingerprint` VARCHAR( 255 ) NOT NULL";
				$this->QUERY_SQL($sql,'artica_backup');
		}		

		
		
	if(!$this->TABLE_EXISTS('postfix_multi','artica_backup')){
		
			$sql="CREATE TABLE IF NOT EXISTS `postfix_multi` (
				  `ID` int(11) NOT NULL AUTO_INCREMENT,
				  `key` varchar(255) NOT NULL,
				  `value` varchar(255) NOT NULL,
				  `ou` varchar(255) NOT NULL,
				  `ValueTEXT` longtext NOT NULL,
				  `uuid` VARCHAR( 255 ) NOT NULL ,
				  `ip_address` VARCHAR( 16 ) NOT NULL,
				  PRIMARY KEY (`ID`),
				  UNIQUE KEY `ID` (`ID`),
				  KEY `key` (`key`),
				  KEY `value`(`value`),
				  KEY `ou` (`ou`),
				  KEY `uuid` (`uuid`),
				  KEY `ip_address` (`ip_address`)
				  )";	
			$this->QUERY_SQL($sql,'artica_backup');
		}
		
		if(!$this->FIELD_EXISTS("postfix_multi","uuid","artica_backup")){
			$sql="ALTER TABLE `postfix_multi` ADD `uuid` VARCHAR( 255 ) NOT NULL ,
			ADD `ip_address` VARCHAR( 16 ) NOT NULL ,
			ADD INDEX ( `uuid` , `ip_address` )";
			$this->QUERY_SQL($sql,'artica_backup');
		}

		
		if(!$this->TABLE_EXISTS('nics_virtuals','artica_backup')){	
			$sql="CREATE TABLE IF NOT EXISTS `nics_virtuals` (
				  `nic` varchar(20) NOT NULL,
				  `ID` int(11) NOT NULL AUTO_INCREMENT,
				  `org` varchar(255) NOT NULL,
				  `ipaddr` varchar(25) NOT NULL,
				  `netmask` varchar(25) NOT NULL,
				  `cdir` varchar(30) NOT NULL,
				  `gateway` varchar(30) NOT NULL,
				  `openvpn_nic` INT( 1 ) NOT NULL,
				  PRIMARY KEY (`ID`),
				  KEY `nic` (`nic`),
				  KEY `org` (`org`,`ipaddr`,`netmask`),
				  KEY `cdir` (`cdir`)
				)";
			$this->QUERY_SQL($sql,'artica_backup');
			}
			
			$this->QUERY_SQL("ALTER TABLE `nics_virtuals` DROP INDEX `cdir_2`",'artica_backup'); 
			
			if(!$this->FIELD_EXISTS("nics_virtuals","gateway","artica_backup")){
				$sql="ALTER TABLE `nics_virtuals` ADD `gateway` VARCHAR( 30 ) NOT NULL";
				$this->QUERY_SQL($sql,'artica_backup');
			}	
			
			if(!$this->FIELD_EXISTS("nics_virtuals","openvpn_nic","artica_backup")){
				$sql="ALTER TABLE `nics_virtuals` ADD `openvpn_nic` INT( 1 ) NOT NULL ";
				$this->QUERY_SQL($sql,'artica_backup');
			}				

			if(!$this->INDEX_EXISTS("nics_virtuals","cdir_2","artica_backup")){
				$sql="ALTER TABLE `nics_virtuals` DROP INDEX `cdir_2` ";
				$this->QUERY_SQL($sql,'artica_backup');
			}			
			
			
			
			
	}

	private function CheckTableEvents(){
	
		if(!$this->TABLE_EXISTS('events','artica_events')){	
			$sql="CREATE TABLE IF NOT EXISTS `events` (
			  	  `ID` int(11) NOT NULL AUTO_INCREMENT,
				  `zDate` datetime NOT NULL,
				  `event_id` int(11) NOT NULL,
				  `event_type` int(11) NOT NULL,
				  `hostname` varchar(75) NOT NULL,
				  `process` varchar(75) NOT NULL,
				  `text` varchar(255) NOT NULL,
				  `context` varchar(75) NOT NULL DEFAULT 'unknown',
				  `sended` int(1) NOT NULL DEFAULT '0',
				  `content` text NOT NULL,
				  `attached_files` TEXT NOT NULL,
				  `recipient` VARCHAR( 255 ) NULL,
				  PRIMARY KEY (`ID`),
				  KEY `zDate` (`zDate`,`event_id`,`event_type`,`process`,`hostname`),
				  KEY `context` (`context`),
				  KEY `sended` (`sended`))";
			$this->QUERY_SQL($sql,'artica_events');
		}
		
		if(!$this->FIELD_EXISTS("events","context","artica_events")){
				$sql="ALTER TABLE `events` ADD `context` VARCHAR( 75 ) NOT NULL DEFAULT 'unknown',ADD INDEX ( context )";
				$this->QUERY_SQL($sql,'artica_events');
		}
		
		if(!$this->FIELD_EXISTS("events","recipient","artica_events")){
				$sql="ALTER TABLE `events` ADD `recipient` VARCHAR( 255 ) NULL DEFAULT";
				$this->QUERY_SQL($sql,'artica_events');
		}		
		
		
		
		
		
		
		if(!$this->FIELD_EXISTS("events","sended","artica_events")){
				$sql="ALTER TABLE `events` ADD `sended` INT( 1 ) NOT NULL DEFAULT '0',ADD INDEX ( sended )";
				$this->QUERY_SQL($sql,'artica_events');
			}
			
			if(!$this->FIELD_EXISTS("events","attached_files","artica_events")){
				$sql="ALTER TABLE `events` ADD `attached_files` TEXT NOT NULL ";
				$this->QUERY_SQL($sql,'artica_events');
			}

			if(!$this->FIELD_EXISTS("events","sended","artica_events")){
				$sql="ALTER TABLE `events` ADD `sended` INT( 1 ) NOT NULL DEFAULT '0',ADD INDEX ( sended )";
				$this->QUERY_SQL($sql,'artica_events');
			}	
		if(!$this->TABLE_EXISTS('cyrus_backup_events','artica_events')){	
			$sql="CREATE TABLE `artica_events`.`cyrus_backup_events` (
					`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`zDate` DATETIME NOT NULL ,
					`local_ressource` VARCHAR( 50 ) NOT NULL ,
					`events` TINYTEXT NOT NULL ,
					`success` INT( 1 ) NOT NULL DEFAULT '0',
					INDEX ( `local_ressource` , `success` )
					) ";
			 
			$this->QUERY_SQL($sql,'artica_events');
			}
	
	if(!$this->FIELD_EXISTS("cyrus_backup_events","remote_ressource","artica_events")){
		$sql="ALTER TABLE `cyrus_backup_events` ADD `remote_ressource` VARCHAR( 125 ) NOT NULL ,ADD INDEX ( remote_ressource )";
		$this->QUERY_SQL($sql,'artica_events');
	}
	
	if(!$this->TABLE_EXISTS('backup_events','artica_events')){	
			$sql="CREATE TABLE `artica_events`.`backup_events` (
					`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`task_id` INT( 10 ) NOT NULL ,
					`zdate` DATETIME NOT NULL ,
					`backup_source` VARCHAR( 255 ) NOT NULL ,
					`event` MEDIUMTEXT NOT NULL ,
					INDEX ( `task_id` , `zdate` , `backup_source` ))";
			 
			$this->QUERY_SQL($sql,'artica_events');
			}	
	}
	
	private function CheckOutOfOfficeTable(){
		$sql="CREATE TABLE `artica_events`.`OutOfOffice` (
			`zMD5` VARCHAR( 100 ) NOT NULL ,
			`uid` VARCHAR( 90 ) NOT NULL ,
			`zDate` DATETIME NOT NULL ,
			`mailfrom` VARCHAR( 90 ) NOT NULL ,
			PRIMARY KEY ( `zMD5` ) ,
			INDEX ( `uid` , `mailfrom` )
			) ";
		
		if(!$this->TABLE_EXISTS('OutOfOffice','artica_events')){$this->QUERY_SQL($sql,'artica_events');}	
	}
	
	
	
	function BuildTables(){
		if(!$this->DATABASE_EXISTS('artica_events')){if(!$this->CREATE_DATABASE('artica_events')){return null;}}
		if(!$this->DATABASE_EXISTS('artica_backup')){if(!$this->CREATE_DATABASE('artica_backup')){return null;}}		
		
		 $this->Check_quarantine_table();
		 $this->Check_iptables_table();
		 $this->check_storage_table();
		 $this->CheckTables_rsync();
		 $this->CheckTables_dar();
		 $this->CheckTableIMAP();
		 $this->CheckTableVirus();
		 $this->CheckTableEvents();	
		 $this->CheckTable_dansguardian();	
		 $this->CheckTables_repositories();
		 $this->CheckTables_openvpn();
		 $this->CheckOutOfOfficeTable();
		 $this->checkAgentTables();
		 $this->CheckTablesSquid();
		 $this->CheckTablesPostfilter();
		 $this->check_emailing_tables();
		 $this->Check_reports_table();
	
	
		if(!$this->FIELD_EXISTS("smtp_logs_day","emails","artica_events")){
			$sql="ALTER TABLE `smtp_logs_day` ADD `emails` int(10) NOT NULL ";
			$this->QUERY_SQL($sql,'artica_events');
		}	
	
	if(!$this->TABLE_EXISTS('smtp_logs_day','artica_events')){		
			$sql="CREATE TABLE `artica_events`.`smtp_logs_day` (
				`key` VARCHAR( 75 ) NOT NULL ,
				`day` DATE NOT NULL ,
				`delivery_domain` VARCHAR( 80 ) NOT NULL ,
				`bounce_error` VARCHAR( 80 ) NOT NULL ,
				`emails` int(10) NOT NULL,
				PRIMARY KEY ( `key` ) ,
				INDEX ( `day` , `delivery_domain` , `bounce_error` )
				)";
						
				 $this->QUERY_SQL($sql,'artica_events');
		}	
	
	
		//ALTER TABLE `quarantine` CHANGE `file_path` `file_path` VARCHAR( 255 ) NOT NULL 
		
		if(!$this->TABLE_EXISTS('spam_events','artica_events')){
			
			$sql="CREATE TABLE IF NOT EXISTS `spam_events` (
					  `msgid` varchar(90) NOT NULL,
					  `zDate` datetime NOT NULL,
					  `mailfrom` varchar(255) NOT NULL,
					  `rcpt_to` varchar(255) NOT NULL,
					  `filter` int(11) NOT NULL,
					  `ipfrom` varchar(25) NOT NULL,
					  `mailfrom_domain` varchar(255) NOT NULL,
					  `rcpt_to_domain` varchar(255) NOT NULL,
					  `spam_level` varchar(50) NOT NULL,
					  PRIMARY KEY  (`msgid`),
					  KEY `zDate` (`zDate`,`mailfrom`,`rcpt_to`,`filter`),
					  KEY `ipfrom` (`ipfrom`),
					  KEY `mailfrom_domain` (`mailfrom_domain`,`rcpt_to_domain`),
					  KEY `spam_level` (`spam_level`)
					)";
						
				 $this->QUERY_SQL($sql,'artica_events');
		}	
		
			
		if(!$this->DATABASE_EXISTS('artica_backup')){if(!$this->CREATE_DATABASE('artica_backup')){return null;}}		

		if(!$this->TABLE_EXISTS('imapsync','artica_backup')){
			if($GLOBALS["DEBUG"]){writelogs("Table imapsync does not exists....",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
			$sql="CREATE TABLE IF NOT EXISTS `imapsync` (
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `uid` varchar(255) NOT NULL,
			  `imap_server` varchar(255) NOT NULL,
			  `username` varchar(255) NOT NULL,
			  `password` varchar(255) NOT NULL,
			  `ssl` int(1) NOT NULL DEFAULT '0',
			  `enabled` int(1) NOT NULL,
			  `state` int(1) NOT NULL,
			  `state_event` longtext NOT NULL,
			  `pid` int(5) NOT NULL,
			  `zDate` datetime NOT NULL,
			  `CronSchedule` varchar(90) NOT NULL,
			  `parameters` text NOT NULL,
			  `folders` text NOT NULL,
			  PRIMARY KEY (`ID`),
			  KEY `uid` (`uid`),
			  KEY `imap_server` (`imap_server`)
			)";
			$this->QUERY_SQL($sql,'artica_backup');
			if($GLOBALS["DEBUG"]){writelogs("$sql",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
			if(!$this->ok){echo "$sql\n$this->mysql_error\n";}else{writelogs("SUCCESS!",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
		}else{
			if($GLOBALS["DEBUG"]){writelogs("Table imapsync already exists....",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}	
		}
		
		
	if(!$this->FIELD_EXISTS("imapsync","parameters","artica_backup")){
		$sql="ALTER TABLE `imapsync` ADD `parameters` text NOT NULL";
		$this->QUERY_SQL($sql,"artica_backup");
	}		
		
		if(!$this->FIELD_EXISTS("imapsync","folders","artica_backup")){
		$sql="ALTER TABLE `imapsync` ADD `folders` text NOT NULL";
		$this->QUERY_SQL($sql,"artica_backup");
	}
		
		if(!$this->TABLE_EXISTS('dspam_stats','artica_events')){	
		$sql="create table dspam_stats (
				  uid smallint unsigned primary key,
				  spam_learned int not null,
				  innocent_learned int not null,
				  spam_misclassified int not null,
				  innocent_misclassified int not null,
				  spam_corpusfed int not null,
				  innocent_corpusfed int not null,
				  spam_classified int not null,
				  innocent_classified int not null
				) type=MyISAM;";
		
		$this->QUERY_SQL($sql,'artica_events');
		}
		
		if(!$this->TABLE_EXISTS('dspam_token_data','artica_events')){	
		$sql="create table dspam_token_data (
			  uid smallint unsigned not null,
			  token bigint unsigned not null,
			  spam_hits int not null,
			  innocent_hits int not null,
			  last_hit date not null
			) type=MyISAM PACK_KEYS=1;";
		$this->QUERY_SQL($sql,'artica_events');
		}
		
		if(!$this->TABLE_EXISTS('dspam_signature_data','artica_events')){	
		$sql="create table dspam_signature_data (
			  uid smallint unsigned not null,
			  signature char(32) not null,
			  data blob not null,
			  length smallint not null,
			  created_on date not null
			) type=MyISAM max_rows=2500000 avg_row_length=8096;";	
		$this->QUERY_SQL($sql,'artica_events');
		}	
		
		
		if(!$this->TABLE_EXISTS('smtp_logs','artica_events')){	
				$sql="CREATE TABLE IF NOT EXISTS `smtp_logs` (
				  `id` int(11) NOT NULL auto_increment,
				  `newmsg_id` bigint(20) default NULL,
				  `bounce_qp` int(11) default NULL,
				  `bytes` int(11) NOT NULL default '0',
				  `sender_user` varchar(128) NOT NULL default '',
				  `sender_domain` varchar(128) NOT NULL default '',
				  `delivery_id` bigint(20) default NULL,
				  `delivery_user` varchar(128) NOT NULL default '',
				  `delivery_domain` varchar(128) NOT NULL default '',
				  `delivery_success` enum('yes','no') NOT NULL default 'no',
				  `time_stamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
				  `delivery_id_text` varchar(128) NOT NULL,
				  `msg_id_text` varchar(128) NOT NULL,
				  `time_connect` datetime NOT NULL,
				  `time_sended` datetime NOT NULL,
				  `bounce_error` varchar(255) NOT NULL,
				  `SPAM` int(11) NOT NULL,
				  `filter_reject` varchar(255) NOT NULL,
				  `time_amavis` datetime NOT NULL,
				  `Country` varchar(25) NOT NULL,
				  `Region` varchar(30) default NULL,
				  `kas` int(3) NOT NULL default '0',
				  `infected` int(1) NOT NULL default '0',
				  `spammy` int(1) NOT NULL default '0',
				  `blacklisted` int(1) NOT NULL default '0',
				  `whitelisted` int(1) NOT NULL default '0',
				  `smtp_sender` varchar(55) default NULL,
				  PRIMARY KEY  (`id`),
				  UNIQUE KEY `bounce_qp` (`bounce_qp`),
				  UNIQUE KEY `newmsg_id` (`newmsg_id`),
				  KEY `sender_domain` (`sender_domain`),
				  KEY `delivery_domain` (`delivery_domain`),
				  KEY `delivery_id_text` (`delivery_id_text`),
				  KEY `msg_id_text` (`msg_id_text`),
				  KEY `time_connect` (`time_connect`),
				  KEY `time_sended` (`time_sended`),
				  KEY `SPAM` (`SPAM`),
				  KEY `filter_reject` (`filter_reject`),
				  KEY `time_amavis` (`time_amavis`),
				  KEY `Country` (`Country`),
				  KEY `Region` (`Region`),
				  KEY `kas` (`kas`),
				  KEY `infected` (`infected`),
				  KEY `spammy` (`spammy`),
				  KEY `blacklisted` (`blacklisted`),
				  KEY `whitelisted` (`whitelisted`),
				  KEY `smtp_sender` (`smtp_sender`)
				) ";	
				$this->QUERY_SQL($sql,'artica_events');
		}else{
			$this->Check_smtp_logs_table();		
		}

		if(!$this->TABLE_EXISTS('dspam_preferences','artica_events')){	
		$sql="
			CREATE TABLE IF NOT EXISTS `dspam_preferences` (
			  `uid` smallint(5) unsigned NOT NULL,
			  `preference` varchar(32) NOT NULL,
			  `value` varchar(64) NOT NULL,
			  UNIQUE KEY `id_preferences_01` (`uid`,`preference`)
			)";
		$this->QUERY_SQL($sql,'artica_events');
		}
		
		if(!$this->TABLE_EXISTS('dspam_virtual_uids','artica_events')){	
		$sql="create table dspam_virtual_uids (
			  uid smallint unsigned primary key AUTO_INCREMENT,
			  username varchar(128)
			)";
		$this->QUERY_SQL($sql,'artica_events');
		}		
				
		

	
	if(!$this->TABLE_EXISTS('storage','artica_backup')){		
	$sql="CREATE TABLE IF NOT EXISTS `storage` (
			  `MessageID` varchar(80) NOT NULL,
			  `zDate` datetime NOT NULL,
			  `mailfrom` varchar(255) NOT NULL,
			  `subject` varchar(255) NOT NULL,
			  `MessageBody` text NOT NULL,
			  `organization` varchar(80) NOT NULL,
			  `uid` varchar(50) NOT NULL,
			  `file_path` varchar(255) default NULL,
			  `original_messageid` varchar(80) NOT NULL,
			  `message_size` int(10) NOT NULL,
			  `mailto` varchar(255) NOT NULL,
			  `mailfrom_domain` varchar(255) NOT NULL,
			  `commandline` varchar(200) NOT NULL,
			  `ExecuteAfter` varchar(200) NOT NULL,
			  `BinMessg` longblob NOT NULL,
			  `filename` varchar(128) NOT NULL,
			  `filesize` int(10) NOT NULL,
			  PRIMARY KEY  (`MessageID`),
			  KEY `MessageID` (`MessageID`),
			  KEY `mailfrom` (`mailfrom`),
			  KEY `organization` (`organization`),
			  KEY `uid` (`uid`),
			  KEY `file_path` (`file_path`),
			  KEY `original_messageid` (`original_messageid`),
			  KEY `mailto` (`mailto`),
			  KEY `mailfrom_domain` (`mailfrom_domain`),
			  KEY `filename` (`filename`),
			  FULLTEXT KEY `MessageBody` (`MessageBody`)
			) ";
		$this->QUERY_SQL($sql,'artica_backup');
	}
	
	
	
	if(!$this->TABLE_EXISTS('storage_recipients','artica_backup')){	
			$sql="CREATE TABLE IF NOT EXISTS `storage_recipients` (
				  `id` int(10) NOT NULL auto_increment,
				  `MessageID` varchar(80) NOT NULL,
				  `recipient` varchar(255) NOT NULL,
				  `recipient_domain` varchar(255) NOT NULL,
				  	PRIMARY KEY  (`id`),
				  	KEY `MessageID` (`MessageID`),
				  	KEY `recipient` (`recipient`),
				  	KEY `recipient_domain` (`recipient_domain`)
				);";
	
			$this->QUERY_SQL($sql,'artica_backup');
	}
	
	
	
	
	
	if(!$this->TABLE_EXISTS('orgmails','artica_backup')){	
			$sql="CREATE TABLE IF NOT EXISTS `orgmails` (
			  `MessageID` varchar(255) NOT NULL,
			  `message_path` varchar(255) NOT NULL,
			  `MessageSize` int(20) NOT NULL,
			  PRIMARY KEY  (`MessageID`),
			  UNIQUE KEY `message_path` (`message_path`)
			) ;";
	
			$this->QUERY_SQL($sql,'artica_backup');
	}	
	
	if(!$this->TABLE_EXISTS('artica_whitelist','artica_backup')){	
			$sql=" CREATE TABLE `artica_backup`.`artica_whitelist` (
						`zMD5` VARCHAR( 90 ) NOT NULL ,
						`mailfrom` VARCHAR( 255 ) NOT NULL ,
						`mailto` VARCHAR( 255 ) NOT NULL ,
						`zDate` DATETIME NOT NULL ,
						PRIMARY KEY ( `zMD5` ) ,
						INDEX ( `mailfrom` , `mailto` , `zDate` ))";
			$this->QUERY_SQL($sql,'artica_backup');
	
 			}
 			
 			
	if(!$this->TABLE_EXISTS('notify','artica_events')){	
			$sql="CREATE TABLE IF NOT EXISTS `notify` (
				  `daemon` varchar(20) NOT NULL,
				  `event_text` varchar(255) NOT NULL,
				  `error_id` int(2) NOT NULL,
				  `zMD5` varchar(80) NOT NULL,
				  PRIMARY KEY  (`zMD5`),
				  KEY `daemon` (`daemon`,`error_id`)
				); ";
			$this->QUERY_SQL($sql,'artica_events');
			}
			
	if(!$this->TABLE_EXISTS('audit2','artica_events')){			
		$sql="CREATE TABLE IF NOT EXISTS `audit2` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `log_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
			  `username` varchar(18) default '',
			  `useraddr` varchar(18) default '',
			  `op_name` varchar(255) default '',
			  `err_msg` text,
			  `op_msg` text,
			  `share` varchar(255) default '',
			  `cnt` smallint(6) default '1',
			  PRIMARY KEY  (`id`)
			)";
		$this->QUERY_SQL($sql,'artica_events');
		}
		
		

		
		
		
if(!$this->TABLE_EXISTS('sys_events','artica_events')){			
		$sql="CREATE TABLE IF NOT EXISTS `sys_events` (
				  `md5` varchar(90) NOT NULL,
				  `ID` int(20) NOT NULL auto_increment,
				  `zDate` datetime NOT NULL,
				  `type` int(1) NOT NULL,
				  `event_text` varchar(255) NOT NULL,
				  `daemon` varchar(20) NOT NULL,
				  `msg_id` varchar(20) default NULL,
				  PRIMARY KEY  (`md5`,`ID`),
				  KEY `type` (`type`,`daemon`),
				  KEY `msg_id` (`msg_id`)
				);";
		$this->QUERY_SQL($sql,'artica_events');
		}				
		
	if(!$this->TABLE_EXISTS('what2','artica_events')){			
		$sql="CREATE TABLE what2 (
			  what varchar(50) NOT NULL,
			  visible tinyint(1) default '0',
			  description varchar(255) default '',
			  PRIMARY KEY  (what)
			);";
		
		$this->QUERY_SQL($sql,'artica_events');
		
		$sql="INSERT INTO `what2` (`what`, `visible`, `description`) VALUES ('connect', 1, ''),
				('disconnect', 1, ''),
				('disk_free', 0, ''),
				('get_quota', 0, ''),
				('set_quota', 0, ''),
				('get_shadow_copy_data', 0, ''),
				('statvfs', 0, ''),
				('opendir', 1, ''),
				('readdir', 0, ''),
				('seekdir', 0, ''),
				('telldir', 0, ''),
				('rewinddir', 0, ''),
				('mkdir', 1, ''),
				('rmdir', 1, ''),
				('closedir', 1, ''),
				('open', 1, ''),
				('close', 1, ''),
				('read', 1, ''),
				('pread', 0, ''),
				('write', 1, ''),
				('pwrite', 0, ''),
				('lseek', 0, ''),
				('rename', 1, ''),
				('fsync', 0, ''),
				('stat', 0, ''),
				('fstat', 0, ''),
				('lstat', 0, ''),
				('unlink', 1, ''),
				('chmod', 1, ''),
				('fchmod', 1, ''),
				('chown', 0, ''),
				('fchown', 0, ''),
				('chdir', 1, ''),
				('getwd', 0, ''),
				('utime', 1, ''),
				('ftruncate', 0, ''),
				('lock', 0, ''),
				('symlink', 0, ''),
				('readlink', 0, ''),
				('link', 0, ''),
				('mknod', 0, ''),
				('realpath', 0, ''),
				('fget_nt_acl', 0, ''),
				('get_nt_acl', 0, ''),
				('fset_nt_acl', 0, ''),
				('set_nt_acl', 0, ''),
				('chmod_acl', 1, ''),
				('fchmod_acl', 1, ''),
				('sys_acl_get_entry', 0, ''),
				('sys_acl_get_tag_type', 0, ''),
				('sys_acl_get_permset', 0, ''),
				('sys_acl_get_qualifier', 0, ''),
				('sys_acl_get_file', 0, ''),
				('sys_acl_get_fd', 0, ''),
				('sys_acl_clear_perms', 0, ''),
				('sys_acl_add_perm', 0, ''),
				('sys_acl_to_text', 0, ''),
				('sys_acl_init', 0, ''),
				('sys_acl_create_entry', 0, ''),
				('sys_acl_set_tag_type', 0, ''),
				('sys_acl_set_qualifier', 0, ''),
				('sys_acl_set_permset', 0, ''),
				('sys_acl_valid', 0, ''),
				('sys_acl_set_file', 0, ''),
				('sys_acl_set_fd', 0, ''),
				('sys_acl_delete_def_file', 0, ''),
				('sys_acl_get_perm', 0, ''),
				('sys_acl_free_text', 0, ''),
				('sys_acl_free_acl', 0, ''),
				('sys_acl_free_qualifier', 0, ''),
				('getxattr', 0, ''),
				('lgetxattr', 0, ''),
				('fgetxattr', 0, ''),
				('listxattr', 0, ''),
				('llistxattr', 0, ''),
				('flistxattr', 0, ''),
				('removexattr', 0, ''),
				('lremovexattr', 0, ''),
				('fremovexattr', 0, ''),
				('setxattr', 0, ''),
				('lsetxattr', 0, ''),
				('fsetxattr', 0, ''),
				('aio_read', 0, ''),
				('aio_write', 0, ''),
				('aio_return', 0, ''),
				('aio_cancel', 0, ''),
				('aio_error', 0, ''),
				('aio_fsync', 0, ''),
				('aio_suspend', 0, '');";
		$this->QUERY_SQL($sql,'artica_events');
		
	}
	
	
	if(!$this->TABLE_EXISTS('shares','artica_events')){			
		$sql="CREATE TABLE `shares` (
			  `id` int(11) NOT NULL auto_increment,
			  `share` varchar(255) NOT NULL,
			  UNIQUE KEY `id` (`id`)
			) AUTO_INCREMENT=1 ;";
		$this->QUERY_SQL($sql,'artica_events');
	}
	
	if(!$this->TABLE_EXISTS('dansguardian_sites','artica_events')){
		$sql="CREATE TABLE `dansguardian_sites` (
				`website_md5` VARCHAR( 90 ) NOT NULL ,
				`website` VARCHAR( 255 ) NOT NULL ,
				UNIQUE (`website_md5`)
				)";
		
		$this->QUERY_SQL($sql,'artica_events');
		
	}
	
	



		if(!$this->TABLE_EXISTS('dansguardian_uris','artica_events')){
				$sql="CREATE TABLE IF NOT EXISTS `dansguardian_uris` (
			  `uri_MD5` varchar(90) NOT NULL,
			  `uri` varchar(255) NOT NULL,
			  `site_md5` varchar(90) NOT NULL,
			  UNIQUE KEY `uri_MD5` (`uri_MD5`),
			  KEY `site_md5` (`site_md5`)
			) ";
			$this->QUERY_SQL($sql,'artica_events');
		}

		
	
	if(!$this->TABLE_EXISTS("mails_events","artica_events")){
		$sql="CREATE TABLE IF NOT EXISTS `mails_events` (
			  `ID` int(20) NOT NULL auto_increment,
			  `mailfrom` varchar(255) NOT NULL,
			  `rcpt_to` varchar(255) NOT NULL,
			  `zDate` datetime NOT NULL,
			  `mailfrom_domain` varchar(255) NOT NULL,
			  `rcpt_to_domain` varchar(255) default NULL,
			  `relayhost` varchar(20) NOT NULL,
			  `Country` varchar(75) NOT NULL,
			  `Region` varchar(55) NOT NULL,
			  PRIMARY KEY  (`ID`),
			  KEY `rcpt_to_domain` (`rcpt_to_domain`),
			  KEY `rcpt_to` (`rcpt_to`),
			  KEY `zDate` (`zDate`),
			  KEY `mailfrom_domain` (`mailfrom_domain`),
			  KEY `relayhost` (`relayhost`),
			  KEY `Country` (`Country`),
			  KEY `Region` (`Region`)
				) AUTO_INCREMENT=1 ;";
		$this->QUERY_SQL($sql,'artica_events');
	}
	
	if(!$this->FIELD_EXISTS("mails_events","mailto","artica_events")){	
		$sql="ALTER TABLE `mails_events` ADD `mailto` VARCHAR( 255 ) NOT NULL ,ADD INDEX ( mailto )";	
		$this->QUERY_SQL($sql,'artica_events');
	}
	if(!$this->FIELD_EXISTS("mails_events","Country","artica_events")){
		$sql="ALTER TABLE `mails_events` ADD `Country` VARCHAR( 75 ) NOT NULL ,ADD INDEX ( Country )";
		$this->QUERY_SQL($sql,'artica_events');
	}
	
	if(!$this->FIELD_EXISTS("mails_events","Region","artica_events")){
		$sql="ALTER TABLE `mails_events` ADD `Region` VARCHAR( 55 ) NOT NULL ,ADD INDEX ( Region )";
		$this->QUERY_SQL($sql,'artica_events');
	}	
	
	if(!$this->FIELD_EXISTS("mails_events","City","artica_events")){
		$sql="ALTER TABLE `mails_events` ADD `City` VARCHAR( 75 ) NOT NULL ,ADD INDEX ( City )";
		$this->QUERY_SQL($sql,'artica_events');
	}	
	
	
	if(!$this->FIELD_EXISTS("mails_events","message_id","artica_events")){
		$sql="ALTER TABLE `mails_events` ADD `message_id` VARCHAR(120) NOT NULL ,ADD INDEX (message_id)";
		$this->QUERY_SQL($sql,'artica_events');
	}

	if(!$this->FIELD_EXISTS("mails_events","subject","artica_events")){
		$sql="ALTER TABLE `mails_events` ADD `subject` VARCHAR(120) NOT NULL ,ADD INDEX (subject)";
		$this->QUERY_SQL($sql,'artica_events');
	}		
	
	if(!$this->FIELD_EXISTS("mails_events","infected","artica_events")){
		$sql="ALTER TABLE `mails_events` ADD `infected` INT(1) NOT NULL ,ADD INDEX ( infected )";
		$this->QUERY_SQL($sql,'artica_events');
	}		
	
	if(!$this->FIELD_EXISTS("mails_events","spammy","artica_events")){
		$sql="ALTER TABLE `mails_events` ADD `spammy` INT(1) NOT NULL ,ADD INDEX ( spammy )";
		$this->QUERY_SQL($sql,'artica_events');
	}

	if(!$this->FIELD_EXISTS("mails_events","spam","artica_events")){
		$sql="ALTER TABLE `mails_events` ADD `spam` INT(1) NOT NULL ,ADD INDEX ( spam )";
		$this->QUERY_SQL($sql,'artica_events');
	}		

	if(!$this->FIELD_EXISTS("mails_events","banned","artica_events")){
		$sql="ALTER TABLE `mails_events` ADD `banned` INT(1) NOT NULL ,ADD INDEX (banned)";
		$this->QUERY_SQL($sql,'artica_events');
	}	

	
	
	
	}
	
public function check_storage_table(){
		
		if(!$this->TABLE_EXISTS('storage','artica_backup')){		
			$sql="CREATE TABLE IF NOT EXISTS `storage` (
				  `MessageID` varchar(80) NOT NULL,
				  `zDate` datetime NOT NULL,
				  `mailfrom` varchar(255) NOT NULL,
				  `subject` varchar(255) NOT NULL,
				  `MessageBody` text NOT NULL,
				  `organization` varchar(80) NOT NULL,
				  `uid` varchar(50) NOT NULL,
				  `file_path` varchar(255) DEFAULT NULL,
				  `original_messageid` varchar(80) NOT NULL,
				  `message_size` int(10) NOT NULL,
				  `mailfrom_domain` varchar(128) NOT NULL,
				  `mailto` varchar(255) NOT NULL,
				  `BinMessg` longblob NOT NULL,
				  `filename` varchar(128) NOT NULL,
				  `filesize` int(10) NOT NULL,
				  PRIMARY KEY (`MessageID`),
				  KEY `mailfrom` (`mailfrom`),
				  KEY `organization` (`organization`),
				  KEY `uid` (`uid`),
				  KEY `original_messageid` (`original_messageid`),
				  KEY `mailfrom_domain` (`mailfrom_domain`),
				  KEY `mailto` (`mailto`),
				  KEY `filename` (`filename`),
				  FULLTEXT KEY `MessageBody` (`MessageBody`)
				) ;";
				
				$this->QUERY_SQL($sql,'artica_backup');
				}
				
		if(!$this->FIELD_EXISTS("storage","BinMessg","artica_backup")){
			$sql="ALTER TABLE `storage` ADD `BinMessg` LONGBLOB NOT NULL ";
			$this->QUERY_SQL($sql,"artica_backup");
		}

		if(!$this->FIELD_EXISTS("storage","filename","artica_backup")){
			$sql="ALTER TABLE `storage` ADD `filename` VARCHAR( 128 ) NOT NULL ,ADD INDEX ( filename )";
			$this->QUERY_SQL($sql,"artica_backup");
		}	
		
		if(!$this->FIELD_EXISTS("storage","filesize","artica_backup")){
			$sql="ALTER TABLE `storage` ADD `filesize` INT( 10 ) NOT NULL";
			$this->QUERY_SQL($sql,"artica_backup");
		}			

		if(!$this->TABLE_EXISTS('backup_schedules','artica_backup')){				 
				$sql="CREATE TABLE IF NOT EXISTS `backup_schedules` (
				  `ID` int(5) NOT NULL AUTO_INCREMENT,
				  `zMD5` varchar(90) NOT NULL,
				  `resource_type` varchar(10) NOT NULL,
				  `pattern` varchar(255) NOT NULL,
				  `schedule` varchar(90) NOT NULL,
				  `datasbackup` text NOT NULL,
				  `container` varchar(20) NOT NULL,
				  `pid` INT( 5 ) NOT NULL,
				  PRIMARY KEY (`ID`),
				  UNIQUE KEY `zMD5` (`zMD5`),
				  KEY `resource_type` (`resource_type`,`pattern`),
				  KEY `container` (`container`)) ";
				$this->QUERY_SQL($sql,'artica_backup');
		}
		
		if(!$this->FIELD_EXISTS("backup_schedules","pid","artica_backup")){
			$sql="ALTER TABLE `backup_schedules` ADD `pid` INT( 5 ) NOT NULL";
			$this->QUERY_SQL($sql,"artica_backup");
		}			
		
		


		if(!$this->TABLE_EXISTS('files_storage','artica_backup')){		
			$sql="CREATE TABLE IF NOT EXISTS `files_storage` (
				  `id_files` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
				  `bin_data` longblob NOT NULL,
				  `description` tinytext NOT NULL,
				  `filename` varchar(50) NOT NULL,
				  `filesize` varchar(50) NOT NULL,
				  `filetype` varchar(50) NOT NULL,
				  PRIMARY KEY (`id_files`)
					)";
			$this->QUERY_SQL($sql,'artica_backup');
		}
	
		if(!$this->FIELD_EXISTS("files_storage","commandline","artica_backup")){
			$sql="ALTER TABLE `files_storage` ADD `commandline` VARCHAR( 200 ) NOT NULL";
			$this->QUERY_SQL($sql,"artica_backup");
		}

		if(!$this->FIELD_EXISTS("files_storage","ExecuteAfter","artica_backup")){
			$sql="ALTER TABLE `files_storage` ADD `ExecuteAfter` VARCHAR( 200 ) NOT NULL";
			$this->QUERY_SQL($sql,"artica_backup");
		}

		if(!$this->FIELD_EXISTS("files_storage","OCS_PACKAGE","artica_backup")){
			$sql="ALTER TABLE `files_storage` ADD `OCS_PACKAGE` INT( 1 ) NOT NULL DEFAULT '0',ADD INDEX ( OCS_PACKAGE ) ";
			$this->QUERY_SQL($sql,"artica_backup");
		}	

	if(!$this->TABLE_EXISTS('deploy_tasks','artica_backup')){	
		$sql="CREATE TABLE IF NOT EXISTS `deploy_tasks` (
			  `ID` int(5) NOT NULL AUTO_INCREMENT,
			  `files_id` int(5) NOT NULL,
			  `computer_id` varchar(90) NOT NULL,
			  `results` text NOT NULL,
			  `executed` int(1) NOT NULL DEFAULT '0',
			  `commandline` varchar(255) NOT NULL,
			  `username` varchar(128) NOT NULL,
			  `password` varchar(128) NOT NULL,
			  PRIMARY KEY (`ID`),
			  KEY `files_id` (`files_id`,`computer_id`),
			  KEY `computer_id` (`computer_id`),
			  KEY `executed` (`executed`)
			) ";
		$this->QUERY_SQL($sql,'artica_backup');	
	
		}
		
	if(!$this->FIELD_EXISTS("deploy_tasks","debug_mode","artica_backup")){
		$sql="ALTER TABLE `deploy_tasks` ADD `debug_mode` INT( 1 ) NOT NULL DEFAULT '0'";
		$this->QUERY_SQL($sql,"artica_backup");
	}		
		

		
		
	
	if(!$this->FIELD_EXISTS("storage","file_path","artica_backup")){
		$sql="ALTER TABLE `storage` ADD `file_path` varchar(255) NOT NULL ,ADD INDEX ( `file_path` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}
	
	if(!$this->FIELD_EXISTS("storage","original_messageid","artica_backup")){
		$sql="ALTER TABLE `storage` ADD `original_messageid` varchar(80) NOT NULL ,ADD INDEX ( `original_messageid` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}	
	
	if(!$this->FIELD_EXISTS("storage","message_size","artica_backup")){
		$sql="ALTER TABLE `storage` ADD `message_size` INT(10) NOT NULL ";
		$this->QUERY_SQL($sql,"artica_backup");
	}		

	if(!$this->FIELD_EXISTS("storage","mailfrom_domain","artica_backup")){
		$sql="ALTER TABLE `storage` ADD `mailfrom_domain` varchar(255) NOT NULL, ADD INDEX ( `mailfrom_domain` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}	

	
	if(!$this->FIELD_EXISTS("storage","mailto","artica_backup")){
		$sql="ALTER TABLE `storage` ADD `mailto` varchar(255) NOT NULL ,ADD INDEX ( `mailto` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}


	$this->QUERY_SQL($sql,"artica_backup");	
		
	}
	
	
	function Check_reports_table(){
		if(!$this->TABLE_EXISTS('reports','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`reports` (
			`ID` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`report_type` INT( 1 ) NOT NULL ,
			`report_datas` TEXT NOT NULL ,
			`enabled` INT( 1 ) NOT NULL ,
			`ou` VARCHAR( 255 ) NOT NULL ,
			INDEX ( `report_type` , `enabled` , `ou` )
			)";		
			$this->QUERY_SQL($sql,'artica_backup');
		}
	}
	
	
	function Check_quarantine_table(){
		if(!$this->TABLE_EXISTS('quarantine','artica_backup')){		
			$sql="CREATE TABLE IF NOT EXISTS `quarantine` (
					  `MessageID` varchar(80) NOT NULL,
					  `zDate` datetime NOT NULL,
					  `mailfrom` varchar(255) NOT NULL,
					  `mailfrom_domain` varchar(255) NOT NULL,
					  `mailto` varchar(255) NOT NULL,
					  `subject` varchar(255) NOT NULL,
					  `MessageBody` text NOT NULL,
					  `fullmesg` text NOT NULL,
					  `header` text NOT NULL,
					  `organization` varchar(80) NOT NULL,
					  PRIMARY KEY  (`MessageID`),
					  KEY `mailfrom` (`mailfrom`),
					  KEY `mailfrom_domain` (`mailfrom_domain`),
					  KEY `mailto` (`mailto`),
					  FULLTEXT KEY `MessageBody` (`MessageBody`),  FULLTEXT KEY `header` (`header`)
					  );";
				
				$this->QUERY_SQL($sql,'artica_backup');
				}
	
		if(!$this->TABLE_EXISTS('smtp_attachments_blocking','artica_backup')){			
			$sql="CREATE TABLE IF NOT EXISTS `smtp_attachments_blocking` (
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `ou` varchar(255) NOT NULL,
			  `IncludeByMime` varchar(90) NOT NULL,
			  `ExcludeByMime` varchar(90) NOT NULL,
			  `IncludeByName` varchar(90) NOT NULL,
			  `ExcludeByName` varchar(90) NOT NULL,
			  `hostname` varchar(255) NOT NULL,
			  PRIMARY KEY (`ID`),
			  	KEY `ou` (`ou`),
			  	KEY `IncludeByMime` (`IncludeByMime`),
			  	KEY `ExcludeByMime` (`ExcludeByMime`),
			  	KEY `IncludeByName` (`IncludeByName`),
			 	KEY `hostname` (`hostname`)
			 	)	";
			$this->QUERY_SQL($sql,'artica_backup');
			}
			
			if(!$this->FIELD_EXISTS("smtp_attachments_blocking","hostname","artica_backup")){
				$sql="ALTER TABLE `smtp_attachments_blocking` ADD `hostname` VARCHAR( 255 ) NOT NULL ,ADD INDEX ( `hostname` )";
				$this->QUERY_SQL($sql,"artica_backup");
			}			
	
	if(!$this->TABLE_EXISTS('kavmilter','artica_backup')){	
	$sql="CREATE TABLE `artica_backup`.`kavmilter` (
			`ou` VARCHAR( 125 ) NOT NULL ,
			`config` TEXT NOT NULL ,
			`configlms` TEXT NOT NULL ,
			PRIMARY KEY ( `ou` ))";
	 $this->QUERY_SQL($sql,'artica_backup');
	}
	
	if(!$this->FIELD_EXISTS("kavmilter","configlms","artica_backup")){
		$sql="ALTER TABLE `kavmilter` ADD `configlms` TEXT NOT NULL ";
		$this->QUERY_SQL($sql,"artica_backup");
	}
	
	
		if(!$this->TABLE_EXISTS('kas3','artica_backup')){	
			$sql="CREATE TABLE IF NOT EXISTS `kas3` (
				  `ID` int(10) NOT NULL AUTO_INCREMENT,
				  `ou` varchar(255) NOT NULL,
				  `key` varchar(255) NOT NULL DEFAULT '',
				  `value` varchar(255) NOT NULL DEFAULT '',
				  	PRIMARY KEY (`ID`),
				  	KEY `ou` (`ou`),
				  	KEY `key` (`key`)
					)";
			$this->QUERY_SQL($sql,"artica_backup");
		}
		
			if(!$this->TABLE_EXISTS('spamassassin_spf_wl','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`spamassassin_spf_wl` (
				`ID` INT( 20 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`domain` VARCHAR( 255 ) NOT NULL ,
				UNIQUE (`domain`))";
			$this->QUERY_SQL($sql,"artica_backup");
		}
		
		if(!$this->TABLE_EXISTS('spamassassin_dkim_wl','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`spamassassin_dkim_wl` (
				`ID` INT( 20 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`domain` VARCHAR( 255 ) NOT NULL ,
				UNIQUE (`domain`))";
			$this->QUERY_SQL($sql,"artica_backup");
		}		

	
	

	
	if(!$this->FIELD_EXISTS("quarantine","file_path","artica_backup")){
		$sql="ALTER TABLE `quarantine` ADD `file_path` varchar(255) NOT NULL ,ADD INDEX ( `file_path` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}
	
	if(!$this->FIELD_EXISTS("quarantine","original_messageid","artica_backup")){
		$sql="ALTER TABLE `quarantine` ADD `original_messageid` varchar(200) NOT NULL ,ADD INDEX ( `original_messageid` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}	
	
	if(!$this->FIELD_EXISTS("quarantine","message_size","artica_backup")){
		$sql="ALTER TABLE `quarantine` ADD `message_size` INT(10) NOT NULL";
		$this->QUERY_SQL($sql,"artica_backup");
	}

	if(!$this->FIELD_EXISTS("quarantine","mailto","artica_backup")){
		$sql="ALTER TABLE `quarantine` ADD `mailto` varchar(255) NOT NULL ,ADD INDEX ( `mailto` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}		
	
	if(!$this->FIELD_EXISTS("quarantine","BinMessg","artica_backup")){
		$sql="ALTER TABLE `quarantine` ADD `BinMessg` LONGBLOB NOT NULL";
		$this->QUERY_SQL($sql,"artica_backup");
	}

	if(!$this->FIELD_EXISTS("quarantine","filesize","artica_backup")){
		$sql="ALTER TABLE `quarantine` ADD `filesize` INT( 10 ) NOT NULL 	";
		$this->QUERY_SQL($sql,"artica_backup");
	}	

	if(!$this->FIELD_EXISTS("quarantine","filename","artica_backup")){
		$sql="ALTER TABLE `quarantine` ADD `filename` VARCHAR( 128 ) NOT NULL ,ADD INDEX ( filename )";
		$this->QUERY_SQL($sql,"artica_backup");
	}		

	
		
	}
	
	
private	function CheckTables_repositories(){
		
		$sql="CREATE TABLE IF NOT EXISTS `debian_packages_logs` (
			  `ID` int(5) NOT NULL auto_increment,
			  `zDate` datetime NOT NULL,
			  `package_name` varchar(255) NOT NULL,
			  `events` text NOT NULL,
			  `install_type` varchar(50) NOT NULL,
			  PRIMARY KEY  (`ID`),
			  KEY `package_name` (`package_name`),
			  KEY `install_type` (`install_type`)
			) ";
		
		$this->QUERY_SQL($sql,'artica_backup');
		
		$sql="CREATE TABLE IF NOT EXISTS `debian_packages` (
			  `package_name` varchar(255) NOT NULL,
			  `package_version` varchar(255) NOT NULL,
			  `package_info` varchar(255) NOT NULL,
			  `package_description` text NOT NULL,
			  `package_status` varchar(10) NOT NULL,
			  UNIQUE KEY `package_name` (`package_name`),
			  KEY `package_version` (`package_version`),
			  KEY `package_status` (`package_status`)
				) ";
		
		$this->QUERY_SQL($sql,'artica_backup');
		if(!$this->TABLE_EXISTS('logon_scripts','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`logon_scripts` (`gpid` INT( 10 ) NOT NULL ,`script_code` TEXT NOT NULL ,PRIMARY KEY ( `gpid` ))";
			$this->QUERY_SQL($sql,'artica_backup');
		}
		
		
	}
	
	public function CheckTables_openvpn(){
		
				if(!$this->TABLE_EXISTS('hamachi','artica_backup')){		
					$sql="CREATE TABLE `artica_backup`.`hamachi` (
					`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`pattern` TEXT NOT NULL)";
			 
					$this->QUERY_SQL($sql,'artica_backup');
				}
			
		
			if(!$this->TABLE_EXISTS('vpnclient','artica_backup')){	
				$sql="		
					CREATE TABLE IF NOT EXISTS `vpnclient` (
					  `ID` int(11) NOT NULL AUTO_INCREMENT,
					  `servername` varchar(180) NOT NULL,
					  `serverport` int(3) NOT NULL,
					  `admin` varchar(50) NOT NULL,
					  `password` varchar(128) NOT NULL,
					  `connexion_name` varchar(128) NOT NULL,
					  `sitename` varchar(128) NOT NULL,
					  `IP_START` varchar(25) NOT NULL,
					  `netmask` varchar(25) NOT NULL,
					  `ethlisten` varchar(10) NOT NULL,
					  `keypassword` varchar(255) NOT NULL,
					  `connexion_type` int(1) NOT NULL,
					  `ca_bin` longblob NOT NULL,
					  `key_bin` longblob NOT NULL,
					  `cert_bin` longblob NOT NULL,
					  `ovpn` longblob NOT NULL,
					  `routes` text NOT NULL,
					  `enabled` int(1) NOT NULL,
					  PRIMARY KEY (`ID`),
					  KEY `sitename` (`sitename`),
					  KEY `ethlisten` (`ethlisten`),
					  KEY `connexion_type` (`connexion_type`),
					  KEY `enabled` (`enabled`)
					) ";
							
		
				if(!$this->QUERY_SQL($sql,'artica_backup')){return false;}
				
			}
			
	
				
				if(!$this->FIELD_EXISTS("vpnclient","enabled","artica_backup")){
					$sql="ALTER TABLE `vpnclient` ADD `enabled` int(1) NOT NULL ,ADD INDEX ( `enabled` )";
					if(!$this->QUERY_SQL($sql,"artica_backup")){return false;}
				}
				
				if(!$this->FIELD_EXISTS("vpnclient","ethlisten","artica_backup")){
					$sql="ALTER TABLE `vpnclient` ADD `ethlisten` varchar(10) NOT NULL ,ADD INDEX ( `ethlisten` )";
					if(!$this->QUERY_SQL($sql,"artica_backup")){return false;}
				}	
				
				if(!$this->FIELD_EXISTS("vpnclient","keypassword","artica_backup")){
					$sql="ALTER TABLE `vpnclient` ADD `keypassword` varchar(255) NOT NULL";
					if(!$this->QUERY_SQL($sql,"artica_backup")){return false;}
				}					

			return true;

	}
	
	
	private function CheckTables_dar(){
		
			if(!$this->TABLE_EXISTS('dar_index','artica_backup')){	
				$sql="		
				CREATE TABLE IF NOT EXISTS `dar_index` (
				  `filekey` varchar(90) NOT NULL,
				  `filedate` datetime NOT NULL,
				  `filepath` varchar(255) NOT NULL,
				  `basepath` varchar(255) NOT NULL,
				  `database_name` varchar(90) NOT NULL,
				  `source_path` varchar(255) NOT NULL,
				  `mount_md5` varchar(90) NOT NULL,
				  `servername` varchar(90) NOT NULL,
				  `filesize` int(10) NOT NULL default '0',
				  PRIMARY KEY  (`filekey`),
				  KEY `filepath` (`filepath`,`basepath`,`database_name`,`mount_md5`,`servername`),
				  KEY `source_path` (`source_path`)
				)";
		
				if(!$this->QUERY_SQL($sql,'artica_backup')){return false;}
				return true;
			}
			if(!$this->FIELD_EXISTS("dar_index","mount_md5","artica_backup")){
				$sql="ALTER TABLE `dar_index` ADD `mount_md5` varchar(90) NOT NULL ,ADD INDEX ( `mount_md5` )";
				if(!$this->QUERY_SQL($sql,"artica_backup")){return false;}
			}
			
			if(!$this->FIELD_EXISTS("dar_index","servername","artica_backup")){
				$sql="ALTER TABLE `dar_index` ADD `servername` varchar(90) NOT NULL ,ADD INDEX ( `servername` )";
				if(!$this->QUERY_SQL($sql,"artica_backup")){return false;}
			}
		return true;	
			
	}
	
	public function CheckTable_dansguardian(){
		if(!$this->TABLE_EXISTS('dansguardian_categories','artica_backup')){
			$sql="CREATE TABLE IF NOT EXISTS `dansguardian_categories` (
  					`ID` int(3) NOT NULL AUTO_INCREMENT,
 					 `category_name` varchar(75) NOT NULL,
  					`pattern` varchar(255) NOT NULL,
  					PRIMARY KEY (`ID`),
  					KEY `category_name` (`category_name`,`pattern`))";
				$this->QUERY_SQL($sql,'artica_backup');
				if(!$this->ok){$this->mysql_error=$this->mysql_error."\n$sql";}
		}
		
		if(!$this->TABLE_EXISTS('dansguardian_community_categories','artica_backup')){	
		$sql="CREATE TABLE `artica_backup`.`dansguardian_community_categories` (
				`zmd5` VARCHAR( 90 ) NOT NULL ,
				`zDate` DATETIME NOT NULL ,
				`category` VARCHAR( 20 ) NOT NULL ,
				`pattern` VARCHAR( 255 ) NOT NULL ,
				`enabled` INT( 1 ) NOT NULL DEFAULT '1',
				`uuid` VARCHAR( 255 ) NOT NULL ,
				`sended` INT( 1 ) NOT NULL DEFAULT '0',
				PRIMARY KEY ( `zmd5` ) ,
				KEY `zDate` (`zDate`),
	  			KEY `pattern` (`pattern`),
	  			KEY `enabled` (`enabled`),
	  			KEY `sended` (`sended`),
	  			KEY `category` (`category`)
			)";
			$this->QUERY_SQL($sql,'artica_backup');
		}	
		
		
		if(!$this->TABLE_EXISTS('dansguardian_groups','artica_backup')){
		$sql="CREATE TABLE IF NOT EXISTS `dansguardian_groups` (
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `RuleID` int(5) NOT NULL,
			  `group_id` int(10) NOT NULL,
			  PRIMARY KEY (`ID`),
			  UNIQUE KEY `group_id` (`group_id`),
			  KEY `RuleID` (`RuleID`)
			) ";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){
					$this->mysql_error=$this->mysql_error."\n$sql";
			}
		}
		
			if(!$this->TABLE_EXISTS('dansguardian_personal_categories','artica_backup')){
			$sql="CREATE TABLE IF NOT EXISTS `dansguardian_personal_categories` (
				  `ID` int(10) NOT NULL AUTO_INCREMENT,
				  `category` varchar(255) NOT NULL,
				  `pattern` varchar(255) NOT NULL,
				  `category_type` varchar(80) NOT NULL,
				  `RuleID` int(10) NOT NULL,
				  PRIMARY KEY (`ID`),
				  KEY `category` (`category`,`pattern`,`category_type`),
				  KEY `RuleID` (`RuleID`)
				)";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){
					$this->mysql_error=$this->mysql_error."\n$sql";
			}
		}		
		
		

		
		if(!$this->TABLE_EXISTS('dansguardian_files','artica_backup')){
			 $sql="CREATE TABLE IF NOT EXISTS `dansguardian_files` (
			  `ID` int(5) NOT NULL AUTO_INCREMENT,
			  `filename` varchar(50) NOT NULL,
			  `pattern` text NOT NULL,
			  `infos` varchar(125) NOT NULL,
			  `RuleID` int(2) NOT NULL,
			  `enabled` int(1) NOT NULL DEFAULT '1',
			  `userdefined` int(1) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`ID`),
			  KEY `filename` (`filename`),
			  KEY `RuleID` (`RuleID`),
			  KEY `enabled` (`enabled`),
			  KEY `userdefined` (`userdefined`)
			)";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){
				$this->mysql_error=$this->mysql_error."\n$sql";
				return false;}
		}
		
		if(!$this->TABLE_EXISTS('dansguardian_rules','artica_backup')){
			 $sql="CREATE TABLE IF NOT EXISTS `dansguardian_rules` (
				  `RuleID` int(1) NOT NULL AUTO_INCREMENT,
				  `RuleOrder` int(1) NOT NULL,
				  `RuleName` varchar(120) NOT NULL,
				  `RuleText` text NOT NULL,
				  PRIMARY KEY (`RuleID`),
				  KEY `RuleOrder` (`RuleOrder`,`RuleName`))";
			 $this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){
				$this->mysql_error=$this->mysql_error."\n$sql";
				return false;}
		}
		
		if(!$this->TABLE_EXISTS('dansguardian_weightedphraselist','artica_backup')){
			 $sql="CREATE TABLE IF NOT EXISTS `dansguardian_weightedphraselist` (
			  `ID` int(3) NOT NULL AUTO_INCREMENT,
			  `category_name` varchar(75) NOT NULL,
			  `pattern` varchar(255) NOT NULL,
			  PRIMARY KEY (`ID`),
			  KEY `category_name` (`category_name`,`pattern`))";
			 $this->QUERY_SQL($sql,'artica_backup'); 
			if(!$this->ok){
				$this->mysql_error=$this->mysql_error."\n$sql";
				return false;}
		} 
		
		if(!$this->TABLE_EXISTS('dansguardian_ipgroups','artica_backup')){
			 $sql="CREATE TABLE `artica_backup`.`dansguardian_ipgroups` (
				`ID` INT( 3 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`pattern` VARCHAR( 128 ) NOT NULL ,
				`RuleID` INT( 1 ) NOT NULL ,
				INDEX ( `pattern` , `RuleID` )
				)";
			 $this->QUERY_SQL($sql,'artica_backup'); 
			if(!$this->ok){
				$this->mysql_error=$this->mysql_error."\n$sql";
				return false;}
		} 

		if(!$this->TABLE_EXISTS('dansguardian_whitelists','artica_events')){
			 $sql="CREATE TABLE IF NOT EXISTS `dansguardian_whitelists` (
				  `ID` int(11) NOT NULL AUTO_INCREMENT,
				  `zDate` datetime NOT NULL,
				  `uri` varchar(255) NOT NULL,
				  `uid` varchar(255) NOT NULL,
				  PRIMARY KEY (`ID`),
				  UNIQUE KEY `ID` (`ID`),
				  KEY `uri` (`uri`),
				  KEY `uid` (`uid`)
				) ";
			 $this->QUERY_SQL($sql,'artica_events'); 
			if(!$this->ok){
				$this->mysql_error=$this->mysql_error."\n$sql";
				return false;}
		}
		
		
	if(!$this->TABLE_EXISTS('dansguardian_events','artica_events')){
		writelogs("Checking dansguardian_events in artica_events NOT EXISTS...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$sql="CREATE TABLE IF NOT EXISTS `dansguardian_events` (
				  `sitename` varchar(90) NOT NULL,
				  `ID` int(11) NOT NULL AUTO_INCREMENT,
				  `uri` varchar(90) NOT NULL,
				  `TYPE` varchar(50) NOT NULL,
				  `REASON` varchar(255) NOT NULL,
				  `CLIENT` varchar(50) NOT NULL DEFAULT '',
				  `zDate` datetime NOT NULL,
				  `zMD5` varchar(90) NOT NULL,
				  `remote_ip` varchar(20) NOT NULL,
				  `country` varchar(20) NOT NULL,
				  `QuerySize` int(10) NOT NULL,
				  PRIMARY KEY (`ID`),
				  UNIQUE KEY `zMD5` (`zMD5`),
				  KEY `sitename` (`sitename`,`TYPE`,`CLIENT`,`uri`),
				  KEY `zDate` (`zDate`),
				  KEY `uri` (`uri`),
				  KEY `remote_ip` (`remote_ip`),
				  KEY `country` (`country`)
				) ";
			 $this->QUERY_SQL($sql,'artica_events'); 
			if(!$this->ok){
				writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$this->mysql_error=$this->mysql_error."\n$sql";
				return false;
			}else{
				writelogs("Checking dansguardian_events SUCCESS",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
			}
			

			
			
			
		}

	if(!$this->FIELD_EXISTS("dansguardian_events","remote_ip","artica_events")){
				$sql="ALTER TABLE `dansguardian_events` ADD `remote_ip` varchar(20) NOT NULL ,ADD INDEX ( `remote_ip` )";
				if(!$this->QUERY_SQL($sql,"artica_events")){return false;}
			}
			
		if(!$this->FIELD_EXISTS("dansguardian_events","country","artica_events")){
				$sql="ALTER TABLE `dansguardian_events` ADD `country` varchar(20) NOT NULL ,ADD INDEX ( `country` )";
				if(!$this->QUERY_SQL($sql,"artica_events")){return false;}
		}

			
		if(!$this->FIELD_EXISTS("dansguardian_events","QuerySize","artica_events")){
				$sql="ALTER TABLE `dansguardian_events` ADD `QuerySize` INT(10) NOT NULL";
				if(!$this->QUERY_SQL($sql,"artica_events")){return false;}
		}			
	
		if(!$this->FIELD_EXISTS("dansguardian_events","uid","artica_events")){
				$sql="ALTER TABLE `dansguardian_events` ADD `uid` VARCHAR( 55 ) NOT NULL,ADD INDEX ( `uid` ) ";
				if(!$this->QUERY_SQL($sql,"artica_events")){return false;}
		}	
		
		return true;
		
		
	}
	
	private function CheckTablesSquid(){
		if(!$this->TABLE_EXISTS('squid_block','artica_backup')){
		$sql="CREATE TABLE `artica_backup`.`squid_block` (
			`ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`uri` VARCHAR( 255 ) NOT NULL ,`task_type` VARCHAR( 90 ) NOT NULL ,
			`zDate` DATETIME NOT NULL ,INDEX ( `task_type` , `zDate` ) ,UNIQUE (`uri`))";
		$this->QUERY_SQL($sql,"artica_backup");
		}
		
		
		
		if(!$this->TABLE_EXISTS('squid_accel','artica_backup')){
			$sql="CREATE TABLE `artica_backup`.`squid_accel` (
			`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`website_name` VARCHAR( 255 ) NOT NULL ,
			`website_ip` VARCHAR( 25 ) NOT NULL ,
			`website_port` INT NOT NULL ,
			`options` TEXT NOT NULL ,
			INDEX ( `website_name` )) ";
			$this->QUERY_SQL($sql,"artica_backup");
		}
		
		
		if(!$this->TABLE_EXISTS('squid_speed','artica_backup')){		
		$sql="CREATE TABLE `artica_backup`.`squid_speed` (
			`ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`domain` VARCHAR( 255 ) NOT NULL ,
			`refresh_pattern_min` INT( 5 ) NOT NULL DEFAULT '0',
			`refresh_pattern_perc` INT( 2 ) NOT NULL DEFAULT '20',
			`refresh_pattern_max` INT( 5 ) NOT NULL DEFAULT '4320',
			`refresh_pattern_options` VARCHAR( 255 ) NOT NULL ,
			INDEX ( `domain` ));";
		$this->QUERY_SQL($sql,"artica_backup");	
		}
		
		if(!$this->TABLE_EXISTS('kav4Proxy','artica_backup')){		
			$sql="
				CREATE TABLE IF NOT EXISTS `kav4Proxy` (
				  `ID` int(11) NOT NULL AUTO_INCREMENT,
				  `key` varchar(50) NOT NULL,
				  `value` varchar(50) NOT NULL,
				  `data` varchar(255) NOT NULL,
				  `zMD5` varchar(95) NOT NULL,
				  PRIMARY KEY (`ID`),
				  UNIQUE KEY `zMD5` (`zMD5`),
				  KEY `key` (`key`,`value`)
				) "; 		
			$this->QUERY_SQL($sql,"artica_backup");
		}
		
			if(!$this->TABLE_EXISTS('squid_parents','artica_backup')){		
			$sql="
				CREATE TABLE `artica_backup`.`squid_parents` (
				`ID` INT( 3 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`servername` VARCHAR( 255 ) NOT NULL ,
				`server_port` INT( 3 ) NOT NULL ,
				`server_type` VARCHAR( 50 ) NOT NULL ,
				`icp_port` INT( 3 ) NOT NULL ,
				`options` TEXT NOT NULL ,
				INDEX ( `servername` ))"; 		
			$this->QUERY_SQL($sql,"artica_backup");
		}		
		
		
 		
		
	}
	
	
	function CheckTables_rsync(){
		
			if(!$this->TABLE_EXISTS('rsync_events','artica_events')){	
					$sql="
						CREATE TABLE IF NOT EXISTS `rsync_events` (
					  `ID` int(20) NOT NULL AUTO_INCREMENT,
					  `folder_md5` varchar(90) NOT NULL,
					  `date_start` datetime NOT NULL,
					  `date_end` datetime NOT NULL,
					  `events` text NOT NULL,
					  `path` varchar(255)  NOT NULL,
					  `failed` int(1) NOT NULL,
					  `sent_size` float NOT NULL,
					  `speed` float NOT NULL,
					  `numfiles` int(5) NOT NULL,
					  `storage_server` varchar(80) NOT NULL,
					  PRIMARY KEY (`ID`),
					  KEY `failed` (`failed`),
					  KEY `storage_server` (`storage_server`),
					  KEY `folder_md5` (`folder_md5`))";		
								
					$this->QUERY_SQL($sql,'artica_events');
				}
				
			if(!$this->TABLE_EXISTS('dar_events','artica_events')){	
					$sql="
					CREATE TABLE IF NOT EXISTS `dar_events` (
					  `ID` int(10) NOT NULL auto_increment,
					  `date_start` datetime NOT NULL,
					  `date_end` datetime NOT NULL,
					  `db_path` varchar(255) NOT NULL,
					  `xml` varchar(255) NOT NULL,
					  `source` varchar(255) NOT NULL,
					  `failed` int(1) NOT NULL default '0',
					  `builded` int(1) NOT NULL,
					  `error` varchar(255) NOT NULL,
					  PRIMARY KEY  (`ID`),
					  KEY `date_start` (`date_start`,`date_end`,`db_path`,`xml`,`source`,`failed`,`builded`)) ";
	
					$this->QUERY_SQL($sql,'artica_events');
				}

		if(!$this->TABLE_EXISTS('backup_folders','artica_backup')){			
				
			$sql="CREATE TABLE `artica_backup`.`backup_folders` (
					`ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`taskid` INT( 10 ) NOT NULL ,
					`path` VARCHAR( 255 ) NOT NULL ,
					`zmd5` VARCHAR( 90 ) NOT NULL ,
					`recursive` INT( 1 ) NOT NULL DEFAULT '0',
					INDEX ( `taskid` , `path` , `zmd5` ))"; 
					$this->QUERY_SQL($sql,'artica_backup');
	
		}
		
			if(!$this->TABLE_EXISTS('rsync_server','artica_events')){			
				
			$sql="CREATE TABLE `artica_events`.`rsync_server` (
					`ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`ip_address` VARCHAR( 90 ) NOT NULL ,
					`zDate` DATETIME NOT NULL ,
					`transaction_size` INT( 20 ) NOT NULL ,
					INDEX ( `ip_address` , `zDate` )) "; 
					$this->QUERY_SQL($sql,'artica_events');
	
		}		
	
	}
}


?>