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


$title="庫存明細";


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
       WHERE I.SID=SM.ID AND I.PID=PM.ID AND PM.TYPE=PT.ID AND I.TYPE=IT.ID
         AND I.SID=PD.SID";
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
        
}

//$hdata=array("STORE_NAME","PROD_NAME","IN_STORE","EXP_STORE");
//$tdata=$RDATA;
//viewArray($RDATA);
$tpl->assign("RDATA",$RDATA);


//產生所有庫存資料
if ($_REQUEST["gen_inv_list"] <> ""){
     $filename="inv_dtl";
     // create a dummy array
     $hdata =array ("庫別", "類別", "品名","條碼","成本","狀態","廠商","進貨日期","進貨人員");
     //excel內容
     //$sql="SELECT S.NAME STORE_NAME,PD.NAME PROD_TYPE,M.NAME PROD_NAME,D.CODE,D.COST,IT.NAME IT_NAME,
     //              D.RT_ID
     //         FROM PROD_D D,
     //              PROD_M M,
     //              PROD_TYPE PD,
     //              INVENTORY I,
     //              STORE_M S,
     //              INV_TYPE IT
     //        WHERE D.ID=M.ID
     //          AND M.TYPE=PD.ID
     //          AND I.PID=M.ID
     //          AND I.SID=S.ID
     //          AND I.SID=D.SID
     //          AND I.TYPE=IT.ID
     //          AND D.TYPE IN (1,3)
     //          AND I.QTY<>0";
     $sql="SELECT S.NAME STORE_NAME,PD.NAME PROD_TYPE,M.NAME PROD_NAME,D.CODE,D.COST,IF (D.TYPE=1,'在庫','轉倉') IT_NAME,
                   D.RT_ID
              FROM PROD_D D,
                   PROD_M M,
                   PROD_TYPE PD,
                   STORE_M S
             WHERE D.ID=M.ID
               AND M.TYPE=PD.ID
               AND D.SID=S.ID
               AND D.TYPE IN (1,3)";
     //庫別
     if ($_POST["sid"]){
          $sql.=" AND D.SID=".$_POST["sid"];
          $filename.="_s".$_POST["sid"];
     }
     
     //產品類別
     if ($_POST["type"]){
          $sql.=" AND M.TYPE=".$_POST["type"];
          $filename.="_t".$_POST["type"];
     }
     
     //品名
     if ($_POST["name"]){
          $sql.=" AND M.NAME like '%".$_POST["name"]."%' ";
          //$filename.="_n".$_POST["name"];
     }
     $sql.="  ORDER BY D.SID,M.TYPE,M.NAME,D.LINE";
     
     $rows=$conn->Execute($sql);
     while($row=$rows->FetchRow()){
            $row["VENDOR_NAME"]="";
            $output_flag="Y";
            if ($row["RT_ID"]==0){
                 $row["VENDOR_NAME"]="開帳用";
                 $r["VID"]=0;
                 $row["RECTIME"]="";
                 $row["REC_MAN"]="";
            }
            else{
                 $sql="SELECT * FROM RT WHERE RT.ID=".$row["RT_ID"];
                 $rs=$conn->execute($sql);
                 $r=$rs->FetchRow();
                 $row["VENDOR_NAME"]=GetName($r["VID"],"VEN_M");
                 $row["RECTIME"]=$r["CTIME"];
                 $row["REC_MAN"]=GetName($r["UID"],"EMP");
            }
            //供應商
            if ($_POST["vid"]){
                 if ($r["VID"] != $_POST["vid"] )$output_flag="N";
            }
            
            if ($output_flag=="Y"){
           	    $tdata[]=array("STORE_NAME"=>$row["STORE_NAME"],"PROD_TYPE"=>$row["PROD_TYPE"],"PROD_NAME"=>$row["PROD_NAME"],
                "CODE"=>$row["CODE"],"COST"=>$row["COST"],"IT_NAME"=>$row["IT_NAME"],"VENDOR_NAME"=>$row["VENDOR_NAME"]
                ,$row["RECTIME"],$row["REC_MAN"]);
            }
     }
     gen_file($filename,$hdata,$tdata);
     
     
	    
}

if ($_REQUEST["gen_inv_list2"] <> ""){
     $filename="inv_summary";
     // create a dummy array
     $hdata =array ("庫別", "類別", "品名","狀態","數量");
     //excel內容
     //$sql="SELECT S.NAME STORE_NAME,PD.NAME PROD_TYPE,M.NAME PROD_NAME,SUM(I.QTY) QTY,IT.NAME IT_NAME
     //         FROM PROD_M M,
     //              INVENTORY I,
     //              PROD_TYPE PD,
     //              STORE_M S,
     //              INV_TYPE IT
     //        WHERE I.PID=M.ID
     //          AND M.TYPE=PD.ID
     //          AND I.SID=S.ID
     //          AND I.TYPE=IT.ID
     //          AND I.QTY<>0
     //          ";
     $sql="SELECT S.NAME STORE_NAME,PD.NAME PROD_TYPE,M.NAME PROD_NAME,D.RT_ID,SUM(D.TYPE) QTY,IF (D.TYPE=1,'在庫','轉倉') IT_NAME
              FROM PROD_M M,
                   PROD_D D,
                   PROD_TYPE PD,
                   STORE_M S
             WHERE M.TYPE=PD.ID
               AND M.ID=D.ID
               AND D.SID=S.ID
               AND D.TYPE IN (1,3)";
     //庫別
     if ($_POST["sid"]){
          $sql.=" AND D.SID=".$_POST["sid"];
          $filename.="_s".$_POST["sid"];
     }
     
     //產品類別
     if ($_POST["type"]){
          $sql.=" AND M.TYPE=".$_POST["type"];
          $filename.="_t".$_POST["type"];
     }
     
     //品名
     if ($_POST["name"]){
          $sql.=" AND M.NAME like '%".$_POST["name"]."%' ";
          //$filename.="_n".$_POST["name"];
     }
     $sql.="  GROUP BY S.NAME,PD.NAME,M.NAME,D.TYPE ORDER BY S.NAME,PD.NAME,M.NAME,D.RT_ID,D.TYPE";
     //print $sql;
     $rows=$conn->Execute($sql);
     while($row=$rows->FetchRow()){

            $row["VENDOR_NAME"]="";
            $output_flag="Y";
            if ($row["RT_ID"]==0){
                 $row["VENDOR_NAME"]="開帳用";
                 $r["VID"]=0;
                 $row["RECTIME"]="";
                 $row["REC_MAN"]="";
            }
            else{
                 $sql="SELECT * FROM RT WHERE RT.ID=".$row["RT_ID"];
                 $rs=$conn->execute($sql);
                 $r=$rs->FetchRow();
                 $row["VENDOR_NAME"]=GetName($r["VID"],"VEN_M");
                 $row["RECTIME"]=$r["CTIME"];
                 $row["REC_MAN"]=GetName($r["UID"],"EMP");
            }
            //供應商
            if ($_POST["vid"]){
                 if ($r["VID"] != $_POST["vid"] )$output_flag="N";
            }
            
            if ($output_flag=="Y"){
           	$tdata[]=array("STORE_NAME"=>$row["STORE_NAME"],"PROD_TYPE"=>$row["PROD_TYPE"],
           	"PROD_NAME"=>$row["PROD_NAME"],"IT_NAME"=>$row["IT_NAME"],"QTY"=>$row["QTY"]);    
            }
     }
     gen_file($filename,$hdata,$tdata);
	    
}
//
$S_OPTION=array();
$S_OPTION[]="";
$sql="SELECT ID,NAME FROM STORE_M WHERE ENABLE='Y' ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
  $S_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("S_OPTION",$S_OPTION);

//產品類別
$TYPE_OPTION=array();
$TYPE_OPTION=array("")+GetProdTypeList();
$tpl->assign("TYPE_OPTION",$TYPE_OPTION);

//供應商
$VEN_OPTION=array();
$VEN_OPTION[]="";
$sql="SELECT ID,NAME FROM VEN_M WHERE STATUS='O' ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
  $VEN_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("VEN_OPTION",$VEN_OPTION);

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

