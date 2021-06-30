<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Api\Controller;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Services\CountryService;
use App\Util\Response;
use App\Util\Constant;

class CountryController extends Controller {

    /**
     * 返回全部国家信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        $data = CountryService::country($request);
        return Response::json($data);
    }

    /**
     * 返回单个国家信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function oneCountry(Request $request) {
        $country = $request->input(Constant::DB_TABLE_COUNTRY_CODE, '');
        $data = CountryService::countryOne($country);
        return Response::json($data);
    }

    /**
     * 返回对应国家的region信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function region(Request $request) {
        $country = $request->input(Constant::DB_TABLE_COUNTRY_CODE, '');
        $data = Region::select(['region_code', 'region_name'])
                ->where([Constant::DB_TABLE_COUNTRY_CODE => $country])
                ->get();
        return Response::json($data);
    }

}
