<?php

/**
 * Created by PhpStorm.
 * User: jay
 * Date: 2017/1/4
 * Time: 下午5:27
 */
namespace App\Http\Commons;

class Response
{
	/**
	 * 封装Response
	 * @param int    $code
	 * @param array  $data
	 * @param string $msg
	 * @param int    $httpCode
	 * @return \Illuminate\Http\JsonResponse
	 */
	public static function json($code = 200, $data = [], $msg = '', $httpCode = 200)
	{
		return response()->json(array(
			'code'   => $code,
			'result' => $data,
			'msg'    => empty($msg) ? trans($code) : $msg,
		), $httpCode, [], JSON_UNESCAPED_UNICODE);
	}
}