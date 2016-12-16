<?php
include "main.php";
//include ('includes/checkUID.php');
include ('includes/tool.php');
//資料庫連接
$conn=CTDB();
$title="進貨作業";




//第一頁為"目前庫存狀況表"
  //可列出目前這家店的商品數量
  //可搜尋
//第二個功能為"客戶資料編輯"
//第三個功能為"廠商資料編輯"
//第四個功能為"進貨管理"
  //新商品輸入  並自動編barcode
  //舊的商品進貨
  //轉倉進貨(應由出貨時處理  由B店轉倉至A店並在A店售出)
//第五個功能為"出貨管理"
  //商品出貨
  //轉倉出貨
//第六個功能為"報表匯總"
  //區間內銷售多少商品 數量

//userid
$UID=$_COOKIE["wdcbcUID"];

//default template
$temp_file=GetTempFilename(__FILE__,"htm");


//這邊有兩種  1.新商品輸入  刷條碼(原商品條碼)-->是否已有該商品   YES--->舊商品進貨
        //                                                                                                    NO-->新商品進貨
        //   2.舊商品進貨
$edit=1;

//供應商
	$sql="SELECT ID,NAME FROM VEN_M ORDER BY ID";
	$rows=$conn->Execute($sql);
	while($row=$rows->FetchRow()){
									  $VEN_OPTION[$row["ID"]]=$row["NAME"];
	}
	$tpl->assign("VEN_OPTION",$VEN_OPTION);
	
	//產品類別
	$sql="SELECT ID,NAME FROM PROD_TYPE ORDER BY ID";
	$rows=$conn->Execute($sql);
	while($row=$rows->FetchRow()){
									   $TYPE_OPTION[$row["ID"]]=$row["NAME"];
	}
	$tpl->assign("TYPE_OPTION",$TYPE_OPTION);
	
	//維護進貨記錄:
	//倉庫類別  //要依該登入員工所屬倉庫來預設倉別
	$sql="SELECT ID,NAME FROM STORE_M  ORDER BY ID";
	$rows=$conn->Execute($sql);
	while($row=$rows->fetchRow()){
									   $S_OPTION[$row["ID"]]=$row["NAME"];
	}
	$tpl->assign("S_OPTION",$S_OPTION);


//if ($_POST["submit"]=="查詢"){
//不能用submit檢查
if ($_POST["CODE"]){
     //die($_POST["submit"]);
					//先查是否有這種條碼
					$sql="SELECT count(*) T FROM PROD_M WHERE CODE1='".$_POST["CODE"]."' OR CODE2='".$_POST["CODE"]."'";
					$rows=$conn->Execute($sql);
					$TT=$rows->FetchRow();
					//print $sql;
					//die($row["T"]);
					if ($TT["T"]==0){
										//新商品  跳至新商品維護
									 //保留刷入的條碼
									 $edit=2;
									 $title1="商品資料維護";
									 //1.數量
									 //2.成本-->商品主檔及進貨資訊都要預留此欄位
									 //3.時間
									 //4.庫別
									 //5.收貨人員*
					}
					else{
									 //已有該資料  跳至進貨
									 $edit=3;
									 //顯示該商品資訊  若有重複則取第一筆
									 $sql="SELECT * FROM PROD_M WHERE  CODE1='".$_POST["CODE"]."' OR CODE2='".$_POST["CODE"]."' ORDER BY ID LIMIT 0,1";
									 $rows=$conn->Execute($sql);
					     $row=$rows->FetchRow();
					     
									 $Prod=new Pclass;
									 $Prod->conn=$conn;
									 $Prod->id=$row["ID"];
									 $tpl->assign("PID",$row["ID"]);
									 
									$Prod->Bdata= $Prod->GetBdata();
									//print_r($Prod->Bdata);
									 $tpl->assign("CDATA",$Prod->Bdata);
					     //print_r($row);
					     $INV_DATA= $Prod->GetIvn();
					     $IDATA=array();
					     if (sizeof($INV_DATA)>0){
					             foreach($INV_DATA as $key => $val){
					                          $IDATA[$key]["NAME"]=$val["NAME"];
					                          $IDATA[$key]["QTY".$val["TYPE"]]=$val["QTY"];
					             }
					     }
					     //print_r($IDATA);
					     $tpl->assign("IDATA",$IDATA);
					}
					//上方為商品資料
					//下方為進貨輸入
					$tpl->assign("CODE",$_POST["CODE"]);
}



if ($_POST["submit"]=="儲存"){
								if ($_POST["edit"]==2){
								    
								    //取號
								    $pdata["ID"]=$PID=GenID("PROD_M");
								    $pdata["NAME"]=$_POST["NAME"];
								    //原條碼
								    $pdata["CODE1"]=$_POST["CODE"];
								    //條碼變化
								    //////前三碼為商品類別 後面則為流水號7位
								    ////$CodeString=sprintf("%03d",$TYPE_OPTION);
								    //////取目前最後的編號
								    ////$sql="SELECT RIGHT(CODE2,7) NO FROM PROD_M WHERE TYPE=$TYPE_OPTION ORDER BY CODE2 DESC limit 0,1";
								    //////print $sql;
     	      ////$rows = $conn->Execute($sql);
								    ////if ($row = $rows->FetchRow()) {	 $CodeString = $CodeString. sprintf("%07d",((int) $row['NO'])+1) ;   }
            ////else { $CodeString = $CodeString .'0000001' ; }
            //前三碼為商品類別 後面則為原條碼
								    $pdata["CODE2"]=sprintf("%03d",$TYPE_OPTION).$_POST["CODE"];
								    $pdata["TYPE"]=$TYPE_OPTION;
								    $pdata["VID"]=$VEN_OPTION;
								    $pdata["UID"]=$UID;
								    $CDATE=($_POST["RDATE"]." ".$_POST["RTIME"]=="")?date("Y-m-d H:i:s"):$_POST["RDATE"]." ".$_POST["RTIME"];
								    $pdata["CDATE"]=$CDATE;
            write_to_table($pdata, "PROD_M");
            
								}
								
								//die('SID:'.$_POST["SID"]);
								if ($_POST["edit"]==3){
								     $PID=$_POST["PID"];
								     $SID=$_POST["SID"];
								}
								//再存入inv_trans
								//取號
								//取目前最後的編號  交易序號
								$txn["ID"]=GenID("INV_TRANS");
								//庫別
								$txn["SID"]=$SID;
								//1:進貨  2.銷貨 3.轉倉出 4.轉倉入  可能還會有一種預訂
								//轉倉步驟 1.在A倉建一筆"轉倉出" 2.在B倉建"轉倉入"
								//轉倉出  帳還在A倉  轉倉入  才將帳轉至B倉
								$txn["IID"]=1;
								$txn["PID"]=$PID;
								$txn["QTY"]=$_POST["QTY"];
								$txn["COST"]=$_POST["COST"];
								$txn["IDATE"]=date("Y-m-d H:i:s");
								$txn["UID"]=$_COOKIE["wdcbcUID"];
								
								$txn["CDATE"]=date("Y-m-d H:i:s");
								write_to_table($txn, "INV_TRANS");
								//更新數量
								$Prod=new Pclass;
								$Prod->conn=$conn;
								$Prod->id=$PID;
								$Prod->Bdata= $Prod->GetBdata();
								$INV_DATA= $Prod->GetIvn();
								
								//1:在庫 2.預轉出 3.預轉入
								$Prod->ModiftQty($_POST["SID"],1,$Prod->id,$_POST["QTY"]);
								
								$edit=1;
}


$conn->close();
$tpl->assign("edit",$edit);
$tpl->assign("submit", $submit);
$tpl->assign("title", $title);
$tpl->display($temp_file);


?>

