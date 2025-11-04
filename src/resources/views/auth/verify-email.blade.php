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
            <a href="http://localhost:8025/#" class="verify-link-button" target="_blank" rel="noopener noreferrer">
                認証はこちらから
            </a>
            <form id="resendForm" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <a href="#" class="mail_resend--link" onclick="event.preventDefault(); document.getElementById('resendForm').submit();">
                    認証メールを再送する
                </a>
            </form>
        </div>
    </div>
@endsection