<?php

namespace Enjine\Utils;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Tools
{

    private static $logger,
                    $logFile;

    public static function initLogger($name="enjine", $path="/var/log/enjine.log"){
        static::$logger = new Logger($name);
        static::$logFile = $path;

        if(empty($path)){
            throw new \ErrorException("Logfile path is empty!");
        }
        
        if( !file_exists($path)){
            mkdir(pathinfo($path)["dirname"], 0777, true);
            touch($path);
        }

        //TODO: allow for separate files per log level;
        $logs = [
            "debug" => new StreamHandler($path, Logger::DEBUG),
            "info" => new StreamHandler($path, Logger::INFO),
            "notice" => new StreamHandler($path, Logger::NOTICE),
            "warn" => new StreamHandler($path, Logger::WARNING),
            "error" => new StreamHandler($path, Logger::ERROR),
            "critical" => new StreamHandler($path, Logger::CRITICAL),
            "alert" => new StreamHandler($path, Logger::ALERT),
            "emergency" => new StreamHandler($path, Logger::EMERGENCY)
        ];

        $formatter = new LineFormatter("%datetime%: %level_name% :: %message% | %context% | %extra%\n", "D M j Y, g:i A");
        foreach ($logs as $log => $handler) {
            $handler->setFormatter($formatter);
            static::$logger->pushHandler($handler);
        }

        return static::$logger;
    }

    private static function getLogger()
    {
        if (static::$logger instanceof Logger) {
            return static::$logger;
        }
        return static::initLogger();
    }

    public static function logArray($data, $message=""){
        static::logInfo($message, $data);
    }

    public static function logInfo($message, $data=[])
    {
        static::getLogger()->addInfo($message, $data);
    }

    public static function logWarn($message, $data=[])
    {
        static::getLogger()->addWarn($message, $data);
    }

    public static function logError($message, $data=[])
    {
        static::getLogger()->addError($message, $data);
    }

    public static function logRaw($data)
    {
        ob_start();
        print_r($data);
        $out = ob_get_contents();
        ob_end_clean();
        file_put_contents(static::$logFile, $out, FILE_APPEND);
    }

    public static function dump($data)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
}
