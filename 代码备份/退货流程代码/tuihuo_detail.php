<?php
 
require_once ("../sys.inc/config.php");
require_once ("../sys.inc/Wlkgo.cls.php");
 
viewHead ( "订单管理 - 订单" );
checkManagerLevel(2);
// Printjs("if(top == self){location.href = '../index.php';}");
Printjs ( "if(parent.$$('admincpnav')) parent.$$('admincpnav').innerHTML='后台首页&nbsp;&raquo;&nbsp;订单&nbsp;&raquo;&nbsp;订单管理';" );

	
	$act=strtolower(F("act"));
	switch($act)
	{
	    case "update":
				OrderUpdate();
				ShowOrder();
			break;	
		default:
			 ShowOrder();
			break;
	}


 

viewFoot();





	function OrderUpdate()
	{
		 
		$OrderState=F("tuihuo_checked");
		$vOrderid=F("vId");
		$Orderid=F("id");
		switch ($OrderState)
		{
		  case "1":
			DB::query ( "Update  `ymy_tuihuo` Set checked=".$OrderState.",OkSendDate=".time()."    WHERE  `id`=$Orderid" );
			echo("Update  `ymy_tuihuo` Set checked=".$OrderState.",OkSendDate=".time()."    WHERE  `id`=$Orderid" );
			break;	
		  case "2":
			DB::query ( "Update  `ymy_tuihuo` Set checked=".$OrderState.",OkSendDate=".time()."    WHERE  `id`=$Orderid" );
			echo("Update  `ymy_tuihuo` Set checked=".$OrderState.",OkSendDate=".time()."    WHERE  `id`=$Orderid" );
			break;
		  case "3":
			DB::query ( "Update  `ymy_orderdetail` Set State=".$OrderState.",OkReceiveDate=".time()."    WHERE  `id`=$Orderid" );
			
			// 佣金
			$order_sql = 'select id,orderid,username,proid,price,procount from '.DB::table('orderdetail').' where id='.$Orderid;
			$rows = DB::fetch_first($order_sql);
			$commission_sql = 'select id from '.DB::table('mydlogs').' where orderid='.$Orderid.' and state=1';
			$commission_rst = DB::fetch_first($commission_sql);
			
			if(is_array($rows) && !is_array($commission_rst)){
				$user_sql = 'select popid from '.DB::table('users').' where username=\''.$rows['username'].'\'';
				$user = DB::fetch_first($user_sql);
				$popid = intval($user['popid']);
				if($popid > 0){
					$extension_sql = 'select username from '.DB::table('users').' where userid='.$popid;
					$extension = DB::fetch_first($extension_sql);
					if(is_array($extension)){
						$price = floatval($rows['price']);
						$buycount = intval($rows['procount']);
						$product_sql = 'select commission from '.DB::table('product').' where id='.$rows['proid'];
						$product = DB::fetch_first($product_sql);
						$permillage = intval($product['commission']); // 佣金比例 [ 千分比 ] !!!!!!
						$commission = floor(($price*$buycount*$permillage)/10) / 100;
						$title = '推广用户购买商品，子订单号['.$Orderid.']';
						$act = '推广佣金';
						writecommissionlogs($title, $act, $commission, $extension['username'], $rows['username'], $Orderid);
					}
				}
			}
			break;
		  case "4":
			DB::query ( "Update  `ymy_orderdetail` Set State=".$OrderState.",okexitdate=".time()."    WHERE  `id`=$Orderid" );
			
			// 佣金
			$order_sql = 'select id,orderid,username,proid,price,procount from '.DB::table('orderdetail').' where id='.$Orderid;
			$rows = DB::fetch_first($order_sql);
			
			$wlkgo_product_sql = "select * from ".DB::table("product_wlkgo")." where productid=".$rows['proid']."";
			$wlkgo_product = DB::fetch_first($wlkgo_product_sql);
			if(!empty($wlkgo_product)){
				$wlkgo = new Wlkgo($wlkgo_product['wlkgoid']);
				$wlkgo->createTradeCancelXml();
				$wlkgo->submit();
			}
			
			$commission_sql = 'select id from '.DB::table('mydlogs').' where orderid='.$Orderid.' and state=0';
			$commission_rst = DB::fetch_first($commission_sql);
			
			if(is_array($rows) && is_array($commission_rst)){
				$user_sql = 'select popid from '.DB::table('users').' where username=\''.$rows['username'].'\'';
				$user = DB::fetch_first($user_sql);
				$popid = intval($user['popid']);
				if($popid > 0){
					$extension_sql = 'select username from '.DB::table('users').' where userid='.$popid;
					$extension = DB::fetch_first($extension_sql);
					if(is_array($extension)){
						$price = floatval($rows['price']);
						$buycount = intval($rows['procount']);
						$product_sql = 'select commission from '.DB::table('product').' where id='.$rows['proid'];
						$product = DB::fetch_first($product_sql);
						$permillage = intval($product['commission']); // 佣金比例 [ 千分比 ] !!!!!!
						$commission = floor(($price*$buycount*$permillage)/10) / 100;
						$title = '推广用户订单退款，子订单号['.$Orderid.']';
						$act = '推广佣金';
						writecommissionlogs($title, $act, -$commission, $extension['username'], $rows['username'], $Orderid, 0);
					}
				}
			}
			break;
		}
		ShowMsg("订单状态设置成功");
	}
 	
	function ShowOrder()
	{
		$id= intval ( R("id" ) ); 
		$rows = DB::fetch_first ( "SELECT a.*,b.okpaydate,b.closedate,b.receipt,b.phone,b.mobile,b.address,b.networkid FROM " . DB::table ( 'orderdetail' ) . " a left join " . DB::table ( 'order' ) . " b on a.Orderid=b.void WHERE a.id=$id" );
		 
		if ( is_array ( $rows )) {

		echo "<table  class=tb width=\"100%\">" ;
		echo "  <tr class=thead>" ;
		echo "    <th >商品订单管理</th>" ;
		echo "  </tr>" ;
		echo "  <tr><td>" ;
		
		
    
 

 


 
echo "      <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"5\">\n";
echo "        <tr>\n";
echo "          <td><strong>总订单号：</strong><font color=red>".$rows["Orderid"]."</font></td>\n";
echo "        </tr>\n";
echo "        <tr>\n";
echo "          <td><strong>子单号：</strong><font color=red>".$rows["id"]."</font></td>\n";
echo "        </tr>\n";

 
echo "      </table>\n";
 

echo "      <table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"3\">\n";
echo "		<tr>\n";
echo "        <td colspan=\"4\"><strong>订单商品</strong></td>\n";
echo "        </tr>\n";
echo "        <tr>\n";

echo "        <td>商品名称</td>\n";
echo "        <td align=\"center\">订购价格</td>\n";
echo "        <td align=\"center\">订购数量</td>\n";
echo "        </tr>\n";
 
echo "        <tr>\n";

echo "        <td><a href=\"/product-".$rows["proid"].".html\" target=\"_blank\">".$rows["proname"]."";
		if($rows["attributes"]!="")
		{
			echo "[".$rows["attributes"]."]";
		}	
echo "</a></td>\n";
echo "        <td align=\"center\">".$rows["price"]."</td>\n";
echo "        <td align=\"center\">".$rows["procount"]."</td>\n";
echo "        </tr>\n";
 
echo "        </tr>\n";

echo "      </table>\n";
		 

if ($rows["state"]==-1) {

echo "   <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"5\">\n";
echo "        <tr>\n";
echo "          <td width=\"70\" class=\"cat_title3\"><b>订单状态：</b></td>\n";
echo "          <td><font color=red>无效订单</font></td>\n";
echo "        </tr>\n";
echo "      </table>\n";

    

}

echo "      <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"5\">\n";
echo "        <tr>\n";
echo "          <td width=\"70\" class=\"cat_title3\" valign=\"top\"><b>订单流程：</b></td>\n";
echo "          <td>\n";

				
				 echo "[" . MyDate ( 'Y-m-d H:i:s', $rows ["postdate"] ) . "] 提交订单<br>";	  
	
 	 							 	     
				 
				 if ($rows["state"]>0)
				 {
					echo "[" . MyDate ( 'Y-m-d H:i:s', $rows ["okpaydate"] ) ."] 买家付款<br>";
		         }			     
				 
				 if ($rows["state"]>1 )
				 {
					echo "[" . MyDate ( 'Y-m-d H:i:s', $rows ["oksenddate"] ) ."] 商家发货<br>";
				 }
					 
				 if ($rows["state"]==3 )
				 {
					echo "[" . MyDate ( 'Y-m-d H:i:s', $rows ["okreceivedate"] ) ."] 买家已收货，交易成功<br>";
				 }
									 
 
					 
					  if ($rows["state"]=="-1" )
					  {
						 
						echo "[" . MyDate ( 'Y-m-d H:i:s', $rows ["closedate"] ) ."] 交易关闭(关闭原因:未知)";
					  }
							 
		 
          
		  echo "          </td>\n";
		  echo "        </tr>\n";
		  echo "      </table>\n";
		 
		  echo "      <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"5\">\n";
 		  echo "        <tr>\n";
		 
		  echo "          <td align=\"center\">\n";
		  
	 
         
       
          echo "<form name=\"Gforms\" method=\"post\" action=\"tuihuo_detail.php\">\n";
echo "          <input type=\"hidden\" name=\"id\"  value=\"".$rows["id"]."\">\n";

echo "		  <select name=\"orderState\">\n";
echo "		  <option value=\"2\">已发货</option>\n";
echo "          <option value=\"3\">买家收货，交易完成</option>\n";
echo "          <option value=\"4\">已退款</option>\n";
echo "		  </select>\n";

echo "		  <select name=\"tuihuo_checked\">\n";
echo "          <option value=\"0\">未通过审核</option>\n";
echo "		  	<option value=\"1\">通过客服审核</option>\n";
echo "		  	<option value=\"2\">通过财务审核</option>\n";
echo "		  </select>\n";

echo "		  <input type=\"hidden\" name=\"act\" value=\"update\">\n";
echo "		  <input type=\"hidden\" name=\"vId\" value=\"".$rows["Orderid"]."\">\n";
 
echo "		  <input type=\"button\" name=\"bk\" value=\"  设置订单状态  \" onClick=\"javascript:chkOrder(this.form);\">\n";
echo "          </form>\n";
        		
    	 
			 
		      
          
			echo "</td>\n";
		 
			echo "          <td align=\"center\"></td>\n";
			echo "        </tr>\n";
		 
			echo "      </table>\n";
		 
	 
		 
			echo "      <table  width=\"100%\"  border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"1\">\n";
		 
			echo "        <tr>\n";
			echo "          <td > <b><span class=\"boldFontSize14\">　 </span>收货人信息地址</b> </td>\n";
			echo "        </tr>\n";
			echo "      </table>\n";
			echo "      <table  width=\"100%\"  border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"3\">\n";
			echo "        <tr>\n";
			echo "          <td><table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"2\" cellspacing=\"1\">\n";
			echo "              <tr>\n";
			echo "                <td width=\"18%\" align=\"center\" >姓　　名：</td>\n";
			echo "                <td width=\"82%\">　".$rows["receipt"]."<span class=\"fontSize12\">　（会员名：".$rows["UserName"]."）</span></td>\n";
			echo "              </tr>\n";
			echo "              <tr>\n";
			echo "                <td align=\"center\" >电　　话：</td>\n";
			echo "                <td>　".$rows["phone"]."</td>\n";
			echo "              </tr>\n";
			echo "              <tr>\n";
			echo "                <td align=\"center\" >手　　机：</td>\n";
			echo "                <td>　".$rows["mobile"]."</td>\n";
			echo "              </tr>\n";
			echo "              <tr>\n";
			echo "                <td align=\"center\" >收货地址：</td>\n";
			echo "                <td>　".$rows["address"]."</td>\n";
			echo "              </tr>\n";

			echo "              <tr>\n";
			echo "                <td align=\"center\" >提货地址：</td>\n";
			echo "                <td>网点编号：　".$rows["networkid"]."";

			$row = DB::fetch_first ( "SELECT * FROM " . DB::table ( 'network' ) . " WHERE number='".$rows["networkid"]."'" );
				
			if ( is_array ( $row )) {
			 echo $row["title"];
			}
			
			echo "              </td></tr>\n";
 
			echo "          </table></td>\n";
			echo "        </tr>\n";
			echo "      </table>\n";
				
    
		echo "    </td>" ;
		echo "  </tr>" ;
		echo "</table>" ;
		}
	Else
	{
		$Msg="对不起,找不到订单详细记录.";
		FormatMsg($Msg,1);
	 
	}
 
}
?>
<script>
	function chkOrder(theform)
		{
		  if(confirm("您确定设置订单状态"))
		  	{
				theform.submit()
			}
			else
			{
				return false;
			}
		}
</script>
