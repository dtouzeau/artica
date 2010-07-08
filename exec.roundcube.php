<?php
if(!is_file(dirname(__FILE__) . '/ressources/settings.inc')){die("Unable to stat ".dirname(__FILE__) . '/ressources/settings.inc');}
include_once(dirname(__FILE__) . '/ressources/settings.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
$bd="roundcubemail";
$GLOBALS["MYSQL_DB"]=$bd;	



if($argv[1]=="--sieverules"){plugin_sieverules();die();}
if($argv[1]=="--calendar"){plugin_calendar();die();}
if($argv[1]=="--database"){check_databases($bd);die();}

if(!$_GLOBAL["roundcube_installed"]){die("Roundcube is not installed, aborting");}


$mailhost=$_GLOBAL["fqdn_hostname"];
echo "Get user list....\n";

$ldap=new clladp();
$users=$ldap->Hash_GetALLUsers();

echo count($users)." user(s) to scan\n";

$q=new mysql();
while (list ($num, $val) = each ($users) ){
		
		$user_id=GetidFromUser($bd,$num);
		echo " user \"$num\" $val user_id=$user_id\n";
		$sql="UPDATE identities SET `email`='$val', `reply-to`='$val' WHERE name='$num';";
		echo $sql."\n";
		$q->QUERY_SQL($sql,$bd);	
		if(!$q->ok){echo "$sql \n$q->mysql_error\n";}	
		
		if($user_id==0){
			CreateRoundCubeUser($bd,$num,$val,'127.0.0.1');
			$user_id=GetidFromUser($bd,$num);
		}
		
		if($user_id==0){continue;}
		$identity_id=GetidentityFromuser_id($bd,$user_id);
		if($identity_id==0){
			CreateRoundCubeIdentity($bd,$user_id,$num,$val);
			$identity_id=GetidentityFromuser_id($bd,$user_id);
			}
		
		if($identity_id==0){continue;}
		
		$count=$count+1;
		UpdateRoundCubeIdentity($bd,$identity_id,$val);
		
		
		
		
}

echo "\n\nsuccess ".$count." user(s) updated\n";

function GetidFromUser($bd,$uid){
	$sql="SELECT user_id FROM users where username='$uid'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,$bd);
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$userid[]=$ligne["user_id"];
	}
	
	if(!is_array($userid)){return 0;}else{return $userid[0];}
			
	
	
}


function CreateRoundCubeUser($bd,$user_id,$email,$mailhost){
	$date=date('Y-m-d H:i:s');
	$sql="INSERT INTO `users` (`username`, `mail_host`, `language`,`created`) VALUES 
	('$user_id','127.0.0.1','en_US','$date');
	";
	$q=new mysql();
	$q->QUERY_SQL($sql,$bd);
	if(!$q->ok){
		echo $q->mysql_error."\n";
	}
	
}

function CreateRoundCubeIdentity($bd,$user_id,$num,$val){
	$sql="INSERT INTO `identities` (`user_id`, `del`, `standard`, `name`, `organization`, `email`, `reply-to`) VALUES ('$user_id','0','1','$num','','$val','$val');";
	$q=new mysql();
	$q->QUERY_SQL($sql,$bd);
}


function GetidentityFromuser_id($bd,$user_id){
	$sql="SELECT identity_id FROM identities where user_id='$user_id'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,$bd);
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$id[]=$ligne["identity_id"];
	}
	
	if(!is_array($id)){return 0;}else{return $id[0];}
}

function UpdateRoundCubeIdentity($bd,$identity_id,$val){
	echo "Update $identity_id to $val\n";
	$sql="UPDATE identities SET email='$val', `reply-to`='$val' WHERE identity_id='$identity_id'";
	$q=new mysql();
	$q->QUERY_SQL($sql,$bd);
	
}

function plugin_sieverules(){
	$users=new usersMenus();
	if(!$users->roundcube_installed){
		writelogs("RoundCube is not installed",__FUNCTION__,__FILE__,__LINE__);
		return ;
	}
	
	$dir=$users->roundcube_folder."/plugins";
	if(!is_dir($dir)){
		writelogs("Unable to stat directory '$dir'",__FUNCTION__,__FILE__,__LINE__);
		return ;		
	}
	writelogs("Roundcube plugins: $dir",__FUNCTION__,__FILE__,__LINE__);
	
	writelogs("remove $dir/sieverules",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("/bin/rm -rf $dir/sieverules >/dev/null 2>&1");
	writelogs("Installing in $dir/sieverules",__FUNCTION__,__FILE__,__LINE__);
	@mkdir("$dir/sieverules",0755,true);
	shell_exec("/bin/cp -rf /usr/share/artica-postfix/bin/install/roundcube/sieverules/* $dir/sieverules/");
	shell_exec("/bin/chmod -R 755 $dir/sieverules");
	writelogs("Installing in $dir/sieverules done...",__FUNCTION__,__FILE__,__LINE__);
	
	///usr/share/roundcube/plugins
	
	
}

function plugin_calendar(){
	$users=new usersMenus();
	if(!$users->roundcube_installed){
		writelogs("RoundCube is not installed",__FUNCTION__,__FILE__,__LINE__);
		return ;
	}
	
	$dir=$users->roundcube_folder."/plugins";
	if(!is_dir($dir)){
		writelogs("Unable to stat directory '$dir'",__FUNCTION__,__FILE__,__LINE__);
		return ;		
	}
	writelogs("Roundcube plugins: $dir",__FUNCTION__,__FILE__,__LINE__);
	
	writelogs("remove $dir/sieverules",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("/bin/rm -rf $dir/calendar >/dev/null 2>&1");
	writelogs("Installing in $dir/calendar",__FUNCTION__,__FILE__,__LINE__);
	@mkdir("$dir/calendar",0755,true);
	shell_exec("/bin/cp -rf /usr/share/artica-postfix/bin/install/roundcube/calendar/* $dir/calendar/");
	shell_exec("/bin/chmod -R 755 $dir/calendar");
	
	
	$sql="CREATE TABLE `events` (
  `event_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `start` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `end` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `summary` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(255) NOT NULL DEFAULT '',
  `all_day` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY(`event_id`),
  CONSTRAINT `user_id_fk_events` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`user_id`)
    /*!40008
      ON DELETE CASCADE
      ON UPDATE CASCADE */
)";
	
	$q=new mysql();
	$q->QUERY_SQL($sql,$GLOBALS["MYSQL_DB"]);
	
	writelogs("Installing in $dir/calendar done...",__FUNCTION__,__FILE__,__LINE__);
	
	///usr/share/roundcube/plugins
	
	
}

function check_databases($bd){
	$q=new mysql();
	$q->checkRoundCubeTables($bd);
	
}
	

	
	
	

	
	

			
			









?>