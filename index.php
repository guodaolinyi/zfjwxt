<?php

require 'vendor/autoload.php';

use zfjwxt\Zfjwxt;

$url = 'http://ems.bjwlxy.cn';

$stu_id = $_GET['stu'];
$password = $_GET['pw'];
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
