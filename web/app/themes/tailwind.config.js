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
        'wp-md': '500px'
      },
      maxWidth: {
        input: '320px',
      },
      zIndex: {
        60: '60',
      }
    }
  }
}
