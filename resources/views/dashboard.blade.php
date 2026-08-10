<!DOCTYPE html>
<html 
1234567891011121314151617181920212223242526272829303132333435363738$0lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        {{-- Content Security Policy (CSP) - Assurez-vous qu'elle est correcte pour Vite --}}
        <meta http-equiv="Content-Security-Policy" content="
            default-src 'self';
            script-src 'self' 'unsafe-eval' http://localhost:5173;
            style-src 'self' 'unsafe-inline';
            img-src 'self' data: https://coin-images.coingecko.com;
            connect-src 'self' http://localhost:5173;
            font-src 'self';
            object-src 'none';
            base-uri 'self';
            form-action 'self';
            frame-ancestors 'self';
        ">

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    </head>
    <body class="font-sans antialiased">
        {{-- C'est ici que votre application Vue.js/Inertia.js est rendue --}}
        @inertia
    </body>
</html>
