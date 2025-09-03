let mix = require('laravel-mix');
require('laravel-mix-polyfill');
require('laravel-mix-purgecss');
let path = require('path');
const proxyURL = "coronadarts.test";
const webpack = require('webpack');

mix.setPublicPath(path.normalize('web'))
    .postCss('assets/css/app.css', 'web/assets/css', [
        require("tailwindcss"),
        require("autoprefixer"),
    ])
    .js(['assets/js/app.js'], 'web/assets/js')
    .vue()
    .extract()
    .browserSync({
        proxy: proxyURL,
        open: false,
        notify: false,
        files: [
            "templates/**/*.twig",
            "web/**/*.(js|css)",
            "tailwind.config.js"
        ]
    })
    .webpackConfig({
        plugins: [
            new webpack.DefinePlugin({
                // Vue CLI is in maintenance mode, and probably won't merge my PR to fix this in their tooling
                // https://github.com/vuejs/vue-cli/pull/7443
                __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: 'false',
            })
        ],
    })
    .options({
        processCssUrls: false
    });

if (mix.inProduction()) {
    mix.version();
}
