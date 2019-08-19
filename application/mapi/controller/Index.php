<?php
namespace app\mapi\controller;
use think\Db;

class IndexController extends BaseController {
	
	//获取被封原因
	public function getUserCause(){
		
		//获取缓存
		$redisinfo = json_decode($this->redis->get('WT_AppUserCauseLists'),true);
		if($redisinfo){
			$data = $redisinfo;
		}else{
			$data[0]['id'] = 1;$data[0]['name'] = '刷普通文章';
			$data[1]['id'] = 2;$data[1]['name'] = '刷高价文章';
			$data[2]['id'] = 3;$data[2]['name'] = '刷阅读金币';
			$data[3]['id'] = 4;$data[3]['name'] = '文章点击数异常';
			$data[4]['id'] = 5;$data[4]['name'] = '文章设备异常';
			$data[5]['id'] = 6;$data[5]['name'] = '短时间增量异常';
			$data[6]['id'] = 7;$data[6]['name'] = '高价点击数异常';
			$data[7]['id'] = 8;$data[7]['name'] = '高价设备异常';
			$data[8]['id'] = 9;$data[8]['name'] = '任务刷徒1元';
			$data[9]['id'] = 10;$data[9]['name'] = '广告费亏损严重';
			$data[10]['id'] = 11;$data[10]['name'] = '异常徒弟较多';
			$data[11]['id'] = 12;$data[11]['name'] = '徒弟活跃度低';
			$data[12]['id'] = 13;$data[12]['name'] = '刷徒弟阅读奖励';	
			$data[13]['id'] = 14;$data[13]['name'] = '系统自动检测';	
			$data[14]['id'] = 15;$data[14]['name'] = '收益百分比<5%';	
			$data[15]['id'] = 16;$data[15]['name'] = '活跃比<10%';
			$data[16]['id'] = 17;$data[16]['name'] = '同1个手机型号，设备号不一样的';
			$data[17]['id'] = 18;$data[17]['name'] = '文章，单个设备大于100';
			$data[18]['id'] = 19;$data[18]['name'] = '文章，5个设备点击大于50';
			$data[19]['id'] = 20;$data[19]['name'] = '硬广，单个设备大于100';
			$data[20]['id'] = 21;$data[20]['name'] = '硬广，5个设备点击大于50';
			
			//存入redis里
			$this->redis->set('WT_AppUserCauseLists',json_encode($data));
		}
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $data;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取文章视频类别
	public function gerArticleClassLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$pid = I('post.type');//类型：1文章 2视频
		
		$Class = M('Class');
		$where['pid'] = $pid;
		$List = $Class->field('id,name')->where($where)->select();
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取广告商筛选
	public function getAdverLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$Advertiser = M('Advertiser');
		$List = $Advertiser->field('id,name')->select();
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取广告位筛选
	public function getAdposiLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$AdPosition = M('AdPosition');
		$List = $AdPosition->field('id,name')->select();
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取广告类型筛选
	public function getAdclassLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$AdClass = M('AdClass');
		$List = $AdClass->field('id,name')->select();
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//根据广告位id获取广告
	public function getAdListsByclassid(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$pid = I('post.pid');//广告位id
		$where['pid'] = $pid;
		$Advertisement = M('Advertisement');
		$List = $Advertisement->field('id,title')->where($where)->select();
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取系统渠道信息
	public function getChannelLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$Channel = M('Channel');
		$List = $Channel->select();
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//用户登录
	public function userLogin(){
		$username = I('post.username');//账号
        $password = getPwdEncodeString(I('post.password'));//密码
        $this->CheckParam($username);//判断参数是否缺失
        
        $User = M('AdminUser');
        $where['username'] = $username;
        $userinfo = $User->field('id,status,password,type')->where($where)->find();
        //判断账号是否存在
        if(!$userinfo){
        		$ajaxReturn['code'] = 501;
        		$ajaxReturn['msg'] = '该账号不存在！';
        		$this->ajaxReturn($ajaxReturn);
        }
        //判断账号是否封停
        if($userinfo['status'] == 0){
        		$ajaxReturn['code'] = 502;
        		$ajaxReturn['msg'] = '该账号已封停！';
        		$this->ajaxReturn($ajaxReturn);
        }
        //判断密码是否正确
        if($password != '' && $userinfo['password'] === $password){
        		//封装数组，将用户信息存到Redis里
        		$token = md5('admin'.time().$userinfo['id']);
            $data['id'] = $userinfo['id'];
            $redisResult = $this->redis->set($token, json_encode($data),86400*60);
            
            if($redisResult){
                $ajaxReturn['code'] = 200;
                $ajaxReturn['msg'] = 'SUCCESS';
                $ajaxReturn['data']['token'] = $token;
                $ajaxReturn['data']['type'] = $userinfo['type'];
            }else{
                $ajaxReturn['code'] = 500;
                $ajaxReturn['msg'] = '登录失败,请稍后重试!';
            }
        }else{
        		$ajaxReturn['code'] = 503;
        		$ajaxReturn['msg'] = '帐号或密码错误！';
        }
        
        $this->ajaxReturn($ajaxReturn);
	}
	
	//用户退出
	public function userLoginOut(){
		$token = I('post.token');    
		$this->CheckParam($token);//判断参数是否缺失		
        $this->redis->set($token,null);
        if(!($this->redis->get($token))){
            $ajaxReturn['code'] = 200;
            $ajaxReturn['msg'] = 'SUCCESS';
        }else{
            $ajaxReturn['code'] = 500;
            $ajaxReturn['msg'] = '失败,请稍后重试!';
        }
        $this->ajaxReturn($ajaxReturn);
	}

}

