<?php

namespace generate\field;

class DisableText extends Field
{

    public static string $html = <<<EOF
<div class="form-group row rowText [FIELD_NAME]-form-group">
    <label for="[FIELD_NAME]" class="col-sm-2 col-form-label">[FORM_NAME]</label>
    <div class="col-sm-10 col-md-4 formInputDiv">
        <input id="[FIELD_NAME]" name="[FIELD_NAME]" disabled value="{\$data.[FIELD_NAME]|default='[FIELD_DEFAULT]'}" placeholder="请输入[FORM_NAME]" type="text" class="form-control fieldText">
    </div>
</div>\n
EOF;

    public static function create($data): string
    {
        return  str_replace(
            array('[FORM_NAME]', '[FIELD_NAME]', '[FIELD_DEFAULT]'),
            array($data['form_name'], $data['field_name'], $data['field_default']),
            self::$html);
    }

}