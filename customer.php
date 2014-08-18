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

$title="客戶資料編輯";

//第一頁為"目前庫存狀況表"
  //可列出目前這家店的商品數量
  //可搜尋
//第二個功能為"客戶資料編輯"
//第三個功能為"廠商資料編輯"
//第四個功能為"進貨管理"
  //新商品輸入  並自動編barcode
  //舊的商品進貨
  //轉倉進貨(應由出貨時處理  由B店轉倉至A店並在A店售出)
//第五個功能為"出貨管理"
  //商品出貨
  //轉倉出貨
//第六個功能為"報表匯總"
  //區間內銷售多少商品 數量

//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];
  
  
$submit=$_POST["submit"];
$NAME=$_POST["name"];
$PHONE=$_POST["phone"];
$EMAIL=$_POST["email"];
//$ID=GenCustID($conn);
//print $ID; 

if ($_POST["submit"]=="新增"){
								
					if ($NAME){
					     $CDATA=array();
					     $CDATA["ID"]=GenID("CUST_M");
					     $CDATA["NAME"]=$NAME;
          $CDATA["PHONE"]=$PHONE;
          $CDATA["EMAIL"]=$EMAIL;
          $CDATA["SID"]=$_POST["sid"];
					     $CDATA["ADDR"]=$_POST["addr"];
					     $CDATA["REMARK"]=$_POST["remark"];
          write_to_table($CDATA,"CUST_M");
					}
}


if ($_POST["submit"]=="編輯"){
					if ($_POST["id"]){
					     $CDATA=array();
					     $CDATA["ID"]=$_POST["id"];
					     $CDATA["NAME"]=$_POST["name"];
					     $CDATA["PHONE"]=$_POST["phone"];
					     $CDATA["EMAIL"]=$_POST["email"];
					     $CDATA["SID"]=$_POST["sid"];
					     $CDATA["ADDR"]=$_POST["addr"];
					     $CDATA["REMARK"]=$_POST["remark"];
					     UpdDataId($CDATA, "CUST_M");
										$edit=0;
					}
								
}

if ($edit==1){
					//丟至頁面上
					$sql="SELECT * FROM CUST_M WHERE ID='$id'";
					$rows=$conn->Execute($sql);
					$row=$rows->FetchRow();
					$tpl->assign("edata",$row);
}

//庫別
$store_option=array();
$sql="SELECT * FROM STORE_M ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
      $store_option[$row["ID"]]=$row["NAME"];
}
$tpl->assign("store_option",$store_option);

$sql="SELECT * FROM CUST_M ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
         $row["STORE_NAME"]=GetName($row["SID"],"STORE_M");
         $cdata[]=$row;
}



$conn->close();
$tpl->assign("cdata",$cdata);
$tpl->assign("edit", $edit);
$tpl->assign("submit", $submit);
$tpl->assign("title", $title);
$tpl->display($temp_file);
?>
