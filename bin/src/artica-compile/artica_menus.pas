unit artica_menus;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
Classes, SysUtils,Process,unix,RegExpr in 'RegExpr.pas', zsystem,
class_install,global_conf,postfix_addons,debian,logs;

  type
  Tmenus=class


private
       GLOBAL_INI:MyConf;
       install:Tclass_install;
       procedure ShowScreen(line:string);
       PROCEDURE Repository(package_name:string);
       function setup_require():boolean;
       LOG:Tlogs;
       install_only_web:boolean;

       x:char;
public
      constructor Create();
      PROCEDURE mysql_setup();
      PROCEDURE ldap_setup(restart_config:boolean);
      procedure Free();
      PROCEDURE install_Packages(notauto:boolean);
      PROCEDURE install_Packages_addon(Packagename:string);
      PROCEDURE HELP_POSTFIX();
      PROCEDURE HELP_AVESERVER();
      PROCEDURE HELP_DNSMASQ();
      PROCEDURE HELP_DSPAM();
      PROCEDURE HELP_EMAILRELAY();
      PROCEDURE Introduction();
      PROCEDURE remove_Packages_addon(Packagename:string);
      PROCEDURE MAILBOX_STORAGE();
      PROCEDURE setup(OnlyWeb:boolean);
      procedure addons_setup;
      PROCEDURE hostname_valid();
      PROCEDURE ARTICA_WEB_INSTALL();
      procedure ARTICA_WEB_ONLY_WITH_STEP();
      procedure POSTFIX_SETUP();
      procedure ROUNDCUBE_SETUP();
END;

implementation

constructor Tmenus.Create();
begin
       forcedirectories('/etc/artica-postfix');
       GLOBAL_INI:=MyConf.Create;
       install:=Tclass_install.Create();
       LOG:=TLogs.Create;

       //CheckPackages();
       
end;
PROCEDURE Tmenus.Free();
begin
   GLOBAL_INI.Free;
   install.Free;
end;
//##############################################################################

PROCEDURE Tmenus.Introduction();
var
   global_ini:myconf;
   whatis:string;
begin
 global_ini:=myconf.Create();

  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                    ARTICA  1.x INSTALLATION                    xxx');
  writeln(chr(9) + 'xxx                      For Postfix & Squid 3                     xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();
  writeln(chr(9) + ' ARTICA INSTALLER will install artica and all necessary libraries');
  writeln(chr(9) + ' on your system.(' + trim(global_ini.SYSTEM_FQDN()) + ')');
  writeln(chr(9) + ' it will check if your system store mandatories libraries');
  writeln();

  writeln(chr(9) + ' If these libraries are not installed, artica will be install');
  writeln(chr(9) + ' them itself in "/opt/artica"');
  writeln();
  writeln();
  writeln(chr(9) + ' libraries installation could take time....');
  writeln(chr(9) + ' So DONT''T WORRY, just wait compilations and installation');
  writeln(chr(9) + ' of these libraries...');
  writeln(chr(9) + ' Sometimes the compilation could failed, it his problem occur');
  writeln(chr(9) + ' just rune antother time "artica-install setup".');
  
  writeln();
  writeln(chr(9) + ' BUT BE PATIENT.... ');
  writeln(chr(9) + ' Type "ENTER" key to start the full installation');
  writeln();
  writeln();
  readln(whatis);

  global_ini.free;
  
  
end;
//##############################################################################
PROCEDURE Tmenus.ARTICA_WEB_INSTALL();
begin
    GLOBAL_INI.SYSTEM_MARK_DEB_CDROM();
    hostname_valid();
    Introduction();
    install.PROXY_INSTALL();
    install_Packages_addon('APP_MAKE');
    if FileExists('/etc/init.d/artica-postfix') then fpsystem('/etc/init.d/artica-postfix stop');
    install_Packages_addon('APP_LIBCURL');
    install.BZIP2_INSTALL();
    install.NETCAT_INSTALL();
    install.LIBCURL_INSTALL(false);
    install.BERKLEY_INSTALL();
    install.SQLITE_INSTALL();
    install.LIBSSL_INSTALL();
    install.LIBICONV_INSTALL();
    install.OPENLDAP_INSTALL(true);


if not install.ARTICA_WEB() then begin
        writeln(chr(9) + 'Error while installing apache and components...');
        writeln(chr(9) + 'Type ENTER key to end');
        readln();
        halt(0);
   end;

   install.InstallArtica();
   install.install_init_d();
end;

//##############################################################################
PROCEDURE Tmenus.MAILBOX_STORAGE();
var
   answer:string;
begin
 if ParamStr(2)='squid' then exit;
 if FileExists(GLOBAL_INI.CYRUS_DELIVER_BIN_PATH()) then exit;
  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                         MAILBOXES STORAGE                      xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();
  writeln(chr(9) + 'Artica can manage IMAP mailboxes with cyrus-imap server');
  writeln(chr(9) + 'Do you want to install IMAP mailboxes system ?');
  writeln(chr(9) + 'If you type "no", artica will switch automatically to');
  writeln(chr(9) + 'mail gateway system');
  writeln();
  writeln();
  writeln(chr(9) + 'Install cyrus-imap mailboxes storage ? [yes/no]:yes');
  readln(answer);
  if length(answer)=0 then answer:='yes';
  if answer='yes' then  begin
     install.CYRUS_INSTALL();
  end;
  
  
end;
//##############################################################################
PROCEDURE Tmenus.hostname_valid();
var
   global_ini:myconf;
   answer:string;
begin
    global_ini:=myconf.Create();
    if global_ini.SYSTEM_IS_HOSTNAME_VALID()=false then begin
       writeln();
       writeln();
       writeln('HOSTNAME -- ' + global_ini.SYSTEM_FQDN() + ' -- WARNING !! ');
       writeln('**********************************************************************');
       writeln('It seems that your system hostname "' + global_ini.SYSTEM_FQDN() + '"');
       writeln('is invalid, for this server, it is mandatory to have a "fqdn" hostname');
       writeln('(server.domain.tlb, server.mydomain.com...)');
       writeln();
       writeln('before installing, specify a fully qualified domain name server:');
       readln(answer);
       fpsystem('/bin/hostname ' + LowerCase(answer));
       global_ini.SYSTEM_SET_HOSTENAME(LowerCase(answer));
       hostname_valid();
       exit;
    
    end;
    
end;
//##############################################################################

procedure Tmenus.ARTICA_WEB_ONLY_WITH_STEP();
begin
writeln('This will install only artica-core module...');
writeln('Press [ENTER] to continue');
readln();
ARTICA_WEB_INSTALL();
halt(0);
end;
//##############################################################################

PROCEDURE Tmenus.setup(OnlyWeb:boolean);
var
   answer               :string;
   postfix              :boolean;
begin
    if install_only_web then OnLyWeb:=true;
    postfix:=true;
    GLOBAL_INI.SYSTEM_MARK_DEB_CDROM();
    if ParamStr(2)='squid' then postfix:=false;
    hostname_valid();
    Introduction();
    install.PROXY_INSTALL();
    install_Packages_addon('APP_MAKE');
    if FileExists('/etc/init.d/artica-postfix') then fpsystem('/etc/init.d/artica-postfix stop');
    install_Packages_addon('APP_LIBCURL');
    install.BZIP2_INSTALL();
    install.NETCAT_INSTALL();
    install.LIBRARIES_INSTALL();
    install.OPENLDAP_INSTALL(false);
    install.MYSQL_INSTALL();
    install.PERL_UPGRADE();
    install.BALANCE_INSTALL();
    install.RRD_INSTALL();
    install.UNRAR_INSTALL();
    install.POF_INSTALL();

    if not FileExists('/opt/artica/bin/perl') then install.PERL_UPGRADE();
    if not FileExists('/opt/artica/bin/perl') then begin
          writeln(chr(9) + 'Error while installing perl and components...');
          readln();
          halt(0);
    end;
       
        
   

   if not install.ARTICA_WEB() then begin
        writeln(chr(9) + 'Error while installing apache and components...');
        writeln(chr(9) + 'Type ENTER key to end');
        readln(answer);
        halt(0);
   end;
   
   install.InstallArtica();
   install.install_init_d();
   


      if ParamStr(2)='squid' then begin
        install.SQUID_INSTALL();
      end;



  ldap_setup(true);
  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                     Installation completed                     xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();
  writeln(chr(9) + 'You can access to artica by typing https://yourserver:9000');
  writeln(chr(9) + 'Use on logon section the username "' + GLOBAL_INI.get_LDAP('admin') + '" ');
  writeln(chr(9) + 'Use on logon section the password "' + GLOBAL_INI.get_LDAP('password') + '" ');
  writeln(chr(9) + 'Currently your mail server (postfix) is not fully configured.');
  writeln(chr(9) + 'You have to logon to artica web site, set yours domains and apply policies');
  writeln();
  writeln();
  writeln(chr(9) + '[Enter] key in order to install addons softwares');
  writeln();
  writeln();
  readln();



   addons_setup();
   if postfix then GLOBAL_INI.SASLAUTHD_STOP();
   GLOBAL_INI.LDAP_STOP();
   if postfix then GLOBAL_INI.POSTFIX_STOP();
   if postfix then GLOBAL_INI.CYRUS_DAEMON_STOP();
   fpsystem('/etc/init.d/artica-postfix restart');

end;
//##############################################################################
procedure Tmenus.POSTFIX_SETUP();
begin

    install.BOGOFILTER_INSTALL();
    install.OPENLDAP_INSTALL(false);
    install.LIBSASL_INSTALL(false,false);
    install.SENDMAIL_REMOVE();
    if install.POSTFIX_INSTALL()=false then begin
               writeln();
               writeln();
               writeln(chr(9) + ' Unable to install postfix, process cannot continue...');
               writeln();
               writeln();
               readln();
               exit();
    end;

    if not GLOBAL_INI.POSTFIX_LDAP_COMPLIANCE() then begin
           writeln('Postfix is not LDAP enabled, setup cannot continue');
           writeln('Press "Enter" to exit');
           readln();
           exit;
    end;



      if FileExists('/opt/artica/cyrus/bin/deliver') then install.CYRUS_CHECK();
      install.GEOIP_UPDATES();
      install.RECONFIGURE_MASTER_CF();
      install.POSTFIX_CONFIGURE_MAIN_CF();
      

end;
//##############################################################################
procedure Tmenus.ROUNDCUBE_SETUP();
begin
   install.ROUNDCUBE_INSTALL();

end;
//##############################################################################
procedure Tmenus.addons_setup;
    var
       answer:string;
       kas_ver,aveserver,fetchmail_ver,dnsmasq_ver,APACHE_VERSION,MAILMAN_VERSION,CYRUS_VERSION,HOTWAY_VERSION,KAV4PROXY_VERSION,ROUNDCUBE_VERSION:string;
       PUREFTPD:string;
       SPAMASS:string;
       AWSTATS :string;
       D:boolean;
       SYS:Tsystem;
       LOGS:Tlogs;
begin
  install.Free;
  install:=Tclass_install.Create();

  LOGS:=TLOGS.Create;
  LOGS.INSTALL_MODULES('APP_ARTICA','Starting all services...');
  fpsystem('/etc/init.d/artica-postfix start');
  D:=global_ini.COMMANDLINE_PARAMETERS('debug');
  kas_ver:=global_ini.KAS_VERSION();
  aveserver:=global_ini.AVESERVER_GET_VERSION();
  fetchmail_ver:=global_ini.FETCHMAIL_VERSION();
  dnsmasq_ver:=global_ini.DNSMASQ_VERSION();
  APACHE_VERSION:=global_ini.APACHE_VERSION();
  MAILMAN_VERSION:=global_ini.MAILMAN_VERSION();
  CYRUS_VERSION:=global_ini.CYRUS_VERSION();
  HOTWAY_VERSION:=global_ini.HOTWAYD_VERSION();
  KAV4PROXY_VERSION:=global_ini.KAV4PROXY_VERSION();
  ROUNDCUBE_VERSION:=global_ini.ROUNDCUBE_VERSION();
  SPAMASS:=GLOBAL_INI.SPAMASSASSIN_VERSION();
  PUREFTPD:=global_ini.PURE_FTPD_VERSION();
  AWSTATS:=global_ini.AWSTATS_VERSION();
  SYS:=TSystem.Create;
  
  if length(trim(CYRUS_VERSION))=0 then begin
     if D then writeln('CYRUS_VERSION=0 =>' + global_ini.CYRUS_DELIVER_BIN_PATH());
     if FileExists(global_ini.CYRUS_DELIVER_BIN_PATH()) then begin
          CYRUS_VERSION:='0.0.0 (service stopped)';
     end;
  end;
  
  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                         Addons softwares                       xxx');
  writeln(chr(9) + 'xxx                          advanced setup                        xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

 if length(APACHE_VERSION)>0 then begin
     writeln(chr(9)+'Infos:the web console of artica can be found here');
     writeln(chr(9)+'https://'+ global_ini.LINUX_GET_HOSTNAME + ':9000');
     writeln(chr(9)+'Username: ' + global_ini.LDAP_READ_ADMIN_NAME() + '; Password: ' + global_ini.LDAP_READ_VALUE_KEY('rootpw'));

 end;


  writeln();
  writeln();
  writeln('Installed products....');
  writeln('##############################################################');
  writeln('apache (artica).......:' + APACHE_VERSION);
  writeln('OpenLdap..............:' + global_ini.LDAP_VERSION());
  
  if FileExists('/opt/artica/sbin/squid') then begin
      writeln('Squid.................:' + global_ini.SQUID_VERSION());
      writeln('Kaspersky for squid...:' + KAV4PROXY_VERSION);
  end;
  
  
if FileExists('/usr/sbin/postconf') then begin
  writeln('Postfix...............:' + global_ini.POSTFIX_VERSION());
  writeln('Cyrus-imapd...........:' + global_ini.CYRUS_VERSION());
  writeln('Kaspersky anti-spam...:' + kas_ver);
  writeln('Kaspersky anti-virus..:' + aveserver);
  writeln('BogoFilter............:' + global_ini.BOGOFILTER_VERSION());
  writeln('Fetchmail.............:' + fetchmail_ver);
  writeln('hotwayd...............:' + dnsmasq_ver);
  writeln('mailman...............:' + MAILMAN_VERSION);
  writeln('awstats...............:' + AWSTATS);
  writeln('SpamAssassin..........:' + SPAMASS);
end;
  writeln('Dnsmasq...............:' + dnsmasq_ver);
  writeln('##############################################################');
  writeln();
  writeln();
  writeln(chr(9) +'Select products you want to install');
  writeln(chr(9) +'Type the number and press Enter key');
  writeln();
  writeln();
                                               writeln(chr(9) +'quit and finish install:......:0');
  writeln();
  if FileExists('/usr/sbin/postconf') then begin
     if length(fetchmail_ver)=0 then           writeln(chr(9) +'Fetchmail.....................:1');
     if length(HOTWAY_VERSION)=0 then          writeln(chr(9) +'hotwayd (http email poper)....:2');
  end;



  if FileExists('/usr/sbin/postconf') then begin
    if length(aveserver)=0 then                writeln(chr(9) +'Kaspersky anti-virus..........:3');
  end;

  if FileExists('/usr/sbin/postconf') then begin
     if length(kas_ver)=0 then                 writeln(chr(9) +'Kaspersky anti-spam...........:4');
  end;
  if length(dnsmasq_ver)=0 then                writeln(chr(9) +'Dnsmasq.......................:5');
  if FileExists('/usr/sbin/postconf') then begin
     if length(CYRUS_VERSION)=0 then           writeln(chr(9) +'Cyrus-imapd...................:6');
     if length(SPAMASS)=0       then           writeln(chr(9) +'Open Mail security............:21');
     

     
  end;
  
                                               writeln(chr(9) +'Admin/Password................:7');
  if length(APACHE_VERSION)=0 then             writeln(chr(9) +'apache for artica.............:8');
  if FileExists('/usr/sbin/postconf') then begin
     if length(AWSTATS)=0 then                 writeln(chr(9) +'awstats.......................:20');
     if length(MAILMAN_VERSION)=0 then         writeln(chr(9) +'mailman.......................:9');
  end;
  if FileExists('/usr/sbin/postconf') then     writeln(chr(9) +'reconfigure postfix...........:10');
  
  
  if FileExists('/opt/artica/sbin/squid') then begin
     if length(KAV4PROXY_VERSION)=0 then       writeln(chr(9) +'Kaspersky antivirus for squid.:13');
  end;
  
  if Not FileExists('/usr/sbin/postconf') then writeln(chr(9) +'Postfix mail system...........:14');
  if length(CYRUS_VERSION)>0              then begin
     if length(ROUNDCUBE_VERSION)=0 then       writeln(chr(9) +'Roundcube webmail.............:15');
  end;

  
  


  writeln();
  writeln();

   if not FileExists('/opt/artica/sbin/squid') then begin
      writeln(chr(9) +'Squid HTTP Proxy..............:16');
   end else  begin
      writeln(chr(9) +'Kaspersky For SQuid ICAP......:17');
      writeln(chr(9) +'DansGuardian..................:18');
   
   end;
  if length(PUREFTPD)=0 then                   writeln(chr(9) +'Pure-ftpd with ldap...........:19');
  writeln();
  writeln();
  writeln(chr(9) +'Install all addons............:11');
  writeln(chr(9) +'Delete addons cache...........:12 around (500Mb)');

  SYS.free;
  writeln();
  writeln(chr(9) +chr(9)+'Type the number:');
  writeln();
  writeln();
  readln(answer);
  if length(answer)=0 then begin
   addons_setup();
   halt(0);
  end;
  
  if answer='0' then begin
  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                     Installation completed                     xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();
  writeln(chr(9) + 'You can access to artica by typing https://yourserver:9000');
  writeln(chr(9) + 'Use on logon section the username "' + GLOBAL_INI.get_LDAP('admin') + '" ');
  writeln(chr(9) + 'Use on logon section the password "' + GLOBAL_INI.get_LDAP('password') + '" ');
  writeln(chr(9) + 'Currently your mail server (postfix) is not fully configured.');
  writeln(chr(9) + 'You have to logon to artica web site, set yours domains and apply policies');
  writeln();
  writeln();
  writeln();
  writeln();
  writeln();
  writeln(chr(9) + '[Enter] key to restart artica and exit setup installation');
  writeln();
  readln();
  fpsystem('/etc/init.d/artica-postfix restart');
  halt(0);
  exit;
  end;
  
  

  if answer='1' then install.FETCHMAIL_INSTALL();
  if answer='2' then install.INSTALL_HOTWAYD();
  if answer='3' then install.KAV_INSTALL();
  if answer='4' then install.KAS_INSTALL();
  if answer='5' then install.DNSMASQ_INSTALL();
  if answer='6' then install.CYRUS_INSTALL();
  if answer='7' then ldap_setup(true);
  if answer='8' then install.ARTICA_WEB();
  if answer='9' then install.MAILMAN_INSTALL();
  if answer='10' then begin
     install.POSTFIX_CONFIGURE_MAIN_CF();
     install.RECONFIGURE_MASTER_CF();
     global_ini.CYRUS_IMAPD_CONFIGURE();
     global_ini.POSTFIX_RESTART_DAEMON();
  end;
  if answer='13' then install.KAVPROXY_INSTALL();
  if answer='14' then POSTFIX_SETUP();
  if answer='15' then ROUNDCUBE_SETUP();

  if answer='16' then begin
     install.SQUID_INSTALL();
     install.SQUID_CONFIGURE();
  end;
  
  if answer='17' then begin
     install.KAVPROXY_INSTALL();
     install.SQUID_CONFIGURE();
  end;
  

  if answer='18' then begin
     install.DANSGUARDIAN_INSTALL();
  end;

  if answer='19' then begin
     install.PUREFTPD_INSTALL();
  end;

  if answer='20' then begin
     install.AWSTATS_INSTALL()
  end;
 if answer='21' then begin
  install.AMAVISD()
 end;
  
  if answer='11' then begin
        if length(APACHE_VERSION)=0 then install.ARTICA_WEB();
        if FileExists('/usr/sbin/postconf') then begin
           if length(fetchmail_ver)=0       then install.FETCHMAIL_INSTALL();
           if length(MAILMAN_VERSION)=0     then install.MAILMAN_INSTALL();
           if length(kas_ver)=0             then install.KAS_INSTALL();
           if length(CYRUS_VERSION)=0       then install.CYRUS_INSTALL();
           if length(aveserver)=0           then install.KAV_INSTALL();
           if length(HOTWAY_VERSION)=0      then install.INSTALL_HOTWAYD();
        end;
        if length(dnsmasq_ver)=0            then install.DNSMASQ_INSTALL();
        install.PUREFTPD_INSTALL();
        
        if FileExists('/opt/artica/sbin/squid') then begin
           if length(KAV4PROXY_VERSION)=0   then install.KAVPROXY_INSTALL();
        
        end;

  end;
 if answer='12' then begin
    writeln('waiting, removing install cache...');
    fpsystem('/bin/rm -rf /opt/artica/install');
 end;

   addons_setup();
   exit;

end;
//##############################################################################


PROCEDURE Tmenus.ldap_setup(restart_config:boolean);
var
   suffix,admin,password,ldap_server,ldap_suffix,ldap_admin,ldap_password,passed_value:string;
   path:string;
begin
     suffix:=GLOBAL_INI.LDAP_READ_VALUE_KEY('suffix');
     admin:=GLOBAL_INI.LDAP_READ_ADMIN_NAME();
     password:=GLOBAL_INI.LDAP_READ_VALUE_KEY('rootpw');

     ldap_server:=trim(GLOBAL_INI.get_LDAP('server'));
     ldap_admin:=trim(GLOBAL_INI.get_LDAP('admin'));
     ldap_suffix:=trim(GLOBAL_INI.get_LDAP('suffix'));
     ldap_password:=trim(GLOBAL_INI.get_LDAP('password'));

     
     if ldap_server='' then ldap_server:='127.0.0.1';
     
     
     if ldap_admin='' then begin
        if length(admin)>0 then ldap_admin:=admin;
     end;
     
     if ldap_password='' then begin
         if length(password)>0 then ldap_password:=password;
     end;
     
     if ldap_suffix='' then begin
        if length(suffix)>0 then ldap_suffix:=suffix;
     end;
     
     
     
     
  if restart_config then begin
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                           LDAP SETTINGS                        xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  
  writeln(chr(9));
  writeln(chr(9));

  writeln(chr(9)+'Infos:the web console of artica can be found here');
  writeln(chr(9)+'https://'+ global_ini.LINUX_GET_HOSTNAME + ':9000');
  writeln(chr(9)+'Username: ' + global_ini.LDAP_READ_ADMIN_NAME() + '; Password: ' + global_ini.LDAP_READ_VALUE_KEY('rootpw'));
  writeln(chr(9));
  writeln(chr(9));
  writeln(chr(9) + 'You need now to set the account of LDAP master administrator,');
  writeln(chr(9) + 'This account allows you to be connected as global administrator');
  writeln(chr(9) + 'on the web console.');
  writeln(chr(9) + 'Artica-install will ask you few settings...');


     writeln('Give the ldap server name: (default is [' + ldap_server + '])');
     readln(passed_value);
     if length(passed_value)>0 then ldap_server:=passed_value;
     
     writeln('Give the ldap administrator name: (default is [' + ldap_admin + '])');
     readln(passed_value);
     if length(passed_value)>0 then ldap_admin:=passed_value;
     
     writeln('Give the ldap database path: (default is [' + ldap_suffix + '])');
     readln(passed_value);
     if length(passed_value)>0 then ldap_suffix:=passed_value;

     writeln('Give the ldap administrator password: (default is [' + ldap_password + '])');
     readln(passed_value);
     if length(passed_value)>0 then ldap_password:=passed_value;

     writeln('writing settings to artica...');
  end;
     
     
     if length(ldap_server)=0 then ldap_server:='127.0.0.1';
     if length(ldap_suffix)=0 then ldap_suffix:='dc=nodomain';
     if length(ldap_admin)=0 then ldap_admin:='Manager';
     if length(ldap_password)=0 then ldap_password:='secret';
     
     forcedirectories('/etc/artica-postfix');
     
     GLOBAL_INI.set_LDAP('server',ldap_server);
     GLOBAL_INI.set_LDAP('suffix',ldap_suffix);
     GLOBAL_INI.set_LDAP('admin',ldap_admin);
     GLOBAL_INI.set_LDAP('password',ldap_password);
     GLOBAL_INI.set_LDAP('cyrus_admin','cyrus');
     GLOBAL_INI.set_LDAP('cyrus_password',ldap_password);
     
     GLOBAL_INI.LDAP_WRITE_VALUE_KEY('rootdn','"cn=' + admin + ',' + suffix + '"');
     GLOBAL_INI.LDAP_WRITE_VALUE_KEY('rootpw',ldap_password);
     GLOBAL_INI.LDAP_WRITE_VALUE_KEY('password-hash','{CLEARTEXT}');

     
     if  FileExists(GLOBAL_INI.LDAP_GET_INITD()) then begin
          path:=ExtractFileDir(ParamStr(0));
          fpsystem('/bin/cp ' + path + '/install/postfix.schema ' +   GLOBAL_INI.LDAP_GET_SCHEMA_PATH() + '/postfix.schema');
          GLOBAL_INI.LDAP_ADDSCHEMA('postfix.schema');
     end;
           LOG.INSTALL_MODULES('APP_ARTICA','Ldap settings.................:OK');

end;
//##############################################################################
PROCEDURE Tmenus.mysql_setup();

          var mysql_server,mysql_admin,mysql_password,passed_value,mysql_repos:string;
          db:TDebian;
begin
     mysql_server:=GLOBAL_INI.MYSQL_SERVER();
     mysql_admin:=GLOBAL_INI.MYSQL_ROOT();
     mysql_password:=GLOBAL_INI.MYSQL_PASSWORD();
     
     if not FileExists(GLOBAL_INI.MYSQL_INIT_PATH) then begin
          db:=TDebian.Create();
          mysql_repos:=db.AnalyseRequiredPackages('mysql');
          writeln('');
          writeln('');
          writeln('Mysql setup....');
          writeln('Currently, there are no mysql server installed on your system.');
          writeln('You can use a remote server.');
          writeln('If you don''t have a remote mysql server and you want to install one');
          writeln('in your system, just install theses packages and restart the installation');
          writeln(mysql_repos);
          writeln('');
          writeln('');
     
     end;

     writeln('');


     writeln('Give the mysql server name: (default is [' + mysql_server + '])');
     readln(passed_value);
     if length(passed_value)>0 then mysql_server:=passed_value;

     writeln('Give the mysql administrator name: (default is [' + mysql_admin + '])');
     readln(passed_value);
     if length(passed_value)>0 then mysql_admin:=passed_value;

     writeln('Give the ldap administrator password: (default is [' + mysql_password + '])');
     readln(passed_value);
     if length(passed_value)>0 then mysql_password:=passed_value;

     writeln('writing settings to artica...');
     forcedirectories('/etc/artica-postfix');
     
     GLOBAL_INI.ARTICA_MYSQL_SET_INFOS('database_admin',mysql_admin);
     GLOBAL_INI.ARTICA_MYSQL_SET_INFOS('database_password',mysql_password);
     GLOBAL_INI.ARTICA_MYSQL_SET_INFOS('mysql_server',mysql_server);
     

     writeln('writing settings to artica done...');
     fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-sql setup');


end;


//##############################################################################

function Tmenus.setup_require():boolean;
var
   ans,suffix_command_line,updater,prefix_command_line,com:string;
   db:TDebian;
   repos:string;
begin
    result:=false;
    db:=TDebian.Create();
    writeln();
    writeln();
    writeln('minimum requirements on this system:');
    writeln('1) Apache + PHP5');
    writeln('**************************************************************************');
    writeln();
    suffix_command_line:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('suffix_command_line');
    updater:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('updater');
    prefix_command_line:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('prefix_command_line');

       repos:=db.AnalyseRequiredPackages('apache') + ' ' + db.AnalyseRequiredPackages('php5');
       writeln('I will install these packages for you ' + repos);
       com:=updater +' ' + prefix_command_line+ ' ' + repos + ' '+ suffix_command_line;
       writeln('Waiting... I execute ' + com);
       fpsystem(com);
       writeln();
       writeln();
       result:=true;
       exit;

    
    
    writeln('Do you want to show minimal packages required ? [y/n]');
    readln(ans);
    if ans='y' then begin
    writeln('Use your favourite repositories manager in order to install these packages');
    writeln('**************************************************************************');
    writeln('');
    writeln('');
    writeln('For apache:');
    writeln(db.AnalyseRequiredPackages('apache'));
    writeln('');
    writeln('For PHP5:');
    writeln(db.AnalyseRequiredPackages('php5') + ' php-sqlite3');
    writeln('');
    writeln('Restart the installation when all these packages are installed...');
    end;
    

end;
 //##############################################################################

PROCEDURE Tmenus.install_Packages(notauto:boolean);
var repos,distribution,reposfile:string;
debian:Tdebian;
com:string;
logs:Tlogs;
phppath,suffix_command_line,updater,prefix_command_line:string;
begin

     logs:=Tlogs.Create;

     phppath:=ExtractFilePath(ParamStr(0));
     GLOBAL_INI.SYSTEM_ENV_PATH_SET('/usr/local/sbin');
     GLOBAL_INI.SYSTEM_ENV_PATH_SET('/usr/sbin');
     GLOBAL_INI.SYSTEM_ENV_PATH_SET('/usr/local/bin');
     GLOBAL_INI.SYSTEM_ENV_PATH_SET('/sbin');

     
     distribution:=install.LinuxInfosDistri();
     if length(distribution)=0 then begin
         ShowScreen('Your distribution is not supported...');
         ShowScreen('install_Packages:: Unable to determine distribution...');
         exit;
         halt(0);
     end;
     writeln(distribution);

     reposfile:=phppath + 'install/distributions/' + distribution + '/repositories.txt';
     if not fileexists(reposfile) then begin
          ShowScreen('install_Packages:unable to locate '+reposfile);
          logs.logsInstall('install_Packages:: unable to locate ' + reposfile);
         exit;
     end;


    suffix_command_line:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('suffix_command_line');
    updater:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('updater');
    prefix_command_line:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('prefix_command_line');

    install.Disable_se_linux();
    

    debian:=tdebian.Create();
    repos:=trim(debian.ShowRepositories(''));
    
    if length(repos)>0 then begin
    writeln('Installing repositories... Waiting for few minutes...');
    writeln('----------------------------------------------------------');
    writeln(repos);
    writeln('----------------------------------------------------------');

    if not FileExists(updater) then begin
       writeln('Unable to stat updater define in REPOSITORIES section :' + updater);
       exit;
    end;
    
    
    com:=updater +' ' + prefix_command_line+ ' ' + repos + ' '+ suffix_command_line;
    writeln(com);
    writeln('----------------------------------------------------------');
   

   if notauto=False then begin
      writeln('Just execute this operation:');
      writeln(com);
      writeln('Enter key to exit:');
      Readln(x);
      exit;
   end;


   fpsystem(com);
   end;
   
end;
//##############################################################################
PROCEDURE Tmenus.remove_Packages_addon(Packagename:string);
var
   debian:Tdebian;
   suffix_command_line:string;
   updater,prefix_command_uninstall,remover,FileLogs, exp:string;
   LOGS:Tlogs;
   D:boolean;
   CMD:string;
begin


       suffix_command_line:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('suffix_command_line');
       updater:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('updater');
       prefix_command_uninstall:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('prefix_command_uninstall');
       remover:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('remover');
       LOGS:=Tlogs.Create;
       D:=LOGS.COMMANDLINE_PARAMETERS('-V');
       LOGS.INSTALL_MODULES(Packagename,'Starting remove program "' + Packagename + '"');
       FileLogs:='/var/log/artica-postfix/artica-install-' + Packagename + '.log';
       if ParamStr(3)='auto' then exp:=' >>' + FileLogs;
       
   if FileExists(FileLogs) then fpsystem('/bin/rm ' + FileLogs);
   debian:=tdebian.Create();
       if D then writeln('Starting remove program "' + Packagename + '"');
       
   if Packagename='APP_AWSTATS' then begin
         LOGS.INSTALL_MODULES(Packagename,'Checking if product "awstats" exists in database...');
        if debian.ISReposListed('dnsmasq') then begin
           if D then writeln('Product exists in package database...');
           LOGS.INSTALL_MODULES(Packagename,'Product exists in package database...');
           if length(remover)=0 then begin
              CMD:=updater + ' ' + prefix_command_uninstall + ' awstats ' + suffix_command_line + exp;
           end else begin
              CMD:=remover + ' ' + prefix_command_uninstall + ' awstats ' + suffix_command_line + exp;
           end;
           LOGS.INSTALL_MODULES(Packagename,CMD);
           if D then writeln(CMD);
           fpsystem(CMD);
        end;
        exit;
   end;

   if Packagename='APP_DNSMASQ' then begin
         LOGS.INSTALL_MODULES(Packagename,'Checking if product "dnsmasq" exists in database...');
        if debian.ISReposListed('dnsmasq') then begin
           if D then writeln('Product exists in package database...');
           LOGS.INSTALL_MODULES(Packagename,'Product exists in package database...');
           if length(remover)=0 then begin
              CMD:=updater + ' ' + prefix_command_uninstall + ' dnsmasq ' + suffix_command_line + exp;
           end else begin
              CMD:=remover + ' ' + prefix_command_uninstall + ' dnsmasq ' + suffix_command_line + exp;
           end;
           LOGS.INSTALL_MODULES(Packagename,CMD);
           if D then writeln(CMD);
           fpsystem(CMD);
        end;

        exit;
   end;
   
   if Packagename='APP_FETCHMAIL' then begin
         LOGS.INSTALL_MODULES(Packagename,'Checking if product "fetchmail" exists in database...');
        if debian.ISReposListed('fetchmail') then begin
           if D then writeln('Product exists in package database...');
           LOGS.INSTALL_MODULES(Packagename,'Product exists in package database...');
           if length(remover)=0 then begin
              CMD:=updater + ' ' + prefix_command_uninstall + ' fetchmail ' + suffix_command_line+ exp;
           end else begin
              CMD:=remover + ' ' + prefix_command_uninstall + ' fetchmail ' + suffix_command_line+ exp;
           end;
           LOGS.INSTALL_MODULES(Packagename,CMD);
           if D then writeln(CMD);
           fpsystem(CMD);
        end else begin
            install.FETCHMAIL_UNINSTALL();
        
        
        end;
        exit;
   end;


   if Packagename='APP_AVESERVER' then begin
         LOGS.INSTALL_MODULES(Packagename,'Checking if product "kav4mailservers-linux55" exists in database...');
        if debian.ISReposListed('kav4mailservers-linux55') then begin
           if D then writeln('Product exists in package database...');
           LOGS.INSTALL_MODULES(Packagename,'Product exists in package database...');
           if length(remover)=0 then begin
              CMD:=updater + ' ' + prefix_command_uninstall + ' kav4mailservers-linux55 ' + suffix_command_line+ exp;
           end else begin
              CMD:=remover + ' ' + prefix_command_uninstall + ' kav4mailservers-linux55 ' + suffix_command_line + exp;
           end;

           LOGS.INSTALL_MODULES(Packagename,CMD);
           if D then writeln(CMD);
           fpsystem(CMD);
        end;
      exit;
   end;
   
   if Packagename='APP_KAS3' then begin
         LOGS.INSTALL_MODULES(Packagename,'Checking if product "kas-3" exists in database...');
        if debian.ISReposListed('kas-3') then begin
           if D then writeln('Product exists in package database...');
           LOGS.INSTALL_MODULES(Packagename,'Product exists in package database...');
           if length(remover)=0 then begin
              CMD:=updater + ' ' + prefix_command_uninstall + ' kas-3 ' + suffix_command_line+ exp;
           end else begin
              CMD:=remover + ' ' + prefix_command_uninstall + ' kas-3 ' + suffix_command_line + exp;
           end;
           LOGS.INSTALL_MODULES(Packagename,CMD);
           if D then writeln(CMD);
           fpsystem(CMD);
        end;
        exit;
   end;

   LOGS.INSTALL_MODULES(Packagename,'uninstall feature of "' + PackageName + '" is not currently supported');
   
//fetchmail
//kav4mailservers-linux55
//kas-3

end;




//##############################################################################
PROCEDURE Tmenus.install_Packages_addon(Packagename:string);
var repos:string;
debian:Tdebian;
com:string;
PackageToInstall:String;
POSTFIX_ADDON:Tpostfix_addon;
install_internal:Tclass_install;
suffix_command_line,updater,prefix_command_line:string;
MustDownloaded:boolean;
LOGS:Tlogs;
FileLogs:string;
D:boolean;
begin
     install_internal:=Tclass_install.Create;
     POSTFIX_ADDON:=Tpostfix_addon.Create();
     MustDownloaded:=True;
     LOGS:=TLogs.Create;
     D:=GLOBAL_INI.COMMANDLINE_PARAMETERS('debug');
     if D Then writeln('install_Packages_addon(): set environnments');
     GLOBAL_INI.SYSTEM_ENV_PATH_SET('/usr/local/sbin');
     GLOBAL_INI.SYSTEM_ENV_PATH_SET('/usr/sbin');
     GLOBAL_INI.SYSTEM_ENV_PATH_SET('/usr/local/bin');
     GLOBAL_INI.SYSTEM_ENV_PATH_SET('/sbin');
     if D Then writeln('install_Packages_addon(): package name:'+Packagename);
     PackageToInstall:=Packagename;
     FileLogs:='/var/log/artica-postfix/artica-install-' + Packagename + '.log';
     if FileExists(FileLogs) then fpsystem('/bin/rm ' + FileLogs);
     LOGS.INSTALL_MODULES(Packagename,'Starting installation of package "' +  Packagename + '"');
     LOGS.INSTALL_MODULES(Packagename,'It could take few times for installing this package');
     
       if Packagename='APP_CYRUS' then begin
          PackageToInstall:='cyrus';
          GLOBAL_INI.set_MANAGE_MAILBOX_SERVER('cyrus');
          GLOBAL_INI.set_MANAGE_MAILBOXES('yes');
       end;
       
      if Packagename='APP_KAS3' then begin
          Repository('perl');
          LOGS.INSTALL_MODULES(Packagename,'Running KAS_INSTALL()');
          install_internal.KAS_INSTALL();
          LOGS.INSTALL_MODULES(Packagename,'Configure postfix');
          install_internal.RECONFIGURE_MASTER_CF();
          exit;
      end;
      
      if Packagename='APP_GEOIP' then begin
          install_internal.GEOIP_UPDATES();
          exit;
      end;
      
      if Packagename='APP_AVESERVER' then begin
          POSTFIX_ADDON.install_kaspersky_mail_servers();
          install_internal.RECONFIGURE_MASTER_CF();
          exit;
      end;
      
      if Packagename='APP_RRDTOOL' then begin
          PackageToInstall:='rrd';
          MustDownloaded:=true;
      end;
      
      if Packagename='APP_AWSTATS' then begin
          PackageToInstall:='rdd';
          MustDownloaded:=true;
      end;
      
      if Packagename='APP_FETCHMAIL' then begin
          PackageToInstall:='make';
          MustDownloaded:=true;
      end;
      
      if Packagename='APP_MAKE' then begin
          PackageToInstall:='make';
          MustDownloaded:=true;
      end;
      
      if Packagename='APP_DNSMASQ' then begin
          if FileExists('/etc/init.d/bind9') then begin
             LOGS.INSTALL_MODULES(Packagename,'Installer could not continue because bind9 is already installed on this computer');
              ShowScreen('Installer could not continue because bind9 is already installed on this computer');
              exit;
          end;
          PackageToInstall:='APP_DNSMASQ';
          MustDownloaded:=false;
          
      end;

      if Packagename='APP_DSPAM' then MustDownloaded:=false;
      if not MustDownloaded then  LOGS.INSTALL_MODULES(Packagename,Packagename+ ' will not use repositories of this distribution');
      
      if Packagename='APP_PROCMAIL' then begin
            if fileexists('/usr/bin/procmail') then begin
               MustDownloaded:=false;
               ShowScreen('procmail already installed...');
            end;
      end;
      

    if MustDownloaded then begin
       suffix_command_line:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('suffix_command_line');
       updater:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('updater');
       prefix_command_line:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('prefix_command_line');
       debian:=tdebian.Create();
       repos:=trim(debian.AnalyseRequiredPackages(PackageToInstall));
       ShowScreen('install_Packages_addon:: repostories length ="' + IntToStr(length(repos)) + '"');

       if length(repos)>0 then begin
           if not FileExists('/tmp/beffore_check') then begin
               writeln('');
               writeln('');
               writeln('');
               writeln('Please wait few moments while checking some components....');
               writeln('');
               writeln('');
               writeln('');
               fpsystem(GLOBAL_INI.LINUX_REPOSITORIES_INFOS('beffore_check'));
               fpsystem('touch /tmp/beffore_check');
           end;

           com:=updater +' ' + prefix_command_line+ ' ' + repos + ' '+ suffix_command_line;
           
           
           
           ShowScreen('install_Packages_addon:: repostories command ="' + com + '"');
           if length(com)>0 then begin
              if ParamStr(3)='auto' then com:=com + ' >>' + FileLogs;
              LOGS.INSTALL_MODULES(Packagename,com);

              fpsystem(com);
           end;
       end;
    end;
   
   if Packagename='APP_FETCHMAIL' then install.FETCHMAIL_INSTALL();
   if packagename='APP_QUEUEGRAPH' then install.QUEUEGRAPH_INSTALL();
   if packagename='APP_YOREL' then GLOBAL_INI.YOREL_RECONFIGURE('');
   if packagename='APP_DNSMASQ' then install.DNSMASQ_RECONFIGURE();
   if packagename='APP_AWSTATS' then install.INSTALL_AWSTATS();

   if packagename='APP_DSPAM' then begin
      LOGS.INSTALL_MODULES(Packagename,'not yet supported ');
      exit;

   end;



   if packagename='APP_SQLITE' then install.SQLITE_INSTALL();
   if packagename='APP_ROUNDCUBE' then install.ROUNDCUBE_INSTALL();
   if packagename='APP_LIBCURL' then install.LIBCURL_INSTALL(false);
   if packagename='APP_DNSMASQ' then install.DNSMASQ_INSTALL();
   if packagename='APP_BERKLEY' then install.BERKLEY_INSTALL();
   if packagename='APP_OPENSSL' then install.LIBSSL_INSTALL();
   if packagename='APP_GEOIP' then install.GEOIP_UPDATES();

   if Packagename='APP_CYRUS' then begin
      writeln('SET CYRUS ADMINISTRATOR IN LDAP');
      install.default_install:=True;
      install.LDAP_SET_CYRUS_ADM();
      writeln('SET CYRUS IN MASTER.CF');
      POSTFIX_ADDON.set_cyrusMastercf();
   end;
   
   
 if Packagename='APP_PROCMAIL' then begin
    if not fileexists('/usr/bin/procmail') then begin
        ShowScreen('install_Packages_addon:: Warning, procmail does not exist');
        POSTFIX_ADDON.PROCMAIL_MASTER_CF;
        POSTFIX_ADDON.PROCMAIL_PROCMAILRC;
        fpsystem('/etc/init.d/postfix restart');
    end;
 end;
   
   
  if Packagename='APP_DNSMASQ' then begin
      if not FileExists('/etc/init.d/dnsmasq') then begin
      
      end;
   end;
   
   
   
    POSTFIX_ADDON.Free;
    install_internal.Free;
   
end;

PROCEDURE tmenus.Repository(package_name:string);
var
   suffix_command_line         :string;
   updater                     :string;
   prefix_command_line         :string;
   repos,com                   :string;
   debian                      :TDebian;
begin

       suffix_command_line:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('suffix_command_line');
       updater:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('updater');
       prefix_command_line:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('prefix_command_line');
       debian:=tdebian.Create();
       repos:=trim(debian.AnalyseRequiredPackages(package_name));
       ShowScreen('Repostory:: repostories length ="' + IntToStr(length(repos)) + '"');

       if length(repos)>0 then begin
           if not FileExists('/tmp/beffore_check') then begin
               writeln('');
               writeln('');
               writeln('');
               writeln('Please wait few moments while checking some components....');
               writeln('');
               writeln('');
               writeln('');
               fpsystem(GLOBAL_INI.LINUX_REPOSITORIES_INFOS('beffore_check'));
               fpsystem('touch /tmp/beffore_check');
           end;

           com:=updater +' ' + prefix_command_line+ ' ' + repos + ' '+ suffix_command_line;



           ShowScreen('Repostory:: repostories command ="' + com + '"');
           if length(com)>0 then begin
              fpsystem(com);
           end;
       end;
    end;



PROCEDURE Tmenus.HELP_DSPAM();
begin
     writeln('');
     writeln(chr(9) + 'dspam usages:');
     writeln(chr(9)+chr(9)+'-dspam install........: compile and install dspam');
     writeln(chr(9)+chr(9)+'-dspam configure......: configure,re-configure dspam');

end;

PROCEDURE Tmenus.HELP_POSTFIX();

begin
     writeln('');
     writeln(chr(9)+chr(9) + '-postfix-reconfigure-master : Reconfigure master.cf and main.cf');
     writeln('');
     writeln(chr(9) + 'Postfix usages: -postfix (conf|cert|rrd|inet|queue [option])');
     writeln(chr(9)+chr(9)+'-postfix         : Configure postfix width ldap settings');
     writeln(chr(9)+chr(9)+'-postfix fix-sasl: Configure fix postfix width sasl settings');
     writeln(chr(9)+chr(9)+'-postfix conf    : View mandatories settings');
     writeln(chr(9)+chr(9)+'-postfix alllogs : Export logs to Artica logs path');
     writeln(chr(9)+chr(9)+'-postfix cert    : creating TLS certificates using default configuration SSL file ');
     writeln(chr(9)+chr(9)+'-postfix rrd     : generate rrd functions for mails statistics in debug mode (experimental)');
     writeln(chr(9)+chr(9)+'-postfix inet    : Automatically change inet_interface in postfix settings');
     writeln(chr(9)+chr(9)+'-postfix check-config [path]    : Apply main.cf with predefined path.');
     writeln(chr(9)+chr(9)+'-postfix errors  : View last errors');
     writeln(chr(9)+chr(9)+'-postfix queue   : Count number of mails in queue values are :');
     writeln(chr(9)+chr(9)+'                                      incoming: incoming queue');
     writeln(chr(9)+chr(9)+'                                      active  : active queue');
     writeln(chr(9)+chr(9)+'                                      deferred: deferred queue');
     writeln(chr(9)+chr(9)+'                                      bounce  : non-delivery status');
     writeln(chr(9)+chr(9)+'                                      defer   : non-delivery status');
     writeln(chr(9)+chr(9)+'                                      trace   : delivery status');
     writeln(chr(9)+chr(9)+'                                      maildrop: dropped status');
     writeln(chr(9)+chr(9)+'Artica Queues caching features:');
     writeln(chr(9)+chr(9)+'In order to prevent CPU load to read the queues, Artica create caches for each queue...');
     writeln(chr(9)+chr(9)+chr(9)+'Generate cache files from queues listed below (available command are flush,debug,queue=,cache_delete)');
     writeln(chr(9)+chr(9)+chr(9)+'Generate cache for all queues        : -postfix queue cache');
     writeln(chr(9)+chr(9)+chr(9)+'Re-generate cache for all queues     : -postfix queue cache flush');
     writeln(chr(9)+chr(9)+chr(9)+'Re-generate cache for queue maildrop : -postfix queue cache  queue=maildrop flush');
     writeln(chr(9)+chr(9)+chr(9)+'Delete message id from cache index   : -postfix queue cache_delete [messageid]');
     
     
     writeln('');
     writeln(chr(9)+chr(9)+'-postfix queuelist: List mails stored in following queue:');
     writeln(chr(9)+chr(9)+'                                      incoming: incoming queue');
     writeln(chr(9)+chr(9)+'                                      active  : active queue');
     writeln(chr(9)+chr(9)+'                                      deferred: deferred queue');
     writeln(chr(9)+chr(9)+'                                      bounce  : non-delivery status');
     writeln(chr(9)+chr(9)+'                                      defer   : non-delivery status');
     writeln(chr(9)+chr(9)+'                                      trace   : delivery status');
     writeln(chr(9)+chr(9)+'                                      maildrop: dropped status');
     writeln(chr(9)+chr(9)+'                (from file number)');
     writeln(chr(9)+chr(9)+'                (to file number)');
     writeln(chr(9)+chr(9)+'                (queue)');
     writeln(chr(9)+chr(9)+'                (destination file)');
     writeln(chr(9)+chr(9)+'-postfix queuelist 0 100 incoming /tmp/file.txt');

end;
//##############################################################################

PROCEDURE Tmenus.HELP_AVESERVER();
begin
     writeln('');
     writeln(chr(9) + 'Kaspersky usages:................................................');
     writeln(chr(9)+chr(9)+'-mailav.................................: Install and configure Kaspersky Antivirus for Mail servers');
     writeln(chr(9)+chr(9)+'-mailav help............................: Show this help');
     writeln(chr(9)+chr(9)+'-mailav reconfigure.....................: reconfigure Kaspersky Antivirus for Mail servers');
     writeln(chr(9)+chr(9)+'-mailav delete..........................: remove Kaspersky Antivirus from master.cf');
     writeln(chr(9)+chr(9)+'-mailav remove..........................: unistall Kaspersky Antivirus');
     writeln(chr(9)+chr(9)+'-mailav template [notification] [user]..: read a template datas');
     writeln(chr(9)+chr(9)+chr(9)+'ex: artica-install -mailav template infected recipient');
     writeln(chr(9)+chr(9)+'-mailav save_templates..................: Replicate templates from artica to Kav templates folder');
     writeln(chr(9)+chr(9)+'-mailav pattern.........................: Show antivirus database date');
     writeln(chr(9)+chr(9)+'-mailav replicate [configuration file]..: replicate configuration file (used by artica web admin)');
     
end;
PROCEDURE Tmenus.HELP_DNSMASQ();
begin
     writeln('');
     writeln(chr(9) + 'dnsmasq usages:................................................');
     writeln(chr(9)+chr(9)+'-dnsmasq help...........: Show this help');
     writeln(chr(9)+chr(9)+'-dnsmasq reconfigure....: reconfigure or install dnsmasq');
     writeln(chr(9)+chr(9)+'-dnsmasq version........: show dnsmasq version');
end;
PROCEDURE Tmenus.HELP_EMAILRELAY();
begin
     writeln('');
     writeln(chr(9) + 'emailrelay usages:................................................');
     writeln(chr(9)+chr(9)+'-emailrelay help........: Show this help');
     writeln(chr(9)+chr(9)+'-emailrelay reconfigure.: reconfigure or install emailrelay');
     writeln(chr(9)+chr(9)+'-emailrelay clean.......: Clean/resend emailrelay queue');
     writeln('');
     writeln('additional option --verbose for debuging');
     writeln('');
     

end;

procedure Tmenus.ShowScreen(line:string);
 var  logs:Tlogs;
 begin
     logs:=Tlogs.Create();
     logs.Enable_echo_install:=True;
     Logs.logs('MENUS::' + line);
     logs.free;

 END;


end.
