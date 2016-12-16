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


$title="廠商未付款查詢";

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
$tpl->assign("eSDATE",$_POST["SDATE"]);
$tpl->assign("eEDATE",$_POST["EDATE"]);
if ($_POST["markpay"]=="標記為已付款"){
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
$sql="SELECT RT.ID RT_ID,RT.VID,V.NAME VEN_NAME,RT.AMT ,RT.CTIME,RT.PFLAG
 FROM RT RT,VEN_M V
WHERE RT.VID=V.ID AND RT.CTIME BETWEEN '".$SDATE."' AND '".$EDATE."' ";

if ($_POST["submit"]=="查詢"){
        //viewArray($_POST);
     //廠商
     if ($_POST["vid"]){
          $sql.=" AND RT.VID=".$_POST["vid"]." ";
     }
     
     $tpl->assign("evid",$_POST["vid"]);
     $sql.=" ORDER BY RT.CTIME DESC";
     
     
     //print $sql;
     $ttamt=0;
     $rs=$conn->Execute($sql);
     while($r=$rs->FetchRow()){
             $ttamt+=$r["AMT"];
             $r["RAMT"]=0;
             $sql="SELECT DISTINCT SO.ID,SO.AMT FROM SO,SO_D D,PROD_D PD WHERE SO.ID=D.ID AND D.PID=PD.ID AND D.PDLINE=PD.LINE AND SO.TYPE=4 AND PD.RT_ID=".$r["RT_ID"];
             //print $sql."<br>"; 
             $rows=$conn->Execute($sql);
             while($row=$rows->FetchRow()){
                   $r["RAMT"]+=$row["AMT"];
             }
             $RDATA[]=$r;
     }
     //viewarray($RDATA);
     $tpl->assign("RDATA",$RDATA);
     $tpl->assign("ttamt",$ttamt);     
     
     
     
     
}

if ($_POST["gen_ven_pay"] <> ""){
     $filename="inv_summary";
     // create a dummy array
     $hdata =array ("廠商","單號","已付未付","類別", "品名","數量","總金額","交易人員","交易日期","備註");
     
     //excel內容
     $sql="SELECT RT.*
              FROM RT
             WHERE RT.CTIME BETWEEN '".$SDATE."' AND '".$EDATE."' ";
     //庫別
     if ($_POST["vid"]){
          $sql.=" AND RT.VID=".$_POST["vid"]." ";
          
          $filename.="_".$_POST["vid"];
     }
     
     $sql.=" ORDER BY RT.VID";
     
     $rows=$conn->Execute($sql);
     //print $sql;
     $TEMP_ID="";
     $tamt=0;
     while($row=$rows->FetchRow()){
            $row["STORE_NAME"]=GetName($row["SID"],"STORE_M");
            $row["VENDOR_NAME"]=GetName($row["VID"],"VEN_M");
            
            $row["EMP_NAME"]=GetName($row["UID"],"EMP");
           	$tdata[] =array($row["VENDOR_NAME"],$row["ID"],$row["PFLAG"],"-","-","-",$row["AMT"],$row["EMP_NAME"],$row["CTIME"],$row["REMARK"]);
           	$sql="SELECT D.*,PM.NAME PROD_NAME,PM.TYPE FROM RT_D D,PROD_M PM WHERE D.PID=PM.ID AND D.ID=".$row["ID"]." ORDER BY D.LINE";
           	//print $sql;
           	$drows=$conn->Execute($sql);
           	while($drow=$drows->FetchRow()){
           	      $drow["PROD_TYPE"]=GetName($drow["TYPE"],"PROD_TYPE");
                  $tdata[] =array("-","-","-",$drow["PROD_TYPE"],$drow["PROD_NAME"],$drow["QTY"],$drow["SUBAMT"],"-","-","-","-");
                  $tamt+=$drow["SUBAMT"];
           	}
     }
     $tdata[] =array("-","-","-","-","-","合計",$tamt,"-","-","-","-");
     
	    gen_file($filename,$hdata,$tdata);
}

//
$V_OPTION=array();
$V_OPTION[]="";
$sql="SELECT ID,NAME FROM VEN_M WHERE STATUS='O' ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
								  $V_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("V_OPTION",$V_OPTION);


//表頭資料
$hdata["EMP_NAME"]=$UNAME;
$hdata["STORE_NAME"]=$USTORE;
$hdata["CDATE"]=$CDATE;


$tpl->assign("hdata",$hdata);
$tpl->assign("title", $title);
$tpl->display($temp_file);
$conn->close();
?>

