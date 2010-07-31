var xMousePos=0;
var yMousePos=0;
document.onmousemove = pointeurDeplace


function YahooWin(size,content,title){
	
	//$('#dialog').dialog('open',content).load(content);
	$('#dialog').dialog( 'destroy' );
	$(function(){
		
		$('#dialog').dialog({
			autoOpen: true,
			width: size+'px',
			title: title}).load(content);
		});


}


function YahooWinS(width,uri,title,waitfor){
    if(!width){width='300';}
    if(!title){title='Windows';}
$('#dialogS').dialog( 'destroy' );
$(function(){
$('#dialogS').dialog({autoOpen: true,width: width+'px',title: title}).load(uri);});
}



function YahooWinT(width,uri,title,waitfor){
if(!width){width='300';}
	if(!title){title='Windows';}
	$('#dialogT').dialog( 'destroy' );
    if(!title){title='Windows';}
$(function(){$('#dialogT').dialog({autoOpen: true,width: width+'px',title: title}).load(uri);});
}


function YahooWin0(width,uri,title,waitfor){
    if(!width){width='750';}
    if(!title){title='Windows';}	
$('#dialog0').dialog( 'destroy' );
$(function(){$('#dialog0').dialog({autoOpen: true,width: width+'px',title: title}).load(uri);});
}

function YahooWin(width,uri,title,waitfor){
    if(!width){width='300';}
    if(!title){title='Windows';}
$('#dialog1').dialog( 'destroy' );
$(function(){$('#dialog1').dialog({autoOpen: true,width: width+'px',title: title}).load(uri);});
}
    
function YahooWin2(width,uri,title,waitfor){
    if(!width){width='300';}
    if(!title){title='Windows';}
$('#dialog2').dialog( 'destroy' );
    $(function(){$('#dialog2').dialog({autoOpen: true,width: width+'px',title: title}).load(uri);});
    } 

function YahooSetupControl(width,uri,title,waitfor){
if(!width){width='300';}SetupControl
if(!title){title='Windows';}
$('#SetupControl').dialog( 'destroy' );
$(function(){$('#SetupControl').dialog({autoOpen: true,width: width+'px',title: title}).load(uri);});

}

function RTMMail(width,uri,title,waitfor){
if(!width){width='300';}
if(!title){title='Windows';}
$('#RTMMail').dialog( 'destroy' );
$(function(){$('#RTMMail').dialog({autoOpen: true,width: width+'px',title: title}).load(uri);});
}

function YahooWinBrowse(width,uri,title,waitfor){
if(!width){width='300';}
if(!title){title='Windows';}
$('#Browse').dialog( 'destroy' );
$(function(){$('#Browse').dialog({autoOpen: true,width: width+'px',title: title}).load(uri);});
}

function YahooWin3(width,uri,title,waitfor){
    if(!width){width='300';}
    if(!title){title='Help';}
	$('#dialog3').dialog( 'destroy' );
	$(function(){$('#dialog3').dialog({autoOpen: true,width: width+'px',title: title}).load(uri);});
    }

function YahooWin4(width,uri,title,waitfor){
if(!width){width='300';}
    if(!title){title='Windows';}
	$('#dialog4').dialog( 'destroy' );
	$(function(){$('#dialog4').dialog({autoOpen: true,width: width+'px',title: title}).load(uri);});
    }

function YahooWin5(width,uri,title,waitfor){
if(!width){width='300';}
    if(!title){title='Windows';}
	$('#dialog5').dialog( 'destroy' );
	$(function(){$('#dialog5').dialog({autoOpen: true,width: width+'px',title: title}).load(uri);});
    }

//A supprimer document.getElementById("YahooUser_c") is null;
function YahooWin6(width,uri,title,waitfor){
if(!width){width='300';}
    if(!title){title='Windows';}
	$('#dialog6').dialog( 'destroy' );
	$(function(){$('#dialog6').dialog({autoOpen: true,width: width+'px',title: title}).load(uri);});
    }
function LoadWinORG(width,uri,title,waitfor){
if(!width){width='300';}
    if(!title){title='Windows';}
	$('#WinORG').dialog( 'destroy' );
	$(function(){$('#WinORG').dialog({autoOpen: true,width: width+'px',title: title}).load(uri);});
    }
function LoadWinORG2(width,uri,title,waitfor){
if(!width){width='300';}
    if(!title){title='Windows';}
	$('#WinORG2').dialog( 'destroy' );
	$(function(){$('#WinORG2').dialog({autoOpen: true,width: width+'px',title: title}).load(uri);});
    }
function YahooLogWatcher(width,uri,title,waitfor){
if(!width){width='300';}
    if(!title){title='Windows';}
	$('#logsWatcher').dialog( 'destroy' );
	$(function(){$('#logsWatcher').dialog({autoOpen: true,width: width+'px',title: title}).load(uri);});
    }
function YahooUser(width,uri,title,waitfor){
if(!width){width='300';}
    if(!title){title='Windows';}
	$('#YahooUser').dialog( 'destroy' );
	$(function(){$('#YahooUser').dialog({autoOpen: true,width: width+'px',title: title}).load(uri);});
    }  
function YahooSearchUser(width,uri,title,waitfor){
if(!width){width='300';}
    if(!title){title='Windows';}
	$('#SearchUser').dialog( 'destroy' );
	$(function(){$('#SearchUser').dialog({autoOpen: true,width: width+'px',title: title}).load(uri);});
    }  
function YahooWin0Hide(){$('#dialog0').dialog( 'destroy' );}
function YahooWinBrowseHide(){$('#Browse').dialog( 'destroy' );}
function RTMMailHide(){$('#RTMMail').dialog( 'destroy' );}
function YahooSetupControlHide(){$('#SetupControl').dialog( 'destroy' );}
function YahooWinHide(){$('#dialog1').dialog('destroy');}
function YahooWin2Hide(){$('#dialog2').dialog('destroy');}
function YahooWin3Hide(){$('#dialog3').dialog('destroy');}
function YahooWin4Hide(){$('#dialog4').dialog('destroy');}
function YahooWin5Hide(){$('#dialog5').dialog('destroy');}
function YahooWin6Hide(){$('#dialog6').dialog('destroy');}
function YahooLogWatcherHide(){$('#logsWatcher').dialog( 'destroy' );}
function YahooUserHide(){$('#YahooUser').dialog( 'destroy' );}
function WinORGHide(){$('#WinORG').dialog( 'destroy' );}
function YahooLogWatcherHide(){$('#logsWatcher').dialog( 'destroy' );}
function YahooSearchUserHide(){$('#SearchUser').dialog( 'destroy' );}
function YahooWinSHide(){$('#dialogS').dialog( 'destroy' );}

function RTMMailOpen(){return $('#RTMMail').dialog('isOpen');}
function YahooWinSOpen(){return $('#dialogS').dialog('isOpen');}
function YahooWinOpen(){return $('#dialog1').dialog('isOpen');}  
function YahooWin5Open(){return $('#dialog5').dialog('isOpen');}  
function YahooWin3Open(){return $('#dialog3').dialog('isOpen');}  
function YahooLogWatcherOpen(){return $('#logsWatcher').dialog('isOpen');}  
function YahooSetupControlOpen(){return $('#SetupControl').dialog('isOpen');}  
function YahooSearchUserOpen(){return $('#SearchUser').dialog('isOpen');}  
function YahooUserOpen(){return $('#YahooUser').dialog('isOpen');}  
function YahooWin6Open(){return $('#dialog6').dialog('isOpen');} 
function YahooWin4Open(){return $('#dialog4').dialog('isOpen');} 
function YahooWin5Open(){return $('#dialog5').dialog('isOpen');} 
function YahooWin3Open(){return $('#dialog3').dialog('isOpen');} 
function YahooWin2Open(){return $('#dialog2').dialog('isOpen');} 
function WinORGOpen(){return $('#WinORG').dialog('isOpen');} 




function LoadAjax(ID,uri,concatene) {
	var uri_add='';
	var datas='';
	var xurl='';
	if(concatene){
		uri_add='&datas='+concatene;
	}
	uri=uri+uri_add;
	if(document.getElementById(ID)){ 
			var WAITX=ID+'_WAITX';
			if(document.getElementById(WAITX)){return;}
	        document.getElementById(ID).innerHTML='<center style="margin:20px;padding:20px" id='+WAITX+'><img src="img/wait_verybig.gif"></center>';
	        //$('#'+ID).load(uri_add, function() {Orgfillpage();});
	        $('#'+ID).load(uri);
	}

}


	

function microtime () {
	var get_as_float=false;
    var now = new Date().getTime() / 1000;
    var s = parseInt(now, 10); 
    return (get_as_float) ? now : (Math.round((now - s) * 1000) / 1000) + ' ' + s;
}


function Loadjs(src){
	$.ajax({ type: "GET", url: src, dataType: "script",error: function(){alert(src+':error');}});
	//RemoveJsFile('');
}

function s_PopUp(url,l,h,asc){
	var PopupWindow=null;
		settings='width='+l +',height='+h +',location=no,directories=no,menubar=no,toolbar=no,status=no,scrollbars=yes,resizable=yes,dependent=yes';
		PopupWindow=window.open(url,'',settings);
		PopupWindow.focus();
	} 


function RemoveJsFile(file) {
	var alljs = document.getElementsByTagName("script");
	var i;
	var j;
	var attributesl=0;
	var scriptsource;
	var scripttype;
	alert(alljs.length);
	for (i = 0; i <alljs.length; i++) {
		scriptsource=alljs[i].getAttribute("src");
		scripttype=alljs[i].getAttribute("type");
		attributesl=alljs[i].attributes.length;
	    alert(i+')='+scriptsource+'('+scripttype+') attrs='+attributesl);
		
		
		
		for (j = 0; j < attributesl; j++ ){
			  alert ( alljs[i].attributes[j].name + ": " + alljs[i].attributes[j].value );
		}
		
		
	if (alljs[i] && alljs[i].getAttribute("src")!= null && alljs[i].getAttribute("src").indexOf(file)!=-1){
			//alljs[i].parentNode.removeChild(alljs[i]);
	}
	}
}

var x_ReloadIdentity= function (obj) {
	var results=obj.responseText;
	document.getElementById('UserIdentity').innerHTML=results;
	}	

function ReloadIdentity(){
	if(!document.getElementById('UserIdentity')){return;}
	var XHR = new XHRConnection();
	XHR.appendData('UserIdentity','yes');
	document.getElementById('UserIdentity').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('user.php', 'GET',x_ReloadIdentity);	
	
}

function lightup(imageobject, opacity){
	if (navigator.appName.indexOf("Netscape")!=-1 &&parseInt(navigator.appVersion)>=5){
	        imageobject.style.MozOpacity=opacity/100;
	        imageobject.style.backgroundColor='none';
	        }
	else if (navigator.appName.indexOf("Microsoft")!= -1 &&parseInt(navigator.appVersion)>=4){imageobject.filters.alpha.opacity=opacity}
	}
function AffBulle(texte) {
	document.onmousemove = pointeurDeplace
		var contenu=texte;
	 	document.getElementById('PopUpInfos').innerHTML="<div style='padding:5px;text-align:left;font-size:10px;' OnMouseOver=\"javascript:HideBulle();\">"+ contenu+ "</div>";
 	document.getElementById('PopUpInfos').style.width='auto';
	document.getElementById('PopUpInfos').style.height='auto';
    document.getElementById('PopUpInfos').style.top=(yMousePos -20) + 'px';
    document.getElementById('PopUpInfos').style.left=(xMousePos +10)+ 'px';
    document.getElementById('PopUpInfos').style.visibility="visible";
    document.getElementById('PopUpInfos').style.backgroundColor="#ffffff";
    document.getElementById('PopUpInfos').style.borderRight = "solid 1px #005447";
    document.getElementById('PopUpInfos').style.borderBottom = "solid 1px #005447";
	document.getElementById('PopUpInfos').style.borderTop = "solid 1px #005447";
	document.getElementById('PopUpInfos').style.borderLeft = "solid 1px #005447";
    if(document.getElementById('PopUpInfos').style.zIndex>99999){
        document.getElementById('PopUpInfos').style.zIndex=document.getElementById('PopUpInfos').style.zIndex+100;
    }else{document.getElementById('PopUpInfos').style.zIndex = "10000";}
	
	}
function HideBulle() {
	document.onmousemove = pointeurDeplace
	document.getElementById('PopUpInfos').style.visibility="hidden";
	document.getElementById('PopUpInfos').style.border ="none";
	document.getElementById('PopUpInfos').style.padding ="0";
	document.getElementById('PopUpInfos').style.backgroundColor="#FFFFFF";
	document.getElementById('PopUpInfos').style.zIndex='0';
	
}
function pointeurDeplace(e){
	xMousePos=pointeurX(e);
    yMousePos = pointeurY(e);
   }

function SwitchParLight(id){
	document.getElementById('table-'+id).className="table_form_over"; 
	document.getElementById('title-'+id).className='parTitleOver';
	document.getElementById('text-'+id).className='parTextOver';
}

function SwitcharPLight(id){
	
	document.getElementById('table-'+id).className="table_form"; 
	document.getElementById('title-'+id).className='parTitleOut';
	document.getElementById('text-'+id).className='parTextOut';
	
}
function checkEnter(e){
	var characterCode 

	characterCode = (typeof e.which != "undefined") ? e.which : event.keyCode;

	if(characterCode == 13){ 
			return true}
		else{
			return false
		}
}

function SwitchTRUEFALSE(id){
	id_value=document.getElementById(id).value;
	id_value=id_value.toUpperCase();
	if(id_value.length==0){id_value='FALSE';}
	
	
	if(!id_value){
		document.getElementById(id).value='TRUE';
		document.getElementById('img_' + id).src='img/status_ok.gif';
		return;
	}
	
	if(id_value=='TRUE'|id_value=='true'|id_value=='1'){
		document.getElementById(id).value='FALSE';
		document.getElementById('img_' + id).src='img/status_critical.gif';
		return;
	}
	
	if(id_value=='FALSE'|id_value=='false'|id_value=='0'){
		document.getElementById(id).value='TRUE';
		document.getElementById('img_' + id).src='img/status_ok.gif';
		return;
	}	
	
}
function CurrentPageName(){
	var sPath = window.location.pathname;
	var sPage = sPath.substring(sPath.lastIndexOf('/') + 1);
	return sPage;		
}


	
	
