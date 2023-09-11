/** @type {import('tailwindcss').Config} */
const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
  content: ["./**/*.html", "./**/*.php"],
  important: true,
  plugins: [],
  blocklist: [
    "sticky"
  ],
  theme: {
    colors: {
      transparent: "rgba(0, 0, 0, 0)",
      cream: "#FFF7E5",
      teal: "#7AD7DB",
      yellow: "#FFEF56",
      orange: "#FFA81A",
      pink: "#FEA9BE",
      "light-green": "#8AE167",
      "dark-green": "#185500",
      "faded-green": "rgba(24, 85, 0, 0.2)",
      white: "#ffffff"
    },
    extend: {
      screens: {
        masonry: 'calc(240px * 4 + 6rem)',
        wpmd: '600px',
        content: '620px',
      },
      height: {
        320: '320px',
        440: '440px'
      },
      maxWidth: {
        input: '320px',
        320: '320px',
        440: '440px',
        1024: '1024px'
      },
      minWidth: {
        320: '320px',
        440: '440px'
      },
      width: {
        320: '320px',
        440: '440px'
      },
      zIndex: {
        60: '60',
      }
    },
    fontFamily: {
      sans: ['pp-mori', ...defaultTheme.fontFamily.sans],
      styrene:  ['Styrene B Web', ...defaultTheme.fontFamily.sans]
    }
  }
}
