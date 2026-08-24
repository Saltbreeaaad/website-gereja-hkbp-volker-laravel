/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        hkbp: {
            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe',
            500: '#1d4ed8', 600: '#1e40af', 700: '#1d3557',
            800: '#003882', 900: '#00255c', 950: '#00183e', 
        },
        gold: {
            400: '#facc15', 500: '#eab308', 600: '#ca8a04',
        }
      },
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
        serif: ['"Playfair Display"', 'serif'],
      }
    },
  },
  plugins: [],
}