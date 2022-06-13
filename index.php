<?php

/**
 * Danmaku PHP Api For DPlayer
 * 
 * @package Danmaku
 * @author HaruhiYunona
 * @version 1.0.0
 * @link https://github.com/HaruhiYunona
 * 
 */
header('Access-Control-Allow-Headers: *');
header("Access-Control-Allow-Origin: *");

require_once __DIR__."/core.php";

use Danmaku\Request;
use Danmaku\Router;

class index
{
    public function run()
    {
        Router::load(Request::path());
    }
}

$index = new index();
$index->run();
