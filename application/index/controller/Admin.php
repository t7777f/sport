<?php
namespace app\index\controller;

class Admin extends Base
{
    public function run()
    {
        return view();
    }
    public function data()
    {
        return view();
    }
    public function km()
    {
        if(input('do')=='dell'){
            db('kms')->where("kid",input('kid'))->delete();
            return $this->success("删除成功");
        }
        if(request()->isPost()){
            $msg = "<ul class='list-group'>
			<li class='list-group-item active'>成功生成以下卡密</li>";
            for ($i = 0; $i < input('num'); $i++) {
                $data = [];
                $data['kind'] = input('kind');
                $data['daili'] = 0;
                $data['km'] = $this->getkm(12);
                $data['ms'] = input('value');
                $data['isuse'] = 0;
                $data['addtime'] = date("Y-m-d H:i:s");
                if (db("kms")->insert($data)) {
                    $msg .= "<li class='list-group-item'>{$data['km']}</li>";
                }
            }
            $msg .= "</ul>";
            $this->assign('msg', $msg);
        }
        $this->assign('list',db('kms')->paginate(15));
        return view();
    }
    private function getkm($len = 12)
    {
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $strlen = strlen($str);
        $randstr = '';
        for ($i = 0; $i < $len; $i++) {
            $randstr .= $str[mt_rand(0, $strlen - 1)];
        }
        return 'BeiTa_'.$randstr;
    }
    public function uinlist()
    {
        if(input('do')=='dell'){
            if(db('uin')->where("qid",input('qid'))->delete()){
                return $this->success("删除成功");
            }else{
                return $this->error("删除失败");
            }
        }
        $this->assign('uinlist',db('uin')->paginate(15));
        return view();
    }
    public function uset()
    {
        if(request()->isPost()){
            $qq = input('qq');
            $active = input('active');
            $pwd = input('pwd');
            if($pwd){
                $data['pass']=md5(input('pwd'));
            }
            $data['qq']=$qq;
            $data['active']=$active;
            if(db('user')->where("uid",input('uid'))->update($data)){
                return json(['code'=>0,"msg"=>"修改成功"]);
            }else{
                return json(['code'=>-1,"msg"=>"修改失败"]);
            }
        }
        $this->assign('row',db('user')->where('uid',input('uid'))->find());
        return view();
    }
    public function ulist()
    {
        if(input('do')=='dell'){
            if(db('user')->where("uid",input('uid'))->delete()){
                return $this->success("删除成功");
            }else{
                return $this->error("删除失败");
            }
        }
        $this->assign('ulist',db('user')->paginate(15));
        return view();
    }
    public function login()
    {
        if(request()->isPost()){
            $pwd = md5(input('pwd'));
            if ($pwd == config('admin_pwd')) {
                session("admin_pwd", $pwd);
                return json(['code'=>0,"msg"=>"登陆成功","url"=>url('index'),'type'=>'url']);
            } else {
                return json(['code'=>-1,"msg"=>"账号或密码不正确"]);
            }
        }
        return view();
    }
    public function index()
    {
        if(request()->isPost()){
            if (empty($_POST['admin_pwd'])) {
                unset($_POST['admin_pwd']);
            } else {
                $_POST['admin_pwd'] = md5($_POST['admin_pwd']);
            }
            foreach ($_POST as $k => $value) {
                db("wx_config")->execute("insert into wx_config set `vkey`='$k',`value`='$value' on duplicate key update `value`='$value'");
            }
            return json(['code'=>0,"msg"=>"保存成功"]);
        }
        return view();
    }
    public function __construct()
    {
        parent::__construct();
        if (request()->action() != 'login') {
            if (config('admin_pwd') && session('admin_pwd') == config('admin_pwd')) {
                // 已登录
            } else {
                $this->success('未登录', '/admin/login');
            }
        }
    }
}
