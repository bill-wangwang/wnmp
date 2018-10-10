/**
 * jQuery 附件上传插件
 * date: 2018-03-22
 * author: nommo <nommo@jiushikeji.com>
 * version: 1.0.0
 * 
 * 插件依赖 jquery.ajaxupload.min.js
 */
;(function($) {
    "use strict";
    
    var getTipString = function(accept) {
        var tmpAccept = [];
        for(var i = 0; i < accept.length; ++i) {
            tmpAccept.push('*.' + accept[i]); 
        }
        return '只支持上传&nbsp;&nbsp;'+tmpAccept.join('&nbsp;&nbsp;&nbsp;')+'&nbsp;&nbsp;格式的文件。';
    }
    
    /**
     * 通过字符串拆分文件信息，返回文件名和后缀
     */
    var getFileInfo = function(file) {
        var data = {
            ext: '',
            fileName: file
        }
        if(file.indexOf('.') > 0) {
            var arrExt = file.split('.');
            data.ext = arrExt.pop();
            data.fileName = arrExt.join('.');
        }
        return data;
    }
    
    /**
     * 获取文件大小（显示）
     * @param int size 传入参数为字节数
     */
    var getFileSize = function(size) {
        if(size >= 1073741824) {
            size = (Math.round(size / 1073741824 * 100) / 100) + 'GB';
        } else if(size >= 1048576) {
            size = (Math.round(size / 1048576 * 100) / 100) + 'MB';
        } else if(size >= 1024) {
            size = (Math.round(size / 1024 * 100) / 100) + 'KB';
        } else {
            size = size + 'B';
        }
        return size;
    }

    /**
     * 获取文件类型的CSS样式名字
     */
    var getFileIcon = function(ext) {
        var commonType = ['apk', 'chm', 'js', 'mdb', 'pdf', 'php', 'psd', 'txt', 'exe', 'xml', 'fla', 'swf', 'flv', 'png', 'bmp', 'ai'];
        var fileType = {
            'doc': ['doc', 'docx', 'dot', 'dotx', 'docm', '.dotm'],
            'obj': ['obj', 'lib'],
            'audio': ['mp3', 'cd', 'ogg', 'wma', 'wav', 'midi', 'vqf'],
            'dll': ['dll', 'ocx', 'so'],
            'html': ['html', 'htm', 'shtm', 'link'],
            'image': ['jpg', 'jpeg', 'gif'],
            'ppt': ['ppt', 'pptx', 'pps', 'pptm', 'ppsx', 'ppam', 'potm'],
            'css': ['css', 'ini', 'inf'],
            'video': ['mp4','rm', 'rmvb', 'ram', 'mov', 'avi', 'mkv', 'wmv', 'mpeg', 'mpg', 'aiff', 'qt', 'asf', 'vob', 'vod', '3gp'],
            'xls': ['xls', 'xlsx', 'xlsm', 'xltx', 'xltm', 'xlsb', 'xlam'],
            'zip': ['rar', 'zip', 'cab', 'arj', 'lzh', 'ace', 'tar', 'gz', 'iso', '7z', 'bz', 'gz', 'uha', 'uda', 'zpaq']
        }
        
        var fileIcon = 'icon-file-';
        var commFlag = false;
        for(var i = 0; i < commonType.length; ++i) {
            if(commonType[i] == ext) {
                fileIcon += commonType[i];
                commFlag = true;
                break;
            }
        }
        
        if(!commFlag) {
            var flag = false;
            for(var y in fileType) {
                for(var x = 0; x < fileType[y].length; ++x) {
                    if(fileType[y][x] == ext) {
                        flag = true;
                        fileIcon += y;
                        break;
                    }
                }
                if(flag) break;
            }
            if(!flag) fileIcon += 'unknow';
        }
        return fileIcon;
    }
    
    
    /**
     * 输出数据
     */
    var outputFileList = function(element, data, file, params) {
        
        var currElement = element.find('.icon-file-loading').parent();   
        
        var fileInfo = getFileInfo(file);
        
        var strHtml = '<i class="icon-file '+getFileIcon(fileInfo.ext)+'"></i>'+
            '<a href="'+data.url+'" title="'+file+'" target="_blank" class="file">'+file+'</a>';
        
        if(params.isShowSize) {
            strHtml += '<span class="size-tip">('+getFileSize(data.size)+')</span>';
        }
        
        if(params.isEdit) {
            strHtml += '<a href="javascript:;" class="iconfont icon-edit" title="修改文件名"></a>';
        }
        
        strHtml +='<a href="javascript:;" class="iconfont icon-delete" title="移除"></a>' +
        '<input type="hidden" name="'+params.controlName+'[\'url\'][]" class="url" value="'+data.url+'" />' +
        '<input type="hidden" name="'+params.controlName+'[\'fullName\'][]" class="full-name" value="'+file+'" />' +
        '<input type="hidden" name="'+params.controlName+'[\'fileName\'][]" class="file-name" value="'+fileInfo.fileName+'" />' +
        '<input type="hidden" name="'+params.controlName+'[\'ext\'][]" class="ext" value="'+fileInfo.ext+'" />' +
        '<input type="hidden" name="'+params.controlName+'[\'size\'][]" class="size" value="'+data.size+'" />';

        currElement.html(strHtml);

        // 删除
        element.find('.icon-delete').off('click').on('click', function() {
            var _self = $(this).parent();
            var fileName = _self.find('.full-name').val();
            $.sui.confirm('是否确定移除文件 ' + fileName + ' 吗？', function() {
                _self.remove();
                element.find('.uploadFile-btn-bar button').prop('disabled', false);
            });
        });

        // 修改文件名
        element.find('.icon-edit').off('click').on('click', function() {
            var _self = $(this).parent();
            var fileName = _self.find('.file-name').val();
            var ext = _self.find('.ext').val();

            $('body').append('<div id="uploadFileDialog" style="display:none">'+
                '<table>'+
                    '<tr>'+
                        '<th>文件名</th>'+
                        '<td><input type="text" value="' + fileName + '" id="inputFileName" /> &nbsp;.'+ext+'</td>'+
                    '</tr>'+
                '</table>' +
            '</div>');
            $('#inputFileName').select();

            $('#uploadFileDialog').dialog({
                modal: true,
                resizable: false,
                dialogClass: 'jquery-ui-dialog-iframe jquery-dialog-form',
                closeText: '',
                title: '修改文件名',
                width: 400,
                close: function() {$(this).remove()},
                buttons:[{
                    text: '确定',
                    click: function() {
                        var inputFileName = $.trim($('#inputFileName').val());

                        if(inputFileName.length <= 0) {
                            $.sui.alert('文件名不能为空。', function() {
                                $('#inputFileName').focus();
                            });
                            return false;
                        }

                        var ext = _self.find('.ext').val();

                        _self.find('.full-name').val(inputFileName + '.' + ext);
                        _self.find('.file').text(inputFileName + '.' + ext);
                        _self.find('.file-name').val(inputFileName);

                        $(this).dialog('close').remove();
                    }
                }, {
                    text: '取消',
                    click: function() {
                        $(this).dialog('close').remove();
                    }
                }]
            });


        });
    }
    
    /**
     * 初始化上传控件
     */
    var initUploadFile = function(element, params) {
        
        var tips = params.tips;
        if(!tips) {
            if(params.accept.length <= 0) {
                var tips = '请选择要上传的文件，支持上传任意格式。';
            } else {
                var tmpAccept = [];
                for(var i = 0; i < params.accept.length; ++i) {
                    tmpAccept.push('*.' + params.accept[i]); 
                }
                var tips = getTipString(params.accept);
            }
        }
        
        var btnIdString = element.attr('id') + '_uploadButton';
        
        var strHtml = '<div class="uploadFile-btn-bar">'+
            '<button class="btn btn-info" id="'+btnIdString+'" type="button"><i class="iconfont icon-upload"></i> ' + params.buttonText + '</button>'+
        '</div>'+
        '<div class="uploadFile-tips">' + tips + '</div>'+
        '<div class="uploadFile-list"><ul></ul></div>';
        element.html(strHtml);
        
        if(params.isSort) {
            element.find('ul').sortable();
        }
        
        // 回显数据
        if(params.data) {
            if(params.length > 0 && params.data.length > params.length) {
                $.sui.error('uploadFile插件：初始化数据溢出！');
                $('#' + btnIdString).prop('disabled', true);
                return false;
            }
            for(var i = 0; i < params.data.length; ++i) {
                var file = params.data[i].fullName;
                element.find('ul').append('<li><div class="icon-file-loading"></div></li>');
                outputFileList(element, params.data[i], file, params);
            }
        }
        
        // 上传控件
        new AjaxUpload(btnIdString, {
            action: params.url,
            name: params.fileControlName,
            onSubmit: function(file, suffix) {
                
                suffix = suffix.toLowerCase();
                var flag = true;
                if(params.accept.length > 0) {
                    flag = false;
                    for(var i = 0; i < params.accept.length; ++i) {
                        if(params.accept[i] == suffix) {
                            flag = true;
                            break;
                        }
                    }
                    if(!flag) {
                        if(!$.isFunction(params.onSubmit)) {
                            $.sui.alert(getTipString(params.accept));
                            return false;
                        }
                    }
                }
                
                // 自定义验证提示
                if($.isFunction(params.onSubmit) && params.onSubmit(file, suffix, flag) === false) {
                    return false;
                }
                
                // 限制数量
                if(params.length > 0 && element.find('.uploadFile-list li').length >= params.length) {
                    $.sui.alert('最多只支持上传' + params.length + '个文件。');
                    return false;
                }
                
                // 显示loading
                element.find('.uploadFile-list ul').append('<li><div class="icon-file-loading">'+file+' 上传中...</div></li>');
                $('#'+btnIdString).prop('disabled', true);
            },
            onComplete: function(file, response) {
                if($.isFunction(params.onComplete)) {
                    params.onComplete(file, response);
                }
                try {
                    var res = JSON.parse(response);
                    if(res.code==0) {
                        var data = res.data;
                        $('#' + btnIdString).prop('disabled', false);

                        if (params.length > 0 && element.find('.uploadFile-list li').length >= params.length) {
                            $('#' + btnIdString).prop('disabled', true);
                        }
                        outputFileList(element, data, file, params);
                    }else {
                         $.sui.error(res.message || "网络连接超时，请检查网络");
                    }
                } catch(err) {
                    $.sui.error("网络连接超时，请检查网络。");
                }
            }
        });
        
        if(params.length > 0 && params.data && params.data.length == params.length) {
            $('#' + btnIdString).prop('disabled', true);
        }
    }
    
    $.fn.uploadFile = function(params) {
        
        var _self = $(this);
        
        // 获取数据
        if(typeof params == 'string' && params == 'get') {
            var data = [];
            _self.find('.url').each(function(index, element) {
                var current = $(element);
                data.push({
                    url: current.val(),
                    fullName: current.siblings('.full-name').val(),
                    fileName: current.siblings('.file-name').val(),
                    ext: current.siblings('.ext').val(),
                    size: current.siblings('.size').val()
                });
            });
            return data;
        }
        
        // 初始化
        params = $.extend({
            length: 0,  // 上传数量，0代表不限制数量
            url: '',    // 上传的URL地址
            buttonText: '上传附件',
            tips: '',   // 提示语
            controlName: 'attachment', // 控件名称，(传统表单提交时用于获取数据, ajax提交数据忽略此项)
            fileControlName: 'file', //file控件的名称，服务端接收用到，默认为file
            accept: [], // 数组, ['jpg', 'doc', 'zip'], 默认不限制类型
            isEdit: true,   // 是否允许修改名称
            isShowSize: true, // 是否显示文件大小
            isSort: true,  // 是否允许拖动排序
            data: null, // 数据格式 [{url, fullName, size},{url, fullName, size}]
            onSubmit: null, // 自定义数据验证 function(file, suffix) {}  返回false可阻挡上传操作
            onComplete: null // Ajax上传完毕触发事件 function(file, response) {}
        }, params);
        
        if($(this).length > 1) {
            alert('初始化失败，插件只支持一个实例。');
            return;
        }
        
        initUploadFile(_self, params);
    }
})(jQuery);