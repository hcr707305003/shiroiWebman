<?php
/**
 * common模块基础service
 * @author yupoxiong<i@yupoxiong.com>
 */

declare (strict_types=1);


namespace app\common\service;


class CommonBaseService
{

    /**
     * 业务状态
     * @var bool
     */
    protected bool $status = false;
    /**
     * 业务消息文本
     * @var string
     */
    protected string $message = "";
    /**
     * 业务数据
     * @var array
     */
    protected array $data = [];

    /**
     * 设置当前服务值
     * @param bool $status
     * @param string $message
     * @param array $data
     * @return self
     */
    protected function setService(bool $status, string $message, array $data = []): self
    {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;

        return $this;
    }

    /**
     * 验证当前业务状态
     * @return bool
     */
    public function checkStatus(): bool
    {
        return $this->status;
    }

    /**
     * 获取当前业务消息文本
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * 获取当前业务数据
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
