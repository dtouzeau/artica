<?php
session_start();
unset($_SESSION["MLDONKEY_{$_SESSION["uid"]}"]);
unset($_SESSION["uid"]);
unset($_SESSION["LANG_FILES"]);
unset($_SESSION["PATTERN_FILE"]);
unset($_SESSION["cached-pages"]);
unset($_SESSION["translation-en"]);


echo "
<html>
<head>
<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; URL=logon.php\"> 
</head>
<body style='background-color:#005447'></body></html>";
?>