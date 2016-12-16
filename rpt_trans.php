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


$title="進出貨明細";





//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];
$USTORE=$_COOKIE["wdcbcSTORE"];
$USID=$_COOKIE["wdcbcSTOREID"];
$CTIME=date("Y-m-d H:i:s");
$CDATE=date("Y-m-d");


//預設時間
$SDATE=(($_POST["SDATE"])?$_POST["SDATE"]:date("Y-m-d",mktime()-86400*7))." 00:00:00";
$EDATE=(($_POST["EDATE"])?$_POST["EDATE"]:date("Y-m-d"))." 23:59:59";




if ($_POST["submit"]=="查詢"){
  //viewArray($_POST);
  //區間
  $sql="SELECT T.*,S.NAME STORE_NAME,PT.NAME PROD_TYPE,PM.NAME PROD_NAME,
	E.NAME EMP_NAME,TT.NAME TRANS_NAME
	  FROM TRANSACTION T,STORE_M S,PROD_M PM,PROD_TYPE PT,EMP E,TRANS_TYPE TT
	  WHERE T.SID=S.ID AND T.PID=PM.ID AND PM.TYPE=PT.ID
	  AND T.UID=E.ID AND T.IID=TT.ID
	  AND T.CTIME BETWEEN '".$SDATE."' AND '".$EDATE."' ";
  //庫別
  if ($_POST["sid"]){
	$sql.=" AND T.SID=".$_POST["sid"]." ";
  }

  //產品類別
  if ($_POST["type"]){
	$sql.=" AND PM.TYPE=".$_POST["type"]." ";
  }

  //交易類別
  if ($_POST["iid"]){
	$sql.=" AND T.IID=".$_POST["iid"]." ";
  }

  //品名
  if ($_POST["name"]){
	$sql.=" AND PM.NAME like '%".$_POST["name"]."%' ";
  }
  $sql.=" ORDER BY T.CTIME DESC";


  //print $sql;
  $rs=$conn->Execute($sql);
  while($r=$rs->FetchRow()){
	$RDATA[]=$r;
  }
  $tpl->assign("RDATA",$RDATA);
}

$tpl->assign("eSDATE",substr($SDATE,0,10));
$tpl->assign("eEDATE",substr($EDATE,0,10));
$tpl->assign("ename",$_POST["name"]);
$tpl->assign("eiid",$_POST["iid"]);
$tpl->assign("etype",$_POST["type"]);
$tpl->assign("esid",$_POST["sid"]);




//進貨明細
if ($_REQUEST["gen_tran_dtl2"] <> ""){
  //20131013 add total amount
  $total_amount=0;
  $filename="trans_rec";
  // create a dummy array
  $hdata =array ("庫別","單號", "廠商","類別", "品名","交易類別","數量","金額","交易人員","交易日期","備註","付款日期","付款人員");
  //excel內容
  $sql="SELECT RT.ID,RT.SID,RT.REMARK,RT.VID,PM.TYPE,PM.NAME PROD_NAME,D.QTY,D.SUBAMT,RT.UID,RT.CTIME,RT.PTIME,RT.PUID
	FROM RT_D D,PROD_M PM,RT
	WHERE RT.ID=D.ID
	AND D.PID=PM.ID
	AND RT.CTIME BETWEEN '".$SDATE."' AND '".$EDATE."' ";
  //庫別
  if ($_POST["sid"]){
	$sql.=" AND RT.SID=".$_POST["sid"]." ";

	$filename.="_s".$_POST["sid"];
  }

  //產品類別
  if ($_POST["type"]){
	$sql.=" AND PM.TYPE=".$_POST["type"]." ";
	$filename.="_t".$_POST["type"];
  }



  //品名
  if ($_POST["ename"]){
	$sql.=" AND PM.NAME like '%".$_POST["ename"]."%' ";
	//$filename.="_n".$_POST["name"];
  }

  //供應商
  if ($_POST["vid"]){
	$sql.=" AND RT.VID=".$_POST["vid"]." ";
  }

  $sql.=" ORDER BY RT.CTIME DESC,RT.VID,RT.ID ";

  $rows=$conn->Execute($sql);
  //print $sql;
  $TEMP_ID="";
  while($row=$rows->FetchRow()){
	if ($TEMP_ID){
	  if ($TEMP_ID==$row["ID"]){
		$row["REMARK"]="";
	  }
	  else{
		$TEMP_ID=$row["ID"];   
	  }
	}
	else{
	  $TEMP_ID=$row["ID"];
	}

	$row["STORE_NAME"]=GetName($row["SID"],"STORE_M");
	$row["VENDOR_NAME"]=GetName($row["VID"],"VEN_M");
	$row["PROD_TYPE"]=GetName($row["TYPE"],"PROD_TYPE");
	$row["TRANS_NAME"]="進貨";
	$row["EMP_NAME"]=GetName($row["UID"],"EMP");
	//付款日期 人員
	$row["PAYER"]=GetName($row["PUID"],"EMP");
    $total_amount = $total_amount + $row["SUBAMT"];
	$tdata[]=array($row["STORE_NAME"],$row["ID"],$row["VENDOR_NAME"],$row["PROD_TYPE"],$row["PROD_NAME"],$row["TRANS_NAME"],$row["QTY"],
		$row["SUBAMT"],$row["EMP_NAME"],$row["CTIME"],$row["REMARK"],$row["PTIME"],$row["PAYER"]);    
  }

  $tdata[]=array("","","","","","","",$total_amount,"","","","","");    
  gen_file($filename,$hdata,$tdata);

}


//出貨明細
if ($_REQUEST["gen_tran_dtl3"] <> ""){
  $filename="trans_shp";
  // create a dummy array
  $hdata=array ("庫別","單號", "廠商","類別", "品名","條碼","交易類別","數量","金額","交易人員","交易日期","備註","客戶");
  //excel內容
  $sql="SELECT SO.ID,SO.REMARK,PM.TYPE,PM.NAME PROD_NAME,PD.CODE,D.QTY,SO.SID,SO.UID,SO.CTIME,PD.RT_ID,SO.CNAME
  ,PD.COST
	FROM SO_D D,PROD_D PD,PROD_M PM,SO
	WHERE SO.ID=D.ID
	AND D.PID=PM.ID
	AND D.PDLINE=PD.LINE
	AND PM.ID=PD.ID
	AND SO.TYPE=2
	AND SO.CTIME BETWEEN '".$SDATE."' AND '".$EDATE."' ";
  //庫別
  if ($_POST["sid"]){
	$sql.=" AND SO.SID=".$_POST["sid"]." ";
	$filename.="_".$_POST["sid"];
  }

  //產品類別
  if ($_POST["type"]){
	$sql.=" AND PM.TYPE=".$_POST["type"]." ";
	$filename.="_".$_POST["type"];
  }

  ////交易類別
  //if ($_POST["iid"]){
  //     $sql.=" AND T.IID=".$_POST["iid"]." ";
  //}

  //品名
  if ($_POST["ename"]){
	$sql.=" AND PM.NAME like '%".$_POST["ename"]."%' ";
  }

  $sql.=" ORDER BY SO.CTIME DESC,SO.ID ";
  $rows=$conn->Execute($sql);
  //print $sql;
  $TEMP_ID="";
  while($row=$rows->FetchRow()){
	if ($TEMP_ID){
	  if ($TEMP_ID==$row["ID"]){
		$row["REMARK"]="";
	  }
	  else{
		$TEMP_ID=$row["ID"];   
	  }
	}
	else{
	  $TEMP_ID=$row["ID"];
	} 

	$row["STORE_NAME"]=GetName($row["SID"],"STORE_M");

	if ($row["RT_ID"]){
	  $sql="SELECT VID FROM RT WHERE ID=".$row["RT_ID"];
	  $Rrs=$conn->Execute($sql);
	  $Rr=$Rrs->FetchRow();

	  $row["VENDOR_NAME"]=($Rr["VID"])?GetName($Rr["VID"],"VEN_M"):"開帳用";
	}
	else{
	  $row["VENDOR_NAME"]="開帳用";
	}
	$row["PROD_TYPE"]=GetName($row["TYPE"],"PROD_TYPE");
	$row["TRANS_NAME"]="出貨";
	$row["EMP_NAME"]=GetName($row["UID"],"EMP");
	$tdata[] =array($row["STORE_NAME"],$row["ID"],$row["VENDOR_NAME"],$row["PROD_TYPE"]
	,$row["PROD_NAME"],"CODE"=>$row["CODE"],$row["TRANS_NAME"]
	,$row["QTY"],$row["COST"],$row["EMP_NAME"],$row["CTIME"],$row["REMARK"],$row["CNAME"]);    
  }
  gen_file($filename,$hdata,$tdata);

}
//轉倉明細
if ($_REQUEST["gen_tran_dtl4"] <> ""){
  //summary
  $filename="trans_tra";
  // create a dummy array
  //20120105轉倉明細格式：庫別、廠商、品名、條碼、交易類別、數量、交易人員、交易日期(Y-m-d)、接收庫別、接收人員、備註
  //$hdata =array ("庫別","單號", "廠商","類別", "品名","條碼","交易類別","數量","交易人員","交易日期","接收庫別","接收人員","接收狀態","備註");
  $hdata =array ("庫別","廠商","品名","條碼","交易類別","數量","交易人員","交易日期","接收庫別","接收人員","備註");

  //excel內容
  $sql="SELECT SO.ID,SO.REMARK,PM.TYPE,PM.NAME PROD_NAME,PD.CODE,D.QTY,SO.SID,SO.UID,SO.CTIME,PD.RT_ID,SO.TSID,SO.TEMP,SO.TFLAG
	FROM SO_D D,PROD_D PD,PROD_M PM,SO
	WHERE SO.ID=D.ID
	AND D.PID=PM.ID
	AND D.PDLINE=PD.LINE
	AND PM.ID=PD.ID
	AND SO.TYPE=3
	AND SO.CTIME BETWEEN '".$SDATE."' AND '".$EDATE."' ";
  //庫別
  if ($_POST["sid"]){
	$sql.=" AND (SO.SID=".$_POST["sid"]." OR SO.TSID=".$_POST["sid"].") ";

	$filename.="_".$_POST["sid"];
  }

  //產品類別
  if ($_POST["type"]){
	$sql.=" AND PM.TYPE=".$_POST["type"]." ";
  }

  ////交易類別
  //if ($_POST["iid"]){
  //     $sql.=" AND T.IID=".$_POST["iid"]." ";
  //}

  //品名
  if ($_POST["ename"]){
	$sql.=" AND PM.NAME like '%".$_POST["ename"]."%' ";
  }

  $TEMP_ID="";
  $rows=$conn->Execute($sql);
  //print $sql;
  while($row=$rows->FetchRow()){
	if ($TEMP_ID){
	  if ($TEMP_ID==$row["ID"]){
		$row["REMARK"]="";
	  }
	  else{
		$TEMP_ID=$row["ID"];   
	  }
	}
	else{
	  $TEMP_ID=$row["ID"];
	} 
	$row["STORE_NAME"]=GetName($row["SID"],"STORE_M");

	if ($row["RT_ID"]){
	  $sql="SELECT VID FROM RT WHERE ID=".$row["RT_ID"];
	  $Rrs=$conn->Execute($sql);
	  $Rr=$Rrs->FetchRow();

	  $row["VENDOR_NAME"]=($Rr["VID"])?GetName($Rr["VID"],"VEN_M"):"開帳用";
	}
	else{
	  $row["VENDOR_NAME"]="開帳用";
	}
	$row["PROD_TYPE"]=GetName($row["TYPE"],"PROD_TYPE");
	$row["TRANS_NAME"]="轉倉";
	$row["EMP_NAME"]=GetName($row["UID"],"EMP");
	$row["REC_STORE_NAME"]=GetName($row["TSID"],"STORE_M");
	$row["REC_EMP_NAME"]=GetName($row["TEMP"],"EMP");
	$row["REC_STATUS"]=($row["TFLAG"]=="Y")?"已接收":"待接收";
	//20120105轉倉明細格式：庫別、廠商、品名、條碼、交易類別、數量、交易人員、交易日期(Y-m-d)、接收庫別、接收人員、備註
	$tdata[] =array($row["STORE_NAME"],$row["VENDOR_NAME"],$row["PROD_NAME"]
		,"CODE"=>$row["CODE"],$row["TRANS_NAME"],$row["QTY"]
		,$row["EMP_NAME"],substr($row["CTIME"],0,10)
		,$row["REC_STORE_NAME"],$row["REC_EMP_NAME"]
		,$row["REMARK"]);    
  }
  gen_file($filename,$hdata,$tdata);

}


if ($_REQUEST["gen_tran_dtl5"] <> ""){
  //summary
  $filename="trans_rtv";
  // create a dummy array
  $hdata =array ("庫別","單號", "廠商","類別", "品名","條碼","交易類別","數量","交易人員","交易日期","備註");
  //excel內容
  $sql="SELECT SO.ID,SO.REMARK,PM.TYPE,PM.NAME PROD_NAME,PD.CODE,D.QTY,SO.SID,SO.UID,SO.CTIME,PD.RT_ID
	FROM SO_D D,PROD_D PD,PROD_M PM,SO
	WHERE SO.ID=D.ID
	AND D.PID=PM.ID
	AND D.PDLINE=PD.LINE
	AND PM.ID=PD.ID
	AND SO.TYPE=4
	AND SO.CTIME BETWEEN '".$SDATE."' AND '".$EDATE."' ";
  //庫別
  if ($_POST["sid"]){
	$sql.=" AND SO.SID=".$_POST["sid"]." ";
	$filename.="_".$_POST["sid"];
  }

  //產品類別
  if ($_POST["type"]){
	$sql.=" AND PM.TYPE=".$_POST["type"]." ";
  }

  ////交易類別
  //if ($_POST["iid"]){
  //     $sql.=" AND T.IID=".$_POST["iid"]." ";
  //}

  //品名
  if ($_POST["ename"]){
	$sql.=" AND PM.NAME like '%".$_POST["ename"]."%' ";
  }


  $rows=$conn->Execute($sql);
  $TEMP_ID="";
  //print $sql;
  while($row=$rows->FetchRow()){
	if ($TEMP_ID){
	  if ($TEMP_ID==$row["ID"]){
		$row["REMARK"]="";
	  }
	  else{
		$TEMP_ID=$row["ID"];   
	  }
	}
	else{
	  $TEMP_ID=$row["ID"];
	} 
	$row["STORE_NAME"]=GetName($row["SID"],"STORE_M");

	if ($row["RT_ID"]){
	  $sql="SELECT VID FROM RT WHERE ID=".$row["RT_ID"];
	  $Rrs=$conn->Execute($sql);
	  $Rr=$Rrs->FetchRow();

	  $row["VENDOR_NAME"]=($Rr["VID"])?GetName($Rr["VID"],"VEN_M"):"開帳用";
	}
	else{
	  $row["VENDOR_NAME"]="開帳用";
	}
	$row["PROD_TYPE"]=GetName($row["TYPE"],"PROD_TYPE");
	$row["TRANS_NAME"]="退貨至廠商";
	$row["EMP_NAME"]=GetName($row["UID"],"EMP");
	$tdata[] =array($row["STORE_NAME"],$row["ID"],$row["VENDOR_NAME"],$row["PROD_TYPE"],$row["PROD_NAME"],"CODE"=>$row["CODE"],$row["TRANS_NAME"],$row["QTY"],$row["EMP_NAME"],$row["CTIME"],$row["REMARK"]);    
  }
  gen_file($filename,$hdata,$tdata);

}


if ($_REQUEST["gen_tran_dtl6"] <> ""){
  $filename="trans_rej";
  // create a dummy array
  $hdata=array ("庫別","單號", "廠商","類別", "品名","條碼","交易類別","數量","交易人員","交易日期","備註");
  //excel內容
  $sql="SELECT SO.ID,SO.REMARK,PM.TYPE,PM.NAME PROD_NAME,PD.CODE,D.QTY,SO.SID,SO.UID,SO.CTIME,PD.RT_ID
	FROM SO_D D,PROD_D PD,PROD_M PM,SO
	WHERE SO.ID=D.ID
	AND D.PID=PM.ID
	AND D.PDLINE=PD.LINE
	AND PM.ID=PD.ID
	AND SO.TYPE=5
	AND SO.CTIME BETWEEN '".$SDATE."' AND '".$EDATE."' ";
  //庫別
  if ($_POST["sid"]){
	$sql.=" AND SO.SID=".$_POST["sid"]." ";
	$filename.="_".$_POST["sid"];
  }

  //產品類別
  if ($_POST["type"]){
	$sql.=" AND PM.TYPE=".$_POST["type"]." ";
  }

  ////交易類別
  //if ($_POST["iid"]){
  //     $sql.=" AND T.IID=".$_POST["iid"]." ";
  //}

  //品名
  if ($_POST["ename"]){
	$sql.=" AND PM.NAME like '%".$_POST["ename"]."%' ";
  }


  $rows=$conn->Execute($sql);
  $TEMP_ID="";
  //print $sql;
  while($row=$rows->FetchRow()){
	if ($TEMP_ID){
	  if ($TEMP_ID==$row["ID"]){
		$row["REMARK"]="";
	  }
	  else{
		$TEMP_ID=$row["ID"];   
	  }
	}
	else{
	  $TEMP_ID=$row["ID"];
	} 
	$row["STORE_NAME"]=GetName($row["SID"],"STORE_M");

	if ($row["RT_ID"]){
	  $sql="SELECT VID FROM RT WHERE ID=".$row["RT_ID"];
	  $Rrs=$conn->Execute($sql);
	  $Rr=$Rrs->FetchRow();

	  $row["VENDOR_NAME"]=($Rr["VID"])?GetName($Rr["VID"],"VEN_M"):"開帳用";
	}
	else{
	  $row["VENDOR_NAME"]="開帳用";
	}
	$row["PROD_TYPE"]=GetName($row["TYPE"],"PROD_TYPE");
	$row["TRANS_NAME"]="退貨";
	$row["EMP_NAME"]=GetName($row["UID"],"EMP");
	$tdata[] =array($row["STORE_NAME"],$row["ID"],$row["VENDOR_NAME"],$row["PROD_TYPE"],$row["PROD_NAME"],"CODE"=>$row["CODE"],$row["TRANS_NAME"],$row["QTY"],$row["EMP_NAME"],$row["CTIME"],$row["REMARK"]);    
  }
  gen_file($filename,$hdata,$tdata);

}
//
$S_OPTION=array();
$S_OPTION[]="";
$sql="SELECT ID,NAME FROM STORE_M WHERE ENABLE='Y' ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
  $S_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("S_OPTION",$S_OPTION);

//產品類別
$TYPE_OPTION=array();
$TYPE_OPTION=array("")+GetProdTypeList();
$tpl->assign("TYPE_OPTION",$TYPE_OPTION);

//交易類別
$TRANS_OPTION=array();
$TRANS_OPTION[]="";
$sql="SELECT ID,NAME FROM TRANS_TYPE ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
  $TRANS_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("TRANS_OPTION",$TRANS_OPTION);

//供應商
$VEN_OPTION=array();
$VEN_OPTION[]="";
$sql="SELECT ID,NAME FROM VEN_M WHERE STATUS='O' ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
  $VEN_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("VEN_OPTION",$VEN_OPTION);


//表頭資料
$hdata["EMP_NAME"]=$UNAME;
$hdata["STORE_NAME"]=$USTORE;
$hdata["CDATE"]=$CDATE;



$tpl->assign("hdata",$hdata);
$tpl->assign("title", $title);
$tpl->display($temp_file);
$conn->close();
?>

