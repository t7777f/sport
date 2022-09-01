<?php
/*
 创建时间 2022-7-11 12:56:43
 严禁反编译、逆向等任何形式的侵权行为，违者将追究法律责任
*/
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Cookie;
use think\Validate;
use think\Request;


class Run extends Base
{
    public function index(){
        
        
    }
    
    public function run()
    {
        $user = 19954446510;
        $pass = 'cjh839214675';
        $count = 18321;
        $t = $this-> get_time();
        $res = $this->xmshuabu($t,$user,$pass,$count);
        //$res = $this->lxSteps($user,$pass,$count);
        return $res;
        //echo json_encode(['code'=>-1,"msg"=>"时间未到"]);
        
        
        
    }

    public function getTaskList()
    {
        
        if ($row = db('uin')->select()->toArray()){
            //$this->Count = $count = db('uin')->count();//获取账号数量
            
            foreach($row as $v){
                
                $re = $this->getTime($v['qid']);
                if($re['code'] == 0){
                    if(strtotime(date('Y-m-d H:i:s')) > strtotime(date('Y-m-d H:'.rand(1,10).':s'))){
                        
                        switch ($v['type']) {//1是小米账号，2是乐心账号
                            case '1':
                                $t = $this-> get_time();
                                $phone = $v['uin'];
                                $password = $v['pwd'];
                                $step = 0;
                                for($x=0;$x<=date('H');$x++){
                                    $step += $v['hour_'.$x.''];
                                }
                                if($v['hour_'.date('H').''] != 0){
                                    $res = $this->xmshuabu($t,$phone,$password,$step);
                                    if($res['code'] == 0){
                                    db('uin')->where('qid',$v['qid'])->update(['intime'=>date('H')]);
                                    echo '小米刷步成功';
                                    }
                                }else{
                                    db('uin')->where('qid',$v['qid'])->update(['intime'=>date('H')]);
                                    echo '该时间段无需刷步';
                                }
                                //return 222;
                                break;
                            case '2':
                                $phone = $v['uin'];
                                $password = $v['pwd'];
                                //$step = $v['hour_'.$v['intime'].''];
                                $step = 0;
                                for($x=0;$x<=date('H');$x++){
                                    $step += $v['hour_'.$x.''];
                                    
                                }
                                
                                if($v['hour_'.date('H').''] != 0){
                                    $res = $this->lxSteps($phone,$password,$step);
                                    if($res['code'] == 0){
                                    db('uin')->where('qid',$v['qid'])->update(['intime'=>date('H')]);
                                    echo json_encode(['code'=>0,"msg"=>"乐心刷步成功","uin"=>$v['uin']]);
                                    }
                                }else{
                                    db('uin')->where('qid',$v['qid'])->update(['intime'=>date('H')]);
                                }
                                
                                
                                break;
                            
                            default:
                                // code...
                                break;
                        }
                        
                        
                    }else{
                        //return 333;
                        echo json_encode(['code'=>-1,"msg"=>"时间未到"]);
                    }
                    
                }else{
                    echo json_encode($re);
                   //return $v['qid'];
                }
                
                    
                
            }
            
        }else {
            exit();
             //return json(['code'=>-1,"msg"=>"获取数据失败"]);
        }
        //Db::getTableInfo('think_user', 'fields');
    }
    
    public function getTime($qid){
        
        if(date('H')>=0 && date('H')<=23){
            $intime = db('uin')->where('qid=:qid')->bind(['qid'=>$qid])->column('intime',$qid);
            if($intime[$qid] != date('H')){
                //$row = rand(strtotime(date('H:i:s')), strtotime(date('H:i:s')) + 1800);
                //if(db('uin')->where('qid',$qid)->update(['intime'=>date('H')])){
                    return ['code'=>0,"msg"=>"更新成功"];
                //}else{
                    //return ['code'=>-1,"msg"=>"更新失败，入库出错"];
                    //return json(date('H:i:s',$row));
                //}
                
            }else{
                return ['code'=>-1,"msg"=>"时间未发生改变"];
            }
            
        }
    }
    
    //乐心登录
    public function lxLogin($phone,$password){
        $api = "sports.lifesense.com/sessions_service/login?systemType=2&version=4.6.7";
        $header = [
			'Content-Type: application/json; charset=utf-8',
		];
        $data = '{
            "appType":6,
            "clientId":"'.md5($phone).'",
            "loginName":"'.$phone.'",
            "password":"'.md5($password).'",
            "roleType":0
        }';
		$res = $this->curl($api,$data,$method='POST',$header,0);
		if($res){
		    return $res;
		}else{
		    exit();
		}
		
        
        
    }
    
    public function lxSteps($phone,$password,$step){
        $api = 'sports.lifesense.com/sport_service/sport/sport/uploadMobileStepV2?version=4.5&systemType=2';
        $row = json_decode($this->lxLogin($phone,$password),true);
        $header = [
            'Cookie: accessToken='.$row['data']['accessToken'],
            'Content-Type: application/json; charset=utf-8'
        ];
        $data = '{"list":[{
            "DataSource":2,
            "active":1,
            "calories":"'.intval($step/4).'",
            "dataSource":2,
            "deviceId":"M_NULL",
            "distance":'.intval($step/3).',
            "exerciseTime":0,
            "isUpload":0,
            "measurementTime":"'.date("Y-m-d H:i:s",time()).'",
            "priority":0,
            "step":'.$step.',
            "type":2,
            "updated":'.$this->getMillisecond().',
            "userId":'.$row['data']['userId'].'
            
        }]}';
        $res = $this->curl($api,$data,$method='POST',$header,0);
        if($res){
		    return ['code'=>0,"msg"=>"乐心刷步成功！"];
		}else{
		    return ['code'=>-1,"msg"=>"乐心刷步失败！"];
		    
		}
    }
    
    
    public function xmshuabu($t,$user,$pass,$count){
	    if($count>99999){$json = array("status"=>-1,"msg"=>'步数最大限制为99999',);return json_encode($json);}
	    if($user==''){$json = array("status"=>-1,"msg"=>'小米运动账号不能为空',);return json_encode($json);}
	    if($pass==''){$json = array("status"=>-1,"msg"=>'小米运动密码不能为空',);return json_encode($json);}
	    if($count==''){$json = array("status"=>-1,"msg"=>'小米运动步数不能为空',);return json_encode($json);}
	    $access = $this->xmLogin($user,$pass);
	    if($access == ''){
		    $json = array(
			    "status"=>0,
			    "msg"=>'小米运动账号或者密码错误',
		    );
		    return json_encode($json);
	    }
	    $user_json = $this->get_user($access);
	    $user_id = $this->get_user_id($user_json);
	    $app_token = $this->get_app_token($user_json);
	    $return = json_decode($this->load($t,$user_id,$app_token,$count),true);
	    if($return['code']=='1'){
		    $json = array(
		    	"status"=>0,
		    	"msg"=>'提交步数成功',
		    	"author"=>'chen',
		    	"qq"=>'201579270'
		        );
	    }else{
		    $json = array(
		    	"status"=>-1,
	    		"msg"=>$return['message'],
		    	"author"=>'chen',
	    		"qq"=>'201579270'
		);
	    }
	    return json_encode($json);
    	
    }
    
    //小米登陆,成功返回access
    public function xmLogin($phone,$password){
        $api = "https://api-user.huami.com/registrations/+86".$phone."/tokens";
        $header = array(
        //"Content-Type:application/x-www-form-urlencoded;charset=UTF-8",
        "User-Agent:ZeppLife/6.1.3 (iPhone; iOS 15.4.1; Scale/2.00)"
        );
        $data = array(
		    "client_id" => "HuaMi",
		    "country_code" => "CN",
		    "password" => $password,
		    "redirect_uri" => "https%3A//s3-us-west-2.amazonaws.com/hm-registration/successsignin.html",
		    "state" => "REDIRECTION",
		    "token" => "access"
	    );
        $return = $this->curl($api,$data,$method='POST',$header,1);
        if(preg_match_all('/access=(.*?)&country_code/i', $return, $res)){
            return $res[1][0];
        }else{
            return '';
        }
        
        
    }
    
    //小米提交步数
    public function load($t,$user_id,$app_token,$count){
	    $api = "https://api-mifit-cn.huami.com/v1/data/band_data.json?&t={$t}";
	    $last_deviceid = 'DA932FFFFE8816E7';
	    $header = [
	        "apptoken:$app_token",
            //"Content-Type:application/x-www-form-urlencoded;charset=UTF-8",
            //"User-Agent:ZeppLife/6.1.3 (iPhone; iOS 15.4.1; Scale/2.00)"
            ];
    	$data_json = $this->data_json($count);
	    $post_data = array(
        "data_json" => $data_json,
        "userid" => $user_id,
        "device_type" => "0",
        "last_sync_data_time" => "1589917081",
        "last_deviceid" => "DA932FFFFE8816E7"
        );
	    $return = $this->curl($api,$post_data,$method='POST',$header,0);
	    $return2 = $this->load2($user_id,$app_token,$count);
	    return $return;		
        
    }
    
    public function load2($user_id,$app_token,$count){
        $api = 'https://api-mifit-cn.huami.com/v1/data/band_data.json';
	    //$last_deviceid = 'FC30D8FFFE3E028A';
	    $last_deviceid = 'DA932FFFFE8816E7';
	    $header = [
	        "apptoken:$app_token",
            //"Content-Type:application/x-www-form-urlencoded;charset=UTF-8",
            //"User-Agent:ZeppLife/6.1.3 (iPhone; iOS 15.4.1; Scale/2.00)"
            ];
	    $data = 'data_json=[{"summary":"{\"stp\":{\"runCal\":7,\"cal\":111,\"conAct\":0,\"stage\":[],\"ttl\":【步数】,\"dis\":3102,\"rn\":2,\"wk\":43,\"runDist\":146,\"ncal\":0},\"v\":5,\"goal\":2000}","data":[{"stop":1439,"value":"","did":"【last_deviceid】","tz":32,"src":24,"start":0}],"data_hr":"","summary_hr":"{\"ct\":0,\"id\":[]}","date":"【今日日期】"}]&device_type=0&enableMultiDevice=1&last_deviceid=【last_deviceid】&last_source=24&last_sync_data_time=【10位时间戳】&userid=【用户ID】&uuid=';
	    $post_data=str_replace('【步数】',$count,$data);
    	$post_data=str_replace('【last_deviceid】',$last_deviceid,$post_data);
    	$post_data=str_replace('【用户ID】',$user_id,$post_data);
    	$post_data=str_replace('【今日日期】',date("Y-m-d",time()),$post_data);
    	$post_data=str_replace('【10位时间戳】',time(),$post_data);
    	$post_data=str_replace('【初始10位时间戳】',strtotime(date("Y-m-d",time())),$post_data);
    	$return = $this->curl($api,$post_data,$method='POST',$header,0);
    	return $return;		

    }
    
    
    public function get_time(){
    $api = "http://api.m.taobao.com/rest/api3.do?api=mtop.common.getTimestamp";
    $res = $this->curl($api,'',$method='GET',[],0);
    return json_decode($res, true)['data']['t'];
    }
        
    public function get_user($code){
        $api = "https://account.huami.com/v2/client/login";
        $header = [
            "Content-Type:application/x-www-form-urlencoded;charset=UTF-8",
            "User-Agent:ZeppLife/6.1.3 (iPhone; iOS 15.4.1; Scale/2.00)"
            ];
        
        $data = 'allow_registration=false&app_name=com.xiaomi.hm.health&app_version=6.1.3&code='.$code.'&country_code=CN&device_id=D0A48B00-049C-4189-96A1-724AF6A6E5DE&device_id_type=uuid&device_model=phone&dn=api-user.huami.com%2Capi-mifit.huami.com%2Capp-analytics.huami.com%2Caccount.huami.com%2Capi-watch.huami.com%2Cauth.huami.com&grant_type=access_token&lang=zh_CN&os_version=1.5.0&source=com.xiaomi.hm.health&third_name=huami_phone';
        $return = $this->curl($api, $data, $method='POST',$header,0);
        return $return;
        
    }
    public function data_json($steps){
    $datas = '[{"summary":"{\"slp\":{\"ss\":73,\"lt\":304,\"dt\":0,\"st\":1589920140,\"lb\":36,\"dp\":92,\"is\":208,\"rhr\":0,\"stage\":[{\"start\":269,\"stop\":357,\"mode\":2},{\"start\":358,\"stop\":380,\"mode\":3},{\"start\":381,\"stop\":407,\"mode\":2},{\"start\":408,\"stop\":423,\"mode\":3},{\"start\":424,\"stop\":488,\"mode\":2},{\"start\":489,\"stop\":502,\"mode\":3},{\"start\":503,\"stop\":512,\"mode\":2},{\"start\":513,\"stop\":522,\"mode\":3},{\"start\":523,\"stop\":568,\"mode\":2},{\"start\":569,\"stop\":581,\"mode\":3},{\"start\":582,\"stop\":638,\"mode\":2},{\"start\":639,\"stop\":654,\"mode\":3},{\"start\":655,\"stop\":665,\"mode\":2}],\"ed\":1589943900,\"wk\":0,\"wc\":0},\"tz\":\"28800\",\"stp\":{\"runCal\":1,\"cal\":6,\"conAct\":0,\"stage\":[],\"ttl\":' . $steps . ',\"dis\":144,\"rn\":0,\"wk\":5,\"runDist\":4,\"ncal\":0},\"v\":5,\"goal\":8000}","data":[{"stop":1439,"value":"WhQAUA0AUAAAUAAAUAAAUAAAUAAAWhQAUAYAcBEAUAYAUA8AUAsAUAYAUDIAUCQAUDkAUCkAUD4AUC0AUFcAUD8AUCkAUCEAUCwAUCsAUB4AUCQAUBsAUCcAUBQAUDcAUBoAUCYAUFcAUCAAUDkAUCEAWhQAWhQAWhQAUBAAUEgAUDsAUAgAWhQAUDwAUCEAUAIAUAsAUDoAUD8AWhQAWhQAWhQAWhQAWhQAWhQAAS0QEAsAWhQAAR8SEBcHYC4AUCoAUBMAUAIAUAYAUAsAUCsAUAUAUBIAUBIAUBsAUBgAUAoAUBsAUBUAUBkAUDIAUC0AUC4AUBAAWhQAUCsAUB8AUAIAUB8AUDUAUEEAUDUAUBkAUCYAUEoAUCYAUBIAUCAAUCkAUDAAUB4AUB0AUDEAUCUAUCgAUAQAWhQAUA8AUDwAUB8AUCUAUBQAUB4AUAUAWhQAUAAAUA8AUBkAUCgAUCwAUCkAUCgAYCIAYCIAYCgAUAoAWhQAUBwAWhQAUBoAUDkAUD4AYAkAYAYAWhQAWhQAUB4AWhQAUAQAUBcAUBAAUAUAWhQAUB0AcBYAehQAcBoAehQAehQAehQAcAMAcAMAehQAcAIAehQAcBIAcA0AehQAehQAcAsAcAYAcAEAcAoAehQAehQAcAwAehQAehQAehQAcAEAehQAehQAcAsAehQAehQAcA8AcBkAcAYAcBkAcC0AcAQAcBsAcAMAWhQAUAMAWhQAUBEAUAIAWhQAWhQAWhQAehQAehQAehQAehQAehQAehQAcAAAcB8AcBMAehQAehQAcDkAcBAAcAEAcAMAcAMAcCwAcA8AcAAAcAAAcCIAcAAAcCcAcB4AehQAcAkAehQAcCMAehQAehQAcAoAehQAehQAehQAcBgAcBgAcAkAehQAcAcAcCgAcBQAcA0AcAwAcCcAcCkAcAAAUAAAUAAAUB4AUBwAUAAAUAAAUCkAUBIAUBMAUCgAUA8AUBEAUD0AUCAAYAMAYCkAUBsAUB4AYCgAahQAUBkAWhQAWhQAUCAAUBcAUA8AUBAAUAcAUB8AUCEAUCMAUCkAYAMAYAAAUBsAUBEAUBgAUAUAUB0AUAAAUAAAUAAAUAAAUAAAUAQAUAAAUAAAUAAAUAAAWwAAUAAAcAAAcAAAcAAAcAAAcAAAcAAAcA0AcAAAcAAAcAAAcAIAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcA8AehQAehQAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAEAeRMAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAsAcAAAcAAAcAAAcAAAcAAAcAoAcAAAcBMAcAAAcAAAcAAAcAAAcAAAcAAAcA4AcAcAehQAehQAcAAAcAAAcAIAehQAehQAcAAAcAAAcAAAcAAAcAAAcAIAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcBcAehQAehQAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAehQAcAMAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcBUAeQAAcAAAcAAAcAAAcFgAcAAAcAAAcAAAcBkAeQAAcAAAcAAAcAAAcAAAcE0AcAQAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAeVAAehQAehQAcAAAcAAAcAAAcAAAcAUAeRwAUAAAUFUAUAAAUAAAUAAAUAAAUAAAUCMAeQAAcAAAcAAAcE0AUAAAUAAAUAAAUAAAUAAAUAAAcAAAcAAAcAAAcE4AcAAAcAAAcAAAcAAAcAgAcBAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAkAcAAAcAAAcAAAcAAAcBwAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAYAcBAAeQAAcB8AeQAAcAAAcAAAcAAAeSoAcAAAcAAAcAAAcAAAcAAAcAsAcAAAeScAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcCAAcAAAUAAAUAAAUAAAUAAAUAAAUBEAehQAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcBwAehQAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcBYAcAAAcAAAcAAAcAYAcAAAcAAAcCsAcAAAcAAAcAgAcAAAcAAAcBsAeRQAcAAAcAAAcAEAcAAAcAAAcAAAcAAAcAAAcAAAcA8AcAAAcAAAcBoAcAAAcAEAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAQAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcBIAcAAAcA0AcBAAcAAAcAAAcAAAcAAAehQAehQAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcCgAcAAAcBkAcAAAcB0AcAAAcAAAcBgAcAAAUAEAUBsAWhQAUB4AWhQAUCkAWQ8AUCsAUA0AWTUAXBAAWhQAUBMAUAQAUAcAUAoAUA8AUBkAUBcAUCoAUAIAUBQAWhQAWhQAUBIAUBQAUAcAWhQAUBYAWhQAUAgAWhQAWhQAUAkAUE0AUHUAAWMTEEcKYDoAYAgAUAMAWhQAUAUAUAYAUAkAUB4AUAsAUAIAUBMAWhQAAVQdAWAlEDYAYCQAUAQAUBgAUAgAUAUAUBQAUAIAWhQAUAkAUAMAUA4AWhQAehQAcAoAcAIAehQAcB0AcCcAUCsAUAEAUAgAUAoAUAIAUAsAUAIAWhQAWhQAUAgAUA0AWhQAUAYAWhQAUAEAWhQAWhQAUBAAUBQAUBIAUBcAUAoAYBAAYAIAAUkZAUglAVYSYBcAYAoAYCAAYAsAUBUAUB0AUBAAUBEAUCAAUBUAUBYAUA0AUB4AUBcAUBsAUBMAUBUAYAsAYAwAYAsAUB4AUBoAUBoAUBoAUBQAUAcAWhQAUBgAUBkAUBsAUBUAUBAAUCAAUCYAUB8AUB4AUBwAUAcAUBsAUBwAUBwAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAA","did":"DA932FFFFE8816E7","tz":32,"src":17,"start":0}],"data_hr":"\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+","summary_hr":"{\"ct\":0,\"id\":[]}","date":"' . date("Y-m-d", time()) . '"}]';
        return $datas;
    }
    
    public function get_app_token($user_json){//解析user_json，获取app_token
	    $json = json_decode($user_json,true);
	    return $json['token_info']['app_token'];	
    }
    
    public function get_user_id($user_json){//解析user_json，获取user_id
	    $json = json_decode($user_json,true);
	    return $json['token_info']['user_id'];
    }
 
    //获取加秒数的时间戳
    public function getMillisecond() {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
        }
        
    
    public function curl($url, $data, $method='POST',$header,$head){   
    	$curl = curl_init(); // 启动一个CURL会话  
    	curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址  
    	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查  
    	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在  
    	curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器  
    	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转  
    	curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer  
    	curl_setopt($curl, CURLOPT_HTTPHEADER,$header);

    	if($method=='POST'){  
        	curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求  
        	if ($data != ''){  
            	curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包  
        	}  
    	}else{
    	    curl_setopt($curl, CURLOPT_POST, 0);
    	}  
		curl_setopt($curl, CURLOPT_ENCODING ,'gzip');
    	curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环  
    	curl_setopt($curl, CURLOPT_HEADER, $head); // 显示返回的Header区域内容  
    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回  
    	//curl_setopt($curl, CURLINFO_HEADER_OUT, true);
    	$tmpInfo = curl_exec($curl); // 执行操作  
    
    	curl_close($curl); // 关闭CURL会话  
    	return $tmpInfo; // 返回数据  
	}
    

    


}
?>