@php $user = auth()->user(); @endphp
<header class="topbar">
    <button type="button" class="icon-btn" data-sidebar-toggle title="القائمة">
        <x-icon name="menu" size="20" />
    </button>

    <div>
        <h1 class="page-title">@yield('page_title', 'لوحة التحكم')</h1>
        @hasSection('page_sub')
            <div class="page-sub">@yield('page_sub')</div>
        @endif
    </div>

    <div class="topbar-spacer"></div>

    @yield('topbar_actions')

    <a href="{{ route('notifications.index') }}" class="icon-btn" title="الإشعارات">
        <x-icon name="bell" size="19" />
        @if(($unreadCount ?? 0) > 0)
            <span class="dot"></span>
        @endif
    </a>

    <div class="dropdown">
        <div class="user-chip" data-dropdown-toggle>
            <span class="avatar">{{ mb_substr($user->name ?: $user->username, 0, 1) }}</span>
            <span>
                <span class="u-name d-block">{{ $user->name ?: $user->username }}</span>
                <span class="u-role d-block">{{ $user->roleLabel() }}</span>
            </span>
        </div>
        <div class="dropdown-menu">
            <a href="{{ route('account.edit') }}"><x-icon name="key" size="16" /> تغيير كلمة المرور</a>
            @if($user->isAdmin())
                <a href="{{ route('settings.edit') }}"><x-icon name="settings" size="16" /> إعدادات المؤسسة</a>
            @endif
            <div class="divider"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"><x-icon name="logout" size="16" /> تسجيل الخروج</button>
            </form>
        </div>
    </div>
</header>
