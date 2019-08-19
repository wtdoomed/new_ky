<?php
namespace app\hapi\controller;
use think\Db;

//提现 列表
class MoneyController extends BaseController{

    //配置自己公众号的参数  -- 给钱阅
//  private $appid = 'wx8e41e9a874959146'; //AppID(应用ID)
//  private $mch_id = '1514739641'; //商户号
//  private $key = '2018gei12qian05yue1shang2hu3hao4';//微信商户平台->API安全->API密钥
    //https://pay.weixin.qq.com/index.php/account/api_cert
    
    //配置自己公众号的参数 -- 零花看点
    private $appid = 'wx1fea89bbd47ec18f'; //AppID(应用ID)
    private $mch_id = '1514415611'; //商户号
    private $key = 'ling12hua98kan05dian36ping47tai1';//微信商户平台->API安全->API密钥
    //https://pay.weixin.qq.com/index.php/account/api_cert

    //-----------------------------------微信商户向用户付款开始---------------------------------------------------------------

	//获取是否绑定提现公众号
	public function getWithdrawInfo(){
		$token = I('post.token');//用户token
		$user_info = $this->IsAppLogin($token);
		
		$User = M('User');
		$where['id'] = $user_info['id'];
		$res = $User->field('unionid')->where($where)->find();
//		if($res['cash_openid'] != ''){
//			$istrue = 1;//已绑定
//		}else{
//			$istrue = 2;//未绑定
//		}
//		
//		$ajaxReturn['code'] = 200;
//		$ajaxReturn['msg'] = 'SUCCESS';
//		$ajaxReturn['data']['istrue'] = $istrue;
//		$this->ajaxReturn($ajaxReturn);
		$res1 = $this->redis->get('App_Com_uni'.$res['unionid']);
		if($res1){
			$istrue = 1;//已绑定
		}else{
			$istrue = 2;//未绑定
		}
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['istrue'] = $istrue;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取提现页面上的数据
	public function getWithdrawData(){
		$token = I('post.token');//用户token
		$user_info = $this->IsAppLogin($token);
		//获取缓存（用户是否提现过）
//		$redisinfo = $this->redis->get('WT_AppUserIsWithdraw'.$user_info['id']);
//		if($redisinfo){
//			$istrue = $redisinfo;
//		}else{
//			//获取详情信息
//			$UserWithdraw = M('UserWithdraw');
//			$where['uid'] = $user_info['id'];
//			$articleinfo = $UserWithdraw->where($where)->find();
//			if($articleinfo === NULL){
//				$istrue = 1;//1、显示 2、不显示
//			}else{
//				$istrue = 2;
//				//存入redis里
//				$this->redis->set('WT_AppUserIsWithdraw'.$user_info['id'],$istrue);
//			}
//		}
		
		//获取用户的注册时间
//		$redisinfo = json_decode($this->redis->get('WT_AppUserRegistertime'.$user_info['id']),true);
//		if($redisinfo){
//			$istrue = $redisinfo;
//		}else{
//			//获取详情信息
//			$User = M('User');
//			$where['id'] = $user_info['id'];
//			$uinfo = $User->field('create_time')->where($where)->find();
//			if($uinfo['create_time'] > 1534003200){//8月12号
//				$istrue = 1;//1、显示 2、不显示
//			}else{
//				$istrue = 2;
//			}
//			//存入redis里
//			$this->redis->set('WT_AppUserRegistertime'.$user_info['id'],$istrue);
//		}
		
		//获取账户信息
		$userAcc = $this->get_user_account_info($user_info['id']);
		$istrue = 2;
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['istrue'] = $istrue;
		$ajaxReturn['data']['user_money'] = $userAcc['user_money']/100;
		$this->ajaxReturn($ajaxReturn);
	}
	
    	//付款方法
    public function payMent(){
		$token = I('post.token');//用户token
		$user_info = $this->IsAppLogin($token);
		$type = I('post.type');//1微信2话费
        $money = I('post.money') * 100;//要付款的金额
        $nonce_str = $this->great_rand();//随机字符串
        $mch_id = $this->mch_id;//商户id
        $ip = get_client_ip();//调用接口的ip
        
        //获取用的提现openid和用户账户
        $User = M('User');
		$uwhere['a.id'] = array('eq',$user_info['id']);
		$UserAcc = $User->alias('a')->field('a.id,a.phone,a.unionid,a.cash_openid,a.status,a.is_withdraw,a.masterid,a.create_time,a.is_pullblack,b.user_money')->join('__USER_ACCOUNT__ b ON a.id = b.uid','LEFT')->where($uwhere)->find();
		$cash_openid = $this->redis->get('App_Com_uni'.$UserAcc['unionid']);
		if(!$UserAcc || !$cash_openid){
			$ajaxReturn['code'] = 401;
        		$ajaxReturn['msg'] = '未绑定公众号，无法提现！';
        		$this->ajaxReturn($ajaxReturn);
		}
		
		if($UserAcc['status'] == 4 || $UserAcc['is_withdraw'] == 0){
			$ajaxReturn['code'] = 406;
        		$ajaxReturn['msg'] = '您的账号异常，请联系客服！';
        		$this->ajaxReturn($ajaxReturn);
		}
		
//		if($UserAcc['masterid'] > 1){
//			//获取是否已赚到1毛的收益
//			$mredisinfo = $this->redis->get('WT_AppUserIsShareMoney'.$user_info['id']);
//			if(!$mredisinfo){
//				$UserMoneyRecord = M('UserMoneyRecord');
//				$mwhere['uid'] = $user_info['id'];
//				$mwhere['cid'] = array('in','1,7');
//				$marr = $UserMoneyRecord->field('sum(money) as money')->where($mwhere)->select();
//				if($marr[0]['money'] < 10){
//					$ajaxReturn['code'] = 410;
//					$ajaxReturn['msg'] = '1元秒提现，需分享一篇文章到你微信群/朋友圈，让你的好友打开认真阅读即可<br/>同时提现成功后系统将再赠送你0.3元！';
//					$this->ajaxReturn($ajaxReturn);
//				}else{
//					$this->redis->set('WT_AppUserIsShareMoney'.$user_info['id'],1);//设置已完成
//				}
//			}
//		}else{
//			//判断用户是否首次分享过文章
//			$isfirst = $this->redis->get('WT_AppUsreWelfare3First'.$user_info['id']);
//			if(!$isfirst){
//				$ajaxReturn['code'] = 410;
//				$ajaxReturn['msg'] = '1元秒提现，需分享一篇文章到你微信群或者朋友圈即可。<br/>同时提现成功后系统将再赠送你0.3元！';
//				$this->ajaxReturn($ajaxReturn);
//			}
//		}
		
		//判断是否绑定手机号
		if(!$UserAcc['phone']){
			$ajaxReturn['code'] = 701;
        		$ajaxReturn['msg'] = '为了你的账户安全，请先绑定手机号！';
        		$this->ajaxReturn($ajaxReturn);
		}
		
		//判断是否已经提现过一次1元，并且金额是否合法
//		if($money == 100){
//			$Mon = M('UserWithdraw');
//			$end1 = strtotime(date("Y-m-d"));//今天开始时间戳
//			$mowhere1['create_time'] = array('egt',$end1);
//			$mowhere1['uid'] = $user_info['id'];
//			$mowhere1['money'] = 100;
//			$arr2 = $Mon->where($mowhere1)->count();
//			if($arr2 > 0){
//				$ajaxReturn['code'] = 408;
//	        		$ajaxReturn['msg'] = '金额不合法！';
//	        		$this->ajaxReturn($ajaxReturn);
//			}
//		}else{
			if($money != 300 && $money != 1000 && $money != 2000 && $money != 5000 && $money != 10000){
				$ajaxReturn['code'] = 408;
	        		$ajaxReturn['msg'] = '金额不合法！';
	        		$this->ajaxReturn($ajaxReturn);
			}
//		}
		
		//判断余额是否够
		if($money > $UserAcc['user_money']){
			$ajaxReturn['code'] = 400;
			$ajaxReturn['msg'] = '您的余额不足';
			$this->ajaxReturn($ajaxReturn);
		}
		
		//判断今天已经提现1次
    		$Mon = M('UserWithdraw');
		$end = strtotime(date("Y-m-d"));//今天开始时间戳
		$mowhere['create_time'] = array('egt',$end);
		$mowhere['uid'] = $user_info['id'];
		$arr = $Mon->where($mowhere)->count();
		if($arr > 0){
			$ajaxReturn['code'] = 403;
			$ajaxReturn['msg'] = '每日提现次数为1次！';
			$this->ajaxReturn($ajaxReturn);
		}
		
		if($money > 5000){
			$istrue = $this->userIsConformStandard($user_info['id'],$UserAcc['create_time']);
			$data3[] = '提现金额大于50，需要审核！';
			$data3 = array_merge($data3,$istrue['msg']);
			
        		$Mon = M('UserWithdraw');
			//添加提现记录
            $dinfo['type'] = $type;
            $dinfo['uid'] = $user_info['id'];
            $dinfo['money'] = $money;
            $dinfo['after_money'] = $UserAcc['user_money'] - $money;
            $dinfo['status'] = 0;
            $dinfo['desc'] = json_encode($data3);
            $dinfo['create_time'] = time();
            $Mon->add($dinfo);
            
            //给用户减钱
        		$UserAccount = M('UserAccount');
        		$whereacc['uid'] = $user_info['id'];
        		$UserAccount->where($whereacc)->setDec('user_money',$money);
            
    			$ajaxReturn['code'] = 402;
			$ajaxReturn['msg'] = '提交成功，系统处理中！';
			$this->ajaxReturn($ajaxReturn);
    		}
		
		//检测该笔金额是否合法
//		if($money != 100){
			//判断用户是否符合提现规范
			$istrue = $this->userIsConformStandard($user_info['id'],$UserAcc['create_time'],$money);
			if($istrue['code'] == 500){
				$Mon = M('UserWithdraw');
				//添加提现记录
	            $dinfo['type'] = $type;
	            $dinfo['uid'] = $user_info['id'];
	            $dinfo['money'] = $money;
	            $dinfo['after_money'] = $UserAcc['user_money'] - $money;
	            $dinfo['status'] = 0;
	            $dinfo['desc'] = json_encode($istrue['msg']);
	            $dinfo['create_time'] = time();
	            $Mon->add($dinfo);
	            
	            //给用户减钱
	        		$UserAccount = M('UserAccount');
	        		$whereacc['uid'] = $user_info['id'];
	        		$UserAccount->where($whereacc)->setDec('user_money',$money);
	            
	            $ajaxReturn['code'] = 407;
	        		$ajaxReturn['msg'] = '提交成功，系统处理中！';
	        		$this->ajaxReturn($ajaxReturn);
			}elseif($istrue['code'] == 501){
				$ts = 3 - $istrue['msg'];
				$ajaxReturn['code'] = 409;
	        		$ajaxReturn['msg'] = '有3天获得分享阅读收益1毛，即可获得3元提现资格，你还差'.$ts.'天。';
	        		$this->ajaxReturn($ajaxReturn);
			}
//		}else{
//			$msg[] = '1元秒提现！';
//			$istrue['msg'] = $msg;
//		}
		
		//执行用户减钱操作
		M()->startTrans();//开启事物
    		$UserAccount = M('UserAccount');
    		$whereacc['uid'] = $user_info['id'];
		$Zhanghu = $UserAccount->lock(true)->field('user_money')->where($whereacc)->find();//加锁
		if($money > $Zhanghu['user_money']){
			M()->rollback();//回滚事物
			$ajaxReturn['code'] = 400;
			$ajaxReturn['msg'] = '您的余额不足';
			$this->ajaxReturn($ajaxReturn);
		}else{
			$res5 = $UserAccount->where($whereacc)->setDec('user_money',$money);
			if(!$res5){
				M()->rollback();//回滚事物
				$ajaxReturn['code'] = 400;
				$ajaxReturn['msg'] = '您的余额不足';
				$this->ajaxReturn($ajaxReturn);
			}
		}

        $params = array();
        $params['mch_appid'] = $this->appid;
        $params['mchid'] = $mch_id;
        $params['nonce_str'] = $nonce_str;
        $params['partner_trade_no'] = $this->get_dingdan();
        $params['openid'] = $cash_openid;
        $params['check_name'] = 'NO_CHECK';
        $params['re_user_name'] = '张三';
        $params['amount'] = $money;
        $params['desc'] = '快阅读提现';
        $params['spbill_create_ip'] = $ip;
        $sign = $this->getSign($params); //生成签名
        $params['sign'] = $sign;
        $data = $this->arrayToXml($params);//生成xml数组
        
		//付款访问路径
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        //执行付款
        $res = $this->curl_post_ssl($url,$data);
        $result = $this->xmlToArray($res);//将xml转为数组
        if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
        		//-----------------------------解封拉黑高价-----------------------------
    			if($UserAcc['is_pullblack'] == 1){//解封拉黑高价
    				$blwhere['id'] = $user_info['id'];
    				$bldata['is_pullblack'] = 0;
    				$User->where($blwhere)->save($bldata);
    				$this->redis->set('WT_AppUserInfos'.$user_info['id'],null);
    			}
        		//-----------------------------解封拉黑高价-----------------------------
        		
        		//添加提现记录
            $dinfo['type'] = $type;
            $dinfo['uid'] = $user_info['id'];
            $dinfo['money'] = $money;
            $dinfo['after_money'] = $UserAcc['user_money'] - $money;
            $dinfo['create_time'] = time();
            $istrue['msg'][] = '正常---';
            $dinfo['desc'] = json_encode($istrue['msg']);
            $dinfo['hit_time'] = $dinfo['create_time'];
            $arr1 = $Mon->add($dinfo);
            if($arr1){
            		M()->commit();//提交事物
            		$ajaxReturn['code'] = 200;
            		$ajaxReturn['msg'] = '提现成功！';
            }else{
            		M()->commit();//提交事物
            		$ajaxReturn['code'] = 500;
            		$ajaxReturn['msg'] = '失败，请稍后重试！';
            }
        }else{
        		M()->rollback();//回滚事物
        		if($result['err_code'] == 'V2_ACCOUNT_SIMPLE_BAN'){
        			$ajaxReturn['code'] = 500;
           	 	$ajaxReturn['msg'] = '微信账户未通过实名认证!<br /><br />认证流程：<br />进入微信>我>钱包>银行卡>添加银行卡';
        		}else{
        			$ajaxReturn['code'] = 500;
           	 	$ajaxReturn['msg'] = '每日提现次数为1次！';
        		}
        }
		$this->ajaxReturn($ajaxReturn);
    }
    //---------------------------生成签名时排序，并返回xml------------------------------------------------
     	/*
            生成签名
        */
        public function getSign($Parameters){

            //签名步骤一：按字典序排序参数
            ksort($Parameters);
            $String = $this->ToUrlParams($Parameters);
            //签名步骤二：在string后加入KEY
            $String = $String."&key=".$this->key;
            //签名步骤三：MD5加密
            $result_ = strtoupper(md5($String));
            return $result_;
        }
    	  //格式化参数格式化成url参数
        public function ToUrlParams($paraMap){

            $buff = "";
            foreach ($paraMap as $k => $v){
                if($k != "sign" && $v != "" && !is_array($v)){
                    $buff .= $k . "=" . $v . "&";
                }
            }

            $buff = trim($buff, "&");
            return $buff;
        }

        //数组转xml
        public function arrayToXml($arr){
            $xml = "<xml>";
            foreach ($arr as $key=>$val){
                if (is_numeric($val)){
                    $xml.="<".$key.">".$val."</".$key.">";

                }
                else{
                    $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
                }
            }
            $xml.="</xml>";
            return $xml;
        }

        /*xml转成数组*/
        public function xmlToArray($xmlstr) {
            if(!$xmlstr){
                die("xml数据异常！");
            }
            //将XML转为array
            //禁止引用外部xml实体
            libxml_disable_entity_loader(true);
            $values = json_decode(json_encode(simplexml_load_string($xmlstr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
            return $values;
        }
    //---------------------------生成签名时排序，并返回xml------------------------------------------------
    /**
     * 生成随机数
     */
    public function great_rand(){
        $str = '1234567890abcdefghijklmnopqrstuvwxyz';
        $t1 = '';
        for($i=0;$i<30;$i++){
            $j=rand(0,35);
            $t1 .= $str[$j];
        }
        return $t1;
    }

    	//生成订单号
    	public function get_dingdan(){
    		$orders = time().rand(100000000,999999999);
    		return $orders;
    	}

    /**
     * 证书生成  执行发送时调用此方法发送
     * @Author: 王通
     * @Date:   2018-04-15
     * @Last Modified time: 2018-04-15
     * @Return Array
     */
    public function curl_post_ssl($url, $data, $second=30,$aHeader=array())
    {

        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        //这里设置代理，如果有的话
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        //cert 与 key 分别属于两个.pem文件
        //请确保您的libcurl版本是否支持双向认证，版本高于7.20.1
        curl_setopt($ch,CURLOPT_SSLCERT,'/home/wwwroot/kuaiyue/certificate/apiclient_cert.pem');
        curl_setopt($ch,CURLOPT_SSLKEY,'/home/wwwroot/kuaiyue/certificate/apiclient_key.pem');
        //curl_setopt($ch,CURLOPT_CAINFO,ROOT_APP_PATH.'/certificate/apiclient_cert.p12');
        if( count($aHeader) >= 1 ){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $data = curl_exec($ch);
        //var_dump($data);die;  //报错时，打开后，就可以看看报了什么错
        if($data){
            curl_close($ch);
            return $data;
        }else{
            $error = curl_errno($ch);
            curl_close($ch);
            return false;
        }
    }
    
    //检测用户是否符合提现规范
    private function userIsConformStandard($uid,$create_time,$txmoney=0){
    		
    		//获取用户上次提现时间
    		$UserWithdraw = M('UserWithdraw');
    		$where['uid'] = $uid;
    		$res = $UserWithdraw->field('create_time')->where($where)->order('create_time desc')->find();
    		if($res === NULL){
    			$starttime = $create_time;
    		}else{
    			$starttime = $res['create_time'];
    		}
    				
		//判断是否3天获得100金币
    		if($txmoney == 300){
    			$wherey3['uid'] = $uid;
    			$wherey3['money'] = 300;
			$yuan3 = $UserWithdraw->where($wherey3)->count();//判断用户是否首次提现3元
			if($yuan3){
				//获取是否已(3天)赚到1毛的收益
				$CountDiscipleProfitData = M('CountDiscipleProfitData');
				$where10['uid'] = $uid;
				$where10['type'] = 2;
				$where10['create_time'] = array('egt',$starttime);
				$res10 = $CountDiscipleProfitData->where($where10)->count();
				if($res10 < 3){
					$ajaxReturn['code'] = 501;
		    			$ajaxReturn['msg'] = $res10;
		    			return $ajaxReturn;
				}
			}
    		}
    		
    		$AdExpenditureRecord = M('AdExpenditureRecord');
		$ArticleReadRecord = M('ArticleReadRecord');
    		$UserMoneyRecord = M('UserMoneyRecord');
    		//获取总收入
    		$where3['uid'] = $uid;
    		$where3['cid'] = array('not in','6');
    		$where3['create_time'] = array('egt',$starttime);
    		$countmoney = $UserMoneyRecord->where($where3)->sum('money');
    		//------------------------------------------------系统基本规则自动检测--------------------------------------------------
		
		$time24 = time() - 86400;//获取24小时前的时间
		$where['create_time'] = array('egt',$starttime);
		
		$res8 = $ArticleReadRecord->field('user_ip')->where($where)->select();
		$c8 = count($res8);
//		$a9 = 0;
//		$b9 = 0;
//		for($i=0;$i<$c8;$i++){
//			if($res8[$i]['user_ip'] == '101.227.139.161' || $res8[$i]['user_ip'] == '183.61.51.54' || $res8[$i]['user_ip'] == '183.61.51.62' || $res8[$i]['user_ip'] == '83.61.51.55' || $res8[$i]['user_ip'] == '101.227.139.172'){
//				continue;
//			}
//			$s = substr($res8[$i]['user_ip'],0,strrpos($res8[$i]['user_ip'],'.'));
//			$data8[$s] += 1; 
//		}
//		$data9 = array_keys($data8);
//		$c9 = count($data9);
//		for($i=0;$i<$c9;$i++){
//			if($data8[$data9[$i]] > 5){
//				$a9 += 1;
//			}elseif($data8[$data9[$i]] > 1){
//				$b9 += 1;
//			}
//		}
//		
//		if($a9 > 0 || $b9 > 2){
//			$data[] = '文章，ip前三段，单组大于5的有:'.$a9.'个，单组>=2的有'.$b9;
//				
//			$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
//			$data = array_merge($data,$data2);
//			
//			$ajaxReturn['code'] = 500;
//  			$ajaxReturn['msg'] = $data;
//  			return $ajaxReturn;
//		}
		
		if($c8 > 49){
			//文章-获取ip最高点击次数
			$res1 = $ArticleReadRecord->field('user_ip,count(id) as num')->where($where)->group('user_ip')->order('count(id) desc')->select();
			if($res1[0]['num'] > 49){
				$data[] = '文章，ip最高点击次数超过50---';
				$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
				$data = array_merge($data,$data2);
				$ajaxReturn['code'] = 500;
	    			$ajaxReturn['msg'] = $data;
	    			return $ajaxReturn;
			}
			$n1 = 0;
			$c1 = count($res1);
			for($i=0;$i<$c1;$i++){
				if($res1[$i]['num'] > 9){
					$n1 += 1;
				}else{
					break;
				}
				if($n1 == 5){
					break;
				}
			}
			
			if($n1 >= 5){
				$data[] = '文章，ip点击超过9的个数>=5---';
				
				$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
				$data = array_merge($data,$data2);
				
				$ajaxReturn['code'] = 500;
	    			$ajaxReturn['msg'] = $data;
	    			return $ajaxReturn;
			}elseif($res1[0]['num'] == 1){
				$data[] = '文章，ip的点击全部为1---';
				
				$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
				$data = array_merge($data,$data2);
				
				$ajaxReturn['code'] = 500;
	    			$ajaxReturn['msg'] = $data;
	    			return $ajaxReturn;
			}else{
				$n1 = 0;
				for($i=0;$i<$c1;$i++){
					if($res1[$i]['num'] > 1){
						$n1 += 1;
					}else{
						break;
					}
				}
				
				$bf1 = $n1 / $c1;
				if($bf1 >= 0.7){
					$data[] = '文章，同一ip的点击>1的占总ip个数的'.$bf1.'---';
					
					$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
					$data = array_merge($data,$data2);
					
					$ajaxReturn['code'] = 500;
		    			$ajaxReturn['msg'] = $data;
		    			return $ajaxReturn;
				}
			}
			//文章-获取设备最高点击次数
			$res2 = $ArticleReadRecord->field('equipment_model,count(id) as num')->where($where)->group('equipment_model')->order('count(id) desc')->select();
			if($res2[0]['num'] > 49){
				$data[] = '文章，设备最高点击次数超过50---';
				$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
				$data = array_merge($data,$data2);
				$ajaxReturn['code'] = 500;
	    			$ajaxReturn['msg'] = $data;
	    			return $ajaxReturn;
			}
			$n1 = 0;
			$c1 = count($res2);
			//判断文章设备<5,设备点击次数>20，>20的设备>2
			if(6 > $c1 && $res2[0]['num'] > 19){
				$data[] = '文章，设备个数<6,点击次数为：'.$res2[0]['num'];
				
				$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
				$data = array_merge($data,$data2);
				
				$ajaxReturn['code'] = 500;
	    			$ajaxReturn['msg'] = $data;
	    			return $ajaxReturn;
			}
			
			for($i=0;$i<$c1;$i++){
				if($res2[$i]['num'] > 9){
					$n1 += 1;
				}else{
					break;
				}
				if($n1 == 5){
					break;
				}
			}
			
			if($n1 >= 5){
				$data[] = '文章，设备点击超过9的个数>=5---';
				
				$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
				$data = array_merge($data,$data2);
				
				$ajaxReturn['code'] = 500;
	    			$ajaxReturn['msg'] = $data;
	    			return $ajaxReturn;
			}elseif($res2[0]['num'] == 1){
				$data[] = '文章，设备的点击全部为1---';
				
				$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
				$data = array_merge($data,$data2);
				
				$ajaxReturn['code'] = 500;
	    			$ajaxReturn['msg'] = $data;
	    			return $ajaxReturn;
			}else{
				$n1 = 0;
				for($i=0;$i<$c1;$i++){
					if($res2[$i]['num'] > 1){
						$n1 += 1;
					}else{
						break;
					}
				}
				
				$bf1 = $n1 / $c1;
				if($bf1 >= 0.7){
					$data[] = '文章，同一设备的点击>1的占总设备个数的'.$bf1.'---';
					
					$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
					$data = array_merge($data,$data2);
					
					$ajaxReturn['code'] = 500;
		    			$ajaxReturn['msg'] = $data;
		    			return $ajaxReturn;
				}
			}
		}
		
		$where['type'] = array('lt',3);
		$where['equipment_model'] = array('neq','');
		$res8 = $AdExpenditureRecord->field('user_ip')->where($where)->select();
		$c8 = count($res8);
//		$a9 = 0;
//		$b9 = 0;
//		for($i=0;$i<$c8;$i++){
//			if($res8[$i]['user_ip'] == '101.227.139.161' || $res8[$i]['user_ip'] == '183.61.51.54' || $res8[$i]['user_ip'] == '183.61.51.62' || $res8[$i]['user_ip'] == '83.61.51.55' || $res8[$i]['user_ip'] == '101.227.139.172'){
//				continue;
//			}
//			$s = substr($res8[$i]['user_ip'],0,strrpos($res8[$i]['user_ip'],'.'));
//			$data8[$s] += 1; 
//		}
//		$data9 = array_keys($data8);
//		$c9 = count($data9);
//		for($i=0;$i<$c9;$i++){
//			if($data8[$data9[$i]] > 5){
//				$a9 += 1;
//			}elseif($data8[$data9[$i]] > 1){
//				$b9 += 1;
//			}
//		}
//		
//		if($a9 > 0 || $b9 > 2){
//			$data[] = '硬广，ip前三段，单组大于5的有:'.$a9.'个，单组>=2的有'.$b9;
//				
//			$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
//			$data = array_merge($data,$data2);
//			
//			$ajaxReturn['code'] = 500;
//  			$ajaxReturn['msg'] = $data;
//  			return $ajaxReturn;
//		}
		if($c8 > 49){
			//广告-获取ip最高点击次数
			$res3 = $AdExpenditureRecord->field('user_ip,count(id) as num')->where($where)->group('user_ip')->order('count(id) desc')->select();
			if($res3[0]['num'] > 49){
				$data[] = '硬广，ip最高点击次数超过50---';
				$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
				$data = array_merge($data,$data2);
				$ajaxReturn['code'] = 500;
	    			$ajaxReturn['msg'] = $data;
	    			return $ajaxReturn;
			}
			$n1 = 0;
			$c1 = count($res3);
			for($i=0;$i<$c1;$i++){
				if($res3[$i]['num'] > 9){
					$n1 += 1;
				}else{
					break;
				}
				if($n1 == 5){
					break;
				}
			}
			
			if($n1 >= 5){
				$data[] = '硬广，ip点击超过9的个数>=5---';
				$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
				$data = array_merge($data,$data2);
				$ajaxReturn['code'] = 500;
	    			$ajaxReturn['msg'] = $data;
	    			return $ajaxReturn;
			}elseif($res3[0]['num'] == 1){
				$data[] = '硬广，ip的点击全部为1---';
				$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
				$data = array_merge($data,$data2);
				$ajaxReturn['code'] = 500;
	    			$ajaxReturn['msg'] = $data;
	    			return $ajaxReturn;
			}else{
				$n1 = 0;
				for($i=0;$i<$c1;$i++){
					if($res3[$i]['num'] > 1){
						$n1 += 1;
					}else{
						break;
					}
				}
				
				$bf1 = $n1 / $c1;
				if($bf1 >= 0.7){
					$data[] = '硬广，同一ip的点击>1的占总ip个数的'.$bf1.'---';
					$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
					$data = array_merge($data,$data2);
					$ajaxReturn['code'] = 500;
		    			$ajaxReturn['msg'] = $data;
		    			return $ajaxReturn;
				}
			}
			
			//广告-获取设备最高点击次数
			$res4 = $AdExpenditureRecord->field('equipment_model,count(id) as num')->where($where)->group('equipment_model')->order('count(id) desc')->select();
			if($res4[0]['num'] > 49){
				$data[] = '硬广，设备最高点击次数超过50---';
				$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
				$data = array_merge($data,$data2);
				$ajaxReturn['code'] = 500;
	    			$ajaxReturn['msg'] = $data;
	    			return $ajaxReturn;
			}
			$n1 = 0;
			$c1 = count($res4);
			//判断文章设备<5,设备点击次数>20，>20的设备>2
			if(6 > $c1 && $res4[0]['num'] > 19){
				$data[] = '硬广，设备个数<6,点击次数为：'.$res4[0]['num'];
				
				$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
				$data = array_merge($data,$data2);
				
				$ajaxReturn['code'] = 500;
	    			$ajaxReturn['msg'] = $data;
	    			return $ajaxReturn;
			}
			
			for($i=0;$i<$c1;$i++){
				if($res4[$i]['num'] > 9){
					$n1 += 1;
				}else{
					break;
				}
				if($n1 == 5){
					break;
				}
			}
			
			if($n1 >= 5){
				$data[] = '硬广，设备点击超过9的个数>=5---';
				$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
				$data = array_merge($data,$data2);
				$ajaxReturn['code'] = 500;
	    			$ajaxReturn['msg'] = $data;
	    			return $ajaxReturn;
			}elseif($res4[0]['num'] == 1){
				$data[] = '硬广，设备的点击全部为1---';
				$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
				$data = array_merge($data,$data2);
				$ajaxReturn['code'] = 500;
	    			$ajaxReturn['msg'] = $data;
	    			return $ajaxReturn;
			}else{
				$n1 = 0;
				for($i=0;$i<$c1;$i++){
					if($res4[$i]['num'] > 1){
						$n1 += 1;
					}else{
						break;
					}
				}
				
				$bf1 = $n1 / $c1;
				if($bf1 >= 0.7){
					$data[] = '硬广，同一设备的点击>1的占总设备个数的'.$bf1.'---';
					$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
					$data = array_merge($data,$data2);
					$ajaxReturn['code'] = 500;
		    			$ajaxReturn['msg'] = $data;
		    			return $ajaxReturn;
				}
			}
		}
		
    		//------------------------------------------------系统基本规则自动检测------------------------------------------------
    		
    		//------------------------------------------------收徒规则-----------------------------------------------------------
    		//获取用户收徒总数
    		$Disciple = M('Disciple');
    		$where1['masterid'] = $uid;
    		$count = $Disciple->where($where1)->count();
    		if($count >= 20){//判断有效徒弟是否>=20个
    			//判断所有徒弟总金额>=1.5元的个数占总徒弟个数的百分比
    			$User = M('User');
    			$where0['a.masterid'] = $uid;
			$t_count = $User->alias('a')->where($where0)->count();//获取总徒弟数
							
			$where0['b.total_money'] = array('egt',250);
			$y_count = $User->alias('a')->field('a.id')->join('__USER_ACCOUNT__ b ON a.id = b.uid','LEFT')->where($where0)->count();
			$bfb = round($y_count / $t_count,1);
    			if($bfb >= 0.5){//判断所有徒弟总金额>=2.5元的个数占总徒弟个数的百分比
    				//获取收徒总收入
		    		$where4['uid'] = $uid;
		    		$where4['cid'] = array('in','2,4,5');
		    		$where4['create_time'] = array('egt',$starttime);
		    		$dismoney = $UserMoneyRecord->where($where4)->sum('money');
		    		if(($dismoney/$countmoney) >= 0.8){//判断徒弟收入占总收入的比是否大于80%
					//获取异常徒弟数
			    		$where2['a.masterid'] = $uid;
			    		$where2['b.status'] = 3;
			    		$yc = $Disciple->alias('a')->field('a.student_uid')->join('__USER__ b ON a.student_uid = b.id')->where($where2)->count();
			    		if(($yc/$count) < 0.5){//判断异常徒弟是否大于总徒弟数的50%
						$where9['cid'] = array('in','4,5');
						$where9['uid'] = $uid;
						$where9['student_uid'] = array('gt',0);
						$where9['create_time'] = array('egt',$starttime);
						$arr9 = $UserMoneyRecord->field('sum(money) as money,student_uid')->where($where9)->group('student_uid')->order('sum(money) desc')->limit(1)->select();
						if($arr9[0]['money'] < 500){//判断有没有徒弟一天给他带来徒弟提成大于5元的
							$data[] = '符合收徒提现规则！';
							$ajaxReturn['code'] = 200;
				    			$ajaxReturn['msg'] = $data;
				    			return $ajaxReturn;
						}else{
							$data[] = '有徒弟一天给他带来提成奖励5元，徒弟或徒孙id.'.$arr9[0]['student_uid'].'---';
						}
			    		}else{
			    			$data[] = '异常徒弟大于50%---';
			    		}
		    		}else{
		    			$data[] = '徒弟收入占总收入的比不足80%---';
		    		}
    			}else{
    				$data[] = '总徒数：'.$t_count.',>=2.5元徒数：'.$y_count.'占比：'.$bfb.'---';
    			}
    		}elseif($count <= 5){
    			//获取收徒总收入
	    		$where4['uid'] = $uid;
	    		$where4['cid'] = array('in','2,4,5');
	    		$where4['create_time'] = array('egt',$starttime);
	    		$dismoney = $UserMoneyRecord->where($where4)->sum('money');
	    		
	    		//判断徒弟收入是否占总收入的比超过90%
	    		if(($dismoney/$countmoney) >= 0.9){
	    			$data[] = '徒弟数小于<=5,并且徒弟收入占总收入的比超过90%---';
	    			$data2 = $this->userAdDetails($countmoney,$starttime,$uid);
				$data = array_merge($data,$data2);
	    			$ajaxReturn['code'] = 500;
	    			$ajaxReturn['msg'] = $data;
	    			return $ajaxReturn;
	    		}
    		}else{
    			$data[] = '有效徒弟不足20个，目前有'.$count.'---';
    		}
    		//------------------------------------------------收徒规则-----------------------------------------------------------
    		
    		
    		//------------------------------------------------阅读规则-----------------------------------------------------------
    		//获取普通文章收入
    		$where5['uid'] = $uid;
    		$where5['cid'] = 1;
    		$where5['create_time'] = array('egt',$starttime);
    		$mymoney = $UserMoneyRecord->where($where5)->sum('money');
    		if(!$mymoney){
    			$mymoney = 0;
    		}
    		$status1 = 1;
    		if($mymoney/$countmoney >= 0.9){
    			$data[] = '普通文章收入大于总收入的90%,普通文章收入为'.($mymoney/100).'总收入为'.($countmoney/100).'---';
//			$status1 = 0;
    		}else{
    			$data[] = '普通文章收入为'.($mymoney/100).'总收入为'.($countmoney/100).'---';
    		}
    		
    		//获取高价文章收入
    		$where6['uid'] = $uid;
    		$where6['cid'] = 7;
    		$where6['create_time'] = array('egt',$starttime);
    		$gjmoney = $UserMoneyRecord->where($where6)->sum('money');
    		if(!$gjmoney){
    			$gjmoney = 0;
    		}
    		$status2 = 1;
    		if($gjmoney/$countmoney >= 0.9){
    			$data[] = '高价文章收入大于总收入的90%,高价文章收入为'.($gjmoney/100).'总收入为'.($countmoney/100).'---';
    			
			$status2 = 0;
    		}else{
    			$data[] = '高价文章收入为'.($gjmoney/100).'总收入为'.($countmoney/100).'---';
    		}
    		
    		//获取所有广告费（普通文章/视频分享出去产生的广告费）
    		$where7['uid'] = $uid;
    		$where7['type'] = 2;
    		$where7['create_time'] = array('egt',$starttime);
    		$ygmoney = $AdExpenditureRecord->where($where7)->sum('money');
    		if(!$ygmoney){
    			$ygmoney = 0;
    			$price = 0;
    		}else{
    			$price = round($ygmoney/$mymoney,2);
    		}
    		
    		//获取该金额对应用户的扣量规则
    		$where8['start_money'] = array('elt',$mymoney);
    		$UserWithdrawRule = M('UserWithdrawRule');
    		$res1 = $UserWithdrawRule->field('number,percent')->where($where8)->order('end_money desc')->find();
    		
    		if($res1['percent'] <= $price && $price < $res1['number']){//判断广告费占比
    			$status4 = 1;
    			$data[] = '广告费金额为'.($ygmoney/100).'普通文章金额为'.($mymoney/100).'占比为'.$price.'---';
    		}else{
    			$data[] = '广告费占比不正常,广告费金额为'.($ygmoney/100).'普通文章金额为'.($mymoney/100).'占比为'.$price.'---';
    			$status4 = 0;
    		}
    		
    		if($status1 && $status2 && $status4){
    			$ajaxReturn['code'] = 200;
    			$ajaxReturn['msg'] = $data;
    			return $ajaxReturn;
    		}else{
    			$ajaxReturn['code'] = 500;
    			$ajaxReturn['msg'] = $data;
    			return $ajaxReturn;
    		}
    		//------------------------------------------------阅读规则-----------------------------------------------------------
    }
    
    private function userAdDetails($countmoney,$starttime,$uid){
    		$UserMoneyRecord = M('UserMoneyRecord');
    		//获取普通文章收入
    		$where5['uid'] = $uid;
    		$where5['cid'] = 1;
    		$where5['create_time'] = array('egt',$starttime);
    		$mymoney = $UserMoneyRecord->where($where5)->sum('money');
    		if(!$mymoney){
    			$mymoney = 0;
    		}

    		if($mymoney/$countmoney >= 0.9){
    			$data[] = '普通文章收入大于总收入的90%,普通文章收入为'.($mymoney/100).'总收入为'.($countmoney/100).'---';
    		}else{
    			$data[] = '普通文章收入为'.($mymoney/100).'总收入为'.($countmoney/100).'---';
    		}
    		
    		//获取高价文章收入
    		$where6['uid'] = $uid;
    		$where6['cid'] = 7;
    		$where6['create_time'] = array('egt',$starttime);
    		$gjmoney = $UserMoneyRecord->where($where6)->sum('money');
    		if(!$gjmoney){
    			$gjmoney = 0;
    		}

    		if($gjmoney/$countmoney >= 0.9){
    			$data[] = '高价文章收入大于总收入的90%,高价文章收入为'.($gjmoney/100).'总收入为'.($countmoney/100).'---';
    		}else{
    			$data[] = '高价文章收入为'.($gjmoney/100).'总收入为'.($countmoney/100).'---';
    		}
    		
    		//获取所有广告费（普通文章/视频分享出去产生的广告费）
    		$AdExpenditureRecord = M('AdExpenditureRecord');
    		$where7['uid'] = $uid;
    		$where7['type'] = 2;
    		$where7['create_time'] = array('egt',$starttime);
    		$ygmoney = $AdExpenditureRecord->where($where7)->sum('money');
    		if(!$ygmoney){
    			$ygmoney = 0;
    			$price = 0;
    		}else{
    			$price = round($ygmoney/$mymoney,2);
    		}
    		
    		//获取该金额对应用户的扣量规则
    		$where8['start_money'] = array('elt',$mymoney);
    		$UserWithdrawRule = M('UserWithdrawRule');
    		$res1 = $UserWithdrawRule->field('number,percent')->where($where8)->order('end_money desc')->find();
    		
    		if($res1['percent'] <= $price && $price < $res1['number']){//判断广告费占比
    			$data[] = '广告费金额为'.($ygmoney/100).'普通文章金额为'.($mymoney/100).'占比为'.$price.'---';
    		}else{
    			$data[] = '广告费占比不正常,广告费金额为'.($ygmoney/100).'普通文章金额为'.($mymoney/100).'占比为'.$price.'---';
    		}
    		
    		return $data;
    }

    //-----------------------------------微信商户向用户付款结束---------------------------------------------------------------
	
}
