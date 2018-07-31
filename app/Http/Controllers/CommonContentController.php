<?php

namespace App\Http\Controllers;

use App;
use App\Http\Service\ClickService;
use App\Http\Service\AuthService;
use App\Http\Service\FilterService;
use App\CommonContent;
use Config;
use Illuminate\Http\Request;

class CommonContentController extends Controller
{
    /** @var ClickService $clickService */
    protected $clickService;
    /** @var  AuthService $authService */
    protected $authService;
    /** @var  FilterService $filterService */
    protected $filterService;

    public function __construct()
    {
        $this->clickService = App::make('ClickService');
        $this->filterService = App::make('FilterService');
        $this->authService = App::make('AuthService');
    }

    private function updateCommonContent($type, $request)
    {
        $commonContent = CommonContent::where('type', $type)->first();
        $commonContent->html_content = $request->html_content;

        $commonContent->save();

        return Config::get('constant.success.update');
    }

    public function updateRule(Request $request)
    {
       return $this->updateCommonContent(CommonContent::RULE_TYPE, $request);
    }

    public function updateGuide(Request $request)
    {
        return $this->updateCommonContent(CommonContent::GUIDE_TYPE, $request);
    }

    public function updateDisplayZalo(Request $request)
    {
        return $this->updateCommonContent(CommonContent::DISPLAY_ZALO, $request);
    }

    private function getCommonContent($type)
    {
        $commonContent = CommonContent::where('type', $type)->first();

        return [
            'status' => 1,
            'data' => $commonContent
        ];
    }

    public function getRule()
    {
        return $this->getCommonContent(CommonContent::RULE_TYPE);
    }

    public function getGuide()
    {
        return $this->getCommonContent(CommonContent::GUIDE_TYPE);
    }

    public function getDisplayZaloConfig()
    {
        return $this->getCommonContent(CommonContent::DISPLAY_ZALO);
    }


    public function isDisplayZalo()
    {
        $displayZalo = CommonContent::where('type', CommonContent::DISPLAY_ZALO)->first();

        return [
            'status' => 1,
            'data' => $displayZalo->html_content === "true" ? true : false
        ];
    }
}
