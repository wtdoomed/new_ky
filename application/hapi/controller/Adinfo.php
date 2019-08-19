<?php
namespace app\hapi\controller;
use think\Db;

//广告业务
class AdinfoController extends BaseController {
	
	//站内所有广告位-启动获取1次-原生（用到了1、5、6、8；1.5版本之后可以去掉其它）
	public function get_allAdlist(){
		$token = I('post.token');
		$user_info = json_decode($this->redis->get($token), true);//判断用户是否登录
		//判断用户是否登陆
		if($user_info){//已登陆
			$uinfo = $this->getBaseUserInfo($user_info['id']);
			$is_pullblack = $uinfo['is_pullblack'];
		}else{//未登陆
			$is_pullblack = 0;
		}
		
		//获取 站内所有广告
		$list = $this->getAdInfoLists('2,21,16,17,25,23,24,15,34','in',$is_pullblack);
		$lists1 = array();
		$lists2 = array();
		$lists3 = array();
		$lists4 = array();
		$lists5 = array();
		$lists6 = array();
		$lists7 = array();
		$lists8 = array();
		$c = count($list);
		for($i=0;$i<$c;$i++){
			switch ($list[$i]['pid'])
			{
			case 2:
			  $lists1[] = $list[$i];
			  break;
			case 15:
			  $lists2[] = $list[$i];
			  break;
			case 16:
			  $lists3[] = $list[$i];
			  break;
			case 17:
			  $lists4[] = $list[$i];
			  break;
			case 21:
			  $lists5[] = $list[$i];
			  break;
			case 23:
			  $lists6[] = $list[$i];
			  break;
			case 24:
			  $lists7[] = $list[$i];
			  break;
			case 25:
			  $lists8[] = $list[$i];
			  break;
			default:
			  break;
			}
		}
		
		$data['lists1'] = $lists1;
		$data['lists2'] = $lists2;
		$data['lists3'] = $lists3;
		$data['lists4'] = $lists4;
		$data['lists5'] = $lists5;
		$data['lists6'] = $lists6;
		$data['lists7'] = $lists7;
		$data['lists8'] = $lists8;
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data'] = $data;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//站内所有广告位-h5
	public function get_h5allAdlist(){
		$token = I('post.token');
		$user_info = json_decode($this->redis->get($token), true);//判断用户是否登录
		//判断用户是否登陆
		if($user_info){//已登陆
			$uid = $user_info['id'];
			$uinfo = $this->getBaseUserInfo($user_info['id']);
//			$is_pullblack = $uinfo['is_pullblack'];//打开即可恢复拉黑高价的看不到广告
			$is_pullblack = 0;
		}else{//未登陆
			$uid = 0;
			$is_pullblack = 0;
		}
		
		//获取 站内所有广告
		$list = $this->getAdInfoLists('2,21,16,17,15,24,34','in',$is_pullblack);
		$lists1 = array();
		$lists2 = array();
		$lists3 = array();
		$lists4 = array();
		$lists5 = array();
		$lists7 = array();
		$lists9 = array();
		$c = count($list);
		for($i=0;$i<$c;$i++){
			switch ($list[$i]['pid'])
			{
			case 2:
			  $lists1[] = $list[$i];
			  break;
			case 15:
			  $lists2[] = $list[$i];
			  break;
			case 16:
			  $lists3[] = $list[$i];
			  break;
			case 17:
			  $lists4[] = $list[$i];
			  break;
			case 21:
			  $lists5[] = $list[$i];
			  break;
			case 24:
			  $lists7[] = $list[$i];
			  break;
			case 34:
			  $lists9[] = $list[$i];
			  break;
			default:
			  break;
			}
		}
		
		$data['lists1'] = $lists1;
		$data['lists2'] = $lists2;
		$data['lists3'] = $lists3;
		$data['lists4'] = $lists4;
		$data['lists5'] = $lists5;
		$data['lists7'] = $lists7;
		$data['lists9'] = $lists9;
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data'] = $data;
		$ajaxReturn['data']['login_type'] = $uid;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//扣除广告费－(站外)
	public function DeductionOtherAdMoney(){
		$aid = I('post.aid','');//广告id
		$uid = I('post.uid','');//用户id
		$tid = I('post.tid','');//文章&视频id
		if(!$aid || !$uid){return;}
		if($tid == ''){
			$tid = 0;
		}
		$ip = get_client_ip(0,true);//获取客户端的ip
		$time = strtotime(date('Ymd', time()));//今天开始时间戳
		$phoneinfo = $_SERVER['HTTP_USER_AGENT'];
		
		//获取广告信息
		$redisinfo = json_decode($this->redis->get('WT_AppADDetailInfo'.$aid),true);
		if($redisinfo){
			$adinfo = $redisinfo;
		}else{
			//获取详情信息
			$Advertisement = M('Advertisement');
			$where['id'] = $aid;
			$adinfo = $Advertisement->field('id,aid,pid,desc,name,title,litpic1,ad_type,ad_url,share_type,shareprice,price,money')->where($where)->find();
			if($adinfo === NULL){
				$ajaxReturn['code'] = 500;
				$ajaxReturn['msg'] = '失败，请稍后重试！';
				$this->ajaxReturn($ajaxReturn);
			}else{
				//存入redis里
				$this->redis->set('WT_AppADDetailInfo'.$aid,json_encode($adinfo));
			}
		}
		
		//判断该ip自然天内有没有点击过(如果没有点击过，扣除广告费)
    		$AdExpenditureRecord = M('AdExpenditureRecord');
    		$where2['aid'] = $aid;
    		$where2['user_ip'] = $ip;
    		$where2['create_time'] = array('egt',$time);
    		$res2 = $AdExpenditureRecord->where($where2)->find();
    		if($res2 === NULL){
    			$Advertisement = M('Advertisement');
			$where['id'] = $aid;
			$res = $Advertisement->where($where)->setDec('surplus_money',$adinfo['price']);//扣除广告费
			if($res){
				//添加记录
				$AdExpenditureRecord = M('AdExpenditureRecord');
				$data['aid'] = $aid;
				$data['pid'] = $adinfo['pid'];
				$data['price'] = $adinfo['price'];
				$data['money'] = $adinfo['money'];
				$data['uid'] = $uid;
				$data['equipment_model'] = $phoneinfo;
				$data['tid'] = $tid;
				$data['type'] = 2;
				$data['user_ip'] = $ip;
				$data['create_time'] = time();
				$arr = $AdExpenditureRecord->add($data);
				if($arr){
					$ajaxReturn['code'] = 200;
					$ajaxReturn['msg'] = 'SUCCESS';
				}else{
					$ajaxReturn['code'] = 500;
					$ajaxReturn['msg'] = '失败，请稍后重试！';
				}
			}else{
				$ajaxReturn['code'] = 500;
				$ajaxReturn['msg'] = '失败，请稍后重试！';
			}
    		}else{
    			//添加记录
			$AdExpenditureRecord = M('AdExpenditureRecord');
			$data['aid'] = $aid;
			$data['pid'] = $adinfo['pid'];
			$data['price'] = 0;
			$data['money'] = 0;
			$data['uid'] = $uid;
			$data['equipment_model'] = $phoneinfo;
			$data['tid'] = $tid;
			$data['type'] = 2;
			$data['user_ip'] = $ip;
			$data['create_time'] = time();
			$AdExpenditureRecord->add($data);
			$ajaxReturn['code'] = 200;
			$ajaxReturn['msg'] = 'SUCCESS';
    		}
		
		$this->ajaxReturn($ajaxReturn);
	}
	
	//JS-广告位点击上报
	public function addAdPositionClickInfo(){
		$uid = I('post.uid');//uid
		$pid = I('post.pid');//广告位id
		$aid = I('post.aid');//广告id
		if(!$aid || !$uid || !$pid){return;}
		$tid = I('post.tid');//文章id
		if(!$tid){
			$tid = 0;
		}
		$ip = get_client_ip(0, true);//用户ip
		$phoneinfo = $_SERVER['HTTP_USER_AGENT'];
		$time = strtotime(date('Ymd', time()));//今天开始时间戳
		$h = date('H',time());//获取小时
		$fzh = date('i',time());//获取分钟
		if($fzh > 30){
			$fzh = 1;
		}else{
			$fzh = 2;
		}
		//每小时-用户针对所有广告计费了多少次了
		$this->redis->incr('WT_AppHourAdClic'.$h.$fzh.$uid);
		
		$AdExpenditureRecord = M('AdExpenditureRecord');
		//判断该ip自然天内点击过几次广告了
    		$where2['user_ip'] = $ip;
    		$where2['create_time'] = array('egt',$time);
    		$res2 = $AdExpenditureRecord->where($where2)->count();
    		if($res2 < 2){//没超过2次
    			$data['aid'] = $aid;
			$data['pid'] = $pid;
			$data['price'] = 30;
			$data['money'] = 30;
			$data['uid'] = $uid;
			$data['equipment_model'] = $phoneinfo;
			$data['tid'] = $tid;
			$data['type'] = 2;
			$data['user_ip'] = $ip;
			$data['create_time'] = time();
    		}else{//已超过2次
    			$data['aid'] = $aid;
			$data['pid'] = $pid;
			$data['price'] = 0;
			$data['money'] = 0;
			$data['uid'] = $uid;
			$data['equipment_model'] = $phoneinfo;
			$data['tid'] = $tid;
			$data['type'] = 2;
			$data['user_ip'] = $ip;
			$data['create_time'] = time();
    		}
		
		$result = $AdExpenditureRecord->add($data);
		if($result){
			$ajaxReturn['code'] = 200;
			$ajaxReturn['msg'] = 'SUCCESS';
		}else{
			$ajaxReturn['code'] = 500;
			$ajaxReturn['msg'] = '失败，请稍后重试！';
		}
		$this->ajaxReturn($ajaxReturn);
	}
	
}

