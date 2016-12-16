<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
include __DIR__.'/vendor/autoload.php';
include __DIR__.'/includes/dbconfig.php';
include __DIR__.'/includes/adodb/adodb.inc.php';

use Dotenv\Dotenv;
$dotenv = new Dotenv(__DIR__);
$dotenv->load();
use Recca0120\LaravelTracy\Tracy;
Tracy::instance();

include __DIR__.'/includes/tool.php';
include __DIR__.'/includes/smarty/Smarty.class.php';
define('APP_PATH', str_replace('\\', '/', dirname(__FILE__)));
$tpl = new Smarty();
$tpl->template_dir = APP_PATH . "/templates/";
$tpl->compile_dir = APP_PATH . "/templates_c/";
$tpl->config_dir = APP_PATH . "/configs/";
$tpl->cache_dir = APP_PATH . "/cache/";


?>
