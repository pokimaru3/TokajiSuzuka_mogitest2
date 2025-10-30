@extends('layouts.default')

@section('title','申請一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/user/application_index.css') }}" />
@endsection

@section('content')
    @include('layouts.header')

    <div class="request-list">
        <h1 class="request-list__title">申請一覧</h1>
        <div class="request-list__tabs">
            <a href="{{ route('stamp_correction_request.list', ['tab' => 'pending']) }}" class="tab {{ $tab === 'pending' ? 'active' : '' }}">承認待ち</a>
            <a href="{{ route('stamp_correction_request.list', ['tab' => 'approved']) }}" class="tab {{ $tab === 'approved' ? 'active' : '' }}">承認済み</a>
        </div>
        <table class="request-table">
            <thead>
                <tr>
                    <th class="table__header">状態</th>
                    <th class="table__header">名前</th>
                    <th class="table__header">対象日時</th>
                    <th class="table__header">申請理由</th>
                    <th class="table__header">申請日時</th>
                    <th class="table__header">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($requests as $request)
                    <tr class="table__row">
                        <td class="table__data">{{ $request->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
                        <td class="table__data">{{ $request->user->name }}</td>
                        <td class="table__data">
                            @if(optional($request->attendance)->work_date)
                                {{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="table__data">{{ $request->remarks }}</td>
                        <td class="table__data">{{ optional($request->created_at)->format('Y/m/d') }}</td>
                        <td class="table__data">
                            @if(optional($request->attendance)->id)
                                <a href="{{ route('attendance.show', $request->attendance->id) }}">詳細</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection