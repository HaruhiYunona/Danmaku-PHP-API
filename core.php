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

namespace Danmaku;


require_once __DIR__ . "/config.php";

use Danmaku\Config;
use PDO;
use PDOException;


/**
 * 路由类
 */
class Router
{
    /**
     * 解析路由
     * @param string $request
     * @return void
     */
    public static function load($request)
    {
        Config::options();
        $request = ltrim(str_replace('/' . ROOT, '', $request), '/');
        $path = explode('/', $request);
        if (count($path) <= 2 || !isset($path)) {
            return Respons::json(['code' => 501, 'msg' => '[Danmaku]缺失接口关键路径']);
        }
        if (!file_exists(__DIR__ . '/libs/' . $path[0] . '.php')) {
            return Respons::json(['code' => 404, 'msg' => '[Danmaku]未找到接口请求对应的插件']);
        }
        require_once __DIR__ . '/libs/' . $path[0] . '.php';
        if (!class_exists('Danmaku\\' . $path[1]) || !method_exists('Danmaku\\' . $path[1], $path[2])) {
            return Respons::json(['code' => 404, 'msg' => '[Danmaku]未找到接口所指定的操作']);
        }
        call_user_func('Danmaku\\' . $path[1] . '::' . $path[2]);
    }
}

/**
 * 请求类
 */
class Request
{

    /**
     * 获取URL指向路径
     * @return string
     */
    public static function path()
    {
        $request = $_SERVER['REQUEST_URI'];
        if (strstr($request, '?') !== false) {
            $request = substr($request, 0, strrpos($request, '?'));
        }
        return $request;
    }

    /**
     * 获取请求内容
     * @param string $name 需获取请求名
     * @param string $method 需获取请求类型,不填则优先输出有值的请求(默认为GET,可选GET/POST)
     * @return mixed;
     */
    public static function get($name, $method = null)
    {
        $request = $_SERVER['REQUEST_URI'];
        $request = explode('&', substr($request, strripos($request, '?') + 1));
        foreach ($request as $row) {
            preg_match('/^.*(?=\=)/', $row, $reqName);
            preg_match('/(?<=\=).*$/', $row, $reqValue);
            isset($reqName[0]) || $reqName[0] = null;
            isset($reqValue[0]) || $reqValue[0] = null;
            $reqs[$reqName[0]] = $reqValue[0];
        }
        $req = json_decode(file_get_contents('php://input'), true)[$name];
        isset($reqs[$name]) || $reqs[$name] = null;
        return ($method == null) ? (
            ($reqs[$name] !== null) ? ($reqs[$name]) : ($req)
        ) : (
            (strtoupper($method) == 'POST') ? ($req) : ($reqs[$name])
        );
    }

    /**
     * 获取所有请求
     * @param string $method 需请求类型(默认为AUTO,可选POST/GET/AUTO. AUTO即自动叠加GET与POST请求. 如果GET内已定义,POST的内容将不会被应用)
     * @return array
     */
    public static function all($method = 'AUTO')
    {
        switch (strtoupper($method)) {
            case 'GET':
                $request = $_SERVER['REQUEST_URI'];
                $request = explode('&', substr($request, strripos($request, '?') + 1));
                foreach ($request as $row) {
                    preg_match('/^.*(?=\=)/', $row, $reqName);
                    preg_match('/(?<=\=).*$/', $row, $reqValue);
                    isset($reqName[0]) || $reqName[0] = null;
                    isset($reqValue[0]) || $reqValue[0] = null;
                    $reqs[$reqName[0]] = $reqValue[0];
                }
                return $reqs;
                break;
            case 'POST':
                return json_decode(file_get_contents('php://input'), true);
                break;
            default:
                $request = $_SERVER['REQUEST_URI'];
                $request = explode('&', substr($request, strripos($request, '?') + 1));
                $posted = json_decode(file_get_contents('php://input'), true);
                foreach ((array)$request as $row) {
                    preg_match('/^.*(?=\=)/', $row, $reqName);
                    preg_match('/(?<=\=).*$/', $row, $reqValue);
                    isset($reqName[0]) || $reqName[0] = null;
                    isset($reqValue[0]) || $reqValue[0] = null;
                    if ($reqName[0] !== null && $reqValue[0] !== null) {
                        isset($posted[$reqName[0]]) || $posted[$reqName[0]] = NULL;
                        $reqs[$reqName[0]] = ($reqValue[0] != NULL) ? ($reqValue[0]) : ($posted[$reqName[0]]);
                    } else {
                        $reqs = null;
                    }
                }
                foreach ((array)$posted as $key => $row) {
                    isset($reqs[$key]) || $reqs[$key] = NULL;
                    $reqs[$key] = ($reqs[$key] == NULL) ? $row : $reqs[$key];
                }
                return $reqs;
                break;
        }
    }


    /**
     * 获取请求接口版本
     * @return string
     */
    public static function version()
    {
        Config::options();
        $request = Request::path();
        $request = ltrim(str_replace('/' . ROOT, '', $request), '/');
        if ($request != '') {
            $path = explode('/', $request);
            isset($path[3]) || $path[3] = null;
            return ($path[3] != null) ? strtolower($path[3]) : 'v3';
        } else {
            return 'v3';
        }
    }


    /**
     * CURL获取网页内容
     * @param string $link 网页链接
     * @param boolean $zip 是否启用GZIP压缩解析
     * @return mixed
     */
    public static function curlGet($url, $zip = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, trim($url));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, -1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
        if ($zip) {
            curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
        }
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }


    /**
     * 参数校验
     * @param array $param 参数
     * @param array $verifier 校验条件
     * @return array 
     */
    public static function validator($param, $verifier)
    {
        $return = ['code' => 0, 'msg' => []];
        foreach ($verifier as $key => $rules) {
            $rules = $verifier[$key];
            $rules = explode('|', $rules);
            foreach ($rules as $rule) {
                $rule = strtolower($rule);
                switch ($rule) {
                    case 'email':
                        $Regx = '/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/';
                        $need = '邮箱';
                        break;
                    case 'mobile':
                        $Regx = '/^1[34578]\d{9}$/';
                        $need = '电话号码';
                        break;
                    case 'en':
                        $Regx = '/^[a-zA-Z\s]+$/';
                        $need = '纯字母';
                        break;
                    case 'number':
                        $Regx = '/^[0-9]*$/';
                        $need = '纯数字';
                        break;
                    case 'zh':
                        $Regx = '/^[\u4e00-\u9fa5]{0,}$/';
                        $need = '纯中文';
                        break;
                    case 'url':
                        $Regx = '/^http://([\w-]+\.)+[\w-]+(/[\w-./?%&=]*)?$/';
                        $need = '链接';
                        break;
                    case 'id':
                        $Regx = '/^[0-9a-zA-Z]*$/';
                        $need = 'ID';
                        break;
                    case 'bv':
                        $Regx = '/BV{1}[a-zA-Z0-9]{10}/';
                        $need = 'BV号';
                        break;
                    case 'qq':
                        $Regx = '/[1-9][0-9]{4,}/';
                        $need = 'QQ号';
                        break;
                    case 'token':
                        $Regx = '/^[0-9a-zA-Z]{32}$/';
                        $need = '32位MD5';
                        break;
                    case 'float':
                        $Regx = '/(^-?[1-9]\d*\.\d+$|^-?0\.\d+$|^-?[1-9]\d*$|^0$)/';
                        $need = '浮点数';
                        break;
                    case preg_match('/between\s*\d*,\d*/', $rule):
                        preg_match('/(?<=between{1}\s)\d*(?=,)/', $rule, $min);
                        preg_match('/(?<=,)\d*/', $rule, $max);
                        $Regx = '/^.{' . $min . ',' . $max . '}$/';
                        break;
                    default:
                        $Regx = '//';
                        break;
                }
                isset($param[$key]) || $param[$key] = NULL;
                if ($param[$key] === null) {
                    if (!in_array('[Danmaku]参数验证 - [' . $key . '] 为空', $return['msg'])) {
                        $return['msg'][] = '[Danmaku]参数验证 - [' . $key . '] 为空';
                    }
                    if ($return['code'] == 0) {
                        $return['code'] = -1;
                    }
                } else {
                    if (!preg_match($Regx, $param[$key])) {
                        $return['msg'][] = '[Danmaku]参数验证 - [' . $key . '] 未通过格式校验:' . $need;
                        if ($return['code'] == 0) {
                            $return['code'] = -1;
                        }
                    }
                }
            }
        }
        $return['msg'] = (count($return['msg']) == 0) ? ['[Danmaku]参数验证通过'] : $return['msg'];
        return $return;
    }
}


/**
 * 响应类
 */
class Respons
{
    /**
     * 返回json参数
     * @param array $array 需要返回的数组
     * @param boolean $cache 是否对接口响应内容进行缓存,默认为是
     */
    public static function json(array $array, $cache = false)
    {
        Config::options();
        header('content-type:application/json;charset=utf-8');
        if ($cache) {
            $cache_time = API_CACHE_TIME;
            $modified_time = @$_SERVER['HTTP_IF_MODIFIED_SINCE'];
            if (strtotime($modified_time) + $cache_time > time()) {
                header("HTTP/1.1 304");
                exit;
            }
            header("Last-Modified: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
            header("Expires: " . gmdate("D, d M Y H:i:s", time() + $cache_time) . " GMT");
            header("Cache-Control: max-age=" . $cache_time);
        } else {
            header('Expires: Mon, 26 Jul 1970 05:00:00 GMT');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
        }
        echo json_encode(Respons::https($array), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * 自动修改返回的内容里包含链接部分http开头
     */
    public static function https($content)
    {
        Config::options();
        if (API_HTTPS) {
            if (is_array($content)) {
                $json = json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                return json_decode(str_replace('http://', 'https://', $json), true);
            } else {
                return str_replace('http://', 'https://', $content);
            }
        }else{
            return $content;
        }
    }
}


/**
 * 数据库类
 */
class Db
{
    /**
     * 链接数据库
     * @return PdoObject
     */
    public static function connect()
    {
        Config::db();
        try {
            return new PDO("mysql:host=" . DB_SERVER_NAME . ";dbname=" . DB_NAME . ";port=" . DB_PORT . ";charset=utf8;", DB_USER_NAME, DB_PASSWORD);
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }


    /**
     * 数据库查询语句
     * @param string $sql SQL语句
     * @param boolean $fetch 是否只输出第一行,默认全部输出
     * @return array/false
     */
    public static function query($sql, $fetch = false)
    {
        $result = Db::connect()->query($sql);
        if ($result == false || $result == null) {
            return false;
        }
        return ($fetch == true) ? $result->fetch() : $result->fetchAll();
    }


    /**
     * 数据库操作语句
     * @param string $sql SQL语句
     * @return int
     */
    public static function exec($sql)
    {
        return Db::connect()->exec($sql);
    }


    /**
     * 检查是否存在对应表
     * @param string $name 表名
     * @return boolean
     */
    public static function isTableExist($name)
    {
        Config::db();
        $result = Db::query('SHOW TABLES LIKE "' . TABLE_PRE_NAME . '_' . $name . '"', true);
        return ($result == false) ? false : true;
    }


    /**
     * 创建表(自带前缀).请注意不要输入uid作为键名,uid为本程序链表默认关键字
     * @param string $table 表名
     * @param array $keys 键名列表,请使用 ['keyName'=>['type','length']]指定键的属性。例如['sid'=>['int',10],'token'=>['varchar',32]]
     * @return boolean
     */
    public static function createTable($table, $keys)
    {
        Config::db();
        if ($table == null || !is_array($keys) || in_array('uid', $keys, true)) {
            return false;
        }
        $keyArray[] = '`uid` int(11) NOT NULL AUTO_INCREMENT';
        foreach ($keys as $key => $attribute) {
            if (!isset($attribute[0])) {
                return false;
            }
            $keyArray[] =  '`' . $key . '` ' . $attribute[0] . (!isset($attribute[1]) ? '' : '(' . $attribute[1] . ')') . ' NOT NULL';
        }
        $sql = 'CREATE TABLE `' . TABLE_PRE_NAME . '_' . $table . '` (' . join(',', $keyArray) . ',PRIMARY KEY (`uid`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
        Db::exec($sql);
        return Db::isTableExist($table);
    }


    /**
     * 向表格插入数据
     * @param string $table 表名
     * @param array $insert 需要插入的数据,请使用带指定下标的数组,例如['uid'=>2,token=>'AS23213F','time'=>1698200333]
     * @return boolean
     */
    public static function insertTable($table, $insert)
    {
        Config::db();
        if ($table == null || !is_array($insert)) {
            return false;
        }
        list($name, $var) = [[], []];
        foreach ($insert as $key => $value) {
            $name[] = $key;
            $var[] = (preg_match('/^\d*$/', $value) == 0) ? "'" . $value . "'" : $value;
        }
        $sql = 'INSERT INTO `' . TABLE_PRE_NAME . '_' . $table . '` (`' . join('`,`', $name) . '`) VALUES (' . join(',', $var) . ')';
        return (Db::exec($sql) >= 1) ? true : false;
    }

    /**
     * 根据表名,条件数组来查询内容
     * @param string $table 表名
     * @param array $search 条件数组
     * @return array/boolean
     */
    public static function get($table, $search, $fetch = true)
    {
        Config::options();
        if (!is_array($search) || empty($search) || empty($table)) {
            return false;
        }
        foreach ($search as $key => $value) {
            $serachList[] = ($key !== null && $value !== null) ? '`' . $key . '`=' . '\'' . $value . '\'' : '';
        }
        if (empty($serachList)) {
            return false;
        }
        $sql = 'SELECT * FROM `' . TABLE_PRE_NAME . '_' . $table . '` WHERE ' . join(' AND ', $serachList);
        return Db::query($sql, $fetch);
    }

    /**
     * 更改指定表的指定参数
     * @param string $table 表名
     * @param array $change 改变的内容数组
     * @param array $search 被改变的对象数组
     * @return boolean
     */
    public static function set($table, $change, $search)
    {
        Config::options();
        if (!is_array($change) || empty($change) || empty($table)) {
            return false;
        }

        foreach ($change as $key => $value) {
            $changeList[] = ($key !== null && $value !== null) ? '`' . $key . '`=' . '\'' . $value . '\'' : '';
        }
        $searchList = Db::get($table, $search, false);
        foreach ($searchList as $row) {
            $uidList[] = $row['uid'];
        }
        if (empty($changeList) || !isset($uidList)) {
            return false;
        }
        $sql = 'UPDATE `' . TABLE_PRE_NAME . '_' . $table . '` SET ' . join(', ', $changeList) . ' WHERE `' . TABLE_PRE_NAME . '_' . $table . '`.`uid` IN (' . join(',', $uidList) . ');';
        return (Db::exec($sql) >= 1) ? true : false;
    }
}
