import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                ink: {
                    500: '#6B635A',
                    600: '#4A443C',
                    700: '#3A342D',
                    900: '#1B1815',
                    950: '#14110E',
                },
                gold: {
                    300: '#F0D06B',
                    500: '#DFA73A',
                    700: '#8A6A1F',
                    900: '#6B5117',
                },
                paper: '#FAFAF9',
                sand: '#F2F0EC',
                line: '#E4E1DB',
                success: '#2E6B4F',
                danger: {
                    DEFAULT: '#A33B2A',
                    700: '#8F3224',
                },
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                DEFAULT: '6px',
                sm: '4px',
                md: '6px',
                lg: '6px',
                xl: '8px',
                '2xl': '10px',
                '3xl': '12px',
            },
            boxShadow: {
                float: '0 12px 32px -8px rgba(20, 17, 14, 0.18)',
            },
        },
    },

    plugins: [forms],
};
