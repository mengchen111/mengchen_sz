<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mews\Captcha\Facades\Captcha;

class TestController extends Controller
{
    public function getCaptcha(Request $request)
    {
        if ($request->getMethod() === 'POST') {
            $validator = Validator::make($request->all(), [
                'captcha' => 'required|captcha'
            ]);
            if ($validator->fails()) {
                throw new CustomException('captcha failed');
            } else {
                return 'captcha success';
            }
        } else {
            return Captcha::create();
        }
    }
}
