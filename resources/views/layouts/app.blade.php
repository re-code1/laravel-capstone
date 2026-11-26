<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Report Cards</title>
    <link rel="stylesheet" href="{{ asset('css/school.css') }}">
</head>
<body>
    <header>
        <a href="{{ url('/sections') }}">Report Cards</a>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>
