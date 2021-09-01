<?php

namespace app\home\controller;

use app\common\library\QRcode;
use think\Controller;

class Tools extends Controller
{
    public function qrcode($url)
    {
        header('Content-Type: image/png');
        exit('<img src="'.QRcode::png($url, false,QR_ECLEVEL_L, 5 , 0).'" />');
    }
}