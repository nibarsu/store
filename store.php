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

$title="倉庫資料編輯";

//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];

$Sto=  new Sclass();
$Sto->conn=$conn;
//print_r($Sto);
$submit=$_POST["submit"];
$NAME=$_POST["name"];
$REMARK=$_POST["remark"];

if ($_POST["submit"]=="新增"){

  if ($NAME){
	$DATA["ID"]=   GenID("STORE_M");
	$DATA["NAME"]=$NAME;
	$DATA["REMARK"]=$REMARK;
	//$Sto->AddData($DATA);
	write_to_table($DATA, "STORE_M");
  }
}

//print $_POST["edit"];
//print_r($_POST);



if ($_POST["submit"]=="編輯"){
  //更新
  if ($_POST["id"]){
	$D["ID"]=$_POST["id"];
	$D["NAME"]=$_POST["name"];
	$D["REMARK"]=$_POST["remark"];
	//print_r($D);
	$Sto->UpdData($D);
	//清除編輯狀態
	$edit=0;
	//跳回原畫面
  }

}

if ($edit==1){
  //丟至頁面上
  $Sto->GetData($id);
  $tpl->assign("edata",$Sto->Data);
}

if ($_REQUEST["edit"]==2){
  //20131013新增刪除功能
  $D["ENABLE"]="N";
  $E["ID"]=$_REQUEST["id"];
  update_to_table($D,"store.store_m", $E );
  //清除編輯狀態
  $edit=0;
  //跳回原畫面
}
$sql="SELECT ID,NAME,REMARK FROM STORE_M  where ENABLE='Y' ORDER BY ID";
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
