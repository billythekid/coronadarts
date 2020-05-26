module.exports = {

  purge: {
    content: [
      './templates/**/*.twig',
      './templates/**/*.svg',
      './assets/js/**/*.vue',
      './web/assets/images/*.svg'
    ],
  },
  theme: {},  // no options to configure

  variants: { // all the following default to ['responsive']
    mixBlendMode: ['responsive'],
    backgroundBlendMode: ['responsive'],
    isolation: ['responsive'],
    backgroundColor: ['odd', 'even']
  },

  plugins: [
    require('tailwindcss-blend-mode')(), // no options to configure
    require('@tailwindcss/custom-forms'),
  ],
}
