<?php
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.templates.inc");

if(isset($_GET["index-page"])){sommaire();exit;}

js();



function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{my_address_book}');
	$page=CurrentPageName();
	$html="
		function LoadGlobalAddressBook(){
			YahooWin3(750,'$page?index-page=yes','$title');
		
		}
	
	
	LoadGlobalAddressBook();
	
	";
	
	echo $html;
	
}



function sommaire(){
	
	$add=Paragraphe("my-address-book-user-add.png",'{add_new_contact}','{add_new_contact_text}',"javascript:Loadjs('contact.php')");
	
	$left=RoundedLightWhite($add);
	
	$html="<H1>{my_address_book}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$left
		</td>
		
		<td valign='top'>
		</td>
	</tr>
	</table>
				
	
	
	";
	
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}



?>