<?php

namespace App\Util\Cdn;

use App\Services\DictStoreService;
use App\Util\Constant;

class AwsS3Cdn extends ResourcesCdn {

    /**
     * 获取云存储对象
     * @param array $extData
     * @return array  云存储对象
     */
    public static function getDisk($extData) {

        $rs = static::getDefaultResponseData(Constant::ORDER_STATUS_SHIPPED_INT, Constant::PARAMETER_STRING_DEFAULT);

        $storeId = data_get($extData, Constant::DB_TABLE_STORE_ID, 0);
        if (empty($storeId)) {
            data_set($rs, Constant::RESPONSE_CODE_KEY, 0);
            data_set($rs, Constant::RESPONSE_MSG_KEY, 'store ID 异常');
            return $rs;
        }

        //获取 disk 名称
        $diskName = DictStoreService::getByTypeAndKey($storeId, 'filesystems.disks', 'aws', true);
        if (empty($diskName)) {
            data_set($rs, Constant::RESPONSE_CODE_KEY, 2);
            data_set($rs, Constant::RESPONSE_MSG_KEY, $storeId . ' s3 配置不存在');
            return $rs;
        }

        //获取driver配置
        $diskConf = DictStoreService::getListByType($storeId, $diskName);
        if ($diskConf->isEmpty()) {
            data_set($rs, Constant::RESPONSE_CODE_KEY, 3);
            data_set($rs, Constant::RESPONSE_MSG_KEY, $storeId . ' s3 云存储配置不存在');
            return $rs;
        }

        //设置配置
        $config = app('config');
        if (!($config->has('filesystems.disks.' . $diskName))) {

            //获取默认配置
            $defaultDiskConf = config('filesystems.disks.s3');

            //更新配置
            foreach ($diskConf as $item) {
                $key = data_get($item, 'conf_key');
                $value = data_get($item, 'conf_value', data_get($defaultDiskConf, $key, ''));
                data_set($defaultDiskConf, $key, $value);
            }

            $config->set('filesystems.disks.' . $diskName, $defaultDiskConf);
        }

        //上传文件至aws s3
        $disk = \Illuminate\Support\Facades\Storage::disk($diskName);

        data_set($rs, Constant::RESPONSE_DATA_KEY, $disk);

        return $rs;
    }

    public static function uploadBase64File($file = null, $vitualPath = '', $is_del = false, $isCn = false, $fileName = '', $resourceType = 1, $extData = Constant::PARAMETER_ARRAY_DEFAULT) {

        $diskData = static::getDisk($extData);
        if (data_get($diskData, Constant::RESPONSE_CODE_KEY, 0) != 1) {
            return $diskData;
        }
        $disk = data_get($diskData, Constant::RESPONSE_DATA_KEY, null);

        $_data = [
            Constant::RESOURCE_TYPE => $resourceType, //资源类型 1:图片 2:视频 3:js 4:css 默认:1
        ];
        $rs = static::getDefaultResponseData(Constant::ORDER_STATUS_SHIPPED_INT, Constant::PARAMETER_STRING_DEFAULT, $_data);

        $fileExtension = '';
        if (strpos($file, 'data:image/png;base64') !== false) {
            $data = explode(',', $file); //data:image/png;base64,iVBORw0KGgoAAAANSUhEU
            $fileContents = base64_decode(end($data));

            $fileExtension = explode('/', $data[0]); //data:image/png;base64,
            unset($data);
            $fileExtension = explode(';', $fileExtension[1]);
            $fileExtension = '.' . $fileExtension[0];
        } else {
            $fileContents = base64_decode($file);
        }

        $_fileName = static::getUploadFileName($resourceType, $vitualPath, $fileExtension, $fileName);
        $isPut = $disk->put($_fileName, $fileContents, 'public');
        if (empty($isPut)) {
            data_set($rs, Constant::RESPONSE_CODE_KEY, 0);
            data_set($rs, Constant::RESPONSE_MSG_KEY, '文件上传失败');

            return $rs;
        }

        $url = $disk->url($_fileName);

        data_set($rs, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::FILE_URL, $url);
        data_set($rs, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::FILE_FULL_PATH, $url);
        data_set($rs, Constant::RESPONSE_CODE_KEY, Constant::ORDER_STATUS_SHIPPED_INT);
        data_set($rs, Constant::RESPONSE_MSG_KEY, Constant::PARAMETER_STRING_DEFAULT);

        return $rs;
    }

    /**
     * 上传文件
     * @param string $filePath 图片在服务器的绝对路径
     * @param string $resourceType 文件类型 1：图片 2：视频
     * @param string $vitualPath 云存储虚拟路径
     * @param boolean $is_del  是否删除原文件  false:否  true：是  默认:false
     * @param boolean $isCn    是否使用国内cdn  false:否  true：是  默认:false
     * @return array 上传结果
     */
    public static function upload($filePath, $files = null, $vitualPath = '', $is_del = false, $isCn = false, $fileName = '', $resourceType = 1, $extData = Constant::PARAMETER_ARRAY_DEFAULT) {

        $diskData = static::getDisk($extData);
        if (data_get($diskData, Constant::RESPONSE_CODE_KEY, 0) != 1) {
            return $diskData;
        }
        $disk = data_get($diskData, Constant::RESPONSE_DATA_KEY, null);

        $_data = [
            Constant::RESOURCE_TYPE => $resourceType, //资源类型 1:图片 2:视频 3:js 4:css 默认:1
        ];
        $rs = static::getDefaultResponseData(Constant::ORDER_STATUS_SHIPPED_INT, Constant::PARAMETER_STRING_DEFAULT, $_data);

        $files = is_array($files) ? $files : [$filePath => $files];
        $uploadData = [];
        if (is_array($files)) {
            $distVitualPath = static::getDistVitualPath($resourceType, $vitualPath);
            foreach ($files as $key => $file) {
                if (is_array($file)) {
                    data_set($uploadData, $key, static::upload(null, $file, $vitualPath, $is_del, $isCn, $fileName, $resourceType, $extData));
                    continue;
                }

                static::$file = $file;
                if (!($file instanceof \Illuminate\Http\UploadedFile)) {

                    static::$file = null;
                    $rs = static::uploadBase64File($file, $vitualPath, $is_del, $isCn, $fileName, $resourceType, $extData);

                    data_set($uploadData, $key, $rs);
                    continue;
                }

                if (!$file->isValid()) {
                    data_set($rs, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::FILE_URL, Constant::PARAMETER_STRING_DEFAULT);
                    data_set($rs, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::FILE_FULL_PATH, Constant::PARAMETER_STRING_DEFAULT);
                    data_set($rs, Constant::RESPONSE_CODE_KEY, 10031);
                    data_set($rs, Constant::RESPONSE_MSG_KEY, $file->getErrorMessage());
                    $uploadData[$key] = $rs;
                    continue;
                }

                if (data_get($extData, 'use_origin_name', Constant::PARAMETER_INT_DEFAULT)) {
                    $url = $disk->url($disk->putFileAs($distVitualPath, $file, $file->getClientOriginalName(), 'public'));
                } else {
                    $url = $disk->url($disk->putFile($distVitualPath, $file, 'public'));
                }

                data_set($rs, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::FILE_URL, $url);
                data_set($rs, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::FILE_FULL_PATH, $url);
                data_set($rs, Constant::RESPONSE_CODE_KEY, Constant::ORDER_STATUS_SHIPPED_INT);
                data_set($rs, Constant::RESPONSE_MSG_KEY, Constant::PARAMETER_STRING_DEFAULT);
                data_set($rs, Constant::RESPONSE_DATA_KEY . Constant::LINKER . 'originalName', $file->getClientOriginalName());

                data_set($uploadData, $key, $rs);
            }
        }

        return data_get($uploadData, $filePath, Constant::PARAMETER_ARRAY_DEFAULT);
    }

}
