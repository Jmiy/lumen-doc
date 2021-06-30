<?php

/**
 * ERP通讯 getDepartmentStatus:获取部门状态  getErpProduct:拉取erp产品数据
 *
 * @ Package  : App\Erps
 * @ Author   : Jmiy
 * @ Version  : 2018-09-11
 *
 */

namespace App\Util\Erp;

use App\Helpers\BLogger;
use Illuminate\Support\Facades\Session;
use App\Model\ProductMap;
use App\Model\ProductTypeMap;
use App\Model\ProductAttrMap;
use App\Model\Site;
use App\Util\ListData;

class ErpNew {

    /**
     * 1，建站所有用到的新ERP的相关域名  测试环境  http://luckydog-erp-front-test.stosz.com   正式环境： http://luckydog.stosz.com ; 端口统一去掉；
     * 2，接口url变更 ：
     *     /pc/getAdUserList/loginid/{loginid} 变更为：  /product/pc/getAdUserList/loginid/{loginid}        
     *     /pc/archive/product/{productId}/loginid/{operatorLoginid}  变更为： /product/pc/archive/product/{productId}/loginid/{operatorLoginid}   
     *    /pc/product/{productId}/seoLoginid/{seoLoginid}  变更为： /product/pc/product/{productId}/seoLoginid/{seoLoginid}  
     * @var type 
     */
    // token
    public $_token;

    public function __construct() {
        $this->_token = config('erp.erpToken');
    }

    /**
     * 获取系统时间微秒数
     * @return float
     */
    private static function getMillisecond() {
        list($s1, $s2) = explode(' ', microtime());
        return (float) sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * 生成随机字符串
     * @param int $length
     * @return string
     */
    private static function getRandomString($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        $len = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, $len), 1);
        }
        return $str;
    }

    private static function getToken() {
        return config('erp.erpToken');
    }

    /**
     * 获取请求头
     * @return string
     */
    private static function getHeaders() {

        $millisecond = static::getMillisecond();
        $randomString = static::getRandomString(8);

        $token = static::getToken();
        $sign = md5($token . $millisecond . $randomString);

        //封装头部验证信息
        $headers = [
            "X-PROJECT-ID:frontend.website.build",
            "Accept:application/json,text/plain,*/*",
            "x-requested-with:XMLHttpRequest",
            "X-AUTH-TIMESTAMP:$millisecond",
            "X-AUTH-NONCE:$randomString",
            "X-AUTH-SIGNATURE:$sign",
        ];

        return $headers;
    }

    /**
     * 获取部门状态
     */
    public static function getDepartmentStatus() {
        $product['success'] = false;
        $product['item'] = [];

        //封装头部验证信息
        $uri = config('erp.url_erp_department_status') . '/admin/department/getDeptConfig';
//        $headers = static::getHeaders();
//        $result = getRequest($uri, $headers);

        $responseData = static::request($uri, [], [], 'GET'); //
        $result = $responseData['responseText'];

        if (empty($result)) {
            $product['message'] = 'ERP产品信息获取失败';

            BLogger::getLogger('department_status', 'erp')->info(date('Y-m-d H:i:s', time()));
            BLogger::getLogger('department_status', 'erp')->info($uri);
            BLogger::getLogger('department_status', 'erp')->info(json_encode($headers));
            return $product;
        }

        //$obj = json_decode($result, true);
        $obj = $result;
        if (!$obj['success']) {
            $product['message'] = $obj['desc'];
            BLogger::getLogger('department_status', 'erp')->info(date('Y-m-d H:i:s', time()));
            BLogger::getLogger('department_status', 'erp')->info($uri);
            BLogger::getLogger('department_status', 'erp')->info(json_encode($headers));
            return $product;
        }

        $product['success'] = true;
        $product['item'] = json_decode($obj['item']['jsonData'], true);
        $product['message'] = "OK";

        return $product;
    }

    /**
     * 拉取erp产品数据
     * @param $erp_id erp产品id
     * @param $loginUserName  账号
     * @return string
     */
    public static function getErpProduct($erp_id, $loginUserName, $siteId = 1) {

        //  /pc/product/{productId}/seoLoginid/{seoLoginid} 改成 /product/pc/product/{productId}/seoLoginid/{seoLoginid}
        $uri = config('erp.url_erp_product') . '/product/pc/product/' . $erp_id . '/seoLoginid/' . $loginUserName;
//        $headers = static::getHeaders();
//        $result = getRequest($uri, $headers); //{"code":"FAIL","desc":"优化师loginid：zhenglongli对应的部门没有申报此产品！","item":null,"total":null,"success":false,"failed":true}

        $responseData = static::request($uri, [], [], 'GET'); //
        $result = $responseData['responseText'];

        if (empty($result)) {
            $product['success'] = false;
            $product['message'] = 'ERP产品信息获取失败';
            $product['item'] = [];

            BLogger::getLogger('product', 'erp/' . $siteId)->info(date('Y-m-d H:i:s', time()));
            BLogger::getLogger('product', 'erp/' . $siteId)->info($uri);
            BLogger::getLogger('product', 'erp/' . $siteId)->info(json_encode($headers));

            return $product;
        }

//        $obj = json_decode($result, 1);
//        var_dump($obj);
        $obj = $result;
        if (empty($obj['success'])) {
            $product['success'] = false;
            $product['message'] = $obj['desc'];
            $product['item'] = [];
            return $product;
        }

        if (empty($obj['item']['productZoneList'])) {
            $product['success'] = false;
            $product['message'] = '产品销售地区为空,可能已经消档';
            $product['item'] = [];
            return $product;
        }

        $product['success'] = true;
        $product['message'] = 'ERP产品信息获取成功';

        $product['product']["erp_id"] = $obj['item']['id'];
        $product['product']["spu"] = $obj['item']['spu'];
        $product['product']["title"] = $obj['item']['title'];
        $product['product']["foreign_title"] = $obj['item']['title'];
        $product['product']["user_id"] = $obj['item']['creatorId'];
        $product['product']["quantity"] = $obj['item']['totalStock'];
        $product['product']['department_id'] = $obj['item']['productZoneList'][0]['departmentId'];
        $product['product']["created_at"] = $obj['item']['createAt'];
        $product['product']["updated_at"] = $obj['item']['updateAt'];

        $product['product']["product_attr"] = $obj['item']['attributeList'];
        $product['product']["product_zone_name"] = array_column($obj['item']['productZoneList'], 'zoneName');

        return $product;
    }

    /**
     * 
     * @param string $url  api接口
     * @param array $requestData 请求报文
     * @param string $username  账号
     * @param string $password  密码
     * @param string $requestMethod 请求方法
     * @return array $responseText 响应报文
     */
    public static function request($url, $requestData = [], $headers = ['Content-Type: application/json; charset=utf-8'], $requestMethod = 'POST') {

        $curlOptions = [
            CURLOPT_CONNECTTIMEOUT_MS => 1000 * 100,
            CURLOPT_TIMEOUT_MS => 1000 * 100,
            CURLOPT_CUSTOMREQUEST => $requestMethod, //自定义的请求方式
        ];
        $headers = static::getHeaders();

        $responseText = \App\Util\Curl::request($url, $headers, $curlOptions, $requestData, $requestMethod);

        return $responseText;
    }

    /**
     * 获取货币数据
     * @return mixed
     */
    public static function getCurrency() {

        $response = static::request(config('erp.url_currency_list'));
        $response = collect($response['responseText']['item'])->keyBy('currencyCode')->toArray();

        return $response;
    }

    /**
     * 获取区域数据
     * @return mixed
     */
    public static function getZone() {

        $responseData = static::request(config('erp.url_zone_list'));

        $responseData = collect($responseData['responseText']['item'])->keyBy(function ($item) {
                    return strtoupper($item['code']);
                })->toArray();

        return $responseData;
    }

    /**
     * 下单地区获取
     * @param string $key 地区编号
     * @return string|int
     */
    public static function getZoneId($zoneCode) {

        if (empty($zoneCode)) {
            return '';
        }

        $data = static::getZone(); //获取区域数据
        $zoneCode = strtoupper($zoneCode);
        if (!isset($data[$zoneCode]) || empty($data[$zoneCode])) {
            return '';
        }

        return $data[$zoneCode]['id'];
    }

    /**
     * 获取区域下拉数据
     * @return mixed
     */
    public static function getZoneSelect() {

        $data = static::getZone(); //获取区域数据
        $selectData = [];
        foreach ($data as $key => $item) {
            $selectData[] = [
                "title" => $item['title'],
                "language" => $item['title'],
                "lang" => $item['code'],
            ];
        }

        return json_encode($selectData, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 添加产品
     * @return mixed
     */
    public static function addProduct($productData) {

        $siteData = Site::find($productData['site_id']);
        $dataItem = [
            "erpProductId" => 0, //erp产品id
            "creator" => $productData['user_name'], //创建人
            "refenceId" => $productData['provider_id'], //参考id,app产品信息的id
            "productSourceEnum" => "api", //产品来源类型:api,batch,app对接使用api
            "title" => $productData['title'], //产品标题
            "categoryId" => $productData['category_id'], //产品所属品类ID
            "classifyEnum" => "S", //产品特性，Y:特货,S普货
            "classifyEnumName" => "普货",
            "innerName" => $productData['title'], //产品内部名
            "mainImageUrl" => $productData['thumb'], //产品主图地址
            "sourceEnum" => "other", //产品来源:market,taobao,alibaba,other
            "sourceEnumName" => $productData['provider'],
            "sourceUrl" => 'https://detail.1688.com/offer/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD', //产品来源地址
            "memo" => $productData['provider'] . "产品导入", //备注
            "checker" => "系统", //审核人
            "customEnum" => "normal", //产品自定义类别：normal(标品)、custome(定制)
            "customEnumName" => "标品",
            "productZoneList" => [//产品区域集合:需要区域ID(zoneId)和部门ID(departmentId),参考示例
                [
                    "departmentId" => $productData['department_id'], //部门ID
                    "zoneId" => static::getZoneId($siteData->lang), //区域ID
                    "table" => "product_zone",
                ]
            ],
            "table" => "product",
        ];

        var_dump('===============添加产品==================');
        var_dump($dataItem);

        $dataItem = json_encode($dataItem);

        //$data = '[{"creator":"chenfengcai","refenceId":24358,"productSourceEnum":"api","title":"2018 Mujeres Abrigo de Invierno Nuevo Ultra Ligero 90% Pato Blanco Abajo","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"2018 Mujeres Abrigo de Invierno Nuevo Ultra Ligero 90% Pato Blanco Abajo","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-803101825_1024x1024_b83a555f-b438-4085-a7e1-c56db8421b99.jpg?v=1539594741","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Rosa,rojo,lago azul,verde oscuro,negro,beige,vino tinto,Verde fluorescente,azul marino,Rosa roja,Azul claro,naranja,p\u00farpura,blanco;Size:Rosa,rojo,lago azul,verde oscuro,negro,beige,vino tinto,Verde fluorescente,azul marino,Rosa roja,Azul claro,naranja,p\u00farpura,blanco,S,XL,L,M,XXL,XXXL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Rosa","table":"attribute_value"},{"title":"rojo","table":"attribute_value"},{"title":"lago azul","table":"attribute_value"},{"title":"verde oscuro","table":"attribute_value"},{"title":"negro","table":"attribute_value"},{"title":"beige","table":"attribute_value"},{"title":"vino tinto","table":"attribute_value"},{"title":"Verde fluorescente","table":"attribute_value"},{"title":"azul marino","table":"attribute_value"},{"title":"Rosa roja","table":"attribute_value"},{"title":"Azul claro","table":"attribute_value"},{"title":"naranja","table":"attribute_value"},{"title":"p\u00farpura","table":"attribute_value"},{"title":"blanco","table":"attribute_value"}]},{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24359,"productSourceEnum":"api","title":"2018 nueva chaqueta de invierno de las mujeres","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"2018 nueva chaqueta de invierno de las mujeres","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-736643097_cc866580-7a70-409b-a6d3-92479fe91335.jpg?v=1539249424","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Negro,Verde,Caqui,Rojo,Azul oscuro,Naranja,Blanco,Vino rojo;Tama\u00f1o:Negro,Verde,Caqui,Rojo,Azul oscuro,Naranja,Blanco,Vino rojo,S,M,L,XL,XXL,XXXL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Negro","table":"attribute_value"},{"title":"Verde","table":"attribute_value"},{"title":"Caqui","table":"attribute_value"},{"title":"Rojo","table":"attribute_value"},{"title":"Azul oscuro","table":"attribute_value"},{"title":"Naranja","table":"attribute_value"},{"title":"Blanco","table":"attribute_value"},{"title":"Vino rojo","table":"attribute_value"}]},{"title":"Tama\u00f1o","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24360,"productSourceEnum":"api","title":"Abrigo y sudadera con capucha irregular de manga larga con cremallera","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Abrigo y sudadera con capucha irregular de manga larga con cremallera","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/20180927_200621_028.jpg?v=1538050553","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Size:S,M,L,XL,XXL,3XL,4XL,5XL;Color:S,M,L,XL,XXL,3XL,4XL,5XL,Negro,gris","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"3XL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"},{"title":"5XL","table":"attribute_value"}]},{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Negro","table":"attribute_value"},{"title":"gris","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24361,"productSourceEnum":"api","title":"Abrigos con capucha y cremallera Casual Denim","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Abrigos con capucha y cremallera Casual Denim","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/20180926_171914_004.jpg?v=1539588158","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Size:M,L,XL,XXL,3XL,4XL;Color:M,L,XL,XXL,3XL,4XL,azul claro,azul profundo","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"3XL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"}]},{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"azul claro","table":"attribute_value"},{"title":"azul profundo","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24362,"productSourceEnum":"api","title":"Chaleco de pato abajo","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Chaleco de pato abajo","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-737384232_0f35ba05-8e7f-4acc-aa93-fc9d8113dbe2.jpg?v=1539252468","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Negro,Azul marino,Lago azul,Verde,Caqui,Naranja,Rosado morado,P\u00farpura,Rojo,Rosa roja,Blanco,Vino rojo;Tama\u00f1o:Negro,Azul marino,Lago azul,Verde,Caqui,Naranja,Rosado morado,P\u00farpura,Rojo,Rosa roja,Blanco,Vino rojo,S,M,L,XL,XXL,XXXL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Negro","table":"attribute_value"},{"title":"Azul marino","table":"attribute_value"},{"title":"Lago azul","table":"attribute_value"},{"title":"Verde","table":"attribute_value"},{"title":"Caqui","table":"attribute_value"},{"title":"Naranja","table":"attribute_value"},{"title":"Rosado morado","table":"attribute_value"},{"title":"P\u00farpura","table":"attribute_value"},{"title":"Rojo","table":"attribute_value"},{"title":"Rosa roja","table":"attribute_value"},{"title":"Blanco","table":"attribute_value"},{"title":"Vino rojo","table":"attribute_value"}]},{"title":"Tama\u00f1o","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24363,"productSourceEnum":"api","title":"Chaqueta de delgada zipper","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Chaqueta de delgada zipper","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-817456033.jpg?v=1539415585","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Negro,Rodas,rojo,ejercito verde,champ\u00e1n,borgo\u00f1a;Size:Negro,Rodas,rojo,ejercito verde,champ\u00e1n,borgo\u00f1a,S,M,L,XL,XXL,XXXL,4XL,5XL,6XL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Negro","table":"attribute_value"},{"title":"Rodas","table":"attribute_value"},{"title":"rojo","table":"attribute_value"},{"title":"ejercito verde","table":"attribute_value"},{"title":"champ\u00e1n","table":"attribute_value"},{"title":"borgo\u00f1a","table":"attribute_value"}]},{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"},{"title":"5XL","table":"attribute_value"},{"title":"6XL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24364,"productSourceEnum":"api","title":"Chaqueta de manga larga con cuello redondo y manga larga","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Chaqueta de manga larga con cuello redondo y manga larga","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/231259_20_2818_29.jpg?v=1537963608","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Size:S,M,L,XL,XXL,3XL,4XL,5XL;Color:S,M,L,XL,XXL,3XL,4XL,5XL,azul,gris claro,ejercito verde,rojo,borgo\u00f1a,gris oscuro,caf\u00e9,verde,negro,rosado","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"3XL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"},{"title":"5XL","table":"attribute_value"}]},{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"azul","table":"attribute_value"},{"title":"gris claro","table":"attribute_value"},{"title":"ejercito verde","table":"attribute_value"},{"title":"rojo","table":"attribute_value"},{"title":"borgo\u00f1a","table":"attribute_value"},{"title":"gris oscuro","table":"attribute_value"},{"title":"caf\u00e9","table":"attribute_value"},{"title":"verde","table":"attribute_value"},{"title":"negro","table":"attribute_value"},{"title":"rosado","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24365,"productSourceEnum":"api","title":"Chaqueta larga de parka","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Chaqueta larga de parka","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-550975840.jpg?v=1539331046","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Grey,Green,Black,Red,Pink,White,Army green;Size:Grey,Green,Black,Red,Pink,White,Army green,XL,L,M,XXL,XXXL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Grey","table":"attribute_value"},{"title":"Green","table":"attribute_value"},{"title":"Black","table":"attribute_value"},{"title":"Red","table":"attribute_value"},{"title":"Pink","table":"attribute_value"},{"title":"White","table":"attribute_value"},{"title":"Army green","table":"attribute_value"}]},{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"XL","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24366,"productSourceEnum":"api","title":"Nueva 2018 chaqueta larga con capucha ultraligera para mujer","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Nueva 2018 chaqueta larga con capucha ultraligera para mujer","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-818244458_740x_2a13bb1e-b93c-43a3-b889-c24801325376.jpg?v=1539597666","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:rojo,Negro,Caqui,borgo\u00f1a,ejercito verde,Azul marino,Rhodo,P\u00farpura;Tama\u00f1o:rojo,Negro,Caqui,borgo\u00f1a,ejercito verde,Azul marino,Rhodo,P\u00farpura,S,XL,L,M,XXL,XXXL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"rojo","table":"attribute_value"},{"title":"Negro","table":"attribute_value"},{"title":"Caqui","table":"attribute_value"},{"title":"borgo\u00f1a","table":"attribute_value"},{"title":"ejercito verde","table":"attribute_value"},{"title":"Azul marino","table":"attribute_value"},{"title":"Rhodo","table":"attribute_value"},{"title":"P\u00farpura","table":"attribute_value"}]},{"title":"Tama\u00f1o","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24367,"productSourceEnum":"api","title":"Pato blanco abrigos de plumas","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Pato blanco abrigos de plumas","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-424562484.jpg?v=1539326145","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Negro,Armada,Caqui oscuro,Fucsia,P\u00farpura,Rojo,Vino,Ejercito verde;Tama\u00f1o:Negro,Armada,Caqui oscuro,Fucsia,P\u00farpura,Rojo,Vino,Ejercito verde,S,M,L,XL,XXL,XXXL,4XL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Negro","table":"attribute_value"},{"title":"Armada","table":"attribute_value"},{"title":"Caqui oscuro","table":"attribute_value"},{"title":"Fucsia","table":"attribute_value"},{"title":"P\u00farpura","table":"attribute_value"},{"title":"Rojo","table":"attribute_value"},{"title":"Vino","table":"attribute_value"},{"title":"Ejercito verde","table":"attribute_value"}]},{"title":"Tama\u00f1o","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24368,"productSourceEnum":"api","title":"S ~ 6XL Oto\u00f1o Invierno Mujeres  Down Chaqueta","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"S ~ 6XL Oto\u00f1o Invierno Mujeres  Down Chaqueta","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-792299424.jpg?v=1540179674","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Rojo oscuro,Azul,Negro,champ\u00e1n,borgo\u00f1a,Azul marino,Rodas,Verde amarillento;Size:Rojo oscuro,Azul,Negro,champ\u00e1n,borgo\u00f1a,Azul marino,Rodas,Verde amarillento,S,XL,4XL,5XL,6XL,L,M,XXL,XXXL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Rojo oscuro","table":"attribute_value"},{"title":"Azul","table":"attribute_value"},{"title":"Negro","table":"attribute_value"},{"title":"champ\u00e1n","table":"attribute_value"},{"title":"borgo\u00f1a","table":"attribute_value"},{"title":"Azul marino","table":"attribute_value"},{"title":"Rodas","table":"attribute_value"},{"title":"Verde amarillento","table":"attribute_value"}]},{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"},{"title":"5XL","table":"attribute_value"},{"title":"6XL","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24369,"productSourceEnum":"api","title":"Sudaderas con capucha y sudaderas de manga larga en color liso oto\u00f1o invierno polar","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Sudaderas con capucha y sudaderas de manga larga en color liso oto\u00f1o invierno polar","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/1_2000x2000_4fc03904-0160-4d4e-a29d-4ad4d08e74d7.jpg?v=1538030407","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Size:S,M,L,XL,XXL,3XL,4XL,5XL;Color:S,M,L,XL,XXL,3XL,4XL,5XL,marr\u00f3n,verde,Gris,rojo,rosado,blanco,azul,negro,gris profundo,ejercito verde","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"3XL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"},{"title":"5XL","table":"attribute_value"}]},{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"marr\u00f3n","table":"attribute_value"},{"title":"verde","table":"attribute_value"},{"title":"Gris","table":"attribute_value"},{"title":"rojo","table":"attribute_value"},{"title":"rosado","table":"attribute_value"},{"title":"blanco","table":"attribute_value"},{"title":"azul","table":"attribute_value"},{"title":"negro","table":"attribute_value"},{"title":"gris profundo","table":"attribute_value"},{"title":"ejercito verde","table":"attribute_value"}]}]}]';

        $response = static::request(config('erp.url_product_add'), ['data' => $dataItem]);

        return $response;
    }

    /**
     * 更新产品
     * @return mixed
     */
    public static function updateProduct($productData) {
        var_dump('===============更新产品==================');
        $siteData = Site::find($productData['site_id']);
        $dataItem = [
            "erpProductId" => $productData['erp_id'], //erp产品id
            "creator" => $productData['user_name'], //创建人
            "refenceId" => $productData['provider_id'], //参考id,app产品信息的id
            "productSourceEnum" => "api", //产品来源类型:api,batch,app对接使用api
            "title" => $productData['title'], //产品标题
            "categoryId" => $productData['category_id'], //产品所属品类ID
            "classifyEnum" => "S", //产品特性，Y:特货,S普货
            "classifyEnumName" => "普货",
            "innerName" => $productData['title'], //产品内部名
            "mainImageUrl" => $productData['thumb'], //产品主图地址
            "sourceEnum" => "other", //产品来源:market,taobao,alibaba,other
            "sourceEnumName" => $productData['provider'],
            "sourceUrl" => 'https://detail.1688.com/offer/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD', //产品来源地址
            "memo" => $productData['provider'] . "产品导入", //备注
            "checker" => "系统", //审核人
            "customEnum" => "normal", //产品自定义类别：normal(标品)、custome(定制)
            "customEnumName" => "标品",
            "productZoneList" => [//产品区域集合:需要区域ID(zoneId)和部门ID(departmentId),参考示例
                [
                    "departmentId" => $productData['department_id'], //部门ID
                    "zoneId" => static::getZoneId($siteData->lang), //区域ID
                    "table" => "product_zone",
                ]
            ],
            "table" => "product",
        ];
        var_dump($dataItem);

        $dataItem = json_encode($dataItem);

        //$data = '[{"creator":"chenfengcai","refenceId":24358,"productSourceEnum":"api","title":"2018 Mujeres Abrigo de Invierno Nuevo Ultra Ligero 90% Pato Blanco Abajo","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"2018 Mujeres Abrigo de Invierno Nuevo Ultra Ligero 90% Pato Blanco Abajo","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-803101825_1024x1024_b83a555f-b438-4085-a7e1-c56db8421b99.jpg?v=1539594741","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Rosa,rojo,lago azul,verde oscuro,negro,beige,vino tinto,Verde fluorescente,azul marino,Rosa roja,Azul claro,naranja,p\u00farpura,blanco;Size:Rosa,rojo,lago azul,verde oscuro,negro,beige,vino tinto,Verde fluorescente,azul marino,Rosa roja,Azul claro,naranja,p\u00farpura,blanco,S,XL,L,M,XXL,XXXL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Rosa","table":"attribute_value"},{"title":"rojo","table":"attribute_value"},{"title":"lago azul","table":"attribute_value"},{"title":"verde oscuro","table":"attribute_value"},{"title":"negro","table":"attribute_value"},{"title":"beige","table":"attribute_value"},{"title":"vino tinto","table":"attribute_value"},{"title":"Verde fluorescente","table":"attribute_value"},{"title":"azul marino","table":"attribute_value"},{"title":"Rosa roja","table":"attribute_value"},{"title":"Azul claro","table":"attribute_value"},{"title":"naranja","table":"attribute_value"},{"title":"p\u00farpura","table":"attribute_value"},{"title":"blanco","table":"attribute_value"}]},{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24359,"productSourceEnum":"api","title":"2018 nueva chaqueta de invierno de las mujeres","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"2018 nueva chaqueta de invierno de las mujeres","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-736643097_cc866580-7a70-409b-a6d3-92479fe91335.jpg?v=1539249424","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Negro,Verde,Caqui,Rojo,Azul oscuro,Naranja,Blanco,Vino rojo;Tama\u00f1o:Negro,Verde,Caqui,Rojo,Azul oscuro,Naranja,Blanco,Vino rojo,S,M,L,XL,XXL,XXXL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Negro","table":"attribute_value"},{"title":"Verde","table":"attribute_value"},{"title":"Caqui","table":"attribute_value"},{"title":"Rojo","table":"attribute_value"},{"title":"Azul oscuro","table":"attribute_value"},{"title":"Naranja","table":"attribute_value"},{"title":"Blanco","table":"attribute_value"},{"title":"Vino rojo","table":"attribute_value"}]},{"title":"Tama\u00f1o","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24360,"productSourceEnum":"api","title":"Abrigo y sudadera con capucha irregular de manga larga con cremallera","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Abrigo y sudadera con capucha irregular de manga larga con cremallera","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/20180927_200621_028.jpg?v=1538050553","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Size:S,M,L,XL,XXL,3XL,4XL,5XL;Color:S,M,L,XL,XXL,3XL,4XL,5XL,Negro,gris","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"3XL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"},{"title":"5XL","table":"attribute_value"}]},{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Negro","table":"attribute_value"},{"title":"gris","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24361,"productSourceEnum":"api","title":"Abrigos con capucha y cremallera Casual Denim","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Abrigos con capucha y cremallera Casual Denim","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/20180926_171914_004.jpg?v=1539588158","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Size:M,L,XL,XXL,3XL,4XL;Color:M,L,XL,XXL,3XL,4XL,azul claro,azul profundo","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"3XL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"}]},{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"azul claro","table":"attribute_value"},{"title":"azul profundo","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24362,"productSourceEnum":"api","title":"Chaleco de pato abajo","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Chaleco de pato abajo","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-737384232_0f35ba05-8e7f-4acc-aa93-fc9d8113dbe2.jpg?v=1539252468","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Negro,Azul marino,Lago azul,Verde,Caqui,Naranja,Rosado morado,P\u00farpura,Rojo,Rosa roja,Blanco,Vino rojo;Tama\u00f1o:Negro,Azul marino,Lago azul,Verde,Caqui,Naranja,Rosado morado,P\u00farpura,Rojo,Rosa roja,Blanco,Vino rojo,S,M,L,XL,XXL,XXXL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Negro","table":"attribute_value"},{"title":"Azul marino","table":"attribute_value"},{"title":"Lago azul","table":"attribute_value"},{"title":"Verde","table":"attribute_value"},{"title":"Caqui","table":"attribute_value"},{"title":"Naranja","table":"attribute_value"},{"title":"Rosado morado","table":"attribute_value"},{"title":"P\u00farpura","table":"attribute_value"},{"title":"Rojo","table":"attribute_value"},{"title":"Rosa roja","table":"attribute_value"},{"title":"Blanco","table":"attribute_value"},{"title":"Vino rojo","table":"attribute_value"}]},{"title":"Tama\u00f1o","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24363,"productSourceEnum":"api","title":"Chaqueta de delgada zipper","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Chaqueta de delgada zipper","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-817456033.jpg?v=1539415585","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Negro,Rodas,rojo,ejercito verde,champ\u00e1n,borgo\u00f1a;Size:Negro,Rodas,rojo,ejercito verde,champ\u00e1n,borgo\u00f1a,S,M,L,XL,XXL,XXXL,4XL,5XL,6XL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Negro","table":"attribute_value"},{"title":"Rodas","table":"attribute_value"},{"title":"rojo","table":"attribute_value"},{"title":"ejercito verde","table":"attribute_value"},{"title":"champ\u00e1n","table":"attribute_value"},{"title":"borgo\u00f1a","table":"attribute_value"}]},{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"},{"title":"5XL","table":"attribute_value"},{"title":"6XL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24364,"productSourceEnum":"api","title":"Chaqueta de manga larga con cuello redondo y manga larga","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Chaqueta de manga larga con cuello redondo y manga larga","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/231259_20_2818_29.jpg?v=1537963608","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Size:S,M,L,XL,XXL,3XL,4XL,5XL;Color:S,M,L,XL,XXL,3XL,4XL,5XL,azul,gris claro,ejercito verde,rojo,borgo\u00f1a,gris oscuro,caf\u00e9,verde,negro,rosado","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"3XL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"},{"title":"5XL","table":"attribute_value"}]},{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"azul","table":"attribute_value"},{"title":"gris claro","table":"attribute_value"},{"title":"ejercito verde","table":"attribute_value"},{"title":"rojo","table":"attribute_value"},{"title":"borgo\u00f1a","table":"attribute_value"},{"title":"gris oscuro","table":"attribute_value"},{"title":"caf\u00e9","table":"attribute_value"},{"title":"verde","table":"attribute_value"},{"title":"negro","table":"attribute_value"},{"title":"rosado","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24365,"productSourceEnum":"api","title":"Chaqueta larga de parka","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Chaqueta larga de parka","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-550975840.jpg?v=1539331046","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Grey,Green,Black,Red,Pink,White,Army green;Size:Grey,Green,Black,Red,Pink,White,Army green,XL,L,M,XXL,XXXL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Grey","table":"attribute_value"},{"title":"Green","table":"attribute_value"},{"title":"Black","table":"attribute_value"},{"title":"Red","table":"attribute_value"},{"title":"Pink","table":"attribute_value"},{"title":"White","table":"attribute_value"},{"title":"Army green","table":"attribute_value"}]},{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"XL","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24366,"productSourceEnum":"api","title":"Nueva 2018 chaqueta larga con capucha ultraligera para mujer","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Nueva 2018 chaqueta larga con capucha ultraligera para mujer","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-818244458_740x_2a13bb1e-b93c-43a3-b889-c24801325376.jpg?v=1539597666","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:rojo,Negro,Caqui,borgo\u00f1a,ejercito verde,Azul marino,Rhodo,P\u00farpura;Tama\u00f1o:rojo,Negro,Caqui,borgo\u00f1a,ejercito verde,Azul marino,Rhodo,P\u00farpura,S,XL,L,M,XXL,XXXL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"rojo","table":"attribute_value"},{"title":"Negro","table":"attribute_value"},{"title":"Caqui","table":"attribute_value"},{"title":"borgo\u00f1a","table":"attribute_value"},{"title":"ejercito verde","table":"attribute_value"},{"title":"Azul marino","table":"attribute_value"},{"title":"Rhodo","table":"attribute_value"},{"title":"P\u00farpura","table":"attribute_value"}]},{"title":"Tama\u00f1o","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24367,"productSourceEnum":"api","title":"Pato blanco abrigos de plumas","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Pato blanco abrigos de plumas","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-424562484.jpg?v=1539326145","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Negro,Armada,Caqui oscuro,Fucsia,P\u00farpura,Rojo,Vino,Ejercito verde;Tama\u00f1o:Negro,Armada,Caqui oscuro,Fucsia,P\u00farpura,Rojo,Vino,Ejercito verde,S,M,L,XL,XXL,XXXL,4XL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Negro","table":"attribute_value"},{"title":"Armada","table":"attribute_value"},{"title":"Caqui oscuro","table":"attribute_value"},{"title":"Fucsia","table":"attribute_value"},{"title":"P\u00farpura","table":"attribute_value"},{"title":"Rojo","table":"attribute_value"},{"title":"Vino","table":"attribute_value"},{"title":"Ejercito verde","table":"attribute_value"}]},{"title":"Tama\u00f1o","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24368,"productSourceEnum":"api","title":"S ~ 6XL Oto\u00f1o Invierno Mujeres  Down Chaqueta","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"S ~ 6XL Oto\u00f1o Invierno Mujeres  Down Chaqueta","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-792299424.jpg?v=1540179674","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Rojo oscuro,Azul,Negro,champ\u00e1n,borgo\u00f1a,Azul marino,Rodas,Verde amarillento;Size:Rojo oscuro,Azul,Negro,champ\u00e1n,borgo\u00f1a,Azul marino,Rodas,Verde amarillento,S,XL,4XL,5XL,6XL,L,M,XXL,XXXL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Rojo oscuro","table":"attribute_value"},{"title":"Azul","table":"attribute_value"},{"title":"Negro","table":"attribute_value"},{"title":"champ\u00e1n","table":"attribute_value"},{"title":"borgo\u00f1a","table":"attribute_value"},{"title":"Azul marino","table":"attribute_value"},{"title":"Rodas","table":"attribute_value"},{"title":"Verde amarillento","table":"attribute_value"}]},{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"},{"title":"5XL","table":"attribute_value"},{"title":"6XL","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24369,"productSourceEnum":"api","title":"Sudaderas con capucha y sudaderas de manga larga en color liso oto\u00f1o invierno polar","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Sudaderas con capucha y sudaderas de manga larga en color liso oto\u00f1o invierno polar","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/1_2000x2000_4fc03904-0160-4d4e-a29d-4ad4d08e74d7.jpg?v=1538030407","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Size:S,M,L,XL,XXL,3XL,4XL,5XL;Color:S,M,L,XL,XXL,3XL,4XL,5XL,marr\u00f3n,verde,Gris,rojo,rosado,blanco,azul,negro,gris profundo,ejercito verde","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"3XL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"},{"title":"5XL","table":"attribute_value"}]},{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"marr\u00f3n","table":"attribute_value"},{"title":"verde","table":"attribute_value"},{"title":"Gris","table":"attribute_value"},{"title":"rojo","table":"attribute_value"},{"title":"rosado","table":"attribute_value"},{"title":"blanco","table":"attribute_value"},{"title":"azul","table":"attribute_value"},{"title":"negro","table":"attribute_value"},{"title":"gris profundo","table":"attribute_value"},{"title":"ejercito verde","table":"attribute_value"}]}]}]';

        $response = static::request(config('erp.url_product_update'), ['data' => $dataItem]);

        return $response;
    }

    /**
     * 添加属性组
     * @return mixed
     */
    public static function addProductType($productTypeMapItem) {
        var_dump('===============添加属性组==================');
        var_dump($productTypeMapItem);
        $dataItem = [
            'erpAttributeId' => 0, //erp属性组id
            'refenceId' => $productTypeMapItem['id'], //参考id,app产品信息的id
            'title' => $productTypeMapItem['name'],
        ];

        var_dump($dataItem);

//        $dataItem = json_encode($dataItem);
//        $response = static::request(config('erp.url_product_type_add'), ['data' => $dataItem]);
        //return $response;

        return ['id' => 2];
    }

    /**
     * 更新属性组
     * @return mixed
     */
    public static function updateProductType($productTypeMapItem) {
        var_dump('===============更新属性组==================');
        var_dump($productTypeMapItem);
        $dataItem = [
            'erpAttributeId' => $productTypeMapItem['erp_id'], //erp属性组id
            'refenceId' => $productTypeMapItem['id'], //参考id,app产品信息的id
            'title' => $productTypeMapItem['name'],
        ];
//        $dataItem = json_encode($dataItem);
//        $response = static::request(config('erp.url_product_type_update'), ['data' => $dataItem]);
        //return $response;

        var_dump($dataItem);

        return ['id' => 2];
    }

    /**
     * 添加属性
     * @return mixed
     */
    public static function addProductAttr($productAttrMapItem) {
        var_dump('===============添加属性==================');
        var_dump($productAttrMapItem);

        $dataItem = [
            'erpProductId' => $productAttrMapItem['erpProductId'], //erp产品id
            'erpAttributeId' => $productAttrMapItem['erpProductId'], //erp属性id
            'refenceId' => $productAttrMapItem['id'], //参考id,app产品信息的id
            'title' => $productAttrMapItem['name'],
        ];
        var_dump($dataItem);
//        $dataItem = json_encode($dataItem);
//        $response = static::request(config('erp.url_product_attr_add'), ['data' => $dataItem]);
        //return $response;

        return ['id' => 3];
    }

    /**
     * 更新属性
     * @return mixed
     */
    public static function updateProductAttr($productAttrMapItem) {
        var_dump('===============更新属性==================');
        var_dump($productAttrMapItem);

        $dataItem = [
            'erpProductId' => $productAttrMapItem['erpProductId'], //erp产品id
            'erpAttributeId' => $productAttrMapItem['erpAttributeId'], //erp属性id
            'erpAttributeValueId' => $productAttrMapItem['erp_id'], //erp属性值id
            'refenceId' => $productAttrMapItem['id'], //参考id,app产品信息的id
            'title' => $productAttrMapItem['name'],
        ];
        var_dump($dataItem);
//        $dataItem = json_encode($dataItem);
//        $response = static::request(config('erp.url_product_attr_update'), ['data' => $dataItem]);
        //return $response;

        return ['id' => 3];
    }

    /**
     * 批量导入产品sh
     * @return mixed
     */
    public static function batchInsert($productData) {

        foreach ($productData as $provider_id => $item) {

            $productMapItem = ProductMap::add($item);
            $productData[$provider_id]['erp_id'] = $productMapItem['erp_id'];

            $productTypeData = $item['product_types'];
            $productSkuData = $item['product_skus'];

            //添加属性组
            foreach ($productTypeData as $index => $typeItem) {

                /*                 * *************添加属性组************************* */
                $productTypeMapItem = ProductTypeMap::add($typeItem);
                $productData[$provider_id]['product_types'][$index]['erp_id'] = $productTypeMapItem['erp_id'];

                /*                 * *************添加属性************************* */
                $productAttrData = $typeItem['attrs'];
                foreach ($productAttrData as $key => $attrItem) {
                    $attrItem['erpProductId'] = $productData[$provider_id]['erp_id']; //erp产品id
                    $attrItem['erpAttributeId'] = $productData[$provider_id]['product_types'][$index]['erp_id']; //erp属性组id
                    $attrItem['provider'] = $item['provider']; //提供商
                    $productAttrMapItem = ProductAttrMap::add($attrItem);
                    $productData[$provider_id]['product_types'][$index]['attrs'][$key]['erp_id'] = $productAttrMapItem['erp_id'];
                }
            }
        }

        return $productData;

//        $data = [];
//        foreach ($productData as $item) {
//            $dataItem = [
//                "creator" => $item['user_name'], //创建人
//                "refenceId" => $item['id'], //参考id,app产品信息的id
//                "productSourceEnum" => "api", //产品来源类型:api,batch,app对接使用api
//                "title" => $item['foreign_title'], //产品标题
//                "categoryId" => $item['category_id'], //产品所属品类ID
//                "classifyEnum" => "S", //产品特性，Y:特货,S普货
//                "classifyEnumName" => "普货",
//                "innerName" => $item['title'], //产品内部名
//                "mainImageUrl" => $item['thumb'], //产品主图地址
//                "sourceEnum" => "other", //产品来源:market,taobao,alibaba,other
//                "sourceEnumName" => $item['provider'],
//                "sourceUrl" => 'https://detail.1688.com/offer/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD', //产品来源地址
//                "memo" => $item['provider'] . "产品导入", //备注
//                "checker" => "系统", //审核人
//                "attributeDesc" => $item['attributeDesc'], //属性描述
//                "customEnum" => "normal", //产品自定义类别：normal(标品)、custome(定制)
//                "customEnumName" => "标品",
//                "productZoneList" => [//产品区域集合:需要区域ID(zoneId)和部门ID(departmentId),参考示例
//                    [
//                        "departmentId" => $item['department_id'], //部门ID
//                        "zoneId" => 2, //区域ID
//                        "table" => "product_zone",
//                    ]
//                ],
//                "table" => "product",
//            ];
//
//            /*             * *************属性集合************************* */
//            $attributeList = [];
//            $productTypeData = $item['product_types'];
//            foreach ($productTypeData as $typeItem) {
//
//                /*                 * *************属性组数据************************* */
//                $attributeItem = [
//                    "title" => $typeItem['name'],
//                    "version" => 1,
//                    "table" => "attribute",
//                ];
//
//                /*                 * *************属性数据************************* */
//                $attributeValueList = [];
//                $productAttrData = $typeItem['attrs'];
//                foreach ($productAttrData as $attrItem) {
//                    $attributeValueList[] = [
//                        "title" => $attrItem['name'],
//                        "table" => "attribute_value"
//                    ];
//                }
//                $attributeItem['attributeValueList'] = $attributeValueList;
//
//                $attributeList[] = $attributeItem;
//            }
//
//            $dataItem['attributeList'] = $attributeList;
//
//            $data[] = $dataItem;
//        }
//        $data = json_encode($data);
//
//        //$data = '[{"creator":"chenfengcai","refenceId":24358,"productSourceEnum":"api","title":"2018 Mujeres Abrigo de Invierno Nuevo Ultra Ligero 90% Pato Blanco Abajo","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"2018 Mujeres Abrigo de Invierno Nuevo Ultra Ligero 90% Pato Blanco Abajo","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-803101825_1024x1024_b83a555f-b438-4085-a7e1-c56db8421b99.jpg?v=1539594741","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Rosa,rojo,lago azul,verde oscuro,negro,beige,vino tinto,Verde fluorescente,azul marino,Rosa roja,Azul claro,naranja,p\u00farpura,blanco;Size:Rosa,rojo,lago azul,verde oscuro,negro,beige,vino tinto,Verde fluorescente,azul marino,Rosa roja,Azul claro,naranja,p\u00farpura,blanco,S,XL,L,M,XXL,XXXL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Rosa","table":"attribute_value"},{"title":"rojo","table":"attribute_value"},{"title":"lago azul","table":"attribute_value"},{"title":"verde oscuro","table":"attribute_value"},{"title":"negro","table":"attribute_value"},{"title":"beige","table":"attribute_value"},{"title":"vino tinto","table":"attribute_value"},{"title":"Verde fluorescente","table":"attribute_value"},{"title":"azul marino","table":"attribute_value"},{"title":"Rosa roja","table":"attribute_value"},{"title":"Azul claro","table":"attribute_value"},{"title":"naranja","table":"attribute_value"},{"title":"p\u00farpura","table":"attribute_value"},{"title":"blanco","table":"attribute_value"}]},{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24359,"productSourceEnum":"api","title":"2018 nueva chaqueta de invierno de las mujeres","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"2018 nueva chaqueta de invierno de las mujeres","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-736643097_cc866580-7a70-409b-a6d3-92479fe91335.jpg?v=1539249424","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Negro,Verde,Caqui,Rojo,Azul oscuro,Naranja,Blanco,Vino rojo;Tama\u00f1o:Negro,Verde,Caqui,Rojo,Azul oscuro,Naranja,Blanco,Vino rojo,S,M,L,XL,XXL,XXXL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Negro","table":"attribute_value"},{"title":"Verde","table":"attribute_value"},{"title":"Caqui","table":"attribute_value"},{"title":"Rojo","table":"attribute_value"},{"title":"Azul oscuro","table":"attribute_value"},{"title":"Naranja","table":"attribute_value"},{"title":"Blanco","table":"attribute_value"},{"title":"Vino rojo","table":"attribute_value"}]},{"title":"Tama\u00f1o","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24360,"productSourceEnum":"api","title":"Abrigo y sudadera con capucha irregular de manga larga con cremallera","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Abrigo y sudadera con capucha irregular de manga larga con cremallera","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/20180927_200621_028.jpg?v=1538050553","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Size:S,M,L,XL,XXL,3XL,4XL,5XL;Color:S,M,L,XL,XXL,3XL,4XL,5XL,Negro,gris","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"3XL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"},{"title":"5XL","table":"attribute_value"}]},{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Negro","table":"attribute_value"},{"title":"gris","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24361,"productSourceEnum":"api","title":"Abrigos con capucha y cremallera Casual Denim","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Abrigos con capucha y cremallera Casual Denim","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/20180926_171914_004.jpg?v=1539588158","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Size:M,L,XL,XXL,3XL,4XL;Color:M,L,XL,XXL,3XL,4XL,azul claro,azul profundo","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"3XL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"}]},{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"azul claro","table":"attribute_value"},{"title":"azul profundo","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24362,"productSourceEnum":"api","title":"Chaleco de pato abajo","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Chaleco de pato abajo","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-737384232_0f35ba05-8e7f-4acc-aa93-fc9d8113dbe2.jpg?v=1539252468","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Negro,Azul marino,Lago azul,Verde,Caqui,Naranja,Rosado morado,P\u00farpura,Rojo,Rosa roja,Blanco,Vino rojo;Tama\u00f1o:Negro,Azul marino,Lago azul,Verde,Caqui,Naranja,Rosado morado,P\u00farpura,Rojo,Rosa roja,Blanco,Vino rojo,S,M,L,XL,XXL,XXXL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Negro","table":"attribute_value"},{"title":"Azul marino","table":"attribute_value"},{"title":"Lago azul","table":"attribute_value"},{"title":"Verde","table":"attribute_value"},{"title":"Caqui","table":"attribute_value"},{"title":"Naranja","table":"attribute_value"},{"title":"Rosado morado","table":"attribute_value"},{"title":"P\u00farpura","table":"attribute_value"},{"title":"Rojo","table":"attribute_value"},{"title":"Rosa roja","table":"attribute_value"},{"title":"Blanco","table":"attribute_value"},{"title":"Vino rojo","table":"attribute_value"}]},{"title":"Tama\u00f1o","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24363,"productSourceEnum":"api","title":"Chaqueta de delgada zipper","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Chaqueta de delgada zipper","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-817456033.jpg?v=1539415585","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Negro,Rodas,rojo,ejercito verde,champ\u00e1n,borgo\u00f1a;Size:Negro,Rodas,rojo,ejercito verde,champ\u00e1n,borgo\u00f1a,S,M,L,XL,XXL,XXXL,4XL,5XL,6XL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Negro","table":"attribute_value"},{"title":"Rodas","table":"attribute_value"},{"title":"rojo","table":"attribute_value"},{"title":"ejercito verde","table":"attribute_value"},{"title":"champ\u00e1n","table":"attribute_value"},{"title":"borgo\u00f1a","table":"attribute_value"}]},{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"},{"title":"5XL","table":"attribute_value"},{"title":"6XL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24364,"productSourceEnum":"api","title":"Chaqueta de manga larga con cuello redondo y manga larga","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Chaqueta de manga larga con cuello redondo y manga larga","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/231259_20_2818_29.jpg?v=1537963608","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Size:S,M,L,XL,XXL,3XL,4XL,5XL;Color:S,M,L,XL,XXL,3XL,4XL,5XL,azul,gris claro,ejercito verde,rojo,borgo\u00f1a,gris oscuro,caf\u00e9,verde,negro,rosado","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"3XL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"},{"title":"5XL","table":"attribute_value"}]},{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"azul","table":"attribute_value"},{"title":"gris claro","table":"attribute_value"},{"title":"ejercito verde","table":"attribute_value"},{"title":"rojo","table":"attribute_value"},{"title":"borgo\u00f1a","table":"attribute_value"},{"title":"gris oscuro","table":"attribute_value"},{"title":"caf\u00e9","table":"attribute_value"},{"title":"verde","table":"attribute_value"},{"title":"negro","table":"attribute_value"},{"title":"rosado","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24365,"productSourceEnum":"api","title":"Chaqueta larga de parka","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Chaqueta larga de parka","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-550975840.jpg?v=1539331046","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Grey,Green,Black,Red,Pink,White,Army green;Size:Grey,Green,Black,Red,Pink,White,Army green,XL,L,M,XXL,XXXL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Grey","table":"attribute_value"},{"title":"Green","table":"attribute_value"},{"title":"Black","table":"attribute_value"},{"title":"Red","table":"attribute_value"},{"title":"Pink","table":"attribute_value"},{"title":"White","table":"attribute_value"},{"title":"Army green","table":"attribute_value"}]},{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"XL","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24366,"productSourceEnum":"api","title":"Nueva 2018 chaqueta larga con capucha ultraligera para mujer","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Nueva 2018 chaqueta larga con capucha ultraligera para mujer","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-818244458_740x_2a13bb1e-b93c-43a3-b889-c24801325376.jpg?v=1539597666","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:rojo,Negro,Caqui,borgo\u00f1a,ejercito verde,Azul marino,Rhodo,P\u00farpura;Tama\u00f1o:rojo,Negro,Caqui,borgo\u00f1a,ejercito verde,Azul marino,Rhodo,P\u00farpura,S,XL,L,M,XXL,XXXL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"rojo","table":"attribute_value"},{"title":"Negro","table":"attribute_value"},{"title":"Caqui","table":"attribute_value"},{"title":"borgo\u00f1a","table":"attribute_value"},{"title":"ejercito verde","table":"attribute_value"},{"title":"Azul marino","table":"attribute_value"},{"title":"Rhodo","table":"attribute_value"},{"title":"P\u00farpura","table":"attribute_value"}]},{"title":"Tama\u00f1o","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24367,"productSourceEnum":"api","title":"Pato blanco abrigos de plumas","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Pato blanco abrigos de plumas","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-424562484.jpg?v=1539326145","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Negro,Armada,Caqui oscuro,Fucsia,P\u00farpura,Rojo,Vino,Ejercito verde;Tama\u00f1o:Negro,Armada,Caqui oscuro,Fucsia,P\u00farpura,Rojo,Vino,Ejercito verde,S,M,L,XL,XXL,XXXL,4XL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Negro","table":"attribute_value"},{"title":"Armada","table":"attribute_value"},{"title":"Caqui oscuro","table":"attribute_value"},{"title":"Fucsia","table":"attribute_value"},{"title":"P\u00farpura","table":"attribute_value"},{"title":"Rojo","table":"attribute_value"},{"title":"Vino","table":"attribute_value"},{"title":"Ejercito verde","table":"attribute_value"}]},{"title":"Tama\u00f1o","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24368,"productSourceEnum":"api","title":"S ~ 6XL Oto\u00f1o Invierno Mujeres  Down Chaqueta","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"S ~ 6XL Oto\u00f1o Invierno Mujeres  Down Chaqueta","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/product-image-792299424.jpg?v=1540179674","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Color:Rojo oscuro,Azul,Negro,champ\u00e1n,borgo\u00f1a,Azul marino,Rodas,Verde amarillento;Size:Rojo oscuro,Azul,Negro,champ\u00e1n,borgo\u00f1a,Azul marino,Rodas,Verde amarillento,S,XL,4XL,5XL,6XL,L,M,XXL,XXXL","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"Rojo oscuro","table":"attribute_value"},{"title":"Azul","table":"attribute_value"},{"title":"Negro","table":"attribute_value"},{"title":"champ\u00e1n","table":"attribute_value"},{"title":"borgo\u00f1a","table":"attribute_value"},{"title":"Azul marino","table":"attribute_value"},{"title":"Rodas","table":"attribute_value"},{"title":"Verde amarillento","table":"attribute_value"}]},{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"},{"title":"5XL","table":"attribute_value"},{"title":"6XL","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"XXXL","table":"attribute_value"}]}]},{"creator":"chenfengcai","refenceId":24369,"productSourceEnum":"api","title":"Sudaderas con capucha y sudaderas de manga larga en color liso oto\u00f1o invierno polar","categoryId":1508,"classifyEnum":"S","classifyEnumName":"\u666e\u8d27","innerName":"Sudaderas con capucha y sudaderas de manga larga en color liso oto\u00f1o invierno polar","mainImageUrl":"https:\/\/cdn.shopify.com\/s\/files\/1\/0073\/3213\/5030\/products\/1_2000x2000_4fc03904-0160-4d4e-a29d-4ad4d08e74d7.jpg?v=1538030407","sourceEnum":"other","sourceEnumName":"shopify","sourceUrl":"https:\/\/detail.1688.com\/offer\/573949431779.html?spm=b26110380.sw1688.mof001.94.727e346bGfyesD","memo":"shopify\u4ea7\u54c1\u5bfc\u5165","checker":"\u7cfb\u7edf","attributeDesc":"Size:S,M,L,XL,XXL,3XL,4XL,5XL;Color:S,M,L,XL,XXL,3XL,4XL,5XL,marr\u00f3n,verde,Gris,rojo,rosado,blanco,azul,negro,gris profundo,ejercito verde","customEnum":"normal","customEnumName":"\u6807\u54c1","productZoneList":[{"departmentId":1,"zoneId":2,"table":"product_zone"}],"table":"product","attributeList":[{"title":"Size","version":1,"table":"attribute","attributeValueList":[{"title":"S","table":"attribute_value"},{"title":"M","table":"attribute_value"},{"title":"L","table":"attribute_value"},{"title":"XL","table":"attribute_value"},{"title":"XXL","table":"attribute_value"},{"title":"3XL","table":"attribute_value"},{"title":"4XL","table":"attribute_value"},{"title":"5XL","table":"attribute_value"}]},{"title":"Color","version":1,"table":"attribute","attributeValueList":[{"title":"marr\u00f3n","table":"attribute_value"},{"title":"verde","table":"attribute_value"},{"title":"Gris","table":"attribute_value"},{"title":"rojo","table":"attribute_value"},{"title":"rosado","table":"attribute_value"},{"title":"blanco","table":"attribute_value"},{"title":"azul","table":"attribute_value"},{"title":"negro","table":"attribute_value"},{"title":"gris profundo","table":"attribute_value"},{"title":"ejercito verde","table":"attribute_value"}]}]}]';
//
//        $response = static::request(config('erp.url_product_batchInsert'), ['productInfo' => $data]);
//
//        return $response;
    }

    /**
     * 获取分类数据
     * @return mixed
     */
    public static function getCategoryTree() {

        $response = static::request(config('erp.url_category_tree'));

        return $response;
    }

}
