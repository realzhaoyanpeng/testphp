<?php
namespace tencentmeetingapi;

class SignatureUtil
{
    private static $secret_id = '';  // SecretId
    private static $secret_key = ''; // SecretKey

    /**
     * 设置secretId和secretKey
     * @param $secret_id
     * @param $secret_key
     */
    public static function setSecretIdAndKey($secret_id, $secret_key)
    {
        self::$secret_id = $secret_id;
        self::$secret_key = $secret_key;
    }

    public static function getSecretId()
    {
        return self::$secret_id;
    }

    /**
     * @param $header = [
     * 'X-TC-Nonce' => '',  // X-TC-Nonce请求头，随机数
     * 'X-TC-Timestamp' => '', // X-TC-Timestamp请求头，当前时间的秒级时间戳
     * 'URI' => ''  //请求uri，eg：/v1/meetings,
     * ];
     * @param $body   请求体，没有的设为空串
     * @param $method  请求方式 字符串 "POST","GET"
     */
    public static function sign($header, $body, $method)
    {
        if (empty(self::$secret_id) || empty(self::$secret_key)) {
            throw new Exception("缺少secretId或者secretKey");
        }
        $secret_id = self::$secret_id;
        $header_string = "X-TC-Key={$secret_id}&X-TC-Nonce={$header['X-TC-Nonce']}&X-TC-Timestamp={$header['X-TC-Timestamp']}";
        $str_to_sign = "{$method}\n{$header_string}\n{$header['URI']}\n{$body}";
        $hash = hash_hmac('sha256', $str_to_sign, self::$secret_key);
        return base64_encode($hash);
    }
}