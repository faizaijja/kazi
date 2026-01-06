/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        azure: {
          50: '#E6F2FF',
          100: '#CCE5FF',
          200: '#99CBFF',
          300: '#66B1FF',
          400: '#3397FF',
          500: '#0078D4', // Azure base color
          600: '#0066B3',
          700: '#005499',
          800: '#004380',
          900: '#003166',
        },
      },
    },
  },
  plugins: [],
}

