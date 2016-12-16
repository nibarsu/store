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


$title="檔案匯入";





//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];
$USTORE=$_COOKIE["wdcbcSTORE"];
$USID=$_COOKIE["wdcbcSTOREID"];
$CTIME=date("Y-m-d H:i:s");
$CDATE=date("Y-m-d");

//匯入庫別
$STORE=($_POST["sid"])?$_POST["sid"]:1; //4:oneone 預設晶豪泰
//開帳用
$VENDOR=0;
//資料匯入
if ($_REQUEST["upload"] <> ""){
  if ($_FILES["upload_file"]["name"] <>"" and $_FILES["upload_file"]["size"] >0 ){
	require_once('includes/excel/reader.php');
	// ExcelFile($filename, $encoding);
	$data = new Spreadsheet_Excel_Reader();
	// Set output Encoding.
	$data->setOutputEncoding('UTF-8');
	$data->read($_FILES["upload_file"]["tmp_name"]);
	error_reporting(E_ALL ^ E_NOTICE);
	$total_emp=sizeof($data->sheets[0]['cells']);
	//print_r($data->sheets);exit();
	//viewArray($data->sheets);exit();
	//print "emp:".$total_emp ;exit();
	if ($total_emp > 0){
	  //viewArray($data->sheets[0]['cells']);exit();
	  //類別品名數量成本
	  foreach($data->sheets[0]['cells'] as $row => $val){
		if ($row==1){
		  //欄位名稱
		  $col_name=$val;
		}
		else{
		  //print "val1:".$val[1]." val2:".$val[2]." val3:".$val[3];
		  //exit();
		  if($val[1]!="" and $val[2]!="" and $val[3]!="" and $val[3]!=0){

			//$val[1]    類別
			$PROD_TYPE="";
			//$val[2]    品名
			$PROD_NAME=$val[2];
			$PID="";
			//$val[3]    數量
			$QTY=$val[3];
			//$val[4]    成本
			$COST=$val[4];
			//檢查是否已有相同類別
			$sql="SELECT COUNT(*) T FROM PROD_TYPE WHERE NAME='".$val[1]."'";
			//print $sql;exit();
			$ptrows=$conn->Execute($sql);
			$ptrow=$ptrows->FetchRow();
			if ($ptrow["T"]==0){
			  //新增該類別
			  $DATA=array();
			  $DATA["ID"]=GenID("PROD_TYPE");
			  $PROD_TYPE=$DATA["ID"];
			  $DATA["NAME"]=$val[1];
			  write_to_table($DATA, "PROD_TYPE");
			}
			else{
			  //取得PROD_TYPE
			  $sql="SELECT ID FROM PROD_TYPE WHERE NAME='".$val[1]."' limit 0,1";
			  $rows=$conn->Execute($sql);
			  $row=$rows->FetchRow();
			  $PROD_TYPE=$row["ID"];
			}

			//品名 PROD_M
			$pdata=array();
			$sql="SELECT COUNT(*) T FROM PROD_M WHERE NAME='".$val[2]."'";
			//print $sql;exit();
			$ptrows=$conn->Execute($sql);
			$ptrow=$ptrows->FetchRow();
			//print "T:".$ptrow["T"];exit();
			if ($ptrow["T"]==0){
			  $PID =GenID("PROD_M");
			  $pdata["ID"]=$PID;
			  $pdata["NAME"]=$val[2];
			  $PROD_NAME=$val[2];
			  $PM_CODE=sprintf("%03d",$PROD_TYPE).sprintf("%06d",$PID);
			  $pdata["CODE"]=$PM_CODE;
			  $pdata["TYPE"]=$PROD_TYPE;
			  $pdata["VID"]=0;
			  $pdata["UID"]        =$UID;
			  $pdata["CTIME"]      =date("Y-m-d H:i:s");
			  //$pdata["COST"]       =$val[4];
			  write_to_table($pdata, "PROD_M" );
			}
			else{
			  //取得PROD_TYPE
			  $sql="SELECT * FROM PROD_M WHERE NAME='".$val[2]."' limit 0,1";
			  //print $sql;exit(); 
			  $rows=$conn->Execute($sql);
			  $row=$rows->FetchRow();
			  $PID=$row["ID"];
			  $PM_CODE=$row["CODE"];
			}

			//print $PID;exit();
			//PROD_D
			for($i=1;$i<=$QTY;$i++){
			  $pddata["ID"]=$PID;
			  $pddata["LINE"]=GenLine($PID,"PROD_D");
			  //RT_ID
			  $pddata["RT_ID"]=$ID;
			  $pddata["CODE"]=$PM_CODE.sprintf("%04d",$pddata["LINE"]);
			  $pddata["SID"]=$STORE;
			  //1:進貨  2.銷貨 3.轉倉
			  $pddata["TYPE"]=1;
			  $pddata["COST"]=$COST;
			  write_to_table($pddata, "PROD_D" );
			}

			$txn["ID"]=GenID("TRANSACTION");
			//庫別
			$txn["SID"]=$STORE;
			//1:進貨  2.銷貨 3.轉倉
			$txn["IID"]=1;
			$txn["PID"]=$PID;
			$txn["QTY"]=$QTY;
			//與進貨單上一致
			$txn["PRICE"]=0;
			$txn["UID"]=$UID;
			$txn["CTIME"]=date("Y-m-d H:i:s");
			//viewArray($txn);exit();
			write_to_table($txn, "TRANSACTION");

			//更新數量
			//庫別 產品 數量

			$inv["SID"]=$STORE;
			$inv["TYPE"]=1;
			$inv["PID"]=$PID;
			$inv["QTY"]=$QTY;
			$sql="SELECT COUNT(*) T FROM INVENTORY WHERE SID=".$inv["SID"]." AND TYPE=".$inv["TYPE"]." AND PID=".$inv["PID"];
			$irows=$conn->Execute($sql);
			$irow=$irows->FetchRow();
			if ($irow["T"]==0){
			  write_to_table($inv, "INVENTORY");
			}
			else{
			  $sql="UPDATE INVENTORY SET QTY=QTY+".$inv["QTY"]." WHERE SID=".$inv["SID"]." AND TYPE=".$inv["TYPE"]." AND PID=".$inv["PID"];
			  $conn->Execute($sql);
			}
		  }
		}

	  }



	}


  }

}


$S_OPTION=array();
$sql="SELECT ID,NAME FROM STORE_M WHERE ENABLE='Y' ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
  $S_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("S_OPTION",$S_OPTION);






//表頭資料
$hdata["EMP_NAME"]=$UNAME;
$hdata["STORE_NAME"]=$USTORE;
$hdata["CDATE"]=$CDATE;



$tpl->assign("hdata",$hdata);
$tpl->assign("title", $title);
$tpl->display($temp_file);
$conn->close();
?>

