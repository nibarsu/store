<?php
include "main.php";
include ('includes/checkUID.php');
include ('includes/tool.php');
//資料庫連接
$conn=CTDB();
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
$UID=1;
//right
//先預設全部
//default template
$temp_file=GetTempFilename(__FILE__,"htm");
  
  
$submit=$_POST["submit"];
$NAME=$_POST["name"];
$PHONE=$_POST["phone"];
$EMAIL=$_POST["email"];
//$ID=GenCustID($conn);
//print $ID; 

if ($_POST["submit"]=="新增"){
								
								if ($NAME){
								//print $submit;exit();
								//$ID=GenCustID($conn);
        $CDATA["ID"]=GenCustID($conn);
								$CDATA["NAME"]=$NAME;
        $CDATA["PHONE"]=$PHONE;
        $CDATA["EMAIL"]=$EMAIL;
								AddCust($conn,$CDATA);
								}
								
								//若有商品代號則新增至CUST_D  顧客購買清單
}

//print $_POST["edit"];
//print_r($_POST);



if ($_POST["submit"]=="編輯"){
								if ($_POST["id"]){
													$sql="UPDATE CUST_M  SET NAME='".$_POST["name"]."',  PHONE='".$_POST["phone"]."',
													EMAIL='".$_POST["email"]."' WHERE ID=".$_POST["id"]."";
													//print $sql;
													if ($conn->Execute($sql)){
																			$edit=0;
													}
													
								}
								
}

if ($edit==1){
					//丟至頁面上
					$sql="SELECT ID,NAME,PHONE,EMAIL FROM CUST_M WHERE ID='$id'";
					$rows=$conn->Execute($sql);
					$row=$rows->FetchRow();
					$tpl->assign("edata",$row);
}


$sql="SELECT ID,NAME,PHONE,EMAIL FROM CUST_M ORDER BY ID";
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
