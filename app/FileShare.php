<?php
/**
 * Created by PhpStorm.
 * User: LiuWenJie
 * Date: 2017/3/15
 * Time: 16:32
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class FileShare extends Model
{
	protected $table = 'file_share';

	const STAT_CLOSE = 0;
	const STAT_NORMAL = 1;

	/**
	 * 更具Id获取文件信息
	 * @param string $id
	 * @return array
	 */
	protected function get($id = 0)
	{
		if(empty($id)){
			return [];
		}
		$cond = ['id' => $id, 'status' => self::STAT_NORMAL];
		return $this->where($cond)->first();
	}

	/**
	 * 更具token获取文件信息
	 * @param string $token
	 * @return array
	 */
	protected function getByToken($token = '')
	{
		if(empty($token)){
			return [];
		}
		$cond = ['token' => $token, 'status' => self::STAT_NORMAL];
		return $this->where($cond)->first();
	}

	/**
	 * 根据文件获取分享数据
	 * @param int $id
	 * @return array
	 */
	protected function getByDir($id = 0)
	{
		$id = intval($id);
		if((empty($id))){
			return [];
		}
		$cond = ['id' => $id];
		return $this->where($cond)->first();
	}

	/**
	 * 分享记录数据写入更新
	 * @param array $data
	 * @param array $cond
	 * @return array
	 */
	protected function setShare($data = [], $cond = [])
	{
		if(empty($data)){
			return [];
		}
		if(empty($cond)){
			return $this->insertGetId($data);
		}
		return $this->where($cond)->update($data);
	}
}