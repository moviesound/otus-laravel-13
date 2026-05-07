@extends('layouts.guest')

@section('title', 'Аутентификация')

@section('content')
    <div class="w-full max-w-md bg-white dark:bg-boxdark p-8 rounded-lg shadow">

        <h1 class="text-xl font-semibold mb-6">Вход в админку</h1>

        <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
            @csrf

            <div>
                <label>Email</label>
                <input type="email" name="email" class="w-full border rounded p-2">
            </div>

            <div>
                <label>Password</label>
                <input type="password" name="password" class="w-full border rounded p-2">
            </div>

            <button class="w-full bg-blue-600 text-white py-2 rounded">
                Войти
            </button>
        </form>

    </div>
@endsection
