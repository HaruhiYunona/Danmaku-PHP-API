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

/******************************************************************\
 *                CopyRight 2022, HaruhiYunona                    *          
 *                Released under the MIT license                  *
\******************************************************************/

namespace Danmaku;


class Config
{

    /**
     * 数据库配置方法
     */
    public static function db()
    {
        //数据库服务器地址
        defined('DB_SERVER_NAME') ||
            define("DB_SERVER_NAME", "localhost");

        //数据库用户名
        defined('DB_USER_NAME') ||
            define("DB_USER_NAME", "root");

        //数据库用户密码
        defined('DB_PASSWORD') ||
            define("DB_PASSWORD", "123456");

        //数据库名
        defined('DB_NAME') ||
            define("DB_NAME", "danmaku");

        //端口
        defined('DB_PORT') ||
            define("DB_PORT", "3306");
    }
    /**
     * 接口配置方法
     */
    public static function options()
    {
        //根目录
        defined('ROOT') ||
            define("ROOT", "Danmaku");

        //数据表前缀
        defined('TABLE_PRE_NAME') ||
            define("TABLE_PRE_NAME", "danmaku");

        //接口版本V3
        defined('PORT_VERSION_TRD') ||
            define("PORT_VERSION_TRD", true);

        //接口版本V2
        defined('PORT_VERSION_DUB') ||
            define("PORT_VERSION_DUB", true);

        //是否开启token校验
        defined('TOKEN_VERIFY') ||
            define("TOKEN_VERIFY", true);

        //cache
        defined('API_CACHE_TIME') ||
            define('API_CACHE_TIME', 600);

        //SSL。设定接口返回数据是Https开头还是Http开头。默认Http开头
        defined('API_HTTPS') ||
            define('API_HTTPS', false);
    }
}
