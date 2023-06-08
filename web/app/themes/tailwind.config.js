/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./**/*.html", "./**/*.php"],
  important: true,
  plugins: [],
  theme: {
    colors: {
      cream: "#FFF7E5",
      teal: "#7AD7DB",
      yellow: "#FFEF56",
      orange: "#FFA81A",
      pink: "#FEA9BE",
      "light-green": "#8AE167",
      "dark-green": "#185500",
      white: "#ffffff"
    },
    extend: {
      screens: {
        masonry: 'calc(240px * 4 + 6rem)',
        wpmd: '600px',
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
    }
  }
}
