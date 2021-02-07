const mix = require('laravel-mix')

.disableNotifications()

mix.js('resources/src/main.js', 'assets').vue({
    version: 3,
})

.webpackConfig({
    externals: {
        "vue": "Vue",
    }
})