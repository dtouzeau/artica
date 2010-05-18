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
		
		
	 	 	
		
		
		
		
		
		
		
	}

?>