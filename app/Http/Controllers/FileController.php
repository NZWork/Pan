<?php
/**
 * Created by PhpStorm.
 * User: LiuWenJie
 * Date: 2017/3/15
 * Time: 10:26
 */

namespace App\Http\Controllers;

use App\Def;
use App\FileDir;
use App\FileShare;
use App\Http\Commons\XToken;
use App\Jobs\FileMerge;
use Storage;
use App\FileMap;
use App\Http\Commons\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

class FileController extends Controller
{

	private $UNIT = [
		0 => 'B',
		1 => 'KB',
		2 => 'MB',
		3 => 'GB'
	];

	public function index()
	{
		return view('file.upload');
	}

	/**
	 * 文件上传
	 * 软链 ln -s  storage public
	 */
	public function upload()
	{
		$user = Session::get('user');
		$file = Input::file('file');
		$_file = [
			'name' => $file->getClientOriginalName(),
			'ext'  => $file->getClientOriginalExtension()
		];
		$chunks = intval(Input::get('chunks'));
		$res = $this->_putTmp($user->uid, $_file['name'], $file->getRealPath(), $chunks);
		$path = Def::TMP_PATH . $user->uid . "/" . $_file['name'];
		if($chunks){    //分区上传
			if($res == 'merge'){    //文件合并
				$this->dispatch(new FileMerge($path, $chunks, $user->uid, $_file));
				return Response::json(200, [], "文件已提交队列处理");
			}
			return Response::json(200, ['file' => $_file['name'], 'chunks' => $chunks, 'chunk' => $res, 'test' => Input::get()], "文件分区模块");
		}
		return $this->_putStore($path, $_file, $user->uid);
	}

	/**
	 * 分享页下载
	 */
	public function getFileFromShare()
	{
		$token = Input::get('token');
		$info = FileShare::getByToken($token);
		$data = FileDir::getByCond(['id' => intval($info['dir_id']), 'uid' => intval($info['share_uid'])]);
		if(isset($data[0])){
			$num = intval($info['down_nums']) + 1;
			FileShare::setShare(['down_nums' => $num], ['id' => $info['id']]);
			$xtoken = XToken::encrypt([
				'id'    => intval($info['dir_id']),
				'uid'   => intval($info['uid']),
				'fid'   => intval($data[0]['file_map']),
				'name'  => $data[0]['name'],
				'share' => TRUE,
				'xauth' => Def::TIKI_API_XAUTH
			], FALSE);
			return redirect('/download?token=' . XToken::urlsafe_b64encode($xtoken['token']) . '&pubkey=' . $xtoken['pubkey']);
		}
		return view('errors.404');
	}


	/**
	 * 下载
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
	 */
	protected function download()
	{
		$data = XToken::decrypt(XToken::urlsafe_b64decode(Input::get('token')), Input::get('pubkey'), FALSE);
		$down = FALSE;
		if(isset($data->xauth) && $data->xauth === Def::TIKI_API_XAUTH){
			if(isset($data->share)){
				$info = FileShare::getByDir($data->id);
				if($info){
					$down = TRUE;
				}
			} else{
				$time = isset($data->time) ? $data->time : 0;
				$down = (time() - $time) < 60;
			}
			if($down && isset($data->fid)){    //下载连接实效性验证
				$fid = $data->fid;
				$file = FileMap::getById($fid);
				$path = Def::ROOT_PATH . '/' . Def::STORE_PATH . '/' . date('Ym//d//', strtotime($file->updated_at)) . $file->file_sha1;
				return response()->download($path, $data->name);
			}
		}
		return view('errors.404');
	}

	/**
	 * 文件存储
	 * @param     $path
	 * @param     $file
	 * @param int $uid
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function _putStore($path, $file, $uid = 0, $response = TRUE)
	{
		//$fileName = sha1_file($path);
		$fileName = md5_file(Def::ROOT_PATH . $path);
		$res = FileMap::getIdBySha1($fileName);
		if($res){    //文件已存在
			$prePath = date('Ym', strtotime($res->updated_at)) . '/' . date('d', strtotime($res->updated_at)) . '/';
			$res = $this->_fileToDbDir($res->id, $uid, $fileName, $file, $prePath);
		} else{
			$prePath = date('Ym', time()) . '/' . date('d', time()) . '/';
			Storage::disk('root')->move($path, Def::STORE_PATH . $prePath . $fileName);
			$res = $this->_fileToDbMap($uid, $fileName, $file, $prePath);
		}
		if($response){
			return Response::json(200, ['file' => $file['name']], $res ? "文件上传成功" : "文件上传失败");
		}
	}

	/**
	 * 分块上传 临时存储
	 * @param        $uid
	 * @param        $fileName
	 * @param string $realPath
	 * @param int    $chunks
	 * @return mixed
	 */
	private function _putTmp($uid, $fileName, $realPath, $chunks)
	{
		$chunk = Input::get('chunk');
		$f_path = $chunks ? "$uid/$fileName" . "_$chunk.part" : "$uid/$fileName";
		$res = Storage::disk('tmp')->put($f_path, file_get_contents($realPath));
		for($index = 0; $index < $chunks; $index++){    //文件遗漏检查
			if(!Storage::disk('tmp')->exists("$uid/$fileName" . "_$index.part")){
				return $chunk;
			}
		}
		return 'merge';
	}

	/**
	 * 文件合并
	 * @param int $chunks
	 * @param     $path
	 * @return bool|mixed
	 */
	public function _fileMerge($path, $chunks)
	{
		$out = fopen($path, "wb");
		if(flock($out, LOCK_EX)){        //文件拼接
			for($index = 0; $index < $chunks; $index++){
				if(!$in = fopen($path . "_$index.part", "rb")){
					break;
				}
				while($buff = fread($in, 4096)){
					fwrite($out, $buff);
				}
				fclose($in);
				unlink($path . "_$index.part");    //回收tmp空间
			}
			flock($out, LOCK_UN);
		}
		fclose($out);
		return $path;
	}

	/**
	 * 数据库文件映射
	 * @param $uid
	 * @param $sha1
	 * @param $file
	 * @param $prePath
	 * @return array|bool
	 */
	private function _fileToDbMap($uid, $sha1, $file, $prePath)
	{
		$id = FileMap::saveMap($uid, $sha1);
		if(!$id){
			return FALSE;
		}
		return $this->_fileToDbDir($id, $uid, $sha1, $file, $prePath);
	}

	/**
	 * 更新目录结构
	 * @param $id
	 * @param $uid
	 * @param $sha1
	 * @param $file
	 * @param $prePath
	 * @return array
	 */
	private function _fileToDbDir($id, $uid, $sha1, $file, $prePath)
	{
		$size = Storage::disk('store')->size($prePath . $sha1);
		$data = [
			'pre'      => Session::get('path') ?: 0,
			'name'     => $file['name'],
			'uid'      => $uid,
			'type'     => FileDir::getType($file['ext']),
			'size'     => $this->_fileSize($size),
			'file_map' => $id
		];
		return FileDir::saveDir($data);
	}

	/**
	 * 文件单位换算
	 * @param $size
	 * @return int|string
	 */
	private function _fileSize($size)
	{
		$size = intval($size);
		$unit = 0;
		while($size > 1024){
			$size /= 1024;
			$unit++;
		}
		$size = round($size, 2) . ' ' . $this->UNIT[$unit];
		return $size;
	}
}