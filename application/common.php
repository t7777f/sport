<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

function getuser($uid,$value){
    $row = db('user')->where("uid",$uid)->find();
    return $row[$value];
}
function yc_phone($str){
    $str=$str;
    $resstr=substr_replace($str,'****',3,4);
    return $resstr;
}