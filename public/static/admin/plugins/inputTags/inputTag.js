/*
 * Name: inputTag
 * Author: cshaptx4869
 * Project: https://github.com/cshaptx4869/inputTag
 */
(function (define) {
  define(['jquery'], function ($) {
    "use strict";

    class InputTag {

      options = {
        elem: '.fairy-tag-input',
        theme: [ 'fairy-bg-orange'],
        data: [],
        maxCount: 5,
        removeKeyNum: 8,
        createKeyNum: 13,
        permanentData: [],
      };

      get elem() {
        return $(this.options.elem);
      }

      get copyData() {
        return [...this.options.data];
      }

      constructor(options) {
        this.render(options);
      }

      render(options) {
        this.init(options);
        this.listen();
      }

      init(options) {
        var spans = '', that = this;
        this.options = $.extend(this.options, options);
        !this.elem.attr('placeholder') && this.elem.attr('placeholder', '回车添加标签');
        $.each(this.options.data, function (index, item) {
          spans += that.spanHtml(item);
        });
        this.elem.before(spans);
      }

      listen() {
        var that = this;

        this.elem.parent().on('click', 'a', function () {
          that.removeItem($(this).parent('span'));
        });

        this.elem.parent().on('click', function () {
          that.elem.focus();
        });

        this.elem.keydown(function (event) {
          var keyNum = (event.keyCode ? event.keyCode : event.which);
          if (keyNum === that.options.removeKeyNum) {
            if (!that.elem.val().trim()) {
              var closeItems = that.elem.parent().find('a');
              if (closeItems.length) {
                that.removeItem($(closeItems[closeItems.length - 1]).parent('span'));
                event.preventDefault();
              }
            }
          } else if (keyNum === that.options.createKeyNum) {
            that.createItem();
            event.preventDefault();
          }
        });
      }

      createItem() {
        var value = this.elem.val().trim();


        if (this.options.beforeCreate && typeof this.options.beforeCreate === 'function') {
          var modifiedValue = this.options.beforeCreate(this.copyData, value);
          if (typeof modifiedValue == 'string' && modifiedValue) {
            value = modifiedValue;
          } else {
            value = '';
          }
        }
        if (this.options.data.length>=this.options.maxCount){
          this.elem.val('');
          layer.msg(`最多设置${this.options.maxCount}个标签`);
          return
        }
        if (value) {
          if (!this.options.data.includes(value)) {
            this.options.data.push(value);
            this.elem.before(this.spanHtml(value));
            this.onChange(value, 'create');
          }
        }

        this.elem.val('');
      }

      removeItem(target) {
        var that = this;
        var closeSpan = target.remove(),
            closeSpanText = $(closeSpan).children('span').text();
        var value = that.options.data.splice($.inArray(closeSpanText, that.options.data), 1);
        value.length === 1 && that.onChange(value[0], 'remove');
      }

      randomColor() {
        return this.options.theme[Math.floor(Math.random() * this.options.theme.length)];
      }

      spanHtml(value) {
        return '<span class="fairy-tag fairy-anim-fadein ' + this.randomColor() + '">' +
            '<span>' + value + '</span>' +
            (this.options.permanentData.includes(value) ? '' : '<a href="#" title="删除标签">&times;</a>') +
            '</span>';
      }

      onChange(value, type) {
        this.options.onChange && typeof this.options.onChange === 'function' && this.options.onChange(this.copyData, value, type);
      }

      getData() {
        return this.copyData;
      }

      clearData() {
        this.options.data = [];
        this.elem.prevAll('span.fairy-tag').remove();
      }
    }

    return {
      render(options) {
        return new InputTag(options);
      }
    }
  });
}(typeof define === 'function' && define.amd ? define : function (deps, factory) {
  var MOD_NAME = 'inputTag';
  if (typeof module !== 'undefined' && module.exports) { //Node
    module.exports = factory(require('jquery'));
  } else if (window.layui && layui.define) {
    layui.define('jquery', function (exports) { //layui加载
      exports(MOD_NAME, factory(layui.jquery));
    });
  } else {
    window[MOD_NAME] = factory(window.jQuery);
  }
}));
