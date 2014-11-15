<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>我的订单状态逻辑</title>
</head>
<body>
	<?php 
		if(未付款){
			合并单元格
			显示"付款"（按总订单付款）	
		}elseif(已付款){
			单个显示单元格
			显示"申请退款"，"查看详情"
		}elseif(已申请退款){
			单个显示单元格
			显示"查看退款进程"
		}
	 ?>
	 <tip class="question">
	 	为毛还分为一个产品或多个产品，有病啊，不都是一样的流程，不知道是哪头猪写的
	 </tip>
</body>
</html>