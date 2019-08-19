<?php
/**
 * Created by PhpStorm.
 * User: WT
 * Date: 18/5/20
 * Time: 下午12:38
 */
namespace app\mapi\controller;
use think\Db;

//全局配置管理
class AppconfigController extends BaseController{
	//详情
    public function categoryInfo(){
        $token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
        
        $model = M('Config');
        $id = 1;
        $where['id'] = $id;
        $result = $model->where($where)->find();
        $result['channels'] = ltrim(rtrim($result['channels'],','),',');
        if($result){
            $ajaxReturn['code'] = 200;
            $ajaxReturn['msg'] = 'SUCCESS';
            $ajaxReturn['data'] = $result;
        }else{
            $ajaxReturn['code'] = 500;
            $ajaxReturn['msg'] = '失败,请稍后重试!';
        }
        $this->ajaxReturn($ajaxReturn);
    }
	
	//修改
	public function save(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
        $model = M('Config');
        
        $id = 1;
        $ios_app_version = I('post.ios_app_version');//ios版本
        $ios_download_msg = I('post.ios_download_msg');//ios下载描述
        $ios_download_url = I('post.ios_download_url');//ios下载地址
        $ios_isdownload = I('post.ios_isdownload');//ios是否强制下载  1是0否
        
        $android_app_version = I('post.android_app_version');//android版本
        $android_download_msg = I('post.android_download_msg');//android下载描述
        $android_download_url = I('post.android_download_url');//android下载地址
        $android_isdownload = I('post.android_isdownload');//android是否强制下载  1是0否
        
        $is_show = I('post.is_show');//魅族是否显示 1显示0不显示
        $is_show_version = I('post.is_show_version');//魅族版本号
        $channels = I('post.channels');//更新渠道 0全部 否则逗号分隔开
        $share_type = I('post.share_type');//文章视频可分享类型：0不可分享、1朋友圈、2微信、3qq、4qq空间、5微博、6其他
        $adposi = I('post.adposi');//悬浮显示位置 逗号分隔
        $isopenad = I('post.isopenad');//悬浮是否开启1是0否
        $adlitpicurl = I('post.adlitpicurl');//悬浮图地址
        $adurl = I('post.adurl');//悬浮跳转地址
        $activity_url = I('post.activity_url');//我的-活动-跳转地址
        $bottom_litpic = I('post.bottom_litpic');//底部图片
        $bottom_url = I('post.bottom_url');//底部url
        
        $data['ios_app_version'] = $ios_app_version;
        $data['ios_download_msg'] = $ios_download_msg;
        $data['ios_download_url'] = $ios_download_url;
        $data['ios_isdownload'] = $ios_isdownload;
        
        $data['android_app_version'] = $android_app_version;
        $data['android_download_msg'] = $android_download_msg;
        $data['android_download_url'] = $android_download_url;
        $data['android_isdownload'] = $android_isdownload;
        
        $data['bottom_litpic'] = $bottom_litpic;
        $data['bottom_url'] = $bottom_url;

        $data['is_show'] = $is_show;
        $data['is_show_version'] = $is_show_version;
        if($channels != 0){
        		$data['channels'] = ','.$channels.',';
        }else{
        		$data['channels'] = $channels;
        }
        $data['share_type'] = $share_type;
        $data['adposi'] = $adposi;
        $data['isopenad'] = $isopenad;
        $data['adlitpicurl'] = $adlitpicurl;
        $data['adurl'] = $adurl;
        $data['activity_url'] = $activity_url;
        $data['update_time'] = time();
        $where['id'] = $id;
        $result = $model->where($where)->save($data);
        if($result){
        		$this->redis->set('WT_AppgetSystemConfig',null);
            $ajaxReturn['code'] = 200;
            $ajaxReturn['msg'] = 'SUCCESS';
        }else{
            $ajaxReturn['code'] = 500;
            $ajaxReturn['msg'] = '失败,请稍后重试!';
        }
        $this->ajaxReturn($ajaxReturn);
	}
    
    //老铁广告接口
	public function SaveAdInfo(){
		$Advertisement = M('Advertisement');
		$str = '';
		$type = I('post.type');
		$mes = I('post.mes','','');
		$mes = json_decode($mes,true);
		if($type == 'artAll'){//批量
			$c = count($mes);
			for($i=0;$i<$c;$i++){
				if($mes[$i]['pid']){
					$where['id'] = $mes[$i]['pid'];
					$data['title'] = $mes[$i]['title'];
					$data['litpic1'] = $mes[$i]['pic'];
//					$data['shareprice'] = $mes[$i]['readprice']/10;
					$data['ad_url'] = $mes[$i]['advurl'];
					if($mes[$i]['status'] == 2){$sta = 1;}else{$sta = 0;}
					$data['status'] = $sta;
					$data['rank'] = $mes[$i]['ordernum'];
					$data['update_time'] = time();
					$Advertisement->where($where)->save($data);
					$str .= $mes[$i]['pid'].'-'.$mes[$i]['dispid'].',';
					//清除单一硬广
					$this->redis->set('WT_AppADDetailInfo'.$mes[$i]['pid'],null);
				}else{
					$data1['name'] = '老铁硬广';
					$data1['aid'] = 63;
					$data1['pid'] = 2;
					$data1['cid'] = 2;
					$data1['title'] = $mes[$i]['title'];
					$data1['litpic1'] = $mes[$i]['pic'];
					$data1['price'] = 30;
					$data1['money'] = 30;
//					$data1['shareprice'] = $mes[$i]['readprice']/10;
					$data1['shareprice'] = 12;
					$data1['total_money'] = 10000000;
					$data1['surplus_money'] = 10000000;
					$data1['down_money'] = 1000;
					$data1['ad_url'] = $mes[$i]['advurl'];
					$data1['ad_code'] = 1;//设置为系统检测
					$data1['ad_type'] = 2;
					$data1['visitnum'] = rand(10000,50000);
					if($mes[$i]['status'] == 2){$sta = 1;}else{$sta = 0;}
					$data1['status'] = $sta;
					$data1['rank'] = $mes[$i]['ordernum'];
					$data1['sid'] = $mes[$i]['dispid'];
					$data1['share_type'] = '1,2';
					$data1['create_time'] = time();
					$data1['update_time'] = $data1['create_time'];
					if($data1){
						$last_id = $Advertisement->add($data1);
						$str .= $last_id.'-'.$mes[$i]['dispid'].',';
					}
				}
			}
		}elseif($type == 'art'){//单个
			if($mes['pid']){
				$where['id'] = $mes['pid'];
				$data['title'] = $mes['title'];
				$data['litpic1'] = $mes['pic'];
//				$data['shareprice'] = $mes['readprice']/10;
				$data['ad_url'] = $mes['advurl'];
				if($mes['status'] == 2){$sta = 1;}else{$sta = 0;}
				$data['status'] = $sta;
				$data['rank'] = $mes['ordernum'];
				$data['update_time'] = time();
				$Advertisement->where($where)->save($data);
				$str .= $mes['pid'].'-'.$mes['dispid'].',';
				//清除单一硬广
				$this->redis->set('WT_AppADDetailInfo'.$mes['pid'],null);
			}else{
				$data1['name'] = '老铁硬广';
				$data1['aid'] = 63;
				$data1['pid'] = 2;
				$data1['cid'] = 2;
				$data1['title'] = $mes['title'];
				$data1['litpic1'] = $mes['pic'];
				$data1['price'] = 30;
				$data1['money'] = 30;
//				$data1['shareprice'] = $mes['readprice']/10;
				$data1['shareprice'] = 12;
				$data1['total_money'] = 10000000;
				$data1['surplus_money'] = 10000000;
				$data1['down_money'] = 1000;
				$data1['ad_url'] = $mes['advurl'];
				$data1['ad_code'] = 1;//设置为系统检测
				$data1['ad_type'] = 2;
				$data1['visitnum'] = rand(10000,50000);
				if($mes['status'] == 2){$sta = 1;}else{$sta = 0;}
				$data1['status'] = $sta;
				$data1['rank'] = $mes['ordernum'];
				$data1['sid'] = $mes['dispid'];
				$data1['share_type'] = '1,2';
				$data1['create_time'] = time();
				$data1['update_time'] = $data1['create_time'];
				if($data1){
					$last_id = $Advertisement->add($data1);
				}
				
				$str .= $last_id.'-'.$mes['dispid'].',';
			}
		}else{//参数有误
			$ajaxReturn['success'] = '0';
			$ajaxReturn['reason'] = '参数有误，你调个毛啊！';
			$this->ajaxReturn($ajaxReturn);
		}
		
		//清除列表
		$this->redis->set('WT_AppADInfoLists2,21,16,17,15,34in1',null);
		$this->redis->set('WT_AppADInfoLists2,21,16,17,15,34in0',null);
		
		$this->redis->set('WT_AppADInfoLists2,21,16,17,25,23,24,15,34in1',null);
		$this->redis->set('WT_AppADInfoLists2,21,16,17,25,23,24,15,34in0',null);

		$ajaxReturn['success'] = '1';
		$ajaxReturn['media'] = 'MT-KYD';
		$ajaxReturn['mes'] = rtrim($str,',');
		$this->ajaxReturn($ajaxReturn);
   }

}
