<?php
namespace Common\Lib\Wxpay;
/**
 * Class WxFpay
 * 微信服务商支付类
 * author universe.h
 */
class WxFpay
{

    var $parameters; 
    var $payment; 
    var $transfers;
    public function __construct()
    {
        $this->payment=array(
            //小程序
            'sub_appid' => C('C_APPID'),
            'wxpay_appsecret' => C('C_APPSECRET'),
            //支付关联公众号
            'appid' => 'wxf64b32fe16bf8e43',
            'mch_id' => C('F_MCHID'),
            //子商户
//            'sub_appid' => C('F_KEY'),
            'sub_mch_id' => '1489667292',
        );

        //企业付款到零钱
        $this->transfers=array(
            'mch_appid'=>C('G_APPID'),              //公众号\小程序appid
            'mchid'=>'1489727812',                  //企业号的商户id
            'key'=>'eds9g3fds8jfdszhi168hui186yun188',                  //企业号的商户id
            'device_info'=>'',                      //企业号的商户id
            'check_name'=>'NO_CHECK',              //校验用户姓名选项NO_CHECK：不校验真实姓名 FORCE_CHECK：强校验真实姓名
        );
    }

    /**
     * 生成支付代码
     * @param $order
     * @return bool|string
     */
    function get_code($order,$openid){
        if ( !$openid )
            return false;

        $this->parameters['openid']=$openid;
        $this->parameters['body']=$order['body'];
        $this->parameters['out_trade_no']=$order['out_trade_no'];
        $this->parameters['total_fee']=$order['total_fee'] * 100;#订单总金额，单位为分
        #接收微信支付异步通知回调地址
        $this->parameters['notify_url']=$order['notify_url'];
        $this->parameters['trade_type']="JSAPI";

        return $jsApiParameters = $this->getParameters();
    }

    /**
     * $prepay_id必须先求出来然后传进来
     * 作用：设置js请求的6个参数
     */
    public function getParameters()
    {

        #最终的目的就是为了得到这个6个参数而已
        $prepay_id = $this->getPrepayId();
        $jsApiObj["appId"] =$this->payment['appid'];
        $jsApiObj["timeStamp"] =strval(time());
        $jsApiObj["nonceStr"] = $this->createNoncestr();#随机串
        $jsApiObj["package"] = "prepay_id=$prepay_id";#这个最烦人
        $jsApiObj["signType"] = "MD5";#签名方式
        $jsApiObj["paySign"] = $this->getSign($jsApiObj);#微信签名

        #转成json格式
        $this->parameters = json_encode($jsApiObj);
        return $this->parameters;
    }

    /**
     * 响应操作
     */
    function callback($data)
    {
        if ($data['status'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 作用：产生随机字符串，不长于32位
     */
    public function createNoncestr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i ++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }



    /**
     * 作用：生成签名
     */
    public function getSign($Obj,$key='')
    {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        // 签名步骤一：按字典序排序参数
        ksort($Parameters);
        
        $buff = "";
        foreach ($Parameters as $k => $v) {
            $buff .= $k . "=" . $v . "&";
        }
        $String = '';
        if (strlen($buff) > 0) {
            $String = substr($buff, 0, strlen($buff) - 1);
        }
        
        // 签名步骤二：在string后加入KEY
        $key =$key ?: $this->payment['wxfpay_key'];
        $String = $String . "&key=" . $key;
        // echo "【string2】".$String."</br>";
        // 签名步骤三：MD5加密
        $String = md5($String);
        // echo "【string3】 ".$String."</br>";
        // 签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        // echo "【result】 ".$result_."</br>";
        return $result_;
    }

    /**
     * 普通商户统一下单接口有参数说明的
     * 获取prepay_id
     */
    function getPrepayId()
    {
        // 设置接口链接
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        try {
            // 检测必填参数
            if ($this->parameters["out_trade_no"] == null) {
                throw new \Exception("缺少统一支付接口必填参数out_trade_no！" . "<br>");
            } elseif ($this->parameters["body"] == null) {
                throw new \Exception("缺少统一支付接口必填参数body！" . "<br>");
            } elseif ($this->parameters["total_fee"] == null) {
                throw new \Exception("缺少统一支付接口必填参数total_fee！" . "<br>");
            } elseif ($this->parameters["notify_url"] == null) {
                throw new \Exception("缺少统一支付接口必填参数notify_url！" . "<br>");
            } elseif ($this->parameters["trade_type"] == null) {
                throw new \Exception("缺少统一支付接口必填参数trade_type！" . "<br>");
            } elseif ($this->parameters["trade_type"] == "JSAPI" && $this->parameters["openid"] == NULL) {
                throw new \Exception("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！" . "<br>");
            }
            $this->parameters["appid"] = $this->payment['appid']; // 公众账号ID
            $this->parameters["mch_id"] = $this->payment['mch_id']; // 商户号
            $this->parameters["sub_appid"] = $this->payment['sub_appid']; //小程序appid
            $this->parameters["sub_mch_id"] = $this->payment['sub_mch_id']; //子商户号
            $this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR']; // 终端ip
            $this->parameters["nonce_str"] = $this->createNoncestr();
            var_dump( $this->parameters);
           //统一下单接口需要的签名
            $this->parameters["sign"] = $this->getSign($this->parameters);  $this->createNoncestr(); // 随机字符串
           #如果值是字符串的话，最好放到CDATA节点中
            $xml = $this->toXml($this->parameters);

            $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        } catch (Exception $e) {
            die($e->getMessage());
        }
        $response = curl_data($url, $xml, 30);
        var_dump($response);
        /**
         * simplexml_load_file(string,class,options,ns,is_prefix)
         *  string  必需。规定要使用的 XML 字符串。
         *  class   可选。规定新对象的 class。
         *  options     可选。规定附加的 Libxml 参数。
         *  ns  可选。
         *  is_prefix   可选。
         * 
         */
        #从SimpleXMLElement对象转成json数据，之后再转成数组而已
        $result = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        $prepay_id = $result["prepay_id"];
        return $prepay_id;
    }

    /**
     * 服务商统一下单接口
     * 获取prepay_id
     */
    function getTransfersPayId()
    {
        // 设置接口链接
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        try {
            // 检测必填参数
            if ($this->parameters["out_trade_no"] == null) {
                throw new \Exception("缺少统一支付接口必填参数out_trade_no！" . "<br>");
            } elseif ($this->parameters["body"] == null) {
                throw new \Exception("缺少统一支付接口必填参数body！" . "<br>");
            } elseif ($this->parameters["total_fee"] == null) {
                throw new \Exception("缺少统一支付接口必填参数total_fee！" . "<br>");
            } elseif ($this->parameters["notify_url"] == null) {
                throw new \Exception("缺少统一支付接口必填参数notify_url！" . "<br>");
            } elseif ($this->parameters["trade_type"] == null) {
                throw new \Exception("缺少统一支付接口必填参数trade_type！" . "<br>");
            } elseif ($this->parameters["trade_type"] == "JSAPI" && $this->parameters["openid"] == NULL) {
                throw new \Exception("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！" . "<br>");
            }
            $this->parameters["appid"] = $this->payment['wxfpay_appid']; // 公众账号ID
            $this->parameters["mch_id"] = $this->payment['wxfpay_mchid']; // 商户号
            $this->parameters["sub_appid"] = $this->payment['sub_appid']; //小程序appid
            $this->parameters["sub_mch_id"] = $this->payment['wxpay_mchid']; //子商户号
            $this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR']; // 终端ip
            $this->parameters["nonce_str"] =
           //统一下单接口需要的签名
            $this->parameters["sign"] = $this->getSign($this->parameters);  $this->createNoncestr(); // 随机字符串
            var_dump( $this->parameters);die;
           #如果值是字符串的话，最好放到CDATA节点中
            $xml = $this->toXml($this->parameters);

            $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        } catch (Exception $e) {
            die($e->getMessage());
        }
        $response = curl_data($url, $xml, 30);
        /**
         * simplexml_load_file(string,class,options,ns,is_prefix)
         *  string  必需。规定要使用的 XML 字符串。
         *  class   可选。规定新对象的 class。
         *  options     可选。规定附加的 Libxml 参数。
         *  ns  可选。
         *  is_prefix   可选。
         *
         */
        #从SimpleXMLElement对象转成json数据，之后再转成数组而已
        $result = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        $prepay_id = $result["prepay_id"];
        return $prepay_id;
    }

    /**
     * 组装成xml
     * @param $xmldata
     * @return string
     */
    public function toXml($xmldata){
        $xml = "<xml>";
        foreach ($xmldata as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     *()
     * 查询订单，WxPayOrderQuery中out_trade_no、transaction_id至少填一个
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param WxPayOrderQuery $inputObj
     * @param int $timeOut
     * @throws WxPayException
     * @return 成功时返回，其他抛异常
     */
    public function orderQuery($data, $timeOut = 30)
    {
        $url = "https://api.mch.weixin.qq.com/pay/orderquery";
        //缺少订单号
        if(!$data['transaction_id'] && !$data['out_trade_no'] ){
            throw new Exception("缺少统微信的支付订单号！" . "<br>");
        }
        //缺少随机字符串
        if(!$data['nonce_str']){
            throw new Exception("缺少随机字符串！" . "<br>");
        }
        //检测必填参数
        $query_data['appid'] = $this->payment['sub_appid']; // 小程序id
        $query_data['mch_id'] = $this->payment['wxpay_mchid']; // 商户号
        $query_data['transaction_id'] = $data['transaction_id']; // 微信订单号
        $query_data['nonce_str'] = $data['nonce_str']; // 随机字符
        $query_data['sign_type'] = 'MD5'; // 加密类型
        $query_data['sign'] =  $this->getSign($query_data); // 签名

        $xml = $this->toXml($query_data);
        $response = curl_data($url, $xml, $timeOut);
        $response = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $response;
    }

    /**
     * 企业商户付款到零钱
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function transfers($data){
        try {
            if (!$data['partner_trade_no']) {
                throw new \Exception("缺少统企业付款商户订单号！" . "<br>");
            } else if (!$data['openid']) {
                throw new \Exception("缺少统企业付款用户openid！" . "<br>");
            } else if ($this->transfers['check_name'] == 'NO_CHECK') {
                unset($data['re_user_name']);
            } else if ($this->transfers['check_name'] == 'FORCE_CHECK' && !$data['re_user_name']) {
                throw new \Exception("缺少统企业付款收款用户真实姓名！" . "<br>");
            } else if (!$data['amount']) {
                throw new \Exception("缺少统企业付款金额！" . "<br>");
            } else if (!$data['desc']) {
                throw new \Exception("缺少统企业付款企业付款描述信息！" . "<br>");
            }
            $post_data = [
                'amount' => $data['amount'],
                'check_name' => $this->transfers['check_name'],
                'desc' => $data['desc'],
                'mch_appid' => $this->transfers['mch_appid'],
                'mchid' => $this->transfers['mchid'],
                'nonce_str'=> $this->createNoncestr(),// 随机字符串
                'partner_trade_no' => $data['partner_trade_no'],
                'openid' => $data['openid'],
//                're_user_name'=>$data['re_user_name'],
                'spbill_create_ip'=>get_client_ip(),
            ];
            $post_data['sign'] = $this->getSign( $post_data, $this->transfers['key'] );


            $xml = $this->toXml( $post_data );
;
        }catch (Exception $e) {
            die($e->getMessage());
        }
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $response = curl_data($url, $xml, 30,true);
        var_dump($response);
        return $response;
    }

}