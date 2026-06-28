/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Livewire/**/*.php", /* Pastikan ini ada agar komponen Livewire terbaca */
  ],
  theme: {
    extend: {
      colors: {
        'klinik-bg': '#F8FAFC',
        'klinik-blue-dark': '#0369A1',
        'klinik-green': '#16A34A',
        'klinik-green-dark': '#15803D',
      },
    },
  },
  plugins: [],
}