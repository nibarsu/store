<?php
include "main.php";
//temp file
$temp_file=GetTempFilename(__FILE__,"htm");
$curr_file=GetFilename(__FILE__);
//current file name
//資料庫連接
$conn=CTDB();
include ('includes/checkUID.php');

$title="員工資料";
$tpl->assign("title",$title);

//userid
$UID=$_COOKIE["wdcbcUID"];
$UNAME=$_COOKIE["wdcbcNAME"];

$EMP=  new EMPclass();
$EMP->conn=$conn;

//print_r($Ob);
$submit=$_POST["submit"];
$NAME=$_POST["name"];
$TYPE=$_POST["type"];
$FILE_NAME=$_POST["file_name"];
//$ID=GenCustID($conn);
//print $ID; 
if ($_REQUEST["gen_emp_list"] <> ""){
   $filename="emp_list";
   $hdata =array ("姓名", "聯絡電話1","聯絡電話2","地址","帳號","密碼");
   //excel內容  開帳用的不要
   $sql="SELECT EMP.NAME,EMP.PHONE1,EMP.PHONE2,EMP.ADDR,M.NAME ACC,M.PASS
      FROM USER_M M,EMP WHERE M.ID=EMP.ID AND EMP.ENABLE='Y'
      ORDER BY EMP.ID";
   $rows=$conn->Execute($sql);
   while($row=$rows->FetchRow()){
      $tdata[]=array("NAME"=>$row["NAME"],"PHONE1"=>$row["PHONE1"],"PHONE2"=>$row["PHONE2"],"ADDR"=>$row["ADDR"],
         "ACC"=>$row["ACC"],"PASS"=>$row["PASS"]);    
   }
   gen_file($filename,$hdata,$tdata);

}
if ($_POST["submit"]=="新增"){
   //檢查帳號是否已使用過
   $sql="SELECT COUNT(*) T FROM USER_M M,EMP WHERE M.ID=EMP.ID AND M.NAME='".$_POST["account"]."' AND EMP.ENABLE='Y'";
   $rts=$conn->Execute($sql);
   $rt=$rts->FetchRow();
   if ($rt["T"]==0){
      if ($NAME){
         $DATA=array();
         $DATA["EMP"]["NAME"]=$_POST["name"];
         $DATA["EMP"]["PHONE1"]=$_POST["phone1"];
         $DATA["EMP"]["PHONE2"]=$_POST["phone2"];
         $DATA["EMP"]["ADDR"]=$_POST["addr"];
         $DATA["EMP"]["HIRED_DATE"]=date("Y-m-d");
         $DATA["EMP"]["CTIME"]=date("Y-m-d H:i:s");

         $DATA["USER_M"]["NAME"]=$_POST["account"];
         $DATA["USER_M"]["PASS"]=$_POST["pass"];
         $DATA["USER_M"]["DID"]=$_POST["did"];
         $DATA["USER_M"]["SID"]=$_POST["sid"];
         $DATA["USER_M"]["STORE_FLAG"]=$_POST["store_flag"];

         $DATA["USER_D"]["DID"]=$_POST["did"];
         $DATA["USER_STORE_D"]["SID"]=$_POST["sid"];

         $EMP->GenEmp($DATA);
         //$Ob->AddData($DATA);
      }
   }
   else{
      jsback("該帳號重複，請重新輸入!!!");
   }
}




if ($_POST["submit"]=="編輯"){
   //更新
   if ($_POST["id"]){
      $D["ID"]=$_POST["id"];
      $D["NAME"]=$_POST["name"];
      $D["PHONE1"]=$_POST["phone1"];
      $D["PHONE2"]=$_POST["phone2"];
      $D["ADDR"]=$_POST["addr"];
      $D["ENABLE"]=$_POST["enable"];
      $EMP->UpdEMP($D);

      $USER_M["ID"]=$_POST["id"];
      $USER_M["NAME"]=$_POST["account"];
      $USER_M["PASS"]=$_POST["pass"];
      $USER_M["DID"]=$_POST["did"];
      $USER_M["SID"]=$_POST["sid"];
      $USER_M["STORE_FLAG"]=$_POST["store_flag"];

      //viewArray($USER_M);exit();
      $EMP->UpdUSERM($USER_M);

      $sql="DELETE FROM USER_STORE_D WHERE ID=".$_POST["id"];
      $conn->Execute($sql);
      $USD_data=array();
      $USD_data["ID"]=$_POST["id"];
      $USD_data["SID"]=$_POST["sid"];
      write_to_table($USD_data,"USER_STORE_D");


      //清除編輯狀態
      $edit=0;
      //跳回原畫面
   }

}

//庫別
$store_option=array();
$sql="SELECT * FROM STORE_M ORDER BY ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
   $store_option[$row["ID"]]=$row["NAME"];
}
$tpl->assign("store_option",$store_option);

$menu_option=array();
$sql="SELECT * FROM MENU_TYPE_D WHERE TYPE<>3 ORDER BY TYPE,ID";
$rows=$conn->Execute($sql);
while($row=$rows->FetchRow()){
   $menu_option[$row["ID"]]=$row["NAME"];
}
$tpl->assign("menu_option",$menu_option);



$EMP->ALLdata=$EMP->GetALLdata();
$tpl->assign("empdata",$EMP->ALLdata["EMP"]);
if ($edit==1){
   //丟至頁面上
   //viewArray($EMP->ALLdata["EMP"][$id]);
   $tpl->assign("edata",$EMP->ALLdata["EMP"][$id]);
}

$conn->close();

$tpl->assign("edit", $edit);
$tpl->assign("submit", $submit);
$tpl->display($temp_file);

?>
