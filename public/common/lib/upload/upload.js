(function ($) {
    $.fn.multUpload = function (options,callback) {
        var that  = this,param = {multiple: true,local:false,defaultUi:true};
        "function"!=typeof options&&(param=$.extend(param,options));
        if (param.defaultUi) {
            var input_name = $(that).attr('data-name');
            $('.'+input_name).bindEvent();
            $('.'+ input_name).delImg()
            $('.'+input_name).activityImg(input_name)
        }
        $(that).upload(param, (rel) => {
            "function" == typeof options ? options(rel) : "function" == typeof callback && callback(rel);
            200==rel.code&&param.defaultUi&&$("."+input_name).appendImg(rel.url);
        });
    }
    $.fn.upload = function (options, callback) {
        var param = {
            server: location.origin +'/tenant/upload',
            multiple: false,
            duplicate: true,
            defaultUi: false,
            local:false,
            resize: true,
            auto: true,
            fileNumLimit: 10,
            chunked: true,
            private: false,
            accept: {
                title:'application',
                extensions: 'gif,jpg,jpeg,jpeg,bmp,png,mp3,pem,doc,docx,xls,xlsx,xlsx,pdf',
                mimeTypes:'image/jpg,image/jpeg,image/png,image/gif,audio/mpeg,image/webp,text/plain,application/pdf,application/zip,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation'
            }
        };
        "function" != typeof options && (param = $.extend(param,options));
        return this.each(function () {
            var that = this;
            param.pick     = {'id':this,multiple:param.multiple};
            param.server   = param.hasOwnProperty('uri')?param.uri:param.server;
            param.formData = {private:param.private?1:0,local:param.local?1:0};
            var uploader = new WebUploader.Uploader(param);
            uploader.on('beforeFileQueued', function (file) {
                if (param.defaultUi) {
                    var input_name = $(that).attr('data-name');
                    var upload_num = $('.'+input_name).find('.ui-img-num').val();
                    var imgs_num = $('.' + input_name).find('.ui-img-input').length;
                    if (imgs_num >= upload_num) {
                        parent.layer.toast("已上传" + imgs_num + "个,最多上传" + upload_num + "个",{skin:'error'});
                        return false;
                    }
                }  
            });
            uploader.on('fileQueued', function (file) {
                $(that).addClass('loading');
            });
            uploader.on('uploadSuccess',function (file, data) {
                parent.layer.toast(data.message, { 'skin': 'success' });
                if (param.defaultUi) {
                    var input_name = $(that).attr('data-name');
                    var upload_num = $('.'+input_name).find('.ui-img-num').val();
                    var imgs_num   = $('.' + input_name).find('.ui-img-input').length;
                    if (imgs_num+1 >= upload_num) {
                        $('.'+input_name).find('.ui-img-botton').hide();
                    }
                } 
                "function" == typeof options ? options(data) : "function" == typeof callback && callback(data);
            });
            uploader.on('uploadComplete', function (file) {
                $(that).removeClass('loading');
                uploader.refresh();
            });
            uploader.on('uploadError',function (file) {
                console.log(file);
            });
        });
    };
    $.fn.appendImg = function (src) {
        var that = this;
        var input_name = $(this).attr('data-name');
        var $file = '<div class="col col-auto ui-img mb-4 mt-4"><div class="card card-hover card-sm w120">'
        +'<div class="card-image space space-align-center h80">'
        +'  <input class="ui-img-input" type="hidden" name="'+input_name+'[]" value="' + src + '" />'
        +'  <img class="ui-img-set img-responsive img-width h80 img-fit-cover" src="' + src + '"/>'
        +'</div>'
        +'<div class="card-footer btn-group btn-group-block">'
        +'  <button outline gray icon type="button" class="btn btn-sm ui-img-left"><i class="icon bi-arrow-left"></i></button>'
        +'  <button outline gray icon type="button" class="btn btn-sm ui-img-right"><i class="icon bi-arrow-right"></i></button>'
        +'  <button outline gray icon type="button" class="btn btn-sm ui-img-del"><i class="icon bi-x-lg"></i></button>'
        +'</div>'
        +'</div></div>';
        $(that).prepend($file);
        if ($(that).find('.ui-img-index').val() == "") {
            $(that).find('.ui-img-index').val(src);
        }
        $(that).bindEvent()
        $(that).activityImg(input_name)
        $(that).delImg()
    }
    //左右移动事件
    $.fn.bindEvent = function () {
        var that = this;
        $(that).find(".ui-img-right").on("click",function () {
            var current_tr = $(this).closest('.ui-img');
                current_tr.insertAfter(current_tr.next());
        });
        $(that).find(".ui-img-left").on("click",function () {
            var current_tr = $(this).closest('.ui-img');
            if (current_tr.prev().html() != null) { 
                current_tr.insertBefore(current_tr.prev());
            } 
        });
    }
    $.fn.activityImg = function (input_name) {
        var that = this;
        $(that).find('.ui-img-set').click(function () {
            $('.'+input_name).find('.card').removeClass("card-activate shadow");
            $(this).closest('.card').addClass("card-activate shadow");
            $(that).find('.ui-img-index').val($(this).attr('src'));
        })
    }
    $.fn.delImg = function () {
        var that = this;
        $(that).find('.ui-img-del').click(function () {
            $(this).closest('.ui-img').remove();
            var upload_num = $(that).find('.ui-img-num').val();
            var imgs_num = $(that).find('.ui-img-input').length;
            if (upload_num-imgs_num == 1) {
                $(that).find('.ui-img-botton').show();
            }
        })
    }
})(jQuery);