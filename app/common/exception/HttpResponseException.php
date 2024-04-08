<?php

namespace app\common\exception;

use support\exception\BusinessException;
use Webman\Http\Request;
use Webman\Http\Response;

class HttpResponseException extends BusinessException
{
    protected $message;

    protected $data;

    protected Response $response;

    public function __construct(Response $response)
    {
        parent::__construct();
        $data = to_array($response->rawBody());
        $this->message = $data['msg'] ?? $data['message'] ?? '';
        $this->code = $data['code'] ?? 500;
        $this->data = $data['data'] ?? [];
        $this->response = $response;
    }

    public function render(Request $request): ?Response
    {
        return $this->response;
    }

    /**
     * @return array|mixed
     */
    public function getData()
    {
        return $this->data;
    }
}