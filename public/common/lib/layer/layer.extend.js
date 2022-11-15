//气泡确认框
layer.popconfirm = function(content, follow, options){ 
    var icon = '<svg width="24" height="24" viewBox="0 0 48 48" fill="none"><path d="M42 24c0 9.941-8.059 18-18 18S6 33.941 6 24 14.059 6 24 6s18 8.059 18 18zM24 20v14M24 18v-4" stroke="#3491FA" stroke-width="4"/></svg>'
    var options = $.extend({type: 4,tips:3,icon:icon,shade:false,resize: false,fixed: false,text:'此修改将不可逆',skin:'info',btn: ['确认','取消']},options);
    switch (options.skin) {
        case 'warning':
            options.skin = 'layer-popconfirm layer-popconfirm-warning'
            options.icon = '<svg width="24" height="24" viewBox="0 0 48 48" fill="none"><path d="M42 24c0 9.941-8.059 18-18 18S6 33.941 6 24 14.059 6 24 6s18 8.059 18 18zM24 20v14M24 18v-4" stroke="#FC7C00" stroke-width="4"/></svg>'
            break;
        case 'danger':
            options.skin = 'layer-popconfirm layer-popconfirm-danger'
            options.icon = '<svg width="24" height="24" viewBox="0 0 48 48" fill="none"><path d="M42 24c0 9.941-8.059 18-18 18S6 33.941 6 24 14.059 6 24 6s18 8.059 18 18zM24 20v14M24 18v-4" stroke="#F53F3F" stroke-width="4"/></svg>'
            break;
        default:
            options.skin = 'layer-popconfirm'
            break;
    }
    options.content = ['<i class="layer-popconfirm-icon">'+options.icon+'</i>'+'<div class="layer-popconfirm-body"><div class="layui-popconfirm-title">'+content+'</div>'+'<div class="layui-popconfirm-disc">'+options.text+'</div></div>',follow];
    return layer.open(options);
}
// 提示
layer.toast =  function(content,options){ 
    var icon = '<svg width="24" height="24" viewBox="0 0 48 48" fill="none"><path d="M42 24c0 9.941-8.059 18-18 18S6 33.941 6 24 14.059 6 24 6s18 8.059 18 18zM24 20v14M24 18v-4" stroke="#3491FA" stroke-width="4"/></svg>'
    var options = $.extend({type:0,title:false,btn:0,shade:false,resize:false,time:3000,offset: '20px',text:'此修改将不可逆',skin:'default'},options);
    switch (options.skin) {
        case 'warning':
            icon = '<svg width="24" height="24" viewBox="0 0 48 48" fill="none"><path d="M42 24c0 9.941-8.059 18-18 18S6 33.941 6 24 14.059 6 24 6s18 8.059 18 18zM24 20v14M24 18v-4" stroke="#FC7C00" stroke-width="4"/></svg>'
            options.skin = 'layer-toast  layer-toast-warning'
            break;
        case 'error':
            icon = '<svg width="24" height="24" viewBox="0 0 48 48" fill="none"><path d="M42 24c0 9.941-8.059 18-18 18S6 33.941 6 24 14.059 6 24 6s18 8.059 18 18zM24 20v14M24 18v-4" stroke="#F53F3F" stroke-width="4"/></svg>'
            options.skin = 'layer-toast layer-toast-error'
            break;
        case 'success':
            icon = '<svg width="24" height="24" viewBox="0 0 48 48" fill="none"><path d="M42 24c0 9.941-8.059 18-18 18S6 33.941 6 24 14.059 6 24 6s18 8.059 18 18z" stroke="#00B42A" stroke-width="4"/><path d="M15 22l7 7 11.5-11.5" stroke="#00B42A" stroke-width="4"/></svg>'
            options.skin = 'layer-toast layer-toast-success'
            break;
        case 'info':
            icon = '<svg width="24" height="24" viewBox="0 0 48 48" fill="none"><path d="M42 24c0 9.941-8.059 18-18 18S6 33.941 6 24 14.059 6 24 6s18 8.059 18 18zM24 20v14M24 18v-4" stroke="#4E5969" stroke-width="4"/></svg>'
            options.skin = 'layer-toast layer-toast-info'
            break;
        default:
            options.skin = 'layer-toast'
            break; 
    }
    options.content = '<div class="nprogress"></div><i class="layer-toast-icon">'+icon+'</i>'+'<div class="layer-toast-body">'+content+'</div>';
    return layer.open(options);
}
// 通知
layer.notice = function(content,options){ 
    var icon = '<svg width="24" height="24" viewBox="0 0 48 48" fill="none"><path d="M42 24c0 9.941-8.059 18-18 18S6 33.941 6 24 14.059 6 24 6s18 8.059 18 18zM24 20v14M24 18v-4" stroke="#3491FA" stroke-width="4"/></svg>'
    var options = $.extend({type:1,title:'通知',shade:false,resize: false,fixed: false,time:3000,offset:'rt',skin:'info'},options);
    switch (options.skin) {
        case 'warning':
            options.skin = 'layer-notice layer-notice-warning'
            icon = '<svg width="24" height="24" viewBox="0 0 48 48" fill="none"><path d="M42 24c0 9.941-8.059 18-18 18S6 33.941 6 24 14.059 6 24 6s18 8.059 18 18zM24 20v14M24 18v-4" stroke="#FC7C00" stroke-width="4"/></svg>'
            break;
        case 'error':
            options.skin = 'layer-notice layer-notice-error'
            icon = '<svg width="24" height="24" viewBox="0 0 48 48" fill="none"><path d="M42 24c0 9.941-8.059 18-18 18S6 33.941 6 24 14.059 6 24 6s18 8.059 18 18zM24 20v14M24 18v-4" stroke="#F53F3F" stroke-width="4"/></svg>'
            break;
        case 'success':
            options.skin = 'layer-notice layer-notice-success'
            icon = '<svg width="24" height="24" viewBox="0 0 48 48" fill="none"><path d="M42 24c0 9.941-8.059 18-18 18S6 33.941 6 24 14.059 6 24 6s18 8.059 18 18z" stroke="#00B42A" stroke-width="4"/><path d="M15 22l7 7 11.5-11.5" stroke="#00B42A" stroke-width="4"/></svg>'
            break;
        default:
            options.skin = 'layer-notice'
            break;
    }
    options.content = '<div class="nprogress"></div><i class="layer-notice-icon">'+icon+'</i>'+'<div class="layer-notice-body"><div class="layui-notice-title">'+options.title+'</div>'+'<div class="layui-notice-disc">'+content+'</div></div>';
    options.title   = false
    return layer.open(options);
}