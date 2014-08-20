<?php 
ob_start ("ob_gzhandler");
header("Content-type: text/js; charset: UTF-8");
header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T', 1331380800));
$offset = 60 * 60 * 24 * 7 ;
$ExpStr = "Expires: " . 
gmdate("D, d M Y H:i:s",
time() + $offset) . " GMT";
header($ExpStr);
?>