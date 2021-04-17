//侧栏功能变色
function userIsActive(){
	let webUrl = window.location.pathname
	$(".aside-meun .aside-meun-item .children a").each(function(){
		let url = $(this).attr('href')
		if(url == webUrl){
			$(this).addClass("this-user")
		}
	})
}
