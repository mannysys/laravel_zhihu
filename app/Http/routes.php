<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*封装数据分页（$page第几页页数，$limit每页显示多少数据）*/
function paginate($page = 1, $limit = 16)
{
    $limit = $limit ?:16;
    $skip = ($page ? $page - 1 : 0) * $limit;
    return [$limit, $skip];
}
/*封装返回错误和结果数据*/
function err($msg = null)
{
    return ['status'=> 0, 'msg'=> $msg];
}
function suc($data_to_merge = [])
{
    $data = ['status'=> 1, 'data'=> []];
    if($data_to_merge)
        $data['data'] = array_merge($data['data'], $data_to_merge);
    return $data;
}


/*动态获取参数key和value*/
function rq($key=null, $default=null)
{
    if(!$key) return Request::all();
    return Request::get($key, $default);
}
/*用户表模型*/
function user_ins()
{
    return new App\User;
}
/*问题表模型*/
function question_ins()
{
    return new App\Question;
}
/*回答表模型*/
function answer_ins()
{
    return new App\Answer;
}
/*评论表模型*/
function comment_ins()
{
    return new App\Comment;
}


Route::get('/', function ()
{
    return view('welcome');
});

/*
 * any支持所有请求方式
 * 用户注册、登录和登出
 */
Route::any('api/signup', function()
{
    return user_ins()->signup();
});
Route::any('api/login', function()
{
    return user_ins()->login();
});
Route::any('api/logout', function()
{
    return user_ins()->logout();
});
Route::any('api/user/change_password', function()
{
    return user_ins()->change_password();
});
Route::any('api/user/reset_password', function()
{
    return user_ins()->reset_password();
});
Route::any('api/user/validate_reset_password', function()
{
    return user_ins()->validate_reset_password();
});
Route::any('api/user/read', function()
{
    return user_ins()->read();
});

/*
 * 问题api
 */
Route::any('api/question/add', function()
{
    return question_ins()->add();
});
Route::any('api/question/change', function()
{
    return question_ins()->change();
});
Route::any('api/question/read', function()
{
    return question_ins()->read();
});
Route::any('api/question/remove', function()
{
    return question_ins()->remove();
});

/*
 * 回答api
 */
Route::any('api/answer/add', function()
{
    return answer_ins()->add();
});
Route::any('api/answer/change', function()
{
    return answer_ins()->change();
});
Route::any('api/answer/read', function()
{
    return answer_ins()->read();
});
Route::any('api/answer/vote', function()
{
    return answer_ins()->vote();
});

/*
 * 评论api
 */
Route::any('api/comment/add', function()
{
    return comment_ins()->add();
});
Route::any('api/comment/read', function()
{
    return comment_ins()->read();
});
Route::any('api/comment/remove', function()
{
    return comment_ins()->remove();
});

/*
 * 综合调用模型数据api
 */
Route::any('api/timeline', 'CommonController@timeline');



/*
 * 测试
 */
Route::any('test', function()
{
    dd(user_ins()->is_logged_in());
});


