<?php
通过用户名在总订单表中查找是否在本网站买过东西,最近买的排序
->买过的话就查找网点的networkid
->通过networkid到网站表中查询网点名称
->查找到内容并显示
->通过pointid和father查找自掉点名称

->如果没有，就显示全部，让其点击利用js加载


现在的问题是地图最后才加载成功，会冲掉我写的功能
可以在地图加载后再用js把获取的内容加上


echo "门店".$row["number"]."名称：".$row["title"]."门店地址：".$row["address"]."负责人：".$row["master"].empty($row["mobile"])?$row["phone"]:$row["mobile"];


点击蚂蚁盾 出现弹框 属性checked=checked
再次点击蚂蚁盾 弹框消失  属性checked=false

问题：如果上次没有购买，没有地址，会出现undefine提示





	var text = data.split("|sw|"); 		
 		if (parseInt(text[0]) == 1) { 	
 			var last_address=$("#last_address").val();
 			var last_value=$("#last_value").val();
 			var last_pointid=$("#last_pointid").val();
 			var last_point_name=$("#last_point_name").val();
			$("#pointid").empty(); 
			$("#pointid").append("<option value=\""+last_pointid+"\">"+last_point_name+"</option>"); 
			$("#pointid").append(text[2]); 			
			$("#networkid").empty();   
			$("#networkid").append("<option pointid=\""+last_pointid+"\"  value=\""+last_value+"\">"+last_address+"</option>"); 
			//$("networkid").append(); 
			$("#networkid").append(text[1]); 
		} else {

			$("#pointid").empty();   
			$("#pointid").append("<option value=\"0\">全部</option>"); 
			$("#networkid").empty();   
			$("#networkid").append("<option value=\"0\">请选择</option>"); 
		}


第二种思路：
查询，ajax中，如果是直接在里面加一个selected

SELECT networkid FROM	ymy_order WHERE	username ='test1' order by okpaydate desc

57101555


?>
