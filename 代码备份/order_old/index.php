<?php 
require_once ("../../common/config.php");
require_once ("../common/config.php");
require_once ("../common/fun.php");
$s=intval(Q("s"));
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>我的订单/我的云蚂蚁 - <?=webname?></title>
<?php include YMY_ROOT_PATH.'custom/style.php'; ?>

<link href="../css/member.css" rel="stylesheet" type="text/css" />
</head>
<body>
<!-- 头部 -->
<?php 
require_once ("../../custom/head.php");
require_once ("../../custom/menum.php");
?>
<!-- 头部 end -->

<div class="w clearfix">
	<div class="member"> 
		<h3 class="me_top">我的云蚂蚁<label>> 我的订单</label></h3>
		<!-- 左边导航 -->
		<?php require_once ("../custom/left.php"); ?>
		<!-- 左边导航 end -->
		<div class="member_right  clearfix">
			<div class="mrborder">
				<form method="get" name="selform" id="selform">
					<div class="tit">
						<div class="right select">
							交易状态：
							<select name="sel" id="sel">
								<option value="99">---请选择---</option>
								<option value="0" <?php if ($s==0) echo " selected"?> >待付款</option>
								<option value="1" <?php if ($s==1) echo " selected"?>  >已付款</option>
								<option value="2" <?php if ($s==2) echo " selected"?> >已发货</option>
								<option value="3" <?php if ($s==3) echo " selected"?> >交易成功</option>
								<option value="4" <?php if ($s==4) echo " selected"?> >已退款</option>
							</select>
						</div>
						我的订单
					</div>
				</form>
				<div class="rtbbox">
					<table width="100%" border="0" cellpadding="0" cellspacing="0" class="orderlist">
						<tr class="ltit">
							<th width="97" class="chkbox">商品图片</th>
							<th width="302" class="chkbox">商品名称</th>
							<th width="95">数量</th>
							<th width="117">商品单价(元)</th>
							<th width="108">交易状态</th>
							<th width="108">退款进程</th>
							<th width="97">操作</th>
						</tr>
						<?php
						$act=Q("act");

						if($act==="del"){
							$orderid=intval(Q("orderid"));
							if($orderid>0){
								$rows = DB::fetch_first ( "SELECT void FROM " . DB::table ( 'order' ) . " where username ='".$ymy_UserName."' and (state=-1 or state=0 ) and orderid =$orderid" );						
								if (is_array($rows)){
									DB::query ( "delete FROM ymy_order WHERE  orderid =$orderid" );
									DB::query ( "delete FROM ymy_orderdetail WHERE Orderid='".$rows["void"]."' " );
								}
							}
						}
						$number = 10;
						$page=intval(Q("page"));
						if($page==0) $page=1;
						$sqlStr = "select  distinct void, orderid,remark,amount,state,coupon,postdate,payamount,integral from " . DB::table ( 'order' ) . "  where   username='".$ymy_UserName."'";
						if($s!=99){
							if($s!=""){
								$sqlStr .= " And state=".intval($s)."";
							}
							if($s==0){
								$sqlStr .= " And state=0";
							}
						}
						$sqlStr .= " order by  orderid desc ";
						$allRecordset = db::getCount ( $sqlStr );
						$tpagecount = ceil ( $allRecordset / $number );
						if ($page > $tpagecount) $page = $tpagecount;
						$page = max ( intval ( $page ), 1 );
						$offset = $number * ($page - 1);	
						$query = DB::query ( "$sqlStr limit $offset, $number" );
						$b=1;
						$ptags="";
						while (false !== ($row = DB::fetch($query))){
						?>
							<tr class="litit">
								<td class="chkbox" colspan="5"> 订单编号：
									<label class="blue"><?=$row ["void"]?></label>
									总金额：
									<label class="price red">￥<?=cutprice( $row ["amount"] )?></label>
									<?php if(!empty($row['remark'])): ?>
									<label>(<?php echo $row['remark']; ?>)</label>
									<?php endif; ?>
									<?php
									if($row ["amount"] !=$row ["payamount"] ){
										echo "=<label class=\"price red\">￥".$row ["payamount"] ."</label>(支付)";
									}
									if($row["coupon"]>0){
										echo "+ <label class=\"price red\">￥".cutprice( $row ["coupon"] )."</label>(蚂蚁盾)";
									}
									if($row["integral"]>0){
										echo "+ <label class=\" red\">". $row ["integral "] ."</label>(积分)";
									}
									?>
								</td>
								<td  align="left" colspan="2">生成时间：<?=MyDate('Y-m-d H:i:s', $row ["postdate"])?></td>
							</tr>
							<?php
							$queryp = DB::query ( "select a.*,b.images,b.nums from " . DB::table ( 'orderdetail ' ) . " a left join " . DB::table ( 'product' ) . " b on a.proid=b.id where a.orderid='".$row["void"]."'  order by id desc" );
													
							$t=1;
							$bcount=mysql_num_rows($queryp);  /* the number of the product in the ordertail */							
							while (false !== ($rowp = DB::fetch($queryp))){
							/* if the number of the kind of the product in the orderdetail is just 1  start*/
								if($bcount==1){	 
							?>
									<tr class="grayBg">
										<td width="97" align="center">
											<a href="/product-<?=$rowp ["proid"]?>.html" target="_blank">
												<img src="<?=$rowp ["images"]?>" class="simg"/ >
											</a>
										</td>
										<td>
											<a href="/product-<?=$rowp ["proid"]?>.html" class="blue" target="_blank"><?=$rowp ["proname"]?></a>
											<?php if( ($rowp['nums'] < 1 && $rowp['state']<1) || !isset($rowp['state']) ):?>
												<span class="red" style="font-weight:bold;">(库存不足)</span>
											<?php endif; ?>
										</td>
										
										<td align="center"><?=$rowp ["procount"]?></td>
										<td align="center"><span class=" price">￥<?=cutprice($rowp ["price"])?></span></td>
										<td align="center"  ><?=getorderstate($rowp ["state"])?></td>
										<!-- the process of the tuihuo  start-->
												<td width="108" align="center" >
												<?php 
													$tuihuo_sql="select id from ".DB::table('tuihuo')." where Orderid=".$rowp['id'];
													$tuihuo_result=DB::fetch_first($tuihuo_sql);
													if(empty($tuihuo_result)){
														echo "无退款记录";		
													}else{									
														echo " <a href=\"./tuihuo_process.php?orderid=".$row["orderid"]."&detail_id=".$rowp["id"]."\"  id=\"newbtn\" class=\"W_btn_b m blue\" target=\"_blank\" ><span style='min-width:60px;'>查看退款进程</span></a>";
													}
												?>
												</td>																								
											<!-- the process of the tuihuo  end-->	
										<td align="center" class="btb">
											<?php
											if ($row ["state"]=="0"){
												echo "<p><a  href=\"javascript:void(0);\" id=\"newbtn\" class=\"W_btn_b gotopay\" orderid=\"".$row ["orderid"]."\" ><span>付款</span></a></p>";
											}
											if   ($row ["state"]<1){
												echo "<span class=\"delSpan\">";
												echo "<a href=\"javascript:void(0);\" class=\"blue\" onclick=\"delAddressBox(this);\">删除</a>";
												echo "<div class=\"sureDiv\">";
												echo "<p>确定要删除吗？</p>";
												echo "<a href=\"?act=del&orderid=".$row ["orderid"]."&page=".$page."&s=".$s."\" class=\"W_btn_b\"><span>确认</span></a>";
												echo "<a href=\"javascript:void(0);\" class=\"W_btn_b\" onclick=\"reAddressBox(this);\"><span>取消</span></a>";
												echo "<b></b></div>";
												echo "</span>";
											}
											elseif($row ["state"]<3){
													echo " <a href=\"./detail_tuihuo.php?orderid=".$row["orderid"]."\"  id=\"newbtn\" class=\"W_btn_b m blue\" target=\"_blank\" ><span>申请退款</span></a>";													
												echo " <a href=\"order-".$row ["orderid"].".html\"  id=\"newbtn\" class=\"W_btn_b m blue\"  target=\"_blank\" ><span>查看详细</span></a>";
											}
											else{
												echo " <a href=\"order-".$row ["orderid"].".html\"  id=\"newbtn\" class=\"W_btn_b m blue\"  target=\"_blank\" ><span>查看详细</span></a>";
											}
											?>
										</td>
									</tr>

								<?php
								/* if the number of the kind of the product in the orderdetail is just 1  end*/
								}
								else{
									if($t==1){
								?>
										<tr class="grayBg">
											<td width="97" align="center">
												<a href="/product-<?=$rowp ["proid"]?>.html" target="_blank">
													<img src="<?=$rowp ["images"]?>" class="simg"/ >
												</a>
											</td>
											<td width="302">
												<a href="/product-<?=$rowp ["proid"]?>.html" class="blue" target="_blank"><?=$rowp ["proname"]?></a>
												<?php if( ($rowp['nums'] < 1 && $rowp['state']<1) || !isset($rowp['state']) ):?>
													<span class="red" style="font-weight:bold;">(库存不足)</span>
												<?php endif; ?>	
											</td>
											<td width="95" align="center"><?=$rowp ["procount"]?></td>
											<td width="117" align="center"><span class="price">￥<?=cutprice($rowp ["price"])?></span></td>
											<td width="108" align="center" ><?=getorderstate($rowp ["state"])?></td>
											<!-- the process of the tuihuo  start-->
												<td width="108" align="center" >
												<?php 
													$tuihuo_sql="select id from ".DB::table('tuihuo')." where Orderid=".$rowp['id'];
													$tuihuo_result=DB::fetch_first($tuihuo_sql);
													if(empty($tuihuo_result)){
														echo "无退款记录";		
													}else{									
														echo " <a href=\"./tuihuo_process.php?orderid=".$row["orderid"]."&detail_id=".$rowp["id"]."\"  id=\"newbtn\" class=\"W_btn_b m blue\" target=\"_blank\" ><span style='min-width:60px;'>查看退款进程</span></a>";
													}
												?>
												</td>																								
											<!-- the process of the tuihuo  end-->	
											<td width="97" align="center" class="btb"  rowspan="<?=$bcount?>" >		
												<?php
												if($row ["state"]=="0"){
													echo "<p><a  href=\"javascript:void(0);\" id=\"newbtn\" class=\"W_btn_b gotopay\" orderid=\"".$row ["orderid"]."\" ><span>付款</span></a></p>";
												}
												if($row ["state"]<1){
													echo "<span class=\"delSpan\">";
													echo "<a href=\"javascript:void(0);\" class=\"blue\" onclick=\"delAddressBox(this);\">删除</a>";
													echo "<div class=\"sureDiv\">";
													echo "<p>确定要删除吗？</p>";
													echo "<a href=\"?act=del&orderid=".$row ["orderid"]."&page=".$page."&s=".$s."\" class=\"W_btn_b\"><span>确认</span></a>";
													echo "<a href=\"javascript:void(0);\" class=\"W_btn_b\" onclick=\"reAddressBox(this);\"><span>取消</span></a>";
													echo "<b></b></div>";
													echo "</span>";
												}
												elseif($row ["state"]<3){
													echo " <a href=\"./detail_tuihuo.php?orderid=".$row["orderid"]."\"  id=\"newbtn\" class=\"W_btn_b m blue\" target=\"_blank\" ><span>申请退款</span></a>";
													echo " <a href=\"order-".$row ["orderid"].".html\"  id=\"newbtn\" class=\"W_btn_b m blue\"  target=\"_blank\" ><span>查看详细</span></a>";					
												}
												?>
											</td>											
										</tr>
									<?php 
									}
									else{
									?>
										<tr class='grayBg bgtopline'  >
											<td align="center">
												<a href="/product-<?=$rowp ["proid"]?>.html" target="_blank">
													<img src="<?=$rowp ["images"]?>" width=60 height="60"  class="simg"/>
												</a>
											</td>
											<td>
												<a href="/product-<?=$rowp ["proid"]?>.html" class="blue" target="_blank"><?=$rowp ["proname"]?></a>
												<?php if( ($rowp['nums'] < 1 && $rowp['state']<1) || !isset($rowp['state']) ):?>
													<span class="red" style="font-weight:bold;">(库存不足)</span>
												<?php endif; ?>	
											</td>
											<td  align="center"><?=$rowp["procount"] ?></td>
											<td align="center"><span class="price">￥<?=cutprice($rowp ["price"])?></span></td>
											<td align="center"><?=getorderstate($rowp ["state"])?></td>
											<!-- the process of the tuihuo  start-->
												<td width="108" align="center" >
												<?php 
													$tuihuo_sql="select id from ".DB::table('tuihuo')." where Orderid=".$rowp['id'];
													$tuihuo_result=DB::fetch_first($tuihuo_sql);
													if(empty($tuihuo_result)){
														echo "无退款记录";		
													}else{									
														echo " <a href=\"./tuihuo_process.php?orderid=".$row["orderid"]."&detail_id=".$rowp["id"]."\"  id=\"newbtn\" class=\"W_btn_b m blue\" target=\"_blank\" ><span style='min-width:60px;'>查看退款进程</span></a>";
													}
												?>
												</td>																								
											<!-- the process of the tuihuo  end-->											
										</tr>
						<?php
									}
								}
								$t=$t+1;
							}
							$b=$b+1;
						}
						?>
					</table>
				</div>
			</div>
			<?php echo pagesto( $tpagecount, $page )?>
		</div>
	</div>
</div>

<?php require_once ("../../custom/footer_new.php");?>
<?php include YMY_ROOT_PATH.'custom/script.php'; ?>

<script type="text/javascript" src="../js/ufun.js"></script>
<script type="text/javascript" >
function delAddressBox(o){
	$(o).siblings().show();
}		 
function reAddressBox(o){
	$(o).parent().hide();
}
$(document).ready(function(){
	$('.grayBg').find("td:eq(0)").addClass("nobor"); 
	$('#sel').change(function(){
		var p1=$(this).children('option:selected').val(); 
		window.location.href="?s="+p1;//页面跳转并传参
	});
});
</script> 
</body>
</html>
