<?php
/**
 * Created by PhpStorm.
 * User: wangtong
 * Date: 18/03/19
 * Time: 下午16:04
 */
namespace app\hapi\controller;
use think\Db;

//用户信息管理
class UserinfoController extends BaseController
{
    
    //构造方法
    public function __construct(){
        parent::__construct();
    }
    
    //获取用户信息
    public function getUserInfos(){
        $token = I('post.token');
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		//获取缓存信息
		$res = $this->getBaseUserInfo($user_info['id']);
		
		//获取用户账户信息
		$user_account = $this->get_user_account_info($user_info['id']);
		
		//获取收徒要分享出去的信息
		$shareInfo = $this->getDiscipleShareInfo($user_info);
		
		//获取是否完成新手任务
//		$redisinfo = $this->redis->get('WT_AppUserDisposableWelfare'.$user_info['id']);
//		if($redisinfo !== false){
			$res['iswelfare'] = 1;//已完成
//		}else{
//			$res['iswelfare'] = 0;//未完成
//		}
		
		$data['userinfo'] = $res;
		$data['useraccount'] = $user_account;
		$data['shareinfo'] = $shareInfo;
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data'] = $data;
		
		$this->ajaxReturn($ajaxReturn);
    }
    
    //修改用户信息
    public function setUserInfos(){
    		$name = I('post.name');//用户名
    		$sex = I('post.sex');//性别1男0女2未知
    		$token = I('post.token');
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$data['name'] = $name;
		$data['sex'] = $sex;
		$where['id'] = $user_info['id'];
		$User = M('User');
		$User->where($where)->save($data);
		
		//清除用户缓存
		A("Clearcache")->clearUserInfo($user_info['id']);
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$this->ajaxReturn($ajaxReturn);
    }
    
    //获取用户收徒排行榜
    public function getUserDiscipleRankList(){
    		$type = I('post.type');//1周排行2月排行
    		$token = I('post.token');
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		if($type == 1){
			$redisinfo[0]['uid'] = 'KY1***10';
			$redisinfo[0]['money'] = 1983.12;
			$redisinfo[0]['num'] = 561;
			$redisinfo[0]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTJKocvf2W6uLwFH6NK1nvBR4aaG2sZPXaPQiaX87N5U4mILic5nCtCEpsuiaSno2gQZbpBD5icMYwhnwg/132';
			$redisinfo[0]['name'] = '时光';
			
			$redisinfo[1]['uid'] = 'KY2***73';
			$redisinfo[1]['money'] = 1769.24;
			$redisinfo[1]['num'] = 584;
			$redisinfo[1]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/kbfN1Pvr5ra2TEanApGAcTLQqBEFw1vy2EJoafFwTguNerBK5uBRh5h0UiaOwAGrvKNHg8txNoZEeibo7AzYePXw/132';
			$redisinfo[1]['name'] = '梦的天空';
			
			$redisinfo[2]['uid'] = 'KY1***13';
			$redisinfo[2]['money'] = 1516.00;
			$redisinfo[2]['num'] = 603;
			$redisinfo[2]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/pPcZUgXaKvvXfAj0qzhsfSVBehibdLQw48DukDy18VCduErLic86kUP2F25wiabiaVN9jMsIT7WUlkp3CDgv1R2agw/132';
			$redisinfo[2]['name'] = '梦梦';
			
			$redisinfo[3]['uid'] = 'KY1***17';
			$redisinfo[3]['money'] = 1433.18;
			$redisinfo[3]['num'] = 513;
			$redisinfo[3]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKCWfIBicEwS3UyrL7GooNtiaVSVSfDKxK5DVXXdVgicFawnV6QON7CrO1npbO8hBaF69Vqib7IkrsE0g/132';
			$redisinfo[3]['name'] = '小苹果';
			
			$redisinfo[4]['uid'] = 'KY3***13';
			$redisinfo[4]['money'] = 1399.97;
			$redisinfo[4]['num'] = 487;
			$redisinfo[4]['litpic'] = 'http://t.cn/RuHQPZF';
			$redisinfo[4]['name'] = '哈哈';
			
			$redisinfo[5]['uid'] = 'KY3***76';
			$redisinfo[5]['money'] = 1200.17;
			$redisinfo[5]['num'] = 456;
			$redisinfo[5]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTIYqsIf6DvaUquKhCS179ZJTxM3ZHKQutDWezwzBXMaiaG3Blibr0BSG11vqZ2GEEJzAYpTk5icHl5yw/132';
			$redisinfo[5]['name'] = '蛋清居士';
			
			$redisinfo[6]['uid'] = 'KY2***16';
			$redisinfo[6]['money'] = 1108.24;
			$redisinfo[6]['num'] = 430;
			$redisinfo[6]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/ulxll5jeojlzFrhTrwh4diaQ0OAMibBibZ9ZsWQCibwKrQXicnxB5hPXlNr5lzkVF9QNrB7oA3jf3jfUMy0cNiaWV7CQ/132';
			$redisinfo[6]['name'] = '宾媚人';
			
			$redisinfo[7]['uid'] = 'KY3***96';
			$redisinfo[7]['money'] = 1032.26;
			$redisinfo[7]['num'] = 408;
			$redisinfo[7]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/yEN1r2ibdsGfYjZuibqibXvRysAtB83unQUb9alGic077ePq0Y9aVpciaIiaxkb4ulEWdJb5OGktwIpzGBqUdOiaeQtUA/132';
			$redisinfo[7]['name'] = '六块五';
			
			$redisinfo[8]['uid'] = 'KY3***16';
			$redisinfo[8]['money'] = 972.19;
			$redisinfo[8]['num'] = 392;
			$redisinfo[8]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKubY3N3icaicfpjLdicyAtc5mGiatKmGSJAkHMfXnjQoNjr0hbaXpI4WM9ntSfsrE6szPhykWmQx1icVg/132';
			$redisinfo[8]['name'] = '羽';
			
			$redisinfo[9]['uid'] = 'KY2***92';
			$redisinfo[9]['money'] = 861.76;
			$redisinfo[9]['num'] = 378;
			$redisinfo[9]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTIVp9JpAujLPTzcRYiaNQ2Y7gn5mJ4wbVDj3EP8Mm5o2hDM1IDWggKxpxYB0RKzLLicoAWrLsBqZ5eg/132';
			$redisinfo[9]['name'] = '惊鲵';
			
			$redisinfo[10]['uid'] = 'KY3***13';
			$redisinfo[10]['money'] = 820.57;
			$redisinfo[10]['num'] = 360;
			$redisinfo[10]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTJ4kV1MOgeWbR33glq8oFNEoqm7O4odWjr9axnP77OCj6cvbW5G8HGhfsN3YT5m2sngk4DZ4Zib1Bg/132';
			$redisinfo[10]['name'] = '宝儿。';
			
			//获取缓存信息
//			$redisinfo = json_decode($this->redis->get('WT_AppDiscipleWeekRank'),true);
//			if($redisinfo === NULL){
//				$redisinfo = array();
//			}
		}else{
			$redisinfo[0]['uid'] = 'KY2***73';
			$redisinfo[0]['money'] = 6789.24;
			$redisinfo[0]['num'] = 2349;
			$redisinfo[0]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/kbfN1Pvr5ra2TEanApGAcTLQqBEFw1vy2EJoafFwTguNerBK5uBRh5h0UiaOwAGrvKNHg8txNoZEeibo7AzYePXw/132';
			$redisinfo[0]['name'] = '梦的天空';
			
			$redisinfo[1]['uid'] = 'KY1***79';
			$redisinfo[1]['money'] = 6028.27;
			$redisinfo[1]['num'] = 2180;
			$redisinfo[1]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/DYAIOgq83eq5rUjO58EVCtSnK3c0geXNrZwr2If8fV28uAaOkvjRv1vdFPcxYrOFb5bECxr3hRmyrYA427CEEg/132';
			$redisinfo[1]['name'] = '波波';
			
			$redisinfo[2]['uid'] = 'KY1***17';
			$redisinfo[2]['money'] = 5978.81;
			$redisinfo[2]['num'] = 2012;
			$redisinfo[2]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKCWfIBicEwS3UyrL7GooNtiaVSVSfDKxK5DVXXdVgicFawnV6QON7CrO1npbO8hBaF69Vqib7IkrsE0g/132';
			$redisinfo[2]['name'] = '小苹果';
			
			$redisinfo[3]['uid'] = 'KY1***80';
			$redisinfo[3]['money'] = 5680.00;
			$redisinfo[3]['num'] = 1872;
			$redisinfo[3]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/LxNK7lTZtMnzxKl38dL9qupPicNkKEUK8Vw7ebdrD0MvFbCZ7QKM2cyWzicuibqNtVL2QwpbQicF6POicxzMeA7zkZg/132';
			$redisinfo[3]['name'] = '美的窒息';
			
			$redisinfo[4]['uid'] = 'KY3***76';
			$redisinfo[4]['money'] = 5201.71;
			$redisinfo[4]['num'] = 1690;
			$redisinfo[4]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTIYqsIf6DvaUquKhCS179ZJTxM3ZHKQutDWezwzBXMaiaG3Blibr0BSG11vqZ2GEEJzAYpTk5icHl5yw/132';
			$redisinfo[4]['name'] = '蛋清居士';
			
			$redisinfo[5]['uid'] = 'KY2***16';
			$redisinfo[5]['money'] = 5012.42;
			$redisinfo[5]['num'] = 1603;
			$redisinfo[5]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/ulxll5jeojlzFrhTrwh4diaQ0OAMibBibZ9ZsWQCibwKrQXicnxB5hPXlNr5lzkVF9QNrB7oA3jf3jfUMy0cNiaWV7CQ/132';
			$redisinfo[5]['name'] = '宾媚人';
			
			$redisinfo[6]['uid'] = 'KY3***13';
			$redisinfo[6]['money'] = 4700.79;
			$redisinfo[6]['num'] = 1021;
			$redisinfo[6]['litpic'] = 'http://t.cn/RuHQPZF';
			$redisinfo[6]['name'] = '哈哈';
			
			$redisinfo[7]['uid'] = 'KY3***13';
			$redisinfo[7]['money'] = 4480.62;
			$redisinfo[7]['num'] = 921;
			$redisinfo[7]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTJ4kV1MOgeWbR33glq8oFNEoqm7O4odWjr9axnP77OCj6cvbW5G8HGhfsN3YT5m2sngk4DZ4Zib1Bg/132';
			$redisinfo[7]['name'] = '宝儿。';
			
			$redisinfo[8]['uid'] = 'KY2***92';
			$redisinfo[8]['money'] = 4012.67;
			$redisinfo[8]['num'] = 870;
			$redisinfo[8]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTIVp9JpAujLPTzcRYiaNQ2Y7gn5mJ4wbVDj3EP8Mm5o2hDM1IDWggKxpxYB0RKzLLicoAWrLsBqZ5eg/132';
			$redisinfo[8]['name'] = '惊鲵';
			
			$redisinfo[9]['uid'] = 'KY3***16';
			$redisinfo[9]['money'] = 3900.00;
			$redisinfo[9]['num'] = 820;
			$redisinfo[9]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKubY3N3icaicfpjLdicyAtc5mGiatKmGSJAkHMfXnjQoNjr0hbaXpI4WM9ntSfsrE6szPhykWmQx1icVg/132';
			$redisinfo[9]['name'] = '羽';
			
			$redisinfo[10]['uid'] = 'KY3***96';
			$redisinfo[10]['money'] = 3780.01;
			$redisinfo[10]['num'] = 792;
			$redisinfo[10]['litpic'] = 'http://thirdwx.qlogo.cn/mmopen/vi_32/yEN1r2ibdsGfYjZuibqibXvRysAtB83unQUb9alGic077ePq0Y9aVpciaIiaxkb4ulEWdJb5OGktwIpzGBqUdOiaeQtUA/132';
			$redisinfo[10]['name'] = '六块五';
			
			//获取缓存信息
//			$redisinfo = json_decode($this->redis->get('WT_AppDiscipleMonthRank'),true);
//			if($redisinfo === NULL){
//				$redisinfo = array();
//			}
		}
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $redisinfo;
		$this->ajaxReturn($ajaxReturn);
    }
    
    //用户阅读赠送金币
    public function userReadAddGold(){
    		$phoneinfo = $_SERVER['HTTP_USER_AGENT'];
    		$platform_type = I('post.platform_type');
//      $channel = I('post.channel');
    		$tid = I('post.tid');//文章/视频id
    		$token = I('post.token');
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
    		$gold = rand(20,60);//没阅读一篇文章或者视频赠送50金币
		
		//从redis里获取今天收益了多少金币了
		$redisResult = $this->redis->get('WT_AppUserTodayReadGold'.$user_info['id']);
		if($redisResult === NULL){//说明今天第一次赠送书币
			$redisResult = 0;
		}
		//判断该用户今天是否得到赠送超过1000
		if($redisResult >= 1000){
			//获取距离本天结束还有多少秒
			$stime = strtotime(date('Y-m-d'));
			$time = 86400 - (time() - $stime);
			$ajaxReturn['data']['countdown'] = $time;
			$ajaxReturn['data']['gold'] = 0;
			
			$ajaxReturn['code'] = 500;
			$ajaxReturn['msg'] = '失败！';
			$this->ajaxReturn($ajaxReturn);
		}
		
		//获取用户该小时阅读了几篇了
//		$num = $this->redis->get('WT_AppUserHourReadNum'.$user_info['id']);
//		if($num > 8){//本小时已经达到10篇阅读
//			//获取距离本小时结束还有多少秒
//			$stime = strtotime(date('Y-m-d H').':00');
//			$time = 3600 - (time() - $stime);
//			$ajaxReturn['data']['countdown'] = $time;
//			$ajaxReturn['data']['gold'] = 0;
//			
//			$ajaxReturn['code'] = 500;
//			$ajaxReturn['msg'] = '失败，不奖励！';
//			$this->ajaxReturn($ajaxReturn);
//		}else{
//			$this->redis->set('WT_AppUserHourReadNum'.$user_info['id'],$num+1);
			$this->redis->set('WT_AppUserTodayReadGold'.$user_info['id'],$redisResult+$gold);
//		}
		
		//给用户加金币
        $UserAccount = M('UserAccount');
        $where['uid'] = $user_info['id'];
        $arr1 = array(
			'total_gold'=>array('exp','total_gold+'.$gold),
			'gold'=>array('exp','gold+'.$gold)
		);
        $res = $UserAccount->where($where)->save($arr1);
        
        //获取用户账户信息
        $user_account = $this->get_user_account_info($user_info['id']);
        
        //添加用户金币流水记录
        $UserGoldRecord = M('UserGoldRecord');
        $data1['uid'] = $user_info['id'];
        $data1['cid'] = 1;
        $data1['gold'] = $gold;
        $data1['after_gold'] = $user_account['gold'];
        $data1['create_time'] = time();
        $res = $UserGoldRecord->add($data1);
        
        //添加大表记录
        parent::addDataRecord(4,$user_info['id'],$gold,0,$platform_type,$user_info['channel'],0,0,$tid,0,0,$phoneinfo);
		
		if($res){
			$ajaxReturn['code'] = 200;
			$ajaxReturn['msg'] = 'SUCCESS';
			$ajaxReturn['data']['countdown'] = 0;
			$ajaxReturn['data']['gold'] = $gold;
		}else{
			$ajaxReturn['code'] = 500;
			$ajaxReturn['msg'] = '失败，请稍后重试！';
			$ajaxReturn['countdown'] = 0;
			$ajaxReturn['data']['gold'] = 0;
		}
		
		$this->ajaxReturn($ajaxReturn);
    }
    
    //用户每天阅读时长上报表
    public function addUserReadTodayLong(){
    		$read_time = I('post.read_time');//时间
    		$read_long = I('post.read_long');//时常
    		$token = I('post.token');
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$UserReadLong = M('UserReadLong');
		$data['uid'] = $user_info['id'];
		$data['read_long'] = floor($read_long/1000);
		$data['read_time'] = floor($read_time/1000);
		$data['create_time'] = time();
		$res = $UserReadLong->add($data);
		
		if($res){
			$ajaxReturn['code'] = 200;
			$ajaxReturn['msg'] = 'SUCCESS';
		}else{
			$ajaxReturn['code'] = 500;
			$ajaxReturn['msg'] = '失败，请稍后重试！';
		}
		$this->ajaxReturn($ajaxReturn);
    }
    
    //绑定师傅
    public function bindingMasterId(){
        $user_id = I('post.user_id');//师傅user_id
        $one_type = 11;//站内分享入口
		$second_type = 1;//站内分享入口对应的类型
    		$platform_type = I('post.platform_type');
//      $channel = I('post.channel');
    		$token = I('post.token');
    		$device_id = I('post.device_id');//设备号
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		//判断是否绑定自己为师傅
		if($user_info['user_id'] == $user_id){
			$ajaxReturn['code'] = 402;
    			$ajaxReturn['msg'] = '不能绑定自己为师傅！';
    			$this->ajaxReturn($ajaxReturn);
		}
		
    		//判断该用户是否已有师傅
    		$User = M('User');
    		$where['id'] = $user_info['id'];
    		$res1 = $User->field('masterid')->where($where)->find();
    		if($res1['masterid'] > 0){
    			$ajaxReturn['code'] = 400;
    			$ajaxReturn['msg'] = '该用户已绑定师傅！';
    			$this->ajaxReturn($ajaxReturn);
    		}
    		
    		//获取师傅在系统的自增id
		$redisinfo = json_decode($this->redis->get('WT_AppUserUser_id'.$user_id),true);
		if($redisinfo){
			$masterid = $redisinfo['id'];
			$m_device_id = $redisinfo['device_id'];
		}else{
			$where1['user_id'] = $user_id;
			$arr = $User->field('id,device_id')->where($where1)->find();
			if($arr === NULL){
				$ajaxReturn['code'] = 401;
	    			$ajaxReturn['msg'] = '该师傅id不存在！';
	    			$this->ajaxReturn($ajaxReturn);
			}else{
				$masterid = $arr['id'];
				$m_device_id = $arr['device_id'];
				//存入redis里
				$this->redis->set('WT_AppUserUser_id'.$user_id,json_encode($arr));
			}
		}
		
		//判断是否同意设备号
		if($device_id == $m_device_id){
			$ajaxReturn['code'] = 405;
    			$ajaxReturn['msg'] = '同一手机不能互相绑定师徒关系！';
    			$this->ajaxReturn($ajaxReturn);
		}
		
		//判断是否互为师徒
    		$Disciple = M('Disciple');
    		$discwhere['student_uid'] = $masterid;
    		$discwhere['masterid'] = $user_info['id'];
    		$discarr = $Disciple->where($discwhere)->find();
    		if($discarr){
    			$ajaxReturn['code'] = 403;
    			$ajaxReturn['msg'] = '不能互相绑定师徒关系！';
    			$this->ajaxReturn($ajaxReturn);
    		}
    		
    		$discwhere1['student_uid'] = $masterid;
    		$discwhere1['masterfatherid'] = $user_info['id'];
    		$discarr1 = $Disciple->where($discwhere1)->find();
    		if($discarr1){
    			$ajaxReturn['code'] = 403;
    			$ajaxReturn['msg'] = '不能互相绑定师徒关系！';
    			$this->ajaxReturn($ajaxReturn);
    		}
    		
    		//判断他师父有没有师父
    		$Dwhere['student_uid'] = $masterid;
    		$DisInfo = $Disciple->field('masterid')->where($Dwhere)->find();
    		if($DisInfo['masterid'] != '' && $DisInfo['masterid'] > 0){
    			$masterfatherid = $DisInfo['masterid'];
    		}else{
    			$masterfatherid = 0;
    		}
    		
    		//执行师徒绑定
    		$data['student_uid'] = $user_info['id'];
    		$data['masterid'] = $masterid;
    		$data['masterfatherid'] = $masterfatherid;
    		$data['create_time'] = time();
    		$res = $Disciple->add($data);
    		
    		//添加大表收徒记录
    		parent::addDataRecord(5,$user_info['id'],0,0,$platform_type,$user_info['channel'],$masterid,$masterfatherid,0,0,0,0,$one_type,$second_type);
    		
    		//输入师傅邀请码－任务福利
		parent::addWelfareRecord(1,2,1000,$user_info['channel'],$platform_type,$user_info['id'],2);
    		
    		if($res){
    			//修改用户表中的masterid
    			$mdata['masterid'] = $masterid;
    			$User->where($where)->save($mdata);
    			
			//清除用户缓存
			A("Clearcache")->clearUserInfo($user_info['id']);
			
			//清除“收徒-我的好友”缓存
	        $keys = $this->redis->keys('WT_AppDiscipleList'.$masterid.'*');
	        for ($i = 0; $i < count($keys); $i++) {
	            $this->redis->set($keys[$i],null);
	        }
	        
	        //------------------------------转盘活动------------------------------
	        
	        //获取用户今天有没有获得收徒奖励转盘3次
//			$istrue = $this->redis->get('WT_AppActivityDiscIsTrue'.$masterid);
//			if(!$istrue){
//				//获取用户今天获得几次点击了
//				$num2 = $this->redis->get('WT_AppActivityDiscNum'.$masterid);
//				if($num2 == 2){
//					//设置今日已获得转盘1次
//					$this->redis->set('WT_AppActivityDiscIsTrue'.$masterid,1);
//				}
//				
//				//执行转盘次数添加操作
//				$ActivityTurntable = M('ActivityTurntable');
//				$where2['uid'] = $masterid;
//				$res2 = $ActivityTurntable->where($where2)->find();
//				if($res2){
//					$ActivityTurntable->where($where2)->setInc('num');
//				}else{
//					$data2['uid'] = $masterid;
//					$data2['num'] = 1;
//					$ActivityTurntable->add($data2);
//				}
//				
//				$this->redis->set('WT_AppActivityDiscNum'.$masterid,$num2+1);
//			}
	        
	        //------------------------------转盘活动------------------------------
    			
    			$ajaxReturn['code'] = 200;
    			$ajaxReturn['msg'] = 'SUCCESS';
    		}else{
    			$ajaxReturn['code'] = 500;
    			$ajaxReturn['msg'] = '失败，请稍后重试！';
    		}
    		
    		$this->ajaxReturn($ajaxReturn);
    }
    
    //获取我的收益  收入、提现
    public function getUserProfitLists(){
    		$type = I('post.type');//1收入2提现
    		$page = I('post.page',1);
		$page_size = 10;
    		$token = I('post.token');
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		//只查询3天内的数据
		$starttime = strtotime(date("Y-m-d",strtotime("-2 day")));
		$where['create_time'] = array('egt',$starttime);
		
		if($type == 1){
			$UserMoneyRecord = M('UserMoneyRecord');
			$where['uid'] = $user_info['id'];
			//获取总条数
			$count = $UserMoneyRecord->where($where)->count();
			//总条数除页大小＝总页数（进1取整）
			$total = ceil($count / $page_size);
			//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
			$limitStart = (($page - 1) * $page_size);
			$incomeList = $UserMoneyRecord
							->field('money,create_time,CASE WHEN cid = 1 or cid = 7 THEN "好友阅读" WHEN cid = 2 THEN "收徒奖励" WHEN cid = 3 THEN "新手任务"  WHEN cid = 6 THEN "金币兑换零钱"  WHEN cid = 8 THEN "活动奖励" WHEN cid = 9 THEN "系统赠送" ELSE "徒弟提成" END as name')
							->where($where)
							->order('create_time desc')
							->limit($limitStart,$page_size)
							->select();
			if($incomeList === NULL){
				$incomeList = array();
			}
		}else{
			$UserWithdraw = M('UserWithdraw');
			$where['uid'] = $user_info['id'];
			//获取总条数
			$count = $UserWithdraw->where($where)->count();
			//总条数除页大小＝总页数（进1取整）
			$total = ceil($count / $page_size);
			//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
			$limitStart = (($page - 1) * $page_size);
			$incomeList = $UserWithdraw->alias('a')
							->field('money,create_time,CASE WHEN type = 1 THEN "微信提现" ELSE "话费充值" END as name,CASE WHEN status = 1 THEN "已打款" WHEN status = 2 THEN "未审核" WHEN status = 3 and r_status = 1 THEN "钱已返回你账户，请联系客服" WHEN status = 3 and r_status = 2 THEN "系统检测违规，请联系客服" ELSE "系统处理中，请耐心等待" END as status_name')
							->where($where)
							->order('create_time desc')
							->limit($limitStart,$page_size)
							->select();
			if($incomeList === NULL){
				$incomeList = array();
			}
		}
		
		//获取用户今日收入
		$today_money = $this->getUserTodayMoney($user_info['id']);
		
		//获取总收入
		$total_money = $this->get_user_account_info($user_info['id']);
		
		//获取跳转文章
		$jumpinfo = $this->getJumpInfo();
		
		$ajaxReturn['code'] = 200;
        $ajaxReturn['msg'] = 'SUCCESS';
        $ajaxReturn['data']['lists'] = $incomeList;
        $ajaxReturn['data']['total_page'] = $total;
        $ajaxReturn['data']['today_size'] = $today_money;
        $ajaxReturn['data']['total_size'] = $total_money['total_money'];
        $ajaxReturn['data']['jumpinfo'] = $jumpinfo;
		$this->ajaxReturn($ajaxReturn);
    }
    
    //获取我的金币  收入、支出（金币兑换）
    public function getUserGoldLists(){
    		$type = I('post.type');//1收入2支出（金币兑换）
    		$page = I('post.page',1);
		$page_size = 10;
    		$token = I('post.token');
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		//只查询3天内的数据
		$starttime = strtotime(date("Y-m-d",strtotime("-2 day")));
		$where['create_time'] = array('egt',$starttime);
		
		if($type == 1){
			$UserGoldRecord = M('UserGoldRecord');
			$where['uid'] = $user_info['id'];
			$where['cid'] = array('gt',0);
			//获取总条数
			$count = $UserGoldRecord->where($where)->count();
			//总条数除页大小＝总页数（进1取整）
			$total = ceil($count / $page_size);
			//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
			$limitStart = (($page - 1) * $page_size);
			$incomeList = $UserGoldRecord
							->field('gold as money,create_time,CASE WHEN cid = -1 THEN "金币转换零钱"  WHEN cid = 1 THEN "阅读奖励" WHEN cid = 2 THEN "每日签到" WHEN cid = 3 THEN "开启宝箱" WHEN cid = 5 THEN "反馈奖励" WHEN cid = 6 THEN "活动奖励" WHEN cid = 7 THEN "时段奖励" WHEN cid = 8 THEN "新手注册" WHEN cid = 9 THEN "收徒奖励" ELSE "任务奖励" END as name')
							->where($where)
							->order('create_time desc')
							->limit($limitStart,$page_size)
							->select();
			if($incomeList === NULL){
				$incomeList = array();
			}
		}else{
			$UserGoldRecord = M('UserGoldRecord');
			$where['uid'] = $user_info['id'];
			$where['cid'] = array('lt',0);
			//获取总条数
			$count = $UserGoldRecord->where($where)->count();
			//总条数除页大小＝总页数（进1取整）
			$total = ceil($count / $page_size);
			//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
			$limitStart = (($page - 1) * $page_size);
			$incomeList = $UserGoldRecord
							->field('gold as money,create_time,CASE WHEN cid = -1 THEN "金币转换零钱" ELSE 0 END as name')
							->where($where)
							->order('create_time desc')
							->limit($limitStart,$page_size)
							->select();
			if($incomeList === NULL){
				$incomeList = array();
			}
		}
		
		//获取用户今日收入
		$today_gold = $this->getUserTodayGold($user_info['id']);
		
		//获取总金币
		$total_gold = $this->get_user_account_info($user_info['id']);
		
		//获取跳转文章
		$jumpinfo = $this->getJumpInfo();
		
		$ajaxReturn['code'] = 200;
        $ajaxReturn['msg'] = 'SUCCESS';
        $ajaxReturn['data']['lists'] = $incomeList;
        $ajaxReturn['data']['total_page'] = $total;
        $ajaxReturn['data']['today_size'] = $today_gold;
        $ajaxReturn['data']['total_size'] = $total_gold['total_gold'];
        $ajaxReturn['data']['jumpinfo'] = $jumpinfo;
		$this->ajaxReturn($ajaxReturn);
   	}
   	
   	//获取用户今日收益
   	private function getUserTodayMoney($uid){
   		if(!$uid){
   			return 0;
   		}
   		$UserMoneyRecord = M('UserMoneyRecord');
		$stime = strtotime(date('Ymd', time()));//今天开始时间戳
		$where['uid'] = $uid;
		$where['create_time'] = array('egt',$stime);
		$res = $UserMoneyRecord->where($where)->sum('money');
		if($res === NULL){
			return 0;
		}else{
			return $res;
		}
   	}
   	
   	//获取用户今日金币
   	private function getUserTodayGold($uid){
   		if(!$uid){
   			return 0;
   		}
   		$UserGoldRecord = M('UserGoldRecord');
		$stime = strtotime(date('Ymd', time()));//今天开始时间戳
		$where['uid'] = $uid;
		$where['cid'] = array('egt',0);
		$where['create_time'] = array('egt',$stime);
		$res = $UserGoldRecord->where($where)->sum('gold');
		if($res === NULL){
			return 0;
		}else{
			return $res;
		}
   	}
   	
   	//跳转文章信息
   	protected function getJumpInfo(){
   		$id = 6;
   		//获取文章详情信息的缓存
		$redisinfo = json_decode($this->redis->get('WT_AppGetJumpInfo'.$id),true);
		if($redisinfo){
			$articleinfo = $redisinfo;
		}else{
			//获取详情信息
			$Article = M('Article');
			$where['id'] = $id;
			$articleinfo = $Article->field('id,cid')->where($where)->find();
			if($articleinfo === NULL){
				$articleinfo = array();
			}else{
				//存入redis里
				$this->redis->set('WT_AppGetJumpInfo'.$id,json_encode($articleinfo));
			}
		}
   		return $articleinfo;
   	}
    
}