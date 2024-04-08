<?php

namespace app\admin\controller;

use app\common\exception\HttpResponseException;
use app\common\plugin\Filesystem;
use support\Request;
use support\Response;
use Throwable;

/**
 * 文件上传controller
 * @author shiroi <707305003@qq.com>
 */
class FileController extends AdminBaseController
{
    /** @var bool $is_upload_local 是否上传到本地 */
    protected bool $is_upload_local = true;

    /** @var int|float $file_size 文件大小限制默认20m */
    protected int $file_size = 1024 * 1024 * 20;

    /**
     * 表单上传组建使用
     * @param Request $request
     * @return Response
     * @throws HttpResponseException
     */
    public function upload(Request $request): Response
    {
        if ($request->isPost()) {
            $param = $request->all();
            $field = $param['file_field'] ?? 'file';
            // 文件类型，默认图片
            $file_type = $param['file_type'] ?? 'image';
            //文件
            $files = $request->file($field);
            //保存的路径
            $filePath =  "upload/{$file_type}/" . date('Y-m-d');
            //获取文件信息
            $fileInfo = $this->uploadFile($files,$file_type,$field,$filePath);

            return json([
                'code'                 => 200,
                'initialPreview'       => [$fileInfo['url']],
                'initialPreviewAsData' => true,
                'showDownload'         => false,
                'initialPreviewConfig' => [
                    [
                        'downloadUrl' => $fileInfo['url'],
                        'key'         => $fileInfo['alt'],
                        'caption'     => $fileInfo['alt'],
                        'url'         => url('admin/file/del', ['file' => $fileInfo['url']]),
                        'size'        => $fileInfo['size'],
                        'type'        => $fileInfo['type'],
                        'filetype'    => $fileInfo['filetype']
                    ]
                ],
            ]);
        }
        return admin_error('非法访问');
    }

    /**
     * 编辑器上传
     * @param Request $request
     * @return Response
     * @throws HttpResponseException
     */
    public function editor(Request $request): Response
    {

        if ($request->isPost()) {
            $param = request()->all();
            $field = $param['file_field'] ?? 'file';
            // 文件类型，默认图片
            $file_type = $param['file_type'] ?? 'image';
            //文件
            $files = $request->file($field);
            //保存的路径
            $filePath =  "upload/{$file_type}/" . date('Y-m-d');
            return json([
                'errno' => 0,
                'data'  => [
                    $this->uploadFile($files,$file_type,$field,$filePath)
                ]
            ]);
        }
        return json([
            "errno"   => 1,
            'message' => '非法访问'
        ]);
    }

    /**
     * uEditor编辑器上传
     * @param $params
     * @param $files
     * @return array
     * @throws HttpResponseException
     */
    public function uEditor($params,$files): array
    {

        $field = $params['file_field'] ?? 'file';
        // 文件类型，默认图片
        $file_type = $params['file_type'] ?? 'image';
        //保存的路径
        $filePath =  "upload/{$file_type}/" . date('Y-m-d');
        return $this->uploadFile($files,$file_type,$field,$filePath);

    }

    /**
     * @throws HttpResponseException
     */
    public function uploadFile($file, $type, $field, $filePath): array
    {
        $res = Filesystem::instance($this->is_upload_local? 'public': 'oss')
            ->path($filePath)
            ->size($this->file_size)
            ->processUpload($file,function ($image) {
                // 图片大小更改 resize()
//                    $image->resize(100,50);
                // 在图片上增加水印 insert()
//                $image->insert('xxx/watermark.png','bottom-right',15,10);
                // 当然你可以使用intervention/image 中的任何功能 最终都会上传在你的storage库中
                return $image;
            }, true);

        dump($res);
        if(empty($res)) {
            throw new HttpResponseException(admin_error("文件上传失败"));
        }
        return [
            'url'         => $this->is_upload_local? str_replace('//'.request()->host(), '', $res->file_url): $res->file_url,
            'href'        => '',
            'alt'         => $res->file_name,
            'size'        => $res->size,
            'type'        => $type,
            'filetype'    => $res->extension
        ];
    }

    /**
     * 删除文件
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function del(Request $request): Response
    {
        $url = urldecode($request->input('file'));
        if($url = parse_url($url, 5)) Filesystem::delete($url, $this->is_upload_local? 'public': 'oss');
        return admin_success('删除成功');
    }
}
