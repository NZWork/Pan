<?php
/**
 * Created by PhpStorm.
 * User: LiuWenJie
 * Date: 2017/3/16
 * Time: 12:41
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class FileDir extends Model
{
	protected $table = 'file_directory';

	CONST NORMAL = 1;

	const SHARE_OPEN = 1;
	const SHARE_CLOSE = 0;
	/**
	 * 文件类型转换
	 * @var array
	 */
	CONST FILE_FOLDER = 0;
	CONST FILE_OTHER = 1;
	CONST FILE_TEST = 2;
	CONST FILE_IMAGE = 3;
	CONST FILE_VIDEO = 4;
	CONST FILE_BT = 5;
	public static $fileType = [
		0 => 'fa-folder-o',
		1 => 'fa-at',
		2 => 'fa-file-text',
		3 => 'fa-file-image-o',
		4 => 'fa-file-video-o',
		5 => 'fa-file-zip-o'
	];

	/**
	 * 添加更新文件信息
	 * @param     $data
	 * @param int $id
	 * @return array
	 */
	protected function saveDir($data = [], $id = 0)
	{
		if(empty($data)){
			return [];
		}
		$id = intval($id);
		//重名处理
		$info = $this->where(['name' => $data['name'], 'pre' => $data['pre'], 'type' => $data['type']])->first();
		if($info){
			$data['name'] = $data['name'] . '(' . date('m/d H:i:s') . ')';
		}
		if($id){
			return $this->where(['id' => $id])->update($data);
		}
		return $this->insertGetId($data);
	}

	/**
	 * 找儿子们 获取用户目录下的文件
	 * @param int $pre
	 * @param int $uid
	 * @return array
	 */
	protected function getFileList($pre = 0, $uid = 0)
	{
		$pre = intval($pre);
		$uid = intval($uid);
		if(empty($uid)){
			return [];
		}
		$cond = [
			'uid'    => $uid,
			'pre'    => $pre,
			'status' => self::NORMAL
		];
		return $this->select(['id', 'uid', 'pre', 'name', 'type', 'size', 'share','updated_at'])->where($cond)->get();
	}

	/**
	 * 找爸爸
	 * @param int $pre
	 * @param int $uid
	 * @return array|int
	 */
	protected function getIdByPre($pre = 0, $uid = 0)
	{
		$pre = intval($pre);
		$uid = intval($uid);
		if(empty($uid)){
			return [];
		}
		$cond = [
			'uid'    => $uid,
			'id'     => $pre,
			'status' => self::NORMAL
		];
		$data = $this->where($cond)->first();
		return $data ? $data->pre : 0;
	}

	/**
	 * 按分类获取数据
	 * @param int $type
	 * @param int $uid
	 * @return array
	 */
	protected function getIdByType($type = 0, $uid = 0)
	{
		$type = intval($type);
		$uid = intval($uid);
		if(empty($uid)){
			return [];
		}
		$cond = [
			'uid'    => $uid,
			'type'   => $type,
			'status' => self::NORMAL
		];
		return $this->where($cond)->get();
	}

	/**
	 * @param $uid
	 * @param $like
	 * @return array
	 */
	protected function getByNameLike($uid, $like)
	{
		if(empty($like)){
			return [];
		}
		return $this->where('name', 'like', '%' . $like . '%')
			->where('type', '!=', 0)
			->where(['uid' => $uid, 'status' => self::NORMAL])->get();
	}

	/**
	 * 获取id下的记录
	 * @param $cond
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

	/**
	 * 获取网盘记录
	 * @param $cond
	 * @return array
	 */
	protected function getByCond($cond = [])
	{
		if(empty($cond)){
			return [];
		}
		if(!isset($cond['status'])){
			$cond['status'] = self::NORMAL;
		}
		return $this->where($cond)->get();
	}

	/**
	 * @param array $cond
	 * @return array
	 */
	protected function delByCond($cond = [])
	{
		if(empty($cond)){
			return [];
		}
		return $this->where($cond)->delete();
	}

	/**
	 * 分享状态更新
	 * @param int $id
	 * @param int $status
	 * @return array
	 */
	protected function setShare($id = 0, $share = 1)
	{
		$id = intval($id);
		if(empty($id)){
			return [];
		}
		$cond = ['id' => $id];
		return $this->where($cond)->update(['share' => $share]);
	}

	/**
	 * 文件类型匹配
	 * @param $ext
	 * @return int
	 */
	protected function getType($ext)
	{
		switch($ext){
			case 'txt':
			case 'html':
			case 'htm':
			case 'php':
			case 'js':
			case 'css':
			case 'doc':
			case 'docx':
			case 'ppt':
			case 'pptx':
			case 'wps':
			case 'pdf':
				return FileDir::FILE_TEST;    //文本类型
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'bmp':
			case 'gif':
			case 'ico':
			case 'ppm':
			case 'psd':
			case 'gifv':
				return FileDir::FILE_IMAGE;    //图片类型
			case 'mp4':
			case 'avi':
			case 'mov':
			case 'rmvb':
			case 'wmv':
			case 'mkv':
			case 'flv':
			case 'navi':
			case '3gp':
				return FileDir::FILE_VIDEO;    //视频类型
			case 'bt':
			case 'ace':
			case 'apk':
			case 'jar':
			case 'gzip':
			case 'lzma':
			case 'gz':
			case 'rar':
			case 'tar':
			case 'zip':
			case 'tz':
			case '7z':
				return FileDir::FILE_BT;    //资源格式
			default:
				return FileDir::FILE_OTHER;    //其他格式
		}
	}

}