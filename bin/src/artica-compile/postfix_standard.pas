unit postfix_standard;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
Classes, SysUtils,Process,Unix,RegExpr in 'RegExpr.pas',zsystem,global_conf,logs;

  type
  Tpostfix_standard=class


private
       GLOBAL_INI:MyConf;
       zSystem:Tsystem;
       procedure ShowScreen(line:string);



public
      constructor Create();
      procedure Free();
      procedure Configure_postfix_ldap();
      procedure ChangeAutoIpInterfaces;
      debug:boolean;
END;

implementation

constructor Tpostfix_standard.Create();
begin
       forcedirectories('/etc/artica-postfix');
       GLOBAL_INI:=MyConf.Create();
       zSystem:=Tsystem.Create();
end;

procedure Tpostfix_standard.Free();
begin
  zSystem.Free;
end;
//###############################################################################
procedure Tpostfix_standard.ChangeAutoIpInterfaces;
var eth0,eth1,postfix_config,eth_config,ChangeAutoInterface:string;
D:boolean;
begin
   ChangeAutoInterface:=GLOBAL_INI.get_INFOS('ChangeAutoInterface');
   D:=GLOBAL_INI.COMMANDLINE_PARAMETERS('debug');
   if  D then showscreen('ChangeAutoIpInterfaces:: ChangeAutoInterface parameter ="' + ChangeAutoInterface + '"');
   
   if length(ChangeAutoInterface)=0 then begin
         if  D then showscreen('ChangeAutoIpInterfaces:: standard method =>follow eth0 and/or eth1');
         eth0:=GLOBAL_INI.GetIPInterface('eth0');
         eth1:=GLOBAL_INI.GetIPInterface('eth1');
   
         if length(eth0)>0 then begin
            eth_config:=eth0;
            if length(eth1)>0 then eth_config:=eth_config + ',' + eth1;
           end else begin
             if length(eth1)>0 then begin
              eth_config:=eth1;
            end;
           end;
     eth_config:='127.0.0.1,' + eth_config;
    end else begin
        if ChangeAutoInterface='all' then eth_config:=GLOBAL_INI.SYSTEM_GET_ALL_LOCAL_IP() else eth_config:=GLOBAL_INI.SYSTEM_GET_LOCAL_IP(ChangeAutoInterface);
    end;
    
    if  D then showscreen('ChangeAutoIpInterfaces:: eth_config="' + eth_config + '"');
    postfix_config:=trim(GLOBAL_INI.ExecPipe('/usr/sbin/postconf -h inet_interfaces'));

    if postfix_config<>eth_config then begin
       if debug then writeln('Change ip inet interfaces from ' + postfix_config + ' to ' + eth_config);
       if  D then showscreen('ChangeAutoIpInterfaces:: Change ip inet interfaces from ' + postfix_config + ' to ' + eth_config);
       fpsystem(GLOBAL_INI.POSFTIX_POSTCONF_PATH() + '-e inet_interfaces=' +  eth_config);
       fpsystem('/etc/init.d/postfix restart');
    end else begin
        if  D then showscreen('ChangeAutoIpInterfaces:: nothing to do');
    end;

end;
//###############################################################################
procedure Tpostfix_standard.Configure_postfix_ldap();
var
   admin,password,suffix,target_file,eth0,eth1:string;
   FileDatas:TstringList;eth_config:string;

begin




    admin:=GLOBAL_INI.get_LDAP('admin');
    password:=GLOBAL_INI.get_LDAP('password');
    eth0:=GLOBAL_INI.GetIPInterface('eth0');
    eth1:=GLOBAL_INI.GetIPInterface('eth1');
    suffix:=GLOBAL_INI.get_LDAP('suffix');
       GLOBAL_INI.debug:=true;
     writeln('Postfix configuration using infos : ');
     writeln('**************************************');
     writeln('suffix (root database)......:' + suffix);
     writeln('admin.......................:' + admin);
     writeln('password....................:' + password);
     writeln('Use local mailboxes.........:' + GLOBAL_INI.get_MANAGE_MAILBOXES());
     writeln('eth0........................:' + eth0);
     writeln('eth1........................:' + eth1);
     writeln('');
     
     if length(eth0)>0 then begin
           eth_config:=eth0;
           if length(eth1)>0 then eth_config:=eth_config + ',' + eth1;
     end else begin
         if length(eth1)>0 then begin
            eth_config:=eth1;
         end;
     end;
     
     
     if length(eth_config)>0 then begin
             fpsystem(GLOBAL_INI.POSFTIX_POSTCONF_PATH() + '-e inet_interfaces=127.0.0.1,' + eth_config);
     end;

    writeln('Creating - updating sasl password for postfix smtp client.. ');
    target_file:='/etc/postfix/hash-smtp_sasl_password_maps.cf';
    FileDatas:=TstringList.Create;
    FileDatas.SaveToFile(target_file);
    FileDatas.Free;

    fpsystem(GLOBAL_INI.POSFTIX_POSTCONF_PATH() + '-e smtp_sasl_auth_enable=yes');
    fpsystem(GLOBAL_INI.POSFTIX_POSTCONF_PATH() + '-e smtp_sasl_password_maps=hash:' + target_file);
    fpsystem(GLOBAL_INI.POSFTIX_POSTCONF_PATH() + '-e ''smtp_sasl_security_options=noplaintext,noanonymous''');
    fpsystem('/usr/sbin/postmap ' + target_file);

    

    

    target_file:='/etc/postfix/ldap-virtual_alias_maps.cf';
    writeln('Creating - updating:' + target_file + '...');

    FileDatas:=TstringList.Create;
    FileDatas.Add('server_host = localhost');
    FileDatas.Add('server_port = 389');
    FileDatas.Add('bind = yes');
    FileDatas.Add('version = 3');
    FileDatas.Add('search_base =' + suffix);
    FileDatas.Add('bind_dn =' + 'cn=' + admin + ',' + suffix);
    FileDatas.Add('bind_pw =' + password);
    FileDatas.Add('##########################################################');
    FileDatas.Add('query_filter =(&(objectClass=userAccount)(mailAlias=%s))');
    FileDatas.Add('result_attribute =mail');
    FileDatas.SaveToFile(target_file);
    FileDatas.Free;
    fpsystem(GLOBAL_INI.POSFTIX_POSTCONF_PATH() + '-e virtual_alias_maps=ldap:/etc/postfix/ldap-virtual_alias_maps.cf');
    
    
    target_file:='/etc/postfix/ldap-virtual_aliases_mta.cf';
    writeln('Creating - updating:' + target_file + '...');
    FileDatas:=TstringList.Create;
    FileDatas.Add('server_host = localhost');
    FileDatas.Add('server_port = 389');
    FileDatas.Add('bind = yes');
    FileDatas.Add('version = 3');
    FileDatas.Add('search_base =' + suffix);
    FileDatas.Add('bind_dn =' + 'cn=' + admin + ',' + suffix);
    FileDatas.Add('bind_pw =' + password);
    FileDatas.Add('##########################################################');
    FileDatas.Add('query_filter =(&(objectclass=organizationalUnit)(VirtualDomainsMapsMTA=%s))');
    FileDatas.Add('result_attribute =VirtualDomainsMapsMTA');
    FileDatas.SaveToFile(target_file);
    FileDatas.Free;

    target_file:='/etc/postfix/ldap-smtpd_sender_login_maps.cf';
    writeln('Creating - updating:' + target_file + '...');
    FileDatas:=TstringList.Create;
    FileDatas.Add('server_host = localhost');
    FileDatas.Add('server_port = 389');
    FileDatas.Add('bind = yes');
    FileDatas.Add('version = 3');
    FileDatas.Add('search_base =' + suffix);
    FileDatas.Add('bind_dn =' + 'cn=' + admin + ',' + suffix);
    FileDatas.Add('bind_pw =' + password);
    FileDatas.Add('##########################################################');
    FileDatas.Add('query_filter =(&(mail=%s)(uid=%u))');
    FileDatas.Add('result_attribute =uid');
    FileDatas.SaveToFile(target_file);
    FileDatas.Free;
    fpsystem(GLOBAL_INI.POSFTIX_POSTCONF_PATH() + '-e smtpd_sender_login_maps=ldap:'+ target_file);
    
    target_file:='/etc/postfix/ldap-Sender_Dependent_Relay_host_Maps.cf';
    writeln('Creating - updating:' + target_file + '...');
    FileDatas:=TstringList.Create;
    FileDatas.Add('server_host = localhost');
    FileDatas.Add('server_port = 389');
    FileDatas.Add('bind = yes');
    FileDatas.Add('version = 3');
    FileDatas.Add('search_base = cn=Sender_Dependent_Relay_host_Maps,cn=artica,' + suffix);
    FileDatas.Add('bind_dn =' + 'cn=' + admin + ',' + suffix);
    FileDatas.Add('bind_pw =' + password);
    FileDatas.Add('##########################################################');
    FileDatas.Add('query_filter =(&(objectclass=senderDependentRelayhostMaps)(SenderRelayHost=*)(cn=%s))');
    FileDatas.Add('result_attribute =SenderRelayHost');
    FileDatas.SaveToFile(target_file);
    FileDatas.Free;
    fpsystem(GLOBAL_INI.POSFTIX_POSTCONF_PATH() + '-e sender_dependent_relay_host_maps=ldap:'+ target_file);
    
    target_file:='/etc/postfix/ldap-smtp_tls_policy_maps.cf';
    writeln('Creating - updating:' + target_file + '...');
    FileDatas:=TstringList.Create;
    FileDatas.Add('server_host = localhost');
    FileDatas.Add('server_port = 389');
    FileDatas.Add('bind = yes');
    FileDatas.Add('version = 3');
    FileDatas.Add('search_base = cn=smtp_tls_policy_maps,cn=artica,' + suffix);
    FileDatas.Add('bind_dn =' + 'cn=' + admin + ',' + suffix);
    FileDatas.Add('bind_pw =' + password);
    FileDatas.Add('##########################################################');
    FileDatas.Add('query_filter =(&(objectclass=SmtpTlsPolicyMaps)(SmtpTlsPolicyMapsValue=*)(cn=%s))');
    FileDatas.Add('result_attribute =SmtpTlsPolicyMapsValue');
    FileDatas.SaveToFile(target_file);
    FileDatas.Free;
    fpsystem(GLOBAL_INI.POSFTIX_POSTCONF_PATH() + '-e smtp_tls_policy_maps=ldap:'+ target_file);



    
    //target_file:='/etc/postfix/ldap-virtual_aliases_LocalDomains.cf';
    //writeln('Creating - updating:' + target_file + '...');
    //FileDatas:=TstringList.Create;
    //FileDatas.Add('server_host = localhost');
    //FileDatas.Add('server_port = 389');
    //FileDatas.Add('bind = yes');
    //FileDatas.Add('version = 3');
    //FileDatas.Add('search_base =' + suffix);
    //FileDatas.Add('bind_dn =' + 'cn=' + admin + ',' + suffix);
    //FileDatas.Add('bind_pw =' + password);
    //FileDatas.Add('##########################################################');
    //FileDatas.Add('query_filter =(&(objectclass=organizationalUnit)(associatedDomain=%s))');
    //FileDatas.Add('result_attribute =associatedDomain');
    //FileDatas.SaveToFile(target_file);
    //FileDatas.Free;
    //fpsystem(POSFTIX_POSTCONF_PATH() + '-e virtual_alias_domains=ldap:/etc/postfix/ldap-virtual_aliases_LocalDomains.cf');
    

    
    
    target_file:='/etc/postfix/ldap-virtual_transport_maps.cf';
    writeln('Creating - updating:' + target_file);
    fpsystem(GLOBAL_INI.POSFTIX_POSTCONF_PATH() + '-e transport_maps=ldap:'+ target_file);
    fpsystem(GLOBAL_INI.POSFTIX_POSTCONF_PATH() + '-e virtual_transport_maps=ldap:'+ target_file);
    fpsystem(GLOBAL_INI.POSFTIX_POSTCONF_PATH() + '-e relais_domain=ldap:'+ target_file);
    FileDatas:=TstringList.Create;
    FileDatas.Add('server_host = localhost');
    FileDatas.Add('server_port = 389');
    FileDatas.Add('bind = yes');
    FileDatas.Add('version = 3');
    FileDatas.Add('search_base =' + suffix);
    FileDatas.Add('bind_dn =' + 'cn=' + admin + ',' + suffix);
    FileDatas.Add('bind_pw =' + password);
    FileDatas.Add('##########################################################');
    FileDatas.Add('query_filter =(&(objectClass=transportTable)(cn=%d))');
    FileDatas.Add('result_attribute=transport');
    FileDatas.SaveToFile(target_file);
    FileDatas.Free;
    

    
    
    if GLOBAL_INI.get_MANAGE_MAILBOXES()='yes' then begin
    target_file:='/etc/postfix/ldap-virtual_mailbox_domains.cf';
    writeln('Creating - updating:' + target_file + '...');
    fpsystem(GLOBAL_INI.POSFTIX_POSTCONF_PATH() + '-e virtual_mailbox_domains=ldap:'+ target_file);
    FileDatas:=TstringList.Create;
    FileDatas.Add('server_host = localhost');
    FileDatas.Add('server_port = 389');
    FileDatas.Add('bind = yes');
    FileDatas.Add('version = 3');
    FileDatas.Add('search_base =' + suffix);
    FileDatas.Add('bind_dn =' + 'cn=' + admin + ',' + suffix);
    FileDatas.Add('bind_pw =' + password);
    FileDatas.Add('##########################################################');
    FileDatas.Add('query_filter =(&(objectClass=domainRelatedObject)(associatedDomain=%s))');
    FileDatas.Add('result_attribute=associatedDomain');
    FileDatas.SaveToFile(target_file);
    FileDatas.Free;
    
    

    target_file:='/etc/postfix/ldap-virtual_mailbox_maps.cf';
    writeln('Creating - updating:' + target_file + '...');
    fpsystem(GLOBAL_INI.POSFTIX_POSTCONF_PATH() + '-e virtual_mailbox_maps=ldap:'+ target_file);
    FileDatas:=TstringList.Create;
    FileDatas.Add('server_host = localhost');
    FileDatas.Add('server_port = 389');
    FileDatas.Add('bind = yes');
    FileDatas.Add('version = 3');
    FileDatas.Add('search_base =' + suffix);
    FileDatas.Add('bind_dn =' + 'cn=' + admin + ',' + suffix);
    FileDatas.Add('bind_pw =' + password);
    FileDatas.Add('##########################################################');
    FileDatas.Add('query_filter =(&(objectClass=userAccount)(mail=%s))');
    FileDatas.Add('result_attribute=uid');
    FileDatas.SaveToFile(target_file);
    FileDatas.Free;
    
    target_file:='/etc/postfix/ldap-sender_canonical_maps.cf';
    writeln('Creating - updating:' + target_file + '...');
    //commandline:=
    fpsystem(GLOBAL_INI.POSFTIX_POSTCONF_PATH + '-e ''sender_canonical_maps=ldap:'+ target_file + ', ldap:/etc/postfix/ldap-default-sender_canonical_maps.cf''');
    FileDatas:=TstringList.Create;
    FileDatas.Add('server_host = localhost');
    FileDatas.Add('server_port = 389');
    FileDatas.Add('bind = yes');
    FileDatas.Add('version = 3');
    FileDatas.Add('search_base =' + suffix);
    FileDatas.Add('bind_dn =' + 'cn=' + admin + ',' + suffix);
    FileDatas.Add('bind_pw =' + password);
    FileDatas.Add('##########################################################');
    FileDatas.Add('query_filter =(&(objectClass=userAccount)(mail=%s)(SenderCanonical=*))');
    FileDatas.Add('result_attribute=SenderCanonical');
    FileDatas.SaveToFile(target_file);
    FileDatas.Free;
    
    writeln('Creating - updating:/etc/postfix/ldap-default-sender_canonical_maps.cf');
    FileDatas:=TstringList.Create;
    FileDatas.Add('server_host = localhost');
    FileDatas.Add('server_port = 389');
    FileDatas.Add('bind = yes');
    FileDatas.Add('version = 3');
    FileDatas.Add('search_base =' + suffix);
    FileDatas.Add('bind_dn =' + 'cn=' + admin + ',' + suffix);
    FileDatas.Add('bind_pw =' + password);
    FileDatas.Add('##########################################################');
    FileDatas.Add('query_filter =(&(objectClass=userAccount)(mail=%s)(mail=*))');
    FileDatas.Add('result_attribute=mail');
    FileDatas.SaveToFile('/etc/postfix/ldap-default-sender_canonical_maps.cf');
    FileDatas.Free;
    
    

    

    
    

    
    
    end;
    
    

    
    writeln('Restarting postfix...');
    fpsystem('/etc/init.d/postfix restart');
    writeln('done...');

end;

//##############################################################################
procedure Tpostfix_standard.ShowScreen(line:string);
 var  logs:Tlogs;
 begin
    logs:=Tlogs.Create();
    logs.Enable_echo:=True;
    logs.logs('Tpostfix_standard::' + line);
    logs.free;

 END;
//##############################################################################





end.

