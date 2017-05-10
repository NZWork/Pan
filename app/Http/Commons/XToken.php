<?php
/**
 * Created by PhpStorm.
 * User: jay
 * Date: 2017/4/9
 * Time: 下午9:58
 */

namespace App\Http\Commons;

use RedisDB;
use App\Def;

class XToken
{
	/**
	 * XToken 加密
	 * @param        $data
	 * @param string $key
	 * @param bool   $setRedis
	 * @return string
	 */
	static public function encrypt($data, $setRedis = TRUE, $key = Def::PAN_XTOKEN)
	{
		$code = '';
		$key = md5($key);
		$pubkey = self::uuid();
		if(is_array($data)){
			$data = json_encode($data, JSON_UNESCAPED_UNICODE);
		}
		$str_len = strlen($data);
		$key_len = strlen($key);
		$pkey_len = strlen($pubkey);
		for($i = 0; $i < $str_len; $i++){
			$code .= chr((ord($data[$i]) + ord($key[$i % $key_len]) + ord($pubkey[$i % $pkey_len])) % 256);
		}
		$code = base64_encode($code);
		if($setRedis){
			RedisDB::SADD(Def::REDIS_PAN_XTOKEN_SETS, $code);
		}
		return [
			'token'  => $code,
			'pubkey' => $pubkey
		];
	}

	/**
	 * XToken 解密
	 * @param        $code
	 * @param        $pubkey
	 * @param bool   $verifyRedis
	 * @param string $key
	 * @return array|string
	 */
	static public function decrypt($code, $pubkey, $verifyRedis = TRUE, $key = Def::PAN_XTOKEN)
	{
		if($verifyRedis){
			if(!RedisDB::SISMEMBER(Def::REDIS_PAN_XTOKEN_SETS, $code)){
				return [];
			}
			RedisDB::SREM(Def::REDIS_PAN_XTOKEN_SETS, $code);
		}
		$data = '';
		$key = md5($key);
		$code = base64_decode($code);
		$key_len = strlen($key);
		$code_len = strlen($code);
		$pkey_len = strlen($pubkey);
		for($i = 0; $i < $code_len; $i++){
			$data .= chr((ord($code[$i]) + 512 - ord($key[$i % $key_len]) - ord($pubkey[$i % $pkey_len])) % 256);
		}
		return json_decode($data) ?: $data;
	}

	/**
	 * 生成UUID
	 * @param string $prefix
	 * @return string
	 */
	static public function uuid($prefix = '')
	{
		$str = md5(uniqid(md5(microtime(TRUE)), TRUE));
		$uuid = substr($str, 0, 8) . '-';
		$uuid .= substr($str, 8, 8) . '-';
		$uuid .= substr($str, 16, 8) . '-';
		$uuid .= substr($str, 24, 8);
		return strtoupper($prefix . $uuid);
	}

	/**
	 * base64 URL 传输处理
	 * @param $string
	 * @return mixed|string
	 */
	static public function urlsafe_b64encode($string)
	{
		$data = base64_encode($string);
		$data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
		return $data;
	}

	/**
	 * @param $string
	 * @return bool|string
	 */
	static public function urlsafe_b64decode($string)
	{
		$data = str_replace(array('-', '_'), array('+', '/'), $string);
		$mod4 = strlen($data) % 4;
		if($mod4){
			$data .= substr('====', $mod4);
		}
		return base64_decode($data);
	}
}