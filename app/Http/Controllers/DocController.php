<?php

namespace App\Http\Controllers;

use App\Events\InviteEvent;
use App\Models\Auths\User as AdminUser;
use App\Models\Erp\Amazon\ErpBusGiftCardApply;
use App\Services\Activity\Factory;
use App\Services\ActivityProductService;
use App\Services\ActivityStatService;
use App\Services\BaseService;
use App\Services\CreditService;
use App\Services\DictStoreService;
use App\Services\ExpService;
use App\Services\GameService;
use App\Services\OrderReviewService;
use App\Services\OrderWarrantyService;
use App\Services\RewardService;
use App\Services\StoreService;
use App\Services\UniqueIdService;
use App\Util\Cdn\CdnManager;
use App\Util\Cdn\ResourcesCdn;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
//Psr
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use App\Services\EmailService;
use App\Services\OrderService;
use App\Util\Response;
use Carbon\Carbon;
use App\Jobs\OrderBindJob;
use App\Util\FunctionHelper;
use Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Wujunze\DingTalkException\DingTalkExceptionHelper;
use App\Util\Constant;
use App\Services\ActivityService;
use App\Http\Controllers\Api\Controller;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use App\Services\Store\PlatformServiceManager;
use Illuminate\Support\Facades\Cookie;
use Swoole\Coroutine;

//rpc
use Hyperf\Jet\DataFormatter\DataFormatter;
use Hyperf\Jet\Packer\JsonEofPacker;
use Hyperf\Jet\PathGenerator\PathGenerator;
use Hyperf\Jet\ProtocolManager;
use Hyperf\Jet\Transporter\StreamSocketTransporter;
use Hyperf\Jet\Transporter\GuzzleHttpTransporter;
use Hyperf\Jet\Transporter\ConsulTransporter;
use Hyperf\Jet\NodeSelector\NodeSelector;

use Hyperf\Jet\ServiceManager;
use Hyperf\Jet\ClientFactory;



class DocController extends Controller {

    /**
     * 测试
     * @var type
     */
    public $aa;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
//    public function __construct(Request $request) {
//        //
//        //$this->middleware('auth');
////        $this->middleware('log', ['only' => [
////            'fooAction',
////            'barAction',
////        ]]);
////
////        $this->middleware('subscribed', ['except' => [
////            'fooAction',
////            'barAction',
////        ]]);
//    }
    //
    public function index(Request $request) {//ServerRequestInterface $request
        var_dump($request->fullUrl());
        $request->route('comment');

        \Illuminate\Support\Facades\DB::enableQueryLog();
        $value = \App\Models\Customer::withTrashed()->select('*')->where('customer_id', 901)->first()->toArray();
        var_dump(\Illuminate\Support\Facades\DB::getQueryLog());
        exit;

        return response()->json(['test' => 2222]);
        //phpinfo();exit;

        /*         * ******************Redis start https://learnku.com/docs/laravel/5.8/redis/3930#configuration *************** */
//        var_dump(Redis::set('name', 'Taylor'));
//        var_dump(Redis::get('name'));
//        $redis = Redis::connection('cache');
//        var_dump($redis);
//        var_dump($redis->set('name', 'Taylor'));
//        var_dump($redis->get('name'));
//        exit;

        /*         * ******************响应 start *************** */
//        基本响应
//        当然，所有的路由及控制器必须返回某个类型的响应，并发送回用户的浏览器。Laravel 提供了几种不同的方法来返回响应。最基本的响应就是从路由或控制器简单的返回一个字符串：
//        $router->get('/', function () {
//            return 'Hello World';
//        });
//        指定的字符串会被框架自动转换成 HTTP 响应。
        //响应对象 https://learnku.com/docs/lumen/5.7/responses/2408
//        但是，对于大多数路由和控制器行为操作，你将返回完整的 Illuminate\Http\Response 实例。 返回完整的 Response 实例允许你自定义响应的 HTTP 状态码和标题。 一个 Response 实例继承自 Symfony\Component\HttpFoundation\Response 类，并且提供了多种构建 HTTP 响应的方法：
//        use Illuminate\Http\Response;
//        $router->get('home', function () {
//            return (new Response($content, $status))
//                          ->header('Content-Type', $value);
//        });
//        为了方便起见，你可以使用 response 辅助函数：
//
//        $router->get('home', function () {
//            return response($content, $status)
//                          ->header('Content-Type', $value);
//        });
//        注意: 有关 Response 方法的完整列表可以参照 API 文档 以及 Symfony API 文档。
//
//
//        附加标头至响应
//        大部份的响应方法是可链式调用的，这让你可以顺畅的创建响应。举例来说，你可以在响应发送给用户之前，使用 header 方法增加一系列的标头至响应：
//        return response($content)
//                        ->header('Content-Type', $type)
//                        ->header('X-Header-One', 'Header Value')
//                        ->header('X-Header-Two', 'Header Value');
//
//        或者你可以使用 withHeaders 方法来设置数组标头：
//        return response($content)
//                        ->withHeaders([
//                            'Content-Type' => $type,
//                            'X-Header-One' => 'Header Value',
//                            'X-Header-Two' => 'Header Value',
//        ]);
//
//        其它响应类型
//        使用辅助函数 response 可以轻松的生成其它类型的响应实例。当你调用辅助函数 response 且不带任何参数时，将会返回 Laravel\Lumen\Http\ResponseFactory contract 的实现。此 Contract 提供了一些有用的方法来生成响应。
//        JSON 响应
//        json 方法会自动将标头的 Content-Type 设置为 application/json，并通过 PHP 的 json_encode 函数将指定的数组转换为 JSON：
//        return response()->json(['name' => 'Abigail', 'state' => 'CA']);
//
//        你可以选择提供一个状态码和一个额外的标题数组：
//        return response()->json(['error' => 'Unauthorized'], 401, ['X-Header-One' => 'Header Value']);
//
//        如果你想创建一个 JSONP 响应，则可以使用 json 方法并加上 setCallback 方法：
//        return response()
//                        ->json(['name' => 'Abigail', 'state' => 'CA'])
//                        ->setCallback($request->input('callback'));
//
//        文件下载
//        download 方法可以用于生成强制让用户的浏览器下载指定路径文件的响应。download 方法接受文件名称作为方法的第二个参数，此名称为用户下载文件时看见的文件名称。最后，你可以传递一个 HTTP 标头的数组作为第三个参数传入该方法：
//        return response()->download($pathToFile);
//        return response()->download($pathToFile, $name, $headers);
//
//        #重定向
//        重定向响应是类 Illuminate\Http\RedirectResponse 的实例，并且包含用户要重定向至另一个 URL 所需的正确标头。有几种方法可以生成 RedirectResponse 的实例。最简单的方法就是通过全局的 redirect 辅助函数：
//        $router->get('dashboard', function () {
//            return redirect('home/dashboard');
//        });
//
//        重定向至命名路由
//        当你调用 redirect 辅助函数且不带任何参数时，将会返回 Laravel\Lumen\Http\Redirector 的实例，你可以对该 Redirector 的实例调用任何方法。举个例子，要生成一个 RedirectResponse 到一个命名路由，你可以使用 route 方法：
//        return redirect()->route('login');
//
//        如果你的路由有参数，则可以将参数放进 route 方法的第二个参数，如下：
//        // For a route with the following URI: profile/{id}
//        return redirect()->route('profile', ['id' => 1]);
//
//        如果你要重定向至路由且路由的参数为 Eloquent 模型的「ID」，则可以直接将模型传入， ID 将会自动被提取：
//        return redirect()->route('profile', [$user]);



        /*         * ******************响应 end   *************** */

        /*         * ******************请求 start *************** */
        //
        //PSR-7 请求
        //PSR-7 标准规定了 HTTP 消息接口包含了请求及响应，如果你想获得 PSR-7 的请求实例，就需要先安装几个库，Laravel 使用 Symfony 的 HTTP 消息桥接组件，将原 Laravel 的请求及响应转换至 PSR-7 所支持的实现：
        //composer require symfony/psr-http-message-bridge
        //composer require zendframework/zend-diactoros
        //安装完这些库后，你就可以在路由或控制器中，简单的对请求类型使用类型提示来获取 PSR-7 请求：
        //use Psr\Http\Message\ServerRequestInterface;
        //$router->get('/', function (ServerRequestInterface $request) {
        //
        //});
        //如果你从路由或控制器返回了一个 PSR-7 的响应实例，那么它会被框架自动转换回 Laravel 的响应实例并显示。

        var_dump($request->getServerParams());
        exit;


        //获取请求的方法
        $method = $request->method();

        var_dump($method, $request->isMethod('post'));

        $name = $request->input('name');

        var_dump($request->name);

        //设置请求参数
        $request->offsetSet('customer_id', 1);
        $request->offsetSet('code', 'code');
        $request->offsetSet('store_id', 'store_id');

        $customerId = $request->input('customer_id', '');
        $storeId = $request->input('store_id', 0);
        var_dump($customerId, $storeId);

        //获取路由参数
        $request->route('comment'); //获取路由上定义的 URI 参数 如：$route->post('comment/{comment}');
        $request->comment;
        exit;

        //当给定一个数组时， has 方法将确定是否所有指定值都存在：
        //var_dump($name,$request->has('name'),$request->has(['name', 'email']));
        //
        //如果你想确定请求中是否存在值并且不为空，可以使用 filled 方法:
        //var_dump($request->filled(['name', 'email']));
        //
        //获取所有输入数据
        //你可以使用 all 方法以 数组 形式获取所有的输入数据：
        //$input = $request->all();
        //var_dump($input);
        //
        //获取部分输入数据
        //如果你想获取数据的子集， 你可以使用 only 和 except 方法，这两个方法都接受单个 数组 或动态列表作为参数：
//        $input = $request->only(['username', 'password']);
//        $input = $request->only('username', 'password');
//        $input = $request->except(['credit_card']);
//        $input = $request->except('credit_card');
//        $input = $request->only(['name', 'password']);
//        var_dump($input);
        //文件上传
        //获取上传文件
        //你可以使用 Illuminate\Http\Request 实例中的 file 方法获取上传的文件， file 方法返回的对象是 Symfony\Component\HttpFoundation\File\UploadedFile 类的实例，这个类继承了 PHP 的 SplFileInfo 类，并且提供了多种与文件交互的方法：
        $file = $request->file('photo');

        //你可以使用 hasFile 方法确认上传的文件是否存在
        if ($request->hasFile('photo')) {
            //
        }

        //验证上传是否成功
        //除了检查文件是否存在之外，你还可以通过 isValid 方法验证上传是否存在问题：
        if ($request->file('photo')->isValid()) {
            //
        }

        //移动上传文件
        //要将上传的文件移动到新的位置，你应该使用 move 方法，这个方法会将文件从临时位置（由你的 PHP 配置决定）移动到你指定永久保存位置：
        //$request->file('photo')->move($destinationPath);
        //$request->file('photo')->move($destinationPath, $fileName);

        $request->file('photo')->extension();

        $request->file('photo')->store('images');
        exit;


        // 不包含请求参数
        $url = $request->url();
        var_dump($url);
        // 包含请求参数
        $url = $request->fullUrl();
        var_dump($url);
        exit;

        $uri = $request->path();

        var_dump($request->is('admin/*'));

        var_dump($uri);
        var_dump($request->name);
        exit;
        /*         * ******************请求 end *************** */


        /*         * ******************路由 start *************** */
        // Generating URLs...
        $url = route('test');
        var_dump($url);
        exit;

        // Generating Redirects...
        return redirect()->route('profile');
        /*         * ******************路由 end *************** */

        $value = config('cache.default');
        var_dump($value);

        $environment = app()->environment();
        var_dump($environment);
        $value = config('app.timezone');
        var_dump($value);
        var_dump(env('APP_KEY', '555'));
        exit;

        \Illuminate\Support\Facades\DB::enableQueryLog();
        $value = \App\Model\Customer::withTrashed()->select('*')->where('customer_id', 901)->first()->toArray();
        var_dump(\Illuminate\Support\Facades\DB::getQueryLog());
        exit;

        $value1 = config('app.locale');
        var_dump($value, $value1);
        exit;
        return $value;
    }

    public function db() {

        //在 laravel 中获取表前缀的方法
        //有3种方法：
        //DB::getConfig('prefix');
        //DB::connection()->getTablePrefix();
        //Config::get('database.connections.mysql.prefix');


        /*         * ******************分页 start https://learnku.com/docs/laravel/5.8/pagination/3927 *************** */

        /*         * ******************查询构造器 start https://learnku.com/docs/laravel/5.8/database/3925 *************** */
        //###获取结果
//        //从一个数据表中获取所有行
//        //你可以 DB facade 上使用 table 方法来开始查询。该 table 方法为给定的表返回一个查询构造器实例，允许你在查询上链式调用更多的约束，最后使用 get 方法获取结果：
//        $users = DB::table('user')->get()->toArray();
//        var_dump($users);
//        //该 get 方法返回一个包含 Illuminate\Support\Collection 的结果，其中每个结果都是 PHP StdClass 对象的一个实例。你可以访问字段作为对象的属性来访问每列的值：
//        foreach ($users as $user) {
//            var_dump($user->username);
//        }
//        exit;
//
//        //###从数据表中获取单行或单列
//        //如果你只需要从数据表中获取一行数据，你可以使用 first 方法。该方法返回一个 StdClass 对象：
//        \Illuminate\Support\Facades\DB::enableQueryLog();
//        $user = DB::table('user')->where('username', 'yuki')->first();
//        echo $user->username;
//        var_dump(\Illuminate\Support\Facades\DB::getQueryLog());
//        exit;
//
//
//        //如果你甚至不需要整行数据，则可以使用 value 方法从记录中获取单个值。该方法将直接返回该字段的值：
//        $email = DB::table('user')->where('username', 'yuki')->value('username');
//
//        //###获取一列的值
//        //如果你想获取包含单列值的集合，则可以使用 pluck 方法。在下面的例子中，我们将获取角色表中标题的集合：
//        $titles = DB::table('roles')->pluck('title');
//        foreach ($titles as $title) {
//            echo $title;
//        }
//
//        //###你还可以在返回的集合中指定字段的自定义键值：
//        $roles = DB::table('roles')->pluck('title', 'name');//字段名为：name的值作为key 字段名为：title的值作为value
//        foreach ($roles as $name => $title) {
//            echo $title;
//        }
//
//        //###分块结果
//        //如果你需要处理上千条数据库记录，你可以考虑使用 chunk 方法。该方法一次获取结果集的一小块，并将其传递给 闭包 函数进行处理。该方法在 Artisan 命令 编写数千条处理数据的时候非常有用。例如，我们可以将全部 users 表数据切割成一次处理 100 条记录的一小块：
//        DB::table('users')->orderBy('id')->chunk(100, function ($users) {
//            foreach ($users as $user) {
//                //
//            }
//        });
//
//        //你可以通过在 闭包 中返回 false 来终止继续获取分块结果：
//        DB::table('users')->orderBy('id')->chunk(100, function ($users) {
//            // Process the records...
//
//            return false;
//        });
//
//        //如果要在分块结果时更新数据库记录，则块结果可能会和预计的返回结果不一致。 因此，在分块更新记录时，最好使用 chunkById 方法。 此方法将根据记录的主键自动对结果进行分页：
//        DB::table('users')->where('active', false)
//            ->chunkById(100, function ($users) {
//                foreach ($users as $user) {
//                    DB::table('users')
//                        ->where('id', $user->id)
//                        ->update(['active' => true]);
//                }
//            });
//        //{提示} 在块的回调里面更新或删除记录时，对主键或外键的任何更改都可能影响块查询。 这可能会导致记录没有包含在分块结果中。
//
//        //###聚合
//        //查询构造器还提供了各种聚合方法，比如 count, max，min， avg，还有 sum。你可以在构造查询后调用任何方法：
//        $users = DB::table('users')->count();
//        $price = DB::table('orders')->max('price');
//
//        //当然，你也可以将这些聚合方法与其他的查询语句相结合：
//        $price = DB::table('orders')
//                        ->where('finalized', 1)
//                        ->avg('price');
//
//        //###判断记录是否存在
//        //除了通过 count 方法可以确定查询条件的结果是否存在之外，还可以使用 exists 和 doesntExist 方法：
//        \Illuminate\Support\Facades\DB::enableQueryLog();
//        DB::table('user')->where('username', 'yuki')->exists();
//        DB::table('user')->where('username', 'yuki')->doesntExist();
//        var_dump(\Illuminate\Support\Facades\DB::getQueryLog());
//
//        //###Selects
//        //指定一个 Select 语句
//        //当然你可能并不总是希望从数据库表中获取所有列。使用 select 方法，你可以自定义一个 select 查询语句来查询指定的字段：
//        $users = DB::table('users')->select('name', 'email as user_email')->get();
//
//        //distinct 方法会强制让查询返回的结果不重复：
//        $users = DB::table('users')->distinct()->get();
//
//        //###如果你已经有了一个查询构造器实例，并且希望在现有的查询语句中加入一个字段，那么你可以使用 addSelect 方法：
//        $query = DB::table('users')->select('name');
//        $users = $query->addSelect('age')->get();
//
//        //###原生表达式
//        //有时候你可能需要在查询中使用原生表达式。你可以使用 DB::raw 创建一个原生表达式：
//        $users = DB::table('users')
//                             ->select(DB::raw('count(*) as user_count, status'))
//                             ->where('status', '<>', 1)
//                             ->groupBy('status')
//                             ->get();
//        //{提示} 原生表达式将会被当做字符串注入到查询中，因此你应该小心使用，避免创建 SQL 注入的漏洞。
//
//        //###原生方法
//        //可以使用以下方法代替 DB::raw，将原生表达式插入查询的各个部分。
//        //selectRaw
//        //selectRaw 方法可以代替 select(DB::raw(...))。该方法的第二个参数是可选项，值是一个绑定参数的数组：
//        $orders = DB::table('orders')
//                        ->selectRaw('price * ? as price_with_tax', [1.0825])
//                        ->get();
//
//        //whereRaw / orWhereRaw
//        //whereRaw 和 orWhereRaw 方法将原生的 where
//        //注入到你的查询中。这两个方法的第二个参数还是可选项，值还是绑定参数的数组：
//        $orders = DB::table('orders')
//                        ->whereRaw('price > IF(state = "TX", ?, 100)', [200])
//                        ->get();
//
//        //havingRaw / orHavingRaw
//        //havingRaw 和 orHavingRaw 方法可以用于将原生字符串设置为 having 语句的值：
//        $orders = DB::table('orders')
//                        ->select('department', DB::raw('SUM(price) as total_sales'))
//                        ->groupBy('department')
//                        ->havingRaw('SUM(price) > ?', [2500])
//                        ->get();
//
//        //orderByRaw
//        //orderByRaw 方法可用于将原生字符串设置为 order by 子句的值：
//        $orders = DB::table('orders')
//                        ->orderByRaw('updated_at - created_at DESC')
//                        ->get();
//
//        //###Joins
//        //Inner Join Clause
//        //查询构造器也可以编写 join 方法。若要执行基本的
//        //「内链接」，你可以在查询构造器实例上使用 join 方法。传递给 join 方法的第一个参数是你需要连接的表的名称，而其他参数则使用指定连接的字段约束。你还可以在单个查询中连接多个数据表：
//        $users = DB::table('users')
//                    ->join('contacts', 'users.id', '=', 'contacts.user_id')
//                    ->join('orders', 'users.id', '=', 'orders.user_id')
//                    ->select('users.*', 'contacts.phone', 'orders.price')
//                    ->get();
//
//        //###Left Join 语句
//        //如果你想使用 「左连接」或者 「右连接」代替「内连接」 ，可以使用 leftJoin 或者 rightJoin 方法。这两个方法与 join 方法用法相同：
//        $users = DB::table('users')
//                    ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
//                    ->get();
//        $users = DB::table('users')
//                    ->rightJoin('posts', 'users.id', '=', 'posts.user_id')
//                    ->get();
//
//        //###Cross Join 语句
//        //使用 crossJoin 方法和你想要连接的表名做 「交叉连接」。交叉连接在第一个表和被连接的表之间会生成笛卡尔积：
//        $users = DB::table('sizes')
//                    ->crossJoin('colours')
//                    ->get();
//
//        //###高级 Join 语句
//        //你可以指定更高级的 join 语句。比如传递一个 闭包 作为 join 方法的第二个参数。此 闭包 接收一个
//        //JoinClause 对象，从而指定 join 语句中指定的约束：
//        DB::table('users')
//                ->join('contacts', function ($join) {
//                    $join->on('users.id', '=', 'contacts.user_id')->orOn('');
//                })
//                ->get();
        //如果你想要在连接上使用「where」 风格的语句，你可以在连接上使用 where 和 orWhere 方法。这些方法会将列和值进行比较，而不是列和列进行比较：
        \Illuminate\Support\Facades\DB::enableQueryLog();
        DB::table('user')
                ->join('user', function ($join) {
                    $join->on('user.id', '=', 'user.id')
                    ->where('user.id', '>', 5);
                })
                ->get();
        var_dump(\Illuminate\Support\Facades\DB::getQueryLog());
        exit;

        //###子连接查询
        //你可以使用 joinSub，leftJoinSub 和 rightJoinSub 方法关联一个查询作为子查询。他们每一种方法都会接收三个参数：子查询，表别名和定义关联字段的闭包：
        $latestPosts = DB::table('posts')
                ->select('user_id', DB::raw('MAX(created_at) as last_post_created_at'))
                ->where('is_published', true)
                ->groupBy('user_id');
        $users = DB::table('users')
                        ->joinSub($latestPosts, 'latest_posts', function($join) {
                            $join->on('users.id', '=', 'latest_posts.user_id');
                        })->get();

        //###Unions
        //查询构造器还提供了将两个查询 「联合」 的快捷方式。比如，你可以先创建一个查询，然后使用 union 方法将其和第二个查询进行联合：
        $first = DB::table('users')
                ->whereNull('first_name');
        $users = DB::table('users')
                ->whereNull('last_name')
                ->union($first)
                ->get();
        //{提示} 你也可以使用 unionAll 方法，用法 union 方法是的一样。
        //###Where 语句
        //简单的 Where 语句
        //在构造 where 查询实例的中，你可以使用 where 方法。调用 where 最基本的方式是需要传递三个参数：第一个参数是列名，第二个参数是任意一个数据库系统支持的运算符，第三个是该列要比较的值。
        //例如，下面是一个要验证 「votes」 字段的值等于 100 的查询：
        $users = DB::table('users')->where('votes', '=', 100)->get();

        //为了方便，如果你只是简单比较列值和给定数值是否相等，可以将数值直接作为 where 方法的第二个参数：
        $users = DB::table('users')->where('votes', 100)->get();

        //当然，你也可以使用其他的运算符来编写 where 子句：
        $users = DB::table('users')
                ->where('votes', '>=', 100)
                ->get();

        $users = DB::table('users')
                ->where('votes', '<>', 100)
                ->get();

        $users = DB::table('users')
                ->where('name', 'like', 'T%')
                ->get();

        //你还可以传递条件数组到 where 函数中：
        $users = DB::table('users')->where([
                    ['status', '=', '1'],
                    ['subscribed', '<>', '1'],
                ])->get();

        //###Or 语句
        //你可以一起链式调用 where 约束，也可以在查询中添加 or 字句。 orWhere 方法和 where 方法接收的参数一样：
        $users = DB::table('users')
                ->where('votes', '>', 100)
                ->orWhere('name', 'John')
                ->get();
        //###其他 Where 语句
        //whereBetween
        //whereBetween 方法验证字段值是否在给定的两个值之间：
        $users = DB::table('users')
                        ->whereBetween('votes', [1, 100])->get();
        //whereNotBetween
        //whereNotBetween 方法验证字段值是否在给定的两个值之外：
        $users = DB::table('users')
                ->whereNotBetween('votes', [1, 100])
                ->get();
        //whereIn / whereNotIn
        //whereIn 方法验证字段的值必须存在指定的数组里，:
        $users = DB::table('users')
                ->whereIn('id', [1, 2, 3])
                ->get();

        //whereNotIn 方法验证字段的值必须不存在于指定的数组里:
        $users = DB::table('users')
                ->whereNotIn('id', [1, 2, 3])
                ->get();

        //whereNull / whereNotNull
        //whereNull 方法验证指定的字段必须是 NULL:
        $users = DB::table('users')
                ->whereNull('updated_at')
                ->get();

        //whereNotNull 方法验证指定的字段必须不是 NULL:
        $users = DB::table('users')
                ->whereNotNull('updated_at')
                ->get();

        //whereDate / whereMonth / whereDay / whereYear / whereTime
        //whereDate 方法用于比较字段值与给定的日期:
        $users = DB::table('users')
                ->whereDate('created_at', '2018-09-08')
                ->get();

        //whereMonth 方法用于比较字段值与一年中指定的月份:
        $users = DB::table('users')
                ->whereMonth('created_at', '9')
                ->get();

        //whereDay 方法用于比较字段值与一月中指定的日期:
        $users = DB::table('users')
                ->whereDay('created_at', '8')
                ->get();

        //whereYear 方法用于比较字段值与指定的年份:
        $users = DB::table('users')
                ->whereYear('created_at', '2018')
                ->get();

        //whereTime 方法用于比较字段值与指定的时间（时分秒）:
        $users = DB::table('users')
                ->whereTime('created_at', '=', '11:20:45')
                ->get();

        //whereColumn
        //whereColumn 方法用于比较两个字段的值 是否相等:
        $users = DB::table('users')
                ->whereColumn('first_name', 'last_name')
                ->get();

        //你也可以传入一个比较运算符:
        $users = DB::table('users')
                ->whereColumn('updated_at', '>', 'created_at')
                ->get();

        //whereColumn 你也可以传递数组 用 and 运算符链接:
        $users = DB::table('users')
                        ->whereColumn([
                            ['first_name', '=', 'last_name'],
                            ['updated_at', '>', 'created_at']
                        ])->get();

        //##参数分组
        //有时候你需要创建更高级的 where 子句，例如「where exists」或者嵌套的参数分组。 Laravel 的查询构造器也能够处理这些。下面，让我们看一个在括号中进行分组约束的例子:
        DB::table('users')
                ->where('name', '=', 'John')
                ->where(function ($query) {
                    $query->where('votes', '>', 100)
                    ->orWhere('title', '=', 'Admin');
                })
                ->get();
        //你可以看到，通过一个 Closure 写入 where 方法构建一个查询构造器 来约束一个分组。这个 Closure 接收一个查询实例，你可以使用这个实例来设置应该包含的约束。上面的例子将生成以下 SQL:
        //select * from users where name = 'John' and (votes > 100 or title = 'Admin')
        //{提示} 你应该用 orWhere 调用这个分组，以避免应用全局作用出现意外.
        //###Where Exists 语句
        //whereExists 方法允许你使用 where exists SQL 语句。 whereExists 方法接收一个 Closure 参数，该 whereExists 方法接受一个 Closure 参数，该闭包获取一个查询构建器实例从而允许你定义放置在 exists 字句中查询：
        DB::table('users')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                    ->from('orders')
                    ->whereRaw('orders.user_id = users.id');
                })
                ->get();
        //上述查询将产生如下的 SQL 语句：
        //select * from users
        //where exists (
        //    select 1 from orders where orders.user_id = users.id
        //)
        //###JSON Where 语句
        //Laravel 也支持查询 JSON 类型的字段（仅在对 JSON 类型支持的数据库上）。目前，本特性仅支持 MySQL 5.7、PostgreSQL、SQL Server 2016 以及 SQLite 3.9.0 (with the JSON1 extension)。使用 -> 操作符查询 JSON 数据：
        $users = DB::table('users')
                ->where('options->language', 'en')
                ->get();

        $users = DB::table('users')
                ->where('preferences->dining->meal', 'salad')
                ->get();

        //你也可以使用 whereJsonContains 来查询 JSON 数组：
        $users = DB::table('users')
                ->whereJsonContains('options->languages', 'en')
                ->get();

        //MySQL 和 PostgreSQL 的 whereJsonContains 可以支持多个值：
        $users = DB::table('users')
                ->whereJsonContains('options->languages', ['en', 'de'])
                ->get();

        //你可以使用 whereJsonLength 来查询 JSON 数组的长度：
        $users = DB::table('users')
                ->whereJsonLength('options->languages', 0)
                ->get();
        $users = DB::table('users')
                ->whereJsonLength('options->languages', '>', 1)
                ->get();

        //Ordering, Grouping, Limit, & Offset
        //orderBy
        //orderBy 方法允许你通过给定字段对结果集进行排序。 orderBy 的第一个参数应该是你希望排序的字段，第二个参数控制排序的方向，可以是 asc 或 desc：
        $users = DB::table('users')
                ->orderBy('name', 'desc')
                ->get();
        //latest / oldest
        //latest 和 oldest 方法可以使你轻松地通过日期排序。它默认使用 created_at 列作为排序依据。当然，你也可以传递自定义的列名：
        $user = DB::table('users')
                ->latest()
                ->first();
        //inRandomOrder
        //inRandomOrder 方法被用来将结果随机排序。例如，你可以使用此方法随机找到一个用户。
        $randomUser = DB::table('users')
                ->inRandomOrder()
                ->first();
        //groupBy / having
        //groupBy 和 having 方法可以将结果分组。 having 方法的使用与 where 方法十分相似：
        $users = DB::table('users')
                ->groupBy('account_id')
                ->having('account_id', '>', 100)
                ->get();

        //你可以向 groupBy 方法传递多个参数：
        $users = DB::table('users')
                ->groupBy('first_name', 'status')
                ->having('account_id', '>', 100)
                ->get();
        //对于更高级的 having 语法，参见 havingRaw 方法。
        //###skip / take
        //要限制结果的返回数量，或跳过指定数量的结果，你可以使用 skip 和 take 方法：
        $users = DB::table('users')->skip(10)->take(5)->get();

        //或者你也可以使用 limit 和 offset 方法：
        $users = DB::table('users')
                ->offset(10)
                ->limit(5)
                ->get();

        //###
        //条件语句
        //有时候你可能想要子句只适用于某个情况为真是才执行查询。例如你可能只想给定值在请求中存在的情况下才应用 where 语句。 你可以通过使用 when 方法：
        $role = $request->input('role');
        $users = DB::table('users')
                ->when($role, function ($query, $role) {
                    return $query->where('role_id', $role);
                })
                ->get();
        //when 方法只有在第一个参数为 true 的时候才执行给的的闭包。如果第一个参数为 false ，那么这个闭包将不会被执行
        //你可以传递另一个闭包作为 when 方法的第三个参数。 该闭包会在第一个参数为 false 的情况下执行。为了说明如何使用这个特性，我们来配置一个查询的默认排序：
        $sortBy = null;
        $users = DB::table('users')
                ->when($sortBy, function ($query, $sortBy) {
                    return $query->orderBy($sortBy);
                }, function ($query) {
                    return $query->orderBy('name');
                })
                ->get();

        //######插入
        //查询构造器还提供了 insert 方法用于插入记录到数据库中。 insert 方法接收数组形式的字段名和字段值进行插入操作：
        DB::table('users')->insert(
                ['email' => 'john@example.com', 'votes' => 0]
        );

        //你甚至可以将数组传递给 insert 方法，将多个记录插入到表中
        DB::table('users')->insert([
            ['email' => 'taylor@example.com', 'votes' => 0],
            ['email' => 'dayle@example.com', 'votes' => 0]
        ]);

        //自增 ID
        //如果数据表有自增 ID ，使用 insertGetId 方法来插入记录并返回 ID 值
        $id = DB::table('users')->insertGetId(
                ['email' => 'john@example.com', 'votes' => 0]
        );
        //{注意} 当使用 PostgreSQL 时， insertGetId 方法将默认把 id 作为自动递增字段的名称。如果你要从其他「序列」来获取 ID ，则可以将字段名称作为第二个参数传递给 insertGetId 方法。
        //######更新
        //当然， 除了插入记录到数据库中，查询构造器也可以通过 update 方法更新已有的记录。 update 方法和 insert 方法一样，接受包含要更新的字段及值的数组。你可以通过 where 子句对 update 查询进行约束：
        DB::table('users')
                ->where('id', 1)
                ->update(['votes' => 1]);
        //###更新或者新增
        //有时您可能希望更新数据库中的现有记录，或者如果不存在匹配记录则创建它。 在这种情况下，可以使用 updateOrInsert 方法。 updateOrInsert 方法接受两个参数：一个用于查找记录的条件数组，以及一个包含要更该记录的键值对数组。
        //updateOrInsert 方法将首先尝试使用第一个参数的键和值对来查找匹配的数据库记录。 如果记录存在，则使用第二个参数中的值去更新记录。 如果找不到记录，将插入一个新记录，更新的数据是两个数组的集合：
        DB::table('users')
                ->updateOrInsert(
                        ['email' => 'john@example.com', 'name' => 'John'], ['votes' => '2']
        );

        //更新 JSON 字段
        //更新 JSON 字段时，你可以使用 -> 语法访问 JSON 对象中相应的值，此操作只能支持 MySQL 5.7+：
        DB::table('users')
                ->where('id', 1)
                ->update(['options->enabled' => true]);

        //自增与自减
        //查询构造器还为给定字段的递增或递减提供了方便的方法。此方法提供了一个比手动编写 update 语句更具表达力且更精练的接口。
        //这两种方法都至少接收一个参数：需要修改的列。第二个参数是可选的，用于控制列递增或递减的量：
        DB::table('users')->increment('votes');
        DB::table('users')->increment('votes', 5);
        DB::table('users')->decrement('votes');
        DB::table('users')->decrement('votes', 5);

        //你也可以在操作过程中指定要更新的字段：
        DB::table('users')->increment('votes', 1, ['name' => 'John']);

        //######删除
        //查询构造器也可以使用 delete 方法从表中删除记录。 在使用 delete 前，可以添加 where 子句来约束 delete 语法：
        DB::table('users')->delete();
        DB::table('users')->where('votes', '>', 100)->delete();

        //如果你需要清空表，你可以使用 truncate 方法，它将删除所有行，并重置自增 ID 为零：
        DB::table('users')->truncate();

        //######悲观锁
        //查询构造器也包含一些可以帮助你在 select 语法上实现「悲观锁定」的函数。若想在查询中实现一个「共享锁」， 你可以使用 sharedLock 方法。 共享锁可防止选中的数据列被篡改，直到事务被提交为止 ：
        DB::table('users')->where('votes', '>', 100)->sharedLock()->get();

        //或者，你可以使用 lockForUpdate 方法。使用 「update」锁可避免行被其它共享锁修改或选取：
        DB::table('users')->where('votes', '>', 100)->lockForUpdate()->get();
        /*         * ******************查询构造器 end https://learnku.com/docs/laravel/5.8/database/3925 *************** */




        /*         * ******************DB start https://learnku.com/docs/laravel/5.8/database/3925 *************** */
//        //###使用多个数据库连接
//        //当使用多个数据库连接时，你可以通过 DB Facade 的 connection 方法访问每一个连接。传递给 connection 方法的参数 name 应该是 config/database.php 配置文件中 connections 数组中的一个值：
//        $users = DB::connection('mysql')->select('');
//
//        //你也可以使用一个连接实例上的 getPdo 方法访问底层的 PDO 实例：
//        $pdo = DB::connection()->getPdo();
//
//        //运行原生 SQL 查询
//        //一旦配置好数据库连接后，便可以使用 DB facade 运行查询。 DB facade 为每种类型的查询提供了方法： select，update，insert，delete 和 statement。
//
//        //###运行 Select 查询
//        //你可以使用 DB Facade 的 select 方法来运行基础的查询语句：
//        $users = DB::select('select * from users where active = ?', [1]);
//        //传递给 select 方法的第一个参数就是一个原生的 SQL 查询，而第二个参数则是需要绑定到查询中的参数值。通常，这些值用于约束 where 语句。参数绑定用于防止 SQL 注入。
//        //select 方法将始终返回一个数组，数组中的每个结果都是一个 = StdClass 对象，可以像下面这样访问结果值：
//        foreach ($users as $user) {
//            echo $user->name;
//        }
//
//        //使用命名绑定
//        //除了使用 ? 表示参数绑定外，你也可以使用命名绑定来执行一个查询：
//        $results = DB::select('select * from users where id = :id', ['id' => 1]);
//
//        //###运行插入语句
//        //可以使用 DB Facade 的 insert 方法来执行 insert 语句。与 select 一样，该方法将原生 SQL 查询作为其第一个参数，并将绑定数据作为第二个参数：
//        DB::insert('insert into users (id, name) values (?, ?)', [1, 'Dayle']);
//
//        //###运行更新语句
//        //update 方法用于更新数据库中现有的记录。该方法返回受该语句影响的行数：
//        $affected = DB::update('update users set votes = 100 where name = ?', ['John']);
//
//        //###运行删除语句
//        //delete 方法用于从数据库中删除记录。与 update 一样，返回受该语句影响的行数：
//        $deleted = DB::delete('delete from users');
//
//        //###运行普通语句
//        //有些数据库语句不会有任何返回值。对于这些语句，你可以使用 DB Facade 的 statement 方法来运行：
//        DB::statement('drop table users');
        //###数据库事务
        //你可以使用 DB facade 的 transaction 方法在数据库事务中运行一组操作。如果事务的闭包 Closure 中出现一个异常，事务将会回滚。如果事务闭包 Closure 执行成功，事务将自动提交。一旦你使用了 transaction ， 就不再需要担心手动回滚或提交的问题：
        DB::transaction(function () {
            DB::table('users')->update(['votes' => 1]);
            DB::table('posts')->delete();
        });

        //###处理死锁
        //transaction 方法接受一个可选的第二个参数 ，该参数用来表示事务发生死锁时重复执行的次数。一旦定义的次数尝试完毕，就会抛出一个异常：
        DB::transaction(function () {
            DB::table('users')->update(['votes' => 1]);
            DB::table('posts')->delete();
        }, 5);

        //###手动使用事务
        //如果你想要手动开始一个事务，并且对回滚和提交能够完全控制，那么你可以使用 DB Facade 的 beginTransaction 方法：
        DB::beginTransaction();

        //你可以使用 rollBack 方法回滚事务：
        DB::rollBack();

        //最后，你可以使用 commit 方法提交事务：
        DB::commit();
        //{tip} DB facade 的事务方法同样适用于 查询构造器 和 Eloquent ORM.
        /*         * ******************DB end https://learnku.com/docs/laravel/5.8/database/3925 *************** */

        \Illuminate\Support\Facades\DB::enableQueryLog();
        $value = \App\Models\Customer::withTrashed()->select('*')->where('customer_id', 901)->first()->toArray();
        var_dump(\Illuminate\Support\Facades\DB::getQueryLog());
        exit;
    }

    public function queue(Request $request) {
        Queue::push(new \App\Jobs\ExampleJob());

        //push 创建即时任务
        Queue::push(new ExampleJob(['a' => 123]), null, 'QueueName');

        //later 创建延时任务
        Queue::later(10, new ExampleJob(['a' => 123]), null, 'QueueName');

        exit;
        //dispatch(new \App\Jobs\ExampleJob);exit;
    }

    public function email(Request $request) {

        $dd = [
            'JP'
        ]; //'US', 'DE', 'UK',

        foreach ($dd as $country) {
//            $requestData = [
//                'store_id' => 3,
//                'customer_id' => '281605',
//                'account' => 'alexhong465@gmail.com',//xiaqq2017@gmail.com
//                'country' => $country,
//                'group' => 'customer',
//                'first_name' => 'longfeili0925',
//                'last_name' => 'longfeili0925',
//                'ip' => '127.0.0.1',
//                'remark' => '注册===ee',
//                'ctime' => '',
//                'act_id' => 9999999,
//                'source' => 0,
//            ];
//            $rs = EmailService::sendCouponEmail(3, $requestData);
//
//            $requestData = [
//                'store_id' => 3,
//                'customer_id' => '281605',
//                'account' => 'sunnyhong1993@yahoo.com',//xiaqq2017@gmail.com
//                'country' => $country,
//                'group' => 'customer',
//                'first_name' => 'longfeili0925',
//                'last_name' => 'longfeili0925',
//                'ip' => '127.0.0.1',
//                'remark' => '注册===aa',
//                'ctime' => '',
//                'act_id' => 9999999,
//                'source' => 0,
//            ];
//            $rs = EmailService::sendCouponEmail(3, $requestData);
//
//            $requestData = [
//                'store_id' => 3,
//                'customer_id' => '281605',
//                'account' => 'Jmiy_cen@patazon.net',//xiaqq2017@gmail.com
//                'country' => $country,
//                'group' => 'customer',
//                'first_name' => 'longfeili0925',
//                'last_name' => 'longfeili0925',
//                'ip' => '127.0.0.1',
//                'remark' => '注册===aa',
//                'ctime' => '',
//                'act_id' => 9999999,
//                'source' => 0,
//            ];
//            $rs = EmailService::sendCouponEmail(3, $requestData);
//
//            $requestData = [
//                'store_id' => 3,
//                'customer_id' => '281605',
//                'account' => 'longfeili0925@gmail.com',//xiaqq2017@gmail.com
//                'country' => $country,
//                'group' => 'customer',
//                'first_name' => 'longfeili0925',
//                'last_name' => 'longfeili0925',
//                'ip' => '127.0.0.1',
//                'remark' => '注册===aa',
//                'ctime' => '',
//                'act_id' => 9999999,
//                'source' => 0,
//            ];
//            $rs = EmailService::sendCouponEmail(3, $requestData);
//
//            $requestData = [
//                'store_id' => 3,
//                'customer_id' => '281605',
//                'account' => 'Alice_huang@patazon.net',//xiaqq2017@gmail.com
//                'country' => $country,
//                'group' => 'customer',
//                'first_name' => 'longfeili0925',
//                'last_name' => 'longfeili0925',
//                'ip' => '127.0.0.1',
//                'remark' => '注册===aa',
//                'ctime' => '',
//                'act_id' => 9999999,
//                'source' => 0,
//            ];
//            $rs = EmailService::sendCouponEmail(3, $requestData);

            $requestData = [
                'store_id' => 3,
                'customer_id' => '281605',
                'account' => 'Maggie_zhang@patazon.net', //xiaqq2017@gmail.com
                'country' => $country,
                'group' => 'customer',
                'first_name' => 'longfeili0925',
                'last_name' => 'longfeili0925',
                'ip' => '127.0.0.1',
                'remark' => '注册===aa',
                'ctime' => '',
                'act_id' => 9999999,
                'source' => 0,
            ];
            $rs = EmailService::sendCouponEmail(3, $requestData);


            dump($rs);
        }
        dd($dd);


        //发送激活邮件
//        $code = '';
//        $inviteCode = '';
//        $orderno = ''; //订单
//        $ip = $request->input('ip', ''); //会员ip
//        $createdAt = '';
//        $inviteId = 0;
//        $handleActivate = 1;
//        $extData = ['act_id' => 2];
//        $country = 'US';
//        $rs = EmailService::sendActivateEmail(1, 0, 'Jmiy_cen@patazon.net', $code, $inviteCode, $country, $orderno, $ip, '会员激活', $createdAt, $inviteId, $handleActivate, $extData);
//        dd($rs);
//        $data = [
//            'CA57BN' => '',
//            'GEPC034AB' => '',
//            'GEPC049ABIT' => '',
//            'GEPC066BB' => '',
//            'GEPC066BR' => '',
//            'GEPC173ABUS' => '',
//            'GEPC217AB' => '',
//            'HMHM235BWEU' => '',
//            'HMHM235BWUK' => '',
//            'VTBH267AB' => '',
//            'VTCA004B' => '',
//            'VTGEHM057ABUS' => '',
//            'VTHM004YEU' => '',
//            'VTHM004YUK' => '',
//            'VTHM024ABEU' => '',
//            'VTHM057AYEU' => '',
//            'VTHM057BBUS' => '',
//            'VTHM129BYUS' => '',
//            'VTHM196AWEU' => '',
//            'VTHM196BWEU' => '',
//            'VTPC109AB' => '',
//            'VTPC120AD' => '',
//            'VTPC132AB' => '',
//            'VTPC132ABES' => '',
//            'VTPC149ABDE' => '',
//            'VTPC149ABES' => '',
//            'VTPC149ABIT' => '',
//            'VTPC149ABUK' => '',
//            'VTPC149ABUS' => '',
//            'VTPC174ABUS' => '',
//            'VTPC175ABDE' => '',
//            'VTPC175ABFR' => '',
//            'VTPC206ABFR' => '',
//            'VTPC22BABUS' => '',
//            'HM235BWEU' => '',
//            'type_A' => 'A',
//            'type_B' => 'B',
//            'type_C' => 'C',
//            'type_D' => 'D',
//            'type_E' => '',
//            'type_F' => '',
//            'start_date' => '2019-09-10',
//            'end_date' => '2019-10-10',
//            'name' => 'name',
//            'link' => 'link',
//        ];
//
//        $accounts = [
//            'sunnyhong1993@yahoo.com',
//            'alexhong465@gmail.com',
////            'xiaqq2017@gmail.com',
////            'xiaqq2017@yahoo.com',
//            'Jmiy_cen@patazon.net',
//            //'751399695@qq.com',
//            //'Alice_huang@patazon.net',
//            //'huangliding623@gmail.com',
//            //'en920927@gmail.com',
//            //'Len_li@patazon.net',
//            '18039292996lyf@gmail.com',
//                //'jmiycen@gmail.com',
//        ];
//
//        $countrys = [
//            'US',
//            'UK',
//            'DE',
//            'JP',
//        ];
//
//        $stores = [
//            'mpow',
//        ];
//
//        $attach = [];
//        foreach ($countrys as $country) {
//            foreach ($stores as $store) {
//                \Illuminate\Support\Facades\Mail::send(('emails.coupon.' . $store . '_' . $country), $data, function ($message) use ($accounts, $country, $store, $attach) {
//                    $message->from(config('mail.from.address'), config('mail.from.name'));
//                    $message->to(['huangliding623@gmail.com']);
//                    $message->cc($accounts);
//                    //Add a subject
//                    $message->subject(config('mail.from.address') . " TEST $store $country");
//                });
//            }
//        }
//
//        return response()->json([], 200);
//        //发送优惠券邮件
//        $countrys = [
//            'US',
//            'UK',
//            'DE',
//            'JP',
//        ];
//
//        $accounts = [
////            'sunnyhong1993@yahoo.com',
////            'alexhong465@gmail.com',
////            'Sunny_hong@patazon.net',
////            'longfeili0925@gmail.com',
////            'longfeili0925@yahoo.com',
//            'xiaqq2017@gmail.com',
//            'xiaqq2017@yahoo.com',
//            'Jmiy_cen@patazon.net',
//                //'751399695@qq.com',
//                //'Alice_huang@patazon.net',
//                //'huangliding623@gmail.com',
//                //'en920927@gmail.com',
//                //'Len_li@patazon.net',
////            '18039292996lyf@gmail.com',
////            'jmiycen@gmail.com',
//        ];
//
//        $i = 1;
//        $rs = [];
//        foreach ($accounts as $key => $account) {
//            foreach ($countrys as $_key => $country) {
//                $requestData = [
//                    'store_id' => 5,
//                    'customer_id' => '',
//                    'account' => $account,
//                    'country' => $country,
//                    'group' => 'customer',
//                    'first_name' => $account,
//                    'last_name' => '',
//                    'ip' => '127.0.0.' . $i,
//                    'remark' => '注册',
//                    'ctime' => '',
//                    'act_id' => 1000,
//                    'source' => 0,
//                ];
//                $rs = EmailService::sendCouponEmail(5, $requestData);
//                $i++;
//            }
//        }
//        dd($rs, $i);
//        get_class(app('swift.mailer'));
//
        \Illuminate\Support\Facades\Mail::send('emails.coupon.default', ['content' => 'test======'], function ($message) //use ($attach)
        {//dump(config('mail.from.address'), config('mail.from.name'));
            $message->from(config('mail.from.address'), config('mail.from.name'));
            $message->to(['Jmiy_cen56562@patazon.net']); //[config('mail.from.address')]
            //Add a subject
            $message->subject("email-smtp.us-west-2.amazonaws.com-->TEST Undang Saya");
        });
        return response()->json([], 200);

        //https://learnku.com/docs/laravel/5.8/mail/3920
        //在发送消息时不止可以指定收件人。还可以通过链式调用「to」、「cc」、「bcc」一次性指定抄送和密送收件人：
//        Mail::to($request->user())
//                ->cc($moreUsers)
//                ->bcc($evenMoreUsers)
//                ->send(new OrderShipped($order));
//
//      //有时可能希望捕获 mailable 的 HTML 内容，而不发送它。可以调用 mailable 的 render 方法实现此目的。此方法返回 mailable 渲染计算后的字符串：
//        $a = new OrderShipped();
//        var_dump($a->render());//有时可能希望捕获 mailable 的 HTML 内容，而不发送它。可以调用 mailable 的 render 方法实现此目的。此方法返回 mailable 渲染计算后的字符串：
//        exit;

        Mail::to('Jmiy_cen@patazon.net')
                ->send(new OrderShipped());
        exit;

//        $message = (new OrderShipped())->onQueue('queueName');
//        Mail::to('Jmiy_cen@patazon.net')->queue($message);

        exit;
    }

    /**
     * https://www.cnblogs.com/KeenLeung/p/6041280.html
     * @param Request $request
     */
    public function reflection(Request $request) {
        //$class = new ReflectionClass('Person'); // 建立 Person这个类的反射类

        $class = new \ReflectionClass('\App\Http\Controllers\ExampleController'); // 建立 \App\Services\ActivityApplyService这个类的反射类
        //$instance = $class->newInstanceArgs([1, 2]); // 相当于实例化\App\Http\Controllers\ExampleController 类
        //dump($instance);
        //
        //获取属性(Properties)：
//        $properties = $class->getProperties();
//        dump($properties);
        //默认情况下，ReflectionClass会获取到所有的属性，private 和 protected的也可以。如果只想获取到private属性，就要额外传个参数：
        //代码如下:
//        可用参数列表：
//        代码如下:
//        ReflectionProperty::IS_STATIC
//        ReflectionProperty::IS_PUBLIC
//        ReflectionProperty::IS_PROTECTED
//        ReflectionProperty::IS_PRIVATE
//        通过$property->getName()可以得到属性名。
//        $private_properties = $class->getProperties(\ReflectionProperty::IS_STATIC);
//        dump($private_properties);
//        foreach ($properties as $property) {
//            dump($property->getName());
//        }
        //2）获取注释：
        //通过getDocComment可以得到写给property的注释。
        //代码如下:
//        foreach ($properties as $property) {
//            //if ($property->isProtected()) {
//            $docblock = $property->getDocComment();
//            dump($docblock);
////                preg_match('/ type\=([a-z_]*) /', $docblock, $matches);
////                dump($matches[1]);
//            //}
//        }
        //3）获取类的方法
//        getMethods() 来获取到类的所有methods。
//        hasMethod(string) 是否存在某个方法
//        getMethod(string) 获取方法
//        $methods = $class->getMethods();
//        foreach ($methods as $method) {
//            dump($method, $method->getName(),$method->getDocComment());
//        }
//        $instance->getName(); // 执行\App\Http\Controllers\ExampleController 里的方法getName
//        // 或者：
//        $method = $class->getMethod('getName'); // 获取\App\Http\Controllers\ExampleController 类中的getName方法
//        $method->invoke($instance);    // 执行getName 方法
//        // 或者：
//        $method = $class->getMethod('setName'); // 获取Person 类中的setName方法
//        $method->invokeArgs($instance, array('snsgou.com'));
//
        //二、通过ReflectionMethod，我们可以得到Person类的某个方法的信息：
//        1.是否“public”、“protected”、“private” 、“static”类型
//        2.方法的参数列表
//        3.方法的参数个数
//        4.反调用类的方法
//
//        代码如下:
        $method = new \ReflectionMethod('\App\Http\Controllers\ExampleController', 'api');
        //$method->isPublic() && !$method->isStatic();
        dump($method->getNumberOfParameters()); // 参数个数
        dump($method->getParameters()); // 参数对象数组
        exit;
    }

    public $data = [
        'store_id' => 3,
        'customer_id' => '',
        'account' => 'Jmiy_cen@patazon.net',
        'country' => 'US',
        'group' => 'customer',
        'first_name' => 'Jmiy_cen@patazon.net',
        'last_name' => '',
        'ip' => '127.0.0.1',
        'remark' => '注册',
        'ctime' => '',
        'act_id' => 0,
        'source' => 0,
    ];

    /**
     * 服务端WebSocket 推送信息
     * @var \Swoole\WebSocket\Server $swoole
     */
    public function push() {

        /**
         * 如果启用WebSocket server，$swoole是`Swoole\WebSocket\Server`的实例，否则是是`Swoole\Http\Server`的实例
         * @var \Swoole\WebSocket\Server|\Swoole\Http\Server $swoole
         */
//        $swoole = app('swoole');
//        var_dump($swoole->stats());// 单例

        $fd = 1; // Find fd by userId from a map [userId=>fd].
        /*         * @var \Swoole\WebSocket\Server $swoole */
        $swoole = app('swoole');
        $success = $swoole->push($fd, 'Push data to fd#1 in Controller');
        var_dump($success);
    }

    public function testProcessWrite() {
        /*         * @var \Swoole\Process $process */
        $process = app('swoole')->customProcesses['test'];
        $process->write(__CLASS__ . ': write data' . time());
        dump($process->read());
    }

    public function test(Request $request) {

        dd(\App\Services\Platform\OrderService::isExists(14, '112-9595771-4509062', Constant::PLATFORM_SERVICE_AMAZON));

        dd(Carbon::yesterday()->toDateTimeString(),Carbon::yesterday()->timestamp);

        $requestData = $request->all();
        $storeId=1;
        $actId=49;
        $customerId=109633;
        $requestData[Constant::DB_TABLE_STORE_ID] = $storeId;
        $requestData[Constant::DB_TABLE_ACT_ID] = $actId;

        $requestData[Constant::DB_TABLE_CUSTOMER_PRIMARY] = $customerId;
        $requestData[Constant::DB_TABLE_ACCOUNT] = 'Jmiy_cen@patazon.net';
        $requestData[Constant::GUESS_NUM] = '021';

        data_set($requestData,Constant::DB_TABLE_STORE_ID,$storeId);
        data_set($requestData,Constant::DB_TABLE_ACT_ID,$actId);
        data_set($requestData,Constant::DB_TABLE_CUSTOMER_PRIMARY,$customerId);

        $requestData[Constant::EVENT_DATA] = [
            Constant::DB_TABLE_EXT_ID => 570,
            Constant::DB_TABLE_EXT_TYPE => 'invite_historys',
            Constant::INVITE_DATA => [
                'data'=>[
                    'id'=>570,
                ],
            ],
            Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId, //邀请者id
            'inviteCustomerId' => 2000, //被邀请者id
            Constant::DB_TABLE_ACT_ID => $actId, //活动id
        ];

//        $parameters = [$storeId, $actId, 'event', [$storeId, $actId, $requestData, Constant::ACTION_INVITE, [Constant::EVENTS, Constant::LISTENERS]]];
//        $dd = FunctionHelper::pushQueue(FunctionHelper::getJobData(Factory::getNamespaceClass(), 'handle', $parameters));
//        dd($dd);

        $dd = \App\Services\Activity\Factory::handle($storeId, $actId, 'handle', [$requestData]);
        dd($dd);



        //$dd = \App\Services\Activity\Factory::handle($storeId, $actId, 'handleInviteEmail', [$requestData],['ActEmailService']);

        $requestData = $request->all();

        $storeId=2;
        $actId=44;
        $customerId=308087;
        $requestData[Constant::DB_TABLE_STORE_ID] = $storeId;
        $requestData[Constant::DB_TABLE_ACT_ID] = $actId;

        $requestData[Constant::DB_TABLE_CUSTOMER_PRIMARY] = $customerId;
        $requestData[Constant::DB_TABLE_ACCOUNT] = 'Jmiy_cen@patazon.net';
        $requestData[Constant::SOCIAL_MEDIA] = 'FB';


        $dd = \App\Services\Activity\Factory::handle($storeId, $actId, 'handleShare', [$requestData]);
        dd($dd);


//        $manyResult = 0;
//
//        dd($manyResult ?: false);

//        $values = [
//            'a' => 1,
//            'b' => 2,
//            'c' => 3,
//            'd' => 5,
//        ];
//        $handleCacheData = FunctionHelper::getJobData(OrderWarrantyService::getNamespaceClass(), 'hmincrby', ['hmset', $values], []);//
//        $dd1 = OrderWarrantyService::handleCache('', $handleCacheData);

//        $handleCacheData = FunctionHelper::getJobData(OrderWarrantyService::getNamespaceClass(), 'hmset', ['hmset', $values,6000], []);//
//        $dd1 = OrderWarrantyService::handleCache('', $handleCacheData);

//        $handleCacheData = FunctionHelper::getJobData(OrderWarrantyService::getNamespaceClass(), 'setbit', ['test_setbit', 0, 1, 600], []);//
//        $dd1 = OrderWarrantyService::handleCache('', $handleCacheData);

        $handleCacheData = FunctionHelper::getJobData(OrderWarrantyService::getNamespaceClass(), 'getbit', ['test_setbit1', 0], []);//, 20
        $dd1 = OrderWarrantyService::handleCache('', $handleCacheData);
        dd($dd1);//,$dd

        $orderConfig = OrderWarrantyService::getOrderEmailData(1, 287856);
        dd($orderConfig);

        $supportCredit = DictStoreService::getByTypeAndKey(1, Constant::ORDER_BIND, 'support_credit', true, true);
        dd($supportCredit || $supportCredit==='');

        dd(Carbon::createFromTimestamp(1622444400)->toDateTimeString());

        $parameters = [1, 49, 'event', [1, 49, $request->all(), Constant::ACTION_INVITE, [Constant::EVENTS, Constant::LISTENERS]]];
        $dd = FunctionHelper::pushQueue(FunctionHelper::getJobData(Factory::getNamespaceClass(), 'handle', $parameters));
        dd($dd);

        dd(decrypt('eyJpdiI6Iis0VnZZVnhsakhsVHRyRkw5OU1YcVE9PSIsInZhbHVlIjoiWEVzdVV2Vnp5S01vNTN1bWNiUzR6KytaSEZMUWp5MGNkaVB2bXU5RXN1OD0iLCJtYWMiOiJjMGNhZjMwMTA5YzM4MDhmNjBlOTljM2JjNTg5MzdjNTVmYmVlNDU5ZTAxNjA1YWJmYTE3MjY2MjRmY2FmNTUxIn0='));

        $configs = DictStoreService::getListByType(1, 'social_media_account', 'sorts asc', 'conf_key', 'conf_value');
        dd($configs);

//        $service = \App\Services\ActivityGuessNumberService::getNamespaceClass();
//        $method = "testemail";
//        $parameters = ["1111", "33333"];
//
//        $extData = [
//            'queueConnectionName' => 'mail',//Queue Connection
//            'queue' => '{emails}',//Queue Name
//            //'delay' => 1,//任务延迟执行时间  单位：秒
//        ];
//        $data = FunctionHelper::getJobData($service, $method, $parameters,[],$extData);
//        dd(FunctionHelper::pushQueue($data));
//
//        dd(Carbon::createFromTimestamp(Carbon::yesterday()->timestamp+24*60*60)->toDateTimeString());
//        dd(Carbon::yesterday()->toDateTimeString(),Carbon::today()->toDateTimeString());
//
//        dd(FunctionHelper::getRandNum(3));

        $requestData = $request->all();

        $storeId=1;
        $actId=49;
        $customerId=109633;
        $requestData[Constant::DB_TABLE_STORE_ID] = $storeId;
        $requestData[Constant::DB_TABLE_ACT_ID] = $actId;

        $requestData[Constant::DB_TABLE_CUSTOMER_PRIMARY] = $customerId;
        $requestData[Constant::DB_TABLE_ACCOUNT] = 'Jmiy_cen@patazon.net';
        $requestData[Constant::GUESS_NUM] = '021';

        data_set($requestData,Constant::DB_TABLE_STORE_ID,$storeId,false);
        data_set($requestData,Constant::DB_TABLE_ACT_ID,$actId,false);
        data_set($requestData,Constant::DB_TABLE_CUSTOMER_PRIMARY,$customerId,false);

        $dd = \App\Services\Activity\Factory::handle($storeId, $actId, 'handleFollow', [$requestData]);
        dd($dd);


        //$dd = \App\Services\Activity\Factory::handle($storeId, $actId, 'handleInviteEmail', [$requestData],['ActEmailService']);

//        $dd = \App\Services\Activity\Factory::handle($storeId, $actId, 'updateNum', [$storeId, $actId, $customerId, $requestData,'add_nums',Constant::ACTION_INVITE,1]);
//        dd($dd);

        //static::event($storeId, $actId, $requestData, Constant::EVENT_UPDATE_NUM, [Constant::EVENTS, Constant::LISTENERS]);

//        $dd=\App\Services\Activity\Factory::handle($storeId, $actId, 'event', [$storeId, $actId, $requestData, Constant::EVENT_UPDATE_NUM, [Constant::EVENTS, Constant::LISTENERS]]);
//        return Response::json($dd);

        $dd = \App\Services\Activity\Factory::handle($storeId, $actId, 'getLuckyNum', [$storeId, $actId]);
        dd($dd);

        //提交猜数
//        $dd = \App\Services\Activity\Factory::handle($storeId, $actId, 'handle', [$storeId, $actId, $customerId, $requestData]);
//        dump($dd);
//
//        $actionData = FunctionHelper::getJobData(\App\Services\ActivityService::getNamespaceClass(), 'get', [],$requestData);
//        $lotteryData = \App\Services\ActivityService::handleLimit($storeId, $actId, $customerId, $actionData);
//        dd($lotteryData);

        //$dd=call([GameService::getNamespaceClass(),'handleLuckyNumPlay'],[$storeId, $actId, $customerId, $requestData]);
        //$dd = call_user_func_array(GameService::class.'::handleLuckyNumPlay', [$storeId, $actId, $customerId, $requestData]);

        //提交邀请
        $requestData[Constant::EVENT_DATA] = [
            Constant::DB_TABLE_EXT_ID => 570,
            Constant::DB_TABLE_EXT_TYPE => 'invite_historys',
            Constant::INVITE_DATA => [
                'data'=>[
                    'id'=>570,
                ],
            ],
            Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId, //邀请者id
            'inviteCustomerId' => 2000, //被邀请者id
            Constant::DB_TABLE_ACT_ID => $actId, //活动id
        ];

        //触发邀请事件
//        $dd = \App\Services\Activity\Factory::handle($storeId, $actId, 'event', [$storeId, $actId, $requestData, Constant::ACTION_INVITE,[Constant::EVENTS, Constant::LISTENERS]]);
//        dump($dd);

//        $dd = \App\Services\Activity\Factory::handle($storeId, $actId, 'handleInvite', [$requestData]);
//        dump($dd);

        $dd = \App\Services\Activity\Factory::handle($storeId, $actId, 'handleFollow', [$requestData]);
        dump($dd);

        $actionData = FunctionHelper::getJobData(\App\Services\ActivityService::getNamespaceClass(), 'get', [],$requestData);
        $lotteryData = \App\Services\ActivityService::handleLimit($storeId, $actId, $customerId, $actionData);
        dd($lotteryData);

        dd('inviteData.data.id'==Constant::INVITE_DATA . Constant::LINKER . Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::DB_TABLE_PRIMARY);


        //$lotteryData = GameService::getPlayNums(1, 49, 'lucky_numbers', 'country');

        $activityConfigData = ActivityService::getActivityConfigData(1, 49, 'lucky_numbers', Constant::DB_TABLE_COUNTRY);
        $activityConfigData = ActivityService::getActivityConfigData(1, 49, 'act_form', 'act_form');
        dd($activityConfigData);


//        $handleCacheData = FunctionHelper::getJobData(ActivityService::getNamespaceClass(), 'hgetall', ['hkey']);
//        $dd = ActivityService::handleCache('', $handleCacheData);

//        $ttl=1000;
//        $values = [
//            'a'=>5,
//            'b'=>3,
//            'c'=>'-10',
//        ];
//        $handleCacheData = FunctionHelper::getJobData(ActivityService::getNamespaceClass(), 'hmset', ['hkey',$values, $ttl]);
//        $dd = ActivityService::handleCache('', $handleCacheData);
////
////        $values = [
////            'a'=>3,
////            'b'=>1,
////            'c'=>-1,
////        ];
////        $handleCacheData = FunctionHelper::getJobData(ActivityService::getNamespaceClass(), 'hmincrby', ['hkey',$values]);
////        $dd = ActivityService::handleCache('', $handleCacheData);
////        dump($dd['b']->getValue());
////
////
//        $handleCacheData = FunctionHelper::getJobData(ActivityService::getNamespaceClass(), 'hincrby', ['hkey','c',-10]);
//        $dd = ActivityService::handleCache('', $handleCacheData);
//        dd($dd);
//
//        $handleCacheData = FunctionHelper::getJobData(ActivityService::getNamespaceClass(), 'hgetall', ['hkey666']);
//        $dd = ActivityService::handleCache('', $handleCacheData);
//
//        dd($dd);

        //dd(event(new \App\Events\InviteEvent($request->all())));

//        $select = ['*',DB::raw("FROM_UNIXTIME(start_at, '%Y-%m-%d %H:%i:%S') as a"),DB::raw("FROM_UNIXTIME(end_at, '%Y-%m-%d %H:%i:%S') as b")];
//        $statData = ActivityStatService::existsOrFirst(1, '', ['id'=>1], true, $select);
//        dump($statData);

//        dump(Carbon::now()->toDateTimeString(),Carbon::createFromTimestamp(1621478206)->toDateTimeString(),Carbon::createFromTimestamp(1621493999)->toDateTimeString());


        $requestData = $request->all();
        //$requestData['act_form'] = 'lucky_numbers';
        $actionData = FunctionHelper::getJobData(\App\Services\ActivityService::getNamespaceClass(), 'decrement', [],$requestData);
        //$actionData = FunctionHelper::getJobData(\App\Services\ActivityService::getNamespaceClass(), 'decrement', [-2],$requestData);
        $lotteryData = ActivityService::handleLimit(1, 49, 109633, $actionData);
        dump($lotteryData);

        $actionData = FunctionHelper::getJobData(\App\Services\ActivityService::getNamespaceClass(), 'get', [],$requestData);
        $lotteryData = ActivityService::handleLimit(1, 49, 109633, $actionData);
        dd($lotteryData);



        dd(FunctionHelper::handleDatetime('5-Feb-19', 'Y-m-d H:i:s', 'date'));

        dd('86.179.109.10',FunctionHelper::getCountry('86.179.109.10'));

        dd(\App\Services\OrderWarrantyService::handleEmail(2, 60545));

        dd('103.120.228.100',FunctionHelper::getCountry('103.120.228.100'),'103.120.228.101',FunctionHelper::getCountry('103.120.228.101'));

        //使用消息队列解析上传的文件
//        $service = \App\Services\CustomerService::getNamespaceClass();
//        $method = 'readShopfiyAppAccount';
//        $parameters = [3, storage_path('logs/export_81162.csv')];
//
//        $dd = FunctionHelper::pushQueue(FunctionHelper::getJobData($service, $method, $parameters, []), null, '{data-import}'); //把任务加入消息队列

        //return Response::json(FunctionHelper::handleTime(str_replace('-', '/', '02-05-2021 00:33:38')));//, 'Y-m-d H:i:s','date'

        //\App\Services\CustomerService::readShopfiyAppAccount(3, storage_path('logs/export_84678.csv'));

//        $dd = explode('  ','Montreal  Canada');
//        $dd1 = explode('  ','United States');
//        dd(end($dd),end($dd1),$dd1,explode('  ','Richmond  United Kingdom'),explode('  ','Inglewood  CA'));
//
//        $file = fopen(storage_path('logs/export_81162.csv'), "r");
//        $data = [];
//        while (!feof($file)) {
//            $data[] = fgetcsv($file);
//        }
//        fclose($file);

        return Response::json([]);

        $orderNo = '403-0397991-5457935';//['403-0397991-5457935','249-0970163-7278212','204-5270630-5345937','408-9935382-8046713','111-0010687-4664207','113-0293470-3599454','112-4092408-8293852','403-0397991-5457935'];
        //dump(\App\Services\Platform\OrderService::isExists(1,$orderNo));
        //dump(\App\Services\Platform\OrderService::getOrderData($orderNo));

        return Response::json(\App\Services\Platform\OrderService::getOrderDataNew($orderNo, ''));

        //dd(base64_decode('Zm9vOmJhcg=='));

        dd($this->httpClient());

        dd($this->elasticSearch());

        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYxNjk4Mzc1OSwiaWQiOiIxMzI1In0.unPXmk5kHxDEJNchpRUH62egStD2JZGezHVLMH1nKbo';
        $user = \App\Services\Psc\ServiceManager::handle(Constant::PLATFORM_SERVICE_PATOZON, 'User','tokenAuthentication',[$token]);
dd($user);

//        $version = 'v2';
//
//        dd(OrderWarrantyService::getWarranyStore(1, 'v2_email_from_name'));


//        $uploadeFile = new \Symfony\Component\HttpFoundation\File\UploadedFile(storage_path('logs/reward_coupon_template (1565689898).xlsx'), 'reward_coupon_template (1565689898).xlsx');
//        $file = \Illuminate\Http\UploadedFile::createFromBase($uploadeFile);
//        $requestData = [
//            'file'=>$file,
//        ];
        //$dd=\App\Services\RewardService::getCodeRecords($request->all(), $group = 'reward', $useType = 1);

        $data = RewardService::add(14, $request->all());
        return Response::json($data);

        dd(OrderReviewService::audit(8, 1, 3, '系统自动审核', '折扣码，积分奖励 自动审核通过', true));

        dd((\App\Services\RewardService::getClientRewardTypeValue(1, '20% GIFT CARD reward name', 1, '10 GIFT CARD', 'USD')));//

        dd(FunctionHelper::getNumber('abc'),md5('.'));

        dd(UniqueIdService::getUniqueId('/order/reviewList'));

        dd(\App\Services\Permission\AdminConfigService::getAdminConfig(1,'/order/reviewList'));

        dd(FunctionHelper::getUniqueId('/order/reviewList'));

        $rewardData = \App\Services\RewardService::getRewardFromAsin(1, 'B01I1430WQ', 'US');
        dd($rewardData);

        var_dump(data_get(app('Activity',['attributes'=>['a'=>'a']]),'a'));
        var_dump(data_get(app('Activity',['attributes'=>['a'=>'b']]),'a'));
        var_dump(data_get(app('Activity',['attributes'=>['a'=>'b']]),'a'));
        var_dump(data_get(app('Activity',['attributes'=>['a'=>'b']]),'a'));
//        var_dump(data_get(app('Activity',['attributes'=>['a'=>'c']]),'a'));
//        var_dump(data_get(app('Activity',['attributes'=>['a'=>'d']]),'a'));

        return [
            'data' => 56565,
        ];



//        $lock = \App\Util\Cache\CacheManager::lock('foo', 100);
//        $get = $lock->get();
//
//        if ($get) {
//            // 获取锁定10秒...
//
//            //$lock->release();
//        }

        $get=\App\Util\Cache\CacheManager::lock('foo')->get(function () {
            // 获取无限期锁并自动释放...
            var_dump('lock');
        });

        return [
            'data' => $get,
        ];



//        FunctionHelper::getCountry('127.0.0.1');
        return Response::json(1);


//            var_dump(swoole_version());
//            return Response::json(version_compare(swoole_version(), '1.9.5', '<'));
//        return Response::json(ActivityProductService::getActProductItems(8, 'ActivityPrize-210'));
//
//        $conf = new \RdKafka\Conf();
//        $conf->set('metadata.broker.list', 'localhost:9092');
//
//        //If you need to produce exactly once and want to keep the original produce order, uncomment the line below
//        //$conf->set('enable.idempotence', 'true');
//
//        $producer = new \RdKafka\Producer($conf);
//        $producer->addBrokers("localhost:9092,localhost:9093,localhost:9094");
//
////        $topicConf = new \RdKafka\TopicConf();
////        //$topicConf->setPartitioner(1);
////        $topicConf->set('replication-factor',3);
////        $topicConf->set('partitions',1);
////        $topic = $producer->newTopic("test52",$topicConf);
//
//        $topic = $producer->newTopic("test53");
//
//        for ($i = 0; $i < 10; $i++) {
//            $topic->produce(RD_KAFKA_PARTITION_UA, 0, "Message $i");
//            $producer->poll(0);
//        }
//
//        for ($flushRetries = 0; $flushRetries < 10; $flushRetries++) {
//            $result = $producer->flush(10000);
//            if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
//                break;
//            }
//        }
//
//        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
//            throw new \RuntimeException('Was unable to flush, messages might be lost!');
//        }

//        $conf = new \RdKafka\Conf();
//        $conf->set('log_level', (string) LOG_DEBUG);
//        $conf->set('debug', 'all');
//        $rk = new \RdKafka\Producer($conf);
//        $rk->addBrokers("127.0.0.1:9092 --replication-factor 3 --partitions 5 --topic");
////        $rk->addBrokers("127.0.0.1:9093");
////        $rk->addBrokers("127.0.0.1:9094");
//        //$rk->addBrokers("127.0.0.1:2181");
//        //$rk->addBrokers("192.168.5.134:9092");
//
//
//        $topic = $rk->newTopic("test6");
//
//        $topic->produce(RD_KAFKA_PARTITION_UA, 0, "Message payload=>".FunctionHelper::randomStr(10));
//
//        $timeout_ms = 1000;
//        // Forget messages that are not fully sent yet
//        //$rk->purge(RD_KAFKA_PURGE_F_QUEUE);
//        //$rk->flush($timeout_ms);


//        return Response::json(1);


        $action = Constant::ACTION_INVITE;
        $type = Constant::SIGNUP_KEY;
        $confKey = 'invite_credit';
        $expType = Constant::SIGNUP_KEY;
        $expConfKey = 'invite_exp';
        $storeId = 1;
        $inviterId = 905234;
        $actId = 29;

        $actCreditLogParameters = CreditService::getHandleLogParameters($storeId, $actId, null, $inviterId, Constant::REGISTERED, $action, 'credit');
        $type = data_get($actCreditLogParameters, Constant::DB_TABLE_TYPE);
        $confKey = data_get($actCreditLogParameters, Constant::DB_TABLE_VALUE);//邀请功能积分

//        $actExpLogParameters = ExpService::getHandleLogParameters($storeId, $actId, $expConfKey, $inviterId, Constant::SIGNUP_KEY, $action, 'exp');
//        $expType = data_get($actExpLogParameters, Constant::DB_TABLE_TYPE);;
//        $expConfKey = data_get($actExpLogParameters, Constant::DB_TABLE_VALUE);//邀请功能经验
//
//        if (null !== $type) {//如果 $actId 对应的活动没有限制邀请积分，就根据常规配置限制邀请积分if (null !== $type) {//如果 $actId 对应的活动没有限制邀请积分，就根据常规配置限制邀请积分
//            $creditLogParameters = CreditService::getHandleLogParameters($storeId, 0, $confKey, $inviterId, Constant::SIGNUP_KEY, $action, 'credit');
//            $type = data_get($creditLogParameters, Constant::DB_TABLE_TYPE);
//            $confKey = data_get($creditLogParameters, Constant::DB_TABLE_VALUE);//邀请功能积分
//        }
//
//        if (null !== $expType) {//如果 $actId 对应的活动没有限制邀请经验，就根据常规配置限制邀请经验
//            $expLogParameters = ExpService::getHandleLogParameters($storeId, 0, $expConfKey, $inviterId, Constant::SIGNUP_KEY, $action, 'exp');
//            $expType = data_get($expLogParameters, Constant::DB_TABLE_TYPE);;
//            $expConfKey = data_get($expLogParameters, Constant::DB_TABLE_VALUE);//邀请功能经验
//        }

        return Response::json([$type,$confKey,$expType,$expConfKey]);


        return Response::json([$type,$confKey,$expType,$expConfKey,CreditService::handleVip($storeId, $inviterId, $action, $type, $confKey, [], $expType, $expConfKey)]);



//        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0);
//        $data = [
//            'customer_id' => $request->input(Constant::DB_TABLE_STORE_ID, 0),
//        ];
//        \App\Services\RankService::getModel($storeId)->insert($data)

        return Response::json(count(app('swoole')->connections));

//        $appEnv = 'sandbox';
//        $storeId = 11;
//        $request->offsetSet('app_env',$appEnv);
//        FunctionHelper::setTimezone($storeId,'','',$appEnv);
//
////        $storeData = StoreService::getModel()->pluck('name', 'id')->sortByDesc(function ($value, $key) {
////            return $value;
////        });
////        dump($storeData);
//
//        \App\Services\Store\Shopify\BaseService::setConf($storeId);
        return Response::json([]);

//        $where = [
//            [
//                [Constant::DB_TABLE_PRIMARY, '>', 787560]
//            ]
//        ];
//
//        $lists = ErpBusGiftCardApply::buildWhere($where)
//            ->orderBy(Constant::DB_TABLE_PRIMARY, Constant::DB_EXECUTION_PLAN_ORDER_ASC)
//            ->limit(100)
//            ->get();
//        //dump($lists);
////        if($lists && $lists->isEmpty()){
////            dump($lists->isEmpty());
////        }
////
////        $lists = $lists->toArray();
//
//        //dump($lists);

        return Response::json([]);




        $requestData = [
            'a'=>89,
            'b'
        ];
        $routeKey = 'd';
        $dd = Arr::has($requestData, $routeKey);

        //$dd = \App\Services\OrderWarrantyService::pushEmailQueue(1, 14929, 0);
        return Response::json([$dd,$request->all()]);


//        $where = [
//            [
//                [Constant::DB_TABLE_PRIMARY, '>=', 97460],
//                [Constant::DB_TABLE_STORE_ID, '=', 1],
//                [Constant::DB_TABLE_TYPE, '=', Constant::DB_TABLE_PLATFORM],
//                [Constant::DB_TABLE_PLATFORM, '=', Constant::PLATFORM_AMAZON],
//                [Constant::DB_TABLE_ORDER_STATUS, '=', 1],
//            ]
//        ];
//        $orderModel = \App\Services\OrderWarrantyService::getModel(1, '');
//        $orderModel->buildWhere($where)->select([Constant::DB_TABLE_PRIMARY])
//            ->orderBy(Constant::DB_TABLE_PRIMARY,'DESC')
//            ->chunk(100, function ($data) {
//                foreach ($data as $item) {
//                    \App\Services\OrderWarrantyService::pushEmailQueue(1, data_get($item,Constant::DB_TABLE_PRIMARY,0), 0); //推送订单邮件到消息队列
//                    dump(1, data_get($item,Constant::DB_TABLE_PRIMARY,0));
//                }
//            });
//
//        dd(8989);

//        // 调用getContent()来获取原始的POST body，而不能用file_get_contents('php://input')
//        $rawContent = $request->getContent();
//        $data = file_get_contents('php://input');
//
//        //dd($rawContent,$data);
//
//        $key = "X-Shopify-Hmac-Sha256";
//        $hmac = $request->headers->get($key, '');
//
//        dump(swoole_cpu_num());
//
////        $fileData = CdnManager::upload(Constant::UPLOAD_FILE_KEY, $request, '/upload/file/');
////        dump($request->files->all(),$request->file());
////
////            $parameters = Response::getResponseData($fileData);
////            return Response::json(...$parameters);
//
//        //服务端相关信息：$request->server->all(),  $request->server->get('REQUEST_TIME_FLOAT')
//        return Response::json([
//            $request->server->get('REQUEST_TIME_FLOAT'),$rawContent,$data,$request->all(),$request->file(),$hmac
//        ]);
//
////        dump(unserialize(file_get_contents(storage_path('laravels.conf'))));
////
////        dump(app('app')->runningInConsole(),app('app')->environment(['localhost']));
//
////        dump(\App\Services\Psc\ServiceManager::handle(Constant::PLATFORM_SERVICE_PATOZON, 'Permission','getPermissionByRole',['eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYwNzU3OTA2NSwiaWQiOiI2NzYifQ.FkqJRHX5cO2CnAHSCsKS8lNRkxJsI2byttgQY_Tt7xc']));
//
////        dump(\App\Services\Psc\ServiceManager::handle(Constant::PLATFORM_SERVICE_PATOZON, 'User','singleSignOff',['eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYwNzU5MTYyMywiaWQiOiI2NzYifQ.3CctR4E63iLdjzoD1ppL8G3BV-9C_4ubsIDTTXUKV0g']));
////
////        dump(\App\Services\Psc\ServiceManager::handle(Constant::PLATFORM_SERVICE_PATOZON, 'Permission','getPermissionByRole',['eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYwNzU5MTYyMywiaWQiOiI2NzYifQ.3CctR4E63iLdjzoD1ppL8G3BV-9C_4ubsIDTTXUKV0g']));
//
//        return Response::json([]);
//
//        $parameters = [
//            'orderno' => '026-0001496-9271579',
//            'order_country' => 'US',
//        ];
//        $orderData = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_AMAZON, 'Order', 'getOrderItem', [1, $parameters]);
//        dd($orderData);
//
//        $action = Constant::ACTION_INVITE;
//        $type = Constant::REGISTERED;
//        $confKey = 'invite_credit';
//        $expType = Constant::SIGNUP_KEY;
//        $expConfKey = 'invite_exp';
//        $inviterId = 55;
//        $storeId = 1;
//        $actId=28;
//
//        $actCreditLogParameters = \App\Services\CreditService::getHandleLogParameters($storeId, $actId, null, $inviterId, Constant::REGISTERED, $action, 'credit');
//        $type = data_get($actCreditLogParameters, Constant::DB_TABLE_TYPE);
//        $confKey = data_get($actCreditLogParameters, Constant::DB_TABLE_VALUE);//邀请功能积分
//
//        $creditLogParameters=[];
//        if (null !== $type) {//如果 $actId 对应的活动没有限制邀请积分，就根据常规配置限制邀请积分if (null !== $type) {//如果 $actId 对应的活动没有限制邀请积分，就根据常规配置限制邀请积分
//            $creditLogParameters = \App\Services\CreditService::getHandleLogParameters($storeId, 0, $confKey, $inviterId, Constant::SIGNUP_KEY, $action, 'credit');
//            $type = data_get($creditLogParameters, Constant::DB_TABLE_TYPE);
//            $confKey = data_get($creditLogParameters, Constant::DB_TABLE_VALUE);//邀请功能积分
//        }
//
//
//        dd($actCreditLogParameters, $creditLogParameters, $type, $confKey);
//
//        $distKey = [Constant::IP_LIMIT_KEY, 'invite_ip_limit', 'invite_ip_limit_ttl'];
//        $extWhere = [
//            Constant::DICT => [
//                Constant::DB_TABLE_DICT_KEY => $distKey,
//            ],
//            Constant::DICT_STORE => [
//                Constant::DB_TABLE_STORE_DICT_KEY => $distKey,
//            ],
//        ];
//        $registeredConfig = \App\Services\CustomerService::getMergeConfig(1, Constant::SIGNUP_KEY, $extWhere);
//        $registeredIpLimit = data_get($registeredConfig, 'invite_ip_limit', Constant::PARAMETER_STRING_DEFAULT); //通过邀请码注册的限制
//        $keyData[] = Constant::ACTION_INVITE;
//        $ttl = data_get($registeredConfig, 'invite_ip_limit_ttl', 0); //通过邀请码注册的限制时间
//        dd($registeredConfig,$registeredIpLimit,$keyData,$ttl);
//
//        dd(\App\Services\CreditService::getHandleLogParameters(8,362816),\App\Services\ExpService::getHandleLogParameters(8,362816,Constant::SIGNUP_KEY, Constant::ACTION_INVITE, 'exp'));
//
//
////        $distKey = [Constant::IP_LIMIT_KEY, 'invite_ip_limit', 'invite_ip_limit_ttl'];
////        $extWhere = [
////            Constant::DICT => [
////                Constant::DB_TABLE_DICT_KEY => $distKey,
////            ],
////            Constant::DICT_STORE => [
////                Constant::DB_TABLE_STORE_DICT_KEY => $distKey,
////            ],
////        ];
////        $registeredConfig = \App\Services\CustomerService::getMergeConfig(2, Constant::SIGNUP_KEY, $extWhere);
////
////        $registeredIpLimit = data_get($registeredConfig, Constant::IP_LIMIT_KEY, Constant::PARAMETER_STRING_DEFAULT); //通过邀请码注册的限制
////        if ($registeredIpLimit !== Constant::PARAMETER_STRING_DEFAULT) {
////            $keyData[] = Constant::ACTION_INVITE;
////            $ttl = data_get($registeredConfig, 'invite_ip_limit_ttl', 0); //通过邀请码注册的限制时间
////        }
////
////        dd($registeredConfig,$registeredIpLimit,$keyData,$ttl);
//
//        $html = \App\Services\Psc\PscService::getPermissionByRole('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYwNzQxMTQ4MywiaWQiOiIxMDE5In0.zQMwFESp0rb_ZpwklmCnJ9HOpYBFodB3MUCqojYgaPg');
//        dd($html);
//
//        $html = \App\Services\Psc\PscService::tokenAuthentication('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYwNzMxNzUyOSwiaWQiOiI2NzYifQ.dH7q6z70AjoWfIeREtCdgzt46whr13apgBqWdOPeWVM');
//        dd($html);
//
//        $postParams = [];
//
//        $headers = [
//            'Content-Type: application/json; charset=utf-8', //设置请求内容为 json  这个时候post数据必须是json串 否则请求参数会解析失败
//            'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYwNzMxNzUyOSwiaWQiOiI2NzYifQ.dH7q6z70AjoWfIeREtCdgzt46whr13apgBqWdOPeWVM', //设置请求内容为 json  这个时候post数据必须是json串 否则请求参数会解析失败
//        ];
//        dump($request->headers->get('Authorization'));
//        //http://172.16.6.92/api/user/tokenAuthentication
//        $html = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_SHOPIFY, 'Base', 'request', ['http://172.16.6.92/api/user/tokenAuthentication', $postParams, '', '', 'GET', $headers]);
//        dd($html);
//
//        $orderIds = explode(',',$request->input('id'));
//        $storeId = $request->input('store_id');
//        $request_mark = $request->input('request_mark');
//        foreach ($orderIds as $orderId) {
//            dump(\App\Services\OrderWarrantyService::handleBind($orderId, $storeId, $request_mark));
//        }
//
//        dd(8989899);
//
//        //dump(config('database.redis'));
//
//        // 调用getContent()来获取原始的POST body，而不能用file_get_contents('php://input')
//        $rawContent = $request->getContent();
//
//        $data = file_get_contents('php://input');
//
//        //dd($rawContent,$data);
//
//        return Response::json([
//            $rawContent,$data
//        ]);


        $select = ['ci.' . Constant::DB_TABLE_ACCOUNT, 'ci.' . Constant::DB_TABLE_FIRST_NAME, 'ci.' . Constant::DB_TABLE_LAST_NAME, 'ci.' . Constant::DB_TABLE_GENDER, 'ci.' . Constant::DB_TABLE_BRITHDAY,
            'ci.' . Constant::DB_TABLE_COUNTRY, 'ci.' . 'mtime', 'ci.' . Constant::DB_TABLE_IP, 'ci.' . Constant::DB_TABLE_PROFILE_URL, 'ci.' . Constant::DB_TABLE_EDIT_AT, 'ci.' . Constant::DB_TABLE_CUSTOMER_PRIMARY];

        $storeId = 1;
//
////        $genderData = \App\Services\DictService::getListByType(Constant::DB_TABLE_GENDER, Constant::DB_TABLE_DICT_KEY, Constant::DB_TABLE_DICT_VALUE);
////        $dbExecutionPlan = [
////            Constant::DB_EXECUTION_PLAN_PARENT => [
////                Constant::DB_EXECUTION_PLAN_SETCONNECTION => true,
////                Constant::DB_EXECUTION_PLAN_STOREID => $storeId,
////                Constant::DB_EXECUTION_PLAN_BUILDER => null,
////                'make' => \App\Services\CustomerInfoService::getNamespaceClass(),
////                'from' => 'customer_info as ci',
////                Constant::DB_EXECUTION_PLAN_SELECT => $select,
////                Constant::DB_EXECUTION_PLAN_WHERE => [],
////                Constant::DB_EXECUTION_PLAN_LIMIT => 10,
////                Constant::DB_EXECUTION_PLAN_OFFSET => 0,
////                Constant::DB_EXECUTION_PLAN_IS_PAGE => false,
////                Constant::DB_EXECUTION_PLAN_PAGINATION => [],
////                Constant::DB_EXECUTION_PLAN_HANDLE_DATA => [
////                    'mtime' => [
////                        Constant::DB_EXECUTION_PLAN_FIELD => Constant::DB_TABLE_EDIT_AT,
////                        'data' => [],
////                        Constant::DB_EXECUTION_PLAN_DATATYPE => '',
////                        Constant::DB_EXECUTION_PLAN_DATA_FORMAT => '',
////                        'glue' => '',
////                        Constant::DB_EXECUTION_PLAN_DEFAULT => '',
////                    ],
////                    Constant::DB_TABLE_GENDER => [
////                        Constant::DB_EXECUTION_PLAN_FIELD => Constant::DB_TABLE_GENDER,
////                        'data' => $genderData,
////                        Constant::DB_EXECUTION_PLAN_DATATYPE => '',
////                        Constant::DB_EXECUTION_PLAN_DATA_FORMAT => '',
////                        'glue' => '',
////                        Constant::DB_EXECUTION_PLAN_DEFAULT => $genderData[0],
////                    ],
////                    'brithday' => [
////                        Constant::DB_EXECUTION_PLAN_FIELD => 'brithday',
////                        'data' => [],
////                        Constant::DB_EXECUTION_PLAN_DATATYPE => 'datetime',
////                        Constant::DB_EXECUTION_PLAN_DATA_FORMAT => 'Y-m-d',
////                        'glue' => '',
////                        Constant::DB_EXECUTION_PLAN_DEFAULT => '',
////                    ],
////                    'name' => [
////                        Constant::DB_EXECUTION_PLAN_FIELD => 'first_name{connection}last_name',
////                        'data' => [],
////                        Constant::DB_EXECUTION_PLAN_DATATYPE => 'string',
////                        Constant::DB_EXECUTION_PLAN_DATA_FORMAT => '',
////                        'glue' => ' ',
////                        Constant::DB_EXECUTION_PLAN_DEFAULT => '',
////                    ],
////                    'region' => [
////                        Constant::DB_EXECUTION_PLAN_FIELD => 'address_home.region{or}address_home.city',
////                        'data' => [],
////                        Constant::DB_EXECUTION_PLAN_DATATYPE => '',
////                        Constant::DB_EXECUTION_PLAN_DATA_FORMAT => '',
////                        'glue' => '',
////                        Constant::DB_EXECUTION_PLAN_DEFAULT => '',
////                    ],
////                    'interest' => [
////                        Constant::DB_EXECUTION_PLAN_FIELD => 'interests.*.interest',
////                        'data' => [],
////                        Constant::DB_EXECUTION_PLAN_DATATYPE => 'string',
////                        Constant::DB_EXECUTION_PLAN_DATA_FORMAT => '',
////                        'glue' => ',',
////                        Constant::DB_EXECUTION_PLAN_DEFAULT => '',
////                    ],
////                ],
////            ],
////            'with' => [
////                'address_home' => [
////                    Constant::DB_EXECUTION_PLAN_SETCONNECTION => true,
////                    Constant::DB_EXECUTION_PLAN_STOREID => 'default_connection_0',
////                    'relation' => 'hasOne',
////                    Constant::DB_EXECUTION_PLAN_DEFAULT => [
////                        Constant::DB_TABLE_CUSTOMER_PRIMARY => Constant::DB_TABLE_CUSTOMER_PRIMARY,
////                    ],
////                    Constant::DB_EXECUTION_PLAN_SELECT => [
////                        Constant::DB_TABLE_CUSTOMER_PRIMARY,
////                        'region',
////                        'city',
////                    ],
////                    Constant::DB_EXECUTION_PLAN_WHERE => [],
////                    Constant::DB_EXECUTION_PLAN_HANDLE_DATA => [],
////                    //'unset' => ['address_home'],
////                ],
////            ],
////            //'sqlDebug' => true,
////        ];
////
////        Arr::set($dbExecutionPlan, 'with.order_data', [
////            Constant::DB_EXECUTION_PLAN_SETCONNECTION => true,
////            Constant::DB_EXECUTION_PLAN_STOREID => 1,
////            'relation' => 'hasOne',
////            Constant::DB_EXECUTION_PLAN_SELECT => [Constant::DB_TABLE_CUSTOMER_PRIMARY, 'orderno'],
////            Constant::DB_EXECUTION_PLAN_DEFAULT => [],
////            Constant::DB_EXECUTION_PLAN_WHERE => [],
////            Constant::DB_EXECUTION_PLAN_HANDLE_DATA => [],
////            //'unset' => [Constant::DB_TABLE_INTERESTS],
////        ]);
//
        $dbExecutionPlan = [
            Constant::DB_EXECUTION_PLAN_PARENT => [
                Constant::DB_EXECUTION_PLAN_SETCONNECTION => true,
                Constant::DB_EXECUTION_PLAN_STOREID => $storeId,
                Constant::DB_EXECUTION_PLAN_BUILDER => null,
                'make' => \App\Services\OrderWarrantyService::getNamespaceClass(),
                'from' => '',
                Constant::DB_EXECUTION_PLAN_SELECT => ['*'],
                Constant::DB_EXECUTION_PLAN_WHERE => [],
                Constant::DB_EXECUTION_PLAN_LIMIT => 1,
                Constant::DB_EXECUTION_PLAN_OFFSET => 0,
                Constant::DB_EXECUTION_PLAN_IS_PAGE => false,
                Constant::DB_EXECUTION_PLAN_PAGINATION => [],
                Constant::DB_EXECUTION_PLAN_HANDLE_DATA => [],
            ],
            'with' => [
                'customer_info' => [
                    Constant::DB_EXECUTION_PLAN_SETCONNECTION => true,
                    Constant::DB_EXECUTION_PLAN_STOREID => 'default_connection_'.$storeId,
                    'relation' => 'hasOne',
                    Constant::DB_EXECUTION_PLAN_DEFAULT => [
                        Constant::DB_TABLE_CUSTOMER_PRIMARY => Constant::DB_TABLE_CUSTOMER_PRIMARY,
                    ],
                    Constant::DB_EXECUTION_PLAN_SELECT => ['*'],
                    Constant::DB_EXECUTION_PLAN_WHERE => [],
                    Constant::DB_EXECUTION_PLAN_HANDLE_DATA => [],
                ],
                'order' => [
                    Constant::DB_EXECUTION_PLAN_SETCONNECTION => false,
                    Constant::DB_EXECUTION_PLAN_STOREID => $storeId,
                    'relation' => 'hasOne',
                    Constant::DB_EXECUTION_PLAN_DEFAULT => [],
                    Constant::DB_EXECUTION_PLAN_SELECT => ['*'],
                    Constant::DB_EXECUTION_PLAN_WHERE => [],
                    Constant::DB_EXECUTION_PLAN_HANDLE_DATA => [],
                ],
            ],
            //'sqlDebug' => true,
        ];

        $dataStructure = 'list';
        $flatten = false;
        $_data = FunctionHelper::getResponseData(null, $dbExecutionPlan, $flatten, false, $dataStructure);

        return Response::json($_data);

//        $storeIds = \App\Services\StoreService::getModel(0)->pluck('id');
//        for($i=0;$i<1000000;$i++){
//            foreach ($storeIds as $storeId) {
//                $data = [
//                    'customer_id'=>$storeId,
//                ];
//                \App\Services\RankService::getModel($storeId)->insert($data);
//            }
//        }
//
//        return Response::json($storeIds);

        dd(\App\Services\StatisticsService::userNumsByCompared('2020-10-01'));

        $ttl = Carbon::parse(FunctionHelper::handleTime('2020-11-18', '-1 day', 'Y-m-d'))->timestamp - Carbon::now()->timestamp;

        dd($configData = \App\Services\StatisticsService::getConfig(1, 'customer_source'));

        $this->testProcessWrite();

        return Response::json(app('swoole')->atomicCount->get());

        /*         * @var \Swoole\Http\Server $swoole */
        $swoole = app('swoole');
        // $swoole->ports：遍历所有Port对象，https://wiki.swoole.com/#/server/properties?id=ports
        $port = $swoole->ports[0]; // 获得`Swoole\Server\Port`对象
        // $fd = 1; // Port中onReceive/onMessage回调的FD
        // $swoole->send($fd, 'Send tcp message from controller to port client');
        // $swoole->push($fd, 'Send websocket message from controller to port client');
        //$swoole = app('swoole');
        //var_dump($swoole->stats());// 单例

        $extWhere = [
            Constant::DICT => [
                Constant::DB_TABLE_DICT_KEY => 'is_show_participation_order',
            ],
            Constant::DICT_STORE => [
                Constant::DB_TABLE_STORE_DICT_KEY => 'is_show_participation_order',
            ],
        ];
        $dd = \App\Services\Platform\OrderService::getConfig(1, Constant::ORDER, $extWhere);
        return Response::json($dd);

        /**
         * 异步的任务队列:https://learnku.com/articles/8050/laravels-accelerate-laravellumen-based-on-swoole-take-you-fly#%E4%BD%BF%E7%94%A8swooletable
         * 投递任务
         */
        // 实例化TestTask并通过deliver投递，此操作是异步的，投递后立即返回，由Task进程继续处理TestTask中的handle逻辑
//        $task = new \App\Tasks\TestTask('task data');
//        // $task->delay(3); // 延迟3秒投递任务
//        // $task->setTries(3); // 出现异常时，累计尝试3次
        $ret = \Hhxsv5\LaravelS\Swoole\Task\Task::deliver($task);
//        var_dump($ret); // 判断是否投递成功
//        return Response::json(config('laravels.inotify_reload.watch_path') . '====99996666');


        /**
         * 触发事件
         */
        // 实例化TestEvent并通过fire触发，此操作是异步的，触发后立即返回，由Task进程继续处理监听器中的handle逻辑
//        $event = new \App\Events\TestEvent('event data');
//        // $event->delay(10); // 延迟10秒触发
//        // $event->setTries(3); // 出现异常时，累计尝试3次
//        $success = \Hhxsv5\LaravelS\Swoole\Task\Event::fire($event);
//        var_dump(__METHOD__, $success); // 判断是否触发成功
        //

        //$this->push();
//        $time = '+1 month';
//        $dateFormat = 'Y-m-d H:i:s';
//        $nowTime = Carbon::now()->toDateTimeString();
//        $realThingWhere = [
//            Constant::DB_TABLE_PRIMARY => $request->input(Constant::DB_TABLE_PRIMARY), //活动id
////            Constant::DB_TABLE_ACT_ID => 29, //活动id
////            Constant::DB_TABLE_CUSTOMER_PRIMARY => 532556, //账号id
////            'is_participation_award' => 0, //非参与奖
////            'prize_type' => 3, //奖品类型 0:其他 1:礼品卡 2:coupon 3:实物 5:活动积分
////            [[Constant::DB_TABLE_CREATED_AT, '>', Carbon::parse(FunctionHelper::handleTime($nowTime, str_replace('+', '-', $time), $dateFormat))->toDateTimeString()]]
//        ];
//
//        $data = [
//            Constant::DB_TABLE_UPDATED_AT => $nowTime
//        ];
//        $query = \App\Services\ActivityWinningService::getModel($request->input(Constant::DB_TABLE_STORE_ID))
//                ->where($realThingWhere);
//
//        for($i=0;$i<10;$i++){
//            $query->where(['id' => $i]);
//        }
//
//
//        $isWinRealThing = $query->get();
        //$isWinRealThing = \App\Services\ActivityWinningService::updateOrCreate($request->input(Constant::DB_TABLE_STORE_ID), $realThingWhere, $data);
//dump($isWinRealThing);
        //$isWinRealThing = \App\Services\ActivityWinningService::existsOrFirst(1, '', $realThingWhere, true);

        return Response::json(1);


        dd(\App\Services\Store\Localhost\Orders\Order::test(1, 'Localhost', '', 'asin_test_8998'));
        dd(\App\Services\Store\Localhost\Products\Product::getProductUniqueId(1, 'Localhost', '', 'asin_test'));


        $requestData = $request->all();

        $actionData = [
            Constant::SERVICE_KEY => \App\Services\ActivityService::getNamespaceClass(),
            Constant::METHOD_KEY => 'decrement',
            Constant::PARAMETERS_KEY => [],
            Constant::REQUEST_DATA_KEY => $requestData,
        ];
        $lotteryData = ActivityService::handleLimit(1, 23, 109633, $actionData);
        dump($lotteryData);

        $actionData = [
            Constant::SERVICE_KEY => \App\Services\ActivityService::getNamespaceClass(),
            Constant::METHOD_KEY => 'get',
            Constant::PARAMETERS_KEY => [],
            Constant::REQUEST_DATA_KEY => $requestData,
        ];

        $lotteryData = ActivityService::handleLimit(1, 23, 109633, $actionData);
        dd($lotteryData);

        $nowTime = Carbon::now()->toDateTimeString();
        dd(Carbon::parse(\App\Util\FunctionHelper::handleTime($nowTime, '+2 month', 'Y-m-01 00:00:00'))->timestamp - Carbon::now()->timestamp);

        phpinfo();
        exit;

        dd(\App\Services\OrderWarrantyService::bind(1, 'Jmiy_cen@patazon.net', '113-0704848-0752265', 'US', Constant::DB_TABLE_PLATFORM, [Constant::DB_TABLE_PLATFORM => 'amazon']));

//        $parameters = [
//            'orderno'=>'026-0001496-9271579',
//            'order_country'=>'US',
//        ];
//        $orderData = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_AMAZON, 'Order', 'getOrderItem', [1, $parameters]);
//        //dd($orderData);
//        return Response::json($orderData, 1, '');

        $request = [
            'amazon_order_id' => '026-0001496-9271579',
                //'country'=>'',
        ]; //
        $rs = $this->sendApiRequestByCurl('https://xcpre.patozon.net/api/external/getXcOrder', $request);
        dd($rs);

//        dd(PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_AMAZON, 'Order', 'getOrderUniqueId', [1, 'US', Constant::PLATFORM_SERVICE_AMAZON, '111-6585829-5405047']));
//
//        $orderData = PlatformServiceManager::handle($platform, 'Order', 'getOrderUniqueId', [$storeId, $orderCountry, $platform, $orderId]);

        dd(\App\Services\OrderWarrantyService::bind(1, 'Jmiy_cen@patazon.net', '111-6585829-5405047', 'US', Constant::DB_TABLE_PLATFORM, Constant::PARAMETER_ARRAY_DEFAULT));

        $requestData = [
            Constant::DB_TABLE_CUSTOMER_PRIMARY => 109633,
            Constant::DB_TABLE_STAR => 5,
            Constant::DB_TABLE_REVIEW_LINK => 'https://ddd.com.cn',
            Constant::DB_TABLE_ACCOUNT => 'Jmiy_cen@patazon.net',
        ];
        dd(\App\Services\OrderReviewService::input(1, '111-6585829-5405047', $requestData));

        //$rewardAsinDbConfig = \App\Services\RewardService::getOrderReviewReward(1, '112-7743747-4969825');
        $rewardAsinDbConfig = \App\Services\RewardService::handleOrderReviewReward(1, 56, 1, '112-7743747-4969825', 'Jmiy_cen@patazon.net');
        $rewardAsinTableAlias = data_get($rewardAsinDbConfig, 'table_alias');
        dd($rewardAsinDbConfig);

        dd(\App\Services\Platform\OrderItemService::getNamespaceClass());

        dd(\App\Services\Platform\OrderItemService::getDbConfig(1));

        $model = \App\Services\Platform\OrderService::getModel(1);
        dd($model, $model->getConnectionName(), config('database.connections.' . $model->getConnectionName(), config('database.connections.mysql')), $model->getTable());

        dd(\App\Services\RewardService::info(1, ['reward_id' => 27]));


//        $orderData = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_AMAZON, 'Order', 'getOrderItem', [1, ['orderno'=>'202-0059748-2522777','order_country'=>'uk']]);
//        dd($orderData);
//
//        $order = '202-0059748-2522777';
//        $country = 'us';
//        dd(\App\Services\Erp\ErpAmazonService::getOrderItem($order, $country, 1));
//
//
//
//        dd(\App\Services\OrderReviewService::audit(6, [1], 4, 'reviewer', 'remarks'));

        $dd = [
            Constant::DB_TABLE_STORE_ID => 1,
//            Constant::START_TIME => '2020-09-19 00:00:00',
//            Constant::DB_TABLE_END_TIME => '2020-10-19 00:00:00',
                //'stat_type' => 'week',
//            'one_category_code' => ['BH'],
//            'sku' => 'sku',
//            'asin' => ['asin'],
//            'country' => ['us'],
        ];

        dd(\App\Services\OrderReviewService::getListData($dd));

        dd(\App\Services\OrderReviewService::statList($dd));

        dd(\App\Services\OrderReviewService::getListData([Constant::DB_TABLE_STORE_ID => 1]));

        $extWhere = [
            Constant::DICT => [
            //Constant::DB_TABLE_DICT_KEY => ['is_force_release_order_lock', 'release_time', 'each_pull_time', 'ttl'],
            ],
            Constant::DICT_STORE => [
                Constant::DB_TABLE_STORE_DICT_KEY => [Constant::CONFIG_KEY_WARRANTY_DATE_FORMAT, Constant::WARRANTY_DATE],
            ],
        ];

        $configType = [
            'audit_status',
            Constant::DB_TABLE_ORDER_STATUS,
            Constant::ORDER,
        ];
        dd(\App\Services\OrderReviewService::getConfig(1, $configType, $extWhere));

        $auditStatusData = data_get($data, 'srcParameters.0.auditStatusData', DictService::getListByType('audit_status', Constant::DB_TABLE_DICT_KEY, Constant::DB_TABLE_DICT_VALUE)); //审核状态 -1:未提交审核 0:未审核 1:已通过 2:未通过 3:其他
        $orderStatusData = data_get($data, 'srcParameters.0.orderStatusData', DictService::getListByType(Constant::DB_TABLE_ORDER_STATUS, Constant::DB_TABLE_DICT_KEY, Constant::DB_TABLE_DICT_VALUE)); //订单状态 -1:匹配中 0:未支付 1:已经支付 2:取消 默认:-1
        $orderConfig = data_get($data, 'srcParameters.0.orderConfig', DictStoreService::getByTypeAndKey($storeId, Constant::ORDER, [Constant::CONFIG_KEY_WARRANTY_DATE_FORMAT, Constant::WARRANTY_DATE]));

        dd(trim('  0indescent0@gmail.com
'));


        dd('0indescent0@gmail.com
', trim(str_replace('
', '', '0indescent0@gmail.com
')));

        dd(\App\Services\Platform\OrderService::pullOrder(1, Constant::PLATFORM_SERVICE_AMAZON, '202-8833383-3045136', false, '', 119221));

        dd(\App\Services\OrderReviewService::input(1, '205-1616291-6649142', [Constant::DB_TABLE_STAR => 5]));

        $categoryData = [
            [
                'category_code' => 'category_code',
                'category_name' => 'category_name',
                'level' => 1,
            ],
            [
                'category_code' => 'category_code1',
                'category_name' => 'category_name1',
                'level' => 1,
            ],
        ];

        dd(\App\Services\RewardCategoryService::handle(1, 1, $categoryData));

//        $requestData = [
//            'act_id' => 33,
//            'store_id' => 3,
//        ];
//        dd(\App\Services\ActivityApplyService::getActApplyList($requestData));

        dd(\App\Services\OrderReviewService::audit(6, [1], 4, 'reviewer', 'remarks'));

        dd(\App\Services\Platform\OrderService::pullOrder(1, Constant::PLATFORM_SERVICE_AMAZON, '702-4534434-5963469', false, 'CA', 271376));

        $storeId = 1;
        $orderModel = \App\Services\OrderWarrantyService::getModel(1, '');
        $orderModel->from('customer_order as co')
                ->leftJoin(DB::raw('`ptxcrm`.`crm_platform_orders` as crm_po'), 'po.orderno', '=', 'co.orderno')
                ->select(['co.' . Constant::DB_TABLE_PRIMARY, 'co.' . Constant::DB_TABLE_ORDER_NO, 'co.' . Constant::DB_TABLE_COUNTRY])
                ->where([
                    'co.ext_type' => 'Order',
                    'po.id' => null,
                ])
                ->orderBy('co.id', 'DESC')
                ->chunk(1, function ($data) use($storeId) {
                    foreach ($data as $item) {
                        $_parameters = [$storeId, Constant::PLATFORM_SERVICE_AMAZON, data_get($item, Constant::DB_TABLE_ORDER_NO), false, data_get($item, Constant::DB_TABLE_COUNTRY), data_get($item, Constant::DB_TABLE_PRIMARY, 0)];
                        dump($_parameters);
                        FunctionHelper::pushQueue(FunctionHelper::getJobData(\App\Services\Platform\OrderService::getNamespaceClass(), 'pullOrder', $_parameters), null, '{amazon-order-bind}'); //把任务加入消息队列
                    }
                    return false;
                });

        dd(5555);

        $dd = \App\Services\Platform\OrderService::getOrderData('114-6368351-8939418', '', Constant::PLATFORM_SERVICE_AMAZON, 1); //订单状态 -1:Matching 0:Pending 1:Shipped 2:Canceled 3:Failure 默认:-1
        dd($dd);

        $dd = \App\Services\Platform\OrderService::getOrderDetails(1, '114-6368351-8939418', '', Constant::PLATFORM_SERVICE_AMAZON); //订单状态 -1:Matching 0:Pending 1:Shipped 2:Canceled 3:Failure 默认:-1
        dd($dd);
//        dd(\App\Services\OrdersService::getOrderData('114-6368351-8939418', '', Constant::PLATFORM_SERVICE_AMAZON, 1, true));
//        dd(\App\Services\Erp\ErpAmazonService::getFctOrderItem(1, '114-6368351-8939418'));
//
//        dd(\App\Services\OrdersService::getOrderData('114-6368351-8939418', '', Constant::PLATFORM_AMAZON, 1, true));
//        $config = [
////            'host' => '192.168.5.134',
////            'port' => 8123,
////            'username' => 'default',
////            'password' => 'fxnFtiZT',
//
//            'port' => '8123',
//            'database' => 'ptx_db',
//            'username' => 'ptx',
//            'password' => 'ptx123',
//            'host' => '172.16.6.207',
//
////            'port' => '53456',
////            'database' => 'ptx_db',
////            'username' => 'ptx',
////            'password' => 'ptx123',
////            'host' => '14.21.71.212'
//        ];
//
//        $_nowTime = microtime(true);
//        $db = new \ClickHouseDB\Client($config);
//        $db->database('ptx_db');
//        $db->setTimeout(60);       // 10 seconds
//        $db->setConnectTimeOut(5); // 5 seconds
//        //dump($db->showTables());
//
//        $dd = $db->select("SELECT * FROM ptx_yw.ads_or_xc_order_item_report WHERE amazon_order_id='104-4444717-6843425' LIMIT 10")->rows();
//        $xcTime = (number_format(microtime(true) - $_nowTime, 8, '.', '') * 1000) . ' ms';
//        dd($xcTime, $dd);
//        $platform = FunctionHelper::getUniqueId(Constant::PLATFORM_SERVICE_AMAZON);
//                $where = [
//                    Constant::DB_TABLE_PLATFORM => $platform, //平台
//                ];
//
//                $maxUpdatedAt = \App\Services\Platform\ProductService::getModel(0)->buildWhere($where)->max(Constant::DB_TABLE_PLATFORM_UPDATED_AT);
//        dd($maxUpdatedAt,FunctionHelper::handleTime($maxUpdatedAt));
//
//        $parameters = [];
//
//        $dd=\App\Services\Platform\ProductService::handlePull(1, Constant::PLATFORM_SERVICE_AMAZON, $parameters);
//        dd($dd);
//
//        $dd = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_AMAZON, 'Product', 'getProduct', [0, $parameters]);
//        dd($dd);

        $parameters = [
            'orderno' => '114-6368351-8939418',
                //'order_country' => 'US',
        ];

        dd(\App\Services\Platform\OrderService::isExists(1, '114-6368351-8939418'));

        dd(\App\Services\Platform\OrderService::handlePull(1, Constant::PLATFORM_SERVICE_AMAZON, [1, $parameters]));

        $categoryData = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_AMAZON, ['Orders', 'Order'], 'getOrder', [0, $parameters]);
        dd($categoryData);

        dd(\App\Services\Platform\CountryService::handlePull(0, Constant::PLATFORM_SERVICE_AMAZON, []));

        $categoryData = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_AMAZON, ['Erp', 'Commons', 'Country'], 'getCountry', [0, []]);
        dd($categoryData);

        $categoryData = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_AMAZON, ['Erp', 'Products', 'Product'], 'getProduct', [0, []]);
        dd($categoryData);
//
//        dd(\App\Services\Platform\ProductCategoryService::handlePull(0, Constant::PLATFORM_SERVICE_AMAZON, []));
        //dd(\App\Services\Platform\RateService::handlePull('cn', Constant::PLATFORM_SERVICE_AMAZON, []));
        $categoryData = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_AMAZON, ['Erp', 'Products', 'Category'], 'getCategory', [0, []]);
        dd($categoryData);
        foreach ($categoryData as $data) {
            $_data = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_AMAZON, ['Erp', 'Products', 'Category'], 'getCategoryData', ['cn', Constant::PLATFORM_SERVICE_AMAZON, $data]);
            dd($_data);
        }
        dd(555);


        $exists = \App\Services\ProductService::getModel(1)->select([Constant::DB_TABLE_PRIMARY])->withTrashed()->get();
        if ($exists && $exists->isNotEmpty()) {

            foreach ($exists as $item) {
                $id = data_get($item, Constant::DB_TABLE_PRIMARY, '-1');
                var_dump($id);
            }
        }

        $ret = \App\Services\ProductService::insert(1, ['credit' => 2]);
        var_dump($ret);
        dd('220000000000000');

        $data = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_SHOPIFY, 'Product', 'count', [1]);
        dd($data);

        $html = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_SHOPIFY, 'Customer', 'count', [8]);
        dd($html);

        dd(json_decode('[{"key":"country","value":["CA","US"]},{"key":"type","value":"2"},{"key":"menu","value":"2"},{"key":"market_credit"}]', true));

        dd(\App\Services\ExcelService::sendEmail(1, '导出文件', '请下载文件：https://brand.patozon.net/public/file/download/excel/20200921041147_6164.xlsx'));

        $header = [
            '邮箱' => Constant::DB_TABLE_ACCOUNT,
            '用户名' => Constant::DB_TABLE_NAME,
            '用户注册ip' => Constant::DB_TABLE_IP,
            '用户国家' => Constant::DB_TABLE_COUNTRY,
            '积分明细' => Constant::DB_TABLE_VALUE,
            '积分明细方式' => 'action',
            "email" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "email",
            "address1" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "address1",
            "address2" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "address2",
            "phone" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "phone",
            "city" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "city",
            "zip" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "zip",
            "province" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "province",
            "country" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "country",
            "name" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "name",
            "company" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "company",
//            "latitude" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "latitude",
//            "longitude" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "longitude",
//            "country_code" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "country_code",
//            "province_code" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "province_code",
            '备注' => 'remark',
            '积分变动时间' => 'ctime',
            'distinctField' => [
                'primaryKey' => Constant::DB_TABLE_PRIMARY,
                'primaryValueKey' => Constant::DB_TABLE_PRIMARY,
                'select' => ['cl.' . Constant::DB_TABLE_PRIMARY]
            ],
        ];
        \App\Services\ExcelService::createExcel($header, '', '', [], '', []);
        dd(899);

        $request->offsetSet(Constant::DB_TABLE_STORE_ID, 1);
        $request->offsetSet('source', 'api');
        //$request->offsetSet('account_country', 'us');
        dd(\App\Services\CreditService::getListData($request->all()));

        $storeId = 1;
        $actionData = [
            0 => 'creditExchange',
            1 => [
                0 => 'NonPhysicalExchange',
                1 => 'NonPhysicalExchange',
                2 => 'NonPhysicalExchange',
                3 => 'PhysicalProductExchange',
                5 => 'NonPhysicalExchange',
            ],
        ];
        $dd = data_get($actionData, $storeId . Constant::LINKER . 9, data_get($actionData, $storeId . Constant::LINKER . '0', data_get($actionData, $storeId, data_get($actionData, 0, 'creditExchange'))));
        dd($dd);

        $type = 'credit_action';
        $orderby = null;
        $country = null;
        $extWhere = [
            Constant::DICT => [
            //Constant::DB_TABLE_DICT_KEY => ['is_force_release_order_lock', 'release_time', 'each_pull_time', 'ttl'],
            ],
            Constant::DICT_STORE => [],
        ];
        $select = [
            Constant::DICT => [
                Constant::DB_TABLE_TYPE, Constant::DB_TABLE_DICT_KEY, Constant::DB_TABLE_DICT_VALUE,
            ],
            Constant::DICT_STORE => [
                Constant::DB_TABLE_TYPE, Constant::DB_TABLE_STORE_DICT_KEY, Constant::DB_TABLE_STORE_DICT_VALUE,
            ],
        ];

        $storeId = 1;
        $dd = \App\Services\DictService::getDistData($storeId, $type, Constant::DB_TABLE_STORE_DICT_KEY, Constant::DB_TABLE_STORE_DICT_VALUE, Constant::DB_TABLE_DICT_KEY, Constant::DB_TABLE_DICT_VALUE, $orderby, $country, $extWhere, $select); //模板类型

        $type = 'credit_action';
        $orderby = 'sorts asc';
        $keyField = 'conf_key';
        $valueField = 'conf_value';
        $actionData = \App\Services\DictStoreService::getListByType($storeId, $type, $orderby, $keyField, $valueField);
        dd($actionData, $dd);

        dd(md5('QNSY%$#24%.159'));

        $order = '114-6368351-8939418';
        $country = 'us';
        dd(\App\Services\Erp\ErpAmazonService::getOrderItem($order, $country, 1));

        dd($storeId = \App\Services\Store\Shopify\BaseService::castToString(1));

        dd(\App\Services\Store\Shopify\BaseService::getProductPriceData('B00PC29ETE', 'MCM3-PS-1', 'us'));


        $variantItems = [
            [
                Constant::DB_TABLE_PRODUCT_ID => 1, //商品主键id
                Constant::DB_TABLE_PRIMARY => 1, //商品变种主键id
                "variant_id" => 32825741181003,
                "quantity" => 1,
            ],
            [
                Constant::DB_TABLE_PRODUCT_ID => 2, //商品主键id
                Constant::DB_TABLE_PRIMARY => 1, //商品变种主键id
                "variant_id" => 32825741181003,
                "quantity" => 2,
            ],
            [
                Constant::DB_TABLE_PRODUCT_ID => 1, //商品主键id
                Constant::DB_TABLE_PRIMARY => 1, //商品变种主键id
                "variant_id" => 32825741181003,
                "quantity" => 2,
            ],
        ];

        $orderItems = [];
        $pointStoreProductQuantities = [];
        foreach ($variantItems as $index => $item) {
            if (empty($item[Constant::DB_TABLE_PRIMARY]) || empty($item[Constant::DB_TABLE_PRODUCT_ID]) || empty($item[Constant::VARIANT_ID]) || empty($item[Constant::DB_TABLE_QUANTITY])) {
                return Response::getDefaultResponseData(9999999999);
            }

            $orderItems[] = Arr::only($item, [Constant::VARIANT_ID, Constant::DB_TABLE_QUANTITY]);

            if (!isset($pointStoreProductQuantities[$item[Constant::DB_TABLE_PRODUCT_ID]][Constant::DB_TABLE_QUANTITY])) {
                $pointStoreProductQuantities[$item[Constant::DB_TABLE_PRODUCT_ID]][Constant::DB_TABLE_QUANTITY] = $item[Constant::DB_TABLE_QUANTITY];
            } else {
                $pointStoreProductQuantities[$item[Constant::DB_TABLE_PRODUCT_ID]][Constant::DB_TABLE_QUANTITY] += $item[Constant::DB_TABLE_QUANTITY];
            }
        }

        $variantIds = array_unique(array_filter(array_column($variantItems, Constant::DB_TABLE_PRIMARY))); //商品变种主键id
        $pointStoreProductPrimaryIds = array_unique(array_filter(array_column($variantItems, Constant::DB_TABLE_PRODUCT_ID))); //商品变种主键id

        $productVariantCount = \App\Services\Platform\ProductVariantService::existsOrFirst(1, '', [Constant::DB_TABLE_PRIMARY => $variantIds]); //通过商品变种主键id获取商品
        dd($productVariantCount, count($variantIds));
        if (count($variantIds) != $productVariantCount) {//商品不存在
            return Response::getDefaultResponseData(60006);
        }

        //获取商品
        $products = \App\Services\PointStoreService::getPointStoreProducts(1, [1000000]);
        //dd($products->isEmpty(),$products->count(),count($pointStoreProductPrimaryIds));
        //商品不存在
        if ($products->isEmpty() || $products->count() != count($pointStoreProductPrimaryIds)) {//商品不存在
            return Response::getDefaultResponseData(60006);
        }

//        dd(Arr::only($variantItems, ['*'.Constant::VARIANT_ID,'*'.Constant::DB_TABLE_QUANTITY]));
//
//        //$variantItems = array_column($variantItems, [Constant::VARIANT_ID,Constant::DB_TABLE_QUANTITY]);
//        $variantItems = collect($variantItems)->only([Constant::VARIANT_ID,Constant::DB_TABLE_QUANTITY]);

        $orderItems = [];
        foreach ($variantItems as $index => $item) {
            if (empty($item[Constant::DB_TABLE_PRIMARY]) || empty($item[Constant::DB_TABLE_PRODUCT_ID]) || empty($item[Constant::VARIANT_ID]) || empty($item[Constant::DB_TABLE_QUANTITY])) {
                return Response::getDefaultResponseData(9999999999);
            }

            $orderItems[] = Arr::only($item, [Constant::VARIANT_ID, Constant::DB_TABLE_QUANTITY]);
        }
        dd($variantItems, $orderItems);

        $variantQuantities = array_column($variantItems, NULL, Constant::DB_TABLE_PRIMARY); //以商品变种主键id为索引的商品数据
        dd($variantQuantities, array_unique(array_filter(data_get($variantItems, '*.' . Constant::DB_TABLE_PRIMARY, [])))); //商品变种主键id);

        dd(\App\Services\ProductService::info(1, 0, 1));

        dd(\App\Services\ActivityService::getActData(1, 22));


        ini_set('memory_limit', '2048M');
        $typeData = [
            \Vtiful\Kernel\Excel::TYPE_STRING,
            \Vtiful\Kernel\Excel::TYPE_STRING,
            \Vtiful\Kernel\Excel::TYPE_STRING,
            \Vtiful\Kernel\Excel::TYPE_STRING,
            \Vtiful\Kernel\Excel::TYPE_TIMESTAMP,
            \Vtiful\Kernel\Excel::TYPE_TIMESTAMP,
        ];

        $filename = storage_path('logs/coupon_template (1)22222.xlsx'); //lottery_prize.xlsx activity_products_helped.xlsx activity_vote_prize_products.xlsx
        $data = \App\Services\ExcelService::parseExcelFile($filename, $typeData); //$file->getRealPath()
        if (isset($data[0])) {
            unset($data[0]); //删除excel表中的表头数据
        }

        $storeId = 5;
        $dataBatch = array_chunk($data, 2000);
        $service = \App\Services\CouponService::getNamespaceClass();
        $method = 'addBatch';
        foreach ($dataBatch as $_data) {
            $parameters = [$storeId, $_data];
            FunctionHelper::pushQueue(FunctionHelper::getJobData($service, $method, $parameters), null, '{data-import}');
        }
        dd(666666);

//21:06:05
//        $checkExistsRet = CouponService::checkBatchExists($storeId, $tableData);
//        if ($checkExistsRet['code'] != 1) {
//            return Response::json([], 10021, $checkExistsRet['msg']);
//        }
        //$retData = \App\Services\CouponService::addBatch(5, $tableData);
        dd(555);



        //$request->offsetSet('social_media', 'INS');//FB, TW， INS
        dd(\App\Services\ActionLogService::login(1, 1, 0, 6, $request->all()));

        $selectHandle = []; //['unique_id'];
        $orderData = \App\Services\Platform\OrderService::updateOrCreate(1, ['id' => 10000], ['unique_id' => 1000], '', FunctionHelper::getDbBeforeHandle([], [], [], $selectHandle)); //

        dd($orderData, data_get($orderData, Constant::RESPONSE_DATA_KEY . '.' . Constant::DB_TABLE_UNIQUE_ID));

//        $rs = null;
//        dd(data_set($rs, Constant::DB_OPERATION, Constant::DB_OPERATION_SELECT),$rs);

        $model = \App\Services\Platform\OrderService::getModel(1);
        $where = [
            'a' => 5,
            'b' => [1, 2],
            [['e', '=', 5], ['f', '>', 6], ['h', '=', DB::raw('h1')]],
            'c=3',
            Constant::DB_TABLE_CREDIT => DB::raw('credit+1'),
            DB::raw('d=12')
        ];
        $key = serialize(Arr::collapse([
                    [
                        $model->getConnectionName(),
                        $model->getTable(),
                    ], $where
        ]));
        //$key = md5($key);
        dd(unserialize($key));

        $storeId = 2;
        $where = [
            'o.unique_id' => 0,
        ];
        $data = \App\Services\OrdersService::getModel($storeId)
                ->from('orders as o')
                ->buildWhere($where)
                ->select(['o.orderno', 'o.country'])
                ->orderBy(Constant::DB_TABLE_PRIMARY, Constant::DB_EXECUTION_PLAN_ORDER_ASC)
                ->limit(100)
                ->get()
        ;
        dd($data);

        $parameters = [1, Constant::PLATFORM_SERVICE_SHOPIFY, 1];

        FunctionHelper::getUniqueId(...$parameters);



        //dd(pow(2, 64)-1);
        dd(\App\Services\Platform\OrderService::getModel(1)->insert(['unique_id' => pow(2, 64) - 1]));

        dd(\App\Services\Store\Shopify\Fulfillments\Fulfillment::getCustomClassName(), \App\Services\ActivityWinningService::getCustomClassName(), \App\Services\ActivityWinningService::getModelAlias());

        dd(FunctionHelper::handleAccount('outlook.com'));

        $parameters = [
            Constant::PLATFORM_SERVICE_SHOPIFY,
            ['value' => FunctionHelper::myHash(Constant::PLATFORM_SERVICE_SHOPIFY)],
        ];
        dump(FunctionHelper::getUniqueId(...$parameters));
        dump(FunctionHelper::getUniqueId(Constant::PLATFORM_SERVICE_SHOPIFY));

        $storeId = '1';
        $platform = Constant::PLATFORM_SERVICE_SHOPIFY;
        $orderId = 2281761669238;
        dump(FunctionHelper::repairUniqueId($storeId, $platform, $orderId));
        dd(FunctionHelper::getUniqueId($storeId, $platform, $orderId));


        //dd(PHP_INT_MAX*2,pow(2,64)-1);
        //dd(md5('dd',true));
        dump(FunctionHelper::getUniqueId('CA', Constant::PLATFORM_SERVICE_AMAZON, '702-4927085-1756202', ['value' => 3256]));
        dump(FunctionHelper::myHash('CA', Constant::PLATFORM_SERVICE_AMAZON, '702-4927085-1756202'));
        dump(FunctionHelper::myHash());
        dd(2222);
//
//        dd(PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_SHOPIFY, 'Order', 'count', [1]));
//        for ($i=1; $i < 100000000; $i++) {
//            \App\Services\Platform\OrderService::getModel(1)->insert(['unique_id' => FunctionHelper::getUniqueId('CA', Constant::PLATFORM_SERVICE_AMAZON, $i)]);
//        }
//        dd(111);
//        dd(FunctionHelper::getUniqueId('CA', Constant::PLATFORM_SERVICE_AMAZON, '702-4927085-1756202'), FunctionHelper::getUniqueId('IT', Constant::PLATFORM_SERVICE_AMAZON, '403-7666944-6748362'));
        dump(\App\Services\Platform\OrderService::getModel(1)->insert(['unique_id' => FunctionHelper::getUniqueId('CA', Constant::PLATFORM_SERVICE_AMAZON, '702-4927085-1756202')]));
        dump(\App\Services\Platform\OrderService::getModel(1)->insert(['unique_id' => FunctionHelper::getUniqueId('IT', Constant::PLATFORM_SERVICE_AMAZON, '403-7666944-6748362')]));
        dd(565);
        $where = [
            'o.unique_id' => 0,
        ];
        $data = \App\Services\OrdersService::getModel(1)
                ->from('orders as o')
                ->buildWhere($where)
                ->select(['o.orderno', 'o.country'])
                ->limit(100)
                ->get()
        ;
        dd($data);

        $request->offsetSet('app_env', 'sandbox');
        $storeId = 2;
//        $ownerId = 2198749478964;
//        $ownerResource = 'order';//12162822045748
        //$html1 = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_SHOPIFY, 'Metafield', 'createMetafield', [$storeId, $ownerId, $ownerResource]);

        $ownerId = 3047836876852;
        $ownerResource = 'customer';
        //$html = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_SHOPIFY, 'Metafield', 'createMetafield', [$storeId, $ownerId, $ownerResource]);
        //$html = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_SHOPIFY, 'Metafield', 'getMetafield', [$storeId, $ownerId, $ownerResource]);
        $html = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_SHOPIFY, 'Customer', 'createMetafield', [$storeId, $ownerId, 'test_key', 98]);
        $html = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_SHOPIFY, 'Customer', 'getMetafield', [$storeId, $ownerId]);
        dd($html); //, $html

        $requestData = $request->all();

        data_set($requestData, 'store_id', 3);
        data_set($requestData, 'is_export', 1);
        data_set($requestData, 'act_id', 33);

        $requestData['deviceTypeData'] = FunctionHelper::getDeviceType($this->storeId);
        $requestData['isRobotData'] = FunctionHelper::getWhetherData(null);
        unset($requestData['country']);

        dd(\App\Services\ActivityApplyService::getActApplyList($requestData));


        dd(\App\Services\ReportLogService::report($request->all()));

        dd(\Illuminate\Support\Str::studly('client_id'));

        $dd = \App\Services\Store\Shopify\Customers\Customer::customerQuery(1, '', 'wexavt28953@chacuo.net');
        dd($dd);

        dd(\App\Services\OrdersService::repair());

        $service = 'service';
        $handleCacheData = FunctionHelper::getJobData($service, 'lock', ['cacheKey'], null, [
                    'serialHandle' => [
                        FunctionHelper::getJobData($service, 'get', []),
                    ]
        ]);
        dd($handleCacheData);


        dd(\App\Services\OrdersService::repair());

        dd(__METHOD__, [1, 2, 1]);

        dd(\App\Services\StatisticsService::userNumsByField($field = Constant::DB_TABLE_BRITHDAY));

        throw new \Exception('89899', 8989);

        $code = 30008;
        $msg = Response::getResponseMsg(2, $code);
        dd($msg);

        dd($activityConfigData = \App\Services\ActivityService::getActivityConfigData(3, 1, Constant::DB_TABLE_EMAIL, ['replyto_address', 'replyto_name']));

        //dd(\App\Services\OrdersService::getOrderPullTimePeriod());

        $extWhere = [
                //Constant::DB_TABLE_DICT_KEY => ['is_force_release_order_lock', 'release_time', 'each_pull_time', 'ttl'],
        ];
        $dictData = \App\Services\OrdersService::getPullOrderConfig(['pull_order', Constant::DB_TABLE_ORDER_STATUS], $extWhere);
        data_set($dictData, Constant::DB_TABLE_ORDER_STATUS, collect(data_get($dictData, Constant::DB_TABLE_ORDER_STATUS, []))->flip());

        $orderStatusData = \App\Services\OrdersService::getPullOrderConfig(Constant::DB_TABLE_ORDER_STATUS);

        $_orderStatusData = \App\Services\DictService::getListByType(Constant::DB_TABLE_ORDER_STATUS, 'dict_key', 'dict_value'); //订单状态 -1:Matching 0:Pending 1:Shipped 2:Canceled 3:Failure 默认:-1

        dd($orderStatusData, $_orderStatusData);

        $tag = 'test';

        $handleCacheData = [
            'service' => '',
            'method' => 'put',
            'parameters' => [
                'test-dd', 1, 864000
            ]
        ];
        \App\Services\OrdersService::handleCache($tag, $handleCacheData);

        $handleCacheData = [
            'service' => '',
            'method' => 'get',
            'parameters' => [
                'test-dd'
            ]
        ];
        $has = \App\Services\OrdersService::handleCache($tag, $handleCacheData);
        dd($has, $has === '1');


        //$type = ['order', 'handle_order', 'pull_order'];
        $type = ['credit_action'];
        $keyField = 'conf_key';
        $valueField = 'conf_value';
        $distKeyField = Constant::DB_TABLE_DICT_KEY;
        $distValueField = Constant::DB_TABLE_DICT_VALUE;
        $orderby = null;
        $country = null;
        $extWhere = [
            Constant::DICT => [
            //Constant::DB_TABLE_DICT_KEY => ['is_force_release_order_lock', 'release_time', 'each_pull_time', 'ttl'],
            ],
            Constant::DICT_STORE => [],
        ];
        $select = [
            Constant::DICT => [
                Constant::DB_TABLE_TYPE, Constant::DB_TABLE_DICT_KEY, Constant::DB_TABLE_DICT_VALUE,
            ],
            Constant::DICT_STORE => [
                Constant::DB_TABLE_TYPE, 'conf_key', 'conf_value',
            ],
        ];

        $dd = \App\Services\DictService::getDistData(3, $type, $keyField, $valueField, $distKeyField, $distValueField, $orderby, $country, $extWhere, $select); //模板类型
        dd($dd);


        $dd = \App\Services\DictService::getDistData(
                        0, ['pull_order'], //'handle_order',
                        ['is_force_release_order_lock', 'release_time', 'each_pull_time', 'ttl'], 'conf_value', ['is_force_release_order_lock', 'release_time', 'each_pull_time', 'ttl'], 'dict_value', null
        );
        dd($dd);

        $requestData = [
            Constant::DB_TABLE_STORE_ID => 3,
            Constant::DB_TABLE_CUSTOMER_PRIMARY => 364679,
        ];
        $data = \App\Services\OrderWarrantyService::getReviewlist($requestData);
        dd($data);

        $storeId = 1;
        $where = [
            [
                [Constant::DB_TABLE_STORE_ID, '=', $storeId],
                [Constant::DB_TABLE_TYPE, '=', Constant::DB_TABLE_PLATFORM],
                [Constant::DB_TABLE_PLATFORM, '=', Constant::PLATFORM_AMAZON],
                [Constant::DB_TABLE_ORDER_STATUS, '>', \App\Services\OrderWarrantyService::$initOrderStatus],
            ],
            Constant::WARRANTY_AT => ['2019-01-01 00:00:00', '1000-01-01 00:00:00'],
        ];
        $service = \App\Services\OrderWarrantyService::getNamespaceClass();
        \App\Services\OrderWarrantyService::getModel($storeId, '')->buildWhere($where)->select([Constant::DB_TABLE_PRIMARY, Constant::DB_TABLE_CUSTOMER_PRIMARY])
                ->chunk(10000, function ($data) use($storeId, $service) {
                    foreach ($data as $item) {

                        $parameters = [$storeId, data_get($item, Constant::DB_TABLE_PRIMARY, -1), data_get($item, Constant::DB_TABLE_CUSTOMER_PRIMARY, -1)];
                        $queueData = [
                            Constant::SERVICE_KEY => $service,
                            Constant::METHOD_KEY => 'handleWarrantyAt',
                            Constant::PARAMETERS_KEY => $parameters,
                        ];
                        FunctionHelper::pushQueue($queueData, null, '{amazon-order-bind}');

                        //\App\Services\OrderWarrantyService::handleWarrantyAt($storeId, data_get($item, Constant::DB_TABLE_PRIMARY, -1), data_get($item, Constant::DB_TABLE_CUSTOMER_PRIMARY, -1));
                    }
                });
        dd(5656);

//        $storeId = 6; //审核状态
//        $auditStatus = 1; //审核状态
//        $reviewer = 'Jmiy'; //审核人
//        $remarks = '测试'; //备注
//        $data = \App\Services\ActivityApplyService::audit($storeId, [956], $auditStatus, $reviewer, $remarks);
//        dd($data);

        throw new \Exception('89899', 8989);

//        try {
//            throw new \Exception('89899', 8989);
//        } catch (\Exception $exception) {
//
////            $dingtalkjob = new \Wujunze\DingTalkException\DingTalkJob(
////                    app('request')->fullUrl(), get_class($exception), $exception->getMessage(), $exception->getCode(), $exception->getFile(), $exception->getLine(), $exception->getTraceAsString(), false
////            );
//////            $dingtalkjob->handle();
//
//            DingTalkExceptionHelper::notify($exception);
//        }

        $html = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_SHOPIFY, 'Customer', 'customerQuery', [6, '', 'bartgaiahome@gmail.com']);
        dd($html);

        dd(FunctionHelper::getCountry('86.175.73.206')); //82.1.201.200

        \App\Services\Erp\ErpAmazonService::getOrderItem('111-2653473-5926605-5', 'us1', 1);

        $postParams = json_encode([
            'jsonrpc' => 2.0,
            'method' => '',
            'id' => 1,
            'params' => [
            ],
        ]);

        $postParams = [];

        $headers = [
            'Content-Type: application/json; charset=utf-8', //设置请求内容为 json  这个时候post数据必须是json串 否则请求参数会解析失败
            'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTU5NjQzMzc3NCwiaWQiOiI2NzYifQ.0PEXmaw0i-lPPIC-dosybkbo3c4lQy0splRcMgC4rHs', //设置请求内容为 json  这个时候post数据必须是json串 否则请求参数会解析失败
        ];
        dump($request->headers->get('Authorization'));
        //http://172.16.6.92/api/user/tokenAuthentication
        $html = PlatformServiceManager::handle(Constant::PLATFORM_SERVICE_SHOPIFY, 'Base', 'request', ['http://192.168.152.128:81/api/shop/opcache', $postParams, '', '', 'GET', $headers]);
        dd($html);

//        dd(\App\Services\VoteService::getListData(['store_id' => 2]));
//
////        $dd = \App\Services\Platform\OnlineStore\AssetService::handlePull(1, Constant::PLATFORM_SERVICE_SHOPIFY, [1, '79369076854']);
////        dd($dd);
//        //dd(\App\Services\OrderWarrantyService::getEmailData(1, 284));
//
//        dd(\App\Services\OrderWarrantyService::handleEmail(1, 284, 0));
//
        dd(FunctionHelper::getCountry('82.1.201.200'));

        $request->offsetSet('app_env', 'sandbox');
        $storeId = 2;

//        $dd = \App\Services\Store\Shopify\Customers\Customer::getMetafield(1, 3282080104566); //50663227444
//        dd($dd);
//        $parameters = [
//            'key' => 'page_key',
//        ];
//        //$dd = \App\Services\Store\Shopify\Metafield\Metafield::getList($storeId, $parameters); //50663227444
//        $dd = \App\Services\Store\Shopify\Metafield\Metafield::getMetafield($storeId, 3140448092227, 'customer'); //50663227444
//        dd($dd);
//        $dd = \App\Services\Store\Shopify\OnlineStore\Page::getMetafield($storeId, 50703007796); //50663227444
//        dd($dd);
//        $dd = \App\Services\Platform\OnlineStore\ThemeService::handlePull($storeId, Constant::PLATFORM_SERVICE_SHOPIFY, [$storeId]);
//        dd($dd);
//        $dd = \App\Services\Platform\OnlineStore\AssetService::handlePull($storeId, Constant::PLATFORM_SERVICE_SHOPIFY, [$storeId, '79829303348']);
//        dd($dd);

        $dd = \App\Services\Platform\OnlineStore\PageService::handlePull($storeId, Constant::PLATFORM_SERVICE_SHOPIFY, [$storeId]);
        dd($dd);

        $dd = \App\Services\Store\Shopify\OnlineStore\Asset::getList($storeId, '79829303348'); //50663227444
        dd($dd);

        $dd = \App\Services\Platform\OnlineStore\PagePublishService::handlePublish($storeId, Constant::PLATFORM_SERVICE_SHOPIFY, ['id' => 4]);
        dd($dd);


        $parameters = [
            "title" => 'tast===',
            "body_html" => 'body_html====',
            'template_suffix' => 'vue_html_view',
            'handle' => 'dev-test23',
            'author' => 'Jmiy',
            'published' => true,
//            'metafields' => [],
        ];

        $dd = \App\Services\Platform\OnlineStore\PagePublishService::handle($storeId, Constant::PLATFORM_SERVICE_SHOPIFY, $parameters);
        dd($dd);

        $dd = \App\Services\Platform\OnlineStore\PageService::handlePull($storeId, Constant::PLATFORM_SERVICE_SHOPIFY, []);
        dd($dd);

        $dd = \App\Services\Store\Shopify\OnlineStore\Page::getList($storeId); //50663227444
        dd($dd);

        //$dd = \App\Services\Store\Shopify\OnlineStore\Theme::getList($storeId); //50663227444
//        $dd = \App\Services\Store\Shopify\OnlineStore\Theme::getTheme($storeId,79829303348); //50663227444
        //dd($dd);
        //$contents = Storage::disk('public')->get('index.html');
//        $value = Storage::disk('front')->get('index.html');
//        $dd = \App\Services\Store\Shopify\OnlineStore\Asset::update($storeId, '79829303348', 'templates/page.vue_test_asset.liquid', $value); //50663227444
//        dd($dd);
//        sleep(1);
//
//        //$bodyHtml = '<!DOCTYPE html><html><head><meta charset=utf-8><meta http-equiv=X-UA-Compatible content="IE=edge,chrome=1"><meta name=viewport content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"><link rel=icon href=https://testapi.patozon.net/favicon.ico><title>Patozon Member</title><link href=https://testapi.patozon.net/static/css/chunk-elementUI.18b11d0e.css rel=stylesheet><link href=https://testapi.patozon.net/static/css/chunk-libs.5cf311f0.css rel=stylesheet><link href=https://testapi.patozon.net/static/css/app.309ec748.css rel=stylesheet></head><body><noscript><strong>We re sorry but Patozon Member doesn t work properly without JavaScript enabled. Please enable it to continue.</strong></noscript><div id=app></div><script src=https://testapi.patozon.net/static/js/chunk-elementUI.a6050625.js></script><script src=https://testapi.patozon.net/static/js/chunk-libs.1dcb1481.js></script><script>(function(e){function n(n){for(var r,c,o=n[0],f=n[1],i=n[2],h=0,d=[];h<o.length;h++)c=o[h],u[c]&&d.push(u[c][0]),u[c]=0;for(r in f)Object.prototype.hasOwnProperty.call(f,r)&&(e[r]=f[r]);l&&l(n);while(d.length)d.shift()();return a.push.apply(a,i||[]),t()}function t(){for(var e,n=0;n<a.length;n++){for(var t=a[n],r=!0,c=1;c<t.length;c++){var o=t[c];0!==u[o]&&(r=!1)}r&&(a.splice(n--,1),e=f(f.s=t[0]))}return e}var r={},c={runtime:0},u={runtime:0},a=[];function o(e){return f.p+"static/js/"+({}[e]||e)+"."+{"chunk-101459f2":"3ddceb1e","chunk-105e71d4":"f97beab0","chunk-18eba54a":"86dcf6b3","chunk-1fb6319c":"ce5bbbcf","chunk-378b2ce5":"43fd61c4","chunk-4a7a3fc0":"c7c13b32","chunk-58f4e2d8":"e1b58ebc","chunk-67c4d03a":"f4455875","chunk-77e26660":"c25b5027","chunk-8c7ee2b8":"ace037ab","chunk-b4e30a22":"10c446eb","chunk-b877d1d4":"39e7aa30","chunk-ce84a222":"55547986"}[e]+".js"}function f(n){if(r[n])return r[n].exports;var t=r[n]={i:n,l:!1,exports:{}};return e[n].call(t.exports,t,t.exports,f),t.l=!0,t.exports}f.e=function(e){var n=[],t={"chunk-105e71d4":1,"chunk-18eba54a":1,"chunk-1fb6319c":1,"chunk-378b2ce5":1,"chunk-4a7a3fc0":1,"chunk-58f4e2d8":1,"chunk-67c4d03a":1,"chunk-77e26660":1,"chunk-8c7ee2b8":1,"chunk-b4e30a22":1,"chunk-b877d1d4":1,"chunk-ce84a222":1};c[e]?n.push(c[e]):0!==c[e]&&t[e]&&n.push(c[e]=new Promise(function(n,t){for(var r="static/css/"+({}[e]||e)+"."+{"chunk-101459f2":"31d6cfe0","chunk-105e71d4":"bbc8d64e","chunk-18eba54a":"3bc552ce","chunk-1fb6319c":"f2df9a59","chunk-378b2ce5":"17420f92","chunk-4a7a3fc0":"2b3458ec","chunk-58f4e2d8":"56480a27","chunk-67c4d03a":"8181e051","chunk-77e26660":"5662b26d","chunk-8c7ee2b8":"9ed42300","chunk-b4e30a22":"6dca6f8a","chunk-b877d1d4":"17748220","chunk-ce84a222":"0df70c63"}[e]+".css",u=f.p+r,a=document.getElementsByTagName("link"),o=0;o<a.length;o++){var i=a[o],h=i.getAttribute("data-href")||i.getAttribute("href");if("stylesheet"===i.rel&&(h===r||h===u))return n()}var d=document.getElementsByTagName("style");for(o=0;o<d.length;o++){i=d[o],h=i.getAttribute("data-href");if(h===r||h===u)return n()}var l=document.createElement("link");l.rel="stylesheet",l.type="text/css",l.onload=n,l.onerror=function(n){var r=n&&n.target&&n.target.src||u,a=new Error("Loading CSS chunk "+e+" failed.\n("+r+")");a.code="CSS_CHUNK_LOAD_FAILED",a.request=r,delete c[e],l.parentNode.removeChild(l),t(a)},l.href=u;var s=document.getElementsByTagName("head")[0];s.appendChild(l)}).then(function(){c[e]=0}));var r=u[e];if(0!==r)if(r)n.push(r[2]);else{var a=new Promise(function(n,t){r=u[e]=[n,t]});n.push(r[2]=a);var i,h=document.createElement("script");h.charset="utf-8",h.timeout=120,f.nc&&h.setAttribute("nonce",f.nc),h.src=o(e),i=function(n){h.onerror=h.onload=null,clearTimeout(d);var t=u[e];if(0!==t){if(t){var r=n&&("load"===n.type?"missing":n.type),c=n&&n.target&&n.target.src,a=new Error("Loading chunk "+e+" failed.\n("+r+": "+c+")");a.type=r,a.request=c,t[1](a)}u[e]=void 0}};var d=setTimeout(function(){i({type:"timeout",target:h})},12e4);h.onerror=h.onload=i,document.head.appendChild(h)}return Promise.all(n)},f.m=e,f.c=r,f.d=function(e,n,t){f.o(e,n)||Object.defineProperty(e,n,{enumerable:!0,get:t})},f.r=function(e){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},f.t=function(e,n){if(1&n&&(e=f(e)),8&n)return e;if(4&n&&"object"===typeof e&&e&&e.__esModule)return e;var t=Object.create(null);if(f.r(t),Object.defineProperty(t,"default",{enumerable:!0,value:e}),2&n&&"string"!=typeof e)for(var r in e)f.d(t,r,function(n){return e[n]}.bind(null,r));return t},f.n=function(e){var n=e&&e.__esModule?function(){return e["default"]}:function(){return e};return f.d(n,"a",n),n},f.o=function(e,n){return Object.prototype.hasOwnProperty.call(e,n)},f.p="https://testapi.patozon.net/",f.oe=function(e){throw console.error(e),e};var i=window["webpackJsonp"]=window["webpackJsonp"]||[],h=i.push.bind(i);i.push=n,i=i.slice();for(var d=0;d<i.length;d++)n(i[d]);var l=h;t()})([]);</script><script src=https://testapi.patozon.net/static/js/app.31a54f0d.js></script></body></html>';
//        $bodyHtml = 'Hello';
//        $title = 'test';
//        $handle = 'hell1';
//        $templateSuffix = 'vue_test_asset';
//        $dd = \App\Services\Store\Shopify\OnlineStore\Page::create($storeId, $title, $bodyHtml, $handle, $templateSuffix); //50663227444
//        dd($dd);
//        $dd = \App\Services\Store\Shopify\OnlineStore\Page::update($storeId, '43495260260', $bodyHtml, true, $title, $handle); //43495129188
//        var_dump($dd);
//        exit;
//        dd(\App\Services\Store\Shopify\Products\Product::getProduct($storeId, ['limit'=>5]));
//        $dd = \App\Services\Store\Shopify\OnlineStore\Asset::delete($storeId, '82760728628','templates/page.test_asset555.liquid'); //50663227444
//        dd($dd);
//        $ret = FunctionHelper::getCountry('5666');
//        dd($ret);
//
//        $ret = \App\Services\Platform\OrderService::getOrderWarrantyDetails(1, 12);
//        dd($ret);

        dd(\App\Services\OrderWarrantyService::getEmailData(1, 5, 0));

        $exceptionName = 'shopify订单号为空的订单如下：';
        $messageData = ['2281761669238'];
        $message = implode(',', $messageData);
        $parameters = [$exceptionName, $message, ''];
        $dd = \App\Services\Monitor\MonitorServiceManager::handle('Ali', 'Ding', 'report', $parameters);
        dd($dd, 5665);

        try {
            throw new \Exception(null, 61107);
        } catch (\Exception $exception) {

//            dd(Response::getDefaultResponseData($exception->getCode(), $exception->getMessage()));
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData($exception->getCode(), $exception->getMessage() ?? null)));
        }

        return Response::json(...Response::getResponseData(Response::getDefaultResponseData(9999999999)));

        return Response::json(...Response::getResponseData(Response::getDefaultResponseData(60008)));

        return Response::json([], 10000, null);

        dd(Response::getResponseMsg(1, 10000));

        dd(FunctionHelper::handleTime(null, $time = '', $dateFormat = 'Y-m-d H:i:s'));

        $ret = \App\Services\OrderWarrantyService::getClientWarrantyData(1, 85, [Constant::DB_TABLE_ORDER_AT => Constant::DB_TABLE_ORDER_TIME]);

        //$ret = \App\Services\Platform\OrderService::getClientWarrantyData(1, 82);
        dd($ret);

        dd(geoip('10.17.131.83, 34.226.97.202')->toArray());

        dd(Response::getResponseMsg(1, 10029565));

        //$this->locale($request);

        $this->validator($request);
//        $geoipData = geoip('72.143.222.218');//->toArray()
//        dd($geoipData);
        //$request->session();
        //dd(\App\Services\Store\PlatformServiceManager::handle('Shopify', 'Order', 'count', [1]));

        $ret = \App\Services\OrderWarrantyService::handleCreditAndExp(1, "7");
        dd($ret);

        $ret = \App\Services\Platform\OrderService::getOrderWarrantyDetails(1, 12);
        dd($ret);

        $operator = 'console';
        $parameters = [
            'operator' => $operator,
        ];
        $ret = \App\Services\ProductService::sync(1, $parameters);
        dd($ret);

        dd(FunctionHelper::getUniqueId('1', 'Shopify', 2281761669238));

        $ret = \App\Services\Platform\TransactionService::handlePull(1, 'Shopify', '2313956917366');
        dd($ret);

        $ret = \App\Services\Platform\OrderService::handlePull(1, 'Shopify');
        dd($ret);

        dd(\App\Services\Store\Shopify\Orders\Order::getOrder(1));

        $data = \App\Services\GameService::getImages(3, 10);
        dump($data);
        return Response::json($data);

        dd(\App\Services\ActivityHelpedLogService::handle(8, 38, 'inviteCode', 'Jmiy_cen@patazon.net', 188, 'i', 'us', []));

        //给解锁者发送助力成功感谢邮件
        $service = \App\Services\ActivityHelpedLogService::getNamespaceClass();
        $method = 'emailUnlockedUsers'; //邮件处理
        $extType = \App\Services\ActivityApplyService::getModelAlias();
        $parameters = [8, 38, 'Jmiy_cen@patazon.net', 942, $extType, Constant::DB_TABLE_EMAIL, 'view_audit_unlock_1'];
        //$dd = FunctionHelper::pushQueue(FunctionHelper::getJobData($service, $method, $parameters));

        $dd = \App\Services\ActivityHelpedLogService::emailUnlockedUsers(...$parameters);
        dd($dd);

        $service = \App\Services\ActivityHelpedLogService::getNamespaceClass();
        $method = 'handleEmail'; //邮件处理
        //发送库存不足邮件
        $extType = \App\Services\ActivityProductService::getModelAlias();
        $parameters = [8, 38, 188, $extType, Constant::DB_TABLE_EMAIL, Constant::OUT_STOCK];
        $dd = FunctionHelper::pushQueue(FunctionHelper::getJobData($service, $method, $parameters));
        dd($dd, 565989);

        $dd = \App\Services\ActivityHelpedLogService::handleEmail(8, 38, 11, 'ActivityProduct', 'email', 'out_stock');
        dd($dd, 565989);

        $clientDetails = []; //'browser_width'=>''
        dd(data_get($clientDetails, 'browser_width') ?? -1);

        dd(FunctionHelper::handleNumber(623565689898));

        //$ret = \App\Services\ActivityHelpedLogService::emailQueue(8, 38, 'Jmiy_cen@patazon.net', 232592, '192.166.66.76', 11, 'as');

        $ret = \App\Services\ActivityHelpedLogService::getEmailData(8, 38, 'Jmiy_cen@patazon.net', 232592, '192.166.66.76', 11, 'as');
        dd($ret);


        $storeId = $request->route(Constant::DB_TABLE_STORE_ID, $request->input(Constant::DB_TABLE_STORE_ID, 2));
        $appEnv = $request->input(Constant::APP_ENV, $request->route(Constant::APP_ENV, null)); //开发环境
        $platform = $request->route('platform', null); //开发环境
        dd($storeId, $platform, $appEnv);

        dd(\App\Services\Store\Shopify\Orders\Order::getOrder(1, '', '', [], '', 1, []));

        $request->offsetSet(Constant::DB_TABLE_STORE_ID, 5);
        dd(\App\Services\OrderService::getDetails(1, '202-8626755-7305904'));
        dd(['w.id', 'p.name', 'p.type', 'pi.type_value', 'pi.asin as item_asin', 'w.customer_id', 'w.ip', 'w.' . Constant::DB_TABLE_ACCOUNT, 'w.' . Constant::DB_TABLE_COUNTRY, ('w.' . Constant::DB_TABLE_IS_PARTICIPATION_AWARD), 'w.' . Constant::DB_TABLE_UPDATED_AT, 'a.country as usercountry', 'a.full_name', 'a.street', 'a.apartment', 'a.city', 'a.state', 'a.zip_code', 'a.phone', 'a.account_link']);

        dd(\App\Services\RewardService::getRewardFromAsin(1, 'B01FZ3BR5S', 'US'));

        dd(\App\Services\RewardService::updateRewardStatus(1, 45, 0, 'B07Z8B814B,B00KON1JIA')); //, 'B07Z8B814B,B00KON1JIA'

        dd(decrypt('eyJpdiI6InNud2hlTTRLYUV4anFlbVU1NytzRXc9PSIsInZhbHVlIjoia0J6dnZSOGUweThkWHo3UGZtNk8wZz09IiwibWFjIjoiODliNDA3NDM1ZDQ2ZjdhMzZhNmI2ZDBlZmFiNDAwNmYyZWM2ODQ0OTY2NmMwNzZkNmYyNzJlYTc3YjM3MDExOCJ9'));

        $orderItemModel = \App\Services\OrderItemService::getModel(1);
        $type = Constant::DB_TABLE_PLATFORM;
        $platform = Constant::PLATFORM_AMAZON;
        $maxPlatformOrderItemIdWhere = [
            Constant::DB_TABLE_TYPE => $type,
            Constant::DB_TABLE_PLATFORM => $platform,
            Constant::DB_TABLE_ORDER_COUNTRY => 'AU',
            Constant::DB_TABLE_PULL_MODE => 2, //订单拉取方式 1:C端用户主动拉取 2:定时任务拉取
        ];
        //data_set($maxPlatformOrderItemIdWhere, Constant::DB_TABLE_PLATFORM_UPDATED_AT, '2019-07-04 17:22:31');
        $orderItemIds = $orderItemModel->buildWhere($maxPlatformOrderItemIdWhere)->max(Constant::DB_TABLE_PLATFORM_UPDATED_AT);
        dd($orderItemIds);

        $storeId = 1;
        $account = 'Jmiy_cen@patazon.net';
        $orderno = '114-2256242-7944999';
        $country = 'US';
        $type = 'platform';
        $ret = OrderService::bind($storeId, $account, $orderno, $country, $type);
        var_dump($ret);
        exit;

        dd(\App\Services\OrdersService::handleAmazonOrder('us', '2020-05-25 00:00:00', null));

        //\App\Services\OrdersService::getOrderPullTimePeriod(); //获取订单拉取时间段
        dd(\App\Services\OrdersService::getOrderPullTimePeriod());

        dd(\App\Services\OrderService::handleBind(14929, 1, $request->input('request_mark', ''), true));


        dd(\App\Services\DictService::getListByType(Constant::DB_TABLE_ORDER_STATUS, 'dict_value', 'dict_key'));

        $startAt = '2020-01-01 00:00:00';
        $endAt = null;
        $tag = \App\Services\OrdersService::handleAmazonOrder('us', $startAt, $endAt);
        dd($tag);

        $dd = \App\Services\OrderService::handleBind(31084, 5, '');
        dump($dd);


        $tag = \App\Services\OrdersService::getOrderData('113-1638163-6995438', 'us');
        dd($tag);

        dd(\App\Services\CompanyApiService::getOrder("112-8119592-1698667", 'us', Constant::PLATFORM_AMAZON, 2));

//        $realTimePullAmazonPrder = \App\Services\DictService::getByTypeAndKey('pull_order', 'real_time_pull_amazon_order', true);
//        dd($realTimePullAmazonPrder);


        $tag = \App\Services\OrdersService::handleAmazonOrder('us', '2020-01-01 00:00:00', null);
        dd($tag);
        $service = \App\Services\OrdersService::getNamespaceClass();

        //删除统计
//        $handleCacheData = [
//            'service' => $service,
//            'method' => 'forget',
//            'parameters' => [
//                $key,
//            ]
//        ];
//        \App\Services\OrdersService::handleCache($tag, $handleCacheData);

        $handleCacheData = [//, 600
            'service' => $service,
            'method' => 'add',
            'parameters' => [
                $key, $time
            ]
        ];
        dump(\App\Services\OrdersService::handleCache($tag, $handleCacheData));

        $handleCacheData = [
            'service' => $service,
            'method' => 'get',
            'parameters' => [
                $key,
            ]
        ];
        $releaseLockTime = \App\Services\OrdersService::handleCache($tag, $handleCacheData);
        dd($releaseLockTime);





        dd(array_filter(['tag', 'country', null, null]));

//        \App\Services\OrdersService::clear();
//
        $tag = \App\Services\OrdersService::getCacheTags();
        $service = \App\Services\OrdersService::getNamespaceClass();
//
//        $cacheKey = $tag . ':*';
//        $handleCacheData = [
//            'service' => $service,
//            'method' => 'lock',
//            'parameters' => [$cacheKey],
//            'serialHandle' => [
//                [
//                    'service' => $service,
//                    'method' => 'forceRelease',
//                    'parameters' => [],
//                ]
//            ]
//        ];
//        $rs = \App\Services\OrdersService::handleCache($tag, $handleCacheData);
//        dump($rs);

        $cacheKey = $tag . ':lock11';

        $method = __FUNCTION__;
        $parameters = func_get_args();

        $handleCacheData = [
            'service' => $service,
            'method' => 'lock',
            'parameters' => [
                $cacheKey, 10
            ],
            'serialHandle' => [
                [
                    'service' => $service,
                    'method' => 'get',
                    'parameters' => [
                        function () use($service, $method, $parameters) {
                            dump('测试分布式锁抛出异常，不释放锁==========');

                            return [898989];
                            $code = 2;
                            $msg = '测试分布式锁抛出异常，不释放锁';
                            throw new \Exception($msg, $code);
                        }
                    ],
                ]
            ]
        ];

        $rs = \App\Services\OrdersService::handleCache($tag, $handleCacheData);
        dd($rs, 5555);


        dd(\App\Services\VoteService::getListData(['store_id' => 2]));

        dd(\App\Services\RewardAsinService::getRewardFromAsin(1, 'B07C48ZYXR', 'us'));

        //dd($productTypeData = \App\Services\DictService::getListByType('prize_type', 'dict_key', 'dict_value')); //获取类型 0:其他 1:礼品卡 2:coupon 3:实物 5:活动积分
//        $request->offsetSet('customer_id', 109633);
//        $request->offsetSet('store_id', 1);
//        dd(\App\Services\OrderReviewService::getReviewList($request->all()));
//
//        dd(FunctionHelper::getDiscountPrice(0, 1));
//        $pa = [5, ".", ''];
//        dd(sprintf('%.2f', floatval('0.0009')),number_format(floatval(null), ...$pa));//(float)
        //dd(\App\Services\OrderService::handleEmail(1, 74885, 0));

        dd(\App\Services\OrderService::handleBind(74885, 1, $request->input('request_mark', '')));

        dd(\App\Services\OrdersService::handleAmazonOrder('US'));

        dd(\App\Services\Erp\ErpAmazonService::getOrderItem('701-6578632-0491422', 'ca', true));

        $orderModel = \App\Services\OrdersService::getModel(2);
        $where = [
            Constant::DB_TABLE_PULL_MODE => 2, //订单拉取方式 1:C端用户主动拉取 2:定时任务拉取
            Constant::DB_TABLE_COUNTRY => 'us',
        ];
        $updateAt = '2020-01-04 09:54:43';

        //获取 更新时间等于 $updateAt 的订单数据
        data_set($where, Constant::DB_TABLE_PLATFORM_UPDATED_AT, $updateAt);
        $orderIds = $orderModel->buildWhere($where)->pluck(Constant::DB_TABLE_ORDER_NO);

        $model = \App\Services\OrdersService::getModel(2, 'us', [], 'AmazonOrderItem')->setTable(\App\Models\Erp\Amazon\AmazonOrderItem::$tablePrefix . '_us');


        $countWhere = [
            Constant::DB_TABLE_MODFIY_AT_TIME => $updateAt
        ];
        $count = $model->buildWhere($countWhere)->count(DB::raw('DISTINCT ' . Constant::DB_TABLE_AMAZON_ORDER_ID));

        $where = [
            [[Constant::DB_TABLE_MODFIY_AT_TIME, '>', $updateAt]]
        ];
        $amazonOrder = $model->select(Constant::DB_TABLE_AMAZON_ORDER_ID)->buildWhere($where)->orderBy(Constant::DB_TABLE_MODFIY_AT_TIME, 'ASC')->first();
        dd($count, $orderIds->count(), $orderIds->toArray(), $amazonOrder->toArray());

        dd(\App\Services\OrderService::handleBind(74885, 1, $request->input('request_mark', '')));

        dd(\App\Services\OrderService::handleEmail(1, 74885, 0));

        dd(\App\Services\OrderService::getEmailData(1, 74885, 0));

        //throw new \Exception('89899', 8989666);
        //dd(\App\Services\OrderAddressService::pullAmazonOrderAddress(1, 1, []));
        //dd(\App\Services\OrdersService::getOrderDetails(1, '111-0097821-7961023'));
        //\App\Services\OrdersService::handleAmazonOrder('US');
        //dd(\App\Services\OrderService::getOrder('113-0669030-5061051', '', Constant::PLATFORM_AMAZON, $storeId = 1));

        dd(\App\Services\Erp\ErpAmazonService::getOrderItem('405-6911247-6307543', '', 2));
        dd(222);

        //$type = ['signup', 'email_template'];
//        $keyField = null;//'dict_key';
//        $valueField = null;//'dict_value';
        $storeId = 1;
        $type = ['template_type', 'signup56', 'signup562333']; //template_type
        //$type = 'template_type'; //template_type
        $keyField = 'conf_key';
        $valueField = 'conf_value';
        $distKeyField = 'dict_key';
        $distValueField = 'dict_value';
        $data = \App\Services\DictService::getDistData($storeId, $type, $keyField, $valueField, $distKeyField, $distValueField);
        dd($data->toArray());

        $mark = 'act_mark';
        dd(FunctionHelper::myHash('/' . Constant::SHOPIFY_URL_PREFIX . '/' . $mark));

        dd(base64_decode('Zm9vOmJhcg=='));

        dd(\App\Services\ActivityProductService::getListData($request->all()));

        $sql = 'SELECT request_data,created_at FROM `ptx_statistical_analysis`.`crm_access_logs` where store_id=8 and act_id=4 and api_url=\'/api/shop/customer/createCustomer\' and  request_data like \'%"invite_code":%\' and request_data not like \'%"invite_code":""%\' and REPLACE (
		JSON_EXTRACT (request_data, \'$.invite_code\'),
		\'"\',
		\'\'
	) like \'%?%\'';
        $connectionName = \App\Services\LogService::getModel(8)->getConnectionName();
        $data = \Illuminate\Support\Facades\DB::connection($connectionName)->select($sql);
        foreach ($data as $item) {
            $requestData = json_decode(data_get($item, 'request_data', ''), true);
            $invite_code_str = data_get($requestData, 'invite_code', '');

            $storeId = data_get($requestData, 'store_id', 0);
            $account = data_get($requestData, 'account', '');
            $createdAt = data_get($item, 'created_at', '');
            $updatedAt = data_get($item, 'created_at', '');
            $actId = data_get($requestData, 'act_id', 0);

            $inviteCode = null;

            if (strripos($invite_code_str, '=') !== false) {
                $index = strripos($invite_code_str, '=') + 1;
                $inviteCode = substr($invite_code_str, $index);

                if (strripos($inviteCode, '?') !== false) {
                    $invite_code_str = explode('?', $inviteCode);
                    $inviteCode = data_get($invite_code_str, 0, null);
                }
            } else if (strripos($invite_code_str, '?') !== false) {
                $invite_code_str = explode('?', $invite_code_str);
                $inviteCode = data_get($invite_code_str, 0, null);
            }

            if ($inviteCode) {
                $accountData = \App\Services\CustomerService::customerExists($storeId, 0, $account, 0, true); //被邀请者数据
                $inviteCustomerId = data_get($accountData, 'customer_id', 0);
                \App\Services\InviteService::handle($inviteCode, $inviteCustomerId, $storeId, $createdAt, $updatedAt, $actId, $requestData);
            }
        }
        dd(11);





        dd(FunctionHelper::randomStr(2));

        dd(in_array(8, [1, 8]));

        //dd(date('Y-m-d H:i:s',strtotime('-8 hour', time())));

        throw new \Exception('89899', 8989);

//        $dd = [
//            'tiffanyturtle??@hotmail.com',
//            'salimabdulkader@gmail.com',
//            'Chaulkchris@yahoo.com',
//            'lester0578@gmail.com',
//            'Brycealandudley@gmail.com',
//            'naderat@yahoo.com',
//            'wwdd83@hotmail.com',
//            'arch@luox.top',
//            'lcrouse2@msn.com',
//            'stargunn@hotmail.com',
//            'diconquality@gmail.com',
//            'kei78066@iencm.com',
//            'fjoc76@gmail.com',
//            'service@goobangdoo.com',
//            'cjone84bia57@hotmail.com',
//            'dhaguidhg157@yahoo.co.jp',
//            'Toyota420xp@gmail.com',
//            'joechen@gmail.com',
//            'dothienchuong@yahoo.ca',
//            'josiaheliot@gmail.com',
//            'Nigelgturner@hotmail.com',
//            'Constanceccri@gmail.com',
//            'robinfillhart@gmail.com',
//            'teja.saiviswa@gmail.com',
//            'helidavidsulbaran@gmail.com',
//            'fitzgeralds2peru@gmail.com',
//            'lacarenterprise@hotmail.com',
//            'germanandres448@gmail.com',
//            'evanlee@gmail.com',
//            'joanbao44@gmail.com',
//            'adammccarthy110@hotmail.com',
//            'mike.dienes@gmail.com',
//            'kmgrosscpa@yahoo.com',
//            'adam.david.engle@gmail.com',
//            'zachshald@gmail.com',
//            'Candacecarroll@rocketmail.com',
//            'michael_fahie@yahoo.ca',
//            'alkatom.11@hotmail.com',
//            'Creid1288@gmail.com',
//            'Patterson.sunny@yahoo.ca',
//            'ajithsganesh@gmail.com',
//            'gretzski@gmail.com',
//            'kdemir@gmx.net',
//            'amljhn33@gmail.com',
//            'alikat0602@hotmail.com',
//            'ronaldslaney@aol.com',
//            'cesar.herrero@gmail.com',
//            'carletta_bethea@yahoo.com',
//            'simonbaker543@gmail.com',
//            'kenzhaowu@gmail.com',
//            'www007@mail.ru',
//            'armchrqbak@gmail.com',
//            'petej007@hotmail.co.uk',
//            'kev3051@hotmail.com',
//            'johnson.laurelmn@gmail.com',
//            'gautamkg@gmail.com',
//            'jlgant123@gmail.com',
//            'harlinghausen@web.de',
//            'shieldbearer@yahoo.com',
//            'blommermichael@gmail.com',
//            'viarabe@gmail.com',
//            'juanmarosa@outlook.com',
//            'chene.patrick@wanadoo.fr',
//            'allanhsv@outlook.com',
//            'erikasm_reis@hotmail.com',
//            'rzt7700roge@gmail.com',
//            'juancarnevali@gmail.com',
//            'zicliew123@gmail.com',
//            'jed830@gmail.com',
//            'zholud@gmail.com',
//            'chowder.005.lt@gmail.com',
//            'thenajeeb@hotmail.com',
//            'Illogicalvoid@gmail.com',
//            'Hvdsusa@gmail.com',
//            'graham46@hotmail.com',
//            'janc39@hotmail.com',
//            'willycel@juno.com',
//            'madzzk22@gmail.com',
//            'delfonix@gmail.com',
//            'linhooi0.0teoh@gmail.com',
//            'erick060778@gmail.com',
//            'Johnandalison@gmail.com',
//            'dragonsroar15@hotmail.com'
//        ];
//        $data = [];
//        foreach ($dd as $value) {
//            $data[$value] = \App\Services\Store\ShopifyService::customerQuery(2, $order = '', $query = $value);
//            sleep(1);
//        }
//        dd($data);



        dd(\App\Services\StoreService::getModel(0)->pluck(Constant::DB_TABLE_PRIMARY));

        dd($startTime = Carbon::parse('2018/10/1')->rawFormat('Y-m-d H:i:s'));

        dd(\App\Services\ExcelService::parseExcelFile('E:\Work\patozon\VipApi\storage\logs\coupon_template (5).xlsx'));


        $activityApply = \App\Services\ActivityHelpedLogService::emailUnlockedUsers(5, 3, 'Jmiy_cen@patazon.net', 981, $extType = 'ActivityApply', $type = 'email', $key = 'view_audit_unlock_1', $extData = []);
        dd($activityApply);

        $extData = [
            'updated_at_min' => '2020-01-17 11:06:00',
            'updated_at_max' => '2020-01-17 11:09:00',
        ];
        $ids = [];
        $sinceId = '';
        $source = 6;
        $operator = 'console';
        $limit = 1000;
        $data = \App\Services\Store\ShopifyService::getCustomer(2, '', '', $ids, $sinceId, $limit, $source, $extData);
        dd($data);

        //dd(\App\Services\ActivityProductService::getProductPrice('B07RK96WF8', 'VTVTPC134AB-USAS20', 'US'));
//        dump(\App\Services\PointClearedLogService::handle(1));
//        dd(1111);
//        \App\Services\OrderService::handleWarranty(8,19642,282743);
//        dd(11);
        //dd(\App\Services\Store\ShopifyService::createCustomer(1, 'Kramerdigitaleffects@hotmail.com', $password = '123abc', $acceptsMarketing = false, $firstName = '', $lastName = ''));

        dd(\App\Services\Store\ShopifyService::customerQuery(2, $order = '', $query = 'jmccarthy1707@gmail.com'));

//        $requestData = [
//            'store_id' => 5,
//            'customer_id' => '281605',
//            'account' => 'Jmiy_cen@patazon.net',
//            'country' => 'US',
//            'group' => 'customer',
//            'first_name' => 'Betty',
//            'last_name' => 'Landis',
//            'ip' => '72.187.54.90',
//            'remark' => '注册',
//            'ctime' => '',
//            'act_id' => 1000,
//            'source' => 0,
//        ];
//        $rs = EmailService::sendCouponEmail(5, $requestData);
//        dd($rs);
//
//        $storeId=5;
//        app('request')->offsetSet('app_env', 'sandbox');
//        $hmac = 'aVB8fEJErbweBCKDsc5MI2kzR8JrfEgUM25Be1NWSQs=';
//        $data = '{"id":820982911946154508,"email":"jon@doe.ca","closed_at":null,"created_at":"2020-02-18T16:54:44-08:00","updated_at":"2020-02-18T16:54:44-08:00","number":234,"note":null,"token":"123456abcd","gateway":null,"test":true,"total_price":"99.98","subtotal_price":"89.98","total_weight":0,"total_tax":"0.00","taxes_included":false,"currency":"USD","financial_status":"voided","confirmed":false,"total_discounts":"5.00","total_line_items_price":"94.98","cart_token":null,"buyer_accepts_marketing":true,"name":"#9999","referring_site":null,"landing_site":null,"cancelled_at":"2020-02-18T16:54:44-08:00","cancel_reason":"customer","total_price_usd":null,"checkout_token":null,"reference":null,"user_id":null,"location_id":null,"source_identifier":null,"source_url":null,"processed_at":null,"device_id":null,"phone":null,"customer_locale":"en","app_id":null,"browser_ip":null,"landing_site_ref":null,"order_number":1234,"discount_applications":[{"type":"manual","value":"5.0","value_type":"fixed_amount","allocation_method":"one","target_selection":"explicit","target_type":"line_item","description":"Discount","title":"Discount"}],"discount_codes":[],"note_attributes":[],"payment_gateway_names":["visa","bogus"],"processing_method":"","checkout_id":null,"source_name":"web","fulfillment_status":"pending","tax_lines":[],"tags":"","contact_email":"jon@doe.ca","order_status_url":"https:\/\/pro-ikich.myshopify.com\/26268696661\/orders\/123456abcd\/authenticate?key=abcdefg","presentment_currency":"USD","total_line_items_price_set":{"shop_money":{"amount":"94.98","currency_code":"USD"},"presentment_money":{"amount":"94.98","currency_code":"USD"}},"total_discounts_set":{"shop_money":{"amount":"5.00","currency_code":"USD"},"presentment_money":{"amount":"5.00","currency_code":"USD"}},"total_shipping_price_set":{"shop_money":{"amount":"10.00","currency_code":"USD"},"presentment_money":{"amount":"10.00","currency_code":"USD"}},"subtotal_price_set":{"shop_money":{"amount":"89.98","currency_code":"USD"},"presentment_money":{"amount":"89.98","currency_code":"USD"}},"total_price_set":{"shop_money":{"amount":"99.98","currency_code":"USD"},"presentment_money":{"amount":"99.98","currency_code":"USD"}},"total_tax_set":{"shop_money":{"amount":"0.00","currency_code":"USD"},"presentment_money":{"amount":"0.00","currency_code":"USD"}},"line_items":[{"id":866550311766439020,"variant_id":31427789717589,"title":"IKICH 4 Slice Toaster with LCD Countdown","quantity":1,"sku":"B07TDPRW7P","variant_title":null,"vendor":null,"fulfillment_service":"manual","product_id":4419240099925,"requires_shipping":true,"taxable":true,"gift_card":false,"name":"IKICH 4 Slice Toaster with LCD Countdown","variant_inventory_management":"shopify","properties":[],"product_exists":true,"fulfillable_quantity":1,"grams":2858,"price":"54.99","total_discount":"0.00","fulfillment_status":null,"price_set":{"shop_money":{"amount":"54.99","currency_code":"USD"},"presentment_money":{"amount":"54.99","currency_code":"USD"}},"total_discount_set":{"shop_money":{"amount":"0.00","currency_code":"USD"},"presentment_money":{"amount":"0.00","currency_code":"USD"}},"discount_allocations":[],"admin_graphql_api_id":"gid:\/\/shopify\/LineItem\/866550311766439020","tax_lines":[]},{"id":141249953214522974,"variant_id":31777821687893,"title":"ikich 2 Slice Stainless Steel Toaster","quantity":1,"sku":"B07QCX8FB3","variant_title":null,"vendor":null,"fulfillment_service":"manual","product_id":4530539528277,"requires_shipping":true,"taxable":true,"gift_card":false,"name":"ikich 2 Slice Stainless Steel Toaster","variant_inventory_management":"shopify","properties":[],"product_exists":true,"fulfillable_quantity":1,"grams":1451,"price":"39.99","total_discount":"5.00","fulfillment_status":null,"price_set":{"shop_money":{"amount":"39.99","currency_code":"USD"},"presentment_money":{"amount":"39.99","currency_code":"USD"}},"total_discount_set":{"shop_money":{"amount":"5.00","currency_code":"USD"},"presentment_money":{"amount":"5.00","currency_code":"USD"}},"discount_allocations":[{"amount":"5.00","discount_application_index":0,"amount_set":{"shop_money":{"amount":"5.00","currency_code":"USD"},"presentment_money":{"amount":"5.00","currency_code":"USD"}}}],"admin_graphql_api_id":"gid:\/\/shopify\/LineItem\/141249953214522974","tax_lines":[]}],"fulfillments":[],"refunds":[],"total_tip_received":"0.0","admin_graphql_api_id":"gid:\/\/shopify\/Order\/820982911946154508","shipping_lines":[{"id":271878346596884015,"title":"Generic Shipping","price":"10.00","code":null,"source":"shopify","phone":null,"requested_fulfillment_service_id":null,"delivery_category":null,"carrier_identifier":null,"discounted_price":"10.00","price_set":{"shop_money":{"amount":"10.00","currency_code":"USD"},"presentment_money":{"amount":"10.00","currency_code":"USD"}},"discounted_price_set":{"shop_money":{"amount":"10.00","currency_code":"USD"},"presentment_money":{"amount":"10.00","currency_code":"USD"}},"discount_allocations":[],"tax_lines":[]}],"billing_address":{"first_name":"Bob","address1":"123 Billing Street","phone":"555-555-BILL","city":"Billtown","zip":"K2P0B0","province":"Kentucky","country":"United States","last_name":"Biller","address2":null,"company":"My Company","latitude":null,"longitude":null,"name":"Bob Biller","country_code":"US","province_code":"KY"},"shipping_address":{"first_name":"Steve","address1":"123 Shipping Street","phone":"555-555-SHIP","city":"Shippington","zip":"40003","province":"Kentucky","country":"United States","last_name":"Shipper","address2":null,"company":"Shipping Company","latitude":null,"longitude":null,"name":"Steve Shipper","country_code":"US","province_code":"KY"},"customer":{"id":115310627314723954,"email":"john@test.com","accepts_marketing":false,"created_at":null,"updated_at":null,"first_name":"John","last_name":"Smith","orders_count":0,"state":"disabled","total_spent":"0.00","last_order_id":null,"note":null,"verified_email":true,"multipass_identifier":null,"tax_exempt":false,"phone":null,"tags":"","last_order_name":null,"currency":"USD","accepts_marketing_updated_at":null,"marketing_opt_in_level":null,"admin_graphql_api_id":"gid:\/\/shopify\/Customer\/115310627314723954","default_address":{"id":715243470612851245,"customer_id":115310627314723954,"first_name":null,"last_name":null,"company":null,"address1":"123 Elm St.","address2":null,"city":"Ottawa","province":"Ontario","country":"Canada","zip":"K2H7A8","phone":"123-123-1234","name":"","province_code":"ON","country_code":"CA","country_name":"Canada","default":true}}}';
//        $verify = \App\Services\Store\ShopifyService::verifyWebhook($storeId, $data, $hmac);
//        dd(1);
//
//        dd(FunctionHelper::randomStr(8));
//        $where = [Constant::DB_TABLE_PRIMARY => 30];
//        $data = \App\Services\ContactUsService::getModel(2)->buildWhere($where)->first()->toArray();
//        dump($data);
//
//
//        //获取邮件模板
//        $replacePairs = [];
//        foreach ($data as $key => $value) {
//            $replacePairs['{{$' . $key . '}}'] = $value;
//        }
//        dd($replacePairs);

        dd(\App\Services\Erp\SyncErpFinanceRateService::getErpFinanceData());

        dd(\App\Util\Constant::RULES_NOT_APPLY_STORE, $this->storeId);

        $row['55'] = null;
        dd($row['55'] ?? -1, data_get($row, '55', -2));

        $requestData = array_filter([
            'ids' => 0, //207119551,1073339460
            'since_id' => '', //925376970775
            'created_at_min' => '', //2019-02-25T16:15:47+08:00
            'created_at_max' => '',
            'updated_at_min' => '2019-02-25T16:15:47+08:00', //2019-02-25T16:15:47+08:00
            'updated_at_max' => '',
            'limit' => 1,
        ]);
        dd($requestData);

        $request->offsetSet('act_form', 'vote');
        $requestData = $request->all();

        //投票
        $isHandleAct = \App\Services\VoteService::handle(8, 2, '8999@qq.com', 3, 1, $requestData);
        dump($isHandleAct);

        //分享
        $socialMedia = $request->input('social_media', ''); //社媒平台 FB TW
        $fromUrl = $request->input('url', $request->headers->get('Referer') ?? 'no');
        \App\Services\ActivityShareService::handle(8, 2, 8999, '8999@qq.com', $socialMedia, $fromUrl, $requestData);

        //获取活动次数
        $actionData = [
            Constant::SERVICE_KEY => ActivityService::getNamespaceClass(),
            Constant::METHOD_KEY => 'get',
            Constant::PARAMETERS_KEY => [],
            Constant::REQUEST_DATA_KEY => $requestData,
        ];
        $lotteryData = ActivityService::handleLimit(8, 2, '8999@qq.com', $actionData);
        $lotteryNum = data_get($lotteryData, 'lotteryNum', 0);
        dd(
                [
                    'lotteryNum' => $lotteryNum > 0 ? $lotteryNum : 0,
                    'lotteryTotal' => data_get($lotteryData, 'lotteryTotal', 0),
                ]
        );

        dd(config('app.codeMap.100000', null));

        $dd = \App\Services\Survey\SurveyResultService::getData($storeId = 5, $actId = 2);
        dd($dd);

        $storeId = 5;
        $country = '';
        $parameters = [];
        $customerData = [
            'store_id' => 1,
            'account' => 'account656',
                //'store_customer_id' => 'storeCustomerId',
//            'status' => 1,
//            'customer_id' => 226642,
                //'ctime' => Carbon::now()->toDateTimeString(),
//            'source' => 3,
//            'last_sys_at' => Carbon::now()->toDateTimeString(),
        ];
        $model = \App\Services\CustomerService::getModel($storeId, $country, $parameters);
        $model->enableQueryLog();
        dd($model, $model->where(['account' => 'account656'])->update($customerData), $model->getQueryLog());

        //dd(\App\Services\CustomerService::customerExists(1, 1, '', 0, $getData = true));

        dd(\App\Services\Survey\SurveyResultService::getModel());

        $dd = \App\Services\Survey\SurveyService::getItemData(5, 1);
        dd($dd, 56);


//        $data=['a'=>'a'];
//        data_set($data, 'a', 'bb', false);
//        dd($data,565666,__FUNCTION__);

        $customerData = [
            'store_id' => 1,
            'account' => 'account',
            //'store_customer_id' => 'storeCustomerId',
            'status' => 1,
            'customer_id' => 226642,
                //'ctime' => Carbon::now()->toDateTimeString(),
//            'source' => 3,
//            'last_sys_at' => Carbon::now()->toDateTimeString(),
        ];

//        \App\Services\CustomerService::getModel(0, '')->enableQueryLog();
//        $customerId = \App\Services\CustomerService::getModel(0, '')->buildWhere(['store_id' => 1,
//                    'account' => 'account',])->update($customerData);
        //\Illuminate\Support\Facades\DB::enableQueryLog();
        $parameters = ['attributes' => ['aa' => 'HHHHHHH']];
        $model = \App\Services\SubcribeService::getModel(1, '', $parameters);
        $model->enableQueryLog();
        dump($model, data_get($model, 'aa', null), data_get($model, 'exists', null));
        //$customerId = \App\Models\CustomerInfo::updateOrCreate(['customer_id' => 226642], $customerData);
        //$customerId = \App\Services\SubcribeService::addSubcribe($storeId = 1, 'account', 'country ', 'ip', $remark = '$remark', '', ['accepts_marketing' => 1]);

        $customerData = [
            'email' => 'account56656',
        ];

        $customerId = $model->insert($customerData); //addSubcribe($storeId = 1, 'account', 'country ', 'ip', $remark = '$remark', '', ['accepts_marketing' => 1]);
        dump($model->getQueryLog());
        //dump(\Illuminate\Support\Facades\DB::getQueryLog());
//        $customerId = \App\Services\CustomerService::getModel(0, '')->insert($customerData);
        //dump(\App\Services\CustomerService::getModel(0, '')->getQueryLog());
        //$customerId = \App\Services\CustomerService::getModel(0, '')->insertGetId($customerData);
//        $model = \App\Services\CustomerService::getModel(0, '');
//
//        //data_set($model, 'store_id', 1);
//
//        $dd = $model->updateOrCreate(
//                [
//            'store_id' => 1,
//            'account' => 'account',
//                ], $customerData);
        //$customerId = \App\Services\CustomerService::getModel(0, '')->updateOrCreate(['store_id' => 1, 'account' => 'account'], $customerData);
        //$customerId = \App\Models\Customer::firstOrCreate(['store_id' => 1, 'account' => 'account'], $customerData);
        //event(new \App\Events\ExampleEvent());
//        $flight = \App\Services\CustomerService::getModel(0, '')->find(226591);
//
//$flight->account = 'account555';
//       $flight->save()

        dd($customerId, data_get($customerId, 'exists', null), data_get($customerId, 'dbOperation', null)); //添加系统日志

        $values = [
            [
                [
                    ['d' => 9, 'a' => 5],
                    ['d' => 7, 'a' => 8]
                ]
            ]
        ];
        $values = \App\Services\CustomerService::getModel(1)->getAttributesData($values);

        dd($values);

//$request->offsetSet('app_env', $request); 3
        $dd = \App\Services\Customer\PlatformService::createCustomer('Shopify', 3, 'Jmiy_cen66655566666@patazon.net', '123456', 'register', true, 'firstName', 'lastName', 'phone', $request->all());
        dd($dd);

        $storeId = $request->route('store_id', -1);
        dd($storeId, $request->route('test_data', null));
        $orderinfo = \App\Services\Erp\ErpAmazonService::getOrderItem('408-1493223-3464363', 'es');
        dd($orderinfo);


        $item = [
            'item_price_amount' => '3978.00',
            'promotion_discount_amount' => '199.00',
        ];
        dd(number_format((data_get($item, 'item_price_amount', 0) - data_get($item, 'promotion_discount_amount', 0)), 2, '.', '') + 0);

        $extData = $request->all();
        $where = [
            'act_id' => 1,
            'ip' => 'ip',
        ];
        $verifiedEmail = 1;
        $data = [
            'ext_type' => 'ActivityApply', //关联模型
            'ext_id' => 1, //申请id
            'account' => 'account',
            'help_account' => 'helpAccount',
            'help_country' => 'helpCountry',
            'customer_id' => 2,
            'verified_help_email' => 'verifiedEmail',
            'device' => data_get($extData, 'clientData.device', ''), //设备信息
            'device_type' => data_get($extData, 'clientData.device_type', 0), // 设备类型 1:手机 2：平板 3：桌面
            'platform' => data_get($extData, 'clientData.platform', ''), //系统信息
            'platform_version' => data_get($extData, 'clientData.platform_version', ''), //系统版本
            'browser' => data_get($extData, 'clientData.browser', ''), // 浏览器信息  (Chrome, IE, Safari, Firefox, ...)
            'browser_version' => data_get($extData, 'clientData.browser_version', ''), // 浏览器版本
            'languages' => data_get($extData, 'clientData.languages', ''), // 语言 ['nl-nl', 'nl', 'en-us', 'en']
            'is_robot' => data_get($extData, 'clientData.is_robot', 0), //是否是机器人
        ];
        $activityHelpedData = \App\Services\ActivityHelpedLogService::updateOrCreate(5, $where, $data);
        dd($activityHelpedData);



        //https://release-api.patozon.net/api/shop/customer/activate/eyJpdiI6IjFDNzA1dW91dWtZMUpQOGo0eitcL3ZRPT0iLCJ2YWx1ZSI6IjFcLzVwTUJ5Unpxb0oxZ2RsRkloVTFwbnNtXC9LWEZaU21Ld2dUOWowZGhadmRraDFkTFBUVW8xeUUyRlFwczYxdXpieHp3TDJcL21USDYraVFUWGdmaXRodEpJWmJ6WXJ2RXRSTmdjMmVDdnRZdytJKzhOdU1WNzB1eXQ0bmE0bWZoelpFQytXSmZtc090dWhuVThjNXRraWwrVUVWYWJ2VFwvN2dFem56YnM1WHUxNVBrTW5nVWg2OGVDQzZWRWdMZUF2aDRTREVkbFBTRXpnVTdxdjFWSk5iakNRZEdtR1B4NHkyM1hsXC9YdG5RTEllSVJvdUs3VE93dHBGaElKdG9PWU8wZUxyaUJQdStsbGdMWUZlNFFac0dNekxHSHB0S1YyYjZ3Q1VpWkd2YTFtMEd6SkFzZXpuMnVlKzNnSk5MdmJXRHRiRDdMMzhudWgrUDNUMzFyNUZGbjFrcHlueXlcLytcL21SUjk5b0xweWs9IiwibWFjIjoiODllYzhiMDJhMWQ3N2IzMjg3NzAyOTdmNWY3MGJkNmI5Y2U0NDJlZWJlMzZlODY3N2NmZjQwYzY1ZWNmYjlhNyJ9
        //http://127.0.0.1:8006/api/shop/customer/activate/eyJpdiI6IjFDNzA1dW91dWtZMUpQOGo0eitcL3ZRPT0iLCJ2YWx1ZSI6IjFcLzVwTUJ5Unpxb0oxZ2RsRkloVTFwbnNtXC9LWEZaU21Ld2dUOWowZGhadmRraDFkTFBUVW8xeUUyRlFwczYxdXpieHp3TDJcL21USDYraVFUWGdmaXRodEpJWmJ6WXJ2RXRSTmdjMmVDdnRZdytJKzhOdU1WNzB1eXQ0bmE0bWZoelpFQytXSmZtc090dWhuVThjNXRraWwrVUVWYWJ2VFwvN2dFem56YnM1WHUxNVBrTW5nVWg2OGVDQzZWRWdMZUF2aDRTREVkbFBTRXpnVTdxdjFWSk5iakNRZEdtR1B4NHkyM1hsXC9YdG5RTEllSVJvdUs3VE93dHBGaElKdG9PWU8wZUxyaUJQdStsbGdMWUZlNFFac0dNekxHSHB0S1YyYjZ3Q1VpWkd2YTFtMEd6SkFzZXpuMnVlKzNnSk5MdmJXRHRiRDdMMzhudWgrUDNUMzFyNUZGbjFrcHlueXlcLytcL21SUjk5b0xweWs9IiwibWFjIjoiODllYzhiMDJhMWQ3N2IzMjg3NzAyOTdmNWY3MGJkNmI5Y2U0NDJlZWJlMzZlODY3N2NmZjQwYzY1ZWNmYjlhNyJ9
        $dd = 'eyJpdiI6IjFDNzA1dW91dWtZMUpQOGo0eitcL3ZRPT0iLCJ2YWx1ZSI6IjFcLzVwTUJ5Unpxb0oxZ2RsRkloVTFwbnNtXC9LWEZaU21Ld2dUOWowZGhadmRraDFkTFBUVW8xeUUyRlFwczYxdXpieHp3TDJcL21USDYraVFUWGdmaXRodEpJWmJ6WXJ2RXRSTmdjMmVDdnRZdytJKzhOdU1WNzB1eXQ0bmE0bWZoelpFQytXSmZtc090dWhuVThjNXRraWwrVUVWYWJ2VFwvN2dFem56YnM1WHUxNVBrTW5nVWg2OGVDQzZWRWdMZUF2aDRTREVkbFBTRXpnVTdxdjFWSk5iakNRZEdtR1B4NHkyM1hsXC9YdG5RTEllSVJvdUs3VE93dHBGaElKdG9PWU8wZUxyaUJQdStsbGdMWUZlNFFac0dNekxHSHB0S1YyYjZ3Q1VpWkd2YTFtMEd6SkFzZXpuMnVlKzNnSk5MdmJXRHRiRDdMMzhudWgrUDNUMzFyNUZGbjFrcHlueXlcLytcL21SUjk5b0xweWs9IiwibWFjIjoiODllYzhiMDJhMWQ3N2IzMjg3NzAyOTdmNWY3MGJkNmI5Y2U0NDJlZWJlMzZlODY3N2NmZjQwYzY1ZWNmYjlhNyJ9';
        $_data = decrypt($dd);
        $_data = json_decode($_data, true);
        dd($_data);
        FunctionHelper::setTimezone(5);
        dd(Carbon::now()->toDateTimeString());

        dd(
                '设备信息: ' . app('agent')->device(), //获取设备信息 (iPhone, Nexus, AsusTablet, ...)
                // 设备类型
                '是否是手机: ' . app('agent')->isMobile(), '是否是平板: ' . app('agent')->isTablet(), '是否是桌面: ' . app('agent')->isDesktop(), '系统信息: ' . app('agent')->platform(), //系统信息  (Ubuntu, Windows, OS X, ...)
                '系统版本: ' . app('agent')->version(app('agent')->platform()), '浏览器信息: ' . app('agent')->browser(), // 浏览器信息  (Chrome, IE, Safari, Firefox, ...)
                '浏览器版本: ' . app('agent')->version(app('agent')->browser()), // 获取浏览器版本
                app('agent')->languages(), // 语言 ['nl-nl', 'nl', 'en-us', 'en']
                '是否是机器人: ' . app('agent')->isRobot(),
                // 厂商产品定位
                '是否是Android: ' . app('agent')->isAndroidOS(), '是否是Nexus: ' . app('agent')->isNexus(), '是否是Safari: ' . app('agent')->isSafari()
        ); //


        $rs = \App\Services\SubcribeService::addSubcribe(1, 'account1', 'country1', 'ip1', 'remark1', '', ['accepts_marketing' => 1, 'actId' => 12, 'verifiedEmail' => 0]);
        dd($rs);

        $rs = \App\Services\Customer\PlatformService::createCustomer(
                        'Shopify', 8, 'Jmiy_cen@patazon.net', '123456', $action = 'register', $acceptsMarketing = true, $firstName = 'test_jmiy', $lastName = 'test_jmiy', $phone = 'phone', ['app_env' => 'sandbox']
        );
        dd($rs);

        $dd = \App\Services\ActivityHelpedLogService::handleEmail(5, 1, 11, 'ActivityProduct', 'email', 'out_stock');
        dd($dd, 565989);

        $activityHelpedData = \App\Services\ActivityProductService::getData(5, 1, '', ['p.id' => 11], 'one', [], null, 1);
        dd($activityHelpedData, 8989);


        $where = [
            'act_id' => 1,
            'ip' => 'ip',
        ];
        $data = [
            'ext_type' => 'ActivityApply', //关联模型
            'ext_id' => 1, //申请id
            'account' => 'account',
            'help_account' => 'helpAccount',
            'help_country' => 'country',
            'customer_id' => 1,
            'verified_help_email' => 1,
        ];
        $activityHelpedData = \App\Services\ActivityHelpedLogService::updateOrCreate(5, $where, $data);
        dd($activityHelpedData->toArray(), 8989);

        $data = [];
        $data = Arr::pluck($data, null, 'key');
        dd($data);

        dd(\App\Services\CustomerService::isEffectiveEmail('quella_xia@patazon.net'));

        $dd = \App\Services\CustomerService::reg(1, 'account89999565666@ttt.com', 0, '', 1, '', 'firstName', 'lastName', 1, '', '', '', '', $request->all());
        dd($dd);

        $dd = Cache::lock('foo')->get(function () {
            // 获取无限期锁并自动释放...
            sleep(1);
            return [888];
        });


        $lock = Cache::lock('foo', 10);
        $isGetLock = $lock->get();
        dump($isGetLock);
        if ($isGetLock) {
            //获取锁定10秒...

            dump($lock->get());

            $lock->release();
        }

        dd($dd, 5656);

        try {
            throw new \Exception('89899', 8989);
        } catch (\Exception $exception) {

//            $dingtalkjob = new \Wujunze\DingTalkException\DingTalkJob(
//                    app('request')->fullUrl(), get_class($exception), $exception->getMessage(), $exception->getCode(), $exception->getFile(), $exception->getLine(), $exception->getTraceAsString(), false
//            );
////            $dingtalkjob->handle();

            DingTalkExceptionHelper::notify($exception);
        }
        dd(8989);



        $actData = \App\Services\ActivityService::getModel(1)->where(['id' => 6])->select(['end_at'])->first();


        if ($actData === null) {
            return Response::json([], 69998, '');
        }

        $nowTime = Carbon::now()->toDateTimeString();

        $endAt = data_get($actData, 'end_at', null);
        if ($endAt !== null && $nowTime > $endAt) {
            return Response::json([], 69999, 'Activity Is End');
        }

        dd($actData, data_get($actData, 'end_at', -1));

        $item = [
            'item_price_amount' => 4380.00,
            'promotion_discount_amount' => 500.00,
        ];
        //dump(data_get($item, 'item_price_amount', 0),data_get($item, 'promotion_discount_amount', 0),(data_get($item, 'item_price_amount', 0) - data_get($item, 'promotion_discount_amount', 0)));

        dd(number_format((data_get($item, 'item_price_amount', 0) - data_get($item, 'promotion_discount_amount', 0)), 2, '.', '') + 0);

//        dd(number_format(3880.00, 2, '.', '') + 0);
//
//        dd(\App\Services\Store\ShopifyService::customerQuery(2, $order = '', $query = '89632588@gmail.com'));
        exit;

//        $header = [
//            '邮箱' => 'email',
//            '国家' => 'country',
//            'ip' => 'ip',
//            '订阅方式' => 'remark',
//            '订阅时间' => 'ctime',
//            'distinctField' => [
//                'primaryKey' => 'id',
//                'primaryValueKey' => 'id',
//                'select' => ['id']
//            ],
//        ];
//
//        $requestData['store_id'] = 1; //
//        $requestData['page_size'] = 20000; //
//        $requestData['page'] = 1;
//
//        $service = \App\Services\SubcribeService::getNamespaceClass();
//        $method = 'getItemData';
//        $select = ['id', 'email', 'country', 'ip', 'remark', 'ctime'];
//        $parameters = [$requestData, true, true, $select, false, false];
//
//        $countMethod = 'getItemData';
//        $countParameters = Arr::collapse([$parameters, [true]]);
//        //$file = \App\Services\ExcelService::createExcelByQueue($header, $service, $countMethod, $countParameters, $method, $parameters); //1000
//        $file = \App\Services\ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000
//        dd($file, 5656);
//        dd(__METHOD__, '66666666===');
//        exit;
//
//        $storeId = 8;
////        $config = app('config');
////        $config->set('app.store_timezone.' . $storeId . '.timezone', 'America/New_York');//America/Anguilla  America/Los_Angeles
//
//        FunctionHelper::setTimezone($storeId); //设置时区
//        $dst = date('I');  //判断是否夏令时
//        dump($dst, Carbon::now()->toDateTimeString());
//        $createdAtMin = '2019-11-03 01:30:00';
//        $dateTime = 1800;
//        $_createdAtMax = Carbon::createFromTimestamp(((Carbon::parse($createdAtMin)->timestamp) + $dateTime))->toDateTimeString();
//        dd($_createdAtMax);
//        exit;
//        dd(\App\Services\Store\ShopifyService::countCustomer(6));
//        dd(565656);
//        $header = [
//            '会员名' => 'name',
////            '性别' => 'gender',
////            '出生日期' => 'brithday',
//            '邮箱' => 'account',
//            '国家' => 'country',
//            '会员当前积分' => 'credit',
//            '会员总积分' => 'total_credit',
//            '会员经验值' => 'exp',
//            '会员等级' => 'vip',
//            '注册时间' => 'ctime',
//            '最后活动时间' => 'lastlogin',
//            '注册ip' => 'ip',
//            '来源' => 'source_value',
//            '是否激活' => 'isactivate',
//            'distinctField' => [
//                'primaryKey' => 'customer_id',
//                'primaryValueKey' => 'a.customer_id',
//                'select' => ['a.customer_id']
//            ],
//        ];
//
//        $requestData['store_id'] = 1;
//        $requestData['page_size'] = 10000; //
//        $requestData['page'] = 1;
//
//        $service = '\App\Services\CustomerService';
//        $method = 'getShowList';
//        $select = [];
//        $parameters = [$requestData, true, true, $select, false, false];
//
//        $countMethod = 'getShowList';
//        $countParameters = Arr::collapse([$parameters, [true]]);
//        $file = \App\Services\ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000
//        $header = [
//            '会员名' => 'name',
//            '性别' => 'gender',
//            '出生日期' => 'brithday',
//            '邮箱' => 'account',
//            '国家' => 'country',
//            '州省' => 'region',
//            '填写时间' => 'mtime',
//            '注册ip' => 'ip',
//            'distinctField' => [
//                'primaryKey' => 'customer_id',
//                'primaryValueKey' => 'a.customer_id',
//                'select' => ['a.customer_id']
//            ],
//        ];
//        $storeId = 1;
//        switch ($storeId) {
//            case 1:
//                $header['profile'] = 'profile_url';
//
//                break;
//
//            default:
//                $header['兴趣'] = 'interest';
//                break;
//        }
//
//        $requestData['store_id'] = 1;
//        $requestData['page_size'] = 20000;
//        $requestData['page'] = 1;
//
//        $service = '\App\Services\CustomerService';
//        $method = 'getDetailsList';
//        $select = [];
//        $parameters = [$requestData, true, true, $select, false, false];
//
//        $countMethod = 'getDetailsList';
//        $countParameters = Arr::collapse([$parameters, [true]]);
//        $file = \App\Services\ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000
//
//        var_dump($file);
//        exit;
//        $builder->orWhere('account', 'like', '%@qq.com%')
//                    ->orWhere('account', 'like', '%@163.com%')
//                    ->orWhere('account', 'like', '%@patazon.net%')
//                    ->orWhere('account', 'like', '%@chacuo.net%');
        //$accountData = ['@qq.com','@163.com','@patazon.net','@chacuo.net'];
//        $accountData = ['Jmiy_cen22156568989@patazon.net'];
//        dd(\App\Services\CustomerService::deleteCustomerData(3, $accountData));
//        dd(565656);
//        $dayPrizes = array("a" => "Dog", "b" => "Cat", "c" => "Horse");
//        shuffle($dayPrizes);
//        dd(data_get($dayPrizes, 0, 0), 888);
        //$column, $values, $boolean = 'and'

        $mutuallyExclusiveWhere = [
            '{customizeWhere}' => [
                [
                    'method' => 'whereNotIn',
                    'parameters' => ['p.type', [1, 2, 3], $boolean = 'and'],
                ],
            ]
        ];
        $customerInfoData = \App\Services\ActivityWinningService::getModel(1)->buildWhere($mutuallyExclusiveWhere, 'and', true);
        dd($customerInfoData);

        $isExists = \App\Services\ActivityAddressService::exists(3, '', $where = ['account' => 'Jmiy_cen@patazon.net'], true);
        dd($isExists, 565656);

        $code = '';
        $inviteCode = '';
        $orderno = ''; //订单
        $ip = ''; //会员ip
        $createdAt = '';
        $inviteId = 0;
        $handleActivate = 1;
        $extData = [
            'act_id' => 1,
            'actId' => 1,
            'activityConfigType' => 'email_activate',
        ];
        $rs = EmailService::sendActivateEmail(5, 109633, 'Jmiy_cen@patazon.net', $code, $inviteCode, 'US', $orderno, $ip, '会员激活======', $createdAt, $inviteId, $handleActivate, $extData);
        dd($rs);

        $creditWhere = [
            'customer_id' => 148934,
            'add_type' => 1,
            'action' => 'order_bind',
            'ext_id' => 30572,
            'ext_type' => 'customer_order',
        ];
        $isExists = \App\Services\CreditService::exists(2, $creditWhere);
        dd($isExists, 56565656);

        $tags = Arr::collapse([config('cache.tags.activity', ['{activity}']), \App\Services\ActivityWinningService::getCustomerWinCacheTag()]);
        dd($tags);
        $ttl = config('cache.ttl', 86400); //认证缓存时间 单位秒
        $data = Cache::tags($tags)->put('customerWin', 'customerWin', $ttl);
        $tags = config('cache.tags.activity', ['{activity}']);
        $data = Cache::tags($tags)->put('activity', 'activity', $ttl);


        Cache::tags(['{activity}'])->flush();

        dump(Cache::tags(['{activity}', '{customerWin}'])->get('customerWin'));

        dump(Cache::tags($tags)->get('activity'));
        dd($tags);



        //dd(strtotime(''),strtotime('0000-00-00 00:00:00'),$value = Carbon::parse('1000-01-01 00:00:00')->rawFormat('Y-m-d'),Carbon::parse('0000-00-00 00:00:00')->rawFormat('Y-m-d'));
        //dd(5656);
        //dd($isExistsCredit = \App\Services\CreditService::exists(1, ['customer_id' => 1999999999999999999, 'action' => 'signup']));
//        $str = 'one two three four';
//        list($first_name, $last_name) = explode(' ', $str, 2);
//        dd($first_name, $last_name);

        dd(\App\Services\CustomerService::restore());

        dd(\App\Services\Store\ShopifyService::countCustomer(5));

        $actionData = [
            'service' => '',
            'method' => 'increment',
            'parameters' => [],
        ];

//        $actionData = [
//            'service' => '',
//            'method' => 'increment',
//            'parameters' => [],
//        ];
//        Cache::increment('key', $amount);
//        Cache::decrement('key');
//        Cache::decrement('key', $amount);

        \App\Services\ActivityWinningService::handleLotteryLimit(2, 6, 412, $actionData);



        //$isEmailCoupon = \App\Services\ActivityWinningService::handle(2, 6, 412);


        $actionData = [
            'service' => '',
            'method' => 'get',
            'parameters' => [],
        ];
        $isEmailCoupon = \App\Services\ActivityWinningService::handleLotteryLimit(2, 6, 412, $actionData);
        dd($isEmailCoupon);


        $headers = [
            'Content-Type: application/json; charset=utf-8', //设置请求内容为 json  这个时候post数据必须是json串 否则请求参数会解析失败
            'X-Requested-With: XMLHttpRequest', //告诉服务器，当前请求是 ajax 请求
        ];

        $curlOptions = [
            CURLOPT_CONNECTTIMEOUT_MS => 1000 * 100,
            CURLOPT_TIMEOUT_MS => 1000 * 100,
        ];
        $url = 'http://127.0.0.1:8006/api/shop/clear';
        $responseText = \App\Util\Curl::request($url, $headers, $curlOptions, [], 'GET');
        dd($responseText);

//        $validatorData = [
//            'to_email' => 'Jmiy_cen56562@patazon.net',
//        ];
//        $rules = [
//            'to_email' => 'required|email',
//        ];
//        $validator = \App\Util\PublicValidator::handle($validatorData, $rules, [], __FUNCTION__);
//        dd($validator);

        $data = [
            'store_id' => 1,
            'act_id' => 1,
            'page_size' => 50,
        ];
        dd(\App\Services\VoteService::getItemData($storeId = 1, $actId = 1, $account = '', $page = 1, $pageSize = 10));

        //dd(Carbon::now()->rawFormat('M. j, Y'));
//        $coupons = \App\Services\CouponService::getRegCoupon(2, 'US');
//        dd($coupons->isEmpty(),$coupons->pluck('code')->all(),$coupons->where('use_type', 1)->pluck('id')->all());
//        $array =['5656'=>5656];
//        $key = '22';
//        dd(data_get($array, $key, null),$array[$key] ?? value(null));
        //dd(decrypt('eyJpdiI6IjNORStVWWVjaDY5MGtjK24weWZoSUE9PSIsInZhbHVlIjoiRFo5UVowajQ2Rnh1Q1FJVzJENjdqUT09IiwibWFjIjoiNjY1MTM1ZDEwNDdjZjQwMjE5ODAyNTE2NzVkZWFhZjJjYzUzMzBiODZhMzc0Y2JhZjgwOTliN2IzMDE4MWYxNyJ9'));
        $requestData = [
            'store_id' => 3,
            'customer_id' => '',
            'account' => 'Jmiy_cen@patazon.net',
            'country' => 'US',
            'group' => 'customer',
            'first_name' => 'Jmiy_cen@patazon.net',
            'last_name' => '',
            'ip' => '127.0.0.1',
            'remark' => '注册',
            'ctime' => '',
            'act_id' => 0,
            'source' => 0,
        ];
        $rs = EmailService::sendCouponEmail(3, $requestData);
        dd($rs);

        $item = ['a' => 55];
        //dd(Redis::LPUSH('test_list',\App\Services\BaseService::getZsetMember()));

        dd(Redis::RPOP('test_list'));

        //保留小数点后两个位
//        $num = 15.99-15.00;
//        dd($num,$format_num = sprintf("%.2f",$num)+0,number_format($num, 2)+0);
        //发送优惠券邮件
        $requestData = [
            'store_id' => 5,
            'customer_id' => '',
            'account' => 'Jmiy_cen@patazon.net',
            'country' => 'US',
            'group' => 'customer',
            'first_name' => 'first_name160',
            'last_name' => 'last_name_sdddd',
            'ip' => '',
            'remark' => '注册',
            'ctime' => '',
            'act_id' => 0,
            'source' => 10000,
        ];
        $rs = EmailService::sendCouponEmail(5, $requestData);
        dd($rs);

//        $dd = \App\Services\CouponService::monitorCoupon();
//        dd($dd);
//
////        $emailView = \App\Services\DictStoreService::getByTypeAndKey(5, 'email', 'view_coupon', true, true, 'jp');
////        dd($emailView);
//        //发送优惠券邮件
//        $storeId = 5;
//        $requestData = [
//            'store_id' => $storeId,
//            'customer_id' => '',
//            'account' => 'Jmiy_cen@patazon.net',
//            'country' => 'UK',
//            'group' => 'coupon',
//            'first_name' => 'firstName',
//            'last_name' => 'lastName',
//            'ip' => '',
//            'remark' => 'remark',
//            'ctime' => '',
//        ];
//        $rs = \App\Services\EmailService::sendCouponEmail($storeId, $requestData);
//        dd($rs);
//        $dd = \App\Services\EmailService::sendToAdmin(5, '测试管理员邮件', '测试管理员邮件=====');
//        dd($dd);

        $dd = \App\Services\OrderService::handleEmail(5, 1, 0);
        //$dd = \App\Services\OrderService::getShippedEmailData(5, 1, 0);
        //$dd = \App\Services\OrderService::getEmailData(5, 1, 0);
        dd($dd);


        //dd(view());exit;

        $view = 'emails.coupon.default';
        dd(view($view, ['content' => '<b>Hi , {{$name}}</b>', 'name' => '8888888888'])->render(function ($view, $contents) {

                    extract($view->getData());
                    dump($name);
                    return $contents;
                }));

        $service = EmailService::getNamespaceClass();
        $method = 'handle'; //邮件处理


        dd(method_exists1($service, $method));

        $dd = \App\Services\OrderService::getNamespaceClass();
        dd($dd);


//        App::setLocale('en'); // 设置站点语言
//        $locale = App::getLocale();
//        dd($locale, App::isLocale('en'));

        $countryMap = \App\Services\CouponService::$countryMap[5];
        $coupon_country = data_get($countryMap, '', data_get($countryMap, 'OTHER', ''));
        dd($coupon_country);



        $users = DB::connection('db_xc_order')->select('SELECT * FROM amazon_order_item_uk LIMIT 1');
        $_users = DB::connection('db_xc_single_product')->select('SELECT * FROM shop_asin LIMIT 1');
        dd($users, $_users);

        $data = [
            'products' => [
                ['name' => 'Desk 1', 'price' => 100],
                ['name' => 'Desk 2', 'price' => 150],
            ],
        ];

        data_set($data, 'products.*.price', 200);
        dd($data);
        exit;

        $orderStatusData = \App\Services\DictService::getListByType('order_status', 'dict_value', 'dict_key'); //订单状态 -1:匹配中 0:未支付 1:已经支付 2:取消 默认:-1
        dd($orderStatusData, Arr::get($orderStatusData, 'Canceled', -1));
        dd(\App\Services\Erp\ErpAmazonService::getOrderInfo('701-0000013-0913834', 'mx'));
        exit;

//        $data = [
//            'to' => 'thomas. cornelius@yahoo.com',
//        ];
//        $messages = [
//            'required' => ':attribute is required.',
//            'email' => ':attribute must be a valid email address.',
////            'same' => 'The :attribute and :other must match.',
////            'size' => 'The :attribute must be exactly :size.',
////            'between' => 'The :attribute value :input is not between :min - :max.',
////            'in' => 'The :attribute must be one of the following types: :values',
//        ];
//        $rules = [
//            'to' => 'required|email',
//        ];
//        $validator = Validator::make($data, $rules, $messages);
//        if (!$validator->fails()) {
//            return true;
//        }
//
//        $errors = $validator->errors();
//        foreach ($rules as $key => $value) {
//            if ($errors->has($key)) {
//                dump($errors->first($key));
//            }
//        }
//        exit;
//
//        $keys = Redis::connection('queue')->KEYS('queues:{default}*');
//        Redis::connection('queue')->DEL($keys);
//        exit;
//
//        $zsetKey = 'queues:{default}:reserved';
//        $customerCount = Redis::connection('queue')->zcard($zsetKey);
//        dump($customerCount);
//        for ($i = 1; $i <= $customerCount; $i++) {
//            $options = [
//                'withscores' => true,
//                'limit' => [
//                    'offset' => ($i - 1) * 1,
//                    'count' => 1,
//                ]
//            ];
//            $data = Redis::connection('queue')->zrangebyscore($zsetKey, '-inf', '+inf', $options); //
//
//            foreach ($data as $rowData => $row) {
//                $_rowData = \App\Services\BaseService::getSrcMember($rowData);
//                if (\Illuminate\Support\Arr::get($_rowData, 'displayName', '') == 'App\Mail\ActivateCustomer' && false !== strpos($rowData, 'thomas. cornelius@yahoo.com')) {
//                    Redis::connection('queue')->ZREM($zsetKey, $rowData);
//                    dump($rowData, $row);
//                }
//            }
//        }
//        exit;

        $pa = '[1,5544,"Jmiy_cen@patazon.net","eUDSGp","","US","","97.127.172.190","\u4f1a\u5458\u6fc0\u6d3b","",0,1,{"act_id":2}]';
        $pa = json_decode($pa, true);
        $dd = \App\Services\EmailService::sendActivateEmail(...$pa);
        dump($dd);

//        $dd = \App\Services\ActivityApplyService::getAuditMailData(1, 1, 1);
//        dd($dd);
//
//        $storeId = 3;
//        $orderId = [11367];
//        $reviewStatus = 2;
//        $reviewCredit = 0;
//        $addType = 2;
//        $action = 'order_review5555';
//        $reviewRemark = 'review_remark';
//        $ret = OrderService::addReviewcheck($storeId, $orderId, $reviewStatus, $reviewCredit, $addType, $action, $reviewRemark);
//        dd($ret);

        $storeId = 1; //审核状态
        $auditStatus = 1; //审核状态
        $reviewer = 'reviewer'; //审核人
        $remarks = 'remarks'; //备注
        $data = \App\Services\ActivityApplyService::audit($storeId, [1], $auditStatus, $reviewer, $remarks);
        dd($data);


        $rs = \App\Services\Customer\PlatformService::createCustomer('Shopify', 6, 'Jmiy_cen@patazon.net', '123456', $acceptsMarketing = true, $firstName = 'test_jmiy', $lastName = 'test_jmiy', $phone = 'phone');
        dd($rs);
        $storeId = 3;
        $orderId = [12099];
        $reviewStatus = 1;
        $reviewCredit = 0;
        $addType = 1;
        $action = 'order_review';
        $reviewRemark = 'review_remark';
        $ret = \App\Services\OrderService::addReviewcheck($storeId, $orderId, $reviewStatus, $reviewCredit, $addType, $action, $reviewRemark);
        dd($ret);

        return $this->delOrder($request);

        dd(implode('', ['\\', __CLASS__]));
        $dd = ['products', 'products11'];

        dd(json_encode($dd, JSON_UNESCAPED_UNICODE)); //
        //dd(strtotime('yyyy-MM-dd'));

        dd(strtotime('16-10-1976'));



//        $storeId = 1;
//        $query = \App\Models\ActivityApply::with(['ext' => function($query) use($storeId) {
//                        $query->select(['*']);
//                    }]);
//
//        $query->getModel()->getConnection()->enableQueryLog();
//
//        $dd = $query->get()->toArray(); //->toSql(); //->toSql();
//        //var_dump($dd);
//
//        var_dump($query->getModel()->getConnection()->getQueryLog());
//
//
//        var_dump($dd);
//        exit;
//        $latestPosts = \App\Models\Customer::from('`ptxcrm`.customer')->select('*');
//        $users = \App\Services\BaseService::createModel(1, 'ActivityCustomer')->from('activity_customers as ac')->joinSub($latestPosts, 'customer', function($join) {
//                            $join->on('ac.customer_id', '=', 'customer.customer_id');
//                        })->get();
//        var_dump($users);exit;
        //\Illuminate\Support\Facades\DB::connection('db_permission')->enableQueryLog();
        $where = [
            'id' => [1, 2, 3],
//            'or' => [
//                'u.id' => 4,
//                'u.name1' => 5,
//                [
//                    ['u.id', '=', 10],
//                    ['u.id', '=', 11]
//                ],
//                [['u.id', 'like', '%55%']],
//                [['u.username', 'like', '%55%']],
//            ],
//            [
//                ['u.id', '=', 6],
//                ['u.id', '=', 7]
//            ],
//            'u.username' => '565',
//            'u.username' => DB::raw('password'),
//            'u.a=kkk',
        ];
        //->onlyTrashed()  withTrashed from('user as u')->withoutTrashed()->
        $query = \App\Models\User::buildWhere($where)
                ->leftJoin('user_roles as b', function ($join) {
            $join->on('b.user_id', '=', 'u.id'); //->where('b.status', '=', 1);
        })
        ; //

        $query->getModel()->getConnection()->enableQueryLog();

        $dd = $query->get()->toArray(); //->toSql(); //->toSql();
        //var_dump($dd);

        var_dump($query->getModel()->getConnection()->getQueryLog());

//        var_dump(\Illuminate\Support\Facades\DB::connection('db_permission')->getQueryLog());
        exit;


        exit;

//        $dd = $request->route();
//        var_dump($dd);
//
//        $dd = app('router')->namedRoutes[$dd[1]['as']];
//        var_dump(app('router')->namedRoutes);
//        var_dump($dd);
//        var_dump(FunctionHelper::getCurrentRouteUri());
//        exit;
//        $nameData = [
////            '会员管理',
////            '积分管理',
////            '订单管理',
////            '产品管理',
////            '邮件管理',
////            '优惠券管理',
////            '经验管理',
//            '账号管理',
//        ];
//        foreach ($nameData as $name) {
//            $where = [];
//            $data = [
//                'name' => $name,
//                'url' => $request->input('url', ''),
//                'type' => $request->input('type', 1),
//                'component' => $request->input('component', ''),
//                'router' => $request->input('router', ''),
//                'icon' => $request->input('icon', ''),
//                'parent_id' => $request->input('parent_id', 0),
//            ];
//            \App\Services\PermissionService::insert($where, $data);
//        }
//        exit;
//
//
//        $rts = app('router')->getRoutes();
//        $rows = [];
//        foreach ($rts as $rt => $route) {
//
//            if (isset($route['action']['middleware']) && in_array('auth:apiAdmin', $route['action']['middleware'])) {
//                var_dump($route['action']);
//                $rows[] = $route['uri'];
//                //var_dump($route['uri']);
//            }
//
//
//
////            $rows[$route['uri']] = [
////                'verb' => $route['method'],
////                'uri' => $route['uri'],
////                'uses' => isset($route['action']['uses']) ? $route['action']['uses'] : 'Closure',
//////                'controller' => $this->getController($route['action']),
//////                'action' => $thiw->getAction($route['action']),
////            ];
//        }
//        var_dump($rows);
//        exit;
//
//        var_dump(app('router')->getRoutes());
//        exit;
//        $dd = decrypt('eyJpdiI6ImNsNTEzNmZTVm1MZFl2ODhSQml6RWc9PSIsInZhbHVlIjoiNFF0RTFrbXplQmpqXC9tR1wvZVJoNTFGN0lsZWlZa1Jrb1FkNWNYK0ZZTzBRPSIsIm1hYyI6ImJkZmYxNzllNmQ0ZDYxNTk3YTZhNzM0OTUxYzU5NDk0NTlhYWVmNzQ4ZjNjYWE3YjEzZmQyOWE5NzZmNTljNWUifQ==');
//        var_dump($dd);exit;
//        $storeId = 5;
//        $dd = \App\Services\Store\Shopify\Customers\Customer::customerQuery($storeId, '', '18039292996lyf@gmail.com');
//        var_dump($dd);
//        exit;
//
//        $dd = \App\Services\Store\Shopify\Customers\Customer::getAccountActivationUrl($storeId, '1901020708964'); //43495129188
//        var_dump($dd);
//        exit;
//
//        //$dd = \App\Services\Store\Shopify\Customers\Customer::customerQuery($storeId, '', 'sssaaas@qq.com');
//
//        $dd = \App\Services\Store\Shopify\Customers\Customer::customerActivate($storeId, '1901020708964', '53a6dd57f06110bd70e044297876320d', '55555'); //
//        var_dump($dd);
//        exit;

        $storeId = 5;
        //$bodyHtml = '<!DOCTYPE html><html><head><meta charset=utf-8><meta http-equiv=X-UA-Compatible content="IE=edge,chrome=1"><meta name=viewport content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"><link rel=icon href=https://testapi.patozon.net/favicon.ico><title>Patozon Member</title><link href=https://testapi.patozon.net/static/css/chunk-elementUI.18b11d0e.css rel=stylesheet><link href=https://testapi.patozon.net/static/css/chunk-libs.5cf311f0.css rel=stylesheet><link href=https://testapi.patozon.net/static/css/app.309ec748.css rel=stylesheet></head><body><noscript><strong>We re sorry but Patozon Member doesn t work properly without JavaScript enabled. Please enable it to continue.</strong></noscript><div id=app></div><script src=https://testapi.patozon.net/static/js/chunk-elementUI.a6050625.js></script><script src=https://testapi.patozon.net/static/js/chunk-libs.1dcb1481.js></script><script>(function(e){function n(n){for(var r,c,o=n[0],f=n[1],i=n[2],h=0,d=[];h<o.length;h++)c=o[h],u[c]&&d.push(u[c][0]),u[c]=0;for(r in f)Object.prototype.hasOwnProperty.call(f,r)&&(e[r]=f[r]);l&&l(n);while(d.length)d.shift()();return a.push.apply(a,i||[]),t()}function t(){for(var e,n=0;n<a.length;n++){for(var t=a[n],r=!0,c=1;c<t.length;c++){var o=t[c];0!==u[o]&&(r=!1)}r&&(a.splice(n--,1),e=f(f.s=t[0]))}return e}var r={},c={runtime:0},u={runtime:0},a=[];function o(e){return f.p+"static/js/"+({}[e]||e)+"."+{"chunk-101459f2":"3ddceb1e","chunk-105e71d4":"f97beab0","chunk-18eba54a":"86dcf6b3","chunk-1fb6319c":"ce5bbbcf","chunk-378b2ce5":"43fd61c4","chunk-4a7a3fc0":"c7c13b32","chunk-58f4e2d8":"e1b58ebc","chunk-67c4d03a":"f4455875","chunk-77e26660":"c25b5027","chunk-8c7ee2b8":"ace037ab","chunk-b4e30a22":"10c446eb","chunk-b877d1d4":"39e7aa30","chunk-ce84a222":"55547986"}[e]+".js"}function f(n){if(r[n])return r[n].exports;var t=r[n]={i:n,l:!1,exports:{}};return e[n].call(t.exports,t,t.exports,f),t.l=!0,t.exports}f.e=function(e){var n=[],t={"chunk-105e71d4":1,"chunk-18eba54a":1,"chunk-1fb6319c":1,"chunk-378b2ce5":1,"chunk-4a7a3fc0":1,"chunk-58f4e2d8":1,"chunk-67c4d03a":1,"chunk-77e26660":1,"chunk-8c7ee2b8":1,"chunk-b4e30a22":1,"chunk-b877d1d4":1,"chunk-ce84a222":1};c[e]?n.push(c[e]):0!==c[e]&&t[e]&&n.push(c[e]=new Promise(function(n,t){for(var r="static/css/"+({}[e]||e)+"."+{"chunk-101459f2":"31d6cfe0","chunk-105e71d4":"bbc8d64e","chunk-18eba54a":"3bc552ce","chunk-1fb6319c":"f2df9a59","chunk-378b2ce5":"17420f92","chunk-4a7a3fc0":"2b3458ec","chunk-58f4e2d8":"56480a27","chunk-67c4d03a":"8181e051","chunk-77e26660":"5662b26d","chunk-8c7ee2b8":"9ed42300","chunk-b4e30a22":"6dca6f8a","chunk-b877d1d4":"17748220","chunk-ce84a222":"0df70c63"}[e]+".css",u=f.p+r,a=document.getElementsByTagName("link"),o=0;o<a.length;o++){var i=a[o],h=i.getAttribute("data-href")||i.getAttribute("href");if("stylesheet"===i.rel&&(h===r||h===u))return n()}var d=document.getElementsByTagName("style");for(o=0;o<d.length;o++){i=d[o],h=i.getAttribute("data-href");if(h===r||h===u)return n()}var l=document.createElement("link");l.rel="stylesheet",l.type="text/css",l.onload=n,l.onerror=function(n){var r=n&&n.target&&n.target.src||u,a=new Error("Loading CSS chunk "+e+" failed.\n("+r+")");a.code="CSS_CHUNK_LOAD_FAILED",a.request=r,delete c[e],l.parentNode.removeChild(l),t(a)},l.href=u;var s=document.getElementsByTagName("head")[0];s.appendChild(l)}).then(function(){c[e]=0}));var r=u[e];if(0!==r)if(r)n.push(r[2]);else{var a=new Promise(function(n,t){r=u[e]=[n,t]});n.push(r[2]=a);var i,h=document.createElement("script");h.charset="utf-8",h.timeout=120,f.nc&&h.setAttribute("nonce",f.nc),h.src=o(e),i=function(n){h.onerror=h.onload=null,clearTimeout(d);var t=u[e];if(0!==t){if(t){var r=n&&("load"===n.type?"missing":n.type),c=n&&n.target&&n.target.src,a=new Error("Loading chunk "+e+" failed.\n("+r+": "+c+")");a.type=r,a.request=c,t[1](a)}u[e]=void 0}};var d=setTimeout(function(){i({type:"timeout",target:h})},12e4);h.onerror=h.onload=i,document.head.appendChild(h)}return Promise.all(n)},f.m=e,f.c=r,f.d=function(e,n,t){f.o(e,n)||Object.defineProperty(e,n,{enumerable:!0,get:t})},f.r=function(e){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},f.t=function(e,n){if(1&n&&(e=f(e)),8&n)return e;if(4&n&&"object"===typeof e&&e&&e.__esModule)return e;var t=Object.create(null);if(f.r(t),Object.defineProperty(t,"default",{enumerable:!0,value:e}),2&n&&"string"!=typeof e)for(var r in e)f.d(t,r,function(n){return e[n]}.bind(null,r));return t},f.n=function(e){var n=e&&e.__esModule?function(){return e["default"]}:function(){return e};return f.d(n,"a",n),n},f.o=function(e,n){return Object.prototype.hasOwnProperty.call(e,n)},f.p="https://testapi.patozon.net/",f.oe=function(e){throw console.error(e),e};var i=window["webpackJsonp"]=window["webpackJsonp"]||[],h=i.push.bind(i);i.push=n,i=i.slice();for(var d=0;d<i.length;d++)n(i[d]);var l=h;t()})([]);</script><script src=https://testapi.patozon.net/static/js/app.31a54f0d.js></script></body></html>';
        $bodyHtml = 'Hello';
        $title = 'Hello test111';
        //$dd = \App\Services\Store\Shopify\OnlineStore\Page::create($storeId, $title, $bodyHtml); //43495129188
        $handle = 'hell0555';
        $dd = \App\Services\Store\Shopify\OnlineStore\Page::update($storeId, '43495260260', $bodyHtml, true, $title, $handle); //43495129188
        var_dump($dd);
        exit;

        //43479367780
        $storeId = 5;
        $pageId = '43479367780';
        $bodyHtml = 'Hello test';
        $published = true;
        $title = 'Hello test';
        $handle = 'hello-test';
        $author = 'Jmiy';
        $metafields = [
            [
                "key" => "new",
                "value" => "new value",
                "value_type" => "string",
                "namespace" => "global",
            ],
        ];
        $dd = \App\Services\Store\Shopify\OnlineStore\Page::update($storeId, $pageId, $bodyHtml, $published, $title, $handle, $author, $metafields); //, $fields = []
        var_dump($dd);
        exit;

//        $firstName = '"' . "656NMM'";
//
//        //$firstName = str_replace("'", "\'", $firstName);
//        var_dump(strtr($firstName, ["'" => "\'"]));
//        exit;
//
//        var_dump(\App\Services\Store\Shopify\Orders\Transaction::getList(2, '1363858096151'));
//        exit;
//
//        var_dump(\App\Services\Store\Shopify\Orders\Order::update(2, '1363858096151', '60 redeem for Customer mateovil@hotmail.fr'));
//        exit;
        //$dd = \App\Services\Store\ShopifyService::deleteCustomer(5, 1902294040676);//1803asdasdas9292996lyf@lyf.com
        //$dd = \App\Services\Store\ShopifyService::countCustomer(1); //1803asdasdas9292996lyf@lyf.com

        $dd = \App\Services\Store\ShopifyService::customerQuery(1, '', $query = 'isevan@126.com'); //, $fields = []
        var_dump($dd);
        exit;

        //            $params = [
//                'store_id' => 1,
//                'country' => 'US', //"US", "CA", "UK", "DE", "FR", "JP", "IN", "IT", "ES"
//                'customer_id' => 9999999,
//                'account' => 'Jmiy_cen@patazon.net', //Quella_xia@patazon.net
//                'remark' => '业务测试优惠券',
//                'group' => 'customer',
//            ];
//            $DD = EmailService::sendCouponEmail($params['store_id'], $params);
//            var_dump($DD);exit;

        $sql = "select c1.customer_id,c1.account,c1.store_id,ci1.country,ci1.first_name,ci1.last_name
from crm_customer c1
LEFT JOIN  crm_customer_info ci1 ON ci1.customer_id=c1.customer_id
where c1.customer_id in(SELECT
MIN(c.customer_id) AS customer_id
FROM crm_customer c
LEFT JOIN  crm_customer_info ci ON ci.customer_id=c.customer_id
LEFT JOIN crm_email_history mc ON mc.to_email=c.account AND mc.type='coupon' AND mc.group='customer' AND mc.store_id=1
WHERE c.store_id=1 AND c.ctime>='2019-08-01 00:00:00' AND mc.id IS NULL GROUP BY ci.ip) and c1.store_id=1 AND c1.ctime>='2019-08-01 00:00:00' AND ci1.country!='CN'";
        $data = DB::select($sql);
        $group = 'customer';
        foreach ($data as $key => $item) {
            $params = [
                'store_id' => $item->store_id,
                'country' => $item->country, //"US", "CA", "UK", "DE", "FR", "JP", "IN", "IT", "ES"
                'customer_id' => $item->customer_id,
                'account' => $item->account, //Quella_xia@patazon.net
                'remark' => '2019-08-0318:58:00:00补发2019-08-01 06:00:00以后由于没有coupon',
                'group' => $group,
                'first_name' => $item->first_name,
                'last_name' => $item->last_name,
            ];
            $DD = EmailService::sendCouponEmail($params['store_id'], $params);
            var_dump($DD);
        }
        exit;

        $data = \App\Services\Store\ShopifyService::customerQuery(5, '', 'Ali');

//        $data = \App\Services\Store\ShopifyService::paidOrder(5,'1363858096151', '4.99', $note = '60 redeem for Customer mateovil@hotmail.fr');//, '2019-01-01 00:00:00'
        var_dump($data);
        exit;

        $storeData = \App\Services\StoreService::getStore('www.ikich.com', $request->input('country', 'default'));
        var_dump($storeData);
        exit;

//        $emailView = \App\Services\DictStoreService::getByTypeAndKey(1, 'email', 'view_coupon', true, true, 'us');
//        var_dump($emailView);exit;
//            $params = [
//                'store_id' => 1,
//                'country' => 'US', //"US", "CA", "UK", "DE", "FR", "JP", "IN", "IT", "ES"
//                'customer_id' => 9999999,
//                'account' => 'Jmiy_cen@patazon.net', //Quella_xia@patazon.net
//                'remark' => '业务测试优惠券',
//                'group' => 'customer',
//            ];
//            $DD = EmailService::sendCouponEmail($params['store_id'], $params);
//            var_dump($DD);exit;

        $countryData = ["US", "CA", "UK", "DE", "FR", "JP", "IN", "IT", "ES"];
        //$countryData = ["US", "CA", "UK", "DE", "FR", "IT", "ES"];
        $group = 'customer';
        foreach ($countryData as $country) {
            $params = [
                'store_id' => 1,
                'country' => $country, //"US", "CA", "UK", "DE", "FR", "JP", "IN", "IT", "ES"
                'customer_id' => 89898989,
                'account' => 'Jmiy_cen@patazon.net', //Quella_xia@patazon.net
                'remark' => '业务测试优惠券',
                'group' => $group,
                'first_name' => 'first_name',
                'last_name' => 'last_name',
            ];
            $DD = EmailService::sendCouponEmail($params['store_id'], $params);
            var_dump($DD);
        }
        exit;

//        $isEmailCoupon = \App\Services\DictStoreService::getByTypeAndKey(1, 'email', 'coupon', true);
//        var_dump($isEmailCoupon);var_dump(empty($isEmailCoupon));exit;
//        $url = 'https://us3.api.mailchimp.com/3.0/lists';
//        $request = [];
//        $rs = $this->sendApiRequestByCurl($url, $request);
//        exit;
//        $countryData = ["US", "CA", "UK", "DE", "FR", "JP", "IN", "IT", "ES"];
//        $group = 'customer';
//        foreach ($countryData as $country) {
//            $params = [
//                'store_id' => 1,
//                'country' => $country, //"US", "CA", "UK", "DE", "FR", "JP", "IN", "IT", "ES"
//                'customer_id' => 95314,
//                'account' => 'misue2017@yahoo.com', //Quella_xia@patazon.net
//                'remark' => '业务测试优惠券',
//                'group' => $group,
//            ];
//            $DD = EmailService::sendCouponEmail($params['store_id'], $params);
//            var_dump($DD);
//        }
//        exit;
//        $user = \App\Models\Customer::where(['store_id' => 1, 'account' => '56565'])->first();
//        var_dump($user);exit;
        $group = 'customer';
        $params = [
            'store_id' => 1,
            'country' => 'UK', //"US", "CA", "UK", "DE", "FR", "JP", "IN", "IT", "ES"
            'customer_id' => 109633,
            'account' => 'Jmiy_cen@patazon.net', //Jmiy_cen@patazon.net
            'remark' => '2019-07-16 15:51:00补发UK的优惠券',
            'group' => $group,
        ];
        $DD = EmailService::sendCouponEmail($params['store_id'], $params);
        var_dump($DD);
        exit;
//        var_dump(\App\Services\RankService::updateRandInit(2, 1, 0));
//
//        exit;
//
//        $actId = \App\Services\ActivityService::getValidActIds(1, false, 1, 1);
//        var_dump($actId);
//        exit;
//        $inviteCode = '000D321B';
//        \App\Services\InviteService::handle($inviteCode, 18, 1);
//        $data = \App\Services\RankService::getRankData(1, 1, 1, 0, 1, 10);
//        var_dump($data);
//        exit;
//        $account = 'Jmiy_cen565656568989@patazon.net';
//        $storeId = 1;
//        $key = $storeId . ':' . $account;
//        $tags = config('cache.tags.auth', ['{auth}']);
//        $user = Cache::tags($tags)->remember($key, 6000, function () use($account, $storeId) {
//            return \App\Models\Customer::where(['store_id' => $storeId, 'account' => $account])->first();
//        });
//        var_dump($user);
//        exit;
//        $data = DB::select("SELECT content,ctime FROM crm_api_log WHERE content LIKE '%\"source\":\"3201907\"%' AND keyinfo='/api/shop/customer/signup' AND store_id=2");
//        foreach ($data as $key => $item) {
//            //{"country":"US","first_name":"J","last_name":"Thong","account":"therandomalt@gmail.com","password":"JZSmithy","store_id":"2","invite_code":"1PDPEA3F","source":"3201907"}
//            $content = json_decode($item->content, true);
//            $storeId = $content['store_id']; //被邀请者store_id
//            $account = $content['account']; //被邀请者
//            $customer = \App\Services\CustomerService::customerExists($storeId, 0, $account, 0, true);
//            if ($customer) {
//                //删除邀请流水
//                $inviteCode = $content['invite_code'];
//                $inviteCustomerId = $customer->customer_id;
//                $_customer_id = \App\Models\InviteCode::where(['invite_code' => $inviteCode])->limit(1)->value('customer_id'); //获取拥有 $invite_code 的客户id
//                $where = [
//                    'customer_id' => $_customer_id, //邀请者id
//                ];
//                \App\Models\InviteHistory::where($where)->delete();
//
//                if ($key == 0) {
//                    //删除邀请排行榜数据
//                    $where = [
//                        'type' => 2, //邀请客户id
//                    ];
//                    \App\Services\RankService::createModel($storeId, 'Rank')->where($where)->forceDelete();
//                }
//            }
//        };
//
//        foreach ($data as $item) {
//            //{"country":"US","first_name":"J","last_name":"Thong","account":"therandomalt@gmail.com","password":"JZSmithy","store_id":"2","invite_code":"1PDPEA3F","source":"3201907"}
//            $content = json_decode($item->content, true);
//            $storeId = $content['store_id']; //被邀请者store_id
//            $account = $content['account']; //被邀请者
//            $customer = \App\Services\CustomerService::customerExists($storeId, 0, $account, 0, true);
//            if ($customer) {
//                $inviteCode = $content['invite_code'];
//                $inviteCustomerId = $customer->customer_id;
//                $ctime = $item->ctime;
//                \App\Services\InviteService::handle($inviteCode, $inviteCustomerId, $storeId, $ctime, $ctime);
//            }
//        };
//
//        $key = [
//            '1:1:share_num',
//            '1:0:share_num',
//            '1:1:interest_num',
//            '1:0:interest_num',
//            '1:0:interest_num',
//            '2:1:1',
//            '2:1:1:lantern',
//            '2:1:2',
//            'vote:2:1',
//            \App\Services\RankService::getRankKey(1, 1, 0), //mpow总榜
//            \App\Services\RankService::getMpowDayRankKey(1, 1), //mpow日榜
//        ];
//        var_dump(\App\Services\RankService::del($key));
//        exit;
//
//
//
//        var_dump(\App\Services\RankService::getRankKey(2, 1, 0));
//        exit;
//
//        $storeId = 1;
//        $customerId = 115797;
//        $account = 'rlira@pm.me';
//        $code = 'zrEDtZ';
//        $inviteCode = 'Us15NLPd';
//        $country = 'US';
//        $orderno = '';
//        $ip = '';
//        $createdAt = '';
//        $inviteId = 99999;
//        $handleActivate = 1;
//        $rs = EmailService::sendActivateEmail($storeId, $customerId, $account, $code, $inviteCode, $country, $orderno, $ip, '会员激活', $createdAt, $inviteId, $handleActivate);
//
//        var_dump($rs);
//        exit;
//
//
//        \Illuminate\Support\Facades\DB::enableQueryLog();
//        $customerId = 1;
//        $data = [];
//        $customerInfoData = \App\Services\RankService::getCustomerData($customerId, $data);
//        var_dump(\Illuminate\Support\Facades\DB::getQueryLog());
//        var_dump($customerInfoData);
//        exit;
//
//        $this->geoip($request);
//        exit;
//
//        $this->imagick($request);
//        exit;
//
//        $this->excel($request);
//        exit;
//
//        $zsetKey = 'testlimit';
//        //Redis::zadd($zsetKey, 0, $zsetKey);
//        //Redis::expire($zsetKey, 30);
//        var_dump(Redis::exists($zsetKey));
//        exit;
//
//        $key = 'testlimit';
//        $tags = config('cache.tags.shareLimt');
//        $shareLimt = Cache::tags($tags)->get($key);
//        if ($shareLimt > 9) {
//            var_dump($shareLimt);
//            exit;
//        }
//
//        if (Cache::tags($tags)->has($key)) {
//            Cache::tags($tags)->increment($key);
//        } else {
//            $ttl = 60; //缓存时间 单位秒
//            Cache::tags($tags)->put($key, 1, $ttl);
//        }
//
//        var_dump($shareLimt);
//        exit;
//
//
//        $account = 'slickcase@rocketmail.com';
//        $start = strrpos($account, '.');
//        $start = $start !== false ? $start : -3;
//        var_dump($account, $start);
//        var_dump(substr($account, $start));
//        var_dump(substr($account, 0, 3) . '******' . ($start != -3 ? '' : '.') . substr($account, $start));
//        exit;
//
//        //发送优惠券邮件
//        $storeId = 1;
//        $account = 'Jmiy_cen@patazon.net';
//        $country = 'IT';
//        $firstName = 'zois';
//        $lastName = 'steriotis';
//        $group = 'customer';
//        $ip = '';
//        $remark = '补发优惠券';
//        $createdAt = '';
//        $dd = \App\Services\SubcribeService::handle($storeId, $account, $country, $firstName, $lastName, $group, $ip, $remark, $createdAt);
//        var_dump($dd);
//        exit;
        //print_r(\App\Services\Store\ShopifyService::createCustomer(1, 'Jmiy_cen1@patazon.net', '123456'));
        //var_dump(\App\Services\Store\ShopifyService::customerAccessTokenCreate(1, 'Jmiy_cen1@patazon.net', '123456'));
        //exit;
//        $data = \App\Util\FunctionHelper::getHistoryData([
//                    'customer_id' => 1,
//                    'value' => 500,
//                    'add_type' => 1,
//                    'action' => 'signup',
//                    'ext_id' => 1,
//                    'ext_type' => 'customer',
//                        ], ['store_id' => 2]);
//        \App\Services\ExpService::handle($data); //记录积分流水
//        exit;
//        $dd = \App\Services\ProductService::sync();
//        var_dump($dd);
//        exit;
//        $group = 'subcribe';
//        $params = [
//            'store_id' => 1,
//            'country' => 'IT', //"US", "CA", "UK", "DE", "FR", "JP", "IN", "IT", "ES"
//            'customer_id' => 99999,
//            'account' => 'palatanos@mecoach.it', //Jmiy_cen@patazon.net
//            'remark' => '2019-06-18错误发送了FR的优惠券，应该发送IT的优惠券，2019-06-18 19:18:30补发IT的优惠券',
//            'group' => $group,
//        ];
//        $DD = EmailService::sendCouponEmail($params['store_id'], $params);
//        var_dump($DD);
        //初始化排行榜 $storeId . ':' . $actId . ':' . $type
        $key = [
            '1:1:share_num',
            '1:0:share_num',
            '1:1:interest_num',
            '1:0:interest_num',
            '1:0:interest_num',
            '2:1:1',
            '2:1:2',
            'vote:2:1',
        ];
        var_dump(\App\Services\RankService::del($key));
        exit;

        $countryData = ["UK", "DE"];
        foreach ($countryData as $country) {
            $params = [
                'store_id' => 1,
                'country' => $country, //"US", "CA", "UK", "DE", "FR", "JP", "IN", "IT", "ES"
                'customer_id' => 99999,
                'account' => 'Jmiy_cen@patazon.net', //Quella_xia@patazon.net
            ];
            $DD = EmailService::sendCouponEmail($params['store_id'], $params);
            var_dump($DD);
        }

        $countryData = ["UK", "DE"];
        foreach ($countryData as $country) {
            $params = [
                'store_id' => 1,
                'country' => $country, //"US", "CA", "UK", "DE", "FR", "JP", "IN", "IT", "ES"
                'customer_id' => 99999,
                'account' => 'xiaqq2017@gmail.com', //Quella_xia@patazon.net
            ];
            $DD = EmailService::sendCouponEmail($params['store_id'], $params);
            var_dump($DD);
        }

        $countryData = ["UK", "DE"];
        foreach ($countryData as $country) {
            $params = [
                'store_id' => 1,
                'country' => $country, //"US", "CA", "UK", "DE", "FR", "JP", "IN", "IT", "ES"
                'customer_id' => 99999,
                'account' => 'Quella_xia@patazon.net', //Quella_xia@patazon.net
            ];
            $DD = EmailService::sendCouponEmail($params['store_id'], $params);
            var_dump($DD);
        }

        $countryData = ["UK", "DE"];
        foreach ($countryData as $country) {
            $params = [
                'store_id' => 1,
                'country' => $country, //"US", "CA", "UK", "DE", "FR", "JP", "IN", "IT", "ES"
                'customer_id' => 99999,
                'account' => 'Alice_huang@patazon.net',
            ];
            $DD = EmailService::sendCouponEmail($params['store_id'], $params);
            var_dump($DD);
        }

//        $countryData = ["US", "CA", "UK", "DE", "FR", "JP", "IN", "IT", "ES"];
//        foreach ($countryData as $country) {
//            $params = [
//                'store_id' => 1,
//                'country' => $country, //"US", "CA", "UK", "DE", "FR", "JP", "IN", "IT", "ES"
//                'customer_id' => 99999,
//                'account' => 'xiaqq2017@gmail.com',//Quella_xia@patazon.net
//            ];
//            $DD = EmailService::sendCouponEmail($params['store_id'], $params);
//            var_dump($DD);
//        }
        exit;

        $storeId = 2;
        $createdAtMin = '2019-06-17 00:00:00';
        $createdAtMax = '2019-06-17 23:59:59';
        $ids = [];
        $sinceId = '';
        $limit = 250;
        $source = 620190618151300;
        //$retData = \App\Services\Store\ShopifyService::getCustomer($storeId, $createdAtMin, $createdAtMax, $ids, $sinceId, $limit, $source);

        $requestData = [
            'start_time' => $createdAtMin,
            'end_time' => $createdAtMax,
            'limit' => 20,
        ];
        $source = 6;
        $retData = \App\Services\CustomerService::sync($storeId, $requestData, $source);
        var_dump($retData);
        exit;

        //初始化排行榜 $storeId . ':' . $actId . ':' . $type
        $key = [
            '1:1:share_num',
            '1:0:share_num',
            '1:1:interest_num',
            '1:0:interest_num',
            '1:0:interest_num',
            '2:1:1',
            '2:1:2',
            'vote:2:1',
        ];
        var_dump(\App\Services\RankService::del($key));

        //发送优惠券邮件
        $storeId = 1;
        $account = 'zois95@gmail.com';
        $country = 'IT';
        $firstName = 'zois';
        $lastName = 'steriotis';
        $group = 'customer';
        $ip = '';
        $remark = '补发优惠券';
        $createdAt = '';
        \App\Services\SubcribeService::handle($storeId, $account, $country, $firstName, $lastName, $group, $ip, $remark, $createdAt);
        exit;


        //补发优惠券
        $data = DB::select("SELECT a.store_id,a.account,d.country,d.first_name,d.last_name
FROM crm_customer a
LEFT JOIN crm_email_history h ON h.to_email=a.account AND h.ctime>'2019-05-06 00:00:00'
LEFT JOIN crm_customer_info d ON d.customer_id=a.customer_id
WHERE a.store_id=1 AND a.ctime >'2019-05-06 00:00:00'
AND h.to_email IS NULL
AND d.country NOT IN('CN','HK')
AND a.account NOT LIKE '%qq.com%'
AND a.account NOT LIKE '%.net%'
AND a.account NOT LIKE '%163.com%'");
        foreach ($data as $item) {
            //发送优惠券邮件
            $storeId = $item->store_id;
            $account = $item->account;
            $country = $item->country;
            $firstName = $item->first_name;
            $lastName = $item->last_name;
            $group = 'customer';
            $ip = '';
            $remark = '补发优惠券';
            $createdAt = '';
            \App\Services\SubcribeService::handle($storeId, $account, $country, $firstName, $lastName, $group, $ip, $remark, $createdAt);
        };

        exit;

        $orderInfo = \App\Services\CompanyApiService::getOrder('114-4259678-0445827', 'US', true);
        var_dump($orderInfo);
        exit;

//        $fromsite = 'referer';
//        $type = 'record';
//        $content = 'content';
//        $keyinfo = '';
//        $subkeyinfo = '';
//        $ip = 'ip';
//        $ctime = '';
//        var_dump(\App\Services\LogService::addApiLog($fromsite, $type, $content, $keyinfo, $subkeyinfo, $ip, $ctime));
//        exit;
        //exit;
        $where = [['keyinfo', '=', '/api/shop/customer/signup'], ['from_site', '!=', 'http://www.l.com']];
        \App\Models\ApiLog::where($where)->select(['content', 'ctime', 'id'])
                ->chunk(1, function ($data) {
                    foreach ($data as $item) {
                        //$url = 'https://brand-api.patozon.net/api/shop/customer/signup';
                        $url = 'http://127.0.0.1:8006/api/shop/customer/signup';
                        $request = json_decode($item->content, true);
                        $request['ctime'] = $item->ctime->toDateTimeString();

                        var_dump($item->id);
                        var_dump(Carbon::now()->toDateTimeString());
                        var_dump($item->ctime, '2019-05-30 12:47:42');
                        exit;
                        //$rs = $this->sendApiRequestByCurl($url, $request);
                        return false;
                    }
                });



        return response()->json(['test' => 'test'], 200);

//        $params = [
//            'start_time' => '2019-05-20 00:00:00',
//            'end_time' => '2019-05-21 00:00:00',
//            'limit' => 100,
//            'store_id' => 1,
//        ];
//        $dd = \App\Services\Store\MpowService::syncCustomer($params);
//        var_dump($dd);
//        exit;

        $storeId = 1;
        $account = 'mchicoine@rscj.org';
        $orderno = '114-2256242-7944208';
        $country = 'US';
        $type = 'platform';
        $ret = OrderService::bind($storeId, $account, $orderno, $country, $type);
        var_dump($ret);
        exit;

//        date_default_timezone_set('America/Los_Angeles'); //设置app时区
//        var_dump(Carbon::now()->toDateTimeString());exit;
//        var_dump(get_called_class());exit;
//        $params = [
//            'store_id' => 1,
//            'country' => 'US', //"US", "CA", "UK", "DE", "FR", "JP", "IN", "IT", "ES"
//            'customer_id' => 99999,
//            'account' => 'Jmiy_cen@patazon.net',
//        ];
//
//        //$params = json_decode('{"email":"5656563@qq.com","store_id":"1","country":"US","to":"5656563@qq.com","group":"subcribe","account":"5656563@qq.com"}', TRUE);//
//        $DD = EmailService::sendCouponEmail($params['store_id'], $params);
//        return Response::json($DD);
//        exit;
//
//
//        $DD = EmailService::sendToAdmin(1, 'coupon库存不足---测试', '官网：' . 2 . ' coupon 库存不足');
//        var_dump($DD);
//        exit;
        // 当前route---做seo定制url时这个方法很有用
        $routeInfo = app('request')->route();
        print_r($routeInfo);
        exit;
//
//        $data = [
//            'store_id' => 1,
//            'customer_id' => 1,
//            'code' => 'code',
//        ];
//        $url = route('customer_activate_get', ['data' => encrypt(json_encode($data))]);
//        var_dump($url);
//        exit;
//        $query = app()->make(\App\Models\Erp\Amazon\AmazonOrder::class);
//        $country = 'DS';
//        if ($country != "US") {
//            $query->setTable(\App\Models\Erp\Amazon\AmazonOrder::$tablePrefix . '_' . strtolower($country));
//        }
//
//        var_dump($query->getTable());
//
//        exit;
//
//
//        $DD = EmailService::sendToAdmin(1, 'coupon库存不足', '官网：' . 2 . ' coupon 库存不足');
//        var_dump($DD);
//        exit;
        //批量绑定  邀请码  上线的时候执行一遍
//        \App\Models\Customer::select('customer_id')
//                ->chunk(100, function ($oaData) {
//                    $data = [];
//                    foreach ($oaData as $item) {
//                        \App\Models\InviteCode::where('customer_id', 0)->limit(1)->update(['customer_id' => $item->customer_id]);
//                    }
//                });
//        exit;
//        phpinfo();
//        exit;
    }

    /**
     * 发送api请求
     * @param string $api_url 请求url 要绝对地址
     * @param type $request  请求参数
     */
    public function sendApiRequestByCurl($api_url, $request, $headers = []) {


//        说明
//        bool curl_setopt ( resource $ch , int $option , mixed $value )
//
//        为给定的cURL会话句柄设置一个选项。
//        参数
//
//        ch
//
//            由 curl_init() 返回的 cURL 句柄。
//        option
//
//            需要设置的CURLOPT_XXX选项。
//        value
//
//            将设置在option选项上的值。
//
//            对于下面的这些option的可选参数，value应该被设置一个bool类型的值：
//            选项 	可选value值 	备注
//            CURLOPT_AUTOREFERER 	当根据Location:重定向时，自动设置header中的Referer:信息。
//            CURLOPT_BINARYTRANSFER 	在启用CURLOPT_RETURNTRANSFER的时候，返回原生的（Raw）输出。
//            CURLOPT_COOKIESESSION 	启用时curl会仅仅传递一个session cookie，忽略其他的cookie，默认状况下cURL会将所有的cookie返回给服务端。session cookie是指那些用来判断服务器端的session是否有效而存在的cookie。
//            CURLOPT_CRLF 	启用时将Unix的换行符转换成回车换行符。
//            CURLOPT_DNS_USE_GLOBAL_CACHE 	启用时会启用一个全局的DNS缓存，此项为线程安全的，并且默认启用。
//            CURLOPT_FAILONERROR 	显示HTTP状态码，默认行为是忽略编号小于等于400的HTTP信息。
//            CURLOPT_FILETIME 	启用时会尝试修改远程文档中的信息。结果信息会通过 curl_getinfo()函数的CURLINFO_FILETIME选项返回。 curl_getinfo().
//            CURLOPT_FOLLOWLOCATION 	启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器，使用CURLOPT_MAXREDIRS可以限定递归返回的数量。
//            CURLOPT_FORBID_REUSE 	在完成交互以后强迫断开连接，不能重用。
//            CURLOPT_FRESH_CONNECT 	强制获取一个新的连接，替代缓存中的连接。
//            CURLOPT_FTP_USE_EPRT 	启用时当FTP下载时，使用EPRT (或 LPRT)命令。设置为FALSE时禁用EPRT和LPRT，使用PORT命令 only.
//            CURLOPT_FTP_USE_EPSV 	启用时，在FTP传输过程中回复到PASV模式前首先尝试EPSV命令。设置为FALSE时禁用EPSV命令。
//            CURLOPT_FTPAPPEND 	启用时追加写入文件而不是覆盖它。
//            CURLOPT_FTPASCII 	CURLOPT_TRANSFERTEXT的别名。
//            CURLOPT_FTPLISTONLY 	启用时只列出FTP目录的名字。
//            CURLOPT_HEADER 	启用时会将头文件的信息作为数据流输出。
//            CURLINFO_HEADER_OUT 	启用时追踪句柄的请求字符串。 	从 PHP 5.1.3 开始可用。CURLINFO_前缀是故意的(intentional)。
//            CURLOPT_HTTPGET 	启用时会设置HTTP的method为GET，因为GET是默认是，所以只在被修改的情况下使用。
//            CURLOPT_HTTPPROXYTUNNEL 	启用时会通过HTTP代理来传输。
//            CURLOPT_MUTE 	启用时将cURL函数中所有修改过的参数恢复默认值。
//            CURLOPT_NETRC 	在连接建立以后，访问~/.netrc文件获取用户名和密码信息连接远程站点。
//            CURLOPT_NOBODY 	启用时将不对HTML中的BODY部分进行输出。
//            CURLOPT_NOPROGRESS
//
//            启用时关闭curl传输的进度条，此项的默认设置为启用。
//
//                Note:
//
//                PHP自动地设置这个选项为TRUE，这个选项仅仅应当在以调试为目的时被改变。
//
//
//            CURLOPT_NOSIGNAL 	启用时忽略所有的curl传递给php进行的信号。在SAPI多线程传输时此项被默认启用。 	cURL 7.10时被加入。
//            CURLOPT_POST 	启用时会发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。
//            CURLOPT_PUT 	启用时允许HTTP发送文件，必须同时设置CURLOPT_INFILE和CURLOPT_INFILESIZE。
//            CURLOPT_RETURNTRANSFER 	将 curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
//            CURLOPT_SSL_VERIFYPEER 	禁用后cURL将终止从服务端进行验证。使用CURLOPT_CAINFO选项设置证书使用CURLOPT_CAPATH选项设置证书目录 如果CURLOPT_SSL_VERIFYPEER(默认值为2)被启用，CURLOPT_SSL_VERIFYHOST需要被设置成TRUE否则设置为FALSE。 	自cURL 7.10开始默认为TRUE。从cURL 7.10开始默认绑定安装。
//            CURLOPT_TRANSFERTEXT 	启用后对FTP传输使用ASCII模式。对于LDAP，它检索纯文本信息而非HTML。在Windows系统上，系统不会把STDOUT设置成binary模式。
//            CURLOPT_UNRESTRICTED_AUTH 	在使用CURLOPT_FOLLOWLOCATION产生的header中的多个locations中持续追加用户名和密码信息，即使域名已发生改变。
//            CURLOPT_UPLOAD 	启用后允许文件上传。
//            CURLOPT_VERBOSE 	启用时会汇报所有的信息，存放在STDERR或指定的CURLOPT_STDERR中。
//
//            对于下面的这些option的可选参数，value应该被设置一个integer类型的值：
//            选项 	可选value值 	备注
//            CURLOPT_BUFFERSIZE 	每次获取的数据中读入缓存的大小，但是不保证这个值每次都会被填满。 	在cURL 7.10中被加入。
//            CURLOPT_CLOSEPOLICY 	不是CURLCLOSEPOLICY_LEAST_RECENTLY_USED就是CURLCLOSEPOLICY_OLDEST，还存在另外三个CURLCLOSEPOLICY_，但是cURL暂时还不支持。
//            CURLOPT_CONNECTTIMEOUT 	在发起连接前等待的时间，如果设置为0，则无限等待。单位：秒
//            CURLOPT_CONNECTTIMEOUT_MS 	尝试连接等待的时间，以毫秒为单位。如果设置为0，则无限等待。 	在cURL 7.16.2中被加入。从PHP 5.2.3开始可用。
//            CURLOPT_DNS_CACHE_TIMEOUT 	设置在内存中保存DNS信息的时间，默认为120秒。
//            CURLOPT_FTPSSLAUTH 	FTP验证方式：CURLFTPAUTH_SSL (首先尝试SSL)，CURLFTPAUTH_TLS (首先尝试TLS)或CURLFTPAUTH_DEFAULT (让cURL自动决定)。 	在cURL 7.12.2中被加入。
//            CURLOPT_HTTP_VERSION 	CURL_HTTP_VERSION_NONE (默认值，让cURL自己判断使用哪个版本)，CURL_HTTP_VERSION_1_0 (强制使用 HTTP/1.0)或CURL_HTTP_VERSION_1_1 (强制使用 HTTP/1.1)。
//            CURLOPT_HTTPAUTH
//
//            使用的HTTP验证方法，可选的值有：CURLAUTH_BASIC、CURLAUTH_DIGEST、CURLAUTH_GSSNEGOTIATE、CURLAUTH_NTLM、CURLAUTH_ANY和CURLAUTH_ANYSAFE。
//
//            可以使用|位域(或)操作符分隔多个值，cURL让服务器选择一个支持最好的值。
//
//            CURLAUTH_ANY等价于CURLAUTH_BASIC | CURLAUTH_DIGEST | CURLAUTH_GSSNEGOTIATE | CURLAUTH_NTLM.
//
//            CURLAUTH_ANYSAFE等价于CURLAUTH_DIGEST | CURLAUTH_GSSNEGOTIATE | CURLAUTH_NTLM.
//
//            CURLOPT_INFILESIZE 	设定上传文件的大小限制，字节(byte)为单位。
//            CURLOPT_LOW_SPEED_LIMIT 	当传输速度小于CURLOPT_LOW_SPEED_LIMIT时(bytes/sec)，PHP会根据CURLOPT_LOW_SPEED_TIME来判断是否因太慢而取消传输。
//            CURLOPT_LOW_SPEED_TIME 	当传输速度小于CURLOPT_LOW_SPEED_LIMIT时(bytes/sec)，PHP会根据CURLOPT_LOW_SPEED_TIME来判断是否因太慢而取消传输。
//            CURLOPT_MAXCONNECTS 	允许的最大连接数量，超过是会通过CURLOPT_CLOSEPOLICY决定应该停止哪些连接。
//            CURLOPT_MAXREDIRS 	指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的。
//            CURLOPT_PORT 	用来指定连接端口。（可选项）
//            CURLOPT_PROTOCOLS
//
//            CURLPROTO_*的位域指。如果被启用，位域值会限定libcurl在传输过程中有哪些可使用的协议。这将允许你在编译libcurl时支持众多协议，但是限制只是用它们中被允许使用的一个子集。默认libcurl将会使用全部它支持的协议。参见CURLOPT_REDIR_PROTOCOLS.
//
//            可用的协议选项为：CURLPROTO_HTTP、CURLPROTO_HTTPS、CURLPROTO_FTP、CURLPROTO_FTPS、CURLPROTO_SCP、CURLPROTO_SFTP、CURLPROTO_TELNET、CURLPROTO_LDAP、CURLPROTO_LDAPS、CURLPROTO_DICT、CURLPROTO_FILE、CURLPROTO_TFTP、CURLPROTO_ALL
//                在cURL 7.19.4中被加入。
//            CURLOPT_PROXYAUTH 	HTTP代理连接的验证方式。使用在CURLOPT_HTTPAUTH中的位域标志来设置相应选项。对于代理验证只有CURLAUTH_BASIC和CURLAUTH_NTLM当前被支持。 	在cURL 7.10.7中被加入。
//            CURLOPT_PROXYPORT 	代理服务器的端口。端口也可以在CURLOPT_PROXY中进行设置。
//            CURLOPT_PROXYTYPE 	不是CURLPROXY_HTTP (默认值) 就是CURLPROXY_SOCKS5。 	在cURL 7.10中被加入。
//            CURLOPT_REDIR_PROTOCOLS 	CURLPROTO_*中的位域值。如果被启用，位域值将会限制传输线程在CURLOPT_FOLLOWLOCATION开启时跟随某个重定向时可使用的协议。这将使你对重定向时限制传输线程使用被允许的协议子集默认libcurl将会允许除FILE和SCP之外的全部协议。这个和7.19.4预发布版本种无条件地跟随所有支持的协议有一些不同。关于协议常量，请参照CURLOPT_PROTOCOLS。 	在cURL 7.19.4中被加入。
//            CURLOPT_RESUME_FROM 	在恢复传输时传递一个字节偏移量（用来断点续传）。
//            CURLOPT_SSL_VERIFYHOST 	1 检查服务器SSL证书中是否存在一个公用名(common name)。译者注：公用名(Common Name)一般来讲就是填写你将要申请SSL证书的域名 (domain)或子域名(sub domain)。2 检查公用名是否存在，并且是否与提供的主机名匹配。
//            CURLOPT_SSLVERSION 	使用的SSL版本(2 或 3)。默认情况下PHP会自己检测这个值，尽管有些情况下需要手动地进行设置。
//            CURLOPT_TIMECONDITION 	如果在CURLOPT_TIMEVALUE指定的某个时间以后被编辑过，则使用CURL_TIMECOND_IFMODSINCE返回页面，如果没有被修改过，并且CURLOPT_HEADER为true，则返回一个"304 Not Modified"的header， CURLOPT_HEADER为false，则使用CURL_TIMECOND_IFUNMODSINCE，默认值为CURL_TIMECOND_IFUNMODSINCE。
//            CURLOPT_TIMEOUT 	设置cURL允许执行的最长秒数。 默认为0，意思是永远不会断开链接
//            CURLOPT_TIMEOUT_MS 	设置cURL允许执行的最长毫秒数。 	在cURL 7.16.2中被加入。从PHP 5.2.3起可使用。
//            CURLOPT_TIMEVALUE 	设置一个CURLOPT_TIMECONDITION使用的时间戳，在默认状态下使用的是CURL_TIMECOND_IFMODSINCE。
//
//            对于下面的这些option的可选参数，value应该被设置一个string类型的值：
//            选项 	可选value值 	备注
//            CURLOPT_CAINFO 	一个保存着1个或多个用来让服务端验证的证书的文件名。这个参数仅仅在和CURLOPT_SSL_VERIFYPEER一起使用时才有意义。 .
//            CURLOPT_CAPATH 	一个保存着多个CA证书的目录。这个选项是和CURLOPT_SSL_VERIFYPEER一起使用的。
//            CURLOPT_COOKIE 	设定HTTP请求中"Cookie: "部分的内容。多个cookie用分号分隔，分号后带一个空格(例如， "fruit=apple; colour=red")。
//            CURLOPT_COOKIEFILE 	包含cookie数据的文件名，cookie文件的格式可以是Netscape格式，或者只是纯HTTP头部信息存入文件。
//            CURLOPT_COOKIEJAR 	连接结束后保存cookie信息的文件。
//            CURLOPT_CUSTOMREQUEST
//
//            使用一个自定义的请求信息来代替"GET"或"HEAD"作为HTTP请求。这对于执行"DELETE" 或者其他更隐蔽的HTTP请求。有效值如"GET"，"POST"，"CONNECT"等等。也就是说，不要在这里输入整个HTTP请求。例如输入"GET /index.html HTTP/1.0\r\n\r\n"是不正确的。
//
//                Note:
//
//                在确定服务器支持这个自定义请求的方法前不要使用。
//
//
//            CURLOPT_EGDSOCKET 	类似CURLOPT_RANDOM_FILE，除了一个Entropy Gathering Daemon套接字。
//            CURLOPT_ENCODING 	HTTP请求头中"Accept-Encoding: "的值。支持的编码有"identity"，"deflate"和"gzip"。如果为空字符串""，请求头会发送所有支持的编码类型。 	在cURL 7.10中被加入。
//            CURLOPT_FTPPORT 	这个值将被用来获取供FTP"POST"指令所需要的IP地址。"POST"指令告诉远程服务器连接到我们指定的IP地址。这个字符串可以是纯文本的IP地址、主机名、一个网络接口名（UNIX下）或者只是一个'-'来使用默认的IP地址。
//            CURLOPT_INTERFACE 	网络发送接口名，可以是一个接口名、IP地址或者是一个主机名。
//            CURLOPT_KRB4LEVEL 	KRB4 (Kerberos 4) 安全级别。下面的任何值都是有效的(从低到高的顺序)："clear"、"safe"、"confidential"、"private".。如果字符串和这些都不匹配，将使用"private"。这个选项设置为NULL时将禁用KRB4 安全认证。目前KRB4 安全认证只能用于FTP传输。
//            CURLOPT_POSTFIELDS 	全部数据使用HTTP协议中的"POST"操作来发送。要发送文件，在文件名前面加上@前缀并使用完整路径。这个参数可以通过urlencoded后的字符串类似'para1=val1&para2=val2&...'或使用一个以字段名为键值，字段数据为值的数组。如果value是一个数组，Content-Type头将会被设置成multipart/form-data。
//            CURLOPT_PROXY 	HTTP代理通道。
//            CURLOPT_PROXYUSERPWD 	一个用来连接到代理的"[username]:[password]"格式的字符串。
//            CURLOPT_RANDOM_FILE 	一个被用来生成SSL随机数种子的文件名。
//            CURLOPT_RANGE 	以"X-Y"的形式，其中X和Y都是可选项获取数据的范围，以字节计。HTTP传输线程也支持几个这样的重复项中间用逗号分隔如"X-Y,N-M"。
//            CURLOPT_REFERER 	在HTTP请求头中"Referer: "的内容。
//            CURLOPT_SSL_CIPHER_LIST 	一个SSL的加密算法列表。例如RC4-SHA和TLSv1都是可用的加密列表。
//            CURLOPT_SSLCERT 	一个包含PEM格式证书的文件名。
//            CURLOPT_SSLCERTPASSWD 	使用CURLOPT_SSLCERT证书需要的密码。
//            CURLOPT_SSLCERTTYPE 	证书的类型。支持的格式有"PEM" (默认值), "DER"和"ENG"。 	在cURL 7.9.3中被加入。
//            CURLOPT_SSLENGINE 	用来在CURLOPT_SSLKEY中指定的SSL私钥的加密引擎变量。
//            CURLOPT_SSLENGINE_DEFAULT 	用来做非对称加密操作的变量。
//            CURLOPT_SSLKEY 	包含SSL私钥的文件名。
//            CURLOPT_SSLKEYPASSWD
//
//            在CURLOPT_SSLKEY中指定了的SSL私钥的密码。
//
//                Note:
//
//                由于这个选项包含了敏感的密码信息，记得保证这个PHP脚本的安全。
//
//
//            CURLOPT_SSLKEYTYPE 	CURLOPT_SSLKEY中规定的私钥的加密类型，支持的密钥类型为"PEM"(默认值)、"DER"和"ENG"。
//            CURLOPT_URL 	需要获取的URL地址，也可以在 curl_init()函数中设置。
//            CURLOPT_USERAGENT 	在HTTP请求中包含一个"User-Agent: "头的字符串。
//            CURLOPT_USERPWD 	传递一个连接中需要的用户名和密码，格式为："[username]:[password]"。
//
//            对于下面的这些option的可选参数，value应该被设置一个数组：
//            选项 	可选value值 	备注
//            CURLOPT_HTTP200ALIASES 	200响应码数组，数组中的响应吗被认为是正确的响应，否则被认为是错误的。 	在cURL 7.10.3中被加入。
//            CURLOPT_HTTPHEADER 	一个用来设置HTTP头字段的数组。使用如下的形式的数组进行设置： array('Content-type: text/plain', 'Content-length: 100')
//            CURLOPT_POSTQUOTE 	在FTP请求执行完成后，在服务器上执行的一组FTP命令。
//            CURLOPT_QUOTE 	一组先于FTP请求的在服务器上执行的FTP命令。
//
//            对于下面的这些option的可选参数，value应该被设置一个流资源 （例如使用 fopen()）：
//            选项 	可选value值
//            CURLOPT_FILE 	设置输出文件的位置，值是一个资源类型，默认为STDOUT (浏览器)。
//            CURLOPT_INFILE 	在上传文件的时候需要读取的文件地址，值是一个资源类型。
//            CURLOPT_STDERR 	设置一个错误输出地址，值是一个资源类型，取代默认的STDERR。
//            CURLOPT_WRITEHEADER 	设置header部分内容的写入的文件地址，值是一个资源类型。
//
//            对于下面的这些option的可选参数，value应该被设置为一个回调函数名：
//            选项 	可选value值
//            CURLOPT_HEADERFUNCTION 	设置一个回调函数，这个函数有两个参数，第一个是cURL的资源句柄，第二个是输出的header数据。header数据的输出必须依赖这个函数，返回已写入的数据大小。
//            CURLOPT_PASSWDFUNCTION 	设置一个回调函数，有三个参数，第一个是cURL的资源句柄，第二个是一个密码提示符，第三个参数是密码长度允许的最大值。返回密码的值。
//            CURLOPT_PROGRESSFUNCTION 	设置一个回调函数，有三个参数，第一个是cURL的资源句柄，第二个是一个文件描述符资源，第三个是长度。返回包含的数据。
//            CURLOPT_READFUNCTION 	回调函数名。该函数应接受三个参数。第一个是 cURL resource；第二个是通过选项 CURLOPT_INFILE 传给 cURL 的 stream resource；第三个参数是最大可以读取的数据的数量。回 调函数必须返回一个字符串，长度小于或等于请求的数据量（第三个参数）。一般从传入的 stream resource 读取。返回空字符串作为 EOF（文件结束） 信号。
//            CURLOPT_WRITEFUNCTION 	回调函数名。该函数应接受两个参数。第一个是 cURL resource；第二个是要写入的数据字符串。数 据必须在函数中被保存。函数必须返回准确的传入的要写入数据的字节数，否则传输会被一个错误所中 断。
        /*
          PHP cURL 超时设置 CURLOPT_CONNECTTIMEOUT 和 CURLOPT_TIMEOUT 的区别
          PHP 浏览：... 2016年03月08日

          PHP cURL 的超时设置有两个 CURLOPT_CONNECTTIMEOUT 和 CURLOPT_TIMEOUT，他们的区别是：

          CURLOPT_CONNECTTIMEOUT 用来告诉 PHP 在成功连接服务器前等待多久（连接成功之后就会开始缓冲输出），这个参数是为了应对目标服务器的过载，下线，或者崩溃等可能状况。
          CURLOPT_TIMEOUT 用来告诉成功 PHP 从服务器接收缓冲完成前需要等待多长时间，如果目标是个巨大的文件，生成内容速度过慢或者链路速度过慢，这个参数就会很有用。

          使用 cURL 下载 MP3 文件是一个对开发人员来说不错的例子，CURLOPT_CONNECTTIMEOUT 可以设置为10秒，标识如果服务器10秒内没有响应，脚本就会断开连接，CURLOPT_TIMEOUT 可以设置为100秒，如果MP3文件100秒内没有下载完成，脚本将会断开连接。

          需要注意的是：CURLOPT_TIMEOUT 默认为0，意思是永远不会断开链接。所以不设置的话，可能因为链接太慢，会把 HTTP 资源用完。
         */
        /* 设置COOKIE的存储临时文件 */
        $cookieFile = \App\Util\Curl::getTemporaryCookieFileName();
        //var_dump($cookieFile);

        /* 设置CURL参数并发送请求，获取响应内容 */
        //$requestUrl = "http://openapp.12321.cn/interfaces/report.api/test";
        //$requestUrl = "http://openapp.12321.cn/interfaces/report.api";

        $requestUrl = $api_url;
        $refer = 'https://api-localhost.com/'; //https://www.victsing.com/pages/vip-benefit
        //dump('requestUrl: ' . $requestUrl);
//        $requestUrl = 'http://test.myaora.net:8100/gameApi.php';
//        $refer = 'http://test.myaora.net:8100/';
//
//        $requestUrl = 'http://test.myaora.net:8300/gameApi.php';
//        $refer = 'http://test.myaora.net:8300/';
//        $method = 'aes-128-cbc';
//        $ivlen = openssl_cipher_iv_length($method);
//        $iv = base64_encode(openssl_random_pseudo_bytes($ivlen));
        $iv = '1234567891011121';
        $headers = $headers ? $headers : array(
            //'Cookie: PHPSESSID=123456860918021331410_41067be18b0415d2622e51080ddeac377b98e908_E6_860918021331410_460029194382961_WIFI_8609180213314101381919171315_1000_1_0_JOP40D',
            //'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.2; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
            'Referer: ' . $refer,
            'Version: ' . CURL_HTTP_VERSION_1_1,
            'IvParameterSpec: ' . $iv,
            'API_VERSION: 27', //
            //'Authorization: Bearer fa83e4f46be69a1417fd3de4bf6fa2a1',
            //'Authorization: AUdCZgFK',
            'Authorization: Basic Zm9vOmJhcg==',
            'Content-Type: application/json',
            "Expect:",
            'X-Requested-With: XMLHttpRequest', //告诉服务器，当前请求是 ajax 请求
            //'X-PJAX: '.false,//告诉服务器在收到这样的请求的时候, 要返回 json 数据
            //'X-PJAX: '.true,//告诉服务器在收到这样的请求的时候, 只需要渲染部分页面返回就可以了
            //'Accept: +json',//告诉服务器，要返回 json 数据
            //'Accept: /json', //告诉服务器，要返回 json 数据
            'X-Shopify-Hmac-Sha256: aVB8fEJErbweBCKDsc5MI2kzR8JrfEgUM25Be1NWSQs=',
            'X-Token: 5d3addb5ddaec3a58d3809010adbf427_1564474859',
        );

        //$hmac = 'aVB8fEJErbweBCKDsc5MI2kzR8JrfEgUM25Be1NWSQs=';
//        $headers = array(
//            'Accept: application/json, text/javascript, */*; q=0.01',
//            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
//            'Origin: https://www.victsing.com',
//            'Referer: https://www.victsing.com/pages/invite-sign-in?country=US&first_name=160&last_name=sdddd&account=1606164240%40qq.com&password=ty123456',
//            'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36',
//        );
//        $postFields = array(
//            'account' => 'APP.B84927X', //帐号
//            'packageurl' => 'http://www.baidu.com/test.apk', //被举报软件的url
//            'filemd5' => strtolower(md5(@file_get_contents('http://www.baidu.com/test.apk'))), //软件包的md5值
//            'vcode' => 5, //软件的版本号
//            'packagename' => 'testapk', //被举报的软件的名称
//            'packagetype' => 1, //软件包的类型 1：android安装包 2：sis格式安装包 3：sisx格式安装包 4：jar格式安装包 5：dep 6：ipa 7：jad 8：wgz 9：wgt
//            'appchannel' => 8, //软件所属的频道 0：未知 1: 系统 2: 理财 3: 社交 4: 娱乐 5: 阅读 6: 生活 7: 办公 8: 游戏
//            'orig' => 1, //举报者环境 1：PC客户端 2：PC端网页 3：移动
//            'clientip' => \Base\Library\Utility::getIP(),
//            'descr' => '游戏广告',
//            'reporttime' => date("Y-m-d H:i:s"), //被举报时间
//        );
//
        //var_dump($postFields);/
        if (is_array($request)) {
            $postString = json_encode($request);
            //$postString = http_build_query($request);
        } else {
            $postString = $request;
        }
        //$postString = $request;
        //exit;
        //$postString = http_build_query($request, '&');
        //var_dump($cookieFile);
        //dump($postString);
        //$postString = \Aora\Util\MyCrypt::encrypt($postString, $iv);
        //var_dump($postString);
        $curlOptions = array(
            CURLOPT_URL => $requestUrl, //访问URL
            CURLOPT_REFERER => $refer, //哪个页面链接过来的
            CURLOPT_HTTPHEADER => $headers, //一个用来设置HTTP头字段的数组。使用如下的形式的数组进行设置： array('Content-type: text/plain', 'Content-length: 100')
            CURLOPT_HEADER => false, //获取返回头信息
            CURLOPT_COOKIEFILE => $cookieFile, //请求时发送的cookie所在的文件
            CURLOPT_COOKIEJAR => $cookieFile, //获取结果后cookie存储的文件
            CURLOPT_POST => true, //发送时带有POST参数
            CURLOPT_POSTFIELDS => $postString, //请求的POST参数字符串 全部数据使用HTTP协议中的"POST"操作来发送。要发送文件，在文件名前面加上@前缀并使用完整路径。这个参数可以通过urlencoded后的字符串类似'para1=val1&para2=val2&...'或使用一个以字段名为键值，字段数据为值的数组。如果value是一个数组，Content-Type头将会被设置成multipart/form-data。
            //CURLOPT_CONNECTTIMEOUT => 160, //等待响应的时间
            //CURLOPT_COOKIE => 'PHPSESSID=123456860918021331410_41067be18b0415d2622e51080ddeac377b98e908_GIONEE S10C_864839030524926_460029194382961_WIFBearerI_8609180213314101381919171315_1000_1_0_JOP40D', //用户 cookie 对应 $_SERVER['HTTP_COOKIE']
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 5.1; rv:24.0) Gecko/20100101 Firefox/24.0', //用户浏览器 信息 $_SERVER['HTTP_USER_AGENT']
            CURLOPT_CONNECTTIMEOUT_MS => 1000 * 1000,
            CURLOPT_TIMEOUT_MS => 1000 * 1000,
            CURLOPT_USERNAME => 'jmiy', //设置账号
            CURLOPT_PASSWORD => '7a1cdde20fb3c15e0455a40674003057-us3', //设置密码
        );
        /* 获取响应信息并验证结果 */
        $responseData = \App\Util\Curl::handle($curlOptions, true); //
        $responseText = data_get($responseData, 'responseText', false);

        if ($responseText === false) {
            //unlink($cookieFile);    //删除临时COOKIE文件
        }
        //dump($responseText);
        if ($responseText) {
            //dump($responseText);
            data_set($responseData, 'responseText', json_decode($responseText, true));
        }


        dump('requestUrl: ' . $requestUrl, $postString, $responseText, $responseData);

        return $responseData;
    }

    /**
     * 清空opcache
     * @param Request $request
     */
    public function opcache(Request $request) {

        opcache_reset();
        echo 'opcache cleared======';
        exit;
    }

    /**
     * 清空缓存
     * @param Request $request
     */
    public function clear(Request $request) {

        $data = [];

        //清空绑定缓存
        //$data[] = '清空排行榜缓存===>' . \App\Services\RankService::clearRankCache([1, 2]);
        //清空用户认证缓存
        $tags = config('cache.tags.auth', ['{auth}']);
        $data[] = '清空用户认证缓存===>' . Cache::tags($tags)->flush();

        //清空邀请统计
        $data[] = '清空邀请统计===>' . \App\Services\InviteService::delInviteStatisticsCache(0, 0, 0);

        //清空活动缓存
        $tags = config('cache.tags.activity', ['{activity}']);
        $data[] = '清空活动缓存===>' . Cache::tags($tags)->flush();

        //清空商城字典缓存
        $tags = config('cache.tags.storeDict');
        $data[] = '清空商城字典缓存===>' . Cache::tags($tags)->flush();

        //清空系统字典缓存
        $tags = config('cache.tags.dict');
        $data[] = '清空系统字典缓存===>' . Cache::tags($tags)->flush();

        //清空会员缓存
        $tags = config('cache.tags.customer', ['{customer}']);
        $data[] = '清空会员缓存===>' . Cache::tags($tags)->flush();

        $tags = config('cache.tags.adminCount', ['{adminCount}']);
        $data[] = '清空后台列表总数缓存===>' . Cache::tags($tags)->flush();

        //$data[] = '清空抽奖次数限制缓存===>' . Cache::tags(config('cache.tags.lotteryLimit'))->flush();
        //清空中奖排行榜缓存
        $data[] = '清空中奖排行榜缓存===>' . \App\Services\ActivityWinningService::delRankCache(2, 6);
        $data[] = '清空中奖排行榜缓存===>' . \App\Services\ActivityWinningService::delRankCache(1, 6);
        $data[] = '清空中奖排行榜缓存===>' . \App\Services\ActivityWinningService::delRankCache(3, 3);

        return $request->expectsJson() ? Response::json($data) : var_dump($data);
    }

    public function api(Request $request) {

        $request_data = $request->all();
//        $url = 'http://127.0.0.1:8006/api/shop/customer/edit';
//        $url = 'http://127.0.0.1:8006/api/shop/customer/info';
//        $url = 'http://127.0.0.1:8006/api/shop/share/add';
//        $url = 'https://testapi.patozon.net/api/shop/share/add';
//        $url = 'http://127.0.0.1:8006/api/shop/customer/signup';
//        $url = 'http://127.0.0.1:8006/api/shop/customer/createCustomer';
        //$url = 'http://127.0.0.1:8006/api/shop/activity/getLanternData';
        //$url = 'http://127.0.0.1:8006/api/shop/public/getCountdownTime';
        //$url = 'http://127.0.0.1:8006/api/shop/pub/subcribe';
        //$url = 'http://127.0.0.1:8006/api/shop/public/upload';
        //$url = 'http://127.0.0.1:8006/api/shop/share/update';
        //$url = 'http://127.0.0.1:8006/api/shop/rank/list';
        //$url = 'http://127.0.0.1:8006/api/shop/vote/list';
        //$url = 'http://127.0.0.1:8006/api/shop/vote/vote';
//        $url = 'http://127.0.0.1:8006/api/shop/vote/getRankData';
        $url = 'http://127.0.0.1:8006/api/shop/order/list';
//        $url = 'http://127.0.0.1:8006/api/shop/order/bind';
////        $url = 'http://127.0.0.1:8006/api/shop/order/creditexchange';
        //$url = 'http://127.0.0.1:8006/api/shop/customer/createCustomer';
        //$url = 'http://127.0.0.1:8006/api/shop/product/list';
        //$url = 'http://127.0.0.1:8006/api/shop/invite/getInviteCode';
        //$url = 'http://127.0.0.1:8006/api/shop/customer/getInviteCustomer';
        //$url = 'http://127.0.0.1:8006/api/shop/customer/actReg';
        //$url = 'http://127.0.0.1:8006/api/shop/order/shopify';
        //$url = 'http://127.0.0.1:8006/api/shop/order/2/creatNotice';
//        $url = 'http://127.0.0.1:8006/api/shop/country/list';
//
//        $url = 'http://127.0.0.1:8006/api/shop/activity/product/list';
        //$url = 'http://127.0.0.1:8006/api/shop/activity/apply/insert';
//        $url = 'http://127.0.0.1:8006/api/shop/activity/apply/info';
//        $url = 'http://127.0.0.1:8006/api/shop/activity/apply/product';
        //$url = 'http://127.0.0.1:8006/api/shop/activity/apply/free';
//        $url = 'http://127.0.0.1:8006/api/shop/activity/product/getDetails';
//        $url = 'http://127.0.0.1:8006/api/shop/activity/apply/getAuditStatus';
//        $url = 'http://127.0.0.1:8006/api/shop/activity/getCountdownTime';
//        $url = 'http://127.0.0.1:8006/api/statistical/access/add';
//        $url = 'http://127.0.0.1:8006/api/shop/activity/product/dealIndex';
        //$url = 'http://127.0.0.1:8006/api/shop/email/activate';
        //$url = 'http://127.0.0.1:8006/api/shop/credit/list';
//        $url = 'http://127.0.0.1:8006/api/shop/vote/list';
//        $url = 'http://127.0.0.1:8006/api/shop/vote/vote';
//        $url = 'http://127.0.0.1:8006/api/shop/activity/prize/list';
//        $url = 'http://127.0.0.1:8006/api/shop/activity/winning/list';
//        $url = 'http://127.0.0.1:8006/api/shop/activity/winning/getLotteryNum';
//        $url = 'http://127.0.0.1:8006/api/shop/activity/winning/handle';
//        $url = 'http://127.0.0.1:8006/api/shop/activity/winning/getRankData';
        //$url = 'http://127.0.0.1:8006/api/shop/activity/share/handle';
//        $url = 'http://127.0.0.1:8006/api/shop/activity/address/add';
//        $url = 'http://127.0.0.1:8006/api/shop/activity/address/info';
//        $url = 'http://127.0.0.1:8006/api/shop/activity/helped/handle';
//        $url = 'http://127.0.0.1:8006/api/shop/activity/helped/list';
        //$url = 'http://127.0.0.1:8006/api/shop/survey/info';
        //$url = 'http://127.0.0.1:8006/api/shop/survey/handle';
//        $url = 'http://127.0.0.1:8006/api/shop/contactus/add';
//        $url = 'http://127.0.0.1:8006/api/shop/action/handle';
        //$url = 'http://127.0.0.1:8006/api/shop/rank/list';
        //$url = 'http://192.168.152.128:81/api/admin/store/getStore';
        $url = 'http://192.168.152.128:81/api/shop/activity/getActivityData';
        $url = 'http://192.168.152.128:81/api/shop/reward/getOrderReviewReward';
        $url = 'http://192.168.152.128:81/api/shop/order/review/input';
        $url = 'http://192.168.152.128:81/api/shop/public/upload';
        $url = 'http://192.168.152.128:81/api/common/upload';
        $url = 'http://192.168.152.128:81/api/shop/vote/list';

        $url = 'http://192.168.152.128:81/api/shop/customer/getAvatar';

        //$url = 'https://testapidev.patozon.net/api/common/upload';
        $url = 'http://192.168.152.128:81/api/shop/contactus/add';

        $url = 'http://192.168.152.128:81/api/shop/activity/order/exists';

//        $url = 'http://192.168.152.128:81/api/shop/order/list';
//
//        $url = 'http://192.168.152.128:81/api/shop/customer/createCustomer';
////        $url = 'http://192.168.152.128:81/api/notice/1/Shopify/transaction/create/sandbox';
//        $url = 'http://192.168.152.128:81/api/shop/order/bind';
//        //$url = 'http://192.168.152.128:81/api/shop/order/warrantyList';
//        $url = 'https://testapidev.patozon.net/api/shop/order/bind';
//        $url = 'http://192.168.152.128:81/api/shop/activity/apply/joinAct';
//        //$url = 'http://192.168.152.128:81/api/shop/activity/prize/customer';
//        //$url = 'https://testapidev.patozon.net/api/shop/activity/prize/customer';
//        //$url = 'https://testapidev.patozon.net/api/shop/activity/apply/joinAct';
////
//        $url = 'http://192.168.152.128:81/api/client/order/create';
        //$url = 'http://192.168.152.128:81/api/client/order/list';
        //$url = 'http://192.168.152.128:81/api/client/order/details';
//        $url = 'https://brand-api.patozon.net/api/client/order/details';
//
//        $url = 'http://192.168.152.128:81/api/shop/action/follow';
//        $url = 'https://testapidev.patozon.net/api/shop/action/follow';
//
//        $url = 'http://192.168.152.128:81/api/shop/customer/createCustomer';
//
//        $url = 'http://192.168.152.128:81/api/shop/product/list';
//        $url = 'http://192.168.152.128:81/api/shop/product/details';
//        $url = 'http://192.168.152.128:81/api/shop/product/exchangeList';
//        $url = 'http://192.168.152.128:81/api/shop/product/pointProducts';
//
//        $url = 'https://testapidev.patozon.net/api/client/order/create';
//        $url = 'https://testapidev.patozon.net/api/shop/product/list';
//        $url = 'https://testapidev.patozon.net/api/shop/product/details';
//        $url = 'http://192.168.152.128:81/api/client/order/list';
//        $url = 'https://testapidev.patozon.net/api/client/order/list';
//        $url = 'https://testapidev.patozon.net/api/client/order/list';
        $url = 'http://192.168.152.128:81/api/shop/customer/createCustomer';
//        $url = 'http://192.168.152.128:81/api/auth/login';
//        $url = 'https://testapidev.patozon.net/api/auth/login';
//        $url = 'https://testapidev.patozon.net/api/client/order/list';
//        $url = 'https://testapidev.patozon.net/api/client/order/details';
//        $url = 'http://192.168.152.128:81/api/shop/order/review/getReviewList';
//        $url = 'http://192.168.152.128:81/api/shop/activity/product/universalList';
        //$url = 'http://127.0.0.1:8006/api/shop/activity/product/dealIndex';
        //$url = 'https://brand-api.patozon.net/api/shop/activity/product/dealIndex';
//        $url = 'https://testapi.patozon.net/api/shop/order/bind';
//        $url = 'https://testapi.patozon.net/api/shop/order/list';
        //$url = 'https://testapi.patozon.net/api/shop/customer/signup';
//        $url = 'https://testapi.patozon.net/api/shop/customer/info';
        //$url = 'https://testapi.patozon.net/api/shop/activity/getLanternData';
        //$url = 'https://testapi.patozon.net/api/shop/public/getCountdownTime';
        //$url = 'https://testapi.patozon.net/api/shop/public/upload';
        //$url = 'https://testapi.patozon.net/api/shop/customer/createCustomer';
        //$url = 'https://testapi.patozon.net/api/shop/share/update';
        //$url = 'https://testapi.patozon.net/api/shop/rank/list';
        //$url = 'https://testapi.patozon.net/api/shop/vote/list';
        //$url = 'https://testapi.patozon.net/api/shop/vote/vote';
        //$url = 'https://testapi.patozon.net/api/shop/vote/getRankData';
        //$url = 'https://testapi.patozon.net/api/common/country/list';
        //$url = 'https://testapi.patozon.net/api/shop/country/list';
//        $url = 'https://testapi.patozon.net/api/shop/activity/product/list';
//        $url = 'https://testapi.patozon.net/api/shop/activity/apply/info';
        //$url = 'https://testapi.patozon.net/api/shop/activity/apply/product';
//        $url = 'https://testapi.patozon.net/api/shop/activity/winning/getRankData';
//        $url = 'https://testapi.patozon.net/api/shop/activity/winning/getLotteryNum';
        //$url = 'https://testapi.patozon.net/api/shop/activity/share/handle';
        //$url = 'https://testapidev.patozon.net/api/shop/activity/apply/insert';
        //$url = 'https://testapidev.patozon.net/api/shop/activity/product/dealIndex';
        //
        //$url = 'https://brand-api.patozon.net/api/shop/customer/info';
        //$url = 'https://brand-api.patozon.net/api/shop/customer/edit';
        //$url = 'https://brand-api.patozon.net/api/shop/share/update';
        //$url = 'https://brand-api.patozon.net/api/shop/rank/list';
        //$url = 'https://brand-api.patozon.net/api/shop/activity/getLanternData';
        //$url = 'https://brand-api.patozon.net/api/shop/customer/createCustomer';
        //$url = 'https://brand-api.patozon.net/api/shop/activity/getLanternData';
        //$url = 'https://brand-api.patozon.net/api/shop/product/list';
        //$url = 'https://brand-api.patozon.net/api/shop/customer/signup';
        //$url = 'https://brand-api.patozon.net/api/common/country/list';
        //$url = 'https://brand-api.patozon.net/api/shop/order/list';
//        $url = 'https://brand-api.patozon.net/api/shop/order/bind';
        //$url = 'https://brand-api.patozon.net/api/shop/customer/createCustomer';
        //$url = 'https://testapidev.patozon.net/api/shop/activity/product/list';
        //$url = 'https://testapidev.patozon.net/api/shop/activity/apply/free';
        //$url = 'https://testapidev.patozon.net/api/shop/activity/getCountdownTime';
        //$url = 'https://testapidev.patozon.net/api/shop/activity/product/getDetails';
        //$url = 'https://testapidev.patozon.net/api/shop/activity/helped/list';
        //$url = 'https://testapidev.patozon.net/api/shop/contactus/add';
        //$filename = storage_path('logs/thumbnail-1.png'); //lottery_prize.xlsx activity_products_helped.xlsx activity_vote_prize_products.xlsx

        $filename = storage_path('logs/reward_coupon_template (1565689898).xlsx'); //reward_coupon_template_test.xlsx lottery_prize.xlsx activity_products_helped.xlsx activity_vote_prize_products.xlsx https://testapi.patozon.net/api/shop/pagePublish
        $minetype = 'image/jpeg';
        $curl_file = curl_file_create($filename, $minetype); //$15 GIFT CARD
//        $filename = storage_path('logs/activity_products_helped.xlsx'); //lottery_prize.xlsx activity_products_helped.xlsx activity_vote_prize_products.xlsx
//        $curl_file1 = curl_file_create($filename, $minetype); //
        //$url = 'http://192.168.152.128:81/api/common/upload';
//        $url = 'http://192.168.152.128:81/api/shop/activity/winning/getLotteryNum';
//        $url = 'http://192.168.152.128:81/api/shop/activity/winning/handleCreditLottery';
        //$url = 'http://192.168.152.128:81/api/client/order/list';
        //$url = 'http://192.168.152.128:81/api/client/order/address';
        $request = array(//1268  153   23626

            Constant::DB_TABLE_STORE_ID => 8,
            Constant::DB_TABLE_ACCOUNT => 'Jmiy_cen88888@patazon.net', //
            Constant::DB_TABLE_PLATFORM => Constant::PLATFORM_SERVICE_SHOPIFY,
            Constant::DB_TABLE_PASSWORD => '123456',
            Constant::DB_TABLE_ACTION => 'register',
            'app_env' => "sandbox",
            Constant::DB_TABLE_IP => 'ip8',

//            Constant::DB_TABLE_STORE_ID => 1,
////            'order_no' => '114-6368351-8939418',
////            Constant::DB_TABLE_STORE_ID => 2,
//            Constant::DB_TABLE_ACCOUNT => 'Jmiy_cen@patazon.net', //
////            Constant::DB_TABLE_INVITE_CODE => '64YSNBWK',
//            Constant::DB_TABLE_ACT_ID => 29,
//            Constant::DB_TABLE_PLATFORM => [Constant::PLATFORM_SERVICE_SHOPIFY, Constant::PLATFORM_SERVICE_LOCALHOST],
//            //Constant::DB_TABLE_PLATFORM => Constant::PLATFORM_SERVICE_LOCALHOST,
//            //Constant::DB_TABLE_ORDER_UNIQUE_ID => 804569,
//            Constant::DB_TABLE_ORDER_TYPE => 2,
//            Constant::DB_TABLE_PLATFORM => Constant::PLATFORM_SERVICE_SHOPIFY,
//            Constant::DB_TABLE_PASSWORD => '123456',
//            Constant::DB_TABLE_ACTION => 'register',
//            'app_env' => "sandbox",
//            Constant::DB_TABLE_IP => 'ip23',
//            Constant::DB_TABLE_STORE_ID => 1,
//            Constant::DB_TABLE_ACCOUNT => 'Jmiy_cen31@patazon.net', //
//            Constant::DB_TABLE_ORDER_TYPE => 2,
//            Constant::DB_TABLE_PLATFORM => 'Shopify',
//            //Constant::DB_TABLE_PRIMARY => 105,
//            "variant_items" => [
//                [
//                    Constant::DB_TABLE_PRODUCT_ID => 11, //商品主键id
//                    Constant::DB_TABLE_PRIMARY => 15, //商品变种主键id
//                    "variant_id" => 32825740263499,
//                    "quantity" => 1,
//                    Constant::DB_TABLE_PRODUCT_COUNTRY => 'US'
//                ],
////                [
////                    Constant::DB_TABLE_PRODUCT_ID => 1, //商品主键id
////                    Constant::DB_TABLE_PRIMARY => 1, //商品变种主键id
////                    "variant_id" => 32825741181003,
////                    "quantity" => 1,
////                ],
////                [
////                    Constant::DB_TABLE_PRODUCT_ID => 2, //商品主键id
////                    Constant::DB_TABLE_PRIMARY => 2, //商品变种主键id
////                    "variant_id" => 32825755238475,
////                    "quantity" => 1,
////                ]
//            ],
//            "apartment" => "ptx",
//            "first_name" => "",
//            "last_name" => "ssdsad dsasdasasd",
//            "address" => "123 asdasdas Street",
//            "city" => "sadasdas",
//            "province" => "asdasd",
//            "country" => "sadasdsa",
//            "zip" => "K2P asdasdas",
//            "company" => "11111",
//            "order_type" => 2,
//            "app_env" => "sandbox",
//            Constant::METAFIELDS => [
//                [
//                    "owner_resource" => "Product",
//                    "key" => "menu",
//                    "value" => 1,
//                ],
//            ],
//            'page' => 1,
//            'page_size' => 20,
//            Constant::DB_TABLE_PRIMARY => 1,
//            Constant::DB_TABLE_STORE_ID => 1,
//            Constant::DB_TABLE_ACCOUNT => 'Jmiy_cen@patazon.net', //
//            Constant::DB_TABLE_PASSWORD => '123456',
//            Constant::ACTION_TYPE => 1,
//            Constant::SUB_TYPE =>1,
//            Constant::DB_TABLE_STORE_ID => 1,
//            Constant::DB_TABLE_ACCOUNT => 'Jmiy_cen@patazon.net', //
//            Constant::SOCIAL_MEDIA => 'FB',
//            Constant::DB_TABLE_STORE_ID => 1,
//            Constant::DB_TABLE_ACCOUNT => 'joe.morand@gmail.com', //
//            Constant::DB_TABLE_PRIMARY => 470, //
//            "platform" => "Shopify",
//            Constant::DB_TABLE_STORE_ID => 3,
//            Constant::DB_TABLE_ACT_ID => 40,
//            Constant::DB_TABLE_STORE_ID => 3,
//            Constant::DB_TABLE_ACCOUNT => 'Jmiy_cen@patazon.net', //
//            Constant::DB_TABLE_ACT_ID => 33,
//            Constant::ACTION_TYPE => 21,
//            Constant::SUB_TYPE => 21,
//            'ext_id' => 106,
//            'ext_type' => 'ActivityProduct',
//            'brand' => 'Holife',
//            Constant::DB_TABLE_ACT_ID => 1,
                //'type' => 'platform', //type: "platform"
//            'platform' => Constant::PLATFORM_SERVICE_SHOPIFY,
//            'id' => 191,
//            'id' => 170, //134,
//            'store_id' => 2,
//            'account' => 'keri.pyke@gmail.com', //Jmiy_cen@patazon.net
//            'act_id' => 1,//32
////            'orderno' => '112-5427503-3470649',
////            //'star' => 3,
////            'review_link' => 'review_link',
////            'review_img_url' => 'review_img_url',
//            'file' => $curl_file, //要上传的本地文件地址
                //'file22' =>$curl_file1 , //要上传的本地文件地址
                //'file' => base64_encode(file_get_contents($filename)), //要上传的本地文件地址   'data:image/png;base64,'.
//            'file[]' =>$curl_file , //要上传的本地文件地址
//            'file[1]' =>$curl_file1, //要上传的本地文件地址
//            'is_origin_image' => 0,
                //'file1' => $curl_file1, //要上传的本地文件地址
                //'act_id' => 8,
                //'act_unique' => '/pages/vote1',
//            //'account' => 'erdemkanki@gmail.com', //
//            //'rank_type' => 1,//榜单种类 1：总榜 2：日榜
//            'mb_type' => 2, //榜单类型 0:综合榜 1:分享 2:邀请 3:签到
//            'aws_country' => 'US',
                //'type' => 'platform', //
//            'store_id' => 1,
//            'account' => 'test@jmiy.com', //
//            'platform' => 'Shopify',
                //'order_no' => 'order_no',
//            'act_id' => 8,
//            'mb_type' => 4,
//            'account' => 'support88888@mpow.com', //
//            'topic' => 'topic11',
//            'product_type' => 'product_type',
//            'orderno' => 'orderno',
//            'subject' => 'subject',
//            'message' => 'message',
//            'contact_us_to' => 'Jmiy_cen@patazon.net', //Jmiy_cen@patazon.net
//            'client_access_url' => 'client_access_url', //Jmiy_cen@patazon.net support@ikich.com
//            'store_id' => 3,
//            'account' => 'Jmiy_cen@patazon.net', //
//            'social_media' => 'FB',
//            'first_name' => 'first_name',
//            'store_id' => 5,
//            'act_id' => 2,
//            'id' => 1,
//            'store_id' => 5,
//            'act_id' => 2,
//            'id' => 1,
//            'name' => 'name',
//            'account' => 'Jmiy_cen@patazon.net',
//            'survey_items' => [
//                [
//                    'item_id' => 1,
//                    'option_id' => 1,
//                    'option_data' => '565656666',
//                ],
//                [
//                    'item_id' => 3,
//                    'option_id' => 7,
//                    'option_data' => '7777777',
//                ],
//                [
//                    'item_id' => 3,
//                    'option_id' => 8,
//                    'option_data' => '88888888888',
//                ],
//                [
//                    'item_id' => 6,
//                    'option_id' => 15,
//                    'option_data' => '',
//                ],
//                [
//                    'item_id' => 6,
//                    'option_id' => 16,
//                    'option_data' => '',
//                ],
//            ],
//            'act_id' => 1,
////            //'product_country' => '',
////            //'account' => '8989@dd.com', //8989@dd.com
//            'id' => 11,
//            'invite_code' => 'nJrRBU0V',
//            'help_account' => 'Jmiy_cen@patazon.net',
//            'ip' => 'ip6666',
//            'apply_id' => 1,
//            'orderno' => 'orderno',
//            'page' => 1,
//            'page_size' => 10,
//            'account' => '23dsa@qq.com', //
//            'social_media' => 'FB',
//            'activity_winning_id' => 1,
//            'store_id' => 3,
//            'act_id' => 3,
//            'account' => 'Jmiy_cen@patazon.net', //
//            'activity_winning_id' => 1,
//            'country' => 'US',
//            'full_name' => 'full_name',
//            'street' => 'street',
//            'apartment' => 'apartment',
//            'city' => 'city',
//            'state' => 'state',
//            'zip_code' => 'zip_code',
//            'phone' => 'phone',
//            'store_id' => 1,
//            'act_id' => 6,
//            'account' => 'Jmiy_cen@patazon.net', //
//            'vote_item_id' => 1,
//            'store_id' => 1,
//            'act_id' => 6,
//            'account' => 'Jmiy_cen01@patazon.net', //
//            'social_media' => 'FB',
//            'first_name' => 'first_name',
//            'country' => 'US',
//            'region' => 'region',
//            'gender' => 1,
//            'brithday' => '2019-01-01',
//            'interests' => ['1', '2', '3'],
//            Constant::DB_TABLE_PLATFORM => Constant::PLATFORM_SERVICE_SHOPIFY,
//            'password' => '123456',
//            'action' => 'register',
                //'orderno' => 'orderno00',
                //'app_env' => "sandbox",
//            'store_id' => 9,
//            'account' => 'Jmiy_cen_test1@patazon.net', //
////            'act_id' => 1000, //
////            'vote_item_id' => 1,
//            'first_name' => 'first_name_iseneo',
//            'last_name' => 'last_name_iseneo',
//            'password' => '123456',
//            'platform' => "Shopify", //Jmiy_cen@patazon.net
//            'action' => "register",
//            //'app_env' => "sandbox",
//            'orderno' => '565655656565',
//            'type' => 'platform', //
//            'page' => 1, //
//            'page_size' => 10,
//            'store_id' => 5,
//            'account' => 'Jmiy_cen@patazon.net', //Jmiy_cen@patazon.net
//            'orderno' => '111-8230658-67138545656',
//            'order_country' => 'US',
////            'orderno' => '112-3027015-2687404',
////            'order_country' => 'US',
//            'type' => 'platform',
                //'brand' => 'brand',
                //
//            'type' => 'platform', //Jmiy_cen@patazon.net
//            'orderno'=>'565655656565',
//            'order_country'=>'US',
//            'id' => 1,
//            'act_id' => 3,
//            'store_id' => 8,
//            'account' => 'Email', //Jmiy_cen@patazon.net
//            'first_name' => 'Full Name*',
////            'last_name' => 'last_name_sdddd',
////            'platform' => "Amazon",
////            'password' => '123456',
////            'orderno'=>'orderno8555989',
//            'country' => 'Country',
////            'city' => 'city',
////            'brithday' => '01/05/1960',
////            'gender' => 1,
//            'profile_url' => 'Amazon Profile URL',
//            'social_media' => 'Facebook',
//            'youtube_channel' => 'Youtube',
//            'blogs_tech_websites' => 'Blogs',
//            'deal_forums' => 'Deal Forums',
//            'others' => 'Other Websites',
//            'products' => ['products', 'Which category product'],
//            'is_purchased' => 1,
//            'orderno' => 'orderno',
//            'order_country' => 'us',
//            'remarks' => 'Self-recommend to apply for LITOM VIP trial qualification',
//            'account_action' => 'account_action',
//            'account_cookies' => 'account_cookies',
//            'ext_type' => 'ext_type',
//
//            'store_id' => 1,
//            'account' => 'tfrbgl51208@chacuo.net', //BIGAMPLOCC804@GMAIL.COM
////            'country' => 'US',
//            'id' => 3,
//            'country' => 'UK',
//            'act_id' => 2, //活动id
//            'page' => 1, //
//            'page_size' => 10,
//            'store_id' => 1,
//            'account' => 'Jmiy_cen@patazon.net',
//            'country' => 'US',
//            'first_name' => 'first_name160',
//            'last_name' => 'last_name_sdddd',
//            'password' => '333',
//            'accepts_marketing' => 'on',
//            'source' => 1,
//            'store_id' => 1,
//            'account' => "Jmiy_cen@patazon.net", //Jmiy_cen@patazon.net
//            'platform' => "Shopify", //Jmiy_cen@patazon.net
//            'password' => "123456", //Jmiy_cen@patazon.net
//            'store_id' => 2,
//            'account' => "lolochic3290@yahoo.com", //Jmiy_cen@patazon.net
                //'orderno' => '114-2256242-7944208',
                //'country' => 'US',
//            'products' => [
//                [
//                    "id" => 1521029054487,
//                    "qty" => 1,
//                ]
//            ],
//            'exchange' => 'buy',
//            'store_id' => 2,
//            'account' => "CHARLEENSTEARNS@GMAIL.COM", //Jmiy_cen@patazon.net
//            'page' => 1, //
//            'page_size' => 10,
//            'type'=>'platform',
//            'vote_item_id' => 2,
//            'store_id' => 2,
//            'account' => "Jmiy_cen@patazon.net", //
                //'invite_code' => "Us15NLPd", //
//            'country' => 'US',
//            'store_id' => 2,
//            'account' => "matthew.r.shine@gmail.com", //Jmiy_cen@patazon.net
                //'vote_item_id' => 2,
//            'type' => 2, //榜单类型 1:分享 2:邀请 0:mpow总榜  -1:mpow日榜
//            'page' => 1, //
//            'page_size' => 10,
//            'store_id' => 3,
//            'account' => "Jmiy_cen115@patazon.net", //
//            //'file' => "data:image/jpeg;base64,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", //
//            //'file' => "data:image/png;base64,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",
//            'country' => 'US',
//            'first_name' => 'Jmiy_cen',
//            'last_name' => 's',
//            'password' => '56565989',
//            'platform' => 'Shopify',
                //'store_id' => 1,
                //'invite_code' => 'K9O6IAKV',
                //'source' => '3555666',
//            'store_id' => 2,
//            'account' => "Jmiy_cen@patazon.net", //
                //'store_id' => 1,
                //'account' => "Jmiy_cen@patazon.net", //
//            'start_time' => '2019-05-20 00:00:00',
//            'end_time' => '2019-05-21 00:00:00',
//            'limit' => 100,
//            'store_id' => 1,
//            'operator' => 'test11',
//            'store_id' => 1,
//            'account' => "Jmiy_cen@patazon.net", //Jmiy_cen@patazon.net
////            'created_at' => '2019-05-31',
//            'country' => "US",
////            'orderno' => '114-2256242-7944208',
//            'first_name' => "first_name",
//            'last_name' => "last_name",
//            'gender' => 1,
//            'brithday' => "brithday",
//            "interests" => ['interests', 'interests1', 'interests2', 'interests3', 'interests4'],
//            'address' => [
//                'region' => 'region',
//                'street' => 'street',
//                'city' => 'city',
//                'addr' => 'addr',
//            ],
//            'orderno' => '114-2256242-7944208',
////            'url' => 'https://www.hao123.com',
//            'invite_code' => 'Vp7aejgc',
        );

//        //注册
//        $request = [
////            'store_id' => 8,
////            'account' => 'a@qq.com',
////            'country' => 'US',
////            'first_name' => 'first_name160',
////            'last_name' => 'last_name_sdddd',
////            'password' => '333',
////            'accepts_marketing' => 'on',
////            'invite_code' => '000D321B',
////            'source' => 2,
//
//            Constant::DB_TABLE_STORE_ID => 8,
//            Constant::DB_TABLE_ACCOUNT => 'Jmiy_cen88888@patazon.net', //
//            Constant::DB_TABLE_PLATFORM => Constant::PLATFORM_SERVICE_SHOPIFY,
//            Constant::DB_TABLE_PASSWORD => '123456',
//            Constant::DB_TABLE_ACTION => 'register',
//            'app_env' => "sandbox",
//            Constant::DB_TABLE_IP => 'ip8',
//            'invite_code' => 'qjwi0HUg',
//            'source' => 15,
//        ];
//
//        $request = [
//            'store_id' => 2,
//            'country' => "UK",
//            'account' => "a@qq.com", //
//            'first_name' => 'first_name16099',
//            'last_name' => 'last_name_sdddd99',
//            'gender' => 2,
//            'brithday' => "1991-01-16",
//            'region' => 'Northern Ireland',
//            'store_customer_id' => '178063595931988',
//            'interests' => ['DIY', 'Music', 'Technology'],
//        ];
//        account: 947689502@qq.com
//        gender: Female
//        country: United Kingdom
//        birthday: 2019-05-23
//        region: England
//        interests[]: DIY
//        interests[]: Electronics
//        store_id: 2
//        platform_uid: 1780635959319
//        $request = [
//            'store_id' => 1,
//            'country' => "US",
//            'account' => "buhu@niu.com", //
//            'first_name' => "we",
//            'last_name' => "asdsa",
//            'gender' => 2,
//            'brithday' => "05/05/2019",
//            'region' => '',
//            'street' => '',
//            'city' => '',
//            'orderno' => '89898989898',
//            'order_country' => 'DE',
//            'store_customer_id' => '27636',
//        ];
//        ////编辑资料https://testapi.patozon.net/api/shop/customer/edit
//        $url = 'https://testapi.patozon.net/api/shop/customer/info';
//        //$url = 'https://testapi.patozon.net/api/shop/customer/edit';
//        //$url = 'http://127.0.0.1:8006/api/shop/customer/info';
//        $url = 'https://brand-api.patozon.net/api/shop/customer/edit';
//        $url = 'https://brand-api.patozon.net/api/shop/pub/subcribe';
//        $url = 'http://127.0.0.1:8006/api/shop/pub/subcribe';
//        $url = 'http://127.0.0.1:8006/api/admin/customer/list';
//        $url = 'http://127.0.0.1:8006/api/admin/customer/export';
////        $url = 'http://127.0.0.1:8006/api/admin/customer/info';
//        $url = 'http://127.0.0.1:8006/api/admin/customer/detailsList';
//        $url = 'http://127.0.0.1:8006/api/admin/customer/exportDetailsList';
//        $url = 'http://127.0.0.1:8006/api/admin/customer/sync';
//        $url = 'http://127.0.0.1:8006/api/admin/customer/forceDelete';
//        $url = 'http://127.0.0.1:8006/api/admin/store/actionlist';
//        $url = 'http://127.0.0.1:8006/api/admin/credit/edit';
//        $url = 'http://127.0.0.1:8006/api/admin/credit/list';
//        $url = 'http://127.0.0.1:8006/api/admin/credit/export';
//        $url = 'http://127.0.0.1:8006/api/admin/exp/edit';
        //$url = 'http://127.0.0.1:8006/api/admin/exp/list';
        //$url = 'http://127.0.0.1:8006/api/admin/exp/export';
        //$url = 'http://127.0.0.1:8006/api/admin/order/list';
//        $url = 'http://127.0.0.1:8006/api/admin/order/export';
////        $url = 'http://127.0.0.1:8006/api/admin/share/export';
//        $url = 'http://127.0.0.1:8006/api/admin/credit/list';
//        $url = 'http://127.0.0.1:8006/api/admin/email/list';
//        $url = 'http://127.0.0.1:8006/api/admin/email/export';
//        $url = 'http://127.0.0.1:8006/api/admin/interest/list';
//        $url = 'http://127.0.0.1:8006/api/admin/interest/export';
//        $url = 'http://127.0.0.1:8006/api/admin/share/list';
//        $url = 'http://127.0.0.1:8006/api/admin/share/export';
//        $url = 'http://127.0.0.1:8006/api/admin/share/audit';
//        $url = 'http://127.0.0.1:8006/api/admin/product/list';
//        $url = 'http://127.0.0.1:8006/api/admin/product/export';
//        $url = 'http://127.0.0.1:8006/api/admin/product/sync';
//        $url = 'http://127.0.0.1:8006/api/admin/product/info';
//        $url = 'http://127.0.0.1:8006/api/admin/product/add';
//        $url = 'http://127.0.0.1:8006/api/admin/product/addedit';
//        $url = 'http://127.0.0.1:8006/api/admin/product/edit';
//        $url = 'http://127.0.0.1:8006/api/admin/product/delete';
//        $url = 'http://127.0.0.1:8006/api/admin/coupon/list';
//        $url = 'http://127.0.0.1:8006/api/admin/coupon/export';
//        $url = 'http://192.168.152.128:81/api/admin/coupon/import';
//        $url = 'http://127.0.0.1:8006/api/admin/user/login';
//        $url = 'http://127.0.0.1:8006/api/admin/user/logout';
//        $url = 'http://127.0.0.1:8006/api/admin/user/info';
//        $url = 'http://127.0.0.1:8006/api/admin/store/getStore';
//        $url = 'http://127.0.0.1:8006/api/permission/permission/list';
//        $url = 'http://127.0.0.1:8006/api/permission/permission/info';
//        $url = 'http://127.0.0.1:8006/api/permission/permission/insert';
//        $url = 'http://127.0.0.1:8006/api/permission/permission/edit';
//        $url = 'http://127.0.0.1:8006/api/permission/permission/delete';
//        $url = 'http://127.0.0.1:8006/api/permission/permission/select';
//
//        $url = 'http://127.0.0.1:8006/api/permission/role/list';
//        $url = 'http://127.0.0.1:8006/api/permission/role/info';
//        $url = 'http://127.0.0.1:8006/api/permission/role/insert';
//        $url = 'http://127.0.0.1:8006/api/permission/role/edit';
//        $url = 'http://127.0.0.1:8006/api/permission/role/delete';
//        $url = 'http://127.0.0.1:8006/api/permission/role/select';
//
//        $url = 'http://127.0.0.1:8006/api/admin/activity/apply/list';
//        $url = 'http://127.0.0.1:8006/api/admin/activity/apply/export';
//        $url = 'http://127.0.0.1:8006/api/admin/activity/apply/audit';
//        $url = 'http://127.0.0.1:8006/api/admin/activity/applyInfo/info';
        //$url = 'http://127.0.0.1:8006/api/admin/order/info';
        //$url = 'http://127.0.0.1:8006/api/admin/pub/export';
//        $url = 'http://127.0.0.1:8006/api/admin/customer/export';
//        $url = 'http://127.0.0.1:8006/api/admin/customer/exportDetailsList';
//
//        $url = 'http://127.0.0.1:8006/api/admin/customer/forceDelete';
        //$url = 'http://127.0.0.1:8006/api/admin/coupon/import';
//
//        $url = 'http://dev.brand.patozon.net/api/admin/user/login';
        //$url = 'http://dev.brand.patozon.net/api/admin/user/login';
//        $url = 'http://dev.brand.patozon.net/api/admin/customer/forceDelete';
//        $url = 'http://dev.brand.patozon.net/api/admin/customer/export';
//        $url = 'http://dev.brand.patozon.net/api/admin/store/getStore';
//        $url = 'http://dev.brand.patozon.net/api/admin/customer/list';
//        $url = 'http://dev.brand.patozon.net/api/admin/customer/export';
//        $url = 'http://dev.brand.patozon.net/api/admin/customer/detailsList';
//        $url = 'http://dev.brand.patozon.net/api/admin/customer/exportDetailsList';
//
//        $url = 'http://dev.brand.patozon.net/api/admin/order/info';
//        $url = 'http://dev.brand.patozon.net/api/admin/order/list';
//
//        $url = 'http://dev.brand.patozon.net/api/admin/activity/apply/list';
//        $url = 'http://dev.brand.patozon.net/api/admin/activity/apply/export';
//        $url = 'http://dev.brand.patozon.net/api/admin/activity/apply/audit';
//        $url = 'http://dev.brand.patozon.net/api/admin/activity/applyInfo/info';
//
//
//        $url = 'https://testapidev.patozon.net/api/admin/coupon/importDeal';
//        $url = 'https://testapidev.patozon.net/api/admin/activity/product/import';
        //$url = 'http://192.168.152.128:81/api/admin/activity/prize/import';
        //$url = 'https://brandwtest.patozon.net/api/admin/store/getStore';
        //$url = 'https://testapidev.patozon.net/api/admin/activity/product/importUniversal';
        //$url = 'http://192.168.152.128:81/api/admin/activity/prize/import';
        //$url = 'http://192.168.152.128:81/api/admin/coupon/import';
        //$url = 'http://192.168.152.128:81/api/admin/coupon/importDeal';
        //$url = 'http://192.168.152.128:81/api/admin/activity/product/importUniversal';
        //$url = 'http://192.168.152.128:81/api/admin/activity/product/import';
//        $url = 'http://192.168.152.128:81/api/common/dict/select';
//        $url = 'http://192.168.152.128:81/api/admin/activity/list';
//        $url = 'http://192.168.152.128:81/api/admin/activity/export';
//        //$url = 'http://192.168.152.128:81/api/admin/activity/select';
//        //$url = 'http://192.168.152.128:81/api/admin/activity/select';
//        $url = 'http://192.168.152.128:81/api/admin/activity/insert';
//        //$url = 'http://192.168.152.128:81/api/admin/activity/edit';
//        //$url = 'http://192.168.152.128:81/api/admin/activity/del';
//        $url = 'http://192.168.152.128:81/api/admin/activity/product/importActProduct';
//        $url = 'http://192.168.152.128:81/api/admin/activity/product/actProductList';
        //$url = 'http://192.168.152.128:81/api/admin/activity/product/exportActProducts';
////        $url = 'http://192.168.152.128:81/api/admin/activity/product/delActProducts';
//        $url = 'http://192.168.152.128:81/api/admin/activity/product/getActProductItems';
//        $url = 'http://192.168.152.128:81/api/admin/activity/product/editActProductItems';
        //$url = 'http://192.168.152.128:81/api/admin/activity/apply/actApplyList';
        //$url = 'http://192.168.152.128:81/api/admin/activity/apply/exportActApplyList';
        //$url = 'http://192.168.152.128:81/api/admin/invite/list';
        //$url = 'http://192.168.152.128:81/api/admin/invite/export';
        //$url = 'https://testapidev.patozon.net/api/admin/activity/product/actProductList';
        //$url = 'http://192.168.152.128:81/api/admin/activity/product/delActProductItems';
        //$url = 'https://testapi.patozon.net/api/admin/activity/product/importActProduct';
        //$url = 'http://192.168.152.128:81/api/admin/activity/prize/customer/import';
        //$url = 'http://192.168.152.128:81/api/admin/activity/apply/exportActApplyList';
        //$url = 'https://testapidev.patozon.net/api/admin/activity/prize/customer/import';
//        $url = 'http://192.168.152.128:81/api/admin/reward/updateRewardStatus';
//        $url = 'http://192.168.152.128:81/api/admin/product/edit';
//        $url = 'http://192.168.152.128:81/api/admin/credit/export';
//        $url = 'https://testapidev.patozon.net/api/admin/credit/export';
//        $url = 'https://release-api.patozon.net/api/admin/credit/export';
//        $url = 'http://192.168.152.128:81/api/admin/platform/order/list';
//        $url = 'http://192.168.152.128:81/api/admin/platform/order/export';
//        $url = 'http://192.168.152.128:81/api/admin/order/list';
        //$url = 'http://192.168.152.128:81/api/common/dict/storeDictSelect';
        //$url = 'http://192.168.152.128:81/api/common/dict/select';
//        $url = 'http://192.168.152.128:81/api/admin/category/select';
//        $url = 'http://192.168.152.128:81/api/admin/platform/order/export';
//        $url = 'http://192.168.152.128:81/api/admin/order/export';
        //$url = 'http://192.168.152.128:81/api/admin/reward/add';
//        $url = 'http://192.168.152.128:81/api/admin/reward/edit';
//        $url = 'http://192.168.152.128:81/api/admin/orderReview/list';
        //$url = 'http://192.168.152.128:81/api/admin/reward/export';
        //$url = 'http://192.168.152.128:81/api/admin/reward/info';
        //$url = 'http://192.168.152.128:81/api/admin/reward/list';
//        $request = [
////            'store_id' => 1,
////            "operator" => "jmiy_cen",
////            "token" => "066a8a6ab9d35ad239fccb5f10bbd3c0_1569478555",
////            'file' => $curl_file, //要上传的本地文件地址
//            //Constant::BUSINESS_TYPE => 1,
//            //Constant::DB_TABLE_NAME => '65656',
//            //Constant::DB_TABLE_TYPE => 2,
//            //Constant::DB_TABLE_REVIEWER => '系统自动审核',
////            Constant::START_TIME => '2020-10-27 00:00:00',
////            Constant::DB_TABLE_END_TIME => '2020-10-31 00:00:00',
////            "page_size" => 10,
////            "page" => 1,
//
//            'product_type' => 2,
//            'name' => '$5 Amazon Gift Card',
//            'type' => 1,
//            'business_type' => 1,
//            //'country' => 'undefined',
//            'asin' => '',
//            'remarks' => '',
//            'reward_status' => 2,
//            'store_id' => 8,
//            "operator" => "jmiy_cen",
//            "token" => "066a8a6ab9d35ad239fccb5f10bbd3c0_1569478555",
//            'file' => $curl_file, //要上传的本地文件地址
//                //'reward_id'=>27,
////            Constant::DB_TABLE_PLATFORM => Constant::PLATFORM_SERVICE_AMAZON,
////            'is_from_email_export' => 1,
////            'export_email' => 'Jmiy_cen@patazon.net',
//                //'sku' => 'WRMPBH415AR-CAAS1',
//                //'category_code'=>['AR'],
////            'asin'=>['asin1'],
////            Constant::DB_TABLE_COUNTRY => ['US'],
//                //'name' => '66',
//                //'order_country' => ['US'],
////            'page_size' => 5,
////            'page' => 1,
////            Constant::DB_TABLE_ORDER_NO => '702-4534434-5963469',
////            //Constant::DB_TABLE_ORDER_NO => ['702-4534434-5963469', '205-1636956-7919550'],
////            Constant::DB_TABLE_ORDER_COUNTRY => ['ca','us'],//, 'DE'
//                //'type' => 'country_cn',
////            Constant::BUSINESS_TYPE => 1,
////            Constant::DB_TABLE_NAME => '礼品名称',
////            Constant::DB_TABLE_TYPE => 5,
////            Constant::DB_TABLE_TYPE_VALUE => 10,
////            Constant::DB_TABLE_REMARKS => '备注',
////            Constant::DB_TABLE_ASIN => 'asin5656,asin898',
////            Constant::DB_TABLE_COUNTRY => 'US,DE',
////            Constant::DB_TABLE_PRIMARY => 27,
////            'category_data' => [
////                [
////                    'category_code' => 'AR',
////                    'category_name' => '艺术手工',
////                    'level' => 1,
////                ],
////                [
////                    'category_code' => 'AR01',
////                    'category_name' => '手工制作',
////                    'level' => 2,
////                ],
////                [
////                    'category_code' => 'AR0101',
////                    'category_name' => '画笔',
////                    'level' => 3,
////                ]
////            ],
//        ];
//        $request = [
////////            'type' => 'source_show', //source_show:会员来源
////////            'store_id' => 1,
////////            "username" => "mpow",
////////            "password" => "123456",
////            'store_id' => 3,
////            "operator" => "jmiy_cen",
////            "token" => "76a79be0e2e4ad6053af45e1f3857b24_1575286763",
////            'country' => 'US',
////            'account' => '123.test_log.vc.gh@chacuo.com',
////////            'account' => implode(',', [
////////'Demi_yu@patazon.net',
////////'1445515089@qq.com',
////////'792507173@qq.com',
////////'95867603612@qq.com',
////////'Alice_huang@chacuo.net',
////////'Alice_huanga@chacuo.net',
////////'asdasd@qq.com',
////////'asdfwer@qq.com',
////////'evmfst30245@chacuo.net',
////////'hxjjxndnkxj@qq.com',
////////'iugtsr52074@chacuo.net',
////////'lvgwkm48690@chacuo.net',
////////'unmkey59470@chacuo.net',
////////'uzemhi38014@chacuo.net',
////////]),
////////            "ids" => [1,2],
////////            "audit_status" => 2,
////////            "reviewer" => 'reviewer1111111',
////////            "remarks" => 'remarks1111111',
////////            'orderno' => '402-2171289-5870700',
////////            'act_id' => 2,
////////            'customer_id' => 5544,//109633
////////            "page_size" => 10,
////////            "page" => 1,
////////            "account" => '',
////////            "sku" => '',
////////            "country" => '',
////////            "audit_status" => '0',
////////            "start_at" => '',
////////            "end_at" => '',
////////            'account' => implode(',', [
////////                'vczsaaajhcSDFGbg@chacuo.net',
////////                'vczsaajh11231234123123234cbg@chacuo.net',
////////                'vczsaajh1123asdfasd1234123123234cbg@chacuo.net',
////////            ]),
////////            'id' => 1,
////////            'name' => '角色name111',
////////            'permissions' => [
////////                1 => [
////////                    'select' => 'select1',
////////                    'update' => 'update1',
////////                ],
////////                2 => [
////////                    'select' => 'select2',
////////                    'update' => 'update2',
////////                ],
////////                12 => [
////////                    'select' => 'select12',
////////                    'update' => 'update12',
////////                ],
////////                9 => [
////////                    'select' => 'select9',
////////                    'update' => 'update9',
////////                ],
////////                5 => [
////////                    'select' => 'select5',
////////                    'update' => 'update5',
////////                ],
////////            ],
////////            'ids' => [2],
////////            'name' => 'name11',
////////            'url' => 'url11',
////////            'type' => 1,
////////            'status' => 1,
////////            'component' => 'component11',
////////            'router' => 'router11',
////////            'icon' => 'icon11',
////////            'parent_ids' => '1,2,3',
////////            'sort' => 0,
////////            'is_show' => 1,
////////            'show_name' => 'show_name11',
////////            'number' => 'number11',
////////            "account" => "rimiva01@hotmail.com",
////////            "orderno" => "403-2543141-9572366",
////////
//            'store_id' => 1,
//            //'act_id' => 33,
//            //"username" => "jmiy_cen",
////            "password" => "123456",
//            "operator" => "jmiy_cen",
//            "token" => "066a8a6ab9d35ad239fccb5f10bbd3c0_1569478555",
////            "token" => "0b78ba7669174d946795e3cd70245fea_1597287816",
////            "token" => "a7de0f7c4fb187f5ef4970727ef33313_1600221228",
////            "is_from_email_export" => 1,
////            "export_email" => "Jmiy_cen@patazon.net",
////            "export_file_name" => "积分",
////
//////            "token" => '0b78ba7669174d946795e3cd70245fea_1597287816',
//            'id' => 11, //[45], //礼品id
//            Constant::DB_TABLE_EXT_ID => 11,
//            Constant::DB_TABLE_EXT_TYPE => 'Product',
//            Constant::EXPIRE_TIME => '2022-01-01 00:00:00',
//            Constant::METAFIELDS => json_encode([
//                [
//                    'key' => Constant::DB_TABLE_TYPE,
//                    'value' => 2
//                ],
//                [
//                    'key' => 'country',
//                    'value' => ['US', 'DE']
//                ],
////                [
////                    'key' => Constant::DB_TABLE_TYPE,
////                    'value' => 5,
////                ],
////                [
////                    'key' => Constant::DB_TABLE_TYPE,
////                    'value' => 6,
////                ],
////                [
////                    'key' => Constant::DB_TABLE_TYPE.'565',
////                    'value' => 2,
////                ],
////                [
////                    'key' => Constant::DB_TABLE_TYPE,
////                    'value' => 3,
////                ]
//            ]),
//////////            'reward_status' => 1,
////////            //'asin' => 'B07C48ZYXR',
////////            //"token" => "1a93755119b24530590a1519b2a0e244_1588902891",
////////            //'use_type' => 1, //coupon：1:独占型 2:通用型 3:限制型 默认:1
//////////            'mb_type' => 2, //deal产品：模板类型 0 未选择 1 新品 2 常规 3 主推 4:通用
//            'file' => $curl_file, //要上传的本地文件地址
////            'file[]' => $curl_file1, //要上传的本地文件地址
////            'name' => 'post_name', //要上传的本地文件地址
////            'address' => 'post_address', //要上传的本地文件地址
//                //'act_id' => 28,
////            'is_prize' => 0, //投票活动时 是否活动奖品 1:是 0:否
////            'store_id' => 1,
//////            "username" => "jmiy_cen",
//////            "password" => "123456",
////            "operator" => "jmiy_cen",
////            "token" => "066a8a6ab9d35ad239fccb5f10bbd3c0_1569478555",
////            //"token" => "477c4ffbb9e164a5ad180a92da842cdb_1585742178",
//////            'type'=>'act_type',
////            //'id' => ['ActivityProduct-58', 'ActivityPrize-1'],
////                'id' => 'ActivityProduct-295',
////                'item_id' => [null],
//////            'id' => 'ActivityPrize-1',
////            'names[0]'=>1,
////            'names[1]'=>2,
////            'item_data' => [
////                [
////                    "act_id" => 21,
////                    "act_name" => "活动名字",
////                    "act_type" => 5,//活动类型 1:九宫格 2:转盘 3:砸金蛋 4:翻牌 5:邀请好友注册 6:上传图片投票
////                    "name" => "产品名称:实物6666",
////                    "img_url" => "https://www/333333.png",
////                    "sort" => 3,
////                    "item_id" => 3792,
////                    "sku" => "sku3",
////                    "asin" => "asin3_9",
////                    "country" => "us",
////                    "qty" => "10",
////                    "type" => "3",
////                    "type_value" => "",
////                    "help_sum" => 20,
////                    "is_prize" => 0,
////                    "des" => "",
////                    "star" => "0",
////                    "id" => "ActivityProduct-46",
////                    "probability" => 0,
////                ],
////                [
////                    "act_id" => 21,
////                    "act_name" => "活动名字",
////                    "act_type" => 5,
////                    "name" => "产品名称:实物6666",
////                    "img_url" => "https://www/55555555.png",
////                    "sort" => 3,
////                    "item_id" => null,
////                    "sku" => "sku3",
////                    "asin" => "asin3_9",
////                    "country" => "DE",
////                    "qty" => "10",
////                    "type" => "3",
////                    "type_value" => "",
////                    "help_sum" => 20,
////                    "is_prize" => 0,
////                    "des" => "",
////                    "star" => "0",
////                    "id" => "ActivityProduct-46",
////                    "probability" => 0,
////                ],
//////                [
//////                    "act_id" => 20,
//////                    "act_name" => "活动名字",
//////                    "act_type" => 2,
//////                    "name" => "产品名称:礼品卡5656",
//////                    "img_url" => "https://www/5656.png",
//////                    "sort" => 1,
//////                    "item_id" => 1,
//////                    "sku" => "sku5656",
//////                    "asin" => "asin5656",
//////                    "country" => "DE",
//////                    "qty" => "20",
//////                    "type" => "1",
//////                    "type_value" => "gift5656",
//////                    "des" => "",
//////                    "star" => "",
//////                    "id" => "ActivityPrize-1",
//////                    "probability" => 0.01,
//////                ],
//////                [
//////                    "act_id" => 20,
//////                    "act_name" => "活动名字",
//////                    "act_type" => 2,
//////                    "name" => "产品名称:礼品卡",
//////                    "img_url" => "https://www/1.png",
//////                    "sort" => 20,
//////                    "item_id" => 6,
//////                    "sku" => "sku1",
//////                    "asin" => "asin1",
//////                    "country" => "US",
//////                    "qty" => "10",
//////                    "type" => "1",
//////                    "type_value" => "gift2",
//////                    "des" => "",
//////                    "star" => "",
//////                    "id" => "ActivityPrize-1",
//////                    "probability" => 10,
//////                ],
//////                [
//////                    "act_id" => 20,
//////                    "act_name" => "活动名字",
//////                    "act_type" => 2,
//////                    "name" => "产品名称:礼品卡898989",
//////                    "img_url" => "https://www/1.png",
//////                    "sort" => 1,
//////                    "item_id" => null,
//////                    "sku" => "sku1",
//////                    "asin" => "asin88",
//////                    "country" => "US",
//////                    "qty" => "10",
//////                    "type" => "1",
//////                    "type_value" => "gift888",
//////                    "des" => "",
//////                    "star" => "",
//////                    "id" => "ActivityPrize-1",
//////                    "probability" => 20,
//////                ],
//////                [
//////                    "act_id" => 16,
//////                    "act_name" => "活动名字",
//////                    "act_type" => 6,
//////                    "name" => "产品名称:礼品卡",
//////                    "img_url" => "https://www/1.png",
//////                    "sort" => 1,
//////                    "item_id" => null,
//////                    "sku" => "sku1",
//////                    "asin" => "asin1",
//////                    "country" => "all",
//////                    "qty" => "0",
//////                    "type" => "0",
//////                    "type_value" => "",
//////                    "help_sum" => 0,
//////                    "is_prize" => 0,
//////                    "des" => "",
//////                    "star" => "0",
//////                    "id" => "ActivityProduct-53",
//////                    "probability" => 0,
//////                ],
//////                [
//////                    "act_id" => 16,
//////                    "act_name" => "活动名字",
//////                    "act_type" => 6,
//////                    "name" => "产品名称:礼品卡55",
//////                    "img_url" => "https://www/2.png",
//////                    "sort" => 10,
//////                    "item_id" => null,
//////                    "sku" => "sku1",
//////                    "asin" => "asin1",
//////                    "country" => "all",
//////                    "qty" => "0",
//////                    "type" => "0",
//////                    "type_value" => "",
//////                    "help_sum" => 0,
//////                    "is_prize" => 0,
//////                    "des" => "",
//////                    "star" => "0",
//////                    "id" => null,
//////                    "probability" => 0,
//////                ],
////            ],
////            'id' => 34,
////            'name' => 'vote88',
////////            "start_time" => '2020-03-25 17:59:59',
////////            'end_time' => '2020-03-31 17:59:59',
////////            "start_at" => '2020-03-25 17:59:59',
////////            'end_at' => '2020-03-31 17:59:59',
////            "act_type" => 6, //活动类型 1:九宫格 2:转盘 3:砸金蛋 4:翻牌 5:邀请好友注册 6:上传图片投票
////            //"type" => 2,//产品类型 0:其他 1:礼品卡 2:coupon 3:实物 5:活动积分
////            'mark' => "vote1",
////            'account'=>'account',
////            'apply_country'=>'US',
////            'product_name'=>'',
////            'customer_start_at'=>'2020-04-07 00:00:00',
////            'customer_end_at'=>'2020-04-07 23:59:59',
////            'order_by_data'=>[['c.ctime','desc']],
////            "page_size" => 100,
////            "page" => 1,
////////            "status"=>"1",
////////            "country"=>"us",
////////            "type"=>"CA57BN",
////////            //"start_time"=>"2019-06-01 00:00:00",
////////            //"end_time"=>"2019-07-01 00:00:00"
////////            //'id'=>128,
////////            //'store_product_id' => '3880394260503111',
//////////            'credit' => 1001,
//////////            'qty' => 2,
//////////            'status'=>1,
//////////            'expire_time'=>'2019-07-16T16:00:00.000Z',
//////////
//////////            'id' => 1,
//////////            'audit_status' => 1,
//////////            'remarks' => 'remarks',
////////            'action' => 'share',
////////            'customer_id' => 116016,
////////            'value' => 5,
////////            'add_type' => 1,
////////            'remark' => "测试添加积分",
////////////            "account" => "555",
////////////            'start_time'=>'2019-06-01',
////////////            'end_time'=>'2019-06-02',
////////////            'orderno'=>'55',
////////////            'country'=>'us',
////////////            "type" => "credit",
////////////            "add_type" => "1",
////////////            "value" => "1",
////////////            "remark" => "试试",
////////////            "customer_id" => 3,
////////////            "action" => "order",
////////////            'country' => "UK",
////////////            'account' => "Jmiy_cen@patazon.net", //
////////////            'email' => "Jmiy_cen@patazon.net", //
////////////            'first_name' => "we",
////////////            'last_name' => "asdsa",
////////////            'gender' => 2,
////////////            'brithday' => "1991-01-16",
////////////            'region' => 'Northern Ireland',
////////////            'street' => '',
////////////            'city' => '',
////////////            'orderno' => '89898989898',
////////////            'order_country' => 'DE',
////////////            'store_customer_id' => '1780635959319',
////////////            'interests' => ['DIY', 'Music', 'Technology'],
//        ];
//        $request = [
//            'store_id' => 1,
//            'account' => 'terdf@admin.com',
//        ];
//        $url = 'http://app.yunbaide1.com/bdm/index/checkLoginAndBindMobile';
//        $request = '{"username":"alice_huang","password":"123456","store_id":"1"}';
        //$url = 'https://brand-api.patozon.net/api/shop/order/list';
//        $url = 'http://127.0.0.1:8006/api/shop/activity/apply/product';
//        $request = [
//            "store_id" => 1,
//            "account" => "qpmzhv65392@chacuo.net",
//            "id" => 3,
//            "act_id" => 2,
//            "source" => 60012,
//            "ip" => "14.136.220.50",
//            "country" => "HK",
//            "created_at" => "2019-09-20 01:11:59",
//            "updated_at" => "2019-09-20 01:11:59",
//            "bk" => "data_bk",
//        ];
//
//        $url = 'http://127.0.0.1:8006/api/shop/order/creditexchange';//  "bk":"data_bk",
//        $request = '{"account":"amartya.kallingal23@paceacademy.org","store_id":2,"products":[{"id":1477093982231,"qty":1}],"source":30005,"ip":"24.99.37.240","country":"US","headerData":{"accept-language":["en-US,en;q=0.9"],"accept-encoding":["gzip, deflate, br"],"referer":["https:\/\/www.victsing.com\/"],"sec-fetch-site":["cross-site"],"content-type":["application\/json"],"sec-fetch-mode":["cors"],"dnt":["1"],"user-agent":["Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/76.0.3809.132 Safari\/537.36"],"origin":["https:\/\/www.victsing.com"],"accept":["application\/json, text\/javascript, *\/*; q=0.01"],"content-length":["104"],"connection":["close"],"x-forwarded-for":["24.99.37.240"],"x-real-ip":["24.99.37.240"],"host":["brand-api.patozon.net"]}}';
//        $rs = $this->sendApiRequestByCurl($url, $request);
//        for ($i = 0; $i < 1; $i++) {
//            $request = '{"app_env":"sandbox","store_id":1,"platform":"Shopify","action":"register","source":2,"invite_code":"","account":"1366662@test.com","password":"123456","country":"CA","act_id":5,"ip":"74.214.159.167","headerData":{"accept-language":["en-ca"],"referer":["https:\/\/holife.com\/pages\/new-product?utm_source=Cy&utm_medium=1&utm_campaign=new-product_hm322"],"user-agent":["Mozilla\/5.0 (iPhone; CPU iPhone OS 13_3 like Mac OS X) AppleWebKit\/605.1.15 (KHTML, like Gecko) Mobile\/15E148 [FBAN\/FBIOS;FBDV\/iPhone9,3;FBMD\/iPhone;FBSN\/iOS;FBSV\/13.3;FBSS\/2;FBID\/phone;FBLC\/en_US;FBOP\/5;FBCR\/TELUS]"],"accept":["application\/json, text\/javascript, *\/*; q=0.01"],"accept-encoding":["gzip, deflate, br"],"origin":["https:\/\/holife.com"],"content-type":["application\/json; charset=utf-8"],"content-length":["166"],"connection":["close"],"x-forwarded-for":["74.214.159.167"],"x-real-ip":["74.214.159.167"],"host":["brand-api.patozon.net"]},"request_mark":"03vaWVulir","clientData":{"device":"iPhone","device_type":1,"platform":"iOS","platform_version":"13_3","browser":"Mozilla","browser_version":false,"languages":"[\"en-ca\"]","is_robot":0}}'; //
//            $rs = $this->sendApiRequestByCurl($url, $request);
//        }
//        $url = 'https://brand-api.patozon.net/api/shop/order/creditexchange';//  "bk":"data_bk",
//        //$url = 'https://47.89.245.0/api/shop/order/creditexchange';//  "bk":"data_bk",
//        //$url = 'https://brand-api.patozon.net/api/shop/opcache';
//        $request = '{"account":"amartya.kallingal23@paceacademy.org","store_id":2,"products":[{"id":1477093982231,"qty":1}],"source":30005,"ip":"24.99.37.240","country":"US","headerData":{"accept-language":["en-US,en;q=0.9"],"accept-encoding":["gzip, deflate, br"],"referer":["https:\/\/www.victsing.com\/"],"sec-fetch-site":["cross-site"],"content-type":["application\/json"],"sec-fetch-mode":["cors"],"dnt":["1"],"user-agent":["Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/76.0.3809.132 Safari\/537.36"],"origin":["https:\/\/www.victsing.com"],"accept":["application\/json, text\/javascript, *\/*; q=0.01"],"content-length":["104"],"connection":["close"],"x-forwarded-for":["24.99.37.240"],"x-real-ip":["24.99.37.240"],"host":["brand-api.patozon.net"]}}';
        //$url = 'https://testapi.patozon.net/api/shop/order/creditexchange';//  "bk":"data_bk
        //$url = 'http://127.0.0.1:8006/api/shop/order/creditexchange';//  "bk":"data_bk"
        //$request = '{"account":"sdasda@chacuo.net","store_id":2,"products":[{"id":4434674221108,"qty":1}]}';//  "bk":"data_bk,
        //$url = 'http://127.0.0.1:8006/api/shop/activity/prize/list';
//$request = '{"store_id":3,"act_id":5}';
//响应数据：{"exeTime":"17.5631 ms","code":1,"msg":"ok","data":{"data":[{"id":109,"name":"NO LUCK","img_url":"https:\/\/cdn.shopify.com\/s\/files\/1\/1884\/7327\/files\/1568.png?v=1578309960","mb_img_url":"","url":""},{"id":110,"name":"NO LUCK","img_url":"https:\/\/cdn.shopify.com\/s\/files\/1\/1884\/7327\/files\/1568.png?v=1578309960","mb_img_url":"","url":""},{"id":111,"name":"NO LUCK","img_url":"https:\/\/cdn.shopify.com\/s\/files\/1\/1884\/7327\/files\/1568.png?v=1578309960","mb_img_url":"","url":""}],"pagination":{"page_index":1,"page_size":10,"offset":0,"total":3,"total_page":1}}}
        //$url = 'http://127.0.0.1:8006/api/shop/order/5/creatNotice/sandbox';
        //$url = 'https://testapidev.patozon.net/api/shop/order/5/creatNotice/sandbox';
        //$url = 'https://testapi.patozon.net/api/shop/order/5/creatNotice/sandbox';
        //$request = "{\"id\":820982911946154508,\"email\":\"jon@doe.ca\",\"closed_at\":null,\"created_at\":\"2020-02-18T22:27:51-08:00\",\"updated_at\":\"2020-02-18T22:27:51-08:00\",\"number\":234,\"note\":null,\"token\":\"123456abcd\",\"gateway\":null,\"test\":true,\"total_price\":\"99.98\",\"subtotal_price\":\"89.98\",\"total_weight\":0,\"total_tax\":\"0.00\",\"taxes_included\":false,\"currency\":\"USD\",\"financial_status\":\"voided\",\"confirmed\":false,\"total_discounts\":\"5.00\",\"total_line_items_price\":\"94.98\",\"cart_token\":null,\"buyer_accepts_marketing\":true,\"name\":\"#9999\",\"referring_site\":null,\"landing_site\":null,\"cancelled_at\":\"2020-02-18T22:27:51-08:00\",\"cancel_reason\":\"customer\",\"total_price_usd\":null,\"checkout_token\":null,\"reference\":null,\"user_id\":null,\"location_id\":null,\"source_identifier\":null,\"source_url\":null,\"processed_at\":null,\"device_id\":null,\"phone\":null,\"customer_locale\":\"en\",\"app_id\":null,\"browser_ip\":null,\"landing_site_ref\":null,\"order_number\":1234,\"discount_applications\":[{\"type\":\"manual\",\"value\":\"5.0\",\"value_type\":\"fixed_amount\",\"allocation_method\":\"one\",\"target_selection\":\"explicit\",\"target_type\":\"line_item\",\"description\":\"Discount\",\"title\":\"Discount\"}],\"discount_codes\":[],\"note_attributes\":[],\"payment_gateway_names\":[\"visa\",\"bogus\"],\"processing_method\":\"\",\"checkout_id\":null,\"source_name\":\"web\",\"fulfillment_status\":\"pending\",\"tax_lines\":[],\"tags\":\"\",\"contact_email\":\"jon@doe.ca\",\"order_status_url\":\"https:\\\/\\\/pro-ikich.myshopify.com\\\/26268696661\\\/orders\\\/123456abcd\\\/authenticate?key=abcdefg\",\"presentment_currency\":\"USD\",\"total_line_items_price_set\":{\"shop_money\":{\"amount\":\"94.98\",\"currency_code\":\"USD\"},\"presentment_money\":{\"amount\":\"94.98\",\"currency_code\":\"USD\"}},\"total_discounts_set\":{\"shop_money\":{\"amount\":\"5.00\",\"currency_code\":\"USD\"},\"presentment_money\":{\"amount\":\"5.00\",\"currency_code\":\"USD\"}},\"total_shipping_price_set\":{\"shop_money\":{\"amount\":\"10.00\",\"currency_code\":\"USD\"},\"presentment_money\":{\"amount\":\"10.00\",\"currency_code\":\"USD\"}},\"subtotal_price_set\":{\"shop_money\":{\"amount\":\"89.98\",\"currency_code\":\"USD\"},\"presentment_money\":{\"amount\":\"89.98\",\"currency_code\":\"USD\"}},\"total_price_set\":{\"shop_money\":{\"amount\":\"99.98\",\"currency_code\":\"USD\"},\"presentment_money\":{\"amount\":\"99.98\",\"currency_code\":\"USD\"}},\"total_tax_set\":{\"shop_money\":{\"amount\":\"0.00\",\"currency_code\":\"USD\"},\"presentment_money\":{\"amount\":\"0.00\",\"currency_code\":\"USD\"}},\"line_items\":[{\"id\":866550311766439020,\"variant_id\":31427789717589,\"title\":\"IKICH 4 Slice Toaster with LCD Countdown\",\"quantity\":1,\"sku\":\"B07TDPRW7P\",\"variant_title\":null,\"vendor\":null,\"fulfillment_service\":\"manual\",\"product_id\":4419240099925,\"requires_shipping\":true,\"taxable\":true,\"gift_card\":false,\"name\":\"IKICH 4 Slice Toaster with LCD Countdown\",\"variant_inventory_management\":\"shopify\",\"properties\":[],\"product_exists\":true,\"fulfillable_quantity\":1,\"grams\":2858,\"price\":\"54.99\",\"total_discount\":\"0.00\",\"fulfillment_status\":null,\"price_set\":{\"shop_money\":{\"amount\":\"54.99\",\"currency_code\":\"USD\"},\"presentment_money\":{\"amount\":\"54.99\",\"currency_code\":\"USD\"}},\"total_discount_set\":{\"shop_money\":{\"amount\":\"0.00\",\"currency_code\":\"USD\"},\"presentment_money\":{\"amount\":\"0.00\",\"currency_code\":\"USD\"}},\"discount_allocations\":[],\"admin_graphql_api_id\":\"gid:\\\/\\\/shopify\\\/LineItem\\\/866550311766439020\",\"tax_lines\":[]},{\"id\":141249953214522974,\"variant_id\":31777821687893,\"title\":\"ikich 2 Slice Stainless Steel Toaster\",\"quantity\":1,\"sku\":\"B07QCX8FB3\",\"variant_title\":null,\"vendor\":null,\"fulfillment_service\":\"manual\",\"product_id\":4530539528277,\"requires_shipping\":true,\"taxable\":true,\"gift_card\":false,\"name\":\"ikich 2 Slice Stainless Steel Toaster\",\"variant_inventory_management\":\"shopify\",\"properties\":[],\"product_exists\":true,\"fulfillable_quantity\":1,\"grams\":1451,\"price\":\"39.99\",\"total_discount\":\"5.00\",\"fulfillment_status\":null,\"price_set\":{\"shop_money\":{\"amount\":\"39.99\",\"currency_code\":\"USD\"},\"presentment_money\":{\"amount\":\"39.99\",\"currency_code\":\"USD\"}},\"total_discount_set\":{\"shop_money\":{\"amount\":\"5.00\",\"currency_code\":\"USD\"},\"presentment_money\":{\"amount\":\"5.00\",\"currency_code\":\"USD\"}},\"discount_allocations\":[{\"amount\":\"5.00\",\"discount_application_index\":0,\"amount_set\":{\"shop_money\":{\"amount\":\"5.00\",\"currency_code\":\"USD\"},\"presentment_money\":{\"amount\":\"5.00\",\"currency_code\":\"USD\"}}}],\"admin_graphql_api_id\":\"gid:\\\/\\\/shopify\\\/LineItem\\\/141249953214522974\",\"tax_lines\":[]}],\"fulfillments\":[],\"refunds\":[],\"total_tip_received\":\"0.0\",\"admin_graphql_api_id\":\"gid:\\\/\\\/shopify\\\/Order\\\/820982911946154508\",\"shipping_lines\":[{\"id\":271878346596884015,\"title\":\"Generic Shipping\",\"price\":\"10.00\",\"code\":null,\"source\":\"shopify\",\"phone\":null,\"requested_fulfillment_service_id\":null,\"delivery_category\":null,\"carrier_identifier\":null,\"discounted_price\":\"10.00\",\"price_set\":{\"shop_money\":{\"amount\":\"10.00\",\"currency_code\":\"USD\"},\"presentment_money\":{\"amount\":\"10.00\",\"currency_code\":\"USD\"}},\"discounted_price_set\":{\"shop_money\":{\"amount\":\"10.00\",\"currency_code\":\"USD\"},\"presentment_money\":{\"amount\":\"10.00\",\"currency_code\":\"USD\"}},\"discount_allocations\":[],\"tax_lines\":[]}],\"billing_address\":{\"first_name\":\"Bob\",\"address1\":\"123 Billing Street\",\"phone\":\"555-555-BILL\",\"city\":\"Billtown\",\"zip\":\"K2P0B0\",\"province\":\"Kentucky\",\"country\":\"United States\",\"last_name\":\"Biller\",\"address2\":null,\"company\":\"My Company\",\"latitude\":null,\"longitude\":null,\"name\":\"Bob Biller\",\"country_code\":\"US\",\"province_code\":\"KY\"},\"shipping_address\":{\"first_name\":\"Steve\",\"address1\":\"123 Shipping Street\",\"phone\":\"555-555-SHIP\",\"city\":\"Shippington\",\"zip\":\"40003\",\"province\":\"Kentucky\",\"country\":\"United States\",\"last_name\":\"Shipper\",\"address2\":null,\"company\":\"Shipping Company\",\"latitude\":null,\"longitude\":null,\"name\":\"Steve Shipper\",\"country_code\":\"US\",\"province_code\":\"KY\"},\"customer\":{\"id\":115310627314723954,\"email\":\"john@test.com\",\"accepts_marketing\":false,\"created_at\":null,\"updated_at\":null,\"first_name\":\"John\",\"last_name\":\"Smith\",\"orders_count\":0,\"state\":\"disabled\",\"total_spent\":\"0.00\",\"last_order_id\":null,\"note\":null,\"verified_email\":true,\"multipass_identifier\":null,\"tax_exempt\":false,\"phone\":null,\"tags\":\"\",\"last_order_name\":null,\"currency\":\"USD\",\"accepts_marketing_updated_at\":null,\"marketing_opt_in_level\":null,\"admin_graphql_api_id\":\"gid:\\\/\\\/shopify\\\/Customer\\\/115310627314723954\",\"default_address\":{\"id\":715243470612851245,\"customer_id\":115310627314723954,\"first_name\":null,\"last_name\":null,\"company\":null,\"address1\":\"123 Elm St.\",\"address2\":null,\"city\":\"Ottawa\",\"province\":\"Ontario\",\"country\":\"Canada\",\"zip\":\"K2H7A8\",\"phone\":\"123-123-1234\",\"name\":\"\",\"province_code\":\"ON\",\"country_code\":\"CA\",\"country_name\":\"Canada\",\"default\":true}}}";
        //$hmac = 'aVB8fEJErbweBCKDsc5MI2kzR8JrfEgUM25Be1NWSQs=';
        //$request ='{"id":820982911946154508,"email":"jon@doe.ca","closed_at":null,"created_at":"2020-02-18T16:54:44-08:00","updated_at":"2020-02-18T16:54:44-08:00","number":234,"note":null,"token":"123456abcd","gateway":null,"test":true,"total_price":"99.98","subtotal_price":"89.98","total_weight":0,"total_tax":"0.00","taxes_included":false,"currency":"USD","financial_status":"voided","confirmed":false,"total_discounts":"5.00","total_line_items_price":"94.98","cart_token":null,"buyer_accepts_marketing":true,"name":"#9999","referring_site":null,"landing_site":null,"cancelled_at":"2020-02-18T16:54:44-08:00","cancel_reason":"customer","total_price_usd":null,"checkout_token":null,"reference":null,"user_id":null,"location_id":null,"source_identifier":null,"source_url":null,"processed_at":null,"device_id":null,"phone":null,"customer_locale":"en","app_id":null,"browser_ip":null,"landing_site_ref":null,"order_number":1234,"discount_applications":[{"type":"manual","value":"5.0","value_type":"fixed_amount","allocation_method":"one","target_selection":"explicit","target_type":"line_item","description":"Discount","title":"Discount"}],"discount_codes":[],"note_attributes":[],"payment_gateway_names":["visa","bogus"],"processing_method":"","checkout_id":null,"source_name":"web","fulfillment_status":"pending","tax_lines":[],"tags":"","contact_email":"jon@doe.ca","order_status_url":"https:\/\/pro-ikich.myshopify.com\/26268696661\/orders\/123456abcd\/authenticate?key=abcdefg","presentment_currency":"USD","total_line_items_price_set":{"shop_money":{"amount":"94.98","currency_code":"USD"},"presentment_money":{"amount":"94.98","currency_code":"USD"}},"total_discounts_set":{"shop_money":{"amount":"5.00","currency_code":"USD"},"presentment_money":{"amount":"5.00","currency_code":"USD"}},"total_shipping_price_set":{"shop_money":{"amount":"10.00","currency_code":"USD"},"presentment_money":{"amount":"10.00","currency_code":"USD"}},"subtotal_price_set":{"shop_money":{"amount":"89.98","currency_code":"USD"},"presentment_money":{"amount":"89.98","currency_code":"USD"}},"total_price_set":{"shop_money":{"amount":"99.98","currency_code":"USD"},"presentment_money":{"amount":"99.98","currency_code":"USD"}},"total_tax_set":{"shop_money":{"amount":"0.00","currency_code":"USD"},"presentment_money":{"amount":"0.00","currency_code":"USD"}},"line_items":[{"id":866550311766439020,"variant_id":31427789717589,"title":"IKICH 4 Slice Toaster with LCD Countdown","quantity":1,"sku":"B07TDPRW7P","variant_title":null,"vendor":null,"fulfillment_service":"manual","product_id":4419240099925,"requires_shipping":true,"taxable":true,"gift_card":false,"name":"IKICH 4 Slice Toaster with LCD Countdown","variant_inventory_management":"shopify","properties":[],"product_exists":true,"fulfillable_quantity":1,"grams":2858,"price":"54.99","total_discount":"0.00","fulfillment_status":null,"price_set":{"shop_money":{"amount":"54.99","currency_code":"USD"},"presentment_money":{"amount":"54.99","currency_code":"USD"}},"total_discount_set":{"shop_money":{"amount":"0.00","currency_code":"USD"},"presentment_money":{"amount":"0.00","currency_code":"USD"}},"discount_allocations":[],"admin_graphql_api_id":"gid:\/\/shopify\/LineItem\/866550311766439020","tax_lines":[]},{"id":141249953214522974,"variant_id":31777821687893,"title":"ikich 2 Slice Stainless Steel Toaster","quantity":1,"sku":"B07QCX8FB3","variant_title":null,"vendor":null,"fulfillment_service":"manual","product_id":4530539528277,"requires_shipping":true,"taxable":true,"gift_card":false,"name":"ikich 2 Slice Stainless Steel Toaster","variant_inventory_management":"shopify","properties":[],"product_exists":true,"fulfillable_quantity":1,"grams":1451,"price":"39.99","total_discount":"5.00","fulfillment_status":null,"price_set":{"shop_money":{"amount":"39.99","currency_code":"USD"},"presentment_money":{"amount":"39.99","currency_code":"USD"}},"total_discount_set":{"shop_money":{"amount":"5.00","currency_code":"USD"},"presentment_money":{"amount":"5.00","currency_code":"USD"}},"discount_allocations":[{"amount":"5.00","discount_application_index":0,"amount_set":{"shop_money":{"amount":"5.00","currency_code":"USD"},"presentment_money":{"amount":"5.00","currency_code":"USD"}}}],"admin_graphql_api_id":"gid:\/\/shopify\/LineItem\/141249953214522974","tax_lines":[]}],"fulfillments":[],"refunds":[],"total_tip_received":"0.0","admin_graphql_api_id":"gid:\/\/shopify\/Order\/820982911946154508","shipping_lines":[{"id":271878346596884015,"title":"Generic Shipping","price":"10.00","code":null,"source":"shopify","phone":null,"requested_fulfillment_service_id":null,"delivery_category":null,"carrier_identifier":null,"discounted_price":"10.00","price_set":{"shop_money":{"amount":"10.00","currency_code":"USD"},"presentment_money":{"amount":"10.00","currency_code":"USD"}},"discounted_price_set":{"shop_money":{"amount":"10.00","currency_code":"USD"},"presentment_money":{"amount":"10.00","currency_code":"USD"}},"discount_allocations":[],"tax_lines":[]}],"billing_address":{"first_name":"Bob","address1":"123 Billing Street","phone":"555-555-BILL","city":"Billtown","zip":"K2P0B0","province":"Kentucky","country":"United States","last_name":"Biller","address2":null,"company":"My Company","latitude":null,"longitude":null,"name":"Bob Biller","country_code":"US","province_code":"KY"},"shipping_address":{"first_name":"Steve","address1":"123 Shipping Street","phone":"555-555-SHIP","city":"Shippington","zip":"40003","province":"Kentucky","country":"United States","last_name":"Shipper","address2":null,"company":"Shipping Company","latitude":null,"longitude":null,"name":"Steve Shipper","country_code":"US","province_code":"KY"},"customer":{"id":115310627314723954,"email":"john@test.com","accepts_marketing":false,"created_at":null,"updated_at":null,"first_name":"John","last_name":"Smith","orders_count":0,"state":"disabled","total_spent":"0.00","last_order_id":null,"note":null,"verified_email":true,"multipass_identifier":null,"tax_exempt":false,"phone":null,"tags":"","last_order_name":null,"currency":"USD","accepts_marketing_updated_at":null,"marketing_opt_in_level":null,"admin_graphql_api_id":"gid:\/\/shopify\/Customer\/115310627314723954","default_address":{"id":715243470612851245,"customer_id":115310627314723954,"first_name":null,"last_name":null,"company":null,"address1":"123 Elm St.","address2":null,"city":"Ottawa","province":"Ontario","country":"Canada","zip":"K2H7A8","phone":"123-123-1234","name":"","province_code":"ON","country_code":"CA","country_name":"Canada","default":true}}}';
        //$url='https://testapidev.patozon.net/api/shop/activity/product/getDetails';
//        $url = 'http://127.0.0.1:8006/api/shop/customer/createCustomer';
//        $request = '{"act_id":3,"source":1,"invite_code":"Iew9y23Q","accepts_marketing":1,"platform":"Shopify","action":"register","first_name":"jmiy","last_name":"jmiy55","account":"jmiy8999@qq.com","password":"123456","app_env":"sandbox","store_id":8}'; //,"ip":""
//        $url = 'https://brand-api.patozon.net/api/admin/customer/forceDelete';
//
//        $accountData = [
//            'mukhtar@hotmail.com'
//        ];
//        $offset = 0;
//        $length = 10;
//        while ($row = array_slice($accountData, $offset, $length)) {
//
//            $offset = $offset + $length;
//
//            if ($row) {
//                $request = [
//                    'store_id' => 1,
//                    "operator" => "jmiy_cen",
//                    "token" => "24ddec280ec6739b54c971e6e93c6fce_1584947436",
//                    'account' => implode(',', $row),
//                ];
//                $rs = $this->sendApiRequestByCurl($url, $request);
//            }
//
//        }
//        $url = 'http://192.168.152.128:81/api/shop/order/list';
//        $url = 'https://brand-api.patozon.net/api/shop/order/list';
//        $url = 'https://testapi.patozon.net/api/shop/activity/product/dealIndex';
//        $request = '{"aws_country":"US","account":"ugdntz61982@chacuo.net","act_id":8,"mb_type":2,"store_id":1}'; //,"ip":""
//        $url = 'http://192.168.152.128:8092/checkbox';
//        $request = 'colors[]=red&colors[]=green';//name=manu&
//        $request = [
////            'value' => 'uuuww',
////            "account" => "jmiy_cen",
////            "password" => "066a8a6ab9d35ad239fccb5f10bbd3c0_1569478555",
////            //'account' => 'erdemkanki@gmail.com',
////            'foo' => 'foo==',
////            'names' => ['ee' => 'uu', 'dd' => 'dd'],
////            'nick'=>'nick',
////            'message'=>'message',
//            'colors[]' => ["red","green","blue"]
//        ];
//        $request = '{"value":"foo56777","account":"user","act_id":8,"password":"password","store_id":1,"foo":"foo=="}'; //,"ip":""
//        $request = json_decode($request, true);
//        $url = 'http://192.168.152.128:81/api/admin/activity/product/export';//export
//        $request = '{"page_size":10,"page":1,"store_id":"1","operator":"jmiy_cen","token":"066a8a6ab9d35ad239fccb5f10bbd3c0_1569478555","asin":"","country":"","mb_type":"","start_time":"","end_time":"","click_start_time":"","click_end_time":"","act_id":"","product_status":"-1"}';
//        $url = 'http://192.168.152.128:81/api/common/dict/storeDictSelect';
//        $url = 'https://testapidev.patozon.net/api/admin/activity/product/editActProductItems';
//        $request = '{"store_id":"1","operator":"dev","id":"ActivityPrize-60","token":"97c33da8d6d12b16854f517afcd1f596_1586429082","itemdata":[{"act_id":23,"item_id":113,"act_name":"zhansan","act_type":3,"name":"30 Member Points","img_url":"11111.jpg","sku":"","asin":"","country":"us","qty":"1000","type_value":"31","probability":2}]}';
//        $url='http://192.168.152.128:81/api/admin/pub/export';
//        $request = '{"store_id":2,"operator":"jmiy_cen","token":"066a8a6ab9d35ad239fccb5f10bbd3c0_1569478555"}';
//        $url = 'http://192.168.152.128:81/api/shop/customer/createCustomer';
//        $request = '{"app_env":"sandbox","platform":"Shopify","action":"register","act_id":"0","store_id":3,"account":"Jmiy_cen@patazon.net","orderno":"112-6698824-7378655","order_country":"US","password":"123456","accepts_marketing":1}';
//        $url = 'http://192.168.152.128:81/api/shop/test';
//        $url = 'http://192.168.152.128:81/api/shop/activity/product/list';
//        https://testapi.patozon.net/api/shop/activity/winning/getLotteryNum
        //$url = 'http://192.168.152.128:81/api/shop/activity/order/exists';
//        $url = 'http://192.168.152.128:81/api/admin/reward/updateRewardStatus';
//        $url = 'https://testapi.patozon.net/api/shop/order/bind';
//        $url = 'https://release-api.patozon.net/api/shop/activity/order/exists';
//        $url = 'http://192.168.152.128:81/api/shop/activity/order/exists';
//        $url = 'https://testapidev.patozon.net/api/payment/paypal/notify';
//        $url = 'https://testapidev.patozon.net/api/admin/reward/list';
//        $url = 'http://192.168.152.128:81/api/admin/statistics/userNumsByTime';
//        $request = '{"operator":"dev","token":"aab6982474383485b60491a2dd75802a_1566454354","store_id":"1","type":"day","start_time":"2020-10-01 00:00:00","end_time":"2020-11-01 23:59:59","stat_type":1}';
//
//        $url = 'http://192.168.152.128:81/api/admin/statistics/userNumsByTime';
//        $request = '{"operator":"dev","token":"aab6982474383485b60491a2dd75802a_1566454354","store_id":"1","type":"day","start_time":"2016-11-16 00:00:00","end_time":"2020-12-29 00:00:00","stat_type":2}';
//
//        $url = 'http://192.168.152.128:81/api/admin/statistics/userNumsByField';
//        $request = '{"operator":"dev","token":"aab6982474383485b60491a2dd75802a_1566454354","store_id":"1","field":"store_id","start_time":"2020-10-01","end_time":"2020-11-25"}';//,"sta_store_id":1
//        $request = '{"operator":"dev","token":"aab6982474383485b60491a2dd75802a_1566454354","store_id":"1","field":"country","start_time":"2020-11-18","end_time":"2020-11-25","sta_store_id":1}';
//        $request = '{"operator":"dev","token":"aab6982474383485b60491a2dd75802a_1566454354","store_id":"1","field":"gender","start_time":"2020-11-18","end_time":"2020-11-25","sta_store_id":1}';
//        $request = '{"operator":"dev","token":"aab6982474383485b60491a2dd75802a_1566454354","store_id":"1","field":"brithday","start_time":"2020-11-18","end_time":"2020-11-25","sta_store_id":1}';
//        $request = '{"operator":"dev","token":"aab6982474383485b60491a2dd75802a_1566454354","store_id":"1","field":"source","start_time":"2020-11-18","end_time":"2020-11-25","sta_store_id":1}';


//        $url = 'http://192.168.152.128:81/api/admin/statistics/userNumsByCompared';
//        $request = '{"operator":"dev","token":"aab6982474383485b60491a2dd75802a_1566454354","store_id":"1","start_time":"2020-10-01"}';


//        $url = 'https://testapidev.patozon.net/api/admin/statistics/userNumsByCompared';
//        $request = '{"operator":"dev","token":"24c835cf61a73672ab25ffae2642da7f_1606113701","store_id":"1","start_time":"2020-10-01"}';

//        $url = 'http://192.168.152.128:81/api/admin/statistics/orderWarraytySta';
//        $request = '{"operator":"dev","token":"aab6982474383485b60491a2dd75802a_1566454354","store_id":"1","start_time":"2020-09-01","end_time":"2020-10-01"}';

//        //https://testbrand.patozon.net
//        $url = 'https://testbrand.patozon.net/api/admin/statistics/userNumsByField';
//        $request = '{"operator":"dev","token":"5d9414777a981eb555a6a2ee2c90f3c7_1606275954","store_id":"1","field":"source","start_time":"2020-11-18","end_time":"2020-11-25","sta_store_id":1}';

//        $url = 'http://192.168.152.128:81/api/admin/order/list';
//        $request = '{"operator":"dev","token":"aab6982474383485b60491a2dd75802a_1566454354","store_id":"1","order_country":"US","name":"8888","sku":"sku"}';

//        $url = 'http://192.168.152.128:82/api/shop/test';
//        $request = '{"operator":"dev","token":"aab6982474383485b60491a2dd75802a_1566454354","store_id":"1","order_country":"US","name":"8888","sku":"sku"}';
//
//        $url = 'http://192.168.152.128:82/api/shop/test';
//        $request = '{"operator":"dev","token":"aab6982474383485b60491a2dd75802a_1566454354","store_id":"1","order_country":"US","name":"8888","sku":"sku"}';
//        //https://brand-api.patozon.net/api/shop/activity/order/exists
//
//        $url = 'http://192.168.152.128:81/api/admin/user/info';

//        $url = 'https://testapidev.patozon.net/api/admin/user/info';
//        $url = 'http://192.168.152.128:82/api/admin/user/info';
        $url = 'http://192.168.152.128:82/api/shop/test';
        $request = '{"operator":"test","store_id":"1","token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYxNTE2Nzg2MiwiaWQiOiI0ODEifQ.0WacAMKBTTKrUv6xBdh42pRRAnymURSgr7M0a1N2LH0","is_psc":"1"}';

        $url = 'http://192.168.152.128:82/api/shop/customer/createCustomer/sandbox';
        $url = 'http://192.168.152.128:82/api/shop/customer/info';
        $url = 'https://release-api.patozon.net/api/shop/customer/createCustomer';
        $url = 'http://192.168.152.128:81/api/admin/orderReview/auditRemarks';
        //$url = 'http://192.168.152.128:81/api/admin/orderReview/audit';
        $url = 'http://192.168.152.128:81/api/admin/orderReview/list';
        //$url = 'http://192.168.152.128:81/api/admin/orderReview/export';
        //$url = 'http://192.168.152.128:81/api/admin/reward/add';
        //$url = 'http://192.168.152.128:81/api/admin/reward/list';
        //$url = 'http://192.168.152.128:81/api/admin/adminConfig/getAdminConfig';
        //$url = 'http://192.168.152.128:81/api/admin/adminConfig/userConfig';

//        $url = 'https://testapidev.patozon.net/api/admin/orderReview/auditRemarks';
//        //$url = 'https://testapidev.patozon.net/api/admin/orderReview/audit';
//        $url = 'https://testapidev.patozon.net/api/admin/orderReview/list';
//        $url = 'https://testapidev.patozon.net/api/admin/orderReview/export';
//        $url = 'https://testapidev.patozon.net/api/admin/reward/add';
//        $url = 'https://testapidev.patozon.net/api/admin/reward/list';
//
//        $url = 'https://testapidev.patozon.net/api/admin/adminConfig/getAdminConfig';
//        $url = 'https://testapidev.patozon.net/api/admin/adminConfig/userConfig';

        //注册
        $request = [
//            'store_id' => 8,
//            'account' => 'a@qq.com',
//            'country' => 'US',
//            'first_name' => 'first_name160',
//            'last_name' => 'last_name_sdddd',
//            'password' => '333',
//            'accepts_marketing' => 'on',
//            'invite_code' => '000D321B',
//            'source' => 2,

//            Constant::DB_TABLE_STORE_ID => 3,
//            Constant::DB_TABLE_ACCOUNT => FunctionHelper::randomStr(8).'@patazon.net', //
//            Constant::DB_TABLE_PLATFORM => Constant::PLATFORM_SERVICE_SHOPIFY,
//            Constant::DB_TABLE_PASSWORD => '123456',
//            Constant::DB_TABLE_ACTION => 'register',
//            'app_env' => "sandbox",
//            Constant::DB_TABLE_IP => 'ipip116677',
//            //'invite_code' => 'qjwi0HUg',
//            //'source' => 15,
//            'first_name' => 'first_name8',
//            'last_name' => 'last_name_8',
//            //Constant::DB_TABLE_ACT_ID=>38,

            Constant::DB_TABLE_STORE_ID => 14,
            //Constant::TOKEN => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYxNTE2Nzg2MiwiaWQiOiI0ODEifQ.0WacAMKBTTKrUv6xBdh42pRRAnymURSgr7M0a1N2LH0',
            Constant::TOKEN => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYxNTQyOTk4NiwiaWQiOiI2NzYifQ.ykMBde7t4mn7QEG8VWOe0h1dc0Lvo0MmX60Zy2k6JBM',
            "is_psc" => true,
            "operator" => "Jmiy_cen(岑永坚)",
//            Constant::DB_TABLE_REMARKS => [
//                [
//                    Constant::DB_TABLE_PRIMARY => 1,
//                    Constant::DB_TABLE_REMARKS => Constant::DB_TABLE_REMARKS . '1',
//                ],
//                [
//                    Constant::DB_TABLE_PRIMARY => 2,
//                    Constant::DB_TABLE_REMARKS => Constant::DB_TABLE_REMARKS . '2',
//                ],
//            ],
//            'ids' => [1,2],
//            Constant::AUDIT_STATUS => 3,
            Constant::REQUEST_PAGE => 1,
            Constant::REQUEST_PAGE_SIZE => 10,
//            Constant::BUSINESS_TYPE => 1,
//            Constant::DB_TABLE_NAME => 'gift cart',
//            Constant::DB_TABLE_TYPE => 1,
            'file' => $curl_file, //要上传的本地文件地址
//            'route'=>'/order/reviewList',
//            'data'=>[5,9,2,1,3],
            'sku' => 'sku',
            Constant::BUSINESS_TYPE=>1,
            Constant::DB_TABLE_NAME=>'coupon===89',
            Constant::DB_TABLE_TYPE=>2,
        ];

//                $url = 'http://192.168.152.128:82/api/shop/test';
////        $request = '{"operator":"test","store_id":"1","token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYwNzU3OTA2NSwiaWQiOiI2NzYifQ.FkqJRHX5cO2CnAHSCsKS8lNRkxJsI2byttgQY_Tt7xc","is_psc":"1"}';
//        $request = [
//            'store_id' => 8,
//            'file' => $curl_file, //要上传的本地文件地址
//        ];
//        $url = 'https://release-api.patozon.net/api/shop/activity/product/dealIndex';
//        $request = '{"aws_country":"UK","account":"pa1617372@163.com","act_id":4,"mb_type":2,"page":1,"page_size":30,"store_id":6,"client_access_url":"http://172.16.7.108:3004/pages/deal-product","app_env":"sandbox"}';
//        $url = 'http://127.0.0.1:5200/api/admin/order/list';
//        $request = '{"operator":"dev","token":"aab6982474383485b60491a2dd75802a_1566454354","store_id":"1","page_size":"1","page":"1"}';//,"order_country":"US","page_size":"1","page":"1" "page_size" => 100,
////            "page" => 1,

//        $url = 'https://testapidev.patozon.net/api/admin/order/list';
//        //$url = 'https://api-dev.patozon.net/api/admin/order/list';
//        //$url = 'https://172.16.6.192/api/admin/order/list';
//        $request = '{"operator":"dev","token":"09e82588da885d4f7e93e2af15b9a2e7_1607948137","store_id":"1","page_size":"1","page":"1"}';//"order_country":"US", ,"page_size":"1","page":"1" "page_size" => 100,
//////            "page" => 1,

//        $url = 'http://192.168.152.128:81/api/shop/contactus/add';
//        $url = 'https://testbrand.patozon.net/api/admin/user/info';
//        $request = '{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYwOTgzNDQxOSwiaWQiOiIxMTA1In0.O1nEqXygk3d-36x5R0H-WxnHhnVnJ_SGbTHA8oaOYf0","store_id":1,"is_psc":true,"operator":"operator"}';
//        $request = [
//            Constant::DB_TABLE_STORE_ID => 6,
//            Constant::DB_TABLE_ACCOUNT => FunctionHelper::randomStr(8).'@patazon.net', //
//            Constant::SUBJECT => '联系我们test',
//            Constant::DB_TABLE_EXT_ID =>0,
//            Constant::DB_TABLE_EXT_TYPE => '',
//            Constant::DB_TABLE_TOPIC => 'topic',
//            Constant::PRODUCT_TYPE => '',
//            Constant::DB_TABLE_ORDER_NO => '112-2236896-7897051',
//            Constant::EXCEPTION_MSG=>'msg===test',
//        ];

//        $filename = storage_path('logs/VTPC206BBIT deal-coupon.xlsx');
//        $minetype = 'image/jpeg';
//        $curl_file = curl_file_create($filename, $minetype); //
//        $request = [
//            Constant::DB_TABLE_STORE_ID => 2,
//            'operator'=>'dev',
//            'token'=>'aab6982474383485b60491a2dd75802a_1566454354',
//            'file' => $curl_file, //要上传的本地文件地址
//            'use_type'=>1,
//        ];
//
//        //$url = 'https://brandwtest.patozon.net/api/admin/coupon/importDeal';
//        $url = 'http://192.168.152.128:81/api/admin/coupon/importDeal';

//        \Swoole\Runtime::enableCoroutine(); // 此行代码后，文件操作，sleep，Mysqli，PDO，streams等都变成异步IO，见'一键协程化'章节 true,SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL
//        Coroutine::create(function () {
//            Coroutine::create(function () {
//                $storeIdData = [1, 2, 3, 5, 6, 7, 8, 9, 10];//,11
//                $request = [
//                    Constant::DB_TABLE_STORE_ID => $storeIdData[array_rand($storeIdData)],
//                ];
//                $url = 'http://192.168.152.128:82/api/shop/test';
//                $rs = $this->sendApiRequestByCurl($url, $request);
//            });
//        });
//        $storeIdData = [1,2,3,5,6,7,8,9,10];//,11
//        $request = [
//            Constant::DB_TABLE_STORE_ID => $storeIdData[array_rand($storeIdData)],
//        ];
//        $url = 'http://192.168.152.128:82/api/shop/test';
        //$url = 'http://192.168.152.128:81/api/admin/statistics/userNumsByField';
//        $url = 'http://192.168.152.128:9501/api/shop/encrypt/996?dd=2689';
//        $request = '{"operator":"Jmiy_cen(岑永坚)","token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYxMjE0Mzk2NSwiaWQiOiI3NDUifQ.lvWs18aQuNoXcepnCkqtwfWW6iI8GTZSZuU4-bddfHk","store_id":"1","field":"country","is_psc":true}';

//        $url = 'https://release-api.patozon.net/api/client/order/list';
//        $request = '{"account":"ovwblp67849@chacuo.net","platform":["Shopify","Localhost"],"order_type":2,"page_size":3,"page":1,"store_id":1,"client_access_url":"https://www.xmpow.com/pages/account-perks"}';

//        $url = 'http://192.168.152.128:9501/doc/filesystemFactory';
//        $request = [
//            'file' => $curl_file, //要上传的本地文件地址
//            'store_id' => 1,
//        ];
//        $url = 'http://192.168.152.128:81/api/admin/orderReview/list';
//        $request = '{"store_id":"6","token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYxNDgzOTUyMywiaWQiOiI0ODEifQ.41qlnIizkGzjII0AWwezZ5T3BAfHsJYvf-mzb6cVMfE","operator":"Jmiy_cen(岑永坚)","orderno":"","account":"","country":[],"asin":"","sku":"","type":[],"start_time":"","star":[],"end_time":"","audit_status":[],"page":1,"page_size":10,"is_psc":true}';

//        $url = 'http://192.168.152.128:81/api/admin/user/info';
//        //$url = 'https://brandwtest.patozon.net/api/admin/user/info';
//        $url = 'https://release-api.patozon.net/api/admin/user/info';
//        $request = '{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYxNjk4Mzc4MSwiaWQiOiI0ODEifQ.kKUkYMBai1W8ogfcgxebs0ME3SRmmIuEvKn8Atvpqlw","store_id":1,"is_psc":true,"operator":"operator"}';

        //$rs = $this->sendApiRequestByCurl($url, $request);

        $url='http://192.168.152.128:81/api/admin/customer/importShopfiyAppAccount';
        $url='https://testapidev.patozon.net/api/admin/customer/importShopfiyAppAccount';
        $url='https://testapi.patozon.net/api/admin/customer/importShopfiyAppAccount';

        //$url='http://192.168.152.128:81/api/admin/leaveMessage/list';
        $url='http://192.168.152.128:82/api/admin/order/list';
//        $url='https://testapidev.patozon.net/api/admin/leaveMessage/import';
//        $url='https://brandwtest.patozon.net/api/admin/leaveMessage/import';
        $url = "http://192.168.152.1:18084/account";
        $optionsData =  [
            [
                'name'      => Constant::DB_TABLE_STORE_ID,
                'contents'  => 1,
            ],
            [
                'name'      => Constant::TOKEN,
                'contents'  => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYyNDU5MjU4MywiaWQiOiI0ODEifQ.zcpLOgUv0fmz4iwFLDIvKCOh-S3gsEsFzsCFloTv1_Y',
            ],
            [
                'name'      => "is_psc",
                'contents'  => true,
            ],
            [
                'name'      => "operator",
                'contents'  => "Sunny_hong(洪磊)",
            ],
        ];
        $options = [

//            RequestOptions::BODY=>'{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYyNDU5MjU4MywiaWQiOiI0ODEifQ.zcpLOgUv0fmz4iwFLDIvKCOh-S3gsEsFzsCFloTv1_Y","is_psc":true,"store_id":1,"operator":"Jmiy_cen(岑永坚)","page_size":10,"page":1,"orderno":"","country":["US","CA"],"act_id":"","platform":"","sku":"","name":"","account":"","order_status":1}',

            RequestOptions::FORM_PARAMS=>[//表单提交
                'userId' => 'U100001',
                "money" => '1',
//                "operator" => "Jmiy_cen(岑永坚)",
//                Constant::TOKEN => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYyNDQ5NzI2MSwiaWQiOiI1MTAifQ.BmIHNfDROJkSBO-eRBqXu3cjrngaRemU-tgM08Nvccg',
//                Constant::DB_TABLE_STORE_ID => 2,
//                //Constant::DB_TABLE_ACCOUNT => 'Jmiy_cen@patazon.net',
//                Constant::REQUEST_PAGE => 1,
//                Constant::REQUEST_PAGE_SIZE => 10,
            ],

//            RequestOptions::MULTIPART => Arr::collapse([$optionsData,
//                [//表单上传文件
//                    [
//                        'name' => 'file',
//                        'contents' => fopen(storage_path('logs/吐槽版V2.0.xlsx'), 'r'),
//                        'filename' => '吐槽版V2.0.xlsx',
//                    ],
//                    [
//                        'name' => Constant::REQUEST_PAGE,
//                        'contents' => 1,
//                    ],
//                    [
//                        'name' => Constant::REQUEST_PAGE_SIZE,
//                        'contents' => 10,
//                    ],
//                    [
//                        'name' => Constant::DB_TABLE_ACCOUNT,
//                        'contents' => 'Jmiy_cen@patazon.net',
//                    ],
//                ]
//            ]),
        ];
//        $headers = [
//            'Content-Type'=>'application/json',
//        ];
        $headers = [];

////        //$url = 'http://192.168.152.128:81/api/shop/leaveMessage/add';
////        $url = 'http://192.168.152.128:81/api/shop/leaveMessage/list';
////        $url = 'http://192.168.152.128:81/api/statistical/report';
//
////        $url = 'https://testapidev.patozon.net/api/shop/leaveMessage/list';
////        $url = 'https://testapidev.patozon.net/api/statistical/report';
//
//        $url = 'http://192.168.152.128:81/api/shop/activity/getNums';
////        $url = 'http://192.168.152.128:81/api/shop/activity/follow';
////        $url = 'http://192.168.152.128:81/api/shop/activity/handle';
//
////        $url = 'https://testapidev.patozon.net/api/shop/activity/getNums';
////        $url = 'https://testapidev.patozon.net/api/shop/activity/follow';
////        $url = 'https://testapidev.patozon.net/api/shop/activity/handle';
////
////        $url = 'http://192.168.152.128:82/api/shop/customer/createCustomer';
////        //$url = 'http://192.168.152.128:82/api/shop/activity/follow';
////        $url = 'http://192.168.152.128:81/api/shop/activity/follow';
////        //$url = 'http://192.168.152.128:81/api/shop/customer/createCustomer';//64YSNBWK
////        $url = 'https://testapi.patozon.net/api/shop/customer/createCustomer';//BULkBrk4
////        $url = 'https://testapi.patozon.net/api/shop/activity/follow';
////
////        $url = 'http://192.168.152.1:18084/test1?store_id1=232&userId=3';
////        $url = 'http://192.168.152.1:18084/test?store_id1=232&userId=3';
////
////        $url = 'http://192.168.152.1:18084/testJson?store_id1=232&userId=3';
//
//        //$url = 'http://192.168.152.128:81/api/shop/activity/guess/users';
//
////        $url = 'http://192.168.152.128:81/api/shop/activity/getNums';
////        $url = 'http://192.168.152.128:81/api/shop/act/share';
//        $url = 'http://192.168.152.128:81/api/shop/act/handle';
//        $url = 'http://192.168.152.128:81/api/shop/activity/getCountdownTime';
////
////        $url = 'https://testapidev.patozon.net/api/shop/activity/getNums';
////        $url = 'https://testapidev.patozon.net/api/shop/act/share';
////        $url = 'https://testapidev.patozon.net/api/shop/act/handle';
//        //$url = 'https://testapidev.patozon.net/api/shop/activity/winning/list';
//        //$url = 'https://testapidev.patozon.net/api/shop/activity/winning/getRankData';
//
//
//        //$url = 'https://testapidev.patozon.net/api/shop/activity/order/exists';
//
//        //$url = 'http://192.168.152.128:81/api/shop/activity/order/exists';
//        //https://testapidev.patozon.net/api/shop/activity/apply/submit
//
//        $url = 'https://testapidev.patozon.net/api/shop/activity/getCountdownTime';
//
//        $options = [
//            RequestOptions::JSON => [//json
////                Constant::DB_TABLE_STORE_ID => 1,
////                Constant::DB_TABLE_ACCOUNT => 'Jmiy_cen@patazon.net',
////                Constant::DB_TABLE_ACT_ID => 49,
//                //Constant::EXCEPTION_MSG => 'msg 555',
////                Constant::REQUEST_PAGE => 1,
////                Constant::REQUEST_PAGE_SIZE => 10,
////                Constant::ACTION_TYPE => 5,//操作类型:1-4登陆相关,5:社媒关注, 6-20预留,21活动相关,其他值待定义 22:留言板相关
////                Constant::SUB_TYPE => 1,//1：滚动查看留言 2：点击留言
//
////                Constant::DB_TABLE_STORE_ID => 1,
////                Constant::DB_TABLE_ACCOUNT => '2p*359301@chacuo.net',
////                Constant::DB_TABLE_ACT_ID => 47,
////
////                Constant::ACTION_TYPE => 5,//操作类型:1-4登陆相关,5:社媒关注, 6-20预留,21活动相关,其他值待定义 22:留言板相关
////                Constant::SUB_TYPE => 1,//1：FB 2:TW
////                Constant::DB_TABLE_EXT_DATA => 'FB',
////
////                Constant::GUESS_NUM=>'001',
//
////                Constant::DB_TABLE_STORE_ID => 2,
//////                Constant::DB_TABLE_ACCOUNT => 'Jmiy_cen@patazon.net',
////                Constant::DB_TABLE_ACT_ID => 44,
//////                Constant::SOCIAL_MEDIA => 'FB',
//
//                Constant::DB_TABLE_STORE_ID => 2,
//                Constant::DB_TABLE_ACCOUNT => 'Jmiy_cen@patazon.net',
//                Constant::DB_TABLE_ACT_ID => 97,
//                //Constant::SOCIAL_MEDIA => 'FB',
//            ],
//
////            RequestOptions::FORM_PARAMS=>[//表单提交
////
////                Constant::DB_TABLE_STORE_ID => 1,
////
////                "is_psc" => true,
////                "operator" => "Jmiy_cen(岑永坚)",
////                Constant::TOKEN => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYyMDM2OTc5MCwiaWQiOiI2NzYifQ.wTVup_pql-ahBaMjAzIZX_hpTQkiSOAcEkDhbVs39EE',
//////                Constant::DB_TABLE_STORE_ID => 2,
//////                //Constant::DB_TABLE_ACCOUNT => 'Jmiy_cen@patazon.net',
//////                Constant::REQUEST_PAGE => 1,
//////                Constant::REQUEST_PAGE_SIZE => 10,
////            ],
//
////            RequestOptions::BODY=>'{"store_id":14,"act_id":1,"account":"test1234@qq.com","order_no":"112-9595771-4509062","client_access_url":"http://172.16.7.156:3010/pages/find-parts?a=1"}',
//        ];

        //
//        $headers = [
//            'Content-Type'=>'application/json',
//        ];
        //$rs = $this->sendApiRequestByGuzzleHttp($url, $options, 'POST', $headers);
        $rs = $this->sendApiRequestByGuzzleHttp($url, $options, 'POST', $headers);

        return Response::json($rs);

        //return response()->json($rs, 200);
    }

    /**
     * http client https://docs.guzzlephp.org/en/stable/
     */
    public function sendApiRequestByGuzzleHttp($url, $options,$method='POST',$headers=[])
    {
        $client = app(Client::class, [
            'config' => [
                //'base_uri' => 'https://www.baidu.com',
                // Use a shared client cookie jar
                RequestOptions::COOKIES => true,
            ],
        ]);

        $refer = 'https://api-localhost.com/'; //https://www.victsing.com/pages/vip-benefit
        $iv = '1234567891011121';

        $headers = Arr::collapse([[
            //'Content-Type'=>'application/json',
            //'Content-Type'=>'application/x-www-form-urlencoded',
            //'Content-Type'=>'multipart/form-data',

//            'Referer' => $refer,
//            'Version' => CURL_HTTP_VERSION_1_1,
//            'IvParameterSpec' => $iv,
//            'API_VERSION' => 27, //
//            //'Authorization: Bearer fa83e4f46be69a1417fd3de4bf6fa2a1',
//            //'Authorization: AUdCZgFK',
//            'Authorization' => 'Basic Zm9vOmJhcg==',
//            //'Content-Type' => 'application/json',
//            'Expect' => '',
//            'X-Requested-With' => 'XMLHttpRequest', //告诉服务器，当前请求是 ajax 请求
//            //'X-PJAX' => false,//告诉服务器在收到这样的请求的时候, 要返回 json 数据
//            //'X-PJAX' => true,//告诉服务器在收到这样的请求的时候, 只需要渲染部分页面返回就可以了
//            //'Accept' => '+json',//告诉服务器，要返回 json 数据
//            //'Accept' => '/json', //告诉服务器，要返回 json 数据
//            'X-Shopify-Hmac-Sha256' => 'aVB8fEJErbweBCKDsc5MI2kzR8JrfEgUM25Be1NWSQs=',
//            'X-Token' => '5d3addb5ddaec3a58d3809010adbf427_1564474859',
        ], $headers]);

        $ip = '127.0.0.1';
        $remotesKeys = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'X_FORWARDED_FOR',
            'CLIENT_IP',
            'X_FORWARDED',
            'FORWARDED_FOR',
            'FORWARDED',
            'ADDR',
            'X_CLUSTER_CLIENT_IP',
            'X-FORWARDED-FOR',
            'CLIENT-IP',
            'X-FORWARDED',
            'FORWARDED-FOR',
            'FORWARDED',
            'REMOTE-ADDR',
            'X-CLUSTER-CLIENT-IP',
        ];
        foreach ($remotesKeys as $remotesKey) {
            $headers[$remotesKey] = $ip;
        }

        $curlInfo = [];
        $errmsg = '';
        $options = Arr::collapse([[
            RequestOptions::HEADERS => $headers,
            //on_stats curl_getinfo
            \GuzzleHttp\RequestOptions::ON_STATS => function (\GuzzleHttp\TransferStats $stats) use (&$curlInfo, &$errmsg) {

                //var_dump(\GuzzleHttp\Psr7\Message::toString($stats->getRequest()));

                $curlInfo = $stats->getHandlerStats();
                $request = $stats->getRequest();
                $data = [
                    'request_method' => $request->getMethod(),//响应状态码 200
                    'request_uri' => $request->getUri(),//响应状态码 200
                    'request_protocol' => $request->getProtocolVersion(),//协议版本
                    'request_headers' => $request->getHeaders(),
                    //'request_body' => (string)$request->getBody(),//__toString(),//->getContents(),//响应body
                    'request_body' => $request->getBody()->__toString(),//->getContents(),//响应body

                    'EffectiveUri' => $stats->getEffectiveUri()->__toString(),
                    'TransferTime' => $stats->getTransferTime(),
                    //'curl_getinfo' =>$curlInfo,//curl_getinfo  (注意：stream=true 时 使用stream 发起请求 无法获取详细的响应数据)
                ];
                dump($data);

                if ($stats->hasResponse()) {
                    //var_dump($stats->getResponse()->getStatusCode());
                } else {
                    // Error data is handler specific. You will need to know what
                    // type of error data your handler uses before using this
                    // value.
                    //var_dump($stats->getHandlerErrorData());
                    $errmsg = $stats->getHandlerErrorData();
                }
            },
            //RequestOptions::VERSION => '2.0',
            //RequestOptions::BODY=>'{"store_id":"6","token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYxNDgzOTUyMywiaWQiOiI0ODEifQ.41qlnIizkGzjII0AWwezZ5T3BAfHsJYvf-mzb6cVMfE","operator":"Jmiy_cen(岑永坚)","orderno":"","account":"","country":[],"asin":"","sku":"","type":[],"start_time":"","star":[],"end_time":"","audit_status":[],"page":1,"page_size":10,"is_psc":true}',
//            RequestOptions::FORM_PARAMS=>[//表单提交
//                'a'=>8888,
//            ],
//
//            RequestOptions::MULTIPART => [//表单上传文件
//                [
//                    'name'     => 'file',
//                    'contents' => fopen(BASE_PATH . '/storage/thumbnail-1.png', 'r'),
//                    'filename' => 'thumbnail-1.png',
//                ],
//                [
//                    'name'      => 'tags',
//                    'contents'  => json_encode([
//                        "external" => [
//                            "tenantId" => 23,
//                            "author" => 34,
//                            "description" => "these are additional tags"
//                        ]
//                    ])
//                ],
//            ],
//
//            RequestOptions::JSON=>[//json
////                [
////                    'name'     => 'file',
////                    'contents' => fopen(BASE_PATH . '/storage/thumbnail-1.png', 'r'),
////                    'filename' => 'thumbnail-1.png',
////                ],
//                [
//                    'name'      => 'tags',
//                    'contents'  => [
//                        "external" => [
//                            "tenantId" => 23,
//                            "author" => 34,
//                            "description" => "these are additional tags"
//                        ]
//                    ],
//                ],
//            ],
        ], $options]);

        $response = $client->request($method, $url, $options);

        $responseBody = $response->getBody()->getContents();//响应body
        $responseData = [
            'ProtocolVersion' => $response->getProtocolVersion(),//协议版本
            'StatusCode' => $response->getStatusCode(),//响应状态码
            'ReasonPhrase' => $response->getReasonPhrase(),//响应状态码描述
            'responseHeaders' => $response->getHeaders(),//响应头
            //'responseBody'=>$responseBody,//响应body
            'responseText' => [],//响应body
            'errmsg' => $errmsg,
            'curlInfo' => $curlInfo,
        ];

        if ($responseBody) {
            //dump($responseBody);
            data_set($responseData, 'responseText', json_decode($responseBody, true));
        }

        dump($responseData);

        return $responseData;
    }

    public function cache(Request $request) {
        /*         * ******************缓存 start https://learnku.com/docs/laravel/5.8/cache/3915 *************** */

        $value = Cache::remember('key_test_release', 600, function () {
                    return 'value111===release';
                });
        return Response::json(['cache' => $value]);
        exit;

        Cache::put('key_test', 'value111', 600);
        var_dump(Cache::get('key_test'));

        //访问多个缓存存储
        //使用 Cache Facade，你可以通过 store 方法来访问各种缓存存储。传入 store 方法的键应该对应 cache 配置信息文件中的 stores 配置数组中所列的存储之一：
        $value = Cache::store('file')->get('foo');
        Cache::store('redis')->put('bar', 'baz', 600); // 10 分钟
        //从缓存中获取数据
        //Cache Facade 的 get 方法是用来从缓存中获取数据的方法。如果该数据在缓存中不存在，那么该方法将返回 null 。正如你想的那样，你也可以向 get 方法传递第二个参数，用来指定如果查找的数据不存在时你希望返回的默认值：
        $value = Cache::get('key');
        $value = Cache::get('key', 'default');

        //你甚至可以传递 Closure 作为默认值。如果指定的数据在缓存中不存在，将返回 Closure 的结果。传递闭包的方法允许你从数据库或其他外部服务中获取默认值：
        $value = Cache::get('key', function () {
                    return DB::table('')->get();
                });

        //检查缓存项是否存在
        //has 方法可以用于判断缓存项是否存在。如果为 null 或 false 则该方法将会返回 false ：
        if (Cache::has('key')) {
            //
        }

        //递增与递减值
        //increment 和 decrement 方法可以用来调整缓存中整数项的值。这两个方法都可以传入第二个可选参数，这个参数用来指明要递增或递减的数量：
        Cache::increment('key');
        Cache::increment('key', $amount);
        Cache::decrement('key');
        Cache::decrement('key', $amount);

        //获取和存储
        //有时你可能想从缓存中获取一个数据，而当请求的缓存项不存在时，程序能为你存储一个默认值。例如，你可能想从缓存中获取所有用户，当缓存中不存在这些用户时，程序将从数据库将这些用户取出并放入缓存。你可以使用 Cache::remember 方法来实现：
        $value = Cache::remember('users', $seconds, function () {
                    return DB::table('users')->get();
                });
        //如果缓存中不存在你想要的数据时，则传递给 remember 方法的 闭包 将被执行，然后将其结果返回并放置到缓存中。
        //你可以使用 rememberForever 方法从缓存中获取数据或者永久存储它：
        $value = Cache::rememberForever('users', function () {
                    return DB::table('users')->get();
                });

        //获取和删除
        //如果你需要从缓存中获取到数据之后再删除它，你可以使用 pull 方法。和 get 方法一样，如果缓存不存在，则返回 null ：
        $value = Cache::pull('key');

        //###在缓存中存储数据
        //你可以使用 Cache Facade 的 put 方法将数据存储到缓存中：
        Cache::put('key', 'value', $seconds);

        //如果缓存的过期时间没有传递给 put 方法， 则缓存将永久有效：
        Cache::put('key', 'value');

        //除了以整数形式传递过期时间的秒数，你还可以传递一个 DateTime 实例来表示该数据的过期时间：
        Cache::put('key', 'value', now()->addMinutes(10));

        //只存储没有的数据
        //add 方法将只存储缓存中不存在的数据。如果存储成功，将返回 true ，否则返回 false ：
        Cache::add('key', 'value', $seconds);

        //数据永久存储
        //forever 方法可用于持久化将数据存储到缓存中。因为这些数据不会过期，所以必须通过 forget 方法从缓存中手动删除它们：
        Cache::forever('key', 'value');
        //{tip} 如果你使用 Memcached 驱动，当缓存数据达到存储上限时，「永久存储」 的数据可能会被删除。
        //###删除缓存中的数据
        //你可以使用 forget 方法从缓存中删除这些数据：
        Cache::forget('key');
        //你也可以通过提供零或者负的 TTL 值删除这些数据：
        Cache::put('key', 'value', 0);
        Cache::put('key', 'value', -5);

        //你可以使用 flush 方法清空所有的缓存：
        Cache::flush();
        //{note} 清空缓存的方法并不会考虑缓存前缀，会将缓存中的所有内容删除。因此在清除与其它应用程序共享的缓存时，请慎重考虑。
        //###原子锁
        //{note} 要使用该特性，你的应用必须使用 memcached， dynamodb 或 redis 缓存驱动作为你应用的默认缓存驱动。此外，所有服务器必须与同一中央缓存服务器进行通信。
        //原子锁允许对分布式锁进行操作而不必担心竞争条件。例如， Laravel Forge 使用原子锁来确保在一台服务器上每次只有一个远程任务在执行。你可以使用 Cache::lock 方法来创建和管理锁：
        $lock = Cache::lock('foo', 10);
        if ($lock->get()) {
            //获取锁定10秒...

            $lock->release();
        }

        //get 方法也可以接收一个闭包。在闭包执行之后，Laravel 将会自动释放锁：
        Cache::lock('foo')->get(function () {
            // 锁无限期获取并自动释放...
        });

        //如果你在请求时锁无法使用，你可以控制 Laravel 等待指定的秒数。如果在指定的时间限制内无法获取锁，则会抛出 Illuminate\Contracts\Cache\LockTimeoutException ：
        //use Illuminate\Contracts\Cache\LockTimeoutException;
        $lock = Cache::lock('foo', 10);
        try {
            $lock->block(5); // 等待最多5秒后获取的锁...
        } catch (LockTimeoutException $e) {
            // 无法获取锁...
        } finally {
            optional($lock)->release();
        }
        Cache::lock('foo', 10)->block(5, function () {
            // 等待最多5秒后获取的锁...
        });

        //###管理跨进程的锁
        //有时，你希望在一个进程中获取锁并在另外一个进程中释放它。例如，你可以在 Web 请求期间获取锁，并希望在该请求触发的队列作业结束时释放锁。在这种情况下，你应该将锁的作用域「owner token」传递给队列作业，以便作业可以使用给定的 token 重新实例化锁：
        // 控制器里面...
        $podcast = Podcast::find($id);
        if ($lock = Cache::lock('foo', 120)->get()) {
            ProcessPodcast::dispatch($podcast, $lock->owner());
        }

        // ProcessPodcast Job 里面...
        Cache::restoreLock('foo', $this->owner)->release();

        //如果你想在不尊重当前锁的所有者的情况下释放锁，你可以使用 forceRelease 方法：
        Cache::lock('foo')->forceRelease();

        //###Cache 辅助函数
        //除了可以使用 Cache Facade 或 Cache 契约 外，你还可以使用全局辅助函数 cache 来获取和保存缓存数据。当 cache 函数只接收一个字符串参数的时候，它将会返回给定键对应的值：
        $value = cache('key');

        //如果你向函数提供了一组键值对和过期时间，它将会在指定时间内缓存数据：
        cache(['key' => 'value'], $seconds);
        cache(['key' => 'value'], now()->addMinutes(10));

        //当 cache 函数在没有任何参数的情况下被调用时，它返回一个 Illuminate\Contracts\Cache\Factory 实现的实例，允许你调用其它缓存方法：
        cache()->remember('users', $seconds, function () {
            return DB::table('users')->get();
        });
        //{tip} 如果在测试中使用全局辅助函数 cache ，你可以使用 Cache::shouldReceive 方法就像 测试 Facade。
        //###缓存标记
        //{note} 缓存标记不支持使用 file 和 database 缓存驱动。此外，当使用多个缓存标记的缓存设置为「永久」时，类似 memcached 的缓存驱动性能最佳，它会自动清除旧的记录。
        //写入被标记的缓存数据
        //缓存标记允许你给缓存相关进行标记，以便后续清除这些缓存值。你可以通过传入标记名称的有序数组来访问标记的缓存。例如，我们可以使用标记的同时使用 put 方法设置缓存。
        Cache::tags(['people', 'artists'])->put('John', $john, $seconds);
        Cache::tags(['people', 'authors'])->put('Anne', $anne, $seconds);

        //访问被标记的缓存数据
        //若要获取一个被标记的缓存数据，请将相同的有序标记数组传递给 tags 方法，然后调用 get 方法来获取你要检索的键：
        $john = Cache::tags(['people', 'artists'])->get('John');
        $anne = Cache::tags(['people', 'authors'])->get('Anne');

        //移除被标记的缓存数据
        //你可以清空有单个标记或是一组标记的所有缓存数据。例如，下面的语句会把被标记为 people，authors 或两者都有的缓存移除。所以，Anne 和 John 都会从缓存中被删除：
        //Cache::tags(['people', 'authors'])->flush();
        //相反，下面的语句只会删除被标记 authors 的缓存，所以 Anne 会被删除，但 John 不会：
        Cache::tags('authors')->flush();

        $tag = \App\Services\OrdersService::getCacheTags();
        $service = \App\Services\OrdersService::getNamespaceClass();
        $cacheKey = $tag . ':lock11';
        $method = __FUNCTION__;
        $parameters = func_get_args();
        $handleCacheData = [
            'service' => $service,
            'method' => 'lock',
            'parameters' => [//获取锁定10秒...
                $cacheKey, 10
            ],
            'serialHandle' => [
                [
                    'service' => $service,
                    'method' => 'get',
                    'parameters' => [
                        function () use($service, $method, $parameters) {
                            dump('测试分布式锁抛出异常，不释放锁==========');

                            return [898989];
                            $code = 2;
                            $msg = '测试分布式锁抛出异常，不释放锁';
                            throw new \Exception($msg, $code);
                        }
                    ],
                ]
            ]
        ];

        $rs = \App\Services\OrdersService::handleCache($tag, $handleCacheData);
        dd($rs, 5555);
    }

    public function auth(Request $request) {

        $user = Auth::guard('apiAdmin')->user();

        //$user = $request->user('apiAdmin');
        return Response::json($user);
    }

    public function transaction(Request $request) {
        DB::beginTransaction();
        try {
            DB::commit();
        } catch (\Exception $e) {
            // 出错回滚
            DB::rollBack();
            LogService::addSystemLog('log', 'signup', 'signup', '注册异常', $e->getTraceAsString()); //添加系统日志
            $reg = false;
        }
    }

    public function carbon(Request $request) {

        dd(Carbon::now()->toDateTimeString());

        dd(Carbon::now()->rawFormat('M. j, Y'));
        //date_default_timezone_set('America/Los_Angeles'); //设置app时区
//        $config = app('config');
//        $config->set('app.timezone', 'America/Los_Angeles');
//        var_dump(config('app.timezone'));
        //date_default_timezone_set('America/Edmonton'); //设置app时区  America/Denver
//        $dst = date('I');  //判断是否夏令时
//        var_dump($dst);
//        var_dump(Carbon::parse(Carbon::parse()->toDateTimeString())->toIso8601String(), date_default_timezone_get());
//        exit;
        //config(['app.timezone' => 'Europe/London']);
//        date_default_timezone_set(config('app.timezone'));
//        echo Carbon::now()->timezoneName;                            // America/Toronto
//        var_dump(config('app.db_timezone'));
//        var_dump(config('database.connections.mysql.timezone'));
//        exit;
        //var_dump(Carbon::now()->toIso8601String());//2019-05-24T13:33:39+08:00 shipif 时间 2019-04-18T15:42:26-04:00
        //var_dump(Carbon::now()->toIso8601ZuluString());//2019-05-24T05:36:01Z Amazon 时间 2017-09-30T11:36:29Z
        //var_dump(Carbon::now()->toRfc1036String());//Fri, 24 May 19 13:12:41 +0800
        //var_dump(Carbon::now()->toRfc1123String());//Fri, 24 May 2019 13:14:26 +0800
        //var_dump(Carbon::now()->toRfc2822String());//Fri, 24 May 2019 13:15:20 +0800
        //var_dump(Carbon::now()->toRfc7231String());//Fri, 24 May 2019 05:19:06 GMT 格林威治标准时间
        //var_dump(Carbon::now()->toRfc822String());//Fri, 24 May 19 13:26:29 +0800
        //var_dump(Carbon::now()->toRfc850String());//Friday, 24-May-19 13:29:12 CST
        //var_dump(Carbon::now()->toRssString());//Fri, 24 May 2019 13:30:10 +0800
        //var_dump(Carbon::now()->toString());//Fri May 24 2019 13:31:23 GMT+0800 Returns english human readable complete date string.
        //var_dump(Carbon::now()->toTimeString());//13:32:26
        //
        //var_dump(Carbon::now()->toRfc3339String());//2019-05-24T13:17:25+08:00
        //var_dump(Carbon::now()->toW3cString());//2019-05-24T13:32:55+08:00
        //var_dump(Carbon::now()->toAtomString());//2019-05-24T13:56:14+08:00
        //
        dd(Carbon::now()->toFormattedDateString()); //May 24, 2019
        //var_dump(Carbon::now()->toDayDateTimeString());//Fri, May 24, 2019 1:41 PM
        //var_dump(Carbon::now()->toDateTimeString());//2019-05-24 13:43:40
        //var_dump(Carbon::now()->toDateTimeLocalString());//2019-05-24T13:45:20
        //var_dump(Carbon::now()->toDateTime());//object(DateTime)#955 (3) { ["date"]=> string(26) "2019-05-24 13:46:18.066000" ["timezone_type"]=> int(3) ["timezone"]=> string(13) "Asia/Shanghai" }
        //var_dump(Carbon::now()->toDateString());//2019-05-24
        //var_dump(Carbon::now()->toDate());//object(DateTime)#955 (3) { ["date"]=> string(26) "2019-05-24 13:53:45.591000" ["timezone_type"]=> int(3) ["timezone"]=> string(13) "Asia/Shanghai" }
        //var_dump(Carbon::now()->toCookieString());//Friday, 24-May-2019 13:54:37 CST
        //
        //var_dump(Carbon::now()->toArray());//array(12) { ["year"]=> int(2019) ["month"]=> int(5) ["day"]=> int(24) ["dayOfWeek"]=> int(5) ["dayOfYear"]=> int(144) ["hour"]=> int(13) ["minute"]=> int(57) ["second"]=> int(15) ["micro"]=> int(268000) ["timestamp"]=> int(1558677435) ["formatted"]=> string(19) "2019-05-24 13:57:15" ["timezone"]=> object(Carbon\CarbonTimeZone)#956 (2) { ["timezone_type"]=> int(3) ["timezone"]=> string(13) "Asia/Shanghai" } }
        //var_dump(Carbon::parse('2010-04-18T15:42:26-04:00')->age); //返回年龄
//        var_dump(Carbon::parse('2019-04-18T15:42:26-04:00')->toDateTimeString());
//        var_dump(Carbon::parse(Carbon::parse('2019-04-18T15:42:26-04:00')->toDateTimeString())->toIso8601String());
//
        //####Getters
        //获取器通过PHP的 __get() 方式实现。可以直接通过一下方式直接获取到属性的值。
        $dt = Carbon::parse('2012-9-5 23:26:11.123789');
        // These getters specifically return integers, ie intval()
        var_dump($dt->year);                                         // int(2012)
        var_dump($dt->month);                                        // int(9)
        var_dump($dt->day);                                          // int(5)
        var_dump($dt->hour);                                         // int(23)
        var_dump($dt->minute);                                       // int(26)
        var_dump($dt->second);                                       // int(11)
        var_dump($dt->micro);                                        // int(123789)
        var_dump($dt->dayOfWeek);                                    // int(3)
        var_dump($dt->dayOfYear);                                    // int(248)
        var_dump($dt->weekOfMonth);                                  // int(1)
        var_dump($dt->weekOfYear);                                   // int(36)
        var_dump($dt->daysInMonth);                                  // int(30)
        var_dump($dt->timestamp);                                    // int(1346901971)
        var_dump(Carbon::createFromDate(1975, 5, 21)->age);          // int(41) calculated vs now in the same tz
        var_dump($dt->quarter);                                      // int(3)
        // Returns an int of seconds difference from UTC (+/- sign included)
        var_dump(Carbon::createFromTimestampUTC(0)->offset);         // int(0)
        var_dump(Carbon::createFromTimestamp(0)->offset);            // int(-18000)
        // Returns an int of hours difference from UTC (+/- sign included)
        var_dump(Carbon::createFromTimestamp(0)->offsetHours);       // int(-5)
        // Indicates if day light savings time is on
        var_dump(Carbon::createFromDate(2012, 1, 1)->dst);           // bool(false)
        var_dump(Carbon::createFromDate(2012, 9, 1)->dst);           // bool(true)
        // Indicates if the instance is in the same timezone as the local timezone
        var_dump(Carbon::now()->local);                              // bool(true)
        var_dump(Carbon::now('America/Vancouver')->local);           // bool(false)
        // Indicates if the instance is in the UTC timezone
        var_dump(Carbon::now()->utc);                                // bool(false)
        var_dump(Carbon::now('Europe/London')->utc);                 // bool(false)
        var_dump(Carbon::createFromTimestampUTC(0)->utc);            // bool(true)
        // Gets the DateTimeZone instance
        echo get_class(Carbon::now()->timezone);                     // DateTimeZone
        echo get_class(Carbon::now()->tz);                           // DateTimeZone
        // Gets the DateTimeZone instance name, shortcut for ->timezone->getName()
        echo Carbon::now()->timezoneName;                            // America/Toronto
        echo Carbon::now()->tzName;                                  // America/Toronto
        //
        //###Setters
        //Setters 通过PHP的 __set() 方法实现。值得注意的是，通过这种方式设置时间戳时，时区不会相对于时间戳而改变。如果需要改变时区的话，需要针对时区单独设置。
        $dt = Carbon::now();
        $dt->year = 1975;
        $dt->month = 13;             // would force year++ and month = 1
        $dt->month = 5;
        $dt->day = 21;
        $dt->hour = 22;
        $dt->minute = 32;
        $dt->second = 5;

        $dt->timestamp = 169957925;  // This will not change the timezone
        // Set the timezone via DateTimeZone instance or string
        $dt->timezone = new DateTimeZone('Europe/London');
        $dt->timezone = 'Europe/London';
        $dt->tz = 'Europe/London';
        //
        //###Fluent Setters
        //此处 Setters 方法的参数是必选参数，Carbon 提供了更多种设置方式可供使用。值得注意的是，所有对于时区的修改都会影响整个到 Carbon 实例。对时间戳进行修改时不会自动转换到时间戳对应的时区。
        $dt = Carbon::now();
        $dt->year(1975)->month(5)->day(21)->hour(22)->minute(32)->second(5)->toDateTimeString();
        $dt->setDate(1975, 5, 21)->setTime(22, 32, 5)->toDateTimeString();
        $dt->setDateTime(1975, 5, 21, 22, 32, 5)->toDateTimeString();
        $dt->timestamp(169957925)->timezone('Europe/London');
        $dt->tz('America/Toronto')->setTimezone('America/Vancouver');
        exit;
    }

    public function redis(Request $request) {
        /*         * ******************Redis start https://learnku.com/docs/laravel/5.8/redis/3930#configuration *************** */
        $zsetKey = 'zset1';
        $offset = 1;
        $count = 10;
        $data = Redis::zscan($zsetKey, $offset, '*', $count); //, false, $count
        var_dump($data);

        //var_dump(Redis::command('ZSCAN', ['zset1', 0, 'MATCH *', [10]]));
        exit;

        //var_dump(Redis::ZADD('zset1', 1, 'member'));
//        var_dump(Redis::ZINCRBY('zset1', 1, 'member'));
//        var_dump(Redis::ZINCRBY('zset1', 2, 'member1'));
//        var_dump(Redis::ZINCRBY('zset1', 3, 'member2'));
//
//        var_dump(Redis::ZINCRBY('zset1', 1, 'member3'));
//        var_dump(Redis::ZINCRBY('zset1', 2, 'member4'));
//        var_dump(Redis::ZINCRBY('zset1', 3, 'member5'));
//
//        var_dump(Redis::ZINCRBY('zset1', 1, 'member6'));
//        var_dump(Redis::ZINCRBY('zset1', 2, 'member7'));
//        var_dump(Redis::ZINCRBY('zset1', 3, 'member8'));
//
//        var_dump(Redis::ZINCRBY('zset1', 1, 'member9'));
//        var_dump(Redis::ZINCRBY('zset1', 2, 'member10'));
//        var_dump(Redis::ZINCRBY('zset1', 3, 'member11'));
        //var_dump(Redis::ZREVRANGE('zset1', 0, -1, 'WITHSCORES')); //, ['LIMIT', 0, 1]
        //var_dump(Redis::del($key));

        $options = [
            'withscores' => true,
            'limit' => [
                'offset' => 0,
                'count' => 10,
            ]
        ];
        $data = Redis::zrevrangebyscore('zset1', '+inf', '-inf', $options);
        var_dump($data);
//        var_dump(Redis::zrevrank('zset1', 'member1-1'));
//        var_dump(Redis::command('ZRANK', ['zset1', 'member2']));
//        var_dump(Redis::command('ZREVRANGEBYSCORE', ['zset1', '+inf', '-inf',$options]));
//        $redis = Redis::connection('cache');
//        var_dump($redis);
//        var_dump($redis->set('name', 'Taylor'));
//        var_dump($redis->get('name'));
        exit;
    }

    /**
     * https://learnku.com/courses/laravel-package/get-the-corresponding-geo-location-information-through-ip-toranngeoip/2024
     * @param Request $request
     */
    public function geoip(Request $request) {
//        var_dump(geoip($request->ip())->toArray());
//        var_dump(geoip('84.64.57.249')->toArray());
//        var_dump(geoip('97.118.149.18')->toArray());
        var_dump(geoip('96.54.17.176')->toArray());
        //var_dump(GeoIp::getLocation('84.64.57.249'));

        exit;
    }

    /**
     * https://gitee.com/viest/php-ext-xlswriter#PECL
     * @param Request $request
     */
    public function excel(Request $request) {
        $config = ['path' => storage_path('logs')];

        //游标读取(按行读取)
        $excel = new \Vtiful\Kernel\Excel($config);

//        //
//        $filePath = $excel->fileName('tutorial.xlsx')
//                ->header(['Item', 'Cost'])
//                ->output();
        //全量读取
//        $data = $excel->openFile('ES_B07BQ1M9K8.xlsx')
//                ->openSheet()
//                ->getSheetData();
//        dd($data);
        //游标读取(按行读取)
        $excel->openFile('ES_B07BQ1M9K8.xlsx')
                ->openSheet();

        //var_dump($excel->nextRow()); // ['Item', 'Cost']
        //var_dump($excel->nextRow()); // NULL

        while ($row = $excel->nextRow()) {
            dump($row);
        }
        dd(1);

//        $excel = new \Vtiful\Kernel\Excel($config);
//        // fileName 会自动创建一个工作表，你可以自定义该工作表名称，工作表名称为可选参数
//        $filePath = $excel
//                ->fileName('tutorial01.xlsx', 'sheet1')
//                ->header(['Item', 'Cost'])
//                ->data([
//                    ['Rent', 1000],
//                    ['Gas', 100],
//                    ['Food', 300],
//                    ['Gym', 50],
//                ])
//                ->output();
        //图表添加数据
//        $fileObject = new \Vtiful\Kernel\Excel($config);
//        $fileObject = $fileObject->fileName('chart.xlsx');
//        $fileHandle = $fileObject->getHandle();
//
//        //直方图
//        $chart = new \Vtiful\Kernel\Chart($fileHandle, \Vtiful\Kernel\Chart::CHART_COLUMN);
//        $chartResource = $chart
//                ->series('Sheet1!$A$2:$A$6')
//                ->seriesName('=Sheet1!$A$1')
//                ->series('Sheet1!$B$2:$B$6')
//                ->seriesName('=Sheet1!$B$1')
//                ->series('Sheet1!$C$2:$C$6')
//                ->seriesName('=Sheet1!$C$1')
//                ->toResource();
//
//        $filePath = $fileObject
//                        ->header(['Number', 'Batch 1', 'Batch 2'])
//                        ->data([
//                            [1, 2, 3],
//                            [2, 4, 6],
//                            [3, 6, 9],
//                            [4, 8, 12],
//                            [5, 10, 15],
//                        ])->insertChart(0, 3, $chartResource)->output();
//        exit;
//
//        //面积图
//        $config = ['path' => storage_path('logs')];
//        $fileObject = new \Vtiful\Kernel\Excel($config);
//        $fileObject = $fileObject->fileName('CHART_AREA.xlsx');
//        $fileHandle = $fileObject->getHandle();
//        $chart = new \Vtiful\Kernel\Chart($fileHandle, \Vtiful\Kernel\Chart::CHART_AREA);
//
//        $chartResource = $chart
//                ->series('=Sheet1!$B$2:$B$7', '=Sheet1!$A$2:$A$7')
//                ->seriesName('=Sheet1!$B$1')
//                ->series('=Sheet1!$C$2:$C$7', '=Sheet1!$A$2:$A$7')
//                ->seriesName('=Sheet1!$C$1')
//                ->style(11)// 值为 1 - 48，可参考 Excel 2007 "设计" 选项卡中的 48 种样式
//                ->axisNameX('Test number') // 设置 X 轴名称
//                ->axisNameY('Sample length (mm)') // 设置 Y 轴名称
//                ->title('Results of sample analysis') // 设置图表 Title
//                ->toResource();
//
//        $filePath = $fileObject->header(['Number', 'Batch 1', 'Batch 2'])
//                        ->data([
//                            [2, 40, 30],
//                            [3, 40, 25],
//                            [4, 50, 30],
//                            [5, 30, 10],
//                            [6, 25, 5],
//                            [7, 50, 10],
//                        ])->insertChart(0, 3, $chartResource)->output();
//        exit;
//
//        //单元格插入文字
//        函数原型
//        insertText(int $row, int $column, string|int|double $data[, string $format, resource $style])
//        int $row
//        单元格所在行
//
//        int $column
//        单元格所在列
//
//        string | int | double $data
//        需要写入的内容
//
//        string $format
//        内容格式
//
//        resource $style
//        单元格样式


        $excel = new \Vtiful\Kernel\Excel($config);
        $textFile = $excel->fileName("free32.xlsx")
                ->header(['customer_id', 'store_customer_id', 'store_id', 'account', 'status', 'ctime', 'source']);
        $row = 0;
        \App\Models\Customer::where('store_id', 1)->select(['*'])
                ->chunk(100, function ($data) use($textFile, &$row) {
                    if ($data) {
                        foreach ($data as $item) {
                            $row = $row + 1;
                            $item = $item->toArray();
                            $item = array_values($item);
                            foreach ($item as $key => $value) {
                                $textFile->insertText($row, $key, $value);
                            }

//                            $textFile->insertText($row, 1, $item->store_customer_id, '#,##0');
//                            $textFile->insertText($row, 2, $item->store_id, '#,##0');
//                            $textFile->insertText($row, 3, $item->account, '#,##0');
//                            $textFile->insertText($row, 4, $item->status, '#,##0');
//                            $textFile->insertText($row, 5, $item->ctime, '#,##0');
//                            $textFile->insertText($row, 6, $item->source, '#,##0');
                        }
                    }
                });
        $textFile->output();

//        for ($index = 0; $index < 1000000; $index++) {
//            $textFile->insertText($index + 1, 0, 'viest' . $index);
//            $textFile->insertText($index + 1, 1, 10000, '#,##0');
//        }
//        $textFile->output();
//
//        for ($index = 1000000; $index < 1000020; $index++) {
//            $textFile->insertText($index + 1, 0, 'viest' . $index);
//            $textFile->insertText($index + 1, 1, 10000, '#,##0');
//            $textFile->output();
//        }


        exit;
//
        //单元格插入链接
//        函数原型
//        insertUrl(int $row, int $column, string $url[, resource $format])
//        int $row
//        单元格所在行
//
//        int $column
//        单元格所在列
//
//        string $url
//        链接地址
//
//        resource $format
//        链接样式
//
//        实例
//        $excel = new \Vtiful\Kernel\Excel($config);
//
//        $urlFile = $excel->fileName("free.xlsx")
//            ->header(['url']);
//
//        $fileHandle = $fileObject->getHandle();
//
//        $format    = new \Vtiful\Kernel\Format($fileHandle);
//        $urlStyle = $format->bold()
//            ->underline(Format::UNDERLINE_SINGLE)
//            ->toResource();
//
//        $urlFile->insertUrl(1, 0, 'https://github.com', $urlStyle);
//
//        $textFile->output();
//
//
//        单元格插入公式
//        函数原型
//        insertFormula(int $row, int $column, string $formula)
//        int $row
//        单元格所在行
//
//        int $column
//        单元格所在列
//
//        string $formula
//        公式
//
//        实例
//        $excel = new \Vtiful\Kernel\Excel($config);
//
//        $freeFile = $excel->fileName("free.xlsx")
//            ->header(['name', 'money']);
//
//        for($index = 1; $index < 10; $index++) {
//            $textFile->insertText($index, 0, 'viest');
//            $textFile->insertText($index, 1, 10);
//        }
//
//        $textFile->insertText(12, 0, "Total");
//        $textFile->insertFormula(12, 1, '=SUM(B2:B11)');
//
//        $freeFile->output();
//
//        //单元格插入本地图片
//        函数原型
//        insertImage(int $row, int $column, string $localImagePath[, double $widthScale, double $heightScale])
//        int $row
//        单元格所在行
//
//        int $column
//        单元格所在列
//
//        string $localImagePath
//        图片路径
//
//        double $widthScale
//        对图像X轴进行缩放处理； 默认为1，保持图像原始宽度；值为0.5时，图像宽度为原图的1/2；
//
//        double $heightScale
//        对图像轴进行缩放处理； 默认为1，保持图像原始高度；值为0.5时，图像高度为原图的1/2；
//
//        实例
//        $excel = new \Vtiful\Kernel\Excel($config);
//        $freeFile = $excel->fileName("insertImage.xlsx");
//        $freeFile->insertImage(5, 0, storage_path('logs/loginbg.b9907988.png'));
//
//        $freeFile->output();
    }

    /**
     * https://gitee.com/viest/php-ext-xlswriter#PECL
     * https://imagemagick.org/script/download.php
     * @param Request $request
     */
    public function imagick(Request $request) {
//        $im = new \imagick(storage_path('logs/loginbg.b9907988.png'));
//        // resize by 200 width and keep the ratio
//        $im->thumbnailImage(200, 0);
//        // write to disk
//        $im->writeImage(storage_path('logs/a_thumbnail.png'));

        $images = new \Imagick(glob(storage_path('logs/*.png')));
        foreach ($images as $image) {
            // Providing 0 forces thumbnailImage to maintain aspect ratio
            $image->thumbnailImage(1024, 0);
        }
        $images->writeImages(storage_path('logs/thumbnail.png'), true);
    }

    public function user(Request $request) {

        $accountData = $request->input('account', ''); //会员账号
        if (!empty($accountData)) {
            $accounts = explode(',', $accountData);
        } else {
            $accounts = [
                'zianiilyes253@outlook.com',
                'fasdfsdf@qq.com',
                '2695651028@qq.com',
                '123456789@qq.com',
                '913419345@qq.com',
                'fdfdf@qq.com',
                '610625747@qq.com',
                '571809491@qq.com',
                '958676036@qq.com',
                '11111111@qq.com',
                '958@qq.com',
                '95867603@qq.com',
                '458521@qq.com',
                '34212123@qq.com',
                '512572682@qq.com',
                '821550938@qq.com',
                '947689502002@qq.com',
                '947689502001@qq.com',
                '123456799@qq.com',
                '569810025@qq.com',
                '804210845@qq.com',
                '704119717@qq.com',
                '212345@qq.com',
                '12345678@qq.com',
                '12345@qq.com',
                '1317145531@qq.com',
                '1111111111@qq.com',
                '349821180@qq.com',
                '1010305442@qq.com',
                '1233456@qq.com',
                '928966506@qq.com',
                '7650249@qq.com',
                '179553190@qq.com',
                '3043295839@qq.com',
                '359157324@qq.com',
                '605677197@qq.com',
                '1783383507@qq.com',
                '604140463@qq.com',
                '3001317754@qq.com',
                '1535051507@qq.com',
                '490379897@qq.com',
                '2551922618@qq.com',
                'adsad@qq.com',
                '1234456@qq.com',
                '444444@qq.com',
                '68978794@qq.com',
                '333333@qq.com',
                '2752401921@qq.com',
                '2222222@qq.com',
                '12151212@qq.com',
                '1234958@qq.com',
                '123345688@qq.com',
                '179559190@qq.com',
                '2695651080@qq.com',
                '462394577@qq.com',
                '1723796780@qq.com',
                '529030557@qq.com',
                '754871847@qq.com',
                '632081601@qq.com',
                '2692311966@qq.com',
                'sky2017@qq.com',
                'sdasdw@qq.com',
                'sdasd@qq.com',
                '531555237@qq.com',
                '8175430@qq.com',
                '270907735@qq.com',
                'harry_su@patazon.net',
                'harry_huang@patazon.net',
                'terrydeng@patazon.net',
                'yiyi@patazon.net',
                'connor@patazon.net',
                'day@163.com',
                '3asdfa6@163.com',
                'heer@163.com',
                'fucn@163.com',
                'what@163.com',
                'five@163.com',
                'yu@163.com',
                'three@163.com',
                'two@163.com',
                'one@163.com',
                '18773218548@163.com',
                'lscewis95@163.com',
                'bebetterdaisy@163.com',
                'ckj111123@yeah.net',
                'ckj111001@yeah.net',
                'ckj1111111@yeah.net',
                '2222@yeah.net',
                '1456@yeah.net'
            ];
        }

        $accounts = implode("','", $accounts);

        //获取账号
        $sql = "select
c.customer_id,c.account,c.store_id,c1.customer_id as invite_customer_id,c1.account as invite_account,c1.store_id as invite_store_id
from crm_customer c
left join crm_invite_historys ih on c.customer_id=ih.customer_id
left join crm_customer c1 on c1.customer_id=ih.invite_customer_id
where c.account in ('" . $accounts . "')";

        $data = DB::select($sql);
        $_data = [];
        $_storeData = [];
        foreach ($data as $key => $item) {
            if ($item->customer_id) {
                $_data[$item->customer_id] = $item->account;
                $_storeData[$item->store_id][$item->customer_id] = $item->account;
            }

            if ($item->invite_customer_id) {
                $_data[$item->invite_customer_id] = $item->invite_account;
                $_storeData[$item->invite_store_id][$item->invite_customer_id] = $item->invite_account;
            }
        }

        if ($_data) {
            $customerIds = array_keys($_data);
            $accounts = array_values($_data);

            \App\Models\CustomerAddress::whereIn('customer_id', $customerIds)->delete();
            \App\Models\CustomerInfo::whereIn('customer_id', $customerIds)->delete();
            \App\Models\CustomerLog::whereIn('customer_id', $customerIds)->delete();


            \App\Models\CustomerSync::whereIn('account', $accounts)->withTrashed()->forceDelete();

            foreach ($_storeData as $storeId => $item) {
                $_customerIds = array_keys($item);
                $_accounts = array_values($item);

                \App\Services\OrderService::getModel($storeId, '')->whereIn('customer_id', $customerIds)->withTrashed()->forceDelete();

                \App\Services\EmailService::getModel($storeId, '')->whereIn('to_email', $_accounts)->where('store_id', $storeId)->delete(); //邮件流水

                \App\Services\BaseService::createModel($storeId, 'ActivityCustomer')->whereIn('customer_id', $_customerIds)->withTrashed()->forceDelete(); //活动用户流水
                \App\Services\BaseService::createModel($storeId, 'CreditLog')->whereIn('customer_id', $_customerIds)->withTrashed()->forceDelete();
                ; //积分流水
                \App\Services\BaseService::createModel($storeId, 'ExpLog')->whereIn('customer_id', $_customerIds)->withTrashed()->forceDelete();
                ; //经验流水

                switch ($storeId) {
                    case 1:
                        \App\Services\BaseService::createModel($storeId, 'RankDay')->whereIn('customer_id', $_customerIds)->withTrashed()->forceDelete(); //mpow日榜

                        break;

                    case 2:
                        \App\Services\BaseService::createModel($storeId, 'VoteLog')->whereIn('account', $_accounts)->withTrashed()->forceDelete(); //投票流水

                        break;

                    default:
                        break;
                }

                \App\Services\BaseService::createModel($storeId, 'Rank')->whereIn('customer_id', $_customerIds)->withTrashed()->forceDelete(); //排行榜
                \App\Services\SubcribeService::getModel($storeId)->whereIn('email', $_accounts)->delete(); //订阅
            }

            \App\Models\Interest::whereIn('customer_id', $customerIds)->delete(); //兴趣
            \App\Models\InviteCode::whereIn('customer_id', $customerIds)->delete(); //邀请码
            \App\Models\Share::whereIn('customer_id', $customerIds)->delete(); //分享

            \App\Models\InviteHistory::whereIn('customer_id', $customerIds)->delete(); //邀请流水
            \App\Models\Customer::whereIn('customer_id', $customerIds)->delete();
        }

        return Response::json($data);
    }

    /**
     * 拉取订单
     * @param Request $request
     * @return type
     */
    public function order(Request $request) {

        $orderData = $request->input('order', ''); //会员账号
        if (empty($orderData)) {
            return Response::json();
        }

        return Response::json();
    }

    /**
     * 删除订单
     * @param Request $request
     * @return type
     */
    public function delOrder(Request $request) {

        $orderno = $request->input('orderno', ''); //会员账号
        $ordernos = array_unique(array_filter(explode(',', $orderno)));
        $ordernos = implode("','", $ordernos);
        $store_id = $request->input('store_id', 0); //会员账号
        $type = $request->input('type', ''); //会员账号
        //获取账号
        $sql = "SELECT id,customer_id,store_id FROM crm_customer_order WHERE store_id=$store_id AND type='$type' AND orderno in ('" . $ordernos . "')";
        $data = DB::select($sql);
        foreach ($data as $key => $item) {

            $storeId = $item->store_id;
            $id = $item->id; //订单id
            $customerId = $item->customer_id;

            //处理积分
            $creditLog = \App\Services\BaseService::createModel($storeId, 'CreditLog');
            $where = [
                'customer_id' => $customerId,
                'ext_id' => $id,
                'ext_type' => 'customer_order',
                'action' => 'order_bind',
            ];
            $creditLogData = $creditLog->buildWhere($where)->withTrashed()->select('id', 'add_type', 'value')->get(); //积分流水
            foreach ($creditLogData as $key => $_item) {
                //更新积分
                $updata = [
                    'credit' => DB::raw('credit-' . $_item->value),
                    'total_credit' => DB::raw('total_credit-' . $_item->value),
                ];
                $_where = [
                    'customer_id' => $customerId,
                ];
                \App\Models\CustomerInfo::where($_where)->update($updata);

                $creditLog->where('id', $_item->id)->withTrashed()->forceDelete(); //删除积分流水
            }

            //处理经验
            $expLog = \App\Services\BaseService::createModel($storeId, 'ExpLog');
            $expLogData = $expLog->buildWhere($where)->withTrashed()->select('id', 'add_type', 'value')->get(); //经验流水
            foreach ($expLogData as $key => $_item) {
                //更新经验
                $updata = [
                    'exp' => DB::raw('exp-' . $_item->value),
                ];
                $_where = [
                    'customer_id' => $customerId,
                ];
                \App\Models\CustomerInfo::where($_where)->update($updata);

                $expLog->where('id', $_item->id)->withTrashed()->forceDelete(); //删除经验流水
            }

            \App\Services\OrderService::getModel($storeId, '')->where('id', $id)->withTrashed()->forceDelete(); //删除订单
        }

        return Response::json($data);
    }

    public function collect(Request $request) {
        $coupons = collect([
            'a' => ['product' => 'Desk', 'price' => 200],
            'b' => ['product' => 'Chair', 'price' => 100],
            'c' => ['product' => 'Bookcase', 'price' => 150],
            'd' => ['product' => 'Door', 'price' => 100],
        ]);

        dd($coupons->where('price', 100)->pluck('product')->all(), $coupons->pluck('price')->all());

        foreach ($collection as $type => $coupon) {
            $data['type_' . $type] = data_get($coupon, 'product', '');
            $data[$type] = data_get($coupon, 'product', '');
            $data[$type . '_start_date'] = substr(data_get($coupon, 'price', ''), 0, 10);
            $data[$type . '_end_date'] = substr(data_get($coupon, 'price', ''), 0, 10);
        }
        dd($data);

        $filtered = $collection->where('price', 100)->pluck('product');
        dd($filtered->all(), data_get($filtered, '*', []), data_get($filtered->all(), '*.product', []));
    }

    public function storage(Request $request) {
        //dd(storage_path('logs/thumbnail-0.png'));s3://vip-test-bucket/thumbnail-0.png
        //dd($url = \Illuminate\Support\Facades\Storage::disk('s3')->url('thumbnail-01.png'));

        dd(\Illuminate\Support\Facades\Storage::disk('s3')->url(\Illuminate\Support\Facades\Storage::disk('s3')->putFile('photos', new \Illuminate\Http\File(storage_path('logs/thumbnail-1.png')), 'public')));
        //dd(\Illuminate\Support\Facades\Storage::disk('s3')->put('thumbnail-01.png', file_get_contents(storage_path('logs/thumbnail-0.png'))));
    }

    public function csv(Request $request) {

        $storeId = 5;
        $country = '';
        $parameters = [];
        $fileName = 'survey.csv';
        $realPath = storage_path('logs/' . $fileName);
        $surveyId = 1;

        $file = fopen($realPath, "r");
        $row = 0;
        while (!feof($file)) {
            $item = fgetcsv($file);
            if ($item && $row > 0) {
                $surveyItems = [
                    'survey_id' => $surveyId,
                    'item_type' => data_get($item, 1, 1),
                    'is_required' => data_get($item, 2, 0),
                    'validation_rules' => data_get($item, 3, ''),
                    'sort' => $row,
                ];
                $name = trim(data_get($item, 0, ''));
                $where = [
                    'name' => $name,
                ];
                $surveyItemsData = \App\Services\Survey\SurveyItemService::getModel($storeId, $country, $parameters)->updateOrCreate($where, $surveyItems);
                $itemId = data_get($surveyItemsData, 'id', 0);

                $i = 4;
                $SurveyItemOptionName = data_get($item, $i, '');
                $sort = 1;
                if ($itemId) {

                    while ($SurveyItemOptionName) {

                        $SurveyItemOptionData = [
                            'survey_id' => $surveyId,
                            'sort' => $sort,
                        ];

                        $where = [
                            'item_id' => $itemId,
                            'name' => $SurveyItemOptionName,
                        ];
                        \App\Services\Survey\SurveyItemOptionService::getModel($storeId, $country, $parameters)->updateOrCreate($where, $SurveyItemOptionData);

                        $sort++;
                        $i++;
                        $SurveyItemOptionName = data_get($item, $i, '');
                    }
                }
            }
            $row++;
        }
        fclose($file);

        return Response::json(['msg' => 'csv======' . (\App\Services\Survey\SurveyService::clearCache($storeId, $surveyId))]);
    }

    public function csvFile(Request $request) {

        $storeId = 5;

        $fileName = 'survey_result_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.csv';
        $realPath = storage_path('logs/' . $fileName);
        $surveyId = 1;

        $file = fopen($realPath, 'a'); //w+

        $colHeaders = [
            'name' => iconv('UTF-8', 'GB2312//IGNORE', '名字'),
            'account' => iconv('UTF-8', 'GB2312//IGNORE', '邮箱'),
            'ip' => iconv('UTF-8', 'GB2312//IGNORE', 'ip'),
            'country' => iconv('UTF-8', 'GB2312//IGNORE', '国家'),
            'created_at' => iconv('UTF-8', 'GB2312//IGNORE', '提交时间'),
        ];

        $data = \App\Services\Survey\SurveyResultService::getData($storeId = 5, $actId = 2);
        $csvData = [];
        foreach ($data as $key => $value) {
            $item = [
                'name' => iconv('UTF-8', 'GB2312//IGNORE', data_get($value, 'name', '')),
                'account' => iconv('UTF-8', 'GB2312//IGNORE', data_get($value, 'account', '')),
                'ip' => iconv('UTF-8', 'GB2312//IGNORE', data_get($value, 'ip', '')),
                'country' => iconv('UTF-8', 'GB2312//IGNORE', data_get($value, 'country', '')),
                'created_at' => iconv('UTF-8', 'GB2312//IGNORE', data_get($value, 'created_at', '')),
            ];

            foreach (data_get($value, 'survey_result_items', []) as $_value) {

                $itemId = data_get($_value, 'item_id', '');
                if ($key == 0) {
                    $colHeaders[$itemId] = iconv('UTF-8', 'GB2312//IGNORE', data_get($_value, 'item_name', ''));
                }

                $item[$itemId] = iconv('UTF-8', 'GB2312//IGNORE', implode(',', data_get($_value, 'item_option_names', [])));
            }

            $csvData[] = $item;
        }

        fputcsv($file, $colHeaders);
        foreach ($csvData as $item) {
            $_item = [];
            foreach ($colHeaders as $key => $value) {
                $_item[] = data_get($item, $key, '');
            }
            fputcsv($file, $_item);
        }

        ob_flush();
        fclose($file);

        return Response::json(['msg' => 'csv======']);
    }

    /**
     * 本地化：https://learnku.com/docs/laravel/7.x/localization/7471
     * @param Request $request
     */
    public function locale(Request $request) {
        app('translator')->setLocale('us');
        dd(__('responsecode.100'), __('I love programming55.'));
        $code = 10000;
        $output = Lang::get('responsecode.' . $code . '.1'); //Lang::get('responsecode.10000.1',Lang::get('responsecode.10000.default',Lang::get('responsecode.10000','')));
        dd($output);
    }

    /**
     * 表单验证 https://learnku.com/docs/laravel/7.x/validation/7467
     * @param Request $request
     */
    public function validator(Request $request) {

        $storeKey = implode('_', ['10002', '1', 'a']);
        $defaultKey = implode('_', ['10002', 'default', 'a']);
        $validatorData = [
//            Constant::TO_EMAIL => '898',
//            'account' => '',
//            $storeKey => '',
//            $defaultKey => '',
//            'test_1' => '8989898989',
//            'test_2' => '8989898989',
            '10000_1' => 'abc',
            '10000_2' => 'abc',
            '10001_1' => '',
        ];
        $rules = [
//            Constant::TO_EMAIL => ['required', 'email'], //'required|email'
//            'account' => ['required', 'numeric', 'between:1,5'],
//            $storeKey => ['required'],
//            $defaultKey => ['required'],
            //'test_1' => ['required', 'between:1,5'], //numeric|
//            'test_1' => ['required', 'active_url'], //numeric|
//            'test_2' => ['required', 'active_url'], //numeric|

            "10000_1" => ['required', 'api_code_msg'],
            "10000_2" => ['required', 'api_code_msg'],
            "10001_10000" => ['api_code_msg'],
        ];


        $messages = [
                //'required' => '10000_288888====>required=====setCustomMessages',
        ];
        $validator = Validator::make($validatorData, $rules); //$messages
        if ($messages) {
            $validator->setCustomMessages($messages); //和直接在  Validator::make($validatorData, $rules, $messages);效果是一样的  setCustomMessages更加灵活
        }
        //dd($validator);

        $errors = $validator->errors();

        //            //查看特定字段的第一个错误信息
//            //$errors->first('email');
//            //查看特定字段的所有错误消息
//            foreach ($errors->get('email') as $message) {
//                //
//            }
//
//            //查看所有字段的所有错误消息
//            foreach ($errors->all() as $message) {
//                //
//                var_dump($message);
//            }
//
//            //判断特定字段是否含有错误消息
//            if ($errors->has('email')) {
//                //
//            }
        foreach ($rules as $key => $value) {

            dump($key, $errors->get($key));

//            if ($errors->has($key)) {
//                dump($key, $errors->first($key));
//            }
        }

        dd($errors);
    }

    public function cookie(Request $request) {
        //https://learnku.com/articles/10752/cookie-use-of-laravel
        //获取 Cookie
        $value = $request->cookie('key');
        $value = request()->cookie('key');
        $value = \Illuminate\Support\Facades\Cookie::get('key');

        //设置 Cookie
        $value = response('Hello Cookie')->cookie(\Illuminate\Support\Facades\Cookie::make('key', 'value', 60)); //cookie 方法其实就是调用了 withCookie 方法
        $value = response('Hello Cookie')->withCookie(\Illuminate\Support\Facades\Cookie::make('key', 'value', 60)); //实际开发使用此方法即可
        //删除 Cookie
        $cookie = Cookie::forget('key');
        response('xxx')->withCookie($cookie);

        dump(\Illuminate\Support\Facades\Cookie::getQueuedCookies());
        $r = \Illuminate\Support\Facades\Cookie::get('visitor_id');
        return response(\Illuminate\Support\Facades\Cookie::get('visitor_id'))->withCookie(new \Symfony\Component\HttpFoundation\Cookie('ssss', 'sid9999', time() + 3600))->withCookie(new \Symfony\Component\HttpFoundation\Cookie('uid', 'uid9999', time() + 3600));

        return response($router->app->version())->withCookie(\Illuminate\Support\Facades\Cookie::make('cookie_key', 'cookie_value'));
    }

    /**
     * 请求
     * @param Request $request
     */
    public function request(Request $request) {
        $name = $request->input('name');
        $all = $request->all();
        $sessionId = $request->cookie('sessionId');
        $photo = $request->file('photo');
        // 调用getContent()来获取原始的POST body，而不能用file_get_contents('php://input')
        $rawContent = $request->getContent();
        //...
    }

    public function json() {
        return response()->json(['time' => time()])->header('header1', 'value1')->withCookie('c1', 'v1');
    }

    /**
     * PHP 获取调用者的方法和行数（查看堆栈调用）
     * @return array
     */
    public function debug() {
        return debug_backtrace();;
    }

    /**
     * http client https://docs.guzzlephp.org/en/stable/
     */
    public function httpClient()
    {
        $client = make(Client::class, [
            'config' => [
                'base_uri' => 'https://www.baidu.com',
                //'base_uri' => 'https://brandwtest.patozon.net',
                //'base_uri' => 'http://192.168.152.128:81',
                //'base_uri' => 'https://brand.patozon.net/',
                //'base_uri' => 'https://brand-api.patozon.net',
                'base_uri' => 'http://httpbin.org',
                // Use a shared client cookie jar
                RequestOptions::COOKIES=>true,
            ],
        ]);

        $requests = function ($total) use ($client) {
            $uri = '/';
            for ($i = 0; $i < $total; $i++) {
                yield function () use ($client, $uri) {
                    return $client->getAsync($uri);
                };
            }
        };

        //###Or using a closure that will return a promise once the pool calls the closure.
        $pool = new \GuzzleHttp\Pool($client, $requests(2), [
            'concurrency' => 5,
            'fulfilled' => function (\GuzzleHttp\Psr7\Response $response, $index) {
                // this is delivered each successful response
                var_dump($index, $response->getStatusCode(), $response->getBody()->getContents());
            },
            'rejected' => function ($reason, $index) {//\GuzzleHttp\Exception\RequestException
                // this is delivered each failed request
                //var_dump($reason->getRequest()->getUri(), $reason->getMessage(), $reason->getRequest()->getMethod());
                //var_dump(get_class($reason));
                throw $reason;

            },
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();
        // Force the pool of requests to complete.
        $responses = $promise->wait();
        return $responses;


        $response = $client->request(
            'GET',
            '/get',
            //[\GuzzleHttp\RequestOptions::DEBUG => true]
            [\GuzzleHttp\RequestOptions::DEBUG => \GuzzleHttp\Psr7\Utils::tryFopen(storage_path('/logs/guzzle_http_debug.log'), 'a+')]
        );//
        return [];

//        $method = 'post';
//        $uri = '/api/admin/store/getStore';
//        //$uri = '/';
//
//        $refer = 'https://api-localhost.com/'; //https://www.victsing.com/pages/vip-benefit
//        $iv = '1234567891011121';
//
//        $headers = [
//            //'Content-Type'=>'application/json',
//            //'Content-Type'=>'application/x-www-form-urlencoded',
//            //'Content-Type'=>'multipart/form-data',
//
//            //'Referer' => $refer,
//            'Version' => CURL_HTTP_VERSION_1_1,
//            'IvParameterSpec' => $iv,
//            'API_VERSION' => 27, //
//            //'Authorization: Bearer fa83e4f46be69a1417fd3de4bf6fa2a1',
//            //'Authorization: AUdCZgFK',
//            'Authorization' => 'Basic Zm9vOmJhcg==',
//            'Content-Type' => 'application/json',
//            'Expect' => '',
//            'X-Requested-With' => 'XMLHttpRequest', //告诉服务器，当前请求是 ajax 请求
//            //'X-PJAX: '.false,//告诉服务器在收到这样的请求的时候, 要返回 json 数据
//            //'X-PJAX: '.true,//告诉服务器在收到这样的请求的时候, 只需要渲染部分页面返回就可以了
//            //'Accept: +json',//告诉服务器，要返回 json 数据
//            //'Accept: /json', //告诉服务器，要返回 json 数据
//            'X-Shopify-Hmac-Sha256' => 'aVB8fEJErbweBCKDsc5MI2kzR8JrfEgUM25Be1NWSQs=',
//            'X-Token' => '5d3addb5ddaec3a58d3809010adbf427_1564474859',
//        ];
//
//        $ip = '151.81.2.93';
//        $remotesKeys = [
//            'HTTP_X_FORWARDED_FOR',
//            'HTTP_CLIENT_IP',
//            'HTTP_X_FORWARDED',
//            'HTTP_FORWARDED_FOR',
//            'HTTP_FORWARDED',
//            'REMOTE_ADDR',
//            'HTTP_X_CLUSTER_CLIENT_IP',
//            'X_FORWARDED_FOR',
//            'CLIENT_IP',
//            'X_FORWARDED',
//            'FORWARDED_FOR',
//            'FORWARDED',
//            'ADDR',
//            'X_CLUSTER_CLIENT_IP',
//            'X-FORWARDED-FOR',
//            'CLIENT-IP',
//            'X-FORWARDED',
//            'FORWARDED-FOR',
//            'FORWARDED',
//            'REMOTE-ADDR',
//            'X-CLUSTER-CLIENT-IP',
//        ];
//        foreach ($remotesKeys as $remotesKey) {
//            $headers[$remotesKey] = $ip;
//        }
//
//        $options = [
//            RequestOptions::HEADERS => $headers,
//            //RequestOptions::VERSION => '2.0',
//            //RequestOptions::BODY=>'{"store_id":"6","token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYxNDgzOTUyMywiaWQiOiI0ODEifQ.41qlnIizkGzjII0AWwezZ5T3BAfHsJYvf-mzb6cVMfE","operator":"Jmiy_cen(岑永坚)","orderno":"","account":"","country":[],"asin":"","sku":"","type":[],"start_time":"","star":[],"end_time":"","audit_status":[],"page":1,"page_size":10,"is_psc":true}',
////            RequestOptions::FORM_PARAMS=>[//表单提交
////                'a'=>8888,
////            ],
//
////            RequestOptions::MULTIPART => [//表单上传文件
////                [
////                    'name'     => 'file',
////                    'contents' => fopen(BASE_PATH . '/storage/thumbnail-1.png', 'r'),
////                    'filename' => 'thumbnail-1.png',
////                ],
////                [
////                    'name'      => 'tags',
////                    'contents'  => json_encode([
////                        "external" => [
////                            "tenantId" => 23,
////                            "author" => 34,
////                            "description" => "these are additional tags"
////                        ]
////                    ])
////                ],
////            ],
//
//            RequestOptions::JSON=>[//json
////                [
////                    'name'     => 'file',
////                    'contents' => fopen(BASE_PATH . '/storage/thumbnail-1.png', 'r'),
////                    'filename' => 'thumbnail-1.png',
////                ],
//                [
//                    'name'      => 'tags',
//                    'contents'  => [
//                        "external" => [
//                            "tenantId" => 23,
//                            "author" => 34,
//                            "description" => "these are additional tags"
//                        ]
//                    ],
//                ],
//            ],
//        ];
//
//        $response = $client->request($method, $uri, $options);
//
//        /************** https://docs.guzzlephp.org/en/stable/quickstart.html#making-a-request ***************/
//        //Sending Requests
//        $response = $client->get('http://httpbin.org/get');
//        $response = $client->delete('http://httpbin.org/delete');
//        $response = $client->head('http://httpbin.org/get');
//        $response = $client->options('http://httpbin.org/get');
//        $response = $client->patch('http://httpbin.org/patch');
//        $response = $client->post('http://httpbin.org/post');
//        $response = $client->put('http://httpbin.org/put');
//
//        $request = new \GuzzleHttp\Psr7\Request('PUT', 'http://httpbin.org/put');
//        $response = $client->send($request, ['timeout' => 2]);
//
//        /*****************Async Requests start ****************/
//        $promise = $client->getAsync('http://httpbin.org/get');
//        $promise = $client->deleteAsync('http://httpbin.org/delete');
//        $promise = $client->headAsync('http://httpbin.org/get');
//        $promise = $client->optionsAsync('http://httpbin.org/get');
//        $promise = $client->patchAsync('http://httpbin.org/patch');
//        $promise = $client->postAsync('http://httpbin.org/post');
//        $promise = $client->putAsync('http://httpbin.org/put');
//        //###You can also use the sendAsync() and requestAsync() methods of a client:
//        // Create a PSR-7 request object to send
//        $headers = ['X-Foo' => 'Bar'];
//        $body = 'Hello!';
//        $request = new \GuzzleHttp\Psr7\Request('HEAD', 'http://httpbin.org/head', $headers, $body);
//        $promise = $client->sendAsync($request);
//
//        // Or, if you don't need to pass in a request instance:
//        $promise = $client->requestAsync('GET', 'http://httpbin.org/get');
//
//        $promise->then(
//            function (\Psr\Http\Message\ResponseInterface $response) use ($i) {
//                dump($i, $response->getStatusCode(), $response->getBody()->getContents());
//            },
//            function (\GuzzleHttp\Exception\RequestException $e) use ($i) {
//                dump($i, $e->getMessage(), $e->getRequest()->getMethod());
//            }
//        );
//        $promise->wait();
//        $response = $promise->wait();
//        dump($response->getStatusCode(), $response->getBody()->getContents());
//        /*****************Async Requests end ****************/
//
        /*****************Concurrent requests(并发请求) start ****************/
        //You can send multiple requests concurrently using promises and asynchronous requests.
        // Initiate each request but do not block
        $promises = [
            'image' => $client->getAsync('/image'),
            'png'   => $client->getAsync('/image/png'),
            'jpeg'  => $client->getAsync('/image/jpeg'),
            'webp'  => $client->getAsync('/image/webp')
        ];

        // Wait for the requests to complete; throws a ConnectException
        // if any of the requests fail
//        $responses = \GuzzleHttp\Promise\Utils::unwrap($promises);
//
//        // You can access each response using the key of the promise
//        dump($responses['image']->getHeader('Content-Length')[0]);
//        dump($responses['png']->getHeader('Content-Length')[0]);

        // Wait for the requests to complete, even if some of them fail
        $responses = \GuzzleHttp\Promise\Utils::settle($promises)->wait();

        // Values returned above are wrapped in an array with 2 keys: "state" (either fulfilled or rejected) and "value" (contains the response)
        dump($responses['image']['state'],$responses['image']); // returns "fulfilled"
//        dump($responses['image']['value']->getHeader('Content-Length')[0]);
//        dump($responses['png']['value']->getHeader('Content-Length')[0]);

        /***************** Concurrent requests(并发请求) You can send multiple requests concurrently using promises and asynchronous requests. Start ****************/
//        $requests = function ($total) {
//            //$uri = 'http://127.0.0.1:8126/guzzle-server/perf';
//            $uri = '/';
//            for ($i = 0; $i < $total; $i++) {
//                yield new \GuzzleHttp\Psr7\Request('GET', $uri);
//            }
//        };
//
//        //###Or using a closure that will return a promise once the pool calls the closure.
//        $requests = function ($total) use ($client) {
//            $uri = '/';
//            for ($i = 0; $i < $total; $i++) {
//                yield function() use ($client, $uri) {
//                    return $client->getAsync($uri);
//                };
//            }
//        };
//
//        //###Or using a closure that will return a promise once the pool calls the closure.
//        $pool = new \GuzzleHttp\Pool($client, $requests(100), [
//            //'concurrency' => 5,
//            'fulfilled' => function (\GuzzleHttp\Psr7\Response $response, $index) {
//                // this is delivered each successful response
//                dump($index, $response->getStatusCode(),$response->getBody()->getContents());
//            },
//            'rejected' => function (\GuzzleHttp\Exception\RequestException $reason, $index) {
//                // this is delivered each failed request
//                dump($reason->getRequest()->getUri(),$reason->getMessage(),$reason->getRequest()->getMethod());
//            },
//        ]);
//
//        // Initiate the transfers and create a promise
//        $promise = $pool->promise();
//        // Force the pool of requests to complete.
//        $promise->wait();

        return [];

        /***************** Concurrent requests(并发请求) You can send multiple requests concurrently using promises and asynchronous requests. end ****************/

        //Query String Parameters
//        $response = $client->request('GET', 'http://httpbin.org?foo=bar');
//        $response = $client->request('GET', 'http://httpbin.org', [
//            'query' => ['foo' => 'bar']
//        ]);
//        $response = $client->request('GET', 'http://httpbin.org', ['query' => 'foo=bar']);

        return [
            $response->getProtocolVersion(),//协议版本
            $response->getStatusCode(),//响应状态码
            $response->getReasonPhrase(),//响应状态码描述
            $response->getHeaders(),//响应头
            $response->getBody()->getContents(),//响应body
        ];
    }

    public function rpc() {

        $dd = microtime(true);


//        ProtocolManager::register($protocol = 'jsonrpc-http', [
//            ProtocolManager::TRANSPORTER => new GuzzleHttpTransporter(),
//            ProtocolManager::PACKER => new JsonEofPacker(),
//            ProtocolManager::PATH_GENERATOR => new PathGenerator(),
//            ProtocolManager::DATA_FORMATTER => new DataFormatter(),
//        ]);
//        // 绑定 CalculatorService 与 jsonrpc 协议，同时设定静态的节点信息
//        ServiceManager::register($service = 'CalculatorService', $protocol, [
//            ServiceManager::NODES => [
//                [$host = '192.168.152.128', $port = 9504],
//            ],
//        ]);
//
//        $clientFactory = new ClientFactory();
//        $client = $clientFactory->create($service, $protocol);
//
//        // 调用远程方法 `add` 并带上参数 `1` 和 `2`
//        // $result 即为远程方法的返回值
//        $result = $client->add(1, 2);
//
//        dump($result);


        //使用 Consul 服务
        $config = [];

        ProtocolManager::register($protocol = 'jsonrpc-http', [
            ProtocolManager::TRANSPORTER => new GuzzleHttpTransporter(),//使用http协议
            ProtocolManager::PACKER => new JsonEofPacker(),
            ProtocolManager::PATH_GENERATOR => new PathGenerator(),
            ProtocolManager::DATA_FORMATTER => new DataFormatter(),
            ProtocolManager::NODE_SELECTOR => new NodeSelector('192.168.152.128', 8500, $config),//使用 Consul 服务
        ]);

        ProtocolManager::register($protocol_rpc = 'jsonrpc', [
            ProtocolManager::TRANSPORTER => new StreamSocketTransporter(),//使用tcp协议
            ProtocolManager::PACKER => new JsonEofPacker(),
            ProtocolManager::PATH_GENERATOR => new PathGenerator(),
            ProtocolManager::DATA_FORMATTER => new DataFormatter(),
            ProtocolManager::NODE_SELECTOR => new NodeSelector('192.168.152.128', 8500, $config),//使用 Consul 服务
        ]);

        // 绑定 CalculatorService 与 jsonrpc 协议，同时设定静态的节点信息
        ServiceManager::register($service = 'CalculatorService', $protocol, [
//            ServiceManager::NODES => [
//                [$host = '192.168.152.128', $port = 9503],
//            ],
        ]);

        $clientFactory = new ClientFactory();
        $client = $clientFactory->create($service, $protocol);

        // 调用远程方法 `add` 并带上参数 `1` 和 `2`
        // $result 即为远程方法的返回值
        $result = $client->add(1, 2);

        dump($result);

        return [(number_format(microtime(true) - $dd, 8, '.', '') * 1000) . ' ms'];
    }

    public function clickHouse() {
        $config = [
//            'host' => '192.168.5.134',
//            'port' => 8123,
//            'username' => 'default',
//            'password' => 'fxnFtiZT',

            'port' => '8123',
            'database' => 'ptx_db',
            'username' => 'ptx',
            'password' => 'ptx123',
            'host' => '172.16.6.207',

//            'port' => '53456',
//            'database' => 'ptx_db',
//            'username' => 'ptx',
//            'password' => 'ptx123',
//            'host' => '14.21.71.212'
        ];

        $_nowTime = microtime(true);

        $db = new \ClickHouseDB\Client($config);
        $db->database('ptx_db');
        $db->setTimeout(60);       // 10 seconds
        $db->setConnectTimeOut(5); // 5 seconds
        //dump($db->showTables());

        $dd = $db->select("SELECT * FROM ptx_yw.ads_or_xc_order_item_report WHERE amazon_order_id='104-4444717-6843425' LIMIT 10")->rows();

        //$result = $db->insert($table, $data,$fields);//目标字段
        //$result = $db->write($sql);

        $xcTime = (number_format(microtime(true) - $_nowTime, 8, '.', '') * 1000) . ' ms';
        dd($xcTime, $dd);
    }

    public function elasticSearch() {

        // The connection class requires 'body' to be a file stream handle
        // Depending on what kind of request you do, you may need to set more values here
//        $handler = new \GuzzleHttp\Ring\Client\MockHandler([
//            'status' => 200,
//            'transfer_stats' => [
//                'total_time' => 100
//            ],
//            'body' => fopen(storage_path('logs/somefile.json'),'w+'),
//            'effective_url' => 'localhost'
//        ]);

        $client = \Elasticsearch\ClientBuilder::create()
            ->setHosts(['http://192.168.152.128:9200'])
            //->setHandler($handler)
            ->build();

        //$response = $client->info();

        //Index a document
        $params = [
            'index' => 'my_index',
            'id'    => 'my_id',
            'body'  => ['testField' => 'abc  id==>my_id']
        ];
        $response = $client->index($params);
        return $response;

        //Get a document
//        $params = [
//            'index' => 'my_index',
//            'id'    => 'my_id'
//        ];
//
//        /**
//         * Array
//        (
//        [_index] => my_index
//        [_type] => _doc
//        [_id] => my_id
//        [_version] => 1
//        [_seq_no] => 0
//        [_primary_term] => 1
//        [found] => 1
//        [_source] => Array
//        (
//        [testField] => abc
//        )
//
//        )
//         */
//        $response = $client->get($params);

        /**
         * If you want to retrieve the _source field directly, there is the getSource method:
        $params = [
        'index' => 'my_index',
        'id'    => 'my_id'
        ];

        $source = $client->getSource($params);
        print_r($source);
         *
        The response will be just the _source value:

        Array
        (
        [testField] => abc
        )
         */
//        $params = [
//            'index' => 'my_index',
//            'id'    => 'my_id'
//        ];
//        $response = $client->getSource($params);

        //Search for a document
//        $params = [
//            'index' => 'my_index',
//            'body'  => [
//                'query' => [
//                    'match' => [
//                        'testField' => 'abc 56'
//                    ]
//                ]
//            ]
//        ];
//        $response = $client->search($params);

        //Delete a document 删除 index=my_index 并且 id=my_id  记录
//        $params = [
//            'index' => 'my_index',
//            'id'    => 'my_id'
//        ];
//        $response = $client->delete($params);

        //Delete an index
//        $deleteParams = [
//            'index' => 'my_index'
//        ];
//        $response = $client->indices()->delete($deleteParams);

        //Create an index
        $params = [
            'index' => 'my_index11',
            'body'  => [
                'settings' => [
                    'number_of_shards' => 2,
                    'number_of_replicas' => 0
                ]
            ]
        ];
        $response = $client->indices()->create($params);

        return $response;
    }




}
