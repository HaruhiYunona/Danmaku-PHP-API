# Danmaku PHP API - 一个功能丰富的Dplayer.JS配套的视频站解析API

![](https://mdzz.pro/usr/uploads/2022/06/1211219036.jpg)

### 前言:

##### 本API是一个弹幕服务器API, 为DPlayer提供弹幕服务器支持。还包含丰富的扩展插件,可以提供额外的解析服务(目前支持Bilibili)。该API同时支持DPlayer V2 V3接口,自由度较高。

**以下是本API已经实装的实例(下面介绍的其他API也可以照格式使用,不过就不一一赘述):**

> **整个API实际应用的Demo:**  https://api.mdzz.pro/demo/
>
> **B站视频解析Demo:**  https://api.mdzz.pro/bilibili/parse/video?bv=BV1Na411r7tN&p=1
>
> **B站弹幕解析Demo:**  https://api.mdzz.pro/danmaku/bilibili/get/v3/?bv=BV1Na411r7tN
>
> **本站弹幕Demo:**  https://api.mdzz.pro/danmaku/server/entrance/v3/?id=BV1Na411r7tN
> 
> **我的个人站:**  https://mdzz.pro/

### 一、安装

**本接口为即装即用式,将内容放置于服务器网站根目录下,绑定接口域名后,配置根目录下config.php即可使用。不过您需要对本API设置伪静态规则才能正常访问。**

**1) 修改config.php配置**

> 配置字段列表    define("配置名", "配置的值"):

| 配置名           | 配置可能值                            | 默认      | 作用                                                         |
| ---------------- | ------------------------------------- | --------- | ------------------------------------------------------------ |
| DB_SERVER_NAME   | 您的数据库地址。如为本机请填localhost | localhost | 数据库链接时应用的数据库地址                                 |
| DB_USER_NAME     | 您的数据库用户名                      | root      | 数据库链接时应用的数据库用户名                               |
| DB_PASSWORD      | 您的数据库用户密码                    | 123456    | 数据库链接时应用的数据库密码                                 |
| DB_NAME          | 您的数据库名                          | danmaku   | 数据库链接时应用的数据库名                                   |
| DB_PORT          | 您的数据库端口                        | 3306      | 数据库链接时应用的数据库端口                                 |
| ROOT             | 网站域名                              | Danmaku   | 在将本API作为网站子文件夹使用时是一个有效的筛除多余路径的设置。它强制定义了本API的根目录名称 |
| TABLE_PRE_NAME   | /^[a-zA-Z0-9]+$/                      | danmaku   | 用于定义API所创建表的前缀,防止与其他项目共用同一个库时，数据表混淆 |
| PORT_VERSION_TRD | boolean                               | true      | 用于控制API接口版本开放(针对DPlayer)。不同版本的接口返回内容不一样 |
| PORT_VERSION_DUB | boolean                               | true      | 同上                                                         |
| TOKEN_VERIFY     | boolean                               | true      | 设置在用户向API发送弹幕时是否需要正确的token,如果不需要验证token,可设置关闭 |
| API_CACHE_TIME   | number                                | 600       | 用于控制缓存时间。部分允许缓存结果的缓存会生成缓存以节省服务器开支 |
| API_HTTPS        | boolean                               | false     | 是否将接口返回的内容中URL全部转换为HTTPS                     |



**2) 设置伪静态**

您需要在API根目录新建一个.htaccess 文件,然后写入以下对应的内容

> Nginx设置伪静态

```php
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php last;
    }
//如果您使用面板,可以直接在面板的网站管理内伪静态选项写入这个

```

> Apache设置伪静态

```shell
Options -MultiViews
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ /index.php [QSA,L]
```

配置好后您应该就可以使用本API了。DEMO文件夹下有demo演示,但需要您根据注释修改一点内容才可以尝试demo。



### 二、API介绍

**由于第三方API是通过域名面向服务对象的，所以以下接口域名均以<u>domain.com</u>示例。实际使用请将domain.com替换为您绑定的的域名**



#### 1、本站弹幕API

**由于DPlayer的设计,会自动在接口上增加/V3,该API将提供存储在本站的弹幕。如果您要请求V3版本弹幕,请在接口后加上V3, 请求V2弹幕同理**

> <u>http://domain.com/danmaku/server/entrance/</u>

##### 1) 允许请求类型

**POST     GET**

##### 2) 请求内容

###### GET:获取本站弹幕

| 参数 | 请求允许值                                                   |
| ---- | ------------------------------------------------------------ |
| id   | 您在程序设计时所使用的视频ID。以Bilibili为例,您用BV作为id的话就会获取该BV所记录的弹幕。除此之外还可以用aid,cid,自己设计的id等 |

###### POST:向本站服务器发送一条弹幕

| 参数   | 请求允许值            | 备注                                                     |
| ------ | --------------------- | -------------------------------------------------------- |
| author | /^[a-zA-Z0-9]{1,50}$/ | 发送弹幕的用户ID                                         |
| color  | /^[a-zA-Z0-9]{1,50}$/ | 发送弹幕的颜色。16进制色。                               |
| id     | /^[a-zA-Z0-9]{1,50}$/ | 视频ID。您可以根据自己需要设置id。同GET的id,用于识别视频 |
| text   | string                | 发送的弹幕内容                                           |
| time   | float                 | 弹幕处于视频的时间。浮点数                               |
| token  | MD5(32bit)            | TOKEN用于验证用户身份。默认为32位MD5码,逻辑为author+id   |
| type   | number                | 弹幕类型,控制弹幕是滚动还是悬停消失                      |

##### 3) 响应样本

```json
//GET返回弹幕(v2):
{
	"code": 0, //状态码
	"version": 2, //版本号
	"danmaku": [  //弹幕内容
		[1.981745, 0, "16777215", "e29a22", "测试弹幕"],
		[4.155057, 0, "16777215", "deea32", "测试"]
      // 出现时间   类型  颜色        用户ID    弹幕内容
	],
	"msg": "[Danmaku]本站弹幕加载完成"  //消息内容
}

//GET返回弹幕(V3)
{
	"code": 0,
	"version": 3,
	"data": [
		[1.981745, 0, "16777215", "e29a22", "测试弹幕"],
		[4.155057, 0, "16777215", "deea32", "测试"]
	],
	"msg": "[Danmaku]本站弹幕加载完成"
}

//POST返回发送弹幕的执行结果
{
	"code": 0,
	"msg": "弹幕发送成功~"
}

//所有接口成功状态码均为0,如果失败会是别的参数。故代码判断返回结果中code即可判断执行结果
```



##### 4) 调用代码示例(以DPlayer为例。如果是自己写的播放器需要自行写代码)

```javascript
                   let domain = 'htts://mydomain.com';
                   let map = {bv:'BVtesttest00',user:'123333',url:'https://xxxxx.com/xxx.mp4'};
                   let dp = new DPlayer({
                        element: document.getElementById('dplayer'),
                        loop: true, //自动循环播放
                        lang: 'zh-cn', //播放器语言
                        hotkey: true, //开启热键控制
                        autoplay: true, //自动播放
                        volume: 1, //音量大小
                        playbackSpeed: '[0.5,0.75,1,1.25,1.5,2]', //播放速度调节
                        preload: 'auto', //预加载方式
                        video: {
                            url: map['url'], //视频链接
                        },
                        danmaku: {
                            id: map['bv'], //视频对应的弹幕ID,这里我默认是BV
                            api: domain + '/danmaku/server/entrance/',
                            token: md5(map['user'] + map['bv']), //TOKEN
                            unlimited: true, //弹幕数量限制
                            user: map['user'], //用户身份信息
                            addition: [domain + '/danmaku/bilibili/get/v3/?bv=' + map['bv']] //外挂弹幕
                        }
                    });
```



#### 2、B站弹幕API

##### 该API用于向B站拉取弹幕。由于B站改版,老版本json接口已经取消,所以该接口会自动获取xml格式弹幕并解析。该代码响应结果与 *1、本站弹幕API*  一致

> http://domain.com/danmaku/bilibili/get/

##### 1) 允许请求类型

**GET**

##### 2) 请求内容

###### GET:获取B站视频弹幕

| 参数 | 请求允许值                                    | 备注                                  |
| ------ | --------------------------------------------- | --------------------------------------------- |
| bv     | BV号 | B站BV号,例如BV23z2i1oPc2 |



#### 3、B站视频解析API

##### 该API仅可以解析UP主上传的视频地址。其核心来自于 https://github.com/injahow/bilibili-parse

> http://domain.com/bilibili/parse/video

##### 1) 允许请求类型

**GET**

##### 2) 请求内容

###### GET:获取B站视频链接

| 参数 | 请求允许值 | 默认 | 备注 |
| ------ | ---------- | ---- | ---- |
| bv | BV号 | - | B站BV号,例如BV23z2i1oPc2 |
| p | >=1 | 1 | 视频分P |
| q | 16/32/64/80 | 80 | 视频清晰度 |
| type | video/bangumi | video | 视频类型 |
| format | flv/dash/mp4 | mp4 | 视频格式 |

##### 3) 响应样本

```json
{
	"code": 0,  //状态码
	"quality": 208,  //视频画质
	"accept_quality": [208],  //视频允许的画质
	"url": "https://cn-hljheb-dx-v-04.bilivideo.com/upgcxcode/71/48/469894871/469894871-1-208.mp4?e=ig8euxZM2rNcNbhBhwdVhwdlhzUVhwdVhoNvNC8BqJIzNbfqXBvEuENvNC8aNEVEtEvE9IMvXBvE2ENvNCImNEVEIj0Y2J_aug859r1qXg8gNEVE5XREto8z5JZC2X2gkX5L5F1eTX1jkXlsTXHeux_f2o859IB_&ua=tvproj&deadline=1655056415&gen=playurl&nbs=1&oi=2095000411&os=bcache&trid=0000bf6a30fd45e3445b907202783d6b7851&uipk=5&upsig=e4e6775f93bf43310b633d18a97404ff&uparams=e,ua,deadline,gen,nbs,oi,os,trid,uipk&mid=0"             //视频地址
}

```

#### 4、B站视频ID关系链API

##### 该API主要用于解析出视频的各种ID,以方便后面的接口内容查询

> http://domain.com/bilibili/parse/chain

##### 1) 允许请求类型

**GET**

##### 2) 请求内容

###### GET:获取B站视频ID关系链

| 参数 | 请求允许值 | 默认 | 备注                                    |
| ---- | ---------- | ---- | --------------------------------------- |
| bv   | BV号       | -    | B站BV号,例如BV23z2i1oPc2。与aid二选其一 |
| aid  | 视频aid    | -    | 视频aid。与BV二选其一                   |
| p    | >=1        | 1    | 视频分P                                 |

##### 3) 响应样本

```json
{
	"code": 0, //状态码
	"data": { //数据内容
		"bv": "BV1Na411r7tN",   //视频BV号
		"aid": 210182364,   //视频aid
		"cid": 469894871,   //视频cid
		"p": "1"    //分p
	}
}


```

#### 5、B站视频详情API

**该API主要用于解析视频详情**

> http://domain.com/bilibili/parse/detail

##### 1) 允许请求类型

**GET**

##### 2) 请求内容

###### GET:获取B站视频ID关系链

| 参数 | 请求允许值 | 默认 | 备注                       |
| ---- | ---------- | ---- | -------------------------- |
| bv   | BV号       | -    | B站BV号,例如BV23z2i1oPc2。 |
| p    | >=1        | 1    | 视频分P                    |

##### 3) 响应样本

```json
{
	"code": 0,  //状态码
 	"data": {  //数据内容
		"cid": 469894871,  //视频CID
		"page": 1,   //视频分p
		"title": "【VRChat】一人一只不要抢 ！！！",  //视频合集名称
		"part": "【VRChat】一人一只不要抢",  //视频名称(如果视频合集只有1集,则视频名称与合集相同)
		"desc": "游戏名：VRChat\nbgm：大喜\n模型：mia\n世界：MMD Dance World\n非常感谢@桜夜幽梦Channel  @鱼香ruosi  以及其他两位朋友的参加\n希望各位客官能喜欢！",    //视频描述
		"author": "未文无心",  //视频作者
		"mid": 148830743,   //视频作者id
		"face": "https://i2.hdslb.com/bfs/face/daaa54417c750c9f04846965f13371e54679471d.jpg",  //视频作者头像
		"time": 1640609730,   //视频上传时间
		"pic": "https://i1.hdslb.com/bfs/storyff/n211227qn2x3fivm0ghhd02mg4jqnvup_firsti.jpg"   //视频头图
	}
}
```

#### 6、B站视频TAG获取API

**该API主要解析B站视频TAG内容**

> http://domain.com/bilibili/parse/tag

##### 1) 允许请求类型

**GET**

##### 2) 请求内容

###### GET:获取B站视频ID关系链

| 参数 | 请求允许值 | 备注    |
| ---- | ---------- | ------- |
| aid  | 视频aid    | 视频aid |

##### 3) 响应样本

```json
{
	"code": 0,  //状态码
	"data": [{ //返回的数据。这里的data是一个数组.里面包含了很多有id和name的tag数组
		"id": 5007682,
		"name": "VRChat"
	}]
}
```

#### 7、B站视频评论解析API

**该API主要用于获取视频评论**

> http://domain.com/bilibili/parse/reply

##### 1) 允许请求类型

**GET**

##### 2) 请求内容

###### GET:获取B站视频ID关系链

| 参数 | 请求允许值 | 备注       |
| ---- | ---------- | ---------- |
| aid  | 视频aid    | 视频aid    |
| page | number     | 评论区页码 |

##### 3) 响应样本

```json
{
	"code": 0,   //状态码
	"data": [{   //这里返回的是一个个评论数组
		"mid": 190206497,  //评论人ID
		"name": "地jo人",   //评论人昵称
		"sex": "男",       //评论人性别
		"sign": "嘤",      //评论人签名
		"avatar": "https://i2.hdslb.com/bfs/face/665043557f9234e2e17ef5688df1ce1153e9147d.jpg",  //评论人头像
		"level": 5,      //评论人等级
		"content": "1：27[doge]",   //评论内容
		"time": 1640760764,    //评论时间
		"like": 1209,     //点赞数
		"comment": [{     //回复该条评论的数组。以下数组内容与评论内容一致
			"name": "qweuir",
			"sex": "男",
			"sign": "mc",
			"avatar": "https://i1.hdslb.com/bfs/face/d963af73c334cb8968dd44d588e44cb4393b5ba3.jpg",
			"content": "我的头像说明了我是什么人[tv_doge]",
			"time": 1640775787,
			"like": 21
		}, {
			"name": "城云_",
			"sex": "男",
			"sign": "诶嘿，诶嘿嘿嘿嘿（",
			"avatar": "https://i0.hdslb.com/bfs/face/7517480dd4cf4b73adb3e8463091236a8c592111.jpg",
			"content": "回复 @bili_1637658766 :我觉得我的也能证明[doge]",
			"time": 1640965784,
			"like": 3
		}, {
			"name": "银姜",
			"sex": "女",
			"sign": "ヾ(*´∀｀*)ﾉ",
			"avatar": "https://i2.hdslb.com/bfs/face/066b43b60f47f682e44b8c8a9081961d402cd466.jpg",
			"content": "up主觉得很涩[doge]",
			"time": 1642324392,
			"like": 3
		}]
	}]
}
```

#### 8、B站用户公开信息获取API

**该API用于获取B站对应mid用户的公开信息。请注意调动次数,频繁调动会被暂时封禁**

> http://domain.com/bilibili/parse/user

##### 1) 允许请求类型

**GET**

##### 2) 请求内容

###### GET:获取B站视频ID关系链

| 参数 | 请求允许值 | 备注                   |
| ---- | ---------- | ---------------------- |
| mid  | 用户mid    | 用户的mid,该id是唯一的 |

##### 3) 响应样本

```json
{
	"code": 0,  //状态码
	"data": {   //返回数据
		"mid": 104092258,  //用户mid
		"name": "绘理绘气星川真紀",   //用户昵称
		"sex": "保密",     //用户性别
		"face": "https://i1.hdslb.com/bfs/face/3330947c5e377abea9040560206e92fb94732f62.jpg",   //用户头像
		"level": 4,   //用户等级
		"banner": "https://i0.hdslb.com/bfs/space/cb1c3ef50e22b6096fde67febe863494caefebad.png",   //用户banner
		"fans_card": {   //用户粉丝牌
			"name": "绘理理",    //粉丝牌名字
			"level": 24,    //粉丝牌等级
			"mid": 278296    //粉丝牌所属主播id
		}
	}
}
```



### 三、API扩展

**本API支持扩展功能和移除功能,工作原理非常简单,就是将对应的php类文件放进对应的文件夹。**

#### 1、libs文件夹

**该文件夹用于存放接口插件。例如我有一个名为`apidemo.php`的文件(注意:插件命名空间必须为Danmaku)**

```php
// apidemo.php
<?php
    
    namespace Danmaku;
    
    class test{
          public static function abc(){
              ...
          }
    }
```

将apidemo.php放置于libs下,用户访问

> http://domain.com/apidemo/test/abc

即可访问到接口内容



#### 2、src文件夹

**该文件夹用于存放接口文件所引用的外部类,需要有不同的命名空间。外部类需要一层基本的命名空间为名字的文件夹,以及以类名为名字的php文件**

**例如我有一个名为demophp的文件夹,里面有一个demosrc.php的文件。**

```php
// Demophp/demosrc.php
<?php
    
    namespace Demophp;

    class demosrc{
        public static function abc(){
            ...
        }
    }
```

**在lib中的插件如果想要引用这个类:**

```php
<?php

namespace Danmaku; //插件命名空间必须为Danmaku

require_once __DIR__ . "/../autoLoader.php";   //引用自动加载文件

use Demophp\demosrc;  //引用外部类

demosrc::abc();调用demosrc内的abc方法
```

**了解以上基本文件夹的用途您也就可以开始编写或者为api添加插件了**



### 四、插件开发注意事项

**在根目录core.php文件中,有着很多预设好的方法。请您开发更多插件时注意:**

**1.数据库操作直接使用以下方法:**

```php
<?php
       
    namespace Danmaku;
    require_once __DIR__ . "/../autoLoader.php";  
    use Danmaku\Db;

    class demo{
        public static function abc(){
            Db:isTableExist($table);//数据表是否存在,查询时会自带表前缀。存在返回true，反之false。
            Db:createTable($table,$keys);//创建新的数据表,详见core。新建的表会自带前缀。成功或失败会返回true/false
            Db:insertTable($table,$insert);//向表格插入数据
            Db:get($table, $search, $fetch);//查询表格内指定的数据
            Db:set($table, $change, $search);//批量修改或单值修改表格内指定数据
            /*以下是当core中没有预设好的方法时可以调用的函数*/
            Db:query($sql,$fetch);//查询方法。$sql为sql语句; $fetch为是否仅输出第一条内容
            Db:exec($sql);//执行语句方法,返回值为影响了多少行。
        }
    }
```

**2.接收接口参数或者使用curl请用以下方法,而不是$\_POST和$\_GET**


```php
<?php
namespace Danmaku;
require_once __DIR__ . "/../autoLoader.php";  
use Danmaku\Request;

class demo{
    public static function abc(){
      Request::path();//将会返回完整的接口地址。如果您的接口在version段以后还有接口内容的话可以用这个获取
      Request::get($name,$method);//将会获取请求传递过来的参数。$name:参数名,$method:方法。默认为GET方法,如果输入POST会获取post参数
      Request::all($method);  //获取所有请求;默认为自动填补,如果输入GET/POST则只会获取其中一种方法全部请求
      Request::curlGet($url,$zip); //curl获取内容。$url地址,$zip是否开启压缩解码
      Request::validator($param,$rules);//请求参数内容校验。详见core
    }
}


```

##### 3.响应请求请用以下方法

```php
<?php

namespace Danmaku;
require_once __DIR__ . "/../autoLoader.php";  
use Danmaku\Respons;

class demo{
    public static function abc(){
      Respons::json($array,$cache);//$array:要返回的信息,$cache:是否开启缓存(输入true/false)
    }
}
```



### 后续会有更丰富的功能,祝您使用愉快!!
