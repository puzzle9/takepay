<?php

namespace App\Models;

use DateTimeInterface;

use Yansongda\Pay as PayLaravel;

use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    const STATUS_CREATE = 'create';
    const STATUS_PAID = 'paid';
    const STATUS_CLOSE = 'close';

    const Status = [
        self::STATUS_CREATE => '已创建',
        self::STATUS_PAID => '已支付',
        self::STATUS_CLOSE => '已关闭',
    ];

    protected $appends = [
        'status_string',
    ];


    public function getStatusStringAttribute()
    {
        return self::Status[$this->status] ?? null;
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    /**
     * 返回相应支付
     * @param string $type 类别
     * @return PayLaravel\Gateways\Alipay | PayLaravel\Gateways\Wechat
     */
    public static function getGateway(string $type)
    {
        $config = config('pay')[$type] ?? null;

        if (!$config) {
            abort(422, 'error pay gateway '.$type);
        }

        if (empty($config['notify_url'])) {
            $config['notify_url'] = route('notify.'.$type);
        }

        return PayLaravel\Pay::$type($config);
    }

    public static function paySuccess($no, $trade_no)
    {
        self::withTrashed()->where('no', $no)->update([
            'trade_no' => $trade_no,
            'status' => self::STATUS_PAID,
        ]);
    }
}
