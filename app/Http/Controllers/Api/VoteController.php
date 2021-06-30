<?php

namespace App\Http\Controllers\Api;

use App\Util\Constant;
use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\VoteService;
use App\Util\Cache\CacheManager as Cache;
use Illuminate\Support\Arr;
use App\Services\ActivityWinningService;
use App\Services\DictStoreService;

class VoteController extends Controller {

    /**
     * 选项列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {

        $data = VoteService::getItemData($this->storeId, $this->actId, $this->account, $this->page, $this->pageSize);

        return Response::json($data);
    }

    /**
     * 投票
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function vote(Request $request) {

        $voteItemId = $request->input('vote_item_id', 0); //选项id
        $score = 1;
        $data = VoteService::handle($this->storeId, $this->actId, $this->account, $voteItemId, $score, $request->all());

        return Response::json($data['data'], $data['code'], $data['msg']);
    }

    /**
     * 投票排行榜
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRankData(Request $request) {

        $data = VoteService::getRankData($this->storeId, $this->actId, $this->account, $this->page, $this->pageSize);

        return Response::json($data);
    }

    /**
     * 用户添加投票项
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addVote(Request $request) {
        $actId = $request->input($this->actIdKey, Constant::PARAMETER_INT_DEFAULT);
        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT);
        $account = $request->input($this->accoutKey, Constant::PARAMETER_STRING_DEFAULT);
        $customerId = $request->input($this->customerPrimaryKey, Constant::PARAMETER_INT_DEFAULT);
        $voteId = $request->input(Constant::DB_TABLE_VOTE_ID, Constant::PARAMETER_INT_DEFAULT);
        $voteItemId = $request->input(Constant::DB_TABLE_VOTE_ITEM_ID, Constant::PARAMETER_INT_DEFAULT);
        $remarks = $request->input(Constant::DB_TABLE_REMARKS, Constant::PARAMETER_STRING_DEFAULT);
        $isVideo = $request->input('is_video', Constant::PARAMETER_INT_DEFAULT);
        $url = $request->input(Constant::FILE_URL, Constant::PARAMETER_STRING_DEFAULT);
        if ($isVideo) {
            if (empty($storeId) || empty($actId) || empty($account) || empty($customerId) || empty($remarks) || empty($url)) {
                //参数错误
                return Response::json([], 9999999999, Constant::PARAMETER_STRING_DEFAULT);
            }
        } else {
            if (empty($storeId) || empty($actId) || empty($account) || empty($customerId) || empty($voteId) || empty($voteItemId) || empty($remarks)) {
                //参数错误
                return Response::json([], 9999999999, Constant::PARAMETER_STRING_DEFAULT);
            }
        }

        //判断当前时间能否添加投票项
        if (!VoteService::isWithinTime($storeId, $actId, 'submit_picture')) {
            return Response::json([], 6111500000, Constant::PARAMETER_STRING_DEFAULT);
        }

        if ($isVideo) {
            //视频投票项添加
            $videoUrl = VoteService::getVideoUrl($url);
            if (empty($videoUrl)) {
                return Response::json([], 9999999999, Constant::PARAMETER_STRING_DEFAULT);
            }
            $request->offsetSet('video_url', $videoUrl);
            $data = VoteService::addVideoVote($storeId, $actId, $customerId, $account, $request->all());
        } else {
            //投票项添加
            $data = VoteService::addVote($storeId, $actId, $customerId, $account, $request->all());
        }

        return Response::json(...Response::getResponseData($data));
    }

    /**
     * 参赛图片列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function voteList(Request $request) {

        $data = VoteService::voteList($this->storeId, $this->actId, $this->account, $this->page, $this->pageSize, $request->all());

        return Response::json($data);
    }

    /**
     * 点赞
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function doLike(Request $request) {
        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT);
        $actId = $request->input($this->actIdKey, Constant::PARAMETER_INT_DEFAULT);
        $ip = $request->input(Constant::DB_TABLE_IP, Constant::PARAMETER_STRING_DEFAULT);
        $voteId = $request->input(Constant::DB_TABLE_VOTE_ID, Constant::PARAMETER_INT_DEFAULT);
        $voteItemId = $request->input(Constant::DB_TABLE_VOTE_ITEM_ID, Constant::PARAMETER_INT_DEFAULT);
        $requestData = $request->all();
        if (empty($storeId) || empty($actId) || empty($ip) || empty($voteId) || empty($voteItemId)) {
            //参数错误
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData(9999999999)));
        }

        //判断当前时间能否点赞
        if (!VoteService::isWithinTime($storeId, $actId, 'vote')) {
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData(61118)));
        }

        $data = Cache::lock('like:' . $storeId . ':' . $actId . ':' . $ip)->get(function () use($storeId, $actId, $ip, $voteId, $voteItemId, $requestData) {
            return VoteService::userVote($storeId, $actId, $ip, $voteId, $voteItemId, $requestData);
        });

        return Response::json(...Response::getResponseData($data));
    }

    /**
     * 用户投票项图片上传
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(Request $request) {
        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT);
        $actId = $request->input($this->actIdKey, Constant::PARAMETER_INT_DEFAULT);
        $account = $request->input($this->accoutKey, Constant::PARAMETER_STRING_DEFAULT);
        $customerId = $request->input($this->customerPrimaryKey, Constant::PARAMETER_INT_DEFAULT);
        $files = $request->file('file');
        $isOriginImage = $request->input('is_origin_image', Constant::PARAMETER_INT_DEFAULT); //是否原图
        $uniqueStr = $request->input('unique_str', Constant::PARAMETER_STRING_DEFAULT);
        $requestData = $request->all();
        if (empty($storeId) || empty($actId) || empty($account) || empty($customerId) || empty($files) || empty($isOriginImage) || empty($uniqueStr)) {
            //参数错误
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData(9999999999)));
        }

        //判断当前时间能否上传图片
        if (!VoteService::isWithinTime($storeId, $actId, 'submit_picture')) {
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData(61118)));
        }

        //文件是否合法
        if (!VoteService::fileCheck($files)) {
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData(61115)));
        }

        //文件上传
        $data = VoteService::uploadVote($storeId, $actId, $customerId, $account, $uniqueStr, $requestData);

        return Response::json(...Response::getResponseData($data));
    }

    /**
     * 用户添加的投票内容
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function voteInfo(Request $request) {
        $data = VoteService::voteInfo($this->storeId, $this->actId, $this->customerId);

        return Response::json($data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivity(Request $request) {
        $data = VoteService::getActivity($this->storeId, $request->all());

        return Response::json($data);
    }

}
