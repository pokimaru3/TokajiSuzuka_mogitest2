@extends('layouts.default')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/verify.css')  }}">
@endsection

@section('content')
    @include('layouts.header')
    <div class="mail_notice--div">
        <div class="mail_notice--content">
            @if (session('message'))
                <p class="notice_resend--p" role="alert">
                    {{ session('message') }}
                </p>
            @endif
            <p class="alert_resend--p">
                登録していただいたメールアドレスに認証メールを送付しました。<br>
                メール認証を完了してください。
            </p>
            <form class="mail_resend--form" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="mail_resend--button">認証メール再送</button>
            </form>
        </div>
    </div>
@endsection