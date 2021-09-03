<?php

namespace app\common\validate;
class Member extends Base
{
    protected $rule = array(
        'username' => 'require|unique:member|alphaDash',
        'email' => 'require|unique:member|email',
        'mobile' => 'unique:member',
        'password' => 'require',
        'repassword' => 'confirm:password'
    );
    protected $message = array(
        'username.require' => '用户名必须填写',
        'username.unique' => '用户名已存在',
        'username.alphaDash' => '用户名只能是字母、数字和下划线_及破折号-',
        'email.require' => '邮箱必须',
        'email.unique' => '邮箱已存在',
        'email.email' => '邮箱格式不正确',
        'mobile.unique' => '手机号已存在',
        'password.require' => '必须输入密码',
        'repassword.confirm' => '确认密码和密码不同',
    );
    protected $scene = array(
        'edit' => 'email,mobile',
        'password' => 'password,repassword'
    );

}