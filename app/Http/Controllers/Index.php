<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;

use App\Models\User;
use App\Models\Order;

class Index extends Controller
{
    public function start()
    {
        return [
            'time' => Carbon::parse()->toDateTimeString(),
        ];
    }

    public function index()
    {
        return view('index');
    }

    public function base(Request $request)
    {
        $this->validate($request, [
            'url' => 'required|url',
        ]);

        if (User::isWechat()) {
            $debug = config('app.debug');

            $wechat_jssdk = User::officialAccount()->jssdk->setUrl($request->input('url'))->buildConfig([
                'hideAllNonBaseMenuItem',
                'chooseWXPay',
            ], $debug, true, false);
        }

        return [
            'title' => '自由兑换',
            'last_amount' => Order::latest()->value('amount'),
            'wechat_jssdk' => $wechat_jssdk ?? null,
        ];
    }
}
