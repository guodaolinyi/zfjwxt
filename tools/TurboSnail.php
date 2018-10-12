<?php
/**
 * Created by PhpStorm.
 * User: 锅岛霖懿
 * Date: 2018-10-06
 * Time: 16:58
 */

namespace zfjwsys\tools;

class TurboSnail extends BaseSnail
{
    function __construct()
    {
        parent::__construct();
        $this->name=$this->name($this->viewstate);
    }
}