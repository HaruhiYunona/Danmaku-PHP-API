//网页布局设置,这个和演示关系不大
const layout = () => {
    let width = document.body.clientWidth;
    let ratio = document.body.clientWidth / document.body.clientHeight;
    let levelSize = null;
    document.getElementsByTagName('body')[0].style.height = width / 855 * 1200;
    if (ratio >= 1) {
        levelSize = '0.8px';
        document.getElementsByClassName('card')[0].style.width = width * 0.6;
        document.getElementsByClassName('card')[1].style.width = width * 0.6;
        descHeight = 0;
        document.getElementsByTagName('h2')[0].setAttribute('style', '');
        document.getElementById('uploader').setAttribute('style', '');
        document.getElementsByClassName('face')[0].setAttribute('style', '');
        document.getElementById('up').setAttribute('style', 'margin-left: 10px;');
        document.getElementsByClassName('desc')[0].setAttribute('style', '');
        document.getElementsByClassName('desc-text')[0].setAttribute('style', '');
        document.getElementById('reply').setAttribute('style', '');
        setStyle(document.getElementsByClassName('tag-text'), '');
        setStyle(document.getElementsByClassName('reply-head'), '')
        setStyle(document.getElementsByClassName('reply-user-name'), '');
        setStyle(document.getElementsByClassName('reply-content'), '');
        setStyle(document.getElementsByClassName('reply-comment-box'), '');
        document.getElementsByTagName('ul')[0].style.height = document.getElementsByTagName('body')[0].offsetHeight - document.getElementsByClassName('card')[0].clientHeight - 240;
    } else {
        levelSize = '0.2vw';
        document.getElementsByClassName('card')[0].style.width = width * 0.84;
        document.getElementsByClassName('card')[1].style.width = width * 0.84;
        document.getElementsByTagName('h2')[0].setAttribute('style', 'font-size:5vw;float:none;');
        document.getElementById('uploader').setAttribute('style', 'float:none;width:100%;display: inline;justify - content: none;padding-left:10%;font-size:1.5rem;line-height:6vw;');
        document.getElementsByClassName('face')[0].setAttribute('style', 'float:left;width:6vw;height:6vw;border-radius:6vw,margin-left:3vw');
        document.getElementById('up').setAttribute('style', 'float:left;margin-top:0;margin-left:10px;font-size:4vw;line-height:6vw');
        document.getElementsByClassName('desc')[0].setAttribute('style', 'font-size:4vw');
        document.getElementsByClassName('desc-text')[0].setAttribute('style', 'font-size:3vw');
        document.getElementById('reply').setAttribute('style', 'font-size:4vw;');
        setStyle(document.getElementsByClassName('tag-text'), 'font-size:3vw;');
        setStyle(document.getElementsByClassName('reply-head'), 'width:10vw;height:10vw;border-radius:10vw;')
        setStyle(document.getElementsByClassName('reply-user-name'), 'font-size:3vw;');
        setStyle(document.getElementsByClassName('reply-content'), 'font-size:2.7vw;');
        setStyle(document.getElementsByClassName('reply-comment-box'), 'font-size:2.4vw;');
        document.getElementsByTagName('ul')[0].style.height = document.getElementsByTagName('body')[0].offsetHeight - document.getElementsByClassName('card')[0].clientHeight + 300;
    }
    document.getElementById('flex-box').style.height = (document.getElementById('flex-box').clientWidth * 9 / 16);
    document.getElementsByClassName('card')[0].style.height = document.getElementById('flex-box').clientHeight + document.getElementsByClassName('desc')[0].clientHeight + document.getElementsByClassName('video-top')[0].clientHeight + 50;
    let level = document.getElementsByClassName('reply-user-level');
    let levelLength = level.length;
    let color = { 0: '#3CB371', 1: '#00CED1', 2: '#4682B4', 3: '#DAA520', 4: '#9932CC', 5: '#DC143C' };
    for (let i = 0; i < levelLength; i++) {
        let lv = level[i].innerHTML.replace(/LV/g, '');
        let lvColor = color[lv * 1 - 1];
        level[i].setAttribute('style', 'margin-left:10px;-webkit-text-stroke: ' + levelSize + ' ' + lvColor + ';');
    }
}




//滚动到底自动加载列表方法
const scrollList = () => {
    if (document.getElementsByTagName('ul')[0].scrollTop + document.getElementsByTagName('ul')[0].clientHeight + 10 >= document.getElementsByTagName('ul')[0].scrollHeight) {
        if (scrollFlag == 0) {
            let aid = document.getElementById('aid').value;
            let page = document.getElementById('page').value * 1 + 1;
            document.getElementById('page').setAttribute('value', page);
            scrollFlag = 1;
            flashList(aid, page);
        }
    }
}


//自行定义的检查方法
const check = (data) => {
    let msg = null;
    if (data.code !== 0) {
        try {
            msg = data.msg;
        } catch (e) {
            msg = null;
        }
        if (msg != null) {
            msg = data.msg;
        } else {
            msg = data.message;
        }
        console.log("%c Danmaku %c " + msg, "color: #fff; margin: 1e m 0; padding: 2px 0; background: #F08080;", "margin: 1e m 0; padding: 5 px 0; background_round: #EFEFEF;");
        return true;
    } else {
        return false;
    }
}

//自行定义的随机数生成,用来随机生成用户id。真实应用场景可以将你网站的用户id填在Dplayer配置上,这样就可以识别是哪个用户发的弹幕了
const getUser = () => {
    return md5(Math.random()).slice(0, 6);
}

//自行定义的批量设置样式的方法
const setStyle = (doc, style) => {
    let docLength = doc.length;
    for (let i = 0; i < docLength; i++) {
        doc[i].setAttribute('style', style);
    }
}

//监听窗大小改变自适应布局
const getWindowInfo = () => {
    layout();
}
const debounce = (fn, delay) => {
    let timer;
    return function () {
        if (timer) {
            clearTimeout(timer);
        }
        timer = setTimeout(() => {
            fn();
        }, delay);
    }
};
const cancalDebounce = debounce(getWindowInfo, 500);

window.addEventListener('resize', cancalDebounce);


