//浏览器升级提示
if (!!window.ActiveXObject && document.documentMode < 8) {
    location.href = 'ieUpdate.html';
}

// 对Date的扩展，将 Date 转化为指定格式的String
// 月(M)、日(d)、小时(h)、分(m)、秒(s)、季度(q) 可以用 1-2 个占位符，
// 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字)
// 例子：
// (new Date()).format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423
// (new Date()).format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18
Date.prototype.format = function (fmt) { //author: meizz
    fmt = fmt || 'yyyy-MM-dd hh:mm:ss';
    var o = {
        "M+": this.getMonth() + 1, //月份
        "d+": this.getDate(), //日
        "h+": this.getHours(), //小时
        "m+": this.getMinutes(), //分
        "s+": this.getSeconds(), //秒
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度
        "S": this.getMilliseconds() //毫秒
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
};

var _request = {};
_request.errorPlacement = function (error, element) {
    if (element.is('.select')) {
        //下拉框的错误显示位置
        error.insertAfter(element.next());
    } else if (element.is('.image-group')) {
        //图像组控件
        error.insertAfter(element.parent());
    } else if (element.is(':radio') || element.is(':checkbox')) {
        //checkbox 或 radio 错误显示位置
        error.insertAfter(element.parent().parent().parent().children('label:last'));
    } else {
        //普通输入框控件的错误显示位置
        error.insertAfter(element);
    }
}
_request.ajax = function (options) {
    //ajax默认参数
    var options = $.extend({
        url: '',
        data: null,
        async: true,
        dataType: "json",
        timeout: 30000,
        type: "post",
        success: null,
        error: null,
        fail: null,
        debug: false
    }, options);

    $.sui.loading.show('玩命处理中。。。');
    $.ajax({
        url: options.url,
        data: options.data,
        async: options.async,
        dataType: options.dataType,
        type: options.type,
        timeout: options.timeout
    }).done(function (res) {
        if (options.debug) {
            console.log(res);
        }
        $.sui.loading.hide();
        if (res.code == 0) {
            if ($.isFunction(options.success)) {
                options.success(res);
            }
        } else {
            if ($.isFunction(options.error)) {
                options.error(res);
            } else {
                $.sui.error(res.message || "操作失败了");
            }
        }

    }).fail(function (xhr, status, error) {
        if (options.debug) {
            console.log(status);
        }
        if ($.isFunction(options.fail)) {
            options.fail(xhr, status, error);
        } else {
            $.sui.loading.hide();
            console.log("发生异常了！");
            console.log(arguments);
        }
    });
}

_request.succeses = function (callback) {
    $.sui.loading.hide();
    if ($.isFunction(callback)) {
        callback();
    } else {
        //TODO: 默认操作
    }
}

;
(function ($) {

    $.getKeyValueText = function (selector, varName) {
        var list = [];
        $(selector).each(function (index, element) {
            var id = $(element).data('id');
            var value = $(element).val();
            var data = {
                id: id,
                value: value
            }
            list.push(data);
        });
        if (typeof (varName) == 'undefined') {
            return list;
        } else {
            var returnData = {};
            returnData[varName] = list;
            return returnData;
        }
    }


    $.getDomValue = function (selector) {
        var ele = $('input[name="' + selector + '"]:checked');
        if (ele.length > 0) {
            return $('input[name="' + selector + '"]:checked').getValue();
        } else {
            if ($('input[name="' + selector + '"]').length <= 0) return undefined;
            if ($('input[name="' + selector + '"]').attr('type').toLocaleLowerCase() == 'checkbox') {
                return [];
            } else {
                return null;
            }
        }
    }

    $.getCheckboxValue = function (selector) {
        return $.getDomValue(selector);
    }

    $.getRadioValue = function (selector) {
        return $.getDomValue(selector);
    }

    $.checkSelected = function (nodeName) {
        var data = $.getCheckboxValue(nodeName)
        if (data.length <= 0) {
            $.sui.error("请至少选中1条记录后再执行相应的操作。");
            return false;
        }
        return data;
    }

    $.forbidDirectAccess = function () {

        if(window.top==window.self){
            $.sui.error("forbid access", function () {
                top.location.href = '/boss'
            });
        }
    };

    $.fn.getValue = function () {
        if ($(this).attr('type').toLocaleLowerCase() == 'checkbox') {
            var result = [];
            $(this).each(function () {
                result.push($(this).val());
            });
            return result;
        } else {
            return $(this).val();
        }
    }
})(window.jQuery);

// 列表处理
$(function () {
    //移除icheck效果
    if ($('.table-list input[type="checkbox"]').length > 0) {
        $('.table-list input[type="checkbox"]').iCheck('destroy');

        $('#selectAll').on('change', function () {
            var checkboxAll = $('.table-list input[name="ids"]');
            var checked = $(this).is(':checked');
            checkboxAll.prop('checked', checked);
            if (checked) {
                checkboxAll.parents('tr').addClass('selected');
            } else {
                checkboxAll.parents('tr').removeClass('selected');
            }
        });

        $('.table-list input[name="ids"]').on('change', function () {
            if ($(this).is(':checked')) {
                $(this).parents('tr').addClass('selected');
            } else {
                $(this).parents('tr').removeClass('selected');
            }
        });
    }
});

//图片自动加预览效果
$(function() {
    $('.view-photo').each(function(index, element) {
        $(element).on('click', function() {
            
            var url = $(this).data('url');
            var ref = $(this).data('ref');
            
            if(typeof ref != 'undefined') {
                var group = $('.view-photo[data-ref="'+ref+'"]');
                var currentIndex = group.index($(this));
                var imageList = [];
                var imageTemp = [];
                group.each(function(index, ele) {
                    if(index < currentIndex) {
						imageTemp.push($(ele).attr('src'));
					} else {
						imageList.push($(ele).attr('src'));
					}
                });
                url = imageList.concat(imageTemp);
            }
            var opts = {
                padding:10,
                openEffect: 'elastic',
                closeEffect: 'elastic'
            };
            if(window.top != window.self) {
                top.$.fancybox.open(url, opts);
            } else {
                $.fancybox.open(url, opts);
            }
        });
    });
});

// 单个图片
;(function($) {
    $.fn.uploadImageSingle = function(url, length, partName) {
        if(!partName){
           var partName = $(this).attr('id');
        }
        if(!length) var length = 1;
        var options = {
            partName: partName + '_photo',	// 控件名，用于服务器接收数据
            length: length, //数量
            isName: false, 			//是否需要配置名称（默认允许）
            isDesc: false, 			//是否需要配置描述 (默认允许)
            uploadUrl: '/boss/Tool/uploadImage'
        };

        if(typeof url == 'undefined' || url.length <= 0) {
        } else {
            var splits = [';', '#', '|', ','];
            var arrUrl = [];
            for(var i = 0; i < splits.length; ++i) {
                if(url.indexOf(splits[i]) >= 0) {
                    var obj = url.split(splits[i]);
                    for(var y = 0; y < obj.length; ++y) {
                        arrUrl.push({url: obj[y]});
                    }
                }
            }
            if(arrUrl.length <= 0) {
                arrUrl = [{url: url}];
            }
            options.defaults = arrUrl;
        }
        $(this).imageGroup(options);
    }
    $.fn.getUploadImageSingle = function(split) {
        var picUrl = [];
        var imgObject = $(this).imageGroup('data');
        if (imgObject.length > 0) {
            for(var o in imgObject) {
                picUrl.push(imgObject[o].url);
            }
        }
        if(!split) split = ';';
        return picUrl.join(split);
    }


})(jQuery);

