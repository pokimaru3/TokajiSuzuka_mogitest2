@extends('layouts.default')

@section('title','勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/admin/attendance_index.css') }}" />
@endsection

@section('content')
    @include('layouts.admin_header')
    <div class="attendance-list-container">
        <div class="page__title-wrapper">
            <h1 class="page__title">{{ \Carbon\Carbon::parse($date)->isoFormat('YYYY年M月D日') }}の勤怠</h1>
        </div>
        <div class="day-navigation">
            <a href="{{ route('admin.attendance.list', ['date' => \Carbon\Carbon::parse($date)->subDay()->format('Y-m-d')]) }}" class="nav-arrow prev">
                <img src="{{ asset('img/arrow.png') }}" alt="前日" class="arrow-icon left">
                <span>前日</span>
            </a>
            <span class="month-display">
                <img src="{{ asset('img/calendar.png') }}" alt="カレンダー" class="calendar-icon">
                {{ \Carbon\Carbon::parse($date)->format('Y年m月d日') }}
            </span>
            <a href="{{ route('admin.attendance.list', ['date' => \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d')]) }}" class="nav-arrow next">
                <span>翌日</span>
                <img src="{{ asset('img/arrow.png') }}" alt="翌日" class="arrow-icon right">
            </a>
        </div>
        <table class="attendance-table">
            <thead>
                <tr>
                    <th class="table__header">名前</th>
                    <th class="table__header">出勤</th>
                    <th class="table__header">退勤</th>
                    <th class="table__header">休憩</th>
                    <th class="table__header">合計</th>
                    <th class="table__header">詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->user->name }}</td>
                        <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                        <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                        <td>{{ $attendance->total_break_time ? gmdate('H:i', $attendance->total_break_time * 60) : '' }}</td>
                        <td>{{ $attendance->total_work_time ? gmdate('H:i', $attendance->total_work_time * 60) : '' }}</td>
                        <td><a href="{{ route('admin.attendance.detail', $attendance->id) }}" class="table__detail">詳細</a></td>
                    </tr>
                @empty
                    {{-- 空白にする --}}
                @endforelse
            </tbody>
        </table>
    </div>
@endsection