/**
 * jQuery 图片组插件
 * date: 2016-04-27
 * author: summer <summer@68coder.com>
 * version: 1.0.0
 *
 * 需要在使用的地方引入fancybox插件的css文件
 *
 */
;(function ($) {

    var getConfig = function (configs, data, callback) {
        var doc = window.top != window.self ? window.top.document : window.self.document;

        $.each(['name', 'desc'], function (k, v) {
            if (typeof(data[v]) == 'undefined') data[v] = '';
        });

        var dialogConfig = {
            modal: true,
            resizable: false,
            dialogClass: 'jquery-ui-dialog-iframe jquery-dialog-form',
            closeText: '',
            title: '图片属性',
            width: 530,
            close: function () {
                $(this).remove()
            },
            buttons: [{
                text: '确定',
                click: function () {
                    if ($.isFunction(callback)) {
                        var d = {};
                        if (configs.isName) d.name = $('#image_group_dialog_name', doc).val();
                        if (configs.isDesc) d.desc = $('#image_group_dialog_desc', doc).val();
                        callback(d);
                    }
                    $(this).dialog('close').remove();
                }
            }, {
                text: '取消',
                click: function () {
                    $(this).dialog('close').remove();
                }
            }]
        };

        var partHtml = '';

        var displayImage = '<td rowspan="2" style="width:100px;border-right:1px #eee solid"><center><img src="' + data.url + '" width="80" height="80" /></center></td>'

        if (configs.isName && !configs.isDesc) dialogConfig = $.extend(dialogConfig, {width: 430});

        if (configs.isName) partHtml += '<tr>$ImageDisplay1<th style="width:50px;">图片名称</th><td><input type="text" id="image_group_dialog_name" style="width:200px" value="' + data.name + '" /></td></tr>';
        if (configs.isDesc) partHtml += '<tr>$ImageDisplay2<th style="width:50px;">图片描述</th><td><textarea id="image_group_dialog_desc" style="width:300px;height:80px;">' + data.desc + '</textarea></td></tr>';

        if (configs.isDesc && !configs.isName) {
            partHtml = partHtml.replace('$ImageDisplay2', displayImage);
            partHtml = partHtml.replace('$ImageDisplay1', '');
        } else {
            partHtml = partHtml.replace('$ImageDisplay1', displayImage);
            partHtml = partHtml.replace('$ImageDisplay2', '');
        }

        var strHtml = '<div id="imageGroupDialog" style="display:none">' +
            '<table>$partHtml</table>' +
            '</div>';
        strHtml = strHtml.replace('$partHtml', partHtml);
        $('body', doc).append(strHtml);
        $('#imageGroupDialog', doc).dialog(dialogConfig);
    }

    //删除图片
    var removeImagePic = function (configs, element, current) {
        current.find('.image-group-close').bind('click', function () {
            var imagePicker = element.find('.image-group-box');
            var index = imagePicker.index(current);
            for (var i = index + 1; i < imagePicker.length; i++) {
                var currImagePicker = imagePicker.eq(i);
                var oldName = '[name="' + configs.partName + '[' + i + ']';
                var newName = configs.partName + '[' + (i - 1) + ']';
                currImagePicker.children(oldName + '[name]"]').attr('name', newName + '[name]');
                currImagePicker.children(oldName + '[desc]"]').attr('name', newName + '[desc]');
                currImagePicker.children(oldName + '[width]"]').attr('name', newName + '[width]');
                currImagePicker.children(oldName + '[height]"]').attr('name', newName + '[height]');
                currImagePicker.children(oldName + '[size]"]').attr('name', newName + '[size]');
                currImagePicker.children(oldName + '[url]"]').attr('name', newName + '[url]');
            }
            current.remove();
            $('#' + configs.partName).val(element.find('.image-group-box').length);
            if (element.children('.image-group-add').length <= 0) {
                loadAddButton(configs, element);
            }
        });
    }

    //加入图片
    var addImagePic = function (configs, element, data) {

        $.each(['name', 'desc', 'width', 'height', 'size', 'url'], function (k, v) {
            if (typeof(data[v]) == 'undefined') data[v] = '';
        });

        var name = configs.partName + '[' + element.find('.image-group-box').length + ']';

        var strHtml = '<div class="image-group-box">' +
            '<div class="image-group-bar">' +
            '<div class="image-group-edit" style="display:none"><i class="fa fa-cog"></i></div>' +
            '<div class="image-group-close"><i class="fa fa-close"></i></div>' +
            '</div>' +
            '<div class="image-group-pic"><center><img src="' + data.url + '" style="display:none" /></center></div>' +
            '<input type="hidden" name="' + name + '[name]" class="image-group-pic-name" value="' + data.name + '" />' +
            '<textarea name="' + name + '[desc]" style="display:none" class="image-group-pic-desc">' + data.desc + '</textarea>' +
            '<input type="hidden" name="' + name + '[width]"  class="image-group-pic-width" value="' + data.width + '" />' +
            '<input type="hidden" name="' + name + '[height]"  class="image-group-pic-height" value="' + data.height + '" />' +
            '<input type="hidden" name="' + name + '[size]"  class="image-group-pic-size" value="' + data.size + '" />' +
            '<input type="hidden" name="' + name + '[url]"  class="image-group-pic-url" value="' + data.url + '" />';
        '</div>';
        var newElement = $(strHtml);
        newElement.appendTo(element.children('.image-group-list'));
        removeImagePic(configs, element, newElement);

        if (configs.isName || configs.isDesc) {
            newElement.find('.image-group-edit').show().bind('click', function () {
                var d = {
                    name: newElement.find('.image-group-pic-name').val(),
                    desc: newElement.find('.image-group-pic-desc').val(),
                    url: newElement.find('.image-group-pic-url').val()
                }
                getConfig(configs, d, function (data) {
                    if (configs.isName) newElement.find('.image-group-pic-name').val(data.name);
                    if (configs.isDesc) newElement.find('.image-group-pic-desc').val(data.desc);
                });
            });
        }

        $('#' + configs.partName).val(element.find('.image-group-box').length);

        if (element.find('.image-group-box').length >= configs.length) {
            //移除上传控件
            $('input[type=file][name=' + configs.uploadPart + ']').parent().remove();
            element.children('.image-group-add').remove();
        }

        var img = new Image();
        img.onload = function () {
            var sizeInfo = img.height > img.width ? {height: '80px'} : {width: '80px'};

            //点击图片, 调用fancybox插件预览图片
            newElement.find('.image-group-pic img').css(sizeInfo).show().bind('click', function () {

                var currentIndex = element.find('.image-group-box').index(newElement);
                var imageList = [];
                var imageTemp = [];
                element.find('.image-group-box img').each(function (index, element) {
                    if (index < currentIndex) {
                        imageTemp.push($(element).attr('src'));
                    } else {
                        imageList.push($(element).attr('src'));
                    }
                });
                imageList = imageList.concat(imageTemp);

                if (window.top != window.self) {
                    top.$.fancybox.open(imageList, {padding: 10, openEffect: 'elastic', closeEffect: 'elastic'});
                } else {
                    $.fancybox.open(imageList, {padding: 10, openEffect: 'elastic', closeEffect: 'elastic'});
                }
            });

        }
        img.onerror = function () {
            newElement.find('.image-group-pic center').html('<div class="muted">无效图</div>');
        }
        img.src = data.url;
    }

    //添加按钮
    var loadAddButton = function (configs, element) {
        element.append('<div id="' + configs.partName + '_add" class="image-group-add"><i class="fa fa-picture-o fa-2x"></i><div>上传图片</div></div>');
        var addBtn = element.children('.image-group-add');
        new AjaxUpload('#' + configs.partName + '_add', {
            action: configs.uploadUrl,
            name: configs.uploadPart,
            accept: '.jpg,.png,.jpeg,.gif',
            onSubmit: function (file, suffix) {
                var patrn = /^(jpg|png|jpeg|gif)$/i;
                if (!patrn.test(suffix)) {
                    $.sui.alert('不允许上传 *.' + suffix + ' 格式的文件。<br>格式局限于: *.jpg *.png *.jpeg *.gif');
                    return false;
                }
                addBtn.hide();
                element.children('.image-group-loading').show();
            },
            onComplete: function (file, response) {
                try {
                    addBtn.show();
                    element.children('.image-group-loading').hide();
                        var res = JSON.parse(response);
                        if(res.code==0){
                            var data = res.data;
                            if (configs.isName || configs.isDesc) {
                                getConfig(configs, data, function (msg) {
                                    if (configs.isName) data.name = msg.name;
                                    if (configs.isDesc) data.desc = msg.desc;
                                    addImagePic(configs, element, data);
                                });
                            } else {
                                addImagePic(configs, element, data);
                            }
                        } else {
                            $.sui.error(res.message || "网络连接超时，请检查网络");
                        }
                } catch (e) {
                    $.sui.error("网络连接超时，请检查网络。");
                }
            }
        });
    }

    //获取数据
    var getData = function (element) {
        var list = [];
        element.find('.image-group-box').each(function (index, element) {
            var data = {
                name: $(this).children('.image-group-pic-name').val(),
                desc: $(this).children('.image-group-pic-desc').val(),
                width: $(this).children('.image-group-pic-width').val(),
                height: $(this).children('.image-group-pic-height').val(),
                size: $(this).children('.image-group-pic-size').val(),
                url: $(this).children('.image-group-pic-url').val()
            }
            list.push(data);
        });
        return list;
    }

    var createId = function () {
        var id = 'image_' + (Math.floor(Math.random() * 99999) + 10000);
        if ($('input[type=file][name=' + id + ']').length > 0) {
            id = createId();
        }
        return id;
    }

    $.fn.imageGroup = function (options) {

        $this = $(this);
        if ($(this).length > 1) {
            console.error('imagePicker只允许传入单例节点。');
            return;
        }

        if (typeof(options) == 'string') {
            if (options == 'data') {
                return getData($this);
            }
            return;
        }

        var configs = $.extend({
            partName: '',	// 部件名称
            length: 1,	// 图片数量
            isName: true, // 是否需要配置名称
            isDesc: true, // 是否需要配置描述
            defaults: [],	//默认值
            uploadUrl: 'upload.php', //上传地址
            uploadPart: createId() //上传控件名
        }, options);

        $this.append('<input type="hidden" class="image-group" id="' + configs.partName + '" name="' + configs.partName + '" value="' + configs.defaults.length + '"><div class="image-group-list"></div><div class="image-group-loading" style="display:none"><i></i><div>正在上传</div></div>');
        loadAddButton(configs, $this);

        //初始化数据
        $.each(configs.defaults, function (key, data) {
            if (key + 1 > configs.length) return;
            addImagePic(configs, $this, data);
        });

        //排序
        $this.children('.image-group-list').sortable({
            stop: function (event, ui) {
                var imagePicker = $this.find('.image-group-box');
                for (var i = 0; i < imagePicker.length; i++) {
                    var currImagePicker = imagePicker.eq(i);
                    var name = configs.partName + '[' + i + ']';
                    currImagePicker.children('.image-group-pic-name').attr('name', name + '[name]');
                    currImagePicker.children('.image-group-pic-desc').attr('name', name + '[desc]');
                    currImagePicker.children('.image-group-pic-width').attr('name', name + '[width]');
                    currImagePicker.children('.image-group-pic-height').attr('name', name + '[height]');
                    currImagePicker.children('.image-group-pic-size').attr('name', name + '[size]');
                    currImagePicker.children('.image-group-pic-url').attr('name', name + '[url]');
                }
            }
        });
    }
})(jQuery);