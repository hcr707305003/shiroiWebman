<?php

namespace generate\field;

class Title extends Field
{

    public static string $html = <<<EOF
<div class="form-group text-center [FIELD_NAME]-form-group">
    <h2>[FORM_NAME]</h2>
</div>\n
EOF;

    public static function create($data): string
    {
        return  str_replace(
            array('[FORM_NAME]'),
            array($data['form_name']),
            self::$html);
    }

}