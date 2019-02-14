<?php
/**
 * 通用通知接口demo
 * ====================================================
 * 支付完成后，微信会把相关支付和用户信息发送到商户设定的通知URL，
 * 商户接收回调信息后，根据需要设定相应的处理流程。
 *
 * 这里举例使用log文件形式记录回调信息。
 */
require_once '../../system/system_init.php';
require '../app/item.php';
require_once "./log_.php";
require_once "../utils/Wxpay.class.php";
$log_ = new Log_();
$log_name = "./notify_url.log";//log文件路径

$postStr1 = "<xml>
     <appid><![CDATA[wxeab7753eb605a4a4]]></appid>
     <attach><![CDATA[项目支付]]></attach>
     <bank_type><![CDATA[CFT]]></bank_type>
     <fee_type><![CDATA[CNY]]></fee_type>
     <is_subscribe><![CDATA[Y]]></is_subscribe>
     <mch_id><![CDATA[1393544402]]></mch_id>
     <nonce_str><![CDATA[5d2b6c2a8db53831f7eda20af46e531c]]></nonce_str>
     <openid><![CDATA[oIZcNxAuS_cSKWdT6vgcNkgrkrXs]]></openid>
     <out_trade_no><![CDATA[20160929975110056912]]></out_trade_no>
     <result_code><![CDATA[SUCCESS]]></result_code>
     <return_code><![CDATA[SUCCESS]]></return_code>
     <sign><![CDATA[A669DBD3734ABD9462BD7DAA4CC3319C]]></sign>
     <sub_mch_id><![CDATA[10000100]]></sub_mch_id>
     <time_end><![CDATA[20160929131540]]></time_end>
     <total_fee>1</total_fee>
     <trade_type><![CDATA[JSAPI]]></trade_type>
     <transaction_id><![CDATA[1004400740201409030005092138]]></transaction_id>
   </xml>";

$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
$xml_arr = array(
    'return_code' => 'SUCCESS',
);

try {
    if (empty($postStr)) {
        throw new Exception('无数据返回');
    }

    $postdata = json_decode(json_encode(simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

    #====先判断上述的xml信息是否是微信服务器给的，即进行签名比对==============
    $wxsign = $postdata['sign'];
    unset($postdata['sign']);#将签名先剖除

    $wxpay = new Wxpay();
    $sign = $wxpay->getSign($postdata);

    if ($wxsign != $sign) {
        throw new Exception('签名失败');
    }

    if ($postdata['result_code'] != 'SUCCESS') {
        throw new Exception('支付失败');
    }

    //开始处理业务
    $order_sn = $postdata['out_trade_no'];
    $amount = round($postdata['total_fee'] / 100, 2);
    $transaction_id = $postdata['transaction_id'];

    // 判断是否已经支付过了
    $order_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "deal_order where notice_sn = '" . $order_sn . "'");
    if ($order_info['order_status'] == 0) {
        if ($amount != $order_info['total_price']) {
            throw new Exception('金额有误');
        }

        // 更新订单状态
        $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_order", array('pay_time' => get_gmtime(), 'order_status' => 3), 'UPDATE', "notice_sn = '" . $order_sn . "'");

        // 生成一条支付记录
        $pay_notice = array(
            'notice_sn' => $order_sn,
            'create_time' => get_gmtime(),
            'pay_time' => get_gmtime(),
            'order_id' => $order_info['id'],
            'is_paid' => 1,
            'user_id' => $order_info['user_id'],
            'payment_id' => 25,
            'memo' => $order_info['support_memo'],
            'money' => $order_info['total_price'],
            'deal_id' => $order_info['deal_id'],
            'deal_item_id' => $order_info['deal_item_id'],
            'deal_dream_id' => $order_info['deal_dream_id'],
            'deal_name' => $order_info['deal_name'],
            'outer_notice_sn' => $transaction_id,
        );
        $GLOBALS['db']->autoExecute(DB_PREFIX . "payment_notice", $pay_notice);

        $pay_id = $GLOBALS['db']->insert_id();

        // 添加支持记录
        $data = array(
            'deal_id' => $order_info['deal_id'],
            'deal_item_id' => $order_info['deal_item_id'],
            'deal_dream_id' => $order_info['deal_dream_id'],
            'user_id' => $order_info['user_id'],
            'create_time' => get_gmtime(),
            'price' => $order_info['total_price'],
            'pay_id' => $pay_id,
        );
        $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_support_log", $data);

        // 更新deal表
        $GLOBALS['db']->query("update " . DB_PREFIX . "deal set support_count=support_count+1,support_amount=support_amount+" . $order_info['total_price'] . " where id=" . $order_info['deal_id']);

        // 更新用户支持字段
        $GLOBALS['db']->query("update " . DB_PREFIX . "user set support_count=support_count+1 where id=" . $order_info['user_id']);

        $GLOBALS['db']->query("update " . DB_PREFIX . "count set support_count=support_count+1 where id=1");

        if($order_info['deal_item_id']>0){
            // 更新deal_item表
            $GLOBALS['db']->query("update " . DB_PREFIX . "deal_item set support_count=support_count+1,support_amount=support_amount+" . $order_info['total_price'] . ",limit_user=limit_user-".$order_info['num']." where id=" . $order_info['deal_item_id']);
        }elseif ($order_info['deal_dream_id']>0){
            // 更新deal_dream表
            $GLOBALS['db']->query("update " . DB_PREFIX . "deal_dream set support_count=support_count+1,support_amount=support_amount+" . $order_info['total_price'] . " where id=" . $order_info['deal_dream_id']);
        }

    }
} catch (Exception $e) {
    $xml_arr['return_code'] = 'FAIL';
    $xml_arr['return_msg'] = $e->getMessage();
}

// 数组转化为xml
$xml = "<xml>";
foreach ($xml_arr as $key => $val) {
    if (is_numeric($val)) {
        $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
    } else
        $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
}
$xml .= "</xml>";

echo $xml;
exit();