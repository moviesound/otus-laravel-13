<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Telegram webhook tester</title>

    <style>
        body {
            font-family: sans-serif;
            margin: 30px;
        }

        textarea {
            width: 100%;
            height: 500px;
            font-family: monospace;
        }

        button {
            margin-top: 15px;
            padding: 10px 20px;
        }

        pre {
            background: #eee;
            padding: 15px;
        }
    </style>
</head>
<body>

<h2>Telegram webhook tester</h2>

@if($errors->any())
    <pre>{{ $errors->first() }}</pre>
@endif

@if(session('status'))
    <h3>HTTP {{ session('status') }}</h3>

    {!! session('response') !!}
@endif

<form id="telegram-form">
    @csrf

    <textarea id="payload">{{ old('payload', <<<JSON
{
    "update_id": 123456789,
    "message": {
        "message_id": 1,
        "from": {
            "id": 111111111,
            "is_bot": false,
            "first_name": "Sergey"
        },
        "chat": {
            "id": 223578088,
            "type": "private"
        },
        "date": 1752000000,
        "text": "/start"
    }
}
JSON
) }}</textarea>

    <button type="submit">
        Отправить
    </button>
</form>


<pre id="response"></pre>


<script>
    document
        .getElementById('telegram-form')
        .addEventListener('submit', async (e) => {
            e.preventDefault();

            const payload = document.getElementById('payload').value;

            const response = await fetch(
                'http://api.localhost:8055/integrations/telegram/{{ config('services.telegram.url_key') }}',
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: payload
                }
            );

            document.getElementById('response').innerHTML =
                await response.text();
        });
</script>

</body>
</html>
