<header class="header">
    <div class="header_logo">
        <a href="{{ url('/') }}">
            <img src="{{ asset('img/logo.png') }}" alt="Time Tracker">
        </a>
    </div>

    <nav class="header_nav">
        <ul class="header_list">    

            @auth
                {{-- 一般ユーザー --}}
                @if(Auth::user()->role === 'user')
                    <li><a href="{{ route('attendance.index') }}">勤怠</a></li>
                    <li><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
                    <li><a href="{{ route('user.correction.list') }}">申請</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="header_logout" type="submit">ログアウト</button>
                        </form>
                    </li>

                {{-- 管理者ユーザー --}}
                @elseif(Auth::user()->role === 'admin')
                    <li><a href="/admin/attendance">勤怠一覧</a></li>
                    <li><a href="/admin/staff">スタッフ一覧</a></li>
                    <li><a href="/admin/request">申請一覧</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="header_logout" type="submit">ログアウト</button>
                        </form>
                    </li>
                @endif
            @endauth

        </ul>
    </nav>
</header>