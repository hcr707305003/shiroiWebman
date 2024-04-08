<?php

namespace generate\field;

class SonList
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
    .bigimg {
        min-width: 600px;
        max-height: 800px;
        height: auto !important;
        position: fixed;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        margin: auto;
        display: none;
        z-index: 9999;
        border: 10px solid #fff;
        object-fit: contain;
    }

    .mask {
        position: fixed;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        background-color: #000;
        opacity: 0.5;
        filter: Alpha(opacity=50);
        z-index: 1098;
        transition: all 1s;
        display: none
    }

    .mask:hover {
        cursor: zoom-out
    }

    .mask > img {
        position: fixed;
        right: 10px;
        top: 10px;
        width: 60px;
    }

    .mask > img:hover {
        cursor: pointer
    }
    .import-btn{ 
        padding: 0;
        font-size: .875rem;
        border-radius: 0.2rem;
        color: #fff;
        height: 30px;
        background-color: red;
        text-align: center;
        position: relative;
        width: 65px;
        cursor: pointer;
        margin-left: 5px;
    }
    .AjaxButton{
    margin:0 5px;
    }
</style>
<div class="form-group row [FIELD_NAME]-form-group">
    <label class="col-form-label row">[TITLE][COUNT][ADD][EXPORT][IMPORT]</label>
    <div class="table-responsive formInputDiv" id="[FIELD_NAME]">
        <table class="table table-hover table-bordered dataTable text-nowrap" field="[TABLE]">
            <thead>
                <tr>
                  [FIELD_ARRAY]
                  [HANDLE_INFO]
                </tr>
            </thead>
            <tbody>
              [VALUE_ARRAY]
            </tbody>
        </table>
    </div>
</div>
<img src="" alt="" mode="widthFix" class="bigimg">
<div class="mask">
</div>
<script>
    $(function (){
         var obj = new zoom('mask', 'bigimg','smallimg');
            obj.init()
            
        $("[data-toggle='popover']").popover({
            delay: { show: 500, hide: 100 }
        });

        $("td").hover(function () {
            if (this.offsetWidth < this.scrollWidth) {
                var that = this;
                var text = $(this).text();
                layer.tips(text, that,{
                    tips: [4, '#428bca'],
                    area: ['auto', 'auto'],
                    time:0
                });
            }
        },function () {
            layer.closeAll('tips');
        });
    });
</script>
\n
EOF;

    public static string $handle_info = <<<EOF
    <th id="[FIELD_NAME]_option">操作</th>
EOF;

    public static string $detail_info = <<<EOF
<a data-id="[ID]" data-url="[INFO_URL]" data-confirm="2" data-type="2"
                                   class="btn btn-default btn-xs AjaxButton" data-title="详情" title="查看详情"
                                   data-toggle="tooltip">
                                    <i class="fas fa-info-circle"></i>
                                    [INFO_CONTENT]
                                </a>
EOF;

    public static string $add_info = <<<EOF
<!--<a title="添加新数据" data-toggle="tooltip" class="btn btn-primary btn-sm " href="[ADD_URL]">-->
<!--                    <i class="fa fa-plus"></i>[ADD_CONTENT]-->
<!--                </a>-->
<a data-url="[ADD_URL]" data-width="30%" data-height="100%" data-offset="r" data-confirm="2" data-type="2" data-title="[ADD_TITLE]" title="新增" data-toggle="tooltip" class="btn btn-primary btn-sm AjaxButton" href="[ADD_URL]">
                    <i class="fa fa-plus"></i>[ADD_CONTENT]
                </a>
EOF;


    public static string $edit_info = <<<EOF
<!--<a href="[EDIT_URL]"-->
<!--                                   class="btn btn-primary btn-xs" title="修改" data-toggle="tooltip">-->
<!--                                    <i class="fas fa-pen"></i>-->
<!--                                    [EDIT_CONTENT]-->
<!--                                </a>-->
<a data-url="[EDIT_URL]" data-width="30%" data-height="100%" data-offset="r" data-confirm="2" data-type="2"
   class="btn btn-primary btn-xs AjaxButton" data-title="修改" title="修改" data-toggle="tooltip">
    <i class="fas fa-pen"></i>
    [EDIT_CONTENT]
</a>
EOF;

    public static string $del_info = <<<EOF
<button class="btn btn-danger btn-xs AjaxButton" data-toggle="tooltip" title="删除"  data-id="[ID]" data-confirm-title="删除确认" data-confirm-content='您确定要删除ID为 <span class="text-red">[ID]</span> 的数据吗' data-url="[DEL_URL]">
                                    <i class="fas fa-trash"></i>
                                    [DELETE_CONTENT]
                                </button>
EOF;

    public static string $default_image = "/static/admin/images/default.png";

    public static string $image = <<<EOF

EOF;

    public static function parseUrl($url)
    {
        //解析url参数
        parse_str(parse_url($url)['query'] ?? '', $param_arr);
        //拼接默认参数
        $param_arr['id'] = input('id');
        //返回url
        return parse_url($url)['path'] . '?' . http_build_query($param_arr);
    }


    public static function create($fields, $data, $type_arr, $option, $data_key, $allData): string
    {
        $field_str = "";
        $value_str = "";
        $title = "";
        $add_title = "";
        $info_url = "";
        $count = false;
        $info_show = false;
        $add_url = "";
        $add_show = false;
        $edit_url = "";
        $edit_show = false;
        $del_url = "";
        $del_show = false;
        $export_show = false;
        $export_url = "";
        $import_show = false;
        $value_arr = [];
        $info_content = "详情";
        $add_content = "新增";
        $edit_content = "修改";
        $del_content = "删除";


        if (isset($fields['{{title}}'])) {
            $title = $fields['{{title}}'] ?? '标题';
            unset($fields['{{title}}']);
        }

        if (isset($fields['{{add_title}}'])) {

            $add_title = $fields['{{add_title}}'] ?? '新增';

            unset($fields['{{add_title}}']);
        }

        if (isset($fields['{{count}}'])) {
            $count = $fields['{{count}}'];
            unset($fields['{{count}}']);
        }

        if (isset($fields['{{info_url}}'])) {
            $info_url = $fields['{{info_url}}'];
            unset($fields['{{info_url}}']);
            //解析url参数
            $info_url = self::parseUrl($info_url);
        }
        if (isset($fields['{{info_show}}'])) {
            $info_show = $fields['{{info_show}}'];
            unset($fields['{{info_show}}']);
        }
        if (isset($fields['{{info_content}}'])) {
            $info_content = $fields['{{info_content}}'];
            unset($fields['{{info_content}}']);
        }

        if (isset($fields['{{add_url}}'])) {
            $add_url = $fields['{{add_url}}'];
            unset($fields['{{add_url}}']);
            //解析url参数
            $add_url = self::parseUrl($add_url);
        }
        if (isset($fields['{{export_url}}'])) {
            $export_url = $fields['{{export_url}}'];
            unset($fields['{{export_url}}']);
            //解析url参数
            $export_url = self::parseUrl($export_url);
        }
        if (isset($fields['{{export_show}}'])) {
            $export_show = $fields['{{export_show}}'];
            unset($fields['{{export_show}}']);
        }
        if (isset($fields['{{import_show}}'])) {
            $import_show = $fields['{{import_show}}'];
            unset($fields['{{import_show}}']);
        }
        if (isset($fields['{{add_show}}'])) {
            $add_show = $fields['{{add_show}}'];
            unset($fields['{{add_show}}']);
        }
        if (isset($fields['{{add_content}}'])) {
            $add_content = $fields['{{add_content}}'];
            unset($fields['{{add_content}}']);
        }


        if (isset($fields['{{edit_url}}'])) {
            $edit_url = $fields['{{edit_url}}'];
            unset($fields['{{edit_url}}']);
            //解析url参数
            parse_str(mb_substr($edit_url, stripos($edit_url, "?") + 1), $edit_param);
            if (array_filter($edit_param)) {
                $edit_url .= "&id=[ID]";
            } else {
                $edit_url .= "?id=[ID]";
            }
        }
        if (isset($fields['{{edit_show}}'])) {
            $edit_show = $fields['{{edit_show}}'];
            unset($fields['{{edit_show}}']);
        }
        if (isset($fields['{{edit_content}}'])) {
            $edit_content = $fields['{{edit_content}}'];
            unset($fields['{{edit_content}}']);
        }

        if (isset($fields['{{del_url}}'])) {
            $del_url = $fields['{{del_url}}'];
            unset($fields['{{del_url}}']);
            //解析url参数
            $del_url = self::parseUrl($del_url);
        }
        if (isset($fields['{{del_show}}'])) {
            $del_show = $fields['{{del_show}}'];
            unset($fields['{{del_show}}']);
        }
        if (isset($fields['{{del_content}}'])) {
            $del_content = $fields['{{del_content}}'];
            unset($fields['{{del_content}}']);
        }

        foreach ($fields as $key => $field) {
            $field_str .= "<th>{$field}</th>";
            $value_arr[$key] = '';
        }
        $tr_value_str = "";

        $count_str = "";
        $data = array_filter((array)$data);
        if ($count) {
            $len = count($data);
            $count_str = "(共有" . ((($len % 100) == 0) ? "{$len}+" : $len) . "条数据)";
        }

        foreach ($data as $son) {
            $diff = array_intersect_key($son, $value_arr);
            $replace_array = array_replace_recursive($value_arr, $diff);
            foreach ($replace_array as $k => $v) {
                $type = $type_arr[$k] ?? 'text';
                if ($type == 'image') {
                    $image = $v ?: self::$default_image;
                    $tr_value_str .= "<td field='{$k}'><img width='50%' class='img-responsive center-block smallimg' src='{$image}'></td>";
                } elseif ($type == 'switch' || $type == 'select') {
                    $v = $option[$k][$v] ?? $v;
                    $tr_value_str .= "<td data-container='body' data-toggle='popover' field='{$k}' data-content='{$v}'>{$v}</td>";
                } else {
                    $v = $k == 'is_alone' ? ($v == 0 ? '否' : '是') : $v;
                    $tr_value_str .= "<td data-container='body' data-toggle='popover' field='{$k}' data-content='{$v}'>{$v}</td>";

                }
            }
            $detail = $info_show ? self::$detail_info : '';
            $edit = $edit_show ? self::$edit_info : '';
            $del = $del_show ? self::$del_info : '';
            if (($info_show + $edit_show + $del_show)) {
                $tr_value_str .= '<td>' . $detail . $edit . $del . '</td>';
                $tr_value_str = str_replace(["[INFO_URL]", "[EDIT_URL]", "[DEL_URL]"], [$info_url, $edit_url, $del_url], $tr_value_str);
                $tr_value_str = str_replace(["[ID]"], [$son['id']], $tr_value_str);
            }
            $value_str .= "<tr>{$tr_value_str}</tr>";
            $tr_value_str = "";
        }
        // 导出
        $export = '<a href="[EXPORT_URL]">
                   <button id="[FIELD_NAME]_export"  class="btn btn-sm btn-info exportData" type="button">
                    <i class="fas fa-file-export"></i> 导出</button></a>';
        // 导入
        $import = '<div class="import-btn" >
                     <label style="margin-top: 5px"> <i class="fas fa-file-upload"></i> 导入</label>
                     <input type="file" id="[FIELD_NAME]_import"  class="form-control-file"  accept=".xlsx" style="position: absolute;top: 0;opacity: 0">
                     </div>';

        $form = str_replace(
            [
                "[TITLE]",
                "[FIELD_ARRAY]",
                "[VALUE_ARRAY]",
                "[ADD]",
                "[EXPORT]",
                "[IMPORT]",
                "[HANDLE_INFO]",
                "[COUNT]",
                "[INFO_CONTENT]",
                "[ADD_CONTENT]",
                "[EDIT_CONTENT]",
                "[DELETE_CONTENT]",
                "[TABLE]"
            ],
            [
                $title,
                $field_str,
                $value_str,
                ($add_show ? self::$add_info : ''),
                ($export_show ? $export : ''),
                ($import_show ? $import : ''),
                (!($info_show + $edit_show + $del_show) ? '' : self::$handle_info),
                $count_str,
                $info_content,
                $add_content,
                $edit_content,
                $del_content,
                $data_key
            ],
            self::$html
        );


        $form = str_replace(["[ADD_URL]", "[ADD_TITLE]", "[EXPORT_URL]"], [$add_url, $add_title, $export_url], $form);
//
        return $form;
    }
}