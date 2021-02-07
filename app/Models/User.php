<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Lumen\Auth\Authorizable;

use Symfony\Component\HttpFoundation\Response;

use Overtrue\LaravelWeChat\Facade as EasyWeChat;

use Illuminate\Support\Facades\Crypt;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    const TYPE_WECHAT = 'wechat';
    const TYPE_ALIPAY = 'alipay';
    const TYPE_QQ = 'qq';

    public static function updateOrCreateUser($user_value)
    {
        $user = self::updateOrCreate([
            'type' => self::getUserType(),
            'value' => $user_value,
        ]);

        return redirect()->route('index', [
            'token' => Crypt::encrypt($user->id),
        ]);
    }

    public static function getUserType($abort_error=true)
    {
        $userAgent = request()->userAgent();

        // \Log::info($userAgent);

        switch (true) {
            case strpos($userAgent, 'MicroMessenger'):
                // Mozilla/5.0 (Linux; Android 10; M2006J10C Build/QP1A.190711.020; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/88.0.4324.93 Mobile Safari/537.36 MicroMessenger/6.7.3.1341(0x26070340) NetType/WIFI Language/zh_CN Process/tools
                return self::TYPE_WECHAT;
                break;

            case strpos($userAgent, 'AlipayClient'):
                // Mozilla/5.0 (Linux; U; Android 10; zh-CN; M2006J10C Build/QP1A.190711.020) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/69.0.3497.100 UWS/3.21.0.83 Mobile Safari/537.36 AlipayChannelId/5136 UCBS/3.21.0.83_200109202211 NebulaSDK/1.8.100112 Nebula AlipayDefined(nt:WIFI,ws:400|0|2.7,ac:sp) AliApp(AP/10.1.87.7347) AlipayClient/10.1.87.7347 Language/zh-Hans useStatusBar/true isConcaveScreen/false Region/CN
                return self::TYPE_ALIPAY;
                break;

            default:
                return $abort_error ? abort(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, '暂不支持此浏览器') : null;
                break;
        }
    }

    public static function isWechat()
    {
        return self::getUserType(false) == self::TYPE_WECHAT;
    }

    public static function officialAccount()
    {
        return EasyWeChat::officialAccount();
    }
}
