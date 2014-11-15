<?php 
/**
 * handle the page of tuihuo 
 */
require_once ("../../common/config.php");
require_once ("../common/config.php");
require_once ("../common/fun.php");

 $orderid=F("orderid");
 	/*confirm the orderid isn't in the tuohuo table    start*/
 	$confirm_sql="select id from ".DB::table('tuihuo')." where Orderid=".$orderid;
 	$confirm_row=DB::fetch_first($confirm_sql);
 	if(!empty($confirm_row)){
 		echo "<br/><span style='color:red'>此订单已申请退款，请不必重复提交</span>";
 		return;
 	}		
 	/*confirm the orderid isn't in the tuohuo table   end*/	
 
 	/*get the infomation of orderdetail according to orderid  start*/
	$tuihuo_pro_sql="select * from ".DB::table('orderdetail')." where id='".$orderid."'";	
	$tuihuo_pro_row=DB::fetch_first($tuihuo_pro_sql);
	if($tuihuo_pro_row){
		$tuihuo=$tuihuo_pro_row;
	}
	/*get the infomation of orderdetail according to orderid  end*/
	
	
	unset($tuihuo['id']);	//I don't need the id data;
	
	$tuihuo['postdate']=time();
	$tuihuo['Orderid']=$orderid;
	$tuihuo['account']=$_POST['account'];
	$tuihuo['account_type']=$_POST['account_type'];
	$tuihuo['bank_address']=$_POST['bank_address'];
	$tuihuo['reason']=$_POST['reason'];	
	$tuihuo_string="'".implode("','", $tuihuo)."'";
	$tuihuo_keys=array_keys($tuihuo);
	$tuihuo_ziduan="`".implode("`,`", $tuihuo_keys)."`";	
	
	$tuihuo_sql="insert into ".DB::table('tuihuo')."(".$tuihuo_ziduan.") values (".$tuihuo_string.")";							
	$tuihuo_row=DB::query($tuihuo_sql);
	if($tuihuo_row){									
		echo "<br/><span style='color:green'>提交成功</span>";
	}else{
		echo "<br/><span style='color:RED'>已提交过</span>",mysql_error();
	}
 
 
 ?>