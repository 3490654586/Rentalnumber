var httpStr = window.location.protocol+"//"+window.location.host+"/";
var presonData = {};
var logoUrl; //logo


function fetchGet(options) {
	return new Promise((resolve, reject) => {
		$.get(
			httpStr + options.url,
			options.data || {},
			function(res) {
				resolve(res)
			}
		)
	})
}

function fetchPost(options) {
	return new Promise((resolve, reject) => {
		$.post(
			httpStr + options.url,
			options.data || {},
			function(res) {
				resolve(res)
			}
		)
	})
}

// //全局配置
fetchPost({
	url: 'index/home/WebsiteSettingsInformation'
}).then(res => {
	let config = res.data
	// console.log(res)
	//配置主题
	var root = document.querySelector(":root")
	root.setAttribute('style', '--text-color:' + config.theme_font_color + ';--back-color:' + config.theme_bkg_color)
	//配置logo
	logoUrl = res.data.logo
	$(".footer-logo img").attr('src',logoUrl)
	$(".logo").attr('src', logoUrl)
	//配置标题
	document.title = config.home_title
	$("head").append('<link rel="icon" href=' + config.icon + '>')
	// 配置meta
	$("head").append('<meta  name="keywords" content=' + config.home_keyword + '/>')
	$("head").append('<meta  name="description" content=' + config.home_describe + '/>')
	$(".keep-on-record").text(config.keep_on_record)
	$("#telephone p").text(config.telephone)

})


//返回顶部
function backTop(){
	$("html,body").stop().animate({scrollTop:0});
}
//判断是否显示返回顶部
window.onscroll = function(){
	let top = $(document).scrollTop()
	if(top == 0){
		$("#kefu-con").children('div:last-child').stop().slideUp('slow')
	}
	else{
		$("#kefu-con").children('div:last-child').stop().slideDown('slow')
	}
}