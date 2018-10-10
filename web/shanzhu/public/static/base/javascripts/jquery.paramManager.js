/**
 * jquery.paramManage Plug-in
 * date: 2016-01-28
 * author: summer <summer@fcuh.com>
 * version: 1.0.3
 */
;(function($) {
    
    if (!window.console) window.console = {};
    if (!window.console.log) window.console.log = function() {};
    
    //显示没有数据提示
    var showNotData = function($this) {
        $this.append('<tr class="param-manager-notdata"><td colspan="10">请添加参数 ^_^ </td></tr>');
    }
    
    //不显示没有数据提示
    var removeNotData = function($this) {
        if($this.children('.param-manager-notdata').length > 0) {
            $this.children('.param-manager-notdata').remove();
        }
    }
    
    //删除节点 - 递归删除子节点
    var deleteItem = function($body, id) {
        $('#' + id).remove();
        if($body.find('tr[data-parentid="' + id + '"]').length > 0) {
            $body.find('tr[data-parentid="' + id + '"]').each(function(index, element) {
                deleteItem($body, $(element).data('id'));
            });
        }
        if($body.find('tr').length <= 0) {
            showNotData($body);
        }
    }
    
    //绑定事件
    var bindParamEvent = function($this) {
        
        $body = $this.parent();
        
        //显示输入控件
        $this.children('.param-manager-input-td').bind('click', function() {
            $ele = $(this);
            if($(this).children('.param-manager-input').length <= 0) {
                var text = $(this).text();
                var element = $('<input type="text" class="param-manager-input" value="' + text + '" />');
                $(this).html('');
                element.appendTo(this);
                element.bind('blur', function() {
                    var level = $(this).parent().parent().data('level');
                    var levelStr = '';
                    if($(this).parent().hasClass('param-manager-level')) {
                        for(var i= 0; i < level; i++) {
                            levelStr += '<span class="param-manager-retract"></span>';
                        }
                    }
                    var text = $.trim($(this).val());
                    $ele.html(levelStr + text);
                });
                element.focus().select();
            }
        });
        
        //删除节点
        $this.find('.param-manager-delete').bind('click', function() {
            deleteItem($body, $(this).parent().parent().data('id'));
        });
        
        //添加子节点
        $this.find('.param-manager-insert').bind('click', function() {
            var parentid = $(this).parent().parent().data('id');
            var level = $this.data('level');
            level++;
            addParamView($this, parentid, level);
        });
        
        //选择类型
        $this.find('.param-manager-type').bind('change', function() {
            if($(this).val() == 'array<object>') {
                $this.find('.param-manager-insert').show();
            } else {
                $this.find('.param-manager-insert').hide();
                $body.find('tr[data-parentid="' + $(this).parent().parent().data('id') + '"]').each(function(index, element) {
                    deleteItem($body, $(this).data('id'));
                });
            }
        });
    }
    
    //初始化界面
    var initView = function($this) {
        var strHtml = '<table class="param-manager-table">' +
            '<thead>' +
                '<tr>' +
                    '<th width="60"><a href="javascript:;" class="root-manager-insert"></a></th>' +
                    '<th>变量名</th>' +
                    '<th width="20%">含义</th>' +
                    '<th width="130">类型</th>' +
                    '<th width="130">长度</th>' +
                    '<th width="60">必填</th>' +
                    '<th>默认值</th>' +
                '</tr>' +
            '</thead>' +
            '<tbody>' +
            '</tbody>' +
        '</table>';
        $this.append(strHtml);
        $this.find('.root-manager-insert').on('click', function() {
            $this.find('.param-manager-notdata').hide();
            addParamView($this.find('tbody'), 'uid_0', 0);
        });
        return $this.find('tbody');
    }


    //添加
    var addParamView = function($this, parentid, level, data) {
        removeNotData($this);
        var id = 'param_manager_' + (new Date().getTime() + parseInt(Math.random() * 100000));
        var levelStr = '';
        for(var i= 0; i < level; i++) {
            levelStr += '<span class="param-manager-retract"></span>';
        }
        
        if(data) {
            levelStr += data.id;
        } else {
            data = {};
        }

        if(typeof data.required == 'undefined') {
            data.required = 1;
        }
        if(typeof data.name == 'undefined') {
            data.name = '';
        }
        if(typeof data.type == 'undefined') {
            data.type = 'string';
        }
        if(typeof data.param_length == 'undefined') {
            data.param_length = '';
        }
        if(typeof data.specified == 'undefined') {
            data.specified = '';
        }
        if(typeof data.child == 'undefined') {
            data.child = [];
        }

        var element = $('<tr class="param-manager-tr" data-parentid="' + parentid + '" id="' + id + '" data-level="' + level + '" data-id="' + id + '">' +
            '<td width="60" class="param-manager-op-td">' +
                '<a href="javascript:;" class="param-manager-delete"></a>' +
                '&nbsp;&nbsp;&nbsp;' +
                '<a href="javascript:;" class="param-manager-insert" style="display:none"></a>' + 
            '</td>' +
            '<td class="param-manager-input-td param-manager-level param-manager-id">' + levelStr + '</td>' +
            '<td width="20%" class="param-manager-input-td param-manager-name"></td>' +
            '<td width="150">' +
            '<select type="text" class="param-manager-select param-manager-type">' +
            '<option value="int">整形 (int)</option>' +
            '<option value="bigInt">长整形 (bigInt)</option>' +
            '<option value="string">字符串 (string)</option>' +
            '<option value="double">双精度 (double)</option>' +
            '<option value="time">时间 (time)</option>' +
            '<option value="date">日期 (date)</option>' +
            '<option value="datetime">日期时间 (datetime)</option>' +
            '<option value="timestamp">时间戳 (timestamp)</option>' +
            '<option value="array<object>">array&lt;object&gt;</option>' +
            '<option value="array<number>">array&lt;number&gt;</option>' +
            '<option value="array<string>">array&lt;string&gt;</option>' +
            '<option value="array<boolean>">array&lt;boolean&gt;</option>' +
            '<option value="array">array</option>' +
            '<option value="object">对象 (object)</option>' +
            '<option value="boolean">boolean</option>' +
            '</select>' +
            '</td>' +
            '<td width="8%" class="param-manager-input-td param-manager-param_length text-center"></td>' +
            '<td width="60">'+
            '<select type="text" class="param-manager-select param-manager-required">' +
            '<option value="1">是</option>' +
            '<option value="0">否</option>' +
            '</select>' +
            '</td>' +
            '<td width="8%" class="param-manager-input-td param-manager-specified text-center"></td>' +
        '</tr>');

        if(parentid == 'uid_0') {
            element.appendTo($this);
        } else {
			if(data) {
				var childAll = $('tr[data-parentid="' + $this.data('id') + '"]');
				if(childAll.length <= 0) {
					element.insertAfter($this);
				} else {
					element.insertAfter(childAll[childAll.length - 1]);
				}
			} else {
				element.insertAfter($this);
			}
        }
        
        // 初始化数据
        if(data) {
            //文本框初始值
            element.find('.param-manager-name').text(data.name);
            element.find('.param-manager-number').text(data.number);
            element.find('.param-manager-specified').text(data.specified);
            element.find('.param-manager-param_length').text(data.param_length);
            //下拉框初始值
            element.find('.param-manager-type option[value="' + data.type + '"]').prop('selected', true);
            element.find('.param-manager-required option[value="' + data.required + '"]').prop('selected', true);

            if(data.type == 'array<object>') {
                element.find('.param-manager-insert').show();
            }
        }
        
        bindParamEvent(element);
        return element;
    }
    
    //获取数据
    function getData($this, parentid) {
        var returnData = [];
        $this.find('tr[data-parentid="' + parentid + '"]').each(function(index, element) {
            var element = $(element);
            var jsonData = {
                'id': element.find('.param-manager-id').text(),
                'name': element.find('.param-manager-name').text(),
                'type': element.find('.param-manager-type').val(),
                'param_length': element.find('.param-manager-param_length').text(),
                'specified': element.find('.param-manager-specified').text(),
                'required': element.find('.param-manager-required').val(),
                'child': []
            };
            var id = $(element).data('id');
            if($this.find('tr[data-parentid="' + id + '"]').length > 0) {
                jsonData.child = getData($this, id);
            }
            returnData.push(jsonData);
        });
        return returnData;
    }
    
    //初始化数据
    function initializeData($this, parent, dataList, cover) {
        if(parent == null) {
            var parentid = 'uid_0';
            var level = 0;
        } else {
            var parentid = $(parent).data('id');
            var level = $(parent).data('level') + 1;
        }
        if(typeof cover == 'undefined' || cover == "0"){
            cover = false;
        }
        if(cover){
            $this.html('');
        }
        for(var i = 0; i < dataList.length; i++) {

            var element = addParamView($this, parentid, level, dataList[i]);
            if(dataList[i].child.length > 0) {
                initializeData(element, element, dataList[i].child);
            }
        }
    }

    var dataConvert =  function(object) {
        var returnData = [];
        for(var data in object) {
            var tType = 'string';
            var child = [];
            switch (typeof object[data]){
                case 'number':
                    tType= 'int';break;
                case 'object':
                    tType= 'array<object>';
                    child = dataConvert(object[data]);
                    break;
                default :
                    tType= 'string';break;
            }

            var o = {
                "id": data,
                "name": "",
                "type": tType,
                "param_length": '',
                "required": 1,
                "specified": object[data],
                "child": child
            };
            returnData.push(o);
        }
        return returnData;
    }
    //入口
    $.fn.paramManager = function(defaults) {

        var configs = $.extend({
            data: []
        }, defaults);
        
        var $this = $(this);
        var object = initView($this);
        
        //初始化数据
        if(!configs.data || configs.data.length <= 0) {
            showNotData(object);
        } else {
            initializeData(object, null, configs.data);
        }

        //获取数据
        object.get = function() {
            var data = getData(this, 'uid_0');
            return data;
        }
        
        //添加
        object.add = function() {
            addParamView(this, 'uid_0', 0);
        }

        object.set = function (data, cover) {
            if(typeof cover == 'undefined'){
                cover = false;
            }
            initializeData(this, null, data, cover);
        }

        return object;
        
    }

    $.paramConvert = function(data) {
        return dataConvert(data);
    }
})(jQuery);