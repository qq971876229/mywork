<?php
session_start ();
require_once ("../sys.inc/config.php");

viewHead ( "订单管理 - 订单" );
checkManagerLevel(2);
// Printjs("if(top == self){location.href = '../index.php';}");
Printjs ( "if(parent.$$('admincpnav')) parent.$$('admincpnav').innerHTML='后台首页&nbsp;&raquo;&nbsp;订单&nbsp;&raquo;&nbsp;订单管理';" );
echo "<script type=\"text/javascript\" src=\"../js/calendar/calendar.js\"></script>";
$action = Q( "action" );
Main ();
 

viewFoot ();

// *************************审核,推荐,删除*********************/
 
 
 
function Deletes() {
	$Id = intval ( Q ( "id" ) );
  	db::query ( "update ymy_order set  isdel=1 WHERE orderid =$Id" );
}
 
function Main() {
	$classid = intval ( R ( "classid" ) );
	$bigclass = intval ( R ( "bigclass" ) );
	$SmallClassId = intval ( R ( "SmallClassId" ) );
	$KeyWord = safe_replace(R ( "KeyWord" ));
	 
	
	$s=intval(R("sel"));
	$ptime=R("ptime");
	$stime=R("stime");
	$u=R("u");

 
 
	 
	?>

<SCRIPT language="JavaScript">
 function Runaction(theform)
{
  if(checkSub(theform,'nId[]')>=1)
  {
	 submitConfirm("你确定要执行此操作吗？");
	// return false;
  }
  else
   {
    ShowAlertb("请最少选择一条信息进行操作！");
	return false;
   }
 }

 
 
</SCRIPT>

<?php

	$number = 35;
	$page = intval ( Q ( "page" ) );

	echo "<table  class=tb width=\"100%\">";
	echo "  <tr class=thead>";
	echo "    <th colspan=11>订单管理</th>";
	echo "  </tr>";
	echo "  <tr>";
	echo "    <form method=\"post\" action=\"tuihuo_kefu.php\" name=\"form1\">";
	echo "      <td  height=\"11\" colspan=\"12\" align=\"left\" > 订单搜索： <input name=\"KeyWord\" type=\"text\" size=\"20\" maxlength=\"255\" />";
	echo "        &nbsp;&nbsp;交易状态：<select name=\"sel\" id=\"sel\"><option value=\"0\">---请选择---</option><option value=\"999\"  ";
	 if ($s==999) echo " selected" ;
	 echo" >待付款</option><option value=\"1\"  ";
	  if ($s==1) echo " selected"   ;
      echo ">已付款</option><option value= \"2\"   ";
	  if ($s==2) echo " selected" ;
      echo " >已发货</option><option value=\"3\"   ";
      if ($s==3) echo " selected";
       echo ">交易成功</option><option value=\"4\"   ";
      if ($s==4) echo " selected";
       echo ">已退款</option> ";
        
       echo "  ></select>&nbsp;&nbsp;";
	   
echo "           从<input name=\"ptime\" id=\"ptime\" type=\"text\"  style='width:150px;' maxlength=50  value=\"".$ptime."\" > 到 <input name=\"stime\" id=\"stime\" type=\"text\"  style='width:150px;' maxlength=50 value=\"".$stime."\">";	   
	
	echo "  &nbsp;&nbsp;&nbsp;<input name=\"button\" type=\"submit\" value=\"搜 索\"  class=\"btn\"/> <span class=gray>可搜 订单号、商品编号、用户名、商品名</span>";
	echo " </td>";
	echo "</form>";
	echo "</tr>";
	echo "<form name=\"iForm\" id=\"iForm\" action=\"ordermana.php?currentpage=\"" . $page . "\" method=\"post\">";
	echo "    <tr class=\"dtit\">";
	
	echo "      <td width=\"8%\" >订单号</td>";
	echo "      <td width=\"12%\" >支付宝/网银订单号</td>";
	echo "      <td  >商品名称</td>";
	echo "      <td  width=\"10%\" >商品编号</td>";
	echo "      <td  width=\"5%\" >价格</td>";
	echo "      <td  width=\"5%\" >数量</td>";
	echo "      <td width=\"8%\" >金额</td>";
	echo "      <td  width=\"8%\" >订货人</td>";
	
 	echo "      <td width=\"8%\">订单状态</td>";
	echo "      <td width=\"12%\">订购日期</td>";
	echo "      <td width=\"5%\">操作</td>";
	echo "    </tr>";
	
	/*查询内容  财务未审核内容*/
	if($page==0) $page=1;
	$sqlStr = "select * from " . DB::table ( 'tuihuo' ) . "   where checked=1";
	if (!empty ( $KeyWord )) {
		$sqlStr .= " And (proname Like '%" . $KeyWord . "%' or id Like '%" . $KeyWord . "%' or username Like '%" . $KeyWord . "%'  or pronumber Like '%" . $KeyWord . "%'  or orderid Like '%" . $KeyWord . "%')";
		
		
	}
	

			switch ($s) {
			case "999" :
				$sqlStr .= " And state=0";
				break;
			case "1" :
					$sqlStr .= " And state=1";
				break;
			case "2" :
					$sqlStr .= " And state=2";
				break;
			case "3" :
					$sqlStr .= " And state=3";
				break;
			case "4" :
					$sqlStr .= " And state=4";
				break;									
		}
		
		if($u=="all")
		{
			$sqlStr.=" and state>0 and DATEDIFF(now(),DATE_FORMAT(FROM_UNIXTIME(postdate),'%Y-%m-%d'))=0";
		
		}


		if($u=="day")
		{
			$sqlStr.=" and   DATEDIFF(now(),DATE_FORMAT(FROM_UNIXTIME(postdate),'%Y-%m-%d'))=0";
		
		}		
	
 
  if (!empty($ptime))
  {
     
	if($s==1)
	{
	$sqlStr.=" and  DATE_FORMAT(FROM_UNIXTIME(okpaydate),'%Y-%m-%d %T')  >='".$ptime."'";
	}else
	{
  	$sqlStr.=" and  DATE_FORMAT(FROM_UNIXTIME(postdate),'%Y-%m-%d %T')  >='".$ptime."'";
	}
  }
  if (!empty($stime))
  {
    //$stime=strtotime($stime);
	if($s==1)
	{
	$sqlStr.=" and  DATE_FORMAT(FROM_UNIXTIME(okpaydate),'%Y-%m-%d %T')  <='".$stime."'";
	}else
	{
  	$sqlStr.=" and  DATE_FORMAT(FROM_UNIXTIME(postdate),'%Y-%m-%d %T') <='".$stime."'";
	}
  } 
	
	$sqlStr .= " order by  id desc ";
	
	
	 $pageurl="sel=".$s."&ptime=".$ptime."&stime=".$stime."&KeyWord=".$KeyWord."&u=".$u."";
	$allRecordset = db::getCount ( $sqlStr );
	$tpagecount = ceil ( $allRecordset / $number );
	if ($page > $tpagecount)
		$page = $tpagecount;
	$page = max ( intval ( $page ), 1 );
	$offset = $number * ($page - 1);
	$query = DB::query ( "$sqlStr limit $offset, $number" );
	while ( $row = DB::fetch ( $query ) ) {
		
		echo "    <tr class=\"tdbg li\">";
		
		echo "      <td align=\"left\"><a href=\"orderdetail.php?id=" . $row ["id"] . "\" style=\"color:#0066FF\">" . $row ["id"] . "</a>";
		echo "</td>";
		echo "      <td>" . $row ["Orderid"] . "</td>";
		echo "      <td><a href='/product-" . $row ["proid"] . ".html' target='_blank'>" . msubstr($row ["proname"],10) . "";
		

		if($row["attributes"]!="")
		{
			echo "[".$row["attributes"]."]";
		}		
		
		echo "</a></td>";
		echo "      <td>" . $row ["pronumber"] . "</td>";
		echo "      <td>" . $row ["price"] . "</td>";
		echo "      <td>" . $row ["procount"] . "</td>";
		echo "      <td><font color=red class=price>&yen;" . cutprice ( $row ["price"]*$row ["procount"] ) . "</font></td>";
		echo "      <td>" . $row ["UserName"] . "</td>";
 
		
		
		echo "      <td>";
		
		
		echo getorderstate($row ["state"]);
		
 
		
		echo "</td>";
		
		echo "      <td>" . MyDate ( 'Y-m-d H:i:s', $row ["postdate"] ) . "</td>";
		echo "      <td>";
		echo "      <A href=\"tuihuo_detail.php?id=" . $row ["id"] . "\" target='_blank'>审核</A> ";
		echo "      </td>";
		echo "    </tr>";
	}
	
	echo "  <tr class=\"tdbg\">";
	echo "    <td colspan=\"11\" > ";
	echo fPageCount ( $allRecordset, $number, $page,$pageurl );
	
	echo "    </td>";
	echo "  </tr>";
	echo "  </form>";
	
	echo " </table>";
	
	?>

<?php
}
?>
<script language="javascript" type="text/javascript">
 
 			Calendar.setup({
				inputField     :    "ptime",
				ifFormat       :    "%Y-%m-%d %H:%M:%S",
				showsTime      :    true,
				position       :    [0, 0],
				timeFormat     :    "24"
			});

  			Calendar.setup({
				inputField     :    "stime",
				ifFormat       :    "%Y-%m-%d %H:%M:%S",
				showsTime      :    true,
				position       :    [0, 0],
				timeFormat     :    "24"
			});	
</script>			