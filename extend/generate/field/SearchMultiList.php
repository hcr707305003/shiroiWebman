<?php
/**
 * User: Shiroi
 * EMail: 707305003@qq.com
 */

namespace generate\field;

class SearchMultiList
{
    public static string $html = <<<EOF
<div class="form-group row rowText [FIELD_NAME]-form-group">
    <label for="[FIELD_NAME]" class="col-sm-2 col-form-label">[FORM_NAME]</label>
    <div class="col-sm-10 col-md-4 formInputDiv">
<!--        <input id="[FIELD_NAME]_name" value="[FIELD_DEFAULT]" class="form-control fieldText" placeholder="请选择[FORM_NAME]" readonly>-->
        <textarea class="form-control" rows="5" id="[FIELD_NAME]_name" style="resize:none;" readonly >[FIELD_DEFAULT_NAME]</textarea>
        <input id="[FIELD_NAME]" value="[FIELD_DEFAULT]" name="[FIELD_NAME]" type="hidden">
        <div style="display: none" id="[DEFAULT_CONTENT_ID]">[DEFAULT_CONTENT]</div>
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
                let value = iframe.getInputValue();
                let ids = value.map(item => item.id).join(',');
                let names = value.map(item => item.name).join(',');
                console.log(value)
                $('#[DEFAULT_CONTENT_ID]').html(JSON.stringify(value))
                $('#[FIELD_NAME]').val(ids);
                $('#[FIELD_NAME]_name').val(names);
                if(typeof get_[FIELD_NAME] === 'function' ) get_[FIELD_NAME](value);
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
        $uniqid = $type . uniqid();
        $url = "/admin/user/userMultiList?default_content_id={$uniqid}";
        $name_content = '';
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

        $names = [];
        $ids = [];
        if(is_array($option)) {
            foreach ($option as $v) {
                $names[] = $v['name'];
                $ids[] = $v['id'];
            }
            $name_content = implode(',', $names);
            $content = implode(',', $ids);

            foreach ($option as &$v) {
                $v['text'] = $v['name'];
                $v['selected'] = true;
            }
        }

        return str_replace(array('[FORM_NAME]', '[FIELD_NAME]', '[FIELD_DEFAULT]', '[FIELD_DEFAULT_NAME]', '[URL]', '[DEFAULT_CONTENT]', '[DEFAULT_CONTENT_ID]'), array($title,$field,$content,$name_content,$url, empty($option) ? '': json_encode($option), $uniqid), self::$html);
    }
}