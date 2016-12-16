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
//$UID=$_COOKIE["wdcbcUID"];
$UID=$AC->id;
//echo $AC->ALLdata["EMP"]["ENABLE"];
//echo $UID;
$UNAME=$_COOKIE["wdcbcNAME"];
$USTORE=$_COOKIE["wdcbcSTORE"];
$USID=$_COOKIE["wdcbcSTOREID"];
$CTIME=date("Y-m-d H:i:s");
$CDATE=date("Y-m-d");

$EDIT=($EDIT)?$EDIT:1;

//供應商
$sql="SELECT ID,NAME FROM VEN_M ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
  $VEN_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("VEN_OPTION",$VEN_OPTION);

$sql="SELECT ID,NAME FROM STORE_M  ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->fetchRow()){
  $S_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("S_OPTION",$S_OPTION);


//if ($_POST["submit"]=="查詢"){
if ($_POST["markrec"]=="接收所有項目"){
  //viewArray($_POST);exit();
  $CFLAG=array();
  $CFLAG=$_POST["CFLAG"];
  if (is_array($CFLAG)){
    foreach($CFLAG as $so => $val){
      if ($val=="Y"){
        //表頭狀態更新  已接收
        $sql="UPDATE SO SET TFLAG='Y' ,TEMP=$UID WHERE ID=$so";
        $conn->Execute($sql);

        //抓取表頭資料
        $sql="SELECT * FROM SO WHERE ID=$so";
        $rs=$conn->Execute($sql);
        $sohdata=array();
        $sohdata=$rs->FetchRow();
        //表身
        $sql="SELECT * FROM SO_D WHERE ID=$so ORDER BY LINE";
        $rs=$conn->Execute($sql);
        while($r=$rs->FetchRow()){

          //TRANSACTION
          $txn["ID"]=GenID("TRANSACTION");
          //轉入庫別							   
          $txn["SID"]=$sohdata["TSID"];
          //1:進貨  2.銷貨 3.轉倉
          $txn["IID"]=1;
          $txn["PID"]=$r["PID"];
          $txn["QTY"]=$r["QTY"];
          //與進貨單上一致
          $txn["PRICE"]=0;
          $txn["UID"]=$UID;
          $txn["CTIME"]=date("Y-m-d H:i:s");
          //viewArray($txn);exit();
          write_to_table($txn, "TRANSACTION");

          //INV

          //將數量轉至目標倉別的轉倉
          $sql="UPDATE INVENTORY SET QTY=QTY-".$r["QTY"]." WHERE SID=".$sohdata["TSID"]." AND TYPE=2 AND PID=".$r["PID"];
          $conn->Execute($sql);
          $imp["SID"]=$sohdata["TSID"];
          $imp["PID"]=$r["PID"];
          $imp["TYPE"]=1;
          $imp["QTY"]=$r["QTY"];


          $sql="SELECT COUNT(*) T FROM INVENTORY WHERE SID=".$sohdata["TSID"]." AND TYPE=1 AND PID=".$r["PID"];
          $rows=$conn->Execute($sql);
          $row=$rows->FetchRow();
          if ($row["T"]==0){

            write_to_table($imp, "INVENTORY");
          }
          else{
            $sql="UPDATE INVENTORY SET QTY=QTY+".$imp["QTY"]." WHERE SID=".$imp["SID"]." 
              AND PID=".$imp["PID"]." AND TYPE=". $imp["TYPE"];
            $conn->Execute($sql);
          }

          $pd=array();
          //更新PROD_D
          $pd["ID"]=$r["PID"];
          $pd["LINE"]=$r["PDLINE"];
          //viewArray($r);
          //入庫
          $pd["TYPE"]=1;
          $sql="UPDATE PROD_D SET TYPE=".$pd["TYPE"].", SID=".$sohdata["TSID"]." WHERE ID=".$pd["ID"]." AND LINE=".$pd["LINE"];
          //print $sql;exit(); 
          $conn->Execute($sql);
        }
        $EDIT=1;
        $sql="SELECT COUNT(*) T FROM SO WHERE TYPE=3 AND TFLAG <>'Y' AND (TEMP=".$_COOKIE["wdcbcUID"]." OR UID=".$_COOKIE["wdcbcUID"].")";
        $rs=$conn->Execute($sql);
        $r=$rs->FetchRow();                      

      }
    }
  }
}
//不能用submit檢查
if ($_POST["submit"]=="接收"){
  //viewArray($_POST);exit();
  $soid=$_POST["soid"];
  //表頭狀態更新  已接收
  $sql="UPDATE SO SET TFLAG='Y',TEMP=$UID WHERE ID=$soid";
  $conn->Execute($sql);
  //抓取表頭資料
  $sql="SELECT * FROM SO WHERE ID=$soid";
  $rs=$conn->Execute($sql);
  $sohdata=array();
  $sohdata=$rs->FetchRow();


  //表身
  $sql="SELECT * FROM SO_D WHERE ID=$soid ORDER BY LINE";
  $rs=$conn->Execute($sql);
  while($r=$rs->FetchRow()){

    //TRANSACTION
    $txn["ID"]=GenID("TRANSACTION");
    //轉入庫別							   
    $txn["SID"]=$sohdata["TSID"];
    //1:進貨  2.銷貨 3.轉倉
    $txn["IID"]=1;
    $txn["PID"]=$r["PID"];
    $txn["QTY"]=$r["QTY"];
    //與進貨單上一致
    $txn["PRICE"]=0;
    $txn["UID"]=$UID;
    $txn["CTIME"]=date("Y-m-d H:i:s");
    //viewArray($txn);exit();
    write_to_table($txn, "TRANSACTION");

    //INV

    //將數量轉至目標倉別的轉倉
    $sql="UPDATE INVENTORY SET QTY=QTY-".$r["QTY"]." WHERE SID=".$sohdata["TSID"]." AND TYPE=2 AND PID=".$r["PID"];
    $conn->Execute($sql);
    $imp["SID"]=$sohdata["TSID"];
    $imp["PID"]=$r["PID"];
    $imp["TYPE"]=1;
    $imp["QTY"]=$r["QTY"];


    $sql="SELECT COUNT(*) T FROM INVENTORY WHERE SID=".$sohdata["TSID"]." AND TYPE=1 AND PID=".$r["PID"];
    $rows=$conn->Execute($sql);
    $row=$rows->FetchRow();
    if ($row["T"]==0){

      write_to_table($imp, "INVENTORY");
    }
    else{
      $sql="UPDATE INVENTORY SET QTY=QTY+".$imp["QTY"]." WHERE SID=".$imp["SID"]." 
        AND PID=".$imp["PID"]." AND TYPE=". $imp["TYPE"];
      $conn->Execute($sql);
    }

    $pd=array();
    //更新PROD_D
    $pd["ID"]=$r["PID"];
    $pd["LINE"]=$r["PDLINE"];
    //viewArray($r);
    //入庫
    $pd["TYPE"]=1;
    $sql="UPDATE PROD_D SET TYPE=".$pd["TYPE"].", SID=".$sohdata["TSID"]." WHERE ID=".$pd["ID"]." AND LINE=".$pd["LINE"];
    //print $sql;exit(); 
    $conn->Execute($sql);
  }
  $EDIT=1;
  $sql="SELECT COUNT(*) T FROM SO WHERE TYPE=3 AND TFLAG <>'Y' AND (TEMP=".$_COOKIE["wdcbcUID"]." OR UID=".$_COOKIE["wdcbcUID"].")";
  $rs=$conn->Execute($sql);
  $r=$rs->FetchRow();
  setcookie("wdcbcUNACCEPT", $r["T"]);
  jslocation("已接收", "expimp2");

}

//表頭資料
if ($EDIT==1){
  //目前手上待接收的單子
  //20110501改為目前該員工所在店家未接收的單子
  $sql="SELECT SO.ID,SS.NAME FROM_STORE,SE.NAME END_STORE,
    ES.NAME FROM_EMP,SO.CTIME
    FROM SO SO,STORE_M SS,STORE_M SE,EMP ES 
    WHERE SO.SID=SS.ID AND SO.TSID=SE.ID AND 
    SO.UID=ES.ID AND SO.TYPE=3 AND TFLAG='N'
    AND SO.TSID=$USID";

  if ($_COOKIE["wdcbcSTOREFLAG"]==1){
    //庫存人員有全部庫別轉倉權限
    $sql="SELECT SO.ID,SS.NAME FROM_STORE,SE.NAME END_STORE,
      ES.NAME FROM_EMP,SO.CTIME
      FROM SO SO,STORE_M SS,STORE_M SE,EMP ES 
      WHERE SO.SID=SS.ID AND SO.TSID=SE.ID AND 
      SO.UID=ES.ID AND SO.TYPE=3 AND TFLAG='N'";
  }
  $rs=$conn->Execute($sql);
  $tdata=array();
  while($r=$rs->FetchRow()){
    $tdata[]=$r;
  }

  //目前手上轉倉出但對方還未接收的單子
  //20110501改為目前該員工所在店家已轉出但轉入店家未接收的單子
  $sql="SELECT SO.ID,SS.NAME FROM_STORE,SE.NAME END_STORE,
    ES.NAME FROM_EMP,SO.CTIME
    FROM SO SO,STORE_M SS,STORE_M SE,EMP ES
    WHERE SO.SID=SS.ID AND SO.TSID=SE.ID AND SO.UID=ES.ID 
    AND SO.TYPE=3 AND TFLAG='N' AND SO.SID=$USID";
  $rs=$conn->Execute($sql);
  $udata=array();
  while($r=$rs->FetchRow()){
    $udata[]=$r;
  }
}


if ($EDIT==2){
  //$soid
  $sohdata=array();
  $soddata=array();
  $sql="SELECT SO.ID,SO.CTIME,ES.NAME FROM_EMP,SS.NAME FROM_STORE,SE.NAME END_STORE FROM SO SO,EMP ES,STORE_M SS
    ,STORE_M SE
    WHERE SO.UID=ES.ID AND SO.SID=SS.ID AND SO.TSID=SE.ID AND SO.ID=$soid";
  $rs=$conn->Execute($sql);
  $r=$rs->FetchRow();
  $sohdata=$r;


  $sql="SELECT D.ID,D.LINE,D.QTY,PM.NAME PROD_NAME,PD.CODE FROM SO_D D,PROD_D PD,PROD_M PM WHERE D.PID=PM.ID AND D.PID=PD.ID AND D.PDLINE=PD.LINE
    AND D.ID=$soid ORDER BY D.ID,D.LINE";
  //print $sql; 
  $rs=$conn->Execute($sql);
  while($r=$rs->FetchRow()){
    $soddata[]=$r;
  }
  $tpl->assign("soid", $soid);
  //viewArray($sohdata);
}
//表頭資料
$hdata["EMP_NAME"]=$UNAME;
$hdata["STORE_NAME"]=$USTORE;
$hdata["CDATE"]=$CDATE;

$tpl->assign("sohdata", $sohdata);
$tpl->assign("soddata", $soddata);
$tpl->assign("tdata", $tdata);
$tpl->assign("udata", $udata);
$tpl->assign("EDIT", $EDIT);
$tpl->assign("hdata",$hdata);
$tpl->assign("title", $title);
$tpl->display($temp_file);
$conn->close();


?>

