<?php
namespace app\mapi\controller;
use think\Db;

//文章视频采集
class CollectionController extends BaseController {
	
	//文章（小豆看点，每5、7、11、15、19小时）
	public function getArticleRecommend(){
		set_time_limit(0);
		$pattern_style ='<.*?style="(.*?)">';
		$url = 'http://dz.zhuan12.com/dz/minfo/call.action';
		$arr['opttype'] = 'ART_LIST';
		
		$times = time();
		$h = date('H',$times);
		if($h == 5 || $h == 7 || $h == 11 || $h == 15 || $h == 19){
			$Article = M('Article');
			//获取文章类别
			$classdata = $this->axdclass();
			$c1 = count($classdata);
			for($j=0;$j<$c1;$j++){
				$arr['jdata'] = '{"app_id":"xzwl","app_token":"xzwltoken070704","channel":"VIVO01_CHANNEL","os":"android","pars":{"art_type":"'.$classdata[$j]['type'].'","before_hour":"1535169948","hot_top":"0","openid":"oH4k8v_3ZAMk7iS6dK47_GhcEvcA","orderby":"DESC","page":"2","pagesize":"10","start_id":"3351863","touch_action":"down","video_type":"-1"},"vercode":30,"version":"12.3.2"}';
				$res = $this->postData($url,$arr,true);
				$res = json_decode($res,true);
	
				$data = $res['datas'];
				$c = count($data);
				for($i=0;$i<$c;$i++){
					//判断是否是广告
					if($data[$i]['art_ad'] == 1){
						continue;
					}
					if(file_get_contents($data[$i]['art_pic'])){
					}else{
						//图片不能打开
						continue;
					}
					$url2 = 'http://dz.zhuan12.com/dz/minfo/call.action?opttype=INF_ART&jdata={"app_id":"xzwl","app_token":"xzwltoken070704","pars":{"article_id":"'.$data[$i]['art_id'].'","app_in":0,"openid":"oH4k8v_3ZAMk7iS6dK47_GhcEvcA"}}';
					//获取页面内容
					$desc = json_decode(file_get_contents($url2),true);
					$desc = $desc['datas']['article']['content'];
					if(strpos($desc,'nj.yunyiwd.cn') !==false){
					 	//包含广告
						continue;
					}
					preg_match_all($pattern_style,$desc,$matches_style);//取出所有style的内容
					$style_c = count($matches_style[1]);
					for($k=0;$k<$style_c;$k++){
						$desc = str_replace($matches_style[1][$k],'',$desc);//循环替换为空
					}
					
					$list[$i]['title'] = $data[$i]['art_title'];
			    		$list[$i]['cid'] = $classdata[$j]['cid'];
		    			$list[$i]['pic_type'] = 2;
		    			$list[$i]['litpic1'] = $data[$i]['art_pic'];
		    			$list[$i]['litpic2'] = '';
		    			$list[$i]['litpic3'] = '';
			    		$list[$i]['desc'] = $desc;
			    		$list[$i]['price'] = 10;
			    		$list[$i]['status'] = 1;
			    		$list[$i]['type'] = 1;
			    		$list[$i]['visitnum'] = rand(7000,50000);
			    		$list[$i]['sharenum'] = rand(7000,50000);
			    		$list[$i]['publish_time'] = time()+rand(1,3000);
			    		$list[$i]['create_time'] = $times;
				}
				$Article->addAll(array_values($list));
				$list = array();
			}
			//清除重复标题
			$this->qinglichongfu(1);
			//更新文章缓存
			A("Timedtask")->SetArticleListCache();
			A("Timedtask")->SetArticleListCache1();
		}
	}
	
	//抓取视频的推荐，5-22点之间每小时10条（趣头条，每小时第5分钟）
	public function getVideoRecommend(){
		set_time_limit(0);
		$times = strtotime(date('Y-m-d H:i',time()));
		$h = date('H',$times);
		if($h > 4 && $h < 23){
			$url = 'http://api.1sapp.com/content/getListV2?qdata=QTQ2RTRFMDVFMDU0ODgzNERGNjAzM0NBODkxQTVBNTcuY0dGeVlXMGZOalV6WkRVM1pUSXRaR1prTmkwME1HWmhMV0poWVRFdE5tVTVOMlU0TWpWbU1tRmhIblpsY25OcGIyNGZNaDV3YkdGMFptOXliUjloYm1SeWIybGsu8a9NIh%2FfQwL%2FbYsDzS8wOWpCpFA9tZDQI%2BgTeoEgvVHhAVeTpaYLR7Lvm5uKdHmPTtCNc7WzdB2Uq7lSUpE%2BkCD%2F2%2B7Yy%2BxKOU9QxKvG4L4tSkN64IXVYeNjoqvJd0%2BRqCufpzMYHALj1jiGI437Z8vpdRlzqGovMWtvaUmxhzZMgXqg25ACMQLsjj3KiRhfgUU29aALqPi54LZ9P9a6AxlhjB2Pdpyu3O%2F6hDV8CuGron6SR3KNOnx2mkdoprPILMw%2BsItZPmVhqLIp%2FfNhpoBBf3EuibOi25XtQDXwo3VWrGdYn9sH%2BU%2FyUJW53fz7PdhEMfjNRBl1DcIGFnj20W3KExKlw6ooo4nkQ1Aw0tUOsw0cWh0TH8MT7a0M3i2slsExXFQN%2F%2BETXyycUNQXNKWSM%2FHiwZwszld1kfBZmIEjL5lh1TrkLkqfAI1vnBbQPHaYU5BI%2BODPYpZ%2FK87senuAY5MLcOkhyGILF71KB6K4sMEc4Kju0xiRdGzixObCPzW072ohH9ZcS6QQUF5GDHG8GIpyxJGqgYlkCjV1PkdRsplfgYLo0NHpf8IHZS2Wv0Rj%2B2kcbDmShODGlklQaoynk6RhW6r68N6DjDLMnmRwN32g%2FA%2FQkcJ6em9J2qriR3w5pANev64CIAo6lRF4whRiKaYt2DwrMcJCkXAXhwIFipMYqz%2FTeJHx9d1rmPDj5Nogas15tY2hxtnMQWnM%2BsNEXFrCIltONzoe10ea9%2BG9byPd4vfXefO0CHqSyUFKNA%3D%3D';
			$res = file_get_contents($url);
			$res = json_decode($res,true);
			$data = $res['data']['data'];
			$c = count($data);
			for($i=0;$i<$c;$i++){
				if(!$data[$i]['video_info']['hd']['url']){
					continue;
				}else{
					$v_url = $data[$i]['video_info']['hd']['url'];
				}
				$list[$i]['title'] = $data[$i]['title'];
		    		$list[$i]['pic_type'] = 2;
		    		$list[$i]['cid'] = 43;
		    		$list[$i]['litpic1'] = $data[$i]['cover'][0];
		    		$list[$i]['video_url'] = $v_url;
		    		$list[$i]['video_long'] = $data[$i]['play_time'];
		    		$list[$i]['status'] = 1;
		    		$list[$i]['price'] = 8;
		    		$list[$i]['type'] = 2;
		    		$list[$i]['visitnum'] = rand(7000,50000);
		    		$list[$i]['sharenum'] = rand(7000,50000);
		    		$list[$i]['publish_time'] = $times;
		    		$list[$i]['create_time'] = $times;
			}

			$res = M('Article')->addAll(array_values($list));
			//清除重复标题
			$this->qinglichongfu(2);
			
			if($res){
				//更新“视频推荐”缓存内容
				A("Timedtask")->SetVideoListCache1();
			}
		}
	}
	
	//360、抓去每个类别下的文章（每天5、11、19点）
	public function getArticleContent(){
		set_time_limit(0);
		$times = time();
		$h = date('H',$times);
		if($h == 5 || $h == 7 || $h == 11 || $h == 15 || $h == 19){
			$Article = M('Article');
			//获取文章类别
			$classdata = $this->aclass();
			
			$c1 = count($classdata);
			for($j=0;$j<$c1;$j++){
				$url = 'http://api.app.btime.com/news/list?protocol=3&cid='.$classdata[$j]['type'].'&refresh=3&is_paging=0&count=12&pid=1&cname=%E6%8E%A8%E8%8D%90&net_level=1&offset=0&refresh_type=1&lastpdate=&ver=20900&os_ver=26&os=OPR1.170623.027&src=lx_android&push_id=39df9d8e4905c47cdf7fb3f0f59d13c7&channel=sogou&pro=360news&token=d692f5c043777dd4b5369e96e7c210de&sid=&carrier=%E4%B8%AD%E5%9B%BD%E8%81%94%E9%80%9A&browse_mode=1&os_type=Android&net=WIFI&timestamp=1530262279&sign=70c5b32';
				$res = file_get_contents($url);
				$res = json_decode($res,true);
	
				$data = $res['data']['data'];
				$c = count($data);
				for($i=2;$i<$c;$i++){
					$list[$i-2]['title'] = $data[$i]['data']['title'];
			    		$list[$i-2]['cid'] = $classdata[$j]['cid'];
			    		if(count($data[$i]['data']['covers']) > 2){
			    			$list[$i-2]['pic_type'] = 3;
			    			$list[$i-2]['litpic1'] = $data[$i]['data']['covers'][0];
			    			$list[$i-2]['litpic2'] = $data[$i]['data']['covers'][1];
			    			$list[$i-2]['litpic3'] = $data[$i]['data']['covers'][2];
			    		}else{
			    			$list[$i-2]['pic_type'] = 2;
			    			$list[$i-2]['litpic1'] = $data[$i]['data']['covers'][0];
			    			$list[$i-2]['litpic2'] = '';
			    			$list[$i-2]['litpic3'] = '';
			    		}
					
					//获取页面内容
					$arr = $this->GrabContent($data[$i]['url']);
					if($arr){
		    				$desc = str_replace('<div data-seed="102" class="seed-area" style="margin-top:25px;">',"",$arr[0]);
					}else{
						$desc = '';
					}
					
			    		$list[$i-2]['desc'] = $desc;
			    		$list[$i-2]['price'] = 10;
			    		$list[$i-2]['status'] = 1;
			    		$list[$i-2]['type'] = 1;
			    		$list[$i-2]['visitnum'] = rand(7000,50000);
			    		$list[$i-2]['sharenum'] = rand(7000,50000);
			    		$list[$i-2]['publish_time'] = time()+rand(1,3000);
			    		$list[$i-2]['create_time'] = $times;
				}
	
				$Article->addAll($list);
				$list = array();
			}
			
			$where['desc']  = '';
			$where['litpic1']  = '';
			$where['_logic'] = 'or';
			$map['_complex'] = $where;
			$map['type']  = 1;
			$Article->where($map)->delete();
			
			//清除重复标题
			$this->qinglichongfu(1);
			
			//更新文章缓存
			A("Timedtask")->SetArticleListCache();
			A("Timedtask")->SetArticleListCache1();
		}
	}
	
	//趣头条、抓取每个类别下的视频（每天5、11、19点）
	public function getVideoContent(){
		set_time_limit(0);
		$times = time();
		$h = date('H',$times);
		if($h == 5 || $h == 7 || $h == 11 || $h == 15 || $h == 19){
			//获取视频的类别
			$classdata = $this->vclass();
			$c1 = count($classdata);
			for($j=0;$j<$c1;$j++){
				$res = file_get_contents($classdata[$j]['url']);
				$res = json_decode($res,true);
				$data = $res['data']['data'];
				$c = count($data);
				if($c > 10){
					$c = 10;
				}
				for($i=0;$i<$c;$i++){
					if(!$data[$i]['video_info']['hd']['url']){
						continue;
					}else{
						$v_url = $data[$i]['video_info']['hd']['url'];
					}
					$list[$i]['title'] = $data[$i]['title'];
			    		$list[$i]['cid'] = $classdata[$j]['cid'];
			    		$list[$i]['pic_type'] = 2;
			    		$list[$i]['litpic1'] = $data[$i]['cover'][0];
			    		$list[$i]['video_url'] = $v_url;
			    		$list[$i]['video_long'] = $data[$i]['play_time'];
			    		$list[$i]['status'] = 1;
			    		$list[$i]['price'] = 8;
			    		$list[$i]['type'] = 2;
			    		$list[$i]['visitnum'] = rand(7000,50000);
			    		$list[$i]['sharenum'] = rand(7000,50000);
			    		$list[$i]['publish_time'] = $times;
			    		$list[$i]['create_time'] = $times;
				}
				
				M('Article')->addAll(array_values($list));
			}
			
			//清除重复标题
			$this->qinglichongfu(2);
			
			//更新视频缓存
			A("Timedtask")->SetVideoListCache();
		}
	}
	
	//聚看点--视频
	public function zhuqujukandian(){
		set_time_limit(0);
		$url = 'https://www.xiaodouzhuan.cn/jkd/newmobile/artlist.action';
		$times = time();
		$h = date('H',$times);
		if($h == 5 || $h == 7 || $h == 11 || $h == 15 || $h == 19){
			//获取视频的类别
			$classdata = $this->wzvclass();
			$c1 = count($classdata);
			for($j=0;$j<$c1;$j++){
				$arr['jsondata'] = '{"cateid":"'.$classdata[$j]['url'].'","optaction":"up","page":"4","pagesize":"12","searchtext":"","appid":"xzwl","apptoken":"xzwltoken070704","appversion":"5.7.4","channel":"SOUGOU01_CHANNEL","mobileinfo":"vivo","openid":"7c7448a2a570421b97bbae98f5747aa1","os":"android","sdktype":"bd_jssdk;bd_sdk;gdt_sdk;tt_sdk;sg_sdk;gdt_api;tt_api;zk_api","token":"Itb3pYO6AW6jsPv9G%252B%252B%252FbTDwtVz1XHQniXzsPnANjMuPWi3wOzj4r5jEHzD0cYCr%250A"}';
				
				$res = $res = $this->postData($url,$arr,true);
				$res = json_decode($res,true);
				$data = $res['artlist'];
				$c = count($data);
				for($i=0;$i<$c;$i++){
					if(!$data[$i]['art_id']){
						continue;
					}
					if(file_get_contents($data[$i]['video_url'])){
					}else{
						//视频不能打开
						continue;
					}
					$litpic = json_decode($data[$i]['art_title_pic']);
					if(file_get_contents($litpic[0])){
					}else{
						//视频不能打开
						continue;
					}

					$list[$i]['title'] = $data[$i]['art_title'];
			    		$list[$i]['cid'] = $classdata[$j]['cid'];
			    		$list[$i]['pic_type'] = 2;
			    		$list[$i]['litpic1'] = $litpic[0];
			    		$list[$i]['video_url'] = $data[$i]['video_url'];
			    		$list[$i]['video_long'] = date('i:s',$data[$i]['durtion']);
			    		$list[$i]['status'] = 1;
			    		$list[$i]['price'] = 8;
			    		$list[$i]['type'] = 2;
			    		$list[$i]['visitnum'] = rand(7000,50000);
			    		$list[$i]['sharenum'] = rand(7000,50000);
			    		$list[$i]['publish_time'] = $times;
			    		$list[$i]['create_time'] = $times;
				}
				M('Article')->addAll(array_values($list));
			}
			
			//清除重复标题
			$this->qinglichongfu(2);
			
			//更新视频缓存
			A("Timedtask")->SetVideoListCache();
		}
	}
	
	//抓去文章内容
	protected function GrabContent($url=''){
		//抓取页面数据
    		$data = file_get_contents($url);
    		//内容正则
    		$con_preg = '/<div class="content-text" id="content-text">[\s\S]+?<div data-seed="102" class="seed-area" style="margin-top:25px;">/';
    		//得到返回的数组数据
    		preg_match($con_preg,$data,$arr);

    		return $arr;
	}
	
	//聚看点--视频
	public function wzvclass(){
		$classdata[0]['name'] = '搞笑';//站内类别名称 -- 农趣
		$classdata[0]['cid'] = 27;//站内类别id
		$classdata[0]['url'] = 54;//抓取url
		
		$classdata[1]['name'] = '健康';//站内类别名称 -- 广场舞
		$classdata[1]['cid'] = 38;//站内类别id
		$classdata[1]['url'] = 60;//抓取url
		//科技
		$classdata[2]['name'] = '情感';//站内类别名称
		$classdata[2]['cid'] = 36;//站内类别id
		$classdata[2]['url'] = 56;//抓取url
		//萌宠
		$classdata[3]['name'] = '教育';//站内类别名称
		$classdata[3]['cid'] = 30;//站内类别id
		$classdata[3]['url'] = 63;//抓取url
		
		$classdata[4]['name'] = '游戏';//站内类别名称
		$classdata[4]['cid'] = 40;//站内类别id
		$classdata[4]['url'] = 69;//抓取url
		
		return $classdata;
	}
	
	//趣头条--视频
	protected function vclass(){
		$classdata[0]['name'] = '音乐';//站内类别名称
		$classdata[0]['cid'] = 33;//站内类别id
		$classdata[0]['url'] = 'http://api.1sapp.com/content/getListV2?qdata=OTgyQzkyMkI4MzlFNTM3QTkzQzE3ODFBRTUwMDI2QzguY0dGeVlXMGZaVEk0WWpKaE1HRXRZV1kxTlMwME0yRTBMVGswTkRFdFlXWTJNekkwT0RZME1USTJIblpsY25OcGIyNGZNaDV3YkdGMFptOXliUjloYm1SeWIybGsuliydQ%2FngwSBGxAliFjFaNXNFEVKhAotLaBu2UpHQ4NP0leFe%2BWIJuBG5yTudQnLE6iuxlK3djyz2uyaIUwvmewNklnT5PoxwtM6TiZUEw%2FpCELxs8KhNzv%2BuMf7kcfZyIBeQxWnO1z1%2FjWt9P7bfsd2C%2BH4e7V3ujUODZZO8MXZgFHP9sZ8gu6y%2BEsyIG6UO4Fio%2Bdo6AKTVwNY7jicgWqWa%2BTtR%2Fvo3lUL2%2FI%2BXGzDv4TYorPRp%2BebIOrONvEF3bTfdxx34%2FBVA4HnW6z9fq%2FuRONz%2FO2FfY%2BykcwU5S96aVHt7FFa46Ajr6Bap8L20KYQvkNOwEVXrP3ifc8y82CB5ooGaqUu%2BlAzu6hL6plRNnMMq8QMwlsf%2BdOpWg1hEB1Uq5%2B%2Fph0yUvR6MmSy357oZpV%2Bxcr%2BtWHvo3%2F%2BCouinUCP2PprQ%2BMYyW4drpCiz1nWv2IIT0BK2Zzk0Lk3Q1EnmgqrfoSFlJKweL3J2LDMF7ACTyeqNNUyu75yIutQYRi2wqr8YLgrjmOsoJjCAbRR3xoeCByJzqj0%2F9Z7pQjzhqWchQlor6J5%2FEoSAs1Phai8iLaWuch8RQfzGxLXTNGLPFwT6vCzE4aeKBdCKGnCr%2FZezQPGaDG%2FhAw%2B%2FWWD7AvIXRwDmhX4o7U%2F%2FFSFvMs3hE9DBGfBtRfzAMPutGaw6H%2B7tCoo1NQXGsmm4bSn2Z2CNVtB7JeXG5c57rLWzDyoZk5LqcB20ExkPHrs%2BqJl2';//抓取url
		
		$classdata[1]['name'] = '娱乐';//站内类别名称
		$classdata[1]['cid'] = 28;//站内类别id
		$classdata[1]['url'] = 'http://api.1sapp.com/content/getListV2?qdata=RDFGMzczRTZGRkM4QkQyRUI5Qzg3QTc3QTlDMzFDMDguY0dGeVlXMGZNVGRrWkRJd01tUXRZekE0WmkwMFpUbGtMVGswWVdRdE16VXpOVEptT0RkbFpEUXhIblpsY25OcGIyNGZNaDV3YkdGMFptOXliUjloYm1SeWIybGsuOacdyq1edVfblMqrFCzl0iWnEHaXCq3m82gKlnyWcSp%2F2WsSSucBXahX8oNFh36HecchETwbnG%2BkA4iZqGew63JSsA7OX7HeT%2BSRSlGzpbeKEHOCXUilgCaMAa0oC1UqkwTNgFFkU1hE5Jz394RUVw722WF65MSp%2FFnF3SAA0JNnFrwHwEtAxyOfBISCcwzWO6uVBGcucK%2F0l%2BGsrfdyDq1HsmB9ub0Lix3r%2FodQltTge3Qki5mTjk5wME1s%2FVBh8VzJwBAyabXyWxKj%2FmxlgteuhiiX%2FOcZHcv%2F%2FNm8z8B%2FWLE0j96y6uduwkTE5U4Dg0qpBJIbSDG%2B%2B2e5eocPCxXt8OfXSyTH1BwbXxfIDI1b0Y8EZSqOjI9FIunb0pR535mciSzUYOTDYpZpPHsYDDuyHG6k1DbYMKZvMstxC5fiRvdqd9p%2FByslaXYDfo%2FZoti1mmSnxzmbH8NysDL7Ro93CaDtSXH4PwtBCBIs0yisYgqfHD%2FZ3lzACwvWACt7LSgQ8j5LdnAeecS7tIpwhxpr3%2B4gjEH0j%2FFMzbGib64oK6jhjRRbyjdNXU6NcX5bRPDcaBEZ7SXMN2pfLl64oARWCGIiCrPopARUGKHrzbuldkSiFPZpIJzhsNLnh5ZU2n%2FjTBjvBknmp9Fcj9KzTjRQqxqsocK6Hc4E9WLeCnZjQtQHm22i3HJXgLf41g7RWrmHHAUA3kDpmO2i%2F5bsK6P12rvYIBMzjyp6ORosBgt2';//抓取url
		
		$classdata[2]['name'] = '社会';//站内类别名称 -- 奇闻
		$classdata[2]['cid'] = 41;//站内类别id
		$classdata[2]['url'] = 'http://api.1sapp.com/content/getListV2?qdata=MUU2QUQwOUE0MjJDQTU4QjM2QzhEOEM4MEVCNjE1NUYuY0dGeVlXMGZNMkZpTm1ZM05qSXROV013WVMwME5qZG1MVGcwWlRndFlUZzNNelJrTWpka05EZ3hIblpsY25OcGIyNGZNaDV3YkdGMFptOXliUjloYm1SeWIybGsuvkCdAf0DD1XMpxow7hvCmC40Fzxc7mqJak5DS7RBnsokg4hQv0CwF1xfdFdjuuSuzZR7Rc1Hc6R090iN1Oe6PwJOomkHTWfl96faHtCNxZquvzlx6uAJRxQ9xB1HlFtR8TbVRWkHZh115r3%2BFajakxcOqEv0EPJx1o9hUOPDSfn2FhHawP9RvRJfZpxl188jwkaIh%2BE76zfmi4COLFvZPMpHt2UWoejKwp0Bs8tvTlhaouUQNKwP4L7em1ruyEW3HX19yLyCcqlywHGt3pIxThLD%2BD7xgZduYYE83pXN%2F9Al89QR2Lywa0uLsoLIw6MypiUNPbRIgawhIhRQWeYZey8kWamXzHqJzjWSH9BOvNsFGFEgZ%2BpMf%2FZ%2BX%2Ftj4gp36UEhy4cu8EKzjCk2SeinKLgjBgVVNE%2FOiXpthr4L6zWGv9RyirD3tzdjk58eUIWx%2BlSBopeTzfzpOPI%2BmnvFzBQk4PvYp6xRyR9fnuSH0GUdQB%2BwV2P1LTkq0hi2ygeurKqlEPjQiCpW54Hd%2FYEj8SONPZVO%2BZ54SXhvNnjsstWpVFxV2eJltGnBCw7P4P%2FqTo2QqfTLE%2BTBjrAoJmdCm1VnCe4k%2BDe40aF1sXqAFHlkR8q90RVE1sxmFFiIDp6zwVGgUPtzTk6GieCWYFZn56GPIo32PxZFgpCdK3wuvrQ0gAWwK4p5VgaV%2Fuguz%2B%2BmIzhcxo3ATRawzGohr%2FszhK0XVeNgC%2BWk04zovucH8WMrsd2TLaPEco%2FsDnXpbQpcdQ%3D%3D';//抓取url

		$classdata[3]['name'] = '影视';//站内类别名称
		$classdata[3]['cid'] = 29;//站内类别id
		$classdata[3]['url'] = 'http://api.1sapp.com/content/getListV2?qdata=OUJBQ0I4MTM3NDVCQjMyN0RFQkI5MTgxM0EyMjhFMUYuY0dGeVlXMGZabVJpTkdGak5XUXRPREEzTXkwME16VTVMVGc1WWpNdE16Y3pNMlEyTWpabVlqa3lIblpsY25OcGIyNGZNaDV3YkdGMFptOXliUjloYm1SeWIybGsugpCBwwj4v9r5DgdFD457ZzVQfAxBnEfO8OeNyUAbE5kxYIqXB%2FSYcvrBHpLd%2BYGVeQ5R8%2FOkLQW9QrFNEisg6f0nl%2FRGfk2NT%2BS%2FGzJKOT9Au5wmZ7xg1ZuPkxe8Neyh0tF7Db60A%2F9hX1Z351absV5jfTLBI%2F34ZozIzL%2B30nsyRyuQLOe5Jql4hxgcL50d6jCplyAS3Oah%2B28S4gBEjyA0nVHy98xtMTuqCjTQ1tlZh6oIFMsiL5TIBZQkJJa98aMZJc1QPeb2mE3evQlDWFmLMXmddWkCHgfTMbiF%2FZTJQmQOUiJpuMGRuqL7kGZawPhqwA7KwtPw68plF9pz845U1nLgx%2B28j4708Yuu0o1A%2F1O3CgV64iYV4zPLbFizOnohNh%2BnJ3QMLKHDGm3joTOcBWus4AnUXKao7R6HSkWJYIJq7ojLGRFG2xAShkEUKvIPYGvGl28sxitKFi32WSGA2YGQ5PjVepKxDbOAGnMbE64hPNdJBnRvFiBG85BSQJA%2BCTom3IQPtjB1Y7aKpDsbCO%2B99CYFygUkd1ESJ4Ru6m3a2k%2BhWJT%2FdGELZGaK9868lh5GIeZ%2FGujN5YAgZCDxNzpoBIa6CbEwBlx3nt5r5eIBcFd9jT0Tkwr0HJPYw9xKKptHQOmPfxPo55kxaQyPDkAKT70WRXZMvTmwRdmnN2RAucMfuNo5jxMu7a%2FMn0qqz15VwpuYDvXMlbUw7zQRNKsbuBu6iIS%2BrYj4amU7';//抓取url

		$classdata[4]['name'] = '生活';//站内类别名称
		$classdata[4]['cid'] = 35;//站内类别id
		$classdata[4]['url'] = 'http://api.1sapp.com/content/getListV2?qdata=OURGN0I4NTNFRkU4MzJFNDZCQ0VGQTczRUQ0Mjk5N0MuY0dGeVlXMGZOakZtWkRGa1lUQXRaR1ppWmkwMFlUYzBMV0poWkdRdFlUUmlaR1F5TkRaaU5XSTBIblpsY25OcGIyNGZNaDV3YkdGMFptOXliUjloYm1SeWIybGsujN%2BovYVb4buWkyjol1%2F8oqFKG0mAgwQp4Jtzo8%2B7t%2FOPG8PRdU1ibZAtTQpoFnxFoNckjqph8oJkHyIww93MJKKkbXc2HLvbexQB6Z2xLDZYAi1Q5Yc9kxz%2BJa54w45IOf6ZCOHEptVMeFlVnuOtUfRWdsMd9L2bXCvYwg8gq6DQyBjS8C3KK27wphmkSMeSx8Unn%2B6ov%2FgL7B7TdzDLORPvQmQLgoqm9mqwsXt9rT0yF51n0t6eBsdCMqvNy%2FHV8rJlQYnxJnDFJ7wCAAgyhw6JyhCCRRHfM4OTRXwIcK3Hmay47E1Jt1EDkLLiCmWRzJwi3A78l8NbcWnn09wRazpRePGov1gebWTKcaMaKdqK%2FxhtUI9KV4Al1ePtj5RTxuSG8uXKiXyVcxZGiOLVDKQkDMpKRvGZLhsLYqJVGzVsH6N4kryinA7Y2Cqa%2BNYZ2lrysSqBmFJ5I7On2PYm9fs7bHQO33JDInpLkhrgcOUBKULdJJJTEyjSWWgZCqyQGKRJZpVSo6niAnACiYnstiqrUG1apnlH9nFPKBOiCKY1Wt07HjVYkvsW6bk4a3s8csqneIeKolws%2FMwk4Jxcp4JnxBQ6C%2FqWWcFWoXT9iFe2YkNQUb%2FozJg%2B9RDDafJJFHfPm%2BsXm%2FOAWq7%2F6AOzFTCRq9zt03xStigmRotV4L2oYdzCVRfeI8Xa%2FkRn8wyMP60ltcwxssiwHiz0uCmzpk1H6L1y68X13%2Fcr43ZvRjAe';//抓取url
		
		$classdata[5]['name'] = '时尚';//站内类别名称
		$classdata[5]['cid'] = 32;//站内类别id
		$classdata[5]['url'] = 'http://api.1sapp.com/content/getListV2?qdata=NTE1RDVCOEU3MUU5RUQ0QUM3MENBRTBGODJENDI1MjQuY0dGeVlXMGZPR0ZtWm1OaE5qRXRZV1E1TUMwME1qWTRMVGs0WVRBdFpqbGhPRGd5TWpCbVpXWXpIblpsY25OcGIyNGZNaDV3YkdGMFptOXliUjloYm1SeWIybGsuso3UO6aiZfRySVbtk6EHaEBDcWjVDhP5dQ5aS3VCykYqf0veA4sYr39tb1ANJ8%2BqSd13yAJVacwzEvXJ4CPhkExn1vmo7sqaNxv%2FvI34C1tvImNWRz2g0IWMRFdfz9SssR80DGkQe7axF9yLjvXqixafwMsZgmI18ZeM%2F%2BsAJssEPeYhN6y5IiV9TJHeNo70CgDyAeXJr1mLqSYQvaUfi6LMcAEQfHK%2FOgqzOd7nM5w3cU9MvooVk%2Bt8LPZiVFnCEAQgpmI35k%2FJhzxwM1S2XWkxmy74vJ%2BL7ECirPwt%2Brz7nySarBsjKo8b%2BpM7ioxr8t3AqrjszoASlinRRJqxxoQbZtABsSDc%2BOcxRminmh09dUidQ3zvozzJfXbGUG%2FcG3w8aESHmlqpeQ9yvUQddk5T%2BALG%2B5tx4Ab2Vqk2AfMQP4IzQTqELmBEV0eRJ1%2B7UhnW%2Bwb%2B5yJfVmCTZykoeQJHFeAhELrQKnMqj6yyemnR9f%2BHIQm7JTRMCdg7zxRBn9WwHxMkRyV%2FPdLi%2BLxhNhwWLEi356YCeTiNOung4XwUA2mIVpBe8%2FeVke4W4ie%2Fv54PQXLeU%2BzRnH61rvg%2FfL56612kr61Ut%2BBFSDLOhJKUcyQW0AuLlJdqW3iXDSvNw%2BlUEU%2BIIKW4BWu8DxCXZWSzWluOAKx8evoMGKN1IKVy8%2BsEpoSrk3QJ8HUU7BE66PUdUPzJawvfBmRsoLJ0CehTP28wTwKJHYZy6K9HBhHP';//抓取url
		
		$classdata[6]['name'] = '美食';//站内类别名称
		$classdata[6]['cid'] = 34;//站内类别id
		$classdata[6]['url'] = 'http://api.1sapp.com/content/getListV2?qdata=QzhDQjk0Mjc3NTYxMEMxNjhFODlGOTRGODVCQUY1ODguY0dGeVlXMGZaREk0WW1NNE0yUXRPR0psWWkwME56QTFMV0U1T1RrdE9XVXhaV1U0T1RZMk5tTmxIblpsY25OcGIyNGZNaDV3YkdGMFptOXliUjloYm1SeWIybGsuEiKw81AjlQ6MezfQY9Rjs%2BlijNEIsZan6RUrIXnaLzFb6vyqe1u8TA0%2BxKqq%2BG5HYXEPU1OSX%2FNQ4pYhv%2FVkvRYtHuahw4%2Fh8wgQpAvIzDE8YdZFBYKj%2Fcgc1rOShnjHdu9qJKxlvpQVAwMrTJICorS65CR77pb6zYMbM%2FQZt30qv4UdSmNMqXJvpfqSe%2BxcBOOCegmCT%2BkD7o%2Bs1tnXoz7L1Wcwrs5CK82qYnr6FcZio2yPyaT6kIM%2FYhEOw3CedebNnfPDFZcwl0U%2FGaQZws%2Fxmykc36Fv2abyNM4c0mr%2Fj%2FtDjRco7V0tAngwM4s3FMm%2FUb54iQjqJ54RL9BALKX0SmS0SX4Z3igi85h7JeIuYnX1b2gOvh0b165iPYWyfm2M7gZIpr%2B7ruNFdHaJDNZpsLwJF1z%2BDJF7w8vMSeTlH6aSCDvHtK2%2FfenP86c3S0a2NrcvGSDrJtwzpoZewhVpLIJMtjuVRns2cwO06%2BjFZVTbbITCPd9bQPDJJdB7pFWtHPGRHpIDyctzbEhHqj2I%2Bo0XQsYm4s%2FXOWS0IiqI%2BjSUsbOzgVRayX4WrFIC5nlOFzFEbjG4wrk3mP1F2z5MtA4EBzH1dWgKLB4G8FXvpJkmTgcPk4CgYVleSCFwyee9TzsRTOMKxX%2Bsgok0nnbOnBqVvSPG053ptKwoq5otEqQG9GTQlP3bWAcTXr1vl16p3xTkr15%2BL3xDp7XjgGqB8JzgkKRRwMrcmDCCR%2FHX';//抓取url
		
		return $classdata;
	}
	
	//获取视频的url
	public function GrabVideoUrl($url=''){
		//抓取页面数据
    		$data = file_get_contents($url);
    		//内容正则
    		$con_preg = '/"value":[\s\S]+?"}/';
    		//得到返回的数组数据
    		preg_match($con_preg,$data,$arr);
    		$arr = str_replace('"value":"',"",$arr);
    		$arr = str_replace('"}',"",$arr);
    		$url1 = 'http://mpapi.qutoutiao.net/video/getAddressByFileId?file_id='.$arr[0].'&token=&dtu=200&r=6080814033975787&o=0&s=2647797956&_=1532071160525';
    		$data1 = file_get_contents($url1);
    		$data1 = json_decode($data1,true);
    		return $data1['data']['ld']['url'];
	}
	
	//获取文章类别---小豆看点
	public function axdclass(){
		$classcdata[0]['name'] = '健康';$classcdata[0]['cid'] = 4;$classcdata[0]['type'] = 7;
		//--文化
		$classcdata[1]['name'] = '美文';$classcdata[1]['cid'] = 25;$classcdata[1]['type'] = 6;
		
		$classcdata[2]['name'] = '情感';$classcdata[2]['cid'] = 13;$classcdata[2]['type'] = 10;
		
		$classcdata[3]['name'] = '搞笑';$classcdata[3]['cid'] = 6;$classcdata[3]['type'] = 4;
		//--亲子
		$classcdata[4]['name'] = '教育';$classcdata[4]['cid'] = 18;$classcdata[4]['type'] = 14;
		
		$classcdata[5]['name'] = '美食';$classcdata[5]['cid'] = 15;$classcdata[5]['type'] = 8;
		// -- 新闻
		$classcdata[6]['name'] = '社会';$classcdata[6]['cid'] = 23;$classcdata[6]['type'] = 31;
		
		$classcdata[7]['name'] = '娱乐';$classcdata[7]['cid'] = 3;$classcdata[7]['type'] = 32;
		//军事
		$classcdata[8]['name'] = '历史';$classcdata[8]['cid'] = 20;$classcdata[8]['type'] = 12;
		
		$classcdata[9]['name'] = '旅行';$classcdata[9]['cid'] = 17;$classcdata[9]['type'] = 33;
		//科技
		$classcdata[10]['name'] = '励志';$classcdata[10]['cid'] = 9;$classcdata[10]['type'] = 40;
		
		return $classcdata;
	}
	
	//获取文章类别 -- UC头条
	protected function aclass(){
		$classcdata[0]['name'] = '时尚';
		$classcdata[0]['cid'] = 16;
		$classcdata[0]['type'] = '963a9c98ca184610c2a3054749eec76f';
		
		$classcdata[1]['name'] = '星座';
		$classcdata[1]['cid'] = 14;
		$classcdata[1]['type'] = '14c4c9d4cc9cac1af79926a5fd5bd85f';
		
		$classcdata[2]['name'] = '体育';
		$classcdata[2]['cid'] = 19;
		$classcdata[2]['type'] = 'eaabd3750a92632e39431d1197b80acc';

		$classcdata[3]['name'] = '电视';
		$classcdata[3]['cid'] = 21;
		$classcdata[3]['type'] = '897f5023c68a873ee0b0e789178ab632';
		
		return $classcdata;
	}
	
	private function qinglichongfu($type=1){
		$sql = 'SELECT id,title,cid, COUNT(title) AS count FROM `kyd_article`WHERE status=1 and type = '.$type.' GROUP BY title ORDER BY COUNT(title) DESC';
		$arr = M()->query($sql);
		$c = count($arr);
		$Article = M('Article');
		for($i=0;$i<$c;$i++){
			if($arr[$i]['count'] > 1){
				$where['title'] = $arr[$i]['title'];
				$where['id'] = array('neq',$arr[$i]['id']);
				$data['status'] = 3;
				$Article->where($where)->save($data);
			}
		}
	}
	
}

