<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');

writelogs("Running jGrowl...","MAIN",__FUNCTION__,__FILE__,__LINE__);

if(is_file("ressources/logs/web/jgrowl.txt")){
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(@file_get_contents("ressources/logs/web/jgrowl.txt"));
	@unlink("ressources/logs/web/jgrowl.txt");
}