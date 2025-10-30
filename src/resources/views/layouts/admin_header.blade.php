<!DOCTYPE html>
<html lang="ja">

<header class="header">
    <div class="header__logo">
        <a href="/admin/attendance/list"><img src="{{ asset('img/logo.png') }}" alt="ロゴ"></a>
    </div>
    <nav class="header__nav">
        <ul>
            @if(Auth::check())
                <li><a href="/admin/attendance/list">勤怠一覧</a></li>
                <li><a href="/admin/staff/list">スタッフ一覧</a></li>
                <li><a href="/admin/stamp_correction_request/list">申請一覧</a></li>
                <li>
                    <form action="{{ route('admin.logout') }}" method="post">
                        @csrf
                        <button class="header__logout">ログアウト</button>
                    </form>
                </li>
            @endif
        </ul>
    </nav>
</header>

</html>