<?php
/**
 * Created by PhpStorm.
 * User: 锅岛霖懿
 * Date: 2018-10-13
 * Time: 18:30
 */

namespace zfjwsys\tools;

require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../function.php');
header("Content-type: text/html; charset=utf8");
session_start();

class Balance
{
    //学号
    protected $username;
    //密码
    protected $password;
    //验证码
    protected $captcha;
    //验证码cookie
    protected $checkcode;
    //登录成功后相应cookie
    protected $cookie;

    function __construct()
    {
        //一卡通验证码
        $this->captcha = $this->get_captcha();
        //一卡通系统验证码cookie
        $this->checkcode = 'ChenyisiCheckCode:CheckCode=' . $this->captcha;
    }

    protected function get_login()
    {
        $default = array(
            'acounttype' => 'StudentNo',
            'action' => 'Login',
            'checkcode' => $this->captcha,
            'managetype' => 'Front',
            'username' => $this->username,
            'userpassword' => $this->password
        );
        $aArg = array_merge($default);
        $requestHeaders = array(
            'Cookie: ChenyisiCheckCode=CheckCode=' . $this->captcha,
        );
        $curlArg = array(
            'url' => yktUrl . "/Handler/UserLogin.ashx",
            'method' => 'post',
            'responseHeaders' => 1,
            'data' => $aArg,
            'requestHeaders' => $requestHeaders,
            'cookie' => $this->checkcode,
            'referer' => yktUrl . '/Default.aspx',
        );
        $result = curl_request($curlArg);
        $this->cookie = get_cookie($result);
    }

    protected function get_captcha()
    {
        preg_match('/(CheckCode=\b)[A-Z0-9]{4}/', get_url(yktUrl . '/CheckCode.aspx'), $captcha);
        return ltrim($captcha[0], 'CheckCode=');
    }

    public function balance($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->get_login();
        $curlArg = array(
            'url' => yktUrl . '/User/User_Account.aspx',
            'method' => 'get',
            'responseHeaders' => 0,
            'cookie' => $this->checkcode . ';' . $this->cookie,
            'referer' => yktUrl,
        );
        $result = curl_request($curlArg);
        $balance = dom_xpath($result, '/html/body/form/div/div/div[2]/div[2]/dl/dt[1]/div/em[1]');
        return rtrim($balance[0][0], '（卡余额）');
    }

    //处理错误信息
    protected function error($result)
    {
        preg_match('/alert(.*?);/', $result, $error);
        if (empty($error[1])) {
            return true;
        }
        switch ($this->to_utf8($error[1])) {
            case "('用户名不存在或未按照要求参加教学活动！！')":
                exit(json_encode(['code' => '300', 'error' => '用户名不存在或未按照要求参加教学活动！']));
            case "('验证码不正确！！')":
                exit(json_encode(['code' => '400', 'error' => '验证码不正确！']));
            case "('密码错误！！')":
                exit(json_encode(['code' => '500', 'error' => '密码错误！']));
            default:
                return true;
        }
    }
}