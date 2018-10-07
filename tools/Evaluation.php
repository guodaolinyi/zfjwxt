<?php
/**
 * Created by PhpStorm.
 * User: 锅岛霖懿
 * Date: 2018-10-07
 * Time: 11:16
 */
namespace zfjwsys\tools;
use zfjwsys\tools\TurboSnail;
class Evaluation extends TurboSnail{
    public function index(){
        preg_match_all('<a\b[^>]+\bonclick="([^"]*)"[^>]*>([\s\S]*?)</a>',$re);
    }
}