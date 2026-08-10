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

    safelist: [
        // Thèmes sombres (useTheme.js)
        'bg-gradient-to-br',
        'from-indigo-900',
        'via-purple-900',
        'to-black',
        'bg-gradient-to-b',
        'from-blue-950',
        'via-slate-900',
        'bg-gradient-to-tr',
        'from-fuchsia-900',

        // Classes liées au texte
        'text-black',
        'text-white',
        'text-gray-800',
        'text-gray-200',

        // Bordures et boutons
        'border-black/20',
        'border-white',
        'border-white/30',
        'hover:bg-black/5',
        'hover:bg-white/10',

        // Custom titles
        'title-gradient',
        'neon-text-night',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
