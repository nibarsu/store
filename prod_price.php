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



$title="商品成本編輯";

//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];


$MC=  new Mclass();
$MC->conn=$conn;
$r=array();

function query_data($type,$name,$vid){
         global $conn;
         $wherestr="";
				     //商品類別
				     if ($type){
				             $wherestr =" AND M.TYPE=".$type;
				             
				     }
				     //品名
				     if ($name){
				             $wherestr .=" AND M.NAME like '%".$name."%'";
				     }
				     //PROD_M.COST--->這個是進貨成本COST2
				     //PROD_D.COST--->這個是門市成本COST
				     $sql="SELECT D.ID PID,D.LINE,D.RT_ID,M.TYPE,M.NAME PROD_NAME,D.CODE
				                  ,D.COST
				                  ,M.COST COST2
				             FROM PROD_D D,PROD_M M 
				            WHERE M.ID=D.ID 
				              AND D.TYPE NOT IN (2,4)  $wherestr 
				              ORDER BY D.RT_ID,M.TYPE,M.ID";
         $rows=$conn->Execute($sql);
         //print $sql; exit();
         //print $vid;exit();
         while($row=$rows->FetchRow()){
               //廠商
               if ($row["RT_ID"]==0){
                   $row["VID"]=1;
                   $row["VENDOR"]="開帳用";
               }
               else{
                   $sql="select VID FROM RT WHERE ID=".$row["RT_ID"];
                   $rs=$conn->Execute($sql);
                   $r=$rs->FetchRow();
                   $row["VID"]=$r["VID"];
                   $row["VENDOR"]=GetName($r["VID"],"VEN_M");
               }
               
               //類別
               $row["PROD_TYPE"]=GetName($row["TYPE"],"PROD_TYPE");
               if ($vid){
                    if ($row["VID"]==$vid){
                         $pdata[]=$row;
                    }
               }
               else{
                    $pdata[]=$row;
               }
               
               
               
         }
         
         return $pdata;
          
}







if ($_POST["submit"]=="儲存"){
     //viewarray($_POST);exit();
     //更新
     if ($_POST["code"]){
          //更新門市成本
          $sql="UPDATE PROD_D SET COST=".$_POST["cost"]." WHERE CODE='".$_POST["code"]."'";
          $conn->Execute($sql);
          //更新進貨成本
          $sql="SELECT ID FROM PROD_D WHERE CODE='".$_POST["code"]."'";
          $rs=$conn->Execute($sql);
          $r=$rs->FetchRow();
          $sql="UPDATE PROD_M SET COST=".$_POST["cost2"]." WHERE ID=".$r["ID"];
          $conn->Execute($sql);
          //清除編輯狀態
          $edit=0;
          //跳回原畫面
     }
     $qtype=$_POST["qtype"];
     $qname=$_POST["qname"];
     $pdata=query_data($qtype,$qname,"");
     $tpl->assign("pdata",$pdata);
								
								
}


if($edit){
        //viewarray($_REQUEST);exit();
        //將資料丟至編輯區
        $r=array();
        $sql="SELECT M.TYPE,M.NAME PROD_NAME,D.LINE,D.ID,D.CODE,D.COST,M.COST COST2 FROM PROD_D D,PROD_M M WHERE D.ID=M.ID AND D.CODE=".$_REQUEST["code"];
        $rs=$conn->Execute($sql);
        $r=$rs->FetchRow();
        
        
        $r["PROD_TYPE"]=GetName($r["TYPE"],"PROD_TYPE");
        //viewarray($r);
        $tpl->assign("e",$r);
        $qname=$_REQUEST["qname"];
        $qtype=$_REQUEST["qtype"];
        //print "<br/>type:".$qtype."<br/>"; 
        $pdata=query_data($qtype,$qname,"");
				    $tpl->assign("pdata",$pdata);
}


if ($_POST["submit"]=="查詢"){
				//viewarray($_POST);exit;
				$pdata=array();
				$qtype=$_POST["type"];
				//品名
				if ($_POST["qname"]){
				    $qname=$_POST["qname"];
				}
				
				//廠商
				if ($_POST["qvid"]){
				    $qvid=$_POST["qvid"];
				}
				$pdata=query_data($qtype,$qname,$qvid);
				$tpl->assign("pdata",$pdata);
}

//產品類別
$TYPE_OPTION=array();
//$TYPE_OPTION[]="";
$sql="SELECT ID,NAME FROM PROD_TYPE ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
								   $TYPE_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("TYPE_OPTION",$TYPE_OPTION);

//廠商
$VEN_OPTION=array();
//$VEN_OPTION[]="";
$sql="SELECT ID,NAME FROM VEN_M ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
								   $VEN_OPTION[$row["ID"]]=$row["NAME"];
}
$tpl->assign("VEN_OPTION",$VEN_OPTION);

$conn->close();
$tpl->assign("qtype",$qtype);
$tpl->assign("qname",$qname);
$tpl->assign("edit", $edit);
$tpl->assign("submit", $submit);
$tpl->assign("title", $title);
$tpl->display($temp_file);
?>
