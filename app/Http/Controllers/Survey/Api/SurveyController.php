<?php

namespace App\Http\Controllers\Survey\Api;

use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\Survey\SurveyService;
use App\Services\Survey\SurveyResultService;

class SurveyController extends Controller {

    /**
     * 获取详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request) {

        $surveyId = $request->input('id', 0); //问券id

        $data = SurveyService::getItemData($this->storeId, $surveyId);

        return Response::json($data);
    }

    /**
     * 获取详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request) {

        $surveyId = $request->input('id', 0); //问券id
        $data = SurveyResultService::handle($this->storeId, $this->actId, $surveyId, $this->ip, $request->all());

        return Response::json(data_get($data, 'data', ''),data_get($data, 'code', 0), data_get($data, 'msg', ''));
    }

}
