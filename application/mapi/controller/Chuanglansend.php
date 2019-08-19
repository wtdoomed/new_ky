<?php
namespace app\mapi\controller;
use Org\ChuanglanSmsApi;
use think\Db;

//短信发送控制器
class ChuanglansendController extends BaseController {
	
	//发送通知验证码
	public function Fasong($phone,$dname){
        $clapi = new ChuanglanSmsApi();
        $result = $clapi->sendSMS($phone, '【快阅读】您好,您的域名ID为：'.$dname.'的域名检测到有问题，请及时查看!');
        $result = $clapi->execResult($result);
	}
	
	//发送通知验证码
	public function Fasong2($phone,$dname){
        $clapi = new ChuanglanSmsApi();
        $result = $clapi->sendSMS($phone, '【快阅读】您好,您的域名ID为：'.$dname.'的域名检测到掉备案了，请及时替换!');
        $result = $clapi->execResult($result);
	}
	
	//发送通知验证码
	public function FasongVip($phone,$type){
		die;
		$msg[1] = '【快阅读】内容升级，爆料，奇闻，大事件还有精选短视频，打开app，1元现金秒到帐，火速参与：http://t.cn/ReyUtbf 退订T';
		$msg[2] = '【快阅读】升级啦，提现无审核秒到帐，阅读奖励翻倍涨，系统赠送5元现金，24小时有效，快来提http://t.cn/ReyUtbf 退订T';
		$msg[3] = '【快阅读】用户大回馈，您的账户中入账1元现金，快去查看，活动期间提现无审核秒到帐，去收钱 http://t.cn/ReyUtbf 退订T';
		$msg[4] = '【快阅读】您还有1元现金未领走，现在打开app，只需5秒即可到账，提现无需审核，赶快提钱：http://t.cn/ReyUtbf 退订T';
        $clapi = new ChuanglanSmsApi();
        $result = $clapi->sendSMSVip($phone,$msg[$type]);
        $result = $clapi->execResult($result);
	}

}

