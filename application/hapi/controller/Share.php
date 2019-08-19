<?php
namespace app\hapi\controller;
use think\Db;

//分享出去的文章或视频打开的操作
class Wz666Controller extends BaseController {
	
	//----------------------------------------------------硬广开始-----------------------------------------------------
	//加载分享出来的广告文章
	public function loadAdArticleHtml(){
		//获取信息
		$uid = I('get.uid');//用户id
		$aid = I('get.aid');//广告id
		$external = I('get.external');//分享时间
		$ip = get_client_ip(0, true);//用户ip
		$phoneinfo = $_SERVER['HTTP_USER_AGENT'];
		if(!$aid || !$uid){return;}

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
				return;
			}else{
				//存入redis里
				$this->redis->set('WT_AppADDetailInfo'.$aid,json_encode($adinfo));
			}
		}
		
		//获取用户是否拉黑高价
		$pblack = $this->getUserPullblack($uid);
		//判断是否2G网络
		if(strpos($_SERVER['HTTP_USER_AGENT'],"NetType/2G") === false){
			//判断是否移动端
			$ismob = $this->isMobile();
			if($ismob){
				if($pblack){
					//判断该ip针对该用户分享的文章/视频/硬广自然天内有没有点击过任何一篇
//					$isclick = $this->getUserIsClick($uid,$ip);
//					if($isclick){
						//获取该文章分享出去的时间是否超过24小时
						$time = $this->getUserShareTime($uid,$aid,$external,$ip,2);
						if($time){
							//判断该ip今日总共点击了多少个人的文章＋硬广了
							$num = $this->getIpReadArticleNum($ip);
							if($num){
								//获取用户该条该不该扣量
								$istrue = $this->getUserDeductionInfo($uid);
								if($istrue){
									//给用户加钱
					    				$res = $this->addUserMoneyRecord($uid,$adinfo['shareprice'],7);
								}
							}
						}
//					}
				}
			}
		}
		
		//判断用户是否拉黑高价
		if($pblack){
			//判断该ip今天有没有点击过该广告(如果没有点击过，扣除广告费)
			$this->getIpIsClickAd($uid,$aid,$ip,$external,$adinfo['price'],$adinfo['pid'],$phoneinfo,$adinfo['money']);
		}
		
		//跳转广告的打开地址
		redirect($adinfo['ad_url']);
	}
	
	//判断用户是否拉黑高价
	protected function getUserPullblack($uid){
		if(!$uid){return false;}
		$res = $this->getBaseUserInfo($uid);
		if($res['is_pullblack'] == 1){
			return false;
		}else{
			return true;
		}
	}
	
	//判断该ip今天有没有点击过(如果没有点击过，扣除广告费)－－硬广
    protected function getIpIsClickAd($uid,$aid,$ip,$external,$price,$pid,$phoneinfo,$money){
    		$AdExpenditureRecord = M('AdExpenditureRecord');
    		$time = strtotime(date('Ymd', time()));//今天开始时间戳
    		$where2['aid'] = $aid;
    		$where2['user_ip'] = $ip;
    		$where2['create_time'] = array('egt',$time);
    		$res2 = $AdExpenditureRecord->where($where2)->find();
    		if($res2 === NULL){
    			$this->DeductionAdMoney($aid,$price,$uid,$ip,$pid,$phoneinfo,$money);
    		}else{
    			//添加广告点击记录
    			$data2['aid'] = $aid;
			$data2['pid'] = $pid;
			$data2['price'] = 0;
			$data2['money'] = 0;
			$data2['uid'] = $uid;
			$data2['type'] = 1;
			$data2['equipment_model'] = $phoneinfo;
			$data2['user_ip'] = $ip;
			$data2['create_time'] = time();
			$AdExpenditureRecord->add($data2);
    		}
    }
    
    //硬广－扣除广告费
	protected function DeductionAdMoney($aid,$price,$uid,$ip,$pid,$phoneinfo,$money){
		
		$Advertisement = M('Advertisement');
		$where['id'] = $aid;
		$res = $Advertisement->where($where)->setDec('surplus_money',$price);//扣除广告费
		if($res){
			//添加记录
			$AdExpenditureRecord = M('AdExpenditureRecord');
			$data['aid'] = $aid;
			$data['pid'] = $pid;
			$data['price'] = $price;
			$data['money'] = $money;
			$data['uid'] = $uid;
			$data['type'] = 1;
			$data['equipment_model'] = $phoneinfo;
			$data['user_ip'] = $ip;
			$data['create_time'] = time();
			$AdExpenditureRecord->add($data);
		}
	}
    
	//----------------------------------------------------硬广结束-----------------------------------------------------
	
	
	//----------------------------------------------------文章开始-----------------------------------------------------
	//加载分享出去之后的文章&视频详情页面
    public function loadArticleSharehtml(){
    		//获取信息
		$uid = I('get.uid');//用户id
		$aid = I('get.aid');//文章&视频id
		$external = I('get.external');//分享时间
		$user_ip = I('get.user_ip');//分享ip
		if(!$uid || !$aid || !$external){
			//base64_decode解密
	    		$str = ltrim($_SERVER['REQUEST_URI'],'/');
			if(strpos($str,'??') !== false){ 
			   $str = substr($str,0,strpos($str,'??'));
			}
			parse_str(base64_decode($str),$q);
	    		$uid = $q['uid'];//用户id
	    		$aid = $q['aid'];//文章&视频id
	    		$external = $q['external'];//分享时间
	    		$user_ip = $q['user_ip'];//分享ip
	    		if(!$uid || !$aid || !$external){
	    			return;
	    		}
		}
		
		if(!$user_ip){$user_ip = 1;}
		
		$ip = get_client_ip(0, true);//用户ip

		//获取用户是否被封号
		$uinfo = $this->getBaseUserInfo($uid);
		if($uinfo['status'] == 4){
			return;
		}
		//获取文章分享阅读域名
		$dname = parent::getDomainNameInfo(2,2);
		//base64_encode加密
		$codes = base64_encode("uid=$uid&aid=$aid&external=$external&actionname=loadAgainArticleSharehtml&user_ip=$user_ip&codes=jm&sj=".time());
		$dname1 = $dname.$codes;
		//进行二次跳转，加载空白页
		$this->assign('share_url',$dname1);
		$this->display('External/firstjump');

    }
    
    //真正加载分享出去之后的文章&视频详情页面
    public function loadAgainArticleSharehtml(){
    		$uid = I('get.uid');//用户id
    		$aid = I('get.aid');//文章&视频id
    		$external = I('get.external');//分享时间
    		$user_ip = I('get.user_ip');//分享ip
    		if(!$uid || !$aid || !$external){
			//base64_decode解密
	    		$str = ltrim($_SERVER['REQUEST_URI'],'/');
			if(strpos($str,'??') !== false){ 
			   $str = substr($str,0,strpos($str,'??'));
			}
			parse_str(base64_decode($str),$q);
	    		$uid = $q['uid'];//用户id
	    		$aid = $q['aid'];//文章&视频id
	    		$external = $q['external'];//分享时间
	    		$user_ip = $q['user_ip'];//分享ip
	    		if(!$uid || !$aid || !$external){
	    			return;
	    		}
		}
		
    		$ip = get_client_ip(0, true);//用户ip

    		$phoneinfo = $_SERVER['HTTP_USER_AGENT'];//浏览器型号 － 网络情况 － 微信版本号 － 设备号
    		
    		//获取用户是否被封号
		$uinfo = $this->getBaseUserInfo($uid);
		if($uinfo['status'] == 4){
			return;
		}
    		//获取详细信息
    		$redisinfo = json_decode($this->redis->get('WT_AppActicleDetailInfo'.$aid),true);
		if($redisinfo){
			$articleinfo = $redisinfo;
		}else{
			//获取详情信息
			$Article = M('Article');
			$where['id'] = $aid;
			$articleinfo = $Article->field('id,title,cid,desc,litpic1,video_url,video_long,type,price,sharenum,visitnum')->where($where)->find();
			if($articleinfo === NULL){
				return;
			}else{
				//存入redis里
				$this->redis->set('WT_AppActicleDetailInfo'.$aid,json_encode($articleinfo));
			}
		}
		
		//设置给用不上报加钱
		$articleinfo['mark'] = 1;//1上报，0不上报
		
		//-------判断是否显示老铁家js---------------------------------------------
		$istrue = 1;// 1显示广告 2不显示

		//-------判断是否显示老铁家js---------------------------------------------
		
		//获取收徒下载信息
		$disinfo = $this->getDiscipleShareUrl($uid);
		$down_url = $disinfo['direct_url'];
		$this->assign('down_url',$down_url);
		
		$ArticleReadRecord = M('ArticleReadRecord');
		//判断该ip今天有没有点击过(来判断执行哪个页面)
		$starttime = strtotime(date('Ymd', time()));//今天开始时间戳
		$where1['create_time'] = array('egt',$starttime);
		$where1['user_ip'] = $ip;
		$cc = $ArticleReadRecord->where($where1)->count();
		
		//添加外部文章点击记录
		$data['user_ip'] = $ip;
//		$data['equipment_model'] = $phoneinfo;
		$data['uid'] = $uid;
		$data['tid'] = $aid;
		$data['create_time'] = time();
		$ArticleReadRecord->add($data);
		
		$uinfo['aid'] = $aid;
		$uinfo['uid'] = $uid;
		$uinfo['external'] = $external;
		$uinfo['istrue'] = $istrue;

		$this->assign('ainfo',$articleinfo);
		$this->assign('uinfo',$uinfo);
		if($articleinfo['type'] == 1){
			$this->display('External/news');
		}else{
			$this->display('External/video');
		}
    }
    
    //外链获取文章信息
   	public function getArtinfo(){
   		$id = I('post.tid');//文章id
   		if(!$id){$id = 1;}
   		//获取文章信息
    		$articleinfo = json_decode($this->redis->get('WT_AppActicleDetailInfo'.$id),true);
    		
    		$data['title'] = $articleinfo['title'];
    		$data['desc'] = $articleinfo['desc'];
   		$ajaxReturn['code'] = 200;
   		$ajaxReturn['msg'] = 'SUCCESS';
   		$ajaxReturn['data'] = $data;
   		$this->ajaxReturn($ajaxReturn);
   	}
    
    //----------------------------------------------------文章结束-----------------------------------------------------
    
    
    //----------------------------------------------------公共开始-----------------------------------------------------
    	
    	//判断该ip针对该用户分享的文章/视频/硬广自然天内有没有点击过任何一篇
    protected function getUserIsClick($uid,$ip){
    		$stime = strtotime(date('Ymd', time()));//今天开始时间戳
//  		$ArticleChargingRecord = M('ArticleChargingRecord');
    		$where['user_ip'] = $ip;
    		$where['uid'] = $uid;
    		$where['create_time'] = array('egt',$stime);
//  		$res = $ArticleChargingRecord->where($where)->count();
//  		if(!$res){
    			$AdExpenditureRecord = M('AdExpenditureRecord');
	    		$res2 = $AdExpenditureRecord->where($where)->find();
	    		if($res2 === NULL){
	    			return true;
	    		}else{
	    			return false;
	    		}
//  		}else{
//  			return false;
//  		}
    }
    
    	//外部点击给用户加钱
    protected function addUserMoneyRecord($uid,$price,$cid){
    		$UserAccount = M('UserAccount');
    		$arr = array(
			'total_money'=>array('exp','total_money+'.$price),
			'user_money'=>array('exp','user_money+'.$price),
		);
    		$where['uid'] = $uid;
    		$res = $UserAccount->where($where)->save($arr);
    		if($res){
    			 //获取用户账户信息
            $user_account = $this->get_user_account_info($uid);
    			//添加收益记录
    			$UserMoneyRecord = M('UserMoneyRecord');
            $data1['uid'] = $uid;
            $data1['cid'] = $cid;
            $data1['money'] = $price;
            $data1['after_money'] = $user_account['user_money'];
            $data1['create_time'] = time();
            $UserMoneyRecord->add($data1);
    		}
    }
    
    //获取分享的文章／视频有没有超过24小时
    protected function getUserShareTime($uid,$aid,$external,$ip,$type=1){
    		$where['uid'] = $uid;
    		$where['aid'] = $aid;
    		$where['type'] = $type;
    		$where['create_time'] = $external;
    		$UserShareRecord = M('UserShareRecord');
    		$res = $UserShareRecord->field('user_ip')->where($where)->find();
    		if($res === NULL){
    			return false;
    		}else{
    			if($external+86400 < time() || $res['user_ip'] == $ip){
    				return false;
    			}else{
    				return true;
    			}
    		}
    }
    
    //判断该ip今日总共点击了多少个人的文章＋硬广了
	protected function getIpReadArticleNum($ip){
		$stime = strtotime(date('Ymd', time()));//今天开始时间戳
    		$where['user_ip'] = $ip;
    		$where['create_time'] = array('egt',$stime);
    		//获取硬广
    		$AdExpenditureRecord = M('AdExpenditureRecord');
    		$res1 = $AdExpenditureRecord->field('distinct uid')->where($where)->select();
    		if(count($res1) < 1){
    			return true;
    		}else{
    			return false;
    		}
	}
    
    //----------------------------------------------------公共结束-----------------------------------------------------

}

