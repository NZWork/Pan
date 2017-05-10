<?php

/**
 * Created by PhpStorm.
 * User: jay
 * Date: 2017/4/8
 * Time: 上午10:07
 */

namespace App;

class Def
{

	const ROOT_PATH = 'storage/';

	const TMP_PATH = 'tmp/';

	const STORE_PATH = 'store/';

	const ZIP_PATH = 'zip/';

	const PAN_XTOKEN = 'tiki_pan_xtoken_key';

	const REDIS_PAN_XTOKEN_SETS = 'pan_xtoken_sets';

	const SHARE_URL_PRE = 'https://pan.tiki.im/share?token=';

	const API_TIKI_PRE = 'https://app.dev.tiki.im';

	const TIKI_API_XAUTH = '5DE0CB6960FDD55B9F7C26E6554617B5';        //接口中间件校验 md5(马越)

}