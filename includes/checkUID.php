<?php

if ($_COOKIE["wdcbcUID"]==""){
      //die('aa');
      header("Location:login");
	     exit();
}
else{
    //ACCOUND CLASS
    $AC=  new Aclass();
    $AC->conn=$conn;
    $AC->id=$_COOKIE["wdcbcUID"];
    $AC->ALLdata=$AC->GetALLdata();
    //目前檔案權限檢查
    global $curr_file;
    global $tpl;
    $curr_file_flag="N";
    //die($curr_file_flag);
    //目前經手或待接收的單子
    
    $sql="SELECT COUNT(*) T FROM SO WHERE TYPE=3 AND TFLAG <>'Y' AND (TSID=".$_COOKIE["wdcbcSTOREID"]." OR SID=".$_COOKIE["wdcbcSTOREID"].")";
    $rs=$conn->Execute($sql);
    //print $sql; 
    $r=$rs->FetchRow();
    
    setcookie("wdcbcUNACCEPT", $r["T"]);
    if (sizeof($AC->ALLdata["USER_D"])>0){
      
            foreach($AC->ALLdata["USER_D"] as $key => $val){
                    if ($val["FILE_NAME"]==$curr_file){
                         $curr_file_flag="Y";
                    }
            }
    }
    else{
        //跳至預設頁面
        
        header("Location:".$AC->ALLdata["USER_M"]["DEFAULT_FILE"]);
	       exit();
    }
    
    if ($curr_file_flag=="Y"){
        //viewArray($AC->ALLdata["USER_D"]);
        $tpl->assign("MENU_DATA",$AC->ALLdata["USER_D"]);
    }
    else{
        //跳至預設頁面
        //jsback("您無此頁權限!!將轉跳回上一頁");
        jslocation("您無此頁權限!!將登出系統", "logout.php");
        //header("Location:".$AC->ALLdata["USER_M"]["DEFAULT_FILE"]);
	       //exit();
    }
}
?>