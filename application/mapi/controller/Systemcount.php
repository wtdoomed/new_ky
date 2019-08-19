<?php
namespace app\mapi\controller;
use think\Db;

//系统后台-纯数据统计
//set_time_limit(0);
//ini_set('memory_limit', '1024M');
class SystemcountController extends BaseController {
	
	//每天定时统计每条广告的数据
	public function CountAdfeeInfos(){
		set_time_limit(0);
		//获取每天阅读金币超过1000并且有师傅的用户
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$endtime = strtotime(date('Ymd', time()));//今天开始时间戳
		$AdExpenditureRecord = M('AdExpenditureRecord');
		$where['create_time'] = array(array('egt',$starttime),array('lt',$endtime));
		$where['price'] = array('gt',0);
		$result = $AdExpenditureRecord->field("aid,count(id) as ad_pvnum,price,count(distinct(user_ip)) as ad_ipnum,1 as 'type',count(distinct(user_ip)) * price as ad_money,'{$starttime}' as 'create_time'")->where($where)->group('aid')->select();
		M('CountAdfeeData')->addAll($result);
	}
	
	//每天定时统计广告类型数据(在Systemcount/CountAdfeeInfos方法之后)
	public function CountAdclassData(){
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$where['a.create_time'] = array('eq',$starttime);
		$CountAdfeeData = M('CountAdfeeData');
		$data = $CountAdfeeData->alias('a')
						->field('a.aid,sum(a.ad_pvnum) as ad_pvnum,sum(a.ad_ipnum) as ad_ipnum,a.price,sum(a.ad_money) as ad_money,a.create_time,b.cid')
						->where($where)
						->join('__ADVERTISEMENT__ b ON a.aid = b.id','LEFT')
						->group('b.cid')
						->select();
		$c = count($data);
		for($i=0;$i<$c;$i++){
			unset($data[$i]['aid']);
		}
		
		M('CountAdclassData')->addAll($data);
	}
	
	//定时统计广告位数据
	public function CountAdPosiData(){
		set_time_limit(0);
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$endtime = strtotime(date('Ymd', time()));//今天开始时间戳
		$AdExpenditureRecord = M('AdExpenditureRecord');
		$where['create_time'] = array(array('egt',$starttime),array('lt',$endtime));
		$data = $AdExpenditureRecord->field("pid,count(id) as pv_num,count(distinct(user_ip)) as ip_num,'{$starttime}' as create_time")
			->where($where)
			->group('pid')
			->select();
			
		M('CountAdpositionData')->addAll($data);
	}
	
	//获取刷量用户对广告的数据
	public function CountFoolUserAdInfo(){
		set_time_limit(0);
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$endtime = strtotime(date('Ymd', time()));//今天开始时间戳
		
		//广告 - 同一用户 - 同一设备信息
		$data = array();
		$AdExpenditureRecord = M('AdExpenditureRecord');
		$where['create_time'] = array(array('egt',$starttime),array('lt',$endtime));
		$res = $AdExpenditureRecord->field("count(id) as num,uid,equipment_model as infos,2 as type,2 as s_type,'{$starttime}' as create_time")->where($where)->group('uid,equipment_model')->order('count(id) desc')->select();
		$c = count($res);
		for($i=0;$i<$c;$i++){
			if($res[$i]['num'] >= 15){
				$data[] = $res[$i];
			}
		}
		
		$CountFooluserData = M('CountFooluserData');
		if($data){
			$CountFooluserData->addAll($data);
		}
		
		//广告 - 同一用户 - 同一ip
		$data1 = array();
		$res1 = $AdExpenditureRecord->field("count(id) as num,uid,user_ip as infos,2 as type,1 as s_type,'{$starttime}' as create_time")->where($where)->group('uid,user_ip')->order('count(id) desc')->select();
		$c1 = count($res1);
		for($i=0;$i<$c1;$i++){
			if($res1[$i]['num'] >= 15){
				$data1[] = $res1[$i];
			}
		}
		if($data1){
			$CountFooluserData->addAll($data1);
		}
	}
	
	//获取刷量用户对文章的数据
	public function CountFoolUserArticelInfo(){
		set_time_limit(0);
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$endtime = strtotime(date('Ymd', time()));//今天开始时间戳
		
		//文章/视频 - 同一用户 - 同一设备信息
		$data2 = array();
		$ArticleReadRecord = M('ArticleReadRecord');
		$where['create_time'] = array(array('egt',$starttime),array('lt',$endtime));
		$res2 = $ArticleReadRecord->field("count(id) as num,uid,equipment_model as infos,1 as type,2 as s_type,'{$starttime}' as create_time")->where($where)->group('uid,equipment_model')->order('count(id) desc')->select();
		$c2 = count($res2);
		for($i=0;$i<$c2;$i++){
			if($res2[$i]['num'] >= 15){
				$data2[] = $res2[$i];
			}
		}
		
		$CountFooluserData = M('CountFooluserData');
		if($data2){
			$CountFooluserData->addAll($data2);
		}

		//文章/视频 - 同一用户 - 同一ip
		$data3 = array();
		$res3 = $ArticleReadRecord->field("count(id) as num,uid,user_ip as infos,1 as type,1 as s_type,'{$starttime}' as create_time")->where($where)->group('uid,user_ip')->order('count(id) desc')->select();
		$c3 = count($res3);
		for($i=0;$i<$c3;$i++){
			if($res3[$i]['num'] >= 15){
				$data3[] = $res3[$i];
			}
		}
		
		if($data3){
			$CountFooluserData->addAll($data3);
		}
	}
	
	//每天定时统计渠道数据
	public function CountChannelDataInfos(){
		set_time_limit(0);
		//获取昨天开始时间戳
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$endtime = strtotime(date('Ymd', time()));//今天开始时间戳
		
		$Zrecord1527782400 = M('Zrecord'.$starttime);
		$ArticleReadRecord = M('ArticleReadRecord');
		$AdExpenditureRecord = M('AdExpenditureRecord');
		$User = M('User');
		$UserMoneyRecord = M('UserMoneyRecord');
		$Channel = M('Channel');
		
		//获取系统所有渠道
		$List = $Channel->field('id as channel,name')->select();
		
		//注册用户
		$where['type'] = 2;
		$res1 = $Zrecord1527782400->field('count(id) as num,channel')->where($where)->group('channel')->select();
		
		//活跃用户
		$where['type'] = 12;
		$res2 = $Zrecord1527782400->field('count(id) as num,channel')->where($where)->group('channel')->select();
		
		//累计用户
		$res3 = $User->field('count(id) as num,channel')->group('channel')->select();
		
		//渠道文章阅读IP数（站内）
		$where['type'] = 13;
		$res4 = $Zrecord1527782400->field('count(distinct(user_ip)) as num,channel')->where($where)->group('channel')->select();
		
		$where1['a.create_time'] = array(array('egt',$starttime),array('lt',$endtime));
		//渠道文章阅读IP数（站外）
		$res5 = $ArticleReadRecord->alias('a')
					->field('a.uid,count(distinct(a.user_ip)) as num,b.channel')
					->where($where1)
					->join('__USER__ b ON a.uid = b.id','LEFT')
					->group('b.channel')
					->select();
		
		//渠道广告IP数（站内站外总和）
		$res6 = $AdExpenditureRecord->alias('a')
					->field('a.uid,count(distinct(a.user_ip)) as num,b.channel')
					->where($where1)
					->join('__USER__ b ON a.uid = b.id','LEFT')
					->group('b.channel')
					->select();
				
		//渠道用户产出（广告费）
		$res7 = $AdExpenditureRecord->alias('a')
					->field('a.uid,sum(a.money) as num,b.channel')
					->where($where1)
					->join('__USER__ b ON a.uid = b.id','LEFT')
					->group('b.channel')
					->select();
		
		//渠道用户成本（金币）
		$where['type'] = array('in','3,4,6,8,10');
		$res8 = $Zrecord1527782400->field('sum(gold) as num,channel')->where($where)->group('channel')->select();
		
		//渠道用户成本（钱）
		$where1['cid'] = array('neq',6);
		$res9 = $UserMoneyRecord->alias('a')
					->field('a.uid,sum(a.money) as num,b.channel')
					->where($where1)
					->join('__USER__ b ON a.uid = b.id','LEFT')
					->group('b.channel')
					->select();
					
		//获取外链给钱的ip（点开全文并阅读的ip）
		$where1['cid'] = array('eq',1);
		$res10 = $UserMoneyRecord->alias('a')
					->field('a.uid,sum(a.money) as num,b.channel')
					->where($where1)
					->join('__USER__ b ON a.uid = b.id','LEFT')
					->group('b.channel')
					->select();
		
		$c = count($List);
		$c1 = count($res1);
		$c2 = count($res2);
		$c3 = count($res3);
		$c4 = count($res4);
		$c5 = count($res5);
		$c6 = count($res6);
		$c7 = count($res7);
		$c8 = count($res8);
		$c9 = count($res9);
		$c10 = count($res10);
		
		for($i=0;$i<$c;$i++){
			
			$List[$i]['register_user'] = 0;
			$List[$i]['active_user'] = 0;
			$List[$i]['count_user'] = 0;
			$List[$i]['ar_ip_within'] = 0;
			$List[$i]['ar_ip_abroad'] = 0;
			$List[$i]['ad_ip'] = 0;
			$List[$i]['ad_money'] = 0;
			$List[$i]['cost_gold'] = 0;
			$List[$i]['cost_money'] = 0;
			$List[$i]['valid_ip'] = 0;
			
			for($j=0;$j<$c1;$j++){
				if($List[$i]['channel'] == $res1[$j]['channel']){
					$List[$i]['register_user'] = $res1[$j]['num'];
					break;
				}
			}
			
			for($j=0;$j<$c2;$j++){
				if($List[$i]['channel'] == $res2[$j]['channel']){
					$List[$i]['active_user'] = $res2[$j]['num'];
					break;
				}
			}
			
			for($j=0;$j<$c3;$j++){
				if($List[$i]['channel'] == $res3[$j]['channel']){
					$List[$i]['count_user'] = $res3[$j]['num'];
					break;
				}
			}
			
			for($j=0;$j<$c4;$j++){
				if($List[$i]['channel'] == $res4[$j]['channel']){
					$List[$i]['ar_ip_within'] = $res4[$j]['num'];
					break;
				}
			}
			
			for($j=0;$j<$c5;$j++){
				if($List[$i]['channel'] == $res5[$j]['channel']){
					$List[$i]['ar_ip_abroad'] = $res5[$j]['num'];
					break;
				}
			}
			
			for($j=0;$j<$c6;$j++){
				if($List[$i]['channel'] == $res6[$j]['channel']){
					$List[$i]['ad_ip'] = $res6[$j]['num'];
					break;
				}
			}
			
			for($j=0;$j<$c7;$j++){
				if($List[$i]['channel'] == $res7[$j]['channel']){
					$List[$i]['ad_money'] = $res7[$j]['num'];
					break;
				}
			}
			
			for($j=0;$j<$c8;$j++){
				if($List[$i]['channel'] == $res8[$j]['channel']){
					$List[$i]['cost_gold'] = $res8[$j]['num'];
					break;
				}
			}
			
			for($j=0;$j<$c9;$j++){
				if($List[$i]['channel'] == $res9[$j]['channel']){
					$List[$i]['cost_money'] = $res9[$j]['num'];
					break;
				}
			}
			
			for($j=0;$j<$c10;$j++){
				if($List[$i]['channel'] == $res10[$j]['channel']){
					$List[$i]['valid_ip'] = $res10[$j]['num'] / 10;
					break;
				}
			}
			
			$List[$i]['create_time'] = $starttime;
		}
		
		$CountChannelData = M('CountChannelData');
		$CountChannelData->addAll($List);
	}
	
	//每天定时统计大表数据
	public function CountBigInfosData(){
		set_time_limit(0);
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$Zrecord = M('Zrecord'.$starttime);
		$res = $Zrecord->field('type,count(id) as num')->group('type')->order('type asc')->select();
		$c = count($res);
		for($i=0;$i<$c;$i++){
			$data['type'.$res[$i]['type']] = $res[$i]['num'];
		}
		$data['create_time'] = $starttime;
		$CountBigData = M('CountBigData');
		$CountBigData->add($data);
	}
	
	//每天定时统计外链数据
	public function CountChainInfos(){
		set_time_limit(0);
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$endtime = strtotime(date('Ymd', time()));//今天开始时间戳
		$Zrecord = M('Zrecord'.$starttime);
		//获取注册人数
		$where1['type'] = 5;
		$res1 = $Zrecord->where($where1)->count();
		
		//获取分享收徒人数
		$where1['type'] = 11;
		$res2 = $Zrecord->where($where1)->count('distinct(uid)');
		
		
		//获取分享文章/视频人数
		$UserShareRecord = M('UserShareRecord');
		$where['a.type'] = 1;
		$where['a.create_time'] = array(array('egt',$starttime),array('lt',$endtime));
		$where['b.type'] = array('exp','is not null');
		$res3 = $UserShareRecord->alias('a')
							   	->field('a.aid,b.type,count(b.id) as num')
							   	->join('__ARTICLE__ b ON a.aid = b.id','LEFT')
							   	->where($where)
							   	->group('b.type')
							   	->select();
		//封装数组
		$data['asharenum'] = $res3[0]['num'];
		$data['vsharenum'] = $res3[1]['num'];
		$data['dsharenum'] = $res2;
		$data['registernum'] = $res1;
		$data['create_time'] = $starttime;
		
		//执行添加		   	
		M('CountChainData')->add($data);
	}
	
	//收徒入口数据统计
	public function CountDisRouteData(){
		set_time_limit(0);
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$Zrecord = M('Zrecord'.$starttime);
		$where['type'] = 5;
		$where['one_type'] = array('gt',0);
		$where['second_type'] = array('gt',0);
		$data = $Zrecord->field("count(id) as num,one_type,second_type,'$starttime' as 'create_time'")->where($where)->group('one_type,second_type')->order('one_type asc')->select();
		
		M('CountRouteData')->addAll($data);
	}
	
	//每天系统财务统计
	public function CountSystemFinanceData(){
		set_time_limit(0);
		//获取系统财务数据
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$endtime = strtotime(date('Ymd', time()));//今天开始时间戳
		
		//获取昨日注册用户
		$Zrecord = M('Zrecord'.$starttime);
		$where4['type'] = 2;
		$res4 = $Zrecord->where($where4)->count();
				
		//获取系统总用户
		$User = M('User');
		$where5['create_time'] = array('lt',$endtime);
		$res5 = $User->where($where5)->count();

		//提现金额
		$UserWithdraw = M('UserWithdraw');
		$where['status'] = 1;
		$where['hit_time'] = array(array('egt',$starttime),array('lt',$endtime));
		$res = $UserWithdraw->field('sum(money) as money')->where($where)->select();
		
		//用户成本（钱）
		$UserMoneyRecord = M('UserMoneyRecord');
		$where1['create_time'] = array(array('egt',$starttime),array('lt',$endtime));
		$res1 = $UserMoneyRecord->field('sum(money) as money')
					->where($where1)
					->select();
					
		//获取广告费
		$AdExpenditureRecord = M('AdExpenditureRecord');
		$res3 = $AdExpenditureRecord->field('sum(money) as money')->where($where1)->select();
					
		//用户成本（金币）
		$UserGoldRecord = M('UserGoldRecord');
		$where1['cid'] = array('gt',0);
		$res2 = $UserGoldRecord->field('sum(gold) as money')
					->where($where1)
					->select();
		
		$data['register_user'] = $res4;
		$data['count_user'] = $res5;
		$data['withdraw_money'] = sprintf('%.2f',$res[0]['money'] / 100);
		$data['gold_count'] = sprintf('%.2f',$res2[0]['money'] / 10000);
		$data['money_count'] = sprintf('%.2f',$res1[0]['money'] / 100);
		$data['admoney_count'] = sprintf('%.2f',$res3[0]['money'] / 100);
		$data['profit_money'] = $data['admoney_count'] - $data['gold_count'] - $data['money_count'];//利润（广告费-金币-零钱）
		$data['actual_profit_money'] = $data['admoney_count'] - $data['withdraw_money'];//实际利润（广告费-提现金额）
		$data['create_time'] = $starttime;
		
		M('CountSystemData')->add($data);
	}
	
	//每天统计用户行为数据（每天2:12）
	public function CountUserbehaviorInfos(){
		$stime = strtotime(date("Y-m-d",strtotime("-2 day")));//前天开始时间戳
		$etime = $stime + 86400;//昨天时间戳
		$where['a.create_time'] = array(array('egt',$stime),array('lt',$etime));
		
		for($type=1;$type<3;$type++){
			if($type == 1){
				$where['a.masterid'] = array('lt',2);//市场过来的
			}elseif($type == 2){
				$where['a.masterid'] = array('gt',1);//收徒过来的
			}
			
			$User = M('User');
			$res = $User->alias('a')
						->field('a.id,a.masterid,a.phone,a.cash_openid,b.create_time,c.total_money,c.user_money,c.total_gold,c.gold')
						->where($where)
						->join('__USER_ACTIVE_RECORD__ b ON a.id = b.uid','LEFT')
						->join('__USER_ACCOUNT__ c ON a.id = c.uid','LEFT')
						->select();
			$a1 = 0;
			$a2 = 0;
			$a3 = 0;
			$a4 = 0;
			$a5 = 0;
			$a6 = 0;
			$a7 = 0;
			$a8 = 0;
			$a9 = 0;
			$a10 = 0;
			$a11 = 0;
			$a12 = 0;
			$a13 = 0;
			$a14 = 0;
			$a15 = 0;
			$a16 = 0;
			
			$c = count($res);	
			for($i=0;$i<$c;$i++){
				//次日活跃
				if($res[$i]['create_time'] >= $etime){
					$a1 += 1;
				}
				
				//绑定手机号
				if($res[$i]['phone']){
					$a15 += 1;
				}
				
				//总收益=1元
				if($res[$i]['total_money'] == 100){
					$a2 += 1;
				}
				
				//总收益=2元
				if($res[$i]['total_money'] == 200){
					$a3 += 1;
				}
				//总收益1<x<2元
				if($res[$i]['total_money'] > 100 && $res[$i]['total_money'] < 200){
					$a4 += 1;
				}
				
				//总收益>2元
				if($res[$i]['total_money'] > 200){
					$a5 += 1;
				}
				
				//总金币>=1050
				if($res[$i]['total_gold'] >= 1050){
					$a6 += 1;
				}
				
				//总金币<1050
				if($res[$i]['total_gold'] < 1050){
					$a9 += 1;
				}
				
				//没有师傅的
				if($res[$i]['masterid'] == '-1'){
					$a10 += 1;
				}
				
				//绑定公众号的
				if($res[$i]['cash_openid']){
					$a16 += 1;
				}
	
			}
			
			$str = '';
			for($i=0;$i<$c;$i++){
				$str .= $res[$i]['id'].',';
			}
	
			//获取多少用户阅读过文章（当天）
			$Zrecord = M('Zrecord'.$stime);
			$where2['type'] = 13;
			$where2['uid'] = array('in',rtrim($str,','));
			$arr2 = $Zrecord->field('count(distinct(uid)) as num')->where($where2)->select();
			
			//获取多少用户阅读过文章得到奖励的（当天）
			$where2['type'] = 4;
			$arr3 = $Zrecord->field('count(distinct(uid)) as num')->where($where2)->select();
			
			//获取多少提现1元的（2天）
			$UserWithdraw = M('UserWithdraw');
			$where3['uid'] = array('in',rtrim($str,','));
			$where3['money'] = 100;
			$arr4 = $UserWithdraw->where($where3)->count();
			
			//分享过收徒用户（当天）
			$where4['type'] = 11;
			$where4['uid'] = array('in',rtrim($str,','));
			$arr5 = $Zrecord->field('count(distinct(uid)) as num')->where($where4)->select();
			
			//获取多少用户分享过文章视频（当天）
			$where1['type'] = 9;
			$where1['uid'] = array('in',rtrim($str,','));
			$arr1 = $Zrecord->field('count(distinct(uid)) as num')->where($where1)->select();
			
			$List[$type-1]['time'] = time();
			$List[$type-1]['create_time'] = $stime;
			$List[$type-1]['type'] = $type;
			$List[$type-1]['register'] = $c;
			
			$List[$type-1]['cihuonum'] = $a1;
			$List[$type-1]['cihuobi'] = round($a1/$c*100,2);
			
			$List[$type-1]['bdphonenum'] = $a15;
			$List[$type-1]['bdphonebi'] = round($a15/$c*100,2);
			
			$List[$type-1]['readartnum'] = $arr2[0]['num'];
			$List[$type-1]['readartbi'] = round($arr2[0]['num']/$c*100,2);
			
			$List[$type-1]['readartjlnum'] = $arr3[0]['num'];
			$List[$type-1]['readartjlbi'] = round($arr3[0]['num']/$c*100,2);
			
			$List[$type-1]['shareartnum'] = $arr1[0]['num'];
			$List[$type-1]['shareartbi'] = round($arr1[0]['num']/$c*100,2);
			
			$List[$type-1]['sharestnum'] = $arr5[0]['num'];
			$List[$type-1]['sharestbi'] = round($arr5[0]['num']/$c*100,2);
			
			$List[$type-1]['bggzhnum'] = $a16;
			$List[$type-1]['bggzhbi'] = round($a16/$c*100,2);
			
			$List[$type-1]['txyynum'] = $arr4;
			$List[$type-1]['txyybi'] = round($arr4/$c*100,2);
			
			$List[$type-1]['zsyyynum'] = $a2;
			$List[$type-1]['zsyyybi'] = round($a2/$c*100,2);
			
			$List[$type-1]['dyyxyenum'] = $a4;
			$List[$type-1]['dyyxyebi'] = round($a4/$c*100,2);
			
			$List[$type-1]['zsyeynum'] = $a3;
			$List[$type-1]['zsyeybi'] = round($a3/$c*100,2);
			
			$List[$type-1]['zsydyenum'] = $a5;
			$List[$type-1]['zsydyebi'] = round($a5/$c*100,2);
			
			$List[$type-1]['zjbdywsnum'] = $a6;
			$List[$type-1]['zjbdywsbi'] = round($a6/$c*100,2);
			
			$List[$type-1]['zjbxywsnum'] = $a9;
			$List[$type-1]['zjbxywsbi'] = round($a9/$c*100,2);
			
			$List[$type-1]['nomastnum'] = $a10;
			$List[$type-1]['nomastbi'] = round($a10/$c*100,2);
		}
		
		M('CountUserbehaviorData')->addAll($List);
	}
	
}

