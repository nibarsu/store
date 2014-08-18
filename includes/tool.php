<?php


function GetTempFilename($file_str,$temp_ext){
              //目前目錄
              $filestr=str_replace('\\', '/', $file_str);
              $dirpath= str_replace('\\', '/', dirname($file_str));
              $cfile_name=str_replace($dirpath."/",'',$filestr);
              //拆成檔名及副檔名
              list($temp_file_head, $ext) =split("[.]",$cfile_name);
              //echo $temp_file_head."-----".$ext."<br>";
              $temp_file=$temp_file_head.".".$temp_ext;
								
              return $temp_file;
}

function GetFilename($file_str){
              //目前目錄
              $filestr=str_replace('\\', '/', $file_str);
              $dirpath= str_replace('\\', '/', dirname($file_str));
              $cfile_name=str_replace($dirpath."/",'',$filestr);
              //拆成檔名及副檔名
              list($temp_file_head, $ext) =split("[.]",$cfile_name);
              //echo $temp_file_head."-----".$ext."<br>";
              return $temp_file_head;
}

function GenCustID($conn){
								       //取目前最後的編號
								       $sql="SELECT MAX(ID) T FROM CUST_M";
								       $rows = $conn->Execute($sql);
								       $row = $rows->FetchRow();
								       //print_r($row);
								       $CUST_ID=($row["T"]=="")?1:$row["T"]+1;
								       //print $CUST_ID;exit;
								       return $CUST_ID; 
}



function AddCust($conn,$CDATA){
								      $sql="INSERT INTO CUST_M(ID,NAME,PHONE,EMAIL)
								      values(".$CDATA["ID"].",'".$CDATA["NAME"]."','".$CDATA["PHONE"]."','".$CDATA["EMAIL"]."')";
								      if(!$conn->Execute($sql))die('SQL ERROR: <BR><font color="#ff0000">' . $sql . '</font><hr>Program: ' .$_SERVER["SCRIPT_NAME"]  . ( $_SERVER["QUERY_STRING"] != '' ? '&' : '' ) . $_SERVER["QUERY_STRING"] . ' <BR>Error LINE: ' . __LINE__ );
}

function CTDB(){
								       //Connect To Data Base
								       include ('includes/dbconfig.php');
               include('includes/adodb/adodb.inc.php');
               $conn = &ADONewConnection('mysql');
               //$conn->debug=ture;
               $conn->Connect($dbhost, $dbuser, $dbpasswd, $dbname);

               if(!$conn){
               								print "function CTDB error!!!!"."Program: " .$_SERVER["SCRIPT_NAME"]  . ( $_SERVER["QUERY_STRING"] != '' ? '&' : '' ) . $_SERVER["QUERY_STRING"] . ' <BR>Error LINE: ' . __LINE__ ;exit;
               }
               $conn->Execute("SET NAMES UTF8");
               $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
               
               return $conn; 
}						
class PCLASS{
      var $conn;
      var $AllData;
      
      function GetAllData($id){
               //PROD_M
               $sql="SELECT M.*,M.NAME PROD_NAME,T.NAME PROD_TYPE,V.NAME VEN_NAME,E.NAME REC_EMP_NAME 
                 FROM PROD_M M,PROD_TYPE T,VEN_M V,EMP E WHERE M.TYPE=T.ID AND M.VID=V.ID AND M.UID=E.ID AND M.ID=$id ";
               $rs=$this->conn->Execute($sql);
               $r=$rs->FetchRow();
               $this->AllData["HDATA"]=$r;      
               
               //PROD_D
               $sql="SELECT PD.ID,PD.LINE,PD.CODE,SM.NAME STORE_NAME,TT.NAME 
                  FROM PROD_D PD,STORE_M SM,TRANS_TYPE TT 
                  WHERE PD.SID=SM.ID AND PD.TYPE=TT.ID AND PD.ID=$id ORDER BY LINE";
               $rs=$this->conn->Execute($sql);
               while($r=$rs->FetchRow()){
                     $this->AllData["DDATA"][]=$r;
               }

      }
        
}

class Vclass{
								  ///廠商class
								  var $conn;
								  var $Data;//廠商主檔array
								  
								  //抓取廠商主檔資料
								  function GetData($id){
								  								       $sql="SELECT * FROM VEN_M WHERE ID=$id";
								  								       $rows=$this->conn->Execute($sql);
								  								       $this->Data=$rows->FetchRow();
								  }
								  
								  function GenID(){
                         //取目前最後的編號
								                 $sql="SELECT MAX(ID) T FROM VEN_M";
								                 //print $sql; 
								                 $rows = $this->conn->Execute($sql);
								                 $row = $rows->FetchRow();
								                 //print_r($row);
								                 $ID=($row["T"]=="")?1:$row["T"]+1;
								                 //print $CUST_ID;exit;
								                 return $ID; 
								  }
								  
								  //新增廠商主檔
								  function AddData($DATA){
								  								      //取號
								  								      $ID=$this->GenID();
								  								      $sql="INSERT INTO VEN_M(ID,NAME,ADDR,PHONE)VALUES(".$ID.",'".$DATA["NAME"]."'
								  								      ,'".$DATA["ADDR"]."','".$DATA["PHONE"]."')";
								  								      $this->conn->Execute($sql);
								  }
     
								  function UpdData($DATA){
								  								      $sql="UPDATE VEN_M SET NAME='".$DATA["NAME"]."',
								  								      ADDR='".$DATA["ADDR"]."',PHONE='".$DATA["PHONE"]."'
								  								      WHERE ID=".$DATA["ID"];
								  								      //print  $sql;exit;
								  								      $this->conn->Execute($sql);
								  }
}

class Sclass{
								  ///倉庫
								  var $conn;
								  var $Data;
								  
								  //抓取倉庫主檔資料
								  function GetData($id){
								  								       $sql="SELECT * FROM STORE_M WHERE ID=$id";
								  								       $rows=$this->conn->Execute($sql);
								  								       $this->Data=$rows->FetchRow();
								  }
								  
								  function GenID(){
                         //取目前最後的編號
								                 $sql="SELECT MAX(ID) T FROM STORE_M";
								                 //print $sql; 
								                 $rows = $this->conn->Execute($sql);
								                 $row = $rows->FetchRow();
								                 //print_r($row);
								                 $ID=($row["T"]=="")?1:$row["T"]+1;
								                 //print $CUST_ID;exit;
								                 return $ID; 
								  }
								  
								  //新增倉庫主檔
								  function AddData($DATA){
								  								      //取號
								  								      $ID=$this->GenID();
								  								      $sql="INSERT INTO STORE_M(ID,NAME,REMARK)VALUES(".$ID.",'".$DATA["NAME"]."'
								  								      ,'".$DATA["REMARK"]."')";
								  								      $this->conn->Execute($sql);
								  }
     
								  function UpdData($DATA){
								  								      $sql="UPDATE STORE_M SET NAME='".$DATA["NAME"]."',
								  								      REMARK='".$DATA["REMARK"]."' 
								  								      WHERE ID=".$DATA["ID"];
								  								      //print  $sql;exit;
								  								      $this->conn->Execute($sql);
								  }
}


class Bclass{
								  ///basic class
								  var $conn;
								  var $Data;
								  var $TableName;
								  //抓取資料
								  function GetData($id){
								  								       $sql="SELECT * FROM $this->TableName WHERE ID=$id";
								  								       //print $sql; 
								  								       $rows=$this->conn->Execute($sql);
								  								       $this->Data=$rows->FetchRow();
								  }
								  
								  //歷史資料
								  function GetDatas(){
								  								       $sql="SELECT * FROM $this->TableName ORDER BY ID";
								  								       //print $sql; 
								  								       $rows=$this->conn->Execute($sql);
								  								       while($row=$rows->FetchRow()){
								  								       								  $DATA[]=$row;
								  								       }
								  								       return $DATA;
								  }
								  //取號
								  function GenID(){
                         //取目前最後的編號
								                 $sql="SELECT MAX(ID) T FROM $this->TableName";
								                 //print $sql; exit;
								                 $rows = $this->conn->Execute($sql);
								                 $row = $rows->FetchRow();
								                 //print_r($row);
								                 $ID=($row["T"]=="")?1:$row["T"]+1;
								                 //print $CUST_ID;exit;
								                 return $ID; 
								  }
								  
								  //新增
								  function AddData($data){
								  								      //取號
								  								      $ID=$this->GenID();
								  								      //print $ID;exit;
								  								      $data["ID"]=$ID;
								  								      if ( sizeof($data)==0 ) return ;
                        $pkey = $pdata = array() ; 
                        foreach( $data as $key => $val ) {
                                      $pkey[] = $key ; 
                                      $pdata[] = $val ; 
                        }
                        $sql = " insert into " . $this->TableName . " ( "  . implode( ',' , $pkey )  ." ) values ('" . implode("','", $pdata) . "')" ;
                        // die($sql);
                        $r = $this->conn->Execute($sql);
                        if (!$r) {
	   	                         print "ERROR-- $sql"."Program: " .$_SERVER["SCRIPT_NAME"]  ." <BR>Error LINE: ". __LINE__ ;
	   	                         exit();
	                       }
                        return ;
								  }
          //更新
								  function UpdData($data){
								  								      if ( sizeof($data)==0 ) return ;
                        $pkey = $pdata = array() ; 
                        $t_flag="N";
                        foreach( $data as $key => $val ) {
                        								      if ($key!="ID"){
                        								      								if ($t_flag!="Y"){
                        								      													$t_flag="Y";
                        								      								}
                        								      								else{
                        								      													$set_str.=",  ";
                        								      								}
                        								              $set_str.=$key."='".$val."'";
                        								      }
                        }
								  								      $sql="UPDATE $this->TableName SET $set_str
								  								      WHERE ID=".$data["ID"];
								  								      //print  $sql;exit;
								  								      $this->conn->Execute($sql);
								  }
}

//inventory
class invclass{
        var $conn;
        
        function GetInvData($sid="%",$pid="%"){
                   $sql="SELECT S.NAME STORE_NAME,P.NAME PROD_NAME,SUM(I.QTY) QTY
                   FROM INVENTORY I,
                   PROD_M P,
                   STORE_M S 
                   WHERE I.PID=P.ID AND I.SID=S.ID
                   AND SID LIKE '$sid'
                   AND PID LIKE '$pid'
                   AND I.TYPE=1
                   GROUP BY S.NAME,P.NAME";
                   $rs=$this->conn->Execute($sql);
                   while($r=$rs->FetchRow()){
                          $data[]=$r;
                   }
                   return $data;
        }
}



//帳號權限
class Aclass{
								  var $conn;
								  var $id;//帳號id EMP.ID 同時也是USER_M.ID
								  var $ALLdata;
								  
								  //抓取資料
								  function GetALLdata(){
								  								 $sql="SELECT * FROM EMP WHERE ID=$this->id";
								  								 //die($sql); 
								  								 $rows=$this->conn->Execute($sql);
								  								 $this->ALLdata["EMP"]=$rows->FetchRow();
								  								 
								  								 $sql="SELECT M.DID,D.FILE_NAME DEFAULT_FILE,M.SID,S.NAME DSTORE_NAME,M.STORE_FLAG 
								  								 FROM MENU_TYPE_D D,USER_M M,STORE_M S WHERE D.ID=M.DID AND M.SID=S.ID AND M.ID=$this->id";
								  								 //die($sql); 
								  								 $rows=$this->conn->Execute($sql);
								  								 $this->ALLdata["USER_M"]=$rows->FetchRow();
								  								 
								  								 $sql="SELECT D.*,D1.TYPE,D1.NAME MENU_NAME,D1.FILE_NAME FROM USER_D D,MENU_TYPE_D D1 WHERE D.DID=D1.ID AND D.ID=$this->id
								  								  ORDER BY D1.TYPE,D1.ID";
								  								 	//die($sql); 
								  								 $rows=$this->conn->Execute($sql);
								  								 while($row=$rows->FetchRow()){
								  								       $this->ALLdata["USER_D"][]=$row;
								  								 }
								  								 return $this->ALLdata;
								  }
								  
								  //新增帳號
								  function GenAcc($data){
								           //取號
								           $EMP_ID=GenID("EMP");
								           //丟入基本資料
								           write_to_table($data,"EMP");
								           //建立基本帳號密碼權限
								           write_to_table($USER_M,"USER_M");
								           write_to_table($USER_D,"USER_D");
								  }
								  
}

//系統選單
class EMPclass{
								  var $conn;
								  var $ALLdata;
								  
								  //抓取資料
								  function GetALLdata(){
								           //所有員工資料
								  								 $sql="SELECT * FROM EMP WHERE ENABLE='Y' ORDER BY CTIME DESC";
								  								 //die($sql); 
								  								 $rows=$this->conn->Execute($sql);
								  								 while($row=$rows->FetchRow()){
								  								       $this->ALLdata["EMP"][$row["ID"]]=$row;
								  								       $sql="SELECT M.*,S.NAME STORE_NAME,D.NAME MENU_NAME FROM USER_M M,STORE_M S,MENU_TYPE_D D WHERE M.SID=S.ID AND M.DID=D.ID AND M.ID=".$row["ID"];
								  								       //print $sql; 
								  								       $rs=$this->conn->Execute($sql);
								  								       $this->ALLdata["USER_M"][$row["ID"]]=$rs->FetchRow();
								  								       $this->ALLdata["EMP"][$row["ID"]]["ACCOUNT"]=$this->ALLdata["USER_M"][$row["ID"]]["NAME"];
								  								       $this->ALLdata["EMP"][$row["ID"]]["PASS"]=$this->ALLdata["USER_M"][$row["ID"]]["PASS"];
								  								       $this->ALLdata["EMP"][$row["ID"]]["DID"]=$this->ALLdata["USER_M"][$row["ID"]]["DID"];
								  								       $this->ALLdata["EMP"][$row["ID"]]["MENU_NAME"]=$this->ALLdata["USER_M"][$row["ID"]]["MENU_NAME"];
								  								       $this->ALLdata["EMP"][$row["ID"]]["SID"]=$this->ALLdata["USER_M"][$row["ID"]]["SID"];
								  								       $this->ALLdata["EMP"][$row["ID"]]["STORE_NAME"]=$this->ALLdata["USER_M"][$row["ID"]]["STORE_NAME"];
								  								       $this->ALLdata["EMP"][$row["ID"]]["STORE_FLAG"]=$this->ALLdata["USER_M"][$row["ID"]]["STORE_FLAG"];
								  								       $sql="SELECT * FROM USER_D WHERE ID=".$row["ID"];
								  								       $rs1=$this->conn->Execute($sql);
								  								       while($r1=$rs1->FetchRow()){
								  								             $this->ALLdata["USER_D"][$row["ID"]][]=$r1;
								  								       }
								  								       
								  								       $sql="SELECT * FROM USER_STORE_D WHERE ID=".$row["ID"];
								  								       $rs2=$this->conn->Execute($sql);
								  								       while($r2=$rs2->FetchRow()){
								  								             $this->ALLdata["USER_STORE_D"][$row["ID"]][]=$r2;
								  								       }
								  								 }
								  								 
								  								 return $this->ALLdata;
								  }
								  
								  //新增
								  function GenEmp($data){
								           //取號
								           $id=GenID("EMP");
								           //基本資料
								           $data["EMP"]["ID"]=$id;
								           write_to_table($data["EMP"],"EMP");
								           //帳號主檔
								           $data["USER_M"]["ID"]=$id;
								           write_to_table($data["USER_M"],"USER_M");
								           //權限
								           $data["USER_D"]["ID"]=$id;
								           write_to_table($data["USER_D"],"USER_D");
								           
								           //預設權限
								           $sql="SELECT * FROM MENU_TYPE_D WHERE BASIC='Y' ORDER BY ID";
								           $rows=$this->conn->Execute($sql);
								           while($row=$rows->FetchRow()){
								                 $did=$row["ID"];
								                 $sql="SELECT COUNT(*) T FROM USER_D WHERE ID=$id AND DID=$did";
								                 $rs=$this->conn->Execute($sql);
								                 $r=$rs->FetchRow();
								                 if ($r["T"]==0){
								                      $temp_data=array();
								                      $temp_data["ID"]=$id;
								                      $temp_data["DID"]=$did;
								                      write_to_table($temp_data,"USER_D");
								                 }
								           }
								           
								           //倉庫權限
								           $data["USER_STORE_D"]["ID"]=$id;
								           write_to_table($data["USER_STORE_D"],"USER_STORE_D");
								           
								  }
								  
								  //更新選單類別
								  function UpdEMP($data){
								           UpdDataId($data, "EMP");
								  }
								  
								  function UpdUSERM($data){
								           UpdDataId($data, "USER_M");
								           $sql="SELECT COUNT(*) T FROM USER_D WHERE ID=".$data["ID"]." AND DID=".$data["DID"];
								           //print $sql;exit(); 
								           $rs=$this->conn->Execute($sql);
								           $r=$rs->FetchRow();
								           if ($r["T"]==0){
								               $USER_D["ID"]=$data["ID"];
								               $USER_D["DID"]=$data["DID"];
								               //viewArray($USER_D);exit();
								               write_to_table($USER_D,"USER_D");
								           }
								  }
								  
								  
								  
}

//系統選單
class Mclass{
								  var $conn;
								  var $ALLdata;
								  
								  //抓取資料
								  function GetALLdata(){
								           //所有選單類別
								  								 $sql="SELECT * FROM MENU_TYPE ORDER BY ID";
								  								 //die($sql); 
								  								 $rows=$this->conn->Execute($sql);
								  								 while($row=$rows->FetchRow()){
								  								       $this->ALLdata["MENU_TYPE"][$row["ID"]]=$row;
								  								 }
								  								 
								  								 
								  								 $sql="SELECT D.ID,D.TYPE,M.NAME MENU_TYPE,D.NAME MENU_NAME,D.FILE_NAME,D.BASIC FROM MENU_TYPE_D D,MENU_TYPE M WHERE D.TYPE=M.ID ORDER BY M.ID,D.ID";
								  								 //die($sql); 
								  								 $rows=$this->conn->Execute($sql);
								  								 while($row=$rows->FetchRow()){
								  								       $this->ALLdata["MENU_TYPE_D"][$row["ID"]]=$row;
								  								 }
								  								 
								  								 return $this->ALLdata;
								  }
								  
								  //新增選單類別
								  function GenType($data){
								           //取號
								           $data["ID"]=GenID("MENU_TYPE");
								           write_to_table($data,"MENU_TYPE");
								  }
								  
								  //更新選單類別
								  function UpdType($data){
								           UpdDataId($data, "MENU_TYPE");
								  }
								  
								  //新增選單
								  function GenMenu($data){
								           //取號
								           $data["ID"]=GenID("MENU_TYPE_D");
								           write_to_table($data,"MENU_TYPE_D");
								  }
								  
								  //更新選單
								  function UpdMenu($data){
								           UpdDataId($data, "MENU_TYPE_D");
								  }
								  
}




function GenID($TNAME){
           global $conn ;
           //取該table目前最後的編號
           $sql="SELECT MAX(ID) T FROM $TNAME";
           //print $sql; 
           $rows = $conn->Execute($sql);
           $row = $rows->FetchRow();
           //print_r($row);
           $ID=($row["T"]=="")?1:$row["T"]+1;
           //print $CUST_ID;exit;
           return $ID; 
}

function GenLine($ID,$TNAME){
           global $conn ;
           //取該table目前最後的編號
           $sql="SELECT MAX(LINE) T FROM $TNAME WHERE ID=$ID";
           //print $sql; 
           $rows = $conn->Execute($sql);
           $row = $rows->FetchRow();
           //print_r($row);
           $LINE=($row["T"]=="")?1:$row["T"]+1;
           //print $CUST_ID;exit;
           return $LINE; 
}

function GenLine2($TYPE,$TNAME){
           global $conn ;
           
           //取該table目前最後的編號
           $sql="SELECT MAX(LINE) T FROM $TNAME WHERE TYPE=$TYPE";
           //print $sql; 
           $rows = $conn->Execute($sql);
           $row = $rows->FetchRow();
           //print_r($row);
           $LINE=($row["T"]=="")?1:$row["T"]+1;
           //print $CUST_ID;exit;
           return $LINE; 
}


function GetEmpList(){
         global $conn;
         $LIST=array();
         $LIST[]="";
         $sql="SELECT * FROM EMP WHERE ENABLE='Y' ORDER BY ID";
         $rs=$conn->Execute($sql);
         while($r=$rs->FetchRow()){
               $LIST[$r["ID"]]=$r["NAME"];
         }
         return $LIST;
          
}

function GetProdTypeList(){
         global $conn;
         $LIST=array();
         $LIST[]="";
         $sql="SELECT * FROM PROD_TYPE WHERE ENABLE='Y' ORDER BY ID";
         $rs=$conn->Execute($sql);
         while($r=$rs->FetchRow()){
               $LIST[$r["ID"]]=$r["NAME"];
         }
         return $LIST;
          
}

function GetMenuList(){
         global $conn;
         $LIST=array();
         $LIST[]="";
         $sql="SELECT * FROM MENU_TYPE_D ORDER BY TYPE,ID";
         $rs=$conn->Execute($sql);
         while($r=$rs->FetchRow()){
               $LIST[$r["ID"]]=$r["NAME"];
         }
         return $LIST;
          
}
function GetName($id,$table_name){
         global $conn;
         $sql="SELECT NAME FROM $table_name WHERE ID=$id";
         if ($rs=$conn->Execute($sql)){
             $r=$rs->FetchRow();
             return $r["NAME"];
         }
         else{
              print $sql;exit(); 
         }
          
}
function write_to_table($data = array(), $table_name ) {
	global $conn ;
    if ( sizeof($data)==0 ) return ;
    $pkey = $pdata = array() ; 
    foreach( $data as $key => $val ) {
        $pkey[] = $key ; 
        $pdata[] = $val ; 
    }
    $sql = " insert into " . $table_name . " ( "  . implode( ',' , $pkey )  ." ) values ('" . implode("','", $pdata) . "')" ;
    // tep_db_query($sql);	
    //die($sql);
     //if ($table_name=="USER_D") die($sql);
    $r = $conn->Execute($sql);
     if (!$r) {
	   	     print "ERROR-- $sql";
	   	     exit();
	   }
    return ;
}

function update_to_table($data = array(), $table_name, $whereData = array()) {
	global $conn ;
    if ( sizeof($data)==0 ) return ;
     
    $sql = " update ".$table_name." set  " ;
    $count = 0;
    foreach( $data as $key => $val ) {
        $count++;
        if ($count == 1) {
        	$sql = $sql.$key." = '".$val."'";
        } else {
        	$sql = $sql.", ".$key." = '".$val."'";
        }
    }
    
    $wcount = 0;
    foreach( $whereData as $key => $val ) {
    	$wcount++;
    	if ($wcount == 1) {
    		$sql = $sql." where  ".$key." = '".$val."' ";
    	} else {
    		$sql = $sql." and  ".$key." = '".$val."' ";
    	}
    }
   
    //echo "update_to_table() ============== sql = ".$sql."<br>";
    
    $r = $conn->Execute($sql);
     if (!$r) {
	   	     print "ERROR-- $sql";
	   	     exit();
	   }
    return ;
}

//更新
function UpdDataId($data = array(), $table_name){
								   if ( sizeof($data)==0 ) return ;
								   if ( $data["ID"]==0 or $data["ID"]=="" ) return ;
								   global $conn ;
           $pkey = $pdata = array() ; 
           $t_flag="N";
           foreach( $data as $key => $val ) {
           								      if ($key!="ID"){
           								      								if ($t_flag!="Y"){
           								      													$t_flag="Y";
           								      								}
           								      								else{
           								      													$set_str.=",  ";
           								      								}
           								              $set_str.=$key."='".$val."'";
           								      }
           }
								   $sql="UPDATE $table_name SET $set_str
								   WHERE ID=".$data["ID"];
								   //print  $sql;exit;
								   $conn->Execute($sql);
}

//fetchdata
function FetchRow($ID = 0, $table_name){
           if ( $ID==0 or $ID=="" ) return ;
								   global $conn ;
								   $sql="SELECT * FROM $table_name WHERE ID=$ID";
								   $rows=$conn->Execute($sql);
								   if ($rows->RecordCount() > 1){
								        //多筆資料
								        print "function name:FetchRowById file:".__FILE__." line:".__LINE__." REASON--too many rows. $sql";
	   	           exit();
								   }
								   
								   if ($rows->RecordCount() == 0){
								        //找不到資料
								        print "function name:FetchRowById file:".__FILE__." line:".__LINE__." REASON--no data found!!. $sql";
	   	           exit();
								   }
								   
								   if ($rows->RecordCount() == 1){
								        $row=$rows->FetchRow();
								        
								        return $row; 
								        
								   }
								   
}

function FetchRowS($ID = "%", $table_name){
								   global $conn ;
								   $sql="SELECT * FROM $table_name WHERE ID like '$ID'";
								   $rows=$conn->Execute($sql);
								   
								   if ($rows->RecordCount() == 0){
								        //找不到資料
								        print "function name:FetchRowS file:".__FILE__." line:".__LINE__." REASON--no data found!!. $sql";
	   	           exit();
								   }
								   
								   if ($rows->RecordCount() > 0){
								        while($row=$rows->FetchRow()){
								               $data[]=$row;
								        }
								        
								        return $data; 
								        
								   }
								   
}

function extArray($arr)
{
   echo '<td>';
   echo '<table cellpadding="0" cellspacing="0" border="1">';
   foreach ($arr as $key => $elem) {
      echo '<tr>';
       echo '<td>'.$key.'&nbsp;</td>';
       if (is_array($elem)) { extArray($elem); }
       else { echo '<td>'.htmlspecialchars($elem).'&nbsp;</td>'; }
      echo '</tr>';
   }
   echo '</table>';
   echo '</td>';
}
function viewArray($arr)
{
   echo '<table cellpadding="0" cellspacing="0" border="1">';
   foreach ($arr as $key1 => $elem1) {
       echo '<tr>';
       echo '<td>'.$key1.'&nbsp;</td>';
       if (is_array($elem1)) { extArray($elem1); }
       else { echo '<td>'.$elem1.'&nbsp;</td>'; }
       echo '</tr>';
   }
   echo '</table>';
}


function jsback($str) {
	 print '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	 print "<script>alert('$str');history.back();</script>";
	 exit();
}

function jslocation($str, $url="") {
	 if ($url == "") {
	 	  //$url = "modules.php?name=" . $_REQUEST["name"] . "&file=" . $_REQUEST["file"];
	 	  $url = "login.php";
	 }
	 print '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	 print "<script>alert('$str');document.location.replace('$url');</script>";
	 exit(); // 一定要exit 勿刪
}


function  utf8_2_big5($utf8_str)  {
              //會逐字轉換
                $i=0;
                $len  =  strlen($utf8_str);
                $big5_str="";
                for  ($i=0;$i<$len;$i++)  {
                        $sbit  =  ord(substr($utf8_str,$i,1));
                        if  ($sbit  <  128)  {
                                $big5_str.=substr($utf8_str,$i,1);
                        }  else  if($sbit  >  191  &&  $sbit  <  224)  {
                                $new_word=iconv("UTF-8","Big5",substr($utf8_str,$i,2));
                                if ($new_word==""){
                                     $word_temp="";
                                     $word_temp=substr($utf8_str,$i,2);
                                     $word_temp=base_convert(bin2hex(iconv("utf-8", "ucs-2", $word_temp)), 16, 10);
                                     //substr($utf8_str,$i,2)
                                     $new_word="&#".$word_temp; 
                                }
                                
                                //$big5_str.=($new_word=="")?"■":$new_word;
                                $big5_str.=$new_word;
                                $i++;
                        }  else  if($sbit  >  223  &&  $sbit  <  240)  {
                                $new_word=iconv("UTF-8","Big5",substr($utf8_str,$i,3));
                                if ($new_word==""){
                                     $word_temp="";
                                     $word_temp=substr($utf8_str,$i,3);
                                     $word_temp=base_convert(bin2hex(iconv("utf-8", "ucs-2", $word_temp)), 16, 10);
                                     //substr($utf8_str,$i,2)
                                     $new_word="&#".$word_temp; 
                                }
                                //$big5_str.=($new_word=="")?"■":$new_word;
                                $big5_str.=$new_word;
                                $i+=2;
                        }  else  if($sbit  >  239  &&  $sbit  <  248)  {
                                $new_word=iconv("UTF-8","Big5",substr($utf8_str,$i,4));
                                if ($new_word==""){
                                     $word_temp="";
                                     $word_temp=substr($utf8_str,$i,4);
                                     $word_temp=base_convert(bin2hex(iconv("utf-8", "ucs-2", $word_temp)), 16, 10);
                                     //substr($utf8_str,$i,2)
                                     $new_word="&#".$word_temp; 
                                }
                                //$big5_str.=($new_word=="")?"■":$new_word;
                                $big5_str.=$new_word;
                                $i+=3;
                        }
                }
                return  $big5_str;
  }

function gen_file($filename,$hdata,$tdata){
           set_time_limit(0);
           header("Content-disposition: attachment; filename=$filename.xls");
           header("Content-Type: application/force-download; charset=utf-8");
           header("Content-Transfer-Encoding: binary");
           echo "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; CHARSET=utf-8\">";
           print "<table border=1 style=\"border: 1px solid black;border-collapse: collapse;\">";
           print "<tr>\n";
           foreach ($hdata as $val) {
           	          print "<td>$val </td>";
           }
           print "</tr>\n";
           if (sizeof($tdata)>0){
                foreach ($tdata as $val) {
                           print "<tr>\n";
                           foreach ($val as $key => $t) {
                	                     //笨Excel  解決Excel自動判別為科學符號的問題
                	                     //print "<td>$t</td>";
                	                     if ($key=="CODE"){
                	                          print "<td>\"$t\"</td>";
                	                     }
                	                     else{
                	 	                        print "<td>$t</td>";
                	 	                   }
                	          }
                	          print "</tr>\n";
                }
           }
           print "</table>";
           
           exit();
}  
  
function trans_proc($type,$sid,$tsid=0,$fid=0,$pid,$line,$uid){
         global $conn ;
         //$type交易類別,$fsid 起始庫別,$tsid=0 目標庫別,$fid 交易單號,$pid 商品id,$uid交易人員
         $txn["ID"]=GenID("TRANSACTION");
				     //庫別
				     $txn["SID"]=$sid;
				     //1:進貨  2.銷貨 3.轉倉 4.RTV 5.退貨 6.調帳
				     $txn["IID"]=$type;
				     //單號
				     $txn["FID"]=$fid;
				     $txn["PID"]=$pid;
				     $txn["QTY"]=1;
				     //與進貨單上一致
				     $txn["PRICE"]=0;
				     $txn["UID"]=$uid;
				     $txn["CTIME"]=date("Y-m-d H:i:s");
				     //viewArray($txn);exit();
				     write_to_table($txn, "TRANSACTION");
				     
				     //INVENTORY
				     //更新數量
				     $inv["SID"]=$sid;
				     $inv["TYPE"]=1;
				     $inv["PID"]=$pid;
				     $inv["QTY"]=1;
				     switch($type){
				             case 1:
				             case 5:
				                      $sql="SELECT COUNT(*) T FROM INVENTORY WHERE SID=".$inv["SID"]." AND TYPE=".$inv["TYPE"]." AND PID=".$inv["PID"];
				                      //print $sql; 
								                  $irows=$conn->Execute($sql);
								                  $irow=$irows->FetchRow();
								                  if ($irow["T"]==0){
								                      write_to_table($inv, "INVENTORY");
								                  }
								                  else{
								                     $sql="UPDATE INVENTORY SET QTY=QTY+".$inv["QTY"]." WHERE SID=".$inv["SID"]." AND TYPE=".$inv["TYPE"]." AND PID=".$inv["PID"];
								                     $conn->Execute($sql);
								                  }
				                     
				             break;
				             case 2:
				             case 4:
				             case 6:
				                      $sql="UPDATE INVENTORY SET QTY=QTY-1 WHERE SID=".$inv["SID"]." AND TYPE=1 
							                     AND PID=".$inv["PID"];
							                   $conn->Execute($sql);
				                     
				             break;
				             case 3:
				                      //扣掉在庫數
							                   $sql="UPDATE INVENTORY SET QTY=QTY-".$inv["QTY"]." WHERE SID=".$inv["SID"]." AND TYPE=1 
							                     AND PID=".$inv["PID"];
							                   $conn->Execute($sql);
							                   
							                   //將數量轉至目標倉別的轉倉
							                   //檢查是否有該產品的轉倉資料  INVENTORY 的狀態只有1:在庫及2:轉倉
							                   $sql="SELECT COUNT(*) T FROM INVENTORY WHERE SID=$tsid AND TYPE=2 AND PID=".$inv["PID"];
							                   $rs=$conn->Execute($sql);
							                   $r=$rs->FetchRow();
							                   $exp["SID"]=$tsid;
							                   //1:在庫 2:轉倉
							                   $exp["TYPE"]=2;
							                   $exp["PID"]=$pid;
							                   $exp["QTY"]=1;
							                   if ($r["T"]==0){
							                       //目標倉別
							                       //viewArray($exp);exit();
							                       write_to_table($exp, "INVENTORY");      
							                   }
							                   else{
							                       $sql="UPDATE INVENTORY SET QTY=QTY+1 WHERE SID=$tsid AND TYPE=2 AND PID=".$exp["PID"];
							                       $conn->Execute($sql);
							                   }
							          break;
							          
				     }
				     
				     
				   		//$conn->Execute($sql);
				   			                 
				   	 //更新PROD_D
				   	 $pd["ID"]=$pid;
				   	 $pd["LINE"]=$line;
				   	 $pd["TYPE"]=$type;
				   	 $sql="UPDATE PROD_D SET TYPE=".$pd["TYPE"]." WHERE ID=".$pd["ID"]." AND LINE=".$pd["LINE"];
				   	 $conn->Execute($sql); 
        
}
?>