<?php
/**
 * User: Shiroi
 * EMail: 707305003@qq.com
 */

namespace generate\field;

class IconFont
{
    protected static string $iconFontCss = 'https://at.alicdn.com/t/c/font_4111627_is3j0w9ropr.css';

    protected static string $iconFontJs = 'https://at.alicdn.com/t/c/font_4111627_is3j0w9ropr.js';

    public static string $html = <<<EOF
    <script src="[ICON_FONT_JS_LINK]"></script>
    <link rel="stylesheet" href="[ICON_FONT_CSS_LINK]" />
    <style>
        .iconpicker-a[FIELD_NAME]:focus {
            background-color: grey 
        }
        .iconpicker-a[FIELD_NAME] {
            float:left;
            border-radius: 3px;
            padding: 5px;
            border: 1px solid #ddd;
            color: inherit;
            width:50px;
            height:60px;
            font-size:40px;
            display:block;
            text-align: center;
        }
    </style>
    <div class="form-group row rowIcon">
        <label for="[FIELD_NAME]" class="col-sm-2 col-form-label">[FORM_NAME]</label>
        <div class="col-sm-10 col-md-4 formInputDiv">
            <div class="input-group iconpicker-container">
                <div class="input-group-prepend">
                    <span class="input-group-text iconpicker-component">
                        <i class="far fa-calendar-alt"></i>
                    </span>
                </div>
                <input maxlength="64" id="[FIELD_NAME]" name="[FIELD_NAME]"
                       value="[FIELD_DEFAULT]" class="form-control fieldIcon"
                       placeholder="请选择[FORM_NAME]">
            </div>
        </div> 
        <div id="[FIELD_NAME]Box" style="display: none">
            [ICON_DATA]
        </div>

    <script>
        function choose(my) {
            let i = $(my).attr("iconname");
            $("#[FIELD_NAME]").val(i);
            //关闭所有
            layer.closeAll();
            // console.log(i)
        }
        
        $('#[FIELD_NAME]').click(function() {
            //关闭所有
            layer.closeAll();
            const index = parent.layer.open({
                type: 1,
                title: '[FORM_NAME]',
                skin: 'layui-layer-skin', //样式类名
                //宽高自适应
                area: [($(window).width()/2)+'px', ($(window).height()/2)+'px'],
                shade: 0,
                maxmin: true,
                content: $("#[FIELD_NAME]Box").html(),
            });
        });
    </script>
</div>\n
EOF;

    public static function create($type, $name, $field, $content, $type_arr): string
    {
        $iconData = "";
        //动态获取iconfont矢量图
        $css_data = @file_get_contents($type_arr['{{href}}'] ?? self::$iconFontCss);
        preg_match_all('/\.icon-[a-zA-Z0-9_-]+/', $css_data, $matches);
        $class_names = array_unique($matches[0]);
        foreach ($class_names as $icon) {
            $icon = ltrim($icon, '.');
            $iconData .= "<svg class='iconpicker-a[FIELD_NAME]' aria-hidden='true' iconName='{$icon}' onclick='choose(this)'><use xlink:href='#{$icon}''></use></svg>";
        }
        self::$html = str_replace(array('[ICON_DATA]', '[ICON_FONT_CSS_LINK]', '[ICON_FONT_JS_LINK]'), array($iconData, $type_arr['{{href}}'] ?? self::$iconFontCss, $type_arr['{{src}}'] ?? self::$iconFontJs), self::$html);

        return str_replace(array('[FORM_NAME]', '[FIELD_NAME]', '[FIELD_DEFAULT]'), array($name, $field, $content), self::$html);
    }
}