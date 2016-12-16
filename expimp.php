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

$title="轉倉作業";


//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];
$USTORE=$_COOKIE["wdcbcSTORE"];
$USID=$_COOKIE["wdcbcSTOREID"];
$CTIME=date("Y-m-d H:i:s");
$CDATE=date("Y-m-d");


//供應商
$sql="SELECT ID,NAME FROM VEN_M ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
  $VEN_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("VEN_OPTION",$VEN_OPTION);
	
//$sql="SELECT ID,NAME FROM STORE_M WHERE ID <>$USID ORDER BY ID";
$sql=($_COOKIE["wdcbcSTOREFLAG"]==1)?"SELECT ID,NAME FROM STORE_M where ENABLE='Y' ORDER BY ID":"SELECT ID,NAME FROM STORE_M WHERE ENABLE='Y' AND ID <>$USID ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->fetchRow()){
  $S_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("S_OPTION",$S_OPTION);


//if ($_POST["submit"]=="查詢"){
//不能用submit檢查
if ($_POST["submit"]=="新增"){
  //viewArray($_POST);exit();
  //表頭
  $soid=GenID("SO");
  //print $soid;exit(); 
  $so["ID"]=$soid;
  //轉倉出
  $so["TYPE"]=3;
  //$so["COST"]=$_POST["ttcost"];
  //$so["AMT"]=$_POST["amt"];
  //$so["PROFIT"]=$_POST["amt"]-$_POST["ttcost"];
  //$so["CID"]="";
  //$so["CNAME"]=$_POST["cname"];
  //接收倉庫
  $so["TSID"]=$_POST["tsid"];
  //接收人員
  $so["TEMP"]=$_POST["temp"];
  //待接收
  $so["TFLAG"]="N";
  $so["SID"]=$USID;
  $so["UID"]=$UID;
  $so["CTIME"]=$CTIME;
  ////20131013 add column "REMARK"
  $so["REMARK"]=$_POST[""];
  write_to_table($so, "SO" );
  //表身  以pid為主	    
  if (is_array($_POST["pid"])){
	$pline=$_POST["pline"];
	foreach($_POST["pid"] as $key => $pid){
	  if ($pid){
		$sod["ID"]=$soid;
		$sod["LINE"]=GenLine($soid,"SO_D");
		$sod["PID"]=$pid;
		$sod["PDLINE"]=$pline[$key];
		$sod["QTY"]=1;
		//$sod["PRICE"]=$_POST["cost"];
		$pddata["SID"]=$USID;
		write_to_table($sod, "SO_D" );


		//TRANSACTION
		$txn["ID"]=GenID("TRANSACTION");
		//庫別
		$txn["SID"]=$USID;
		//1:進貨  2.銷貨 3.轉倉
		$txn["IID"]=3;
		$txn["FID"]=$soid;
		$txn["PID"]=$pid;
		$txn["QTY"]=1;
		//與進貨單上一致
		$txn["PRICE"]=0;
		$txn["UID"]=$UID;
		$txn["CTIME"]=date("Y-m-d H:i:s");
		//viewArray($txn);exit();
		write_to_table($txn, "TRANSACTION");

		//更新數量
		$inv["SID"]=$USID;
		//1:在庫 2:轉倉
		//$inv["TYPE"]=2;
		$inv["PID"]=$pid;
		$inv["QTY"]=1;
		//扣掉在庫數
		$sql="UPDATE INVENTORY SET QTY=QTY-".$inv["QTY"]." WHERE SID=".$inv["SID"]." AND TYPE=1 
		  AND PID=".$inv["PID"];
		$conn->Execute($sql);


		//將數量轉至目標倉別的轉倉
		//檢查是否有該產品的轉倉資料
		$sql="SELECT COUNT(*) T FROM INVENTORY WHERE SID=".$_POST["tsid"]." AND TYPE=2 AND PID=".$inv["PID"];
		$rs=$conn->Execute($sql);
		$r=$rs->FetchRow();
		$exp["SID"]=$_POST["tsid"];
		//1:在庫 2:轉倉
		$exp["TYPE"]=2;
		$exp["PID"]=$pid;
		$exp["QTY"]=1;
		if ($r["T"]==0){
		  //目標倉別
		  //viewArray($exp);exit();
		  write_to_table($exp, "INVENTORY");      
		}
		else{
		  $sql="UPDATE INVENTORY SET QTY=QTY+1 WHERE SID=".$_POST["tsid"]." AND TYPE=2 AND PID=".$exp["PID"];
		  $conn->Execute($sql);
		}
		//write_to_table($inv, "INVENTORY");

		//更新PROD_D
		$pd["ID"]=$pid;
		$pd["LINE"]=$pline[$key];
		$pd["TYPE"]=3;
		//改至目標庫別
		$pd["SID"]=$_POST["tsid"];
		$sql="UPDATE PROD_D SET TYPE=".$pd["TYPE"].", SID=".$pd["SID"]." WHERE ID=".$pd["ID"]." AND LINE=".$pd["LINE"];
		$conn->Execute($sql);
	  }
	}
  }
}

//表頭資料
$hdata["EMP_NAME"]=$UNAME;
$hdata["STORE_NAME"]=$USTORE;
$hdata["CDATE"]=$CDATE;



$tpl->assign("hdata",$hdata);
$tpl->assign("title", $title);
$tpl->display($temp_file);
$conn->close();


?>

