(function ($) {
    $.fn.isForm = function (options, callback) {
        var param = {type:'POST',datatype:"json",headers: {"X-SOFT-NAME": "SAPI++ SaaS Framework", 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}};
        if (typeof options !== 'function') {
            param = $.extend(param,options); 
        }
        return this.each(function () {
            $(".ui-date").length > 0 && $(this).find('.ui-date').inputDate()   //时间选择器
            $(".ui-datetime").length > 0 && $(this).find('.ui-datetime').inputDateTime()  //时间选择器
            $(".ui-editor").length > 0 && $(this).find('.ui-editor').editor()  //kindeditor编辑器
            $(this).validator({focusCleanup:true,valid:function (form) {
                var that = this, index = window.hasOwnProperty('name') ? parent.layer.getFrameIndex(window.name) : 0;
                that.holdSubmit();
                if (param.type.toUpperCase() == 'POST') {
                    $(form).ajaxSubmit({type:param.type,dataType:param.datatype,headers:param.headers,timeout:3000,async:true,
                        success: (rel) => {
                            switch (rel.code) {
                                case 200://提示并跳转或静默
                                    parent.layer.close(index);
                                    parent.layer.toast(rel.message, {'skin': 'success'});
                                    if (rel.hasOwnProperty('is_parent') && rel.is_parent == true) {
                                        true == rel.hasOwnProperty('url') && parent.window.location.replace(rel.url);
                                    } else {
                                        true == rel.hasOwnProperty('url') && window.location.replace(rel.url);
                                    }
                                    break;
                                case 204://静默待回调处理
                                    parent.layer.close(index);
                                    break;
                                case 302://跳转
                                    parent.layer.close(index);
                                    if (rel.hasOwnProperty('is_parent') && rel.is_parent == true) {
                                        rel.hasOwnProperty('url') ? parent.window.location.replace(rel.url):parent.window.location.reload();
                                    } else {
                                        rel.hasOwnProperty('url') ? window.location.replace(rel.url):window.location.reload();
                                    }
                                    break;
                                default://失败
                                    parent.layer.toast(rel.message,{skin:'warning'});
                                    break;
                            }
                            "function" == typeof options ? options(rel) : "function" == typeof callback && callback(rel);
                            that.holdSubmit(false);
                            return false;
                        }, error: function (rel) {
                            that.holdSubmit(false);
                        }
                    })
                } else {
                    form.submit();
                    that.holdSubmit(false);
                }
            }}); 
        });
    };
    $.fn.setSmsBtn = function (callback){
        return this.each(function () {
            var time = 60;
            var times = setInterval(() => {
                time--;
                this.setAttribute("disabled",true);
                $(this).html("( " + time + " ) 重发");
                if (time == 0) {
                    clearInterval(times)
                    this.removeAttribute("disabled");
                    $(this).html("获取验证码");
                }
            }, 1000);
            "function" == typeof callback && callback();
        });
    };
    //时间 2002-12-22
    $.fn.inputDate = function () {
        return this.each(function () {
            laydate.render({ elem: this });
        });
    };
    //时间 2002-12-22 00:00:00
    $.fn.inputDateTime = function () {
        return this.each(function () {
            laydate.render({elem:this,'type':'datetime'});
        });
    };
    //编辑器
    $.fn.editor = function (uploads){
        return this.each(function () {
            var uploadJson = !!$(this).attr("url") ? $(this).attr("url") : location.origin + '/tenant/upload'
            var editorConfig = { langType:'zh_CN',minWidth:750,minHeight:350,uploadJson: uploadJson,urlType: "domain",width:'100%',height: '250px',themeType: 'simple',filePostName:'file',
                items:['formatblock', 'fontsize','|','forecolor','hilitecolor','bold','italic','underline','|','justifyleft','justifycenter','justifyright','insertorderedlist',
                'insertunorderedlist', '|','link','insertimages','|','removeformat','clearhtml','source','|','fullscreen'],
                afterBlur: function () { this.sync() }
            };
            KindEditor.create(this, editorConfig);
        });
    };
})(jQuery);