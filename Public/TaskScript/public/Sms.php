<?php 
class Sms
{

/**
 * 发送手机验证码
 * @param array  $mobile 手机号
 * @param string $msg 短信内容
 * @return array
 */
public function sendSms($mobile,$msg)
{
    //判断数据是否完整
    if (!$mobile || count($mobile) < 1) {
        $res = array(
            "Code"  => false,
            "Msg"   => "请输入手机号码！"
        );
        return $res;
    }
    
    if (!$msg) {
		$res = array(
            "Code"  => false,
            "Msg"   => "内容不能为空"
        );
        return $res;
    }

    $mobileinfo = array();
    foreach ($mobile as $key => $value) {
    	$mobileinfo[$key]['content'] = $msg;
    	$mobileinfo[$key]['phone']   = $value;
    }
    /**
     * 进行SMS接口对接
     */
    $sms        = false;
    $user       = '200990';
    $password   = 'cywl20170712';
    $seqid      = date("ymdHis").rand(100000, 999999);
    $send       = array(
        "id"        => $seqid,
        "method"    => "send",
        "params"    => array(
            "userid"    => $user,
            "seqid"     => $seqid,
            "sign"      => md5($seqid.md5($password)),
            "submit"    => $mobileinfo
        )
    );

    //发送SMS请求
    $res = $this->curl_post("https://112.74.139.4:8008/sms3_api/jsonapi/jsonrpc2.jsp", json_encode($send));
    $Res = json_decode($res, true);

    if($Res["result"][0]["return"] === "0"){
        $sms = true;
    }

    //是否请求发送成功
    if ($sms) {
        $res = array(
            "Code"  => true,
            "Msg"   => "发送成功！"
        );
    } else {
        $res = array(
            "Code"  => false,
            "Msg"   => "发送失败！"
        );
    }
    return $res;
}

/**
 * CURL模拟POST请求
 * @param $url
 * @param $params
 * @param int $timeout
 * @return mixed
 */
public function curl_post($url, $params, $timeout = 5) {
    $ch = curl_init();
    $header = array(
        'Content-Type: application/x-www-form-urlencoded',
    );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //定义请求类型
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_POST, true);
    if (strpos(strtolower($url), "https://") !== false) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    $returnTransfer = curl_exec($ch);
    curl_close($ch);
    return $returnTransfer;
}

}

?>