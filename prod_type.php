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



$title="商品類別編輯";

//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];

$temp_file=GetTempFilename(__FILE__,"htm");


//print_r($Ob);
$submit=$_POST["submit"];
$NAME=$_POST["name"];
//$ID=GenCustID($conn);
//print $ID; 

if ($_POST["submit"]=="新增"){
								
								if ($NAME){
								     $DATA["ID"]=GenID("PROD_TYPE");
													$DATA["NAME"]=$NAME;
													write_to_table($DATA, "PROD_TYPE");
								     //$Ob->AddData($DATA);
        }
}

//print $_POST["edit"];
//print_r($_POST);



if ($_POST["submit"]=="編輯"){
								//更新
								//viewarray($_POST);
								if ($_POST["id"]){
													$D["ID"]=$_POST["id"];
													$D["NAME"]=$_POST["name"];
													$D["ENABLE"]=$_POST["enable"];
													$sql="UPDATE PROD_TYPE SET NAME='".$D["NAME"]."'
													,ENABLE='".$D["ENABLE"]."' 
													WHERE ID=".$D["ID"];
													//print $sql;
													$conn->Execute($sql);
													//清除編輯狀態
													$edit=0;
													//跳回原畫面
								}
								
}

if ($edit==1){
					//丟至頁面上
					$sql="SELECT * FROM PROD_TYPE WHERE ID=$id";
					$rs=$conn->Execute($sql);
					$edate=$rs->FetchRow();
					//$edate["ID"]=$id;
					$edate["NAME"]=GetName($id,"PROD_TYPE");
					//viewarray($edate);exit;
					$tpl->assign("edata",$edate);
}

$sql="SELECT ID,NAME,ENABLE FROM PROD_TYPE ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
         $cdata[]=$row;
}


$conn->close();
$tpl->assign("cdata",$cdata);
$tpl->assign("edit", $edit);
$tpl->assign("submit", $submit);
$tpl->assign("title", $title);
$tpl->display($temp_file);
?>
