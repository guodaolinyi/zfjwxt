<?php

namespace zfjwxt;

class Ykt
{
    //学号
    protected $username;
    //密码
    protected $password;
    // 一卡通网址
    protected $url;
    //验证码
    protected $captcha;
    //验证码cookie
    protected $checkcode;
    //登录成功后相应cookie
    protected $cookie;

    function __construct($username, $password, $url)
    {
        $this->username = $username;
        $this->password = $password;
        $this->url = $url;
        //一卡通验证码
        $this->captcha = $this->getCaptcha();
        //一卡通系统验证码cookie
        $this->checkcode = 'ChenyisiCheckCode:CheckCode=' . $this->captcha;
    }

    /**
     * @return string 获取验证码
     */
    protected function getCaptcha()
    {
        preg_match('/(CheckCode=\b)[A-Z0-9]{4}/', get_url($this->url . '/CheckCode.aspx'), $captcha);
        return ltrim($captcha[0], 'CheckCode=');
    }

    /**
     * 模拟登陆
     */
    protected function getLogin()
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
            'url' => $this->url . "/Handler/UserLogin.ashx",
            'method' => 'post',
            'responseHeaders' => 1,
            'data' => $aArg,
            'requestHeaders' => $requestHeaders,
            'cookie' => $this->checkcode,
            'referer' => $this->url . '/Default.aspx',
        );
        $result = curl_request($curlArg);
        $this->cookie = get_cookie($result);
    }

    /**
     * @return string 获取卡余额
     */
    public function getBalance()
    {
        $this->getLogin();
        $curlArg = array(
            'url' => $this->url . '/User/User_Account.aspx',
            'method' => 'get',
            'responseHeaders' => 0,
            'cookie' => $this->checkcode . ';' . $this->cookie,
            'referer' => $this->url,
        );
        $result = curl_request($curlArg);
        $balance = dom_xpath($result, '/html/body/form/div/div/div[2]/div[2]/dl/dt[1]/div/em[1]');
        return rtrim($balance[0][0], '（卡余额）');
    }
}
