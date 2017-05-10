<?php
/**
 * Created by PhpStorm.
 * User: LiuWenJie
 * Date: 2017/3/15
 * Time: 16:32
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class FileMap extends Model
{
	protected $table = 'file_map';

	/**
	 * 添加更新文件记录
	 * @param     $uid
	 * @param     $sha1
	 * @param int $id
	 * @return array
	 */
	protected function saveMap($uid, $sha1, $id = 0)
	{
		$uid = intval($uid);
		if(empty($uid) || empty($sha1)){
			return [];
		}
		$data = [
			'first_uid' => $uid,
			'file_sha1' => $sha1,
		];
		if($id){
			return $this->where(['id' => $id])->update($data);
		}
		return $this->insertGetId($data);
	}

	/**
	 * GET BY SHA1
	 * @param $sha1
	 * @return array
	 */
	protected function getIdBySha1($sha1)
	{
		if(empty($sha1)){
			return [];
		}
		$data = $this->where(['file_sha1' => $sha1])->first();
		return $data;
	}

	/**
	 * @param $id
	 * @return array
	 */
	protected function getById($id)
	{
		$id = intval($id);
		if(empty($id)){
			return [];
		}
		return $this->where(['id' => $id])->first();
	}
}