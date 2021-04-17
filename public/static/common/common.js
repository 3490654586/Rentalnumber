document.write("<script src='/static/common/jquery.js'><\/script>"); // 引入Jquery

const url = window.location.protocol+"//"+window.location.host+"/"; // 接口安全域名,全局指定请求
// 全局封装Ajax
function asynchronousAjax(domain, type, data, dataType, async = true) {// 默认为异步

    var options = {
        url: url + domain,
        type: type,
        data: data,
        dataType: dataType,
        async: async
    };

    return $.ajax(options)
        .done(function (data) { // 成功
        })
        .fail(function (err) { // 失败
        })
        .always(function () { // 总是会执行区间
        });

}

// 判断是否登录
function IsLogin() {
    let code;
    var token = window.localStorage.getItem('token')

    asynchronousAjax('index/index/backstageSignin', 'post', { 'token': token }, 'json', false) // 改为同步进行
        .done(function (data) { // 成功
            if (data.code != 0) { // token验证码失败无法进入页面
                code = 1;
            }
        })
    return code;
}


//限制输入框只能输入数字
function inputIntRegEx(obj) {
    obj.value=(obj.value.match(/\d+(\.\d{0,2})?/)||[''])[0] // 正则表达式，只能输入正数，可以有小数点，小数点后面两位。
}

// 数字跑马灯(手写)
function DigitalLantern(obj){
    
    if(!obj){
        var obj = {
            'num' : 0,
            'maxnum' : 100,
            'thatId' : 'all_user'
        };        
    } // 配置对象

    var speed = 0.5 // 跑马速度

    if(obj.maxnum > 10){
        speed = 0
    } // 如果大于10加快最大速度

    let x = 1
    let run = setInterval(()=>{ // 开始跑

        obj.num++
        x++
        if (x == 100){
            obj.num = obj.maxnum
            clearInterval(run)
        }
        if(obj.num >= obj.maxnum){

            if(obj.thatId == 'Percent_user'){ // 如果是百分比加上百分号
                $('#' + obj.thatId).text(obj.maxnum + '%')
            }else{
                $('#' + obj.thatId).text(obj.maxnum)
            }
            clearInterval(run)
        } else {
            if(obj.thatId == 'Percent_user'){ // 如果是百分比加上百分号
                $('#' + obj.thatId).text(obj.num + '%')
            }else{
                $('#' + obj.thatId).text(obj.num)
            }

        }



    },speed)
    // 结束
}