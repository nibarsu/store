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


$title="檔案匯入";

//print date("Y-m-d H:i:s");



//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];
$USTORE=$_COOKIE["wdcbcSTORE"];
$USID=$_COOKIE["wdcbcSTOREID"];
$CTIME=date("Y-m-d H:i:s");
$CDATE=date("Y-m-d");

//匯入庫別
$STORE=($_POST["sid"])?$_POST["sid"]:1; //4:oneone 預設晶豪泰
//開帳用
$VENDOR=0;
//資料匯入
//新增狀態6：系統調帳
//先備份處理前的資料 PROD_D INVENTORY TRANSACTION


//$sql="UPDATE PROD_D SET TYPE=4 WHERE SID =1 AND TYPE IN (1,3)";
//將所有檔案上的條碼改為該店家且狀態為1
if ($_REQUEST["upload"] <> ""){
	  if ($_FILES["upload_file"]["name"] <>"" and $_FILES["upload_file"]["size"] >0 ){
	      
       
	      
	       //1.儲存資料
	       //共執行幾筆
	       $total=0;
	       //條碼長度不正確的
	       $lerr=0;
	       //系統內不存在
	       $nexist=0;
	       //資料重複幾筆
	       $dup=0;
	       //儲存幾筆
	       $insr=0;
        $lines = file($_FILES["upload_file"]["tmp_name"]);
        // Loop through our array, show HTML source as HTML source; and line numbers too.
        foreach ($lines as $line_num => $file_str) {
                $total++;
                //執行前要先測一下換行的問題
                //$line=str_replace("\n","",$line);
                $file_str=preg_replace("/\s/","",$file_str);
                $tmp_array=array();
                $tmp_array=explode(",",$file_str);
                $line=$tmp_array[1];
                //條碼長度不正確的
                if (strlen($line) <> 13){
                    $lerr++;
                    $lerr_str.=$line."<br/>";
                }
                else{
                    //系統內不存在
                    $sql="SELECT COUNT(*)T FROM PROD_D WHERE CODE='$line'";
                    $rts=$conn->Execute($sql);
                    $rt=$rts->FetchRow();
                    if ($rt["T"]==0){
                        $nexist++;
                        $nexist_str.=$line."<br/>";
                    }
                    else{
                        //資料重複幾筆
                        $sql="SELECT COUNT(*) T FROM INV_COOR_DATA WHERE CODE='$line'";
                        $rts=$conn->Execute($sql);
                        $rt=$rts->FetchRow();
                        if ($rt["T"]==0){
                            $insr++;
                            $COOR=array();
                            $COOR["SID"]=$STORE;
                            $COOR["CODE"]=$line;
                            write_to_table($COOR, "INV_COOR_DATA");
                        }
                        else{
                            $dup++;
                            $dup_str.=$line."<br/>";
                        }
                    }
                }
        }
	       $MSG="共執行".$total."筆<br/>";
	       $MSG.="系統內不存在的有".$nexist."筆：<br/>";
	       $MSG.=$nexist_str;
	       $MSG.="條碼長度錯誤的有".$lerr."筆：<br/>";
	       $MSG.=$lerr_str;
	       $MSG.="資料重複".$dup."筆：<br/>";
	       $MSG.=$dup_str;
	       $MSG.="儲存".$insr."筆<br/>";
	       $tpl->assign("MSG",$MSG);
	       
	       
        
   }
    
}



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

