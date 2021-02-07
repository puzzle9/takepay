<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Order;

use Yansongda\Pay\Exceptions\InvalidArgumentException;

class Notify extends Controller
{
    public function wechat()
    {
        $wechat = Order::getGateway('wechat');

        try {
            $result = $wechat->verify();
            \Log::info($result);
        } catch (InvalidArgumentException $e) {
            abort(404);
        }

        // Order::paySuccess($result['out_trade_no'], $result['']);

        return $wechat->success();
    }

    public function alipay()
    {
        $alipay = Order::getGateway('alipay');

        try {
            $result = $alipay->verify();
        } catch (InvalidArgumentException $e) {
            abort(404);
        }

        \Log::info($result);

        return $alipay->success();
    }
}
