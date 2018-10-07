<?php
/**
 * Created by PhpStorm.
 * User: 锅岛霖懿
 * Date: 2018-10-07
 * Time: 11:47
 */

namespace zfjwsys\tools;

use zfjwsys\tools\BaseSnail;

class Realname extends BaseSnail
{
    public function check_name($studentcode, $password)
    {
        $this->studentcode = $studentcode;
        $this->password = $password;
        $temp = $this->name = $this->name($this->viewstate);
        $realname = $this->to_utf8($temp);
        return $realname;
    }
}