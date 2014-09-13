<?php
require_once ("../sys.inc/config.php");
define('YMY_TUAN_MANEGE', true);
define('YMY_NOW_TIME', time());
define('YMY_IS_GET', (strtolower($_SERVER['REQUEST_METHOD']) == 'get'));
define('YMY_IS_POST', (strtolower($_SERVER['REQUEST_METHOD']) == 'post'));
define('YMY_NOW_MASTER', getcookie('sw_username'));

// 权限检测
checkManagerLevel(1);

$action = Q('act');

viewHead( "APP设置 -商品" );

if(YMY_IS_POST){
	if($action == 'add' || $action == 'edit'){
		$tuanid = intval(F('tuanid'));
		$productid = intval(F('productid'));
		$title = I(F('title'));
		$description = I(F('description'));
		$content = I(F('content'));
		$images = I(F('images'));
		$bigclassid = I(F('bigclassid'));
		$smallclassid = intval(F('smallclassid'));
		$checked = F('checked') == 1 ? 1 : 0;
		$tprice = floatval(F('tprice'));
		$starttime = F('starttime');
		$endtime = F('endtime');
		$sortorder = intval(F('sortorder'));
		$price = (double) F('price');
		
		$starttime = strtotime($starttime);
		$endtime = strtotime($endtime);
		
		if( empty($tprice) || $tprice < 0 ){
			FormatMsg('请设置团购价', 1);
		}
		if(!$starttime){
			FormatMsg('请设置团购开始时间', 1);
		}
		if(!$endtime){
			FormatMsg('请设置团购结束时间', 1);
		}
		
		if($starttime > $endtime){
			list($starttime, $endtime) = array($endtime, $starttime);
		}
		
		$product_sql = 'select id,bigclassid,smallclassid,title,marketprice,price,number,images,checked,isdel,state from '.DB::table('product').' where id='.$productid;
		$product = DB::fetch_first($product_sql);
		if(empty($product)){
			FormatMsg('商品不存在，无法设置团购<br /><a href="index.php">返回团购列表</a>');
		}
		if($product['isdel'] > 0){
			FormatMsg('商品已删除，无法设置团购<br /><a href="index.php">返回团购列表</a>');
		}
		if($product['checked'] < 1){
			FormatMsg('商品未通过审核，无法设置团购<br /><a href="index.php">返回团购列表</a>');
		}
		if($product['state'] < 1){
			FormatMsg('商品未上架，无法设置团购<br /><a href="index.php">返回团购列表</a>');
		}
		$producttitle = $product['title'];
		$productimages = $product['images'];
		$productnumber = $product['number'];
		$bigclassid = $product['bigclassid'];
		$smallclassid = $product['smallclassid'];
		$price = $product['price'];
		$mprice = $product['marketprice'];
		
		$discount = floor(($tprice / $mprice) * 100);
		
		if($action == 'add'){
			$tuan_sql = 'select id from '.DB::table('tuan').' where productid='.$productid.' and ((starttime<'.YMY_NOW_TIME.' and endtime>'.YMY_NOW_TIME . ') or starttime>' . YMY_NOW_TIME . ') and isdel=0';
			$tuan = DB::fetch_first($tuan_sql);
			if(!empty($tuan)){
				FormatMsg('商品已设置团购，无需重复设置<br /><a href="index.php">返回团购列表</a>');
			}
		}
		
		$data = array(
			'productid' => $productid,
			'producttitle' => $producttitle,
			'productimages' => $productimages,
			'productnumber' => $productnumber,
			'bigclassid' => $bigclassid,
			'smallclassid' => $smallclassid,
			'title' => $title,
			'description' => $description,
			'content' => $content,
			'images' => $images,
			'price' => $price,
			'mprice' => $mprice,
			'tprice' => $tprice,
			'discount' => $discount,
			'starttime' => $starttime,
			'endtime' => $endtime,
			'checked' => $checked,
			'sortorder' => $sortorder
		);
		
		if($action == 'add'){
			$data['postmaster'] = YMY_NOW_MASTER;
			$data['posttime'] = YMY_NOW_TIME;
			$result = DB::insert('tuan', $data, true);
			$action_type = '添加';
		}
		elseif($action == 'edit'){
			$data['editmaster'] = YMY_NOW_MASTER;
			$data['edittime'] = YMY_NOW_TIME;
			$condition = array('id' => $tuanid);
			$result = DB::update('tuan', $data, $condition);
			$action_type = '编辑';
		}
		
		if($result){
			$msg = '团购'.$action_type.'成功<br /><a href="index.php">返回团购列表</a>';
			FormatMsg($msg);
		}
		else{
			FormatMsg($action_type.'失败', 1);
		}
	}
	
	
	exit;
}



// 添加 and 编辑
if($action == 'add' || $action == 'edit'){
	if($action == 'add'){
		$productid = Q('productid');
		$productid = intval($productid);
		$tuan_sql = 'select id,productid,bigclassid,smallclassid,price,tprice,title,description,content,discount,starttime,endtime,posttime,checked,sortorder from '.DB::table('tuan').' where productid='.$productid.' and ((starttime<'.YMY_NOW_TIME.' and endtime>'.YMY_NOW_TIME.') or starttime>'.YMY_NOW_TIME.')  and isdel=0';
	}
	else if($action == 'edit'){
		$tuanid = Q('tuanid');
		$tuanid = intval($tuanid);
		// $tuan_sql = 'select id,productid,bigclassid,smallclassid,price,title,description,content,discount,starttime,endtime,posttime,checked,sortorder from '.DB::table('tuan').' where id='.$tuanid.' and ((starttime<'.YMY_NOW_TIME.' and endtime>'.YMY_NOW_TIME.') or starttime>'.YMY_NOW_TIME.')  and isdel=0';
		$tuan_sql = 'select id,productid,bigclassid,smallclassid,price,tprice,title,description,content,images,discount,starttime,endtime,posttime,checked,sortorder from '.DB::table('product_app').' where id='.$tuanid.' and isdel=0';
	}
	
	$tuan = DB::fetch_first($tuan_sql);
	if($action == 'edit' && empty($tuan)){
		FormatMsg('没有该团购信息, 无法编辑');
	}
	if($action == 'add' && !empty($tuan)){
		$action = 'edit';
	}
	if($action == 'edit'){
		$tuanid = $tuan['id'];
		$productid = $tuan['productid'];
	}
	$product_sql = 'select id,bigclassid,smallclassid,title,marketprice,price,number,images,checked,isdel,state from '.DB::table('product').' where id='.$productid;
	$product = DB::fetch_first($product_sql);
	
	if(empty($product)){
		FormatMsg('商品不存在，无法设置团购<br /><a href="index.php">返回团购列表</a>');
	}
	if($product['isdel'] > 0){
		FormatMsg('商品已删除，无法设置团购<br /><a href="index.php">返回团购列表</a>');
	}
	if($product['checked'] < 1){
		FormatMsg('商品未通过审核，无法设置团购<br /><a href="index.php">返回团购列表</a>');
	}
	if($product['state'] < 1){
		FormatMsg('商品未上架，无法设置团购<br /><a href="index.php">返回团购列表</a>');
	}
	
	// 商品类别
	$cate_str = $product['bigclassid'] . $product['smallclassid'];
	$cate_str = replaceStr($cate_str,"|0|","");
	$cate_ids = explode('|', $cate_str);
	$cate_sql = 'select id,cnname from '.DB::table('productclass').' where id in ('.implode(',', $cate_ids).')';
	$cates = DB::query($cate_sql);
	
	if($action == 'edit'){
		if($tuan['starttime'] < YMY_NOW_TIME && $tuan['endtime'] > YMY_NOW_TIME){
			$tuan_status = '团购已开始';
		}
		elseif($tuan['starttime'] > YMY_NOW_TIME){
			$tuan_status = '团购未开始';
		}
		elseif($tuan['endtime'] < YMY_NOW_TIME){
			$tuan_status = '团购已结束';
		}
		
		if($tuan['checked'] < 1){
			$tuan_status = '团购未通过审核';
		}
	}
}

// 列表
if($action != 'add' && $action !='edit'){
	if($action == 'del'){
		$del_id = intval( Q('id') );
		$del_sql = 'update '.DB::table('product_app').' set isdel=1,checked=0,editmaster=\''.YMY_NOW_MASTER.'\' where id='.$del_id.' limit 1';		
		$del_rst = DB::query($del_sql);
	}
	
	$page = intval(Q('page'));
	$page = $page < 1 ? 1 : $page;
	$size = 35;
	
	$keyword = trim(Q('keyword'));
	$classid = intval(Q('classid'));
	$bigclassid = intval(Q('bigclassid'));
	$smallclassid = intval(Q('smallclassid'));
	$checked = trim(Q('checked'));
	$sort = intval(Q('sort'));
	$starttime = trim(Q('starttime'));
	$starttime = strtotime($starttime);
	$starttime_eq = trim(Q('starttime_eq'));
	$endtime = trim(Q('endtime'));
	$endtime = strtotime($endtime);
	$endtime_eq = trim(Q('endtime_eq'));
	
	$list_fields = array(
		'a.id',
		'a.productid',
		'a.bigclassid',
		'a.smallclassid',
		'a.title',
		'a.number',
		'a.content',
		'a.images',
		'a.imageslist',
		'a.soldnum',
		'a.checked',
		'a.state',
		'a.isdel',
		'a.editmaster',
		'a.postmaster',
		'a.edittime',
		'a.posttime',
		'b.price',
		'b.soldnum'
	);
	$list_sql = 'select '.implode(',', $list_fields).' from '.DB::table('product_app').' as a left join '.DB::table('product').' as b on a.productid=b.id  where a.isdel=0 order by posttime desc ';
	
	
	
	if(!empty($keyword)){
		$list_sql .= ' and (a.number like \'%'.$keyword.'%\' or a.title like \'%'.$keyword.'%\') ';
	}
	
	if($smallclassid > 0){
		$list_sql .= ' and (a.smallclassid=\''.$smallclassid.'\' or a.bigclassid like \'%|'.$smallclassid.'|%\') ';
	}
	elseif($bigclassid > 0){
		$list_sql .= ' and (a.smallclassid=\''.$bigclassid.'\' or a.bigclassid like \'%|'.$bigclassid.'|%\') ';
	}
	elseif($classid > 0){
		$list_sql .= ' and (a.smallclassid=\''.$classid.'\' or a.bigclassid like \'%|'.$classid.'|%\') ';
	}
	
	if(!empty($checked)){
		if($checked == 99){
			$list_sql .= ' and a.checked=1 ';
		}
		else{
			$list_sql .= ' and a.checked=0 ';
		}
	}
	
	$start_comparison = $starttime_eq == 'gt' ? '>' : '<';
	$end_comparison = $endtime_eq == 'gt' ? '>' : '<';
	if($starttime && $endtime){
		if($starttime > $endtime){
			list($starttime, $endtime) = array($endtime, $starttime);
		}
		if($start_comparison == '>' && $end_comparison == '<'){
			$time_comparison = ' and ';
		}
		else{
			$time_comparison = ' or ';
		}
		$list_sql .= ' and (starttime'.$start_comparison.$starttime.$time_comparison.' endtime'.$end_comparison.$endtime.') ';
		$starttime = date('Y-m-d H:i:s', $starttime);
		$endtime = date('Y-m-d H:i:s', $endtime);
	}
	elseif($starttime){
		$list_sql .= ' and starttime'.$start_comparison.$starttime.' ';
		$starttime = date('Y-m-d H:i:s', $starttime);
	}
	elseif($endtime){
		$list_sql .= ' and endtime'.$end_comparison.$endtime.' ';
		$endtime = date('Y-m-d H:i:s', $endtime);
	}
	
	
	if($sort > 0){
		$desc = $sort == 1 ? ' asc ' : ' desc ';
		$list_sql .= ' order by sortorder '.$desc;
	}
	
	// page start
	$pageurl = 'keyword='.$keyword.'&classid='.$classid.'&bigclassid='.$bigclassid.'&smallclassid='.$smallclassid.'&checked='.$checked.'&sort='.$sort.'&starttime='.$starttime.'&starttime_eq='.$starttime_eq.'&endtime='.$endtime.'&endtime_eq='.$endtime_eq;
	
	
	$total = DB::getCount($list_sql);
	$pagecount = ceil($total/$size);
	if($page > $pagecount){
		$page = $pagecount;
	}
	$page = $page < 1 ? 1 : $page;
	$offset = ($page - 1) * $size;
	// page end
	
	$list_sql .= ' limit '.$offset.','.$size;
	
	//echo $list_sql;
	$list = DB::query($list_sql);
}

switch($action){
	case 'add': // 添加
		include 'edit.php';
		break;
	case 'edit': // 编辑
		include 'edit.php';
		break;
	case 'del': // 删除		
		include 'list.php';		
		break;
	default: // 列表
		//echo "<script>alert('I am default')</script>";
		include 'list.php';
}

viewFoot();