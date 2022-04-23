const mix = require('laravel-mix');
require('laravel-mix-polyfill');
require('laravel-mix-purgecss');
let path = require('path');
const tailwindcss = require('tailwindcss');
const proxyURL = "cd.test";
const rootPath = Mix.paths.root.bind(Mix.paths);

mix.setPublicPath(path.normalize('web'))
  .sass('assets/sass/app.scss', 'web/assets/css')
  .js(['assets/js/app.js'], 'web/assets/js')
  .vue({
    postCss: [tailwindcss('./tailwind.config.js')],
  })
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
  .version()
  .purgeCss({
    enabled: mix.inProduction(),
    content: [
      rootPath('templates/**/*.twig'),
      rootPath('templates/**/*.svg'),
      rootPath('assets/js/**/*.vue'),
      rootPath('web/assets/images/*.svg'),
    ],
    whitelistPatterns: [/-active$/, /-enter$/, /-leave-to$/, /^bg-mg-/, /^video-/, /video$/, /iframe$/, /strong/]
  })
;
