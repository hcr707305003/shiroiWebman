<?php

namespace generate\field;

class SearchList
{
    public static string $html = <<<EOF
<div class="form-group row rowText [FIELD_NAME]-form-group">
    <label for="[FIELD_NAME]" class="col-sm-2 col-form-label">[FORM_NAME]</label>
    <div class="col-sm-10 col-md-4 formInputDiv">
        <input maxlength="64" id="[FIELD_NAME]_name" value="[FIELD_DEFAULT_NAME]" class="form-control fieldText" placeholder="请选择[FORM_NAME]" readonly>
        <input type="hidden" id="[FIELD_NAME]" value="[FIELD_DEFAULT]" name="[FIELD_NAME]">
    </div>
</div>\n
<script>
    $('#[FIELD_NAME]_name').click(function () {
        //关闭所有
        layer.closeAll();
        const index = parent.layer.open({
            type: 2,
            title: '[FORM_NAME]',
            skin: 'layui-layer-skin', //样式类名
            //宽高自适应
            area: [($(window).width()/2)+'px', (window.outerHeight/2)+'px'],
            shade: 0,
            maxmin: true,
            content: "[URL]",
            btn: ['确认', '取消'],
            //按钮居中显示
            btnAlign:'c',
            //确认按钮事件
            yes : function(index, layero) {
                let iframe = layero.find('iframe')[0].contentWindow;
                let key = iframe.getInputValue();
                let name = iframe.getInputText();
                $('#[FIELD_NAME]').val(key);
                $('#[FIELD_NAME]_name').val(name);
                if(typeof get_[FIELD_NAME] === 'function' ) get_[FIELD_NAME](key);
                if(typeof get_[FIELD_NAME]_name === 'function' ) get_[FIELD_NAME]_name(name);
                parent.layer.close(index);
            },
            //取消按钮事件
            btn2:function(){
                parent.layer.close(index);
            }
        });
    });
</script>
EOF;

    public static function create($type, $info, $field, $content, $option): string
    {
        $url = '/admin/user/userList';
        if(is_array($info)) {
            if(isset($info['{{title}}'])) {
                $title = $info['{{title}}']??'标题';
            }
            if(isset($info['{{url}}'])) {
                $url = $info['{{url}}'];
            }
            if(isset($info['{{param}}'])) {
                $url .= '?'.http_build_query($info['{{param}}']);
            }
        } else {
            $title = $info;
        }

        return str_replace(array('[FORM_NAME]', '[FIELD_NAME]', '[FIELD_DEFAULT]', '[FIELD_DEFAULT_NAME]', '[URL]'), array($title,$field,$content,${"{$field}_name"} ?? '',$url), self::$html);
    }
}