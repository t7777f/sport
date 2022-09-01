<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Cookie;
use think\Validate;
use think\Request;

class Ajax extends Controller
{
    public function ajax()
    {
        $act = input('act');
        switch ($act) {
            case 'UserReg':
                return $this->UserReg();
                break;
            case 'UserLogin':
                return $this->UserLogin();
                break;
            case 'add':
                return $this->add();
                break;
            case 'setsteps':
                return $this->setsteps();
                break;
            case 'pass':
                return $this->pass();
                break;
            case 'shop':
                return $this->shop();
                break;
            default:
                # code...
                break;
            case 'update':
                return $this->update();
                break;
            case 'deleteuin':
                return $this->deleteuin();
                break;
        }
    }
    public function shop()
    {
        $this->islogin();
        if (request()->isPost()) {
            $km = input('kms');
            if ($row = db('kms')->where('km', $km)->where('kind', 1)->find()) {
                if ($row['isuse'] == 1) {
                    return json(['code' => -1, "msg" => "卡密已被使用"]);
                } else {
                    $value = $row['ms'];
                    $udata['vipstart'] = date("Y-m-d");
                    $udata['vipend'] = date("Y-m-d", strtotime("+ {$value} months"));
                }
                if (db("user")->where("uid={$this->user['uid']}")->update($udata)) {
                    db('kms')->where('kid', $row['kid'])->update(['isuse' => 1, "uid" => $this->user['uid'], "usetime" => date("Y-m-d H:i:s")]);
                    return json(['code' => 1, "msg" => "充值" . $value . "个月会员成功"]);
                } else {
                    return json(['code' => -1, "msg" => "充值失败"]);
                }
            } else {
                return json(['code' => -1, "msg" => "卡密不存在"]);
            }
        }
    }
    public function pass()
    {
        $this->islogin();
        if(request()->isPost()){
            $pass = input('pass');
            $cpass = input('cpass');
            $cpass2 = input('cpass2');
            if(!$pass || !$cpass || !$cpass2){
                return json(['code'=>-1,"msg"=>"请输入完整"]);
            }elseif(md5($pass)!=$this->user['pass']){
                return json(['code'=>-1,"msg"=>"旧密码不正确"]);
            }elseif($cpass!=$cpass2){
                return json(['code'=>-1,"msg"=>"新密码跟重新输入密码不匹配"]);
            }elseif($pass==$cpass){
                return json(['code'=>-1,"msg"=>'旧密码不能与新密码一样']);
            }else{
                if(db('user')->where('uid',$this->user['uid'])->update(['pass'=>md5($cpass)])){
                    return json(['code'=>1,"msg"=>'修改成功']);
                }else{
                    return json(['code'=>-1,"msg"=>"修改失败"]);
                }
            }
        }
    }
    public function setsteps()
    {
        $this->islogin();
        $qid = input('qid');
        $steps = input('steps');
        if(!$qid || !$steps){
            return json(['code' => -1, "msg" => "请输入完整"]);
        }elseif(db('uin')->where("qid",$qid)->update(['steps'=>$steps])) {
            return json(['code' => 0, "msg" => "更新成功"]);
        }elseif($row = db("uin")->where("qid=:qid and uid=:uid and steps=:steps")->bind(['qid'=>$qid,"uid"=>$this->user['uid'],"steps"=>$steps])->find()){
            return json(['code'=>-1,"msg"=>"更新失败,步数跟修改步数一样"]);
        }else{
            return json(['code'=>-1,"msg"=>"更新失败:入库出错"]);
        }
    }
    public function deleteuin()
    {
        $this->islogin();
        $qid = input('qid');
        if(db('uin')->where('uid',$this->user['uid'])->where('qid',$qid)->delete()){
            return json(['code'=>1,"msg"=>"删除成功"]);
        }else{
            return json(['code'=>-1,"msg"=>"删除失败"]);
        }
    }
    public function add()
    {
        $this->islogin();
        $type = input('type');
        $uin = input('uin');
        $pwd = input('pwd');
        $uincount = db('uin')->where('uid',$this->user['uid'])->count();
        if(!$uin || !$pwd) {
            return json(['code' => -1, "msg" => "请输入完整"]);
        }elseif($row = db("uin")->where("uin=:uin and uid=:uid")->bind(['uin'=>$uin,"uid"=>$this->user['uid']])->find()){
            if(db('uin')->where("uin",$uin)->update(["pwd"=>$pwd,"skcode"=>1])){
                return json(['code'=>0,"msg"=>"更新成功"]);
            }else{
                return json(['code'=>-1,"msg"=>"更新失败:入库出错"]);
            }
        }else{
            
            if($this->user['vipend'] > date("Y-m-d")){
                if(db('uin')->insert(['type'=>$type,'uin'=>$uin,"uid"=>$this->user['uid'],"pwd"=>$pwd,"addtime"=>date("Y-m-d H:i:s"),"skcode"=>1,"steps"=>0])){
                    return json(['code'=>0,"msg"=>"添加成功"]);
                }else{
                    return json(['code'=>-1,"msg"=>"添加失败:入库出错"]);
                }
                
            }else{
                if($uincount < 2){
                    if(db('uin')->insert(['type'=>$type,'uin'=>$uin,"uid"=>$this->user['uid'],"pwd"=>$pwd,"addtime"=>date("Y-m-d H:i:s"),"skcode"=>1,"steps"=>0])){
                        return json(['code'=>0,"msg"=>"添加成功"]);
                    }else{
                        return json(['code'=>-1,"msg"=>"添加失败:入库出"]);
                    }
                    
                }else{
                    return json(['code'=>-1,"msg"=>"非会员只能添加2个账号"]);
                }
               
            }
            
        }
    }
    public function UserLogin()
    {
        $rule=[
            'username'  => 'require|alphaDash|length:5,20',
            'password'  => 'require|alphaNum|length:5,20',
        ];
        $message=[
            'username.require'=>'用户名不能为空！',
            'username.length'=>'用户名长度必须在5到20之间！',
            'password.require'=>'密码不能为空！',
            'password.alphaNum'=>'密码只能字母和数字',
            'password.length'=>'密码长度必须在5到20之间',
        ];
        $validate=new \think\Validate($rule,$message);
        if(!$validate->check(input('post.'))){
            return json(['code'=>-1,"msg"=>$validate->getError()]);
        }elseif(!$row=db('user')->where("user=:user and pass=:pass")->bind(['user'=>input('username'),"pass"=>md5(input('password'))])->find()){
            return json(['code'=>-1,"msg"=>"账号或密码错误"]);
        }else{
            if($row['active']==0){
                return json(['code'=>-1,"msg"=>"账号已被禁用"]);
            }else{
                cookie("user_token",base64_encode(json_encode(['user'=>$row['user'],"pass"=>$row['pass']])));
                return json(['code'=>0,"msg"=>"登陆成功"]);
            }
        }
    }
    private function islogin(){
        $user_token = json_decode(base64_decode(cookie("user_token")),true);
        if($row = db('user')->where("user=:user and pass=:pass")->bind(['user'=>$user_token['user'],"pass"=>$user_token['pass']])->find()){
            $this->user = $row;
            
        }else{
            return json(['code'=>-1,"msg"=>"您没有登陆账号"]);
        }
    }
    public function UserReg()
    {
        $rule=[
            'username'  => 'require|alphaDash|length:5,20',
            'password'  => 'require|alphaNum|length:5,20',
            'qq'    => 'require|number|length:4,13',
            'vcode' => 'require|alphaNum',
        ];
        $message=[
            'username.require'=>'用户名不能为空！',
            'username.length'=>'用户名长度必须在5到20之间！',
            'password.require'=>'密码不能为空！',
            'password.alphaNum'=>'密码只能字母和数字',
            'password.length'=>'密码长度必须在5到20之间',
            'qq.require'=>"QQ不能为空",
            'qq.number'=>"QQ只能数字",
            'qq.length'=>"QQ长度必须在4到13之间",
            'vcode.require'=>'验证码不能为空',
            'vcode.alphaNum'=>"验证码只能字母跟数字",
        ];
        $code = input('vcode');
        $validate=new \think\Validate($rule,$message);
        if(!$validate->check(input('post.'))){
            return json(['code'=>-1,"msg"=>$validate->getError()]);
        }elseif (strlen($code) !== 4 || $_SESSION['mz_code'] !== $code){
            return json(['code'=>-1,'msg'=>'注册失败:验证码错误']);
        }elseif(db('user')->where("user",input("username"))->find()){
            return json(['code'=>-1,"msg"=>"注册失败:用户名存在"]);
        }else{
            $check = db('user')->insert([
                'user'=>input('username'),
                'pass'=>md5(input('password')),
                'qq'=>input('qq'),
                'regtime'=>date("Y-m-d H:i:s"),
            ]);
            if($check){
                return json(['code'=>0,"msg"=>"注册成功"]);
            }else{
                return json(['code'=>-1,"msg"=>"注册失败:入库出错"]);
            }
        }
    }
    
    public function update(){
        $this->islogin();
        $qid = input('qid');
        $uin = input('uin');
        $pwd = input('pwd');
        $status = input('status');
        $hour = Request::instance()->only('hour_0,hour_1,hour_2,hour_3,hour_4,hour_5,hour_6,hour_7,hour_8,hour_9,hour_10,hour_11,hour_12,hour_13,hour_14,hour_15,hour_16,hour_17,hour_18,hour_19,hour_20,hour_21,hour_22,hour_23');
        //hour_1,hour_2,hour_3,hour_4,hour_5,hour_6,hour_7,hour_8,hour_9,hour_10,hour_11,hour_12,hour_13,hour_14,hour_15,hour_16,hour_17,hour_18,hour_19,hour_20,hour_21,hour_22,hour_23
        $uincount = db('uin')->where('uid',$this->user['uid'])->count();
         //if(!$doForm['uin'] || !$doForm['pwd']) {
        if(!$uin || !$pwd) {
            return json(['code' => -1, "msg" => "请输入完整"]);
        }elseif($row = db("uin")->where("qid=:qid and uid=:uid")->bind(['qid'=>$qid,"uid"=>$this->user['uid']])->find()){
            
            if(db('uin')->where("qid",$qid)->update(["uin"=>$uin,"pwd"=>$pwd,"skcode"=>$status,"hour_0"=>$hour['hour_0'],"hour_1"=>$hour['hour_1'],"hour_2"=>$hour['hour_2'],"hour_3"=>$hour['hour_3'],"hour_4"=>$hour['hour_4'],"hour_5"=>$hour['hour_5'],"hour_6"=>$hour['hour_6'],"hour_7"=>$hour['hour_7'],"hour_8"=>$hour['hour_8'],"hour_9"=>$hour['hour_9'],"hour_10"=>$hour['hour_10'],"hour_11"=>$hour['hour_11'],"hour_12"=>$hour['hour_12'],"hour_13"=>$hour['hour_13'],"hour_14"=>$hour['hour_14'],"hour_15"=>$hour['hour_16'],"hour_17"=>$hour['hour_18'],"hour_19"=>$hour['hour_19'],"hour_20"=>$hour['hour_20'],"hour_21"=>$hour['hour_21'],"hour_22"=>$hour['hour_22'],"hour_23"=>$hour['hour_23']])){
                return json(['code'=>0,"msg"=>"更新成功"]);
            }else{
                return json(['code'=>-1,"msg"=>"更新失败，入库出错"]);
            }
            
        }else{
            return json(['code'=>-1,"msg"=>"账号不存在"]);
        }
    }
}
