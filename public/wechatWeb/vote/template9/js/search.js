function searchZpPc(item){
	if($('.pcInpArea').css('display')==='block'){
		console.log('搜索...')
		var id = $(item).data('id');
        var gid = $(item).data('gid');
        var token = $(item).data('token');
        var keyword = $.trim($('#pcSearch').val());

        if (keyword==''){
    	    layer.open({
                content: '请输入选手姓名或编号'
                ,skin: 'msg'
                ,time: 2 
            })
    	    return false
    	}

    	var loading=layer.open({
                type: 2
                ,shadeClose: false
                ,content: '拼命搜索中'
            })

    	$.ajax({
                type: "post",
                url: searchUrl,
                data: {"id": id,"token":token,"keyword":keyword,"gid":gid},
                dataType: "json",
                success: function (data, textStatus, jqXHR) {
                	$('#pcSearch').val('')
                    setTimeout(function () {
                        layer.close(loading);
                        if (true == data.status) {
                            window.location.href=data.url;
                        } else {
                            layer.open({
                                skin: 'msg'
                                ,time: 2 //2秒后自动关闭
                                ,content: data.message
                            });
                            return false
                        }
                    },1200);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    setTimeout(function(){
                        layer.close(loading);
                        layer.open({
                            skin: 'msg'
                            ,time: 2 //2秒后自动关闭
                            ,content: '请求出错,请联系工作人员'
                        });
                    },1200)
                }
        })
	}
}

function searchZp(item){
	if($('.mbSearchInpArea').css('display')==='block'){
		console.log('搜索...')
		var id = $(item).data('id');
        var gid = $(item).data('gid');
        var token = $(item).data('token');
        var keyword = $.trim($('#mobileSearch').val());

        if (keyword==''){
    	    layer.open({
                content: '请输入选手姓名或编号'
                ,skin: 'msg'
                ,time: 2 
            })
    	    return false
    	}

    	var loading=layer.open({
                type: 2
                ,shadeClose: false
                ,content: '拼命搜索中'
            })

    	$.ajax({
                type: "post",
                url: searchUrl,
                data: {"id": id,"token":token,"keyword":keyword,"gid":gid},
                dataType: "json",
                success: function (data, textStatus, jqXHR) {
                	$('#mobileSearch').val('')
                    setTimeout(function () {
                        layer.close(loading);
                        if (true == data.status) {
                            window.location.href=data.url;
                        } else {
                            layer.open({
                                skin: 'msg'
                                ,time: 2 //2秒后自动关闭
                                ,content: data.message
                            });
                            return false
                        }
                    },1200);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    setTimeout(function(){
                        layer.close(loading);
                        layer.open({
                            skin: 'msg'
                            ,time: 2 //2秒后自动关闭
                            ,content: '请求出错,请联系工作人员'
                        });
                    },1200)
                }
        })
	}
}