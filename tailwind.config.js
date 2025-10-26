/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/views/public/**/*.blade.php',
    './resources/js/Public/**/*.js',
    './resources/js/Public/**/*.vue',
  ],
  theme: {
    extend: {
      colors: {
        pcpb: {
          gold: '#bea55a',
          blue: '#1e3a8a',
          gray: '#374151',
        },
      },
    },
  },
  plugins: [],
};
