<?php
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

/*
$conf=$conf . $this->BuildLdapSettings("VirtualAliasMailingTable",null,"(&(objectClass=MailingAliasesTable)(cn=%s))","MailingListAddress");
$conf=$conf . $this->BuildLdapSettings("VirtualAliasMapsTable",null,"(&(objectClass=userAccount)(mailAlias=%s))","mail");
$conf=$conf . $this->BuildLdapSettings("AliasMapsTable",null,"(&(objectClass=userAccount)(uid=%u))","mail");
$conf=$conf . $this->BuildLdapSettings("CatchAllAliasMaps","cn=catch-all,cn=artica","(&(objectclass=AdditionalPostfixMaps)(cn=%s))","");
$conf=$conf . $this->BuildLdapSettings("VirtualMailManMaps",null,"(&(objectClass=ArticaMailManRobots)(cn=%s))","cn");
$conf=$conf . $this->BuildLdapSettings("RelaisDomainsTable",$ldap->suffix,"(&(objectclass=PostFixRelayDomains)(cn=%s))","cn");
$conf=$conf . $this->BuildLdapSettings("RecipientBccMaps",null,"(&(objectClass=UserArticaClass)(mail=%s))","RecipientToAdd");
*/


$sock=new sockets();
$unix=new unix();
$GLOBALS["EnablePostfixMultiInstance"]=$sock->GET_INFO("EnablePostfixMultiInstance");
$GLOBALS["EnableBlockUsersTroughInternet"]=$sock->GET_INFO("EnableBlockUsersTroughInternet");
$GLOBALS["postconf"]=$unix->find_program("postconf");
$GLOBALS["postmap"]=$unix->find_program("postmap");
$GLOBALS["newaliases"]=$unix->find_program("newaliases");
$GLOBALS["postalias"]=$unix->find_program("postalias");
$GLOBALS["postfix"]=$unix->find_program("postfix");

if(!is_file($GLOBALS["postfix"])){die();}

if(!Build_pid_func(__FILE__,$argv[1])){
	echo "Starting......: Already executed\n"; 
}
$ldap=new clladp();
if($ldap->ldapFailed){
	echo "Starting......: failed connecting to ldap server $ldap->ldap_host\n"; 
	die();
}

if($argv[1]=="--bcc"){
	recipient_bcc_maps();
	recipient_bcc_maps_build();
	sender_bcc_maps();
	sender_bcc_maps_build();
	shell_exec("{$GLOBALS["postfix"]} reload >/dev/null 2>&1");
	die();
}


if($argv[1]=="--transport"){
	transport_maps_build();
	transport_maps();
	relais_domains_build();
	relay_domains();	
	shell_exec("{$GLOBALS["postfix"]} reload >/dev/null 2>&1");
	die();}
	
if($argv[1]=="--aliases"){
	maillings_table();
	aliases_users();
	aliases();
	virtual_alias_maps();
	aliases_maps();
	shell_exec("{$GLOBALS["postfix"]} reload >/dev/null 2>&1");
	die();}
		
if($argv[1]=="--smtp-passwords"){
	sender_canonical_maps_build();
	sender_canonical_maps();
	sender_dependent_relayhost_maps();
	smtp_sasl_password_maps();
	shell_exec("{$GLOBALS["postfix"]} reload >/dev/null 2>&1");
	die();}	

$GLOBALS["virtual_alias_maps"]=array();
$GLOBALS["alias_maps"]=array();
$GLOBALS["relay_domains"]=array();
$GLOBALS["bcc_maps"]=array();
$GLOBALS["transport_maps"]=array();


maillings_table();
aliases_users();
aliases();
aliases_maps();
virtual_alias_maps();

relais_domains_build();
relay_domains();

relay_recipient_maps_build();

recipient_canonical_maps_build();
recipient_canonical_maps();

sender_canonical_maps_build();
sender_canonical_maps();


sender_dependent_relayhost_maps();
smtp_sasl_password_maps();

recipient_bcc_maps();
recipient_bcc_maps_build();

sender_bcc_maps();
sender_bcc_maps_build();

local_recipient_maps();

mydestination_build();
mydestination();

transport_maps_build();
transport_maps();

relayhost();


shell_exec("{$GLOBALS["postfix"]} reload >/dev/null 2>&1");


function recipient_bcc_maps(){
	
$ldap=new clladp();
	$filter="(&(objectClass=UserArticaClass)(RecipientToAdd=*))";
	$attrs=array("RecipientToAdd","mail");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["mail"][0];
		$RecipientToAdd=$hash[$i]["recipienttoadd"][0];
		$GLOBALS["bcc_maps"][]="$mail\t$RecipientToAdd";
		
	}	
	echo "Starting......: ". count($GLOBALS["bcc_maps"])." recipient(s) BCC\n"; 	
}
function sender_bcc_maps(){
$ldap=new clladp();
	$filter="(&(objectClass=UserArticaClass)(SenderBccMaps=*))";
	$attrs=array("SenderBccMaps","mail");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["mail"][0];
		$senderbccmaps=$hash[$i]["senderbccmaps"][0];
		$GLOBALS["sender_bcc_maps"][]="$mail\t$senderbccmaps";
		
	}	
	echo "Starting......: ". count($GLOBALS["sender_bcc_maps"])." Sender(s) BCC\n"; 	
}
function sender_bcc_maps_build(){
if(!is_array($GLOBALS["sender_bcc_maps"])){
		shell_exec("{$GLOBALS["sender_bcc_maps"]} -e \"sender_bcc_maps = \" >/dev/null 2>&1");
		return null;
		}
	
		shell_exec("{$GLOBALS["postconf"]} -e \"sender_bcc_maps =hash:/etc/postfix/sender_bcc\" >/dev/null 2>&1");
		echo "Starting......: Compiling Sender(s) BCC\n"; 
		@file_put_contents("/etc/postfix/sender_bcc",implode("\n",$GLOBALS["sender_bcc_maps"]));
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/sender_bcc >/dev/null 2>&1");	
}



function maillings_table(){
	$ldap=new clladp();
	$filter="(&(objectClass=MailingAliasesTable)(cn=*))";
	$attrs=array("cn","MailingListAddress","MailingListAddressGroup");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	
	for($i=0;$i<$hash["count"];$i++){
		$cn=$hash[$i]["cn"][0];
		$MailingListAddressGroup=$hash[$i]["mailinglistaddressgroup"][0];
		for($t=0;$t<$hash[$i]["mailinglistaddress"]["count"];$t++){
			$mailinglistaddress[$hash[$i]["mailinglistaddress"][$t]]=$hash[$i]["mailinglistaddress"][$t];
		}
		
		if($MailingListAddressGroup==1){
			$uid=$ldap->uid_from_email($cn);
			$user=new user($uid);
			$array=$user->MailingGroupsLoadAliases();
			while (list ($num, $ligne) = each ($array) ){
    			if($ligne==null){continue;}  	
    			$mailinglistaddress[$ligne]=$ligne;
    		}	
		}
		
		if(is_array($mailinglistaddress)){
				while (list ($num, $ligne) = each ($mailinglistaddress) ){
					$final[]=$num;
				}
				$GLOBALS["virtual_alias_maps"][]="$cn\t". implode(",",$final);
			}	
			
		unset($final);
		unset($mailinglistaddress);
		$MailingListAddressGroup=0;
	}
	
	

	
	$filter="(&(objectClass=ArticaMailManRobots)(cn=*))";
	$attrs=array("cn","MailManAliasPath");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	$sock=new sockets();
	if($sock->GET_INFO("MailManEnabled")==1){$GLOBALS["MAILMAN"]=true;}else{
		$GLOBALS["MAILMAN"]=false;
		return;
	}
	
	if($hash["count"]>0){$GLOBALS["MAILMAN"]=true;}else{$GLOBALS["MAILMAN"]=false;}
	

}


function catch_all(){
	$ldap=new clladp();
	$filter="(&(objectClass=AdditionalPostfixMaps)(cn=*))";
	$attrs=array("cn","MailingListAddress");
	$dn="cn=catch-all,cn=artica,$ldap->suffix";
	
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	
	for($i=0;$i<$hash["count"];$i++){
		$cn=$hash[$i]["cn"][0];
		for($t=0;$t<$hash[$i][strtolower("CatchAllPostfixAddr")]["count"];$t++){
			$GLOBALS["virtual_alias_maps"][]="@$cn\t{$hash[$i][strtolower("CatchAllPostfixAddr")][$t]}";
		}
	}

}

function relais_domains_build(){
$ldap=new clladp();
	$filter="(&(objectClass=PostFixRelayDomains)(cn=*))";
	$attrs=array("cn");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$GLOBALS["relay_domains"][]=$hash[$i]["cn"][0]."\tOK";
		
	}	
	
	echo "Starting......: ". count($GLOBALS["relay_domains"])." relay domain(s)\n";
}
	
function relay_domains(){
	if(!is_array($GLOBALS["relay_domains"])){
		shell_exec("{$GLOBALS["postconf"]} -e \"relay_domains = \" >/dev/null 2>&1");
		return null;
		}

	shell_exec("{$GLOBALS["postconf"]} -e \"relay_domains =hash:/etc/postfix/relay_domains\" >/dev/null 2>&1");
	@file_put_contents("/etc/postfix/relay_domains",implode("\n",$GLOBALS["relay_domains"]));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/relay_domains >/dev/null 2>&1");	
		
}

function mydestination_build(){
$ldap=new clladp();
	$filter="(&(objectClass=organizationalUnit)(associatedDomain=*))";
	$attrs=array("associatedDomain");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);

	for($i=0;$i<$hash["count"];$i++){
		for($t=0;$t<$hash[$i]["associateddomain"]["count"];$t++){
			$GLOBALS["mydestination"][]=$hash[$i][strtolower("associatedDomain")][$t]."\tOK";
		}
		
	}
	echo "Starting......: ".count($GLOBALS["mydestination"])." Local domain(s)\n"; 	
}


function recipient_bcc_maps_build(){
if(!is_array($GLOBALS["bcc_maps"])){
		shell_exec("{$GLOBALS["postconf"]} -e \"recipient_bcc_maps = \" >/dev/null 2>&1");
		return null;
		}
	
		shell_exec("{$GLOBALS["postconf"]} -e \"recipient_bcc_maps =hash:/etc/postfix/recipient_bcc\" >/dev/null 2>&1");
		echo "Starting......: Compiling Recipient(s) BCC\n";
		@file_put_contents("/etc/postfix/recipient_bcc",implode("\n",$GLOBALS["bcc_maps"]));
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/recipient_bcc >/dev/null 2>&1");	
}

function mydestination(){
	if(!is_array($GLOBALS["mydestination"])){
		shell_exec("{$GLOBALS["postconf"]} -e \"mydestination = \" >/dev/null 2>&1");
		shell_exec("{$GLOBALS["virtual_mailbox_domains"]} -e \"virtual_mailbox_domains = \" >/dev/null 2>&1");
		return null;
		}
	
		shell_exec("{$GLOBALS["postconf"]} -e \"mydestination =hash:/etc/postfix/mydestination\" >/dev/null 2>&1");
		shell_exec("{$GLOBALS["postconf"]} -e \"virtual_mailbox_domains =\" >/dev/null 2>&1");
		@file_put_contents("/etc/postfix/mydestination",implode("\n",$GLOBALS["mydestination"]));
		
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/mydestination >/dev/null 2>&1");
}	
	








function aliases_users(){
	$ldap=new clladp();
	$filter="(&(objectClass=userAccount)(uid=*))";
	$attrs=array("uid","mail");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);

	for($i=0;$i<$hash["count"];$i++){
		$uid=$hash[$i]["uid"][0];
		for($t=0;$t<$hash[$i]["mail"]["count"];$t++){
			$GLOBALS["alias_maps"][]="$uid\t{$hash[$i]["mail"][$t]}";
			$GLOBALS["virtual_mailbox"]="{$hash[$i]["mail"][$t]}\t$uid";
		}
	}

	$filter="(&(objectClass=transportTable)(cn=*@*))";
	$attrs=array("cn");
	$dn="cn=PostfixRobots,cn=artica,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$cn=$hash[$i]["cn"][0];
		$GLOBALS["alias_maps"][]="$cn\tx";
	}

}


function local_recipient_maps(){
if(!is_array($GLOBALS["local_recipient_maps"])){
		shell_exec("{$GLOBALS["postconf"]} -e \"local_recipient_maps = \" >/dev/null 2>&1");
		echo "Starting......: No recipients maps\n"; 
		return null;
		}	

echo "Starting......: ". count($GLOBALS["local_recipient_maps"])." local recipient(s)\n"; 
shell_exec("{$GLOBALS["postconf"]} -e \"local_recipient_maps =hash:/etc/postfix/local_recipients\" >/dev/null 2>&1");
file_put_contents("/etc/postfix/local_recipients",implode("\n",$GLOBALS["local_recipient_maps"]));
shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/local_recipients >/dev/null 2>&1");		
	
}

function virtual_alias_maps(){
if(!is_array($GLOBALS["virtual_alias_maps"])){
		shell_exec("{$GLOBALS["postconf"]} -e \"virtual_alias_maps = \" >/dev/null 2>&1");
		echo "Starting......: No virtual aliases\n"; 
		return null;
		}	

	echo "Starting......: ". count($GLOBALS["virtual_alias_maps"])." virtual aliase(s)\n"; 		
	shell_exec("{$GLOBALS["postconf"]} -e \"virtual_alias_maps =hash:/etc/postfix/virtual\" >/dev/null 2>&1");		
	@file_put_contents("/etc/postfix/virtual",implode("\n",$GLOBALS["virtual_alias_maps"]));
	echo "Starting......: compiling virtual aliase database /etc/postfix/virtual\n"; 
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/virtual >/dev/null 2>&1");		
}


function aliases_maps(){
if(!is_array($GLOBALS["alias_maps"])){
		shell_exec("{$GLOBALS["postconf"]} -e \"aliases_maps = \" >/dev/null 2>&1");
		shell_exec("{$GLOBALS["postconf"]} -e \"virtual_mailbox_maps = \" >/dev/null 2>&1");
		echo "Starting......: No aliases\n"; 
		virtual_alias_maps();
		return null;
		
		}	
		
	if($GLOBALS["MAILMAN"]=true){
		echo "Starting......: Building mailman aliase(s)\n";
		if(mailman_aliases()){
			$hash_mailman=",hash:{$GLOBALS["MAILMAN_ALIASES"]}";
			$hash_mailman_virtual=",hash:/var/lib/mailman/data/virtual-mailman";
		}
	}
	
	
	
		
	
		
		echo "Starting......: ". count($GLOBALS["alias_maps"])." aliase(s)\n"; 
		shell_exec("{$GLOBALS["postconf"]} -e \"alias_maps =hash:/etc/postfix/aliases$hash_mailman\" >/dev/null 2>&1");
		shell_exec("{$GLOBALS["postconf"]} -e \"alias_database =hash:/etc/postfix/aliases\" >/dev/null 2>&1");
		@file_put_contents("/etc/postfix/aliases",implode("\n",$GLOBALS["alias_maps"]));
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/aliases >/dev/null 2>&1");	
		virtual_alias_maps();
	
}

function mailman_aliases(){
	if(!is_file("/var/lib/mailman/bin/genaliases")){
		echo "Starting......: Unable to locate genaliases tool\n";
		return false;
	}
	
	shell_exec("/var/lib/mailman/bin/genaliases");
	
	if(is_file("/var/lib/mailman/data/aliases")){
		$GLOBALS["MAILMAN_ALIASES"]="/var/lib/mailman/data/aliases";
		
	
	if(is_file("/var/lib/mailman/data/virtual-mailman")){
		$GLOBALS["MAILMAN_VIRTUAL"]="/var/lib/mailman/data/virtual-mailman";
		return true;
	}
	}
}


function aliases(){
	$ldap=new clladp();
	$filter="(&(objectClass=userAccount)(mailAlias=*))";
	$attrs=array("mail","mailAlias");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);

	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["mail"][0];
		for($t=0;$t<$hash[$i]["mailalias"]["count"];$t++){
			$GLOBALS["virtual_alias_maps"][]="{$hash[$i]["mailalias"][$t]}\t$mail";
		}
	}	
	
}

function transport_maps(){
	if(!is_array($GLOBALS["transport_maps"])){
		shell_exec("{$GLOBALS["postconf"]} -e \"transport_maps =\" >/dev/null 2>&1");
	}
	
	while (list ($num, $ligne) = each ($GLOBALS["transport_maps"]) ){
		if($ligne==null){continue;}
		$array[]="$num\t$ligne";
		
	}
	
	echo "Starting......: ". count($array)." routings rules\n"; 
	
	if(is_array($GLOBALS["transport_maps_AT"])){
	while (list ($num, $ligne) = each ($GLOBALS["transport_maps_AT"]) ){
		if($ligne==null){continue;}
		$array[]="$num\t$ligne";
		
	}}
	
	
	
	@file_put_contents("/etc/postfix/transport",implode("\n",$array));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/transport >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"transport_maps = hash:/etc/postfix/transport\" >/dev/null 2>&1");
	
}

function relay_recipient_maps_build(){
	shell_exec("{$GLOBALS["postconf"]} -e \"relay_recipient_maps =\" >/dev/null 2>&1");

}


function recipient_canonical_maps_build(){
	$ldap=new clladp();
	$filter="(&(objectClass=RecipientCanonicalMaps)(cn=*))";
	$attrs=array("cn","MailAlternateAddress");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);

	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["cn"][0];
		$canonical=$hash[$i][strtolower("MailAlternateAddress")][0];
		$GLOBALS["recipient_canonical_maps"][]="$mail\t$canonical";
	}		
}

function smtp_sasl_password_maps_build(){
	$ldap=new clladp();
	
	$filter="(&(objectClass=PostfixSmtpSaslPaswordMaps)(cn=*))";
	$attrs=array("cn","SmtpSaslPasswordString");
	$dn="cn=smtp_sasl_password_maps,cn=artica,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["cn"][0];
		$value=$hash[$i][strtolower("SmtpSaslPasswordString")][0];
		if($value==null){continue;}
		$GLOBALS["smtp_sasl_password_maps"][]="$mail\t$value";
	}

	$filter="(&(objectClass=SenderDependentSaslInfos)(cn=*))";
	$attrs=array("cn","SenderCanonicalRelayPassword");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["cn"][0];
		$value=$hash[$i][strtolower("SenderCanonicalRelayPassword")][0];
		if($value==null){continue;}
		$GLOBALS["smtp_sasl_password_maps"][]="$mail\t$value";
	}	
	

}

function smtp_sasl_password_maps(){
	smtp_sasl_password_maps_build();
	if(!is_array($GLOBALS["smtp_sasl_password_maps"])){
		echo "Starting......: 0 smtp password rule(s)\n"; 
		shell_exec("{$GLOBALS["postconf"]} -e \"smtp_sasl_password_maps =\" >/dev/null 2>&1");
	}

	echo "Starting......: ". count($GLOBALS["smtp_sasl_password_maps"])." smtp password rule(s)\n"; 
	@file_put_contents("/etc/postfix/smtp_sasl_password",implode("\n",$GLOBALS["smtp_sasl_password_maps"]));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/smtp_sasl_password >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"smtp_sasl_password_maps = hash:/etc/postfix/smtp_sasl_password\" >/dev/null 2>&1");
}

function sender_dependent_relayhost_maps_build(){
	$ldap=new clladp();
	$filter="(&(objectClass=SenderDependentRelayhostMaps)(cn=*))";
	$attrs=array("cn","SenderRelayHost");
	$dn="cn=Sender_Dependent_Relay_host_Maps,cn=artica,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["cn"][0];
		$value=$hash[$i][strtolower("SenderRelayHost")][0];
		if($value==null){continue;}
		$GLOBALS["sender_dependent_relayhost_maps"][]="$mail\t$value";
	}
	
	$filter="(&(objectClass=userAccount)(mail=*))";
	$attrs=array("mail","AlternateSmtpRelay");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["mail"][0];
		$value=$hash[$i][strtolower("AlternateSmtpRelay")][0];
		if($value==null){continue;}
		$GLOBALS["sender_dependent_relayhost_maps"][]="$mail\t$value";
	}	
	
	$filter="(&(objectClass=SenderDependentSaslInfos)(cn=*))";
	$attrs=array("cn","SenderCanonicalRelayHost");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["cn"][0];
		$value=$hash[$i][strtolower("SenderCanonicalRelayHost")][0];
		if($value==null){continue;}
		$GLOBALS["sender_dependent_relayhost_maps"][]="$mail\t$value";
	}	

}

function sender_dependent_relayhost_maps(){
	sender_dependent_relayhost_maps_build();
	if(!is_array($GLOBALS["sender_dependent_relayhost_maps"])){
		echo "Starting......: 0 sender dependent relayhost rule(s)\n"; 
		shell_exec("{$GLOBALS["postconf"]} -e \"sender_dependent_relayhost_maps =\" >/dev/null 2>&1");
	}

	echo "Starting......: ". count($GLOBALS["sender_dependent_relayhost_maps"])." sender dependent relayhost rule(s)\n"; 
	@file_put_contents("/etc/postfix/sender_dependent_relayhost",implode("\n",$GLOBALS["sender_dependent_relayhost_maps"]));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/sender_dependent_relayhost >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"sender_dependent_relayhost_maps = hash:/etc/postfix/sender_dependent_relayhost\" >/dev/null 2>&1");
}


function sender_canonical_maps_build(){
	$ldap=new clladp();
	$filter="(&(objectClass=userAccount)(mail=*))";
	$attrs=array("mail","SenderCanonical");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);

	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["mail"][0];
		$canonical=$hash[$i][strtolower("SenderCanonical")][0];
		if($canonical==null){continue;}
		$GLOBALS["sender_canonical_maps"][]="$mail\t$canonical";
	}	
	
			
}
function sender_canonical_maps(){
	if(!is_array($GLOBALS["sender_canonical_maps"])){
		echo "Starting......: 0 sender retranslation rule(s)\n"; 
		shell_exec("{$GLOBALS["postconf"]} -e \"sender_canonical_maps =\" >/dev/null 2>&1");
	}

	echo "Starting......: ". count($GLOBALS["sender_canonical_maps"])." sender retranslation rule(s)\n"; 
	@file_put_contents("/etc/postfix/sender_canonical",implode("\n",$GLOBALS["sender_canonical_maps"]));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/sender_canonical >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"sender_canonical_maps = hash:/etc/postfix/sender_canonical\" >/dev/null 2>&1");
}


function recipient_canonical_maps(){
	if(!is_array($GLOBALS["recipient_canonical_maps"])){
		echo "Starting......: 0 recipient retranslation rule(s)\n"; 
		shell_exec("{$GLOBALS["postconf"]} -e \"recipient_canonical_maps =\" >/dev/null 2>&1");
		return;
	}

	echo "Starting......: ". count($GLOBALS["recipient_canonical_maps"])." recipient retranslation rule(s)\n"; 
	@file_put_contents("/etc/postfix/recipient_canonical",implode("\n",$GLOBALS["recipient_canonical_maps"]));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/recipient_canonical >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"recipient_canonical_maps = hash:/etc/postfix/recipient_canonical\" >/dev/null 2>&1");
	
}



function transport_maps_build(){
	$ldap=new clladp();
	$filter="(&(objectClass=transportTable)(cn=*))";
	$attrs=array("cn","transport");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);	
	for($i=0;$i<$hash["count"];$i++){
		$domain=$hash[$i]["cn"][0];
		$transport=$hash[$i]["transport"][0];
		//$transport=str_replace("relay:","smtp:",$transport);
		
		if(substr($domain,0,1)=="@"){$domain=substr($domain,1,strlen($domain));}
		$GLOBALS["transport_maps"]["$domain"]="$transport";
		
		if(strpos("  $domain","@")==0){$domain="@$domain";}
		$GLOBALS["transport_maps_AT"]["$domain"]="$transport";
		

	}
	
	
  	$dn="cn=artica_smtp_sync,cn=artica,$ldap->suffix";
  	$filter="(&(objectClass=InternalRecipients)(cn=*))";	
  	$attrs=array("cn","ArticaSMTPSenderTable");	
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);	
	for($i=0;$i<$hash["count"];$i++){
		$email=$hash[$i]["cn"][0];
		$transport=$hash[$i][strtolower("ArticaSMTPSenderTable")][0];
		$GLOBALS["transport_maps"]["$email"]="$transport";

	}  
}

function relayhost(){
	$sock=new sockets();
	$PostfixRelayHost=trim($sock->GET_INFO("PostfixRelayHost"));
	if($PostfixRelayHost==null){
		shell_exec("{$GLOBALS["postconf"]} -e \"relayhost =\" >/dev/null 2>&1");
		return null;
	}
	$tools=new DomainsTools();
	$hash=$tools->transport_maps_explode($PostfixRelayHost);
	if($hash[2]==null){$hash[2]=25;}
	$PostfixRelayHost_pattern="[{$hash[1]}]:{$hash[2]}";
	echo "Starting......: Relay host: $PostfixRelayHost_pattern\n"; 
	$ldap=new clladp();
	$sasl_password_string=$ldap->sasl_relayhost($hash[1]);
	if($sasl_password_string<>null){
		$relayhost_hash="$PostfixRelayHost_pattern\t$sasl_password_string\n";
		@file_put_contents("/etc/postfix/sasl_passwd",$relayhost_hash);
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/sasl_passwd >/dev/null 2>&1");
		shell_exec("{$GLOBALS["postconf"]} -e \"smtp_sasl_password_maps = hash:/etc/postfix/sasl_passwd\" >/dev/null 2>&1");
	}
	
	
	shell_exec("{$GLOBALS["postconf"]} -e \"relayhost =$PostfixRelayHost_pattern\" >/dev/null 2>&1");
	
}


?>
