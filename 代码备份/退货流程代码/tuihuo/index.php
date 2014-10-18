<?php 
require_once ("../../common/config.php");
$did=Q("id");
if (empty($did)){
	$rows = DB::fetch_first ( "SELECT id,CnName,tags,detail,content FROM " . DB::table ( 'static' ) . " where classid=2  order by sortid asc LIMIT 1" );
	if (! is_array ( $rows )) {
		gotoerrpage();
	}
	else{
		$did=$rows["id"];
		$title=$rows["CnName"];
		$content=$rows["content"];
		$tags=$rows["tags"];
		$detail=$rows["detail"];
	}
}
else{
	if (intval($did)==0){
		exit;
	}
	$rows = DB::fetch_first ( "SELECT id,CnName,tags,detail,content FROM " . DB::table ( 'static' ) . " WHERE id=$did" );
	if(!is_array($rows)){
		gotoerrpage();
	}
	else{
		$title=$rows["CnName"];
		$content=$rows["content"];
		$tags=$rows["tags"];
		$detail=$rows["detail"];
	}	   
}
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$title?> 帮助中心 - <?=webname?></title>
<meta name="keywords" content="<?=$tags?>" />
<meta name="description" content="<?=$detail?>" />
<?php include YMY_ROOT_PATH.'custom/style.php'; ?>

<link href="../../css/help.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php 
require_once ("../../custom/headm.php");
require_once ("../../custom/menu.php");
?>
<!-- 头部 end -->
 

<div class="middle">
	<div class="w">
		<div class="help_con clearfix">
			<div class="help_nav">
				<div class="orders"><span><b>购物指南</b></span></div>
				<div class="MB_left_nav">
					<?php helpnavleft(2,$did); ?>
				</div>
				<div class="orders"><span><b>配送方式</b></span></div>
				<div class="MB_left_nav">
					<?php helpnavleft(3,$did); ?>
				</div>
				<div class="orders"><span><b>支付方式</b></span></div>
				<div class="MB_left_nav">
					<?php helpnavleft(4,$did); ?> 
				</div>
				<div class="orders"><span><b>售后服务</b> </span></div>
				<div class="MB_left_nav">
					<?php helpnavleft(5,$did); ?>        
				</div>
				<div class="orders"><span><b>商家服务</b> </span></div>
				<div class="MB_left_nav">
					<?php helpnavleft(6,$did); ?>  
				</div>
				<div class="orders"><span><b>服务支持</b> </span></div>
				<div class="MB_left_nav">
					<?php helpnavleft(7,$did); ?>  
				</div> 
			</div>     

			<div class="right_te">
				<div class="title clearfix">
					<span class="ba"><?=$title?>&nbsp;</span>
					<span class="xin">
						<a href="/">首页</a> > <a href="/help/index.html">帮助中心</a>
					</span>
				</div>
				<div class="helpconbox">
				<!-- 退款表单提交start -->
					<form action="./index.php" method="POST" >
						<div class="username">
							用户名：<input type="text" name="username" />
						</div>
						<div class="orderid">
							订单编号：<input type="text" name="orderid" />							
						</div>
						<div class="reason">
							退货原因：<textarea name="reason" id="reason" cols="30" rows="10"></textarea>							
						</div>
						<div class="action">
							<input type="submit" name="sub" value='提交申请'>
							<input type="reset" name="reset" value="取消申请">
						</div>
						
					</form>
				<!-- 退款表单提交end -->	
				<!-- 提交处理 start-->
					<?php 				
						if($_POST['sub']){		
								/*通过用户提交的订单号来查询具体内容并插入到退货表中*/					
								$tuihuo['orderid']=trim($_POST['orderid']);
								$tuihuo_pro_sql="select * from ".DB::table('orderdetail')." where id='".$tuihuo['orderid']."'";
								$tuihuo_pro_row=DB::fetch_first($tuihuo_pro_sql);
								if($tuihuo_pro_row){
									$tuihuo=$tuihuo_pro_row;
								}
								//print_r($tuihuo_pro_row);exit;
								
								//$tuihuo['username']=trim($_POST['username']);
								$tuihuo['reason']=$_POST['reason'];	
								$tuihuo_string="'".implode("','", $tuihuo)."'";
								$tuihuo_keys=array_keys($tuihuo);
								$tuihuo_ziduan="`".implode("`,`", $tuihuo_keys)."`";
								
								
								$tuihuo_sql="insert into ".DB::table('tuihuo')."(".$tuihuo_ziduan.") values (".$tuihuo_string.")";
								echo $tuihuo_sql;								
								$tuihuo_row=DB::query($tuihuo_sql);
								if($tuihuo_row){									
									echo "提交成功";
									//echo "<script>alert('提交成功');location.href='../../help-32.html'</script>";
								}else{
									echo "已提交过";
									echo mysql_error();
								}
							
						}
					?>
				<!-- 提交处理 end-->
				</div>
			</div>
		</div>
	</div>
</div>


<?php require_once ("../../custom/footer_new.php");?>
<?php include YMY_ROOT_PATH.'custom/script.php'; ?>
</body>
</html>
