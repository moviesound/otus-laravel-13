@extends('query_logging::layout')

@section('title', 'Action Logs')

@section('content')

    <h2>Action Logs</h2>

    <form method="GET">

        <input
                type="number"
                name="user_id"
                value="{{ request('user_id') }}"
                placeholder="User ID"
        >

        <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Action"
        >

        <button type="submit">
            Filter
        </button>

        <a href="{{ url()->current() }}">
            Reset
        </a>

    </form>

    @include('query_logging::partials.table')

@endsection