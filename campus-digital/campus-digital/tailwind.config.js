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
        success: '#10b981',
        error: '#ef4444',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}