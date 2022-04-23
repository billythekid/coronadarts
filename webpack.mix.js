let mix = require('laravel-mix');
require('laravel-mix-polyfill');
require('laravel-mix-purgecss');
let path = require('path');
const proxyURL = "coronadarts.test";


mix.setPublicPath(path.normalize('web'))
  .postCss('assets/css/app.css', 'web/assets/css', [
    require("tailwindcss"),
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
  });

if (mix.inProduction()) {
  mix.version();
}
