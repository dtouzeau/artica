<?php
if(!class_exists("Bs_IniHandler")){include_once(dirname(__FILE__).'/class.ini.inc');}
class unix {
	
	function unix(){
		
	}
	
	function __construct() {
	
	}
	
	function __destruct() {
	
	}
	
	public function LOCATE_DB_STAT(){
		if(is_file("/usr/bin/db4.7_stat")){return "/usr/bin/db4.7_stat";}
		if(is_file("/usr/bin/db4.6_stat")){return "/usr/bin/db4.6_stat";}
		if(is_file("/usr/bin/db4.1_stat")){return "/usr/bin/db4.1_stat";}
		$this->events("Unable to stat db_stat !");
	}
	
	public function LOCATE_CYRRECONSTRUCT(){
		if(is_file("/usr/sbin/cyrreconstruct")){return "/usr/sbin/cyrreconstruct";}
		if(is_file("/usr/lib/cyrus/bin/reconstruct")){return "/usr/lib/cyrus/bin/reconstruct";}
		if(is_file("/usr/lib/cyrus-imapd/reconstruct")){return "/usr/lib/cyrus-imapd/reconstruct";}
		$this->events("Unable to stat cyrus reconstruct tool");
	}
	
	public function LOCATE_SIEVEC(){
		if(is_file("/usr/sbin/sievec")){return "/usr/sbin/sievec";}
		if(is_file("/usr/lib/cyrus/bin/sievec")){return "/usr/lib/cyrus/bin/sievec";}
		if(is_file("/usr/lib/cyrus-imapd/sievec")){return "/usr/lib/cyrus-imapd/sievec";}
		$this->events("Unable to stat cyrus sievec tool");
	}


	public function LOCATE_SMBD_PID(){
		if(is_file("/var/run/smbd.pid")){return "/var/run/smbd.pid";}
		if(is_file("/var/run/samba/smbd.pid")){return "/var/run/samba/smbd.pid";}
		
	}
	public function LOCATE_NMBD_PID(){
		if(is_file("/var/run/nmbd.pid")){return "/var/run/nmbd.pid";}
		if(is_file("/var/run/samba/nmbd.pid")){return "/var/run/samba/nmbd.pid";}
		
	}	
	public function LOCATE_WINBINDD_PID(){
		if(is_file("/var/run/winbindd.pid")){return "/var/run/winbindd.pid";}
		if(is_file("/var/run/samba/winbindd.pid")){return "/var/run/samba/winbindd.pid";}
		
	}	

	public function LOCATE_CYRUS_IPURGE(){
		if(is_file("/usr/sbin/ipurge")){return "/usr/sbin/ipurge";}
		if(is_file("/usr/lib/cyrus/bin/ipurge")){return "/usr/lib/cyrus/bin/ipurge";}
		if(is_file("/usr/lib/cyrus-imapd/ipurge")){return "/usr/lib/cyrus-imapd/ipurge";}
		$this->events("Unable to stat cyrus ipurge tool");
	}	

	
	public function LOCATE_CYRQUOTA(){
		if(is_file("/usr/sbin/cyrquota")){return "/usr/sbin/cyrquota";}
		if(is_file("/usr/lib/cyrus/bin/quota")){return "/usr/lib/cyrus/bin/quota";}
		if(is_file("/usr/lib/cyrus-imapd/quota")){return "/usr/lib/cyrus-imapd/quota";}
		$this->events("Unable to stat cyrus cyrquota tool");
	}
	
	public function LOCATE_CYRUS_PIDPATH(){
		if(is_file("/var/run/cyrus-master.pid")){return "/var/run/cyrus-master.pid";}
		if(is_file("/var/run/cyrmaster.pid")){return "/var/run/cyrmaster.pid";}
		if(is_file("/var/run/cyrus.pid")){return "/var/run/cyrus.pid";}
		$this->events("Unable to stat cyrus pid path");
	}	
	
	public function LOCATE_MILTER_GREYLIST_PID(){
		if(is_file("/var/run/milter-greylist/milter-greylist.pid")){return "/var/run/milter-greylist/milter-greylist.pid";}
	}
	
	public function IS_OPENLDAP_RUNNING(){
		$pid_path=$this->LOCATE_SLAPD_PID_PATH();
		if($GLOBALS["DEBUG"]){echo __FUNCTION__." pid path: $pid_path\n";}
		$pidnum=$this->get_pid_from_file($pid_path);
		if($GLOBALS["DEBUG"]){echo __FUNCTION__." pid num: $pidnum\n";}
		return $this->process_exists($pidnum);
	}
	
	private function LOCATE_SLAPD_PID_PATH(){
		if($GLOBALS[__FUNCTION__]<>null){return $GLOBALS[__FUNCTION__];}
		$path=$this->LOCATE_SLAPD_CONF_PATH();
		$f=explode("\n",@file_get_contents($path));
		while (list ($num, $line) = each ($f)){
			if(preg_match("#pidfile\s+(.+)#",$line,$re)){
				if($GLOBALS["DEBUG"]){echo __FUNCTION__." pid path: $path\n";}
				$GLOBALS[__FUNCTION__]=trim($re[1]);
				return trim($re[1]);
			}else{
			
			}
		}
		
	}
	
	private function LOCATE_SLAPD_CONF_PATH(){
		if($GLOBALS[__FUNCTION__]<>null){return $GLOBALS[__FUNCTION__];}
		$f[]="/etc/ldap/slapd.conf";
		$f[]="/etc/openldap/slapd.conf";
		$f[]="/usr/local/etc/openldap/slapd.conf";
		while (list ($num, $path) = each ($f)){
			if(is_file($path)){
				if($GLOBALS["DEBUG"]){echo __FUNCTION__." path: $path\n";}
				$GLOBALS[__FUNCTION__]=$path;
				return $path;
			}
		}
		
		
	}

	
	public function DANSGUARDIAN_CATEGORIES(){
			$path="/etc/dansguardian/lists/blacklists";
			$unix=new unix();
			$dirs=$unix->dirdir($path);
			while (list ($num, $line) = each ($dirs)){
				
				
			}
		}
		
		
	public function APACHE_GROUPWARE_ACCOUNT(){
		$tbl=explode("\n",@file_get_contents("/usr/local/apache-groupware/conf/apache-groupware.conf"));
		while (list ($num, $line) = each ($tbl)){
			if(preg_match("#User\s+(.+)#",$line,$re)){
				return "{$re[1]}:{$re[1]}";
			}
			
		}
		
	}
		
		
	public function LIGHTTPD_PORT(){
		$tbl=explode("\n",@file_get_contents($this->LIGHTTPD_CONF_PATH()));
		while (list ($num, $line) = each ($tbl)){
			if(preg_match("#^server\.port.+?=.+?([0-9]+)#",$line,$re)){
				return $re[1];
			}
		}
		
	}
	
	private function LIGHTTPD_CONF_PATH(){
		if(is_file('/etc/lighttpd/lighttpd.conf')){return '/etc/lighttpd/lighttpd.conf';}
		if(is_file('/usr/local/etc/lighttpd/lighttpd.conf')){return '/usr/local/etc/lighttpd/lighttpd.conf';}
		
	}
	
	public function MEMORY_OF($pid){
		$datas=explode("\n",@file_get_contents("/proc/$pid/status"));
		while (list ($index, $line) = each ($datas)){
		 if(preg_match("#^VmSize.+?([0-9]+)#",$line,$re)){
		 	return $re[1];
		 }
		}
		
	}
	
	public function PPID_OF($pid){
		$datas=explode("\n",@file_get_contents("/proc/$pid/status"));
		while (list ($index, $line) = each ($datas)){
		 if(preg_match("#^PPid.+?([0-9]+)#",$line,$re)){
		 	return $re[1];
		 }
		}
		
	}

	public function GET_WIRELESS_CARD(){
		
		$t=explode("\n",@file_get_contents("/proc/net/wireless"));
		while (list ($index, $line) = each ($t)){
			if(preg_match("#(.+?):\s+([0-9]+)#",$line,$re)){return $re[1];}
		}
		
		$iwconfig=$this->find_program("iwconfig");
		if($iwconfig==null){
			if($GLOBALS["VERBOSE"]){echo "Unable to find iwconfig...\n";}
			return;
		}
		
		exec("$iwconfig 2>&1",$results);
		
		while (list ($index, $line) = each ($results)){
			if(preg_match("#^(.+?)\s+.+?ESSID#",$line,$re)){
				return $re[1];
			}else{
				if($GLOBALS["VERBOSE"]){echo "$line NO MATCH...\n";}
			}
		}
		
	}
	
	public function COUNT_LINES_OF_FILE($path){
		$wc=$this->find_program("wc");
		if($wc==null){
			$array=explode("\n",@file_get_contents("$path"));
			return count($array);
			
		}
		
		exec("$wc -l $path",$results);
		$line=@implode(" ",$results);
		if(preg_match("#^([0-9]+)\s+#",$line,$re)){
			return $re[1];
		}
		
	}
	
	
	
	
	public function LOCATE_SQUID_CONF(){
	   if(is_file('/etc/squid3/squid.conf')){ return '/etc/squid3/squid.conf';}
   	   if(is_file('/opt/artica/etc/squid.conf')){ return '/opt/artica/etc/squid.conf';}
   	   if(is_file('/etc/squid/squid.conf')){ return '/etc/squid/squid.conf';}
	}
	
 public function SQUID_CONFIG_PATH(){
   if(is_file("/etc/squid3/squid.conf")){return '/etc/squid3/squid.conf';}
   if(is_file('/opt/artica/etc/squid.conf')){return '/opt/artica/etc/squid.conf';}
   if(is_file('/etc/squid/squid.conf')){return '/etc/squid/squid.conf';}
}	
	
	public function BetterFS(){
		if(is_file($this->find_program("mkfs.ext4"))){return "ext4";}
		if(is_file($this->find_program("mkfs.ext3"))){return "ext3";}
		return "ext3";
	}

	public function hostname_g(){
		$tmp=$this->FILE_TEMP();
 		shell_exec("/bin/hostname -f >$tmp 2>&1");
 		$res=trim(@file_get_contents($tmp));
 		@unlink($tmp);
 		return $res; 
		}
		
	public function DISK_LIST(){
		$tmp=$this->FILE_TEMP();
		$results=array();
		exec($this->find_program("df") ." -P -h -B G",$returns);
		$pattern="#(.+)\s+([0-9]+)([A-Z])\s+([0-9]+)([A-Z])\s+([0-9]+)([A-Z])\s+([0-9]+)\%\s+(.+?)$#";
		while (list ($num, $ligne) = each ($returns) ){
			if(!preg_match($pattern,$ligne,$re)){continue;}
			$results[trim($re[1])]=array("SIZE"=>$re[2],"UNIT"=>$re[3],"USED"=>$re[4],"DISP"=>$re[6],"POURC"=>trim($re[8]));
			
		}
		
		return $results;
	}
	
public function file_size($path){

	exec($this->find_program("stat")." $path ",$results);
	while (list ($num, $line) = each ($results)){
		if(preg_match("#Size:\s+([0-9]+)\s+Blocks#",$line,$re)){
			return $re[1];break;
		}
	}
	return 0;
}

public function file_size_human($path){

	exec($this->find_program("du")." -h $path ",$results);
	while (list ($num, $line) = each ($results)){
		if(preg_match("#^([0-9A-Z]+)\s+#",$line,$re)){
			return $re[1];break;
		}
	}
	return 0;
}
	
public function alt_stat($file) {
	 $ss=@stat($file);
	 if(!$ss) return false; //Couldnt stat file
	 
	 $ts=array(
	  0140000=>'ssocket',
	  0120000=>'llink',
	  0100000=>'-file',
	  0060000=>'bblock',
	  0040000=>'ddir',
	  0020000=>'cchar',
	  0010000=>'pfifo'
	 );
	 
	 $p=$ss['mode'];
	 $t=decoct($ss['mode'] & 0170000); // File Encoding Bit
	 
	 $str =(array_key_exists(octdec($t),$ts))?$ts[octdec($t)]{0}:'u';
	 $str.=(($p&0x0100)?'r':'-').(($p&0x0080)?'w':'-');
	 $str.=(($p&0x0040)?(($p&0x0800)?'s':'x'):(($p&0x0800)?'S':'-'));
	 $str.=(($p&0x0020)?'r':'-').(($p&0x0010)?'w':'-');
	 $str.=(($p&0x0008)?(($p&0x0400)?'s':'x'):(($p&0x0400)?'S':'-'));
	 $str.=(($p&0x0004)?'r':'-').(($p&0x0002)?'w':'-');
	 $str.=(($p&0x0001)?(($p&0x0200)?'t':'x'):(($p&0x0200)?'T':'-'));
 
		 $s=array(
		 'perms'=>array(
		  'umask'=>sprintf("%04o",@umask()),
		  'human'=>$str,
		  'octal1'=>sprintf("%o", ($ss['mode'] & 000777)),
		  'octal2'=>sprintf("0%o", 0777 & $p),
		  'decimal'=>sprintf("%04o", $p),
		  'fileperms'=>@fileperms($file),
		  'mode1'=>$p,
		  'mode2'=>$ss['mode']),
		 
		 'owner'=>array(
		  'fileowner'=>$ss['uid'],
		  'filegroup'=>$ss['gid'],
		  'owner'=>
		  (function_exists('posix_getpwuid'))?
		  @posix_getpwuid($ss['uid']):'',
		  'group'=>
		  (function_exists('posix_getgrgid'))?
		  @posix_getgrgid($ss['gid']):''
		  ),
		 
		 'file'=>array(
		  'filename'=>$file,
		  'realpath'=>(@realpath($file) != $file) ? @realpath($file) : '',
		  'dirname'=>@dirname($file),
		  'basename'=>@basename($file)
		  ),
		
		 'filetype'=>array(
		  'type'=>substr($ts[octdec($t)],1),
		  'type_octal'=>sprintf("%07o", octdec($t)),
		  'is_file'=>@is_file($file),
		  'is_dir'=>@is_dir($file),
		  'is_link'=>@is_link($file),
		  'is_readable'=> @is_readable($file),
		  'is_writable'=> @is_writable($file)
		  ),
		 
		 'device'=>array(
		  'device'=>$ss['dev'], //Device
		  'device_number'=>$ss['rdev'], //Device number, if device.
		  'inode'=>$ss['ino'], //File serial number
		  'link_count'=>$ss['nlink'], //link count
		  'link_to'=>($s['type']=='link') ? @readlink($file) : ''
		  ),
		 
		 'size'=>array(
		  'size'=>$ss['size'], //Size of file, in bytes.
		  'blocks'=>$ss['blocks'], //Number 512-byte blocks allocated
		  'block_size'=> $ss['blksize'] //Optimal block size for I/O.
		  ),
		 
		 'time'=>array(
		  'mtime'=>$ss['mtime'], //Time of last modification
		  'atime'=>$ss['atime'], //Time of last access.
		  'ctime'=>$ss['ctime'], //Time of last status change
		  'accessed'=>@date('Y M D H:i:s',$ss['atime']),
		  'modified'=>@date('Y M D H:i:s',$ss['mtime']),
		  'created'=>@date('Y M D H:i:s',$ss['ctime'])
		  ),
		 );

 return $s;
}	
	
	private function MapperDetect($dev){
	$mapper=@readlink($dev);
	if($mapper==null){return $dev;}
	$mapper=str_replace("../mapper","/dev/mapper",$mapper);
	return $mapper;
	}
	
	public function DISK_MOUNTED($dev){
		$dev=$this->MapperDetect($dev);
		$datas=explode("\n",@file_get_contents("/proc/mounts"));
		$dev=str_replace("/","\/",$dev);
		$dev=str_replace(".","\.",$dev);
		while (list ($num, $ligne) = each ($datas) ){
			if(preg_match("#$dev#",$ligne)){
				writelogs_framework("\"$dev\"=$ligne",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				return true;
			}else{
				
			}
		}
	return false;}
	
	public function MOUNTED_PATH($dev){
		$dev=$this->MapperDetect($dev);
		$dev=str_replace("/","\/",$dev);
		$dev=str_replace(".","\.",$dev);
		$datas=explode("\n",@file_get_contents("/proc/mounts"));
		while (list ($num, $ligne) = each ($datas) ){
			if(preg_match("#^$dev\s+(.+?)\s+(ext|rw)#",$ligne,$re)){
				return trim($re[1]);	
			}
		}
		
	}
	
	public function PROCCESS_TIME_MIN($pid){
		if($pid<5){return 0;}
		if($pid==null){return 0;}
		exec("/usr/share/artica-postfix/bin/procstat $pid",$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match("#start_time:.+?\(([0-9]+)#",$ligne,$re)){
				$sec=$re[1];
			}
		}
		
		if($sec==null){$sec=0;}
		if($sec>0){$sec=$sec/60;return round($sec);}
		return 0;
	}
	
	public function PROCESS_MEMORY($pid,$noMo=false){
		//$PPID=$this->PROCESS_PPID();
		//if($PPID>1)
		
		if(!is_file("/proc/$pid/status")){return 0;}
		$f=@explode("\n",@file_get_contents("/proc/$pid/status"));
		$pattern="#^VmRSS:\s+([0-9]+)#";
		while (list ($num, $ligne) = each ($f) ){
			if(preg_match($pattern,$ligne,$re)){
				if($noMo){return $re[1];}
				return round($re[1]/1024,2);
			}
		}
		
	}
	
	public function PROCESS_CACHE_MEMORY($pid,$noMo=false){
		//$PPID=$this->PROCESS_PPID();
		//if($PPID>1)
		
		if(!is_file("/proc/$pid/status")){return 0;}
		$f=@explode("\n",@file_get_contents("/proc/$pid/status"));
		$pattern="#^VmSize:\s+([0-9]+)#";
		while (list ($num, $ligne) = each ($f) ){
			if(preg_match($pattern,$ligne,$re)){
				if($noMo){return $re[1];}
				return round($re[1]/1024,2);
			}
		}
		
	}	
	
	private function PROCESS_PPID($pid){
	if(!is_file("/proc/$pid/status")){return 0;}
		$f=@explode("\n",@file_get_contents("/proc/$pid/status"));
		$pattern="#^PPid:\s+([0-9]+)#";
		while (list ($num, $ligne) = each ($f) ){
			if(preg_match($pattern,$ligne,$re)){
				return intval($re[1]);
			}
		}
		
	}
	
	
	public function PROCESS_STATUS($pid){
		$datas=explode("\n",@file_get_contents("/proc/$pid/status"));
		if(!is_array($datas)){return false;}
		while (list ($num, $ligne) = each ($datas) ){
			if(preg_match("#State:\s+([A-Z])\s+#",$ligne,$re)){
				$STATUS=$re[1];
			}
			if(preg_match("#PPid:\s+([0-9]+)#",$ligne,$re)){
				$PPID=$re[1];
			}			
		}
		if($STATUS==null){return false;}
		return array($STATUS,$PPID);
	}
	
	
	public function SPAMASSASSIN_LOCAL_CF(){
		if(is_file("/etc/spamassassin/local.cf")){return('/etc/spamassassin/local.cf');}
		if(is_file("/etc/mail/spamassassin/local.cf")){return('/etc/mail/spamassassin/local.cf');}
		if(is_file("/opt/artica/etc/spamassassin/local.cf")){return('/opt/artica/etc/spamassassin/local.cf');}
	    @mkdir('/etc/spamassassin');
		shell_exec('/bin/touch /etc/spamassassin/local.cf');
		return '/etc/spamassassin/local.cf';
		}
		
	public function get_pid_from_file($file){
		return trim(@file_get_contents($file));
	}
	
	
	public function AddFSTab($dev,$mount){
		if($dev==null){return null;}
		if($mount==null){return null;}
		writelogs_framework("create directory $mount ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		@mkdir($mount);
		
		$datas=explode("\n",@file_get_contents("/etc/fstab"));
		$chk=$datas;
		
		while (list ($num, $ligne) = each ($chk) ){
			if(preg_match("#(.+?)\s+(.+?)\s+#",$ligne)){
				if(trim(strtolower($dev))==trim(strtolower($re[1]))){
					if(trim(strtolower($mount))==trim(strtolower($re[2]))){
						return null;
					}
				}
			}
			
		}
		
		$datas[]="$dev\t$mount\tauto\tdefaults\t0\t0\n";
		if(!file_put_contents("/etc/fstab",implode("\n",$datas))){
			writelogs_framework("Unable to save /etc/fstab",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		
	}
	
	public function DelFSTab($dev){
		if($dev==null){return null;}
		 $datas=explode("\n",@file_get_contents("/etc/fstab"));
		 while (list ($num, $ligne) = each ($datas) ){
		 	if($ligne==null){continue;}
		 	if(preg_match("#(.+?)\s+(.+?)\s+#",$ligne)){
		 		if(trim(strtolower($dev))==trim(strtolower($re[1]))){
		 			shell_exec($this->find_program("umount")." -f $dev");
		 			unset($datas[$num]);
		 			@rmdir(trim($re[2]));
		 		}
		 	}
		 }
		 
	if(!file_put_contents("/etc/fstab",implode("\n",$datas))){
			writelogs_framework("Unable to save /etc/fstab",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}	 
	
	
	}
	
	public function GetFSTabMountPoint($dev){
	if($dev==null){return null;}
	$dev=$this->MapperDetect($dev);
	$array=array();
	$dev=str_replace("/","\/",$dev);
	$datas=explode("\n",@file_get_contents("/etc/fstab"));
	while (list ($num, $ligne) = each ($datas) ){
		 	if($ligne==null){continue;}
		 	if(preg_match("#$dev\s+(.+?)\s+#",$ligne,$re)){
		 			$array[]=trim($re[1]);
		 			continue;
		 		//	writelogs_framework("#$dev\s+(.+?)\s+# success => $ligne",__FUNCTION__,__FILE__,__LINE__);
		 		}else{
		 		//	writelogs_framework("#$dev\s+(.+?)\s+# failed => $ligne",__FUNCTION__,__FILE__,__LINE__);
		 		}
		 	}
	
	return $array;
	
	}
	
	public function TOTAL_MEMORY_MB(){
		$unix=new unix();
		$free=$unix->find_program("free");
		exec("$free -m 2>&1",$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match("#Mem:\s+([0-9]+)\s+#",$ligne,$re)){
				return $re[1];
			}
		}
		
	}


	
	public function process_exists($pid){
		if(!preg_match("#([0-9]+)#",$pid,$re)){return false;}
		$pid=$re[1];
		if(is_file("/proc/$pid/exe")){return true;}
		if(is_file("/proc/$pid/cmdline")){return true;}
		return false;
	}
	
	public function file_time_min($path){
		if(!is_dir($path)){
			if(!is_file($path)){return 100000;}
			}
	 		$last_modified = filemtime($path);
	 		$data1 = $last_modified;
			$data2 = time();
			$difference = ($data2 - $data1); 	 
			return round($difference/60);	 
		}

	PUBLIC function LOCATE_SENDMAIL_PATH(){
		$path=$this->find_program("sendmail");
		if(is_file($path)){return $path;}
		if(is_file("/usr/lib/sendmail")){return "/usr/lib/sendmail";}	
		
	}
	
	public function LOCATE_APACHE_BIN_PATH(){
		if(is_file('/usr/sbin/apache2')){return '/usr/sbin/apache2';}
		if(is_file('/usr/sbin/apache')){return '/usr/sbin/apache';}
		if(is_file('/usr/sbin/httpd')){return '/usr/sbin/httpd';}
		if(is_file('/usr/sbin/httpd2')){return '/usr/sbin/httpd2';}
		if(is_file('/usr/local/obm-apache/bin/obm-apache')){return '/usr/local/obm-apache/bin/obm-apache';}
		if(is_file('/usr/local/apache-groupware/bin/apache-groupware')){return '/usr/local/apache-groupware/bin/apache-groupware';} 		
	}
	

	public function IMAPD_GET($key){
		if($GLOBALS["IMAPD_GET"]==null){
			$GLOBALS["IMAPD_GET"]=@file_get_contents("/etc/imapd.conf");
		}
		$datas=explode("\n",$GLOBALS["IMAPD_GET"]);
		while (list ($num, $ligne) = each ($datas) ){
			if(preg_match("#^$key:(.+)#",$ligne,$re)){
				return trim($re[1]);
			}
		}
	}
	
public function ServicesCMDArray(){
		$f[]="ldap";
		$f[]="saslauthd";
		$f[]="imap";
		$f[]="kav6";
		$f[]="squid";
		$f[]="dansgardian";
		$f[]="boa";
		$f[]="ftp";
		$f[]="mysql";
		$f[]="apache";
		$f[]="spamd";
		$f[]="clamd";
		$f[]="freshclam";
		$f[]="mgreylist";
		$f[]="daemon";
		$f[]="clammilter";
		$f[]="postfix";
		$f[]="dnsmasq";
		$f[]="stunnel";
		$f[]="postfix";
		$f[]="mailgraph";
		$f[]="mimedefang";
		$f[]="roundcube";
		$f[]="kav4samba";
		$f[]="bind9";
		$f[]="obm";
		$f[]="yorel";
		$f[]="p3scan";
		$f[]="syslogng";
		$f[]="mailarchive";
		$f[]="bogom";
		$f[]="collectd";
		$f[]="mysql";
		$f[]="fetchmail";
		$f[]="mailspy";
		$f[]="amavis";
		$f[]="retranslator";
		$f[]="spfmilter";
		$f[]="dotclear";
		$f[]="jcheckmail";
		$f[]="mailman";
		$f[]="kas3";
		$f[]="dhcp";
		$f[]="cicap";
		$f[]="openvpn";
		$f[]="postfix-logger";
		$f[]="dansguardian-tail";
		$f[]="apache-groupware";
		$f[]="cups";
		$f[]="dstat";
		$f[]="dstat-top-mem";
		$f[]="dstat-top-cpu";
		$f[]="rsync";
		$f[]="policydw";
		$f[]="autofs";
		$f[]="mysql-cluster";
		$f[]="assp";
		$f[]="pdns";
		$f[]="gluster";
		$f[]="gluster-cli";
		$f[]="syslogger";
		$f[]="zabbix";
		$f[]="smartd";
		return $f;	
}


public function monit_array(){
	exec("/usr/share/artica-postfix/bin/artica-install --monit-status",$array);
	if(!is_array($array)){return array();}
	while (list ($num, $ligne) = each ($array) ){
		if(preg_match("#Process\s+'(.+)'#",$ligne,$re)){
			$product=$re[1];
			$ARR[$product]["monit"]=true;
			continue;
		}
		
	if(preg_match("#status\s+(.+)#",$ligne,$re)){
			$ARR[$product]["status"]=$re[1];
			continue;
		}

		
		if(preg_match("#monitoring status\s+(.+)#",$ligne,$re)){
			$ARR[$product]["monitoring status"]=$re[1];
			continue;
		}

		if(preg_match("#uptime\s+(.+)#",$ligne,$re)){
			$ARR[$product]["uptime"]=$re[1];
			continue;
		}		
		if(preg_match("#children\s+(.+)#",$ligne,$re)){
			$ARR[$product]["children"]=$re[1];
			continue;
		}
	
		
		if(preg_match("#memory kilobytes total\s+(.+)#",$ligne,$re)){
			$ARR[$product]["memory kilobytes total"]=$re[1];
			continue;
		}
		
		if(preg_match("#^pid\s+([0-9]+)$#",trim($ligne),$re)){
			$ARR[$product]["pid"]=$re[1];
			continue;
		}		

		if(preg_match("#memory percent total\s+(.+)#",$ligne,$re)){
			$ARR[$product]["memory percent total"]=$re[1];
			continue;
		}
			if(preg_match("#cpu percent total\s+(.+)#",$ligne,$re)){
			$ARR[$product]["cpu percent total"]=$re[1];
			continue;
		}			
	}
	
	return $ARR;
	
}
	
	
public function IsProtectedDirectory($folder){
	if($folder==null){return true;}
	if($folder=="/"){return true;}
	$l["/cdrom"]=true;
	$l["/sys"]=true;
	$l["/initrd"]=true;
	$l["/dev"]=true;
	$l["/etc"]=true;
	$l["/boot"]=true;
	$l["/var/lib"]=true;
	$l["/sbin"]=true;
	$l["/lib"]=true;
	$l["/bin"]=true;
	$l["/usr/libexec"]=true;
	$l["/usr/sbin"]=true;
	$l["/usr/bin"]=true;	
	$l["/usr/include"]=true;	
	$l["/usr/local"]=true;	
	$l["/usr/src"]=true;		
	$l["/usr/usr"]=true;
	$l["/usr/X11R6"]=true;
	$l["/usr/lib"]=true;
	$l["/usr/lib64"]=true;
	$l["/usr/src"]=true;	
	$l["/srv"]=true;
	$l["/var/log"]=true;
	$l["/var/db"]=true;
	$l["/var/lib"]=true;
	$l["/var/lock"]=true;
	$l["/var/mail"]=true;
	$l["/var/milter-greylist"]=true;
	$l["/var/spool"]=true;
	$l["/var/opt"]=true;
	$l["/usr/share"]=true;
	$l["/opt"]=true;
	$l["/var/cache"]=true;
	$l["/var/local"]=true;
	$l["/var/run"]=true;
	$l["/var/tmp"]=true;
	$l["/var/webmin"]=true;
	$l["/lost+found"]=true;
	
	while (list ($num, $ligne) = each ($l) ){
		$p=str_replace("/","\/",$num);
		$p=str_replace(".","\.",$p);
		if(preg_match("#$p#",$folder)){return true;}
	}
}

	
	public function LOCATE_HEAD(){
		return $this->find_program("head");
		$this->events("Unable to stat head !");
		
	}	
	
	public function LOCATE_SYSLOG_PATH(){
		if(is_file("/etc/artica-postfix/settings/Daemons/syslog_path")){
			$datas=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/syslog_path"));
			if(!is_file($datas)){$datas="/var/log/syslog";}
			
		}
		return $datas;
		
	}
	
	public function LOCATE_DU(){
		return $this->find_program("du");
		$this->events("Unable to stat du !");
		
	}

	public function FILE_TEMP(){
		if(function_exists("sys_get_temp_dir")){
			return tempnam(sys_get_temp_dir(), 'artica-php');
		}
		return "/tmp/artica-php-".md5(date("Y-m-d H:i:s"));
		
	}
	
	
	public function KERNEL_VERSION_BIN(){
		$cmd=$this->find_program("uname")." -r";
		exec($cmd,$datas);
	if(preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)#",$datas[0],$re)){
			if(strlen($re[2])==1){$re[2]='0'.$re[2];}
			if(strlen($re[3])==1){$re[3]='0'.$re[3];}
			$kernel=$re[1].$re[2].$re[3];
			
		}
		
		return $kernel;
	}
	
	
	public function  miltergreylist_addacl($complete_string){
	   $ConfigurationFile=$this->MILTER_GREYLIST_CONF_PATH();
	   if(!is_file($ConfigurationFile)){return null;}
		$datas=@file_get_contents($ConfigurationFile);
		$tbl=explode("\n",$datas);
		while (list ($num, $ligne) = each ($tbl) ){
	   		if(strtolower(trim($ligne))==strtolower(trim($complete_string))){
	   			return ;
	   		}
		}
	
	  $tbl[]=$complete_string;
	  @file_put_contents($ConfigurationFile,implode("\n",$tbl));
	  
	}
	
	public function miltergreylist_cleanconf(){
		$ConfigurationFile=$this->MILTER_GREYLIST_CONF_PATH();
		$datas=@file_get_contents($ConfigurationFile);
		$tbl=explode("\n",$datas);
		while (list ($num, $ligne) = each ($tbl) ){
			if(trim($ligne)==null){unset($tbl[$num]);}
			if(substr($ligne,0,1)=="#"){unset($tbl[$num]);}
		}
		
		$tbl[]="\n";
		file_put_contents($ConfigurationFile,implode("\n",$tbl));
		
	 	$this->miltergreylist_check();
	}
	
	private function miltergreylist_check(){
		$ConfigurationFile=$this->MILTER_GREYLIST_CONF_PATH();
		$MILTER_GREYLIST_BIN_PATH=$this->MILTER_GREYLIST_BIN_PATH();
		$tmpf=$this->FILE_TEMP();
		for($i=0;$i<50;$i++){
			exec("$MILTER_GREYLIST_BIN_PATH -f $ConfigurationFile -c >$tmpf 2>&1");
			$dd=@file_get_contents($tmpf);
			
			@unlink($tmpf);
			if(preg_match("#config error at line\s+([0-9]+)#is",$dd,$ri)){
				if(!$this->miltergreylist_removeline($ri[1])){break;}
				continue;
			}
			
			else{break;}
		}
	}
	
	private function miltergreylist_removeline($line_number){
		 $ConfigurationFile=$this->MILTER_GREYLIST_CONF_PATH();
		 if(!is_file($ConfigurationFile)){return null;}
		 $datas=@file_get_contents($ConfigurationFile);
		 $tbl=explode("\n",$datas);
		 if(count($tbl)==$line_number){
		 	$tbl[]="";
		 	file_put_contents($ConfigurationFile,implode("\n",$tbl));
		 	return false;
		 }
		 echo "Starting......: milter-greylist Config...: error, removing  \"{$tbl[$line_number]}\" line $line_number";
		 unset($tbl[$line_number]);
		 file_put_contents($ConfigurationFile,implode("\n",$tbl));
		 return true;
	}
	
	
	
	public function miltergreylist_buildacl($pattern){
	 	if(preg_match("#(.+?)@(.+)#",$pattern,$re)){
	 		$part=trim($re[1]);
	 		$domain=trim($re[2]);
	 	}
	   	 $part=str_replace('*','',$part);
	 	
	 	 if(strlen($part)==0){ 
	           $suffix='acl whitelist domain';
	           $pattern=$domain;
	 	 }else{
	 	 	$suffix="acl whitelist from";
	 	 }
	   
		if(strpos($pattern,'*')>0){
	        $pattern=str_replace('.','\.',$pattern);
	        $pattern=str_replace('*','.*',$pattern);
	       	$pattern="/$pattern/";
		}
	     
		return "$suffix $pattern";
	}
	
 
	public function MILTER_GREYLIST_CONF_PATH(){
		if(is_file('/etc/milter-greylist/greylist.conf')){return '/etc/milter-greylist/greylist.conf';}
		if(is_file('/etc/mail/greylist.conf')){return '/etc/mail/greylist.conf';}
		if(is_file('/opt/artica/etc/milter-greylist/greylist.conf')){return '/opt/artica/etc/milter-greylist/greylist.conf';}
		return '/etc/mail/greylist.conf';
	}
	
	public function MILTER_GREYLIST_BIN_PATH(){
		if(is_file('/usr/local/bin/milter-greylist')){return '/usr/local/bin/milter-greylist';}
		if(is_file('/usr/sbin/milter-greylist')){return '/usr/sbin/milter-greylist';}
		if(is_file('/usr/bin/milter-greylist')){return '/usr/bin/milter-greylist';}	
		}
	
	
	
	public function DIRSIZE_KO($directory){
		$cmd=$this->find_program("du")." -s $directory";
		echo "$cmd\n";
		
		exec($cmd,$datas);

		if(preg_match("#^([0-9]+)\s+#",$datas[0],$re)){
			return trim($re[1]);
		}
		return 0;
	}	
	
	public function LOCATE_PHP5_BIN(){
	  if(is_file('/usr/bin/php5')){return '/usr/bin/php5';}
	  if(is_file('/usr/bin/php')){return '/usr/bin/php';}
	  if(is_file('/usr/local/apache-groupware/php5/bin/php')){return '/usr/local/apache-groupware/php5/bin/php';}
	  $this->events("Unable to stat php5 !");
	  
	}
	public function LOCATE_CUPS_ACCEPT(){
 		if(is_file('/usr/sbin/cupsaccept')){return '/usr/sbin/cupsaccept';}
    	if(is_file('/usr/sbin/accept')){return '/usr/sbin/accept';}
	}
 //############################################################################# 	

	public function PATH_LDAP_DIRECTORY_DATA(){
		$file=file_get_contents("/etc/ldap/slapd.conf");
		$datas=explode("\n",$file);
		if(!is_array($datas)){
			$this->events("Unable to stat /etc/ldap/slpad.conf !");
			return null;}
		while (list ($num, $ligne) = each ($datas) ){
			if(preg_match("#^directory\s+(.+)#",$ligne,$re)){
				return trim($re[1]);
			}
			
		}
		
		$this->events("Unable to stat directory in /etc/ldap/slpad.conf ! in ".count($datas)." rows");
	}
	
	public function find_program($strProgram) {
	$key=md5($strProgram);
	if(function_exists("apc_store")){
		$value=trim(apc_fetch("LOCATE_PROGRAM_$key"));
   		if($value<>null){
   			if(basename($value)==$strProgram){return trim($value);}
		}
	}
	  if($_SESSION["find_program"]["$strProgram"]<>null){return $_SESSION["find_program"]["$strProgram"];}
	  if($GLOBALS["find_program"]["$strProgram"]<>null){return $GLOBALS["find_program"]["$strProgram"];}	
		
		$value=$this->internal_find_program($strProgram);
		if(function_exists("apc_store")){ 
			apc_store("LOCATE_PROGRAM_$key",$value);
	  		return $value;
	 	}	
		
	 	$_SESSION["find_program"]["$strProgram"]=$value;
	    $GLOBALS["find_program"]["$strProgram"]=$value;
    	return $value;
		
	}
	
	private function internal_find_program($strProgram){
	  global $addpaths;	
	  $arrPath = array('/bin', '/sbin', '/usr/bin', '/usr/sbin', '/usr/local/bin', '/usr/local/sbin');
	  if (addPaths !== false) {
	    $addpaths = explode(',', addPaths);
	    $arrPath = array_merge($arrPath, $addpaths);
	  }
	  if (function_exists("is_executable")) {
	    foreach($arrPath as $strPath) {
	      $strProgrammpath = $strPath . "/" . $strProgram;
	      if (is_executable($strProgrammpath)) {
	      	  return $strProgrammpath;
	      }
	    }
	  } else {
	   	return strpos($strProgram, '.exe');
	  }
	}
	
	public function POSTCONF_SET($key,$value){
	$value=str_replace('$','\$',$value);
	if($GLOBALS["MEM_POSTCONF"]<>null){$postconf=$GLOBALS["MEM_POSTCONF"];}else{$postconf=$this->find_program("postconf");$GLOBALS["MEM_POSTCONF"]=$postconf;}
		shell_exec("$postconf -e \"$key = $value\" >/dev/null 2>&1");
	}
	
	
	public function POSTCONF_GET($value){
		if($GLOBALS["MEM_POSTCONF"]<>null){$postconf=$GLOBALS["MEM_POSTCONF"];}else{$postconf=$this->find_program("postconf");$GLOBALS["MEM_POSTCONF"]=$postconf;}
		exec("$postconf -h $value",$results);
		return trim(implode("\n",$results));
		
	}
	
	public function POSTCONF_MULTI_GET($instance,$value){
	if($GLOBALS["MEM_postmulti"]<>null){$postmulti=$GLOBALS["MEM_postmulti"];}else{$postmulti=$this->find_program("postmulti");$GLOBALS["MEM_postmulti"]=$postmulti;}
		
		if($GLOBALS["DEBUG"]){echo "POSTCONF_MULTI_GET:: postmulti=$postmulti\n";}
		exec("$postmulti -i postfix-$instance -x postconf -h $value",$results);
		return trim(implode("\n",$results));
		
	}

	public function POSTCONF_MULTI_SET($instance,$key,$value){
		$value=str_replace('$','\$',$value);
		if($GLOBALS["MEM_postmulti"]<>null){$postmulti=$GLOBALS["MEM_postmulti"];}else{$postmulti=$this->find_program("postmulti");$GLOBALS["MEM_postmulti"]=$postmulti;}
		$cmd="$postmulti -i postfix-$instance -x postconf -e \"$key=$value\" >/dev/null 2>&1";
		if($GLOBALS["DEBUG"]){echo "POSTCONF_MULTI_SET:: $cmd\n";}
		shell_exec("$postmulti -i postfix-$instance -x postconf -e \"$key=$value\" >/dev/null 2>&1");
	}		
	
	public function POSTFIX_MULTI_PID($instance){
		if($GLOBALS["DEBUG"]){echo "POSTFIX_MULTI_PID:: $instance\n";}
		$path=$this->POSTCONF_MULTI_GET($instance,"queue_directory")."/pid/master.pid";
		$data=trim(@file_get_contents($path));
		return trim($data);
	}	
	
	public function POSTFIX_PID(){
		$path=$this->POSTCONF_GET("queue_directory")."/pid/master.pid";
		$data=trim(@file_get_contents($path));
		return trim($data);
	}
	
	
	public function POSTFIX_VERSION(){return $this->POSTCONF_GET("mail_version");}
	
	
	
  private function events($text){
		error_log("$text in ". __FILE__);
		}

	public function dirdir($path){
		$dir_handle = @opendir($path);
		if(!$dir_handle){
			writelogs_framework("Unable to dir $path",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return array();
		}
			$count=0;	
			while ($file = readdir($dir_handle)) {
			  if($file=='.'){continue;}
			  if($file=='..'){continue;}
			  if(!is_dir("$path/$file")){continue;}
			  $array["$path/$file"]=$file;
			  }
			if(!is_array($array)){return array();}
			@closedir($dir_handle);
			return $array;
	}
	
public	function getDirectories($directory){
	$array_items = array();
	if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				if (is_dir($directory. "/" . $file)) {
						$array_items = array_merge($array_items, $this->getDirectories($directory. "/" . $file));
					
					$file = $directory . "/" . $file;
					$array_items[] = preg_replace("/\/\//si", "/", $file);
				} else {
					$file = $directory . "/" . $file;
					$array_items[] = preg_replace("/\/\//si", "/", $file);
				}
			}
		}
		closedir($handle);
	}
	return $array_items;
	

} 

function DirRecursiveFiles($dir){
    $files = Array();
    $file_tmp= glob($dir.'*',GLOB_MARK | GLOB_NOSORT);

    foreach($file_tmp as $item){
        if(substr($item,-1)!=DIRECTORY_SEPARATOR)
            $files[] = $item;
        else
            $files = array_merge($files,$this->DirRecursiveFiles($item));
    }

    return $files;
}
	
function DirFiles($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){
		return array();
	}
	$count=0;	
	while ($file = readdir($dir_handle)) {
	  if($file=='.'){continue;}
	  if($file=='..'){continue;}
	  if(!is_file("$path/$file")){continue;}
		
			$array[$file]=$file;
			continue;
		
		
	  }
	if(!is_array($array)){return array();}
	@closedir($dir_handle);
	return $array;
}	


	public function dir_count_files_recursive($path) {
	    $files = 0;
	    $dir = opendir($path);
	    if (!$dir){return 0;}
	    while (($file = readdir($dir)) !== false) {
	        if ($file[0] == '.'){ continue; }
	        if (is_dir($path.$file)){        
	            $files += $this->dir_count_files_recursive($path.$file.DIRECTORY_SEPARATOR);
	        }
	        else {
	            $files++;
	        }
	    }    
	    closedir($dir);
	    return $files;
		}




	public function PIDOF($binpath){
		exec($this->find_program('pidof')." -s $binpath",$re);
		while (list ($num, $ligne) = each ($re) ){	
			if(preg_match("#[0-9]+#",$ligne)){return trim($ligne);}
		}
		
	}
	
	public function PIDOF_PATTERN($pattern){
		$pattern=str_replace("/","\/",$pattern);
		$pattern=$this->find_program('pgrep')." -l -f \"$pattern\"";
		exec($pattern,$re);
		
		if(preg_match("#pgrep -l#",$re[0])){$re[0]=$re[1];}
		if(preg_match("#^([0-9]+)\s+#",$re[0],$i)){return $i[1];}
		
	}	
	
	public function PROCESS_TIME_TO_LIVE($pid){
		exec("/usr/share/artica-postfix/bin/procstat",$array);
		while (list ($num, $ligne) = each ($array) ){
			if(preg_match("#start_time:.+?\((.+?\.#",$ligne,$re)){
				$mins=($re[1]/60);
				return $mins;
			}
		}
	}
	
	
	public function MYSQL_SOCKET_PATH(){
		$cnf=MYSQL_MYCNF_PATH();
		$ini=new Bs_IniHandler($cnf);
		$return=$ini->get("mysqld","socket");
		if(trim($return==null)){return "/var/run/mysqld/mysqld.sock";}
	}
	
	private function MYSQL_MYCNF_PATH(){
		if(is_file('/etc/mysql/my.cnf')){return('/etc/mysql/my.cnf');}
  		if(is_file('/etc/my.cnf')){return '/etc/my.cnf';}

	} 
	
	public function FSTAB_GETDEV($targetPointPath){
		$datas=explode("\n",@file_get_contents("/etc/fstab"));
		while (list ($num, $ligne) = each ($datas) ){
			if(strpos($ligne,$targetPointPath)>0){
				if(preg_match("#(.+?)\s+#",$ligne,$re)){
				return $re[1];
			}}
		}
		
	}
	
	public function FSTAB_ACL($dev,$aclenabled=0){
	
			$rows=explode("\n",@file_get_contents("/etc/fstab"));
			
			$dev_pattern=str_replace("/","\/",$dev);
			if(!is_array($rows)){
				writelogs_framework("no rows, abort",__FUNCTION__,__FILE__);
				return ;}
			while (list ($index, $line) = each ($rows) ){
						  //  <file system> <mount point>   <type>  <options>       <dump>  <pass>
				if(preg_match("#$dev_pattern\s+(.+?)\s+(.*?)\s+(.*?)\s+(.+)#",$line,$re)){
					$row_index=$index;
					$mount_path=$re[1];
					$type=$re[2];
					$option=$re[3];
					$other=$re[4];
					break;
				}
			}
			
			
			if($mount_path==null){return;}
			$options=explode(",",$option);
			
			while (list ($i, $token) = each ($options) ){
				$tokens[$token]=true;
			}
			
			writelogs_framework("OPTIONS=$option mount on $mount",__FUNCTION__,__FILE__);
			if($aclenabled==0){
				unset($tokens["acl"]);
				unset($tokens["user_xattr"]);
				$tune2fs_cmd="-acl";
			}else{
				$tokens["acl"]=true;
				$tokens["user_xattr"]=true;
				$tune2fs_cmd="+acl";
			}
			while (list ($token,$i) = each ($tokens) ){
				$final[]=$token;
			}	
			
			$new_options=implode(",",$final);
			writelogs_framework("$dev\t$mount_path\t$type\t$new_options\t$other",__FUNCTION__,__FILE__,__LINE__);
			reset($rows);
			$rows[$row_index]="$dev\t$mount_path\t$type\t$new_options\t$other";
			@file_put_contents("/etc/fstab",@implode("\n",$rows));
			$mount=$this->find_program("mount");
			exec("$mount -o remount $dev");	
			$tune2fs=$this->find_program("tune2fs");
			if($tune2fs<>null){
				exec("$tune2fs -o $tune2fs_cmd $dev 2>&1",$rs);
				writelogs_framework("$tune2fs -o $tune2fs_cmd $dev",__FUNCTION__,__FILE__,__LINE__);
				writelogs_framework(implode(",",$rs),__FUNCTION__,__FILE__,__LINE__);
				
			}
			
			}
	
	
	public function DISK_GET_TYPE($dev_path){
		$dev_path=str_replace("/","\/",$dev_path);
		$blkid=$this->find_program("blkid");
		$tmp=$this->FILE_TEMP();
		shell_exec("$blkid >$tmp 2>&1");
		$datas=explode("\n",@file_get_contents($tmp));
		@unlink($tmp);
		while (list ($num, $ligne) = each ($datas) ){
			if(preg_match("#^$dev_path.+?TYPE=\"(.+?)\"#",$ligne,$re)){
				return $re[1];
			}
			
		}
		
		
		
	}
	
	

	
	
	
	
	
	
}

function BuildingExecStatus($text,$pourc){
	$ini=new Bs_IniHandler("/usr/share/artica-postfix/ressources/logs/exec.status.ini");
	$ini->set("status","text","$text");
	$ini->set("status","pourc","$pourc");
	$ini->set("status","gpourc","$magor_pourc");
	$ini->saveFile("/usr/share/artica-postfix/ressources/logs/exec.status.ini");
	@chmod("/usr/share/artica-postfix/ressources/logs/exec.status.ini",0755);
	error_log(__FUNCTION__.":: status $pourc% $text");
	
}
function BuildingExecRightStatus($text,$pourc){
	$file="/usr/share/artica-postfix/ressources/logs/exec-right.status.ini";
	$ini=new Bs_IniHandler($file);
	$ini->set("status","text","$text");
	$ini->set("status","pourc","$pourc");
	$ini->saveFile($file);
	@chmod($file,0755);	
}


function cpulimit($forced=0){
	$unix=new unix();
	if($GLOBALS["cpulimit_path"]==null){
		$GLOBALS["cpulimit_path"]=$unix->find_program("cpulimit");
	}
	if($GLOBALS["cpulimit_path"]==null){return null;}
	if($forced==0){
		$cpu=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/cpulimit"));
		if($cpu==null){$cpu=0;}
	}else{
		$cpu=$forced;
	}
	if($cpu==0){return null;}
	$pid=getmypid();
	$cmd="{$GLOBALS["cpulimit_path"]} -p $pid -l $cpu -z >>/var/log/cpulimit 2>&1 &";
	if($GLOBALS["VERBOSE"]){writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);}
	shell_exec($cmd);
	
}


function GET_INFO_DAEMON($key){
   	if(function_exists("apc_store")){$value=apc_fetch($key);if($value<>null){return trim($value);}}	
	if($GLOBALS["GET_INFO_DAEMON"][$key]<>null){return $GLOBALS["GET_INFO_DAEMON"][$key];}
	$result=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/$key"));
	$GLOBALS["GET_INFO_DAEMON"][$key]=$result;
	if(function_exists("apc_store")){apc_store($key, $result);}	
	return $result;
}

function cpulimitProcessName($processName){
	$unix=new unix();
	$cpulimit=$unix->find_program("cpulimit");
	if($cpulimit==null){return null;}
	$cpu=GET_INFO_DAEMON('cpulimit');
	if($cpu==null){$cpu=0;}
	if($cpu==0){return null;}
	$cmd="$cpulimit -e $processName -l $cpu &";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	
}

function cpulimitProcessNameKill($processName){
	$unix=new unix();
	$cpulimit=$unix->find_program("cpulimit");
	if($cpulimit==null){return null;}	
	$cmd=$unix->find_program("pgrep")." -l -f \"$cpulimit -e $processName\"";
	exec($cmd,$results);
	if(!is_array($results)){return null;}
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#([0-9]+)\s+(.+)#",$ligne,$re)){
			if(preg_match("#cpulimit#",$re[2])){
				if($re[1]>2){shell_exec($unix->find_program("kill")," -9 {$re[1]}");}	
			}
		}
	}
	
}


function writelogs_framework($text=null,$function=null,$file_source=null,$line=null){
if($file_source==null){$file_source= __FILE__ ;}
if(posix_getuid()==0){$prefix="[DAEMON]";}else{$prefix="[CONSOLE]";}
if(function_exists("getmypid")){$pid=getmypid();}

if($line<>null){$line=" line $line";}
$file_source=str_replace("/usr/share/artica-postfix/","",$file_source);
$file_source=str_replace("/home/dtouzeau/developpement/artica-postfix/","",$file_source);
if($GLOBALS["VERBOSE"]){echo "[$pid] $prefix::$function:: $text in $file_source$line\n";}
$text="[$pid] $prefix::$function:: $text in $file_source$line";
error_log("$text");
writeFrameOtherlogs('/var/log/artica-postfix/framework.debug',$text);
}
function writeFrameOtherlogs($file,$text=null){
			@mkdir(dirname($file));
		    $logFile=$file;
		    if(!is_dir(dirname($logFile))){mkdir(dirname($logFile));}
   		if (is_file($logFile)) { 
   			$size=filesize($logFile);
		    	if($size>1000000){unlink($logFile);}
   		}
		 $logFile=str_replace("//","/",$logFile);
		$f = @fopen($logFile, 'a');
		
		@fwrite($f, "$text\n");
		@fclose($f);
}


class services_status{
	
	function services_status(){
		
	}
	
	public function artica_backup_status(){
		$unix=new unix();
		$pid=$unix->PIDOF("artica-backup");
		
		
	}
	
}




?>