### 正方教务系统信息爬虫

#### 使用场景

之前开发了一款校园APP，为了集成教务系统的查询功能，写了一套能够爬取成绩及课表的爬虫。

> 本人使用环境

* PHP 7.0.10
* MySQL 5.7
* Apache 2.4
* Laravel 5.5

#### 获取方式

##### composer方式

> composer require guodaolinyi/zfjwxt

##### git方式

> https://github.com/guodaolinyi/zfjwxt.git

##### 使用方法

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

##### Ex.

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