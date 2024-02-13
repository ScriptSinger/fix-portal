<!DOCTYPE html>
<html lang="en">

@include('public.layouts.includes.head')

<body>
    <div id="wrapper">
        @include('public.layouts.includes.header')
        @yield('banner')
        <section class="section lb @if (Request::is('articles/*') || Request::is('questions/*')) m3rem @endif">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
                        @yield('content')
                    </div>
                    <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                        @yield('sidebar')
                    </div>
                </div>
            </div>
        </section>
        @include('public.layouts.includes.footer')
        <div class="dmtop">Scroll to Top</div>
    </div>
    <script src="{{ asset('assets/front/js/main.js') }}"></script>
    @if (session('success') || session('error'))
        <script src="{{ asset('assets/front/js/custom/toast/notifications.js') }}"></script>

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
