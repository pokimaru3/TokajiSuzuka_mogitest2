@extends('layouts.default')

@section('title','勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/user/attendance_show.css') }}" />
@endsection

@section('content')
    @include('layouts.header')
    <div class="attendance-detail-container">
        <div class="page__title-wrapper">
            <h1 class="page__title">勤怠詳細</h1>
        </div>
        <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="attendance-detail-card">
                <div class="attendance-detail__row">
                    <span class="attendance-detail__label">名前</span>
                    <span class="attendance-detail__value">{{ $attendance->user->name }}</span>
                </div>
                <div class="attendance-detail__row">
                    <span class="attendance-detail__label">日付</span>
                    <span class="attendance-detail__value">
                        <span class="attendance-date__year">{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}</span>
                        <span class="attendance-date__monthday">{{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}</span>
                    </span>
                </div>
                <div class="attendance-detail__row">
                    <label class="attendance-detail__label">出勤・退勤</label>
                    <input type="time" name="clock_in" value="{{ optional($attendance->correctionRequest)->requested_clock_in ? \Carbon\Carbon::parse($attendance->correctionRequest->requested_clock_in)->format('H:i') : ($attendance->clock_in ? $attendance->clock_in->format('H:i') : '') }}" class="attendance-detail__input" {{ optional($attendance->correctionRequest)->status === 'pending' ? 'readonly' : '' }}>
                    〜
                    <input type="time" name="clock_out" value="{{ optional($attendance->correctionRequest)->requested_clock_out ? \Carbon\Carbon::parse($attendance->correctionRequest->requested_clock_out)->format('H:i') : ($attendance->clock_out ? $attendance->clock_out->format('H:i') : '') }}" class="attendance-detail__input" {{ optional($attendance->correctionRequest)->status === 'pending' ? 'readonly' : '' }}>
                    @if ($errors->has('clock_in') || $errors->has('clock_out'))
                        <div class="form__error">
                            {{ $errors->first('clock_in') ?? $errors->first('clock_out') }}
                        </div>
                    @endif
                </div>
                @php
                    $breaks = $attendance->breaks;
                    $correctionBreaks = optional($attendance->correctionRequest)->correctionBreaks ?? collect();
                    $breaks[] = null;
                @endphp
                @foreach($breaks as $index => $break)
                    @php
                        $startValue = optional($correctionBreaks->get($index))->requested_break_start ? \Carbon\Carbon::parse($correctionBreaks->get($index)->requested_break_start)->format('H:i') : optional($break)->break_start?->format('H:i') ?? '';
                        $endValue = optional($correctionBreaks->get($index))->requested_break_end
                        ? \Carbon\Carbon::parse($correctionBreaks->get($index)->requested_break_end)->format('H:i') : optional($break)->break_end?->format('H:i') ?? '';
                    @endphp
                    <div class="attendance-detail__row">
                        <label class="attendance-detail__label">休憩{{ $index + 1 }}</label>
                        <input type="time" name="break_start_{{ $index + 1 }}" value="{{ $startValue }}" class="attendance-detail__input" {{ optional($attendance->correctionRequest)->status === 'pending' ? 'readonly' : '' }}>
                        〜
                        <input type="time" name="break_end_{{ $index + 1 }}" value="{{ $endValue }}" class="attendance-detail__input" {{ optional($attendance->correctionRequest)->status === 'pending' ? 'readonly' : '' }}>
                        <div class="form__error">
                            {{ $errors->first('break_start_' . ($index + 1)) ?: $errors->first('break_end_' . ($index + 1)) }}
                        </div>
                    </div>
                @endforeach
                <div class="attendance-detail__row">
                    <label class="attendance-detail__label">備考</label>
                    <textarea name="remarks" class="attendance-detail__textarea" {{ optional($attendance->correctionRequest)->status === 'pending' ? 'readonly' : '' }}>{{ optional($attendance->correctionRequest)->remarks ?? $attendance->remarks }}</textarea>
                    @if ($errors->has('remarks'))
                        <div class="form__error">{{ $errors->first('remarks') }}</div>
                    @endif
                </div>
            </div>
            <div class="attendance-detail__actions">
                @if ($attendance->correctionRequest && $attendance->correctionRequest->status === 'pending')
                    <p class="attendance-detail__notice">*承認待ちのため修正はできません。</p>
                @else
                    <button type="submit" class="btn">修正</button>
                @endif
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timeInputs = document.querySelectorAll('input[type="time"]');
            timeInputs.forEach(input => {
                if (!input.value) input.style.color = 'transparent';
                input.addEventListener('input', () => {
                    input.style.color = input.value ? '#000000' : 'transparent';
                });
                input.addEventListener('focus', () => {
                    input.style.color = '#000000';
                });
                input.addEventListener('blur', () => {
                    if (!input.value) input.style.color = 'transparent';
                });
            });
        });
    </script>
@endsection