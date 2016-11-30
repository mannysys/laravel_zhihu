<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{

    /*添加评论api*/
    public function add()
    {
        /*检查用户是否登录*/
        if(!user_ins()->is_logged_in())
            return ['status'=> 0, 'msg'=>'login required'];

        /*检查评论内容*/
        if(!rq('content'))
            return ['status'=> 0, 'msg'=>'empty content'];

        /*检查问题id和回答id是否都有或者是否都没有（这2个只能有一个）*/
        if(
            (!rq('question_id') && !rq('answer_id')) ||
            (rq('question_id') && rq('answer_id'))
        )
            return ['status'=> 0, 'msg'=>'question_id or answer_id is required'];


        if(rq('question_id'))
        {
            /*如果有问题id*/
            $question = question_ins()->find(rq('question_id'));
            if(!$question) return ['status'=> 0, 'msg'=>'question not exists'];
            $this->question_id = rq('question_id');
        } else
        {
            /*如果有回答id*/
            $answer = answer_ins()->find(rq('answer_id'));
            if(!$answer) return ['status'=> 0, 'msg'=>'answer not exists'];
            $this->answer_id = rq('answer_id');
        }

        /*如果有评论id*/
        if(rq('reply_to'))
        {
            $target = $this->find(rq('reply_to'));
            /*检查目标评论是否存在*/
            if(!$target) return ['status'=> 0, 'msg'=>'target comment not exists'];
            /*检查是否回复自己的评论，不能回复自己的评论*/
            if($target->user_id == session('user_id'))
                return ['status'=> 0, 'msg'=>'cannot replay to yourself'];
            $this->reply_to = rq('reply_to');
        }

        /*保存数据*/
        $this->content = rq('content');
        $this->user_id = session('user_id');
        return $this->save() ?
            ['status'=> 1, 'id'=> $this->id] :
            ['status'=> 0, 'msg'=> 'db insert failed'];


    }

    /*查看评论api*/
    public function read()
    {
        /*检查问题和回答id是否存在*/
        if(!rq('question_id') && !rq('answer_id'))
            return ['status'=> 0, 'msg'=> 'question_id or answer_id is required'];

        if(rq('question_id'))
        {
            /*查看问题下的所有评论*/
            $question = question_ins()->find(rq('question_id'));
            if(!$question) return ['status'=> 0, 'msg'=> 'question not exists'];
            $data = $this->where('question_id', rq('question_id'));
        }
        else
        {
            /*查看回答下的所有评论*/
            $answer = answer_ins()->find(rq('answer_id'));
            if(!$answer) return ['status'=> 0, 'msg'=> 'answer not exists'];
            $data = $this->where('answer_id', rq('answer_id'));
        }

        $data = $data->get()->keyBy('id');
        return ['status'=> 1, 'data'=> $data];

    }

    /*删除评论api*/
    public function remove()
    {
        /*检查用户是否登录*/
        if(!user_ins()->is_logged_in())
            return ['status'=> 0, 'msg'=> 'login required'];

        /*检查评论id是否存在*/
        if(!rq('id'))
            return ['status'=> 0, 'msg'=> 'id is required'];

        /*检查这条评论是否存在*/
        $comment = $this->find(rq('id'));
        if(!$comment) return ['status'=> 0, 'msg'=> 'comment not exists'];

        /*检查这条评论是否是自己的*/
        if($comment->user_id != session('user_id'))
            return ['status'=> 0, 'msg'=> 'permission denied'];

        /*删除这条评论下回复*/
        $this->where('reply_to', rq('id'))->delete();

        /*删除评论*/
        return $comment->delete() ?
            ['status'=> 1] :
            ['status'=> 0, 'msg'=> 'db delete failed'];



    }











}
