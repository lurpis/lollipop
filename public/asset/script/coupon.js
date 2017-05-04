/*
* @Author: hangbale
* @Date:   2016-10-23 13:03:40
* @Last Modified by:   hangbale
* @Last Modified time: 2016-10-23 15:38:47
*/

window.app.module['/coupon'] = function () {
    if(window.pingxx.browserType !== 'weixin'){
        alert('请在微信中打开');
        return;
    }else{
        $('#channel-common').hide();
        if(!getCookie('open_id')){
            if(!window.pingxx.getToken('open_id')){
                 window.pingxx.getLogin('http://pop.gmcloud.io/coupon');
            }else{
                setCookie('open_id', window.pingxx.getToken('open_id'), 7);
                window.pingxx.openId = window.pingxx.getToken('open_id');
            }
        }else{
            window.pingxx.openId = getCookie('open_id');
        }
    }
    request();
    function request() {
        $.ajax({
            url: 'api/reward',
            type: 'GET',
            data: {open_id: window.pingxx.openId}
        })
        .done(function(res) {
            if(res.state === 'success'){
                setView(res.data);
            }
        })
        .fail(function() {
            ui.modal('查询失败,请重试');
        })  
    }
    function parseKeyTime(time) {
        var times = [
            {
                param : 1,
                desc : '天'
            },
            {
                param : 7,
                desc : '周'
            },
            {
                param : 31,
                desc : '月'
            },
            {
                param : 365,
                desc : '年'
            }
        ];
        for (var i = times.length - 1; i >= 0; i--) {
            if(time % times[i].param === 0){
                return +time / times[i].param + times[i].desc;
            }
        }
    }
    function setView(data) {
        var tpl = '';
        if(data.length < 1){
            tpl = '<li class="list-container">暂无代点卡</li>';
        }else{
            for (var i = 0, l = data.length; i < l; i++) {
                tpl += '<li class="list-container">'+ (i+1) + '&emsp;' +
                        data[i].key +
                        '(' + parseKeyTime(data[i].day) + ')' +
                        '<div class="list-action">';
                if(data[i].is_used){
                    tpl += '<a href="javascript:;">已用</a>';
                }else{
                    tpl += '<a href="javascript:;">未用</a>';
                }
                tpl += '</div></li>';
            }
        }
        $('#coupon-list').append(tpl);
    }
}