<?php

namespace Enjine\Utils;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Tools
{
    private static $LOG_LEVELS = [
        "debug" => Logger::DEBUG,
        "info" => Logger::INFO,
        "notice" => Logger::NOTICE,
        "warn" => Logger::WARNING,
        "error" => Logger::ERROR,
        "critical" => Logger::CRITICAL,
        "alert" => Logger::ALERT,
        "emergency" => Logger::EMERGENCY
    ];

    private static $logger,
                    $logsDir;

    public static function initLogger($name="enjine", $dir="/var/log"){
        if(!is_dir($dir)){
            throw new \ErrorException("Logs path does not exist!");
        }

        static::$logger = new Logger($name);
        static::$logsDir = $dir;

        $handlers = [];
        $formatter = new LineFormatter("%datetime%: %level_name% :: %message% | %context% | %extra%\n", "D M j Y, g:i A");

        foreach(static::$LOG_LEVELS as $label => $level){
            $logFile = static::$logsDir . DIRECTORY_SEPARATOR . "{$label}.log";
            $handlers[$label] = new StreamHandler($logFile, $level);
            $handlers[$label]->setFormatter($formatter);
            static::$logger->pushHandler($handlers[$label]);

            if(!file_exists($logFile)){
                $d = pathinfo($logFile)["dirname"];
                if(!is_dir($d)){
                    mkdir($d, 0664, true);
                }
                touch($logFile);
            }
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

    public static function getLogsDir()
    {
        return static::$logsDir;
    }

    public static function logArray($data, $message=""){
        static::logInfo($message, $data);
    }

    public static function logDebug($message="", $data=[])
    {
        static::getLogger()->addDebug($message, $data);
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

    public static function HMACVerify($test, $string="", $secret="", $algo="sha256"){
        if(strtolower(hash_hmac($algo, $string, $secret)) === strtolower($test)){
            return true;
        };
        throw new \ErrorException("Unable to verify HMAC hash! {$test} != {$string} ~ {$secret}");
    }
}
