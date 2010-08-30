<?php
include_once(dirname(__FILE__).'/class.templates.inc');
include_once(dirname(__FILE__).'/class.ldap.inc');
include_once(dirname(__FILE__).'/class.mysql.inc');

	class ocs{
		var $mac=null;
		var $HARDWARE_ID=0;
		var $ComputerName;
		var $ComputerOS;
		var $ComputerIP;
		var $PROLOG_FREQ=0;
		
		function ocs($mac=null){
			if($mac<>null){
				$this->mac=$mac;
				$this->Loadinfos();
			}
		}
		
		private function Loadinfos(){
			$sql="SELECT HARDWARE_ID FROM networks WHERE MACADDR='$this->mac'";
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));
			if($ligne["HARDWARE_ID"]>0){
				$this->HARDWARE_ID=$ligne["HARDWARE_ID"];
				
			}
			
		}
		
		public function LoadParams(){
			if($this->HARDWARE_ID==0){return null;}
			$q=new mysql();
			$sql="SELECT * FROM hardware WHERE ID='$this->HARDWARE_ID'";
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));
			$this->ComputerName=$ligne["NAME"];
			$this->ComputerOS=$ligne["OSNAME"];
			$this->ComputerIP=$ligne["IPADDR"];
			
			
		}
		
		public function GET_SERVER_SETTINGS(){
			$sql="SELECT * FROM config";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"ocsweb");
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
				$array[$ligne["NAME"]]=$ligne["IVALUE"];
			}
			
			return $array;
		}
		
		public function SET_SERVER_SETTINGS($key,$value){
			$sql="UPDATE config SET IVALUE='$value' WHERE NAME='$key'";
			$q=new mysql();
			$q->QUERY_SQL($sql,"ocsweb");
			if(!$q->ok){echo __FUNCTION__.":".$q->mysql_error;return;}
			return true;
		}
		
		
		public function BuildFirstInfos(){
			if($this->HARDWARE_ID==0){return null;}
			$q=new mysql();
			$sql="SELECT * FROM networks WHERE HARDWARE_ID='$this->HARDWARE_ID'";
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));			
			$html="
			<table style='width:100%'>
			<tr>
			<td valign='top' width=1%><img src='img/folder-network-64.png'></td>
			<td valign='top'>
			<H3>{tcp_main_settings}</H3>
			<table style='width:100%'>
			<tr>
				<td class=legend nowrap width=1%>{tcp_address}:</td>
				<td><span style='font-size:11px;font-weight:bold'>{$ligne["IPADDRESS"]}</span></td>
			</tr>
			<tr>
				<td class=legend nowrap width=1%>{netmask}:</td>
				<td><span style='font-size:11px;font-weight:bold'>{$ligne["IPMASK"]}</span></td>
			</tr>
			<tr>
				<td class=legend nowrap width=1%>{gateway}:</td>
				<td><span style='font-size:11px;font-weight:bold'>{$ligne["IPGATEWAY"]}</span></td>
			</tr>	
			<tr>
				<td class=legend nowrap width=1%>{NIC_NAME}:</td>
				<td><span style='font-size:11px;font-weight:bold'>{$ligne["DESCRIPTION"]}</span></td>
			</tr>								
			</table>
			</tr>
			</table>";
			
			return $this->SystemInfos()."<hr>".RoundedLightWhite($html)."<hr>".$this->BuidStorage()."<hr>".$this->BuidMemory();
		}
		
		private function BuidStorage(){
			$sql="SELECT * FROM storages WHERE HARDWARE_ID='$this->HARDWARE_ID'";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"ocsweb");
			$html="<table style='width:99%'>";
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
				
				$DISKSIZE=$ligne["DISKSIZE"];
				$name=$ligne["NAME"];
				$MANUFACTURER=$ligne["MANUFACTURER"];
				$DESCRIPTION=$ligne["DESCRIPTION"];
				$TYPE=$ligne["TYPE"];
				
				$tooltip="{manufacturer}:<b>$MANUFACTURER</b><br>{description}:<b>$DESCRIPTION</b><br>{type}:$TYPE";
				
				$DISKSIZE_TOT=$DISKSIZE_TOT+$DISKSIZE;
				$DISKSIZE=FormatBytes(($DISKSIZE*1024));
				
				$name=texttooltip($name,$tooltip,null,null,1);
				
				$html=$html . "
				<tr ". CellRollOver().">
					<td width=1%><img src='img/mailbox_hd.gif'></td>
					<td><span style='font-size:11px;font-weight:bold'>$name</span>
					<td><span style='font-size:11px;font-weight:bold'>{$DISKSIZE}</span>
				</tr>
				
				";
				
			}
			$DISKSIZE_TOT=FormatBytes(($DISKSIZE_TOT*1024));
			$html=$html."
			<tr ". CellRollOver().">
					<td width=1%>&nbsp;</td>
					<td><span style='font-size:11px;font-weight:bold'>&nbsp;</span>
					<td><span style='font-size:11px;font-weight:bold'>{$DISKSIZE_TOT}</span>
				</tr>
			</table>";
			$page="			<table style='width:99%'>
			<tr>
			<td valign='top' width=1%><img src='img/hd-64.png'></td>
			<td valign='top'>
			<H3>{internal_hard_drives}</H3>
			$html
			</td>
			</tr>
			</table>";
			
			return RoundedLightWhite($page);
			
			
		}
	    private function BuidMemory(){
			$sql="SELECT * FROM memories WHERE HARDWARE_ID='$this->HARDWARE_ID'";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"ocsweb");
			$html="<table style='width:100%'>";
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
				
				$DISKSIZE=$ligne["CAPACITY"];
				$name=$ligne["CAPTION"];
				$DESCRIPTION=$ligne["DESCRIPTION"];
				$TYPE=$ligne["TYPE"];
				if($DISKSIZE==0){continue;}
				$DISKSIZE_TOT=$DISKSIZE_TOT+$DISKSIZE;
				$DISKSIZE=FormatBytes(($DISKSIZE*1024));
				
				$tooltip="<br>{description}:<b>$DESCRIPTION</b><br>{type}:$TYPE";
				
				$name=texttooltip($name,$tooltip,null,null,1);
				
				$html=$html . "
				<tr ". CellRollOver().">
					<td width=1%><img src='img/fw_bold.gif'></td>
					<td><span style='font-size:11px;font-weight:bold'>$name</span>
					<td><span style='font-size:11px;font-weight:bold'>{$DISKSIZE}</span>
				</tr>
				
				";
				
			}
			$DISKSIZE_TOT=FormatBytes(($DISKSIZE_TOT*1024));
			$html=$html."
			<tr ". CellRollOver().">
					<td width=1%>&nbsp;</td>
					<td><span style='font-size:11px;font-weight:bold'>&nbsp;</span>
					<td><span style='font-size:11px;font-weight:bold'>{$DISKSIZE_TOT}</span>
				</tr>			
			</table>";
			$page="			<table style='width:99%'>
			<tr>
			<td valign='top' width=1%><img src='img/bg_memory-64.png'></td>
			<td valign='top'>
			<H3>{memory}</H3>
			$html
			</td>
			</tr>
			</table>";
			
			return RoundedLightWhite($page);
			
			
		}
		
		private function SystemInfos(){
			if($this->HARDWARE_ID==0){return null;}
			$q=new mysql();
			$sql="SELECT * FROM hardware WHERE ID='$this->HARDWARE_ID'";
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));	
			$html="
			<table style='width:99%'>
			<tr>
			<td valign='top' width=1%><img src='img/64-computer.png'></td>
			<td valign='top'>
			
			<table style='width:100%'>
			<tr>
				<td class=legend nowrap width=1% valign='top'>{computer_name}:</td>
				<td><span style='font-size:11px;font-weight:bold'>{$ligne["NAME"]}</span></td>
			</tr>
			<tr>
				<td class=legend nowrap width=1% valign='top'>{WORKGROUP}:</td>
				<td><span style='font-size:11px;font-weight:bold'>{$ligne["WORKGROUP"]}</span></td>
			</tr>
			<tr>
				<td class=legend nowrap width=1% valign='top'>{ComputerOS}:</td>
				<td><span style='font-size:11px;font-weight:bold'>{$ligne["OSNAME"]} ({$ligne["OSVERSION"]}) ({$ligne["OSCOMMENTS"]})</span></td>
			</tr>	
			<tr>
				<td class=legend nowrap width=1% valign='top'>{ComputerCPU}:</td>
				<td><span style='font-size:11px;font-weight:bold'>{$ligne["PROCESSORT"]} ({$ligne["PROCESSORN"]})</span></td>
			</tr>								
			</table>
			</tr>
			</table>";			
			return RoundedLightWhite($html);
			
		}
		
		function PACKAGE_ID_FROM_FILEID($FILEID){
			if($_SESSION["PACKAGE_ID_FROM_FILEID"][$FILEID]>0){return $_SESSION["PACKAGE_ID_FROM_FILEID"][$FILEID];}
			if($FILEID<1){return 0;}
			$sql="SELECT ID FROM download_enable WHERE FILEID='$FILEID'";
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));
			$ID=$ligne["ID"];
			if($ID==null){return 0;}
			$_SESSION["PACKAGE_ID_FROM_FILEID"][$FILEID]=$ID;
			return $ID;
		}
		
		function PACKAGE_NAME_FROM_FILEID($FILEID){
			$sql="SELECT NAME FROM download_available WHERE FILEID=$FILEID";
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));
			return $ligne["NAME"];
			
		}
		
		function COMPUTER_SEARCH_QUERY($pattern=null){
				$pattern=str_replace("*","%",$pattern);
				$pattern=$pattern."%";
				$pattern=str_replace("%%","%",$pattern);
				$osc=new ocs();
				
				
				
				return "SELECT hardware.LASTCOME,networks.MACADDR,networks.IPADDRESS,networks.HARDWARE_ID,hardware.ID,hardware.NAME,hardware.IPADDR,hardware.IPSRC FROM networks,hardware WHERE 1 AND
				(networks.HARDWARE_ID=hardware.ID AND hardware.NAME LIKE '$pattern') 
				OR (networks.HARDWARE_ID=hardware.ID AND hardware.IPADDR LIKE '$pattern')
				OR (networks.HARDWARE_ID=hardware.ID AND hardware.IPSRC LIKE '$pattern')
				ORDER BY hardware.LASTCOME DESC
				LIMIT 0,50 
				";			
			
		}
		
		/*
		 * return tableau HARDWARE_ID=>1 d'un fileid spécifié.
		 */
		
		function PACKAGE_HASH_AFFECTED_COMPUTERS($FILEID){
			if(isset($_SESSION["PACKAGE_HASH_AFFECTED_COMPUTERS"])){return $_SESSION["PACKAGE_HASH_AFFECTED_COMPUTERS"];}
			$ID=$this->PACKAGE_ID_FROM_FILEID($FILEID);
			$sql="SELECT HARDWARE_ID FROM devices WHERE IVALUE=$ID AND NAME='DOWNLOAD'";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"ocsweb");
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
				$_SESSION["PACKAGE_HASH_AFFECTED_COMPUTERS"][$ligne["HARDWARE_ID"]]=1;
				
			}
			
			return $_SESSION["PACKAGE_HASH_AFFECTED_COMPUTERS"];
			
		}
		
		function PACKAGE_IS_AFFECTED($FILEID,$HARDWARE_ID){
			$ID=$this->PACKAGE_ID_FROM_FILEID($FILEID);
			$sql="SELECT NAME FROM devices WHERE HARDWARE_ID='$HARDWARE_ID' AND IVALUE='$ID' AND NAME='DOWNLOAD'";
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));
			if($ligne["NAME"]=='DOWNLOAD'){return true;}
		}
		
		function PACKAGE_AFFECT($FILEID,$HARDWARE_ID){
			if($this->PACKAGE_IS_AFFECTED($FILEID,$HARDWARE_ID)){return;}
			$ID=$this->PACKAGE_ID_FROM_FILEID($FILEID);
			$sql="INSERT INTO devices (NAME,HARDWARE_ID,IVALUE) VALUES('DOWNLOAD',$HARDWARE_ID,$ID)";
			$q=new mysql();
			$q->QUERY_SQL($sql,"ocsweb");
			writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			if(!$q->ok){echo __FUNCTION__.":".$q->mysql_error;return;}
			$_SESSION["PACKAGE_HASH_AFFECTED_COMPUTERS"][$HARDWARE_ID]=1;
			
		}
		function PACKAGE_DESAFFECT($FILEID,$HARDWARE_ID){
			if(!$this->PACKAGE_IS_AFFECTED($FILEID,$HARDWARE_ID)){return;}
			$ID=$this->PACKAGE_ID_FROM_FILEID($FILEID);
			$sql="DELETE FROM devices WHERE HARDWARE_ID='$HARDWARE_ID' AND IVALUE='$ID' AND NAME='DOWNLOAD'";
			$q=new mysql();
			$q->QUERY_SQL($sql,"ocsweb");
			if(!$q->ok){echo __FUNCTION__.":".$q->mysql_error;return;}
			unset($_SESSION["PACKAGE_HASH_AFFECTED_COMPUTERS"][$HARDWARE_ID]);
			
		}
		
		function PACKAGE_DOWNLOAD_INFOS_HASH($FILEID){
			$sql="SELECT * FROM download_enable WHERE FILEID='$FILEID'";
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));
			return $ligne;
		}
		
	function PACKAGE_AFFECT_COUNT_COMPUTERS($FILEID){
		$ID=$this->PACKAGE_ID_FROM_FILEID($FILEID);
		$sql="SELECT COUNT(HARDWARE_ID) as tcount FROM devices WHERE IVALUE='$ID' AND NAME='DOWNLOAD'";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));
		writelogs($sql."={$ligne["tcount"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		return $ligne["tcount"];
	}

	function PACKAGE_UPDATE_ALL_RESSOURCES(){
			$sock=new sockets();
			$OCSCertInfos=unserialize(base64_decode($sock->GET_INFO("OCSCertInfos")));
			$servername=$OCSCertInfos["OCSCertServerName"];
			$domainname=$OCSCertInfos["OCSCertDomainName"];
			$OCSWebPort=$sock->GET_INFO("OCSWebPort");
			$OCSWebPortSSL=$sock->GET_INFO("OCSWebPortSSL");
			if($OCSWebPort==null){$OCSWebPort=9080;}
			if($OCSWebPortSSL==null){$OCSWebPortSSL=$OCSWebPort+50;}	
			if($servername==null){return;}
			if($domainname<>null){
				$servername=$servername.".$domainname";
			}
			
			$sql="UPDATE download_enable SET INFO_LOC='$servername:$OCSWebPortSSL/download',PACK_LOC='$servername:$OCSWebPort/download'";
			$q=new mysql();
			$q->QUERY_SQL($sql,"ocsweb");
			if(!$q->ok){echo __CLASS__.'/'.__FUNCTION__."\n".$q->mysql_error;}
		
	}
	
	function SSL_SERVER_NAME(){
			$sock=new sockets();
			$OCSCertInfos=unserialize(base64_decode($sock->GET_INFO("OCSCertInfos")));
			$servername=$OCSCertInfos["OCSCertServerName"];
			$domainname=$OCSCertInfos["OCSCertDomainName"];
			$users=new usersMenus();
			if($servername==null){$servername=$users->hostname;}
			if($domainname<>null){
				if(preg_match("#^(.+?)\.#",$servername,$re)){$servername=$re[1];}
				$servername=$servername.".$domainname";
			}
			
			return $servername;
		
	}
		
		
	 	 	
		
		
		
		
		
		
		
	}

?>