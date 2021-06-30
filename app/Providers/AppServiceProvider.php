<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Validator;

class AppServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        //创建单例
        $models = config('app.morphMap');
        foreach ($models as $key => $value) {
            $this->app->singleton($key, $value);
        }//

        $this->app->bind('Illuminate\Database\Eloquent\Relations\MorphTo', function() {
            return app('MorphTo', []);
        });
    }

    /**
     * 启动应用服务
     *
     * @return void
     */
    public function boot() {
        $models = config('app.morphMap', []);
        Relation::morphMap($models); //Set or get the morph map for polymorphic relations.
        DB::listen(function ($query) {
            // $query->sql
            // $query->bindings
            // $query->time
            //var_dump($query);
            //dump(['db_sql' => $query->sql, 'bindings' => $query->bindings, 'time' => $query->time]);
            if(env('DB_DEBUG', false)){
                $request = app('request');
                $dbSql = $request->input('db_debug',[]);

                $dbSql[] = ['db_sql' => $query->sql, 'bindings' => $query->bindings, 'time' => $query->time];
                $request->offsetSet('db_debug', $dbSql);
            }

        });
//        Customer::observe(UserObserver::class); //观察者 https://learnku.com/docs/laravel/5.8/eloquent/3931#observers  如果在一个模型上监听了多个事件，可以使用观察者来将这些监听器组织到一个单独的类中。观察者类的方法名映射到你希望监听的 Eloquent 事件。 这些方法都以模型作为其唯一参数

        /*         * ***********************自定义验证规则 https://learnku.com/docs/laravel/7.x/validation/7467 ******************************************************** */
        /**
         * 注册自定义的验证规则的另一种方法是使用 Validator facade 中的 extend 方法。让我们在 服务容器 中使用这个方法来注册自定义验证规则：
         * Validator::extend('api_code_msg', function ($attribute, $value, $parameters, $validator) {
          return false;
          });
         */
        /**
         * 除了使用闭包，你也可以传入类和方法到 extend 方法中：
         * Validator::extend('foo', 'FooValidator@validate');
         */
        /**
         * 当创建一个自定义验证规则时，你可能有时候需要为错误信息定义自定义占位符。可以通过创建自定义验证器然后调用 Validator 门面上的 replacer 方法。你可以在 服务容器 的 boot 方法中执行如下操作：
         * Validator::replacer('foo', function ($message, $attribute, $rule, $parameters) {
          //return str_replace(...);
          });
         */
        /**
         * 隐式扩展
         * 如果即使属性为空也要验证规则，则一定要暗示属性是必须的。要创建这样一个「隐式」扩展，可以使用 Validator::extendImplicit() 方法：
         */
        Validator::extendImplicit('api_code_msg', function ($attribute, $value, $parameters, $validator) {
            return false;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        $models = config('app.morphMap');
        return array_keys($models);
    }

}
