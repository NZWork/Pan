<?php
/**
 * Created by PhpStorm.
 * User: jay
 * Date: 2017/1/11
 * Time: 下午6:34
 */

namespace App\Http\Commons;

class Curl
{
    /**
     * @param string $url
     * @return mixed|string
     */
    public static function get($url = '', $is_header = 0)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return '';
        }
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, $is_header);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        return $data;
    }

    /**
     * @param string $url
     * @param array $post_data
     * @return mixed|string
     */
    public static function post($url = '', $post_data = [], $is_header = 0)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return '';
        }
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, $is_header);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return $data;
    }
}