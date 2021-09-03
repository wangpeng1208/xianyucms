<?php

namespace app\common\validate;
class User extends Base
{
    protected $rule = array(
        'username' => 'require|unique:user|max:25',
        'email' => 'unique:user|email|require',
        'password' => 'require|max:20|min:6',
        'repassword' => 'confirm:password',
        'verifypass' => 'confirm:password'
    );
    protected $message = array(
        'username.require' => '用户名必填',
        'username.unique' => '用户名存在',
        'username.max' => '用户名最多不能超过25个字符',
        'email.email' => '邮箱格式错误',
        'email.unique' => '邮箱已被占用',
        'email.require' => '邮箱不能为空',
        'password.require' => '必须输入密码',
        'password.max' => '密码不能不大于于20位字符',
        'password.min' => '密码不能不少于6位字符',
        'repassword.confirm' => '确认密码和密码必须一致',
        'verifypass.confirm' => '重复密码必须和新密码一致',
    );
    protected $scene = array(
        'edit' => 'email',
        'password' => 'password,repassword',
        'pwd' => 'password,verifypass',
        'email' => 'email',
        'username' => 'username',
        'sns' => 'username,password',
        'reg' => 'username,email,password',
    );

}