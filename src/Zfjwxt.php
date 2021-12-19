<?php

namespace zfjwxt;

use CAPTCHAReader\src\App\IndexController;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\DomCrawler\Crawler;

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

    // 验证码路径
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
        if (empty($this->name)) $this->name = $this->name();
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
        return to_utf8($this->name);
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

    /**
     * 获取本人信息
     * @return array
     */
    public function getPersonInfo()
    {
        $curlArg = [
            'url' => $this->url . '/xsgrxx.aspx?xh=' . $this->studentcode . '&xm=' . $this->name,
            'method' => 'post',
            'responseHeaders' => 0,
            'cookie' => $_SESSION['sessionId'],
            'referer' => $this->url,
        ];
        $result = curl_request($curlArg);
        $crawler = new Crawler($result);
        return [
            // 行政班
            'class' => $crawler->filterXPath('//*[@id="lbl_xzb"]')->text(),
            // 所在年级
            'grade' => $crawler->filterXPath('//*[@id="lbl_dqszj"]')->text(),
            // 专业名称
            'major' => $crawler->filterXPath('//*[@id="lbl_zymc"]')->text(),
            // 学院
            'college' => $crawler->filterXPath('//*[@id="lbl_xy"]')->text(),
            // 出生日期
            'birthday' => $crawler->filterXPath('//*[@id="lbl_csrq"]')->text(),
            // 性别
            'gender' => $crawler->filterXPath('//*[@id="lbl_xb"]')->text(),
            // 姓名
            'name' => $crawler->filterXPath('//*[@id="xm"]')->text(),
            // 毕业中学
            'high_school' => $crawler->filterXPath('//*[@id="byzx"]')->attr('value'),
            // 民族
            'nation' => $crawler->filterXPath('//*[@id="mz"]')->children()->first()->text(),
            // 家庭所在地
            'home_location' => $crawler->filterXPath('//*[@id="jtszd"]')->attr('value'),
            // 政治面貌
            'politics' => $crawler->filterXPath('//*[@id="zzmm"]')->children()->first()->text(),
        ];
    }

    /**
     * 获取班级课表
     * @return array
     */
    public function getClassSchedule()
    {
        $curlArg = [
            'url' => $this->url . '/tjkbcx.aspx?xh=' . $this->studentcode . '&xm=' . $this->name,
            'method' => 'post',
            'responseHeaders' => 0,
            'cookie' => $_SESSION['sessionId'],
            'referer' => $this->url,
        ];
        $result = curl_request($curlArg);
        if (!$result) {
            return result_arr(FAIL);
        }
        $crawler = new Crawler($result);
        $table = $crawler->filterXPath('//*[@id="Table6"]')->html();
        $data['year'] = $crawler->filterXPath('//select[@id="xn"]/option[@selected="selected"]')->text();
        $data['semester'] = $crawler->filterXPath('//select[@id="xq"]/option[@selected="selected"]')->text();
        $data['grade'] = $crawler->filterXPath('//select[@id="nj"]/option[@selected="selected"]')->text();
        $data['schedule'] = format_schedule($table);
        return result_arr(SUCCESS, '班级课表', $data);
    }

    /**
     * 获取个人课表
     * @return array
     */
    public function getPersonSchedule()
    {
        $curlArg = [
            'url' => $this->url . '/xskbcx.aspx?xh=' . $this->studentcode . '&xm=' . $this->name,
            'method' => 'post',
            'responseHeaders' => 0,
            'cookie' => $_SESSION['sessionId'],
            'referer' => $this->url,
        ];
        $result = curl_request($curlArg);
        if (!$result) {
            return result_arr(FAIL);
        }
        $crawler = new Crawler($result);
        $table = $crawler->filterXPath('//*[@id="Table1"]')->html();
        $data['year'] = $crawler->filterXPath('//*[@id="xnd"]')->children()->first()->text();
        $data['semester'] = $crawler->filterXPath('//*[@id="xqd"]')->children()->first()->text();
        $data['schedule'] = format_schedule($table);
        return result_arr(SUCCESS, '个人课表', $data);
    }

    /**
     * 获取CET成绩
     * @return array
     */
    public function getCET()
    {
        $curlArg = [
            'url' => $this->url . '/xsdjkscx.aspx?xh=' . $this->studentcode . '&xm=' . $this->name,
            'method' => 'post',
            'responseHeaders' => 0,
            'cookie' => $_SESSION['sessionId'],
            'referer' => $this->url,
        ];
        $result = curl_request($curlArg);
        $crawler = new Crawler($result);
        $table = $crawler->filterXPath('//*[@id="DataGrid1"]')->html();
        return format_cet($table);
    }

    /**
     * 获取历年成绩
     * @return array
     */
    public function getScore()
    {
        // viewstate值发生了变化 需要重新获取viewstate
        $curlArg = array(
            'url' => $this->url . '/xscjcx.aspx?xh=' . $this->studentcode . '&xm=' . $this->name . '&gnmkdm=N121605',
            'method' => 'get',
            'responseHeaders' => 0,
            'cookie' => $_SESSION['sessionId'],
            'referer' => $this->url,
        );
        $result = curl_request($curlArg);
        preg_match_all('/name="__VIEWSTATE" value="(.*)"/', $result, $match);  //唯一识别码
        $viewstate = $match[1][0];
        $aArg = [
            '__VIEWSTATE' => $viewstate,
            '__EVENTARGET' => '',
            '__EVENTARGUMENT' => '',
            'hidLanguage' => '',
            'ddlXN' => '',
            'ddlXQ' => '',
            'ddl_kcxz' => '',
            'btn_zcj' => '%C0%FA%C4%EA%B3%C9%BC%A8'
        ];
        $curlArg = [
            'url' => $this->url . '/xscjcx.aspx?xh=' . $this->studentcode . '&xm=' . $this->name,
            'method' => 'post',
            'responseHeaders' => 0,
            'data' => $aArg,
            'cookie' => $_SESSION['sessionId'],
            'referer' => $this->url,
        ];
        $result = curl_request($curlArg);
        if (!$result) {
            return result_arr(FAIL);
        }
        $crawler = new Crawler($result);
        $table = $crawler->filterXPath('//*[@id="Datagrid1"]')->html();
        $data = format_score($table);
        return result_arr(SUCCESS, '历年成绩', $data);
    }
}
