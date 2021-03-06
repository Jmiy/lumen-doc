<?php

namespace App\Util\Cdn;

use App\Util\Cdn\CdnManager;
use App\Util\Constant;

class ResourcesCdn {

    public static $uriPrefix = []; // 图片路径前缀
    public static $imgUrlCn = ''; //国内
    public static $cdnDomains = []; //css域名
    public static $resourceKey = []; //资源域名序号
    public static $file = null; //文件对象

    /**
     * 获取服务器地区
     * @return string 服务器地区
     */

    public static function getArea() {
        $_area = explode('_', config('app.serverarea'));
        $area = end($_area);
        return $area;
    }

    /**
     * 获取资源域名
     * @param int $resourceType  资源类型 1:图片 2:视频 3:js 4:css 默认:1
     * @param array|null $domain cdn域名
     * @return array 图片cdn域名
     */
    public static function getResourceTypeDomain($resourceType = 1, $isCn = false, $domain = null) {

        $area = static::getArea();
        $cdnDomains = isset(static::$cdnDomains[$area]) ? static::$cdnDomains[$area] : static::$cdnDomains['asia'];
        $cdnDomains = isset($cdnDomains[$resourceType]) ? $cdnDomains[$resourceType] : $cdnDomains[1];

        if (is_array($domain)) {
            $cdnDomains = array_merge($cdnDomains, $domain);
        }

        return array_unique($cdnDomains);
    }

    /**
     * 获取资源域名
     * @param int $resourceType  资源类型 1:图片 2:视频 3:js 4:css 默认:1
     * @param array|null $domain cdn域名
     * @return array 图片cdn域名
     */
    public static function getResourceDomain($resourceType = 1, $isCn = false, $domain = null) {

        //获取资源cdn数据
        $cdnData = static::getResourceTypeDomain($resourceType, $isCn, $domain);

        $num = count($cdnData);
        if (!isset(static::$resourceKey[$resourceType])) {
            static::$resourceKey[$resourceType] = $num;
        }

        $key = static::$resourceKey[$resourceType] % $num;

        static::$resourceKey[$resourceType] += 1;

        return $cdnData[$key];
    }

    /**
     * 获取资源地址
     * @param string $resourceUrl 资源地址
     * @param int $resourceType  资源类型 1:图片 2:视频 默认:1
     * @param boolean $isCn  是否使用国内cdn  false:否  true:是 默认：false
     * @param string $wh  宽*高
     * @param int $mode  缩略模式：0-5 详情：https://developer.qiniu.com/dora/manual/1279/basic-processing-images-imageview2
     * @return string
     */
    public static function getResourceUrl($resourceUrl, $resourceType = 1, $isCn = false, $wh = '', $mode = '0') {

        if (empty($resourceUrl)) {
            return '';
        }

        $resourceUrl = parse_url($resourceUrl, PHP_URL_PATH);
        $cdnData = static::getResourceDomain($resourceType, $isCn);

        $url = $cdnData . '/' . ltrim($resourceUrl, '/');
        $urlParam = static::getUrlParam($wh, $mode);

        return $url . $urlParam;
    }

    /**
     * 获取图片 格式转换、缩略、剪裁 参数
     * @param string $wh  宽*高
     * @param int $mode  缩略模式：0-5 详情：https://developer.qiniu.com/dora/manual/1279/basic-processing-images-imageview2
     * @param string $quality 新图的图片质量  取值范围是[1, 100]，默认75。七牛会根据原图质量算出一个修正值，取修正值和指定值中的小值。
     *    注意：
     *    ● 如果图片的质量值本身大于90，会根据指定值进行处理，此时修正值会失效。
     *    ● 指定值后面可以增加 !，表示强制使用指定值，如100!。
     *    ● 支持图片类型：jpg。
     *    详情：https://developer.qiniu.com/dora/manual/1279/basic-processing-images-imageview2#1
     * @return string
     */
    public static function getUrlParam($wh = '', $mode = '0', $quality = '75') {
        $urlParam = '';

        if (empty($wh)) {
            return $urlParam;
        }

        $whData = explode('*', $wh);

        if ((isset($whData[0]) && $whData[0] != 0) || isset($whData[1]) && $whData[1] != 0) {
            $urlParam .= '?imageView2/' . $mode;
        }

        if (isset($whData[0]) && $whData[0] != 0) {
            $urlParam .= '/w/' . $whData[0];
        }

        if (isset($whData[1]) && $whData[1] != 0) {
            $urlParam .= '/h/' . $whData[1];
        }

        if ($urlParam) {
            $urlParam .= '/interlace/1/ignore-error/1/q/' . $quality;
        }

        return $urlParam;
    }

    /**
     * 获取上传到七牛所使用的文件URI
     * @param string $vitualPath 七牛虚拟路径
     * @param string $ext 文件后缀
     * @param string $fileName 文件名
     * @return string 文件URI
     */
    public static function getUploadFileName($resourceType = 1, $vitualPath = '', $ext = null, $fileName = '') {
        $fileName = static::getFileName($ext, $fileName);
        $filePath = static::getDistVitualPath($resourceType, $vitualPath);
        return implode('/', [$filePath, $fileName]);
    }

    /**
     * Returns locale independent base name of the given path.
     *
     * @param string $name The new file name
     *
     * @return string containing
     */
    public static function getName($name) {
        $originalName = str_replace('\\', '/', $name);
        $pos = strrpos($originalName, '/');
        $originalName = false === $pos ? $originalName : substr($originalName, $pos + 1);

        return $originalName;
    }

    public static function upload($filePath, $files = null, $vitualPath = '', $is_del = false, $isCn = false, $fileName = '', $resourceType = 1, $extData = Constant::PARAMETER_ARRAY_DEFAULT) {

    }

    /**
     * @param $url
     * @return array
     * 删除空间文件
     */
    public static function deleteFiles($url) {

    }

    /**
     * 获取默认的响应数据结构
     * @param int $code 响应状态码
     * @param string $msg 响应提示
     * @param array $data 响应数据
     * @return array $data
     */
    public static function getDefaultResponseData($code = Constant::PARAMETER_INT_DEFAULT, $msg = Constant::PARAMETER_STRING_DEFAULT, $data = Constant::PARAMETER_ARRAY_DEFAULT) {
        return CdnManager::getDefaultResponseData($code, $msg, $data);
    }

    /**
     * 获取目的云存储虚拟路径
     * @param int $resourceType 文件类型 1：图片 2：视频
     * @param string $vitualPath 云存储虚拟路径
     * @return string 目的云存储虚拟路径
     */
    public static function getDistVitualPath($resourceType = 1, $vitualPath = '') {
        $_path = date('YmdHis');
        return (isset(static::$uriPrefix[$resourceType]) && static::$uriPrefix[$resourceType] ? static::$uriPrefix[$resourceType] : '') . ($vitualPath ? (rtrim(ltrim($vitualPath, '/'), '/') . '/') : '') . $_path;
    }

    /**
     * 获取文件名
     * @param string $ext
     * @param string $fileName
     * @return string 文件名
     */
    public static function getFileName($ext = null, $fileName = '') {
        $fileExt = $ext ? $ext : (static::$file ? ('.' . static::$file->extension()) : '');
        return $fileName ? $fileName : (time() . rand(100, 999) . $fileExt);
    }

}
