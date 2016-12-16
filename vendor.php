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

$title="廠商資料編輯";

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


if ($_REQUEST["gen_vendor_list"] <> ""){
     $filename="vendor_list";
     $hdata =array ("廠商名稱", "電話", "手機","傳真","業務姓名","地址");
     //excel內容  開帳用的不要
     $sql="SELECT *
              FROM VEN_M WHERE ID <> 1 AND STATUS='O'
             ORDER BY ID";
     //print $sql;
     $rows=$conn->Execute($sql);
     while($row=$rows->FetchRow()){
           	$tdata[]=array("NAME"=>$row["NAME"],"PHONE1"=>$row["PHONE1"],"PHONE2"=>$row["PHONE2"],"FAX"=>$row["FAX"],"SNAME"=>$row["SNAME"],"ADDR"=>$row["ADDR"]);    
     }
     gen_file($filename,$hdata,$tdata);
	    
}

if ($_POST["submit"]=="新增"){
								
					if ($_POST["name"]){
					     $DATA=array();
					     $DATA["ID"]=GenID("VEN_M");
										$DATA["NAME"]=$_POST["name"];
										$DATA["SNAME"]=$_POST["sname"];
										$DATA["PHONE1"]=$_POST["phone1"];
										$DATA["PHONE2"]=$_POST["phone2"];
										$DATA["FAX"]=$_POST["fax"];
										$DATA["ADDR"]=$_POST["addr"];
										write_to_table($DATA, "VEN_M" );
     }
}

//print $_POST["edit"];
//print_r($_POST);



if ($_POST["submit"]=="編輯"){
					//更新
					if ($_POST["id"]){
					     $DATA=array();
										$DATA["ID"]=$_POST["id"];
										$DATA["NAME"]=$_POST["name"];
										$DATA["SNAME"]=$_POST["sname"];
										$DATA["PHONE1"]=$_POST["phone1"];
										$DATA["PHONE2"]=$_POST["phone2"];
										$DATA["FAX"]=$_POST["fax"];
										$DATA["ADDR"]=$_POST["addr"];
										UpdDataId($DATA, "VEN_M");
										//清除編輯狀態
										$edit=0;
										//跳回原畫面
					}
								
}

if ($edit==1){
					//丟至頁面上
					$data="";
					$data=FetchRow($id,"VEN_M");
					$tpl->assign("edata",$data);
}
if ($del==1){
				$sql="UPDATE VEN_M SET STATUS='C' WHERE ID=$id";
				$conn->Execute($sql);
}


$sql="SELECT ID,NAME,PHONE1,PHONE2,FAX,SNAME,ADDR FROM VEN_M WHERE STATUS='O' ORDER BY ID";
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
