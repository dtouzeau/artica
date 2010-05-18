unit dhcp_server;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,tcpip,
    RegExpr in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/RegExpr.pas',
    zsystem in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/zsystem.pas';


  type
  tdhcp3=class


private
     LOGS:Tlogs;
     artica_path:string;
     SYS:Tsystem;
     EnableDHCPServer:integer;
     function DAEMON_PID():string;
     function READ_PID():string;
     function PID_PATH():string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function  STATUS():string;
    function  BIN_PATH():string;
    procedure START();
    procedure STOP();
    function  VERSION():string;
    function  INIT_PATH():string;
    function  DEFAULT_PATH():string;
    function  CONF_PATH():string;
    procedure ApplyConf();

END;

implementation

constructor tdhcp3.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       EnableDHCPServer:=0;
       SYS:=zSYS;
       
       if not TryStrToInt(SYS.GET_INFO('EnableDHCPServer'),EnableDHCPServer) then EnableDHCPServer:=0;
       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tdhcp3.free();
begin
    logs.Free;
end;
//##############################################################################
function tdhcp3.BIN_PATH():string;
begin
    if FileExists('/usr/sbin/dhcpd') then exit('/usr/sbin/dhcpd');
    if FileExists('/usr/sbin/dhcpd3') then exit('/usr/sbin/dhcpd3');
end;
//#############################################################################
function tdhcp3.INIT_PATH():string;
begin
    if FileExists('/etc/init.d/dhcpd') then exit('/etc/init.d/dhcpd');
    if FileExists('/etc/init.d/dhcp3-server') then exit('/etc/init.d/dhcp3-server');
//etc/sysconfig/dhcpd
///etc/default/dhcp3-server
end;
//#############################################################################
function tdhcp3.CONF_PATH():string;
begin
    if FIleExists('/etc/dhcp3/dhcpd.conf') then exit('/etc/dhcp3/dhcpd.conf');
    if FIleExists('/etc/dhcpd.conf') then exit('/etc/dhcpd.conf');
    if FIleExists('/etc/dhcpd/dhcpd.conf') then exit('/etc/dhcpd/dhcpd.conf');
end;
//#############################################################################
function tdhcp3.DEFAULT_PATH():string;
begin
    if FIleExists('/etc/default/dhcp3-server') then exit('/etc/default/dhcp3-server');
    if FIleExists('/etc/sysconfig/dhcpd') then exit('/etc/sysconfig/dhcpd');
end;
//#############################################################################
function tdhcp3.VERSION():string;
var
   i:integer;
   l:Tstringlist;
   RegExpr:TRegExpr;
   tmpstr:string;
begin
   if not FileExists(BIN_PATH()) then exit;
   
   result:=SYS.GET_CACHE_VERSION('APP_DHCP');
   if length(result)>0 then exit;
   
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='V([0-9\.]+)';
   tmpstr:=LOGS.FILE_TEMP();
   fpsystem(BIN_PATH() + ' -h >'+tmpstr+' 2>&1');
   if not FileExists(tmpstr) then exit;
   l:=TstringList.Create;
   l.LoadFromFile(tmpstr);
   logs.DeleteFile(tmpstr);
   for i:=0 to l.Count-1 do begin
       if RegExpr.Exec(l.Strings[i]) then begin
          result:=RegExpr.Match[1];
          break;
       end;
   end;
   
   l.Free;
   RegExpr.Free;
   SYS.SET_CACHE_VERSION('APP_DHCP',result);
   
end;
//#############################################################################
function tdhcp3.DAEMON_PID():string;
var
   pid:string;
begin
   pid:=READ_PID();
   if length(pid)=0 then pid:=SYS.PIDOF(BIN_PATH());
   exit(pid);
end;
//##############################################################################
function tdhcp3.PID_PATH():string;
begin
if FileExists('/var/run/dhcpd.pid') then exit('/var/run/dhcpd.pid');
if FileExists('/var/run/dhcpd/dhcpd.pid') then exit('/var/run/dhcpd/dhcpd.pid');
end;
function tdhcp3.READ_PID():string;
begin
exit(SYS.GET_PID_FROM_PATH(PID_PATH()));
end;
//##############################################################################
procedure tdhcp3.START();
var
   pid,cmd:string;
   count:integer;
begin
    count:=0;
    logs.DebugLogs('################# DHCP SERVER ######################');

    if not FileExists(BIN_PATH()) then begin
       logs.DebugLogs('Starting......: DHCP server is not installed...');
       exit;
    end;

    if EnableDHCPServer=0 then begin
        logs.DebugLogs('Starting......: DHCP server is disabled...');
        STOP();
        exit;
    end;

    pid:=DAEMON_PID();
    if SYS.PROCESS_EXIST(pid) then begin
       logs.DebugLogs('Starting......: DHCP SERVER already exists using pid ' + pid+ '...');
       exit;
    end;
    ApplyConf();
    cmd:=INIT_PATH() + ' start';
    logs.OutputCmd(cmd);

        while not SYS.PROCESS_EXIST(DAEMON_PID()) do begin
              sleep(150);
              inc(count);
              if count>100 then begin
                 logs.DebugLogs('Starting......: DHCP Server daemon. (timeout!!!)');
                 break;
              end;
        end;

    if not SYS.PROCESS_EXIST(DAEMON_PID()) then begin
         logs.DebugLogs('Starting......: DHCP server daemon. (failed!!!)');
    end else begin
         logs.DebugLogs('Starting......: DHCP server daemon. PID '+DAEMON_PID());

    end;
end;
//##############################################################################

procedure tdhcp3.STOP();
var pid:string;
begin

    if not FileExists(BIN_PATH()) then begin
       writeln('Stopping DHCP Server.....: not installed');
       exit;
    end;


    pid:=DAEMON_PID();
    if not SYS.PROCESS_EXIST(pid) then begin
       writeln('Stopping DHCP Server.....: Already stopped');
       exit;
    end;
    writeln('Stopping DHCP Server.....: ' + pid + ' PID');
    
    logs.OutputCmd(INIT_PATH+' stop');

end;
//##############################################################################
function tdhcp3.STATUS():string;
var
   ini:TstringList;
   pid:string;
begin
ini:=TstringList.Create;
pid:=DAEMON_PID();

   if not FileExists(BIN_PATH()) then exit;
   ini.Add('[DHCPD]');
   ini.Add('service_name=APP_DHCP');
   ini.Add('service_cmd=dhcp');
   ini.Add('service_disabled='+IntToStr(EnableDHCPServer));
   ini.Add('master_version='+VERSION());
   if EnableDHCPServer=0 then begin
      result:=ini.Text;
      ini.free;
      SYS.MONIT_DELETE('APP_DHCP');
      exit;
   end;


   logs.Debuglogs('DHCP PID:'+PID_PATH());

   if SYS.MONIT_CONFIG('APP_DHCP',PID_PATH(),'dhcp') then begin
      ini.Add('monit=1');
      result:=ini.Text;
      ini.free;
      exit;
   end;


   if SYS.PROCESS_EXIST(pid) then ini.Add('running=1') else  ini.Add('running=0');
   ini.Add('application_installed=1');
   ini.Add('master_pid='+ pid);
   ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(pid)));
   ini.Add('status='+SYS.PROCESS_STATUS(pid));
   result:=ini.Text;
   ini.free;
    logs.Debuglogs('DHCP STATUS OK');
end;
//#########################################################################################
procedure tdhcp3.ApplyConf();
var
   l:TstringList;
   DHCP3ConfigurationFile:string;
   DHCP3ListenNIC:string;
   tcp:ttcpip;
   ipAddr:string;
begin

DHCP3ConfigurationFile:=SYS.GET_INFO('DHCP3ConfigurationFile');
DHCP3ListenNIC:=trim(SYS.GET_INFO('DHCP3ListenNIC'));
if length(DHCP3ConfigurationFile)=0 then exit;
if length(DHCP3ListenNIC)=0 then exit;
logs.Syslogs('Starting......: DHCP server Change configuration "'+CONF_PATH()+'"');

l:=TstringList.Create;
l.Add(DHCP3ConfigurationFile);
logs.WriteToFile(l.Text,CONF_PATH());
l.Clear;


if EnableDHCPServer=1 then begin
tcp:=ttcpip.Create;
   ipAddr:=tcp.IP_ADDRESS_INTERFACE(DHCP3ListenNIC);
   logs.Syslogs('Starting......: DHCP server '+DHCP3ListenNIC+' "'+ipAddr+'"');
   if ipAddr='0.0.0.0' then begin
      logs.Syslogs('Starting......: DHCP server testing if '+DHCP3ListenNIC+' in not linked to br0');
      ipAddr:=tcp.IP_ADDRESS_INTERFACE('br0');
      if ipAddr<>'0.0.0.0' then begin
          logs.Syslogs('Starting......: DHCP server change '+DHCP3ListenNIC+' to br0 for this instance...');
          DHCP3ListenNIC:='br0';
      end;
   end;


   l.Add('INTERFACES='+DHCP3ListenNIC);
   l.Add('DHCPDARGS="'+DHCP3ListenNIC+'"');
   logs.WriteToFile(l.Text,DEFAULT_PATH());
end;





end;
//#########################################################################################





end.

