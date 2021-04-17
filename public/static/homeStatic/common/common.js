// 登录
function login()
{
	layer.open({
		type: 2,
		content: loginUrl,
		title: [' ', 'background-color:#fff;border-bottom:none'], // 不需要title,给他留空
		area: ['400px', '410px'],
		resize: false,
		anim: 1	
	});
}

// 注册
function registered()
{
	layer.open({
		type: 2,
		content: regUrl,
		title: [' ', 'background-color:#fff;border-bottom:none'], // 不需要title,给他留空
		area: ['400px', '410px'],
		resize: false,
		anim: 1	,
		success:function(layero,index){
			layero.find('iframe').contents().find('#login').removeClass('layui-this')
			layero.find('iframe').contents().find('#reg').addClass('layui-this')
			layero.find('iframe').contents().find('#login_content').removeClass('layui-show')
			layero.find('iframe').contents().find('#reg_content').addClass('layui-show')
		}
	});
}

// post跳转
function StandardPost(url,args) 
{
    var form = $("<form method='post'></form>");
    form.attr({"action":url});
    for (arg in args)
    {
        var input = $("<input type='hidden'>");
       

        input.attr({"name":arg});
        input.val(args[arg]);
        form.append(input);
    }

    $("body").append(form);
    form.submit();
}

window.inputIntRegEx = function(obj){
	obj.value=(obj.value.match(/\d+(\.\d{0,2})?/)||[''])[0] // 正
}