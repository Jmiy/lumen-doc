<?php

namespace App;

use Psr\Log\LoggerInterface;
use SeasLog;

class Logger implements LoggerInterface {

    /**
     * All level.
     */
    const ALL = -2147483647;

    /**
     * Detailed debug information.
     */
    const DEBUG = 100;

    /**
     * Interesting events.
     *
     * Examples: User logs in, SQL logs.
     */
    const INFO = 200;

    /**
     * Uncommon events.
     */
    const NOTICE = 250;

    /**
     * Exceptional occurrences that are not errors.
     *
     * Examples: Use of deprecated APIs, poor use of an API,
     * undesirable things that are not necessarily wrong.
     */
    const WARNING = 300;

    /**
     * Runtime errors.
     */
    const ERROR = 400;

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     */
    const CRITICAL = 500;

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc.
     * This should trigger the SMS alerts and wake you up.
     */
    const ALERT = 550;

    /**
     * Urgent alert.
     */
    const EMERGENCY = 600;

    /** @var SeasLog */
    protected $seaslog;

    /** @var string */
    protected $level = self::ALL;
    protected static $levels = [
        self::DEBUG => 'DEBUG',
        self::INFO => 'INFO',
        self::NOTICE => 'NOTICE',
        self::WARNING => 'WARNING',
        self::ERROR => 'ERROR',
        self::CRITICAL => 'CRITICAL',
        self::ALERT => 'ALERT',
        self::EMERGENCY => 'EMERGENCY',
    ];

    /**
     * Logger constructor.
     * @param SeasLog $seasLog
     */
    public function __construct(SeasLog $seasLog, array $config = []) {
        $this->seaslog = $seasLog;
        if (!empty($config['path'])) {
            $this->seaslog->setBasePath($config['path']);
        }
        if (!empty($config['name'])) {
            $this->seaslog->setLogger($config['name']);
        }
        if (!empty($config['level'])) {
            $this->level = array_search($config['level'], self::$levels);
        }
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function emergency($message, array $context = []) {
        $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function alert($message, array $context = []) {
        $this->log(self::ALERT, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function critical($message, array $context = []) {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function error($message, array $context = []) {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function warning($message, array $context = []) {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function notice($message, array $context = []) {
        $this->log(self::NOTICE, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function info($message, array $context = []) {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function debug($message, array $context = []) {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = []) {
        if ((int) $level < $this->level) {
            return;
        }

        if (!array_key_exists($level, self::$levels)) {
            return;
        }

        $levelFunction = strtolower(self::$levels[$level]);

        $this->seaslog->$levelFunction($message, $context);
    }

    /**
     * 设置本次请求标识.
     *
     * @param string
     *
     * @return bool
     */
    public function setRequestID($request_id) {
        return $this->seaslog->setRequestID($request_id);
    }

    /**
     * 获取本次请求标识.
     *
     * @return string
     */
    public function getRequestID() {
        return $this->seaslog->getRequestID();
    }

    /**
     * 设置模块目录.
     *
     * @param $module
     *
     * @return bool
     */
    public function setLogger($module) {
        return $this->seaslog->setLogger($module);
    }

    /**
     * 获取最后一次设置的模块目录.
     *
     * @return string
     */
    public function getLastLogger() {
        return $this->seaslog->getLastLogger();
    }

    /**
     * 设置DatetimeFormat配置.
     *
     * @param $format
     *
     * @return bool
     */
    public function setDatetimeFormat($format) {
        return $this->seaslog->setDatetimeFormat($format);
    }

    /**
     * 返回当前DatetimeFormat配置格式.
     *
     * @return string
     */
    public function getDatetimeFormat() {
        return $this->seaslog->getDatetimeFormat();
    }

    /**
     * 统计所有类型（或单个类型）行数.
     *
     * @param string $level
     * @param string $log_path
     * @param null $key_word
     *
     * @return array
     */
    public function analyzerCount($level = 'all', $log_path = '*', $key_word = null) {
        return $this->seaslog->analyzerCount($level, $log_path, $key_word);
    }

    /**
     * 以数组形式，快速取出某类型log的各行详情.
     *
     * @param        $level
     * @param string $log_path
     * @param null $key_word
     * @param int $start
     * @param int $limit
     * @param        $order    默认为正序 SEASLOG_DETAIL_ORDER_ASC，可选倒序 SEASLOG_DETAIL_ORDER_DESC
     *
     * @return array
     */
    public function analyzerDetail(
    $level = SEASLOG_INFO, $log_path = '*', $key_word = null, $start = 1, $limit = 20, $order = SEASLOG_DETAIL_ORDER_ASC
    ) {
        return $this->seaslog->analyzerDetail(
                        $level, $log_path, $key_word, $start, $limit, $order
        );
    }

    /**
     * 获得当前日志buffer中的内容.
     *
     * @return array
     */
    public function getBuffer() {
        return $this->seaslog->getBuffer();
    }

    /**
     * 将buffer中的日志立刻刷到硬盘.
     *
     * @return bool
     */
    public function flushBuffer() {
        return $this->seaslog->flushBuffer();
    }

    /**
     * Create a custom SeasLog instance.
     *
     * @param array $config
     *
     * @return Logger
     */
    public function __invoke(array $config) {
        $seaslog = new Seaslog();
        return new Logger($seaslog, $config);
    }

    /**
     * Manually release stream flow from logger
     *
     * @param $type
     * @param string $name
     * @return bool
     */
    public function closeLoggerStream($type = SEASLOG_CLOSE_LOGGER_STREAM_MOD_ALL, $name = '') {
        if (empty($name)) {
            return $this->seaslog->closeLoggerStream($type);
        }

        return $this->seaslog->closeLoggerStream($type, $name);
    }

}
