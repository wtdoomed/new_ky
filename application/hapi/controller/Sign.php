<?php
namespace app\hapi\controller;
use think\Db;

//签到模块
class SignController extends BaseController {
	
	//加载签到页
	public function postActivityInfo(){
//		$platform_type = I('post.platform_type');
//      $channel = I('post.channel');
		$token = I('post.token');
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		//获取用户今日是否已签到
		$issign = $this->isUserSign($user_info['id']);
		
		//获取用户的签到天数
		$Sign = M('Sign');
		$where['uid'] = $user_info['id'];
		$res = $Sign->field('count,create_time')->where($where)->find();
		if($res === NULL){
			$c = 0;
		}else{
			//判断用户是否连续签到
			$time = $res['create_time'] + 86400;
            $now_midnight = strtotime(date('Ymd', time()));
            $next_midnight = $now_midnight + 86400;
            if($res['create_time'] >= $now_midnight){
            		$c = $res['count'] % 7;
            }elseif($time >= $now_midnight && $time < $next_midnight){
            		$c = $res['count'] % 7;
            }else{
            		$c = 0;
            }
		}
		
		//获取百宝箱状态 1、敬请期待 2、可抽奖
//		$Box = $this->returnOpenTime($user_info['id']);
		
		$data['signinfo']['count'] = $c;//已签到天数 0表示签满7天/未签到过 
		$data['signinfo']['code'] = $issign['code'];//用户今日是否已签到 200未签到 600已签到
//		$data['boxinfo'] = $Box;//百宝箱信息
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data'] = $data;
		
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取签到奖励对应的金币
	private function getSignGold($c){
		if($c == 1){$gold = 50;}elseif($c == 2){$gold = 100;}elseif($c == 3){$gold = 150;}elseif($c == 4){$gold = 200;}elseif($c == 5){$gold = 250;}elseif($c == 6){$gold = 300;}elseif($c == 0){$gold = 350;}else{$gold = 0;}
		return $gold;
	}
	
	//签到接口
	public function userSign(){
		$platform_type = I('post.platform_type');
//      $channel = I('post.channel');
		$token = I('post.token');
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		//获取用户今日是否已签到
		$issign = $this->isUserSign($user_info['id']);
		if($issign['code'] == 600){
			$ajaxReturn['code'] = 600;
        		$ajaxReturn['msg'] = '今日已签到！';
        		$this->ajaxReturn($ajaxReturn);
		}
		
		//获取用户的签到天数
		$Sign = M('Sign');
		$where['uid'] = $user_info['id'];
		$signcount = $Sign->field('count,create_time')->where($where)->find();
		if($signcount === NULL){
			$c = 1;
			$status = 0;//非连续签到
		}else{
			//判断用户是否连续签到
			$time = $signcount['create_time'] + 86400;
            $now_midnight = strtotime(date('Ymd', time()));
            $next_midnight = $now_midnight + 86400;
            if($time >= $now_midnight && $time < $next_midnight){
            		$c = $signcount['count'] % 7 + 1;
            		if($c == 7){$c = 0;}
            		$status = 1;//连续签到
            }else{
            		$c = 1;
            		$status = 0;//非连续签到
            }
		}
		
		//获取要赠送的金币
        $coins = $this->getSignGold($c);
        
        $Model = M('Sign');
        $where['uid'] = $user_info['id'];
        if($signcount){
        		//判断是否连续签到
            if($status){
            		$arr = array(
					'count'=>array('exp','count+1'),
					'gold'=>array('exp','gold+'.$coins),
					'create_time'=>array('exp',time()),
				);
            }else{
            		$arr = array(
					'count'=>array('exp',1),
					'gold'=>array('exp','gold+'.$coins),
					'create_time'=>array('exp',time()),
				);
            }
            $res = $Model->where($where)->save($arr);
        }else{
        		//添加签到记录
            $data['uid'] = $user_info['id'];
            $data['create_time'] = time();
            $data['gold'] = $coins;
            $res = $Model->add($data);
        }
        
        if($res){
        		//给用户加金币
            $UserAccount = M('UserAccount');
            $arr1 = array(
				'total_gold'=>array('exp','total_gold+'.$coins),
				'gold'=>array('exp','gold+'.$coins)
			);
            $res = $UserAccount->where($where)->save($arr1);
            
            //获取用户账户信息
            $user_account = $this->get_user_account_info($user_info['id']);
            
            //添加用户金币流水记录
            $UserGoldRecord = M('UserGoldRecord');
            $data1['uid'] = $user_info['id'];
            $data1['cid'] = 2;
            $data1['gold'] = $coins;
            $data1['after_gold'] = $user_account['gold'];
            $data1['create_time'] = time();
            $res = $UserGoldRecord->add($data1);
            
            //添加大表记录
            parent::addDataRecord(3,$user_info['id'],$coins,0,$platform_type,$user_info['channel']);
            
            //设置redis已签到
            $this->redis->set('WT_AppisUserSign'.$user_info['id'],1);
            
            $ajaxReturn['code'] = 200;
        		$ajaxReturn['msg'] = 'SUCCESS';
        		$ajaxReturn['data']['count'] = $c;
        }else{
        		$ajaxReturn['code'] = 500;
        		$ajaxReturn['msg'] = '失败，请稍后重试！';
        }
        $this->ajaxReturn($ajaxReturn);
	}
	
	//判断是否签到
    private function isUserSign($user_id)
    {
//  		$redisinfo = $this->redis->get('WT_AppisUserSign'.$user_id);
//  		if($redisinfo){
//  			$ajaxReturn['code'] = 600;//您今天已签到！
//  		}else{
//  			$ajaxReturn['code'] = 200;//未签到，可以签到
//  		}
        $Model = M('Sign');
        $start_time = strtotime(date('Ymd', time()));
        $end_time = $start_time + 86400;
        $where['uid'] = $user_id;
        $where['create_time'] = [['egt', $start_time], ['lt', $end_time]];
        $result = $Model->field('id')->where($where)->find();
        if(!$result){
            $ajaxReturn['code'] = 200;//未签到，可以签到
        }else{
            $ajaxReturn['code'] = 600;//您今天已签到！
        }
        return $ajaxReturn;
    }
    
    //返回百宝箱时间
    protected function returnOpenTime($uid)
    {
        $time1 = strtotime(date('6:0:0'));
        $time2 = strtotime(date('8:0:0'));
        $time3 = strtotime(date('12:0:0'));
        $time4 = strtotime(date('13:0:0'));
        $time8 = strtotime(date('17:0:0'));
        $time9 = strtotime(date('18:0:0'));
        $time5 = strtotime(date('20:0:0'));
        $time6 = strtotime(date('21:0:0'));
        $time10 = strtotime(date('23:0:0'));
        $time11 = strtotime(date('23:59:59'));
        $time7 = strtotime(date('Ymd', strtotime('+1 day')));
        $where['cid'] = 3;
        $where['uid'] = $uid;
        if (time() < $time1) {
            $view_time = '今天 6:00 ~ 8:00';
            $status = 1;
        } elseif (time() < $time3 && time() > $time2) {
            $view_time = '今天 12:00 ~ 13:00';
            $status = 1;
        } elseif (time() < $time8 && time() > $time4) {
            $view_time = '今天 17:00 ~ 18:00';
            $status = 1;
        } elseif (time() < $time5 && time() > $time9) {
            $view_time = '今天 20:00 ~ 21:00';
            $status = 1;
        } elseif (time() < $time10 && time() > $time6) {
            $view_time = '今天 23:00 ~ 00:00';
            $status = 1;
        } elseif (time() > $time11 && time() < $time7) {
            $view_time = '明天 6:00 ~ 8:00';
            $status = 1;
        } elseif (time() >= $time1 && time() < $time2) {
            $where['create_time'] = [['egt', $time1], ['lt', $time2]];
            $user = M('UserGoldRecord')->where($where)->find();
            if ($user) {
                $view_time = '今天 12:00 ~ 13:00';
                $status = 1;
            } else {
                $status = 2;
            }
        } elseif (time() >= $time3 && time() < $time4) {
            $where['create_time'] = [['egt', $time3], ['lt', $time4]];
            $user = M('UserGoldRecord')->where($where)->find();
            if ($user) {
                $view_time = '今天 17:00 ~ 18:00';
                $status = 1;
            } else {
                $status = 2;
            }
        } elseif (time() >= $time8 && time() < $time9) {
            $where['create_time'] = [['egt', $time8], ['lt', $time9]];
            $user = M('UserGoldRecord')->where($where)->find();
            if ($user) {
                $view_time = '今天 20:00 ~ 21:00';
                $status = 1;
            } else {
                $status = 2;
            }
        } elseif (time() >= $time5 && time() < $time6) {
            $where['create_time'] = [['egt', $time5], ['lt', $time6]];
            $user = M('UserGoldRecord')->where($where)->find();
            if ($user) {
                $view_time = '今天 23:00 ~ 00:00';
                $status = 1;
            } else {
                $status = 2;
            }
        } elseif (time() >= $time10 && time() < $time11) {
            $where['create_time'] = [['egt', $time10], ['lt', $time11]];
            $user = M('UserGoldRecord')->where($where)->find();
            if ($user) {
                $view_time = '明天 7:00 ~ 8:00';
                $status = 1;
            } else {
                $status = 2;
            }
        }
        
        $arr['view_time'] = $view_time;
        $arr['status'] = $status;
        return $arr;
    }
    
    //开启宝箱
    public function openTreasureBox()
    {
        $platform_type = I('post.platform_type');
//      $channel = I('post.channel');
        $token = I('post.token');
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		//获取该用户开启情况
        $status = $this->returnOpenTime($user_info['id']);
        if ($status['status'] == 1) {
            $ajaxReturn['code'] = 700;
            $ajaxReturn['msg'] = $status['view_time'];
            $this->ajaxReturn($ajaxReturn);
        }
        
        $coins = mt_rand(20,50);//获取随机金币
        //给用户加金币
        $user_model = M('UserAccount');
        $where['uid'] = $user_info['id'];
        $arr1 = array(
			'total_gold'=>array('exp','total_gold+'.$coins),
			'gold'=>array('exp','gold+'.$coins)
		);
        $res = $user_model->where($where)->save($arr1);
        
        //获取用户账户信息
        $user_account = $this->get_user_account_info($user_info['id']);
        
        //添加用户金币流水记录
        $UserGoldRecord = M('UserGoldRecord');
        $data1['uid'] = $user_info['id'];
        $data1['cid'] = 3;
        $data1['gold'] = $coins;
        $data1['after_gold'] = $user_account['gold'];
        $data1['create_time'] = time();
        $res = $UserGoldRecord->add($data1);
        
        //添加大表记录
        parent::addDataRecord(10,$user_info['id'],$coins,0,$platform_type,$user_info['channel']);
        
        //获取信息
		$data = $this->getDiscipleShareInfo($user_info);
        
        $ajaxReturn['code'] = 200;
    		$ajaxReturn['msg'] = 'SUCCESS';
    		$ajaxReturn['data']['gold'] = $coins;
    		$ajaxReturn['data']['shareinfo'] = $data;
        $this->ajaxReturn($ajaxReturn);
    }
    
    //获取用户的任务记录
	public function get_UserWelfareInfo(){
		$token = I('post.token');
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		//获取用户信息(获取用户是否关注公众号)
		$uinfo = $this->getBaseUserInfo($user_info['id']);

		$Welfare = M('Welfare');
		$Welfarerecord = M('Welfarerecord');
		
		$redisinfo = $this->redis->get('WT_AppUserDisposableWelfare'.$user_info['id']);
		if($redisinfo){
			$arr = array();
		}else{
			//获取用户的一次性任务信息
			$where['wtype'] = 2;
			$where['uid'] = $user_info['id'];
			$res = $Welfarerecord->field('wid')->where($where)->select();
			
			//获取系统一次性任务
			$where1['type'] = 2;
			$where1['is_dis'] = 1;
			$arr = $Welfare->field('id,name,desc,type,gold,m_type,hot_type,jump_type,cname')->where($where1)->order('jump_type desc')->select();
			$c = count($arr);
			for($i=0;$i<$c;$i++){
				$arr[$i]['ctype'] = 1;//领取
				//判断是否已完成绑定公众号
				if($arr[$i]['id'] == 15){
					if($uinfo['cash_openid'] != ''){
						$arr[$i]['ctype'] = 2;//已完成
						$arr[$i]['cname'] = '已完成';
					}
					continue;
				}
				for($j=0;$j<count($res);$j++){
					if($arr[$i]['id'] == $res[$j]['wid']){
						$arr[$i]['ctype'] = 2;//已完成
						$arr[$i]['cname'] = '已完成';
						break;
					}
				}
			}
			
			$s = 0;
			for($i=0;$i<$c;$i++){
				if($arr[$i]['ctype'] == 2){
					$s += 1;
				}
			}
			if($c == $s){
				$arr = array();
				//存入redis里
				$this->redis->set('WT_AppUserDisposableWelfare'.$user_info['id'],1);
			}
		}
		
		//获取用户的永久性任务信息
		$time = strtotime(date("Y-m-d"));//今天开始时间戳
		$where2['uid'] = $user_info['id'];
		$where2['wtype'] = 1;
		$where2['create_time'] = array('egt',$time);
		$res2 = $Welfarerecord->field('wid')->where($where2)->select();
		
		//获取系统永久性任务
		$where3['type'] = 1;
		$where3['is_dis'] = 1;
		$arr2 = $Welfare->field('id,name,desc,type,gold,m_type,hot_type,jump_type,cname')->where($where3)->order('jump_type desc')->select();
		for($i=0;$i<count($arr2);$i++){
			$arr2[$i]['ctype'] = 1;//领取
			for($j=0;$j<count($res2);$j++){
				if($arr2[$i]['id'] == $res2[$j]['wid']){
					$arr2[$i]['ctype'] = 3;//继续领取
						
					break;
				}
			}
		}
		
		
		//获取广告 签到-底部
		$Adinfo = $this->getAdInfoOne(5);
		
		$data['novice'] = $arr;
		$data['daily'] = $arr2;
		$data['adinfo'] = $Adinfo;
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data'] = $data;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//认识快阅读、阅读时常
	public function userDotasks(){
		$platform_type = I('post.platform_type');
//      $channel = I('post.channel');
        $token = I('post.token');
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$wid = I('post.id');//任务id
		$wtype = I('post.type');//任务的类型
		$gold = I('post.gold');//赠送金币/金额
		$m_type = I('post.m_type');//1人民币2金币
		
		//认识快阅读、阅读时常－任务福利
		parent::addWelfareRecord($wid,$wtype,$gold,$user_info['channel'],$platform_type,$user_info['id'],$m_type);
	}

}

