unit setup_zarafa;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,RegExpr in 'RegExpr.pas',
  unix,setup_libs,distridetect,postfix_class,zsystem,
  install_generic;

  type
  tzarafa=class


private
   libs:tlibs;
   distri:tdistriDetect;
   install:tinstall;
   source_folder,cmd:string;
   webserver_port:string;
   artica_admin:string;
   artica_password:string;
   ldap_suffix:string;
   mysql_server:string;
   mysql_admin:string;
   mysql_password:string;
   ldap_server:string;
   postfix:tpostfix;
   SYS:Tsystem;
   CODE_NAME:string;
   function VERSION_INTEGER():integer;
   function REMOTE_VERSION_INTEGER():integer;

public
      constructor Create();
      procedure Free;
      procedure xinstall();
      function libvmime():boolean;
      function google_perftools():boolean;
      function clucene():boolean;
      function libical():boolean;
      procedure REMOVE();

END;

implementation

constructor tzarafa.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.Create();
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
CODE_NAME:='APP_ZARAFA';
end;
//#########################################################################################
procedure tzarafa.Free();
begin
  libs.Free;
  //libgsasl7-dev
  ///usr/lib/libvmime.so
  // /usr/local/lib/libicalvcal.a
end;

//#########################################################################################



//#########################################################################################
function tzarafa.libical():boolean;
begin
result:=false;
if FileExists('/usr/local/lib/libicalvcal.a') then begin
   writeln('/usr/local/lib/libicalvcal.a OK');
   exit(true);
end;
if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('libical');
  if not DirectoryExists(source_folder) then begin
     writeln('Install libical failed...');
     exit;
  end;
SetCurrentDir(source_folder);
   install.INSTALL_PROGRESS(CODE_NAME,'libicalvcal');
   install.INSTALL_STATUS(CODE_NAME,70);
   fpsystem('./configure');
   install.INSTALL_PROGRESS(CODE_NAME,'libicalvcal');
   install.INSTALL_STATUS(CODE_NAME,70);
   fpsystem('make');
   install.INSTALL_PROGRESS(CODE_NAME,'libicalvcal');
   install.INSTALL_STATUS(CODE_NAME,70);
   fpsystem('make install');
   install.INSTALL_STATUS(CODE_NAME,70);
if FileExists('/usr/local/lib/libicalvcal.a') then begin
   writeln('/usr/local/lib/libicalvcal.a success');
   SetCurrentDir('/root');
   exit(true);
end;

writeln('Unable to stat /usr/local/lib/libicalvcal.a');
install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
install.INSTALL_STATUS(CODE_NAME,110);

end;
//#########################################################################################

function tzarafa.libvmime():boolean;
begin
result:=false;



if FileExists('/usr/local/lib/libvmime.so.0.7.1') then begin
     writeln('/usr/local/lib/libicalvcal.a already installed');
    exit(true);
end;

   install.INSTALL_STATUS(CODE_NAME,50);
   writeln('Downloading libvmime-0.7.1.tar.bz2');
libs.WGET_DOWNLOAD_FILE('http://www.artica.fr/download/libvmime-0.7.1.tar.bz2','/tmp/libvmime-0.7.1.tar.bz2');
if not FileExists('/tmp/libvmime-0.7.1.tar.bz2') then begin
   install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
   install.INSTALL_STATUS(CODE_NAME,110);
   exit;
end;

writeln('Extracting libvmime-0.7.1.tar.bz2');
fpsystem('tar xjf /tmp/libvmime-0.7.1.tar.bz2 -C /root/');
fpsystem('/bin/rm /tmp/libvmime-0.7.1.tar.bz2');
install.INSTALL_STATUS(CODE_NAME,50);
if not DirectorYExists('/root/libvmime-0.7.1') then begin
   install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
   install.INSTALL_STATUS(CODE_NAME,110);
   exit;
end;


writeln('Downloading zarafa-vmime-patches.tar.gz');
libs.WGET_DOWNLOAD_FILE('http://www.artica.fr/download/zarafa-vmime-patches.tar.gz','/tmp/zarafa-vmime-patches.tar.gz');
if not FileExists('/tmp/zarafa-vmime-patches.tar.gz') then begin
   install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
   install.INSTALL_STATUS(CODE_NAME,110);
   exit;
end;

ForceDirectories('/root/zarafa-vmime-patches');
writeln('Extracting zarafa-vmime-patches.tar.gz');
fpsystem('tar xf /tmp/zarafa-vmime-patches.tar.gz -C /root/zarafa-vmime-patches/');
if not FileExists('/root/zarafa-vmime-patches/README') then begin
   install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
   install.INSTALL_STATUS(CODE_NAME,110);
   exit;
end;


   SetCurrentDir('/root/libvmime-0.7.1');
   writeln('Patching');
   install.INSTALL_STATUS(CODE_NAME,50);
   install.INSTALL_PROGRESS(CODE_NAME,'libvmime');
   fpsystem('for i in ../zarafa-vmime-patches/*.diff; do patch -p1 < $i; done');
   fpsystem('./configure');
  install.INSTALL_STATUS(CODE_NAME,50);
   fpsystem('make');
   install.INSTALL_STATUS(CODE_NAME,50);
   fpsystem('make install');
   install.INSTALL_STATUS(CODE_NAME,50);
   SetCurrentDir('/root');


if FileExists('/usr/local/lib/libvmime.so.0.7.1') then begin
   writeln('/usr/local/lib/libvmime.so.0.7.1 success');
   exit(true);
end;


writeln('Unable to stat /usr/local/lib/libvmime.so.0.7.1');
install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
install.INSTALL_STATUS(CODE_NAME,110);
end;
//#########################################################################################


function tzarafa.clucene():boolean;
begin
result:=false;
if FileExists('/usr/lib/CLucene/clucene-config.h') then exit(true);
if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('clucene-core');
  if not DirectoryExists(source_folder) then begin
     writeln('Install clucene failed...');
     exit;
  end;
SetCurrentDir(source_folder);
   install.INSTALL_PROGRESS(CODE_NAME,'clucene');
   install.INSTALL_STATUS(CODE_NAME,10);

   fpsystem('./configure --prefix=/usr --includedir="\${prefix}/include" --mandir="\${prefix}/share/man" --infodir="\${prefix}/share/info" --sysconfdir=/etc --localstatedir=/var --libexecdir="\${prefix}/lib/clucene-core"');
   install.INSTALL_PROGRESS(CODE_NAME,'clucene');
   install.INSTALL_STATUS(CODE_NAME,10);
   fpsystem('make');
   install.INSTALL_PROGRESS(CODE_NAME,'clucene');
   install.INSTALL_STATUS(CODE_NAME,10);
   fpsystem('make install');
if FileExists('/usr/lib/CLucene/clucene-config.h') then begin
   SetCurrentDir('/root');
   exit(true);
end;
install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
install.INSTALL_STATUS(CODE_NAME,110);

end;
//#########################################################################################
function tzarafa.google_perftools():boolean;
begin
result:=false;
if FileExists('/usr/bin/pprof') then exit(true);
if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('google-perftools');
  if not DirectoryExists(source_folder) then begin
     writeln('Install google perftools failed...');
     exit;
  end;
  forceDirectories('/root/goolge-perfs');
  fpsystem('/bin/cp -rf '+source_folder+'/* /root/goolge-perfs/');


   SetCurrentDir('/root/goolge-perfs');
   install.INSTALL_PROGRESS(CODE_NAME,'perftools');
   install.INSTALL_STATUS(CODE_NAME,30);
   fpsystem('./configure --prefix=/usr --includedir="\${prefix}/include" --mandir="\${prefix}/share/man" --infodir="\${prefix}/share/info" --sysconfdir=/etc --localstatedir=/var --libexecdir="\${prefix}/lib/google-perftools"');
   install.INSTALL_PROGRESS(CODE_NAME,'perftools');
    install.INSTALL_STATUS(CODE_NAME,30);

   fpsystem('make');
   install.INSTALL_PROGRESS(CODE_NAME,'perftools');
   install.INSTALL_STATUS(CODE_NAME,30);
   fpsystem('make install');

if FileExists('/usr/bin/pprof') then begin
   SetCurrentDir('/root');
   exit(true);
end;
 writeln('Unable to stat /usr/bin/pprof tool');
install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
install.INSTALL_STATUS(CODE_NAME,110);
end;
//#########################################################################################


procedure tzarafa.xinstall();
var
   CODE_NAME:string;
   cmd:string;
   PERL_MODULES:string;
   configure_scripts:string;
   LOCAL_INTEGER:integer;
   REMOTE_INTEGER:integer;
   t:tstringlist;
   l:Tstringlist;
   i:integer;

begin
LOCAL_INTEGER:=0;
REMOTE_INTEGER:=0;


 if libs.COMMANDLINE_PARAMETERS('--uninstall') then begin
     writeln('Uninstall process is enabled');
     REMOVE();
 end;


 REMOTE_INTEGER:=REMOTE_VERSION_INTEGER();
 LOCAL_INTEGER:=VERSION_INTEGER();

 writeln('LOCAL VERSION..........: ',LOCAL_INTEGER);
 writeln('REMOTE VERSION.........: ',REMOTE_INTEGER);

 if  LOCAL_INTEGER>=REMOTE_INTEGER then begin
      install.INSTALL_STATUS(CODE_NAME,100);
      install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
      writeln('RESULT.................: No update needed');
      install.INSTALL_STATUS('APP_ZARAFA',100);
      install.INSTALL_PROGRESS('APP_ZARAFA','{installed}');
      exit();
end;
writeln('RESULT.................: Installing/Upgrading');

 if not libs.COMMANDLINE_PARAMETERS('--running') then begin
        t:=tstringlist.Create;
        t.add('#!/bin/sh');
        t.add('PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/X11R6/bin');
        t.add('echo "Running (1)"');
        t.add(paramStr(0)+' APP_ZARAFA --running');
        t.add('sleep 300');
        t.add('echo "Running (2)"');
        t.add(paramStr(0)+' APP_ZARAFA --running');
        t.add('sleep 300');
        t.add('echo "Running (3)"');
        t.add(paramStr(0)+' APP_ZARAFA --running');
        t.add('sleep 300');
        t.add('echo "Running (4)"');
        t.add(paramStr(0)+' APP_ZARAFA --running');
        t.SaveToFile('/root/run-zarafa.sh');
        t.free;
        fpsystem('/bin/chmod 777 /root/run-zarafa.sh');
        fpsystem('/root/run-zarafa.sh &');
        halt(0);
 end else begin
      writeln('-------------- RUNNING MODE ------------');
 end;




      SetCurrentDir('/root');
      CODE_NAME:='APP_ZARAFA';
 writeln('-------------- Checking  clucene library ------------');
 install.INSTALL_STATUS(CODE_NAME,10);
 install.INSTALL_PROGRESS(CODE_NAME,'{checking}');
   if not clucene() then begin
      install.INSTALL_STATUS(CODE_NAME,110);
      install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
      exit;
   end;

 CODE_NAME:='APP_ZARAFA';
 writeln('-------------- Checking  Google perftools library ------------');
 install.INSTALL_STATUS(CODE_NAME,30);
 install.INSTALL_PROGRESS(CODE_NAME,'perftools');
   if not google_perftools() then begin
      install.INSTALL_STATUS(CODE_NAME,110);
      install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
      writeln('-------------- installing Google perftools failed ------------');
      exit;
   end;
 CODE_NAME:='APP_ZARAFA';
 writeln('-------------- Checking libvmime library ------------');
 install.INSTALL_STATUS(CODE_NAME,50);
 install.INSTALL_PROGRESS(CODE_NAME,'libvmime');
   if not libvmime() then begin
      install.INSTALL_STATUS(CODE_NAME,110);
      install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
      writeln('-------------- installing libvmime library failed ------------');
      exit;
   end;
 CODE_NAME:='APP_ZARAFA';
 writeln('-------------- Checking libical library ------------');
 install.INSTALL_STATUS(CODE_NAME,70);
 install.INSTALL_PROGRESS(CODE_NAME,'libical');
    if not libical() then begin
      install.INSTALL_STATUS(CODE_NAME,110);
      install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
       writeln('-------------- installing libical library failed ------------');
      exit;
   end;

 writeln('-------------- Installing Zarafa server ------------');
  fpsystem('ldconfig');
  CODE_NAME:='APP_ZARAFA';
  install.INSTALL_STATUS(CODE_NAME,80);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  configure_scripts:='VMIME_PREFIX="/usr/include/vmime" ./configure --with-vmime-prefix=/usr/local/include --with-ical-prefix=/usr/local/include';
  configure_scripts:=configure_scripts+'  --enable-tcmalloc --disable-static --disable-testtools --with-userscript-prefix=/etc/zarafa/userscripts --with-quotatemplate-prefix=/etc/zarafa/quotamails --prefix=/usr/local --sysconfdir=/etc';
  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('zarafa');


  if not DirectoryExists(source_folder) then begin
     writeln('Install zarafa failed...');
      install.INSTALL_STATUS(CODE_NAME,110);
      install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
      exit;
  end;
    SetCurrentDir(source_folder);
    install.INSTALL_STATUS(CODE_NAME,80);
    install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
    writeln('using: ',configure_scripts);
    fpsystem(configure_scripts);
    fpsystem('make');
    install.INSTALL_STATUS(CODE_NAME,90);
    install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
    fpsystem('make install');

    if not FIleExists('/usr/local/bin/zarafa-server') then begin
          install.INSTALL_STATUS(CODE_NAME,110);
          install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
          exit;
    end;

    forceDirectories('/usr/share/zarafa-webaccess');
    forceDirectories('/usr/share/zarafa-webaccess-mobile');
    if not DirectoryExists(source_folder+'/php-webclient-ajax') then begin
       writeln('Unable to stat '+source_folder+'/php-webclient-ajax');
       install.INSTALL_STATUS(CODE_NAME,110);
       install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
       exit;
    end;

    fpsystem('/bin/cp -rfv '+source_folder+'/php-webclient-ajax/* /usr/share/zarafa-webaccess/');
    fpsystem('/bin/cp -rfv '+source_folder+'/php-mobile-webaccess/* /usr/share/zarafa-webaccess-mobile/');

    install.INSTALL_STATUS(CODE_NAME,100);
    install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
    forcedirectories('/var/lib/zarafa-webaccess/tmp');

l:=tstringlist.Create;
l.add('/usr/bin/msgfmt /usr/share/zarafa-webaccess/server/language/da_DK.UTF-8/LC_MESSAGES/zarafa.po -o /usr/share/zarafa-webaccess/server/language/da_DK.UTF-8/LC_MESSAGES/zarafa.mo');
l.add('/usr/bin/msgfmt /usr/share/zarafa-webaccess/server/language/de_DE.UTF-8/LC_MESSAGES/zarafa.po -o /usr/share/zarafa-webaccess/server/language/de_DE.UTF-8/LC_MESSAGES/zarafa.mo');
l.add('/usr/bin/msgfmt /usr/share/zarafa-webaccess/server/language/en_US.UTF-8/LC_MESSAGES/zarafa.po -o /usr/share/zarafa-webaccess/server/language/en_US.UTF-8/LC_MESSAGES/zarafa.mo');
l.add('/usr/bin/msgfmt /usr/share/zarafa-webaccess/server/language/es_CA.UTF-8/LC_MESSAGES/zarafa.po -o /usr/share/zarafa-webaccess/server/language/es_CA.UTF-8/LC_MESSAGES/zarafa.mo');
l.add('/usr/bin/msgfmt /usr/share/zarafa-webaccess/server/language/es_ES.UTF-8/LC_MESSAGES/zarafa.po -o /usr/share/zarafa-webaccess/server/language/es_ES.UTF-8/LC_MESSAGES/zarafa.mo');
l.add('/usr/bin/msgfmt /usr/share/zarafa-webaccess/server/language/fi_FI.UTF-8/LC_MESSAGES/zarafa.po -o /usr/share/zarafa-webaccess/server/language/fi_FI.UTF-8/LC_MESSAGES/zarafa.mo');
l.add('/usr/bin/msgfmt /usr/share/zarafa-webaccess/server/language/fr_BE.UTF-8/LC_MESSAGES/zarafa.po -o /usr/share/zarafa-webaccess/server/language/fr_BE.UTF-8/LC_MESSAGES/zarafa.mo');
l.add('/usr/bin/msgfmt /usr/share/zarafa-webaccess/server/language/fr_FR.UTF-8/LC_MESSAGES/zarafa.po -o /usr/share/zarafa-webaccess/server/language/fr_FR.UTF-8/LC_MESSAGES/zarafa.mo');
l.add('/usr/bin/msgfmt /usr/share/zarafa-webaccess/server/language/it_IT.UTF-8/LC_MESSAGES/zarafa.po -o /usr/share/zarafa-webaccess/server/language/it_IT.UTF-8/LC_MESSAGES/zarafa.mo');
l.add('/usr/bin/msgfmt /usr/share/zarafa-webaccess/server/language/nl_NL.UTF-8/LC_MESSAGES/zarafa.po -o /usr/share/zarafa-webaccess/server/language/nl_NL.UTF-8/LC_MESSAGES/zarafa.mo');
l.add('/usr/bin/msgfmt /usr/share/zarafa-webaccess/server/language/no_NO.UTF-8/LC_MESSAGES/zarafa.po -o /usr/share/zarafa-webaccess/server/language/no_NO.UTF-8/LC_MESSAGES/zarafa.mo');
l.add('/usr/bin/msgfmt /usr/share/zarafa-webaccess/server/language/pt_BR.UTF-8/LC_MESSAGES/zarafa.po -o /usr/share/zarafa-webaccess/server/language/pt_BR.UTF-8/LC_MESSAGES/zarafa.mo');
l.add('/usr/bin/msgfmt /usr/share/zarafa-webaccess/server/language/pt_PT.UTF-8/LC_MESSAGES/zarafa.po -o /usr/share/zarafa-webaccess/server/language/pt_PT.UTF-8/LC_MESSAGES/zarafa.mo');
l.add('/usr/bin/msgfmt /usr/share/zarafa-webaccess/server/language/sv_SE.UTF-8/LC_MESSAGES/zarafa.po -o /usr/share/zarafa-webaccess/server/language/sv_SE.UTF-8/LC_MESSAGES/zarafa.mo');
for i:=0 to l.Count-1 do begin
    fpsystem(l.Strings[i]);
end;

    fpsystem('/usr/share/artica-postfix/bin/artica-install --postfix-reload');
    fpsystem('/etc/init.d/artica-postfix restart apache');
    fpsystem('/etc/init.d/artica-postfix restart zarafa');

    // installer/userscripts ??


end;
//#########################################################################################
procedure tzarafa.REMOVE();
var
   l:Tstringlist;
   i:integer;
begin
l:=Tstringlist.Create;
l.add('/usr/local/lib/libical.a');
l.add('/usr/local/lib/libical.la');
l.add('/usr/local/lib/libicalmapi.la');
l.add('/usr/local/lib/libicalmapi.so');
l.add('/usr/local/lib/libicalmapi.so.1');
l.add('/usr/local/lib/libicalmapi.so.1.0.0');
l.add('/usr/local/lib/libical.so');
l.add('/usr/local/lib/libical.so.0');
l.add('/usr/local/lib/libical.so.0.44.0');
l.add('/usr/local/lib/libicalss.a');
l.add('/usr/local/lib/libicalss.la');
l.add('/usr/local/lib/libicalss.so');
l.add('/usr/local/lib/libicalss.so.0');
l.add('/usr/local/lib/libicalss.so.0.44.0');
l.add('/usr/local/lib/libicalvcal.a');
l.add('/usr/local/lib/libicalvcal.la');
l.add('/usr/local/lib/libicalvcal.so');
l.add('/usr/local/lib/libicalvcal.so.0');
l.add('/usr/local/lib/libicalvcal.so.0.44.0');
l.add('/usr/local/lib/libinetmapi.la');
l.add('/usr/local/lib/libinetmapi.so');
l.add('/usr/local/lib/libinetmapi.so.1');
l.add('/usr/local/lib/libinetmapi.so.1.0.0');
l.add('/usr/local/lib/libmapi.la');
l.add('/usr/local/lib/libmapi.so');
l.add('/usr/local/lib/libmapi.so.0');
l.add('/usr/local/lib/libmapi.so.0.0.0');
l.add('/usr/local/lib/libvmime.a');
l.add('/usr/local/lib/libvmime.la');
l.add('/usr/local/lib/libvmime.so');
l.add('/usr/local/lib/libvmime.so.0');
l.add('/usr/local/lib/libvmime.so.0.7.1');
l.add('/usr/local/lib/libzarafaclient.la');
l.add('/usr/local/lib/libzarafaclient.so');
l.add('/usr/local/bin/zarafa-admin');
l.add('/usr/local/bin/zarafa-autorespond');
l.add('/usr/local/bin/zarafa-dagent');
l.add('/usr/local/bin/zarafa-fsck');
l.add('/usr/local/bin/zarafa-gateway');
l.add('/usr/local/bin/zarafa-ical');
l.add('/usr/local/bin/zarafa-monitor');
l.add('/usr/local/bin/zarafa-passwd');
l.add('/usr/local/bin/zarafa-server');
l.add('/usr/local/bin/zarafa-spooler');
l.add('/usr/local/bin/zarafa-stats');


if DirectoryExists('/usr/local/lib/zarafa') then begin
   writeln('Remove directory /usr/local/lib/zarafa');
   fpsystem('/bin/rm -rf /usr/local/lib/zarafa');
end;


for i:=0 TO l.Count-1 do begin
    if FileExists(l.Strings[i]) then begin
       writeln('Remove file '+l.Strings[i]);
       fpsystem('/bin/rm '+ l.Strings[i]);
    end;
end;

writeln('done.');
end;

//##############################################################################
function tzarafa.VERSION_INTEGER():integer;
var
    path:string;
    RegExpr:TRegExpr;
    FileData:TStringList;
    i:integer;
    D:Boolean;
    tmpstr:string;
    a,b,c,final:string;

begin
     result:=0;
     path:='/usr/local/bin/zarafa-server';
     if not FileExists(path) then begin
        exit(0);
     end;
     tmpstr:='/tmp/artica-zarafa-version';
     FileData:=TStringList.Create;
     RegExpr:=TRegExpr.Create;
     fpsystem(path+' -V >'+ tmpstr + ' 2>&1');
     FileData.LoadFromFile(tmpstr);
 RegExpr.Expression:='version:\s+([0-9]+),([0-9]+),([0-9]+)';
  for i:=0 to FileData.Count -1 do begin
          if RegExpr.Exec(FileData.Strings[i]) then  begin
               a:=RegExpr.Match[1];
               b:=RegExpr.Match[2];
               c:=RegExpr.Match[3];
              writeln('LOCAL VERSION  (string): ',a,'.',b,'.',c);
               if length(a)=1 then a:='0'+a;
               if length(b)=1 then b:='0'+b;
               if length(c)=1 then c:='0'+c;
               final:=a+''+b+''+c;
               TryStrToInt(final,result);
          end;
  end;

end;
//#############################################################################
function tzarafa.REMOTE_VERSION_INTEGER():integer;
var
    path:string;
    RegExpr:TRegExpr;
    FileData:TStringList;
    i:integer;
    D:Boolean;
    remoteversion:string;
    a,b,c,final:string;

begin
   RegExpr:=TRegExpr.Create;
    remoteversion:=libs.COMPILE_VERSION_STRING('zarafa');
   RegExpr.Expression:='([0-9]+).([0-9]+).([0-9]+)';
          if RegExpr.Exec(remoteversion) then  begin
               a:=RegExpr.Match[1];
               b:=RegExpr.Match[2];
               c:=RegExpr.Match[3];
              writeln('REMOTE VERSION (string): ',a,'.',b,'.',c);
               if length(a)=1 then a:='0'+a;
               if length(b)=1 then b:='0'+b;
               if length(c)=1 then c:='0'+c;
               final:=a+''+b+''+c;
               TryStrToInt(final,result);
         end;

end;
//#############################################################################





end.
