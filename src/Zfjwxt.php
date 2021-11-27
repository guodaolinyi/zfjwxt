<?php

namespace zfjwxt;

use CAPTCHAReader\src\App\IndexController;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;

session_start();

class Zfjwxt
{
    // 学号
    protected $studentcode;
    // 密码
    protected $password;
    // 验证码
    protected $captcha;
    // 姓名
    protected $name;
    // VIEWSTATE
    protected $viewstate;
    // url
    protected $url;

    //成绩查询uri
    const ZF_GRADE_URI = 'xscjcx.aspx';

    //考试查询uri
    const ZF_EXAM_URI = 'xskscx.aspx';

    //四六级成绩查询uri
    const ZF_CET_URI = 'xsdjkscx.aspx';

    //课表查询uri
    const ZF_SCHEDULE_URI = 'xskbcx.aspx';

    private $client;

    private $base_uri; //The base_uri of your Academic Network Systems. Like 'http://xuanke.lzjtu.edu.cn/'

    private $login_uri = 'default_ysdx.aspx';

    private $main_page_uri = 'xs_main.aspx';

    private $headers = [
        'timeout' => 3.0,
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.94 Safari/537.36',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];

    private $stu_id;

    //private $password;

    private $cacheCookie = false; // Is cookie cached

    private $cache; //Doctrine\Common\Cache\Cache

    private $cachePrefix = 'Lcrawl';

    //The login post param
    private $loginParam = [];
    // 验证码路径
    const CheckCode = '/CheckCode.aspx';
    const CaptchaPath = __DIR__ . '/../runtime/captcha/';

    function __construct($username, $password, $url)
    {
        if (!is_dir(self::CaptchaPath)) {
            @mkdir(self::CaptchaPath);
        }
        $this->studentcode = $username;
        $this->password = $password;
        $this->url = $url;
        if (empty($this->viewstate)) {
            // 教务系统session
            $_SESSION['sessionId'] = get_cookie(get_url($this->url));
            //设置验证码缓存名称
            $imgPath = self::CaptchaPath . md5(time()) . '.gif';
            // 获取验证码
            file_put_contents($imgPath, captcha_new($this->url, $_SESSION['sessionId']));
            // 识别验证码
            $a = new IndexController();
            $c = $a->entrance($imgPath, 'local', "ZhengFangNormal");
            $this->captcha = $c;
            @unlink($imgPath);
            $this->viewstate = $this->viewstate();
        }
        if (empty($this->viewstate)) exit(json_encode(['code' => 109, 'error' => '网络状态异常!']));
    }

    /**
     * @return string 获取教务系统登录时隐藏表单viewstate信息
     */
    protected function viewstate()
    {
        $curlArg = array(
            'url' => $this->url,
            'method' => 'get',
            'responseHeaders' => 0,
        );
        $result = curl_request($curlArg);
        preg_match_all('/name="__VIEWSTATE" value="(.*)"/', $result, $match);  //唯一识别码
        return $match[1][0];
    }

    /**
     * @return string 姓名
     */
    protected function name()
    {
        $default = array(
            '__VIEWSTATE' => $this->viewstate,
            'txtUserName' => $this->studentcode,
            'TextBox2' => $this->password,
            'txtSecretCode' => $this->captcha,
            'RadioButtonList1' => '%D1%A7%C9%FA',
            'Button1' => '',
            'lbLanguage' => ''
        );
        $aArg = array_merge($default);
        $curlArg = array(
            'url' => $this->url . '/' . "default2.aspx",
            'method' => 'post',
            'responseHeaders' => 0,
            'data' => $aArg,
            'cookie' => $_SESSION['sessionId'],
            'referer' => $this->url,
        );
        $result = curl_request($curlArg);
        $this->error(strip_tags($result));
        preg_match('/<span id=\"xhxm\">(.*)<\/span>/', $result, $name);
        return mb_substr($name[1], 0, -2, 'gb2312');
    }

    /**
     * @return string 用户姓名
     */
    public function getName()
    {
        return to_utf8($this->name());
    }

    /**
     * 处理错误信息
     * @param $result
     * @return bool|void
     */
    protected function error($result)
    {
        preg_match('/alert(.*?);/', $result, $error);
        if (empty($error[1])) {
            return true;
        }
        switch (to_utf8($error[1])) {
            case "('用户名不存在或未按照要求参加教学活动！！')":
                exit(300);  //用户名不存在或未按照要求参加教学活动
            case "('验证码不正确！！')":
                exit(400);  //验证码不正确
            case "('密码错误！！')":
                exit(500);  //用户名或密码不正确
            default:
                return true;
        }
    }
}
