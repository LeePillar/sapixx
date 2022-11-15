/*!
 * jQuery cxSelect
 * @name jquery.cxselect.js
 * @version 1.4.2
 * @date 2017-09-26
 * @author ciaoca
 * @email ciaoca@gmail.com
 * @site https://github.com/ciaoca/cxSelect
 * @license Released under the MIT license
 */
(function(factory) {
  if (typeof define === 'function' && define.amd) {
    define(['jquery'], factory);
  } else {
    factory(window.jQuery || window.Zepto || window.$);
  };
}(function($) {
  var linkage = function() {
    var self = this;
    var dom, settings, callback;

    // 分配参数
    for (var i = 0, l = arguments.length; i < l; i++) {
      if (linkage.isJquery(arguments[i]) || linkage.isZepto(arguments[i])) {
        dom = arguments[i];
      } else if (linkage.isElement(arguments[i])) {
        dom = $(arguments[i]);
      } else if (typeof arguments[i] === 'function') {
        callback = arguments[i];
      } else if (typeof arguments[i] === 'object') {
        settings = arguments[i];
      };
    };

    var api = new linkage.init(dom, settings);

    if (typeof callback === 'function') {
      callback(api);
    };

    return api;
  };

  linkage.isElement = function(o){
    if (o && (typeof HTMLElement === 'function' || typeof HTMLElement === 'object') && o instanceof HTMLElement) {
      return true;
    } else {
      return (o && o.nodeType && o.nodeType === 1) ? true : false;
    };
  };

  linkage.isJquery = function(o){
    return (o && o.length && (typeof jQuery === 'function' || typeof jQuery === 'object') && o instanceof jQuery) ? true : false;
  };

  linkage.isZepto = function(o){
    return (o && o.length && (typeof Zepto === 'function' || typeof Zepto === 'object') && Zepto.zepto.isZ(o)) ? true : false;
  };

  linkage.getIndex = function(n, required) {
    return required ? n : n - 1;
  };

  linkage.getData = function(data, space) {
    if (typeof space === 'string' && space.length) {
      space = space.split('.');
      for (var i = 0, l = space.length; i < l; i++) {
        data = data[space[i]];
      };
    };
    return data;
  };

  linkage.init = function(dom, settings) {
    var self = this;

    if (!linkage.isJquery(dom) && !linkage.isZepto(dom)) {return};

    var theSelect = {
      dom: {
        box: dom
      }
    };

    self.attach = linkage.attach.bind(theSelect);
    self.detach = linkage.detach.bind(theSelect);
    self.setOptions = linkage.setOptions.bind(theSelect);
    self.clear = linkage.clear.bind(theSelect);

    theSelect.changeEvent = function() {
      linkage.selectChange.call(theSelect, this.className);
    };

    theSelect.settings = $.extend({}, $.linkage.defaults, settings, {
      url: theSelect.dom.box.data('url'),
      emptyStyle: theSelect.dom.box.data('emptyStyle'),
      required: theSelect.dom.box.data('required'),
      firstTitle: theSelect.dom.box.data('firstTitle'),
      firstValue: theSelect.dom.box.data('firstValue'),
      jsonSpace: theSelect.dom.box.data('jsonSpace'),
      jsonName: theSelect.dom.box.data('jsonName'),
      jsonValue: theSelect.dom.box.data('jsonValue'),
      jsonSub: theSelect.dom.box.data('jsonSub')
    });

    var _dataSelects = theSelect.dom.box.data('selects');

    if (typeof _dataSelects === 'string' && _dataSelects.length) {
      theSelect.settings.selects = _dataSelects.split(',');
    };

    self.setOptions();
    self.attach();

    // 使用独立接口获取数据
    if (!theSelect.settings.url && !theSelect.settings.data) {
      linkage.start.apply(theSelect);

    // 设置自定义数据
    } else if ($.isArray(theSelect.settings.data)) {
      linkage.start.call(theSelect, theSelect.settings.data);

    // 设置 URL，通过 Ajax 获取数据
    } else if (typeof theSelect.settings.url === 'string' && theSelect.settings.url.length) {
      $.getJSON(theSelect.settings.url, function(json) {
        linkage.start.call(theSelect, json);
      });
    };
  };

  // 设置参数
  linkage.setOptions = function(opts) {
    var self = this;

    if (opts) {
      $.extend(self.settings, opts);
    };

    // 初次或重设选择器组
    if (!$.isArray(self.selectArray) || !self.selectArray.length || (opts && opts.selects)) {
      self.selectArray = [];

      if ($.isArray(self.settings.selects) && self.settings.selects.length) {
        var _tempSelect;

        for (var i = 0, l = self.settings.selects.length; i < l; i++) {
          _tempSelect = self.dom.box.find('select.' + self.settings.selects[i]);

          if (!_tempSelect || !_tempSelect.length) {break};

          self.selectArray.push(_tempSelect);
        };
      };
    };

    if (opts) {
      if (!$.isArray(opts.data) && typeof opts.url === 'string' && opts.url.length) {
        $.getJSON(self.settings.url, function(json) {
          linkage.start.call(self, json);
        });

      } else {
        linkage.start.call(self, opts.data);
      };
    };
  };

  // 绑定
  linkage.attach = function() {
    var self = this;

    if (!self.attachStatus) {
      self.dom.box.on('change', 'select', self.changeEvent);
    };

    if (typeof self.attachStatus === 'boolean') {
      linkage.start.call(self);
    };

    self.attachStatus = true;
  };

  // 移除绑定
  linkage.detach = function() {
    var self = this;
    self.dom.box.off('change', 'select', self.changeEvent);
    self.attachStatus = false;
  };

  // 清空选项
  linkage.clear = function(index) {
    var self = this;
    var _style = {
      display: '',
      visibility: ''
    };

    index = isNaN(index) ? 0 : index;

    // 清空后面的 select
    for (var i = index, l = self.selectArray.length; i < l; i++) {
      self.selectArray[i].empty().prop('disabled', true);

      if (self.settings.emptyStyle === 'none') {
        _style.display = 'none';
      } else if (self.settings.emptyStyle === 'hidden') {
        _style.visibility = 'hidden';
      };

      self.selectArray[i].css(_style);
    };
  };

  linkage.start = function(data) {
    var self = this;

    if ($.isArray(data)) {
      self.settings.data = linkage.getData(data, self.settings.jsonSpace);
    };

    if (!self.selectArray.length) {return};

    // 保存默认值
    for (var i = 0, l = self.selectArray.length; i < l; i++) {
      if (typeof self.selectArray[i].attr('data-value') !== 'string' && self.selectArray[i][0].options.length) {
        self.selectArray[i].attr('data-value', self.selectArray[i].val());
      };
    };

    if (self.settings.data || (typeof self.selectArray[0].data('url') === 'string' && self.selectArray[0].data('url').length)) {
      linkage.getOptionData.call(self, 0);
    } else if (self.selectArray[0][0].options.length && typeof self.selectArray[0].attr('data-value') === 'string' && self.selectArray[0].attr('data-value').length) {
      self.selectArray[0].val(self.selectArray[0].attr('data-value'));
      linkage.getOptionData.call(self, 1);
    } else {
      self.selectArray[0].prop('disabled', false).css({
        'display': '',
        'visibility': ''
      });
    };
  };

  // 获取选项数据
  linkage.getOptionData = function(index) {
    var self = this;

    if (typeof index !== 'number' || isNaN(index) || index < 0 || index >= self.selectArray.length) {return};

    var _indexPrev = index - 1;
    var _select = self.selectArray[index];
    var _selectData;
    var _valueIndex;
    var _dataUrl = _select.data('url');
    var _jsonSpace = typeof _select.data('jsonSpace') === 'undefined' ? self.settings.jsonSpace : _select.data('jsonSpace');
    var _query = {};
    var _queryName;
    var _selectName;
    var _selectValue;

    linkage.clear.call(self, index);

    // 使用独立接口
    if (typeof _dataUrl === 'string' && _dataUrl.length) {
      if (index > 0) {
        for (var i = 0, j = 1; i < index; i++, j++) {
          _queryName = self.selectArray[j].data('queryName');
          _selectName = self.selectArray[i].attr('name');
          _selectValue = self.selectArray[i].val();

          if (typeof _queryName === 'string' && _queryName.length) {
            _query[_queryName] = _selectValue;
          } else if (typeof _selectName === 'string' && _selectName.length) {
            _query[_selectName] = _selectValue;
          };
        };
      };

      $.getJSON(_dataUrl, _query, function(json) {
        _selectData = linkage.getData(json, _jsonSpace);

        linkage.buildOption.call(self, index, _selectData);
      });

    // 使用整合数据
    } else if (self.settings.data && typeof self.settings.data === 'object') {
      _selectData = self.settings.data;

      for (var i = 0; i < index; i++) {
        _valueIndex = linkage.getIndex(self.selectArray[i][0].selectedIndex, typeof self.selectArray[i].data('required') === 'boolean' ? self.selectArray[i].data('required') : self.settings.required);

        if (typeof _selectData[_valueIndex] === 'object' && $.isArray(_selectData[_valueIndex][self.settings.jsonSub]) && _selectData[_valueIndex][self.settings.jsonSub].length) {
          _selectData = _selectData[_valueIndex][self.settings.jsonSub];
        } else {
          _selectData = null;
          break;
        };
      };

      linkage.buildOption.call(self, index, _selectData);
    };
  };

  // 构建选项列表
  linkage.buildOption = function(index, data) {
    var self = this;

    var _select = self.selectArray[index];
    var _required = typeof _select.data('required') === 'boolean' ? _select.data('required') : self.settings.required;
    var _firstTitle = typeof _select.data('firstTitle') === 'undefined' ? self.settings.firstTitle : _select.data('firstTitle');
    var _firstValue = typeof _select.data('firstValue') === 'undefined' ? self.settings.firstValue : _select.data('firstValue');
    var _jsonName = typeof _select.data('jsonName') === 'undefined' ? self.settings.jsonName : _select.data('jsonName');
    var _jsonValue = typeof _select.data('jsonValue') === 'undefined' ? self.settings.jsonValue : _select.data('jsonValue');

    if (!$.isArray(data)) {return};

    var _html = !_required ? '<option value="' + String(_firstValue) + '">' + String(_firstTitle) + '</option>' : '';

    // 区分标题、值的数据
    if (typeof _jsonName === 'string' && _jsonName.length) {
      // 无值字段时使用标题作为值
      if (typeof _jsonValue !== 'string' || !_jsonValue.length) {
        _jsonValue = _jsonName;
      };

      for (var i = 0, l = data.length; i < l; i++) {
        _html += '<option value="' + String(data[i][_jsonValue]) + '">' + String(data[i][_jsonName]) + '</option>';
      };

    // 数组即为值的数据
    } else {
      for (var i = 0, l = data.length; i < l; i++) {
        _html += '<option value="' + String(data[i]) + '">' + String(data[i]) + '</option>';
      };
    };

    _select.html(_html).prop('disabled', false).css({
      'display': '',
      'visibility': ''
    });

    // 初次加载设置默认值
    if (typeof _select.attr('data-value') === 'string') {
      _select.val(String(_select.attr('data-value'))).removeAttr('data-value');

      if (_select[0].selectedIndex < 0) {
        _select[0].options[0].selected = true;
      };
    };

    if (_required || _select[0].selectedIndex > 0) {
      _select.trigger('change');
    };

  };

  // 改变选择时的处理
  linkage.selectChange = function(name) {
    var self = this;

    if (typeof name !== 'string' || !name.length) {return};

    var index;

    name = name.replace(/\s+/g, ',');
    name = ',' + name + ',';

    // 获取当前 select 位置
    for (var i = 0, l = self.selectArray.length; i < l; i++) {
      if (name.indexOf(',' + self.settings.selects[i] + ',') > -1) {
        index = i;
        break;
      };
    };

    if (typeof index === 'number' && index > -1) {
      index += 1;
      linkage.getOptionData.call(self, index);
    };
  };

  $.linkage = function() {
    return linkage.apply(this, arguments);
  };

  // 默认值
  $.linkage.defaults = {
    selects: [],            // 下拉选框组
    url: null,              // 列表数据文件路径（URL）或数组数据
    data: null,             // 自定义数据
    emptyStyle: null,       // 无数据状态显示方式
    required: false,        // 是否为必选
    firstTitle: '请选择',    // 第一个选项的标题
    firstValue: '',         // 第一个选项的值
    jsonSpace: '',          // 数据命名空间
    jsonName: 'n',          // 数据标题字段名称
    jsonValue: '',          // 数据值字段名称
    jsonSub: 's'            // 子集数据字段名称
  };

  $.fn.linkage = function(settings, callback) {
    this.each(function(i) {
      $.linkage(this, settings, callback);
    });
    return this;
  };
}));
