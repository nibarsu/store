<?php
include "main.php";
include ('includes/tool.php');
//temp file
$temp_file=GetTempFilename(__FILE__,"htm");
$curr_file=GetFilename(__FILE__);
//current file name
//資料庫連接
$conn=CTDB();
include ('includes/checkUID.php');



$title="系統選單編輯";

//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];


$MC=  new Mclass();
$MC->conn=$conn;

//print_r($Ob);
$submit=$_POST["submit"];
$submit1=$_POST["submit1"];
$NAME=$_POST["name"];
$TYPE=$_POST["type"];
$FILE_NAME=$_POST["file_name"];
$BASIC=$_POST["basic"];
//$ID=GenCustID($conn);
//print $ID; 

if ($_POST["submit"]=="新增"){
								
				if ($NAME){
									$DATA["NAME"]=$NAME;
									$MC->GenType($DATA);
				     //$Ob->AddData($DATA);
    }
}
if ($_POST["submit1"]=="新增"){
								
				if ($NAME){
									$DATA["TYPE"]=$TYPE;
									$DATA["NAME"]=$NAME;
									$DATA["FILE_NAME"]=$FILE_NAME;
									$DATA["BASIC"]=$BASIC;
									$MC->GenMenu($DATA);
				     //$Ob->AddData($DATA);
    }
}

//print $_POST["edit"];
//print_r($_POST);



if ($_POST["submit"]=="編輯"){
								//更新
								if ($_POST["id"]){
													$D["ID"]=$_POST["id"];
													$D["NAME"]=$_POST["name"];
													$MC->UpdType($D);
													//清除編輯狀態
													$edit=0;
													//跳回原畫面
								}
								
}
if ($_POST["submit1"]=="編輯"){
								//更新
								if ($_POST["id"]){
													$D["ID"]=$_POST["id"];
													$D["TYPE"]=$_POST["type"];
													$D["NAME"]=$_POST["name"];
													$D["FILE_NAME"]=$_POST["file_name"];
													$D["BASIC"]=$_POST["basic"];
													$MC->UpdMenu($D);
													//清除編輯狀態
													$edit=0;
													//跳回原畫面
								}
								
}



$MC=  new Mclass();
$MC->conn=$conn;
$MC->ALLdata=$MC->GetALLdata();
$tpl->assign("menutypedata",$MC->ALLdata["MENU_TYPE"]);
$tpl->assign("menudata",$MC->ALLdata["MENU_TYPE_D"]);

if ($edit==1){
					//丟至頁面上
					$tpl->assign("edata",$MC->ALLdata["MENU_TYPE"][$id]);
}
if ($edit==2){
					//丟至頁面上
					$tpl->assign("e2data",$MC->ALLdata["MENU_TYPE_D"][$id]);
}
$conn->close();

$tpl->assign("edit", $edit);
$tpl->assign("submit", $submit);
$tpl->assign("submit1", $submit1);
$tpl->display($temp_file);
?>
