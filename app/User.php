<?php

namespace App;

use Hash;
use Request;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    /* 注册api */
    public function signup()
    {
        $has_username_and_password = $this->has_username_and_password();

        /* 检查用户名和密码是否为空 */
        if (!$has_username_and_password)
            return err('用户名和密码不可为空');
        $username = $has_username_and_password[0];
        $password = $has_username_and_password[1];

        /* 检查用户名和密码是否存在 */
        $user_exists = $this
            ->where('username', $username)
            ->exists();
        if ($user_exists)
            return err('用户名已存在');

        /* 密码加密，也可以写成Hash::make() */
        $hashed_password = bcrypt($password);

        /* 存入数据库 */
        $user = $this;
        $user->password = $hashed_password;
        $user->username = $username;
        if ($user->save())
            return suc(['id' => $user->id]);
        else
            return err('db insert failed');

    }
    /*获取用户信息api*/
    public function read()
    {
        if(!rq('id'))
            return err('required id');

        $get = ['id', 'username', 'avatar_url', 'intro'];
        // $this->get($get);
        $user = $this->find(rq('id'), $get);
        $data = $user->toArray();

        $answer_count = answer_ins()->where('user_id', rq('id'))->count();
        $question_count = question_ins()->where('user_id', rq('id'))->count();

        $data['answer_count'] = $answer_count;
        $data['question_count'] = $question_count;

        return suc($data);
    }
    /* 登录api */
    public function login()
    {
        /*检查用户名和密码是否存在*/
        $has_username_and_password = $this->has_username_and_password();
        if (!$has_username_and_password)
            return err('username and password are required');
        $username = $has_username_and_password[0];
        $password = $has_username_and_password[1];

        /* 检查用户是否存在*/
        $user = $this->where('username', $username)->first();
        if (!$user)
            return err('user not exists');

        /*检查密码是否正确，Hash::check第1个参数是接收用户的明文密码第2个参数是查询数据库加密密码比较是否正确*/
        $hashed_password = $user->password;
        if (!Hash::check($password, $hashed_password))
            return err('invalid password');

        /*将用户名存储到session*/
        session()->put('username', $user->username);
        session()->put('user_id', $user->id);

        return suc(['id' => $user->id]);

    }


    public function has_username_and_password()
    {
        $username = rq('username');
        $password = rq('password');
        if ($username && $password)
            return [$username, $password];
        return false;
    }

    /*登出api*/
    public function logout()
    {
        //session()->flush();
        //直接删除值
        session()->forget('username');
        session()->forget('user_id');
        return suc();
    }

    /*检测用户是否登录*/
    public function is_logged_in()
    {
        return session('user_id') ?: false;
    }

    /*修改密码api*/
    public function change_password()
    {
        /*检查用户是否登录*/
        if (!$this->is_logged_in())
            return err('login required');

        /*检查参数是否存在*/
        if (!rq('old_password') || !rq('new_password'))
            return err('old_password and new_password are required');

        /*查询出用户数据*/
        $user = $this->find(session('user_id'));

        /*检查用户老密码是否正确*/
        if (!Hash::check(rq('old_password'), $user->password))
            return err('invalid old_password');

        /*更新用户密码*/
        $user->password = bcrypt(rq('new_password'));
        return $user->save() ?
            suc() :
            err('db update failed');

    }

    /*找回密码api*/
    public function reset_password()
    {
        if($this->is_robot())
            return err('max frequency reached');

        if (!rq('phone'))
            return err('phone is required');

        /*检查这个电话号码是否在数据库里存在，返回一个model模型*/
        $user = $this->where('phone', rq('phone'))->first();

        if (!$user)
            return err('invalid phone number');

        /*生成验证码*/
        $captcha = $this->generate_captcha();
        $user->phone_captcha = $captcha;

        if($user->save())
        {
            /*如果验证码保存成功，就发送短信验证码*/
            $this->send_sms();
            /*为下一次调用做准备，检查是否是机器人*/
            $this->update_robot_time();
            return suc();
        }

        return err('db update failed');

    }
    /*检查机器人*/
    public function is_robot($time = 10)
    {
        /*如果session中没有last_sms_time说明接口从未调用过*/
        if(!session('last_action_time'))
            return false;
        /*如果当前时间减去第一次发送短信的时间差，大于10的话就报错*/
        $current_time = time();
        $last_active_time = session('last_sms_time');
        $elapsed = $current_time - $last_active_time;
        return !($elapsed > $time);
    }
    /*更新机器人行为时间*/
    public function update_robot_time()
    {
        session()->set('last_action_time', time());
    }

    /*验证找回密码api*/
    public function validate_reset_password()
    {
        if($this->is_robot(2))
            return err('max frequency reached');

        if(!rq('phone') || !rq('phone_captcha') || !rq('new_password'))
            return err('phone, new_password and phone_captcha are required');

        /*检查用户是否存在*/
        $user = $this->where([
            'phone'=> rq('phone'),
            'phone_captcha'=> rq('phone_captcha')
        ])->first();
        if(!$user)
            return err('invalid phone or invalid phone_captcha');
        /*加密新密码*/
        $user->password = bcrypt(rq('new_password'));

        /*为下一次调用做准备，检查是否是机器人*/
        $this->update_robot_time();

        return $user->save() ?
            suc() : err('db update failed');


    }
    /*发送短信验证码*/
    public function send_sms()
    {
        return true;
    }
    /*生成验证码，随机数*/
    public function generate_captcha()
    {
        return rand(1000, 9999);
    }


    /*连接answers表，多对多关系*/
    public function answers()
    {
        return $this->belongsToMany('App\Answer')
            ->withPivot('vote')//如果在连接关系表里添加额外字段，需要指定下）
            ->withTimestamps(); //如果我们新增了数据或者更新了数据这个timestamps也会同时更新

    }


    /*连接answers表，多对多关系*/
    public function questions()
    {
        return $this
            ->belongsToMany('App\Question')
            ->withPivot('vote')//如果在连接关系表里添加额外字段，需要指定下）
            ->withTimestamps(); //如果我们新增了数据或者更新了数据这个timestamps也会同时更新

    }


}