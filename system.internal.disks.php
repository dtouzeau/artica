<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once("ressources/class.os.system.inc");
	include_once("ressources/class.lvm.org.inc");
	
	$user=new usersMenus();
	if(!$user->AsSystemAdministrator){echo "alert('no privileges');";die();}
	
	if(isset($_GET["display"])){hd_index();exit;}
	if(isset($_GET["partinfos"])){hd_partinfos();exit;}
	if(isset($_GET["ChangeLabel"])){ChangeLabel();exit;}
	if(isset($_GET["BuildBigPartition"])){BuildBigPartition();exit;}
	if(isset($_GET["Lastlogs"])){Lastlogs();exit;}
	if(isset($_GET["SMARTDIndex"])){SMARTDIndex();exit;}
	if(isset($_GET["SMARTDStats"])){SMARTDStats();exit;}
	if(isset($_GET["SMARTDAttrs"])){SMARTDAttrs();exit;}
	if(isset($_GET["SMARTDInfo"])){SMARTDIndexInfos($_GET["SMARTDInfo"]);exit;}
	if(isset($_GET["SMARTDSlefttestShort"])){SMARTDTestShort();exit;}
	if(isset($_GET["EnableSmartctl"])){SMARTDEnable();exit;}
	if(isset($_GET["pvcreatedev"])){lvm_pvcreate_dev();exit;}
	if(isset($_GET["vgcreatedev"])){lvm_vgcreate_dev();exit;}
	if(isset($_GET["vglist"])){echo lv_groups_list($_GET["vglist"]);exit;}
	if(isset($_GET["lvcreate"])){lvm_lvcreate();exit;}
	if(isset($_GET["LVMS_SIZE"])){lvm_lvcreate_save();exit;}
	if(isset($_GET["switchtab"])){hd_partinfos_switch();exit;}
	if(isset($_GET["disk-toolbar-for"])){hd_partinfos_toolbar();exit;}
	if(isset($_GET["lvg"])){echo lv_groups_list($_GET["lvg"]);exit;}
	if(isset($_GET["hd-display"])){echo hd_list();exit;}
	if(isset($_GET["lvm_status"])){lvm_status();}
	if(isset($_GET["VGUnlinkDisk"])){lvm_unlink_disk();exit;}
	if(isset($_GET["VGlinkDisk"])){lvm_link_disk();exit;}
	if(isset($_GET["vgextend"])){lvm_vgextend_popup();exit;}
	
js();


function js(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body('{internal_hard_drives}');
	$APP_SMARTMONTOOLS=html_entity_decode($tpl->_ENGINE_parse_body('{APP_SMARTMONTOOLS}'));
	$change_label_text=html_entity_decode($tpl->_ENGINE_parse_body('{change_label_text}'));
	$macro_build_bigpart_warning=$tpl->javascript_parse_text('{macro_build_bigpart_warning}');
	$macro_build_bigpart_text=$tpl->javascript_parse_text('{macro_build_bigpart_text}');
	$vgcreate_dev_text=$tpl->javascript_parse_text('{vgcreate_dev_text}');
	$ADD_VG=$tpl->_ENGINE_parse_body('{ADD_VG}');
	$unlink_hard_drive_confirm=$tpl->javascript_parse_text('{unlink_hard_drive_confirm}');
	$link_hard_drive=$tpl->_ENGINE_parse_body('{link_hard_drive}');
	$html="
	var mem_dev='';
	var mem_vggroup='';
	
		function hdload(){
			YahooWin3('745','$page?display=yes','$title');
		
		}
		
		function vgmanage(dev){
			Loadjs('lvm.vg.php?dev='+dev);
		}
		
		function PartInfos(dev){
			mem_dev=dev;
			YahooWin4('800','$page?partinfos='+dev,dev);
			
		}
		
		var x_LVMVolumeGroupCreate= function (obj) {
			var response=obj.responseText;
			if(response.length>0){alert(response);}
			RefreshTab('partinfosdiv');
		}		
		
		function LVMVolumeGroupCreate(dev){
		var XHR = new XHRConnection();
				var group=prompt('$vgcreate_dev_text');
				if(group){
					XHR.appendData('vgcreatedev',dev);
					XHR.appendData('group',group);
					document.getElementById('lvcreategroupid').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
					XHR.sendAndLoad('$page', 'GET',x_LVMVolumeGroupCreate);
				}
			}		
		
		
		var x_ChangeLabel= function (obj) {
			var response=obj.responseText;
			if(response.length>0){alert(response);}
			PartInfos(mem_dev);
		}

		var x_LastLogsFormat= function (obj) {
				PartInfos(mem_dev);
				YahooWin5('600','$page?Lastlogs='+mem_dev,mem_dev);
		}			
		
		function ChangeLabel(dev){
			var label=prompt(dev+'\\n$change_label_text');
			if(label.length>0){
				var XHR = new XHRConnection();
				XHR.appendData('ChangeLabel',dev);
				XHR.appendData('label',label);
				document.getElementById('partitions').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
				XHR.sendAndLoad('$page', 'GET',x_ChangeLabel);
			}
		
		}
		
		function lvcreate(group){
			YahooWin5('400','$page?lvcreate='+group,'$ADD_VG::'+group);
		
		}
		
		function BuildBigPartition(dev){
			if(confirm(dev+'\\n$macro_build_bigpart_warning')){
				var label=prompt('$macro_build_bigpart_text');
				if(label.length>0){
					var XHR = new XHRConnection();
					XHR.appendData('BuildBigPartition',dev);
					XHR.appendData('label',label);
					document.getElementById('partitions').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
					XHR.sendAndLoad('$page', 'GET',x_LastLogsFormat);
				
				}
				
			}
		
		}
		
	var x_pvcreate_dev= function (obj) {
			var response=obj.responseText;
			if(response.length>0){alert(response);}
			PartInfos(mem_dev);	
		}			
		
	function pvcreate_dev(dev){
			if(confirm(dev+'\\n$macro_build_bigpart_warning')){
					var XHR = new XHRConnection();
					XHR.appendData('pvcreatedev',dev);
					document.getElementById('partitions').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
					XHR.sendAndLoad('$page', 'GET',x_pvcreate_dev);
				}
		}

	function vgcreate_dev(dev){
		var XHR = new XHRConnection();
		var group=prompt('$vgcreate_dev_text');
		if(group){
			XHR.appendData('vgcreatedev',dev);
			XHR.appendData('group',group);
			document.getElementById('partitions').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
			XHR.sendAndLoad('$page', 'GET',x_pvcreate_dev);
		}
	}
	
	var x_lvcreateSubmit= function (obj) {
			var response=obj.responseText;
			if(response.length>0){alert(response);}
			YahooWin5Hide();
			vg_refresh_list(mem_vggroup);
		}		
	
	function lvcreateSubmit(){
		var XHR = new XHRConnection();
		mem_vggroup=document.getElementById('LV_GROUP').value;
		XHR.appendData('LV_GROUP',mem_vggroup);
		XHR.appendData('LVMS_NAME',document.getElementById('LVMS_NAME').value);
		XHR.appendData('LVMS_SIZE',document.getElementById('LVMS_SIZE').value);
		XHR.appendData('affectToThisOu',document.getElementById('affectToThisOu').value);
		document.getElementById('lvmcreate').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_lvcreateSubmit);
	}
	
	function vg_refresh_list(vg_group){
		LoadAjax('lvg','system.internal.disks.php?lvg='+vg_group);
		
	}

		
		var x_SMARTDSlefttestShort= function (obj) {
			var response=obj.responseText;
			if(response.length>0){alert(response);}
			SMARTDStats(mem_dev)
		}	

		var x_EnableSmartctl= function (obj) {
			var response=obj.responseText;
			if(response.length>0){alert(response);}
			SMARTDIndex(mem_dev)
		}		
		
		function SMARTDSlefttestShort(dev){
			mem_dev=dev;
			var XHR = new XHRConnection();
			XHR.appendData('SMARTDSlefttestShort',dev);
			XHR.sendAndLoad('$page', 'GET',x_SMARTDSlefttestShort);
		}
		
		function EnableSmartctl(dev,mode){
			mem_dev=dev;
			var XHR = new XHRConnection();
			XHR.appendData('EnableSmartctl',dev);
			XHR.appendData('mode',mode);
			XHR.sendAndLoad('$page', 'GET',x_EnableSmartctl);
		}
		
		function SMARTDIndex(dev){
			YahooWin5(700,'$page?SMARTDIndex='+dev,'$APP_SMARTMONTOOLS '+dev);
		}
		
		function SMARTDStats(dev){
			LoadAjax('smartmon','$page?SMARTDStats='+dev);
		}
		
		function SMARTDInfo(dev){
			LoadAjax('smartmon','$page?SMARTDInfo='+dev);
		}
		
		function SMARTDAttrs(dev){
			LoadAjax('smartmon','$page?SMARTDAttrs='+dev);
		}
		
		var x_VGUnlinkDisk2= function (obj) {
			var response=obj.responseText;
			document.getElementById('lvm_status').innerHTML=response;	
			
			RefreshLVMDisk(mem_dev);
		}
		
		var x_VGUnlinkDisk= function (obj) {
			var response=obj.responseText;
			YahooWin6Hide();
			if(response.length>0){alert(response);}
			var XHR = new XHRConnection();
			XHR.appendData('lvm_status','yes');
			XHR.appendData('dev',mem_dev);
			XHR.sendAndLoad('$page', 'GET',x_VGUnlinkDisk2);		
			
			
		}	

		
		
		function VGUnlinkDisk(groupname,dev){
			if(confirm('$unlink_hard_drive_confirm')){
				var XHR = new XHRConnection();
				mem_dev=dev;
				XHR.appendData('VGUnlinkDisk','yes');
				XHR.appendData('groupname',groupname);
				XHR.appendData('dev',dev);
				XHR.sendAndLoad('$page', 'GET',x_VGUnlinkDisk);			
			
			}
		}
		
		function VGlinkDisk(groupname){
			YahooWin6(300,'$page?vgextend=yes&groupname='+groupname,'$link_hard_drive');
			
		}
		
		function RefreshLVMDisk(dev){
			LoadAjax('lvm_status','$page?lvm_status=yes&dev='+dev);
		}
	
	
		hdload();
	";
		
		echo $html;
	
}

function hd_list(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?usb-scan-write=yes");
	if(!file_exists('ressources/usb.scan.inc')){die('ressources/usb.scan.inc !!');}
	include_once 'ressources/usb.scan.inc';

	$d=$sock->getFrameWork("cmd.php?fdiskl=yes");
	$d=unserialize(base64_decode($d));

	
if(!is_array($_GLOBAL["disks_list"])){return null;}	

$artmp=$_GLOBAL["disks_list"];
while (list ($num, $line) = each ($d)){
	if(!is_array($_GLOBAL["disks_list"][$num])){
		$_GLOBAL["disks_list"][$num]=array("SIZE"=>$line,"ID_MODEL_1"=>"unknown");
	}
	
}


$html="<table style='width:99%'>";
$count=0;
	while (list ($num, $line) = each ($_GLOBAL["disks_list"])){
		
		if($count==2){$tr="</tr><tr>";$count=0;}else{$tr=null;}
		$html=$html . "$tr<td valign='top'>". ParseHDline($num,$line)."</td>";
		$count=$count+1;
		
		
	}

	$html=$html . "</table>";
	return $html;
	
}



function ParseHDline($dev,$array){
		$ID_MODEL=$array["ID_MODEL_1"];
		if($ID_MODEL==null){$ID_MODEL=$array["ID_MODEL_2"];}
		$SIZE=$array["SIZE"];
		$ID_BUS=$array["ID_BUS"];
		$ID_FS_LABEL=$array["ID_FS_LABEL"];
		$ID_VENDOR=$array["ID_VENDOR"];
		$title="$ID_FS_LABEL ($SIZE)";
		if(strlen($ID_MODEL)>14){$ID_MODEL=substr($ID_MODEL,0,11).'...';}
		$part_number=count($array["PARTITIONS"]);
		$tableau="
		<table style='width:99%'>
		<tr>
			<td class=legend>{path}:</td>
			<td><strong>$dev</strong></td>
		</tr>		
		<tr>
			<td class=legend>{model}:</td>
			<td><strong>$ID_MODEL</strong></td>
		</tr>
		<tr>
			<td class=legend nowrap>{vendor}:</td>
			<td><strong>$ID_VENDOR</strong></td>
		</tr>
		<tr>
			<td class=legend nowrap>Bus:</td>
			<td><strong>$ID_BUS</strong></td>
		</tr>
		<tr>
			<td class=legend nowrap>{partitions_number}:</td>
			<td><strong>$part_number</strong></td>
		</tr>		
							
		</table>
		
		";
		$link="javascript:PartInfos('$dev')";
		return ParagrapheSimple('64-hd.png',$title,$tableau,$link,null,290,null,1);
	
	
	}

function hd_index(){
	$tpl=new templates();
	$p=CurrentPageName();
	$hd_list="<div style='width:99%;height:550px;overflow:auto;' id='hd-display'>".hd_list()."</div>";
	$html="
	<H1>{internal_hard_drives}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=99% style='font-size:12px'>{internal_hard_drives_text}</td>
		<td>". imgtootltip("32-usb-refresh.png","{refresh}","LoadAjax('hd-display','$p?hd-display=yes')")."</td>
	</tr>
	</table>
	
	
	$hd_list
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function hd_partinfos(){
	$dev=$_GET["partinfos"];
	$tpl=new templates();
	$page=CurrentPageName();
	$array["status"]='{status}';
	
	
	$sock=new sockets();
	$a=unserialize(base64_decode($sock->getFrameWork('cmd.php?lvmdiskscan=yes')));
	if($a[$dev]<>null){$array["lvm"]='{virtual_disks}';}
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html[]= "<li><a href=\"$page?switchtab=$num&dev=$dev\"><span>$ligne</span></li>\n";
	
		
		
		//$html=$html . "<li><a href=\"javascript:LoadAjax('main_config_postfix','$page?main=$num&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	
	
	echo $tpl->_parse_body("
	<div id=partinfosdiv style='width:100%;height:430px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#partinfosdiv').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>");		
	
	
}

function hd_partinfos_switch(){
	
	if($_GET["switchtab"]=="status"){hd_partinfos_status();exit;}
	if($_GET["switchtab"]=="lvm"){echo "<div id='lvm_status'>";lvm_status();echo "</div>";exit;}
	
	
}

function hd_partinfos_toolbar(){
	$dev=$_GET["disk-toolbar-for"];
	$users=new usersMenus();
	$tpl=new templates();
	$sock=new sockets();
	if(!is_file('ressources/usb.scan.inc')){
		
		$sock->getFrameWork("cmd.php?usb-scan-write=yes");
	}	
	
	
	
	include_once 'ressources/usb.scan.inc';
	$array=$_GLOBAL["disks_list"][$dev];
	$mounted=$array["PARTITIONS"]["{$dev}1"]["MOUNTED"];
	
	
	$BuildBigParts="<td width=1%><img src='img/35/format.png'></td><td nowrap ". CellRollOver("BuildBigPartition('$dev')")."><strong>{macro_build_bigpart}</strong></td>";
	if($users->LVM_INSTALLED){
		$lvm_master="<td width=1%><img src='img/35/format-lvm.png'></td><td nowrap ". CellRollOver("pvcreate_dev('$dev')")."><strong>{macro_build_lvm}</strong></td>";
		$hash=unserialize(base64_decode($sock->getFrameWork("cmd.php?lvmdiskscan=yes")));
		if($hash[$dev]<>null){$lvm_master=null;}
		
	 }
	
	if(is_array($array["PARTITIONS"])){ 
	 	$fstab="<td width=1%><img src='img/fw_bold.gif'></td><td nowrap ". CellRollOver("Loadjs('fstab.php?dev=$dev');").">$CONNECT_HD</td>";
	 	
	}
				
	if($mounted=='/'){
		$BuildBigParts=null;
		$lvm_master=null;
	}
	$tpl=new templates();
	$html="<table style='width:100%'>
	$BuildBigParts
	$lvm_master
	</table>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}



function hd_partinfos_status(){
	$dev=$_GET["dev"];
	$users=new usersMenus();
	$tpl=new templates();
	if(!is_file('ressources/usb.scan.inc')){
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?usb-scan-write=yes");
	}
	
	$sock=new sockets();
	$lvmdisks=unserialize(base64_decode($sock->getFrameWork('cmd.php?lvmdiskscan=yes')));

include_once 'ressources/usb.scan.inc';
$array=$_GLOBAL["disks_list"][$dev];

$usb=new usb();

	if(is_array($lvm_dev["$dev"])){
		
		$lvm_dev["$dev"]["PSize"]=intval($lvm_dev["$dev"]["PSize"]);
		$lvm_dev["$dev"]["PFree"]=intval($lvm_dev["$dev"]["PFree"]);
		$used=$lvm_dev["$dev"]["PSize"]-$lvm_dev["$dev"]["PFree"];
		if($lvm_dev["$dev"]["PSize"]-$lvm_dev["$dev"]["PFree"]>0){
			$lvmreste=$lvm_dev["$dev"]["PSize"]-$lvm_dev["$dev"]["PFree"];
			
			$pourcent=round($lvmreste/$lvm_dev["$dev"]["PSize"],1)*100;
		}else{
			$pourcent=0;
		}
		$array["PARTITIONS"]=array("$dev"=>array(
					"TYPE"=>"LVM",
					"MOUNTED"=>"",
					"ID_FS_LABEL"=>"",
					"ID_FS_TYPE"=>"lvm2",
					"free_size"=>"{$lvm_dev["$dev"]["PSize"]};$used;{$lvm_dev["$dev"]["PFree"]};$pourcent",
					"SIZE"=>"{$lvm_dev["$dev"]["PSize"]} GB"));
		
	}
	
		$ID_MODEL=$array["ID_MODEL_1"];
		if($ID_MODEL==null){$ID_MODEL=$array["ID_MODEL_2"];}
		$SIZE=$array["SIZE"];
		$ID_BUS=$array["ID_BUS"];
		$ID_FS_LABEL=$array["ID_FS_LABEL"];
		$ID_VENDOR=$array["ID_VENDOR"];	
		
		$mounted=$_GLOBAL["disks_list"][$dev]["PARTITIONS"]["{$dev}1"]["MOUNTED"];	
		
//$img=hd_partinfos_graphic($dev);
$array=hd_partitions_scan($array["PARTITIONS"]);
// 119=0% et 0=100%
if(is_array($array)){
	$partitions="<table style='width:100%'>
		<tr>
		<th width=1%>&nbsp;</th>
		<th><strong style='font-size:12px'>{disk}</th>
		<th><strong style='font-size:12px' valign='middle'>&nbsp;</th>
		<th><strong style='font-size:12px' width=1% nowrap>{used}</th>
		<th><strong style='font-size:12px' width=1% nowrap>{size}</th>
		<th width=1% nowrap><strong style='font-size:12px' >{mounted}</th>
		<th><strong style='font-size:12px' width=1% nowrap>{type}</th>
		</tr>";
	
	while (list ($num, $line) = each ($array)){
		if($num==null){continue;}
		$perc=pourcentage($line["POURC"]);
		
		$MOUNTED=$line["MOUNTED"];
		if($line["USED"]==null){$line["USED"]=0;}
		if($line["SIZE"]==null){$line["SIZE"]=0;}
		if($line["TYPE"]==5){
			$perc="-";
		}
		if($line["TYPE"]==82){
			$perc="-";
		}	
		
		
		$mounted_align="left";	
		
		$icon="<img src='img/48-hd.png'>";
		
		if($users->LVM_INSTALLED){
		
				if($lvmdisks[$num]<>null){
					$line["SIZE"]=$lvmdisks[$num];
					$MOUNTED=imgtootltip("35/format-lvm.png","{APP_LVM}","PartInfos('$num')");
					$icon=imgtootltip("48-hd.png","{partition_infos}","PartInfos('$num')");
					$mounted_align="center";
				}else{
					if($line["TYPE"]=="8e"){
						$MOUNTED=imgtootltip("35/format-lvm.png","{APP_LVM}","PartInfos('$num')");
						$icon=imgtootltip("48-hd.png","{partition_infos}","PartInfos('$num')");
						$mounted_align="center";
					}
				}
				
				if(($MOUNTED==null) && ($line["TYPE"]==83) && ($line["USED"]==0)){
					$MOUNTED=imgtootltip("35/format-lvm.png","{APP_LVM}","PartInfos('$num')");
					$icon=imgtootltip("48-hd.png","{partition_infos}","PartInfos('$num')");
					$mounted_align="center";
					
				}
				
				if($line["TYPE"]==5){
					$icon="<img src='img/48-hd.png'>";
					$MOUNTED="&nbsp;";
				}
		
		}
		
		
		
		
		$partitions=$partitions . "
		
		<tr>
		<td width=1%>$icon</td>
		<td><strong style='font-size:12px'>". basename($num)."</td>
		<td valign='middle'><strong style='font-size:12px' >$perc</td>
		<td width=1%  nowrap align='right'><strong style='font-size:12px' >{$line["USED"]}</td>
		<td width=1% nowrap align='right'><strong style='font-size:12px' >{$line["SIZE"]}</td>
		<td width=1% nowrap align='$mounted_align'><strong style='font-size:12px' >$MOUNTED</td>
		<td width=1% nowrap><strong style='font-size:12px' >{$usb->getPartypename($line["TYPE"])} ({$line["TYPE"]})</td>
		</tr>
		<tr>
			<td colspan=7><hr></td>
		</tr>
		
		";
		
	}
	$partitions=$partitions."</table>";
	
}

if($SIZE==null){
	$sock=new sockets();
	$fdisk=unserialize(base64_decode($sock->getFrameWork("cmd.php?fdiskl=yes")));
	$SIZE=$fdisk["$dev"];
}

if($ID_BUS==NULL){$ID_BUS=0;}
if($ID_MODEL==NULL){$ID_MODEL="unknown";}
$page=CurrentPageName();
if($ID_FS_LABEL==null){$ID_FS_LABEL=$dev;}

$title="<H1>{partitions}:$ID_FS_LABEL ($SIZE)</H1>
<div style='text-align:right;margin-top:-10px'><strong style='font-size:14px'>{model}:$ID_BUS:: $ID_MODEL ($ID_VENDOR)</strong></div>
<table style='width:100%'>
<tr>
	<td valign='top'>
			<div id='toolbar-disks'></div>
		</td>
</tr>
	<tr>
		<td valign='top'>
			<div id='partitions'>
			$partitions
			</div>
		</td>
		
	</tr>
</table>
<script>
	LoadAjax('toolbar-disks','$page?disk-toolbar-for=$dev');
</script>
";

$html="$title";

echo $tpl->_ENGINE_parse_body($html);
}


	
	
function hd_partitions_scan($array){
	
	if(!is_array($array)){return null;}
	$os=new os_system();
	$users=new usersMenus();
	$disk_type_array=$os->disk_type_array();
	
if(!is_file('ressources/usb.scan.inc')){
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?usb-scan-write=yes");
	}
	include('ressources/usb.scan.inc');	
	
	
	
	while (list ($num, $line) = each ($array)){
		$dev_path=$num;
		$TYPE=$disk_type_array[$line["TYPE"]];
		$MOUNTED=$line["MOUNTED"];
		$ID_FS_TYPE=$line["ID_FS_TYPE"];
		$TOTSIZE=$line["SIZE"];
		$free_size=explode(";",$line["free_size"]);
		$bigsize=$free_size[0];
		$used=$free_size[1];
		$free=$free_size[2];
		$pourcent=$free_size[3];
		$ID_FS_LABEL=$line["ID_FS_LABEL"];
		
		$text="$used/$bigsize&nbsp;$pourcent% {used}";
		if(trim($pourcent)==null){$pourcent="0%";$text=null;}
		$first_div_size=$bigsize;
		
		if($TOTSIZE>1000){$TOTSIZE=round($TOTSIZE/1000)."GB";}
		
		$PART_ARRAY["$dev_path"]["TEXT_SIZE"]=$text;
		$PART_ARRAY["$dev_path"]["POURC"]=$pourcent;
		$PART_ARRAY["$dev_path"]["MOUNTED"]=$MOUNTED;
		$PART_ARRAY["$dev_path"]["SIZE"]=$TOTSIZE;
		$PART_ARRAY["$dev_path"]["TYPE"]=$line["TYPE"];
		$PART_ARRAY["$dev_path"]["USED"]=$used;
		
		if(($line["TYPE"]<>5) AND ($line["TYPE"]<>82)){
			$PART_ARRAY["$dev_path"]["CHANGE_LABEL"]=true;
			$PART_ARRAY["$dev_path"]["CHANGE_LABEL_JS"]="ChangeLabel('$dev_path')";
			if($users->LVM_INSTALLED){	
				$PART_ARRAY["$dev_path"]["PVCREATE"]=true;
				$PART_ARRAY["$dev_path"]["PVCREATE_JS"]="pvcreate_dev('$dev_path')";
			}
		}
	}

	return $PART_ARRAY;
	
}

function lvm_vgextend_popup(){
	$groupname=$_GET["groupname"];
	$sock=new sockets();	
	$ar=unserialize(base64_decode($sock->getFrameWork("cmd.php?vg-disks=yes")));	
	$disks=$ar[$groupname];
	$tpl=new templates();
	$page=CurrentPageName();
	$link_hard_drive_confirm=$tpl->javascript_parse_text("{link_hard_drive_confirm}");
	if(is_array($disks)){
		while (list ($index, $array) = each ($disks)){
			$array[0]=trim($array[0]);
			$exists[$array[0]]=true;
		}
	}
	
	$lvmdiskscan=unserialize(base64_decode($sock->getFrameWork("cmd.php?lvmdiskscan=yes")));
	if(is_array($lvmdiskscan)){
		while (list ($dev, $size) = each ($lvmdiskscan)){
			if(!$exists[$dev]){
				$free[$dev]=$size;
			}
		}
	}
	
	
	if(is_array($free)){
		
		$table="<table class=table_form>";
		while (list ($dev, $size) = each ($free)){
			$table=$table."
			<tr ". CellRollOver("VgExtendPerform('$dev')","{link_hard_drive}").">
				<td width=1%><img src='img/usb-disk-64-2.png'></td>
				<td><strong style='font-size:12px'>$dev</strong>
				<td><strong style='font-size:12px'>$size</strong>
			</tr>
			";
			
		}
	$table=$table."</table>";}
	
	
	$html="<H1>{link_hard_drive}</H1>
	<p style='font-size:12px'>{vgextend_explain}</p>
	<div id='vgextenddiv'>
	$table
	</div>
	
	<script>

		function VgExtendPerform(dev){
			if(confirm('$link_hard_drive_confirm')){
				var XHR = new XHRConnection();
				mem_dev=dev;
				XHR.appendData('VGlinkDisk','yes');
				XHR.appendData('groupname','$groupname');
				XHR.appendData('dev',dev);
				document.getElementById('vgextenddiv').innerHTML='<div style=\"width:100%;height:300px:overflow:auto;background-color:white\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
				XHR.sendAndLoad('$page', 'GET',x_VGUnlinkDisk);			
			
			}		
		}
	</script>
	";
	

	echo $tpl->_ENGINE_parse_body($html);
	
}

function partitions_scan($array){
	if(!is_array($array)){return null;}
	$os=new os_system();
	$users=new usersMenus();
	$disk_type_array=$os->disk_type_array();
	
if(!is_file('ressources/usb.scan.inc')){
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?usb-scan-write=yes");
	}
	include('ressources/usb.scan.inc');	
	
	
	
	while (list ($num, $line) = each ($array)){
		$dev_path=$num;
		$TYPE=$disk_type_array[$line["TYPE"]];
		$MOUNTED=$line["MOUNTED"];
		$ID_FS_TYPE=$line["ID_FS_TYPE"];
		$TOTSIZE=$line["SIZE"];
		$free_size=explode(";",$line["free_size"]);
		$bigsize=$free_size[0];
		$used=$free_size[1];
		$free=$free_size[2];
		$pourcent=$free_size[3];
		$ID_FS_LABEL=$line["ID_FS_LABEL"];
		
		$text="$used/$bigsize&nbsp;$pourcent% {used}";
		if(trim($pourcent)==null){$pourcent="0%";$text=null;}
		$first_div_size=$bigsize;
		
		$PART_ARRAY["$dev_path"]["TEXT_SIZE"]=$text;
		$PART_ARRAY["$dev_path"]["POURC"]=$pourcent;
		
	
		
		if(($line["TYPE"]<>5) AND ($line["TYPE"]<>82)){
			$PART_ARRAY["$dev_path"]["CHANGE_LABEL"]=true;
			$PART_ARRAY["$dev_path"]["CHANGE_LABEL_JS"]="ChangeLabel('$dev_path')";
			$PART_ARRAY["$dev_path"]["SIZE"]=$TOTSIZE;
			
			
		
		if($users->LVM_INSTALLED){
			
			$PART_ARRAY["$dev_path"]["PVCREATE"]=true;
			$PART_ARRAY["$dev_path"]["PVCREATE_JS"]="pvcreate_dev('$dev_path')";
			$partitions_options="<table style='width:100%'><tr>";
			
			
			
			$init="
			<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td ". CellRollOver("pvcreate_dev('$dev_path')").">{pvcreate_dev}</td>
			</tr>";
			
			
			if(is_array($lvm_dev[$dev_path])){
				$init=null;
				if($lvm_dev["$dev_path"]["GROUP"]==null){
					$create_group="
					<tr>
					<td width=1%><img src='img/fw_bold.gif'></td>
					<td ". CellRollOver("vgcreate_dev('$dev_path')").">{vgcreate_dev}</td>
					</tr>";
				}else{
					$create_group="
					<tr>
					<td colspan=2><hr><span style='font-size:14px'>{APP_LVM}</td></td>
					</tr>
					<tr><td colspan=2>&nbsp;</td></tr>
					<tr>
					<td width=1%><img src='img/mailbox_hd.gif'></td>
					<td ". CellRollOver("vg_refresh_list('{$lvm_dev["$dev_path"]["GROUP"]}')")." style='font-size:13px'>{vgmanage}:{$lvm_dev["$dev_path"]["GROUP"]}</td>
					</tr>
					<tr>
						<td colspan=2><div id='LIST_{$lvm_dev["$dev_path"]["GROUP"]}'>".lv_groups_list($lvm_dev["$dev_path"]["GROUP"])."</div></td>
					</tr>";
					
				}
			}
			
			$partitions_options=$partitions_options.$init.$create_group;
			$partitions_options=$partitions_options."</table>";
			}
		

		
	$pourcent_pix=$pourcent*3;	
	$html=$html . "
	<table style='width:100%' class=table_form>
		<td valign='top'>
				<table style='width:100%'>
					<tr>
						<td>
						<strong style='font-size:16px'>$ID_FS_LABEL $MOUNTED</strong><br>
						<span style='font-size:15px;font-weight:bold'>$TYPE - {$line["TYPE"]} ($TOTSIZE)&nbsp;&laquo;&nbsp;$dev_path&nbsp;&raquo;</span>
						</td>
					</tr>
					<tr>
						<td>
							<div style='width:300px;border:1px solid #CCCCCC;height:30px;background-color:#00E100'>
								<div style='float:right;color:white;padding:5px;font-size:13px;font-weight:bold'>$text</div>
								<div style='width:$pourcent_pix;background-color:#D20B2A;height:30px'>&nbsp;</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							$partitions_options
						</td>
					</tr>
					
					
				</table>
			</td>
			<td valign='top'>
				<table style='width:100%' class=table_form>
					$option_label
					$option_macro_create
				</table>
			</td>
		</tr>
	</table>
	
	";
		
		
	}
	return $html;
	
	
}
}

function vg_disks($groupname){
$sock=new sockets();	
$ar=unserialize(base64_decode($sock->getFrameWork("cmd.php?vg-disks=yes")));	
$disks=$ar[$groupname];

while (list ($index, $array) = each ($disks)){
 $array[0]=trim($array[0]);
 $table1=$table1."<td align='center'>". imgtootltip('usb-disk-64-2-del.png',"{unlink}","VGUnlinkDisk('$groupname','{$array[0]}')")."</td>";
 $table2=$table2."<TD nowrap><strong>".basename($array[0]). "&nbsp;({$array[1]}G)</strong></td>";
 
}
 $table1=$table1."<td align='center'>". imgtootltip('usb-disk-64-2-add.png',"{link_hard_drive}","VGlinkDisk('$groupname')")."</td>";
 $table2=$table2."<TD nowrap><strong>{link_hard_drive}</strong></td>";
 $table1=$table1."<td align='center'>". imgtootltip('database-48-add.png',"{ADD_VG_TEXT}","lvcreate('$groupname')")."</td>";
 $table2=$table2."<TD nowrap><strong>{ADD_VG}</strong></td>";
 

$html="<table><tr>$table1</tr><tr>$table2</tr></table>";
return $html;
	
}

function lvm_unlink_disk(){
	$groupname=$_GET["groupname"];
	$dev=$_GET["dev"];
	$sock=new sockets();
	echo(implode("\n",unserialize(base64_decode($sock->getFrameWork("cmd.php?lvm-unlink-disk=yes&dev=$dev&groupname=$groupname")))));	
	
}
function lvm_link_disk(){
	$groupname=$_GET["groupname"];
	$dev=$_GET["dev"];
	$sock=new sockets();
	echo(implode("\n",unserialize(base64_decode($sock->getFrameWork("cmd.php?lvm-link-disk=yes&dev=$dev&groupname=$groupname")))));	
	
}


function lvm_status(){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?pvscan=yes")));
	
	$LVM_AR=$array[$_GET["dev"]];
	$group_name=$LVM_AR["VG"];
	if($group_name<>null){
		$VG_INFO=unserialize(base64_decode($sock->getFrameWork("cmd.php?vgs-info=$group_name")));
		$disks=vg_disks($group_name);
		}else{
			$disks="
			<div id='lvcreategroupid'>
			
			<center>
			<hr>
				<div style='font-size:16px'>{vgcreate_dev}</div>
			".imgtootltip("rename-disk-64.png","{vgcreate_dev_explain}","LVMVolumeGroupCreate('{$_GET["dev"]}')")."
			</center>
			<hr>
			</div>";
		}

	$add="
	<tr>
		<td width=1%><img src='img/add-database-32.png'></td>
		<td ". CellRollOver("lvcreate('$group_name')","{ADD_VG_TEXT}").">{ADD_VG}</td>
	</tr>
	
	";
	
	$html="
	<table style='width:100%'>
		<td width=99%><H1>$group_name&nbsp;&raquo;&raquo;{APP_LVMS} {size}:&nbsp;{$VG_INFO["$group_name"]["SIZE"]}</H1></td>
		<td width=1%>". imgtootltip("64-usb-refresh.png","{refresh}","RefreshLVMDisk('{$_GET["dev"]}')")."</td>
	</tr>
	</table>
	$disks
	<table style='width:100%'>
		<tr>
			<td valign='top' width=99%>
				<div id='lvg' style='width:100%;height:450px;overflow:auto'>
					". lv_groups_list($group_name)."
				</div>
			</td>
			
		</tr>
	</table>
	
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
}


function lv_groups_list($group){
		if($group==null){return null;}
		$sock=new sockets();
		$datas=$sock->getFrameWork("cmd.php?lvm-lvs=$group");
		$array=unserialize(base64_decode($datas));
		$all_lvs=unserialize(base64_decode($sock->getFrameWork("cmd.php?lvs-all=yes")));
		
		print_r($all_vgs);
	include('ressources/usb.scan.inc');	
	$html="
	<br>
	<div style='width:100%;height:250px;overflow:auto'>
	<table style='width:100%'>";
	
	
if(is_array($array)){
		$partitions="<table style='width:100%'>
		<tr>
		<th width=1%>&nbsp;</th>
		<th><strong style='font-size:12px'>{disk}</th>
		<th><strong style='font-size:12px' valign='middle'>&nbsp;</th>
		<th><strong style='font-size:12px' width=1% nowrap>{used}</th>
		<th><strong style='font-size:12px' width=1% nowrap>{size}</th>
		<th width=1% nowrap><strong style='font-size:12px' >{mounted}</th>
		<th>&nbsp;</th>
		
		</tr>";
	
	while (list ($name, $size) = each ($array)){
		if($name==null){continue;}
		
			$count=$count+1;
			$size=str_replace('.00','',$size);
			$dev="/dev/$group/$name";
			$name_mapper=str_replace("-","--",$name);
			$mapper="/dev/mapper/$group-$name_mapper";
			$diskInfos=unserialize(base64_decode($sock->getFrameWork("cmd.php?DiskInfos=$mapper")));
			
			$js="vgmanage('$dev')";		
		
		$perc=pourcentage($diskInfos["POURC"]);
		
		$MOUNTED=$diskInfos["MOUNTED"];
		if($diskInfos["USED"]==null){$diskInfos["USED"]=0;}
		$SIZE=$all_lvs[$name]["SIZE"];
		$delete=imgtootltip("database-48-delete.png","{delete}","Loadjs('lvm.vg.php?lvremove-js=$mapper&group_name=$group')");
		$show=imgtootltip("database-48.png","{view}",$js);
		
		
		
		//lvremove('/dev/mapper/internet_backup-toto');
		
		$partitions=$partitions . "
		
		<tr>
		<td width=1%>$show</td>
		<td><strong style='font-size:12px'>$name</td>
		<td valign='middle'><strong style='font-size:12px' >$perc</td>
		<td width=1%  nowrap align='right'><strong style='font-size:12px' >{$diskInfos["USED"]}</td>
		<td width=1% nowrap align='right'><strong style='font-size:12px' >$SIZE</td>
		<td width=1% nowrap><strong style='font-size:12px'>$MOUNTED</td>
		<td width=1% nowrap><strong style='font-size:12px'>$delete</td>
		
		</tr>
		<tr>
			<td colspan=7><hr></td>
		</tr>
		
		";
		
	}
	$partitions=$partitions."</table>";
	
}
	

	
	$tpl=new templates();
	$html=RoundedLightWhite($partitions);
	return $tpl->_ENGINE_parse_body($html);
	
}

function ChangeLabel(){
	$label=$_GET["label"];
	$label=substr($label,0,16);
	$label=trim($label);
	$label=replace_accents($label);
	$sock=new sockets();
	$sock->getfile("ChangeDiskLabel:{$_GET["ChangeLabel"]};$label");
	}
	
function BuildBigPartition(){
	$dev=$_GET["BuildBigPartition"];
	$label=$_GET["label"];
	$label=substr($label,0,16);
	$label=trim($label);
	$label=replace_accents($label);
	$sock=new sockets();
	
	//--format-b-part
	$datas=base64_decode($sock->getFrameWork("cmd.php?fdisk-build-big-partitions=yes&dev=$dev&label=$label"));
	file_put_contents("ressources/logs/BuildUniquePartition_".md5($dev),$datas);
	}
	
function Lastlogs(){
	$dev=$_GET["Lastlogs"];
	$datas=file_get_contents("ressources/logs/BuildUniquePartition_". md5($dev));
	$datas=htmlspecialchars($datas);
		$datas=str_replace("\n\n","<br>",$datas);
	
	$html="
	<H1>$dev {events}</H1>
	".RoundedLightWhite("
	<div style='width:100%;height:300px;overflow:auto'>$datas</div>");
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function SMARTDIndex(){
	$dev=$_GET["SMARTDIndex"];
	$SMARTDIndexInfos=SMARTDIndexInfos($dev,1);
	
	
	$info=ParagrapheSimple("64-hd.png","{HD_INFOS}","{HD_INFOS_TEXT}","javascript:SMARTDInfo('$dev');");
	$stats=ParagrapheSimple("64-hd-stats.png","{HD_STATISTICS}","{HD_STATISTICS_TEXT}","javascript:SMARTDStats('$dev');");
	$attrs=ParagrapheSimple("64-hd-attrs.png","{HD_ATTRIBUTES}","{HD_ATTRIBUTES_TEXT}","javascript:SMARTDAttrs('$dev');");
	
	
	$enable="<input type='button' OnClick=\"javascript:EnableSmartctl('$dev','on');\" value='&laquo;&nbsp;{enable_monitoring}'>";
	$disbale="<input type='button' OnClick=\"javascript:EnableSmartctl('$dev','off');\" value='{disable_monitoring}&nbsp;&raquo;'>";
	
	$html="<H1>$dev</H1>
	
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'>$info$stats$attrs</td>
	<td width=100% valign='top'>
		<div id='smartmon' style='width:450px;height:400px;overflow:auto'>
		$SMARTDIndexInfos
		</div>
		<br>
		<table style='width:100%' class=table_form>
		<tr>
		<td align='left'>$enable</td>
		<td align='right'>$disbale</td>
		</tr>
		</table>
	</td>
	</tr>
	</table>
	
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
	
}

function SMARTDAttrs(){
	$dev=$_GET["SMARTDAttrs"];
	$smart=new smartd($dev);	
$array=$smart->disk_attributes;

if(is_array($array)){
		$info="
		
		<table style='width:99%'>
		<th>#</th>
		<th>{ATTRIBUTE_NAME}</th>
		<th>{FLAG}</th>
		<th>{WORST}</th>
		<th>{THRESH}</th>
		<th>{type}</th>
		<th>{UPDATED}</th>
		<th>{WHEN_FAILED}</th>
		<th>{RAW_VALUE}</th>
		</tr>
		";
		while (list ($num, $val) = each ($array) ){
			$info=$info."
			<tr>
				<td class=legend nowrap valign='top'>$num</td>
				<td style='font-weight:bold'><code style='font-size:9px'>{$val["ATTRIBUTE_NAME"]}</code></td>
				<td style='font-weight:bold' align='center'><code style='font-size:9px'>{$val["FLAG"]}</code></td>
				<td style='font-weight:bold'><code style='font-size:9px'>{$val["WORST"]}</code></td>
				<td style='font-weight:bold'><code style='font-size:9px'>{$val["THRESH"]}</code></td>
				<td style='font-weight:bold'><code style='font-size:9px'>{$val["TYPE"]}</code></td>	
				<td style='font-weight:bold'><code style='font-size:9px'>{$val["UPDATED"]}</code></td>	
				<td style='font-weight:bold'><code style='font-size:9px'>{$val["WHEN_FAILED"]}</code></td>
				<td style='font-weight:bold'><code style='font-size:9px'>{$val["RAW_VALUE"]}</code></td>			
			</tr>";
			
		}
	$info=$info."</table>";
	}
	$info=RoundedLightWhite($info);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($info);		
}

function SMARTDStats(){
	$dev=$_GET["SMARTDStats"];
	$smart=new smartd($dev);	
	$array=$smart->diskstats_array;

	$head="
		<table style='width:100%' class=table_form>
		<tr>
		<td>" . imgtootltip("ico2672.png","{run_shortselftestroutine}","SMARTDSlefttestShort('$dev')")."</td>
		<td>" . texttooltip("{run_shortselftestroutine}",'{run_shortselftestroutine}',"SMARTDSlefttestShort('$dev')")."</td>
		</tr>
		</table>
		";


if(is_array($array)){
		$info="
		
		<table style='width:100%'>
		<tr>
			<td colspan=5 align='right'>" . imgtootltip('refresh-24.png','{refresh}',"SMARTDStats('$dev')")."</td>
		<tr>
		<th>#</th>
		<th>{status}</th>
		<th>{remaining}</th>
		<th>{lifetime}</th>
		<th>{error}</th>
		</tr>
		";
		while (list ($num, $val) = each ($array) ){
			$info=$info."
			<tr>
				<td class=legend nowrap valign='top'>$num</td>
				<td style='font-weight:bold'><code style='font-size:9px'>{$val["status"]}</code></td>
				<td style='font-weight:bold' align='center'><code>{$val["remaining"]}%</code></td>
				<td style='font-weight:bold'><code>{$val["lifetime"]}</code></td>
				<td style='font-weight:bold'><code>{$val["lba_of_first_error"]}</code></td>	
			</tr>";
			
		}
	$info=$info."</table>
	<div style='text-align:right;font-weight:bolder;border-top:1px dotted #CCCCCC;margin:4px' class=caption>{lifetime}={hours}</div>
	
	
	";}
	$info=$head."<br>".RoundedLightWhite($info);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($info);		
}

function SMARTDIndexInfos($dev,$return=0){
$smart=new smartd($dev);
	
	if(is_array($smart->diskinfos_array)){
		$info="<table style='width:100%'>";
		while (list ($num, $val) = each ($smart->diskinfos_array) ){
			
			$val=str_replace("Enabled","<strong style='color:red'>{enabled}</strong>",$val);
			$val=str_replace("Disabled","<strong style='color:red'>{disabled}</strong>",$val);
			$info=$info."
			<tr>
				<td class=legend nowrap valign='top'>$num</td>
				<td style='font-weight:bold'><code>$val</code></td>
			</tr>";
			
		}
	$info=$info."</table>";}
	
	$info=RoundedLightWhite($info);
	if($return==1){return $info;}
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($info);	
	
}

function SMARTDTestShort(){
	$dev=$_GET["SMARTDSlefttestShort"];
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{run_shortselftestroutine_text}");
	$sock=new sockets();
	$sock->getfile("SMARTDSlefttestShort:$dev");
	echo $title;
	
}

function SMARTDEnable(){
	$dev=$_GET["EnableSmartctl"];
	$sock=new sockets();
	if($_GET["mode"]=="on"){
		echo $sock->getfile("SMARTDEnable:$dev");
		exit;
	}
	
	if($_GET["mode"]=="off"){
		echo $sock->getfile("SMARTDDisable:$dev");
		exit;
	}	
	
}

function lvm_pvcreate_dev(){
	$dev=$_GET["pvcreatedev"];
	$sock=new sockets();
	$tpl=new templates();
	///usr/share/artica-install --pvcreate-dev
	$datas=explode("\n",$sock->getfile("pvcreate:$dev"));
	while (list ($num, $val) = each ($datas)){
		if($val==null){continue;}
		echo html_entity_decode(trim($tpl->_ENGINE_parse_body($val)))."\n";
	}
	$sock->getFrameWork("cmd.php?usb-scan-write=yes");
	
	
}

function lvm_lvcreate(){
	$group=$_GET["lvcreate"];
	$ldap=new clladp();
	$ous=$ldap->hash_get_ou(true);
	$ous[null]="{select}";
	$field=Field_array_Hash($ous,"affectToThisOu",$ou);
	
	
	$html="<h1>{ADD_VG}</H1>
	<p class=caption>{ADD_VG_TEXT}</p>
	<input type='hidden' id='LV_GROUP' value='$group'>
	". RoundedLightWhite("
	<div id='lvmcreate'>
	<table style='width:100%'>
	<tr>
		<td class=legend nowrap>{LVMS_NAME}:</td>
		<td>" . Field_text('LVMS_NAME',null,'width:90px')."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{LVMS_SIZE}:</td>
		<td>" . Field_text('LVMS_SIZE',null,'width:30px')."<strong style='font-size:12px'>&nbsp;G</strong></td>
	</tr>
	<tr>
		<td colspan=2 style='font-size:11px'><br>{AFFECT_VG_EXPLAIN}</td>
	</tr>
	<tr>
		<td class=legend>{organization}:</td>
		<td>$field</td>
	</tr>		
	<tr>
		<td colspan='2' align='right'><hr>". button("{add}","lvcreateSubmit();")."</td>
	</tr>
	</table>
	
	</div>");
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
		
}

function lvm_lvcreate_save(){
	$sock=new sockets();
	$tpl=new templates();
	$_GET["LVMS_NAME"]=trim(strtolower($_GET["LVMS_NAME"]));
	$_GET["LVMS_NAME"]=str_replace(" ","-",$_GET["LVMS_NAME"]);
	$_GET["LVMS_NAME"]=str_replace("-","_",$_GET["LVMS_NAME"]);
	$data=$sock->getfile("lvcreate:{$_GET["LV_GROUP"]};{$_GET["LVMS_NAME"]};{$_GET["LVMS_SIZE"]}");
	$datas=explode("\n",$data);
	$ou=$_GET["affectToThisOu"];
	if($ou<>null){
		
		$dev="/dev/{$_GET["LV_GROUP"]}/{$_GET["LVMS_NAME"]}";
		writelogs("Create dev: $dev",__FUNCTION__,__FILE__,__LINE__);
		
		$lvm=new lvm_org($ou);
		writelogs("affect dev: $dev -> $ou",__FUNCTION__,__FILE__,__LINE__);
		$lvm->AffectDev($dev);
		$mount_point="/media/$ou/{$_GET["LVMS_NAME"]}";
		writelogs("mount point $mount_point",__FUNCTION__,__FILE__,__LINE__);
		$datas[]="$dev will be formated";
		writelogs("format",__FUNCTION__,__FILE__,__LINE__);
		$sock->getFrameWork("cmd.php?format-disk-unix=$dev");
		$datas[]="$dev will be mounted to $mount_point";
		writelogs("-> fstab",__FUNCTION__,__FILE__,__LINE__);
		$sock->getFrameWork("cmd.php?fstab-add=yes&dev=$dev&mount=$mount_point");
		}

	if(!is_array($datas)){return null;}	
	while (list ($num, $val) = each ($datas)){
		if($val==null){continue;}
		echo html_entity_decode(trim($tpl->_ENGINE_parse_body($val)))."\n";
	}
	$sock->getFrameWork("cmd.php?usb-scan-write=yes");	
}

function lvm_vgcreate_dev(){
	$dev=$_GET["vgcreatedev"];
	$group=trim($_GET["group"]);
	$group=str_replace(" ","_",$group);
	$group=str_replace("-","_",$group);
	$group=strtolower($group);
	$sock=new sockets();
	$tpl=new templates();
	///usr/share/artica-install --vgcreate-dev /dev/sdb1 groupname
	///usr/share/artica-postfix/bin/artica-install --vgcreate-dev '+RegExpr.Match[1] +' "' + RegExpr.Match[2]+'" --verbose >'+tmpstr+' 2>&1'
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?vgcreate-dev=yes&dev=$dev&groupname=$group")));
	
	while (list ($num, $val) = each ($datas)){
		if($val==null){continue;}
		echo html_entity_decode(trim($tpl->_ENGINE_parse_body($val)))."\n";
	}
	$sock->getFrameWork("cmd.php?usb-scan-write=yes");
	
	
}


function hd_partinfos_graphic($dev){
	
$f_name="hd-". md5($dev).".png";
$fileName = dirname(__FILE__)."/ressources/logs/$f_name";
@unlink($fileName);

	$ydata[]=$ligne["tcount"];
	$xdata[] =$ligne["sitename"] ." ". $ligne["tcount"];
	
	
	


$width = 700; $height = 200;
$graph = new PieGraph($width,$height);
$graph->title->Set("$dev");
$p1 = new PiePlot3D($ydata);
$p1->SetLegends($xdata);
$p1->ExplodeSlice(1);


$graph->Add($p1);
$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
$graph->img->Stream($fileName);
return "ressources/logs/$f_name";
}


	
?>

