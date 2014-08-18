<?php
include "includes/smarty/Smarty.class.php";
define('APP_PATH', str_replace('\\', '/', dirname(__FILE__)));
//print __FILE__;
$tpl = new Smarty();
$tpl->template_dir = APP_PATH . "/templates/";
$tpl->compile_dir = APP_PATH . "/templates_c/";
$tpl->config_dir = APP_PATH . "/configs/";
$tpl->cache_dir = APP_PATH . "/cache/";
?>