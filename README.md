### 正方教务系统信息爬虫

#### 使用场景

之前开发了一款校园APP，为了集成教务系统的查询功能，写了一套能够爬取成绩及课表的爬虫。

#### 本人使用环境

* PHP 7.3.4

#### 获取方式

* composer方式

> composer require guodaolinyi/zfjwxt

* git方式

> git clone https://github.com/guodaolinyi/zfjwxt.git

##### 实现功能

* 校园一卡通

| 功能 | 进度 | 备注 |
|---|---|---|
| 余额查询 | 已完成 | |
| 挂失 | 计划中 | |
| 流水查询 | 计划中 |

* 教务系统

| 功能 | 进度 | 备注 |
|---|---|---|
| 姓名获取 | 完成 |
| 个人信息获取（学院、年级、所在专业、行政班级） | 完成 |
| 班级课表查询 | 完成 |
| 个人课表查询（含选修） | 完成 |
| 四六级成绩查询 | 完成 |
| 成绩查询（历年成绩查询） | 开发中 |
| 教室课表查询（为了实现空教室查询） | 计划中 |

##### 使用方法

###### 一卡通查询功能

* 一卡通余额查询

```
使用场景：饭卡余额查询

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

###### 教务系统查询

* 获取真实姓名

```
使用场景：校内APP实名认证

<?php

use zfjwsys\Zfjwxt;

class YourController extends Controller
{
    public function index()
    {
        $app = new Zfjwxt($code, $pw, $url);
        $name=$app->getName();  
        ...   
    }
}
```

###### 致谢

* 致开源组件作者

关于爬虫自动登录部分的验证码识别，这里使用了"kurisu/captcha_reader"作为验证码识别工具，对于"kurisu"大佬表示衷心的感谢！

* 致小伙伴

感谢你们使我又一次开始鼓足勇气去完善我的项目，使我停工三年的烂尾项目又重新充满活力，谢谢你们!
