<?php
namespace app\mapi\controller;
use think\Db;

//数据统计－系统定时任务
class DatacountController extends BaseController {
	
	//统计每个用户每小时赚了多少收益（每小时第1分钟）
	public function CountDiscipleProfitInfo(){
		set_time_limit(0);
		$stime = strtotime(date("Y-m-d H",strtotime("-1 hour")).':00:00');//一小时的开始时间
		$end = $stime + 3600;//一小时的结束时间

		//统计每个用户每小时赚了多少收益
		$UserMoneyRecord = M('UserMoneyRecord');
		$where['cid'] = array('in','1,7');
		$where['create_time'] = array(array('egt',$stime),array('lt',$end));
		$data = $UserMoneyRecord->field("uid,sum(money) as money,'{$stime}' as 'create_time'")->where($where)->group('uid')->select();
		
		$CountDiscipleProfitData = M('CountDiscipleProfitData');
		$CountDiscipleProfitData->addAll($data);
	}
	
	//统计每个用户每天赚了多少收益（每天02:08）
	public function CountDiscipleProfitInfoDays(){
		set_time_limit(0);
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$endtime = strtotime(date('Ymd', time()));//今天开始时间戳

		//统计每个用户每小时赚了多少收益
		$CountDiscipleProfitData = M('CountDiscipleProfitData');
		$where['type'] = 1;
		$where['create_time'] = array(array('egt',$starttime),array('lt',$endtime));
		$data = $CountDiscipleProfitData->field("uid,sum(money) as money,'{$starttime}' as 'create_time',2 as 'type'")->where($where)->group('uid')->select();
		
		$CountDiscipleProfitData = M('CountDiscipleProfitData');
		$CountDiscipleProfitData->addAll($data);
	}
	
	//定时统计徒弟每小时赚了多少钱，给师傅加钱（每小时第6分钟）
    public function saveMasteridProfit(){
    		set_time_limit(0);
    		$stime = strtotime(date("Y-m-d H",strtotime("-1 hour")).':00:00');//一小时的开始时间
    		$CountDiscipleProfitData = M('CountDiscipleProfitData');
    		$where['a.create_time'] = $stime;
    		$where['a.type'] = 1;
    		$where['c.status'] = array('neq',4);
    		$where['c.is_withdraw'] = 1;
    		$res = $CountDiscipleProfitData->alias('a')
    					->field('a.uid,a.money,b.masterid')
    					->join('__DISCIPLE__ b ON a.uid = b.student_uid')
    					->join('__USER__ c ON a.uid = c.id')
    					->where($where)
    					->select();
    		$UserAccount = M('UserAccount');
    		$UserMoneyRecord = M('UserMoneyRecord');
    		$Disciple = M('Disciple');
    		for($i=0;$i<count($res);$i++){
    			if($res[$i]['masterid'] > 0){
    				//获取该金额对应的扣量规则
	    			$istrue = $this->getDeductionInfo($res[$i]['money'],$res[$i]['masterid']);
	    			//计算要给师傅&师祖多少钱
	    			$m = $res[$i]['money'] * 0.3;
	    			if($istrue){
	    				$money = $m - ($m * $istrue);//扣量后要给师傅和师祖的钱
	    				$f = floor($money*(2/3));
	    			}else{
	    				$f = floor($m*(2/3));
	    			}
	    			
	    			//如果要提成的钱为0，则不执行加钱
	    			if($f == 0){
	    				continue;
	    			}
	    			
	    			//给师傅加钱
	    			$where1['uid'] = $res[$i]['masterid'];
	    			$arr1 = array(
					'total_money'=>array('exp','total_money+'.$f),
					'user_money'=>array('exp','user_money+'.$f),
				);
				$UserAccount->where($where1)->save($arr1);
				
				//获取师傅账户信息
				$userAcc = $this->get_user_account_info($res[$i]['masterid']);
				
	    			//添加师傅收益记录
	    			$data1[$i]['uid'] = $res[$i]['masterid'];
	    			$data1[$i]['student_uid'] = $res[$i]['uid'];
	    			$data1[$i]['money'] = $f;
	    			$data1[$i]['after_money'] = $userAcc['user_money'];
	    			$data1[$i]['cid'] = 4;
	    			$data1[$i]['create_time'] = time();
	    			
	    			//给师傅加钱(给师傅加钱的时候，要记录徒弟给师傅加了多少钱了)
	    			$where2['student_uid'] = $res[$i]['uid'];
	    			$Disciple->where($where2)->setInc('master_money',$f);
	    			
	    			//清除“收徒-我的好友，以及二级页面的缓存”
//	    			$this->CleanUserDiscipleList($res[$i]['masterid'],2);
    			}
    		}
    		
		$UserMoneyRecord->addAll(array_values($data1));
    }
    
    //定时统计徒孙小时赚了多少钱，给师祖加钱（每小时第5分钟）
    public function saveMasterfatheridProfit(){
    		set_time_limit(0);
    		$stime = strtotime(date("Y-m-d H",strtotime("-1 hour")).':00:00');//一小时的开始时间
    		$CountDiscipleProfitData = M('CountDiscipleProfitData');
    		$where['a.create_time'] = $stime;
    		$where['a.type'] = 1;
    		$where['c.status'] = array('neq',4);
    		$where['c.is_withdraw'] = 1;
    		$res = $CountDiscipleProfitData->alias('a')
    					->field('a.uid,a.money,b.masterfatherid')
    					->join('__DISCIPLE__ b ON a.uid = b.student_uid')
    					->join('__USER__ c ON a.uid = c.id')
    					->where($where)
    					->select();
    		$UserAccount = M('UserAccount');
    		$UserMoneyRecord = M('UserMoneyRecord');
    		$Disciple = M('Disciple');
    		for($i=0;$i<count($res);$i++){
    			
    			if($res[$i]['masterfatherid'] > 0){
    				//获取该金额对应的扣量规则
	    			$istrue = $this->getDeductionInfo($res[$i]['money'],$res[$i]['masterfatherid']);
	    			//计算要给师傅&师祖多少钱
	    			$m = $res[$i]['money'] * 0.3;
	    			if($istrue){
	    				$money = $m-($m * $istrue);//扣量后要给师傅和师祖的钱
	    				$z = floor($money*(1/3));
	    			}else{
	    				$z = floor($m*(1/3));
	    			}
	    			
	    			//如果要提成的钱为0，则不执行加钱
	    			if($z == 0){
	    				continue;
	    			}
    			
    				//给师祖加钱
    				$where1['uid'] = $res[$i]['masterfatherid'];
	    			$arr1 = array(
					'total_money'=>array('exp','total_money+'.$z),
					'user_money'=>array('exp','user_money+'.$z),
				);
				$UserAccount->where($where1)->save($arr1);
				
				//获取师傅账户信息
				$userAcc = $this->get_user_account_info($res[$i]['masterfatherid']);
				
    				//添加师祖收益记录
    				$data1[$i]['uid'] = $res[$i]['masterfatherid'];
	    			$data1[$i]['student_uid'] = $res[$i]['uid'];
	    			$data1[$i]['money'] = $z;
	    			$data1[$i]['after_money'] = $userAcc['user_money'];
	    			$data1[$i]['cid'] = 5;
	    			$data1[$i]['create_time'] = time();
	    			
	    			//给师傅加钱(给师傅加钱的时候，要记录徒弟给师傅加了多少钱了)
    				$where2['student_uid'] = $res[$i]['uid'];
    				$Disciple->where($where2)->setInc('masterfather_money',$z);
    			}
    		}
    		
    		$UserMoneyRecord->addAll(array_values($data1));
    }
    
    //获取扣量规则
    private function getDeductionInfo($money,$uid){
    		//获取该金额对应用户的扣量规则
    		$where1['start_money'] = array('elt',$money);
    		$where1['type'] = 2;
    		$where1['uid'] = $uid;
    		$where1['status'] = 1;
    		$UserDeduction = M('UserDeduction');
    		$res1 = $UserDeduction->field('deduction_pre')->where($where1)->order('end_money desc')->find();
    		
    		if($res1 === NULL){
    			return false;//不在扣量范围内
    		}else{
    			return $res1['deduction_pre'];
    		}
    }
    
	//徒弟收益周排行（每天03:01）
	public function SetDiscipleWeekRank(){
		set_time_limit(0);
		$da = date("w");//获取今天是周几
   		if($da == 1){
   			$starttime = mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));//上周开始时间
   			$endtime = mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));//上周结束时间
   		}else{
   			$starttime = mktime(0,0,0,date("m"),date("d")-date("w")+1,date("Y"));//本周开始时间
   			$endtime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间
   		}
   		
		$CountDiscipleProfitData = M('CountDiscipleProfitData');
		$where['a.create_time'] = [['egt', $starttime], ['elt', $endtime]];
		$where['a.type'] = 2;
		$res = $CountDiscipleProfitData->alias('a')
									   ->field('a.uid,sum(a.money) as money,sum(a.num) as num,b.name,b.litpic')
									   ->join('__USER__ b ON a.uid = b.id','LEFT')
									   ->where($where)
									   ->group('a.uid')
									   ->order('sum(a.money) desc,sum(a.num) desc')
									   ->limit(0,50)
									   ->select();
		if($res !== NULL){
			//存入redis里
			$this->redis->set('WT_AppDiscipleWeekRank',json_encode($res));
		}
	}
	
	//徒弟收益月排行（每天03:05）
	public function SetDiscipleMonthRank(){
		set_time_limit(0);
		$da = date("d");//获取今天是几号
   		if($da == 1){
   			$starttime = strtotime(date('Y-m-01',strtotime('-1 month')));//上月开始时间
   			$endtime = strtotime(date('Y-m-t',strtotime('-1 month')));//上月结束时间
   		}else{
   			$starttime = mktime(0,0,0,date('m'),1,date('Y'));//本月开始时间
   			$endtime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间
   		}
		$CountDiscipleProfitData = M('CountDiscipleProfitData');
		$where['a.create_time'] = [['egt', $starttime], ['elt', $endtime]];
		$where['a.type'] = 2;
		$res = $CountDiscipleProfitData->alias('a')
									   ->field('a.uid,sum(a.money) as money,sum(a.num) as num,b.name,b.litpic')
									   ->join('__USER__ b ON a.uid = b.id','LEFT')
									   ->where($where)
									   ->group('a.uid')
									   ->order('sum(a.money) desc,sum(a.num) desc')
									   ->limit(0,50)
									   ->select();
		if($res !== NULL){
			//存入redis里
			$this->redis->set('WT_AppDiscipleMonthRank',json_encode($res));
		}
	}
	
	//每天00:00分清除用户每天赠送的金币（每天00:00）
	public function CleanUserTodayReadGold(){
		set_time_limit(0);
		$keys = $this->redis->keys('WT_AppUserTodayReadGold*');
        for ($i = 0; $i < count($keys); $i++) {
            $this->redis->set($keys[$i],null);
        }
	}

//	//每天定时统计哪些徒弟完成了给师傅的7次奖励,给师傅加收益（每天02:30）
//	public function CountDiscipleFinishToMaster(){
//		set_time_limit(0);
//		//获取每天阅读收益达到5毛的并且有师傅的用户
//		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
//		$CountDiscipleProfitData = M('CountDiscipleProfitData');
//		$where['a.type'] = 2;
//		$where['a.create_time'] = array('egt',$starttime);
//		$where['a.money'] = array('egt',50);
//		$where['b.masterid'] = array('gt',0);
//		$where['b.status'] = array('neq',4);
//		$result = $CountDiscipleProfitData->alias('a')->field('a.uid,b.masterid')->join('__USER__ b ON a.uid = b.id','LEFT')->where($where)->select();
//		if($result){
//			for($i=0;$i<count($result);$i++){
//				//获取是第几次奖励
//				$DiscipleReward = M('DiscipleReward');
//				$where1['masterid'] = $result[$i]['masterid'];
//				$where1['student_uid'] = $result[$i]['uid'];
//				$res = $DiscipleReward->field('cid')->where($where1)->order('cid desc')->find();
//				if($res['cid'] == 7){
//					continue;
//				}elseif($res){
//					$id = $res['cid'] + 1;
//				}else{
//					$id = 2;
//				}
//				//获取该奖励的金额
//				$redisinfo = $this->redis->get('WT_AppDiscipleRewardMoney'.$id);
//				if($redisinfo){
//					$money = $redisinfo;
//				}else{
//					$DiscipleRewardConfig = M('DiscipleRewardConfig');
//					$where2['id'] = $id;
//					$Reward = $DiscipleRewardConfig->field('money')->where($where2)->find();
//					if($Reward === NULL){
//						continue;//如果找不到该奖励规则对应的金额，直接停止
//					}else{
//						$money = $Reward['money'];
//						//存入redis里
//						$this->redis->set('WT_AppDiscipleRewardMoney'.$id,$money);
//					}
//				}
//				
//				//给师傅加收益
//				$UserAccount = M('UserAccount');
//				$arr = array(
//					'total_money'=>array('exp','total_money+'.$money),
//					'user_money'=>array('exp','user_money+'.$money)
//				);
//				$where3['uid'] = $result[$i]['masterid'];
//	            $res = $UserAccount->where($where3)->save($arr);
//	            
//	            //获取用户账户信息
//	            $user_account = $this->get_user_account_info($result[$i]['masterid']);
//				if(!$user_account){
//					continue;
//				}
//	            
//				//添加师傅收益记录
//				$UserMoneyRecord = M('UserMoneyRecord');
//				$data['uid'] = $result[$i]['masterid'];
//				$data['student_uid'] = $result[$i]['uid'];
//				$data['money'] = $money;
//				$data['after_money'] = $user_account['user_money'];
//				$data['cid'] = 2;
//				$data['create_time'] = time();
//				$UserMoneyRecord->add($data);
//				
//				//给师傅加完钱之后要更新收徒表中 该徒弟给师傅带来的收益
//				$where4['student_uid'] = $result[$i]['uid'];
//				M('Disciple')->where($where4)->setInc('master_money',$money);
//				
//				//添加师傅收徒6次奖励记录
//				$data1['cid'] = $id;
//				$data1['masterid'] = $result[$i]['masterid'];
//				$data1['student_uid'] = $result[$i]['uid'];
//				$data1['create_time'] = time();
//				$DiscipleReward->add($data1);
//				
//				//清除“收徒-我的好友，以及二级页面的缓存”
//  				$this->CleanUserDiscipleList($result[$i]['masterid'],1);
//			}
//		}
//	}

	//每天定时统计哪些徒弟完成了给师傅的7次奖励,给师傅加收益（每天02:30）
	public function CountDiscipleFinishToMaster(){
		set_time_limit(0);
		//获取每天阅读收益达到5毛的并且有师傅的用户
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$CountDiscipleProfitData = M('CountDiscipleProfitData');
		$where['a.type'] = 2;
		$where['a.create_time'] = $starttime;
		$where['a.money'] = array('egt',50);
		$where['b.masterid'] = array('gt',0);
		$where['b.status'] = array('neq',4);
		$result = $CountDiscipleProfitData->alias('a')->field('a.uid,b.masterid')->join('__USER__ b ON a.uid = b.id','LEFT')->where($where)->select();
		$DiscipleReward = M('DiscipleReward');
		$UserMoneyRecord = M('UserMoneyRecord');
		for($i=0;$i<count($result);$i++){
			//获取是第几次奖励
			$where1['masterid'] = $result[$i]['masterid'];
			$where1['student_uid'] = $result[$i]['uid'];
			$res = $DiscipleReward->field('cid')->where($where1)->order('cid desc')->find();
			if($res['cid'] > 3){
				continue;
			}elseif($res){
				$id = $res['cid'] + 1;
			}else{
				$id = 1;
			}
			//获取该奖励的金额
			$redisinfo = $this->redis->get('WT_AppDiscipleRewardMoney'.$id);
			if($redisinfo){
				$money = $redisinfo;
			}else{
				$DiscipleRewardConfig = M('DiscipleRewardConfig');
				$where2['id'] = $id;
				$Reward = $DiscipleRewardConfig->field('money')->where($where2)->find();
				if($Reward === NULL){
					continue;//如果找不到该奖励规则对应的金额，直接停止
				}else{
					$money = $Reward['money'];
					//存入redis里
					$this->redis->set('WT_AppDiscipleRewardMoney'.$id,$money);
				}
			}
			
			//给师傅加收益
			$UserAccount = M('UserAccount');
			$arr = array(
				'total_money'=>array('exp','total_money+'.$money),
				'user_money'=>array('exp','user_money+'.$money)
			);
			$where3['uid'] = $result[$i]['masterid'];
            $res = $UserAccount->where($where3)->save($arr);
            
            //获取用户账户信息
            $user_account = $this->get_user_account_info($result[$i]['masterid']);
			if(!$user_account){
				continue;
			}
            
			//添加师傅收益记录
			$data[$i]['uid'] = $result[$i]['masterid'];
			$data[$i]['student_uid'] = $result[$i]['uid'];
			$data[$i]['money'] = $money;
			$data[$i]['after_money'] = $user_account['user_money'];
			$data[$i]['cid'] = 2;
			$data[$i]['create_time'] = time();
			
			//给师傅加完钱之后要更新收徒表中 该徒弟给师傅带来的收益
			$where4['student_uid'] = $result[$i]['uid'];
			M('Disciple')->where($where4)->setInc('master_money',$money);
			
			//添加师傅收徒6次奖励记录
			$data1[$i]['cid'] = $id;
			$data1[$i]['masterid'] = $result[$i]['masterid'];
			$data1[$i]['student_uid'] = $result[$i]['uid'];
			$data1[$i]['create_time'] = time();
			
			//清除“收徒-我的好友，以及二级页面的缓存”
//			$this->CleanUserDiscipleList($result[$i]['masterid'],1);
		}
			
		$UserMoneyRecord->addAll(array_values($data));
		$DiscipleReward->addAll(array_values($data1));
	}
	
	//收徒奖励10000金币,给师傅加收益（每天02:30）
	public function CountDisToMaster(){
		set_time_limit(0);
		//获取每天站内阅读金币达到500的并且有师傅的用户
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$CountUseraccountData = M('CountUseraccountData');
		$where['a.create_time'] = $starttime;
		$where['a.gold'] = array('egt',500);
		$where['b.masterid'] = array('gt',1);
		$where['b.status'] = array('neq',4);
		$result = $CountUseraccountData->alias('a')->field('a.uid,b.masterid')->join('__USER__ b ON a.uid = b.id','LEFT')->where($where)->select();
		$UserGoldRecord = M('UserGoldRecord');
		for($i=0;$i<count($result);$i++){
			//获取是第几次奖励
			$where1['uid'] = $result[$i]['masterid'];
			$where1['student_uid'] = $result[$i]['uid'];
			$res = $UserGoldRecord->where($where1)->count();
			if($res > 2){
				continue;
			}elseif($res == 2){
				$gold = 4000;
			}else{
				$gold = 3000;
			}
			
			//给师傅加收益
			$UserAccount = M('UserAccount');
			$arr = array(
				'total_gold'=>array('exp','total_gold+'.$gold),
				'gold'=>array('exp','gold+'.$gold)
			);
			$where3['uid'] = $result[$i]['masterid'];
            $res = $UserAccount->where($where3)->save($arr);
            
            //获取用户账户信息
            $user_account = $this->get_user_account_info($result[$i]['masterid']);
			if(!$user_account){
				continue;
			}
            
			//添加师傅收益记录
			$data[$i]['uid'] = $result[$i]['masterid'];
			$data[$i]['student_uid'] = $result[$i]['uid'];
			$data[$i]['gold'] = $gold;
			$data[$i]['after_gold'] = $user_account['gold'];
			$data[$i]['cid'] = 9;
			$data[$i]['create_time'] = time();
		}
		if($data){
			$UserGoldRecord->addAll(array_values($data));
		}
	}
	
	//每天定时统计哪些徒弟完成了给师傅的7次奖励中的一元提现,给师傅加收益（每小时第10分钟）
//	public function CountDiscipleFinishToMasterOne(){
//		set_time_limit(0);
//		//获取昨天哪些用户完成了1元提现
//		$starttime = strtotime(date("Y-m-d H",strtotime("-1 hour")).':00:00');//一小时的开始时间
//		$endtime = $starttime + 3600;//一小时的结束时间
//		$UserWithdraw = M('UserWithdraw');
//		$where['a.create_time'] = array(array('egt',$starttime),array('lt',$endtime));
//		$where['a.status'] = 1;
//		$where['b.masterid'] = array('gt',0);
//		$where['b.status'] = array('neq',4);
//		$result = $UserWithdraw->alias('a')->field('a.uid,b.masterid')->join('__USER__ b ON a.uid = b.id','LEFT')->where($where)->select();
//		if($result){
//			for($i=0;$i<count($result);$i++){
//				//获取是第几次奖励
//				$DiscipleReward = M('DiscipleReward');
//				$where1['masterid'] = $result[$i]['masterid'];
//				$where1['student_uid'] = $result[$i]['uid'];
//				$res = $DiscipleReward->field('cid')->where($where1)->order('cid asc')->find();
//				if($res['cid'] == 1){
//					continue;
//				}else{
//					$id = 1;
//				}
//				
//				//获取该奖励的金额
//				$redisinfo = $this->redis->get('WT_AppDiscipleRewardMoney'.$id);
//				if($redisinfo){
//					$money = $redisinfo;
//				}else{
//					$DiscipleRewardConfig = M('DiscipleRewardConfig');
//					$where2['id'] = $id;
//					$Reward = $DiscipleRewardConfig->field('money')->where($where2)->find();
//					if($Reward === NULL){
//						continue;//如果找不到该奖励规则对应的金额，直接停止
//					}else{
//						$money = $Reward['money'];
//						//存入redis里
//						$this->redis->set('WT_AppDiscipleRewardMoney'.$id,$money);
//					}
//				}
//				
//				//给师傅加收益
//				$UserAccount = M('UserAccount');
//				$arr = array(
//					'total_money'=>array('exp','total_money+'.$money),
//					'user_money'=>array('exp','user_money+'.$money)
//				);
//				$where3['uid'] = $result[$i]['masterid'];
//	            $res = $UserAccount->where($where3)->save($arr);
//				
//	            //获取用户账户信息
//	            $user_account = $this->get_user_account_info($result[$i]['masterid']);
//	            if(!$user_account){
//					continue;
//				}
//				
//				//添加师傅收益记录
//				$UserMoneyRecord = M('UserMoneyRecord');
//				$data['uid'] = $result[$i]['masterid'];
//				$data['student_uid'] = $result[$i]['uid'];
//				$data['money'] = $money;
//				$data['after_money'] = $user_account['user_money'];
//				$data['cid'] = 2;
//				$data['create_time'] = time();
//				$UserMoneyRecord->add($data);
//				
//				//给师傅加完钱之后要更新收徒表中 该徒弟给师傅带来的收益
//				$where4['student_uid'] = $result[$i]['uid'];
//				M('Disciple')->where($where4)->setInc('master_money',$money);
//				
//				//添加师傅收徒6次奖励记录
//				$data1['cid'] = $id;
//				$data1['masterid'] = $result[$i]['masterid'];
//				$data1['student_uid'] = $result[$i]['uid'];
//				$data1['create_time'] = time();
//				$DiscipleReward->add($data1);
//				
//				//清除“收徒-我的好友，以及二级页面的缓存”
//  				$this->CleanUserDiscipleList($result[$i]['masterid'],1);
//			}
//		}
//	}
	
	//每天定时给用户兑换书币（每天00:05）
	public function UserGoldExchangeMoney(){
		set_time_limit(0);
		$UserAccount = M('UserAccount');
		$where['gold'] = array('egt',1000);
		$result = $UserAccount->field('uid,user_money,gold')->where($where)->select();
		
		for($i=0;$i<count($result);$i++){
			$gold = floor($result[$i]['gold'] / 1000);
			$s = $gold*10;
			$d = $gold*1000;
			$uwhere['uid'] = $result[$i]['uid'];
			$arr = array(
				'total_money'=>array('exp','total_money+'.$s),
				'user_money'=>array('exp','user_money+'.$s),
				'gold'=>array('exp','gold-'.$d),
			);
			//执行金币兑换
			$res = $UserAccount->where($uwhere)->save($arr);
			
			$data[$i]['uid'] = $result[$i]['uid'];
			$data[$i]['cid'] = -1;
			$data[$i]['gold'] = -$d;
			$data[$i]['after_gold'] = $result[$i]['gold'] - $d;
			$data[$i]['create_time'] = time();
			
			$data1[$i]['uid'] = $result[$i]['uid'];
			$data1[$i]['money'] = $s;
			$data1[$i]['after_money'] = $result[$i]['user_money'] + $s;
			$data1[$i]['cid'] = 6;
			$data1[$i]['create_time'] = $data[$i]['create_time'];
		}
		
		if($data){
			M('UserGoldRecord')->addAll($data);
			M('UserMoneyRecord')->addAll($data1);
		}
	}
	
	//半小时轮训一次下广告金额（半小时1次）
	public function setRemoveAdList(){
		set_time_limit(0);
		$Advertisement = M('Advertisement');
		$where['_string'] = 'surplus_money <= down_money';
		$where['status'] = 1;
		$res = $Advertisement->field('id,pid')->where($where)->select();//获取要下架的广告
		if($res){
			$id = '';
			$c = count($res);
			for($i=0;$i<$c;$i++){
				$id .= $res[$i]['id'].',';
				$this->redis->set('WT_AppADInfoSingleData'.$res[$i]['pid'],null);
			}
			$where2['id'] = array('in',rtrim($id,','));
			$data['status'] = 0;
			$res1 = $Advertisement->where($where2)->save($data);//执行下架
			if($res1){//清除广告列表缓存
				$keys = $this->redis->keys('WT_AppADInfoLists*');
		        for ($i = 0; $i < count($keys); $i++) {
		            $this->redis->set($keys[$i],null);
		        }
			}
		}
	}
	
	//定时检测--微信--域名是否被封（每5分钟一次）
	public function TestingDomainNameList(){
		set_time_limit(0);
		//获取系统获取在线的域名
		$Domainname = M('Domainname');
		$where['status'] = 1;
		$res1 = $Domainname->field('id,dname')->where($where)->select();
		$c = count($res1);
		$url = 'http://lj.52jcc.cc/api.php?key=foif8ejduwkljduskjduiudwlkxuewiufjwjfdf&ym=';
		for($i=0;$i<$c;$i++){
			$dname = rtrim(str_replace("http://","",$res1[$i]['dname']),'/');
			$url1 = $url.$dname;
			$arr = json_decode($this->postData($url1),true);
			if($arr['code']['status'] == 9){
				//调用验证码发送
				A('Chuanglansend')->Fasong('13683285116',$res1[$i]['id']);
			}elseif($arr['code']['status'] == '-2'){
				//调用验证码发送
				A('Chuanglansend')->Fasong('13683285116',0);
			}
			sleep(1);
		}
		
//		$this->TestingDomain11();
	}
	
	//定时检测--qq--域名是否被封（每5分钟一次）
	private function TestingDomain11(){
		//获取系统获取在线的域名
		$Domainname = M('Domainname');
		$where['status'] = 1;
		$where['gid'] = 1;
		$res1 = $Domainname->field('id,dname')->where($where)->select();
		$c = count($res1);
		
		$url = 'http://lj.52jcc.cc/qqapi.php?key=foif8ejduwkljduskjduiudwlkxuewiufjwjfdf&ym=';
		for($i=0;$i<$c;$i++){
			$dname = rtrim(str_replace("http://","",$res1[$i]['dname']),'/');
			$url = $url.$dname;
			$arr = json_decode($this->postData($url),true);
			if($arr['code']['status'] == 2){
				//调用验证码发送
				A('Chuanglansend')->Fasong('13683285116',$res1[$i]['id']);
				A('Chuanglansend')->Fasong('17346510912',$res1[$i]['id']);
			}elseif($arr['code']['status'] == '-2'){
				//调用验证码发送
				A('Chuanglansend')->Fasong('13683285116',0);
				A('Chuanglansend')->Fasong('17346510912',0);
			}
		}
	}
	
	//定时检测--微信--域名是否掉备案（每小时一次）
	public function TestingDomainDiaoBA(){
		set_time_limit(0);
		//获取系统获取在线的域名
		$Domainname = M('Domainname');
		$where['status'] = array('lt',3);
		$res1 = $Domainname->field('id,dname,gid,e_type')->where($where)->select();
		$c = count($res1);
		$url = 'http://lj.52jcc.cc/badkv.php?key=foif8ejduwkljduskjduiudwlkxuewiufjwjfdf&ym=';
		for($i=0;$i<$c;$i++){
			$dname = rtrim(str_replace("http://","",$res1[$i]['dname']),'/');
			$url2 = $url.$dname;
			$arr = json_decode($this->postData($url2),true);
			if($arr == 1){
				//调用验证码发送（掉备案）
				A('Chuanglansend')->Fasong2('13683285116',$res1[$i]['id']);
				A('Chuanglansend')->Fasong2('13716817606',$res1[$i]['id']);
			}
			sleep(1);
		}
	}
	
	//使用队列给用户插扣量规则(每1分钟1次)
	public function addUserQueueInfos(){
		set_time_limit(0);
		//获取该队列还有几条数据
    		$count = $this->redis->lLen('WT_AppQueueLists');
    		if($count > 0){
    			$UserDeduction = M('UserDeduction');
			$data = $this->getUserQueueInfo();
			for($i=0;$i<$count;$i++){
				$uid =  $this->redis->lpop('WT_AppQueueLists');
				$data[0]['uid'] = $uid;$data[1]['uid'] = $uid;$data[2]['uid'] = $uid;$data[3]['uid'] = $uid;$data[4]['uid'] = $uid;$data[5]['uid'] = $uid;$data[6]['uid'] = $uid;
				$UserDeduction->addAll($data);
			}
    		}
	}
	
	//获取用户扣量数据
	private function getUserQueueInfo(){
		$data[0]['type'] = 2;$data[0]['start_money'] = 0;$data[0]['end_money'] = 1000;$data[0]['deduction_pre'] = 0.5;$data[0]['create_time'] = time();
		$data[1]['type'] = 2;$data[1]['start_money'] = 1000;$data[1]['end_money'] = 1001;$data[1]['deduction_pre'] = 0.6;$data[1]['create_time'] = time();
		
		$data[2]['type'] = 1;$data[2]['start_money'] = 0;$data[2]['end_money'] = 500;$data[2]['deduction_pre'] = 2;$data[2]['create_time'] = time();
		$data[3]['type'] = 1;$data[3]['start_money'] = 500;$data[3]['end_money'] = 1500;$data[3]['deduction_pre'] = 2;$data[3]['create_time'] = time();
		$data[4]['type'] = 1;$data[4]['start_money'] = 1500;$data[4]['end_money'] = 3000;$data[4]['deduction_pre'] = 1;$data[4]['create_time'] = time();
		$data[5]['type'] = 1;$data[5]['start_money'] = 3000;$data[5]['end_money'] = 5000;$data[5]['deduction_pre'] = 1;$data[5]['create_time'] = time();
		$data[6]['type'] = 1;$data[6]['start_money'] = 5000;$data[6]['end_money'] = 5001;$data[6]['deduction_pre'] = 2;$data[6]['create_time'] = time();
		
		return $data;
	}
	
	//统计每个用户每天赚了多少金币(每天02:05执行)
	public function CountUserEverydayGold(){
		set_time_limit(0);
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$endtime = strtotime(date('Ymd', time()));//今天开始时间戳
		$where['create_time'] = array(array('egt',$starttime),array('lt',$endtime));
		$where['cid'] = 1;
		$UserGoldRecord = M('UserGoldRecord');
		$arr = $UserGoldRecord->field('uid,sum(gold) as gold')->where($where)->group('uid')->select();
		$c = count($arr);
		for($i=0;$i<$c;$i++){
			$data[$i]['uid'] = $arr[$i]['uid'];
			$data[$i]['gold'] = $arr[$i]['gold'];
			$data[$i]['create_time'] = $starttime;
		}
		
		M('CountUseraccountData')->addAll($data);
	}
	
	//清除每小时用户阅读的文章/视频篇数（每小时第1分钟）
	public function CleanUserHourReadNum(){
		set_time_limit(0);
		$keys = $this->redis->keys('WT_AppUserHourReadNum*');
        for ($i = 0; $i < count($keys); $i++) {
            $this->redis->set($keys[$i],null);
        }
	}
	
	//清除参与转盘活动的用户（每天00:00）
	public function CleanUserActivityData(){
		$ActivityTurntable = M('ActivityTurntable');
		$data['num'] = 0;
		$where['num'] = array('neq',0);
		$ActivityTurntable->where($where)->save($data);

		$keys = $this->redis->keys('WT_AppActivity*');
        for ($i = 0; $i < count($keys); $i++) {
            $this->redis->set($keys[$i],null);
        }
	}
	
	//清除每天签到用户（每天00:00）
	public function CleanUserSignInfo(){
		set_time_limit(0);
		$keys = $this->redis->keys('WT_AppisUserSign*');
        for ($i = 0; $i < count($keys); $i++) {
            $this->redis->set($keys[$i],null);
        }
	}
	
	//清除用户每条扣量数据（每天00:00）
	public function CleanUserDeductionInfo(){
		set_time_limit(0);
		$keys = $this->redis->keys('WT_AppUserDeduction*');
        for ($i = 0; $i < count($keys); $i++) {
            $this->redis->set($keys[$i],null);
        }
        //每小时-用户针对JS广告计费了多少次了
        $keys = $this->redis->keys('WT_AppHourAdClic*');
        $this->redis->delete($keys);
	}
	
	//系统金币支出情况（每天01:05）
	public function CountSysGoldData(){
		set_time_limit(0);
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$endtime = $starttime + 86400;
		$UserGoldRecord = M('UserGoldRecord');
		$where['create_time'] = array(array('egt',$starttime),array('lt',$endtime));
		$where['cid'] = array('neq','-1');
		$res = $UserGoldRecord->field('sum(gold) as gold,cid')->where($where)->group('cid')->select();
		
		$c = count($res);
		$countnum = 0;
		for($i=0;$i<$c;$i++){
			$data['type'.$res[$i]['cid']] = $res[$i]['gold'];
			$countnum += $res[$i]['gold'];
		}
		$data['create_time'] = $starttime;
		$data['countnum'] = $countnum;
		M('CountGoldconsumeData')->add($data);
	}
	
	//系统零钱支出情况（每天01:06）
	public function CountSysMoneyData(){
		set_time_limit(0);
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$endtime = $starttime + 86400;
		$UserMoneyRecord = M('UserMoneyRecord');
		$where['create_time'] = array(array('egt',$starttime),array('lt',$endtime));
		$res = $UserMoneyRecord->field('sum(money) as money,cid')->where($where)->group('cid')->select();
		$c = count($res);
		$countnum = 0;
		for($i=0;$i<$c;$i++){
			$data['type'.$res[$i]['cid']] = $res[$i]['money'];
			$countnum += $res[$i]['money'];
		}
		$data['create_time'] = $starttime;
		$data['countnum'] = $countnum;
		M('CountMoneyconsumeData')->add($data);		
	}
	
	//检测硬广数据(1小时1次)
	public function TestingYingguangData(){
		set_time_limit(0);
		$stime = strtotime(date('Ymd', time()));//今天开始时间戳
		$htime = strtotime(date("Y-m-d H",strtotime("-1 hour")).':00:00');//一小时的开始时间
		$etime = $stime + 3600;//一小时的结束时间
		$data = array();
		$str = '';
		
		//获取目前在跑硬广
		$Advertisement = M('Advertisement');
		$where['ad_code'] = 1;
		$res = $Advertisement->field('id,price,create_time')->where($where)->select();
		if(!$res){return;}

		$AdExpenditureRecord = M('AdExpenditureRecord');
		$where1['type'] = 1;
		$c = count($res);
		for($i=0;$i<$c;$i++){
			$where1['aid'] = $res[$i]['id'];
			$where1['create_time'] = array(array('egt',$htime),array('lt',$etime));
			
			//单个ip点击超过5次
			$res1 = $AdExpenditureRecord->field('user_ip,count(id) as num,uid')
						->where($where1)
						->group('uid,user_ip')
						->order('count(id) desc')
						->select();
					
			$c1 = count($res1);
			for($j=0;$j<$c1;$j++){
				if($res1[$j]['num'] > 5){
					$arr['create_time'] = time();
					$arr['uid'] = $res1[$j]['uid'];
					$arr['aid'] = $res[$i]['id'];
					$arr['desc'] = $res1[$j]['user_ip'].'---点击数：'.$res1[$j]['num'];
					$data[] = $arr;
				}else{
					break;
				}
			}
			
			//单个设备点击超过5次
			$res2 = $AdExpenditureRecord->field('equipment_model,count(id) as num,uid')
						->where($where1)
						->group('uid,equipment_model')
						->order('count(id) desc')
						->select();

			$c2 = count($res2);
			for($j=0;$j<$c2;$j++){
				if($res2[$j]['num'] > 5){
					$arr['create_time'] = time();
					$arr['uid'] = $res2[$j]['uid'];
					$arr['aid'] = $res[$i]['id'];
					$arr['desc'] = $res2[$j]['equipment_model'].'---点击数：'.$res2[$j]['num'];
					$data[] = $arr;
				}else{
					break;
				}
			}
			
			//单用户单广告ID一小时用户收入超过7元
			$where2['aid'] = $res[$i]['id'];
			$where2['type'] = 1;
			$where2['create_time'] = array(array('egt',$htime),array('lt',$etime));
			$res3 = $AdExpenditureRecord->field('uid,count(distinct(user_ip)) as num')
						->where($where2)
						->group('uid')
						->order('count(distinct(user_ip)) desc')
						->select();
						
			$c3 = count($res3);
			for($j=0;$j<$c3;$j++){
				if($res3[$j]['num']*$res[$i]['price'] > 700){
					$arr['create_time'] = time();
					$arr['uid'] = $res3[$j]['uid'];
					$arr['aid'] = $res[$i]['id'];
					$arr['desc'] = '---单广告金额1小时(广告单价)：'.$res3[$j]['num']*$res[$i]['price']/100;
					$data[] = $arr;
				}else{
					break;
				}
			}
			
			//单用户单广告ID自然天用户收入超过20元
			$where2['create_time'] = array('egt',$stime);
			$res4 = $AdExpenditureRecord->field('uid,count(distinct(user_ip)) as num')
						->where($where2)
						->group('uid')
						->order('count(distinct(user_ip)) desc')
						->select();
						
			$c4 = count($res4);
			for($j=0;$j<$c4;$j++){
//				if($res4[$j]['num']*$res[$i]['price'] > 3000){
//					$str .= $res4[$j]['uid'].',';
//					$arr['create_time'] = time();
//					$arr['uid'] = $res4[$j]['uid'];
//					$arr['aid'] = $res[$i]['id'];
//					$arr['desc'] = '---单广告金额自然天(广告单价)--系统已拉黑：'.$res4[$j]['num']*$res[$i]['price']/100;
//					$data[] = $arr;
//				}else
				if($res4[$j]['num']*$res[$i]['price'] > 1000){
					$arr['create_time'] = time();
					$arr['uid'] = $res4[$j]['uid'];
					$arr['aid'] = $res[$i]['id'];
					$arr['desc'] = '---单广告金额自然天(广告单价)：'.$res4[$j]['num']*$res[$i]['price']/100;
					$data[] = $arr;
				}else{
					break;
				}
			}
		}
		
		//单用户一天广告总消耗大于30的
		$where5['type'] = 1;
		$where5['create_time'] = array('egt',$stime);
		$res5 = $AdExpenditureRecord->field('uid,sum(price) as price')
					->where($where5)
					->group('uid')
					->order('sum(price) desc')
					->select();
		$c5 = count($res5);
		for($j=0;$j<$c5;$j++){
			if($res5[$j]['price'] > 2999){
				$str .= $res4[$j]['uid'].',';
				$arr['create_time'] = time();
				$arr['uid'] = $res5[$j]['uid'];
				$arr['aid'] = 0;
				$arr['desc'] = '---当日广告总收益(广告单价)：'.$res5[$j]['price']/100;
				$data[] = $arr;
			}else{
				break;
			}
		}
		
		//获取ip>=2的大于9组的
		$res6 = $AdExpenditureRecord->field('count(id) as num,uid')->where($where5)->group('uid,user_ip')->order('count(id) desc')->select();
		$c6 = count($res6);
		for($i=0;$i<$c6;$i++){
			if($res6[$i]['num'] > 1){
				$data6[$res6[$i]['uid']] += 1;
			}else{
				break;
			}
		}
		
		$data7 = array_keys($data6);
		$c7 = count($data7);
		for($i=0;$i<$c7;$i++){
			if($data6[$data7[$i]] > 9){
				$str .= $data7[$i].',';
				$arr['create_time'] = time();
				$arr['uid'] = $data7[$i];
				$arr['aid'] = 0;
				$arr['desc'] = '---当日广告ip>=2有：'.$data6[$data7[$i]];
				$data[] = $arr;
			}
		}
		
		$UserMoneyRecord = M('UserMoneyRecord');
		//获取用户1天硬广收益超过20元
		$where3['create_time'] = array('egt',$stime);
		$where3['cid'] = 7;
		$res4 = $UserMoneyRecord->field('uid,sum(money) as money')->where($where3)->group('uid')->order('sum(money) desc')->select();
	
		$c4 = count($res4);
		for($j=0;$j<$c4;$j++){
			if($res4[$j]['money'] > 500){
				$arr['create_time'] = time();
				$arr['uid'] = $res4[$j]['uid'];
				$arr['aid'] = 0;
				$arr['desc'] = '---当日广告总收益(阅读单价)：'.$res4[$j]['money']/100;
				$data[] = $arr;
			}else{
				break;
			}
		}
		
		if($data){
			M('CountYingguangData')->addAll($data);
		}
		
		//将用户拉黑高价
		$str = rtrim($str,',');
		if($str){
			$blwhere['id'] = array('in',"$str");
			$bldata['is_pullblack'] = 1;
			M('User')->where($blwhere)->save($bldata);
			
			//清除用户前台缓存
			$uids = explode(',',$str);
			$c5 = count($uids);
			for($i=0;$i<$c5;$i++){
				$token = $this->redis->get('WT_AppUserToken'.$uids[$i]);
				$this->redis->set($token,NULL);
				$this->redis->set('WT_AppUserInfos'.$uids[$i],null);
			}
		}
	}
	
	//新注册用户，检测硬广是否显示(每天检测前天的,每天凌晨检测一次)
	public function JcyingguangDisplay(){
		$btime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$atime = $btime - 86400;//前天时间戳
		$ctime = $btime + 86400;//今天开始时间戳

		$where1['a.create_time'] = array(array('egt',$atime),array('lt',$ctime));
		$where1['b.create_time'] = array(array('egt',$atime),array('lt',$btime));
		$where1['b.is_pullblack'] = 1;
		$ArticleReadRecord = M('ArticleReadRecord');
		//获取前天注册用户的文章点击数据
		$arr = $ArticleReadRecord->alias('a')
			->field('count(a.id) as num,a.uid,b.name')
			->join('__USER__ b ON a.uid = b.id','LEFT')
			->where($where1)
			->order('count(a.id) desc')
			->group('a.uid')
			->select();
		$c = count($arr);	
		for($i=0;$i<$c;$i++){//获取ip超过30个的
			if($arr[$i]['num'] >= 30){
				$str1 .= $arr[$i]['uid'].',';
			}
		}
		
		if(!$str1){
			return;
		}
		
		$str1 = rtrim($str1,',');
		
		$UserMoneyRecord = M('UserMoneyRecord');
		$AdExpenditureRecord = M('AdExpenditureRecord');
		//获取总收入
    		$where3['uid'] = array('in',$str1);
    		$where3['cid'] = array('not in','6');
    		$where3['create_time'] = array(array('egt',$atime),array('lt',$ctime));
    		$list2 = $UserMoneyRecord->field('sum(money) as money,uid')->where($where3)->group('uid')->select();
    		
    		$where7['uid'] = array('in',$str1);
    		$where7['type'] = 2;
    		$where7['create_time'] = array(array('egt',$atime),array('lt',$ctime));
    		$list1 = $AdExpenditureRecord->field('sum(money) as money,uid')->where($where7)->group('uid')->select();
		
		$c1 = count($list1);
		$c2 = count($list2);
		$str2 = '';
		for($i=0;$i<$c1;$i++){
			if($list1[$i]['money'] == 0){
				continue;
			}
			for($j=0;$j<$c2;$j++){
				if($list1[$i]['uid'] == $list2[$j]['uid']){
					$price = round($list1[$i]['money'] / $list2[$j]['money'],2);
					$list1[$i]['price'] = $price;
					$list1[$i]['money2'] = $list2[$j]['money'];
					if(0.3 > $price || $price > 6){
						$str2 .= $list1[$i]['uid'].',';
					}
					break;
				}
			}
		}

		//获取当日注册用户
		$User = M('User');
		$uwhere['create_time'] = array(array('egt',$atime),array('lt',$btime));
		$zhuce = $User->field('id')->where($uwhere)->select();
		//执行用户解封
		if($str2){
			$str2 = rtrim($str2,',');
			$uwhere['id'] = array('not in',$str2);
			$uwhere['is_pullblack'] = 1;
			$data['is_pullblack'] = 0;
			$User->where($uwhere)->save($data);
			
			//清除缓存
			$ca = count($zhuce);
			for($i=0;$i<$ca;$i++){
				$this->redis->set('WT_AppUserInfos'.$zhuce[$i]['id'],null);
			}
		}else{
			$uwhere['is_pullblack'] = 1;
			$data['is_pullblack'] = 0;
			$User->where($uwhere)->save($data);
			
			//清除缓存
			$ca = count($zhuce);
			for($i=0;$i<$ca;$i++){
				$this->redis->set('WT_AppUserInfos'.$zhuce[$i]['id'],null);
			}
		}
		
	}
	
}

