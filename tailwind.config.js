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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                gaming: {
                    blue: {
                        light: '#60a5fa',
                        DEFAULT: '#2563eb',
                        dark: '#1e40af',
                    },
                    green: {
                        light: '#4ade80',
                        DEFAULT: '#16a34a',
                        dark: '#15803d',
                    },
                    dark: '#0f172a',
                    surface: '#1e293b',
                }
            },
            boxShadow: {
                'gaming': '0 0 20px rgba(37, 99, 235, 0.3)',
                'gaming-green': '0 0 20px rgba(22, 163, 74, 0.3)',
            }
        },
    },

    plugins: [forms],
};
