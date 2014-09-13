<?php defined('YMY_TUAN_MANEGE') or exit; ?>
<form action="index_tuan.php" method="get">
	<table class="tb" width="100%" id="tb">
		<tr class="thead">
			<th colspan="2">团购管理</th>
		</tr>
		<tr class="tdbg">
			<td>
				&nbsp;商品搜索：<input type="text" name="keyword" style="width:200px;" value="<?php echo $keyword; ?>" />&nbsp;<span class="gray">可搜 名称、编号</span>
				&nbsp;&nbsp;&nbsp;&nbsp;商品类别：
				<select name="classid" id="city" USEDATA="dataSrc" SUBCLASS="1"></select>
				<select name="bigclassid" id="country" USEDATA="dataSrc" SUBCLASS="2"></select>
				<select name="smallclassid" id="town" USEDATA="dataSrc" SUBCLASS="3"></select>
				&nbsp;&nbsp;&nbsp;&nbsp;审核：
				<select name="checked">
					<option value="0">请选择</option>
					<option value="99" <?php echo $checked==99?' selected="selected"':'';?>>通过</option>
					<option value="10" <?php echo $checked==10?' selected="selected"':'';?>>待审</option>
				</select>
				&nbsp;&nbsp;&nbsp;&nbsp;根据展现排序：
				<select name="sort">
					<option value="0">不排序</option>
					<option value="1" <?php echo $sort==1?' selected="selected"':'';?>>从小到大</option>
					<option value="2" <?php echo $sort==2?' selected="selected"':'';?>>从大到小</option>
				</select>
				<br />
				&nbsp;开始时间：
				<select name="starttime_eq">
					<option value="gt">大于</option>
					<option value="lt" <?php echo $starttime_eq=='lt'?' selected="selected"':'';?>>小于</option>
				</select>
				<input type="text" name="starttime" id="starttime" value="<?php echo $starttime; ?>" style="width:150px;" readonly="readonly" />
				&nbsp;&nbsp;&nbsp;&nbsp;结束时间：
				<select name="endtime_eq">
					<option value="gt">大于</option>
					<option value="lt" <?php echo $endtime_eq=='lt'?' selected="selected"':'';?>>小于</option>
				</select>
				<input type="text" name="endtime" id="endtime" value="<?php echo $endtime; ?>" style="width:150px;" readonly="readonly" />
				&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class="btn" value="搜 索" />
			</td>
		</tr>
	</table>
</form>

<table class="tb" width="100%">
	<tr class="dtit">
		<td width="12%">商品编号</td>
		<td>标题</td>
		<td width="5%">价格</td>
		<td width="4%">折扣</td>
		<td width="5%">折后价</td>
		<td width="4%">已售</td>
		<td width="4%">审核</td>
		<td width="8%">开始日期</td>
		<td width="8%">结束日期</td>
		<td width="6%">新增人</td>
		<td width="8%">新增日期</td>
		<td width="8%">展现排序</td>
		<td width="8%">操作</td>
	</tr>
	
	<?php 
	if($list !== false){
		while (false !== ($tuan = DB::fetch($list))){
	?>
		<tr class="tdbg li">
			<td><?php echo $tuan['productnumber']; ?></td>
			<td><?php echo empty($tuan['title'])?$tuan['producttitle']:$tuan['title']; ?></td>
			<td><?php echo $tuan['price']; ?></td>
			<td><?php echo $tuan['discount']; ?>%</td>
			<td><?php echo $tuan['tprice']; ?></td>
			<td><?php echo $tuan['soldnum']; ?></td>
			<td><?php echo empty($tuan['checked'])?'<span style="color:gray;">×</span>':'<span style="color:green;">√</span>';?></td>
			<td><?php echo date('Y-m-d H:i:s', $tuan['starttime']); ?></td>
			<td><?php echo date('Y-m-d H:i:s', $tuan['endtime']); ?></td>
			<td><?php echo $tuan['postmaster']; ?></td>
			<td><?php echo date('Y-m-d H:i:s', $tuan['posttime']); ?></td>
			<td>
				<input type="text" name="sort_order[<?php echo $tuan['id']; ?>]" value="<?php echo $tuan['sortorder']; ?>" maxlength="4" style="width:46px;text-align:center;" />
			</td>
			<td>
				<a href="index_tuan.php?act=edit_tuan&tuanid=<?php echo $tuan['id']; ?>">编辑</a>&nbsp;
				<a href="javascript:;" class="delinfo" gourl="?act=del&id=<?php echo $tuan['id']; ?>">删除</a>
			</td>
		</tr>
	<?php 
		}
	}
	?>
	
	<tr class="tdbg">
		<td colspan="13">
			<?php echo fPageCount($total, $size, $page, $pageurl); ?>
		</td>
	</tr>
</table>

<script type="text/javascript" src="../js/calendar/calendar.js"></script>
<script type="text/javascript" src="../js/prototype.min.js"></script>
<script type="text/javascript" src="../js/linkage.js"></script>
<script type="text/javascript">
$(function(){
	var linkclass = new Linkage("dataSrc", "../product/class.xml");
	linkclass.BLANK_SELECT = "---请选择---";
	linkclass.init();
	linkclass.initLinkage("dataSrc","<?php echo $classid; ?>",1);
	linkclass.initLinkage("dataSrc","<?php echo $bigclassid; ?>",2);
	linkclass.initLinkage("dataSrc","<?php echo $smallclassid; ?>",3);
	
	
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