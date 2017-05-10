<?php
/**
 * Created by PhpStorm.
 * User: jay
 * Date: 2017/1/17
 * Time: 下午7:40
 */

namespace App\Http\Controllers;

use ZipArchive;
use App\Def;
use App\FileDir;
use App\FileShare;
use App\Http\Commons\Curl;
use App\Http\Commons\Response;
use App\Http\Commons\XToken;
use App\User;
use App\FileMap;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

class CenterController extends Controller
{
	/**
	 * 首页数据
	 * @return $this
	 */
	public function index()
	{
		$user = Session::get('user');
		$type = Input::get('type');
		$search = Input::get('search');
		$path_pre = intval(Input::get('pre'));
		if($type){        //分类
			$data = FileDir::getIdByType($type, $user->uid);
		} elseif($search){        //搜索
			$data = FileDir::getByNameLike($user->uid, $search);
		} else{        //目录
			Session::put('path', $path_pre);
			$data = FileDir::getFileList($path_pre, $user->uid);
		}
		return view('pan.index')->with([
			'title'  => 'Tiki-C盘',
			'user'   => $user,
			'data'   => $data,
			'type'   => FileDir::$fileType,
			'search' => strval($search),
			'pre'    => $path_pre
		]);
	}

	/**
	 * 分享页
	 * @return $this
	 */
	public function share()
	{
		$user = Session::get('user');
		$token = Input::get('token');
		$info = FileShare::getByToken($token);
		if(empty($info)){
			return view('errors.404');
		}
		$post_data = ['xauth' => Def::TIKI_API_XAUTH, 'uid' => $info['share_uid']];
		$from_user = json_decode(Curl::post(Def::API_TIKI_PRE . '/api/nz/user', $post_data));
		$data = [
			'file_name'   => $info['file_name'],
			'file_size'   => $info['file_size'],
			'file_type'   => $info['file_type'],
			'time'        => $info['created_at'],
			'user'        => $from_user->result->name,
			'token'       => $token,
			'like_nums'   => $info['like_nums'],
			'report_nums' => $info['report_nums'],
			'down_nums'   => $info['down_nums'],
		];
		return view('file.share')->with([
			'title' => 'Tiki-分享页',
			'user'  => $user,
			'data'  => $data,
		]);
	}

	/**
	 * 获取分享地址
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function shareUrl()
	{
		$id = Input::get('id');
		$user = Session::get('user');
		$file = FileDir::getById($id);
		if($user->uid == $file['uid']){
			$data = FileShare::getByDir($id);
			if(empty($data)){
				$token = (XToken::urlsafe_b64encode(base64_decode(XToken::uuid($user->uid . '_'))));
				$data = [
					'dir_id'    => $id,
					'share_uid' => $user->uid,
					'token'     => $token,
					'file_size' => $file['size'],
					'file_name' => $file['name'],
					'file_type' => $file['type']
				];
				$res = FileShare::setShare($data);
			}
			return Response::json(200, ['url' => (Def::SHARE_URL_PRE . $data['token'])], '分享标识');
		}
		return Response::json(400, [], '分享地址异常');
	}

	/**
	 * 新建文件夹
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function newFolder()
	{
		$user = Session::get('user');
		$name = Input::get('folder') ?: '新建文件夹';
		$pre = Session::get('path') ?: 0;
		$data = [
			'uid'  => $user->uid,
			'type' => FileDir::FILE_FOLDER,
			'name' => $name,
			'pre'  => $pre,
		];
		$res = FileDir::saveDir($data);
		return Response::json(200, ['pre' => $pre, 'folder' => $name], $res ? '目录创建成功' : '目录创建失败');
	}

	/**
	 * Pjax 打开目录数据
	 * @return $this
	 */
	public function getDataByOpen()
	{
		$user = Session::get('user');
		$path_pre = Input::get('pre') ?: Session::get('path');
		Session::put('path', $path_pre);
		$data = FileDir::getFileList($path_pre, $user->uid);
		return view('pan.index')->with([
			'title' => 'Tiki-C盘',
			'user'  => $user,
			'data'  => $data,
			'type'  => FileDir::$fileType,
			'pre'   => $path_pre
		]);
	}

	/**
	 * Pjax 上层目录
	 * @return $this
	 */
	public function getDataByClose()
	{
		$user = Session::get('user');
		$path_pre = Session::get('path') ?: 0;
		if($path_pre){
			$path_pre = FileDir::getIdByPre($path_pre, $user->uid);
		}
		Session::put('path', $path_pre);
		$data = FileDir::getFileList($path_pre, $user->uid);
		return view('pan.index')->with([
			'title' => 'Tiki-C盘',
			'user'  => $user,
			'data'  => $data,
			'type'  => FileDir::$fileType,
			'pre'   => $path_pre
		]);
	}

	/**
	 * Pjax 删除文件／目录
	 * @return $this|\Illuminate\Http\JsonResponse
	 */
	public function delDir()
	{
		$user = Session::get('user');
		$path = Session::get('path');
		$id = Input::get('id');
		$info = FileDir::getById($id);
		if($info && $info->uid == $user->uid){    //删除处理
			$count = 0;
			if($info->type == FileDir::FILE_FOLDER){    //文件夹内文件删除
				$count = $this->_delFolder($id);
			} else{
				//同步去除分享
				FileShare::setShare(['status' => FileShare::STAT_CLOSE, 'fid' => $info->file_map], ['dir_id' => $id]);
			}
			$count += FileDir::delByCond(['id' => $id]);
		}
		$data = FileDir::getFileList($path, $user->uid);
		return view('pan.index')->with([
			'title' => 'Tiki-C盘',
			'user'  => $user,
			'data'  => $data,
			'type'  => FileDir::$fileType,
			'pre'   => $path
		]);
	}

	/**
	 * 批量删除
	 * @return $this
	 */
	public function delDirs()
	{
		$user = Session::get('user');
		$ids = Input::get('ids') ?: [];
		$count = 0;
		foreach($ids as $id){
			$info = FileDir::getById($id);
			if($info && $info->uid == $user->uid){    //删除处理
				if($info->type == FileDir::FILE_FOLDER){    //文件夹内文件删除
					$count = $this->_delFolder($id);
				} else{
					//同步去除分享
					FileShare::setShare(['status' => FileShare::STAT_CLOSE, 'fid' => $info->file_map], ['dir_id' => $id]);
				}
				$count += FileDir::delByCond(['id' => $id]);
			}
		}
		return Response::json(200, ['count' => $count], '文件删除成功');
	}

	/**
	 * 用户个人文件下载
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function getFile()
	{
		$id = Input::get('id');
		$user = Session::get('user');
		$data = FileDir::getByCond(['id' => $id, 'uid' => $user->uid]);
		if(isset($data[0]) && isset($user->uid)){
			$xtoken = XToken::encrypt([
				'id'    => intval($id),
				'uid'   => intval($user->uid),
				'fid'   => intval($data[0]['file_map']),
				'time'  => time(),
				'name'  => $data[0]['name'],
				'xauth' => Def::TIKI_API_XAUTH
			], FALSE);
			return redirect('/download?token=' . XToken::urlsafe_b64encode($xtoken['token']) . '&pubkey=' . $xtoken['pubkey']);
		}
		return view('errors.404');
	}

	/**
	 * zip压缩多文件下载方式
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
	 */
	public function zipDownFiles()
	{
		$user = Session::get('user');
		$ids = Input::get('ids') ?: [];
		$count = 0;
		$zip_name = 'Tiki_FileDown_'.time().'.zip';
		$zip_path = Def::ZIP_PATH . $user->uid . '_';
		$zip = new ZipArchive;
		$zip->open($zip_path . $zip_name, ZipArchive::CREATE);
		foreach($ids as $id){
			$data = FileDir::getByCond(['id' => $id, 'uid' => $user->uid]);
			if(isset($data[0]) && isset($user->uid)){
				$file = FileMap::getById(intval($data[0]['file_map']));
				$path = Def::ROOT_PATH . '/' . Def::STORE_PATH . '/' . date('Ym//d//', strtotime($file->updated_at)) . $file->file_sha1;
				$zip->addFile($path,$data[0]['name']);
				$count++;
			}
		}
		$zip->close();
		return response()->download($zip_path . $zip_name, $zip_name);
	}

	/**
	 * 重命名
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function rename()
	{
		$user = Session::get('user');
		$id = Input::get('id');
		$name = Input::get('name');
		if($id && $name){
			$info = FileDir::getByCond(['id' => $id, 'uid' => $user->uid]);
			if(isset($info[0])){
				$ext = explode('.', $name);
				$ext = end($ext);
				$data = [
					'name' => $name,
					'type' => $info[0]['type'] == FileDir::FILE_FOLDER ?
						FileDir::FILE_FOLDER : FileDir::getType($ext),
					'pre'  => Session::get('path'),
				];
				$res = FileDir::saveDir($data, $id);
				if($res !== FALSE){
					return Response::json(200, ['name' => $name], '文件重命名');
				}
			}
		}
		return Response::json(200, [], '文件重命名操作失败');
	}

	/**
	 * 分享文件点赞
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function shareLike()
	{
		$token = Input::get('token');
		$info = FileShare::getByToken($token);
		if($info){
			$num = intval($info['like_nums']) + 1;
			$res = FileShare::setShare(['like_nums' => $num], ['id' => $info['id']]);
			if($res){
				return Response::json(200, ['num' => $num], '谢谢您的点赞');
			}
		}
		return Response::json(400, [], '系统在瑟瑟发抖，请稍后重试');
	}

	/**
	 * 分享文件举报
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function shareReport()
	{
		$token = Input::get('token');
		$info = FileShare::getByToken($token);
		if($info){
			$num = intval($info['report_nums']) + 1;
			$res = FileShare::setShare(['report_nums' => $num], ['id' => $info['id']]);
			if($res){
				return Response::json(200, ['num' => $num], '感谢您的反馈');
			}
		}
		return Response::json(400, [], '系统在瑟瑟发抖，请稍后重试');
	}

	/**
	 * 目录递归清除
	 * @param $id
	 * @return array|int
	 */
	private function _delFolder($id)
	{
		$data = FileDir::getByCond(['pre' => $id, 'type' => FileDir::FILE_FOLDER]);
		$count = 0;
		foreach($data as $item){
			//同步去除分享
			FileShare::setShare(['status' => FileShare::STAT_CLOSE, 'fid' => $item->file_map], ['dir_id' => $item->id]);
			$count += $this->_delFolder($item->id);        //清除子目录
		}
		$count += FileDir::delByCond(['pre' => $id]);    //清除当前目录
		return $count;
	}
}