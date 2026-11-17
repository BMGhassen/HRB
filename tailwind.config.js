/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        lion: {
          light: '#FFD54F', // Bright mane highlight
          DEFAULT: '#FFC107', // Exact Lion Yellow
          dark: '#E5A100', // Dark shadow gold
          black: '#0A0D17', // Deep Carbon Black
          card: '#141824', // Card Background
        }
      }
    },
  },
  plugins: [],
}