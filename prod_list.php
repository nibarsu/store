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


$title="購買清單";





//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];
$USTORE=$_COOKIE["wdcbcSTORE"];
$USID=$_COOKIE["wdcbcSTOREID"];
$CTIME=date("Y-m-d H:i:s");
$CDATE=date("Y-m-d");


//只秀資料
//$id=$_POST["id"];
//找中文姓名
$sql="";
$cname=GetName($id,"CUST_M");
//RT
$sql="SELECT SO.ID,SO.CTIME FROM SO WHERE SO.TYPE=2 AND SO.CNAME='".$cname."' ";
//SO.TYPE 2:出貨 3.轉倉
$rs=array();
//print $sql."<br/>"; 
$rs=$conn->Execute($sql);
//print "count:".$rs->RecordCount();
//exit();
if ($rs->RecordCount()==0){
    //jsback("沒有該客戶購買記錄!!!");
    print '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	   print "<script>alert('沒有該客戶購買記錄!!!');window.opener=null;window.close(); </script>";
	   exit();
    
}
while($r=$rs->FetchRow()){
      $sql="SELECT D.PID,D.PDLINE FROM SO_D D,PROD_D PD WHERE D.PID=PD.ID AND D.PDLINE=PD.LINE AND D.ID=".$r["ID"]." ORDER BY D.LINE";
      $rows=$conn->Execute($sql);
      //print $sql;
      while($row=$rows->FetchRow()){
             $sql="SELECT * FROM PROD_M WHERE ID=".$row["PID"];
             $prows=$conn->Execute($sql);
             $prow=$prows->FetchRow();
             //品名
             $row["PROD_NAME"]=$prow["NAME"];
             //類別
             $row["PROD_TYPE"]=GetName($prow["TYPE"],"PROD_TYPE");
             $row["CTIME"]=$r["CTIME"];
             //$r["COST"]=$pline->AllData["HDATA"]["COST"];
             $ddata[]=$row;
      }      
}



$tpl->assign("ddata",$ddata);




$tpl->assign("title", $title);
$tpl->display($temp_file);
$conn->close();
?>

