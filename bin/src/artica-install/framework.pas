unit framework;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,md5,logs,unix,RegExpr in 'RegExpr.pas',zsystem,awstats,mailmanctl,tcpip,openldap,Baseunix;

type
  TStringDynArray = array of string;

  type
  tframework=class


private
     LOGS:Tlogs;
     D:boolean;
     GLOBAL_INI:TiniFIle;
     SYS:TSystem;
     artica_path:string;
     awstats:tawstats;
     pid_root_path:string;
     mem_pid:string;
    function    Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;




public
EnableLighttpd:integer;
    InsufficentRessources:Boolean;
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START(notroubleshoot:boolean=false);
    function    LIGHTTPD_BIN_PATH():string;
    function    LIGHTTPD_PID():string;
    procedure   STOP();
    function    LIGHTTPD_VERSION():string;
    FUNCTION    STATUS():string;
    function    PHP5_CGI_BIN_PATH():string;
    function    DEFAULT_CONF():string;
    function    MON():string;

END;

implementation                      

constructor tframework.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       forcedirectories('/opt/artica/tmp');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       EnableLighttpd:=1;
       awstats:=tawstats.Create(SYS);
        InsufficentRessources:=SYS.ISMemoryHiger1G();



       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tframework.free();
begin
    logs.Free;
    SYS.Free;
end;
//##############################################################################
function tframework.LIGHTTPD_BIN_PATH():string;
begin
exit(SYS.LOCATE_LIGHTTPD_BIN_PATH());
end;
//##############################################################################
function tframework.PHP5_CGI_BIN_PATH():string;
begin
   if FileExists('/usr/bin/php-fcgi') then exit('/usr/bin/php-fcgi');
   if FileExists('/usr/bin/php-cgi') then exit('/usr/bin/php-cgi');
   if FileExists('/usr/local/bin/php-cgi') then exit('/usr/local/bin/php-cgi');
end;
//##############################################################################
procedure tframework.START(notroubleshoot:boolean);
var
   cmdline:string;
   count:integer;
   pid:string;
   user:string;
   group:string;
   logs_path:string;
   daemon:boolean;
   RegExpr:TRegExpr;
begin
   daemon:=LOGS.COMMANDLINE_PARAMETERS('--daemon');

logs.Debuglogs('###################### FRAMEWORK #####################');
   count:=0;
   if not FileExists(LIGHTTPD_BIN_PATH()) then begin
       logs.Debuglogs('LIGHTTPD_START():: it seems that lighttpd is not installed... Aborting');
       exit;
   end;

    if FileExists('/var/log/artica-postfix/framework.log') then logs.DeleteFile('/var/log/artica-postfix/framework.log');

   pid:=LIGHTTPD_PID();


   if SYS.PROCESS_EXIST(pid) then begin
      logs.Debuglogs('Starting......: framework daemon is already running using PID ' + LIGHTTPD_PID() + '...');
      logs.Debuglogs('LIGHTTPD_START():: framework already running with PID number ' + pid);
      exit();
   end;

    DEFAULT_CONF();
    logs.OutputCmd(LIGHTTPD_BIN_PATH()+ ' -f /etc/artica-postfix/framework.conf');


   if not SYS.PROCESS_EXIST(LIGHTTPD_PID()) then begin
      logs.Debuglogs('Starting framework...........: Failed "' + LIGHTTPD_BIN_PATH()+ ' -f /etc/artica-postfix/framework.conf"');
    end else begin
      logs.Debuglogs('Starting framework...........: Success (PID ' + LIGHTTPD_PID() + ')');
   end;

end;
//##############################################################################
function tframework.MON():string;
var
l:TstringList;
begin
l:=TstringList.Create;
l.ADD('check process '+ExtractFileName(LIGHTTPD_BIN_PATH())+' with pidfile /var/run/lighttpd/framework.pid');
l.ADD('group framework');
l.ADD('start program = "/etc/init.d/artica-postfix start apache"');
l.ADD('stop program = "/etc/init.d/artica-postfix stop apache"');
l.ADD('if 5 restarts within 5 cycles then timeout');
result:=l.Text;
l.free;
end;
//##############################################################################


procedure tframework.STOP();
 var
    count      :integer;
begin

     count:=0;

     logs.DeleteFile('/etc/artica-postfix/cache.global.status');
     if SYS.PROCESS_EXIST(LIGHTTPD_PID()) then begin
        writeln('Stopping framework...........: ' + LIGHTTPD_PID() + ' PID..');
        logs.OutputCmd('/bin/kill ' + LIGHTTPD_PID());

        while SYS.PROCESS_EXIST(LIGHTTPD_PID()) do begin
              sleep(100);
              inc(count);
              if count>100 then begin
                 writeln('Stopping framework...........: Failed force kill');
                 logs.OutputCmd('/bin/kill -9 '+LIGHTTPD_PID());
                 exit;
              end;
        end;

      end else begin

        writeln('Stopping framework...........: Already stopped');
     end;

end;
//##############################################################################
FUNCTION tframework.STATUS():string;
var
pidpath:string;
begin
SYS.MONIT_DELETE('APP_FRAMEWORK');
pidpath:=logs.FILE_TEMP();
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --framework >'+pidpath +' 2>&1');
result:=logs.ReadFromFile(pidpath);
logs.DeleteFile(pidpath);
end;
//#########################################################################################
function tframework.LIGHTTPD_VERSION():string;
var
     l:TstringList;
     RegExpr:TRegExpr;
     i:integer;
     tmpstr:string;
begin
    if not FileExists(LIGHTTPD_BIN_PATH()) then exit;

    result:=SYS.GET_CACHE_VERSION('APP_LIGHTTPD');
    if length(result)>0 then exit;
    tmpstr:=logs.FILE_TEMP();

    fpsystem(LIGHTTPD_BIN_PATH()+' -v >'+tmpstr+' 2>&1');
    if not FileExists(tmpstr) then exit;
    l:=TStringList.Create;
    l.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='lighttpd-([0-9\.]+)';
    For i:=0 to l.Count-1 do begin
        if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            logs.Debuglogs('LIGHTTPD_VERSION:: ' + result);
        end;
    end;

    SYS.SET_CACHE_VERSION('APP_LIGHTTPD',result);

    l.free;
    RegExpr.Free;
end;
//##############################################################################
function tframework.LIGHTTPD_PID():string;
begin

   result:=SYS.PIDOF_PATTERN(LIGHTTPD_BIN_PATH() + ' -f /etc/artica-postfix/framework.conf');
   mem_pid:=result;
   exit;
end;
//##############################################################################
function tframework.DEFAULT_CONF():string;
var
l:TstringList;
mailman:tmailman;
user:string;
RegExpr:TRegExpr;
group,name:string;
PHP_FCGI_CHILDREN:Integer;
PHP_FCGI_MAX_REQUESTS:integer;
max_procs:integer;
begin
l:=TstringList.Create;

forceDirectories('/usr/share/artica-postfix/framework');
forceDirectories('/usr/share/artica-postfix/ressources/sock');
fpsystem('/bin/chmod -R 755 /usr/share/artica-postfix/framework');

PHP_FCGI_CHILDREN:=3;
max_procs:=2;
PHP_FCGI_MAX_REQUESTS:=500;
if not InsufficentRessources then begin
     PHP_FCGI_CHILDREN:=2;
     PHP_FCGI_MAX_REQUESTS:=1000;
     max_procs:=1;
end;



l.Add('#artica-postfix saved by artica lighttpd.conf');
l.Add('');
l.Add('server.modules = (');
l.Add('        "mod_alias",');
l.Add('        "mod_access",');
l.Add('        "mod_accesslog",');
l.Add('        "mod_compress",');
l.Add('        "mod_fastcgi",');
l.Add('        "mod_cgi",');
l.Add('	       "mod_status"');
l.Add(')');
l.Add('');
l.Add('server.document-root        = "/usr/share/artica-postfix/framework"');
//l.Add('server.username = "root"');
//l.Add('server.groupname = "root"');
l.Add('server.errorlog             = "/var/log/artica-postfix/framework_error.log"');
l.Add('index-file.names            = ( "index.php")');
l.Add('');
l.Add('mimetype.assign             = (');
l.Add('  ".pdf"          =>      "application/pdf",');
l.Add('  ".sig"          =>      "application/pgp-signature",');
l.Add('  ".spl"          =>      "application/futuresplash",');
l.Add('  ".class"        =>      "application/octet-stream",');
l.Add('  ".ps"           =>      "application/postscript",');
l.Add('  ".torrent"      =>      "application/x-bittorrent",');
l.Add('  ".dvi"          =>      "application/x-dvi",');
l.Add('  ".gz"           =>      "application/x-gzip",');
l.Add('  ".pac"          =>      "application/x-ns-proxy-autoconfig",');
l.Add('  ".swf"          =>      "application/x-shockwave-flash",');
l.Add('  ".tar.gz"       =>      "application/x-tgz",');
l.Add('  ".tgz"          =>      "application/x-tgz",');
l.Add('  ".tar"          =>      "application/x-tar",');
l.Add('  ".zip"          =>      "application/zip",');
l.Add('  ".mp3"          =>      "audio/mpeg",');
l.Add('  ".m3u"          =>      "audio/x-mpegurl",');
l.Add('  ".wma"          =>      "audio/x-ms-wma",');
l.Add('  ".wax"          =>      "audio/x-ms-wax",');
l.Add('  ".ogg"          =>      "application/ogg",');
l.Add('  ".wav"          =>      "audio/x-wav",');
l.Add('  ".gif"          =>      "image/gif",');
l.Add('  ".jar"          =>      "application/x-java-archive",');
l.Add('  ".jpg"          =>      "image/jpeg",');
l.Add('  ".jpeg"         =>      "image/jpeg",');
l.Add('  ".png"          =>      "image/png",');
l.Add('  ".xbm"          =>      "image/x-xbitmap",');
l.Add('  ".xpm"          =>      "image/x-xpixmap",');
l.Add('  ".xwd"          =>      "image/x-xwindowdump",');
l.Add('  ".css"          =>      "text/css",');
l.Add('  ".html"         =>      "text/html",');
l.Add('  ".htm"          =>      "text/html",');
l.Add('  ".js"           =>      "text/javascript",');
l.Add('  ".asc"          =>      "text/plain",');
l.Add('  ".c"            =>      "text/plain",');
l.Add('  ".cpp"          =>      "text/plain",');
l.Add('  ".log"          =>      "text/plain",');
l.Add('  ".conf"         =>      "text/plain",');
l.Add('  ".text"         =>      "text/plain",');
l.Add('  ".txt"          =>      "text/plain",');
l.Add('  ".dtd"          =>      "text/xml",');
l.Add('  ".xml"          =>      "text/xml",');
l.Add('  ".mpeg"         =>      "video/mpeg",');
l.Add('  ".mpg"          =>      "video/mpeg",');
l.Add('  ".mov"          =>      "video/quicktime",');
l.Add('  ".qt"           =>      "video/quicktime",');
l.Add('  ".avi"          =>      "video/x-msvideo",');
l.Add('  ".asf"          =>      "video/x-ms-asf",');
l.Add('  ".asx"          =>      "video/x-ms-asf",');
l.Add('  ".wmv"          =>      "video/x-ms-wmv",');
l.Add('  ".bz2"          =>      "application/x-bzip",');
l.Add('  ".tbz"          =>      "application/x-bzip-compressed-tar",');
l.Add('  ".tar.bz2"      =>      "application/x-bzip-compressed-tar",');
l.Add('  ""              =>      "application/octet-stream",');
l.Add(' )');
l.Add('');
l.Add('');
l.Add('accesslog.filename          = "/var/log/artica-postfix/framework.log"');
l.Add('url.access-deny             = ( "~", ".inc" )');
l.Add('');
l.Add('static-file.exclude-extensions = ( ".php", ".pl", ".fcgi" )');
l.Add('server.port                 = 47980');
l.Add('server.bind                = "127.0.0.1"');
l.Add('#server.error-handler-404   = "/error-handler.html"');
l.Add('#server.error-handler-404   = "/error-handler.php"');
l.Add('server.pid-file             = "/var/run/lighttpd/framework.pid"');
l.Add('server.max-fds 		   = 2048');
l.Add('server.network-backend      = "write"');
l.Add('');
l.Add('fastcgi.server = ( ".php" =>((');
l.Add('                "bin-path" => "/usr/bin/php-cgi",');
l.Add('                "socket" => "/var/run/lighttpd/php.framework.socket",');
l.Add('		       "min-procs" => 1,');
l.Add('                "max-procs" => '+IntToStr(max_procs)+',');
l.Add('                "idle-timeout" => 30,');
l.Add('                "bin-environment" => (');
l.Add('                        "PHP_FCGI_CHILDREN" => "'+IntToStr(PHP_FCGI_CHILDREN)+'",');
l.Add('                        "PHP_FCGI_MAX_REQUESTS" => "'+IntToStr(PHP_FCGI_MAX_REQUESTS)+'"');
l.Add('                ),');
l.Add('                "bin-copy-environment" => (');
l.Add('                        "PATH", "SHELL", "USER"');
l.Add('                ),');
l.Add('                "broken-scriptfilename" => "enable"');
l.Add('        ))');
l.Add(')');
l.Add('ssl.engine                 = "disable"');
l.Add('ssl.pemfile                = "/opt/artica/ssl/certs/lighttpd.pem"');
l.Add('status.status-url          = "/server-status"');
l.Add('status.config-url          = "/server-config"');
l.Add('$HTTP["url"] =~ "^/webmail" {');
l.Add('	server.follow-symlink = "enable"');
l.Add('}');
l.Add('alias.url += ( "/cgi-bin/" => "/usr/lib/cgi-bin/" )');
l.Add('alias.url += ( "/css/" => "/usr/share/artica-postfix/css/" )');
l.Add('alias.url += ( "/img/" => "/usr/share/artica-postfix/img/" )');
l.Add('alias.url += ( "/js/" => "/usr/share/artica-postfix/js/" )');
l.Add('');
l.Add('cgi.assign= (');
l.Add('	".pl"  => "/usr/bin/perl",');
l.Add('	".php" => "/usr/bin/php-cgi",');
l.Add('	".py"  => "/usr/bin/python",');
l.Add('	".cgi"  => "/usr/bin/perl",');
l.Add(')');
logs.WriteToFile(l.Text,'/etc/artica-postfix/framework.conf');
l.free;
end;
//##############################################################################
function tframework.Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
var
  SepLen       : Integer;
  F, P         : PChar;
  ALen, Index  : Integer;
begin
  SetLength(Result, 0);
  if (S = '') or (Limit < 0) then
    Exit;
  if Separator = '' then
  begin
    SetLength(Result, 1);
    Result[0] := S;
    Exit;
  end;
  SepLen := Length(Separator);
  ALen := Limit;
  SetLength(Result, ALen);

  Index := 0;
  P := PChar(S);
  while P^ <> #0 do
  begin
    F := P;
    P := StrPos(P, PChar(Separator));
    if (P = nil) or ((Limit > 0) and (Index = Limit - 1)) then
      P := StrEnd(F);
    if Index >= ALen then
    begin
      Inc(ALen, 5); // mehrere auf einmal um schneller arbeiten zu können
      SetLength(Result, ALen);
    end;
    SetString(Result[Index], F, P - F);
    Inc(Index);
    if P^ <> #0 then
      Inc(P, SepLen);
  end;
  if Index < ALen then
    SetLength(Result, Index); // wirkliche Länge festlegen
end;

end.

