<?php
include "main.php";
include ('includes/tool.php');
//temp file
$temp_file=GetTempFilename(__FILE__,"htm");
$curr_file=GetFilename(__FILE__);
//current file name
//資料庫連接
$conn=CTDB();
//include ('includes/checkUID.php');


$title="出貨/轉倉單";





//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];
$USTORE=$_COOKIE["wdcbcSTORE"];
$USID=$_COOKIE["wdcbcSTOREID"];
$CTIME=date("Y-m-d H:i:s");
$CDATE=date("Y-m-d");


//只秀資料
//$id=$_POST["id"];
//RT

$sql="SELECT *,E.NAME REC_EMP_NAME,V.NAME VEN_NAME FROM RT,EMP E,VEN_M V WHERE RT.UID=E.ID AND RT.VID=V.ID AND RT.ID=$id";
$sql="SELECT *,TT.NAME TRANS_TYPE FROM SO,TRANS_TYPE TT WHERE SO.TYPE=TT.ID AND SO.ID=$id";
//SO.TYPE 2:出貨 3.轉倉
//print $sql; 
$rs=$conn->Execute($sql);
$r=$rs->FetchRow();
//接收人員姓名
$r["TEMP_NAME"]=GetName($r["TEMP"],"EMP");
//接收庫別
$r["TSID_NAME"]=GetName($r["TSID"],"STORE_M");
//出貨轉倉人員
$r["UID_NAME"]=GetName($r["UID"],"EMP");
//出貨庫別
$r["SID_NAME"]=GetName($r["SID"],"STORE_M");
//客戶名稱
if ($r["CID"]){
   $r["CNAME"]=GetName($r["CID"],"CUST_M");
}
$hdata=$r;

//RT_D
$sql="SELECT * FROM SO_D WHERE ID=$id ORDER BY LINE";
$rs=$conn->Execute($sql);
while($r=$rs->FetchRow()){
       $pline=new PCLASS();
       $pline->conn=$conn;
       $pline->AllData=array();
       $pline->GetAllData($r["PID"]);
       $r["PROD_NAME"]=$pline->AllData["HDATA"]["PROD_NAME"];
       $r["PROD_TYPE"]=$pline->AllData["HDATA"]["PROD_TYPE"];
       $r["COST"]=$pline->AllData["HDATA"]["COST"];
       $ddata[]=$r;
}

$tpl->assign("hdata",$hdata);
$tpl->assign("ddata",$ddata);



//供應商
$sql="SELECT ID,NAME FROM VEN_M ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
								  $VEN_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("VEN_OPTION",$VEN_OPTION);

//產品類別
$sql="SELECT ID,NAME FROM PROD_TYPE ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
								   $TYPE_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("TYPE_OPTION",$TYPE_OPTION);

//表頭資料
$hdata["EMP_NAME"]=$UNAME;
$hdata["STORE_NAME"]=$USTORE;
$hdata["CDATE"]=$CDATE;


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

