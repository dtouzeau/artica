unit gluster;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,RegExpr,zsystem,cyrus;



  type
  tgluster=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableGluster:integer;
     cyrus        :Tcyrus;
     function PID_NUM():string;
     function TARGER_MOUNT_FOLDERS(conffile:string):string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function    VERSION():string;
    procedure   START();
    procedure   STOP();
    FUNCTION    STATUS():string;
    function    RELOAD():string;
    procedure    START_CLIENT();
    function     MOUNTED_CLIENTS():Tstringlist;
    procedure    RESTART_PRODUCTS();
    procedure    STOP_CLIENT();

END;

implementation

constructor tgluster.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       EnableGluster:=1;
       cyrus:=Tcyrus.Create(SYS);
        if not FIleExists('/etc/artica-cluster/glusterfs-server.vol') then EnableGluster:=0;


       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tgluster.free();
begin
    logs.Free;
end;
//##############################################################################
function BIN_PATH():string;
begin
    if FIleExists('/usr/sbin/glusterfsd') then exit('/usr/sbin/glusterfsd');
end;
//##############################################################################

function tgluster.VERSION():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    filetmp:string;
    debug:boolean;
begin
if not FileExists(BIN_PATH()) then exit;
   debug:=false;
   debug:=SYS.COMMANDLINE_PARAMETERS('--debug');
   result:=SYS.GET_CACHE_VERSION('APP_GLUSTER');
if length(result)>0 then begin
   if debug then writeln('GET_CACHE_VERSION ->',result);
   exit;
end;


filetmp:=logs.FILE_TEMP();
if debug then writeln('/usr/sbin/glusterfsd -V >'+filetmp+' 2>&1');
fpsystem('/usr/sbin/glusterfsd -V >'+filetmp+' 2>&1');


if not FileExists(filetmp) then exit;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='glusterfs\s+([0-9\.]+)';
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
SYS.SET_CACHE_VERSION('APP_GLUSTER',result);

end;
//#############################################################################
function tgluster.PID_NUM():string;
var
   pid:string;
begin
    pid:=SYS.GET_PID_FROM_PATH('/var/run/glusterfsd');
    if not SYS.PROCESS_EXIST(pid) then pid:=SYS.PIDOF_PATTERN(BIN_PATH());
    result:=pid;
end;


//#############################################################################
procedure tgluster.RESTART_PRODUCTS();
begin
       if FileExists(cyrus.CYRUS_DAEMON_BIN_PATH()) then begin
          cyrus.WRITE_IMAPD_CONF();
          cyrus.CYRUS_DAEMON_RELOAD();
       end;

end;
//#############################################################################



function tgluster.RELOAD():string;
var
   pid:string;
begin
   result:='';
   pid:=PID_NUM();
   if not SYS.PROCESS_EXIST(pid) then begin
      START();
      exit;
   end;
   logs.OutputCmd(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.gluster.php --conf');
   logs.OutputCmd('/bin/kill -HUP '+pid);

end;


//#############################################################################
procedure tgluster.START();
var
   pid:string;
   ck:integer;
   cmd:string;
begin

   if not FileExists(BIN_PATH()) then begin
      logs.DebugLogs('Starting......: Gluster Not installed');
      exit;
   end;

   if not FIleExists('/etc/artica-cluster/glusterfs-server.vol') then begin
        logs.DebugLogs('Starting......: Gluster Not notified/configured');
        exit;
   end;

   pid:=PID_NUM();
   if SYS.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting......: Gluster Already running using PID '+pid);
      START_CLIENT();
      exit;
   end;

   logs.OutputCmd(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.gluster.php --conf');
   cmd:=BIN_PATH() +' -f /etc/artica-cluster/glusterfs-server.vol -l /var/log/glusterfs/glusterfs.log --pid-file=/var/run/glusterfsd';
   logs.DebugLogs('Starting......: Gluster server mode '+ cmd);
   fpsystem(cmd);

   pid:=PID_NUM();
       ck:=0;
       while not SYS.PROCESS_EXIST(pid) do begin
           pid:=PID_NUM();
           sleep(100);
           inc(ck);
           if ck>40 then begin
                logs.DebugLogs('Starting......: Gluster server timeout...');
                break;
           end;
       end;

    pid:=PID_NUM();
    if not SYS.PROCESS_EXIST(pid) then begin
       logs.DebugLogs('Starting......: Gluster server failed...');

    end else begin
        logs.DebugLogs('Starting......: Gluster server success PID '+pid);
    end;
    START_CLIENT();

    logs.OutputCmd(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.gluster.php --notify-server');


end;
//#############################################################################
procedure tgluster.STOP();
var
 pid:string;
 count:integer;
begin

  pid:=PID_NUM();
  count:=0;

   if not FileExists(BIN_PATH()) then begin
      writeln('Stopping Gluster server......: Not installed');
      exit;
   end;   


  if not SYS.PROCESS_EXIST(pid) then begin
     writeln('Stopping Gluster server......: Already stopped');
     STOP_CLIENT();
     exit;
  end;

      writeln('Stopping Gluster server......: Stopping PID '+pid);
     fpsystem('/bin/kill ' + pid);
     while SYS.PROCESS_EXIST(pid) do begin
           Inc(count);
           sleep(100);
           if count>50 then begin
              writeln('Stopping Gluster server......: ' + pid + ' PID (timeout)');
              fpsystem('/bin/kill -9 ' + pid);
              break;
           end;
           pid:=PID_NUM();
     end;
     pid:=PID_NUM();
     if SYS.PROCESS_EXIST(pid) then begin
           writeln('Stopping Gluster server......: ' + pid + '  failed already exists PID '+ pid);
           exit;
     end;

       writeln('Stopping Gluster server......: success');
       STOP_CLIENT();



end;
//#############################################################################

procedure tgluster.START_CLIENT();
var
   i:integer;
   RegExpr:TRegExpr;
   direct,confpath,cmd:string;
   count:integer;
begin

   logs.OutputCmd(SYS.LOCATE_PHP5_BIN() +' /usr/share/artica-postfix/exec.gluster.php --build-replicator');

   SYS.DirFiles('/etc/artica-cluster','dispatcher*');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='dispatcher-([0-9]+).vol';
   for i:=0 to SYS.DirListFiles.Count-1 do begin
       if not RegExpr.Exec(SYS.DirListFiles.Strings[i]) then continue;
       confpath:='/etc/artica-cluster/'+SYS.DirListFiles.Strings[i];
       direct:=TARGER_MOUNT_FOLDERS(confpath);
       if length(trim(direct))=0 then continue;
       if not SYS.IS_GLFS_MOUNTED(direct) then begin
          forceDirectories(direct);
          cmd:='/usr/sbin/glusterfs --log-file=/var/log/glusterfs/'+SYS.DirListFiles.Strings[i]+'.log --log-level=DEBUG -f '+confpath+' '+direct;
          logs.DebugLogs('Starting......: Gluster replicator mount '+direct );
          logs.OutputCmd(cmd);
          count:=0;
          while not SYS.IS_GLFS_MOUNTED(direct) do begin
              sleep(100);
              inc(count);
              if count>20 then begin
                   logs.DebugLogs('Starting......: Gluster replicator mount '+direct+' time-out' );
                   break;
              end;
          end;

          if SYS.IS_GLFS_MOUNTED(direct) then begin
               logs.DebugLogs('Starting......: Gluster replicator mount '+direct+' success' );
          end;



       end else begin
          logs.DebugLogs('Starting......: Gluster replicator mount folder-'+RegExpr.Match[1]+' Already mounted');
       end;
   end;

      RESTART_PRODUCTS();

end;
//#############################################################################

function tgluster.TARGER_MOUNT_FOLDERS(conffile:string):string;
var

   i:integer;
   RegExpr:TRegExpr;
   tmpstr:Tstringlist;
begin
   result:='';
   tmpstr:=TstringList.Create;
   tmpstr.LoadFromFile(conffile);
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='<DIR>(.+?)</DIR>';


   for i:=0 to tmpstr.Count-1 do begin
       if RegExpr.Exec(tmpstr.Strings[i]) then begin
          result:=RegExpr.Match[1];
          break;
       end;
   end;

  tmpstr.free;
  RegExpr.free;
end;

//#############################################################################
function tgluster.MOUNTED_CLIENTS():Tstringlist;
var
   l:tstringlist;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:Tstringlist;
begin

   tmpstr:=TstringList.Create;
   tmpstr.LoadFromFile('/proc/mounts');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='(.+?)\s+(.+?)\s+fuse\.glusterfs\s+';
   l:=Tstringlist.Create;

   for i:=0 to tmpstr.Count-1 do begin
       if RegExpr.Exec(tmpstr.Strings[i]) then l.Add(RegExpr.Match[2]);
   end;

  tmpstr.free;
  RegExpr.free;
  result:=l;
end;
//#############################################################################


procedure tgluster.STOP_CLIENT();
var
   i:integer;
   tmpstr:tSTRINGLIST;
begin

   tmpstr:=TstringList.Create;
   tmpstr.AddStrings(MOUNTED_CLIENTS());
   writeln('Stopping Gluster client......: '+ iNTtOsTR(tmpstr.Count) +' mounted directories');
   for i:=0 to tmpstr.Count-1 do begin
         if length(trim(tmpstr.Strings[i]))=0 then continue;
         writeln('Stopping Gluster client......: umount '+tmpstr.Strings[i]);
         fpsystem('umount '+tmpstr.Strings[i]);
   end;

   tmpstr.Clear();
   tmpstr.AddStrings(MOUNTED_CLIENTS());
   if tmpstr.Count>0 then begin
      writeln('Stopping Gluster client......: '+ iNTtOsTR(tmpstr.Count) +' mounted directories to force kill');
      for i:=0 to tmpstr.Count-1 do begin
         if length(trim(tmpstr.Strings[i]))=0 then continue;
         writeln('Stopping Gluster client......: umount '+tmpstr.Strings[i]);
         fpsystem('umount -l '+tmpstr.Strings[i]);
      end;
   end;

   tmpstr.Clear();
   tmpstr.AddStrings(MOUNTED_CLIENTS());
   if tmpstr.Count=0 then begin
      writeln('Stopping Gluster client......: success');
      RESTART_PRODUCTS();
   end else begin
       writeln('Stopping Gluster client......: failed');
   end;
   

end;


FUNCTION tgluster.STATUS():string;
var
   ini:TstringList;
   pid:string;
begin

 //

if not FileExists(BIN_PATH()) then exit;
      ini:=TstringList.Create;
      ini.Add('[GLUSTER]');
      ini.Add('service_name=APP_GLUSTER');
      ini.Add('service_cmd=gluster');
      ini.Add('service_disabled='+ IntToStr(EnableGluster));
      ini.Add('master_version=' +VERSION());

      if EnableGluster=0 then begin
         result:=ini.Text;
         SYS.MONIT_DELETE('APP_GLUSTER');
         exit;
      end;

if SYS.MONIT_CONFIG('APP_GLUSTER','/var/run/glusterfsd','gluster') then begin
      ini.Add('monit=1');
      result:=ini.Text;
      exit;
end;

      pid:=PID_NUM();
      if SYS.PROCESS_EXIST(pid) then ini.Add('running=1') else  ini.Add('running=0');
      ini.Add('application_installed=1');
      ini.Add('master_pid='+ pid);
      ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(pid)));

      ini.Add('status='+SYS.PROCESS_STATUS(pid));


      result:=ini.Text;
      ini.free;
end;
//#########################################################################################

end.
