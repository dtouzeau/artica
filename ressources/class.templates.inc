<?php
// SAVED 3oct 17:01

if(isset($GLOBALS["KAV4PROXY_NOSESSION"])){if(!$GLOBALS["KAV4PROXY_NOSESSION"]){session_start();}}
include_once(dirname(__FILE__) . "/class.browser.detection.inc");
include_once(dirname(__FILE__) . "/class.mailboxes.inc");
include_once(dirname(__FILE__) . "/class.sockets.inc");
include_once(dirname(__FILE__) . '/class.ldap.inc');
include_once("logs.inc");
include_once(dirname(__FILE__) . '/class.users.menus.inc');
include_once(dirname(__FILE__) . '/class.icons.inc');
include_once(dirname(__FILE__).  '/class.os.system.inc');


CheckSessions();


$GLOBALS['_DLANG']='en';
$GLOBALS['_LANG'] = array(
		'af',	//	afrikaans.
		'ar',	//	arabic.
		'bg',	//	bulgarian.
		'ca',	//	catalan.
		'cs',	//	czech.
		'da',	//	danish.
		'de',	//	german.
		'el',	//	greek.
		'en',	//	english.
		'es',	//	spanish.
		'et',	//	estonian.
		'fi',	//	finnish.
		'fr',	//	french.
		'gl',	//	galician.
		'he',	//	hebrew.
		'hi',	//	hindi.
		'hr',	//	croatian.
		'hu',	//	hungarian.
		'id',	//	indonesian.
		'it',	//	italian.
		'ja',	//	japanese.
		'ko',	//	korean.
		'ka',	//	georgian.
		'lt',	//	lithuanian.
		'lv',	//	latvian.
		'ms',	//	malay.
		'nl',	//	dutch.
		'no',	//	norwegian.
		'pl',	//	polish.
		'pt',	//	portuguese.
		'ro',	//	romanian.
		'ru',	//	russian.
		'sk',	//	slovak.
		'sl',	//	slovenian.
		'sq',	//	albanian.
		'sr',	//	serbian.
		'sv',	//	swedish.
		'th',	//	thai.
		'tr',	//	turkish.
		'uk',	//	ukrainian.
		'zh' 	//	chinese.
);

function CurrentPageName(){
	$phpPage=basename($_SERVER["SCRIPT_FILENAME"]);
	return $phpPage;}  

class templates{
	var $head;
	var $body;
	var $web_page;
	var $language;
	var $left_menus;
	var $top_menus;
	var $search_menus;
	var $title;
	var $js_add;
	var $GLOBAL_CONFIG;
	var $array_config;
	var $AutomaticConfig=false;
	var $pattern_array;
	function templates($title=null,$body=null,$js_add=null,$do_nothing=0,$arrayConfig=array()){
			if(!is_file(dirname(__FILE__) . '/settings.inc')){
				if(posix_getuid()==0){
					shell_exec("/usr/share/artica-postfix/bin/process1 --force");
				}
			}
		
			if(is_file(dirname(__FILE__) . '/settings.inc')){@include(dirname(__FILE__) . '/settings.inc');$this->GLOBAL_CONFIG=$_GLOBAL;}
			$this->AutomaticConfig=$this->GLOBAL_CONFIG["AutomaticConfig"];
			$this->array_config=$arrayConfig;
			if($do_nothing==1){return false;}
			$this->title=$title;
			$this->js_add=$js_add;
			$this->language=$this->_detect_lang();
			$currentPage=CurrentPageName();
			if($currentPage=="logon.php"){error_log("$currentPage=> language: $this->language class".__CLASS__ ."(".__FUNCTION__.") in " . __FILE__. " line ".__LINE__);}
			
			$this->headers();
			$this->search_menus=$this->search_menus();
			$this->Body($body);
			$this->web_page="$this->head $this->body</html>";
			$this->_parse_body();
			
			}
	
	function search_menus(){
		if(isset($_SESSION["uid"])){
		$html="<div class=search><strong style='color:#CCCCCC' >{search}:</strong>&nbsp;
		<input type='text' value='{$_G["finduser"]}' id='finduser' style='border:1px solid #000000;width:120px' OnKeyPress=\"javascript:Findusr(event);\">
		&nbsp;<input type='submit' value='{submit}&nbsp;&raquo' style='margin-bottom:0px' OnClick=\"javascript:FindUser();\">
		</div>";
		return $this->_ENGINE_parse_body($html);
		}
		
	}
	
	function headers($array=null){
		if($this->language==null){$this->language="en";}
		$backspace="\n\t\t";

		if(substr($this->language,strlen($this->language),1)=='/'){
			$this->language=substr($this->language,0,strlen($this->language)-1);
		}
		$page=CurrentPageName();
		if(is_file(dirname(__FILE__) . "/language/$this->language/$page.js")){
		 	$js_addon="$backspace<script type=\"text/javascript\" language=\"JavaScript\" src=\"ressources/language/$this->language/$page.js\"></script>";
		}
		
		if($array["INSERT"]<>null){
			$js_addon3="$backspace<script type=\"text/javascript\">\n\t".file_get_contents($array["INSERT"]) . "\n</script>";
		}		
		
		if($this->js_add<>null){
			$js_addon_2="$backspace<script type=\"text/javascript\" language=\"JavaScript\" src=\"ressources/language/$this->language/$this->js_add\"></script>";
		}
		
		if($array["css"]==null){
			$array["css"]="$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"img/content.css\"/>$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"img/forms.css\"/>";
			
		}
		
		if($array["TITLE"]<>null){$this->title=$this->_ENGINE_parse_body($array["TITLE"]);}
		if($array["refresh"]>0){$refresh="<meta http-equiv=\"refresh\" content=\"{$array["refresh"]}\">";}
		
		$tafelpages["domains.edit.group.php"]=true;
		$tafelpages["artica.backup.index.php"]=true;
		$tafelpages["SambaBrowse.php"]=true;
		$tafelpages["fileshares.index.php"]=true;
			
			
			
			
			if(is_array($array["JS"])){
				while (list ($num, $val) = each ($array["JS"]) ){
				$javascripts=$javascripts . "$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"$val\"></script>";
				}
			}else{$javascripts=$array["JS"];}
			

			$html=$html . $backspace.$mouse_position_js;
			$html=$html . "$backspace<!--  css files  -->\n";
			$html=$html .  $GLOBALS["ADD_HTML_HEADER"]."\n".$this->headcss();
			$html=$html . "$backspace<!-- Artica javascript  -->";
			$html=$html . $this->jsArtica();
			$html=$html . "$backspace<!-- js Artica  -->";
			$html=$html . $js_addon;
			$html=$html . $js_addon_2;
			$html=$html . $js_addon3;
			$html=$html . "$javascripts\n";
			//$html=$html . $this->YahooInstances();
			
			$html=str_replace('<script type="text/javascript" language="javascript" src=""></script>','',$html);
			$this->head=$html;
			return $this->head;
	}
	
	function _parse_body($content=null){
		return $this->_ENGINE_parse_body($content);
	}
	
	
	function YahooBody(){
			$conf=$conf ."<div id=\"SetupControl\" style='width:0;height:0'></div>\n";			
			$conf=$conf. "<div id=\"dialogS\" style='width:0;height:0'></div> \n";
			$conf=$conf. "<div id=\"dialogT\" style='width:0;height:0'></div> \n";
			$conf=$conf ."<div id=\"dialog0\" style='width:0;height:0'></div> \n";
			$conf=$conf ."<div id=\"dialog1\" style='width:0;height:0'></div>\n";
			$conf=$conf ."<div id=\"dialog2\" style='width:0;height:0'></div> \n";
			$conf=$conf ."<div id=\"dialog3\" style='width:0;height:0'></div>\n";
			$conf=$conf ."<div id=\"dialog4\" style='width:0;height:0'></div>\n";
			$conf=$conf ."<div id=\"dialog5\" style='width:0;height:0'></div>\n";
			$conf=$conf ."<div id=\"dialog6\" style='width:0;height:0'></div>\n"; 
			$conf=$conf ."<div id=\"YahooUser\" style='width:0;height:0'></div>\n"; 
			$conf=$conf ."<div id=\"logsWatcher\" style='width:0;height:0'></div>\n";
			$conf=$conf ."<div id=\"WinORG\" style='width:0;height:0'></div>\n";	
			$conf=$conf ."<div id=\"WinORG2\" style='width:0;height:0'></div>\n";						
			$conf=$conf ."<div id=\"RTMMail\" style='width:0;height:0'></div>\n";			
			$conf=$conf ."<div id=\"Browse\" style='width:0;height:0'></div>\n";
			$conf=$conf ."<div id=\"SearchUser\" style='width:0;height:0'></div>\n";									
			return $conf;		
	}
	
	function jsArtica(){
			$backspace="\n\t\t";
			if(!function_exists("browser_detection")){include(dirname(__FILE__).'/class.browser.detection.inc');}
			$browser=browser_detection();
			$html="$backspace<!-- $this->language  -->$backspace<!-- " . dirname(__FILE__) . "/language/$this->language/$page.js  -->$backspace" ;
		
			if(CurrentPageName()=='domains.php' or CurrentPageName()=='samba.index.php'){
				$html=$html . "\n<script type=\"text/javascript\" src=\"js/prototype.js\"></script>
				<script type=\"text/javascript\" language=\"javascript\" src=\"js/scriptaculous.js\"></script>
				<script type=\"text/javascript\" language=\"javascript\" src=\"Tree.js\"></script>
				<script type=\"text/javascript\" language=\"javascript\" src=\"ressources/language/en/users.kav.php.js\"></script>	
				<script type=\"text/javascript\" language=\"javascript\" src=\"ressources/language/en/users.kas.php.js\"></script>	
				<script type=\"text/javascript\" language=\"javascript\" src=\"task.js\"></script>
				<script type=\"text/javascript\" language=\"javascript\" src=\"postfix.js\"></script>
				";
			}
			
			$mouse=$mouse . "$backspace<script type=\"text/javascript\" language=\"JavaScript\" src=\"mouse.js\"></script>";
			if($browser=='ie'){
			$mouse=$mouse . "$backspace<script type=\"text/javascript\" language=\"JavaScript\" src=\"mouse_ie.js\"></script>";
			}		

			/*
			$tafel=$tafel ."$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"ressources/ajaxproto/prototype.js\"> </script>";
			$tafel=$tafel ."$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"ressources/ajaxproto/effects.js\"> </script>";
			$tafel=$tafel ."$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"ressources/ajaxproto/window.js\"> </script>";
			$tafel=$tafel ."$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"ressources/ajaxproto/window_ext.js\"> </script>";
			$tafel=$tafel ."$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"ressources/ajaxproto/debug.js\"> </script>";	
			$tafel=$tafel ."$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/tafelTree/js/scriptaculous.js\"> </script>";
			$tafel=$tafel ."$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/tafelTree/Tree.js\"> </script>";	
			$tafel=$tafel ."$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"img/tree.css\" />";
			$tafel=null;
			*/
			$floatbarr="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/float-barr.js\"></script>";
			$TimersLogs="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"TimersLogs.js\"></script>";
			$artica_confapply="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/artica_confapply.js\"></script>";
			$editusers="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/edit.user.js\"></script>";
			$cookies="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/cookies.js\"></script>";
			$default="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"default.js\"></script>";
			
			
			//$jquery="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery-1.3.2.min.js\"></script>";
			//$jquery6="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery-ui-1.7.2.custom.min.js\"></script>";
			$jquery="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery-1.4.2.min.js\"></script>";
			$jquery6="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery-ui-1.8.custom.min.js\"></script>";
			
			
			$jquery2="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/jqueryFileTree.js\"></script>";
			$jquery3="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.easing.1.3.js\"></script>";
			$jquery4="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.uploadify.v2.1.0.min.js\"></script>";
			$jquery5="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery-swfobject.js\"></script>";
			
			$jquery7="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/thickbox-compressed.js\"></script>";
			$jquery8="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.simplemodal-1.3.3.min.js\"></script>";
			$jquery9="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.jgrowl_minimized.js\"></script>";
			$jquery10="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.cluetip.js\"></script>";
			$jquery11="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.blockUI.js\"></script>";
			$jquery12="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.metadata.js\"></script>";
			$jquery13="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/mbContainer.min.js\"></script>";

			$jquery14="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.treeview.min.js\"></script>";
			//$jquery6=$jquery6. "$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/core/ui.resizable.js\"></script>";
			//$jquery6=$jquery6. "$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/core/jquery-ui-1.7.2.custom.js\"></script>";
			//$jquery6=$jquery6. "$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/core/ui.selectable.js\"></script>";
			//$jquery6=$jquery6. "$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/core/ui.draggable.js\"></script>";
			//$jquery6=$jquery6. "$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/core/ui.sortable.js\"></script>";
			//$jquery6=$jquery6. "$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/core/ui.droppable.js\"></script>";
			//$jquery6=$jquery6. "$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/core/ui.core.js\"></script>";	
			

			
			if((CurrentPageName()=="logon.php") OR (CurrentPageName()=="logoff.php")){
				$tafel=null;
				$floatbarr=null;
				$TimersLogs=null;
				$artica_confapply=null;
				$editusers=null;
				}
			
			

			
			$html=$html . "$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"XHRConnection.js\"></script>";
			$html=$html . $tafel;
			$html=$html . $mouse;
			$html=$html . $TimersLogs;
			$html=$html . $floatbarr;
			$html=$html . $artica_confapply;
			$html=$html . $editusers;
			$html=$html . $default;
			$html=$html . $cookies;
			$html=$html. $jquery;
			$html=$html. $jquery2;
			$html=$html. $jquery3;
			//$html=$html. $jquery4;
			//$html=$html. $jquery5;
			$html=$html. $jquery6;
			$html=$html. $jquery7;
			$html=$html. $jquery8.$jquery9.$jquery10.$jquery11.$jquery14;

			return $html;		
	
	}
	
	

	
	
	function headcss(){
		$backspace="\n\t\t";
		//if(strlen($_SESSION["ARTICA_HEAD_TEMPLATE"])>100){return $backspace."<!-- Session head -->".$_SESSION["ARTICA_HEAD_TEMPLATE"];}
		if($_COOKIE["artica-template"]==null){$_COOKIE["artica-template"]="default";}
		if($_COOKIE["artica-template"]=='!nil'){$_COOKIE["artica-template"]="default";}
		$html=$html . $backspace."<!-- template used :".dirname(__FILE__)."/templates/{$_COOKIE["artica-template"]}/index.html-->";
			
			$css_files["menu.css"]="menu.css";
			$css_files["contact.css"]="contact.css";
			$css_files["style.css"]="style.css";
			$css_files["styles_rounded.css"]="styles_rounded.css";
			$css_files["styles_tabs.css"]="styles_tabs.css";
			$css_files["styles_forms.css"]="styles_forms.css";
			$css_files["styles_tables.css"]="styles_tables.css";
			$css_files["titles.css"]="titles.css";
			$css_files["nav.css"]="nav.css";
			$css_files["menus_top.css"]="menus_top.css";
			$css_files["blurps.css"]="blurps.css";
			$css_files["isoqlog.css"]="isoqlog.css";
			
			if((CurrentPageName()=="logon.php") OR (CurrentPageName()=="logoff.php")){
				unset($css_files["menu.css"]);
				unset($css_files["style.css"]);
				unset($css_files["isoqlog.css"]);
				unset($css_files["blurps.css"]);
				unset($css_files["nav.css"]);
				unset($css_files["styles_tables.css"]);	
				unset($css_files["styles_forms.css"]);
				unset($css_files["styles_tabs.css"]);
				unset($css_files["styles_rounded.css"]);		
				
													
				
			}
				reset($css_files);
				while (list ($num, $val) = each ($css_files) ){
					if(file_exists(dirname(__FILE__)."/templates/{$_COOKIE["artica-template"]}/$val")){
						$css_array[]="$backspace<link rel=\"stylesheet\" type=\"text/css\" rel=\"styleSheet\"  href=\"ressources/templates/{$_COOKIE["artica-template"]}/$val\" />";
					}else{
						$csserror[]="$backspace<!-- missing ".dirname(__FILE__)."/templates/{$_COOKIE["artica-template"]}/$val -->";
						
					}
				}
				
			if(is_array($csserror)){$html=$html . "$backspace".implode("",$csserror);}
			if(is_array($css_array)){$html=$html . "$backspace".implode("",$css_array);}
			$html=$html . "$backspace<link href=\"css/calendar.css\" rel=\"stylesheet\" type=\"text/css\">";
			$html=$html . "$backspace<link href=\"js/jqueryFileTree.css\" rel=\"stylesheet\" type=\"text/css\">";
			$html=$html . "$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"css/uploadify.css\" />";	
			$html=$html . "$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"css/artica-theme/jquery-ui-1.7.2.custom.css\" />";	
			//$html=$html . "$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"css/artica-theme/jquery-ui-1.8.custom.css\" />";
			$html=$html . "$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"css/jquery.jgrowl.css\" />";
			//$html=$html . "$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"css/magicalHover-01beta2.css\" />";		
			$html=$html . "$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"css/jquery.cluetip.css\" />";
			$html=$html . "$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"css/jquery.treeview.css\" />";
			$html=$html . "$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"css/thickbox.css\" media=\"screen\"/>";
			//$html=$html . "$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"css/nyroModal.css\" media=\"screen\"/>";		
			$html=$html . "$backspace<div id='PopUpInfos' style='position:absolute'></div>";
			$html=$html . "$backspace<div id='find' style='position:absolute'></div>";
			
			
			
			$_SESSION["ARTICA_HEAD_TEMPLATE"]=$html;
			return $html;
		}
		
	function javascript_parse_text($content,$noremovecrlf=0){
		$content=$this->_ENGINE_parse_body($content);
		
		$content=html_entity_decode($content);
		
		$content=replace_accents($content);
		if($noremovecrlf==0){
			$content=nl2br($content);
			$content=str_replace("\n","\\n",$content);
			$content=str_replace("<br>","\\n",$content);
			$content=str_replace("<br />","\\n",$content);
			
		}else{
			$content=str_replace("<br />",'',$content);
			
		}
		return $content;
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
		if(posix_getuid()==0){return $content;}
		$sock=new sockets();
		if($this->language==null){$this->language="en";}
		
		
		if(is_array($content)){$content=implode(" ",$content);}
		
		if(preg_match_all('#{(.+?)}#is',$content,$regs)){
			while (list ($num, $val) = each ($regs[1]) ){
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
							
							$text=str_ireplace('%u201C','&laquo;',$text);
							$text=str_ireplace('%u201D','&raquo;',$text);
							
							$text=str_ireplace('[u]','<u>',$text);
							
							$text=str_ireplace('[blk]','<blockquote>',$text);	
							$text=str_ireplace('[/blk]','</blockquote>',$text);	
							if(preg_match_all('#\[a\s+(.+?)\]#',$text,$r)){
								while (list ($a, $b) = each ($r[1]) ){
								$text=str_replace($r[0][$a],"<a href=\"$b\">",$text);}
							}
													
							$content=str_replace("{{$val}}",$text,$content);
						}
					}}
		
		$content=str_replace("<input type='button'","<input type='button' class='button'",$content);
		return $content;
		}	
	
	function _readfile($filepath){
	$file = @fopen($filepath, "r");
	if ($file<>null){
			return @fread($file,filesize($filepath));
			fclose($file); 
			return null;
			}
		
	}
	

	
	


			
	
	
	function get_path_lang(){
		
		
	}
	
	
	function build_left_menus(){
		include_once(dirname(__FILE__) . "/class.menus.inc");
		$menus=new leftmenus();
		return $menus->html;
		
		
	}
	
	
	function build_top_menus(){
		include_once(dirname(__FILE__) . "/class.menus.inc");
		$menus=new top_menus();
		return $menus->html;
	}
	
	function BuildMenusBarr($menus){
		if(!isset($_SESSION["uid"])){
			unset($_SESSION["TEMPLATE_TOP_MENUS"]);
			return null;
		}
		
		if(isset($_SESSION["TEMPLATE_TOP_MENUS"])){return $_SESSION["TEMPLATE_TOP_MENUS"];}
		$tpl=new templates(null,null,null,1);
		while (list ($num, $ligne) = each ($menus)){
			$text=$this->_ENGINE_parse_body($num);
			if(strlen($text)>20){$text=texttooltip(substr($text,0,17).'...',$text,null,null,1);}
			$newarray[$num]=array("URI"=>$ligne,"TEXT"=>$text);
		}
		reset($menus);
		
		if(file_exists("ressources/templates/{$_COOKIE["artica-template"]}/menus-top-in-line")){
			while (list ($num, $ligne) = each ($newarray) ){
			if($page==$ligne){
				$class="id='current'";
			}else{$class=null;}
			$html=$html . "\t\t<a href=\"{$ligne["URI"]}\" title=\"$num\">{$ligne["TEXT"]}</a><span>|</span>\n";
			}
			$this->menus= $html;
			return ;
		}
		
		
		$html="<div id=menu_top>
		<ul>";
		while (list ($num, $ligne) = each ($newarray) ){
			if($page==$ligne){
				$class="id='current'";
			}else{$class=null;}
			$html=$html . "\t\t<li $class><a href='{$ligne["URI"]}'>{$ligne["TEXT"]}</a></li>\n";
			
		}
		$html=$html."<li id='topemnucurrentdate'></li>\n";
		$html=$html . "</ul>
		</div>";
		$_SESSION["TEMPLATE_TOP_MENUS"]=$html;
		return $html;
		}	
	
	
	function Build_high_menus(){
		$users=new usersMenus();
		
		$community="<td class=HeadMenus><a href='http://www.artica.fr/forum/viewforum.php?f=3'>{community}</a></td>";
		if($users->KASPERSKY_SMTP_APPLIANCE){
			$community="<td class=HeadMenus><a href='http://www.kaspersky.com'>Kaspersky Lab</a></td>";
		}
		if($users->AsAnAdministratorGeneric){
			$html="	
			<table style='width:80%;padding:0px;margin:0px;border:0px solid black'>
			<tr>
			$community
			<td class=HeadMenus><a href='#' OnClick=\"javascript:Loadjs('support.php');\">{support}</a></td>
			<td class=HeadMenus><a href='#' OnClick=\"javascript:Loadjs('artica.update.php?js=yes')\">{$users->ARTICA_VERSION}</a></td>	
			</tr>
			</table>
			";
			return $this->_ENGINE_parse_body($html);
			}
	}
	
	
	function Body($content=null){
		
		
		if(!isset($_SESSION["uid"])){
			$this->search_menus=null;
			$this->top_menus=null;
			}
			
		if($_COOKIE["artica-template"]==null){$_COOKIE["artica-template"]="default";}
		$template=$this->_readfile(dirname(__FILE__) . "/templates/{$_COOKIE["artica-template"]}/index.html");
		$template=str_replace("{TEMPLATE_SEARCH}",$this->search_menus,$template);
		$template=str_replace("{TEMPLATE_LEFT_MENUS}",$this->left_menus,$template);
		$template=str_replace("{TEMPLATE_TOP_MENUS}",$this->top_menus,$template);
		$template=str_replace("{TEMPLATE_CONTENT}",$content,$template);
		$template=str_replace("{TEMPLATE_TITLE}",$this->title,$template);
		$template=str_replace("{TEMPLATE_HIGH_MENUS}",$this->Build_high_menus(),$template);
		$template=str_replace("{TEMPLATE_BODY_YAHOO}",$this->YahooBody(),$template);
		$right="<div style='position:absolute;left:600px;top:50px'><img src='img/rght.gif'></div>";
		$template=str_replace("{RIGHT}",$right,$template);
		
		
		$this->body=$template;
		
		
	}
	
function _get_env_var($Var){
	if(empty($GLOBALS[$Var])){
		$GLOBALS[$Var] = (!empty($GLOBALS['_SERVER'][$Var])) ?$GLOBALS['_SERVER'][$Var] : (!empty($GLOBALS['HTTP_SERVER_VARS'][$Var])) ? $GLOBALS['HTTP_SERVER_VARS'][$Var] : '';
		}
	}

function _detect_lang(){
		$currentPage=CurrentPageName();
		if($currentPage=="logon.php"){error_log("Logon.php=> language: _COOKIE={$_COOKIE["artica-language"]} ".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
		
		if($_SESSION["detected_lang"]<>null){
			$this->language=$_SESSION["detected_lang"];
			return $_SESSION["detected_lang"];
		}
		
		if($_COOKIE["artica-language"]<>null){
			$this->language=$_COOKIE["artica-language"];
			return $_COOKIE["artica-language"];
		}
	
	 if($this->language==null){
			if(posix_getuid()==0){
				$sock=new sockets();
				$disk_session_language=$sock->GET_INFO("session_language");
				if($disk_session_language==null){$disk_session_language="en";}
				$this->language=$disk_session_language;
		}}
	
	
	if(isset($_SESSION["detected_lang"])){
		if(!is_dir(dirname(__FILE__)  ."/language/{$_SESSION["detected_lang"]}")){return "en";}else{return $_SESSION["detected_lang"];}
	}
	
	

	
	// Detect HTTP_ACCEPT_LANGUAGE & HTTP_USER_AGENT.
	$this->_get_env_var('HTTP_ACCEPT_LANGUAGE');
	$this->_get_env_var('HTTP_USER_AGENT');
	
	$_AL=strtolower($GLOBALS['HTTP_ACCEPT_LANGUAGE']);
	$_UA=strtolower($GLOBALS['HTTP_USER_AGENT']);
	
	// Try to detect Primary language if several languages are accepted.
	if(!is_array($GLOBALS['_LANG'])){return 'en';}
	foreach($GLOBALS['_LANG'] as $K){
		if(strpos($_AL, $K) === 0){
			if (!is_dir(dirname(__FILE__)  ."/language/$K")){return "en";}
			return $K;}
		}
	
	// Try to detect any language if not yet detected.
	foreach($GLOBALS['_LANG'] as $K){
		if(strpos($_AL, $K) !== false){
			if (!is_dir(dirname(__FILE__)  ."/language/$K")){return "en";}
			return $K;}
	}
	foreach($GLOBALS['_LANG'] as $K){
		if(preg_match("/[\[\( ]{$K}[;,_\-\)]/",$_UA)){
			if (!is_dir(dirname(__FILE__)  ."/language/$K")){return "en";}
			return $K;}
	}
	
	if (!is_dir(dirname(__FILE__)  ."/language/{$GLOBALS['_DLANG']}")){return "en";}	
	return $GLOBALS['_DLANG'];
}


	
	
	
}

function writequeries($function=null,$file=null){
	writelogs($_SERVER['REMOTE_ADDR'] . "->" . CurrentPageName() . "?{$_SERVER['QUERY_STRING']}",__FUNCTION__,__FILE__);
	
	
}


	
function writelogsSocketsLogs($text=null,$function=null,$file_source=null){
		$text=str_replace("\n"," ",$text);
		if($file_source==null){$file_source= __FILE__ ;}
		$file_source=basename($file_source);
			@mkdir(dirname(__FILE__) . '/logs/web');
		    $logFile=dirname(__FILE__) . '/logs/web/artica-socket-web.log';
		    if(!is_dir(dirname($logFile))){mkdir(dirname($logFile));}
   		if (is_file($logFile)) { 
   			$size=filesize($logFile);
		    	if($size>100000){unlink($logFile);}
   		}
		 $logFile=str_replace("//","/",$logFile);
		$f = @fopen($logFile, 'a');
		$date=date("Y-m-d H:i:s");
		@fwrite($f, "$date:($file_source) [$function()][{$_SERVER['REMOTE_ADDR']}]:: $text\n");
		@fclose($f);
				
		
	}	
	
function Field_hidden($name,$value){return "<input type='hidden' name='$name' id='$name' value=\"$value\">";}
	
function Field_checkbox($name,$enabledatas,$value=null,$js=null,$help=null){
	$def=$value;
if($value<>null){if($value==$enabledatas){$value="checked";}else{$value="";}}else{$value=null;}
		if($js<>null){
			if(strpos($js,"script:")==0){$js="javascript:$js";}
			if(strpos($js,"lick=\"")==0){$js="OnClick=\"$js\"";}
		}


$html="<input type=\"checkbox\" id='$name' name=\"$name\" value=\"$enabledatas\" $value style='padding:0px;margin:0px;border:0px' $js>";
return $html;}



function Field_yesno_checkbox($name,$value,$js=null){
	$value=strtolower(trim($value));
	$def=$value;
	if($js<>null){$js=" OnClick=\"javascript:$js\"";}
if($value=="yes"){$value="checked";}else{$value="";}	
$html="<!-- $value: $def -->
<input type=\"checkbox\" id='$name' name=\"$name\" value=\"yes\" $value style='padding:0px;margin:0px;border:0px' $js>";
return $html;}


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
		style=\"filter:alpha(opacity=50);-moz-opacity:0.5;border:0px;\" 
		OnClick=\"javascript:LoadHelp('$help_source','$additional_langfile',false);\">
		</div>";	
	
}


function Field_text($name,$value=null,$style=null,$class=null,$OnChange=null,$help=null,$helpInside=false,$jsPressKey=null,$DISABLED=false,$OnClick=null){
	$value=trim($value);
	if($style==null){$style='width:98%;font-size:12px';$size="size=50";}
	if($class<>null){$class="class='$class'";}
	if($OnChange<>null){$OnChange="\nOnChange=\"javascript:$OnChange\"";}
	if($jsPressKey<>null){$jsPressKey="\nonkeypress=\"javascript:$jsPressKey;\"";}
	if($OnClick<>null){$OnClick="OnClick=\"javascript:$OnClick;\"";}
	
	if(!preg_match("#font-size#",$style)){$style=$style.";font-size:12px";}
	
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
function Field_password($name,$value=null,$style=null,$class=null,$OnChange=null,$help=null,$helpInside=false,$jsPressKey=null){
	if($style==null){$style='width:120px';$size="size=50";}
	if($class<>null){$class="class='$class'";}
	if($OnChange<>null){$OnChange="\nOnChange=\"javascript:$OnChange\"";}
	if($jsPressKey<>null){$jsPressKey="\nonkeypress=\"javascript:$jsPressKey;\"";}
	if(!preg_match("#font-size#",$style)){$style=$style.";font-size:12px";}
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
		$OnMouseOver="OnMouseOver=\"javascript:AffBulle('$help');lightup(this, 100);\"";
		$OnMouseOut="OnMouseOut=\"javascript:HideBulle();lightup(this, 50);\"";
		$help=null;
		}
	}
	
	$md=md5($name);
	$img=imgtootltip('key_small.gif','{switch_view}',"SwitchPassword('$md','$name')");
	return "
	<div style='background-image:url(img/bg_pic-1.png);background-repeat:repeat-x;'>
		$help$img
			<span id='$md'>
				<input type='password' id='$name' name='$name' value=\"$value\" $OnChange $jsPressKey style='$style' $class $size $OnMouseOver $OnMouseOut>
			</span>
	</div>";
}

function imgtootltip($img,$tooltip=null,$js=null,$align=null,$imgid=null){
	$tooltip=ParseTooltip($tooltip);
	if($imgid<>null){$id=$imgid;}
	if($align<>null){$align=' align=\''.$align.'\' ';}

	$image_path="img/$img";
	if(preg_match('#images\.listener\.php#',$img)){
		$image_path=$img;
	}
	
	if(preg_match('#^ressources#',$img)){
		$image_path=$img;
	}	
	
	if(preg_match('#img\/(.+)#',$img,$re)){
		$image_path=$img;
	}

	if(is_file($img)){$image_path=$img;}
	
	
	
	if(preg_match("#SwitchPassword#",$js)){
		$float="float:right;";
	}
	
	
	
	if($js<>null){$js="OnClick=\"javascript:$js;\"";}
	if($tooltip<>null){$tooltip=tooltipjs($tooltip);}
	
	//$tooltip="OnMouseOver=javascript:\"$(function(){\$('li.thumb').magicalHover({speedView:200,speedRemove:400,altAnim:true,speedTitle:400,debug:false})});\"";	
	
	
	
	//$(this).fadeIn(500);
	$IMAGE="
	<div OnMouseOver=\";this.style.cursor='pointer';\" OnMouseOut=\";this.style.cursor='default';\" style='$float'>
		<img src='$image_path' $tooltip $align id='$id' $js>
	</div>";
	
		if(ae_detect_ie()){
			list($width, $height, $type, $attr) = getimagesize("$image_path");
			$IMAGE="<div STYLE=\"height:$width; width:$height;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='$image_path', sizingMethod='scale');\" $js></div>";
		}
		
		
		
	return "$IMAGE";
	
}

function PostFixTimeToPhp($PostFixDate){
	$months=array("Jan"=>"01","Feb"=>"02" ,"Mar"=>"03","Apr"=>"04", "May"=>"05","Jun"=>"06", "Jul"=>"07", "Aug"=>"08", "Sep"=>"09", "Oct"=>"10","Nov"=>"11", "Dec"=>"12");
	if(preg_match('#(\w+),\s+([0-9]+)\s+(\w+)\s+([0-9]+)\s+([0-9]+):([0-9]+):([0-9]+)#',$PostFixDate,$ar)){
			if(strlen($ar[2])==1){$ar[2]="0{$ar[2]}";}
			$date=$ar[2] . "/" . $months[$ar[3]] . "/" . $ar[4];
			return $date. ' ' . $ar[5].":".$ar[6];
			
		}
	if(preg_match('#(\w+)\s+(\w+)\s+([0-9]+)\s+([0-9\:]+)\s+([0-9]+)#',$PostFixDate,$ar)){
		if(strlen($ar[3])==1){$ar[3]="0{$ar[3]}";}
		$date=$ar[3] . '/' . $months[$ar[2]] . '/' . $ar[5] . ' ' . $ar[4];
		return $date;
	}
	//
		
	return $PostFixDate;
	
}

function PasswordGenerator(){
	
$totalChar = 7; // number of chars in the password
$salt = "abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789";  // salt to select chars from
srand((double)microtime()*1000000); // start the random generator
$password=""; // set the inital variable
for ($i=0;$i<$totalChar;$i++)  // loop and create password
            $password = $password . substr ($salt, rand() % strlen($salt), 1);
// *************************
// Display Password
// *************************
return $password;	
	
}

function tooltipjs($tooltip,$noalpha=0){
	$tooltip=ParseTooltip($tooltip);
	$lightup1="lightup(this, 100);";
	
	$lightup1="\$(this).fadeOut('slow');";
	
	//$lightup2="lightup(this, 50);";
	
	$lightup1="\$(this).fadeOut('slow');$(this).fadeIn('fast');AffBulle('$tooltip');";
	//$lightup1="\$(this).slideUp('slow');$(this).fadeIn('fast');\"";	
	
	if($noalpha==1){$alpha=null;$lightup1=null;$lightup2=null;}
	
	
	return " onMouseOver=\"javascript:\$(this).cluetip();$lightup1\" OnMouseOut=\"javascript:HideBulle();$lightup2\"";
	
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
function CellRollOver_jaune($jsOnClick=null,$tooltips=null){
	if($jsOnClick<>null){
		$java="OnClick=\"javascript:$jsOnClick\"";
		$cursor="this.style.cursor='pointer'";
		}
	if($tooltips<>null){
		$lightup1="lightup(this, 100);";
		$lightup2="lightup(this, 50);";
		$mtips_1="AffBulle('$tooltips');";
		$mtips_2="HideBulle();";
	}
	
	return "OnMouseOver=\"this.style.background='#feffd6';$cursor;$mtips_1\" OnMouseOut=\"this.style.background='transparent';this.style.cursor='default';$mtips_2\" $java ";
	
}

function CellRollOver_rouge($jsOnClick=null,$tooltips=null){
	if($jsOnClick<>null){
		$java="OnClick=\"javascript:$jsOnClick\"";
		$cursor="this.style.cursor='pointer'";
		}
	if($tooltips<>null){
		$lightup1="lightup(this, 100);";
		$lightup2="lightup(this, 50);";
		$mtips_1="AffBulle('$tooltips');";
		$mtips_2="HideBulle();";
	}
	
	return "style='background-color:red;color:white' OnMouseOver=\"this.style.background='black';this.style.color='white';$cursor;$mtips_1\" OnMouseOut=\"this.style.background='red';this.style.color='white';this.style.cursor='default';$mtips_2\" $java ";
	
}

function CellRollOver_black($jsOnClick=null,$tooltips=null,$orignal_color){
	if($jsOnClick<>null){
		$java="OnClick=\"javascript:$jsOnClick\"";
		
		}
	if($tooltips<>null){
		$lightup1="lightup(this, 100);";
		$lightup2="lightup(this, 50);";
		$mtips_1="AffBulle('$tooltips');";
		$mtips_2="HideBulle();";
		$cursor="this.style.cursor='pointer'";
	}
	
	return "OnMouseOver=\"this.style.background='#000000';this.style.color='white';$cursor;$mtips_1\" 
	OnMouseOut=\"this.style.background='$orignal_color';this.style.color='black';this.style.cursor='default';$mtips_2\" 
	$java ";
	
}


function icon_help($help,$nofloat=0){
	$f="float:right";
	$page=CurrentPageName();
	$text="{" . $help . "}";
	if($nofloat==1){$f=null;}
	
	$img="<div style='$f;width:1%;margin-top:-5px;'><img src='img/help.png' OnClick=\"javascript:LoadHelp('$help','$page');\"". tooltipjs('{click_for_help}') ."></div>";
	return "$img&nbsp;";
	
}

function texttooltip($text,$tooltip=null,$js=null,$dbclk=null,$NOHREF=0,$style=null){
	
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
	
	
	
	
	return "<div $style $js $tooltip>$jshref$text$jsafter$jsafter</div>";
	
}
function LinkToolTipjs($tooltip=null,$js=null,$style=null){
	
	if($js==null){$js='void(0)';}
	$pointer_on="this.style.cursor='pointer'"; 
	$pointer_off="this.style.cursor='default'";
	
if($style<>null){$style=" style='$style'";}
	if($tooltip<>null){
		$tooltip=ParseTooltip($tooltip);
		$tooltip="onMouseOver=\"javascript:$pointer_on;AffBulle('$tooltip');this.style.textDecoration='underline';this.style.backgroundColor='none'\"  OnMouseOut=\"javascript:$pointer_off;HideBulle();this.style.textDecoration='none'\"";
		}
	
	if($js<>null){
		if(strpos($js,'ttp://')>0){$jshref="<a href=\"$js;\" target=_new $style>";}else
		{$js="OnClick=\"$js;\"";}
	}
		
	return "<a $style $js $tooltip>";
	
}
function ParseTooltip($tooltip){
	if($GLOBALS["EXECUTED_AS_ROOT"]){return $tooltip;}
if($tooltip<>null){
		$tpl=new templates();
		$tooltip=$tpl->_ENGINE_parse_body($tooltip);
		$tooltip=str_replace("\n",'',$tooltip);	
		$tooltip=str_replace("\r",'',$tooltip);	
		$tooltip=str_replace("\r\n"," ",$tooltip);
		$tooltip=trim($tooltip);
		
}$tooltip=ASCII_TO_HTML($tooltip);return $tooltip;
}

function Field_yesno_checkbox_img($name,$value,$tooltip=null){
	$value=strtolower($value);
	if($tooltip==null){$tooltip='{click_enable_disable}';}
	$tooltip=ParseTooltip($tooltip);
	if($value==null){$value="no";}
	if($tooltip<>null){$tooltip="onMouseOver=\"javascript:AffBulle('$tooltip');lightup(this, 100);\" OnMouseOut=\"javascript:HideBulle();lightup(this, 50);\" style=\"filter:alpha(opacity=50);-moz-opacity:0.5;border:0px;\"";}
	if($value=='yes'){$img='img/status_ok.gif';}else{$img='img/status_critical.gif';}
	$html="
	<input type='hidden' name='$name' id='$name' value='$value'><a href=\"javascript:SwitchOnOff('$name');\"><img src='$img' id='img_$name' $tooltip></a>";
	return $html;
	
}



function Field_onoff_checkbox_img($name,$value,$tooltip=null){
	$value=strtolower($value);
	if($tooltip==null){$tooltip='{click_enable_disable}';}
	$tooltip=ParseTooltip($tooltip);
	if($value==null){$value="off";}
	if($tooltip<>null){$tooltip="onMouseOver=\"javascript:AffBulle('$tooltip');lightup(this, 100);\" OnMouseOut=\"javascript:HideBulle();lightup(this, 50);\" style=\"filter:alpha(opacity=50);-moz-opacity:0.5;border:0px;\"";}
	if($value=='on'){$img='img/status_ok.gif';}else{$img='img/status_critical.gif';}
	$html="
	<input type='hidden' name='$name' id='$name' value='$value'><a href=\"javascript:SwitchOnOff_on('$name');\"><img src='$img' id='img_$name' $tooltip></a>";
	return $html;
	
}
function Field_numeric_checkbox_img($name,$value,$tooltip=null,$OnClick=null){
	
	if($tooltip==null){$tooltip='{click_enable_disable}';}
	$tooltip=ParseTooltip($tooltip);
	if($value==null){$value="0";}
	if($tooltip<>null){$tooltip="onMouseOver=\"javascript:AffBulle('$tooltip');this.style.cursor='pointer'\" OnMouseOut=\"javascript:HideBulle();this.style.cursor='default'\"";}
	
	if($OnClick<>null){$click="$OnClick('$name');";}
	
	if($value=='1'){$img='img/status_ok.gif';}else{$img='img/status_critical.gif';}
	$html="
	<div OnClick=\"javascript:SwitchNumeric('$name');{$click}\" $tooltip style='width:22px;height:22px'>
		<input type='hidden' name='$name' id='$name' value='$value'>
		<img src='$img' id='img_$name' >
	</div>
	";
	return $html;
	
}
function Field_numeric_checkbox_img_disabled($name,$value,$tooltip=null){
	
	if($tooltip==null){$tooltip='{disbaled}';}
	$tooltip=ParseTooltip($tooltip);
	$name=md5(date('Y-mdhis'));
	$value=$name;
	if($tooltip<>null){$tooltip="onMouseOver=\"javascript:AffBulle('$tooltip');lightup(this, 100);\" OnMouseOut=\"javascript:HideBulle();lightup(this, 50);\" style=\"filter:alpha(opacity=50);-moz-opacity:0.5;border:0px;\"";}
	$img='img/status_disabled.gif';
	$html="
	<input type='hidden' name='$name' id='$name' value='$value'>
	<img src='$img' id='img_$name' $tooltip>";
	return $html;
	
}
function Field_key_checkbox_img($name,$value,$tooltip=null){
	$value=strtolower($value);
	if($tooltip==null){$tooltip='{click_enable_disable}';}
	$tooltip=ParseTooltip($tooltip);
	if($value==null){$value="nokey";}
	if($tooltip<>null){$tooltip="onMouseOver=\"javascript:AffBulle('$tooltip');lightup(this, 100);\" OnMouseOut=\"javascript:HideBulle();lightup(this, 50);\" style=\"filter:alpha(opacity=50);-moz-opacity:0.5;border:0px;\"";}
	if($value=='justkey'){$img='img/status_ok.gif';}else{$img='img/status_critical.gif';}
	$html="
	<input type='hidden' name='$name' id='$name' value='$value'><a href=\"javascript:SwitchKeyOnOff('$name');\"><img src='$img' id='img_$name' $tooltip></a>";
	return $html;
	
}

function KeyPress_enter($javascript_function){return "onkeypress=\"javascript:if(checkEnter(event)){".$javascript_function.";}\"";}

function array_reverse_key($array){
	if(!isset($array)){return $array;}
	while (list ($num, $ligne) = each ($array) ){
		$ret[$ligne]=$num;
	}
	return $ret;
}

function helpCoolapse($datas){
	$key=md5($datas);
	
	$tooltip='{click_to_expand_or_collapse}';
	$tooltip="onMouseOver=\"javascript:AffBulle('$tooltip');lightup(this, 100);\" OnMouseOut=\"javascript:HideBulle();lightup(this, 50);\"";
	$html="
	<input type='hidden' id='{$key}_source' value='$datas'>
	<div style='float:right;margin:5px;cursor:pointer;filter:alpha(opacity=50);-moz-opacity:0.5;border:0px;
	border:1px solid #CCCCCC;padding:3px;margin:3px;background-color:#FFFFFF' OnClick=\"javascript:HelpExpand('$key');\" $tooltip>
		<img id='{$key}_img' src='img/expand.gif' >&nbsp;<strong id='{$key}_text'>expand</strong>
	</div>
	<div id='{$key}_fill' style='width:99%;text-align:justify;font-size:12px;padding:3px;margin:3px;'></div>";
	return $html;
	
	
	
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
	
	if($tooltip<>null){$tooltip="onMouseOver=\"javascript:AffBulle('$tooltip');lightup(this, 100);\" OnMouseOut=\"javascript:HideBulle();lightup(this, 50);\" style=\"filter:alpha(opacity=50);-moz-opacity:0.5;border:0px;\"";}
	
	
	$html="
	<input type='hidden' name='$name' id='$name' value='$value'><a href=\"javascript:SwitchTRUEFALSE('$name');\" ><img src=\"$img\" id='img_$name' $tooltip></a>";
	return $html;
	
}
function Field_TRUEFALSE_checkbox($name,$value,$js=null){
	$value=strtolower(trim($value));
	$def=$value;
	if($js<>null){$js=" OnClick=\"javascript:$js\"";}
if($value=="true"){$value="checked";}else{$value="";}	
$html="<!-- $value: $def -->
<input type=\"checkbox\" id='$name' name=\"$name\" value=\"TRUE\" $value style='padding:0px;margin:0px;border:0px' $js>";
return $html;}


function array_move_element_flat_moveUp($input,$index) {
      $new_array = $input;
     
       if((count($new_array)>$index) && ($index>0)){
                 array_splice($new_array, $index-1, 0, $input[$index]);
                 array_splice($new_array, $index+1, 1);
             }

       return $new_array;
}

function array_move_element_flat_moveDown($input,$index) {
       $new_array = $input;
        
       if(count($new_array)>$index) {
                 array_splice($new_array, $index+2, 0, $input[$index]);
                 array_splice($new_array, $index, 1);
             }
  
       return $new_array;
 }


function array_move_element_flat($array,$index,$direction='up'){
	if($direction=="up"){
		return 	array_move_element_flat_moveUp($array,$index);
	}else{
		return 	array_move_element_flat_moveDown($array,$index);
	}
}


function array_move_element($array, $value, $direction = 'up') {
    $temp = array();
    while (list ($num, $ligne) = each ($array) ){
    	if($ligne==$value){
    		$index=$num;
    		break;
    	}
    }
    reset($array);
    if($direction == 'down'){
    	$index2=$index-1;
    }
    
    if($direction == 'up'){
    	$index2=$index+1;
    }   
    if($index2>count($array)-1){return $array;}
	if($index2<0){return $array;}
    $arrc=new tweak_array();
   	return $arrc->move($array,$index,$index2);
    
    
    if(end($array) == $value && $direction == 'down') {
        return $array;
    }
    if(reset($array) == $value && $direction == 'up') {
        return $array;
    }
	reset($array);
    while ($array_value = current($array)) {
       
        $this_key = key($array);

        if ($array_value == $value) {
            if($direction == 'down') {
                $next_value = next($array);
                $temp[key($array)] = $next_value;
                $temp[$this_key] = $array_value;
            } else {
                $prev_value = prev($array);
                $prev_key = key($array);
                unset($temp[$prev_key]);
                $temp[$this_key] = $array_value;
                $temp[$prev_key] = $prev_value;
                next($array);
                next($array);
            }
            continue;
        } else {
            $temp[$this_key] = $array_value;
        }

        next($array);
    }
    return $temp;
   
}

function element_rollover($js){
	$js=str_replace("javascript:",'',$js);
	$html="
	OnMouseOver=\"this.style.cursor='pointer';this.style.backgroundImage='url(img/bg_jaune-deg.png)';this.style.backgroundRepeat='repeat-y'\"
	OnMouseOut=\"this.style.cursor='auto';this.style.backgroundImage='';\"
	OnClick=\"javascript:$js\"";
	
	return $html;
	
}


function array_move_position($array = '', $from = '', $to = ''){  

		include_once(dirname(__FILE__) . '/class.arrayManip.inc');
		$array_t=new ArrayManip(1);
		writelogs("move an array of {" . count($array) . "} elements from $from to $to",__FUNCTION__,__FILE__);
		return $array_t->moveElement($array,$from,$to);
	
}

function array_insert($array = array(), $position = '' , $elements= ''){
writelogs("insert in position=$position elements ($elements)",__FUNCTION__,__FILE__);
	
	if ($position == '' || (!is_array($array)) || $elements == ''){
		writelogs("error insert in position=$position elements ($elements) in " .count($array) . " array rows",__FUNCTION__,__FILE__);
	}
	else{
		$left = array_slice ($array, 0, $position-1);
		$right = array_slice ($array, $position-1);
		$insert = explode ('\,', $elements);
		$array = array_merge ($left, $insert, $right);
		unset($left, $right, $insert);
		unset ($position, $elements);
		
	}
	return $array; 
} 





function Field_array_Hash($Hash,$FieldName,$default=null,$java=null,$class='',$maxlength=0,$style_of_field=null,$disabled=false){
	if(!is_array($Hash)){return "<input type='hidden' name='$FieldName' value='0' id='$FieldName'>";}
		if($java<>null){$java="OnChange=\"javascript:$java\"";}	
		
if(!preg_match("#font-size#",$style_of_field)){$style_of_field=$style_of_field.";font-size:12px";}
		
	$class="class='$class'  style='$style_of_field'";
	if($disabled){$DISABLED_TOKEN="DISABLED";}
	$html="
	<!--Field_array_Hash value=\"$default\" -->
	<select name=\"$FieldName\" id='$FieldName' $java $class $DISABLED_TOKEN>";	
	reset ($Hash);
	while (list($num,$val)=each($Hash)){	
	$Default_value=$num;
	if($Default_value==$default){$def="selected";}else{$def=null;}
	
	
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

FUNCTION DIV_SHADOW($text,$zoneToKill=null,$color='#FCFCFC',$height=0){
		$tpl=new templates();
		$text=$tpl->_parse_body($text);
		return $text;
		if($color==null){$color='#FCFCFC';}
		$zoneToKill=AJAX_CLOSE($zoneToKill);
		if($height>0){$h="height:" .$height . "px;";}
$html="	
<div id='SHADOW' style=\"position:relative; top:7px; left:7px;background:black;\">
	<div style=\"position:relative;
 		top:-7px; left:-7px;
  		background:$color;border:1px solid #005447;\">
		<div  id='locker' style='padding:0px;
			cursor:pointer;
			background-color:#005447;
			background-image:url(img/barrecroix.gif);
			background-repeat:no-repeat;height:19px;
			padding-right:3px;
			background-position:right;'>
			
		$zoneToKill
		<img src='http://images.kaspersky.fr/vide.gif' height=18 width=90 border=0 align='right'></a>
		</div>
		<div style='margin:4px;$h'>$text</div>
	</div>
</div>";
return $html;
}
FUNCTION AJAX_CLOSE($divname='zone'){
return "<a href='#' OnClick=\"javascript:HideDive('$divname');\">";
	
}
function statusLogs($line){

	$stat=array(
	"daemon started"=>"S",
	"failed to parse line"=>"C",
	"fatal"=>"C",
	"No route to host"=>"W",
	"Connection refused"=>"W",
	"warning"=>"W",
	"KASINFO"=>"K",
	"kav4mailservers/bin/smtpscanner"=>"K",
	"server killed"=>"SS",
	"server pid"=>"ST",
	"process\s+[0-9]+\s+exited"=>"SS",
	"server successfully"=>"SR",
	"server successfully spawned"=>"SR",
	"prefork: child states"=>"ST",
	"server started"=>"S",
	"statistics:"=>"STAT",
	"No ap-mailfiler"=>"SS",
	"KASNOTICE Logs"=>"K",
	"permission denied"=>"PERM",
	"terminating on signal 15"=>"SS",
	"unknown user"=>"UK",
	"Must issue a STARTTLS"=>"BC",
	"Binding to TCP port"=>"S",
	"starting! pid"=>"S",
	"Recipient address rejected"=>"W",
	"status=deferred"=>"W",
	"Server closing!"=>"SS",
	"stopping authdaemond"=>"SS",
	"keepup2date failed"=>"W",
	"socket error"=>"W",
	"timeout after"=>"W",
	"reload configuration"=>"SR",
	"awakened"=>"ST",
	"protocol error"=>"W",
	"bounced"=>"W",
	"couldn't find"=>"W",
	"sleeping"=>"ST",
	"verification error"=>"W",
	"connect to\s+.+failed"=>"C",
	"transaction error"=>"W",
	"mismatch"=>"W",
	"authorization failure"=>"W",
	"\:\: ERROR "=>"C",
	"SASL\(\-13\)"=>"W",
	"archiving log file"=>"AR",
	"archiving database"=>"AR",
	"checkpointing cyrus databases"=>"AR",
	"Ok: queued as "=>"MBX",
	"Delivered:\s+"=>"MBX",
	"postfix\/qmgr.+removed"=>"MBXR",
	"to=<.+?>,\s+relay=procmail,\s+delay"=>"MDA",
	"postfix\/smtpd.+client=.+"=>"NETINFOS",
	"postfix\/smtpd.+\s+connect from.+"=>"NET",
	"postfix\/smtpd.+\s+disconnect from.+"=>"NETSTOP",
	"postfix\/pipe.+to=.+relay=.+status=sent\s+\(delivered via.+"=>"MDA",
	"postfix\/cleanup.+message.+"=>"MAIL",
	"Subject: "=>"MAIL",
	"procmail: Match on"=>"INFO",
	"procmail: No match on"=>"DELETE",
	"Connection timed out"=>"W",
	"Config error in line"=>"W",
	"no such file"=>"W",
	"mailbox does not"=>"W",
	"is restarted"=>"S",
	"Starting slave"=>"S",
	"accepted connection"=>"NET",
	);
	
	while (list ($num, $val) = each ($stat) ){
			if(preg_match('#' . $num . '#i',$line)>0){$img=$val;}
		
	}
	
	switch ($img) {
		case "AR":return 'img/cab_small.gif';break;
		case "BC":return 'img/status_bad_config.jpg';break;
		case "UK":return 'img/status_unknow_user.jpg';break;
		case "PERM":return 'img/status_permission.jpg';break;
		case "STAT":return 'img/status_statistics.jpg';break;
		case "SR":return 'img/status_service_run.jpg';break;
		case "S":return 'img/status_service_ok.jpg';break;
		case "ST":return 'img/status_service_wait.jpg';break;
		case"SS":return 'img/status_service_stop.jpg';break;
		case "C":return 'img/status_critical.jpg';break;
		case "W":return 'img/status_warning.jpg';break;
		case "MBX":return 'img/mailbox_hd.gif';break;
		case "MBXR":return 'img/mailbox_hd_ed.gif';break;
		case "MDA":return 'img/mailbox-2-arrow.gif';break;
		case 'NET':return 'img/tree-network.gif';break;
		case 'NETSTOP':return 'img/tree-network-delete.gif';break;
		case 'NETINFOS':return 'img/tree-network-infos.gif';break;
		case 'MAIL':return 'img/mailbox_storage.gif';break;
		case "INFO":return 'img/icon_info.gif';break;
		case "DELETE":return "img/ed_delete.gif";break;
		case "K":return 'img/k.gif';break;
		default:return 'img/status_ok.jpg';break;

	}
	
}

class template_users{
	var $head;
	var $body;
	var $web_page;
	var $language;
	var $left_menus;
	var $top_menus;
	var $title;
	var $js_add;	
	var $nogbPopup;
	function template_users($title=null,$html=null,$nosession=0,$Popup=0,$nogbPopup=0,$refresh=0,$cfg=array()){
		$currentPage=CurrentPageName();
		if($currentPage=="logon.php"){error_log("$currentPage:template_users()=>".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
		
		
		if($html<>null){
			
			if($currentPage=="logon.php"){error_log("Logon.php=>".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
			$tpl=new templates(null,null,null,1,$array);
			$this->body=$html;
			$this->nogbPopup=$nogbPopup;
			$this->title=$tpl->_ENGINE_parse_body($title,$cfg["LANG_FILE"]);
			
			if($currentPage=="logon.php"){error_log("$currentPage:Title  =>$this->title function ".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
			
			$cfg["refresh"]=$refresh;
			$cfg["TITLE"]=$this->title;
			$this->language=$tpl->_detect_lang();
			if($currentPage=="logon.php"){error_log("$currentPage:language  =>$this->language function ".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
			$this->head="<!-- Artica head template_users  -->\n".$tpl->headers($cfg);
			if($currentPage=="logon.php"){error_log("$currentPage:head  =>" .strlen($this->head). " bytes function ".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
			
			$this->body=$tpl->_ENGINE_parse_body($this->body,$cfg["LANG_FILE"]);		
			if($currentPage=="logon.php"){error_log("$currentPage:Body  =>" .strlen($this->body). " bytes (popup=$Popup) function  ".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
			if($Popup==1){
				$this->_BuildPopUp($html,$title);}else{
					if($currentPage=="logon.php"){error_log("$currentPage:->_BuildBody() function ".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
					$this->_BuildBody();
			}
			
			if($currentPage=="logon.php"){error_log("Logon.php finish =>".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
			$this->web_page=$this->body;
		}
	}
	
	function _BuildPopUp($html,$title){
		$tpl=new templates();
		$html=$tpl->_ENGINE_parse_body($html);
		$title=$tpl->_ENGINE_parse_body($title);
		$bg="background-image:url(img/bg-coins-blc-450.jpg);background-repeat:no-repeat;clear:both;width:453px;height:465px";
		if($this->nogbPopup==1){$bg=null;}
		$this->body="
		<body style='background-image:none;background-color:white;margin-left:10px'>
			<style type=\"text/css\">
	  			html {  background-color: #FFFFFF }
	  		</style>
			<link href='css/styles_main.css' rel=\"styleSheet\" type='text/css' />
			<link href='css/styles_header.css' rel=\"styleSheet\" type='text/css' />
			<link href='css/styles_middle.css' rel=\"styleSheet\" type='text/css' />
		
			
			<link href='css/styles_tables.css' rel=\"styleSheet\" type='text/css' />
			<div style='$bg;padding-right:15px;padding-left:12px;'>
				$html
			</div>
			
			</body>";
			$this->web_page="
			<html>
			<head>
				<title>$title</title>
				{$GLOBALS["ADD_HTML_HEADER"]}
				$this->head
				</head>
				$this->body 
			</html>";
	}
	
	
	function _BuildBody(){
		include_once(dirname(__FILE__) . "/class.users.menus.inc");
		$menus=new usersMenus();
		$cuurentpage=CurrentPageName();
		
		$tpl=new templates();
		$buildtabs=$menus->BuildUsersTabs();
		$top_menus=$tpl->BuildMenusBarr($menus->menus);
		$template_users_menus_h=$menus->template_users_menus_h;
		$template_user_addr=$menus->template_user_addr;
		
		if($cuurentpage=="logon.php"){writelogs("$cuurentpage ->"._FUNCTION__.'/'.__LINE__." file ".__FILE__);}
		if(!isset($_SESSION["uid"])){
			$menus->menus=null;
			$menus->menusright=null;
			$menus->toplinks=null;
			$buildtabs=null;
			$top_menus=null;
		}
			
		if(!isset($_SESSION["template"])){
			$_SESSION["template"]="default";
			
		}
			
		if($cuurentpage=="logon.php"){error_log("_SESSION[\"template\"]={$_SESSION["template"]} function ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}	
		
		$template=file_get_contents(dirname(__FILE__) . "/templates/{$_COOKIE["artica-template"]}/index.html");	

		if($cuurentpage=="logon.php"){error_log("index.html =>". strlen($template)." bytes function ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		
		$template=str_replace("{TEMPLATE_SEARCH}",$tpl->search_menus(),$template);
		if($cuurentpage=="logon.php"){error_log("$cuurentpage:: index.html =>{TEMPLATE_SEARCH} done ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$template=str_replace("{TEMPLATE_HEAD}",$this->head,$template);
		$template=str_replace("{TEMPLATE_LEFT_MENUS}","<div id='TEMPLATE_LEFT_MENUS'></div><script>LoadAjax('TEMPLATE_LEFT_MENUS','admin.tabs.php?left-menus=yes');</script>",$template);
		if($cuurentpage=="logon.php"){error_log("$cuurentpage:: index.html =>{TEMPLATE_LEFT_MENUS} done ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$template=str_replace("{TEMPLATE_TOP_MENUS}",$top_menus,$template);
		$template=str_replace('{TEMPLATE_RIGHT_MENUS}',$tpl->_ENGINE_parse_body($menus->menusright),$template);
		$template=str_replace("{TEMPLATE_CONTENT}",$tpl->_ENGINE_parse_body($this->body),$template);
		$template=str_replace("{TEMPLATE_TOP_LINKS}",$tpl->_ENGINE_parse_body($menus->toplinks),$template);
		$template=str_replace("{TEMPLATE_TITLE_HEAD}",strip_tags($this->title),$template);
		$template=str_replace("{TEMPLATE_TITLE}",$this->title,$template);
		if($cuurentpage=="logon.php"){error_log("$cuurentpage:: index.html =>{TEMPLATE_TITLE} done ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$template=str_replace("{TEMPLATE_USER_MENUS}",$tpl->_ENGINE_parse_body($buildtabs),$template);
		$template=str_replace("{TEMPLATE_USER_MENUS_H}",$tpl->_ENGINE_parse_body($template_users_menus_h),$template);
		$template=str_replace("{ACCOUNT}",$_SESSION["uid"],$template);
		$template=str_replace("{TOOLBOX}",$_GET["TOOLBOX"],$template);
		$template=str_replace("{TEMPLATE_USER_ADDR}",$template_user_addr,$template);
		$template=str_replace("{TEMPLATE_HIGH_MENUS}",$tpl->Build_high_menus(),$template);
		$template=str_replace("{TEMPLATE_BODY_YAHOO}",$tpl->YahooBody(),$template);
		
		if($menus->AsSystemAdministrator==true){
			writelogs("AsSystemAdministrator=\"$menus->AsSystemAdministrator\" = TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$right="" .imgtootltip('rght-cut.gif','{logoff}',"javascript:logoff()")."";
		}else{
			$right="" .imgtootltip('rght-cut.gif','{logoff}',"MyHref('logoff.php')")."";
		}
		if(!isset($_SESSION["uid"])){$right=null;}
		$template=str_replace("{RIGHT}",$right,$template);
		if($cuurentpage=="logon.php"){error_log("$cuurentpage: index.html FINISH=>". strlen($template)." bytes function ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$this->body=$template;
		}
		
		
	
	function _ParsePrivieleges($content=null){
		if($content==null){$content=$_SESSION["privileges"]["ArticaGroupPrivileges"];}
		
			if(preg_match_all('#\[([a-zA-Z]+)\]="([a-z0-9]+)"#is',$content,$reg)){
				while (list ($num, $ligne) = each ($reg[1]) ){
					$priv[$ligne]=$reg[2][$num];
					}}
			return $priv;
	}
	

	
	
}

function is_array_key($key,$array){
		$key=strtolower($key);
		$r=array_change_key_case($array,CASE_LOWER);
		if(array_key_exists($key,$r)){return true;}return false;
}

function  _FileExt($ext){
if(is_file("img/file_ico/$ext.gif")){ return "img/file_ico/$ext.gif";}
if(is_file("img/file_ico/$ext.png")){ return "img/file_ico/$ext.png";}
}

function get_img_ext($value){
	if(preg_match('#\.([a-zA-Z0-9]+)$#',$value,$reg)){
		$ext=$reg[1];
		$img_path=_FileExt($ext);
		if($img_path==null){$img_path="img/file_ico/unknown.gif";}	
		return "<img src=\"$img_path\">";
		
	}else{
	 return '<img src="img/file_ico/unknown.gif">';
	}
}
function Get_extension($filename){
		   	$parts = explode('.',$filename);
   			$last = count($parts) - 1;
   			$ext = $parts[$last];
   			return $ext;
		
		
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
function DirListTable($dir,$extToFind){
$Files = array();
		$It=  @opendir($dir);
		if (! $It){return null;}
		while ($Filename = readdir($It)){
			if ($Filename == '.' || $Filename == '..')
			continue;
			$ext=Get_extension($Filename);
				if($ext<>null){
					if($ext==$extToFind){$Files[]=$Filename;}
				}

		}

		return $Files;
		}
		
function DirFolders($dir){
$Files = array();
		$It=  @opendir($dir);
		if (! $It){return null;}
		while ($Filename = readdir($It)){
			
			if ($Filename == '.' || $Filename == '..')
			continue;
			if(is_dir("$dir/$Filename")){
				$Files[$Filename]=$Filename;
			}

		}

		return $Files;
		}		
		
function CleanEscapes($html){
	$html=str_replace("\n",'',$html);
	$html=str_replace("\t",'',$html);
	$html=str_replace("\r",'',$html);
	return $html;
}

function RoundedWhite($text){
	return "
	<div style='width:100%;padding:-1px;border:#005447 1px solid;'>
	<div class='abl' style='width:100%;background-color:#FFFFFF'>
			<div class='abr' style='background-color:#FFFFFF'>
				<div class='atl' style='background-color:#FFFFFF'>
					<div class='atr' style='padding:3px;'>
						<div class='insides' style='width:99%'>$text</div>
					</div>
				</div>
			</div>
		</div>
		</div>
		<div class='clear'>&nbsp;</div>";
	
}

function RoundedGrey($text){
	return "<div class='bl'>
			<div class='br'>
				<div class='tl'>
					<div class='tr'>
						<div class='insides'>$text</div>
					</div>
				</div>
			</div>
		</div>
		<div class='clear'>&nbsp;</div>";
	
}

function RoundedLightGrey($text,$url=null,$asrolover=0){
	
	$id=md5($text);
	
	
	if($url<>null){
		$add="this.style.cursor='pointer';";
		$add1="this.style.cursor='auto'";
		if(preg_match('#javascript#i',$url)){
			$url="OnClick=\"$url\"";
		}else{$url="OnClick=\"javascript:MyHref('$url');\"";}
	}
	
	if($asrolover==1){
		$ls="OnMouseOver=\"javascript:rol1_('$id');$add\" OnMouseOut=\"javascript:rol0_('$id');$add1\"" . " $url";	
	}else{
		if($url<>null){
			$ls="OnMouseOver=\"javascript:$add\" OnMouseOut=\"javascript:$add1\"" . " $url";	
			
		}
	}
	

	
return "
<div $ls>
  <b class=\"RLightGrey\" id='$id'>
  <b class=\"RLightGrey1\" id ='$id" . "_1'><b></b></b>
  <b class=\"RLightGrey2\" id ='$id" . "_2'><b></b></b>
  <b class=\"RLightGrey3\" id ='$id" . "_3'></b>
  <b class=\"RLightGrey4\" id ='$id" . "_4'></b>
  <b class=\"RLightGrey5\" id ='$id" . "_5'></b></b>

  <div class=\"RLightGreyfg\" style='padding:8px;'  id ='$id" . "_11'>
   			$left$text
  </div>

  <b class=\"RLightGrey\" id ='$id" . "_0'>
  <b class=\"RLightGrey5\" id ='$id" . "_6'></b>
  <b class=\"RLightGrey4\" id ='$id" . "_7'></b>
  <b class=\"RLightGrey3\" id ='$id" . "_8'></b>
  <b class=\"RLightGrey2\" id ='$id" . "_9'><b></b></b>
  <b class=\"RLightGrey1\" id ='$id" . "_10'><b></b></b></b>
</div>
";	
	
}

function RoundedLightGreen($text){
	
return "<div>
  <b class=\"RLightGreen\">
  <b class=\"RLightGreen1\"><b></b></b>
  <b class=\"RLightGreen2\"><b></b></b>
  <b class=\"RLightGreen3\"></b>
  <b class=\"RLightGreen4\"></b>
  <b class=\"RLightGreen5\"></b></b>

  <div class=\"RLightGreenfg\" style='padding:7px;'>
   $text
  </div>

  <b class=\"RLightGreen\">
  <b class=\"RLightGreen5\"></b>
  <b class=\"RLightGreen4\"></b>
  <b class=\"RLightGreen3\"></b>
  <b class=\"RLightGreen2\"><b></b></b>
  <b class=\"RLightGreen1\"><b></b></b></b>
</div>
";	
}
function RoundedLightYellow($text){
	
return "<div>
  <b class=\"RLightyellow\">
  <b class=\"RLightyellow1\"><b></b></b>
  <b class=\"RLightyellow2\"><b></b></b>
  <b class=\"RLightyellow3\"></b>
  <b class=\"RLightyellow4\"></b>
  <b class=\"RLightyellow5\"></b></b>

  <div class=\"RLightyellowfg\" style='padding:7px;'>
   $text
  </div>

  <b class=\"RLightyellow\">
  <b class=\"RLightyellow5\"></b>
  <b class=\"RLightyellow4\"></b>
  <b class=\"RLightyellow3\"></b>
  <b class=\"RLightyellow2\"><b></b></b>
  <b class=\"RLightyellow1\"><b></b></b></b>
</div>
";	
}
function RoundedLightBlue($text){
	
return "<div>
  <b class=\"RLightBlue\">
  <b class=\"RLightBlue1\"><b></b></b>
  <b class=\"RLightBlue2\"><b></b></b>
  <b class=\"RLightBlue3\"></b>
  <b class=\"RLightBlue4\"></b>
  <b class=\"RLightBlue5\"></b></b>

  <div class=\"RLightBluefg\" style='padding:7px;'>
   $text
  </div>

  <b class=\"RLightBlue\">
  <b class=\"RLightBlue5\"></b>
  <b class=\"RLightBlue4\"></b>
  <b class=\"RLightBlue3\"></b>
  <b class=\"RLightBlue2\"><b></b></b>
  <b class=\"RLightBlue1\"><b></b></b></b>
</div>
";	
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
function RoundedBlack($text){
return "<div>
  <b class=\"RLightBlack\">
  <b class=\"RLightBlack1\"><b></b></b>
  <b class=\"RLightBlack2\"><b></b></b>
  <b class=\"RLightBlack3\"></b>
  <b class=\"RLightBlack4\"></b>
  <b class=\"RLightBlack5\"></b></b>

  <div class=\"RLightBlackfg\"style='padding:5px;'>
   $text
  </div>

  <b class=\"RLightBlack\">
  <b class=\"RLightBlack5\"></b>
  <b class=\"RLightBlack4\"></b>
  <b class=\"RLightBlack3\"></b>
  <b class=\"RLightBlack2\"><b></b></b>
  <b class=\"RLightBlack1\"><b></b></b></b>
</div>";
}

function my_mkdir($dir, $chmod = 0777){
		if (!empty($dir)) {
			$array = explode('/', $dir);
			$base_dir = '';

			foreach ($array as $key => $value) {
				if ($key){
					$base_dir .= '/' . $value;
					$base_dir=str_replace("\\","",$base_dir);
					//WriteLogs("Fichiers:my_mkdir:base_dir=$base_dir");
				}
				else{
					$base_dir .= $value;
				}

		
				if (!file_exists($base_dir) && !empty($value))
				if (!mkdir($base_dir, $chmod))
				echo 'Unable to create folder "' . $base_dir . '"<br />'; }

		}
	}
	

function Paragraphe_switch_disable($title,$text,$tooltip=null,$width="220"){	
$id=md5('Paragraphe_switch_img'.$field_name.$text.$title);
	$hrf="<a href='$link' class='nostylea'>";

	if($tooltip==null){$tooltip='{click_enable_disable}';}
	$tooltip=ParseTooltip($tooltip);
	if($tooltip<>null){$tooltip="
	onMouseOver=\"javascript:AffBulle('$tooltip');lightup(this, 100);\" 
	OnMouseOut=\"javascript:HideBulle();lightup(this, 50);\" 
	style=\"filter:alpha(opacity=50);-moz-opacity:0.5;border:0px;\"";}
	
	
	
	
	
	
	
	
	$img="<img src='img/64-grey.png' id='img_$field_name' $tooltip>";
	
	
	
	$html="<div style='width:{$width}px;' >
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top' style='background-image:url(img/bg_pic-2.png);background-position:top center;background-repeat:no-repeat'><div style='min-height: 120px;'>$img</a></div></td>
	<td valign='top' id='$id'><h3 style='font-size:16px'>$title</h3>
	<p class='legend' style='text-align:left' id='text_$id'>$text</p>
	</td>
	</tr>
	</table>
	
	</div>";
	return RoundedLightWhite($html);	
	
}
	
function Paragraphe_switch_img_32($title,$text,$field_name=null,$value=null,$tips=null,$width="120"){
	$text_key=$text;
	$id=md5('Paragraphe_switch_img'.$field_name.$text.$title);
	$hrf="<a href='$link' class='nostylea'>";

	if(trim($tooltip)==null){$tooltip='{click_enable_disable}';}
	$tooltip=ParseTooltip($tooltip);
	if($value==null){$value="0";}
	if($tooltip<>null){$tooltip="
	onMouseOver=\"javascript:AffBulle('$tooltip');lightup(this,100);\" 
	OnMouseOut=\"javascript:HideBulle();lightup(this, 50);\" 
	style=\"filter:alpha(opacity=50);-moz-opacity:0.5;border:0px;\"";}
	
	if(strpos($width,"%")>0){$width="width:{$width};";}else{$width="width:{$width}px;";}
		
	
	
	if($value=='1'){$img='img/ok32.png';}else{$img='img/danger32.png';}
	$field="<input type='hidden' name='$field_name' id='$field_name' value='$value'>";
	
	
	$tpl=new templates();
	$text=$tpl->_ENGINE_parse_body($text);
	if(strlen($text)>60){$text="<table style='width:100%'>
	<tr>
	<td valign='top' width=99% style='text-align:left;font-size:11px'>" . substr($text,0,60)."...</td>
	<td valign='top' width=1%>" . help_icon($text_key)."</td>
	</tr>
	</table>";
	}
	
	
	$img="<a href=\"javascript:Switch32Numeric('$field_name');\">
			<img src='$img' id='img_$field_name' 
			$tooltip
			>
		</a>";
	
	
	
	$html="<div style='{$width}' >
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'>$img</a></td>
	<td valign='top' id='$id'><h3>$title</h3>
	<div style='text-align:left;font-size:11px' id='text_$id'>$field$text</div>
	</td>
	</tr>
	</table>
	
	</div>";
	return "<div style='margin:4px'>".RoundedLightWhite($html)."</div>";
	
	
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
	return RoundedLightWhite($html);
	
	
}
function ParagrapheSimple($img,$title,$text,$link=null,$tips="go_to_section",$width="210",$height=null){
	
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body($title);
	$text=$tpl->_ENGINE_parse_body($text);	
	$tips=str_replace("'","",$tips);
	$tips=str_replace("	","",$tips);
	$height=null;
	if($height==null){$height="min-height:70px;";}

	
	$id=md5($img.$text.$title.$link);

	
	if($tips<>null){
		$tips=str_replace('{','',$tips);
		$tips=str_replace('}','',$tips);
		}

		
	if (strpos($link,'script:')){
		$jslink="$link";
		$jslink=str_replace("javascript:",'',$jslink);
	}else{$jslink="MyHref('$link');";}
	
	$jslink=str_replace('"',"'",$jslink);
	$tooltip=imgtootltip($img,"{$tips}",null,null,"img_$id");
	

	
		$lightup1="lightup(document.getElementById('img_$id'), 100);";
		$lightup2="lightup(document.getElementById('img_$id'), 50);";	
	
	if(($tips<>null)&& ($link<>null)){
		$js_tips=ParseTooltip($tips);
		$tooltip_over="AffBulle('{{$js_tips}}');";
		$tooltip_out="HideBulle()";}
	
	$h3style="
		OnMouseOver=\"javascript:this.className='paragraphe_over';this.style.cursor='pointer';$lightup1;$tooltip_over\"
		OnMouseOut=\"javascript:this.className='paragraphe';this.style.cursor='default';$lightup2;$tooltip_out\"
		OnClick=\"javascript:$jslink\"";
	
		if($link==null){$hrf=null;$h3style=null;$tooltip="<img src='img/$img'>";}


	$min_height="min-height:70px";
	if(is_numeric($height)){
	if($height<70){
		$min_height=null;
		$min_height_title=null;
	}}
		
	
	$title="<h3>$title</h3>";
		$text="<div id='text_$id' style='$min_height;font-size:11px'>$text</div>";
		
	
	if(trim($height)<>null){$height=";height:{$height}px";}
	if($nojs){
		$bigA="<a href=\"$link\" target=_new>";
		$bigZ="</a>";
	}
	
	$html="
	<div style='width:{$width}px$height$styleFloat' class='paragraphe' id='$id' $h3style>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'>$bigA$hrf$tooltip</a></td>
	<td valign='top' >$title$text</td>
	</tr>
	</table>
	</div>
	
	";
	return $html;
	
	
}	

function Buildicon64($ICON_KEY,$width=210,$height=null,$param=null){
	include_once(dirname(__FILE__)."/class.icons.inc");
	$tpl=new templates();
	$icon=new deficons();
	if(!is_array($icon->array_icons[$ICON_KEY])){return null;}
	
	if($param<>null){
		$icon->array_icons[$ICON_KEY]["JS"]=str_replace("')","$param')",$icon->array_icons[$ICON_KEY]["JS"]);
	}
	
	$p=Paragraphe($icon->array_icons[$ICON_KEY]["IMG"],"{{$icon->array_icons[$ICON_KEY]["TITLE"]}}",
	"{{$icon->array_icons[$ICON_KEY]["TEXT"]}}",
	"javascript:".$icon->array_icons[$ICON_KEY]["JS"],"{{$icon->array_icons[$ICON_KEY]["TEXT"]}}",$width,$height);
	if(posix_getuid()==0){return $p;}
	return $tpl->_ENGINE_parse_body($p,$icon->array_icons[$ICON_KEY]["LANG"]);
	
		
}	


function Paragraphe32($title,$text,$js,$img,$size=220){
	
	if(preg_match("#noacco:(.+)#",$title,$re)){
		$title=$re[1];
		
	}else{
	$text="{{$text}}";
	$title="{{$title}}";
	}
	
		$id=md5($js);
		$img_id="{$id}_img";
	$html="
	<table style='width:198px;'>
	<tr>
	<td width=1% valign='top'>" . imgtootltip($img,"{{$text}}","$js",null,$img_id)."</td>
	<td><strong style='font-size:12px'>$title</strong><div style='font-size:11px'>$text</div><!-- replace --></td>
	</tr>
	</table>";
	

return "<div style=\"width:{$size}px;margin:2px\" 
	OnMouseOver=\"javascript:ParagrapheWhiteToYellow('$id',0);this.style.cursor='pointer';\" 
	OnMouseOut=\"javascript:ParagrapheWhiteToYellow('$id',1);this.style.cursor='auto'\" OnClick=\"javascript:$js\">
  <b id='{$id}_1' class=\"RLightWhite\">
  <b id='{$id}_2' class=\"RLightWhite1\"><b></b></b>
  <b id='{$id}_3' class=\"RLightWhite2\"><b></b></b>
  <b id='{$id}_4' class=\"RLightWhite3\"></b>
  <b id='{$id}_5' class=\"RLightWhite4\"></b>
  <b id='{$id}_6' class=\"RLightWhite5\"></b></b>

  <div id='{$id}_0' class=\"RLightWhitefg\" style='padding:2px;'>
   $html
  </div>

  <b id='{$id}_7' class=\"RLightWhite\">
  <b id='{$id}_8' class=\"RLightWhite5\"></b>
  <b id='{$id}_9' class=\"RLightWhite4\"></b>
  <b id='{$id}_10' class=\"RLightWhite3\"></b>
  <b id='{$id}_11' class=\"RLightWhite2\"><b></b></b>
  <b id='{$id}_12' class=\"RLightWhite1\"><b></b></b></b>
</div>
";		
		
	
}
function hide_ressources($pattern){
	if(preg_match("#smb://(.+?):(.+?)@#",$pattern,$re)){
		return str_replace($re[2],'*****',$pattern);
	}
	return $pattern;
}


	
function Paragraphe($img,$title,$text,$link=null,$tips="go_to_section",$width="210",$height=null,$nowrap=0,$float=false,$nojs=false){
	
	
	if(is_array($img)){
		$img_add_1=$img[1];
		$img_add_2=$img[2];
		$img=$img[0];
	}
	if(posix_getuid()==0){$GLOBALS["EXECUTED_AS_ROOT"]=true;}
	$tpl=new templates();
	$tips=$text;
	
	if(!$GLOBALS["EXECUTED_AS_ROOT"]){
		$title=$tpl->_ENGINE_parse_body($title);
		$text=$tpl->_ENGINE_parse_body($text);	
	}
	$tips=str_replace("'","",$tips);
	$tips=str_replace("	","",$tips);
	$height=null;
	if($height==null){$height="min-height:70px;";}

	
	$id=md5($img.$text.$title.$link);
	
	if(preg_match('#new:#',$link)){
		$link=str_replace('new:','',$link);
		$link="javascript:s_PopUpFull('$link',800,600);";
	}
	
	
	
	
	if(!$GLOBALS["EXECUTED_AS_ROOT"]){
		$tips=$tpl->_ENGINE_parse_body($tips);
		}
		
if(strlen($title)>30){$tips="<b>$title</b><br>$tips";}
	
	if (strpos($link,'script:')){
		$jslink="$link";
		$jslink=str_replace("javascript:",'',$jslink);
	}else{$jslink="MyHref('$link');";}
	
	$jslink=str_replace('"',"'",$jslink);
	
	if(($tips<>null)&& ($link<>null)){
		$js_tips=ParseTooltip($tips);
		$tooltip_over="AffBulle('{$js_tips}');";
		$tooltip_out="HideBulle()";
		}
		
	$js_tips_title=	$js_tips;
	$js_tips_title=str_replace("\n"," ",$js_tips_title);
	$js_tips_title=strip_tags($js_tips);
	$js_tips_title=str_replace("<br>"," ",$js_tips_title);
	$h3style="OnClick=\"javascript:$jslink\"";
	

		
		if($img<>null){
			if(preg_match("#\/#",$img)){$img_p="<img src='$img'>";}else{$img_p="<img src='img/$img'>";}
		}
		if($link==null){$hrf=null;$h3style=null;}

	
	$min_height="height:70px";
	$min_height_title="height:36px;";
	
	$tbl=explode("\n",$text);
	if(is_array($tbl)){
		$text=null;
	while (list ($num, $ligne) = each ($tbl) ){
		$ligne=str_replace("  "," ",$ligne);
		$ligne=str_replace("\r","",$ligne);
		if(trim($ligne)==null){continue;}
		
		$text=$text.$ligne."<br>";
	}
	
	}
		
	$max_caracts=30;
	$max_text_caracts=83;
	if($width>290){
		$max_caracts=40;
		$max_text_caracts=130;
	}	
	
	$title_entity_decode=html_entity_decode($title);

	if(trim($title)<>null){
			if(strlen($title_entity_decode)>$max_caracts){
			if(!$GLOBALS["EXECUTED_AS_ROOT"]){
				if($nowrap==0){$title_entity_decode=substr($title_entity_decode,0,($max_caracts-3))."...";}
				$title=htmlentities($title_entity_decode);
			}
			
			
		}		
		
		$title="<h3 style='$min_height_title'>$title</h3>";

		if($nowrap==0){
			if(strlen($text)>$max_text_caracts){
				$text=substr($text,0,($max_text_caracts-3))."...";
				$img_p=imgtootltip($img,null,null,null,"img_$id");
				
			
			}else{
				$tooltip_over=null;
				$tooltip_out=null;
			}
		}
		
		$text="<div id='text_$id' style='$min_height;font-size:11px'>$text</div>";
		}else{
			$text="<span id='text_$id'>$text</span>";
		}
	
	$height=null;
	if(trim($height)<>null){$height=";height:{$height}px";}
	
	if($float){$styleFloat=";float:left;";}
	

	
	$text=trim($text);
	if($nojs){
		$bigA="<a href=\"$link\" target=_new>";
		$bigZ="</a>";
	}
	
	$text=str_replace("<br/>"," ",$text);
	$text=str_replace("<br />"," ",$text);
	$text=str_replace("<br>"," ",$text);

	$OnMouseOver="OnMouseOver=\"javascript:this.className='paragraphe_over';this.style.cursor='pointer';\"";
	$OnMouseOut="OnMouseOut=\"javascript:this.className='paragraphe';this.style.cursor='default';\"";
	
	$OnMouseOverToolTip="OnMouseOver=\"$tooltip_over\"";
	$OnMouseOutToolTip="OnMouseOut=\"$tooltip_out\"";
	
	$html="
	<div style='width:{$width}px$height$styleFloat' class='paragraphe' id='$id' $OnMouseOver $OnMouseOut>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><div $h3style $OnMouseOverToolTip $OnMouseOutToolTip>$bigA$hrf$img_p</a></div>$img_add_1$img_add_2</td>
	<td valign='top' ><div $h3style $OnMouseOverToolTip $OnMouseOutToolTip>$title$text</div></td>
	</tr>
	</table>
	</div>
	
	";
	return $html;
	
	
}
function ip2cidr($ip_start,$ip_end) {
	if(long2ip(ip2long($ip_start))!=$ip_start or long2ip(ip2long($ip_end))!=$ip_end) return NULL;
	$ipl_start=(int)ip2long($ip_start);
	$ipl_end=(int)ip2long($ip_end);
	if($ipl_start>0 && $ipl_end<0) $delta=($ipl_end+4294967296)-$ipl_start;
	else $delta=$ipl_end-$ipl_start;
	$netmask=str_pad(decbin($delta),32,"0","STR_PAD_LEFT");
	if(ip2long($ip_start)==0 && substr_count($netmask,"1")==32) return "0.0.0.0/0";
	if($delta<0 or ($delta>0 && $delta%2==0)) return NULL;
	for($mask=0;$mask<32;$mask++) if($netmask[$mask]==1) break;
	if(substr_count($netmask,"0")!=$mask) return NULL;
	return "$ip_start/$mask";
}

function ae_detect_ie(){
	//return false;
    if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)){return true;}
	
        
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
		mode : \"textareas\",
		theme : \"advanced\",
		skin : \"o2k7\",
		skin_variant : \"silver\",
		plugins : \"safari,pagebreak,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups\",
		theme_advanced_buttons1 : \"save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect\",
		theme_advanced_buttons2 : \"cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor\",
		theme_advanced_buttons3 : \"tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen\",
		theme_advanced_buttons4 : \"insertlayer,moveforward,movebackward,absolute,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak\",
		theme_advanced_toolbar_location : \"top\",
		theme_advanced_toolbar_align : \"left\",
		theme_advanced_statusbar_location : \"bottom\",
		theme_advanced_resizing : true,
	});\n";	
	
}

function applysettings($module_name,$js=null){
	$mod=$module_name;
	if($js==null){$js="ApplyConfig('$module_name')";}
	if($mod=='fetch'){$mod='fetchmail';}
	$html="<table style='width:100%'>
	<tr>
		<td valign='top'>" . imgtootltip('system-64.png','{apply_settings_text}',"$js")."</td>
		<td valign='top'><H5>{apply config} ($mod)</H5>{apply config text}</td>
	</tr>
	</table>
	
	";
	$tpl=new templates();
	
	return $tpl->_ENGINE_parse_body(RoundedLightGrey($html)) . "<br>";
	
}
function applysettings_dansguardian($float=false){
	
	return Paragraphe('system-64.png','{apply config}','{APPLY_SETTINGS_DANSGUARDIAN}','javascript:applysettings_dansguardian()','APPLY_SETTINGS_DANSGUARDIAN',210,null,0,$float);
}

function applysettings_miltergreylist($float=false){
	
	return Paragraphe('system-64.png','{apply config}','{APPLY_SETTINGS_MILTERGREYLIST}','javascript:applysettings_miltergreylist()','APPLY_SETTINGS_MILTERGREYLIST',210,null,0,$float);
}

function applysettingsGeneral($title,$java,$text,$noparse=false){
	$mod=$module_name;
	
	$html="<table style='width:100%'>
	<tr>
		<td valign='top' width=1%>" . imgtootltip('system-64.png',"{".$text."}",$java)."</td>
		<td valign='top'><H5>{".$title."}</H5>{".$text."}</td>
	</tr>
	</table>
	
	";
	if(!$noparse){
		$tpl=new templates();
		return $tpl->_ENGINE_parse_body(RoundedLightWhite($html)) . "<br>";
	}else{
		return RoundedLightWhite($html) . "<br>";
	}
	
}

function applysettings_postfix($float=false){
	
	return Paragraphe('system-64.png','{apply config}','{APPLY_SETTINGS_POSTFIX}','javascript:Default_ApplyConfigPostfix()','APPLY_SETTINGS_POSTFIX',210,null,0,$float);
}
function applysettings_fetchmail($float=false){
	
	return Paragraphe('system-64.png','{apply config}','{apply_settings_text}','javascript:ApplyConfig(\'fetchmail\')','apply_settings_text',210,null,0,$float);
}

function iframe($content,$refresh=20,$bodywidth=null){
	if($bodywidth<>null){$bodywidth="width:$bodywidth";}else{$bodywidth="width:auto";}
	if($refresh>0){$refresh="<META HTTP-EQUIV='REFRESH' CONTENT='$refresh'>";}else{$refresh=null;}
	$tpl=new templates();
	
		$html="<html style='$bodywidth;margin:0px;padding:0px'>
		
		<head>$tpl->head
			<link href='css/styles_main.css' rel=\"styleSheet\" type='text/css' />
			<link href='css/styles_header.css' rel=\"styleSheet\" type='text/css' />
			<link href='css/styles_middle.css' rel=\"styleSheet\" type='text/css' />
			<link href='css/styles_tables.css' rel=\"styleSheet\" type='text/css' />
			$refresh
		</head><body style='margin:0px;padding:0px;background:transparent;$bodywidth'>$content</body></html>";	
	
	return  $html;	
	
}


function BuildSessionAuth(){
	error_log("BuildSessionAuth.. in".__FUNCTION__." line ".__LINE__);
	if(!isset($_SERVER['PHP_AUTH_USER'])){return false;}
	$ldap=new clladp();
	$hash=$ldap->UserFromAuth($_SERVER['PHP_AUTH_USER']);
	if($hash[0]["uid"][0]==null){$hash[0]["uid"][0]=$hash[0]["cn"][0];}
	$uid=$hash[0]["uid"][0];
	if(trim(strtolower($uid))==trim(strtolower($ldap->ldap_admin))){
		if($_SERVER["PHP_AUTH_PW"]==$ldap->ldap_password){	
		$uid="-100";
		$_SESSION["passwd"]=$_SERVER["PHP_AUTH_PW"];
		}
	}
	if(trim($uid)<>null){
		$_SESSION["uid"]=$uid;
		$_SESSION["passwd"]=$_SERVER["PHP_AUTH_PW"];
		return true;
	}
	
	}
	
function DefineSessions(){

$Lifetime = 3600;
$Seperator = (strstr(strtoupper(substr(PHP_OS, 0, 3)), "WIN")) ? "\\" : "/";
$DirectoryPath = dirname(__FILE__) . "/sessions{$Seperator}SessionData";
is_dir($DirectoryPath) or mkdir($DirectoryPath, 0777);

if (ini_get("session.use_trans_sid") == true) {
    ini_set("url_rewriter.tags", "");
    ini_set("session.use_trans_sid", false);

}

ini_set("session.gc_maxlifetime", $Lifetime);
ini_set("session.gc_divisor", "1");
ini_set("session.gc_probability", "1");
ini_set("session.cookie_lifetime", "0");
ini_set("session.save_path", $DirectoryPath);		
}
	
function CheckSessions(){
		$page=CurrentPageName();
		

		if($currentPage=="logon.php"){error_log("$currentPage:CheckSessions()=>".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
		if($currentPage=="exec.squidguard.php"){error_log("$currentPage:CheckSessions()=>".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
		
		if($_SESSION["uid"]==null){
		if(isset($_GET["session"])){
			include_once(dirname(__FILE__).'/class.user.inc');	
			$users=new user($_GET["uid"]);
			if(md5($users->password)==$_GET["session"]){
				writelogs("Creating new session for uid={$_GET["uid"]}",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$_SESSION["uid"]=$_GET["uid"];
				return true;
			}
		}}	
		$noback["cron.obm.synchro.php"]=true;
		$noback["logon.php"]=true;
		$noback["obm.export.php"]=true;
		$noback["auto-account.php"]=true;
		$noback["exec.scan-networks.php"]=true;
		$noback["exec.vacationtime.php"]=true;
		$noback["dansguardian.users.index.php"]=true;
		$noback["cyrus.murder.listener.php"]=true;
		$noback["cluster.listener.php"]=true;
		$noback["exec.squidguard.php"]=true;
		
		if(posix_getuid()==0){return true;}
		
		if(!class_exists("usersMenus")){
			writelogs("Unable to stat class usersMenus include ".dirname(__FILE__)."/class.users.menus.inc",__CLASS__.'/'.__FUNCTION__,__FILE__);
			include_once(dirname(__FILE__)."/class.users.menus.inc");
		}
		
		if(preg_match('#^exec\..+\.php$#',$page)){return true;}
		if(preg_match('#listener\.php$#',$page)){return true;}
		
		if($noback[CurrentPageName()]){return true;}

		
		$currentdir=basename(dirname($_SERVER['SCRIPT_FILENAME']));
		if($currentdir<>'smartsieve'){
			//session END --------------------------------------------
			if(!isset($_SESSION["uid"])){
				if(!$noback[$page]){
					$user=new usersMenus();
					if($user->LIGHTTPD_LDAP_AUTH){BuildSessionAuth();}
				}
			}
		
			if(!isset($_SESSION["uid"])){
					if(!$noback[$page]){
						include_once(dirname(__FILE__).'/logs.inc');
						error_log("$page is not allowed, no session, die.. in ".__FILE__." line ". __LINE__);
						
						echo "
						<html>
						<head>
						<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; URL=logoff.php\"> 
						</head>
						<body>
						<script>document.location.href='logoff.php';</script>
						</body>
						</html>";die();
						
						echo header('location:logoff.php');die();
					
						}
				
					
			}}

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



class filesClass{
	
		function FindFileInFolder($dir,$extension){
		$f=new filesClass();
		$array=$f->DirListTable($dir,true);
		while (list($num,$val)=each($array)){
			if($f->Get_extension($val)==$extension){return $val;}
		}
	}
	
	function Get_extension($filename){
		   	$parts = explode('.',$filename);
   			$last = count($parts) - 1;
   			$ext = $parts[$last];
   			return $ext;
		
		
		}
		
			
	function DirListTable($dir,$onlyfiles=false){
		$Files = array();
		$It=  @opendir($dir);
		if (! $It){return null;}
		while ($Filename = readdir($It)){
			if ($Filename == '.' || $Filename == '..')
			continue;
			
			if($onlyfiles==true){
				$ext=$this->Get_extension($Filename);
				if($ext<>null)
					$Files[]=$Filename;
				}
			else{$Files[]=$Filename;}
		}

		return $Files;
	}
	
	
	function isMounted($mountpoint){
		$mountpoint=str_replace('/','\/',$mountpoint);
		$mountpoint=str_replace('.','\.',$mountpoint);
		$datas=explode("\n",file_get_contents('/proc/mounts'));
		
		if(!is_array($datas)){return false;}
		while (list($num,$line)=each($datas)){
			if(preg_match("#\s+$mountpoint#",$line)){
				return true;
			}else{
				
			}
			
		}
		
		
	}
	
	
}


function browserTree($target_field){
	return "
		<div style='width:100%;text-align:right'>
				<input type='button' OnClick=\"javascript:Loadjs('TreeBrowse.php?t=$target_field');\" value='{browse}...&nbsp;&raquo;'>
			</div>
		";
	
}

function DAEMON_STATUS_LINE($key,$bsini,$textoadd=null,$noenable=0){
if($bsini->_params["$key"]["service_name"]==null){return null;}
if($bsini->_params["$key"]["service_disabled"]==null){$bsini->_params["$key"]["service_disabled"]=-1;}
if($noenable==1){if($bsini->_params["$key"]["service_disabled"]==0){return null;}}
$service_cmd=$bsini->_params["$key"]["service_cmd"];
$monit=$bsini->_params["$key"]["monit"];
$app=$bsini->_params["$key"]["service_name"];


if($monit==1){
if(!isset($GLOBALS["MONIT_ARRAY"])){
		$sock=new sockets();
		$GLOBALS["MONIT_ARRAY"]=unserialize(base64_decode($sock->getFrameWork("cmd.php?monit-status=yes")));
	}
	
	
	
		$monit_status=$GLOBALS["MONIT_ARRAY"][$app]["status"];
		$monit_uptime=$GLOBALS["MONIT_ARRAY"][$app]["uptime"];
		$bsini->_params["$key"]["master_memory"]=$GLOBALS["MONIT_ARRAY"][$app]["memory kilobytes total"];
		$memory_percent_total=$GLOBALS["MONIT_ARRAY"][$app]["memory percent total"];
		$cpu_percent_total=$GLOBALS["MONIT_ARRAY"][$app]["cpu percent total"];
		$application_installed=1;
		$bsini->_params["$key"]["master_pid"]=$GLOBALS["MONIT_ARRAY"][$app]["pid"];
		$uptime=$GLOBALS["MONIT_ARRAY"][$app]["uptime"];
		$service_disabled=0;
		$application_installed=1;
		
		switch ($monit_status) {
			case "monitored":$bsini->_params[$key]["running"]=1;$status="sleeping";break;
			case "not monitored":$bsini->_params[$key]["running"]=0;$status="stopped";break;
			default:
				;
			break;
		}	
}

if($bsini->_params[$key]["running"]==0){
		$img="danger16.png";
		$status="{stopped}";
		$js_service="Loadjs('StartStopServices.php?APP={$bsini->_params["$key"]["service_name"]}&cmd=$service_cmd&action=start')";
		if($bsini->_params[$key]["service_croned"]==1){
			$img="ok16.png";
			$status="Croned";
		}
	}else{
		$js_service="Loadjs('StartStopServices.php?APP={$bsini->_params["$key"]["service_name"]}&cmd=$service_cmd&action=stop')";
		$img="ok16.png";
		$status="running";
		
	}	

	
return "<table style='width:100%;margin:0px;' " .CellRollOver($js_service).">
		<tr>
			<td width=1%><img src='img/$img'></td>
			<td align='left' nowrap><strong>{{$bsini->_params["$key"]["service_name"]}}:</td>
			<td width=1% align='right'><strong><span style='font-size:10px'>$status</span>$pid_text</strong></td>
		</tr>
	</table>";	
	
}


function DAEMON_STATUS_ROUND($key,$bsini,$textoadd=null,$noenable=0){
if($bsini->_params["$key"]["service_name"]==null){return null;}
if($bsini->_params["$key"]["service_disabled"]==null){$bsini->_params["$key"]["service_disabled"]=-1;}
$app=$bsini->_params["$key"]["service_name"];
$monit=$bsini->_params["$key"]["monit"];

if($monit==1){
if(!isset($GLOBALS["MONIT_ARRAY"])){
		$sock=new sockets();
		$GLOBALS["MONIT_ARRAY"]=unserialize(base64_decode($sock->getFrameWork("cmd.php?monit-status=yes")));
	}
		$monit_status=$GLOBALS["MONIT_ARRAY"][$app]["status"];
		$monit_uptime=$GLOBALS["MONIT_ARRAY"][$app]["uptime"];
		$bsini->_params["$key"]["master_memory"]=$GLOBALS["MONIT_ARRAY"][$app]["memory kilobytes total"];
		$memory_percent_total=$GLOBALS["MONIT_ARRAY"][$app]["memory percent total"];
		$cpu_percent_total=$GLOBALS["MONIT_ARRAY"][$app]["cpu percent total"];
		$application_installed=1;
		$bsini->_params["$key"]["master_pid"]=$GLOBALS["MONIT_ARRAY"][$app]["pid"];
		$uptime=$GLOBALS["MONIT_ARRAY"][$app]["uptime"];
		$service_disabled=0;
		$application_installed=1;
		
		switch ($monit_status) {
			case "monitored":$bsini->_params[$key]["running"]=1;$status="sleeping";break;
			case "not monitored":$bsini->_params[$key]["running"]=0;$status="stopped";break;
			default:
				;
			break;
		}	
}


if($noenable==1){if($bsini->_params["$key"]["service_disabled"]==0){return null;}}
$tpl=new templates();
$prod=$bsini->_params[$key]["service_name"];
$title=$tpl->_ENGINE_parse_body("{{$prod}}");


if($bsini->_params[$key]["running"]==0){
		$img="42-red.png";
		$status="{stopped}";
		$js_service="javascript:StartStopService('".$bsini->_params["$key"]["service_cmd"]."','1','$title');";
		if($bsini->_params[$key]["service_croned"]==1){
			$img="42-green.png";
			$status="Croned";
		}
	}else{
		$img="42-green.png";
		$status="running";
		$js_service="StartStopService('".$bsini->_params["$key"]["service_cmd"]."','0','$title');";
		
	}	

	
	if($bsini->_params["$key"]["running"]==1){
	
	$button="<input type='button' OnClick=\"$js_service\" value='{stop}&nbsp;&raquo;'>";
	}else{
	$js_service="StartStopService('".$bsini->_params["$key"]["service_cmd"]."','1','$title');";	
	$button="<input type='button' OnClick=\"$js_service\" value='{start}&nbsp;&raquo;'>";
	}
	
	
if($bsini->_params[$key]["pattern_version"]<>null && $bsini->_params[$key]["pattern_date"]==null){
	$bsini->_params[$key]["pattern_date"]=$bsini->_params[$key]["pattern_version"];
}
	
if($bsini->_params["$key"]["service_cmd"]==null){$js_service=null;}

if($bsini->_params[$key]["pattern_date"]<>null){
	$date=date('Y-m-d');
	$bsini->_params[$key]["pattern_date"]=str_replace($date,"{today},",$bsini->_params[$key]["pattern_date"]);
	$date=date('Y-d-m');
	$bsini->_params[$key]["pattern_date"]=str_replace($date,"{today},",$bsini->_params[$key]["pattern_date"]);	
	$pattern=
	"<tr>
	<td align='right' valign='top'><strong>{pattern_ver}:</strong></td>
	<td align='left' valing='top'><strong>{$bsini->_params[$key]["pattern_date"]}</strong></td>
	</tr>";
}

if($bsini->_params["$key"]["service_disabled"]=="0"){	
	$img="warning42.png";
	$js_service="StartStopService('".$bsini->_params["$key"]["service_cmd"]."','1','$title');";
	$disabled="<tr><td colspan=2 align='right'><strong style='font-size:11px'><i>{service_disabled}</i></strong></td></tr>";
}

if($bsini->_params["$key"]["master_pid"]<>null){

	$pid_text="";
		$pid="
		<tr>
			<td align='right' nowrap><strong>{pid}:</strong></td>
			<td><strong>{$bsini->_params["$key"]["master_pid"]}</strong></td>
		</tr>			
		<tr>
			<td align='right'><strong>{memory}:</strong></td>
			<td><strong>".FormatBytes($bsini->_params["$key"]["master_memory"])."</strong></td>
		</tr>		
		";
			
			if(trim($bsini->_params[$key]["master_cached_memory"])<>null){	
			$pid=$pid."
				<tr>
					<td align='right'><strong>{virtual_memory}:</strong></td>
					<td><strong>".FormatBytes($bsini->_params["$key"]["master_cached_memory"])."</strong></td>
				</tr>	
			";		
			}
			
}

if(trim($bsini->_params[$key]["processes_number"])<>null){
	if($bsini->_params[$key]["processes_number"]>0){
		$processes="<tr>
						<td align='right'><strong>{processes}:</strong></td>
						<td><strong>".$bsini->_params["$key"]["processes_number"]."</strong></td>
				</tr>";
	}
}


if(trim($bsini->_params[$key]["license_error"])<>null){
	$img="warning42.png";
	$license_error=
	"<tr>
	<td align='right' valign='top'><strong>{license_error}:</strong></td>
	<td align='left'><strong>". texttooltip(substr($bsini->_params[$key]["license_error"],0,13)."...",$bsini->_params[$key]["license_error"],null,nulll,1)."</strong></td>
	</tr>";
}

$bsini->_params["$key"]["master_version"]=str_replace('/DEBUG','',$bsini->_params["$key"]["master_version"]);

$version="<tr>
		<td align='right' nowrap valign='top'><strong>{version}:</strong></td>
		<td valign='top'><strong>{$bsini->_params["$key"]["master_version"]}</strong></td>
		</tr>";
if($bsini->_params["$key"]["master_version"]==null){$version=null;}

if($bsini->_params["$key"]["why"]<>null){
	$img="warning42.png";
	$textoadd=
	"<tr>
	<td align='right' valign='top'><strong>{info}:</strong></td>
	<td align='left'><strong style='color:red'>{$bsini->_params["$key"]["why"]}</strong></td>
	</tr>";
}
if($bsini->_params["$key"]["other"]<>null){
	$textoadd2=
	"<tr>
	<td align='right' valign='top'><strong>{info}:</strong></td>
	<td align='left'><strong style='color:black'>{$bsini->_params["$key"]["other"]}</strong></td>
	</tr>";
}





$html="<table style='width:1OO%'>
<tr>
<td width=1% valign='top'>" . imgtootltip($img,'{start_stop}',$js_service)."</td>
<td>
	<table style='width:100%'>
		<tr>
		<td align='right' nowrap><strong>{{$bsini->_params["$key"]["service_name"]}}:</td>
		<td><strong><span style='font-size:12px'>$status</span>$pid_text</strong></td>
		</tr>
		$pid
		$processes
		$license_error
		$version
		$icap$pattern$textoadd$textoadd2$disabled
	</table>
	</td>
</table>";		
	
return RoundedLightGreen($html)."<br>";
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


function CalendarPickup(){
	$page=CurrentPageName();
	$field=$_GET["field"];
	$html="
	loadssfile('js/dhtmlxcalendar/codebase/dhtmlxcalendar.css');
	Loadjs('js/dhtmlxcalendar/dhtmlxcalendar.js');
	Loadjs('js/dhtmlxcalendar/dhtmlxcommon.js');
	window.dhx_globalImgPath='js/dhtmlxcalendar/codebase/imgs/';
	function LoadAll(){
		 if (typeof dhtmlxCalendarObject=='undefined'){	
			Loadjs('$page?CalendarPickup=yes&field=$field');
			return null;
		}
	
	var cal1 = new dhtmlxCalendarObject('$field',true,{isYearEditable: true,headerButtons: 'TMX'});
	cal1.show();
	//cal1.curDate=document.getElementById('$field').value;
	
	}
	

	LoadAll();

	
	";
	
echo $html;	
	
}


function button_browse($field_name){
	$button="&nbsp;<input type='button' OnClick=\"javascript:Loadjs('SambaBrowse.php?no-shares=yes&field=$field_name');\" value='{browse}...&nbsp;'>";
	return $button;
	}
	
function button_browse_computer($computer,$field_name){
	$button="&nbsp;<input type='button' OnClick=\"javascript:Loadjs('ComputerBrowse.php?&computer=$computer&field=$field_name');\" value='{browse}...&nbsp;'>";
	return $button;
	}
	
function js_browse_direct($startfolder,$fieldname){
	return "Loadjs('SambaBrowse.php?jdisk=disk&mounted=$startfolder&t=&homeDirectory=&no-shares=yes&field=$fieldname&without-start=yes')";
	
}
	


function EMPTY_CACHE(){
	while (list ($num, $val) = each ($_SESSION) ){
		if(preg_match("#^cachepage_(.+)#",$num)){
			unset($_SESSION[$num]);
		}
	}
}

function formatQueryResultsTable($ligne,$count=null,$memory=true,$js=null,$bgcolor=null){
	if($memory){
		if($_GET["ID"][$ligne["MessageID"]]=="OK"){return null;}
		$_GET["ID"][$ligne["MessageID"]]="OK";
	}
	
	if($js==null){$js="ShowBackupMail('{$ligne["MessageID"]}')";}
	$title=$ligne["subject"];
	$title=utf8_decode($title);
	if(strlen($title)>50){
		$title=texttooltip(substr($title,0,47).'...',$title,$js,null,0,"text-decoration:underline;font-size:11px");
	}else{
	$title="<div OnClick=\"javascript:$js\" style='text-decoration:underline;font-size:11px'>$title</div>";
	}
	$body=$ligne["MessageBody"];
	if(preg_match('#<!--X-Body-of-Message-->(.+?)<!--X-Body-of-Message-End-->#is',$body,$re)){
		$body=$re[1];
		
		
	}
	
	$y=date('Y');
	$today=date('Y-m-d');
	$ligne["zDate"]=str_replace("$today",'{today}',$ligne["zDate"]);
	$ligne["zDate"]=str_replace("-$y",'',$ligne["zDate"]);
	
	
	if(is_numeric($count)){$number="$count.&nbsp;";}
	$body=strip_tags($body);
	$body=substr($body,0,300).'...';
	$body=wordwrap($body, 100, "<br />\n");
	$html="
	<tr style='background-color:$bgcolor'>
		<td style='padding:4px'>$number</td>
		<td style='padding:4px'>". round($ligne["pertinence"],2)."</td>
		<td style='padding:4px'>$title</td>
		<td style='padding:4px'>{$ligne["mailfrom"]}</td>
		<td nowrap style='padding:4px'><strong>{$ligne["zDate"]}</strong></td>
	</tR>";
	
	return $html;
	
	
	
	$tt="
	<table style='width:99%;margin-top:6px'>
	<tr>
		<td>$number$title</td>
	</tr>
	<tr>
	<td><span style='font-size:small;color:#676767'>&laquo&nbsp;{$ligne["mailfrom"]}&nbsp;&raquo;&nbsp;-&nbsp;{$ligne["zDate"]}</span></td>
	</tr>
	<tr>
	<td>
		<table style='width:100%'>
		<tr>
			<td valign='top' width=1%><img src='img/outicon_1002.gif'></td>
			<td width=99%><span style='font-size:small;color:#676767'>&laquo&nbsp;{$ligne["mailto"]}&nbsp;&raquo;</span></td>
		</tr>
		</table>
	</tr>	
	<tr>
	<td style='font-size:small'>$body</td>
	</tr>
	<tr>
	<td style='font-size:small;color:green' align='left'>{$ligne["MessageID"]} (".round($ligne["pertinence"],2).")</td>
	</tr>	
	</table>
	";
	
	return $html;
	
	
}

function formatQueryResultsAsGoogle($ligne,$count=null,$memory=true,$js=null){
	if($memory){
		if($_GET["ID"][$ligne["MessageID"]]=="OK"){return null;}
		$_GET["ID"][$ligne["MessageID"]]="OK";
	}
	
	if($js==null){$js="ShowBackupMail('{$ligne["MessageID"]}')";}
	$title=$ligne["subject"];

	if(strlen($title)>65){
		$title=texttooltip(substr($title,0,61).'...',$title,$js,null,0,"color:#0000CC;text-decoration:underline;font-size:medium");
	}else{
	$title="<a href='#' OnClick=\"javascript:$js\" style='color:#0000CC;text-decoration:underline;font-size:medium'>$title</a>";
	}
	$body=$ligne["MessageBody"];
	if(preg_match('#<!--X-Body-of-Message-->(.+?)<!--X-Body-of-Message-End-->#is',$body,$re)){
		$body=$re[1];
		
		
	}
	if(is_numeric($count)){$number="$count.&nbsp;";}
	$body=strip_tags($body);
	$body=substr($body,0,300).'...';
	$body=wordwrap($body, 100, "<br />\n");
	$html="
	
	<table style='width:99%;margin-top:6px'>
	<tr>
		<td>$number$title</td>
	</tr>
	<tr>
	<td><span style='font-size:small;color:#676767'>&laquo&nbsp;{$ligne["mailfrom"]}&nbsp;&raquo;&nbsp;-&nbsp;{$ligne["zDate"]}</span></td>
	</tr>
	<tr>
	<td>
		<table style='width:100%'>
		<tr>
			<td valign='top' width=1%><img src='img/outicon_1002.gif'></td>
			<td width=99%><span style='font-size:small;color:#676767'>&laquo&nbsp;{$ligne["mailto"]}&nbsp;&raquo;</span></td>
		</tr>
		</table>
	</tr>	
	<tr>
	<td style='font-size:small'>$body</td>
	</tr>
	<tr>
	<td style='font-size:small;color:green' align='left'>{$ligne["MessageID"]} (".round($ligne["pertinence"],2).")</td>
	</tr>	
	</table>
	";
	
	return $html;
	
	
}

function CleanMail($data){
		$data=str_replace("<hr>",'',$data);
	$data=str_replace("<o:p></o:p>","",$data);
	$data=str_replace('face="Times New Roman"',"",$data);
	$data=str_replace('face="Calibri"',"",$data);		
	$data=str_replace('face="Arial"',"",$data);
	$data=str_replace('face="Tahoma"',"",$data);	
	$data=str_replace('face=Tahoma',"",$data);		
	$data=str_replace('face=Arial',"",$data);
	$data=str_replace('face=Calibri',"",$data);
	$data=str_replace('font-family:Arial','',$data);
	$data=str_replace('font-family:Calibri','',$data);	
	$data=str_replace('font-family:Tahoma','',$data);		
	$data=str_replace('size="2"','',$data);		
	$data=str_replace('size="1"','',$data);		
	$data=str_replace('size="3"','',$data);	
	$data=str_replace('size=2','',$data);		
	$data=str_replace('size=1','',$data);		
	$data=str_replace('size=3','',$data);
	$data=str_replace('FONT-FAMILY: Verdana','',$data);
	$data=str_replace('10pt','',$data);
	$data=str_replace('10.0pt','',$data);
	$data=str_replace('11.0pt','',$data);	
	$data=str_replace('12.0pt','',$data);		
	
	$data=str_replace('<o:p>&nbsp;</o:p>','',$data);
	
	$data=str_replace("<h1>","<p style='font-size:15px;font-weight:bold;
	border-bottom:1px solid #CCCCCC;margin-bottom:5px;padding:5px;'>",$data);
	$data=str_replace("</h1>","</p>",$data);
	return $data;
}

function ICON_BIND9(){
	return Paragraphe('folder-64-bind9.png','{APP_BIND9}','{APP_BIND9_TEXT}',"javascript:Loadjs('index.bind9.php?script=yes')");
}


function ICON_USB_FORMAT($dev){
	$tpl=new templates();
	
	$js="Loadjs('usb.index.php?format-index=yes&dev=$dev')";
	$html="

	
	<table style='width:100%;'
	OnMouseOver=\";this.style.cursor='pointer';\"
	OnMouseOut=\";this.style.cursor='default';\"
	OnClick=\"javascript:$js\"
	>
	<tr>
		<td valign='top' width=1%>
			". imgtootltip('usb-32-format.png','{format_device_explain}')
			."
		</td>
		<td valign='top'><strong style='font-size:12px'>
			{formatdevice}
		</strong>
		</td>
	</tr>
	</table>";
	return $tpl->_ENGINE_parse_body($html,'usb.index.php');
	
}

function ICON_BIND9_COMPILE($nodiv=0){
$tpl=new templates();
$icon=$tpl->_ENGINE_parse_body(
Paragraphe("system-64.png",'{apply_settings_bind9}',"{apply_settings_bind9_text}",
"javascript:Loadjs('index.bind9.php?CompileBind9=yes');","apply_settings_bind9"));
if($nodiv==1){return $icon;}
return "<div id='CompileBind9'>$icon</div>";
}

function ICON_ADD_COMPUTER(){
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body(Paragraphe("computer-64-add.png","{add_computer}","{add_computer_text}","javascript:YahooWin3(670,\"domains.edit.user.php?userid=newcomputer$&ajaxmode=yes&gpid=$gpid&zone-name=$zone\",\"windows: New {add_computer}\");"),'domains.edit.group.php');
}

function ICON_DANSGUARDIAN_STATISTICS(){
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body(Paragraphe("system-64.png","{APP_DANSGUARDIAN} {compile_statistics}","{compile_statistics_text}",
	"javascript:YahooWin3(500,\"squid.newbee.php?dansguardian-stats-compile\",\"windows:{compile_statistics}\");"));
}

function ICON_OPENVPN_APPLY(){
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body(Paragraphe("system-64.png","{APP_OPENVPN_APPLY}","{APP_OPENVPN_APPLY_TEXT}",
	"javascript:YahooWin3(500,\"index.openvpn.php?restart-server=yes\",\"windows:{APP_OPENVPN_APPLY}\");"));
}

function ICON_FETCHMAIL(){
$tpl=new templates();
return $tpl->_ENGINE_parse_body(Paragraphe('folder-64-fetchmail.png',
'{APP_FETCHMAIL_TINY}','{APP_FETCHMAIL_TEXT}',"javascript:Loadjs('fetchmail.index.php?ajax=yes');",null,210,100,0,true));
	
}

function URL_COMPUTER($uid,$win){
	return "YahooWin$win(670,'domains.edit.user.php?userid=$uid$&ajaxmode=yes&Yahoowin=$win','$uid')";
}
function MEMBER_JS($uid,$noclik=0,$nojavascript=0){
	
	$java="javascript:";
	if($nojavascript==1){$java=null;}
	$js="{$java}YahooUser(800,'domains.edit.user.php?userid=$uid&ajaxmode=yes','windows: $uid');";
	if($noclik==0){
		return "OnClick=\"$js\"";
	}
	return $js;
}

function br2nl($string)
{
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

function p2nl ($str) {
    return preg_replace(array("/<p[^>]*>/iU","/<\/p[^>]*>/iU"),
                        array("","\n"),
                        $str);
}

function strip_tabs($array){
$tpl=new templates()	;
while (list ($num, $ligne) = each ($array) ){
	$title=$tpl->_ENGINE_parse_body($ligne);
	$title=html_entity_decode($title);
	$title=replace_accents($title);
	$calc=str_replace(" ","-",$title);
	if(strlen($calc)>15){
		$text=$title;
		$title=substr($title,0,11);
		$title=str_replace(" ","&nbsp;",$title);
		$link=texttooltip($title."...",$text,null,null,1);
		$re[$num]=$link;
	}else{
		
		$title=str_replace(" ","&nbsp;",$title);
		$re[$num]=$title;
	}
	
	
}

return $re;
	
}

function IsIPValid($ip) {
    $ip_segments = explode('.', $ip);

    if (count($ip_segments) != 4)
        return FALSE;

    if ($ip_segments[0] == 0)
        return FALSE;

    foreach ($ip_segments as $segment) {
        if (!ctype_digit($segment))
            return FALSE;
        if (strlen($segment) > 3)
            return FALSE;
        if ($segment > 255)
            return FALSE;
    }
        
    return TRUE;
}

function isBuildPid($file){
$pid=getmypid();
$file=basename($file);
$pidfile="/etc/artica-postfix/croned.1/$file.pid";
if(file_exists($pidfile)){
	$currentpid=trim(file_get_contents($pidfile));
	write_syslog("NewPID PID: $pid");
	write_syslog("Current PID: $currentpid");
	if($currentpid<>$pid){
		if(is_dir('/proc/'.$currentpid)){
			write_syslog("Already instance executed");
			return false;
	}else{
		write_syslog("$currentpid is not executed continue...");
		return true;
	}
		
	}
	
}	

return true;
}
function BuildParagraphe($title,$content,$js,$image,$div=false){
	$page=CurrentPageName();
	$element=element_rollover($js);
	$html="<div style='float:none;padding:3px;'>
	<table $element style='width:99%;border-right:1px solid #CCCCCC;border-bottom:1px solid #CCCCCC;margin:3px'>
	<tr>
	<td width=1% valign='top'>" . imgtootltip($image,"{{$content}}")."</td>
	<td valign='top'>
	<table style='width:100%'>
	<tr>
	<td style='border-bottom:1px dotted #CCCCCC'><strong style='font-size:13px'>{{$title}}</strong></td>
	</tr>
	<tr>
	<td valign='top'><div style='min-height:59px'><a href=\"javascript:$js\" style='font-size:11px'>{{$content}}</a></div></td>
	</tr>
	</table>
	</td>
	</tr>
	</table></div>";
	
	if($div){
		$html="<div style='float:left;width:200px;margin:5px'>$html</div>";
	}
return $html;	
	
}



function system_alreadyExecuted($phpfile,$logfile=null){
	
	if(is_file("framework/frame.class.inc")){include_once("framework/frame.class.inc");}
	if(is_file("../framework/frame.class.inc")){include_once("../framework/frame.class.inc");}
	
	if(function_exists("Build_pid_func")){
		return Build_pid_func($phpfile,"MAIN");
	}
	
	//system_alreadyExecuted_events("want to find ". basename($phpfile),$logfile);
	$filetemp=OS_FILE_TEMP();
	$phpfile=basename($phpfile);
	$phpfile=str_replace('.','\.',$phpfile);
	$pid=getmypid();
	exec("/usr/bin/pgrep -l -f \"$phpfile\"",$vals);
	
	while (list ($num, $val) = each ($vals) ){
		$val=trim($val);
		if($val==null){continue;}
		if(preg_match("#pgrep#",$val)){continue;}
		if(preg_match('#([0-9]+)#',$val,$re)){
			
			if(is_file("/proc/{$re[1]}/exe")){$realfile=@readlink("/proc/{$re[1]}/exe");}
			system_alreadyExecuted_events("Found pid $val $realfile",$logfile);
			$array[$re[1]]="[{$re[1]}]:$realfile";
		}else{
			system_alreadyExecuted_events("unable to preg_match $val",$logfile);
		}
		
	}
	
	if(!is_array($array)){
		system_alreadyExecuted_events("no array given..");
		return false;
	}
	
	//system_alreadyExecuted_events("remove pid $pid of ".implode(",",$array),$logfile);
	unset($array[$pid]);
	unset($array[null]);
	if(!is_array($array)){
		system_alreadyExecuted_events("no array given..",$logfile);
		return false;
		}
	
	while (list ($num, $val) = each ($array) ){
		
		if(is_file("/proc/$num/exe")){
			$realfile=@readlink("/proc/$num/exe");
			if($realfile=="/bin/bash"){continue;}
			if($realfile=="/bin/dash"){continue;}
			//if($realfile=="/usr/bin/php5"){continue;}
			//system_alreadyExecuted_events("$date [$pid]: /proc/$num/exe exists \"". @readlink("/proc/$num/exe")."\" for \"$phpfile\"",$logfile);
			system_alreadyExecuted_events("$phpfile FOUND IN MEMORY",$logfile);
			return true;
		}
	}
	
	

}
function system_alreadyExecuted_events($text,$filepath=null){
		if($filepath==null){return null;}
		if(!is_file($filepath)){return null;}		
		$filename=basename(__FILE__);
		$pid=getmypid();
		$date=date("H:i:s");
		$logFile=$filepath;
		$size=filesize($logFile);
		if($size>1000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$date [$pid] $filename system_alreadyExecuted(): $text\n");
		@fclose($f);		
		}
		
function TransFormBounceError($bounce_error){
	if(preg_match('#User unknown#',$bounce_error)){$bounce_error="User unknown";}
	if(preg_match('#250.+?[OokK]+#',$bounce_error)){$bounce_error="Sended";}
	if(preg_match('#delivered via#',$bounce_error)){$bounce_error="Sended";}	
	return $bounce_error;	
	
}

function SET_CACHE($content,$page){
	$sock=new sockets();
	$sock->DATA_CACHE_SAVE($page,$content);
}
function GET_CACHE($page){
		$sock=new sockets();
		return $sock->DATA_CACHE($page,false);
}
function distanceOfTimeInWords($fromTime, $toTime = 0, $showLessThanAMinute = true) {
    $distanceInSeconds = round(abs($toTime - $fromTime));
    $distanceInMinutes = round($distanceInSeconds / 60);
       
        if ( $distanceInMinutes <= 1 ) {
            if ( !$showLessThanAMinute ) {
                return ($distanceInMinutes == 0) ? 'less than a minute' : '1 minute';
            } else {
                if ( $distanceInSeconds < 5 ) {
                    return 'less than 5 seconds ('.$distanceInSeconds.'s)';
                }
                if ( $distanceInSeconds < 10 ) {
                    return 'less than 10 seconds ('.$distanceInSeconds.'s)';
                }
                if ( $distanceInSeconds < 20 ) {
                    return 'less than 20 seconds ('.$distanceInSeconds.'s) ';
                }
                if ( $distanceInSeconds < 40 ) {
                    return 'about half a minute ('.$distanceInSeconds.'s)';
                }
                if ( $distanceInSeconds < 60 ) {
                    return 'less than a minute';
                }
               
                return '1 minute';
            }
        }
        if ( $distanceInMinutes < 45 ) {
            return $distanceInMinutes . ' minutes';
        }
        if ( $distanceInMinutes < 90 ) {
            return 'about 1 hour';
        }
        if ( $distanceInMinutes < 1440 ) {
            return 'about ' . round(floatval($distanceInMinutes) / 60.0) . ' hours';
        }
        if ( $distanceInMinutes < 2880 ) {
            return '1 day';
        }
        if ( $distanceInMinutes < 43200 ) {
            return 'about ' . round(floatval($distanceInMinutes) / 1440) . ' days';
        }
        if ( $distanceInMinutes < 86400 ) {
            return 'about 1 month';
        }
        if ( $distanceInMinutes < 525600 ) {
            return round(floatval($distanceInMinutes) / 43200) . ' months';
        }
        if ( $distanceInMinutes < 1051199 ) {
            return 'about 1 year';
        }
       
        return 'over ' . round(floatval($distanceInMinutes) / 525600) . ' years';
}

function file_ext($filename)
{
$filename = strtolower($filename) ;
$exts = explode("[/\\.]", $filename) ;
$n = count($exts)-1;
$exts = $exts[$n];
return $exts;
} 
function file_get_time_min($path){
	if(!is_file($path)){return 10000;}
	 $last_modified = filemtime($path);
	 
$data1 = $last_modified;

$data2 = time();
$difference = ($data2 - $data1); 	 
return round($difference/60);	 
}


function send_email_events($subject,$text,$context,$date=null,$attached_files=array(),$recipient=null){
	
	$arr_APP=array(
		"APP_ROUNDCUBE"=>"Web service For RoundCube",
		"APP_ZABBIX_SERVER"=>"Zabbix server",
		"APP_MYSQL_ARTICA"=>"Mysql server",
		"APP_ARTICA_MYSQMAIL"=>"Postfix Monitor",
		"APP_ARTICA_DANSGUARDIAN_TAIL"=>"DansGuardian Monitor",
		"APP_ARTICA_WATCHDOG"=>"Artica Watchdog",
		"APP_ARTICA"=>"Artica service",
		"APP_ASSP"=>"Anti-Spam SMTP Proxy",
		"APP_BOA"=>"Boa Daemon",
		"APP_CLAMAV"=>"ClamAV Daemon",
		"APP_CUPS"=>"Printer service",
		"APP_CYRUS"=>"Cyrus-imap service",
		"APP_DANSGUARDIAN"=>"DansGuardian",
		"APP_FETCHMAIL_LOGGER"=>"Fetchmail Monitor",
		"APP_FRAMEWORK"=>"Artica FrameWork",
		"APP_FETCHMAIL"=>"Fetchmail Daemon",
		"APP_MAILMAN"=>"Mailman mailinglist Daemon",
		"APP_FRESHCLAM"=>"ClamAV Updater",
		"APP_KAS3_MILTER"=>"Kaspersky Anti-Spam hook",
		"APP_KAS3"=>"Kaspersky Anti-Spam service",
		"APP_KAVMILTER"=>"Kaspersky Anti-virus For Mail",
		"APP_KRETRANSLATOR_HTTPD"=>"Kaspersky Antivirus updater web service",
		"APP_LDAP"=>"OpenLDAP service",
		"APP_LIGHTTPD"=>"Artica Web server",
		"APP_MONIT"=>"Watchdog system",
		"APP_RSYNC"=>"Rsync service",
		"APP_PDNS_RECURSOR"=>"PowerDNS external query ",
		"APP_PDNS"=>"PowerDNS service",
		"APP_POSTFIX"=>"Postfix",
		"APP_SAMBA_NMBD"=>"Samba NetBIOS name service",
		"APP_SAMBA_WINBIND"=>"Samba Name Service Switch",
		"APP_SAMBA_SMBD"=>"Samba Filesharing and printing services",
		"APP_SASLAUTHD"=>"System Authentication",
		"APP_SPAMASSASSIN"=>"SpamAssassin",
		"APP_SQUID"=>"Proxy service (squid)",
		"APP_SYSLOGER"=>"Artica System monitor",
		"APP_KAV4PROXY"=>"Kaspersky Antivirus For Squid",
		"APP_C_ICAP"=>"C-ICAP For Squid",
		"APP_SCANNED_ONLY"=>"Samba Antivirus Scanner",
		"APP_PUREFTPD"=>"FTP Service",
		"APP_MILTERGREYLIST"=>"Greylist service for Postfix",
		"APP_AMAVISD_MILTER"=>"Amavis listener milter",
		"APP_AMAVISD_NEW"=>"Amavis Daemon",
		"APP_CLAMAV_MILTER"=>"ClamAV For Postfix",
		"APP_MAILSPY"=>"MailSpy",
		"APP_ZARAFA_WEB"=>"Zarafa web server",
		"APP_ZARAFA_SERVER"=>"Zarafa server",
		"APP_ZARAFA_SPOOLER"=>"Zarafa Spooler",
		"APP_ZARAFA_DAGENT"=>"Zarafa Delivery Agent",
		"APP_ZARAFA_GATEWAY"=>"Zarafa Gateway",			
		"APP_ZARAFA_MONITOR"=>"Zarafa Monitor",
		
		
	);
	

	while (list ($tok, $service_name) = each ($arr_APP) ){
		$subject=str_replace($tok,$service_name,$subject);
		$text=str_replace($tok,$service_name,$text);
	}
		
	$users=new usersMenus();
	if($date==null){
		$date=date('Y-m-d H:i:s');
		$datesubject=date('H:i:s');
	}else{
		$datesubject=$date;
	}
	$filename="/var/log/artica-postfix/events/".md5($context.$subject);
	if(is_file($filename)){return null;}
	$ini=new Bs_IniHandler($filename);
	$ini->set('LOG','processname',basename(__FILE__));
	$ini->set('LOG','date',$date);
	$ini->set('LOG','context',$context);
	$ini->set('LOG','subject',"[$datesubject]: [ARTICA]: ($users->hostname) : $subject");
	$ini->set('LOG','recipient',$recipient);
	$ini->saveFile($filename);
	$datas=@file_get_contents($filename);
	$datas=$datas."\n<text>$text</text>\n";
	$datas=$datas."\n<attachedfiles>". base64_encode(serialize($attached_files))."</attachedfiles>\n";
	
	
if($GLOBALS["VERBOSE"]){echo strlen($datas)." bytes file $filename text\n";}
	
	@file_put_contents($filename,$datas);
	@mkdir("/usr/share/artica-postfix/ressources/logs/jGrowl",0775,true);
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/jGrowl/".basename($filename).".txt",$datas);
	@chmod("/usr/share/artica-postfix/ressources/logs/jGrowl/".basename($filename).".txt",0777);
	}
	
function THREAD_COMMAND_SET($zcommands){
  $md5command=md5(trim($zcommands));
  $FileDataCommand=@file_get_contents('/etc/artica-postfix/background');
  $tbl=explode("\n",$FileDataCommand);
 
if(is_array($tbl)){
	while (list ($num, $ligne) = each ($tbl) ){
		if($ligne==null){continue;}
		 $mdtmp=md5(trim($ligne));
		 if($md5command==$mdtmp){
		 	return false;
		 }
		}
	reset($tbl);
	}
$tbl[]=$zcommands;
@file_put_contents('/etc/artica-postfix/background',implode("\n",$tbl));
}


function infected_queue($TaskName,$InfectedPath,$ComputerName,$virusname){
	$date=date('Y-m-d H:i:s');
	$md5=md5($date.$product.$from.$virusname);
	$filename="/var/log/artica-postfix/infected-queue/$md5.sql";
	if(is_file($filename)){return null;}
	if($TaskName=="kavmilter"){$TaskName="eMail Scan Kaspersky";}
	$sql="INSERT INTO antivirus_events (zDate,TaskName,VirusName,InfectedPath,ComputerName,zmd5) VALUES('$date','$TaskName','$virusname','$InfectedPath','$ComputerName','$md5');";
	@file_put_contents($filename,$sql);
	}


//##############################################################################	
function autoencoder($s)//encode if necessary
{
$is_utf=1;
$ss=SplitByLength($s,5000);//with 10000+ => segfault (apache/php)
foreach($ss AS $s_)
{
if
(
preg_match('%^(?:
[\x09\x0A\x0D\x20-\x7E]              # ASCII
| [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
|  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
|  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
|  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
| [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
|  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
)*$%xs',
$s_)
)
$is_utf=0;
}
if(!$is_utf)
$s=utf8_encode($s);
return $s;
}
function SplitByLength($string, $chunkLength=1){
$Result     = array();
$Remainder  = strlen($string) % $chunkLength;
$cycles = ((strlen($string) - $Remainder) / $chunkLength) + (($Remainder != 0) ? 1 : 0);
for ($x=0; $x < $cycles; $x++)
$Result[$x] = substr($string, ($x * $chunkLength), $chunkLength);
return $Result;
}
class tweak_array{
		var $error_l = '<p><b>Warning:</b> Check your input parameters in your call to <i><b>';
		var $error_r = '</b></i></p>';
		
		function insert($array = '', $position = '' , $elements= ''){
			if ($position == '' || $array == '' || $elements == '' || $position < 1 || $position > count($array)+1){
				return $array;
			}else{
				$left = array_slice ($array, 0, $position-1);
				$right = array_slice ($array, $position-1);
				$insert = explode ('\,', $elements);
				$array = array_merge ($left, $insert, $right);
				unset($left, $right, $insert);
			}
		
			unset ($position, $elements);
			return $array;
		}
		
		function delete($array = '', $from = '', $to=''){
			if ($to == '') $to = $from;
			if ($array == '' || $from == '' || $to > count($array) || $to < 0 || $from > count($array)){
				return $array;
			}
			elseif ($to < $from){
				return $array;
			}
			else{
			$left = array_slice ($array, 0, $from-1);
			$right = array_slice ($array, $to);
			$array = array_merge ($left, $right);
			unset ($left, $right);
			}
			unset ($from, $to);
			return $array;
		}
		
		function move($array = '', $from = '', $to = ''){
				if ($array == ''|| $from == ''|| $to == '' || $to > count($array) || $to < 0 || $from > count($array) || $from < 0){
					return $array;
				}
				else{
					$hopper = $array[$from-1];
					$array = $this->delete($array, $from);
					$array = $this->insert($array, $to, $hopper);
				}
				unset ($hopper, $from, $to);
				return $array;
		}
		
		function replace($array = '', $position = '', $new_element = ' '){
			if ($array == '' || $position == '' || $position > count($array) || $position < 1){return $array;}
			else{
				$array = $this->insert($array, $position, $new_element);
				$array = $this->delete($array, $position+count(explode('\,', $new_element)));
			}
		
			unset ($position, $new_element);
			return $array;
		}
} 
function button($text,$js){
	
	$over="OnMouseOut=\"javascript:this.className='ui-state-default ui-corner-all';this.style.cursor='auto'\"";
	$out="OnMouseOver=\"javascript:this.className='ui-state-active ui-corner-all';this.style.cursor='pointer'\"";
	
	return "<button type='button' style='padding:5px;' $over $out class='ui-state-default ui-corner-all' OnClick=\"javascript:this.className='ui-state-over';$js;\">&nbsp;$text&nbsp;&nbsp;&raquo</button>";
}

function pourcentage($pourc){
	$pourc=str_replace('%','',$pourc);
	$orginal_pourc=$pourc;
	$pourc=round($pourc);
	
	
	if($pourc==0){$int=-119;}
	if($pourc>1){$int=$pourc-118;}
	if($pourc>50){$int=$pourc-100;}
	
	
	$precent="
		<table><tr><td><img style=\"background:white url(img/percentImage_back4.png) no-repeat scroll 1px 0;margin:5px 0 0;padding:0;
		background-position:{$int}px 0pt;\"  
		src=\"img/percentImage.png\"></td><td widht=1% nowrap style='font-size:12px'>$orginal_pourc%</td></tr></table>";
	
	return $precent;
}

function GET_CACHED($file,$function,$token=null,$return=false,$maxmin=0){
	$md5=md5($file.$function.$token.$_SESSION["detected_lang"]);
	if($GLOBALS["LOGON-PAGE"]){error_log("-> _SESSION[\"cached-pages\"][$md5]". basename(__FILE__). " line ". __LINE__);}
	$data=trim($_SESSION["cached-pages"][$md5]);
	if($GLOBALS["LOGON-PAGE"]){error_log("-> _SESSION[\"cached-pages\"][$md5]=". strlen($data)." bytes " . __FILE__. " line ". __LINE__);}
	if($data<>null){
		if($return){return $data;}
		if($data==null){return false;}
		echo $data;
		return true;
	}
	
	if($token==null){$token=$function;}
	
	if($GLOBALS["LOGON-PAGE"]){error_log("->new sockets() ". basename(__FILE__). " line ". __LINE__);}
	$sock=new sockets();
	$data=trim($sock->DATA_CACHE($md5,false,$maxmin));
	if($data<>null){$_SESSION["cached-pages"][$md5]=$data;}
	if(trim($data)==null){
		writelogs("\"$md5\"  get cache  $function/". basename($file) ." ($token) is null",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	if($GLOBALS["LOGON-PAGE"]){error_log("finish.. ". basename(__FILE__). " line ". __LINE__);}
	if($return){return $data;}
	if($data==null){return false;}
	echo $data;
	return true;
}

function SET_CACHED($file,$function,$token,$datas){
	if(strlen($datas)==0){return;}
	if(trim($token)==null){$token=$function;}
	$md5=md5($file.$function.$token.$_SESSION["detected_lang"]);
	$sock=new sockets();
	writelogs("\"$md5\" Writing cache  ". basename($file)." ". __FUNCTION__." ($token) {$_SESSION["detected_lang"]} ". strlen($datas)." bytes",__FUNCTION__,__FILE__,__LINE__);
	$sock->DATA_CACHE_SAVE($md5,$datas);
	$_SESSION["cached-pages"][$md5]=$datas;
	
	
}
FUNCTION REMOVE_CACHED($file){
	foreach (glob(dirname(__FILE__)."/logs/cache/".basename($file)."*.cached") as $filename) {
    unlink($filename);
}
$sock=new sockets();
$sock->DATA_CACHE_EMPTY();
unset($_SESSION["cached-pages"]);

}
function finduser_format($array){
	
	if(strlen($array["mail"])>32){
		$array["mail"]=texttooltip(substr($array["mail"],0,29),$array["mail"]).'...';
	}
	
	
	if ($array["title"]<>null){
		$title="<tr>
					<td><strong style='font-size:9px;font-weight:bold'>{$array["title"]}</strong></td>
				</tr>";
		
	}
	
	$disp="{$array["givenname"]} {$array["sn"]}";
	if(trim($disp)==null){
		$disp=$array["displayname"];
	}
	
	if($array["phone"]<>null){
		$phone="<tr>
					<td><strong style='font-size:10px;font-weight:normal'>{$array["phone"]}</strong></td>
				</tr>";
	}
	
	
	if($array["ou"]<>null){
		$ou="<tr>
					<td><strong style='font-size:11px;font-weight:bold'>{$array["ou"]}</strong></td>
				</tr>";
	}
		
	
	if($array["mobile"]<>null){
		$mobile="<tr>
					<td><strong style='font-size:10px;font-weight:normal'>{$array["mobile"]}</strong></td>
				</tr>";
	}	
	

	
	if($array["mail"]<>null){
		$mail="<tr>
					<td><strong style='font-size:10px;font-weight:normal'>{$array["mail"]}</strong></td>
				</tr>";
	}
	
	if($array["JS"]<>null){
		$style_plus="
		OnMouseOver=\";this.style.cursor='pointer';\" 
		OnMouseOut=\";this.style.cursor='default';\"
		OnClick=\"{$array["JS"]}\";
		
		";
	}
	$html="
	<div style='background-image:url(img/user-calque.jpg);width:285px;height:137px;padding:5px;background-repeat:no-repeat' $style_plus>
	<table style='width:265px'>
	<tr>
		<td valign='top' width=1%><div style='background-color:#FFFFFF;border:1px solid black;padding:2px;margin:2px'><img src='{$array["img"]}'></div></td>
		<td valign='top' width=99%>
			<table style='width:100%'>
				<tr>
					<td><strong style='font-size:13px;font-weight:bold;text-transform : capitalize;'>$disp</strong></td>
				</tr>
				$title
				$ou
				$phone
				$mobile
				$mail
			</table>
		</td>
		</tr>
	</tr>
	</table>
	</div>
	
	
	";
return $html;
	
}
 function is_base64_encoded($encodedString) {
 	writelogs("$encodedString =\"".substr($encodedString,strlen($encodedString)-2,2)."\"",__FUNCTION__,__FILE__,__LINE__);
 	if(substr($encodedString,strlen($encodedString)-2,2)=="=="){return true;}
 	
 	$arr["150"]=true;
 	$arr["135"]=true;
 	$arr["181"]=true;
 	$arr["26"]=true;
 	$arr["182"]=true;
 	$arr["137"]=true;
 	$arr["139"]=true;
 	$arr["166"]=true;
 	$arr["169"]=true;
 	$arr["186"]=true;
 	$arr["179"]=true;
 	$arr["171"]=true;
 	$arr["202"]=true;
 	
 	
 	$datas=base64_decode($encodedString);
	for($i=0;$i<strlen($datas);$i++){
		$chr=getChr($datas[$i]);
		if($arr[$chr]){return false;}
		writelogs("{$datas[$i]}==".getChr($datas[$i]),__FUNCTION__,__FILE__,__LINE__);
	}
	return true;  
  }
  
  
function getChr($car){
	for($i=0;$i<256;$i++){
		if($car==chr($i)){return $i;}
	}
	
}




?>