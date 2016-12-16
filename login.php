<?php
include __DIR__.'/main.php';

//資料庫連接
$conn=CTDB();
$title="系統登入";
$msg="請輸入帳號密碼!!";
if ($_COOKIE["wdcbcUID"]!=""){
   header("Location:receive");
   exit();
}

if ($_POST["submit"]){
   //驗證帳號密碼
   $sql="SELECT M.* FROM USER_M M,EMP E WHERE M.ID=E.ID AND E.ENABLE='Y' AND M.NAME='".$_POST["name"]."' AND M.PASS='".$_POST["pass"]."'";
   //print $sql; 
   $rows=$conn->Execute($sql);
   if ($rows->RecordCount()==1){


      //存入cookie
      $row=$rows->FetchRow();
      setcookie("wdcbcUID", $row["ID"]);


      //ACCOUND CLASS
      $AC=  new Aclass();
      $AC->conn=$conn;
      $AC->id=$row["ID"];
      $AC->ALLdata=$AC->GetALLdata();
      //viewArray($AC->ALLdata);exit();
      //找尋預設登入頁面

      //可再存入其他相關資訊
      setcookie("wdcbcNAME", $AC->ALLdata["EMP"]["NAME"]);
      setcookie("wdcbcSTORE", $AC->ALLdata["USER_M"]["DSTORE_NAME"]);
      setcookie("wdcbcSTOREID", $AC->ALLdata["USER_M"]["SID"]);
      setcookie("wdcbcSTOREFLAG", $AC->ALLdata["USER_M"]["STORE_FLAG"]);
      header("Location:".$AC->ALLdata["USER_M"]["DEFAULT_FILE"]);

   }
   else{
      //找不到相關的帳號資訊
      $msg="輸入的帳號或密碼錯誤!!請重新輸入";
   }
}
$conn->close();
$tpl->assign("msg", $msg);
$tpl->assign("title", $title);
$tpl->display(GetTempFilename(__FILE__,"htm"));
?>
