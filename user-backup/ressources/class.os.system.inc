<?php
include_once(dirname(__FILE__).'/class.templates.inc');
include_once(dirname(__FILE__).'/class.users.menus.inc');
include_once(dirname(__FILE__).'/class.sockets.inc');






class usb{
	var $uuid;
	var $path;
	var $LABEL;
	var $TYPE;
	var $SEC_TYPE;
	var $mounted;
	var $vendor;
	var $model;
	var $product;
	var $manufacturer;
	var $id;
	var $speed;
	var $size;
	var $pourc;
	var $ID_MODEL;
	var $ID_VENDOR;
	var $img;
	var $ID_FS_TYPE;
	
public function usb($uuid=null){
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?usb-scan-write=yes");
		if($uuid<>null){
			$this->uuid=$uuid;
			$this->lOADINFOS();
		}
		
		
	}
	
public function HASH_UUID_LIST(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?usb-scan-write=yes");
	$tpl=new templates();
	if(!file_exists('ressources/usb.scan.inc')){return array();}		
	include(dirname(__FILE__)."/usb.scan.inc");	
	while (list ($uid, $array) = each ($_GLOBAL["usb_list"]) ){
		if(preg_match('#swap#',$array["TYPE"])){continue;}
		if(trim($array["mounted"])=='/'){continue;}
		$VENDOR=$array["ID_VENDOR"];
		if($mounted=="/"){continue;}
		if($TYPE=="swap"){continue;}
		$UUID=$array["UUID"];
		if($UUID==null){continue;}
		$TYPE=$array["TYPE"];
		$ID_MODEL=$array["ID_MODEL"];
		$ss=explode(";",$array["SIZE"]);
		$ARR[$UUID]="{$ss[0]}: $ID_MODEL ($TYPE)";
	}	
	
	return $ARR;
}
	
	
function getPartypename($id){
		if(strlen($id)==1){$id="0$id";}
		$part_type["01"]="DOS 12-bit fat";
		$part_type["02"]="XENIX root";
		$part_type["03"]="XENIX /usr";
		$part_type["04"]="DOS 3.0+ 16-bit FAT (up to 32M)";
		$part_type["05"]="DOS 3.3+ Extended Partition";
		$part_type["06"]="DOS 3.31+ 16-bit FAT (over 32M)";
		$part_type["07"]="OS/2 IFS (e.g., HPFS)";
		$part_type["07"]="Advanced Unix";
		$part_type["07"]="Windows NT NTFS";
		$part_type["07"]="QNX2.x (pre-1988)";
		$part_type["08"]="OS/2 (v1.0-1.3 only)";
		$part_type["08"]="AIX boot partition";
		$part_type["08"]="SplitDrive";
		$part_type["08"]="DELL partition spanning multiple drives";
		$part_type["08"]="Commodore DOS";
		$part_type["08"]="QNX 1.x and 2.x (qny)";
		$part_type["09"]="AIX data partition";
		$part_type["09"]="Coherent filesystem";
		$part_type["09"]="QNX 1.x and 2.x (qnz)";
		$part_type["0a"]="OS/2 Boot Manager";
		$part_type["0a"]="Coherent swap partition";
		$part_type["0a"]="OPUS";
		$part_type["0b"]="WIN95 OSR2 32-bit FAT";
		$part_type["0c"]="WIN95 OSR2 32-bit FAT, LBA-mapped";
		$part_type["0e"]="WIN95: DOS 16-bit FAT, LBA-mapped";
		$part_type["0f"]="WIN95: Extended partition, LBA-mapped";
		$part_type["10"]="OPUS (?)";
		$part_type["11"]="Hidden DOS 12-bit FAT";
		$part_type["12"]="Compaq config partition";
		$part_type["14"]="Hidden DOS 16-bit FAT <32M";
		$part_type["16"]="Hidden DOS 16-bit FAT >=32M";
		$part_type["17"]="Hidden IFS (e.g., HPFS)";
		$part_type["18"]="AST SmartSleep Partition";
		$part_type["19"]="Unused (Claimed for Willowtech Photon COS)";
		$part_type["1b"]="Hidden WIN95 OSR2 32-bit FAT";
		$part_type["1c"]="Hidden WIN95 OSR2 32-bit FAT, LBA-mapped";
		$part_type["1e"]="Hidden WIN95 16-bit FAT, LBA-mapped";
		$part_type["20"]="Unused";
		$part_type["21"]="Reserved";
		$part_type["21"]="Unused";
		$part_type["22"]="Unused";
		$part_type["23"]="Reserved";
		$part_type["24"]="NEC DOS 3.x";
		$part_type["26"]="Reserved";
		$part_type["31"]="Reserved";
		$part_type["32"]="NOS";
		$part_type["33"]="Reserved";
		$part_type["34"]="Reserved";
		$part_type["35"]="JFS on OS/2 or eCS";
		$part_type["36"]="Reserved";
		$part_type["38"]="THEOS ver 3.2 2gb partition";
		$part_type["39"]="Plan 9 partition";
		$part_type["39"]="THEOS ver 4 spanned partition";
		$part_type["3a"]="THEOS ver 4 extended partition";
		$part_type["41"]="Personal RISC Boot";
		$part_type["41"]="PPC PReP (Power PC Reference Platform) Boot";
		$part_type["42"]="Linux swap (sharing disk with DRDOS)";
		$part_type["42"]="SFS (Secure Filesystem)";
		$part_type["42"]="Windows 2000 marker";
		$part_type["43"]="Linux native (sharing disk with DRDOS)";
		$part_type["44"]="GoBack partition";
		$part_type["45"]="Boot-US boot manager";
		$part_type["45"]="Priam";
		$part_type["45"]="EUMEL/Elan";
		$part_type["46"]="EUMEL/Elan";
		$part_type["47"]="EUMEL/Elan";
		$part_type["4"]="EUMEL/Elan";
		$part_type["4a"]="AdaOS Aquila (Default)";
		$part_type["4a"]="ALFS/THIN lightweight filesystem for DOS";
		$part_type["4c"]="Oberon partition";
		$part_type["4d"]="QNX4.x";
		$part_type["4e"]="QNX4.x 2nd part";
		$part_type["4f"]="QNX4.x 3rd part";
		$part_type["4f"]="Oberon partition";
		$part_type["50"]="OnTrack Disk Manager (older versions) RO";
		$part_type["50"]="Lynx RTOS";
		$part_type["50"]="Native Oberon (alt)";
		$part_type["51"]="OnTrack Disk Manager RW (DM6 Aux1)";
		$part_type["51"]="Novell";
		$part_type["52"]="CP/M";
		$part_type["52"]="Microport SysV/AT";
		$part_type["53"]="Disk Manager 6.0 Aux3";
		$part_type["54"]="Disk Manager 6.0 Dynamic Drive Overlay";
		$part_type["55"]="EZ-Drive";
		$part_type["56"]="Golden Bow VFeature Partitioned Volume.";
		$part_type["56"]="DM converted to EZ-BIOS";
		$part_type["57"]="DrivePro";
		$part_type["57"]="VNDI Partition";
		$part_type["5c"]="Priam EDisk";
		$part_type["61"]="SpeedStor";
		$part_type["63"]="Unix System V (SCO, ISC Unix, UnixWare, ...), Mach, GNU Hurd";
		$part_type["64"]="PC-ARMOUR protected partition";
		$part_type["64"]="Novell Netware 286, 2.xx";
		$part_type["65"]="Novell Netware 386, 3.xx or 4.xx";
		$part_type["66"]="Novell Netware SMS Partition";
		$part_type["67"]="Novell";
		$part_type["68"]="Novell";
		$part_type["69"]="Novell Netware 5+, Novell Netware NSS Partition";
		$part_type["70"]="DiskSecure Multi-Boot";
		$part_type["71"]="Reserved";
		$part_type["73"]="Reserved";
		$part_type["74"]="Reserved";
		$part_type["74"]="Scramdisk partition";
		$part_type["75"]="IBM PC/IX";
		$part_type["76"]="Reserved";
		$part_type["77"]="M2FS/M2CS partition";
		$part_type["77"]="VNDI Partition";
		$part_type["78"]="XOSL FS";
		$part_type["80"]="MINIX until 1.4a";
		$part_type["81"]="Linux";
		$part_type["81"]="Mitac disk manager";
		$part_type["82"]="Prime";
		$part_type["82"]="Solaris x86";
		$part_type["82"]="Linux swap";
		$part_type["83"]="Linux native";
		$part_type["84"]="OS/2 hidden C: drive";
		$part_type["84"]="Hibernation partition";
		$part_type["85"]="Linux extended partition";
		$part_type["86"]="Old Linux RAID partition superblock";
		$part_type["86"]="NTFS volume set";
		$part_type["87"]="NTFS volume set";
		$part_type["8a"]="Linux Kernel Partition (used by AiR-BOOT)";
		$part_type["8b"]="Legacy Fault Tolerant FAT32 volume";
		$part_type["8c"]="Legacy Fault Tolerant FAT32 volume using BIOS extd INT 13h";
		$part_type["8d"]="Free FDISK hidden Primary DOS FAT12 partitition";
		$part_type["8e"]="Linux Logical Volume Manager partition";
		$part_type["90"]="Free FDISK hidden Primary DOS FAT16 partitition";
		$part_type["91"]="Free FDISK hidden DOS extended partitition";
		$part_type["92"]="Free FDISK hidden Primary DOS large FAT16 partitition";
		$part_type["93"]="Hidden Linux native partition";
		$part_type["93"]="Amoeba";
		$part_type["94"]="Amoeba bad block table";
		$part_type["95"]="MIT EXOPC native partitions";
		$part_type["97"]="Free FDISK hidden Primary DOS FAT32 partitition";
		$part_type["98"]="Free FDISK hidden Primary DOS FAT32 partitition (LBA)";
		$part_type["99"]="DCE376 logical drive";
		$part_type["9a"]="Free FDISK hidden Primary DOS FAT16 partitition (LBA)";
		$part_type["9b"]="Free FDISK hidden DOS extended partitition (LBA)";
		$part_type["9f"]="BSD/OS";
		$part_type["a0"]="Laptop hibernation partition";
		$part_type["a1"]="Laptop hibernation partition";
		$part_type["a1"]="HP Volume Expansion (SpeedStor variant)";
		$part_type["a8"]="Mac OS-X";
		$part_type["a9"]="NetBSD";
		$part_type["aa"]="Olivetti Fat 12 1.44Mb Service Partition";
		$part_type["ab"]="Mac OS-X Boot partition";
		$part_type["ab"]="GO! partition";
		$part_type["ae"]="ShagOS filesystem";
		$part_type["af"]="ShagOS swap partition";
		$part_type["b0"]="BootStar Dummy";
		$part_type["b1"]="Reserved";
		$part_type["b3"]="Reserved";
		$part_type["b4"]="Reserved";
		$part_type["b6"]="Reserved";
		$part_type["b7"]="BSDI BSD/386 filesystem";
		$part_type["b8"]="BSDI BSD/386 swap partition";
		$part_type["bb"]="Boot Wizard hidden";
		$part_type["be"]="Solaris 8 boot partition";
		$part_type["c0"]="CTOS";
		$part_type["c0"]="REAL/32 secure small partition";
		$part_type["c0"]="NTFT Partition";
		$part_type["c1"]="DRDOS/secured (FAT-12)";
		$part_type["c2"]="Reserved for DR-DOS 7+";
		$part_type["c2"]="Hidden Linux";
		$part_type["c3"]="Hidden Linux swap";
		$part_type["c4"]="DRDOS/secured (FAT-16, < 32M)";
		$part_type["c5"]="DRDOS/secured (extended)";
		$part_type["c6"]="DRDOS/secured (FAT-16, >= 32M)";
		$part_type["c6"]="Windows NT corrupted FAT16 volume/stripe set";
		$part_type["c7"]="Windows NT corrupted NTFS volume/stripe set";
		$part_type["c7"]="Syrinx boot";
		$part_type["c8"]="(See also ID c2.)";
		$part_type["c9"]="(See also ID c2.)";
		$part_type["ca"]="(See also ID c2.)";
		$part_type["cb"]="reserved for DRDOS/secured (FAT32)";
		$part_type["cc"]="reserved for DRDOS/secured (FAT32, LBA)";
		$part_type["cd"]="CTOS Memdump?";
		$part_type["ce"]="reserved for DRDOS/secured (FAT16, LBA)";
		$part_type["d0"]="REAL/32 secure big partition";
		$part_type["d1"]="Old Multiuser DOS secured FAT12";
		$part_type["d4"]="Old Multiuser DOS secured FAT16 <32M";
		$part_type["d5"]="Old Multiuser DOS secured extended partition";
		$part_type["d6"]="Old Multiuser DOS secured FAT16 >=32M";
		$part_type["d8"]="CP/M-86";
		$part_type["da"]="Non-FS Data";
		$part_type["db"]="Digital Research CP/M, Concurrent CP/M, Concurrent DOS";
		$part_type["db"]="CTOS (Convergent Technologies OS -Unisys)";
		$part_type["db"]="KDG Telemetry SCPU boot";
		$part_type["dd"]="Hidden CTOS Memdump?";
		$part_type["de"]="Dell PowerEdge Server utilities (FAT fs)";
		$part_type["df"]="DG/UX virtual disk manager partition";
		$part_type["df"]="BootIt EMBRM";
		$part_type["e0"]="Reserved by STMicroelectronics for a filesystem called ST AVFS.";
		$part_type["e1"]="DOS access or SpeedStor 12-bit FAT extended partition";
		$part_type["e3"]="DOS R/O or SpeedStor";
		$part_type["e4"]="SpeedStor 16-bit FAT extended partition < 1024 cyl.";
		$part_type["e5"]="Tandy DOS with logical sectored FAT (According to Powerquest.)";
		$part_type["e5"]="Reserved";
		$part_type["e6"]="Reserved";
		$part_type["eb"]="BFS (aka BeFS)";
		$part_type["ed"]="Reserved for Matthias Paul’s Sprytix";
		$part_type["ee"]="Indication that this legacy MBR is followed by an EFI header";
		$part_type["ef"]="Partition that contains an EFI file system";
		$part_type["f0"]="Linux/PA-RISC boot loader";
		$part_type["f1"]="SpeedStor";
		$part_type["f2"]="DOS 3.3+ secondary partition (Powerquest writes: Unisys DOS with logical sectored FAT.)";
		$part_type["f3"]="Reserved    (Powerquest writes: Storage Dimensions SpeedStor.)";
		$part_type["f4"]="SpeedStor large partition";
		$part_type["f4"]="Prologue single-volume partition";
		$part_type["f5"]="Prologue multi-volume partition";
		$part_type["f6"]="Reserved (Powerquest writes: Storage Dimensions SpeedStor. )";
		$part_type["fa"]="Bochs";
		$part_type["fb"]="VMware File System partition";
		$part_type["fc"]="VMware Swap partition";
		$part_type["fd"]="Linux raid partition with autodetect using persistent superblock (Powerquest writes: Reserved for FreeDOS. )";
		$part_type["fe"]="SpeedStor > 1024 cyl.";
		$part_type["fe"]="LANstep";
		$part_type["fe"]="IBM PS/2 IML (Initial Microcode Load) partition, located at the end of the disk.";
		$part_type["fe"]="Windows NT Disk Administrator hidden partition";
		$part_type["fe"]="Linux Logical Volume Manager partition (old)";
		$part_type["ff"]="Xenix Bad Block Table";
		return $part_type[$id];
	}
	
	
	public function isMounted($dev){
		if($dev==null){return false;}
		$sock=new sockets();
		$dev=$sock->getFrameWork("cmd.php?check-dev=$dev");
		$dev=str_replace("/","\/",$dev);
		//echo $dev;
		$datas=explode("\n",@file_get_contents("/proc/mounts"));
		while (list ($num, $val) = each ($datas) ){
			if(preg_match("#$dev\s+(.+?)\s+(.+?)\s+#",$val,$re)){return $re[1];}
		}
		return false;
		
	}
	
	
	private function lOADINFOS(){
		if(empty($_GLOBAL["usb_list"])){
			if(!file_exists(dirname(__FILE__).'/usb.scan.inc')){return null;}
			include_once(dirname(__FILE__).'/usb.scan.inc');
			if(!is_array($_GLOBAL["usb_list"])){return null;}	
		}
		
		if(!is_array($_GLOBAL["disks_list"])){
			if(is_file(dirname(__FILE__).'/usb.scan.inc')){
				include dirname(__FILE__).'/usb.scan.inc';
				if(is_array($_GLOBAL["disks_list"]["$path"])){
					$this->ID_MODEL=$_GLOBAL["disks_list"]["$path"]["ID_MODEL"];
				}
			}	
		}
	$array=$_GLOBAL["usb_list"][$this->uuid];
	$this->path=trim($array["PATH"]);
	$this->img="usb-128.png";
	
		$this->LABEL=trim($array["LABEL"]);
		$this->TYPE=trim($array["TYPE"]);
		$this->SEC_TYPE=trim($array["SEC_TYPE"]);
		$this->mounted=trim($array["mounted"]);
		$this->ID_MODEL=trim($array["ID_MODEL"]);
		$this->ID_VENDOR=trim($array["ID_VENDOR"]);
		$this->ID_FS_TYPE=trim($array["ID_FS_TYPE"]);
	
	if(preg_match("#(.+?)[0-9]+$#",$this->path,$ri)){
		if(is_array($_GLOBAL["disks_list"]["{$ri[1]}"])){
			if(is_array($_GLOBAL["disks_list"]["{$ri[1]}"]["PARTITIONS"])){
			$array2=$_GLOBAL["disks_list"]["{$ri[1]}"];
			$this->model=trim($array2["ID_MODEL_1"]);
			if($this->model==null){$this->model=trim($array2["ID_MODEL_2"]);}
			$this->size=trim($array2["SIZE"]);
			$this->img="disk-128.png";
			
			}
		}
	}		
		
		


		
		
	$tbl=explode(";",$array["model"]);
	
		if(is_array($tbl)){
			if(count($tbl)>3){
			if(trim($tbl[0])<>null){$this->vendor=trim($tbl[0]);}
			$this->model=trim($tbl[1]);
			$this->product=trim($tbl[2]);
			$this->manufacturer=trim($tbl[3]);
			$this->id=trim($tbl[4]);
			$this->speed=trim($tbl[5]);
			if($this->speed=="Mbps"){$this->speed="?? Mbps";}
			}
		}
		
	if(preg_match("#(.+?);(.+?);(.+?);([0-9]+)%#",$array["SIZE"],$re)){
	 $this->size=$re[1];
	 $this->pourc=$re[4];
	}


	if($this->LABEL==null){$this->LABEL=$this->path;}
	if(trim($this->model)==null){if($this->ID_MODEL<>null){$this->model=$this->ID_MODEL;}}
	if(trim($this->vendor)==null){if($this->ID_VENDOR<>null){$this->vendor=$this->ID_VENDOR;}}
	
		
		
	}
	
}






class os_system{
	
	
	
function memory() {
    $results['ram'] = array('total' => 0, 'free' => 0, 'used' => 0, 'percent' => 0);
    $results['swap'] = array('total' => 0, 'free' => 0, 'used' => 0, 'percent' => 0);
    $results['devswap'] = array();
    if ($this->rfts('/proc/meminfo', $bufr)) {
      $bufe = explode("\n", $bufr);
      foreach($bufe as $buf) {
        if (preg_match('/^MemTotal:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
          $results['ram']['total'] = $ar_buf[1];
        } else if (preg_match('/^MemFree:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
          $results['ram']['free'] = $ar_buf[1];
        } else if (preg_match('/^Cached:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
          $results['ram']['cached'] = $ar_buf[1];
        } else if (preg_match('/^Buffers:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
          $results['ram']['buffers'] = $ar_buf[1];
        }
      }
      $results['ram']['used'] = $results['ram']['total']-$results['ram']['free'];
      $results['ram']['percent'] = round(($results['ram']['used']*100) /$results['ram']['total']);
      // values for splitting memory usage
      if (isset($results['ram']['cached']) && isset($results['ram']['buffers'])) {
        $results['ram']['app'] = $results['ram']['used']-$results['ram']['cached']-$results['ram']['buffers'];
        $results['ram']['app_percent'] = round(($results['ram']['app']*100) /$results['ram']['total']);
        $results['ram']['buffers_percent'] = round(($results['ram']['buffers']*100) /$results['ram']['total']);
        $results['ram']['cached_percent'] = round(($results['ram']['cached']*100) /$results['ram']['total']);
      }
      if ($this->rfts('/proc/swaps', $bufr)) {
        $swaps = explode("\n", $bufr);
        for ($i = 1;$i < (sizeof($swaps));$i++) {
          if (trim($swaps[$i]) != "") {
            $ar_buf = preg_split('/\s+/', $swaps[$i], 6);
            $results['devswap'][$i-1] = array();
            $results['devswap'][$i-1]['dev'] = $ar_buf[0];
            $results['devswap'][$i-1]['total'] = $ar_buf[2];
            $results['devswap'][$i-1]['used'] = $ar_buf[3];
            $results['devswap'][$i-1]['free'] = ($results['devswap'][$i-1]['total']-$results['devswap'][$i-1]['used']);
            $results['devswap'][$i-1]['percent'] = round(($ar_buf[3]*100) /$ar_buf[2]);
            $results['swap']['total']+= $ar_buf[2];
            $results['swap']['used']+= $ar_buf[3];
            $results['swap']['free'] = $results['swap']['total']-$results['swap']['used'];
            $results['swap']['percent'] = ceil(($results['swap']['used']*100) /(($results['swap']['total'] <= 0) ? 1 : $results['swap']['total']));
          }
        }
      }
    }
    return $results;
  }
  
  	function rfts($strFileName, &$strRet, $intLines = 0, $intBytes = 4096, $booErrorRep = true) {
  $strFile = "";
  $intCurLine = 1;
  if (file_exists($strFileName)) {
    if ($fd = fopen($strFileName, 'r')) {
      while (!feof($fd)) {
        $strFile.= fgets($fd, $intBytes);
        if ($intLines <= $intCurLine && $intLines != 0) {
          break;
        } else {
          $intCurLine++;
        }
      }
      fclose($fd);
      $strRet = $strFile;
    } else {
      if ($booErrorRep) {
        
      }
      return false;
    }
  } else {
    if ($booErrorRep) {
      
    }
    return false;
  }
  return true;
}

public function cpu_info() {
    $results = array("cpus" => 0);
    if ($this->rfts('/proc/cpuinfo', $bufr)) {
      $bufe = explode("\n", $bufr);
      $results = array('cpus' => 0, 'bogomips' => 0);
      $ar_buf = array();
      foreach($bufe as $buf) {
        $arrBuff = preg_split('/\s+:\s+/', trim($buf));
        if (count($arrBuff) == 2) {
          $key = $arrBuff[0];
          $value = $arrBuff[1];
          // All of the tags here are highly architecture dependant.
          // the only way I could reconstruct them for machines I don't
          // have is to browse the kernel source.  So if your arch isn't
          // supported, tell me you want it written in.
          switch ($key) {
            case 'model name':
              $results['model'] = $value;
            break;
            case 'cpu MHz':
              $results['cpuspeed'] = sprintf('%.2f', $value);
            break;
            case 'cycle frequency [Hz]': // For Alpha arch - 2.2.x
              $results['cpuspeed'] = sprintf('%.2f', $value/1000000);
            break;
            case 'clock': // For PPC arch (damn borked POS)
              $results['cpuspeed'] = sprintf('%.2f', $value);
            break;
            case 'cpu': // For PPC arch (damn borked POS)
              $results['model'] = $value;
            break;
            case 'L2 cache': // More for PPC
              $results['cache'] = $value;
            break;
            case 'revision': // For PPC arch (damn borked POS)
              $results['model'].= ' ( rev: ' . $value . ')';
            break;
            case 'cpu model': // For Alpha arch - 2.2.x
              $results['model'].= ' (' . $value . ')';
            break;
            case 'cache size':
              $results['cache'] = $value;
            break;
            case 'bogomips':
              $results['bogomips']+= $value;
            break;
            case 'BogoMIPS': // For alpha arch - 2.2.x
              $results['bogomips']+= $value;
            break;
            case 'BogoMips': // For sparc arch
              $results['bogomips']+= $value;
            break;
            case 'cpus detected': // For Alpha arch - 2.2.x
              $results['cpus']+= $value;
            break;
            case 'system type': // Alpha arch - 2.2.x
              $results['model'].= ', ' . $value . ' ';
            break;
            case 'platform string': // Alpha arch - 2.2.x
              $results['model'].= ' (' . $value . ')';
            break;
            case 'processor':
              $results['cpus']+= 1;
            break;
            case 'Cpu0ClkTck': // Linux sparc64
              $results['cpuspeed'] = sprintf('%.2f', hexdec($value) /1000000);
            break;
            case 'Cpu0Bogo': // Linux sparc64 & sparc32
              $results['bogomips'] = $value;
            break;
            case 'ncpus probed': // Linux sparc64 & sparc32
              $results['cpus'] = $value;
            break;
          }
        }
      }
      // sparc64 specific code follows
      // This adds the ability to display the cache that a CPU has
      // Originally made by Sven Blumenstein <bazik@gentoo.org> in 2004
      // Modified by Tom Weustink <freshy98@gmx.net> in 2004
      $sparclist = array('SUNW,UltraSPARC@0,0', 'SUNW,UltraSPARC-II@0,0', 'SUNW,UltraSPARC@1c,0', 'SUNW,UltraSPARC-IIi@1c,0', 'SUNW,UltraSPARC-II@1c,0', 'SUNW,UltraSPARC-IIe@0,0');
      foreach($sparclist as $name) {
        if ($this->rfts('/proc/openprom/' . $name . '/ecache-size', $buf, 1, 32, false)) {
          $results['cache'] = base_convert($buf, 16, 10) /1024 . ' KB';
        }
      }
      // sparc64 specific code ends
      // XScale detection code
      if ($results['cpus'] == 0) {
        foreach($bufe as $buf) {
          $fields = preg_split('/\s*:\s*/', trim($buf), 2);
          if (sizeof($fields) == 2) {
            list($key, $value) = $fields;
            switch ($key) {
              case 'Processor':
                $results['cpus']+= 1;
                $results['model'] = $value;
              break;
              case 'BogoMIPS': //BogoMIPS are not BogoMIPS on this CPU, it's the speed, no BogoMIPS available
                $results['cpuspeed'] = $value;
              break;
              case 'I size':
                $results['cache'] = $value;
              break;
              case 'D size':
                $results['cache']+= $value;
              break;
            }
          }
        }
        $results['cache'] = $results['cache']/1024 . " KB";
      }
    }
    $keys = array_keys($results);
    $keys2be = array('model', 'cpuspeed', 'cache', 'bogomips', 'cpus');
    while ($ar_buf = each($keys2be)) {
      if (!in_array($ar_buf[1], $keys)) {
        $results[$ar_buf[1]] = 'N.A.';
      }
    }
    if ($this->rfts('/proc/acpi/thermal_zone/THRM/temperature', $buf, 1, 4096, false)) {
      $results['temp'] = substr($buf, 25, 2);
    }
    return $results;
  }
  
  function realMemory(){
  		$datas=shell_exec(dirname(__FILE__)."/mem.pl");
  		if(preg_match('#T=([0-9]+) U=([0-9]+)#',$datas,$re)){
  			$ram_total=$re[1];
  			$ram_used=$re[2];
  			}
		$pourc=($ram_used*100)/$ram_total;
  		$pourc = round($pourc);
  		
  		$hash_mem["ram"]["percent"]=$pourc;
  		$hash_mem["ram"]["used"]=$ram_used;
  		$hash_mem["ram"]["total"]=$ram_total;
  		
  		return $hash_mem;
  		
  	
  }
  
  function swap(){
  	$datas=explode("\n",shell_exec('free -o'));
  	if(!is_array($datas)){return array();}
  	while (list ($num, $val) = each ($datas) ){
  		if(preg_match("#Swap:\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)#",$val,$re)){
  			$total=$re[1];
  			$used=$re[2];
  			$pourc=round(($used/$total)*100);
  			return array($pourc,$used,$total);
  		}
  		
  	}
  	
  	
  }
  
function html_load_usage($loadc=0){
	$users=new usersMenus();
	$cpunum=intval($users->CPU_NUMBER);
	$array_load=sys_getloadavg();
	$org_load=$array_load[2];
	$load=intval($org_load);
	//middle =$cpunum on va dire que 100% ($cpunum*2) + orange =0,75*$cpunum
	$max_vert_fonce=$cpunum;
	$max_vert_tfonce=$cpunum+1;
	$max_orange=$cpunum*0.75;
	$max_over=$cpunum*2;
	$purc1=$load/$cpunum;
	$pourc=round($purc1*100,2);
	$color="#5DD13D";
	if($load>=$max_orange){
		$color="#F59C44";
	}
	
	if($load>$max_vert_fonce){
		$color="#C5792D";
	}

	if($load>$max_vert_tfonce){
		$color="#83501F";
	}	
	

	
	if($load>=$max_over){
		$color="#640000";
		$text="<br>".texttooltip("{overloaded}","{overloaded}","Loadjs('overloaded.php')",null,0,"font-size:9px;font-weight:bold;color:red");
	}	

	if($pourc>100){$pourc=100;}

return "
<tr>
	<td width=1% nowrap class=legend nowrap>{load_avg}:</strong></td>
	<td align='left'>
		<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $color;margin-top:3px'>
			<div style='width:{$pourc}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
				<span style='color:white;font-size:11px;font-weight:bold'>$pourc%</span>
			</div>
		</div>
	</td>
	<td width=1% nowrap><strong>{load}: $org_load&nbsp;[$cpunum cpu(s)]$text</strong></td>
</tr>";	
	
	 
	
	
}
  

function html_Memory_usage(){
	$hash_mem=$this->realMemory();
	$mem_used_p=$hash_mem["ram"]["percent"];
	$mem_used_kb=FormatBytes($hash_mem["ram"]["used"]);
	$total=FormatBytes($hash_mem["ram"]["total"]);
	
	$sock=new sockets();
	$hdparms=unserialize(base64_decode($sock->GET_INFO("HdparmInfos")));
	
	$users=new usersMenus();
	$Disks=$users->disks_size;
	if($Disks<>null){
		$tbl=explode(";",$Disks);
		while (list ($num, $val) = each ($tbl) ){
			if(trim($val)<>null){
				$values=explode(",",$val);
				if(is_array($values)){
					$dc=$dc+1;
					$disk=$values[0];
					$disk=str_replace("mapper/","",$disk);
					$disk_name=$disk;
					$size=$values[1];
					$occ=$values[2];
					$disp=$values[3];
					$pourc=$values[4];
					$label=base64_decode($sock->getFrameWork("cmd.php?disk-get-label=/dev/$disk_name"));
									
					
					if(!$array_dup[$disk]){$array_dup[$disk]=true;}else{continue;}
					if(preg_match("#([a-z\_\-]+)[0-9]+$#",$disk_name,$rt)){$disk_name=$rt[1];}
					
					
					
					if($hdparms[$disk_name]<>null){
						$hdparms_info=intval($hdparms[$disk_name]);
						$hdparms_color="#5DD13D";
						if($hdparms_info>100){$hdparms_text_add="&nbsp;|{$hdparms_info}Mbs/s";$hdparms_info=100;}
						if($hdparms_info<70){$hdparms_color="#F59C44";}
						if($hdparms_info<31){$hdparms_color="#D32D2D";}
						 $pouc_hdparm_text=texttooltip("{performance}:$hdparms_info%$hdparms_text_add","<strong>{performance}:$hdparms_info%</strong><hr><br>{hdparm_infos_text}",null,null,0,"font-size:9px");
						
							$hdparm="
							<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $hdparms_color;margin-top:3px'>
								<div style='width:{$hdparms_info}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$hdparms_color'>
									
								</div>
							</div>
							";						
					}
					
					
					if($users->IOSTAT_INSTALLED){
						
						$pouc_disk_io=$users->IOSTAT_ARRAY[trim($disk)];
						
						if($pouc_disk_io<>null){
							$pouc_disk_io=intval($pouc_disk_io);
							$pouc_disk_io_text=$pouc_disk_io."%";
							$color="#5DD13D";
							if($pouc_disk_io>40){$color="#F59C44";}
							if($pouc_disk_io>90){$color="#D32D2D";}
							$pouc_disk_io_text="<br><span style='font-size:9px'>% i/o {use}:$pouc_disk_io%</span>";
							$iostat="
							<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $color;margin-top:3px'>
								<div style='width:{$pouc_disk_io}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
									
								</div>
							</div>
							";
						}
					}
					$color="#5DD13D";
					if($pourc>70){$color="#F59C44";}
					if($pourc>90){$color="#D32D2D";}
					$disk_text=$disk;
					if($label<>null){$disk_text="$label ($disk)";}
					
					$dd=$dd."
					<tr>
						<td nowrap align=right width=90px valign='top'><strong>$disk_text:</strong></td>
						
						<td>
							<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $color'>
								<div style='width:{$pourc}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
									<strong>{$pourc}%</strong>
								</div>
							</div>
							$iostat
							$hdparm
						</td>
						<td width=1% nowrap><strong>{$occ}G/{$size}G&nbsp;$pouc_disk_io_text$pouc_hdparm_text</strong></td>
						
					</tr>";
				}
			}
			
		}
	}
	
	$color="#5DD13D";
	if($mem_used_p>70){$color="#F59C44";}
	if($mem_used_p>79){$color="#D32D2D";}
					
	if($users->DSTAT_INSTALLED){
		$mem_js="Loadjs('dstat.memory.php')";
		$mem_cpu="Loadjs('dstat.cpu.php')";
		$sock=new sockets();
		$lastcpu=trim($sock->getfile('dstatlastcpu'));
		if(preg_match('#.+?\|\s+([0-9]+)\s+#',$lastcpu,$re)){
			if($re[1]>90){
				$cpu_color="#D32D2D";
			}else{
				$cpu_color="#5DD13D";
			}
			$cpu="<tr ". CellRollOver($mem_cpu).">
				<td width=1% nowrap class=legend nowrap>{cpu_usage}:</strong></td>
				<td align='left'>
					<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $cpu_color'>
						<div style='width:{$re[1]}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$cpu_color'><strong>{$re[1]}%</strong></div>
					</div>
				</td>
				<td width=1% nowrap><strong style='color:$cpu_color'>{$re[1]}%</strong></td>
				</tr>";
		}
		
		
	}else{
		$mem_js="LoadTaskManager();";
	}
	
	$swap_js="Loadjs('swap.status.php');";
	$swapar=$this->swap();
	if(is_array($swapar)){
		$swap_color="rgb(93, 209, 61)";
		$swap_text="<br><span style='font-size:9px'>swap: {$swapar[0]}% {used}</span>";
		if($swapar[0]>30){
			$swap_color="#F59C44";
		}
		if($swapar[0]>50){
			$swap_color="#D32D2D";
		}		
		$swap="<div style=\"border: 1px solid $swap_color; width: 100px; background-color: white; padding-left: 0px; margin-top: 3px;\" ". CellRollOver($swap_js).">
				<div style=\"width: {$swapar[0]}px; text-align: center; color: white; padding-top: 3px; padding-bottom: 3px; background-color:$swap_color;\"> </div>
		</div>";
	}
	
	$html="<table style='width:100%'>
	<tr ". CellRollOver().">
	<td width=1% nowrap class=legend nowrap>{memory_usage}:</strong></td>
	<td align='left'>
		<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $color' ". CellRollOver($mem_js).">
			<div style='width:{$mem_used_p}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'><strong>{$mem_used_p}%</strong></div>
		</div>
		$swap
	</td>
	<td width=1% nowrap><strong>$mem_used_kb/$total$swap_text</strong></td>
	</tr>
	". $this->html_load_usage()."
	$cpu
	$dd
	</table>
	";	
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}

function disk_type_array(){
	return array("1"=>"DOS 12-bit FAT","9"=>"AIX bootable","80"=>"Old MINIX","c7"=>"Syrinx",
   				"2"=>"XENIX root","a"=>"OPUS","81"=>"Linux/MINIX","db"=>"CP/M",
   				"3"=>"XENIX usr","40"=>"Venix 80286","82"=>"Linux swap","e1"=>"DOS access",
   				"4"=>"DOS 16-bit <32M", "51"=>"Novell?","83"=>"Linux native","e3"=>"DOS R/O",
   				"5"=>"Extended","52"=>"Microport","93"=>"Amoeba","f2"=>"DOS secondary",
   				"6"=>"DOS 16-bit >=32M","63"=>"GNU HURD","94"=>"Amoeba BBT","ff"=>"BBT",
   				"7"=>"OS/2 HPFS","64"=>"Novell","b7"=>"BSDI fs","8"=>"AIX","75"=>"PC/IX","b8"=>"BSDI swap");
	

	
	
	
 }
 
 
 function usb_parse_array($array,$internal_hd=true){
		$path=trim($array["PATH"]);
		$LABEL=trim($array["LABEL"]);
		$TYPE=trim($array["TYPE"]);
		$SEC_TYPE=trim($array["SEC_TYPE"]);
		$mounted=trim($array["mounted"]);
		$UUID=$array["UUID"];
		$ID_MODEL=$array["ID_MODEL"];
		$imgs="usb-64.png";
		if($mounted=='/'){return null;}
		
		if(!is_array($_GLOBAL["disks_list"])){
			if(is_file(dirname(__FILE__).'/usb.scan.inc')){
				include dirname(__FILE__).'/usb.scan.inc';
				if(is_array($_GLOBAL["disks_list"]["$path"])){
					$ID_MODEL=$_GLOBAL["disks_list"]["$path"]["ID_MODEL"];
				}
			}	
		}
		
		
if(preg_match("#(.+?)[0-9]+$#",$path,$ri)){
if(is_array($_GLOBAL["disks_list"]["{$ri[1]}"])){
		if(is_array($_GLOBAL["disks_list"]["{$ri[1]}"]["PARTITIONS"]))
		$imgs="usb-disk-64.png";
	}
}
if($vendor<>null){
		$text_vendor="		
		<tr>
			<td width=1% valign='top' class=legend nowrap>{vendor}:</strong></td>
			<td width=99% valign='top' align='left'><code style='font-size:11px'>$vendor</code></td>
		</tr>
		";};
		
		if(strlen($manufacturer)>2){
		$text_manufacturer="
		<tr>
			<td width=1% valign='top' class=legend nowrap>{manufacturer}:</td>
			<td width=99% valign='top' align='left'><code style='font-size:11px'>$manufacturer</code></td>
		</tr>	
		";};		
		
	
		
 		if(preg_match("#(.+?);(.+?);(.+?);([0-9]+)%#",$array["SIZE"],$re)){
	 		$size=$re[1];
	 		$pourc=$re[4];
			}
			
		if($LABEL==null){if($path<>null){$title=$path;}}else{$title=$LABEL;}
		
 	if(($mounted==null) && ($size==null)){
		$error=true;
		if($TYPE==null){$TYPE=$array["ID_FS_TYPE"];}	
		$title_mounted="<span style='color:red;font-size:9px'>{error_not_mounted}</span>";
			$umount="
				<tr>
				<td align='right' >" . imgtootltip('fw_bold.gif','{mount}',"Loadjs('usb.index.php?mount=yes&uuid=$UUID&mounted=$path&type=$TYPE')")."</td>
				<td style='font-size:12px'>". texttooltip('{mount}','{mount_explain}',"Loadjs('usb.index.php?mount=yes&uuid=$UUID&mounted=$path&type=$TYPE')")."</td>
				</tr>";				
		
	}			
			
 		if(strlen($mounted)>30){$mounted=texttooltip(substr($mounted,0,27)."...",$mounted,null,null,1,'font-size:11px');}
		

		$html="
		<table style='width:221px;margin:3px;border:1px dotted #CCCCCC'>
			<tr ". CellRollOver("Loadjs('usb.index.php?uuid-infos=$UUID');").">
			<td valign='top'>
				<table style='width=100%'>
					<tr>
						<td valign='top' width=1%>" . imgtootltip($imgs,"{parameters}","Loadjs('usb.browse.php?uuid=$UUID');")."</td>
						<td valign='top'>
									<span style='font-weight:bold;font-size:13px;color:#939393'>$title</span>
									<table style='width:100%'>
									<tr>
										<td valign='top'>
										</td>
										<td valign='top'>
											$title_mounted
											<span style='font-size:11px;text-transform:italic'>$ID_MODEL</span>
											<br>
											<code>{mounted}:$mounted</code>
										</td>
									</tr>
									</table>
						
							</td>
					</tr>		
				</table>
			</td>
		</tr>
		</table>
		";
 	return $html;
			

 }


function usb_parse_array_old($array,$internal_hd=true){
		$path=trim($array["PATH"]);
		$LABEL=trim($array["LABEL"]);
		$TYPE=trim($array["TYPE"]);
		$SEC_TYPE=trim($array["SEC_TYPE"]);
		$mounted=trim($array["mounted"]);
		$UUID=$array["UUID"];
		$title=null;
		
		if(!$internal_hd){
			if($TYPE=='swap'){return null;}
			if($mounted=='/'){return null;}
		}
		
	$tbl=explode(";",$array["model"]);
		if(is_array($tbl)){
			$vendor=trim($tbl[0]);
			$model=trim($tbl[1]);
			$product=trim($tbl[2]);
			$manufacturer=trim($tbl[3]);
			$id=trim($tbl[4]);
			$speed=trim($tbl[5]);
			if($speed=="Mbps"){$speed="?? Mbps";}
		}
		
	if(preg_match("#(.+?);(.+?);(.+?);([0-9]+)%#",$array["SIZE"],$re)){
	 $size=$re[1];
	 $pourc=$re[4];
	}
	
	if(($mounted==null) && ($size==null)){
		$error=true;
		if($TYPE==null){$TYPE=$array["ID_FS_TYPE"];}	
		$size="<span style='color:red;font-size:9px'>{error_not_mounted}</span>";
			$umount="
				<tr>
				<td align='right' >" . imgtootltip('fw_bold.gif','{mount}',"Loadjs('usb.index.php?mount=yes&uuid=$UUID&mounted=$path&type=$TYPE')")."</td>
				<td style='font-size:12px'>". texttooltip('{mount}','{mount_explain}',"Loadjs('usb.index.php?mount=yes&uuid=$UUID&mounted=$path&type=$TYPE')")."</td>
				</tr>";				
		
	}
	
	
	
	$title=$path;
	if(($path==null) && ($TYPE==null)){
		$error=true;
			
		$size="<span style='color:red;font-size:9px'>{error_not_plugged}</span>";
		

		
	}
	
	if($pourc>90){$color="#D32D2D";}else{$color="#5DD13D";}
		
	$vendor=$array["ID_VENDOR"];
	$model=$array["ID_MODEL"];
	if($TYPE==null){
		$TYPE=$array["ID_FS_TYPE"];
	}
		
		$text_DEV="			
		<tr>
			<td width=1% valign='top' class=legend nowrap>{dev_point}:</strong></td>
			<td width=99% valign='top' align='left'><code style='font-size:11px'>$path</code></td>
		</tr>";	
		
		if($LABEL<>null){
		$title=$LABEL;
		$text_LABEL="			
		<tr>
			<td width=1% valign='top' class=legend nowrap>{label}:</strong></td>
			<td width=99% valign='top' align='left'><code style='font-size:11px'>$LABEL</code></td>
		</tr>";};
		
		if(trim($TYPE)<>null){
		$text_TYPE="			
		<tr>
			<td width=1% valign='top' align='right' class=legend  nowrap>{fs_type}:</strong></td>
			<td width=99% valign='top' align='left'><code style='font-size:11px'>$TYPE</code></td>
		</tr>
		";};
		
		if($mounted<>null){
			$mounted_text=$mounted;
			$umount="
				<tr>
				<td align='right' >" . imgtootltip('ed_delete.gif','{umount}',"Loadjs('usb.index.php?umount=yes&uuid=$UUID&mounted=$mounted')")."</td>
				<td style='font-size:12px'>". texttooltip('{umount}','{umount_explain}',"Loadjs('usb.index.php?umount=yes&uuid=$UUID&mounted=$mounted')")."</td>
				</tr>";
			
			$js_brows=js_browse_direct($mounted,"{$UUID}_stick_folder");
			$browse="<tr>
				<td align='right' >" . imgtootltip('plusfull3.gif','{browse}',$js_brows)."</td>
				<td style='font-size:12px'>". texttooltip('{browse}','{browse_text}',$js_brows)."
				<input type='hidden' id='{$UUID}_stick_folder' value=''>
				<input type='hidden' id='{$UUID}_stick_mounted' value='$mounted'>
				</td>
				</tr>";
			
			if(strlen($mounted_text)>20){$mounted_text=texttooltip(substr($mounted_text,0,17).'...',$mounted_text,null,null,1);}
			$text_mounted="				
			<tr>
				<td width=1% valign='top' align='right' class=legend  nowrap>{mounted}:</strong></td>
				<td width=99% valign='top' align='left'><code style='font-size:11px'>$mounted_text</code></td>
			</tr>";};
		
		if($vendor<>null){
		$text_vendor="		
		<tr>
			<td width=1% valign='top' class=legend nowrap>{vendor}:</strong></td>
			<td width=99% valign='top' align='left'><code style='font-size:11px'>$vendor</code></td>
		</tr>
		";};
		
		if(strlen($manufacturer)>2){
		$text_manufacturer="
		<tr>
			<td width=1% valign='top' class=legend nowrap>{manufacturer}:</td>
			<td width=99% valign='top' align='left'><code style='font-size:11px'>$manufacturer</code></td>
		</tr>	
		";};
		
		if(strlen(trim($model))>2){
			$text_model="			
			<tr>
				<td width=1% valign='top' class=legend nowrap>{model}:</td>
				<td width=99% valign='top' align='left'><code style='font-size:11px'>$model</code></td>
			</tr>";
		}
		
		
		
		if(strlen($id)>2){
		$text_id="	
		<tr>
			<td width=1% valign='top' class=legend nowrap>ID:</td>
			<td width=99% valign='top' align='left'><code style='font-size:11px'>$id</code></td>
		</tr>
		";};
		
		if(strlen($speed)>5){
		$text_speed="	
		
		<tr>
			<td width=1% valign='top' class=legend nowrap>{speed}:</td>
			<td width=99% valign='top' align='left'><code style='font-size:11px'>$speed</code></td>
		</tr>																
		<tr>
		
		";};
		
		
		$format="
		<tr>
		<td align='right'>" . imgtootltip('mailbox_hd.gif','{format_device}',"Loadjs('usb.index.php?format-index=yes&dev=$path')")."</td>
		<td style='font-size:12px'>". texttooltip('{format_device}','{format_device_explain}',"Loadjs('usb.index.php?format-index=yes&dev=$path')")."</td>
		</tr>
		";
		
		$rename="
		<tr>
		<td align='right' >" . imgtootltip('quote.gif','{change_label}',"Loadjs('usb.index.php?change-label-js=yes&uuid=$UUID')")."</td>
		<td style='font-size:12px'>". texttooltip('{change_label}','{change_label_explain}',"Loadjs('usb.index.php?change-label-js=yes&uuid=$UUID')")."</td>
		</tr>
		";		
		
		if(!$error){
		if($size<>null){
		$text_size="
		<tr>
			<td width=1% valign='top' class=legend nowrap>{size} ($size):</td>
		<td>
			<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $color'>
				<div style='width:{$pourc}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
					<strong>{$pourc}%</strong>
				</div>
			</div>
		</td>
		</tr>";
		}}
		

		
		$html="
		
		<table style='width:100%;padding:1px;margin:3px;'>
		<tr>
			<td>
				<div id=tablist>
					<li><a href=\"#1\" OnClick=\"javascript:document.getElementById('{$UUID}normal_infos').style.display='none';document.getElementById('{$UUID}extended_infos').style.display='block';\" $class>{advanced_infos}</a></li>
					<li><a href=\"#2\" OnClick=\"javascript:document.getElementById('{$UUID}extended_infos').style.display='none';document.getElementById('{$UUID}normal_infos').style.display='block';\"  $class>{normal_infos}</a></li>
				</div>
			</td>
		</tr>
		<tr>
		<td>
		<table style='width:100%;padding:1px;margin:3px;'>
		<tr>
			<td colspan=2>
				<H3>$title - $size</h3>

			</td>
		</tr>
		<tr>
			<td colspan=2 valign='top'>
				<div id='{$UUID}normal_infos'>
					<table style='width:100%'>
						$text_DEV
						$text_mounted
						$text_size
						$format
						$rename
						$umount
						$browse
					</table>
				</div>
				<div id='{$UUID}extended_infos' style='display:none'>
					<table style='width:100%'>
						$text_LABEL
						$text_speed
						$text_TYPE
						$text_vendor
						$text_manufacturer
						$text_id					
					</table>
				</div>
			</td>
		</tr>
		</table>
		</td>
		</tr>
		</table>
				
		
		";		


	return $html;	
		}

	
}

class smartd{
	var $dev;
	var $diskinfos_array=array();
	var $diskstats_array=array();
	var $disk_attributes=array();
	
	function smartd($dev=null){
		$this->dev=$dev;
		if($this->dev<>null){
			$this->smartctl_infos();
			$this->SelfTests();
			$this->SmartAttributes();
		}
		
		
		
	}
	
	function smartctl_infos(){
		$sock=new sockets();
		$datas=$sock->getfile("smartclinfos:$this->dev");
		$tbl=explode("\n",$datas);
		while (list ($num, $val) = each ($tbl) ){
			if(preg_match("#Home page#",$val)){continue;}
			if(preg_match('#(.+?):(.+)#',$val,$re)){
				$this->diskinfos_array[trim($re[1])]=trim($re[2]);
			}
		}
		
	}
	
	function SelfTests(){
		$sock=new sockets();
		$datas=$sock->getfile("smartcltests:$this->dev");
		$tbl=explode("\n",$datas);
			while (list ($num, $val) = each ($tbl) ){
				if(preg_match("#Home page#",$val)){continue;}
				if(preg_match('#.+?([0-9]+)\s+(.+?)\s+\s+(.+?)([0-9]+).+?([0-9]+)\s+(.+)#',$val,$re)){
				$this->diskstats_array[trim($re[1])]=array(
						"test_description"=>trim($re[2]),
						"status"=>trim($re[3]),
						"remaining"=>trim($re[4]),
						"lifetime"=>trim($re[5]),
						"lba_of_first_error"=>trim($re[6])
						);
				}
			}		
	}
	
	function SmartAttributes(){
		$sock=new sockets();
		$datas=$sock->getfile("smartcltAttributes:$this->dev");
		$tbl=explode("\n",$datas);
			while (list ($num, $val) = each ($tbl) ){
				if(preg_match("#Home page#",$val)){continue;}
				if(preg_match('#([A-Za-z\_]+)\s+([0-9xf]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+(.+?)\s+(.+?)\s+(.+?)\s+([0-9]+)#',$val,$re)){
				$this->disk_attributes[]=array(
						"ATTRIBUTE_NAME"=>trim($re[1]),
						"FLAG"=>trim($re[2]),
						"VALUE"=>trim($re[3]),
						"WORST"=>trim($re[4]),
						"THRESH"=>trim($re[5]),
						"TYPE"=>trim($re[6]),
						"UPDATED"=>trim($re[7]),
						"WHEN_FAILED"=>trim($re[8]),
						"RAW_VALUE"=>trim($re[9]),
						);
				}
			}		
	}	
	
	
	
}


class fstab{
	
	var $fstab_array=array();
	var $mount_array=array();
	function fstab(){
		$sock=new sockets();
		$tbl=unserialize(base64_decode($sock->getFrameWork('cmd.php?fstablist=yes')));
		
		
		while (list ($num, $val) = each ($tbl) ){
			$val=trim($val);
			if($val==null){continue;}
			if(substr($val,0,1)=='#'){continue;}
			
			if(preg_match("#(.+?)\s+(.+?)\s+(.+)#",$val,$re)){
				$this->fstab_array[$re[1]][]=array("mount"=>$re[2],"options"=>$re[3]);
			}
		}
		
		$datas=$sock->getfile('mountlist');
		$tbl=explode("\n",$datas);
		while (list ($num, $val) = each ($tbl) ){
			$val=trim($val);
			if($val==null){continue;}
			if(substr($val,0,1)=='#'){continue;}
			if(preg_match("#(.+?)\s+on\s+(.+?)\s+type#",$val,$re)){
				$this->mount_array[$re[1]][$re[2]]=true;
			}
		}
		
	}
	
	public function save(){
		if(!is_array($this->fstab_array)){return false;}
		reset($this->fstab_array);
		while (list ($dev, $array) = each ($this->fstab_array) ){
			while (list ($index, $array2) = each ($array) ){
			 $conf=$conf ."$dev\t{$array2["mount"]}\t{$array2["options"]}\n";
			 }
		}
		
		$sock=new sockets();
		$sock->SaveConfigFile($conf,'SystemFSTAB');
		$sock->getfile('fstabapply');
	}
	

	
		
		
	
	
	
}


class mount{
	var $eventspath=null;
	public function mount($eventspath=null){
		$this->eventspath=$eventspath;
	}
	
	public function ismounted($mount_point){
		$mount_point=str_replace("/",'\/',$mount_point);
		$mount_point=str_replace(".",'\.',$mount_point);
		$mount_point=str_replace("$",'\$',$mount_point);
		
		$tbl=explode("\n",@file_get_contents("/proc/mounts"));
		
		if(!is_array($tbl)){
			$this->events("ismounted:: not an array in /proc/mounts");
			return false;
		}
		while (list ($index, $line) = each ($tbl) ){
			if(preg_match("#$mount_point#",$line)){return true;}
		}
		//$this->events("ismounted() failed found \"$mount_point\"");	
	}
	
	
	public function smb_mount($mountpoint,$server,$username,$password,$path){
		
		if($this->ismounted($mountpoint)){
			$this->events("smb_mount() already mounted");
			return true;
		}
		
		if($username<>null){
			$credentials=" -o username=$username,password=$password ";
		}
		$ip=gethostbyname($server);
		if($ip==null){
			$this->events("smb_mount() failed while resolving \"$server\"");	
			return false;
		}
		$pp=new ping($ip);
		if(!$pp->Isping()){
			$this->events("smb_mount() Failed to ping $ip, aborting process.");
			return false;	
		}	
		$this->events("smb_mount() ping ok");	
		
		$cmd="/bin/mount -t smbfs$credentials//$server/$path $mountpoint";
		
		if(is_file("/sbin/mount.cifs")){
			$cmd="/sbin/mount.cifs //$server/$path $mountpoint$credentials";
			if(function_exists("writelogs")){writelogs("$cmd",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
		}
		
		
		$this->events("smb_mount() mounting //$server/$path -> $mountpoint");
		if(!is_dir("$mountpoint")){@mkdir($mountpoint,null,true);}
		//$this->events("$cmd");
		exec($cmd,$results);
		while (list ($index, $line) = each ($results) ){
			if(function_exists("writelogs")){writelogs("$line",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
			$this->events("LOG:\"$line\"");
		}
		
		if($this->ismounted($mountpoint)){return true;}
		
	}
	
	public function usb_mount($mountpoint,$type,$dev){	
		
	if($this->ismounted($mountpoint)){
			$this->events("usb_mount() already mounted");
			return true;
		}
		$cmd="/bin/mount -t $type $dev $mountpoint";	
		$this->events("usb_mount() mounting $dev -> $mountpoint");
		if(!is_dir("$mountpoint")){@mkdir($mountpoint,null,true);}
		exec($cmd,$results);
		while (list ($index, $line) = each ($results) ){
			$this->events("LOG:\"$line\"");
		}
		
		if($this->ismounted($mountpoint)){return true;}		
			
			
	}
	
private function events($text){
	
		if(function_exists("writelogs")){
			writelogs("$text",__CLASS__,__FILE__,__LINE__);
			return;
		}
	
		if($this->eventspath<>null){
			$pid=getmypid();
			$date=date("H:i:s");
			$logFile=$this->eventspath;
			$size=filesize($logFile);
			if($size>1000000){unlink($logFile);}
			$f = @fopen($logFile, 'a');
			$line="$date $text\n";
			if($_GET["DEBUG"]){echo $line;}
			@fwrite($f,$line);
			@fclose($f);
		}

		if($_GET["logcomputer"]<>null){
			$f = @fopen($_GET["logcomputer"], 'a');
			@fwrite($f,$line);
			@fclose($f);
		}
		
		
		}	
	
	
}

class ping{
	var $ip='';
	
	
	public function ping($ip){
		$this->ip=$ip;
	}
	
	public function Isping(){
		$ftmp="/tmp/". md5(__FILE__);
		exec("/bin/ping -q -c 1 -s 16 -W1 -Q 0x02 $this->ip >$ftmp 2>&1");
		$results=explode("\n",@file_get_contents($ftmp) );
		@unlink($ftmp);
		if(!is_array($results)){return false;}
		while (list ($index, $line) = each ($results) ){
			if(preg_match("#[0-9]+\s+[a-zA-Z]+\s+[a-zA-Z]+,\s+([0-9]+)\s+received#",$line,$re)){
				if($re[1]>0){
					return true;
				}else{
					return false;
				}
			}
		}
		
	}
	
}

function ProcessExistsBypattern($pattern){
	$pgrep=LOCATE_PGREP();
	$array=ProcessListBypattern($pattern);
	events_class("ProcessExistsBypattern:: found an array of ". count($array)." rows");
	if(count($array)>0){return true;}
	return false;
}

function ProcessListBypattern($pattern){
	if($_GET["LOCATE_PGREP"]==null){$_GET["LOCATE_PGREP"]=LOCATE_PGREP();}
	$pgrep=$_GET["LOCATE_PGREP"];
	$tmp=OS_FILE_TEMP();
	$cmd="$pgrep -f \"$pattern\" >$tmp 2>&1";
	events_class("ProcessListBypattern:: $cmd");
	system($cmd);
	$datas=explode("\n",@file_get_contents($tmp));
	@unlink($tmp);
	while (list ($index, $line) = each ($datas) ){
		
		if(preg_match("#^([0-9]+)#",$line,$re)){
			events_class("ProcessListBypattern:: found={$re[1]}");
			$array[]=$re[1];
		}
		
	}
	
	if(!is_array($array)){return array();}
	return $array;
}

	function OS_FILE_TEMP(){
		if(function_exists("sys_get_temp_dir")){
			return tempnam(sys_get_temp_dir(), 'artica-php');
		}
		return "/tmp/artica-php-".md5(date("Y-m-d H:i:s"));
		
	}



function LOCATE_PGREP(){
	if(is_file("/usr/bin/pgrep")){return "/usr/bin/pgrep";}
	if(is_file("/bin/pgrep")){return "/bin/pgrep";}
}

function ListOnlyDirectories($path){
	$dir_handle = @opendir($path);
	while ($file = readdir($dir_handle)) {
		if($file=='.'){continue;}
		if($file=='..'){continue;}
		
  		$results[]=$file;
	}

	closedir($dir_handle);
	if(!is_array($results)){return array();}
	return $results;
	
}

function events_class($text){
	if(!is_array($_GET["FILE_DEBUG"])){return ;}
	$f=new debuglogs();
	for($i=0;$i<count($_GET["FILE_DEBUG"]);$i++){
			$f->events($text,$_GET["FILE_DEBUG"][$i]);
		}
	}
		
class debuglogs{
	function debuglogs(){}
	
	public function events($text,$logFile){
		
		$pid=getmypid();
		$date=date("H:i:s");
		$size=@filesize($logFile);
		$f = @fopen($logFile, 'a');
		$sline="[$pid] $text";
		$line="$date [$pid] $text\n";
		error_log("$sline");
		@fwrite($f,$line);
		@fclose($f);
	}
	
}

function system_is_overloaded(){
	$users=new usersMenus();
	$d=new debuglogs();
	$cpunum=intval($users->CPU_NUMBER);
	$array_load=sys_getloadavg();
	$internal_load=$array_load[0];
	$cpunum=$cpunum+1.5;
	$file=basename(__FILE__);
	
	
	if($internal_load>$cpunum){
		$d->events("$file system_is_overloaded():: Load: $internal_load max=$cpunum SYSTEM OVERLOADED","/var/log/artica-postfix/artica-status.debug");
		return true;
		
	}
	//$d->events("$file system_is_overloaded():: CPU: $cpunum, load: $internal_load, max=$cpunum GOOD","/var/log/artica-postfix/artica-status.debug");
	return false;

	
}


function systemMaxOverloaded(){
	$d=new debuglogs();
	$array_load=sys_getloadavg();
	$sock=new sockets();
	$systemMaxOverloaded=$sock->GET_INFO("systemMaxOverloaded");
	if($systemMaxOverloaded==null){$systemMaxOverloaded=17;}
	if($internal_load>$systemMaxOverloaded){
		$d->events(basename(__FILE__)." systemMaxOverloaded():: Load: $internal_load/$systemMaxOverloaded SYSTEM REALLY OVERLOADED die all processes during this load","/var/log/artica-postfix/artica-status.debug");
		return true;
	}
}

function LOCATE_PROGRAM($file){
	$array[]="/usr/sbin";
	$array[]="/usr/bin";
	$array[]="/bin";
	$array[]="/sbin";
	$array[]="/usr/local/bin";
	$array[]="/usr/local/sbin";
	$array[]="/usr/bin/X11";
	while (list ($num, $path) = each ($array) ){if(is_file("$path/$file")){return "$path/$file";}}
	}

function LOCATE_NOHUP(){
	return LOCATE_PROGRAM("nohup");
	}
	
function LOCATE_SYSLOG_CONF(){
	if(is_file('/etc/syslog.conf')){return '/etc/syslog.conf';}
	if(is_file('/etc/syslog-ng/syslog-ng.conf')){return '/etc/syslog-ng/syslog-ng.conf';}
	if(is_file('/etc/rsyslog.d/50-default.conf')){return '/etc/rsyslog.d/50-default.conf';}
	if(is_file('/etc/rsyslog.conf')){return '/etc/rsyslog.conf';}
}
function LOCATE_SYSLOG_NG_CONF(){
  if(is_file('/etc/syslog-ng/syslog-ng.conf')){ return '/etc/syslog-ng/syslog-ng.conf';}
  if(is_file('/etc/syslog-ng.conf')){return '/etc/syslog-ng.conf';}
}
//############################################################################## 
//############################################################################## 
function MAILLOG_FROM_SYSLOGNG(){
	$path=LOCATE_SYSLOG_NG_CONF();
	   if(!is_file($path)){return null;}
	   $l=explode("\n",@file_get_contents($path));
	   while (list ($index, $line) = each ($l) ){
	   		if(preg_match("#^destination.+?mail\s+.+?\((.+?)\)#",$line,$re)){
		   		$file=$re[1];
		   		$file=str_replace('"','',$file);
		   		$file=str_replace("'",'',$file);
		   		return $file;
	   		}
	   }
   }
//############################################################################## 
function LOCATE_PHP5_BIN(){
  if($GLOBALS["LOCATE_PHP5_BIN"]<>null){return $GLOBALS["LOCATE_PHP5_BIN"];}
  if(is_file('/usr/bin/php5')){$GLOBALS["LOCATE_PHP5_BIN"]='/usr/bin/php5';return '/usr/bin/php5';}
  if(is_file('/usr/bin/php')){$GLOBALS["LOCATE_PHP5_BIN"]='/usr/bin/php';return '/usr/bin/php';}
  if(is_file('/usr/local/apache-groupware/php5/bin/php')){$GLOBALS["LOCATE_PHP5_BIN"]='/usr/bin/php5';return '/usr/local/apache-groupware/php5/bin/php';}
  
}

function LOCATE_MAILLOG_PATH(){

if($_GET["CLASS_DEBUG"]){echo "MAILLOG_PATH()\n";}
	if(is_file(LOCATE_SYSLOG_NG_CONF())){
		if($_GET["CLASS_DEBUG"]){echo "RETURN MAILLOG_FROM_SYSLOGNG()\n";}
	   return MAILLOG_FROM_SYSLOGNG();
		}
	
	$logconfig=LOCATE_SYSLOG_CONF();
	$filedatas=@file_get_contents($logconfig);
	if($_GET["CLASS_DEBUG"]){echo "$logconfig=". strlen($filedatas)." bytes\n";}
	
	if(preg_match("#mail\.=info.+?-([\/a-zA-Z_0-9\.]+)?#is",$filedatas,$re)){
		return $re[1];
	}
	  
	if(preg_match("#mail\.\*.+?-([\/a-zA-Z_0-9\.]+)?#is",$filedatas,$re)){
		return $re[1];
	}
	if(preg_match('#destination mailinfo[\s\{a-z]+\("(.+?)"#is',$filedatas,$re)){
		return $re[1];
	}
	
if($_GET["CLASS_DEBUG"]){echo "RETURN FAILED FROM $logconfig\n";}

}  

?>
