<?php
namespace app\hapi\controller;
use think\Db;

class Index
{
    public function index()
    {
    }

    //获取系统配置
    public function getConfigInfo(){
        $platform_type = I('post.platform_type');//平台1安卓2ios
        $channel = I('post.channel');//渠道号
        $app_version = I('post.app_version');//版本号

        //获取系统配置
        $data = $this->getSystemConfig();
        //判断魅族是否显示 1显示 0不显示
        if($data['is_show'] == 0 && $data['is_show_version'] == $app_version){
            if($channel == 8 || $channel == 3){
                $data['is_show'] = 0;
            }else{
                $data['is_show'] = 1;
            }
        }else{
            $data['is_show'] = 1;
        }

        //判断该渠道是否更新 0更新 1不更新
        if($data['channels'] == '0'){
            $data['channels'] = 0;
        }else{
            if(strpos($data['channels'],','.$channel.',') !== false){
                $data['channels'] = 0;
            }else{
                $data['channels'] = 1;
            }
        }

        //1008版本及以上显示悬浮活动入口
        if($data['isopenad'] == 1 && $app_version > 1007){
            $data['isopenad'] = 1;
        }else{
            $data['isopenad'] = 0;
        }
        //1008版本及以上显示我的“活动”入口
        if($app_version < 1008){
            $data['activity_url'] = 'http://webh5.kuaiyuekeji.com/html/game.html';
        }

        $ajaxReturn['code'] = 200;
        $ajaxReturn['msg'] = 'SUCCESS';
        $ajaxReturn['data'] = $data;
        $this->ajaxReturn($ajaxReturn);
    }

}
