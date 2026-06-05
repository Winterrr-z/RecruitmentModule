<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('company.name') }} - Portal Pelamar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-on-surface min-h-screen flex flex-col justify-center items-center font-body-md">
    <main class="w-full">
        {{ $slot }}
    </main>
</body>
</html>
