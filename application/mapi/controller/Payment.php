<?php
namespace app\mapi\controller;
use think\Db;

//提现 列表
class PaymentController extends BaseController{

    //配置自己公众号的参数 -- 给钱阅
//  private $appid = 'wx8e41e9a874959146'; //AppID(应用ID)
//  private $mch_id = '1514739641'; //商户号
//  private $key = '2018gei12qian05yue1shang2hu3hao4';//微信商户平台->API安全->API密钥
    
    //配置自己公众号的参数  -- 零花看点
    private $appid = 'wx1fea89bbd47ec18f'; //AppID(应用ID)
    private $mch_id = '1514415611'; //商户号
    private $key = 'ling12hua98kan05dian36ping47tai1';//微信商户平台->API安全->API密钥

    //-----------------------------------微信商户向用户付款开始---------------------------------------------------------------
	
	//拒绝打款
	public function setStatusInfo(){
		$token = I('post.token');//用户token
		$user_info = $this->IsAppLogin($token);
    		$id = I('post.id');//提现id
    		$type = I('post.type');//是否回滚 1回滚 0不回滚
    		
        $wwwhere['id'] = $id;
        $wwwhere['status'] = 0;
        $Mon = M('UserWithdraw');
        //获取当前是不是审核失败状态
        $arr = $Mon->where($wwwhere)->find();
        if(!$arr){
        		$ajaxReturn['code'] = 401;
        		$ajaxReturn['msg'] = '不合法！';
        		$this->ajaxReturn($ajaxReturn);
        }
        
        //执行拒绝打款并设置回滚状态
        if($type == 1){
        		$data['r_status'] = 1;
        }
        $data['status'] = 3;
        $where['id'] = $id;
        $res = $Mon->where($where)->save($data);
        if($res){
        		//执行钱回滚
        		if($type == 1){
        			$User = M('UserAccount');
        			$where1['uid'] = $arr['uid'];
        			$User->where($where1)->setInc('user_money',$arr['money']);
        		}
        		
        		$ajaxReturn['code'] = 200;
        		$ajaxReturn['msg'] = 'SUCCESS';
        }else{
        		$ajaxReturn['code'] = 500;
        		$ajaxReturn['msg'] = '失败，请稍后充值！';
        }
        
        $this->ajaxReturn($ajaxReturn);
	}
	
	//回滚提现
	public function setRollBack(){
		$token = I('post.token');//用户token
		$user_info = $this->IsAppLogin($token);
    		$id = I('post.id');//提现id
    		
        $wwwhere['id'] = $id;
        $wwwhere['status'] = 3;
        $wwwhere['r_status'] = 2;
        $Mon = M('UserWithdraw');
        //获取当前是不是拒绝打款状态
        $arr = $Mon->field('uid,money')->where($wwwhere)->find();
        if(!$arr){
        		$ajaxReturn['code'] = 401;
        		$ajaxReturn['msg'] = '不合法！';
        		$this->ajaxReturn($ajaxReturn);
        }
        
         
        $data1['r_status'] = 1;
        $where1['id'] = $id;
        $Mon->where($where1)->save($data1);
        
        $User = M('UserAccount');
        $where['uid'] = $arr['uid'];
        $res = $User->where($where)->setInc('user_money',$arr['money']); // 用户的积分加3
        if($res){
        		$ajaxReturn['code'] = 200;
        		$ajaxReturn['msg'] = 'SUCCESS';
        }else{
        		$ajaxReturn['code'] = 500;
        		$ajaxReturn['msg'] = '失败，请稍后充值！';
        }
        
        $this->ajaxReturn($ajaxReturn);
	}
	
	//给用户打钱
    public function setPayment(){
        $token = I('post.token');//用户token
		$user_info = $this->IsAppLogin($token);
    		$is_pullblack = I('post.is_pullblack',0);//1解封高价 0不解封高价
    		
    		$id = I('post.id');//提现id
        $wwwhere['a.id'] = $id;
        $wwwhere['a.status'] = 0;
        $Mon = M('UserWithdraw');
        $arr11 = $Mon->alias('a')
        					->field('a.money,a.uid,c.cash_openid,c.is_pullblack,c.unionid,b.user_money')
        					->join('__USER__ c ON a.uid = c.id','LEFT')
        					->join('__USER_ACCOUNT__ b ON a.uid = b.uid','LEFT')
        					->where($wwwhere)
        					->find();
        	//获取用户提现openid
        	$cash_openid = $this->redis->get('App_Com_uni'.$arr11['unionid']);
        	if(!$cash_openid){
        		$ajaxReturn['code'] = 402;
        		$ajaxReturn['msg'] = '未绑定公众号！';
        		$this->ajaxReturn($ajaxReturn);
        	}
        $money = $arr11['money'];
        if(!$arr11){
        		$ajaxReturn['code'] = 401;
        		$ajaxReturn['msg'] = '不合法！';
        		$this->ajaxReturn($ajaxReturn);
        }

        $nonce_str = $this->great_rand();//随机字符串
        $mch_id = $this->mch_id;//商户id
        $ip = get_client_ip();//调用接口的ip
        
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
        		//给用户解封高价
			if($is_pullblack == 1 && $arr11['is_pullblack'] == 1){
				$blwhere['id'] = $arr11['uid'];
    				$bldata['is_pullblack'] = 0;
    				M('User')->where($blwhere)->save($bldata);
    				$this->redis->set('WT_AppUserInfos'.$arr11['uid'],null);
			}
        		
        		//修改提现记录
            $dinfo['status'] = 1;
            $dinfo['hit_time'] = time();
            $arr1 = $Mon->alias('a')->where($wwwhere)->save($dinfo);
            if($arr1){
            		$ajaxReturn['code'] = 200;
            		$ajaxReturn['msg'] = '提现成功！';
            }else{
            		$ajaxReturn['code'] = 500;
            		$ajaxReturn['msg'] = '失败，请稍后重试！';
            }
        }else{
        		if($result['err_code'] == 'V2_ACCOUNT_SIMPLE_BAN'){
        			$ajaxReturn['code'] = 500;
           	 	$ajaxReturn['msg'] = '微信账户未通过实名认证!<br /><br />认证流程：<br />进入微信>我>钱包>银行卡>添加银行卡';
        		}else{
        			$ajaxReturn['code'] = 500;
           	 	$ajaxReturn['msg'] = $result['err_code'];
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

    //-----------------------------------微信商户向用户付款结束---------------------------------------------------------------

	
	//------------快阅读活动给用户打钱---------------------------------------------------------------------------------
	    
    //快阅读活动给用户打钱
    private function setPayment1($money,$openid){
die;
        $money = $money * 100;
        $nonce_str = $this->great_rand();//随机字符串
        $mch_id = $this->mch_id;//商户id
        $ip = get_client_ip();//调用接口的ip
        
        $params = array();
        $params['mch_appid'] = $this->appid;
        $params['mchid'] = $mch_id;
        $params['nonce_str'] = $nonce_str;
        $params['partner_trade_no'] = $this->get_dingdan();
        $params['openid'] = $openid;
        $params['check_name'] = 'NO_CHECK';
        $params['re_user_name'] = '张三';
        $params['amount'] = $money;
        $params['desc'] = '快阅读“收徒大竞赛”活动奖励';
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
        		echo '成功'.'<br />';
        }else{
        		echo '失败'.'<br />';
        }
    }
	
	//------------快阅读活动给用户打钱---------------------------------------------------------------------------------
}
