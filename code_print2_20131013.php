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


$title="條碼列印";

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

if ($_POST["codeprint"]){
        //viewArray($_POST);exit();
        $CFLAG=array();
        $CFLAG=$_POST["CFLAG"];
        if (is_array($CFLAG)){
             foreach($CFLAG as $rt => $val){
                       if ($val=="Y"){
                            $sql="UPDATE RT SET PFLAG='Y' WHERE ID=$rt";
                            $conn->Execute($sql);
                       }
             }
        }
}

//區間
//$sql="SELECT M.NAME PROD_NAME,D.CODE,D.RT_ID FROM PROD_D D,PROD_M M WHERE D.ID=M.ID  AND D.SID=$USID";
//20110724改為不卡SID
//$sql="SELECT M.NAME PROD_NAME,D.CODE,D.RT_ID FROM PROD_D D,PROD_M M WHERE D.ID=M.ID";
$sql="SELECT M.NAME PROD_NAME,D.CODE,D.RT_ID FROM PROD_D D,PROD_M M,RT RT WHERE D.ID=M.ID AND D.RT_ID=RT.ID";

if ($_POST["submit"]=="查詢"){
     
     //viewArray($_POST);
     ////20120614 add qCODE
     if ($_POST["qCODE"]){
     	   $sql.=" AND D.CODE like '".$_POST["qCODE"]."%' ";
           $tpl->assign("qcode",$_POST["qCODE"]);
     }
     else{
          //類別
          if ($_POST["tid"]){
               $sql.=" AND M.TYPE=".$_POST["tid"]." ";
               $tpl->assign("etid",$_POST["tid"]);
          }
          //品名
          if ($_POST["pname"]){
               $sql.=" AND M.NAME like '%".$_POST["pname"]."%' ";
               $tpl->assign("epname",$_POST["pname"]);
          }
          //品名
          if ($_POST["CDATE"]){
               $sql.=" AND RT.CTIME like '".$_POST["CDATE"]."%' ";
          }
     }
     $sql.=" ORDER BY M.ID,D.CODE";


//print $sql;
$rs=$conn->Execute($sql);
while($r=$rs->FetchRow()){
        //RT
        if ($r["RT_ID"]==0){
        $r["EMP_NAME"]="";
        $r["RTIME"]="";
        }
        else{
        $sql="SELECT * FROM RT WHERE ID=".$r["RT_ID"];
        $rows=$conn->Execute($sql);
        $row=$rows->FetchRow();
        //print $sql."<br>"; 
        $r["EMP_NAME"]=GetName($row["UID"],"EMP");
        $r["CTIME"]=$row["CTIME"];
        }
        $RDATA[]=$r;
}
//viewarray($RDATA);
$tpl->assign("RDATA",$RDATA);
}




//
$PT_TYPE=array();
$PT_TYPE=array("")+GetProdTypeList();
$tpl->assign("PT_TYPE",$PT_TYPE);


//表頭資料
$tpl->assign("title", $title);
$tpl->display($temp_file);
$conn->close();
?>

