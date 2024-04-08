<?php

namespace support;

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LogHandler extends StreamHandler
{
    protected int $maxFileSize;
    protected bool $mustRotate;
    protected string $runtimeLogPath;
    protected ?string $channelDirName;

    /**
     * @param string $channelDirName 日志通道路径
     * @param int $maxFileSize The maximal file size (default 10MB)
     * @param int $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     * @param int|null $filePermission Optional file permissions (default (0644) are only for owner read/write)
     * @param bool $useLocking Try to lock log file before doing any writes
     *
     * @throws Exception
     */
    public function __construct($channelDirName = null, int $maxFileSize = 10, $level = Logger::DEBUG, bool $bubble = true, int $filePermission = null, bool $useLocking = false)
    {
        $this->runtimeLogPath = runtime_path() . '/logs/';
        $this->channelDirName = $channelDirName;
        $dateDir = date('Ym').'/';
        $filename = date('d') .'.log';
        $fullFilePath = empty($channelDirName) ? $this->runtimeLogPath . $dateDir .$filename : $this->runtimeLogPath . $this->channelDirName . '/' . $dateDir . $filename;
        $this->maxFileSize = (int)($maxFileSize * 1024 * 1024);
        if ($maxFileSize <= 0) {
            throw new Exception('Max file size must be higher than 0');
        }
        parent::__construct($fullFilePath, $level, $bubble, $filePermission, $useLocking);
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        parent::close();
        if ($this->mustRotate) {
            $this->rotate();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        parent::reset();
        if ($this->mustRotate) {
            $this->rotate();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record): void
    {
        $dateDir = date('Ym') . '/';
        $logBasePath = empty($this->channelDirName) ? $this->runtimeLogPath . $dateDir : $this->runtimeLogPath . $this->channelDirName . '/' . $dateDir;
        $fullLogFilename = $logBasePath . date('d').'.log';
        clearstatcache(true, $fullLogFilename);
        if (file_exists($fullLogFilename)) {
            $fileSize = filesize($fullLogFilename);
            if ($fileSize >= $this->maxFileSize) {
                $this->mustRotate = true;
                $this->close();
            }else{
                $this->stream = null;
                $this->url = $fullLogFilename;
            }
        }else{
            // 解决WebMan启动后删除日志文件无法写入的问题
            $this->mustRotate = true;
            $this->close();
        }
        parent::write($record);
    }

    /**
     * Rotates the files.
     */
    protected function rotate()
    {
        // skip GC of old logs if file size is unlimited
        if ($this->maxFileSize === 0) {
            return;
        }
        $dateDir = date('Ym') . '/';
        $logBasePath = empty($this->channelDirName) ? $this->runtimeLogPath . $dateDir : $this->runtimeLogPath . $this->channelDirName . '/' . $dateDir;
        $filename = date('d').'.log';
        $fullLogFilename = $logBasePath . $filename;
        $fileCount = count(glob($logBasePath . date('d').'_*.log')) + count(glob($fullLogFilename));
        // archive latest file
        clearstatcache(true, $fullLogFilename);
        if (file_exists($fullLogFilename)) {
            $target = $logBasePath.  date('d') . '_' . $fileCount .'.log';
            rename($fullLogFilename, $target);
        }else{
            if (!is_dir($logBasePath))
            {
                mkdir($logBasePath,0755,true);
            }
            $this->url = $fullLogFilename;
        }
        $this->mustRotate = false;
    }
}