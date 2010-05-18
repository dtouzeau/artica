/**
 * @author touzeau
 */

var operations;
var znumber;


function ArticaGroupBehavior(){
	win = new Window({className: "artica",width:500, height:300, zIndex: 1000, resizable: true, draggable:true, wiredDrag: true,closable:true})
	var pars = 'GroupBehavior=yes'
	win.setAjaxContent('artica.settings.php', {method: 'get', parameters: pars});
	win.setDestroyOnClose();
 	win.showCenter();
	win.toFront();
	win_id=win.getId();		

}

function ArticaWebRootURI(){
	var XHR = new XHRConnection();
	var uri=document.getElementById('ArticaWebRootURI').value;
	XHR.appendData('ArticaWebRootURI',uri);
	XHR.appendData('ArticaMaxTempLogFilesDay',document.getElementById('ArticaMaxTempLogFilesDay').value);
	XHR.appendData('ArticaMaxLogsSize',document.getElementById('ArticaMaxLogsSize').value);
	XHR.sendAndLoad('artica.settings.php', 'GET',x_parseform);
}


function RelayBehavior(){
win = new Window({className: "artica",width:500, height:300, zIndex: 1000, resizable: true, draggable:true, wiredDrag: true,closable:true})
	var pars = 'RelayBehavior=yes'
	win.setAjaxContent('artica.settings.php', {method: 'get', parameters: pars});
	win.setDestroyOnClose();
 	win.showCenter();
	win.toFront();
	win_id=win.getId();		
	
}

function buttonApply(){
	document.getElementById('button').innerHTML="<img src='img/frw8at_ajaxldr_7.gif'>";
	setTimeout('ApplyConfigPostfix()',1200);
	
}

function ApplyConfigPostfix(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('postfix');
		XHR.sendAndLoad('actions.apply.configs.php?Step=postfix', 'GET');			
		setTimeout('ApplyConfigAveServer()',1200);
}

function ApplyConfigAveServer(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('kavmail');
		XHR.sendAndLoad('actions.apply.configs.php?Step=kavmail', 'GET');	
		setTimeout('ApplyConfigKasServer()',1200);		
	}
	
function ApplyConfigKasServer(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('kas');
		XHR.sendAndLoad('actions.apply.configs.php?Step=kas', 'GET');
		}	

var X_ChangeGlobalAdminPassword= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
	MyHref("logoff.php");
	}
	
var X_refresh= function (obj) {
	MyHref("artica.settings.php");
	}	


function ChangeGlobalAdminPassword(){
	var text=document.getElementById('global_admin_confirm').value;
	if(confirm(text)){
		var XHR = new XHRConnection();
		XHR.appendData('change_admin',document.getElementById('change_admin').value);
		XHR.appendData('change_password',document.getElementById('change_password').value);
		XHR.appendData('suffix',document.getElementById('ldap_suffix').value);
		XHR.appendData('ldap_server',document.getElementById('ldap_server').value);
		XHR.appendData('ldap_port',document.getElementById('ldap_port').value);
		if(document.getElementById('change_ldap_server_settings').checked){
			XHR.appendData('change_ldap_server_settings','yes');
		}
		
		XHR.appendData('ChangeSuperSuser','yes');
		document.getElementById('ChangePasswordDivNOtifiy').innerHTML="<img src='img/frw8at_ajaxldr_7.gif'>";
		XHR.sendAndLoad('artica.settings.php', 'POST',X_ChangeGlobalAdminPassword);
		
		
	}
}

function ArticaMailAddonsLevel_switch(){
	var mswitch=document.getElementById('ArticaMailAddonsLevel').value;
	LoadAjax('smtp_performances_explain','artica.settings.php?ArticaMailAddonsLevel_switch='+ mswitch);
	}
	
function ArticaMailAddonsLevel_save(){
	var mswitch=document.getElementById('ArticaMailAddonsLevel').value;
	var XHR = new XHRConnection();
	XHR.appendData('ArticaMailAddonsLevel_save',mswitch);
	XHR.sendAndLoad('artica.settings.php', 'GET',X_refresh);
	}
	
function ArticaUpdateInstallPackage(pack){
	var tx=document.getElementById('install_package_text').value;
	if(confirm(tx)){
		var XHR = new XHRConnection();
		XHR.appendData('ArticaUpdateInstallPackage',pack);
		XHR.sendAndLoad('artica.update.php', 'GET');
	}
}
function auto_update_perform(){
var tx=document.getElementById('perform_update_text').value;
	if(confirm(tx)){
		var XHR = new XHRConnection();
		XHR.appendData('auto_update_perform','yes');
		XHR.sendAndLoad('artica.update.php', 'GET',x_parseform);
	}	
}
function HTTPS_PROCESSES(){
	alert(document.getElementById('interface_restarted').value);
	ParseForm('FFM119','artica.settings.php',true)
}
function HTTPS_PORT(){
	alert(document.getElementById('interface_restarted').value);
	ParseForm('FFM109','artica.settings.php',true)
}

 
 
 
