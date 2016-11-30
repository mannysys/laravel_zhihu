<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{

    /* 添加回答api */
    public function add()
    {
        /*检查用户是否登录*/
        if(!user_ins()->is_logged_in())
            return ['status'=> 0, 'msg'=>'login required'];

        /* 检查参数中是否存在question_id和content */
        if(!rq('question_id') || !rq('content'))
            return ['status'=> 0, 'msg'=> 'question_id and content are required'];

        /* 检查问题是否存在 */
        $question = question_ins()->find(rq('question_id'));
        if(!$question) return ['status'=> 0, 'msg'=> 'question not exists'];

        /* 检查重复回答(用户回答一个问题是否有多次回答) */
        $answered = $this
            ->where(['question_id'=> rq('question_id'), 'user_id'=> session('user_id')])
            ->count();
        if($answered)
            return ['status'=> 0, 'msg'=> 'duplicate answers'];

        /*保存数据*/
        $this->content = rq('content');
        $this->question_id = rq('question_id');
        $this->user_id = session('user_id');

        return $this->save() ?
            ['status'=> 1, 'id'=> $this->id] :
            ['status'=> 0, 'msg'=> 'db insert failed'];

    }

    /*更新回答api*/
    public function change()
    {
        /*检查用户是否登录*/
        if(!user_ins()->is_logged_in())
            return ['status'=> 0, 'msg'=>'login required'];

        /*检查回答id参数*/
        if(!rq('id') || !rq('content'))
            return ['status'=> 0, 'msg'=>'id and content are required'];

        /*检查回答是否是自己回答*/
        $answer = $this->find(rq('id'));
        if($answer->user_id != session('user_id'))
            return ['status'=> 0, 'msg'=>'permission denied'];

        /*更新数据*/
        $answer->content = rq('content');
        return $answer->save() ?
            ['status'=> 1] :
            ['status'=> 0, 'msg'=>'db update failed'];

    }

    /*查看回答api*/
    public function read()
    {
        /*检查回答参数*/
        if(!rq('id') && !rq('question_id'))
            return ['status'=> 0, 'msg'=>'id or question_id required'];

        if(rq('id'))
        {
            /*查看单个回答*/
            $answer = $this->find(rq('id'));
            if(!$answer)
                return ['status'=> 0, 'msg'=>'answer not exists'];
            return ['status'=> 1, 'data'=> $answer];
        }

        /*检查问题是否存在*/
        if(!question_ins()->find(rq('question_id')))
            return ['status'=> 0, 'msg'=> 'question not exists'];

        /*查看同一个问题下的所有回答*/
        $answers = $this
            ->where('question_id', rq('question_id'))
            ->get()
            ->keyBy('id'); //指定id做为键
        return ['status'=> 1, 'data'=> $answers];

    }

    /*投票api*/
    public function vote()
    {
        /*检查用户是否登录*/
        if(!user_ins()->is_logged_in())
            return ['status'=> 0, 'msg'=> 'login required'];

        /*检查参数是否存在*/
        if(!rq('id') || !rq('vote'))
            return ['status'=> 0, 'msg'=> 'id and vote are required'];

        /*检查回答是否存在*/
        $answer = $this->find(rq('id')); //查询返回数据对象，就会存储在了$answer中
        if(!$answer) return ['status'=> 0, 'msg'=> 'answer not exists'];

        /*检查vote参数，1为赞同票，2是反对票*/
        $vote = rq('vote') <= 1 ? 1 : 2;

        /*检查此用户是否在相同问题下投过票，如果投过票则删除投票*/
        $answer
            ->users()   //返回连接关系表对象
            ->newPivotStatement()  //这个方法是让我们进入连接关系表，进行操作
            ->where('user_id', session('user_id'))
            ->where('answer_id', rq('id'))
            ->delete();

        /*在连接关系表中增加数据*/
        $answer
            ->users()
            ->attach(session('user_id'), ['vote'=> $vote]);

        return ['status'=> 1];
    }

    /*连接users表，多对多关系*/
    public function users()
    {
        return $this->belongsToMany('App\User')
            ->withPivot('vote') //如果在连接关系表里添加额外字段，需要指定下）
            ->withTimestamps(); //如果我们新增了数据或者更新了数据这个timestamps也会同时更新

    }














}














