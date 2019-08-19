<?php
/**
 * Created by PhpStorm.
 * User: wangtong
 * Date: 18/03/19
 * Time: 下午16:04
 */
namespace app\hapi\controller;
use think\Db;

class UseraccountController extends BaseController
{
    
    //构造方法
    public function __construct(){
        parent::__construct();
    }
    
    //手机号登录
    public function userPhoneLogin(){
        $phone = I('post.phone');//用户手机号
        $password = getPwdEncodeString(I('post.password'));//密码
        $this->CheckParam($phone);//判断参数是否缺失
        
        $User = M('User');
        $where['phone'] = $phone;
        $userinfo = $User->field('id,user_id,password,status,platform_type,channel,litpic')->where($where)->find();
        //判断账号是否存在
        if(!$userinfo){
        		$ajaxReturn['code'] = 501;
        		$ajaxReturn['msg'] = '该手机号不存在，请选择微信登录！';
        		$this->ajaxReturn($ajaxReturn);
        }
        //判断账号是否封停
        if($userinfo['status'] == 4){
        		$ajaxReturn['code'] = 502;
        		$ajaxReturn['msg'] = '该账号已封停，请联系客服！';
        		$this->ajaxReturn($ajaxReturn);
        }
        //判断密码是否正确
        if($password != '' && $userinfo['password'] === $password){
        		//封装数组，将用户信息存到Redis里
        		$token = md5($userinfo['id'] . time());
            $data['id'] = $userinfo['id'];
            $data['user_id'] = $userinfo['user_id'];
            $data['litpic'] = $userinfo['litpic'];
            $data['channel'] = $userinfo['channel'];
            $redisResult = $this->redis->set($token, json_encode($data),86400*60);
            
            //将用户token存到redis（后台封用户账号时用到）
            $this->redis->set('WT_AppUserToken'.$userinfo['id'],$token,86400*60);
            
            //添加大表登录记录
            parent::addDataRecord(1,$userinfo['id'],0,0,$userinfo['platform_type'],$userinfo['channel']);
            
            if($redisResult){
                $ajaxReturn['code'] = 200;
                $ajaxReturn['msg'] = 'SUCCESS';
                $ajaxReturn['data']['token'] = $token;
                $ajaxReturn['data']['uid'] = $userinfo['id'];
            } else {
                $ajaxReturn['code'] = 500;
                $ajaxReturn['msg'] = '登录失败,请稍后重试!';
            }
        }else{
        		$ajaxReturn['code'] = 503;
        		$ajaxReturn['msg'] = '手机号或密码错误！';
        }
        
        $this->ajaxReturn($ajaxReturn);
    }
    
    //微信登录
    public function userWechatLogin(){
    		
        $platform_type = I('get.platform_type');
        $channel = I('get.channel');
        $phone_model = I('get.phone_model_info');
        $device_id = I('get.device_id');
        $divece_brand = I('get.divece_brand');
        //获取客户端的ip
        $app_ip = I('get.app_ip');
        $ip = get_client_ip(0, true);
        $CODE = I('get.code');//用户授权之后获取code
        if($CODE){
            $APPID = 'wx0361df005cc6b004';
            $SECRET = '655979b4a121004b5600f15105146d8b';
            $url= "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$APPID&secret=$SECRET&code=$CODE&grant_type=authorization_code";
            $arr = $this->postData($url);//通过code换取access_token和openid
            $dres = json_decode($arr,true);//将json转换为数组
            if($dres['access_token']){
                $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$dres['access_token']."&openid=".$dres['openid']."&lang=zh_CN";
                $row = $this->postData($url);//拉取用户的信息
                $drow = json_decode($row,true);
                $user = M('user');
                $unionid_where['unionid'] = $drow['unionid'];
                $unionid_result = $user->field('id,user_id,phone,status,platform_type,channel,litpic')->where($unionid_where)->find();
                //判断unionid是否存在
                if($unionid_result){
                		//判断用户是否封号
                    if ($unionid_result['status'] == 4) {
                        $ajaxReturn['code'] = 502;
                        $ajaxReturn['msg'] = '该账号已封停，请联系客服！';
                        $this->ajaxReturn($ajaxReturn);
                    }
                    
                    //封装数组，将用户信息存到Redis里
                    $token = md5($unionid_result['id'].time());
                    $userInfo['id'] = $unionid_result['id'];
                    $userInfo['user_id'] = $unionid_result['user_id'];
                    $userInfo['litpic'] = $unionid_result['litpic'];
                    $userInfo['channel'] = $unionid_result['channel'];
                    $redisResult = $this->redis->set($token, json_encode($userInfo),86400*60);
                    
                    //将用户token存到redis（后台封用户账号时用到）
            			$this->redis->set('WT_AppUserToken'.$unionid_result['id'],$token,86400*60);
                    
                    //添加大表登录记录
            			parent::addDataRecord(1,$unionid_result['id'],0,0,$unionid_result['platform_type'],$unionid_result['channel']);
            			
                    if($redisResult){
                        $ajaxReturn['code'] = 200;
                        $ajaxReturn['msg'] = 'SUCCESS';
                        $ajaxReturn['data']['token'] = $token;
                        $ajaxReturn['data']['uid'] = $unionid_result['id'];
                        $ajaxReturn['data']['phone_type'] = $unionid_result['phone'] ? 1 : 0;;
                    }else{
                        $ajaxReturn['code'] = 500;
                        $ajaxReturn['msg'] = '登录失败,请稍后重试!';
                    }
                    
                }else{//用户不存在执行注册
                	
                		//判断设备号是否已经存在
                		if($device_id){
                			$dwhere['device_id'] = $device_id;
	                		$darr = $user->where($dwhere)->count();
	                		if($darr > 1){
	                			$ajaxReturn['code'] = 500;
	                        	$ajaxReturn['msg'] = '1个手机仅限登录2个微信哦!';
	                			$this->ajaxReturn($ajaxReturn);
	                		}
                		}
                		
                		$time = time();
                		//判断用户有没有师父
                    $DiscipleIpRecord = M('DiscipleIpRecord');
			    		$unionid = $drow['unionid'];
			    		$where['_string'] = "user_ip = '{$ip}'  OR  user_ip = '{$app_ip}' OR unionid = '{$unionid}'";
			    		$res = $DiscipleIpRecord->field('uid,one_type,second_type')->where($where)->order('create_time desc')->find();
                		if($res['uid'] > 0){
                    		$data['masterid'] = $res['uid'];
                		}
                    $data['name'] = $drow['nickname'] ? $drow['nickname'] : 'KY'.$time;
                    $data['litpic'] = $drow['headimgurl'] ? $drow['headimgurl'] : 'http://t.cn/RuHQPZF';
                    $data['sex'] = $drow['sex'];
                    $data['unionid'] = $drow['unionid'];
                    $data['openid'] = $drow['openid'];
                    $data['platform_type'] = $platform_type;
                    $data['channel'] = $channel;
                    $data['user_ip'] = $ip;
                    $data['phone_model'] = $phone_model;
                    $data['device_id'] = $device_id;
                    $data['divece_brand'] = $divece_brand;
                    $data['create_time'] = $time;
                    $data['update_time'] = $time;
                    if(RedisLink::get_instance()) {
                        $user_id_value = RedisLink::get_instance()->get('WT_AppCreateUserId');
                        if (isset($user_id_value) && strlen($user_id_value) >= 6) {
                            $data['user_id'] = RedisLink::get_instance()->incr('WT_AppCreateUserId');
                        } else {
                            $data['user_id'] = random();
                        }
                    }else{
                        $data['user_id'] = random();
                    }
                    //执行用户注册
                    $insert_result = $user->add($data);
                    if ($insert_result) {
                    		//添加用户账户
                    		$UaerAccount = M('UserAccount');
                    		$accdata['uid'] = $insert_result;
                    		$accdata['total_gold'] = 1000;
                    		$accdata['gold'] = 1000;
                    		$accdata['create_time'] = $data['create_time'];
                    		$UaerAccount->add($accdata);
                    		//添加用户收益流水记录
						$UserGoldRecord = M('UserGoldRecord');
						$data1['uid'] = $insert_result;
						$data1['cid'] = 8;
						$data1['gold'] = 1000;
						$data1['after_gold'] = 1000;
						$data1['create_time'] = $time;
						$UserGoldRecord->add($data1);
                    		
                    		//封装数组，将用户信息存到Redis里
                        $userInfo = array();
                        $token = md5($insert_result . $time);
                        $userInfo['id'] = $insert_result;
                        $userInfo['user_id'] = $data['user_id'];
                        $userInfo['litpic'] = $data['litpic'];
                        $userInfo['channel'] = $channel;
                        $redisResult = $this->redis->set($token, json_encode($userInfo), 86400*60);
                        
                        //将用户token存到redis（后台封用户账号时用到）
            			    $this->redis->set('WT_AppUserToken'.$insert_result,$token,86400*60);
                        
                        //扣量数据插入队列
						$this->redis->rpush('WT_AppQueueLists',$insert_result);
                        
                        //添加大表登录、注册记录
            				parent::addDataRecord(1,$insert_result,0,0,$platform_type,$channel);
            				parent::addDataRecord(2,$insert_result,0,0,$platform_type,$channel);
                        
                        if($res['uid'] > 0){
                        		//判断他师父有没有师父
                        		$Disciple = M('Disciple');
                        		$Dwhere['student_uid'] = $res['uid'];
                        		$DisInfo = $Disciple->field('masterid')->where($Dwhere)->find();
                        		if($DisInfo['masterid'] != '' && $DisInfo['masterid'] > 0){
                        			$masterfatherid = $DisInfo['masterid'];
                        		}else{
                        			$masterfatherid = 0;
                        		}
                        		
                        		//执行师徒绑定
                        		$data['student_uid'] = $insert_result;
                        		$data['masterid'] = $res['uid'];
                        		$data['masterfatherid'] = $masterfatherid;
                        		$data['create_time'] = $time;
                        		$res3 = $Disciple->add($data);
                        		
                        		//------------------------------转盘活动------------------------------
	        
					        //获取用户今天有没有获得收徒奖励转盘3次
//							$istrue = $this->redis->get('WT_AppActivityDiscIsTrue'.$res['uid']);
//							if(!$istrue){
//								//获取用户今天获得几次点击了
//								$num2 = $this->redis->get('WT_AppActivityDiscNum'.$res['uid']);
//								if($num2 == 2){
//									//设置今日已获得转盘1次
//									$this->redis->set('WT_AppActivityDiscIsTrue'.$res['uid'],1);
//								}
//								
//								//执行转盘次数添加操作
//								$ActivityTurntable = M('ActivityTurntable');
//								$where2['uid'] = $res['uid'];
//								$res2 = $ActivityTurntable->where($where2)->find();
//								if($res2){
//									$ActivityTurntable->where($where2)->setInc('num');
//								}else{
//									$data2['uid'] = $res['uid'];
//									$data2['num'] = 1;
//									$ActivityTurntable->add($data2);
//								}
//								
//								$this->redis->set('WT_AppActivityDiscNum'.$res['uid'],$num2+1);
//							}
					        
					        //------------------------------转盘活动------------------------------
                        		
                        		//清除“收徒-我的好友”缓存
						    $keys = $this->redis->keys('WT_AppDiscipleList'.$res['uid'].'*');
						    for ($i = 0; $i < count($keys); $i++) {
						        $this->redis->set($keys[$i],null);
						    }
                        		
                        		//添加大表收徒记录
                        		parent::addDataRecord(5,$insert_result,0,0,$platform_type,$channel,$res['uid'],$masterfatherid,0,0,0,0,$res['one_type'],$res['second_type']);
                        		//输入师傅邀请码－任务福利
							parent::addWelfareRecord(1,2,1000,$channel,$platform_type,$insert_result,2);
                        }
                        
                        if($redisResult){
                            $ajaxReturn['code'] = 200;
                            $ajaxReturn['msg'] = 'SUCCESS';
                            $ajaxReturn['data']['token'] = $token;
                            $ajaxReturn['data']['uid'] = $insert_result;
                            $ajaxReturn['data']['phone_type'] = 0;//1已绑定手机号0未绑定
                        }else{
                            $ajaxReturn['code'] = 500;
                            $ajaxReturn['msg'] = '登录失败,请稍后重试!';
                        }
                    }
                }
	        }else{
	        		$ajaxReturn['code'] = 601;
            		$ajaxReturn['msg'] = 'access_token获取失败!';
	        }
        }else{
            $ajaxReturn['code'] = 600;
            $ajaxReturn['msg'] = 'code参数缺失!';
        }
        $this->ajaxReturn($ajaxReturn);
    }
    
    //退出登录
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
    
    //找回密码
    public function resetPassword(){
    		$type = 1;//1、找回密码 2、绑定手机号
    		$phone = trim(I('post.phone'));
    		$code = I('post.code');
    		$password = getPwdEncodeString(I('post.password'));
    		
    		//检测验证码是否正确
    		$check = $this->forgot_submit($phone,$code,$type);
    		if($check['code'] == 200){
    			$User = M('User');
    			$where['phone'] = $phone;
    			$data['password'] = $password;
    			$res = $User->where($where)->save($data);
    			if($res){
    				$ajaxReturn['code'] = 200;
    				$ajaxReturn['msg'] = 'SUCCESS';
    			}else{
    				$ajaxReturn['code'] = 500;
    				$ajaxReturn['msg'] = '失败，请稍后重试！';
    			}
    		}else{
    			$ajaxReturn['code'] = $check['code'];
            $ajaxReturn['msg'] = $check['msg'];
    		}
    		
    		$this->ajaxReturn($ajaxReturn);
    }
    
    //判断验证码是否正确
    protected function forgot_submit($phone,$checkCode,$type){
        $redisResult = json_decode($this->redis->get('zhmm_check'.$phone.$type),true);
        if($redisResult === NULL){
        		$ajaxReturn['code'] = 301;
            $ajaxReturn['msg'] = '验证码填写错误！';
            return $ajaxReturn;
        }
        
        $time = $redisResult['time'];
        $code = $redisResult['code'];
        if($code != md5($phone.$checkCode)){
            $ajaxReturn['code'] = 301;
            $ajaxReturn['msg'] = '验证码填写错误！';
        }elseif($time + 300 < time()){
            $ajaxReturn['code'] = 302;
            $ajaxReturn['msg'] = '验证码已过期！';
        }else{
            $ajaxReturn['code'] = 200;
            $ajaxReturn['msg'] = "SUCCESS";
        }
        
        return $ajaxReturn;
    }
    
    //绑定手机号
    public function bindingPhone(){
    		$type = 2;//1、找回密码 2、绑定手机号
    		$platform_type = I('post.platform_type');
//      $channel = I('post.channel');
    		$phone = trim(I('post.phone'));
    		$password = getPwdEncodeString(I('post.password'));
    		$code = I('post.code');
    		$token = I('post.token');
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		//检测验证码是否正确
    		$check = $this->forgot_submit($phone,$code,$type);
    		if($check['code'] == 200){
    			$User = M('User');
    			$where['id'] = $user_info['id'];
    			$data['phone'] = $phone;
    			$data['password'] = $password;
    			$res = $User->where($where)->save($data);
    			if($res){
    				//清除用户缓存
				A("Clearcache")->clearUserInfo($user_info['id']);
				
    				//绑定手机号－任务福利
				parent::addWelfareRecord(5,2,30,$user_info['channel'],$platform_type,$user_info['id'],2);
    				$ajaxReturn['code'] = 200;
    				$ajaxReturn['msg'] = 'SUCCESS';
    			}else{
    				$ajaxReturn['code'] = 500;
    				$ajaxReturn['msg'] = '失败，请稍后重试！';
    			}
    		}else{
    			$ajaxReturn['code'] = $check['code'];
            $ajaxReturn['msg'] = $check['msg'];
    		}
    		
    		$this->ajaxReturn($ajaxReturn);
    }
    
    //绑定手机号(公众号)
//  public function wx_bindingPhone(){
//  		$type = 2;//1、找回密码 2、绑定手机号
//  		$platform_type = 1;
//  		$phone = trim(I('post.phone'));
//  		$password = getPwdEncodeString(I('post.password'));
//  		$code = I('post.code');
//  		$uid = I('post.uid');
//  		$unionid = I('post.unionid');
//		
//		//检测验证码是否正确
//  		$check = $this->forgot_submit($phone,$code,$type);
//  		if($check['code'] == 200){
//  			$User = M('User');
//  			$where['unionid'] = $unionid;
//  			$data['phone'] = $phone;
//  			$data['password'] = $password;
//  			$res = $User->where($where)->save($data);
//  			if($res){
//  				//清除用户缓存
//				A("Clearcache")->clearUserInfo($uid);
//				
//  				//绑定手机号－任务福利
//				parent::addWelfareRecord(5,2,30,$user_info['channel'],$platform_type,$uid,2);
//  				$ajaxReturn['code'] = 200;
//  				$ajaxReturn['msg'] = 'SUCCESS';
//  			}else{
//  				$ajaxReturn['code'] = 500;
//  				$ajaxReturn['msg'] = '失败，请稍后重试！';
//  			}
//  		}else{
//  			$ajaxReturn['code'] = $check['code'];
//          $ajaxReturn['msg'] = $check['msg'];
//  		}
//  		
//  		$this->ajaxReturn($ajaxReturn);
//  }
    
    //用户活跃(打开app)记录
    public function addUserActive(){
        $device_id = I('post.device_id');
        $this->CheckParam($device_id);//判断参数是否缺失
        $platform_type = I('post.platform_type');
        $channel = I('post.channel');
		$ip = get_client_ip(0,true);//获取客户端的ip
    		$token = I('post.token');
		$user_info = json_decode($this->redis->get($token), true);

		if($user_info){
			$UserActiveRecord = M('UserActiveRecord');
			$where['uid'] = $user_info['id'];
			$res = $UserActiveRecord->where($where)->find();//判断用户是否首次打开app
			if($res){
				$data['create_time'] = time();
				$res = $UserActiveRecord->where($where)->save($data);
			}else{
				$data['uid'] = $user_info['id'];
				$data['create_time'] = time();
				$res = $UserActiveRecord->add($data);
			}
			//添加大表记录
			parent::addDataRecord(16,$user_info['id'],0,0,$platform_type,$user_info['channel'],0,0,0,0,$ip,$device_id);
		}else{
			//添加大表记录
			parent::addDataRecord(12,0,0,0,$platform_type,$channel,0,0,0,0,$ip,$device_id);
		}
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$this->ajaxReturn($ajaxReturn);
   	}
    
}