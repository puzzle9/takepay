<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Order as OrderModel;

use Illuminate\Support\Str;

use Carbon\Carbon;

class Order extends Controller
{
    public function create(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required',
            'remark' => 'nullable|max:50',
        ]);

        $amount = $request->input('amount');
        $remark = $request->input('remark');

        $user = $request->user();

        $type = $user->type;

        $no = $type.Carbon::parse()->format('YmdHms').Str::random(5);

        OrderModel::create([
            'type' => $type,
            'no' => $no,
            'amount' => $amount,
            'remark' => $remark,
            'status' => OrderModel::STATUS_CREATE,
            'user_id' => $user->id,
        ]);

        return [
            'no' => $no,
        ];
    }

    public function pay(Request $request)
    {
        $this->validate($request, [
            'no' => 'required',
        ]);

        $order = OrderModel::where([
            'no' => $request->input('no'),
            'user_id' => $request->user()->id,
        ])->first();

        if (!$order) {
            abort(422, '订单错误');
        }

        if ($order->status != OrderModel::STATUS_CREATE) {
            abort(422, '订单状态错误');
        }

        $no = $order->no;
        $type = $order->type;
        $amount = $order->amount;
        $remark = $order->remark ?: '备注信息';

        switch ($type) {
            case User::TYPE_WECHAT:
                $user_value = $request->user()->value;

                $pay = OrderModel::getGateway($type)->mp([
                    'out_trade_no' => $no,
                    'body' => $remark,
                    'total_fee' => $amount * 100,
                    'openid' => $user_value,
                ]);

                break;

            default:
                abort(422, '暂不支持此订单');
                break;
        }
    }

    public function lists(Request $request)
    {
        $this->validate($request, [
            'page' => 'required|int',
        ]);

        $user_id = $request->user()->id;

        $data = OrderModel::select('no', 'amount', 'status', 'remark', 'created_at', 'updated_at')->latest()->where('user_id', $user_id)->simplePaginate(15);

        return [
            'data' => $data->items(),
            'current' => $data->currentPage(),
            'has_more_page' => $data->hasMorePages(),
        ];
    }

    public function delete(Request $request, $no)
    {
        $user_id = $request->user()->id;
        OrderModel::where('no', $no)->where('user_id', $user_id)->delete();
    }
}
