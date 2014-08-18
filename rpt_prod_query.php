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

$title="商品查詢";

//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];
$USTORE=$_COOKIE["wdcbcSTORE"];
$USID=$_COOKIE["wdcbcSTOREID"];
$CTIME=date("Y-m-d H:i:s");
$CDATE=date("Y-m-d");

$sql="SELECT ID,NAME FROM STORE_M  ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->fetchRow()){
						$S_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("S_OPTION",$S_OPTION);


//if ($_POST["submit"]=="查詢"){
//不能用submit檢查
if ($_POST["code"]){
    //viewArray($_POST);exit();
    $ddata=array();
    $code=$_POST["code"];
    //該商品相關資料---先不用
    $sql="SELECT * FROM PROD_D WHERE CODE='$code' LIMIT 0,1";
    $rows=$conn->Execute($sql);
    $row=$rows->FetchRow();
    
    $sql="SELECT * FROM PROD_M WHERE ID=".$row["ID"];
    $Prows=$conn->Execute($sql);
    $Prow=$Prows->FetchRow();
    
    //類別
    $row["PROD_TYPE"]=GetName($Prow["TYPE"],"PROD_TYPE");
    
    
         
    //品名
    $row["PROD_NAME"]=$Prow["NAME"];
    $row["STATUS_DESC"]="狀態為".GetName($row["TYPE"],"TRANS_TYPE");
    switch($row["TYPE"]){
    case "1"://進貨
            $row["STATUS_DESC"].="<br>庫別:".GetName($row["SID"],"STORE_M");
            break;
    case "3"://轉倉
            $row["STATUS_DESC"].="<br>庫別:".GetName($row["SID"],"STORE_M");
            break;
    case "2"://出貨
            $sql="SELECT SO.* FROM SO_D D,SO WHERE D.ID=SO.ID AND D.PID=".$row["ID"]." 
            AND D.PDLINE=".$row["LINE"]." ORDER BY SO.CTIME DESC limit 0,1";
            $rs=$conn->Execute($sql);
            $r=$rs->FetchRow();
            $row["STATUS_DESC"].="<br>售出日期為:".substr($r["CTIME"],0,10)."<br>由".GetName($r["UID"],"EMP")."售出";
            $row["STATUS_DESC"].=($r["CNAME"])?"，客戶:".$r["CNAME"]:"";
            break;
    case "4"://退貨至廠商
            $sql="SELECT SO.UID,SO.CTIME FROM SO_D D,SO WHERE D.ID=SO.ID AND D.PID=".$row["ID"]." 
            AND D.PDLINE=".$row["LINE"]." ORDER BY SO.CTIME DESC limit 0,1";
            $rs=$conn->Execute($sql);
            $r=$rs->FetchRow();
            $row["STATUS_DESC"].="<br>退貨日期為:".substr($r["CTIME"],0,10)." <br>由".GetName($r["UID"],"EMP")."退貨";
            break;
    default:
            
            //$row["STATUS_DESC"]="狀態為".GetName($row["TYPE"]);
            $row["STATUS_DESC"].=" ";
            break;
    }
    
    //廠商
    $sql="SELECT V.NAME,RT.UID,RT.CTIME FROM RT RT,VEN_M V WHERE RT.VID=V.ID AND RT.ID='".$row["RT_ID"]."'";
    $Vrows=$conn->Execute($sql);
    $Vrow=$Vrows->FetchRow();
    $row["VENDOR"]=($Vrow["NAME"]=="")?"開帳用":$Vrow["NAME"];
    if ($row["VENDOR"]=="開帳用"){
        $row["STATUS_DESC"].="<br>進貨時間:(開帳用無進貨時間)";
        $row["STATUS_DESC"].="<br>進貨人員:(開帳用無進貨人員)";
    }
    else{
        $row["STATUS_DESC"].="<br>進貨時間:".$Vrow["CTIME"];
        $row["STATUS_DESC"].="<br>進貨人員:".GetName($Vrow["UID"],"EMP");
    }
    
    
    $ddata=$row;
    $tpl->assign("ddata",$ddata);
					
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

