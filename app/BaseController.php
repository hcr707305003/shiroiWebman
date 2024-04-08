<?php

namespace app;

use Exception;
use support\Response;
use think\Model;

class BaseController
{
    /** @var int 当前页数 */
    protected int $page;
    /** @var int 当前每页数量 */
    protected int $limit;
    /** @var string $url 当前url */
    protected string $url = '';
    /** @var string $path 当前路径 */
    protected string $path = '';
    /** @var int|string $id 当前的id */
    protected $id = 0;
    /** @var string $action 当前方法 */
    protected string $action;
    /** @var string $controller 当前控制器 */
    protected string $controller;
    /** @var array 当前请求参数 */
    protected array $param = [];

    public function __construct()
    {
        $this->param = (array)request()->all();
        //当前页数
        $this->page = input('page', 1);
        //当前条数
        $this->limit = input('limit', 10);
        //当前请求的url
        $this->url = ltrim(request()->path(), '/');
        //当前路径
        $pathArr = explode('/', $this->url);
        $pathArr[2] = request()->getAction(); //转换为真实方法
        $this->path = implode('/', $pathArr);
        //当前请求id
        $this->id = request()->all()['id'] ?? 0;
        //当前请求的方法
        $this->action = request()->getAction();
        //当前请求的控制器
        $this->controller = request()->getController();
    }

    /**
     * 导入数据
     * @param string|Model $table
     * @param array $fields
     * @param string $columnType
     * @return Response
     * @throws Exception
     */
    public function importData($table, array $fields = [], string $columnType = 'field'): Response
    {
        $file = request()->file('file');
        if (!$file || !$file->isValid()) {
            return $this->error('上传文件校验失败！');
        }

        if (!in_array($file->getUploadExtension(), ['xls', 'xlsx'])) {
            return $this->error('仅支持xls xlsx文件格式！');
        }

        return $this->success(import($table, $file->getRealPath(), $fields, $columnType));
    }

    /**
     * 导出数据
     * @param $tableName
     * @param array $showColumn
     * @param array $where
     * @return Response
     * @throws Exception
     */
    public function exportData($tableName, array $showColumn = [], array $where = []): Response
    {
        return $this->success(str_replace('\\', '/', str_replace(public_path(), '', export($tableName, $showColumn, $where, $this->page, $this->limit, false, $this->getFileUploadPath('file')))));
    }

    /**
     * 导出二进制文件(直接下载文件不保存)
     * @param $tableName
     * @param array $showColumn
     * @param array $where
     * @return Response
     * @throws Exception
     */
    public function exportDataToBinary($tableName, array $showColumn = [], array $where = []): Response
    {
        return response(export($tableName, $showColumn, $where, $this->page, $this->limit, true), 200, [
            'Content-Disposition' => 'attachment; filename="text.xlsx"',
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    /**
     * 获取文件上传地址
     * @param $type
     * @return string
     */
    protected function getFileUploadPath($type): string
    {
        return "upload/{$type}/" . date('Y-m-d');
    }

    /**
     * 未认证（未登录）
     * @param string|array $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @return Response
     */
    public function unauthorized($msg = 'unauthorized', $data = [], int $code = 401, array $header = []): Response
    {
        return $this->result($msg, $data, $code, $header);
    }

    /**
     * 无权限
     * @param string|array $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @return Response
     */
    public function forbidden($msg = 'forbidden', $data = [], int $code = 403, array $header = []): Response
    {
        return $this->result($msg, $data, $code, $header);
    }

    /**
     * 成功返回
     * @param mixed $data
     * @param string|array $msg
     * @param int $code
     * @param array $header
     * @return Response
     */
    public function success($data = [], $msg = 'success', int $code = 200, array $header = []): Response
    {
        return $this->result($msg, $data, $code, $header);
    }

    /**
     * 错误返回
     * @param string|array $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @return Response
     */
    public function error($msg = 'fail', $data = [], int $code = 500, array $header = []): Response
    {
        return $this->result($msg, $data, $code, $header);
    }

    /**
     * 系统维护中
     * @param string|array $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @return Response
     */
    public function service_unavailable($msg = 'service unavailable', $data = [], int $code = 503, array $header = []): Response
    {
        return $this->result($msg, $data, $code, $header);
    }

    /**
     * 客户端错误 例如提交表单的时候验证不通过，是因为客户填写端错误引起的
     * @param string|array $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @return Response
     */
    public function error_client($msg = 'client error', $data = [], int $code = 400, array $header = []): Response
    {
        return $this->result($msg, $data, $code, $header);
    }

    /**
     * 服务端错误
     * @param string|array $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @return Response
     */
    public function error_server($msg = 'server error', $data = [], int $code = 500, array $header = []): Response
    {
        return $this->result($msg, $data, $code, $header);
    }

    /**
     * 资源或接口不存在
     * @param string|array $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @return Response
     */
    public function error_404($msg = '404 not found', $data = [], int $code = 404, array $header = []): Response
    {
        return $this->result($msg, $data, $code, $header);
    }

    /**
     * 默认response
     * @param string|array $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @return Response
     */
    public function result($msg = 'result', $data = [], int $code = 200, array $header = []): Response
    {
        // http code是否同步业务code
        $http_code = server_config('response.http_code_sync') ? $code : 200;

        return response_result([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ], $http_code, $header);
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int|string $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     * @param bool $isSimple
     * @return string
     */
    public function getController(bool $isSimple = false): string
    {
        return $isSimple ? remove_both_str($this->controller,'Controller',2): $this->controller;
    }

    /**
     * @param string $controller
     */
    public function setController(string $controller): void
    {
        $this->controller = $controller;
    }
}