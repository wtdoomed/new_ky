<?php
namespace app\mapi\controller;
use think\Db;
use JPush\Client as JPush;

//系统推送设置 
class PushsaveController extends BaseController {
	
	public function iospushbyid(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$tid = I('post.tid');//文章id
		if(!$tid){
			$ajaxReturn['code'] = 500;
			$ajaxReturn['msg'] = '缺少参数！';
			$this->ajaxReturn($ajaxReturn);
		}
		//获取文章信息
		$where['id'] = $tid;
		$res = M('Article')->field('cid,title,type')->where($where)->find();
		if(!$res){
			$ajaxReturn['code'] = 500;
			$ajaxReturn['msg'] = '文章错误！';
			$this->ajaxReturn($ajaxReturn);
		}
		
		//收徒狂欢，额外奖励666元现金！
		//不多说了，就是这么壕，送你666元现金！
		//抽奖赚现金了！
		//【抽奖+50元】，每天只要分享1篇文章，即可获得一次抽取现金奖励机会，100%中奖！
		//【收益提醒】转发文章赚钱快！
		//你刚才分享到微信的文章/视频，域名已被微信封停，请再次分享一下，就可以继续赚零钱了！
		//涨钱啦! 转发文章单价提升到1.2毛了!
		//进钱速度1天抵5天！赶紧去转发文章吧，不要错过哦！
		$a = $res['title'];
		$b = '爆款热文，分享到朋友圈，好友每阅读奖励你0.1元，超赚钱！';
        $app_key = '70f806cece152fbf657e155e';
        $master_secret = 'd5e0c2dcfc8a3ad51c4db50c';
		
//		$notification = [
//			"notification" => [
//		        "android" => [
//		            "alert" => "Hi, JPush!",
//		            "title" => "这是一个推送！",
//		            "builder_id" => 1,
//		            "extras" => [
//		                "newsid" => 321
//		            ]
//		        ],
//		    		"ios"=>[
//		            "alert"=>"Hi, JPush!",
//		            "sound"=>"default",
//		            "badge"=>"+1",
//		            "extras"=>[
//		                "newsid"=>321
//		            ]
//		        ]
//		 	]
//		];
//		
  		$extras['p_type'] = 2;//1、H5  2、某一个详情 3、跳版本  4、跳收徒 5、跳任务 6、跳广告
  		$extras['p_title'] = $a;//标题（p_type=1时用）
  		$extras['p_url'] = 'http://webh5.kuaiyuekeji.com/html/game1.html';//H5地址（p_type=1时用）
		$extras['p_id'] = $tid;//文章/视频id（p_type=2时用）
		$extras['p_cid'] = $res['cid'];//文章的类别id（p_type=2时用）
		$extras['p_atype'] = $res['type'];//1文章2视频（p_type=2时用）
		$extras['p_video_url'] = 'http://video.yidianzixun.com/video/get-url?key=user_upload/15245638417091bcde9b51bd6a12b10433c66aadeddde.mp4';//视频地址（p_atype=2时用）
		$extras['p_litpic1'] = 'http://i1.go2yd.com/image.php?url=V_01ZxIIFnyu';//视频第一针地址（p_atype=2时用）
		$adinfo['id'] = 128;//（p_type=6时用）
		$adinfo['title'] = '千人在线  精彩刺激  绝对公平 千万金币等你来拿!';//（p_type=6时用）
		$adinfo['litpic1'] = 'http://imglf4.nosdn.127.net/img/WW9DVmdTd2ZSODYzWHB3ZUF5RmVCZnM0S0wrTENIWVZwaDYzVlpucWE3SGFwazN1UWRYeHRRPT0.jpg?imageView&amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;thumbnail=500x0&amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;quality=96&amp;amp;amp;amp;am';//（p_type=6时用）
		$adinfo['ad_url'] = 'https://mob.0792gdst.com/054/';//（p_type=6时用）
		$adinfo['share_type'] = '1,2,3,4,5,6';//（p_type=6时用）
		$extras['p_adinfo'] = $adinfo;//（p_type=6时用）
		$extras['p_push_title'] = $a;//（用户打开app时，站内弹出使用）
		$extras['p_push_content'] = $b;//（用户打开app时，站内弹出使用）
  		
		$notification['title'] = $a;
  		$notification['builder_id'] = 1;
  		$notification['extras'] = $extras;
		
        $client = new JPush($app_key, $master_secret);
		$arr = $client->push()
		    ->setPlatform('all')
//		    ->addAlias('qqwwee')//单个设备
		    ->addAllAudience()//全部设备
//		    ->iosNotification($notification)//IOS发送
		    ->androidNotification($b,$notification)//安卓发送
		    //->message('快阅读推送信息！')
		    ->send();
		    
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$this->ajaxReturn($ajaxReturn);
   	}
   	
   	public function iospushbyid2(){
		$tid = I('post.tid',208859);//文章id
		if(!$tid){
			$ajaxReturn['code'] = 500;
			$ajaxReturn['msg'] = '缺少参数！';
			$this->ajaxReturn($ajaxReturn);
		}
		//获取文章信息
		$where['id'] = $tid;
		$res = M('Article')->field('cid,title,type')->where($where)->find();
		if(!$res){
			$ajaxReturn['code'] = 500;
			$ajaxReturn['msg'] = '文章错误！';
			$this->ajaxReturn($ajaxReturn);
		}
		//收徒狂欢，额外奖励666元现金！
		//不多说了，就是这么壕，送你666元现金！
		//抽奖赚现金了！
		//【抽奖+50元】，每天只要分享1篇文章，即可获得一次抽取现金奖励机会，100%中奖！
		//【收益提醒】转发文章赚钱快！
		//你刚才分享到微信的文章/视频，域名已被微信封停，请再次分享一下，就可以继续赚零钱了！
		//涨钱啦! 转发文章单价提升到1.2毛了!
		//进钱速度1天抵5天！赶紧去转发文章吧，不要错过哦！
		$a = '紧急通知！紧急通知！';
		$b = '因微信今天严查，本平台白天暂停，晚上恢复！';
        $app_key = '70f806cece152fbf657e155e';
        $master_secret = 'd5e0c2dcfc8a3ad51c4db50c';
		
//		$notification = [
//			"notification" => [
//		        "android" => [
//		            "alert" => "Hi, JPush!",
//		            "title" => "这是一个推送！",
//		            "builder_id" => 1,
//		            "extras" => [
//		                "newsid" => 321
//		            ]
//		        ],
//		    		"ios"=>[
//		            "alert"=>"Hi, JPush!",
//		            "sound"=>"default",
//		            "badge"=>"+1",
//		            "extras"=>[
//		                "newsid"=>321
//		            ]
//		        ]
//		 	]
//		];
//		
  		$extras['p_type'] = 2;//1、H5  2、某一个详情 3、跳版本  4、跳收徒 5、跳任务 6、跳广告
  		$extras['p_title'] = $a;//标题（p_type=1时用）
  		$extras['p_url'] = 'http://webh5.kuaiyuekeji.com/html/concern.html';//H5地址（p_type=1时用）
		$extras['p_id'] = $tid;//文章/视频id（p_type=2时用）
		$extras['p_cid'] = $res['cid'];//文章的类别id（p_type=2时用）
		$extras['p_atype'] = $res['type'];//1文章2视频（p_type=2时用）
		$extras['p_video_url'] = 'http://video.yidianzixun.com/video/get-url?key=user_upload/15245638417091bcde9b51bd6a12b10433c66aadeddde.mp4';//视频地址（p_atype=2时用）
		$extras['p_litpic1'] = 'http://i1.go2yd.com/image.php?url=V_01ZxIIFnyu';//视频第一针地址（p_atype=2时用）
		$adinfo['id'] = 128;//（p_type=6时用）
		$adinfo['title'] = '千人在线  精彩刺激  绝对公平 千万金币等你来拿!';//（p_type=6时用）
		$adinfo['litpic1'] = 'http://imglf4.nosdn.127.net/img/WW9DVmdTd2ZSODYzWHB3ZUF5RmVCZnM0S0wrTENIWVZwaDYzVlpucWE3SGFwazN1UWRYeHRRPT0.jpg?imageView&amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;thumbnail=500x0&amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;quality=96&amp;amp;amp;amp;am';//（p_type=6时用）
		$adinfo['ad_url'] = 'https://mob.0792gdst.com/054/';//（p_type=6时用）
		$adinfo['share_type'] = '1,2,3,4,5,6';//（p_type=6时用）
		$extras['p_adinfo'] = $adinfo;//（p_type=6时用）
		$extras['p_push_title'] = $a;//（用户打开app时，站内弹出使用）
		$extras['p_push_content'] = $b;//（用户打开app时，站内弹出使用）
  		
		$notification['title'] = $a;
  		$notification['builder_id'] = 1;
  		$notification['extras'] = $extras;
		
        $client = new JPush($app_key, $master_secret);
		$arr = $client->push()
		    ->setPlatform('all')
//		    ->addAlias('qqwwee')//单个设备
		    ->addAllAudience()//全部设备
//		    ->iosNotification($notification)//IOS发送
		    ->androidNotification($b,$notification)//安卓发送
		    //->message('快阅读推送信息！')
		    ->send();
		    
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$this->ajaxReturn($ajaxReturn);
   	}
   
}

