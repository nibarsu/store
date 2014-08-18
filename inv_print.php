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


$title="庫存查詢";





//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];
$USTORE=$_COOKIE["wdcbcSTORE"];
$USID=$_COOKIE["wdcbcSTOREID"];
$CTIME=date("Y-m-d H:i:s");
$CDATE=date("Y-m-d");


//預設時間

$sql="SELECT I.*,SM.NAME STORE_NAME,PM.NAME PROD_NAME,PT.NAME PROD_TYPE,IT.NAME ITYPE
        FROM INVENTORY I,STORE_M SM,PROD_M PM,PROD_TYPE PT,INV_TYPE IT
       WHERE I.SID=SM.ID AND I.PID=PM.ID AND PM.TYPE=PT.ID AND I.TYPE=IT.ID";
if ($_POST["submit"]=="查詢"){
        //viewArray($_POST);
     //庫別
     if ($_POST["sid"]){
          $sql.=" AND I.SID=".$_POST["sid"]." ";
     }
     
     //產品類別
     if ($_POST["type"]){
          $sql.=" AND PM.TYPE=".$_POST["type"]." ";
     }
     
     //品名
     if ($_POST["name"]){
          $sql.=" AND PM.NAME like '%".$_POST["name"]."%' ";
     }
     
}
$tpl->assign("ename",$_POST["name"]);
$tpl->assign("etype",$_POST["type"]);
$tpl->assign("esid",$_POST["sid"]);
$sql.=" ORDER BY I.SID,I.PID,I.TYPE";
//print $sql;
$rs=$conn->Execute($sql);
$temp_data=array();
while($r=$rs->FetchRow()){
       $temp_data[$r["STORE_NAME"]][$r["PROD_NAME"]][$r["TYPE"]]["QTY"]+=$r["QTY"];
}

if (is_array($temp_data)){
        foreach($temp_data as $sname => $val1){
                foreach($val1 as $pname => $val2){
                        //viewArray($val2);
                        $IN_QTY=($val2[1]["QTY"])?$val2[1]["QTY"]:0;
                        $EXP_QTY=($val2[2]["QTY"])?$val2[2]["QTY"]:0;
                        $RDATA[]=array("STORE_NAME"=>$sname,"PROD_NAME"=>$pname,"IN_STORE"=>$IN_QTY,"EXP_STORE"=>$EXP_QTY);
                }
        }
}
//viewArray($RDATA);
$tpl->assign("RDATA",$RDATA);


//
$S_OPTION=array();
$S_OPTION[]="";
$sql="SELECT ID,NAME FROM STORE_M ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
								  $S_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("S_OPTION",$S_OPTION);

//產品類別
$TYPE_OPTION=array();
$TYPE_OPTION[]="";
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

