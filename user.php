<?php
include "main.php";
include ('includes/checkUID.php');
include ('includes/tool.php');
//資料庫連接
$conn=CTDB();
$title="使用者資料編輯";

//userid
$UID=1;
//right
//先預設全部
//default template
$temp_file=GetTempFilename(__FILE__,"htm");
$temp_file="mainmenu.htm";  
//print_r($Sto);
$submit=$_POST["submit"];
$NAME=$_POST["name"];
$PASS=$_POST["pass"];
$REMARK=$_POST["remark"];

if ($_POST["submit"]=="新增"){
								
								if ($NAME){
													$DATA["NAME"]=$NAME;
													$DATA["PASS"]=$PASS;
													$DATA["REMARK"]=$REMARK;
													write_to_table($DATA, "USER_M" );
        }
}

//print $_POST["edit"];
//print_r($_POST);



if ($_POST["submit"]=="編輯"){
								//更新
								if ($_POST["id"]){
													$D["ID"]=$_POST["id"];
													$D["NAME"]=$_POST["name"];
													$D["PASS"]=$_POST["pass"];
													$D["REMARK"]=$_POST["remark"];
													//print_r($D);
													UpdDataId($D, "USER_M")
													//清除編輯狀態
													$edit=0;
													//跳回原畫面
								}
								
}

if ($edit==1){
					//丟至頁面上
					$edata=FetchRowS($id, "USER_M");
					
					$tpl->assign("edata",$Sto->Data);
}






$conn->close();
$tpl->assign("cdata",$cdata);
$tpl->assign("edit", $edit);
$tpl->assign("submit", $submit);
$tpl->assign("title", $title);
$tpl->display($temp_file);
?>
