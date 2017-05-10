<?php
/**
 * Created by PhpStorm.
 * User: jay
 * Date: 2017/1/15
 * Time: 下午1:56
 */

namespace App\Http\Controllers;

use App\Http\Commons\Response;
use Illuminate\Support\Facades\Session;

/**
 *
 */
class UserController extends Controller
{
	public function index()
	{
		dump(Session::get('user'));
	}

	public function logout()
	{
		Session::flush();
		return redirect('/login');
	}
}