<?php
/**
 * Created by PhpStorm.
 * User: 锅岛霖懿
 * Date: 2018-10-07
 * Time: 11:47
 */

namespace zfjwsys\tools;

class Realname extends BaseSnail
{
    public function realname($studentcode, $password)
    {
        $this->studentcode = $studentcode;
        $this->password = $password;
        $realname = $this->to_utf8($this->name($this->viewstate));
        return $realname;
    }
}