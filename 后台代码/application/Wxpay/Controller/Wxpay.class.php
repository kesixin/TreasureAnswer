<?php

class Wxpay
{

    var $parameters; 
    var $payment; 
    public function __construct()
    {
        $this->payment=array(
            //小程序
            'wxpay_appid' => C('C_APPID'),
            'wxpay_appsecret' => C('C_APPSECRET'),
              'wxpay_appid' => 'wxeab7753eb605a4a4',
              'wxpay_appsecret' => 'ca200687c63df802d7983945d37a966b',
              'wxpay_key' => 'Zizaishe186186186ziZaishe186186z',
              'wxpay_mchid' => '1393544402',
            );
    }

    /**
     * 生成支付代码
     * @param $order
     * @return bool|string
     */
    function get_code($order){
        if ( !es_session::get('openid') )
            return false;

        $this->parameters['openid']=es_session::get('openid');
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
        $jsApiObj["appId"] =$this->payment['wxpay_appid'];
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
    public function getSign($Obj)
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
        $String = $String . "&key=" . $this->payment['wxpay_key'];
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
     * 统一下单接口有参数说明的
     * 获取prepay_id
     */
    function getPrepayId()
    {
        // 设置接口链接
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        try {
            // 检测必填参数
            if ($this->parameters["out_trade_no"] == null) {
                throw new Exception("缺少统一支付接口必填参数out_trade_no！" . "<br>");
            } elseif ($this->parameters["body"] == null) {
                throw new Exception("缺少统一支付接口必填参数body！" . "<br>");
            } elseif ($this->parameters["total_fee"] == null) {
                throw new Exception("缺少统一支付接口必填参数total_fee！" . "<br>");
            } elseif ($this->parameters["notify_url"] == null) {
                throw new Exception("缺少统一支付接口必填参数notify_url！" . "<br>");
            } elseif ($this->parameters["trade_type"] == null) {
                throw new Exception("缺少统一支付接口必填参数trade_type！" . "<br>");
            } elseif ($this->parameters["trade_type"] == "JSAPI" && $this->parameters["openid"] == NULL) {
                throw new Exception("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！" . "<br>");
            }
            $this->parameters["appid"] = $this->payment['wxpay_appid']; // 公众账号ID
            $this->parameters["mch_id"] = $this->payment['wxpay_mchid']; // 商户号
            $this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR']; // 终端ip
            $this->parameters["nonce_str"] = $this->createNoncestr(); // 随机字符串
           //统一下单接口需要的签名
            $this->parameters["sign"] = $this->getSign($this->parameters); 

           #如果值是字符串的话，最好放到CDATA节点中
            $xml = "<xml>";
            foreach ($this->parameters as $key => $val) {
                if (is_numeric($val)) {
                    $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
                } else {
                    $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
                }
            }
            $xml .= "</xml>";

            $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        } catch (Exception $e) {
            die($e->getMessage());
        }

        require_once APP_ROOT_PATH . "wap/utils/Http.class.php";
        $response = Http::curlPost($url, $xml, 30);

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


}