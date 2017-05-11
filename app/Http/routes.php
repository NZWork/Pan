<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/


Route::group(['middleware' => ['web']], function () {
	Route::get('/login', function () {
		if(\Illuminate\Support\Facades\Session::get('user')){
			return redirect('/');
		}
		//return view('login');
		return redirect('https://oauth.tiki.im/auth?response_type=code&redirect_uri=https://app.dev.tiki.im/login/callback&client_id=test&state=2');
	});

	Route::get('/share', 'CenterController@share');						//分享页
	Route::get('/getShareFile', 'FileController@getFileFromShare');		//分享下载

	Route::get('/download', 'FileController@download');					//下载

});

/**
 * 登录中间件限制
 */
Route::group(['middleware' => ['web', 'login']], function () {
	Route::get('/logout', 'UserController@logout');

	Route::get('/', 'CenterController@index');
	Route::post('/addFolder', 'CenterController@newFolder');			//新建文件夹
	Route::get('/openFolder', 'CenterController@getDataByOpen');		//打开目录
	Route::get('/closeFolder', 'CenterController@getDataByClose');		//返回上层
	Route::get('/delete', 'CenterController@delDir');					//删除
	Route::get('/getFile', 'CenterController@getFile');					//用户下载个人文件
	Route::post('/dels', 'CenterController@delDirs');					//批量删除
	Route::post('/downFiles', 'CenterController@zipDownFiles');			//批量下载
	Route::post('/shareFiles', 'CenterController@shareFiles');			//批量分享
	Route::post('/rename', 'CenterController@rename');					//重命名
	Route::post('/getShare', 'CenterController@shareUrl');				//获取分享地址
	Route::post('/shareClose', 'CenterController@shareClose');			//关闭分享
	Route::post('/shareLike', 'CenterController@shareLike');			//分享点赞
	Route::post('/shareReport', 'CenterController@shareReport');		//分享举报


	Route::get('/upload', 'FileController@index');						//上传页
	Route::post('/upload', 'FileController@upload');					//上传操作

});
