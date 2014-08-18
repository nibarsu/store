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


$title="條碼例印";

//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];
$USTORE=$_COOKIE["wdcbcSTORE"];
$USID=$_COOKIE["wdcbcSTOREID"];
$CTIME=date("Y-m-d H:i:s");
$CDATE=date("Y-m-d");
$special_type=array("原電","副電","座充","拍立得","底片","GOLLA包","原廠電池","副廠電池","副廠充電器","遮光罩","快門線","防潮箱");
//丟入RT單號
if ($rtid){
    $sql="SELECT PM.NAME,PD.CODE FROM RT_D RD,PROD_M PM,PROD_D PD WHERE
      RD.PID=PM.ID AND PM.ID=PD.ID  AND PD.RT_ID=RD.ID AND RD.ID=$rtid";
     //print $sql;
    $rs=$conn->Execute($sql);
    while($r=$rs->FetchRow()){
            $BDATA[]=$r;
    }
}

if (count($CFLAG)>0){
        //viewArray($CFLAG);exit();
        $BDATA=array();
        if (is_array($CFLAG)){
            foreach($CFLAG as $key => $code){
                    $sql="SELECT M.NAME,D.CODE,M.TYPE FROM PROD_D D,PROD_M M WHERE M.ID=D.ID AND D.CODE='".$code."'";
                    //print $sql; 
                    $rs=$conn->Execute($sql);
                    $PROD_TYPE="";
                    while($r=$rs->FetchRow()){
                          $PROD_TYPE=GetName($r["TYPE"],"PROD_TYPE");
                          if (in_array($PROD_TYPE,$special_type)){
                                  $r["NAME"]=$PROD_TYPE.$r["NAME"];
                          }
                          $BDATA[]=$r;
                    }
            }
        
        }
}
$tpl->assign("BDATA", $BDATA);
$tpl->assign("title", $title);
$tpl->display($temp_file);
$conn->close();
?>

