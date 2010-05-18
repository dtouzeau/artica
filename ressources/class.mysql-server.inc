<?php
include_once(dirname(__FILE__). '/class.ini.inc');
include_once(dirname(__FILE__). '/class.sockets.inc');
include_once(dirname(__FILE__). '/class.users.menus.inc');

class mysqlserver{
	
	var $main_array=array();
	var $mysql_version_string=0;
	var $mysqlvbin=0;
	
	function mysqlserver(){
		$this->load();
		$users=new usersMenus();
		$this->mysql_version_string=$users->mysqld_version;
		
	if(posix_getuid()==0){
			echo "Starting......: Mysql version.......:$this->mysql_version_string\n";
			
		}
		
		if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)#",$this->mysql_version_string,$re)){
			if(strlen($re[1])==1){$re[1]=$re[1].'0';}
			if(strlen($re[2])==1){$re[2]=$re[2].'0';}
			if(strlen($re[3])==1){$re[3]=$re[3].'0';}
			$this->mysqlvbin=$re[1].$re[2].$re[3];
		}
		
	}
	
	
	private function load(){
		$ini=new Bs_IniHandler();
		$sock=new sockets();
		$datas=$sock->GET_INFO("MysqlParameters");
		$ini->loadString($datas);
		$this->main_array=$ini->_params["MYSQL"];
		$this->BuildDefault();
		
	}
	
	private function BuildDefault(){
		if($this->main_array["skip_external_locking"]==null){$this->main_array["skip_external_locking"]='yes';}
		if($this->main_array["bind-address"]==null){$this->main_array["bind-address"]='127.0.0.1';}
		if($this->main_array["tmp_table_size"]==null){$this->main_array["tmp_table_size"]='64';}
		if($this->main_array["table_cache"]==null){$this->main_array["table_cache"]='512';}		
		if($this->main_array["max_allowed_packet"]==null){$this->main_array["max_allowed_packet"]='100';}
		if($this->main_array["sort_buffer_size"]==null){$this->main_array["sort_buffer_size"]='8';}
		if($this->main_array["key_buffer_size"]==null){$this->main_array["key_buffer_size"]='256';}
		if($this->main_array["table_open_cache"]==null){$this->main_array["table_open_cache"]='256';}
		if($this->main_array["read_buffer_size"]==null){$this->main_array["read_buffer_size"]='1';}
		if($this->main_array["read_rnd_buffer_size"]==null){$this->main_array["read_rnd_buffer_size"]='4';}
		if($this->main_array["myisam_sort_buffer_size"]==null){$this->main_array["myisam_sort_buffer_size"]='64';}
		if($this->main_array["query_cache_size"]==null){$this->main_array["query_cache_size"]='16';}		
	}
	
	public function save(){
		$ini=new Bs_IniHandler();
		$ini->_params["MYSQL"]=$this->main_array;
		$sock=new sockets();
		$datas=$ini->toString();
		$sock->SET_INFO("MysqlParameters",$datas);
		$sock->getFrameWork("cmd.php?restart-mysql=yes");
		
	}
	
	function Mysql_low_config(){
		$this->main_array["max_allowed_packet"]=1;
		$this->main_array["key_buffer"]=16;
		$this->main_array["table_cache"]=1;
		if(posix_getuid()==0){
			echo "Starting......: Mysql binary version:$this->mysqlvbin\n";
		}
		
		
		$conf="[client]\n";
		$conf=$conf ."port=3306\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		$conf=$conf ."\n";
		$conf=$conf ."[mysqld_safe]\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		$conf=$conf ."nice=0\n";
		$conf=$conf ."\n";
		$conf=$conf ."[mysqld]\n";
		$conf=$conf ."user=mysql\n";
		$conf=$conf ."pid-file=/var/run/mysqld/mysqld.pid\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		$conf=$conf ."port=3306\n";
		if($this->main_array["bind-address"]<>null){
			$conf=$conf ."bind-address={$this->main_array["bind-address"]}\n";
		}		
		$conf=$conf ."basedir=/usr\n";
		$conf=$conf ."datadir=/var/lib/mysql\n";
		$conf=$conf ."tmpdir=/tmp\n";
		$conf=$conf. "skip-locking\n";
		$conf=$conf. "skip-bdb\n";
		$conf=$conf. "skip-innodb\n";		
		$conf=$conf. "key_buffer = 16K\n";
		$conf=$conf. "max_allowed_packet = {$this->main_array["max_allowed_packet"]}M\n";
		$conf=$conf. "table_cache = {$this->main_array["table_cache"]}\n";
		$conf=$conf. "sort_buffer_size = 16K\n";
		$conf=$conf. "read_buffer_size = 16K\n";
		$conf=$conf. "read_rnd_buffer_size = 1K\n";
		$conf=$conf. "net_buffer_length = 1K\n";
		$conf=$conf. "thread_stack =16K\n";
		$conf=$conf ."wait_timeout=7200\n";
		$conf=$conf ."connect_timeout=10\n";
		$conf=$conf ."max_connect_errors=1000\n";
		$conf=$conf ."query_cache_type=1\n";
		$conf=$conf ."query_cache_limit = 1048576\n";
		$conf=$conf ."query_cache_size = 1048576\n";
		$conf=$conf ."query_cache_type = 1\n";	
		$conf=$conf ."default-storage-engine=MyISAM\n";		
		$conf=$conf ."\n";
		$conf=$conf ."[mysqldump]\n";	
		$conf=$conf ."quick\n";	
		$conf=$conf ."max_allowed_packet = 16K\n";	
		$conf=$conf ."[isamchk]\n";
		$conf=$conf ."key_buffer = 16KM\n";
		$conf=$conf ."sort_buffer_size = 16K\n";
		$conf=$conf ."\n";
		$conf=$conf ."[myisamchk]\n";
		$conf=$conf ."key_buffer = 16K\n";
		$conf=$conf ."sort_buffer_size = 16K\n";
		$conf=$conf ."\n";
		$conf=$conf ."[mysqlhotcopy]\n";
		$conf=$conf ."interactive-timeout\n";	
		return $conf;
		
	}
	
	
	
	function BuildConf(){
		
		$conf="[client]\n";
		$conf=$conf ."port=3306\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		$conf=$conf ."\n";
		$conf=$conf ."[mysqld_safe]\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		$conf=$conf ."nice=0\n";
		$conf=$conf ."\n";
		$conf=$conf ."[mysqld]\n";
		$conf=$conf ."user=mysql\n";
		$conf=$conf ."pid-file=/var/run/mysqld/mysqld.pid\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		$conf=$conf ."port=3306\n";
		$conf=$conf ."basedir=/usr\n";
		$conf=$conf ."datadir=/var/lib/mysql\n";
		$conf=$conf ."tmpdir=/tmp\n";
		
	
		if($this->main_array["skip_external_locking"]=="yes"){
			$conf=$conf ."skip-external-locking\n";
		}
		if($this->main_array["bind-address"]<>null){
			$conf=$conf ."bind-address={$this->main_array["bind-address"]}\n";
		}
		
		if($this->main_array["key_buffer"]<>null){
			$conf=$conf ."key_buffer={$this->main_array["key_buffer"]}M\n";
		}
		$conf=$conf ."myisam-recover=BACKUP\n";
		$conf=$conf ."expire_logs_days=10\n";
		$conf=$conf ."max_binlog_size=100M\n";
		$conf=$conf ."back_log=75\n";
		
		if($this->main_array["read_rnd_buffer_size"]<>null){
			$conf=$conf ."read_rnd_buffer_size={$this->main_array["read_rnd_buffer_size"]}M\n";
		}

		if($this->main_array["sort_buffer"]<>null){
			$conf=$conf ."sort_buffer={$this->main_array["sort_buffer"]}M\n";
		}	
		if(posix_getuid()==0){
			echo "Starting......: Mysql binary version :$this->mysqlvbin\n";
			
		}
		if($this->mysqlvbin>0){
		if($this->mysqlvbin<501030){
			if($this->main_array["table_cache"]<>null){
				$conf=$conf ."table_cache={$this->main_array["table_cache"]}\n";
		}}}	
		
		if($this->main_array["tmp_table_size"]<>null){
			$conf=$conf ."tmp_table_size={$this->main_array["tmp_table_size"]}M\n";
		}
		
		if($this->main_array["innodb_additional_mem_pool_size"]<>null){
			$conf=$conf ."innodb_additional_mem_pool_size={$this->main_array["innodb_additional_mem_pool_size"]}M\n";
		}	
		
		if($this->main_array["max_allowed_packet"]<>null){
			$conf=$conf ."max_allowed_packet={$this->main_array["max_allowed_packet"]}M\n";
		}	
		
		if($this->main_array["max_connections"]<>null){
			$conf=$conf ."max_connections={$this->main_array["max_connections"]}\n";
		}

		if($this->main_array["myisam_sort_buffer_size"]<>null){
			$conf=$conf ."myisam_sort_buffer_size={$this->main_array["myisam_sort_buffer_size"]}M\n";
		}		
		
		if($this->main_array["sort_buffer_size"]<>null){
			$conf=$conf ."sort_buffer_size={$this->main_array["sort_buffer_size"]}M\n";
		}				
		
		if($this->main_array["net_buffer_length"]<>null){
			$conf=$conf ."net_buffer_length={$this->main_array["net_buffer_length"]}M\n";
		}	

		if($this->main_array["join_buffer_size"]<>null){
			$conf=$conf ."join_buffer_size={$this->main_array["join_buffer_size"]}M\n";
		}			
		
		if($this->main_array["read_buffer_size"]<>null){
			$conf=$conf ."read_buffer_size={$this->main_array["read_buffer_size"]}M\n";
		}	
		if($this->main_array["thread_cache_size"]<>null){
			$conf=$conf ."thread_cache_size={$this->main_array["thread_cache_size"]}\n";
		}	
		if($this->main_array["query_cache_limit"]<>null){
			$conf=$conf ."query_cache_limit={$this->main_array["query_cache_limit"]}M\n";
		}		
		
		if($this->main_array["key_buffer_size"]<>null){
			$conf=$conf ."key_buffer_size={$this->main_array["key_buffer_size"]}M\n";
		}		
		if($this->main_array["query_cache_size"]<>null){
			$conf=$conf ."query_cache_size={$this->main_array["query_cache_size"]}M\n";
		}	
		if($this->mysqlvbin>0){
			if($this->mysqlvbin>501030){
				if($this->main_array["table_cache"]<>null){
				if($mysql->main_array["table_cache"]==0){$mysql->main_array["table_cache"]=1;}
				$conf=$conf ."table_open_cache={$mysql->main_array["table_cache"]}\n";
			}}
		}			

		$conf=$conf ."wait_timeout=7200\n";
		$conf=$conf ."connect_timeout=10\n";
		$conf=$conf ."max_connect_errors=1000\n";
		$conf=$conf ."query_cache_type=1\n";
		$conf=$conf ."default-storage-engine=MyISAM\n";
		$conf=$conf ."\n";
		$conf=$conf ."[mysqldump]\n";
		$conf=$conf ."quick\n";
		$conf=$conf ."quote-names\n";
		$conf=$conf ."max_allowed_packet=16M\n";
		$conf=$conf ."\n";
		$conf=$conf ."[mysql]\n";
		$conf=$conf ."\n";
		
		return $conf;
	}
	
	
}
?>