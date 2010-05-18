<?php
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
$tpl=new templates();
echo $tpl->_ENGINE_parse_body("
<div style='width:100%;height:423px;background-image:url(img/artica4.jpg);'>
<center><br>
<H2>{closed_session}</H2>
". button('{login}',"document.location.href='logoff.php'")."
</center></div>
</div>");
?>