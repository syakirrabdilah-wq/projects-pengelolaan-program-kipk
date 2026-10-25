<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KIP-K')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container"><a class="navbar-brand" href="{{ route('admin.penerima.index') }}">KIP-K</a></div>
</nav>
<main class="container pb-5">
    @if (session('sukses'))<div class="alert alert-success" role="status">{{ session('sukses') }}</div>@endif
    @yield('content')
</main>
</body>
</html>
