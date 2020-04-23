<?php
/**
 * Created by PhpStorm.
 * User: konglingquan
 * Date: 2018-12-19
 * Time: 14:23
 */

class wxpay extends abstract_payment
{
    public function create_pay_url($args)
    {
        //unfinished
    }

    public function response($args)
    {
        //unfinished
    }

    /**
     * 发起订单
     * @param float $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * @param string $notifyUrl 支付结果通知url 不要有问号
     * @param string $timestamp 订单发起时间
     * @return array
     */
    public function createJsBizPackage($totalFee, $outTradeNo, $orderName, $notifyUrl, $timestamp,$text='pay')
    {
        $config = array(
            'mch_id' => $this->config['mch_id'],
            'appid' => $this->config['appid'],
            'key' => $this->config['pay_key'],
        );
        //$orderName = iconv('GBK','UTF-8',$orderName);
        $unified = array(
            'appid' => $config['appid'],
            'attach' => $text,             //商家数据包，原样返回，如果填写中文，请注意转换为utf-8
            'body' => $orderName,
            'mch_id' => $config['mch_id'],
            'nonce_str' => self::createNonceStr(),
            'notify_url' => $notifyUrl,
            'out_trade_no' => $outTradeNo,
            'spbill_create_ip' => '127.0.0.1',
            'total_fee' => intval($totalFee * 100),       //单位 转为分
            'trade_type' => 'NATIVE',
        );
        $unified['sign'] = self::getSign($unified, $config['key']);
        $responseXml = self::curlPost('https://api.mch.weixin.qq.com/pay/unifiedorder', self::arrayToXml($unified));

        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false) {
            die('parse xml error');
        }
        if ($unifiedOrder->return_code != 'SUCCESS') {
            die($unifiedOrder->return_msg);
        }
        if ($unifiedOrder->result_code != 'SUCCESS') {
            die($unifiedOrder->err_code);
        }
        $codeUrl = (array)($unifiedOrder->code_url);
        if(!$codeUrl[0]) exit('get code_url error');
        $arr = array(
            "appId" => $config['appid'],
            "timeStamp" => $timestamp,
            "nonceStr" => self::createNonceStr(),
            "package" => "prepay_id=" . $unifiedOrder->prepay_id,
            "signType" => 'MD5',
            "code_url" => $codeUrl[0],
        );
        $arr['paySign'] = self::getSign($arr, $config['key']);
        return $arr;
    }
    public function notify()
    {
        $postStr = file_get_contents('php://input');
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $postObj = json_decode(json_encode(simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if ($postObj === false) {
            die('parse xml error');
        }
        if ($postObj['return_code'] != 'SUCCESS') {
            die($postObj['return_msg']);
        }
        if ($postObj['result_code'] != 'SUCCESS') {
            die($postObj['err_code']);
        }
        $arr = (array)$postObj;

        $order_info = $this->orderquery($arr['out_trade_no']);
//        $log = APP_DIR . '/protected/data/log/data_0.txt';
//        file_put_contents($log, json_encode($order_info));
        if ($order_info['code'] == 0) {
            if (!empty($order_info['data'])) {
                if ($order_info['data'][0] == "SUCCESS") {
                    return [
                        'code' => true,
                        'order' => $order_info['data'][0]
                    ];
                }
            }
        }
        return [
            'code' => false
        ];
    }

    /**
     * 查看支付订单状态
     * @param $outTradeNo
     * @return mixed
     */
    public function orderquery($outTradeNo)
    {
        $config = array(
            'mch_id' => $this->config['mch_id'],
            'appid' => $this->config['appid'],
            'key' => $this->config['pay_key'],
        );
        //$orderName = iconv('GBK','UTF-8',$orderName);
        $unified = array(
            'appid' => $config['appid'],
            'mch_id' => $config['mch_id'],
            'out_trade_no' => $outTradeNo,
            'nonce_str' => self::createNonceStr(),
        );
        $unified['sign'] = self::getSign($unified, $config['key']);
        $responseXml = self::curlPost('https://api.mch.weixin.qq.com/pay/orderquery', self::arrayToXml($unified));
        $queryResult = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($queryResult === false) {
            die('parse xml error');
        }
        if ($queryResult->return_code != 'SUCCESS') {
            die($queryResult->return_msg);
        }
        $trade_state = $queryResult->trade_state;
        $data['code'] = $trade_state == 'SUCCESS' ? 0 : 1;
        $data['data'] = (array)$queryResult->attach;
        $data['msg'] = $this->getTradeSTate($trade_state);
        $data['time'] = date('Y-m-d H:i:s');
        return $data;
        exit();
    }

    public function getTradeSTate($str)
    {
        switch ($str) {
            case 'SUCCESS';
                return '支付成功';
                break;
            case 'REFUND';
                return '转入退款';
                break;
            case 'NOTPAY';
                return '未支付';
                break;
            case 'CLOSED';
                return '已关闭';
                break;
            case 'REVOKED';
                return '已撤销（刷卡支付）';
                break;
            case 'USERPAYING';
                return '用户支付中';
                break;
            case 'PAYERROR';
                return '支付失败';
                break;
            default:
                return '失败';
        }
    }


    /**
     * curl get
     *
     * @param string $url
     * @param array $options
     * @return mixed
     */
    public static function curlGet($url = '', $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    public static function curlPost($url = '', $postData = '', $options = array())
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    public static function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 获取签名
     * @param $params
     * @param $key
     * @return string
     */
    public static function getSign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatQueryParaMap($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }

    protected static function formatQueryParaMap($paraMap, $urlEncode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
}