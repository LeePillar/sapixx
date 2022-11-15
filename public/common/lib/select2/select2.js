(function ($) {
  $.fn.selectUser = function (options) {
    var options = $.extend({
      uri:location.origin + '/tenant/common/selectUser',
      val:'',
      format: function (data) {
        if (!data.id) return data.text;
        return '<div class="tile tile-centered">'+
            '<div class="tile-icon"><img class="w40 rounded img-responsive" src="'+data.face+'"></div>'+
            '<div class="tile-content">'+
            '<div class="tile-title">' + data.nickname + ' </div>' +
            '<div class="tile-subtitle"><span class="text-tiny"><i class="icon bi-phone"></i> ' + data.as_phone + '</span></div>' +
        '</div>'
      }
    },options);
    return this.each(function () {
      $(this).select2({
          language:'zh-CN',
          templateResult: options.format,
          templateSelection: options.format,
          placeholder: options.val,
          escapeMarkup:(markup) => {return markup;}, 
          ajax: {
            delay: 500,
            url: options.uri,
            cache: true,
            data:(params)=>{ return{keyword:params.term}},
            processResults: (rel) => {return {results: rel.data }},
          }
      })
    })
  };
})(jQuery);