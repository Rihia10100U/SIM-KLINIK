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
        'klinik-bg': '#F8FAFC',   /* Ganti dengan kode warna latar belakang asli klinikmu */
        'klinik-blue': '#0284C7', /* Ganti dengan kode warna biru utama klinikmu */
      },
    },
  },
  plugins: [],
}