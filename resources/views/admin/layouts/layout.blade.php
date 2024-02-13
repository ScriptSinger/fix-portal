<!DOCTYPE html>
<html lang="en">
@include('admin.layouts.includes.head')

<body class="hold-transition sidebar-mini">

    <div class="wrapper">
        @include('admin.layouts.includes.preloader')
        @include('admin.layouts.includes.navbar')
        @include('admin.layouts.includes.sidebar')
        @yield('content')
        @include('admin.layouts.includes.footer')
    </div>

    <script src="{{ asset('assets/admin/js/adminlte.js') }}"></script>
    @if (session('success') || session('error'))
        <script src="{{ asset('assets/admin/js/custom/toast/notifications.js') }}"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var notificationType = @json(session('success') ? 'success' : 'error');
                var notificationMessage = @json(session('success') ?? session('error'));

                showNotification(notificationType, notificationMessage);
            });
        </script>

        @php
            session()->forget(['success', 'error']);
        @endphp
    @endif
    @stack('scripts')
</body>

</html>
