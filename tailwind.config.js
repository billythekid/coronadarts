module.exports = {
  theme: {},  // no options to configure

  variants: { // all the following default to ['responsive']
    mixBlendMode: ['responsive'],
    backgroundBlendMode: ['responsive'],
    isolation: ['responsive'],
    backgroundColor: ['odd','even']
  },

  plugins: [
    require('tailwindcss-blend-mode')(), // no options to configure
  ],
}
