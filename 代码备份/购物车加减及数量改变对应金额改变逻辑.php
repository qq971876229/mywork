<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>购物车</title>
</head>
<body>
	<div class="luoji">
		1.数量改变 对应的金额和总金额改变
		改变数量后，应该是其他产品的金额加上新的金额
		可以删除此产品（类似于垃圾桶效果）然后再加上新的金额
		注：同时要修改cookie的值,提交订单后获取的是cookie里的值,购物车cookie是通过所传的id重新生成一个购物车
		2.加  点击一次总价就加一次产品的价格	
		3.减  点击一次总价就减一次产品的价格	
	</div>
	<div class="tip">
		1.输入小数未做判断，必须是正整数,如果是小数传partseInt的值
		2.小数会产生误差
	</div>
	<div class="code">
		<script type="text/javascript">
		/*购物车数量改变 金额跟着改变 start*/	
			$(".proNum").blur(function(){
				if($(this).val()<=0){
					$(this).val("1");
				}

				productid=$(this).attr("productid");
				productprice=$(this).attr("productprice");	//初始单个产品总价
				proNum=parseFloat($(this).val());
				proPrice=parseFloat($(this).attr("proPrice")*proNum);//单个产品新的价格			

				totalprice=parseFloat($("#totalprice").html());
				totalprice-=parseFloat(productprice);
				totalprice+=parseFloat(proPrice);

				$("#totalprice").html(totalprice);

				$.ajax({
					type:"post",
					url:"../plus/appshopcart.php",
					data:"act=changeNum&productid="+productid+"&proNum="+proNum,
					success:function(msg){
						//alert(msg);
					}
				});
			});
			/*购物车数量改变 金额跟着改变 end*/

			/*提交前判断是否选择地址 提交订单前判断 start*/				
			$("#pay_order").click(function(){
				radio_len=$(":radio:checked").length;
				if(radio_len<2){	//收货地址
					$("#messageBox").show();
					setTimeout(xiaoshi,2000);
					return false;					
				}else{		//提货网点					
					networkid=$("#networkid").val();					
					if(networkid=="请选择"||networkid==0||networkid==null){
						$("#messageBox").html("请选择提货网点").show();
						setTimeout(xiaoshi,2000);
						return false;
					}
				}				
			});				

			function xiaoshi(){
				$("#messageBox").fadeOut(2000);
			}

			/*提交前判断是否选择地址 end*/
			/*没有地址跳转 start*/
			$address_rest_row=DB::fetch($address_rest);
			if(empty($address_rest_row)){
				echo "<div id='messageBox' style='display:block;left:1%;'>亲，你没有填写任何收货地址哦，3秒后跳转……</div>";
				echo "<script>setTimeout('location.href=\"./addAddress.php\"',3000);</script>";
			}
			/*没有地址跳转 end*/

		</script>
	</div>
</body>
</html>