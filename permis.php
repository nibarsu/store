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



$title="使用者權限編輯";

//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];



$MC=  new Mclass();
$MC->conn=$conn;
$EMP=  new EMPclass();
$EMP->conn=$conn;

//print_r($Ob);
$submit=$_POST["submit"];
$NAME=$_POST["name"];
//$ID=GenCustID($conn);
//print $ID; 

if ($_POST["submit"]=="修改"){
     //viewarray($_POST);
     $sql="DELETE FROM USER_D WHERE ID=".$_POST["EUID"];
     $conn->Execute($sql);
     if (is_array($_POST["menu"])){
          foreach($_POST["menu"] as $key=>$val){
                    $DATA=array();
                    $DATA["ID"]=$_POST["EUID"];
                    $DATA["DID"]=$val;
                    //viewarray($DATA);
                    write_to_table($DATA,"USER_D");
          }
     }
     
}





$qid_rights=array();
//目前編輯的員工權限
//$quid
if ($quid){
     $sql="SELECT D.DID FROM USER_D D WHERE D.ID=$quid order by D.DID";
     //print $sql; 
     if ($rows=$conn->Execute($sql)){
          while($row=$rows->FetchRow()){
                 $qid_rights[$row["DID"]]="Y";
          }
     }
     $tpl->assign("EUID",$quid);
     $tpl->assign("ENAME",GetName($quid,"EMP"));
     
}
//viewarray($qid_rights);
//功能清單
$mlist=array();
$sql="SELECT ID,NAME FROM menu_type_d ORDER BY TYPE,ID";
$rows=$conn->Execute($sql);
$page=0;
$i=0;
while($row=$rows->FetchRow()){
       if ($i > 5){
            $i=0;
            $page++;
       }
       $i++;
       $row["FLAG"]=$qid_rights[$row["ID"]];
       $mlist[$page][$row["ID"]]=$row;
}
$tpl->assign("mlist",$mlist);
//viewarray($mlist);
//有效員工清單
$EMP_LIST=array();
$sql="SELECT * FROM EMP WHERE ENABLE='Y' ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
        $EMP_LIST[$row["ID"]]=$row;
}
$tpl->assign("EMP_LIST",$EMP_LIST);


$conn->close();
$tpl->assign("edit", $edit);
$tpl->assign("submit", $submit);
$tpl->display($temp_file);
?>
