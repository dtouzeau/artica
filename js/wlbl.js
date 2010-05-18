
var hostname_mem;
var wbl;
var mem_domain;


var x_Addwl=function(obj){
      var tempvalue=obj.responseText;
      if(tempvalue.length>0){alert(tempvalue);}
      
      if(wbl==0){LoadAjax('wblarea','whitelists.admin.php?SelectedDomain='+mem_domain+'&type=white')  ;}
      if(wbl==1){ LoadAjax('wblarea','whitelists.admin.php?SelectedDomain='+mem_domain+'&type=black')  ;}
      
}

function AddblwformCheck(ztype,e){
	if(checkEnter(e)){
		Addblwform(ztype);
	}
}


function Addblwform(num){
      var XHR = new XHRConnection();
      wbl=num;
      mem_domain=document.getElementById('selected_domain').value;
      XHR.appendData('RcptDomain',document.getElementById('selected_domain').value);
      XHR.appendData('whitelist',document.getElementById('wlfrom').value);
      XHR.appendData('recipient',document.getElementById('wlto').value);
      XHR.appendData('wbl',num);
      document.getElementById('wblarea').innerHTML='<center style="width:100%"><img src=img/wait_verybig.gif></center>';
      XHR.sendAndLoad('whitelists.admin.php', 'GET',x_Addwl);
      }
      
function DeleteWhiteList(to,from){
      var XHR = new XHRConnection();
      wbl=0;
      mem_domain=document.getElementById('selected_domain').value;
      XHR.appendData('RcptDomain',document.getElementById('selected_domain').value);
      XHR.appendData('del_whitelist',from);
      XHR.appendData('recipient',to);
      XHR.appendData('wbl','0');
      document.getElementById('wblarea').innerHTML='<center style="width:100%"><img src=img/wait_verybig.gif></center>';
      XHR.sendAndLoad('whitelists.admin.php', 'GET',x_Addwl);    
      }
      
function DeleteBlackList(to,from){
      var XHR = new XHRConnection();
      XHR.appendData('RcptDomain',document.getElementById('selected_domain').value);
      XHR.appendData('del_whitelist',from);
      wbl=1;
      XHR.appendData('recipient',to);
      XHR.appendData('wbl','1');
      document.getElementById('wblarea').innerHTML='<center style="width:100%"><img src=img/wait_verybig.gif></center>';
      XHR.sendAndLoad('whitelists.admin.php', 'GET',x_Addwl);   
      
      
      
      }      
      
      
      
      
  
      
      


