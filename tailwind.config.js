import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                heading: ['Poppins', 'Manrope', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    light: '#ECFDF5',
                    DEFAULT: '#14B8A6',
                    dark: '#0F766E',
                },
                secondary: {
                    light: '#ECFDF5',
                    DEFAULT: '#D1FAE5',
                },
                accent: {
                    light: '#FB7185',
                    DEFAULT: '#F97316',
                },
                whatsapp: '#25D366',
                neutral: {
                    bg: '#FAFAF9',
                    text: '#1E293B',
                },
            },        },
    },
    plugins: [forms],
};
