unit squidguard;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,logs,unix,RegExpr,zsystem;



  type
  tsquidguard=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     cdirlist:string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   CONFIG_DEFAULT();

    function    VERSION():string;
    function    BIN_PATH():string;
    function    VERSIONNUM():integer;
    procedure   START();
    function    STATUS:string;
    procedure   RELOAD();
    procedure   BuildStatus();

END;

implementation

constructor tsquidguard.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;

       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tsquidguard.free();
begin
    logs.Free;
end;
//##############################################################################
function tsquidguard.BIN_PATH():string;
begin
   if FileExists(SYS.LOCATE_GENERIC_BIN('squidGuard')) then exit(SYS.LOCATE_GENERIC_BIN('squidGuard'));
end;
//##############################################################################
function tsquidguard.VERSION():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    BinPath:string;
    filetmp:string;
    nocache:boolean;
begin
nocache:=false;
if ParamStr(1)='--squid-version-bin' then nocache:=true;

if not nocache then begin
   result:=SYS.GET_CACHE_VERSION('APP_SQUIDGUARD');
   if length(result)>2 then exit;
end;
filetmp:=logs.FILE_TEMP();
if not FileExists(BIN_PATH()) then begin
   logs.Debuglogs('unable to find squidGuard');
   exit;
end;

logs.Debuglogs(BIN_PATH()+' -v >'+filetmp+' 2>&1');
fpsystem(BIN_PATH()+' -v >'+filetmp+' 2>&1');

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='SquidGuard:\s+([0-9\.]+)';
    FileDatas:=TStringList.Create;
    FileDatas.LoadFromFile(filetmp);
    logs.DeleteFile(filetmp);
    for i:=0 to FileDatas.Count-1 do begin
        if RegExpr.Exec(FileDatas.Strings[i]) then begin
             result:=RegExpr.Match[1];
             break;
        end;
    end;
             RegExpr.free;
             FileDatas.Free;

SYS.SET_CACHE_VERSION('APP_SQUIDGUARD',result);

end;
//#############################################################################
procedure tsquidguard.START();
var
   count:integer;
   pid:string;
   loglevel:integer;
   straces:string;
begin

end;


//#############################################################################
procedure tsquidguard.RELOAD();
begin
fpsystem(BIN_PATH()+' -c /etc/monit/monitrc -p /var/run/monit/monit.pid reload');
end;
//#############################################################################

function tsquidguard.STATUS:string;
var
ini:TstringList;
pid:string;
begin
   if not fileExists(BIN_PATH()) then exit;
   ini:=TstringList.Create;
   ini.Add('[APP_MONIT]');
      ini.Add('service_name=APP_MONIT');
      ini.Add('service_cmd=monit');
      ini.Add('service_disabled=1');
      ini.Add('application_installed=0');
      ini.Add('service_disabled=0');
      ini.Add('master_version='+VERSION());


   if SYS.MONIT_CONFIG('APP_MONIT','/var/run/monit/monit.pid','monit') then begin
      ini.Add('monit=1');
      result:=ini.Text;
      ini.free;
      logs.Debuglogs('tmonit.STATUS(): done.');
      exit;
   end;

      pid:='0';
      if SYS.PROCESS_EXIST(pid) then ini.Add('running=1') else  ini.Add('running=0');
      ini.Add('application_installed=1');
      ini.Add('application_enabled=1');
      ini.Add('master_pid='+ pid);
      ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(pid)));
      ini.Add('status='+SYS.PROCESS_STATUS(pid));
      ini.Add('pid_path=/var/run/monit/monit.pid');
      result:=ini.Text;
      ini.free;

end;
//##############################################################################
function tsquidguard.VERSIONNUM():integer;
var
   zversion:string;
begin
    zversion:=VERSION();
    zversion:=AnsiReplaceText(zversion,'.','');
    if length(zversion)=3 then zversion:=zversion+'0';
    TryStrToInt(zversion,result);

end;

procedure tsquidguard.BuildStatus();
begin
    fpsystem(BIN_PATH()+' -c /etc/monit/monitrc -p /var/run/monit/monit.pid status');
end;
//##############################################################################
procedure tsquidguard.CONFIG_DEFAULT();
var
   l:Tstringlist;
   i:integer;
   cpunum:integer;
   normal:integer;
   normal2:integer;
   busy:integer;
   notif:TiniFile;
   EnableNotifs:integer;
   smtp_server:string;
   smtp_server_port:string;
   smtp_sender:string;
   smtp_dest:string;
   smtp_auth_user:string;
   smtp_auth_passwd:string;
   tls_enabled:integer;
   recipients:Tstringlist;
   bcc:Tstringlist;
   myversion:integer;
begin
ForceDirectories('/var/monit');
ForceDirectories('/var/run/monit');
ForceDirectories('/etc/monit/conf.d');
myversion:=VERSIONNUM();
l:=Tstringlist.Create;
logs.DebugLogs('Starting......: daemon monitor version '+ INtTOstr(myversion));

notif:=TiniFile.Create('/etc/artica-postfix/smtpnotif.conf');
EnableNotifs:=notif.ReadInteger('SMTP','monit',0);
tls_enabled:=notif.ReadInteger('SMTP','tls_enabled',0);


smtp_server:=notif.ReadString('SMTP','smtp_server_name','');
smtp_server_port:=notif.ReadString('SMTP','smtp_server_port','25');
smtp_dest:=notif.ReadString('SMTP','smtp_dest','');
smtp_sender:=notif.ReadString('SMTP','smtp_sender','');
smtp_auth_user:=trim(notif.ReadString('SMTP','smtp_auth_user',''));
smtp_auth_passwd:=notif.ReadString('SMTP','smtp_auth_passwd','');
recipients:=Tstringlist.Create;
if length(smtp_dest)>0 then recipients.Add(smtp_dest);
bcc:=Tstringlist.Create;
if FIleExists('/etc/artica-postfix/settings/Daemons/SmtpNotificationConfigCC') then begin
   bcc.LoadFromFile('/etc/artica-postfix/settings/Daemons/SmtpNotificationConfigCC');
   for i:=0 to bcc.Count-1 do begin
       if length(trim(bcc.Strings[i]))>0 then recipients.Add(trim(bcc.Strings[i]));
   end;
end;
bcc.free;
smtp_server:=trim(smtp_server);
if length(smtp_server)=0 then  EnableNotifs:=0;
if length(smtp_sender)=0 then  EnableNotifs:=0;
if recipients.Count=0 then EnableNotifs:=0;
if length(trim(smtp_server_port))=0 then smtp_server_port:='25';

if myversion<5000 then begin
  l.add('set daemon 60');

end else begin
  l.add('set daemon 60 with start delay 20');
  l.add('set idfile /var/run/monit/monit.id');
end;

cpunum:=SYS.CPU_NUMBER();
normal:=(cpunum*2)+1;
normal2:=cpunum*2;
busy:=cpunum*4;


l.add('set logfile syslog facility log_daemon');

l.add('set statefile /var/run/monit/monit.state');
l.add('');
if EnableNotifs=1 then begin
    l.add('set mailserver '+smtp_server+' PORT '+smtp_server_port);
    if length(smtp_auth_user)>0  then L.Add(chr(9)+'USERNAME "'+smtp_auth_user+'" PASSWORD "'+smtp_auth_passwd+'"');
    if tls_enabled=1 then L.Add(chr(9)+'using TLSV1');
    l.add('set eventqueue');
    l.add('     basedir /var/monit');
    l.add('     slots 100');

   l.add('set mail-format {');
   l.add('   from: '+smtp_sender);
   l.add('   subject: Artica service monitor: $SERVICE $EVENT');
   l.add('   message: Artica service monitor $ACTION $SERVICE at $DATE on $HOST: $DESCRIPTION.');
   l.add('}');
   l.add('');
   for i:=0 to recipients.Count-1 do begin
       l.add('set alert '+recipients.Strings[i]+' but not on { instance,action}');
   end;
   l.add('');
end;
l.add('set httpd port 2874 and use address localhost allow localhost');
l.add('');
l.add('check system '+SYS.HOSTNAME_g());
l.add('    if loadavg (1min) > '+IntToStr(busy)+' then exec "' +SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.watchdog.php --loadavg"');
l.add('    if loadavg (5min) > '+IntToStr(normal)+' then exec "' +SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.watchdog.php --loadavg"');
l.add('    if loadavg (15min) > '+IntToStr(normal2)+' then exec "'+ SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.watchdog.php --loadavg"');
l.add('    if memory usage > 75% then exec "'+SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.watchdog.php --mem"');
l.add('    if cpu usage (user) > 80% then exec "'+SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.watchdog.php --cpu"');
l.add('    if cpu usage (system) > 80% then exec "'+SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.watchdog.php --cpu"');
l.add('');
l.add('include /etc/monit/conf.d/*');
logs.WriteToFile(l.Text,'/etc/monit/monitrc');
logs.DebugLogs('Starting......: daemon monitor succes writing configuration file');
fpsystem('/bin/chmod -R 600 /etc/monit/monitrc');
logs.DebugLogs('Starting......: Launching status and build monitor configurations files');
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.first.settings.php --monit >/dev/null 2>&1');
fpsystem('/usr/share/artica-postfix/bin/artica-install --status >/dev/null 2>&1');
logs.DebugLogs('Starting......: done');
l.free;
end;


end.
