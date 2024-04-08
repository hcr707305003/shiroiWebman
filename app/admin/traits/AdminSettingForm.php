<?php
/**
 * 设置表单操作
 * @author yupoxiong<i@yufuping.com>
 */
namespace app\admin\traits;

use generate\field\Field;

trait AdminSettingForm
{
    protected function getFieldTable($type, $name, $field, $content, $option, $url_condition='')
    {
        $type_arr = $type;
        if(is_array($type) && isset($type['{{type}}'])) {
            $type = $type['{{type}}'];
            unset($type_arr['{{type}}']);
        }

        /** @var Field $fieldClass */
        $fieldClass = '\\generate\\field\\' . parse_name($type === 'switch' ? 'switch_field' : $type, 1, true);
        $form = '';
        switch ($type) {
            case 'image':
            case 'color_select':
            case 'color':
                $form = $fieldClass::$td_html;
                $content = str_replace("[FIELD_NAME]", $content,$form);
                break;
            case 'switch':
            case 'select':
                $content = $option;
                break;
            default:
                break;
        }
        return $content;
    }


    protected function getFieldForm($type, $name, $field, $content, $option, $url_condition='', $key = '', $data = [])
    {
        $type_arr = $type;
        if(is_array($type) && isset($type['{{type}}'])) {
            $type = $type['{{type}}'];
            unset($type_arr['{{type}}']);
        } else if(is_string($type)) {
            $type_arr = explode('|',$type);
            $type = reset($type_arr);
        }

        /** @var Field $fieldClass */
        $fieldClass = '\\generate\\field\\' . parse_name($type === 'switch' ? 'switch_field' : $type, 1, true);
        $form       = $fieldClass::$html;
        $form_value = "{\$data.[FIELD_NAME]|default='[FIELD_DEFAULT]'}";
        switch ($type) {
            case 'switch':
                if($option && is_array($option)) {
                    $status = array_keys($option);
                    $values = array_values($option);
                } else {
                    $status = [1,0];
                    $values = ['是', '否'];
                }
                if(!in_array($content,$status)) {
                    $content = reset($status);
                }

                $form = str_replace("[field_default]",((int)array_search($content,$status) ? '' : 'checked'),$form);
                $form = str_replace(array('[ON_TEXT]', '[OFF_TEXT]'), $values, $form);
                $form = str_replace(array('[ON_STATUS]', '[OFF_STATUS]'), $status, $form);
                $form = str_replace(array($form_value, '[FIELD_NAME]', '[FORM_NAME]',), array($content, $field, $name,), $form);
                break;
            case 'select':

                $option_html = '';
                if(is_array($option)) foreach ($option as $key => $item) {
                    $select = '';
                    if ($content == $key) {
                        $select = 'selected';
                    }
                    $option_html .= '<option value="' . $key . '" ' . $select . '>' . $item . '</option>';
                }
                if(is_string($option)) foreach (explode("\r\n", $option) as $v) {
                    if($v) {
                        list($key,$item) = explode('||', $v);
                        $select = '';
                        if($content == $key) {
                            $select = 'selected';
                        }
                        $option_html .= '<option value="' . $key . '" ' . $select . '>' . $item . '</option>';
                    }
                }
                $form = str_replace('[OPTION_DATA]', $option_html, $form);
                $form = str_replace(array($form_value, '[FIELD_NAME]', '[FORM_NAME]',), array($content, $field, $name,), $form);
                if(in_array('text',$type_arr)) {
                    $form = str_replace('[CHANGE_TEXT]', '<a class="btn btn-default" id="'.$field.'_add" >新增</a>', $form);
                } else {
                    $form = str_replace('[CHANGE_TEXT]', '', $form);
                }

                break;
            case 'multi_select':
                $option_html = '';
                if(is_array($content)) {
                    foreach ($content as $c) {
                        $option_html .= '<option value="' . $c['name'] . '" selected>' . $c['name'] . '</option>';
                    }
                } else {
                    if(is_string($option)){
                        $option      = explode("\r\n", $option);
                    }
                    if(is_string($content)){
                        $content = explode(',',$content);
                    }
                    foreach ($option as $item) {
                        $option_key_value = explode('||', $item);
                        $select = '';
                        if(is_array($content)) {
                            if (in_array($option_key_value[0], $content, false)) {
                                $select = 'selected';
                            }
                        } else {
                            if ($option_key_value[0] == $content) {
                                $select = 'selected';
                            }
                        }
                        $option_html .= '<option value="' . $option_key_value[0] . '" ' . $select . '>' . $option_key_value[1] . '</option>';
                    }
                }

                $form    = str_replace('[OPTION_DATA]', $option_html, $form);
                $content = '';
                $form = str_replace(array($form_value, '[FIELD_NAME]',), array($content, $field,), $form);
                $form = ('\\generate\\field\\' . parse_name($type, 1, true))::modify($name,$form);
                break;
            case 'multi_select2':
//                dd($content, $option);
                $option_html = '';
                if(is_array($option)) foreach ($option as $type => $names) {
                    $option_son_html = "";
                    foreach ($names as $id => $n) {
                        $select = isset($content[$type][$id]) ? "selected='selected'": '';
                        $option_son_html .= "<option value='{$id}' {$select}>{$n}</option>";
                    }
                    $option_html .= "<optgroup label='{$type}'>{$option_son_html}</optgroup>";
                }
                $form    = str_replace('[OPTION_DATA]', $option_html, $form);
                $form = str_replace(array('[FIELD_NAME]', '[FORM_NAME]',), array($field, $name,), $form);
                break;
            case 'image':
                $search1 = "{if isset(\$data)}{\$data.[FIELD_NAME]}{/if}";
                $form    = str_replace(array($search1), array($content), $form);
                $form = str_replace(array($form_value, '[FIELD_NAME]', '[FORM_NAME]',), array($content, $field, $name,), $form);
                break;
            case 'cropperImage':

                $form = ('\\generate\\field\\' . parse_name($type, 1, true))::create($type, $name, $field, $content,$data);
                $form = str_replace(array($form_value, '[FIELD_NAME]', '[FORM_NAME]',), array($content, $field, $name,), $form);
                break;
            case 'editor':
                $search1 = "{\$data.[FIELD_NAME]|raw|default='[FIELD_DEFAULT]'}";

                $search2  = "{\$data.[FIELD_NAME]|raw|default='<p>[FIELD_DEFAULT]</p>'}";
                $replace2 = $content !== '' ? $content : '<p></p>';

                $form = str_replace(array($search1, $search2), array($content, $replace2), $form);
                $form = str_replace(array($form_value, '[FIELD_NAME]', '[FORM_NAME]',), array($content, $field, $name,), $form);
                break;
            case 'map':
                $position = is_string($content) ? explode(',', $content) : $content;
                $lng      = $position[0] ?? 117;
                $lng      = $lng > 180 || $lng < -180 ? 117 : $lng;

                $lat = $position[1] ?? 36;
                $lat = $lat < -90 || $lat > 90 ? 36 : $lat;

                $search1 = "{\$data.[FIELD_NAME_LNG]|default='117'}";
                $search2 = "{\$data.[FIELD_NAME_LAT]|default='36'}";
                $search3 = 'name="[FIELD_NAME_LNG]"';
                $search4 = 'name="[FIELD_NAME_LAT]"';

                $search5 = 'id="[FIELD_NAME_LNG]"';
                $search6 = 'id="[FIELD_NAME_LAT]"';
                $search7 = "$('#[FIELD_NAME_LNG]')";
                $search8 = "$('#[FIELD_NAME_LAT]')";

                $replace4 = $replace3 = 'name="' . $field . '[]"';
                $replace5 = 'id="' . $field . '_lng"';
                $replace6 = 'id="' . $field . '_lat"';
                $replace7 = "$('#" . $field . "_lng')";
                $replace8 = "$('#" . $field . "_lat')";

                $search9 = '[FIELD_NAME_LNG]';


                $form = str_replace(
                    array($search1, $search2, $search3, $search4, $search5, $search6, $search7, $search8, $search9),
                    array($lng, $lat, $replace3, $replace4, $replace5, $replace6, $replace7, $replace8, $field),
                    $form);

                $content = '';
                $form = str_replace(array($form_value, '[FIELD_NAME]', '[FORM_NAME]',), array($content, $field, $name,), $form);
                break;
            case 'son_list':
//                dd($name,$content,$type_arr,$option, $key, $data);
                //创建
                $form = ('\\generate\\field\\' . parse_name($type, 1, true))::create($name,$content,$type_arr,$option, $key, $data);
                $form = str_replace(['[FIELD_NAME]'],[$field], $form);
                break;
            case 'image_list':
                //创建
                $form = ('\\generate\\field\\' . parse_name($type, 1, true))::create($name,$content,$type);
                break;
            case 'district':
//                dd($form,$type, $name, $field, $content, $option,$data);
                $form = str_replace(array('[DISTRICT_SRC]'), array(file_get_contents(public_path('static/admin/js').'distinct.js')), $form);
                $form = str_replace(array('[PROVINCE]','[CITY]','[DISTRICT]'), array($data['province']??0,$data['city']??0,$data['district']??0), $form);
                $form = str_replace(array($form_value, '[FIELD_NAME]', '[FORM_NAME]'), array($content, $field, $name), $form);

                break;
            case 'radio_tree_select':
                $form = ('\\generate\\field\\' . parse_name($type, 1, true))::create($type, $name, $field, $content, $option, $type_arr);
                break;
            case 'radio':
            case 'search_list':
            case 'search_multi_list':
            case 'search_list1':
                $form = ('\\generate\\field\\' . parse_name($type, 1, true))::create($type, $name, $field, $content, $option);
                break;
            case 'tag_list':
            case 'color_select':
            case 'element_icon':
            case 'icon_font':
                $form = ('\\generate\\field\\' . parse_name($type, 1, true))::create($type, $name, $field, $content, $type_arr);
                break;
            case 'color':
            case 'icon':
            case 'number':
            case 'number1':
            case 'hidden':
            case 'datetime_range':
            case 'time_range':
            case 'disable_text':
            case 'textarea':
            case 'text':
            case 'time':
            case 'year_month':
            case 'date':
            case 'datetime':
            case 'multi_file':
            case 'multi_image':
                $form = str_replace(array($form_value, '[FIELD_NAME]', '[FORM_NAME]',), array($content, $field, $name,), $form);
                break;
            case 'video':
            case 'file':
//                $search1 = "{if isset(\$data)}{\$data.[FIELD_NAME]}{/if}";
//                $form    = str_replace(array($search1), array($content), $form);
//                $form = str_replace(array($form_value, '[FIELD_NAME]', '[FORM_NAME]',), array($content, $field, $name,), $form);
//                dd($name,$content,$field,$type, $data);
                $form = ('\\generate\\field\\' . parse_name($type, 1, true))::create($name,$content,$field,$type, $data);
                break;
            case 'text_list':
                //默认值
                $text_html = '<div><input id="[FIELD_NAME]" name="[FIELD_NAME][]" placeholder="请输入[FORM_NAME]" type="text" class="form-control fieldText"><a class="btn btn-default" id="[FIELD_NAME]_add">新增</a></div>';
                $init = 0;
                if(is_array($content)) foreach ($content as $text) {
                    if($init == 0) {
                        $text_html = '<div><input id="[FIELD_NAME]" name="[FIELD_NAME][]" value="'.$text.'" placeholder="请输入[FORM_NAME]" type="text" class="form-control fieldText"><a class="btn btn-default" id="[FIELD_NAME]_add">新增</a></div>';
                    } else {
                        $text_html .= '<div><input id="[FIELD_NAME]" name="[FIELD_NAME][]" value="'.$text.'" placeholder="请输入[FORM_NAME]" type="text" class="form-control fieldText"><a class="btn btn-danger" id="[FIELD_NAME]_del">删除</a></div>';
                    }
                    $init += 1;
                }
                $form = str_replace(array('[TEXT_DATA]'), array($text_html), $form);
                $form = str_replace(array('[FIELD_NAME]', '[FORM_NAME]',), array($field, $name,), $form);
                break;
            case 'line':
            case 'title':
                $form = str_replace(array('[FORM_NAME]'), $name, $form);
                break;
            case 'tags_input':

                $form = ('\\generate\\field\\' . parse_name($type, 1, true))::create($type, $name, $field, $content);
                break;
            case 'project_model':
                $form = ('\\generate\\field\\' . parse_name($type, 1, true))::create($name,$content,$field,$type, $data);
                break;
            default:
//                $form = str_replace(array($form_value, '[FIELD_NAME]', '[FORM_NAME]',), array($content, $field, $name,), $form);
                break;
        }
        return $form;

    }

}