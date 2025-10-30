@extends('layouts.default')

@section('title','スタッフ一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/admin/staff_index.css') }}" />
@endsection

@section('content')
    @include('layouts.admin_header')
    <div class="staff-list-container">
        <h1 class="page__title">スタッフ一覧</h1>
        <table class="staff-table">
            <thead>
                <tr>
                    <th class="table__header">名前</th>
                    <th class="table__header">メールアドレス</th>
                    <th class="table__header">月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <a href="{{ route('admin.staff.attendance', $user->id) }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection