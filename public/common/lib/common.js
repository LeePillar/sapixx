(function ($) {
    //列表全选
    $.fn.checkAll = function (options) {
        var options = $.extend({checkboxAll:'thead input[type="checkbox"]',checkbox: 'tbody input[type="checkbox"]'},options);
        $(this).click(function () {
            var that = this;
            $(options.checkbox).each(function () {
                if ($(that).prop("checked")) {
                    $(this).prop("checked",true)
                } else {
                    $(this).prop("checked",false)
                }
            });
        })
    };
    //批量操作选择的表单
    $.fn.multiAction = function (options,callback) {
        var options = $.extend({post:true},options);
        $(this).click(function () {
            var ids = "";
            $("input:checkbox[name='ids[]']:checked").each(function() {
               ids += $(this).val() + ",";
            });
            options.param = {ids:ids.slice(0,-1)}
            options.follow = this;
            $(this).requestUrl(options, callback);
            delete options.param
            return false;
        })
    };
    //随机字符串
    $.fn.randomWord = function (options) {
        var options = $.extend({len:43,dom:this},options);
        $(this).click(function () {
            var str = "", range = options.min;
            var arr = ['0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
            if(options.len){
                range = Math.round(Math.random() * (options.len-options.len)) + options.len;
            }  
            for(var i=0; i < range; i++){
                pos = Math.round(Math.random() * (arr.length-1));
                str += arr[pos];
            }
            $(options.dom).val(str)
        }) 
    };
    //表单改变值请求
    $.fn.changeUrl = function (options,callback) {
        var options = $.extend({post:true,confirm:false},options);
        $(this).change(function () {
            if (!options.hasOwnProperty('param')) {
                options.param = {id:!!$(this).attr('data-id')?$(this).attr('data-id'):$(this).attr('id'),sort:$(this).val()}
            }
            options.follow = this;
            $(this).requestUrl(options, callback);
            delete options.param
            return false;
        })
    };
    //点击URL请求
    $.fn.actUrl = function (options,callback) {
        var options = $.extend({confirm:true},options);
        $(this).click(function () {
            options.follow = this;
            $(this).requestUrl(options,callback);
            return false;
        })
    };
    $.fn.requestUrl = function (options,callback) {
        var options = $.extend({ confirm: true, post: false, param: {}, content: '确认操作', text: '此修改将不可逆' },options);
        var uri = options.hasOwnProperty('uri')?options.uri:(!!$(this).attr("url")?$(this).attr("url"):$(this).attr("href"))
        if (uri == 'undefined'){
            parent.layer.notice('找不到请求的url', { skin: 'error' })
            return false;
        }
        //返回结果处理
        var returnfun = (rel) => {
            if (options.is_delete && rel.code == 204) {
                $(this).parents("tr").remove();
                $(this).parent().remove();
            }
            "function" == typeof callback && callback(rel);
        }
        //确认还是直接执行
        if (options.confirm) {
            options.yes = function(index) {
                layer.close(index);
                if (options.post) {
                    http.post(uri,options.param,returnfun)
                } else {
                    http.get(uri,options.param,returnfun)
                }
            }
            layer.popconfirm(options.content,options.follow,options)
        } else {
            if (options.post) {
                http.post(uri,options.param,returnfun)
            } else {
                http.get(uri,options.param,returnfun)
            }
        }
        return false;
    };
    //弹出窗口
    $.fn.win = function (options) {
        var options = $.extend({area:'680px',title: '快捷窗口', reload: false,maxmin:false}, options);
        $(this).click(function () {
            var uri = options.hasOwnProperty('uri') ? options.uri : (!!$(this).attr("url") ? $(this).attr("url") : $(this).attr("href"))
            if (uri == 'undefined'){
                parent.layer.notice('找不到请求的url', { skin: 'error' })
                return false;
            }
            parent.layer.open({
                type: 2,maxHeight:'60%',offset: '5em',title: options.title, area: options.area,maxmin:options.maxmin,content:uri,
                success:function (layero,index) {
                    parent.layer.iframeAuto(index)
                    parent.layer.getChildFrame('body',index).addClass(window.name);
                    $('body', document).on('keyup', function (e) {
                        if (e.which === 27) {
                            parent.layer.close(index);
                        }
                    })
                }, end: function () {
                    true == options.reload && parent.$("#" + window.name)[0].contentWindow.location.reload();
                }
            });
            return false;
        })
    };
})(jQuery);