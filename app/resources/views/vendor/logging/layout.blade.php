<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>
        @yield('title', 'Logging')
    </title>

    <style>

        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        form {
            margin-bottom: 15px;
        }

        input {
            padding: 6px;
            margin-right: 5px;
        }

        button {
            padding: 6px 12px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 13px;
            vertical-align: top;
        }

        th {
            background: #f5f5f5;
            text-align: left;
        }

        .mono {
            font-family: monospace;
            font-size: 12px;
        }

    </style>
</head>
<body>

@yield('content')

</body>
</html>