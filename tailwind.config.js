import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/web/js/**/*.vue',
        './resources/web/js/**/*.js',
    ],

    theme: {
        extend: {
            colors: {
                tr: {
                    bg:           '#000003',
                    surface:      '#0e0f11',
                    'surface-2':  '#1a1c1f',
                    'surface-3':  '#252729',
                    primary:      '#ff6100',
                    accent:       '#c1f527',
                    text:         '#feeddf',
                    'text-muted': '#9a9590',
                    'text-dim':   '#5a5550',
                    border:       '#2a2c2e',
                },
            },
            fontFamily: {
                mono: ['"Share Tech Mono"', ...defaultTheme.fontFamily.mono],
            },
            borderRadius: {
                tr: '6px',
            },
        },
    },

    plugins: [forms],
};
