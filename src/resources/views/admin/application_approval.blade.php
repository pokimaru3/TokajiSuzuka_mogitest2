@extends('layouts.default')

@section('title','修正申請')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/admin/application_approval.css') }}" />
@endsection

@section('content')
    @include('layouts.admin_header')
    <div class="attendance-detail-container">
        <div class="page__title-wrapper">
            <h1 class="page__title">勤怠詳細</h1>
        </div>
        <form id="approvalForm" action="{{ route('admin.correction.approve', $requestData->id) }}" method="POST">
            @csrf
            <div class="attendance-detail-card">
                <div class="attendance-detail__row">
                    <span class="attendance-detail__label">名前</span>
                    <span class="attendance-detail__value">{{ $requestData->user->name }}</span>
                </div>
                <div class="attendance-detail__row">
                    <span class="attendance-detail__label">日付</span>
                    <span class="attendance-detail__value">
                        {{ $requestData->attendance->work_date->format('Y年n月j日') }}
                    </span>
                </div>
                <div class="attendance-detail__row">
                    <label class="attendance-detail__label">出勤・退勤</label>
                    <input type="time" name="clock_in"
                        value="{{ $requestData->requested_clock_in ? \Carbon\Carbon::parse($requestData->requested_clock_in)->format('H:i') : ($requestData->attendance->clock_in ? \Carbon\Carbon::parse($requestData->attendance->clock_in)->format('H:i') : '') }}"
                        class="attendance-detail__input"
                        {{ $requestData->status === 'pending' ? 'readonly' : '' }}>
                    〜
                    <input type="time" name="clock_out"
                        value="{{ $requestData->requested_clock_out ? \Carbon\Carbon::parse($requestData->requested_clock_out)->format('H:i') : ($requestData->attendance->clock_out ? \Carbon\Carbon::parse($requestData->attendance->clock_out)->format('H:i') : '') }}"
                        class="attendance-detail__input"
                        {{ $requestData->status === 'pending' ? 'readonly' : '' }}>
                </div>
                @php
                    $breaks = $requestData->attendance->breaks;
                    $correctionBreaks = $requestData->correctionBreaks ?? collect();
                    $maxBreaks = max($breaks->count(), $correctionBreaks->count(), 2);
                @endphp
                @for($i = 0; $i < $maxBreaks; $i++)
                    @php
                        $break = $breaks[$i] ?? null;
                        $correction = $correctionBreaks->get($i);
                        $hasData = $break || $correction;
                        $startValue = $correction?->requested_break_start
                            ? \Carbon\Carbon::parse($correction->requested_break_start)->format('H:i')
                            : ($break?->break_start?->format('H:i') ?? '');
                        $endValue = $correction?->requested_break_end
                            ? \Carbon\Carbon::parse($correction->requested_break_end)->format('H:i')
                            : ($break?->break_end?->format('H:i') ?? '');
                    @endphp
                    <div class="attendance-detail__row">
                        <label class="attendance-detail__label">休憩{{ $i + 1 }}</label>
                        @if($hasData)
                            <input type="time" name="break_start_{{ $i + 1 }}" value="{{ $startValue }}" class="attendance-detail__input"
                            {{ $requestData->status === 'pending' ? 'readonly' : '' }}>
                            〜
                            <input type="time" name="break_end_{{ $i + 1 }}" value="{{ $endValue }}" class="attendance-detail__input"
                            {{ $requestData->status === 'pending' ? 'readonly' : '' }}>
                        @else
                            <span class="attendance-detail__empty"></span>
                        @endif
                    </div>
                @endfor
                <div class="attendance-detail__row">
                    <label class="attendance-detail__label">備考</label>
                    <textarea name="remarks" class="attendance-detail__textarea"
                    {{ $requestData->status === 'pending' ? 'readonly' : '' }}>{{ $requestData->remarks }}</textarea>
                </div>
            </div>
            <div class="attendance-detail__actions">
                <button type="submit" id="approveButton" class="btn"
                {{ $requestData->status === 'approved' ? 'disabled' : '' }}>
                {{ $requestData->status === 'approved' ? '承認済み' : '承認' }}
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('approvalForm');
            const button = document.getElementById('approveButton');
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        }
                    });
                    if (response.ok) {
                        const result = await response.json();
                        if (result.status === 'approved') {
                            button.textContent = '承認済み';
                            button.classList.add('approved');
                        }
                    } else {
                        button.textContent = 'エラー';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    button.textContent = 'エラー';
                }
            });
        });
    </script>
@endsection