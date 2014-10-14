<?php
/**
 * 支付
 * 日期：2014-08-09
 * @author Smile(陈文章)
 */
require '../common/config.php';
define('YMY_NOW_TIME', time());
define('IN_PAY_SMILE', 'dsisjkrghieriuskgjdkbgiuhasudig');


if (empty($ymy_UserName) || !isset($ymy_UserName)) {exit();}

if (isOutSubmit ()) {
	echo '非法外部提交被禁止';
	exit;
}

$paytype = I(F('paytype'));
$paytypeword = '';
$source = '';
switch($paytype){
	case 'alipay': // 支付宝
		echo '正在跳转至支付宝支付页面，请耐心等待.....<br />';
		$paytypeword = '支付宝';
		$source = YMY_ROOT_PATH.'pay/alipay/pay.php';
		break;
	case 'chinapay': // 银联
		echo '<title>正在跳转至银联在线支付页面</title>';
		echo '正在跳转至网银在线支付页面，请耐心等待.....';
		$paytypeword = '网银在线';
		$source = YMY_ROOT_PATH.'pay/bankpay/pay.php';
		break;
	case 'mayipay':
		$paytypeword = '蚂蚁盾';
		$source = YMY_ROOT_PATH.'pay/mayipay/pay.php';
		break;
}

if(empty($source) || !is_file($source)){
	echo '错误的支付方式';
	exit;
}

$orderid = intval(F('orderid'));

if(empty($orderid)){
	// 购物车直接跳转
	$buylist = getcookie("shopcard");
	$payfrom = 'shopcart';
}
else{
	$payfrom = 'order';
}

$Receiptid = F("Receiptid");
$networkid = F("networkid");
$amount = F("totalprice");
$amount = 0;
$voucher = I(F('voucher')); // 满50 使用20抵用卷

$getuip = getIp();
$userip = trim($getuip);
$ismyd = intval(F("ismyd"));
$myd = floatval(F("myd"));
$isjif = intval(F("isjif"));
$jif = intval(F("jif"));
$orderxml = "";
$payamount = 0;
$total = 0;
$okpaydate = 0;
$orderstate = 0;


if($ismyd == 0){ $myd = 0; }
if($isjif == 0){ $jif = 0; }
$monjf = cutprice( ($jif * sys_monjf) / 100 );

$product_empty_list = array();

if(!empty($voucher) && ($myd > 0 || $monjf > 0)){
	echo '错误信息：使用了抵用券，就无法再使用蚂蚁盾或积分';
	exit;
}


if($payfrom == 'shopcart'){
	$realbuystr = trim(getcookie('buylist'));
	$buylistrule = '/^([0-9]+,[0-9]+[\.]{0,1})+$/';
	if(!preg_match($buylistrule, $realbuystr)){
		header("Location: /cart/buytlist.html");
		exit;
	}
	

	$address_sql = 'select * from '.DB::table('myaddress').' where id='.$Receiptid;
	$address = DB::fetch_first($address_sql);
	
	if(!is_array($address)){
		echo "参数错误:err106";
		exit;
	}
	
	$templist = explode('.', $realbuystr);
	$realbuylist = array();
	$productids = array();
	foreach($templist as $key=>$value){
	    $_temp = explode(',', $value);
		$realbuylist[] = $_temp;
		$productids[] = $_temp[0];
	}
	
	$product_list = explode('$$$', $buylist);
	$new_shopcard = '';
	
	// Seckill
	/*$seckilllist = array();
	$killproductids = array();
	$seckill_point = date('H');
	$haskillorder = false;
	$haskillproduct = false;
	if(in_array($seckill_point, array(10,12,14,16))){
	    $seckill_today = strtotime('today');
	    $seckill_tomorrow = strtotime('tomorrow');
	    $kill_sql = 'SELECT id,productid,price,limitnum FROM '.DB::table('seckill').' WHERE isdel=0 AND checked=1 AND starttime='.$seckill_today;
	    $kill_rst = DB::query($kill_sql);
	    
	    if($kill_rst){
	        while(false !== ($kill = DB::fetch($kill_rst))){
	            $seckilllist[$kill['productid']] = $kill;
	            $killproductids[] = $kill['productid'];
	        }
	        if(!empty($killproductids)){
	        	$kill_order_limit_sql = 'SELECT id FROM '.DB::table('orderdetail').' WHERE username=\''.$ymy_UserName.'\' AND proid in('.implode(',', $killproductids).') AND ((okpaydate BETWEEN '.$seckill_today.' AND '.$seckill_tomorrow.') OR (postdate BETWEEN '.$seckill_today.' AND '.$seckill_tomorrow.')) AND (state BETWEEN 0 AND 3)';
	        	// $kill_order_limit_sql = 'SELECT id FROM '.DB::table('orderdetail').' WHERE username=\''.$ymy_UserName.'\' AND proid in('.implode(',', $killproductids).') AND ((okpaydate BETWEEN 1407772800 AND 1408118400) OR (postdate BETWEEN 1407772800 AND 1408118400)) AND (state BETWEEN 0 AND 3)';
	            $kill_order_count = DB::getCount($kill_order_limit_sql);
	            if($kill_order_count > 0){
	                $haskillorder = true;
	            }
	        }
	    }
	}*/
	// Seckill V2
	$seckill_v2_sql = 'SELECT * FROM '.DB::table('seckill_v2').' WHERE isdel=0 AND starttime<'.YMY_NOW_TIME.' AND endtime>'.YMY_NOW_TIME.' AND productid in ('.implode(',', $productids).')';
	$seckill_v2_rst = DB::query($seckill_v2_sql);
	$seckill_v2_list = array();
	if($seckill_v2_rst){
	    while(false !== ($seckill_v2 = DB::fetch($seckill_v2_rst))){
	        $seckill_v2['setting'] = unserialize($seckill_v2['setting']);
	        $_sc = count($seckill_v2['setting']);
	        $_st = $seckill_v2['starttime'];
	        $_et = $seckill_v2['endtime'];
	        $_pi = $seckill_v2['productid'];
	        // 秒杀订单量
	        $_os = 'SELECT id FROM '.DB::table('orderdetail').' WHERE state<>4 AND postdate>\''.$_st.'\' AND postdate<\''.$_et.'\' AND proid='.$_pi.' AND activity=\'限时秒杀\'';
	        if($ymy_UserName!=""){
	        	$_os.="and UserName='".$ymy_UserName."'";
	        }
	        $_oc = DB::getCount($_os);
	        
	        if($_oc < $_sc){
	            $seckill_v2['valid'] = true;
	            $seckill_v2['price'] = $seckill_v2['setting'][$_oc];
	        }
	        else{
	            $seckill_v2['valid'] = false;
	        }
	        $seckill_v2_list[$_pi] = $seckill_v2;
	    }
	}
	
	$productids = array();
	$isIndexTimeLimit = false;
	foreach($product_list as $key=>$value){
		$pro_info = explode('@@@', $value);
		$product_id = intval($pro_info[0]);
		$pro_count = intval($pro_info[1]);
		$pro_attr = intval($pro_info[2]);
		$prize = intval($pro_info[3]);
		$pro_attrs = '';
		$activity = '';
		
		if(!in_array(array($product_id, $pro_attr), $realbuylist)){
			$new_shopcard = empty($new_shopcard) ? $value : '$$$'.$value;
			continue;
		}
	
		if($pro_count < 1){
			continue;
		}
	
		$pro_sql = "SELECT id,price,title,number,cprice,attribute,nums FROM " . DB::table ( 'product' ) . " WHERE checked=1 and state=1 and id={$product_id}";
		$product = DB::fetch_first($pro_sql);
		if(is_array($product)){
			if($product['attribute'] > 0 && $pro_attr > 1){
				$pro_attrs = getspecitemSign($pro_attr);
				$product_attr = DB::fetch_first ( "SELECT price FROM " . DB::table ( 'product_attribute' ) . " WHERE attributeid=$pro_attr  and   productid=$product_id" );
				$product['price'] = $product_attr['price'];
			}
			
			if($product['nums'] < 1 || $pro_count > $product['nums']){
				$product_empty_list[] = array('title'=>$product['title']);
			}
				
			// Tuan
			$tuan_sql = 'select id,price,discount,tprice from '.DB::table('tuan').' where productid='.$product['id'].' and (starttime<'.YMY_NOW_TIME.' and endtime>'.YMY_NOW_TIME.')';
			$tuan = DB::fetch_first($tuan_sql);
	
			if($tuan){
				$tuan['price'] = $product['price'];
				$product['price'] = $tuan['tprice'];
				$activity = '团购';
			}
			
			
			
			// Yunmayi Zan
			$yunmayizan_sql = 'SELECT productid,zan,settings FROM '.DB::table('zan').' WHERE isdel=0 and checked=1 and productid='.$product['id'].' and starttime<'.YMY_NOW_TIME.' and endtime>'.YMY_NOW_TIME;
			$yunmayizan = DB::fetch_first($yunmayizan_sql);
			
			if($yunmayizan){
				$zan_settings = unserialize($yunmayizan['settings']);
				$zan_tmp = array();
				foreach($zan_settings as $k=>$v){
					$zan_tmp[$v['zannum']] = $v['discount'];
				}
				ksort($zan_tmp);
				
				$zan_discount = 100;
				foreach($zan_tmp as $key=>$vo){
					if($key < $yunmayizan['zan']){
						$zan_discount = $vo;
					}
				}
				$product['price'] = intval($product['price'] * $zan_discount) / 100;
				$activity = '蚂蚁赞';
			}
			
			// Time Limit
			$timelimitdate = date('N-H');
			list($week, $hour) = explode('-', $timelimitdate);
			if(in_array($week, array(1,3,5))){
				$limit_today = strtotime('today');
				$limit_tomorrow = strtotime('tomorrow');
				$timelimit_sql = 'SELECT productid,price,nums,limitnum FROM '.DB::table('timelimit').' WHERE isdel=0 and checked=1 and starttime='.$limit_today.' and endtime='.$limit_tomorrow.' and productid='.$product_id;
				$timelimit = DB::fetch_first($timelimit_sql);
				if($timelimit){
					if($timelimit['limitnum'] < $pro_count){
						echo '[聚实惠抢购]'.$product['title'].'，每日限购'.$timelimit['limitnum'].'件';
						exit;
					}
					
					$orderCountSql = 'Select * From '.DB::table('orderdetail').' where proid='.$product_id.' and activity=\'聚实惠抢购\'';
					$orderCount = DB::getCount($orderCountSql);
					
					if($orderCount >= $timelimit['limitnum']){
					    echo '[聚实惠抢购]'.$product['title'].'，每日限购'.$timelimit['limitnum'].'件';
					    exit;
					}
					
					if($timelimit['nums'] < $pro_count){
						echo '[聚实惠抢购]'.$product['title'].'，已被抢购完啦';
						exit;
					}
					
					
					
					$product['price'] = $timelimit['price'];
					$activity = '聚实惠抢购';
				}
			}
			
			
			//index time limit
			$index_timelimit_sql = "select productid,price,nums,limitnum from ".DB::table("timelimit")." where isdel=0 and checked=1 and point=0 and productid=".$product['id']." and (starttime<".YMY_NOW_TIME." and endtime>".YMY_NOW_TIME.")";
			$index_timelimit = DB::fetch_first($index_timelimit_sql);
			
			$usersql = "select * from ".DB::table("users")." where username='".$ymy_UserName."'";
			$user_query = DB::fetch_first($usersql);
			
			
			
			if($index_timelimit){
				
				if($user_query['mobile_verify']==0){
					echo '您当前账号没有绑定手机号，不能参与该活动，请先绑定手机号再购买';
					exit;
				}
				
				if($isIndexTimeLimit){
					echo '该活动所有商品限购一件';
					exit;
				}
				$isIndexTimeLimit = true;
				
				if($index_timelimit['limitnum'] < $pro_count){
					echo '[整点限时抢购]'.$product['title'].'，限购'.$index_timelimit['limitnum'].'件';
					exit;
				}
				
				$orderCountSql = 'Select * From '.DB::table('orderdetail').' where state<>0 and UserName= \''.$ymy_UserName.'\' and activity=\'整点限时抢购\'';
				$orderCount = DB::getCount($orderCountSql);
			
					
				if($orderCount >= $index_timelimit['limitnum']){
					echo '[整点限时抢购]'.$product['title'].'，限购'.$index_timelimit['limitnum'].'件';
					exit;
				}
					
				if($index_timelimit['nums'] < $pro_count){
					echo '[整点限时抢购]'.$product['title'].'，已被抢购完啦';
					exit;
				}
				
				$product['price'] = $index_timelimit['price'];
				$activity = '整点限时抢购';
			}
			
			
			// Seckill
			/*if(isset($seckilllist[$product_id])){
			    if($haskillorder){
			        echo '付款失败，错误信息：今日已购买过秒杀商品或已有秒杀商品的订单未付款，无法再次购买';
			        exit;
			    }
			    
			    if($haskillproduct){
			        echo '付款失败，错误信息：每日限购一件秒杀商品，无法同时购买多个秒杀商品';
			        exit;
			    }
			    $haskillproduct = true;
			    $seckill = $seckilllist[$product_id];
			    
			    if($seckill['limitnum'] < $pro_count){
					echo '付款失败，错误信息：[限时秒杀]'.$product['title'].'，限购'.$seckill['limitnum'].'件';
					exit;
				}
				
			    $product['price'] = $seckill['price'];
			    $activity = '限时秒杀';
			}*/
			
			// Seckill V2
			if(isset($seckill_v2_list[$product['id']]) && $pro_count == 1){
			    $seckill_v2 = $seckill_v2_list[$product['id']];
			    if($seckill_v2['valid']){
			        $product['price'] = $seckill_v2['price'];
			        $activity = '限时秒杀';
			    }
			}
			
			
			// 推广会员满10人 http://www.yunmayi.com/product-113546.html 大米 10元   返10蚂蚁盾
			if($product_id == 113546 && $pro_count == 1){
				$popuser_sql = 'SELECT count(userid) FROM '.DB::table('users').' WHERE popid='.$ymy_UserId;
				$popuser_count = DB::getCount($popuser_sql);
				if($ymy_UserName == 'chenwenzhang'){
					$popuser_count = 11;
				}
				if($popuser_count >= 10){
					$orderdetail_sql = 'SELECT id FROM '.DB::table('orderdetail').' WHERE activity=\'推广会员满10送大米\' and UserName=\''.$ymy_UserName.'\'';
					$orderdetail = DB::fetch_first($orderdetail_sql);
					if(!$orderdetail){
						$product['price'] = 10;
						$activity = '推广会员满10送大米';
					}
				}
			}
			
			// 计算价格
			$total = $product['price'] * $pro_count;
			$payamount += $total;
				
			if(!$tuan){
				$tuan = array('id'=>0, 'price'=>0, 'discount'=>0);
			}
		}
		else{
			echo "参数错误:err101";
			exit;
		}
	
		$order_detail_feeds[] = array(
				'Orderid' => '',
				'UserName' => $ymy_UserName,
				'price' => $product['price'],// 如果商品处于团购，价格为团购价
				'cprice' => $product['cprice'],
				'proid' => $product_id,
				'proname'=>$product['title'],
				'state' => '0',
				'procount' => $pro_count,
				'pronumber' => $product['number'],
				'attribute' => $pro_attr,
				'postdate'=>YMY_NOW_TIME,
				'prize'=>$prize,
				'attributes' => $pro_attrs,
				'tuanid' => $tuan['id'],
				'tuanprice' => $tuan['price'],
				'tuandiscount' => $tuan['discount'],
				'activity' => $activity
		);
		$total = 0;
	}
	
	if(isset($_POST['huan']) && is_array($_POST['huan'])){
		$activity = '满换购';
		$huanids = $_POST['huan'];
		$huanids = array_unique($huanids);
		foreach($huanids as $k=>$v){
			$v = intval($v);
			if($v > 0)
				$huanids[$k] = $v; 
		}
		$huan_sql = 'SELECT productid,price FROM '.DB::table('huangou').' WHERE id in ('.implode(',', $huanids).')';
		$huan_rst = DB::query($huan_sql);
		$huans = array();
		$productids = array();
		$products = array();
		if($huan_rst){
			while(false !== ($huan = DB::fetch($huan_rst))){
				$huans[] = $huan;
				$productids[] = $huan['productid'];
			}
		}
		
		if(!empty($productids)){
			$product_sql = 'SELECT id,price,title,number,cprice,attribute,nums FROM '.DB::table('product').' WHERE isdel=0 and checked=1 and state=1 and id in ('.implode(',', $productids).')';
			$product_rst = DB::query($product_sql);
			if($product_rst){
				while(false !== ($product = DB::fetch($product_rst))){
					$products[$product['id']] = $product;
				}
			}
		}
		
		if(!empty($huans)){
			foreach($huans as $k=>$huan){
				$payamount += $huan['price'];
				$product = $products[$huan['productid']];
				$order_detail_feeds[] = array(
						'Orderid' => '',
						'UserName' => $ymy_UserName,
						'price' => $huan['price'],// 如果商品处于团购，价格为团购价
						'cprice' => $product['cprice'],
						'proid' => $product['id'],
						'proname'=> $product['title'],
						'state' => '0',
						'procount' => 1,
						'pronumber' => $product['number'],
						'attribute' => $pro_attr,
						'postdate'=>YMY_NOW_TIME,
						'prize'=>0,
						'attributes' => '',
						'activity' => $activity
				);
			}
		}
		
	}
	$remark = '';
	// 满50  使用20抵用卷
	if(!empty($voucher) && $payamount >= 50){
		$code = $voucher;
		$voucher_sql = 'SELECT username FROM '.DB::table('voucher').' WHERE code=\''.$code.'\' AND used=0 AND activate=1';
		$voucher = DB::fetch_first($voucher_sql);
		if($ymy_UserName == $voucher['username']){
		    /*
			$data = array(
					'used' => 1,
					'usedtime' => YMY_NOW_TIME
			);
			$condition = array(
					'code' => $code
			);
			$vrst = DB::update('voucher', $data, $condition);
			*/
		    $payamount -= 20;
		    $remark = '20抵用券';
		}
	}
	
	$amount = $payamount;
	$void = getsignorder_no(build_order_no());
	
	if(empty($void)){
		echo "参数错误:err102";
		exit;
	}
	
	if($ismyd == 1 || $isjif == 1){
		//取得当前会员的真实蚂蚁盾
		$credit = DB::fetch_first ( "SELECT coupon,integral FROM " . DB::table ( 'users' ) . " WHERE username='".$ymy_UserName."'" );
		if(!is_array($credit)){
			echo "参数错误:err103";
			exit;
		}
		else{
			if($ismyd == 1){
				if($myd > $credit['coupon']){
					echo "参数错误:err104";
					exit;
				}
				if($myd > $payamount){
					$myd = $payamount;
					$monjf = 0;
					$jif = 0;
				}
				$payamount = $payamount - $myd;
			}
				
			if($isjif == 1){
				if($jif > $credit['integral']){
					echo "参数错误:err105";
					exit;
				}
				if($monjf > $payamount){
					$monjf = $payamount;
					$jif = ($monjf * 100) / sys_monjf;
				}
				$payamount = $payamount - $monjf;
			}
		}
	}
	if($payamount < 0 && $paytypeword != 'mayipay'){
		echo '参数错误:err106';
		exit;
	}
	
	if($paytype == 'mayipay'){
		$okpaydate = YMY_NOW_TIME;
		$orderstate = 1;
	}
	
	
	
	$order_feed = array(
			'username' => $ymy_UserName,
			'userid' => $ymy_UserId,
			'state' => $orderstate,
			'receipt' => $address['recename'],
			'address' => $address['address'],
			'amount' => $amount,
			'payamount' => $payamount,
			'receiptemail' => $address['email'],
			'phone' => $address['phone'],
			'mobile' => $address['mobile'],
			'userip' => $userip,
			'actiondate' => YMY_NOW_TIME,
			'postdate' => YMY_NOW_TIME,
			'okpaydate' => $okpaydate,
			'networkid' => $networkid,
			'express' => '',
			'paytype' => $paytypeword,
			'void' => $void,
			'sex'=>'',
			'post'=>'',
			'remark'=>$remark,
			'cnprovince' => $address['cnprovince'],
			'cncity' => $address['cncity'],
			'cnarea' => $address['cnarea'],
			'coupon'=>$myd,
			'integral'=>$jif
	);
	// 订单入库
	$rst = DB::insert('order', $order_feed);
	if($rst > 0){
		DB::query('BEGIN');
		foreach($order_detail_feeds as $key=>$value){
			$order_detail_feeds[$key]['Orderid'] = $value['Orderid'] = $void;
			$order_detail_feeds[$key]['state'] = $value['state'] = $orderstate;
			$order_detail_feeds[$key]['okpaydate'] = $value['okpaydate'] = $okpaydate;
			$detail_rst = DB::insert('orderdetail', $value, true);
			if(!$detail_rst){
				DB::query('ROLLBACK');
				// 子订单入库错误删除order字段
				$delete_order_sql = 'DELETE FROM '.DB::table('order').' where orderid='.$rst;
				DB::query($delete_order_sql);
				echo '订单提交失败，请返回后重新提交';
				exit;
			}
			
			$order_detail_feeds[$key]['id'] = $detail_rst;
		}
		DB::query('COMMIT');
	}
	else{
		echo '订单提交失败，请返回后重新提交';
		exit;
	}
	
	cookie('shopcard', $new_shopcard, (YMY_NOW_TIME + 3600*24*30), '/');
	
}
else{
	$order_sql = 'select * from '.DB::table('order').' where orderid='.$orderid;
	
	$order_feed = $order = DB::fetch_first($order_sql);
	if(empty($order)){
		echo "参数错误:err200";
		exit;
	}
	$order_detail_feeds = array();
	$void = $order['void'];
	
	$order_detail_sql = 'select a.id,a.orderid,b.images,a.proid,b.title,a.procount,a.proname,a.pronumber,a.price,a.cprice,a.state,b.nums,a.attributes from '.DB::table('orderdetail').' a left join '.DB::table('product')."  b on a.proid=b.id  WHERE a.state=0 and  a.username='".$ymy_UserName."' and    b.checked=1 and a.orderid='{$void}'";
	$order_details = DB::query($order_detail_sql);
	
	while(false !== ($order_detail = DB::fetch($order_details))){
		$total = $order_detail['price'] * $order_detail['procount'];
		$payamount += $total;
		$total = 0;
		if($order_detail['nums'] < 1 || $order_detail['procount'] > $order_detail['nums']){
			$product_empty_list[] = array('title'=>$order_detail['title']);
		}
		$order_detail_feeds[] = $order_detail;
	}
	
	if($ismyd == 1 || $isjif == 1){
		//取得当前会员的真实蚂蚁盾
		$credit = DB::fetch_first ( "SELECT coupon,integral FROM " . DB::table ( 'users' ) . " WHERE username='".$ymy_UserName."'" );
		if(!is_array($credit)){
			echo "参数错误:err103";
			exit;
		}
		else{
			if($ismyd == 1){
				if($myd > $credit['coupon']){
					echo "参数错误:err104";
					exit;
				}
				if($myd > $payamount){
					$myd = $payamount;
					$monjf = 0;
					$jif = 0;
				}
				$payamount = $payamount - $myd;
			}
			
			if($isjif == 1){
				if($jif > $credit['integral']){
					echo "参数错误:err105";
					exit;
				}
				if($monjf > $payamount){
					$monjf = $payamount;
					$jif = ($monjf * 100) / sys_monjf;
				}
				$payamount = $payamount - $monjf;
			}
		}
	}
	
	if($payamount < 0){
		echo '参数错误:err106';
		exit;
	}
	
	if($order['remark'] == '20抵用券'){
	    $v_sql = 'SELECT used FROM '.DB::table('voucher').' WHERE username=\''.$order['username'].'\'';
	    $voucher = DB::fetch_first($v_sql);
	    if(is_array($voucher) && isset($voucher['used']) && $voucher['used'] < 1){
	        $payamount = $order['payamount'];
	    }
	}
	// $payamount = $order['payamount'];
}


if(!empty($product_empty_list)){
	$prompt = '';
	foreach($product_empty_list as $empty_p){
		$prompt .= empty($prompt) ? $empty_p['title'] : '，' . $empty_p['title'];
	}
	echo $prompt . '&nbsp;&nbsp;&nbsp;&nbsp;库存不足';
	exit;
}



include $source;


