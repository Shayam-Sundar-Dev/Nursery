/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        botanical: {
          50: '#F2F7F3',
          100: '#E3EDE5',
          200: '#C5DCC9',
          300: '#9CBDA2',
          400: '#6E9C76',
          500: '#477B50',
          600: '#34633C',
          700: '#274D2E',
          800: '#1D3B23',
          900: '#132818',
          950: '#09150C',
        },
        cream: {
          50: '#FDFCFA',
          100: '#FAF7F0',
          200: '#F4EFE4',
          300: '#E8DFC8',
        },
        sand: {
          50: '#FAF9F6',
          100: '#F5F2EB',
          200: '#E9E3D5',
        },
      },
      fontFamily: {
        serif: ['"Playfair Display"', 'Georgia', 'Cambria', 'serif'],
        sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'sans-serif'],
      },
      boxShadow: {
        'subtle': '0 2px 10px rgba(0, 0, 0, 0.03), 0 1px 3px rgba(0, 0, 0, 0.02)',
        'card': '0 4px 20px -2px rgba(21, 41, 25, 0.06), 0 2px 6px -1px rgba(21, 41, 25, 0.04)',
        'card-hover': '0 16px 36px -4px rgba(21, 41, 25, 0.12), 0 6px 14px -2px rgba(21, 41, 25, 0.06)',
        'float': '0 24px 48px -12px rgba(11, 23, 14, 0.18)',
      },
    },
  },
  plugins: [],
};
