<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

class OAuth extends Controller
{
    public function wechat(Request $request)
    {
        $oauth = User::officialAccount()->oauth;

        $code = $request->input('code');
        $state = $request->input('state');

        if ($code && $state) {
            $user_value = $oauth->userFromCode($code)->id;

            if (!$user_value) {
                return redirect('/');
            }

            return User::updateOrCreateUser($user_value);

        } else {
            $url = $oauth->redirect(route('oauth.wechat'));
            return redirect($url);
        }
    }
}
