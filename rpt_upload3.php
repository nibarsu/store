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


$title="盤點檔案匯入";





//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];
$USTORE=$_COOKIE["wdcbcSTORE"];
$USID=$_COOKIE["wdcbcSTOREID"];
$CTIME=date("Y-m-d H:i:s");
$CDATE=date("Y-m-d");

//匯入庫別
$STORE=($_POST["sid"])?$_POST["sid"]:1; //4:oneone 預設晶豪泰


//$sql="UPDATE PROD_D SET TYPE=4 WHERE SID =1 AND TYPE IN (1,3)";
//將所有檔案上的條碼改為該店家且狀態為1
if ($_REQUEST["upload"] <> ""){
	  if ($_FILES["upload_file"]["name"] <>"" and $_FILES["upload_file"]["size"] >0 ){
	       set_time_limit(0);
        //刪除該庫別的盤點資料
	       $sql="DELETE FROM STOCKTAKE_DATA WHERE SID=$STORE";
	       $conn->Execute($sql);
	       
	       //匯入檔案資料
	       $lines = file($_FILES["upload_file"]["tmp_name"]);
	       foreach ($lines as $line_num => $file_str) {
	                  $file_str=preg_replace("/\s/","",$file_str);
	                  $file_str=iconv("big5","utf-8",$file_str);
	                  //先不管資料正確性  先全部insert
	                  $tmp_array=array();
	                  $code="";
                   $tmp_array=explode(",",$file_str);
                   $code=$tmp_array[1];
                   
	                  $COOR=array();
                   $COOR["SID"]=$STORE;
                   $COOR["CODE"]=$code;
                   $COOR["DATA"]=$file_str;
                   write_to_table($COOR, "STOCKTAKE_DATA");
	                  
	       }
	       
	       //比對庫存
	       
	       //1.儲存資料
	       //共執行幾筆
	       $sql="SELECT COUNT(*) T FROM STOCKTAKE_DATA WHERE SID=$STORE";
	       $rows=$conn->Execute($sql);
	       $row=$rows->FetchRow();
	       //檔案共有幾筆資料
	       $total=$row["T"];
	       
	       $dup=0;
        $dup_str="";
	       
	       
	       $filename="stocktake_".$STORE;
        // create a dummy array
        $hdata =array ("類別", "品名","條碼","狀態","檔案資料");
        $sql="SELECT T.NAME TYPE_NAME,M.NAME PROD_NAME,D.CODE,D.TYPE 
        FROM PROD_D D,PROD_TYPE T,PROD_M M WHERE D.ID=M.ID AND M.TYPE=T.ID 
        AND D.TYPE IN (1,3) AND D.SID=$STORE ORDER BY M.TYPE";
        $rows=$conn->Execute($sql);
        
        while($row=$rows->FetchRow()){
               $STATUS="";
               $FILE_STR="";
               //比對檔案
               $sql="SELECT COUNT(*) T FROM STOCKTAKE_DATA WHERE CODE='".$row["CODE"]."' AND SID=$STORE";
               //print $sql="SELECT COUNT(*) T FROM STOCKTAKE_DATA WHERE CODE='".$row["CODE"]."' AND SID=$STORE"."   \n";
               $Srows=$conn->Execute($sql);
               $Srow=$Srows->FetchRow();
               if ($Srow["T"] > 0){
                    $sql="SELECT * FROM STOCKTAKE_DATA WHERE CODE='".$row["CODE"]."' AND SID=$STORE limit 0,1";
                    $Frows=$conn->Execute($sql);
                    $Frow=$Frows->FetchRow();
                    $STATUS="盤點確認";
                    $FILE_STR=$Frow["DATA"];
                    if ($Srow["T"] > 1){
                         $dup++;
                         $dup_str.=$line."<br/>";
                         $STATUS.="，但重複盤點";
                    }
                    $wData = array();
                    $wData["SID"]=$STORE;
                    $wData["CODE"]=$row["CODE"];
                    $uData = array();
                    $uData["FLAG"]='Y';
                    update_to_table($uData, "STOCKTAKE_DATA", $wData);
                    
               }
               else{
                    $STATUS="檔案上未出現";
               }
               $STATUS.=($row["TYPE"]==1) ? "，目前為在庫" : "，目前為轉倉中";
               $tdata[]=array("PROD_TYPE"=>$row["TYPE_NAME"],
               "PROD_NAME"=>$row["PROD_NAME"],"CODE"=>$row["CODE"],"STATUS"=>$STATUS,"FILE_STR"=>$FILE_STR); 
               
        }
        
        //剩餘檔案列出
        $sql="SELECT * FROM STOCKTAKE_DATA WHERE SID=$STORE AND FLAG='N'";
        $rows=$conn->Execute($sql);
        
        while($row=$rows->FetchRow()){
               $STATUS="";
               $FILE_STR=$row["DATA"];
               $FDATA=explode(",",$FILE_STR);
               $PTYPE="";
               $PNAME="";
               $PTYPE=$FDATA[0];
               //是否在資料庫內但是其他狀態
               $sql="SELECT count(*) T FROM PROD_D WHERE CODE='".$row["CODE"]."'";
               $Crows=$conn->Execute($sql);
               $Crow=$Crows->FetchRow();
               if ($Crow["T"] == 1){
                    $STATUS="可能已出貨或退貨";
               }
               else{
                    $STATUS="庫存內無該筆資料";
               }
               $PTYPE=$FDATA[0];
               if ($Crow["ID"]){
                   $PNAME= GetName($Crow["ID"],"PROD_M");
               }
               
               //20110930列出原盤點檔案的品名
               $PNAME = ($PNAME) ? $PNAME:$PTYPE;
               $tdata[]=array("PROD_TYPE"=>"",
                 "PROD_NAME"=>$PNAME,"CODE"=>$row["CODE"],"STATUS"=>$STATUS,"FILE_STR"=>$FILE_STR);
          
        }
        
        gen_file($filename,$hdata,$tdata);
        
        
   }
   else{
        print "檔案內容為空或格式有問題!!!";
   }
    
}
/*
if ($_REQUEST["query"] <> ""){
	       //比對庫存
	       set_time_limit(0); 
	       //1.儲存資料
	       //共執行幾筆
	       $sql="SELECT COUNT(*) T FROM STOCKTAKE_DATA WHERE SID=$STORE";
	       $rows=$conn->Execute($sql);
	       $row=$rows->FetchRow();
	       //檔案共有幾筆資料
	       $total=$row["T"];
	       
	       $dup=0;
        $dup_str="";
	       
	       
	       $filename="stocktake_".$STORE;
        // create a dummy array
        $hdata =array ("類別", "品名","條碼","狀態","檔案資料");
        $sql="SELECT T.NAME TYPE_NAME,M.NAME PROD_NAME,D.CODE,D.TYPE 
        FROM PROD_D D,PROD_TYPE T,PROD_M M WHERE D.ID=M.ID AND M.TYPE=T.ID 
        AND D.TYPE IN (1,3) AND D.SID=$STORE ORDER BY M.TYPE";
        $rows=$conn->Execute($sql);
        while($row=$rows->FetchRow()){
               $STATUS="";
               $FILE_STR="";
               //比對檔案
               $sql="SELECT COUNT(*) T FROM STOCKTAKE_DATA WHERE CODE='".$row["CODE"]."' AND SID=$STORE";
               $Srows=$conn->Execute($sql);
               if ($Srow["T"] > 0){
                    $sql="SELECT * FROM STOCKTAKE_DATA WHERE CODE='".$row["CODE"]."' AND SID=$STORE limit 0,1";
                    $Frows=$conn->Execute($sql);
                    $Frow=$Frows->FetchRow();
                    $STATUS="盤點確認";
                    $FILE_STR=$Frow["DATA"];
                    if ($Srow["T"] > 1){
                         $dup++;
                         $dup_str.=$line."<br/>";
                         $STATUS.="，但重複盤點";
                    }
                    $wData = array();
                    $wData["SID"]=$STORE;
                    $wData["CODE"]=$STORE;
                    $uData = array();
                    $uData["FLAG"]='Y';
                    update_to_table($uData, "STOCKTAKE_DATA", $wData);
                    
               }
               else{
                    $STATUS="檔案上未出現";
               }
               $STATUS.=($row["TYPE"]==1) ? "，目前為在庫" : "，目前為轉倉中";
               $tdata[]=array("PROD_TYPE"=>$row["TYPE_NAME"],
                 "PROD_NAME"=>$row["PROD_NAME"],"CODE"=>$row["CODE"],"STATUS"=>$STATUS,"FILE_STR"=>$FILE_STR); 
               
        }
        
        //剩餘檔案列出
        $sql="SELECT * FROM STOCKTAKE_DATA WHERE SID=$STORE AND FLAG='N'";
        $rows=$conn->Execute($sql);
        
        while($row=$rows->FetchRow()){
               $STATUS="";
               $FILE_STR=$row["DATA"];
               //是否在資料庫內但是其他狀態
               $sql="SELECT count(*) T FROM PROD_D WHERE CODE='".$row["CODE"]."'";
               $Crows=$conn->Execute($sql);
               if ($Crow["T"] == 1){
                    $STATUS="可能已出貨或退貨";
               }
               else{
                    $STATUS="庫存內無該筆資料";
               }
               $tdata[]=array("PROD_TYPE"=>"",
                 "PROD_NAME"=>"","CODE"=>$row["CODE"],"STATUS"=>$STATUS,"FILE_STR"=>$FILE_STR);
          
        }
        gen_file($filename,$hdata,$tdata);
    
}
*/
/*

if ($_REQUEST["reset"] <> ""){
    //2.將該店家所有物品狀態改為6
	   $sql="SELECT * FROM PROD_D WHERE SID=$STORE AND TYPE IN (1,3)";
	   $rows=$conn->Execute($sql);
	   while($row=$rows->FetchRow()){
	         trans_proc(6,$STORE,0,0,$row["ID"],$row["LINE"],$UID);
	   }
	   $sql="DELETE FROM INVENTORY WHERE SID=$STORE ";
	   $conn->Execute($sql);
}	   

if ($_REQUEST["recover"] <> ""){
    //3.將儲存資料恢復為在庫
    $sql="SELECT SID,CODE FROM INV_COOR_DATA WHERE SID=$STORE";
    $rows=$conn->Execute($sql);
    while($row=$rows->FetchRow()){
          $line=$row["CODE"];
          //改為該庫別
          $sql="UPDATE PROD_D SET SID=$STORE WHERE CODE='".$line."'";
          $conn->Execute($sql);
          
          $sql="SELECT ID,LINE FROM PROD_D WHERE CODE='".$line."'";
          //echo $sql."<br/>"; 
          //print $sql;exit(); 
          $rts=$conn->Execute($sql);
          $rt=$rts->FetchRow();
          //print "count:".$rts->RecordCount(); 
          //viewArray($rt);exit();
          if ($rt["ID"]){
              trans_proc(1,$STORE,0,0,$rt["ID"],$rt["LINE"],$UID);
          }
                
                
    }
    //重新計算INVENTORY
    //SID 	TYPE 	PID 	QTY
    $sql="SELECT SID, ID, COUNT( CODE ) QTY
           FROM PROD_D
           WHERE SID =$STORE
             AND TYPE =1
           GROUP BY SID, ID";
//print $sql;           
    $rows=$conn->Execute($sql);
    while($row=$rows->FetchRow()){
          $sql="DELETE FROM INVENTORY WHERE SID=$STORE AND PID=".$row["ID"]." AND TYPE=1";
          $conn->Execute($sql);
          $INV=array();
          $INV["SID"]=$STORE;
          $INV["TYPE"]=1;
          $INV["PID"]=$row["ID"];
          $INV["QTY"]=$row["QTY"];
          write_to_table($INV, "INVENTORY");
    }
}
*/

$S_OPTION=array();
$sql="SELECT ID,NAME FROM STORE_M ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
								  $S_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("S_OPTION",$S_OPTION);






//表頭資料
$hdata["EMP_NAME"]=$UNAME;
$hdata["STORE_NAME"]=$USTORE;
$hdata["CDATE"]=$CDATE;



$tpl->assign("hdata",$hdata);
$tpl->assign("title", $title);
$tpl->display($temp_file);
$conn->close();
?>

