<?php
namespace app\index\controller;

use think\Controller;

class Base extends Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($rows = db('config')->field(true)->cache(false)->select()) {
            foreach ($rows as $row) {
                $config[$row['vkey']] = $row['value'];
            }
        }
        config($config);
    }
}

