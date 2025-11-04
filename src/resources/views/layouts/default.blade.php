<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('/css/reset.css')  }}">
    <link rel="stylesheet" href="{{ asset('/css/common.css')  }}">
    <link rel="stylesheet" href="{{ asset('/css/header.css')  }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @yield('css')
</head>

<body>
    @yield('content')
</body>

</html>