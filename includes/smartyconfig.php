<?php
//smarty root位置
/*
	$Id: smartyconf.php,v 1.1.1.1 2004/08/20 03:55:10 dannis Exp $
	Smarty Directory Configureation
	Smarty root 位置於 /usr/local/share/smarty 採用 port 安裝後手動昇級
	Change Log

*/

 $webroot = "/usr/local/www/data-dist" ;
 $modules_dir = ( $_REQUEST["name"] != "") ? "/modules" : "";
 $module_name = ( $_REQUEST["name"] != "") ? "/" . $_REQUEST["name"] : "";
 $nowdir = getcwd() ;
 if (!@chdir($webroot.$modules_dir.$module_name."/templates_c/")) @mkdir($webroot.$modules_dir.$module_name."/templates_c/",0777) ;

 @chdir($nowdir) ;

 require("Smarty.class.php");
 $smarty = new Smarty;
 $smarty->template_dir = $webroot.$modules_dir.$module_name."/templates/" ;
 $smarty->config_dir = $webroot.$modules_dir.$module_name."/templates/language/" ;
 $smarty->compile_dir = $webroot.$modules_dir.$module_name."/templates_c/";
 $smarty->cache_dir = $webroot."/cache/";
 $smarty->assign("LANG","lang-$currentlang.conf") ;
// $smarty->caching = true;

?>