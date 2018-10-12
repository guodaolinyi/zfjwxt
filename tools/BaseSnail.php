<?php
/**
 * Created by PhpStorm.
 * User: 锅岛霖懿
 * Date: 2018-10-06
 * Time: 10:50
 *
 * 构造提交表单说明
 * __VIEWSTATE:隐藏信息
 * txtUserName:用户名(学号)
 * TextBox2:密码
 * textSecretCode:验证码
 * RadioButtonList1:登录身份
 */

namespace zfjwsys\tools;

use CAPTCHAReader\src\App\IndexController;

require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../function.php');
session_start();

class BaseSnail
{
    //学号
    protected $studentcode;
    //密码
    protected $password;
    //验证码
    protected $captcha;
    //姓名
    protected $name;
    //VIEWSTATE
    protected $viewstate;

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
        $app = new IndexController();
        $captcha = $app->entrance($imgPath, 'local');
        //$this->studentcode = 201696094003;
        //$this->password = 'MMQ846834650';
        //$this->studentcode = 201696094025;
        //$this->password = 'agmt.13579';
        //$this->studentcode = '201796094115';
        //$this->password = 'nh.970821';
        //$this->studentcode = '201796094102';
        //$this->password = 'xbh2939..';
        $this->captcha = $captcha;
        $this->viewstate = $this->viewstate();
        if (empty($this->viewstate)) exit(json_encode(['code' => '200', 'error' => '网络状态异常!']));
    }

    //获取教务系统登录时隐藏表单viewstate信息
    protected function viewstate()
    {
        $curlArg = array(
            'url' => $_SESSION['version'] == 'new' ?
                jwsysUrl :
                jwsysUrl . '/(' . $_SESSION['sessionId'] . ")/default2.aspx",
            'method' => 'get',
            'responseHeaders' => 0,
        );
        $result = curl_request($curlArg);
        preg_match_all('/name="__VIEWSTATE" value="(.*)"/', $result, $match);  //唯一识别码
        return $match[1][0];
    }

    //name() 函数用来获取并设置姓名
    protected function name($viewstate)
    {
        $default = array('__VIEWSTATE' => $viewstate, 'txtUserName' => $this->studentcode, 'TextBox2' => $this->password, 'txtSecretCode' => $this->captcha, 'RadioButtonList1' => '%D1%A7%C9%FA', 'Button1' => '', 'lbLanguage' => '');
        $aArg = array_merge($default);
        $curlArg = array(
            'url' => $_SESSION['version'] == 'new' ?
                jwsysUrl . '/' . "default2.aspx" :
                jwsysUrl . '/(' . $_SESSION['sessionId'] . ")/default2.aspx",
            'method' => 'post',
            'responseHeaders' => 0,
            'data' => $aArg,
            'cookie' => $_SESSION['sessionId'],
            'referer' => jwsysUrl,
        );
        $result = curl_request($curlArg);
        $this->error(strip_tags($result));
        preg_match('/<span id=\"xhxm\">(.*)<\/span>/', $result, $name);
        return $realname = mb_substr($name[1], 0, -2, 'gb2312');
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

    //字符编码转换
    protected function to_utf8($sArg = '')
    {
        $string = iconv('gb2312', 'utf-8', $sArg);
        return $string;
    }

    //字符编码转换
    protected function to_gb2312($sArg = '')
    {
        $string = iconv('utf-8', 'gb2312', $sArg);
        return $string;
    }
}