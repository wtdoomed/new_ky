<?php
namespace app\hapi\controller;

use think\Controller;
use think\Db;
use think\RedisLink;

//前台父类控制器
class BaseController extends Controller
{
    protected $redis;
    public function __construct()
    {
        parent::__construct();

		//连接redis
        $this->redis = \Think\RedisLink::get_instance();

		//redis前缀
		$this->redis_name = 'CSBG_';

		//外链详情页控制器
		$this->chain_cont = '/Wz666';

		//收徒控制器
		$this->dis_cont = 'Dis222';

		//一、二级返回页控制器
		$this->intercept_cont = '/Intercept';

		//站内图片访问地址
		$this->homepic = 'http://webh5.kuaiyuekeji.com/homepic/';
		
		//给钱阅公众号（提现公众号）
		$this->kyqwAppId = 'wx8e41e9a874959146';
		$this->AppSecret = '334ec4dae3c9926daff27fcda809fcbe';
		
    }
	
	/**
     * 大表数据记录
     * type: 1、登录2、注册3、签到4、内部阅读5、收徒6、做任务7、提现8、打开登录页9、分享文章10、开启宝箱11、分享收徒页12、活跃(打开app)13、内部打开文章/视频详情页
     *
     * 按天分表（每天的开始时间）
     */
	protected function addDataRecord($type=0,$uid=0,$gold=0,$money=0,$platform_type=0,$channel=0,$masterid=0,$masterfatherid=0,$tid=0,$rid=0,$user_ip=0,$device_id=0,$one_type=0,$second_type=0){
		if($channel === NULL){
			$channel = 1;
		}
		//获取今天开始的时间戳
        $t = strtotime(date('Y-m-d', time()));

		//判断表是否存在
		$isTable = db()->query('SHOW TABLES LIKE "Zrecord'.$t.'"');
		if(!$isTable){return 0;}

		$Zrecord = Db::name('Zrecord' . $t);
        $ip = get_client_ip(0, true);//用户ip
        
        //封装数组
        $data['type'] = $type;
        $data['uid'] = $uid;
        $data['gold'] = $gold;
        $data['money'] = $money;
        $data['platform_type'] = $platform_type;
        $data['channel'] = $channel;
        $data['masterid'] = $masterid;
        $data['masterfatherid'] = $masterfatherid;
        $data['tid'] = $tid;
        $data['rid'] = $rid;
        $data['user_ip'] = $ip;
        $data['one_type'] = $one_type;
        $data['second_type'] = $second_type;
        $data['device_id'] = $device_id;
        $data['create_time'] = time();
        //执行添加操作
        $Zrecord->insert($data);
	}

	//检测是否登录
	protected function IsAppLogin($token){
		$redisResult = json_decode($this->redis->get($this->redis_name.$token),true);
		if(!$redisResult){
			$ajaxReturn['code'] = 300;
			$ajaxReturn['msg'] = "登录超时，或未登录！请重新登录";
			echo json_encode($ajaxReturn);die;
		}else{
			return $redisResult;
		}
	}
	
	//判断参数是否缺失
    function CheckParam($param)
    {
        if (!isset($param) || empty($param)) {
            $ajaxReturn['code'] = 600;
            $ajaxReturn['msg'] = '缺失参数!';
            $this->ajaxReturn($ajaxReturn);
        }
        return true;
    }
    
    //获取用户信息
    protected function getBaseUserInfo($uid){
    		//获取缓存信息
		$redisinfo = json_decode($this->redis->get('WT_AppUserInfos'.$uid),true);
		if($redisinfo){
			$res = $redisinfo;
		}else{
			$User = M('User');
			$where['id'] = $uid;
			$res = $User->field('id,user_id,sex,phone,name,litpic,masterid,is_pullblack,cash_openid,status')->where($where)->find();
			if($res === NULL){
				$res = array();
			}else{
				//存入redis里
				$this->redis->set('WT_AppUserInfos'.$uid,json_encode($res));
			}
		}
		return $res;
    }
    
    //获取用户账户信息
    protected function get_user_account_info($uid){
		if(!$uid){
			return array();
		}
		$UserAccount = M('UserAccount');
		$where['uid'] = $uid;
		$res = $UserAccount->field('total_money,user_money,total_gold,gold')->where($where)->find();

		return $res;
    }
    
    //获取收徒要分出去的信息
    protected function getDiscipleShareInfo($user_info){
		//分享标题
		$title[0] = '太牛了！看条新闻就能赚钱，快来试试，我提现了18元！';
		$title[1] = '这么壕气的APP还是第一次看到，来了就送最高18元现金，可微信提现！';
		$title[2] = '快阅读又在发钱了！只要来了就送最高18元现金，我已经领好了，是真的！';
		$title[3] = '白送钱要不要？要就快去快阅读，最高有18元，可微信提现！';
		$title[4] = '快阅读，邀请你加入“福利发放”，最高领取18元，可微信提现！';
		
		//分享内容
		$desc[0] = '不可思议，使用这款软件居然能赚到万元！';
		$desc[1] = '我已经赶紧提现到微信了，大家也赶快去领吧！';
		$desc[2] = '不知道活动什么时候结束，大家赶紧趁早去领吧！';
		$desc[3] = '我已经领完了，已经提现微信到账，亲测有效，大家赶紧去，晚了就没了！';
		$desc[4] = '快阅读邀请你加入“福利发放小组”，点击查看详情！';
		
		//收入图片
		$litpic[0] = 'http://t.cn/Rkapgsj';
		$litpic[1] = 'http://t.cn/Rka02I2';
		$litpic[2] = 'http://t.cn/Rka0cv6';
		$litpic[3] = 'http://t.cn/Rka0J6Q';
		$litpic[4] = 'http://t.cn/Rka0pbj';
		
		//封装信息
		$i = rand(0,4);
		
		//获取收徒链接
		$data = $this->getDiscipleShareUrl($user_info['id']);
		
		$data['title'] = $title[$i];
		$data['desc'] = $desc[$i];
		$data['litpic'] = $user_info['litpic'];
		$data['user_id'] = $user_info['user_id'];
		$data['circle_litpic'] = $litpic[$i];//分享朋友圈收徒图片
		$data['count_money'] = rand(25,70);//用户收入总金额--晒收入
		$data['bask_money_litpic'] = 'https://kydlitpic.oss-cn-beijing.aliyuncs.com/shaishouru.jpg';//晒收入分享图片
		
		return $data;
    }
    
    //获取收徒链接
    protected function getDiscipleShareUrl($uid){
    		//获取收徒分享域名
		$dname = $this->getDomainNameInfo(1,1);
    		$data['share_url'] = $dname.'Spare/loadShareInfohtml?uid='.$uid;//跳收徒页面(1.5之后用不到)
		$data['direct_url'] = $dname.$this->dis_cont.'/addUserIpInfo?uid='.$uid;//直接跳下载
		return $data;
    }
    
    //获取提现轮播
    protected function getWithdrawalsLists(){
    		$redisinfo = json_decode($this->redis->get('WT_AppDiscipleCarouselList'),true);
		if($redisinfo){
			$data = $redisinfo;
		}else{
			$data[0]['name'] = '奋斗青年';$data[0]['num'] = '35';$data[0]['money'] = '175.00';$data[1]['name'] = '天道酬勤';$data[1]['num'] = '101';$data[1]['money'] = '505.00';$data[2]['name'] = '相聚是缘';$data[2]['num'] = '135';$data[2]['money'] = '675.00';$data[3]['name'] = '未来梦';$data[3]['num'] = '201';$data[3]['money'] = '1005.00';
			$data[4]['name'] = '明天更好';$data[4]['num'] = '10';$data[4]['money'] = '50.00';$data[5]['name'] = '自由飞翔';$data[5]['num'] = '120';$data[5]['money'] = '600.00';$data[6]['name'] = '魔鬼威少';$data[6]['num'] = '15';$data[6]['money'] = '75.00';$data[7]['name'] = '难得糊涂';$data[7]['num'] = '12';$data[7]['money'] = '60.00';
			$data[8]['name'] = '浅笑安然';$data[8]['num'] = '8';$data[8]['money'] = '40.00';$data[9]['name'] = '东阳之光';$data[9]['num'] = '19';$data[9]['money'] = '95.00';$data[10]['name'] = '一帆风顺';$data[10]['num'] = '18';$data[10]['money'] = '90.00';$data[11]['name'] = '花好月圆';$data[11]['num'] = '17';$data[11]['money'] = '85.00';$data[12]['name'] = '生活向前';$data[12]['num'] = '15';$data[12]['money'] = '75.00';
			$data[13]['name'] = '海阔天空';$data[13]['num'] = '6';$data[13]['money'] = '30.00';$data[14]['name'] = '春暖花开';$data[14]['num'] = '12';$data[14]['money'] = '60.00';$data[15]['name'] = '飞龙在天';$data[15]['num'] = '17';$data[15]['money'] = '85.00';$data[16]['name'] = '悠闲的静';$data[16]['num'] = '10';$data[16]['money'] = '50.00';
			$data[17]['name'] = '一生平安';$data[17]['num'] = '99';$data[17]['money'] = '495.00';$data[18]['name'] = '抹记忆';$data[18]['num'] = '11';$data[18]['money'] = '55.00';$data[19]['name'] = '心之所向';$data[19]['num'] = '5';$data[19]['money'] = '25.00';$data[20]['name'] = '小白扬';$data[20]['num'] = '55';$data[20]['money'] = '255.00';
			//存入redis里
			$this->redis->set('WT_AppDiscipleCarouselList',json_encode($data));
		}
		
		return $data;
    }
    
    //获取全局配置信息
    protected function getSystemConfig(){
    		$redisinfo = json_decode($this->redis->get('WT_AppgetSystemConfig'),true);
		if($redisinfo){
			$data = $redisinfo;
		}else{
			$Config = M('Config');
			$data = $Config->field('android_app_version,android_download_msg,android_download_url,android_isdownload,isopenad,adlitpicurl,adurl,adposi,is_show,is_show_version,channels,activity_url,share_type,bottom_litpic,bottom_url')->find();
			if($data){
				//存入redis里
				$this->redis->set('WT_AppgetSystemConfig',json_encode($data));
			}else{
				$data = array();
			}
		}
		
		return $data;
    }
    
    //福利添加记录
    protected function addWelfareRecord($wid,$wtype,$money0,$channel,$platform_type,$uid,$m_type1){
    		if($wid == 6 || $wid == 7 || $wid == 8 || $wid == 9 || $wid == 15){
    			return true;//不是正常任务，直接返回掉
    		}
		//判断该任务是否已完成
		$Welfarerecord = M('Welfarerecord');
		$end = strtotime(date("Y-m-d"));//今天开始时间戳
		$wwhe['uid'] = $uid;
		$wwhe['wid'] = $wid;
		if($wtype == 1){
			$wwhe['create_time'] = array('egt',$end);
		}
		$istrue = $Welfarerecord->where($wwhe)->find();
		if($istrue){
			return true;//已完成，直接返回
		}
		
		//获取该id对应的金额
		$redisinfo = json_decode($this->redis->get('WT_AppGetIdCorrespondingMon'.$wid),true);
		if($redisinfo){
			$money = $redisinfo['gold'];
			$m_type = $redisinfo['m_type'];
		}else{
			$Welfare = M('Welfare');
			$wwhere['id'] = $wid;
			$Winfo = $Welfare->field('gold,m_type')->where($wwhere)->find();
			if($Winfo){
				$money = $Winfo['gold'];
				$m_type = $Winfo['m_type'];
				//存入redis里
				$this->redis->set('WT_AppGetIdCorrespondingMon'.$wid,json_encode($Winfo));
			}else{
				return;
			}
		}
		
		if($m_type == 2){//金币
			//给用户加金币
			$UserAccount = M('UserAccount');
			$uwhere['uid'] = $uid;
			$arr = array(
				'total_gold'=>array('exp','total_gold+'.$money),
				'gold'=>array('exp','gold+'.$money)
			);
			$res = $UserAccount->where($uwhere)->save($arr);
			
			//获取用户账户信息
			$user_account = $this->get_user_account_info($uid);
			
			//添加用户金币流水记录
			$UserGoldRecord = M('UserGoldRecord');
			$data1['uid'] = $uid;
			$data1['cid'] = 4;
			$data1['gold'] = $money;
			$data1['after_gold'] = $user_account['gold'];
			$data1['create_time'] = time();
			$res = $UserGoldRecord->add($data1);
			
			//添加任务记录
			$wdata['uid'] = $uid;
			$wdata['wid'] = $wid;
			$wdata['wtype'] = $wtype;
			$wdata['gold'] = $money;
			$wdata['create_time'] = time();
			$Welfarerecord->add($wdata);
			
			//添加大表记录
			$this->addDataRecord(6,$uid,$money,0,$platform_type,$channel,0,0,0,$wid);
				
		}else{//收益
			//给用户加收益
			$UserAccount = M('UserAccount');
			$arr = array(
				'total_money'=>array('exp','total_money+'.$money),
				'user_money'=>array('exp','user_money+'.$money)
			);
			$uwhere['uid'] = $uid;
			$res = $UserAccount->where($uwhere)->save($arr);
			
			//获取用户账户信息
			$user_account = $this->get_user_account_info($uid);
			
			//添加用户收益流水记录
			$UserMoneyRecord = M('UserMoneyRecord');
			$data1['uid'] = $uid;
			$data1['cid'] = 3;
			$data1['money'] = $money;
			$data1['after_money'] = $user_account['user_money'];
			$data1['create_time'] = time();
			$res = $UserMoneyRecord->add($data1);
			
			//添加任务记录
			$wdata['uid'] = $uid;
			$wdata['wid'] = $wid;
			$wdata['wtype'] = $wtype;
			$wdata['gold'] = $money;
			$wdata['create_time'] = time();
			$Welfarerecord->add($wdata);
			
			//添加大表记录
			$this->addDataRecord(6,$uid,0,$money,$platform_type,$channel,0,0,0,$wid);
		}
		
		//完成记录存到reids（记录用户首次分享文章/收徒；提现那里也有用到）
		if($wid == 3 || $wid == 4){
			//存入redis里
			$this->redis->set('WT_AppUsreWelfare'.$wid.'First'.$uid,1);
		}
    }
    
    //获取分享域名（备用域名）参数：gid所属组  e_type1、分享2、阅读
    protected function getDomainNameInfo($gid,$e_type){
    		$redisResult = json_decode($this->redis->get('WT_AppGetDomainNameInfo'.$gid.$e_type),true);
    		if($redisResult){
    			$res = $redisResult;
    		}else{
    			$Domainname = M('Domainname');
	    		$where['gid'] = $gid;//所属组
	    		$where['e_type'] = $e_type;//作用：1、分享2、阅读
	    		$where['status'] = 1;//状态：1、启用2、备用3、停用
	    		$where['isdel'] = 0;
	    		$where['s_type'] = 1;
	    		$res = $Domainname->field('dname')->where($where)->select();
	    		if($res){
	    			$this->redis->set('WT_AppGetDomainNameInfo'.$gid.$e_type,json_encode($res));
	    		}
    		}
    		
    		$n = (count($res)-1);//获取有几个域名
    		$i = rand(0,$n);//随机获取一个
    		return $res[$i]['dname'];
    }
    
    //模仿http请求
    protected function postData($url, $data = null,$post=false){
        try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, $post);
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            ob_start();
            curl_exec($ch);
            $result = ob_get_contents();
            ob_end_clean();
            return $result;
        } catch (Exception $e){
            throw $e;
        }
    }
    
    //获取AccessToken - 快阅趣闻
//  protected function getAccessToken(){
//      $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx6a7cf351e29df190&secret=2eb95181987cffb05017cd7c3291be1b";
//      $atjson=$this->postData($url);
//
//      $result=json_decode($atjson,true);//json解析成数组
//      if(!isset($result['access_token'])){
//          exit( '获取access_token失败！' );
//      }
//		$this->redis->set('KYQW_AccessToken',$result["access_token"],$result['expires_in']);
//      return $result["access_token"];
//  }
    
    //获取AccessToken - 零花看点
    protected function getAccessToken(){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx1fea89bbd47ec18f&secret=32e7f392560cb6955743193ad2080b72";
        $atjson=$this->postData($url);

        $result=json_decode($atjson,true);//json解析成数组
        if(!isset($result['access_token'])){
            exit( '获取access_token失败！' );
        }
		$this->redis->set('LHKD_AccessToken',$result["access_token"],$result['expires_in']);
        return $result["access_token"];
    }
    
    //获取AccessToken - 给钱阅
    protected function getAccessTokengqy(){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx8e41e9a874959146&secret=334ec4dae3c9926daff27fcda809fcbe";
        $atjson=$this->postData($url);

        $result=json_decode($atjson,true);//json解析成数组
        if(!isset($result['access_token'])){
            exit( '获取access_token失败！' );
        }
		$this->redis->set('GEIQY_AccessToken',$result["access_token"],$result['expires_in']);
        return $result["access_token"];
    }
    
    //获取广告列表
    protected function getAdInfoLists($pid,$op='eq',$is_pullblack=0){
		$redisinfo = json_decode($this->redis->get('WT_AppADInfoLists'.$pid.$op.$is_pullblack),true);
		if($redisinfo){
			$res = $redisinfo;
		}else{
			$Advertisement = M('Advertisement');
			
			$where['pid'] = array($op,$pid);
			$where['status'] = 1;
			//判断是否已拉黑高价
			if($is_pullblack == 1){
				$where['share_type'] = 0;
				$where['shareprice'] = 0;
			}
			$res = $Advertisement->field('id,pid,title,pic_type,litpic1,litpic2,litpic3,ad_type,ad_url,ad_code,visitnum,share_type,shareprice,price')->where($where)->order('rank desc')->select();
			if($res === NULL){
				$res = array();
			}else{
				//存入redis里
				$this->redis->set('WT_AppADInfoLists'.$pid.$op.$is_pullblack,json_encode($res));
			}
		}
		
		return $res;
    }
    
    //获取单一广告
    protected function getAdInfoOne($pid){
    		$redisinfo = json_decode($this->redis->get('WT_AppADInfoSingleData'.$pid),true);
    		if($redisinfo){
    			$res = $redisinfo;
    		}else{
    			$Advertisement = M('Advertisement');
			$where['pid'] = array('eq',$pid);
			$where['status'] = 1;
			$res = $Advertisement->field('id,pid,title,pic_type,litpic1,litpic2,litpic3,ad_type,ad_url,ad_code,visitnum,share_type,shareprice,price')->where($where)->find();
			if($res === NULL){
				$res = array();
			}else{
				//存入redis里
				$this->redis->set('WT_AppADInfoSingleData'.$pid,json_encode($res));
			}
    		}
    		
		return $res;
    }
    
    //获取用户扣量规则
    protected function getUserDeductionInfo($uid){
    		//获取用户今天收益多少钱了
    		$UserMoneyRecord = M('UserMoneyRecord');
		$starttime = strtotime(date('Ymd', time()));//今天开始时间戳
		$where['cid'] = array('in','1,7');
    		$where['uid'] = $uid;
		$where['create_time'] = array('egt',$starttime);
    		$money = $UserMoneyRecord->where($where)->sum('money');
    		if(!$money || $money == 0){return true;}//不在扣量范围内，直接给用户加钱
    		
    		//获取该金额对应用户的扣量规则
    		$where1['start_money'] = array('elt',$money);
    		$where1['uid'] = $uid;
    		$where1['type'] = 1;
    		$where1['status'] = 1;
    		$UserDeduction = M('UserDeduction');
    		$res1 = $UserDeduction->field('deduction_pre,start_money')->where($where1)->order('end_money desc')->find();
    		if($res1 === NULL){
    			return true;//不在扣量范围内，直接给用户加钱
    		}else{
    			if($res1['start_money'] == 5000){//每隔n个给用户1个（n越大给用户越少）
	    			$redisnum = $this->redis->get('WT_AppUserDeduction'.$uid.$res1['start_money']);
				$this->redis->set('WT_AppUserDeduction'.$uid.$res1['start_money'],$redisnum+1);
				if(($redisnum+1) % ($res1['deduction_pre']+1) == 0){
					return true;//不在扣量范围内，直接给用户加钱
				}else{
					return false;//执行扣除，不给用户加钱
				}
	    		}else{//每隔1个给用户n个（n越大给用户越多）
	    			$redisnum = $this->redis->get('WT_AppUserDeduction'.$uid.$res1['start_money']);
				$this->redis->set('WT_AppUserDeduction'.$uid.$res1['start_money'],$redisnum+1);
				if(($redisnum+1) % ($res1['deduction_pre']+1) == 0){
					return false;//执行扣除，不给用户加钱
				}else{
					return true;//不在扣量范围内，直接给用户加钱
				}
	    		}
    		}
    }
    
    //判断手机端还是移动端
    protected function isMobile()
    { 
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        {
            return true;
        } 
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA']))
        { 
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        } 
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT']))
        {
            $clientkeywords = array ('nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
                ); 
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
            {
                return true;
            } 
        } 
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT']))
        { 
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
            {
                return true;
            } 
        } 
        return false;
    }
    
}