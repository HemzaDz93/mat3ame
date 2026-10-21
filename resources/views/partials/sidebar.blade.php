@php $user = auth()->user(); @endphp
<aside class="sidebar">
    <div class="sidebar-brand">
        @if(setting('institution_logo'))
            <img src="{{ route('settings.logo') }}" alt="الشعار">
        @endif
        <div>
            <div class="brand-text">{{ setting('institution_name', 'منصة الزاوية') }}</div>
            <div class="brand-sub">نظام إدارة مركز التكوين</div>
        </div>
    </div>

    <nav>
        <div class="nav-section-title">الرئيسية</div>
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <x-icon name="dashboard" /> <span>لوحة التحكم</span>
        </a>

        @if($user->hasRole('admin','supervisor','staff'))
            <div class="nav-section-title">الملفات والإدارة</div>
            <a class="nav-link {{ request()->routeIs('trainees.*') ? 'active' : '' }}" href="{{ route('trainees.index') }}">
                <x-icon name="users" /> <span>المتربصون</span>
            </a>
            <a class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}" href="{{ route('employees.index') }}">
                <x-icon name="user" /> <span>الموظفون</span>
            </a>
            <a class="nav-link {{ request()->routeIs('cards.*') ? 'active' : '' }}" href="{{ route('cards.index') }}">
                <x-icon name="id-card" /> <span>البطاقات التعريفية</span>
            </a>
            <a class="nav-link {{ request()->routeIs('roster*') ? 'active' : '' }}" href="{{ route('roster') }}">
                <x-icon name="list" /> <span>القوائم الاسمية</span>
            </a>
        @endif

        @if($user->hasRole('admin','supervisor','staff'))
            <div class="nav-section-title">المالية</div>
            <a class="nav-link {{ request()->routeIs('payments.index') || request()->routeIs('payments.create') ? 'active' : '' }}" href="{{ route('payments.index') }}">
                <x-icon name="credit-card" /> <span>السدادات</span>
            </a>
            <a class="nav-link {{ request()->routeIs('payments.renewals') ? 'active' : '' }}" href="{{ route('payments.renewals') }}">
                <x-icon name="refresh" /> <span>التجديد والانتهاء</span>
            </a>
            <a class="nav-link {{ request()->routeIs('payments.debts') ? 'active' : '' }}" href="{{ route('payments.debts') }}">
                <x-icon name="alert" /> <span>المتأخرات</span>
            </a>
        @endif

        <div class="nav-section-title">المطعم</div>
        @if($user->hasRole('admin','supervisor','staff','canteen'))
            <a class="nav-link {{ request()->routeIs('canteen.index') || request()->routeIs('canteen.find') || request()->routeIs('canteen.show') ? 'active' : '' }}" href="{{ route('canteen.index') }}">
                <x-icon name="utensils" /> <span>منصة المطعم</span>
            </a>
        @endif
        @if($user->canViewCanteenReports())
            <a class="nav-link {{ request()->routeIs('canteen.report*') ? 'active' : '' }}" href="{{ route('canteen.report') }}">
                <x-icon name="chart" /> <span>تقارير المطعم</span>
            </a>
        @endif
        @if($user->canManageMealForecast())
            <a class="nav-link {{ request()->routeIs('forecast.*') ? 'active' : '' }}" href="{{ route('forecast.index') }}">
                <x-icon name="clipboard" /> <span>إعلان الوجبات</span>
            </a>
        @endif

        @if($user->canBulkImport() || $user->isAdmin())
            <div class="nav-section-title">البيانات</div>
            @if($user->canBulkImport())
                <a class="nav-link {{ request()->routeIs('import.*') ? 'active' : '' }}" href="{{ route('import.form') }}">
                    <x-icon name="file-spreadsheet" /> <span>استيراد Excel</span>
                </a>
                <a class="nav-link {{ request()->routeIs('photos.*') ? 'active' : '' }}" href="{{ route('photos.form') }}">
                    <x-icon name="image" /> <span>رفع الصور الجماعي</span>
                </a>
            @endif
        @endif

        <div class="nav-section-title">النظام</div>
        <a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}">
            <x-icon name="bell" /> <span>الإشعارات</span>
            @if(($unreadCount ?? 0) > 0)
                <span class="nav-badge">{{ $unreadCount }}</span>
            @endif
        </a>
        @if($user->isAdmin())
            <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                <x-icon name="shield" /> <span>المستخدمون والأدوار</span>
            </a>
            <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.edit') }}">
                <x-icon name="settings" /> <span>إعدادات المؤسسة</span>
            </a>
            <a class="nav-link {{ request()->routeIs('logs.*') ? 'active' : '' }}" href="{{ route('logs.index') }}">
                <x-icon name="history" /> <span>سجل العمليات</span>
            </a>
            <a class="nav-link {{ request()->routeIs('backups.*') ? 'active' : '' }}" href="{{ route('backups.index') }}">
                <x-icon name="database" /> <span>النسخ الاحتياطي</span>
            </a>
        @endif
        <a class="nav-link {{ request()->routeIs('account.*') ? 'active' : '' }}" href="{{ route('account.edit') }}">
            <x-icon name="key" /> <span>حسابي</span>
        </a>
    </nav>
</aside>
