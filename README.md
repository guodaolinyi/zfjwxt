### 正方教务系统信息爬虫

#### 使用场景

之前开发了一款校园APP，为了集成教务系统的查询功能，写了一套能够爬取成绩及课表的爬虫。

> 本人使用环境

* PHP 7.3.4

#### 获取方式

##### composer方式

> composer require guodaolinyi/zfjwxt

##### git方式

> https://github.com/guodaolinyi/zfjwxt.git

##### 实现功能

* 校园一卡通

| 功能 | 进度 | 备注 |
|---|---|---|
| 余额查询 | 已完成 | |
| 挂失 | | |

* 教务系统

| 功能 | 进度 | 备注 |
|---|---|---|
| 课表查询 | 开发中 | |
| 四六级成绩查询 | 开发中 | |

##### 使用方法

* 一卡通余额查询

```
<?php

use zfjwxt\Ykt;

class YourController extends Controller
{
    public function yourfunction()
    {
        $app = new Ykt($code, $pw, $url);
        $balance = $app->getBalance();
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
