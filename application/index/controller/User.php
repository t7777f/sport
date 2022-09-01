<?php
namespace app\index\controller;

class User extends Base
{
    public function userinfo()
    {
        $this->islogin();
        return view();
    }
    public function logout()
    {
        cookie("user_token",null);
        header("location:/index");
        exit();
    }
    public function km()
    {
        $this->islogin();
        return view();
    }
    public function uinconfig()
    {
        $this->islogin();
        $qid = input('qid');
        if($row = db('uin')->where("qid=:qid and uid=:uid")->bind(['qid'=>$qid,"uid"=>$this->user['uid']])->find()){
            $this->assign('log',db('runlog')->where('uid',$this->user['uid'])->paginate(100));
            $this->assign('row',$row);
        }else{
            $this->error("网站中无此QQ");
            $this->assign('row',null);
        }
        return view();
    }
    public function uinlist()
    {
        $this->islogin();
        $this->assign('row',db('uin')->where('uid',$this->user['uid'])->paginate(4));
        return view();
    }
    public function add()
    {
        $this->islogin();
        return view();
    }
    public function index()
    {
        $this->islogin();
        return view();
    }
    private function islogin(){
        $user_token = json_decode(base64_decode(cookie("user_token")),true);
        if($row = db('user')->where("user=:user and pass=:pass")->bind(['user'=>$user_token['user'],"pass"=>$user_token['pass']])->find()){
            $this->user = $row;
            $this->assign('user',$row);
        }else{
            header("location:/index/login");
            exit();
        }
    }
}

