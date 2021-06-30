<?php
/**
 * @see https://github.com/hhxsv5/laravel-s/blob/master/Settings-CN.md  Chinese
 * @see https://github.com/hhxsv5/laravel-s/blob/master/Settings.md  English
 */
return [
    'listen_ip'                => env('LARAVELS_LISTEN_IP', '0.0.0.0'),//127.0.0.1
    'listen_port'              => env('LARAVELS_LISTEN_PORT', 5200),
    'socket_type'              => defined('SWOOLE_SOCK_TCP') ? SWOOLE_SOCK_TCP : 1,
    'enable_coroutine_runtime' => false,//运行时协程：需Swoole>=4.1.0，同时启用协程 'enable_coroutine'   => true,否则此项配置不生效
    'server'                   => env('LARAVELS_SERVER', 'LaravelS'),
    'handle_static'            => env('LARAVELS_HANDLE_STATIC', false),
    'laravel_base_path'        => env('LARAVEL_BASE_PATH', base_path()),
    'inotify_reload'           => [
        'enable'        => env('LARAVELS_INOTIFY_RELOAD', false),//是否开启Inotify Reload，用于当修改代码后实时Reload所有worker进程，依赖库inotify，通过命令php --ri inotify检查是否可用，默认false，建议仅开发环境开启
        'watch_path'    => base_path(),//监控的文件路径，默认有base_path()
        'file_types'    => ['.php'],//监控的文件类型，默认有.php
        'excluded_dirs' => [],//监控时需要排除(或忽略)的目录，默认[]，示例：[base_path('vendor')]
        'log'           => true,//是否输出Reload的日志，默认true。
    ],
    'event_handlers'           => [// 按数组顺序触发事件
        'ServerStart' => [\App\Events\ServerStartEvent::class], //发生在 Master 进程启动时，此事件中不应处理复杂的业务逻辑，只能做一些初始化的简单工作
        'WorkerStart' => [\App\Events\WorkerStartEvent::class], //发生在 Worker/Task 进程启动完成后
    ],
    'websocket'                => [
        //'enable' => false,
        //'handler' => XxxWebSocketHandler::class,
        'enable'  => true, // 看清楚，这里是true
        'handler' => \App\Services\WebSocketService::class,
    ],
    'sockets'                  => [
//        [
//            'host'     => '127.0.0.1',
//            'port'     => 5291,
//            'type'     => SWOOLE_SOCK_TCP,// 支持的嵌套字类型：https://wiki.swoole.com/#/consts?id=socket-%e7%b1%bb%e5%9e%8b
//            'settings' => [// Swoole可用的配置项：https://wiki.swoole.com/#/server/port?id=%e5%8f%af%e9%80%89%e5%8f%82%e6%95%b0
//                'open_eof_check' => true,
//                'package_eof'    => "\r\n",
//            ],
//            'handler'  => \App\Sockets\TestTcpSocket::class,
//            'enable'   => true, // 是否启用，默认为true
//        ],
//        [
//            'host'     => '0.0.0.0',
//            'port'     => 5292,
//            'type'     => SWOOLE_SOCK_UDP,
//            'settings' => [
//                'open_eof_check' => true,
//                'package_eof'    => "\r\n",
//            ],
//            'handler'  => \App\Sockets\TestUdpSocket::class,
//        ],
//        [
//            'host'     => '0.0.0.0',
//            'port'     => 5293,
//            'type'     => SWOOLE_SOCK_TCP,
//            'settings' => [
//                'open_http_protocol' => true,
//            ],
//            'handler'  => \App\Sockets\TestHttp::class,
//        ],
//        [
//            'host'     => '0.0.0.0',
//            'port'     => 5294,
//            'type'     => SWOOLE_SOCK_TCP,
//            'settings' => [
//                'open_http_protocol'      => true,
//                'open_websocket_protocol' => true,
//            ],
//            'handler'  => \App\Sockets\TestWebSocket::class,
//        ],
    ],
    'processes'                => [
//        [
//            'class'    => \App\Processes\TestProcess::class,
//            'redirect' => false, // Whether redirect stdin/stdout, true or false
//            'pipe'     => 0 // The type of pipeline, 0: no pipeline 1: SOCK_STREAM 2: SOCK_DGRAM
//            'enable'   => true // Whether to enable, default true
//        ],
//        'test' => [ // Key为进程名
//            'class'    => \App\Processes\TestProcess::class,
//            'redirect' => false, // 是否重定向输入输出
//            'pipe'     => 1,     // 管道类型：0不创建管道，1创建SOCK_STREAM类型管道，2创建SOCK_DGRAM类型管道
//            'enable'   => true,  // 是否启用，默认true
//            //'queue'    => [ // 启用消息队列作为进程间通信，配置空数组表示使用默认参数
//            //    'msg_key'  => 0,    // 消息队列的KEY，默认会使用ftok(__FILE__, 1)
//            //    'mode'     => 2,    // 通信模式，默认为2，表示争抢模式
//            //    'capacity' => 8192, // 单个消息长度，长度受限于操作系统内核参数的限制，默认为8192，最大不超过65536
//            //],
//            //'restart_interval' => 5, // 进程异常退出后需等待多少秒再重启，默认5秒
//        ],
        'baseProcess' => [ // Key为进程名
            'class'    => \App\Processes\Hhxsv5\BaseProcess::class,
            'redirect' => false, // 是否重定向输入输出
            'pipe'     => 2,     // unixSocket类型(管道类型)：0:不创建管道，1:创建SOCK_STREAM类型管道(管道类型为STREAM时，read是流式的，需要自行处理包完整性问题)，2:创建SOCK_DGRAM类型管道(管道类型为DGRAM数据报时，read可以读取完整的一个数据包)
            'enable'   => true,  // 在 callback function 中是否启用协程，开启后可以直接在子进程的函数中使用协程 API，默认true
            //'queue'    => [ // 启用消息队列作为进程间通信，配置空数组表示使用默认参数
            //    'msg_key'  => 0,    // 消息队列的KEY，默认会使用ftok(__FILE__, 1)
            //    'mode'     => 2,    // 通信模式，默认为2，表示争抢模式
            //    'capacity' => 8192, // 单个消息长度，长度受限于操作系统内核参数的限制，默认为8192，最大不超过65536
            //],
            //'restart_interval' => 5, // 进程异常退出后需等待多少秒再重启，默认5秒
        ],
    ],// + Hhxsv5\LaravelS\Components\Apollo\Process::getDefinition()
    'timer'                    => [//毫秒级定时任务
        'enable'          => env('LARAVELS_TIMER', false),// 启用Timer
        'jobs'            => [// 注册的定时任务类列表
            // Enable LaravelScheduleJob to run `php artisan schedule:run` every 1 minute, replace Linux Crontab
            //\Hhxsv5\LaravelS\Illuminate\LaravelScheduleJob::class,
            // Two ways to configure parameters:
            // [\App\Jobs\XxxCronJob::class, [1000, true]], // Pass in parameters when registering
            // \App\Jobs\XxxCronJob::class, // Override the corresponding method to return the configuration

            // 启用LaravelScheduleJob来执行`php artisan schedule:run`，每分钟一次，替代Linux Crontab
            // \Hhxsv5\LaravelS\Illuminate\LaravelScheduleJob::class,
            // 两种配置参数的方式：
            // [\App\Jobs\Timer\TestCronJob::class, [1000, true]], // 注册时传入参数
            \App\Jobs\Timer\TestCronJob::class, // 重载对应的方法来返回参数
        ],
        'max_wait_time'   => 5,// Reload时最大等待时间
        // Enable the global lock to ensure that only one instance starts the timer when deploying multiple instances.
        // 打开全局定时器开关：当多实例部署时，确保只有一个实例运行定时任务，此功能依赖 Redis，具体请看 https://learnku.com/docs/laravel/7.x/redis
        'global_lock'     => false,
        'global_lock_key' => config('app.name', 'Laravel'),
    ],
    'swoole_tables'            => [
        // 场景：WebSocket中UserId与FD绑定
        'ws' => [// Key为Table名称，使用时会自动添加Table后缀，避免重名。这里定义名为wsTable的Table
            'size'   => 102400,//Table的最大行数
            'column' => [// Table的列定义
                ['name' => 'value', 'type' => \Swoole\Table::TYPE_INT, 'size' => 8],
            ],
        ],
        //...继续定义其他Table
    ],
    'register_providers'       => [
        //\App\Providers\AppServiceProvider::class,
    ],
    'cleaners'                 => [
        // See LaravelS's built-in cleaners: https://github.com/hhxsv5/laravel-s/blob/master/Settings.md#cleaners

        // 如果你的项目中使用到了Session、Authentication、Passport
        Hhxsv5\LaravelS\Illuminate\Cleaners\SessionCleaner::class,
        Hhxsv5\LaravelS\Illuminate\Cleaners\AuthCleaner::class,
    ],
    'destroy_controllers'      => [
        'enable'        => false,
        'excluded_list' => [
            //\App\Http\Controllers\TestController::class,
        ],
    ],
    'swoole'                   => [//设置 Server 运行时的各项参数 详情：https://wiki.swoole.com/#/server/setting
        'daemonize'          => env('LARAVELS_DAEMONIZE', false),//守护进程化【默认值：false】 https://wiki.swoole.com/#/server/setting?id=daemonize
        'dispatch_mode'      => 2,//数据包分发策略。【默认值：2】 https://wiki.swoole.com/#/server/setting?id=dispatch_mode
        'reactor_num'        => env('LARAVELS_REACTOR_NUM', function_exists('swoole_cpu_num') ? swoole_cpu_num() * 2 : 4),//reactor thread num (reactor:负责维护客户端 TCP 连接、处理网络 IO、处理协议、收发数据 https://wiki.swoole.com/#/learn?id=diff-process)
        'worker_num'         => env('LARAVELS_WORKER_NUM', function_exists('swoole_cpu_num') ? swoole_cpu_num() * 2 : 8),//https://wiki.swoole.com/#/learn?id=diff-process
        'task_worker_num'    => env('LARAVELS_TASK_WORKER_NUM', function_exists('swoole_cpu_num') ? swoole_cpu_num() * 2 : 8),//支持自定义的异步事件
        'task_ipc_mode'      => 1,
        'task_max_request'   => env('LARAVELS_TASK_MAX_REQUEST', 8000),
        'task_tmpdir'        => @is_writable('/dev/shm/') ? '/dev/shm' : '/tmp',
        'max_request'        => env('LARAVELS_MAX_REQUEST', 8000),
        'open_tcp_nodelay'   => true,
        'pid_file'           => storage_path('laravels.pid'),
        'log_file'           => storage_path(sprintf('logs/swoole-%s.log', date('Y-m-d'))),
        'log_level'          => 4,
        'document_root'      => base_path('public'),
        'buffer_output_size' => 2 * 1024 * 1024,
        'socket_buffer_size' => 128 * 1024 * 1024,
        'package_max_length' => 200 * 1024 * 1024,//4 * 1024 * 1024
        'reload_async'       => true,//reload 还要配合这两个参数 max_wait_time 和 reload_async，设置了这两个参数之后就能实现异步安全重启
        'max_wait_time'      => 60,//reload 如果没有此特性，Worker 进程收到重启信号或达到 max_request 时，会立即停止服务，这时 Worker 进程内可能仍然有事件监听，这些异步任务将会被丢弃。设置上述参数后会先创建新的 Worker，旧的 Worker 在完成所有事件之后自行退出，即 reload_async。如果旧的 Worker 一直不退出，底层还增加了一个定时器，在约定的时间 (max_wait_time 秒) 内旧的 Worker 没有退出，底层会强行终止，并会产生一个 WARNING 报错
        'enable_reuse_port'  => true,//设置端口重用。【默认值：false】 启用端口重用后，可以重复启动监听同一个端口的 Server 程序
        'enable_coroutine'   => false,//启用协程，默认是关闭的
        'http_compression'   => false,

        // Slow log
        // 'request_slowlog_timeout' => 2,
        // 'request_slowlog_file'    => storage_path(sprintf('logs/slow-%s.log', date('Y-m'))),
        // 'trace_event_worker'      => true,

        /**
         * More settings of Swoole
         * @see https://wiki.swoole.com/#/server/setting  Chinese
         * @see https://www.swoole.co.uk/docs/modules/swoole-server/configuration  English
         */

        // 表示每60秒遍历一次，一个连接如果600秒内未向服务器发送任何数据，此连接将被强制关闭
      'heartbeat_idle_time'      => 600,
      'heartbeat_check_interval' => 60,
    ],
];
