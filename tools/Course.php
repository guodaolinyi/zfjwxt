<?php
/**
 * Created by PhpStorm.
 * User: 锅岛霖懿
 * Date: 2018-10-07
 * Time: 21:57
 */

namespace zfjwsys\tools;

class Course extends BaseSnail
{
    protected function course($url, $query, $p)
    {
        empty($p) ? $p = 1 : $p = 0;
        //$studentcode = '201796094115';
        $studentcode = '201696094025';
        //$password = 'nh.970821';
        $password = 'agmt.13579';
        $this->studentcode = $studentcode;
        $this->password = $password;
        $this->name = $this->name($this->viewstate);
        $curlArg = array(
            'url' => $url . $studentcode . "&xm=" . $this->name,
            'method' => 'post',
            'responseHeaders' => 0,
            'cookie' => $_SESSION['sessionId'],
            'referer' => jwsysUrl,
        );
        $temp = curl_request($curlArg);
        $table = dom_xpath($temp, $query);
        //星期一第一节课从下标18开始，去掉前16组
        for ($i = 0; $i <= 16; $i++) {
            unset($table[$i]);
        }
        $begin = 17;//第一节课的表头
        for ($i = 1; $i <= count($table); $i++) {
            if (preg_match('/第\d*[02468]节/', $table[$begin][0]) || preg_match('/上午/', $table[$begin][0]) || preg_match('/下午/', $table[$begin][0]) || preg_match('/晚上/', $table[$begin][0]))
                unset($table[$begin]);
            $begin++;
        }
        $m = 0;
        //$day = 1;//星期几
        $n = 1;//第几节课
        $schedule = array();
        foreach ($table as $x => $z) {
            if (preg_match('/第[13579]节/', $z[0])) {
                for ($i = 0; $i <= 7; $i++) {
                    if (isset($table[$x + $i + 1][0])) $schedule[$m + $i]['course'] = $table[$x + $i + 1][0];
                    if (isset($table[$x + $i + 1][0])) $schedule[$m + $i]['time'] = $i + 1 . '-' . $n;
                    if (isset($table[$x + $i + 1][2])) {
                        //$schedule[$m + $i]['time'] = $table[$x + $i + 1][2];
                        if (preg_match('/单/', $table[$x + $i + 1][2])) $schedule[$m + $i]['single'] = 1;
                        elseif (preg_match('/双/', $table[$x + $i + 1][2])) $schedule[$m + $i]['single'] = 2;
                    }
                    //if (isset($table[$x + $i + 1][4])) $schedule[$m + $i]['teacher'] = $table[$x + $i + 1][4];
                    if (isset($table[$x + $i + 1][6])) $schedule[$m + $i]['room'] = $table[$x + $i + 1][6];
                    if (isset($table[$x + $i + 1][10])) $schedule[$m + $i + 100]['course'] = $table[$x + $i + 1][9 + $p];
                    if (isset($table[$x + $i + 1][0])) $schedule[$m + $i]['time'] = $i + 1 . '-' . $n;
                    if (isset($table[$x + $i + 1][10])) {
                        //$schedule[$m + $i]['time'] = $table[$x + $i + 1][2];
                        if (preg_match('/单/', $table[$x + $i + 1][2])) $schedule[$m + $i]['single'] = 1;
                        elseif (preg_match('/双/', $table[$x + $i + 1][2])) $schedule[$m + $i]['single'] = 2;
                    }
                    //if (isset($table[$x + $i + 1][10])) $schedule[$m + $i + 100]['teacher'] = $table[$x + $i + 1][13 + $p];
                    if (isset($table[$x + $i + 1][10])) $schedule[$m + $i + 100]['room'] = $table[$x + $i + 1][15 + $p];
                }
                $m = $m + 7;
                $x = $x + 7;
                $n++;
            }
        }
        if (isset($schedule[35])) unset($schedule[35]);
        return $schedule;
    }

    public function class_course()
    {
        $url = 'http://ems.bjwlxy.cn/tjkbcx.aspx?xh=';
        $query = '//*[@id="Table6"]/tr/td';
        $p = '';
        return $this->course($url, $query, $p);
    }

    public function personal_course()
    {
        $url = 'http://ems.bjwlxy.cn/xskbcx.aspx?xh=';
        $query = '//*[@id="Table1"]/tr/td';
        $p = 1;
        return $this->course($url, $query, $p);
    }
}