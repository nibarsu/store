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

$title="進貨輸入";


//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];
$USTORE=$_COOKIE["wdcbcSTORE"];
$USID=$_COOKIE["wdcbcSTOREID"];
$CTIME=date("Y-m-d H:i:s");
$CDATE=date("Y-m-d");



if ($_POST["submit"]=="新增"){
  //viewArray($_POST);exit();
  //表頭
  $ID                   =GenID("RT");
  $hdata["ID"]         =$ID;
  $hdata["VID"]        =$_POST["vid"];
  $hdata["PFLAG"]      =$_POST["pflag"];
  //應稅免稅
  $hdata["TFLAG"]      =$_POST["tflag"];
  $hdata["AMT"]        =$_POST["amt"];
  $hdata["REMARK"]     =$_POST["remark"];
  //$hdata["SID"]        =$USID;
  $hdata["SID"]        =$_POST["sid"];
  $hdata["UID"]        =$UID;
  //$hdata["CTIME"]      =date("Y-m-d H:i:s");
  $hdata["CTIME"]      =$_POST["ctime"]." ".date("H:i:s");

  write_to_table($hdata, "RT" ); 
  //表身
  //以品名為主  無品名者不輸入
  if (is_array($_POST["pid"])){
	$PROD_LIST  =array();
	$PT   =array();
	$QTY  =array();
	$COST =array();
	$PROD_LIST=$_POST["pid"];
	$PT=$_POST["pt"];
	$QTY=$_POST["qty"];
	$COST=$_POST["cost"];
	//20110501新增單項成本
	$DCOST=$_POST["dcost"];
	$line=0;
	foreach($PROD_LIST as $key => $val){
	  if ($val){
		$ddata=array();
		$pdata=array();
		$PID ="";
		$AVG_COST=0;
		$line++;
		//檢查品名是否有一樣的
		$sql="SELECT COUNT(*) T FROM PROD_M WHERE NAME='$val'";
		$rs=$conn->Execute($sql);
		$r=$rs->FetchRow();
		if ($r["T"]==0){
		  $PID =GenID("PROD_M");
		  $pdata["ID"]=$PID;
		  //$pdata["LINE"]=GenLine2($PT[$key],"PROD_M");
		  //$PT[$key]品名
		  $pdata["NAME"]=$val;
		  //20110315確認為同一次進貨的同一商品編碼不同
		  $PM_CODE=sprintf("%03d",$PT[$key]).sprintf("%06d",$PID);
		  $pdata["CODE"]=$PM_CODE;
		  $pdata["TYPE"]=$PT[$key];
		  $pdata["VID"]=$_POST["vid"];
		  $pdata["UID"]        =$UID;
		  $pdata["CTIME"]      =date("Y-m-d H:i:s");
		  //平均單價(成本) 
		  //20110326此欄位改為 控制用成本 平均單價 改存至PROD_D.COST
		  //第一次存平均單價
		  $pdata["COST"]       =round($COST[$key]/$QTY[$key]);
		  $AVG_COST       =round($COST[$key]/$QTY[$key]);
		  //viewArray($pdata);exit();
		  write_to_table($pdata, "PROD_M" );
		}
		else{
		  $sql="SELECT * FROM PROD_M WHERE NAME='$val' LIMIT 0,1";
		  $prows=$conn->Execute($sql);
		  $prow=$prows->FetchRow();
		  $PID =$prow["ID"];
		  $PM_CODE        =$prow["CODE"];
		  $AVG_COST       =round($COST[$key]/$QTY[$key]);
		  //這裏可以加每次進貨改平均成本
		}


		for($i=1;$i<=$QTY[$key];$i++){
		  $pddata["ID"]=$PID;
		  $pddata["LINE"]=GenLine($PID,"PROD_D");
		  //RT_ID
		  $pddata["RT_ID"]=$ID;
		  $pddata["CODE"]=$PM_CODE.sprintf("%04d",$pddata["LINE"]);
		  //$pddata["SID"]=$USID;
		  $pddata["SID"]=$_POST["sid"];
		  //1:進貨  2.銷貨 3.轉倉
		  $pddata["TYPE"]=1;
		  //$pddata["COST"]=$AVG_COST;
		  //20110501改存放單項成本
		  $pddata["COST"]=$DCOST[$key];
		  write_to_table($pddata, "PROD_D" );
		}

		$ddata["ID"]=$ID;
		//有效的進貨項目
		$ddata["LINE"]=$line;
		$ddata["PID"]=$PID;
		$ddata["QTY"]=$QTY[$key];
		$ddata["SUBAMT"]=$COST[$key];
		$ddata["PRICE"]=round($COST[$key]/$QTY[$key]);
		write_to_table($ddata, "RT_D" );


		$txn["ID"]=GenID("TRANSACTION");
		//庫別
		//$txn["SID"]=$USID;
		$txn["SID"]=$_POST["sid"];
		//1:進貨  2.銷貨 3.轉倉
		$txn["IID"]=1;
		//記錄單號
		$txn["FID"]=$ID;
		$txn["PID"]=$PID;
		$txn["QTY"]=$QTY[$key];
		//與進貨單上一致
		$txn["PRICE"]=$COST[$key];
		$txn["UID"]=$UID;
		$txn["CTIME"]=date("Y-m-d H:i:s");
		//viewArray($txn);exit();
		write_to_table($txn, "TRANSACTION");

		//更新數量
		//庫別 產品 數量

		//$inv["SID"]=$USID;
		$inv["SID"]=$_POST["sid"];
		$inv["TYPE"]=1;
		$inv["PID"]=$PID;
		$inv["QTY"]=$QTY[$key];
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


  //跳出列印畫面
  print "<script>window.open('code_print.php?rtid=$ID', 'Barcode') ;</script>";
  //print "<script>window.open('code_print2.php?rtid=$ID', 'Barcode') ;</script>";
}


//供應商
$sql="SELECT ID,NAME FROM VEN_M ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
  $VEN_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("VEN_OPTION",$VEN_OPTION);

//產品類別
$TYPE_OPTION=array();
$sql="SELECT * FROM PROD_TYPE WHERE ENABLE='Y' ORDER BY ID";
$rs=$conn->Execute($sql);
while($r=$rs->FetchRow()){
 $TYPE_OPTION[$r["ID"]]=$r["NAME"];
}
$tpl->assign("TYPE_OPTION",$TYPE_OPTION);

//庫別
$S_OPTION=array();
//$S_OPTION[]="";
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
$hdata["STORE_ID"]=$USID;



$INV=new invclass;
$INV->conn=$conn;
$ndata=array();
$ndata=$INV->GetInvData($USID,"%");
//viewArray($ndata);

$tpl->assign("ndata",$ndata);
$tpl->assign("hdata",$hdata);
$tpl->assign("title", $title);
$tpl->display($temp_file);
$conn->close();
?>

