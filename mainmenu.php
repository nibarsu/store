<?php
include "main.php";
include ('includes/checkUID.php');
include ('includes/tool.php');
//資料庫連接
$conn=CTDB();

//userid
$UID=$_COOKIE["wdcbcUID"];

//default template
$temp_file=GetTempFilename(__FILE__,"htm");

$conn->close();
$tpl->display($temp_file);

?>

