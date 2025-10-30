@extends('layouts.default')

@section('title','管理者ログイン')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/admin/login.css')  }}">
@endsection

@section('content')
    @include('layouts.header')
        <div class="form-container">
            <form action="/admin/login" method="post">
                @csrf
                <h1 class="page-title">管理者ログイン</h1>
                <label for="mail" class="entry__name">メールアドレス</label>
                <input name="email" id="mail" type="text" class="input" value="{{ old('email') }}">
                <div class="form__error">
                    @error('email')
                    {{ $message }}
                    @enderror
                </div>
                <label for="password" class="entry__name">パスワード</label>
                <input name="password" id="password" type="password" class="input">
                <div class="form__error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
                <button class="btn btn--big">管理者ログインする</button>
            </form>
        </div>
@endsection