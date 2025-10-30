@extends('layouts.default')

@section('title','スタッフ一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/admin/staff_index.css') }}" />
@endsection

@section('content')
    @include('layouts.admin_header')
    <div class="attendance-list-container">
        <h1 class="page__title">{{ $user->name }}さんの勤怠</h1>
        <div class="month-navigation">
            <a href="{{ route('admin.staff.attendance', ['id' => $user->id,'month' => \Carbon\Carbon::parse($month)->subMonth()->format('Y-m')]) }}">← 前月</a>
            <span class="month-display">{{ \Carbon\Carbon::parse($month)->format('Y年m月') }}</span>
            <a href="{{ route('admin.staff.attendance', ['id' => $user->id,'month' => \Carbon\Carbon::parse($month)->addMonth()->format('Y-m')]) }}">翌月 →</a>
        </div>
        <table class="attendance-table">
            <thead>
                <tr>
                    <th class="table__header">日付</th>
                    <th class="table__header">出勤</th>
                    <th class="table__header">退勤</th>
                    <th class="table__header">休憩</th>
                    <th class="table__header">合計</th>
                    <th class="table__header">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($days as $date)
                    @php
                        $attendance = $attendances->get($date);
                    @endphp
                    @php
                        if ($attendance) {
                            $totalBreakMinutes = $attendance->breaks->sum(fn($b) =>
                                $b->break_start && $b->break_end
                                    ? \Carbon\Carbon::parse($b->break_start)->diffInMinutes(\Carbon\Carbon::parse($b->break_end))
                                    : 0
                            );
                            $breakHours = floor($totalBreakMinutes / 60);
                            $breakMinutes = $totalBreakMinutes % 60;
                            if ($attendance->clock_in && $attendance->clock_out) {
                                $totalWorkMinutes = \Carbon\Carbon::parse($attendance->clock_in)
                                    ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out)) - $totalBreakMinutes;
                                $workHours = floor($totalWorkMinutes / 60);
                                $workMinutes = $totalWorkMinutes % 60;
                            } else {
                                $workHours = $workMinutes = 0;
                            }
                        }
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($date)->isoFormat('M月D日(ddd)') }}</td>
                        <td>{{ $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                        <td>{{ $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                        <td>{{ $attendance && $totalBreakMinutes > 0 ? sprintf('%02d:%02d', $breakHours, $breakMinutes) : '' }}</td>
                        <td>{{ $attendance && $attendance->clock_in && $attendance->clock_out ? sprintf('%02d:%02d', $workHours, $workMinutes) : '' }}</td>
                        <td>
                            @if ($attendance)
                                <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}">詳細</a>
                            @else
                                <span class="disabled-text">詳細</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <button type="submit" class="btn">CSV出力</button>
    </div>
@endsection