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

$title="出貨作業";

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

$E_OPTION=array();
$sql="SELECT ID FROM USER_M WHERE SID=$USID ORDER BY ID";
$rows=$conn->Execute($sql);
//print $sql; 
while($row=$rows->fetchRow()){
       $sql="SELECT NAME FROM EMP WHERE ENABLE='Y' AND ID=".$row["ID"];
       if ($Erows=$conn->Execute($sql)){
            $Erow=$Erows->FetchRow();
            if ($Erow["NAME"]){
                 $E_OPTION[$row["ID"]]=$Erow["NAME"];
						      }
						 }
}
$tpl->assign("E_OPTION",$E_OPTION);


//if ($_POST["submit"]=="查詢"){
//不能用submit檢查
if ($_POST["submit"]=="新增"){
    //viewArray($_POST);exit();
    //表頭
    $soid=GenID("SO");
    //print $soid;exit(); 
    $so["ID"]=$soid;
    //2:出貨
    $so["TYPE"]=2;
	   $so["COST"]=$_POST["ttcost"];
	   $so["AMT"]=$_POST["amt"];
	   $so["PROFIT"]=$_POST["amt"]-$_POST["ttcost"];
	   $so["CID"]="";
	   $so["CNAME"]=$_POST["cname"];
	   $so["SID"]=$USID;
	   //$so["UID"]=$UID;
	   $so["UID"]=$_POST["uid"];
	   $so["CTIME"]=$CTIME;
	   
	   $so["UNPAY"]=$_POST["unpay"];
	   $so["CASH"]=$_POST["cash"];
	   $so["CARD"]=$_POST["card"];
	   $so["LAYAPAY"]=$_POST["layapay"];
	   $so["FCURR"]=$_POST["fcurr"];
	   $so["FCASH"]=$_POST["fcash"];
	   $so["PREPAY"]=$_POST["prepay"];
	   $so["REMARK"]=$_POST["remark"];
    
    write_to_table($so, "SO" );
    //表身  以pid為主	    
	   if (is_array($_POST["pid"])){
	           $pline=$_POST["pline"];
	           foreach($_POST["pid"] as $key => $pid){
	                   if ($pid){
	                       $sod["ID"]=$soid;
                        $sod["LINE"]=GenLine($soid,"SO_D");
                        $sod["PID"]=$pid;
                        $sod["PDLINE"]=$pline[$key];
                        $sod["QTY"]=1;
                        //$sod["PRICE"]=$_POST["cost"];
                        $pddata["SID"]=$USID;
                        write_to_table($sod, "SO_D" );
                        
                        
                        //TRANSACTION
                        $txn["ID"]=GenID("TRANSACTION");
							                 //庫別
							                 $txn["SID"]=$USID;
							                 //1:進貨  2.銷貨 3.轉倉
							                 $txn["IID"]=2;
							                 //單號
							                 $txn["FID"]=$soid;
							                 $txn["PID"]=$pid;
							                 $txn["QTY"]=1;
							                 //與進貨單上一致
							                 $txn["PRICE"]=0;
							                 //$txn["UID"]=$UID;
							                 $txn["UID"]=$_POST["uid"];
							                 $txn["CTIME"]=date("Y-m-d H:i:s");
							                 //viewArray($txn);exit();
							                 write_to_table($txn, "TRANSACTION");
							                  
							                 //更新數量
							                 $inv["SID"]=$USID;
							                 $inv["TYPE"]=1;
							                 $inv["PID"]=$pid;
							                 $inv["QTY"]=1;
							                 $sql="UPDATE INVENTORY SET QTY=QTY-".$inv["QTY"]." WHERE SID=".$inv["SID"]." AND TYPE=1 
							                   AND PID=".$inv["PID"];
							                 $conn->Execute($sql);
							                 //write_to_table($inv, "INVENTORY");
							                 
							                 //更新PROD_D
							                 $pd["ID"]=$pid;
							                 $pd["LINE"]=$pline[$key];
							                 $pd["TYPE"]=2;
							                 $sql="UPDATE PROD_D SET TYPE=".$pd["TYPE"]." WHERE ID=".$pd["ID"]." AND LINE=".$pd["LINE"];
							                 $conn->Execute($sql);
	                   }
	           }
	   }

     
					
					
}

//表頭資料
$hdata["EMP_NAME"]=$UNAME;
$hdata["STORE_NAME"]=$USTORE;
$hdata["CDATE"]=$CDATE;
$hdata["UID"]=$UID;



$tpl->assign("hdata",$hdata);
$tpl->assign("title", $title);
$tpl->display($temp_file);
$conn->close();
?>

