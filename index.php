<?php

require 'vendor/autoload.php';

use zfjwxt\Zfjwxt;

/*
202096094022
20010130

202184126018
225118
nh.970821

202096084058
qq1418115209

202096094022
xuzhe12345...

202096104026
sjl20010418

201694064125
sj19961204
*/
$stu_id = '201694064125';
$password = 'sj19961204';

$url = 'http://ems.bjwlxy.cn';

//$stu_id = $_GET['stu'];
//$password = $_GET['pw'];
$client = new Zfjwxt($stu_id, $password, $url);
$name = $client->getName();
dd($name);

/**
 * @param Array/string $arr
 * @param String $hint debug hint
 * @return void
 */
function dd($arr, $hint = '')
{
    if (is_object($arr) || is_array($arr)) {
        echo "<pre>";
        print_r($arr);
        echo PHP_EOL . $hint;
        echo "</pre>";
    } else {
        var_dump($arr);
        echo PHP_EOL . $hint;
    }
}
