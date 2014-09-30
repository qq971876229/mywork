<?php
require_once ("../../common/config.php");
require_once YMY_ROOT_PATH.'yunmayi_admin/sys.inc/Wlkgo.cls.php';
require_once ("alipay.config.php");
require_once ("lib/alipay_notify.class.php");
define('YMY_NOW_TIME', time());
// 计算得出通知验证结果
$alipayNotify = new AlipayNotify ( $alipay_config );
$verify_result = $alipayNotify->verifyNotify ();
if ($verify_result) {
	$out_trade_no = $_POST ['out_trade_no'];
	// 支付宝交易号
	$trade_no = $_POST ['trade_no'];
	// 交易状态
	$trade_status = $_POST ['trade_status'];
	
	if ($_POST ['trade_status'] == 'TRADE_FINISHED') {
		
		
		
		// 判断当前订单的状态 ，如果以付款，则不执行下面流程
	$sql="SELECT * FROM " . DB::table ( 'order' ) . " WHERE state=0 and void='" . $out_trade_no . "'" ;		
		$rows = DB::fetch_first ( "SELECT * FROM " . DB::table ( 'order' ) . " WHERE state=0 and void='" . $out_trade_no . "'" );			
		if (is_array ( $rows )) {			
			// 查看当前用户是否为推广来的用户并取得推广用户Id
			$popid = 0;
			$rowp = DB::fetch_first ( "SELECT popid,coupon,integral FROM " . DB::table ( 'users' ) . " WHERE    username='" . $rows ["username"] . "'" );
			if (is_array ( $rowp )) {
				$popid = $rowp ["popid"];
				$pusername = DB::result_first ( "select username from " . DB::table ( 'users' ) . " where  userid=$popid" );
				// 如果用户使用蚂蚁盾或积分购买，但所需不购时，不能支付
				if ($rows ["coupon"] > $rowp ["coupon"] || $rows ["integral"] > $rowp ["integral"]) {
					echo "fail";
					exit ();
				}
			}
			$paydatetime = time ();
			$camount = 0;
			$queryb = DB::query ( "select * from " . DB::table ( 'orderdetail' ) . " where Orderid='" . $rows ["void"] . "' order by id desc" );
			while ( false !== ($row = DB::fetch ( $queryb )) ) {
				$procount = $row ["procount"];
				$proid = $row ["proid"];
				$camount = $camount + (($row ["proprice"] - $row ["cprice"]) * $row ["procount"]);
				db::query ( "Update  `ymy_product` Set nums=nums-$procount,soldnum=soldnum+$procount  WHERE  `id`=$proid" );
				// 更新折扣 信息
				if ($row ["prize"] > 0) {
					db::query ( "update `ymy_prize` set state=1 WHERE username='" . $rows ["username"] . "' and `id` =" . $row ["prize"] . "" );
				}
				
				// 推广会员满10人 http://www.yunmayi.com/product-113546.html 大米 10元   返10蚂蚁盾
				if($row['proid'] == 113546 && $row['procount'] == 1 && $row['activity'] == '推广会员满10送大米'){
					writemydlogs ( "推广", '推广会员满10人，购买指定大米，返10蚂蚁盾:订单号[' . $rows['void'] . ']', 10, $rows["username"] );
				}
				
				// 付款后生成xml,一个产品一个订单
				// 付款后生成xml,一个产品一个订单
				$orderxml = "<orderCode>" . $row ["id"] . "</orderCode>\r\n";
				$orderxml .= "<void>" . $rows ["void"] . "</void>  \r\n";
				$orderxml .= "<orderStatus>1</orderStatus>\r\n";
				$orderxml .= "<userId>" . $rows ["userid"] . "</userId>\r\n";
				$orderxml .= "<consigneeName>" . $rows ["receipt"] . "</consigneeName> \r\n";
				$orderxml .= "<consigneeArea1>" . $rows ["cnprovince"] . "</consigneeArea1> \r\n";
				$orderxml .= "<consigneeArea2>" . $rows ["cncity"] . "</consigneeArea2> \r\n";
				$orderxml .= "<consigneeArea3>" . $rows ["cnarea"] . "</consigneeArea3> \r\n";
				$orderxml .= "<consigneeDetailedAddress>" . $rows ["address"] . "</consigneeDetailedAddress>  \r\n";
				$orderxml .= "<consigneeFixTel>" . $rows ["phone"] . "</consigneeFixTel>  \r\n";
				$orderxml .= "<consigneeTel>" . $rows ["mobile"] . "</consigneeTel>  \r\n";
				$orderxml .= "<consigneeEmail>" . $rows ["receiptemail"] . "</consigneeEmail>  \r\n";
				$orderxml .= "<orderTime>" . MyDate ( 'Y-m-d H:i:s', $paydatetime ) . "</orderTime> \r\n";
				$orderxml .= "<payType>" . $rows ["paytype"] . "</payType> \r\n";
				$orderxml .= "<goodsNo>" . $row ["pronumber"] . "</goodsNo>  \r\n";
				$orderxml .= "<goodsName>" . $row ["proname"] . "";
				if ($row ["attributes"] != "") {
					$orderxml .= "[" . $row ["attributes"] . "]";
				}
				$orderxml .= "</goodsName>  \r\n";
				$orderxml .= "<brand></brand>  \r\n";
				$orderxml .= "<goodsPrice_cb>" . $row ["cprice"] . "</goodsPrice_cb> \r\n";
				$orderxml .= "<goodsPrice_xs>" . $row ["price"] . "</goodsPrice_xs>  \r\n";
				$orderxml .= "<buyNum>" . $row ["procount"] . "</buyNum>  \r\n";
				$orderxml .= "<total>" . $row ["price"] * $row ["procount"] . "</total> \r\n";
				$orderxml .= "<shopNo>" . $rows ["networkid"] . "</shopNo>  \r\n";
				$Filen = "D:/xmlfilecache/" . $row ["id"] . ".xml";
				$DateXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
				$DateXml .= "<root version=\"2.0\">";
				$DateXml .= "<file>";
				$DateXml .= $orderxml;
				$DateXml .= "</file>";
				$DateXml .= "</root>";
				createTextFile ( $DateXml, $Filen );
				
				// wlkgo 订单推送
				$product_sql = 'SELECT * FROM '.DB::table('product').' WHERE id='.$row['proid'];
				$product = DB::fetch_first($product_sql);
				if($product){
				    if($product['postmaster'] == 'wlkgo'){
				        $wlkgo = new Wlkgo($row['id']);
				        $wlkgo->createTradeAddXml();
				        $result = $wlkgo->submit();
				        $result = @ simplexml_load_string($result);
				        $insert_array = array(
				                'wlkgoorderid'=>$row['id'],
				                'state'=>$result->status,
				                'msg'=>$result->msg,
				                'posttime'=>date('Y-m-d H-i-s',time())
				        );
				        DB::insert('post_wlkgo', $insert_array,true);
				    }
				}
			}
			// 自己购买返回积分
			writeilogs ( "购买商品", "商品订单号[" . $rows ["void"] . "]", floor ( $rows ["payamount"] * (sys_payjf / 100) ), $rows ["username"] );
			if ($popid > 0 && $rows ["coupon"] == 0 && $rows ["integral"] == 0) {
				writeilogs ( "推广", "推广用户购买商品[" . $rows ["void"] . "]", floor ( $rows ["payamount"] * (sys_tpayjf / 100) ), $pusername );
				// writemydlogs("推广","推广用户购买商品[".$rows["void"]."]",$rows["payamount"]*(sys_tpaymyd/100),$pusername,$rows["username"]);
			}
			writeilogs ( "商品购买支付", "商品订单号[" . $rows ["void"] . "]", - $rows ["integral"], $rows ["username"] );
			writemydlogs ( "商品购买支付", "商品订单号[" . $rows ["void"] . "]", - $rows ["coupon"], $rows ["username"] );
			// 满就送
			if ($rows ["payamount"] > 0) {
				$mydarr = cutpymyarr ( $rows ["payamount"] );
				if ($mydarr [0] > 0 && $mydarr [1] > 0) {
					writemydlogs ( "购买商品", "满" . $mydarr [0] . "送" . $mydarr [1] . ":订单号[" . $rows ["void"] . "]", $mydarr [1], $rows ["username"] );
				}
			}
			// 更新订单状态
			$feedarr = array (
					'state' => 1,
					'okpaydate' => $paydatetime,
			        'paytype' => '支付宝'
			);
			DB::update ( 'order', $feedarr, array (
					'state' => 0,
					'void' => $out_trade_no 
			) );
			
			// Time Limit Update
			$timelimitdate = date ( 'N-H' );
			list ( $week, $hour ) = explode ( '-', $timelimitdate );
			if (in_array ( $week, array (
					1,
					3,
					5 
			) )) {
				$limit_today = strtotime ( 'today' );
				$limit_tomorrow = strtotime ( 'tomorrow' );
				$timelimit_sql = 'SELECT id,productid,price,nums,limitnum FROM ' . DB::table ( 'timelimit' ) . ' WHERE isdel=0 and checked=1 and starttime=' . $limit_today . ' and endtime=' . $limit_tomorrow . ' and productid=' . $row ['proid'];
				$timelimit = DB::fetch_first ( $timelimit_sql );
				if ($timelimit) {
					$limit_up_sql = 'UPDATE ' . DB::table ( 'timelimit' ) . ' SET nums=nums-' . $row ['procount'] . ' WHERE id=' . $timelimit ['id'];
					DB::query ( $limit_up_sql );
				}
			}
			
			
			
			
			
			// 满就送	满返		满足大概条件就细分
			$white_amount=0;	//白名单中商品总价			
			
			$fullreturn_sql = 'SELECT sum,back FROM '.DB::table('fullreturn').' WHERE isdel=0 AND checked=1 AND (starttime<'.YMY_NOW_TIME.' AND endtime>'.YMY_NOW_TIME.') AND sum<='.$rows['amount'].' order by sum desc';
			
			$fullreturn=DB::fetch_first($fullreturn_sql);
			
			if(!empty($fullreturn)){
				$queryb = DB::query ( "select * from " . DB::table ( 'orderdetail' ) . " where Orderid='" . $rows ["void"] . "' order by id desc" );
				while ( false !== ($row = DB::fetch ( $queryb )) ) {
					$is_white=DB::fetch_first("select * from ".DB::table('fullreturn')." where whitelist like '%".$row['proid']."%'");
					/*订单中产品是否在白名单中，不在去黑名单中查，在的话从总额中减去*/
					if(!empty($is_white)){
												
						$pro_detail=DB::fetch_first("select * from ".DB::table('orderdetail')." where proid='".$row['proid']."'");					
						$white_price=$pro_detail['procount']*$pro_detail['cprice'];
						$rows['amount']-=$white_price;
						$white_amount+=$white_price;					
					}else{
						$is_black=DB::fetch_first("select * from ".DB::table('fullreturn')." where blacklist like '%".$row['proid']."%'");
						if(!empty($is_black)){
							$pro_detail=DB::fetch_first("select * from ".DB::table('orderdetail')." where id='".$row['proid']."'");
							$rows['amount']=$rows['amount']-($pro_detail['procount']*$pro_detail['cprice']);							
						}
					}
					
				}
			}
			
				/*白名单中返回蚂蚁盾*/
			$fullreturn_white='SELECT sum,back FROM '.DB::table('fullreturn').' WHERE isdel=0 AND iswhite=1 AND checked=1 AND (starttime<'.YMY_NOW_TIME.' AND endtime>'.YMY_NOW_TIME.') AND sum<='.$white_amount.' order by sum desc';
			$fullreturn=DB::fetch_first($fullreturn_white);
			file_put_contents('D:/fan.txt', json_encode($fullreturn)."\r\n\r\n");
			if(!empty($fullreturn)){
				writemydlogs ( "购买商品", "满" . $rows['amount'] . "送" . $fullreturn['back'] . ":订单号[" . $rows ["void"] . "]", $fullreturn['back'], $rows["username"] );
				
			}
			
			/*除去黑白名单剩余产品返回蚂蚁盾*/
			$fullreturn_white='SELECT sum,back FROM '.DB::table('fullreturn').' WHERE isdel=0 AND iswhite=0 AND checked=1 AND (starttime<'.YMY_NOW_TIME.' AND endtime>'.YMY_NOW_TIME.') AND sum<='.$rows['amount'].' order by sum desc';
			$fullreturn=DB::fetch_first($fullreturn_white);
			file_put_contents('D:/fan.txt', json_encode($fullreturn)."\r\n\r\n");
			if(!empty($fullreturn)){
				writemydlogs ( "购买商品", "满" . $rows['amount'] . "送" . $fullreturn['back'] . ":订单号[" . $rows ["void"] . "]", $fullreturn['back'], $rows["username"] );			
			
			}	
			
			
		      // 20优惠券
			if($rows['remark'] == '20抵用券'){
    			$vou_data = array(
    			        'used' => 1,
    			        'usedtime' => time()
    			);
    			$condition = array(
    			        'username' => $rows['username']
    			);
    			DB::update('voucher', $vou_data, $condition);
			}
			
			// 更新订单状态
			$feedarr = array (
					'state' => 1 
			);
			DB::update ( 'orderdetail', $feedarr, array (
					'state' => 0,
					'Orderid' => $out_trade_no 
			) );
			// 更新成长值
			$growvalue = ceil ( $rows ['amount'] );
			$grow_sql = 'update ' . DB::table ( 'users' ) . ' set growvalue=growvalue+' . $growvalue . ' where username=\'' . $rows ['username'] . '\'';
			DB::query ( $grow_sql );
		}
		
		// logResult("交易完成 高级".$sqlb);
		
		
		
		
		
		
	} else if ($_POST ['trade_status'] == 'TRADE_SUCCESS') {
		
		
		
		
		
		
		
		
		// 判断当前订单的状态 ，如果以付款，则不执行下面流程
	$sql="SELECT * FROM " . DB::table ( 'order' ) . " WHERE state=0 and void='" . $out_trade_no . "'" ;		
		$rows = DB::fetch_first ( "SELECT * FROM " . DB::table ( 'order' ) . " WHERE state=0 and void='" . $out_trade_no . "'" );			
		if (is_array ( $rows )) {			
			// 查看当前用户是否为推广来的用户并取得推广用户Id
			$popid = 0;
			$rowp = DB::fetch_first ( "SELECT popid,coupon,integral FROM " . DB::table ( 'users' ) . " WHERE    username='" . $rows ["username"] . "'" );
			if (is_array ( $rowp )) {
				$popid = $rowp ["popid"];
				$pusername = DB::result_first ( "select username from " . DB::table ( 'users' ) . " where  userid=$popid" );
				// 如果用户使用蚂蚁盾或积分购买，但所需不购时，不能支付
				if ($rows ["coupon"] > $rowp ["coupon"] || $rows ["integral"] > $rowp ["integral"]) {
					echo "fail";
					exit ();
				}
			}
			$paydatetime = time ();
			$camount = 0;
			$queryb = DB::query ( "select * from " . DB::table ( 'orderdetail' ) . " where Orderid='" . $rows ["void"] . "' order by id desc" );
			while ( false !== ($row = DB::fetch ( $queryb )) ) {
				$procount = $row ["procount"];
				$proid = $row ["proid"];
				$camount = $camount + (($row ["proprice"] - $row ["cprice"]) * $row ["procount"]);
				db::query ( "Update  `ymy_product` Set nums=nums-$procount,soldnum=soldnum+$procount  WHERE  `id`=$proid" );
				// 更新折扣 信息
				if ($row ["prize"] > 0) {
					db::query ( "update `ymy_prize` set state=1 WHERE username='" . $rows ["username"] . "' and `id` =" . $row ["prize"] . "" );
				}
				
				// 推广会员满10人 http://www.yunmayi.com/product-113546.html 大米 10元   返10蚂蚁盾
				if($row['proid'] == 113546 && $row['procount'] == 1 && $row['activity'] == '推广会员满10送大米'){
					writemydlogs ( "推广", '推广会员满10人，购买指定大米，返10蚂蚁盾:订单号[' . $rows['void'] . ']', 10, $rows["username"] );
				}
				
				// 付款后生成xml,一个产品一个订单
				// 付款后生成xml,一个产品一个订单
				$orderxml = "<orderCode>" . $row ["id"] . "</orderCode>\r\n";
				$orderxml .= "<void>" . $rows ["void"] . "</void>  \r\n";
				$orderxml .= "<orderStatus>1</orderStatus>\r\n";
				$orderxml .= "<userId>" . $rows ["userid"] . "</userId>\r\n";
				$orderxml .= "<consigneeName>" . $rows ["receipt"] . "</consigneeName> \r\n";
				$orderxml .= "<consigneeArea1>" . $rows ["cnprovince"] . "</consigneeArea1> \r\n";
				$orderxml .= "<consigneeArea2>" . $rows ["cncity"] . "</consigneeArea2> \r\n";
				$orderxml .= "<consigneeArea3>" . $rows ["cnarea"] . "</consigneeArea3> \r\n";
				$orderxml .= "<consigneeDetailedAddress>" . $rows ["address"] . "</consigneeDetailedAddress>  \r\n";
				$orderxml .= "<consigneeFixTel>" . $rows ["phone"] . "</consigneeFixTel>  \r\n";
				$orderxml .= "<consigneeTel>" . $rows ["mobile"] . "</consigneeTel>  \r\n";
				$orderxml .= "<consigneeEmail>" . $rows ["receiptemail"] . "</consigneeEmail>  \r\n";
				$orderxml .= "<orderTime>" . MyDate ( 'Y-m-d H:i:s', $paydatetime ) . "</orderTime> \r\n";
				$orderxml .= "<payType>" . $rows ["paytype"] . "</payType> \r\n";
				$orderxml .= "<goodsNo>" . $row ["pronumber"] . "</goodsNo>  \r\n";
				$orderxml .= "<goodsName>" . $row ["proname"] . "";
				if ($row ["attributes"] != "") {
					$orderxml .= "[" . $row ["attributes"] . "]";
				}
				$orderxml .= "</goodsName>  \r\n";
				$orderxml .= "<brand></brand>  \r\n";
				$orderxml .= "<goodsPrice_cb>" . $row ["cprice"] . "</goodsPrice_cb> \r\n";
				$orderxml .= "<goodsPrice_xs>" . $row ["price"] . "</goodsPrice_xs>  \r\n";
				$orderxml .= "<buyNum>" . $row ["procount"] . "</buyNum>  \r\n";
				$orderxml .= "<total>" . $row ["price"] * $row ["procount"] . "</total> \r\n";
				$orderxml .= "<shopNo>" . $rows ["networkid"] . "</shopNo>  \r\n";
				$Filen = "D:/xmlfilecache/" . $row ["id"] . ".xml";
				$DateXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
				$DateXml .= "<root version=\"2.0\">";
				$DateXml .= "<file>";
				$DateXml .= $orderxml;
				$DateXml .= "</file>";
				$DateXml .= "</root>";
				createTextFile ( $DateXml, $Filen );
				
				// wlkgo 订单推送
				$product_sql = 'SELECT * FROM '.DB::table('product').' WHERE id='.$row['proid'];
				$product = DB::fetch_first($product_sql);
				if($product){
				    if($product['postmaster'] == 'wlkgo'){
				        $wlkgo = new Wlkgo($row['id']);
				        $wlkgo->createTradeAddXml();
				        $result = $wlkgo->submit();
				        $result = @ simplexml_load_string($result);
				        $insert_array = array(
				                'wlkgoorderid'=>$row['id'],
				                'state'=>$result->status,
				                'msg'=>$result->msg,
				                'posttime'=>date('Y-m-d H-i-s',time())
				        );
				        DB::insert('post_wlkgo', $insert_array,true);
				    }
				}
			}
			// 自己购买返回积分
			writeilogs ( "购买商品", "商品订单号[" . $rows ["void"] . "]", floor ( $rows ["payamount"] * (sys_payjf / 100) ), $rows ["username"] );
			if ($popid > 0 && $rows ["coupon"] == 0 && $rows ["integral"] == 0) {
				writeilogs ( "推广", "推广用户购买商品[" . $rows ["void"] . "]", floor ( $rows ["payamount"] * (sys_tpayjf / 100) ), $pusername );
				// writemydlogs("推广","推广用户购买商品[".$rows["void"]."]",$rows["payamount"]*(sys_tpaymyd/100),$pusername,$rows["username"]);
			}
			writeilogs ( "商品购买支付", "商品订单号[" . $rows ["void"] . "]", - $rows ["integral"], $rows ["username"] );
			writemydlogs ( "商品购买支付", "商品订单号[" . $rows ["void"] . "]", - $rows ["coupon"], $rows ["username"] );
			// 满就送
			if ($rows ["payamount"] > 0) {
				$mydarr = cutpymyarr ( $rows ["payamount"] );
				if ($mydarr [0] > 0 && $mydarr [1] > 0) {
					writemydlogs ( "购买商品", "满" . $mydarr [0] . "送" . $mydarr [1] . ":订单号[" . $rows ["void"] . "]", $mydarr [1], $rows ["username"] );
				}
			}
			// 更新订单状态
			$feedarr = array (
					'state' => 1,
					'okpaydate' => $paydatetime,
			        'paytype' => '支付宝'
			);
			DB::update ( 'order', $feedarr, array (
					'state' => 0,
					'void' => $out_trade_no 
			) );
			
			// Time Limit Update
			$timelimitdate = date ( 'N-H' );
			list ( $week, $hour ) = explode ( '-', $timelimitdate );
			if (in_array ( $week, array (
					1,
					3,
					5 
			) )) {
				$limit_today = strtotime ( 'today' );
				$limit_tomorrow = strtotime ( 'tomorrow' );
				$timelimit_sql = 'SELECT id,productid,price,nums,limitnum FROM ' . DB::table ( 'timelimit' ) . ' WHERE isdel=0 and checked=1 and starttime=' . $limit_today . ' and endtime=' . $limit_tomorrow . ' and productid=' . $row ['proid'];
				$timelimit = DB::fetch_first ( $timelimit_sql );
				if ($timelimit) {
					$limit_up_sql = 'UPDATE ' . DB::table ( 'timelimit' ) . ' SET nums=nums-' . $row ['procount'] . ' WHERE id=' . $timelimit ['id'];
					DB::query ( $limit_up_sql );
				}
			}
			
			
			
			
			
			// 满就送	满返		满足大概条件就细分
			$white_amount=0;	//白名单中商品总价			
			
			$fullreturn_sql = 'SELECT sum,back FROM '.DB::table('fullreturn').' WHERE isdel=0 AND checked=1 AND (starttime<'.YMY_NOW_TIME.' AND endtime>'.YMY_NOW_TIME.') AND sum<='.$rows['amount'].' order by sum desc';
			
			$fullreturn=DB::fetch_first($fullreturn_sql);
			
			if(!empty($fullreturn)){
				$queryb = DB::query ( "select * from " . DB::table ( 'orderdetail' ) . " where Orderid='" . $rows ["void"] . "' order by id desc" );
				while ( false !== ($row = DB::fetch ( $queryb )) ) {
					$is_white=DB::fetch_first("select * from ".DB::table('fullreturn')." where whitelist like '%".$row['proid']."%'");
					/*订单中产品是否在白名单中，不在去黑名单中查，在的话从总额中减去*/
					if(!empty($is_white)){
												
						$pro_detail=DB::fetch_first("select * from ".DB::table('orderdetail')." where proid='".$row['proid']."'");					
						$white_price=$pro_detail['procount']*$pro_detail['cprice'];
						$rows['amount']-=$white_price;
						$white_amount+=$white_price;					
					}else{
						$is_black=DB::fetch_first("select * from ".DB::table('fullreturn')." where blacklist like '%".$row['proid']."%'");
						if(!empty($is_black)){
							$pro_detail=DB::fetch_first("select * from ".DB::table('orderdetail')." where id='".$row['proid']."'");
							$rows['amount']=$rows['amount']-($pro_detail['procount']*$pro_detail['cprice']);							
						}
					}
					
				}
			}
			
			/*白名单中返回蚂蚁盾*/
			$fullreturn_white='SELECT sum,back FROM '.DB::table('fullreturn').' WHERE isdel=0 AND iswhite=1 AND checked=1 AND (starttime<'.YMY_NOW_TIME.' AND endtime>'.YMY_NOW_TIME.') AND sum<='.$white_amount.' order by sum desc';
			$fullreturn=DB::fetch_first($fullreturn_white);
			file_put_contents('D:/fan.txt', json_encode($fullreturn)."\r\n\r\n");
			if(!empty($fullreturn)){
				writemydlogs ( "购买商品", "满" . $rows['amount'] . "送" . $fullreturn['back'] . ":订单号[" . $rows ["void"] . "]", $fullreturn['back'], $rows["username"] );
				
			}
			
			/*除去黑白名单剩余产品返回蚂蚁盾*/
			$fullreturn_white='SELECT sum,back FROM '.DB::table('fullreturn').' WHERE isdel=0 AND iswhite=0 AND checked=1 AND (starttime<'.YMY_NOW_TIME.' AND endtime>'.YMY_NOW_TIME.') AND sum<='.$rows['amount'].' order by sum desc';
			$fullreturn=DB::fetch_first($fullreturn_white);
			file_put_contents('D:/fan.txt', json_encode($fullreturn)."\r\n\r\n");
			if(!empty($fullreturn)){
				writemydlogs ( "购买商品", "满" . $rows['amount'] . "送" . $fullreturn['back'] . ":订单号[" . $rows ["void"] . "]", $fullreturn['back'], $rows["username"] );			
			
			}
		
			
			
			
		      // 20优惠券
			if($rows['remark'] == '20抵用券'){
    			$vou_data = array(
    			        'used' => 1,
    			        'usedtime' => time()
    			);
    			$condition = array(
    			        'username' => $rows['username']
    			);
    			DB::update('voucher', $vou_data, $condition);
			}
			
			// 更新订单状态
			$feedarr = array (
					'state' => 1 
			);
			DB::update ( 'orderdetail', $feedarr, array (
					'state' => 0,
					'Orderid' => $out_trade_no 
			) );
			// 更新成长值
			$growvalue = ceil ( $rows ['amount'] );
			$grow_sql = 'update ' . DB::table ( 'users' ) . ' set growvalue=growvalue+' . $growvalue . ' where username=\'' . $rows ['username'] . '\'';
			DB::query ( $grow_sql );
		}
		// logResult("交易完成 高1级".$sqlb);
		
		
		
		
		
		
		
		
		
		
		
	
	}
	echo "success"; // 请不要修改或删除
		                // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} else {
	// 验证失败
	echo "fail";
	// 调试用，写文本函数记录程序运行情况是否正常
	logResult ( $out_trade_no );
}
function creatorderxml($proid, $orderid) {
	global $ymy_UserId;
	$rows = DB::fetch_first ( "SELECT * FROM " . DB::table ( 'Product' ) . " WHERE id=$proid" );
	if (is_array ( $rows )) {
		$orderxml .= "<orderCode>" . $orderid . "</orderCode>\r\n";
		$orderxml .= "<orderStatus>1</orderStatus>\r\n";
		$orderxml .= "<userId>" . $rows ["userid"] . "</userId>\r\n";
		$orderxml .= "<consigneeName>" . $rows ["receipt"] . "</consigneeName> \r\n";
		$orderxml .= "<consigneeArea1>" . $rows ["cnprovince"] . "</consigneeArea1> \r\n";
		$orderxml .= "<consigneeArea2>" . $rows ["cncity"] . "</consigneeArea2> \r\n";
		$orderxml .= "<consigneeArea3>" . $rows ["cnarea"] . "</consigneeArea3> \r\n";
		$orderxml .= "<consigneeDetailedAddress>" . $rows ["address"] . "</consigneeDetailedAddress>  \r\n";
		$orderxml .= "<consigneeFixTel>" . $rows ["phone"] . "</consigneeFixTel>  \r\n";
		$orderxml .= "<consigneeTel>" . $rows ["mobile"] . "</consigneeTel>  \r\n";
		$orderxml .= "<consigneeEmail>" . $rows ["receiptemail"] . "</consigneeEmail>  \r\n";
		$orderxml .= "<orderTime>" . MyDate ( 'Y-m-d H:i:s', $rows ["okpaydate"] ) . "</orderTime> \r\n";
		$orderxml .= "<payType>" . $rows ["paytype"] . "</payType> \r\n";
		$orderxml .= "<goodsNo>" . $rows ["pronumber"] . "</goodsNo>  \r\n";
		$orderxml .= "<goodsName>" . $rows ["proname"] . "</goodsName>  \r\n";
		$orderxml .= "<brand></brand>  \r\n";
		$orderxml .= "<goodsPrice_cb>" . $rows ["cprice"] . "</goodsPrice_cb> \r\n";
		$orderxml .= "<goodsPrice_xs>" . $rows ["proprice"] . "</goodsPrice_xs>  \r\n";
		$orderxml .= "<buyNum>" . $rows ["procount"] . "</buyNum>  \r\n";
		$orderxml .= "<total>" . $rows ["amount"] . "</total> \r\n";
		$orderxml .= "<shopNo>" . $rows ["networkid"] . "</shopNo>  \r\n";
		$Filen = "D:/xmlfilecache/" . $orderid . ".xml";
		$DateXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
		$DateXml .= "<root version=\"2.0\">";
		$DateXml .= "<file>";
		$DateXml .= $orderxml;
		$DateXml .= "</file>";
		$DateXml .= "</root>";
		createTextFile ( $DateXml, $Filen );
	}
}
?>