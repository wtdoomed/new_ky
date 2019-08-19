<?php
namespace app\mapi\controller;
use think\Db;

//测试控制器
class CeshiController extends BaseController {
	public function ceshi777(){
		die;
//		$AdExpenditureRecord = M('ArticleReadRecord');
//		$where['create_time'] = array('lt',1547913600);
//		$res = $AdExpenditureRecord->where($where)->delete();
//		echo $res;
//		die;
//		$where['create_time'] = array('egt',1542384000);
//		$where['cid'] = 1;
//		$UserMoneyRecord = M('UserMoneyRecord');
//		$res = $UserMoneyRecord->field('uid')->where($where)->group('uid')->select();
//		$c = count($res);
//		for($i=0;$i<$c;$i++){
//			$str .= $res[$i]['uid'].',';
//		}
//echo $str;die;
//		$ArticleReadRecord = M('ArticleChargingRecord');
//		$start_time = 1541952000;
//		$etime = 1542038400;
//		$where['create_time'] = array(array('egt',$start_time),array('lt',$etime));
//		$res = $ArticleReadRecord->field('count(distinct(user_ip)) as num')->where($where)->select();
//		echo '<pre>';
//		var_dump($res);
//		die;
//		
//		$ArticleReadRecord = M('AdExpenditureRecord');
//		$start_time = 1542268800;
//		$where['create_time'] = array('egt',$start_time);
//		$res = $ArticleReadRecord->field('count(id) as num,uid')->where($where)->group('uid')->order('count(id) desc')->select();
//		echo '<pre>';
//		var_dump($res);
//		die;
		
//		die;
//		$endtime = strtotime(date('Ymd', time()));//今天开始时间戳
//		$where['create_time'] = array('egt',$endtime);
		$ArticleReadRecord = M('ArticleReadRecord');
		$start_time = 1545840000;
		$etime = 1545926400;
		$where['create_time'] = array(array('egt',$start_time),array('lt',$etime));
		$res = $ArticleReadRecord->field('count(distinct(user_ip)) as num')->where($where)->select();
		echo '<pre>';
		var_dump($res);
		die;
//		$times = 1527782400;
//		$AdExpenditureRecord = M('AdExpenditureRecord');
//		$where['create_time'] = array();
		die;
		$where1['start_money'] = array('elt',10);
    		$where1['uid'] = 2;
    		$where1['type'] = 1;
    		$where1['status'] = 1;
    		$UserDeduction = M('UserDeduction');
    		$res1 = $UserDeduction->field('deduction_pre,start_money')->where($where1)->order('end_money desc')->find();
    		echo '<pre>';
    		var_dump($res1);
		die;
//		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
//		$endtime = $starttime + 86400;
//		$where['create_time'] = array(array('egt',$starttime),array('lt',$endtime));
//		
//		$UserMoneyRecord = M('UserMoneyRecord');
//		$where['cid'] = 1;
//		$res = $UserMoneyRecord->field('sum(money) as money,uid')->where($where)->group('uid')->order('sum(money) desc')->select();
//		$c = count($res);
//		for($i=0;$i<$c;$i++){
//			if($res[$i]['money'] <= 50){
//				$a += 1;
//			}
//		}
//		echo '<pre>';
//		var_dump($a);
//		die;
//		$ArticleChargingRecord = M('ArticleChargingRecord');
//		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
//		$endtime = $starttime + 86400;
//		$UserMoneyRecord = M('UserMoneyRecord');
//		$where1['create_time'] = array(array('egt',$starttime),array('lt',$endtime));
//		$where1['cid'] = 1;
//		$res = $UserMoneyRecord->field('sum(money) as money,uid')->where($where1)->group('uid')->select();
//		$c = count($res);
//		for($i=0;$i<$c;$i++){
//			if($res[$i]['money'] < 100){
//				$a1 += 1;
//				continue;
//			}elseif($res[$i]['money'] >= 500 && $res[$i]['money'] < 1000){
//				$b1 += 1;
//				continue;
//			}elseif($res[$i]['money'] >= 1000){
//				$c1 += 1;
//				continue;
//			}
//		}
//		echo '<hr/>';
//		echo '小于5元：  '.$a1.'<br/>';
//		echo '5-9.9元：  '.$b1.'<br/>';
//		echo '大于9.9元：'.$c1.'<br/>';
//		die;	
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$endtime = $starttime + 86400;
		$where['a.create_time'] = array(array('egt',$starttime),array('lt',$endtime));
		$User = M('User');
		$arr = $User->alias('a')
			->field('a.id,a.cash_openid,b.total_money,b.total_gold')
			->join('__USER_ACCOUNT__ b ON a.id = b.uid','LEFT')
			->where($where)
			->select();
		$c = count($arr);
		for($i=0;$i<$c;$i++){
			if($arr[$i]['total_money'] < 50){
				$a += 1;
			}
			
			if($arr[$i]['total_gold'] > 55){
				$b += 1;
			}
			
			if($arr[$i]['cash_openid']){
				$c1 += 1;
			}
			
			$str .= $arr[$i]['id'].',';
		}
		
//		$Welfarerecord = M('Welfarerecord');
//		$where1['uid'] = array('in',rtrim($str,','));
//		$where1['wid'] = 3;
//		$res2 = $Welfarerecord->where($where1)->count();
		echo '<pre>';
		var_dump($a);
		var_dump($b);
		var_dump($c1);
//		var_dump($res2);
		die;
		
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$endtime = $starttime + 86400;
		$UserGoldRecord = M('UserGoldRecord');
		$where['create_time'] = array('egt',$endtime);
		$where['cid'] = array('neq','-1');
		$res = $UserGoldRecord->field('sum(gold) as gold')->where($where)->select();
		echo '<pre>';
		var_dump($res);
		die;
		//---------------------昨日获得金币排名-----------------------
//		$UserGoldRecord = M('UserGoldRecord');
//		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
//		$endtime = strtotime(date('Ymd', time()));//今天开始时间戳
//		$where['create_time'] = array(array('egt',$starttime),array('lt',$endtime));
//		$where['cid'] = array('gt',0);
//		$res = $UserGoldRecord->field('sum(gold) as gold,uid')->where($where)->group('uid')->order('sum(gold) desc')->select();
//		echo '<pre>';
//		var_dump($res);
//		die;
		//---------------------昨日获得金币排名-----------------------

		//---------------------检测短信唤醒数据-----------------------
//		$UserSmsSend = M('UserSmsSend');
//		$where['id'] = 3;
//		$res = $UserSmsSend->field('phones,create_time')->where($where)->select();
//		
//		$where1['a.phone'] = array('in',$res[0]['phones']);
//		$User = M('User');
//		$arr = $User->alias('a')
//			->field('a.id,b.create_time,c.money')
//			->join('__USER_ACTIVE_RECORD__ b ON a.id = b.uid','LEFT')
//			->join('__USER_WITHDRAW__ c ON a.id = c.uid and c.money = 100','LEFT')
//			->where($where1)
//			->select();
//		
//		$c = count($arr);
//		$s = 0;
//		$s_str = '';
//		$s1 = 0;
//		for($i=0;$i<$c;$i++){
//			if($arr[$i]['create_time'] >= $res[0]['create_time']){
//				$s += 1;
//				$s_str .= $arr[$i]['id'].'--'.$arr[$i]['create_time'].'<br/>';
//			}
//			if($arr[$i]['money'] > 0){
//				$s1 += 1;
//			}
//		}
//		echo '<pre>';
//		var_dump($s);
//		var_dump($s1);
//		var_dump($s_str);
//		die;
		//---------------------检测短信唤醒数据-----------------------
		
//		$sql = 'SELECT id,title,cid, COUNT(title) AS count FROM `kyd_article`WHERE status=1 GROUP BY title ORDER BY COUNT(title) DESC';
//		$arr = M()->query($sql);
////		echo '<pre>';
////		var_dump($arr);
////		die;
//		$c = count($arr);
//		$Article = M('Article');
//		for($i=0;$i<$c;$i++){
//			if($arr[$i]['count'] > 1){
//				$where['title'] = $arr[$i]['title'];
//				$where['id'] = array('neq',$arr[$i]['id']);
//				$data['status'] = 3;
//				$Article->where($where)->save($data);
//			}
//		}


		//------------------------会员短信营销------------------------------------
//		$starttime = strtotime(date("Y-m-d",strtotime("-3 day")));//前3天开始时间戳
//		$endtime = strtotime(date('Ymd', time()));//今天开始时间戳
//		//三天前不活跃未提现正常用户
//		$UserActiveRecord = M('UserActiveRecord');
//		$where['a.create_time'] = array('lt',$starttime);
//		$where['b.status'] = array('neq',4);
//		$where['b.is_withdraw'] = 1;
//		$where['b.phone'] = array('gt',1);
//		$res = $UserActiveRecord->alias('a')
//					->field('a.uid,a.create_time,b.phone,c.money,d.total_money')
//					->join('__USER__ b ON a.uid = b.id','LEFT')
//					->join('__USER_WITHDRAW__ c ON a.uid = c.uid and c.status = 1','LEFT')
//					->join('__USER_ACCOUNT__ d ON a.uid = d.uid','LEFT')
//					->where($where)
//					->select();
//
//		$c = count($res);	
		//三天前不活跃未提现正常用户		
//		for($i=0;$i<$c;$i++){
//			if($res[$i]['money'] > 1){
//				unset($res[$i]);
//			}
//		}
//		
//		$fsdata = array_values($res);
//		$phone = '';
//		for($i=0;$i<2000;$i++){
//			$phone .= $fsdata[$i]['phone'].',';
//		}
//		
//		$phones = rtrim($phone,',');
//		
//		$udata['phones'] = $phones;
//		$udata['type'] = 1;
//		$udata['create_time'] = time();
//		$trues = M('UserSmsSend')->add($udata);
//		if($trues){
//			A('Chuanglansend')->FasongVip($phones.',17301394325,13716817606',1);
//		}
//		echo '<pre>';
//		var_dump($trues);
//		die;
		
		//三天前不活跃已提现总收益大于2正常用户
//		for($i=0;$i<$c;$i++){
//			if($res[$i]['money'] > 1 && $res[$i]['total_money'] > 200){
//			}else{
//				unset($res[$i]);
//			}
//		}
//		$fsdata = array_values($res);
//		$phone = '';
//		for($i=0;$i<2000;$i++){
//			$phone .= $fsdata[$i]['phone'].',';
//		}
//		
//		$phones = rtrim($phone,',');
//		
//		$udata['phones'] = $phones;
//		$udata['type'] = 2;
//		$udata['create_time'] = time();
//		$trues = M('UserSmsSend')->add($udata);
//		if($trues){
//			A('Chuanglansend')->FasongVip($phones.',17301394325,13716817606',1);
//		}
//		echo '<pre>';
//		var_dump($trues);
//		die;
		
		//三天前不活跃已提现1元无收益正常用户
//		for($i=0;$i<$c;$i++){
//			if($res[$i]['money'] > 1 && $res[$i]['total_money'] < 201){
//			}else{
//				unset($res[$i]);
//			}
//		}
//		$fsdata = array_values($res);
//		$phone = '';
//		for($i=0;$i<2000;$i++){
//			$phone .= $fsdata[$i]['phone'].',';
//		}
//		
//		$phones = rtrim($phone,',');
//		
//		$udata['phones'] = $phones;
//		$udata['type'] = 3;
//		$udata['create_time'] = time();
//		$trues = M('UserSmsSend')->add($udata);
//		if($trues){
//			A('Chuanglansend')->FasongVip($phones.',18500411357,13683285116',1);
//		}
//		echo '<pre>';
//		var_dump($trues);
//		die;
		
		//三天内未提现1元正常用户
//		$User = M('User');
//		$where1['a.create_time'] = array(array(array('egt',$starttime),array('lt',$endtime)),array('lt',$endtime));
//		$where1['a.status'] = array('neq',4);
//		$where1['a.is_withdraw'] = 1;
//		$where1['a.phone'] = array('gt',1);
//		$res1 = $User->alias('a')
//					->field('a.id,a.create_time,a.phone,c.money')
//					->join('__USER_WITHDRAW__ c ON a.id = c.uid and c.status = 1','LEFT')
//					->where($where1)
//					->select();
//
//		$c = count($res1);
//		for($i=0;$i<$c;$i++){
//			if($res1[$i]['money'] > 1){
//				unset($res1[$i]);
//			}
//		}
//		$fsdata = array_values($res1);
//		$phone = '';
//		for($i=0;$i<count($fsdata);$i++){
//			$phone .= $fsdata[$i]['phone'].',';
//		}
//		
//		$phones = rtrim($phone,',');
//		
//		$udata['phones'] = $phones;
//		$udata['type'] = 4;
//		$udata['create_time'] = time();
//		$trues = M('UserSmsSend')->add($udata);
//		if($trues){
//			A('Chuanglansend')->FasongVip($phones.',18500411357,13683285116',1);
//		}
//		echo '<pre>';
//		var_dump($trues);
//		die;
		//------------------------会员短信营销------------------------------------
		
		//--------------------------------------聚看点--------------------------------------
//		$url = 'https://www.xiaodouzhuan.cn/jkd/newmobile/artlist.action';
//		$data['cateid'] = "60";
//		$data['optaction'] = "up";
//		$data['page'] = "2";
//		$data['pagesize'] = "12";
//		$data['searchtext'] = "";
//		$data['appid'] = "xzwl";
//		$data['apptoken'] = "xzwltoken070704";
//		$data['appversion'] = "5.6.7";
//		$data['channel'] = "XIAOMI01_CHANNEL";
//		$data['openid'] = "5785cdfe85ee422fa658d6959c954d02";
//		$data['os'] = "android";
//		$data['token'] = "lcyegNMlP5NG4RH1i9xy8Er4ZOKBenbUQLUWc0VsirOBKMhekTeowSDitOjJ3YNN";
//		$arr['jsondata'] = json_encode($data);
//
//		$res = $this->postData($url,$arr,true);
//		echo '<pre>';
//		var_dump(json_decode($res,true));
//		die;
		//--------------------------------------聚看点--------------------------------------
		
		//--------获取内容超过text最大字节的------------
//		$Article = M('Article');
//		$where['cid'] = 42;
//		$res = $Article->field('id,desc')->where($where)->select();
//		$c = count($res);
//		for($i=0;$i<$c;$i++){
//			if(strlen($res[$i]['desc']) > 65535){
//				echo $res[$i]['id'].'-----'.strlen($res[$i]['desc']).'-------超过'.'<br/>';
//			}else{
//				echo '正常'.'<br/>';
//			}
//		}
		//--------获取内容超过text最大字节的------------
		

		//--------------------------修改文章单价--------------------------

//			$Article = M('Article');
//			$where['status'] = 1;
//			$where['type'] = 1;
//			$data['price'] = 10;
//			$res = $Article->where($where)->save($data);
//			清除缓存
//			$keys = $this->redis->keys('WT_AppActicleDetailInfo*');
//	        for ($i = 0; $i < count($keys); $i++) {
//	            $this->redis->set($keys[$i],null);
//	        }
//die;
		//--------------------------修改文章单价--------------------------
		
		
		//-------获取用户使用那个收徒渠道的多----------
//		$DiscipleIpRecord = M('DiscipleIpRecord');
//		$where['a.second_type'] = 6;
//		$res = $DiscipleIpRecord->alias('a')
//					->field('count(a.id) as num,a.uid,b.name,b.user_id,b.status,b.is_withdraw')
//					->join('__USER__ b ON a.uid = b.id','LEFT')
//					->where($where)
//					->group('a.uid')
//					->order('count(a.id) desc')
//					->select();
//		echo '<pre>';
//		var_dump($res);
//		die;
		//-------获取用户使用那个收徒渠道的多----------
		
//		$user_info['id'] = 2;
//		//获取该用户当前有没有正在进行的付款
//      $count = $this->redis->lLen('WT_Appceshi'.$user_info['id']);
//		if($count < 1){
//			$this->redis->rpush('WT_Appceshi'.$user_info['id'],1);//设置正在执行
//		}else{
//			$this->redis->lpop('WT_Appceshi'.$user_info['id']);//清除正在执行
//			$ajaxReturn['code'] = 409;
//      		$ajaxReturn['msg'] = '正在执行，请稍后！';
//      		$this->ajaxReturn($ajaxReturn);
//		}

		//------------------------------获取提现1元每天超过N次的-------------------------------------
//		$UserWithdraw = M('UserWithdraw');
//		$where['a.money'] = 100;
//		$where['a.create_time'] = array('egt',1529251200);
//		$where['b.status'] = array('neq',4);
//		$where['b.is_withdraw'] = 1;
//		$res = $UserWithdraw->alias('a')
//							->field('count(a.id) as num,a.uid,b.name,b.user_id')
//							->where($where)
//							->join('__USER__ b ON a.uid = b.id','LEFT')
//							->group('a.uid')
//							->order('count(a.id) desc')
//							->select();
//		echo '<pre>';
//		var_dump($res);
		//------------------------------获取提现1元每天超过N次的-------------------------------------
		
		
		//---------------------------获取要发送短信的用户---------------------------
//		$User = M('User');
//		$where['a.status'] = array('neq',4);
//		$where['a.phone'] = array('neq','');
//		$where['_string'] = ' (b.total_money > 100)  OR ( b.total_gold > 1050) ';
//		
//		$res = $User->alias('a')
//					->field('a.id,a.phone,a.status,c.create_time,b.total_money')
//					->where($where)
//					->join('__USER_ACCOUNT__ b ON a.id = b.uid','LEFT')
//					->join('__USER_ACTIVE_RECORD__ c ON a.id = c.uid','LEFT')
//					->select();
//					
//		$c = count($res);
//		for($i=0;$i<$c;$i++){
//			if($res[$i]['create_time'] < 1529251200){
//				$data .= $res[$i]['phone'].'<br/>';
//			}
//		}
//		echo '<pre>';
//		var_dump($data);
//		die;
		//---------------------------获取要发送短信的用户---------------------------
		
		//-------------------------------------------包含某个设备的--------------------------------------------------
//		$AdExpenditureRecord = M('AdExpenditureRecord');
//		$where['a.equipment_model'] = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:36.0) Gecko/20100101 Firefox/36.0';
//		$where['b.status'] = array('eq',4);
//		$res = $AdExpenditureRecord->alias('a')->field('a.uid,b.user_id')->where($where)->join('__USER__ b ON a.uid = b.id','LEFT')->group('a.uid')->select();
//		for($i=0;$i<count($res);$i++){
//			$str .= $res[$i]['user_id'].'<br />';
//			$c += 1;
//		}
//		echo '<pre>';
//		var_dump($str);
//		var_dump($c);
//		die;
		//-------------------------------------------包含某个设备的--------------------------------------------------

		//-------------------------------------------获取2个数据的差集-------------------------------------------
//		$UserDeduction = M('UserDeduction');
//		$res = $UserDeduction->field('uid')->group('uid')->select();
//		$c = count($res);
//		for($i=0;$i<$c;$i++){
//			$data1[$i] = $res[$i]['uid'];
//		}
//		
//		$User = M('User');
//		$res1 = $User->field('id')->select();
//		$c1 = count($res1);
//		for($i=0;$i<$c1;$i++){
//			$data[$i] = $res1[$i]['id'];
//		}
//		
//		$aa = array_diff($data1,$data);
//		echo '<pre>';
//		var_dump($aa);
//		die;
		//-------------------------------------------获取2个数据的差集-------------------------------------------
		
		//-------------------------------------------手机号在黑名单的-------------------------------------------
		//		$UserBlack = M('UserBlack');
//		$User = M('User');
//		$uwhere['phone'] = array('neq','');
//		$uwhere['status'] = array('neq',4);
//		$uwhere['is_withdraw'] = 1;
//		$arr1 = $User->field('user_id,phone')->where($uwhere)->select();
//		$arr2 = $UserBlack->field('phone')->select();
//		$c1 = count($arr1);
//		$c2 = count($arr2);
//		$str = '';
//		for($i=0;$i<$c1;$i++){
//			for($j=0;$j<$c2;$j++){
//				if($arr1[$i]['phone'] == $arr2[$j]['phone']){
//					$str .= $arr1[$i]['user_id'].',';
//				}
//			}
//		}
//		
//		echo $str;die;
		//-------------------------------------------手机号在黑名单的-------------------------------------------
		
		
    		//------------------------------------------------网络情况检测--------------------------------------------------------
		//检测硬广是不是全是wifi网络
//		$where10['uid'] = $uid;
//		$where10['create_time'] = array(array('egt',$starttime),array('lt',$endtime));
//		$where10['_string'] = ' equipment_model like "%NetType/2G%" OR equipment_model like "%NetType/3G%" OR equipment_model like "%NetType/4G%" OR equipment_model like "%NetType/WIFI%"';
//		$res10 = $AdExpenditureRecord->field('id')->where($where10)->find();
//		//检测文章是不是全是wifi网络
//		$res11 = $ArticleReadRecord->field('id')->where($where10)->find();
//		if(!$res10 || !$res11){
//			$data[] = '所有点击全是WIFI网络---';
//			$ajaxReturn['code'] = 500;
//  			$ajaxReturn['msg'] = $data;
//  			return $ajaxReturn;
//		}
    		//------------------------------------------------网络情况检测--------------------------------------------------------
    		
		
	}
	
	//导入中青
	protected function GrabContent($url=''){
		//抓取页面数据
    		$data = file_get_contents($url);
    		//内容正则
    		$con_preg = '/<div class="rich_media_content" id="box">[\s\S]+?<div class="subdy_box fn-hides">/';
    		//得到返回的数组数据
    		preg_match($con_preg,$data,$arr);

    		return $arr;
	}
	
	//导入中青
	public function daoruzhongqing(){
		die;
		$data1 = json_decode('{
    "success":true,
    "error_code":"0",
    "message":"获取成功",
    "count":"50",
    "rows":"5",
    "items":[
        {
            "id":"8090777",
            "catid":"2",
            "thumb":"http://res.youth.cn/article_201808_15_15t_5b73cb71904ab.jpg/180x135",
            "account_id":"16642",
            "extra":[

            ],
            "title":"逼出来的坚强，扛出来的独立（精辟）",
            "share_num":"1218",
            "wurl":"https://m.toutiaocdn.cn/a6589774963014631943/?iid=8783147412&app=news_article&is_hit_share_recommend=0",
            "ctype":"0",
            "image_type":"1",
            "op_mark":"",
            "op_mark_iurl":"",
            "op_mark_icolor":"#fff",
            "input_time":"1534314661",
            "tagid":"0",
            "cmt_num":"15",
            "catname":"美文",
            "account_name":"倾听美文",
            "url":"https://kd.youth.cn/n?timestamp=1534391589&signature=5lp8qBWyXjGdwJDgLm1R47klmte97eBvxYQzM2N6onVr9E0kOK&device_type=android&from=top",
            "is_cache":"1",
            "article_type":"0",
            "read_num":"97413",
            "oid":"8090777"
        },
        {
            "id":"8107982",
            "catid":"3",
            "thumb":"http://res.youth.cn/article_201808_15_15w_5b740cbfe1c1a.jpg/180x135",
            "account_id":"16637",
            "extra":[

            ],
            "title":"人老脚先衰！教你腿足保健操，舒筋活络人不老！",
            "share_num":"726",
            "wurl":"https://m.toutiaocdn.cn/a6588850669178847752/?iid=8783147412&app=news_article&is_hit_share_recommend=0",
            "ctype":"0",
            "image_type":"1",
            "op_mark":"",
            "op_mark_iurl":"",
            "op_mark_icolor":"#fff",
            "input_time":"1534331684",
            "tagid":"0",
            "cmt_num":"2",
            "catname":"健康",
            "account_name":"健康与养生",
            "url":"https://kd.youth.cn/n?timestamp=1534391589&signature=dbnADWmNrE79QyJ5woPp9yzA6tYwMYV1zOqjKklBX84xg3V0a2&device_type=android&from=top",
            "is_cache":"1",
            "article_type":"0",
            "read_num":"92386",
            "oid":"8107982"
        },
        {
            "id":"8078255",
            "catid":"8",
            "thumb":"http://res.youth.cn/article_201808_15_15b_5b73a7a93de23.jpg/180x135",
            "account_id":"4435",
            "extra":[

            ],
            "title":"宝宝身上3个部位都是生命线，家长不管有多生气也不能打",
            "share_num":"408",
            "wurl":"https://m.toutiaocdn.cn/a6560587548249293325/?iid=8783147412&app=news_article&is_hit_share_recommend=0",
            "ctype":"0",
            "image_type":"1",
            "op_mark":"",
            "op_mark_iurl":"",
            "op_mark_icolor":"#fff",
            "input_time":"1534306144",
            "tagid":"0",
            "cmt_num":"6",
            "catname":"育儿",
            "account_name":"精彩每日",
            "url":"https://kd.youth.cn/n?timestamp=1534391589&signature=eOEAnwrm8d5Lko7YRgPwzEGxVu47K4rvpBlqzX2M63QbaNK0Wx&device_type=android&from=top",
            "is_cache":"1",
            "article_type":"0",
            "read_num":"86290",
            "oid":"8078255"
        },
        {
            "id":"8091920",
            "catid":"2",
            "thumb":"http://res.youth.cn/article_201808_15_15r_5b73cd8cd569d.jpg/180x135",
            "account_id":"16642",
            "extra":[

            ],
            "title":"左手夫，右手妻，别嫌你的左手，别弃你的右手！（句句入心）",
            "share_num":"1616",
            "wurl":"https://m.toutiaocdn.cn/a6588162168854675975/?iid=8783147412&app=news_article&is_hit_share_recommend=0",
            "ctype":"0",
            "image_type":"1",
            "op_mark":"",
            "op_mark_iurl":"",
            "op_mark_icolor":"#fff",
            "input_time":"1534315577",
            "tagid":"0",
            "cmt_num":"14",
            "catname":"美文",
            "account_name":"倾听美文",
            "url":"https://kd.youth.cn/n?timestamp=1534391589&signature=M4o0Aw5xKl6zBEaXnRZD8MOD9cyMNynZpdmjWQDgY3rey2ON89&device_type=android&from=top",
            "is_cache":"1",
            "article_type":"0",
            "read_num":"56924",
            "oid":"8091920"
        },
        {
            "id":"8117509",
            "catid":"2",
            "thumb":"http://res.youth.cn/article_201808_15_15q_5b7442a87da07.jpg/180x135",
            "account_id":"16641",
            "extra":[

            ],
            "title":"离开位子你是谁——一针见血，值得一看",
            "share_num":"497",
            "wurl":"http://m.toutiao.com/a6589724733309190660/?iid=0&app=news_article&is_hit_share_recommend=1",
            "ctype":"0",
            "image_type":"1",
            "op_mark":"",
            "op_mark_iurl":"",
            "op_mark_icolor":"#fff",
            "input_time":"1534345328",
            "tagid":"0",
            "cmt_num":"15",
            "catname":"美文",
            "account_name":"美文共享",
            "url":"https://kd.youth.cn/n?timestamp=1534391589&signature=DqX03yj8LaYAxdrb6p1V9NAEDuq7jqNPoEMmB7klRQ9zg2eVwG&device_type=android&from=top",
            "is_cache":"1",
            "article_type":"0",
            "read_num":"26043",
            "oid":"8117509"
        },
        {
            "id":"8104371",
            "catid":"2",
            "thumb":"http://res.youth.cn/article_201808_15_156_5b73f76e1f9ac.jpg/180x135",
            "account_id":"16641",
            "extra":[

            ],
            "title":"半个西瓜看人品，真准！",
            "share_num":"890",
            "wurl":"https://m.toutiaocdn.com/a6588106017781842445/?iid=8783147412&app=news_article&is_hit_share_recommend=0",
            "ctype":"0",
            "image_type":"1",
            "op_mark":"",
            "op_mark_iurl":"",
            "op_mark_icolor":"#fff",
            "input_time":"1534326492",
            "tagid":"0",
            "cmt_num":"14",
            "catname":"美文",
            "account_name":"美文共享",
            "url":"https://kd.youth.cn/n?timestamp=1534391589&signature=2Wnkz8yBLAw9mNXq0G1L6044lh5RJ5nvEDjlMYpodaQK53b7J4&device_type=android&from=top",
            "is_cache":"1",
            "article_type":"0",
            "read_num":"42445",
            "oid":"8104371"
        },
        {
            "id":"8077677",
            "catid":"2",
            "thumb":"http://res.youth.cn/article_201808_15_151_5b73a32612799.jpg/180x135",
            "account_id":"16642",
            "extra":[

            ],
            "title":"珍惜每一天吧，人生不能重来，也没有所谓的下辈子",
            "share_num":"1429",
            "wurl":"https://m.toutiaocdn.com/a6565996238146109966/?iid=8783147412&app=news_article&is_hit_share_recommend=0",
            "ctype":"0",
            "image_type":"1",
            "op_mark":"",
            "op_mark_iurl":"",
            "op_mark_icolor":"#fff",
            "input_time":"1534304801",
            "tagid":"0",
            "cmt_num":"26",
            "catname":"美文",
            "account_name":"倾听美文",
            "url":"https://kd.youth.cn/n?timestamp=1534391589&signature=5lp8qBWyXjGdwJDgLm1R47Kaefe97eBvxYQzM2N6onVr9E0kOK&device_type=android&from=top",
            "is_cache":"1",
            "article_type":"0",
            "read_num":"90186",
            "oid":"8077677"
        },
        {
            "id":"7549781",
            "catid":"2",
            "thumb":"http://res.youth.cn/article_201808_01_01u_5b6174bab7132.jpg/180x135",
            "account_id":"16642",
            "extra":[

            ],
            "title":"做人，别太善良！（送给天下没心眼的人）",
            "share_num":"4203",
            "wurl":"https://m.toutiaocdn.cn/a6570255008812499470/?iid=8783147412&app=news_article&is_hit_share_recommend=0",
            "ctype":"0",
            "image_type":"1",
            "op_mark":"",
            "op_mark_iurl":"",
            "op_mark_icolor":"#fff",
            "input_time":"1534321850",
            "tagid":"0",
            "cmt_num":"77",
            "catname":"美文",
            "account_name":"倾听美文",
            "url":"https://kd.youth.cn/n?timestamp=1534391589&signature=yAa9wR7YlKmo0Mq6LVvlakGeAfNLjNBPO4pGxDXdjnr5WNkeQB&device_type=android&from=top",
            "is_cache":"1",
            "article_type":"0",
            "read_num":"94823",
            "oid":"7549781"
        },
        {
            "id":"8090921",
            "catid":"8",
            "thumb":"http://res.youth.cn/article_201808_15_15x_5b73cd4ade821.jpg/180x135",
            "account_id":"6018",
            "extra":[

            ],
            "title":"今后对孩子好点吧，看完真难受！",
            "share_num":"207",
            "wurl":"https://m.toutiaocdn.com/a6588508928223478275/?iid=8783147412&app=news_article&is_hit_share_recommend=0",
            "ctype":"0",
            "image_type":"1",
            "op_mark":"",
            "op_mark_iurl":"",
            "op_mark_icolor":"#fff",
            "input_time":"1534315700",
            "tagid":"0",
            "cmt_num":"6",
            "catname":"育儿",
            "account_name":"宝宝成长树",
            "url":"https://kd.youth.cn/n?timestamp=1534391589&signature=rzOqw7m0y9YWX6kGodPMKqm5XiExRE6ZlJ2MAE5b3x4K8NLgDV&device_type=android&from=top",
            "is_cache":"1",
            "article_type":"0",
            "read_num":"76119",
            "oid":"8090921"
        },
        {
            "id":"8120387",
            "catid":"2",
            "thumb":"http://res.youth.cn/article_201808_16_161_5b74ae249e193.jpg/180x135",
            "account_id":"16642",
            "extra":[

            ],
            "title":"人的一生永远不要弄破两样东西：信任和真情",
            "share_num":"416",
            "wurl":"https://m.toutiaocdn.com/a6589864752166470151/?iid=8783147412&app=news_article&is_hit_share_recommend=0",
            "ctype":"0",
            "image_type":"1",
            "op_mark":"",
            "op_mark_iurl":"",
            "op_mark_icolor":"#fff",
            "input_time":"1534373130",
            "tagid":"0",
            "cmt_num":"7",
            "catname":"美文",
            "account_name":"倾听美文",
            "url":"https://kd.youth.cn/n?timestamp=1534391589&signature=LY0AEgK294Jm5a8zdyZYadmzecXYzX9ZMOo7VWQGNXB6rnkqDR&device_type=android&from=top",
            "is_cache":"1",
            "article_type":"0",
            "read_num":"101989",
            "oid":"8120387"
        }
    ]
}',true);
	$data = $data1['items'];

	$c = count($data);
	for($i=0;$i<$c;$i++){
		$list[$i]['title'] = $data[$i]['title'];
    		$list[$i]['cid'] = 42;
    		
    		if($data[$i]['image_type'] == 2){
    			if(count($data[$i]['extra']) > 2){
	    			$list[$i]['pic_type'] = 3;
	    			$list[$i]['litpic1'] = $data[$i]['extra'][0];
	    			$list[$i]['litpic2'] = $data[$i]['extra'][1];
	    			$list[$i]['litpic3'] = $data[$i]['extra'][2];
	    		}else{
	    			$list[$i]['pic_type'] = 2;
	    			$list[$i]['litpic1'] = $data[$i]['extra'][0];
	    			$list[$i]['litpic2'] = '';
	    			$list[$i]['litpic3'] = '';
	    		}
    		}else{
    			$list[$i]['pic_type'] = 2;
    			$list[$i]['litpic1'] = $data[$i]['thumb'];
    			$list[$i]['litpic2'] = '';
    			$list[$i]['litpic3'] = '';
    		}
    		
		
		//获取页面内容
		$arr = $this->GrabContent($data[$i]['url']);
		if($arr){
				$desc = str_replace('<div class="subdy_box fn-hides">',"",$arr[0]);
				$desc = str_replace('data-src','src',$desc);
		}else{
			$desc = '';
		}
		
    		$list[$i]['desc'] = $desc;
    		$list[$i]['price'] = 10;
    		$list[$i]['status'] = 1;
    		$list[$i]['type'] = 1;
    		$list[$i]['visitnum'] = rand(7000,50000);
    		$list[$i]['sharenum'] = rand(7000,50000);
    		$list[$i]['publish_time'] = time();
    		$list[$i]['create_time'] = time();
	}
  		M('Article')->addAll($list);
	}
	
	public function caa(){
die;
		//---------------------设置扣量规则---------------------
			
			$UserDeduction = M('UserDeduction');
			
//			$where['type'] = 2;
//			$where['start_money'] = 0;
//			$data['deduction_pre'] = 0.5;
//			$UserDeduction->where($where)->save($data);
//			
//			$where['type'] = 2;
//			$where['start_money'] = 1000;
//			$data['deduction_pre'] = 0.6;
//			$UserDeduction->where($where)->save($data);
			
			$where['type'] = 1;
			$where['end_money'] = 500;
			$data['deduction_pre'] = 2;
			$UserDeduction->where($where)->save($data);
			
//			$where['type'] = 1;
//			$where['start_money'] = 500;
//			$where['deduction_pre'] = array('lt',4);
//			$data['deduction_pre'] = 2;
//			$UserDeduction->where($where)->save($data);
//			
//			$where['type'] = 1;
//			$where['start_money'] = 1500;
//			$where['deduction_pre'] = array('lt',3);
//			$data['deduction_pre'] = 1;
//			$UserDeduction->where($where)->save($data);
		//---------------------设置扣量规则---------------------
	}
	
	public function ceshi666(){
		/*载入excel读取类*/
//  		Vendor('PHPExcel.PHPExcel.IOFactory');
//		$filename = '/home/wwwroot/kuaiyue/Public/admin/uploads/ceshiphone.xls';//excel文件在项目里的路径
//      $type = 'Excel5';//设置为Excel5代表支持2003或以下版本
////Excel2007
////      $type = 'Excel5';//设置为Excel5代表支持2003或以下版本
//
//      $xlsReader = \PHPExcel_IOFactory::createReader($type);
//      $xlsReader->setReadDataOnly(true);
//      $xlsReader->setLoadSheetsOnly(true);
//      $Sheets = $xlsReader->load($filename);
//      //开始读取上传到服务器中的Excel文件，返回一个二维数组
//      $dataArray = $Sheets->getSheet(0)->toArray();
//      $c = count($dataArray);
//      for($i=0;$i<$c;$i++){
//      		if(preg_match("/^134[0-8]\d{7}$|^(?:13[5-9]|147|15[0-27-9]|178|18[2-478])\d{8}$/",$dataArray[$i][0])){
////      			echo $dataArray[$i][0].',';
//				
//      		}else{
//      			echo $dataArray[$i][0].',';
////      			$data[] = $dataArray[$i][0];
//      		}
//
//      }
	}
	
	//抓取文章的推荐，5-22点之间每小时10条（薪火，每小时第8分钟）(备注：看看他们家什么时候更新)
//	public function getArticleRecommend(){
//		$pattern_style ='<img.*?style="(.*?)">';
//		$pattern_src ='<img.*? src="(.*?)">';
//		$times = time();
//		$h = date('H',$times);
//		if($h > 4 && $h < 23){
//			$page = $h - 4;
//			$url = 'http://app.xfirevlian.com/as/28/ListWinnerTypeNews.json?classId=6e1e905ba0604d9bb8af183c1d254c54&pageIndex='.$page.'&pageSize=10&memberId=55555';
//			$res = file_get_contents($url);
//			$res = json_decode($res,true);
//	
//			$data = $res['list'];
//			$c = count($data);
//			for($i=0;$i<$c;$i++){
//				$url2 = 'http://app.xfirevlian.com/w/28/FindNews.json?id='.$data[$i]['id'];
//				$list[$i]['title'] = $data[$i]['taskTitle'];
//		    		$list[$i]['cid'] = 42;
//	    			$list[$i]['pic_type'] = 2;
//	    			if(strpos($data[$i]['imgLink'],'img04.sogoucdn.com') !== false){
//					$images = $data[$i]['imgLink'];
//	    			}else{
//	    				$images = 'http://img04.sogoucdn.com/net/a/04/link?appid=100520033&url='.str_replace('https://','http://',$data[$i]['imgLink']);
//	    			}
//	    			$list[$i]['litpic1'] = $images;
//	    			$list[$i]['litpic2'] = '';
//	    			$list[$i]['litpic3'] = '';
//				//获取页面内容
//				$arr = json_decode(file_get_contents($url2),true);
//				$desc = $arr['task']['news']['content'];//获取文章内容
//				preg_match_all($pattern_style,$desc,$matches_style);//取出所有style的内容
//				$style_c = count($matches_style[1]);
//				for($k=0;$k<$style_c;$k++){
//					$desc = str_replace($matches_style[1][$k],'',$desc);//循环替换为空
//				}
//
//				preg_match_all($pattern_src,$desc,$matches_src);
//				$src_c = count($matches_src[1]);
//				for($k=0;$k<$src_c;$k++){
//					if(strpos($matches_src[1][$k],'640') !== false){
//						$desc = str_replace($matches_src[1][$k],strstr($matches_src[1][$k],'640',true).'640',$desc);//循环替换为空
//		    			}
//				}
//				
//				$desc = str_replace('margin: -1.5rem 0px 0px 1.5rem;','',$desc);
//		    		$list[$i]['desc'] = $desc;
//		    		$list[$i]['price'] = 12;
//		    		$list[$i]['status'] = 1;
//		    		$list[$i]['type'] = 1;
//		    		$list[$i]['visitnum'] = rand(7000,50000);
//		    		$list[$i]['sharenum'] = rand(7000,50000);
//		    		$list[$i]['publish_time'] = time();
//		    		$list[$i]['create_time'] = time();
//			}
//
//			$res = M('Article')->addAll($list);
//			if($res){
//				//更新“文章推荐”缓存内容
//				A("Timedtask")->SetArticleListCache1();
//			}
//		}
//	}


	//获取不活跃用户的行为
	public function buhuoyue(){
		die;
//		$starttime = strtotime(date("Y-m-d",strtotime("-2 day")));//昨天开始时间戳
//		$endtime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
////		$endtime = $starttime + 86400;//昨天开始时间戳
//		$where['create_time'] = array(array('egt',$starttime),array('lt',$endtime));
//		$where['phone'] = array('gt',1);
//		$User = M('User');
//		$arr = $User->field('id')->where($where)->select();
//		$c = count($arr);
//		for($i=0;$i<$c;$i++){
//			$str .= $arr[$i]['id'].',';
//		}
//		
//		$Zrecord1534694400 = M('Zrecord'.$starttime);
//		$where3['type'] = 13;
//		$where3['uid'] = array('in',rtrim($str,','));
//		$res = $Zrecord1534694400->field('count(id) as num,uid')->where($where3)->group('uid')->select();
//		
//		$ccc = count($res);
//		
//		for($i=0;$i<$c;$i++){
//			for($j=0;$j<$ccc;$j++){
//				if($arr[$i]['id'] == $res[$j]['uid']){
//					unset($arr[$i]);
//					break;
//				}
//			}
//		}
//		
//		
//		$arr1 = array_values($arr);
//		$c1 = count($arr1);
//		for($i=0;$i<$c1;$i++){
//			$str1 .= $arr1[$i]['id'].',';
//		}
//		
//		$str1 = rtrim($str1,',');
		
		//获取签到的人数
//		$where4['type'] = 3;
//		$where4['uid'] = array('in',rtrim($str1,','));
//		$res4 = $Zrecord1534694400->field('count(id) as num,uid')->where($where4)->group('uid')->select();
		
		//获取时段奖励
//		$where4['type'] = 8;
//		$where4['uid'] = array('in',rtrim($str1,','));
//		$res4 = $Zrecord1534694400->field('count(id) as num,uid')->where($where4)->group('uid')->select();
		
		//获取开宝箱
//		$where4['type'] = 10;
//		$where4['uid'] = array('in',rtrim($str1,','));
//		$res4 = $Zrecord1534694400->field('count(id) as num,uid')->where($where4)->group('uid')->select();

		//获取绑定公众号
//		$where4['id'] = array('in',rtrim($str1,','));
//		$where4['cash_openid'] = array('neq','');
//		$res4 = $User->where($where4)->count();

		//次日阅读的
//		$Zrecordendtime = M('Zrecord'.$endtime);
//		$where4['type'] = 13;
//		$where4['uid'] = array('in',rtrim($str1,','));
//		$res4 = $Zrecordendtime->field('count(id) as num,uid')->where($where4)->group('uid')->select();

		//获取做任务的
//		$where4['type'] = 6;
//		$where4['rid'] = array('neq',5);
//		$where4['uid'] = array('in',rtrim($str1,','));
//		$res4 = $Zrecord1534694400->field('count(id) as num,uid')->where($where4)->group('uid')->select();
		
//		echo '<pre>';
//		var_dump($res4);
//		die;
	}
	
	//获取时时数据
	public function ceshi66(){
		$type = I('get.type',1);
		$starttime = I('get.stime',1535558400);
		$endtime = $starttime + 86400;
		echo date('Y-m-d',$starttime).'日数据：<hr/>';
		
		
		
		//获取时时ip
		$where['create_time'] = array(array('egt',$starttime),array('lt',$endtime));
		$AdExpenditureRecord = M('ArticleReadRecord');
		$res3 = $AdExpenditureRecord->field('count(distinct(user_ip)) as ad_ipnum')->where($where)->select();
		
		$where['pid'] = 31;
		$AdExpenditureRecord = M('AdExpenditureRecord');
		$res1 = $AdExpenditureRecord->field('count(distinct(user_ip)) as ad_ipnum')->where($where)->select();
		echo '广告ip:'.$res3[0]['ad_ipnum'].'<br/>';
		echo '外链ip:'.$res1[0]['ad_ipnum'].'<br/>';
		
		
		//获取------------------------
		$UserMoneyRecord = M('UserMoneyRecord');
		$where1['create_time'] = array(array('egt',$starttime),array('lt',$endtime));
		$where1['cid'] = 1;
		$res = $UserMoneyRecord->field('sum(money) as money,uid')->where($where1)->group('uid')->select();
		$c = count($res);
		for($i=0;$i<$c;$i++){
			if($res[$i]['money'] < 500){
				$a1 += 1;
				continue;
			}elseif($res[$i]['money'] >= 500 && $res[$i]['money'] < 1000){
				$b1 += 1;
				continue;
			}elseif($res[$i]['money'] >= 1000){
				$c1 += 1;
				continue;
			}
		}
		echo '<hr/>';
		echo '小于5元：  '.$a1.'<br/>';
		echo '5-9.9元：  '.$b1.'<br/>';
		echo '大于9.9元：'.$c1.'<br/>';
		
		//----------------------------------
		$Zrecord1534694400 = M('Zrecord'.$starttime);
		$where3['type'] = 11;
		$res4 = $Zrecord1534694400->field('count(id) as num')->where($where3)->group('uid')->select();
		echo '<hr/>';
		echo '参与收徒人数：'.count($res4).'<br/>';
		$where3['type'] = 5;
		$res5 = $Zrecord1534694400->where($where3)->count();
		echo '收到的徒弟人数：'.$res5.'<br/>';
		
		
		//-------------------------------------
		
		$where4['a.create_time'] = array(array('egt',$starttime),array('lt',$endtime));
		
		if($type == 1){
			$where4['a.masterid'] = array('lt',2);//市场过来的
		}elseif($type == 2){
			$where4['a.masterid'] = array('gt',1);//收徒过来的
		}
		$where4['b.status'] = 1;
		$where4['b.money'] = 100;
		
		$User = M('User');
		$res6 = $User->alias('a')
					->field('a.id,b.money')
					->where($where4)
					->join('__USER_WITHDRAW__ b ON a.id = b.uid','LEFT')
					->select();

		$c6 = count($res6);
		echo '<hr/>';
		if($type == 1){
			echo '市场过来的：<br/>';
		}elseif($type == 2){
			echo '收徒过来的：<br/>';
		}
		echo '提现1元的：'.$c6.'<br/>';
		for($i=0;$i<$c6;$i++){
			$str .= $res6[$i]['id'].',';
		}
		
		$Ztime = M('Zrecord'.$endtime);
		$where6['uid'] = array('in',rtrim($str,','));
		$res8 = $Ztime->field('count(id) as num')->where($where6)->group('uid')->select();
		echo '提现1元的,在次日活跃的：'.count($res8).'<br/>';
		
		
		$where7['create_time'] = array(array('egt',$starttime),array('lt',$endtime));
		if($type == 1){
			$where7['masterid'] = array('lt',2);//市场过来的
		}elseif($type == 2){
			$where7['masterid'] = array('gt',1);//收徒过来的
		}
		$res9 = $User->field('id')
					->where($where7)
					->select();
					
		$c9 = count($res9);
		for($i=0;$i<$c9;$i++){
			$str3 .= $res9[$i]['id'].',';
		}
					
		$where8['uid'] = array('in',rtrim($str3,','));
		$where8['cid'] = 1;
		$where8['create_time'] = array(array('egt',$starttime),array('lt',$endtime));
		$UserMoneyRecord = M('UserMoneyRecord');
		$res7 = $UserMoneyRecord->field('sum(money) as money,uid')->where($where8)->group('uid')->select();
		$c2 = count($res7);
		echo '阅读收益>= 0.1元的：'.$c2.'<br/>';
		for($i=0;$i<$c2;$i++){
			$str2 .= $res7[$i]['uid'].',';
		}
		
		$where10['uid'] = array('in',rtrim($str2,','));
		$res10 = $Ztime->field('count(id) as num')->where($where10)->group('uid')->select();
		echo '阅读收益>= 0.1,在次日活跃的：'.count($res10).'<br/>';
		
	}
	
}

