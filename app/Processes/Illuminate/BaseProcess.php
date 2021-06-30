<?php

namespace App\Processes\Illuminate;

use App\Util\FunctionHelper;

class BaseProcess
{

    /**
     * 向管道内写入数据
     * @param array $data
     * @param string $customProcesses
     * @return mixed
     */
    public static function write($data = [], $customProcesses = 'baseProcess')
    {
        foreach ($data as $item) {
            FunctionHelper::pushQueue($item);//Task 进入消息队列
        }

        return true;
    }

}
