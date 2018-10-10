/**
 * 基于JQuery UI 弹出框组件
 * date: 2018-05-28
 * author: summer <nommo@jiushikeji.cn>
 * version: 2.0.0
 */
;(function($) {

    //全局Key
	var _handle = '@.SUI.DIALOG.HANDLE',
    _data = '@.SUI.DIALOG.DATA',
    _opener = '@.SUI.DIALOG.OPENER';
    _dialog_callback = '@.SUI.DIALOG.CALLBACK';

    //生成唯一ID
	var createId = function(name) {
		var d = new Date();
		return name + '_' + (Math.floor(Math.random() * 999) + 100) + d.getDate() + d.getHours() + d.getMinutes() + d.getSeconds();
    }
    
    //Toast
	var toastTimer;
	var toastBridge = function(dtype, message, callback, msec) {
		
		if(typeof(message) == 'object') {
			callback = message.callback;
			msec = message.msec;
		}
		
		clearTimeout(toastTimer);
		$('#dialogToast').remove();
		
		var isAuto = typeof(msec) == 'undefined' ? true : false;
		var isLoading = dtype == 'loading' ? true : false;
		
		if(isLoading && typeof(message) == 'undefined') message = '数据加载中...';
		
		$('body').append('<div id="dialogToast" class="dialog-toast dialog-toast-' + dtype + '">' + message + '</div>');
		$('#dialogToast').css({marginLeft: -($('#dialogToast').outerWidth() / 2) + 'px', 'top': '0px'});

		if((isLoading && !isAuto) || !isLoading) {
			if(isAuto) msec = 3;
			toastTimer = setTimeout(function() {
				$('#dialogToast').fadeOut('fast', function() {
					$(this).remove();
				});
			}, msec * 1000);
		}
		
    }
    
    //创建提示框
	var dialogBridge = function(dtype, message, callback, options) {
		
		if(typeof(options) == 'undefined') options = {};

		var config = $.extend({
			message: message,
			ok: callback,
			title: '温馨提示',
			cancel: null,
			okVal: '确定',
			cancelVal: '取消'
		}, options);
		
		
		if(typeof(message) == 'object') {
			config = $.extend(config, {
				message: '',
				ok: null
			}, message);
		}
		
		var id = createId(dtype);
		
		$('body').append('<div id="' + id + '" style="display:none"><div class="dialog-ui-icon dialog-ui-' + dtype + '">' + config.message + '</div></div>');
		var dialogConfig = {
			
			modal: true,
			resizable: false,
			dialogClass: 'jquery-ui-dialog',
			width: 360,
			minHeight:150,
			closeText: '',
			title: config.title,
			focus: function(event, ui) {
				$(this).parent().find('button').blur();
			},
			close: function() {$(this).remove()}
		};
		
		//显示按钮
		var buttons = [{
			text: config.okVal,
			click: function() {
				$(this).dialog('close').remove();
				if($.isFunction(config.ok)) {
					config.ok();
				}
			}
		}];
		
		if(dtype == 'confirm') {
			buttons.push({
				text: config.cancelVal,
				click: function() {
					$(this).dialog('close').remove();
					if($.isFunction(config.cancel)) {
						config.cancel();
					}
				}
			});
		}		
		
		dialogConfig.buttons = buttons;
		$('#' + id).dialog(dialogConfig);
    }
    
    //对外公开接口
    if(typeof($.sui) == 'undefined') $.sui = {};
    var dialogList = {};

    dialogList.dialog = {
        open: function(url, data, callback, options, onClick) {
            if(window.top != window.self) {
				return top.$.sui.dialog.open(url, data, callback, options);
            }

            if(typeof(url) == 'object') {
				options = url;
				url = options.url;
				data = options.data;
                callback = options.callback;
                onClick = options.onClick;
			}
			
			var options = $.extend({
                scrollX: false,
                scrollY: true,
				scroll: 'auto'
            }, options);
                
            var id = createId('dialog_iframe');
            var cover = $('<div id="' + id + '_cover" class="ui-dialog-iframe-cover" style="display:none"></div>');
            var dialog = $('<div id="' + id + '" class="ui-dialog-iframe" style="visibility:hidden">' +
                '<div class="ui-dialog-header">' +
                    '<h3></h3>' +
                    '<div class="close"></div>' +
                '</div>' +
                '<div class="ui-dialog-contents">'+
                    '<div class="cover" style="display:none"></div>' +
                    '<iframe id="' + id + '_detector" style="display:none" scrolling="' + options.scroll + '" frameborder="0"></iframe>' +
                '</div>' +
            '</div>');

            dialog.appendTo('body');
            cover.appendTo('body');

            // 关闭
            dialog.find('.ui-dialog-header .close').bind('click', function() {
                if($.isFunction(onClick)) {
                    onClick();
                }
                $('#' + id).fadeOut('fast', function() {
                    $(this).remove();
                });
				$('#' + id + '_cover').fadeOut('fast', function() {
                    $(this).remove();
                });
            });

            // 移动
            dialog.find('.ui-dialog-header h3').mousedown(function(e) {
                var offset = dialog.offset();
                    absX = e.pageX - offset.left,
                    absY = e.pageY - offset.top;
                dialog.find('.cover').show();
                $(this).bind("selectstart", function(){return false});
                $(document).mousemove(function(e) {
                    dialog.stop();
                    var left = e.pageX - absX,
                        top = e.pageY - absY;
                    
                    if(left <= 0) {
                        left = 0;
                    } else if(left + dialog.width() > $(document).width()) {
                        left = $(document).width() - dialog.width();
                    }
                    if(top <= 0) {
                        top = 0;
                    } else if(top + dialog.height() > $(document).height()) {
                        top = $(document).height() - dialog.height();
                    }

                    dialog.css({
                        left: left + "px",
                        top: top + "px"
                    });

                }).mouseup(function() {
                    $(this).unbind("selectstart");
                    $(this).unbind("mousemove");
                    dialog.find('.cover').hide();
                });
            });

            $.sui.loading.show('页面加载中');
            $('#' + id + '_detector').load(function() {
                $.sui.loading.hide();
                cover.show();
                $(this).show();

                var isAutoSize = /^(http:|https:|ftp:)\/\/.*/.test(url);
				if(isAutoSize) {
					var title = '\u6d88\u606f\u7a97\u53e3',
						width = 800,
						height = 400;
				} else {
					var title = $(this).contents().find('title').html(),
						width = $(this).contents().find('body').outerWidth(true),
                        height = $(this).contents().find('body').outerHeight(true);
                        if(!options.scrollX) {
                            $(this).contents().find('body').css('overflow-x', 'hidden');
                        }
                        if(!options.scrollY) {
                            $(this).contents().find('body').css('overflow-y', 'hidden');
                        }
                }

                // 宽高大于可视化范围，取屏幕尺寸
                if(width + 10 > $(window).width()) width = $(window).width() - 10;
                if(height + 70 > $(window).height()) height = $(window).height() - 60;
                var top = $(window).scrollTop() + parseInt(($(window).height() - height - 50) / 2),
                    left = $(window).scrollLeft() + parseInt(($(window).width() - width) / 2);
                
                title = typeof(options.title) != 'undefined' ? options.title : title;
                var index = $('.ui-dialog-iframe').length + 9;

                cover.css('z-index', index);
                dialog.css({
                    'left': left,
                    'top': top,
                    'width': width,
                    'z-index': index + 1,
                    'visibility': 'visible'
                });
                dialog.find('.ui-dialog-header h3').text(title);
                $(this).css({
                    'width': width,
                    'height': height
                });

                // 注入全局变量
                $(this)[0].contentWindow[_handle] = id;
                if(data) {
                    $(this)[0].contentWindow[_opener] = data;
                }
                if($.isFunction(callback)) {
                    $(this)[0].contentWindow[_dialog_callback] = callback;
                }
                if( $.isFunction($(this)[0].contentWindow.onloadSuccess) ) {
                    $(this)[0].contentWindow.onloadSuccess();
                }

            }).attr('src', url);
			return id;
        },
        close: function(handle, callback, data) {
            var isSelfWindow = typeof(self.window[_handle]) != 'undefined' && typeof(handle) == 'undefined';
			if(isSelfWindow) {
				top.$.sui.dialog.close(self.window[_handle], self.window[_dialog_callback], self.window[_data]);
				return;
			}
			if(window.top != window.self) {
				top.$.sui.dialog.close(handle);
				return;
			}
			if(!isSelfWindow) {
                if($.isFunction(callback)) {
                    callback(data);
                }
				$('#' + handle).fadeOut('fast', function() {
                    $(this).remove();
                });
				$('#' + handle + '_cover').fadeOut('fast', function() {
                    $(this).remove();
                });
			}
        },
        data: function(dataValue) {
            if(typeof(dataValue) == 'undefined') {
				return self.window[_opener] ? self.window[_opener] : '';
			} else {
				if(window.top != window.self && typeof(self.window[_handle]) != 'undefined') {
					self.window[_data] = dataValue;
				}
			}
        },
        updateSize: function(handle, width, height) {
            var isSelfWindow = typeof(self.window[_handle]) != 'undefined' && typeof(handle) == 'undefined';
			if(isSelfWindow) {
				window.top.$.sui.dialog.updateSize(self.window[_handle], $('body').width(), $('body').height());
				return;
            }
            var dialog = $('#' + handle);
            var iframe = dialog.find('iframe');

            if(width + 10 > $(window).width()) width = $(window).width() - 10;
            if(height + 70 > $(window).height()) height = $(window).height() - 60;
            var top = $(window).scrollTop() + parseInt(($(window).height() - height - 50) / 2),
                left = $(window).scrollLeft() + parseInt(($(window).width() - width) / 2);

            dialog.animate({
                'left': left,
                'top': top,
                'width': width
            }, 'fast');

            iframe.animate({
                'width': width,
                'height': height
            }, 'fast');
        }
    }

    //站内消息
	dialogList.message = function(strHtml, title, msec) {
		if(window.top != window.self) {
			top.$.sui.message(strHtml, title, msec);
			return;
		}
		$('#dialog_message').remove();
		$('body').append('<div id="dialog_message" style="display:none">' + strHtml + '</div>');
		$('#dialog_message').dialog({
			title:title,
			modal: false,
			resizable: false,
			dialogClass: 'jquery-ui-dialog-message',
			width: 270,
			minHeight: 180,
			position:{at: "right bottom", of: window},
			closeText:'',
			close: function() {$(this).remove()}
		});
		
		if(typeof(msec) == 'undefined') return;
		
		setTimeout(function() {
			$('#dialog_message').dialog('close').remove();
		}, msec * 1000);
	}

    // 提示框
    $.each(['alert', 'error', 'success', 'confirm'], function(key, value) {
		dialogList[value] = function(message, callback, options) {
			if(window.top != window.self) {
				top.$.sui[value](message, callback, options);
			} else {
				dialogBridge(value, message, callback, options);
			}
		}
	});
    
    //toast
	dialogList.toast = {};
	$.each(['warning', 'error', 'success'], function(key, value) {
		dialogList.toast[value] = function(message, callback, msec) {
			if(window.top != window.self) {
				top.$.sui.toast[value](message, callback, msec);
			} else {
				toastBridge(value, message, callback, msec);
			}
		}
    });

    //toast loading
	dialogList.toast.loading = {
		show: function(message, callback, msec) {
			if(window.top != window.self) {
				top.$.sui.toast.loading.show(message, callback, msec);
			} else {
				toastBridge('loading', message, callback, msec);
			}
		},
		hide: function()　{
			if(window.top != window.self) {
				top.$.sui.toast.loading.hide();
			} else {
				$('.dialog-toast-loading').fadeOut('fast', function() {
					$(this).remove();
				});
			}
		}
	}

    //loading
	dialogList.loading = {
		show: function(message, msec) {
			if(window.top != window.self) {
				top.$.sui.loading.show(message, msec);
				return;
			}
			if(typeof(message) == 'undefined') message = '正在提交数据...';
			var strHtml = '<div class="jquery-dialog-loading"><div class="loading-icon"></div><p class="loading_content">' + message + '</p></div><div class="loading-overlay"></div>';
			
			$('.jquery-dialog-loading').remove();
			$('.loading-overlay').remove();
			$('body').append(strHtml);

			if(typeof(msec) != 'undefined') {
				setTimeout(function() {
					$('.jquery-dialog-loading').fadeOut('fast', function() {$(this).remove()});
					$('.loading-overlay').fadeOut('fast', function() {$(this).remove()});
				}, msec * 1000);
			}
		},
		hide: function() {
			if(window.top != window.self) {
				top.$.sui.loading.hide();
				return;
			}
			$('.jquery-dialog-loading').fadeOut('fast', function() {$(this).remove()});
			$('.loading-overlay').fadeOut('fast', function() {$(this).remove()});
		}
	};
    
    $.sui = $.extend($.sui, dialogList);
})(window.jQuery);