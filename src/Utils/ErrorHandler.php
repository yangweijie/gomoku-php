<?php

declare(strict_types=1);

namespace phpgo\Utils;

use Throwable;

/**
 * 错误处理类
 * 
 * 提供统一的错误处理和日志记录功能。
 */
class ErrorHandler
{
    /**
     * 处理异常并显示错误信息
     *
     * @param Throwable $exception 异常对象
     * @param bool $exit 是否退出程序
     * @return void
     */
    public static function handleException(Throwable $exception, bool $exit = true): void
    {
        $errorMessage = "Error: " . $exception->getMessage() . "\n";
        $errorMessage .= "File: " . $exception->getFile() . "\n";
        $errorMessage .= "Line: " . $exception->getLine() . "\n";
        $errorMessage .= "Stack trace:\n" . $exception->getTraceAsString() . "\n";

        // 记录错误日志
        error_log($errorMessage, 3, self::getLogFilePath());

        // 显示错误信息到标准错误输出
        fwrite(STDERR, $errorMessage);

        if ($exit) {
            exit(1);
        }
    }

    /**
     * 获取日志文件路径
     *
     * @return string 日志文件路径
     */
    protected static function getLogFilePath(): string
    {
        // 尝试在系统临时目录创建日志文件
        $tempDir = sys_get_temp_dir();
        $logFile = $tempDir . '/phpgo_error.log';

        // 如果临时目录不可写，使用当前目录
        if (!is_writable($tempDir)) {
            $logFile = dirname(__DIR__, 2) . '/phpgo_error.log';
        }

        return $logFile;
    }
}