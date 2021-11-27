<?php
/**
 * Created by PhpStorm.
 * User: 锅岛霖懿
 * Date: 2018-10-06
 * Time: 10:25
 */

/**
 * curl_request() 函数用来进行远程 http 请求
 * @param array $aArg 设置请求的参数，可最多包含下面 (array)$default 中所有的键值对
 * @return string      返回请求结果，结果是字符串
 */
if (!function_exists('curl_request')) {
    function curl_request($aArg = array())
    {
        /* 定义默认的参数 */
        $default = array(
            'url' => '', //远程请求的页面, 等同于 html 表单中的 action;
            'method' => 'get', //数据传输方式: post 和 get(默认);
            'data' => '', //HTTP请求中的数据, 支持数组和 name=value 方式的 url 查询字符串, 要发送文件，在文件名前面加上@前缀并使用完整路径。;
            'cookie' => '', //HTTP请求中"Cookie: "部分的内容。多个cookie用分号分隔，分号后带一个空格(例如， "fruit=apple; colour=red");
            'referer' => '', //HTTP请求头中"Referer: "的内容
            'userAgent' => '', //HTTP请求中包含一个"User-Agent: "头的字符串
            'requestHeaders' => array(), //用来设置 HTTP 请求头部字段的数组，形式： array('Content-type: text/plain', 'Content-length: 100')
            'sessionCookie' => false, //传输 cookie 时仅传输 Session Cookie
            'autoReferer' => true, //当根据 Location: 重定向时自动填写头部 Referer: 信息
            'responseHeaders' => false, //也将响应头部返回在文件流中
            'sslVerify' => false, //SSL 安全证书验证
            'timeout' => 30, //设置超时;
            'username' => '', //http 登录方式的用户名;
            'password' => '' //http 登录方式的密码;
        );
        $aArg = array_merge($default, $aArg);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $aArg['url']);
        curl_setopt($ch, CURLOPT_COOKIESESSION, $aArg['sessionCookie']);
        curl_setopt($ch, CURLOPT_AUTOREFERER, $aArg['autoReferer']);
        curl_setopt($ch, CURLOPT_HEADER, $aArg['responseHeaders']);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/4");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $aArg['timeout']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (strtolower($aArg['method']) == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $aArg['data']);
        } else {
            if ($aArg['data'] && (is_string($aArg['data']) || is_array($aArg['data']))) {
                $aArg['url'] .= (preg_match('/\?/', $aArg['url']) ? '&' : '?') . (is_string($aArg['data']) ? $aArg['data'] : http_build_query($aArg['data']));
            }
        }
        if ($aArg['sslVerify']) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 2);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
        if ($aArg['cookie']) curl_setopt($ch, CURLOPT_COOKIE, $aArg['cookie']);
        if ($aArg['referer']) curl_setopt($ch, CURLOPT_REFERER, $aArg['referer']);
        if ($aArg['userAgent']) curl_setopt($ch, CURLOPT_USERAGENT, $aArg['userAgent']);
        if (!empty($aArg['requestHeaders'])) curl_setopt($ch, CURLOPT_HTTPHEADER, $aArg['requestHeaders']);
        if ($aArg['username'] && $aArg['password']) curl_setopt($ch, CURLOPT_USERPWD, '[' . $aArg['username'] . ']:[' . $aArg['password'] . ']');
        $data = curl_exec($ch);
        if (curl_errno($ch)) return curl_error($ch);
        curl_close($ch);
        return $data;
    }
}
/**
 * get_cookie() 函数用来获取相应头部中的 Cookie
 * @param string $sArg 可能包含 Cookie 的字符串
 * @return string        返回所有匹配的 Cookie 字符串
 */
function get_cookie($sArg = '')
{
    preg_match_all('/Cookie: (.*);/iU', $sArg, $aCookie);
    for ($i = 0, $sCookie = ''; $i < count($aCookie[1]); $i++) {
        if (strpos($sCookie, $aCookie[1][$i])) continue;
        if ($i != 0) $sCookie .= '; ';
        $sCookie .= $aCookie[1][$i];
    }
    return $sCookie;
}

/**
 * get_location() 函数用来获取相应头部中 Location: 的内容
 * @param string $sArg 可能包含 Location: 的字符串
 * @return string        返回所有匹配的 Location: 字符串
 */
function get_location($sArg = '')
{
    preg_match('/Location: (.*)/', $sArg, $aLocation);
    return $aLocation[1];
}

/**
 * get_url() 函数用来获取相应URL返回的内容
 * @param string $sArg URL地址
 * @return string        返回相应URL返回的内容
 */
function get_url($sArg = '')
{
    $curlArg = array(
        'url' => $sArg,
        'method' => 'get',
        'responseHeaders' => 1
    );
    $result = curl_request($curlArg);
    return $result;
}

/**
 * captcha_new() 函数用来获取新版教务系统验证码
 * @param string $url URL地址
 * @param string $sessionId 对应的sessionID
 * @return string 返回相应URL返回的内容
 */
if (!function_exists('captcha_new')) {
    function captcha_new($url, $sessionId)
    {
        $curlArg = array(
            'url' => $url . '/CheckCode.aspx',
            'method' => 'get',
            'cookie' => $sessionId,
            'responseHeaders' => 0
        );
        $result = curl_request($curlArg);
        return $result;
    }
}

/*
 * xpath操作
 * */
function dom_xpath($file_content, $xpath_query)
{
    $table = array();
    $dom = new DOMDocument;
    @$dom->loadHTML($file_content);
    $xpath = new DOMXPath($dom);
    $dls = $xpath->query($xpath_query);
    foreach ($dls as $i => $dl) {
        $spans = $dl->childNodes;
        foreach ($spans as $j => $span) {
            $table[$i][$j] = $span->textContent;
        }
    }
    return $table;
}

/**
 * 转为utf-8
 * @param string $sArg
 * @return false|string
 */
function to_utf8($sArg = '')
{
    $str = iconv('gb2312', 'utf-8', $sArg);
    return $str;
}

/**
 * 转为gb2312
 * @param string $sArg
 * @return false|string
 */
function to_gb2312($sArg = '')
{
    $str = iconv('utf-8', 'gb2312', $sArg);
    return $str;
}

if (!function_exists('remove_brackets')) {
    /**
     * 去掉中文括号
     * @param $sArg
     * @return array|string|string[]|null
     */
    function remove_brackets($sArg)
    {
        $str = preg_replace("/\(.*\)/", '', $sArg);
        return preg_replace("/\（.*\）/", '', $str);
    }
}

if (!function_exists('table_to_array')) {
    /**
     * 表格数据转数组
     * @param $table
     * @return array
     */
    function table_to_array($table)
    {
        $table = preg_replace("'<table[^>]*?>'si", "", $table);
        $table = preg_replace("'<tr[^>]*?>'si", "", $table);
        $table = preg_replace("'<td[^>]*?>'si", "", $table);
        $table = str_replace("</tr>", "{tr}", $table);
        $table = str_replace("</td>", "{td}", $table);
        // <br> 替换
        $table = str_replace("<br>", '{br}', $table);
        //去掉空白字符
        $table = preg_replace("'([rn])[s]+'", "", $table);
        $table = str_replace(" ", "", $table);
        $table = str_replace(" ", "", $table);
        $table = explode('{tr}', $table);
        unset($table[0]);
        unset($table[1]);
        array_pop($table);
        $td_array = [];
        foreach ($table as $key => $tr) {
            $td = explode('{td}', $tr);
            // 每周大于7天删除无用元素
            // 删除上午、下午
            array_shift($td);
            // 删除第几节课
            array_pop($td);
            // 删除多余元素
            if (count($td) == 8) {
                array_shift($td);
            }
            $td_array[] = $td;
        }
        return $td_array;
    }
}
