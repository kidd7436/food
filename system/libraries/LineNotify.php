<?php if(!defined('DEVIL_SYS_CORE_PATH')) exit('No direct script access allowed');
/**
  @brief        LineNotify Controller ( 訊息推播 )
**/
class LineNotify
{
    /**
     * LINE Notify API
    **/
    public function myRoute($_message = '', $_intro = '')
    {
        switch($_intro) {
            default:
                $this->accessToken = 'vPijpHrHa8brsKosaqozR7Y0dJgF61StzP3tepeWdFw';
                break;
        }
        // 觸發訊息
        self::_lineNotify($_message);
    }

    /**
     * LINE Notify API
    **/
    private function _lineNotify($_message = '')
    {
        // 初始化 curl
        $ch = curl_init();
        // 表头
        $header = array(
            'Accept: application/json',
            // 'Content-Type: application/form-data',
            'Authorization: Bearer '.$this->accessToken
        );
        curl_setopt($ch, CURLOPT_URL, "https://notify-api.line.me/api/notify");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array("message" => preg_replace('/<br\\s*?\/??>/i',"\n"," - ".DEVIL_APP_PROJECT_NAME."\n".$_message))));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        // 回传结果内容
        $result = curl_exec($ch);
        // 无法通过 connect() 连接至主机或代理服务器
        if(curl_error($ch) != '') {
            //
            curl_close($ch);
        }else{
            // 解开LINE回传讯息
            $_responDataArray = json_decode($result, true);
            // 有错误的话
            if($_responDataArray['status'] != 200) {
                //
            }
            curl_close($ch);
        }
    }
}