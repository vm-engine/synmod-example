<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    <title>{{ $title ?? config('app.name') }}</title>
    @themeStyles('app')
</head>

<body class="bg-white p-8 text-gray-900 print:p-0">
    {{ $slot }}
    @themeScripts('app')
</body>

</html>
