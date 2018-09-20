<?php

namespace user\lib;

use core\handler\factory;
use ext\crypt;
use ext\pdo_mysql;

class prep extends factory
{
    /*** @var pdo_mysql $pdo_mysql */
    protected $pdo_mysql;
    /*** @var crypt $crypt */
    private $crypt;
    private $rsa_key;

    public function __construct()
    {
        $this->pdo_mysql = self::use('ext/pdo_mysql');
        $this->crypt     = self::use('ext/crypt');
        $this->rsa_key   = '';
    }

    /**
     * verify whether the user is logged in
     *
     * @param string $token
     * @param string $expire
     *
     * @return array
     * @throws \Exception
     */
    public function check_login(string $token, string $expire): array
    {
        if (empty($token)) {
            $code = '000';
            $msg  = 'Please login !';
        } elseif (empty($expire) || $expire < time()) {
            $code = '010';
            $msg  = 'Your login is expired , please login again !';
        } else {
            $uid  = $this->crypt->verify($token, $this->rsa_key);
            $user = $this->pdo_mysql->table('user')->where(['id' => $uid])->select();
            if ($user) {
                $code = '200';
                $msg  = 'Login success !';
            } else {
                $code = '011';
                $msg  = 'Verify user information fail, please login again! ';
            }
        }

        return ['code' => $code, 'msg' => $msg, 'token' => $token, 'expire' => $expire];
    }


    /**
     * create token for user
     *
     * @param string $user
     * @param string $password
     *
     * @return array
     * @throws \Exception
     */
    public function create_token(string $user, string $password): array
    {
        $uid = $this->pdo_mysql->table('user')->where(['user_name' => $user, 'pwd' => md5($password)])->get_one_feiled('id')['id'];
        if ($uid) {
            $code   = '200';
            $msg    = 'Login Success!';
            $token  = $this->crypt->sign($uid, $this->rsa_key);
            $expire = time() + 3600;
        } else {
            $code = '000';
            $msg  = 'Login error,please check your username or password !!';
        }

        return ['code' => $code, 'msg' => $msg, 'token' => $token, 'expire' => $expire];
    }

}