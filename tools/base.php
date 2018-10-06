<?php
/**
 * Created by PhpStorm.
 * User: 锅岛霖懿
 * Date: 2018-10-06
 * Time: 10:50
 */
namespace zfjwsys\tools;

require_once(__DIR__ . '../config.php');
require_once(__DIR__ . '../function.php');
session_start();

class base
{
    function __construct()
    {
        if (!is_dir(verifyCodePath)) {
            @mkdir(verifyCodePath, 777);
        }
        //教务系统session
        $_SESSION['sessionId'] = get_cookie(get_url(jwsysUrl));
        //设置验证码缓存名称
        $imgPath = verifyCodePath . '/' . md5(time()) . '.gif';
        //判断教务系统版本
        if (empty($_SESSION['sessionId'])) {
            $_SESSION['version'] = 'old';
            //获取旧版本教务系统sessionId
            $_SESSION['sessionId'] = substr(get_location(get_url(jwsysUrl)), 2, 24);
            //保存验证码
            file_put_contents($imgPath, captcha_old(jwsysUrl, $_SESSION['sessionId']));
        } else {
            $_SESSION['version'] = 'new';
            //保存验证码
            file_put_contents($imgPath, captcha_new(jwsysUrl, $_SESSION['sessionId']));
        }
    }
}