<?php

namespace App\Util;

class Resources {

    /**
     * 获取资源地址
     * @param string $resourceUrl 资源地址
     * @param int $resourceType  资源类型 1:图片 2:视频 3:js 4:css 默认:1
     * @param boolean $isCn  是否使用国内cdn  false:否  true:是 默认：false
     * @return string
     */
    public static function getResourceUrl($resourceUrl, $resourceType = 1, $isCn = false, $wh = '', $mode = '0') {

        if (empty($resourceUrl)) {
            return '';
        }

        $resourceSrcUrl = $resourceUrl;

        $parseurl = parse_url($resourceUrl);
        $host = isset($parseurl['host']) ? $parseurl['host'] : '';

        $changeData = [
            'imgcn.stosz.com', 'cdn.bgnht.com', 'img.stosz.com', 'awscdn.szcuckoo.net', 'video.bgnht.com', 'stoshop-img1.stosz.com', 'stoshop-img2.stosz.com', 'stoshop-img3.stosz.com', 'stoshop-img4.stosz.com'
        ]; //单品站 七牛 cdn

        if (in_array($host, $changeData)) {//如果是单品站 七牛 cdn 资源，就强制使用商城站七牛 cdn 加速
            $cdnProvider = 'QiniuCdn';
        } else {
            if (config('app.env') == 'development') {//如果是本地开发环境，就不需要替换cdn域名，使用上传文件时的cdn域名
                if (isset($parseurl['scheme']) || isset($parseurl['host'])) {
                    return $resourceUrl;
                }
            }

            $resourceUrl = parse_url($resourceUrl, PHP_URL_PATH);
            $resourceUrl = ltrim($resourceUrl, '/');

            //获取CDN提供商
            $cdnProvider = explode('/', $resourceUrl, 2);
            $cdnProvider = array_first($cdnProvider);
        }
        $cdnProvider = "\\App\\Util\\Cdn\\$cdnProvider";

        if (!class_exists($cdnProvider)) {//如果没有对应的CDN提供商，就直接返回原始url
            return $resourceSrcUrl;
        }

        return $cdnProvider::getResourceUrl($resourceUrl, $resourceType, $isCn, $wh, $mode);
    }

    /**
     * 获取视频地址
     * @param string $videoUrl 视频地址
     * @return string 视频地址
     */
    public static function getVideoUrl($videoUrl, $is_https = 0) {
        return self::getResourceUrl($videoUrl, 2);
    }

    /**
     * 获取  图片 资源地址
     * @param String $imgUrl
     * @param int $is_https 1：强制用https 0:不强制用https
     * @return String 
     */
    public static function getImgUrl($imgUrl, $is_https = 0) {//1代表走https
        return self::getResourceUrl($imgUrl, 1);
    }

    /**
     * 货币格式化
     * @param number $number 数值
     * @param int $decimals 小数位数
     * @param string $is_currency_local
     *                  NUMBER:默认number_format格式化,没有千分位符号，
     *                  NUMBER_THOUSANDS:默认number_format格式化,有千分位符号，
     *                  CURRENCY:是显示货币符号,有千分位
     *                  CURRENCY_THOUSANDS_NO:是显示货币符号,没有千分位符号，
     *                  CURRENCY_NO:不显示货币符号,有千分位
     * @param array $options 扩展参数 ['dec_point' => '.', 'thousands_se' => ',', 'locale' => 'zh_CN']
     * @return string
     */
    public static function moneyMumberFormat($number, $decimals = 0, $is_currency_local = 'NUMBER', array $options = []) {
        $opt = ['dec_point' => '.', 'thousands_se' => '', 'locale' => 'zh_CN'];
        $options && $opt = array_merge($opt, $options);
        if ($is_currency_local == 'NUMBER') {
            return number_format($number, $decimals, $opt['dec_point'], $opt['thousands_se']);
        } elseif ($is_currency_local == 'NUMBER_THOUSANDS') {
            $opt['thousands_se'] = ',';
            return number_format($number, $decimals, $opt['dec_point'], $opt['thousands_se']);
        } else {
            //国家语言
            if (!$opt['locale']) {
                $opt['locale'] = 'zh_CN';
            }
            if ($is_currency_local == 'CURRENCY') {
                $currency = get_S('MONEY/TYPE')[$opt['locale']];
                if (!$currency) {
                    $currency = 'CNY';
                }
                return (new \NumberFormatter($opt['locale'], \NumberFormatter::CURRENCY))->formatCurrency($number, $currency);
            } else if ($is_currency_local == 'CURRENCY_THOUSANDS_NO') {
                $currency = get_S('MONEY/TYPE')[$opt['locale']];
                if (!$currency) {
                    $currency = 'CNY';
                }
                return str_replace(',', '', (new \NumberFormatter($opt['locale'], \NumberFormatter::CURRENCY))->formatCurrency($number, $currency));
            } else {
                $fmt = new \NumberFormatter($opt['locale'], \NumberFormatter::DECIMAL);
                $number = $fmt->format($number);
                if (intl_is_failure($fmt->getErrorCode())) {
                    exit("Formatter error");
                }
                return $number;
            }
        }
    }

    /**
     * 过滤资源数据数据
     * @param array $data 源数据
     * @param array $handleKeys 要过滤的数据 [$type => $keys] => $type 资源类型 1:图片 2:视频 3:js  4:css; $keys:要处理的key 
     * 如：[1 => ['thumb','title'],100 => [],200 => ['price', 'special']] 表示 资源类型：图片  要处理的key：'thumb','title'  100：html 200：价格
     * @return array 过滤后的数据
     */
    public static function handle($data, $handleKeys, $theme = '', $mode = '0') {
        $result = [];

        if (empty($data)) {
            return $data;
        }

        if (!is_array($data) && method_exists($data, 'toArray')) {//&& method_exists($data, 'toArray')
            $data = $data->toArray();
        }

        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            $_theme = $theme;
            if (is_string($key)) {
                $_theme .= '.' . $key;
            }

            if ($value instanceof \stdClass) {
                $value = json_decode(json_encode($value), true);
            }

            if (is_array($value) || $value instanceof \ArrayAccess) {
                $result[$key] = self::handle($value, $handleKeys, $_theme, $mode);
            } else {
                $result[$key] = $value;
                foreach ($handleKeys as $resourceType => $keys) {

                    if (!in_array($key, $keys, true)) {
                        continue;
                    }

                    $wh = config('resources.' . $_theme, '');
                    switch ($resourceType) {
                        case 100://内容
                            $result[$key] = self::getValidHtml($result[$key], [], $wh, $mode);
                            break;

                        case 200://价格
                            $_key = array_search($key, $keys);

                            $currencyCode = __('theme.currency_symbol');
                            $decimals = 0;
                            if (is_string($_key)) {
                                $ruleData = explode('|', $_key);
                                $currencyCode = ListData::getValidData($ruleData, 0, 1) ? $currencyCode : '';
                                $decimals = ListData::getValidData($ruleData, 1, 0);
                            }
                            $result[$key] = $currencyCode . static::moneyMumberFormat($result[$key], $decimals); //

                            break;

                        case 201://折扣
                            $result[$key] = static::moneyMumberFormat($result[$key] * 100, 0) . '%'; //
                            break;

                        default://图片 视频 js css 
                            $result[$key] = self::getResourceUrl($result[$key], $resourceType, false, $wh, $mode);

                            break;
                    }
                }
            }
        }
        unset($data);

        return $result;
    }

    /**
     * 获取有效数据
     * @return string|array
     */
    public static function getValidData($data = null, $name = null, $default = null) {

        if (empty($data)) {
            return $default;
        }

        return $name === null ? $data : (isset($data[$name]) ? $data[$name] : $default);
    }

    /**
     * 获取html中的图片数据
     * @param string $html
     * @param mix $name  0:获取html中的图片html标签 1:获取html中的图片地址  null：所有匹配结果
     * @param mix $default
     * @param array $changeData
     * @param string $wh  宽*高
     * @param int $mode  缩略模式
     * @return array|mix
     */
    public static function getImgFromHtml($html, $name = null, $default = [], $changeData = [], $wh = '', $mode = '0') {
        $html = strip_tags($html, '<img>');
        $html = str_ireplace(array('img', 'src'), array('img', 'src'), $html); //将 img和src 进行不区分大小写替换成 img和src，以保证后续正则匹配的正确执行
        preg_match_all("<img.*?src=\"(.*?.*?)\".*?>", $html, $match); //正则匹配图片

        $data = self::getValidData($match, $name, $default);
        $_changeData = [];
        $titleChangeData = [];
        foreach ($data as $url) {
            $_url = '/themes/style3/images/loading.gif" class="lazyload" data-img="' . self::getResourceUrl($url, 1, false, $wh, $mode) . '" onerror="this.src=window.redirectImage(\'image/none_pic.png\')'; //资源类型 1:图片 2:视频 3:js 4:css 默认:1
            $_changeData[$url] = $_url; //资源类型 1:图片 2:视频 3:js 4:css 默认:1
            $titleChangeData['title="' . $_url . '"'] = ' '; //资源类型 1:图片 2:视频 3:js 4:css 默认:1
        }

        preg_match_all("<img.*?title=\"(.*?.*?)\".*?>", $html, $_match); //正则匹配图片
        $data = self::getValidData($_match, $name, $default);
        foreach ($data as $url) {
            $titleChangeData['title="' . $url . '"'] = ' '; //资源类型 1:图片 2:视频 3:js 4:css 默认:1
        }

        $changeData['title'] = $titleChangeData;

        return array_merge($changeData, $_changeData);
    }

    /**
     * 获取html中的视频数据
     * @param string $html
     * @param mix $name  0:获取html中的图片html标签 1:获取html中的图片地址  null：所有匹配结果
     * @param mix $default
     * @param array $changeData
     * @param string $wh  宽*高
     * @param int $mode  缩略模式
     * @return array|mix
     */
    public static function getVideoFromHtml($html, $name = null, $default = [], $changeData = [], $wh = '', $mode = '0') {
        $html = strip_tags($html, '<video>');
        $html = str_ireplace(array('video', 'src'), array('video', 'src'), $html); //将 img和src 进行不区分大小写替换成 img和src，以保证后续正则匹配的正确执行
        preg_match_all("<video.*?src=\"(.*?.*?)\".*?>", $html, $match); //正则匹配图片

        $data = self::getValidData($match, $name, $default);
        $_changeData = [];
        foreach ($data as $url) {
            $_changeData[$url] = self::getResourceUrl($url, 2); //资源类型 1:图片 2:视频 3:js 4:css 默认:1  , false, $wh, $mode
        }
        return array_merge($changeData, $_changeData);
    }

    /**
     * 获取html中的js数据
     * @param string $html
     * @param mix $name  0:获取html中的图片html标签 1:获取html中的图片地址  null：所有匹配结果
     * @param mix $default
     * @param array $changeData
     * @param string $wh  宽*高
     * @param int $mode  缩略模式
     * @return array|mix
     */
    public static function getJsFromHtml($html, $name = null, $default = [], $changeData = [], $wh = '', $mode = '0') {
        $html = strip_tags($html, '<script>');
        $html = str_ireplace(array('script', 'src'), array('script', 'src'), $html); //将 img和src 进行不区分大小写替换成 img和src，以保证后续正则匹配的正确执行
        preg_match_all('/<script.*?src=([^> ]+)/', $html, $match); //正则匹配图片

        $data = self::getValidData($match, $name, $default);
        $_changeData = [];
        foreach ($data as $url) {
            $_changeData[$url] = self::getResourceUrl($url, 3, false); //资源类型 1:图片 2:视频 3:js 4:css 默认:1  , $wh, $mode
        }
        return array_merge($changeData, $_changeData);
    }

    /**
     * 获取html中的css数据
     * @param string $html
     * @param mix $name  0:获取html中的图片html标签 1:获取html中的图片地址  null：所有匹配结果
     * @param mix $default
     * @param array $changeData
     * @param string $wh  宽*高
     * @param int $mode  缩略模式
     * @return array|mix
     * @return mix
     */
    public static function getCssFromHtml($html, $name = null, $default = [], $changeData = [], $wh = '', $mode = '0') {
        $html = strip_tags($html, '<link>');
        $html = str_ireplace(array('link', 'href'), array('link', 'href'), $html); //将 img和src 进行不区分大小写替换成 img和src，以保证后续正则匹配的正确执行
        preg_match_all('/<link.*?href=([^> ]+)/', $html, $match); //正则匹配图片

        $data = self::getValidData($match, $name, $default);

        $_changeData = [];
        foreach ($data as $url) {
            $_changeData[$url] = self::getResourceUrl($url, 4); //资源类型 1:图片 2:视频 3:js 4:css 默认:1  , false, $wh, $mode
        }

        return array_merge($changeData, $_changeData);
    }

    /**
     * 获取有效html
     * @param string $html 原始html串
     * @param string $changeData 要替换的数据
     * @param string $wh  宽*高
     * @param int $mode  缩略模式
     * @return string 有效html
     */
    public static function getValidHtml($html, $changeData = [], $wh = '', $mode = '0') {

        $changeData = self::getCssFromHtml($html, 1, [], $changeData, $wh, $mode); //将css地址替换成cdn加速地址
        $changeData = self::getJsFromHtml($html, 1, [], $changeData, $wh, $mode); //将js地址替换成cdn加速地址
        $changeData = self::getVideoFromHtml($html, 1, [], $changeData, $wh, $mode); //将图片地址替换成cdn加速地址
        $changeData = self::getImgFromHtml($html, 1, [], $changeData, $wh, $mode); //将图片地址替换成cdn加速地址

        $changeData = array_filter($changeData);
        if ($changeData) {

            $titleChangeData = [];
            if (isset($changeData['title'])) {
                $titleChangeData = $changeData['title'];
                unset($changeData['title']);
            }

            if ($changeData) {
                $html = strtr($html, $changeData);
            }

            if ($titleChangeData) {
                $html = strtr($html, $titleChangeData);
            }
        }


        return $html;
    }

    /**
     * 过滤资源数据数据
     * @param array $data 源数据
     * @param array $handleKeys 要过滤的数据 [$type => $keys] => $type 资源类型 1:图片 2:视频 3:js  4:css; $keys:要处理的key 
     * 如：[1 => ['thumb','title']] 表示 资源类型：图片  要处理的key：'thumb','title'
     * @return array 过滤后的数据
     */
    public static function getResourcesWh($data) {
        $result = [];

        if (empty($data)) {
            return $data;
        }

        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $rs = self::getResourcesWh($value);
                $result = array_merge($result, $rs);
            } else {
                $result[] = $value;
            }
        }

        $result = array_filter($result);
        $result = array_unique($result);

        return $result;
    }

}
