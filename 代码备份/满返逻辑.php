
满返
总额满就返	黑名单，应该去除黑名单中商品的总价
			白名单,	总额应该就是白名单中的总价


先查询表看总价是否满足基本条件
	->查询订单中的产品id
	->查看这些产品id是否在白名单中,如果在的话就表明以白名单为准,暂时这么干
	->查看白名单中的总额是否满足条件


订单中的商品有普通商品，黑名单中商品，白名单中的商品
	->总额是否足满返条件
	->总额减去白名单中的商品	查出订单中的商品id看看是否在白名单中  在的话要查出该产品的价格和数目
	->总额减去黑名单中的商品	查出订间中的商品id看看是否在黑名单中
	->返回蚂蚁盾=白名单返回+剩余总额返回