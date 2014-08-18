<?php
/*
 $Id: rtmdownload.php,v 1.1.1.1 2004/08/20 03:55:19 dannis Exp $
*/
//print "test";


session_start();
require_once('includes/excel/Worksheet.php');
require_once('includes/excel/Workbook.php');

  function HeaderingExcel($filename) {
      header("Content-type: application/vnd.ms-excel;charset=utf-8");
      header("Content-Disposition: attachment; filename=$filename" );
      header("Expires: 0");
      header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
      header("Pragma: public");
  }
  $FNAME = $_REQUEST["FNAME"];
  
  HeaderingExcel($FNAME);
  $workbook = new Workbook("-");
  // Creating the first worksheet
  $worksheet1 =& $workbook->add_worksheet('Report');
	$x=0 ;
	$y=0 ;
	
	//foreach ($pr_price_hdata as $val) {
	foreach ($hdata as $val) {
		$worksheet1->write_string($y,$x,$val) ;
		$x++ ;
	}

	//reset($pr_price_tdata) ;
	//foreach( $pr_price_tdata as $val) {
	reset($tdata) ;
	foreach( $tdata as $val) {
		$y++ ;
		$x=0 ;
		foreach( $val as $akey => $v) {			
				
					#$worksheet1->write_number($y,$x,$v);
				
				 #worksheet1->write_string($y,$x,$v) ;
				 $worksheet1->write($y,$x,$v) ;
				$x++ ;
			
		}
	}
  $workbook->close();

?>