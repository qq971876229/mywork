<?php 
/**
 * the page of tui huo
 */
require_once ("../../common/config.php");
require_once ("../common/config.php");
require_once ("../common/fun.php");

 $orderid=intval(Q("orderid"));
 $detail_id=intval(Q("detail_id"));

if($orderid == 0){
	gotoerrpage();
}
$row = DB::fetch_first ( "SELECT * FROM " . DB::table ( 'order' ) . "    WHERE    username='".$ymy_UserName."' and  orderid=$orderid" );
if(!is_array($row)){
	gotoerrpage();
}
 
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>我的订单/我的云蚂蚁 - <?=webname?></title>
<?php include YMY_ROOT_PATH.'custom/style.php'; ?>

<link href="../css/member.css" rel="stylesheet" type="text/css" />
</head>
<style type="text/css">
	.tips{color:red;}
	.display_none{display:none;}
	.zhence{position:relative;top:30px;left:-70px;width:500px;height:60px;color:green;float:right;}
	.process{color:green}
</style>

<body>
<!-- 头部 -->
<?php 
require_once ("../custom/head.php");
require_once ("../../custom/menu.php");
?>
<!-- 头部 end -->

<div class="w clearfix">
	<div class="member"> 
		<h3 class="me_top">我的云蚂蚁<label>&gt; 我的订单</label></h3>
		<div class="orderWarp">
			<div class="order-title">
				<div class="order-num">
					订单编号：<i class="blue"><?=$row["void"]?></i>
					下单时间：<?=MyDate( 'Y-m-d', $row ["postdate"] )?>
				</div>
			</div>
			<div class="goods-order">
				<h2>商品清单</h2>
				<table class="goods-table" cellpadding="0" cellspacing="0">
					<tr class="ltit">
						<th width="158">子单号</th>
						<th width="158"></th>
						<th width="306">商品</th>
						<th width="313">单价</th>
						<th width="102">数量</th>
						<th width="119">操作</th>
					</tr>
					<?php
					$querys = DB::query ( "select a.*,b.images from " . DB::table ( 'orderdetail' ) . " a left join " . DB::table ( 'product' ) . " b on a.proid=b.id where a.id='".$detail_id."' order by a.id desc" );
					//echo("select a.*,b.images from " . DB::table ( 'orderdetail' ) . " a left join " . DB::table ( 'product' ) . " b on a.proid=b.id where a.id='".$detail_id."' order by a.id desc");
					while(false !== ($rows = DB::fetch($querys))){
						$houtai=1;	/* to determine whether the data in the tuihuo table */						
					?>
					<tr>
						<td align="center" ><?=$rows ["id"]?></td>
						<td align="center" >
							<a  href="/product-<?=$rows ["proid"]?>.html" target="_blank">
								<img alt="<?=$rows ["proname"]?>" src="<?=$rows ["images"]?>" class="simg" />
							</a>
						</td>
						<td>
							<a href="/product-<?=$rows ["proid"]?>.html" class="blue" target="_blank"><?=$rows ["proname"]?></a>
						</td>
						<td width="313" align="center"><span class="red price">￥<?=$rows ["price"]?></span></td>
						<td width="102" align="center"><?=$rows ["procount"]?></td>
						<td width="119" align="center">
							<?php
							if   ($rows ["state"]>0){
								echo " <a href=\"javascript:void(null)\"   class=\"W_btn_b m blue\" onclick=\"tuihuo('".$rows ["id"]."')\"><span>查看进程</span></a>";
							}
							?>
						</td>
					</tr>
					<?php } ?>
				</table>
			</div>
			
			<!-- 申请退款   start -->
			<div class="user-info disnone" id="tuihuo_reason">						
						<br/>
						<div class="process">
							<?php 
								if($houtai==1){
									$tuihuo_sql="select checked from ".DB::table('tuihuo')." where Orderid=".$detail_id;
									$tuihuo_result=DB::fetch_first($tuihuo_sql);								
									if(!empty($tuihuo_result)){
										if($tuihuo_result['checked']==0){
											echo "提交订单&nbsp;&nbsp;---><span style='color:red'>客服审核</span>--->财务审核--->退款成功<br/>";
										}elseif ($tuihuo_result['checked']==1){
											echo "提交订单&nbsp;&nbsp;--->客服审核---><span style='color:red'>财务审核</span>--->退款成功<br/>";
										}else{										
											echo "提交订单&nbsp;&nbsp;--->客服审核--->财务审核---><span style='color:red'>退款成功</span><br/>";
										}
									}
								}
							?>											
						</div>						
														
			</div>
			<!-- 申请退款   end -->
			
			<div class="user-info">
				<dl>
					<dt>收货人信息</dt>
					<dd>姓　　名：<?=$row["receipt"]?></dd>
					<dd>联系电话：<?=$row["phone"]?></dd>
					<dd>联系手机：<?=$row["mobile"]?></dd>
					<dd>收货地址：<?=$row["address"]?></dd>
				</dl>
				<dl>
					<dt>支付方式</dt>
					<dd>付款方式： <?=$row["paytype"]?></dd>
				</dl>
				<dl>
					<dt>取货网点</dt>
					<dd>网点编号： <?=$row["networkid"]?></dd>
					<?php
					$rows = DB::fetch_first ( "SELECT * FROM " . DB::table ( 'network' ) . " WHERE number='".$row["networkid"]."'" );
					if (is_array($rows)){
						echo "<dd>网点名称： ".$rows["title"]."</dd>";
						echo "<dd>网点地址： ".$rows["address"]."</dd>";
						echo "<dd>负 责 人： ".$rows["master"]."</dd>";
						echo "<dd>手　　机： ".$rows["mobile"]."</dd>";
						echo "<dd>电　　话： ".$rows["phone"]."</dd>";
					}
					?>
				</dl>
				<div class="order-payfor">
					<p class="snPrice">订单金额：<i>¥ <?=cutprice($row ["amount"])?></i></p>
					<p class="snPrice red-price"><strong>应付金额：</strong><i>¥ <?=cutprice($row ["amount"])?></i></p>
				</div>
			</div>
		</div>
	</div>
</div>


<?php require_once ("../../custom/footer_new.php");?>
<?php include YMY_ROOT_PATH.'custom/script.php'; ?>

<script type="text/javascript" src="../js/ufun.js"></script>
<script language="javascript" type="text/javascript">
$(document).ready(function(){
	$('#sel').change(function(){
		var p1=$(this).children('option:selected').val(); 
		window.location.href="?s="+p1;//页面跳转并传参
	});
	
	//dynamic display the bank or the aiplay input start
	$("select[name=account_type]").change(function(){
		if($(this).val()==1){
			$("#alipay").css("display","block");
			$("#bankpay").css("display","none");			
		}else if($(this).val()==2){
			$("#alipay").css("display","block");
			$("#bankpay").css("display","block");
		}else{
			$("#alipay").css("display","none");
			$("#bankpay").css("display","none");
		}		
	});
	//dynamic display the bank or the aiplay input end
})
</script> 
</body>
</html>
