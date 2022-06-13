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

require_once __DIR__ . "/core.php";

use Danmaku\Respons;

class autoloader
{
    public static function loader($class)
    {
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        if (strstr($path, DIRECTORY_SEPARATOR) !== false) {
            $namespace = substr($path, 0, strrpos($path, DIRECTORY_SEPARATOR));
            if ($namespace == 'Danmaku') {
                require_once __DIR__ . "/config.php";
                require_once __DIR__ . "/core.php";
            } else {
                $class = substr($path, strripos($path, DIRECTORY_SEPARATOR) + 1);
                $file = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $namespace . DIRECTORY_SEPARATOR . $class . '.php';
                if (file_exists($file)) {
                    require_once $file;
                    if (!class_exists($namespace.'\\'.$class)) {
                        return Respons::json(['code' => 404, 'msg' => '[Danmaku]你所访问的接口请求了一个自动加载的类文件,但未从中成功引入类']);
                    }
                } else {
                    return Respons::json(['code' => 404, 'msg' => '[Danmaku]你所访问的接口请求了一个自动加载的类文件,但未能找到该文件']);
                }
            }
        }
    }
}
spl_autoload_register('autoloader::loader');
