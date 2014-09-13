<?php defined('YMY_TUAN_MANEGE') or exit; ?>
<form action="index_tuan.php?act=<?php echo $action; ?>" method="post" id="tuan_form" autocomplete="off">
	<table  class="tb" width="100%" id="tb">
		<?php if($action == 'edit'): ?>
		<tr class="thead">
			<th colspan="2">团购状态</th>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle"   align="right">&nbsp;</td>
			<td>
				<span class="red"><?php echo $tuan_status; ?></span>
			</td>
		</tr>
		<?php endif; ?>
		<tr class="thead">
			<th colspan="2">商品信息</th>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle"   align="right">商品ID：</td>
			<td>
				<?php echo $product['id']; ?>
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
			<td width="88" height="22" valign="middle"   align="right">商品类别：</td>
			<td>
				<?php 
					while(false !== ($cat = DB::fetch($cates))){
						echo $cat['cnname'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					}
				?>
			</td>
		</tr>
		<?php if(!empty($product['images'])): ?>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle"   align="right">商品图片：</td>
			<td>
				<img src="<?php echo $product['images']?>" width="80px" height="80px" alt="<?php echo $product['title']; ?>" />
			</td>
		</tr>
		<?php endif; ?>
		<tr class="thead">
			<th colspan="2"><?php echo $action=='add' ? '添加':'编辑'; ?>团购</th>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle"   align="right">团购标题：</td>
			<td>
				<input type="text" name="title" value="<?php echo $tuan['title']; ?>" style="width:380px;" />
				<span class="red">您如果不设置标题，则默认会显示商品原标题</span>
			</td>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle"   align="right"><span class="red">*</span>&nbsp;团购价：</td>
			<td>
				<input type="text" name="tprice" value="<?php echo $tuan['tprice']; ?>" id="tprice" style="width:100px;" />
			</td>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle"   align="right">展现排序：</td>
			<td>
				<input type="text" name="sortorder" value="<?php echo $tuan['sortorder']; ?>" style="width:50px;" />
				<span class="red">数值范围：0-9999，数值越大，团购页面展现越靠前</span>
			</td>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle"   align="right">团购展示图：</td>
			<td>
				<input name="images" value="<?php echo $tuan['images']; ?>" id=sPic type="text"  style="width:200px;" maxlength="200"  >
				&nbsp;&nbsp;<span onClick="javascript:previewObj('img','',document.tuan_form.sPic.value)" class="preview">查看</span>
				&nbsp;&nbsp;<span onClick="javascript:delFile('',document.tuan_form.sPic)" class="preview">删除</span>
				&nbsp;<iframe src="../ueditor/sanways_upload.php" scrolling="no" topmargin="0" width="300" height="24" marginwidth="0" marginheight="0" frameborder="0" align="center"></iframe>&nbsp;&nbsp;此图片为商品的缩略图，不会在商品的详细内容页显示。
			</td>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle"   align="right"><span class="red">*</span>&nbsp;开始时间：</td>
			<td>
				<input type="text" name="starttime" id="starttime" value="<?php if(isset($tuan['starttime'])){echo date('Y-m-d H:i:s',$tuan['starttime']);}else{echo date('Y-m-d H:i:s', YMY_NOW_TIME);} ?>" style="width:150px;" readonly="readonly" />
				<span class="gray"></span>
			</td>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle"   align="right"><span class="red">*</span>&nbsp;结束时间：</td>
			<td>
				<input type="text" name="endtime" id="endtime" value="<?php if(isset($tuan['endtime'])){echo date('Y-m-d H:i:s',$tuan['endtime']);}else{echo date('Y-m-d H:i:s', YMY_NOW_TIME+86400);} ?>" style="width:150px;" readonly="readonly" />
				<span class="gray"></span>
			</td>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle"   align="right">团购摘要：</td>
			<td>
				<textarea  rows="5" name="description" style="width:480px;height:50px"><?php echo $tuan['description']; ?></textarea>
				<span class="red">您如果不设置摘要，则默认会显示商品原摘要</span>
			</td>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="top"   align="right">团购介绍：</td>
			<td>
				<textarea name="content" id="content"><?php echo $tuan['content']; ?></textarea>
				<span class="red" style="line-height:26px;">您如果不设置介绍，则默认会显示商品原介绍</span>
			</td>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="top"   align="right">审核：</td>
			<td>
				<input class="np"  type="radio" name="checked" value="1" style="vertical-align:top;" <?php if($tuan['checked']>0) echo ' checked="checked"'; ?> />通过
				&nbsp;&nbsp;
				<input class="np"  type="radio" name="checked" value="0" style="vertical-align:top;" <?php if(!isset($tuan['checked']) || $tuan['checked']<1) echo 'checked="checked"'; ?> />待审
			</td>
		</tr>
		<tr class="tdbg">
			<td width="88" height="22" valign="middle" align="right">&nbsp</td>
			<td>
				<input type="hidden" name="productid" value="<?php echo $product['id']; ?>" />
				<input type="hidden" name="bigclassid" value="<?php echo $product['bigclassid']; ?>" />
				<input type="hidden" name="smallclassid" value="<?php echo $product['smallclassid']; ?>" />
				<input type="hidden" name="price" id="price" value="<?php echo $product['price']; ?>" />
				<input type="hidden" name="productnumber"  value="<?php echo $product['productnumber']; ?>" />
				<input type="hidden" name="tuanid" value="<?php echo $tuan['id']?>" />
				<input type="submit" class="btn" value="提 交" />
			</td>
		</tr>
	</table>
</form>
<script type="text/javascript" src="../ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="../ueditor/ueditor.all.js"></script>
<script type="text/javascript" src="../js/calendar/calendar.js"></script>
<script type="text/javascript">
$(function(){
	var action = '<?php echo $action; ?>',
		ue = UE.getEditor('content'),
		price = $('#price').val();
	

	$('#tuan_form').on('submit', function(){
		var tprice = $('#tprice').val(),
			starttime = $('#starttime').val(),
			endtime = $('#endtime').val();
		tprice = $.trim(tprice);
		starttime = $.trim(starttime);
		endtime = $.trim(endtime);
		if(discount == '' || isNaN(discount)){
			alert('请设置团购折扣价');
			return false;
		}
		if(starttime == ''){
			alert('请设置团购开始时间');
			return false;
		}
		if(endtime == ''){
			alert('请设置团购结束时间');
			return false;
		}
		
		
		return true;
	});
	
	Calendar.setup({
		inputField     :    "starttime",
		ifFormat       :    "%Y-%m-%d %H:%M:%S",
		showsTime      :    true,
		position       :    [0, 0],
		timeFormat     :    "24"
	});
	Calendar.setup({
		inputField     :    "endtime",
		ifFormat       :    "%Y-%m-%d %H:%M:%S",
		showsTime      :    true,
		position       :    [0, 0],
		timeFormat     :    "24"
	});
	
});
</script>