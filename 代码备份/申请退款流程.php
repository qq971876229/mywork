<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>申请退款流程</title>
</head>
<body>
	<div class="title">
		客户退款->客服退款显示->->客服审核通过->财务退款显示->购物完成并删除
		跳转到查看详细2，提交申请退款表单		
	</div>
	<div class="tip">
		1.提交只需要一个orderid,然后通过orderid查询表中它的内容并插入到数据库对应的表中
		2.现在有一个问题就是如果订单详情那里改了，这边表的内容不会更改，应该是从数据库里动太获取，但是放表中，速度会快一些
		3.checked 
			0 客服未审核->客服表显示
			1 财务款审核->客服审核成功，提交财务，财务表显示，客服表不显示
			2 退款成功->财务审核成功,退款成功后，财务表不显示，一旦审核成功，就不显示，程序员可在数据库中修改
			3 客服那里应该没有财务审核那一项
	</div>
</body>
</html>