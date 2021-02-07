<!DOCTYPE html>
<html lang="cmn-Hans-CN">
    <head>
        <meta charset="UTF-8" />
        <link rel="icon" href="/favicon.ico" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta http-equiv="Cache-Control" content="no-siteapp" />
        <title>{{config('app.name')}}</title>
        <style type="text/css">
            body {
                background: #f7f8fa
            }
        </style>

        <script type="text/javascript">
            window.token = '{!! env('TOKEN', request()->input('token')) !!}'
        </script>

        <script src="https://cdn.jsdelivr.net/npm/flyio@0.6.14/dist/fly.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/vue@3.0.5/dist/vue.runtime.global.prod.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vant@3.0.6/lib/index.css" />
        <script src="https://cdn.jsdelivr.net/npm/vant@3.0.6/lib/vant.min.js"></script>

        @if(\App\Models\User::isWechat())
        <script src="https://res.wx.qq.com/open/js/jweixin-1.6.0.js"></script>
        @endif

    </head>
    <body>
        <div id="app">
        </div>

        <script src="/assets/main.js?{{time()}}"></script>
    </body>
</html>
