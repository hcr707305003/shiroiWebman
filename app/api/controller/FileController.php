<?php /** @noinspection DuplicatedCode */

namespace app\api\controller;

use app\common\plugin\Filesystem;
use Exception;
use hg\apidoc\annotation as Apidoc;
use support\Response;

/**
 * @Apidoc\Title("文件上传相关")
 */
class FileController extends ApiBaseController
{

    /** @var bool $is_upload_local 是否上传到本地 */
    protected bool $is_upload_local = true;

    /** @var int|float $file_size 文件大小限制默认20m */
    protected int $file_size = 1024 * 1024 * 20;

    /**
     * 保存图片
     * @Apidoc\Method("post")
     * @Apidoc\ParamType("formdata")
     * @Apidoc\Param("file",type="file",require=true,desc="上传的图片(文件大小限制默认10m)")
     * @Apidoc\Param("name",type="string",desc="自定义字段")
     * @return Response
     */
    public function saveImage(): Response
    {
        $file = request()->file(input('file_field', 'file'));
        if(empty($file)) return api_error('文件未上传');
        try {
            $res = Filesystem::instance($this->is_upload_local? 'public': 'oss')
                ->path($this->getFileUploadPath('image'))
                ->size($this->file_size)
                ->extYes(['image/jpeg', 'image/gif', 'image/png'])
                ->processUpload($file, function ($image) {
                    // 图片大小更改 resize()
//                    $image->resize(100,50);
                    // 在图片上增加水印 insert()
//                $image->insert('xxx/watermark.png','bottom-right',15,10);
                    // 当然你可以使用intervention/image 中的任何功能 最终都会上传在你的storage库中
                    return $image;
                }, true);
        } catch (Exception $exception) {
            return api_error($exception->getMessage());
        }
        return api_success(array_merge(to_array($res),request()->post(['name'])));
    }

    /**
     * 保存文件
     * @Apidoc\Method("post")
     * @Apidoc\ParamType("formdata")
     * @Apidoc\Param("file",type="file",require=true,desc="上传的图片(文件大小限制默认10m)")
     * @Apidoc\Param("name",type="string",desc="自定义字段")
     * @return Response
     */
    public function saveFile(): Response
    {
        $file = request()->file(input('file_field', 'file'));
        if(empty($file)) return api_error('文件未上传');
        try {
            $res = Filesystem::instance($this->is_upload_local? 'public': 'oss')
                ->path($this->getFileUploadPath('file'))
                ->size($this->file_size)
                ->extYes([])
                ->extNo([])
                ->upload($file);
        } catch (Exception $exception) {
            return api_error($exception->getMessage());
        }
        return api_success(array_merge(to_array($res),request()->post(['name'])));
    }

    /**
     * 保存base64图片
     * @Apidoc\Method("post")
     * @Apidoc\ParamType("formdata")
     * @Apidoc\Param("file",type="string",require=true,desc="上传的图片(文件大小限制默认10m)",mock="@dataImage")
     * @Apidoc\Param("name",type="string",desc="自定义字段")
     * @return Response
     */
    public function saveBase64Image(): Response
    {
        try {
            $res = Filesystem::instance($this->is_upload_local? 'public': 'oss')
                ->path($this->getFileUploadPath('image'))
                ->size($this->file_size)
                ->extYes(['image/jpeg', 'image/gif', 'image/png'])
                ->base64Upload(input(input('file_field', 'file')));
        }catch (Exception $exception){
            return api_error($exception->getMessage());
        }
        return api_success(array_merge(to_array($res),request()->post(['name'])));
    }
}