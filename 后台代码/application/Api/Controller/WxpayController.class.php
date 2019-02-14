<?php
/**
 * 微信支付回調
 * author universe.h
 */
namespace Api\Controller;
use Common\Lib\Wxpay\Notify;
use Think\Controller;

class WxpayController extends Controller {

    // 支付回調方法
    public function OrderChange() {
        \Think\Log::write("微信支付回调开始",'','','./WXpaylog/Callback'.date('y_m_d').'.log');
        $notify = new Notify();
        $notify -> notifySet();
        \Think\Log::write("微信支付回调结束",'','','./WXpaylog/Callback'.date('y_m_d').'.log');
    }
}

