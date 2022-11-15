(function(factory) {
    typeof module === "object" && module.exports ? module.exports = factory( require( "jquery" ) ) :
    typeof define === 'function' && define.amd ? define(['jquery'], factory) :
    factory(jQuery);
}(function($) {
    $.validator.config({
        rules: {
            digits: [/^\d+$/, "请填写数字"],
            npot: [/^(\d+\.\d{1,2}|\d+)$/, "小数点后最多2位的数字"],
            letters: [/^[A-Za-z]+$/i, "请填写字母"],
            letters_num: [/^[A-Za-z0-9]+$/i, "请填写字母或数字"],
            date: [/^\d{4}-\d{2}-\d{2}$/, "请填写有效日期,格式:yyyy-mm-dd"],
            time: [/^([01]\d|2[0-3])(:[0-5]\d){1,2}$/, "请填写有效时间,00:00到23:59之间"],
            email: [/^[\w\+\-]+(\.[\w\+\-]+)*@[a-z\d\-]+(\.[a-z\d\-]+)*\.([a-z]{2,4})$/i, "请填写有效邮箱"],
            http: [/^(http|https):\/\/[\w\-_]+(\.[\w\-_]+)+\.(com|edu|net|org|biz|info|cn|com.cn|me)*$/i, "请填写有效网址"],
            url: [/^(http|https):\/\/[\w\-_]+(\.[\w\-_]+)+\.(com|edu|net|org|biz|info|cn|com.cn|me)+.*$/i, "请填写有效网址"],
            qq: [/^[1-9]\d{4,}$/, "请填写有效QQ号"],
            tel: [/^(?:(?:0\d{2,3}[\-]?[1-9]\d{6,7})|(?:[48]00[\-]?[1-9]\d{6}))$/, "请填写有效电话号"],
            mobile: [/^1[3-9]\d{9}$/,"请填写有效手机号"],
            zipcode: [/^\d{6}$/, "请检查邮编格式"],
            chinese: [/^[\u0391-\uFFE5]+$/, "请填写中文"],
            username: [/^\w{3,12}$/, "请填写3-12位数字、字母、下划线"],
            password: [/^[\S]{6,16}$/, "请填写6-16位字符，不能包含空格"],
            card: [/^\d{6}(19|2\d)?\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)?$/, "请填写正确身份证"],
            idCard: function (element) { 
                var value = $(element).val();
				var wi = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1 ];// 加权因子;
				var ValideCode = [ 1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2 ];// 身份证验证位值，10代表X;
				if (value.length == 15) {
					return isValidityBrithBy15IdCard(value) ? true:'请填写正确身份证';
				}else if (value.length == 18){
					var a_idCard = value.split("");// 得到身份证数组   
					if (isValidityBrithBy18IdCard(value)&&isTrueValidateCodeBy18IdCard(a_idCard)) {   
						return true;
					}   
				}
				return '请填写正确身份证';
				function isTrueValidateCodeBy18IdCard(a_idCard) {   
					var sum = 0; // 声明加权求和变量   
					if (a_idCard[17].toLowerCase() == 'x') {   
						a_idCard[17] = 10;// 将最后位为x的验证码替换为10方便后续操作   
					}   
					for ( var i = 0; i < 17; i++) {   
						sum += wi[i] * a_idCard[i];// 加权求和   
					}   
					valCodePosition = sum % 11;// 得到验证码所位置   
					if (a_idCard[17] == ValideCode[valCodePosition]) {   
						return true;   
					}
					return false;   
				}
				function isValidityBrithBy18IdCard(idCard18){   
					var year = idCard18.substring(6,10);   
					var month = idCard18.substring(10,12);   
					var day = idCard18.substring(12,14);   
					var temp_date = new Date(year,parseFloat(month)-1,parseFloat(day));   
					// 这里用getFullYear()获取年份，避免千年虫问题   
					if(temp_date.getFullYear()!=parseFloat(year) || temp_date.getMonth()!=parseFloat(month)-1 || temp_date.getDate()!=parseFloat(day)){   
						return false;
					}
					return true;
				}
				function isValidityBrithBy15IdCard(idCard15){   
					var year =  idCard15.substring(6,8);   
					var month = idCard15.substring(8,10);   
					var day = idCard15.substring(10,12);
					var temp_date = new Date(year,parseFloat(month)-1,parseFloat(day));   
					// 对于老身份证中的你年龄则不需考虑千年虫问题而使用getYear()方法   
					if(temp_date.getYear()!=parseFloat(year) || temp_date.getMonth()!=parseFloat(month)-1 || temp_date.getDate()!=parseFloat(day)){   
						return false;
					}
					return true;
				}
            },
            accept: function (element, params) {
                if (!params) return true;
                var ext = params[0],
                    value = $(element).val();
                return (ext === '*') ||
                       (new RegExp(".(?:" + ext + ")$", "i")).test(value) ||
                       this.renderMsg("只接受{1}后缀的文件", ext.replace(/\|/g,','));
            }
        },
        // Default error messages
        messages: {
            0: "此处",
            fallback: "{0}格式错误",
            loading: "正在验证...",
            error: "网络异常",
            timeout: "请求超时",
            required: "{0}必填",
            remote: "{0}已被使用",
            integer: {
                '*': "请填写整数",
                '+': "请填写正整数",
                '+0': "请填写正整数或0",
                '-': "请填写负整数",
                '-0': "请填写负整数或0"
            },
            match: {
                eq: "{0}与{1}不一致",
                neq: "{0}与{1}不能相同",
                lt: "{0}必须小于{1}",
                gt: "{0}必须大于{1}",
                lte: "{0}不能大于{1}",
                gte: "{0}不能小于{1}"
            },
            range: {
                rg: "请填写{1}到{2}的数",
                gte: "请填写不小于{1}的数",
                lte: "请填写最大{1}的数",
                gtlt: "请填写{1}到{2}之间的数",
                gt: "请填写大于{1}的数",
                lt: "请填写小于{1}的数"
            },
            checked: {
                eq: "请选择{1}项",
                rg: "请选择{1}到{2}项",
                gte: "请至少选择{1}项",
                lte: "请最多选择{1}项"
            },
            length: {
                eq: "请填写{1}个字符",
                rg: "请填写{1}到{2}个字符",
                gte: "请至少填写{1}个字符",
                lte: "请最多填写{1}个字符",
                eq_2: "",
                rg_2: "",
                gte_2: "",
                lte_2: ""
            }
        }
    });
    /* Themes
     */
    var TPL_ARROW = '<span class="n-arrow"></span>';
    $.validator.setTheme({
        'simple_right': {
            formClass: 'n-simple',
            msgClass: 'n-right'
        },
        'simple_bottom': {
            formClass: 'n-simple',
            msgClass: 'n-bottom'
        },
        'yellow_top': {
            formClass: 'n-yellow',
            msgClass: 'n-top',
            msgArrow: TPL_ARROW
        },
        'yellow_right': {
            formClass: 'n-yellow',
            msgClass: 'n-right',
            msgArrow: TPL_ARROW
        },
        'yellow_right_effect': {
            formClass: 'n-yellow',
            msgClass: 'n-right',
            msgArrow: TPL_ARROW,
            msgShow: function($msgbox, type){
                var $el = $msgbox.children();
                if ($el.is(':animated')) return;
                if (type === 'error') {
                    $el.css({left: '20px', opacity: 0}).delay(100).show().stop().animate({left: '-4px', opacity: 1}, 150).animate({left: '3px'}, 80).animate({left: 0}, 80);
                } else {
                    $el.css({left: 0, opacity: 1}).fadeIn(200);
                }
            },
            msgHide: function($msgbox, type){
                var $el = $msgbox.children();
                $el.stop().delay(100).show().animate({ left: '20px', opacity: 0 }, 300, function () {
                    $msgbox.hide();
                });
            }
        }
    });
}));
