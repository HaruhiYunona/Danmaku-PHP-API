<?php

/**
 * Bilibili parse PHP Api
 * 
 * @package Danmaku
 * @author HaruhiYunona
 * @version 1.0.0
 * @link https://github.com/HaruhiYunona
 * 
 */

namespace Danmaku;


require_once __DIR__ . "/../autoLoader.php";

use Danmaku\Request;
use Danmaku\Respons;
use Injahow\Bilibili;

//Bilibili解析类
class parse
{
    /**
     * B站视频播放地址解析
     */
    public static function video()
    {
        $data = Request::all();
        $verify = Request::validator($data, [
            'bv' => 'bv',
            'p' => 'number|between 0,10'
        ]);
        if ($verify['code'] == -1) {
            return Respons::json(['code' => 502, 'msg' => join(';', $verify['msg'])]);
        }
        isset($data['type']) || $data['type'] = 'video';
        isset($data['ep']) || $data['ep'] = null;
        isset($data['bv']) || $data['bv'] = null;
        isset($data['p']) || $data['p'] = 1;
        isset($data['q']) || $data['q'] = 80;
        isset($data['format']) || $data['format'] = 'mp4';
        list($type, $ep, $bv, $p, $q, $format) = [$data['type'], $data['ep'], $data['bv'], $data['p'], $data['q'], $data['format']];
        $bp = new Bilibili($type);
        $bp->epid($ep);
        $bp->bvid($bv)->page($p);
        $bp->quality($q)->format($format);
        $result = json_decode($bp->result(), true);
        return Respons::json($result, true);
    }

    /**
     * B站视频ID链解析
     */
    public static function chain()
    {
        //定义变量并校验
        list($bv, $aid, $page) = [Request::get('bv'),  Request::get('aid'), Request::get('p')];
        ($page == null) && $page = 1;
        $verify = ($aid != null) ?
            Request::validator(['aid' => $aid, 'page' => $page], [
                'aid' => 'number|between 1,15',
                'page' => 'number|between 1,10'
            ]) :
            Request::validator(['bv' => $bv, 'page' => $page], [
                'bv' => 'bv',
                'page' => 'number|between 1,10',
            ]);
        if ($verify['code'] == -1) {
            return Respons::json(['code' => 502, 'msg' => join(';', $verify['msg'])]);
        }

        //获取视频整个查询链信息
        $url = 'https://api.bilibili.com/x/web-interface/view/detail?' . ((empty($bv)) ? 'aid=' . $aid : 'bvid=' . $bv);
        $content = json_decode(Request::curlGet($url, true), true);
        if ($content['code'] !== 0) {
            return Respons::json(['code' => 404, 'msg' => '获取视频信息失败']);
        }

        return Respons::json(['code' => 0, 'data' => [
            'bv' => $content['data']['View']['bvid'],
            'aid' => $content['data']['View']['aid'],
            'cid' => $content['data']['View']['pages'][$page - 1]['cid'],
            'p' => $page
        ]], true);
    }

    /**
     * B站视频基本信息获取
     */
    public static function detail()
    {
        $bv = Request::get('bv');
        $p = Request::get('p');
        isset($p) || $p = 1;
        $verify = Request::validator(['bv' => $bv, 'p' => $p], [
            'bv' => 'bv',
            'p' => 'number|between 0,10'
        ]);
        if ($verify['code'] == -1) {
            return Respons::json(['code' => 502, 'msg' => join(';', $verify['msg'])]);
        }

        $content = json_decode(Request::curlGet('https://api.bilibili.com/x/web-interface/view/detail?bvid=' . $bv, true), true);
        if ($content['code'] !== 0) {
            return Respons::json(['code' => 404, 'msg' => '获取视频信息失败']);
        }

        return Respons::json(['code' => 0, 'data' => [
            'cid' => $content['data']['View']['cid'],
            'page' => $content['data']['View']['pages'][$p - 1]['page'],
            'title' => $content['data']['View']['title'],
            'part' => $content['data']['View']['pages'][$p - 1]['part'],
            'desc' => $content['data']['View']['desc'],
            'author' => $content['data']['Card']['card']['name'],
            'mid' => (int)$content['data']['Card']['card']['mid'],
            'face' => $content['data']['Card']['card']['face'],
            'time' => $content['data']['View']['ctime'],
            'pic' => $content['data']['View']['pages'][$p - 1]['first_frame']
        ]], true);
    }

    /**
     * B站视频TAG获取
     */
    public static function tag()
    {
        $aid = Request::get('aid');
        $verify = Request::validator(['aid' => $aid], [
            'aid' => 'number|between 1,15',
        ]);
        if ($verify['code'] == -1) {
            return Respons::json(['code' => 502, 'msg' => join(';', $verify['msg'])]);
        }

        $content = json_decode(Request::curlGet('https://api.bilibili.com/x/web-interface/view/detail/tag?aid=' . $aid, true), true);
        if ($content['code'] !== 0) {
            return Respons::json(['code' => 404, 'msg' => '获取视频信息失败']);
        }

        foreach ($content['data'] as $tag) {
            $tags[] = ['id' => $tag['tag_id'], 'name' => $tag['tag_name']];
        }

        return Respons::json(['code' => 0, 'data' => $tags], true);
    }

    /**
     * 获取视频评论
     */
    public static function reply()
    {
        $aid = Request::get('aid');
        $page = Request::get('page');
        isset($page) || $page = 1;
        $verify = Request::validator(['aid' => $aid, 'page' => $page], [
            'aid' => 'number|between 1,15',
            'page' => 'number|between 1,10'
        ]);
        if ($verify['code'] == -1) {
            return Respons::json(['code' => 502, 'msg' => join(';', $verify['msg'])]);
        }

        $url = 'https://api.bilibili.com/x/v2/reply/main?oid=' . $aid . '&type=1&next=' . $page;
        $content = json_decode(Request::curlGet($url, true), true);
        if ($content['code'] !== 0) {
            return Respons::json(['code' => 404, 'msg' => '获取视频信息失败']);
        }

        foreach ($content['data']['replies'] as $reply) {
            if (is_array($reply['replies'])) {
                foreach ($reply['replies'] as $comment) {
                    $comments[] = [
                        'name' => $comment['member']['uname'],
                        'sex' => $comment['member']['sex'],
                        'sign' => $comment['member']['sign'],
                        'avatar' => $comment['member']['avatar'],
                        'content' => $comment['content']['message'],
                        'time' => $comment['ctime'],
                        'like' => $comment['like'],
                    ];
                }
            }
            empty($comments) && $comments = [];
            $replies[] = [
                'mid' => $reply['mid'],
                'name' => $reply['member']['uname'],
                'sex' => $reply['member']['sex'],
                'sign' => $reply['member']['sign'],
                'avatar' => $reply['member']['avatar'],
                'level' => $reply['member']['level_info']['current_level'],
                'content' => $reply['content']['message'],
                'time' => $reply['ctime'],
                'like' => $reply['like'],
                'comment' => $comments
            ];
            $comments = [];
        }
        return Respons::json(['code' => 0, 'data' => $replies]);
    }

    /**
     * B站用户信息获取
     */
    public static function user()
    {
        $mid = Request::get('mid');
        $verify = Request::validator(['mid' => $mid], [
            'mid' => 'number|between 1,15',
        ]);
        if ($verify['code'] == -1) {
            return Respons::json(['code' => 502, 'msg' => join(';', $verify['msg'])]);
        }

        $b_page = Request::curlGet('https://space.bilibili.com/' . $mid , true);
        preg_match('/(?<=window.__INITIAL_STATE__=).*?(?=;\(function\(\)\{)/', $b_page, $str);
        $allInfo = json_decode($str[0], true);
        $array = $allInfo['space']['info'];
        if (empty($array['mid'])) {
            return Respons::json(['code' => 404, 'msg' => '未找到该用户']);
        }

        $fansCard = ($array['fans_medal']['wear']) ?
            [
                'name' => $array['fans_medal']['medal']['medal_name'],
                'level' => $array['fans_medal']['medal']['level'],
                'mid' => $array['fans_medal']['medal']['target_id']
            ] : [
                'name' => null,
                'level' => null,
                'target' => null
            ];

        return Respons::json(['code' => 0, 'data' => [
            'mid' => $array['mid'],
            'name' => $array['name'],
            'sex' => $array['sex'],
            'face' => $array['face'],
            'level' => $array['level'],
            'banner' => $array['top_photo'],
            'fans_card' => $fansCard
        ]], true);
    }
}
