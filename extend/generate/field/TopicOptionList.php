<?php
/**
 * User: Shiroi
 * EMail: 707305003@qq.com
 */

namespace generate\field;

class TopicOptionList
{
    public static string $html = <<<EOF
<style>
table {
    table-layout:fixed;
}
td {
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
}
.[FIELD_NAME]_input_number {
    width: 50px;
}

.[FIELD_NAME]_topic_option_thead tr th:nth-child(1) {
    width: 100px;
}

.[FIELD_NAME]_topic_option tr td:nth-child(1) {
    width: 100px;
}

.[FIELD_NAME]_topic_option tr td:nth-child(1) textarea {
    height: 100%;
    width: 100%;
    min-height: 75px;
    overflow:auto;
    background:none;
    outline:none;
    border:none;
    resize: none;
}

.[FIELD_NAME]_topic_option tr td:nth-child(2) {
    height: 100px;
}

.[FIELD_NAME]_topic_option tr td:nth-child(2) textarea {
    height: 100%;
    width: 100%;
    min-width: 100%;
    min-height: 75px;
    overflow:auto;
    background:none;
    outline:none;
    border:none;
}

.[FIELD_NAME]_topic_option_thead tr th:nth-child(3) {
    width: 100px;
}

.[FIELD_NAME]_topic_option tr td:nth-child(3) a {
    color:#E65F5B;
    cursor: pointer;
}

</style>
<div class="form-group row rowText [FIELD_NAME]-form-group">
    <label class="col-sm-4 col-form-label">[FORM_NAME] <a style="color: #5589ff;cursor: pointer;" onclick="[FIELD_NAME]_addTopicOption()">添加选项</a></label>
    <div class="table-responsive formInputDiv">
        <table class="table table-hover table-bordered dataTable text-nowrap">
            <thead class="[FIELD_NAME]_topic_option_thead">
                <tr>
                    <th>选项编号</th>
                    <th>选项内容</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody class="[FIELD_NAME]_topic_option">
              [FIELD_DEFAULT]
            </tbody>
        </table>
        <script>
            function [FIELD_NAME]_delTopicOption(that) {
                $(that).parent().parent().remove();
            }
            
            function [FIELD_NAME]_addTopicOption() {
              $('.[FIELD_NAME]_topic_option').append("<tr><td><textarea></textarea></td><td><textarea></textarea></td><td><a onclick='[FIELD_NAME]_delTopicOption(this)'>删除</a></td></tr>")
            }
            
        </script>
    </div>
</div>\n
EOF;

    public static function create($type, $name, $field, $content, $option): string
    {
        $option_html = "";
        if(is_array($content)) foreach ($content as $o) {
            $option_html .= <<<EOF
                <tr>
                    <td><textarea>{$o['seq']}</textarea></td>
                    <td><textarea>{$o['content']}</textarea></td>
                    <td><a onclick="{$field}_delTopicOption(this)">删除</a></td>
                </tr>
EOF;
        }
//        dd($type, $name, $field, $content, $option);
        return str_replace(array('[FORM_NAME]', '[FIELD_NAME]', '[FIELD_DEFAULT]'), array($name,$field,$option_html), self::$html);
    }
}