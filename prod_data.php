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



$title="商品資料編輯";

//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];


$MC=  new Mclass();
$MC->conn=$conn;
$r=array();


if ($_POST["gen_cost_rpt"] <> ""){
     //summary
     // include the php-excel class
     //require (dirname (__FILE__) . "/class-excel-xml.inc.php");
     require 'includes/excel/php-excel.class.php';
     $filename="trans_sum";
     // create a dummy array
     $doc[] =array ("類別", "品名", "成本");
     //excel內容
     $sql="SELECT * FROM PROD_M WHERE 1 $wherestr ORDER BY TYPE,ID";
     $wherestr="";
     
     //產品類別
     if ($_POST["type"]){
          $wherestr=" AND TYPE=".$_POST["type"]." ";
     }
     
     //品名
     if ($_POST["qname"]){
          $wherestr=" AND NAME like '%".$_POST["qname"]."%' ";
     }
     
     $rows=$conn->Execute($sql);
     //print $sql;
     while($row=$rows->FetchRow()){
            $row["PROD_TYPE"]=GetName($row["TYPE"],"PROD_M");
           	$doc[] =array($row["PROD_TYPE"],$row["NAME"],$row["COST"]);    
     }
     //header("Content-type: application/vnd.ms-excel");
     //header("Content-Disposition: attachment; filename=$filename" );
     // generate excel file
     $xls = new Excel_XML;
     $xls->addArray ( $doc );
     $xls->generateXML ($filename);//file name
     
     
	    exit();
	    
}


if ($_POST["submit"]=="查詢"){
				//viewarray($_POST);exit;
				$wherestr="";
				//商品類別
				if ($_POST["type"]){
				        $wherestr =" AND TYPE=".$_POST["type"];
				        $qtype=$_POST["type"];
				        
				}
				//品名
				if ($_POST["qname"]){
				        $wherestr .=" AND NAME like '%".$_POST["qname"]."%'";
				        $qname=$_POST["qname"];
				}
				
				
				
				
				$sql="SELECT * FROM PROD_M WHERE 1 $wherestr ORDER BY ID";
    $rows=$conn->Execute($sql);
    //分頁
				$limit=20;//一頁20筆資料
				$total_count=$rows->RecordCount();//總筆數
				$rcount=0;
				$page_temp=array();
				while($row=$rows->FetchRow()){
				      $rcount++;
          $row["PROD_TYPE"]=GetName($row["TYPE"],"PROD_TYPE");
          //$cdata[]=$row;
          $page_temp[]=$row;
          if ($rcount==$limit){
              $page_data[]=$page_temp;
              $page_temp=array();
              $rcount=0;
          }
    }
    //殘頁
    $page_data[]=$page_temp;
    $page_temp=array();
    $rcount=0;
    $edit=0;
    //viewarray($cdata);
    //$tpl->assign("cdata",$cdata);
    $tpl->assign("page_data",$page_data);
}



if ($_POST["submit"]=="儲存"){
        //viewarray($_POST);exit();
								//更新
								if ($_POST["prod_id"]){
													$D["ID"]=$_POST["prod_id"];
													$D["NAME"]=$_POST["prod_name"];
													//$D["COST"]=$_POST["prod_cost"];
													UpdDataId($D, "PROD_M");
													
													//清除編輯狀態
													$edit=0;
													//跳回原畫面
								}
								$qtype=$_POST["qtype"];
								$qname=$_POST["qname"];
								
								$wherestr="";
				    //商品類別
				    if ($qtype){
				            $wherestr =" AND TYPE=".$qtype;
				    }
				    //品名
				    if ($qname){
				            $wherestr .=" AND NAME like '%".$qname."%'";
				    }
				    $sql="SELECT * FROM PROD_M WHERE 1 $wherestr ORDER BY ID";
        $rows=$conn->Execute($sql);
        while($row=$rows->FetchRow()){
                $row["PROD_TYPE"]=GetName($row["TYPE"],"PROD_TYPE");
                 $cdata[]=$row;
        }
        $tpl->assign("cdata",$cdata);
								
								
}
if($edit){
        //viewarray($_REQUEST);exit();
        //將資料丟至編輯區
        $r=array();
        $sql="SELECT * FROM PROD_M WHERE ID=".$_REQUEST["id"];
        $rs=$conn->Execute($sql);
        $r=$rs->FetchRow();
        $r["PROD_TYPE"]=GetName($r["TYPE"],"PROD_TYPE");
        //viewarray($r);
        $tpl->assign("e",$r);
        $qname=$_REQUEST["qname"];
        $qtype=$_REQUEST["qtype"];
}
//產品類別
$TYPE_OPTION=GetProdTypeList();
//$sql="SELECT ID,NAME FROM PROD_TYPE ORDER BY ID";
//$rows=$conn->Execute($sql);
//while($row=$rows->FetchRow()){
//								   $TYPE_OPTION[$row["ID"]]=$row["NAME"];
//}
$tpl->assign("TYPE_OPTION",$TYPE_OPTION);

$conn->close();
$tpl->assign("qtype",$qtype);
$tpl->assign("qname",$qname);
$tpl->assign("edit", $edit);
$tpl->assign("submit", $submit);
$tpl->assign("title", $title);
$tpl->display($temp_file);
?>
