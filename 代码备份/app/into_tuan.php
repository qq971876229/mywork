<?php
require_once ("../sys.inc/config.php");
define('YMY_TUAN_MANEGE', true);
define('YMY_NOW_TIME', time());
define('YMY_IS_GET', (strtolower($_SERVER['REQUEST_METHOD']) == 'get'));
define('YMY_IS_POST', (strtolower($_SERVER['REQUEST_METHOD']) == 'post'));
define('YMY_NOW_MASTER', getcookie('sw_username'));

// 权限检测
checkManagerLevel(1);

viewHead( "手机app商品设置团购 -APP" );

$action = 'intotuan';
//提交
if(YMY_IS_POST){
	$appid=intval(F('appid'));
	$productid = intval(F('productid'));
	$title = I(F('title'));
	$content = I(F('content'));
	$images = I(F('images'));
	$checked = intval(F('checked'));
	$state = intval(F('state'));
	$checked = $checked == 1 ? 1 : 0;
	$state = $state == 1 ? 1 : 0;
	
	$product_sql = 'select * from '.DB::table('product_app').' where isdel=0 and id='.$appid;
	
	$product = DB::fetch_first($product_sql);
	if(empty($product)){		
		FormatMsg('商品不存在或已删除1111<br /><a href="index.php">返回商品列表</a>');
	}
/* 	if($checked< 1){
		FormatMsg('商品未通过审核xxx<br />',1);
	}
	if($state < 1){
		FormatMsg('商品未上架<br />',1);
	} */
	
	$app_sql = 'select * from '.DB::table('product_app').' where isdel=0 and id='.$appid;	
	$app = DB::fetch_first($app_sql);
	if(empty($app)){
		$action = 'add';
	}
	
	$data = array(
			'productid' => $productid,
			'bigclassid' => $product['bigclassid'],
			'smallclassid' => $product['smallclassid'],
			'title' => $title,
			'number' => $product['number'],
			'content' => $content,
			'images' => $images,
			'checked' => $checked,
			'state' => $state,
	);
	
	if($action == 'add'){
		$data['postmaster'] = YMY_NOW_MASTER;
		$data['posttime'] = YMY_NOW_TIME;
		$result = DB::insert('product_app', $data, true);
		$action_type = '添加';
	}
	else{
		$data['editmaster'] = YMY_NOW_MASTER;
		$data['edittime'] = YMY_NOW_TIME;
		$condition = array('id' => $app['id']);		
		$result = DB::update('product_app', $data, $condition);
		$action_type = '编辑';
	}
	if($result){
		$msg = '手机app商品'.$action_type.'成功<br /><a href="index.php">返回商品列表</a>';
		FormatMsg($msg);
	}
	else{
		FormatMsg($action_type.'失败', 1);
	}
	exit;
}

//默认
$productid = intval(Q('tuanid'));
$product_sql = 'select a.id,a.productid,a.checked,b.checked as bchecked, a.state,a.number,a.title,b.price,b.marketprice from '.DB::table('product_app').' as a left join  '.DB::table('product').' as b  on a.productid=b.id    where a.isdel=0 and a.id='.$productid;

$product = DB::fetch_first($product_sql);

if(empty($product)){
	echo $product_sql;
	FormatMsg('商品不存在或已删除<br /><a href="index.php">返回商品列表</a>');
}
/* if($product['checked'] < 1){
	FormatMsg('商品未通过审核<br /><a href="ProductMana.php">返回商品列表</a>');
} */
/* if($product['state'] < 1){
	FormatMsg('商品未上架<br /><a href="ProductMana.php">返回商品列表</a>');
} */

$app_sql = 'select title,number,content,images,checked,state from '.DB::table('product_app').' where id='.$productid;
$app = DB::fetch_first($app_sql);

if(empty($app)){
	$action = 'add';
	$app = array(
		'title' => $product['title'],
		'content' => '',
		'images' => $product['images'],
		'checked' => 0,
		'state' => 0
	);
}

?>


<form action="app_edit.php" method="post" id="wap_form" autocomplete="off">
	<table  class="tb" width="100%" id="tb">
		<tr class="thead">
			<th colspan="2">商品信息</th>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle"   align="right">商品ID：</td>
			<td>
				<?php echo $product['productid']; ?>
			</td>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle"   align="right">商品编号：</td>
			<td>
				<?php echo $product['number']; ?>
			</td>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle"   align="right">商品名称：</td>
			<td>
				<?php echo $product['title']; ?>
			</td>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle"   align="right">参考价格：</td>
			<td>
				<?php echo $product['marketprice']; ?>
			</td>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle"   align="right">销售价格：</td>
			<td>
				<?php echo $product['price']; ?>
			</td>
		</tr>
		
		<tr class="tdbg">
			<td width="88" height="22" valign="middle"   align="right">商品图片：</td>
			<td>
				<img src="<?php echo $product['images']?>" width="80px" height="80px" alt="<?php echo $product['title']; ?>" />
			</td>
		</tr>
		
		<tr class="thead">
			<th colspan="2"><?php echo $action == 'edit' ? '编辑' : '添加'; ?>手机商品信息设置</th>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle"   align="right">商品标题：</td>
			<td>
				<input type="text" name="title" value="<?php echo $app['title']; ?>" style="width:380px;" />
				<span class="red">您如果不设置标题，则默认会显示商品原标题</span>
			</td>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle"   align="right">商品展示图：</td>
			<td>
				<input name="images" id=sPic type="text" value="<?php echo $app['images']; ?>" style="width:200px;" maxlength="200"  >
				&nbsp;&nbsp;<span onClick="javascript:previewObj('img','',document.tuan_form.sPic.value)" class="preview">查看</span>
				&nbsp;&nbsp;<span onClick="javascript:delFile('',document.tuan_form.sPic)" class="preview">删除</span>
				&nbsp;<iframe src="../ueditor/sanways_upload.php" scrolling="no" topmargin="0" width="300" height="24" marginwidth="0" marginheight="0" frameborder="0" align="center"></iframe>&nbsp;&nbsp;
			</td>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="top"   align="right">商品介绍：</td>
			<td>
				<textarea name="content" id="content"><?php echo $app['content']; ?></textarea>
			</td>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="top"   align="right">审核：</td>
			<td>			
				<input class="np"  type="radio" name="checked" value="1" style="vertical-align:top;" <?php if($app['checked'] == 1) echo ' checked="checked"'; ?> />通过
				&nbsp;&nbsp;
				<input class="np"  type="radio" name="checked" value="0" style="vertical-align:top;" <?php if($app['checked'] != 1) echo ' checked="checked"'; ?> />待审
			</td>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="top"   align="right">状态：</td>
			<td>
				<input class="np"  type="radio" name="state" value="1" style="vertical-align:top;" <?php if($app['state'] == 1) echo ' checked="checked"'; ?> />上架
				&nbsp;&nbsp;
				<input class="np"  type="radio" name="state" value="0" style="vertical-align:top;" <?php if($app['state'] != 1) echo ' checked="checked"'; ?> />下架
			</td>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle" align="right">&nbsp</td>
			<td>
				<input type="hidden" name="productid" value="<?php echo $product['productid']; ?>" />
				<input type="hidden" name="appid" value="<?php echo $_GET['appid'] ?> "/>
				<input type="submit" class="btn" value="提 交" />
			</td>
		</tr>
	</table>
</form>
<script type="text/javascript" src="../ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="../ueditor/ueditor.all.js"></script>
<script type="text/javascript">
$(function(){
	var ue = UE.getEditor('content');
});
</script>
<?php 
viewFoot();
?>