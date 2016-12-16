<?php
include "main.php";
include ('includes/tool.php');
//資料庫連接
$conn=CTDB();
$title="商品類別編輯";

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
$UID=1;
//right
//先預設全部
//default template
$temp_file=GetTempFilename(__FILE__,"htm");

$Ob=  new Bclass();
$Ob->conn=$conn;
$Ob->TableName="PROD_TYPE";
//print_r($Ob);
$submit=$_POST["submit"];
$NAME=$_POST["name"];
//$ID=GenCustID($conn);
//print $ID; 

if ($_POST["submit"]=="新增"){
								
								if ($NAME){
													$DATA["NAME"]=$NAME;
								     $Ob->AddData($DATA);
        }
}

//print $_POST["edit"];
//print_r($_POST);



if ($_POST["submit"]=="編輯"){
								//更新
								if ($_POST["id"]){
													$D["ID"]=$_POST["id"];
													$D["NAME"]=$_POST["name"];
													$Ob->UpdData($D);
													//清除編輯狀態
													$edit=0;
													//跳回原畫面
								}
								
}

if ($edit==1){
					//丟至頁面上
					$Ob->GetData($id);
					$tpl->assign("edata",$Ob->Data);
}

//$sql="SELECT ID,NAME,PHONE,ADDR FROM VEN_M ORDER BY ID";
//$rows=$conn->Execute($sql);
//while($row=$rows->FetchRow()){
//         $cdata[]=$row;
//}

$cdata=$Ob->GetDatas();



$conn->close();
$tpl->assign("cdata",$cdata);
$tpl->assign("edit", $edit);
$tpl->assign("submit", $submit);
$tpl->assign("title", $title);
$tpl->display($temp_file);
?>
