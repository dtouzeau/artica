unit ocsi;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,logs,unix,RegExpr,zsystem,openldap,mysql_daemon;



  type
  tocsi=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     zldap:Topenldap;


     procedure   dbconfig();

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function    VERSION():string;
    function    VirtualHost(port:string):string;
    function    ifmodperl():string;
END;

implementation

constructor tocsi.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       zldap:=Topenldap.Create;


       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tocsi.free();
begin
    logs.Free;
    zldap.Free;
end;
//##############################################################################
function tocsi.VERSION():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    BinPath:string;
    filetmp:string;
begin

result:=SYS.GET_CACHE_VERSION('APP_OCSI');
if length(result)>0 then exit;
filetmp:='/usr/share/ocsinventory-reports/ocsreports/header.php';
if not FileExists(filetmp) then exit;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='Ver\.\s+([0-9\.]+)';
    FileDatas:=TStringList.Create;
    FileDatas.LoadFromFile(filetmp);
    for i:=0 to FileDatas.Count-1 do begin
        if RegExpr.Exec(FileDatas.Strings[i]) then begin
             result:=RegExpr.Match[1];
             break;
        end;
    end;
             RegExpr.free;
             FileDatas.Free;
SYS.SET_CACHE_VERSION('APP_OCSI',result);

end;
//#############################################################################
procedure tocsi.dbconfig();
var
   l:tstringlist;
   COMPTE_BASE:string;
   SERVEUR_SQL:string;
   PSWD_BASE:string;
begin
if not FileExists('/usr/share/ocsinventory-reports/ocsreports/dbconfig.inc.php') then exit;
l:=Tstringlist.Create;
   COMPTE_BASE:=SYS.MYSQL_INFOS('root');
   PSWD_BASE:=SYS.MYSQL_INFOS('database_password');
   SERVEUR_SQL:=SYS.MYSQL_INFOS('mysql_server')+':'+SYS.MYSQL_INFOS('port');


l.add('<?php');
l.add('$_SESSION["SERVEUR_SQL"]="'+SERVEUR_SQL+'";');
l.add('$_SESSION["COMPTE_BASE"]="'+COMPTE_BASE+'";');
l.add('$_SESSION["PSWD_BASE"]="'+PSWD_BASE+'";');
l.add('?>');
logs.WriteToFile(l.Text,'/usr/share/ocsinventory-reports/ocsreports/dbconfig.inc.php');
logs.DebugLogs('Starting......: OCS updating dbconfig.inc.php done');
l.free;
end;
//#############################################################################
function tocsi.VirtualHost(port:string):string;
var
l:TstringList;
ocswebservername:string;
   COMPTE_BASE:string;
   SERVEUR_SQL:string;
   PSWD_BASE:string;
   sql_port:string;
   ROTATELOGS:string;
begin
if not FileExists('/usr/share/ocsinventory-reports/ocsreports/dbconfig.inc.php') then exit;
   l:=TstringList.Create;
   ocswebservername:=SYS.GET_INFO('ocswebservername');
   if length(ocswebservername)=0 then ocswebservername:='ocs.localhost.localdomain';

   COMPTE_BASE:=SYS.MYSQL_INFOS('root');
   PSWD_BASE:=SYS.MYSQL_INFOS('database_password');
   SERVEUR_SQL:=SYS.MYSQL_INFOS('mysql_server');
   sql_port:=SYS.MYSQL_INFOS('port');
   dbconfig();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.ocsweb.install.php &');
   ROTATELOGS:=SYS.LOCATE_ROTATELOGS();

l.add('<VirtualHost *:'+port+'>');
l.add('        ServerName '+ocswebservername);
l.add('        DocumentRoot "/usr/share/ocsinventory-reports/ocsreports"');
l.add('');
l.add('        <Directory "/usr/share/ocsinventory-reports/ocsreports">');
l.add('                Options Indexes FollowSymLinks');
l.add('                AllowOverride All');
l.add('                Allow from all');
l.add('        </Directory>');
l.add('');
l.add('        <IfModule alias_module>');
l.add('                ScriptAlias /cgi-bin/ "/usr/share/ocsinventory-reports/ocsreports/cgi-bin/"');
l.add('                Alias /download /var/lib/ocsinventory-reports/download');
l.add('        </IfModule>');
l.add('');
l.add('        <Directory "/usr/share/ocsinventory-reports/ocsreports/cgi-bin">');
l.add('                AllowOverride None');
l.add('                Options None');
l.add('                Order allow,deny');
l.add('                Allow from all');
l.add('        </Directory>');
l.add('');
l.add('#Injection du referentiel Perl pour OCSng');
l.add(ifmodperl());
l.add('');
l.add('CustomLog "|'+ROTATELOGS+' /usr/local/apache-groupware/logs/ocs.log 86400" "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" %V"');
l.add('ErrorLog /usr/local/apache-groupware/logs/ocs_err.log');

l.add('</VirtualHost>');
   result:=l.Text;
   l.free;

end;
//##############################################################################
function tocsi.ifmodperl():string;
var
   l:Tstringlist;
   COMPTE_BASE:string;
   SERVEUR_SQL:string;
   PSWD_BASE:string;
   sql_port:string;
begin

if not FileExists('/usr/share/ocsinventory-reports/ocsreports/dbconfig.inc.php') then exit;

forceDirectories('/var/lib/ocsinventory-reports/download');
fpsystem('/bin/chmod 0755 /var/lib/ocsinventory-reports/download');


l:=Tstringlist.Create;

   COMPTE_BASE:=SYS.MYSQL_INFOS('root');
   PSWD_BASE:=SYS.MYSQL_INFOS('database_password');
   SERVEUR_SQL:=SYS.MYSQL_INFOS('mysql_server');
   sql_port:=SYS.MYSQL_INFOS('port');

l.add('        <IfModule mod_perl.c>');
l.add('                  # Database options');
l.add('                  PerlSetEnv OCS_DB_HOST '+SERVEUR_SQL);
l.add('                  PerlSetEnv OCS_DB_PORT '+sql_port);
l.add('                  PerlSetEnv OCS_DB_NAME ocsweb');
l.add('                  PerlSetEnv OCS_DB_LOCAL ocsweb');
l.add('                  PerlSetEnv OCS_DB_USER '+COMPTE_BASE);
if length(trim(PSWD_BASE))>0 then begin
   l.add('                  PerlSetVar OCS_DB_PWD '+PSWD_BASE);
end;
l.add('                  PerlSetEnv OCS_MODPERL_VERSION 2');
l.add('');
{l.add('                  # Slave Database settings');
l.add('                  # Replace localhost by hostname or ip of MySQL server for READ');
l.add('                  # Useful if you handle mysql slave databases');
l.add('                  # PerlSetEnv OCS_DB_SL_HOST localhost');
l.add('                  # Replace 3306 by port where running MySQL server, generally 3306');
l.add('                  # PerlSetEnv OCS_DB_SL_PORT_SLAVE 3306');
l.add('                  # User allowed to connect to database');
l.add('                  # PerlSetEnv OCS_DB_SL_USER ocs');
l.add('                  # Name of the database');
l.add('                  # PerlSetEnv OCS_DB_SL_NAME ocsweb');
l.add('                  # Password for user');
l.add('                  # PerlSetVar OCS_DB_SL_PWD ocs');
l.add('  ');                                              }

l.add('                  # Path to log directory (must be writeable)');
l.add('                  PerlSetEnv OCS_OPT_LOGPATH "/var/log/ocsinventory-server"');
l.add('  ');
l.add('                  # If you need to specify a mysql socket that the client''s built-in');
l.add('                  #PerlSetVar OCS_OPT_DBI_MYSQL_SOCKET "path/to/mysql/unix/socket"');
l.add('                  # DBI verbosity');
l.add('                  PerlSetEnv OCS_OPT_DBI_PRINT_ERROR 0');
l.add('  ');
l.add('                  # Unicode support');
l.add('                  PerlSetEnv OCS_OPT_UNICODE_SUPPORT 1');
l.add('');
l.add('                  # If you are using a multi server architecture, ');
l.add('                  # Put the ip addresses of the slaves on the master');
l.add('                  # (This is read as perl regular expressions)');
l.add('                  PerlAddVar OCS_OPT_TRUSTED_IP 127.0.0.1');
l.add('                  #PerlAddVar OCS_OPT_TRUSTED_IP XXX.XXX.XXX.XXX');
l.add('  ');
l.add('                  # ===== WEB SERVICE (SOAP) SETTINGS =====');
l.add('');
l.add('                  PerlSetEnv OCS_OPT_WEB_SERVICE_ENABLED 0');
l.add('                  PerlSetEnv OCS_OPT_WEB_SERVICE_RESULTS_LIMIT 100');
l.add('                  # PerlSetEnv OCS_OPT_WEB_SERVICE_PRIV_MODS_CONF "WEBSERV_PRIV_MOD_CONF_FILE"');
l.add('');
l.add('                  # Be careful: you must restart apache to make settings taking effects');
l.add('');
l.add('                  # Configure engine to use the settings from this file  ');
l.add('                  PerlSetEnv OCS_OPT_OPTIONS_NOT_OVERLOADED 0');
l.add('');
l.add('                  # Try to use other compress algorythm than raw zlib');
l.add('                  # GUNZIP and clear XML are supported');
l.add('                  PerlSetEnv OCS_OPT_COMPRESS_TRY_OTHERS 1');
l.add('  ');
l.add('                  ##############################################################');
l.add('                  # ===== OPTIONS BELOW ARE OVERLOADED IF YOU USE OCS GUI =====#');
l.add('                  ##############################################################');
l.add('');
l.add('                  # NOTE: IF YOU WANT TO USE THIS CONFIG FILE INSTEAD, set OCS_OPT_OPTIONS_NOT_OVERLOADED to ''1''');
l.add('');
l.add('                  # ===== MAIN SETTINGS =====');
l.add('');
l.add('                  # Enable engine logs (see LOGPATH setting)');
l.add('                  PerlSetEnv OCS_OPT_LOGLEVEL 1');
l.add('                  # Specify agent''s prolog frequency');
l.add('                  PerlSetEnv OCS_OPT_PROLOG_FREQ 12');
l.add('                  # Configure the duplicates detection system');
l.add('                  PerlSetEnv OCS_OPT_AUTO_DUPLICATE_LVL 15');
l.add('                  # Futur security improvements');
l.add('                  PerlSetEnv OCS_OPT_SECURITY_LEVEL 0');
l.add('                  # Validity of a computer''s lock');
l.add('                  PerlSetEnv OCS_OPT_LOCK_REUSE_TIME 600');
l.add('                  # Enable the history tracking system (useful for external data synchronisation');
l.add('                  PerlSetEnv OCS_OPT_TRACE_DELETED 1');
l.add('  ');
l.add('                  # ===== INVENTORY SETTINGS =====');
l.add('  ');
l.add('                  # Specify the validity of inventory data');
l.add('                  PerlSetEnv OCS_OPT_FREQUENCY 0  ');
l.add('                  # Configure engine to update inventory regarding to CHECKSUM agent value (lower DB backend load)');
l.add('                  PerlSetEnv OCS_OPT_INVENTORY_DIFF 1');
l.add('                  # Make engine consider an inventory as a transaction (lower concurency, better disk usage)');
l.add('                  PerlSetEnv OCS_OPT_INVENTORY_TRANSACTION 1');
l.add('                  # Configure engine to make a differential update of inventory sections (row level). Lower DB backend load, higher frontend load');
l.add('                  PerlSetEnv OCS_OPT_INVENTORY_WRITE_DIFF 1');
l.add('                  # Enable some stuff to improve DB queries, especially for GUI multicriteria searching system');
l.add('                  PerlSetEnv OCS_OPT_INVENTORY_CACHE_ENABLED 1');
l.add('                  # Specify when the engine will clean the inventory cache structures');
l.add('                  PerlSetEnv OCS_OPT_INVENTORY_CACHE_REVALIDATE 7');
l.add('                  # Enable you to keep trace of every elements encountered in db life');
l.add('                  PerlSetEnv OCS_OPT_INVENTORY_CACHE_KEEP 1');
l.add('');
l.add('                  # ===== SOFTWARES DEPLOYMENT SETTINGS =====');
l.add('');
l.add('                  # Enable this feature');
l.add('                  PerlSetEnv OCS_OPT_DOWNLOAD 1');
l.add('                  # Package wich have a priority superior than this value will not be downloaded');
l.add('                  PerlSetEnv OCS_OPT_DOWNLOAD_PERIOD_LENGTH 10');
l.add('                  # Time between two download cycles (bandwidth control)');
l.add('                  PerlSetEnv OCS_OPT_DOWNLOAD_CYCLE_LATENCY 60');
l.add('                  # Time between two fragment downloads (bandwidth control)');
l.add('                  PerlSetEnv OCS_OPT_DOWNLOAD_FRAG_LATENCY 60');
l.add('                  # Specify if you want to track packages affected to a group on computer''s level');
l.add('                  PerlSetEnv OCS_OPT_DOWNLOAD_GROUPS_TRACE_EVENTS 1');
l.add('                  # Time between two download periods (bandwidth control)');
l.add('                  PerlSetEnv OCS_OPT_DOWNLOAD_PERIOD_LATENCY 60');
l.add('                  # Agents will send ERR_TIMEOUT event and clean the package it is older than this setting');
l.add('                  PerlSetEnv OCS_OPT_DOWNLOAD_TIMEOUT 7');
l.add('                  # Number of cycle within a period');
l.add('  ');
l.add('                  # Enable ocs engine to deliver agent''s files (deprecated)');
l.add('                  PerlSetEnv OCS_OPT_DEPLOY 0');
l.add('                  # Enable the softwares deployment capacity (bandwidth control)');
l.add('  ');
l.add('                  # ===== GROUPS SETTINGS =====');
l.add('');
l.add('                  # Enable the computer\s groups feature');
l.add('                  PerlSetEnv OCS_OPT_ENABLE_GROUPS 1');
l.add('                  # Random number computed in the defined range. Designed to avoid computing many groups in the same process');
l.add('                  PerlSetEnv OCS_OPT_GROUPS_CACHE_OFFSET 43200');
l.add('                  # Specify the validity of computer''s groups (default: compute it once a day - see offset)');
l.add('                  PerlSetEnv OCS_OPT_GROUPS_CACHE_REVALIDATE 43200');
l.add('  ');
l.add('                  # ===== IPDISCOVER SETTINGS =====');
l.add('');
l.add('                  # Specify how much agent per LAN will discovered connected peripherals (0 to disable)');
l.add('                  PerlSetEnv OCS_OPT_IPDISCOVER 2');
l.add('                  # Specify the minimal difference to replace an ipdiscover agent');
l.add('                  PerlSetEnv OCS_OPT_IPDISCOVER_BETTER_THRESHOLD 1');
l.add('                  # Time between 2 arp requests (mini: 10 ms)');
l.add('                  PerlSetEnv OCS_OPT_IPDISCOVER_LATENCY 100');
l.add('                  # Specify when to remove a computer when it has not come until this period');
l.add('                  PerlSetEnv OCS_OPT_IPDISCOVER_MAX_ALIVE 14');
l.add('                  # Disable the time before a first election (not recommended)');
l.add('                  PerlSetEnv OCS_OPT_IPDISCOVER_NO_POSTPONE 0');
l.add('                  # Enable groups for ipdiscover (for example, you might want to prevent some groups to be ipdiscover agents)');
l.add('                  PerlSetEnv OCS_OPT_IPDISCOVER_USE_GROUPS 1');
l.add('  ');
l.add('                  # ===== INVENTORY FILES MAPPING SETTINGS =====');
l.add('');
l.add('                  # Use with ocsinventory-injector, enable the multi entities feature');
l.add('                  PerlSetEnv OCS_OPT_GENERATE_OCS_FILES 0');
l.add('                  # Generate either compressed file or clear XML text');
l.add('                  PerlSetEnv OCS_OPT_OCS_FILES_FORMAT OCS');
l.add('                  # Specify if you want to keep trace of all inventory between to synchronisation with the higher level server');
l.add('                  PerlSetEnv OCS_OPT_OCS_FILES_OVERWRITE 0');
l.add('                  # Path to ocs files directory (must be writeable)');
l.add('                  PerlSetEnv OCS_OPT_OCS_FILES_PATH /tmp');
l.add('');
l.add('                  # ===== FILTER SETTINGS =====');
l.add('');
l.add('                  # Enable prolog filter stack');
l.add('                  PerlSetEnv OCS_OPT_PROLOG_FILTER_ON 0');
l.add('                  # Enable core filter system to modify some things "on the fly"');
l.add('                  PerlSetEnv OCS_OPT_INVENTORY_FILTER_ENABLED 0');
l.add('                  # Enable inventory flooding filter. A dedicated ipaddress ia allowed to send a new computer only once in this period');
l.add('                  PerlSetEnv OCS_OPT_INVENTORY_FILTER_FLOOD_IP 0');
l.add('                  # Period definition for INVENTORY_FILTER_FLOOD_IP');
l.add('                  PerlSetEnv OCS_OPT_INVENTORY_FILTER_FLOOD_IP_CACHE_TIME 300');
l.add('                  # Enable inventory filter stack');
l.add('                  PerlSetEnv OCS_OPT_INVENTORY_FILTER_ON 0');
l.add('  ');
l.add('                  # ===== REGISTRY SETTINGS =====');
l.add('');
l.add('                  # Enable the registry capacity');
l.add('                  PerlSetEnv OCS_OPT_REGISTRY 1');
l.add('  ');
l.add('                  # ===== SESSION SETTINGS =====');
l.add('                  # Not yet in GUI');
l.add('');
l.add('                  # Validity of a session (prolog=>postinventory)');
l.add('                  PerlSetEnv OCS_OPT_SESSION_VALIDITY_TIME 600');
l.add('                  # Consider a session obsolete if it is older thant this value');
l.add('                  PerlSetEnv OCS_OPT_SESSION_CLEAN_TIME 86400');
l.add('                  # Accept an inventory only if required by server');
l.add('                  #( Refuse "forced" inventory)');
l.add('                  PerlSetEnv OCS_OPT_INVENTORY_SESSION_ONLY 0');
l.add('');
l.add('                  # ===== TAG =====');
l.add('');
l.add('                  # The default behavior of the server is to ignore TAG changes from the');
l.add('                   # agent.');
l.add('                   PerlSetEnv OCS_OPT_ACCEPT_TAG_UPDATE_FROM_CLIENT 0');
l.add('');
l.add('');
l.add('                   # ===== DEPRECATED =====');
l.add('');
l.add('                   # Set the proxy cache validity in http headers when sending a file');
l.add('                   PerlSetEnv OCS_OPT_PROXY_REVALIDATE_DELAY 3600');
l.add('                   # Deprecated');
l.add('                   PerlSetEnv OCS_OPT_UPDATE 0');
l.add('  ');
l.add('                   ############ DO NOT MODIFY BELOW ! #######################');
l.add('  ');
l.add('                   # External modules');
l.add('                   PerlModule Apache::DBI');
l.add('                   PerlModule Compress::Zlib');
l.add('                   PerlModule XML::Simple');
l.add('  ');
l.add('                   # Ocs');
l.add('                   PerlModule Apache::Ocsinventory');
l.add('                   PerlModule Apache::Ocsinventory::Server::Constants');
l.add('                   PerlModule Apache::Ocsinventory::Server::System');
l.add('                   PerlModule Apache::Ocsinventory::Server::Communication');
l.add('                   PerlModule Apache::Ocsinventory::Server::Inventory');
l.add('                   PerlModule Apache::Ocsinventory::Server::Duplicate');
l.add('');
l.add('                   # Capacities');
l.add('                   PerlModule Apache::Ocsinventory::Server::Capacities::Registry');
l.add('                   PerlModule Apache::Ocsinventory::Server::Capacities::Update');
l.add('                   PerlModule Apache::Ocsinventory::Server::Capacities::Ipdiscover');
l.add('                   PerlModule Apache::Ocsinventory::Server::Capacities::Download');
l.add('                   PerlModule Apache::Ocsinventory::Server::Capacities::Notify');
l.add('                   # This module guides you through the module creation');
l.add('                   # PerlModule Apache::Ocsinventory::Server::Capacities::Example');
l.add('                   # This module adds some rules to filter some request sent to ocs server in the prolog and inventory stages');
l.add('                   # PerlModule Apache::Ocsinventory::Server::Capacities::Filter');
l.add('  ');
l.add('                   # PerlTaintCheck On');
l.add('');
l.add('                   # SSL apache settings ');
l.add('                   #SSLEngine "SSL_ENABLE"');
l.add('                   #SSLCertificateFile "SSL_CERTIFICATE_FILE"');
l.add('                   #SSLCertificateKeyFile "SSL_CERTIFICATE_KEY_FILE"');
l.add('                   #SSLCACertificateFile "SSL_CERTIFICATE_FILE"');
l.add('                   #SSLCACertificatePath "SSL_CERTIFICATE_PATH"');
l.add('                   #SSLVerifyClient "SSL_VALIDATE_CLIENT"');
l.add('');
l.add('                   # Engine apache settings');
l.add('                   # "Virtual" directory for handling OCS Inventory NG agents communications');
l.add('                   # Be careful, do not create such directory into your web server root document !');
l.add('                   <Location /ocsinventory>');
l.add('	                      order deny,allow');
l.add('	                      allow from all');
l.add('	                      Satisfy Any');
l.add('	                      # If you protect this area you have to deal with http_auth_* agent''s parameters');
l.add('	                      # AuthType Basic');
l.add('	                      # AuthName "OCS Inventory agent area"');
l.add('	                      # AuthUserFile "APACHE_AUTH_USER_FILE"');
l.add('	                      # require valid-user');
l.add('                       SetHandler perl-script');
l.add('                       PerlHandler Apache::Ocsinventory');
l.add('                   </Location>');
l.add('');
l.add('                   # Web service apache settings');
l.add('                   PerlModule Apache::Ocsinventory::SOAP');
l.add('');
l.add('                  <location /ocsinterface>');
l.add('                        SetHandler perl-script');
l.add('                        PerlHandler "Apache::Ocsinventory::SOAP"');
l.add('');
l.add('                        # By default, you can query web service from everywhere with a valid user');
l.add('                        Order deny,allow');
l.add('                        Allow from all');
l.add('       	               AuthType Basic');
l.add('	                       AuthName "OCS Inventory SOAP Area"');
l.add('	                       # Use htpasswd to create/update soap-user (or another granted user)');
l.add('	                       AuthUserFile "APACHE_AUTH_USER_FILE"');
l.add('	                       require "SOAP_USER"');
l.add('                   </location>');
l.add('        </IfModule>');
result:=l.Text;
l.free;
end;
end.
