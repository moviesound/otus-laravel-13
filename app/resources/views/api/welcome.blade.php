<html>
<head>

</head>
<body>Welcome</body>
@php
    dd(
        config('domains.api'),
        gethostbyname('api.localhost')
    );
    @endphp
</html>
