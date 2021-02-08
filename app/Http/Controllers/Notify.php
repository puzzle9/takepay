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

        // {"appid":"wx2ed04ec3264a1b24","bank_type":"CMB_CREDIT","cash_fee":"1","fee_type":"CNY","is_subscribe":"Y","mch_id":"1604952925","nonce_str":"MG2W1W3OPPW1berk","openid":"o9tFBwKdeBs55IcQHyTrnE1Ue-cw","out_trade_no":"wechat20210208220205bIZsN","result_code":"SUCCESS","return_code":"SUCCESS","sign":"6114FEC635C559FA533ABC16B0A031E6","time_end":"20210208222309","total_fee":"1","trade_type":"JSAPI","transaction_id":"4200000780202102081584484302"}
        Order::paySuccess($result['out_trade_no'], $result['transaction_id']);

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
