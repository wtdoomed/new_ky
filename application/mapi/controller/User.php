<?php
namespace app\mapi\controller;
use think\Db;

class UserController extends BaseController {
	
	//获取用户列表
	public function getUserlists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		$uname = I('post.uname');//用户昵称
		if($uname != ''){
			$where['a.name'] = array('like','%'.$uname.'%');
		}
		
		$is_withdraw = I('post.w_status');//提现状态1正常0封号
		if($is_withdraw != ''){
			$where['a.is_withdraw'] = $is_withdraw;
		}
		
		$status = I('post.status');//用户状态1正常2白名单3黑名单4用户封号
		if($status != ''){
			$where['a.status'] = $status;
		}
		
		$is_pullblack = I('post.is_pullblack');//高价状态：1拉黑 0正常
		if($is_pullblack != ''){
			$where['a.is_pullblack'] = $is_pullblack;
		}
		
		$phone = I('post.uphone');//用户绑定手机号
		if($phone != ''){
			$where['a.phone'] = array('like','%'.$phone.'%');
		}
		
		$user_id = I('post.user_id');//用户user_id
		if($user_id != ''){
			$where['a.user_id'] = array('like','%'.$user_id.'%');
		}
		
		$uchannel = I('post.uchannel');//用户渠道
		if($uchannel != ''){
			$where['a.channel'] = $uchannel;
		}
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$User = M('User');
		//获取总条数
		$count = $User->alias('a')->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$userList = $User->alias('a')
						->field('a.id,a.user_id,a.name,a.sex,a.phone,a.masterid,a.status,a.platform_type,a.is_pullblack,a.is_withdraw,a.phone_model,a.device_id,a.channel,a.create_time,a.sealreason,b.total_money,b.user_money,b.total_gold,b.gold,c.name as channel_name,d.create_time as active_time,e.user_id as masterid')
						->join('__USER_ACCOUNT__ b ON a.id = b.uid','LEFT')
						->join('__CHANNEL__ c ON a.channel = c.id','LEFT')
						->join('__USER_ACTIVE_RECORD__ d ON a.id = d.uid','LEFT')
						->join('__USER__ e ON a.masterid = e.id','LEFT')
						->where($where)
						->order('a.create_time desc')
						->limit($limitStart,$page_size)
						->select();
		$c = count($userList);
		for($i=0;$i<$c;$i++){
			$arr = json_decode($this->redis->get('WT_AppUserCauseLists'),true);
			$sealreason = explode(',',$userList[$i]['sealreason']);
			$c1 = count($sealreason);
			$c2 = count($arr);
			$str = '';
			for($j=0;$j<$c1;$j++){
				for($k=0;$k<$c2;$k++){
					if($sealreason[$j] == $arr[$k]['id']){
						$str .= $arr[$k]['name'].'，';
						break;
					}
				}
			}
			
			$userList[$i]['seal_name'] = $str;
		}
		if($userList === NULL){
			$userList = array();
		}
		
		//获取安卓注册数
		$where1['platform_type'] = 1;
		$androidcount = $User->where($where1)->count();
		
		//获取IOS注册数
		$where1['platform_type'] = 2;
		$ioscount = $User->where($where1)->count();
		
		//获取有师傅的用户
		$where2['masterid'] = array('gt',0);
		$discount = $User->where($where2)->count();
		
		//获取绑定手机号的用户
		$where3['phone'] = array('neq','');
		$phonecount = $User->where($where3)->count();
		
		$infos['androidcount'] = $androidcount;
		$infos['ioscount'] = $ioscount;
		$infos['discount'] = $discount;
		$infos['phonecount'] = $phonecount;
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $userList;
		$ajaxReturn['data']['infos'] = $infos;
		$ajaxReturn['data']['total_count'] = $count;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//修改状态--1账号状态2提现状态
	public function setUserStatus(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$uid = I('post.uid');//用户id，多个用逗号分割
		$type = I('post.type');//1账号状态2提现状态
		$status = I('post.status');//账号状态：1正常2白名单3黑名单4用户封号  提现状态：1正常0封号
		$this->CheckParam($type);
		if($type == 1){$data['status'] = $status;}else{$data['is_withdraw'] = $status;}
		
		$User = M('User');
		$where['id'] = array('in',$uid);
		$arr = $User->where($where)->save($data);
		if($arr){
			if($type == 1){
				//清除用户前台缓存
				$uids = explode(',',$uid);
				$c = count($uids);
				for($i=0;$i<$c;$i++){
					$token = $this->redis->get('WT_AppUserToken'.$uids[$i]);
					$this->redis->set($token,NULL);
					$this->redis->set('WT_AppUserInfos'.$uids[$i],null);
				}
			}
			$ajaxReturn['code'] = 200;
            $ajaxReturn['msg'] = "SUCCESS";
		}else{
		    $ajaxReturn['code'] = 500;
            $ajaxReturn['msg'] = "失败，请稍后重试！";
		}
		$this->ajaxReturn($ajaxReturn);
	}
	
	//拉黑用户高价
	public function setUserPullblack(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$uid = I('post.uid');//用户id
		$is_pullblack = I('post.is_pullblack');//是否拉黑高价文章1是0否
		$where['id'] = $uid;
		$data['is_pullblack'] = $is_pullblack;
		$User = M('User');
		$res = $User->where($where)->save($data);
		
		if($res){
			//清除用户缓存
			$this->redis->set('WT_AppUserInfos'.$uid,null);
			
			//清除用户登陆信息
			if($is_pullblack == 1){
				$token = $this->redis->get('WT_AppUserToken'.$uid);
				$this->redis->set($token,NULL);
				$this->redis->set('WT_AppUserInfos'.$uid,null);
			}
			
			$ajaxReturn['code'] = 200;
            $ajaxReturn['msg'] = "SUCCESS";
		}else{
			$ajaxReturn['code'] = 500;
            $ajaxReturn['msg'] = "失败，请稍后重试！";
		}
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取用户扣量管理
	public function getUserDeducLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$uid = I('post.uid');//用户id
		$where['uid'] = $uid;
		$UserDeduction = M('UserDeduction');
		$List = $UserDeduction->field('id,type,start_money,end_money,deduction_pre')->where($where)->select();
		if(!$List){
			$where['uid'] = 0;
			$List = $UserDeduction->field('id')->where($where)->select();
		}
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//修改用户扣量信息
	public function saveUserDeducInfos(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$id = I('post.id');//扣量id
		$start_money = I('post.start_money');
		$end_money = I('post.end_money');
		$deduction_pre = I('post.deduction_pre');
		
		$data['start_money'] = $start_money;
		$data['end_money'] = $end_money;
		$data['deduction_pre'] = $deduction_pre;
		
		$where['id'] = $id;
		$UserDeduction = M('UserDeduction');
		$res = $UserDeduction->where($where)->save($data);
		if($res){
			$ajaxReturn['code'] = 200;
            $ajaxReturn['msg'] = "SUCCESS";
		}else{
		    $ajaxReturn['code'] = 500;
            $ajaxReturn['msg'] = "失败，请稍后重试！";
		}
		$this->ajaxReturn($ajaxReturn);
	}
	
	//设置用户被封原因
	public function setUserSealReason(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		$id = I('post.id');//用户被封原因(多个用逗号分隔开)
		$uid = I('post.uid');//用户id
		
		$User = M('User');
		$where['id'] = $uid;
		$data['sealreason'] = $id;
		$res = $User->where($where)->save($data);
		if($res){
			$ajaxReturn['code'] = 200;
            $ajaxReturn['msg'] = "SUCCESS";
		}else{
		    $ajaxReturn['code'] = 500;
            $ajaxReturn['msg'] = "失败，请稍后重试！";
		}
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取用户问卷调查
	public function getUserInvestigation(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size',10);
		
		$user_id = I('post.user_id');//用户user_id
		if($user_id != ''){
			$where['a.uid'] = array('like','%'.$user_id.'%');
		}
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$User = M('UserInvestigation');
		//获取总条数
		$count = $User->alias('a')->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $User->alias('a')
						->field('a.uid,a.one,a.two,a.three,a.four,a.five,a.desc,a.create_time,b.masterid,b.name')
						->join('__USER__ b ON a.uid = b.id','LEFT')
						->where($where)
						->order('a.create_time desc')
						->limit($limitStart,$page_size)
						->select();
		
		if($List === NULL){
			$List = array();
		}
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$ajaxReturn['data']['total_count'] = $count;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取用户问卷调查数据统计
	public function getInvestigationData(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$UserInvestigation = M('UserInvestigation');
		$res1 = $UserInvestigation->field('count(id) as num,one')->group('one')->order('count(id) desc')->select();
		
		$res2 = $UserInvestigation->field('count(id) as num,two')->group('two')->order('count(id) desc')->select();
		
		$res3 = $UserInvestigation->field('count(id) as num,three')->group('three')->order('count(id) desc')->select();
		
		$arr = $UserInvestigation->field('four,five')->select();
		$c = count($arr);
		for($i=0;$i<$c;$i++){
			$a1 = explode(',',rtrim($arr[$i]['four'],','));
			$ca1 = count($a1);
			for($j=0;$j<$ca1;$j++){
				$res4[$a1[$j]] += 1;
			}
			$a1 = explode(',',rtrim($arr[$i]['five'],','));
			$ca1 = count($a1);
			for($j=0;$j<$ca1;$j++){
				$res5[$a1[$j]] += 1;
			}
		}
		
		$r4 = array_keys($res4);
		$c4 = count($r4);
		for($i=0;$i<$c4;$i++){
			$data4[$i]['num'] = $res4[$r4[$i]];
			$data4[$i]['four'] = $r4[$i];
		}
		
		$r5 = array_keys($res5);
		$c5 = count($r5);
		for($i=0;$i<$c5;$i++){
			$data5[$i]['num'] = $res5[$r5[$i]];
			$data5[$i]['four'] = $r5[$i];
		}
		
		$data['list1'] = $res1;
		$data['list2'] = $res2;
		$data['list3'] = $res3;
		$data['list4'] = $data4;
		$data['list5'] = $data5;
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data'] = $data;
		$this->ajaxReturn($ajaxReturn);
	}
	
}

