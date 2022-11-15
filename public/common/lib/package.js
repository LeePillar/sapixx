//定义包路径
var js = document.scripts;
var package = js[js.length-1].src.substring(0,js[js.length-1].src.lastIndexOf("/")+1);
//定义加载核心库
Do.setConfig('coreLib', [package+'jquery-2.2.4.min.js']);
//进度条
Do.add('nprogress_css',{path :package + 'nprogress/nprogress.css',type : 'css'});
Do.add('nprogress', { path: package + 'nprogress/nprogress.js', type: 'js',requires:['nprogress_css']});
//弹出层
Do.add('layer_lib', { path: package+'layer/layer.js', type: 'js'});
Do.add('layer', { path: package+'layer/layer.extend.js', type: 'js',requires:['layer_lib','nprogress']});
//HTTP
Do.add('template', {path : package+'art-template/template-web.js',type:'js'});
Do.add('request', {path : package+'http/http.min.js',type:'js',});
Do.add('http', {path : package+'http/request.js',type:'js',requires:['request','template','layer']});
//请求
Do.add('base',{path : package+'common.js',type:'js',requires: ['http']});
//表单
Do.add('validator',{path : package + 'form/validator.js?local=zh-CN',type:'js'});
Do.add('jquery.form', {path: package + 'form/jquery.form.js',type: 'js'});
Do.add('form',{path : package + 'form/form.js',type:'js',requires:['layer','jquery.form','validator']});
//菜单
Do.add('menucss',{path : package + 'select-menu/selectmenu.css',type : 'css'});
Do.add('menu', { path: package + 'select-menu/selectmenu.min.js', type: 'js', requires: ['menucss'] });
//selec2
Do.add('selectcss', { path: package + 'select2/select2.css', type: 'css' });
Do.add('select2', { path: package + 'select2/select2.min.js', type: 'js' });
Do.add('select-zh-CN', { path: package + 'select2/select2.js', type: 'js'});
Do.add('select', { path: package + 'select2/i18n/zh-CN.js', type: 'js', requires: ['selectcss', 'select2', 'select-zh-CN'] });
//上传
Do.add('upload_css',{path : package + 'upload/webuploader.css',type:'css'});
Do.add('upload_core', { path: package + 'upload/webuploader.min.js',type:'js'});
Do.add('upload', { path: package + 'upload/upload.js', requires: ['upload_css','upload_core'] });
//ztree
Do.add('ztree_css',{path : package + 'ztree/zTreeStyle.css',type:'css'});
Do.add('ztree',{path : package + 'ztree/jquery.ztree.min.js',requires:['ztree_css']});
//二维码
Do.add('qrcode', { path: package + 'qrcode/qrcode.min.js', type: 'js' });
//DatePicker
Do.add('date', { path: package + 'date/laydate.js' });
//多级联动
Do.add('linkage', { path: package + 'linkage/jquery.select.js' });
//编辑器
Do.add('editor', { path: package + 'editor/kindeditor.js' });
// Do.add('editorcss',{path : package + 'editor/style.css',type : 'css'});
// Do.add('editor', { path: package + 'editor/wangeditor.js', type: 'js',requires: ['editorcss']});