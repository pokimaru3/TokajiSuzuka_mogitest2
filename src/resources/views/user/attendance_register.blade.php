@extends('layouts.default')

@section('title','勤怠登録')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/user/attendance_register.css') }}" />
@endsection

@section('content')
    @include('layouts.header')
    <div class="attendance-container">
        <div class="attendance-status" id="status">
            {{ $work_status === 'off_duty' ? '勤務外' :
                ($work_status === 'working' ? '出勤中' :
                ($work_status === 'on_break' ? '休憩中' : '退勤済'))
            }}
        </div>
        <div class="attendance-date">{{ $formattedDate }}</div>
        <div class="attendance-time" id="clock">{{ $time }}</div>
        <div id="button-area">
            @if($work_status === 'off_duty')
                <button class="attendance-button" data-action="clock_in">出勤</button>
            @elseif($work_status === 'working')
                <button class="attendance-button" data-action="clock_out">退勤</button>
                <button class="attendance-button" data-action="break_start">休憩入</button>
            @elseif($work_status === 'on_break')
                <button class="attendance-button" data-action="break_end">休憩戻</button>
            @elseif($work_status === 'finished')
                <p class="attendance-message">お疲れ様でした。</p>
            @endif
        </div>
    </div>

    <script>
        setInterval(() => {
            const now = new Date();
            document.getElementById('clock').textContent =
                now.toTimeString().slice(0,5);
        }, 1000);

        document.addEventListener('click', async (e) => {
            if (!e.target.matches('#button-area [data-action]')) return;
            const action = e.target.dataset.action;
            const res = await fetch('/attendance', {
                method: 'POST',
                headers: {
                    'Content-Type' :'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body:JSON.stringify({ action }),
                credentials: 'same-origin'
            });

            const data = await res.json();
            console.log(data);

            if (data.status) {
                document.getElementById('status').textContent = data.status_text;
                document.getElementById('button-area').innerHTML = data.buttons_html;
            }
        });
    </script>
@endsection
