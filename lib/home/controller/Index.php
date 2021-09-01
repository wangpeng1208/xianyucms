<?php

namespace app\home\controller;

use app\common\controller\Home;

class Index extends Home
{
    public function index()
    {
        return $this->fetch('/index');
    }
}