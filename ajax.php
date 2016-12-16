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



if ($cal == "GetProdName"){
     
     $sql="SELECT M.ID,M.NAME,T.NAME TYPE_NAME FROM PROD_M M,PROD_TYPE T WHERE M.TYPE=T.ID AND M.NAME LIKE '%$name%' ORDER BY ID";
     $sql="SELECT M.ID,M.NAME,T.NAME TYPE_NAME FROM PROD_M M,PROD_TYPE T WHERE M.TYPE=T.ID 
     AND M.TYPE =$type
     AND M.NAME LIKE '%$name%' ORDER BY ID limit 0,50";
     //print "<script>alert('$sql');</script>";
     //print $sql; 
     $rows=$conn->Execute($sql);
     while($row=$rows->FetchRow()){
            $JDATA[]=array("LABEL"=>$row["TYPE_NAME"]."-".$row["NAME"],"VALUE"=>$row["NAME"]);
     }
     
     $JDATA=json_encode($JDATA);
     echo $JDATA; 
     $conn->close();
     exit();
}



if ($_POST["cal"]=="GetProdName2"){
     //barcode
     $code=$_POST["code"];
     //store
     $sid=$_POST["sid"];
     //20110316改為同產品編碼不同 所以一個條碼只會有一個
     $sql="SELECT D.ID,D.LINE,PM.NAME,D.COST FROM PROD_D D,PROD_M PM WHERE D.ID=PM.ID AND D.CODE='$code' AND D.SID=$sid AND D.TYPE=1";
     //20110430 改由PROD_M.COST為成本
     //20110501 改回由PROD_D.COST為成本
     $sql="SELECT D.RT_ID,D.ID,D.LINE,PM.NAME,D.COST FROM PROD_D D,PROD_M PM WHERE D.ID=PM.ID AND D.CODE='$code' AND D.SID=$sid AND D.TYPE=1";
     //print $sql; 
     $rs=$conn->Execute($sql);
     if ($rs->RecordCount() > 0){
          $r=$rs->FetchRow();
          $sql="SELECT VID FROM RT WHERE ID=".$r["RT_ID"];
          $Rows=$conn->Execute($sql);
          $Row=$Rows->FetchRow();
          //GetName($Row["VID"],"VEN_M");
          if ($r["RT_ID"]==0){
                  $r["NAME"]=$r["NAME"]."(開帳用)";
          }
          else{
             $r["NAME"]=(GetName($Row["VID"],"VEN_M"))?$r["NAME"]."(".GetName($Row["VID"],"VEN_M").")":"";
          }
          $r["FLAG"]="Y";
          
          $JDATA=json_encode($r);     
     }
     else{
          $r=array();
          $r["FLAG"]="N";
          $JDATA=json_encode($r);
     }
     
     //{"":""}
     echo $JDATA;
     $conn->close();
     exit();
}
if ($_POST["cal"]=="GetProdName3"){
     //barcode
     $code=$_POST["code"];
     //store
     $sid=$_POST["sid"];
     //20110316改為同產品編碼不同 所以一個條碼只會有一個
     $sql="SELECT D.ID,D.LINE,PM.NAME,D.COST FROM PROD_D D,PROD_M PM WHERE D.ID=PM.ID AND D.CODE='$code' AND D.SID=$sid AND D.TYPE=2";
     //$sql="SELECT PM.ID,PM.NAME,PM.COST,I.QTY FROM PROD_M PM,INVENTORY I WHERE PM.ID=I.PID AND PM.CODE='$code' AND I.TYPE=1 AND I.SID=$sid";
     //print $sql; 
     $rs=$conn->Execute($sql);
     if ($rs->RecordCount() > 0){
          $r=$rs->FetchRow();
          $r["FLAG"]="Y";
          $JDATA=json_encode($r);     
     }
     else{
          $r=array();
          $r["FLAG"]="N";
          $JDATA=json_encode($r);
     }
     
     //{"":""}
     echo $JDATA;
     $conn->close();
     exit();
}
if ($_POST["cal"]=="GetProdName4"){
     //全倉庫用
     //barcode
     $code=$_POST["code"];
     //store
     $sid=$_POST["sid"];
     $sql="SELECT D.RT_ID,D.ID,D.LINE,PM.NAME,D.COST FROM PROD_D D,PROD_M PM WHERE D.ID=PM.ID AND D.CODE='$code' AND D.SID=$sid AND D.TYPE=1";
     //20110624新增不卡庫別
     $sql="SELECT D.RT_ID,D.ID,D.LINE,PM.NAME,D.COST FROM PROD_D D,PROD_M PM WHERE D.ID=PM.ID AND D.CODE='$code' AND D.TYPE=1";
     //print $sql; 
     $rs=$conn->Execute($sql);
     if ($rs->RecordCount() > 0){
          $r=$rs->FetchRow();
          $sql="SELECT VID FROM RT WHERE ID=".$r["RT_ID"];
          $Rows=$conn->Execute($sql);
          $Row=$Rows->FetchRow();
          //GetName($Row["VID"],"VEN_M");
          if ($r["RT_ID"]==0){
                  $r["NAME"]=$r["NAME"]."(開帳用)";
          }
          else{
             $r["NAME"]=(GetName($Row["VID"],"VEN_M"))?$r["NAME"]."(".GetName($Row["VID"],"VEN_M").")":"";
          }
          $r["FLAG"]="Y";
          
          $JDATA=json_encode($r);     
     }
     else{
          $r=array();
          $r["FLAG"]="N";
          $JDATA=json_encode($r);
     }
     
     //{"":""}
     echo $JDATA;
     $conn->close();
     exit();
}
if ($_POST["cal"]=="GetEmpList"){
    //$_POST["sid"]    
    //找出目前該倉庫的人員
    //echo '<option value="" ></option>' . "\n";
    $sql="SELECT DISTINCT E.ID,E.NAME FROM USER_STORE_D D,EMP E WHERE E.ID=D.ID AND E.ENABLE='Y' AND D.SID=".$_POST["sid"];
    
    $rs=$conn->execute($sql);
    if ($rs->RecordCount()==0){
            echo '<option value="" ></option>' . "\n";
    }
    else{
            while($r=$rs->FetchRow()){
            	     //$selected="";
            	     //if ($r["ORG_CODE"]==$qorg)$selected="selected";
            	     //echo '<option value="' . $r["ID"] . '" '.$selected.'>' . $r["NAME"] . '</option>' . "\n";
            	     echo '<option value="' . $r["ID"] . '" >' . $r["NAME"] . '</option>' . "\n";
            	     
            }
    }
    $conn->close();
    exit();
}
if ($_POST["cal"]=="GetEmpList2"){
    //$_POST["sid"]    
    //找出目前該倉庫的人員
    //echo '<option value="" ></option>' . "\n";
    $sql="SELECT DISTINCT E.ID,E.NAME FROM USER_STORE_D D,EMP E WHERE E.ID=D.ID AND E.ENABLE='Y' AND D.SID=".$_POST["sid"];
    
    $rs=$conn->execute($sql);
    if ($rs->RecordCount()==0){
            echo '<option value="" ></option>' . "\n";
    }
    else{
            echo '<option value="" ></option>' . "\n";
            while($r=$rs->FetchRow()){
            	     //$selected="";
            	     //if ($r["ORG_CODE"]==$qorg)$selected="selected";
            	     //echo '<option value="' . $r["ID"] . '" '.$selected.'>' . $r["NAME"] . '</option>' . "\n";
            	     echo '<option value="' . $r["ID"] . '" >' . $r["NAME"] . '</option>' . "\n";
            	     
            }
    }
    $conn->close();
    exit();
}

if ($cal=="GetPHisVal"){
    $mess="商品歷史價格資訊測試中!!";
    //商品名稱
    //$pid=$pid;
    //廠商代碼
    //$vid=$_POST["vid"];
    //
    $vname=GetName($vid,"VEN_M");
    //die('aa');
    //找出PID
    $sql="SELECT ID FROM PROD_M WHERE NAME='$pid' AND VID=$vid";
    if ($prows=$conn->Execute($sql)){
         if ($prow=$prows->FetchRow()){
              //用商品代碼找最近一次進貨資料
              $sql="SELECT D.PRICE,M.CTIME FROM RT_D D ,RT M WHERE M.ID=D.ID AND D.PID=".$prow["ID"]." ORDER BY M.CTIME DESC LIMIT 0,1";
              $Rrows=$conn->Execute($sql);
              if  ($Rrow=$Rrows->FetchRow()){
                    //$mess="最近一次進貨價：".$Rrow["PRICE"]." 由廠商：".$vname." 在".$Rrow["CTIME"]."進貨";
                    $mess="最近一次進貨價(單價)：".$Rrow["PRICE"];
              }
         }
    }
    
    
    $JDATA=array("MESS"=>$mess);
    //die('aa');
    //viewarray($JDATA);
    $JDATA=json_encode($JDATA);
    echo $JDATA; 
    $conn->close();
    exit();
    
}
############################### AJAX FUNCTION #########################################

//$conn->close();

?>  
