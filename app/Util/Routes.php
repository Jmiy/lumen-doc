<?php

namespace App\Util;

use Illuminate\Support\Arr;

class Routes {

    public static $webTheme = ['seasia', 'style1', 'style2', 'style3', 'style4', 'style5', 'style6', 'style7', 'style8', 'style9', 'style10']; //前后没有分离的模板

    /**
     * 获取路由uri
     * @param mix $routeAs 路由别名
     * @param mix $default 默认值
     * @return mix 路由uri
     */

    public static function getRouteUri($routeAs = null, $default = null) {
        $namedRoutes = app('router')->namedRoutes;
        return $routeAs !== null ? Arr::get($namedRoutes, $routeAs, $default) : $namedRoutes;
    }

    /**
     * 获取当前路由uri
     * @return mix 当前路由uri
     */
    public static function getCurrentRouteUri() {
        $currentRouteData = app('request')->route();
        return static::getRouteUri(Arr::get($currentRouteData, '1.as', ''), '');
    }

    /**
     * 跳转到产品详情页
     * @param int $id 产品id
     * @return type
     */
    public static function productDetail($id) {

        if (request()->expectsJson()) {
            return null;
        }

        $id = (int) $id ? $id : request()->route()->parameter('id', 0); //产品id
        $site = \App\Model\Site::getItemByDomain();

        $fileName = request()->getRequestUri();
        $routeParam = ['id' => $id, 'theme' => $site->theme];
        if ($site && !in_array($site->theme, static::$webTheme) && false === strpos($fileName, '/detail/')) {//如果 $site->theme 对应的模板是前后分离的，就根据站点模板跳转到对应的详情页url
            $routeName = 'product_detail'; //前后分离静态详情页路由名称
            //没有前后分离的商品详情页静态url地址
            $url = route($routeName, $routeParam); //如果是有定义参数的命名路由，可以把参数作为 route 函数的第二个参数传入，指定的参数将会自动插入到 URL 中对应的位置：

            return redirect($url); //302重定向到 $url
        }

        if ($site && in_array($site->theme, static::$webTheme) && false === strpos($fileName, '/product/')) {//如果 $site->theme 对应的模板没有前后分离，就根据站点模板跳转到对应的详情页url
            $routeName = 'html_product_detail'; //没有前后分离的静态详情页路由名称
            //没有前后分离的商品详情页静态url地址
            $url = route($routeName, $routeParam); //如果是有定义参数的命名路由，可以把参数作为 route 函数的第二个参数传入，指定的参数将会自动插入到 URL 中对应的位置：

            return redirect($url); //302重定向到 $url
        }

        if (false === strpos($fileName, '.html')) {//如果不是静态url，就跳转到静态url
            $routeName = 'product_detail'; //前后分离静态详情页路由名称
            if ($site && in_array($site->theme, static::$webTheme)) {//如果 $site->theme 对应的模板没有前后分离，就根据地址进行跳转
                $routeName = 'html_product_detail'; //没有前后分离的静态详情页路由名称
            }
            //没有前后分离的商品详情页静态url地址
            $url = route($routeName, $routeParam); //如果是有定义参数的命名路由，可以把参数作为 route 函数的第二个参数传入，指定的参数将会自动插入到 URL 中对应的位置：

            return redirect($url); //302重定向到 $url
        }

        return null;
    }

}
