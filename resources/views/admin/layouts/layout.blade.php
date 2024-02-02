<!DOCTYPE html>
<html lang="en">

@include('admin.layouts.includes.head')

<body class="hold-transition sidebar-mini">
    <!-- Site wrapper -->
    <div class="wrapper">
        @include('admin.layouts.includes.preloader')
        @include('admin.layouts.includes.navbar')

        @include('admin.layouts.includes.sidebar')


        <!-- Content Wrapper. Contains page content -->
        @yield('content')
        <!-- /.content-wrapper -->

        @include('admin.layouts.includes.footer')

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->


    <script src="{{ asset('assets/admin/js/adminlte.js') }}"></script>
    <script src="{{ asset('assets/admin/js/custom/adminlte/sidebar.js') }}"></script>

    @if (session('success') || session('error'))
        <script src="{{ asset('assets/admin/js/custom/toast/notifications.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var notificationType = @json(session('success') ? 'success' : 'error');
                var notificationMessage = @json(session('success') ?? session('error'));

                showNotification(notificationType, notificationMessage);
            });
        </script>
    @endif

    @stack('scripts')


</body>

</html>
