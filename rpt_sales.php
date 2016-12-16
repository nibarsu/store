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


$title="員工業績查詢";





//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];
$USTORE=$_COOKIE["wdcbcSTORE"];
$USID=$_COOKIE["wdcbcSTOREID"];
$CTIME=date("Y-m-d H:i:s");
$CDATE=date("Y-m-d");


//預設時間
$SDATE=(($_POST["SDATE"])?$_POST["SDATE"]:date("Y-m-d"))." 00:00:00";
$EDATE=(($_POST["EDATE"])?$_POST["EDATE"]:date("Y-m-d"))." 23:59:59";


//區間
$sql="SELECT SO.ID, S.NAME STORE_NAME,E.NAME EMP_NAME,SO.COST,SO.AMT,SO.PROFIT,SO.CTIME,SO.TYPE
FROM SO SO,STORE_M S,EMP E
  WHERE SO.SID=S.ID AND SO.UID=E.ID
AND SO.TYPE in (2,5)
  AND SO.CTIME BETWEEN '".$SDATE."' AND '".$EDATE."' ";
  if ($_POST["submit"]=="查詢"){
	//viewArray($_POST);
	//庫別
	if ($_POST["uid"]){
	  $sql.=" AND SO.UID=".$_POST["uid"]." ";
	}

	//庫別
	if ($_POST["sid"]){
	  $sql.=" AND SO.SID=".$_POST["sid"]." ";
	}

	$sql.=" ORDER BY SO.CTIME DESC";


	//print $sql;
	$ttamt=0;
	$ttprofit=0;
	$rs=$conn->Execute($sql);
	while($r=$rs->FetchRow()){

	  $r["PROFIT"]=($r["TYPE"]==2)?$r["PROFIT"]:-$r["PROFIT"];
	  $ttprofit+=$r["PROFIT"];
	  $ttamt+=$r["AMT"];
	  $RDATA[]=$r;
	}
	$tpl->assign("RDATA",$RDATA);
	$tpl->assign("ttamt",$ttamt);
	$tpl->assign("ttprofit",$ttprofit);
  }
$tpl->assign("eSDATE",substr($SDATE,0,10));
$tpl->assign("eEDATE",substr($EDATE,0,10));
$tpl->assign("euid",$_POST["uid"]);
$tpl->assign("esid",$_POST["sid"]);

//產生員工銷售總計
if ($_REQUEST["gen_sales_total"] <> ""){
  $filename="sales_ttl";
  // create a dummy array
  $hdata=array ("庫別", "姓名", "銷售金額","成本","利潤");
  //excel內容

  $sql="SELECT SID,UID,TYPE
	SUM(COST) COST,SUM(AMT) AMT,SUM(PROFIT) PROFIT FROM so SO WHERE SO.CTIME BETWEEN '$SDATE' AND '$EDATE'
	AND TYPE IN (2,5) ";
  $sql="SELECT SID,UID,TYPE,
	SUM(COST) COST,SUM(AMT) AMT,SUM(PROFIT) PROFIT 
	  FROM so SO
	  WHERE CTIME BETWEEN '$SDATE' AND '$EDATE'
	  AND TYPE IN (2,5) ";


  //員工
  if ($_POST["uid"]){
	$sql.=" AND SO.UID=".$_POST["uid"]." ";
  }

  //庫別
  if ($_POST["sid"]){
	$sql.=" AND SO.SID=".$_POST["sid"]." ";
	$filename.="_".$_POST["sid"];
  }

  $sql.="GROUP BY SID,UID,TYPE ORDER BY CTIME";

  //print $sql;
  $rows=$conn->Execute($sql);
  $tamt=0;
  $tcost=0;
  $tpft=0;
  while($row=$rows->FetchRow()){
	$UID=$row["UID"];

	$SID=$row["SID"];
	////20120702
	//$row["PROFIT"]=($row["TYPE"]==2)?$row["PROFIT"]:-$row["PROFIT"];
	$row["AMT"]=($row["TYPE"]==5)?-$row["AMT"]:$row["AMT"];
	$row["COST"]=($row["TYPE"]==5)?-$row["COST"]:$row["COST"];
	$row["PROFIT"]=($row["TYPE"]==5)?-$row["PROFIT"]:$row["PROFIT"];

	$tdata[] =array ("STORE_NAME"=>GetName($SID,"store_m"), "EMP_NAME"=> GetName($UID,"emp"), 
		"AMT"=>$row["AMT"],"COST"=>$row["COST"],
		"PROFIT"=>$row["PROFIT"]);
	$tamt=$tamt+$row["AMT"];
	$tcost=$tcost+$row["COST"];
	$tpft=$tpft+$row["PROFIT"];

  }
  $tdata[] =array ("STORE_NAME"=>"合計", "EMP_NAME"=> "", 
	  "AMT"=>$tamt,"COST"=>$tcost,
	  "PROFIT"=>$tpft);
  gen_file($filename,$hdata,$tdata);
  exit();

}


//產生銷售總表
if ($_REQUEST["gen_sales_sum"] <> ""){
  $filename="sales_sum";
  // create a dummy array
  $hdata=array ("庫別", "姓名", "銷售金額","成本","利潤","掛帳","現金","刷卡","分期","外幣","訂金","備註","日期","客戶");
  //excel內容

  $sql="SELECT CTIME,SID,UID,TYPE,UNPAY,CASH,CARD,LAYAPAY,FCURR,FCASH,PREPAY,REMARK,SO.CNAME,
	SUM(COST) COST,SUM(AMT) AMT,SUM(PROFIT) PROFIT FROM so SO WHERE SO.CTIME BETWEEN '$SDATE' AND '$EDATE'
	  AND TYPE IN (2,5) ";


  //員工
  if ($_POST["uid"]){
	$sql.=" AND SO.UID=".$_POST["uid"]." ";
  }

  //庫別
  if ($_POST["sid"]){
	$sql.=" AND SO.SID=".$_POST["sid"]." ";
	$filename.="_".$_POST["sid"];
  }

  $sql.="GROUP BY CTIME,SID,UID,TYPE,UNPAY,CASH,CARD,LAYAPAY,FCURR,FCASH,PREPAY,REMARK ORDER BY CTIME";

  //print $sql;
  $rows=$conn->Execute($sql);
  $tamt=0;
  $tcost=0;
  $tpft=0;
  while($row=$rows->FetchRow()){
	$UID=$row["UID"];
	$row["AMT"]=($row["TYPE"]==5)?-$row["AMT"]:$row["AMT"];
	$row["COST"]=($row["TYPE"]==5)?-$row["COST"]:$row["COST"];
	$row["PROFIT"]=($row["TYPE"]==5)?-$row["PROFIT"]:$row["PROFIT"];
	$SID=$row["SID"];
	$tdata[] =array ("STORE_NAME"=>GetName($SID,"store_m"), "EMP_NAME"=> GetName($UID,"emp"), 
		"AMT"=>$row["AMT"],"COST"=>$row["COST"],
		"PROFIT"=>$row["PROFIT"],"UNPAY"=>$row["UNPAY"],"CASH"=>$row["CASH"],"CARD"=>$row["CARD"],
		"LAYPAY"=>$row["LAYAPAY"],
		"FCURR"=>$row["FCURR"].":".$row["FCASH"],
		"PREPAY"=>$row["PREPAY"],
		"REMARK"=>$row["REMARK"],"CTIME"=>$row["CTIME"]);
	$tamt=$tamt+$row["AMT"];
	$tcost=$tcost+$row["COST"];
	$tpft=$tpft+$row["PROFIT"];

  }
  $tdata[] =array ("STORE_NAME"=>"合計", "EMP_NAME"=> "", 
	  "AMT"=>$tamt,"COST"=>$tcost,
	  "PROFIT"=>$tpft,"UNPAY"=>"","CASH"=>"","CARD"=>"",
	  "LAYPAY"=>"",
	  "FCURR"=>"",
	  "PREPAY"=>"",
	  "REMARK"=>"","CTIME"=>"");
  gen_file($filename,$hdata,$tdata);
  exit();

}


if ($_REQUEST["gen_sales_dtl"] <> ""){
  $filename="sales_dtl";

  $hdata =array ("庫別", "姓名","品名", "銷售金額","成本","利潤","掛帳","現金","刷卡","分期","外幣","訂金","備註","日期","客戶");

  $sql="SELECT SO.ID,SID,UID,TYPE,COST,AMT,PROFIT,UNPAY,CASH,CARD,LAYAPAY,FCURR,FCASH,PREPAY,REMARK,SO.CTIME,SO.CNAME
	FROM so SO WHERE SO.CTIME BETWEEN '$SDATE' AND '$EDATE' AND TYPE IN (2,5) ";


  //員工
  if ($_POST["uid"]){
	$sql.=" AND SO.UID=".$_POST["uid"]." ";
  }

  //庫別
  if ($_POST["sid"]){
	$sql.=" AND SO.SID=".$_POST["sid"]." ";
	$filename.="_".$_POST["sid"];
  }

  $sql.="ORDER BY SO.SID,SO.UID";

  //print $sql;

  $rows=$conn->Execute($sql);
  $tamt=0;
  $tcost=0;
  $tpft=0;
  while($row=$rows->FetchRow()){
	$UID=$row["UID"];

	$SID=$row["SID"];

	$row["AMT"]=($row["TYPE"]==5)?-$row["AMT"]:$row["AMT"];
	$row["COST"]=($row["TYPE"]==5)?-$row["COST"]:$row["COST"];
	$row["PROFIT"]=($row["TYPE"]==5)?-$row["PROFIT"]:$row["PROFIT"];

	$row["FSTR"]=($row["FCURR"])?$row["FCURR"].":".$row["FCASH"]:"";
	$tdata[] =array ("STORE_NAME"=>GetName($SID,"store_m"),"EMP_NAME"=> GetName($UID,"emp"),
		"--"=>"--","AMT"=> $row["AMT"],"COST"=>$row["COST"],
		"PROFIT"=>$row["PROFIT"],"UNPAY"=>$row["UNPAY"],"CASH"=>$row["CASH"],"CARD"=>$row["CARD"],
		"LAYAPAY"=>$row["LAYAPAY"],
		"FSTR"=>$row["FSTR"],"PREPAY"=>$row["PREPAY"],"REMARK"=>$row["REMARK"],"CTIME"=>$row["CTIME"],
		"CNAME"=>$row["CNAME"]);
	$tamt=$tamt+$row["AMT"];
	$tcost=$tcost+$row["COST"];
	$tpft=$tpft+$row["PROFIT"];
	//$sql="SELECT FROM SO_D D WHERE D.ID=".$row["ID"]."";
	$sql="SELECT PT.NAME PROD_TYPE,PM.NAME PROD_NAME,PD.CODE,PD.COST 
	  FROM so_d D,prod_m PM,prod_d PD,prod_type PT WHERE D.ID=".$row["ID"]." 
	  AND D.PID=PM.ID 
	  AND D.PID=PD.ID 
	  AND D.PDLINE=PD.LINE 
	  AND PM.TYPE=PT.ID ORDER BY D.ID,D.LINE";
	$drows=$conn->Execute($sql);
	while($drow=$drows->FetchRow()){
	  $tdata[] =array ("STORE_NAME"=>GetName($SID,"store_m"), "EMP_NAME"=>GetName($UID,"emp"),
		  "PROD_NAME"=>$drow["PROD_TYPE"]."-".$drow["PROD_NAME"], "aa"=>"-","COST"=>$drow["COST"],"bb"=>"-");       
	}
  }
  $tdata[] =array ("STORE_NAME"=>"合計","EMP_NAME"=>"",
	  "--"=>"--","AMT"=> $tamt,"COST"=>$tcost,
	  "PROFIT"=>$tpft,"UNPAY"=>"","CASH"=>"","CARD"=>"",
	  "LAYAPAY"=>"",
	  "FSTR"=>"","PREPAY"=>"","REMARK"=>"","CTIME"=>"",
	  "CNAME"=>"");
  //viewarray($tdata);exit();
  gen_file($filename,$hdata,$tdata);
  exit();

}



//
$S_OPTION=array();
$S_OPTION[]="";
$sql="SELECT ID,NAME FROM store_m WHERE ENABLE='Y' ORDER BY ID";
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

