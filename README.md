###正方教务系统信息抓取包(zfjwsys)

>本人使用环境

* PHP 7.0.10
* MySQL 5.7
* Apache 2.4
* Laravel 5.5

####获取方式

#####composer方式

>composer require guodaolinyi/zfjwsys

#####git方式

>https://git.coding.net/guodaolinyi/zfjwsys.git

#####使用方法

```
<?php

use zfjwsys\tools\Mytools;

class YourController extends Controller
{
    public function yourfunction()
    {
        $app = new Mytools();
        ...    
    }
}
```

#####Ex.

```
实名认证:教务系统查询姓名

<?php

use zfjwsys\tools\Realname;

class YourController extends Controller
{
    public function index()
    {
        $app = new Realname();
        $studentcode = '';  //学号
        $password = ''; //密码
        $realname=$app->realname($studentcode, $password);    //获得真实姓名
    }
}
```