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


require_once __DIR__ . "/../autoLoader.php";

use Danmaku\Config;
use Danmaku\Db;
use Danmaku\Request;
use Danmaku\Respons;
use XMLReader;


class bilibili
{
    /**
     * 获取B站弹幕
     * @return json
     */
    public static function get()
    {
        Config::options();

        //判断请求接口是否开放
        $apiVersion = Request::version();
        if (($apiVersion == 'v2') ? (
            (PORT_VERSION_DUB == true) ? false : true
        ) : (
            (PORT_VERSION_TRD == true) ? false : true
        )) {
            return Respons::json(['code' => 404, 'msg' => '[Danmaku]请求的接口版本已被禁用']);
        }

        //定义变量并校验
        list($cid, $bv, $page) = [Request::get('cid'), Request::get('bv'), Request::get('page')];
        $verify = ($cid != null) ?
            Request::validator(['cid' => $cid], [
                'cid' => 'number|between 1,10'
            ]) :
            Request::validator(['bv' => $bv], [
                'bv' => 'bv',
            ]);
        if ($verify['code'] == -1) {
            return Respons::json(['code' => 502, 'msg' => join(';', $verify['msg'])]);
        }

        //如果不是通过CID获取弹幕,先通过BV获取CID
        if ($cid == null) {
            ($page == null) && $page = 1;
            $verify = Request::validator(['page' => $page], [
                'page' => 'number|between 1,10',
            ]);
            if ($verify['code'] == -1) {
                return Respons::json(['code' => 502, 'msg' => join(';', $verify['msg'])]);
            }
            $videoInfo = json_decode(Request::curlGet('https://api.bilibili.com/x/player/pagelist?bvid=' . $bv . '&jsonp=jsonp'), true);
            if ($videoInfo['code'] == 0) {
                $cid = $videoInfo['data'][$page - 1]['cid'];
            }
        }

        //如果获取CID失败,返回失败JSON
        if ($cid == null) {
            return Respons::json(['code' => 404, 'msg' => '[Danmaku]通过BV查询CID失败']);
        }

        //获取弹幕XML并解析
        $reader = new XMLReader();
        $reader->xml(Request::curlGet('https://api.bilibili.com/x/v1/dm/list.so?oid=' . $cid, true));
        list($danmakuData, $danmakuText, $danmaku[], $node) = [[], null, [0, 0, '16777215', 'f5211eb2', ''], null];
        while ($reader->read()) {
            ($reader->nodeType == XMLReader::ELEMENT && $reader->localName == 'd') &&
                list($node, $danmakuData) = [$reader->name, explode(',', $reader->getAttribute('p'))];
            ($reader->nodeType == XMLReader::TEXT && $node == 'd') &&
                $danmakuText = $reader->value;
            ($danmakuData !== null && $danmakuText !== null) &&
                list($danmakuData, $danmakuText, $danmaku[]) = [null, null, [(float)$danmakuData[0], (int)$danmakuData[5], $danmakuData[3], $danmakuData[6], $danmakuText]];
        }

        //响应接口请求:返回弹幕JSON
        $result = ($apiVersion == 'v2') ?
            [
                'code' => 0,
                'version' => 2,
                'danmaku' => $danmaku,
                'msg' => '[Danmaku]B站弹幕加载完成'
            ] :
            [
                'code' => 0,
                'version' => 3,
                'data' => $danmaku,
                'msg' => '[Danmaku]B站弹幕加载完成'
            ];
        return Respons::json($result);
    }
}

class server
{
    /**
     * 处理Dplayer弹幕请求
     * @return json
     */
    public static function entrance()
    {
        if (!Db::isTableExist('pool')) {
            if (!Db::createTable('pool', [
                'user' => ['varchar', 50],
                'vid' => ['varchar', 50],
                'text' => ['text', null],
                'color' => ['varchar', 20],
                'type' => ['varchar', 20],
                'time' => ['varchar', 20]
            ])) {
                return Respons::json(['code' => 404, 'msg' => '[Danmaku]数据库配置出现错误,请联系管理员进行检查']);
            }
        }
        if (Request::get('text', 'POST') !== null) {
            server::post();
        } else {
            server::get();
        }
    }

    /**
     * 获取本站弹幕
     * @return json
     */
    public static function get()
    {
        Config::options();
        //判断请求接口是否开放
        $apiVersion = Request::version();
        if (($apiVersion == 'v2') ? (
            (PORT_VERSION_DUB == true) ? false : true
        ) : (
            (PORT_VERSION_TRD == true) ? false : true
        )) {
            return Respons::json(['code' => 404, 'msg' => '[Danmaku]请求的接口版本已被禁用']);
        }

        //获取参数
        $id = Request::get('id');
        $verify = Request::validator(['id' => $id], [
            'id' => 'id|between 1,50',
        ]);
        if ($verify['code'] == -1) {
            return Respons::json(['code' => 502, 'msg' => join(';', $verify['msg'])]);
        }

        //查询弹幕池
        $result = Db::get('pool', ['vid' => $id], false);
        if ($result == false) {
            $result = [['time' => 0, 'type' => 0, 'color' => '0', 'user' => '0', 'text' => '']];
        }

        foreach ($result as $data) {
            $danmaku[] = [(float)$data['time'], (int)$data['type'], $data['color'], $data['user'], $data['text']];
        }


        //响应接口请求:返回弹幕JSON
        $result = ($apiVersion == 'v2') ?
            [
                'code' => 0,
                'version' => 2,
                'danmaku' => $danmaku,
                'msg' => '[Danmaku]本站弹幕加载完成'
            ] :
            [
                'code' => 0,
                'version' => 3,
                'data' => $danmaku,
                'msg' => '[Danmaku]本站弹幕加载完成'
            ];
        return Respons::json($result);
    }


    /**
     * 提交弹幕
     */
    public static function post()
    {
        Config::options();

        $postData = Request::all('POST');
        //校验参数
        $verify = Request::validator($postData, [
            'author' => 'id|between 1,50',
            'color' => 'between 1,20',
            'id' => 'id|between 1,50',
            'text' => 'between 1,30',
            'time' => 'float|between 1,20',
            'token' => 'token',
            'type' => 'number|between 1,20'
        ]);
        if ($verify['code'] == -1) {
            return Respons::json(['code' => 502, 'msg' => join(';', $verify['msg'])]);
        }

        //token校验:
        if (TOKEN_VERIFY && strtoupper($postData['token']) != strtoupper(md5($postData['author'] . $postData['id']))) {
            return Respons::json(['code' => 506, 'msg' => 'TOKEN不正确']);
        }

        //向弹幕池插入数据
        return (Db::insertTable('pool', [
            'user' => $postData['author'],
            'vid' => $postData['id'],
            'text' => $postData['text'],
            'color' => $postData['color'],
            'type' => $postData['type'],
            'time' => $postData['time']
        ])) ? (Respons::json([
            'code' => 0,
            'msg' => '弹幕发送成功~'
        ])) : (Respons::json([
            'code' => 500,
            'msg' => '发送弹幕出现了错误'
        ]));
    }
}
