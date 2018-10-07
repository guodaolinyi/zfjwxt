<?php
/**
 * Created by Tiny'Wo.
 * User: 锅岛霖懿
 * Date: 2018-10-07
 * Time: 9:01
 */	$ch=curl_init("http://jw.luas.edu.cn/xs_main.aspx?xh={$jwid}");
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_TIMEOUT,60);
//curl_setopt ($ch, CURLOPT_HTTPHEADER , $headerArr );
curl_setopt($ch,CURLOPT_REFERER,"http://jw.luas.edu.cn/default2.aspx");
curl_setopt($ch,CURLOPT_COOKIE,$cookies);
$str1=curl_exec($ch);
$info=curl_getinfo($ch);
curl_close($ch);
$pattern = '/xkkh=(.*?)&xh=/i';
preg_match_all($pattern, $str1, $matches);
if(empty($matches[1]))
{
    return "你没有需要评教的课程";
}

$i=1;
foreach($matches[1] as $fk){
    $ch=curl_init("http://jw.luas.edu.cn/xsjxpj.aspx?xkkh={$fk}&xh={$jwid}");
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    //curl_setopt($ch, CURLOPT_TIMEOUT,60);
    //curl_setopt ($ch, CURLOPT_HTTPHEADER , $headerArr );
    curl_setopt($ch,CURLOPT_REFERER,"http://jw.luas.edu.cn/xs_main.aspx?xh={$jwid}");
    curl_setopt($ch,CURLOPT_COOKIE,$cookies);
    $str2=curl_exec($ch);
    $info=curl_getinfo($ch);
    curl_close($ch);
    $pattern = '/<input type="hidden" name="__VIEWSTATE" value="(.*)" \/>/i';
    preg_match($pattern, $str2, $matcheaaas);
    $view1 = urlencode($matcheaaas[1]);
    $pattern = '/<select name="DataGrid1:(.*?)"/i';
    preg_match_all($pattern, $str2, $matcheaafas);
    $lianghao=iconv('UTF-8', 'GB2312', '良好');
    $youxiu=iconv('UTF-8', 'GB2312', '优秀');
    $all="";
    foreach($matcheaafas[1] as $num){
        $all .="&DataGrid1%3A".urlencode($num)."=".urlencode($youxiu);
    }
    $all=substr($all,0,-12).urlencode($lianghao);
    if($i<count($matches[1])){
        $alldata="__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=".$view1.$all."&pjkc=".urlencode($fk)."&pjxx=&txt1=&TextBox1=0&Button1=%B1%A3++%B4%E6";
        $ch=curl_init("http://jw.luas.edu.cn/xsjxpj.aspx?xkkh={$fk}&xh={$jwid}&gnmkdm=N12141");
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,60);
        curl_setopt($ch,CUPLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36");
        curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt ($ch, CURLOPT_HTTPHEADER , $headerArr );
        curl_setopt($ch,CURLOPT_REFERER,"http://jw.luas.edu.cn/xsjxpj.aspx?xkkh={$fk}&xh={$jwid}&gnmkdm=N12141");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $alldata);
        curl_setopt($ch,CURLOPT_COOKIE,$cookies);
        $str2=curl_exec($ch);
        $info=curl_getinfo($ch);
        curl_close($ch);
    }else{
        $alldata="__VIEWSTATE=".$view1.$all."&pjkc=".urlencode($fk)."&TextBox1=0&Button1=%B1%A3++%B4%E6";
        $ch=curl_init("http://jw.luas.edu.cn/xsjxpj.aspx?xkkh={$fk}&xh={$jwid}");
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,60);
        curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt ($ch, CURLOPT_HTTPHEADER , $headerArr );
        curl_setopt($ch,CURLOPT_REFERER,"http://jw.luas.edu.cn/xsjxpj.aspx?xkkh={$fk}&xh={$jwid}");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $alldata);
        curl_setopt($ch,CURLOPT_COOKIE,$cookies);
        $str2=curl_exec($ch);
        $info=curl_getinfo($ch);
        curl_close($ch);
        $alldata="__VIEWSTATE=".$view1.$all."&pjkc=".urlencode($fk)."&TextBox1=0&Button2=+%CC%E1++%BD%BB+";
        $ch=curl_init("http://jw.luas.edu.cn/xsjxpj.aspx?xkkh={$fk}&xh={$jwid}");
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,60);
        curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt ($ch, CURLOPT_HTTPHEADER , $headerArr );
        curl_setopt($ch,CURLOPT_REFERER,"http://jw.luas.edu.cn/xsjxpj.aspx?xkkh={$fk}&xh={$jwid}");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $alldata);
        curl_setopt($ch,CURLOPT_COOKIE,$cookies);
        $str2=curl_exec($ch);
        $info=curl_getinfo($ch);
        curl_close($ch);
    }

    $i++;
}