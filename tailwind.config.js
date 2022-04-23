const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {

  content: [
    './templates/**/*.twig',
    './templates/**/*.svg',
    './assets/js/**/*.vue',
    './web/assets/images/*.svg'
  ],
  theme: {

  },  // no options to configure

  corePlugins: {
    //preflight: false,
  },

  plugins: [
    require('@tailwindcss/forms')({
      strategy: 'class', // only generate classes
    }),
  ],

}
