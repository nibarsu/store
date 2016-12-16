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


$title="進貨單";





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
//print $sql; 
$rs=$conn->Execute($sql);
$hdata=$rs->FetchRow();

//RT_D
$sql="SELECT * FROM RT_D WHERE ID=$id ORDER BY LINE";
$rs=$conn->Execute($sql);
while($r=$rs->FetchRow()){
       $pline=new PCLASS();
       $pline->conn=$conn;
       $pline->AllData=array();
       $pline->GetAllData($r["PID"]);
       $r["PROD_NAME"]=$pline->AllData["HDATA"]["PROD_NAME"];
       $r["PROD_TYPE"]=$pline->AllData["HDATA"]["PROD_TYPE"];
       $ddata[]=$r;
}

//退貨資訊
$rdata=array();
$sql="SELECT DISTINCT SO.* FROM PROD_D PD,SO_D D,SO 
WHERE PD.ID=D.PID AND PD.LINE=D.PDLINE
AND D.ID=SO.ID
AND PD.RT_ID=$id AND PD.TYPE=4 ORDER BY PD.LINE";
//print $sql; 
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
       $rdata[]=$row;
}
$tpl->assign("hdata",$hdata);
$tpl->assign("ddata",$ddata);
$tpl->assign("rdata",$rdata);



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

$INV=new invclass;
$INV->conn=$conn;
$ndata=array();
$ndata=$INV->GetInvData($USID,"%");
//viewArray($ndata);

$tpl->assign("ndata",$ndata);
$tpl->assign("title", $title);
$tpl->display($temp_file);
$conn->close();
?>

