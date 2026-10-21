@if(session('success'))
    <div class="alert alert-success" data-auto-hide>
        <x-icon name="check-circle" />
        <div>{{ session('success') }}</div>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        <x-icon name="x-circle" />
        <div>{{ session('error') }}</div>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning">
        <x-icon name="alert" />
        <div>{{ session('warning') }}</div>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info" data-auto-hide>
        <x-icon name="info" />
        <div>{{ session('info') }}</div>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <x-icon name="x-circle" />
        <div>
            <strong>يرجى تصحيح الأخطاء التالية:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
