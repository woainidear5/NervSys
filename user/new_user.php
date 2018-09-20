<?php

namespace user;

use user\lib\prep;

class new_user extends prep
{
    public static $tz = [
        'test' => []
    ];


    public function test()
    {
        $user = $this->pdo_mysql->table('user')->where(['id' , 1]);
        return $user;
    }


}