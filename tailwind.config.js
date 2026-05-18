import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['var(--font-sans)', ...defaultTheme.fontFamily.sans],
                display: ['var(--font-display)', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                background: 'var(--background)',
                foreground: 'var(--foreground)',
                card: 'var(--card)',
                'card-foreground': 'var(--card-foreground)',
                popover: 'var(--popover)',
                'popover-foreground': 'var(--popover-foreground)',
                primary: 'var(--primary)',
                'primary-foreground': 'var(--primary-foreground)',
                secondary: 'var(--secondary)',
                'secondary-foreground': 'var(--secondary-foreground)',
                muted: 'var(--muted)',
                'muted-foreground': 'var(--muted-foreground)',
                accent: 'var(--accent)',
                'accent-foreground': 'var(--accent-foreground)',
                destructive: 'var(--destructive)',
                'destructive-foreground': 'var(--destructive-foreground)',
                border: 'var(--border)',
                input: 'var(--input)',
                ring: 'var(--ring)',
                electric: 'var(--electric)',
                'electric-foreground': 'var(--electric-foreground)',
                'cyan-neon': 'var(--cyan-neon)',
                'purple-neon': 'var(--purple-neon)',
                success: 'var(--success)',
                warning: 'var(--warning)',
                gaming: {
                    dark: 'var(--gaming-dark)',
                    darker: 'var(--gaming-darker)',
                    panel: 'var(--gaming-panel)',
                }
            },
            boxShadow: {
                'neon': 'var(--shadow-neon)',
                'neon-hover': 'var(--shadow-neon-hover)',
                'purple': 'var(--shadow-purple)',
                'purple-hover': 'var(--shadow-purple-hover)',
            }
        },
    },


    plugins: [forms],
};
