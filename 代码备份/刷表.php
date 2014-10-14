<?php
/*
通过子单查找总单的内容并修改网站编号networkid

select orderid from ymy_orderdetail where id='1000017567'

select network from ymy_order where void='orderid'

network='编号'


select a.orderid,b.void,b.network from ymy_orderdetail as a left join on a.orderid=b.void where a.id=''


*/



select Orderid from ymy_orderdetail where id='1000017567'

update ymy_order set networkid=''  where void=''


?>