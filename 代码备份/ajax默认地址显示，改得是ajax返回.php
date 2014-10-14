<?php 
require_once ("../common/config.php");
require_once ("../common/cls.mail.php");
define('YMY_NOW_TIME', time());
session_start();
AjaxHead();


$act=F("act");

	if (isOutSubmit ()) {
		echo "非法外部提交被禁止";
		exit();
	}

 switch ($act)
 {
 	case "CheckMail":
 		CheckMail();
 		break;
 	case "CheckUserName":
 		CheckUserName();
 		break;
 	case "CheckCode":
 		CheckCode();
 		break;
 	case "reg":
 		reg();
 		break;
 	case "reg_app":
 		reg_app();
 		break;
 	 case "login":
 		login();
 		break; 	
 	case "appLogin":
 		appLogin();
 		break;
 	case "appAlterPwd":
 		appAlterPwd();
 		break;
 	 case "addcart":
 		CardAdd();
 		break; 
 	 case "upbuycart":
 		UpCash();
 		break; 	
	 case "addresssellist":
	    addresssellist();
		break;	
	 case "delReceipt":
	    delReceipt();
		break;	
	 case "commentinfo":
	    commentinfo();
		break;		
	 case "mobilecode":
	 	mobilecode();
	 	break;
 	case "sendcode":
 		sendcode();
 		break;
 	case "sendclassNums":
 		sendclassNums();
 		break;
 	default:
 	 break;
 	
 }

 function CheckMail()
 {
 	$UserEmail=safe_replace(unescape(trim(F("Mail"))));
 	
 	if (empty($UserEmail) || strlen($UserEmail)<4)
 	{ 
 		echo "0";
 		exit();
 	}
 
 	$row = DB::fetch_first ( "select userid from " . DB::table ( 'users' ) . " where useremail='" . $UserEmail . "'" );
 	if (! is_array ( $row )) {
 		echo "1";
 	}else {
 		
 		echo "0";
 	}
 	
 	
 }
 
 function CheckUserName()
 {
 	
 	$Uname=safe_replace(unescape(trim(F("Uname"))));
 	 
 	if (empty($Uname) || strlen($Uname)<2)
 	{
 		echo "0";
 		exit();
 	}
 	
 	$row = DB::fetch_first ( "select userid from " . DB::table ( 'users' ) . " where UserName='" . $Uname . "'" );
 	if (! is_array ( $row )) {
 		echo "1";
 	}else {
 			
 		echo "0";
 	}	
 
 }
 
 function CheckCode()
 {
 	 
 	$code=unescape(trim(F("code")));
 	if (strtolower($code)==strtolower($_SESSION['code']) )
 	{
 		echo "1";
 	}
 	else
 	{	echo "0";
 	}
 	
 
 }
 
function reg()
{
 $urpwd=F("yurpwd");
 $urrpwd=F("urrpwd");
 $uname=safe_replace(unescape(F("yuname")));
 $province=safe_replace(unescape(F("province")));
 $city=safe_replace(unescape(F("city")));
 $area=safe_replace(unescape(F("area")));
 $uaddress=safe_replace(unescape(F("uaddress")));
 $umob=safe_replace(unescape(F("umob")));
 $code=F("code");
 $ErrMsg="";
 $founer="true";
 /*if (strlen($uemail)<4  )
 {
    $ErrMsg="请输入您的登录邮箱";
 	$founer="false";
}*/

 if (strlen($umob)!=11  )
 {
    $ErrMsg="请输入您的手机号码";
 	$founer="false";
}


 if (strlen($uaddress)<4  )
 {
    $ErrMsg="请输入您的详细地址";
 	$founer="false";
}

if (strlen($urpwd)<6 ||  strlen($urpwd)>22)
{
	$ErrMsg="您输入您的密码长度必须在6-22之间？";
	$founer="false";
}

if ($urpwd!= $urrpwd)
{
	$ErrMsg="您输入您的密码长度必须在6-22之间？";
	$founer="false";
} 


if (strlen($uname)<2  )
{
	$ErrMsg="请输入您的用户名";
	$founer="false";
}

 
if (strtolower($code)!=strtolower($_SESSION['code']) )
{
	$ErrMsg="验证码不正确";
	$founer="false";
}

if(!empty($_SESSION['isreg']))
{
	if(time()-$_SESSION['isreg']<300)
	{
		$ErrMsg="五分钟内请不要重复注册";
		$founer="false";
	
	}
}

$popid=intval(getcookie("popuid"));

 
 
  	if ($founer=="true")
 	{
 		$UserIp=getIp();
 		$row = DB::fetch_first ( "select * from " . DB::table ( 'users' ) . " where UserName='" . $uname . "'" );
 		if (! is_array ( $row )) {
 			 
 			 
			$userlogins=1;
			$userface="/images/defauface.png";
 			$feedarr = array (
 					'username' => $uname,
 					'userpassword' => md5($urpwd),
 					'userface' => $userface,
 					'jobdate' => time(),
 					'lastlogin' => time(),
 					'userlogins' => $userlogins,
 					'realname' => '',
 					'province' => $province,
					'area' => $area,
					'city' => $city,
					'address' => $uaddress,
 					'usermobile' => $umob,
 					'usertel' => '',
 					'userpost' => '',
 					'usersign' => '',
 					'usersex' => '',
					'popid' => $popid,
 					'userlastip' => $UserIp
 					
 			);
 			$reuserid=DB::insert ( 'users', $feedarr,true );

			
			if($reuserid>0)
			{
 		    	
			$_SESSION['isreg']=time();
			
 			cookie ("username", $uname , time()+3600*24*30,"/");
			cookie ("userid", $reuserid , time()+3600*24*30,"/");
			writeilogs('注册','注册网站会员',sys_regjf,$uname);
			//sendmaillink($uemail);	
			}else{
				$ErrMsg="系统繁忙，请稍后重试";
				$founer="false";			
			}
 			
 			
 		}else{
 			$ErrMsg="用户或邮箱已存在";
 			$founer="false";
 		}
 		
 		
 	}
 
 
   if ($founer=="true") 
   {
   echo "1|sw|".$ErrMsg;
   }else {
   	echo "0|sw|".$ErrMsg;
   }
 		 
 
}

function reg_app()
{
	$urpwd=F("yurpwd");
	$urrpwd=F("urrpwd");
	$uname=safe_replace(unescape(F("yuname")));
	/* $province=safe_replace(unescape(F("province")));
	$city=safe_replace(unescape(F("city")));
	$area=safe_replace(unescape(F("area")));
	$uaddress=safe_replace(unescape(F("uaddress")));
	$umob=safe_replace(unescape(F("umob")));
	$code=F("code"); */
	$ErrMsg="";
	$founer="true";
	/*if (strlen($uemail)<4  )
	 {
	$ErrMsg="请输入您的登录邮箱";
	$founer="false";
	}*/

	/* if (strlen($umob)!=11  )
	{
		$ErrMsg="请输入您的手机号码";
		$founer="false";
	}


	if (strlen($uaddress)<4  )
	{
		$ErrMsg="请输入您的详细地址";
		$founer="false";
	} */

	
	
	
	if (strlen($urpwd)<6 ||  strlen($urpwd)>22)
	{
		$ErrMsg="您输入您的密码长度必须在6-22之间!";
		$founer="false";
	}

	if ($urpwd!= $urrpwd)
	{
		$ErrMsg="两次输入的密码不一致!";
		$founer="false";
	}

	if (strlen($uname)<2  )
	{
		$ErrMsg="请输入您的用户名";
		$founer="false";
	}		



	/* if (strtolower($code)!=strtolower($_SESSION['code']) )
	{
		$ErrMsg="验证码不正确";
		$founer="false";
	} */

/* 	if(!empty($_SESSION['isreg']))
	{
		if(time()-$_SESSION['isreg']<300)
		{
			$ErrMsg="五分钟内请不要重复注册";
			$founer="false";

		}
	} */

	$popid=intval(getcookie("popuid"));



	if ($founer=="true")
	{
		$UserIp=getIp();
		$row = DB::fetch_first ( "select * from " . DB::table ( 'users' ) . " where UserName='" . $uname . "'" );
		if (! is_array ( $row )) {
				
				
			$userlogins=1;
			$userface="/images/defauface.png";
			$feedarr = array (
					'username' => $uname,
					'userpassword' => md5($urpwd),
					'userface' => $userface,
					'jobdate' => time(),
					'lastlogin' => time(),
					'userlogins' => $userlogins,
					'realname' => '',
					/* 'province' => $province,
					'area' => $area,
					'city' => $city,
					'address' => $uaddress,
					'usermobile' => $umob, */
					'usertel' => '',
					'userpost' => '',
					'usersign' => '',
					'usersex' => '',
					'popid' => $popid,
					'userlastip' => $UserIp

			);
			$reuserid=DB::insert ( 'users', $feedarr,true );

				
			if($reuserid>0)
			{

				$_SESSION['isreg']=time();
					
				cookie ("username", $uname , time()+3600*24*30,"/");
				cookie ("userid", $reuserid , time()+3600*24*30,"/");
				writeilogs('注册','注册网站会员',sys_regjf,$uname);
				//sendmaillink($uemail);
			}else{
				$ErrMsg="系统繁忙，请稍后重试";
				$founer="false";
			}


		}else{
			$ErrMsg="用户或邮箱已存在";
			$founer="false";


		}
			
			
	}


	if ($founer=="true")
	{
		echo "1|sw|".$ErrMsg;
	}else {
		echo "0|sw|".$ErrMsg;
	}


}

 
function login()
{
	 
	$urpwd=F("pstr");
	$uname=safe_replace(unescape(F("ustr")));
	$code=F("ucode");
	$ErrMsg="";
	$founer="true";
	if (strlen($uname)<2  )
	{
		$ErrMsg="请输入您的登录用户名或邮箱";
		$founer="false";
	}

	/* if (strlen($code)!=4  )
	{
		$ErrMsg="请输入登录验证码";
		$founer="false";
	}

	if (strtolower($code)!=strtolower($_SESSION['code']) )
	{
		$ErrMsg="验证码不正确";
		$founer="false";
	} */
 
	if ($founer=="true")
	{
       
		$UserIp=getIp();
		if(strpos($uname,"@")>0 && strpos($uname,".")>0)
		{
		$row = DB::fetch_first ( "select userid,userlogins,lockuser,username from " . DB::table ( 'users' ) . " where useremail='" . $uname . "' and isemail=1 and userpassword='" . md5($urpwd) . "'" );
		}
		else {			
		$row = DB::fetch_first ( "select userid,userlogins,lockuser,username from " . DB::table ( 'users' ) . " where UserName='" . $uname . "'  and userpassword='" . md5($urpwd) . "'" );	
		}
 
		if ( is_array ( $row )) {
				
			if($row["lockuser"]==1)
			{
				$ErrMsg="登录账号被禁止登录，请与客服联系！";
				$founer="false";				
				
			}
		    else
			{	
				$feedarr = array (
						'userlogins' => $row["userlogins"]+1,
						'lastlogin' => time(),
						'userlastip' => $UserIp
						 
				);
				
				DB::update ( 'users', $feedarr, array (
						'userid' => $row["userid"] 
				) );
				$ErrMsg="登录成功";
				$founer="true";				
	
				
			cookie ("username", $row["username"] , time()+3600*24*30,"/");
			cookie ("userid",$row["userid"] , time()+3600*24*30,"/");
			} 
	
		}else{
			$ErrMsg="账号或密码错误";
			$founer="false";
	
	
		}
			
			
	}
	
	
	if ($founer=="true")
	{
		echo "1|sw|".$ErrMsg;
	}else {
		echo "0|sw|".$ErrMsg;
	}
	
}


function appLogin(){
	$urpwd=F("pstr");
	$uname=safe_replace(unescape(F("ustr")));
	$ErrMsg="";
	$founer="true";
	if ($founer=="true"){ 
		$UserIp=getIp();
		if(strpos($uname,"@")>0 && strpos($uname,".")>0){
			$row = DB::fetch_first ( "select userid,userlogins,lockuser,username from " . DB::table ( 'users' ) . " where useremail='" . $uname . "' and isemail=1 and userpassword='" . md5($urpwd) . "'" );
		}
		else {
			$row = DB::fetch_first ( "select userid,userlogins,lockuser,username from " . DB::table ( 'users' ) . " where UserName='" . $uname . "'  and userpassword='" . md5($urpwd) . "'" );
		}
		if ( is_array ( $row )) {
			if($row["lockuser"]==1){
				$ErrMsg="登录账号被禁止登录，请与客服联系！";
				$founer="false";
			}
			else{
				$feedarr = array (
						'userlogins' => $row["userlogins"]+1,
						'lastlogin' => time(),
						'userlastip' => $UserIp
				);
				DB::update ( 'users', $feedarr, array (
				'userid' => $row["userid"]
				) );
				$ErrMsg="登录成功";
				$founer="true";
				cookie ("username", $row["username"] , time()+3600*24*30,"/");
				cookie ("userid",$row["userid"] , time()+3600*24*30,"/");
			}
		}else{
			$ErrMsg="账号或密码错误";
			$founer="false";
		}	
	}
	if ($founer=="true"){
		echo "1|sw|".$ErrMsg;
	}else {
		echo "0|sw|".$ErrMsg;
	}
}


function appAlterPwd(){
	global $ymy_UserId;
	$oldPwd = F("op");
	$newPwd = F("np");
	$rPwd = F("rp");
	$Msg="";
	$sql = "SELECT userid FROM " . DB::table ( 'users' ) . " WHERE userid=".$ymy_UserId." and userpassword='".md5($oldPwd)."'";
	$rows = DB::fetch_first ( $sql );
	if (!is_array ( $rows )) {
		$Msg="修改失败,您输入的原始密码不正确";
		$founer="false";
	}else{
		$feedarr = array ('userpassword' => md5($newPwd));
		DB::update ( 'users', $feedarr, array ('userid' => $ymy_UserId ) );
		$Msg="恭喜您，密码修改成功";
	}
	if ($founer=="true"){
		echo "1|sw|".$Msg;
	}else {
		echo "0|sw|".$Msg;
	}
}


function CardAdd()
{
	
	$updated=0;
     $ErrMsg="";
	 $pid=intval(F("pid"));
	 $pcount=intval(F("pcount"));
	 $attribute=intval(F("attributeid"));
	 
	 if ($pcount==0) $pcount=1;
	 if ($pid==0) exit();
	 $rows = DB::fetch_first ( "SELECT id,price,nums,soldnum,Limitnum,isLimit FROM " . DB::table ( 'Product' ) . " WHERE checked=1  and state=1 and id=$pid" );
	 if ( is_array ( $rows )) {
			$buylist=getcookie("shopcard");
			$lastnum=$rows["nums"];
			$Limitnum=$rows["Limitnum"];
			$isLimit=$rows["isLimit"];
			
			if ($lastnum<1) 
			{
			 echo "0|sw|货品已卖光，正在备货!";
			 exit();
			}

			if ($isLimit==1&&$pcount>$Limitnum) 
			{
			 echo "0|sw|此商品限购".$Limitnum."件!";
			 exit();
			}
			
			if ($pcount>$lastnum)   $pcount=$lastnum;
			if (empty($buylist))
			{ 
 				cookie ("shopcard", $pid."@@@".$pcount."@@@".$attribute."@@@0" , time()+3600*24*30,"/");
				//echo $pid."@@@".$pcount;
			}
			else
			{
				$shopcard="";
				$arrcard=explode('$$$',$buylist);
				foreach ($arrcard as $key => $value){
				   $items=explode('@@@',$value);
					if ($pid==intval($items[0])&&$attribute==intval($items[2])) {
						if ($lastnum >intval($items[1])) {
						 
						  if($isLimit==1&&(intval($items[1])+1)>$Limitnum) 
						  {
						     echo "0|sw|此商品限购".$Limitnum."件!";
							 exit();
						   $shopcard.="$$$".$items[0]."@@@".($items[1])."@@@".($items[2])."@@@".($items[3]);
						   }else{
						   $shopcard.="$$$".$items[0]."@@@".($items[1]+1)."@@@".($items[2])."@@@".($items[3]);
						   }
						 
						 }
						 else
						 {
						  $shopcard.="$$$".$items[0]."@@@".$items[1]."@@@".($items[2])."@@@".($items[3]);
						}
						$updated=1;
						}
					else
					{
						$shopcard.="$$$".$items[0]."@@@".$items[1]."@@@".($items[2])."@@@".($items[3]);
					}
				}
				if ($updated==0) $shopcard=$pid."@@@".$pcount."@@@".$attribute."@@@0".$shopcard;
				if (stripos($shopcard,"$$$")=="0"&&stripos($shopcard,"$$$")!== false) $shopcard = substr($shopcard,3);
 				cookie ("shopcard", $shopcard , time()+3600*24*30,"/");		
			 }
			//echo $shopcard; 
	 	 echo "1|sw|加入成功";
	}
}


function UpCash()	
{

	$updated=0;
	$totalprice=0;
    
	 $pid=intval(F("pid"));
	$pcount=intval(F("pc"));
	$attid=intval(F("attid"));
	
	 if ($pid==0) {
		echo "1|sw|参数错误";
		exit();
	 }
	 
	 $buylist=getcookie("shopcard");

	$rows = DB::fetch_first ( "SELECT id,price,nums,soldnum,Limitnum,isLimit FROM " . DB::table ( 'Product' ) . " WHERE checked=1  and state=1 and id=$pid" );

	 if ( !is_array ( $rows )) {
	     echo "1|sw|参数错误";
		exit();
	 }else{
	 
	 		$lastnum=$rows["nums"];
			if ($pcount>$lastnum)   $pcount=$lastnum;

			$Limitnum=$rows["Limitnum"];
			$isLimit=$rows["isLimit"];
			
			if($isLimit==1&&$pcount>$Limitnum) {
			
				  echo "1|sw|此商品限购".$Limitnum."件!";
		          exit();
			}		
	 
	 } 

 
	 		
	 
	if (empty($buylist))
	{ 
		echo "1|sw|0";
		exit();
		}
	else
	{
		$shopcard="";
		$arrcard=explode('$$$',$buylist);
		foreach ($arrcard as $key => $value){
			$items=explode('@@@',$value);
			if ($pid==intval($items[0])&&$attid==intval($items[2]))
			{ 
				if ($pcount==0) 
				{ 
				 $shopcard=$shopcard;
				}else
				{
				$shopcard=$shopcard."$$$".$pid."@@@".$pcount."@@@".$items[2]."@@@".$items[3];
				}
			}	
			else
			{
				$shopcard=$shopcard."$$$".$items[0]."@@@".$items[1]."@@@".$items[2]."@@@".$items[3];
			}
		}
		
		if (stripos($shopcard,"$$$")=="0"&&stripos($shopcard,"$$$")!== false) $shopcard = substr($shopcard,3);
		 cookie ("shopcard", $shopcard , time()+3600*24*30,"/");		
		 echo "0|sw|";	
	 }

}

function delReceipt()
{
	global $ymy_UserName; 
	$ReceiptId=intval(F("ReceiptId"));
	
	db::query ( "delete FROM ymy_myaddress WHERE username='".$ymy_UserName."' and id =$ReceiptId" );
}

function addresssellist()
{
		$province=intval(F("province"));
		$city=intval(F("city"));
		$area=intval(F("area"));
		
		$restr="";
		$restrp="";
		$wstrsql='';
	    if($province>0)
		{
			$wstrsql.=" and provinceid=$province";
		}

	    if($city>0)
		{
			$wstrsql.=" and cityid=$city";
		}
		

		/*查询上次是否购买,如果购买取出地址start*/
		$default_sql="SELECT networkid FROM	".DB::table('order')." WHERE	username ='".$_COOKIE['username']."' order by okpaydate desc";
		//echo $default_sql;exit;
		
		$default_row=DB::fetch_first($default_sql);
		$last_networkid=$default_row['networkid'];
		//echo $default_row['networkid'];exit;
		
		if($default_row){	//如果购买过
			$default_sql2="select * from ".DB::table('network')." where number='".$default_row['networkid']."'";
			$default_row2=DB::fetch_first($default_sql2);
			$last_pointid=$default_row2['pointid'];			
		
			/* $default_sql3="select * from ".DB::table('point')." where pointid='".$default_row2['pointid']."' and father='".$default_row2['areaid']."'";
			//echo $default_sql3;exit;
			$default_row3=DB::fetch_first($default_sql3);
			if($default_row3){
				$last_pointid=$default_row3['pointid'];	
			} */
		}
		
		
		/*查询上次是否购买,如果购买取出地址end*/
		
		
		

	    if($area>0)
		{
			$wstrsql.=" and areaid=$area";

			$queryp = DB::query ( "select pointid,point from " . DB::table ( 'point ' ) . " where father='".$area."'  order by id " );
 		   while ( $rowp = DB::fetch ( $queryp ) ) {
 		   	/*上次的门店被选中*/
 		   	if($rowp['pointid']==$last_pointid){
 		   		$string_selected="selected";
 		   	}else{
 		   		$string_selected="";
 		   	}
 		   	
			 $restrp.="<option value=\"".$rowp["pointid"]."\" ".$string_selected.">".$rowp["point"]."</option>";
			}
			
		}					

		$query = DB::query ( "select id,title,address,master,mobile,phone,number,pointid from " . DB::table ( 'network' ) . " where checked=1 ".$wstrsql." order by id asc" );		
 	   while ( $row = DB::fetch ( $query ) ) {
	   	   if($row["mobile"]==""){
	   	   	   if($row["phone"] != ""){
	   	   	   	   $row["mobile"]=$row["phone"];
	   	   	   }
	   	   }
	   	   /*上次的网点地址被选中*/
	   	   if($row['number']==$last_networkid){
	   	   		$string_selected="selected";
 		   	}else{
 		   		$string_selected="";
 		   	}
	   	   
	       $restr.="<option value=\"".$row["number"]."\" pointid=\"".$row["pointid"]."\" ".$string_selected." >门店".$row["number"]."名称：".$row["title"]." 门店地址：".$row["address"]." 负责人：".$row["master"]." 手机：".$row["mobile"]."</option>";
	   
	   }
	   
	   echo "1|sw|".$restr."|sw|".$restrp;
		
}

function commentinfo()
{
	$proid=intval(F("proid"));

	$rows = DB::fetch_first ( "SELECT count(comment) as a,sum(comment) as b FROM " . DB::table ( 'comment' ) . " WHERE proid=$proid" );
	if (  is_array ( $rows )) {
		 echo $rows["a"]."|sw|".floor($rows["b"]/$rows["a"]);
	} else{
	   echo "0|sw|0";
	}
}

function mobilecode(){
	global $ymy_UserId;
	$founer = true;
	
	$mobile = I(F("mobile"));
	$code = I(F("code"));
	$re = '/^1[3|4|5|8][0-9]\d{8}$/i';
	$Msg="";
	
	$sql = "select mobile,code,sendtime,valid,sendip from ".DB::table("mobile_verify")." where mobile=".$mobile." order by valid desc";
	$row = DB::fetch_first($sql);
	if($code!=$row['code']){
		$Msg = "验证码错误";
		$founer = false;
	}else{
		$mobile_sql = 'SELECT userid FROM '.DB::table('users').' WHERE usermobile=\''.$mobile.'\' and mobile_verify=1';
		$member = DB::fetch_first($mobile_sql);
		if(!empty($member)){
			$Msg = "该手机号码已被用户绑定";
			$founer = false;
		}else{
			$user_mobile = array(
					"mobile_verify" => 1,
					"usermobile" => $mobile
			);
			$user_where = array(
					"userid" => $ymy_UserId
			);
			DB::update("users", $user_mobile,$user_where);
		}
	}
	if ($founer=="true"){
		echo "1|sw|".$Msg;
	}else {
		echo "0|sw|".$Msg;
	}
}

function sendcode(){	
	$mobile = F("mobile");
	$founer = true;
	$ErrMsg = "";
	$sendip = getIp();
	$limit_time = YMY_NOW_TIME - 200;
	
	
// 	$mv_ip_sql = 'SELECT mobile,code,sendtime,valid FROM '.DB::table('mobile_verify').' WHERE sendip=\''.$sendip.'\' and sendtime>'.$limit_time.' ORDER BY id desc';
// 	$mv_ip = DB::fetch_first($mv_ip_sql);
	$mv_sql = 'SELECT mobile,code,sendtime,valid FROM '.DB::table('mobile_verify').' WHERE mobile='.$mobile.' and sendtime>'.$limit_time.' ORDER BY id desc';
	$mv = DB::fetch_first($mv_sql);
	
	$mobile_sql = 'SELECT userid FROM '.DB::table('users').' WHERE usermobile=\''.$mobile.'\'  and mobile_verify=1';
	$member = DB::fetch_first($mobile_sql);
	if(!empty($member)){
		$ErrMsg = "该手机号码已被用户绑定";
		$founer = false;
	}else{
// 		if(!empty($mv_ip)){
// 			$left = YMY_NOW_TIME - $mv_ip['sendtime'];
// 			$left = 200 - $left;
// 			$ErrMsg = "该手机200秒内已发送过验证码，请稍后再发送";
// 			$founer = false;
// 		}else 
		if(!empty($mv)){
			$left = YMY_NOW_TIME - $mv['sendtime'];
			$left = 200 - $left;
			$ErrMsg = "该手机200秒内已发送过验证码，请稍后再发送";
			$founer = false;
		}else{
			$code = rand(100000, 999999);
			$sendtime = YMY_NOW_TIME;
			$valid = $sendtime + 600;
			$data = array(
					'mobile' => $mobile,
					'code' => $code,
					'sendtime' => $sendtime,
					'valid' => $valid,
					'sendip' => $sendip
			);
			$insert_id = DB::insert('mobile_verify', $data, true);
			if(!$insert_id){
				$ErrMsg = '系统发生未知错误，请重新发送';
				$founer = false;
			}else{
				$sendrst = sendSmsApi($mobile, '您的验证码为'.$code);
				if(is_array($sendrst) && isset($sendrst['result']) && ($sendrst['result'] < 1)){
					$ErrMsg = '短信发送成功，请查收';
					$founer = true;
				}
				else{
					$ErrMsg = '短信发送失败，请重新发送';
					// 删除入库内容
					DB::delete('mobile_verify', array('id'=>$insert_id), 1);
					$founer = false;
				}
			}
		}
	}
	
	 if ($founer==true){
		echo "1|sw|".$ErrMsg;
	}else {
		echo "0|sw|".$ErrMsg;
	} 
}



function sendclassNums(){
	$classNums = F("classNums");
	
}




?>