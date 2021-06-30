<?php

namespace App\Util;

class Html {

    public static $disk = 'app_root';

    /**
     * 生成静态html文件
     * @param array|string $data 页面对应的数据|页面html串
     * @param string $fileName   文件名
     * @param string $domain     站点域名
     * @return string 页面html串
     */
    public static function handle($data = [], $fileName = 'index.html', $domain = '', $isCreateFile = true) {

        $domain = $domain ? $domain : request()->getHost();

        if (is_array($data)) {
            if (!isset($data['site'])) {
                $data['site'] = \App\Model\Site::getItemByDomain();
            }

            $js = 'var data = ' . json_encode($data) . ';';
            $html = \Illuminate\Support\Facades\Storage::disk(static::$disk)->get('index.html'); //获取模板文件

            $changeData = [
                '<meta name=AppPublic content=http://asia-cube-js-css.stosz.com>' => '<meta name="AppPublic" content="' . \App\Util\Cdn\QiniuCdn::getResourceDomain(3) . '">',
                '/*__DATA__*/' => $js,
            ];
            $html = \App\Util\Resources::getValidHtml($html, $changeData);
        } else {
            $changeData = [];
            $html = $data;
        }

//        if ($isCreateFile) {
//            $srcFileName = '/html/' . $domain . '/' . $fileName;
//            $exists = \Illuminate\Support\Facades\Storage::disk(static::$disk)->exists($srcFileName);
//            if (!$exists) {
//                \Illuminate\Support\Facades\Storage::disk(static::$disk)->put($srcFileName, $html);
//            }
//        }

        return $html;
    }

    public static function getClearHtmlCacheKey($domain = '') {
        return 'html_clear';
    }

    public static function setClearHtmlCache($domain = '') {
        $dataCacheKey = static::getClearHtmlCacheKey($domain);
        return \Illuminate\Support\Facades\Cache::put($dataCacheKey, time(), 60); //缓存清空站点html的时间戳，缓存60分钟;
    }

    /**
     * 清空站点静态文件
     * @param string $domain     站点域名
     * @return boolean true:清空成功 false:清空失败
     */
    public static function clear($domain = '') {

        try {
            $dataCacheKey = static::getClearHtmlCacheKey($domain);
            $clearAllTime = \Illuminate\Support\Facades\Cache::get($dataCacheKey);
            $clearAllTime = $clearAllTime ? intval($clearAllTime) : 0;

            $realip = gethostbyname(gethostname()); //获取服务器ip地址
            $cacheKey = $dataCacheKey . '_' . md5($domain . '_' . $realip);
            $clearTime = \Illuminate\Support\Facades\Cache::get($cacheKey);
            $clearTime = $clearTime ? intval($clearTime) : 0;

            if ($clearAllTime < $clearTime) {
                return ['code' => 0, 'msg' => 'cleared==>30 min'];
            }

            \Illuminate\Support\Facades\Cache::put($cacheKey, time(), 30); //缓存清空站点html的时间戳，防止频繁清空，缓存30分钟

            $directory = 'html' . ($domain ? ('/' . $domain) : '');
            \Illuminate\Support\Facades\Storage::disk(static::$disk)->deleteDirectory($directory);

//                $directories = \Illuminate\Support\Facades\Storage::disk(static::$disk)->allDirectories($directory);
//                $files = \Illuminate\Support\Facades\Storage::disk(static::$disk)->allFiles($directory);
//                \Illuminate\Support\Facades\Storage::disk(static::$disk)->delete($files); //删除文件
        } catch (\Exception $exc) {
            return ['code' => 1, 'msg' => $exc->getMessage()];
        }

        return ['code' => 0, 'msg' => 'success'];
    }

}
