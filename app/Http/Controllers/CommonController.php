<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class CommonController extends Controller
{

    /*时间线api*/
    public function timeline()
    {
        /*分页数据（把方法返回的数组值赋值给list中变量）*/
        list($limit, $skip) = paginate(rq('page'), rq('limit'));

        /*获取问题数据*/
        $questions = question_ins()
            ->limit($limit)
            ->skip($skip)
            ->orderBy('created_at', 'desc')
            ->get();
        /*获取回答数据*/
        $answers = answer_ins()
            ->limit($limit)
            ->skip($skip)
            ->orderBy('created_at', 'desc')
            ->get();

        /*合并2组数据*/
        $data = $questions->merge($answers);
        /*sortByDesc按照时间排序*/
        $data = $data->sortByDesc(function($item){
            return $item->created_at;
        });
        /*直接获取里面的值，不要键*/
        $data = $data->values()->all();

        return ['status'=> 1, 'data'=> $data];

    }





}
