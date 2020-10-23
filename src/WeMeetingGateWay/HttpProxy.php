<?php

namespace WeMeetingGateWay;

use http\Exception;
use WeMeetingGateWay\Credential;

class HttpProxy
{
    private $_url = "";
    private $_method = "";
    private $_curl_host = "";
    private $_curl_port = 0;
    private $_header = array();
    private $_post_data = "";

    const ALLOW_METHODS = array('GET', 'POST', 'DELETE', 'PUT');
    private $_appid = "";

    public function __construct($appid)
    {
        $this->_appid = $appid;
        $this->_header = array(
            'AppId' => $this->_appid,
        );
    }

    public function setUrl($url)
    {
        $this->_url = $url;
        return $this;
    }

    public function setTimeOut($timeout)
    {
        $timeout = (int) $timeout;
        if($timeout < 0 || $timeout > 30){
            throw new \Exception("超时时间不合法");
        }
        $this->_timeout = $timeout;
        return $this;
    }

    public function setMethod($method)
    {
        $method = strtoupper($method);
        if (!in_array($method, self::ALLOW_METHODS)) {
            throw new \Exception("不支持的方法");
        }
        $this->_method = $method;
        return $this;
    }

    public function setHeader($header)
    {
        $this->_header = array_merge($header,$this->_header);
        return $this;
    }
    public function setPostJson($json_string)
    {
        $this->_post_data = $json_string;
        return $this;
    }

    public function setCurlProxy($host, $port = 80)
    {
        $this->_curl_host = $host;
        $this->_curl_port = $port;
        return $this;
    }

    private function sendRequest()
    {
        $curl_opts = array(
            CURLOPT_TIMEOUT => $this->_timeout,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        );
        foreach($this->_header as $k=> $v){
            $curl_opts['CURLOPT_HTTPHEADER'][] = $k.": ".$v;
        }
        $curl_opts[CURLOPT_URL] = $this->_url;

        if($this->_method == 'POST' || $this->_method == 'PUT'){
            $curl_opts['CURLOPT_HTTPHEADER'][] = array('Content-Type: application/json');
            if($this->_post_data){
                $curl_opts['CURLOPT_POSTFIELDS'] = $this->_post_data;
            }
        }
        switch ($this->_method){
            case 'POST':
                $curl_opts[CURLOPT_POST] = 1;
                break;
            case 'PUT':
                $curl_opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
                break;
            case 'DELETE':
                $curl_opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $curl_opts);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if(!empty($error)){
            throw new \Exception($error);
        }
        return $response;
    }
}
