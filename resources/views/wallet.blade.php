<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet Dashboard</title>
    @vite('resources/js/app.js')
</head>
<body class="bg-gray-100">
    <div id="app" class="p-6">
        <wallet-dashboard></wallet-dashboard>
    </div>
</body>
</html>
