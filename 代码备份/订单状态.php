<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
</head>
<body>
	<div class="state">
		对应关系
		state=0	 款付款
		state=1  已发货，待收货
		state=2  
		state=3  
	</div>
	<div class="code">
		<?php 
			function getorderstate($s)
			{
				 $rstr="";
					switch ($s) {
					case "0" :
						$rstr= "<font color=red>待付款</font>";
						break;
					case "1" :
						$rstr= "<font color=red>已付款,待发货</font>";
						break;
					case "2" :
						$rstr= "<font color=red>已发货,待收货</font>";
						break;
					case "3" :
						$rstr= "<font color=green>交易完成</font>";
						break;
					case "4" :
						$rstr= "<font color=gray>已退款</font>";				
						break;
					case "-1" :
						$rstr= "<font color=gray>无效定单</font>";
						break;
					default :
						$rstr= ("未知");
						break;
				}
				return $rstr;
			}
		?>
	</div>
</body>
</html>