<?php
session_start();
if($_SESSION["uid"]==-100){header("Location: logoff.php");die();}
include_once(dirname(__FILE__).'/class.lang.inc');
include_once(dirname(__FILE__).'/class.users.menus.inc');


class templates{
	protected $header=null;
	protected $footer=null;
	protected $pattern_array=array();
	protected $content=null;
	protected $language;
	protected $title;
	function templates($html=null,$title=null){
		$this->language=$this->_GetLangs();
		if($html<>null){
			$this->buildHeaders();
			$this->BuildFooter();
			$this->content=$html;
			$this->title=$title;
		}
		
		
	}
	
	public function buildPage(){
		$this->content=$this->_ENGINE_parse_body($this->content);
		return "$this->header\n\n$this->content\n\n$this->footer";
		
	}
	
	private function BuildFooter(){
		
	$html= "

	<div class=\"footer\">
			Copyright ". date('Y')." | <a href=\"http://www.artica.fr\" class=\"footer-link\">Artica Web console management</a> | 
	</div>
	</div>
	</div>	
		</body>
		</html>";
	$this->footer=$html;		
		
	}
	
	public function PopupPage($content){
		$html="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns=\"http://www.w3.org/1999/xhtml\">
		<head>
			<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
			<title>Artica: {$_SESSION["uid"]}</title>
			<link href=\"css/stylesheet.css\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />
			<link href=\"css/styles_tabs.css\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />	
			<link type=\"text/css\" href=\"css/custom-theme/jquery-ui-1.7.2.custom.css\" rel=\"stylesheet\" />
			
			<link type=\"text/css\" href=\"css/jqueryFileTree.css\" rel=\"stylesheet\" />
			<link type=\"text/css\" href=\"css/rounded.css\" rel=\"stylesheet\" />
			<script src=\"js/jquery.filetree.js\" type=\"text/javascript\"></script>
			
			<script src=\"js/jquery-1.3.2.min.js\" type=\"text/javascript\"></script>
			<script src=\"js/jquery-ui-1.7.2.custom.min.js\" type=\"text/javascript\"></script>
			
			<script src=\"js/default.js\" type=\"text/javascript\"></script>
			<script src=\"js/XHRConnection.js\" type=\"text/javascript\"></script>
			<script src=\"js/mouse_ie.js\" type=\"text/javascript\"></script>
			<script src=\"js/mouse.js\" type=\"text/javascript\"></script>
			<div id='PopUpInfos' style='position:absolute'></div>
			". $this->YahooBody()."
		</head>
		<body style='padding:20px;margin:5px;border:5px solid #005447;height:100%;background-color:#FAFAFA;background-image:none'>
		$content</body>
		</html>";
		
		return $html;
	}
	
	function YahooBody(){
			$conf=$conf ."<div id=\"SetupControl\"></div>\n";			
			$conf=$conf. "<div id=\"dialogS\"></div> \n";
			$conf=$conf. "<div id=\"dialogT\"></div> \n";
			$conf=$conf ."<div id=\"dialog0\"></div> \n";
			$conf=$conf ."<div id=\"dialog1\"></div>\n";
			$conf=$conf ."<div id=\"dialog2\"></div> \n";
			$conf=$conf ."<div id=\"dialog3\"></div>\n";
			$conf=$conf ."<div id=\"dialog4\"></div>\n";
			$conf=$conf ."<div id=\"dialog5\"></div>\n";
			$conf=$conf ."<div id=\"dialog6\"></div>\n"; 
			$conf=$conf ."<div id=\"YahooUser\"></div>\n"; 
			$conf=$conf ."<div id=\"logsWatcher\"></div>\n";
			$conf=$conf ."<div id=\"WinORG\"></div>\n";	
			$conf=$conf ."<div id=\"WinORG2\"></div>\n";						
			$conf=$conf ."<div id=\"RTMMail\"></div>\n";			
			$conf=$conf ."<div id=\"Browse\"></div>\n";
			$conf=$conf ."<div id=\"SearchUser\"></div>\n";									
			return $conf;		
	}	
	

	
	public function Heads(){
				$title=$this->_ENGINE_parse_body($this->title);
				
				$tabs="	
				<script>
					$(document).ready(function(){
					$('#container-tabs').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			

			});</script>
			";
				
				if(!isset($_SESSION["uid"])){$tabs=null;}
				
		$html="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns=\"http://www.w3.org/1999/xhtml\">
		<head>
			<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
			<title>Artica: {$_SESSION["uid"]}</title>
			<link href=\"css/stylesheet.css\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />
			<link type=\"text/css\" href=\"css/custom-theme/jquery-ui-1.7.2.custom.css\" rel=\"stylesheet\" />
			<script src=\"js/jquery-1.3.2.min.js\" type=\"text/javascript\"></script>
			<script src=\"js/jquery-ui-1.7.2.custom.min.js\" type=\"text/javascript\"></script>
			<script src=\"js/ui.datepicker.js\" type=\"text/javascript\"></script>
			<script src=\"js/default.js\" type=\"text/javascript\"></script>
			<script src=\"js/XHRConnection.js\" type=\"text/javascript\"></script>
			<script src=\"js/mouse_ie.js\" type=\"text/javascript\"></script>
			<script src=\"js/mouse.js\" type=\"text/javascript\"></script>
			
			<link type=\"text/css\" href=\"css/jqueryFileTree.css\" rel=\"stylesheet\" />
			<link href=\"css/styles_tabs.css\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />	
			<script src=\"js/jqueryFileTree.js\" type=\"text/javascript\"></script>
			
			$tabs

			<div id='PopUpInfos' style='position:absolute'></div>
			". $this->YahooBody()."
		</head>";
		return $html;
		
	}
	
	private function buildHeaders(){
		$sock=new sockets();
		$users=new usersMenus();
		
		$toolbox="<li><a href=\"user.php?ajax=yes\"><span>{index}</span></a></li>";
		
		if($users->POSTFIX_INSTALLED){
			$toolbox=$toolbox."
                	<li><a href=\"rtmm.php?ajax=yes\"><span>{events}</span></a></li>
                	<li><a href=\"backup.php?ajax=yes\"><span>{backup}</span></a></li>
                	<li><a href=\"quarantine.php?ajax=yes\"><span>{quarantinem}</span></a></li>
					<li><a href=\"user.messaging.php\"><span>{messaging}</span></a></li>
            		";

		}
		
		if($users->OPENVPN_INSTALLED){
			if($users->AllowOpenVPN){
				$toolbox=$toolbox."<li><a href=\"vpn.php\"><span>VPN</span></a></li>";
			}
		}
		
		if($users->SAMBA_INSTALLED){
			$toolbox=$toolbox."<li><a href=\"storage.php\"><span>{storage}</span></a></li>";
		}else{
			if($users->MLDONKEY_INSTALLED){
				$toolbox=$toolbox."<li><a href=\"storage.php\"><span>{storage}</span></a></li>";
			}
		}
		
		if($this->GetComputersNumber($_SESSION["uid"])>0){
			$toolbox=$toolbox."<li><a href=\"my-computers.php\"><span>{computers}</span></a></li>";
		}
		
		if($users->DANSGUARDIAN_INSTALLED){
			if($users->AsDansGuardianGroupRule){
				$toolbox=$toolbox."<li><a href=\"dansguardian.php\"><span>{web_filter}</span></a></li>";
			}
		}
		
		
		$logoff="<div style='position:absolute;margin:-5px'>". imgtootltip("logoff-64.png","{logoff}","document.location.href='logoff.php'")."</div>";
		
		$toolbox="

		<div id='container-tabs' style='width:98%;margin:4px;height:650px;overflow:auto'>
			<ul>
				$toolbox
			</ul>
		</div>";
		
	if(!isset($_SESSION["uid"])){$toolbox=null;$logoff=null;}else{
		$toolbox=$this->_ENGINE_parse_body($toolbox);
		$logoff=$this->_ENGINE_parse_body($logoff);
	
		}
		
		$html=$this->Heads()."
		<body>
		
		<div class=\"main\">
		$logoff
			<div class=\"header\">
		 		<div class=logo><img src='img/logo.gif'></div>
		 	</div>
			<div class=\"body-main\">
			$toolbox
		  ";
		$this->header=$html;
		
	}
	
	function javascript_parse_text($content){
		
		$content=$this->_ENGINE_parse_body($content);
		$content=html_entity_decode($content);
		$content=str_replace("\n","\\n",$content);
		$content=utf8_encode($content);
		return $content;
	}

	
	
function GetComputersNumber($user_id){
		$userid=new user($user_id);
		$dn=$userid->dn;
		$ldap=new clladp();
		$pattern="(&(objectClass=ComputerAfectation)(cn=*))";
		$attr=array();
		$sr=@ldap_search($ldap->ldap_connection,$dn,$pattern,$attr);
		if(!$sr){return null;}
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		return $hash["count"];
		}

	private function get_cached_text($text){
	
		if(count($_SESSION["translation"])<100){
			$sock=new sockets();
			$_SESSION["translation"]=$sock->LANGUAGE_DUMP($this->language);
			writelogs("Load ". count($_SESSION["translation"]).
			" rows into $this->language session language",
			__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		$value=$_SESSION["translation"][$text];
		if($value==null){
			$sock=new sockets();
			if(count($_SESSION["translation-en"])<100){$_SESSION["translation-en"]=$sock->LANGUAGE_DUMP("en");}
			$value=$_SESSION["translation-en"][$text];
		}
		
		return $value;
	}		
	
	
function _ENGINE_parse_body($content=null,$currpage=null){	
	$regs=array();
	$r=array();
	$num=0;
	
		if(preg_match_all('#{(.+?)}#is',$content,$regs)){
			while (list ($num, $val) = each ($regs[1]) ){
						$num=$num;
						$text=$this->get_cached_text($val);
						if($text<>null){
							$text=str_replace('"',"&quot;",$text);
							$text=str_replace("'","`",$text);
							$text=str_ireplace('[code]','<code>',$text);
							$text=str_ireplace('[br]','<br>',$text);
							$text=str_ireplace('[b]','<b>',$text);
							$text=str_ireplace('[/b]','</b>',$text);
							$text=str_ireplace('[ul]','<ul>',$text);
							$text=str_ireplace('[/ul]','</ul>',$text);
							$text=str_ireplace('[li]','<li>',$text);
							$text=str_ireplace('[/li]','</li>',$text);
							$text=str_ireplace('[/center]','</center>',$text);
							$text=str_ireplace('[center]','<center>',$text);
							$text=str_ireplace('[/strong]','</strong>',$text);
							$text=str_ireplace('[strong]','<strong>',$text);	
							$text=str_ireplace('[/h4]','</h4>',$text);
							$text=str_ireplace('[h4]','<h4>',$text);
							$text=str_ireplace('[h5]','<h5>',$text);
							$text=str_ireplace('[h5r]','<h5 style="color:red">',$text);
							$text=str_ireplace('[/h5r]','</h5>',$text);														
							$text=str_ireplace('[/h5]','</h5>',$text);
							$text=str_ireplace('[i]','<i>',$text);	
							$text=str_ireplace('[/i]','</i>',$text);	
							$text=str_ireplace('[/a]','</a>',$text);
							$text=str_ireplace('[/u]','</u>',$text);
							$text=str_ireplace('[b12]','<strong style="font-size:12px">',$text);							
							$text=str_ireplace('[/b12]','</strong>',$text);
							
							$text=str_ireplace('[u]','<u>',$text);
							
							$text=str_ireplace('[blk]','<blockquote>',$text);	
							$text=str_ireplace('[/blk]','</blockquote>',$text);	
							if(preg_match_all('#\[a\s+(.+?)\]#',$text,$r)){
								while (list ($a, $b) = each ($r[1]) ){
								$text=str_replace($r[0][$a],"<a href=\"$b\">",$text);}
							}
													
							$content=str_replace("{{$val}}",$text,$content);
						}else{
							error_log("_ENGINE_parse_body() Unable to find {{$val}} in array of ".count($_SESSION["LANG_FILES"]));
							
						}
					}}
		
		$content=str_replace("<input type='button'","<input type='button' class='button'",$content);
		return $content;
		}	
		
	

	private function _GetLangs(){
		if($_COOKIE["ArticaForceLanguageUsers"]<>null){return $_COOKIE["ArticaForceLanguageUsers"];}
		if($_SESSION["privs"]["ForceLanguageUsers"]<>null){return $_SESSION["privs"]["ForceLanguageUsers"];}
		$lang=new articaUserLang();
		return $lang->get_languages();
	}
		
		

	private function CurrentPageName(){
	$phpPage=basename($_SERVER["SCRIPT_FILENAME"]);
	return $phpPage;}  		

}


function Field_text($name,$value=null,$style=null,$class=null,$OnChange=null,$help=null,$helpInside=false,$jsPressKey=null,$DISABLED=false,$OnClick=null){
	$value=trim($value);
	if($style==null){$style='width:98%';$size="size=50";}
	if($class<>null){$class="class='$class'";}
	if($OnChange<>null){$OnChange="\nOnChange=\"javascript:$OnChange\"";}
	if($jsPressKey<>null){$jsPressKey="\nonkeypress=\"javascript:$jsPressKey;\"";}
	if($OnClick<>null){$OnClick="OnClick=\"javascript:$OnClick;\"";}
	
	if($help<>null){
		$tpl=new templates();
		$help_source=$help;
		$help_source=str_replace('{','',$help_source);
		$help_source=str_replace('}','',$help_source);
		$help=$tpl->_ENGINE_parse_body($help);
		$help=str_replace("\n","",$help);
		$help=str_replace("\r\n","",$help);
		$help=str_replace("\r","",$help);
		$help=str_replace('"',"`",$help);	
	
		if($helpInside==false){
		$help=help_icon($help);
		}
		else{
		$OnMouseOver="OnMouseOver=\"javascript:AffBulle('$help');lightup(this, 100);\" ";
		$OnMouseOut="OnMouseOut=\"javascript:HideBulle();lightup(this, 50);\"";
		$help=null;
		}
	}
	
	if($DISABLED){$disa=" DISABLED";}
	return "$help
	<input type='text' id='$name' name='$name' value=\"$value\" $OnChange $jsPressKey $OnClick style='$style' $class $size $OnMouseOver $OnMouseOut$disa>";
}
function Field_password($name,$value=null,$style=null,$class=null,$OnChange=null,$help=null,$helpInside=false,$jsPressKey=null,$DISABLED=false,$OnClick=null){
	$value=trim($value);
	if($style==null){$style='width:98%;letter-spacing:3px';$size="size=50";}
	if($class<>null){$class="class='$class'";}
	if($OnChange<>null){$OnChange="\nOnChange=\"javascript:$OnChange\"";}
	if($jsPressKey<>null){$jsPressKey="\nonkeypress=\"javascript:$jsPressKey;\"";}
	if($OnClick<>null){$OnClick="OnClick=\"javascript:$OnClick;\"";}
	
	if($help<>null){
		$tpl=new templates();
		$help_source=$help;
		$help_source=str_replace('{','',$help_source);
		$help_source=str_replace('}','',$help_source);
		$help=$tpl->_ENGINE_parse_body($help);
		$help=str_replace("\n","",$help);
		$help=str_replace("\r\n","",$help);
		$help=str_replace("\r","",$help);
		$help=str_replace('"',"`",$help);	
	
		if($helpInside==false){
		$help=help_icon($help);
		}
		else{
		$OnMouseOver="OnMouseOver=\"javascript:AffBulle('$help');lightup(this, 100);\" ";
		$OnMouseOut="OnMouseOut=\"javascript:HideBulle();lightup(this, 50);\"";
		$help=null;
		}
	}
	
	if($DISABLED){$disa=" DISABLED";}
	return "$help
	<input type='password' id='$name' name='$name' value=\"$value\" $OnChange $jsPressKey $OnClick style='$style' $class $size $OnMouseOver $OnMouseOut$disa>";
}
function Field_array_Hash($Hash,$FieldName,$default=null,$java=null,$class='',$maxlength=0,$style_of_field=null,$disabled=false){
	if(!is_array($Hash)){return "<input type='hidden' name='$FieldName' value='0' id='$FieldName'>";}
		if($java<>null){$java="OnChange=\"javascript:$java\"";}	
	$class="class='$class'  style='$style_of_field'";
if($disabled){$DISABLED_TOKEN="DISABLED";}
	$html="
	<!--Field_array_Hash value=\"$default\" -->
	<select name=\"$FieldName\" id='$FieldName' $java $class $DISABLED_TOKEN>";	
	reset ($Hash);
	while (list($num,$val)=each($Hash)){	
	$Default_value=$num;
	if(is_numeric($Default_value) && is_numeric($default)){
			if($Default_value==$default){$def="selected";}else{$def=null;}
	}
	if((!is_numeric($Default_value)) && (!is_numeric($default))){
		if($Default_value==$default){$def="selected";}else{$def=null;}
	}
	if($maxlength>0){
		if (strlen($val)>$maxlength){
			$val=substr($val,0,$maxlength-3) . "...";
		}
	}
	
	
	
	$html=$html ."
		<option value=\"$Default_value\" $def>$val</option>\n";
	}
		
	$html=$html ."</select>	";
return $html;}

function CurrentPageName(){
	$phpPage=basename($_SERVER["SCRIPT_FILENAME"]);
	return $phpPage;} 

function imgtootltip($img,$tooltip=null,$js=null,$align=null,$imgid=null){
	$tooltip=ParseTooltip($tooltip);
	if($imgid<>null){$id=$imgid;}
	$alpha="style=\"filter:alpha(opacity=50);-moz-opacity:0.5;border:0px;text-decoration:none\"";
	$lightup1="lightup(this, 100);";
	$lightup2="lightup(this, 50);";	
	if($align<>null){$align=' align=\''.$align.'\' ';}
	
	
	
	$image_path="img/$img";
	if(preg_match('#images\.listener\.php#',$img)){
		$image_path=$img;
	}
	
	if(preg_match('#img\/(.+)#',$img,$re)){
		$image_path=$img;
	}

	if(is_file($img)){$image_path=$img;}
	
	if($js<>null){$js="OnClick=\"javascript:$js;\"";}
	if($tooltip<>null){$tooltip=tooltipjs($tooltip);}
	return "<img src='$image_path' $tooltip $alpha $align id='$id' $js 	
			>";
	
}
function tooltipjs($tooltip,$noalpha=0){
	$tooltip=ParseTooltip($tooltip);
	$alpha="style=\"filter:alpha(opacity=50);-moz-opacity:0.5;border:0px;\"";
	$lightup1="lightup(this, 100);";
	$lightup2="lightup(this, 50);";
	if($noalpha==1){$alpha=null;$lightup1=null;$lightup2=null;}	
	
	
	return " onMouseOver=\"javascript:AffBulle('$tooltip');$lightup1;this.style.cursor='pointer';\" OnMouseOut=\"javascript:HideBulle();$lightup2;this.style.cursor='auto'\" $alpha";
}
function ParseTooltip($tooltip){
if($tooltip<>null){
		$tpl=new templates();
		$tooltip=$tpl->_ENGINE_parse_body($tooltip);
		$tooltip=str_replace("\n",'',$tooltip);	
		$tooltip=str_replace("\r",'',$tooltip);	
		$tooltip=str_replace("\r\n"," ",$tooltip);
		$tooltip=trim($tooltip);
		
}$tooltip=ASCII_TO_HTML($tooltip);return $tooltip;
}
function ASCII_TO_HTML($data){
	$data=str_replace("'","&#39",$data);
	$data=str_replace('"',"&#34",$data);
	$data=str_replace('"',"&#34",$data);
	$data=str_replace('�',"&eacute;",$data);
	$data=str_replace('�',"&egrave;",$data);
	$data=str_replace('�',"&agrave;",$data);
	$data=str_replace('�',"&acirc;",$data);
	$data=str_replace("\rn","<br>",$data);
	$data=str_replace("\n","<br>",$data);
	$data=str_replace("\r","<br>",$data);
	
	return $data;
}
function divlien($js,$text){
	return "<div 
			OnClick=\"javascript:$js\" 
			OnMouseOver=\"javascript:this.style.cursor='pointer';this.style.textDecoration='underline'\"  
			OnMouseOut=\"javascript:this.style.cursor='auto';this.style.textDecoration='none'\"
			style='padding:0px;margin:0px'
			>$text</div>";
}

function button($text,$js){
	
	$over="OnMouseOut=\"javascript:this.className='ui-state-default ui-corner-all';this.style.cursor='pointer'\"";
	$out="OnMouseOver=\"javascript:this.className='ui-state-active ui-corner-all';this.style.cursor='auto'\"";
	
	return "<button type='button' style='padding:5px' $over $out class='ui-state-default ui-corner-all' OnClick=\"javascript:this.className='ui-state-over';$js;\">$text&nbsp;&raquo</button>";
}
function button_hidden(){
	
	$over="OnMouseOut=\"javascript:this.className='ui-state-over ui-corner-all'\"";
	$out="OnMouseOver=\"javascript:this.className='ui-state-over ui-corner-all'\"";
	
	return "<button type='button' style='padding:5px' $over $out class='ui-state-over ui-corner-all' OnClick=\"javascript:this.className='ui-state-over';;\">{disabled}&nbsp;&raquo</button>";
}

function iconTable($img=null,$title,$text,$js=null,$tooltip=null,$width=255){
	$id=md5(date("H:i:s").$title.$text.$js.rand(0, 1500)*2000);

	if($img<>null){
		$img="<img src='img/$img' style='margin:3px;padding:5px' title='$text'>";
	}
	if($js<>null){$ONClick="OnClick=\"javascript:$js\"";}
	
	$html="
	<div class=Par style='width:{$width}px' 
		OnMouseOver=\"javascript:this.style.cursor='pointer'\" 
		OnMouseOut=\"javascript:this.style.cursor='auto'\" 
		$ONClick>
	<table>
				<tr>
					<td valign='top'>$img</td>
					<td valign='top'  style='padding-left:5px'>
						<div class=ParTitle>$title</div>
						<div style='font-size:11px;font-weight:normal;color:black'>$text</div>
					</td>
				</tr>
			</table>
		</div>
	";
		return $html;
	
	
}

function Paragraphe($img,$title,$text,$link=null,$tips="go_to_section",$width="210",$height=null,$nowrap=0,$float=false,$nojs=false){
	
	$link=str_replace("javascript:","",$link);
	return iconTable($img,$title,$text,$link,$width);
	
	
}
function ParagrapheTXT($text=null){
	
	$html="<div class='p_head'></div>
	<div class=t_head>
	$text</div>	
	";
	return $html;
}
function CellRollOver($jsOnClick=null,$tooltips=null){
	if($jsOnClick<>null){
		$jsOnClick=str_replace("javascript:",'',$jsOnClick);
		$java="OnClick=\"javascript:$jsOnClick\"";
		$cursor="this.style.cursor='pointer'";
		}
	if($tooltips<>null){
		$lightup1="lightup(this, 100);";
		$lightup2="lightup(this, 50);";
		$mtips_1="AffBulle('$tooltips');";
		$mtips_2="HideBulle();";
		}
	
	return "OnMouseOver=\"this.style.background='#D1D1D1';$cursor;$mtips_1\" OnMouseOut=\"this.style.background='transparent';this.style.cursor='default';$mtips_2\" $java ";
	}
function Field_checkbox($name,$enabledatas,$value=null,$js=null,$help=null){
	$def=$value;
if($value<>null){if($value==$enabledatas){$value="checked";}else{$value="";}}else{$value=null;}
if($help<>null){
		$help=nl2br($help);
		$help=str_replace("\n","",$help);
		$help=str_replace("\r\n","",$help);
		$help=str_replace("\r","",$help);
		$help=str_replace('"',"`",$help);
		$help=htmlentities($help);	
		$helpjs="onMouseOver=\"javascript:AffBulle('$help');lightup(this, 100);\" 
		OnMouseOut=\"javascript:HideBulle();lightup(this, 50);\" ";

}
		if($js<>null){
			if(strpos($js,"script:")==0){$js="javascript:$js";}
			if(strpos($js,"lick=\"")==0){$js="OnClick=\"$js\"";}
		}


$html="
<!-- $value $def -->
<input type=\"checkbox\" id='$name' name=\"$name\" value=\"$enabledatas\" $value style='padding:0px;margin:0px;border:0px' $js $helpjs>";
return $html;}


function ParseOrginalMessage($messages,$message_id,$page){
	$tpl=new templates();
	if(preg_match("#<body>(.+?)</body>#is",$messages,$re)){
		$messages=$re[1];
	}
	
	if(preg_match("#<h1>(.+?)</h1>#is",$messages,$re)){
		$title=$re[1];
		$messages=str_replace($re[0],'',$messages);
	}
	
	return 
	"<div class=message style='width:775px;height:500px;overflow:auto'>
	<table style='width:95%'>
		<tr>
			<td width=95%><H3>$title</H3></td>
			<td nowrap>". $tpl->_ENGINE_parse_body(button("{release_mail}","Loadjs('$page?release-mail=$message_id')"))."</td>
		</tr>
	</table>
	
	$messages</div>";
	
}
function TinyMce($FiledName,$datas){
	

$html="
<script language=\"javascript\" type=\"text/javascript\" src=\"js/tiny_mce/tiny_mce.js\"></script>
<script language=\"javascript\" type=\"text/javascript\">
" . TinyMceInit()."
</script>
<textarea id=\"$FiledName\" name=\"$FiledName\" rows=\"15\" cols=\"80\" style=\"width: 100%\">$datas</textarea>

";
return $html;	
}

function TinyMceInit(){
	
return "	
	tinyMCE.init({
		// General options
		mode : \"textareas\",
		theme : \"advanced\",
		skin : \"o2k7\",
		skin_variant : \"silver\",
		plugins : \"safari,pagebreak,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups\",

		// Theme options
		theme_advanced_buttons1 : \"save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect\",
		theme_advanced_buttons2 : \"cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor\",
		theme_advanced_buttons3 : \"tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen\",
		theme_advanced_buttons4 : \"insertlayer,moveforward,movebackward,absolute,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak\",
		theme_advanced_toolbar_location : \"top\",
		theme_advanced_toolbar_align : \"left\",
		theme_advanced_statusbar_location : \"bottom\",
		theme_advanced_resizing : true,
	});
";	
	
}
function Field_TRUEFALSE_checkbox_img($name,$value,$tooltip=null){
	if($value==true){$value=='true';}
	if($value==false){$value=='false';}
	if($tooltip==null){$tooltip='{click_enable_disable}';}
	$tooltip=ParseTooltip($tooltip);
	$value=strtoupper($value);
	if($value==null){$value="FALSE";}
	if($value==TRUE){$img='img/status_ok.gif';}
	if($value=='TRUE'){$img='img/status_ok.gif';}
	if($value=='FALSE'){$img='img/status_critical.gif';}
	if($value==false){$img='img/status_critical.gif';}
	
	if($tooltip<>null){$tooltip="onMouseOver=\"javascript:AffBulle('$tooltip');lightup(this, 100);\" OnMouseOut=\"javascript:HideBulle();lightup(this, 50);\" style=\"filter:alpha(opacity=50);-moz-opacity:0.5;border:0px;text-decoration:none;\"";}
	
	
	$html="
	<input type='hidden' name='$name' id='$name' value='$value'><a href=\"javascript:SwitchTRUEFALSE('$name');\" style='text-decoration:none;border:0px'><img src=\"$img\" id='img_$name' $tooltip style='text-decoration:none;border:0px'></a>";
	return $html;
	
}

function RoundedLightWhite($text){
	
return "<div>
  <b class=\"RLightWhite\">
  <b class=\"RLightWhite1\"><b></b></b>
  <b class=\"RLightWhite2\"><b></b></b>
  <b class=\"RLightWhite3\"></b>
  <b class=\"RLightWhite4\"></b>
  <b class=\"RLightWhite5\"></b></b>

  <div class=\"RLightWhitefg\" style='padding:7px;'>
   $text
  </div>

  <b class=\"RLightWhite\">
  <b class=\"RLightWhite5\"></b>
  <b class=\"RLightWhite4\"></b>
  <b class=\"RLightWhite3\"></b>
  <b class=\"RLightWhite2\"><b></b></b>
  <b class=\"RLightWhite1\"><b></b></b></b>
</div>
";	
}
function replace_accents($s) {
    $s = htmlentities($s);
    $s = preg_replace ('/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil|ring);/', '$1', $s);
    $s=str_replace("&Ntilde;","N",$s);
    $s=str_replace("&ntilde;","n",$s);
    $s=str_replace("&Oacute;","O",$s);
    $s=str_replace("&oacute;","O",$s);
    $s=str_replace("&Ograve;","O",$s);
    $s=str_replace("&ograve;","o",$s);
    $s=str_replace("&Ocirc;","O",$s);
    $s=str_replace("&ocirc;","o",$s);
    $s=str_replace("&Ouml;","O",$s);
    $s=str_replace("&ouml;","o",$s);
    $s=str_replace("&Otilde;","O",$s);
    $s=str_replace("&otilde;","o",$s);
    $s=str_replace("&Oslash;","O",$s);
    $s=str_replace("&oslash;","o",$s);
    $s=str_replace("&szlig;","b",$s);
    $s=str_replace("&Thorn;","T",$s);
    $s=str_replace("&thorn;","t",$s);
    $s=str_replace("&Uacute;","U",$s);
    $s=str_replace("&uacute;","u",$s);
    $s=str_replace("&Ugrave;","U",$s);
    $s=str_replace("&ugrave;","u",$s);
    $s=str_replace("&Ucirc;","U",$s);
    $s=str_replace("&ucirc;","u",$s);
    $s=str_replace("&Uuml;","U",$s);
    $s=str_replace("&uuml;","u",$s);
    $s=str_replace("&Yacute;","Y",$s);
    $s=str_replace("&yacute;","y",$s);
    $s=str_replace("&yuml;","y",$s);
    $s=str_replace("&Icirc;","I",$s);
    $s=str_replace("&icirc;","i",$s);  
    
    
      
    $s = html_entity_decode($s);
    
    return $s;		
}
function texttooltip($text,$tooltip=null,$js=null,$dbclk=null,$NOHREF=0,$style=null,$inner=null){
	
	if($js==null){$js='void(0)';}
	if($dbclk<>null){$js_dblclik="ondblclick=\"javascript:$dbclk\"";}
	
	$pointer_on="this.style.cursor='pointer'"; 
	$pointer_off="this.style.cursor='default'";
	
if($style<>null){$style=" style='$style'";}
	if($tooltip<>null){
		$tooltip=ParseTooltip($tooltip);
		$tooltip="onMouseOver=\"javascript:$pointer_on;AffBulle('$tooltip');this.style.textDecoration='underline';this.style.backgroundColor='none'\"  OnMouseOut=\"javascript:$pointer_off;HideBulle();this.style.textDecoration='none'\"";
		}
	
	if($js<>null){
		if(strpos($js,'ttp://')>0){$jshref="<a href=\"$js;\" target=_new $js_dblclik$style>";$jsafter="</a>";}else
		{$js="OnClick=\"$js;\" $js_dblclik";$jsafter="</a>";}
	}
	if($NOHREF==1){
		$js=null;
		$jsafter=null;
	}
	
	
	
	
	return "<div $style $js $tooltip>$jshref$text$jsafter$jsafter$inner</div>";
	
}
function Get_extension($filename){
		   	$parts = explode('.',$filename);
   			$last = count($parts) - 1;
   			$ext = $parts[$last];
   			return $ext;
		
		
		}
		function FormatBytes($kbytes){
	
	if($kbytes>1048576){
		$value=round($kbytes/1048576, 2);
		return "$value&nbsp;GB";
	}
	elseif ($kbytes>1024){
		$value=round($kbytes/1024, 2); 
		return "$value&nbsp;MB";
	}
	else{
		$value=round($kbytes, 2);
		return "$value&nbsp;KB";
	}
}
function ParseBytes($bytes){
	$kb=$bytes/1024;
	return FormatBytes($kb);
}		
function Field_hidden($name,$value){return "<input type='hidden' name='$name' id='$name' value=\"$value\">";}
function help_icon($help,$no_float=false,$additional_langfile=null){
$tpl=new templates();	
$help_source=$help;
		$help_source=str_replace('{','',$help_source);
		$help_source=str_replace('}','',$help_source);
		$help=str_replace("[br][br]","[br]",$help);
		$help=str_replace("\n","",$help);
		$help=str_replace("\r\n","",$help);
		$help=str_replace("\r","",$help);
		$help=str_replace('"',"`",$help);		
		$help=$tpl->_ENGINE_parse_body($help,$additional_langfile);
		$help=htmlentities($help);
		$help=str_replace("\n","",$help);
		$help=str_replace("\r\n","",$help);
		$help=str_replace("\r","",$help);		

		$myfloat="float:right";
		if($no_float>0){$myfloat=null;}
		if($additional_langfile==null){$additional_langfile=CurrentPageName();}
		
return "
		<div style='$myfloat;margin-top:-5px'>
		<img src='img/help.png'  
		onMouseOver=\"javascript:AffBulle('$help');lightup(this, 100);\" 
		OnMouseOut=\"javascript:HideBulle();lightup(this, 50);\" 
		style=\"filter:alpha(opacity=50);-moz-opacity:0.5;border:0px;\" 
		style=\"filter:alpha(opacity=50);-moz-opacity:0.5;border:0px;\" >
		</div>";	
	
}

function Paragraphe_switch_img($title,$text,$field_name=null,$value=null,$tips=null,$width="220"){
	
	$id=md5('Paragraphe_switch_img'.$field_name.$text.$title);
	$hrf="<a href='$link' class='nostylea'>";

	if(trim($tooltip)==null){$tooltip='{click_enable_disable}';}
	$tooltip=ParseTooltip($tooltip);
	if($value==null){$value="0";}
	if($tooltip<>null){$tooltip="
	onMouseOver=\"javascript:AffBulle('$tooltip');lightup(this,100);\" 
	OnMouseOut=\"javascript:HideBulle();lightup(this, 50);\" 
	style=\"filter:alpha(opacity=50);-moz-opacity:0.5;border:0px;\"";}
	
	
	if($value=='1'){$img='img/64-green.png';}else{$img='img/64-red.png';}
	$field="<input type='hidden' name='$field_name' id='$field_name' value='$value'>";
	
	
	
	
	
	$img="<div OnClick=\"javascript:SwitchBigNumeric('$field_name');\" OnMouseOver=\";this.style.cursor='pointer';\" OnMouseOut=\";this.style.cursor='default';\" >
			<img src='$img' id='img_$field_name'  $tooltip>
		</div>";
	
	
	
	$html="<div style='width:{$width}px;' >
	<table style='width:100%;'>
	<tr>
	<td width=1% valign='top' style='background-image:url(img/bg_pic-2.png);background-position:top center;background-repeat:no-repeat'><div style='min-height: 120px;'>$img</a></div></td>
	<td valign='top' id='$id'><h3 style='font-size:16px;color:#005447'>$title</h3>
	<div style='text-align:left;font-size:11px' id='text_$id'>$field$text</div>
	</td>
	</tr>
	</table>
	
	</div>";
	return $html;
	
	
}
function file_ext($filename)
{
$filename = strtolower($filename) ;
$exts = explode("[/\\.]", $filename) ;
$n = count($exts)-1;
$exts = $exts[$n];
return $exts;
} 
function pourcentage($pourc){
	$pourc=str_replace('%','',$pourc);
	$orginal_pourc=$pourc;
	$pourc=round($pourc);
	
	if($pourc<0){$pourc=0;}
	if($pourc==0){$int=-119;}
	if($pourc>0){$int=$pourc-118;}
	if($pourc>50){$int=$pourc-100;}
	
	
	$precent="
		<table><tr><td><img style=\"background:white url(img/percentImage_back4.png) no-repeat scroll 1px 0;margin:5px 0 0;padding:0;
		background-position:{$int}px 0pt;\"  
		src=\"img/percentImage.png\"></td><td widht=1% nowrap style='font-size:12px'>$orginal_pourc%</td></tr></table>";
	
	return $precent;
}
?>