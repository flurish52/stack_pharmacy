<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

{{--    //sitemap verification--}}
    <meta name="google-site-verification" content="q_MB4L73f8sFlCCR2OGSB4qH25L-wyzhf5ubioP_0Rk" />
{{--    //end of sitemap verification--}}

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <title inertia>{{ config('app.name', 'Stack Pharmacy') }}</title>

    @routes
    @vite(['resources/js/app.js'])
    @inertiaHead
</head>
<body class="font-sans antialiased">
@inertia
</body>
</html>
